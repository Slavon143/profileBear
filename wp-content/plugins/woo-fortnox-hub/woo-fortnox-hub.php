<?php

/**
 * The main plugin file for WooCommerce Fortnox Hub.
 *
 * This file is included during the WordPress bootstrap process if the plugin is active.
 *
 * @package   WooCommerce_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      https://bjorntech.com
 * @copyright 2017-2020 BjornTech AB
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Fortnox Hub
 * Plugin URI:        https://www.bjorntech.com/fortnox-hub?utm_source=wp-fortnox&utm_medium=plugin&utm_campaign=product
 * Description:       Sync your WooCommerce shop with Fortnox
 * Version:           5.4.3
 * Author:            BjornTech
 * Author URI:        https://bjorntech.com?utm_source=wp-fortnox&utm_medium=plugin&utm_campaign=product
 * Text Domain:       woo-fortnox-hub
 * Domain Path:       /languages
 *
 * WC requires at least: 4.0
 * WC tested up to: 6.9
 *
 * Copyright:         2020 BjornTech AB
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('ABSPATH') || exit;

define('WC_FORTNOX_MIN_WC_VER', '4.0');

/**
 * WooCommerce fallback notice.
 *
 * @since 4.0.0
 * @return string
 */

if (!function_exists('woocommerce_fortnox_hub_missing_wc_notice')) {
    function woocommerce_fortnox_hub_missing_wc_notice()
    {
        /* translators: 1. URL link. */
        echo '<div class="error"><p><strong>' . sprintf(esc_html__('Fortnox Hub requires WooCommerce to be installed and active. You can download %s here.', 'woo-fortnox-hub'), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>') . '</strong></p></div>';
    }
}

/**
 * WooCommerce not supported fallback notice.
 *
 * @since 4.0.0
 * @return string
 */

if (!function_exists('woocommerce_fortnox_hub_wc_not_supported')) {
    function woocommerce_fortnox_hub_wc_not_supported()
    {
        /* translators: $1. Minimum WooCommerce version. $2. Current WooCommerce version. */
        echo '<div class="error"><p><strong>' . sprintf(esc_html__('Fortnox Hub requires WooCommerce %1$s or greater to be installed and active. WooCommerce %2$s is no longer supported.', 'woo-fortnox-hub'), WC_FORTNOX_MIN_WC_VER, WC_VERSION) . '</strong></p></div>';
    }
}

/**
 *    Woo_Fortnox_Hub
 */

if (!class_exists('Woo_Fortnox_Hub', false)) {
    class Woo_Fortnox_Hub
    {
        /**
         * Plugin data
         */
        const NAME = 'WooCommerce Fortnox Hub';
        const CLIENT_ID = 'c8abuXyDQxt2';
        const VERSION = '5.4.3';
        const SCRIPT_HANDLE = 'woo-fortnox-hub';
        const PLUGIN_FILE = __FILE__;

        public $plugin_basename;
        public $includes_dir;
        public $admin_url;
        public $plugin_file = __FILE__;
        public $client_id;

        /**
         * Plugin helper classes
         */
        public $logger;
        public $fortnox;
        public $plugin_version;
        public $do_not_sync = false;

        /**
         *  The instance for the plugin
         *
         * @var    mixed
         * @access public
         * @static
         */
        public static $instance = null;

        /**
         * Init and hook in the integration.
         **/
        public function __construct()
        {
            $this->plugin_basename = plugin_basename(self::PLUGIN_FILE);
            $this->includes_dir = plugin_dir_path(self::PLUGIN_FILE) . 'includes/';
            $this->libraries_dir = plugin_dir_path(self::PLUGIN_FILE) . 'libraries/';
            $this->admin_url = trailingslashit(plugins_url('admin', self::PLUGIN_FILE));
            $this->plugin_version = self::VERSION;

            $this->client_id = self::CLIENT_ID;

            $this->includes();

            add_action('plugins_loaded', array($this, 'maybe_load_plugin'));
        }

        private function includes()
        {
            require_once $this->includes_dir . 'admin/woo-fortnox-hub-constants.php';
            require_once $this->includes_dir . 'admin/class-wcfh-util.php';
            require_once $this->includes_dir . 'admin/class-woo-fortnox-hub-log.php';
            require_once $this->includes_dir . 'admin/class-woo-fortnox-hub-exception.php';
            require_once $this->includes_dir . 'class-woo-fortnox-countries.php';
            //   require_once $this->includes_dir . 'class-woo-fortnox-hub-email.php';
        }

        public function include_settings($settings)
        {
            $settings[] = include $this->includes_dir . 'admin/class-woo-fortnox-hub-settings.php';
            return $settings;
        }

        public function maybe_load_plugin()
        {
            if (!class_exists('WooCommerce')) {
                add_action('admin_notices', 'woocommerce_fortnox_hub_missing_wc_notice');
                return;
            }

            if (!WCFH_Util::wc_version_check(WC_FORTNOX_MIN_WC_VER)) {
                add_action('admin_notices', 'woocommerce_fortnox_hub_wc_not_supported');
                return;
            }

            require_once $this->includes_dir . 'admin/class-fortnox-notices.php';

            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links'));
            add_filter('woocommerce_get_settings_pages', array($this, 'include_settings'));
            add_action('woocommerce_admin_field_infotext', array($this, 'show_infotext'), 10);
            add_action('woocommerce_api_fortnox-event', array($this, 'fortnox_event'));
            add_action('woocommerce_api_fortnox_admin', array($this, 'fortnox_admin'));

            if (is_admin()) {
                add_action('wp_ajax_fortnox_clear_notice', array($this, 'ajax_clear_notice'));
                add_action('admin_enqueue_scripts', array($this, 'add_admin_styles_and_scripts'));
                add_action('wp_ajax_fortnox_clear_cache', array($this, 'ajax_clear_cache'));
                add_action('wp_ajax_fortnox_connection', array($this, 'ajax_fortnox_connection'));
                add_action('wp_ajax_fortnox_check_activation', array($this, 'ajax_fortnox_check_activation'));
                add_action('wp_ajax_fortnox_price_stocklevel_message', array($this, 'ajax_fortnox_price_stocklevel_message'));
                add_action('wp_ajax_fortnox_wc_product_message', array($this, 'ajax_wc_product_message'));
                add_action('in_admin_header', array($this, 'fortnox_modal_admin'));
                add_action('admin_notices', array($this, 'check_configuration'));
                add_action('wp_ajax_wcfh_processing_button', array($this, 'ajax_wcfh_processing_button'));

                require_once $this->includes_dir . 'class-woo-fortnox-hub-product-quick-bulk-edit.php';
            }

            require_once $this->libraries_dir . 'bjorntech-fortnox-api/class-fortnox-api.php';

            $this->logger = new Woo_Fortnox_Hub_Log(get_option('fortnox_logging') != 'yes');
            $this->fortnox = new Fortnox_API(get_option('fortnox_service_url'));

            require_once $this->includes_dir . 'class-woo-fortnox-account-handler.php';
            require_once $this->includes_dir . 'class-woo-fortnox-product-tabs.php';
            require_once $this->includes_dir . 'class-woo-fortnox-customer-handler.php';
            require_once $this->includes_dir . 'class-woo-fortnox-hub-document-handler.php';
            require_once $this->includes_dir . 'class-woo-fortnox-hub-document-admin.php';

            if ('stockchange' === get_option('fortnox_woo_order_creates')) {
                require_once $this->includes_dir . 'class-woo-fortnox-hub-stockchange.php';
            } else {
                require_once $this->includes_dir . 'class-woo-fortnox-hub-order.php';
                require_once $this->includes_dir . 'class-woo-fortnox-hub-invoice.php';
                require_once $this->includes_dir . 'class-woo-fortnox-hub-refund.php';
            }

            require_once $this->includes_dir . 'class-woo-fortnox-hub-invoice-status-handler.php';
            require_once $this->includes_dir . 'api/class-woo-fortnox-api-filters-and-hooks.php';
            require_once $this->includes_dir . 'class-woo-fortnox-hub-wc-product-handler.php';
            require_once $this->includes_dir . 'class-woo-fortnox-hub-fn-product-handler.php';
            require_once $this->includes_dir . 'payout-handlers/class-woo-fortnox-payouts-handler.php';
            require_once $this->includes_dir . 'payout-handlers/class-woo-fortnox-payouts.php';
            require_once $this->includes_dir . 'payout-handlers/class-woo-fortnox-payouts-invoice.php';
            require_once $this->includes_dir . 'payout-handlers/class-woo-fortnox-payouts-voucher.php';
            require_once $this->includes_dir . 'payout-handlers/class-woo-fortnox-payouts-detailed-invoice.php';
            require_once $this->includes_dir . 'payout-handlers/class-woo-fortnox-payouts-detailed-voucher.php';

            if (class_exists('WC_iZettle_Integration', false) || class_exists('WC_Zettle_Integration', false)) {
                require_once $this->includes_dir . 'payout-handlers/zettle/class-woo-fortnox-hub-zettle-handler.php';
            }

            if (class_exists('WC_Stripe', false) || function_exists('woocommerce_gateway_stripe_init') || function_exists('stripe_wc') || wc_string_to_bool(get_option('fortnox_stripe_payouts'))) {
                require_once $this->includes_dir . 'payout-handlers/stripe/class-woo-fortnox-hub-payouts-handler-stripe.php';
            }

            if (class_exists('WC_Klarna_Payments', false) || class_exists('KCO') || class_exists('Klarna_Checkout_For_WooCommerce', false)) {
                require_once $this->includes_dir . 'payout-handlers/klarna/class-woo-fortnox-hub-payouts-handler-klarna.php';
            }

            if (class_exists('WC_Svea_Checkout') || wc_string_to_bool(get_option('fortnox_svea_payouts'))) {
                require_once $this->includes_dir . 'payout-handlers/svea/class-woo-fortnox-hub-payouts-handler-svea.php';
            }

            if ('yes' === get_option('fortnox_clearhaus_payouts')) {
                require_once $this->includes_dir . 'payout-handlers/clearhaus/class-woo-fortnox-hub-payouts-handler-clearhaus.php';
            }

            if (function_exists('Nets_Easy')) {
                //  require_once $this->includes_dir . 'payout-handlers/nets-easy/class-woo-fortnox-hub-payouts-handler-nets-easy.php';
            }

            if (function_exists('activateResursGatewayScripts')) {
                require_once $this->includes_dir . 'payout-handlers/resurs/class-woo-fortnox-hub-resurs-handler.php';
            }

            if (defined('YITH_YWGC_PREMIUM')) {
                require_once $this->includes_dir . 'giftcard-handlers/yith-woocommerce-gift-cards/class-woo-fortnox-hub-ywgc.php';
            }

            if (class_exists('PW_Gift_Cards', false)) {
                require_once $this->includes_dir . 'giftcard-handlers/pw-gift-cards/class-woo-fortnox-hub-pwgc.php';
            }

            require_once $this->includes_dir . 'payout-handlers/paypal/class-woo-fortnox-hub-paypal-handler.php';

            add_action('init', array($this, 'init'));
        }

        /**
         * Add Admin JS
         */
        public function add_admin_styles_and_scripts($pagehook)
        {

            // do nothing if we are not on the target pages
            if ('edit.php' == $pagehook) {
                wp_enqueue_script('fortnox_quick_bulk_edit', plugin_dir_url(__FILE__) . 'includes/javascript/fortnox_quick_bulk_edit.js', array('jquery'), $this->plugin_version);
            }

            wp_register_style('fortnox-hub', plugin_dir_url(__FILE__) . 'assets/css/fortnox.css', array(), $this->plugin_version);
            wp_enqueue_style('fortnox-hub');

            wp_enqueue_script('woo-fortnox-hub-admin-script', plugins_url('/admin/js/admin.js', __FILE__), ['jquery'], $this->plugin_version, true);

            wp_localize_script('woo-fortnox-hub-admin-script', 'fortnox', array(
                'nonce' => wp_create_nonce('ajax-fortnox-hub'),
                'redirect_warning' => __('I agree to the BjornTech Privacy Policy', 'woo-fortnox-hub'),
                'email_warning' => __('Enter mail and save before connecting to the service', 'woo-fortnox-hub'),
                'sync_message' => __('Number of days back to sync.', 'woo-fortnox-hub'),
                'sync_warning' => __('Please enter the number of days back you want to sync.', 'woo-fortnox-hub'),
                'wc_product_pricelist' => __('Updating the pricelist using price from WooCommerce replaces any price existing in the selected Fortnox pricelist.', 'woo-fortnox-hub'),
                'wc_product_sale_pricelist' => __('Updating the pricelist using sale price from WooCommerce replaces any price existing in the selected Fortnox pricelist.', 'woo-fortnox-hub'),
                'process_price' => __('Updating price from Fortnox replaces the existing price in WooCommerce with the price from the selected Fortnox pricelist.', 'woo-fortnox-hub'),
                'process_sale_price' => __('Updating sale price from Fortnox replaces the existing sale price in WooCommerce with the price from the selected Fortnox pricelist.', 'woo-fortnox-hub'),
                'process_stocklevel' => __('Updating stocklevel from Fortnox replaces the existing stocklevel in WooCommerce. Are you sure that you want to change this setting?', 'woo-fortnox-hub'),
                'process_wc_stocklevel' => __('Updating stocklevel from WooCommerce replaces the existing stocklevel in Fortnox. Are you sure that you want to change this setting?', 'woo-fortnox-hub'),
                'remove_vat_from_prices' => __('When changing this setting, always make sure to manually update all prices from WooCommerce to Fortnox before you import any prices from Fortnox to WooCommerce. If this is done in the wrong order prices will be corrupted. Have you set this up correctly and are you sure that you want to change this setting?', 'woo-fortnox-hub'),
                'default_wc_stocklevel' => __('Do not update stocklevel', 'woo-fortnox-hub'),
                'update_product_text' => __('Update', 'woo-fortnox-hub'),
                'update_variation_text' => __('Update Fortnox', 'woo-fortnox-hub'),
            ));
        }

        public function init()
        {
            if ('yes' == get_option('fortnox_manual_cron')) {
                do_action('action_scheduler_run_queue');
            }

            if (!get_site_transient('fortnox_update_5_1_2')) {
                if ('yes' === get_option('fortnox_process_stocklevel')) {
                    update_option('fortnox_maybe_change_manage_stock', 'yes');
                }
                set_site_transient('fortnox_update_5_1_2', microtime());
            }

            if (!get_site_transient('fortnox_update_5_3_0')) {

                if (wc_string_to_bool(get_option('fortnox_create_klarna_invoices'))) {
                    update_option('fortnox_klarna_document_type', 'detailed_invoice');
                    update_option('fortnox_create_klarna_payouts_automatically', 'yes');

                }

                if (wc_string_to_bool(get_option('fortnox_create_stripe_invoices'))) {
                    update_option('fortnox_stripe_document_type', 'detailed_invoice');
                    update_option('fortnox_create_stripe_payouts_automatically', 'yes');
                }

                set_site_transient('fortnox_update_5_3_0', microtime());

            }

            if (!get_site_transient('fortnox_hub_activated_or_upgraded')) {
                delete_site_transient('fortnox_accesstoken');

                Fortnox_Notice::clear();
                set_site_transient('fortnox_hub_activated_or_upgraded', date('c'));

                if (!get_site_transient('fortnox_did_show_start_message') && $this->is_fortnox_hub_new_install()) {
                    $message = __('Fortnox Hub is now ready for use. Please do read our <a href="https://bjorntech.com/sv/kom-igang-med-fortnox-hub?utm_source=wp-fortnox&utm_medium=plugin&utm_campaign=product">installation and configuration instructions</a> before starting.', 'woo-fortnox-hub');
                    Fortnox_Notice::add($message, 'info', false, true);
                }
            }

            $response = WCFH_Util::service_message();
            if (false !== $response) {
                Fortnox_Notice::add($response->message, $response->type, 'subscription_info', false);
            } else {
                Fortnox_Notice::clear('subscription_info');
            }

            $this->set_fortnox_hub_version_upgraded();

        }

        public function check_configuration()
        {
            if (false !== ($message = WCFH_Util::check_sync_config())) {
                Fortnox_Notice::add($message, 'error', 'sync_loop_error', false);
                $this->do_not_sync = true;
            } else {
                Fortnox_Notice::clear('sync_loop_error');
                $this->do_not_sync = false;
            }
        }

        public function ajax_wc_product_message()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'ajax-fortnox-hub')) {
                wp_die();
            }

            if (isset($_POST['id'])) {
                $id = $_POST['id'];
                if ($response = Fortnox_Notice::get($id)) {
                    wp_send_json(array(
                        'status' => $response['type'],
                        'message' => '<p>' . $response['message'] . '</p>',
                    ));
                }
            }

            wp_send_json(array(
                'status' => 'false',
            ));

            exit;
        }

        public function is_fortnox_hub_new_install()
        {
            return !get_option('fortnox_hub_version');
        }

        public function fortnox_hub_version_greater_than($version)
        {
            return version_compare($this->plugin_version, $version, '>');
        }

        public function set_fortnox_hub_version_upgraded()
        {
            update_option('fortnox_hub_version', $this->plugin_version);
        }

        public function ajax_fortnox_price_stocklevel_message()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'ajax-fortnox-hub')) {
                wp_die();
            }

            if (isset($_POST['id'])) {
                $id = $_POST['id'];
                if ($response = Fortnox_Notice::get($id)) {
                    wp_send_json(array(
                        'status' => $response['type'],
                        'message' => '<p>' . $response['message'] . '</p>',
                    ));
                }
            }

            wp_send_json(array(
                'status' => 'false',
            ));

            exit;
        }

        public function ajax_clear_notice()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'ajax-fortnox-hub')) {
                wp_die();
            }

            if (isset($_POST['id'])) {
                $id = $_POST['id'];
                $this->logger->add(sprintf('Clear notice %s', $id));
                Fortnox_Notice::clear($id);
            }

            $response = array(
                'status' => 'success',
            );

            wp_send_json($response);
            exit;
        }

        public static function add_action_links($links)
        {
            $links = array_merge(array(
                '<a href="' . admin_url('admin.php?page=wc-settings&tab=fortnox_hub') . '">' . __('Settings', 'woo-fortnox-hub') . '</a>',
            ), $links);

            return $links;
        }

        public function tomorrow_morning()
        {
            return strtotime('tomorrow') - current_time('timestamp') + (HOUR_IN_SECONDS * 6);
        }

        public function ajax_wcfh_processing_button()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'ajax-fortnox-hub')) {
                wp_die();
            }

            $id = $_POST['id'];

            $processing_queue = WCFH_Util::get_processing_queue($id);
            $queue_lenght = count($processing_queue);

            $display_name = WCFH_Util::display_name($id);

            if ('start' == $_POST['task'] && 0 == $queue_lenght) {
                if (false === WC_FH()->do_not_sync) {
                    if (0 == ($number_synced = apply_filters($id . '_filter', 0, true))) {
                        $response = array(
                            'status' => 'success',
                            'ready' => true,
                            'message' => sprintf(__('No %s to syncronize found.', 'woo-fortnox-hub'), $display_name),
                        );
                    } else {
                        $response = array(
                            'status' => 'success',
                            'button_text' => __('Cancel', 'woo-fortnox-hub'),
                            'message' => sprintf(__('Added %s %s to the syncronisation queue.', 'woo-fortnox-hub'), $number_synced, $display_name),
                        );
                    }
                } else {
                    $response = array(
                        'status' => 'success',
                        'message' => sprintf(__('You are not allowed to syncronise %s at this time.', 'woo-fortnox-hub'), $display_name),
                    );
                }
            } elseif ('start' == $_POST['task']) {
                as_unschedule_all_actions($id . '_process');
                $response = array(
                    'status' => 'success',
                    'button_text' => __('Start', 'woo-fortnox-hub'),
                    'ready' => true,
                    'message' => sprintf(__('Successfully removed %s %s from the syncronisation queue.', 'woo-fortnox-hub'), $queue_lenght, $display_name),
                );
            } elseif (0 != $queue_lenght) {
                $response = array(
                    'status' => 'success',
                    'button_text' => __('Cancel', 'woo-fortnox-hub'),
                    'status_message' => sprintf(__('%s %s in syncronisation queue - click "Cancel" to clear queue.', 'woo-fortnox-hub'), $queue_lenght, $display_name),
                    'message' => sprintf(__('%ss have been added to the syncronisation queue.', 'woo-fortnox-hub'), $display_name),
                );
            } else {
                $response = array(
                    'status' => 'success',
                    'ready' => true,
                    'button_text' => __('Start', 'woo-fortnox-hub'),
                    'message' => sprintf(__('%s syncronisation finished', 'woo-fortnox-hub'), $display_name),
                );
            }

            wp_send_json($response);
        }

        public function fortnox_event()
        {
            try {
                $request_body = file_get_contents("php://input");

                if (empty($request_body)) {
                    $this->logger->add(sprintf('Request body missing'));
                } else {
                    $json = json_decode($request_body);

                    if ($json->authorization_code == get_option('fortnox_authorization_code')) {
                        $this->logger->add(sprintf('Received callback %s - %s', $json->topic, property_exists($json, 'entityId') ? $json->entityId : 'n/a'));

                        $this_sync_time = date('Y-m-d H:i', current_time('timestamp', true) + get_option('fortnox_gmt_offset', 0));

                        if ('articles' == $json->topic && 'yes' == get_option('fortnox_sync_from_fortnox_automatically') && false === $this->do_not_sync) {
                            update_option('fortnox_last_sync_products', $this_sync_time, true);
                            do_action('wcfh_sync_fn_products_process', $json->entityId);
                        }

                        if ('invoices' == $json->topic && 'yes' == get_option('fortnox_check_invoices_automatically')) {
                            update_option('fortnox_hub_sync_last_sync_invoices', $this_sync_time, true);
                            do_action('fortnox_process_changed_invoices', $json->entityId);
                        }

                        if ('termsofdeliveries' == $json->topic) {
                            delete_site_transient('fortnox_terms_of_deliveries');
                        }

                        if ('waysofdeliveries' == $json->topic) {
                            delete_site_transient('fortnox_way_of_deliveries');
                        }

                        if ('termsofpayments' == $json->topic) {
                            delete_site_transient('fortnox_terms_of_payments');
                        }

                        if ('subscriptionstart' == $json->topic) {
                            do_action('fortnox_clear_cache');
                            delete_site_transient('fortnox_accesstoken');
                            $company_info = apply_filters('fortnox_get_company_information', false);
                            Fortnox_Notice::clear();
                        }
                    }
                }
            } catch (Throwable $t) {
                if (method_exists($t, 'write_to_logs')) {
                    $t->write_to_logs();
                } else {
                    WC_FH()->logger->add(print_r($t, true));
                }
            }

            wp_die('Sucess', 200);
        }

        public function fortnox_modal_admin()
        {?>
<div id="fortnox-modal-id" class="fortnox-modal" style="display: none">
    <div class="fortnox-modal-content fortnox-centered">
        <span class="fortnox-close">&times;</span>
        <div class="fortnox-messages fortnox-centered">
            <h1>
                <p id="fortnox-status"></p>
            </h1>
        </div>
        <div class="bjorntech-logo fortnox-centered">
            <img id="fortnox-logo-id" class="fortnox-centered"
                src="<?php echo plugin_dir_url(__FILE__) . 'assets/images/BjornTech_logo_small.png'; ?>" />
        </div>
    </div>
</div>
<?php }

        public function ajax_fortnox_check_activation()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'ajax-fortnox-hub')) {
                wp_die();
            }

            $message = '';
            if (get_site_transient('fortnox_handle_account')) {
                if ($connected = get_site_transient('fortnox_connect_result')) {
                    delete_site_transient('fortnox_handle_account');
                    delete_site_transient('fortnox_connect_result');
                    if ($connected == 'failure') {
                        $message = __('The activation of the account failed', 'woo-fortnox-hub');
                    }
                } else {
                    $message = __('We have sent a mail with the activation link. Click on the link to activate the service.', 'woo-fortnox-hub');
                }
            } else {
                $connected = 'failure';
                $message = __('The link has expired, please connect again to get a new link.', 'woo-fortnox-hub');
            }

            $response = array(
                'status' => $connected ? $connected : 'waiting',
                'message' => $message,
            );

            wp_send_json($response);
            wp_die();
        }

        public function show_infotext($value)
        {
            echo '<div id="' . esc_attr($value['id']) . '">';
            echo '<tr valign="top">';
            echo '<th scope="row" class="titledesc">';
            echo '<label for="' . esc_attr($value['id']) . '">' . esc_html($value['title']) . wc_help_tip($value['desc']) . '</label>';
            echo '</th>';
            echo '<td class="' . esc_attr(sanitize_title($value['id'])) . '-description">';
            echo wp_kses_post(wpautop(wptexturize($value['text'])));
            echo '</td>';
            echo '</tr>';
            echo '</div>';
        }

        public function fortnox_admin()
        {
            if (array_key_exists('nonce', $_REQUEST) && $_REQUEST['nonce'] == get_site_transient('fortnox_handle_account')) {
                if (array_key_exists('authorization_code', $_REQUEST) && $_REQUEST['authorization_code'] == get_option('fortnox_authorization_code')) {
                    $request_body = file_get_contents("php://input");
                    $json = json_decode($request_body);
                    if ($json !== null && json_last_error() === JSON_ERROR_NONE) {
                        update_option('fortnox_refresh_token', $json->refresh_token);
                        update_option('fortnox_valid_to', $json->valid_to);
                        delete_site_transient('fortnox_accesstoken');
                        $this->logger->add(sprintf('Got refresh token %s from service', $json->refresh_token));
                        set_site_transient('fortnox_connect_result', 'success', MINUTE_IN_SECONDS);
                        wp_die('', '', 200);
                    } else {
                        $this->logger->add('Failed decoding authorize json');
                    }
                } else {
                    $this->logger->add('Faulty call to admin callback');
                }
            } else {
                $this->logger->add('Nonce not verified at fortnox_admin');
            }
            set_site_transient('fortnox_connect_result', 'failure', MINUTE_IN_SECONDS);
            wp_die();
        }

        public function ajax_clear_cache()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'ajax-fortnox-hub')) {
                wp_die();
            }

            do_action('fortnox_clear_cache');

            delete_site_transient('fortnox_accesstoken');

            Fortnox_Notice::clear();

            $response = array(
                'result' => 'success',
                'message' => __('The cache holding Fortnox data has been cleared.', 'woo-fortnox-hub'),
            );

            wp_send_json($response);
        }

        public function ajax_fortnox_connection()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'ajax-fortnox-hub')) {
                wp_die();
            }

            if ('fortnox_connect' == $_POST['id']) {
                $user_email = get_option('fortnox_user_email');
                $site_url = ($webhook_url = get_option('bjorntech_alternate_webhook_url')) ? $webhook_url : get_site_url();

                if (($user_email == '' && $_POST['user_email'] == '') || $_POST['user_email'] == '') {
                    $response = array(
                        'result' => 'error',
                        'message' => __('A valid email address to where the verification mail is to be sent must be present.', 'woo-fortnox-hub'),
                    );
                } else {
                    $authorization_code = get_option('fortnox_authorization_code');
                    //$authorization_code = sanitize_text_field($_POST['authorization_code']);
                    $user_email = sanitize_email($_POST['user_email']);
                    update_option('fortnox_authorization_code', $authorization_code);
                    update_option('fortnox_user_email', $user_email);

                    $nonce = wp_create_nonce('fortnox_handle_account');
                    set_site_transient('fortnox_handle_account', $nonce, DAY_IN_SECONDS);

                    $site_params = array(
                        'user_email' => $user_email,
                        'plugin_version' => $this->plugin_version,
                        'authorization_code' => $authorization_code,
                        'site_url' => $site_url,
                        'nonce' => $nonce,
                    );

                    $encoded_params = base64_encode(json_encode($site_params, JSON_INVALID_UTF8_IGNORE));
                    $adm_url = "https://" . $this->fortnox->get_service_url() . "oauth-redirect";

                    $scope = "archive%20bookkeeping%20costcenter%20companyinformation%20currency%20customer%20invoice%20article%20order%20payment%20price%20print%20profile%20project%20settings%20supplier%20supplierinvoice%20warehouse";
                    if ($user_email == $_POST['email']) {
                        $state = array(
                            'state' => "https://apps.fortnox.se/oauth-v1/auth?response_type=code&client_id=$this->client_id&scope=$scope&state=$encoded_params&redirect_uri=$adm_url",
                            'result' => "success",
                        );
                    } else {
                        $state = array(
                            'state' => 'mismatch',
                            'result' => "failure",
                        );
                    }
                    wp_send_json($state);
                    wp_die();

                    /*$url = 'https://' . $this->fortnox->get_service_url() . 'connect?' . http_build_query(array(
                'user_email' => $user_email,
                'plugin_version' => $this->plugin_version,
                'authorization_code' => $authorization_code,
                'site_url' => $site_url,
                'nonce' => $nonce,
                ));

                $sw_response = wp_remote_get($url, array('timeout' => 20));

                if (is_wp_error($sw_response)) {
                $code = $sw_response->get_error_code();
                $error = $sw_response->get_error_message($code);
                $response_body = json_decode(wp_remote_retrieve_body($sw_response));
                $response = array(
                'result' => 'error',
                'message' => __('Something went wrong when connecting to the BjornTech service. Contact support at hello@bjorntech.com', 'woo-fortnox-hub'),
                );
                $this->logger->add(sprintf('Failed connecting the plugin to the service %s - %s', print_r($code, true), print_r($error, true)));
                } else {
                if ($response_body = json_decode(wp_remote_retrieve_body($sw_response))) {
                $response = (array) $response_body;
                }
                }*/
                }
            }

            if ('fortnox_disconnect' == $_POST['id']) {
                delete_option('fortnox_refresh_token');
                delete_site_transient('fortnox_accesstoken');
                delete_option('fortnox_valid_to');
                $response = array(
                    'result' => 'success',
                    'message' => __('Successfully disconnected from Fortnox', 'woo-fortnox-hub'),
                );
            }

            wp_send_json($response);
        }

        /**
         * Returns a new instance of self, if it does not already exist.
         *
         * @access public
         * @static
         * @return Woo_Fortnox_Hub
         */
        public static function instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function is_front_end()
        {
            return !is_admin() || defined('DOING_AJAX');
        }
    }
}

/**
 * Make the object available for later use
 *
 * @return Woo_Fortnox_Hub
 */
function WC_FH()
{
    return Woo_Fortnox_Hub::instance();
}

/**
 * Instantiate
 */
$wc_fortnox_hub = WC_FH();

/**
 * Activation activities to be performed then the plugin is activated
 */
function woo_fortnox_hub_activate()
{
    delete_site_transient('fortnox_hub_activated_or_upgraded');
}

register_activation_hook(__FILE__, 'woo_fortnox_hub_activate');

/**
 * Upgrade activities to be performed when the plugin is upgraded
 */
function fortnox_hub_upgrade_completed($upgrader_object, $options)
{
    $our_plugin = plugin_basename(__FILE__);

    if ($options['action'] == 'update' && $options['type'] == 'plugin' && isset($options['plugins'])) {
        foreach ($options['plugins'] as $plugin) {
            if ($plugin == $our_plugin) {

                /**
                 * Delete transient containing the date for activation or upgrade
                 */
                delete_site_transient('fortnox_hub_activated_or_upgraded');
            }
        }
    }
}
add_action('upgrader_process_complete', 'fortnox_hub_upgrade_completed', 10, 2);

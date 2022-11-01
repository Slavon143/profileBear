<?php

/**
 * The plugin bootstrap file
 *
 *
 * @link              https://www.webtoffee.com/
 * @since             2.5.0
 * @package           Wf_Woocommerce_Packing_List
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels
 * Plugin URI:        https://www.webtoffee.com/product/woocommerce-pdf-invoices-packing-slips/
 * Description:       Prints Packing List,Invoice,Delivery Note and Shipping Label.
 * Version:           3.0.6
 * Author:            WebToffee
 * Author URI:        https://www.webtoffee.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       print-invoices-packing-slip-labels-for-woocommerce
 * Domain Path:       /languages
 * WC tested up to:   6.9
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}


include_once(ABSPATH.'wp-admin/includes/plugin.php');


$current_plugin_name='WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels (Basic)';
$wt_pklist_no_plugin_conflict=true;

//check if premium version is there
if(is_plugin_active('wt-woocommerce-packing-list/wf-woocommerce-packing-list.php')) 
{
    $active_plugin_name='WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels (Pro)';
    $wt_pklist_no_plugin_conflict=false;

}else if (is_plugin_active('shipping-labels-for-woo/wf-woocommerce-packing-list.php'))
{
    $active_plugin_name='WooCommerce Shipping Label (Basic)';
    $wt_pklist_no_plugin_conflict=false;
}

if(!$wt_pklist_no_plugin_conflict)
{
    //return;
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die(sprintf(__("The plugins %s and %s cannot be active in your store at the same time. Kindly deactivate one of these prior to activating the other.", 'print-invoices-packing-slip-labels-for-woocommerce'), $active_plugin_name, $current_plugin_name), "", array('link_url' => admin_url('plugins.php'), 'link_text' => __('Go to plugins page', 'print-invoices-packing-slip-labels-for-woocommerce') ));
}

if(!defined('WF_PKLIST_VERSION')) //check plugin file already included
{
    define ( 'WF_PKLIST_PLUGIN_DEVELOPMENT_MODE', false );
    define ( 'WF_PKLIST_PLUGIN_BASENAME', plugin_basename(__FILE__) );
    define ( 'WF_PKLIST_PLUGIN_PATH', plugin_dir_path(__FILE__) );
    define ( 'WF_PKLIST_PLUGIN_URL', plugin_dir_url(__FILE__));
    define ( 'WF_PKLIST_PLUGIN_FILENAME',__FILE__);
    define ( 'WF_PKLIST_POST_TYPE','wf_woocommerce_packing_list');
    define ( 'WF_PKLIST_ACTIVATION_ID','wt_pdfinvoice');
    define ( 'WF_PKLIST_DOMAIN','print-invoices-packing-slip-labels-for-woocommerce');
    define ( 'WF_PKLIST_SETTINGS_FIELD','Wf_Woocommerce_Packing_List');
    define ( 'WF_PKLIST_PLUGIN_NAME','print-invoices-packing-slip-labels-for-woocommerce');
    define ( 'WF_PKLIST_PLUGIN_DESCRIPTION','WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels');

    /**
     * Currently plugin version.
     */
    define( 'WF_PKLIST_VERSION', '3.0.6' );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wf-woocommerce-packing-list-activator.php
 */
if(!function_exists('activate_wf_woocommerce_packing_list'))
{
    function activate_wf_woocommerce_packing_list()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wf-woocommerce-packing-list-activator.php';
        Wf_Woocommerce_Packing_List_Activator::activate();
    }
    register_activation_hook( __FILE__, 'activate_wf_woocommerce_packing_list' );
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wf-woocommerce-packing-list-deactivator.php
 */
if(!function_exists('deactivate_wf_woocommerce_packing_list'))
{
    function deactivate_wf_woocommerce_packing_list()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wf-woocommerce-packing-list-deactivator.php';
        Wf_Woocommerce_Packing_List_Deactivator::deactivate();
    }
    register_deactivation_hook( __FILE__,'deactivate_wf_woocommerce_packing_list');
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wf-woocommerce-packing-list.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.5.0
 */
if(!function_exists('run_wf_woocommerce_packing_list'))
{
    function run_wf_woocommerce_packing_list() {

        $plugin = new Wf_Woocommerce_Packing_List();
        $plugin->run();

    }
}
if(!function_exists('woocommerce_packing_list_check_necessary'))
{
    function woocommerce_packing_list_check_necessary()
    {
        global $wpdb;
        $search_query = "SHOW TABLES LIKE %s";
        $tb=Wf_Woocommerce_Packing_List::$template_data_tb;
        $like = '%' . $wpdb->prefix.$tb.'%';
        if(!$wpdb->get_results($wpdb->prepare($search_query, $like),ARRAY_N)) 
        {
            return false;
            //wp_die(_e('Plugin not installed correctly','print-invoices-packing-slip-labels-for-woocommerce'));
        }
        return true;    
    }
}

if(function_exists('woocommerce_packing_list_check_necessary') && function_exists('run_wf_woocommerce_packing_list'))
{   
    if( woocommerce_packing_list_check_necessary() && (in_array( 'woocommerce/woocommerce.php',apply_filters('active_plugins',get_option('active_plugins'))) || array_key_exists( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_site_option( 'active_sitewide_plugins', array() ) ) )) ) 
    {
        run_wf_woocommerce_packing_list(); 
    }else
    {
        if(!function_exists('WC'))
        {
            add_action('admin_notices', 'wt_pklist_require_wc_admin_notice');
            function wt_pklist_require_wc_admin_notice()
            {
                ?>
                <div class="error">
                    <p><?php echo sprintf(__('%s WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels (Basic) %s is enabled but not effective. It requires %s WooCommerce %s in order to work.', 'print-invoices-packing-slip-labels-for-woocommerce'), '<b>', '</b>', '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">', '</a>'); ?></p>
                </div>
                <?php
            }
        }
    }
}
if(!function_exists('wf_woocommerce_packing_list_update_message'))
{
    function wf_woocommerce_packing_list_update_message( $data, $response )
    {
        if(isset( $data['upgrade_notice']))
        {
            add_action( 'admin_print_footer_scripts','wf_woocommerce_packing_list_plugin_screen_update_js');
            $msg=str_replace(array('<p>','</p>'),array('<div>','</div>'),$data['upgrade_notice']);
            echo '<style type="text/css">
            #print-invoices-packing-slip-labels-for-woocommerce-update .update-message p:last-child{ display:none;}     
            #print-invoices-packing-slip-labels-for-woocommerce-update ul{ list-style:disc; margin-left:30px;}
            .wf-update-message{ padding-left:30px;}
            </style>
            <div class="update-message wf-update-message">'. wpautop($msg).'</div>';
        }
    }
    add_action( 'in_plugin_update_message-print-invoices-packing-slip-labels-for-woocommerce/wf-woocommerce-packing-list.php', 'wf_woocommerce_packing_list_update_message', 10, 2 );
}
if(!function_exists('wf_woocommerce_packing_list_plugin_screen_update_js'))
{
    function wf_woocommerce_packing_list_plugin_screen_update_js()
    {
        ?>
        <script>
            ( function( $ ){
                var update_dv=$( '#print-invoices-packing-slip-labels-for-woocommerce-update');
                update_dv.find('.wf-update-message').next('p').remove();
                update_dv.find('a.update-link:eq(0)').click(function(){
                    $('.wf-update-message').remove();
                });
            })( jQuery );
        </script>
        <?php
    }
}
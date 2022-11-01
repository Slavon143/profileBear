<?php

/**
 * This class handles notices to admin
 *
 * @package   Woo_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2018 BjornTech - Finnvid Innovation AB
 */

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Notice', false)) {

    class Fortnox_Notice
    {
        public function __construct()
        {
            add_action('admin_notices', array($this, 'check_displaylist'), 100);
        }

        /**
         * Adds a message to be displayed to the admin
         *
         * @param string  $message The message to be displayed
         * @param string  $type Type of message. Valid variants are 'error' (default), 'warning', 'success', 'info'
         * @param string|boolean  $id An unique id for the message
         * @param boolean  $dismiss

         * @param boolean  $valid_to The message should be valid until. Set to false (default) if no time limit
         *
         * @return string An unique id for the message, this can be used to delete it.
         */
        public static function add($message, $type = 'error', $id = false, $dismiss = true, $valid_to = false)
        {

            if ('yes' != get_option('fortnox_disable_notices')) {

                $notices = get_site_transient('fortnox_notices');
                if (!$notices) {
                    $notices = array();
                }
                $notice = array(
                    'type' => $type,
                    'valid_to' => $valid_to === false ? false : $valid_to,
                    'message' => $message,
                    'dismissable' => $dismiss,
                );

                $id = $id === false ? uniqid() : esc_html($id);
                $notices[$id] = $notice;

                /*        if (WCFH_Util::wc_version_check('4.3.0')) {
                require_once dirname(__FILE__) . '/class-fortnox-admin-notes.php';
                \Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes_Fortnox::add_activity_panel_inbox_note($id, $message);
                }*/

                set_site_transient('fortnox_notices', $notices);
            }

            return $id;
        }

        public static function clear($id = false)
        {

            $notices = get_site_transient('fortnox_notices');

            if ($id && isset($notices[esc_html($id)])) {

                unset($notices[esc_html($id)]);
            } elseif (!$id) {

                $notices = array();
            }

            set_site_transient('fortnox_notices', $notices);
        }

        public static function get($id)
        {
            $notices = get_site_transient('fortnox_notices');
            if ($id && isset($notices[$id])) {
                return $notices[$id];
            }
            return false;
        }

        public static function display($message, $type = 'error', $id = '', $dismiss = true)
        {
            $dismissable = $dismiss ? 'is-dismissible' : '';
            $date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), date('U') + (get_option('gmt_offset') * HOUR_IN_SECONDS));
            $message = 'Fortnox: ' . $message;
            echo '<div class="fortnox_notice ' . $dismissable . ' notice notice-' . $type . ' ' . $id . '" id="' . $id . '"><p>' . $message . '</p></div>';
        }

        public function check_displaylist()
        {
            $notices = get_site_transient('fortnox_notices');

            if (false !== $notices && !empty($notices)) {
                foreach ($notices as $key => $notice) {
                    self::display($notice['message'], $notice['type'], $key, $notice['dismissable']);
                    if ($notice['valid_to'] !== false && $notice['valid_to'] < time()) {
                        unset($notices[$key]);
                    }
                }
            }

            set_site_transient('fortnox_notices', $notices);
        }
    }

    new Fortnox_Notice();
}

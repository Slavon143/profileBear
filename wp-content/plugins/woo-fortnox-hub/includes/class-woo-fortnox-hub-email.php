<?php

/**
 * Fortnox Hub Email Class
 *
 * Modifies the base WooCommerce email class and extends it to send fortnox emails.
 *
 * @package    WooCommerce Fortnox Hub
 * @subpackage Woo_Fortnox_Hub_Email
 * @category   Class
 * @author     BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Woo_Fortnox_Hub_Email', false)) {

    class Woo_Fortnox_Hub_Email
    {

        /**
         * Bootstraps the class and hooks required actions & filters.
         *
         * @since 4.4
         */
        public function __construct()
        {

            add_action('woocommerce_email_classes', __CLASS__ . '::add_emails', 10, 1);
        }

        /**
         * Add Subscriptions' email classes.
         *
         * @since 4.4
         */
        public static function add_emails($email_classes)
        {

            require_once WC_FH()->includes_dir . 'emails/class-woo-fortnox-hub-email-order-sync-problem.php';
            $email_classes['Woo_Fortnox_Hub_Email_Failed_Order_Sync'] = new Woo_Fortnox_Hub_Email_Failed_Order_Sync();

            return $email_classes;
        }
    }

    new Woo_Fortnox_Hub_Email();
}

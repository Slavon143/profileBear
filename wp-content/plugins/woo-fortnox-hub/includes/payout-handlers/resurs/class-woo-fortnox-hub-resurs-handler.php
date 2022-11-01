<?php

/**
 * Handles
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Resurs_Handler', false)) {

    class Fortnox_Hub_Resurs_Handler
    {

        public function __construct()
        {
            add_filter('fortnox_include_fee_item', array($this, 'remove_resurs_fee_item'), 10, 3);
        }

        public function remove_resurs_fee_item($return, $fee, $order)
        {

            $payment_method = $order->get_payment_method();

            if (false !== strstr($payment_method, 'resurs')) {

                if ('yes' == get_option('fortnox_amounts_excl_tax')) {
                    $price = $order->get_total();
                } else {
                    $price = $order->get_total() + $order->get_total_tax();
                }

                if (0 == $price) {
                    return false;
                }

            }

            $return;
        }

    }

    new Fortnox_Hub_Resurs_Handler();
}

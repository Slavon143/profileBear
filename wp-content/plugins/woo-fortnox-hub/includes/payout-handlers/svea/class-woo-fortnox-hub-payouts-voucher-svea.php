<?php

/**
 * Creating Supplier Vouchers from a Svea payments
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Payouts_Voucher_Svea', false)) {

    final class Fortnox_Hub_Payouts_Voucher_Svea extends Fortnox_Hub_Payouts_Voucher
    {

        public $payout_id = 'svea';

        public function __construct()
        {
            parent::__construct();
        }

    }

}

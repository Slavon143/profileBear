<?php

/**
 * Creating Supplier Vouchers from a Clearhaus payments
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Payouts_Voucher_Clearhaus', false)) {

    final class Fortnox_Hub_Payouts_Voucher_Clearhaus extends Fortnox_Hub_Payouts_Voucher
    {

        public $payout_id = 'clearhaus';

        public function __construct()
        {
            parent::__construct();
        }

    }

}

<?php

/**
 * Creating Supplier Vouchers from a Klarna payments
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Payouts_Voucher_Klarna', false)) {

    final class Fortnox_Hub_Payouts_Voucher_Klarna extends Fortnox_Hub_Payouts_Voucher
    {

        public $payout_id = 'klarna';

        public function __construct()
        {
            parent::__construct();
        }

    }

}

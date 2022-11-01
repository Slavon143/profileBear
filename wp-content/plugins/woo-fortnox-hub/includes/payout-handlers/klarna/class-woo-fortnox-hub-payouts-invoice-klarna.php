<?php

/**
 * Creating Supplier invoices from a Klarna payments
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Payouts_Invoice_Klarna', false)) {

    final class Fortnox_Hub_Payouts_Invoice_Klarna extends Fortnox_Hub_Payouts_Invoice
    {

        public $payout_id = 'klarna';

        public function __construct()
        {
            parent::__construct();
        }

    }

}

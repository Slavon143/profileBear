<?php

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Payouts', false)) {

    class Fortnox_Hub_Payouts
    {

        /**
         * The created document to be sent to Fortnox
         *
         * @var array
         */
        public $document = [];

        /**
         * The document rows to be included in the document
         *
         * @var array
         */
        public $document_rows = [];

        /**
         * Payout type specific check. This function should be overwritten in child classes if needed
         *
         * @return bool
         */
        public function payout_type_specific_check()
        {
            return true;
        }

        /**
         * Get document type
         *
         * @return void
         */
        public function document_type()
        {
            return $this->document_type;
        }

        /**
         * Starting a new document
         *
         * @return void
         */
        public function start_document()
        {
            $this->document_rows = [];
            $this->document = [];
            $this->account_debit = [];
            $this->account_credit = [];
            $this->account_transaction_information = [];
            $this->account_description = [];
            $this->account_price = [];
        }

    }

}

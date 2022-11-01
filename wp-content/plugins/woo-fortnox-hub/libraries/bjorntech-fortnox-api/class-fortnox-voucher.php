<?php

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_API_Voucher', false)) {

    class Fortnox_API_Voucher extends Fortnox_API_Class
    {
        protected $resource = 'Voucher';
        protected $resource_url = 'vouchers';
        protected $keys = [

        ];
        protected $readonly_keys = [
            '@url',
            'ReferenceNumber',
            'ReferenceType',
            'VoucherNumber',
            'Year',
            'ApprovalState',
        ];

        /**
         * Gets Direct URL to the record.
         *
         * @return string
         */
        public function getUrl()
        {
            return $this->get_data('@url');
        }

        /**
         * Gets Comments of the voucher.
         *
         * @return string
         */
        public function getComments()
        {
            return $this->get_data('Comments');
        }

        /**
         * Gets Code of the cost center. The code must be of an existing cost center.
         *
         * @return string
         */
        public function getCostCenter()
        {
            return $this->get_data('CostCenter');
        }

        /**
         * Gets Description of the voucher.
         *
         * @return string
         */
        public function getDescription()
        {
            return $this->get_data('Description');
        }

        /**
         * Gets Code of the project. The code must be of an existing project.
         *
         * @return string
         */
        public function getProject()
        {
            return $this->get_data('Project');
        }

        /**
         * Gets Reference number, for example an invoice number.
         *
         * @return string
         */
        public function getReferenceNumber()
        {
            return $this->get_data('ReferenceNumber');
        }

        /**
         * Gets Reference type. Can be INVOICE SUPPLIERINVOICE INVOICEPAYMENT SUPPLIERPAYMENT MANUAL CASHINVOICE or ACCRUAL
         *
         * @return string
         */
        public function getReferenceType()
        {
            return $this->get_data('ReferenceType');
        }

        /**
         * Gets Date of the transaction. Must be a valid date according to our date format.
         *
         * @return date
         */
        public function getTransactionDate()
        {
            return $this->get_data('TransactionDate');
        }

        /**
         * Gets Number of the voucher
         *
         * @return integer
         */
        public function getVoucherNumber()
        {
            return $this->get_data('VoucherNumber');
        }

        /**
         * Gets The properties for the object in this array is listed in the table for “Voucher Rows”.
         *
         * @return array
         */
        public function getVoucherRows()
        {
            return $this->get_data('VoucherRows');
        }

        /**
         * Gets Code of the voucher series. The code must be of an existing voucher series.
         *
         * @return string
         */
        public function getVoucherSeries()
        {
            return $this->get_data('VoucherSeries');
        }

        /**
         * Gets Id of the year of the voucher.
         *
         * @return integer
         */
        public function getYear()
        {
            return $this->get_data('Year');
        }

        /**
         * Gets The approval state f the voucher.
         * Not for approval: 0
         * Not ready for approval: 1
         * Not approved: 2
         * Approved: 3
         *
         * @return integer
         */
        public function getApprovalState()
        {
            return $this->get_data('ApprovalState');
        }

        /**
         * Sets Comments of the voucher.
         *
         * @var string $Comments
         * @return void
         */
        public function setComments($Comments = null)
        {
            $this->set_data('Comments', $Comments);
        }

        /**
         * Sets Code of the cost center. The code must be of an existing cost center.
         *
         * @var string $CostCenter
         * @return void
         */
        public function setCostCenter($CostCenter = null)
        {
            $this->set_data('CostCenter', $CostCenter);
        }

        /**
         * Sets Description of the voucher.
         *
         * @var string $Description
         * @return void
         */
        public function setDescription($Description = null)
        {
            $this->set_data('Description', $Description);
        }

        /**
         * Sets Code of the project. The code must be of an existing project.
         *
         * @var string $Project
         * @return void
         */
        public function setProject($Project = null)
        {
            $this->set_data('Project', $Project);
        }

        /**
         * Sets Date of the transaction. Must be a valid date according to our date format.
         *
         * @var date $TransactionDate
         * @return void
         */
        public function setTransactionDate($TransactionDate = null)
        {
            $this->set_data('TransactionDate', $TransactionDate);
        }

        /**
         * Sets The properties for the object in this array is listed in the table for “Voucher Rows”.
         *
         * @var array $VoucherRows
         * @return void
         */
        public function setVoucherRows(array $VoucherRows = null)
        {
            $this->set_data('VoucherRows', $VoucherRows);
        }

        public function addVoucherRow(Fortnox_Voucher_Row $VoucherRow)
        {

        }

        /**
         * Sets Code of the voucher series. The code must be of an existing voucher series.
         *
         * @var string $VoucherSeries
         * @return void
         */
        public function setVoucherSeries($VoucherSeries = null)
        {
            $this->set_data('VoucherSeries', $VoucherSeries);
        }

    }
}

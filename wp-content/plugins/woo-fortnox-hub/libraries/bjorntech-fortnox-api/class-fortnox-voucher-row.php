<?php

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Voucher', false)) {
    class Fortnox_Voucher_Row extends Fortnox_API_Class
    {

        public function __construct($data)
        {
            parent::__construct($data);
        }

        /**
         * Account number. The number must be of an existing active account.
         *
         * @return integer, 4 digits
         */
        public function getAccount()
        {
            return $this->get_data('Account');
        }

        /**
         * Code of the cost center. The code must be of an existing cost center.
         *
         * @return string
         */
        public function getCostCenter()
        {
            return $this->get_data('CostCenter');
        }

        /**
         * Amount of credit.
         *
         * @return float, 14 digits (incl. decimals)
         */
        public function getCredit()
        {
            return $this->get_data('Credit');
        }

        /**
         * The description of the account.
         *
         * @return string, read-only
         */
        public function getDescription()
        {
            return $this->get_data('Description');
        }

        /**
         * Amount of debit.
         *
         * @return float, 14 digits (incl. decimals)
         */
        public function getDebit()
        {
            return $this->get_data('Debit');
        }

        /**
         * Code of the project. The code must be of an existing project.
         *
         * @return string
         */
        public function getProject()
        {
            return $this->get_data('Project');
        }

        /**
         * If the row is marked as removed.
         *
         * @return boolean, read-only
         */
        public function getRemoved()
        {
            return $this->get_data('Removed');
        }

        /**
         * Transaction information regarding the row.
         *
         * @return string, 100 characters
         */
        public function getTransactionInformation()
        {
            return $this->get_data('TransactionInformation');
        }

        /**
         * Account number. The number must be of an existing active account.
         *
         * @param mixed $Account
         * @return void
         */
        public function setAccount($Account = null)
        {
            $this->set_data('Account', $Account);
        }

        /**
         * Code of the cost center. The code must be of an existing cost center.
         *
         * @param mixex $CostCenter
         * @return void
         */
        public function setCostCenter($CostCenter = null)
        {
            $this->set_data('CostCenter', $CostCenter);
        }

        /**
         * Amount of credit.
         *
         * @param mixed $Credit
         * @return void
         */
        public function setCredit($Credit = null)
        {
            $this->set_data('Credit', $Credit);
        }

        /**
         * Amount of debit.
         *
         * @param mixed $Debit
         * @return void
         */
        public function setDebit($Debit = null)
        {
            $this->set_data('Debit', $Debit);
        }

        /**
         * Code of the project. The code must be of an existing project.
         *
         * @param mixed $Project
         * @return void
         */
        public function setProject($Project = null)
        {
            $this->set_data('Project', $Project);
        }

        /**
         * Transaction information regarding the row.
         *
         * @param mixed $TransactionInformation
         * @return void
         */
        public function setTransactionInformation($TransactionInformation = null)
        {
            $this->set_data('TransactionInformation', $TransactionInformation);
        }

    }

}

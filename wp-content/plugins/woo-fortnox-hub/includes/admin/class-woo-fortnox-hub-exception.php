<?php

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Exception', false)) {
    class Fortnox_Exception extends Exception
    {
        /**
         * Contains a log object instance
         * @access protected
         */
        protected $log;

        /**
         * Contains the object instance
         * @access protected
         */
        protected $request_data;

        /**
         * Contains the url
         * @access protected
         */
        protected $request_url;

        /**
         * Contains the response data
         * @access protected
         */
        protected $response_data;

        /**
         * __Construct function.
         *
         * Redefine the exception so message isn't optional
         *
         * @access public
         * @return void
         */
        public function __construct($message, $code = 0, Exception $previous = null, $request_url = '', $request_data = '', $response_data = '')
        {
            // make sure everything is assigned properly
            parent::__construct($message, $code, $previous);

            $this->log = new Woo_Fortnox_Hub_Log(false);

            $this->request_data = $request_data;
            $this->request_url = $request_url;
            $this->response_data = $response_data;
        }

        /**
         * write_to_logs function.
         *
         * Stores the exception dump in the WooCommerce system logs
         *
         * @access public
         * @return void
         */
        public function write_to_logs($function = false)
        {
            $this->log->separator();
            if ($function) {
                $this->log->add('Fortnox Exception function: ' . $function);
            }
            $this->log->add('Fortnox Exception file: ' . $this->getFile());
            $this->log->add('Fortnox Exception line: ' . $this->getLine());
            $this->log->add('Fortnox Exception code: ' . $this->getCode());
            $this->log->add('Fortnox Exception message: ' . $this->getMessage());
            $this->log->separator();
        }

        /**
         * write_standard_warning function.
         *
         * Prints out a standard warning
         *
         * @access public
         * @return void
         */
        public function write_standard_warning()
        {
            printf(
                wp_kses(
                    __("An error occured. For more information check out the <strong>%s</strong> logs inside <strong>WooCommerce -> System Status -> Logs</strong>.", 'woo-fortnox-hub'),
                    array('strong' => array())
                ),
                $this->log->get_domain()
            );
        }
    }
}

if (!class_exists('Fortnox_API_Exception', false)) {
    class Fortnox_API_Exception extends Fortnox_Exception
    {
        /**
         * write_to_logs function.
         *
         * Stores the exception dump in the WooCommerce system logs
         *
         * @access public
         * @return void
         */
        public function write_to_logs($function = false)
        {
            $this->log->separator();
            if ($function) {
                $this->log->add('Fortnox Exception function: ' . $function);
            }
            $this->log->add('Fortnox API Exception file: ' . $this->getFile());
            $this->log->add('Fortnox API Exception line: ' . $this->getLine());
            $this->log->add('Fortnox API Exception code: ' . $this->getCode());
            $this->log->add('Fortnox API Exception message: ' . $this->getMessage());

            if (!empty($this->request_url)) {
                $this->log->add('Fortnox API Exception Request URL: ' . $this->request_url);
            }

            if (!empty($this->request_data)) {
                $this->log->add('Fortnox API Exception Request DATA: ' . is_array($this->request_data) ? print_r($this->request_data, true) : $this->request_data);
            }

            if (!empty($this->response_data)) {
                $this->log->add('Fortnox API Exception Response DATA: ' . is_array($this->response_data) ? print_r($this->response_data, true) : $this->response_data);
            }

            $this->log->separator();
        }
    }
}

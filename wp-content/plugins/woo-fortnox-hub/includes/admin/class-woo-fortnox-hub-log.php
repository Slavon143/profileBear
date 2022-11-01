<?php
/**
 * Woo_Fortnox_Log class
 *
 * @package  Woocommerce_Fortnox/Classes
 * @category Logs
 */

defined('ABSPATH') || exit;

if (!class_exists('Woo_Fortnox_Hub_Log', false)) {

    class Woo_Fortnox_Hub_Log
    {

        /**
         * The domain handler used to name the log
         *
         * @access private
         */
        private $_domain = 'woo-fortnox-hub';

        /**
         * The WC_Logger instance
         *
         * @access private
         */
        private $_logger;

        /**
         * Internal storage of the silent flag
         *
         * @access private
         */
        private $_silent;

        /**
         * Class constructor
         *
         * @access public
         * @param bool $silent Set to true if logging should not be done.
         * @return void
         */
        public function __construct($silent = false)
        {
            $this->_logger = new WC_Logger();
            $this->_silent = $silent;
        }

        /**
         * Add a message to the logfile
         *
         * Uses the build in logging method in WooCommerce.
         * Logs are available inside the System status tab
         *
         * @access public
         * @param  string|array|object
         * @param  bool $override_silent Set to true if the log message should be printed dispite the logging being in silent mode
         * @return void
         */
        public function add($message, $override_silent = false)
        {
            if (!$this->_silent || true === $override_silent) {
                if (is_array($message)) {
                    $message = print_r($message, true);
                }

                $this->_logger->add($this->get_domain(), getmypid() . ' - ' . $message);
            }
        }

        /**
         *
         * Clear the entire log file
         *
         * @access public
         * @return void
         */
        public function clear()
        {
            return $this->_logger->clear($this->_domain);
        }

        /**
         * separator function.
         *
         * Inserts a separation line for better overview in the logs.
         *
         * @access public
         * @return void
         */
        public function separator()
        {
            $this->add('--------------------------------------------------');
        }

        /**
         *
         * Returns the log text domain
         *
         * @access public
         * @return string
         */
        public function get_domain()
        {
            return $this->_domain;
        }

        /**
         * Returns a link to the log files in the WP backend.
         */
        public function get_admin_link()
        {
            $log_path = wc_get_log_file_path($this->_domain);
            $log_path_parts = explode('/', $log_path);
            return add_query_arg(array(
                'page' => 'wc-status',
                'tab' => 'logs',
                'log_file' => end($log_path_parts),
            ), admin_url('admin.php'));
        }

    }

}

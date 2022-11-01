<?php
if (!class_exists('VG_Freemium_Plugin_SDK')) {

	/**
	 * Display the post types item in the toolbar to tease users of the free 
	 * version into purchasing the premium plugin.
	 */
	class VG_Freemium_Plugin_SDK {

		var $settings = array();
		var $version = '1.0.0';
		var $textname = 'vg_freemium_plugin_sdk';

		function get_plugin_install_url($plugin_slug) {
			$install_plugin_base_url = ( is_multisite() ) ? network_admin_url() : admin_url();
			$install_plugin_url = add_query_arg(array(
				's' => $plugin_slug,
				'tab' => 'search',
				'type' => 'term'
					), $install_plugin_base_url . 'plugin-install.php');
			return $install_plugin_url;
		}

		function _generate_random_string($length = 10) {
			return substr(str_shuffle(str_repeat($x = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
		}

		function __construct($args = array()) {
			if (!is_admin()) {
				return;
			}

			$args = wp_parse_args($args, array(
				'main_plugin_file' => '',
				'show_welcome_page' => true,
				'welcome_page_file' => '',
				'upgrade_message_file' => '',
				'logo' => '',
				'buy_link' => '',
				'buy_link_text' => __('Buy extension now', $this->textname),
				'plugin_name' => '',
				'plugin_prefix' => $this->_generate_random_string(5),
				'settings_page_url' => '',
				'show_whatsnew_page' => true,
				'whatsnew_pages_directory' => '',
				'email_optin_form_file' => '',
				'plugin_version' => '',
				'plugin_options' => '',
				'website' => '',
			));
			if (empty($args['website'])) {
				$args['website'] = $args['buy_link'];
			}
			$this->settings = $args;

			add_action('admin_init', array($this, 'redirect_to_welcome_page'));
			add_action('admin_menu', array($this, 'register_menu'));
			add_action('admin_init', array($this, 'redirect_to_whats_new_page'));
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
			register_activation_hook($this->settings['main_plugin_file'], array($this, 'enable_welcome_page'));
		}

		function admin_enqueue_scripts($hook) {

			$allowed_pages = array(
				$this->settings['plugin_prefix'] . 'whats_new',
				$this->settings['plugin_prefix'] . 'welcome_page'
			);

			$allowed = false;
			foreach ($allowed_pages as $allowed_page) {
				if (strpos($hook, $allowed_page) !== false) {
					$allowed = true;
					break;
				}
			}

			if (!$allowed) {
				return;
			}
			$this->_enqueue_assets();
		}

		function _enqueue_assets() {

			wp_enqueue_style('vg-plugin-sdk-styles', plugins_url('/', __FILE__) . 'assets/css/styles.css', '', $this->version, 'all');
		}

		/**
		 * Redirect to "whats new" page after plugin update
		 */
		function redirect_to_whats_new_page() {

			// bail if settings are empty = fresh install
			if (empty($this->settings['plugin_options'])) {
				return;
			}

			// Bail if activating from network, or bulk
			if (is_network_admin() || isset($_GET['activate-multi'])) {
				return;
			}


			$file_path = trailingslashit($this->settings['whatsnew_pages_directory']) . $this->settings['plugin_version'] . '.php';
			$flag_key = $this->settings['plugin_prefix'] . 'hide_whats_new_' . $this->settings['plugin_version'];

			// bail if there aren´t new features for this release			
			if (!file_exists($file_path)) {
				return;
			}

			// exit if the page was already showed
			if (get_option($flag_key)) {
				return;
			}

			// Delete the redirect transient
			update_option($flag_key, 'yes');

			wp_redirect(add_query_arg(array('page' => $this->settings['plugin_prefix'] . 'whats_new'), admin_url('admin.php')));
			exit();
		}

		function register_menu() {
			add_submenu_page(null, $this->settings['plugin_name'], $this->settings['plugin_name'], 'manage_options', $this->settings['plugin_prefix'] . 'welcome_page', array($this, 'render_welcome_page'));

			add_submenu_page(null, $this->settings['plugin_name'], $this->settings['plugin_name'], 'manage_options', $this->settings['plugin_prefix'] . 'whats_new', array($this, 'render_whats_new_page'));
		}

		/**
		 * Render quick setup page
		 */
		function render_welcome_page() {
			if (!current_user_can('manage_options')) {
				wp_die(__('You dont have enough permissions to view this page.', $this->textname));
			}

			do_action('vg_plugin_sdk/welcome_page/before_rendering', $this->settings);
			$page_id = 'welcome-page';
			if (!empty($this->settings['welcome_page_file']) && file_exists($this->settings['welcome_page_file'])) {
				ob_start();
				include $this->settings['welcome_page_file'];
				$content = ob_get_clean();
			}

			if (!empty($this->settings['upgrade_message_file']) && file_exists($this->settings['upgrade_message_file'])) {
				ob_start();
				include $this->settings['upgrade_message_file'];
				$upgrade_message = ob_get_clean();
			}

			if (!empty($this->settings['email_optin_form_file']) && file_exists($this->settings['email_optin_form_file'])) {
				ob_start();
				include $this->settings['email_optin_form_file'];
				$email_optin_form = ob_get_clean();
				$content = str_replace('</ol>', '<li>' . $email_optin_form . '</li></ol>', $content);
			}

			require 'views/page-template.php';
		}

		/**
		 * Render "whats new" page
		 */
		function render_whats_new_page() {
			if (!current_user_can('manage_options')) {
				wp_die(__('You dont have enough permissions to view this page.', $this->textname));
			}

			$page_id = 'whatsnew-page';
			$file_path = trailingslashit($this->settings['whatsnew_pages_directory']) . $this->settings['plugin_version'] . '.php';
			ob_start();
			include $file_path;

			$items = apply_filters('vg_plugin_sdk/whats_new_page/items', $items, $this->settings['plugin_prefix']);

			if (!empty($items)) {
				echo '<ol class="steps">';
				foreach ($items as $key => $step_content) {
					?>
					<li><?php echo wp_kses_post($step_content); ?></li>		
					<?php
				}

				echo '</ol>';
			}
			$content = ob_get_clean();


			ob_start();
			include $this->settings['upgrade_message_file'];
			$upgrade_message = ob_get_clean();

			require 'views/page-template.php';
		}

		function enable_welcome_page() {
			$flag_key = $this->settings['plugin_prefix'] . 'welcome_redirect';
			update_option($flag_key, '');
		}

		/**
		 * Redirect to welcome page after plugin activation
		 */
		function redirect_to_welcome_page() {
			// Bail if no activation redirect
			$flag_key = $this->settings['plugin_prefix'] . 'welcome_redirect';

			$flag = get_option($flag_key, '');
			if ($flag === 'no') {
				return;
			}

			// Delete the redirect transient
			update_option($flag_key, 'no');

			// Bail if activating from network, or bulk
			if (is_network_admin() || isset($_GET['activate-multi'])) {
				return;
			}

			do_action('vg_plugin_sdk/before_welcome_page_redirect', $this->settings);
			wp_redirect(add_query_arg(array('page' => $this->settings['plugin_prefix'] . 'welcome_page'), admin_url('admin.php')));
			exit();
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}
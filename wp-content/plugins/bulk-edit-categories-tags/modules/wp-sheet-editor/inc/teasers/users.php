<?php
if (!class_exists('WP_Sheet_Editor_Users_Teaser')) {

	/**
	 * Display users item in the toolbar to tease users of the free 
	 * version into purchasing the premium plugin.
	 */
	class WP_Sheet_Editor_Users_Teaser {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {

			if (class_exists('WP_Sheet_Editor_Users') || !is_admin()) {
				return;
			}
			add_action('admin_notices', array($this, 'render_notice'));
			add_filter('vg_sheet_editor/prepared_post_types', array($this, 'add_users_lite_to_quick_setup'));
		}

		function add_users_lite_to_quick_setup($sheets) {
			if (wp_doing_ajax() || !is_admin() || isset($sheets['user'])) {
				return $sheets;
			}
			$install_url = VGSE()->get_plugin_install_url('Bulk Edit and Create User Profiles – WP Sheet Editor');
			$sheets['user'] = array(
				'key' => 'user',
				'label' => __('Users'),
				'is_disabled' => true,
				'description' => '<small><a href="' . esc_url($install_url) . '" target="_blank">' . __('(Install free extension)', VGSE()->textname) . '</a></small>',
			);
			return $sheets;
		}

		function render_notice() {
			$screen = get_current_screen();
			if ($screen->parent_base !== 'users') {
				return;
			}
			$notice_key = 'wpse_hide_users_teaser_notice';
			if (get_option($notice_key)) {
				return;
			}
			$nonce = wp_create_nonce('bep-nonce');
			?>
			<div class="notice notice-success is-dismissible wpse-notice" data-key="<?php echo esc_attr($notice_key); ?>">
				<p><?php printf(__('<b>Tip from WP Sheet Editor:</b> You can view all the users in a table, view thousands of full profiles, edit hundreds of users at once without crashing your server, view all shipping/billing/buddypress information. Make advanced searches, create hundreds of users, and more. <a href="%s" target="_blank">Download Plugin</a>', VGSE()->textname), 'https://wpsheeteditor.com/extensions/edit-users-spreadsheet/?utm_source=wp-admin&utm_medium=admin-notice&utm_campaign=users'); ?></p>
			</div>
			<script>
				jQuery(window).on('load', function () {
					jQuery('.wpse-notice .notice-dismiss').click(function () {
						console.log('click');
						jQuery.post(ajaxurl, {
							action: 'vgse_notice_dismiss',
							key: jQuery(this).parent().data('key'),
							nonce: <?php echo json_encode(esc_attr($nonce)); ?>
						});
					});
				});
			</script>
			<?php
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * 
		 */
		static function get_instance() {
			if (null == WP_Sheet_Editor_Users_Teaser::$instance) {
				WP_Sheet_Editor_Users_Teaser::$instance = new WP_Sheet_Editor_Users_Teaser();
				WP_Sheet_Editor_Users_Teaser::$instance->init();
			}
			return WP_Sheet_Editor_Users_Teaser::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}


add_action('vg_sheet_editor/initialized', 'vgse_init_users_teaser');

if (!function_exists('vgse_init_users_teaser')) {

	function vgse_init_users_teaser() {
		WP_Sheet_Editor_Users_Teaser::get_instance();
	}

}

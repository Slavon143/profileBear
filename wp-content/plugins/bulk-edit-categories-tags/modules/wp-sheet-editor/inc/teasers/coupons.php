<?php
if (!class_exists('WP_Sheet_Editor_Coupons_Teaser')) {

	/**
	 * Display coupons item in the toolbar to tease users of the free 
	 * version into purchasing the premium plugin.
	 */
	class WP_Sheet_Editor_Coupons_Teaser {

		static private $instance = false;
		var $post_type = 'shop_coupon';

		private function __construct() {
			
		}

		function init() {

			if (class_exists('WP_Sheet_Editor_WC_Coupons')) {
				return;
			}
			add_action('admin_notices', array($this, 'render_notice'));
			add_filter('vg_sheet_editor/prepared_post_types', array($this, 'add_lite_version_to_quick_setup'));
		}

		function add_lite_version_to_quick_setup($sheets) {
			if (wp_doing_ajax() || !is_admin() || isset($sheets[$this->post_type]) || !post_type_exists($this->post_type)) {
				return $sheets;
			}
			$install_url = VGSE()->get_plugin_install_url('WooCommerce Bulk Edit Coupons - WP Sheet Editor');
			$sheets[$this->post_type] = array(
				'key' => $this->post_type,
				'label' => __('WooCommerce Coupons', VGSE()->textname),
				'is_disabled' => true,
				'description' => '<small><a href="' . esc_url($install_url) . '" target="_blank">' . __('(Install free extension)', VGSE()->textname) . '</a></small>',
			);
			return $sheets;
		}

		function render_notice() {
			if (empty($_GET['post_type']) || $_GET['post_type'] !== 'shop_coupon') {
				return;
			}
			$notice_key = 'wpse_hide_coupons_teaser_notice';
			if (get_option($notice_key)) {
				return;
			}
			$nonce = wp_create_nonce('bep-nonce');
			?>
			<div class="notice notice-success is-dismissible wpse-notice" data-key="<?php echo esc_attr($notice_key); ?>">
				<p><?php printf(__('Edit Coupons in a Spreadsheet.<br/>Edit coupon codes, amounts, status, restrictions, and more. Make advanced searches. The spreadsheet is in sync with your site, no need to import/export. <a href="%s" class="" target="_blank">Download Plugin</a>', VGSE()->textname), 'https://wpsheeteditor.com/extensions/woocommerce-coupons-spreadsheet/?utm_source=wp-admin&utm_medium=admin-notice&utm_campaign=coupons'); ?></p>
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
			if (null == WP_Sheet_Editor_Coupons_Teaser::$instance) {
				WP_Sheet_Editor_Coupons_Teaser::$instance = new WP_Sheet_Editor_Coupons_Teaser();
				WP_Sheet_Editor_Coupons_Teaser::$instance->init();
			}
			return WP_Sheet_Editor_Coupons_Teaser::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}


add_action('vg_sheet_editor/initialized', 'vgse_init_coupons_teaser');

if (!function_exists('vgse_init_coupons_teaser')) {

	function vgse_init_coupons_teaser() {
		WP_Sheet_Editor_Coupons_Teaser::get_instance();
	}

}

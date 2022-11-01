<?php

if (!class_exists('VGSE_Columns_Resizing')) {

	class VGSE_Columns_Resizing {

		static private $instance = false;
		var $db_key = 'vgse_column_sizes';

		private function __construct() {
			
		}

		function init() {

			add_action('vg_sheet_editor/after_enqueue_assets', array($this, 'enqueue_assets'), 99);
			add_filter('vg_sheet_editor/handsontable/custom_args', array($this, 'allow_column_resize'));
			add_action('wp_ajax_vgse_save_manual_column_resize', array($this, 'save_manual_column_resize'));

			// CORE >= v2.0.0
			add_filter('vg_sheet_editor/columns/provider_items', array($this, 'filter_columns_settings'), 20, 2);

			// CORE < v2.0.0
			add_filter('vg_sheet_editor/columns/post_type_items', array($this, 'filter_columns_settings'), 20, 2);
		}

		/**
		 * Modify spreadsheet columns settings.
		 * 
		 * Add custom column sizes.
		 * @param array $spreadsheet_columns
		 * @param string $post_type
		 * @param bool $exclude_formatted_settings
		 * @return array
		 */
		function filter_columns_settings($spreadsheet_columns, $post_type) {


			$option = get_user_meta(get_current_user_id(), $this->db_key, true);

			if (empty($option) || empty($option[$post_type])) {
				return $spreadsheet_columns;
			}

			foreach ($option[$post_type] as $column_key => $column_width) {
				if (!isset($spreadsheet_columns[$column_key])) {
					continue;
				}
				$spreadsheet_columns[$column_key]['column_width'] = (int) $column_width;
			}

			return $spreadsheet_columns;
		}

		function enqueue_assets() {
			wp_enqueue_script('vgse-columns-resizing-init', plugins_url('/assets/js/init.js', __FILE__), array('bep_init_js'), VGSE()->version, false);
		}

		function save_manual_column_resize() {

			if (empty($_REQUEST['nonce']) || empty($_REQUEST['post_type']) || !is_array($_REQUEST['sizes']) || !wp_verify_nonce($_REQUEST['nonce'], 'bep-nonce') || !VGSE()->helpers->user_can_view_post_type($_REQUEST['post_type'])) {
				wp_send_json_error(array('message' => __('You dont have enough permissions to view this page.', VGSE()->textname)));
			}

			$option = get_user_meta(get_current_user_id(), $this->db_key, true);

			if (empty($option)) {
				$option = array();
			}

			$post_type = VGSE()->helpers->sanitize_table_key($_REQUEST['post_type']);
			$sizes = array();
			foreach ($_REQUEST['sizes'] as $column_key => $size) {
				if ($size > 0) {
					$sizes[sanitize_text_field($column_key)] = (int) $size;
				}
			}

			$option[$post_type] = $sizes;

			update_user_meta(get_current_user_id(), $this->db_key, $option);

			wp_send_json_success();
		}

		function allow_column_resize($args) {
			$args['manualColumnResize'] = true;
			return $args;
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @return  Foo A single instance of this class.
		 */
		static function get_instance() {
			if (null == VGSE_Columns_Resizing::$instance) {
				VGSE_Columns_Resizing::$instance = new VGSE_Columns_Resizing();
				VGSE_Columns_Resizing::$instance->init();
			}
			return VGSE_Columns_Resizing::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

	add_action('vg_sheet_editor/initialized', 'vgse_columns_resizing_init');

	function vgse_columns_resizing_init() {
		VGSE_Columns_Resizing::get_instance();
	}

}
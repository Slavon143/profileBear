<?php

if (!class_exists('WP_Sheet_Editor_Custom_Columns_Teaser')) {

	/**
	 * Display custom_columns item in the toolbar to tease users of the free 
	 * version into purchasing the premium plugin.
	 */
	class WP_Sheet_Editor_Custom_Columns_Teaser {

		static private $instance = false;
		var $found_columns = array();

		private function __construct() {
			
		}

		function init() {
			if (!is_admin()) {
				return;
			}

			if (class_exists('WP_Sheet_Editor_Custom_Columns')) {
				return;
			}

			add_action('vg_sheet_editor/editor/before_init', array($this, 'register_columns'));
		}

		function _convert_key_to_label($input) {
			preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
			$ret = $matches[0];
			foreach ($ret as &$match) {
				$match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
			}
			return ucwords(trim(implode(' ', $ret)));
		}

		/**
		 * Register spreadsheet columns
		 */
		function register_columns($editor) {
			$post_type = $editor->args['provider'];
			$meta_keys = apply_filters('vg_sheet_editor/custom_columns/all_meta_keys', VGSE()->helpers->get_all_meta_keys($post_type, 1000), $post_type, $editor);

			$this->found_columns[$post_type] = array();

			foreach ($meta_keys as $meta_key) {
				if ($editor->args['columns']->has_item($meta_key, $post_type)) {
					continue;
				}
				$label = $this->_convert_key_to_label($meta_key);
				$this->found_columns[$post_type][$label] = $meta_key;

				$is_locked = apply_filters('vg_sheet_editor/custom_columns/teaser/allow_to_lock_column', true, $meta_key);
				$column_args = array();
				if ($is_locked) {
					$column_args = array(
						'unformatted' => array('renderer' => 'html', 'readOnly' => true),
						'formatted' => array('renderer' => 'html', 'readOnly' => true),
						'allow_to_save' => false,
						'is_locked' => $is_locked,
						'lock_template_key' => 'lock_cell_template_pro',
					);
				}
				$editor->args['columns']->register_item($meta_key, $post_type, array_merge(array(
					'data_type' => 'meta_data',
					'column_width' => (6.1 * strlen($label)) + 75, // Set the width based on the label length+the locked icon length
					'title' => $label,
					'type' => '',
					'supports_formulas' => true,
					'allow_to_hide' => true,
					'allow_to_rename' => true,
								), $column_args));
			}
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * 
		 */
		static function get_instance() {
			if (null == WP_Sheet_Editor_Custom_Columns_Teaser::$instance) {
				WP_Sheet_Editor_Custom_Columns_Teaser::$instance = new WP_Sheet_Editor_Custom_Columns_Teaser();
				WP_Sheet_Editor_Custom_Columns_Teaser::$instance->init();
			}
			return WP_Sheet_Editor_Custom_Columns_Teaser::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

add_action('vg_sheet_editor/initialized', 'vgse_init_custom_columns_teaser');

if (!function_exists('vgse_init_custom_columns_teaser')) {

	function vgse_init_custom_columns_teaser() {
		return WP_Sheet_Editor_Custom_Columns_Teaser::get_instance();
	}

}	
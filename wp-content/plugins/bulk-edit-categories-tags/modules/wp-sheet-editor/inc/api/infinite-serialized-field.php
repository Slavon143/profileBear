<?php

if (!class_exists('WP_Sheet_Editor_Infinite_Serialized_Field')) {

	class WP_Sheet_Editor_Infinite_Serialized_Field {

		var $settings = array();
		var $column_keys = array();

		function __construct($settings = array()) {
			if (!empty(VGSE()->options['be_disable_serialized_columns']) || !apply_filters('vg_sheet_editor/serialized_addon/is_enabled', true)) {
				return;
			}
			$defaults = array(
				'prefix' => 'seis_',
				'column_settings' => array()
			);
			$this->settings = apply_filters('vg_sheet_editor/infinite_serialized_column/settings', wp_parse_args($settings, $defaults));

			$this->settings['prefix'] = $this->settings['sample_field_key'] . '_';
			$this->column_keys = array_keys($this->get_column_keys());

			// Priority 20 to allow to instantiate from another editor/before_init function
			add_action('vg_sheet_editor/editor/before_init', array($this, 'register_columns'), 20);
			if (!empty($this->settings['allow_in_wc_product_variations'])) {
				add_filter('vg_sheet_editor/woocommerce/variation_columns', array($this, 'allow_in_variations'));
			}
		}

		function allow_in_variations($variation_columns) {
			$variation_columns = array_merge($variation_columns, $this->column_keys);
			return $variation_columns;
		}

		function array_to_dot($myArray) {
			$ritit = new RecursiveIteratorIterator(new RecursiveArrayIterator($myArray));
			$result = array();
			foreach ($ritit as $leafValue) {
				$keys = array();
				foreach (range(0, $ritit->getDepth()) as $depth) {
					$keys[] = $ritit->getSubIterator($depth)->key();
				}
				$result[join('.', $keys)] = $leafValue;
			}
			return $result;
		}

		function get_column_keys() {
			$master = $this->settings['sample_field'];
			$fields = $this->array_to_dot($master);

			$out = array();
			foreach ($fields as $key => $value) {
				$out[$this->settings['prefix'] . $key] = $value;
			}
			return $out;
		}

		function register_columns($editor) {


			$post_types = array_intersect($this->settings['allowed_post_types'], $editor->args['enabled_post_types']);
			if (empty($post_types)) {
				return;
			}

			$fields = $this->column_keys;

			foreach ($post_types as $post_type) {

				if (method_exists($editor->args['columns'], 'columns_limit_reached') && $editor->args['columns']->columns_limit_reached($post_type)) {
					$editor->args['columns']->add_rejection($this->settings['sample_field_key'], 'columns_limit_reached', $post_type);
					break;
				}
				foreach ($fields as $field_key) {

					if (method_exists($editor->args['columns'], 'columns_limit_reached') && $editor->args['columns']->columns_limit_reached($post_type)) {
						$editor->args['columns']->add_rejection($this->settings['sample_field_key'], 'columns_limit_reached', $post_type);
						break;
					}

					$column_key = str_replace('.', '=', $field_key);
					$title = vgse_custom_columns_init()->_convert_key_to_label(str_replace(array($this->settings['prefix'], '='), array($this->settings['prefix'] . ': ', ' : '), $column_key));
					$editor->args['columns']->register_item($column_key, $post_type, apply_filters('vg_sheet_editor/infinite_serialized_column/column_settings', array_merge(array(
						'key' => $column_key,
						'data_type' => 'meta_data',
						'column_width' => 300,
						'title' => $title,
						'type' => '',
						'get_value_callback' => array($this, 'get_value'),
						'save_value_callback' => array($this, 'save_value'),
						'supports_formulas' => true,
						'supports_sql_formulas' => false,
						'allow_to_hide' => true,
						'allow_to_rename' => true,
						'infinite_serialized_handler' => true,
						'serialized_field_type' => 'infinite',
						'allow_direct_search' => false,
						'allow_search_during_import' => false,
						'allow_for_variations' => !empty($this->settings['allow_in_wc_product_variations']),
						'serialized_field_original_key' => $this->settings['sample_field_key']
											), $this->settings['column_settings']), $this, $post_type));
				}
			}
		}

		public function get($array, $key, $default = null) {
			if (is_null($key)) {
				return $array;
			}

			if (isset($array[$key])) {
				return $array[$key];
			}

			foreach (explode('.', $key) as $segment) {
				if (!is_array($array) ||
						!array_key_exists($segment, $array)) {
					return $default;
				}

				$array = $array[$segment];
			}

			return $array;
		}

		public function set(&$array, $key, $value) {
			if (is_null($key)) {
				return $array = $value;
			}

			$keys = explode('.', $key);

			while (count($keys) > 1) {
				$key = array_shift($keys);

				if (!isset($array[$key]) || !is_array($array[$key])) {
					$array[$key] = array();
				}

				$array = & $array[$key];
			}

			$array[array_shift($keys)] = $value;

			return $array;
		}

		function get_dot_notation_key($key) {
			$prefix = $this->settings['prefix'];
			if (substr($key, 0, strlen($prefix)) == $prefix) {
				$key = substr($key, strlen($prefix));
			}
			return $key;
		}

		function get_existing_value($post_id, $key) {
			return apply_filters('vg_sheet_editor/infinite_serialized_column/existing_value', VGSE()->helpers->get_current_provider()->get_item_meta($post_id, $key, true), $post_id, $key);
		}

		function update_value($post_id, $key, $value) {
			$value = apply_filters('vg_sheet_editor/infinite_serialized_column/update_value', $value, $post_id, $key);

			// Make sure that we don't save empty rows
			if (is_array($value)) {
				$first_value = current($value);
				if (is_array($first_value)) {
					foreach ($value as $index => $single_value) {
						if (!is_array($single_value)) {
							continue;
						}
						$filtered_value = array_filter($single_value);
						if (empty($filtered_value)) {
							unset($value[$index]);
						}
					}
				}
			}
			VGSE()->helpers->get_current_provider()->update_item_meta($post_id, $key, $value);
		}

		function save_value($post_id, $cell_key, $data_to_save, $post_type, $cell_args, $spreadsheet_columns) {
			$existing = $this->get_existing_value($post_id, $this->settings['sample_field_key']);
			if (empty($existing)) {
				$existing = array();
			}
			$custom_saved = apply_filters('vg_sheet_editor/infinite_serialized_column/save_value_in_full_array', null, $existing, $data_to_save, $post_id, $cell_key, $post_type, $cell_args, $spreadsheet_columns, $this);
			if (is_null($custom_saved)) {
				$data_to_save = apply_filters('vg_sheet_editor/infinite_serialized_column/save_value', $data_to_save, $post_id, $cell_key, $post_type, $cell_args, $spreadsheet_columns, $this);
				$dot_notation = $this->get_dot_notation_key(str_replace('=', '.', $cell_key));
				$this->set($existing, $dot_notation, $data_to_save);
			} else {
				$existing = $custom_saved;
			}
			$this->update_value($post_id, $this->settings['sample_field_key'], $existing);
		}

		function get_value($post, $cell_key, $cell_args) {
			$existing = $this->get_existing_value($post->ID, $this->settings['sample_field_key']);
			if (empty($existing)) {
				$existing = array();
			}
			$dot_notation = $this->get_dot_notation_key(str_replace('=', '.', $cell_key));
			$value = apply_filters('vg_sheet_editor/infinite_serialized_column/value', $this->get($existing, $dot_notation, ''), $post, $cell_key, $cell_args, $this);
			return $value;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}
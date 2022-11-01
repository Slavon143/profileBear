<?php

if (!class_exists('VGFP_SDK_Settings_Page')) {

	class VGFP_SDK_Settings_Page {

		var $sections = array();
		var $args = array();
		var $settings = array();
		var $textname = 'vg_freemium_plugin_sdk';

		function __construct($args = array()) {
			$defaults = array(
				'sections' => array(), // Array containing sections and fields using same redux framework's format
				'opt_name' => '',
				'display_name' => __('Settings', $this->textname),
				'page_permissions' => 'manage_options',
				'enable_wpmu_mode' => is_multisite(),
				'sdk' => null,
			);
			$args = wp_parse_args($args, $defaults);

			// Remove empty sections
			foreach ($args['sections'] as $section_index => $section) {
				if (empty($section) || empty($section['fields'])) {
					unset($args['section'][$section_index]);
				}
			}

			$this->sections = $args['sections'];
			$this->args = $args;
			if (empty($this->args['page_permissions'])) {
				$this->args['page_permissions'] = 'manage_options';
			}
			$this->settings = $args['sdk']->settings;

			if (empty($this->sections) || empty($args['opt_name'])) {
				return;
			}

			add_action('wp_ajax_vgfpsdk_settings_' . $args['opt_name'], array($this, 'save_settings'));
		}

		function get_setting($key = null, $default = null, $skip_filter = false) {
			if (is_multisite() && $this->args['enable_wpmu_mode']) {
				$options = get_blog_option(1, $this->args['opt_name'], array());
			}
			if (empty($options)) {
				$options = get_option($this->args['opt_name'], array());
			}

			$out = $options;
			if (!empty($key)) {
				$out = ( isset($options[$key])) ? $options[$key] : null;
			}

			if (empty($out)) {
				$out = $default;
			}

			// If the value is empty and we didn't receive an explicit default, 
			// use the default from the field definition
			if (empty($out)) {
				$fields = $this->_get_fields();
				if (isset($fields[$key]) && !empty($fields[$key]['default'])) {
					$default = $fields[$key]['default'];
					$out = $default;
				}
			}

			if (!$skip_filter) {
				$out = apply_filters('vg_plugin_sdk/settings/' . $this->args['opt_name'] . '/get_setting', $out, $key, $default);
			}

			$out = wp_unslash($out);
			return $out;
		}

		function _clean($var) {
			if (is_array($var)) {
				return array_map(array($this, '_clean'), $var);
			} else {
				return is_scalar($var) ? wp_kses_post($var) : $var;
			}
		}

		function _sanitize_field_value($value, $field) {
			switch ($field['type']) {
				case 'textarea':
					$value = sanitize_textarea_field($value);
					break;
				case 'editor':
					$value = wp_kses_post($value);
					break;
				case 'color':
					$value = sanitize_hex_color($value);
					break;
				case 'switch':
					$value = (bool) $value;
					break;
				case 'multi_text_repeater':
					if (isset($value['{index}'])) {
						unset($value['{index}']);
					}
					$value = $this->_clean($value);
					foreach ($value as $value_index => $single_value) {
						if (empty($single_value[0]) && empty($single_value[1])) {
							unset($value[$value_index]);
						}
					}
					break;
				case 'select':
				case 'checkbox':
				case 'radio':
					if (is_array($value)) {
						foreach ($value as $value_index => $single_value) {
							if (!isset($field['options'][$single_value])) {
								unset($value[$value_index]);
							}
						}
					} elseif (!isset($field['options'][$value])) {
						$value = '';
					}
					break;

				default:
					$value = sanitize_text_field($value);
					break;
			}
			if (!empty($field['validate'])) {
				switch ($field['validate']) {
					case 'url':
						$value = esc_url_raw($value);
						break;
					case 'numeric':
						$value = intval($value);
						if (!$value) {
							$value = '';
						}
						break;
					case 'email':
						$value = sanitize_email($value);
						break;
					default:
						break;
				}
			}
			return $value;
		}

		function save_settings() {
			$data = $this->_clean($_POST);
			$nonce = $data['nonce'];
			if (empty($data['nonce']) || !wp_verify_nonce($nonce, 'vgfpsdk_settings_' . $this->args['opt_name']) || !current_user_can($this->args['page_permissions'])) {
				wp_send_json_error();
			}

			$allowed_option_keys = array();
			$fields = $this->_get_fields();
			foreach ($fields as $field) {
				$allowed_option_keys[] = $field['id'];

				if (isset($data[$field['id']])) {
					$data[$field['id']] = $this->_sanitize_field_value($data[$field['id']], $field);
				}
			}
			$new_settings = array_intersect_key($data, array_flip($allowed_option_keys));

			// This is safe. We save options only inside our serialized array, so there's 
			// zero chance of editing other site options
			// and we run this only if the user can manage_options

			if (is_multisite() && $this->args['enable_wpmu_mode']) {
				$options = get_blog_option(1, $this->args['opt_name'], array());
			}
			if (empty($options)) {
				$options = get_option($this->args['opt_name'], array());
			}

			if (empty($options) || !is_array($options)) {
				$options = array();
			}
			$options = wp_parse_args($new_settings, $options);
			$options = apply_filters('vg_plugin_sdk/settings/' . $this->args['opt_name'] . '/before_saving', $options, $this->args['opt_name'], $data, $this);
			if (is_multisite() && $this->args['enable_wpmu_mode']) {
				update_blog_option(1, $this->args['opt_name'], $options);
			} else {
				update_option($this->args['opt_name'], $options);
			}
			do_action('vg_plugin_sdk/settings/' . $this->args['opt_name'] . '/after_saving', $options, $this->args['opt_name'], $data, $this);

			wp_send_json_success();
		}

		function get_sections() {
			$this->sections = apply_filters('vg_plugin_sdk/settings/' . $this->args['opt_name'] . '/options', $this->sections);
			// Redux filter is here for backwards compatibility
			$this->sections = apply_filters('redux/options/' . $this->args['opt_name'] . '/sections', $this->sections);
			return $this->sections;
		}

		function render_settings_page() {
			if (!current_user_can($this->args['page_permissions'])) {
				return;
			}

			$sections = $this->_get_sections();
			$this->args['sdk']->_enqueue_assets();
			wp_enqueue_style('wp-color-picker');
			wp_enqueue_script('wp-color-picker');
			require __DIR__ . '/views/settings-page.php';
		}

		function _get_fields() {
			$sections = $this->_get_sections();
			$fields = array();
			foreach ($sections as $section) {
				$fields = array_merge($fields, $section['fields']);
			}
			return $fields;
		}

		function _get_sections() {

			$supported_types = array('text', 'textarea', 'switch', 'password', 'color', 'date', 'select', 'radio', 'checkbox', 'editor', 'image_select', 'info', 'message', 'multi_text_repeater');
			$raw_sections = $this->get_sections();
			$sections = array();
			foreach ($raw_sections as $section_index => $section) {
				foreach ($section['fields'] as $field) {
					if (in_array($field['type'], $supported_types, true)) {
						if (!isset($sections[$section_index])) {
							$section['fields'] = array();
							$sections[$section_index] = $section;
						}
						$sections[$section_index]['fields'][$field['id']] = $field;
					}
				}
			}
			return $sections;
		}

		function render_settings_form() {
			if (!current_user_can($this->args['page_permissions'])) {
				return;
			}
			$sections = $this->_get_sections();
			wp_enqueue_style('wp-color-picker');
			wp_enqueue_script('wp-color-picker');
			require __DIR__ . '/views/settings-form.php';
		}

	}

}
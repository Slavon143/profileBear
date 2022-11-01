<?php

if (!class_exists('WP_Sheet_Editor_Helpers')) {

	class WP_Sheet_Editor_Helpers {

		var $post_type;
		static private $instance = false;
		var $urls_to_file_ids_cache = array();
		var $meta_keys_refreshed = array();

		private function __construct() {
			
		}

		/**
		 * Allow letters, numbers, spaces, and ()
		 * @param string $input
		 * @return string
		 */
		function convert_key_to_label($input) {
			return ucwords(trim(str_replace(array('-', '_'), ' ', preg_replace("/[^a-zA-Z0-9\:\.\-\_\s\(\)]/", "", $input))));
		}

// Read a file and display its content chunk by chunk
		function readfile_chunked($filename, $retbytes = TRUE) {
			$buffer = '';
			$cnt = 0;
			$handle = fopen($filename, 'rb');

			if ($handle === false) {
				return '';
			}

			while (!feof($handle)) {
				$buffer = fread($handle, 1024 * 1024);
// We must echo without sanitizing because this function is used to download a file existing in the server,
// We're reading the contents of the file and echoing with http headers that instruct the browser to download it as a regular file
// This is used by the data exporter, download of logs for troubleshooting purposes, and export of settings, so we don't want to alter the values being exported
				echo $buffer; // WPCS: XSS ok.
// Removed because some servers download an empty file
//				ob_flush();
//				flush();

				if ($retbytes) {
					$cnt += strlen($buffer);
				}
			}

			$status = fclose($handle);

			if ($retbytes && $status) {
				return $cnt; // return num. bytes delivered like readfile() does.
			}

			return $status;
		}

		public function set_with_dot_notation(&$array, $key, $value) {
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

		public function get_with_dot_notation($array, $key, $default = null) {
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

		/**
		 * Notation to numbers.
		 *
		 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
		 *
		 * @param  string $size Size value.
		 * @return int
		 */
		function let_to_num($size) {
			$l = substr($size, -1);
			$ret = (int) substr($size, 0, -1);
			switch (strtoupper($l)) {
				case 'P':
					$ret *= 1024;
// No break.
				case 'T':
					$ret *= 1024;
// No break.
				case 'G':
					$ret *= 1024;
// No break.
				case 'M':
					$ret *= 1024;
// No break.
				case 'K':
					$ret *= 1024;
// No break.
			}
			return $ret;
		}

		/**
		 * Remove all empty elements from an array recursively
		 * @param array $haystack
		 * @return array
		 */
		function array_remove_empty($haystack) {
			foreach ($haystack as $key => $value) {
				if (is_array($value)) {
					$haystack[$key] = $this->array_remove_empty($haystack[$key]);
				}

				if (empty($haystack[$key])) {
					unset($haystack[$key]);
				}
			}

			return $haystack;
		}

		function get_random_date_in_range($start, $end) {
			$int = mt_rand($start, $end);
			return date("Y-m-d H:i:s", $int);
		}

		function columns_cache_expiration($total_rows = 0) {

			$cache_expiration = DAY_IN_SECONDS * 7;
			if ($total_rows < 200) {
				$cache_expiration = MINUTE_IN_SECONDS * 30;
			}
			return $cache_expiration;
		}

		function get_current_query_session_id() {
			global $wp_query;
			$out = false;

			if (!is_object($wp_query) || empty($wp_query->query_vars) || !array_diff(array_keys($_GET), array('post_type'))) {
				return $out;
			}
			$wp_query_vars = json_encode(array_filter($wp_query->query_vars));
			$transient_key = 'wpse_catalog_session' . is_user_logged_in() . '_' . crc32($wp_query_vars);
			if (!get_transient($transient_key)) {
				set_transient($transient_key, $wp_query_vars, WEEK_IN_SECONDS);
			}
			return $transient_key;
		}

		function _get_post_id_from_search($search_value) {

			$product_parts = explode('--', $search_value);
			return (int) end($product_parts);
		}

		function get_columns_limit() {
			$columns_limit = (!empty(VGSE()->options['be_columns_limit'])) ? (int) VGSE()->options['be_columns_limit'] : 410;
			return apply_filters('vg_sheet_editor/columns_limit', $columns_limit);
		}

		function get_enabled_post_types() {

			$post_types = VGSE()->post_type;
			if (empty($post_types)) {
				$post_types = array();
			}
			if (!is_array($post_types)) {
				$post_types = array($post_types);
			}

// Every editor has its own settings regarding post types
// because plugins can have custom spreadsheet bootstrap processes
// so we merge all the enabled_post_types from the core settings and each
// editor settings			
			foreach (VGSE()->editors as $editor) {
				$post_types = array_merge($post_types, $editor->args['enabled_post_types']);
			}

			$enabled_post_types = array_unique(apply_filters('vg_sheet_editor/get_enabled_post_types', $this->remove_disallowed_post_types(array_unique($post_types))));

			return $enabled_post_types;
		}

		function get_view_spreadsheet_capability($post_type_key) {

			$out = false;
			if (empty($post_type_key)) {
				return $out;
			}
			$provider = VGSE()->helpers->get_data_provider($post_type_key);
			$capability = $provider->get_provider_read_capability($post_type_key);
			return $capability;
		}

		function user_can_view_post_type($post_type_key) {

			$out = false;
			$capability = $this->get_view_spreadsheet_capability($post_type_key);
			if ($capability && current_user_can($capability)) {
				$out = true;
			}

			return apply_filters('vg_sheet_editor/user_can_view_post_type/' . $post_type_key, $out, $post_type_key);
		}

		function get_edit_spreadsheet_capability($post_type_key) {

			$out = false;
			if (empty($post_type_key)) {
				return $out;
			}
			$provider = VGSE()->helpers->get_data_provider($post_type_key);
			$out = $provider->get_provider_edit_capability($post_type_key);
			return $out;
		}

		function user_can_edit_post_type($post_type_key) {

			$out = false;
			$capability = $this->get_edit_spreadsheet_capability($post_type_key);
			if ($capability && current_user_can($capability)) {
				$out = true;
			}

			return apply_filters('vg_sheet_editor/user_can_edit_post_type/' . $post_type_key, $out, $post_type_key);
		}

		function user_can_delete_post_type($post_type_key) {

			$out = false;
			if (empty($post_type_key)) {
				return $out;
			}
			$provider = VGSE()->helpers->get_data_provider($post_type_key);
			if (method_exists($provider, 'get_provider_delete_capability')) {
				$capability = $provider->get_provider_delete_capability($post_type_key);
				if ($capability) {
					$out = current_user_can($capability);
				}
			}
			return $out;
		}

		/**
		 * Get all files in the folder
		 * @return array
		 */
		function get_files_list($directory_path, $file_format = '.php') {
			$files = glob(trailingslashit($directory_path) . '*' . $file_format);
			return $files;
		}

		function get_settings_page_url() {
			return esc_url(add_query_arg(array('page' => VGSE()->options_key), admin_url('admin.php')));
		}

		function can_rescan_db_fields($post_type) {
			$post_type_to_check = $post_type === 'product_variation' ? 'product' : $post_type;
			$allowed = false;
			if (!empty($_GET['wpse_rescan_db_fields']) && $_GET['wpse_rescan_db_fields'] === $post_type_to_check) {
				$allowed = true;
			}
			return $allowed;
		}

		function get_all_meta_keys($post_type = '', $limit = null) {
			$transient_key = 'vgse_all_meta_keys_' . $post_type;
// Only clear the cache once per page execution
// We call this function many times, if we don't use meta_keys_refreshed 
// it will make the heavy query to the DB many times and sometimes overloading the server
			if ($this->can_rescan_db_fields($post_type) && !in_array($post_type, $this->meta_keys_refreshed, true)) {
				$this->meta_keys_refreshed[] = $post_type;
				delete_transient($transient_key);
			}
			$meta_keys = get_transient($transient_key);

			if (!$meta_keys) {
				$meta_keys = VGSE()->helpers->get_current_provider()->get_all_meta_fields($post_type);
				set_transient($transient_key, $meta_keys, DAY_IN_SECONDS);
			}
			if (!$meta_keys) {
				$meta_keys = array();
			}

			if (is_int($limit) && count($meta_keys) > $limit) {
				$meta_keys = array_slice($meta_keys, 0, $limit);
			}

			return $meta_keys;
		}

		function is_settings_page() {
			return isset($_GET['page']) && $_GET['page'] === VGSE()->options_key;
		}

		function get_data_provider_class_key($provider) {
			$class_name = 'VGSE_Provider_' . ucwords($provider);

			if (!class_exists($class_name)) {
				$provider = apply_filters('vg_sheet_editor/provider/default_provider_key', 'post', $provider);
			}

			return apply_filters('vg_sheet_editor/provider/class_key', $provider);
		}

		function get_current_provider() {
			if (empty(VGSE()->current_provider)) {
				VGSE()->current_provider = VGSE()->helpers->get_data_provider($this->get_provider_from_query_string());
			}
			return VGSE()->current_provider;
		}

		function get_prepared_post_types() {

			$allowed_post_types = VGSE()->helpers->get_allowed_post_types();
			$post_types = VGSE()->helpers->get_all_post_types(array(
				'show_in_menu' => true,
			));
			$free = array('post', 'page', 'product');
			$free_install_url = VGSE()->get_plugin_install_url('bulk edit posts wp sheet editor');

			$sheets = array();
			if (!empty($post_types)) {
				foreach ($post_types as $post_type) {
					$key = $post_type->name;
					$post_type_name = $post_type->label;
					$disabled = !isset($allowed_post_types[$key]) ? ' disabled ' : '';
					if ($key === 'users') {
						$buy_link = VGSE()->bundles['users']['inactive_action_url'];
					} else {
						$extension = VGSE()->helpers->get_extension_by_post_type($key);
						$buy_link = ( $extension && !empty($extension['inactive_action_url']) ) ? $extension['inactive_action_url'] : '';
					}
					$maybe_go_premium = !empty($disabled) ? '<small><a href="' . VGSE()->get_buy_link('setup-post-type-selector', $buy_link) . '" target="_blank">' . __('(Pro extension)', VGSE()->textname) . '</a></small>' : '';

// The free extension option will be displayed from 2020-01-20 to 2020-01-27 only
					if ($disabled && in_array($key, $free) && (date('Y-m-d') >= '2020-01-20' && date('Y-m-d') <= '2020-01-27' )) {
						$maybe_go_premium = '<small><a href="' . esc_url($free_install_url) . '" target="_blank">' . __('(Install free extension)', VGSE()->textname) . '</a></small>';
					}

					$sheets[$key] = array(
						'key' => $key,
						'label' => $post_type_name,
						'is_disabled' => !isset($allowed_post_types[$key]),
						'description' => $maybe_go_premium,
					);
				}
			}

			$final_sheets = apply_filters('vg_sheet_editor/prepared_post_types', $sheets, $allowed_post_types, $post_types);
			$sorted = array(
				'available' => array(),
				'free' => array(),
				'premium' => array(),
			);
			foreach ($final_sheets as $sheet) {
				if (empty($sheet['is_disabled'])) {
					$sorted['available'][] = $sheet;
				} elseif (strpos($sheet['description'], 'free') !== false) {
					$sorted['free'][] = $sheet;
				} else {
					$sorted['premium'][] = $sheet;
				}
			}
			return array_merge($sorted['available'], $sorted['free'], $sorted['premium']);
		}

		function get_data_provider($provider) {
			$provider_key = $this->get_data_provider_class_key($provider);
			$class_name = 'VGSE_Provider_' . ucwords($provider_key);

			return $class_name::get_instance();
		}

		function get_provider_editor($provider) {
			$provider_key = VGSE()->helpers->get_data_provider_class_key($provider);
			return (isset(VGSE()->editors[$provider_key])) ? VGSE()->editors[$provider_key] : false;
		}

		function get_unfiltered_provider_columns($post_type, $run_callbacks = false) {
			$spreadsheet_columns = VGSE()->helpers->get_provider_columns($post_type, $run_callbacks);
			$raw_unfiltered_columns = array();

			if (class_exists('WP_Sheet_Editor_Columns_Visibility')) {
				$unfiltered_columns = WP_Sheet_Editor_Columns_Visibility::$unfiltered_columns;
				$raw_unfiltered_columns = isset($unfiltered_columns[$post_type]) ? $unfiltered_columns[$post_type] : array();
			}
			$unfiltered_columns = array_merge($raw_unfiltered_columns, $spreadsheet_columns);
			return $unfiltered_columns;
		}

		function get_provider_columns($post_type, $run_callbacks = false) {

			$current_editor = VGSE()->helpers->get_provider_editor($post_type);
			if (!$current_editor) {
				return array();
			}
			return $current_editor->args['columns']->get_provider_items($post_type, $run_callbacks);
		}

		function create_placeholder_posts($post_type, $rows = 1, $out_format = 'rows') {
			$data = array();

			if (!$rows) {
				return $data;
			}
			VGSE()->current_provider = VGSE()->helpers->get_data_provider($post_type);
			$spreadsheet_columns = VGSE()->helpers->get_provider_columns($post_type);

			if (VGSE()->options['be_disable_post_actions']) {
				VGSE()->helpers->remove_all_post_actions($post_type);
			}

			$new_posts_ids = apply_filters('vg_sheet_editor/add_new_posts/create_new_posts', array(), $post_type, $rows, $spreadsheet_columns);

			if (is_wp_error($new_posts_ids)) {
				return $new_posts_ids;
			}

			if (empty($new_posts_ids)) {

				for ($i = 0; $i < $rows; $i++) {
					$my_post = array(
						'post_title' => __('...', VGSE()->textname),
						'post_type' => $post_type,
						'post_content' => ' ',
						'post_status' => 'draft',
						'post_author' => get_current_user_id(),
					);

					$my_post = apply_filters('vg_sheet_editor/add_new_posts/post_data', $my_post);
					$post_id = VGSE()->helpers->get_current_provider()->create_item($my_post);

					if (!$post_id || is_wp_error($post_id)) {
						return new WP_Error('vgse', __('The item could not be saved. Please try again in other moment.', VGSE()->textname));
					}

					do_action('vg_sheet_editor/add_new_posts/after', $post_id, $post_type, $rows, $spreadsheet_columns);

					$new_posts_ids[] = $post_id;
				}
			}
			do_action('vg_sheet_editor/add_new_posts/after_all_posts_created', $new_posts_ids, $post_type, $rows, $spreadsheet_columns);

			if ($out_format === 'ids') {
				$out = $new_posts_ids;
			} elseif (!empty($new_posts_ids)) {
				$get_rows_args = apply_filters('vg_sheet_editor/add_new_posts/get_rows_args', array(
					'nonce' => sanitize_text_field($_REQUEST['nonce']),
					'post_type' => $post_type,
					'wp_query_args' => array(
						'post__in' => $new_posts_ids,
						'posts_per_page' => -1,
						'orderby' => 'post_date ID',
						'order' => 'DESC'
					),
					'filters' => '',
					'wpse_source' => 'create_rows'
				));
				$data = VGSE()->helpers->get_rows($get_rows_args);

				if (is_wp_error($data)) {
					return $data;
				}

				$out = $data['rows'];
			}
			VGSE()->helpers->increase_counter('editions', count($new_posts_ids));
			VGSE()->helpers->increase_counter('processed', count($new_posts_ids));

			$out = apply_filters('vg_sheet_editor/add_new_posts/output', $out, $post_type, $spreadsheet_columns);
			return array_values($out);
		}

		function sanitize_data_for_db($data, $post_type) {
			VGSE()->current_provider          = VGSE()->helpers->get_data_provider( $post_type );
			$spreadsheet_columns              = VGSE()->helpers->get_provider_columns( $post_type );
			$columns_with_custom_sanitization = array_filter( wp_list_pluck( $spreadsheet_columns, 'custom_sanitization_before_saving', 'key' ) );
			$data                             = wp_unslash( $data );
			if ( ! empty( $columns_with_custom_sanitization ) ) {
				foreach ( $data as $index => $row ) {
					foreach ( $row as $column_key => $column_value ) {
						if ( empty( $column_value ) ) {
							continue;
						}
						$data[ $index ][ $column_key ] = ! empty( $columns_with_custom_sanitization[ $column_key ] ) ? call_user_func( $columns_with_custom_sanitization[ $column_key ], $column_value ) : VGSE()->helpers->safe_html( $column_value );
					}
				}
			} else {
				$data = VGSE()->helpers->safe_html( $data );				
			}
			return $data;
		}

		function save_rows($settings = array()) {
			$post_type = $settings['post_type'];
			VGSE()->current_provider = VGSE()->helpers->get_data_provider($post_type);
			$spreadsheet_columns = VGSE()->helpers->get_provider_columns($post_type);

			$data = apply_filters('vg_sheet_editor/save_rows/incoming_data', $settings['data'], $settings);
			if( is_wp_error($data)){
				return $data;
			}
			$data = VGSE()->helpers->get_current_provider()->filter_rows_before_edit($data, $post_type);

			if (VGSE()->options['be_disable_post_actions']) {
				VGSE()->helpers->remove_all_post_actions($post_type);
			}

			do_action('vg_sheet_editor/save_rows/before_saving_rows', $data, $post_type, $spreadsheet_columns);

			$editions_count = 0;

// We used to use wp_suspend_cache_invalidation(); to suspend the cache invalidation
// and prevent WP from doing unnecessary mysql queries. But we disabled it because it caused
// too many issues on sites that use aggressive cache (wp.com)
//			if (!empty(VGSE()->options['be_suspend_object_cache_invalidation']) && strpos($data_as_json, '"post_name":') === false) {
//				wp_suspend_cache_invalidation();
//			}

			$new_rows_ids = array();
			$original_new_rows_ids = array();
			if (!empty($settings['allow_to_create_new'])) {
				$new_rows_count = 0;
				foreach ($data as $row_index => $item) {
					if (empty($item['ID']) || !$this->sanitize_integer($item['ID'])) {
						$new_rows_count++;
					}
				}
				$new_rows_ids = VGSE()->helpers->create_placeholder_posts($post_type, $new_rows_count, 'ids');
				$original_new_rows_ids = $new_rows_ids;

				if (function_exists('WPSE_Logger_Obj') && !empty($_REQUEST['wpse_job_id'])) {
					WPSE_Logger_Obj()->entry(sprintf('Before saving: Created %d rows as placeholder that will be used for saving real data later.', $new_rows_count), sanitize_text_field($_REQUEST['wpse_job_id']));
				}
			}

			try {
				foreach ($data as $row_index => $item) {
					if (!empty($settings['allow_to_create_new']) && !empty($new_rows_ids) && !is_wp_error($new_rows_ids) && empty($item['ID'])) {
						$item['ID'] = array_shift($new_rows_ids);
					}
					if (empty($item['ID'])) {
						continue;
					}
					$post_id = $this->sanitize_integer($item['ID']);

					if (empty($post_id)) {
						continue;
					}

					if (function_exists('WPSE_Logger_Obj') && !empty($_REQUEST['wpse_job_id'])) {
						WPSE_Logger_Obj()->entry(sprintf('Saving row with index: %d.', $row_index + 1), sanitize_text_field($_REQUEST['wpse_job_id']));
					}
					$item = apply_filters('vg_sheet_editor/save_rows/row_data_before_save', $item, $post_id, $post_type, $spreadsheet_columns, $settings);
					if (is_wp_error($item)) {
						return $item;
					}
					if (empty($item)) {
						continue;
					}

					$my_post = array();

					foreach ($spreadsheet_columns as $key => $column_settings) {

						if (!isset($item[$key])) {
							continue;
						}

// If this is a <select> column, we check if the incoming value 
// is a label and we convert it into the real value to prevent mistakes from the user
// We don't do this for autocomplete columns because they don't have static option values
						$cell_value = $item[$key];
						$allowed_values = !empty($column_settings['formatted']['selectOptions']) ? $column_settings['formatted']['selectOptions'] : array();

						if ($allowed_values && !empty($cell_value) && !isset($allowed_values[$cell_value])) {
							$value_key = array_search($cell_value, $allowed_values, true);
							if ($value_key !== false && is_string($value_key)) {
								$item[$key] = $value_key;
								if (function_exists('WPSE_Logger_Obj') && !empty($_REQUEST['wpse_job_id'])) {
									WPSE_Logger_Obj()->entry(sprintf('Saving row with index: %d - Converting friendly value to database format: %s to %s', $row_index + 1, $cell_value, $value_key), sanitize_text_field($_REQUEST['wpse_job_id']));
								}
							}
						}

						do_action('vg_sheet_editor/save_rows/before_saving_cell', $item, $post_type, $column_settings, $key, $spreadsheet_columns, $post_id);
						if (!$column_settings['allow_to_save']) {
							continue;
						}

// If the value should be prepared using a callback before we save
						if (!empty($column_settings['prepare_value_for_database'])) {
							$item[$key] = call_user_func($column_settings['prepare_value_for_database'], $post_id, $key, $item[$key], $post_type, $column_settings, $spreadsheet_columns);
						}

// Use column callback to save the cell value
						if (!empty($column_settings['save_value_callback']) && is_callable($column_settings['save_value_callback'])) {
							call_user_func($column_settings['save_value_callback'], $post_id, $key, $item[$key], $post_type, $column_settings, $spreadsheet_columns);
							continue;
						}

// If file cells, convert URLs to file IDs					
						if (in_array($column_settings['value_type'], array('boton_gallery', 'boton_gallery_multiple')) && is_string($item[$key])) {

							$item[$key] = implode(',', array_filter(VGSE()->helpers->maybe_replace_urls_with_file_ids(explode(',', $item[$key]), $post_id)));
						}

						if ($column_settings['type'] === 'handsontable' && !empty($item[$key])) {
							$item[$key] = json_decode(wp_unslash($item[$key]), true);
						}

						if ($column_settings['data_type'] === 'post_data') {

							$final_key = $key;
							if (VGSE()->helpers->get_current_provider()->is_post_type) {
								if ($key !== 'ID' && !in_array($key, array('comment_status', 'menu_order', 'comment_count')) && strpos($key, 'post_') === false) {
									$final_key = 'post_' . $key;
								}
							}

							$my_post[$final_key] = VGSE()->data_helpers->set_post($key, $item[$key], $post_id);
						}
						if ($column_settings['data_type'] === 'meta_data' || $column_settings['data_type'] === 'post_meta') {
							$result = VGSE()->helpers->get_current_provider()->update_item_meta($post_id, $key, $item[$key]);

							if ($result) {
								$editions_count++;
							}
						}
						if ($column_settings['data_type'] === 'post_terms') {

							$terms_saved = VGSE()->data_helpers->prepare_post_terms_for_saving($item[$key], $key);
							VGSE()->helpers->get_current_provider()->set_object_terms($post_id, $terms_saved, $key);
						}

						$new_value = $item[$key];
						$post_id = $post_id;
						$cell_args = $column_settings;
						do_action('vg_sheet_editor/save_rows/after_saving_cell', $post_type, $post_id, $key, $new_value, $cell_args, $spreadsheet_columns, $item);
					}


					if (!empty($data[$row_index]['_thumbnail_id']) && empty($item['_thumbnail_id']) && !empty($_REQUEST['pending_post_if_image_failed']) && VGSE()->helpers->get_current_provider()->is_post_type) {
						$my_post['post_status'] = 'pending';
					}

					if (!empty($my_post)) {
						if (empty($my_post['ID'])) {
							$my_post['ID'] = $post_id;
						}
						if (!empty($my_post['post_title'])) {
							$my_post['post_title'] = empty(VGSE()->options['allow_html_in_post_titles']) ? wp_strip_all_tags($my_post['post_title']) : wp_kses_post($my_post['post_title']);
						}
						if (!empty($my_post['post_date'])) {
							$my_post['post_date_gmt'] = get_gmt_from_date($my_post['post_date']);
							$my_post['edit_date'] = true;
						}

						$original_post = VGSE()->helpers->get_current_provider()->get_item($my_post['ID'], ARRAY_A);

// count how many fields were modified 
						foreach ($original_post as $key => $original_value) {
							if (isset($my_post[$key]) && $my_post[$key] !== $original_value) {
								$editions_count++;
							}
						}

						$post_id = VGSE()->helpers->get_current_provider()->update_item_data($my_post, true);
						if (is_wp_error($post_id)) {
							return $post_id;
						}
					}
					do_action('vg_sheet_editor/save_rows/after_saving_post', $post_id, $item, $data, $post_type, $spreadsheet_columns, $settings, $original_new_rows_ids);
					if (function_exists('WPSE_Logger_Obj') && !empty($_REQUEST['wpse_job_id'])) {
						WPSE_Logger_Obj()->entry(sprintf('Saving row with index: %d has completed', $row_index + 1), sanitize_text_field($_REQUEST['wpse_job_id']));
					}
				}
			} catch (Exception $e) {
				$exception_message = $e->getMessage();
// If there is an invalid post type error, this means the import stopped and we 
// delete the placeholder posts becaue we don't need them
				if (strpos($exception_message, 'wpse_invalid_post_type') !== false) {
					$exception_message_data = json_decode($exception_message, true);
					$exception_message = $exception_message_data['message'];
					wp_delete_post($exception_message_data['post_id'], true);
					foreach ($new_rows_ids as $placeholder_post_id) {
						wp_delete_post($placeholder_post_id, true);
					}
				}

				do_action('vg_sheet_editor/save_rows/fatal_error_handler', $e, $data, $post_type, $spreadsheet_columns, $settings);
				return new WP_Error('vgse', sprintf(__('Error: %s', VGSE()->textname), $exception_message));
			}
			do_action('vg_sheet_editor/save_rows/after_saving_rows', $data, $post_type, $spreadsheet_columns, $settings);

			VGSE()->helpers->increase_counter('editions', $editions_count);
			VGSE()->helpers->increase_counter('processed', count($data));

			return apply_filters('vg_sheet_editor/save_rows/response', true, $data, $post_type, $spreadsheet_columns, $settings);
		}

		function sanitize_integer($integer) {
			if (is_string($integer)) {
				$out = (int) trim(wp_strip_all_tags($integer));
			} else {
				$out = (int) $integer;
			}
			return $out;
		}

		function get_sheet_sort_options($sheet_key){
			$sort_options = array();
			if ( ! VGSE()->helpers->has_paid_addon_active() ) {
				return $sort_options;
			}
			$provider = VGSE()->helpers->get_data_provider( $sheet_key );
			if ( ! $provider || ! $provider->is_post_type ) {
				return $sort_options;
			}
			$transient_key = 'vgse_sort_options_' . $sheet_key;
			$sort_options  = get_transient( $transient_key );

			if ( method_exists( VGSE()->helpers, 'can_rescan_db_fields' ) && VGSE()->helpers->can_rescan_db_fields( $sheet_key ) ) {
				$sort_options = false;
			}
			if ( ! $sort_options ) {
				$spreadsheet_columns = VGSE()->helpers->get_provider_columns( $sheet_key );
				$meta_columns        = wp_list_filter( $spreadsheet_columns, array( 'data_type' => 'meta_data' ) );
				ksort( $meta_columns );
				foreach ( $meta_columns as $key => $column ) {
					if ( ! empty( $column['serialized_field_original_key'] ) || ! empty( $column['prepare_value_for_database'] ) || in_array( $column['type'], array( 'handsontable', 'metabox', 'view_post', 'boton_gallery_multiple' ), true ) || strpos( $key, 'wpse_' ) !== false ) {
						unset( $meta_columns[ $key ] );
					}
				}
				$sort_keys    = array_merge( array( 'ID', 'post_title', 'post_name', 'post_date', 'post_modified' ), array_keys( $meta_columns ) );
				$sort_options = array();
				foreach ( $sort_keys as $key ) {
					$column_name                   = isset( $spreadsheet_columns[ $key ] ) ? $spreadsheet_columns[ $key ]['title'] : $key;
					$sort_options[ 'ASC:' . $key ] = sanitize_text_field( $column_name ) . ' : ASC';

					if ( $key === 'post_date' ) {
						$sort_options[''] = sanitize_text_field( $column_name ) . ' : DESC (' . __( 'Default', VGSE()->textname ) . ')';
					} else {
						$sort_options[ 'DESC:' . $key ] = sanitize_text_field( $column_name ) . ' : DESC';
					}
				}
				set_transient( $transient_key, $sort_options, WEEK_IN_SECONDS );
			}
			
			return $sort_options;
		}

		function prepare_query_params_for_retrieving_rows($settings) {
			if (!current_user_can('manage_options') && !empty($settings['posts_per_page']) && $settings['posts_per_page'] > 100) {
				$settings['posts_per_page'] = 100;
			}


			if (!empty($settings['posts_per_page'])) {
				$posts_per_page = (int) $settings['posts_per_page'];
			} elseif (!empty(VGSE()->options) && !empty(VGSE()->options['be_posts_per_page'])) {
				$posts_per_page = (int) VGSE()->options['be_posts_per_page'];
			} else {
				$posts_per_page = 20;
			}
			$post_type_object = get_post_type_object($settings['post_type']);

// We use this instead of the provider->get_post_statuses() because the list of rows
// should always support custom statuses added by other plugins. The provider function
// is used for other places like the search, column dropdown, etc.
			$post_statuses = get_post_stati(array('show_in_admin_status_list' => true), 'names');

			$qry = array(
				'wpse_source' => $settings['wpse_source'],
				'post_type' => $settings['post_type'],
				'posts_per_page' => $posts_per_page,
				'paged' => isset($settings['paged']) ? (int) $settings['paged'] : 1,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
			);

			if (!empty($post_statuses)) {
// Ignore trash posts by default, they need to use the search form to see trashed posts
				if (isset($post_statuses['trash'])) {
					unset($post_statuses['trash']);
				}
				$post_statuses_keys = array_keys($post_statuses);
				$qry['post_status'] = $post_statuses_keys;
				if ($qry['post_type'] === 'attachment') {
					$qry['post_status'] = array_merge($post_statuses_keys, array('inherit'));
				}
				if ($post_type_object) {
					$edit_published_posts_capability = property_exists($post_type_object->cap, 'edit_published_posts') ? $post_type_object->cap->edit_published_posts : $post_type_object->cap->edit_posts;
					$edit_private_posts_capability = property_exists($post_type_object->cap, 'edit_private_posts') ? $post_type_object->cap->edit_private_posts : $post_type_object->cap->edit_posts;

// Exclude published pages or posts if the user is not allowed to edit them
					if (!current_user_can($edit_published_posts_capability)) {
						if (!isset($qry['post_status'])) {
							$qry['post_status'] = $post_statuses_keys;
						}
						$qry['post_status'] = VGSE()->helpers->remove_array_item_by_value('publish', $qry['post_status']);
					}
					if (!current_user_can($edit_private_posts_capability)) {
						if (!isset($qry['post_status'])) {
							$qry['post_status'] = $post_statuses_keys;
						}
						$qry['post_status'] = VGSE()->helpers->remove_array_item_by_value('private', $qry['post_status']);
					}
				}
			}



// Exit if the user is not allowed to edit pages
			if ($post_type_object && !current_user_can($post_type_object->cap->edit_posts)) {
				$message = __('User not allowed to edit rows', VGSE()->textname);
				return new WP_Error('vgse', $message);
			}



			if (!empty($settings['wp_query_args'])) {
				$qry = wp_parse_args($settings['wp_query_args'], $qry);
			}

			if( ! empty(VGSE()->options['default_sortby_' . $settings['post_type']]) && empty($qry['orderby'])){
				$custom_order_by = preg_replace('/^(ASC|DESC):/', '', VGSE()->options['default_sortby_' . $settings['post_type']]);
				$custom_order = strpos(VGSE()->options['default_sortby_' . $settings['post_type']], 'ASC:') === 0 ? 'ASC' : 'DESC';
				$post_data_fields = array('ID', 'post_title', 'post_name', 'post_date', 'post_modified' );

				$qry['order'] = $custom_order;
				if( in_array($custom_order_by, $post_data_fields, true)){
					$qry['orderby'] = str_replace('post_', '', $custom_order_by);
					if( $custom_order_by !== 'ID'){
						$qry['orderby'] .= ' ID';
					}
				} else {
					$qry['orderby'] = 'meta_value ID';
					if( ! isset($qry['meta_query'])){
						$qry['meta_query'] = array();
					}
					$qry['meta_query'][] = array(
						'relation' => 'OR',
						array(
							'key' => $custom_order_by, 
							'compare' => 'NOT EXISTS'
						),
						array(
							'key' => $custom_order_by, 
							'compare' => 'EXISTS'
						),
					);
				}
			}

			if ($post_type_object && !current_user_can($post_type_object->cap->edit_others_posts)) {
				$qry['author'] = get_current_user_id();
			}

			if (!empty(VGSE()->options['be_initial_rows_offset'])) {
				$initial_page = (int) ( (int) VGSE()->options['be_initial_rows_offset'] / $qry['posts_per_page'] );
				$qry['paged'] += $initial_page;
			}

			$qry = apply_filters('vg_sheet_editor/load_rows/wp_query_args', $qry, $settings);
			return $qry;
		}

		function prepare_raw_value_for_display($value, $post, $column_settings) {
			if (!empty($column_settings['prepare_value_for_display']) && is_callable($column_settings['prepare_value_for_display'])) {
				$value = call_user_func($column_settings['prepare_value_for_display'], $value, $post, $column_settings['key'], $column_settings);
			}
			return $value;
		}

		function get_rows($settings = array()) {
			if (function_exists('WPSE_Profiler_Obj')) {
				WPSE_Profiler_Obj()->record("Start " . __FUNCTION__);
			}
			$settings = apply_filters('vg_sheet_editor/load_rows/raw_incoming_data', $settings);
			$provider = $settings['post_type'];
			VGSE()->current_provider = $this->get_data_provider($provider);

			$wp_query_args = $this->prepare_query_params_for_retrieving_rows($settings);

			if (is_wp_error($wp_query_args)) {
				return $wp_query_args;
			}

// Note. I already tried to disable the post meta cache with the filter 
// update_post_metadata_cache , but it breaks the get_post_meta calls
// when we need meta data not retrieved during prefetch
// We can use the filter again on specific sections.

			if (function_exists('WPSE_Profiler_Obj')) {
				WPSE_Profiler_Obj()->record("After qry " . __FUNCTION__);
			}
// Allow other plugins to replace the query
			$query = apply_filters('vg_sheet_editor/get_rows/query', null, $wp_query_args);
			if (!$query) {
				$query = VGSE()->helpers->get_current_provider()->get_items($wp_query_args);
			}

			if (!empty($settings['return_raw_results'])) {
				return $query->posts;
			}
			$GLOBALS['wpse_main_query'] = $query;

			if (function_exists('WPSE_Profiler_Obj')) {
				WPSE_Profiler_Obj()->record('After $query ' . __FUNCTION__);
			}
			$data = array();
			$not_found_message = '';
			$spreadsheet_columns = VGSE()->helpers->get_provider_columns($settings['post_type']);
			if (!empty($query->posts)) {

				$count = 0;

				if (function_exists('WPSE_Profiler_Obj')) {
					WPSE_Profiler_Obj()->record('After $spreadsheet_columns ' . __FUNCTION__);
				}
				$posts = apply_filters('vg_sheet_editor/load_rows/found_posts', $query->posts, $wp_query_args, $settings, $spreadsheet_columns);

				$data = apply_filters('vg_sheet_editor/load_rows/preload_data', $data, $posts, $wp_query_args, $settings, $spreadsheet_columns);

				$post_ids = wp_list_pluck($posts, 'ID');

				if (empty(VGSE()->options['be_disable_data_prefetch'])) {
					VGSE()->helpers->get_current_provider()->prefetch_data($post_ids, $settings['post_type'], $spreadsheet_columns);
				}

				if (function_exists('WPSE_Profiler_Obj')) {
					WPSE_Profiler_Obj()->record('Before $posts foreach ' . __FUNCTION__);
				}

				$can_setup_postdata = apply_filters('vg_sheet_editor/load_rows/can_setup_postdata', false, $posts, $wp_query_args, $spreadsheet_columns, $settings);

				$referenced_post_types = array_unique(wp_list_pluck($posts, 'post_type'));
				$allowed_columns_per_type = array();
				if (count($referenced_post_types) > 1) {
					$post_type_column = array_column($posts, 'post_type');

					foreach ($referenced_post_types as $referenced_post_type) {
						$row_index = array_search($referenced_post_type, $post_type_column);

						$allowed_columns_per_type[$referenced_post_type] = apply_filters('vg_sheet_editor/load_rows/allowed_post_columns', $spreadsheet_columns, $posts[$row_index], $wp_query_args);
					}
				}

				foreach ($posts as $post) {

					$GLOBALS['post'] = & $post;

					if (isset($post->post_title) && $can_setup_postdata) {
						setup_postdata($post);
					}

					$post_id = $post->ID;

					if (!apply_filters('vg_sheet_editor/load_rows/can_edit_item', true, $post, $wp_query_args, $spreadsheet_columns)) {
						continue;
					}

					$data[$post_id]['post_type'] = $post->post_type;
					$data[$post_id]['provider'] = $post->post_type;

// Allow other plugins to filter the fields for every post, so we can optimize 
// the process and avoid retrieving unnecessary data					
					if (count($referenced_post_types) > 1 && isset($allowed_columns_per_type[$post->post_type])) {
						$allowed_columns_for_post = $allowed_columns_per_type[$post->post_type];
					} else {
						$allowed_columns_for_post = $spreadsheet_columns;
					}

					foreach ($allowed_columns_for_post as $column_key => $column_settings) {
						if (isset($data[$post_id][$column_key])) {
							continue;
						}
						$item_custom_data = apply_filters('vg_sheet_editor/load_rows/get_cell_data', false, $post, $column_key, $column_settings);

						if (!is_bool($item_custom_data)) {
							$data[$post_id][$column_key] = $item_custom_data;
							continue;
						}

// Use column callback to retrieve the cell value
						if (!empty($column_settings['get_value_callback']) && is_callable($column_settings['get_value_callback'])) {
							$data[$post_id][$column_key] = call_user_func($column_settings['get_value_callback'], $post, $column_key, $column_settings);
							$data[$post_id][$column_key] = $this->prepare_raw_value_for_display($data[$post_id][$column_key], $post, $column_settings);

							continue;
						}

						if (!empty($column_settings['data_type'])) {

							if ($column_settings['data_type'] === 'post_data') {
								$data[$post_id][$column_key] = VGSE()->data_helpers->get_post_data($column_key, $post->ID);
							}
							if ($column_settings['data_type'] === 'meta_data') {
								$data[$post_id][$column_key] = VGSE()->helpers->get_current_provider()->get_item_meta($post->ID, $column_key, true, 'read');
							}
							if ($column_settings['data_type'] === 'post_terms') {
								$data[$post_id][$column_key] = VGSE()->helpers->get_current_provider()->get_item_terms($post->ID, $column_key);
							}

							$data[$post_id][$column_key] = $this->prepare_raw_value_for_display($data[$post_id][$column_key], $post, $column_settings);

							if ($column_settings['type'] === 'boton_gallery') {
								$data[$post_id][$column_key] = VGSE()->helpers->get_gallery_cell_content($post->ID, $column_key, $column_settings['data_type'], $data[$post_id][$column_key]);
							}
							if ($column_settings['type'] === 'boton_gallery_multiple') {
								$data[$post_id][$column_key] = VGSE()->helpers->get_gallery_cell_content($post->ID, $column_key, $column_settings['data_type'], $data[$post_id][$column_key]);
							}
						} else {
							if ($column_settings['type'] === 'external_button') {
								$data[$post_id][$column_key] = str_replace(array(
									'{ID}',
									'{post_title}',
									'{post_content}',
									'{post_type}',
									'{post_status}',
									'{post_url}',
									'{parent_post_url}',
									'{post_parent}',
										), array(
									$post->ID,
									$post->post_title,
									$post->post_content,
									$post->post_type,
									$post->post_status,
									get_permalink($post->ID),
									get_permalink($post->post_parent),
									$post->post_parent,
										), $column_settings['external_button_template']);
							}
							if (in_array($column_settings['type'], apply_filters('vg_sheet_editor/get_rows/cell_content/custom_modal_editor_types', array('metabox', 'handsontable')))) {
								$data[$post_id][$column_key] = VGSE()->helpers->get_custom_modal_editor_cell_content($post->ID, $column_key, $column_settings);
							}

// Tmp. We use the new handsontable renderer only for _default_attributes for now
// we will use it for all in the future
							if ($column_settings['type'] === 'handsontable' && $column_settings['use_new_handsontable_renderer']) {

								$raw_value = apply_filters('vg_sheet_editor/handsontable_cell_content/existing_value', maybe_unserialize(VGSE()->helpers->get_current_provider()->get_item_meta($post->ID, $column_key, true, 'read')), $post, $column_key, $column_settings);

								if (empty($raw_value)) {
									$raw_value = array();
								}
								$data[$post_id][$column_key] = json_encode($raw_value);
							}
						}

						$is_checkbox = !empty($column_settings['formatted']['type']) && $column_settings['formatted']['type'] === 'checkbox';
// Make sure checkboxes have allowed values only
						if ($is_checkbox && !empty($data[$post_id][$column_key])) {
							$allowed_checkbox_values = array($column_settings['formatted']['checkedTemplate'], $column_settings['formatted']['uncheckedTemplate']);
							$should_be_integers = is_numeric(implode('', $allowed_checkbox_values));
							if ($should_be_integers) {
								$allowed_checkbox_values = array_map('intval', $allowed_checkbox_values);
								$data[$post_id][$column_key] = intval($data[$post_id][$column_key]);
							}
							if (!in_array($data[$post_id][$column_key], $allowed_checkbox_values, true)) {
								$data[$post_id][$column_key] = $column_settings['default_value'];
							}
						}
// Use default value if the field is empty
						if (empty($data[$post_id][$column_key]) && isset($column_settings['default_value']) && $data[$post_id][$column_key] !== $column_settings['default_value']) {
							$data[$post_id][$column_key] = $column_settings['default_value'];
						}

// Catch all columns registered by mistake having arrays/objects as values
						if (is_array($data[$post_id][$column_key]) || is_object($data[$post_id][$column_key])) {
							$data[$post_id][$column_key] = '';
						}
					}
					$count++;
				}
				if (function_exists('WPSE_Profiler_Obj')) {
					WPSE_Profiler_Obj()->record('After $posts foreach ' . __FUNCTION__);
				}
			} else {

				$filters = WP_Sheet_Editor_Filters::get_instance()->get_raw_filters();
				if ((int) $wp_query_args['paged'] > 1) {
					$not_found_message = __('No more posts available.', VGSE()->textname);
				} elseif (!empty($filters)) {
					$not_found_message = __('No posts found matching your search parameters. You can remove the active filters or try with a different search.', VGSE()->textname);
				} else {
					$not_found_message = __('No posts available for the current page.', VGSE()->textname);
				}
			}


			wp_reset_postdata();
			wp_reset_query();

			do_action('vg_sheet_editor/load_rows/after_processing', $data, $wp_query_args, $spreadsheet_columns, $settings, $not_found_message);

			if (empty($query->posts) && !empty($not_found_message)) {
				return new WP_Error('vgse', apply_filters('vg_sheet_editor/load_rows/not_found_message', $not_found_message, $wp_query_args, $spreadsheet_columns, $settings), array(
					'request' => current_user_can('manage_options') && is_object($query) && property_exists($query, 'request') ? $query->request : null,
				));
			}

			if (function_exists('WPSE_Profiler_Obj')) {
				WPSE_Profiler_Obj()->record('Before load_rows/output ' . __FUNCTION__);
			}
			$data = apply_filters('vg_sheet_editor/load_rows/output', $data, $wp_query_args, $spreadsheet_columns, $settings);
			$number_of_pages = ceil((int) $query->found_posts / $wp_query_args['posts_per_page']);
			$out = array(
				'rows' => $data,
				'request' => current_user_can('manage_options') && is_object($query) && property_exists($query, 'request') ? $query->request : null,
				'total' => (int) $query->found_posts,
				'message' => apply_filters('vg_sheet_editor/load_rows/rows_found_message', __('Items loaded in the spreadsheet', VGSE()->textname), $wp_query_args, $spreadsheet_columns, $settings),
				'pagination' => null,
				'max_pages' => $number_of_pages
			);

			if (!empty(VGSE()->options['enable_pagination'])) {
				if (!class_exists('WPSE_Pagination_Links_Generator')) {
					require_once VGSE_DIR . '/inc/pagination-links-generator.php';
				}
				$out['pagination'] = WPSE_Pagination_Links_Generator::create($wp_query_args['paged'], $number_of_pages, 3, '<button class="load-more button" data-pagination="%d">%d</button>');
			}
			if (function_exists('WPSE_Profiler_Obj')) {
				WPSE_Profiler_Obj()->record('Before out ' . __FUNCTION__);
				WPSE_Profiler_Obj()->finish();
			}

			return apply_filters('vg_sheet_editor/load_rows/full_output', $out, $wp_query_args, $spreadsheet_columns, $settings);
		}

		function get_term_separator() {

			$separator = (!empty(VGSE()->options['be_taxonomy_terms_separator']) ) ? VGSE()->options['be_taxonomy_terms_separator'] : ',';
// Sanitization: Only get the first character in the string, we don't allow separators with 2+ characters
			$separator = substr(trim($separator), 0, 1);
			return $separator;
		}

		function is_plain_text_request() {
			return !empty($_REQUEST['vgse_plain_mode']);
		}

		function get_editor_url($post_type) {
			$url_part = 'admin.php?page=vgse-bulk-edit-' . $post_type;
			return esc_url(admin_url($url_part));
		}

		function remove_disallowed_post_types($post_types) {

			$out = array();

			if (empty($post_types) || !is_array($post_types)) {
				return $out;
			}

			foreach ($post_types as $post_type_key) {
				if (!VGSE()->helpers->user_can_edit_post_type($post_type_key)) {
					continue;
				}

				if (VGSE()->helpers->is_post_type_allowed($post_type_key)) {
					$out[$post_type_key] = $post_type_key;
				}
			}
			return $out;
		}

		/**
		 * Get current plugin mode. If it's free or pro.
		 * @return str
		 */
		function get_plugin_mode() {
			$mode = ( defined('VGSE_ANY_PREMIUM_ADDON') && VGSE_ANY_PREMIUM_ADDON) ? 'pro' : 'free';

			return $mode . '-plugin';
		}

		/**
		 * Check if there is at least one paid addon active
		 * @return str
		 */
		function has_paid_addon_active() {
			$extensions = VGSE()->extensions;
			$has_paid_addon = wp_list_filter($extensions, array(
				'is_active' => true,
				'has_paid_offering' => true
			));

			return count($has_paid_addon);
		}

		/**
		 * Maybe replace urls in a list with wp media file ids.
		 * 
		 * @param str|array $ids
		 * @param int|null $post_id
		 * @return array
		 */
		function maybe_replace_urls_with_file_ids($ids = array(), $post_id = null) {
			global $wpdb;
			if (!is_array($ids)) {
				$ids = array($ids);
			}

			$ids = array_filter(array_map('trim', $ids));

			$out = array();
			foreach ($ids as $id) {
				$media_file_id = false;
// Urlencode spaces because the filter_var doesn't consider them URL if they have spaces.
				$id = str_replace(' ', '%20', $id);
				$cache_id = 'f' . md5($id);

// If found in cache, we also cache negative results when 
// the file couldn't be downloaded, that's why we use the double if
				if (isset($this->urls_to_file_ids_cache[$cache_id]) && empty($_REQUEST['wpse_no_cache'])) {
					if ($this->urls_to_file_ids_cache[$cache_id]) {
						$out[] = $this->urls_to_file_ids_cache[$cache_id];
					}
					continue;
				}

				$new_id = apply_filters('vg_sheet_editor/save/url_to_file_id', null, $id, $post_id);
				if (!is_null($new_id)) {
					$this->urls_to_file_ids_cache[$cache_id] = $new_id;

					if ($new_id) {
						$out[] = (int) $new_id;
					}
					continue;
				}
				$original_id = $id;
				$url_data = array(
					'title' => '',
					'alt' => '',
					'caption' => '',
					'description' => '',
					'filename' => '',
				);

// Remove alt text from URL
				if (strpos($id, ':::') !== false) {
					$url_parts = array_map('trim', explode(':::', $id));
					$id = $url_parts[0];
					unset($url_parts[0]);
					foreach ($url_parts as $url_part) {
						$url_part_split = array_map('trim', explode('::', $url_part));
						if (count($url_part_split) !== 2) {
							continue;
						}
						$url_data_key = current($url_part_split);
						$url_data[$url_data_key] = urldecode(end($url_part_split));
					}

					if (!empty($url_data['all'])) {
						$url_data = array(
							'title' => $url_data['all'],
							'alt' => $url_data['all'],
							'caption' => $url_data['all'],
							'description' => $url_data['all'],
							'filename' => $url_data['all'],
						);
					}
				}

				if (empty($id)) {
					if (function_exists('WPSE_Logger_Obj') && !empty($url_parts)) {
						WPSE_Logger_Obj()->entry('Saving image skipped. We received an empty URL with image meta data', sanitize_text_field($_REQUEST['wpse_job_id']));
					}
					continue;
				}

// We use strpos instead of filter_var because filter_var doesn't detect as 
// URL when the string contains portuguese characters
				if (strpos($id, 'http://') === 0 || strpos($id, 'https://') === 0) {


					if (strpos($id, '?wpId') !== false && strpos($id, WP_CONTENT_URL) === 0 ) {
						$media_file_id = preg_replace('/.+wpId=(\d+)$/', '$1', $id);
						// Use the wpId value only if the id exists as a media attachment, otherwise download it as a regular URL
						if (get_post_type($media_file_id) !== 'attachment') {
							$media_file_id = $this->add_file_to_gallery_from_url($id, null, $post_id);
						}
					} elseif (strpos($id, WP_CONTENT_URL) !== false) {
						$media_file_id = $this->get_attachment_id_from_url($id);
					}
					if (empty($media_file_id)) {
						$media_file_id = $this->add_file_to_gallery_from_url($id, null, $post_id);
					}

					if ($media_file_id) {
						$out[] = (int) $media_file_id;
						if (function_exists('WPSE_Logger_Obj') && !empty($_REQUEST['wpse_job_id'])) {
							WPSE_Logger_Obj()->entry(sprintf('Saving image successful: %s - Image imported to the wp media library', $id), sanitize_text_field($_REQUEST['wpse_job_id']));
						}
						do_action('vg_sheet_editor/save/after_image_url_saved', $media_file_id, $url_data['alt'], $url_data['title'], $url_data['caption'], $url_data['description'], $url_data['filename'], $original_id);
					}
					$this->urls_to_file_ids_cache[$cache_id] = (int) $media_file_id;
				} elseif (strpos($id, '.') !== false && strpos($id, '[') === false && strpos($id, '/') === false) {
// If the $id contains a file name, use the first image from the media library matching the file name
					$sql = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND (meta_value LIKE %s OR meta_value = %s ) LIMIT 1";
					$new_id = (int) $wpdb->get_var($wpdb->prepare($sql, '%/' . $wpdb->esc_like($id), $id));
					if ($new_id) {
						$out[] = $new_id;
						if (function_exists('WPSE_Logger_Obj') && !empty($_REQUEST['wpse_job_id'])) {
							WPSE_Logger_Obj()->entry(sprintf('Saving image successful: %s - Image found in the wp media library', $id), sanitize_text_field($_REQUEST['wpse_job_id']));
						}
					} else {
						if (function_exists('WPSE_Logger_Obj') && !empty($_REQUEST['wpse_job_id'])) {
							WPSE_Logger_Obj()->entry(sprintf('Saving image failed: %s - we could not find an image in the media library with same file name', $id), sanitize_text_field($_REQUEST['wpse_job_id']));
						}
					}
					$this->urls_to_file_ids_cache[$cache_id] = (int) $new_id;
				} elseif (preg_match('/^\/.*\.(jpg|png|jpeg|gif|webp)$/i', $id) && file_exists(WP_CONTENT_DIR . '/wpse-temp-images' . $id)) {
					$file_path = WP_CONTENT_DIR . '/wpse-temp-images' . preg_replace('/\.\.\/|\/\/|\.\.|\:|\%/i', '', wp_normalize_path($id));
					$media_file_id = $this->add_file_to_gallery_from_path($file_path, null, $post_id);
					if ($media_file_id) {
						$out[] = (int) $media_file_id;
						if (function_exists('WPSE_Logger_Obj') && !empty($_REQUEST['wpse_job_id'])) {
							WPSE_Logger_Obj()->entry(sprintf('Saving image successful: %s - Image found and import into the wp media library', $id), sanitize_text_field($_REQUEST['wpse_job_id']));
						}
						do_action('vg_sheet_editor/save/after_image_url_saved', $media_file_id, $url_data['alt'], $url_data['title'], $url_data['caption'], $url_data['description'], $url_data['filename'], $original_id);
					} else {
						if (function_exists('WPSE_Logger_Obj') && !empty($_REQUEST['wpse_job_id'])) {
							WPSE_Logger_Obj()->entry(sprintf('Saving image failed: %s - we could not import the image into the media library', $id), sanitize_text_field($_REQUEST['wpse_job_id']));
						}
					}
				} else {
					$out[] = $id;
				}
			}

// Automatically attach images to the post
			if (is_int($post_id) && VGSE()->helpers->get_current_provider()->is_post_type) {
				foreach ($out as $image_id) {
					$image = get_post($image_id);
					if ($image && empty((int) $image->post_parent)) {
						wp_update_post(array(
							'ID' => $image_id,
							'post_parent' => $post_id
						));
					}
				}
			}


			return $out;
		}

		function add_file_to_gallery_from_path($file_path, $save_as, $post_id = null, $original_url = null) {
			if (is_wp_error($file_path) || !is_string($file_path)) {
				return false;
			}
			require_once(ABSPATH . 'wp-admin/includes/media.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/image.php');

			if (!$save_as) {
				$save_as = basename($file_path);
			}

			if (preg_match('/\.com$/', $save_as)) {
				$save_as .= '.jpg';
			}

// build up array like PHP file upload
			$file = array();
			$file['name'] = $save_as;
			$file['tmp_name'] = $file_path;

			if (empty($file['tmp_name']) || is_wp_error($file['tmp_name'])) {
				if (is_string($file['tmp_name']) && file_exists($file['tmp_name'])) {
					unlink($file['tmp_name']);
				}
				return false;
			}

			$attachmentId = media_handle_sideload($file, $post_id);

// If error storing permanently, unlink
			if (is_wp_error($attachmentId)) {
				unlink($file['tmp_name']);
				return false;
			}

// create the thumbnails
			$attach_data = wp_generate_attachment_metadata($attachmentId, get_attached_file($attachmentId));

			wp_update_attachment_metadata($attachmentId, $attach_data);
			if (!empty($original_url)) {
				update_post_meta($attachmentId, 'wpse_external_file_url', esc_url($original_url));
			}
			return $attachmentId;
		}

		/**
		 * Add file to gallery from url
		 * Download a file from an external url and add it to 
		 * the wordpress gallery.		 
		 * @param str $url External file url
		 * @param str $save_as New file name
		 * @param int $post_id Append to the post ID
		 * @return mixed Attachment ID on success, false on failure
		 */
		function add_file_to_gallery_from_url($url, $save_as = null, $post_id = null) {
			global $wpdb;
			if (!$url) {
				return false;
			}
// Remove query strings, we accept only static files.
			$url = preg_replace('/\?.*/', '', $url);

			$file_id = (int) $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key IN ('wpse_external_file_url', '_wc_attachment_source') AND meta_value = %s LIMIT 1", esc_url($url)));
			if ($file_id > 0) {
				return $file_id;
			}
			if (!$save_as) {
				$save_as = basename($url);
			}

// Compatibility for WP Offload Media or weird server setups where the local images used different hostname or folder
			if (function_exists('as3cf_init')) {
				$file_id = (int) $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND (meta_value LIKE %s OR meta_value = %s) LIMIT 1", '%/' . $wpdb->esc_like($save_as), $save_as));
				if ($file_id > 0) {
					return $file_id;
				}
			}

			$timeout = (!empty(VGSE()->options['remote_image_timeout'])) ? (int) VGSE()->options['remote_image_timeout'] : 4;
			$file_path = download_url(esc_url($url), $timeout);
			$attachment_id = $this->add_file_to_gallery_from_path($file_path, $save_as, $post_id, $url);

			if (!$attachment_id) {
				if (function_exists('WPSE_Logger_Obj') && !empty($_REQUEST['wpse_job_id'])) {
					WPSE_Logger_Obj()->entry(sprintf('Saving image failed: %s - we could not download image from external URL. This error happens outside our plugin, maybe the external server rejected the request, or your internet connection failed if you are using a local server, or maybe the server speed was too slow and it exceeded the 4 seconds time limit that we wait for the download', $url), sanitize_text_field($_REQUEST['wpse_job_id']));
				}
			}
			return $attachment_id;
		}

		function _prepare_data_for_saving($data, $cell_args) {
			if (is_wp_error($data)) {
				return $data;
			}

			$out = $data;

			$cell_key = $cell_args['key_for_formulas'];

			if ($cell_args['data_type'] === 'post_data') {
				if ($cell_key !== 'post_content') {
					$out = VGSE()->data_helpers->set_post($cell_key, $data);
				}
				if ($cell_key === 'post_title') {
					$out = wp_strip_all_tags($out);
				}
			}
			if ($cell_args['data_type'] === 'post_terms') {
				$out = VGSE()->data_helpers->prepare_post_terms_for_saving($data, $cell_key);
			}

			return $out;
		}

		function save_column_text_value($data, $post_id, $cell_key, $post_type, $cell_args, $spreadsheet_columns) {
			// Same filter is available on save_rows
			$item = apply_filters('vg_sheet_editor/save_rows/row_data_before_save', array(
				'ID' => $post_id,
				$cell_key => $data,
					), $post_id, $post_type, $spreadsheet_columns, array(
				'wpse_source' => 'save_individual_cell_programmatically'
			));

			if (is_wp_error($item)) {
				return $item;
			}
			// If $item is empty, it means that the value was saved using the row_data_before_save hook
			if (empty($item)) {
				return;
			}


			do_action('vg_sheet_editor/save_rows/before_saving_cell', array(
				'ID' => $post_id,
				$cell_key => $data,
					), $post_type, $cell_args, $cell_key, $spreadsheet_columns, $post_id);

			$data_to_save = $this->_prepare_data_for_saving($data, $cell_args);

			// If the value should be prepared using a callback before we save
			if (!empty($cell_args['prepare_value_for_database'])) {
				$data_to_save = call_user_func($cell_args['prepare_value_for_database'], $post_id, $cell_key, $data_to_save, $post_type, $cell_args, $spreadsheet_columns);
			}

			// Use column callback to save the cell value
			if (!empty($cell_args['save_value_callback']) && is_callable($cell_args['save_value_callback'])) {
				call_user_func($cell_args['save_value_callback'], $post_id, $cell_key, $data_to_save, $post_type, $cell_args, $spreadsheet_columns);
			} else {

				if ($cell_args['data_type'] === 'post_data') {

					// If the modified data is different, we save it
					$update = array();

					$final_key = $cell_key;
					if (VGSE()->helpers->get_current_provider()->is_post_type) {
						if (!in_array($cell_key, array('comment_status', 'menu_order', 'comment_count', 'ID')) && strpos($cell_key, 'post_') === false) {
							$final_key = 'post_' . $cell_key;
						}
					}
					$update[$final_key] = $data_to_save;

					if (empty($update['ID'])) {
						$update['ID'] = $post_id;
					}
					$post_id = VGSE()->helpers->get_current_provider()->update_item_data($update, true);
				}
				if ($cell_args['data_type'] === 'meta_data' || $cell_args['data_type'] === 'post_meta') {
					$update = VGSE()->helpers->get_current_provider()->update_item_meta($post_id, $cell_key, $data_to_save);
				}
				if ($cell_args['data_type'] === 'post_terms') {
					$update = VGSE()->helpers->get_current_provider()->set_object_terms($post_id, $data_to_save, $cell_key);
				}
			}
		}

		/**
		 * Get column textual value.
		 * 
		 * @param str $column_key
		 * @param int $post_id
		 * @return boolean|string
		 */
		function get_column_text_value($column_key, $post_id, $column_settings = array(), $post_type = null) {

			if (empty($column_settings)) {
				$spreadsheet_columns = VGSE()->helpers->get_provider_columns($post_type, false);

				if (empty($spreadsheet_columns) || !is_array($spreadsheet_columns) || !isset($spreadsheet_columns[$column_key])) {
					return false;
				}

				$column_settings = $spreadsheet_columns[$column_key];
			}
			$data_type = $column_settings['data_type'];
			if (is_numeric($post_id)) {
				$post_id = (int) $post_id;
				$post = VGSE()->helpers->get_current_provider()->get_item($post_id);
			} else {
				$post = $post_id;
				$post_id = $post->ID;
			}

			$item_custom_data = apply_filters('vg_sheet_editor/load_rows/get_cell_data', false, $post, $column_key, $column_settings);

			if (!is_bool($item_custom_data)) {
				return $item_custom_data;
			}

// Use column callback to retrieve the cell value
			$out = '';
			if (!empty($column_settings['get_value_callback']) && is_callable($column_settings['get_value_callback'])) {
				$out = call_user_func($column_settings['get_value_callback'], $post, $column_key, $column_settings);
			} elseif ($data_type === 'post_data') {
				$out = VGSE()->data_helpers->get_post_data($column_key, $post_id);
			} elseif ($data_type === 'meta_data' || $data_type === 'post_meta') {
				$out = VGSE()->helpers->get_current_provider()->get_item_meta($post_id, $column_key, true, 'read');
			} elseif ($data_type === 'post_terms') {
				$out = VGSE()->helpers->get_current_provider()->get_item_terms($post_id, $column_key);
			}

			$out = VGSE()->helpers->prepare_raw_value_for_display($out, $post, $column_settings);

			return $out;
		}

		/**
		 * Get column settings
		 * 
		 * @param str $column_key
		 * @param str $post_type
		 * @return boolean|array
		 */
		function get_column_settings($column_key, $post_type = null) {

			if (!$post_type) {
				$post_type = VGSE()->helpers->get_current_provider()->key;
			}

			$spreadsheet_columns = VGSE()->helpers->get_provider_columns($post_type, false);

			$out = false;
			if (empty($spreadsheet_columns) || !is_array($spreadsheet_columns) || !isset($spreadsheet_columns[$column_key])) {
				return $out;
			}

			$column_settings = $spreadsheet_columns[$column_key];
			return $column_settings;
		}

		/**
		 * Remove keys from array
		 * @param array $array
		 * @param array $keys
		 * @return array
		 */
		public function remove_unlisted_keys($array, $keys = array()) {
			$out = array();
			foreach ($array as $key => $value) {
				if (in_array($key, $keys)) {
					$out[$key] = $value;
				}
			}
			return $out;
		}

		/**
		 * Rename array keys
		 * @param array $array Rest endpoint route
		 * @param array $keys_map Associative array of old keys => new keys.
		 * @return array
		 */
		function rename_array_keys($array, $keys_map) {

			foreach ($keys_map as $old => $new) {

				if ($old === $new) {
					continue;
				}
				if (isset($array[$old])) {
					$array[$new] = $array[$old];
					unset($array[$old]);
				} else {
					$array[$new] = '';
				}
			}
			return $array;
		}

		/**
		 * Add a post type element to posts rows.
		 * @param array $rows
		 * @return array
		 */
		public function add_post_type_to_rows($rows) {
			$new_data = array();
			foreach ($rows as $row) {
				if (!empty($row['post_type'])) {
					$new_data[] = $row;
					continue;
				}
				$post_id = (int) $this->sanitize_integer($row['ID']);

				if (empty($post_id)) {
					continue;
				}
				$row['ID'] = $post_id;
				$post = VGSE()->helpers->get_current_provider()->get_item($post_id);
				$post_type = $post->post_type;

				$row['post_type'] = $post_type;
				$new_data[] = $row;
			}
			return $new_data;
		}

		/**
		 * Process array elements and replace old values with new values.
		 * @param array $array
		 * @param array $new_format
		 * @return array
		 */
		function change_values_format($array, $new_format) {
			$boolean_to_yes = array(array(
					'old' => true,
					'new' => 'yes'
				), array(
					'old' => false,
					'new' => 'no'
			));

			foreach ($array as $key => $value) {
				if (!isset($new_format[$key])) {
					continue;
				}

				if ($new_format[$key] === 'boolean_to_yes_no') {
					$new_format[$key] = $boolean_to_yes;
				}
				foreach ($new_format[$key] as $format) {
					if ($value === $format['old']) {
						$array[$key] = $format['new'];
						break;
					}
				}
			}
			return $array;
		}

		/**
		 * Make a rest request internally
		 * @param str $method Request method.
		 * @param str $route Rest endpoint route
		 * @param array $data Request arguments.
		 * @return obj
		 */
		function create_rest_request($method = 'GET', $route = '', $data = array()) {

			if (empty($route)) {
				return false;
			}
			$request = new WP_REST_Request($method, $route);

// Add specified request parameters into the request.
			if (!empty($data)) {
				foreach ($data as $param_name => $param_value) {
					$request->set_param($param_name, $param_value);
				}
			}
			$response = rest_do_request($request);
			return $response;
		}

		/**
		 * Remove array item by value
		 * @param str $value
		 * @param array $array
		 * @return array
		 */
		function remove_array_item_by_value($value, $array) {
			$key = array_search($value, $array);
			if ($key) {
				unset($array[$key]);
			}
			return $array;
		}

		public function merge_arrays_by_value($array1, $array2, $value_key = '') {

			foreach ($array1 as $index => $item) {
				$filtered_array2 = wp_list_filter($array2, array(
					$value_key => $item[$value_key]
				));

				$first_match = current($filtered_array2);
				$array1[$index] = wp_parse_args($array1[$index], $first_match);
			}
			return $array1;
		}

		/**
		 * is plugin active?
		 * @return boolean
		 */
		function is_plugin_active($plugin_file) {
			if (empty($plugin_file)) {
				return false;
			}
			if (in_array($plugin_file, apply_filters('active_plugins', get_option('active_plugins')))) {
				return true;
			} else {
				return false;
			}
		}

		public function is_rest_request() {
			$rest_prefix = function_exists('rest_get_url_prefix') ? rest_get_url_prefix() : '';

			return !empty($rest_prefix) && strpos(sanitize_text_field($_SERVER['REQUEST_URI']), '/' . $rest_prefix) !== false;
		}

		public function is_wpse_page() {
			$out = false;

// Is a normal wp-admin page?
			if (isset($_GET['page']) && (strpos($_GET['page'], 'vgse') !== false || strpos($_GET['page'], 'vg_') !== false)) {
				$out = true;
			}

// Is an ajax request or form submission related to our plugin?
			if (isset($_REQUEST['action']) && (strpos($_REQUEST['action'], 'vgse') !== false)) {
				$out = true;
			}
			return apply_filters('vg_sheet_editor/is_wpse_page', $out);
		}

		public function is_editor_page() {
			$out = false;
			if (isset($_GET['page']) && strpos($_GET['page'], 'vgse-bulk-edit-') !== false) {
				$out = true;
			}
			return apply_filters('vg_sheet_editor/is_editor_page', $out);
		}

		/**
		 * Get handsontable cell content (html)
		 * @param int $id
		 * @param string $key
		 * @param string $type
		 * @return string
		 */
		function get_custom_modal_editor_cell_content($id, $key, $cell_args) {
			$post = VGSE()->helpers->get_current_provider()->get_item($id);
			$type = $cell_args['type'];

			if ($type !== 'metabox') {
				$existing_value = apply_filters('vg_sheet_editor/' . $type . '_cell_content/existing_value', maybe_unserialize($this->get_column_text_value($key, $id, $cell_args, $post->post_type)), $post, $key, $cell_args);
			}

			if (empty($existing_value)) {
				$existing_value = array();
			}

// We unserialize 3 times. In weird cases, some serialized values might be serialized multiple times
			if (is_string($existing_value)) {
				$existing_value = maybe_unserialize($existing_value);
			}
			if (is_string($existing_value)) {
				$existing_value = maybe_unserialize($existing_value);
			}

// This should be an array, if it's any other format we assume it's empty.
// I.e. Any other format is not compatible with WooCommerce so it won't work anyway.
			if (!is_array($existing_value)) {
				$existing_value = array();
			}

			$modal_settings = array_merge((array) $post, array('post_id' => $id), $cell_args);

			$out = '<a class="button button-' . $type . ' button-custom-modal-editor" data-existing="' . htmlentities(json_encode(array_values($existing_value)), ENT_QUOTES, 'UTF-8') . '" '
					. 'data-modal-settings="' . htmlentities(json_encode($modal_settings), ENT_QUOTES, 'UTF-8') . '"><i class="fa fa-edit"></i> ' . $modal_settings['edit_button_label'] . '</a>';

			return apply_filters('vg_sheet_editor/' . $type . '_cell_content/output', $out, $id, $key, $cell_args);
		}

		function get_gutenberg_cell_content() {
			global $wp_version;
			$post_type = VGSE()->helpers->get_provider_from_query_string();
			$post_content_settings = VGSE()->helpers->get_column_settings('post_content', $post_type);

			if (version_compare($wp_version, '5.0', '<') || empty($post_content_settings['formatted']['wpse_template_key']) || $post_content_settings['formatted']['wpse_template_key'] !== 'gutenberg_cell_template') {
				return '';
			}

// The cell is plain text, we use metabox here to make the JS work			
			$post_content_settings['type'] = 'metabox';
			$modal_settings = array_merge(array(
				'post_type' => $post_type,
				'post_title' => '{post_title}',
				'post_id' => '{id}',
					), $post_content_settings);

			$out = '<a class="button button-metabox button-custom-modal-editor button-gutenberg-post-content" data-existing="[]"  data-modal-settings="' . htmlentities(json_encode($modal_settings), ENT_QUOTES, 'UTF-8') . '"><i class="fa fa-edit"></i> ' . $modal_settings['edit_button_label'] . '</a>';

			return $out;
		}

		/**
		 * Get tinymce cell content (html)
		 * @param int $id
		 * @param string $key
		 * @param string $type
		 * @return string
		 */
		function get_tinymce_cell_content() {
			$out = '<a class="btn-popup-content button button-tinymce-{key}" data-type={type}" data-key="{key}" data-id="{id}"><i class="fa fa-edit"></i></a>';

			return apply_filters('vg_sheet_editor/tinymce_cell_content', $out);
		}

		/**
		 * Remove all post related actions.
		 * @param string $post_type
		 */
		function remove_all_post_actions($post_type) {

			foreach (array('transition_post_status', 'save_post', 'pre_post_update', 'add_attachment', 'edit_attachment', 'edit_post', 'post_updated', 'wp_insert_post', 'save_post_' . $post_type) as $act) {
				remove_all_actions($act);
			}
		}

		/**
		 * Get image gallery cell content (html)
		 * @param int $id
		 * @param string $key
		 * @param string $type
		 * @param bool $multiple
		 * @return string
		 */
		function get_gallery_cell_content($id, $key, $type, $current_value = null) {

			if (empty($current_value)) {
				if ($type === 'post_data') {
					$current_value = VGSE()->data_helpers->get_post_data($key, (int) $id);
				} else {
					$current_value = VGSE()->helpers->get_current_provider()->get_item_meta((int) $id, $key, true);
				}
			}

			$image_size = ( VGSE()->helpers->is_plain_text_request() ) ? 'full' : 'medium';
			$final_urls = array();
			$first_url = '';
			if (!empty($current_value)) {
				$current_value = ( is_array($current_value)) ? implode(',', $current_value) : $current_value;
				$file_ids = array_map('trim', explode(',', $current_value));
				foreach ($file_ids as $file_id) {
					if (is_numeric($file_id)) {
						$medium_url = wp_get_attachment_image_url($file_id, $image_size);
						$url = $medium_url ? $medium_url : wp_get_attachment_url($file_id);
						if (empty(VGSE()->options['dont_add_id_to_image_urls'])) {
							$url = esc_url(add_query_arg('wpId', $file_id, $url));
						}
					} elseif (strpos($file_id, WP_CONTENT_URL) !== false) {
						$url = $file_id;
						$file_id = VGSE()->helpers->get_attachment_id_from_url($file_id);
					} else {
						$url = $file_id;
						$file_id = '';
					}
// Fix. Needed when using cloudflare flexible ssl
					$final_urls[] = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? str_replace('http://', 'https://', $url) : $url;
				}
				$first_url = current($final_urls);
			}


			return implode(', ', $final_urls);
		}

		/**
		 * Initialize class
		 * @param string $post_type
		 */
		function init($post_type = null) {

			$this->post_type = (!empty($post_type) ) ? $post_type : $this->get_provider_from_query_string();
		}

		static function get_instance() {
			if (null == WP_Sheet_Editor_Helpers::$instance) {
				WP_Sheet_Editor_Helpers::$instance = new WP_Sheet_Editor_Helpers();
				WP_Sheet_Editor_Helpers::$instance->init();
			}
			return WP_Sheet_Editor_Helpers::$instance;
		}

		function get_allowed_post_types() {
			$post_types = apply_filters('vg_sheet_editor/allowed_post_types', array());
			$out = array_filter($post_types);
			return $out;
		}

		/**
		 * Dump
		 * 
		 * Dump any variable
		 * .
		 * @param int|string|array|object $var
		 * 
		 */
		function d($var) {
			if (defined('VGSE_DEBUG') && !VGSE_DEBUG) {
				return;
			}
			if (count(func_get_args()) > 1) {
				foreach (func_get_args() as $arg) {
					$this->d($arg);
				}
				return $this;
			}
			echo '<pre>';
			var_dump($var);
			echo '</pre>';
			return $this;
		}

		/**
		 * Dump and Die
		 * 
		 * @param int|string|array|object $var
		 */
		function dd($var) {
			if (defined('VGSE_DEBUG') && !VGSE_DEBUG) {
				return;
			}
			if (count(func_get_args()) > 1) {
				foreach (func_get_args() as $arg) {
					$this->d($arg);
				}
				die();
			}
			$this->d($var);
			die();
		}

		/**
		 * Get attachment ID from URL
		 * 
		 * It accepts auto-generated thumbnails URLs.
		 * 
		 * @global type $wpdb
		 * @param type $attachment_url
		 * @return type
		 */
		function get_attachment_id_from_url($attachment_url = '') {
			global $wpdb;
			$attachment_id = false;
// If there is no url, return.
			if (empty($attachment_url)) {
				return;
			}
// Get the upload directory paths
			$upload_dir_paths = wp_upload_dir();
// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
			if (false !== strpos($attachment_url, $upload_dir_paths['baseurl'])) {
// If this is the URL of an auto-generated thumbnail, get the URL of the original image
				$attachment_url = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif|webp)$)/i', '', $attachment_url);
// Remove the upload path base directory from the attachment URL
				$attachment_url = urldecode(str_replace($upload_dir_paths['baseurl'] . '/', '', $attachment_url));
// Finally, run a custom database query to get the attachment ID from the modified attachment URL
				$sql = $wpdb->prepare("SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url);
				$attachment_id = $wpdb->get_var($sql);
			}
			return $attachment_id;
		}

		/**
		 * Get post type from query string
		 * @return string
		 */
		function get_provider_from_query_string($always_return_post_type = true) {
			$current_post = null;
			if (!empty($_REQUEST['page']) && strpos($_REQUEST['page'], 'vgse-bulk-edit-') !== false) {
				$current_post = str_replace('vgse-bulk-edit-', '', sanitize_text_field($_REQUEST['page']));
			} elseif (!empty($_REQUEST['post_type'])) {
				$current_post = $this->sanitize_table_key($_REQUEST['post_type']);
// sheet_key is used in the REST API
			} elseif (!empty($_REQUEST['sheet_key'])) {
				$current_post = $this->sanitize_table_key($_REQUEST['sheet_key']);
			} elseif ($always_return_post_type) {
				$current_post = 'post';
			}
			$out = $this->sanitize_table_key(apply_filters('vg_sheet_editor/bootstrap/get_current_provider', $current_post));
			return $out;
		}

		function sanitize_table_key($key) {
			if (!empty($key) && !is_string($key)) {
				$key = '';
			}
			if (is_string($key)) {
				$key = preg_replace("/[^A-Za-z0-9\-\_]/", '', $key);
			}
			return $key;
		}

		/**
		 * Get post types as array
		 * @return array
		 */
		function post_type_array() {
			if (!is_array($this->post_type)) {
				$this->post_type = array($this->post_type);
			}
			return $this->post_type;
		}

		/**
		 * Is post type allowed?
		 * @param string $post_type
		 * @return boolean
		 */
		function is_post_type_allowed($post_type) {
			$allowed_post_types = VGSE()->helpers->get_allowed_post_types();
			return isset($allowed_post_types[$post_type]);
		}

		function safe_html($data) {
			if (is_string($data)) {
				$data = str_replace('&amp;', '&', wp_kses_post($data));
			} elseif (is_array($data)) {
				foreach ($data as $key => $value) {
					if ( current_user_can( 'unfiltered_html' ) && ! empty(VGSE()->options['be_allow_raw_content_unfiltered_html_capability']) && $key === 'post_content') {
						$data[$key] = $value;
					} else {
						$data[$key] = $this->safe_html($value);
					}
				}
			}
			return $data;
		}

		function safe_text_only($var) {
			if (is_array($var)) {
				return array_map(array($this, 'safe_text_only'), $var);
			} else {
				return is_scalar($var) ? sanitize_text_field($var) : $var;
			}
		}

		/**
		 * Get post type label from key
		 * @param string $post_type_key
		 * @return string
		 */
		function get_post_type_label($post_type_key) {

// Get all post type *names*, that are shown in the admin menu
			$post_types = $this->get_all_post_types();
			$name = (isset($post_types[$post_type_key]) ) ? $post_types[$post_type_key]->label : $post_type_key;

			return esc_html($name);
		}

		/**
		 * Get taxonomies registered with a post type
		 * @param string $post_type
		 * @return array
		 */
		function get_post_type_taxonomies($post_type) {
			$taxonomies = VGSE()->helpers->get_provider_editor($post_type)->provider->get_object_taxonomies($post_type);

			$out = array();
			if (!empty($taxonomies) && is_array($taxonomies)) {
				foreach ($taxonomies as $taxonomy) {
// We used to exclude taxonomies with show_in_ui=false, but we removed 
// the filter because some private taxonomies are used in the sheet, like the product visibility
					$out[] = $taxonomy;
				}
			}
			return $out;
		}

		/**
		 * Get all post types
		 * @return array
		 */
		function get_all_post_types($args = array(), $output = 'objects', $condition = 'OR') {
			$out = get_post_types($args, $output, $condition);
			$post_types = apply_filters('vg_sheet_editor/api/all_post_types', $out, $args, $output);

			$private_post_types = apply_filters('vg_sheet_editor/api/blacklisted_post_types', get_post_types(array('show_ui' => false)), $post_types, $args, $output);

			foreach ($post_types as $index => $post_type) {
				$post_type_key = ( is_object($post_type) ) ? $post_type->name : $post_type;

				$post_types[$post_type_key] = $post_type;
				if (!empty($private_post_types)) {
					if (in_array($post_type_key, $private_post_types)) {
						unset($post_types[$index]);
					}
				}
			}
			return $post_types;
		}

		/**
		 * Get all post types names
		 * @return array
		 */
		function get_all_post_types_names($include_private = true) {
			$args = array();

			if (!$include_private) {
				$args = array(
					'public' => true,
					'public_queryable' => true,
				);
			}

			$out = $this->get_all_post_types($args, 'names', 'OR');
			return $out;
		}

		/**
		 * Get single data from all taxonomies registered with a post type.
		 * @param string $post_type
		 * @param string $field_key
		 * @return mixed
		 */
		function get_post_type_taxonomies_single_data($post_type, $field_key) {

			$taxonomies = $this->get_post_type_taxonomies($post_type);
			$out = array();
			if (!empty($taxonomies) && is_array($taxonomies)) {
				foreach ($taxonomies as $taxonomy) {
					$out[] = $taxonomy->$field_key;
				}
			}
			return $out;
		}

		function is_happy_user() {
			$happy = false;

			$is_editor_page = $this->is_editor_page();
			$post_type = $this->get_provider_from_query_string(false);
			$extension = $this->get_extension_by_post_type($post_type);
			$is_backend = is_admin();
			$is_admin = current_user_can('manage_options');
			$used_sheet_multiple_times = (bool) get_user_meta(get_current_user_id(), 'wpse_has_saved_sheet', true);
			$free_post_types_that_might_be_happy = array('user', 'post', 'page');
			$mode = $this->get_plugin_mode();

			if ($is_editor_page && $is_admin && $is_backend && $extension && $used_sheet_multiple_times && ($mode === 'pro-plugin' || in_array($post_type, $free_post_types_that_might_be_happy))) {
				$happy = true;
			}
			return $happy;
		}

		function get_post_types_with_own_sheet() {
			$post_types_included_in_core = array('product');
			$exclude = array_unique(array_values(array_merge(VGSE()->helpers->array_flatten(wp_list_pluck(VGSE()->bundles, 'post_types')), VGSE()->helpers->array_flatten(wp_list_pluck(VGSE()->extensions, 'post_types')))));

			return apply_filters('vg_sheet_editor/custom_post_types/get_post_types_with_own_sheet', array_diff($exclude, $post_types_included_in_core));
		}

		function get_post_types_without_own_sheet() {

			$all_post_types = apply_filters('vg_sheet_editor/custom_post_types/get_all_post_types', VGSE()->helpers->get_all_post_types());
			$excluded = $this->get_post_types_with_own_sheet();
			$out = array();
			foreach ($all_post_types as $post_type) {
				if (in_array($post_type->name, $excluded)) {
					continue;
				}
				$out[$post_type->name] = $post_type->label;
			}
			return $out;
		}

		function get_extension_by_post_type($post_type) {
			$out = array();
			if (empty($post_type)) {
				return $out;
			}
			foreach (VGSE()->extensions as $extension) {
				if (!empty($extension['post_types']) && in_array($post_type, $extension['post_types'], true)) {
					$out = $extension;
					break;
				}
			}
			if (empty($out)) {
				foreach (VGSE()->bundles as $extension) {
					if (!empty($extension['post_types']) && in_array($post_type, $extension['post_types'], true)) {
						$out = $extension;
						break;
					}
				}
			}
			return $out;
		}

		/**
		 * Convert multidimensional arrays to unidimensional
		 * @param array $array
		 * @param array $return
		 * @return array
		 */
		function array_flatten($array) {
			$return = array();
			foreach ($array as $key => $value) {
				if (is_array($value)) {
					$return = array_merge($return, $this->array_flatten($value));
				} else {
					$return[$key] = $value;
				}
			}
			return $return;
		}

		/**
		 * Get a list of <option> tags of all enabled columns from a post type
		 * @param string $post_type
		 * @param array $filters
		 * @return string
		 */
		function get_post_type_columns_options($post_type, $filters = array(), $formula_format = false, $string = true, $just_data = false) {

			if (method_exists(VGSE()->helpers, 'get_unfiltered_provider_columns')) {
				$spreadsheet_columns = VGSE()->helpers->get_unfiltered_provider_columns($post_type);
			} else {
// Backwards compatibility
				$unfiltered_columns = WP_Sheet_Editor_Columns_Visibility::$unfiltered_columns;
				$spreadsheet_columns = isset($unfiltered_columns[$post_type]) ? $unfiltered_columns[$post_type] : array();
			}
			$out = array();
			if (!empty($spreadsheet_columns) && is_array($spreadsheet_columns)) {
				if (!empty($filters)) {
					if (empty($filters['operator'])) {
						$filters['operator'] = 'AND';
					}
					$spreadsheet_columns = wp_list_filter($spreadsheet_columns, $filters['conditions'], $filters['operator']);
				}
				foreach ($spreadsheet_columns as $item => $value) {
					if (empty($value['value_type'])) {
						$value['value_type'] = 'text';
					}
					$name = $value['title'];
					$key = $item;

					if ($formula_format) {
						$name = $value['title'] . ' ($' . $item . '$)';
						$key = '$' . $item . '$';
					}

					if ($just_data) {
						$out[$key] = $value;
					} else {
						$out[$key] = '<option value="' . $key . '" data-value-type="' . $value['value_type'] . '">' . $name . '</option>';
					}
				}
			}

			return ( $string ) ? implode($out) : $out;
		}

		/**
		 * Increase editions counter. This is used to keep track of 
		 * how many posts have been edited using the spreadsheet editor.
		 * 
		 * This information is displayed on the dashboard widget.
		 */
		function increase_counter($key = 'editions', $count = 1) {
			$allowed_keys = array(
				'editions',
				'processed',
			);

			if (!in_array($key, $allowed_keys)) {
				return;
			}
			$counter = (int) get_option('vgse_' . $key . '_counter', 0);

			$counter += (int) $count;

			update_option('vgse_' . $key . '_counter', $counter);
		}

	}

}
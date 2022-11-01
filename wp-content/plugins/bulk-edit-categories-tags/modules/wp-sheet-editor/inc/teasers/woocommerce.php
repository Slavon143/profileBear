<?php
if (!class_exists('WP_Sheet_Editor_WooCommerce_Teaser')) {

	/**
	 * Display woocommerce item in the toolbar to tease users of the free 
	 * version into purchasing the premium plugin.
	 */
	class WP_Sheet_Editor_WooCommerce_Teaser {

		static private $instance = false;
		var $post_type = null;
		var $allowed_columns = null;
		var $wc_lookuptable_after_save_synced = array();
		var $variation_post_type = 'product_variation';

		private function __construct() {
			
		}

		function init() {
			if (!is_admin()) {
				return;
			}
			$this->post_type = apply_filters('vg_sheet_editor/woocommerce/product_post_type_key', 'product');

			if (class_exists('WP_Sheet_Editor_WooCommerce') || !class_exists('WooCommerce')) {
				return;
			}

			$this->allowed_columns = apply_filters('vg_sheet_editor/woocommerce/teasers/allowed_columns', array(
				'ID',
				'post_title',
				'_sku',
				'_regular_price',
				'_sale_price',
				'_manage_stock',
				'_stock_status',
				'_stock',
				'view_post',
				'open_wp_editor',
				'post_status',
				'post_modified',
				'post_date',
				'_length',
				'_height',
				'_width',
				'_weight',
			));
			$this->variation_only_columns = array(
			);
			$this->variation_columns = apply_filters('vg_sheet_editor/woocommerce/teasers/allowed_variation_columns', array(
				'ID',
				'_sku',
				'_regular_price',
				'_sale_price',
				'_manage_stock',
				'_stock_status',
				'_stock',
				'_length',
				'_height',
				'_width',
				'_weight',
			));

			add_filter('vg_sheet_editor/allowed_post_types', array($this, 'allow_product_post_type'));
			add_filter('vg_sheet_editor/add_new_posts/create_new_posts', array($this, 'create_new_products'), 10, 3);
			add_action('vg_sheet_editor/editor/before_init', array($this, 'register_columns'), 15);
			add_action('vg_sheet_editor/editor/before_init', array($this, 'filter_columns_settings'), 60);
			add_filter('vg_sheet_editor/custom_columns/teaser/allow_to_lock_column', array($this, 'dont_lock_allowed_columns'), 10, 2);
			add_action('woocommerce_variable_product_before_variations', array($this, 'render_variations_metabox_teaser'));
			add_action('vg_sheet_editor/editor_page/after_console_text', array($this, 'notify_variations_arent_allowed'), 30, 1);
			add_action('vg_sheet_editor/save_rows/after_saving_post', array($this, 'product_updated_on_spreadsheet'), 10, 4);
			add_filter('vg_sheet_editor/js_data', array($this, 'watch_cells_to_lock'), 10, 2);

			add_action('vg_sheet_editor/load_rows/found_posts', array(
				$this,
				'maybe_include_variations_posts'
					), 10, 2);

			// Filter load_rows to include variations if toolbar item is enabled.
			// The general fields will contain the same info as the parent post.
			add_action('vg_sheet_editor/load_rows/output', array(
				$this,
				'maybe_modify_variations_output'
					), 10, 3);

			// When loading posts, disable product columns in variations
			add_action('vg_sheet_editor/load_rows/allowed_post_columns', array(
				$this,
				'disable_general_columns_for_variations'
					), 10, 2);
			// Filter load_rows output to remove data in general columns and display a lock icon instead, also modify some columns values
			add_action('vg_sheet_editor/load_rows/output', array(
				$this,
				'maybe_lock_general_columns_in_variations'
					), 10, 3);
			// Force WC to generate variation titles with all attributes, even when having a lot of attributes
			// because the spreadsheet needs it for "delete duplicates" based on title and some search functionality
			add_filter('woocommerce_product_variation_title_include_attributes', '__return_true', 99999);
		}

		function watch_cells_to_lock($data, $post_type) {
			if ($post_type === $this->post_type) {
				$data['watch_cells_to_lock'] = true;
			}
			return $data;
		}

		/**
		 * Modify variations fields before returning the spreadsheet rows.
		 * @param type $rows
		 * @param array $wp_query
		 * @param array $spreadsheet_columns
		 * @return array
		 */
		function maybe_modify_variations_output($rows, $wp_query, $spreadsheet_columns) {

			if (empty($rows) || !is_array($rows) || VGSE()->helpers->get_provider_from_query_string() !== $this->post_type) {
				return $rows;
			}

			$args = apply_filters('vg_sheet_editor/woocommerce/variations/modify_variation_output_args', array(
				'add_variation_title_prefix' => true,
					), $rows, $wp_query, $spreadsheet_columns);

			$parent_titles = array();
			foreach ($rows as $row_index => $post) {
				if ($post['post_type'] !== $this->variation_post_type) {
					continue;
				}
				$post_obj = get_post($post['ID']);
				$rows[$row_index]['post_status'] = 'publish';

				// Set variation titles
				if ($args['add_variation_title_prefix']) {
					$rows[$row_index]['post_title'] = sprintf(__('Variation: %s', VGSE()->textname), esc_html($post_obj->post_title));
					// WC doesn't add the attribute names to some variation titles, so we'll add them ourselves when loading the rows
					if (!isset($parent_titles[$post_obj->post_parent])) {
						$parent_titles[$post_obj->post_parent] = get_post_field('post_title', $post_obj->post_parent);
					}
					if ($post_obj->post_title === $parent_titles[$post_obj->post_parent]) {
						$rows[$row_index]['post_title'] .= ' - ' . wc_get_formatted_variation(wc_get_product($post['ID']), true, false);
					}
				} else {
					$rows[$row_index]['post_title'] = $post_obj->post_title;
				}
			}

			return $rows;
		}

		function get_variation_whitelisted_columns() {
			return $this->variation_columns;
		}

		function get_product_type($product_id) {
			return VGSE()->helpers->get_current_provider()->get_item_terms($product_id, 'product_type');
		}

		/**
		 * Add a lock icon to the cells enabled for variations or products.
		 * 
		 * @param array $posts Rows for display in spreadsheet
		 * @param array $wp_query Arguments used to query the posts.
		 * @param array $spreadsheet_columns
		 * @param array $request_data Data received in the ajax request
		 * @return array
		 */
		function maybe_lock_general_columns_in_variations($posts, $wp_query, $spreadsheet_columns) {
			if (VGSE()->helpers->get_provider_from_query_string() !== $this->post_type || empty($posts) || !is_array($posts) || VGSE()->helpers->is_plain_text_request()) {
				return $posts;
			}
			if (function_exists('WPSE_Profiler_Obj')) {
				WPSE_Profiler_Obj()->record('Before ' . __FUNCTION__);
			}

			$products = wp_list_filter($posts, array(
				'post_type' => $this->post_type
			));
			// We need at least one parent product to detect the parent vs variations columns and lock them
			if (empty($products)) {
				return $posts;
			}
			$first_product_keys = array_keys(current($products));

			$whitelist_variations = $this->get_variation_whitelisted_columns();
			$columns_with_visibility = array_keys($spreadsheet_columns);

			// Lock keys on variation rows for fields used in parent products that are not used in variations
			$locked_keys_in_variations = array_intersect(array_diff($first_product_keys, $whitelist_variations), $columns_with_visibility);

			// Lock keys on parent rows for fields used in variations that are not used by parent products
			$locked_keys_in_general = array_intersect(array_diff($whitelist_variations, $first_product_keys), $columns_with_visibility);

			$locked_keys_in_variations = apply_filters('vg_sheet_editor/woocommerce/locked_keys_in_variations', $locked_keys_in_variations, $whitelist_variations);
			$lock_icon = '<i class="fa fa-lock vg-cell-blocked vg-variation-lock"></i>';

			foreach ($posts as $index => $post) {


				if ($post['post_type'] === $this->post_type) {
					$locked_keys = $locked_keys_in_general;
				} else {
					$locked_keys = $locked_keys_in_variations;
				}
				if (isset($posts[$index]['_stock'])) {
					$posts[$index]['_stock'] = (int) $posts[$index]['_stock'];
				}
				$product_type = !empty($post['product_type']) ? $post['product_type'] : $this->get_product_type($post['ID']);
				// We are locking keys here because the automatic locking works with fields 
				// used by all parent products or all variations, not fields used by some parents only.
				// That's why in this case, we need to check the product type and disable them manually
				if ($product_type === 'variable') {
					$locked_keys[] = '_regular_price';
					$locked_keys[] = '_sale_price';
					$locked_keys[] = '_sale_price_dates_from';
					$locked_keys[] = '_sale_price_dates_to';
				}
				$posts[$index] = array_merge($posts[$index], array_fill_keys(array_diff($locked_keys, array_keys($post)), ''));
				foreach ($locked_keys as $locked_key) {

					if (strpos($posts[$index][$locked_key], 'vg-cell-blocked') !== false) {
						continue;
					}
					if (in_array($locked_key, array('title', 'post_title'))) {
						$posts[$index][$locked_key] = $lock_icon . ' ' . $posts[$index][$locked_key];
					} else {
						$posts[$index][$locked_key] = $lock_icon;
					}
				}
			}

			if (function_exists('WPSE_Profiler_Obj')) {
				WPSE_Profiler_Obj()->record('After ' . __FUNCTION__);
			}
			return $posts;
		}

		function get_variation_only_columns() {
			return $this->variation_only_columns;
		}

		/**
		 * Make sure that product variations dont have the columns exclusive to general products.
		 * @param array $columns
		 * @param obj $post
		 * @return array
		 */
		function disable_general_columns_for_variations($columns, $post) {

			if ($post->post_type !== $this->variation_post_type && $post->post_type !== $this->post_type) {
				return $columns;
			}

			if ($post->post_type === $this->variation_post_type) {
				$disallowed = array_diff(array_keys($columns), $this->get_variation_whitelisted_columns());
			} else {
				$disallowed = $this->get_variation_only_columns();
			}

			$new_columns = array();

			foreach ($columns as $key => $column) {
				if (!in_array($key, $disallowed)) {
					$new_columns[$key] = $column;
				}
			}

			return $new_columns;
		}

		/**
		 * Include variations posts to the posts list before processing.
		 * 
		 * Note. The search variations logic is very good because it allows pagination by variations
		 * but we can't use it without searching because it would exclude the non-variable products.
		 * 
		 * @param type $posts
		 * @param type $wp_query
		 * @param array $request_data Data received in the ajax request
		 * @return array
		 */
		function maybe_include_variations_posts($posts, $wp_query) {

			if ($wp_query['post_type'] !== $this->post_type || empty($posts) || !is_array($posts)) {
				return $posts;
			}

			$posts_to_inject_query = new WP_Query(array(
				'post_type' => 'product_variation',
				'nopaging' => true,
				'post_parent__in' => wp_list_pluck($posts, 'ID'),
				'orderby' => array('menu_order' => 'ASC', 'ID' => 'ASC'),
			));

			if (!$posts_to_inject_query->have_posts()) {
				return $posts;
			}

			// Cache list of variations for future use
			$this->posts_to_inject_query = $posts_to_inject_query;

			$new_posts = array();
			$wc_default_non_variable_types = array('simple', 'grouped', 'external');

			foreach ($posts as $post) {
				$new_posts[] = $post;

				if (in_array($this->get_product_type($post->ID), $wc_default_non_variable_types, true)) {
					continue;
				}

				$product_variations = wp_list_filter($posts_to_inject_query->posts, array(
					'post_parent' => $post->ID
				));

				$new_posts = array_merge($new_posts, $product_variations);
			}
			return $new_posts;
		}

		function product_updated_on_spreadsheet($product_id, $item, $data, $post_type) {
			if (!in_array($post_type, array($this->post_type))) {
				return;
			}
			$this->_sync_product_lookup_table($product_id, array_keys($item));
		}

		/**
		 * Sync with product lookup table
		 * 
		 * WC 3.6 introduces a new lookup table, we need to sync some fields after every change.
		 * @see https://woocommerce.wordpress.com/2019/04/01/performance-improvements-in-3-6/
		 */
		function _sync_product_lookup_table($product_id, $modified_data = array()) {
			$product_lookup_keys = array('_price', '_regular_price', '_sale_price', '_sale_price_dates_from', '_sale_price_dates_to', '_sku', '_stock', '_stock_status', '_manage_stock', '_downloadable', '_virtual', '_thumbnail_id');
			if (array_intersect($modified_data, $product_lookup_keys) && !in_array($product_id, $this->wc_lookuptable_after_save_synced)) {

				// We resave the regular price to force WC to execute the internal, protected method update_lookup_table()
				$regular_price = get_post_meta($product_id, '_regular_price', true);

				$product = wc_get_product($product_id);
				$product->set_regular_price(999999);
				$product->save();
				$product->set_regular_price($regular_price);
				$product->save();
				$this->wc_lookuptable_after_save_synced[] = $product_id;
			}
		}

		function notify_variations_arent_allowed($post_type) {
			if ($post_type === $this->post_type) {
				echo '<span class="wpse-lite-version-message">';
				_e('. <b>Lite version.</b> Showing all products and all fields as columns, 15 columns are editable and the rest are read only. <br><b>Upgrade:</b> Edit in Excel/Google Sheets, export, import, bulk edit thousands of products at once.', VGSE()->textname);
				echo '</span>';
			}
		}

		function render_variations_metabox_teaser() {
			?>
			<style>
				.wpse-variation-metabox-teaser {
					padding: 10px;
				}
			</style>
			<div class="notice-success is-dismissible wpse-variation-metabox-teaser">
				<?php printf(__('<b>Tip from WP Sheet Editor:</b> You can view and edit Product Variations in a spreadsheet, bulk edit, make advanced searches, edit hundreds of variations at once, copy variations to multiple products, etc. <a href="%s" class="" target="_blank">Download Plugin</a>', VGSE()->textname), 'https://wpsheeteditor.com/extensions/woocommerce-spreadsheet/?utm_source=wp-admin&utm_medium=variations-metabox&utm_campaign=products'); ?>
			</div>
			<?php
		}

		function dont_lock_allowed_columns($allowed_to_lock, $column_key) {
			if (in_array($column_key, $this->allowed_columns, true)) {
				$allowed_to_lock = false;
			}

			return $allowed_to_lock;
		}

		/**
		 * Modify spreadsheet columns settings.
		 * 
		 * It changes the names and settings of some columns.
		 * @param array $spreadsheet_columns
		 * @param string $post_type
		 * @param bool $exclude_formatted_settings
		 * @return array
		 */
		function filter_columns_settings($editor) {
			$post_type = $this->post_type;

			if ($editor->provider->key === 'user') {
				return;
			}

			if (defined('VGSE_WC_TEASER_LIMIT_COLUMNS') && !VGSE_WC_TEASER_LIMIT_COLUMNS) {
				return;
			}
			if ($post_type !== $this->post_type) {
				return;
			}

			// Adapt core columns to woocommerce format
			$editor->args['columns']->register_item('post_excerpt', $post_type, array(
				'title' => __('Short description', VGSE()->textname),
				'default_title' => __('Short description', VGSE()->textname),
				'column_width' => 150
					), true);
			$editor->args['columns']->register_item('comment_status', $post_type, array(
				'title' => __('Enable reviews', VGSE()->textname),
				'default_title' => __('Enable reviews', VGSE()->textname),
					), true);

			$spreadsheet_columns = $editor->args['columns']->get_provider_items($post_type);
			// Increase column width for disabled columns, so the "premium" message fits
			foreach ($spreadsheet_columns as $key => $column) {
				if (!in_array($key, $this->allowed_columns)) {
					$editor->args['columns']->register_item($key, $post_type, array(
						'column_width' => $column['column_width'] + 80,
						'is_locked' => true,
						'lock_template_key' => 'lock_cell_template_pro',
							), true);
				}
			}
		}

		/**
		 * Register spreadsheet columns
		 */
		function register_columns($editor) {
			$post_type = $this->post_type;

			if ($editor->provider->key === 'user') {
				return;
			}

			$product_type_tax = 'product_type';
			$editor->args['columns']->register_item($product_type_tax, $post_type, array(
				'data_type' => 'post_terms',
				'unformatted' => array('data' => $product_type_tax),
				'column_width' => 150,
				'title' => __('Type', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => $product_type_tax, 'type' => 'autocomplete', 'source' => 'loadTaxonomyTerms'),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));
			$editor->args['columns']->register_item('_sku', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_sku'),
				'column_width' => 150,
				'title' => __('SKU', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_sku', 'renderer' => 'html'),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_regular_price', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_regular_price'),
				'column_width' => 150,
				'title' => __('Regular Price', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_regular_price'),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
				'value_type' => 'number',
			));

			$editor->args['columns']->register_item('_sale_price', $post_type, array(
				'value_type' => 'number',
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_sale_price'),
				'column_width' => 150,
				'title' => __('Sale Price', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_sale_price', 'renderer' => 'html'),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_weight', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_weight'),
				'column_width' => 100,
				'title' => __('Weight', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_weight', 'renderer' => 'html'),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_width', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_width'),
				'column_width' => 100,
				'title' => __('Width', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_width', 'renderer' => 'html'),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_height', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_height'),
				'column_width' => 100,
				'title' => __('Height', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_height', 'renderer' => 'html'),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_length', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_length'),
				'column_width' => 100,
				'title' => __('Length', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_length', 'renderer' => 'html'),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));
			$editor->args['columns']->register_item('_manage_stock', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_manage_stock'),
				'column_width' => 150,
				'title' => __('Manage stock', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array(
					'data' => '_manage_stock',
					'type' => 'checkbox',
					'checkedTemplate' => 'yes',
					'uncheckedTemplate' => 'no',
				),
				'default_value' => 'no',
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_stock_status', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_stock_status'),
				'column_width' => 150,
				'title' => __('Stock status', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array(
					'data' => '_stock_status',
					'type' => 'checkbox',
					'checkedTemplate' => 'instock',
					'uncheckedTemplate' => 'outofstock',
				),
				'default_value' => 'instock',
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_stock', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_stock'),
				'column_width' => 75,
				'title' => __('Stock', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_stock'),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_visibility', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_visibility'),
				'column_width' => 150,
				'title' => __('Visibility', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_visibility', 'editor' => 'select', 'selectOptions' => array('visible', 'catalog', 'search', 'hidden')),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_product_image_gallery', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_product_image_gallery', 'renderer' => 'html', 'readOnly' => true),
				'column_width' => 300,
				'supports_formulas' => true,
				'title' => __('Gallery', VGSE()->textname),
				'type' => 'boton_gallery_multiple',
				'formatted' => array('data' => '_product_image_gallery', 'renderer' => 'html', 'readOnly' => true),
				'allow_to_hide' => true,
				'allow_to_save' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_downloadable', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_downloadable'),
				'column_width' => 150,
				'title' => __('Downloadable', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_downloadable',
					'type' => 'checkbox',
					'checkedTemplate' => 'yes',
					'uncheckedTemplate' => 'no',
				),
				'default_value' => 'no',
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_virtual', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_virtual'),
				'column_width' => 150,
				'title' => __('Virtual', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_virtual',
					'type' => 'checkbox',
					'checkedTemplate' => 'yes',
					'uncheckedTemplate' => 'no',
				),
				'default_value' => 'no',
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_sale_price_dates_from', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_sale_price_dates_from'),
				'column_width' => 150,
				'title' => __('Sales price date from', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_sale_price_dates_from', 'type' => 'date', 'dateFormat' => 'YYYY-MM-DD', 'correctFormat' => true, 'defaultDate' => '', 'datePickerConfig' => array('firstDay' => 0, 'showWeekNumber' => true, 'numberOfMonths' => 1)),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_sale_price_dates_to', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_sale_price_dates_to'),
				'column_width' => 150,
				'title' => __('Sales price date to', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_sale_price_dates_to', 'type' => 'date', 'dateFormat' => 'YYYY-MM-DD', 'correctFormat' => true, 'defaultDate' => '', 'datePickerConfig' => array('firstDay' => 0, 'showWeekNumber' => true, 'numberOfMonths' => 1)),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));
			$editor->args['columns']->register_item('_sold_individually', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_sold_individually'),
				'column_width' => 150,
				'title' => __('Sold individually', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_sold_individually',
					'type' => 'checkbox',
					'checkedTemplate' => 'yes',
					'uncheckedTemplate' => 'no',
				),
				'default_value' => 'no',
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));
			$editor->args['columns']->register_item('_featured', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_featured'),
				'column_width' => 150,
				'title' => __('is featured?', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_featured',
					'type' => 'checkbox',
					'checkedTemplate' => 'yes',
					'uncheckedTemplate' => 'no',
				),
				'default_value' => 'no',
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));
			$editor->args['columns']->register_item('_backorders', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_backorders'),
				'column_width' => 150,
				'title' => __('Allow backorders', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_backorders',
					'editor' => 'select',
					'selectOptions' => array(
						'no' => __('Do not allow', 'woocommerce'),
						'notify' => __('Allow, but notify customer', 'woocommerce'),
						'yes' => __('Allow', 'woocommerce'),
					)
				),
				'default_value' => 'no',
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_purchase_note', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_purchase_note'),
				'column_width' => 250,
				'title' => __('Purchase note', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_purchase_note',),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$shipping_tax_name = 'product_shipping_class';
			$editor->args['columns']->register_item($shipping_tax_name, $post_type, array(
				'data_type' => 'post_terms',
				'unformatted' => array('data' => $shipping_tax_name),
				'column_width' => 150,
				'title' => __('Shipping class', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => $shipping_tax_name, 'type' => 'autocomplete', 'source' => 'loadTaxonomyTerms'),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));

			$editor->args['columns']->register_item('_download_limit', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_download_limit'),
				'column_width' => 150,
				'title' => __('Download limit', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_download_limit',),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
				'value_type' => 'number',
			));
			$editor->args['columns']->register_item('_download_expiry', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_download_expiry'),
				'column_width' => 150,
				'title' => __('Download expiry', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_download_expiry',),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
				'value_type' => 'number',
			));
			$editor->args['columns']->register_item('_download_type', $post_type, array(
				'data_type' => 'meta_data',
				'unformatted' => array('data' => '_download_type'),
				'column_width' => 250,
				'title' => __('Download type', VGSE()->textname),
				'type' => '',
				'supports_formulas' => true,
				'formatted' => array('data' => '_download_type', 'editor' => 'select', 'selectOptions' => array(
						'' => __('Standard Product', 'woocommerce'),
						'application' => __('Application/Software', 'woocommerce'),
						'music' => __('Music', 'woocommerce'),
					)),
				'allow_to_hide' => true,
				'allow_to_rename' => true,
			));
			$editor->args['columns']->register_item('_downloadable_files', $post_type, array(
				'data_type' => null,
				'unformatted' => array('data' => '_downloadable_files', 'renderer' => 'html', 'readOnly' => true),
				'column_width' => 120,
				'title' => __('Download files', VGSE()->textname),
				'type' => 'handsontable',
				'edit_button_label' => __('Edit files', VGSE()->textname),
				'edit_modal_id' => 'vgse-download-files',
				'edit_modal_title' => __('Download files', VGSE()->textname),
				'edit_modal_description' => '<div class="vgse-copy-files-from-product-wrapper"><label>' . __('Copy files from this product: (You need to save the changes afterwards.)', VGSE()->textname) . ' </label><br/><select name="copy_from_product" data-remote="true" data-min-input-length="4" data-action="vgse_find_post_by_name" data-post-type="' . $this->post_type . '" data-nonce="' . wp_create_nonce('bep-nonce') . '" data-placeholder="' . __('Select product...', VGSE()->textname) . '" class="select2 vgse-copy-files-from-product">
									<option></option>
								</select><a href="#" class="button vgse-copy-files-from-product-trigger">Copy</a></div>',
				'edit_modal_local_cache' => true,
				'edit_modal_save_action' => 'vgse_save_download_files',
				'handsontable_columns' => array(
					$this->post_type => array(
						array(
							'data' => 'name'
						),
						array(
							'data' => 'file'
						),
					),
					'product_variation' => array(
						array(
							'data' => 'name'
						),
						array(
							'data' => 'file'
						),
					)),
				'handsontable_column_names' => array(
					$this->post_type => array(__('Name', VGSE()->textname), __('File (url or path)', VGSE()->textname)),
					'product_variation' => array(__('Name', VGSE()->textname), __('File (url or path)', VGSE()->textname)),
				),
				'handsontable_column_widths' => array(
					$this->post_type => array(160, 300),
					'product_variation' => array(160, 300),
				),
				'supports_formulas' => false,
				'formatted' => array('data' => '_downloadable_files', 'renderer' => 'html', 'readOnly' => true),
				'allow_to_hide' => true,
				'allow_to_save' => false,
				'allow_to_rename' => false,
			));

			$editor->args['columns']->register_item('_variation_description', $post_type, array(
				'key' => '_variation_description',
				'data_type' => 'post_meta',
				'unformatted' => array(
					'data' => '_variation_description'
				),
				'column_width' => 175,
				'title' => __('Variation description', VGSE()->textname),
				'type' => '',
				'supports_formulas' => false,
				'formatted' => array(
					'data' => '_variation_description',
				),
				'default_value' => '',
				'allow_to_hide' => true,
				'allow_to_save' => true,
				'allow_to_rename' => false
			));
			$editor->args['columns']->register_item('_vgse_variation_enabled', $post_type, array(
				'key' => '_vgse_variation_enabled',
				'data_type' => 'post_data',
				'unformatted' => array(
					'data' => '_vgse_variation_enabled'
				),
				'column_width' => 140,
				'title' => __('Variation enabled?', VGSE()->textname),
				'type' => '',
				'supports_formulas' => false,
				'formatted' => array(
					'data' => '_vgse_variation_enabled',
					'type' => 'checkbox',
					'checkedTemplate' => 'on',
					'uncheckedTemplate' => ''
				),
				'default_value' => 'on',
				'allow_to_hide' => true,
				'allow_to_save' => false,
				'allow_to_rename' => false
			));

			$editor->args['columns']->register_item('default_attributes', $post_type, array(
				'data_type' => null,
				'unformatted' => array('data' => 'default_attributes', 'renderer' => 'html', 'readOnly' => true),
				'column_width' => 160,
				'title' => __('Default attributes', VGSE()->textname),
				'type' => 'handsontable',
				'edit_button_label' => __('Default attributes', VGSE()->textname),
				'edit_modal_id' => 'vgse-default-attributes',
				'edit_modal_title' => __('Default attributes', VGSE()->textname),
				'edit_modal_description' => sprintf(__('Note: Separate values with the character %s<br/>The product must be variable and have existing variations for this to work, otherwise the default attributes won\'t be saved.'), WC_DELIMITER),
				'edit_modal_save_action' => 'vgse_save_default_attributes',
				'edit_modal_get_action' => 'vgse_save_default_attributes',
				'edit_modal_local_cache' => false,
				'handsontable_columns' => array(
					$this->post_type => array(
						array(
							'data' => 'name'
						),
						array(
							'data' => 'option'
						),
					)),
				'handsontable_column_names' => array(
					$this->post_type => array(
						__('Name', VGSE()->textname),
						__('Value', VGSE()->textname)
					)
				),
				'handsontable_column_widths' => array(
					$this->post_type => array(160, 300),
				),
				'supports_formulas' => false,
				'formatted' => array('data' => 'default_attributes', 'renderer' => 'html', 'readOnly' => true),
				'allow_to_hide' => true,
				'allow_to_save' => false,
				'allow_to_rename' => false,
			));
		}

		/**
		 * Create new products using WC API
		 * @param array $post_ids
		 * @param str $post_type
		 * @param int $number
		 * @return array Post ids
		 */
		public function create_new_products($post_ids, $post_type, $number) {

			if ($post_type !== $this->post_type || !empty($post_ids)) {
				return $post_ids;
			}

			for ($i = 0; $i < $number; $i++) {
				$api_response = VGSE()->helpers->create_rest_request('POST', '/wc/v1/products', array(
					'name' => __('...', VGSE()->textname),
					'status' => 'draft'
				));

				if ($api_response->status === 200 || $api_response->status === 201) {
					$api_data = $api_response->get_data();
					$post_ids[] = $api_data['id'];
				}
			}

			return $post_ids;
		}

		/**
		 * Allow woocomerce product post type
		 * @param array $post_types
		 * @return array
		 */
		function allow_product_post_type($post_types) {

			if (!isset($post_types[$this->post_type]) && class_exists('WP_Sheet_Editor_Dist')) {
				$post_types[$this->post_type] = VGSE()->helpers->get_post_type_label($this->post_type);
			}
			return $post_types;
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * 
		 */
		static function get_instance() {
			if (null == WP_Sheet_Editor_WooCommerce_Teaser::$instance) {
				WP_Sheet_Editor_WooCommerce_Teaser::$instance = new WP_Sheet_Editor_WooCommerce_Teaser();
				WP_Sheet_Editor_WooCommerce_Teaser::$instance->init();
			}
			return WP_Sheet_Editor_WooCommerce_Teaser::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}


add_action('vg_sheet_editor/initialized', 'vgse_init_woocommerce_teaser');

if (!function_exists('vgse_init_woocommerce_teaser')) {

	function vgse_init_woocommerce_teaser() {
		WP_Sheet_Editor_WooCommerce_Teaser::get_instance();
	}

}

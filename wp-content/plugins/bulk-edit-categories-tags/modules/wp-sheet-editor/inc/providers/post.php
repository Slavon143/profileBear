<?php

// Fix. If they update one plugin and use an old version of another,
// the Abstract class might not exist and they will get fatal errors.
// So we make sure it loads the class from the current plugin if it's missing
// This can be removed in a future update.
if (!class_exists('VGSE_Provider_Abstract')) {
	require_once 'abstract.php';
}

class VGSE_Provider_Post extends VGSE_Provider_Abstract {

	static private $instance = false;
	var $key = 'post';
	var $is_post_type = true;
	static $data_store = array();

	private function __construct() {
		
	}

	function _get_allowed_ids_for_edit($row_ids, $post_type) {
		global $wpdb;
		$ids_in_query_placeholders = implode(', ', array_fill(0, count($row_ids), '%d'));
		$prepare_data = array_merge($row_ids, array(get_current_user_id(), $post_type));
		$prepare_sql = "SELECT ID FROM $wpdb->posts WHERE ID IN ($ids_in_query_placeholders) AND post_author = %s AND post_type = %s";
		$allowed_row_ids = array_map('intval', $wpdb->get_col($wpdb->prepare($prepare_sql, $prepare_data)));
		return $allowed_row_ids;
	}

	function filter_rows_before_edit($data, $post_type) {
		$post_type_object = get_post_type_object($post_type);
		if (!$post_type_object || current_user_can($post_type_object->cap->edit_others_posts) || empty($data)) {
			return $data;
		}
		$first_row = current($data);
		if (is_numeric($first_row)) {
			$row_ids = array_filter(array_map('intval', $data));
		} elseif (is_array($first_row)) {
			$row_ids = array_filter(array_map('intval', array_map(array(VGSE()->helpers, 'sanitize_integer'), wp_list_pluck($data, 'ID'))));
		}
		if (empty($row_ids)) {
			return $data;
		}
		$allowed_row_ids = $this->_get_allowed_ids_for_edit($row_ids, $post_type);
		foreach ($data as $row_index => $row) {
			if (is_array($row)) {
				$id = (empty($row['ID'])) ? 0 : (int) VGSE()->helpers->sanitize_integer($row['ID']);
			} else {
				$id = (int) $row;
			}
			if ($id && !in_array($id, $allowed_row_ids, true)) {
				unset($data[$row_index]);
			}
		}

		return $data;
	}

	function get_provider_read_capability($post_type_key) {
		return $this->get_provider_edit_capability($post_type_key);
	}

	function delete_meta_key($old_key, $post_type) {
		global $wpdb;
		$meta_table_name = $this->get_meta_table_name($post_type);

		$wc_product_post_type = apply_filters('vg_sheet_editor/woocommerce/product_post_type_key', 'product');
		if ($post_type === $wc_product_post_type && function_exists('WC')) {
			$post_type = array($wc_product_post_type, 'product_variation');
		}
		if (is_string($post_type)) {
			$post_type = array($post_type);
		}

		$post_types_in_query_placeholders = implode(', ', array_fill(0, count($post_type), '%s'));
		$sql = $wpdb->prepare("DELETE pm FROM $meta_table_name pm INNER JOIN $wpdb->posts p ON 
p.ID = pm.post_id 
WHERE p.post_type IN ($post_types_in_query_placeholders) 
AND pm.meta_key = %s", array_merge($post_type, array($old_key)));

		$modified = $wpdb->query($sql);
		return $modified;
	}

	function rename_meta_key($old_key, $new_key, $post_type) {
		global $wpdb;
		$meta_table_name = $this->get_meta_table_name($post_type);
		$wc_product_post_type = apply_filters('vg_sheet_editor/woocommerce/product_post_type_key', 'product');
		if ($post_type === $wc_product_post_type && function_exists('WC')) {
			$post_type = array($wc_product_post_type, 'product_variation');
		}
		if (is_string($post_type)) {
			$post_type = array($post_type);
		}
		$post_types_in_query_placeholders = implode(', ', array_fill(0, count($post_type), '%s'));
		$sql = $wpdb->prepare("UPDATE $meta_table_name pm LEFT JOIN $wpdb->posts p ON 
p.ID = pm.post_id 
SET pm.meta_key = %s
WHERE p.post_type IN ($post_types_in_query_placeholders) 
AND pm.meta_key = %s", array_merge(array($new_key), $post_type, array($old_key)));

		$modified = $wpdb->query($sql);
		return $modified;
	}

	function get_provider_edit_capability($post_type_key) {
		if (!post_type_exists($post_type_key)) {
			return false;
		}
		$post_type_object = get_post_type_object($post_type_key);
		return $post_type_object->cap->edit_posts;
	}

	function get_provider_delete_capability($post_type_key) {
		if (!post_type_exists($post_type_key)) {
			return false;
		}
		$post_type_object = get_post_type_object($post_type_key);
		return $post_type_object->cap->delete_posts;
	}

	function init() {
		
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return  Foo A single instance of this class.
	 */
	static function get_instance() {
		if (null == VGSE_Provider_Post::$instance) {
			VGSE_Provider_Post::$instance = new VGSE_Provider_Post();
			VGSE_Provider_Post::$instance->init();
		}
		return VGSE_Provider_Post::$instance;
	}

	function get_post_data_table_id_key($post_type = null) {
		if (!$post_type) {
			$post_type = VGSE()->helpers->get_provider_from_query_string();
		}

		$post_id_key = apply_filters('vgse_sheet_editor/provider/post/post_data_table_id_key', 'ID', $post_type);
		if (!$post_id_key) {
			$post_id_key = 'ID';
		}
		if (method_exists(VGSE()->helpers, 'sanitize_table_key')) {
			$post_id_key = VGSE()->helpers->sanitize_table_key($post_id_key);
		}
		return $post_id_key;
	}

	function get_meta_table_post_id_key($post_type = null) {
		if (!$post_type) {
			$post_type = VGSE()->helpers->get_provider_from_query_string();
		}

		$post_id_key = apply_filters('vgse_sheet_editor/provider/post/meta_table_post_id_key', 'post_id', $post_type);
		if (!$post_id_key) {
			$post_id_key = 'post_id';
		}
		if (method_exists(VGSE()->helpers, 'sanitize_table_key')) {
			$post_id_key = VGSE()->helpers->sanitize_table_key($post_id_key);
		}
		return $post_id_key;
	}

	function get_meta_table_name($post_type = null) {
		global $wpdb;
		if (!$post_type) {
			$post_type = VGSE()->helpers->get_provider_from_query_string();
		}

		$table_name = apply_filters('vgse_sheet_editor/provider/post/meta_table_name', $wpdb->postmeta, $post_type);
		if (!$table_name) {
			$table_name = $wpdb->postmeta;
		}
		if (method_exists(VGSE()->helpers, 'sanitize_table_key')) {
			$table_name = VGSE()->helpers->sanitize_table_key($table_name);
		}
		return $table_name;
	}

	function prefetch_data($post_ids, $post_type, $spreadsheet_columns) {

		if (!isset(VGSE_Provider_Post::$data_store)) {
			VGSE_Provider_Post::$data_store = array(
				'terms' => array(),
				'meta' => array(),
				'item' => array()
			);
		}
		$new_terms = $this->_get_all_terms($post_ids, $post_type, $spreadsheet_columns);
		VGSE_Provider_Post::$data_store['terms'] = (!empty(VGSE_Provider_Post::$data_store['terms']) ) ? array_merge(VGSE_Provider_Post::$data_store['terms'], $new_terms) : $new_terms;

		$new_meta = $this->_get_all_meta($post_ids, $post_type, $spreadsheet_columns);
		VGSE_Provider_Post::$data_store['meta'] = (!empty(VGSE_Provider_Post::$data_store['meta']) ) ? array_merge(VGSE_Provider_Post::$data_store['meta'], $new_meta) : $new_meta;
	}

	function _get_all_meta($post_ids, $post_type, $spreadsheet_columns) {
		global $wpdb;
		$post_meta = array();
		$post_ids = array_map('intval', array_unique($post_ids));
		$raw_meta_columns = wp_list_filter($spreadsheet_columns, array('data_type' => 'meta_data'));

		// Exclude serialized sub column
		foreach ($raw_meta_columns as $index => $raw_meta_column) {
			if (!empty($raw_meta_column['serialized_field_original_key'])) {
				unset($raw_meta_columns[$index]);
			}
		}

		$meta_columns = apply_filters('vgse_sheet_editor/provider/post/prefetch/meta_keys', array_unique(array_values(array_merge(array_keys($raw_meta_columns), wp_list_pluck($raw_meta_columns, 'key_for_formulas')))), $post_type);

		$post_meta_table = $this->get_meta_table_name($post_type);
		$post_meta_post_id_key = $this->get_meta_table_post_id_key($post_type);

		$meta_columns_groups = array_chunk($meta_columns, 100);
		$post_meta_raw = array();

		foreach ($meta_columns_groups as $meta_columns_group) {

			$meta_columns_group_sanitized = array_map('trim', array_unique($meta_columns_group));
			$meta_columns_group_in_query_placeholders = implode(', ', array_fill(0, count($meta_columns_group_sanitized), '%s'));
			$post_ids_in_query_placeholders = implode(', ', array_fill(0, count($post_ids), '%d'));
			$meta_sql = $wpdb->prepare("SELECT m1.* 
FROM $post_meta_table as m1 USE INDEX () 
WHERE m1.meta_key IN ($meta_columns_group_in_query_placeholders) AND 
m1.$post_meta_post_id_key IN ($post_ids_in_query_placeholders)  AND 
	m1.meta_value <> ''  
	GROUP BY m1.$post_meta_post_id_key, m1.meta_key", array_merge($meta_columns_group_sanitized, $post_ids));
			$post_meta_raw_group = $wpdb->get_results($meta_sql, ARRAY_A);

			// If any DB error happened during the prefetch, skip the prefetch and disable it for future sessions
			// If we don't skip, we set all the meta fields as empty at the end of this function so we must avoid that
			if ($wpdb->last_error) {
				$options = get_option(VGSE()->options_key);
				$options['be_disable_data_prefetch'] = true;
				update_option(VGSE()->options_key, $options, false);
				return $post_meta;
			}
			$post_meta_raw = array_merge($post_meta_raw, $post_meta_raw_group);
		}

		foreach ($post_meta_raw as $post_meta_per_key) {
			$post_id = 'item' . $post_meta_per_key[$post_meta_post_id_key];
			if (!isset($post_meta[$post_id])) {
				$post_meta[$post_id] = array();
			}
			$post_meta[$post_id][$post_meta_per_key['meta_key']] = maybe_unserialize($post_meta_per_key['meta_value']);
		}

		$post_meta = $this->_prepare_prefetched_data($post_meta, $post_ids, $meta_columns);

		return $post_meta;
	}

	function _prepare_prefetched_data($post_meta, $post_ids, $columns) {

		// Find posts from original list that are missing from the mysql results, so we assume 
		// that they don't have any meta for the required field keys, so we auto generate the array with empty values.
		$posts_missing_meta = array_diff($post_ids, array_map('intval', explode(',', preg_replace('/[^0-9,]/', '', implode(',', array_keys($post_meta))))));
		if (!empty($posts_missing_meta)) {
			foreach ($posts_missing_meta as $post_id) {
				$post_meta['item' . $post_id] = array();
			}
		}

		$default_meta_values = array_fill_keys($columns, '');
		foreach ($post_meta as $post_id => $post_meta_fields) {
			$post_meta_fields = wp_parse_args($post_meta_fields, $default_meta_values);
			$post_meta[$post_id] = $post_meta_fields;
		}
		return $post_meta;
	}

	function _get_all_terms($post_ids, $post_type, $spreadsheet_columns) {
		global $wpdb;
		$post_terms = array();
		$post_ids = array_map('intval', array_unique($post_ids));
		$taxonomy_columns = apply_filters('vgse_sheet_editor/provider/post/prefetch/taxonomy_keys', array_keys(wp_list_filter($spreadsheet_columns, array('data_type' => 'post_terms'))), $post_type);
		if (empty($taxonomy_columns)) {
			return array();
		}
		$separator = VGSE()->helpers->get_term_separator();
		if (!empty(VGSE()->options['manage_taxonomy_columns_term_ids'])) {
			$field_key_to_concatenate = 't.term_id';
		} elseif (!empty(VGSE()->options['manage_taxonomy_columns_term_slugs'])) {
			$field_key_to_concatenate = 't.slug';
		} else {
			$field_key_to_concatenate = 't.name';
		}

		$prepared_data = array_merge(array($separator . ' '), array_map('trim', $taxonomy_columns), $post_ids);
		$taxonomy_columns_in_query_placeholders = implode(', ', array_fill(0, count($taxonomy_columns), '%s'));
		$post_ids_in_query_placeholders = implode(', ', array_fill(0, count($post_ids), '%d'));
		$post_terms_sql = $wpdb->prepare("SELECT tr.object_id, tt.taxonomy, GROUP_CONCAT($field_key_to_concatenate SEPARATOR %s) as terms, GROUP_CONCAT(tt.parent SEPARATOR '') as parents
FROM $wpdb->terms AS t 
INNER JOIN $wpdb->term_taxonomy AS tt
ON t.term_id = tt.term_id
INNER JOIN $wpdb->term_relationships AS tr
ON tr.term_taxonomy_id = tt.term_taxonomy_id
AND tt.taxonomy IN ($taxonomy_columns_in_query_placeholders) 
AND tr.object_id IN ($post_ids_in_query_placeholders)  
GROUP BY tr.object_id, tt.taxonomy  
ORDER BY t.name ASC", $prepared_data);
		$post_terms_raw = $wpdb->get_results($post_terms_sql, ARRAY_A);

		foreach ($post_terms_raw as $post_terms_per_taxonomy) {

			// When a post is using a term with a parent, we set a placeholder to remove the taxonomy later
			// because we'll generate it with PHP to have hierarchy like parent > child
			if (!preg_match('/^0+$/', $post_terms_per_taxonomy['parents'])) {
				$post_terms_per_taxonomy['terms'] = 'wpse-has-parents';
			}

			$post_id = 'item' . $post_terms_per_taxonomy['object_id'];
			if (!isset($post_terms[$post_id])) {
				$post_terms[$post_id] = array();
			}
			$post_terms[$post_id][$post_terms_per_taxonomy['taxonomy']] = html_entity_decode($post_terms_per_taxonomy['terms']);
		}

		$post_terms = $this->_prepare_prefetched_data($post_terms, $post_ids, $taxonomy_columns);

		// We remove the empty hierarchical taxonomies to generate them with PHP with the hierarchy
		foreach ($post_terms as $post_key => $taxonomies) {
			foreach ($taxonomies as $taxonomy_key => $terms) {
				if ($terms === 'wpse-has-parents') {
					unset($post_terms[$post_key][$taxonomy_key]);
				}
			}
		}

		return $post_terms;
	}

	function get_item_terms($post_id, $taxonomy) {
		if (isset(VGSE_Provider_Post::$data_store['terms']['item' . $post_id]) && isset(VGSE_Provider_Post::$data_store['terms']['item' . $post_id][$taxonomy])) {
			$raw_value = VGSE_Provider_Post::$data_store['terms']['item' . $post_id][$taxonomy];
		} else {
			$raw_value = VGSE()->data_helpers->prepare_post_terms_for_display(wp_get_post_terms($post_id, $taxonomy, array(
				'update_term_meta_cache' => false
			)));
			VGSE_Provider_Post::$data_store['terms']['item' . $post_id][$taxonomy] = $raw_value;
		}
		return apply_filters('vg_sheet_editor/provider/post/get_items_terms', $raw_value, $post_id, $taxonomy);
	}

	function get_statuses() {
		$post_type = VGSE()->helpers->get_provider_from_query_string();
		$all_statuses = get_post_stati(array('show_in_admin_status_list' => true), 'objects');
		$post_statuses = array();
		foreach ($all_statuses as $status_key => $status) {
			if (empty(VGSE()->options['show_all_custom_statuses']) && !empty($status->label_count['domain'])) {
				continue;
			}
			$post_statuses[$status_key] = $status->label;
		}
		if (( $post_type === 'page' && !current_user_can('publish_pages') ) || ( $post_type !== 'page' && !current_user_can('publish_posts'))) {
			unset($post_statuses['publish']);
		}
		if (($post_type === 'page' && !current_user_can('delete_pages')) || ($post_type !== 'page' && !current_user_can('delete_posts'))) {
			unset($post_statuses['trash']);
		}

		return apply_filters('vg_sheet_editor/provider/post/statuses', $post_statuses, $post_type);
	}

	function maybe_add_order_clause($wp_query_args) {
		global $wpdb;
		if (!empty($wp_query_args['orderby'])) {
			return $wp_query_args;
		}

		$cache_key = 'wpse_has_duplicate_dates' . $wp_query_args['post_type'];
		$has_duplicate_dates = get_transient($cache_key);

		if (!is_string($has_duplicate_dates)) {
			$sql = $wpdb->prepare("SELECT COUNT(*) as count FROM $wpdb->posts WHERE post_type = %s GROUP BY post_date HAVING count > 1 ORDER BY count DESC LIMIT 1", $wp_query_args['post_type']);
			$has_duplicate_dates = (int) $wpdb->get_var($sql) ? 'yes' : 'no';
			set_transient($cache_key, $has_duplicate_dates, DAY_IN_SECONDS);
		}

		if ($has_duplicate_dates === 'yes') {
			$wp_query_args['orderby'] = 'post_date ID';
		}
		return $wp_query_args;
	}

	function get_items($query_args) {
		// Fix. Conflict with the "Post types order" plugin. It won't load all the rows.
		$query_args['ignore_custom_sort'] = true;
		$query_args = $this->maybe_add_order_clause(apply_filters('vg_sheet_editor/provider/post/get_items_args', $query_args));
		$query = new WP_Query($query_args);

		if (empty($query_args['fields']) || $query_args['fields'] !== 'ids') {
			foreach ($query->posts as $item) {
				VGSE_Provider_Post::$data_store['item'][$item->ID] = $item;
			}
		}

		return $query;
	}

	function get_item($id, $format = null) {
		if (isset(VGSE_Provider_Post::$data_store['item'][$id])) {
			$item = VGSE_Provider_Post::$data_store['item'][$id];
		} else {
			$item = get_post($id);
			VGSE_Provider_Post::$data_store['item'][$id] = $item;
		}

		if ($format === ARRAY_A && is_object($item)) {
			$item = (array) $item;
		}
		return apply_filters('vg_sheet_editor/provider/post/get_item', $item, $id, $format);
	}

	function get_item_meta($post_id, $key, $single, $context = 'save', $bypass_cache = false) {
		if (!$bypass_cache && isset(VGSE_Provider_Post::$data_store['meta']['item' . $post_id]) && isset(VGSE_Provider_Post::$data_store['meta']['item' . $post_id][$key])) {
			$raw_value = VGSE_Provider_Post::$data_store['meta']['item' . $post_id][$key];
		} else {
			$raw_value = get_post_meta($post_id, $key, $single);
			VGSE_Provider_Post::$data_store['meta']['item' . $post_id][$key] = $raw_value;
		}
		$original_value = $raw_value;
		$raw_value = apply_filters('vg_sheet_editor/provider/post/get_item_meta', $raw_value, $post_id, $key, $single, $context);

		if (!is_null($original_value) && is_null($raw_value) && VGSE_DEBUG) {
			throw new Exception("Post meta was filtered and didn't return a value.", E_USER_ERROR);
		}

		return $raw_value;
	}

	function get_item_data($id, $key) {
		$raw_item = $this->get_item($id);
		if (!$raw_item) {
			return false;
		}
		$item = get_object_vars($raw_item);
		$second_key = 'wp_' . $key;
		$out = false;
		if (isset($item[$key])) {
			$out = $item[$key];
		}
		if (isset($item[$second_key])) {
			$out = $item[$second_key];
		}
		$out = apply_filters('vg_sheet_editor/provider/post/get_item_data', $out, $id, $key, true, 'read');

		return $out;
	}

	function update_item_data($values, $wp_error = false) {
		global $wpdb;

		if (!empty($values['post_type']) && !post_type_exists($values['post_type'])) {
			throw new Exception(json_encode(array(
								'post_id' => $values['ID'],
								'code' => 'wpse_invalid_post_type',
								'message' => sprintf(__("Row ID: %d, Post type: %s does not exist in WordPress. Make sure your CSV uses the right name in the post type column.", VGSE()->textname), $values['ID'], sanitize_text_field($values['post_type'])),
							)), E_USER_ERROR);
		}
		if (isset($values['post_type']) && empty($values['post_type'])) {
			throw new Exception(json_encode(array(
								'post_id' => $values['ID'],
								'code' => 'wpse_invalid_post_type',
								'message' => sprintf(__("Row ID: %d. You are trying to save an empty post type. Make sure your CSV uses the right name in the post type column.", VGSE()->textname), $values['ID']),
							)), E_USER_ERROR);
		}
		if (isset($values['post_status']) && empty($values['post_status'])) {
			$values['post_status'] = 'draft';
		}

		$post_id = $values['ID'];
		if (isset($values['post_date'])) {
			$values['edit_date'] = true;
		}

		// If converting from post to product, migrate the tags and categories too
		$product_type = apply_filters('vg_sheet_editor/woocommerce/product_post_type_key', 'product');
		if (function_exists('WC') && !empty($values['post_type']) && $values['post_type'] === $product_type && get_post_type($post_id) === 'post') {
			$post_tags = $this->get_item_terms($post_id, 'post_tag');
			$categories = $this->get_item_terms($post_id, 'category');
			// @todo wpml is not working
			do_action('vg_sheet_editor/provider/post/post_converted_to_product', $post_id, $values);
		}


		if (!empty($values['post_modified'])) {
			$mysql_time_format = "Y-m-d H:i:s";
			$time = strtotime($values['post_modified']);
			$post_modified = gmdate($mysql_time_format, $time);
			$post_modified_gmt = gmdate($mysql_time_format, ( $time + get_option('gmt_offset') * HOUR_IN_SECONDS));
			$wpdb->update($wpdb->posts, array(
				'post_modified' => $post_modified,
				'post_modified_gmt' => $post_modified_gmt,
					), array(
				'ID' => $post_id
					), array(
				'%s', '%s'
					), array(
				'%d'
			));
			unset($values['post_modified']);
		}

		$post_template = get_post_meta($post_id, '_wp_page_template', true);

		// Make sure the posts don't use invalid templates because wp throws errors
		if (!empty($post_template)) {
			$post_obj = get_post($post_id);
			$post_type_for_saving = (!empty($values['post_type'])) ? sanitize_text_field($values['post_type']) : $post_obj->post_type;
			$available_templates = get_page_templates($post_obj, $post_type_for_saving);

			if (!in_array($post_template, $available_templates, true) || !post_type_supports($post_type_for_saving, 'page-attributes')) {
				update_post_meta($post_id, '_wp_page_template', '');
			}
		}

		$out = true;
		if (isset($values['post_status']) && $values['post_status'] === 'delete') {
			VGSE()->deleted_rows_ids[] = $post_id;
			$deleted_child_ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_parent = %d", (int) $post_id));
			VGSE()->deleted_rows_ids = array_map('intval', array_merge($deleted_child_ids, VGSE()->deleted_rows_ids));

			if (!empty(VGSE()->options['delete_attached_images_when_post_delete'])) {
				$gallery = get_post_meta($post_id, '_product_image_gallery', true);
				$featured_image = get_post_meta($post_id, '_thumbnail_id', true);
				$post_images = array();

				if (is_string($gallery) && !empty($gallery)) {
					$post_images = array_merge($post_images, explode(',', $gallery));
				}
				if (!empty($featured_image) && is_numeric($featured_image)) {
					$post_images[] = $featured_image;
				}
				$post_images = apply_filters('vg_sheet_editor/deleted_post/post_images_to_delete', $post_images, $post_id, $values);

				foreach ($post_images as $image_id) {
					wp_delete_attachment($image_id, true);
				}
			}

			if (current_user_can('delete_post', $post_id)) {
				wp_delete_post($post_id, true);
			} else {
				throw new Exception(sprintf(__("Row ID: %d, You do not have permission to delete this post.", VGSE()->textname), $post_id), E_USER_ERROR);
			}
		} else {
			if (count($values) === 1 && isset($post_id)) {
				$out = true;
			} else {
				$out = wp_update_post($values, $wp_error);

				if (!empty($post_tags) || !empty($categories)) {
					$this->set_object_terms($post_id, VGSE()->data_helpers->prepare_post_terms_for_saving($post_tags, 'product_tag'), 'product_tag');
					$this->set_object_terms($post_id, VGSE()->data_helpers->prepare_post_terms_for_saving($categories, 'product_cat'), 'product_cat');
				}
			}
		}

		do_action('vg_sheet_editor/provider/post/data_updated', $post_id, $values);

		return $out;
	}

	function update_item_meta($id, $key, $value) {
		$result = update_post_meta($id, $key, apply_filters('vg_sheet_editor/provider/post/update_item_meta', $value, $id, $key));

		// clear internal cache
		if (isset(VGSE_Provider_Post::$data_store['meta']['item' . $id][$key])) {
			unset(VGSE_Provider_Post::$data_store['meta']['item' . $id][$key]);
		}
		return $result;
	}

	function get_object_taxonomies($post_type) {
		return get_object_taxonomies($post_type, 'objects');
	}

	function set_object_terms($post_id, $terms_saved, $key) {
		return wp_set_object_terms($post_id, $terms_saved, $key);
	}

	function get_total($current_post) {
		global $wpdb;

		$numeroposts = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s", $current_post));
		if (0 < $numeroposts) {
			$numeroposts = (int) $numeroposts;
		} else {
			$numeroposts = 0;
		}
		return $numeroposts;
	}

	function create_item($values) {
		return wp_insert_post($values);
	}

	function get_item_ids_by_keyword($keyword, $post_type, $operator = 'LIKE') {
		global $wpdb;
		$operator = ( $operator === 'LIKE') ? 'LIKE' : 'NOT LIKE';

		$checks = array();
		$keywords = array_map('trim', explode(';', $keyword));
		$prepared_data = array();
		foreach ($keywords as $single_keyword) {
			$checks[] = " post_title $operator %s ";
			$prepared_data[] = '%' . $wpdb->esc_like($single_keyword) . '%';
		}
		if (empty($checks)) {
			return array();
		}
		$sql = "SELECT DISTINCT ID FROM $wpdb->posts WHERE  ( " . implode(' OR ', $checks) . " ) ";
		if (!empty($post_type)) {
			$sql .= " AND post_type = %s";
			$prepared_data[] = $post_type;
		}

		$ids = $wpdb->get_col($wpdb->prepare($sql, $prepared_data));
		return $ids;
	}

	function get_meta_object_id_field($field_key, $column_settings) {
		$post_meta_post_id_key = $this->get_meta_table_post_id_key();
		return $post_meta_post_id_key;
	}

	function get_table_name_for_field($field_key, $column_settings) {
		global $wpdb;
		$table_name = ( $column_settings['data_type'] === 'post_data' ) ? $wpdb->posts : $this->get_meta_table_name();
		if (method_exists(VGSE()->helpers, 'sanitize_table_key')) {
			$table_name = VGSE()->helpers->sanitize_table_key($table_name);
		}
		return $table_name;
	}

	function get_meta_field_unique_values($meta_key, $post_type = 'post') {
		global $wpdb;
		$post_meta_table = $this->get_meta_table_name($post_type);
		$post_meta_post_id_key = $this->get_meta_table_post_id_key($post_type);
		$sql = $wpdb->prepare("SELECT m.meta_value FROM $wpdb->posts p LEFT JOIN $post_meta_table m ON p.ID = m.$post_meta_post_id_key WHERE p.post_type = %s AND m.meta_key = %s GROUP BY m.meta_value ORDER BY LENGTH(m.meta_value) DESC LIMIT 4", $post_type, $meta_key);
		$values = apply_filters('vg_sheet_editor/provider/post/meta_field_unique_values', $wpdb->get_col($sql), $meta_key, $post_type);

		// Remove any field value with extremely long length (5mb) to avoid high memory usage
		foreach ($values as $index => $value) {
			if (is_string($value) && strlen($value) > 5000000) {
				unset($values[$index]);
			}
		}
		return $values;
	}

	function get_all_meta_fields($post_type = 'post') {
		global $wpdb;
		$pre_value = apply_filters('vg_sheet_editor/provider/post/all_meta_fields_pre_value', null, $post_type);

		if (is_array($pre_value)) {
			return $pre_value;
		}
		$post_meta_table = $this->get_meta_table_name($post_type);
		$post_meta_post_id_key = $this->get_meta_table_post_id_key($post_type);
		$meta_keys_sql = $wpdb->prepare("SELECT m.meta_key FROM $wpdb->posts p LEFT JOIN $post_meta_table m ON p.ID = m.$post_meta_post_id_key WHERE p.post_type = %s AND m.meta_key NOT LIKE '%oembed%' AND m.meta_key NOT LIKE '%_base64_image%' AND m.meta_value NOT LIKE 'field_%' GROUP BY m.meta_key LIMIT 2500", $post_type);
		$meta_keys = $wpdb->get_col($meta_keys_sql);
		return apply_filters('vg_sheet_editor/provider/post/all_meta_fields', $meta_keys, $post_type);
	}

}

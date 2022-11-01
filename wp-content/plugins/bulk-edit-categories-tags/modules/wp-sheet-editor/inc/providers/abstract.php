<?php

if (!class_exists('VGSE_Provider_Abstract')) {

	abstract class VGSE_Provider_Abstract {

		static private $instance = false;
		var $key = null;
		var $is_post_type = true;
		static $data_store = array();

		private function __construct() {
			
		}

		function filter_rows_before_edit($data, $post_type) {
			return $data;
		}

		function get_random_string($length, $spChars = false) {
			$alpha = 'abcdefghijklmnopqrstwvxyz';
			$alphaUp = strtoupper($alpha);
			$num = '12345678901234567890';
			$sp = '@/+.*-\$#!)[';
			$thread = $alpha . $alphaUp . $num;
			if ($spChars) {
				$thread .= $sp;
			}
			$str = '';
			for ($i = 0; $i < $length; $i++) {
				$str .= $thread[mt_rand(0, strlen($thread) - 1)];
			}
			return $str;
		}

		abstract function get_provider_read_capability($post_type_key);

		abstract function delete_meta_key($old_key, $post_type);

		abstract function rename_meta_key($old_key, $new_key, $post_type);

		abstract function get_provider_edit_capability($post_type_key);

		function get_provider_delete_capability($post_type_key) {
			return $this->get_provider_edit_capability($post_type_key);
		}

		abstract function init();

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @return  Foo A single instance of this class.
		 */
		static function get_instance() {
			if (null == self::$instance) {
				self::$instance = new self();
				self::$instance->init();
			}
			return self::$instance;
		}

		abstract function get_post_data_table_id_key($post_type = null);

		abstract function get_meta_table_post_id_key($post_type = null);

		abstract function get_meta_table_name($post_type = null);

		abstract function prefetch_data($post_ids, $post_type, $spreadsheet_columns);

		abstract function get_item_terms($post_id, $taxonomy);

		abstract function get_statuses();

		abstract function get_items($query_args);

		abstract function get_item($id, $format = null);

		abstract function get_item_meta($post_id, $key, $single, $context = 'save', $bypass_cache = false);

		abstract function get_item_data($id, $key);

		abstract function update_item_data($values, $wp_error = false);

		abstract function update_item_meta($id, $key, $value);

		abstract function get_object_taxonomies($post_type);

		abstract function set_object_terms($post_id, $terms_saved, $key);

		abstract function get_total($current_post);

		abstract function create_item($values);

		abstract function get_item_ids_by_keyword($keyword, $post_type, $operator = 'LIKE');

		abstract function get_meta_object_id_field($field_key, $column_settings);

		abstract function get_table_name_for_field($field_key, $column_settings);

		abstract function get_meta_field_unique_values($meta_key, $post_type = 'post');

		abstract function get_all_meta_fields($post_type = 'post');
	}

}
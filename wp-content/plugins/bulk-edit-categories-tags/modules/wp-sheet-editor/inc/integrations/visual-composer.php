<?php

if (!class_exists('VG_Visual_Composer_Integration')) {

	class VG_Visual_Composer_Integration {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {

			add_action('vg_sheet_editor/editor/before_init', array($this, 'register_columns'));
		}

		/**
		 * Register spreadsheet columns
		 */
		function register_columns($editor) {
			if (!$editor->provider->is_post_type) {
				return;
			}
			$post_types = $editor->args['enabled_post_types'];
			foreach ($post_types as $post_type) {
				if (!$this->is_post_type_allowed($post_type)) {
					continue;
				}

				$editor->args['columns']->register_item('wpse_visual_composer', $post_type, array(
					'get_value_callback' => array($this, 'get_cell_value'),
					'title' => __('WPBakery Page Builder', VGSE()->textname),
					'column_width' => 180,
					'allow_to_save' => false,
				));
			}
		}

		function get_cell_value($post, $column_key) {

			$html = '<a href="' . esc_url(add_query_arg(array(
						'vc_action' => 'vc_inline',
						'post_id' => $post->ID,
						'post_type' => $post->post_type,
									), admin_url('post.php'))) . '" class="button visual-composer-backend visual-composer-edit" target="_blank" /><i class="fa fa-edit"></i> ' . __('Live', VGSE()->textname) . '</a>';
			if (vc_enabled_frontend()) {
				$html .= '<a href="' . esc_url(add_query_arg(array(
							'action' => 'edit',
							'post' => $post->ID,
							'wpb_vc_js_status' => 'true',
										), admin_url('post.php'))) . '" class="button visual-composer-live visual-composer-edit" target="_blank" /><i class="fa fa-edit"></i> ' . __('Backend', VGSE()->textname) . '</a>';
			}
			return $html;
		}

		function is_post_type_allowed($post_type) {

			if (!function_exists('vc_enabled_frontend') || !function_exists('vc_editor_post_types')) {
				return false;
			}
			$vc_post_types = vc_editor_post_types();
			if (!in_array($post_type, $vc_post_types)) {
				return false;
			}

			return true;
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 */
		static function get_instance() {
			if (null == VG_Visual_Composer_Integration::$instance) {
				VG_Visual_Composer_Integration::$instance = new VG_Visual_Composer_Integration();
				VG_Visual_Composer_Integration::$instance->init();
			}
			return VG_Visual_Composer_Integration::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('VG_Visual_Composer_Integration_Obj')) {

	function VG_Visual_Composer_Integration_Obj() {
		return VG_Visual_Composer_Integration::get_instance();
	}

}

VG_Visual_Composer_Integration_Obj();

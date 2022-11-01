<?php

if ( ! class_exists( 'WPSE_Elementor_Integration' ) ) {

	class WPSE_Elementor_Integration {

		private static $instance = false;

		private function __construct() {

		}

		function init() {
			if ( ! defined( 'ELEMENTOR_VERSION' ) && ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
				return;
			}
			add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'register_columns' ) );
			add_action( 'vg_sheet_editor/save_rows/after_saving_post', array( $this, 'clear_elementor_css_cache_after_saving_row' ), 10, 4 );
			add_filter( 'vg_sheet_editor/duplicate/final_post_id', array( $this, 'clear_elementor_css_cache_after_duplication' ), 10, 3 );
		}
		function _clear_cache($post_id){
			if ( \Elementor\Utils::is_post_support( $post_id ) ) {
				$css_file = \Elementor\Core\Files\CSS\Post::create( $post_id );

				$css_file->delete();
			}
		}
		function clear_elementor_css_cache_after_duplication( $post_id, $template_id, $post_type ) {
			$this->_clear_cache($post_id);
			return $post_id;
		}

		function clear_elementor_css_cache_after_saving_row( $post_id, $item, $data, $post_type ) {
			$this->_clear_cache($post_id);
		}

		/**
		 * Register spreadsheet columns
		 */
		function register_columns( $editor ) {
			if ( ! $editor->provider->is_post_type ) {
				return;
			}
			$post_types = $editor->args['enabled_post_types'];
			foreach ( $post_types as $post_type ) {
				if ( ! post_type_supports( $post_type, 'elementor' ) ) {
					continue;
				}

				$editor->args['columns']->register_item(
					'wpse_open_elementor',
					$post_type,
					array(
						'data_type'                => 'post_data',
						'unformatted'              => array(
							'renderer' => 'wp_external_button',
							'readOnly' => true,
						),
						'column_width'             => 115,
						'title'                    => __( 'Elementor', VGSE()->textname ),
						'type'                     => 'external_button',
						'supports_formulas'        => false,
						'formatted'                => array(
							'renderer' => 'wp_external_button',
							'readOnly' => true,
						),
						'allow_to_hide'            => true,
						'allow_to_save'            => false,
						'allow_to_rename'          => true,
						'external_button_template' => admin_url( 'post.php?post={ID}&action=elementor' ),
					)
				);
			}
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 */
		static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new WPSE_Elementor_Integration();
				self::$instance->init();
			}
			return self::$instance;
		}

		function __set( $name, $value ) {
			$this->$name = $value;
		}

		function __get( $name ) {
			return $this->$name;
		}

	}

}

if ( ! function_exists( 'WPSE_Elementor_Integration_Obj' ) ) {

	function WPSE_Elementor_Integration_Obj() {
		return WPSE_Elementor_Integration::get_instance();
	}
}

add_action( 'vg_sheet_editor/initialized', 'WPSE_Elementor_Integration_Obj' );

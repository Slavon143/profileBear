<?php
/**
 * Avada Studio
 *
 * @package Avada-Builder
 * @since 3.5
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * AWB Studio class.
 *
 * @since 3.5
 */
class AWB_Studio_Remove {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 3.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Class constructor.
	 *
	 * @since 3.0
	 * @access private
	 */
	private function __construct() {

		if ( ! class_exists( 'AWB_Studio' ) || ! AWB_Studio::is_studio_enabled() ) {
			return;
		}

		// Import Studio Media from Builder (both live and backend).
		add_action( 'wp_ajax_awb_studio_remove_content', [ $this, 'ajax_remove_all_content' ] );
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 3.5
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new AWB_Studio_Remove();
		}
		return self::$instance;
	}

	/**
	 * AJAX callback for removing all studio content.
	 *
	 * @since 3.5
	 * @access public
	 * @return void
	 */
	public function ajax_remove_all_content() {

		check_ajax_referer( 'awb_remove_studio_content', 'nonce' );

		$response = [];

		$this->remove_all_content();

		wp_send_json( $response, 200 );
	}

	/**
	 * Deletes all imported studio content.
	 *
	 * @since 3.5
	 * @access protected
	 * @return void
	 */
	protected function remove_all_content() {

		if ( ! current_user_can( 'delete_posts' ) ) {
			return;
		}

		// Delete all content from wp_posts table, except attachments.
		$args = [
			'post_type'      => [ 'fusion_element', 'fusion_template', 'fusion_tb_section', 'fusion_icons', 'fusion_form', 'nav_menu_item' ],
			'fields'         => 'ids',
			'posts_per_page' => -1,
			'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				[
					'key'     => '_avada_studio_post',
					'compare' => 'EXISTS',
				],
			],
		];

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			$posts_number = count( $query->posts );
			for ( $i = 0; $i < $posts_number; $i++ ) {

				// Remove menu term as well.
				if ( 'nav_menu_item' === get_post_type( $query->posts[ $i ] ) ) {
					$parent_menu = wp_get_object_terms( $query->posts[ $i ], 'nav_menu' );

					if ( is_array( $parent_menu ) && ! empty( $parent_menu ) && isset( $parent_menu[0]->term_id ) ) {
						wp_delete_term( $parent_menu[0]->term_id, 'nav_menu' );
					}
				}

				wp_delete_post( $query->posts[ $i ], true );
			}
		}

		// Delete attachments.
		$args = [
			'post_type'      => [ 'attachment' ],
			'post_status'    => 'inherit',
			'fields'         => 'ids',
			'posts_per_page' => -1,
			'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				[
					'key'     => '_avada_studio_media',
					'compare' => 'EXISTS',
				],
			],
		];

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			$posts_number = count( $query->posts );
			for ( $i = 0; $i < $posts_number; $i++ ) {
				wp_delete_attachment( $query->posts[ $i ], true );
			}
		}
	}
}

/**
 * Instantiates the AWB_Studio_Remove class.
 * Make sure the class is properly set-up.
 *
 * @since object 3.5
 * @return object AWB_Studio_Remove
 */
function AWB_Studio_Remove() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Studio_Remove::get_instance();
}
AWB_Studio_Remove();

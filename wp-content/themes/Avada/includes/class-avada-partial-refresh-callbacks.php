<?php
/**
 * Partial-Refresh callbacks for the fusion-panel and the customizer.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * A wrapper for static methods.
 */
class Avada_Partial_Refresh_Callbacks {

	/**
	 * Footer.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function footer() {
		$social_icons = fusion_get_social_icons_class();
		get_template_part( 'templates/footer-content' );
	}

	/**
	 * Copyright.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function copyright() {
		avada_render_footer_copyright_notice();
	}

	/**
	 * Logo.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function logo() {
		add_filter( 'avada_setting_get_logo[url]', '__return_empty_string' );
		add_filter( 'avada_setting_get_logo[id]', '__return_empty_string' );
		get_template_part( 'templates/logo' );
	}

	/**
	 * The wp_footer.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function wp_footer() {
		wp_footer();
	}

	/**
	 * Slider.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function avada_slider() {

		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
		$slider_page_id     = 0;
		$visibility_options = [ 'small-visibility', 'medium-visibility', 'large-visibility' ];
		if ( isset( $_POST['post_id'] ) ) {
			$slider_page_id = sanitize_text_field( wp_unslash( $_POST['post_id'] ) );
		}
		$archive           = false !== strpos( $slider_page_id, 'archive' );
		$slider_page_id    = str_replace( '-archive', '', $slider_page_id );
		$visibility_values = Avada_Helper::get_slider_visibility( $slider_page_id, $archive );
		$visibility_values = empty( $visibility_values ) ? $visibility_options : $visibility_values;
		$visibility_values = ! is_array( $visibility_values ) ? explode( ',', $visibility_values ) : $visibility_values;

		avada_slider( $slider_page_id, $archive, $visibility_values );
	}

	/**
	 * Header.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function header() {

		if ( 'left' === fusion_get_option( 'header_position' ) || 'right' === fusion_get_option( 'header_position' ) ) {
			avada_side_header();
		} else {
			avada_header_template( 'below', ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && ! ( class_exists( 'WooCommerce' ) && is_shop() ) );
			avada_header_template( 'above', ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && ! ( class_exists( 'WooCommerce' ) && is_shop() ) );
		}
	}

	/**
	 * Header Position.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function header_position() {
		avada_header_template( 'below', ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && ! ( class_exists( 'WooCommerce' ) && is_shop() ) );
		if ( 'left' === fusion_get_option( 'header_position' ) || 'right' === fusion_get_option( 'header_position' ) ) {
			avada_side_header();
		}

		avada_sliders_container();

		avada_header_template( 'above', ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && ! ( class_exists( 'WooCommerce' ) && is_shop() ) );
	}

	/**
	 * Page-titlebar.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function page_titlebar_wrapper() {
		$override = Fusion_Template_Builder()->get_override( 'page_title_bar', true );
		if ( $override ) {
			Fusion_Template_Builder()->render_content( $override );
		} else {
			avada_current_page_title_bar();
		}
	}

	/**
	 * Related Posts.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function related_posts_template() {
		avada_render_related_posts();
	}

	/**
	 * Sliding Bar.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function sliding_bar() {
		get_template_part( 'sliding_bar' );
	}

	/**
	 * Featured images on singular pages.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function singular_featured_image() {
		global $post;

		$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

		if ( ! $post_id ) {
			return;
		}

		$post = get_post( $post_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride

		// Setup the global.
		setup_postdata( $post );

		avada_singular_featured_image( $post->post_type );

		// Reset global data just in case.
		wp_reset_postdata();
	}

	/**
	 * Searchform.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function searchform() {
		get_template_part( 'searchform' );
	}

	/**
	 * Main Menu.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function menu() {
		avada_main_menu();
	}

	/**
	 * Social-sharing
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function sharingbox() {
		global $post;

		$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

		if ( ! $post_id ) {
			return;
		}

		$post = get_post( $post_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride

		// Setup the global.
		setup_postdata( $post );

		avada_render_social_sharing( $post->post_type );
	}

	/**
	 * WooCommerce top user container.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function wc_top_user_container() {
		get_template_part( 'templates/wc-top-user-container' );
	}

	/**
	 * Privacy bar.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public static function privacy_bar() {
		get_template_part( 'templates/privacy-bar' );
	}
}

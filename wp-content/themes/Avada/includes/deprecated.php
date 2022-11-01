<?php
/**
 * This file contains functions that have been deprecated.
 * They will still work, but it we recommend you switch to the new methods instead.
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
 * The Avada_Nav_Walker class was deprecated in v7.0
 * and moved to Fusion-Library as Fusion_Nav_Walker.
 */
class_alias( 'Fusion_Nav_Walker', 'Avada_Nav_Walker' );
class_alias( 'Fusion_Nav_Walker', 'FusionCoreFrontendWalker' );

/**
 * How comments are displayed
 * This is simply a wrapper for the comment_template method in the Avada_Template class
 * Kept for backwards-compatibility
 */
function avada_comment( $comment, $args, $depth ) {
	Avada()->template->comment_template( $comment, $args, $depth );
}

/**
 * Retrieve protected post password form content.
 * This is simply a wrapper for the get_the_password_form method in the Avada_Template class
 * Kept for backwards-compatibility
 */
function avada_get_the_password_form() {
	return get_the_password_form();
}

if ( ! function_exists( 'tf_content' ) ) :
	/**
	 * Retrieve the content and apply and read-more modifications needed.
	 * This is simply a wrapper for the content method in the Avada_Blog class
	 * Kept for backwards-compatibility
	 */
	function tf_content( $limit, $strip_html ) {
		Avada()->blog->content( $limit, $strip_html );
	}
endif;

/**
 * Strip the content and buid the excerpt
 * This is simply a wrapper for the avada_get_content_stripped_and_excerpted method in the Avada_Blog class
 * Kept for backwards-compatibility
 */
function avada_get_content_stripped_and_excerpted( $excerpt_length, $content ) {
	return Avada()->blog->get_content_stripped_and_excerpted( $excerpt_length, $content );
}

if ( ! function_exists( 'tf_content' ) ) {
	/**
	 * The content.
	 */
	function tf_content( $limit, $strip_html ) {
		return Avada()->blog->content( $limit, $strip_html );
	}
}

if ( ! function_exists( 'tf_checkIfMenuIsSetByLocation' ) ) {
	/**
	 * Simply for backwards-compatibility purposes.
	 */
	function tf_checkIfMenuIsSetByLocation( $menu_location = '' ) {
		return ( has_nav_menu( $menu_location ) ) ? true : false;
	}
}

if ( ! function_exists( 'avada_slider_name' ) ) {
	/**
	 * This is simply a wrapper for the slider_name method in the Avada_Helper class
	 * Kept for backwards-compatibility
	 */
	function avada_slider_name( $name ) {
		return Avada_Helper::slider_name( $name );
	}
}

if ( ! function_exists( 'avada_get_slider_type' ) ) {
	/**
	 * This is simply a wrapper for the get_slider_type method in the Avada_Helper class
	 * Kept for backwards-compatibility
	 */
	function avada_get_slider_type( $post_id, $is_archive = false ) {
		return Avada_Helper::get_slider_type( $post_id, $is_archive );
	}
}

add_filter( 'avada_load_more_posts_name', 'avada_handle_deprecated_load_more_posts_filter' );
/**
 * Make sure that the wrongly spelled avada_load_more_pots_name filter can still be used
 * Kept for backwards-compatibility
 */
function avada_handle_deprecated_load_more_posts_filter( $text ) {
	$load_more_posts_text = apply_filters( 'avada_load_more_pots_name', '' );

	if ( $load_more_posts_text ) {
		return $load_more_posts_text;
	} else {
		return $text;
	}
}

add_filter( 'avada_read_more_name', 'avada_handle_deprecated_blog_read_more_link_filter' );
/**
 * Make sure that the wrongly spelled avada_load_more_pots_name filter can still be used
 * Kept for backwards-compatibility
 */
function avada_handle_deprecated_blog_read_more_link_filter( $text ) {
	$read_more_text = apply_filters( 'avada_blog_read_more_link', '' );

	if ( $read_more_text ) {
		return $read_more_text;
	} else {
		return $text;
	}
}

add_filter( 'fusion_faq_all_filter_name', 'avada_handle_deprecated_faq_all_filter_name_filter' );
/**
 * Keep Backwards-compatibility.
 *
 * @since 5.0.0
 */
function avada_handle_deprecated_faq_all_filter_name_filter( $filter_name_default ) {
	$filter_name = apply_filters( 'avada_faq_all_filter_name', '' );

	if ( $filter_name ) {
		return $filter_name;
	} else {
		return $filter_name_default;
	}
}

add_filter( 'avada_breadcrumbs_defaults', 'avada_handle_deprecated_fusion_breadcrumbs_defaults_filter' );
/**
 * Keep Backwards-compatibility.
 *
 * @since 5.0.4
 */
function avada_handle_deprecated_fusion_breadcrumbs_defaults_filter( $defaults ) {
	$fusion_breadcrumbs = apply_filters( 'fusion_breadcrumbs_defaults', '' );

	if ( $fusion_breadcrumbs ) {
		return $fusion_breadcrumbs;
	} else {
		return $defaults;
	}
}


add_action( 'avada_before_main_container', 'avada_handle_deprecated_before_main_action' );
/**
 * Keep Backwards-compatibility.
 */
function avada_handle_deprecated_before_main_action() {
	do_action( 'avada_before_main' );
}

add_action( 'avada_after_content', 'avada_handle_deprecated_after_content_action' );
/**
 * Keep Backwards-compatibility.
 */
function avada_handle_deprecated_after_content_action() {
	do_action( 'fusion_after_content' );
}

add_action( 'fusion_portfolio_shortcode_content', 'avada_handle_deprecated_recent_works_content' );
/**
 * Keep Backwards-compatibility.
 */
function avada_handle_deprecated_recent_works_content() {
	do_action( 'fusion_recent_works_shortcode_content' );
}

add_filter( 'fusion_google_analytics', 'avada_handle_deprecated_google_analytics_action' );
/**
 * Keep Backwards-compatibility.
 * @param string $value HTML.
 * @return string
 */
function avada_handle_deprecated_google_analytics_action( $value ) {
	return apply_filters( 'avada_google_analytics', $value );
}


/**
 * Alias for the Avada_Megamenu_Framework class.
 * Kept for child-themes compatibility.
 */
class FusionMegaMenuFramework extends Avada_Megamenu_Framework {}

/**
 * Alias for the Avada_Megamenu class.
 * Kept for child-themes compatibility.
 */
class FusionMegaMenu extends Avada_Megamenu {}

/**
 * Alias for the Avada_Nav_Walker_Megamenu class.
 * Kept for child-themes compatibility.
 */
class FusionCoreMegaMenus extends Avada_Nav_Walker_Megamenu {}

if ( ! function_exists( 'avada_render_rich_snippets_for_pages' ) ) {
	function avada_render_rich_snippets_for_pages( $title_tag = true, $author_tag = true, $updated_tag = true ) {
		return fusion_render_rich_snippets_for_pages( $title_tag, $author_tag, $updated_tag );
	}
}

if ( ! function_exists( 'avada_render_post_metadata' ) ) {
	function avada_render_post_metadata( $layout, $settings = array() ) {
		return fusion_render_post_metadata( $layout, $settings );
	}
}

if ( ! function_exists( 'avada_render_first_featured_image_markup' ) ) {
	function avada_render_first_featured_image_markup( $post_id, $post_featured_image_size = '', $post_permalink = '', $display_placeholder_image = false, $display_woo_price = false, $display_woo_buttons = false, $display_post_categories = 'default', $display_post_title = 'default', $type = '', $gallery_id = '', $display_rollover = 'yes', $display_woo_rating = false ) {
		return fusion_render_first_featured_image_markup( $post_id, $post_featured_image_size, $post_permalink, $display_placeholder_image, $display_woo_price, $display_woo_buttons, $display_post_categories, $display_post_title, $type, $gallery_id, $display_rollover, $display_woo_rating );
	}
}

if ( ! function_exists( 'avada_extract_shortcode_contents' ) ) {
	function avada_extract_shortcode_contents( $m ) {
		return fusion_extract_shortcode_contents( $m );
	}
}

if ( ! function_exists( 'avada_get_portfolio_excerpt_length' ) ) {
	function avada_get_portfolio_excerpt_length( $id ) {
		return fusion_get_portfolio_excerpt_length( $id );
	}
}

if ( ! function_exists( 'avada_link_pages' ) ) {
	function avada_link_pages() {
		fusion_link_pages();
	}
}

if ( ! function_exists( 'avada_get_sermon_content' ) ) {
	function avada_get_sermon_content( $archive = false ) {
		error_log( 'The `avada_get_sermon_content` function has been deprecated since Avada 5.1.0. Please use `Avada()->sermon_manager->get_sermon_content()` instead.' );
		return Avada()->sermon_manager->get_sermon_content( $archive );
	}
}

if ( ! function_exists( 'fusion_render_wpfc_sorting' ) ) {
	function fusion_render_wpfc_sorting() {
		error_log( 'The `fusion_render_wpfc_sorting` function has been deprecated since Avada 5.1.0. Please use `Avada()->sermon_manager->render_wpfc_sorting()` instead.' );
		Avada()->sermon_manager->render_wpfc_sorting();
	}
}

if ( ! function_exists( 'kd_mfi_get_featured_image_id' ) ) {
	function kd_mfi_get_featured_image_id( $image_id, $post_type, $post_id = NULL ) {
		error_log( 'The `kd_mfi_get_featured_image_id` function has been deprecated since Avada 5.2.0. Please use `fusion_get_featured_image_id` instead.' );
		return fusion_get_featured_image_id( $image_id, $post_type, $post_id );
	}
}

/**
 * Keep Backwards-compatibility.
 */
if ( ! class_exists( 'Avada_Color' ) ) {
	class Avada_Color extends Fusion_Color {}
}

/**
 * Keep Backwards-compatibility.
 */
if ( ! class_exists( 'Avada_Data' ) ) {
	class Avada_Data extends Fusion_Data {}
}

/**
 * Backwards-compatibility for the avada_post_metadata_date filter.
 *
 * @since 5.1
 * @param string $value The date format.
 * @return string
 */
function apply_avada_post_metadata_date_filter( $value ) {
	return apply_filters( 'avada_post_metadata_date', $value );
}
add_filter( 'fusion_post_metadata_date', 'apply_avada_post_metadata_date_filter' );

/**
 * Backwards-compatibility for the avada_post_metadata_markup filter.
 *
 * @since 5.1
 * @param string $value HTML.
 * @return string
 */
function apply_avada_post_metadata_markup_filter( $value ) {
	return apply_filters( 'avada_post_metadata_markup', $value );
}
add_filter( 'fusion_post_metadata_markup', 'apply_avada_post_metadata_markup_filter' );

/**
 * Backwards-compatibility for the avada_post_metadata_markup filter.
 *
 * @since 5.1
 * @param string $value HTML.
 * @return string
 */
function apply_avada_blog_read_more_excerpt_filter( $value ) {
	return apply_filters( 'avada_blog_read_more_excerpt', $value );
}
add_filter( 'fusion_blog_read_more_excerpt', 'apply_avada_blog_read_more_excerpt_filter' );

/**
 * Backwards-compatibility for the fusion_get_attachment_data_from_url_attachment_base_url filter.
 *
 * @since 5.7.1
 * @param string $value HTML.
 * @return string
 */
function apply_fusion_get_attachment_data_from_url_attachment_base_url_filter( $value ) {
	return apply_filters( 'fusion_get_attachment_data_from_url_attachment_base_url', $value );
}
add_filter( 'fusion_get_attachment_base_url', 'apply_fusion_get_attachment_data_from_url_attachment_base_url_filter' );

/**
 * Backwards-compatibility for the avada_tabs_widget_excerpt_length filter.
 *
 * @since 5.1
 * @param string $value HTML.
 * @return string
 */
function apply_avada_tabs_widget_excerpt_length_filter( $value ) {
	return apply_filters( 'avada_tabs_widget_excerpt_length', $value );
}
add_filter( 'fusion_tabs_widget_excerpt_length', 'apply_avada_tabs_widget_excerpt_length_filter' );

/**
 * Backwards-compatibility for the avada_social_icons_html filter.
 *
 * @since 5.9.2
 * @param string $value HTML.
 * @return string
 */
function apply_avada_social_icons_html_filter( $value ) {
	return apply_filters( 'avada_social_icons_html', $value );
}
add_filter( 'fusion_social_icons_html', 'apply_avada_social_icons_html_filter' );

/**
 * Backwards-compatibility for the avada_social_sharing_html filter.
 *
 * @since 5.9.2
 * @param string $value HTML.
 * @return string
 */
function apply_avada_social_sharing_html_filter( $value ) {
	return apply_filters( 'avada_social_sharing_html', $value );
}
add_filter( 'fusion_social_sharing_html', 'apply_avada_social_sharing_html_filter' );

if ( ! function_exists( 'avada_featured_images_for_pages' ) ) {
	/**
	 * Featured images for pages.
	 */
	function avada_featured_images_for_pages() {
		avada_singular_featured_image();
	}
}

/**
 * Function deprecated in v6.2.0 and replaced with JS implementations.
 *
 * @since 6.2.0
 * @return bool
 */
function avada_jetpack_is_mobile() {
	return wp_is_mobile();
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

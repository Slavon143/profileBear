<?php
/**
 * Custom avada functions.
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

add_action( 'wp_head', 'avada_set_post_views' );
add_action( 'wp_head', 'avada_set_today_post_views' );

add_action( 'wp_ajax_avada_set_ajax_post_views', 'avada_set_ajax_post_views' );
add_action( 'wp_ajax_nopriv_avada_set_ajax_post_views', 'avada_set_ajax_post_views' );

if ( ! function_exists( 'avada_set_post_views' ) ) {
	/**
	 * Post views inc.
	 */
	function avada_set_post_views() {
		global $post;

		$fusion_settings = awb_get_fusion_settings();
		if ( 'page_load' !== $fusion_settings->get( 'post_views' ) ) {
			return;
		}

		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
		if ( ! is_singular() || is_preview() || $is_builder ) {
			return;
		}

		$post_id = $post->ID;
		if ( empty( $post_id ) ) {
			return;
		}

		avada_increase_post_views( $post_id );
	}
}

if ( ! function_exists( 'avada_set_today_post_views' ) ) {

	/**
	 * Function that triggers at action to increase today views.
	 *
	 * @since 7.5
	 */
	function avada_set_today_post_views() {
		global $post;

		$fusion_settings = awb_get_fusion_settings();
		if ( 'page_load' !== $fusion_settings->get( 'post_views' ) ) {
			return;
		}

		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
		if ( ! is_singular() || is_preview() || $is_builder ) {
			return;
		}

		$post_id = $post->ID;
		if ( empty( $post_id ) ) {
			return;
		}

		avada_increase_today_post_views( $post_id );
	}
}

if ( ! function_exists( 'avada_set_ajax_post_views' ) ) {

	/**
	 * Function that triggers at action to increase post views.
	 */
	function avada_set_ajax_post_views() {
		$fusion_settings = awb_get_fusion_settings();
		if ( 'ajax' !== $fusion_settings->get( 'post_views' ) ) {
			return;
		}

		if ( ! isset( $_POST['postId'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		$post_id = intval( wp_unslash( $_POST['postId'] ) ); //phpcs:ignore WordPress.Security.NonceVerification
		if ( ! ( $post_id > 0 ) ) {
			return;
		}

		avada_increase_post_views( $post_id );
		avada_increase_today_post_views( $post_id );
	}
}

if ( ! function_exists( 'avada_increase_post_views' ) ) {

	/**
	 * For a given post_id, increase the views.
	 *
	 * @param int $post_id Post id.
	 * @return void
	 */
	function avada_increase_post_views( $post_id ) {
		if ( ! $post_id || ! avada_can_increase_post_views() ) {
			return;
		}

		$count = avada_get_post_views( $post_id );
		$count++;
		update_post_meta( $post_id, 'avada_post_views_count', $count );
	}
}

if ( ! function_exists( 'avada_increase_today_post_views' ) ) {

	/**
	 * For a given post_id, increase the today views.
	 *
	 * @param int $post_id Post id.
	 * @return void
	 */
	function avada_increase_today_post_views( $post_id ) {
		if ( ! $post_id || ! avada_can_increase_post_views() ) {
			return;
		}

		$today_views = avada_get_today_post_views( $post_id );
		$today_views++;
		update_post_meta( $post_id, 'avada_today_post_views_count', $today_views );

		// Check if also it's needed to update the today date, and update if necessary.
		if ( ! avada_are_post_views_stored_from_today( $post_id ) ) {
			update_post_meta( $post_id, 'avada_post_views_count_today_date', date( 'd-m-Y' ) );
		}
	}
}

if ( ! function_exists( 'avada_can_increase_post_views' ) ) {

	/**
	 * Check if post views can be increased.
	 *
	 * @return bool
	 */
	function avada_can_increase_post_views() {
		$fusion_settings        = awb_get_fusion_settings();
		$increase_views_setting = $fusion_settings->get( 'post_views_counting' );

		$can_increase_views = false;

		if ( 'all' === $increase_views_setting ) {
			$can_increase_views = true;
		}

		if ( 'logged_out' === $increase_views_setting && ! is_user_logged_in() ) {
			$can_increase_views = true;
		}

		if ( 'non_admins' === $increase_views_setting && ! current_user_can( 'manage_options' ) ) {
			$can_increase_views = true;
		}

		$can_increase_views = apply_filters( 'avada_can_increase_post_views', $can_increase_views );

		return $can_increase_views;
	}
}

if ( ! function_exists( 'avada_get_post_views' ) ) {

	/**
	 * Get the post views of a post.
	 *
	 * @since 7.5
	 * @param WP_Post|int|null $post The post object, id or null. Defaults to query post.
	 * @return int
	 */
	function avada_get_post_views( $post = null ) {
		if ( intval( $post ) === -99 ) {
			return 100;
		}

		$post = get_post( $post );
		if ( ! $post ) {
			return 0;
		}
		$post_id = $post->ID;

		$count_key = 'avada_post_views_count';
		$count     = get_post_meta( $post_id, $count_key, true );

		if ( ! is_numeric( $count ) ) {
			return 0;
		}

		return (int) $count;
	}
}

if ( ! function_exists( 'avada_get_today_post_views' ) ) {

	/**
	 * Get the today post views of a post.
	 *
	 * @since 7.5
	 * @param WP_Post|int|null $post The post object, id or null. Defaults to query post.
	 * @return int
	 */
	function avada_get_today_post_views( $post = null ) {
		if ( intval( $post ) === -99 ) {
			return 20;
		}

		$post = get_post( $post );
		if ( ! $post ) {
			return 0;
		}
		$post_id = $post->ID;

		if ( ! avada_are_post_views_stored_from_today( $post ) ) {
			return 0;
		}

		$count_key = 'avada_today_post_views_count';
		$count     = get_post_meta( $post_id, $count_key, true );

		if ( ! is_numeric( $count ) ) {
			return 0;
		}

		return (int) $count;
	}
}

if ( ! function_exists( 'avada_are_post_views_stored_from_today' ) ) {

	/**
	 * Check if the today date is set correctly in meta, or in another words,
	 * check if the views counted are from today.
	 *
	 * @since 7.5
	 * @param WP_Post|int|null $post The post object, id or null. Defaults to query post.
	 */
	function avada_are_post_views_stored_from_today( $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return 0;
		}
		$post_id = $post->ID;

		$post_meta_today = get_post_meta( $post_id, 'avada_post_views_count_today_date', true );
		$today           = date( 'd-m-Y' );

		if ( $today === $post_meta_today ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'avada_get_slider' ) ) {
	/**
	 * Get the slider type.
	 *
	 * @param int    $post_id    The post ID.
	 * @param string $type       The slider type.
	 * @param bool   $is_archive Whether archive page.
	 * @return  string
	 */
	function avada_get_slider( $post_id, $type, $is_archive = false ) {
		$type = Avada_Helper::slider_name( $type );
		if ( $type ) {
			return ( $is_archive ) ? fusion_data()->term_meta( $post_id )->get( $type ) : fusion_data()->post_meta( $post_id )->get( $type );
		}
		return false;
	}
}

if ( ! function_exists( 'avada_slider' ) ) {
	/**
	 * Slider.
	 *
	 * @param int   $post_id           The post ID.
	 * @param bool  $is_archive        Whether archive page.
	 * @param array $visibility_values Visibility values for slider.
	 */
	function avada_slider( $post_id, $is_archive = false, $visibility_values = [] ) {

		$slider_type = Avada_Helper::get_slider_type( $post_id, $is_archive );
		$slider      = avada_get_slider( $post_id, $slider_type, $is_archive );

		if ( $slider ) {
			$slider_name = Avada_Helper::slider_name( $slider_type );
			$slider_name = ( 'slider' === $slider_name ) ? 'layerslider' : $slider_name;

			$function = 'avada_' . $slider_name;

			$function( $slider );
		}
	}
}

if ( ! function_exists( 'avada_slider_visibility' ) ) {
	/**
	 * Slider visibility.
	 *
	 * @param string $visibility_values  The visibility values.
	 * @param string $visibility_options The visibility options.
	 */
	function avada_slider_visibility( $visibility_values, $visibility_options ) {
		$class = 'fusion-slider-visibility';

		foreach ( $visibility_options as $visibility_option ) {
			if ( ! in_array( $visibility_option, $visibility_values ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				$class .= ' fusion-no-' . $visibility_option;
			}
		}

		return $class;
	}
}

if ( ! function_exists( 'avada_revslider' ) ) {
	/**
	 * Slider Revolution.
	 *
	 * @param string $name The Slider Revolution slider name.
	 */
	function avada_revslider( $name ) {

		// We use include() instead of get_template_part() to pass the $name.
		include locate_template( 'templates/revslider.php' );
	}
}

if ( ! function_exists( 'avada_layerslider' ) ) {
	/**
	 * Layerslider.
	 *
	 * @param int|string $id The layerslider ID.
	 */
	function avada_layerslider( $id ) {

		// We use include() instead of get_template_part() to pass the $id.
		include locate_template( 'templates/layerslider.php' );
	}
}

if ( ! function_exists( 'avada_elasticslider' ) ) {
	/**
	 * The elastic-slider.
	 *
	 * @param int|string $term The term.
	 */
	function avada_elasticslider( $term ) {

		// We use include() instead of get_template_part() to pass the $term.
		include locate_template( 'templates/elasticslider.php' );
	}
}

if ( ! function_exists( 'avada_wooslider' ) ) {
	/**
	 * Per-term slider.
	 *
	 * @param int|string $term The term.
	 */
	function avada_wooslider( $term ) {
		if ( method_exists( 'Fusion_Slider', 'render_fusion_slider' ) ) {
			Fusion_Slider::render_fusion_slider( $term );
		}
	}
}

if ( ! function_exists( 'avada_current_page_title_bar' ) ) {
	/**
	 * Get the current page title.
	 *
	 * @param int|string $post_id The post ID.
	 */
	function avada_current_page_title_bar( $post_id = false ) {
		$post_id = $post_id ? $post_id : Avada()->fusion_library->get_page_id();
		if ( has_action( 'avada_override_current_page_title_bar' ) ) {
			do_action( 'avada_override_current_page_title_bar', $post_id );
		} elseif ( 'hide' !== fusion_get_option( 'page_title_bar' ) ) {
			$page_title_bar_contents = Fusion_Helper::fusion_get_page_title_bar_contents( $post_id );

			avada_page_title_bar( $page_title_bar_contents[0], $page_title_bar_contents[1], $page_title_bar_contents[2] );
		}
		do_action( 'avada_after_page_title_bar' );
	}
}

if ( ! function_exists( 'avada_is_page_title_bar_active' ) ) {
	/**
	 * Check if page title bar is active.
	 * Note: This checks if the PTB is displayed at all.
	 *
	 * @since 5.8.1
	 * @param int $post_id The post ID.
	 * @return bool
	 */
	function avada_is_page_title_bar_active( $post_id ) {
		return 'hide' !== fusion_get_option( 'page_title_bar' );
	}
}

if ( ! function_exists( 'avada_is_page_title_bar_enabled' ) ) {
	/**
	 * Check if page title bar is enabled.
	 *
	 * @param int $post_id The post ID.
	 * @return bool
	 */
	function avada_is_page_title_bar_enabled( $post_id ) {
		return fusion_get_option( 'page_title_bar_text' ) && 'hide' !== fusion_get_option( 'page_title_bar' );
	}
}

if ( ! function_exists( 'avada_backend_check_new_bbpress_post' ) ) {
	/**
	 * Check if we're creating a new bbPress post.
	 *
	 * @return bool
	 */
	function avada_backend_check_new_bbpress_post() {
		global $pagenow, $post_type;
		return ( 'post-new.php' === $pagenow && in_array( $post_type, [ 'forum', 'topic', 'reply' ], true ) ) ? true : false;
	}
}

if ( ! function_exists( 'avada_display_sidenav' ) ) {
	/**
	 * Displays side navigation.
	 *
	 * @param  int $post_id The post ID.
	 * @return string
	 */
	function avada_display_sidenav( $post_id ) {

		if ( is_page_template( 'side-navigation.php' ) && 0 !== get_queried_object_id() ) {
			$html = '<ul class="side-nav">';

			$post_ancestors = get_ancestors( $post_id, 'page' );
			$post_parent    = end( $post_ancestors );

			$html .= ( is_page( $post_parent ) ) ? '<li class="current_page_item">' : '<li>';

			if ( $post_parent ) {
				$html    .= '<a href="' . get_permalink( $post_parent ) . '" title="' . esc_html__( 'Back to Parent Page', 'Avada' ) . '">' . get_the_title( $post_parent ) . '</a></li>';
				$children = wp_list_pages( 'title_li=&child_of=' . $post_parent . '&echo=0&link_before=<span class="link-text">&link_after=</span>' );
			} else {
				$html    .= '<a href="' . get_permalink( $post_id ) . '" title="' . esc_html__( 'Back to Parent Page', 'Avada' ) . '">' . get_the_title( $post_id ) . '</a></li>';
				$children = wp_list_pages( 'title_li=&child_of=' . $post_id . '&echo=0&link_before=<span class="link-text">&link_after=</span>' );
			}

			if ( $children ) {
				$html .= $children;
			}

			$html .= '</ul>';

			return $html;
		}
	}
}

if ( ! function_exists( 'avada_number_of_featured_images' ) ) {
	/**
	 * Get the number of featured images.
	 *
	 * @return int
	 */
	function avada_number_of_featured_images() {
		global $post;
		$number_of_images = 0;

		if ( has_post_thumbnail() && fusion_get_option( 'show_first_featured_image' ) ) {
			$number_of_images++;
		}

		$posts_slideshow_number = Avada()->settings->get( 'posts_slideshow_number' );
		for ( $i = 2; $i <= $posts_slideshow_number; $i++ ) {
			$attachment_new_id = fusion_get_featured_image_id( 'featured-image-' . $i, $post->post_type );

			if ( $attachment_new_id ) {
				$number_of_images++;
			}
		}

		return $number_of_images;
	}
}

if ( ! function_exists( 'avada_singular_featured_image' ) ) {
	/**
	 * Featured images for singular pages.
	 *
	 * @since 6.0
	 * @param string|false $context The featured-image context(example: tribe_event, avada_portfolio etc).
	 * @return void
	 */
	function avada_singular_featured_image( $context = '' ) {

		if ( '' === $context ) {
			$context = get_post_type();
		}

		// ID check is needed for EC.
		if ( 0 === get_the_ID() || false === $context ) {
			return;
		}

		Fusion_Dynamic_JS::enqueue_script( 'fusion-flexslider' );

		if ( 'tribe_events' !== $context && ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() || fusion_doing_ajax() ) ) {
			echo '<div class="fusion-featured-image-wrapper">';
		}

		if ( 'tribe_events' === $context ) {
			if ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() || fusion_doing_ajax() ) {
				add_filter( 'tribe_event_featured_image', 'avada_event_featured_image_wrap', 99, 3 );
			}

			// Event featured image, but exclude link.
			echo tribe_event_featured_image( get_the_ID(), 'full', false ); // phpcs:ignore WordPress.Security.EscapeOutput

			if ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() || fusion_doing_ajax() ) {
				remove_filter( 'tribe_event_featured_image', 'avada_event_featured_image_wrap', 99 );
			}
		} elseif ( 'avada_portfolio' === $context ) {
			include FUSION_CORE_PATH . '/templates/featured-image-' . $context . '.php';
		} elseif ( 'product' === $context && function_exists( 'woocommerce_show_product_images' ) ) {
			woocommerce_show_product_images();
		} elseif ( 'page' === $context || 'wpfc_sermon' === $context ) {
			get_template_part( 'templates/featured-image-' . $context );
		} else {
			get_template_part( 'templates/featured-image-post' );
		}

		if ( 'tribe_events' !== $context && ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() || fusion_doing_ajax() ) ) {
			echo '</div>';
		}

	}
}

/**
 * Filter EC featured image markup on single page.
 *
 * @since 6.0
 * @param string $featured_image Generated HTML.
 * @param int    $post_id        Post ID.
 * @param string $size           Image size.
 * @return $string
 */
function avada_event_featured_image_wrap( $featured_image, $post_id, $size ) {
	return '<div class="fusion-featured-image-wrapper">' . $featured_image . '</div>';
}

add_action( 'woocommerce_before_template_part', 'avada_product_before_featured_image_wrap', 99, 4 );
/**
 * Open product featured image wrapper div on single page.
 *
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $located       Path to be included.
 * @param array  $args          Arguments. (default: array).
 */
function avada_product_before_featured_image_wrap( $template_name, $template_path, $located, $args ) {

	if ( 'single-product/product-image.php' === $template_name && ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) ) {
		echo '<div class="fusion-featured-image-wrapper">';
	}
}

add_action( 'woocommerce_after_template_part', 'avada_product_after_featured_image_wrap', 99, 4 );
/**
 * Close product featured image wrapper div on single page.
 *
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $located       Path to be included.
 * @param array  $args          Arguments. (default: array).
 */
function avada_product_after_featured_image_wrap( $template_name, $template_path, $located, $args ) {

	if ( 'single-product/product-image.php' === $template_name && ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) ) {
		echo '</div>';
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

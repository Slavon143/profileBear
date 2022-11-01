<?php
/**
 * Functions for retrieving dynamic data values.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada Builder
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * A wrapper for static methods.
 */
class Fusion_Dynamic_Data_Callbacks {

	/**
	 * Post ID for callbacks to use.
	 *
	 * @access public
	 * @var array
	 */
	public $post_data = [];

	/**
	 * Whether it has rendered already or not.
	 *
	 * @access protected
	 * @since 3.3
	 * @var array
	 */
	protected $has_rendered = [];

	/**
	 * Class constructor.
	 *
	 * @since 2.1
	 * @access public
	 */
	public function __construct() {
		add_action( 'wp_ajax_ajax_acf_get_field', [ $this, 'ajax_acf_get_field' ] );
		add_action( 'wp_ajax_ajax_get_post_date', [ $this, 'ajax_get_post_date' ] );

		add_action( 'wp_ajax_ajax_dynamic_data_default_callback', [ $this, 'ajax_dynamic_data_default_callback' ] );

		if ( class_exists( 'WooCommerce' ) ) {
			add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'woo_fragments' ] );
		}
	}

	/**
	 * Returns the post-ID.
	 *
	 * @since 6.2.0
	 * @return int
	 */
	public static function get_post_id() {

		if ( fusion_doing_ajax() && isset( $_GET['fusion_load_nonce'] ) && isset( $_GET['post_id'] ) ) {
			check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
			$post_id = sanitize_text_field( wp_unslash( $_GET['post_id'] ) );
		} else {
			$post_id = fusion_library()->get_page_id();
		}

		return apply_filters( 'fusion_dynamic_post_id', $post_id );
	}

	/**
	 * Get ACF field value.
	 *
	 * @since 2.1
	 */
	public function ajax_acf_get_field() {
		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
		$return_data = [];

		if ( isset( $_POST['field'] ) && isset( $_POST['post_id'] ) && function_exists( 'get_field' ) ) {
			$field_value = get_field( wp_unslash( $_POST['field'] ), wp_unslash( $_POST['post_id'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( ! isset( $_POST['image'] ) || ! $_POST['image'] ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$return_data['content'] = $field_value;
			} elseif ( is_array( $field_value ) && isset( $field_value['url'] ) ) {
				$return_data['content'] = $field_value['url'];
			} elseif ( is_integer( $field_value ) ) {
				$return_data['content'] = wp_get_attachment_url( $field_value );
			} elseif ( is_string( $field_value ) ) {
				$return_data['content'] = $field_value;
			} else {
				$return_data['content'] = $field_value;
			}
		}

		echo wp_json_encode( $return_data );
		wp_die();
	}

	/**
	 * Runs the defined callback.
	 *
	 * @access public
	 * @since 2.1
	 * @return void
	 */
	public function ajax_dynamic_data_default_callback() {
		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
		$return_data = [];

		$callback_function = ( isset( $_GET['callback'] ) ) ? sanitize_text_field( wp_unslash( $_GET['callback'] ) ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$callback_exists   = $callback_function && ( is_callable( 'Fusion_Dynamic_Data_Callbacks::' . $callback_function ) || is_callable( $callback_function ) ) ? true : false;
		$post_id           = ( isset( $_GET['post_id'] ) ) ? apply_filters( 'fusion_dynamic_post_id', wp_unslash( $_GET['post_id'] ) ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		// If its a term of some kind.
		if ( isset( $_GET['is_term'] ) && $_GET['is_term'] ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$term = get_term( $post_id );
			if ( $term ) {
				$GLOBALS['wp_query']->is_tax         = true;
				$GLOBALS['wp_query']->is_archive     = true;
				$GLOBALS['wp_query']->queried_object = $term;
			}
		}

		if ( $callback_function && $callback_exists && $post_id && isset( $_GET['args'] ) ) {
			$return_data['content'] = is_callable( 'Fusion_Dynamic_Data_Callbacks::' . $callback_function ) ? call_user_func_array( 'Fusion_Dynamic_Data_Callbacks::' . $callback_function, [ wp_unslash( $_GET['args'] ), $post_id ] ) : call_user_func_array( $callback_function, [ wp_unslash( $_GET['args'] ), $post_id ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}

		echo wp_json_encode( $return_data );
		wp_die();

	}

	/**
	 * Shortcode.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param arrsy $args Arguments.
	 * @return string
	 */
	public static function dynamic_shortcode( $args ) {
		(string) $shortcode_string = isset( $args['shortcode'] ) ? $args['shortcode'] : '';
		return do_shortcode( $shortcode_string );
	}

	/**
	 * Featured image.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function post_featured_image( $args ) {
		$src = '';

		if ( is_tax() || is_category() || is_tag() ) {
			return fusion_get_term_image();
		}

		if ( isset( $args['type'] ) && 'main' !== $args['type'] ) {
			$attachment_id = fusion_get_featured_image_id( $args['type'], get_post_type( self::get_post_id() ) );
			$attachment    = wp_get_attachment_image_src( $attachment_id, 'full' );
			$src           = isset( $attachment[0] ) ? $attachment[0] : '';
		} else {
			$src = get_the_post_thumbnail_url( self::get_post_id() );
		}

		return $src;
	}

	/**
	 * Product category thumbnail image.
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function woo_category_thumbnail( $args ) {
		if ( is_tax( 'product_cat' ) ) {
			$thumbnail_id = get_term_meta( get_queried_object()->term_id, 'thumbnail_id', true );
			if ( $thumbnail_id ) {
				return wp_get_attachment_url( $thumbnail_id );
			}

			// Fallback.
			if ( function_exists( 'wc_placeholder_img_src' ) ) {
				return wc_placeholder_img_src();
			}
		}
		return '';
	}

	/**
	 * Featured images.
	 *
	 * @static
	 * @access public
	 * @since 3.2
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function post_gallery( $args ) {
		$images          = [];
		$fusion_settings = awb_get_fusion_settings();
		$post_type       = get_post_type( self::get_post_id() );

		// Check if we should add featured image.
		if ( isset( $args['include_main'] ) && 'yes' === $args['include_main'] ) {
			$post_thumbnail_id = get_post_thumbnail_id( self::get_post_id() );
			$image_src         = $post_thumbnail_id ? wp_get_attachment_image_src( $post_thumbnail_id, 'full' ) : false;

			if ( $post_thumbnail_id ) {
				$images[] = [
					'ID'  => $post_thumbnail_id,
					'url' => $image_src[0],
				];
			}
		}

		// Check if we should add Avada featured images.
		$i = 2;
		while ( $i <= $fusion_settings->get( 'posts_slideshow_number' ) ) {
			$attachment_new_id = fusion_get_featured_image_id( 'featured-image-' . $i, $post_type, self::get_post_id() );
			if ( $attachment_new_id ) {
				$image_src = wp_get_attachment_image_src( $attachment_new_id, 'full' );
				$images[]  = [
					'id'  => $attachment_new_id,
					'url' => $image_src[0],
				];
			}
			$i++;
		}

		return $images;
	}

	/**
	 * Get post or archive title.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_object_title( $args ) {
		$include_context = ( isset( $args['include_context'] ) && 'yes' === $args['include_context'] ) ? true : false;

		if ( FusionBuilder()->post_card_data['is_rendering'] && FusionBuilder()->post_card_data['is_post_card_archives'] ) {
			$title = self::fusion_get_post_title( $args );
		} elseif ( is_search() ) {
			/* translators: The search keyword(s). */
			$title = sprintf( __( 'Search: %s', 'fusion-builder' ), get_search_query() );

			if ( get_query_var( 'paged' ) ) {
				/* translators: %s is the page number. */
				$title .= sprintf( __( '&nbsp;&ndash; Page %s', 'fusion-builder' ), get_query_var( 'paged' ) );
			}
		} elseif ( is_category() ) {
			$title = single_cat_title( '', false );

			if ( $include_context ) {
				/* translators: Category archive title. */
				$title = sprintf( __( 'Category: %s', 'fusion-builder' ), $title );
			}
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
			if ( $include_context ) {
				/* translators: Tag archive title. */
				$title = sprintf( __( 'Tag: %s', 'fusion-builder' ), $title );
			}
		} elseif ( is_author() ) {
			$author = get_user_by( 'id', get_query_var( 'author' ) );
			$title  = get_the_author_meta( 'nickname', (int) $author->ID );

			if ( $include_context ) {
				/* translators: Author archive title. */
				$title = sprintf( __( 'Author: %s', 'fusion-builder' ), $title );
			}
		} elseif ( is_year() ) {
			$title = get_the_date( _x( 'Y', 'yearly archives date format', 'fusion-builder' ) );

			if ( $include_context ) {
				/* translators: Yearly archive title. */
				$title = sprintf( __( 'Year: %s', 'fusion-builder' ), $title );
			}
		} elseif ( is_month() ) {
			$title = get_the_date( _x( 'F Y', 'monthly archives date format', 'fusion-builder' ) );

			if ( $include_context ) {
				/* translators: Monthly archive title. */
				$title = sprintf( __( 'Month: %s', 'fusion-builder' ), $title );
			}
		} elseif ( is_day() ) {
			$title = get_the_date( _x( 'F j, Y', 'daily archives date format', 'fusion-builder' ) );

			if ( $include_context ) {
				/* translators: Daily archive title. */
				$title = sprintf( __( 'Day: %s', 'fusion-builder' ), $title );
			}
		} elseif ( is_tax( 'post_format' ) ) {
			if ( is_tax( 'post_format', 'post-format-aside' ) ) {
				$title = _x( 'Asides', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
				$title = _x( 'Galleries', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
				$title = _x( 'Images', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
				$title = _x( 'Videos', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
				$title = _x( 'Quotes', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
				$title = _x( 'Links', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
				$title = _x( 'Statuses', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
				$title = _x( 'Audio', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
				$title = _x( 'Chats', 'post format archive title', 'fusion-builder' );
			}
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );

			if ( $include_context ) {
				/* translators: Post type archive title. */
				$title = sprintf( __( 'Archives: %s', 'fusion-builder' ), $title );
			}
		} elseif ( is_tax() ) {
			$title = single_term_title( '', false );

			if ( $include_context ) {
				$tax = get_taxonomy( get_queried_object()->taxonomy );

				if ( $tax ) {
					/* translators: Taxonomy term archive title. %1$s: Taxonomy singular name, %2$s: Current taxonomy term. */
					$title = sprintf( __( '%1$s: %2$s', 'fusion-builder' ), $tax->labels->singular_name, $title );
				}
			}
		} elseif ( is_archive() ) {
			$title = __( 'Archives', 'fusion-builder' );
		} elseif ( is_404() ) {
			$title = __( '404', 'fusion-builder' );
		} else {
			$title = self::fusion_get_post_title( $args );
		}

		return $title;
	}

	/**
	 * Post title.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string The post title.
	 */
	public static function fusion_get_post_title( $args ) {
		$include_context = ( isset( $args['include_context'] ) && 'yes' === $args['include_context'] ) ? true : false;

		/* translators: %s: Search term. */
		$title = get_the_title( self::get_post_id() );

		if ( $include_context ) {
			$post_type_obj = get_post_type_object( get_post_type( self::get_post_id() ) );

			if ( $post_type_obj ) {
				/* translators: %1$s: Post Object Label. %2$s: Post Title. */
				$title = sprintf( '%s: %s', $post_type_obj->labels->singular_name, $title );
			}
		}

		return $title;
	}

	/**
	 * Post ID.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return int
	 */
	public static function fusion_get_post_id( $args ) {
		return (string) self::get_post_id();
	}

	/**
	 * Get post excerpt or archive description.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return int
	 */
	public static function fusion_get_object_excerpt( $args ) {
		return is_archive() ? get_the_archive_description() : get_the_excerpt( self::get_post_id() );
	}

	/**
	 * Post date.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_get_post_date( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$format = isset( $args['format'] ) ? $args['format'] : '';
		$date   = 'modified' === $args['type'] ? get_the_modified_date( $format, $post_id ) : get_the_date( $format, $post_id );

		if ( ! $date ) {
			$date = self::fusion_get_date( $args );
		}

		return $date;
	}

	/**
	 * Current date.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_date( $args ) {
		$format = isset( $args['format'] ) ? $args['format'] : '';
		return wp_date( $format );
	}

	/**
	 * Get dynamic heading.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_get_dynamic_heading( $args, $post_id = 0 ) {
		$title = self::fusion_get_dynamic_option( $args, $post_id );
		if ( ! $title ) {
			$title = self::fusion_get_object_title( $args );
		}
		return $title;
	}

	/**
	 * Get Dynamic Content Page Option.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_get_dynamic_option( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$post_type   = get_post_type( $post_id );
		$pause_metas = ( 'fusion_tb_section' === $post_type || ( isset( $_POST['meta_values'] ) && strpos( $_POST['meta_values'], 'dynamic_content_preview_type' ) ) ); // phpcs:ignore WordPress.Security

		if ( $pause_metas ) {
			do_action( 'fusion_pause_meta_filter' );
		}

		$data = fusion_get_page_option( $args['data'], $post_id );

		if ( $pause_metas ) {
			do_action( 'fusion_resume_meta_filter' );
		}

		// For image data.
		if ( is_array( $data ) && isset( $data['url'] ) ) {
			$data = $data['url'];
		}

		return $data;
	}

	/**
	 * Post time.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_get_post_time( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$format = isset( $args['format'] ) && '' !== $args['format'] ? $args['format'] : 'U';
		return get_post_time( $format, false, $post_id );
	}

	/**
	 * Get post total views.
	 *
	 * @since 3.5
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string Empty string if no total views exist.
	 */
	public static function get_post_total_views( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$total_views = avada_get_post_views( $post_id );

		if ( empty( $total_views ) ) {
			return '';
		}

		return number_format_i18n( $total_views );
	}

	/**
	 * Get post today views.
	 *
	 * @since 3.5
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string Empty string if no today views exist.
	 */
	public static function get_post_today_views( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$today_views = avada_get_today_post_views( $post_id );

		if ( empty( $today_views ) ) {
			return '';
		}

		return number_format_i18n( $today_views );
	}

	/**
	 * Get the post reading time.
	 *
	 * @since 3.5
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string The reading time.
	 */
	public static function get_post_reading_time( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		return awb_get_reading_time_for_display( $post_id, $args );
	}

	/**
	 * Post type.
	 *
	 * @static
	 * @access public
	 * @since 3.5
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_get_post_type( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$post_type_label = '';
		$post_type_obj   = get_post_type_object( get_post_type( $post_id ) );

		if ( $post_type_obj ) {
			$post_type_label = $post_type_obj->labels->singular_name;
		}

		return $post_type_label;
	}

	/**
	 * Post terms.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_get_post_terms( $args, $post_id = 0 ) {
		$output = '';
		if ( ! isset( $args['type'] ) ) {
			return $output;
		}

		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$terms = wp_get_object_terms( $post_id, $args['type'] );
		if ( ! is_wp_error( $terms ) ) {
			$separator   = isset( $args['separator'] ) ? $args['separator'] : '';
			$should_link = isset( $args['link'] ) && 'no' === $args['link'] ? false : true;

			foreach ( $terms as $term ) {
				if ( $should_link ) {
					$output .= '<a href="' . get_term_link( $term->slug, $args['type'] ) . '" title="' . esc_attr( $term->name ) . '">';
				}

				$output .= esc_html( $term->name );

				if ( $should_link ) {
					$output .= '</a>';
				}

				$output .= $separator;
			}

			return '' !== $separator ? rtrim( $output, $separator ) : $output;
		}

		return $output;
	}

	/**
	 * Post permalink.
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_post_permalink( $args ) {
		if ( is_tax() ) {
			$term_link = get_term_link( get_queried_object() );
			if ( ! is_wp_error( $term_link ) ) {
				return $term_link;
			}
		}
		return get_permalink( self::get_post_id() );
	}

	/**
	 * Post meta.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_post_custom_field( $args ) {
		do_action( 'fusion_pause_meta_filter' );

		$post_id   = isset( $args['post_id'] ) && ! empty( $args['post_id'] && 0 !== $args['post_id'] ) ? $args['post_id'] : self::get_post_id();
		$post_meta = get_post_meta( $post_id, $args['key'], true );

		do_action( 'fusion_resume_meta_filter' );

		if ( ! is_array( $post_meta ) ) {
			return $post_meta;
		}
		return '';
	}

	/**
	 * Site title.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_site_title( $args ) {
		return get_bloginfo( 'name' );
	}

	/**
	 * Site tagline.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_site_tagline( $args ) {
		return get_bloginfo( 'description' );
	}

	/**
	 * Site URL.
	 *
	 * @static
	 * @access public
	 * @since 3.0
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_site_url( $args ) {
		return home_url( '/' );
	}

	/**
	 * Site Logo.
	 *
	 * @static
	 * @access public
	 * @since 3.0
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_site_logo( $args ) {
		$type = isset( $args['type'] ) ? $args['type'] : false;

		if ( ! $type ) {
			return '';
		}

		switch ( $type ) {

			case 'default_normal':
				return fusion_get_theme_option( 'logo', 'url' );

			case 'default_retina':
				return fusion_get_theme_option( 'logo_retina', 'url' );

			case 'sticky_normal':
				return fusion_get_theme_option( 'sticky_header_logo', 'url' );

			case 'sticky_retina':
				return fusion_get_theme_option( 'sticky_header_logo_retina', 'url' );

			case 'mobile_normal':
				return fusion_get_theme_option( 'mobile_logo', 'url' );

			case 'mobile_retina':
				return fusion_get_theme_option( 'mobile_logo_retina', 'url' );

			case 'all':
				return wp_json_encode(
					[
						'default' => [
							'normal' => fusion_get_theme_option( 'logo' ),
							'retina' => fusion_get_theme_option( 'logo_retina' ),
						],
						'sticky'  => [
							'normal' => fusion_get_theme_option( 'sticky_header_logo' ),
							'retina' => fusion_get_theme_option( 'sticky_header_logo_retina' ),
						],
						'mobile'  => [
							'normal' => fusion_get_theme_option( 'mobile_logo' ),
							'retina' => fusion_get_theme_option( 'mobile_logo_retina' ),
						],
					]
				);
		}

		return '';
	}


	/**
	 * Site request parameter.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_site_request_param( $args ) {
		$type  = isset( $args['type'] ) ? strtoupper( $args['type'] ) : false;
		$name  = isset( $args['name'] ) ? $args['name'] : false;
		$value = '';

		if ( ! $name || ! $type ) {
			return '';
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		switch ( $type ) {
			case 'POST':
				if ( ! isset( $_POST[ $name ] ) ) {
					return '';
				}
				$value = wp_unslash( $_POST[ $name ] );
				break;
			case 'GET':
				if ( ! isset( $_GET[ $name ] ) ) {
					return '';
				}
				$value = wp_unslash( $_GET[ $name ] );
				break;
			case 'QUERY_VAR':
				$value = get_query_var( $name );
				break;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		return htmlentities( wp_kses_post( $value ) );
	}

	/**
	 * Toggle Off Canvas.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_toggle_off_canvas( $args ) {
		if ( ! isset( $args['off_canvas_id'] ) ) {
			return '';
		}

		// Add Off Canvas to stack, so it's markup is added to the page.
		AWB_Off_Canvas_Front_End::add_off_canvas_to_stack( $args['off_canvas_id'] );

		return '#awb-oc__' . $args['off_canvas_id'];
	}

	/**
	 * Open Off Canvas.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_open_off_canvas( $args ) {
		if ( ! isset( $args['off_canvas_id'] ) ) {
			return '';
		}

		// Add Off Canvas to stack, so it's markup is added to the page.
		AWB_Off_Canvas_Front_End::add_off_canvas_to_stack( $args['off_canvas_id'] );

		return '#awb-open-oc__' . $args['off_canvas_id'];
	}

	/**
	 * Close Off Canvas.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_close_off_canvas( $args ) {
		if ( ! isset( $args['off_canvas_id'] ) ) {
			return '';
		}
		return '#awb-close-oc__' . $args['off_canvas_id'];
	}

	/**
	 * ACF text field.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function acf_get_field( $args ) {
		if ( ! isset( $args['field'] ) ) {
			return '';
		}

		$post_id = self::get_post_id();

		if ( false !== strpos( $post_id, '-archive' ) ) {
			if ( is_author() ) {
				$post_id = 'user_' . str_replace( '-archive', '', $post_id );
			} else {
				$post_id = get_term_by( 'term_taxonomy_id', str_replace( '-archive', '', $post_id ) );
			}

			return get_field( $args['field'], $post_id );
		}

		return get_field( $args['field'], get_post( $post_id ) );
	}

	/**
	 * ACF get link field.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function acf_get_link_field( $args ) {
		if ( ! isset( $args['field'] ) ) {
			return '';
		}
		$link = '';

		$post_id = self::get_post_id();
		if ( false !== strpos( $post_id, '-archive' ) ) {
			$image_data = get_field( $args['field'], get_term_by( 'term_taxonomy_id', str_replace( '-archive', '', $post_id ) ) );
		} else {
			$image_data = get_field( $args['field'], get_post( $post_id ) );
		}

		if ( is_array( $image_data ) && isset( $image_data['url'] ) ) {
			$link = $image_data['url'];
		} elseif ( is_string( $image_data ) ) {
			$link = $image_data;
		}

		return $link;
	}

	/**
	 * ACF get image field.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function acf_get_image_field( $args ) {
		if ( ! isset( $args['field'] ) ) {
			return '';
		}

		$post_id = self::get_post_id();
		if ( false !== strpos( $post_id, '-archive' ) ) {
			$image_data = get_field( $args['field'], get_term_by( 'term_taxonomy_id', str_replace( '-archive', '', $post_id ) ) );
		} else {
			$image_data = get_field( $args['field'], get_post( $post_id ) );
		}

		if ( is_array( $image_data ) && isset( $image_data['url'] ) ) {
			return $image_data['url'];
		} elseif ( is_integer( $image_data ) ) {
			return wp_get_attachment_url( $image_data );
		} elseif ( is_string( $image_data ) ) {
			return $image_data;
		}

		return '';
	}

	/**
	 * ACF get file field.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function acf_get_file_field( $args ) {
		if ( ! isset( $args['field'] ) ) {
			return '';
		}

		$post_id = self::get_post_id();
		if ( false !== strpos( $post_id, '-archive' ) ) {
			$video_data = get_field( $args['field'], get_term_by( 'term_taxonomy_id', str_replace( '-archive', '', $post_id ) ) );
		} else {
			$video_data = get_field( $args['field'], get_post( $post_id ) );
		}

		if ( is_array( $video_data ) && isset( $video_data['url'] ) ) {
			return $video_data['url'];
		} elseif ( is_integer( $video_data ) ) {
			return wp_get_attachment_url( $video_data );
		} elseif ( is_string( $video_data ) ) {
			return $video_data;
		}

		return '';
	}

	/**
	 * Gets Events Calendar date of the event. Return a string with the date.
	 *
	 * @param array $args    The args.
	 * @param int   $post_id The post ID.
	 * @return string
	 */
	public static function get_event_date_to_display( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			return '';
		}

		$post_is_event_type = ( 'tribe_events' === $post->post_type ? true : false );
		if ( ! $post_is_event_type ) {
			return '';
		}

		$date = '';
		if ( ! isset( $args['event_date_type'] ) ) {
			$args['event_date_type'] = 'both';
		}

		if ( 'start_event_date' === $args['event_date_type'] ) {
			$date = tribe_get_start_date( $post_id );
		} elseif ( 'end_event_date' === $args['event_date_type'] ) {
			$date = tribe_get_end_date( $post_id );
		} else {
			add_filter( 'tribe_events_recurrence_tooltip', [ self::class, 'remove_event_recurring_info' ], 999 );
			$date = tribe_events_event_schedule_details( $post_id );
			remove_filter( 'tribe_events_recurrence_tooltip', [ self::class, 'remove_event_recurring_info' ], 999 );
		}

		if ( ! $date ) {
			$date = '';
		}

		return $date;
	}

	/**
	 * Remove the recurring event after the meta, since the HTML will take a
	 * lot of space.
	 *
	 * @param string $tooltip The recurring tooltip.
	 * @return string Empty string, containing no tooltip.
	 */
	public static function remove_event_recurring_info( $tooltip ) {
		return '';
	}

	/**
	 * Gets Events Calendar date value. Returns an array with a date.
	 *
	 * @param array $args    The args.
	 * @param int   $post_id The post ID.
	 * @return array
	 */
	public static function get_event_date( $args, $post_id = 0 ) {
		if ( isset( $args['event_id'] ) && ! empty( $args['event_id'] ) ) {
			$post_id = $args['event_id'];
		}
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		if ( 'end_event_date' === $args['event_date'] ) {
			$date               = tribe_get_end_date( $post_id, true, Tribe__Date_Utils::DBDATETIMEFORMAT );
			$args['start_date'] = tribe_get_start_date( $post_id, true, Tribe__Date_Utils::DBDATETIMEFORMAT );
		} else {
			$date = tribe_get_start_date( $post_id, true, Tribe__Date_Utils::DBDATETIMEFORMAT );
		}

		return [
			'date' => $date,
			'args' => $args,
		];
	}

	/**
	 * Gets Woo start or end sale date value
	 *
	 * @param array   $args    The args.
	 * @param integer $post_id The post ID.
	 * @return string
	 */
	public static function woo_sale_date( $args, $post_id = 0 ) {

		if ( isset( $args['product_id'] ) && ! empty( $args['product_id'] ) ) {
			$post_id = $args['product_id'];
		}
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}
		if ( 'end_date' === $args['sale_date'] ) {
			$field_name         = '_sale_price_dates_to';
			$start_date         = $date = self::fusion_get_post_custom_field(
				[
					'key'     => '_sale_price_dates_from',
					'post_id' => $post_id,
				]
			);
			$args['start_date'] = $start_date;
		} else {
			$field_name = '_sale_price_dates_from';
		}

		$date = self::fusion_get_post_custom_field(
			[
				'key'     => $field_name,
				'post_id' => $post_id,
			]
		);
		return ! empty( $date ) ? [
			'date' => date( 'Y-m-d H:i:s', $date ),
			'args' => $args,
		] : '';
	}


	/**
	 * Get product price.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_price( $args, $post_id = 0 ) {

		if ( ! isset( $args['format'] ) ) {
			$args['format'] = '';
		}

		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$_product = wc_get_product( $post_id );
		$price    = '';

		if ( ! $_product ) {
			return;
		}

		if ( '' === $args['format'] ) {
			$price = $_product->get_price_html();
		}

		if ( 'original' === $args['format'] ) {
			$price = wc_price( wc_get_price_to_display( $_product, [ 'price' => $_product->get_regular_price() ] ) );
		}

		if ( 'sale' === $args['format'] ) {
			$price = wc_price( wc_get_price_to_display( $_product, [ 'price' => $_product->get_sale_price() ] ) );
		}

		return $price;
	}

	/**
	 * Get product SKU.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_sku( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$_product = wc_get_product( $post_id );

		if ( ! $_product ) {
			return;
		}

		return '<span class="awb-sku product_meta"><span class="sku">' . esc_html( $_product->get_sku() ) . '</span></span>';
	}

	/**
	 * Get product stock.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_stock( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$_product = wc_get_product( $post_id );

		if ( ! $_product ) {
			return;
		}

		$stock = $_product->get_stock_quantity();

		return null !== $stock ? $stock : '';
	}

	/**
	 * Get product gallery.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_gallery( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$_product = wc_get_product( $post_id );

		if ( ! $_product ) {
			return;
		}

		$gallery = $_product->get_gallery_image_ids();

		return $gallery;
	}

	/**
	 * Get term count.
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function get_term_count( $args, $post_id = 0 ) {
		$term_count = '0';

		if ( ! is_tax() && ! is_category() && ! is_tag() && ! is_author() ) {
			return $term_count;
		}

		if ( is_author() ) {
			$author     = get_user_by( 'slug', get_query_var( 'author_name' ) );
			$term_count = isset( $author->ID ) ? (string) count_user_posts( $author->ID ) : '0';
		} elseif ( isset( get_queried_object()->count ) ) {
			$term_count = (string) get_queried_object()->count;
		}

		if ( isset( $args['singular_text'] ) && isset( $args['plural_text'] ) ) {
			$term_count .= '1' === $term_count ? $args['singular_text'] : $args['plural_text'];
		}

		return $term_count;
	}

	/**
	 * Get cart count.
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_cart_count( $args, $post_id = 0 ) {
		$cart_count  = 0;
		$opening_tag = '<span class="fusion-dynamic-cart-count-wrapper"';

		if ( is_object( WC()->cart ) ) {
			$cart_count = WC()->cart->get_cart_contents_count();
		}

		if ( isset( $args['singular_text'] ) && isset( $args['plural_text'] ) ) {
			$cart_count .= 1 === $cart_count ? $args['singular_text'] : $args['plural_text'];

			$opening_tag .= ' data-singular="' . esc_attr( $args['singular_text'] ) . '" data-plural="' . esc_attr( $args['plural_text'] ) . '"';

			if ( ! isset( $has_rendered['woo_cart_count'] ) || true !== $has_rendered['woo_cart_count'] ) {
				$has_rendered['woo_cart_count'] = true;

				// Enqueue only if we use singular and plural texts.
				Fusion_Dynamic_JS::enqueue_script(
					'fusion-woo-cart-count',
					FusionBuilder::$js_folder_url . '/general/woo-cart-count.js',
					FusionBuilder::$js_folder_path . '/general/woo-cart-count.js',
					[ 'jquery' ],
					'1.0',
					true
				);
			}
		}

		return $opening_tag . '><span class="fusion-dynamic-cart-count">' . $cart_count . '</span></span>';
	}

	/**
	 * Get cart total.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_cart_total( $args, $post_id = 0 ) {
		$cart_total  = 0;
		$opening_tag = '<span class="fusion-dynamic-cart-total-wrapper"';

		if ( is_object( WC()->cart ) ) {
			$cart_total = WC()->cart->get_cart_total();
		}

		return '<span class="fusion-dynamic-cart-total-wrapper"><span class="fusion-dynamic-cart-total">' . $cart_total . '</span></span>';
	}

	/**
	 * Get add to cart link.
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_cart_link( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$_product = wc_get_product( $post_id );

		if ( ! $_product ) {
			return '';
		}

		return $_product->add_to_cart_url();
	}

	/**
	 * Generates the update card link
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_update_cart_class( $args, $post_id = 0 ) {
		return '#updateCart';
	}

	/**
	 * Modify the cart ajax.
	 *
	 * @access public
	 * @since 3.3
	 * @param array $fragments Ajax fragments handled by WooCommerce.
	 * @return array
	 */
	public function woo_fragments( $fragments ) {
		$cart_contents_count = '';
		$cart_total          = '';

		if ( is_object( WC()->cart ) ) {
			$cart_contents_count = WC()->cart->get_cart_contents_count();
			$cart_total          = WC()->cart->get_cart_total();
		}

		$fragments['.fusion-dynamic-cart-count'] = '<span class="fusion-dynamic-cart-count">' . $cart_contents_count . '</span>';
		$fragments['.fusion-dynamic-cart-total'] = '<span class="fusion-dynamic-cart-total">' . $cart_total . '</span>';

		return $fragments;
	}

	/**
	 * Get product rating.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_rating( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$_product = wc_get_product( $post_id );

		if ( ! $_product ) {
			return;
		}

		if ( '' === $args['format'] ) {
			$output = $_product->get_average_rating();
		}

		if ( 'rating' === $args['format'] ) {
			$output = $_product->get_rating_count();
		}

		if ( 'review' === $args['format'] ) {
			$output = $_product->get_review_count();
		}

		return $output;
	}

	/**
	 * Author Name.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function get_author_name( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}
		$user_id = get_post_field( 'post_author', $post_id );
		return get_the_author_meta( 'display_name', $user_id );
	}

	/**
	 * Author Description.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function get_author_description( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}
		$user_id = get_post_field( 'post_author', $post_id );
		return get_the_author_meta( 'description', $user_id );
	}

	/**
	 * Author Avatar.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function get_author_avatar( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}
		$user_id = get_post_field( 'post_author', $post_id );
		return get_avatar_url( get_the_author_meta( 'email', $user_id ) );
	}

	/**
	 * Author URL.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function get_author_url( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}
		$user_id = get_post_field( 'post_author', $post_id );
		return esc_url( get_author_posts_url( $user_id ) );
	}

	/**
	 * Author Social Link.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function get_author_social( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$type    = isset( $args['type'] ) ? $args['type'] : 'author_email';
		$user_id = get_post_field( 'post_author', $post_id );
		$url     = get_the_author_meta( $type, $user_id );

		if ( 'author_email' === $type ) {
			$url = 'mailto:' . $url;
		}
		return esc_url( $url );
	}

	/**
	 * Post comments number.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_get_post_comments( $args, $post_id = 0 ) {
		$output      = '';
		$should_link = isset( $args['link'] ) && 'no' === $args['link'] ? false : true;

		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}
		$number = get_comments_number( $post_id );

		if ( 0 === $number ) {
			$output = esc_html__( 'No Comments', 'fusion-builder' );
		} elseif ( 1 === $number ) {
			$output = esc_html__( 'One Comment', 'fusion-builder' );
		} else {
			/* Translators: Number of comments */
			$output = sprintf( _n( '%s Comment', '%s Comments', $number, 'fusion-builder' ), number_format_i18n( $number ) );

		}

		if ( $should_link ) {
			$output = '<a class="fusion-one-page-text-link" href="' . get_comments_link( $post_id ) . '">' . $output . '</a>';
		}
		return $output;
	}

	/**
	 * Author Name.
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_logged_in_username( $args ) {
		$user = wp_get_current_user();
		return is_user_logged_in() ? $user->display_name : '';
	}

	/**
	 * Get search count.
	 *
	 * @static
	 * @access public
	 * @since 3.5
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function get_search_count( $args, $post_id = 0 ) {
		$search_count = 0;

		if ( is_search() ) {
			global $wp_query;
			$search_count = $wp_query->found_posts;
		} elseif ( isset( $_GET['awb-studio-content'] ) && isset( $_GET['search'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$query        = fusion_cached_query( Fusion_Template_Builder()->archives_type( [] ) );
			$search_count = $query->found_posts;
		}

		if ( ! isset( $args['plural_text'] ) ) {
			$args['plural_text'] = '';
		}

		if ( ! isset( $args['singular_text'] ) ) {
			$args['singular_text'] = '';
		}

		$search_string = ( 1 === $search_count ? $args['singular_text'] : $args['plural_text'] );
		$space_before  = ( ! empty( $args['before'] ) ) ? ' ' : '';
		$space_after   = ( ! empty( $args['after'] ) ) ? ' ' : '';

		/* translators: 1: The search count, 2: The search string. */
		return $space_before . sprintf( __( '%1$d %2$s', 'fusion-builder' ), $search_count, $search_string ) . $space_after;
	}

	/**
	 * Woo Shop Page URL.
	 *
	 * @static
	 * @access public
	 * @since 3.7
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function woo_shop_page_url( $args ) {
		return get_permalink( wc_get_page_id( 'shop' ) );
	}

	/**
	 * Woo Cart Page URL.
	 *
	 * @static
	 * @access public
	 * @since 3.7
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function woo_cart_page_url( $args ) {
		return wc_get_cart_url();
	}

	/**
	 * Woo Checkout Page URL.
	 *
	 * @static
	 * @access public
	 * @since 3.7
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function woo_checkout_page_url( $args ) {
		return wc_get_checkout_url();
	}

	/**
	 * Woo My Account Page URL.
	 *
	 * @static
	 * @access public
	 * @since 3.7
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function woo_myaccount_page_url( $args ) {
		return wc_get_page_permalink( 'myaccount' );
	}

	/**
	 * Woo Terms & Conditions Page URL.
	 *
	 * @static
	 * @access public
	 * @since 3.7
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function woo_tnc_page_url( $args ) {
		return get_permalink( wc_terms_and_conditions_page_id() );
	}

	/**
	 * Open HubSpot chat.
	 *
	 * @static
	 * @access public
	 * @since 3.7.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_open_hubspot_chat( $args ) {

		// Enqueue js file.
		Fusion_Dynamic_JS::enqueue_script(
			'fusion-hubspot',
			FusionBuilder::$js_folder_url . '/general/fusion-hubspot.js',
			FusionBuilder::$js_folder_path . '/general/fusion-hubspot.js',
			[ 'jquery' ],
			'1.0',
			true
		);

		return '#hubspot-open-chat';
	}
}

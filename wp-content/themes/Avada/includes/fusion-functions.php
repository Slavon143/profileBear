<?php
/**
 * Contains all framework specific functions that are not part of a separate class
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      1.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
if ( ! function_exists( 'fusion_get_related_posts' ) ) {
	/**
	 * Get related posts by category
	 *
	 * @param  integer $post_id      Current post id.
	 * @param  integer $number_posts Number of posts to fetch.
	 * @return object                Object with posts info.
	 */
	function fusion_get_related_posts( $post_id, $number_posts = -1 ) {

		$args = '';

		$number_posts = (int) $number_posts;
		if ( 0 === $number_posts ) {
			$query = new WP_Query();
			return $query;
		}

		$args = wp_parse_args(
			$args,
			apply_filters(
				'fusion_related_posts_query_args',
				array(
					'category__in'        => wp_get_post_categories( $post_id ),
					'ignore_sticky_posts' => 0,
					'posts_per_page'      => $number_posts,
					'post__not_in'        => array( $post_id ),
				)
			)
		);

		// If placeholder images are disabled,
		// add the _thumbnail_id meta key to the query to only retrieve posts with featured images.
		if ( ! Avada()->settings->get( 'featured_image_placeholder' ) ) {
			$args['meta_key'] = '_thumbnail_id'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		}

		return fusion_cached_query( $args );

	}
}

if ( ! function_exists( 'fusion_get_custom_posttype_related_posts' ) ) {
	/**
	 * Get related posts by a custom post type category taxonomy.
	 *
	 * @param  integer $post_id      Current post id.
	 * @param  integer $number_posts Number of posts to fetch.
	 * @param  string  $post_type    The custom post type that should be used.
	 * @return object                Object with posts info.
	 */
	function fusion_get_custom_posttype_related_posts( $post_id, $number_posts = 8, $post_type = 'avada_portfolio' ) {

		$query = new WP_Query();

		$args = '';

		$number_posts = (int) $number_posts;
		if ( 0 === $number_posts || ! $number_posts ) {
			return $query;
		}

		$post_type = str_replace( 'avada_', '', $post_type );

		$item_cats = get_the_terms( $post_id, $post_type . '_category' );

		$item_array = array();
		if ( $item_cats ) {
			foreach ( $item_cats as $item_cat ) {
				$item_array[] = $item_cat->term_id;
			}
		}

		if ( ! empty( $item_array ) ) {
			$args = wp_parse_args(
				$args,
				array(
					'ignore_sticky_posts' => 0,
					'posts_per_page'      => $number_posts,
					'post__not_in'        => array( $post_id ),
					'post_type'           => 'avada_' . $post_type,
					'tax_query'           => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
						array(
							'field'    => 'id',
							'taxonomy' => $post_type . '_category',
							'terms'    => $item_array,
						),
					),
				)
			);

			// If placeholder images are disabled, add the _thumbnail_id meta key to the query to only retrieve posts with featured images.
			if ( ! Avada()->settings->get( 'featured_image_placeholder' ) ) {
				$args['meta_key'] = '_thumbnail_id'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			}

			$query = fusion_cached_query( apply_filters( 'fusion_related_posts_args', $args ) );

		}

		return $query;
	}
}

if ( ! function_exists( 'fusion_attr' ) ) {
	/**
	 * Function to apply attributes to HTML tags.
	 * Devs can override attr in a child theme by using the correct slug
	 *
	 * @param  string $slug         Slug to refer to the HTML tag.
	 * @param  array  $attributes   Attributes for HTML tag.
	 * @return string               Attributes in attr='value' format.
	 */
	function fusion_attr( $slug, $attributes = array() ) {

		$out  = '';
		$attr = apply_filters( "fusion_attr_{$slug}", $attributes );

		if ( empty( $attr ) ) {
			$attr['class'] = $slug;
		}

		foreach ( $attr as $name => $value ) {
			$out .= ' ' . esc_html( $name );
			if ( ! empty( $value ) ) {
				$out .= '="' . esc_attr( $value ) . '"';
			}
		}

		return trim( $out );

	}
}

if ( ! function_exists( 'fusion_breadcrumbs' ) ) {
	/**
	 * Render the breadcrumbs with help of class-breadcrumbs.php.
	 *
	 * @return void
	 */
	function fusion_breadcrumbs() {
		$breadcrumbs = Fusion_Breadcrumbs::get_instance();
		$breadcrumbs->get_breadcrumbs();
	}
}

if ( ! function_exists( 'fusion_strip_unit' ) ) {
	/**
	 * Strips the unit from a given value.
	 *
	 * @param  string $value The value with or without unit.
	 * @param  string $unit_to_strip The unit to be stripped.
	 * @return string   the value without a unit.
	 */
	function fusion_strip_unit( $value, $unit_to_strip = 'px' ) {
		$value_length = strlen( $value );
		$unit_length  = strlen( $unit_to_strip );

		if ( $value_length > $unit_length && 0 === substr_compare( $value, $unit_to_strip, $unit_length * ( -1 ), $unit_length ) ) {
			return substr( $value, 0, $value_length - $unit_length );
		} else {
			return $value;
		}
	}
}

add_filter( 'feed_link', 'fusion_feed_link', 1, 2 );
if ( ! function_exists( 'fusion_feed_link' ) ) {
	/**
	 * Replace default WP RSS feed link with global option RSS feed link.
	 *
	 * @param  string $output Feed link.
	 * @param  string $feed   Feed type.
	 * @return string         Return modified feed link.
	 */
	function fusion_feed_link( $output, $feed ) {
		if ( Avada()->settings->get( 'rss_link' ) ) {
			$feed_url = Avada()->settings->get( 'rss_link' );

			$feed_array          = array(
				'rss'           => $feed_url,
				'rss2'          => $feed_url,
				'atom'          => $feed_url,
				'rdf'           => $feed_url,
				'comments_rss2' => '',
			);
			$feed_array[ $feed ] = $feed_url;
			$output              = $feed_array[ $feed ];
		}

		return $output;
	}
}


add_filter( 'the_excerpt_rss', 'fusion_feed_excerpt' );
if ( ! function_exists( 'fusion_feed_excerpt' ) ) {
	/**
	 * Modifies feed description, by extracting shortcode contents.
	 *
	 * @since  5.0.4
	 * @param  string $excerpt The post excerpt.
	 * @return string The modified post excerpt.
	 */
	function fusion_feed_excerpt( $excerpt ) {

		$excerpt = wp_strip_all_tags( fusion_get_post_content_excerpt( 55, true ) );

		return $excerpt;

	}
}

if ( ! function_exists( 'fusion_build_url' ) ) {
	/**
	 * Build final URL form $url_data returned from fusion_add_url_paramtere.
	 *
	 * @param  array $url_data  url data with custom params.
	 * @return string           fully formed url with custom params.
	 */
	function fusion_build_url( $url_data ) {
		$url = '';
		if ( isset( $url_data['host'] ) ) {
			$url .= $url_data['scheme'] . '://';
			if ( isset( $url_data['user'] ) ) {
				$url .= $url_data['user'];
				if ( isset( $url_data['pass'] ) ) {
					$url .= ':' . $url_data['pass'];
				}
				$url .= '@';
			}
			$url .= $url_data['host'];
			if ( isset( $url_data['port'] ) ) {
				$url .= ':' . $url_data['port'];
			}
		}

		if ( isset( $url_data['path'] ) ) {
			$url .= $url_data['path'];
		}

		if ( isset( $url_data['query'] ) ) {
			$url .= '?' . $url_data['query'];
		}

		if ( isset( $url_data['fragment'] ) ) {
			$url .= '#' . $url_data['fragment'];
		}

		return $url;
	}
}

if ( ! function_exists( 'fusion_compress_css' ) ) {
	/**
	 * Compress CSS
	 *
	 * @param  string $minify CSS to compress.
	 * @return string         Compressed CSS.
	 */
	function fusion_compress_css( $minify ) {
		// Remove comments.
		$minify = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $minify );

		// Remove tabs, spaces, newlines, etc.
		return str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $minify );
	}
}

if ( ! function_exists( 'fusion_count_widgets' ) ) {
	/**
	 * Count number of widgets in the widget area.
	 *
	 * @param  string $area_id ID of widget area.
	 * @return int             Number of widgets.
	 */
	function fusion_count_widgets( $area_id ) {
		global $_wp_sidebars_widgets;

		if ( empty( $_wp_sidebars_widgets ) ) {
			$_wp_sidebars_widgets = get_option( 'sidebars_widgets', array() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}

		$sidebars_widgets_count = $_wp_sidebars_widgets;

		if ( isset( $sidebars_widgets_count[ $area_id ] ) ) {
			return count( $sidebars_widgets_count[ $area_id ] );
		}

		return 0;
	}
}

if ( ! function_exists( 'fusion_import_to_media_library' ) ) {
	/**
	 * Imports a file to the media library.
	 *
	 * @param string $url The file URL.
	 * @param string $theme_option If we're doing this for a global option,
	 *                             specify which option that is to properly save the data.
	 */
	function fusion_import_to_media_library( $url, $theme_option = '' ) {

		// Gives us access to the download_url() and wp_handle_sideload() functions.
		require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );

		$timeout_seconds = 30;

		// Download file to temp dir.
		$temp_file = download_url( $url, $timeout_seconds );

		if ( ! is_wp_error( $temp_file ) ) {
			// Array based on $_FILE as seen in PHP file uploads.
			$file = array(
				'name'     => basename( $url ),
				'type'     => 'image/png',
				'tmp_name' => $temp_file,
				'error'    => 0,
				'size'     => filesize( $temp_file ),
			);

			$overrides = array(
				// Tells WordPress to not look for the POST form
				// fields that would normally be present, default is true,
				// we downloaded the file from a remote server, so there
				// will be no form fields.
				'test_form'   => false,

				// Setting this to false lets WordPress allow empty files, not recommended.
				'test_size'   => true,

				// A properly uploaded file will pass this test.
				// There should be no reason to override this one.
				'test_upload' => true,
			);

			// Move the temporary file into the uploads directory.
			$results = wp_handle_sideload( $file, $overrides );

			if ( ! empty( $results['error'] ) ) {
				return false;
			}
			$attachment = array(
				'guid'           => $results['url'],
				'post_mime_type' => $results['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $results['file'] ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			// Insert the attachment.
			$attach_id = wp_insert_attachment( $attachment, $results['file'] );

			// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
			require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/image.php' );

			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $attach_id, $results['file'] );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			if ( $theme_option ) {
				Avada()->settings->set( $theme_option, $results['url'] );
			}

			return $attach_id;
		}
		return false;
	}
}
/* Omit closing PHP tag to avoid "Headers already sent" issues. */

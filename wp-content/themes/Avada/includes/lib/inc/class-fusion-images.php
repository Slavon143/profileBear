<?php
/**
 * Images handler.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * Handle images.
 * Includes responsive-images tweaks.
 *
 * @since 1.0.0
 */
class Fusion_Images {

	/**
	 * The grid image meta.
	 *
	 * @static
	 * @access public
	 * @var array
	 */
	public static $grid_image_meta;

	/**
	 * An array of the accepted widths.
	 *
	 * @static
	 * @access public
	 * @var array
	 */
	public static $grid_accepted_widths;

	/**
	 * An array of supported layouts.
	 *
	 * @static
	 * @access public
	 * @var array
	 */
	public static $supported_grid_layouts;

	/**
	 * Ratio used for masonry calculations.
	 *
	 * @static
	 * @access public
	 * @var float
	 */
	public static $masonry_grid_ratio;

	/**
	 * Width used for masonry 2x2 calculations.
	 *
	 * @static
	 * @access public
	 * @var int
	 */
	public static $masonry_width_double;

	/**
	 * Whether lazy load is active or not.
	 *
	 * @static
	 * @access public
	 * @var bool
	 */
	public static $is_avada_lazy_load_images;

	/**
	 * Whether avada iframes lazy load is active or not.
	 *
	 * @var bool
	 */
	public static $is_avada_lazy_load_iframes;

	/**
	 * Constructor.
	 *
	 * @access  public
	 */
	public function __construct() {
		$fusion_settings = awb_get_fusion_settings();

		self::$grid_image_meta            = [];
		self::$grid_accepted_widths       = [ '200', '400', '600', '800', '1200' ];
		self::$supported_grid_layouts     = [ 'masonry', 'grid', 'timeline', 'large', 'portfolio_full', 'related-posts' ];
		self::$masonry_grid_ratio         = $fusion_settings->get( 'masonry_grid_ratio' );
		self::$masonry_width_double       = $fusion_settings->get( 'masonry_width_double' );
		self::$is_avada_lazy_load_images  = 'avada' === $fusion_settings->get( 'lazy_load' ) ? true : false;
		self::$is_avada_lazy_load_iframes = 'avada' === $fusion_settings->get( 'lazy_load_iframes' ) ? true : false;

		// Enbale SVG file upload.
		if ( 'enabled' === $fusion_settings->get( 'svg_upload' ) ) {
			add_filter( 'upload_mimes', [ $this, 'allow_svg' ] );
			add_filter( 'wp_check_filetype_and_ext', [ $this, 'correct_svg_filetype' ], 10, 5 );
		}

		// Disable WP lazy loading for both, "Avada" method and "None".
		if ( 'wordpress' !== $fusion_settings->get( 'lazy_load' ) ) {
			add_filter( 'wp_lazy_loading_enabled', [ $this, 'remove_wp_image_lazy_loading' ], 10, 2 );

			// Skip lazy loading for fancy product designer plugin.
			if ( class_exists( 'WooCommerce' ) && fusion_is_plugin_activated( 'fancy-product-designer/fancy-product-designer.php' ) ) {
				add_filter( 'woocommerce_cart_item_thumbnail', [ $this, 'skip_lazy_loading' ], 110, 3 );
			}

			add_filter( 'wp_get_attachment_image_attributes', [ $this, 'remove_lazy_loading_attr_from_image' ] );
		}

		if ( 'wordpress' !== $fusion_settings->get( 'lazy_load_iframes' ) ) {
			add_filter( 'wp_lazy_loading_enabled', [ $this, 'remove_wp_iframe_lazy_loading' ], 10, 2 );
		}

		add_filter( 'max_srcset_image_width', [ $this, 'set_max_srcset_image_width' ] );
		add_filter( 'wp_calculate_image_srcset', [ $this, 'set_largest_image_size' ], 10, 5 );
		add_filter( 'wp_calculate_image_srcset', [ $this, 'edit_grid_image_srcset' ], 15, 5 );
		add_filter( 'wp_calculate_image_sizes', [ $this, 'edit_grid_image_sizes' ], 10, 5 );
		add_filter( 'post_thumbnail_html', [ $this, 'edit_grid_image_src' ], 10, 5 );
		add_action( 'delete_attachment', [ $this, 'delete_resized_images' ] );
		add_filter( 'wpseo_sitemap_urlimages', [ $this, 'extract_img_src_for_yoast' ], '10', '2' );
		add_filter( 'fusion_library_image_base_size_width', [ $this, 'fb_adjust_grid_image_base_size' ], 20, 4 );
		add_filter( 'fusion_masonry_element_class', [ $this, 'adjust_masonry_element_class' ], 10, 2 );
		add_filter( 'attachment_fields_to_edit', [ $this, 'add_image_meta_fields' ], 10, 2 );
		add_filter( 'attachment_fields_to_save', [ $this, 'save_image_meta_fields' ], 10, 2 );
		add_action( 'admin_head', [ $this, 'style_image_meta_fields' ] );
		add_filter( 'wp_update_attachment_metadata', [ $this, 'remove_dynamically_generated_images' ], 10, 2 );
		add_action( 'wp', [ $this, 'enqueue_lazy_loading_scripts' ] );
		add_filter( 'post_thumbnail_html', [ $this, 'apply_lazy_loading' ], 99, 5 );
		add_filter( 'wp_get_attachment_image_attributes', [ $this, 'lazy_load_attributes' ], 10, 2 );
		add_action( 'the_content', [ $this, 'apply_bulk_lazy_loading' ], 999 );
		add_action( 'the_content', [ $this, 'apply_bulk_avada_lazy_loading_iframe' ], 999 );
		add_filter( 'revslider_layer_content', [ $this, 'prevent_rev_lazy_loading' ], 10, 5 );
		add_filter( 'layerslider_slider_markup', [ $this, 'prevent_ls_lazy_loading' ], 10, 3 );
		add_filter( 'wp_get_attachment_metadata', [ $this, 'map_old_image_size_names' ], 10, 2 );
		add_filter( 'wp_get_attachment_image_src', [ $this, 'wp_get_attachment_image_fix_svg' ], 10, 4 );
		add_filter( 'rank_math/sitemap/urlimages', [ $this, 'extract_img_src_for_rank_math' ], 10, 2 );
	}

	/**
	 * Adds lightbox attributes to links.
	 *
	 * @param string $link          The link.
	 * @param int    $attachment_id The attachment ID.
	 * @param string $size          Size of the image. Image size or array of width and height values (in that order).
	 *                              Default 'thumbnail'.
	 * @return string               The updated attachment link.
	 */
	public function prepare_lightbox_links( $link, $attachment_id, $size ) {
		if ( ! is_string( $size ) ) {
			$size = 'full';
		}

		$attachment_data = $this->get_attachment_data( $attachment_id, $size );

		$title   = $attachment_data['title_attribute'];
		$caption = $attachment_data['caption_attribute'];
		$link    = preg_replace( '/<a/', '<a data-rel="iLightbox[postimages]" data-title="' . $title . '" data-caption="' . $caption . '"', $link, 1 );

		return $link;
	}

	/**
	 * Modify the maximum image width to be included in srcset attribute.
	 *
	 * @since 1.0.0
	 * @param int $max_width  The maximum image width to be included in the 'srcset'. Default '1600'.
	 * @return int  The new max width.
	 */
	public function set_max_srcset_image_width( $max_width ) {
		return 1920;
	}

	/**
	 * Add the fullsize image to the scrset attribute.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $sources {
	 *     One or more arrays of source data to include in the 'srcset'.
	 *
	 *     @type array $width {
	 *         @type string $url        The URL of an image source.
	 *         @type string $descriptor The descriptor type used in the image candidate string,
	 *                                  either 'w' or 'x'.
	 *         @type int    $value      The source width if paired with a 'w' descriptor, or a
	 *                                  pixel density value if paired with an 'x' descriptor.
	 *     }
	 * }
	 * @param array  $size_array    Array of width and height values in pixels (in that order).
	 * @param string $image_src     The 'src' of the image.
	 * @param array  $image_meta    The image meta data as returned by 'wp_get_attachment_metadata()'.
	 * @param int    $attachment_id Image attachment ID or 0.
	 *
	 * @return array $sources       One or more arrays of source data to include in the 'srcset'.
	 */
	public function set_largest_image_size( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		$cropped_image = false;

		foreach ( $sources as $source => $details ) {
			if ( $details['url'] === $image_src ) {
				$cropped_image = true;
			}
		}

		if ( ! $cropped_image ) {
			$full_image_src = wp_get_attachment_image_src( $attachment_id, 'full' );

			$full_size = [
				'url'        => $full_image_src[0],
				'descriptor' => 'w',
				'value'      => $image_meta['width'],
			];

			$sources[ $image_meta['width'] ] = $full_size;
		}

		return $sources;
	}

	/**
	 * Filter out all srcset attributes, that do not fit current grid layout.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $sources {
	 *     One or more arrays of source data to include in the 'srcset'.
	 *
	 *     @type array $width {
	 *         @type string $url        The URL of an image source.
	 *         @type string $descriptor The descriptor type used in the image candidate string,
	 *                                  either 'w' or 'x'.
	 *         @type int    $value      The source width if paired with a 'w' descriptor, or a
	 *                                  pixel density value if paired with an 'x' descriptor.
	 *     }
	 * }
	 * @param array  $size_array    Array of width and height values in pixels (in that order).
	 * @param string $image_src     The 'src' of the image.
	 * @param array  $image_meta    The image meta data as returned by 'wp_get_attachment_metadata()'.
	 * @param int    $attachment_id Image attachment ID or 0.
	 *
	 * @return array $sources       One or more arrays of source data to include in the 'srcset'.
	 */
	public function edit_grid_image_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		// Only do manipulation for blog images.
		if ( ! empty( self::$grid_image_meta ) ) {
			// Only include the uncropped sizes in srcset.
			foreach ( $sources as $width => $source ) {

				// Make sure the original image isn't deleted.
				preg_match( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif|tiff|svg)$)/i', $source['url'], $matches );

				if ( ! in_array( $width, self::$grid_accepted_widths ) && isset( $matches[0] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
					unset( $sources[ $width ] );
				}
			}
		}

		ksort( $sources );

		return $sources;
	}

	/**
	 * Edits the'sizes' attribute for grid images.
	 *
	 * @since 1.0.0
	 *
	 * @param string       $sizes         A source size value for use in a 'sizes' attribute.
	 * @param array|string $size          Image size to retrieve. Accepts any valid image size, or an array
	 *                                    of width and height values in pixels (in that order). Default 'medium'.
	 * @param string       $image_src     Optional. The URL to the image file. Default null.
	 * @param array        $image_meta    Optional. The image meta data as returned by 'wp_get_attachment_metadata()'.
	 *                                    Default null.
	 * @param int          $attachment_id Optional. Image attachment ID. Either `$image_meta` or `$attachment_id`
	 *                                    is needed when using the image size name as argument for `$size`. Default 0.
	 * @return string|bool A valid source size value for use in a 'sizes' attribute or false.
	 */
	public function edit_grid_image_sizes( $sizes, $size, $image_src, $image_meta, $attachment_id ) {
		if ( isset( self::$grid_image_meta['layout'] ) ) {
			$content_width = apply_filters( 'fusion_library_content_width', 1170 );

			// Flex columns content breakpoints.
			if ( function_exists( 'fusion_builder_container' ) && fusion_builder_container()->is_flex() ) {
				$content_break_point = '';

				if ( '1_1' === fusion_library()->get_option( 'col_width_medium' ) ) {
					$content_break_point .= '(max-width: ' . fusion_library()->get_option( 'visibility_medium' ) . 'px) 100vw, ';
				}

				if ( '1_1' === fusion_library()->get_option( 'col_width_small' ) ) {
					$content_break_point .= '(max-width: ' . fusion_library()->get_option( 'visibility_small' ) . 'px) 100vw, ';
				}
			} else {

				// Legacy columns content breakpoints.
				$content_break_point = apply_filters( 'fusion_library_content_break_point', 1100 );
				$content_break_point = '(max-width: ' . $content_break_point . 'px) 100vw, ';
			}

			if ( isset( self::$grid_image_meta['gutter_width'] ) ) {
				$content_width -= (int) self::$grid_image_meta['gutter_width'] * ( (int) self::$grid_image_meta['columns'] - 1 );
			}

			// Grid.
			if ( in_array( self::$grid_image_meta['layout'], [ 'masonry', 'grid', 'portfolio_full', 'related-posts' ], true ) ) {

				$main_break_point = (int) apply_filters( 'fusion_library_grid_main_break_point', 800 );
				if ( 640 < $main_break_point ) {
					$breakpoint_range = $main_break_point - 640;
				} else {
					$breakpoint_range = 360;
				}

				$breakpoint_interval = $breakpoint_range / 5;

				$main_image_break_point = apply_filters( 'fusion_library_main_image_breakpoint', $main_break_point );
				$break_points           = apply_filters(
					'fusion_library_image_breakpoints',
					[
						6 => $main_image_break_point,
						5 => $main_image_break_point - $breakpoint_interval,
						4 => $main_image_break_point - 2 * $breakpoint_interval,
						3 => $main_image_break_point - 3 * $breakpoint_interval,
						2 => $main_image_break_point - 4 * $breakpoint_interval,
						1 => $main_image_break_point - 5 * $breakpoint_interval,
					]
				);

				$sizes = apply_filters( 'fusion_library_image_grid_initial_sizes', '', $main_break_point, (int) self::$grid_image_meta['columns'] );

				$sizes .= '(min-width: 2200px) 100vw, ';

				foreach ( $break_points as $columns => $breakpoint ) {
					if ( $columns <= (int) self::$grid_image_meta['columns'] ) {
						$width = $content_width / $columns;

						// For one column layouts where the content width is larger than column breakpoint width, don't reset the width.
						if ( $breakpoint < $width && ! ( 1 === (int) self::$grid_image_meta['columns'] && $content_width > $breakpoint + $breakpoint_interval ) ) {
							$width = $breakpoint + $breakpoint_interval;
						}

						$sizes .= '(min-width: ' . round( $breakpoint ) . 'px) ' . round( $width ) . 'px, ';
					}
				}
			} elseif ( 'timeline' === self::$grid_image_meta['layout'] ) { // Timeline.
				$width = 40;
				$sizes = $content_break_point . $width . 'vw';

				// Large Layouts (e.g. person or image element).
			} elseif ( false !== strpos( self::$grid_image_meta['layout'], 'large' ) ) {

				// If possible, set the correct size for the content width, depending on columns.
				if ( $attachment_id ) {
					$base_image_size = $this->get_grid_image_base_size( $attachment_id, self::$grid_image_meta['layout'], self::$grid_image_meta['columns'], 'get_closest_ceil' );

					if ( is_integer( $base_image_size ) ) {
						global $fusion_col_type;

						if ( ! empty( $fusion_col_type['type'] ) && class_exists( 'Avada' ) && Avada()->layout->is_current_wrapper_hundred_percent() ) {
							if ( false !== strpos( $fusion_col_type['type'], '.' ) ) {

								// Custom % value.
								$max_image_size = $fusion_col_type['type'];
							} elseif ( false !== strpos( $fusion_col_type['type'], '_' ) ) {

								// Standard columns.
								$coeff          = explode( '_', $fusion_col_type['type'] );
								$max_image_size = round( $coeff[0] / $coeff[1] * 100 );
							}

							$additional_sizes  = '(max-width: 1919px) ' . $base_image_size . 'px,';
							$additional_sizes .= '(min-width: 1920px) ' . $max_image_size . 'vw';
						} else {
							$additional_sizes = $base_image_size . 'px';
						}
					} else {
						$additional_sizes = $size[0] . 'px';
					}
				}

				$sizes = $content_break_point . $additional_sizes;
			}
		}

		return $sizes;
	}

	/**
	 * Change the src attribute for grid images.
	 *
	 * @since 1.0.0
	 *
	 * @param string       $html              The post thumbnail HTML.
	 * @param int          $post_id           The post ID.
	 * @param string       $post_thumbnail_id The post thumbnail ID.
	 * @param string|array $size              The post thumbnail size. Image size or array of width and height
	 *                                        values (in that order). Default 'post-thumbnail'.
	 * @param string       $attr              Query string of attributes.
	 * @return string The html markup of the image.
	 */
	public function edit_grid_image_src( $html, $post_id = null, $post_thumbnail_id = null, $size = null, $attr = null ) {
		if ( ! $this->is_lazy_load_enabled() && isset( self::$grid_image_meta['layout'] ) && in_array( self::$grid_image_meta['layout'], self::$supported_grid_layouts ) && 'full' === $size ) { // phpcs:ignore WordPress.PHP.StrictInArray
			$image_size = $this->get_grid_image_base_size( $post_thumbnail_id, self::$grid_image_meta['layout'], self::$grid_image_meta['columns'] );

			$full_image_src = wp_get_attachment_image_src( $post_thumbnail_id, $image_size );
			if ( $full_image_src ) {
				$html = preg_replace( '@src="([^"]+)"@', 'src="' . $full_image_src[0] . '"', $html );
			}
		}
		return $html;
	}

	/**
	 * Get image size based on column size.
	 *
	 * @since 1.0.0
	 *
	 * @param null|int    $post_thumbnail_id Attachment ID.
	 * @param null|string $layout            The layout.
	 * @param null|int    $columns           Number of columns.
	 * @param string      $match_basis       Use 'get_closest' or 'get_closest_ceil'.
	 * @return string Image size name.
	 */
	public function get_grid_image_base_size( $post_thumbnail_id = null, $layout = null, $columns = null, $match_basis = 'get_closest' ) {
		$sizes = [];

		// Get image metadata.
		$image_meta = wp_get_attachment_metadata( $post_thumbnail_id );

		if ( $image_meta ) {
			$image_sizes = [];
			if ( isset( $image_meta['sizes'] ) && ! empty( $image_meta['sizes'] ) ) {
				$image_sizes = $image_meta['sizes'];
			}

			if ( $image_sizes && is_array( $image_sizes ) ) {

				foreach ( $image_sizes as $name => $image ) {
					$size_name = str_replace( 'fusion-', '', $name );
					if ( in_array( $size_name, self::$grid_accepted_widths, true ) ) {
						// Create accepted sizes array.
						if ( $image['width'] ) {
							$sizes[ $image['width'] ] = intval( $size_name );
						}
					}
				}
			}

			if ( isset( $image_meta['width'] ) ) {
				$sizes[ $image_meta['width'] ] = 'full';
			}
		}
		$gutter = isset( self::$grid_image_meta['gutter_width'] ) ? self::$grid_image_meta['gutter_width'] : '';
		$width  = apply_filters( 'fusion_library_image_base_size_width', 1000, $layout, $columns, $gutter );

		ksort( $sizes );

		$image_size = null;
		$size_name  = null;

		// Find the best match.
		foreach ( $sizes as $size => $name ) {

			// Find closest size match.
			$match_condition = null === $image_size || abs( $width - $image_size ) > abs( $size - $width );

			// Find closest match greater than available width.
			if ( 'get_closest_ceil' === $match_basis ) {
				$match_condition = $size > $width && abs( $width - $image_size ) > abs( $size - $width );
			}

			if ( $match_condition ) {
				$image_size = $size;
				$size_name  = $name;
			}
		}

		// Fallback to 'full' image size if no match was found.
		if ( ! $size_name || empty( $size_name ) ) {
			$size_name = 'full';
		}

		return $size_name;
	}

	/**
	 * Returns adjusted width of an image container.
	 * Adjustment is made based on Avada Builder column image container is currently in.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param  int    $width           The container width in pixels.
	 * @param  string $layout          The layout name.
	 * @param  int    $columns         The number of columns used as a divider.
	 * @param  int    $gutter_width    The gutter width - in pixels.
	 * @global array  $fusion_col_type The current column padding and type.
	 * @return int
	 */
	public function fb_adjust_grid_image_base_size( $width, $layout = 'large', $columns = 1, $gutter_width = 30 ) {
		global $fusion_col_type;

		if ( ! empty( $fusion_col_type['type'] ) ) {

			// Do some advanced column size calcs respecting margins for better column width estimation.
			if ( ! empty( $fusion_col_type['margin'] ) ) {
				$width = $this->calc_width_respecting_spacing( $width, $fusion_col_type['margin']['large'] );
			} elseif ( ! empty( $fusion_col_type['spacings'] ) ) {
				$width = $this->calc_width_respecting_spacing( $width, $fusion_col_type['spacings'] );
			}

			// Calc the column width.
			if ( false !== strpos( $fusion_col_type['type'], '.' ) ) {

				// Custom % value.
				$width = absint( $width * $fusion_col_type['type'] / 100 );
			} elseif ( false !== strpos( $fusion_col_type['type'], '_' ) ) {

				// Standard columns.
				$coeff = explode( '_', $fusion_col_type['type'] );
				$width = absint( $width * $coeff[0] / $coeff[1] );
			}

			// Do some advanced column size calcs respecting in column paddings for better column width estimation.
			if ( isset( $fusion_col_type['padding'] ) ) {
				$padding = explode( ' ', $fusion_col_type['padding'] );

				if ( isset( $padding[1] ) && isset( $padding[3] ) ) {
					$padding = [ $padding[1], $padding[3] ];

					$width = $this->calc_width_respecting_spacing( $width, $padding );
				}
			}
		}

		return $width;
	}

	/**
	 * Reduces a given width by the amount of spacing set.
	 *
	 * @since 1.8.0
	 * @param int   $width         The width to be reduced.
	 * @param array $spacing_array The array of spacings that need subtracted.
	 * @return int The reduced width.
	 */
	public function calc_width_respecting_spacing( $width, $spacing_array ) {
		$fusion_settings = awb_get_fusion_settings();

		$base_font_size = $fusion_settings->get( 'body_typography', 'font-size' );

		foreach ( $spacing_array as $index => $spacing ) {
			if ( false !== strpos( $spacing, 'px' ) ) {
				$width -= (int) $spacing;
			} elseif ( false !== strpos( $base_font_size, 'px' ) && false !== strpos( $spacing, 'em' ) ) {
				$width -= (int) $base_font_size * (int) $spacing;
			} elseif ( false !== strpos( $spacing, '%' ) ) {
				$width -= $width * (int) $spacing / 100;
			}
		}

		return $width;
	}

	/**
	 * Setter function for the $grid_image_meta variable.
	 *
	 * @since 1.0.0
	 * @param array $grid_image_meta    Array containing layout and number of columns.
	 * @return void
	 */
	public function set_grid_image_meta( $grid_image_meta ) {
		self::$grid_image_meta = $grid_image_meta;
	}

	/**
	 * Gets the ID of the "translated" attachment.
	 *
	 * @static
	 * @since 1.2.1
	 * @param int $attachment_id The base attachment ID.
	 * @return int The ID of the "translated" attachment.
	 */
	public static function get_translated_attachment_id( $attachment_id ) {

		$wpml_object_id = apply_filters( 'wpml_object_id', $attachment_id, 'attachment' );
		$attachment_id  = $wpml_object_id ? $wpml_object_id : $attachment_id;

		return $attachment_id;
	}

	/**
	 * Gets the base URL for an attachment.
	 *
	 * @static
	 * @since 1.2.1
	 * @param string $attachment_url The url of the used attachment.
	 * @return string The base URL of the attachment.
	 */
	public static function get_attachment_base_url( $attachment_url = '' ) {

		$attachment_url      = set_url_scheme( $attachment_url );
		$attachment_base_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif|tiff|svg)$)/i', '', $attachment_url );
		$attachment_base_url = apply_filters( 'fusion_get_attachment_base_url', $attachment_base_url );

		return $attachment_base_url;
	}

	/**
	 * Gets the attachment ID from the URL.
	 *
	 * @static
	 * @since 1.0
	 * @param string $attachment_url The URL of the attachment.
	 * @return string The attachment ID
	 */
	public static function get_attachment_id_from_url( $attachment_url = '' ) {
		global $wpdb;
		$attachment_id = false;

		if ( '' === $attachment_url || ! is_string( $attachment_url ) ) {
			return '';
		}

		$upload_dir_paths         = wp_upload_dir();
		$upload_dir_paths_baseurl = set_url_scheme( $upload_dir_paths['baseurl'] );

		// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image.
		if ( false !== strpos( $attachment_url, $upload_dir_paths_baseurl ) ) {

			// If this is the URL of an auto-generated thumbnail, get the URL of the original image.
			$attachment_url = self::get_attachment_base_url( $attachment_url );

			// Remove the upload path base directory from the attachment URL.
			$attachment_url = str_replace( $upload_dir_paths_baseurl . '/', '', $attachment_url );

			// Get the actual attachment ID.
			$attachment_id = attachment_url_to_postid( $attachment_url );
			$attachment_id = self::get_translated_attachment_id( $attachment_id );
		}

		return $attachment_id;
	}

	/**
	 * Gets the most important attachment data from the url.
	 *
	 * @since 1.0.0
	 * @param string $attachment_url The url of the used attachment.
	 * @return array/bool The attachment data of the image, false if the url is empty or attachment not found.
	 */
	public function get_attachment_data_from_url( $attachment_url = '' ) {

		if ( '' === $attachment_url ) {
			return false;
		}

		$attachment_data['id'] = self::get_attachment_id_from_url( $attachment_url );

		if ( ! $attachment_data['id'] ) {
			return false;
		}

		$attachment_data = $this->get_attachment_data( $attachment_data['id'], 'full', $attachment_url );

		return $attachment_data;
	}

	/**
	 * Gets the most important attachment data.
	 *
	 * @since 1.2
	 * @access public
	 * @param int    $attachment_id  The ID of the used attachment.
	 * @param string $size           The image size to be returned.
	 * @param string $attachment_url The URL of the attachment.
	 * @return array/bool            The attachment data of the image,
	 *                               false if the url is empty or attachment not found.
	 */
	public function get_attachment_data( $attachment_id = 0, $size = 'full', $attachment_url = '' ) {
		$attachment_data = [
			'id'                => 0,
			'url'               => '',
			'width'             => '',
			'height'            => '',
			'alt'               => '',
			'caption'           => '',
			'caption_attribute' => '',
			'title'             => '',
			'title_attribute'   => '',
		];
		$attachment_src  = false;

		if ( ! $attachment_id && ! $attachment_url ) {
			return $attachment_data;
		}

		if ( ! $attachment_id ) {
			$attachment_id = self::get_attachment_id_from_url( $attachment_url );
		} else {
			$attachment_id = self::get_translated_attachment_id( $attachment_id );

			$test_size      = ( 'none' === $size ) ? 'full' : $size;
			$attachment_src = wp_get_attachment_image_src( $attachment_id, $size );

			if ( ! $attachment_src ) {
				$attachment_id = self::get_attachment_id_from_url( $attachment_url );
			}
		}

		if ( ! $attachment_id ) {
			$attachment_data['url'] = $attachment_url;

			return $attachment_data;
		}

		$attachment_data['id'] = $attachment_id;

		if ( 'none' !== $size ) {
			$attachment_src = ( $attachment_src ) ? $attachment_src : wp_get_attachment_image_src( $attachment_id, $size );
			if ( $attachment_src ) {
				$attachment_data['url'] = esc_url( $attachment_src[0] );

				if ( $attachment_url && $attachment_data['url'] !== $attachment_url ) {
					$attachment_data['url'] = $attachment_url;
					preg_match( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif|tiff|svg|webp)$)/i', $attachment_url, $matches );
					if ( $matches ) {
						$dimensions = explode( 'x', $matches[0] );
						if ( 2 <= count( $dimensions ) ) {
							$attachment_data['width']  = absint( $dimensions[0] );
							$attachment_data['height'] = absint( $dimensions[1] );
						}
					} else {
						$attachment_data['width']  = $attachment_src[1] ? absint( $attachment_src[1] ) : '';
						$attachment_data['height'] = $attachment_src[1] ? absint( $attachment_src[2] ) : '';
					}
				} else {
					$attachment_data['width']  = $attachment_src[1] ? absint( $attachment_src[1] ) : '';
					$attachment_data['height'] = $attachment_src[1] ? absint( $attachment_src[2] ) : '';
				}
			}
		}

		$attachment_data['alt'] = esc_attr( get_post_field( '_wp_attachment_image_alt', $attachment_id ) );

		// Check for WP versions prior to 4.6.
		if ( function_exists( 'wp_get_attachment_caption' ) ) {
			$attachment_data['caption'] = wp_get_attachment_caption( $attachment_id );
		} else {
			$post = get_post( $attachment_id );

			$attachment_data['caption'] = ( $post ) ? $post->post_excerpt : '';
		}
		$attachment_data['caption_attribute'] = esc_attr( strip_tags( $attachment_data['caption'] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$attachment_data['title']             = get_the_title( $attachment_id );
		$attachment_data['title_attribute']   = esc_attr( strip_tags( $attachment_data['title'] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		return $attachment_data;
	}

	/**
	 * Gets the most important attachment data.
	 *
	 * @since 1.2.1
	 * @access public
	 * @param string $attachment_id_size The ID and size of the used attachmen in a string separated by |.
	 * @param string $attachment_url     The URL of the attachment.
	 * @return array/bool                The attachment data of the image,
	 *                                   false if the url is empty or attachment not found.
	 */
	public function get_attachment_data_by_helper( $attachment_id_size = 0, $attachment_url = '' ) {
		$attachment_data = false;

		// Image ID is set, so we can get the image data directly.
		if ( $attachment_id_size ) {
			$attachment_id_size = explode( '|', $attachment_id_size );

			// Both image ID and image size are available.
			if ( 2 === count( $attachment_id_size ) ) {
				$attachment_data = $this->get_attachment_data( $attachment_id_size[0], $attachment_id_size[1], $attachment_url );
			} else {

				// Only image ID is available.
				$attachment_data = $this->get_attachment_data( $attachment_id_size[0], 'full', $attachment_url );
			}
		} elseif ( $attachment_url ) {

			// Fallback, if we don't have the image ID, we have to get the data through the image URL.
			$attachment_data = $this->get_attachment_data( 0, 'full', $attachment_url );
		}

		return $attachment_data;
	}

	/**
	 * Deletes the resized images when the original image is deleted from the WordPress Media Library.
	 * This is necessary in order to handle custom image sizes created from the Fusion_Image_Resizer class.
	 *
	 * @access public
	 * @param int   $post_id The post ID.
	 * @param array $delete_image_sizes Array of images sizes to be deleted. All are deleted if empty.
	 * @return void
	 */
	public function delete_resized_images( $post_id, $delete_image_sizes = [] ) {
		// Get attachment image metadata.
		$metadata = wp_get_attachment_metadata( $post_id );
		if ( ! $metadata ) {
			return;
		}
		$wp_filesystem = Fusion_Helper::init_filesystem();

		// Do some bailing if we cannot continue.
		if ( ! isset( $metadata['file'] ) || ! isset( $metadata['image_meta']['resized_images'] ) ) {
			return;
		}
		$pathinfo       = pathinfo( $metadata['file'] );
		$resized_images = isset( $metadata['image_meta']['resized_images'] ) ? $metadata['image_meta']['resized_images'] : [];
		// Get WordPress uploads directory (and bail if it doesn't exist).
		$wp_upload_dir = wp_upload_dir();
		$upload_dir    = $wp_upload_dir['basedir'];
		if ( ! is_dir( $upload_dir ) ) {
			return;
		}
		// Delete the resized images.
		foreach ( $resized_images as $handle => $dims ) {
			if ( ! empty( $delete_image_sizes ) && ! in_array( $handle, $delete_image_sizes ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				continue;
			}

			// Get the resized images filename.
			$file = $upload_dir . '/' . $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '-' . $dims . '.' . $pathinfo['extension'];
			// Delete the resized image.
			$wp_filesystem->delete( $file, false, 'f' );

			// Get the retina resized images filename.
			$retina_file = $upload_dir . '/' . $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '-' . $dims . '@2x.' . $pathinfo['extension'];
			// Delete the resized retina image.
			$wp_filesystem->delete( $retina_file, false, 'f' );
		}
	}


	/**
	 * Adds [fusion_imageframe], [fusion_gallery] and [fusion_image_before_after] images to Yoast SEO XML sitemap.
	 *
	 * @since 1.0.0
	 * @param array $images Current post images.
	 * @param int   $post_id The post ID.
	 */
	public function extract_img_src_for_yoast( $images, $post_id ) {
		$post    = get_post( $post_id );
		$content = $post->post_content;

		// For images from fusion_imageframe shortcode.
		if ( preg_match_all( '/\[fusion_imageframe(.+?)?\](?:(.+?)?\[\/fusion_imageframe\])?/', $content, $matches ) ) {

			foreach ( $matches[0] as $image_frame ) {
				$src = '';

				if ( false === strpos( $image_frame, '<img' ) && $image_frame ) {

					$pattern = get_shortcode_regex();
					$matches = [];
					preg_match( "/$pattern/s", $image_frame, $matches );
					$src = $matches[5];
				} else {
					preg_match( '/(src=["\'](.*?)["\'])/', $image_frame, $src );

					if ( array_key_exists( '2', $src ) ) {
						$src = $src[2];
					}
				}

				if ( ! in_array( $src, $images, true ) ) {
					$images[] = [
						'src' => $src,
					];
				}
			}
		}

		// For images from newer structure of gallery element.
		if ( preg_match_all( '/\[fusion_gallery_image(.+?)?\](?:(.+?)?\[\/fusion_gallery_image\])?/', $content, $matches ) ) {
			foreach ( $matches[0] as $item ) {
				$atts = shortcode_parse_atts( $item );
				if ( isset( $atts['image'] ) && ! empty( $atts['image'] ) ) {
					$images[] = [
						'src' => $atts['image'],
					];
				} elseif ( isset( $atts['image_id'] ) && ! empty( $atts['image_id'] ) ) {
					$images[] = [
						'src' => wp_get_attachment_url( $atts['image_id'] ),
					];
				}
			}
		}

		// For images from older structure fusion_gallery shortcode.
		if ( preg_match_all( '/\[fusion_gallery(.+?)?\](?:(.+?)?\[\/fusion_gallery\])?/', $content, $matches ) ) {
			foreach ( $matches[0] as $image_gallery ) {
				$atts = shortcode_parse_atts( $image_gallery );
				if ( isset( $atts['image_ids'] ) && ! empty( $atts['image_ids'] ) ) {
					$image_ids = explode( ',', $atts['image_ids'] );
					foreach ( $image_ids as $image_id ) {
						$images[] = [
							'src' => wp_get_attachment_url( $image_id ),
						];
					}
				}
			}
		}

		// For images from fusion_image_before_after shortcode.
		if ( preg_match_all( '/\[fusion_image_before_after(.+?)?\](?:(.+?)?\[\/fusion_image_before_after\])?/', $content, $matches ) ) {
			foreach ( $matches[0] as $item ) {
				$atts = shortcode_parse_atts( $item );
				if ( isset( $atts['before_image'] ) && ! empty( $atts['before_image'] ) ) {
					$images[] = [
						'src' => $atts['before_image'],
					];
				}
				if ( isset( $atts['after_image'] ) && ! empty( $atts['after_image'] ) ) {
					$images[] = [
						'src' => $atts['after_image'],
					];
				}
			}
		}

		return $images;
	}

	/**
	 * Returns element orientation class, based on width and height ratio of an attachment image.
	 *
	 * @since 1.1
	 * @param int   $attachment_id ID of attachment image.
	 * @param array $attachment    An image attachment array.
	 * @param float $ratio         The aspect ratio threshold.
	 * @param int   $width_double  Width above which 2x2 content should be displayed.
	 * @return string              Orientation class.
	 */
	public function get_element_orientation_class( $attachment_id = '', $attachment = [], $ratio = false, $width_double = false ) {
		$element_class = 'fusion-element-grid';
		$ratio         = $ratio ? $ratio : self::$masonry_grid_ratio;
		$width_double  = $width_double ? $width_double : self::$masonry_width_double;

		if ( empty( $attachment ) && '' !== $attachment_id ) {
			$attachment = wp_get_attachment_image_src( $attachment_id, 'full' );
		}

		if ( isset( $attachment[1] ) && isset( $attachment[2] ) && 0 < $attachment[1] && 0 < $attachment[2] ) {

			// Fallback to legacy calcs of Avada 5.4.2 or earlier.
			if ( '1.0' === $ratio ) {
				$fallback_ratio = 0.8;
				$lower_limit    = ( $fallback_ratio / 2 ) + ( $fallback_ratio / 4 );
				$upper_limit    = ( $fallback_ratio * 2 ) - ( $fallback_ratio / 2 );

				if ( $lower_limit > $attachment[2] / $attachment[1] ) {
					// Landscape image.
					$element_class = 'fusion-element-landscape';
				} elseif ( $upper_limit < $attachment[2] / $attachment[1] ) {
					// Portrait image.
					$element_class = 'fusion-element-portrait';
				} elseif ( $attachment[1] > $width_double ) {
					// 2x2 image.
					$element_class = 'fusion-element-landscape fusion-element-portrait';
				}
			} else {
				if ( $ratio < $attachment[1] / $attachment[2] ) {
					// Landscape image.
					$element_class = 'fusion-element-landscape';

				} elseif ( $ratio < $attachment[2] / $attachment[1] ) {
					// Portrait image.
					$element_class = 'fusion-element-portrait';
				} elseif ( $attachment[1] > $width_double ) {
					// 2x2 image.
					$element_class = 'fusion-element-landscape fusion-element-portrait';
				}
			}
		}

		return apply_filters( 'fusion_masonry_element_class', $element_class, $attachment_id );
	}

	/**
	 * Returns element orientation class, based on width and height ratio of an attachment image.
	 *
	 * @since 1.1
	 * @param string $element_orientation_class The orientation class.
	 * @return int|float.
	 */
	public function get_element_base_padding( $element_orientation_class = '' ) {
		$fusion_element_grid_padding = 0.8;

		$masonry_element_padding = [
			'fusion-element-grid'      => $fusion_element_grid_padding,
			'fusion-element-landscape' => $fusion_element_grid_padding / 2,
			'fusion-element-portrait'  => $fusion_element_grid_padding * 2,
		];

		if ( isset( $masonry_element_padding[ $element_orientation_class ] ) ) {
			$fusion_element_grid_padding = $masonry_element_padding[ $element_orientation_class ];
		}

		return $fusion_element_grid_padding;
	}

	/**
	 * Filters element orientation class, based on image meta.
	 *
	 * @since 1.5
	 * @param string $element_class Orientation class.
	 * @param int    $attachment_id ID of attachment image.
	 * @return string               Orientation class.
	 */
	public function adjust_masonry_element_class( $element_class, $attachment_id = '' ) {

		if ( '' !== $attachment_id ) {
			$image_meta_layout = get_post_meta( $attachment_id, 'fusion_masonry_element_layout', true );
			if ( $image_meta_layout && '' !== $image_meta_layout ) {
				$element_class = $image_meta_layout;
			}
		}

		return $element_class;
	}

	/**
	 * Add Image meta fields
	 *
	 * @param  array  $form_fields Fields to include in attachment form.
	 * @param  object $post        Attachment record in database.
	 * @return array  $form_fields Modified form fields.
	 */
	public function add_image_meta_fields( $form_fields, $post ) {

		if ( wp_attachment_is_image( $post->ID ) ) {
			$image_layout = '' !== get_post_meta( $post->ID, 'fusion_masonry_element_layout', true ) ? sanitize_text_field( get_post_meta( $post->ID, 'fusion_masonry_element_layout', true ) ) : '';

			$form_fields['fusion_masonry_element_layout'] = [
				'label' => __( 'Masonry Image Layout', 'Avada' ),
				'input' => 'html',
				'html'  => '<select name="attachments[' . $post->ID . '][fusion_masonry_element_layout]" id="attachments[' . $post->ID . '][fusion_masonry_element_layout]"">
					    <option value="">' . esc_html__( 'Default', 'Avada' ) . '</option>
						<option value="fusion-element-grid" ' . selected( 'fusion-element-grid', $image_layout, false ) . '>' . esc_html__( '1x1', 'Avada' ) . '</option>
						<option value="fusion-element-landscape" ' . selected( 'fusion-element-landscape', $image_layout, false ) . '>' . esc_html__( 'Landscape', 'Avada' ) . '</option>
						<option value="fusion-element-portrait" ' . selected( 'fusion-element-portrait', $image_layout, false ) . '>' . esc_html__( 'Portrait', 'Avada' ) . '</option>
						<option value="fusion-element-landscape fusion-element-portrait" ' . selected( 'fusion-element-landscape fusion-element-portrait', $image_layout, false ) . '>' . esc_html__( '2x2', 'Avada' ) . '</option>
					</select>',
				'helps' => __( 'Set layout which will be used when image is displayed in masonry.', 'Avada' ),
			];
		}

		return $form_fields;
	}

	/**
	 * Save values of Photographer Name and URL in media uploader
	 *
	 * @param  array $post       The post data for database.
	 * @param  array $attachment Attachment fields from $_POST form.
	 * @return array $post       Modified post data.
	 */
	public function save_image_meta_fields( $post, $attachment ) {

		if ( wp_attachment_is_image( $post['ID'] ) ) {
			if ( isset( $attachment['fusion_masonry_element_layout'] ) ) {
				if ( '' !== $attachment['fusion_masonry_element_layout'] ) {
					update_post_meta( $post['ID'], 'fusion_masonry_element_layout', $attachment['fusion_masonry_element_layout'] );
				} else {
					delete_post_meta( $post['ID'], 'fusion_masonry_element_layout' );
				}
			}
		}

		return $post;
	}

	/**
	 * Style image meta fields.
	 */
	public function style_image_meta_fields() {
		global $pagenow;

		if ( 'post.php' === $pagenow && wp_attachment_is_image( get_the_ID() ) ) {
			echo '<style type="text/css">.compat-field-fusion_masonry_element_layout th, .compat-field-fusion_masonry_element_layout td{display: block;}.compat-field-fusion_masonry_element_layout th{padding-bottom: 10px;}</style>';
		}

	}

	/**
	 * Removes dynamically created thumbnails.
	 *
	 * @since 1.6
	 *
	 * @param array $data          Array of updated attachment meta data.
	 * @param int   $attachment_id Attachment post ID.
	 *
	 * @return array $data         Array of updated attachment meta data.
	 */
	public function remove_dynamically_generated_images( $data, $attachment_id ) {

		if ( ! isset( $data['image_meta']['resized_images']['fusion-500'] ) ) {
			$this->delete_resized_images( $attachment_id, [ 'fusion-500' ] );
		}

		return $data;
	}

	/**
	 * Return placeholder image for given dimensions
	 *
	 * @static
	 * @access public
	 * @since 1.8.0
	 * @param int $width  Width of real image.
	 * @param int $height Height of real image.
	 *
	 * @return string     Placeholder html string.
	 */
	public static function get_lazy_placeholder( $width = 0, $height = 0 ) {
		$placeholder = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
		if ( isset( $width ) && isset( $height ) && $width && $height ) {
			$width  = (int) $width;
			$height = (int) $height;

			return 'data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%27' . $width . '%27%20height%3D%27' . $height . '%27%20viewBox%3D%270%200%20' . $width . '%20' . $height . '%27%3E%3Crect%20width%3D%27' . $width . '%27%20height%3D%27' . $height . '%27%20fill-opacity%3D%220%22%2F%3E%3C%2Fsvg%3E';
		}
		return apply_filters( 'fusion_library_lazy_placeholder', $placeholder, $width, $height );
	}

	/**
	 * Function used in filter 'wp_lazy_loading_enabled' to remove the lazy loading
	 * for the images.
	 *
	 * @since 3.6
	 * @param bool   $default The default value before filter is applied.
	 * @param string $tag_name The HTML tag name to disable lazy-loading for.
	 * @return bool
	 */
	public function remove_wp_image_lazy_loading( $default, $tag_name ) {
		if ( 'img' === $tag_name ) {
			return false;
		}

		return $default;
	}

	/**
	 * Function used in filter 'wp_lazy_loading_enabled' to remove the lazy loading
	 * for the iframes.
	 *
	 * @since 3.6
	 * @param bool   $default The default value before filter is applied.
	 * @param string $tag_name The HTML tag name to disable lazy-loading for.
	 * @return bool
	 */
	public function remove_wp_iframe_lazy_loading( $default, $tag_name ) {
		if ( 'iframe' === $tag_name ) {
			return false;
		}

		return $default;
	}

	/**
	 * Filter attributes for the current gallery image tag to add a 'data-full'
	 * data attribute.
	 *
	 * @access public
	 * @param array $atts       Gallery image tag attributes.
	 * @param mixed $attachment WP_Post object for the attachment or attachment ID.
	 * @return array (maybe) filtered gallery image tag attributes.
	 * @since 1.8.0
	 */
	public function lazy_load_attributes( $atts, $attachment ) {
		if ( $this->is_lazy_load_enabled() ) {

			$replaced_atts = $atts;

			if ( ! isset( $atts['class'] ) ) {
				$replaced_atts['class'] = 'lazyload';
			} elseif ( false !== strpos( $atts['class'], 'lazyload' ) || false !== strpos( $atts['class'], 'rev-slidebg' ) || false !== strpos( $atts['class'], 'ls-' ) || false !== strpos( $atts['class'], 'attachment-portfolio' ) ) {
				return $atts;
			} else {
				$replaced_atts['class'] .= ' lazyload';
			}

			if ( isset( $atts['data-ls'] ) ) {
				return $atts;
			}

			// Get image dimensions.
			$image_id  = is_object( $attachment ) ? $attachment->ID : $attachment;
			$meta_data = wp_get_attachment_metadata( $image_id );
			$width     = isset( $meta_data['width'] ) ? $meta_data['width'] : 0;
			$height    = isset( $meta_data['height'] ) ? $meta_data['height'] : 0;

			// No meta data, but we do have width and height set on tag.
			$width  = ! $width && isset( $atts['width'] ) ? $atts['width'] : $width;
			$height = ! $height && isset( $atts['height'] ) ? $atts['height'] : $height;

			$replaced_atts['data-orig-src'] = $atts['src'];

			if ( isset( $atts['srcset'] ) ) {
				$replaced_atts['srcset']      = self::get_lazy_placeholder( $width, $height );
				$replaced_atts['data-srcset'] = $atts['srcset'];
				$replaced_atts['data-sizes']  = 'auto';
			} else {
				$replaced_atts['src'] = self::get_lazy_placeholder( $width, $height );
			}

			unset( $replaced_atts['sizes'] );
			return $replaced_atts;
		}

		return $atts;
	}

	/**
	 * Removes "loading" from image attributes.
	 *
	 * @param array $atts THe image attributes.
	 * @return array
	 */
	public function remove_lazy_loading_attr_from_image( $atts ) {
		if ( isset( $atts['loading'] ) ) {
			unset( $atts['loading'] );
		}

		return $atts;
	}

	/**
	 * Filter markup for lazy loading.
	 *
	 * @since 1.8.0
	 *
	 * @param string       $html              The post thumbnail HTML.
	 * @param int          $post_id           The post ID.
	 * @param string       $post_thumbnail_id The post thumbnail ID.
	 * @param string|array $size              The post thumbnail size. Image size or array of width and height
	 *                                        values (in that order). Default 'post-thumbnail'.
	 * @param string       $attr              Query string of attributes.
	 * @return string The html markup of the image.
	 */
	public function apply_lazy_loading( $html, $post_id = null, $post_thumbnail_id = null, $size = null, $attr = null ) {
		if ( $this->is_lazy_load_enabled() && false === strpos( $html, 'lazyload' ) && false === strpos( $html, 'rev-slidebg' ) && false === strpos( $html, 'fusion-gallery-image-size-fixed' ) && false === strpos( $html, 'awb-instagram-masonry-image' ) ) {

			$src    = '';
			$width  = 0;
			$height = 0;

			// Get the image data from src.
			if ( $post_thumbnail_id && 'full' === $size ) {
				$full_image_src = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );

				// If image found, use the dimensions and src of image.
				if ( is_array( $full_image_src ) ) {
					$src    = isset( $full_image_src[0] ) ? $full_image_src[0] : $src;
					$width  = isset( $full_image_src[1] ) ? $full_image_src[1] : $width;
					$height = isset( $full_image_src[2] ) ? $full_image_src[2] : $height;
				}
			} else {

				// Get src from markup.
				preg_match( '@src="([^"]+)"@', $html, $src );
				if ( array_key_exists( 1, $src ) ) {
					$src = $src[1];
				} else {
					$src = '';
				}

				// Get dimensions from markup.
				preg_match( '/width="(.*?)"/', $html, $width );
				if ( array_key_exists( 1, $width ) ) {
					preg_match( '/height="(.*?)"/', $html, $height );
					if ( array_key_exists( 1, $height ) ) {
						$width  = $width[1];
						$height = $height[1];
					}
				} elseif ( $src && '' !== $src ) {

					// No dimensions on tag, try to get from image url.
					$full_image_src = $this->get_attachment_data_from_url( $src );
					if ( is_array( $full_image_src ) ) {
						$width  = isset( $full_image_src['width'] ) ? $full_image_src['width'] : $width;
						$height = isset( $full_image_src['height'] ) ? $full_image_src['height'] : $height;
					}
				}
			}

			// If src is a data image, just skip.
			if ( false !== strpos( $src, 'data:image' ) ) {
				return $html;
			}

			// Srcset replacement.
			if ( strpos( $html, 'srcset' ) ) {
				$html = str_replace(
					[
						' src=',
						' srcset=',
						' sizes=',
					],
					[
						' src="' . $src . '" data-orig-src=',
						' srcset="' . self::get_lazy_placeholder( $width, $height ) . '" data-srcset=',
						' data-sizes="auto" data-orig-sizes=',
					],
					$html
				);
			} else {

				// Simplified non srcset replacement.
				$html = str_replace( ' src=', ' src="' . self::get_lazy_placeholder( $width, $height ) . '" data-orig-src=', $html );
			}

			if ( strpos( $html, ' class=' ) ) {
				$html = str_replace( ' class="', ' class="lazyload ', $html );
			} else {
				$html = str_replace( '<img ', '<img class="lazyload" ', $html );
			}
		}
		return $html;
	}

	/**
	 * Filter markup for lazy loading.
	 *
	 * @since 1.8.0
	 *
	 * @param string $content Full html string.
	 * @return string The html markup of the image.
	 */
	public function apply_bulk_lazy_loading( $content ) {
		if ( $this->is_lazy_load_enabled() ) {
			preg_match_all( '/<img\s+[^>]*src="([^"]*)"[^>]*>/isU', $content, $images );
			if ( array_key_exists( 1, $images ) ) {
				foreach ( $images[0] as $key => $image ) {

					$orig  = $image;
					$image = $this->apply_lazy_loading( $image );

					// Replace image.
					$content = str_replace( $orig, $image, $content );
				}
			}
		}
		return $content;
	}

	/**
	 * Disable lazy loading for slider revolution images.
	 *
	 * @since 1.8.1
	 *
	 * @param string $html Full html string.
	 * @param string $content Non stripped original content.
	 * @param object $slider Slider.
	 * @param object $slide Individual slide.
	 * @param string $layer Individual layer.
	 * @return string Altered html markup.
	 */
	public function prevent_rev_lazy_loading( $html, $content, $slider, $slide, $layer ) {
		if ( $this->is_lazy_load_enabled() ) {
			preg_match_all( '/<img\s+[^>]*src="([^"]*)"[^>]*>/isU', $html, $images );
			if ( array_key_exists( 1, $images ) ) {
				foreach ( $images[0] as $key => $image ) {

					$orig  = $image;
					$image = $this->prevent_lazy_loading( $image );

					// Replace image.
					$html = str_replace( $orig, $image, $html );
				}
			}
		}
		return $html;
	}

	/**
	 * Prevent layerslider lazy loading.
	 *
	 * @since 1.8.1
	 *
	 * @param string $html The HTML code that contains the slider markup.
	 * @param array  $slider The slider database record as an associative array.
	 * @param string $id  The ID attribute of the slider element.
	 * @return string Altered html markup.
	 */
	public function prevent_ls_lazy_loading( $html, $slider = false, $id = false ) {
		if ( $this->is_lazy_load_enabled() ) {
			preg_match_all( '/<img\s+[^>]*src="([^"]*)"[^>]*>/isU', $html, $images );
			if ( array_key_exists( 1, $images ) ) {
				foreach ( $images[0] as $key => $image ) {

					$orig  = $image;
					$image = $this->prevent_lazy_loading( $image );

					// Replace image.
					$html = str_replace( $orig, $image, $html );
				}
			}
		}
		return $html;
	}

	/**
	 * Filter markup to prevent lazyloading.
	 *
	 * @since 1.8.0
	 *
	 * @param string $html The post thumbnail HTML.
	 * @return string The html markup of the image.
	 */
	public function prevent_lazy_loading( $html ) {
		if ( $this->is_lazy_load_enabled() && ! strpos( $html, 'disable-lazyload' ) ) {

			if ( strpos( $html, ' class=' ) ) {
				$html = str_replace( ' class="', ' class="disable-lazyload ', $html );
			} else {
				$html = str_replace( '<img ', '<img class="disable-lazyload" ', $html );
			}
		}
		return $html;
	}

	/**
	 * Filter to skip lazy loading.
	 *
	 * @since 3.7
	 *
	 * @param string $thumbnail         The post thumbnail HTML.
	 * @param array  $cart_item         The cart item.
	 * @return string   The html markup of the image.
	 */
	public function skip_lazy_loading( $thumbnail, $cart_item = null ) {
		if ( $this->is_lazy_load_enabled() && false !== strpos( $thumbnail, 'lazyload' ) ) {
			$src = '';

			// Get src from markup.
			preg_match( '@src="([^"]+)"@', $thumbnail, $src );
			if ( array_key_exists( 1, $src ) ) {
				$src = $src[1];
			}

			// If src is a data image, just skip.
			if ( false !== strpos( $src, 'data:image' ) ) {
				return str_replace( 'lazyload', '', $thumbnail );
			}
		}
		return $thumbnail;
	}

	/**
	 * Enqueues lazy loading scripts.
	 *
	 * @access public
	 * @since 1.8.0
	 * @return void
	 */
	public function enqueue_lazy_loading_scripts() {
		if ( $this->is_lazy_load_enabled() || $this->is_avada_iframe_lazy_load_enabled() ) {
			Fusion_Dynamic_JS::enqueue_script( 'lazysizes' );
		}
	}

	/**
	 * For an html text that contains an iframe, apply the lazy-loading attributes needed.
	 *
	 * Will probably not work if html contains multiple iframes.
	 * Lazy-loading will be added only if src, width and height attributes exist on html tag.
	 *
	 * @since 3.6
	 * @param string $iframe_html The iframe html.
	 * @return string
	 */
	public function apply_global_selected_lazy_loading_to_iframe( $iframe_html ) {
		$fusion_settings = awb_get_fusion_settings();
		$lazy_load_type  = $fusion_settings->get( 'lazy_load_iframes' );

		if ( 'none' === $lazy_load_type ) {
			return $iframe_html;
		} elseif ( 'wordpress' === $lazy_load_type ) {
			// When using WP method, an additional check is needed to not apply "loading" attribute 2 times.
			if ( false === strpos( $iframe_html, ' loading=' ) ) {
				return wp_iframe_tag_add_loading_attr( $iframe_html, 'avada_iframe' );
			}
		} elseif ( 'avada' === $lazy_load_type ) {
			return $this->apply_bulk_avada_lazy_loading_iframe( $iframe_html );
		}

		return $iframe_html;
	}

	/**
	 * Adds the avada lazy-loading to all iframes if necessary.
	 *
	 * Lazy-loading will be added only if src, width and height attributes exist on html tag.
	 *
	 * @since 3.6
	 * @param string $content Full html string.
	 * @return string The new markup.
	 */
	public function apply_bulk_avada_lazy_loading_iframe( $content ) {
		if ( $this->is_avada_iframe_lazy_load_enabled() ) {
			preg_match_all( '/<iframe\s+[^>]*src="([^"]*)"[^>]*>/isU', $content, $iframes );
			if ( array_key_exists( 1, $iframes ) ) {
				foreach ( $iframes[0] as $iframe ) {
					$orig   = $iframe;
					$iframe = $this->apply_avada_lazy_loading_iframe( $iframe );

					// Replace image.
					$content = str_replace( $orig, $iframe, $content );
				}
			}
		}
		return $content;
	}

	/**
	 * Apply markup for avada lazy loading to all iframes that are missing.
	 *
	 * Lazy-loading will be added only if src, width and height attributes exist on html tag.
	 *
	 * @since 3.6
	 * @param string       $html              The iframe HTML.
	 * @param string|array $size              Array of width and height values (in that order).
	 * @return string The html markup of the image.
	 */
	public function apply_avada_lazy_loading_iframe( $html, $size = null ) {
		if ( $this->is_avada_iframe_lazy_load_enabled() && false === strpos( $html, 'lazyload' ) && false === strpos( $html, 'rev-slidebg' ) ) {
			$src    = '';
			$width  = 0;
			$height = 0;

			// Iframes with fallback content (see `wp_filter_oembed_result()`) should not be lazy-loaded because they are
			// visually hidden initially.
			if ( false !== strpos( $html, ' data-secret="' ) ) {
				return $html;
			}

			// Get src from markup.
			preg_match( '@src="([^"]+)"@', $html, $src_regex );
			if ( array_key_exists( 1, $src_regex ) ) {
					$src = $src_regex[1];
			} else {
					$src = '';
			}

			// If src is a data image, just skip.
			if ( false !== strpos( $src, 'data:image' ) ) {
				return $html;
			}

			// Get dimensions from markup.
			if ( is_array( $size ) && isset( $size[0], $size[1] ) ) {
				$width  = $size[0];
				$height = $size[1];
			} else {
				preg_match( '/width="(.*?)"/', $html, $width_regex );
				if ( array_key_exists( 1, $width_regex ) ) {
					preg_match( '/height="(.*?)"/', $html, $height_regex );
					if ( array_key_exists( 1, $height_regex ) ) {
						$width  = $width_regex[1];
						$height = $height_regex[1];
					}
				}
			}

			if ( ! $src || ! $width || ! $height ) {
				return $html;
			}

			// Simplified non srcset replacement.
			$html = str_replace( ' src=', ' src="' . self::get_lazy_placeholder( $width, $height ) . '" data-orig-src=', $html );

			if ( strpos( $html, ' class=' ) ) {
				$html = str_replace( ' class="', ' class="lazyload ', $html );
			} else {
				$html = str_replace( '<iframe ', '<iframe class="lazyload" ', $html );
			}
		}

		return $html;
	}

	/**
	 * Determine if we want to lazy-load images or not.
	 *
	 * @access public
	 * @since 1.8.1
	 * @return bool
	 */
	public function is_lazy_load_enabled() {
		return ( self::$is_avada_lazy_load_images && ! Fusion_AMP::is_amp_endpoint() && ! is_admin() && ! is_feed() );
	}

	/**
	 * Determine if avada lazy loading for iframes is enabled.
	 *
	 * @since 3.6
	 * @return bool
	 */
	public function is_avada_iframe_lazy_load_enabled() {
		return ( self::$is_avada_lazy_load_iframes && ! Fusion_AMP::is_amp_endpoint() && ! is_admin() && ! is_feed() );
	}

	/**
	 * Maps the old image size names to the new ones.
	 *
	 * @access public
	 * @since 2.2
	 * @param array $data    An array of the image-sizes data.
	 * @param int   $post_id The post-ID.
	 * @return array
	 */
	public function map_old_image_size_names( $data, $post_id ) {
		$old_sizes = [ 200, 400, 600, 800, 1200 ];

		foreach ( $old_sizes as $size ) {
			if ( isset( $data['sizes'][ $size ] ) ) {
				$data['sizes'][ 'fusion-' . $size ] = $data['sizes'][ $size ];
				unset( $data['sizes'][ $size ] );
			}
		}

		return $data;
	}

	/**
	 * Adds width and height to svg images, if possible.
	 *
	 * @since 3.3.1
	 * @access public
	 * @param array|false  $image {
	 *     Array of image data, or boolean false if no image is available.
	 *     @type string $0 Image source URL.
	 *     @type int    $1 Image width in pixels.
	 *     @type int    $2 Image height in pixels.
	 *     @type bool   $3 Whether the image is a resized image.
	 * }
	 * @param int          $attachment_id Image attachment ID.
	 * @param string|int[] $size          Requested image size. Can be any registered image size name, or
	 *                                    an array of width and height values in pixels (in that order).
	 * @param bool         $icon          Whether the image should be treated as an icon.
	 *
	 * @return array|false                The updated image data.
	 */
	public function wp_get_attachment_image_fix_svg( $image, $attachment_id, $size, $icon ) {
		if ( is_array( $image ) && preg_match( '/\.svg$/i', $image[0] ) && 1 >= $image[1] ) {
			if ( is_array( $size ) ) {
				$image[1] = $size[0];
				$image[2] = $size[1];
			} elseif ( false !== ( $xml = fusion_file_get_contents( $image[0] ) ) ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
				$xml = simplexml_load_string( $xml );

				if ( ! is_bool( $xml ) ) {
					$attr    = $xml->attributes();
					$viewbox = explode( ' ', $attr->viewBox ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

					if ( isset( $attr->width ) && preg_match( '/\d+/', $attr->width, $value ) ) {
						$image[1] = (int) $value[0];
					} elseif ( 4 === count( $viewbox ) ) {
						$image[1] = (int) $viewbox[2];
					}

					if ( isset( $attr->height ) && preg_match( '/\d+/', $attr->height, $value ) ) {
						$image[2] = (int) $value[0];
					} elseif ( 4 === count( $viewbox ) ) {
						$image[2] = (int) $viewbox[3];
					}
				}
			}
		}
		return $image;
	}

	/**
	 * Adds [fusion_imageframe], [fusion_gallery] and [fusion_image_before_after] images to Rank Math SEO XML sitemap.
	 *
	 * @access public
	 * @since 7.0
	 * @param array $images Current post images.
	 * @param int   $post_id The post ID.
	 * @return array The images array with added, extracted element images.
	 */
	public function extract_img_src_for_rank_math( $images, $post_id ) {
		$post    = get_post( $post_id );
		$content = $post->post_content;

		// For images from Image element.
		if ( preg_match_all( '/\[fusion_imageframe(.+?)?\](?:(.+?)?\[\/fusion_imageframe\])?/', $content, $matches ) ) {

			foreach ( $matches[0] as $image_frame ) {
				$src = '';

				if ( false === strpos( $image_frame, '<img' ) && $image_frame ) {

					$pattern = get_shortcode_regex();
					$matches = [];
					preg_match( "/$pattern/s", $image_frame, $matches );
					$src = $matches[5];
				} else {
					preg_match( '/(src=["\'](.*?)["\'])/', $image_frame, $src );

					if ( array_key_exists( '2', $src ) ) {
						$src = $src[2];
					}
				}

				if ( ! in_array( $src, $images, true ) ) {
					$images[] = [
						'src' => $src,
					];
				}
			}
		}

		// For images from newer structure of Gallery element.
		if ( preg_match_all( '/\[fusion_gallery_image(.+?)?\](?:(.+?)?\[\/fusion_gallery_image\])?/', $content, $matches ) ) {
			foreach ( $matches[0] as $item ) {
				$atts = shortcode_parse_atts( $item );
				if ( isset( $atts['image'] ) && ! empty( $atts['image'] ) ) {
					$images[] = [
						'src' => $atts['image'],
					];
				} elseif ( isset( $atts['image_id'] ) && ! empty( $atts['image_id'] ) ) {
					$images[] = [
						'src' => wp_get_attachment_url( $atts['image_id'] ),
					];
				}
			}
		}

		// For images from older structure of Gallery alement.
		if ( preg_match_all( '/\[fusion_gallery(.+?)?\](?:(.+?)?\[\/fusion_gallery\])?/', $content, $matches ) ) {
			foreach ( $matches[0] as $image_gallery ) {
				$atts = shortcode_parse_atts( $image_gallery );
				if ( isset( $atts['image_ids'] ) && ! empty( $atts['image_ids'] ) ) {
					$image_ids = explode( ',', $atts['image_ids'] );
					foreach ( $image_ids as $image_id ) {
						$images[] = [
							'src' => wp_get_attachment_url( $image_id ),
						];
					}
				}
			}
		}

		// For images from Image Before After elemenz.
		if ( preg_match_all( '/\[fusion_image_before_after(.+?)?\](?:(.+?)?\[\/fusion_image_before_after\])?/', $content, $matches ) ) {
			foreach ( $matches[0] as $item ) {
				$atts = shortcode_parse_atts( $item );
				if ( isset( $atts['before_image'] ) && ! empty( $atts['before_image'] ) ) {
					$images[] = [
						'src' => $atts['before_image'],
					];
				}
				if ( isset( $atts['after_image'] ) && ! empty( $atts['after_image'] ) ) {
					$images[] = [
						'src' => $atts['after_image'],
					];
				}
			}
		}

		return $images;
	}

	/**
	 * Allow SVG upload.
	 *
	 * @access public
	 * @since 3.7
	 * @param  array $mimes Mimes allowed.
	 * @return array  Mimes to allow.
	 */
	public function allow_svg( $mimes ) {
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}


	/**
	 * Correct SVG file uploads to make them pass the WP check.
	 *
	 * WP upload validation relies on the fileinfo PHP extension, which causes inconsistencies.
	 * E.g. json file type is application/json but is reported as text/plain.
	 * ref: https://core.trac.wordpress.org/ticket/45633
	 *
	 * @access public
	 * @since 3.7
	 * @param array       $data                      Values for the extension, mime type, and corrected filename.
	 * @param string      $file                      Full path to the file.
	 * @param string      $filename                  The name of the file (may differ from $file due to
	 *                                               $file being in a tmp directory).
	 * @param string[]    $mimes                     Array of mime types keyed by their file extension regex.
	 * @param string|bool $real_mime                 The actual mime type or false if the type cannot be determined.
	 *
	 * @return array
	 */
	public function correct_svg_filetype( $data, $file, $filename, $mimes, $real_mime = false ) {

		// If both ext and type are.
		if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
			return $data;
		}

		$wp_file_type = wp_check_filetype( $filename, $mimes );

		if ( 'svg' === $wp_file_type['ext'] ) {
			$data['ext']  = 'svg';
			$data['type'] = 'image/svg+xml';
		}

		return $data;
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

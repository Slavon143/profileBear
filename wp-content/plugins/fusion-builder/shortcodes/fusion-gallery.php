<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_gallery' ) ) {

	if ( ! class_exists( 'FusionSC_FusionGallery' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_FusionGallery extends Fusion_Element {

			/**
			 * An array of the parent shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $parent_args;

			/**
			 * An array of the child shortcode arguments.
			 *
			 * @access protected
			 * @since 1.8
			 * @var array
			 */
			protected $child_args;

			/**
			 * Number of columns.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $num_of_columns = 1;

			/**
			 * Total number of columns.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $total_num_of_columns = 1;

			/**
			 * The image szie.
			 *
			 * @access private
			 * @since 1.8
			 * @var string
			 */
			private $image_size = '';

			/**
			 * The image data.
			 *
			 * @access private
			 * @since 1.0
			 * @var false|array
			 */
			private $image_data = false;

			/**
			 * The image counter.
			 *
			 * @access private
			 * @since 1.8
			 * @var int
			 */
			private $image_counter = 1;

			/**
			 * The gallery counter.
			 *
			 * @access private
			 * @since 1.5.3
			 * @var int
			 */
			private $gallery_counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_shortcode( 'fusion_gallery', [ $this, 'render' ] );
				add_shortcode( 'fusion_gallery_image', [ $this, 'render_child' ] );

				add_filter( 'fusion_attr_gallery-wrapper', [ $this, 'wrapper_attr' ] );
				add_filter( 'fusion_attr_gallery-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_gallery-shortcode-masonry-wrapper', [ $this, 'masonry_wrapper_attr' ] );
				add_filter( 'fusion_attr_gallery-shortcode-images', [ $this, 'image_attr' ] );
				add_filter( 'fusion_attr_gallery-shortcode-link', [ $this, 'link_attr' ] );
				add_filter( 'fusion_attr_gallery-shortcode-caption', [ $this, 'caption_attr' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_gallery', [ $this, 'query' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param string $context Can be either "parent" or "child".
			 * @return array
			 */
			public static function get_element_defaults( $context ) {
				$fusion_settings = awb_get_fusion_settings();

				$parent = [
					'margin_top'                           => '',
					'margin_right'                         => '',
					'margin_bottom'                        => '',
					'margin_left'                          => '',
					'hide_on_mobile'                       => fusion_builder_default_visibility( 'string' ),
					'class'                                => '',
					'id'                                   => '',
					'image_ids'                            => '',
					'order_by'                             => 'desc',
					'limit'                                => ( '' !== $fusion_settings->get( 'gallery_limit' ) ) ? (int) $fusion_settings->get( 'gallery_limit' ) : -1,
					'pagination_type'                      => $fusion_settings->get( 'gallery_pagination_type' ),
					'load_more_btn_text'                   => $fusion_settings->get( 'gallery_load_more_button_text' ),
					'columns'                              => ( '' !== $fusion_settings->get( 'gallery_columns' ) ) ? (int) $fusion_settings->get( 'gallery_columns' ) : 3,
					'hover_type'                           => ( '' !== $fusion_settings->get( 'gallery_hover_type' ) ) ? strtolower( $fusion_settings->get( 'gallery_hover_type' ) ) : 'none',
					'lightbox_content'                     => ( '' !== $fusion_settings->get( 'gallery_lightbox_content' ) ) ? strtolower( $fusion_settings->get( 'gallery_lightbox_content' ) ) : '',
					'lightbox'                             => $fusion_settings->get( 'status_lightbox' ),
					'column_spacing'                       => ( '' !== $fusion_settings->get( 'gallery_column_spacing' ) ) ? strtolower( $fusion_settings->get( 'gallery_column_spacing' ) ) : '',
					'picture_size'                         => ( '' !== $fusion_settings->get( 'gallery_picture_size' ) ) ? strtolower( $fusion_settings->get( 'gallery_picture_size' ) ) : '',
					'layout'                               => ( '' !== $fusion_settings->get( 'gallery_layout' ) ) ? strtolower( $fusion_settings->get( 'gallery_layout' ) ) : 'grid',
					'gallery_masonry_grid_ratio'           => $fusion_settings->get( 'masonry_grid_ratio' ),
					'gallery_masonry_width_double'         => $fusion_settings->get( 'masonry_width_double' ),
					'bordersize'                           => $fusion_settings->get( 'gallery_border_size' ),
					'bordercolor'                          => $fusion_settings->get( 'gallery_border_color' ),
					'border_radius'                        => (int) $fusion_settings->get( 'gallery_border_radius' ) . 'px',
					'load_more_btn_span'                   => 'no',
					'button_alignment'                     => '',
					'load_more_btn_color'                  => '',
					'load_more_btn_bg_color'               => '',
					'load_more_btn_hover_color'            => '',
					'load_more_btn_hover_bg_color'         => '',

					// Caption params.
					'caption_style'                        => 'off',
					'caption_title_color'                  => '',
					'caption_title_size'                   => '',
					'caption_title_transform'              => '',
					'caption_title_line_height'            => '',
					'caption_title_letter_spacing'         => '',
					'caption_title_tag'                    => '2',
					'fusion_font_family_caption_title_font' => '',
					'fusion_font_variant_caption_title_font' => '',
					'caption_text_color'                   => '',
					'caption_text_size'                    => '',
					'caption_text_line_height'             => '',
					'caption_text_letter_spacing'          => '',
					'fusion_font_family_caption_text_font' => '',
					'fusion_font_variant_caption_text_font' => '',
					'caption_border_color'                 => '',
					'caption_overlay_color'                => $fusion_settings->get( 'primary_color' ),
					'caption_background_color'             => '',
					'caption_margin_top'                   => '',
					'caption_margin_right'                 => '',
					'caption_margin_bottom'                => '',
					'caption_margin_left'                  => '',
					'caption_text_transform'               => '',
					'caption_align'                        => 'none',
					'caption_align_medium'                 => 'none',
					'caption_align_small'                  => 'none',
					// aspect ratio.
					'aspect_ratio'                         => '',
					'custom_aspect_ratio'                  => '',
					'aspect_ratio_position'                => '',

				];

				$child = [
					'hidden'                 => 'no',
					'image'                  => '',
					'image_id'               => '',
					'image_title'            => '',
					'image_caption'          => '',
					'link'                   => '',
					'linktarget'             => '_self',
					'aspect_ratio_position'  => '',
					'masonry_image_position' => '',
				];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				}
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param string $context Can be 'parent' or 'child'.
			 * @return array
			 */
			public static function settings_to_params( $context ) {
				$parent = [
					'gallery_limit'                 => 'limit',
					'gallery_pagination_type'       => 'pagination_type',
					'gallery_load_more_button_text' => 'load_more_btn_text',
					'gallery_columns'               => 'columns',
					'gallery_hover_type'            => 'hover_type',
					'gallery_lightbox_content'      => 'lightbox_content',
					'status_lightbox'               => 'lightbox',
					'gallery_column_spacing'        => 'column_spacing',
					'gallery_picture_size'          => 'picture_size',
					'gallery_layout'                => 'layout',
					'masonry_grid_ratio'            => 'gallery_masonry_grid_ratio',
					'masonry_width_double'          => 'gallery_masonry_width_double',
					'gallery_border_size'           => 'bordersize',
					'gallery_border_color'          => 'bordercolor',
					'gallery_border_radius'         => 'border_radius',
				];

				$child = [];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				}
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				global $fusion_library, $fusion_settings;

				$this->defaults = self::get_element_defaults( 'parent' );
				$defaults       = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_gallery' );
				$defaults       = apply_filters( 'fusion_builder_default_args', $defaults, 'fusion_gallery', $args );
				$content        = apply_filters( 'fusion_shortcode_content', $content, 'fusion_gallery', $args );

				$defaults['bordersize']    = FusionBuilder::validate_shortcode_attr_value( $defaults['bordersize'], 'px' );
				$defaults['border_radius'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_radius'], 'px' );
				$defaults['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_bottom'], 'px' );
				$defaults['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_left'], 'px' );
				$defaults['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_right'], 'px' );
				$defaults['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_top'], 'px' );

				if ( 'round' === $defaults['border_radius'] ) {
					$defaults['border_radius'] = '50%';
				}

				$defaults['limit'] = '0' == $defaults['limit'] ? get_option( 'posts_per_page' ) : $defaults['limit']; // phpcs:ignore WordPress.PHP.StrictComparisons

				extract( $defaults );

				$this->parent_args = $this->args = $defaults;

				$this->num_of_columns = $this->parent_args['columns'];
				$image_ids            = '';
				$counter              = 0;

				$this->parent_args['original_picture_size'] = $this->parent_args['picture_size'];
				if ( 'fixed' === $this->parent_args['picture_size'] && 'masonry' !== $this->parent_args['layout'] ) {
					$this->parent_args['picture_size'] = 'portfolio-two';
					if ( in_array( (int) $this->parent_args['columns'], [ 4, 5, 6 ], true ) ) {
						$this->parent_args['picture_size'] = 'blog-medium';
					}
				} else {
					$this->parent_args['picture_size'] = 'full';
				}

				if ( $this->parent_args['image_ids'] ) {
					$image_ids                  = explode( ',', $this->parent_args['image_ids'] );
					$this->total_num_of_columns = count( $image_ids );
					$this->child_args           = [];
				} else {
					preg_match_all( '/\[fusion_gallery_image (.*?)\]/s', $content, $matches );

					if ( is_array( $matches ) && ! empty( $matches ) ) {
						$this->total_num_of_columns = count( $matches[0] );
					}
				}

				$this->parent_args['column_spacing'] = $fusion_library->sanitize->get_value_with_unit( $this->parent_args['column_spacing'] / 2 );

				$html  = '<div ' . FusionBuilder::attributes( 'gallery-wrapper' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'gallery-shortcode' ) . '>';

				if ( 'masonry' === $this->parent_args['layout'] ) {
					$this->parent_args['grid_sizer'] = true;
					$html                           .= '<div ' . FusionBuilder::attributes( 'gallery-shortcode-images' ) . '></div>';
				}

				if ( $image_ids ) {
					$image_ids = $this->sort_gallery_items( $image_ids );
					foreach ( $image_ids as $image_id ) {
						$this->child_args = 0 < $this->parent_args['limit'] && $counter >= $this->parent_args['limit'] ? [ 'hidden' => 'yes' ] : [];
						$html            .= $this->get_image_markup( $image_id, '', false );

						$counter++;
					}
				} else {
					$content = $this->sort_gallery_items( $content );

					// If limit added, hide next set of items.
					$pattern = get_shortcode_regex( [ 'fusion_gallery_image' ] );

					if ( 0 < $this->parent_args['limit'] && preg_match_all( '/' . $pattern . '/s', $content, $matches ) && array_key_exists( 2, $matches ) && in_array( 'fusion_gallery_image', $matches[2], true ) ) {
						foreach ( $matches[0] as $match ) {
							if ( $counter >= $this->parent_args['limit'] ) {
								$html .= do_shortcode( str_replace( 'fusion_gallery_image', 'fusion_gallery_image hidden="yes"', $match ) );
							} else {
								$html .= do_shortcode( $match );
							}

							$counter++;
						}
					} else {
						// Render all items.
						$html .= do_shortcode( $content );
					}
				}

				$html .= '</div>';

				if ( -1 < $this->parent_args['limit'] && $counter > $this->parent_args['limit'] ) {

					// Load more loading container.
					if ( 'button' === $this->parent_args['pagination_type'] || 'infinite' === $this->parent_args['pagination_type'] ) {
						$html .= self::loading_container_markup( 'awb-gallery-posts-loading-container', __( 'Loading the next set of gallery items...', 'fusion-builder' ) );
					}

					// Infinite scroll container.
					if ( 'infinite' === $this->parent_args['pagination_type'] ) {
						// is-active class to preven loop.
						$html .= '<div class="awb-gallery-infinite-scroll-handle is-active"></div>';
					}

					// The load more button.
					if ( 'button' === $this->args['pagination_type'] ) {
						$html .= '<div class="awb-gallery-buttons">';
						$html .= '<a href="#" class="fusion-button button-flat button-default fusion-button-default-size awb-gallery-load-more-btn">' . $this->parent_args['load_more_btn_text'] . '</a>';
						$html .= '</div>';
					}
				}

				$html .= '</div>';

				$styles = '';

				if ( '' !== $this->parent_args['bordersize'] && 0 !== $this->parent_args['bordersize'] ) {
					$styles .= '.fusion-gallery-' . $this->gallery_counter . ' .fusion-gallery-image {';
					$styles .= "border:{$bordersize} solid {$bordercolor};";
					if ( '0' !== $this->parent_args['border_radius'] && 0 !== $this->parent_args['border_radius'] && '0px' !== $this->parent_args['border_radius'] && 'px' !== $this->parent_args['border_radius'] ) {
						$styles .= "-webkit-border-radius:{$border_radius};-moz-border-radius:{$border_radius};border-radius:{$border_radius};";
						if ( '50%' === $this->parent_args['border_radius'] || 100 < (int) $this->parent_args['border_radius'] ) {
								$styles .= '-webkit-mask-image: -webkit-radial-gradient(circle, white, black);';
						}
					}
					$styles .= '}';
				}

				// caption style.
				$styles .= $this->generate_caption_styles();
				$styles .= $this->generate_aspect_ratio_styles();

				if ( '' !== $styles ) {
					$style_tag = '<style type="text/css">' . $styles . '</style>';
				}

				$this->gallery_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_gallery_parent_content', $style_tag . $html, $args );
			}

			/**
			 * Render the child shortcode.
			 *
			 * @access public
			 * @since 1.8
			 * @param  array  $args   Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string         HTML output.
			 */
			public function render_child( $args, $content = '' ) {
				global $fusion_library;

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'child' ), $args, 'fusion_gallery_image' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_gallery_image', $args );

				extract( $defaults );

				$this->child_args = $defaults;

				$image_url = '' === $this->child_args['image_id'] ? $this->child_args['image'] : '';
				$html      = $this->get_image_markup( $this->child_args['image_id'], $image_url, false );

				return apply_filters( 'fusion_element_gallery_child_content', $html, $args );
			}

			/**
			 * Loading container markup.
			 *
			 * @access public
			 * @since 3.8
			 * @param  string $class The section class.
			 * @param  string $msg   The message to display.
			 * @return string
			 */
			public static function loading_container_markup( $class = '', $msg = false ) {
				$html = '<div class="fusion-loading-container fusion-clearfix ' . esc_attr( $class ) . '">
				<div class="fusion-loading-spinner">
					<div class="fusion-spinner-1"></div>
					<div class="fusion-spinner-2"></div>
					<div class="fusion-spinner-3"></div>
				</div>';

				if ( $msg ) {
					$html .= '<div class="fusion-loading-msg"><em>' . $msg . '</em></div>';
				}

				$html .= '</div>';

				return $html;
			}

			/**
			 * Render the markup of an image.
			 *
			 * @access public
			 * @since 1.8
			 * @param int    $image_id    The image ID.
			 * @param string $image       The image URL.
			 * @param array  $parent_args Parent element arguments.
			 * @param array  $child_args  Child element arguments.
			 * @return string The HTML output of the image.
			 */
			public function get_image_markup( $image_id, $image = '', $parent_args = false, $child_args = false ) {

				global $fusion_library;

				$ajax_request = false;

				if ( false === $parent_args ) {
					$parent_args = $this->parent_args;
				} else {
					$ajax_request = true;
				}
				if ( false === $child_args ) {
					$child_args = $this->child_args;
				} else {
					$ajax_request = true;
				}
				$image_html = $image_url = $image_class = '';

				$image_id = explode( '|', $image_id );
				$image_id = $image_id[0];

				$this->image_data = $fusion_library->get_images_obj()->get_attachment_data_by_helper( $image_id . '|' . $parent_args['picture_size'], $image );

				if ( $this->image_data['url'] ) {
					$image_url = $this->image_data['url'];
				}

				$css_class = 'img-responsive wp-image-' . $image_id;

				if ( isset( $parent_args['original_picture_size'] ) && 'fixed' === $parent_args['original_picture_size'] ) {
					$css_class .= ' fusion-gallery-image-size-fixed';
				}
				// Image aspect ratio.
				$image_inline_style = '';
				if ( ! empty( $parent_args['aspect_ratio'] ) && ! empty( $child_args['aspect_ratio_position'] ) ) {
					$image_inline_style = 'style="object-position:' . $child_args['aspect_ratio_position'] . ';"';
				}
				$image = '<img src="' . $image_url . '" width="' . $this->image_data['width'] . '" height="' . $this->image_data['height'] . '" alt="' . $this->image_data['alt'] . '" title="' . $this->image_data['title'] . '" aria-label="' . $this->image_data['title'] . '" class="' . $css_class . '" ' . $image_inline_style . ' />';

				// For masonry layout, set the correct column size and classes.
				$element_orientation_class = '';
				$responsive_images_columns = $parent_args['columns'];
				if ( 'masonry' === $parent_args['layout'] && $this->image_data ) {

					// Get the correct image orientation class.
					$element_orientation_class                = $fusion_library->get_images_obj()->get_element_orientation_class( $this->image_data['id'], [], $parent_args['gallery_masonry_grid_ratio'], $parent_args['gallery_masonry_width_double'] );
					$element_base_padding                     = $fusion_library->get_images_obj()->get_element_base_padding( $element_orientation_class );
					$this->image_data['orientation_class']    = $element_orientation_class;
					$this->image_data['element_base_padding'] = $element_base_padding;

					// Check if we have a landscape image, then it has to stretch over 2 cols.
					if ( 1 !== $parent_args['columns'] && '1' !== $parent_args['columns'] && false !== strpos( $element_orientation_class, 'fusion-element-landscape' ) ) {
						$responsive_images_columns = (int) $parent_args['columns'] / 2;
					}
				}

				// Responsive images.
				$fusion_library->get_images_obj()->set_grid_image_meta(
					[
						'layout'       => $parent_args['layout'],
						'columns'      => $responsive_images_columns,
						'gutter_width' => $parent_args['column_spacing'],
					]
				);

				if ( 'full' === $parent_args['picture_size'] ) {
					$image = $fusion_library->get_images_obj()->edit_grid_image_src( $image, null, $image_id, 'full' );
				}

				$image = fusion_add_responsive_image_markup( $image );

				$image = $fusion_library->get_images_obj()->apply_lazy_loading( $image, null, $image_id, $parent_args['picture_size'] );

				$fusion_library->get_images_obj()->set_grid_image_meta( [] );

				if ( ! $ajax_request ) {
					if ( 'masonry' === $parent_args['layout'] ) {
						$image = '<div ' . FusionBuilder::attributes( 'gallery-shortcode-masonry-wrapper' ) . '>' . $image . '</div>';
					}

					$image_html .= '<div ' . FusionBuilder::attributes( 'gallery-shortcode-images' ) . '>';

					if ( 'above' === $parent_args['caption_style'] ) {
						$image_html .= $this->render_caption();
					}

					if ( 'liftup' === $parent_args['hover_type'] && in_array( $parent_args['caption_style'], [ 'off', 'above', 'below' ], true ) ) {
						$image_class = ' fusion-gallery-image-liftup';
					}

					// Caption style.
					if ( ! in_array( $parent_args['caption_style'], [ 'off', 'above', 'below' ], true ) ) {
						$image_class .= ' awb-imageframe-style awb-imageframe-style-' . $parent_args['caption_style'];
					}
					$image_html .= '<div class="fusion-gallery-image' . $image_class . '">';

					if ( ! in_array( $parent_args['caption_style'], [ 'off', 'above', 'below' ], true ) ) {
						$image .= $this->render_caption();
					}

					if ( ! empty( $this->child_args['link'] ) || ( 'no' !== $parent_args['lightbox'] && $parent_args['lightbox'] ) ) {
						$image_html .= '<a ' . FusionBuilder::attributes( 'gallery-shortcode-link' ) . '>' . $image . '</a>';
					} else {
						$image_html .= $image;
					}

					$image_html .= '</div>';

					if ( 'below' === $parent_args['caption_style'] ) {
						$image_html .= $this->render_caption();
					}

					$image_html .= '</div>';

					if ( 0 === $this->image_counter % $this->num_of_columns && 'grid' === $parent_args['layout'] ) {
						$image_html .= '<div class="clearfix"></div>';
					}
				} else {
					$image_html .= $image;
				}

				$this->image_counter++;

				return $image_html;
			}

			/**
			 * Builds the attributes array for wrapper.
			 *
			 * @access public
			 * @since 3.8
			 * @return array
			 */
			public function wrapper_attr() {
				$attr = [
					'class' => 'awb-gallery-wrapper  awb-gallery-wrapper-' . $this->gallery_counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->parent_args['hide_on_mobile'], $attr );

				if ( '' !== $this->parent_args['limit'] && 0 < $this->parent_args['limit'] ) {
					$attr['data-limit'] = $this->parent_args['limit'];
					$attr['data-page']  = 1;
				}

				if ( '' !== $this->args['load_more_btn_span'] ) {
					$attr['class'] .= ' button-span-' . $this->args['load_more_btn_span'];
				}

				if ( '' !== $this->parent_args['button_alignment'] ) {
					$attr['style'] .= '--more-btn-alignment:' . $this->parent_args['button_alignment'] . ';';
				}

				if ( '' !== $this->parent_args['load_more_btn_color'] ) {
					$attr['style'] .= '--more-btn-color:' . $this->parent_args['load_more_btn_color'] . ';';
				}

				if ( '' !== $this->parent_args['load_more_btn_bg_color'] ) {
					$attr['style'] .= '--more-btn-bg:' . $this->parent_args['load_more_btn_bg_color'] . ';';
				}

				if ( '' !== $this->parent_args['load_more_btn_hover_color'] ) {
					$attr['style'] .= '--more-btn-hover-color:' . $this->parent_args['load_more_btn_hover_color'] . ';';
				}

				if ( '' !== $this->parent_args['load_more_btn_hover_bg_color'] ) {
					$attr['style'] .= '--more-btn-hover-bg:' . $this->parent_args['load_more_btn_hover_bg_color'] . ';';
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->parent_args );

				return $attr;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = [];

				$attr['class']  = 'fusion-gallery fusion-gallery-container';
				$attr['class'] .= ' fusion-grid-' . $this->num_of_columns;
				$attr['class'] .= ' fusion-columns-total-' . $this->total_num_of_columns;
				$attr['class'] .= ' fusion-gallery-layout-' . $this->parent_args['layout'];
				$attr['class'] .= ' fusion-gallery-' . $this->gallery_counter;

				if ( $this->parent_args['column_spacing'] ) {
					$margin        = ( -1 ) * (int) $this->parent_args['column_spacing'];
					$attr['style'] = 'margin:' . $margin . 'px;';
				}

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

				if ( '' !== $this->args['aspect_ratio'] ) {
					$attr['class'] .= ' has-aspect-ratio';
				}

				return $attr;
			}

			/**
			 * Builds the attributes array for the masonry image wrapper.
			 *
			 * @access public
			 * @since 1.2
			 * @return array
			 */
			public function masonry_wrapper_attr() {
				$fusion_settings = awb_get_fusion_settings();

				$lazy_load = ( 'avada' === $fusion_settings->get( 'lazy_load' ) && ! is_feed() ) ? true : false;

				$attr = [
					'style' => '',
					'class' => 'fusion-masonry-element-container',
				];

				if ( isset( $this->image_data['url'] ) ) {
					$attr['style'] .= $lazy_load ? '' : 'background-image:url(' . $this->image_data['url'] . ');';
				}

				if ( isset( $this->image_data['element_base_padding'] ) ) {

					// If portrait it requires more spacing.
					$column_offset = ' - ' . $this->parent_args['column_spacing'];
					if ( false !== strpos( $this->image_data['orientation_class'], 'fusion-element-portrait' ) ) {
						$column_offset = '';
					}

					$column_spacing = 2 * (int) $this->parent_args['column_spacing'] . 'px';

					// Calculate the correct size of the image wrapper container, based on orientation and column spacing.
					$attr['style'] .= 'padding-top:calc((100% + ' . $column_spacing . ') * ' . $this->image_data['element_base_padding'] . $column_offset . ');';
				}
				if ( isset( $this->child_args['masonry_image_position'] ) ) {
					$attr['style'] .= 'background-position:' . $this->child_args['masonry_image_position'] . ';';
				}
				if ( $lazy_load && isset( $this->image_data['url'] ) ) {
					$attr['data-bg'] = $this->image_data['url'];
					$attr['class']  .= ' lazyload';
				}

				return $attr;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.2
			 * @return array
			 */
			public function image_attr() {
				global $fusion_library;

				$columns = 12 / $this->num_of_columns;

				$attr           = [
					'style' => '',
					'class' => 'fusion-grid-column',
				];
				$attr['class'] .= ' fusion-gallery-column fusion-gallery-column-' . $this->num_of_columns;

				if ( 'liftup' !== $this->parent_args['hover_type'] && in_array( $this->parent_args['caption_style'], [ 'off', 'above', 'below' ], true ) ) {
					$attr['class'] .= ' hover-type-' . $this->parent_args['hover_type'];
				}

				if ( isset( $this->image_data['orientation_class'] ) ) {
					$attr['class'] .= ' ' . $this->image_data['orientation_class'];
				}

				if ( '' !== $this->parent_args['column_spacing'] && ! ( isset( $this->parent_args['grid_sizer'] ) && $this->parent_args['grid_sizer'] ) ) {
					$attr['style'] = 'padding:' . $this->parent_args['column_spacing'] . ';';
				}

				if ( isset( $this->parent_args['grid_sizer'] ) && $this->parent_args['grid_sizer'] ) {
					$this->parent_args['grid_sizer'] = false;
					$attr['class']                  .= ' fusion-grid-sizer';
				}

				if ( ! is_null( $this->child_args ) && 'yes' === $this->child_args['hidden'] ) {
					$attr['class'] .= ' awb-gallery-item-hidden';
				}

				// Caption style.
				if ( in_array( $this->parent_args['caption_style'], [ 'above', 'below' ], true ) ) {
					$attr['class'] .= ' awb-imageframe-style awb-imageframe-style-' . $this->parent_args['caption_style'] . ' awb-imageframe-style-' . $this->gallery_counter;
				}

				return $attr;
			}

			/**
			 * Builds the link attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function link_attr() {

				$attr = [];

				if ( ! empty( $this->child_args['link'] ) ) {
					$attr['href'] = $this->child_args['link'];
				} elseif ( 'fixed' === $this->parent_args['original_picture_size'] && $this->image_data['id'] ) {
					$image_data = fusion_library()->get_images_obj()->get_attachment_data( $this->image_data['id'], 'full' );

					if ( $image_data['url'] ) {
						$attr['href'] = $image_data['url'];
					}
				}

				if ( ! isset( $attr['href'] ) ) {
					$attr['href'] = $this->image_data['url'];
				}

				if ( 'yes' === $this->parent_args['lightbox'] ) {
					if ( $this->image_data ) {

						if ( false !== strpos( $this->parent_args['lightbox_content'], 'title' ) ) {
							$attr['data-title'] = $this->image_data['title'];
							$attr['title']      = $this->image_data['title'];
						}

						if ( false !== strpos( $this->parent_args['lightbox_content'], 'caption' ) ) {
							$attr['data-caption'] = $this->image_data['caption'];
						}
					}
					$attr['rel']      = 'noreferrer';
					$attr['data-rel'] = 'iLightbox[gallery_image_' . $this->gallery_counter . ']';
					$attr['class']    = 'fusion-lightbox';
				}

				if ( ! empty( $this->child_args['linktarget'] ) ) {
					$attr['target'] = $this->child_args['linktarget'];
				}

				return $attr;

			}

			/**
			 * Builds the caption attributes array.
			 *
			 * @access public
			 * @since 3.5
			 * @return array
			 */
			public function caption_attr() {

				$attr = [
					'class' => 'awb-imageframe-caption-container',
					'style' => '',
				];

				if ( ! fusion_element_rendering_is_flex() ) {
					return $attr;
				}

				if ( in_array( $this->args['caption_style'], [ 'above', 'below' ], true ) ) {
					// Responsive alignment.
					foreach ( [ 'large', 'medium', 'small' ] as $size ) {
						$key = 'caption_align' . ( 'large' === $size ? '' : '_' . $size );

						$align = ! empty( $this->args[ $key ] ) && 'none' !== $this->args[ $key ] ? $this->args[ $key ] : false;
						if ( $align ) {
							if ( 'large' === $size ) {
								$attr['style'] .= 'text-align:' . $this->args[ $key ] . ';';
							} else {
								$attr['class'] .= ( 'medium' === $size ? ' md-text-align-' : ' sm-text-align-' ) . $this->args[ $key ];
							}
						}
					}
				}

				return $attr;
			}

			/**
			 * Gets the query data.
			 *
			 * @access public
			 * @since 2.0.0
			 * @return void
			 */
			public function query() {

				$fusion_settings = awb_get_fusion_settings();

				if ( isset( $_POST['children'] ) && isset( $_POST['gallery'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$image_ids        = $_POST['children']; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
					$gallery_settings = wp_unslash( $_POST['gallery'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification

					if ( ! isset( $gallery_settings['layout'] ) || ! $gallery_settings['layout'] ) {
						$gallery_settings['layout'] = ( '' !== $fusion_settings->get( 'gallery_layout' ) ) ? strtolower( $fusion_settings->get( 'gallery_layout' ) ) : 'grid';
					}

					if ( ! isset( $gallery_settings['gallery_masonry_grid_ratio'] ) || ! $gallery_settings['gallery_masonry_grid_ratio'] ) {
						$gallery_settings['gallery_masonry_grid_ratio'] = $fusion_settings->get( 'masonry_grid_ratio' );
					}

					if ( ! isset( $gallery_settings['gallery_masonry_width_double'] ) || ! $gallery_settings['gallery_masonry_width_double'] ) {
						$gallery_settings['gallery_masonry_width_double'] = $fusion_settings->get( 'masonry_width_double' );
					}

					if ( ! isset( $gallery_settings['columns'] ) || ! $gallery_settings['columns'] ) {
						$gallery_settings['columns'] = ( '' !== $fusion_settings->get( 'gallery_columns' ) ) ? (int) $fusion_settings->get( 'gallery_columns' ) : 3;
					}

					if ( ! isset( $gallery_settings['column_spacing'] ) || ! $gallery_settings['column_spacing'] ) {
						$gallery_settings['column_spacing'] = ( '' !== $fusion_settings->get( 'gallery_column_spacing' ) ) ? strtolower( $fusion_settings->get( 'gallery_column_spacing' ) ) : '';
					}

					if ( ! isset( $gallery_settings['picture_size'] ) || ! $gallery_settings['picture_size'] ) {
						$gallery_settings['picture_size'] = ( '' !== $fusion_settings->get( 'gallery_picture_size' ) ) ? strtolower( $fusion_settings->get( 'gallery_picture_size' ) ) : '';
					}

					if ( 'fixed' === $gallery_settings['picture_size'] && 'masonry' !== $gallery_settings['layout'] ) {
						$gallery_settings['picture_size'] = 'portfolio-two';
						if ( in_array( $gallery_settings['columns'], [ 4, 5, 6 ] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
							$gallery_settings['picture_size'] = 'blog-medium';
						}
					} else {
						$gallery_settings['picture_size'] = 'full';
					}

					$image_url            = '';
					$regular_images_found = false;
					$element_base_padding = '';
					$image_size           = 'full';

					if ( is_array( $image_ids ) && ! empty( $image_ids ) ) {
						foreach ( $image_ids as $index => $image ) {

							$image_id    = isset( $image['image_id'] ) ? $image['image_id'] : 0;
							$image_index = $image_id;
							$image_id    = explode( '|', $image_id );
							$image_id    = $image_id[0];

							$image_data = fusion_library()->get_images_obj()->get_attachment_data( $image_id, $image_size );
							$image_url  = '';
							$pic_link   = '';

							if ( $image_data['url'] ) {
								$image_url = $pic_link = $image_data['url'];
							}

							// For masonry layout, set the correct column size and classes.
							$element_orientation_class = '';
							$responsive_images_columns = $gallery_settings['columns'];

							// Get the correct image orientation class.
							$element_orientation_class          = fusion_library()->get_images_obj()->get_element_orientation_class( $image_data['id'], [], $gallery_settings['gallery_masonry_grid_ratio'], $gallery_settings['gallery_masonry_width_double'] );
							$element_base_padding               = fusion_library()->get_images_obj()->get_element_base_padding( $element_orientation_class );
							$image_data['orientation_class']    = $element_orientation_class;
							$image_data['element_base_padding'] = $element_base_padding;

							$image_data['specific_element_orientation_class'] = ( '' !== get_post_meta( $image_id, 'fusion_masonry_element_layout', true ) ) ? true : false;

							// Check if we have a landscape image, then it has to stretch over 2 cols.
							if ( 1 !== $gallery_settings['columns'] && '1' !== $gallery_settings['columns'] && false !== strpos( $element_orientation_class, 'fusion-element-landscape' ) ) {
								$responsive_images_columns = (int) $gallery_settings['columns'] / 2;
							} else {
								$regular_images_found = true;
							}

							// Responsive images.
							fusion_library()->get_images_obj()->set_grid_image_meta(
								[
									'layout'       => $gallery_settings['layout'],
									'columns'      => $responsive_images_columns,
									'gutter_width' => $gallery_settings['column_spacing'],
								]
							);

							fusion_library()->get_images_obj()->set_grid_image_meta( [] );

							$url = $image_data['url'];

							$image_html = $this->get_image_markup( $image_id, '', $gallery_settings, $image );

							$return_data['images'][ $image_index ] = [
								'image_html'           => $image_html,
								'image_data'           => $image_data,
								'url'                  => $url,
								'element_orientation_class' => $element_orientation_class,
								'element_base_padding' => $element_base_padding,
								'pic_link'             => $pic_link,
							];
						}

						$return_data['regular_images_found'] = $regular_images_found;
					} else {
						$return_data['placeholder'] = fusion_builder_placeholder( 'gallery', 'gallery images' );
					}

					echo wp_json_encode( $return_data );
				}
				wp_die();
			}

			/**
			 * Render the caption.
			 *
			 * @access public
			 * @since 3.5
			 * @return string HTML output.
			 */
			public function render_caption() {
				if ( 'off' === $this->parent_args['caption_style'] ) {
					return '';
				}
				$output  = '<div ' . FusionBuilder::attributes( 'gallery-shortcode-caption' ) . '><div class="awb-imageframe-caption">';
				$title   = '';
				$caption = '';

				if ( $this->image_data ) {
					if ( '' !== $this->image_data['title'] ) {
						$title = $this->image_data['title'];
					}
					if ( '' !== $this->image_data['caption'] ) {
						$caption = $this->image_data['caption'];
					}
				}

				if ( '' !== $this->child_args['image_title'] ) {
					$title = $this->child_args['image_title'];
				}
				if ( '' !== $this->child_args['image_caption'] ) {
					$caption = $this->child_args['image_caption'];
				}

				if ( '' !== $title ) {
					$title_tag = 'div' === $this->parent_args['caption_title_tag'] ? 'div' : 'h' . $this->parent_args['caption_title_tag'];
					$output   .= sprintf( '<%1$s class="awb-imageframe-caption-title">%2$s</%1$s>', $title_tag, $title );
				}
				if ( '' !== $caption ) {
					$output .= sprintf( '<p class="awb-imageframe-caption-text">%1$s</p>', $caption );
				}
				$output .= '</div></div>';
				return $output;
			}

			/**
			 * Generate caption styles.
			 *
			 * @access public
			 * @since 3.5
			 * @return string CSS output.
			 */
			public function generate_caption_styles() {
				if ( 'off' === $this->parent_args['caption_style'] ) {
					return '';
				}
				$this->dynamic_css   = [];
				$this->base_selector = '.fusion-gallery.fusion-gallery-' . $this->gallery_counter;
				if ( in_array( $this->parent_args['caption_style'], [ 'above', 'below' ], true ) ) {
					$this->base_selector = '.awb-imageframe-style.awb-imageframe-style-' . $this->gallery_counter;
				}

				$selectors = [
					$this->base_selector . ' .awb-imageframe-caption-container .awb-imageframe-caption-title',
				];
				// title color.
				if ( ! $this->is_default( 'caption_title_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->parent_args['caption_title_color'], true );
				}
				// title size.
				if ( ! $this->is_default( 'caption_title_size' ) ) {
					$this->add_css_property( $selectors, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->parent_args['caption_title_size'] ), true );
				}
				// title font.
				$font_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->parent_args, 'caption_title_font', 'array' );

				foreach ( $font_styles as $rule => $value ) {
					$this->add_css_property( $selectors, $rule, $value, true );
				}
				// title transform.
				if ( ! $this->is_default( 'caption_title_transform' ) ) {
					$this->add_css_property( $selectors, 'text-transform', $this->parent_args['caption_title_transform'], true );
				}

				// Line height.
				if ( ! $this->is_default( 'caption_title_line_height' ) ) {
					$this->add_css_property( $selectors, 'line-height', fusion_library()->sanitize->get_value_with_unit( $this->parent_args['caption_title_line_height'] ), true );
				}

				// Letter spacing.
				if ( ! $this->is_default( 'caption_title_letter_spacing' ) ) {
					$this->add_css_property( $selectors, 'letter-spacing', $this->parent_args['caption_title_letter_spacing'], true );
				}

				$selectors = [
					$this->base_selector . ' .awb-imageframe-caption-container .awb-imageframe-caption-text',
				];
				// text color.
				if ( ! $this->is_default( 'caption_text_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->parent_args['caption_text_color'] );
				}
				// text size.
				if ( ! $this->is_default( 'caption_text_size' ) ) {
					$this->add_css_property( $selectors, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->parent_args['caption_text_size'] ) );
				}
				// text font.
				$font_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->parent_args, 'caption_text_font', 'array' );

				foreach ( $font_styles as $rule => $value ) {
					$this->add_css_property( $selectors, $rule, $value );
				}
				// text transform.
				if ( ! $this->is_default( 'caption_text_transform' ) ) {
					$this->add_css_property( $selectors, 'text-transform', $this->parent_args['caption_text_transform'] );
				}
				// Line height.
				if ( ! $this->is_default( 'caption_text_line_height' ) ) {
					$this->add_css_property( $selectors, 'line-height', $this->parent_args['caption_text_line_height'] );
				}
				// Letter spacing.
				if ( ! $this->is_default( 'caption_text_letter_spacing' ) ) {
					$this->add_css_property( $selectors, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->parent_args['caption_text_letter_spacing'] ) );
				}

				// Border color.
				if ( 'resa' === $this->parent_args['caption_style'] && ! $this->is_default( 'caption_border_color' ) ) {
					$selectors = [
						$this->base_selector . ' .awb-imageframe-caption-container:before',
					];
					$this->add_css_property( $selectors, 'border-top-color', $this->parent_args['caption_border_color'] );
					$this->add_css_property( $selectors, 'border-bottom-color', $this->parent_args['caption_border_color'] );
					$selectors = [
						$this->base_selector . ' .awb-imageframe-caption-container:after',
					];
					$this->add_css_property( $selectors, 'border-right-color', $this->parent_args['caption_border_color'] );
					$this->add_css_property( $selectors, 'border-left-color', $this->parent_args['caption_border_color'] );
				}

				if ( 'dario' === $this->parent_args['caption_style'] && ! $this->is_default( 'caption_border_color' ) ) {
					$selectors = [
						$this->base_selector . ' .awb-imageframe-caption .awb-imageframe-caption-title:after',
					];
					$this->add_css_property( $selectors, 'background', $this->parent_args['caption_border_color'] );
				}

				// Overlay color.
				if ( in_array( $this->parent_args['caption_style'], [ 'dario', 'resa', 'schantel', 'dany', 'navin' ], true ) ) {
					$selectors = [
						$this->base_selector . ' .awb-imageframe-style',
					];
					$this->add_css_property( $selectors, 'background', $this->parent_args['caption_overlay_color'] );
				}

				// Background color.
				if ( in_array( $this->parent_args['caption_style'], [ 'schantel', 'dany' ], true ) && ! $this->is_default( 'caption_background_color' ) ) {
					$selectors = [
						$this->base_selector . ' .awb-imageframe-caption-container .awb-imageframe-caption-text',
					];
					$this->add_css_property( $selectors, 'background', $this->parent_args['caption_background_color'] );
				}

				// Caption area margin.
				if ( in_array( $this->parent_args['caption_style'], [ 'above', 'below' ], true ) ) {
					$sides     = [ 'top', 'right', 'bottom', 'left' ];
					$selectors = [
						$this->base_selector . ' .awb-imageframe-caption-container',
					];

					foreach ( $sides as $side ) {
						// Element margin.
						$margin_name = 'caption_margin_' . $side;

						if ( ! $this->is_default( $margin_name ) ) {
							$this->add_css_property( $selectors, 'margin-' . $side, fusion_library()->sanitize->get_value_with_unit( $this->parent_args[ $margin_name ] ) );
						}
					}

					if ( ! $this->is_default( 'caption_title' ) ) {
						$selectors = [
							$this->base_selector . ' .awb-imageframe-caption-container .awb-imageframe-caption-text',
						];
						$this->add_css_property( $selectors, 'margin-top', '0.5em' );
					}
				}

				return $this->parse_css();
			}

			/**
			 * Generate aspect ratio styles.
			 *
			 * @access public
			 * @since 3.7
			 * @return string CSS output.
			 */
			public function generate_aspect_ratio_styles() {
				if ( '' === $this->args['aspect_ratio'] ) {
					return '';
				}

				$this->dynamic_css   = [];
				$this->base_selector = '.fusion-gallery.fusion-gallery-' . $this->gallery_counter . ' .fusion-gallery-image img';

				$selectors = [ $this->base_selector ];

				// Calc Ratio.
				if ( 'custom' === $this->args['aspect_ratio'] && '' !== $this->args['custom_aspect_ratio'] ) {
					$this->add_css_property( $selectors, 'aspect-ratio', '100 / ' . $this->args['custom_aspect_ratio'] );
				} else {
					$aspect_ratio = explode( '-', $this->args['aspect_ratio'] );
					$width        = isset( $aspect_ratio[0] ) ? $aspect_ratio[0] : '';
					$height       = isset( $aspect_ratio[1] ) ? $aspect_ratio[1] : '';

					$this->add_css_property( $selectors, 'aspect-ratio', "$width / $height" );
				}

				// Set Image Position.
				if ( '' !== $this->args['aspect_ratio_position'] ) {
					$this->add_css_property( $selectors, 'object-position', $this->args['aspect_ratio_position'] );
				}

				return $this->parse_css();
			}

			/**
			 * Sorts gallery items
			 *
			 * @access public
			 * @param mixed $data Gallery items data.
			 * @since 3.8
			 * @return mixed
			 */
			public function sort_gallery_items( $data ) {
				$is_array = is_array( $data );

				if ( ! $is_array ) {
					$pattern = get_shortcode_regex( [ 'fusion_gallery_image' ] );

					// Get individual items.
					preg_match_all( '/' . $pattern . '/s', $data, $matches );

					$data = array_key_exists( 2, $matches ) && in_array( 'fusion_gallery_image', $matches[2], true ) ? $matches[0] : [];
				}

				if ( is_array( $data ) ) {
					switch ( $this->args['order_by'] ) {
						case 'asc':
							krsort( $data, SORT_NUMERIC );
							break;
						case 'rand':
							shuffle( $data );
							break;
					}
				}

				return $is_array ? $data : implode( '', $data );
			}


			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {
				Fusion_Dynamic_JS::enqueue_script(
					'fusion-gallery',
					FusionBuilder::$js_folder_url . '/general/fusion-gallery.js',
					FusionBuilder::$js_folder_path . '/general/fusion-gallery.js',
					[ 'jquery', 'fusion-animations', 'packery', 'isotope', 'fusion-lightbox', 'images-loaded' ],
					'1',
					true
				);

				Fusion_Dynamic_JS::localize_script(
					'fusion-gallery',
					'fusionGalleryVars',
					[
						'no_more_items_msg' => '<em>' . __( 'All items displayed.', 'fusion-builder' ) . '</em>',
					]
				);
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 3.5
			 * @return array
			 */
			public static function get_element_extras() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'visibility_large'  => $fusion_settings->get( 'visibility_large' ),
					'visibility_medium' => $fusion_settings->get( 'visibility_medium' ),
					'visibility_small'  => $fusion_settings->get( 'visibility_small' ),
				];
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/grid.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/gallery.min.css' );
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1.6
			 * @return array $sections Button settings.
			 */
			public function add_options() {

				return [
					'gallery_shortcode_section' => [
						'label'  => esc_html__( 'Gallery', 'fusion-builder' ),
						'id'     => 'gallery_shortcode_section',
						'type'   => 'accordion',
						'icon'   => 'fusiona-dashboard',
						'fields' => [
							'gallery_limit'            => [
								'label'       => esc_html__( 'Number Of Items', 'fusion-core' ),
								'description' => esc_html__( 'Choose the number of items you want to display. Set to -1 to display all. Set to 0 to use number of items from Settings > Reading.', 'fusion-core' ),
								'id'          => 'gallery_limit',
								'default'     => '-1',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '-1',
									'max'  => '25',
									'step' => '1',
								],
							],
							'gallery_pagination_type'  => [
								'label'           => esc_html__( 'Pagination Type', 'fusion-builder' ),
								'description'     => esc_html__( 'Choose the pagination type.', 'fusion-builder' ),
								'id'              => 'gallery_pagination_type',
								'default'         => 'button',
								'type'            => 'radio-buttonset',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
								'choices'         => [
									'button'   => esc_html__( 'Load More Button', 'fusion-builder' ),
									'infinite' => esc_html__( 'Infinite Scrolling', 'fusion-builder' ),
								],
								'transport'       => 'postMessage',
							],
							'gallery_load_more_button_text' => [
								'label'           => esc_html__( 'Load More Button Text', 'fusion-builder' ),
								'description'     => esc_html__( 'Insert custom load more button text.', 'fusion-builder' ),
								'id'              => 'gallery_load_more_button_text',
								'default'         => esc_html__( 'Load More', 'fusion-builder' ),
								'type'            => 'text',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'gallery_picture_size'     => [
								'type'        => 'radio-buttonset',
								'label'       => esc_attr__( 'Picture Size', 'fusion-builder' ),
								'description' => __( 'Fixed = width and height will be fixed<br/>Auto = width and height will adjust to the image.', 'fusion-builder' ),
								'id'          => 'gallery_picture_size',
								'choices'     => [
									'fixed' => esc_attr__( 'Fixed', 'fusion-builder' ),
									'auto'  => esc_attr__( 'Auto', 'fusion-builder' ),
								],
								'default'     => 'auto',
								'transport'   => 'postMessage',
							],
							'gallery_layout'           => [
								'type'        => 'radio-buttonset',
								'label'       => esc_attr__( 'Gallery Layout', 'fusion-builder' ),
								'description' => __( 'Select the gallery layout type.', 'fusion-builder' ),
								'id'          => 'gallery_layout',
								'choices'     => [
									'grid'    => esc_attr__( 'Grid', 'fusion-builder' ),
									'masonry' => esc_attr__( 'Masonry', 'fusion-builder' ),
								],
								'default'     => 'grid',
								'transport'   => 'postMessage',
							],
							'gallery_columns'          => [
								'type'        => 'slider',
								'label'       => esc_attr__( 'Number of Columns', 'fusion-builder' ),
								'description' => __( 'Set the number of columns per row. <strong>IMPORTANT:</strong> Masonry layout does not work with 1 column.', 'fusion-builder' ),
								'id'          => 'gallery_columns',
								'default'     => '3',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '1',
									'max'  => '6',
									'step' => '1',
								],
							],
							'gallery_column_spacing'   => [
								'label'       => esc_attr__( 'Column Spacing', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the column spacing for gallery images.', 'fusion-builder' ),
								'id'          => 'gallery_column_spacing',
								'default'     => '10',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '300',
									'step' => '1',
								],
							],
							'gallery_hover_type'       => [
								'type'        => 'select',
								'label'       => esc_attr__( 'Hover Type', 'fusion-builder' ),
								'description' => esc_attr__( 'Select the hover effect type.', 'fusion-builder' ),
								'id'          => 'gallery_hover_type',
								'choices'     => [
									''        => esc_attr__( 'Default', 'fusion-builder' ),
									'none'    => esc_attr__( 'None', 'fusion-builder' ),
									'zoomin'  => esc_attr__( 'Zoom In', 'fusion-builder' ),
									'zoomout' => esc_attr__( 'Zoom Out', 'fusion-builder' ),
									'liftup'  => esc_attr__( 'Lift Up', 'fusion-builder' ),
								],
								'default'     => 'none',
								'transport'   => 'postMessage',
							],
							'gallery_lightbox_content' => [
								'type'        => 'radio-buttonset',
								'label'       => esc_attr__( 'Lightbox Content', 'fusion-builder' ),
								'id'          => 'gallery_lightbox_content',
								'default'     => 'none',
								'choices'     => [
									'none'              => esc_attr__( 'None', 'fusion-builder' ),
									'titles'            => esc_attr__( 'Titles', 'fusion-builder' ),
									'captions'          => esc_attr__( 'Captions', 'fusion-builder' ),
									'title_and_caption' => esc_attr__( 'Titles & Captions', 'fusion-builder' ),
								],
								'description' => esc_attr__( 'Choose if titles and captions will display in the lightbox.', 'fusion-builder' ),
								'transport'   => 'postMessage',
							],
							'gallery_border_size'      => [
								'label'       => esc_html__( 'Gallery Image Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the image.', 'fusion-builder' ),
								'id'          => 'gallery_border_size',
								'default'     => '0',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '50',
									'step' => '1',
								],
							],
							'gallery_border_color'     => [
								'label'       => esc_html__( 'Gallery Image Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color of the image.', 'fusion-builder' ),
								'id'          => 'gallery_border_color',
								'default'     => 'var(--awb-color3)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'gallery_border_radius'    => [
								'label'       => esc_html__( 'Gallery Image Border Radius', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border radius of the gallery images.', 'fusion-builder' ),
								'id'          => 'gallery_border_radius',
								'default'     => '0px',
								'type'        => 'dimension',
								'choices'     => [ 'px', '%' ],
								'transport'   => 'postMessage',
							],
						],
					],
				];
			}
		}
	}

	new FusionSC_FusionGallery();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_gallery() {
	$fusion_settings = awb_get_fusion_settings();
	$is_builder      = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

	if ( $is_builder ) {
		$to_link_lightbox_status = '<span class="fusion-panel-shortcut" data-fusion-option="lightbox_status">' . esc_html__( 'Global Options', 'fusion-builder' ) . '</span>';
		$to_link_lightbox_title  = '<span class="fusion-panel-shortcut" data-fusion-option="lightbox_title">' . esc_html__( 'Global Options', 'fusion-builder' ) . '</span>';
	} else {
		$to_link_lightbox_status = '<a href="' . esc_url( $fusion_settings->get_setting_link( 'lightbox_status' ) ) . '" target="_blank">' . esc_html__( 'Global Options', 'fusion-builder' ) . '</a>';
		$to_link_lightbox_title  = '<a href="' . esc_url( $fusion_settings->get_setting_link( 'lightbox_title' ) ) . '" target="_blank">' . esc_html__( 'Global Options', 'fusion-builder' ) . '</a>';
	}

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_FusionGallery',
			[
				'name'            => esc_attr__( 'Gallery', 'fusion-builder' ),
				'shortcode'       => 'fusion_gallery',
				'icon'            => 'fusiona-dashboard',
				'preview'         => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-gallery-preview.php',
				'preview_id'      => 'fusion-builder-block-module-gallery-preview-template',
				'allow_generator' => true,
				'multi'           => 'multi_element_parent',
				'element_child'   => 'fusion_gallery_image',
				'sortable'        => false,
				'help_url'        => 'https://theme-fusion.com/documentation/avada/elements/gallery-element/',
				'subparam_map'    => [
					/* Caption title */
					'fusion_font_family_caption_title_font' => 'caption_title_fonts',
					'fusion_font_variant_caption_title_font' => 'caption_title_fonts',
					'caption_title_size'                   => 'caption_title_fonts',
					'caption_title_transform'              => 'caption_title_fonts',
					'caption_title_line_height'            => 'caption_title_fonts',
					'caption_title_letter_spacing'         => 'caption_title_fonts',
					'caption_title_color'                  => 'caption_title_fonts',

					/* Caption text */
					'fusion_font_family_caption_text_font' => 'caption_text_fonts',
					'fusion_font_variant_caption_text_font' => 'caption_text_fonts',
					'caption_text_size'                    => 'caption_text_fonts',
					'caption_text_transform'               => 'caption_text_fonts',
					'caption_text_line_height'             => 'caption_text_fonts',
					'caption_text_letter_spacing'          => 'caption_text_fonts',
					'caption_text_color'                   => 'caption_text_fonts',
				],
				'params'          => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this gallery.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_gallery_image link="" linktarget="_self" alt="" /]',
					],
					[
						'type'             => 'multiple_upload',
						'heading'          => esc_attr__( 'Bulk Image Upload', 'fusion-builder' ),
						'description'      => __( 'This option allows you to select multiple images at once and they will populate into individual items. It saves time instead of adding one image at a time.', 'fusion-builder' ),
						'param_name'       => 'multiple_upload',
						'child_params'     => [
							'image'    => 'url',
							'image_id' => 'id',
						],
						'remove_from_atts' => true,
						'dynamic_data'     => true,
						'callback'         => [
							'function' => 'fusion_gallery_images',
							'action'   => 'get_fusion_gallery',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'param_name'  => 'order_by',
						'heading'     => esc_attr__( 'Order By', 'fusion-builder' ),
						'description' => __( 'Defines how items should be ordered. <strong>NOTE:</strong> In the case of order by <strong>ASC</strong> and <strong>RAND</strong>, pagination in Avada Live will not work the same as the regular front-end.', 'fusion-builder' ),
						'default'     => 'desc',
						'value'       => [
							'desc' => esc_html__( 'DESC', 'fusion-builder' ),
							'asc'  => esc_html__( 'ASC', 'fusion-builder' ),
							'rand' => esc_html__( 'RAND', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'range',
						'param_name'  => 'limit',
						'heading'     => esc_attr__( 'Number Of Items', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the number of items you want to display. Set to -1 to display all. Set to 0 to use number of items from Settings > Reading.', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'gallery_limit' ),
						'min'         => '-1',
						'max'         => '25',
						'step'        => '1',
					],
					[
						'type'        => 'radio_button_set',
						'param_name'  => 'pagination_type',
						'heading'     => esc_attr__( 'Pagination Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the pagination type.', 'fusion-builder' ),
						'default'     => '',
						'value'       => [
							''         => esc_html__( 'Default', 'fusion-builder' ),
							'button'   => esc_html__( 'Load More Button', 'fusion-builder' ),
							'infinite' => esc_html__( 'Infinite Scrolling', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'limit',
								'value'    => '-1',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'param_name'  => 'load_more_btn_text',
						'heading'     => esc_attr__( 'Load More Button Text', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert custom load more button text.', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'gallery_load_more_button_text' ),
						'dependency'  => [
							[
								'element'  => 'limit',
								'value'    => '-1',
								'operator' => '!=',
							],
							[
								'element'  => 'pagination_type',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_update_gallery_load_more_text',
							'args'     => [
								'selector' => '.awb-gallery-wrapper',
							],
						],
					],
					[
						'type'             => 'radio_button_set',
						'heading'          => esc_attr__( 'Gallery Layout', 'fusion-builder' ),
						'description'      => __( 'Select the gallery layout type.', 'fusion-builder' ),
						'param_name'       => 'layout',
						'value'            => [
							''        => esc_attr__( 'Default', 'fusion-builder' ),
							'grid'    => esc_attr__( 'Grid', 'fusion-builder' ),
							'masonry' => esc_attr__( 'Masonry', 'fusion-builder' ),
						],
						'default'          => '',
						'child_dependency' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Picture Size', 'fusion-builder' ),
						'description' => __( 'Fixed = width and height will be fixed.<br/>Auto = width and height will adjust to the image.<br/>', 'fusion-builder' ),
						'param_name'  => 'picture_size',
						'value'       => [
							''      => esc_attr__( 'Default', 'fusion-builder' ),
							'fixed' => esc_attr__( 'Fixed', 'fusion-builder' ),
							'auto'  => esc_attr__( 'Auto', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '!=',
							],
						],
						'default'     => '',
						'callback'    => [
							'function' => 'fusion_gallery_images',
							'action'   => 'get_fusion_gallery',
							'ajax'     => true,
						],
					],
					[
						'type'             => 'select',
						'heading'          => esc_attr__( 'Images Aspect Ratio', 'fusion-builder' ),
						'description'      => __( 'Select an aspect ratio for the gallery images. <strong>IMPORTANT:</strong> Aspect ratio won\'t work with masonry layout.', 'fusion-builder' ),
						'param_name'       => 'aspect_ratio',
						'value'            => [
							''       => esc_attr__( 'Automatic', 'fusion-builder' ),
							'1-1'    => esc_attr__( '1:1', 'fusion-builder' ),
							'2-1'    => esc_attr__( '2:1', 'fusion-builder' ),
							'2-3'    => esc_attr__( '2:3', 'fusion-builder' ),
							'3-1'    => esc_attr__( '3:1', 'fusion-builder' ),
							'3-2'    => esc_attr__( '3:2', 'fusion-builder' ),
							'4-1'    => esc_attr__( '4:1', 'fusion-builder' ),
							'4-3'    => esc_attr__( '4:3', 'fusion-builder' ),
							'5-4'    => esc_attr__( '5:4', 'fusion-builder' ),
							'16-9'   => esc_attr__( '16:9', 'fusion-builder' ),
							'9-16'   => esc_attr__( '9:16', 'fusion-builder' ),
							'21-9'   => esc_attr__( '21:9', 'fusion-builder' ),
							'9-21'   => esc_attr__( '9:21', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom', 'fusion-builder' ),
						],
						'child_dependency' => true,
						'dependency'       => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Custom Aspect Ratio', 'fusion-builder' ),
						'description' => esc_attr__( 'Set a custom aspect ratio for the image.', 'fusion-builder' ),
						'param_name'  => 'custom_aspect_ratio',
						'min'         => 1,
						'max'         => 500,
						'value'       => 100,
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '!=',
							],
							[
								'element'  => 'aspect_ratio',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'image_focus_point',
						'heading'     => esc_attr__( 'Images Focus Point', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the gallery images focus point by dragging the blue dot. You can customize the focus point for each image.', 'fusion-builder' ),
						'param_name'  => 'aspect_ratio_position',
						'image'       => false,
						'lazy'        => true,
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '!=',
							],
							[
								'element'  => 'aspect_ratio',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
						'description' => __( 'Set the number of columns per row. <strong>IMPORTANT:</strong> Masonry layout does not work with 1 column.', 'fusion-builder' ),
						'param_name'  => 'columns',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'min'         => '1',
						'max'         => '6',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'gallery_columns' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the column spacing for gallery images.', 'fusion-builder' ),
						'param_name'  => 'column_spacing',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'min'         => '0',
						'max'         => '300',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'gallery_column_spacing' ),
						'dependency'  => [
							[
								'element'  => 'columns',
								'value'    => '1',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Masonry Image Aspect Ratio', 'fusion-builder' ),
						'description' => __( 'Set the ratio to decide when an image should become landscape (ratio being width : height) and portrait (ratio being height : width). <strong>IMPORTANT:</strong> The value of "1.0" represents a special case, which will use the auto calculated ratios like in versions prior to Avada 5.5.', 'fusion-builder' ),
						'param_name'  => 'gallery_masonry_grid_ratio',
						'value'       => '',
						'min'         => '1',
						'max'         => '4',
						'step'        => '0.1',
						'default'     => $fusion_settings->get( 'masonry_grid_ratio' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Masonry 2x2 Width', 'fusion-builder' ),
						'description' => __( 'This option decides when a square 1x1 image should become 2x2. This will not apply to images that highly favor landscape or portrait layouts. <strong>IMPORTANT:</strong> There is a “Masonry Image Layout” setting for every image in the WP media library that allows you to manually set how an image will appear (1x1, landscape, portrait or 2x2), regardless of the original ratio. In pixels.', 'fusion-builder' ),
						'param_name'  => 'gallery_masonry_width_double',
						'value'       => '',
						'min'         => '200',
						'max'         => '5120',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'masonry_width_double' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Hover Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the hover effect type. Hover Type will be disabled when caption styles other than Above or Below are chosen.', 'fusion-builder' ),
						'param_name'  => 'hover_type',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => [
							''        => esc_attr__( 'Default', 'fusion-builder' ),
							'none'    => esc_attr__( 'None', 'fusion-builder' ),
							'zoomin'  => esc_attr__( 'Zoom In', 'fusion-builder' ),
							'zoomout' => esc_attr__( 'Zoom Out', 'fusion-builder' ),
							'liftup'  => esc_attr__( 'Lift Up', 'fusion-builder' ),
						],
						'default'     => '',
						'preview'     => [
							'selector' => '.fusion-grid-column,.fusion-gallery-image-liftup',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'caption_style',
								'value'    => 'navin',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dario',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'resa',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'schantel',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dany',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Image Lightbox', 'fusion-builder' ),
						/* translators: Link containing the "Global Options" text. */
						'description' => sprintf( esc_html__( 'Show image in lightbox. Lightbox must be enabled in %s or the image will open up in the same tab by itself.', 'fusion-builder' ), $to_link_lightbox_status ),
						'param_name'  => 'lightbox',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => $fusion_settings->get( 'status_lightbox' ) ? 'yes' : 'no',
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Lightbox Content', 'fusion-builder' ),
						/* translators: Link containing the "Global Options" text. */
						'description' => sprintf( esc_html__( 'Choose if titles and captions will display in the lightbox. Titles and captions can only be displayed when this is globally enabled for the lightbox on the corresponding %s tab.', 'fusion-builder' ), $to_link_lightbox_title ),
						'param_name'  => 'lightbox_content',
						'default'     => '',
						'value'       => [
							''                  => esc_attr__( 'Default', 'fusion-builder' ),
							'none'              => esc_attr__( 'None', 'fusion-builder' ),
							'titles'            => esc_attr__( 'Titles', 'fusion-builder' ),
							'captions'          => esc_attr__( 'Captions', 'fusion-builder' ),
							'title_and_caption' => esc_attr__( 'Titles and Captions', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'lightbox',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gallery Image Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'In pixels.', 'fusion-builder' ),
						'param_name'  => 'bordersize',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'gallery_border_size' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Gallery Image Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the border color. ', 'fusion-builder' ),
						'param_name'  => 'bordercolor',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'gallery_border_color' ),
						'dependency'  => [
							[
								'element'  => 'bordersize',
								'value'    => '0',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Gallery Image Border Radius', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gallery image border radius. In pixels (px), ex: 1px, or "round". ', 'fusion-builder' ),
						'param_name'  => 'border_radius',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Load More Button Span', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the load more button spans the full width/remaining width of row.', 'fusion-builder' ),
						'param_name'  => 'load_more_btn_span',
						'default'     => 'no',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'limit',
								'value'    => '-1',
								'operator' => '!=',
							],
							[
								'element'  => 'pagination_type',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Load More Button Alignment', 'fusion-builder' ),
						'description' => esc_attr__( "Select the button's alignment.", 'fusion-builder' ),
						'param_name'  => 'button_alignment',
						'default'     => 'center',
						'grid_layout' => true,
						'back_icons'  => true,
						'icons'       => [
							'flex-start' => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'     => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'   => '<span class="fusiona-horizontal-flex-end"></span>',
						],
						'value'       => [
							'flex-start' => esc_html__( 'Flex Start', 'fusion-builder' ),
							'center'     => esc_html__( 'Center', 'fusion-builder' ),
							'flex-end'   => esc_html__( 'Flex End', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'limit',
								'value'    => '-1',
								'operator' => '!=',
							],
							[
								'element'  => 'pagination_type',
								'value'    => 'button',
								'operator' => '==',
							],
							[
								'element'  => 'load_more_btn_span',
								'value'    => 'yes',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Load More Button Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Customize Load More button colors.', 'fusion-builder' ),
						'param_name'       => 'load_more_button_styles',
						'default'          => 'regular',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'regular' => esc_html__( 'Regular', 'fusion-builder' ),
							'hover'   => esc_html__( 'Hover', 'fusion-builder' ),
						],
						'icons'            => [
							'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
							'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
						],
						'dependency'       => [
							[
								'element'  => 'limit',
								'value'    => '-1',
								'operator' => '!=',
							],
							[
								'element'  => 'pagination_type',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Load More Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select load more button text color.', 'fusion-builder' ),
						'param_name'  => 'load_more_btn_color',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => 'var(--awb-color8)',
						'subgroup'    => [
							'name' => 'load_more_button_styles',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'limit',
								'value'    => '-1',
								'operator' => '!=',
							],
							[
								'element'  => 'pagination_type',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Load More Button Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select load more button background color.', 'fusion-builder' ),
						'param_name'  => 'load_more_btn_bg_color',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => 'var(--awb-color3)',
						'subgroup'    => [
							'name' => 'load_more_button_styles',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'limit',
								'value'    => '-1',
								'operator' => '!=',
							],
							[
								'element'  => 'pagination_type',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Load More Button Hover Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select load more button hover text color.', 'fusion-builder' ),
						'param_name'  => 'load_more_btn_hover_color',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => 'var(--awb-color1)',
						'subgroup'    => [
							'name' => 'load_more_button_styles',
							'tab'  => 'hover',
						],
						'preview'     => [
							'selector' => '.awb-gallery-buttons a.awb-gallery-load-more-btn',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'limit',
								'value'    => '-1',
								'operator' => '!=',
							],
							[
								'element'  => 'pagination_type',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Load More Button Hover Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select load more button hover background color.', 'fusion-builder' ),
						'param_name'  => 'load_more_btn_hover_bg_color',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => 'var(--awb-color5)',
						'subgroup'    => [
							'name' => 'load_more_button_styles',
							'tab'  => 'hover',
						],
						'preview'     => [
							'selector' => '.awb-gallery-buttons a.awb-gallery-load-more-btn',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'limit',
								'value'    => '-1',
								'operator' => '!=',
							],
							[
								'element'  => 'pagination_type',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'select',
						'heading'          => esc_attr__( 'Caption', 'fusion-builder' ),
						'description'      => esc_attr__( 'Choose the caption style.', 'fusion-builder' ),
						'param_name'       => 'caption_style',
						'value'            => [
							'off'      => esc_attr__( 'Off', 'fusion-builder' ),
							'above'    => esc_attr__( 'Above', 'fusion-builder' ),
							'below'    => esc_attr__( 'Below', 'fusion-builder' ),
							'navin'    => esc_attr__( 'Navin', 'fusion-builder' ),
							'dario'    => esc_attr__( 'Dario', 'fusion-builder' ),
							'resa'     => esc_attr__( 'Resa', 'fusion-builder' ),
							'schantel' => esc_attr__( 'Schantel', 'fusion-builder' ),
							'dany'     => esc_attr__( 'Dany', 'fusion-builder' ),
						],
						'default'          => 'off',
						'group'            => esc_attr__( 'Caption', 'fusion-builder' ),
						'child_dependency' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Image Title Heading Tag', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose HTML tag of the image title, either div or the heading tag, h1-h6.', 'fusion-builder' ),
						'param_name'  => 'caption_title_tag',
						'value'       => [
							'1'   => 'H1',
							'2'   => 'H2',
							'3'   => 'H3',
							'4'   => 'H4',
							'5'   => 'H5',
							'6'   => 'H6',
							'div' => 'DIV',
						],
						'default'     => '2',
						'group'       => esc_attr__( 'Caption', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Image Title Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the image title. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'caption_title_fonts',
						'choices'          => [
							'font-family'    => 'caption_title_font',
							'font-size'      => 'caption_title_size',
							'text-transform' => 'caption_title_transform',
							'line-height'    => 'caption_title_line_height',
							'letter-spacing' => 'caption_title_letter_spacing',
							'color'          => 'caption_title_color',
						],
						'default'          => [
							'font-family' => '',
							'variant'     => '400',
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Caption', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Image Caption Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the caption.', 'fusion-builder' ),
						'param_name'  => 'caption_background_color',
						'value'       => '',
						'group'       => esc_attr__( 'Caption', 'fusion-builder' ),
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'above',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'below',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'navin',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dario',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'resa',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Image Caption Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the image caption. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'caption_text_fonts',
						'choices'          => [
							'font-family'    => 'caption_text_font',
							'font-size'      => 'caption_text_size',
							'text-transform' => 'caption_text_transform',
							'line-height'    => 'caption_text_line_height',
							'letter-spacing' => 'caption_text_letter_spacing',
							'color'          => 'caption_text_color',
						],
						'default'          => [
							'font-family' => '',
							'variant'     => '400',
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Caption', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Caption Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the caption border.', 'fusion-builder' ),
						'param_name'  => 'caption_border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Caption', 'fusion-builder' ),
						'default'     => 'var(--awb-color1)',
						'dependency'  => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'above',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'below',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'navin',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'schantel',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dany',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Image Overlay Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the image overlay.', 'fusion-builder' ),
						'param_name'  => 'caption_overlay_color',
						'value'       => '',
						'group'       => esc_attr__( 'Caption', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'primary_color' ),
						'dependency'  => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'above',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'below',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Caption Align', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how to align the caption.', 'fusion-builder' ),
						'param_name'  => 'caption_align',
						'responsive'  => [
							'state' => 'large',
						],
						'value'       => [
							'none'   => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
						],
						'default'     => 'none',
						'group'       => esc_attr__( 'Caption', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'schantel',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dany',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'navin',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dario',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'resa',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Caption Area Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'caption_margin',
						'value'            => [
							'caption_margin_top'    => '',
							'caption_margin_right'  => '',
							'caption_margin_bottom' => '',
							'caption_margin_left'   => '',
						],
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'group'            => esc_attr__( 'Caption', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'schantel',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dany',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'navin',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dario',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'resa',
								'operator' => '!=',
							],
						],
					],
					'fusion_margin_placeholder' => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
				],
			],
			'parent'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_gallery' );

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_fusion_gallery_image() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_FusionGallery',
			[
				'name'              => esc_attr__( 'Image', 'fusion-builder' ),
				'description'       => esc_attr__( 'Enter some content for this textblock.', 'fusion-builder' ),
				'shortcode'         => 'fusion_gallery_image',
				'hide_from_builder' => true,
				'params'            => [
					[
						'type'         => 'upload',
						'heading'      => esc_attr__( 'Image', 'fusion-builder' ),
						'description'  => esc_attr__( 'Upload an image to display.', 'fusion-builder' ),
						'param_name'   => 'image',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Image ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'image_id',
						'value'       => '',
						'hidden'      => true,
						'callback'    => [
							'function' => 'fusion_gallery_image',
							'action'   => 'get_fusion_gallery',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'image_focus_point',
						'heading'     => esc_attr__( 'Image Focus Point', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the image focus point by dragging the blue dot.', 'fusion-builder' ),
						'param_name'  => 'aspect_ratio_position',
						'image'       => 'image',
						'default'     => 'parent_aspect_ratio_position',
						'callback'    => [
							'function' => 'fusion_gallery_image_ar_position',
						],
						'dependency'  => [
							[
								'element'  => 'parent_layout',
								'value'    => 'masonry',
								'operator' => '!=',
							],
							[
								'element'  => 'parent_aspect_ratio',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'image_focus_point',
						'heading'     => esc_attr__( 'Image Focus Point', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the image focus point by dragging the blue dot.', 'fusion-builder' ),
						'param_name'  => 'masonry_image_position',
						'image'       => 'image',
						'callback'    => [
							'function' => 'fusion_gallery_image_masonry_position',
						],
						'dependency'  => [
							[
								'element'  => 'parent_layout',
								'value'    => 'masonry',
								'operator' => '==',
							],
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Image Title', 'fusion-builder' ),
						'description'  => esc_attr__( 'Enter title text to be displayed on image.', 'fusion-builder' ),
						'param_name'   => 'image_title',
						'value'        => '',
						'dynamic_data' => true,
						'dependency'   => [
							[
								'element'  => 'parent_caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Image Caption', 'fusion-builder' ),
						'description'  => esc_attr__( 'Enter caption text to be displayed on image.', 'fusion-builder' ),
						'param_name'   => 'image_caption',
						'value'        => '',
						'dynamic_data' => true,
						'dependency'   => [
							[
								'element'  => 'parent_caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'link_selector',
						'heading'     => esc_attr__( 'Image Link', 'fusion-builder' ),
						'description' => esc_attr__( 'Add the url the image should link to. If lightbox option is enabled, you can also use this to open a different image in the lightbox.', 'fusion-builder' ),
						'param_name'  => 'link',
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
						'description' => __( '_self = open in same window <br />_blank = open in new window.', 'fusion-builder' ),
						'param_name'  => 'linktarget',
						'value'       => [
							'_self'  => esc_attr__( '_self', 'fusion-builder' ),
							'_blank' => esc_attr__( '_blank', 'fusion-builder' ),
						],
						'default'     => '_self',
					],
				],
				'callback'          => [
					'function' => 'fusion_gallery_image',
					'action'   => 'get_fusion_gallery',
					'ajax'     => true,
				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_fusion_gallery_image' );

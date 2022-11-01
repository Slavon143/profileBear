<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_pagination' ) ) {

	if ( ! class_exists( 'FusionTB_Pagination' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 2.2
		 */
		class FusionTB_Pagination extends Fusion_Component {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 2.2
			 * @var array
			 */
			protected $args;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 2.2
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 2.2
			 */
			public function __construct() {
				parent::__construct( 'fusion_tb_pagination' );
				add_filter( 'fusion_attr_fusion_tb_pagination-shortcode', [ $this, 'attr' ] );
			}


			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 2.2
			 * @return boolean
			 */
			public function should_render() {
				return is_singular();
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'layout'                => 'text',
					'preview_position'      => 'bottom',
					'same_term'             => 'no',
					'taxonomy'              => 'category',
					'inverse_post_order'    => 'no',
					'alignment'             => '',
					'font_size'             => $fusion_settings->get( 'body_typography', 'font-size' ),
					'text_color'            => $fusion_settings->get( 'link_color' ),
					'text_hover_color'      => $fusion_settings->get( 'primary_color' ),
					'border_size'           => 1,
					'border_color'          => $fusion_settings->get( 'sep_color' ),
					'height'                => '36',
					'preview_height'        => '90',
					'preview_wrapper_width' => '500',
					'preview_width'         => '20',
					'z_index'               => '',
					'margin_bottom'         => '',
					'margin_left'           => '',
					'margin_right'          => '',
					'margin_top'            => '',
					'bg_color'              => '',
					'preview_font_size'     => $fusion_settings->get( 'body_typography', 'font-size' ),
					'preview_text_color'    => $fusion_settings->get( 'link_color' ),
					'box_shadow'            => 'no',
					'box_shadow_blur'       => '',
					'box_shadow_color'      => '',
					'box_shadow_horizontal' => '',
					'box_shadow_spread'     => '',
					'box_shadow_vertical'   => '',
					'hide_on_mobile'        => fusion_builder_default_visibility( 'string' ),
					'class'                 => '',
					'id'                    => '',
					'animation_type'        => '',
					'animation_direction'   => 'down',
					'animation_speed'       => '0.1',
					'animation_offset'      => $fusion_settings->get( 'animation_offset' ),
				];
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'sep_color'                  => 'border_color',
					'link_color'                 => 'text_color',
					'primary_color'              => 'text_hover_color',
					'body_typography[font-size]' => 'font_size',
					'body_typography[font-size]' => 'preview_font_size',
					'link_color'                 => 'preview_text_color',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 2.2
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_pagination' );

				$defaults['border_size']           = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );
				$defaults['height']                = FusionBuilder::validate_shortcode_attr_value( $defaults['height'], 'px' );
				$defaults['preview_height']        = FusionBuilder::validate_shortcode_attr_value( $defaults['preview_height'], 'px' );
				$defaults['preview_wrapper_width'] = FusionBuilder::validate_shortcode_attr_value( $defaults['preview_wrapper_width'], 'px' );
				$defaults['preview_width']         = FusionBuilder::validate_shortcode_attr_value( $defaults['preview_width'], 'px' );

				$this->args = $defaults;

				$this->emulate_post();

				$html  = $this->get_styles();
				$html .= '<div ' . FusionBuilder::attributes( 'fusion_tb_pagination-shortcode' ) . '>' . $this->get_pagination_content() . '</div>';

				$this->restore_post();

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Builds HTML for Pagination element.
			 *
			 * @static
			 * @access public
			 * @since 2.4
			 * @return string
			 */
			public function get_pagination_content() {
				$content   = '';
				$same_term = ( isset( $this->args['same_term'] ) && 'no' !== $this->args['same_term'] ) ? true : false;
				$term      = ( isset( $this->args['same_term'] ) && '' !== $this->args['taxonomy'] ) ? $this->args['taxonomy'] : 'category';

				// If the user wants to reverse the logic of the post order, then swap posts.
				if ( 'yes' === $this->args['inverse_post_order'] ) {
					$prev_post = get_adjacent_post( $same_term, '', false, $term );
					$next_post = get_adjacent_post( $same_term, '', true, $term );

					add_filter( 'previous_post_link', [ $this, 'swap_rel_attr_in_prev_post_link' ] );
					add_filter( 'next_post_link', [ $this, 'swap_rel_attr_in_next_post_link' ] );
					$prev_post_link = get_next_post_link( '%link', esc_attr__( 'Previous', 'fusion-builder' ), $same_term, '', $term );
					$next_post_link = get_previous_post_link( '%link', esc_attr__( 'Next', 'fusion-builder' ), $same_term, '', $term );
					remove_filter( 'previous_post_link', [ $this, 'swap_rel_attr_in_prev_post_link' ] );
					remove_filter( 'next_post_link', [ $this, 'swap_rel_attr_in_next_post_link' ] );
				} else {
					$prev_post = get_adjacent_post( $same_term, '', true, $term );
					$next_post = get_adjacent_post( $same_term, '', false, $term );

					$prev_post_link = get_previous_post_link( '%link', esc_attr__( 'Previous', 'fusion-builder' ), $same_term, '', $term );
					$next_post_link = get_next_post_link( '%link', esc_attr__( 'Next', 'fusion-builder' ), $same_term, '', $term );
				}

				if ( 'sticky' !== $this->args['layout'] ) {
					$content .= '<div class="fusion-tb-previous">' . $prev_post_link;
					$content .= $this->get_text_preview( $prev_post );
					$content .= '</div>';
					$content .= '<div class="fusion-tb-next">' . $next_post_link;
					$content .= $this->get_text_preview( $next_post );
					$content .= '</div>';
				} elseif ( 'sticky' === $this->args['layout'] ) {
					if ( is_object( $prev_post ) ) {
						$content .= '<div class="fusion-control-navigation prev">';
						$content .= '<a href="' . get_permalink( $prev_post ) . '" rel="prev">';
						$content .= '<span class="fusion-item-title"><i class="awb-icon-angle-left" aria-hidden="true"></i>';
						$content .= '<p>' . $prev_post->post_title . '</p></span>';
						$content .= '<span class="fusion-item-media">' . $this->get_thumbnail( $prev_post, $this->args['preview_height'] ) . '</span></a></div>';
					}

					if ( is_object( $next_post ) ) {
						$content .= '<div class="fusion-control-navigation next">';
						$content .= '<a href="' . get_permalink( $next_post ) . '" rel="next">';
						$content .= '<span class="fusion-item-media">' . $this->get_thumbnail( $next_post, $this->args['preview_height'] ) . '</span>';
						$content .= '<span class="fusion-item-title"><p>' . $next_post->post_title . '</p><i class="awb-icon-angle-right" aria-hidden="true"></i>';
						$content .= '</span></a></div>';
					}
				}

				return $content;
			}

			/**
			 * Renders text preview.
			 *
			 * @access public
			 * @since 3.2
			 * @param  object $post The post object.
			 * @return string HTML output.
			 */
			protected function get_text_preview( $post ) {
				$content = '';

				if ( ! is_object( $post ) || 'preview' !== $this->args['layout'] ) {
					return $content;
				}

				$thumbnail = has_post_thumbnail( $post ) ? $this->get_thumbnail( $post, 90 ) : $this->get_placeholder_image();

				$content .= '<div class="fusion-pagination-preview-wrapper">';
				$content .= '<span class="fusion-item-media">' . $thumbnail . '</span>';
				$content .= '<span class="fusion-item-title">' . $post->post_title . '</span>';
				$content .= '</div>';

				return $content;
			}

			/**
			 * Gets thumbnail.
			 *
			 * @access public
			 * @since 3.2
			 * @param  object $post           The post object.
			 * @param  string $wrapper_height The wrapper height.
			 * @return string HTML output.
			 */
			protected function get_thumbnail( $post, $wrapper_height ) {
				$html       = '';
				$size_class = 'large';

				// return placeholder if no featured image exists.
				if ( ! is_object( $post ) || ! has_post_thumbnail( $post ) ) {
					return $this->get_placeholder_image();
				}

				$attachment_id = get_post_thumbnail_id( $post );
				$image         = wp_get_attachment_image_src( $attachment_id, $size_class );
				if ( $image ) {
					list( $src, $width, $height ) = $image;

					$aspect_ratio = $height / $width;
					$width        = intval( $wrapper_height ) / $aspect_ratio;
					$height       = intval( $wrapper_height );
					$hwstring     = image_hwstring( $width, $height );

					$attr = [
						'src'   => $src,
						'class' => "attachment-$size_class size-$size_class",
						'alt'   => trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
					];

					// Add `loading` attribute.
					if ( wp_lazy_loading_enabled( 'img', 'wp_get_attachment_image' ) ) {
						$attr['loading'] = 'lazy';
					}

					// If the default value of `lazy` for the `loading` attribute is overridden
					// to omit the attribute for this image, ensure it is not included.
					if ( array_key_exists( 'loading', $attr ) && ! $attr['loading'] ) {
						unset( $attr['loading'] );
					}

					// Generate 'srcset' and 'sizes'.
					$image_meta = wp_get_attachment_metadata( $attachment_id );

					if ( is_array( $image_meta ) ) {
						$size_array = [ absint( $width ), absint( $height ) ];
						$srcset     = wp_calculate_image_srcset( $size_array, $src, $image_meta, $attachment_id );
						$sizes      = wp_calculate_image_sizes( $size_array, $src, $image_meta, $attachment_id );

						if ( $srcset && ( $sizes || ! empty( $attr['sizes'] ) ) ) {
							$attr['srcset'] = $srcset;

							if ( empty( $attr['sizes'] ) ) {
								$attr['sizes'] = $sizes;
							}
						}
					}

					$attr = array_map( 'esc_attr', $attr );
					$html = rtrim( "<img $hwstring" );

					foreach ( $attr as $name => $value ) {
						$html .= " $name=" . '"' . $value . '"';
					}

					$html .= ' />';
				}

				return $html;

			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.2
			 * @return string
			 */
			protected function get_styles() {
				$this->base_selector = '.fusion-pagination-tb.fusion-pagination-tb-' . $this->counter;
				$this->dynamic_css   = [];

				$selectors = [
					$this->base_selector . '.single-navigation:not(.layout-sticky)',
				];

				if ( ! $this->is_default( 'border_color' ) ) {
					$this->add_css_property( $selectors, 'border-color', $this->args['border_color'] );
				}

				if ( ! $this->is_default( 'border_size' ) ) {
					$this->add_css_property( $selectors, 'border-width', $this->args['border_size'] );

					if ( 'preview' === $this->args['layout'] ) {
						$selectors = [
							$this->base_selector . '.single-navigation.layout-preview .fusion-pagination-preview-wrapper',
						];

						if ( 'top' === $this->args['preview_position'] ) {
							$this->add_css_property( $selectors, 'margin-bottom', 'calc(' . $this->args['border_size'] . ' + 1px )' );
						} else {
							$this->add_css_property( $selectors, 'margin-top', 'calc(' . $this->args['border_size'] . ' + 1px )' );
						}
					}
				}

				$selectors = [
					'.fusion-fullwidth .fusion-builder-row.fusion-row ' . $this->base_selector . '.single-navigation:not(.layout-sticky) a',
					'.fusion-fullwidth .fusion-builder-row.fusion-row ' . $this->base_selector . '.single-navigation:not(.layout-sticky) a:before',
					'.fusion-fullwidth .fusion-builder-row.fusion-row ' . $this->base_selector . '.single-navigation:not(.layout-sticky) a:after',
				];

				if ( ! $this->is_default( 'text_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['text_color'] );
				}

				$selectors = [
					'.fusion-fullwidth .fusion-builder-row.fusion-row ' . $this->base_selector . '.single-navigation:not(.layout-sticky) a:hover',
					'.fusion-fullwidth .fusion-builder-row.fusion-row ' . $this->base_selector . '.single-navigation:not(.layout-sticky) a:hover::before',
					'.fusion-fullwidth .fusion-builder-row.fusion-row ' . $this->base_selector . '.single-navigation:not(.layout-sticky) a:hover::after',
				];

				if ( ! $this->is_default( 'text_hover_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['text_hover_color'] );
				}

				$selectors = [
					'.fusion-body ' . $this->base_selector . '.layout-sticky .fusion-control-navigation',
					'.fusion-body ' . $this->base_selector . ':not(.layout-sticky).layout-preview .fusion-pagination-preview-wrapper',
				];

				if ( ! $this->is_default( 'bg_color' ) && 'text' !== $this->args['layout'] ) {
					$this->add_css_property( $selectors, 'background', $this->args['bg_color'] );
				}

				$selectors = [
					'.fusion-body ' . $this->base_selector . '.layout-sticky.has-box-shadow .fusion-control-navigation:before',
					'.fusion-body ' . $this->base_selector . ':not(.layout-sticky).layout-preview.has-box-shadow .fusion-pagination-preview-wrapper',
				];

				if ( 'yes' === $this->args['box_shadow'] && '' !== $this->args['box_shadow_color'] && 'text' !== $this->args['layout'] ) {
					$this->add_css_property( $selectors, 'box-shadow', Fusion_Builder_Box_Shadow_Helper::get_box_shadow_styles( $this->args ) );
				}

				$selectors = [
					'.fusion-fullwidth .fusion-builder-row.fusion-row ' . $this->base_selector . '.layout-sticky .fusion-control-navigation a',
					'.fusion-fullwidth .fusion-builder-row.fusion-row ' . $this->base_selector . ':not(.layout-sticky).layout-preview .fusion-pagination-preview-wrapper .fusion-item-title',
				];

				if ( ! $this->is_default( 'preview_text_color' ) && 'text' !== $this->args['layout'] ) {
					$this->add_css_property( $selectors, 'color', $this->args['preview_text_color'] );
				}

				if ( ! $this->is_default( 'preview_font_size' ) && 'text' !== $this->args['layout'] ) {
					$this->add_css_property( $selectors, 'font-size', $this->args['preview_font_size'] );
				}

				$selectors = [
					'.fusion-body ' . $this->base_selector . '.layout-sticky .fusion-control-navigation',
				];

				if ( ! $this->is_default( 'preview_height' ) && 'sticky' === $this->args['layout'] ) {
					$this->add_css_property( $selectors, 'height', $this->args['preview_height'] );
				}

				if ( ! $this->is_default( 'preview_wrapper_width' ) && 'sticky' === $this->args['layout'] ) {
					$this->add_css_property( $selectors, 'min-width', $this->args['preview_wrapper_width'] );
				}

				if ( ! $this->is_default( 'preview_width' ) && 'sticky' === $this->args['layout'] ) {
					$selectors = [
						'.fusion-body ' . $this->base_selector . '.layout-sticky .fusion-control-navigation.next',
					];
					if ( is_rtl() ) {
						$this->add_css_property( $selectors, 'transform', 'translate( calc( max( -' . $this->args['preview_wrapper_width'] . ', -50vw ) + ' . $this->args['preview_width'] . '), -50% )', true );
					} else {
						$this->add_css_property( $selectors, 'transform', 'translate( calc( min( ' . $this->args['preview_wrapper_width'] . ', 50vw ) - ' . $this->args['preview_width'] . '), -50% )' );
					}

					$selectors = [
						'.fusion-body ' . $this->base_selector . '.layout-sticky .fusion-control-navigation.prev',
					];
					if ( is_rtl() ) {
						$this->add_css_property( $selectors, 'transform', 'translate( calc( min( ' . $this->args['preview_wrapper_width'] . ', 50vw ) - ' . $this->args['preview_width'] . '), -50% )', true );
					} else {
						$this->add_css_property( $selectors, 'transform', 'translate( calc( max( -' . $this->args['preview_wrapper_width'] . ', -50vw ) + ' . $this->args['preview_width'] . '), -50% )' );
					}
				}

				$selectors = [
					'.fusion-body ' . $this->base_selector . '.layout-sticky',
				];

				if ( '' !== $this->args['z_index'] && 'sticky' === $this->args['layout'] ) {
					$this->add_css_property( $selectors, 'z-index', intval( $this->args['z_index'] ) );
				}

				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'fusion-pagination-tb fusion-pagination-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( $this->args['alignment'] && 'sticky' !== $this->args['layout'] ) {
					$attr['class'] .= ' align-' . $this->args['alignment'];
				}

				if ( $this->args['layout'] ) {
					$attr['class'] .= ' layout-' . $this->args['layout'];
				}

				if ( $this->args['preview_position'] && 'preview' === $this->args['layout'] ) {
					$attr['class'] .= ' position-' . $this->args['preview_position'];
				}

				if ( 'sticky' !== $this->args['layout'] ) {
					$attr['class'] .= ' single-navigation clearfix ';
				}

				if ( 'yes' === $this->args['box_shadow'] ) {
					$attr['class'] .= ' has-box-shadow';
				}

				if ( $this->args['height'] && 'sticky' !== $this->args['layout'] ) {
					$attr['style'] .= 'min-height:' . $this->args['height'] . ';';
				}

				if ( $this->args['font_size'] ) {
					$attr['style'] .= 'font-size:' . $this->args['font_size'] . ';';
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}

			/**
			 * Replace the "next" rel attribute, with "prev". This function is a
			 * filter.
			 *
			 * @access public
			 * @since 3.5
			 * @param string $html The Html of the adjacent link.
			 * @return string
			 */
			public function swap_rel_attr_in_next_post_link( $html ) {
				$regex_match_next = '/(<a.*?rel=["\'])(next)(["\'].*?>)/';
				$new_html         = preg_replace( $regex_match_next, '$1prev$3', $html );

				if ( empty( $new_html ) ) {
					return $html;
				}

				return $new_html;
			}

			/**
			 * Replace the "prev" rel attribute, with "next". This function is a
			 * filter.
			 *
			 * @access public
			 * @since 3.5
			 * @param string $html The Html of the adjacent link.
			 * @return string
			 */
			public function swap_rel_attr_in_prev_post_link( $html ) {
				$regex_match_prev = '/(<a.*?rel=["\'])(prev)(["\'].*?>)/';
				$new_html         = preg_replace( $regex_match_prev, '$1next$3', $html );

				if ( empty( $new_html ) ) {
					return $html;
				}

				return $new_html;
			}

			/**
			 * Returns placeholder image
			 *
			 * @access public
			 * @since 3.2
			 * @return string
			 */
			public function get_placeholder_image() {
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 560"><g fill-rule="evenodd" clip-rule="evenodd"><path fill="#BBC0C4" d="M378.9 432L630.2 97.4c9.4-12.5 28.3-12.6 37.7 0l221.8 294.2c12.5 16.6.7 40.4-20.1 40.4H378.9z"/><path fill="#CED3D6" d="M135 430.8l153.7-185.9c10-12.1 28.6-12.1 38.7 0L515.8 472H154.3c-21.2 0-32.9-24.8-19.3-41.2z"/><circle fill="#FFF" cx="429" cy="165.4" r="55.5"/></g></svg>';
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/pagination.min.css' );
			}
		}
	}

	new FusionTB_Pagination();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 2.2
 */
function fusion_component_pagination() {
	$fusion_settings = awb_get_fusion_settings();

	$allowed_taxonomies = [
		'category'    => __( 'Post Categories', 'fusion-builder' ),
		'post_tag'    => __( 'Post Tags', 'fusion-builder' ),
		'post_format' => __( 'Post Formats', 'fusion-builder' ),
	];
	$exclude_taxonomies = [ 'fusion_tb_category', 'slide-page', 'product_shipping_class' ];
	$exclude_taxonomies = apply_filters( 'fusion_pagination_excluded_taxonomies', $exclude_taxonomies );
	$args               = [
		'public'   => true,
		'_builtin' => false,
	];

	$taxonomies = get_taxonomies( $args, 'objects', 'and' );

	if ( $taxonomies ) {
		foreach ( $taxonomies  as $taxonomy ) {
			if ( ! in_array( $taxonomy->name, $exclude_taxonomies, true ) ) {
				$allowed_taxonomies[ $taxonomy->name ] = $taxonomy->label;
			}
		}
	}

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Pagination',
			[
				'name'                    => esc_attr__( 'Pagination', 'fusion-builder' ),
				'shortcode'               => 'fusion_tb_pagination',
				'icon'                    => 'fusiona-pagination',
				'component'               => true,
				'templates'               => [ 'content', 'page_title_bar' ],
				'components_per_template' => 2,
				'params'                  => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the layout of the pagination element.', 'fusion-builder' ),
						'param_name'  => 'layout',
						'value'       => [
							'text'    => esc_attr__( 'Text', 'fusion-builder' ),
							'preview' => esc_attr__( 'Text With Preview', 'fusion-builder' ),
							'sticky'  => esc_attr__( 'Sticky Preview', 'fusion-builder' ),
						],
						'default'     => 'text',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Preview Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection for preview position.', 'fusion-builder' ),
						'param_name'  => 'preview_position',
						'default'     => 'bottom',
						'value'       => [
							'bottom' => esc_html__( 'Bottom', 'fusion-builder' ),
							'top'    => esc_html__( 'Top', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'preview',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Height', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the pagination section height. In pixels.', 'fusion-builder' ),
						'param_name'  => 'height',
						'value'       => '36',
						'min'         => '0',
						'max'         => '200',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'sticky',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Same Taxonomy Term', 'fusion-builder' ),
						'description' => esc_attr__( 'Whether next/previous link should be in a same taxonomy term or not.', 'fusion-builder' ),
						'param_name'  => 'same_term',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Taxonomy', 'fusion-builder' ),
						'description' => esc_attr__( 'Select taxonomy to get next/previous link from.', 'fusion-builder' ),
						'param_name'  => 'taxonomy',
						'default'     => 'category',
						'value'       => $allowed_taxonomies,
						'dependency'  => [
							[
								'element'  => 'same_term',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Swap Post Order', 'fusion-builder' ),
						'description' => esc_attr__( 'Whether or not next/previous buttons should invert the post order logic.', 'fusion-builder' ),
						'param_name'  => 'inverse_post_order',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_html__( 'Yes', 'fusion-builder' ),
							'no'  => esc_html__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Text Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection for pagination text alignment.', 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''      => esc_html__( 'Distributed', 'fusion-builder' ),
							'left'  => esc_html__( 'Left', 'fusion-builder' ),
							'right' => esc_html__( 'Right', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'sticky',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Text Font Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the font size for the pagination text. Enter value including CSS unit (px, em, rem), ex: 10px', 'fusion-builder' ),
						'param_name'  => 'font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'sticky',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the pagination section text.', 'fusion-builder' ),
						'param_name'  => 'text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'sticky',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text hover color of the pagination section text.', 'fusion-builder' ),
						'param_name'  => 'text_hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'sticky',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Separator Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border size of the separators. In pixels.', 'fusion-builder' ),
						'param_name'  => 'border_size',
						'value'       => '1',
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'sticky',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Separator Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the separators.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'border_size',
								'value'    => '0',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'sticky',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Z Index', 'fusion-builder' ),
						'description' => esc_attr__( 'Value for preview section\'s z-index CSS property, can be both positive or negative.', 'fusion-builder' ),
						'param_name'  => 'z_index',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'sticky',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Preview Height', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the preview section height. In pixels.', 'fusion-builder' ),
						'param_name'  => 'preview_height',
						'value'       => '90',
						'min'         => '50',
						'max'         => '500',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'sticky',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Preview Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the preview section width. In pixels.', 'fusion-builder' ),
						'param_name'  => 'preview_wrapper_width',
						'value'       => '500',
						'min'         => '200',
						'max'         => '500',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'sticky',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Preview Visible Area Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the preview section width that is displayed before hover. In pixels.', 'fusion-builder' ),
						'param_name'  => 'preview_width',
						'value'       => '20',
						'min'         => '5',
						'max'         => '500',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'sticky',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Preview Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the pagination section.', 'fusion-builder' ),
						'param_name'  => 'bg_color',
						'value'       => '',
						'default'     => '#fff',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Preview Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the preview section text.', 'fusion-builder' ),
						'param_name'  => 'preview_text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Preview Text Font Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the font size for the preview text. Enter value including CSS unit (px, em, rem), ex: 10px', 'fusion-builder' ),
						'param_name'  => 'preview_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'text',
								'operator' => '!=',
							],
						],
					],
					'fusion_box_shadow_no_inner_placeholder' => [
						'dependency' => [
							[
								'element'  => 'box_shadow',
								'value'    => 'yes',
								'operator' => '==',
							],
							[
								'element'  => 'layout',
								'value'    => 'text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'margin',
						'value'            => [
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
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-pagination-tb',
					],
				],
			]
		)
	);
}
add_action( 'wp_loaded', 'fusion_component_pagination' );

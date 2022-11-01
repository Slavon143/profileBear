<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_flickr' ) ) {

	if ( ! class_exists( 'FusionSC_Flickr' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_Flickr extends Fusion_Element {

			/**
			 * The image-frame counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $flickr_counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * An array of wrapper attributes.
			 *
			 * @access protected
			 * @since 3.0
			 * @var array
			 */
			protected $wrapper_attr = [
				'class' => '',
				'style' => '',
			];

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_flickr-shortcode', [ $this, 'attr' ] );

				add_shortcode( 'fusion_flickr', [ $this, 'render' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'api_key'                 => 'c9d2c2fda03a2ff487cb4769dc0781ea',
					'flickr_id'               => '32452368@N05',
					'type'                    => 'photostream',
					'album_id'                => '',
					'count'                   => 10,
					'columns'                 => '',
					'columns_medium'          => '',
					'columns_small'           => '',
					'columns_spacing'         => '',
					'columns_spacing_medium'  => '',
					'columns_spacing_small'   => '',

					// aspect ratio.
					'aspect_ratio'            => '',
					'custom_aspect_ratio'     => '',
					'aspect_ratio_position_x' => '',
					'aspect_ratio_position_y' => '',

					'hover_type'              => '',
					'link_type'               => '',
					'link_target'             => '',

					// margin.
					'margin_top'              => '',
					'margin_right'            => '',
					'margin_bottom'           => '',
					'margin_left'             => '',
					'margin_top_medium'       => '',
					'margin_right_medium'     => '',
					'margin_bottom_medium'    => '',
					'margin_left_medium'      => '',
					'margin_top_small'        => '',
					'margin_right_small'      => '',
					'margin_bottom_small'     => '',
					'margin_left_small'       => '',

					// css.
					'class'                   => '',
					'id'                      => '',
					'id'                      => '',

					// animation.
					'animation_direction'     => 'left',
					'animation_offset'        => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'         => '',
					'animation_type'          => '',

					// visibility.
					'hide_on_mobile'          => fusion_builder_default_visibility( 'string' ),
				];
			}

			/**
			 * Sets the args from the attributes.
			 *
			 * @access public
			 * @since 3.0
			 * @param array $args Element attributes.
			 * @return void
			 */
			public function set_args( $args ) {
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_flickr' );
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode paramters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				$this->set_element_id( $this->flickr_counter );

				$this->set_args( $args );

				$element_styles = '';
				// Aspect ratio.
				$element_styles .= $this->generate_aspect_ratio_styles();

				// Margins.
				$element_styles .= $this->build_margin_styles();

				// Margins.
				$element_styles .= $this->build_columns_styles();

				// Output styles.
				if ( '' !== $element_styles ) {
					$element_styles = '<style>' . $element_styles . '</style>';
				}

				$html = $element_styles . '<div ' . FusionBuilder::attributes( 'flickr-shortcode' ) . '>
					<div class="fusion-loading-container fusion-clearfix">
						<div class="fusion-loading-spinner">
							<div class="fusion-spinner-1"></div>
							<div class="fusion-spinner-2"></div>
							<div class="fusion-spinner-3"></div>
						</div>
					</div>
				</div>';

				$this->flickr_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_flickr_content', $html, $args );

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {
				$fusion_settings = awb_get_fusion_settings();

				$attr = [
					'class' => '',
					'style' => '',
				];

				$attr['id']     = $this->args['id'];
				$attr['class'] .= 'fusion-flickr-element loading flickr-' . $this->flickr_counter . ' ' . $this->args['class'];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( '' !== $this->args['hover_type'] ) {
					$attr['class'] .= ' hover-' . $this->args['hover_type'];
				}

				if ( '' !== $this->args['api_key'] ) {
					$attr['data-api_key'] = $this->args['api_key'];
				}
				if ( '' !== $this->args['flickr_id'] ) {
					$attr['data-id'] = $this->args['flickr_id'];
				}
				if ( '' !== $this->args['type'] ) {
					$attr['data-type'] = $this->args['type'];
				}
				if ( '' !== $this->args['album_id'] ) {
					$attr['data-album_id'] = $this->args['album_id'];
				}
				if ( '' !== $this->args['count'] ) {
					$attr['data-count'] = $this->args['count'];
				}
				if ( 'lightbox' !== $this->args['link_type'] ) {
					$attr['data-lightbox'] = 'true';
				}
				if ( '' !== $this->args['link_type'] ) {
					$attr['data-link_type'] = $this->args['link_type'];
				}
				if ( 'page' === $this->args['link_type'] && '_blank' === $this->args['link_target'] ) {
					$attr['data-link_target'] = $this->args['link_target'];
				}
				if ( '' !== $fusion_settings->get( 'lazy_load' ) ) {
					$attr['data-lazy'] = $fusion_settings->get( 'lazy_load' );
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				return $attr;
			}
			/**
			 * Generate aspect ratio styles.
			 *
			 * @access public
			 * @since 3.6
			 * @return string CSS output.
			 */
			public function generate_aspect_ratio_styles() {
				if ( '' === $this->args['aspect_ratio'] ) {
					return '';
				}

				$this->dynamic_css   = [];
				$this->base_selector = '.fusion-flickr-element.flickr-' . $this->element_id . ' .flickr-image';

				$selectors = [ $this->base_selector ];

				// Calc Ratio.
				if ( 'custom' === $this->args['aspect_ratio'] && '' !== $this->args['custom_aspect_ratio'] ) {
					$this->add_css_property( $selectors, 'padding-top', $this->args['custom_aspect_ratio'] . '%' );
				} else {
					$aspect_ratio = explode( '-', $this->args['aspect_ratio'] );
					$width        = isset( $aspect_ratio[0] ) ? $aspect_ratio[0] : '';
					$height       = isset( $aspect_ratio[1] ) ? $aspect_ratio[1] : '';
					$padding      = '' !== $width && '' !== $height ? ( $height / $width ) * 100 : '';

					$this->add_css_property( $selectors, 'padding-top', $padding . '%' );
				}

				// Set Image Postion.
				$selectors = [ $this->base_selector . ' img' ];

				$x = '' !== $this->args['aspect_ratio_position_x'] ? intval( $this->args['aspect_ratio_position_x'] ) . '%' : '50%';
				$y = '' !== $this->args['aspect_ratio_position_y'] ? intval( $this->args['aspect_ratio_position_y'] ) . '%' : '50%';

				$this->add_css_property( $selectors, 'object-position', $x . ' ' . $y );

				return $this->parse_css();
			}
			/**
			 * Builds margin styles.
			 *
			 * @access public
			 * @since 1.0
			 * @return string
			 */
			public function build_margin_styles() {
				// Responsive Margin.
				$fusion_settings = awb_get_fusion_settings();
				$styles          = '';

				foreach ( [ 'large', 'medium', 'small' ] as $size ) {
					$margin_styles = '';
					foreach ( [ 'top', 'right', 'bottom', 'left' ] as $direction ) {

						$margin_key = 'large' === $size ? 'margin_' . $direction : 'margin_' . $direction . '_' . $size;
						if ( '' !== $this->args[ $margin_key ] ) {
							$margin_styles .= 'margin-' . $direction . ' : ' . fusion_library()->sanitize->get_value_with_unit( $this->args[ $margin_key ] ) . ';';
						}
					}

					if ( '' === $margin_styles ) {
						continue;
					}

					$margin_styles = '.fusion-flickr-element.flickr-' . $this->flickr_counter . '{ ' . $margin_styles . '}';

					// Large styles, no wrapping needed.
					if ( 'large' === $size ) {
						$styles .= $margin_styles;
					} else {
						// Medium and Small size screen styles.
						$styles .= '@media only screen and (max-width:' . $fusion_settings->get( 'visibility_' . $size ) . 'px) {' . $margin_styles . '}';
					}
				}

				return $styles;
			}
			/**
			 * Build Responsive columns.
			 *
			 * @access public
			 * @since 1.0
			 * @return string
			 */
			public function build_columns_styles() {

				$fusion_settings = awb_get_fusion_settings();
				$styles          = '';

				foreach ( [ 'large', 'medium', 'small' ] as $size ) {
					$selector        = '.fusion-flickr-element.flickr-' . $this->flickr_counter;
					$columns         = 'large' === $size ? $this->args['columns'] : $this->args[ 'columns_' . $size ];
					$columns_spacing = 'large' === $size ? $this->args['columns_spacing'] : $this->args[ 'columns_spacing_' . $size ];

					$columns_style = '';

					if ( '' !== $columns ) {
						$columns_style .= 'grid-template-columns: repeat(' . $columns . ', 1fr);';
					}
					if ( '' !== $columns_spacing ) {
						$columns_style .= 'grid-gap:' . fusion_library()->sanitize->get_value_with_unit( $columns_spacing ) . ';';
					}

					if ( '' !== $columns_style ) {
						$columns_style = $selector . '{' . $columns_style . '}';
					}

					// Large styles, no wrapping needed.
					if ( 'large' === $size ) {
						$styles .= $columns_style;
					} else {
						// Medium and Small size screen styles.
						$styles .= '@media only screen and (max-width:' . $fusion_settings->get( 'visibility_' . $size ) . 'px) {' . $columns_style . '}';
					}
				}

				return $styles;
			}
			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/flickr.min.css' );
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {
				Fusion_Dynamic_JS::enqueue_script( 'fusion-flickr' );
			}
		}
	}

	new FusionSC_Flickr();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_flickr_element() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Flickr',
			[
				'name'      => esc_attr__( 'Flickr', 'fusion-builder' ),
				'shortcode' => 'fusion_flickr',
				'icon'      => 'fusiona-flickr-feed',
				'params'    => [
					[
						'type'        => 'textfield',
						'param_name'  => 'api_key',
						'heading'     => esc_attr__( 'API Key', 'fusion-builder' ),
						/* translators: Flickr API Link. */
						'description' => sprintf( __( 'Use default API key or get your own from <a href="%s" target="_blank">Flickr APP Garden</a>.', 'fusion-builder' ), 'http://www.flickr.com/services/apps/create/apply' ),
						'value'       => 'c9d2c2fda03a2ff487cb4769dc0781ea',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Flickr ID', 'fusion-builder' ),
						/* translators: Flickr ID Service. */
						'description' => sprintf( __( 'Enter your Flickr ID to display your own feed. <a href="%s" target="_blank">Get your flickr ID</a>.', 'fusion-builder' ), 'https://www.webfx.com/tools/idgettr/' ),
						'param_name'  => 'flickr_id',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select your flickr feed type.', 'fusion-builder' ),
						'param_name'  => 'type',
						'default'     => 'photostream',
						'value'       => [
							'photostream' => esc_attr__( 'Photostream', 'fusion-builder' ),
							'album'       => esc_attr__( 'Album', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Album ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter your Flickr Album ID. The album ID is the last, numerical part of your album URL. ', 'fusion-builder' ),
						'param_name'  => 'album_id',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'album',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'param_name'  => 'count',
						'heading'     => esc_attr__( 'Number Of Images', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the number of images you want to display.', 'fusion-builder' ),
						'value'       => 10,
						'min'         => 1,
						'max'         => 50,
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Images Aspect Ratio', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the aspect ratio of the images. Images will be cropped.', 'fusion-builder' ),
						'param_name'  => 'aspect_ratio',
						'value'       => [
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
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Custom Aspect Ratio', 'fusion-builder' ),
						'description' => esc_attr__( 'Set a custom aspect ratio for the images.', 'fusion-builder' ),
						'param_name'  => 'custom_aspect_ratio',
						'min'         => 0,
						'max'         => 500,
						'value'       => 100,
						'dependency'  => [
							[
								'element'  => 'aspect_ratio',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Hover Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the hover effect type.', 'fusion-builder' ),
						'param_name'  => 'hover_type',
						'value'       => [
							'none'    => esc_attr__( 'None', 'fusion-builder' ),
							'zoomin'  => esc_attr__( 'Zoom In', 'fusion-builder' ),
							'zoomout' => esc_attr__( 'Zoom Out', 'fusion-builder' ),
							'liftup'  => esc_attr__( 'Lift Up', 'fusion-builder' ),
						],
						'default'     => 'none',
					],
					[
						'type'        => 'radio_button_set',
						'param_name'  => 'link_type',
						'heading'     => esc_attr__( 'Image Link', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose where the image should link to.', 'fusion-builder' ),
						'default'     => '',
						'value'       => [
							''         => esc_html__( 'None', 'fusion-builder' ),
							'lightbox' => esc_html__( 'Lightbox', 'fusion-builder' ),
							'page'     => esc_html__( 'Flickr Page', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'param_name'  => 'link_target',
						'heading'     => esc_attr__( 'Image Link Target', 'fusion-builder' ),
						'description' => __( '_self = open in same window<br />_blank = open in new window.', 'fusion-builder' ),
						'default'     => '',
						'value'       => [
							''       => esc_html__( '_self', 'fusion-builder' ),
							'_blank' => esc_html__( '_blank', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'link_type',
								'value'    => 'page',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'param_name'  => 'columns',
						'heading'     => esc_attr__( 'Number Of Columns', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the number of columns to display.', 'fusion-builder' ),
						'value'       => 4,
						'min'         => 1,
						'max'         => 10,
						'responsive'  => [
							'state' => 'large',
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'param_name'  => 'columns_spacing',
						'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the spacing between columns.', 'fusion-builder' ),
						'value'       => 10,
						'min'         => 0,
						'max'         => 100,
						'responsive'  => [
							'state' => 'large',
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					'fusion_margin_placeholder'            => [
						'param_name' => 'margin',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
						'responsive' => [
							'state' => 'large',
						],
					],
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.fusion-flickr-element',
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					'fusion_sticky_visibility_placeholder' => [],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					],

				],
			]
		)
	);

}
add_action( 'fusion_builder_before_init', 'fusion_flickr_element' );

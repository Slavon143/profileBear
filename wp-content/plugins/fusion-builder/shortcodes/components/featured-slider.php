<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_featured_slider' ) ) {

	if ( ! class_exists( 'FusionTB_Featured_Slider' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 2.2
		 */
		class FusionTB_Featured_Slider extends Fusion_Component {

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
			 * @access protected
			 * @since 2.2
			 * @var int
			 */
			protected $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 2.2
			 */
			public function __construct() {
				parent::__construct( 'fusion_tb_featured_slider' );
				add_filter( 'fusion_attr_fusion_tb_featured_slider-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_featured_slider', [ $this, 'ajax_render' ] );
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
			 * Gets ajax render.
			 *
			 * @access public
			 * @since 2.2
			 * @return void
			 */
			public function ajax_render() {

				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );

					// Build live query response.
					$return_data = [];

					$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $defaults, $this->shortcode_handle );

					// Validate and normalize args.
					$this->validate_args();

					$return_data['output'] = apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', do_shortcode( $this->build_slider() ), $this->args );

					echo wp_json_encode( $return_data );
					wp_die();
				}
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
				return [
					'hide_on_mobile'            => fusion_builder_default_visibility( 'string' ),
					'class'                     => '',
					'id'                        => '',
					'height'                    => '100%',
					'width'                     => '100%',
					'hover_type'                => 'none',
					'lightbox'                  => 'no',
					'margin_bottom'             => '',
					'margin_left'               => '',
					'margin_right'              => '',
					'margin_top'                => '',
					'show_first_featured_image' => '',
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

				$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, $this->shortcode_handle );

				// Validate and normalize args.
				$this->validate_args();

				$slider = $this->build_slider();

				if ( '' !== $slider ) {
					// Start building content.
					$content .= '<div ' . FusionBuilder::attributes( 'fusion_tb_featured_slider-shortcode' ) . '>';

					$content .= $slider;

					$content .= '</div>';
				} else {
					$content = '';
				}

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', do_shortcode( $content ), $args );
			}

			/**
			 * Validates args.
			 *
			 * @since 2.2
			 */
			protected function validate_args() {
				$this->args['width']                     = FusionBuilder::validate_shortcode_attr_value( $this->args['width'], 'px' );
				$this->args['height']                    = FusionBuilder::validate_shortcode_attr_value( $this->args['height'], 'px' );
				$this->args['show_first_featured_image'] = 'yes' === $this->args['show_first_featured_image'];
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
					'class' => 'fusion-featured-slider-tb fusion-featured-slider-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}

			/**
			 * Build slider shortcode.
			 *
			 * @since 2.2
			 * @return string
			 */
			protected function build_slider() {
				$fusion_settings = awb_get_fusion_settings();

				$content = '';

				$this->emulate_post();

				$post_type   = get_post_type( $this->get_target_post() );
				$post_id     = get_the_ID();
				$video_embed = apply_filters( 'privacy_iframe_embed', fusion_get_page_option( 'video', $post_id ) );
				$image_src   = [];
				$images      = [];

				if ( ( ! $post_id || -99 === $post_id ) && fusion_is_preview_frame() ) {
					$this->restore_post();
					return '';
				}

				$content .= '[fusion_slider hover_type="' . $this->args['hover_type'] . '" hide_on_mobile="small-visibility,medium-visibility,large-visibility"';

				if ( '' !== $this->args['width'] ) {
					$content .= ' width="' . $this->args['width'] . '"';
				}

				if ( '' !== $this->args['height'] ) {
					$content .= ' height="' . $this->args['height'] . '"';
				}

				if ( '' !== $this->args['margin_bottom'] ) {
					$content .= ' class="fusion-has-margin"';
				}

				$content .= ']';

				// Add video to slider.
				if ( '' !== $video_embed ) {
					$content .= '[fusion_slide type="video" image_id="" link="" lightbox="no" linktarget="_self"]';
					$content .= $video_embed;
					$content .= '[/fusion_slide]';
				}

				// Check if we should add featured image.
				if ( has_post_thumbnail() && $this->args['show_first_featured_image'] ) {
					$post_thumbnail_id = get_post_thumbnail_id( $post_id );
					$image_src         = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );

					$images[] = [
						'id'  => get_post_thumbnail_id( $post_thumbnail_id ),
						'url' => $image_src[0],
					];
				}

				// Check if we should add Avada featured images.
				$i = 2;
				while ( $i <= $fusion_settings->get( 'posts_slideshow_number' ) ) {
					$attachment_new_id = fusion_get_featured_image_id( 'featured-image-' . $i, $post_type );
					if ( $attachment_new_id ) {
						$image_src = wp_get_attachment_image_src( $attachment_new_id, 'full' );
						$images[]  = [
							'id'  => $attachment_new_id,
							'url' => $image_src[0],
						];
					}
					$i++;
				}

				// If we are on a product, get the product gallery images.
				if ( 'product' === $post_type ) {
					$product = wc_get_product( $post_id );

					if ( ! $product ) {
						return;
					}

					$gallery_images = $product->get_gallery_image_ids();
					foreach ( $gallery_images as $gallery_image_id ) {
						if ( $gallery_image_id ) {
							$image_src = wp_get_attachment_image_src( $gallery_image_id, 'full' );
							$images[]  = [
								'id'  => $gallery_image_id,
								'url' => $image_src[0],
							];
						}
					}
				}

				// Add all images to slider.
				foreach ( $images as $image ) {
					$link     = 'yes' === $this->args['lightbox'] ? $image['url'] : '';
					$content .= '[fusion_slide type="image" link="' . $link . '" linktarget="_self" lightbox="' . $this->args['lightbox'] . '" image_id="' . $image['id'] . '|full"]' . $image['url'] . '[/fusion_slide]';
				}

				$content .= '[/fusion_slider]';

				$this->restore_post();

				if ( 0 === count( $images ) ) {
					$content = '';
				}

				return $content;
			}
		}
	}

	new FusionTB_Featured_Slider();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 2.2
 */
function fusion_component_featured_slider() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Featured_Slider',
			[
				'name'                    => esc_attr__( 'Featured Images Slider', 'fusion-builder' ),
				'shortcode'               => 'fusion_tb_featured_slider',
				'icon'                    => 'fusiona-featured-images',
				'component'               => true,
				'templates'               => [ 'content', 'post_cards', 'page_title_bar' ],
				'components_per_template' => 1,
				'callback'                => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_featured_slider',
					'ajax'     => true,
				],

				// Map subfields to their parent.
				'subparam_map'            => [
					'width'  => 'dimensions',
					'height' => 'dimensions',
				],
				'params'                  => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Show First Featured Image', 'fusion-builder' ),
						'description' => esc_html__( "Turn on if you don't want to display first featured image.", 'fusion-builder' ),
						'param_name'  => 'show_first_featured_image',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_html__( 'Yes', 'fusion-builder' ),
							'no'  => esc_html__( 'No', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_featured_slider',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'select',
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
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_featured_slider',
							'ajax'     => true,
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Image Size Dimensions', 'fusion-builder' ),
						'description'      => esc_attr__( 'Dimensions in percentage (%) or pixels (px).', 'fusion-builder' ),
						'param_name'       => 'dimensions',
						'value'            => [
							'width'  => '100%',
							'height' => '100%',
						],
						'callback'         => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_featured_slider',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Lightbox', 'fusion-builder' ),
						'description' => esc_attr__( 'Show image in lightbox. Lightbox must be enabled in Global Options.', 'fusion-builder' ),
						'param_name'  => 'lightbox',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
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
					'fusion_margin_placeholder' => [
						'param_name' => 'margin',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_featured_slider' );

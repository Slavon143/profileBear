<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_slider' ) ) {

	if ( ! class_exists( 'FusionSC_Media_Slider' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Media_Slider extends Fusion_Element {

			/**
			 * Sliders counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $slider_counter = 1;

			/**
			 * Parent SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $parent_args;

			/**
			 * Child SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $child_args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_slider-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_slider-shortcode-wrapper', [ $this, 'wrapper_attr' ] );
				add_filter( 'fusion_attr_slider-shortcode-slide-link', [ $this, 'slide_link_attr' ] );
				add_filter( 'fusion_attr_slider-shortcode-slide-li', [ $this, 'slide_li_attr' ] );
				add_filter( 'fusion_attr_slider-shortcode-slide-img', [ $this, 'slide_img_attr' ] );
				add_filter( 'fusion_attr_slider-shortcode-slide-img-wrapper', [ $this, 'slide_img_wrapper_attr' ] );

				add_shortcode( 'fusion_slider', [ $this, 'render_parent' ] );
				add_shortcode( 'fusion_slide', [ $this, 'render_child' ] );

			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param string $context Whether we want parent or child.
			 *                        Returns array( parent, child ) if empty.
			 * @return array
			 */
			public static function get_element_defaults( $context = '' ) {
				$fusion_settings = awb_get_fusion_settings();

				$parent = [
					'hide_on_mobile'          => fusion_builder_default_visibility( 'string' ),
					'class'                   => '',
					'id'                      => '',
					'height'                  => '100%',
					'width'                   => '100%',
					'hover_type'              => 'none',
					'alignment'               => '',
					'margin_bottom'           => '',
					'margin_left'             => '',
					'margin_right'            => '',
					'margin_top'              => '',
					'slideshow_autoplay'      => $fusion_settings->get( 'slideshow_autoplay' ),
					'slideshow_smooth_height' => $fusion_settings->get( 'slideshow_smooth_height' ),
					'slideshow_speed'         => $fusion_settings->get( 'slideshow_speed' ),
				];

				$child = [
					'image_id'   => '',
					'lightbox'   => 'no',
					'link'       => '',
					'linktarget' => '_self',
					'type'       => 'image',
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
			 * @since 2.2
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'slideshow_autoplay'      => 'slideshow_autoplay',
					'slideshow_smooth_height' => 'slideshow_smooth_height',
					'slideshow_speed'         => 'slideshow_speed',
				];
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
			public function render_parent( $args, $content = '' ) {
				$flex_wrapper      = '';
				$this->parent_args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args, 'fusion_slider' );

				// Validate and normalize args.
				$this->validate_parent_args();
				$this->args['is_flex'] = fusion_element_rendering_is_flex();
				if ( $this->args['is_flex'] ) {
					$flex_wrapper = '<div ' . FusionBuilder::attributes( 'slider-shortcode-wrapper' ) . '>';
				}

				$html = '<div ' . FusionBuilder::attributes( 'slider-shortcode' ) . '><ul ' . FusionBuilder::attributes( 'slides' ) . '>' . do_shortcode( $content ) . '</ul></div>';

				if ( $flex_wrapper ) {
					$html = $flex_wrapper . $html . '</div>';
				}

				$this->slider_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_slider_parent_content', $html, $args );

			}

			/**
			 * Validates args.
			 *
			 * @since 2.2
			 */
			protected function validate_parent_args() {
				$this->parent_args['width']                   = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['width'], 'px' );
				$this->parent_args['height']                  = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['height'], 'px' );
				$this->parent_args['slideshow_autoplay']      = ( 'yes' === $this->parent_args['slideshow_autoplay'] || '1' === $this->parent_args['slideshow_autoplay'] ) ? true : false;
				$this->parent_args['slideshow_smooth_height'] = ( 'yes' === $this->parent_args['slideshow_smooth_height'] || '1' === $this->parent_args['slideshow_smooth_height'] ) ? true : false;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function wrapper_attr() {
				$attr = fusion_builder_visibility_atts(
					$this->parent_args['hide_on_mobile'],
					[
						'class' => 'fusion-slider-sc',
					]
				);

				$attr['style'] = Fusion_Builder_Margin_Helper::get_margins_style( $this->parent_args );

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

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

				// Initial attrs.
				if ( ! $this->args['is_flex'] ) {
					$attr = fusion_builder_visibility_atts(
						$this->parent_args['hide_on_mobile'],
						[
							'class' => 'fusion-slider-sc fusion-flexslider-loading flexslider',
						]
					);
				} else {
					$attr = [
						'class' => 'fusion-flexslider-loading flexslider',
					];
				}

				if ( '' !== $this->parent_args['alignment'] ) {
					$attr['class'] .= ' fusion-align' . $this->parent_args['alignment'];
				}

				if ( $this->parent_args['hover_type'] ) {
					$attr['class'] .= ' flexslider-hover-type-' . $this->parent_args['hover_type'];
				}

				if ( isset( $this->parent_args['slideshow_autoplay'] ) ) {
					$attr['data-slideshow_autoplay'] = $this->parent_args['slideshow_autoplay'] ? '1' : '0';
				}

				if ( isset( $this->parent_args['slideshow_smooth_height'] ) ) {
					$attr['data-slideshow_smooth_height'] = $this->parent_args['slideshow_smooth_height'] ? '1' : '0';
				}

				if ( isset( $this->parent_args['slideshow_speed'] ) ) {
					$attr['data-slideshow_speed'] = $this->parent_args['slideshow_speed'];
				}

				if ( false !== strpos( $this->parent_args['width'], 'px' ) && false !== strpos( $this->parent_args['height'], 'px' ) ) {
					$attr['class'] .= ' fusion-slider-sc-cover';
				}

				$attr['style'] = 'max-width:' . $this->parent_args['width'] . ';height:' . $this->parent_args['height'] . ';';

				if ( ! $this->args['is_flex'] ) {
					$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->parent_args );

					if ( $this->parent_args['class'] ) {
						$attr['class'] .= ' ' . $this->parent_args['class'];
					}

					if ( $this->parent_args['id'] ) {
						$attr['id'] = $this->parent_args['id'];
					}
				}

				return $attr;

			}

			/**
			 * Render the child shortcode
			 *
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_child( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'child' ), $args, 'fusion_slide' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_slide', $args );

				extract( $defaults );

				$this->child_args = $defaults;

				if ( 'image' === $type ) {

					$this->child_args['src'] = $src = str_replace( '&#215;', 'x', $content );

					$this->child_args['image_data'] = fusion_library()->images->get_attachment_data_by_helper( $this->child_args['image_id'], $src );

					if ( is_array( $this->child_args['image_data'] ) && $this->child_args['image_data']['url'] ) {
						$this->child_args['src'] = $this->child_args['image_data']['url'];
					}
				}

				if ( $link && ! empty( $link ) && 'image' === $type ) {
					$this->child_args['link'] = $link;
				}

				$html = '<li ' . FusionBuilder::attributes( 'slider-shortcode-slide-li' ) . '>';

				if ( $link && ! empty( $link ) ) {
					$html .= '<a ' . FusionBuilder::attributes( 'slider-shortcode-slide-link' ) . '>';
				}

				if ( ! empty( $type ) && 'video' === $type ) {
					$html .= '<div ' . FusionBuilder::attributes( 'full-video' ) . '>' . do_shortcode( $content ) . '</div>';
				} else {
					$image = '<span ' . FusionBuilder::attributes( 'slider-shortcode-slide-img-wrapper' ) . '><img ' . FusionBuilder::attributes( 'slider-shortcode-slide-img' ) . ' /></span>';

					fusion_library()->images->set_grid_image_meta(
						[
							'layout'  => 'large',
							'columns' => '1',
						]
					);

					$image = fusion_add_responsive_image_markup( $image );

					$image_id   = explode( '|', $this->child_args['image_id'] );
					$image_id   = $image_id[0];
					$image_size = isset( $image_id[1] ) ? $image_id[1] : 'full';
					$image      = fusion_library()->images->apply_lazy_loading( $image, null, $image_id, $image_size );

					fusion_library()->images->set_grid_image_meta( [] );

					$html .= $image;
				}

				if ( $link && ! empty( $link ) ) {
					$html .= '</a>';
				}

				$html .= '</li>';

				return apply_filters( 'fusion_element_slider_child_content', $html, $args );

			}

			/**
			 * Builds the slider-link attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function slide_link_attr() {
				$attr = [];

				if ( 'yes' === $this->child_args['lightbox'] ) {
					$attr['class']    = 'lightbox-enabled';
					$attr['data-rel'] = 'iLightbox[slider_carousel_' . $this->slider_counter . ']';
				}

				$attr['title']        = $this->child_args['image_data']['title_attribute'];
				$attr['data-caption'] = $this->child_args['image_data']['caption_attribute'];
				$attr['data-title']   = $this->child_args['image_data']['title_attribute'];
				$attr['aria-label']   = $this->child_args['image_data']['title_attribute'];

				$attr['href']   = $this->child_args['link'];
				$attr['target'] = $this->child_args['linktarget'];

				if ( '_blank' === $attr['target'] ) {
					$attr['rel'] = 'noopener noreferrer';
				}

				return $attr;

			}

			/**
			 * Builds the slider-list-item attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function slide_li_attr() {
				return [
					'class' => ( 'video' === $this->child_args['type'] ) ? 'video' : 'image',
				];
			}

			/**
			 * Builds the slider image attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function slide_img_attr() {
				$attr = [
					'src' => $this->child_args['src'],
				];

				if ( $this->child_args['image_data'] ) {
					$attr['alt']    = $this->child_args['image_data']['alt'];
					$attr['width']  = $this->child_args['image_data']['width'];
					$attr['height'] = $this->child_args['image_data']['height'];
				}

				if ( ! empty( $this->child_args['image_id'] ) ) {
					$image_id      = explode( '|', $this->child_args['image_id'] );
					$attr['class'] = 'wp-image-' . $image_id[0];
				}

				return $attr;
			}

			/**
			 * Builds the image-wrapper attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function slide_img_wrapper_attr() {
				if ( $this->parent_args['hover_type'] ) {
					return [
						'class' => 'fusion-image-hover-element hover-type-' . $this->parent_args['hover_type'],
					];
				}
				return [];
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {
				Fusion_Dynamic_JS::enqueue_script( 'fusion-lightbox' );
				Fusion_Dynamic_JS::enqueue_script( 'fusion-flexslider' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/media-slider.min.css' );
			}
		}
	}

	new FusionSC_Media_Slider();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_media_slider() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Media_Slider',
			[
				'name'          => esc_attr__( 'Media Slider', 'fusion-builder' ),
				'shortcode'     => 'fusion_slider',
				'multi'         => 'multi_element_parent',
				'element_child' => 'fusion_slide',
				'icon'          => 'fusiona-uniF61C',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-media-slider-preview.php',
				'preview_id'    => 'fusion-builder-block-module-media-slider-preview-template',
				'child_ui'      => true,
				'help_url'      => 'https://theme-fusion.com/documentation/avada/elements/slider-element/',
				'sortable'      => false,
				'params'        => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this slider.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_slide type="image" link="" linktarget="_self" lightbox="no" /]',
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
					],
					'fusion_margin_placeholder' => [
						'param_name' => 'margin',
						'group'      => 'fusion_remove_param',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( "Select the slider's alignment.", 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => '',
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
					],
					[
						'heading'     => esc_html__( 'Autoplay', 'fusion-builder' ),
						'description' => esc_html__( 'Turn on to autoplay the slideshows.', 'fusion-builder' ),
						'param_name'  => 'slideshow_autoplay',
						'default'     => '',
						'type'        => 'radio_button_set',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'heading'     => esc_html__( 'Smooth Height', 'fusion-builder' ),
						'description' => esc_html__( 'Turn on to enable smooth height on slideshows when using images with different heights.', 'fusion-builder' ),
						'param_name'  => 'slideshow_smooth_height',
						'default'     => '',
						'type'        => 'radio_button_set',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'heading'     => esc_html__( 'Slideshow Speed', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the speed of slideshows for the slider element. ex: 1000 = 1 second.', 'fusion-builder' ),
						'param_name'  => 'slideshow_speed',
						'value'       => '',
						'default'     => $fusion_settings->get( 'slideshow_speed' ),
						'type'        => 'range',
						'min'         => '100',
						'max'         => '20000',
						'step'        => '50',
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
						'preview'     => [
							'selector' => '.fusion-image-hover-element',
							'type'     => 'class',
							'toggle'   => 'hover',
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
				],
			],
			'parent'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_media_slider' );

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_slide() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Media_Slider',
			[
				'name'              => esc_attr__( 'Slide', 'fusion-builder' ),
				'description'       => esc_attr__( 'Enter some content for this textblock.', 'fusion-builder' ),
				'shortcode'         => 'fusion_slide',
				'option_dependency' => 'type',
				'hide_from_builder' => true,
				'params'            => [
					[
						'type'        => 'textarea',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Content', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '',
						'hidden'      => true,
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Slide Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a video or image slide.', 'fusion-builder' ),
						'param_name'  => 'type',
						'value'       => [
							'image' => esc_attr__( 'Image', 'fusion-builder' ),
							'video' => esc_attr__( 'Video', 'fusion-builder' ),
						],
						'default'     => 'image',
					],
					[
						'type'             => 'upload',
						'heading'          => esc_attr__( 'Image', 'fusion-builder' ),
						'description'      => esc_attr__( 'Upload an image to display.', 'fusion-builder' ),
						'param_name'       => 'image',
						'remove_from_atts' => true,
						'value'            => '',
						'dependency'       => [
							[
								'element'  => 'type',
								'value'    => 'image',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Image ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'image_id',
						'value'       => '',
						'hidden'      => true,
					],
					[
						'type'             => 'textarea',
						'heading'          => esc_attr__( 'Video Element or Video Embed Code', 'fusion-builder' ),
						'description'      => __( 'Click the Youtube or Vimeo Element button below then enter your unique video ID, or copy and paste your video embed code. <p class="insert-slider-video-wrap"><a href="#" class="insert-slider-video" data-type="fusion_youtube">Add YouTube Video</a></p><p class="insert-slider-video-wrap"><a href="#" class="insert-slider-video" data-type="fusion_vimeo">Add Vimeo Video</a></p>.', 'fusion-builder' ),
						'param_name'       => 'video',
						'remove_from_atts' => true,
						'value'            => '',
						'dependency'       => [
							[
								'element'  => 'type',
								'value'    => 'video',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Full Image Link or External Link', 'fusion-builder' ),
						'description' => esc_attr__( 'Add the url of where the image will link to. If lightbox option is enabled, you have to add the full image link to show it in the lightbox.', 'fusion-builder' ),
						'param_name'  => 'link',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'image',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Lightbox', 'fusion-builder' ),
						'description' => esc_attr__( 'Show image in lightbox. Lightbox must be enabled in Global Options or the image will open up in the same tab by itself.', 'fusion-builder' ),
						'param_name'  => 'lightbox',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'image',
								'operator' => '==',
							],
							[
								'element'  => 'link',
								'value'    => '',
								'operator' => '!=',
							],
						],
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
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'image',
								'operator' => '==',
							],
							[
								'element'  => 'link',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'lightbox',
								'value'    => 'no',
								'operator' => '==',
							],
						],
					],
				],
				'tag_name'          => 'li',
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_slide' );

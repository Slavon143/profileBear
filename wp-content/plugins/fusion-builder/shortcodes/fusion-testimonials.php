<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_testimonials' ) ) {

	if ( ! class_exists( 'FusionSC_Testimonials' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Testimonials extends Fusion_Element {

			/**
			 * The testimonials counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $testimonials_counter = 1;

			/**
			 * The testimonials child counter.
			 *
			 * @access private
			 * @since 3.4
			 * @var int
			 */
			private $testimonials_child_counter = 1;

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
				add_filter( 'fusion_attr_testimonials-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-testimonials', [ $this, 'testimonials_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-quote', [ $this, 'quote_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-blockquote', [ $this, 'blockquote_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-review', [ $this, 'review_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-thumbnail', [ $this, 'thumbnail_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-image', [ $this, 'image_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-author', [ $this, 'author_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-pagination', [ $this, 'pagination_attr' ] );

				add_shortcode( 'fusion_testimonials', [ $this, 'render_parent' ] );
				add_shortcode( 'fusion_testimonial', [ $this, 'render_child' ] );

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
					'margin_top'      => '',
					'margin_right'    => '',
					'margin_bottom'   => '',
					'margin_left'     => '',
					'hide_on_mobile'  => fusion_builder_default_visibility( 'string' ),
					'class'           => '',
					'id'              => '',
					'backgroundcolor' => strtolower( $fusion_settings->get( 'testimonial_bg_color' ) ),
					'design'          => 'classic',
					'navigation'      => '',
					'speed'           => $fusion_settings->get( 'testimonials_speed' ),
					'random'          => $fusion_settings->get( 'testimonials_random' ),
					'textcolor'       => strtolower( $fusion_settings->get( 'testimonial_text_color' ) ),
				];

				$child = [
					'avatar'              => 'male',
					'company'             => '',
					'image'               => '',
					'image_id'            => '',
					'image_border_radius' => '',
					'link'                => '',
					'name'                => '',
					'target'              => '_self',
					'gender'              => '',  // Deprecated.
				];

				return fusion_get_context_specific_values( $context, $parent, $child );
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @param string $context Whether we want parent or child.
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_params( $context = '' ) {

				$parent = [
					'testimonial_bg_color'   => 'backgroundcolor',
					'testimonials_random'    => 'random',
					'testimonial_text_color' => 'textcolor',
				];

				return fusion_get_context_specific_values( $context, $parent );
			}

			/**
			 * Render the parent shortcode.
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args     Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_parent( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args, 'fusion_testimonials' );

				if ( 'yes' === $defaults['random'] || '1' === $defaults['random'] ) {
					$defaults['random'] = 1;
				} else {
					$defaults['random'] = 0;
				}

				if ( 'clean' === $defaults['design'] && '' === $defaults['navigation'] ) {
					$defaults['navigation'] = 'yes';
				} elseif ( 'classic' === $defaults['design'] && '' === $defaults['navigation'] ) {
					$defaults['navigation'] = 'no';
				}

				extract( $defaults );

				$this->parent_args = $defaults;

				$this->parent_args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_bottom'], 'px' );
				$this->parent_args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_left'], 'px' );
				$this->parent_args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_right'], 'px' );
				$this->parent_args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_top'], 'px' );

				$styles  = '<style type="text/css">';
				$styles .= '#fusion-testimonials-' . $this->testimonials_counter . ' a{border-color:' . $textcolor . ';}';
				$styles .= '#fusion-testimonials-' . $this->testimonials_counter . ' a:hover, #fusion-testimonials-' . $this->testimonials_counter . ' .activeSlide{background-color: ' . $textcolor . ';}';
				$styles .= '.fusion-testimonials.' . $design . '.fusion-testimonials-' . $this->testimonials_counter . ' .author:after{border-top-color:' . $backgroundcolor . ' !important;}';
				$styles .= '</style>';

				$pagination = '';
				if ( 'yes' === $this->parent_args['navigation'] ) {
					preg_match_all( '/\[fusion_testimonial [^\/]*\/\]|\[fusion_testimonial .*\].*(.|\s|\S)*\/fusion_testimonial\]/U', $content, $single_testimonials );

					if ( isset( $single_testimonials[0] ) ) {
						$pagination = '<div ' . FusionBuilder::attributes( 'testimonials-shortcode-pagination' ) . '>';
						$count      = count( $single_testimonials[0] );

						if ( 1 < $count ) {
							for ( $i = 0; $i < $count; $i++ ) {
								$active_class = 0 === $i ? ' class="activeSlide"' : '';
								$pagination  .= '<a href="#" aria-label="' . esc_attr__( 'Testimonial Pagination', 'fusion-builder' ) . '" ' . $active_class . '></a>';
							}
						}
						$pagination .= '</div>';
					}
				}

				if ( $this->parent_args['random'] ) {
					if ( ! isset( $single_testimonials[0] ) ) {
						preg_match_all( '/\[fusion_testimonial [^\/]*\/\]|\[fusion_testimonial .*\].*(.|\s|\S)*\/fusion_testimonial\]/U', $content, $single_testimonials );
					}

					if ( isset( $single_testimonials[0] ) ) {
						shuffle( $single_testimonials[0] );
						$content = implode( '', $single_testimonials[0] );
					}
				}

				fusion_element_rendering_elements( true );
				$html = sprintf(
					'<div %s>%s<div %s>%s</div>%s</div>',
					FusionBuilder::attributes( 'testimonials-shortcode' ),
					$styles,
					FusionBuilder::attributes( 'testimonials-shortcode-testimonials' ),
					do_shortcode( $content ),
					$pagination
				);
				fusion_element_rendering_elements( false );

				$this->testimonials_counter++;
				$this->testimonials_child_counter = 1;

				$this->on_render();

				return apply_filters( 'fusion_element_testimonials_parent_content', $html, $args );

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->parent_args['hide_on_mobile'],
					[
						'class' => 'fusion-testimonials ' . $this->parent_args['design'] . ' fusion-testimonials-' . $this->testimonials_counter,
						'style' => '',
					]
				);

				$attr['data-random'] = $this->parent_args['random'];
				$attr['data-speed']  = $this->parent_args['speed'];

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->parent_args );

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

				return $attr;

			}

			/**
			 * Builds the testimonials attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function testimonials_attr() {
				return [
					'class' => 'reviews',
				];
			}

			/**
			 * Render the child shortcode.
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args   Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string         HTML output.
			 */
			public function render_child( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'child' ), $args, 'fusion_testimonial' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_testimonial', $args );

				$defaults['image_border_radius'] = FusionBuilder::validate_shortcode_attr_value( $defaults['image_border_radius'], 'px' );

				if ( 'round' === $defaults['image_border_radius'] ) {
					$defaults['image_border_radius'] = '50%';
				}

				extract( $defaults );

				$this->child_args = $defaults;

				// Check for deprecated.
				if ( $gender ) {
					$this->child_args['avatar'] = $gender;
				}

				if ( 'clean' === $this->parent_args['design'] ) {
					$html = $this->render_child_clean( $content );
				} else {
					$html = $this->render_child_classic( $content );
				}

				$this->testimonials_child_counter++;

				return apply_filters( 'fusion_element_testimonials_child_content', $html, $args );

			}

			/**
			 * Render classic design.
			 *
			 * @access private
			 * @since 1.0
			 * @param string $content The content.
			 * @return string
			 */
			private function render_child_classic( $content ) {

				$inner_content = $thumbnail = $pic = '';

				if ( 'image' === $this->child_args['avatar'] && $this->child_args['image'] ) {

					$image_data = fusion_library()->images->get_attachment_data_by_helper( $this->child_args['image_id'], $this->child_args['image'] );

					$this->child_args['image_width']  = $image_data['width'];
					$this->child_args['image_height'] = $image_data['height'];
					$this->child_args['image_alt']    = $image_data['alt'];

					$pic = sprintf( '<img %s />', FusionBuilder::attributes( 'testimonials-shortcode-image' ) );
				}

				if ( 'image' === $this->child_args['avatar'] && ! $this->child_args['image'] ) {
					$this->child_args['avatar'] = 'none';
				}

				if ( 'none' !== $this->child_args['avatar'] ) {
					$thumbnail = sprintf( '<span %s>%s</span>', FusionBuilder::attributes( 'testimonials-shortcode-thumbnail' ), $pic );
				}

				$inner_content .= sprintf( '<div %s>%s<span %s>', FusionBuilder::attributes( 'testimonials-shortcode-author' ), $thumbnail, FusionBuilder::attributes( 'company-name' ) );

				if ( $this->child_args['name'] ) {
					$inner_content .= sprintf( '<strong>%s</strong>', $this->child_args['name'] );
				}

				if ( $this->child_args['name'] && $this->child_args['company'] ) {
					$inner_content .= ', ';
				}

				if ( $this->child_args['company'] ) {

					if ( ! empty( $this->child_args['link'] ) && $this->child_args['link'] ) {

						$combined_attribs = 'target="' . $this->child_args['target'] . '"';
						if ( '_blank' === $this->child_args['target'] ) {
							$combined_attribs = 'target="' . $this->child_args['target'] . '" rel="noopener noreferrer"';
						}
						$inner_content .= sprintf( '<a href="%s" %s>%s</a>', $this->child_args['link'], $combined_attribs, sprintf( '<span>%s</span>', $this->child_args['company'] ) );

					} else {

						$inner_content .= sprintf( '<span>%s</span>', $this->child_args['company'] );

					}
				}

				$inner_content .= '</span></div>';

				$html = sprintf(
					'<div %s><blockquote><q %s>%s</q></blockquote>%s</div>',
					FusionBuilder::attributes( 'testimonials-shortcode-review' ),
					FusionBuilder::attributes( 'testimonials-shortcode-quote' ),
					do_shortcode( $content ),
					$inner_content
				);

				return $html;

			}

			/**
			 * Render clean design.
			 *
			 * @access private
			 * @since 1.0
			 * @param string $content The content.
			 * @return string
			 */
			private function render_child_clean( $content ) {

				$thumbnail = $pic = $author = '';

				if ( 'image' === $this->child_args['avatar'] && $this->child_args['image'] ) {

					$image_data = fusion_library()->images->get_attachment_data_by_helper( $this->child_args['image_id'], $this->child_args['image'] );

					$this->child_args['image_width']  = $image_data['width'];
					$this->child_args['image_height'] = $image_data['height'];
					$this->child_args['image_alt']    = $image_data['alt'];

					if ( ! $this->child_args['image_id'] ) {
						$this->child_args['image_id'] = $image_data['id'];
					}

					$pic = sprintf( '<img %s />', FusionBuilder::attributes( 'testimonials-shortcode-image' ) );
				}

				if ( 'image' === $this->child_args['avatar'] && ! $this->child_args['image'] ) {
					$this->child_args['avatar'] = 'none';
				}

				if ( 'none' !== $this->child_args['avatar'] ) {
					$thumbnail = sprintf( '<div %s>%s</div>', FusionBuilder::attributes( 'testimonials-shortcode-thumbnail' ), $pic );
				}

				$author .= sprintf( '<div %s><span %s>', FusionBuilder::attributes( 'testimonials-shortcode-author' ), FusionBuilder::attributes( 'company-name' ) );

				if ( $this->child_args['name'] ) {
					$author .= sprintf( '<strong>%s</strong>', $this->child_args['name'] );
				}

				if ( $this->child_args['name'] && $this->child_args['company'] ) {
					$author .= ', ';
				}

				if ( $this->child_args['company'] ) {

					if ( ! empty( $this->child_args['link'] ) && $this->child_args['link'] ) {
						$combined_attribs = 'target="' . $this->child_args['target'] . '"';
						if ( '_blank' === $this->child_args['target'] ) {
							$combined_attribs = 'target="' . $this->child_args['target'] . '" rel="noopener noreferrer"';
						}
						$author .= sprintf( '<a href="%s" %s>%s</a>', $this->child_args['link'], $combined_attribs, sprintf( '<span>%s</span>', $this->child_args['company'] ) );
					} else {
						$author .= sprintf( '<span>%s</span>', $this->child_args['company'] );
					}
				}

				$author .= '</span></div>';

				$html = sprintf(
					'<div %s>%s<blockquote %s><q %s>%s</q></blockquote>%s</div>',
					FusionBuilder::attributes( 'testimonials-shortcode-review' ),
					$thumbnail,
					FusionBuilder::attributes( 'testimonials-shortcode-blockquote' ),
					FusionBuilder::attributes( 'testimonials-shortcode-quote' ),
					do_shortcode( $content ),
					$author
				);

				return $html;
			}

			/**
			 * Builds the blockquote attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function blockquote_attr() {
				$attr = [];

				if ( Fusion_Color::new_color( $this->parent_args['backgroundcolor'] )->is_color_transparent() && 'none' !== $this->child_args['avatar'] ) {
					$attr['style'] = 'margin: -25px;';
				}

				return $attr;

			}

			/**
			 * Builds the quotes attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function quote_attr() {
				return [
					'style' => 'background-color:' . $this->parent_args['backgroundcolor'] . ';color:' . $this->parent_args['textcolor'] . ';',
					'class' => 'fusion-clearfix',
				];
			}

			/**
			 * Builds the review attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function review_attr() {

				$attr = [
					'class' => 'review ',
				];

				if ( 1 === $this->testimonials_child_counter ) {
					$attr['class'] .= 'active-testimonial ';
				}

				if ( 'none' === $this->child_args['avatar'] ) {
					$attr['class'] .= 'no-avatar';
				} elseif ( 'image' === $this->child_args['avatar'] ) {
					$attr['class'] .= 'avatar-image';
				} else {
					$attr['class'] .= $this->child_args['avatar'];
				}

				return $attr;

			}

			/**
			 * Builds the thumbnail attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function thumbnail_attr() {

				$attr = [
					'class' => 'testimonial-thumbnail',
				];

				if ( 'image' !== $this->child_args['avatar'] ) {
					$attr['class'] .= ' doe';
					$attr['style']  = sprintf( 'color:%s;', $this->parent_args['textcolor'] );
				}

				return $attr;

			}

			/**
			 * Builds the image attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function image_attr() {
				$attr = [
					'class'  => 'testimonial-image',
					'src'    => $this->child_args['image'],
					'width'  => $this->child_args['image_width'],
					'height' => $this->child_args['image_height'],
					'alt'    => $this->child_args['image_alt'],
				];

				if ( $this->child_args['image_border_radius'] ) {
					$attr['style'] = sprintf(
						'-webkit-border-radius:%s;-moz-border-radius:%s;border-radius:%s;',
						$this->child_args['image_border_radius'],
						$this->child_args['image_border_radius'],
						$this->child_args['image_border_radius']
					);
				}

				$attr = fusion_library()->images->lazy_load_attributes( $attr, $this->child_args['image_id'] );

				return $attr;

			}

			/**
			 * Builds the author attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function author_attr() {
				return [
					'class' => 'author',
					'style' => 'color:' . $this->parent_args['textcolor'] . ';',
				];
			}

			/**
			 * Builds the pagination attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function pagination_attr() {
				return [
					'class' => 'testimonial-pagination',
					'id'    => 'fusion-testimonials-' . $this->testimonials_counter,
				];
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Testimonials settings.
			 */
			public function add_options() {

				return [
					'testimonials_shortcode_section' => [
						'label'       => esc_html__( 'Testimonials', 'fusion-builder' ),
						'description' => '',
						'id'          => 'testimonials_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-bubbles',
						'fields'      => [
							'testimonial_bg_color'   => [
								'label'       => esc_html__( 'Testimonial Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the testimonial background.', 'fusion-builder' ),
								'id'          => 'testimonial_bg_color',
								'default'     => 'var(--awb-color2)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--testimonial_bg_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'testimonial_text_color' => [
								'label'       => esc_html__( 'Testimonial Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the testimonial text.', 'fusion-builder' ),
								'id'          => 'testimonial_text_color',
								'default'     => 'var(--awb-color8)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--testimonial_text_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'testimonials_speed'     => [
								'label'       => esc_html__( 'Testimonials Speed', 'fusion-builder' ),
								'description' => __( 'Controls the speed of the testimonial slider. ex: 1000 = 1 second. <strong>IMPORTANT:</strong> Setting speed to 0 will disable autoplay for testimonials slider.', 'fusion-builder' ),
								'id'          => 'testimonials_speed',
								'default'     => '4000',
								'type'        => 'slider',
								'choices'     => [
									'min'  => '0',
									'max'  => '20000',
									'step' => '250',
								],
							],
							'testimonials_random'    => [
								'label'       => esc_html__( 'Random Order', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on to display testimonials in a random order.', 'fusion-builder' ),
								'id'          => 'testimonials_random',
								'default'     => '0',
								'type'        => 'switch',
							],
						],
					],
				];
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {
				$fusion_settings = awb_get_fusion_settings();

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-testimonials',
					FusionBuilder::$js_folder_url . '/general/fusion-testimonials.js',
					FusionBuilder::$js_folder_path . '/general/fusion-testimonials.js',
					[ 'jquery' ],
					'1',
					true
				);
				Fusion_Dynamic_JS::localize_script(
					'fusion-testimonials',
					'fusionTestimonialVars',
					[
						'testimonials_speed' => intval( $fusion_settings->get( 'testimonials_speed' ) ),
					]
				);
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/testimonials.min.css' );
			}
		}
	}

	new FusionSC_Testimonials();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_testimonials() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Testimonials',
			[
				'name'          => esc_attr__( 'Testimonials', 'fusion-builder' ),
				'shortcode'     => 'fusion_testimonials',
				'multi'         => 'multi_element_parent',
				'element_child' => 'fusion_testimonial',
				'icon'          => 'fusiona-bubbles',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-testimonials-preview.php',
				'preview_id'    => 'fusion-builder-block-module-testimonials-preview-template',
				'child_ui'      => true,
				'sortable'      => false,
				'help_url'      => 'https://theme-fusion.com/documentation/avada/elements/testimonials-element/',
				'params'        => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this testimonial element.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_testimonial name="' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '" avatar="male" image="" image_border_radius="" company="' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '" link="" target="_self"]' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '[/fusion_testimonial]',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Design', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a design for the element.', 'fusion-builder' ),
						'param_name'  => 'design',
						'value'       => [
							'classic' => esc_attr__( 'Classic', 'fusion-builder' ),
							'clean'   => esc_attr__( 'Clean', 'fusion-builder' ),
						],
						'default'     => 'classic',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Navigation Bullets', 'fusion-builder' ),
						'description' => esc_attr__( 'Select to show navigation bullets.', 'fusion-builder' ),
						'param_name'  => 'navigation',
						'value'       => [
							'yes' => esc_attr__( 'Show', 'fusion-builder' ),
							'no'  => esc_attr__( 'Hide', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Testimonials Speed', 'fusion-builder' ),
						'description' => __( 'Set the speed of the testimonial slider. ex: 1000 = 1 second. <strong>IMPORTANT:</strong> Setting speed to 0 will disable autoplay for testimonials slider.', 'fusion-builder' ),
						'param_name'  => 'speed',
						'default'     => $fusion_settings->get( 'testimonials_speed' ),
						'min'         => '0',
						'max'         => '20000',
						'step'        => '250',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color. ', 'fusion-builder' ),
						'param_name'  => 'backgroundcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'testimonial_bg_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color. ', 'fusion-builder' ),
						'param_name'  => 'textcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'testimonial_text_color' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Random Order', 'fusion-builder' ),
						'description' => esc_attr__( 'Turn on to display testimonials in a random order.' ),
						'param_name'  => 'random',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
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
add_action( 'fusion_builder_before_init', 'fusion_element_testimonials' );

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_testimonial() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Testimonials',
			[
				'name'              => esc_attr__( 'Testimonial', 'fusion-builder' ),
				'shortcode'         => 'fusion_testimonial',
				'hide_from_builder' => true,
				'allow_generator'   => true,
				'params'            => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Name', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert the name of the person.', 'fusion-builder' ),
						'param_name'  => 'name',
						'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Avatar', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose which kind of Avatar to be displayed.', 'fusion-builder' ),
						'param_name'  => 'avatar',
						'value'       => [
							'none'   => esc_attr__( 'None', 'fusion-builder' ),
							'male'   => esc_attr__( 'Male', 'fusion-builder' ),
							'female' => esc_attr__( 'Female', 'fusion-builder' ),
							'image'  => esc_attr__( 'Image', 'fusion-builder' ),
						],
						'icons'       => [
							'male'   => '<svg width="18" height="18" viewBox="0 0 1024 1024"><path d="M889.366 737.92c-44.8-58.454-98.986-96.426-176.618-117.952l-72.748 254.698c0 23.466-19.2 42.666-42.666 42.666s-42.666-19.2-42.666-42.666v-202.666c0-29.44-23.894-53.334-53.334-53.334s-53.334 23.894-53.334 53.334v202.666c0 23.466-19.2 42.666-42.666 42.666s-42.666-19.2-42.666-42.666l-72.746-254.698c-77.654 21.76-131.84 59.498-176.64 117.952-17.708 23.040-27.308 69.334-27.948 94.080v106.666c0 47.146 38.186 85.334 85.334 85.334h661.334c47.146 0 85.334-38.186 85.334-85.334v-106.666c-0.642-24.746-10.242-71.040-27.97-94.080zM501.334 533.334c143.786 0 224-183.040 224-307.628s-100.268-225.706-224-225.706-224 101.12-224 225.706 77.652 307.628 224 307.628z"></path></svg>',
							'female' => '<svg width="18" height="18" viewBox="0 0 1024 1024"><path d="M889.366 737.92c-24.96-32.618-52.886-58.88-86.4-79.552-51.82 114.966-167.446 194.966-301.632 194.966s-249.814-80-301.674-194.986c-33.28 20.694-61.418 46.934-86.378 79.552-17.708 23.060-27.33 69.354-27.948 94.1 0.214 6.4 0 106.666 0 106.666 0 47.146 38.186 85.334 85.334 85.334h661.334c47.146 0 85.334-38.186 85.334-85.334 0 0-0.214-100.266 0-106.666-0.642-24.746-10.242-71.040-27.97-94.080zM385.472 602.666c-17.898 1.92-34.986 4.266-51.178 7.040-18.56 4.694-32.64 21.546-32.64 41.6 0 8.32 2.346 15.766 6.4 22.4 44.8 57.388 114.752 94.294 193.28 94.294 76.586 0 144.854-34.986 189.866-89.814 5.952-7.254 9.366-16.64 9.366-26.88 0-20.906-15.146-38.4-35.2-42.026-16.618-3.008-34.134-5.568-52.886-7.488-24.106-4.458-42.24-21.526-42.24-47.126 0-23.466 17.472-41.366 40.96-44.374 2.326-0.426 7.872-0.618 7.872-0.618 114.794-8.128 192.874-31.382 244.266-75.754 7.062-7.466 11.328-17.494 11.328-28.586 0-22.186-16.854-40.32-38.4-42.454-63.36-12.8-110.932-65.28-110.932-128.214v-8.96c0-124.586-100.268-225.706-224-225.706s-224 101.12-224 225.706v8.96c0 62.934-47.574 115.414-110.934 128.214-21.546 2.134-38.4 20.266-38.4 42.454 0 11.094 4.266 21.12 11.286 28.586 51.648 44.374 129.706 67.626 244.714 75.754 3.414 0 6.634 0.214 9.6 0.618 23.872 3.414 38.806 20.48 38.806 44.374 0.020 27.308-20.438 44.8-46.934 48z"></path></svg>',
							'image'  => '<span class="fusiona-image" style="font-size:18px;"></span>',
						],
						'default'     => 'male',
					],
					[
						'type'        => 'upload',
						'heading'     => esc_attr__( 'Custom Avatar', 'fusion-builder' ),
						'description' => esc_attr__( 'Upload a custom avatar image.', 'fusion-builder' ),
						'param_name'  => 'image',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'avatar',
								'value'    => 'image',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Avatar Image ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Avatar Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'image_id',
						'value'       => '',
						'hidden'      => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Border Radius', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the radius of the testimonial image. In pixels (px), ex: 1px, or "round". ', 'fusion-builder' ),
						'param_name'  => 'image_border_radius',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'avatar',
								'value'    => 'image',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Company', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert the name of the company.', 'fusion-builder' ),
						'param_name'  => 'company',
						'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'link_selector',
						'heading'     => esc_attr__( 'Link', 'fusion-builder' ),
						'description' => esc_attr__( 'Add the URL the company name will link to.', 'fusion-builder' ),
						'param_name'  => 'link',
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
						'description' => __( '_self = open in same window. <br />_blank = open in new window.', 'fusion-builder' ),
						'param_name'  => 'target',
						'value'       => [
							'_self'  => '_self',
							'_blank' => '_blank',
						],
						'default'     => '_self',
						'dependency'  => [
							[
								'element'  => 'link',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Testimonial Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Add the testimonial content.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_testimonial' );

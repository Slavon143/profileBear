<?php
/**
 * Add the image hotspots element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.5
 */

if ( fusion_is_element_enabled( 'fusion_image_hotspots' ) ) {

	if ( ! class_exists( 'FusionSC_Image_Hotspots' ) ) {

		/**
		 * Shortcode class.
		 *
		 * @since 3.5
		 */
		class FusionSC_Image_Hotspots extends Fusion_Element {

			/**
			 * The number of instance of this element. Working as an id.
			 *
			 * @since 3.5
			 * @var int
			 */
			protected $element_counter = 1;

			/**
			 * The number of instance of the children elements. Working as an id.
			 *
			 * @since 3.5
			 * @var int
			 */
			protected $child_element_counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.5
			 * @var array
			 */
			protected $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.5
			 */
			public function __construct() {
				parent::__construct();

				add_shortcode( 'fusion_image_hotspots', [ $this, 'render_parent' ] );
				add_shortcode( 'fusion_image_hotspot_point', [ $this, 'render_child' ] );

				add_filter( 'fusion_attr_image-hotspot-element-attr', [ $this, 'element_attr' ] );

				add_filter( 'fusion_attr_image-hotspot-child-element-attr', [ $this, 'child_elem_attr' ] );
				add_filter( 'fusion_attr_image-hotspot-child-element-icon-attr', [ $this, 'child_elem_icon_attr' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.5.0
			 * @param string $context Whether we want parent or child.
			 *                        Returns array( parent, child ) if empty.
			 * @return array
			 */
			public static function get_element_defaults( $context = '' ) {
				$fusion_settings = awb_get_fusion_settings();

				$parent = [
					'image'                            => '',
					'image_id'                         => '',
					'element_content'                  => '',
					'image_max_width'                  => '',
					'alignment'                        => '',
					'alignment_medium'                 => '',
					'alignment_small'                  => '',
					'popover_trigger'                  => 'hover',
					'hide_on_mobile'                   => fusion_builder_default_visibility( 'string' ),
					'class'                            => '',
					'id'                               => '',

					'items_animation'                  => 'none',
					'popover_heading_background_color' => '',
					'popover_content_background_color' => '',
					'popover_border_color'             => '',
					'popover_text_color'               => '',

					'margin_top'                       => '',
					'margin_right'                     => '',
					'margin_bottom'                    => '',
					'margin_left'                      => '',

					'animation_direction'              => 'left',
					'animation_offset'                 => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'                  => '',
					'animation_type'                   => '',
				];

				$child = [
					'pos_x'                          => '25',
					'pos_y'                          => '25',
					'icon'                           => '',
					'title'                          => '',
					'button_action'                  => 'popover',
					'long_title'                     => '',
					'long_text'                      => '',
					'popover_placement'              => 'auto',
					'link'                           => '',
					'link_title'                     => '',
					'link_target'                    => '_blank',

					'font_size'                      => '',
					'hotspot_text_color'             => '',
					'hotspot_background_color'       => '',
					'hotspot_hover_text_color'       => '',
					'hotspot_hover_background_color' => '',

					'icon_distance'                  => '',

					'padding_top'                    => '',
					'padding_right'                  => '',
					'padding_bottom'                 => '',
					'padding_left'                   => '',

					'border_radius_top_left'         => '',
					'border_radius_top_right'        => '',
					'border_radius_bottom_right'     => '',
					'border_radius_bottom_left'      => '',
				];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				} else {
					return [
						'parent' => $parent,
						'child'  => $child,
					];
				}
			}

			/**
			 * Render the shortcode.
			 *
			 * @access public
			 * @since 3.5
			 * @param array  $args Shortcode parameters.
			 * @param string $content The content inside the shortcode.
			 * @return string HTML output.
			 */
			public function render_parent( $args, $content = '' ) {
				$this->args        = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args, 'fusion_image_hotspots' );
				$this->parent_args = $this->args;

				$html  = '<div ' . FusionBuilder::attributes( 'image-hotspot-element-attr' ) . '>';
				$html .= $this->get_style_element();
				$html .= '<div class="awb-image-hotspots-wrapper">';

				$image_html = $this->get_image_html();
				if ( ! empty( $image_html ) ) {
					$html .= $image_html;
					$html .= do_shortcode( $content );
				}

				$html .= '</div>';
				$html .= '</div>';

				if ( 1 === $this->element_counter ) {
					$this->on_first_render();
				}
				$this->element_counter++;

				return $html;
			}

			/**
			 * Get the image html.
			 *
			 * @since 3.5
			 * @return string
			 */
			public function get_image_html() {
				if ( empty( $this->args['image'] ) ) {
					return '';
				}

				// Ex: This happens when image comes from "Logo" dynamic data.
				$image_in_json_format = is_array( json_decode( $this->args['image'], true ) );
				if ( $image_in_json_format ) {
					$image_json             = json_decode( $this->args['image'], true );
					$extracted_json_data    = $this->get_logo_image_data( $image_json );
					$this->args['image_id'] = $extracted_json_data['id'];
					$this->args['image']    = $extracted_json_data['url'];
					$json_srcset            = $extracted_json_data['srcset'];
				}

				$image_data = fusion_library()->images->get_attachment_data_by_helper( $this->args['image_id'], $this->args['image'] );

				$img_attr = 'src="' . esc_attr( $this->args['image'] ) . '"';

				if ( ! empty( $image_data['alt'] ) ) {
					$img_attr .= ' alt="' . esc_attr( $image_data['alt'] ) . '"';
				} elseif ( ! empty( $image_data['title_attribute'] ) ) {
					$img_attr .= ' alt="' . esc_attr( $image_data['title_attribute'] ) . '"';
				} else {
					$img_attr .= ' alt=""';
				}

				if ( ! empty( $image_data['width'] ) && ! empty( $image_data['height'] ) ) {
					$img_attr .= ' width="' . esc_attr( $image_data['width'] ) . '" height="' . esc_attr( $image_data['height'] ) . '"';
				}

				if ( ! empty( $image_data['title_attribute'] ) ) {
					$img_attr .= ' title="' . esc_attr( $image_data['title_attribute'] ) . '"';
				}

				$img_classes = 'awb-image-hotspots-image';
				if ( ! empty( $image_data['id'] ) ) {
					$img_classes .= ' wp-image-' . $image_data['id'];
				}
				$img_attr .= ' class="' . $img_classes . '"';

				$srcset = wp_get_attachment_image_srcset( $image_data['id'], 'full' );
				$sizes  = wp_get_attachment_image_sizes( $image_data['id'], 'full' );

				if ( $srcset ) {
					$img_attr .= ' srcset="' . $srcset . '"';
				} elseif ( ! empty( $json_srcset ) ) {
					$img_attr .= ' srcset="' . $json_srcset . '"';
				}

				if ( $sizes ) {
					$img_attr .= ' sizes="' . $sizes . '"';
				}

				$image = '<img ' . $img_attr . '/>';

				$image = fusion_library()->images->apply_lazy_loading( $image, null, $image_data['id'], 'full' );

				return $image;
			}

			/**
			 * Creates the element attributes.
			 *
			 * @since 3.5
			 * @return array
			 */
			public function element_attr() {
				$attr = [
					'class' => 'awb-image-hotspots',
				];

				$attr['class'] .= ' ' . $this->get_base_class();

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$alignment_large = $this->args['alignment'];
				if ( ! empty( $alignment_large ) ) {
					$attr['style'] = 'justify-content:' . $this->args['alignment'] . ';';
				}

				$alignment_medium = ! empty( $this->args['alignment_medium'] ) ? $this->args['alignment_medium'] : false;
				if ( $alignment_medium && $alignment_large !== $alignment_medium ) {
					$attr['class'] .= ' md-flex-align-' . $alignment_medium;
				}

				$alignment_small = ! empty( $this->args['alignment_small'] ) ? $this->args['alignment_small'] : false;
				if ( $alignment_small && $alignment_large !== $alignment_small ) {
					$attr['class'] .= ' sm-flex-align-' . $alignment_small;
				}

				return $attr;
			}

			/**
			 * Creates the style HTML tag for this element.
			 *
			 * @return string
			 */
			public function get_style_element() {
				$style             = '';
				$base_class_name   = '.awb-image-hotspots.' . $this->get_base_class();
				$this->dynamic_css = [];

				$image_wrapper_selector            = $base_class_name . ' .awb-image-hotspots-wrapper';
				$popover_selector                  = $base_class_name . ' .popover';
				$popover_heading_selector          = $base_class_name . ' .popover-title';
				$popover_content_selector          = $base_class_name . ' .popover-content';
				$popover_border_color_selectors    = [
					$popover_selector,
					$popover_heading_selector,
					$popover_content_selector,
				];
				$popover_border_color_arrow_right  =
				[
					$base_class_name . ' .popover.right .arrow',
					$base_class_name . ' .popover.right .arrow:after',
				];
				$popover_border_color_arrow_left   =
				[
					$base_class_name . ' .popover.left .arrow',
					$base_class_name . ' .popover.left .arrow:after',
				];
				$popover_border_color_arrow_top    =
				[
					$base_class_name . ' .popover.top .arrow',
					$base_class_name . ' .popover.top .arrow:after',
				];
				$popover_border_color_arrow_bottom =
				[
					$base_class_name . ' .popover.bottom .arrow',
					$base_class_name . ' .popover.bottom .arrow:after',
				];

				if ( $this->args['image_max_width'] ) {
					$this->add_css_property( $image_wrapper_selector, 'max-width', $this->args['image_max_width'] );
				}

				if ( $this->args['popover_heading_background_color'] ) {
					$this->add_css_property( $popover_heading_selector, 'background-color', $this->args['popover_heading_background_color'] );
					$this->add_css_property( $popover_border_color_arrow_bottom, 'border-bottom-color', $this->args['popover_heading_background_color'] );
				}

				if ( $this->args['popover_content_background_color'] ) {
					$this->add_css_property( $popover_content_selector, 'background-color', $this->args['popover_content_background_color'] );
					$this->add_css_property( $popover_border_color_arrow_right, 'border-right-color', $this->args['popover_content_background_color'] );
					$this->add_css_property( $popover_border_color_arrow_left, 'border-left-color', $this->args['popover_content_background_color'] );
					$this->add_css_property( $popover_border_color_arrow_top, 'border-top-color', $this->args['popover_content_background_color'] );
				}

				if ( $this->args['popover_text_color'] ) {
					$this->add_css_property( [ $popover_heading_selector, $popover_content_selector ], 'color', $this->args['popover_text_color'] );
				}

				if ( $this->args['popover_border_color'] ) {
					$this->add_css_property( $popover_selector, 'background-color', $this->args['popover_border_color'] );
					$this->add_css_property( $popover_border_color_selectors, 'border-color', $this->args['popover_border_color'] );
				}

				if ( $this->args['margin_top'] ) {
					$this->add_css_property( $base_class_name, 'margin-top', $this->args['margin_top'] );
				}

				if ( $this->args['margin_right'] ) {
					$this->add_css_property( $base_class_name, 'margin-right', $this->args['margin_right'] );
				}

				if ( $this->args['margin_bottom'] ) {
					$this->add_css_property( $base_class_name, 'margin-bottom', $this->args['margin_bottom'] );
				}

				if ( $this->args['margin_left'] ) {
					$this->add_css_property( $base_class_name, 'margin-left', $this->args['margin_left'] );
				}

				$style = $this->parse_css();

				return $style ? '<style>' . $style . '</style>' : '';
			}

			/**
			 * Render the child shortcode.
			 *
			 * @access public
			 * @since 3.5
			 * @param array  $args Shortcode parameters.
			 * @param string $content The content inside the shortcode.
			 * @return string HTML output.
			 */
			public function render_child( $args, $content = '' ) {
				$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'child' ), $args, 'fusion_image_hotspot_point' );

				$html  = '';
				$html .= $this->get_children_style_element();
				$html .= '<a ' . FusionBuilder::attributes( 'image-hotspot-child-element-attr' ) . '>';
				if ( ! empty( $this->args['icon'] ) ) {
					$html .= '<i ' . FusionBuilder::attributes( 'image-hotspot-child-element-icon-attr' ) . '></i>';
				}
				$html .= ' ';
				if ( ! empty( $this->args['title'] ) ) {
					$html .= esc_html( $this->args['title'] );
				}
				$html .= '</a>';

				$this->child_element_counter++;
				return $html;
			}

			/**
			 * Create the child style.
			 *
			 * @since 3.5
			 * @return string HTML style tag.
			 */
			protected function get_children_style_element() {
				$style             = '';
				$base_class_id     = '.awb-image-hotspots-hotspot-' . $this->child_element_counter;
				$base_class_name   = '.awb-image-hotspots-hotspot' . $base_class_id;
				$this->dynamic_css = [];

				$hovered_base_class = [
					$base_class_name . ':hover',
					$base_class_name . ':focus',
				];

				if ( $this->args['pos_x'] ) {
					$this->add_css_property( $base_class_name, 'left', $this->args['pos_x'] . '%' );
				}

				if ( $this->args['pos_y'] ) {
					$this->add_css_property( $base_class_name, 'top', $this->args['pos_y'] . '%' );
				}

				if ( $this->args['font_size'] ) {
					$this->add_css_property( $base_class_name, 'font-size', $this->args['font_size'] );
				}

				if ( $this->args['hotspot_text_color'] ) {
					$this->add_css_property( $base_class_name, 'color', $this->args['hotspot_text_color'] );
				}

				if ( $this->args['hotspot_background_color'] ) {
					$this->add_css_property( $base_class_name, 'background-color', $this->args['hotspot_background_color'] );

					if ( 'sonar' === $this->parent_args['items_animation'] ) {
						$animation_class_name = $base_class_id . '.' . $this->animation_to_class_name( $this->parent_args['items_animation'] );
						$this->add_css_property( $animation_class_name, 'border-color', $this->args['hotspot_background_color'] );
					}
				}

				if ( $this->args['hotspot_hover_text_color'] ) {
					$this->add_css_property( $hovered_base_class, 'color', $this->args['hotspot_hover_text_color'] );
				}

				if ( $this->args['hotspot_hover_background_color'] ) {
					$this->add_css_property( $hovered_base_class, 'background-color', $this->args['hotspot_hover_background_color'] );

					if ( 'sonar' === $this->parent_args['items_animation'] ) {
						$animation_class_name = [
							$base_class_id . ':hover.' . $this->animation_to_class_name( $this->parent_args['items_animation'] ),
							$base_class_id . ':focus.' . $this->animation_to_class_name( $this->parent_args['items_animation'] ),
						];
						$this->add_css_property( $animation_class_name, 'border-color', $this->args['hotspot_hover_background_color'] );
					}
				}

				if ( $this->args['padding_top'] ) {
					$this->add_css_property( $base_class_name, 'padding-top', $this->args['padding_top'] );
				}

				if ( $this->args['padding_right'] ) {
					$this->add_css_property( $base_class_name, 'padding-right', $this->args['padding_right'] );
				}

				if ( $this->args['padding_bottom'] ) {
					$this->add_css_property( $base_class_name, 'padding-bottom', $this->args['padding_bottom'] );
				}

				if ( $this->args['padding_left'] ) {
					$this->add_css_property( $base_class_name, 'padding-left', $this->args['padding_left'] );
				}

				if ( $this->args['border_radius_top_left'] ) {
					$this->add_css_property( $base_class_name, 'border-top-left-radius', $this->args['border_radius_top_left'] );
				}

				if ( $this->args['border_radius_top_right'] ) {
					$this->add_css_property( $base_class_name, 'border-top-right-radius', $this->args['border_radius_top_right'] );
				}

				if ( $this->args['border_radius_bottom_right'] ) {
					$this->add_css_property( $base_class_name, 'border-bottom-right-radius', $this->args['border_radius_bottom_right'] );
				}

				if ( $this->args['border_radius_bottom_left'] ) {
					$this->add_css_property( $base_class_name, 'border-bottom-left-radius', $this->args['border_radius_bottom_left'] );
				}

				$style = $this->parse_css();

				return $style ? '<style>' . $style . '</style>' : '';
			}

			/**
			 * Creates the child element attributes.
			 *
			 * @since 3.5
			 * @return array
			 */
			public function child_elem_attr() {
				$attr = [
					'class' => 'awb-image-hotspots-hotspot',
				];

				$attr['class'] .= ' awb-image-hotspots-hotspot-' . $this->child_element_counter;

				$animation_class = $this->animation_to_class_name( $this->parent_args['items_animation'] );
				if ( $animation_class ) {
					$attr['class'] .= ' ' . $animation_class;
				}

				if ( 'link' === $this->args['button_action'] ) {
					$attr['href'] = esc_attr( $this->args['link'] );

					if ( ! empty( $this->args['link_title'] ) ) {
						$attr['title'] = esc_attr( $this->args['link_title'] );
					}

					$attr['target'] = $this->args['link_target'];

				} else {
					$attr['role']                                  = 'button';
					$attr['tabindex']                              = '0';
					$attr['data-awb-toggle-image-hotspot-popover'] = 'true';
					$attr['data-title']                            = esc_attr( trim( $this->args['long_title'] ) );
					$attr['data-content']                          = esc_attr( trim( fusion_decode_if_needed( $this->args['long_text'] ) ) );

					// Enable focus trigger(on hover) for keyboard-only disabled users.
					$trigger = $this->parent_args['popover_trigger'];
					if ( 'hover' === $trigger ) {
						$trigger .= ' focus';
					}
					$attr['data-trigger'] = $trigger;

					$placement = 'auto';
					if ( 'auto' !== $this->args['popover_placement'] ) {
						$placement .= ' ' . $this->args['popover_placement'];
					}
					$attr['data-placement'] = $placement;
				}

				return $attr;
			}

			/**
			 * Creates the child icon element attributes.
			 *
			 * @since 3.5
			 * @return array
			 */
			public function child_elem_icon_attr() {
				$attr = [
					'class' => fusion_font_awesome_name_handler( $this->args['icon'] ),
				];

				if ( ! empty( $this->args['icon_distance'] ) ) {
					if ( ! is_rtl() ) {
						$attr['style'] = 'margin-right:' . $this->args['icon_distance'] . ';';
					} else {
						$attr['style'] = 'margin-left:' . $this->args['icon_distance'] . ';';
					}
				}

				return $attr;
			}

			/**
			 * Get an array with the logo id, url, and srcset of the logo.
			 *
			 * @param array $logo_json Logo Json settings.
			 * @return array
			 */
			public function get_logo_image_data( $logo_json ) {
				$data           = [
					'id'     => '',
					'url'    => '',
					'srcset' => '',
				];
				$keys_to_verify = [ 'default', 'sticky', 'mobile' ];

				foreach ( $keys_to_verify as $key ) {
					$is_url        = ( ! empty( $logo_json[ $key ]['normal']['url'] ) && ! empty( $logo_json[ $key ]['normal']['id'] ) );
					$is_retina_url = ( ! empty( $logo_json[ $key ]['retina']['url'] ) && ! empty( $logo_json[ $key ]['retina']['id'] ) );

					if ( $is_url ) {
						$data['url']    = $logo_json[ $key ]['normal']['url'];
						$data['id']     = $logo_json[ $key ]['normal']['id'];
						$data['srcset'] = $data['url'] . ' 1x';
						if ( $is_retina_url ) {
							$data['srcset'] .= ', ' . $logo_json[ $key ]['retina']['url'] . ' 2x';
						}
						return $data;
					}
					if ( $is_retina_url ) {
						$data['url']    = $logo_json[ $key ]['retina']['url'];
						$data['id']     = $logo_json[ $key ]['retina']['id'];
						$data['srcset'] = $data['url'] . ' 1x';
						return $data;
					}
				}

				return $data;
			}

			/**
			 * Get the class name with an unique id among elements.
			 *
			 * @since 3.5
			 * @return string
			 */
			public function get_base_class() {
				return 'awb-image-hotspots-' . $this->element_counter;
			}

			/**
			 * Get the animation class corresponding with the animation id.
			 *
			 * @since 3.5
			 * @param string $animation_name The animation name.
			 * @return string Empty string if do not exist.
			 */
			protected function animation_to_class_name( $animation_name ) {
				if ( 'pumping' === $animation_name ) {
					return 'awb-image-hotspots-hotspot-anim-pumping';
				}

				if ( 'pulsating' === $animation_name ) {
					return 'awb-image-hotspots-hotspot-anim-pulsating';
				}

				if ( 'showing' === $animation_name ) {
					return 'awb-image-hotspots-hotspot-anim-showing';
				}

				if ( 'sonar' === $animation_name ) {
					return 'awb-image-hotspots-hotspot-anim-sonar';
				}

				if ( 'pumping_showing' === $animation_name ) {
					return 'awb-image-hotspots-hotspot-anim-pump-showing';
				}

				return '';
			}

			/**
			 * Function that runs only on the first render.
			 *
			 * @since 3.5
			 * @return void
			 */
			public function on_first_render() {
				Fusion_Dynamic_JS::enqueue_script(
					'fusion-image-hotspots-js',
					FusionBuilder::$js_folder_url . '/general/fusion-image-hotspots.js',
					FusionBuilder::$js_folder_path . '/general/fusion-image-hotspots.js',
					[ 'jquery', 'fusion-popover' ],
					FUSION_BUILDER_VERSION,
					true
				);
			}

			/**
			 * Load base CSS.
			 *
			 * @since 3.5
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/image-hotspots.min.css' );
			}

		}

		new FusionSC_Image_Hotspots();

	}
}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 3.5
 */
function fusion_element_image_hotspots() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Image_Hotspots',
			[
				'name'          => esc_attr__( 'Image Hotspots', 'fusion-builder' ),
				'shortcode'     => 'fusion_image_hotspots',
				'multi'         => 'multi_element_parent',
				'icon'          => 'fusiona-hotspot-image',
				'child_ui'      => true,
				'element_child' => 'fusion_image_hotspot_point',
				'params'        => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this content box.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_image_hotspot_point][/fusion_image_hotspot_point]',
					],
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
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Image Max Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the maximum width the image should take up. Enter value including any valid CSS unit, ex: 200px. Leave empty to use full image width.', 'fusion-builder' ),
						'param_name'  => 'image_max_width',
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_html__( 'Select the alignment of the image.', 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => '',
						'responsive'  => [
							'state' => 'large',
						],
						'value'       => [
							''           => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'flex-start' => esc_attr__( 'Left', 'fusion-builder' ),
							'center'     => esc_attr__( 'Center', 'fusion-builder' ),
							'flex-end'   => esc_attr__( 'Right', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Popover Trigger Method', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose mouse action to trigger popover.' ),
						'param_name'  => 'popover_trigger',
						'value'       => [
							'hover' => esc_attr__( 'Hover', 'fusion-builder' ),
							'click' => esc_attr__( 'Click', 'fusion-builder' ),
						],
						'default'     => 'hover',
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_html__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
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

					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Hotspots Animation', 'fusion-builder' ),
						'description' => esc_html__( 'Select an animation for the image hotspots.', 'fusion-builder' ),
						'param_name'  => 'items_animation',
						'default'     => 'none',
						'value'       => [
							'none'            => esc_attr__( 'None', 'fusion-builder' ),
							'pumping'         => esc_attr__( 'Pumping', 'fusion-builder' ),
							'pulsating'       => esc_attr__( 'Pulsating', 'fusion-builder' ),
							'showing'         => esc_attr__( 'Showing', 'fusion-builder' ),
							/* translators: Name of an HTML element animation. */
							'sonar'           => esc_attr__( 'Sonar', 'fusion-builder' ),
							'pumping_showing' => esc_attr__( 'Pumping + Showing', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Popover Heading Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the background color of the popover heading.', 'fusion-builder' ),
						'param_name'  => 'popover_heading_background_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'popover_heading_bg_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Popover Content Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the background color of the popover content area.', 'fusion-builder' ),
						'param_name'  => 'popover_content_background_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'popover_content_bg_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Popover Border Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the border color of the of the popover box.', 'fusion-builder' ),
						'param_name'  => 'popover_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'popover_border_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Popover Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls all the text color inside the popover box.', 'fusion-builder' ),
						'param_name'  => 'popover_text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'popover_text_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					'fusion_margin_placeholder'    => [
						'param_name' => 'margin',
						'heading'    => esc_attr__( 'Margin', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.awb-image-hotspots',
					],
				],
				'parent',
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_image_hotspots' );

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_image_hotspot_point() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Image_Hotspots',
			[
				'name'              => esc_attr__( 'Hotspot Point', 'fusion-builder' ),
				'description'       => esc_attr__( 'Select the options for this hotspot point.', 'fusion-builder' ),
				'shortcode'         => 'fusion_image_hotspot_point',
				'hide_from_builder' => true,
				'allow_generator'   => false,
				'inline_editor'     => false,
				'show_ui'           => false,
				'tag_name'          => 'a',
				'subparam_map'      => [
					'padding_top'                => 'padding_dimensions',
					'padding_right'              => 'padding_dimensions',
					'padding_bottom'             => 'padding_dimensions',
					'padding_left'               => 'padding_dimensions',
					'border_radius_top_left'     => 'border_radius',
					'border_radius_top_right'    => 'border_radius',
					'border_radius_bottom_right' => 'border_radius',
					'border_radius_bottom_left'  => 'border_radius',
				],
				'params'            => [
					[
						'type'         => 'range',
						'heading'      => esc_attr__( 'Horizontal Position', 'fusion-builder' ),
						'description'  => esc_attr__( 'Select the horizontal position of the hotspot. In percentage of the image width.', 'fusion-builder' ),
						'param_name'   => 'pos_x',
						'value'        => '25',
						'min'          => '0',
						'max'          => '100',
						'step'         => '0.1',
						'dynamic_data' => true,
					],
					[
						'type'         => 'range',
						'heading'      => esc_attr__( 'Vertical Position', 'fusion-builder' ),
						'description'  => esc_attr__( 'Select the vertical position of the hotspot. In percentage of the image height.', 'fusion-builder' ),
						'param_name'   => 'pos_y',
						'value'        => '25',
						'min'          => '0',
						'max'          => '100',
						'step'         => '0.1',
						'dynamic_data' => true,
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Triggering Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Select an icon to be displayed inside the hotspot.', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Triggering Text', 'fusion-builder' ),
						'description'  => esc_attr__( 'Enter the text to be displayed on the hotspot.', 'fusion-builder' ),
						'param_name'   => 'title',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'         => 'radio_button_set',
						'heading'      => esc_attr__( 'Hotspot Trigger Action', 'fusion-builder' ),
						'description'  => esc_attr__( 'Select the hotspot trigger action. This can be a popover or a link.' ),
						'param_name'   => 'button_action',
						'value'        => [
							'popover' => esc_attr__( 'Popover', 'fusion-builder' ),
							'link'    => esc_attr__( 'Link', 'fusion-builder' ),
						],
						'default'      => 'popover',
						'dynamic_data' => true,
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Popover Heading', 'fusion-builder' ),
						'description'  => esc_attr__( 'Enter the popover heading.', 'fusion-builder' ),
						'param_name'   => 'long_title',
						'value'        => '',
						'dependency'   => [
							[
								'element'  => 'button_action',
								'value'    => 'popover',
								'operator' => '==',
							],
						],
						'dynamic_data' => true,
					],
					[
						'type'         => 'raw_textarea',
						'heading'      => esc_attr__( 'Popover Content', 'fusion-builder' ),
						'description'  => esc_attr__( 'Enter the popover content.', 'fusion-builder' ),
						'param_name'   => 'long_text',
						'value'        => '',
						'dependency'   => [
							[
								'element'  => 'button_action',
								'value'    => 'popover',
								'operator' => '==',
							],
						],
						'dynamic_data' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Popover Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the display position of the popover in relation to the triggering hotspot. Note: This will automatically change if the popover can\'t open in the preferred position.' ),
						'param_name'  => 'popover_placement',
						'value'       => [
							'auto'   => esc_attr__( 'Auto', 'fusion-builder' ),
							'top'    => esc_attr__( 'Top', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
							'bottom' => esc_attr__( 'Bottom', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
						],
						'default'     => 'auto',
						'dependency'  => [
							[
								'element'  => 'button_action',
								'value'    => 'popover',
								'operator' => '==',
							],
						],
					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Link', 'fusion-builder' ),
						'description'  => esc_attr__( 'Enter or select a link for the triggering hotspot.', 'fusion-builder' ),
						'param_name'   => 'link',
						'default'      => '',
						'dependency'   => [
							[
								'element'  => 'button_action',
								'value'    => 'link',
								'operator' => '==',
							],
						],
						'dynamic_data' => true,
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Link Title', 'fusion-builder' ),
						'description'  => esc_attr__( 'Set a link title that will be displayed, when the triggering hotspot is hovered.' ),
						'param_name'   => 'link_title',
						'default'      => '',
						'dependency'   => [
							[
								'element'  => 'button_action',
								'value'    => 'link',
								'operator' => '==',
							],
						],
						'dynamic_data' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
						'description' => __( '_self = open in same window<br />_blank = open in new window.', 'fusion-builder' ),
						'param_name'  => 'link_target',
						'value'       => [
							'_self'  => esc_attr__( '_self', 'fusion-builder' ),
							'_blank' => esc_attr__( '_blank', 'fusion-builder' ),
						],
						'default'     => '_blank',
						'dependency'  => [
							[
								'element'  => 'button_action',
								'value'    => 'link',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Font Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the color of the text and the icon.', 'fusion-builder' ),
						'param_name'  => 'hotspot_text_color',
						'value'       => '',
						'default'     => '#000000',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the background color of the hotspot.', 'fusion-builder' ),
						'param_name'  => 'hotspot_background_color',
						'value'       => '',
						'default'     => '#ffffff',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Hover Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the hover color of the text and the icon.', 'fusion-builder' ),
						'param_name'  => 'hotspot_hover_text_color',
						'value'       => '',
						'default'     => '#000000',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Hover Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the background hover color of the hotspot.', 'fusion-builder' ),
						'param_name'  => 'hotspot_hover_background_color',
						'value'       => '',
						'default'     => 'rgb(226, 226, 226)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Icon And Text Spacing', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the spacing between icon and text. Ex: 5px.', 'fusion-builder' ),
						'param_name'  => 'icon_distance',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'padding_dimensions',
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Border Radius', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'border_radius',
						'value'            => [
							'border_radius_top_left'     => '',
							'border_radius_top_right'    => '',
							'border_radius_bottom_right' => '',
							'border_radius_bottom_left'  => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_image_hotspot_point' );

<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( ! class_exists( 'Fusion_Column_Element' ) ) {
	/**
	 * Shortcode class.
	 *
	 * @since 1.0
	 */
	class Fusion_Column_Element extends Fusion_Element {

		/**
		 * The one, true instance array of this object.
		 *
		 * @static
		 * @access private
		 * @since 1.0
		 * @var array
		 */
		public static $instances = [];

		/**
		 * Column counter.
		 *
		 * @access private
		 * @since 1.9
		 * @var int
		 */
		private $column_counter = 0;

		/**
		 * An array of the shortcode arguments.
		 *
		 * @access public
		 * @since 1.0
		 * @var array
		 */
		public $args;

		/**
		 * An array of the shortcode attributes, before merging with defaults.
		 *
		 * @access public
		 * @since 3.0
		 * @var array
		 */
		public $atts;

		/**
		 * Styles for style block.
		 *
		 * @access protected
		 * @since 3.0
		 * @var string
		 */
		protected $styles = '';

		/**
		 * Previous spacing if set.
		 *
		 * @access protected
		 * @since 3.0
		 * @var mixed
		 */
		protected $previous_spacing = '';

		/**
		 * Active parent or not.
		 *
		 * @access protected
		 * @since 3.0
		 * @var bool
		 */
		public $active_parent = false;

		/**
		 * Column args for parent if nested.
		 *
		 * @access private
		 * @since 3.0
		 * @var mixed
		 */
		private $parent_args = false;

		/**
		 * If current column is nested.
		 *
		 * @access private
		 * @since 3.0
		 * @var mixed
		 */
		private $nested = false;

		/**
		 * If column is in the process of rendering.
		 *
		 * @access private
		 * @since 3.0
		 * @var mixed
		 */
		private $rendering = false;

		/**
		 * Constructor.
		 *
		 * @access public
		 * @param string $shortcode         The shortcode we want to add.
		 * @param string $shortcode_attr_id The shortcode attribute-ID.
		 * @param string $classname         The shortcode's CSS classname.
		 * @param string $content_filter    The filter-name we want to apply using apply_filters.
		 * @since 1.0
		 */
		public function __construct( $shortcode, $shortcode_attr_id, $classname, $content_filter ) {
			parent::__construct();

			add_shortcode( $shortcode, [ $this, 'render' ] );

			$this->shortcode_attr_id   = $shortcode_attr_id;
			$this->shortcode_classname = $classname;
			$this->shortcode_name      = $shortcode;
			$this->content_filter      = $content_filter;

			add_filter( "fusion_attr_{$this->shortcode_attr_id}", [ $this, 'attr' ] );
			add_filter( "fusion_attr_{$this->shortcode_attr_id}-wrapper", [ $this, 'wrapper_attr' ] );
			add_filter( "fusion_attr_{$this->shortcode_attr_id}-anchor", [ $this, 'anchor_attr' ] );
			add_filter( "fusion_attr_{$this->shortcode_attr_id}-hover-wrapper", [ $this, 'hover_wrapper_attr' ] );
			add_filter( "fusion_attr_{$this->shortcode_attr_id}-hover-inner-wrapper", [ $this, 'hover_inner_wrapper_attr' ] );
			add_filter( "fusion_attr_{$this->shortcode_attr_id}-empty-col-bg-img", [ $this, 'empty_col_bg_img_attr' ] );
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @static
		 * @access public
		 * @since 2.2
		 * @return array An array of classes, one for parent columns, one for child columns.
		 */
		final public static function get_instance() {
			$called_class = get_called_class();

			if ( ! isset( self::$instances[ $called_class ] ) ) {
				self::$instances[ $called_class ] = new $called_class();
			}

			return self::$instances[ $called_class ];
		}


		/**
		 * Resets previous spacing.
		 *
		 * @static
		 * @access public
		 * @since 3.0
		 */
		public function reset_previous_spacing() {
			$this->previous_spacing = '';
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
				'hide_on_mobile'              => fusion_builder_default_visibility( 'string' ),
				'sticky_display'              => '',
				'class'                       => '',
				'id'                          => '',
				'background_color'            => '',
				'gradient_start_color'        => '',
				'gradient_end_color'          => '',
				'gradient_start_position'     => '0',
				'gradient_end_position'       => '100',
				'gradient_type'               => 'linear',
				'radial_direction'            => 'center',
				'linear_angle'                => '180',
				'background_image'            => '',
				'background_image_id'         => '',
				'background_position'         => 'left top',
				'background_repeat'           => 'no-repeat',
				'background_blend_mode'       => 'none',
				'border_color'                => '',
				'border_position'             => 'all',
				'border_radius_bottom_left'   => '',
				'border_radius_bottom_right'  => '',
				'border_radius_top_left'      => '',
				'border_radius_top_right'     => '',
				'border_size'                 => '', // Backwards-compatibility.
				'border_sizes_top'            => '',
				'border_sizes_bottom'         => '',
				'border_sizes_left'           => '',
				'border_sizes_right'          => '',
				'border_style'                => '',
				'box_shadow'                  => '',
				'box_shadow_blur'             => '',
				'box_shadow_color'            => '',
				'box_shadow_horizontal'       => '',
				'box_shadow_spread'           => '',
				'box_shadow_style'            => '',
				'box_shadow_vertical'         => '',
				'overflow'                    => '',
				'column_tag'                  => 'div',
				'z_index'                     => '',
				'z_index_hover'               => '',

				'link_attributes'             => '',

				// Width.
				'type'                        => '1_3',
				'type_medium'                 => '',
				'type_small'                  => '',

				// Margins.
				'margin_top'                  => $fusion_settings->get( 'col_margin', 'top' ),
				'margin_bottom'               => $fusion_settings->get( 'col_margin', 'bottom' ),
				'margin_top_medium'           => '',
				'margin_bottom_medium'        => '',
				'margin_top_small'            => '',
				'margin_bottom_small'         => '',

				// Spacing.
				'spacing'                     => '4%',
				'spacing_left'                => '',
				'spacing_right'               => '',
				'spacing_left_medium'         => '',
				'spacing_right_medium'        => '',
				'spacing_left_small'          => '',
				'spacing_right_small'         => '',

				// Padding.
				'padding_top'                 => '0px',
				'padding_right'               => '0px',
				'padding_bottom'              => '0px',
				'padding_left'                => '0px',
				'padding_top_medium'          => '',
				'padding_right_medium'        => '',
				'padding_bottom_medium'       => '',
				'padding_left_medium'         => '',
				'padding_top_small'           => '',
				'padding_right_small'         => '',
				'padding_bottom_small'        => '',
				'padding_left_small'          => '',

				'animation_type'              => '',
				'animation_direction'         => 'left',
				'animation_speed'             => '0.3',
				'animation_offset'            => $fusion_settings->get( 'animation_offset' ),
				'link'                        => '',
				'link_description'            => '',
				'target'                      => '_self',
				'hover_type'                  => 'none',

				// Render logics.
				'render_logics'               => '',

				// Lazy Loading.
				'skip_lazy_load'              => '',

				// Legacy only.
				'min_height'                  => '',
				'center_content'              => 'no',

				// Filters.
				'filter_hue'                  => '0',
				'filter_saturation'           => '100',
				'filter_brightness'           => '100',
				'filter_contrast'             => '100',
				'filter_invert'               => '0',
				'filter_sepia'                => '0',
				'filter_opacity'              => '100',
				'filter_blur'                 => '0',
				'filter_hue_hover'            => '0',
				'filter_saturation_hover'     => '100',
				'filter_brightness_hover'     => '100',
				'filter_contrast_hover'       => '100',
				'filter_invert_hover'         => '0',
				'filter_sepia_hover'          => '0',
				'filter_opacity_hover'        => '100',
				'filter_blur_hover'           => '0',

				// transform.
				'transform_scale_x'           => '1',
				'transform_scale_y'           => '1',
				'transform_translate_x'       => '0',
				'transform_translate_y'       => '0',
				'transform_rotate'            => '0',
				'transform_skew_x'            => '0',
				'transform_skew_y'            => '0',
				'transform_scale_x_hover'     => '1',
				'transform_scale_y_hover'     => '1',
				'transform_translate_x_hover' => '0',
				'transform_translate_y_hover' => '0',
				'transform_rotate_hover'      => '0',
				'transform_skew_x_hover'      => '0',
				'transform_skew_y_hover'      => '0',
				'transform_origin'            => '',

				// Flex.
				'align_self'                  => 'auto',
				'order'                       => '',
				'order_medium'                => '',
				'order_small'                 => '',
				'align_content'               => 'flex-start',
				'valign_content'              => 'flex-start',
				'content_wrap'                => 'wrap',
				'content_layout'              => 'column',

				// Sticky.
				'sticky'                      => 'off',
				'sticky_devices'              => fusion_builder_default_visibility( 'string' ),
				'sticky_offset'               => 0,

				// Hidden ones.
				'padding'                     => '',
				'row_column_index'            => '',
				'last'                        => '',
			];
		}

		/**
		 * Maps settings to param variables.
		 *
		 * @static
		 * @access public
		 * @since 2.0.0
		 * @return array
		 */
		public static function settings_to_params() {
			return [
				'animation_offset'   => 'animation_offset',
				'col_margin[top]'    => 'margin_top',
				'col_margin[bottom]' => 'margin_bottom',
				'flex_align_items'   => 'align_self',
			];
		}

		/**
		 * Used to set any other variables for use on front-end editor template.
		 *
		 * @static
		 * @access public
		 * @since 2.0.0
		 * @return array
		 */
		public static function get_element_extras() {
			$fusion_settings = awb_get_fusion_settings();
			return [
				'col_margin'        => $fusion_settings->get( 'col_margin' ),
				'visibility_large'  => $fusion_settings->get( 'visibility_large' ),
				'visibility_medium' => $fusion_settings->get( 'visibility_medium' ),
				'visibility_small'  => $fusion_settings->get( 'visibility_small' ),
				'col_width_medium'  => $fusion_settings->get( 'col_width_medium' ),
				'col_width_small'   => $fusion_settings->get( 'col_width_small' ),
			];
		}

		/**
		 * Maps settings to extra variables.
		 *
		 * @static
		 * @access public
		 * @since 2.0.0
		 * @return array
		 */
		public static function settings_to_extras() {

			return [
				'col_margin'        => 'col_margin',
				'visibility_large'  => 'visibility_large',
				'visibility_medium' => 'visibility_medium',
				'visibility_small'  => 'visibility_small',
				'col_width_medium'  => 'col_width_medium',
				'col_width_small'   => 'col_width_small',
			];
		}

		/**
		 * Render the shortcode
		 *
		 * @access public
		 * @since 1.0
		 * @param  array  $atts    Shortcode parameters.
		 * @param  string $content Content between shortcode.
		 * @return string          HTML output.
		 */
		public function render( $atts, $content = '' ) {
			global $fusion_col_type;

			if ( ! Fusion_Builder_Conditional_Render_Helper::should_render( $atts ) ) {
				return;
			}

			$this->set_active_status();

			$this->styles = '';

			$this->set_args( $atts );

			$this->validate_args();

			$this->set_extra_args();

			if ( FusionBuilder()->post_card_data['is_rendering'] && 'fusion_builder_column' === $this->shortcode_name ) {
				$this->post_card_args();
			} else {
				$this->legacy_inherit( $atts );

				// Works out aspects of the column in relation to others.
				$this->set_column_map_data();
			}

			// Sets styles which are used on multiple elements.
			$this->set_shared_styles();

			// Sets attributes necessary for lazy load.
			$this->set_lazy_load_data();

			// Check empty dims bg image.
			$this->args['empty_dims_bg_img'] = isset( $this->args['background_data'] ) && is_array( $this->args['background_data'] ) && '' === $this->args['background_data']['width'] && '' === $this->args['background_data']['height'] ? true : false;

			// Sets styles for responsive options.
			if ( $this->args['flex'] ) {
				$this->set_responsive_column_styles();
			} else {
				// Enqueue legacy scripts.
				$this->add_legacy_scripts();
			}

			$column_tag = apply_filters( 'fusion_column_tag', $this->args['column_tag'], $this->args );

			// Retrieve column content.
			$column_content                    = fusion_builder_fix_shortcodes( $content );
			$placeholder_img                   = '';
			$this->args['empty_column_bg_img'] = false;

			if ( isset( $this->args['background_data'] ) && is_array( $this->args['background_data'] ) ) {
				$placeholder_img                   = '<img ' . FusionBuilder::attributes( $this->shortcode_attr_id . '-empty-col-bg-img' ) . '>';
				$this->args['empty_column_bg_img'] = empty( $column_content ) ? true : $this->args['empty_column_bg_img'];
			}

			$output  = '<' . $column_tag . ' ' . FusionBuilder::attributes( $this->shortcode_attr_id ) . '>';
			$output .= '<div ' . FusionBuilder::attributes( $this->shortcode_attr_id . '-wrapper' ) . '>';

			// Add opening tag for extra centering wrapper.
			if ( 'yes' === $this->args['center_content'] && ! $this->args['flex'] ) {
				$output .= '<div class="fusion-column-content-centered"><div class="fusion-column-content">';
			}

			// The actual column content.
			$output .= ! empty( $column_content ) ? $column_content : $placeholder_img;

			// Closing tags for centering wrapper.
			if ( 'yes' === $this->args['center_content'] && ! $this->args['flex'] ) {
				$output .= '</div></div>';
			}

			// Clearing tag.
			if ( ! $this->args['flex'] ) {
				$output .= '<div class="fusion-clearfix"></div>';
			}

			// Closing tag for fusion-column-wrapper.
			$output .= '</div>';

			// If we have a hover or link, we need extra markup.
			if ( $this->args['hover_or_link'] ) {

				// Lift up and border radius we need to apply radius to lift up markup.
				if ( '' !== $this->args['border_radius_style'] && 'liftup' === $this->args['hover_type'] ) {
					$this->styles .= '.' . $this->shortcode_classname . '-' . $this->args['column_counter'] . ' .hover-type-liftup:before{' . $this->args['border_radius_style'] . '}';
				}

				// zoom, zoomout or has a link and also has border radius, then set along with overflow.

				$output .= '<span ' . FusionBuilder::attributes( $this->shortcode_attr_id . '-hover-wrapper' ) . '>';

				$tag = ! empty( $this->args['link'] ) ? 'a' : 'span';

				$output .= '<' . $tag . ' ' . FusionBuilder::attributes( $this->shortcode_attr_id . '-anchor' ) . '>';

				$output .= '<span ' . FusionBuilder::attributes( $this->shortcode_attr_id . '-hover-inner-wrapper' ) . '></span>';

				$output .= '</' . $tag . '></span>';
			}

			// Z index hover.
			if ( '' !== $this->args['z_index_hover'] ) {
				$this->styles .= '.' . $this->shortcode_classname . '-' . $this->args['column_counter'] . ':hover {z-index:' . $this->args['z_index_hover'] . ' !important;}';
			}

			if ( '' !== $this->styles ) {
				$output .= '<style type="text/css">' . $this->styles . '</style>';
			}

			// Add filter styles.
			$selector     = 'fusion_builder_column' === $this->shortcode_name ? '.fusion-builder-column-' : '.fusion-builder-nested-column-';
			$selector     = $selector . $this->args['column_counter'];
			$filter_style = Fusion_Builder_Filter_Helper::get_filter_style_element( $this->args, $selector );
			if ( '' !== $filter_style ) {
				$output .= $filter_style;
			}

			$transform_style = Fusion_Builder_Transform_Helper::get_transform_style_element( $this->args, $selector );
			if ( '' !== $transform_style ) {
				$output .= $transform_style;
			}

			$output  = apply_filters( 'fusion_column_before_close', $output, $this->shortcode_name );
			$output .= '</' . $column_tag . '>';

			$nested = $this->is_nested;

			$content = apply_filters( $this->content_filter, do_shortcode( $output ), $atts );

			$fusion_col_type['type'] = null;

			// If we are rendering a top level column, then set render to false.
			if ( ! $nested ) {
				$this->rendering = false;
			}

			// End of nested, restore parent args in case it is not finished rendering.
			if ( $this->is_nested ) {
				if ( $this->parent_args ) {
					$this->args = $this->parent_args;
				}
			}

			// If this is an inner column, set its parent state to inactive, so that the real parent column can take over again.
			if ( 'fusion_builder_column_inner' === $this->shortcode_name ) {
				fusion_builder_column_inner()->set_inactive();
			}

			$this->on_render();

			return $content;
		}

		/**
		 * Sets active status and turns off on the other.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_active_status() {
			// If this is a parent column, set the nested column active state to false because at this point main column is element parent.
			if ( 'fusion_builder_column' === $this->shortcode_name ) {
				fusion_builder_column_inner()->set_inactive();
			} elseif ( 'fusion_builder_column_inner' === $this->shortcode_name ) {
				fusion_builder_column()->set_inactive();
			}
			$this->active_parent = true;

			// If we are inside another container render, then we count nested.
			$rendering = $this->rendering;
			if ( ! $this->rendering ) {
				$this->rendering   = true;
				$this->parent_args = false;
			} else {

				// If not set yet, set args as parent args.
				if ( ! $this->parent_args ) {
					$this->parent_args = $this->args;
				}
			}

			$this->is_nested = $rendering ? true : false;
		}

		/**
		 * Sets attributes for the outer-most element.
		 *
		 * @access public
		 * @since 3.0
		 * @return array
		 */
		public function attr() {
			$attr = [
				'class' => "fusion-layout-column {$this->shortcode_name} {$this->shortcode_classname}-" . $this->args['column_counter'],
				'style' => '',
			];

			// Sticky column.
			if ( 'on' === $this->args['sticky'] ) {
				$attr['class'] .= ' awb-sticky';

				if ( '' !== $this->args['sticky_offset'] && 0 !== $this->args['sticky_offset'] ) {

					// If its not a selector then get value and set to css variable.
					if ( false === strpos( $this->args['sticky_offset'], '.' ) && false === strpos( $this->args['sticky_offset'], '#' ) ) {
						$attr['style'] .= '--awb-sticky-offset:' . fusion_library()->sanitize->get_value_with_unit( $this->args['sticky_offset'] ) . ';';
					} else {
						$attr['data-sticky-offset'] = (string) $this->args['sticky_offset'];
					}
				}

				if ( '' !== $this->args['sticky_devices'] ) {
					$this->args['sticky_devices'] = str_replace( '-visibility', '', $this->args['sticky_devices'] );
					$sticky_devices               = explode( ',', (string) $this->args['sticky_devices'] );
					foreach ( $sticky_devices as $sticky_device ) {
						$attr['class'] .= ' awb-sticky-' . str_replace( ' ', '', $sticky_device );
					}
				}
			}

			if ( ! empty( $this->args['type'] ) && ( ! FusionBuilder()->post_card_data['is_rendering'] || 'fusion_builder_column' !== $this->shortcode_name ) ) {
				$type = esc_attr( $this->args['type'] );
				if ( false !== strpos( $type, '_' ) ) {
					$attr['class'] .= ' ' . $this->shortcode_name . '_' . $type;
					$attr['class'] .= ' ' . $type;
				}
			}

			if ( $this->args['z_index'] ) {
				$attr['style'] .= 'z-index:' . $this->args['z_index'] . ';';
			}

			// Flexbox column.
			if ( $this->args['flex'] ) {
				$attr['class'] .= ' fusion-flex-column';

				// Alignment of column vertically.
				if ( 'auto' !== $this->args['align_self'] ) {
					$attr['class'] .= ' fusion-flex-align-self-' . esc_attr( $this->args['align_self'] );
				}
			} elseif ( ! FusionBuilder()->post_card_data['is_rendering'] || 'fusion_builder_column' !== $this->shortcode_name ) {
				// Class for the specific size of column.
				if ( '' !== $this->args['size_class'] ) {
					$attr['class'] .= ' ' . $this->args['size_class'];
				}

				// First column.
				if ( $this->args['first'] ) {
					$attr['class'] .= ' fusion-column-first';
				}

				// Last column.
				if ( $this->args['last'] ) {
					$attr['class'] .= ' fusion-column-last';
				}

				// Special calcs for spacing.
				if ( '' !== $this->args['spacing_classes'] ) {
					$attr['class'] .= $this->args['spacing_classes'];
				}

				// Column spacing style, margin and width.
				if ( '' !== $this->args['column_spacing_style'] ) {
					$attr['style'] .= $this->args['column_spacing_style'];
				}

				// Top margin.
				if ( '' !== $this->args['margin_top'] ) {
					$attr['style'] .= 'margin-top:' . esc_attr( $this->args['margin_top'] ) . ';';
				}

				// Bottom margin.
				if ( '' !== $this->args['margin_bottom'] ) {
					$attr['style'] .= 'margin-bottom:' . esc_attr( $this->args['margin_bottom'] ) . ';';
				}
			}

			// Custom CSS class.
			if ( ! empty( $this->args['class'] ) ) {
				$attr['class'] .= ' ' . $this->args['class'];
			}

			// Min height for newly created columns by the converter.
			if ( 'none' === $this->args['min_height'] ) {
				$attr['class'] .= ' fusion-column-no-min-height';
			}

			// Visibility classes.
			$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

			$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

			// Hover type or link.
			if ( $this->args['hover_or_link'] ) {
				$attr['class'] .= ' fusion-column-inner-bg-wrapper';
			}

			// TODO: check why it is looking at animation type/class.
			if ( $this->args['hover_or_link'] && ! empty( $this->args['animation_type'] ) && 'liftup' === $this->args['hover_type'] ) {
				$attr['class'] .= ' fusion-column-hover-type-liftup';
			}

			// Lift up and border.
			if ( 'liftup' === $this->args['hover_type'] && '' !== $this->args['border_style'] ) {
				$attr['class'] .= ' fusion-column-liftup-border';
			}

			if ( $this->args['animation_type'] ) {
				$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
			}

			if ( ! empty( $this->args['id'] ) ) {
				$attr['id'] = esc_attr( $this->args['id'] );
			}

			return $attr;
		}

		/**
		 * Sets attributes for content wrapper element.
		 *
		 * @access public
		 * @since 3.0
		 * @return array
		 */
		public function wrapper_attr() {
			$attr = [
				'class' => 'fusion-column-wrapper',
				'style' => '',
			];

			// Check if we have a hover, markup is different so need extra.
			if ( ! $this->args['hover_or_link'] ) {
				// $bg_color_fix . '" ' . $lazy_bg
				$attr['style'] .= $this->args['wrapper_style_bg'];
			}

			// Image URL for empty dimension calculations.
			$attr['data-bg-url'] = $this->args['background_image'];

			// Adds lazy loading attributes if necessary.
			$attr = $this->add_lazy_attributes( $attr, 'wrapper' );

			if ( ! $this->args['hover_or_link'] ) {
				$attr['style'] .= $this->args['background_color_style'];
			}

			// Border.
			if ( '' !== $this->args['border_full_style'] ) {
				$attr['style'] .= $this->args['border_full_style'];
			}

			// Border radius.
			if ( '' !== $this->args['border_radius_style'] ) {
				$attr['style'] .= $this->args['border_radius_style'];
			}

			// Overflow.
			if ( '' !== $this->args['border_radius_style'] && '' === $this->args['overflow'] ) {
				$attr['style'] .= 'overflow:hidden;';
			}

			if ( '' !== $this->args['overflow'] ) {
				$attr['style'] .= 'overflow:' . $this->args['overflow'] . ';';
			}

			// Box shadow.
			if ( 'liftup' !== $this->args['hover_type'] && '' !== $this->args['box_shadow_styles'] ) {
				$attr['style'] .= $this->args['box_shadow_styles'];
				$attr['class'] .= ' fusion-column-has-shadow'; // Move this to appropriate.
			}

			// Padding.
			if ( ! empty( $this->args['padding'] ) ) {
				$attr['style'] .= 'padding: ' . $this->args['padding'] . ';';
			}

			// Flex.
			if ( ! empty( $this->args['flex'] ) ) {
				$attr['class'] .= ' fusion-flex-justify-content-' . $this->args['align_content'];
				$attr['class'] .= ' fusion-content-layout-' . $this->args['content_layout'];

				if ( 'row' === $this->args['content_layout'] && 'flex-start' !== $this->args['valign_content'] ) {
					$attr['class'] .= ' fusion-flex-align-items-' . $this->args['valign_content'];
				}
				if ( 'wrap' !== $this->args['content_wrap'] ) {
					$attr['class'] .= ' fusion-content-' . $this->args['content_wrap'];
				}
			} else {
				$attr['class'] .= ' fusion-flex-column-wrapper-legacy';
			}

			// Empty column with BG Image.
			if ( $this->args['empty_column_bg_img'] ) {
				$attr['class'] .= ' fusion-empty-column-bg-image';
			}

			// Empty BG Image Dims.
			if ( $this->args['empty_dims_bg_img'] ) {
				$attr['class'] .= ' fusion-empty-dims-bg-image';
			}

			// Escape attributes before return.
			$attr['class'] = esc_attr( $attr['class'] );

			if ( '' !== $attr['style'] ) {
				$attr['style'] = esc_attr( $attr['style'] );
			}

			return $attr;
		}

		/**
		 * Attributes for the hover wrapper element.
		 *
		 * @access public
		 * @since 3.0
		 * @return array
		 */
		public function hover_wrapper_attr() {
			$attr = [
				'class' => 'fusion-column-inner-bg hover-type-' . $this->args['hover_type'],
				'style' => '',
			];

			if ( 'liftup' !== $this->args['hover_type'] && ( 'zoomin' === $this->args['hover_type'] || 'zoomout' === $this->args['hover_type'] || ! empty( $this->args['link'] ) ) && '' !== $this->args['border_radius_style'] ) {
				$attr['style'] .= 'overflow:hidden;' . $this->args['border_radius_style'] . ';';
			}

			return $attr;
		}

		/**
		 * Attributes for the hover inner wrapper element.
		 *
		 * @access public
		 * @since 3.0
		 * @return array
		 */
		public function hover_inner_wrapper_attr() {
			$attr = [
				'class' => 'fusion-column-inner-bg-image',
				'style' => '',
			];

			// Background style.
			if ( '' !== $this->args['wrapper_style_bg'] ) {
				$attr['style'] .= $this->args['wrapper_style_bg'];
			}

			// Adds lazy loading attributes if necessary.
			$attr = $this->add_lazy_attributes( $attr );

			return $attr;
		}

		/**
		 * Sets atrributes for the anchor element.
		 *
		 * @access public
		 * @since 3.0
		 * @return array
		 */
		public function anchor_attr() {
			$attr = [
				'class' => 'fusion-column-anchor',
			];

			if ( ! empty( $this->args['link'] ) ) {
				$attr['href'] = $this->args['link'];
			}

			if ( '_blank' === $this->args['target'] ) {
				$attr['rel']    = 'noopener noreferrer';
				$attr['target'] = '_blank';
			} elseif ( 'lightbox' === $this->args['target'] ) {
				$attr['data-rel'] = 'iLightbox';
			}

			if ( ! empty( $this->args['link_description'] ) ) {
				$attr['aria-label'] = esc_attr( $this->args['link_description'] );
			}

			$attr = fusion_get_link_attributes( $this->args, $attr );

			return $attr;
		}

		/**
		 * Sets attributes for pseudo image placeholder.
		 *
		 * @access public
		 * @since 3.4
		 * @return array
		 */
		public function empty_col_bg_img_attr() {

			$attr = [
				'class' => 'fusion-empty-dims-img-placeholder',
				'alt'   => '',
				'src'   => call_user_func_array( [ fusion_library()->images, 'get_lazy_placeholder' ], [ $this->args['background_data']['width'], $this->args['background_data']['height'] ] ),
			];

			if ( 'stretch' === $this->args['flex_align_items'] && in_array( $this->args['align_self'], [ 'auto', 'stretch' ], true ) ) {
				$attr['class'] .= ' fusion-no-large-visibility';
				$width_key      = 'type_medium';

				if ( '' !== $this->args[ $width_key ] && 'auto' !== $this->args[ $width_key ] && 0 < (float) $this->args[ $width_key ] ) {
					if ( 100 > ( (float) $this->args[ $width_key ] * 100 ) ) {
						$attr['class'] .= ' fusion-no-medium-visibility';
					}
				}
			}

			return $attr;
		}

		/**
		 * Sets different styles to the object so they can be accessed from anywhere.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_shared_styles() {
			$this->set_background_color_style();
			$this->set_background_image();
			$this->set_border_style();
			$this->set_border_radius_style();
			$this->set_box_shadow_style();
			$this->set_wrapper_style_bg();
		}

		/**
		 * Sets styles necessary for column responsiveness.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_responsive_column_styles() {
			$fusion_settings  = awb_get_fusion_settings();
			$post_card_column = FusionBuilder()->post_card_data['is_rendering'] && 'fusion_builder_column' === $this->shortcode_name;

			foreach ( [ 'large', 'medium', 'small' ] as $size ) {
				$column_styles         = '';
				$column_wrapper_styles = '';
				$hover_wrapper_styles  = '';

				// Width and order come from post cards element.
				if ( ! $post_card_column ) {
					// Width.
					$width_key = 'large' === $size ? 'column_size' : 'type_' . $size;
					if ( '' !== $this->args[ $width_key ] && 'auto' !== $this->args[ $width_key ] && 0 < (float) $this->args[ $width_key ] ) {
						$column_styles .= 'width:' . esc_attr( fusion_i18_float_to_string( (float) $this->args[ $width_key ] * 100 ) ) . '% !important;';
					} elseif ( 'auto' === $this->args[ $width_key ] ) {
						$column_styles .= 'width: auto;';
					}

					// Order.
					$order_key = 'large' === $size ? 'order' : 'order_' . $size;
					if ( '' !== $this->args[ $order_key ] ) {
						$column_styles .= 'order : ' . (int) $this->args[ $order_key ] . ';';
					}
				}

				foreach ( [ 'top', 'right', 'bottom', 'left' ] as $direction ) {

					// Padding.
					$padding_key = 'large' === $size ? 'padding_' . $direction : 'padding_' . $direction . '_' . $size;
					if ( '' !== $this->args[ $padding_key ] ) {
						$column_wrapper_styles .= 'padding-' . $direction . ' : ' . $this->args[ $padding_key ] . ' !important;';
					}

					// Margin comes from post cards column and row spacing.
					if ( ! $post_card_column ) {
						// Margin.
						$key_base    = 'left' === $direction || 'right' === $direction ? 'spacing' : 'margin';
						$spacing_key = 'large' === $size ? $key_base . '_' . $direction : $key_base . '_' . $direction . '_' . $size;

						if ( '' !== $this->args[ $spacing_key ] ) {

							// If its top and bottom margin, add to outer column element.
							if ( 'margin' === $key_base ) {
								$column_styles .= 'margin-' . $direction . ' : ' . $this->args[ $spacing_key ] . ';';
							} else {
								$column_wrapper_styles .= 'margin-' . $direction . ' : ' . $this->args[ $spacing_key ] . ';';
							}
							if ( $this->args['hover_or_link'] && 'margin' !== $key_base ) {
								$hover_wrapper_styles .= 'margin-' . $direction . ':' . $this->args[ $spacing_key ] . ';';
							}
						}
					}
				}

				if ( '' === $column_styles && '' === $column_wrapper_styles ) {
					continue;
				}

				// Wrap CSS selectors.
				if ( '' !== $column_styles ) {
					$column_styles = '.fusion-body .' . $this->shortcode_classname . '-' . $this->args['column_counter'] . '{' . $column_styles . '}';
				}
				if ( '' !== $column_wrapper_styles ) {
					$column_wrapper_styles = '.' . $this->shortcode_classname . '-' . $this->args['column_counter'] . ' > .fusion-column-wrapper {' . $column_wrapper_styles . '}';
				}
				if ( '' !== $hover_wrapper_styles ) {
					$hover_wrapper_styles = '.fusion-flex-container .fusion-row .' . $this->shortcode_classname . '-' . $this->args['column_counter'] . ' > .fusion-column-inner-bg { ' . $hover_wrapper_styles . ' }';
				}

				// Large styles, no wrapping needed.
				if ( 'large' === $size ) {
					$this->styles .= $column_styles . $column_wrapper_styles . $hover_wrapper_styles;
				} else {
					// Medium and Small size screen styles.
					$this->styles .= '@media only screen and (max-width:' . $fusion_settings->get( 'visibility_' . $size ) . 'px) {' . $column_styles . $column_wrapper_styles . $hover_wrapper_styles . '}';

					// Empty dims style.
					if ( 'medium' === $size && $this->args['empty_dims_bg_img'] ) {
						$this->styles .= '@media only screen and (min-width:' . $fusion_settings->get( 'visibility_' . $size ) . 'px) {.fusion-body .' . $this->shortcode_classname . '-' . $this->args['column_counter'] . ' .fusion-empty-dims-img-placeholder { display: none; } }';
					}
				}
			}
		}

		/**
		 * Checks if the column is rendering elements in flex or block.
		 *
		 * @access public
		 * @since 3.0
		 * @return bool
		 */
		public function is_flex_rendering() {
			return ! empty( $this->args['flex'] ) && 'block' !== $this->args['content_layout'];
		}

		/**
		 * Sets style for background color depending on image and alpha.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_background_color_style() {
			$background_color_style = '';
			if ( ! empty( $this->args['background_color'] ) && ( empty( $this->args['background_image'] ) || 0 !== $this->args['alpha_background_color'] ) ) {
				$background_color_style = 'background-color:' . esc_attr( $this->args['background_color'] ) . ';';
			}
			$this->args['background_color_style'] = $background_color_style;
		}

		/**
		 * Sets style for background image
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_background_image() {
			$background_image_style = '';
			$bg_title               = '';
			$background_data        = '';
			if ( ! empty( $this->args['background_image'] ) ) {

				$background_data         = fusion_library()->images->get_attachment_data_by_helper( $this->args['background_image_id'], $this->args['background_image'] );
				$background_image_style .= $this->args['lazy_load'] ? '' : "background-image: url('" . $this->args['background_image'] . "');";
				$bg_title                = $background_data['title'];
			}

			if ( $this->is_gradient_color() ) {
				$background_image_style .= 'background-image: ' . Fusion_Builder_Gradient_Helper::get_gradient_string( $this->args, 'column' );
			}

			if ( ! empty( $this->args['background_position'] ) ) {
				$background_image_style .= 'background-position:' . $this->args['background_position'] . ';';
			}

			if ( 'none' !== $this->args['background_blend_mode'] ) {
				$background_image_style .= 'background-blend-mode: ' . $this->args['background_blend_mode'] . ';';
			}

			if ( ! empty( $this->args['background_repeat'] ) ) {
				$background_image_style .= 'background-repeat:' . $this->args['background_repeat'] . ';';

				if ( 'no-repeat' === $this->args['background_repeat'] ) {
					$background_image_style .= '-webkit-background-size:cover;-moz-background-size:cover;-o-background-size:cover;background-size:cover;';
				}
			}

			$this->args['bg_title']               = $bg_title;
			$this->args['background_image_style'] = esc_attr( $background_image_style );
			$this->args['background_data']        = $background_data;
		}

		/**
		 * Sets style for border if set.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_border_style() {
			$this->args['border_full_style'] = '';

			if ( '' === $this->args['border_sizes_top'] && '' === $this->args['border_sizes_bottom'] && '' === $this->args['border_sizes_left'] && '' === $this->args['border_sizes_right'] ) {

				// Backwards-compatibility.
				if ( $this->args['border_color'] && $this->args['border_size'] && $this->args['border_style'] ) {
					$this->args['border_full_style'] = 'border' . $this->args['border_position'] . ':' . $this->args['border_size'] . ' ' . $this->args['border_style'] . ' ' . $this->args['border_color'] . ';';
				}
			} else {
				$border = [
					'top'    => '0',
					'bottom' => '0',
					'left'   => '0',
					'right'  => '0',
				];

				// Border-sizes.
				if ( '' !== $this->args['border_sizes_top'] ) {
					$border['top'] = esc_attr( $this->args['border_sizes_top'] );
				}
				if ( '' !== $this->args['border_sizes_bottom'] ) {
					$border['bottom'] = esc_attr( $this->args['border_sizes_bottom'] );
				}
				if ( '' !== $this->args['border_sizes_left'] ) {
					$border['left'] = esc_attr( $this->args['border_sizes_left'] );
				}
				if ( '' !== $this->args['border_sizes_right'] ) {
					$border['right'] = esc_attr( $this->args['border_sizes_right'] );
				}

				// Border-styles.
				if ( ! empty( $this->args['border_color'] ) ) {
					$this->args['border_full_style'] .= "border-width: {$border['top']} {$border['right']} {$border['bottom']} {$border['left']};";
					$this->args['border_full_style'] .= 'border-color:' . esc_attr( $this->args['border_color'] ) . ';';

					// Border-style.
					if ( ! empty( $this->args['border_style'] ) ) {
						$this->args['border_full_style'] .= 'border-style:' . esc_attr( $this->args['border_style'] ) . ';';
					}
				}
			}
		}

		/**
		 * Sets style for border radius if set.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_border_radius_style() {
			$this->args['border_radius_style'] = '';
			if ( $this->args['border_radius'] ) {
				$this->args['border_radius_style'] = 'border-radius:' . esc_attr( $this->args['border_radius'] ) . ';';
			}
		}

		/**
		 * Sets style for box shadow if set.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_box_shadow_style() {
			$this->args['box_shadow_styles'] = '';
			if ( 'yes' === $this->args['box_shadow'] ) {
				$box_shadow_styles               = Fusion_Builder_Box_Shadow_Helper::get_box_shadow_styles(
					[
						'box_shadow_horizontal' => $this->args['box_shadow_horizontal'],
						'box_shadow_vertical'   => $this->args['box_shadow_vertical'],
						'box_shadow_blur'       => $this->args['box_shadow_blur'],
						'box_shadow_spread'     => $this->args['box_shadow_spread'],
						'box_shadow_color'      => $this->args['box_shadow_color'],
						'box_shadow_style'      => $this->args['box_shadow_style'],
					]
				);
				$this->args['box_shadow_styles'] = 'box-shadow:' . esc_attr( trim( $box_shadow_styles ) ) . ';';
			}
		}

		/**
		 * Sets lazy load attributes if set.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_lazy_load_data() {
			$this->args['lazy_class'] = '';
			$this->args['lazy_bg']    = [];
			$this->args['lazy_dims']  = [];

			if ( $this->args['lazy_load'] ) {
				$this->args['lazy_bg']    = [
					'data-bg' => $this->args['background_image'],
				];
				$this->args['lazy_class'] = ' lazyload';

				if ( isset( $this->args['background_data'] ) && is_array( $this->args['background_data'] ) ) {
					$this->args['lazy_dims'] = [
						'data-bg-height' => $this->args['background_data']['height'],
						'data-bg-width'  => $this->args['background_data']['width'],
					];
				}

				if ( $this->is_gradient_color() ) {
					$this->args['lazy_bg']['data-bg-gradient'] = Fusion_Builder_Gradient_Helper::get_gradient_string( $this->args );
				}
			}
		}

		/**
		 * Sets styles for wrapper background.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_wrapper_style_bg() {

			$this->args['wrapper_style_bg'] = '';

			// Background color.
			if ( $this->args['hover_or_link'] ) {
				$this->args['wrapper_style_bg'] .= $this->args['background_color_style'];
			}

			// Background image.
			$this->args['wrapper_style_bg'] .= $this->args['background_image_style'];

			// Border.
			if ( 'liftup' === $this->args['hover_type'] && '' !== $this->args['border_full_style'] ) {
				$this->args['wrapper_style_bg'] .= $this->args['border_full_style'];
			}

			// Border radius.
			if ( '' !== $this->args['border_radius_style'] ) {
				$this->args['wrapper_style_bg'] .= $this->args['border_radius_style'];
			}

			// Box shadow.
			if ( 'liftup' === $this->args['hover_type'] && '' !== $this->args['box_shadow_styles'] ) {
				$this->args['wrapper_style_bg'] .= $this->args['box_shadow_styles'];
			}
		}

		/**
		 * Sets the args from the attributes.
		 *
		 * @access public
		 * @since 3.0
		 * @param array $atts Element attributes.
		 * @return void
		 */
		public function set_args( $atts ) {
			$this->atts = $atts;

			$defaults = self::get_element_defaults();

			// We have old value but not new, split into new and use as defaults.
			if ( ! empty( $atts['padding'] ) ) {
				$padding_values = explode( ' ', $atts['padding'] );
				if ( is_array( $padding_values ) ) {
					$padding_count = count( $padding_values );

					if ( 1 === $padding_count ) {
						$defaults['padding_top']    = $padding_values[0];
						$defaults['padding_right']  = $padding_values[0];
						$defaults['padding_bottom'] = $padding_values[0];
						$defaults['padding_left']   = $padding_values[0];
					} elseif ( 2 === $padding_count ) {
						$defaults['padding_top']    = $padding_values[0];
						$defaults['padding_bottom'] = $padding_values[0];
						$defaults['padding_right']  = $padding_values[1];
						$defaults['padding_left']   = $padding_values[1];
					} elseif ( 3 === $padding_count ) {
						$defaults['padding_top']    = $padding_values[0];
						$defaults['padding_right']  = $padding_values[1];
						$defaults['padding_left']   = $padding_values[1];
						$defaults['padding_bottom'] = $padding_values[2];
					} elseif ( 4 === $padding_count ) {
						$defaults['padding_top']    = $padding_values[0];
						$defaults['padding_right']  = $padding_values[1];
						$defaults['padding_bottom'] = $padding_values[2];
						$defaults['padding_left']   = $padding_values[3];
					}
				}
			}

			$padding_values           = [];
			$padding_values['top']    = ( isset( $atts['padding_top'] ) && '' !== $atts['padding_top'] ) ? $atts['padding_top'] : $defaults['padding_top'];
			$padding_values['right']  = ( isset( $atts['padding_right'] ) && '' !== $atts['padding_right'] ) ? $atts['padding_right'] : $defaults['padding_right'];
			$padding_values['bottom'] = ( isset( $atts['padding_bottom'] ) && '' !== $atts['padding_bottom'] ) ? $atts['padding_bottom'] : $defaults['padding_bottom'];
			$padding_values['left']   = ( isset( $atts['padding_left'] ) && '' !== $atts['padding_left'] ) ? $atts['padding_left'] : $defaults['padding_left'];

			$defaults['padding'] = implode( ' ', $padding_values );

			$args = FusionBuilder::set_shortcode_defaults( $defaults, $atts, $this->shortcode_name );

			$this->args = $args;
		}

		/**
		 * Legacy inherit mode.  When old containers are now using flex.
		 *
		 * @access public
		 * @since 3.0
		 * @param array $atts The attributes set on element.
		 * @return void
		 */
		public function legacy_inherit( $atts ) {
			// No align self set but ignore equal heights is on.
			if ( ! isset( $atts['align_self'] ) && isset( $atts['min_height'] ) && 'none' === $atts['min_height'] ) {
				$this->args['align_self'] = 'flex-start';
			}
			// No align content set, but legacy center_content is on.
			if ( ! isset( $atts['align_content'] ) && isset( $atts['center_content'] ) && 'yes' === $atts['center_content'] ) {
				$this->args['align_content'] = 'center';
			}
			// Flex type, old spacing set, new spacing not and column map available.
			if ( $this->args['flex'] && ! isset( $atts['spacing_left'] ) && ! isset( $atts['spacing_right'] ) && isset( $this->args['column_map'][ $this->shortcode_name ] ) && ! empty( $this->args['column_map'][ $this->shortcode_name ] ) ) {

				$non_global_column_array       = $this->args['column_map'][ $this->shortcode_name ];
				$current_row                   = false;
				$current_column_type           = false;
				$current_row_number_of_columns = false;

				// Set the row and column index as well as the column type for the current column.
				if ( '' !== $this->args['row_column_index'] ) {
					$this->args['row_column_index'] = explode( '_', $this->args['row_column_index'] );
					$current_row_index              = $this->args['row_column_index'][0];
					$current_column_index           = $this->args['row_column_index'][1];
					if ( isset( $non_global_column_array ) && isset( $non_global_column_array[ $current_row_index ] ) ) {
						$current_row = $non_global_column_array[ $current_row_index ];
					}

					if ( isset( $current_row ) && is_array( $current_row ) ) {
						$current_row_number_of_columns = count( $current_row );
						$current_column_type           = $current_row[ $current_column_index ][1];
					}
				}

				// Fallback values to values.
				if ( 'yes' === $this->args['spacing'] ) {
					$this->args['spacing'] = '4%';
				} elseif ( 'no' === $this->args['spacing'] ) {
					$this->args['spacing'] = '0px';
				}

				$first = false;
				$last  = false;
				if ( $current_column_type ) {
					if ( false !== strpos( $current_column_type, 'first' ) ) {
						$first = true;
					}
					if ( false !== strpos( $current_column_type, 'last' ) ) {
						$last = true;
					}
				}

				// Half spacing for this column.
				$weighted_spacing = $this->get_weighted_spacing( $this->args['spacing'], $current_row_number_of_columns );

				// Use what is set as right spacing.
				if ( ! $last ) {
					$this->args['spacing_right'] = $weighted_spacing;
				}

				// Check right spacing of previous column.
				if ( '' !== $this->previous_spacing && ! $first ) {
					$this->args['spacing_left'] = $this->get_weighted_spacing( $this->previous_spacing, $current_row_number_of_columns );
				}

				// Set previous to current half spacing.
				$this->previous_spacing = $this->args['spacing'];
			}
		}

		/**
		 * Validate the arguments into correct format.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function validate_args() {
			$fusion_settings = awb_get_fusion_settings();

			// Alpha related checks.
			$this->args['alpha_background_color']     = 1;
			$this->args['alpha_gradient_start_color'] = 1;
			$this->args['alpha_gradient_end_color']   = 1;
			if ( class_exists( 'Fusion_Color' ) ) {
				$this->args['alpha_background_color']     = Fusion_Color::new_color( $this->args['background_color'] )->alpha;
				$this->args['alpha_gradient_start_color'] = Fusion_Color::new_color( $this->args['gradient_start_color'] )->alpha;
				$this->args['alpha_gradient_end_color']   = Fusion_Color::new_color( $this->args['gradient_end_color'] )->alpha;
			}

			// If no blend mode is defined, check if we should set to overlay.
			if ( ! isset( $this->atts['background_blend_mode'] ) && 1 > $this->args['alpha_background_color'] && 0 !== $this->args['alpha_background_color'] && ! $this->is_gradient_color() && ! empty( $this->args['background_image'] ) ) {
				$this->args['background_blend_mode'] = 'overlay';
			}

			$this->args['margin_bottom'] = '' === $this->args['margin_bottom'] ? $fusion_settings->get( 'col_margin', 'bottom' ) : fusion_library()->sanitize->get_value_with_unit( $this->args['margin_bottom'] );
			$this->args['margin_top']    = '' === $this->args['margin_top'] ? $fusion_settings->get( 'col_margin', 'top' ) : fusion_library()->sanitize->get_value_with_unit( $this->args['margin_top'] );

			if ( $this->args['border_size'] ) {
				$this->args['border_size'] = FusionBuilder::validate_shortcode_attr_value( $this->args['border_size'], 'px' );
			}

			if ( $this->args['padding'] ) {
				$this->args['padding'] = fusion_library()->sanitize->get_value_with_unit( $this->args['padding'] );
			}

			if ( $this->args['border_sizes_top'] ) {
				$this->args['border_sizes_top'] = fusion_library()->sanitize->get_value_with_unit( $this->args['border_sizes_top'] );
			}

			if ( $this->args['border_sizes_bottom'] ) {
				$this->args['border_sizes_bottom'] = fusion_library()->sanitize->get_value_with_unit( $this->args['border_sizes_bottom'] );
			}

			if ( $this->args['border_sizes_left'] ) {
				$this->args['border_sizes_left'] = fusion_library()->sanitize->get_value_with_unit( $this->args['border_sizes_left'] );
			}

			if ( $this->args['border_sizes_right'] ) {
				$this->args['border_sizes_right'] = fusion_library()->sanitize->get_value_with_unit( $this->args['border_sizes_right'] );
			}

			$this->args['border_radius_top_left']     = $this->args['border_radius_top_left'] ? fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_top_left'] ) : '0px';
			$this->args['border_radius_top_right']    = $this->args['border_radius_top_right'] ? fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_top_right'] ) : '0px';
			$this->args['border_radius_bottom_right'] = $this->args['border_radius_bottom_right'] ? fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_bottom_right'] ) : '0px';
			$this->args['border_radius_bottom_left']  = $this->args['border_radius_bottom_left'] ? fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_bottom_left'] ) : '0px';
			$this->args['border_radius']              = $this->args['border_radius_top_left'] . ' ' . $this->args['border_radius_top_right'] . ' ' . $this->args['border_radius_bottom_right'] . ' ' . $this->args['border_radius_bottom_left'];
			$this->args['border_radius']              = ( '0px 0px 0px 0px' === $this->args['border_radius'] ) ? '' : $this->args['border_radius'];

			$this->args['border_position'] = ( 'all' !== $this->args['border_position'] ) ? '-' . $this->args['border_position'] : '';
		}

		/**
		 * Sets the extra args.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_extra_args() {
			$fusion_settings = awb_get_fusion_settings();

			$this->args['flex']             = false;
			$this->args['column_map']       = [
				'fusion_builder_column'       => [],
				'fusion_builder_column_inner' => [],
			];
			$this->args['column_spacing']   = false;
			$this->args['flex_align_items'] = false;
			if ( function_exists( 'fusion_builder_container' ) ) {
				if ( fusion_builder_container()->rendering ) {
					$this->args['flex']             = fusion_builder_container()->is_flex();
					$this->args['column_spacing']   = fusion_builder_container()->get_column_spacing();
					$this->args['flex_align_items'] = fusion_builder_container()->get_column_alignment();
					if ( ! empty( fusion_builder_container()->column_map ) ) {
						$this->args['column_map'] = fusion_builder_container()->column_map;
					}
				} elseif ( null === fusion_builder_container()->data && defined( 'STUDIO_VERSION' ) ) {
					$this->args['flex'] = true;
				}
			}

			// If there is no map of columns, we must use fallback method like 4.0.3.
			if ( ! isset( $this->args['column_map'][ $this->shortcode_name ] ) || empty( $this->args['column_map'][ $this->shortcode_name ] ) && 'no' !== $this->args['spacing'] ) {
				$this->args['spacing'] = 'yes';
			}

			$this->args['column_counter'] = $this->column_counter++;

			// If not ajax this will be column_counter.
			if ( isset( $_POST['cid'] ) ) { // phpcs:disable WordPress.Security.NonceVerification.Missing
				$this->set_element_id( $this->args['column_counter'] );
				$this->args['column_counter'] = $this->element_id;
			}

			// Fixes selectors duplication for terms & conditions section on checkout page.
			if ( class_exists( 'WooCommerce' ) && is_checkout() && fusion_library()->get_page_id() !== intval( get_option( 'woocommerce_checkout_page_id' ) ) ) {
				$this->set_element_id( $this->column_counter . '_' . fusion_library()->get_page_id() );
				$this->args['column_counter'] = $this->element_id;
			}

			// Whether lazy load should be used.
			$this->args['lazy_load'] = ( 'avada' === $fusion_settings->get( 'lazy_load' ) && ! is_feed() && 'skip' !== $this->args['skip_lazy_load'] ) ? true : false;
			if ( ! $this->args['background_image'] || '' === $this->args['background_image'] ) {
				$this->args['lazy_load'] = false;
			}

			$this->args['old_spacing_values'] = [
				'yes',
				'Yes',
				'No',
				'no',
			];

			$this->args['hover_or_link'] = ( 'none' !== $this->args['hover_type'] && ! empty( $this->args['hover_type'] ) ) || ! empty( $this->args['link'] );
		}

		/**
		 * Overrides args for post card rendering
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function post_card_args() {
			$defaults = self::get_element_defaults();

			$reset = [
				'align_self',
				'order',
				'order_medium',
				'order_small',
				'hide_on_mobile',
				'spacing',
				'spacing_left',
				'spacing_right',
				'spacing_left_medium',
				'spacing_right_medium',
				'spacing_left_small',
				'spacing_right_small',
				'type',
				'type_medium',
				'type_small',
				'margin_top_medium',
				'margin_bottom_medium',
				'margin_top_small',
				'margin_bottom_small',
			];

			foreach ( $reset as $reset_key ) {
				$this->args[ $reset_key ] = $defaults[ $reset_key ];
			}

			$this->args['margin_top']       = '0px';
			$this->args['margin_bottom']    = '0px';
			$this->args['flex']             = true;
			$this->args['column_spacing']   = false;
			$this->args['flex_align_items'] = false;
		}

		/**
		 * Works out the column size based on args.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_column_size() {
			$size_class = '';

			switch ( $this->args['type'] ) {
				case '1_1':
					$size_class = 'fusion-one-full';
					break;
				case '1_4':
					$size_class = 'fusion-one-fourth';
					break;
				case '3_4':
					$size_class = 'fusion-three-fourth';
					break;
				case '1_2':
					$size_class = 'fusion-one-half';
					break;
				case '1_3':
					$size_class = 'fusion-one-third';
					break;
				case '2_3':
					$size_class = 'fusion-two-third';
					break;
				case '1_5':
					$size_class = 'fusion-one-fifth';
					break;
				case '2_5':
					$size_class = 'fusion-two-fifth';
					break;
				case '3_5':
					$size_class = 'fusion-three-fifth';
					break;
				case '4_5':
					$size_class = 'fusion-four-fifth';
					break;
				case '5_6':
					$size_class = 'fusion-five-sixth';
					break;
				case '1_6':
					$size_class = 'fusion-one-sixth';
					break;
			}

			$this->args['column_size'] = $this->validate_column_size( $this->args['type'] );
			$this->args['size_class']  = $size_class;
		}

		/**
		 * Gets column size as a decimal.
		 *
		 * @access public
		 * @since 3.0
		 * @param mixed $column_size Size of column.
		 * @return mixed
		 */
		public function validate_column_size( $column_size = '1_3' ) {

			// Fractional value.
			if ( false !== strpos( $column_size, '_' ) ) {
				$fractions = explode( '_', $column_size );
				return (float) $fractions[0] / (float) $fractions[1];
			}

			// Greater than one, assume percentage and divide by 100.
			if ( 1 < (float) $column_size ) {
				return (float) $column_size / 100;
			}

			return $column_size;
		}

		/**
		 * Works out if column is first or last in a row.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_first_last() {
			global $columns, $inner_columns;

			// Globals depending on column type.
			$non_global_columns = 'fusion_builder_column' === $this->shortcode_name ? $columns : $inner_columns;

			$first = false;
			$last  = false;

			// If not using a fallback, work out first and last from the generated array.
			if ( ! $this->args['fallback'] ) {
				if ( false !== strpos( $this->args['current_column_type'], 'first' ) ) {
					$first = true;
				}

				if ( false !== strpos( $this->args['current_column_type'], 'last' ) ) {
					$last = true;

				}
			} else {
				if ( ! $non_global_columns ) {
					$non_global_columns = 0;
				}

				if ( 0 === $non_global_columns ) {
					$first = true;
				}
				$non_global_columns += $this->args['column_size'];
				if ( 0.990 < $non_global_columns ) {
					$last               = true;
					$non_global_columns = 0;
				}
				if ( 1 < $non_global_columns ) {
					$last               = false;
					$non_global_columns = $this->args['column_size'];
				}
			}

			if ( 'fusion_builder_column' === $this->shortcode_name ) {
				$columns = $non_global_columns;
			} else {
				$inner_columns = $non_global_columns;
			}
			$this->args['last']  = $last;
			$this->args['first'] = $first;
		}

		/**
		 * Half a value passed in
		 *
		 * @access public
		 * @since 3.0
		 * @param string $value The value you want to get half of.
		 * @return string
		 */
		public function get_half_spacing( $value ) {
			if ( 0 === (float) $value ) {
				return $value;
			}
			$unitless_spacing = (float) filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
			$unitless_half    = $unitless_spacing / 2;
			return str_replace( $unitless_spacing, $unitless_half, $value );
		}

		/**
		 * Spacing weighted for col width.
		 *
		 * @access public
		 * @since 3.0
		 * @param string $value The value you want to get half of.
		 * @param mixed  $columns Number of columns in the row.
		 * @return string
		 */
		public function get_weighted_spacing( $value, $columns = false ) {
			if ( ! isset( $this->args['column_size'] ) ) {
				$this->set_column_size();
			}
			if ( 0 === (float) $value ) {
				return $value;
			}
			$unitless_spacing = (float) filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

			// If we only have two columns, weight the distribution, otherwise just half.
			if ( false !== $columns && 3 > $columns ) {
				$unitless_weighted = $unitless_spacing * (float) $this->args['column_size'];
			} else {
				$unitless_weighted = $unitless_spacing / 2;
			}
			return str_replace( $unitless_spacing, $unitless_weighted, $value );
		}

		/**
		 * Set column spacing data.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_column_map_data() {
			global $fusion_col_type;

			// Globals depending on column type.
			$non_global_column_array = $this->args['column_map'][ $this->shortcode_name ];

			$current_row                   = false;
			$current_column_type           = false;
			$current_row_number_of_columns = false;

			$fusion_col_type = [
				'padding' => $this->args['padding'],
				'type'    => $this->args['type'],
			];

			// If we are flex, we do not have a column map.
			if ( $this->args['flex'] ) {
				$this->set_column_size();

				$fusion_settings = awb_get_fusion_settings();

				// Medium default to TO or validate if set.
				if ( empty( $this->args['type_medium'] ) ) {
					$this->args['type_medium'] = 'inherit_from_large' === $fusion_settings->get( 'col_width_medium' ) ? $this->args['column_size'] : 1;
				} else {
					$this->args['type_medium'] = $this->validate_column_size( $this->args['type_medium'] );
				}

				// Small default to TO or validate if set.
				if ( empty( $this->args['type_small'] ) ) {
					$this->args['type_small'] = 'inherit_from_large' === $fusion_settings->get( 'col_width_small' ) ? $this->args['column_size'] : 1;
				} else {
					$this->args['type_small'] = $this->validate_column_size( $this->args['type_small'] );
				}

				// Not full width medium, inherit from large if set.
				if ( ! empty( $this->args['type_medium'] ) && 1 !== $this->args['type_medium'] ) {
					if ( '' === $this->args['spacing_left_medium'] ) {
						$this->args['spacing_left_medium'] = $this->args['spacing_left'];
					}
					if ( '' === $this->args['spacing_right_medium'] ) {
						$this->args['spacing_right_medium'] = $this->args['spacing_right'];
					}
				}

				// Not full width small, inherit from medium or large if set.
				if ( ! empty( $this->args['type_small'] ) && 1 !== $this->args['type_small'] ) {
					if ( '' === $this->args['spacing_left_small'] ) {
						$this->args['spacing_left_small'] = '' !== $this->args['spacing_left_medium'] ? $this->args['spacing_left_medium'] : $this->args['spacing_left'];
					}
					if ( '' === $this->args['spacing_right_small'] ) {
						$this->args['spacing_right_small'] = '' !== $this->args['spacing_right_medium'] ? $this->args['spacing_right_medium'] : $this->args['spacing_right'];
					}
				}

				// Half the spacing on container.
				$half_spacing = $this->get_half_spacing( $this->args['column_spacing'] );

				// Validate left and right margins that are set.
				foreach ( [ 'large', 'medium', 'small' ] as $width ) {

					// Need to calc for each because column width may be different and that changes things.
					$width_key    = 'large' === $width ? 'column_size' : 'type_' . $width;
					$empty_offset = $this->validate_percentage_margin( $half_spacing, $this->args[ $width_key ] );

					// We have a value, validate it, else we use the empty offset.
					$spacing_left_key = 'large' === $width ? 'spacing_left' : 'spacing_left_' . $width;
					if ( '' !== $this->args[ $spacing_left_key ] ) {
						$this->args[ $spacing_left_key ] = $this->validate_percentage_margin( $this->args[ $spacing_left_key ], $this->args[ $width_key ] );
					} else {
						$this->args[ $spacing_left_key ] = $empty_offset;

					}

					$spacing_right_key = 'large' === $width ? 'spacing_right' : 'spacing_right_' . $width;
					if ( '' !== $this->args[ $spacing_right_key ] ) {
						$this->args[ $spacing_right_key ] = $this->validate_percentage_margin( $this->args[ $spacing_right_key ], $this->args[ $width_key ] );
					} else {
						$this->args[ $spacing_right_key ] = $empty_offset;
					}

					$fusion_col_type['margin'][ $width ] = [
						'left'  => $this->args[ $spacing_left_key ],
						'right' => $this->args[ $spacing_right_key ],
					];
				}
			} else {
				// Set the row and column index as well as the column type for the current column.
				if ( '' !== $this->args['row_column_index'] ) {
					$this->args['row_column_index'] = explode( '_', $this->args['row_column_index'] );
					$current_row_index              = $this->args['row_column_index'][0];
					$current_column_index           = $this->args['row_column_index'][1];
					if ( isset( $non_global_column_array ) && isset( $non_global_column_array[ $current_row_index ] ) ) {
						$current_row = $non_global_column_array[ $current_row_index ];
					}

					if ( isset( $current_row ) && is_array( $current_row ) ) {
						$current_row_number_of_columns = count( $current_row );
						$current_column_type           = $current_row[ $current_column_index ][1];
					}
				}

				// Check if all columns are yes, no, or empty.
				$fallback = true;
				if ( is_array( $current_row ) && 0 !== count( $non_global_column_array ) ) {
					foreach ( $current_row as $column_space ) {
						if ( isset( $column_space[0] ) && ! in_array( $column_space[0], $this->args['old_spacing_values'], true ) ) {
							$fallback = false;
						}
					}
				}

				// Fix the spacing values.
				if ( is_array( $current_row ) ) {
					foreach ( $current_row as $key => $value ) {
						if ( '' === $value[0] || 'yes' === $value[0] ) {
							$current_row[ $key ] = '4%';
						} elseif ( 'no' === $value[0] ) {
							unset( $current_row[ $key ] );
						} else {
							$current_row[ $key ] = $value[0];
						}
					}
				}

				$fusion_col_type['spacings']                 = $current_row;
				$this->args['fallback']                      = $fallback;
				$this->args['current_row']                   = $current_row;
				$this->args['current_column_type']           = $current_column_type;
				$this->args['current_row_number_of_columns'] = $current_row_number_of_columns;

				$this->set_column_size();
				$this->set_first_last();
				$this->set_spacing_styling();
			}
		}

		/**
		 * Works out the column spacing and creates styles for it.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_spacing_styling() {
			$map_old_spacing = [
				'0.1667' => '13.3333%',
				'0.8333' => '82.6666%',
				'0.2'    => '16.8%',
				'0.4'    => '37.6%',
				'0.6'    => '58.4%',
				'0.8'    => '79.2%',
				'0.25'   => '22%',
				'0.75'   => '74%',
				'0.3333' => '30.6666%',
				'0.6667' => '65.3333%',
				'0.5'    => '48%',
				'1'      => '100%',
			];

			$this->args['column_spacing_style'] = '';
			$this->args['spacing_classes']      = '';

			$rounded   = round( (float) $this->args['column_size'], 4 );
			$old_width = isset( $map_old_spacing[ fusion_i18_float_to_string( $rounded ) ] ) ? $map_old_spacing[ fusion_i18_float_to_string( $rounded ) ] : ( $rounded * 100 ) . '%';

			// Spacing.  If using fallback and spacing is no then ignore and just use full % width.
			if ( isset( $this->args['spacing'] ) && ! ( in_array( $this->args['spacing'], [ '0px', 'no' ], true ) && $this->args['fallback'] ) ) {
				$width = fusion_i18_float_to_string( $this->args['column_size'] * 100 ) . '%';

				if ( 'yes' === $this->args['spacing'] || '' === $this->args['spacing'] ) {
					$this->args['spacing'] = '4%';
				} elseif ( 'no' === $this->args['spacing'] ) {
					$this->args['spacing'] = '0px';
				}
				$this->args['spacing'] = fusion_library()->sanitize->get_value_with_unit( esc_attr( $this->args['spacing'] ) );

				if ( 0 === filter_var( $this->args['spacing'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) ) {
					$this->args['spacing_classes'] = ' fusion-spacing-no';
				}

				$width_offset = '';
				if ( is_array( $this->args['current_row'] ) ) {
					$width_offset = '( ( ' . implode( ' + ', $this->args['current_row'] ) . ' ) * ' . fusion_i18_float_to_string( $this->args['column_size'] ) . ' ) ';
				}

				if ( ! $this->args['last'] && ! ( $this->args['fallback'] && '0px' === $this->args['spacing'] ) ) {
					$spacing_direction = 'right';
					if ( is_rtl() ) {
						$spacing_direction = 'left';
					}
					if ( ! $this->args['fallback'] ) {
						$this->args['column_spacing_style'] = 'width:' . $width . ';width:calc(' . $width . ' - ' . $width_offset . ');margin-' . $spacing_direction . ': ' . $this->args['spacing'] . ';';
					} else {
						$this->args['column_spacing_style'] = 'width:' . $old_width . '; margin-' . $spacing_direction . ': ' . $this->args['spacing'] . ';';
					}
				} elseif ( isset( $this->args['current_row_number_of_columns'] ) && false !== $this->args['current_row_number_of_columns'] && 1 < $this->args['current_row_number_of_columns'] ) {
					if ( ! $this->args['fallback'] ) {
						$this->args['column_spacing_style'] = 'width:' . $width . ';width:calc(' . $width . ' - ' . $width_offset . ');';
					} elseif ( '0px' !== $this->args['spacing'] ) {
						$this->args['column_spacing_style'] = 'width:' . $old_width . ';';
					} else {
						$this->args['column_spacing_style'] = 'width:' . $width . ';';
					}
				} elseif ( ! isset( $this->args['current_row_number_of_columns'] ) || false === $this->args['current_row_number_of_columns'] ) {
					$this->args['column_spacing_style'] = 'width:' . $old_width . ';';
				}
			}
		}

		/**
		 * Checks if column has a gradient color.
		 *
		 * @access public
		 * @since 3.0
		 * @return bool
		 */
		public function is_gradient_color() {
			return ( ! empty( $this->args['gradient_start_color'] ) && 0 !== $this->args['alpha_gradient_start_color'] ) || ( ! empty( $this->args['gradient_end_color'] ) && 0 !== $this->args['alpha_gradient_end_color'] );
		}

		/**
		 * Helper which adds lazy load attributes into existing attributes.
		 *
		 * @access public
		 * @since 3.0
		 * @param array $attr The attributes.
		 * @param array $element The element adding to.
		 * @return array
		 */
		public function add_lazy_attributes( $attr, $element = '' ) {

			// Check if we are using lazy load.
			if ( $this->args['lazy_load'] ) {

				// If its not wrapper and we call this it is always true.  If we have no hover then we also add to wrapper.
				if ( 'wrapper' !== $element || ! $this->args['hover_or_link'] ) {
					$attr['class'] .= ' lazyload';

					// Have background image, set its url.
					if ( ! empty( $this->args['lazy_bg'] ) ) {
						foreach ( $this->args['lazy_bg'] as $key => $value ) {
							$attr[ $key ] = $value;
						}
					}
				}
			}
			return $attr;
		}

		/**
		 * Checks value and returns relative to row.
		 *
		 * @access public
		 * @since 3.0
		 * @param string $value Margin value.
		 * @param mixed  $column_size Column width.
		 * @return string $value Formatted value.
		 */
		public function validate_percentage_margin( $value = '', $column_size = 1 ) {

			// If value is in percentage and not calc, make it relative to container.
			if ( 0 < (float) $column_size && strpos( $value, '%' ) && ! strpos( $value, 'calc' ) ) {
				// If all are in % just work it out.
				if ( strpos( $this->args['column_spacing'], '%' ) && ! strpos( $this->args['column_spacing'], 'calc' ) ) {
					return (float) $value / (float) $column_size / 100 * ( 100 - (float) $this->args['column_spacing'] ) . '%';
				} else {

					// Not all % then we need to use calc.
					return 'calc( ' . ( (float) $value / (float) $column_size / 100 ) . ' * calc( 100% - ' . $this->args['column_spacing'] . ' ) )';
				}
			}
			return $value;
		}

		/**
		 * Set parent state to inactive.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_inactive() {
			$this->active_parent = false;
		}

		/**
		 * Adds settings to element options panel.
		 *
		 * @access public
		 * @since 1.1
		 * @return array $sections Column settings.
		 */
		public function add_options() {

			return [
				'column_shortcode_section' => [
					'label'       => esc_html__( 'Column', 'fusion-builder' ),
					'description' => '',
					'id'          => 'column_shortcode_section',
					'default'     => '',
					'type'        => 'accordion',
					'icon'        => 'fusiona-column',
					'fields'      => [
						'col_margin'       => [
							'label'       => esc_html__( 'Column Margins', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the top/bottom margins for all column sizes.', 'fusion-builder' ),
							'id'          => 'col_margin',
							'type'        => 'spacing',
							'choices'     => [
								'top'    => true,
								'bottom' => true,
							],
							'transport'   => 'postMessage',
							'default'     => [
								'top'    => '0px',
								'bottom' => '20px',
							],
						],
						'col_spacing'      => [
							'label'       => esc_html__( 'Column Spacing', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the column spacing between one column to the next.', 'fusion-builder' ),
							'id'          => 'col_spacing',
							'default'     => '4%',
							'type'        => 'dimension',
							'transport'   => 'postMessage',
						],
						'col_width_medium' => [
							'label'       => esc_html__( 'Column Width On Medium Screens', 'fusion-builder' ),
							'description' => esc_html__( 'Controls how columns should be displayed on medium sized screens.', 'fusion-builder' ),
							'id'          => 'col_width_medium',
							'default'     => 'inherit_from_large',
							'type'        => 'radio-buttonset',
							'transport'   => 'postMessage',
							'choices'     => [
								'inherit_from_large' => esc_html__( 'Inherit From Large', 'fusion-builder' ),
								'1_1'                => esc_html__( 'Full Width ', 'fusion-builder' ),
							],
						],
						'col_width_small'  => [
							'label'       => esc_html__( 'Column Width On Small Screens', 'fusion-builder' ),
							'description' => esc_html__( 'Controls how columns should be displayed on small sized screens.', 'fusion-builder' ),
							'id'          => 'col_width_small',
							'default'     => '1_1',
							'type'        => 'radio-buttonset',
							'transport'   => 'postMessage',
							'choices'     => [
								'inherit_from_large' => esc_html__( 'Inherit From Large', 'fusion-builder' ),
								'1_1'                => esc_html__( 'Full Width ', 'fusion-builder' ),
							],
						],
					],
				],
			];
		}

		/**
		 * Sets the necessary scripts.
		 *
		 * @access public
		 * @since 1.1
		 * @return void
		 */
		public function add_scripts() {

			$fusion_settings = awb_get_fusion_settings();

			$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

			if ( $is_builder ) {
				Fusion_Dynamic_JS::localize_script(
					'fusion-column-bg-image',
					'fusionBgImageVars',
					[
						'content_break_point' => intval( $fusion_settings->get( 'content_break_point' ) ),
					]
				);
				Fusion_Dynamic_JS::enqueue_script(
					'fusion-column-bg-image',
					FusionBuilder::$js_folder_url . '/general/fusion-column-bg-image.js',
					FusionBuilder::$js_folder_path . '/general/fusion-column-bg-image.js',
					[ 'jquery', 'modernizr', 'fusion-equal-heights' ],
					'1',
					true
				);
			}
			Fusion_Dynamic_JS::enqueue_script(
				'fusion-column',
				FusionBuilder::$js_folder_url . '/general/fusion-column.js',
				FusionBuilder::$js_folder_path . '/general/fusion-column.js',
				[ 'jquery', 'fusion-animations' ],
				'1',
				true
			);

			// Legacy column script.
			Fusion_Dynamic_JS::register_script(
				'fusion-column-legacy',
				FusionBuilder::$js_folder_url . '/general/fusion-column-legacy.js',
				FusionBuilder::$js_folder_path . '/general/fusion-column-legacy.js',
				[ 'jquery', 'fusion-animations', 'fusion-equal-heights' ],
				'1',
				true
			);
		}

		/**
		 * Sets the legacy scripts.
		 *
		 * @access public
		 * @since 3.4
		 * @return void
		 */
		public function add_legacy_scripts() {
			Fusion_Dynamic_JS::enqueue_script( 'fusion-column-legacy' );
		}
	}

	if ( ! function_exists( 'fusion_get_column_subparam_map' ) ) {

		/**
		 * Return an array of column sub param maps..
		 *
		 * @since 3.0
		 * @return array
		 */
		function fusion_get_column_subparam_map() {
			return [
				'spacing_left'          => 'dimension_spacing',
				'spacing_right'         => 'dimension_spacing',
				'spacing_left_medium'   => 'dimension_spacing_medium',
				'spacing_right_medium'  => 'dimension_spacing_medium',
				'spacing_left_small'    => 'dimension_spacing_small',
				'spacing_right_small'   => 'dimension_spacing_small',
				'margin_top'            => 'dimension_margin',
				'margin_bottom'         => 'dimension_margin',
				'margin_top_medium'     => 'dimension_margin_medium',
				'margin_bottom_medium'  => 'dimension_margin_medium',
				'margin_top_small'      => 'dimension_margin_small',
				'margin_bottom_small'   => 'dimension_margin_small',
				'padding_top'           => 'padding',
				'padding_right'         => 'padding',
				'padding_bottom'        => 'padding',
				'padding_left'          => 'padding',
				'padding_top_medium'    => 'padding_medium',
				'padding_right_medium'  => 'padding_medium',
				'padding_bottom_medium' => 'padding_medium',
				'padding_left_medium'   => 'padding_medium',
				'padding_top_small'     => 'padding_small',
				'padding_right_small'   => 'padding_small',
				'padding_bottom_small'  => 'padding_small',
				'padding_left_small'    => 'padding_small',

			];
		}
	}
	if ( ! function_exists( 'fusion_get_column_params' ) ) {

		/**
		 * Return an array of column parameters.
		 *
		 * @since 3.0
		 * @return array
		 */
		function fusion_get_column_params() {
			$fusion_settings = awb_get_fusion_settings();

			return [
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
					'description' => esc_attr__( 'Defines how the column should align itself within the container. Overrides what is set on the container.', 'fusion-builder' ),
					'param_name'  => 'align_self',
					'default'     => 'auto',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
					'value'       => [
						'auto'       => esc_attr__( 'Default', 'fusion-builder' ),
						'flex-start' => esc_attr__( 'Flex Start', 'fusion-builder' ),
						'center'     => esc_attr__( 'Center', 'fusion-builder' ),
						'flex-end'   => esc_attr__( 'Flex End', 'fusion-builder' ),
						'stretch'    => esc_attr__( 'Stretch', 'fusion-builder' ),
					],
					'icons'       => [
						'auto'       => '<span class="fusiona-cog"></span>',
						'flex-start' => '<span class="fusiona-align-top-columns"></span>',
						'center'     => '<span class="fusiona-align-center-columns"></span>',
						'flex-end'   => '<span class="fusiona-align-bottom-columns"></span>',
						'stretch'    => '<span class="fusiona-full-height"></span>',
					],
					'grid_layout' => true,
					'back_icons'  => true,
					'dependency'  => [
						[
							'element'  => 'fusion_builder_container',
							'param'    => 'type',
							'value'    => 'flex',
							'operator' => '==',
						],
					],
					'callback'    => [
						'function' => 'fusion_update_flex_column',
						'args'     => [
							'selector' => '.fusion-layout-column',
						],
					],
				],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Content Layout', 'fusion-builder' ),
					'description' => esc_attr__( 'Defines how the column content should be positioned.  If block is selected it will not use flex positioning and will instead allow floated elements.', 'fusion-builder' ),
					'param_name'  => 'content_layout',
					'default'     => 'column',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
					'value'       => [
						'column' => esc_attr__( 'Column', 'fusion-builder' ),
						'row'    => esc_attr__( 'Row', 'fusion-builder' ),
						'block'  => esc_attr__( 'Block', 'fusion-builder' ),
					],
					'dependency'  => [
						[
							'element'  => 'fusion_builder_container',
							'param'    => 'type',
							'value'    => 'flex',
							'operator' => '==',
						],
					],
					'callback'    => [
						'function' => 'fusion_update_flex_elements',
					],
				],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Content Alignment', 'fusion-builder' ),
					'description' => esc_attr__( 'Defines how the column content should align. Works only with full height columns.', 'fusion-builder' ),
					'param_name'  => 'align_content',
					'default'     => 'flex-start',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
					'value'       => [
						'flex-start'    => esc_attr__( 'Flex Start', 'fusion-builder' ),
						'center'        => esc_attr__( 'Center', 'fusion-builder' ),
						'flex-end'      => esc_attr__( 'Flex End', 'fusion-builder' ),
						'space-between' => esc_attr__( 'Space Between', 'fusion-builder' ),
						'space-around'  => esc_attr__( 'Space Around', 'fusion-builder' ),
						'space-evenly'  => esc_attr__( 'Space Evenly', 'fusion-builder' ),
					],
					'icons'       => [
						'flex-start'    => '<span class="fusiona-align-top-vert"></span>',
						'center'        => '<span class="fusiona-align-center-vert"></span>',
						'flex-end'      => '<span class="fusiona-align-bottom-vert"></span>',
						'space-between' => '<span class="fusiona-space-between"></span>',
						'space-around'  => '<span class="fusiona-space-around"></span>',
						'space-evenly'  => '<span class="fusiona-space-evenly"></span>',
					],
					'grid_layout' => true,
					'back_icons'  => true,
					'dependency'  => [
						[
							'element'  => 'fusion_builder_container',
							'param'    => 'type',
							'value'    => 'flex',
							'operator' => '==',
						],
						[
							'element'  => 'content_layout',
							'value'    => 'block',
							'operator' => '!=',
						],
					],
					'callback'    => [
						'function' => 'fusion_update_flex_column',
						'args'     => [
							'selector' => '.fusion-layout-column',
						],
					],
				],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Content Vertical Alignment', 'fusion-builder' ),
					'description' => esc_attr__( 'Defines how the column content should vertically align.', 'fusion-builder' ),
					'param_name'  => 'valign_content',
					'default'     => 'flex-start',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
					'value'       => [
						'flex-start' => esc_attr__( 'Flex Start', 'fusion-builder' ),
						'center'     => esc_attr__( 'Center', 'fusion-builder' ),
						'flex-end'   => esc_attr__( 'Flex End', 'fusion-builder' ),
						'stretch'    => esc_attr__( 'Stretch', 'fusion-builder' ),
					],
					'icons'       => [
						'flex-start' => '<span class="fusiona-align-top-columns"></span>',
						'center'     => '<span class="fusiona-align-center-columns"></span>',
						'flex-end'   => '<span class="fusiona-align-bottom-columns"></span>',
						'stretch'    => '<span class="fusiona-full-height"></span>',
					],
					'grid_layout' => true,
					'back_icons'  => true,
					'dependency'  => [
						[
							'element'  => 'fusion_builder_container',
							'param'    => 'type',
							'value'    => 'flex',
							'operator' => '==',
						],
						[
							'element'  => 'content_layout',
							'value'    => 'row',
							'operator' => '==',
						],
					],
					'callback'    => [
						'function' => 'fusion_update_flex_column',
						'args'     => [
							'selector' => '.fusion-layout-column',
						],
					],
				],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Content Wrap', 'fusion-builder' ),
					'description' => esc_attr__( 'Defines whether elements are forced onto one line or can wrap onto multiple lines.', 'fusion-builder' ),
					'param_name'  => 'content_wrap',
					'default'     => 'wrap',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
					'value'       => [
						'wrap'   => esc_attr__( 'Wrap', 'fusion-builder' ),
						'nowrap' => esc_attr__( 'No Wrap', 'fusion-builder' ),
					],
					'dependency'  => [
						[
							'element'  => 'fusion_builder_container',
							'param'    => 'type',
							'value'    => 'flex',
							'operator' => '==',
						],
						[
							'element'  => 'content_layout',
							'value'    => 'row',
							'operator' => '==',
						],
					],
					'callback'    => [
						'function' => 'fusion_update_flex_column',
						'args'     => [
							'selector' => '.fusion-layout-column',
						],
					],
				],
				[
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the column spacing between one column to the next. Enter value including any valid CSS unit, ex: 4%.', 'fusion-builder' ),
					'param_name'  => 'spacing',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
					'value'       => '',
					'dependency'  => [
						[
							'element'  => 'fusion_builder_container',
							'param'    => 'type',
							'value'    => 'flex',
							'operator' => '!=',
						],
					],
				],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Center Content', 'fusion-builder' ),
					'description' => esc_attr__( 'Set to "Yes" to center the content vertically. Equal heights on the parent container must be turned on.', 'fusion-builder' ),
					'param_name'  => 'center_content',
					'default'     => 'no',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
					'value'       => [
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					],
					'dependency'  => [
						[
							'element'  => 'fusion_builder_container',
							'param'    => 'type',
							'value'    => 'flex',
							'operator' => '!=',
						],
					],
				],
				[
					'type'        => 'select',
					'heading'     => esc_attr__( 'Column HTML Tag', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose column HTML tag, default is div.', 'fusion-builder' ),
					'param_name'  => 'column_tag',
					'value'       => [
						'div'     => 'Default',
						'section' => 'Section',
						'header'  => 'Header',
						'footer'  => 'Footer',
						'main'    => 'Main',
						'article' => 'Article',
						'aside'   => 'Aside',
						'nav'     => 'Nav',
					],
					'default'     => 'div',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
				],
				[
					'type'         => 'link_selector',
					'heading'      => esc_attr__( 'Link URL', 'fusion-builder' ),
					'description'  => __( 'Add the URL the column will link to, ex: http://example.com. <strong>IMPORTANT:</strong> This will disable links on elements inside the column.', 'fusion-builder' ),
					'param_name'   => 'link',
					'value'        => '',
					'dynamic_data' => true,
				],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
					'description' => esc_attr__( '_self = open in same browser tab, _blank = open in new browser tab.', 'fusion-builder' ),
					'param_name'  => 'target',
					'default'     => '_self',
					'value'       => [
						'_self'    => esc_attr__( '_self', 'fusion-builder' ),
						'_blank'   => esc_attr__( '_blank', 'fusion-builder' ),
						'lightbox' => esc_attr__( 'Lightbox', 'fusion-builder' ),
					],
					'dependency'  => [
						[
							'element'  => 'link',
							'value'    => '',
							'operator' => '!=',
						],
					],
				],
				[
					'type'         => 'textfield',
					'heading'      => esc_attr__( 'Link Description', 'fusion-builder' ),
					'description'  => esc_attr__( 'Add descriptive text to the link to make it easier accessible.', 'fusion-builder' ),
					'param_name'   => 'link_description',
					'value'        => '',
					'dynamic_data' => true,
					'dependency'   => [
						[
							'element'  => 'link',
							'value'    => '',
							'operator' => '!=',
						],
					],
				],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Ignore Equal Heights', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to ignore equal heights on this column if you are using equal heights on the surrounding container.', 'fusion-builder' ),
					'param_name'  => 'min_height',
					'default'     => '',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
					'value'       => [
						'none' => esc_attr__( 'Yes', 'fusion-builder' ),
						''     => esc_attr__( 'No', 'fusion-builder' ),
					],
					'callback'    => [
						'function' => 'fusion_toggle_class',
						'args'     => [
							'classes' => [
								'none' => 'fusion-column-no-min-height',
								''     => '',
							],
						],
					],
					'dependency'  => [
						[
							'element'  => 'fusion_builder_container',
							'param'    => 'type',
							'value'    => 'flex',
							'operator' => '!=',
						],
					],
				],
				[
					'type'        => 'checkbox_button_set',
					'heading'     => esc_attr__( 'Column Visibility', 'fusion-builder' ),
					'param_name'  => 'hide_on_mobile',
					'value'       => fusion_builder_visibility_options( 'full' ),
					'default'     => fusion_builder_default_visibility( 'array' ),
					'description' => esc_attr__( 'Choose to show or hide the column on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
				],
				'fusion_sticky_visibility_placeholder'  => [],
				[
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
					'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					'param_name'  => 'class',
					'value'       => '',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
					'callback'    => [
						'function' => 'fusion_add_class',
					],
				],
				[
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
					'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					'param_name'  => 'id',
					'value'       => '',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
					'callback'    => [
						'function' => 'fusion_add_id',
					],
				],
				[
					'type'        => 'column_width',
					'heading'     => esc_attr__( 'Width', 'fusion-builder' ),
					'description' => esc_attr__( 'Column width on respective display size.', 'fusion-builder' ),
					'param_name'  => 'type',
					'default'     => '0',
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					'responsive'  => [
						'state'             => 'large',
						'additional_states' => [ 'medium', 'small' ],
					],
					'dependency'  => [
						[
							'element'  => 'fusion_builder_container',
							'param'    => 'type',
							'value'    => 'flex',
							'operator' => '==',
						],
					],
				],
				[
					'type'        => 'range',
					'heading'     => esc_attr__( 'Order', 'fusion-builder' ),
					'description' => esc_attr__( 'Column order on respective display size.', 'fusion-builder' ),
					'param_name'  => 'order',
					'value'       => '0',
					'min'         => '0',
					'max'         => '50',
					'step'        => '1',
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					'responsive'  => [
						'state'              => 'large', // Just an example, not needed if main state is excluded.
						'additional_states'  => [ 'medium', 'small' ],
						'exclude_main_state' => true,
					],
					'dependency'  => [
						[
							'element'  => 'fusion_builder_container',
							'param'    => 'type',
							'value'    => 'flex',
							'operator' => '==',
						],
					],
				],
				[
					'type'             => 'dimension',
					'remove_from_atts' => true,
					'heading'          => esc_attr__( 'Column Spacing', 'fusion-builder' ),
					'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 4%. Leave empty to inherit from container or Global Option.', 'fusion-builder' ),
					'param_name'       => 'dimension_spacing',
					'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					'value'            => [
						'spacing_left'  => '',
						'spacing_right' => '',
					],
					'responsive'       => [
						'state'             => 'large',
						'additional_states' => [ 'medium', 'small' ],
					],
					'dependency'       => [
						[
							'element'  => 'fusion_builder_container',
							'param'    => 'type',
							'value'    => 'flex',
							'operator' => '==',
						],
					],
					'callback'         => [
						'function' => 'fusion_column_margin',
					],
				],
				[
					'type'             => 'dimension',
					'remove_from_atts' => true,
					'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
					'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 4%.', 'fusion-builder' ),
					'param_name'       => 'dimension_margin',
					'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					'value'            => [
						'margin_top'    => '',
						'margin_bottom' => '',
					],
					'responsive'       => [
						'state'             => 'large',
						'additional_states' => [ 'medium', 'small' ],
					],
					'callback'         => [
						'function' => 'fusion_column_margin',
					],
				],
				[
					'type'             => 'dimension',
					'remove_from_atts' => true,
					'heading'          => esc_attr__( 'Padding', 'fusion-builder' ),
					'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 4%.', 'fusion-builder' ),
					'param_name'       => 'padding',
					'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					'value'            => [
						'padding_top'    => '',
						'padding_right'  => '',
						'padding_bottom' => '',
						'padding_left'   => '',
					],
					'responsive'       => [
						'state'             => 'large',
						'additional_states' => [ 'medium', 'small' ],
					],
					'callback'         => [
						'function' => 'fusion_column_padding',
					],
				],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Hover Type', 'fusion-builder' ),
					'description' => __( 'Select the hover effect type. <strong>IMPORTANT:</strong> For the effect to be noticeable, you\'ll need a background color/image, and/or a border enabled. This will disable links and hover effects on elements inside the column.', 'fusion-builder' ),
					'param_name'  => 'hover_type',
					'default'     => 'none',
					'value'       => [
						'none'    => esc_attr__( 'None', 'fusion-builder' ),
						'zoomin'  => esc_attr__( 'Zoom In', 'fusion-builder' ),
						'zoomout' => esc_attr__( 'Zoom Out', 'fusion-builder' ),
						'liftup'  => esc_attr__( 'Lift Up', 'fusion-builder' ),
					],
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					'preview'     => [
						'selector' => '.fusion-column-inner-bg',
						'type'     => 'class',
						'toggle'   => 'hover',
					],
				],
				[
					'type'        => 'dimension',
					'heading'     => esc_attr__( 'Column Border Size', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the border size of the column element.', 'fusion-builder' ),
					'param_name'  => 'border_sizes',
					'value'       => [
						'border_sizes_top'    => '',
						'border_sizes_right'  => '',
						'border_sizes_bottom' => '',
						'border_sizes_left'   => '',
					],
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
				],
				[
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Column Border Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the border color of the column element.', 'fusion-builder' ),
					'param_name'  => 'border_color',
					'value'       => '',
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					'or'          => true,
					'dependency'  => [
						[
							'element'  => 'border_sizes_top',
							'value'    => '',
							'operator' => '!=',
						],
						[
							'element'  => 'border_sizes_bottom',
							'value'    => '',
							'operator' => '!=',
						],
						[
							'element'  => 'border_sizes_left',
							'value'    => '',
							'operator' => '!=',
						],
						[
							'element'  => 'border_sizes_right',
							'value'    => '',
							'operator' => '!=',
						],
					],
				],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Border Style', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the border style.', 'fusion-builder' ),
					'param_name'  => 'border_style',
					'default'     => 'solid',
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					'or'          => true,
					'dependency'  => [
						[
							'element'  => 'border_sizes_top',
							'value'    => '',
							'operator' => '!=',
						],
						[
							'element'  => 'border_sizes_bottom',
							'value'    => '',
							'operator' => '!=',
						],
						[
							'element'  => 'border_sizes_left',
							'value'    => '',
							'operator' => '!=',
						],
						[
							'element'  => 'border_sizes_right',
							'value'    => '',
							'operator' => '!=',
						],
					],
					'value'       => [
						'solid'  => esc_attr__( 'Solid', 'fusion-builder' ),
						'dashed' => esc_attr__( 'Dashed', 'fusion-builder' ),
						'dotted' => esc_attr__( 'Dotted', 'fusion-builder' ),
					],
				],
				[
					'type'             => 'dimension',
					'remove_from_atts' => true,
					'heading'          => esc_attr__( 'Border Radius', 'fusion-builder' ),
					'description'      => __( 'Enter values including any valid CSS unit, ex: 10px. <strong>IMPORTANT:</strong> In order to make border radius work in browsers, the overflow CSS rule of the column needs set to hidden. Thus, depending on the setup, some contents might get clipped.', 'fusion-builder' ),
					'param_name'       => 'border_radius',
					'value'            => [
						'border_radius_top_left'     => '',
						'border_radius_top_right'    => '',
						'border_radius_bottom_right' => '',
						'border_radius_bottom_left'  => '',
					],
					'group'            => esc_attr__( 'Design', 'fusion-builder' ),
				],
				'fusion_box_shadow_placeholder'         => [
					'callback' => [
						'function' => 'fusion_update_box_shadow',
						'args'     => [
							'selector' => '.fusion-column-wrapper',
						],
					],
				],
				[
					'type'             => 'subgroup',
					'heading'          => esc_attr__( 'Z Index', 'fusion-builder' ),
					'description'      => esc_attr__( 'Set z-index to control on the stack order of the element.', 'fusion-builder' ),
					'param_name'       => 'z_index_subgroup',
					'default'          => 'regular',
					'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					'remove_from_atts' => true,
					'value'            => [
						'regular' => esc_attr__( 'Regular', 'fusion-builder' ),
						'hover'   => esc_attr__( 'Hover', 'fusion-builder' ),
					],
					'icons'            => [
						'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
						'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
					],
				],
				[
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Z Index Value', 'fusion-builder' ),
					'description' => esc_attr__( 'Value for the z-index CSS property of the column, can be both positive or negative.', 'fusion-builder' ),
					'param_name'  => 'z_index',
					'value'       => '',
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					'subgroup'    => [
						'name' => 'z_index_subgroup',
						'tab'  => 'regular',
					],
				],
				[
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Z Index Value', 'fusion-builder' ),
					'description' => esc_attr__( 'Value for the z-index CSS property of the column, can be both positive or negative.', 'fusion-builder' ),
					'param_name'  => 'z_index_hover',
					'value'       => '',
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					'subgroup'    => [
						'name' => 'z_index_subgroup',
						'tab'  => 'hover',
					],
				],
				[
					'type'        => 'select',
					'heading'     => esc_attr__( 'Overflow', 'fusion-builder' ),
					'description' => esc_attr__( 'Value for column\'s overflow CSS property.', 'fusion-builder' ),
					'param_name'  => 'overflow',
					'value'       => [
						''        => esc_attr__( 'Default', 'fusion-builder' ),
						'visible' => esc_attr__( 'Visible', 'fusion-builder' ),
						'scroll'  => esc_attr__( 'Scroll', 'fusion-builder' ),
						'hidden'  => esc_attr__( 'Hidden', 'fusion-builder' ),
						'auto'    => esc_attr__( 'Auto', 'fusion-builder' ),
					],
					'default'     => '',
					'group'       => esc_attr__( 'Design', 'fusion-builder' ),
				],
				[
					'type'             => 'subgroup',
					'heading'          => esc_attr__( 'Background Type', 'fusion-builder' ),
					'description'      => esc_attr__( 'Use filters to see specific type of content.', 'fusion-builder' ),
					'param_name'       => 'background_type',
					'default'          => 'single',
					'group'            => esc_attr__( 'Background', 'fusion-builder' ),
					'remove_from_atts' => true,
					'value'            => [
						'single'   => esc_attr__( 'Color', 'fusion-builder' ),
						'gradient' => esc_attr__( 'Gradient', 'fusion-builder' ),
						'image'    => esc_attr__( 'Image', 'fusion-builder' ),
					],
					'icons'            => [
						'single'   => '<span class="fusiona-fill-drip-solid" style="font-size:18px;"></span>',
						'gradient' => '<span class="fusiona-gradient-fill" style="font-size:18px;"></span>',
						'image'    => '<span class="fusiona-image" style="font-size:18px;"></span>',
						'video'    => '<span class="fusiona-video" style="font-size:18px;"></span>',
					],
				],
				'fusion_gradient_placeholder'           => [
					'selector' => '.fusion-column-wrapper',
				],
				[
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the background color.', 'fusion-builder' ),
					'param_name'  => 'background_color',
					'value'       => '',
					'group'       => esc_attr__( 'Background', 'fusion-builder' ),
					'subgroup'    => [
						'name' => 'background_type',
						'tab'  => 'single',
					],
				],
				[
					'type'         => 'upload',
					'heading'      => esc_attr__( 'Background Image', 'fusion-builder' ),
					'description'  => esc_attr__( 'Upload an image to display in the background.', 'fusion-builder' ),
					'param_name'   => 'background_image',
					'value'        => '',
					'group'        => esc_attr__( 'Background', 'fusion-builder' ),
					'dynamic_data' => true,
					'subgroup'     => [
						'name' => 'background_type',
						'tab'  => 'image',
					],
				],
				[
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Background Image ID', 'fusion-builder' ),
					'description' => esc_attr__( 'Background Image ID from Media Library.', 'fusion-builder' ),
					'param_name'  => 'background_image_id',
					'value'       => '',
					'hidden'      => true,
				],
				[
					'type'             => 'select',
					'heading'          => esc_attr__( 'Lazy Load', 'fusion-builder' ),
					'description'      => esc_attr__( 'Lazy load which is being used.', 'fusion-builder' ),
					'param_name'       => 'lazy_load',
					'value'            => [
						'avada'     => esc_attr__( 'Avada', 'fusion-builder' ),
						'wordpress' => esc_attr__( 'WordPress', 'fusion-builder' ),
						'none'      => esc_attr__( 'None', 'fusion-builder' ),
					],
					'default'          => $fusion_settings->get( 'lazy_load' ),
					'hidden'           => true,
					'remove_from_atts' => true,
					'group'            => esc_attr__( 'Background', 'fusion-builder' ),
					'subgroup'         => [
						'name' => 'background_type',
						'tab'  => 'image',
					],
				],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Skip Lazy Loading', 'fusion-builder' ),
					'description' => esc_attr__( 'Select whether you want to skip lazy loading on this image or not.', 'fusion-builder' ),
					'param_name'  => 'skip_lazy_load',
					'default'     => '',
					'group'       => esc_attr__( 'Background', 'fusion-builder' ),
					'subgroup'    => [
						'name' => 'background_type',
						'tab'  => 'image',
					],
					'value'       => [
						'skip' => esc_attr__( 'Yes', 'fusion-builder' ),
						''     => esc_attr__( 'No', 'fusion-builder' ),
					],
					'dependency'  => [
						[
							'element'  => 'lazy_load',
							'value'    => 'avada',
							'operator' => '==',
						],
						[
							'element'  => 'background_image',
							'value'    => '',
							'operator' => '!=',
						],
					],
				],
				[
					'type'        => 'select',
					'heading'     => esc_attr__( 'Background Position', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose the position of the background image.', 'fusion-builder' ),
					'param_name'  => 'background_position',
					'default'     => 'left top',
					'group'       => esc_attr__( 'Background', 'fusion-builder' ),
					'subgroup'    => [
						'name' => 'background_type',
						'tab'  => 'image',
					],
					'dependency'  => [
						[
							'element'  => 'background_image',
							'value'    => '',
							'operator' => '!=',
						],
					],
					'value'       => [
						'left top'      => esc_attr__( 'Left Top', 'fusion-builder' ),
						'left center'   => esc_attr__( 'Left Center', 'fusion-builder' ),
						'left bottom'   => esc_attr__( 'Left Bottom', 'fusion-builder' ),
						'right top'     => esc_attr__( 'Right Top', 'fusion-builder' ),
						'right center'  => esc_attr__( 'Right Center', 'fusion-builder' ),
						'right bottom'  => esc_attr__( 'Right Bottom', 'fusion-builder' ),
						'center top'    => esc_attr__( 'Center Top', 'fusion-builder' ),
						'center center' => esc_attr__( 'Center Center', 'fusion-builder' ),
						'center bottom' => esc_attr__( 'Center Bottom', 'fusion-builder' ),
					],
				],
				[
					'type'        => 'select',
					'heading'     => esc_attr__( 'Background Repeat', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose how the background image repeats.', 'fusion-builder' ),
					'param_name'  => 'background_repeat',
					'default'     => 'no-repeat',
					'group'       => esc_attr__( 'Background', 'fusion-builder' ),
					'subgroup'    => [
						'name' => 'background_type',
						'tab'  => 'image',
					],
					'dependency'  => [
						[
							'element'  => 'background_image',
							'value'    => '',
							'operator' => '!=',
						],
					],
					'value'       => [
						'no-repeat' => esc_attr__( 'No Repeat', 'fusion-builder' ),
						'repeat'    => esc_attr__( 'Repeat Vertically and Horizontally', 'fusion-builder' ),
						'repeat-x'  => esc_attr__( 'Repeat Horizontally', 'fusion-builder' ),
						'repeat-y'  => esc_attr__( 'Repeat Vertically', 'fusion-builder' ),
					],
				],
				[
					'type'        => 'select',
					'heading'     => esc_attr__( 'Background Blend Mode', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose how blending should work for each background layer.', 'fusion-builder' ),
					'param_name'  => 'background_blend_mode',
					'value'       => [
						'none'        => esc_attr__( 'Disabled', 'fusion-builder' ),
						'multiply'    => esc_attr__( 'Multiply', 'fusion-builder' ),
						'screen'      => esc_attr__( 'Screen', 'fusion-builder' ),
						'overlay'     => esc_attr__( 'Overlay', 'fusion-builder' ),
						'darken'      => esc_attr__( 'Darken', 'fusion-builder' ),
						'lighten'     => esc_attr__( 'Lighten', 'fusion-builder' ),
						'color-dodge' => esc_attr__( 'Color Dodge', 'fusion-builder' ),
						'color-burn'  => esc_attr__( 'Color Burn', 'fusion-builder' ),
						'hard-light'  => esc_attr__( 'Hard Light', 'fusion-builder' ),
						'soft-light'  => esc_attr__( 'Soft Light', 'fusion-builder' ),
						'difference'  => esc_attr__( 'Difference', 'fusion-builder' ),
						'exclusion'   => esc_attr__( 'Exclusion', 'fusion-builder' ),
						'hue'         => esc_attr__( 'Hue', 'fusion-builder' ),
						'saturation'  => esc_attr__( 'Saturation', 'fusion-builder' ),
						'color'       => esc_attr__( 'Color', 'fusion-builder' ),
						'luminosity'  => esc_attr__( 'Luminosity', 'fusion-builder' ),
					],
					'default'     => 'none',
					'group'       => esc_attr__( 'Background', 'fusion-builder' ),
					'subgroup'    => [
						'name' => 'background_type',
						'tab'  => 'image',
					],
					'dependency'  => [
						[
							'element'  => 'background_image',
							'value'    => '',
							'operator' => '!=',
						],
					],
				],
				'fusion_conditional_render_placeholder' => [],
				'fusion_get_column_params'              => [],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Position Sticky', 'fusion-builder' ),
					'description' => __( 'Turn on to have the column stick inside its parent container on scroll. <strong>NOTE:</strong> this feature uses the browser native sticky positioning.  Depending on the browser and specific setup the feature may not be available.', 'fusion-builder' ),
					'param_name'  => 'sticky',
					'default'     => 'off',
					'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
					'value'       => [
						'on'  => esc_html__( 'On', 'fusion-builder' ),
						'off' => esc_html__( 'Off', 'fusion-builder' ),
					],
				],
				[
					'type'        => 'checkbox_button_set',
					'heading'     => esc_attr__( 'Responsive Position Sticky', 'fusion-builder' ),
					'param_name'  => 'sticky_devices',
					'value'       => fusion_builder_visibility_options( 'full' ),
					'default'     => fusion_builder_default_visibility( 'array' ),
					'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose at which screen sizes the container should be sticky.', 'fusion-builder' ),
					'dependency'  => [
						[
							'element'  => 'sticky',
							'value'    => 'on',
							'operator' => '==',
						],
					],
				],
				[
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Sticky Column Offset', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls how far the top of the column is offset from top of viewport when sticky. Use either a unit of measurement, or a CSS selector.', 'fusion-builder' ),
					'param_name'  => 'sticky_offset',
					'value'       => '',
					'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
					'dependency'  => [
						[
							'element'  => 'sticky',
							'value'    => 'on',
							'operator' => '==',
						],
					],
				],
				'fusion_filter_placeholder'             => [
					'selector_base' => 'fusion-builder-column-live-',
				],
				'fusion_transform_placeholder'          => [
					'selector_base' => 'fusion-builder-column-live-',
				],
				'fusion_animation_placeholder'          => [
					'preview_selector' => '$el',
				],
			];
		}
	}
}

<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.5
 */

if ( fusion_is_element_enabled( 'fusion_views_counter' ) ) {

	if ( ! class_exists( 'Fusion_Views_Counter' ) && class_exists( 'Fusion_Element' ) ) {

		/**
		 * Shortcode class.
		 *
		 * @since 3.5
		 */
		class Fusion_Views_Counter extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.5
			 * @var array
			 */
			protected $args;

			/**
			 * The number of instance of this element. Working as an id.
			 *
			 * @access protected
			 * @since 3.5
			 * @var int
			 */
			protected $element_id = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.5
			 */
			public function __construct() {
				parent::__construct();

				add_filter( 'fusion_attr_views-counter-wrapper', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_views-counter-content', [ $this, 'attr_content' ] );
				add_shortcode( 'fusion_views_counter', [ $this, 'render' ] );
				add_filter( 'fusion_pipe_seprator_shortcodes', [ $this, 'allow_separator' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @since 3.5
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'views_displayed'            => 'total_views,today_views',
					'separator'                  => '',
					'layout'                     => 'floated',
					'labels'                     => 'before',
					'total_views_label'          => '',
					'today_views_label'          => '',
					'hide_on_mobile'             => fusion_builder_default_visibility( 'string' ),
					'class'                      => '',
					'id'                         => '',

					'font_size'                  => '',
					'color'                      => '',
					'background'                 => '',
					'alignment_floated'          => 'flex-start',
					'alignment_stacked'          => 'flex-start',

					'padding_bottom'             => '',
					'padding_left'               => '',
					'padding_right'              => '',
					'padding_top'                => '',

					'margin_bottom'              => '',
					'margin_left'                => '',
					'margin_right'               => '',
					'margin_top'                 => '',

					'border_radius_top_left'     => '',
					'border_radius_top_right'    => '',
					'border_radius_bottom_right' => '',
					'border_radius_bottom_left'  => '',

					'box_shadow'                 => '',
					'box_shadow_blur'            => '',
					'box_shadow_color'           => '',
					'box_shadow_horizontal'      => '',
					'box_shadow_spread'          => '',
					'box_shadow_style'           => '',
					'box_shadow_vertical'        => '',

					'animation_direction'        => 'left',
					'animation_offset'           => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'            => '',
					'animation_type'             => '',
				];
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @since 3.5
			 * @return array
			 */
			public static function get_element_extras() {
				return [
					'total_views'                     => number_format_i18n( self::get_post_views( self::get_views_post_id() ) ),
					'today_views'                     => number_format_i18n( self::get_today_post_views( self::get_views_post_id() ) ),
					'default_total_views_before_text' => self::get_default_total_views_before_text(),
					'default_today_views_before_text' => self::get_default_today_views_before_text(),
					'default_total_views_after_text'  => self::get_default_total_views_after_text(),
					'default_today_views_after_text'  => self::get_default_today_views_after_text(),
				];
			}

			/**
			 * Enables pipe separator for short code.
			 *
			 * @access public
			 * @since 2.4
			 * @param array $shortcodes The shortcodes array.
			 * @return array
			 */
			public function allow_separator( $shortcodes ) {
				if ( is_array( $shortcodes ) ) {
					array_push( $shortcodes, 'fusion_views_counter' );
				}

				return $shortcodes;
			}

			/**
			 * Get the post_id in which the element is displayed.
			 *
			 * @since 3.5
			 * @return int|string
			 */
			public static function get_views_post_id() {
				$id = get_the_ID();
				if ( isset( $_POST['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.PHP.StrictComparisons.LooseComparison
					$id = (int) sanitize_text_field( wp_unslash( $_POST['post_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.PHP.StrictComparisons.LooseComparison
				}

				return apply_filters( 'fusion_dynamic_post_id', $id );
			}

			/**
			 * Render the shortcode.
			 *
			 * @since 3.5
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$this->args      = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_views_counter' );
				$fusion_settings = awb_get_fusion_settings();

				if ( 'disabled' === $fusion_settings->get( 'post_views' ) ) {
					return '';
				}

				$html  = '<div ' . FusionBuilder::attributes( 'views-counter-wrapper' ) . '>';
				$html .= $this->get_element_style();
				$html .= '<div ' . FusionBuilder::attributes( 'views-counter-content' ) . '>';
				$html .= $this->get_views_text();
				$html .= '</div>';
				$html .= '</div>';

				$this->element_id++;

				return $html;
			}

			/**
			 * Get the text that is displaying the views.
			 *
			 * @return string
			 */
			private function get_views_text() {
				$views_to_display = explode( ',', $this->args['views_displayed'] );
				$separator        = '<span class="awb-views-counter-separator">' . $this->args['separator'] . '</span>';

				$text            = '';
				$number_of_views = count( $views_to_display );
				foreach ( $views_to_display as $index => $view_type ) {
					$is_last_item = ( $number_of_views - 1 === $index ? true : false );
					if ( $is_last_item ) {
						$separator = '';
					}

					if ( 'total_views' === $view_type ) {
						$total_views = number_format_i18n( self::get_post_views() );
						$label       = empty( $this->args['total_views_label'] ) ? $this->get_translation_text( $view_type ) : $this->args['total_views_label'];

						if ( 'before' === $this->args['labels'] ) {
							$views_text = $label . $total_views;
						} elseif ( 'after' === $this->args['labels'] ) {
							$views_text = $total_views . $label;
						} else {
							$views_text = $total_views;
						}

						$text .= '<span class="awb-views-counter-total-views">' . $views_text . '</span>' . $separator;
					}

					if ( 'today_views' === $view_type ) {
						$today_views = number_format_i18n( self::get_today_post_views() );
						$label       = empty( $this->args['today_views_label'] ) ? $this->get_translation_text( $view_type ) : $this->args['today_views_label'];

						if ( 'before' === $this->args['labels'] ) {
							$views_text = $label . $today_views;
						} elseif ( 'after' === $this->args['labels'] ) {
							$views_text = $today_views . $label;
						} else {
							$views_text = $today_views;
						}

						$text .= '<span class="awb-views-counter-today-views">' . $views_text . '</span>' . $separator;
					}
				}

				return $text;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @since 3.5
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'awb-views-counter awb-views-counter-' . $this->element_id,
					'style' => '',
				];

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

				return $attr;
			}

			/**
			 * Builds the attributes array for the content element.
			 *
			 * @since 3.5
			 * @return array
			 */
			public function attr_content() {
				$attr = [
					'class' => 'awb-views-counter-content',
				];

				if ( 'stacked' === $this->args['layout'] ) {
					$attr['class'] .= ' awb-views-counter-content-stacked';
				}

				return $attr;
			}

			/**
			 * Get the style HTML tag for this element.
			 *
			 * @since 3.5
			 * @return string
			 */
			public function get_element_style() {
				$style             = '';
				$base_selector     = '.awb-views-counter-' . $this->element_id;
				$wrapper_selector  = $base_selector . '.awb-views-counter';
				$content_selector  = $base_selector . ' .awb-views-counter-content';
				$this->dynamic_css = [];

				if ( $this->args['color'] ) {
					$this->add_css_property( $content_selector, 'color', $this->args['color'] );
				}

				if ( $this->args['background'] ) {
					$this->add_css_property( $wrapper_selector, 'background-color', $this->args['background'] );
				}

				if ( $this->args['alignment_floated'] && 'floated' === $this->args['layout'] ) {
					$this->add_css_property( $content_selector, 'justify-content', $this->args['alignment_floated'] );
				}

				if ( $this->args['alignment_stacked'] && 'stacked' === $this->args['layout'] ) {
					$this->add_css_property( $content_selector, 'align-items', $this->args['alignment_stacked'] );
				}

				if ( $this->args['font_size'] ) {
					$this->add_css_property( $content_selector, 'font-size', $this->args['font_size'] );
				}

				if ( $this->args['padding_top'] ) {
					$this->add_css_property( $wrapper_selector, 'padding-top', $this->args['padding_top'] );
				}

				if ( $this->args['padding_bottom'] ) {
					$this->add_css_property( $wrapper_selector, 'padding-bottom', $this->args['padding_bottom'] );
				}

				if ( $this->args['padding_left'] ) {
					$this->add_css_property( $wrapper_selector, 'padding-left', $this->args['padding_left'] );
				}

				if ( $this->args['padding_right'] ) {
					$this->add_css_property( $wrapper_selector, 'padding-right', $this->args['padding_right'] );
				}

				if ( $this->args['margin_top'] ) {
					$this->add_css_property( $wrapper_selector, 'margin-top', $this->args['margin_top'] );
				}

				if ( $this->args['margin_bottom'] ) {
					$this->add_css_property( $wrapper_selector, 'margin-bottom', $this->args['margin_bottom'] );
				}

				if ( $this->args['margin_left'] ) {
					$this->add_css_property( $wrapper_selector, 'margin-left', $this->args['margin_left'] );
				}

				if ( $this->args['margin_right'] ) {
					$this->add_css_property( $wrapper_selector, 'margin-right', $this->args['margin_right'] );
				}

				if ( $this->args['border_radius_top_left'] ) {
					$this->add_css_property( $wrapper_selector, 'border-top-left-radius', $this->args['border_radius_top_left'] );
				}

				if ( $this->args['border_radius_top_right'] ) {
					$this->add_css_property( $wrapper_selector, 'border-top-right-radius', $this->args['border_radius_top_right'] );
				}

				if ( $this->args['border_radius_bottom_right'] ) {
					$this->add_css_property( $wrapper_selector, 'border-bottom-right-radius', $this->args['border_radius_bottom_right'] );
				}

				if ( $this->args['border_radius_bottom_left'] ) {
					$this->add_css_property( $wrapper_selector, 'border-bottom-left-radius', $this->args['border_radius_bottom_left'] );
				}

				if ( 'yes' === $this->args['box_shadow'] ) {
					$box_shadow_value = Fusion_Builder_Box_Shadow_Helper::get_box_shadow_styles(
						[
							'box_shadow_horizontal' => $this->args['box_shadow_horizontal'],
							'box_shadow_vertical'   => $this->args['box_shadow_vertical'],
							'box_shadow_blur'       => $this->args['box_shadow_blur'],
							'box_shadow_spread'     => $this->args['box_shadow_spread'],
							'box_shadow_color'      => $this->args['box_shadow_color'],
							'box_shadow_style'      => $this->args['box_shadow_style'],
						]
					);
					$this->add_css_property( $wrapper_selector, 'box-shadow', str_replace( ';', '', $box_shadow_value ) );
				}

				$style = $this->parse_css();
				return $style ? '<style>' . $style . '</style>' : '';
			}

			/**
			 * Get the total post views.
			 *
			 * @since 3.5
			 * @param WP_Post|int|null $post The post object, id or null. Defaults to global post.
			 * @return int
			 */
			private static function get_post_views( $post = null ) {
				$post_views = 0;

				if ( function_exists( 'avada_get_post_views' ) ) {
					$post_views = avada_get_post_views( $post );
				}

				return $post_views;
			}

			/**
			 * Get the daily post views.
			 *
			 * @since 3.5
			 * @param WP_Post|int|null $post The post object, id or null. Defaults to global post.
			 * @return int
			 */
			private static function get_today_post_views( $post = null ) {
				$daily_views = 0;

				if ( function_exists( 'avada_get_today_post_views' ) ) {
					$daily_views = avada_get_today_post_views( $post );
				}

				return $daily_views;
			}

			/**
			 * Get the translation for default total views.
			 *
			 * @since 3.5
			 * @return string
			 */
			private static function get_default_total_views_before_text() {
				/* translators: The number of views will be displayed after this text. */
				return esc_html__( 'Total Views:', 'fusion-builder' ) . ' ';
			}

			/**
			 * Get the translation for default today views.
			 *
			 * @since 3.5
			 * @return string
			 */
			private static function get_default_today_views_before_text() {
				/* translators: The number of views will be displayed after this text. */
				return esc_html__( 'Daily Views:', 'fusion-builder' ) . ' ';
			}

			/**
			 * Get the translation for default total views. Text displayed after
			 * the views.
			 *
			 * @since 3.5
			 * @return string
			 */
			private static function get_default_total_views_after_text() {
				/* translators: The number of views will be displayed before this text. */
				return ' ' . esc_html__( 'Total Views', 'fusion-builder' );
			}

			/**
			 * Get the translation for default today views. Text displayed after
			 * the views.
			 *
			 * @since 3.5
			 * @return string
			 */
			private static function get_default_today_views_after_text() {
				/* translators: The number of views will be displayed before this text. */
				return ' ' . esc_html__( 'Daily Views', 'fusion-builder' );
			}

			/**
			 * Get the translation text, depending if the labels are before or
			 * after.
			 *
			 * @access private
			 * @since 3.5
			 * @param string $view_type The views type.
			 * @return string
			 */
			private function get_translation_text( $view_type ) {
				$translation_text = '';

				if ( 'total_views' === $view_type ) {
					if ( 'before' === $this->args['labels'] ) {
						$translation_text = self::get_default_total_views_before_text();
					} elseif ( 'after' === $this->args['labels'] ) {
						$translation_text = self::get_default_total_views_after_text();
					}
				} elseif ( 'today_views' === $view_type ) {
					if ( 'before' === $this->args['labels'] ) {
						$translation_text = self::get_default_today_views_before_text();
					} elseif ( 'after' === $this->args['labels'] ) {
						$translation_text = self::get_default_today_views_after_text();
					}
				}

				return $translation_text;
			}


			/**
			 * Load base CSS.
			 *
			 * @since 3.5
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/views-counter.min.css' );
			}
		}
	}

	new Fusion_Views_Counter();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.5
 */
function fusion_views_counter_map() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			// Class reference.
			'Fusion_Views_Counter',
			[
				'name'      => esc_attr__( 'Views Counter', 'fusion-builder' ),
				'shortcode' => 'fusion_views_counter',
				'icon'      => 'fusiona-eye',
				'params'    => [
					[
						'type'        => 'connected_sortable',
						'heading'     => esc_attr__( 'View Types', 'fusion-builder' ),
						'description' => esc_attr__( 'Displays the enabled view types. Drag to rearrange.', 'fusion-builder' ),
						'empty'       => esc_attr__( 'Drag view types here to disable them.', 'fusion-builder' ),
						'param_name'  => 'views_displayed',
						'default'     => 'total_views,today_views',
						'choices'     => [
							'total_views' => esc_attr__( 'Total Views', 'fusion-builder' ),
							'today_views' => esc_attr__( 'Daily Views', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Layout', 'fusion-builder' ),
						'description' => esc_html__( 'Select layout for view types.', 'fusion-builder' ),
						'param_name'  => 'layout',
						'default'     => 'floated',
						'value'       => [
							'floated' => esc_html__( 'Floated', 'fusion-builder' ),
							'stacked' => esc_html__( 'Stacked', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Separator', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter a separator to display between view types.', 'fusion-builder' ),
						'param_name'  => 'separator',
						'escape_html' => true,
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'stacked',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Labels', 'fusion-builder' ),
						'description' => esc_html__( 'Choose where to display the labels, or turn them off.', 'fusion-builder' ),
						'param_name'  => 'labels',
						'default'     => 'before',
						'value'       => [
							'before' => esc_html__( 'Before', 'fusion-builder' ),
							'after'  => esc_html__( 'After', 'fusion-builder' ),
							'off'    => esc_html__( 'Off', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Total Views Label', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the total views label to display before/after the views.', 'fusion-builder' ),
						'param_name'  => 'total_views_label',
						'escape_html' => true,
						'dependency'  => [
							[
								'element'  => 'labels',
								'value'    => 'off',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Daily Views Label', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the daily views label to display before/after the views.', 'fusion-builder' ),
						'param_name'  => 'today_views_label',
						'escape_html' => true,
						'dependency'  => [
							[
								'element'  => 'labels',
								'value'    => 'off',
								'operator' => '!=',
							],
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
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Font Size', 'fusion-builder' ),
						'description' => esc_html__( 'Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the text color.', 'fusion-builder' ),
						'param_name'  => 'color',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the background color.', 'fusion-builder' ),
						'param_name'  => 'background',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the views alignment.', 'fusion-builder' ),
						'param_name'  => 'alignment_floated',
						'default'     => 'flex-start',
						'grid_layout' => true,
						'back_icons'  => true,
						'icons'       => [
							'flex-start'    => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'        => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'      => '<span class="fusiona-horizontal-flex-end"></span>',
							'space-between' => '<span class="fusiona-horizontal-space-between"></span>',
							'space-around'  => '<span class="fusiona-horizontal-space-around"></span>',
							'space-evenly'  => '<span class="fusiona-horizontal-space-evenly"></span>',
						],
						'value'       => [
							'flex-start'    => esc_html__( 'Flex Start', 'fusion-builder' ),
							'center'        => esc_html__( 'Center', 'fusion-builder' ),
							'flex-end'      => esc_html__( 'Flex End', 'fusion-builder' ),
							'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
							'space-around'  => esc_html__( 'Space Around', 'fusion-builder' ),
							'space-evenly'  => esc_html__( 'Space Evenly', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'floated',
								'operator' => '==',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the views alignment.', 'fusion-builder' ),
						'param_name'  => 'alignment_stacked',
						'default'     => 'flex-start',
						'grid_layout' => true,
						'back_icons'  => true,
						'value'       => [
							'flex-start' => esc_attr__( 'Flex Start', 'fusion-builder' ),
							'center'     => esc_attr__( 'Center', 'fusion-builder' ),
							'flex-end'   => esc_attr__( 'Flex End', 'fusion-builder' ),
						],
						'icons'       => [
							'flex-start' => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'     => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'   => '<span class="fusiona-horizontal-flex-end"></span>',
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'stacked',
								'operator' => '==',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'padding',
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					'fusion_margin_placeholder'        => [
						'param_name' => 'margin',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					'fusion_border_radius_placeholder' => [],
					'fusion_box_shadow_placeholder'    => [],
					'fusion_animation_placeholder'     => [
						'preview_selector' => '.awb-views-counter',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_views_counter_map' );

<?php
/**
 * Add the rating element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.5
 */

if ( fusion_is_element_enabled( 'fusion_star_rating' ) ) {

	if ( ! class_exists( 'FusionSC_Star_Rating' ) ) {

		/**
		 * Shortcode class.
		 *
		 * @since 3.5
		 */
		class FusionSC_Star_Rating extends Fusion_Element {

			/**
			 * The number of instance of this element. Working as an id.
			 *
			 * @since 3.5
			 * @var int
			 */
			protected $element_counter = 1;

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
				add_filter( 'fusion_attr_star-rating-element-attr', [ $this, 'element_attr' ] );
				add_filter( 'fusion_attr_star-rating-icon-attr', [ $this, 'icon_attr' ] );
				add_filter( 'fusion_attr_star-rating-partial-filled-icon-attr', [ $this, 'partial_filled_icon_attr' ] );

				add_shortcode( 'fusion_star_rating', [ $this, 'render' ] );
			}

			/**
			 * Render the shortcode.
			 *
			 * @access public
			 * @since 3.5
			 * @param array $args Shortcode parameters.
			 * @return string HTML output.
			 */
			public function render( $args ) {
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_star_rating' );

				$rating         = $this->get_rating();
				$maximum_rating = $this->args['maximum_rating'];

				if ( $rating > $maximum_rating ) {
					$rating = $maximum_rating;
				}

				if ( 'hide' === $this->args['display_empty_rating'] && 0 >= $rating ) {
					return '';
				}

				$html  = '<div ' . FusionBuilder::attributes( 'star-rating-element-attr' ) . '>';
				$html .= $this->get_element_style();

				$html .= '<div class="awb-stars-rating-icons-wrapper">';
				$html .= $this->get_icons_html();
				$html .= '</div>';

				if ( 'yes' === $this->args['display_rating_text'] ) {
					$html .= '<div class="awb-stars-rating-text">';
					$html .= $this->get_rating_text_html();
					$html .= '</div>';
				}

				$html .= '</div>';

				if ( $rating > 0 ) {
					if ( '0decimals' === $this->args['rating_number_rounding'] ) {
						$schema_rating = (string) round( $rating, 0 );
					} elseif ( '1decimal' === $this->args['rating_number_rounding'] ) {
						$schema_rating = (string) round( $rating, 1 );
					} else {
						$schema_rating = (string) round( $rating, 2 );
					}

					// Add the schema for this element.
					new Fusion_JSON_LD(
						'fusion-star-rating',
						[
							'@context'    => 'https://schema.org',
							'@type'       => 'Rating',
							'ratingValue' => $schema_rating,
							'bestRating'  => $maximum_rating,
						]
					);
				}

				$this->element_counter++;
				return $html;
			}

			/**
			 * Get the HTML of the icons.
			 *
			 * @since 3.5
			 * @return string
			 */
			protected function get_icons_html() {
				$html = '';

				$rating = $this->get_rating();

				if ( '0decimals' === $this->args['rating_number_rounding'] && 'yes' === $this->args['display_rating_text'] ) {
					$rating = intval( round( $rating, 0 ) );
				}

				$maximum_rating           = $this->args['maximum_rating'];
				$is_perfect_round_average = ( intval( $rating ) === $rating );

				$current_star = 1;
				while ( $current_star <= $maximum_rating ) {
					$html .= '<i ' . FusionBuilder::attributes( 'star-rating-icon-attr', $current_star ) . '>';
					if ( ! $is_perfect_round_average && ( intval( $rating ) + 1 ) === $current_star ) {
						$html .= '<i ' . FusionBuilder::attributes( 'star-rating-partial-filled-icon-attr' ) . '></i>';
					}
					$html .= '</i>';
					$current_star++;
				}

				return $html;
			}

			/**
			 * Get the HTML of the rating text.
			 *
			 * @since 3.5
			 * @return string
			 */
			protected function get_rating_text_html() {
				$rating         = $this->get_rating();
				$maximum_rating = $this->args['maximum_rating'];
				$html           = $before = $after = '';

				if ( $rating > $maximum_rating ) {
					$rating = $maximum_rating;
				}

				// Dynamic data before/after.
				if ( is_string( $this->args['rating'] ) && ! is_numeric( str_replace( ',', '.', $this->args['rating'] ) ) ) {
					$before_after = explode( $this->get_rating( false ), $this->args['rating'] );
					$before       = ' ' !== $before_after[0] ? $before_after[0] : '';
					$after        = isset( $before_after[1] ) ? $before_after[1] : '';
				}

				$html .= $before . '<span>' . number_format_i18n( $rating, $this->get_number_to_round( $rating ) ) . '</span> / <span>' . $maximum_rating . '</span>' . $after;

				return $html;
			}

			/**
			 * Get the attributes of the main element HTML tag.
			 *
			 * @since 3.5
			 * @return array
			 */
			public function element_attr() {
				$attr = [
					'class' => 'awb-stars-rating ' . $this->get_base_class_name(),
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( 'no' === $this->args['display_rating_text'] ) {
					$attr['class'] .= ' awb-stars-rating-no-text';
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				// Add aria label for accessibility.
				$rating         = $this->get_rating();
				$maximum_rating = $this->args['maximum_rating'];

				if ( $rating > $maximum_rating ) {
					$rating = $maximum_rating;
				}

				if ( '0decimals' === $this->args['rating_number_rounding'] ) {
					$rating_label = intval( round( $rating, 0 ) );
				} elseif ( '1decimal' === $this->args['rating_number_rounding'] ) {
					$rating_label = number_format_i18n( $rating, 1 );
				} else {
					$rating_label = number_format_i18n( $rating, 2 );
				}

				/* translators: %1$s: The average rating, %1$s: The maximum rating. */
				$aria_label = sprintf( esc_attr__( 'Rating: %1$s out of %2$s', 'fusion-builder' ), $rating_label, $maximum_rating );

				$attr['aria-label'] = $aria_label;

				return $attr;
			}

			/**
			 * Get the attributes of the icon tag.
			 *
			 * @since 3.5
			 * @param int $current_icon_num The number of the current icon.
			 * @return array
			 */
			public function icon_attr( $current_icon_num ) {
				$attr = [
					'class' => fusion_font_awesome_name_handler( $this->args['icon'] ),
				];

				$rating = $this->get_rating();

				if ( '0decimals' === $this->args['rating_number_rounding'] && 'yes' === $this->args['display_rating_text'] ) {
					$rating = intval( round( $rating, 0 ) );
				}

				$is_perfect_round_average = ( intval( $rating ) === $rating );
				$icon_is_partially_filled = ( ( intval( $rating ) + 1 ) === $current_icon_num );

				if ( $current_icon_num <= $rating ) {
					$attr['class'] .= ' awb-stars-rating-filled-icon';
				} elseif ( ! $is_perfect_round_average && $icon_is_partially_filled ) {
					$attr['class'] .= ' awb-stars-rating-partial-icon-wrapper';
				} else {
					$attr['class'] .= ' awb-stars-rating-empty-icon';
				}

				return $attr;
			}

			/**
			 * Get the attributes of the partial filled icon tag.
			 *
			 * @since 3.5
			 * @return array
			 */
			public function partial_filled_icon_attr() {
				$attr = [
					'class' => fusion_font_awesome_name_handler( $this->args['icon'] ) . ' awb-stars-rating-partial-icon',
				];

				$rating        = $this->get_rating();
				$decimals      = $rating - intval( $rating );
				$width_percent = intval( $decimals * 100 ) . '%';

				$attr['style'] = 'width:' . $width_percent . ';';

				return $attr;
			}

			/**
			 * Create the style HTML element.
			 *
			 * @since 3.5
			 * @return string
			 */
			public function get_element_style() {
				$style             = '';
				$base_class_name   = '.' . $this->get_base_class_name();
				$this->dynamic_css = [];

				$base_class_selector = [
					$base_class_name . '.awb-stars-rating',
				];

				$all_relative_icons_selector = [
					$base_class_name . ' .awb-stars-rating-filled-icon',
					$base_class_name . ' .awb-stars-rating-empty-icon',
					$base_class_name . ' .awb-stars-rating-partial-icon-wrapper',
				];

				$all_icons_that_have_active_color = [
					$base_class_name . ' .awb-stars-rating-filled-icon',
					$base_class_name . ' .awb-stars-rating-partial-icon',
				];

				$icons_wrapper_selector = [
					$base_class_name . ' .awb-stars-rating-icons-wrapper',
				];

				$rtl_icons_wrapper_selector = [
					'.rtl ' . $base_class_name . ' .awb-stars-rating-icons-wrapper',
				];

				$text_wrapper_selector = [
					$base_class_name . ' .awb-stars-rating-text',
				];

				if ( ! $this->is_default( 'icons_distance' ) && $this->args['icons_distance'] ) {
					$this->add_css_property( $all_relative_icons_selector, 'margin-right', $this->args['icons_distance'] );
				}

				if ( ! $this->is_default( 'active_color' ) && $this->args['active_color'] ) {
					$this->add_css_property( $all_icons_that_have_active_color, 'color', $this->args['active_color'] );
				}

				if ( ! $this->is_default( 'inactive_color' ) && $this->args['inactive_color'] ) {
					$this->add_css_property( $icons_wrapper_selector, 'color', $this->args['inactive_color'] );
				}

				if ( ! $this->is_default( 'icon_font_size' ) && $this->args['icon_font_size'] ) {
					$this->add_css_property( $icons_wrapper_selector, 'font-size', $this->args['icon_font_size'] );
				}

				if ( 'yes' === $this->args['display_rating_text'] ) {
					if ( ! $this->is_default( 'text_font_size' ) && $this->args['text_font_size'] ) {
						$this->add_css_property( $text_wrapper_selector, 'font-size', $this->args['text_font_size'] );
					}

					if ( ! $this->is_default( 'text_font_color' ) && $this->args['text_font_color'] ) {
						$this->add_css_property( $text_wrapper_selector, 'color', $this->args['text_font_color'] );
					}

					if ( ! $this->is_default( 'icons_text_distance' ) && $this->args['icons_text_distance'] ) {
						if ( ! is_rtl() ) {
							$this->add_css_property( $icons_wrapper_selector, 'margin-right', $this->args['icons_text_distance'] );
						} else {
							$this->add_css_property( $rtl_icons_wrapper_selector, 'margin-left', $this->args['icons_text_distance'] );
						}
					}
				}

				if ( ! $this->is_default( 'alignment' ) && $this->args['alignment'] ) {
					$this->add_css_property( $base_class_selector, 'justify-content', $this->args['alignment'] );
				}

				if ( ! $this->is_default( 'margin_top' ) && $this->args['margin_top'] ) {
					$this->add_css_property( $base_class_selector, 'margin-top', $this->args['margin_top'] );
				}

				if ( ! $this->is_default( 'margin_right' ) && $this->args['margin_right'] ) {
					$this->add_css_property( $base_class_selector, 'margin-right', $this->args['margin_right'] );
				}

				if ( ! $this->is_default( 'margin_bottom' ) && $this->args['margin_bottom'] ) {
					$this->add_css_property( $base_class_selector, 'margin-bottom', $this->args['margin_bottom'] );
				}

				if ( ! $this->is_default( 'margin_left' ) && $this->args['margin_left'] ) {
					$this->add_css_property( $base_class_selector, 'margin-left', $this->args['margin_left'] );
				}

				$style = $this->parse_css();

				return $style ? '<style>' . $style . '</style>' : '';
			}

			/**
			 * Get the average rating number.
			 *
			 * @since 3.5
			 * @param bool $number_conversion Flag to have rating converted to int|float.
			 * @return int|float|string
			 */
			public function get_rating( $number_conversion = true ) {
				$avg = $this->args['rating'];

				// Let the users use ',' as decimal separator.
				if ( is_string( $avg ) ) {
					$avg = str_replace( [ ',', ' ' ], [ '.', '' ], $avg );
				}

				if ( ! is_numeric( $avg ) ) {
					$avg = preg_replace( '/[^.|0-9]/', '', $avg );
				}

				if ( ! $number_conversion ) {
					return $avg;
				}

				if ( is_numeric( $avg ) ) {
					$avg = + $avg;
				} else {
					$avg = 0;
				}

				return $avg;
			}

			/**
			 * Get the number of decimals to round the rating.
			 *
			 * @since 3.7
			 * @param int|string $rating The rating to calculate how much decimals to round.
			 * @return int
			 */
			public function get_number_to_round( $rating ) {
				if ( '0decimals' === $this->args['rating_number_rounding'] ) {
					return 0;
				} elseif ( '1decimal' === $this->args['rating_number_rounding'] ) {
					return 1;
				} elseif ( '2decimals' === $this->args['rating_number_rounding'] ) {
					return 2;
				}

				// 'rating_number_rounding' is set to 'auto' if here.

				$number_of_decimals = strlen( substr( strrchr( (string) floatval( $rating ), '.' ), 1 ) );

				if ( 0 === $number_of_decimals ) {
					return 0;
				} elseif ( 1 === $number_of_decimals ) {
					return 1;
				} else {
					return 2;
				}
			}

			/**
			 * Get the class name with an unique id among elements.
			 *
			 * @since 3.5
			 * @return string
			 */
			public function get_base_class_name() {
				return 'awb-stars-rating-' . $this->element_counter;
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
					'maximum_rating'         => '5',
					'rating'                 => '3',
					'display_empty_rating'   => 'show',
					'hide_on_mobile'         => fusion_builder_default_visibility( 'string' ),
					'class'                  => '',
					'id'                     => '',

					'icon'                   => 'fa-star fas',
					'active_color'           => '',
					'inactive_color'         => '',
					'icons_distance'         => '',
					'display_rating_text'    => 'yes',
					'alignment'              => '',
					'icon_font_size'         => '',
					'text_font_size'         => '',
					'text_font_color'        => '',
					'rating_number_rounding' => 'auto',
					'icons_text_distance'    => '',
					'margin_top'             => '',
					'margin_right'           => '',
					'margin_bottom'          => '',
					'margin_left'            => '',

					'animation_direction'    => 'left',
					'animation_offset'       => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'        => '',
					'animation_type'         => '',
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
					'is_rtl' => is_rtl(),
				];
			}

			/**
			 * Load base CSS.
			 *
			 * @since 3.5
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/star-rating.min.css' );
			}

		}

		new FusionSC_Star_Rating();

	}
}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 3.5
 */
function fusion_element_star_rating() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Star_Rating',
			[
				'name'      => esc_attr__( 'Star Rating', 'fusion-builder' ),
				'shortcode' => 'fusion_star_rating',
				'icon'      => 'fusiona-af-rating',
				'params'    => [
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Maximum Rating', 'fusion-builder' ),
						'description' => esc_html__( 'Select the maximum possible rating.', 'fusion-builder' ),
						'param_name'  => 'maximum_rating',
						'value'       => '5',
						'min'         => '1',
						'max'         => '10',
						'step'        => '1',
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Rating', 'fusion-builder' ),
						'description'  => esc_html__( 'Enter the rating.', 'fusion-builder' ),
						'param_name'   => 'rating',
						'dynamic_data' => true,
						'value'        => '3',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Display Empty Rating', 'fusion-builder' ),
						'description' => esc_attr__( 'Select whether to display the rating if no rating is present (eg. 0/5), or to completely hide it. Note: In the live editor the element will be always be displayed.', 'fusion-builder' ),
						'param_name'  => 'display_empty_rating',
						'default'     => 'show',
						'value'       => [
							'show' => esc_html__( 'Show', 'fusion-builder' ),
							'hide' => esc_html__( 'Hide', 'fusion-builder' ),
						],
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
						'description' => esc_html__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'description' => esc_html__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => 'fa-star fas',
						'description' => esc_html__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Icon Size', 'fusion-builder' ),
						'description' => esc_html__( 'Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'icon_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Filled Icon Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the color of the filled icons.', 'fusion-builder' ),
						'param_name'  => 'active_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Empty Icon Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the color of the empty icons.', 'fusion-builder' ),
						'param_name'  => 'inactive_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Icon Spacing', 'fusion-builder' ),
						'description' => esc_html__( 'Control the spacing between the icons. Enter value including any valid CSS unit, ex: 15px.', 'fusion-builder' ),
						'param_name'  => 'icons_distance',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Display Rating Text', 'fusion-builder' ),
						'description' => esc_attr__( 'Select whether or not to display the rating text next to the icons.', 'fusion-builder' ),
						'param_name'  => 'display_rating_text',
						'default'     => 'yes',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							'yes' => esc_html__( 'Yes', 'fusion-builder' ),
							'no'  => esc_html__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Rating Font Size', 'fusion-builder' ),
						'description' => esc_html__( 'Control the rating font size. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'text_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'display_rating_text',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Rating Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the color of the rating text.', 'fusion-builder' ),
						'param_name'  => 'text_font_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'display_rating_text',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Rating Number Rounding', 'fusion-builder' ),
						'description' => esc_html__( "Select how the rating number should be rounded. The 'auto' option will round to 2 decimals if needed or display the number until the left-most significant '0' decimal(Eg: instead of 4.70 will display 4.7).", 'fusion-builder' ),
						'param_name'  => 'rating_number_rounding',
						'default'     => 'auto',
						'value'       => [
							'auto'      => esc_html__( 'Auto', 'fusion-builder' ),
							'0decimals' => esc_html__( 'No Decimals', 'fusion-builder' ),
							'1decimal'  => esc_html__( '1 Decimal', 'fusion-builder' ),
							'2decimals' => esc_html__( '2 Decimals', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'display_rating_text',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Icon/Text Spacing', 'fusion-builder' ),
						'description' => esc_html__( 'Control the spacing between the icons and the rating text. Enter value including any valid CSS unit, ex: 15px.', 'fusion-builder' ),
						'param_name'  => 'icons_text_distance',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'display_rating_text',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_html__( 'Select the alignment of the icons and the text.', 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''           => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'flex-start' => esc_attr__( 'Left', 'fusion-builder' ),
							'center'     => esc_attr__( 'Center', 'fusion-builder' ),
							'flex-end'   => esc_attr__( 'Right', 'fusion-builder' ),
						],
					],
					'fusion_margin_placeholder'    => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'Design', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.awb-stars-rating',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_star_rating' );

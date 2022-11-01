<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_submit' ) ) {

	if ( ! class_exists( 'FusionForm_Submit' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_Submit extends Fusion_Form_Component {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.1
			 * @var array
			 */
			protected $args;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.1
			 * @var int
			 */
			public $counter = 0;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.1
			 */
			public function __construct() {
				parent::__construct( 'fusion_form_submit' );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'button_el_type'                     => 'submit',
					'hide_on_mobile'                     => fusion_builder_default_visibility( 'string' ),
					'tab_index'                          => '',
					'class'                              => '',
					'id'                                 => '',
					'accent_color'                       => ( '' !== $fusion_settings->get( 'button_accent_color' ) ) ? strtolower( $fusion_settings->get( 'button_accent_color' ) ) : '#ffffff',
					'accent_hover_color'                 => ( '' !== $fusion_settings->get( 'button_accent_hover_color' ) ) ? strtolower( $fusion_settings->get( 'button_accent_hover_color' ) ) : '#ffffff',
					'bevel_color'                        => ( '' !== $fusion_settings->get( 'button_bevel_color' ) ) ? strtolower( $fusion_settings->get( 'button_bevel_color' ) ) : '#54770F',
					'border_color'                       => ( '' !== $fusion_settings->get( 'button_border_color' ) ) ? strtolower( $fusion_settings->get( 'button_border_color' ) ) : '#ffffff',
					'border_hover_color'                 => ( '' !== $fusion_settings->get( 'button_border_hover_color' ) ) ? strtolower( $fusion_settings->get( 'button_border_hover_color' ) ) : '#ffffff',
					'color'                              => 'default',
					'gradient_colors'                    => '',
					'icon'                               => '',
					'icon_divider'                       => 'no',
					'icon_position'                      => 'left',
					'link'                               => '',
					'link_attributes'                    => '',
					'size'                               => '',
					'modal'                              => '',
					'margin_bottom'                      => '',
					'margin_left'                        => '',
					'margin_right'                       => '',
					'margin_top'                         => '',
					'stretch'                            => ( '' !== $fusion_settings->get( 'button_span' ) ) ? $fusion_settings->get( 'button_span' ) : 'no',
					'default_stretch_value'              => ( '' !== $fusion_settings->get( 'button_span' ) ) ? $fusion_settings->get( 'button_span' ) : 'no',
					'text_transform'                     => '',
					'type'                               => ( '' !== $fusion_settings->get( 'button_type' ) ) ? strtolower( $fusion_settings->get( 'button_type' ) ) : 'flat',
					'alignment'                          => '',
					'alignment_medium'                   => '',
					'alignment_small'                    => '',
					'animation_type'                     => '',
					'animation_direction'                => 'down',
					'animation_speed'                    => '',
					'animation_offset'                   => $fusion_settings->get( 'animation_offset' ),

					// Combined in accent_color.
					'icon_color'                         => '',
					'text_color'                         => '',

					// Combined in accent_hover_color.
					'icon_hover_color'                   => '',
					'text_hover_color'                   => '',

					'padding_top'                        => '',
					'padding_right'                      => '',
					'padding_bottom'                     => '',
					'padding_left'                       => '',
					'font_size'                          => '',
					'line_height'                        => '',
					'letter_spacing'                     => '',
					'fusion_font_family_button_font'     => '',
					'fusion_font_variant_button_font'    => '',
					'gradient_start_position'            => $fusion_settings->get( 'button_gradient_start' ),
					'gradient_end_position'              => $fusion_settings->get( 'button_gradient_end' ),
					'gradient_type'                      => $fusion_settings->get( 'button_gradient_type' ),
					'radial_direction'                   => $fusion_settings->get( 'button_radial_direction' ),
					'linear_angle'                       => $fusion_settings->get( 'button_gradient_angle' ),
					'border_radius_top_left'             => $fusion_settings->get( 'button_border_radius', 'top_left' ),
					'border_radius_top_right'            => $fusion_settings->get( 'button_border_radius', 'top_right' ),
					'border_radius_bottom_right'         => $fusion_settings->get( 'button_border_radius', 'bottom_right' ),
					'border_radius_bottom_left'          => $fusion_settings->get( 'button_border_radius', 'bottom_left' ),
					'border_top'                         => '',
					'border_right'                       => '',
					'border_bottom'                      => '',
					'border_left'                        => '',

					// Combined with gradient_colors.
					'gradient_hover_colors'              => '',

					'button_gradient_top_color'          => ( '' !== $fusion_settings->get( 'button_gradient_top_color' ) ) ? $fusion_settings->get( 'button_gradient_top_color' ) : '#65bc7b',
					'button_gradient_bottom_color'       => ( '' !== $fusion_settings->get( 'button_gradient_bottom_color' ) ) ? $fusion_settings->get( 'button_gradient_bottom_color' ) : '#65bc7b',
					'button_gradient_top_color_hover'    => ( '' !== $fusion_settings->get( 'button_gradient_top_color_hover' ) ) ? $fusion_settings->get( 'button_gradient_top_color_hover' ) : '#5aa86c',
					'button_gradient_bottom_color_hover' => ( '' !== $fusion_settings->get( 'button_gradient_bottom_color_hover' ) ) ? $fusion_settings->get( 'button_gradient_bottom_color_hover' ) : '#5aa86c',
					'button_accent_color'                => ( '' !== $fusion_settings->get( 'button_accent_color' ) ) ? $fusion_settings->get( 'button_accent_color' ) : '#ffffff',
					'button_accent_hover_color'          => ( '' !== $fusion_settings->get( 'button_accent_hover_color' ) ) ? $fusion_settings->get( 'button_accent_hover_color' ) : '#ffffff',
					'button_bevel_color'                 => ( '' !== $fusion_settings->get( 'button_bevel_color' ) ) ? $fusion_settings->get( 'button_bevel_color' ) : '#54770F',

					'sticky_display'                     => '',
				];
			}

			/**
			 * Render form field html.
			 *
			 * @access public
			 * @since 3.1
			 * @param string $content The content.
			 * @return string
			 */
			public function render_input_field( $content ) {
				global $shortcode_tags;

				$html = '';

				$this->args['link_attributes'] .= " data-form-number='" . $this->params['form_number'] . "'";
				$this->args['button_el_type']   = 'submit';

				if ( ! fusion_is_preview_frame() ) {
					$this->args['class'] .= ' form-form-submit button-default';
				}

				$button_html = call_user_func( $shortcode_tags['fusion_button'], $this->args, $content, 'fusion_button' );

				$html .= $button_html;

				return $html;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.1
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/form/submit.min.css' );
			}
		}
	}

	new FusionForm_Submit();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_submit() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Submit',
			[
				'name'                => esc_attr__( 'Submit Button', 'fusion-builder' ),
				'shortcode'           => 'fusion_form_submit',
				'form_component'      => true,
				'components_per_form' => 1,
				'icon'                => 'fusiona-check-empty',
				'preview'             => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-button-preview.php',
				'preview_id'          => 'fusion-builder-block-module-button-preview-template',
				'subparam_map'        => [
					'fusion_font_family_button_font'  => 'main_typography',
					'fusion_font_variant_button_font' => 'main_typography',
					'font_size'                       => 'main_typography',
					'line_height'                     => 'main_typography',
					'letter_spacing'                  => 'main_typography',
					'text_transform'                  => 'main_typography',
				],
				'params'              => [
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Button Text', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Submit', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the text that will display on button.', 'fusion-builder' ),
						'dynamic_data' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Button Additional Attributes', 'fusion-builder' ),
						'param_name'  => 'link_attributes',
						'value'       => '',
						'description' => esc_attr__( "Add additional attributes to the anchor tag. Separate attributes with a whitespace and use single quotes on the values, doubles don't work. If you need to add square brackets, [ ], to your attributes, please use curly brackets, { }, instead. They will be replaced correctly on the frontend. ex: rel='nofollow'.", 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( "Select the button's alignment.", 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => '',
						'responsive'  => [
							'state' => 'large',
						],
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Button Style', 'fusion-builder' ),
						'description' => esc_attr__( "Select the button's color. Select default or color name for Global Options, or select custom to use advanced color options below.", 'fusion-builder' ),
						'param_name'  => 'color',
						'value'       => [
							'default'   => esc_attr__( 'Default', 'fusion-builder' ),
							'custom'    => esc_attr__( 'Custom', 'fusion-builder' ),
							'green'     => esc_attr__( 'Green', 'fusion-builder' ),
							'darkgreen' => esc_attr__( 'Dark Green', 'fusion-builder' ),
							'orange'    => esc_attr__( 'Orange', 'fusion-builder' ),
							'blue'      => esc_attr__( 'Blue', 'fusion-builder' ),
							'red'       => esc_attr__( 'Red', 'fusion-builder' ),
							'pink'      => esc_attr__( 'Pink', 'fusion-builder' ),
							'darkgray'  => esc_attr__( 'Dark Gray', 'fusion-builder' ),
							'lightgray' => esc_attr__( 'Light Gray', 'fusion-builder' ),
						],
						'default'     => 'default',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Top Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the top color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_top_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_top_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Bottom Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the bottom color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_bottom_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Top Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the top hover color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_top_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_top_color_hover' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Bottom Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the bottom hover color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_bottom_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient Start Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select start position for gradient.', 'fusion-builder' ),
						'param_name'  => 'gradient_start_position',
						'default'     => $fusion_settings->get( 'button_gradient_start' ),
						'value'       => '',
						'min'         => '0',
						'max'         => '100',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient End Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select end position for gradient.', 'fusion-builder' ),
						'param_name'  => 'gradient_end_position',
						'default'     => $fusion_settings->get( 'button_gradient_end' ),
						'value'       => '100',
						'min'         => '',
						'max'         => '100',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Gradient Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls gradient type.', 'fusion-builder' ),
						'param_name'  => 'gradient_type',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'linear' => esc_attr__( 'Linear', 'fusion-builder' ),
							'radial' => esc_attr__( 'Radial', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Radial Direction', 'fusion-builder' ),
						'description' => esc_attr__( 'Select direction for radial gradient.', 'fusion-builder' ),
						'param_name'  => 'radial_direction',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''              => esc_attr__( 'Default', 'fusion-builder' ),
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
						'dependency'  => [
							[
								'element'  => 'gradient_type',
								'value'    => 'linear',
								'operator' => '!=',
							],
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient Angle', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gradient angle. In degrees.', 'fusion-builder' ),
						'param_name'  => 'linear_angle',
						'default'     => $fusion_settings->get( 'button_gradient_angle' ),
						'value'       => '180',
						'min'         => '',
						'max'         => '360',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'gradient_type',
								'value'    => 'radial',
								'operator' => '!=',
							],
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the button text, divider and icon.', 'fusion-builder' ),
						'param_name'  => 'accent_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_accent_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Accent Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover color of the button text, divider and icon.', 'fusion-builder' ),
						'param_name'  => 'accent_hover_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_accent_hover_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the button type.', 'fusion-builder' ),
						'param_name'  => 'type',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''     => esc_attr__( 'Default', 'fusion-builder' ),
							'flat' => esc_attr__( 'Flat', 'fusion-builder' ),
							'3d'   => esc_attr__( '3D', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Bevel Color For 3D Mode', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the bevel color of the button when using 3D button type.', 'fusion-builder' ),
						'param_name'  => 'bevel_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_bevel_color' ),
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'flat',
								'operator' => '!=',
							],
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Button Border Size', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the border size. In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'border_width',
						'value'            => [
							'border_top'    => '',
							'border_right'  => '',
							'border_bottom' => '',
							'border_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Button Border Radius', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the border radius. Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'border_radius',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'border_radius_top_left'     => '',
							'border_radius_top_right'    => '',
							'border_radius_bottom_right' => '',
							'border_radius_bottom_left'  => '',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the button.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover border color of the button.', 'fusion-builder' ),
						'param_name'  => 'border_hover_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_hover_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the button size.', 'fusion-builder' ),
						'param_name'  => 'size',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'small'  => esc_attr__( 'Small', 'fusion-builder' ),
							'medium' => esc_attr__( 'Medium', 'fusion-builder' ),
							'large'  => esc_attr__( 'Large', 'fusion-builder' ),
							'xlarge' => esc_attr__( 'XLarge', 'fusion-builder' ),
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the padding for the button.', 'fusion-builder' ),
						'param_name'       => 'padding',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
						'dependency'       => [
							[
								'element'  => 'size',
								'value'    => '',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Typography', 'fusion-builder' ),
						/* translators: URL for the link. */
						'description'      => esc_html__( 'Controls the button typography, if left empty will inherit from globals.', 'fusion-builder' ),
						'param_name'       => 'main_typography',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'button_font',
							'font-size'      => 'font_size',
							'line-height'    => 'line_height',
							'letter-spacing' => 'letter_spacing',
							'text-transform' => 'text_transform',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => '',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Span', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the button spans the full width of its container.', 'fusion-builder' ),
						'param_name'  => 'stretch',
						'default'     => 'default',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							'default' => esc_attr__( 'Default', 'fusion-builder' ),
							'yes'     => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'      => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					'fusion_margin_placeholder'    => [
						'param_name' => 'margin',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the position of the icon on the button.', 'fusion-builder' ),
						'param_name'  => 'icon_position',
						'value'       => [
							'left'  => esc_attr__( 'Left', 'fusion-builder' ),
							'right' => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => 'left',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Divider', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to display a divider between icon and text.', 'fusion-builder' ),
						'param_name'  => 'icon_divider',
						'default'     => 'no',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon',
								'value'    => '',
								'operator' => '!=',
							],
						],
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-button',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Tab Index', 'fusion-builder' ),
						'param_name'  => 'tab_index',
						'value'       => '',
						'description' => esc_attr__( 'Tab index for this field.', 'fusion-builder' ),
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
						'type'        => 'hidden',
						'heading'     => esc_attr__( 'Form ID', 'fusion-builder' ),
						'param_name'  => 'form_id',
						'value'       => '',
						'description' => esc_attr__( 'Contains active form ID.', 'fusion-builder' ),
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_form_submit' );

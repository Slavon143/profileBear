<?php
/**
 * Off Canvas design Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage off-canvas
 */

/**
 * Off Canvas design settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_off_canvas_design( $sections ) {
	$sections['off_canvas_design'] = [
		'label'    => esc_html__( 'Design', 'Avada' ),
		'alt_icon' => 'fusiona-customize',
		'id'       => 'off_canvas_design',
		'fields'   => [
			'background_color'          => [
				'id'          => 'background_color',
				'label'       => esc_attr__( 'Background Color', 'Avada' ),
				'description' => esc_html__( 'Choose the background color. Leave empty for default value of #ffffff.', 'Avada' ),
				'default'     => '#ffffff',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'type'        => 'color-alpha',
			],
			'background_image'          => [
				'id'          => 'background_image',
				'label'       => esc_attr__( 'Background Image', 'Avada' ),
				'description' => esc_html__( 'Upload an image to display in the background.', 'Avada' ),
				'default'     => '',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'type'        => 'upload',
			],
			'background_position'       => [
				'id'          => 'background_position',
				'label'       => esc_attr__( 'Background Position', 'Avada' ),
				'description' => esc_attr__( 'Choose how the background image is positioned.', 'Avada' ),
				'default'     => 'left top',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'type'        => 'select',
				'choices'     => [
					'left top'      => esc_attr__( 'Left Top', 'Avada' ),
					'left center'   => esc_attr__( 'Left Center', 'Avada' ),
					'left bottom'   => esc_attr__( 'Left Bottom', 'Avada' ),
					'right top'     => esc_attr__( 'Right Top', 'Avada' ),
					'right center'  => esc_attr__( 'Right Center', 'Avada' ),
					'right bottom'  => esc_attr__( 'Right Bottom', 'Avada' ),
					'center top'    => esc_attr__( 'Center Top', 'Avada' ),
					'center center' => esc_attr__( 'Center Center', 'Avada' ),
					'center bottom' => esc_attr__( 'Center Bottom', 'Avada' ),
				],
				'dependency'  => [
					[
						'field'      => 'background_image',
						'value'      => '',
						'comparison' => '!=',
					],
				],
			],
			'background_repeat'         => [
				'id'          => 'background_repeat',
				'label'       => esc_attr__( 'Background Repeat', 'Avada' ),
				'description' => esc_attr__( 'Select how the background image repeats.', 'Avada' ),
				'default'     => 'repeat',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'type'        => 'select',
				'choices'     => [
					'no-repeat' => esc_attr__( 'No Repeat', 'Avada' ),
					'repeat'    => esc_attr__( 'Repeat Vertically and Horizontally', 'Avada' ),
					'repeat-x'  => esc_attr__( 'Repeat Horizontally', 'Avada' ),
					'repeat-y'  => esc_attr__( 'Repeat Vertically', 'Avada' ),
				],
				'dependency'  => [
					[
						'field'      => 'background_image',
						'value'      => '',
						'comparison' => '!=',
					],
					[
						'field'      => 'background_size',
						'value'      => 'cover',
						'comparison' => '!=',
					],
				],
			],
			'background_size'           => [
				'id'          => 'background_size',
				'label'       => esc_attr__( 'Background Size', 'Avada' ),
				'description' => esc_attr__( 'Select the background image size.', 'Avada' ),
				'default'     => 'auto',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'type'        => 'select',
				'choices'     => [
					'auto'    => esc_attr__( 'Auto', 'Avada' ),
					'cover'   => esc_attr__( 'Cover', 'Avada' ),
					'contain' => esc_attr__( 'Contain', 'Avada' ),
					'custom'  => esc_attr__( 'Custom', 'Avada' ),
				],
				'dependency'  => [
					[
						'field'      => 'background_image',
						'value'      => '',
						'comparison' => '!=',
					],
				],
			],
			'background_custom_size'    => [
				'label'       => esc_html__( 'Background Custom Size', 'Avada' ),
				'description' => esc_html__( 'Use any valid CSS value ex. 500px, 50%, 60vw.', 'Avada' ),
				'id'          => 'background_custom_size',
				'type'        => 'dimensions',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'value'       => [
					'width'  => '',
					'height' => '',
				],
				'dependency'  => [
					[
						'field'      => 'background_image',
						'value'      => '',
						'comparison' => '!=',
					],
					[
						'field'      => 'background_size',
						'value'      => 'custom',
						'comparison' => '==',
					],
				],
			],
			'background_blend_mode'     => [
				'id'          => 'background_blend_mode',
				'label'       => esc_attr__( 'Background Blend Mode', 'Avada' ),
				'description' => esc_attr__( 'Choose how blending should work for each background layer.', 'Avada' ),
				'default'     => 'none',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'type'        => 'select',
				'choices'     => [
					'none'        => esc_attr__( 'Disabled', 'Avada' ),
					'multiply'    => esc_attr__( 'Multiply', 'Avada' ),
					'screen'      => esc_attr__( 'Screen', 'Avada' ),
					'overlay'     => esc_attr__( 'Overlay', 'Avada' ),
					'darken'      => esc_attr__( 'Darken', 'Avada' ),
					'lighten'     => esc_attr__( 'Lighten', 'Avada' ),
					'color-dodge' => esc_attr__( 'Color Dodge', 'Avada' ),
					'color-burn'  => esc_attr__( 'Color Burn', 'Avada' ),
					'hard-light'  => esc_attr__( 'Hard Light', 'Avada' ),
					'soft-light'  => esc_attr__( 'Soft Light', 'Avada' ),
					'difference'  => esc_attr__( 'Difference', 'Avada' ),
					'exclusion'   => esc_attr__( 'Exclusion', 'Avada' ),
					'hue'         => esc_attr__( 'Hue', 'Avada' ),
					'saturation'  => esc_attr__( 'Saturation', 'Avada' ),
					'color'       => esc_attr__( 'Color', 'Avada' ),
					'luminosity'  => esc_attr__( 'Luminosity', 'Avada' ),
				],
				'dependency'  => [
					[
						'field'      => 'background_image',
						'value'      => '',
						'comparison' => '!=',
					],
				],
			],
			'oc_scrollbar'              => [
				'id'          => 'oc_scrollbar',
				'label'       => esc_attr__( 'Scrollbar', 'Avada' ),
				'description' => esc_attr__( 'Hide or customize Off Canvas scrollbar. Styling and support varies depending on the browser.', 'Avada' ),
				'default'     => 'default',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'type'        => 'radio-buttonset',
				'choices'     => [
					'default' => esc_attr__( 'Default', 'Avada' ),
					'custom'  => esc_attr__( 'Custom', 'Avada' ),
					'hidden'  => esc_attr__( 'Hidden', 'Avada' ),
				],
			],
			'oc_scrollbar_background'   => [
				'id'          => 'oc_scrollbar_background',
				'label'       => esc_attr__( 'Scrollbar Background Color', 'Avada' ),
				'description' => esc_attr__( 'Choose the background color of the scrollbar.', 'Avada' ),
				'default'     => '#f2f3f5',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'type'        => 'color-alpha',
				'dependency'  => [
					[
						'field'      => 'oc_scrollbar',
						'value'      => 'custom',
						'comparison' => '==',
					],
				],
			],
			'oc_scrollbar_handle_color' => [
				'id'          => 'oc_scrollbar_handle_color',
				'label'       => esc_attr__( 'Scrollbar Handle Color', 'Avada' ),
				'description' => esc_attr__( 'Choose the scrollbar handle color.', 'Avada' ),
				'default'     => '#65bc7b',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'type'        => 'color-alpha',
				'dependency'  => [
					[
						'field'      => 'oc_scrollbar',
						'value'      => 'custom',
						'comparison' => '==',
					],
				],
			],
			'border_radius'             => [
				'label'       => esc_html__( 'Border Radius', 'Avada' ),
				'description' => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'Avada' ),
				'id'          => 'border_radius',
				'type'        => 'dimensions',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'value'       => [
					'top_left'     => '',
					'top_right'    => '',
					'bottom_right' => '',
					'bottom_left'  => '',
				],
			],
			'border_width'              => [
				'label'       => esc_html__( 'Border Width', 'Avada' ),
				'description' => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'Avada' ),
				'id'          => 'border_width',
				'type'        => 'dimensions',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'value'       => [
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
			],
			'border_color'              => [
				'label'       => esc_html__( 'Border Color', 'Avada' ),
				'description' => esc_html__( 'Set the border color.', 'Avada' ),
				'id'          => 'border_color',
				'type'        => 'color-alpha',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
			],
			'margin'                    => [
				'label'       => esc_html__( 'Margin', 'Avada' ),
				'description' => esc_html__( 'Controls the Off Canvas margin.', 'Avada' ),
				'id'          => 'margin',
				'type'        => 'dimensions',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'value'       => [
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
			],
			'padding'                   => [
				'label'       => esc_html__( 'Padding', 'Avada' ),
				'description' => esc_html__( 'Controls the Off Canvas padding.', 'Avada' ),
				'id'          => 'padding',
				'type'        => 'dimensions',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'value'       => [
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
			],
			'box_shadow'                => [
				'id'          => 'box_shadow',
				'label'       => esc_attr__( 'Box Shadow', 'Avada' ),
				'description' => esc_html__( 'Enable/Disable box shadow.', 'Avada' ),
				'default'     => 'no',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'type'        => 'radio-buttonset',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
			],
			'box_shadow_position'       => [
				'label'       => esc_html__( 'Box Shadow Position', 'Avada' ),
				'description' => esc_html__( 'Set the vertical and horizontal position of the box shadow. Positive values put the shadow below and right of the box, negative values put it above and left of the box. In pixels, ex. 5px.', 'Avada' ),
				'id'          => 'box_shadow_position',
				'type'        => 'dimensions',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'value'       => [
					'vertical'   => '',
					'horizontal' => '',
				],
				'dependency'  => [
					[
						'field'      => 'box_shadow',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			'box_shadow_blur'           => [
				'type'        => 'slider',
				'label'       => esc_html__( 'Box Shadow Blur Radius', 'Avada' ),
				'description' => esc_attr__( 'Set the blur radius of the box shadow. In pixels.', 'Avada' ),
				'id'          => 'box_shadow_blur',
				'default'     => 0,
				'choices'     => [
					'step' => 1,
					'min'  => 0,
					'max'  => 100,
				],
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'dependency'  => [
					[
						'field'      => 'box_shadow',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			'box_shadow_spread'         => [
				'type'        => 'slider',
				'label'       => esc_html__( 'Box Shadow Spread Radius', 'Avada' ),
				'description' => esc_attr__( 'Set the spread radius of the box shadow. In pixels.', 'Avada' ),
				'id'          => 'box_shadow_spread',
				'default'     => 0,
				'choices'     => [
					'step' => 1,
					'min'  => 0,
					'max'  => 100,
				],
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'dependency'  => [
					[
						'field'      => 'box_shadow',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			'box_shadow_color'          => [
				'type'        => 'color-alpha',
				'label'       => esc_html__( 'Box Shadow Color', 'Avada' ),
				'description' => esc_attr__( 'Set the color of the box shadow.', 'Avada' ),
				'id'          => 'box_shadow_color',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'dependency'  => [
					[
						'field'      => 'box_shadow',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
		],
	];

	return apply_filters( 'avada_off_canvas_design_sections', $sections );

}

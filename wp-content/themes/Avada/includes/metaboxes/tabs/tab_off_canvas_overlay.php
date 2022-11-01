<?php
/**
 * Off Canvas overlay Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage off-canvas
 */

/**
 * Off Canvas overlay settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_off_canvas_overlay( $sections ) {
	$sections['off_canvas_overlay'] = [
		'label'    => esc_html__( 'Overlay', 'Avada' ),
		'alt_icon' => 'fusiona-overlay',
		'id'       => 'off_canvas_overlay',
		'fields'   => [
			'overlay'                        => [
				'id'          => 'overlay',
				'label'       => esc_attr__( 'Overlay', 'Avada' ),
				'description' => esc_html__( 'Enable/Disable overlay.', 'Avada' ),
				'default'     => 'yes',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
					'awb-off-canvas-attr',
				],
				'type'        => 'radio-buttonset',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
			],
			'overlay_z_index'                => [
				'type'        => 'text',
				'label'       => esc_html__( 'Z-Index', 'Avada' ),
				'description' => esc_attr__( 'Enter the value for overlay\'s z-index CSS property, can be both positive or negative.', 'Avada' ),
				'id'          => 'overlay_z_index',
				'default'     => '',
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'overlay',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
				'events'      => [
					'awb-off-canvas-styles',
				],
			],
			'overlay_page_scrollbar'         => [
				'id'          => 'overlay_page_scrollbar',
				'label'       => esc_attr__( 'Page Scrollbar', 'Avada' ),
				'description' => esc_html__( 'Enable/Disable page scrollbar when Off Canvas is active.', 'Avada' ),
				'default'     => 'yes',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'type'        => 'radio-buttonset',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
				'dependency'  => [
					[
						'field'      => 'overlay',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			'overlay_background_color'       => [
				'id'          => 'overlay_background_color',
				'label'       => esc_attr__( 'Background Color', 'Avada' ),
				'description' => esc_html__( 'Choose the background color of the overlay. Leave empty for default value of rgba(0,0,0,0.8).', 'Avada' ),
				'default'     => 'rgba(0,0,0,0.8)',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'type'        => 'color-alpha',
				'dependency'  => [
					[
						'field'      => 'overlay',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			'overlay_background_image'       => [
				'id'          => 'overlay_background_image',
				'label'       => esc_attr__( 'Background Image', 'Avada' ),
				'description' => esc_html__( 'Upload an image to display in the background.', 'Avada' ),
				'default'     => '',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'type'        => 'upload',
				'dependency'  => [
					[
						'field'      => 'overlay',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			'overlay_background_position'    => [
				'id'          => 'overlay_background_position',
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
						'field'      => 'overlay',
						'value'      => 'yes',
						'comparison' => '==',
					],
					[
						'field'      => 'overlay_background_image',
						'value'      => '',
						'comparison' => '!=',
					],
				],
			],
			'overlay_background_repeat'      => [
				'id'          => 'overlay_background_repeat',
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
						'field'      => 'overlay',
						'value'      => 'yes',
						'comparison' => '==',
					],
					[
						'field'      => 'overlay_background_image',
						'value'      => '',
						'comparison' => '!=',
					],
					[
						'field'      => 'overlay_background_size',
						'value'      => 'cover',
						'comparison' => '!=',
					],
				],
			],
			'overlay_background_size'        => [
				'id'          => 'overlay_background_size',
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
						'field'      => 'overlay',
						'value'      => 'yes',
						'comparison' => '==',
					],
					[
						'field'      => 'overlay_background_image',
						'value'      => '',
						'comparison' => '!=',
					],
				],
			],
			'overlay_background_custom_size' => [
				'label'       => esc_html__( 'Background Custom Size', 'Avada' ),
				'description' => esc_html__( 'Use any valid CSS value ex. 500px, 50%, 60vw.', 'Avada' ),
				'id'          => 'overlay_background_custom_size',
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
						'field'      => 'overlay',
						'value'      => 'yes',
						'comparison' => '==',
					],
					[
						'field'      => 'overlay_background_image',
						'value'      => '',
						'comparison' => '!=',
					],
					[
						'field'      => 'overlay_background_size',
						'value'      => 'custom',
						'comparison' => '==',
					],
				],
			],
			'overlay_background_blend_mode'  => [
				'id'          => 'overlay_background_blend_mode',
				'label'       => esc_attr__( 'Background Blend Mode', 'Avada' ),
				'description' => esc_attr__( 'Choose how blending should work for each background layer.', 'Avada' ),
				'default'     => 'none',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'type'        => 'select',
				'choices'     => [
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
				'dependency'  => [
					[
						'field'      => 'overlay',
						'value'      => 'yes',
						'comparison' => '==',
					],
					[
						'field'      => 'overlay_background_image',
						'value'      => '',
						'comparison' => '!=',
					],
				],
			],
		],
	];

	return apply_filters( 'avada_off_canvas_overlay_sections', $sections );

}

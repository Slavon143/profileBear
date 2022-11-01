<?php
/**
 * Off Canvas close button Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage off-canvas
 */

/**
 * Off Canvas close button settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_off_canvas_close( $sections ) {
	$sections['off_canvas_close'] = [
		'label'    => esc_html__( 'Close', 'Avada' ),
		'alt_icon' => 'fusiona-cross',
		'id'       => 'off_canvas_close',
		'fields'   => [
			'overlay_close_on_click'       => [
				'id'          => 'overlay_close_on_click',
				'label'       => esc_attr__( 'Close On Overlay Click', 'Avada' ),
				'description' => esc_html__( 'Off Canvas is closed by clicking on overlay.', 'Avada' ),
				'default'     => 'yes',
				'transport'   => 'postMessage',
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
			'close_on_esc'                 => [
				'id'          => 'close_on_esc',
				'label'       => esc_attr__( 'Close With ESC Key', 'Avada' ),
				'description' => esc_html__( 'Enable/Disable. When enabled, you can use the ESC button to close the Off Canvas.', 'Avada' ),
				'default'     => 'yes',
				'transport'   => 'postMessage',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
			],
			'auto_close_after_time'        => [
				'id'          => 'auto_close_after_time',
				'label'       => esc_attr__( 'Automatically Close After Time', 'Avada' ),
				'description' => esc_html__( 'Set time in seconds to close Off Canvas automatically after it has passed, Leave empty to remain open.', 'Avada' ),
				'transport'   => 'postMessage',
				'type'        => 'text',
			],
			'close_button'                 => [
				'id'          => 'close_button',
				'label'       => esc_attr__( 'Close Button', 'Avada' ),
				'description' => esc_html__( 'Enable/Disable close button.', 'Avada' ),
				'default'     => 'yes',
				'transport'   => 'postMessage',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
				'events'      => [
					'awb-off-canvas-styles',
				],
			],
			'close_button_position'        => [
				'id'          => 'close_button_position',
				'label'       => esc_attr__( 'Position', 'Avada' ),
				'description' => esc_html__( 'Set close button position.', 'Avada' ),
				'default'     => 'right',
				'transport'   => 'postMessage',
				'type'        => 'radio-buttonset',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'choices'     => [
					'right' => esc_html__( 'Right', 'Avada' ),
					'left'  => esc_html__( 'Left', 'Avada' ),
				],
				'dependency'  => [
					[
						'field'      => 'close_button',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			'show_close_button_after_time' => [
				'id'          => 'show_close_button_after_time',
				'label'       => esc_attr__( 'Show Close Button After Time', 'Avada' ),
				'description' => esc_html__( 'Set time in seconds to show the close button, Leave empty to immediately show.', 'Avada' ),
				'transport'   => 'postMessage',
				'type'        => 'text',
				'dependency'  => [
					[
						'field'      => 'close_button',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			'close_button_margin'          => [
				'label'       => esc_html__( 'Close Button Margin', 'Avada' ),
				'description' => esc_html__( 'Set the margin of the close button. Default is 20px from the top and the selected position side. Enter values including any valid CSS unit. Negative values are accepted.', 'Avada' ),
				'id'          => 'close_button_margin',
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
				'dependency'  => [
					[
						'field'      => 'close_button',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			'close_button_color'           => [
				'id'          => 'close_button_color',
				'label'       => esc_attr__( 'Close Icon Color', 'Avada' ),
				'description' => esc_html__( 'Set the color of the close icon.', 'Avada' ),
				'default'     => '',
				'transport'   => 'postMessage',
				'type'        => 'color-alpha',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'dependency'  => [
					[
						'field'      => 'close_button',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			'close_button_color_hover'     => [
				'id'          => 'close_button_color_hover',
				'label'       => esc_attr__( 'Close Icon Hover Color', 'Avada' ),
				'description' => esc_html__( 'Set the hover color of the close icon.', 'Avada' ),
				'default'     => '',
				'transport'   => 'postMessage',
				'type'        => 'color-alpha',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'dependency'  => [
					[
						'field'      => 'close_button',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			'close_icon_size'              => [
				'label'       => esc_html__( 'Close Icon Size', 'fusion-builder' ),
				'description' => esc_html__( 'Set the size of the close icon.', 'fusion-builder' ),
				'id'          => 'close_icon_size',
				'default'     => '16',
				'type'        => 'slider',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'choices'     => [
					'min'  => '1',
					'max'  => '100',
					'step' => '1',
				],
				'dependency'  => [
					[
						'field'      => 'close_button',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			'close_button_custom_icon'     => [
				'id'          => 'close_button_custom_icon',
				'label'       => esc_attr__( 'Custom Icon', 'Avada' ),
				'description' => esc_html__( 'Select a custom icon for the close button.', 'Avada' ),
				'default'     => '',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-custom-close-button',
				],
				'type'        => 'iconpicker',
				'dependency'  => [
					[
						'field'      => 'close_button',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
		],
	];

	return apply_filters( 'avada_off_canvas_close_sections', $sections );

}

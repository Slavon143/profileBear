<?php
/**
 * Off Canvas animation Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage off-canvas
 */

/**
 * Off Canvas animation settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_off_canvas_animation( $sections ) {
	$enter_animations = [
		''             => esc_attr__( 'None', 'fusion-builder' ),
		'bounce'       => esc_attr__( 'Bounce', 'fusion-builder' ),
		'fade'         => esc_attr__( 'Fade', 'fusion-builder' ),
		'flash'        => esc_attr__( 'Flash', 'fusion-builder' ),
		'rubberBand'   => esc_attr__( 'Rubberband', 'fusion-builder' ),
		'shake'        => esc_attr__( 'Shake', 'fusion-builder' ),
		'slide'        => esc_attr__( 'Slide', 'fusion-builder' ),
		'zoom'         => esc_attr__( 'Zoom', 'fusion-builder' ),
		'flipinx'      => esc_attr__( 'Flip Vertically', 'fusion-builder' ),
		'flipiny'      => esc_attr__( 'Flip Horizontally', 'fusion-builder' ),
		'lightspeedin' => esc_attr__( 'Light Speed', 'fusion-builder' ),
	];

	$exit_animations = [
		''              => esc_attr__( 'None', 'fusion-builder' ),
		'bounce'        => esc_attr__( 'Bounce', 'fusion-builder' ),
		'fade'          => esc_attr__( 'Fade', 'fusion-builder' ),
		'slide'         => esc_attr__( 'Slide', 'fusion-builder' ),
		'zoom'          => esc_attr__( 'Zoom', 'fusion-builder' ),
		'flipOutX'      => esc_attr__( 'Flip Vertically', 'fusion-builder' ),
		'flipOutY'      => esc_attr__( 'Flip Horizontally', 'fusion-builder' ),
		'lightSpeedOut' => esc_attr__( 'Light Speed', 'fusion-builder' ),
	];

	$sections['off_canvas_animation'] = [
		'label'    => esc_html__( 'Animation', 'Avada' ),
		'alt_icon' => 'fusiona-extras',
		'id'       => 'off_canvas_animation',
		'fields'   => [
			'enter_animation'           => [
				'type'        => 'select',
				'label'       => esc_html__( 'Entrance Animation', 'Avada' ),
				'description' => esc_attr__( 'Set the Off Canvas entrance animation.', 'Avada' ),
				'id'          => 'enter_animation',
				'default'     => '',
				'choices'     => $enter_animations,
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-enter-animation',
				],
				'dependency'  => [
					[
						'field'      => 'type',
						'value'      => 'sliding-bar',
						'comparison' => '!=',
					],
				],
			],
			'enter_animation_direction' => [
				'type'        => 'select',
				'label'       => esc_attr__( 'Direction Of Entrance Animation', 'Avada' ),
				'description' => esc_attr__( 'Select the direction of the entrance animation.', 'Avada' ),
				'id'          => 'enter_animation_direction',
				'default'     => 'static',
				'choices'     => [
					'down'   => esc_attr__( 'Top', 'fusion-builder' ),
					'right'  => esc_attr__( 'Right', 'fusion-builder' ),
					'up'     => esc_attr__( 'Bottom', 'fusion-builder' ),
					'left'   => esc_attr__( 'Left', 'fusion-builder' ),
					'static' => esc_attr__( 'Static', 'fusion-builder' ),
				],
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-enter-animation',
				],
				'dependency'  => [
					[
						'field'      => 'enter_animation',
						'value'      => '',
						'comparison' => '!=',
					],
					[
						'field'      => 'enter_animation',
						'value'      => 'flash',
						'comparison' => '!=',
					],
					[
						'field'      => 'enter_animation',
						'value'      => 'shake',
						'comparison' => '!=',
					],
					[
						'field'      => 'enter_animation',
						'value'      => 'rubberBand',
						'comparison' => '!=',
					],
					[
						'field'      => 'enter_animation',
						'value'      => 'flipinx',
						'comparison' => '!=',
					],
					[
						'field'      => 'enter_animation',
						'value'      => 'flipiny',
						'comparison' => '!=',
					],
					[
						'field'      => 'enter_animation',
						'value'      => 'lightspeedin',
						'comparison' => '!=',
					],
					[
						'field'      => 'type',
						'value'      => 'sliding-bar',
						'comparison' => '!=',
					],
				],
			],
			'enter_animation_speed'     => [
				'type'        => 'slider',
				'label'       => esc_html__( 'Entrance Animation Speed', 'Avada' ),
				'description' => esc_attr__( 'Set the speed of the entrance animation in seconds (0.1 - 5).', 'Avada' ),
				'id'          => 'enter_animation_speed',
				'default'     => 0.5,
				'choices'     => [
					'step' => 0.1,
					'min'  => 0.1,
					'max'  => 5,
				],
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-enter-animation',
				],
				'dependency'  => [
					[
						'field'      => 'enter_animation',
						'value'      => '',
						'comparison' => '!=',
					],
					[
						'field'      => 'type',
						'value'      => 'sliding-bar',
						'comparison' => '!=',
					],
				],
			],
			'exit_animation'            => [
				'type'        => 'select',
				'label'       => esc_html__( 'Exit Animation', 'Avada' ),
				'description' => esc_attr__( 'Set the Off Canvas exit animation.', 'Avada' ),
				'id'          => 'exit_animation',
				'default'     => '',
				'choices'     => $exit_animations,
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-exit-animation',
				],
				'dependency'  => [
					[
						'field'      => 'type',
						'value'      => 'sliding-bar',
						'comparison' => '!=',
					],
				],
			],
			'exit_animation_direction'  => [
				'type'        => 'select',
				'label'       => esc_attr__( 'Direction Of Exit Animation', 'Avada' ),
				'description' => esc_attr__( 'Select the direction of the exit animation.', 'Avada' ),
				'id'          => 'exit_animation_direction',
				'default'     => 'static',
				'choices'     => [
					'down'   => esc_attr__( 'Top', 'fusion-builder' ),
					'right'  => esc_attr__( 'Right', 'fusion-builder' ),
					'up'     => esc_attr__( 'Bottom', 'fusion-builder' ),
					'left'   => esc_attr__( 'Left', 'fusion-builder' ),
					'static' => esc_attr__( 'Static', 'fusion-builder' ),
				],
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-exit-animation',
				],
				'dependency'  => [
					[
						'field'      => 'exit_animation',
						'value'      => '',
						'comparison' => '!=',
					],
					[
						'field'      => 'exit_animation',
						'value'      => 'flipOutX',
						'comparison' => '!=',
					],
					[
						'field'      => 'exit_animation',
						'value'      => 'flipOutY',
						'comparison' => '!=',
					],
					[
						'field'      => 'exit_animation',
						'value'      => 'lightSpeedOut',
						'comparison' => '!=',
					],
					[
						'field'      => 'exit_animation',
						'value'      => '',
						'comparison' => '!=',
					],
					[
						'field'      => 'type',
						'value'      => 'sliding-bar',
						'comparison' => '!=',
					],
				],
			],
			'exit_animation_speed'      => [
				'type'        => 'slider',
				'label'       => esc_html__( 'Exit Animation Speed', 'Avada' ),
				'description' => esc_attr__( 'Set the speed of the exit animation in seconds (0.1 - 5).', 'Avada' ),
				'id'          => 'exit_animation_speed',
				'default'     => 0.5,
				'choices'     => [
					'step' => 0.1,
					'min'  => 0.1,
					'max'  => 5,
				],
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-exit-animation',
				],
				'dependency'  => [
					[
						'field'      => 'exit_animation',
						'value'      => '',
						'comparison' => '!=',
					],
					[
						'field'      => 'type',
						'value'      => 'sliding-bar',
						'comparison' => '!=',
					],
				],
			],
			// sliding bar animations.
			'sb_enter_animation'        => [
				'type'        => 'select',
				'label'       => esc_html__( 'Entrance Animation', 'Avada' ),
				'description' => esc_attr__( 'Set the Off Canvas entrance animation.', 'Avada' ),
				'id'          => 'sb_enter_animation',
				'default'     => 'slideShort',
				'choices'     => [
					'slideShort' => esc_html__( 'Slide', 'Avada' ),
					'bounce'     => esc_html__( 'Bounce', 'Avada' ),
					'fade'       => esc_html__( 'Fade', 'Avada' ),
				],
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-enter-animation',
				],
				'dependency'  => [
					[
						'field'      => 'type',
						'value'      => 'sliding-bar',
						'comparison' => '==',
					],
				],
			],
			'sb_enter_animation_speed'  => [
				'type'        => 'slider',
				'label'       => esc_html__( 'Entrance Animation Speed', 'Avada' ),
				'description' => esc_attr__( 'Set the speed of the entrance animation in seconds (0.1 - 5).', 'Avada' ),
				'id'          => 'sb_enter_animation_speed',
				'default'     => 0.5,
				'choices'     => [
					'step' => 0.1,
					'min'  => 0.1,
					'max'  => 5,
				],
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-enter-animation',
				],
				'dependency'  => [
					[
						'field'      => 'sb_enter_animation',
						'value'      => '',
						'comparison' => '!=',
					],
					[
						'field'      => 'type',
						'value'      => 'sliding-bar',
						'comparison' => '==',
					],
				],
			],
			'sb_exit_animation'         => [
				'type'        => 'select',
				'label'       => esc_html__( 'Exit Animation', 'Avada' ),
				'description' => esc_attr__( 'Set the Off Canvas exit animation.', 'Avada' ),
				'id'          => 'sb_exit_animation',
				'default'     => 'slideShort',
				'choices'     => [
					'slideShort' => esc_html__( 'Slide', 'Avada' ),
					'bounce'     => esc_html__( 'Bounce', 'Avada' ),
					'fade'       => esc_html__( 'Fade', 'Avada' ),
				],
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-exit-animation',
				],
				'dependency'  => [
					[
						'field'      => 'type',
						'value'      => 'sliding-bar',
						'comparison' => '==',
					],
				],
			],
			'sb_exit_animation_speed'   => [
				'type'        => 'slider',
				'label'       => esc_html__( 'Exit Animation Speed', 'Avada' ),
				'description' => esc_attr__( 'Set the speed of the exit animation in seconds (0.1 - 5).', 'Avada' ),
				'id'          => 'sb_exit_animation_speed',
				'default'     => 0.5,
				'choices'     => [
					'step' => 0.1,
					'min'  => 0.1,
					'max'  => 5,
				],
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-exit-animation',
				],
				'dependency'  => [
					[
						'field'      => 'sb_exit_animation',
						'value'      => '',
						'comparison' => '!=',
					],
					[
						'field'      => 'type',
						'value'      => 'sliding-bar',
						'comparison' => '==',
					],
				],
			],
		],
	];

	return apply_filters( 'avada_off_canvas_animation_sections', $sections );

}

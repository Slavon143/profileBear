<?php
/**
 * Avada Builder Animation Helper class.
 *
 * @package Avada-Builder
 * @since 2.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Animation Helper class.
 *
 * @since 2.2
 */
class Fusion_Builder_Animation_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 2.2
	 * @access public
	 */
	public function __construct() {

	}

	/**
	 * Get animation params.
	 *
	 * @since 2.2
	 * @access public
	 * @param array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_params( $args ) {

		$selector = isset( $args['preview_selector'] ) ? $args['preview_selector'] : '';

		return [
			[
				'type'        => 'select',
				'heading'     => esc_attr__( 'Animation Type', 'fusion-builder' ),
				'description' => esc_attr__( 'Select the type of animation to use on the element.', 'fusion-builder' ),
				'param_name'  => 'animation_type',
				'value'       => fusion_builder_available_animations(),
				'default'     => '',
				'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
				'preview'     => [
					'selector' => $selector,
					'type'     => 'animation',
				],
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Direction of Animation', 'fusion-builder' ),
				'description' => esc_attr__( 'Select the incoming direction for the animation.', 'fusion-builder' ),
				'param_name'  => 'animation_direction',
				'default'     => 'left',
				'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
				'dependency'  => [
					[
						'element'  => 'animation_type',
						'value'    => '',
						'operator' => '!=',
					],
					[
						'element'  => 'animation_type',
						'value'    => 'flash',
						'operator' => '!=',
					],
					[
						'element'  => 'animation_type',
						'value'    => 'shake',
						'operator' => '!=',
					],
					[
						'element'  => 'animation_type',
						'value'    => 'rubberband',
						'operator' => '!=',
					],
					[
						'element'  => 'animation_type',
						'value'    => 'flipinx',
						'operator' => '!=',
					],
					[
						'element'  => 'animation_type',
						'value'    => 'flipiny',
						'operator' => '!=',
					],
					[
						'element'  => 'animation_type',
						'value'    => 'lightspeedin',
						'operator' => '!=',
					],
				],
				'value'       => [
					'down'   => esc_attr__( 'Top', 'fusion-builder' ),
					'right'  => esc_attr__( 'Right', 'fusion-builder' ),
					'up'     => esc_attr__( 'Bottom', 'fusion-builder' ),
					'left'   => esc_attr__( 'Left', 'fusion-builder' ),
					'static' => esc_attr__( 'Static', 'fusion-builder' ),
				],
				'preview'     => [
					'selector' => $selector,
					'type'     => 'animation',
				],
			],
			[
				'type'        => 'range',
				'heading'     => esc_attr__( 'Speed of Animation', 'fusion-builder' ),
				'description' => esc_attr__( 'Type in speed of animation in seconds (0.1 - 5).', 'fusion-builder' ),
				'param_name'  => 'animation_speed',
				'min'         => '0.1',
				'max'         => '5',
				'step'        => '0.1',
				'value'       => '0.3',
				'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
				'dependency'  => [
					[
						'element'  => 'animation_type',
						'value'    => '',
						'operator' => '!=',
					],
				],
				'preview'     => [
					'selector' => $selector,
					'type'     => 'animation',
				],
			],
			[
				'type'        => 'select',
				'heading'     => esc_attr__( 'Offset of Animation', 'fusion-builder' ),
				'description' => esc_attr__( 'Controls when the animation should start.', 'fusion-builder' ),
				'param_name'  => 'animation_offset',
				'default'     => '',
				'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
				'dependency'  => [
					[
						'element'  => 'animation_type',
						'value'    => '',
						'operator' => '!=',
					],
				],
				'value'       => [
					''                => esc_attr__( 'Default', 'fusion-builder' ),
					'top-into-view'   => esc_attr__( 'Top of element hits bottom of viewport', 'fusion-builder' ),
					'top-mid-of-view' => esc_attr__( 'Top of element hits middle of viewport', 'fusion-builder' ),
					'bottom-in-view'  => esc_attr__( 'Bottom of element enters viewport', 'fusion-builder' ),
				],
			],
		];

	}

	/**
	 * Add animation attributes.
	 *
	 * @since 2.2
	 * @param array   $args   Element arguments.
	 * @param array   $attr   Element attributes.
	 * @param boolean $parent Is parent.
	 * @return array
	 */
	public static function add_animation_attributes( $args = [], $attr = [], $parent = false ) {

		$animations = FusionBuilder::animations(
			[
				'type'      => $args['animation_type'],
				'direction' => $args['animation_direction'],
				'speed'     => $args['animation_speed'],
				'offset'    => $args['animation_offset'],
			]
		);

		$attr = array_merge( $attr, $animations );

		if ( false === $parent ) {
			$attr['class'] .= ' ' . $attr['animation_class'];
		}
		unset( $attr['animation_class'] );

		return $attr;
	}

}

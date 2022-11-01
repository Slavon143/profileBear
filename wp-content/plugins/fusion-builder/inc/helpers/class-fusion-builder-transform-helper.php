<?php
/**
 * Avada Builder Transform Helper class.
 *
 * @package Avada-Builder
 * @since 3.8
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Transform Helper class.
 *
 * @since 3.8
 */
class Fusion_Builder_Transform_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 3.8
	 * @access public
	 */
	public function __construct() {

	}

	/**
	 * Get transform params.
	 *
	 * @since 3.8
	 * @access public
	 * @param array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_params( $args ) {

		$selector_base = isset( $args['selector_base'] ) ? $args['selector_base'] : '';

		$states            = [ 'regular', 'hover' ];
		$transform_options = [
			[
				'type'             => 'subgroup',
				'heading'          => esc_attr__( 'Transform', 'fusion-builder' ),
				'description'      => esc_attr__( 'Use transform options to scale, translate, rotate and skew the element.', 'fusion-builder' ),
				'param_name'       => 'transform_type',
				'default'          => 'regular',
				'group'            => esc_attr__( 'Extras', 'fusion-builder' ),
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
		];

		foreach ( $states as $key ) {
			$transform_options = array_merge(
				$transform_options,
				[
					[
						'type'        => 'range',
						'reset'       => true,
						'heading'     => esc_attr__( 'Scale X', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the scale in the horizontal direction.', 'fusion-builder' ),
						'param_name'  => 'transform_scale_x' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '1',
						'min'         => '0',
						'max'         => '2',
						'step'        => '0.01',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'transform_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_transform_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'reset'       => true,
						'heading'     => esc_attr__( 'Scale Y', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the scale in the vertical direction.', 'fusion-builder' ),
						'param_name'  => 'transform_scale_y' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '1',
						'min'         => '0',
						'max'         => '2',
						'step'        => '0.01',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'transform_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_transform_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'reset'       => true,
						'heading'     => esc_attr__( 'Translate X', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the translate in the horizontal direction. in pixels.', 'fusion-builder' ),
						'param_name'  => 'transform_translate_x' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '0',
						'min'         => '-300',
						'max'         => '300',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'transform_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_transform_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'reset'       => true,
						'heading'     => esc_attr__( 'Translate Y', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the translate in the vertical direction. in pixels.', 'fusion-builder' ),
						'param_name'  => 'transform_translate_y' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '0',
						'min'         => '-300',
						'max'         => '300',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'transform_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_transform_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'reset'       => true,
						'heading'     => esc_attr__( 'Rotate', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the rotation of the element.', 'fusion-builder' ),
						'param_name'  => 'transform_rotate' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '0',
						'min'         => '-360',
						'max'         => '360',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'transform_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_transform_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'reset'       => true,
						'heading'     => esc_attr__( 'Skew X', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the skew in the horizontal direction.', 'fusion-builder' ),
						'param_name'  => 'transform_skew_x' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '0',
						'min'         => '-100',
						'max'         => '100',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'transform_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_transform_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'reset'       => true,
						'heading'     => esc_attr__( 'Skew Y', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the skew in the vertical direction.', 'fusion-builder' ),
						'param_name'  => 'transform_skew_y' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '0',
						'min'         => '-100',
						'max'         => '100',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'transform_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_transform_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
				]
			);
		}

		$transform_options[] = [
			'type'        => 'image_focus_point',
			'heading'     => esc_attr__( 'Transform Origin', 'fusion-builder' ),
			'description' => esc_attr__( 'Set the location of origin point for transform, .', 'fusion-builder' ),
			'param_name'  => 'transform_origin',
			'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
			'mode'        => 'position',
			'subgroup'    => [
				'name' => 'transform_type',
				'tab'  => 'regular',
			],
			'callback'    => [
				'function' => 'fusion_update_transform_style',
				'args'     => [
					'selector_base' => $selector_base,
				],
			],
		];
		return $transform_options;
	}

	/**
	 * Get transform styles
	 *
	 * @since 3.8
	 * @access public
	 * @param array  $atts The transform parameters.
	 * @param string $state Element state, regular or hover.
	 * @return string
	 */
	public static function get_transform_styles( $atts, $state = 'regular' ) {

		$state_suffix       = 'regular' === $state ? '' : '_hover';
		$other_state_suffix = 'regular' === $state ? '_hover' : '';

		$transforms = [
			'transform_scale_x'     => [
				'property' => 'scaleX',
				'unit'     => '',
				'default'  => '1',
			],
			'transform_scale_y'     => [
				'property' => 'scaleY',
				'unit'     => '',
				'default'  => '1',
			],
			'transform_translate_x' => [
				'property' => 'translateX',
				'unit'     => 'px',
				'default'  => '0',
			],
			'transform_translate_y' => [
				'property' => 'translateY',
				'unit'     => 'px',
				'default'  => '0',
			],
			'transform_rotate'      => [
				'property' => 'rotate',
				'unit'     => 'deg',
				'default'  => '0',
			],
			'transform_skew_x'      => [
				'property' => 'skewX',
				'unit'     => 'deg',
				'default'  => '0',
			],
			'transform_skew_y'      => [
				'property' => 'skewY',
				'unit'     => 'deg',
				'default'  => '0',
			],
		];

		$transform_style = '';
		foreach ( $transforms as $transform_id => $transform ) {
			$transform_id_state = $transform_id . $state_suffix;
			$transform_id_other = $transform_id . $other_state_suffix;
			if ( $transform['default'] !== $atts[ $transform_id_state ] || $transform['default'] !== $atts[ $transform_id_other ] ) {
				$transform_style .= $transform['property'] . '(' . $atts[ $transform_id_state ] . $transform['unit'] . ') ';
			}
		}

		return trim( $transform_style );
	}

	/**
	 * Get transform style element.
	 *
	 * @since 3.8
	 * @access public
	 * @param array  $atts The transform parameters.
	 * @param string $selector Element selector.
	 * @param bool   $include_style_tag Include <style> tag or not.
	 * @return string
	 */
	public static function get_transform_style_element( $atts, $selector, $include_style_tag = true ) {

		$opening_style_tag = true === $include_style_tag ? '<style type="text/css">' : '';
		$closing_style_tag = true === $include_style_tag ? '</style>' : '';

		$transform_style = self::get_transform_styles( $atts, 'regular' );
		if ( '' !== $transform_style ) {
			$transform_style = $selector . '{transform: ' . $transform_style . ';}';
		}

		$transform_style_hover = self::get_transform_styles( $atts, 'hover' );
		if ( '' !== $transform_style_hover ) {

			// Add transition.
			$transform_style = str_replace( '}', 'transition: transform 0.3s ease;}', $transform_style );

			// Hover state.
			$transform_style .= $selector . ':hover{transform: ' . $transform_style_hover . ';}';
		}

		return '' !== $transform_style ? $opening_style_tag . $transform_style . $closing_style_tag : '';
	}

	/**
	 * Get transform style variables.
	 *
	 * @since 3.8
	 * @access public
	 * @param array $atts The transform parameters.
	 * @return string
	 */
	public static function get_transform_style_vars( $atts ) {

		$transform_style = self::get_transform_styles( $atts, 'regular' );
		$output          = '';
		if ( '' !== $transform_style ) {
			$output = '--awb-transform: ' . $transform_style . ';';
		}

		$transform_style_hover = self::get_transform_styles( $atts, 'hover' );
		if ( '' !== $transform_style_hover ) {
			// Hover state.
			$output .= '--awb-transform-hover: ' . $transform_style_hover . ';';

			// Add Transition.
			$output .= '--awb-transform-transition: transform 0.3s ease;';
		}

		if ( '' !== $atts['transform_origin'] ) {
			$output .= '--awb-transform-origin: ' . $atts['transform_origin'] . ';';
		}

		return $output;
	}

}


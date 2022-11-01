<?php
/**
 * Avada Builder Filter Helper class.
 *
 * @package Avada-Builder
 * @since 2.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Filter Helper class.
 *
 * @since 2.2
 */
class Fusion_Builder_Filter_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 2.2
	 * @access public
	 */
	public function __construct() {

	}

	/**
	 * Get filter params.
	 *
	 * @since 2.2
	 * @access public
	 * @param array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_params( $args ) {

		$selector_base = isset( $args['selector_base'] ) ? $args['selector_base'] : '';

		$states         = [ 'regular', 'hover' ];
		$filter_options = [
			[
				'type'             => 'subgroup',
				'heading'          => esc_attr__( 'Filter Type', 'fusion-builder' ),
				'description'      => esc_attr__( 'Use filters to see specific type of content.', 'fusion-builder' ),
				'param_name'       => 'filter_type',
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
			$filter_options = array_merge(
				$filter_options,
				[
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Hue', 'fusion-builder' ),
						'description' => esc_attr__( 'Filter hue.', 'fusion-builder' ),
						'param_name'  => 'filter_hue' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '0',
						'min'         => '0',
						'max'         => '359',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'filter_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_filter_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Saturation', 'fusion-builder' ),
						'description' => esc_attr__( 'Filter saturation.', 'fusion-builder' ),
						'param_name'  => 'filter_saturation' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '100',
						'min'         => '0',
						'max'         => '200',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'filter_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_filter_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Brightness', 'fusion-builder' ),
						'description' => esc_attr__( 'Filter brightness.', 'fusion-builder' ),
						'param_name'  => 'filter_brightness' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '100',
						'min'         => '0',
						'max'         => '200',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'filter_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_filter_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Contrast', 'fusion-builder' ),
						'description' => esc_attr__( 'Filter contrast.', 'fusion-builder' ),
						'param_name'  => 'filter_contrast' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '100',
						'min'         => '0',
						'max'         => '200',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'filter_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_filter_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Invert', 'fusion-builder' ),
						'description' => esc_attr__( 'Filter invert.', 'fusion-builder' ),
						'param_name'  => 'filter_invert' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '0',
						'min'         => '0',
						'max'         => '100',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'filter_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_filter_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Sepia', 'fusion-builder' ),
						'description' => esc_attr__( 'Filter sepia.', 'fusion-builder' ),
						'param_name'  => 'filter_sepia' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '0',
						'min'         => '0',
						'max'         => '100',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'filter_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_filter_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Opacity', 'fusion-builder' ),
						'description' => esc_attr__( 'Filter opacity.', 'fusion-builder' ),
						'param_name'  => 'filter_opacity' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '100',
						'min'         => '0',
						'max'         => '100',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'filter_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_filter_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Blur', 'fusion-builder' ),
						'description' => esc_attr__( 'Filter blur.', 'fusion-builder' ),
						'param_name'  => 'filter_blur' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '0',
						'min'         => '0',
						'max'         => '50',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'filter_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_filter_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
				]
			);
		}

		return $filter_options;
	}

	/**
	 * Get filter styles
	 *
	 * @since 2.2
	 * @access public
	 * @param array  $atts The filter parameters.
	 * @param string $state Element state, regular or hover.
	 * @return string
	 */
	public static function get_filter_styles( $atts, $state = 'regular' ) {

		$state_suffix       = 'regular' === $state ? '' : '_hover';
		$other_state_suffix = 'regular' === $state ? '_hover' : '';

		$filters = [
			'filter_hue'        => [
				'property' => 'hue-rotate',
				'unit'     => 'deg',
				'default'  => '0',
			],
			'filter_saturation' => [
				'property' => 'saturate',
				'unit'     => '%',
				'default'  => '100',
			],
			'filter_brightness' => [
				'property' => 'brightness',
				'unit'     => '%',
				'default'  => '100',
			],
			'filter_contrast'   => [
				'property' => 'contrast',
				'unit'     => '%',
				'default'  => '100',
			],
			'filter_invert'     => [
				'property' => 'invert',
				'unit'     => '%',
				'default'  => '0',
			],
			'filter_sepia'      => [
				'property' => 'sepia',
				'unit'     => '%',
				'default'  => '0',
			],
			'filter_opacity'    => [
				'property' => 'opacity',
				'unit'     => '%',
				'default'  => '100',
			],
			'filter_blur'       => [
				'property' => 'blur',
				'unit'     => 'px',
				'default'  => '0',
			],
		];

		$filter_style = '';
		foreach ( $filters as $filter_id => $filter ) {
			$filter_id_state = $filter_id . $state_suffix;
			$filter_id_other = $filter_id . $other_state_suffix;
			if ( $filter['default'] !== $atts[ $filter_id_state ] || $filter['default'] !== $atts[ $filter_id_other ] ) {
				$filter_style .= $filter['property'] . '(' . $atts[ $filter_id_state ] . $filter['unit'] . ') ';
			}
		}

		return trim( $filter_style );
	}

	/**
	 * Get filter style element.
	 *
	 * @since 2.2
	 * @access public
	 * @param array  $atts The filter parameters.
	 * @param string $selector Element selector.
	 * @param bool   $include_style_tag Include <style> tag or not.
	 * @return string
	 */
	public static function get_filter_style_element( $atts, $selector, $include_style_tag = true ) {

		$opening_style_tag = true === $include_style_tag ? '<style type="text/css">' : '';
		$closing_style_tag = true === $include_style_tag ? '</style>' : '';

		$filter_style = self::get_filter_styles( $atts, 'regular' );
		if ( '' !== $filter_style ) {
			$filter_style = $selector . '{filter: ' . $filter_style . ';}';
		}

		$filter_style_hover = self::get_filter_styles( $atts, 'hover' );
		if ( '' !== $filter_style_hover ) {

			// Add transition.
			$filter_style = str_replace( '}', 'transition: filter 0.3s ease;}', $filter_style );

			// Hover state.
			$filter_style .= $selector . ':hover{filter: ' . $filter_style_hover . ';}';
		}

		return '' !== $filter_style ? $opening_style_tag . $filter_style . $closing_style_tag : '';
	}
}


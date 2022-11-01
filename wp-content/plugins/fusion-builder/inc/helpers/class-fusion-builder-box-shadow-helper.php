<?php
/**
 * Avada Builder Box Shadow Helper class.
 *
 * @package Avada-Builder
 * @since 2.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Box Shadow Helper class.
 *
 * @since 2.2
 */
class Fusion_Builder_Box_Shadow_Helper {

	/**
	 * Get box-shadow params.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param  array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_params( $args ) {
		$params = [
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Box Shadow', 'fusion-builder' ),
				'description' => esc_attr__( 'Set to "Yes" to enable box shadows.', 'fusion-builder' ),
				'param_name'  => 'box_shadow',
				'default'     => 'no',
				'group'       => esc_html__( 'Design', 'fusion-builder' ),
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
					'no'  => esc_attr__( 'No', 'fusion-builder' ),
				],
				'callback'    => [],
			],
			[
				'type'             => 'dimension',
				'remove_from_atts' => true,
				'heading'          => esc_attr__( 'Box Shadow Position', 'fusion-builder' ),
				'description'      => esc_attr__( 'Set the vertical and horizontal position of the box shadow. Positive values put the shadow below and right of the box, negative values put it above and left of the box. In pixels, ex. 5px.', 'fusion-builder' ),
				'param_name'       => 'dimension_box_shadow',
				'value'            => [
					'box_shadow_vertical'   => '',
					'box_shadow_horizontal' => '',
				],
				'group'            => esc_html__( 'Design', 'fusion-builder' ),
				'dependency'       => [
					[
						'element'  => 'box_shadow',
						'value'    => 'yes',
						'operator' => '==',
					],
				],
				'callback'         => [],
			],
			[
				'type'        => 'range',
				'heading'     => esc_attr__( 'Box Shadow Blur Radius', 'fusion-builder' ),
				'description' => esc_attr__( 'Set the blur radius of the box shadow. In pixels.', 'fusion-builder' ),
				'param_name'  => 'box_shadow_blur',
				'value'       => '0',
				'min'         => '0',
				'max'         => '100',
				'step'        => '1',
				'group'       => esc_html__( 'Design', 'fusion-builder' ),
				'dependency'  => [
					[
						'element'  => 'box_shadow',
						'value'    => 'yes',
						'operator' => '==',
					],
				],
				'callback'    => [],
			],
			[
				'type'        => 'range',
				'heading'     => esc_attr__( 'Box Shadow Spread Radius', 'fusion-builder' ),
				'description' => esc_attr__( 'Set the spread radius of the box shadow. A positive value increases the size of the shadow, a negative value decreases the size of the shadow. In pixels.', 'fusion-builder' ),
				'param_name'  => 'box_shadow_spread',
				'value'       => '0',
				'min'         => '-100',
				'max'         => '100',
				'step'        => '1',
				'group'       => esc_html__( 'Design', 'fusion-builder' ),
				'dependency'  => [
					[
						'element'  => 'box_shadow',
						'value'    => 'yes',
						'operator' => '==',
					],
				],
				'callback'    => [],
			],
			[
				'type'        => 'colorpickeralpha',
				'heading'     => esc_attr__( 'Box Shadow Color', 'fusion-builder' ),
				'description' => esc_attr__( 'Controls the color of the box shadow.', 'fusion-builder' ),
				'param_name'  => 'box_shadow_color',
				'value'       => '',
				'group'       => esc_html__( 'Design', 'fusion-builder' ),
				'dependency'  => [
					[
						'element'  => 'box_shadow',
						'value'    => 'yes',
						'operator' => '==',
					],
				],
				'callback'    => [],
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Box Shadow Style', 'fusion-builder' ),
				'description' => esc_attr__( 'Set the style of the box shadow to either be an outer or inner shadow.', 'fusion-builder' ),
				'param_name'  => 'box_shadow_style',
				'default'     => '',
				'group'       => esc_html__( 'Design', 'fusion-builder' ),
				'value'       => [
					''      => esc_attr__( 'Outer', 'fusion-builder' ),
					'inset' => esc_attr__( 'Inner', 'fusion-builder' ),
				],
				'dependency'  => [
					[
						'element'  => 'box_shadow',
						'value'    => 'yes',
						'operator' => '==',
					],
				],
				'callback'    => [],
			],
		];

		foreach ( $params as $key => $param ) {
			$tmp_args = $args;

			// Prevent param being dependant on itself.
			if ( isset( $args['dependency'] ) ) {
				foreach ( $tmp_args['dependency'] as $k => $dep ) {
					if ( $param['param_name'] === $dep['element'] ) {
						unset( $tmp_args['dependency'][ $k ] );
					}
				}
			}

			$params[ $key ] = wp_parse_args( $tmp_args, $param );
		}

		return $params;
	}

	/**
	 * Get box-shadow params.
	 * Same as the get_box_shadow_params, but with the "box_shadow_style" parameter removed.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param  array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_no_inner_params( $args ) {
		$params = self::get_params( $args );
		foreach ( $params as $key => $param ) {
			if ( 'box_shadow_style' === $param['param_name'] ) {
				unset( $params[ $key ] );
			}
		}
		return $params;
	}

	/**
	 * Get box-shadow styles.
	 *
	 * @since 2.2
	 * @access public
	 * @param array $params The box-shadow parameters.
	 * @return string
	 */
	public static function get_box_shadow_styles( $params ) {
		$style  = fusion_library()->sanitize->get_value_with_unit( $params['box_shadow_horizontal'] );
		$style .= ' ' . fusion_library()->sanitize->get_value_with_unit( $params['box_shadow_vertical'] );
		$style .= ' ' . fusion_library()->sanitize->get_value_with_unit( $params['box_shadow_blur'] );
		$style .= ' ' . fusion_library()->sanitize->get_value_with_unit( $params['box_shadow_spread'] );
		$style .= ' ' . $params['box_shadow_color'];
		if ( isset( $params['box_shadow_style'] ) && $params['box_shadow_style'] ) {
			$style .= ' ' . $params['box_shadow_style'];
		}
		$style .= ';';

		return $style;
	}

}

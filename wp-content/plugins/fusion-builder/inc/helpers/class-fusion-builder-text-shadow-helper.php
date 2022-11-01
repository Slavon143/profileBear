<?php
/**
 * Avada Builder Text Shadow Helper class.
 *
 * @package Avada-Builder
 * @since 3.1
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Box Shadow Helper class.
 *
 * @since 3.1
 */
class Fusion_Builder_Text_Shadow_Helper {

	/**
	 * Get text-shadow params.
	 *
	 * @static
	 * @access public
	 * @since 3.1
	 * @param  array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_params( $args ) {
		$params = [
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Text Shadow', 'fusion-builder' ),
				'description' => esc_attr__( 'Set to "Yes" to enable text shadows.', 'fusion-builder' ),
				'param_name'  => 'text_shadow',
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
				'heading'          => esc_attr__( 'Text Shadow Position', 'fusion-builder' ),
				'description'      => esc_attr__( 'Set the vertical and horizontal position of the text shadow. Positive values put the shadow below and right of the text, negative values put it above and left of the text. In pixels, ex. 5px.', 'fusion-builder' ),
				'param_name'       => 'dimension_text_shadow',
				'value'            => [
					'text_shadow_vertical'   => '',
					'text_shadow_horizontal' => '',
				],
				'group'            => esc_html__( 'Design', 'fusion-builder' ),
				'dependency'       => [
					[
						'element'  => 'text_shadow',
						'value'    => 'yes',
						'operator' => '==',
					],
				],
				'callback'         => [],
			],
			[
				'type'        => 'range',
				'heading'     => esc_attr__( 'Text Shadow Blur Radius', 'fusion-builder' ),
				'description' => esc_attr__( 'Set the blur radius of the text shadow. In pixels.', 'fusion-builder' ),
				'param_name'  => 'text_shadow_blur',
				'value'       => '0',
				'min'         => '0',
				'max'         => '100',
				'step'        => '1',
				'group'       => esc_html__( 'Design', 'fusion-builder' ),
				'dependency'  => [
					[
						'element'  => 'text_shadow',
						'value'    => 'yes',
						'operator' => '==',
					],
				],
				'callback'    => [],
			],
			[
				'type'        => 'colorpickeralpha',
				'heading'     => esc_attr__( 'Text Shadow Color', 'fusion-builder' ),
				'description' => esc_attr__( 'Controls the color of the text shadow.', 'fusion-builder' ),
				'param_name'  => 'text_shadow_color',
				'value'       => '',
				'group'       => esc_html__( 'Design', 'fusion-builder' ),
				'dependency'  => [
					[
						'element'  => 'text_shadow',
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
	 * Get text-shadow styles.
	 *
	 * @since 2.2
	 * @access public
	 * @param array $params The text-shadow parameters.
	 * @return string
	 */
	public static function get_text_shadow_styles( $params ) {
		$style  = fusion_library()->sanitize->get_value_with_unit( $params['text_shadow_horizontal'] );
		$style .= ' ' . fusion_library()->sanitize->get_value_with_unit( $params['text_shadow_vertical'] );
		$style .= ' ' . fusion_library()->sanitize->get_value_with_unit( $params['text_shadow_blur'] );
		$style .= ' ' . $params['text_shadow_color'];

		return $style;
	}

}

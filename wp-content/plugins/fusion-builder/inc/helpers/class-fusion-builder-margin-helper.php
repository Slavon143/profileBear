<?php
/**
 * Avada Builder Margin Helper class.
 *
 * @package Avada-Builder
 * @since 2.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Margin Helper class.
 *
 * @since 2.2
 */
class Fusion_Builder_Margin_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 2.2
	 * @access public
	 */
	public function __construct() {

	}

	/**
	 * Get margin params.
	 *
	 * @since 2.2
	 * @access public
	 * @param array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_params( $args ) {

		$params = [
			[
				'type'             => 'dimension',
				'remove_from_atts' => true,
				'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
				'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 4%.', 'fusion-builder' ),
				'param_name'       => 'dimension_margin',
				'value'            => [
					'margin_top'    => '',
					'margin_bottom' => '',
				],
				'group'            => esc_attr__( 'Design', 'fusion-builder' ),
			],
		];

		// Override params.
		foreach ( $args as $key => $value ) {
			if ( 'fusion_remove_param' === $value && isset( $params[0][ $key ] ) ) {
				unset( $params[0][ $key ] );
				continue;
			}

			$params[0][ $key ] = $value;
		}

		return $params;

	}

	/**
	 * Generates margins CSS properties.
	 *
	 * @since 2.2
	 * @param array $args Element arguments.
	 * @return string
	 */
	public static function get_margins_style( $args ) {

		$style        = '';
		$margin_sides = [
			'margin_top',
			'margin_right',
			'margin_bottom',
			'margin_left',
		];

		foreach ( $margin_sides as $margin_side ) {
			if ( isset( $args[ $margin_side ] ) && $args[ $margin_side ] ) {
				$style .= str_replace( '_', '-', $margin_side ) . ':' . fusion_library()->sanitize->get_value_with_unit( $args[ $margin_side ] ) . ';';
			}
		}

		return $style;
	}

}

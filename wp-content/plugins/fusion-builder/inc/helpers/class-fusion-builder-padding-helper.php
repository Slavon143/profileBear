<?php
/**
 * Avada Builder Margin Helper class.
 *
 * @package Avada-Builder
 * @since 3.1.1
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
class Fusion_Builder_Padding_Helper {

	/**
	 * Generates padding CSS properties.
	 *
	 * @since 3.1.1
	 * @param array $args Element arguments.
	 * @return string
	 */
	public static function get_paddings_style( $args ) {

		$style         = '';
		$padding_sides = [
			'padding_top',
			'padding_right',
			'padding_bottom',
			'padding_left',
		];

		foreach ( $padding_sides as $padding_side ) {
			if ( isset( $args[ $padding_side ] ) && $args[ $padding_side ] ) {
				$style .= str_replace( '_', '-', $padding_side ) . ':' . fusion_library()->sanitize->get_value_with_unit( $args[ $padding_side ] ) . ';';
			}
		}

		return $style;
	}

}

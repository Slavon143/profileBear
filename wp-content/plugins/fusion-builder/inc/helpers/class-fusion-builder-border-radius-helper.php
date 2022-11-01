<?php
/**
 * Avada Builder Border Radius  Helper class.
 *
 * @package Avada-Builder
 * @since 2.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Border Radius Helper class.
 *
 * @since 2.2
 */
class Fusion_Builder_Border_Radius_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 2.2
	 * @access public
	 */
	public function __construct() {

	}

	/**
	 * Get border radius params.
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
				'type'             => 'dimension',
				'remove_from_atts' => true,
				'heading'          => esc_attr__( 'Border Radius', 'fusion-builder' ),
				'description'      => __( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
				'param_name'       => 'border_radius',
				'group'            => esc_attr__( 'Design', 'fusion-builder' ),
				'value'            => [
					'border_radius_top_left'     => '',
					'border_radius_top_right'    => '',
					'border_radius_bottom_right' => '',
					'border_radius_bottom_left'  => '',
				],
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
	 * Checks if all border radius values are defined and adds a unit if needed.
	 * Sets default value if value is not set.
	 *
	 * @param  array $border_radius Border radius values.
	 * @return array
	 */
	public static function get_border_radius_array_with_fallback_value( $border_radius ) {

		return [
			'top_left'     => isset( $border_radius['top_left'] ) ? fusion_library()->sanitize->get_value_with_unit( $border_radius['top_left'] ) : '0px',
			'top_right'    => isset( $border_radius['top_right'] ) ? fusion_library()->sanitize->get_value_with_unit( $border_radius['top_right'] ) : '0px',
			'bottom_right' => isset( $border_radius['bottom_right'] ) ? fusion_library()->sanitize->get_value_with_unit( $border_radius['bottom_right'] ) : '0px',
			'bottom_left'  => isset( $border_radius['bottom_left'] ) ? fusion_library()->sanitize->get_value_with_unit( $border_radius['bottom_left'] ) : '0px',
		];
	}
}

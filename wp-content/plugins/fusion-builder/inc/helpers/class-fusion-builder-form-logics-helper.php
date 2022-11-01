<?php
/**
 * Avada Builder Form Logics Helper class.
 *
 * @package Avada-Builder
 * @since 3.3
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Form Conditional Logic Helper class.
 *
 * @since 3.3
 */
class Fusion_Builder_Form_Logics_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 3.3
	 * @access public
	 */
	public function __construct() {

	}

	/**
	 * Get conditional logic params.
	 *
	 * @since 3.3
	 * @access public
	 * @param array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_params( $args ) {

		$params = [
			[
				'type'        => 'fusion_logics',
				'heading'     => esc_html__( 'Conditional Logic', 'fusion-builder' ),
				'param_name'  => 'logics',
				'description' => esc_html__( 'Add conditional logic for the input field.', 'fusion-builder' ),
				'group'       => esc_attr__( 'Conditionals', 'fusion-builder' ),
				'placeholder' => [
					'id'          => 'placeholder',
					'title'       => esc_html__( 'Select A Field', 'fusion-builder' ),
					'type'        => 'text',
					'comparisons' => [
						'equal'        => esc_attr__( 'Equal To', 'fusion-builder' ),
						'not-equal'    => esc_attr__( 'Not Equal To', 'fusion-builder' ),
						'greater-than' => esc_attr__( 'Greater Than', 'fusion-builder' ),
						'less-than'    => esc_attr__( 'Less Than', 'fusion-builder' ),
						'contains'     => esc_attr__( 'Contains', 'fusion-builder' ),
					],
				],
				'comparisons' => [
					'equal'        => esc_attr__( 'Equal To', 'fusion-builder' ),
					'not-equal'    => esc_attr__( 'Not Equal To', 'fusion-builder' ),
					'greater-than' => esc_attr__( 'Greater Than', 'fusion-builder' ),
					'less-than'    => esc_attr__( 'Less Than', 'fusion-builder' ),
					'contains'     => esc_attr__( 'Contains', 'fusion-builder' ),
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

}

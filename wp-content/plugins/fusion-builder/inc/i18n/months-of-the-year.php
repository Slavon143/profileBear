<?php
/**
 * Months of the year.
 *
 * @package fusion-builder
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Adds Months of the year to predefined choices.
 *
 * @param array $choices The predefined choices.
 */
function fusion_builder_predefined_months_of_the_year( $choices = [] ) {

	$choices[] = [
		'name'   => __( 'Months of the Year', 'fusion-builder' ),
		'values' => [
			__( 'January', 'fusion-builder' ),
			__( 'February', 'fusion-builder' ),
			__( 'March', 'fusion-builder' ),
			__( 'April', 'fusion-builder' ),
			__( 'May', 'fusion-builder' ),
			__( 'June', 'fusion-builder' ),
			__( 'July', 'fusion-builder' ),
			__( 'August', 'fusion-builder' ),
			__( 'September', 'fusion-builder' ),
			__( 'October', 'fusion-builder' ),
			__( 'November', 'fusion-builder' ),
			__( 'December', 'fusion-builder' ),
		],
	];
	return $choices;
}
add_filter( 'fusion_predefined_choices', 'fusion_builder_predefined_months_of_the_year' );

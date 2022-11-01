<?php
/**
 * Days of the month.
 *
 * @package fusion-builder
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Adds Days of the month to predefined choices
 *
 * @param array $choices The predefined choices.
 */
function fusion_builder_predefined_days_of_the_month( $choices = [] ) {
	$values = [];
	for ( $x = 1; $x <= 31; $x++ ) {
		$values[] = (string) $x;
	}

	$choices[] = [
		'name'   => __( 'Days of the Month', 'fusion-builder' ),
		'values' => $values,
	];
	return $choices;
}
add_filter( 'fusion_predefined_choices', 'fusion_builder_predefined_days_of_the_month' );

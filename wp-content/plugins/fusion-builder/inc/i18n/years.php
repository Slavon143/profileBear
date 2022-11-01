<?php
/**
 * Years.
 *
 * @package fusion-builder
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Adds years predefined choices
 *
 * @param array $choices The predefined choices.
 */
function fusion_builder_predefined_years( $choices = [] ) {
	$values = [];
	$date   = (int) date( 'Y' );

	for ( $x = 0; $x <= 100; $x++ ) {
		$values[] = (string) $date;
		$date--;
	}

	$choices[] = [
		'name'   => __( 'Years', 'fusion-builder' ),
		'values' => $values,
	];
	return $choices;
}
add_filter( 'fusion_predefined_choices', 'fusion_builder_predefined_years' );

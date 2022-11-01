<?php
/**
 * Days of the week.
 *
 * @package fusion-builder
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Adds Days of the week to predefined choices
 *
 * @param array $choices The predefined choices.
 */
function fusion_builder_predefined_days_of_the_week( $choices = [] ) {

	$choices[] = [
		'name'   => esc_html__( 'Days of the Week', 'fusion-builder' ),
		'values' => [
			esc_attr__( 'Monday', 'fusion-builder' ),
			esc_attr__( 'Tuesday', 'fusion-builder' ),
			esc_attr__( 'Wednesday', 'fusion-builder' ),
			esc_attr__( 'Thursday', 'fusion-builder' ),
			esc_attr__( 'Friday', 'fusion-builder' ),
			esc_attr__( 'Saturday', 'fusion-builder' ),
			esc_attr__( 'Sunday', 'fusion-builder' ),
		],
	];
	return $choices;
}
add_filter( 'fusion_predefined_choices', 'fusion_builder_predefined_days_of_the_week' );

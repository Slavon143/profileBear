<?php
/**
 * Registers U.S. States for predefined choices.
 *
 * @package fusion-builder
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Adds U.S. States to predefined choices
 *
 * @param array $choices The predefined choices.
 */
function fusion_builder_predefined_us_states( $choices = [] ) {

	$choices[] = [
		'name'   => __( 'U.S. States', 'fusion-builder' ),
		'values' => [
			__( 'Alabama', 'fusion-builder' ),
			__( 'Alaska', 'fusion-builder' ),
			__( 'Arizona', 'fusion-builder' ),
			__( 'Arkansas', 'fusion-builder' ),
			__( 'California', 'fusion-builder' ),
			__( 'Colorado', 'fusion-builder' ),
			__( 'Connecticut', 'fusion-builder' ),
			__( 'Delaware', 'fusion-builder' ),
			__( 'District Of Columbia', 'fusion-builder' ),
			__( 'Florida', 'fusion-builder' ),
			_x( 'Georgia', 'US state of Georgia', 'fusion-builder' ),
			__( 'Hawaii', 'fusion-builder' ),
			__( 'Idaho', 'fusion-builder' ),
			__( 'Illinois', 'fusion-builder' ),
			__( 'Indiana', 'fusion-builder' ),
			__( 'Iowa', 'fusion-builder' ),
			__( 'Kansas', 'fusion-builder' ),
			__( 'Kentucky', 'fusion-builder' ),
			__( 'Louisiana', 'fusion-builder' ),
			__( 'Maine', 'fusion-builder' ),
			__( 'Maryland', 'fusion-builder' ),
			__( 'Massachusetts', 'fusion-builder' ),
			__( 'Michigan', 'fusion-builder' ),
			__( 'Minnesota', 'fusion-builder' ),
			__( 'Mississippi', 'fusion-builder' ),
			__( 'Missouri', 'fusion-builder' ),
			__( 'Montana', 'fusion-builder' ),
			__( 'Nebraska', 'fusion-builder' ),
			__( 'Nevada', 'fusion-builder' ),
			__( 'New Hampshire', 'fusion-builder' ),
			__( 'New Jersey', 'fusion-builder' ),
			__( 'New Mexico', 'fusion-builder' ),
			__( 'New York', 'fusion-builder' ),
			__( 'North Carolina', 'fusion-builder' ),
			__( 'North Dakota', 'fusion-builder' ),
			__( 'Ohio', 'fusion-builder' ),
			__( 'Oklahoma', 'fusion-builder' ),
			__( 'Oregon', 'fusion-builder' ),
			__( 'Pennsylvania', 'fusion-builder' ),
			__( 'Rhode Island', 'fusion-builder' ),
			__( 'South Carolina', 'fusion-builder' ),
			__( 'South Dakota', 'fusion-builder' ),
			__( 'Tennessee', 'fusion-builder' ),
			__( 'Texas', 'fusion-builder' ),
			__( 'Utah', 'fusion-builder' ),
			__( 'Vermont', 'fusion-builder' ),
			__( 'Virginia', 'fusion-builder' ),
			__( 'Washington', 'fusion-builder' ),
			__( 'West Virginia', 'fusion-builder' ),
			__( 'Wisconsin', 'fusion-builder' ),
			__( 'Wyoming', 'fusion-builder' ),
			__( 'Armed Forces (AA)', 'fusion-builder' ),
			__( 'Armed Forces (AE)', 'fusion-builder' ),
			__( 'Armed Forces (AP)', 'fusion-builder' ),
		],
	];
	return $choices;
}
add_filter( 'fusion_predefined_choices', 'fusion_builder_predefined_us_states' );

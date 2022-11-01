<?php
/**
 * Off Canvas triggers Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage off-canvas
 */

/**
 * Off Canvas conditions settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_off_canvas_conditions( $sections ) {
	$sections['off_canvas_conditions'] = [
		'label'    => esc_html__( 'Conditions', 'Avada' ),
		'alt_icon' => 'fusiona-conditions',
		'id'       => 'off_canvas_conditions',
		'fields'   => [
			'conditions_enabled' => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Enable Conditions', 'Avada' ),
				'description' => __( 'Set conditions for displaying Off Canvas. <strong>NOTE:</strong> Off Canvas won\'t be displayed at all, when this is turned off.', 'Avada' ),
				'id'          => 'conditions_enabled',
				'default'     => 'no',
				'transport'   => 'postMessage',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
			],
			'layout_conditions'  => [
				'type'        => 'layout_conditions',
				'label'       => esc_html__( 'Conditions', 'Avada' ),
				'description' => esc_html__( 'Load Off Canvas on these pages.', 'Avada' ),
				'id'          => 'layout_conditions',
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'conditions_enabled',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
		],
	];

	return apply_filters( 'avada_off_canvas_conditions_sections', $sections );

}

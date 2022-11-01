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
 * Off Canvas triggers settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_off_canvas_triggers( $sections ) {
	$sections['off_canvas_triggers'] = [
		'label'    => esc_html__( 'Triggers', 'Avada' ),
		'alt_icon' => 'fusiona-hover-state',
		'id'       => 'off_canvas_triggers',
		'fields'   => [
			// Page load trigger.
			'on_page_load'          => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'On Page Load', 'Avada' ),
				'description' => esc_html__( 'Display Off Canvas on page load.', 'Avada' ),
				'id'          => 'on_page_load',
				'default'     => 'no',
				'transport'   => 'postMessage',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
			],

			// Time trigger.
			'time_on_page'          => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Time On Page', 'Avada' ),
				'description' => esc_html__( 'Display Off Canvas after visitor has spent a specific amount of time on the page.', 'Avada' ),
				'id'          => 'time_on_page',
				'default'     => 'no',
				'transport'   => 'postMessage',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
			],
			'time_on_page_duration' => [
				'type'        => 'text',
				'label'       => esc_html__( 'Time On Page Duration', 'Avada' ),
				'description' => esc_html__( 'Set the time that needs to pass before the Off Canvas will be displayed. In seconds, ex: 5.', 'Avada' ),
				'id'          => 'time_on_page_duration',
				'default'     => '',
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'time_on_page',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],

			// On scroll trigger.
			'on_scroll'             => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'On Scroll', 'Avada' ),
				'description' => esc_html__( 'Display Off Canvas when the visitor has scrolled up or down to a specific position or element.', 'Avada' ),
				'id'          => 'on_scroll',
				'default'     => 'no',
				'transport'   => 'postMessage',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
			],
			'scroll_direction'      => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Scroll Direction', 'Avada' ),
				'description' => esc_html__( 'Set a scroll direction for triggering the Off Canvas.', 'Avada' ),
				'id'          => 'scroll_direction',
				'default'     => 'up',
				'transport'   => 'postMessage',
				'choices'     => [
					'up'   => esc_html__( 'Up', 'Avada' ),
					'down' => esc_html__( 'Down', 'Avada' ),
				],
				'dependency'  => [
					[
						'field'      => 'on_scroll',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			'scroll_to'             => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Scroll To', 'Avada' ),
				'description' => esc_html__( 'Choose whether the scroll trigger should be relative to position or element on the page.', 'Avada' ),
				'id'          => 'scroll_to',
				'default'     => 'position',
				'transport'   => 'postMessage',
				'choices'     => [
					'position' => esc_html__( 'Position', 'Avada' ),
					'element'  => esc_html__( 'Element', 'Avada' ),
				],
				'dependency'  => [
					[
						'field'      => 'on_scroll',
						'value'      => 'yes',
						'comparison' => '==',
					],
					[
						'field'      => 'scroll_direction',
						'value'      => 'down',
						'comparison' => '==',
					],
				],
			],
			'scroll_position'       => [
				'type'        => 'text',
				'label'       => esc_html__( 'Scroll Position', 'Avada' ),
				'description' => esc_html__( 'Set the scroll position. Enter value including any valid CSS unit, ex: 300px', 'Avada' ),
				'id'          => 'scroll_position',
				'default'     => '',
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'on_scroll',
						'value'      => 'yes',
						'comparison' => '==',
					],
					[
						'field'      => 'scroll_direction',
						'value'      => 'down',
						'comparison' => '==',
					],
					[
						'field'      => 'scroll_to',
						'value'      => 'position',
						'comparison' => '==',
					],
				],
			],
			'scroll_element'        => [
				'type'        => 'text',
				'label'       => esc_html__( 'Scroll Element', 'Avada' ),
				'description' => esc_html__( 'Insert element CSS selector like class or ID, ex: .element-class, #element-ID.', 'Avada' ),
				'id'          => 'scroll_element',
				'default'     => '',
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'on_scroll',
						'value'      => 'yes',
						'comparison' => '==',
					],
					[
						'field'      => 'scroll_direction',
						'value'      => 'down',
						'comparison' => '==',
					],
					[
						'field'      => 'scroll_to',
						'value'      => 'element',
						'comparison' => '==',
					],
				],
			],
			// On click trigger.
			'on_click'              => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'On Click', 'Avada' ),
				'description' => esc_html__( 'Display Off Canvas when visitor clicks on the element.', 'Avada' ),
				'id'          => 'on_click',
				'default'     => 'no',
				'transport'   => 'postMessage',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
			],
			'on_click_element'      => [
				'type'        => 'text',
				'label'       => esc_html__( 'Element Selector', 'Avada' ),
				'description' => esc_html__( 'Insert element CSS selector like class or ID, ex: .element-class, #element-ID.', 'Avada' ),
				'id'          => 'on_click_element',
				'default'     => '',
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'on_click',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			// Exit intent.
			'exit_intent'           => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Exit Intent', 'Avada' ),
				'description' => esc_html__( 'Display Off Canvas when visitor intends to close or leave the page.', 'Avada' ),
				'id'          => 'exit_intent',
				'default'     => 'no',
				'transport'   => 'postMessage',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
			],
			// After inactivity.
			'after_inactivity'      => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'After Inactivity', 'Avada' ),
				'description' => esc_html__( 'Display Off Canvas if visitor is inactive for a specific amount of time.', 'Avada' ),
				'id'          => 'after_inactivity',
				'default'     => 'no',
				'transport'   => 'postMessage',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
			],
			'inactivity_duration'   => [
				'type'        => 'text',
				'label'       => esc_html__( 'Inactivity Duration', 'Avada' ),
				'description' => esc_html__( 'Set the time that needs to pass for visitor inactivity before the Off Canvas will be displayed. In seconds, ex: 30.', 'Avada' ),
				'id'          => 'inactivity_duration',
				'default'     => '',
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'after_inactivity',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
		],
	];

	// Product added to cart.
	if ( class_exists( 'WooCommerce' ) ) {
		$sections['off_canvas_triggers']['fields']['on_add_to_cart'] = [
			'type'        => 'radio-buttonset',
			'label'       => esc_html__( 'After Product Is Added to Cart', 'Avada' ),
			'description' => esc_html__( 'Display Off Canvas after WooCommerce product is added to cart.', 'Avada' ),
			'id'          => 'on_add_to_cart',
			'default'     => 'no',
			'transport'   => 'postMessage',
			'choices'     => [
				'yes' => esc_html__( 'Yes', 'Avada' ),
				'no'  => esc_html__( 'No', 'Avada' ),
			],
		];
	}

	return apply_filters( 'avada_off_canvas_triggers_sections', $sections );

}

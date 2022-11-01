<?php
/**
 * Off Canvas rules Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage off-canvas
 */

/**
 * Off Canvas rules settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_off_canvas_rules( $sections ) {
	if ( ! function_exists( 'get_editable_roles' ) ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
	}
	$user_roles       = get_editable_roles();
	$user_roles_array = [];
	foreach ( $user_roles as $id => $role ) {
		$user_roles_array[ $id ] = translate_user_role( $role['name'] );
	}
	$is_builder = isset( $_GET['builder'] ) && isset( $_GET['builder_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$sections['off_canvas_rules'] = [
		'label'    => esc_html__( 'Rules', 'Avada' ),
		'alt_icon' => 'fusiona-rules',
		'id'       => 'off_canvas_rules',
		'fields'   => [

			// Frequency.
			'frequency'            => [
				'type'        => 'select',
				'label'       => esc_html__( 'Frequency', 'Avada' ),
				'description' => esc_html__( 'Select display frequency for the Off Canvas.', 'Avada' ),
				'id'          => 'frequency',
				'default'     => 'forever',
				'transport'   => 'postMessage',
				'choices'     => [
					'forever' => esc_html__( 'Show every time', 'Avada' ),
					'close'   => esc_html__( 'Show every time until user closes it', 'Avada' ),
					'once'    => esc_html__( 'Show once', 'Avada' ),
					'xtimes'  => esc_html__( 'Show up to x times', 'Avada' ),
					'session' => esc_html__( 'Show every session', 'Avada' ),
					'day'     => esc_html__( 'Show every day', 'Avada' ),
					'week'    => esc_html__( 'Show every week', 'Avada' ),
					'month'   => esc_html__( 'Show every month', 'Avada' ),
					'xdays'   => esc_html__( 'Show on specific days', 'Avada' ),
				],
			],
			'frequency_xdays'      => [
				'type'        => 'text',
				'label'       => esc_html__( 'Number Of Days', 'Avada' ),
				'description' => esc_html__( 'Set the number of days to display the off canvas.', 'Avada' ),
				'id'          => 'frequency_xdays',
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'frequency',
						'value'      => 'xdays',
						'comparison' => '==',
					],
				],
			],
			'frequency_xtimes'     => [
				'type'        => 'text',
				'label'       => esc_html__( 'Number Of times', 'Avada' ),
				'description' => esc_html__( 'Set the Off Canvas display count ex: 3.', 'Avada' ),
				'id'          => 'frequency_xtimes',
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'frequency',
						'value'      => 'xtimes',
						'comparison' => '==',
					],
				],
			],

			// Show after x page views.
			'after_x_page_views'   => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Show After X Page Views', 'Avada' ),
				'description' => esc_html__( 'Display Off Canvas after visitor views a specific number of pages.', 'Avada' ),
				'id'          => 'after_x_page_views',
				'default'     => 'no',
				'transport'   => 'postMessage',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
			],
			'number_of_page_views' => [
				'type'        => 'text',
				'label'       => esc_html__( 'Number Of Page Views', 'Avada' ),
				'description' => esc_html__( 'Set the number of page views needed to display the Off Canvas, ex: 3.', 'Avada' ),
				'id'          => 'number_of_page_views',
				'default'     => '',
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'after_x_page_views',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			// Show after x sessions.
			'after_x_sessions'     => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Show After X Sessions', 'Avada' ),
				'description' => esc_html__( 'Display Off Canvas after a specific number of sessions.', 'Avada' ),
				'id'          => 'after_x_sessions',
				'default'     => 'no',
				'transport'   => 'postMessage',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
			],
			'number_of_sessions'   => [
				'type'        => 'text',
				'label'       => esc_html__( 'Number Of Sessions', 'Avada' ),
				'description' => esc_html__( 'Set the number of visiting sessions needed to display the Off Canvas, ex: 3.', 'Avada' ),
				'id'          => 'number_of_sessions',
				'default'     => '',
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'after_x_sessions',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],
			'number_of_times'      => [
				'type'        => 'text',
				'label'       => esc_html__( 'Number Of Times', 'Avada' ),
				'description' => esc_html__( 'Set the number of times the Off Canvas should be displayed, ex: 3.', 'Avada' ),
				'id'          => 'number_of_times',
				'default'     => '',
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'for_x_times',
						'value'      => 'yes',
						'comparison' => '==',
					],
				],
			],

			// Show When Arriving From.
			'when_arriving_from'   => [
				'type'        => 'multiple_select',
				'label'       => esc_html__( 'Show When Arriving From', 'Avada' ),
				'description' => esc_html__( 'Choose when Off Canvas should be displayed depending on referring link type.', 'Avada' ),
				'id'          => 'when_arriving_from',
				'default'     => '',
				'transport'   => 'postMessage',
				'choices'     => [
					'internal' => esc_html__( 'Internal Links', 'Avada' ),
					'external' => esc_html__( 'External Links', 'Avada' ),
					'search'   => esc_html__( 'Search Engine', 'Avada' ),
				],
			],

			// Users rule.
			'users'                => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Users', 'Avada' ),
				'description' => esc_html__( 'Choose the user type that will be able to see the Off Canvas.', 'Avada' ),
				'id'          => 'users',
				'default'     => 'all',
				'transport'   => 'postMessage',
				'choices'     => [
					'all'        => esc_html__( 'All Users', 'Avada' ),
					'logged-in'  => esc_html__( 'Logged in users', 'Avada' ),
					'logged-out' => esc_html__( 'Logged out users', 'Avada' ),
				],
			],
			'users_roles'          => [
				'type'        => 'multiple_select',
				'label'       => esc_html__( 'User Roles', 'Avada' ),
				'description' => esc_html__( 'Display Off Canvas for specific user roles. Leave blank for all.', 'Avada' ),
				'id'          => 'users_roles',
				'default'     => '',
				'transport'   => 'postMessage',
				'choices'     => $user_roles_array,
				'dependency'  => [
					[
						'field'      => 'users',
						'value'      => 'logged-in',
						'comparison' => '==',
					],
				],
			],

			// Device.
			'device'               => [
				'type'        => 'multiple_select',
				'label'       => esc_html__( 'Device', 'Avada' ),
				'description' => esc_html__( 'Display Off Canvas for specific device types. Leave blank for all.', 'Avada' ),
				'id'          => 'device',
				'default'     => '',
				'transport'   => 'postMessage',
				'choices'     => [
					'desktop' => esc_html__( 'Desktop', 'Avada' ),
					'tablet'  => esc_html__( 'Tablet', 'Avada' ),
					'mobile'  => esc_html__( 'Mobile', 'Avada' ),
				],
			],
		],
	];

	return apply_filters( 'avada_off_canvas_rules_sections', $sections );

}

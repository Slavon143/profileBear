<?php
/**
 * Form General Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage forms
 */

/**
 * Form General page settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_form_general( $sections ) {

	if ( ! function_exists( 'get_editable_roles' ) ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
	}

	$user_roles       = get_editable_roles();
	$user_roles_array = [];
	foreach ( $user_roles as $id => $role ) {
		$user_roles_array[ $id ] = translate_user_role( $role['name'] );
	}

	$sections['form_general'] = [
		'label'    => esc_html__( 'General', 'Avada' ),
		'alt_icon' => 'fusiona-file',
		'id'       => 'form_general',
		'fields'   => [
			'member_only_form' => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Enable Member Only Form', 'Avada' ),
				'description' => esc_html__( 'Select if you want to display this form to only logged in users with specific user roles.', 'Avada' ),
				'id'          => 'member_only_form',
				'default'     => '0',
				'transport'   => 'postMessage',
				'choices'     => [
					'1' => esc_html__( 'Yes', 'Avada' ),
					'0' => esc_html__( 'No', 'Avada' ),
				],
				'dependency'  => [],
			],
			'user_roles'       => [
				'type'        => 'multiple_select',
				'label'       => esc_html__( 'Select User Role(s)', 'Avada' ),
				'description' => esc_html__( 'Select user role(s) you want to display this form to. Leaving blank will display form to any logged in user.', 'Avada' ),
				'id'          => 'user_roles',
				'choices'     => $user_roles_array,
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'member_only_form',
						'value'      => '0',
						'comparison' => '!=',
					],
				],
			],
			'nonce_method'     => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Security Nonce Method', 'fusion-builder' ),
				/* translators: %1$s: Opening anchor tag. %2$s: Closing anchor tag. */
				'description' => sprintf( __( 'Select the method which is used to add %1$s nonce %2$s field to the form. Note that Localized might cause problems if page\'s HTML is cached.', 'fusion-builder' ), '<a href="https://codex.wordpress.org/WordPress_Nonces" target="_blank" rel="noopener noreferrer">', '</a>' ),
				'id'          => 'nonce_method',
				'default'     => 'ajax',
				'choices'     => [
					'none'      => esc_html__( 'None', 'fusion-builder' ),
					'ajax'      => esc_html__( 'AJAX', 'fusion-builder' ),
					'localized' => esc_html__( 'Localized', 'fusion-builder' ),
				],
				'dependency'  => [],
				'transport'   => 'postMessage',
			],
		],
	];

	return apply_filters( 'avada_form_submission_sections', $sections );

}

<?php
/**
 * Form Notifications Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage forms
 */

/**
 * Form Notifications page settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_form_notifications( $sections ) {

	if ( ! function_exists( 'get_editable_roles' ) ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
	}

	$user_roles       = get_editable_roles();
	$user_roles_array = [];
	foreach ( $user_roles as $id => $role ) {
		$user_roles_array[ $id ] = translate_user_role( $role['name'] );
	}

	$sections['form_notifications'] = [
		'label'    => esc_html__( 'Notifications', 'Avada' ),
		'alt_icon' => 'fusiona-envelope',
		'id'       => 'form_notifications',
		'fields'   => [
			'email_placeholders'   => [
				'type'        => 'custom',
				'label'       => '',
				/* translators: Documentation post link. */
				'description' => '<div class="fusion-redux-important-notice">' . sprintf( __( '<strong>IMPORTANT NOTE:</strong> In email options, field names within square brackets can be used as placeholders which will be replaced when the form is submitted, ie: [email_address]. For more information check out our <a href="%s" target="_blank">form placeholders post</a>.', 'Avada' ), 'https://theme-fusion.com/documentation/avada/forms/avada-forms-email-submission-placeholders/' ) . '</div>',
				'id'          => 'email_placeholders',
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'ajax',
						'comparison' => '==',
					],
				],
			],
			'notifications_hidden' => [
				'type'        => 'custom',
				'label'       => '',
				'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> For notifications setup, please set submission type to "AJAX" .', 'Avada' ) . '</div>',
				'id'          => 'notifications_hidden',
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'post',
						'comparison' => '==',
					],
				],
			],
			'notifications'        => [
				'type'        => 'repeater',
				'label'       => esc_html__( 'Notifications', 'Avada' ),
				'description' => esc_html__( 'Add form notifications.', 'Avada' ),
				'id'          => 'notifications',
				'row_add'     => esc_html__( 'Add Notification', 'Avada' ),
				'row_title'   => esc_html__( 'Notification', 'Avada' ),
				'bind_title'  => 'label',
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'ajax',
						'comparison' => '==',
					],
				],
				'transport'   => 'postMessage',
				'fields'      => [
					'label'                => [
						'type'        => 'text',
						'label'       => esc_html__( 'Label', 'Avada' ),
						'id'          => 'label',
						'description' => esc_html__( 'Enter notification label.', 'Avada' ),
						'default'     => esc_html__( 'Notification', 'Avada' ),
						'transport'   => 'postMessage',
					],
					'email'                => [
						'type'        => 'text',
						'label'       => esc_html__( 'Email', 'Avada' ),
						'id'          => 'email',
						'description' => esc_html__( 'Enter email ID where form data should be sent to. If left empty, email will be sent to the WordPress admin.', 'Avada' ),
						'transport'   => 'postMessage',
					],
					'email_subject'        => [
						'type'        => 'text',
						'label'       => esc_html__( 'Email Subject', 'Avada' ),
						'description' => esc_html__( 'Enter email subject. If left empty, the form title will be used.', 'Avada' ),
						'id'          => 'email_subject',
						'default'     => esc_html__( 'Form submission received', 'Avada' ),
						'transport'   => 'postMessage',
					],
					'email_subject_encode' => [
						'type'        => 'radio-buttonset',
						'label'       => esc_html__( 'Encode Email Subject', 'Avada' ),
						'description' => esc_html__( 'Select if you want to encode email subjects. This helps to display special characters correctly in the subject field. A few hosting environments and email clients might have issues with this setting.', 'Avada' ),
						'id'          => 'email_subject_encode',
						'default'     => '0',
						'transport'   => 'postMessage',
						'choices'     => [
							'1' => esc_html__( 'Yes', 'Avada' ),
							'0' => esc_html__( 'No', 'Avada' ),
						],
					],
					'email_from'           => [
						'type'        => 'text',
						'label'       => esc_html__( 'Email From Name', 'Avada' ),
						'description' => esc_html__( 'Enter email from name. If left empty, WordPress will be used.', 'Avada' ),
						'id'          => 'email_from',
						'default'     => get_bloginfo( 'name' ),
						'transport'   => 'postMessage',
					],
					'email_from_id'        => [
						'type'        => 'text',
						'label'       => esc_html__( 'Sender Email', 'Avada' ),
						'description' => esc_html__( 'Enter sender email address. If left empty, wordpress@sitename.com will be used.', 'Avada' ),
						'id'          => 'email_from_id',
						'default'     => get_bloginfo( 'admin_email' ),
						'transport'   => 'postMessage',
					],
					'email_reply_to'       => [
						'type'        => 'text',
						'label'       => esc_html__( 'Reply To Email', 'Avada' ),
						'description' => esc_html__( 'Enter reply to email address. ', 'Avada' ),
						'id'          => 'email_reply_to',
						'default'     => '',
						'transport'   => 'postMessage',
					],
					'email_attachments'    => [
						'type'        => 'radio-buttonset',
						'default'     => 'no',
						'label'       => esc_html__( 'Attach Uploaded Files', 'Avada' ),
						'id'          => 'email_attachments',
						'description' => esc_html__( 'Add uploaded files as email attachments.', 'Avada' ),
						'transport'   => 'postMessage',
						'choices'     => [
							'yes' => esc_html__( 'Yes', 'Avada' ),
							'no'  => esc_html__( 'No', 'Avada' ),
						],
					],
					'email_message'        => [
						'type'        => 'textarea',
						'default'     => '',
						'label'       => esc_html__( 'Email Message', 'Avada' ),
						'id'          => 'email_message',
						'description' => esc_html__( 'Enter email message, leave empty to get the default message with all form fields. You can add form fields to the message by insert field name wrapped with square brackets ie: [email_address], also you can add all fields with [all_fields] tag.', 'Avada' ),
						'transport'   => 'postMessage',
					],

				],
			],
		],
	];

	return apply_filters( 'avada_form_notifications_sections', $sections );

}

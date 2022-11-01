<?php
/**
 * Form Submissions Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage forms
 */

/**
 * Form Submissions page settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_form_submission( $sections ) {

	$sections['form_submission'] = [
		'label'    => esc_html__( 'Submission', 'Avada' ),
		'alt_icon' => 'fusiona-submission',
		'id'       => 'form_submission',
		'fields'   => [
			'form_type'        => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Submission Type', 'Avada' ),
				'description' => __( 'Select "AJAX" to store form data using predefined methods and "POST" if you want to implement your own. <strong>NOTE:</strong> Notification will be only sent when submission type is set to "AJAX". To receive Email submission notifications, please visit the Notifications tab.', 'Avada' ),
				'id'          => 'form_type',
				'default'     => 'ajax',
				'choices'     => [
					'ajax' => esc_html__( 'AJAX', 'Avada' ),
					'post' => esc_html__( 'POST', 'Avada' ),
				],
				'transport'   => 'postMessage',
			],
			'form_actions'     => [
				'type'             => 'multiple_select',
				'label'            => esc_html__( 'Actions', 'Avada' ),
				'description'      => esc_html__( 'Select actions to apply when form submit.', 'Avada' ),
				'placeholder_text' => esc_html__( 'Select or Leave Blank for None.', 'Avada' ),
				'id'               => 'form_actions',
				'default'          => '',
				'choices'          => [
					'database'   => esc_html__( 'Save To Database', 'Avada' ),
					'url'        => esc_html__( 'Send To URL', 'Avada' ),
					'mailchimp'  => esc_html__( 'Mailchimp', 'Avada' ),
					'hubspot'    => esc_html__( 'HubSpot', 'Avada' ),
					'off-canvas' => esc_html__( 'Open Off Canvas', 'Avada' ),
				],
				'dependency'       => [
					[
						'field'      => 'form_type',
						'value'      => 'ajax',
						'comparison' => '==',
					],
				],
				'transport'        => 'postMessage',
			],
			'entries_notice'   => [
				'type'        => 'custom',
				'label'       => '',
				/* translators: Form entries link. */
				'description' => '<div class="fusion-redux-important-notice">' . sprintf( __( '<strong>IMPORTANT NOTE:</strong> You can view and manage form submissions by going to <a href="%s" target="_blank">form entries</a> section and selecting this form from the dropdown list.', 'Avada' ), admin_url( 'admin.php?page=avada-form-entries' ) ) . '</div>',
				'id'          => 'entries_notice',
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'ajax',
						'comparison' => '==',
					],
					[
						'field'      => 'form_actions',
						'value'      => 'database',
						'comparison' => 'contains',
					],
				],
			],
			'method'           => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Submission Method', 'Avada' ),
				'description' => esc_html__( 'Make a selection for form submission method.', 'Avada' ),
				'id'          => 'method',
				'default'     => 'post',
				'transport'   => 'postMessage',
				'choices'     => [
					'post' => esc_html__( 'POST', 'Avada' ),
					'get'  => esc_html__( 'GET', 'Avada' ),
				],
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'post',
						'comparison' => '==',
					],
				],
			],
			'post_method_url'  => [
				'type'        => 'text',
				'label'       => esc_html__( 'Form Submission URL', 'Avada' ),
				'id'          => 'post_method_url',
				'description' => esc_html__( 'Enter the URL where form data should be sent to.', 'Avada' ),
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'post',
						'comparison' => '==',
					],
				],
			],
			'form_url_options' => [
				'type'       => 'toggle',
				'row_title'  => esc_html__( 'URL', 'Avada' ),
				'id'         => 'form_url_options',
				'transport'  => 'postMessage',
				'dependency' => [
					[
						'field'      => 'form_type',
						'value'      => 'ajax',
						'comparison' => '==',
					],
					[
						'field'      => 'form_actions',
						'value'      => 'url',
						'comparison' => 'contains',
					],
				],
				'fields'     => [
					'url_method'     => [
						'type'        => 'radio-buttonset',
						'label'       => esc_html__( 'Submission Method', 'Avada' ),
						'description' => esc_html__( 'Make a selection for form submission method.', 'Avada' ),
						'id'          => 'url_method',
						'default'     => 'post',
						'transport'   => 'postMessage',
						'choices'     => [
							'post' => esc_html__( 'POST', 'Avada' ),
							'get'  => esc_html__( 'GET', 'Avada' ),
						],
					],
					'action'         => [
						'type'        => 'text',
						'label'       => esc_html__( 'Form Submission URL', 'Avada' ),
						'id'          => 'action',
						'description' => esc_html__( 'Enter the URL where form data should be sent to.', 'Avada' ),
						'transport'   => 'postMessage',
					],
					'custom_headers' => [
						'type'        => 'repeater',
						'label'       => esc_html__( 'Custom Headers', 'Avada' ),
						'description' => esc_html__( 'If you are using this form to integrate with a third-party API, you can use custom headers to implement authentication or pass-on any extra headers the API requires.', 'Avada' ),
						'id'          => 'custom_headers',
						'default'     => [],
						'row_add'     => 'Add Header',
						'row_title'   => 'Custom Header',
						'bind_title'  => 'header_key',
						'transport'   => 'postMessage',
						'fields'      => [
							'header_key'   => [
								'id'          => 'header_key',
								'type'        => 'text',
								'label'       => esc_html__( 'Custom Header Key', 'Avada' ),
								'description' => __( 'Enter the key for the request\'s custom header. Example: <code>Content-Type</code>', 'Avada' ),
								'default'     => '',
							],
							'header_value' => [
								'id'          => 'header_value',
								'type'        => 'text',
								'label'       => esc_html__( 'Custom Header Value', 'Avada' ),
								'description' => esc_html__( 'Enter the value for your custom-header.', 'Avada' ),
								'default'     => '',
							],
						],
					],
				],
			],
		],
	];

	if ( class_exists( 'AWB_Off_Canvas' ) && false !== AWB_Off_Canvas::is_enabled() ) {
		$sections['form_submission']['fields']['off_canvas'] = [
			'type'        => 'select',
			'label'       => esc_html__( 'Off Canvas', 'Avada' ),
			'description' => esc_html__( 'Select Off Canvas to open after form submission.', 'Avada' ),
			'id'          => 'off_canvas',
			'default'     => 'post',
			'transport'   => 'postMessage',
			'choices'     => AWB_Off_Canvas_Front_End()->get_available_items(),
			'dependency'  => [
				[
					'field'      => 'form_type',
					'value'      => 'ajax',
					'comparison' => '==',
				],
				[
					'field'      => 'form_actions',
					'value'      => 'off-canvas',
					'comparison' => 'contains',
				],
			],
		];
	}

	return apply_filters( 'avada_form_submission_sections', $sections );

}

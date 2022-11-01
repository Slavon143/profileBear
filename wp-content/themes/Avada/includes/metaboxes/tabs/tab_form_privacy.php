<?php
/**
 * Form Submissions Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    fusion-builder
 * @subpackage forms
 */

/**
 * Form Submissions page settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_form_privacy( $sections ) {
	$sections['form_privacy'] = [
		'label'    => esc_html__( 'Privacy', 'Avada' ),
		'alt_icon' => 'fusiona-privacy',
		'id'       => 'form_privacy',
		'fields'   => [
			'privacy_store_ip_ua'         => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Store IP and User-Agent.', 'Avada' ),
				'description' => esc_html__( 'Select if you want to store the IP and User-Agent on submissions. Depending on the legislation that applies to your site, you may need to disable this option.', 'Avada' ),
				'id'          => 'privacy_store_ip_ua',
				'default'     => 'no',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'Avada' ),
					'no'  => esc_html__( 'No', 'Avada' ),
				],
				'transport'   => 'postMessage',
			],
			'privacy_expiration_interval' => [
				'type'        => 'text',
				'label'       => esc_html__( 'Duration of Submissions Log (Months)', 'Avada' ),
				'description' => esc_html__( 'Select the duration for which form submissions will be kept in months. You can choose what happens on expiration below.', 'Avada' ),
				'id'          => 'privacy_expiration_interval',
				'default'     => '48',
				'transport'   => 'postMessage',
			],
			'privacy_expiration_action'   => [
				'type'        => 'select',
				'label'       => esc_html__( 'Submission Expiration Action', 'Avada' ),
				'description' => esc_html__( 'Choose what will happen when the time defined above comes to pass. If you have selected to log the user\'s IP & UA above, selecting "Anonymize" will delete these from the log. If you want old entries to be deleted automatically after a certain time, then select "Delete".', 'Avada' ),
				'id'          => 'privacy_expiration_action',
				'default'     => 'anonymize',
				'dependency'  => [],
				'transport'   => 'postMessage',
				'choices'     => [
					'ignore'    => esc_html__( 'No Action', 'Avada' ),
					'anonymize' => esc_html__( 'Anonymize IP and User-Agent', 'Avada' ),
					'delete'    => esc_html__( 'Delete Submission', 'Avada' ),
				],
			],
		],
	];
	return $sections;
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

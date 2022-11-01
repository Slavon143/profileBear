<?php
/**
 * Avada Options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      4.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Privacy settings.
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_privacy( $sections ) {
	$embed_types = [];
	if ( class_exists( 'Avada_Privacy_Embeds' ) ) {
		$embed_types    = Avada()->privacy_embeds->get_embed_defaults( true );
		$embed_defaults = array_keys( $embed_types );
	}

	$sections['privacy'] = [
		'label'    => esc_html__( 'Privacy', 'Avada' ),
		'id'       => 'heading_privacy',
		'priority' => 25,
		'icon'     => 'el-icon-user',
		'alt_icon' => 'fusiona-privacy',
		'fields'   => [
			'privacy_note'                   => [
				'label'       => '',
				'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The options in this section will help to easier comply with data privacy regulations, like the European GDPR. When the "Privacy Consent" option is used, Avada will create a cookie with the name <b>"privacy_embeds"</b> on user clients browsing your site to manage and store user consent to load the different third party embeds and tracking scripts. You may want to add information about this cookie to your privacy page.', 'Avada' ) . '</div>',
				'id'          => 'privacy_note',
				'type'        => 'custom',
			],
			'gfonts_load_method'             => [
				'id'          => 'gfonts_load_method',
				'label'       => esc_html__( 'Google & Font Awesome Fonts Mode', 'Avada' ),
				'description' => esc_html__( 'When set to "Local", the Google and Font Awesome fonts set in Global Options will be downloaded to your server. Set to "CDN" to use the Google and FontAwesome CDNs.', 'Avada' ),
				'type'        => 'radio-buttonset',
				'default'     => 'cdn',
				'choices'     => [
					'local' => esc_html__( 'Local', 'Avada' ),
					'cdn'   => esc_html__( 'CDN', 'Avada' ),
				],
				// No need to refresh the page.
				'transport'   => 'postMessage',
			],
			'privacy_embeds'                 => [
				'label'       => esc_html__( 'Privacy Consent', 'Avada' ),
				'description' => esc_html__( 'Turn on to prevent embeds and scripts from loading until user consent is given.', 'Avada' ),
				'id'          => 'privacy_embeds',
				'default'     => '0',
				'type'        => 'switch',
				// No need to refresh the page.
				'transport'   => 'postMessage',
			],
			'privacy_expiry'                 => [
				'label'       => esc_html__( 'Privacy Consent Cookie Expiration', 'Avada' ),
				'description' => esc_html__( 'Controls how long the consent cookie should be stored for.  In days.', 'Avada' ),
				'id'          => 'privacy_expiry',
				'default'     => '30',
				'type'        => 'slider',
				'choices'     => [
					'min'  => '1',
					'max'  => '366',
					'step' => '1',
				],
				'required'    => [
					[
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					],
				],
				// No need to refresh the page.
				'transport'   => 'postMessage',
			],
			'privacy_embed_types'            => [
				'label'       => esc_html__( 'Privacy Consent Types', 'Avada' ),
				'description' => esc_html__( 'Select the types of embeds which you would like to require consent.', 'Avada' ),
				'id'          => 'privacy_embed_types',
				'default'     => $embed_defaults,
				'type'        => 'select',
				'multi'       => true,
				'choices'     => $embed_types,
				'required'    => [
					[
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					],
				],
				// No need to refresh the page.
				'transport'   => 'postMessage',
			],
			'privacy_embed_defaults'         => [
				'label'       => esc_html__( 'Privacy Selected Consent Types', 'Avada' ),
				'description' => esc_html__( 'Select the types of embeds which you would like to have checked by default.  This applies to both the privacy bar and the privacy element.', 'Avada' ),
				'id'          => 'privacy_embed_defaults',
				'default'     => [],
				'type'        => 'select',
				'multi'       => true,
				'choices'     => $embed_types,
				'required'    => [
					[
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					],
				],
				// No need to refresh the page.
				'transport'   => 'postMessage',
			],
			'privacy_bg_color'               => [
				'label'       => esc_html__( 'Privacy Placeholder Background Color', 'Avada' ),
				'description' => esc_html__( 'Controls the background color for the privacy placeholders.', 'Avada' ),
				'id'          => 'privacy_bg_color',
				'type'        => 'color-alpha',
				'default'     => 'hsla(var(--awb-color8-h),var(--awb-color8-s),var(--awb-color8-l),calc(var(--awb-color8-a) - 90%))',
				'required'    => [
					[
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--privacy_bg_color',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'privacy_color'                  => [
				'label'       => esc_html__( 'Privacy Placeholder Text Color', 'Avada' ),
				'description' => esc_html__( 'Controls the text color for the embed placeholders.', 'Avada' ),
				'id'          => 'privacy_color',
				'type'        => 'color-alpha',
				'default'     => 'hsla(var(--awb-color8-h),var(--awb-color8-s),var(--awb-color8-l),calc(var(--awb-color8-a) - 70%))',
				'required'    => [
					[
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--privacy_color',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'privacy_bar'                    => [
				'label'       => esc_html__( 'Privacy Bar', 'Avada' ),
				'description' => esc_html__( 'Turn on to enable a privacy bar at the bottom of the page.', 'Avada' ),
				'id'          => 'privacy_bar',
				'default'     => '0',
				'type'        => 'switch',
			],
			'privacy_bar_padding'            => [
				'label'       => esc_html__( 'Privacy Bar Padding', 'Avada' ),
				'description' => esc_html__( 'Controls the top/right/bottom/left paddings of the privacy bar area.', 'Avada' ),
				'id'          => 'privacy_bar_padding',
				'default'     => [
					'top'    => '15px',
					'bottom' => '15px',
					'left'   => '30px',
					'right'  => '30px',
				],
				'choices'     => [
					'top'    => true,
					'bottom' => true,
					'left'   => true,
					'right'  => true,
				],
				'type'        => 'spacing',
				'required'    => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
				],
				'css_vars'    => [
					[
						'name'   => '--privacy_bar_padding-top',
						'choice' => 'top',
					],
					[
						'name'   => '--privacy_bar_padding-bottom',
						'choice' => 'bottom',
					],
					[
						'name'   => '--privacy_bar_padding-left',
						'choice' => 'left',
					],
					[
						'name'   => '--privacy_bar_padding-right',
						'choice' => 'right',
					],
				],
			],
			'privacy_bar_bg_color'           => [
				'label'       => esc_html__( 'Privacy Bar Background Color', 'Avada' ),
				'description' => esc_html__( 'Controls the background color for the privacy bar.', 'Avada' ),
				'id'          => 'privacy_bar_bg_color',
				'type'        => 'color-alpha',
				'default'     => 'var(--awb-color8)',
				'required'    => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--privacy_bar_bg_color',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'privacy_bar_font_size'          => [
				'label'       => esc_html__( 'Privacy Bar Font Size', 'Avada' ),
				'description' => esc_html__( 'Controls the font size for the privacy bar content.', 'Avada' ),
				'id'          => 'privacy_bar_font_size',
				'default'     => '13px',
				'type'        => 'dimension',
				'choices'     => [
					'units' => [ 'px', 'em' ],
				],
				'required'    => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
				],
				'css_vars'    => [
					[
						'name' => '--privacy_bar_font_size',
					],
				],
			],
			'privacy_bar_color'              => [
				'label'       => esc_html__( 'Privacy Bar Text Color', 'Avada' ),
				'description' => esc_html__( 'Controls the text color for the privacy bar content.', 'Avada' ),
				'id'          => 'privacy_bar_color',
				'type'        => 'color-alpha',
				'default'     => 'var(--awb-color6)',
				'required'    => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--privacy_bar_color',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'privacy_bar_link_color'         => [
				'label'       => esc_html__( 'Privacy Bar Link Color', 'Avada' ),
				'description' => esc_html__( 'Controls the link color for the privacy bar.', 'Avada' ),
				'id'          => 'privacy_bar_link_color',
				'type'        => 'color-alpha',
				'default'     => 'var(--awb-color2)',
				'required'    => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--privacy_bar_link_color',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'privacy_bar_link_hover_color'   => [
				'label'       => esc_html__( 'Privacy Bar Link Hover Color', 'Avada' ),
				'description' => esc_html__( 'Controls the link hover color for the privacy bar.', 'Avada' ),
				'id'          => 'privacy_bar_link_hover_color',
				'type'        => 'color-alpha',
				'default'     => 'var(--awb-color4)',
				'required'    => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--privacy_bar_link_hover_color',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'privacy_bar_text'               => [
				'label'           => esc_html__( 'Privacy Bar Text', 'Avada' ),
				'description'     => esc_html__( 'Enter the text which you want to appear on the privacy bar.', 'Avada' ),
				'id'              => 'privacy_bar_text',
				'default'         => esc_html__( 'This website uses cookies and third party services.', 'Avada' ),
				'type'            => 'textarea',
				'required'        => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
				],
				// Partial refresh for the searchform.
				'partial_refresh' => [
					'privacy_bar_text_partial' => [
						'selector'              => '.fusion-privacy-bar.fusion-privacy-bar-bottom',
						'container_inclusive'   => true,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'privacy_bar' ],
						'success_trigger_event' => [ 'fusionPrivacyBar' ],
					],
				],
			],
			'privacy_bar_button_text'        => [
				'label'           => esc_html__( 'Privacy Bar Button Text', 'Avada' ),
				'description'     => esc_html__( 'Controls the button text for the privacy bar acceptance.', 'Avada' ),
				'id'              => 'privacy_bar_button_text',
				'default'         => esc_html__( 'OK', 'Avada' ),
				'type'            => 'text',
				'required'        => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
				],
				// Partial refresh for the searchform.
				'partial_refresh' => [
					'privacy_bar_button_text_partial' => [
						'selector'              => '.fusion-privacy-bar.fusion-privacy-bar-bottom',
						'container_inclusive'   => true,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'privacy_bar' ],
						'success_trigger_event' => [ 'fusionPrivacyBar' ],
					],
				],
			],
			'privacy_bar_button_save'        => [
				'label'       => esc_html__( 'Privacy Bar Button Save On Click', 'Avada' ),
				'description' => esc_html__( 'If enabled, when the button is clicked it will save the default consent selection.  If disabled the button will only save the preferences after a checkbox has been changed (bar will be hidden however).', 'Avada' ),
				'id'          => 'privacy_bar_button_save',
				'default'     => '0',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
					[
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the avadaPrivacyVars.button var.
					[
						'element'           => 'helperElement',
						'property'          => 'bottom',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'avadaPrivacyVars',
								'id'        => 'button',
								'trigger'   => [ 'fusionPrivacyBar' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'privacy_bar_more'               => [
				'label'           => esc_html__( 'Privacy Bar Settings', 'Avada' ),
				'description'     => esc_html__( 'If enabled, a settings section will be added to show more information and to provide checkboxes for tracking and third party embeds.', 'Avada' ),
				'id'              => 'privacy_bar_more',
				'default'         => '0',
				'type'            => 'switch',
				'required'        => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
				],
				// Partial refresh for the searchform.
				'partial_refresh' => [
					'privacy_bar_more_partial' => [
						'selector'              => '.fusion-privacy-bar.fusion-privacy-bar-bottom',
						'container_inclusive'   => true,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'privacy_bar' ],
						'success_trigger_event' => [ 'fusionPrivacyBar' ],
					],
				],
			],
			'privacy_bar_more_text'          => [
				'label'           => esc_html__( 'Privacy Bar Settings Text', 'Avada' ),
				'description'     => esc_html__( 'Controls the link text for the privacy bar settings.', 'Avada' ),
				'id'              => 'privacy_bar_more_text',
				'default'         => esc_html__( 'Settings', 'Avada' ),
				'type'            => 'text',
				'required'        => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
					[
						'setting'  => 'privacy_bar_more',
						'operator' => '!=',
						'value'    => '0',
					],
				],
				// Partial refresh for the searchform.
				'partial_refresh' => [
					'privacy_bar_more_text_partial' => [
						'selector'              => '.fusion-privacy-bar.fusion-privacy-bar-bottom',
						'container_inclusive'   => true,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'privacy_bar' ],
						'success_trigger_event' => [ 'fusionPrivacyBar' ],
					],
				],
			],
			'privacy_bar_update_text'        => [
				'label'           => esc_html__( 'Privacy Bar Update Button Text', 'Avada' ),
				'description'     => esc_html__( 'Controls the button text for the privacy bar after a checkbox has changed.', 'Avada' ),
				'id'              => 'privacy_bar_update_text',
				'default'         => esc_html__( 'Update Settings', 'Avada' ),
				'type'            => 'text',
				'required'        => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
					[
						'setting'  => 'privacy_bar_more',
						'operator' => '!=',
						'value'    => '0',
					],
					[
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					],
				],
				// Partial refresh for the searchform.
				'partial_refresh' => [
					'privacy_bar_update_text_partial' => [
						'selector'              => '.fusion-privacy-bar.fusion-privacy-bar-bottom',
						'container_inclusive'   => true,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'privacy_bar' ],
						'success_trigger_event' => [ 'fusionPrivacyBar' ],
					],
				],
			],
			'privacy_bar_headings_font_size' => [
				'label'       => esc_html__( 'Privacy Bar Heading Font Size', 'Avada' ),
				'description' => esc_html__( 'Controls the font size for the privacy bar heading text.', 'Avada' ),
				'id'          => 'privacy_bar_headings_font_size',
				'default'     => '13px',
				'type'        => 'dimension',
				'choices'     => [
					'units' => [ 'px', 'em' ],
				],
				'required'    => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
					[
						'setting'  => 'privacy_bar_more',
						'operator' => '!=',
						'value'    => '0',
					],
				],
			],
			'privacy_bar_headings_color'     => [
				'label'       => esc_html__( 'Privacy Bar Headings Color', 'Avada' ),
				'description' => esc_html__( 'Controls the text color of the privacy bar heading font.', 'Avada' ),
				'id'          => 'privacy_bar_headings_color',
				'default'     => 'var(--awb-color1)',
				'type'        => 'color-alpha',
				'required'    => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
					[
						'setting'  => 'privacy_bar_more',
						'operator' => '!=',
						'value'    => '0',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--privacy_bar_headings_color',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'privacy_bar_content'            => [
				'label'       => esc_html__( 'Privacy Bar Content', 'Avada' ),
				'description' => esc_html__( 'The privacy bar content uses a repeater field to select the content for each column. Click the "Add" button to add additional columns.', 'Avada' ),
				'id'          => 'privacy_bar_content',
				'default'     => [],
				'type'        => 'repeater',
				'bind_title'  => 'title',
				'limit'       => 6,
				'fields'      => [
					'type'        => [
						'id'          => 'type',
						'type'        => 'select',
						'description' => esc_html__( 'Select the type of cookie/content to display.', 'Avada' ),
						'default'     => 'custom',
						'choices'     => [
							'custom'   => 'Custom',
							'tracking' => 'Tracking Cookies',
							'embeds'   => 'Third Party Embeds',
						],
					],
					'title'       => [
						'id'      => 'title',
						'type'    => 'text',
						'label'   => esc_html__( 'Title for the content', 'Avada' ),
						'default' => '',
					],
					'description' => [
						'id'      => 'description',
						'type'    => 'textarea',
						'label'   => esc_html__( 'Description for the content', 'Avada' ),
						'default' => '',
					],
				],
				'required'    => [
					[
						'setting'  => 'privacy_bar_more',
						'operator' => '!=',
						'value'    => '0',
					],
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
				],
			],
			'privacy_bar_reject'             => [
				'label'           => esc_html__( 'Privacy Bar Reject Button', 'Avada' ),
				'description'     => esc_html__( 'If enabled, the privacy bar will be extended with a button that allows users to reject all.', 'Avada' ),
				'id'              => 'privacy_bar_reject',
				'default'         => '0',
				'type'            => 'switch',
				'required'        => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
					[
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					],
				],
				// Partial refresh for the searchform.
				'partial_refresh' => [
					'privacy_bar_reject_partial' => [
						'selector'              => '.fusion-privacy-bar.fusion-privacy-bar-bottom',
						'container_inclusive'   => true,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'privacy_bar' ],
						'success_trigger_event' => [ 'fusionPrivacyBar' ],
					],
				],
			],
			'privacy_bar_reject_text'        => [
				'label'           => esc_html__( 'Privacy Bar Reject Button Text', 'Avada' ),
				'description'     => esc_html__( 'Controls the link text for the privacy bar reject button.', 'Avada' ),
				'id'              => 'privacy_bar_reject_text',
				'default'         => esc_html__( 'Reject', 'Avada' ),
				'type'            => 'text',
				'required'        => [
					[
						'setting'  => 'privacy_bar',
						'operator' => '!=',
						'value'    => '0',
					],
					[
						'setting'  => 'privacy_bar_reject',
						'operator' => '!=',
						'value'    => '0',
					],
					[
						'setting'  => 'privacy_embeds',
						'operator' => '==',
						'value'    => '1',
					],
				],
				// Partial refresh for the searchform.
				'partial_refresh' => [
					'privacy_bar_reject_text_partial' => [
						'selector'              => '.fusion-privacy-bar.fusion-privacy-bar-bottom',
						'container_inclusive'   => true,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'privacy_bar' ],
						'success_trigger_event' => [ 'fusionPrivacyBar' ],
					],
				],
			],
		],
	];

	return $sections;

}

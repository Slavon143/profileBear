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
 * Contact
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_contact( $sections ) {

	$option_name = Avada::get_option_name();
	$settings    = (array) get_option( $option_name );
	if ( ! isset( $settings['map_overlay_color'] ) ) {
		$settings['map_overlay_color'] = '#65bc7b';
	}

	$contact_page_callback = [
		[
			'where'     => 'postMeta',
			'condition' => '_wp_page_template',
			'operator'  => '===',
			'value'     => 'contact.php',
		],
	];

	$sections['contact'] = [
		'label'    => esc_html__( 'Contact Template', 'Avada' ),
		'id'       => 'heading_contact',
		'priority' => 22,
		'is_panel' => true,
		'icon'     => 'el el-map-marker',
		'alt_icon' => 'fusiona-envelope',
		'fields'   => [
			'contact_form_options_subsection'   => [
				'label'       => esc_html__( 'Contact Template', 'Avada' ),
				'description' => '',
				'id'          => 'contact_form_options_subsection',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'contact_form_important_note_info' => [
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The options on this tab are only for the contact form that displays on the "Contact" page template.', 'Avada' ) . '</div>',
						'id'          => 'contact_form_important_note_info',
						'type'        => 'custom',
					],
					'email_address'                    => [
						'label'       => esc_html__( 'Email Address', 'Avada' ),
						'description' => esc_html__( 'Enter the email address the form should be sent to. This only works for the form on the contact page template.', 'Avada' ),
						'id'          => 'email_address',
						'default'     => '',
						'type'        => 'text',
						// This option doesn't require updating the preview.
						'transport'   => 'postMessage',
					],
					'contact_comment_position'         => [
						'label'           => esc_html__( 'Contact Form Comment Area Position', 'Avada' ),
						'description'     => esc_html__( 'Controls the position of the comment field with respect to the other fields.', 'Avada' ),
						'id'              => 'contact_comment_position',
						'default'         => 'below',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'above' => esc_html__( 'Above', 'Avada' ),
							'below' => esc_html__( 'Below', 'Avada' ),
						],
						'edit_shortcut'   => [
							'selector'  => [ '.fusion-contact-form' ],
							'shortcuts' => [
								[
									'aria_label'  => esc_html__( 'Edit Contact Form', 'Avada' ),
									'icon'        => 'fusiona-pen',
									'open_parent' => true,
								],
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'contact_form_privacy_checkbox'    => [
						'label'           => esc_html__( 'Display Data Privacy Confirmation Box', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display a checkbox and custom label that has to be checked in order to confirm data privacy terms and that the form can be sent.', 'Avada' ),
						'id'              => 'contact_form_privacy_checkbox',
						'default'         => '0',
						'type'            => 'switch',
						'update_callback' => $contact_page_callback,
					],
					'contact_form_privacy_label'       => [
						'label'           => esc_html__( 'Data Privacy Checkbox Label', 'Avada' ),
						'description'     => esc_html__( 'Enter the contents that should be displayed as label for the data privacy checkbox. Can contain HTML.', 'Avada' ),
						'id'              => 'contact_form_privacy_label',
						'default'         => esc_html__( 'By checking this box, you confirm that you have read and are agreeing to our terms of use regarding the storage of the data submitted through this form.', 'Avada' ),
						'type'            => 'textarea',
						'required'        => [
							[
								'setting'  => 'contact_form_privacy_checkbox',
								'operator' => '=',
								'value'    => '1',
							],
						],
						'output'          => [
							[
								'element'  => '#comment-privacy-checkbox-wrapper label',
								'function' => 'html',
							],
						],
						'update_callback' => $contact_page_callback,
					],
				],
			],
			'google_map_section'                => [
				'label'       => esc_html__( 'Google Map', 'Avada' ),
				'description' => '',
				'id'          => 'google_map_section',
				'default'     => esc_html__( 'Google Map', 'Avada' ),
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'google_map_disabled_note'       => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? [] : [
						'label'           => '',
						'description'     => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Google Maps Script is disabled in Advanced > Theme Features section. Please enable it to see the options.', 'Avada' ) . '</div>',
						'id'              => 'google_map_disabled_note',
						'type'            => 'custom',
						'required'        => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '0',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'google_map_important_note_info' => [
						'label'           => '',
						'description'     => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The options on this tab are for the google map that displays on the "Contact" page template. The only option that controls the Avada Builder google map element is the Google Maps API Key.', 'Avada' ) . '</div>',
						'id'              => 'google_map_important_note_info',
						'type'            => 'custom',
						'required'        => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'gmap_api'                       => [
						'label'             => esc_html__( 'Google Maps API Key', 'Avada' ),
						/* translators: "the Google docs" link. */
						'description'       => sprintf( esc_html__( 'Follow the steps in %s to get the API key. This key applies to both the contact page map and Avada Builder google map element.', 'Avada' ), '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key#key" target="_blank" rel="noopener noreferrer">' . esc_html__( 'the Google docs', 'Avada' ) . '</a>' ),
						'id'                => 'gmap_api',
						'default'           => '',
						'type'              => 'text',
						'required'          => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
						],
						'update_callback'   => $contact_page_callback,
						'validate_callback' => 'fusion_fusionredux_validate_google_api_key',
					],
					'gmap_api_type'                  => [
						'label'           => esc_html__( 'Google API Type', 'Avada' ),
						/* translators: "the Google Maps Users Guide" link. */
						'description'     => sprintf( __( 'Select the Google API type that should be used to load your map. The JavaScript API allows for more options and custom styling, but could be charged for by Google depending on map loads, while the embed API can be used for free regardless of map loads. For more information please see the <a href="%s" target="_blank">Google Maps Users Guide</a>.', 'Avada' ), 'https://cloud.google.com/maps-platform/user-guide/' ),
						'id'              => 'gmap_api_type',
						'default'         => 'js',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'js'    => esc_html__( 'JS API', 'Avada' ),
							'embed' => esc_html__( 'Embed API', 'Avada' ),
						],
						'edit_shortcut'   => [
							'selector'  => [ '#fusion-gmap-container' ],
							'shortcuts' => [
								[
									'aria_label'  => esc_html__( 'Edit Google Map', 'Avada' ),
									'icon'        => 'fusiona-pen',
									'open_parent' => true,
								],
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'gmap_embed_address'             => [
						'label'           => esc_html__( 'Address', 'Avada' ),
						'description'     => esc_html__( 'Add the address of the location you wish to display. Leave empty, if you don\'t want to display a map on the contact page. Address example: 775 New York Ave, Brooklyn, Kings, New York 11203. If the location is off, please try to use long/lat coordinates. ex: 12.381068,-1.492711.', 'Avada' ),
						'id'              => 'gmap_embed_address',
						'default'         => '',
						'type'            => 'text',
						'required'        => [
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'embed',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'gmap_embed_map_type'            => [
						'label'           => esc_html__( 'Map Type', 'Avada' ),
						'description'     => esc_html__( 'Select the type of google map to display.', 'Avada' ),
						'id'              => 'gmap_embed_map_type',
						'default'         => 'roadmap',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'roadmap'   => esc_html__( 'Roadmap', 'Avada' ),
							'satellite' => esc_html__( 'Satellite', 'Avada' ),
						],
						'required'        => [
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'embed',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'gmap_address'                   => [
						'label'           => esc_html__( 'Google Map Address', 'Avada' ),
						'description'     => esc_html__( 'Add the address to the location you wish to display. Leave empty, if you don\'t want to display a map on the contact page. Single address example: 775 New York Ave, Brooklyn, Kings, New York 11203. If the location is off, please try to use long/lat coordinates with latlng=. ex: latlng=12.381068,-1.492711. For multiple addresses, separate addresses by using the | symbol. ex: Address 1|Address 2|Address 3.', 'Avada' ),
						'id'              => 'gmap_address',
						'default'         => '775 New York Ave, Brooklyn, Kings, New York 11203',
						'type'            => 'textarea',
						'required'        => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'gmap_type'                      => [
						'label'           => esc_html__( 'Google Map Type', 'Avada' ),
						'description'     => esc_html__( 'Controls the type of google map that displays.', 'Avada' ),
						'id'              => 'gmap_type',
						'default'         => 'roadmap',
						'type'            => 'select',
						'choices'         => [
							'roadmap'   => esc_html__( 'Roadmap', 'Avada' ),
							'satellite' => esc_html__( 'Satellite', 'Avada' ),
							'hybrid'    => esc_html__( 'Hybrid', 'Avada' ),
							'terrain'   => esc_html__( 'Terrain', 'Avada' ),
						],
						'required'        => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'gmap_dimensions'                => [
						'label'           => esc_html__( 'Google Map Dimensions', 'Avada' ),
						'description'     => esc_html__( 'Controls the width and height of the google map. NOTE: height does not accept percentage value.', 'Avada' ),
						'id'              => 'gmap_dimensions',
						'units'           => false,
						'default'         => [
							'width'  => '100%',
							'height' => '415px',
						],
						'type'            => 'dimensions',
						'required'        => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'gmap_topmargin'                 => [
						'label'       => esc_html__( 'Google Map Top Margin', 'Avada' ),
						'description' => esc_html__( 'This is only applied to google maps that are not 100% width. It controls the distance to menu/page title.', 'Avada' ),
						'id'          => 'gmap_topmargin',
						'default'     => '55px',
						'type'        => 'dimension',
						'required'    => [
							[
								'setting'  => 'status_gmap',
								'operator' => '==',
								'value'    => '1',
							],
						],
						'css_vars'    => [
							[
								'name'     => '--gmap_topmargin',
								'element'  => '.avada-google-map',
								'callback' => [
									'conditional_return_value',
									[
										'value_pattern' => [ '$', '55px' ],
										'conditions'    => [
											[ 'gmap_dimensions[width]', '===', '100%' ],
										],
									],
								],
							],
						],
					],
					'map_zoom_level'                 => [
						'label'           => esc_html__( 'Map Zoom Level', 'Avada' ),
						'description'     => esc_html__( 'Choose the zoom level for the map. 0 corresponds to a map of the earth fully zoomed out, and larger zoom levels zoom in at a higher resolution.', 'Avada' ),
						'id'              => 'map_zoom_level',
						'default'         => 8,
						'type'            => 'slider',
						'choices'         => [
							'min'  => 0,
							'max'  => 22,
							'step' => 1,
						],
						'required'        => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'map_pin'                        => [
						'label'           => esc_html__( 'Address Pin', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the google map address pin.', 'Avada' ),
						'id'              => 'map_pin',
						'default'         => '1',
						'type'            => 'switch',
						'required'        => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'gmap_pin_animation'             => [
						'label'           => esc_html__( 'Address Pin Animation', 'Avada' ),
						'description'     => esc_html__( 'Turn on to enable address pin animation when the map first loads.', 'Avada' ),
						'id'              => 'gmap_pin_animation',
						'default'         => '1',
						'type'            => 'switch',
						'required'        => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'map_popup'                      => [
						'label'           => esc_html__( 'Map Popup On Click', 'Avada' ),
						'description'     => esc_html__( 'Turn on to require a click to display the popup graphic with address info for the pin on the map.', 'Avada' ),
						'id'              => 'map_popup',
						'default'         => '0',
						'type'            => 'switch',
						'required'        => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'map_scrollwheel'                => [
						'label'           => esc_html__( 'Map Zoom With Scrollwheel', 'Avada' ),
						'description'     => esc_html__( 'Turn on to enable zooming using the mouse scroll wheel. Use Cmd/Ctrl key + scroll to zoom. If set to no, zooming through two-finger movements (cooperative gesture handling) will be enabled.', 'Avada' ),
						'id'              => 'map_scrollwheel',
						'default'         => '1',
						'type'            => 'switch',
						'required'        => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'map_scale'                      => [
						'label'           => esc_html__( 'Map Scale', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the google map scale.', 'Avada' ),
						'id'              => 'map_scale',
						'default'         => '1',
						'type'            => 'switch',
						'required'        => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'map_zoomcontrol'                => [
						'label'           => esc_html__( 'Map Zoom & Pan Control Icons', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the google map zoom control and pan control icons.', 'Avada' ),
						'id'              => 'map_zoomcontrol',
						'default'         => '1',
						'type'            => 'switch',
						'required'        => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
				],
			],
			'google_map_design_styling_section' => [
				'label'       => esc_html__( 'Google Map Styling', 'Avada' ),
				'description' => '',
				'id'          => 'google_map_design_styling_section',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'google_map_disabled_note_1' => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? [] : [
						'label'           => '',
						'description'     => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Google Maps Script is disabled in Advanced > Theme Features section. Please enable it to see the options.', 'Avada' ) . '</div>',
						'id'              => 'google_map_disabled_note_1',
						'type'            => 'custom',
						'required'        => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '0',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'google_map_styling_important_note_info' => [
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The options on this tab are only for the google map that displays on the "Contact" page template, they do not control the google map element.  These options are only available for the JS API type.', 'Avada' ) . '</div>',
						'id'          => 'google_map_styling_important_note_info',
						'type'        => 'custom',
						'required'    => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
						],
					],
					'map_styling'                => [
						'label'           => esc_html__( 'Select the Map Styling', 'Avada' ),
						'description'     => esc_html__( 'Controls the google map styles. Default is google style, Theme is our style, or choose Custom to select your own style options below.', 'Avada' ),
						'id'              => 'map_styling',
						'default'         => 'default',
						'type'            => 'select',
						'choices'         => [
							'default' => esc_html__( 'Default Styling', 'Avada' ),
							'theme'   => esc_html__( 'Theme Styling', 'Avada' ),
							'custom'  => esc_html__( 'Custom Styling', 'Avada' ),
						],
						'required'        => [
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'map_overlay_color'          => [
						'label'           => esc_html__( 'Map Overlay Color', 'Avada' ),
						'description'     => esc_html__( 'Custom styling setting only. Pick any overlaying color for the map besides pure black or white. Works best with "roadmap" type.', 'Avada' ),
						'id'              => 'map_overlay_color',
						'default'         => 'var(--awb-color4)',
						'type'            => 'color-alpha',
						'required'        => [
							[
								'setting'  => 'map_styling',
								'operator' => '==',
								'value'    => 'custom',
							],
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'map_infobox_styling'        => [
						'label'           => esc_html__( 'Info Box Styling', 'Avada' ),
						'description'     => esc_html__( 'Custom styling setting only. Controls the styling of the info box.', 'Avada' ),
						'id'              => 'map_infobox_styling',
						'default'         => 'default',
						'type'            => 'select',
						'choices'         => [
							'default' => esc_html__( 'Default Infobox', 'Avada' ),
							'custom'  => esc_html__( 'Custom Infobox', 'Avada' ),
						],
						'required'        => [
							[
								'setting'  => 'map_styling',
								'operator' => '==',
								'value'    => 'custom',
							],
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'map_infobox_content'        => [
						'label'           => esc_html__( 'Info Box Content', 'Avada' ),
						'description'     => esc_html__( 'Custom styling setting only. Type in custom info box content to replace the default address string. For multiple addresses, separate info box contents by using the | symbol. ex: InfoBox 1|InfoBox 2|InfoBox 3', 'Avada' ),
						'id'              => 'map_infobox_content',
						'default'         => '',
						'type'            => 'textarea',
						'required'        => [
							[
								'setting'  => 'map_styling',
								'operator' => '==',
								'value'    => 'custom',
							],
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'map_infobox_bg_color'       => [
						'label'           => esc_html__( 'Info Box Background Color', 'Avada' ),
						'description'     => esc_html__( 'Custom styling setting only. Controls the info box background color.', 'Avada' ),
						'id'              => 'map_infobox_bg_color',
						'default'         => 'var(--awb-color5)',
						'type'            => 'color-alpha',
						'required'        => [
							[
								'setting'  => 'map_infobox_styling',
								'operator' => '==',
								'value'    => 'custom',
							],
							[
								'setting'  => 'map_styling',
								'operator' => '==',
								'value'    => 'custom',
							],
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'map_infobox_text_color'     => [
						'label'           => esc_html__( 'Info Box Text Color', 'Avada' ),
						'description'     => esc_html__( 'Custom styling setting only. Controls the info box text color.', 'Avada' ),
						'id'              => 'map_infobox_text_color',
						'default'         => 'var(--awb-color1)',
						'type'            => 'color-alpha',
						'required'        => [
							[
								'setting'  => 'map_infobox_styling',
								'operator' => '==',
								'value'    => 'custom',
							],
							[
								'setting'  => 'map_styling',
								'operator' => '==',
								'value'    => 'custom',
							],
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
					'map_custom_marker_icon'     => [
						'label'           => esc_html__( 'Custom Marker Icon', 'Avada' ),
						'description'     => esc_html__( 'Custom styling setting only. Use full image urls for custom marker icons or input "theme" for our custom marker. For multiple addresses, separate icons by using the | symbol or use one for all. ex: Icon 1|Icon 2|Icon 3', 'Avada' ),
						'id'              => 'map_custom_marker_icon',
						'default'         => '',
						'type'            => 'textarea',
						'required'        => [
							[
								'setting'  => 'map_infobox_styling',
								'operator' => '==',
								'value'    => 'custom',
							],
							[
								'setting'  => 'map_styling',
								'operator' => '==',
								'value'    => 'custom',
							],
							[
								'setting'  => 'status_gmap',
								'operator' => '=',
								'value'    => '1',
							],
							[
								'setting'  => 'gmap_api_type',
								'operator' => '=',
								'value'    => 'js',
							],
						],
						'update_callback' => $contact_page_callback,
					],
				],
			],
		],
	];

	return $sections;

}

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
 * Logo
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_logo( $sections ) {

	$logo_margin_elements = [
		'.fusion-header .fusion-logo',
		'#side-header .fusion-logo',
	];

	$sections['logo'] = [
		'label'    => esc_html__( 'Logo', 'Avada' ),
		'id'       => 'heading_logo',
		'is_panel' => true,
		'logo'     => 5,
		'icon'     => 'el-icon-plus-sign',
		'alt_icon' => 'fusiona-plus-circle',
		'fields'   => [
			'logo_options_wrapper' => [
				'label'       => esc_html__( 'Logo', 'Avada' ),
				'description' => '',
				'id'          => 'logo_options_wrapper',
				'icon'        => true,
				'position'    => 'start',
				'type'        => 'sub-section',
				'fields'      => [
					'logo_alignment'            => [
						'label'       => esc_html__( 'Logo Alignment', 'Avada' ),
						'description' => esc_html__( 'Controls the logo alignment. "Center" only works on Header 5 and Side Headers.', 'Avada' ),
						'id'          => 'logo_alignment',
						'default'     => 'left',
						'type'        => 'radio-buttonset',
						'choices'     => [
							'left'   => esc_html__( 'Left', 'Avada' ),
							'center' => esc_html__( 'Center', 'Avada' ),
							'right'  => esc_html__( 'Right', 'Avada' ),
						],
						'required'    => [
							[
								'setting'  => 'logo',
								'operator' => '!=',
								'value'    => '',
							],
							[
								'setting'  => 'logo',
								'operator' => '!=',
								'value'    => [
									'url' => '',
								],
							],
							[
								'setting'  => 'logo',
								'operator' => '!=',
								'value'    => [
									'url'       => '',
									'id'        => '',
									'height'    => '',
									'width'     => '',
									'thumbnail' => '',
								],
							],
						],
						'css_vars'    => [
							[
								'name' => '--logo_alignment',
							],
						],
						'output'      => [
							[
								'element'       => '.fusion-logo-alignment',
								'function'      => 'attr',
								'attr'          => 'class',
								'value_pattern' => 'fusion-logo-$',
								'remove_attrs'  => [ 'fusion-logo-left', 'fusion-logo-center', 'fusion-logo-right' ],
							],
							[
								'element'           => 'helperElement',
								'property'          => 'bottom',
								'choice'            => 'top',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'choice'    => 'top',
										'globalVar' => 'avadaMenuVars',
										'id'        => 'logo_alignment',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
							[
								'element'       => 'body',
								'function'      => 'attr',
								'attr'          => 'class',
								'value_pattern' => 'mobile-logo-pos-$',
								'remove_attrs'  => [ 'mobile-logo-pos-left', 'mobile-logo-pos-center', 'mobile-logo-pos-right' ],
							],
							[
								'element'       => '.side-header-wrapper .side-header-content',
								'function'      => 'attr',
								'attr'          => 'class',
								'value_pattern' => 'fusion-logo-$',
								'remove_attrs'  => [ 'fusion-logo-left', 'fusion-logo-center', 'fusion-logo-right' ],
							],
							[
								'element'       => '.side-header-wrapper .fusion-main-menu-container',
								'function'      => 'attr',
								'attr'          => 'class',
								'value_pattern' => 'fusion-logo-menu-$',
								'remove_attrs'  => [ 'fusion-logo-menu-left', 'fusion-logo-menu-center', 'fusion-logo-menu-right' ],
							],
						],
					],
					'logo_margin'               => [
						'label'       => esc_html__( 'Logo Margins', 'Avada' ),
						'description' => esc_html__( 'Controls the top/right/bottom/left margins for the logo.', 'Avada' ),
						'id'          => 'logo_margin',
						'default'     => [
							'top'    => '34px',
							'bottom' => '34px',
							'left'   => '0px',
							'right'  => '0px',
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
								'setting'  => 'logo',
								'operator' => '!=',
								'value'    => '',
							],
							[
								'setting'  => 'logo',
								'operator' => '!=',
								'value'    => [
									'url' => '',
								],
							],
							[
								'setting'  => 'logo',
								'operator' => '!=',
								'value'    => [
									'url'       => '',
									'id'        => '',
									'height'    => '',
									'width'     => '',
									'thumbnail' => '',
								],
							],
						],
						'css_vars'    => [
							[
								'name'     => '--logo_margin-top',
								'element'  => '.fusion-logo',
								'choice'   => 'top',
								'callback' => [
									'conditional_return_value',
									[
										'value_pattern' => [ '$', '0px' ],
										'conditions'    => [
											[ 'logo[url]', '!==', '' ],
										],
									],
								],
							],
							[
								'name'     => '--logo_margin-bottom',
								'element'  => '.fusion-logo',
								'choice'   => 'bottom',
								'callback' => [
									'conditional_return_value',
									[
										'value_pattern' => [ '$', '0px' ],
										'conditions'    => [
											[ 'logo[url]', '!==', '' ],
										],
									],
								],
							],
							[
								'name'     => '--logo_margin-left',
								'element'  => '.fusion-logo',
								'choice'   => 'left',
								'callback' => [
									'conditional_return_value',
									[
										'value_pattern' => [ '$', '0px' ],
										'conditions'    => [
											[ 'logo[url]', '!==', '' ],
										],
									],
								],
							],
							[
								'name'     => '--logo_margin-right',
								'element'  => '.fusion-logo',
								'choice'   => 'right',
								'callback' => [
									'conditional_return_value',
									[
										'value_pattern' => [ '$', '0px' ],
										'conditions'    => [
											[ 'logo[url]', '!==', '' ],
										],
									],
								],
							],
						],
						'output'      => [
							[
								'element'  => $logo_margin_elements,
								'choice'   => 'top',
								'function' => 'attr',
								'attr'     => 'data-margin-top',
							],
							[
								'choice'            => 'top',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'avadaHeaderVars',
										'id'        => 'header_padding_top',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
							[
								'element'  => $logo_margin_elements,
								'choice'   => 'bottom',
								'function' => 'attr',
								'attr'     => 'data-margin-bottom',
							],
							[
								'choice'            => 'bottom',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'avadaHeaderVars',
										'id'        => 'logo_margin_bottom',
										'trigger'   => [ 'fusion-reinit-sticky-header' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
							[
								'element'  => $logo_margin_elements,
								'choice'   => 'left',
								'function' => 'attr',
								'attr'     => 'data-margin-left',
							],
							[
								'element'  => $logo_margin_elements,
								'choice'   => 'right',
								'function' => 'attr',
								'attr'     => 'data-margin-right',
							],
						],
					],
					'logo_background'           => [
						'label'           => esc_html__( 'Logo Background', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display a colored background for the logo.', 'Avada' ),
						'id'              => 'logo_background',
						'default'         => '0',
						'type'            => 'switch',
						'class'           => 'fusion-gutter-and-and-or-and',
						'required'        => [
							[
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v4',
							],
							[
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v5',
							],
							[
								'setting'  => 'logo',
								'operator' => '!=',
								'value'    => '',
							],
							[
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'top',
							],
							[
								'setting'  => 'logo',
								'operator' => '!=',
								'value'    => '',
							],
						],
						'partial_refresh' => [
							'partial_refresh_logo' => [
								'selector'            => '.fusion-logo,.fusion-logo-background',
								'container_inclusive' => true,
								'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'logo' ],
							],
						],
						'output'          => [
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ '', 'true' ],
										'element'   => 'body,html',
										'className' => 'avada-has-logo-background',
									],

								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'logo_background_color'     => [
						'label'       => esc_html__( 'Logo Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color for the logo.', 'Avada' ),
						'id'          => 'logo_background_color',
						'default'     => 'var(--awb-color4)',
						'type'        => 'color-alpha',
						'class'       => 'fusion-gutter-and-and-and-or-and-and',
						'required'    => [
							[
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v4',
							],
							[
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v5',
							],
							[
								'setting'  => 'logo_background',
								'operator' => '==',
								'value'    => '1',
							],
							[
								'setting'  => 'logo',
								'operator' => '!=',
								'value'    => '',
							],
							[
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'top',
							],
							[
								'setting'  => 'logo_background',
								'operator' => '==',
								'value'    => '1',
							],
							[
								'setting'  => 'logo',
								'operator' => '!=',
								'value'    => '',
							],
						],
						'css_vars'    => [
							[
								'name'     => '--logo_background_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'logo_custom_link'          => [
						'label'       => esc_html__( 'Logo Custom Link URL', 'Avada' ),
						'description' => esc_html__( 'Enter a custom URL the site logo should link to. Leave empty to let logo link to the home page.', 'Avada' ),
						'id'          => 'logo_custom_link',
						'default'     => '',
						'type'        => is_admin() ? 'text' : 'link_selector',
						'output'      => [
							// Change attr in the DOM.
							[
								'element'       => '.fusion-logo-link',
								'function'      => 'attr',
								'attr'          => 'href',
								'value_pattern' => '$',
							],
						],
					],
					'default_logo_info_title'   => [
						'label'       => esc_html__( 'Default Logo', 'Avada' ),
						'description' => '',
						'id'          => 'default_logo_info_title',
						'icon'        => true,
						'type'        => 'info',
					],
					'logo'                      => [
						'label'           => esc_html__( 'Default Logo', 'Avada' ),
						'description'     => esc_html__( 'Select an image file for your logo.', 'Avada' ),
						'id'              => 'logo',
						'default'         => Avada::$template_dir_url . '/assets/images/logo.png',
						'mod'             => 'min',
						'type'            => 'media',
						'mode'            => false,
						'partial_refresh' => [
							'partial_refresh_logo' => [
								'selector'            => '.fusion-logo',
								'container_inclusive' => true,
								'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'logo' ],
							],
						],
						'edit_shortcut'   => [
							'selector'  => [ '.fusion-header', '#side-header .side-header-wrapper' ],
							'shortcuts' => [
								[
									'aria_label' => esc_html__( 'Edit Logo', 'Avada' ),
									'icon'       => 'fusiona-plus-circle',
									'order'      => 2,
								],
							],
						],
					],
					'logo_retina'               => [
						'label'           => esc_html__( 'Retina Default Logo', 'Avada' ),
						'description'     => esc_html__( 'Select an image file for the retina version of the logo. It should be exactly 2x the size of the main logo.', 'Avada' ),
						'id'              => 'logo_retina',
						'default'         => Avada::$template_dir_url . '/assets/images/logo@2x.png',
						'mod'             => 'min',
						'type'            => 'media',
						'mode'            => false,
						'required'        => [
							[
								'setting'  => 'logo',
								'operator' => '!=',
								'value'    => '',
							],
							[
								'setting'  => 'logo',
								'operator' => '!=',
								'value'    => [
									'url' => '',
								],
							],
							[
								'setting'  => 'logo',
								'operator' => '!=',
								'value'    => [
									'url'       => '',
									'id'        => '',
									'height'    => '',
									'width'     => '',
									'thumbnail' => '',
								],
							],
						],
						'partial_refresh' => [
							'partial_refresh_logo_retina' => [
								'selector'            => '.fusion-logo',
								'container_inclusive' => true,
								'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'logo' ],
							],
						],
					],
					'sticky_logo_info_title'    => [
						'label'       => esc_html__( 'Sticky Header Logo', 'Avada' ),
						'description' => esc_html__( 'Select an image file for your sticky header logo.', 'Avada' ),
						'description' => '',
						'id'          => 'sticky_logo_info_title',
						'icon'        => true,
						'type'        => 'info',
					],
					'sticky_header_logo'        => [
						'label'           => esc_html__( 'Sticky Header Logo', 'Avada' ),
						'description'     => esc_html__( 'Select an image file for your sticky header logo.', 'Avada' ),
						'id'              => 'sticky_header_logo',
						'default'         => '',
						'mod'             => 'min',
						'type'            => 'media',
						'mode'            => false,
						'partial_refresh' => [
							'partial_refresh_sticky_header_logo' => [
								'selector'            => '.fusion-logo',
								'container_inclusive' => true,
								'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'logo' ],
							],
						],
						'output'          => [
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ '', 'has-image' ],
										'element'   => '.fusion-logo-alignment',
										'className' => 'fusion-sticky-logo-1',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'sticky_header_logo_retina' => [
						'label'           => esc_html__( 'Retina Sticky Header Logo', 'Avada' ),
						'description'     => esc_html__( 'Select an image file for the retina version of the sticky header logo. It should be exactly 2x the size of the sticky header logo.', 'Avada' ),
						'id'              => 'sticky_header_logo_retina',
						'default'         => '',
						'mod'             => 'min',
						'type'            => 'media',
						'mode'            => false,
						'required'        => [
							[
								'setting'  => 'sticky_header_logo',
								'operator' => '!=',
								'value'    => '',
							],
							[
								'setting'  => 'sticky_header_logo',
								'operator' => '!=',
								'value'    => [
									'url' => '',
								],
							],
							[
								'setting'  => 'sticky_header_logo',
								'operator' => '!=',
								'value'    => [
									'url'       => '',
									'id'        => '',
									'height'    => '',
									'width'     => '',
									'thumbnail' => '',
								],
							],
						],
						'partial_refresh' => [
							'partial_refresh_sticky_header_logo_retina' => [
								'selector'            => '.fusion-logo',
								'container_inclusive' => true,
								'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'logo' ],
							],
						],
					],
					'mobile_logo_info_title'    => [
						'label'       => esc_html__( 'Mobile Logo', 'Avada' ),
						'description' => '',
						'id'          => 'mobile_logo_info_title',
						'icon'        => true,
						'type'        => 'info',
					],
					'mobile_logo'               => [
						'label'           => esc_html__( 'Mobile Logo', 'Avada' ),
						'description'     => esc_html__( 'Select an image file for your mobile logo.', 'Avada' ),
						'id'              => 'mobile_logo',
						'default'         => '',
						'mod'             => 'min',
						'type'            => 'media',
						'mode'            => false,
						'partial_refresh' => [
							'partial_refresh_mobile_logo' => [
								'selector'            => '.fusion-logo',
								'container_inclusive' => true,
								'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'logo' ],
							],
						],
					],
					'mobile_logo_retina'        => [
						'label'           => esc_html__( 'Retina Mobile Logo', 'Avada' ),
						'description'     => esc_html__( 'Select an image file for the retina version of the mobile logo. It should be exactly 2x the size of the mobile logo.', 'Avada' ),
						'id'              => 'mobile_logo_retina',
						'default'         => '',
						'mod'             => 'min',
						'type'            => 'media',
						'mode'            => false,
						'required'        => [
							[
								'setting'  => 'mobile_logo',
								'operator' => '!=',
								'value'    => '',
							],
							[
								'setting'  => 'mobile_logo',
								'operator' => '!=',
								'value'    => [
									'url' => '',
								],
							],
							[
								'setting'  => 'mobile_logo',
								'operator' => '!=',
								'value'    => [
									'url'       => '',
									'id'        => '',
									'height'    => '',
									'width'     => '',
									'thumbnail' => '',
								],
							],
						],
						'partial_refresh' => [
							'partial_refresh_mobile_logo_retina' => [
								'selector'            => '.fusion-logo',
								'container_inclusive' => true,
								'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'logo' ],
							],
						],
					],
				],
			],
			'favicons'             => [
				'label'       => esc_html__( 'Favicon', 'Avada' ),
				'description' => '',
				'id'          => 'favicons',
				'icon'        => true,
				'position'    => 'start',
				'type'        => 'sub-section',
				'fields'      => [
					'fav_icon'             => [
						'label'       => esc_html__( 'Favicon', 'Avada' ),
						'description' => esc_html__( 'Favicon for your website at 32px x 32px or 64px x 64px.', 'Avada' ),
						'id'          => 'fav_icon',
						'default'     => '',
						'type'        => 'media',
						'mode'        => false,
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'fav_icon_apple_touch' => [
						'label'       => esc_html__( 'Apple Touch Icon', 'Avada' ),
						'description' => esc_html__( 'Favicon for Apple iOS devices at 180px x 180px.', 'Avada' ),
						'id'          => 'fav_icon_apple_touch',
						'default'     => '',
						'type'        => 'media',
						'mode'        => false,
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'fav_icon_android'     => [
						'label'       => esc_html__( 'Android Devices Icon', 'Avada' ),
						'description' => esc_html__( 'Favicon for Android-based devices at 192px x 192px.', 'Avada' ),
						'id'          => 'fav_icon_android',
						'default'     => '',
						'type'        => 'media',
						'mode'        => false,
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'fav_icon_edge'        => [
						'label'       => esc_html__( 'Microsoft Edge Icon', 'Avada' ),
						'description' => esc_html__( 'Favicon for Microsoft Edge browsers at 270px x 270px.', 'Avada' ),
						'id'          => 'fav_icon_edge',
						'default'     => '',
						'type'        => 'media',
						'mode'        => false,
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
				],
			],
		],
	];

	return $sections;

}

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
 * Header
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_header( $sections ) {

	// Check if we have a global header override.
	$has_global_header = false;
	if ( class_exists( 'Fusion_Template_Builder' ) ) {
		$default_layout    = Fusion_Template_Builder::get_default_layout();
		$has_global_header = (bool) isset( $default_layout['data']['template_terms'] ) && isset( $default_layout['data']['template_terms']['header'] ) && $default_layout['data']['template_terms']['header'];

		if ( $has_global_header ) {
			$global_header_edit_url = admin_url( 'post.php?post=' . $default_layout['data']['template_terms']['header'] . '&action=edit' );
			if ( ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() ) ) {
				$global_header_edit_url = add_query_arg( 'fb-edit', '1', get_permalink( $default_layout['data']['template_terms']['header'] ) );
			}
		}
	}

	$sections['header'] = [
		'label'    => esc_html__( 'Header', 'Avada' ),
		'id'       => 'heading_header',
		'is_panel' => true,
		'priority' => 3,
		'icon'     => 'el-icon-arrow-up',
		'alt_icon' => 'fusiona-header',
		'fields'   => [],
	];

	if ( $has_global_header ) {
		$sections['header']['fields'] = [
			'slider_position' => [
				'label'       => esc_html__( 'Slider Position', 'Avada' ),
				'description' => esc_html__( 'Controls if the slider displays below or above the header.', 'Avada' ),
				'id'          => 'slider_position',
				'default'     => 'below',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'below' => esc_html__( 'Below', 'Avada' ),
					'above' => esc_html__( 'Above', 'Avada' ),
				],
				'transport'   => 'postMessage',
				'output'      => [
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'callback'          => [
							'change_slider_position',
							[
								'element' => '#sliders-container',
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'header_global_header_override_template_notice' => [
				'id'            => 'header_global_header_override_template_notice',
				'label'         => '',
				'description'   => sprintf(
					/* translators: %1$s: Content|Footer|Page Title Bar. %2$s: Layout selection URL. %3$s: URL for global header layout edit. */
					'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The options on this tab are not available because a global %1$s override is currently used. To edit your global layout please visit <a href="%2$s" target="_blank">this page</a>, or <a href="%3$s" target="_blank">edit the header</a>.', 'Avada' ) . '</div>',
					Fusion_Template_Builder::get_instance()->get_template_terms()['header']['label'],
					admin_url( 'admin.php?page=avada-layouts' ),
					$global_header_edit_url
				),
				'type'          => 'custom',
				'edit_shortcut' => [
					'selector'  => [ '.fusion-tb-header' ],
					'shortcuts' => [
						[
							'aria_label'  => esc_html__( 'Edit Header', 'Avada' ),
							'icon'        => 'fusiona-header',
							'open_parent' => true,
							'link_to_template_if_override_active' => 'header',
						],
					],
				],
			],
		];
	}

	$fields = [
		'header_info_1'  => [
			'label'       => esc_html__( 'Header Content', 'Avada' ),
			'description' => '',
			'id'          => 'header_info_1',
			'default'     => '',
			'icon'        => true,
			'type'        => 'sub-section',
			'hidden'      => $has_global_header,
			'fields'      => [
				'header_global_header_template_content_notice' => [
					'id'          => 'header_global_header_template_content_notice',
					'label'       => '',
					'description' => class_exists( 'Fusion_Template_Builder' ) ? sprintf(
						/* translators: %1$s: Content|Footer|Page Title Bar. %2$s: Layout selection URL. */
						'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> For more flexibility and a more modern, performant setup, we recommend using the %1$s Builder. To create a custom %1$s Layout, <a href="%2$s" target="_blank">visit this page</a>.', 'Avada' ) . '</div>',
						Fusion_Template_Builder::get_instance()->get_template_terms()['header']['label'],
						admin_url( 'admin.php?page=avada-layouts' )
					) : '',
					'type'        => 'custom',
				],
				'header_position'      => [
					'label'           => esc_html__( 'Header Position', 'Avada' ),
					'description'     => esc_html__( 'Controls the position of the header to be in the top, left or right of the site. The main menu height, header padding and logo margin options will auto adjust based off your selection for ideal aesthetics.', 'Avada' ),
					'id'              => 'header_position',
					'default'         => 'top',
					'type'            => 'radio-buttonset',
					'choices'         => [
						'top'   => esc_html__( 'Top', 'Avada' ),
						'left'  => esc_html__( 'Left', 'Avada' ),
						'right' => esc_html__( 'Right', 'Avada' ),
					],
					// Partial refresh for the header.
					'partial_refresh' => [
						'header_position_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper, .fusion-header-wrapper, #side-header-sticky, #side-header, #sliders-container',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_position_replace_after_hook' => [
							'selector'              => '.avada-hook-after-header-wrapper',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header_position' ],
							'success_trigger_event' => 'header-rendered fusion-partial-wooslider',
						],
					],
					'output'          => [
						// This is for the avadaMenuVars.header_position var.
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'globalVar' => 'avadaMenuVars',
									'id'        => 'header_position',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
						// This is for the avadaFusionSliderVars.header_position var.
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'globalVar' => 'avadaFusionSliderVars',
									'id'        => 'header_position',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
						// Update necessary CSS classes.
						[
							'js_callback' => [
								'change_header_position',
							],
						],
					],
				],
				'header_layout'        => [
					'label'           => esc_html__( 'Select a Header Layout', 'Avada' ),
					'description'     => esc_html__( 'Controls the general layout of the header. Headers 2-5 allow additional content areas via the header content options 1-3. Header 6 only allows parent level menu items, no child levels will display. The main menu height, header padding and logo margin options will auto adjust based off your selection for ideal aesthetics.', 'Avada' ),
					'id'              => 'header_layout',
					'default'         => 'v3',
					'type'            => 'radio-image',
					'choices'         => [
						'v1' => Avada::$template_dir_url . '/assets/images/patterns/header1.jpg',
						'v2' => Avada::$template_dir_url . '/assets/images/patterns/header2.jpg',
						'v3' => Avada::$template_dir_url . '/assets/images/patterns/header3.jpg',
						'v4' => Avada::$template_dir_url . '/assets/images/patterns/header4.jpg',
						'v5' => Avada::$template_dir_url . '/assets/images/patterns/header5.jpg',
						'v6' => Avada::$template_dir_url . '/assets/images/patterns/header6.jpg',
						'v7' => Avada::$template_dir_url . '/assets/images/patterns/header7.jpg',
					],
					'required'        => [
						[
							'setting'  => 'header_position',
							'operator' => '==',
							'value'    => 'top',
						],
					],
					// Partial refresh for the header.
					'partial_refresh' => [
						'header_content_header_layout_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_content_header_layout_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_content_header_layout' => [
							'selector'              => '.fusion-header-wrapper',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'header-rendered',
						],
					],
					'edit_shortcut'   => [
						'selector'  => [ '.fusion-header', '#side-header .side-header-wrapper' ],
						'shortcuts' => [
							[
								'aria_label'  => esc_html__( 'Edit Header Layout', 'Avada' ),
								'icon'        => 'fusiona-header',
								'open_parent' => true,
								'order'       => 1,
							],
							[
								'aria_label' => esc_html__( 'Add Slider', 'Avada' ),
								'icon'       => 'fusiona-uniF61C',
								'link'       => '#',
								'css_class'  => 'add-slider',
								'order'      => 4,
							],
							[
								'aria_label' => esc_html__( 'Add Page Title Bar', 'Avada' ),
								'icon'       => 'fusiona-page_title',
								'link'       => '#',
								'css_class'  => 'add-ptb',
								'order'      => 5,
							],
						],
					],
					'output'          => [
						// Change classes in <body>.
						[
							'element'       => 'body',
							'function'      => 'attr',
							'attr'          => 'class',
							'value_pattern' => 'fusion-header-layout-$',
							'remove_attrs'  => [ 'fusion-header-layout-v1', 'fusion-header-layout-v2', 'fusion-header-layout-v3', 'fusion-header-layout-v4', 'fusion-header-layout-v5', 'fusion-header-layout-v6', 'fusion-header-layout-v7' ],
						],

						// Change the avadaSidebarsVars.header_layout var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'choice'    => 'top',
									'globalVar' => 'avadaSidebarsVars',
									'id'        => 'header_layout',
									'trigger'   => [ 'fusionReSettStickySidebarStatus' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				( $has_global_header ? '' : 'slider_position' ) => ( $has_global_header ? [] : [
					'label'       => esc_html__( 'Slider Position', 'Avada' ),
					'description' => esc_html__( 'Controls if the slider displays below or above the header.', 'Avada' ),
					'id'          => 'slider_position',
					'default'     => 'below',
					'type'        => 'radio-buttonset',
					'choices'     => [
						'below' => esc_html__( 'Below', 'Avada' ),
						'above' => esc_html__( 'Above', 'Avada' ),
					],
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '==',
							'value'    => 'top',
						],
					],
					'transport'   => 'postMessage',
					'output'      => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'change_slider_position',
								[
									'element' => '#sliders-container',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				] ),
				'header_left_content'  => [
					'label'           => esc_html__( 'Header Content 1', 'Avada' ),
					'description'     => esc_html__( 'Controls the content that displays in the top left section.', 'Avada' ),
					'id'              => 'header_left_content',
					'default'         => 'social_links',
					'type'            => 'select',
					'choices'         => [
						'contact_info' => esc_html__( 'Contact Info', 'Avada' ),
						'social_links' => esc_html__( 'Social Links', 'Avada' ),
						'navigation'   => esc_html__( 'Navigation', 'Avada' ),
						'leave_empty'  => esc_html__( 'Leave Empty', 'Avada' ),
					],
					'class'           => 'fusion-or-gutter',
					'required'        => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v2',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v3',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v4',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v5',
						],
					],
					// Partial refresh for the header.
					'partial_refresh' => [
						'header_content_header_left_content_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_content_header_left_content_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_content_header_left_content' => [
							'selector'              => '.fusion-header-wrapper, #side-header-sticky, #side-header',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'header-rendered resize',
						],
					],
				],
				'header_right_content' => [
					'label'           => esc_html__( 'Header Content 2', 'Avada' ),
					'description'     => esc_html__( 'Controls the content that displays in the top right section.', 'Avada' ),
					'id'              => 'header_right_content',
					'default'         => 'navigation',
					'type'            => 'select',
					'choices'         => [
						'contact_info' => esc_html__( 'Contact Info', 'Avada' ),
						'social_links' => esc_html__( 'Social Links', 'Avada' ),
						'navigation'   => esc_html__( 'Navigation', 'Avada' ),
						'leave_empty'  => esc_html__( 'Leave Empty', 'Avada' ),
					],
					'class'           => 'fusion-or-gutter',
					'required'        => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v2',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v3',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v4',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v5',
						],
					],
					// Partial refresh for the header.
					'partial_refresh' => [
						'header_content_header_right_content_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_content_header_right_content_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_content_header_right_content' => [
							'selector'              => '.fusion-header-wrapper, #side-header-sticky, #side-header',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'header-rendered resize',
						],
					],
				],
				'header_v4_content'    => [
					'label'           => esc_html__( 'Header Content 3', 'Avada' ),
					'description'     => esc_html__( 'Controls the content that displays in the middle right section.', 'Avada' ),
					'id'              => 'header_v4_content',
					'default'         => 'tagline_and_search',
					'type'            => 'select',
					'choices'         => [
						'tagline'            => esc_html__( 'Tagline', 'Avada' ),
						'search'             => esc_html__( 'Search', 'Avada' ),
						'tagline_and_search' => esc_html__( 'Tagline And Search', 'Avada' ),
						'banner'             => esc_html__( 'Banner', 'Avada' ),
						'none'               => esc_html__( 'Leave Empty', 'Avada' ),
					],
					'class'           => 'fusion-or-gutter',
					'required'        => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '==',
							'value'    => 'v4',
						],
					],
					// Partial refresh for the header.
					'partial_refresh' => [
						'header_content_header_v4_content_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_content_header_v4_content_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_content_header_v4_content' => [
							'selector'              => '.fusion-header-wrapper, #side-header-sticky, #side-header',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'header-rendered resize',
						],
					],
				],
				'header_number'        => [
					'label'       => esc_html__( 'Phone Number For Contact Info', 'Avada' ),
					'description' => esc_html__( 'This content will display if you have "Contact Info" selected for the Header Content 1 or 2 option above.', 'Avada' ),
					'id'          => 'header_number',
					'default'     => 'Call Us Today! 1.555.555.555',
					'type'        => 'text',
					'class'       => 'fusion-or-gutter',
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v2',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v3',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v4',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v5',
						],
					],
					'output'      => [
						[
							'element'  => '.fusion-contact-info-phone-number',
							'function' => 'html',
						],
					],
				],
				'header_email'         => [
					'label'       => esc_html__( 'Email Address For Contact Info', 'Avada' ),
					'description' => esc_html__( 'This content will display if you have "Contact Info" selected for the Header Content 1 or 2 option above.', 'Avada' ),
					'id'          => 'header_email',
					'default'     => 'info@yourdomain.com',
					'type'        => 'text',
					'class'       => 'fusion-or-gutter',
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v2',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v3',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v4',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v5',
						],
					],
					'output'      => [
						[
							'element'       => '.fusion-contact-info-email-address',
							'function'      => 'html',
							'value_pattern' => '<a href="mailto:$">$</a>',
						],
					],
				],
				'header_tagline'       => [
					'label'       => esc_html__( 'Tagline For Content 3', 'Avada' ),
					'description' => esc_html__( 'This content will display if you have "Tagline" selected for the Header Content 3 option above.', 'Avada' ),
					'id'          => 'header_tagline',
					'default'     => 'Insert Tagline Here',
					'type'        => 'textarea',
					'class'       => 'fusion-gutter-and-or-and',
					'required'    => [
						[
							'setting'  => 'header_v4_content',
							'operator' => 'contains',
							'value'    => 'tagline',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v4',
						],
						[
							'setting'  => 'header_v4_content',
							'operator' => 'contains',
							'value'    => 'tagline',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
					],
					'output'      => [
						[
							'element'  => '.fusion-header-tagline',
							'function' => 'html',
						],
					],
				],
				'header_banner_code'   => [
					'label'           => esc_html__( 'Banner Code For Content 3', 'Avada' ),
					'description'     => esc_html__( 'This content will display if you have "Banner" selected for the Header Content 3 option above. Add HTML banner code for Header Content 3. Elements, like buttons, can be used here also.', 'Avada' ),
					'id'              => 'header_banner_code',
					'default'         => '',
					'type'            => 'code',
					'choices'         => [
						'language' => 'html',
						'theme'    => 'chrome',
					],
					'class'           => 'fusion-gutter-and-or-and',
					'required'        => [
						[
							'setting'  => 'header_v4_content',
							'operator' => '=',
							'value'    => 'banner',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v4',
						],
						[
							'setting'  => 'header_v4_content',
							'operator' => '=',
							'value'    => 'banner',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
					],
					// Partial refresh for the header.
					'partial_refresh' => [
						'header_content_banner_code' => [
							'selector'              => '.fusion-header-content-3-wrapper',
							'container_inclusive'   => true,
							'render_callback'       => 'avada_header_content_3',
							'success_trigger_event' => 'header-rendered',
						],
					],
				],
			],
		],
		'header_info_2'  => [
			'label'       => esc_html__( 'Header Background Image', 'Avada' ),
			'description' => '',
			'id'          => 'header_info_2',
			'default'     => '',
			'icon'        => true,
			'type'        => 'sub-section',
			'hidden'      => $has_global_header,
			'fields'      => [
				'header_global_header_template_bg_notice' => [
					'id'          => 'header_global_header_template_bg_notice',
					'label'       => '',
					'description' => class_exists( 'Fusion_Template_Builder' ) ? sprintf(
						/* translators: %1$s: Content|Footer|Page Title Bar. %2$s: Layout selection URL. */
						'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> For more flexibility and a more modern, performant setup, we recommend using the %1$s Builder. To create a custom %1$s Layout, <a href="%2$s" target="_blank">visit this page</a>.', 'Avada' ) . '</div>',
						Fusion_Template_Builder::get_instance()->get_template_terms()['header']['label'],
						admin_url( 'admin.php?page=avada-layouts' )
					) : '',
					'type'        => 'custom',
				],
				'header_bg_image'                         => [
					'label'       => esc_html__( 'Background Image For Header Area', 'Avada' ),
					'description' => esc_html__( 'Select an image for the header background. If left empty, the header background color will be used. For top headers the image displays on top of the header background color and will only display if header background color opacity is set to 1. For side headers the image displays behind the header background color so the header background opacity must be set below 1 to see the image.', 'Avada' ),
					'id'          => 'header_bg_image',
					'default'     => '',
					'mod'         => '',
					'type'        => 'media',
					'css_vars'    => [
						[
							'name'     => '--header_bg_image',
							'choice'   => 'url',
							'callback' => [ 'fallback_to_value', [ 'url("$")', '' ] ],
						],
					],
					'output'      => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'has-image' ],
									'element'   => 'body',
									'className' => 'avada-has-header-bg-image',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_bg_full'                          => [
					'label'       => esc_html__( '100% Background Image', 'Avada' ),
					'description' => esc_html__( 'Turn on to have the header background image display at 100% in width and height according to the window size.', 'Avada' ),
					'id'          => 'header_bg_full',
					'default'     => '0',
					'type'        => 'switch',
					'required'    => [
						[
							'setting'  => 'header_bg_image',
							'operator' => '!=',
							'value'    => '',
						],
						[
							'setting'  => 'header_bg_image',
							'operator' => '!=',
							'value'    => [
								'url' => '',
							],
						],
						[
							'setting'  => 'header_bg_image',
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
					'output'      => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'true' ],
									'element'   => 'body',
									'className' => 'avada-has-header-bg-full',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_bg_parallax'                      => [
					'label'       => esc_html__( 'Parallax Background Image', 'Avada' ),
					'description' => esc_html__( 'Turn on to use a parallax scrolling effect on the background image. Only works for top header position.', 'Avada' ),
					'id'          => 'header_bg_parallax',
					'default'     => '1',
					'type'        => 'switch',
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '==',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_bg_image',
							'operator' => '!=',
							'value'    => '',
						],
						[
							'setting'  => 'header_bg_image',
							'operator' => '!=',
							'value'    => [
								'url' => '',
							],
						],
						[
							'setting'  => 'header_bg_image',
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
					'output'      => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'true' ],
									'element'   => 'body',
									'className' => 'avada-has-header-bg-parallax',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_bg_repeat'                        => [
					'label'       => esc_html__( 'Background Repeat', 'Avada' ),
					'description' => esc_html__( 'Controls how the background image repeats.', 'Avada' ),
					'id'          => 'header_bg_repeat',
					'default'     => 'no-repeat',
					'type'        => 'select',
					'choices'     => [
						'repeat'    => esc_html__( 'Repeat All', 'Avada' ),
						'repeat-x'  => esc_html__( 'Repeat Horizontally', 'Avada' ),
						'repeat-y'  => esc_html__( 'Repeat Vertically', 'Avada' ),
						'no-repeat' => esc_html__( 'No Repeat', 'Avada' ),
					],
					'required'    => [
						[
							'setting'  => 'header_bg_image',
							'operator' => '!=',
							'value'    => '',
						],
						[
							'setting'  => 'header_bg_image',
							'operator' => '!=',
							'value'    => [
								'url' => '',
							],
						],
						[
							'setting'  => 'header_bg_image',
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
							'name' => '--header_bg_repeat',
						],
					],
					'output'      => [
						[
							'element'       => 'body',
							'function'      => 'attr',
							'attr'          => 'class',
							'value_pattern' => 'avada-header-bg-$',
							'remove_attrs'  => [ 'avada-header-bg-repeat', 'avada-header-bg-repeat-x', 'avada-header-bg-repeat-y', 'avada-header-bg-no-repeat' ],
						],
					],
				],
			],
		],
		'header_styling' => [
			'label'       => esc_html__( 'Header Styling', 'Avada' ),
			'description' => '',
			'id'          => 'header_styling',
			'default'     => '',
			'icon'        => true,
			'type'        => 'sub-section',
			'hidden'      => $has_global_header,
			'fields'      => [
				'header_global_header_template_styling_notice' => [
					'id'          => 'header_global_header_template_styling_notice',
					'label'       => '',
					'description' => class_exists( 'Fusion_Template_Builder' ) ? sprintf(
						/* translators: %1$s: Content|Footer|Page Title Bar. %2$s: Layout selection URL. */
						'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> For more flexibility and a more modern, performant setup, we recommend using the %1$s Builder. To create a custom %1$s Layout, <a href="%2$s" target="_blank">visit this page</a>.', 'Avada' ) . '</div>',
						Fusion_Template_Builder::get_instance()->get_template_terms()['header']['label'],
						admin_url( 'admin.php?page=avada-layouts' )
					) : '',
					'type'        => 'custom',
				],
				'side_header_width'       => [
					'label'       => esc_html__( 'Header Width For Left/Right Position', 'Avada' ),
					'description' => esc_html__( 'Controls the width of the left or right side header.', 'Avada' ),
					'id'          => 'side_header_width',
					'default'     => '280',
					'type'        => 'slider',
					'choices'     => [
						'min'  => '0',
						'max'  => '800',
						'step' => '1',
					],
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
					],
					'css_vars'    => [
						[
							'name'          => '--side_header_width',
							'value_pattern' => '$px',
						],
						[
							'name' => '--side_header_width-int',
						],
					],
					'output'      => [
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'globalVar' => 'dummy',
									'id'        => 'dummy',
									'trigger'   => [ 'resize' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_padding'          => [
					'label'       => esc_html__( 'Header Padding', 'Avada' ),
					'description' => esc_html__( 'Controls the top/right/bottom/left padding for the header.', 'Avada' ),
					'id'          => 'header_padding',
					'choices'     => [
						'top'    => true,
						'bottom' => true,
						'left'   => true,
						'right'  => true,
					],
					'default'     => [
						'top'    => '0px',
						'bottom' => '0px',
						'left'   => '0px',
						'right'  => '0px',
					],
					'type'        => 'spacing',
					'css_vars'    => [
						[
							'name'   => '--header_padding-top',
							'choice' => 'top',
						],
						[
							'name'   => '--header_padding-bottom',
							'choice' => 'bottom',
						],
						[
							'name'   => '--header_padding-left',
							'choice' => 'left',
						],
						[
							'name'   => '--header_padding-right',
							'choice' => 'right',
						],
					],
					'output'      => [
						// This is for the avadaHeaderVars.header_padding_top var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'choice'            => 'top',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'choice'    => 'top',
									'globalVar' => 'avadaHeaderVars',
									'id'        => 'header_padding_top',
									'trigger'   => [ 'resize', 'scroll' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
						// This is for the avadaHeaderVars.header_padding_bottom var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'choice'            => 'bottom',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'choice'    => 'bottom',
									'globalVar' => 'avadaHeaderVars',
									'id'        => 'header_padding_bottom',
									'trigger'   => [ 'resize', 'scroll' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_shadow'           => [
					'label'       => esc_html__( 'Header Shadow', 'Avada' ),
					'description' => esc_html__( 'Turn on to display a header drop shadow.', 'Avada' ),
					'id'          => 'header_shadow',
					'default'     => '0',
					'type'        => 'switch',
					'output'      => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'true' ],
									'element'   => '.fusion-top-header .fusion-header-wrapper, #side-header',
									'className' => 'fusion-header-shadow',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_100_width'        => [
					'label'       => esc_html__( '100% Header Width', 'Avada' ),
					'description' => esc_html__( 'Turn on to have the header area display at 100% width according to the window size. Turn off to follow site width.', 'Avada' ),
					'id'          => 'header_100_width',
					'default'     => '0',
					'type'        => 'switch',
					'required'    => [
						[
							'setting'  => 'layout',
							'operator' => '==',
							'value'    => 'wide',
						],
						[
							'setting'  => 'header_position',
							'operator' => '==',
							'value'    => 'top',
						],
					],
					'output'      => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'true' ],
									'element'   => 'body',
									'className' => 'avada-has-header-100-width',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_bg_color'         => [
					'label'       => esc_html__( 'Header Background Color', 'Avada' ),
					'description' => esc_html__( 'Controls the background color and opacity for the header. For top headers, opacity set below 1 will remove the header height completely. For side headers, opacity set below 1 will display a color overlay. Transparent headers are disabled on all archive pages due to technical limitations.', 'Avada' ),
					'id'          => 'header_bg_color',
					'type'        => 'color-alpha',
					'default'     => 'var(--awb-color1)',
					'css_vars'    => [
						[
							'name'     => '--header_bg_color',
							'element'  => '#side-header,.fusion-header',
							'callback' => [ 'sanitize_color' ],
						],
					],
					'output'      => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'header-not-opaque' ],
									'element'   => 'html',
									'className' => 'avada-header-color-not-opaque',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'archive_header_bg_color' => [
					'label'       => esc_html__( 'Archive Header Background Color', 'Avada' ),
					'description' => esc_html__( 'Controls the background color and opacity for the header on archive pages, search page and 404 page. For top headers, opacity set below 1 will remove the header height completely. For side headers, opacity set below 1 will display a color overlay.', 'Avada' ),
					'id'          => 'archive_header_bg_color',
					'type'        => 'color-alpha',
					'default'     => 'var(--awb-color1)',
					'css_vars'    => [
						[
							'name'     => '--archive_header_bg_color',
							'element'  => '#side-header,.fusion-header',
							'callback' => [ 'sanitize_color' ],
						],
					],
					'output'      => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'header-not-opaque' ],
									'element'   => 'html',
									'className' => 'avada-header-color-not-opaque',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_border_color'     => [
					'label'       => esc_html__( 'Header Border Color', 'Avada' ),
					'description' => esc_html__( 'Controls the border colors for the header. If using left or right header position it controls the menu divider lines.', 'Avada' ),
					'id'          => 'header_border_color',
					'default'     => 'rgba(226,226,226,0)',
					'type'        => 'color-alpha',
					'class'       => 'fusion-or-gutter',
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v2',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v3',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v4',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v5',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--header_border_color',
							'element'  => '.fusion-header-wrapper,#side-header',
							'callback' => [ 'sanitize_color' ],
						],
					],
					'output'      => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'full-transparent' ],
									'element'   => 'body',
									'className' => 'avada-header-border-color-full-transparent',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_top_bg_color'     => [
					'label'       => esc_html__( 'Header Top Background Color', 'Avada' ),
					'description' => esc_html__( 'Controls the background color of the top header section used in Headers 2-5.', 'Avada' ),
					'id'          => 'header_top_bg_color',
					'default'     => 'var(--awb-color4)',
					'type'        => 'color-alpha',
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v1',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--header_top_bg_color',
							'element'  => '.fusion-secondary-header',
							'callback' => [ 'sanitize_color' ],
						],
					],
					'output'      => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'not-opaque' ],
									'element'   => 'html',
									'className' => 'avada-header-top-bg-not-opaque',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'tagline_font_size'       => [
					'label'       => esc_html__( 'Header Tagline Font Size', 'Avada' ),
					'description' => esc_html__( 'Controls the font size for the tagline text when using header 4.', 'Avada' ),
					'id'          => 'tagline_font_size',
					'default'     => '16px',
					'type'        => 'dimension',
					'choices'     => [
						'units' => [ 'px', 'em' ],
					],
					'class'       => 'fusion-or-gutter',
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '==',
							'value'    => 'v4',
						],
					],
					'css_vars'    => [
						[
							'name'    => '--tagline_font_size',
							'element' => '.fusion-header-tagline',
						],
					],
				],
				'tagline_font_color'      => [
					'label'       => esc_html__( 'Header Tagline Font Color', 'Avada' ),
					'description' => esc_html__( 'Controls the font color for the tagline text when using header 4.', 'Avada' ),
					'id'          => 'tagline_font_color',
					'default'     => 'var(--awb-color8)',
					'type'        => 'color-alpha',
					'class'       => 'fusion-or-gutter',
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '==',
							'value'    => 'v4',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--tagline_font_color',
							'element'  => '.fusion-header-tagline',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
			],
		],
		'sticky_header'  => [
			'label'       => esc_html__( 'Sticky Header', 'Avada' ),
			'description' => '',
			'id'          => 'sticky_header',
			'default'     => '',
			'icon'        => true,
			'type'        => 'sub-section',
			'hidden'      => $has_global_header,
			'fields'      => [
				'header_global_header_template_sticky_notice' => [
					'id'          => 'header_global_header_template_sticky_notice',
					'label'       => '',
					'description' => class_exists( 'Fusion_Template_Builder' ) ? sprintf(
						/* translators: %1$s: Content|Footer|Page Title Bar. %2$s: Layout selection URL. */
						'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> For more flexibility and a more modern, performant setup, we recommend using the %1$s Builder. To create a custom %1$s Layout, <a href="%2$s" target="_blank">visit this page</a>.', 'Avada' ) . '</div>',
						Fusion_Template_Builder::get_instance()->get_template_terms()['header']['label'],
						admin_url( 'admin.php?page=avada-layouts' )
					) : '',
					'type'        => 'custom',
				],
				'header_sticky'               => [
					'label'           => esc_html__( 'Sticky Header', 'Avada' ),
					'description'     => esc_html__( 'Turn on to enable a sticky header.', 'Avada' ),
					'id'              => 'header_sticky',
					'default'         => 1,
					'type'            => 'switch',
					// Partial refresh for the header.
					'partial_refresh' => [
						'header_content_sticky_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_content_sticky_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_content_sticky' => [
							'selector'              => '.fusion-header-wrapper',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'fusion-reinit-sticky-header header-rendered',
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
									'element'   => 'body',
									'className' => 'fusion-sticky-header',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
						// This is for the avadaHeaderVars.header_sticky var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'globalVar' => 'avadaHeaderVars',
									'id'        => 'header_sticky',
									'trigger'   => [ 'fusion-reinit-sticky-header' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
						// This is for the avadaMenuVars.header_sticky var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'globalVar' => 'avadaMenuVars',
									'id'        => 'header_sticky',
									'trigger'   => [ 'fusion-reinit-sticky-header' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
						// This is for the avadaSidebarsVars.header_sticky var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'globalVar' => 'avadaSidebarsVars',
									'id'        => 'header_sticky',
									'trigger'   => [ 'fusion-reinit-sticky-header' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_sticky_tablet'        => [
					'label'       => esc_html__( 'Sticky Header on Tablets', 'Avada' ),
					'description' => esc_html__( 'Turn on to enable a sticky header when scrolling on tablets.', 'Avada' ),
					'id'          => 'header_sticky_tablet',
					'default'     => '0',
					'type'        => 'switch',
					'required'    => [
						[
							'setting'  => 'header_sticky',
							'operator' => '!=',
							'value'    => 0,
						],
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
					],
					'output'      => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'false' ],
									'element'   => 'body',
									'className' => 'no-tablet-sticky-header',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
						// This is for the avadaHeaderVars.header_sticky_tablet var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'choice'            => 'top',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'choice'    => 'top',
									'globalVar' => 'avadaHeaderVars',
									'id'        => 'header_sticky_tablet',
									'trigger'   => [ 'resize', 'scroll' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
						// This is for the avadaMenuVars.header_sticky_tablet var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'choice'            => 'top',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'choice'    => 'top',
									'globalVar' => 'avadaMenuVars',
									'id'        => 'header_sticky_tablet',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
						// This is for the avadaSidebarsVars.header_sticky_tablet var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'choice'            => 'top',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'choice'    => 'top',
									'globalVar' => 'avadaSidebarsVars',
									'id'        => 'header_sticky_tablet',
									'trigger'   => [ 'resize', 'scroll' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_sticky_mobile'        => [
					'label'       => esc_html__( 'Sticky Header on Mobiles', 'Avada' ),
					'description' => esc_html__( 'Turn on to enable a sticky header when scrolling on mobiles.', 'Avada' ),
					'id'          => 'header_sticky_mobile',
					'default'     => '0',
					'type'        => 'switch',
					'required'    => [
						[
							'setting'  => 'header_sticky',
							'operator' => '!=',
							'value'    => 0,
						],
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
					],
					'output'      => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'false' ],
									'element'   => 'body',
									'className' => 'no-mobile-sticky-header',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
						// This is for the avadaHeaderVars.header_sticky_mobile var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'choice'            => 'top',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'choice'    => 'top',
									'globalVar' => 'avadaHeaderVars',
									'id'        => 'header_sticky_mobile',
									'trigger'   => [ 'resize', 'scroll' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
						// This is for the avadaMenuVars.header_sticky_mobile var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'choice'            => 'top',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'choice'    => 'top',
									'globalVar' => 'avadaMenuVars',
									'id'        => 'header_sticky_mobile',
									'trigger'   => [ 'resize', 'scroll' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
						// This is for the avadaSidebarsVars.header_sticky_mobile var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'choice'            => 'top',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'choice'    => 'top',
									'globalVar' => 'avadaSidebarsVars',
									'id'        => 'header_sticky_mobile',
									'trigger'   => [ 'resize', 'scroll' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_sticky_shrinkage'     => [
					'label'       => esc_html__( 'Sticky Header Animation', 'Avada' ),
					'description' => esc_html__( 'Turn on to allow the sticky header to animate to a smaller height when activated. Only works with header v1 - v3, v6 and v7.', 'Avada' ),
					'id'          => 'header_sticky_shrinkage',
					'default'     => '0',
					'type'        => 'switch',
					'required'    => [
						[
							'setting'  => 'header_sticky',
							'operator' => '!=',
							'value'    => 0,
						],
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
					],
					'output'      => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'true' ],
									'element'   => 'body',
									'className' => 'avada-sticky-shrinkage',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_sticky_type2_layout'  => [
					'label'           => esc_html__( 'Sticky Header Display For Headers 4 - 5 ', 'Avada' ),
					'description'     => esc_html__( 'Controls what displays in the sticky header when using header v4 - v5.', 'Avada' ),
					'id'              => 'header_sticky_type2_layout',
					'default'         => 'menu_only',
					'type'            => 'radio-buttonset',
					'choices'         => [
						'menu_only'     => esc_html__( 'Menu Only', 'Avada' ),
						'menu_and_logo' => esc_html__( 'Menu + Logo Area', 'Avada' ),
					],
					'required'        => [
						[
							'setting'  => 'header_sticky',
							'operator' => '!=',
							'value'    => 0,
						],
						[
							'setting'  => 'header_position',
							'operator' => '=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v1',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v2',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v3',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v7',
						],
					],
					// Partial refresh for the header.
					'partial_refresh' => [
						'header_sticky_type2_layout_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_sticky_type2_layout_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_sticky_type2_layout_layout' => [
							'selector'              => '.fusion-header-wrapper',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'fusion-reinit-sticky-header header-rendered',
						],
					],
				],
				'header_sticky_shadow'        => [
					'label'       => esc_html__( 'Sticky Header Shadow', 'Avada' ),
					'description' => esc_html__( 'Turn on to display a sticky header drop shadow.', 'Avada' ),
					'id'          => 'header_sticky_shadow',
					'default'     => '1',
					'type'        => 'switch',
					'required'    => [
						[
							'setting'  => 'header_sticky',
							'operator' => '!=',
							'value'    => 0,
						],
					],
					'output'      => [
						// This is for the avadaHeaderVars.header_sticky_shadow var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'choice'            => 'top',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'choice'    => 'top',
									'globalVar' => 'avadaHeaderVars',
									'id'        => 'header_sticky_shadow',
									'trigger'   => [ 'ready', 'scroll' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'true' ],
									'element'   => '.fusion-header',
									'className' => 'fusion-sticky-shadow',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_sticky_bg_color'      => [
					'label'       => esc_html__( 'Sticky Header Background Color', 'Avada' ),
					'description' => esc_html__( 'Controls the background color for the sticky header.', 'Avada' ),
					'id'          => 'header_sticky_bg_color',
					'type'        => 'color-alpha',
					'default'     => 'var(--awb-color1)',
					'required'    => [
						[
							'setting'  => 'header_sticky',
							'operator' => '!=',
							'value'    => 0,
						],
					],
					'css_vars'    => [
						[
							'name'     => '--header_sticky_bg_color',
							'element'  => '.fusion-arrow-svg,.fusion-header-wrapper,#side-header',
							'callback' => [ 'sanitize_color' ],
						],
					],
					'output'      => [
						[
							'element'           => 'helperElement',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'globalVar' => 'fusionContainerVars',
									'id'        => 'is_sticky_header_transparent',
									'trigger'   => [ 'resize', 'fusion-element-render-fusion_builder_container' ],
									'callback'  => 'fusionReturnColorAlphaInt',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'header_sticky_menu_color'    => [
					'label'       => esc_html__( 'Sticky Header Menu Font Color', 'Avada' ),
					'description' => esc_html__( 'Controls the color for main menu text in the sticky header.', 'Avada' ),
					'id'          => 'header_sticky_menu_color',
					'type'        => 'color-alpha',
					'default'     => 'var(--awb-color8)',
					'required'    => [
						[
							'setting'  => 'header_sticky',
							'operator' => '!=',
							'value'    => 0,
						],
					],
					'css_vars'    => [
						[
							'name'     => '--header_sticky_menu_color',
							'element'  => '.fusion-main-menu',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'header_sticky_nav_padding'   => [
					'label'       => esc_html__( 'Sticky Header Menu Item Padding', 'Avada' ),
					'description' => esc_html__( 'Controls the space between each menu item in the sticky header.', 'Avada' ),
					'id'          => 'header_sticky_nav_padding',
					'default'     => '35',
					'type'        => 'slider',
					'choices'     => [
						'min'  => '0',
						'max'  => '200',
						'step' => '1',
					],
					'required'    => [
						[
							'setting'  => 'header_sticky',
							'operator' => '!=',
							'value'    => '0',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name'          => '--header_sticky_nav_padding',
							'element'       => '.fusion-main-menu,.fusion-logo-background',
							'value_pattern' => '$px',
						],
					],
				],
				'header_sticky_nav_font_size' => [
					'label'       => esc_html__( 'Sticky Header Navigation Font Size', 'Avada' ),
					'description' => esc_html__( 'Controls the font size of the menu items in the sticky header.', 'Avada' ),
					'id'          => 'header_sticky_nav_font_size',
					'default'     => '14px',
					'type'        => 'dimension',
					'choices'     => [
						'units' => [ 'px', 'em' ],
					],
					'required'    => [
						[
							'setting'  => 'header_sticky',
							'operator' => '!=',
							'value'    => 0,
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name'    => '--header_sticky_nav_font_size',
							'element' => '.fusion-main-menu',
						],
					],
				],
			],
		],
	];

	$sections['header']['fields'] = array_merge( $sections['header']['fields'], $fields );

	return $sections;

}

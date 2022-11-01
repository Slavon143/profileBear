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
 * Layout
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_layout( $sections ) {

	$settings = get_option( Avada::get_option_name(), [] );
	$language = Fusion_Multilingual::get_active_language();

	$avada_510_site_width_calc_option_name = 'avada_510_site_width_calc';
	$display_site_width_warning            = false;
	if ( '' !== $language && 'en' !== $language ) {
		$avada_510_site_width_calc_option_name .= $language;
	}
	if ( get_option( $avada_510_site_width_calc_option_name, false ) ) {
		if ( isset( $settings['site_width'] ) && false !== strpos( $settings['site_width'], 'calc' ) ) {
			$display_site_width_warning = true;
		}
	}

	$sections['layout'] = [
		'label'    => esc_html__( 'Layout', 'Avada' ),
		'id'       => 'heading_layout',
		'priority' => 1,
		'icon'     => 'el-icon-website',
		'alt_icon' => 'fusiona-browser',
		'fields'   => [
			'layout'                      => [
				'label'       => esc_html__( 'Layout', 'Avada' ),
				'description' => esc_html__( 'Controls the site layout.', 'Avada' ),
				'id'          => 'layout',
				'default'     => 'wide',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'boxed' => esc_html__( 'Boxed', 'Avada' ),
					'wide'  => esc_html__( 'Wide', 'Avada' ),
				],
				'output'      => [
					// Toggle <html> classes.
					[
						'element'       => 'html',
						'function'      => 'attr',
						'attr'          => 'class',
						'toLowerCase'   => true,
						'value_pattern' => 'avada-html-layout-$',
						'remove_attrs'  => [ 'avada-html-layout-boxed', 'avada-html-layout-wide' ],
					],
					// Toggle <body> classes.
					[
						'element'       => 'body',
						'function'      => 'attr',
						'attr'          => 'class',
						'toLowerCase'   => true,
						'value_pattern' => 'layout-$-mode',
						'remove_attrs'  => [ 'layout-boxed-mode', 'layout-wide-mode' ],
					],
					// avadaMenuVars.site_layout.
					[
						'element'           => 'helperElement',
						'property'          => 'bottom',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'avadaMenuVars',
								'id'        => 'site_layout',
								'trigger'   => [ 'fusionPositionSubmenus' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
					// avadaHeaderVars.layout_mode.
					[
						'element'           => 'helperElement',
						'property'          => 'bottom',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'avadaHeaderVars',
								'id'        => 'layout_mode',
								'trigger'   => [ 'fusionSliderReTrigger', 'fusion-reinit-sticky-header' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'site_width'                  => [
				'label'           => esc_html__( 'Site Width', 'Avada' ),
				'description'     => esc_html__( 'Controls the overall site width.', 'Avada' ),
				'id'              => 'site_width',
				'default'         => '1200px',
				'type'            => 'dimension',
				'choices'         => [ 'px', '%' ],
				'desc'            => ( $display_site_width_warning ) ? esc_html__( 'The value was changed in Avada 5.1 to include both the site-width & side-header width, ex: calc(90% + 300px). Leave this as is, or update it with a single percentage, ex: 95%', 'Avada' ) : '',
				'css_vars'        => [
					[
						'name' => '--site_width',
					],
					[
						'name'     => '--site_width-int',
						'callback' => [ 'convert_font_size_to_px', '' ],
					],
				],
				'output'          => [
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'callback'          => [
							'toggle_class',
							[
								'condition' => [ '%', 'contains' ],
								'element'   => 'html',
								'className' => 'avada-has-site-width-percent',
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
								'condition' => [ '100%', '===' ],
								'element'   => 'html',
								'className' => 'avada-has-site-width-100-percent',
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],

					// fusionTypographyVars.site_width.
					[
						'element'           => 'helperElement',
						'property'          => 'bottom',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionTypographyVars',
								'id'        => 'site_width',
								'trigger'   => [ 'fusionInitTypography' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
					// Trigger events.
					[
						'element'           => 'helperElement',
						'property'          => 'bottom',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'dummy',
								'id'        => 'dummy',
								'trigger'   => [ 'fusion-reinit-sticky-header' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
				// Partial refresh for the header.
				'partial_refresh' => [
					'site_width_header_remove_before_hook' => [
						'selector'            => '.avada-hook-before-header-wrapper',
						'container_inclusive' => true,
						'render_callback'     => '__return_null',
					],
					'site_width_header_remove_after_hook'  => [
						'selector'            => '.avada-hook-after-header-wrapper',
						'container_inclusive' => true,
						'render_callback'     => '__return_null',
					],
					'site_width_header'                    => [
						'selector'              => '.fusion-header-wrapper',
						'container_inclusive'   => true,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
						'success_trigger_event' => 'header-rendered',
					],
				],
			],
			'margin_offset'               => [
				'label'       => esc_html__( 'Boxed Mode Top/Bottom Offset', 'Avada' ),
				'description' => esc_html__( 'Controls the top/bottom offset of the boxed background.', 'Avada' ),
				'id'          => 'margin_offset',
				'choices'     => [
					'top'    => true,
					'bottom' => true,
				],
				'default'     => [
					'top'    => '0px',
					'bottom' => '0px',
				],
				'type'        => 'spacing',
				'required'    => [
					[
						'setting'  => 'layout',
						'operator' => '==',
						'value'    => 'boxed',
					],
				],
				'css_vars'    => [
					[
						'name'   => '--margin_offset-top',
						'choice' => 'top',
					],
					[
						'name'   => '--margin_offset-bottom',
						'choice' => 'bottom',
					],
					[
						'name'     => '--margin_offset-top-no-percent',
						'choice'   => 'top',
						'callback' => [ 'string_replace', [ '%', 'vh' ] ],
					],
					[
						'name'     => '--margin_offset-bottom-no-percent',
						'choice'   => 'bottom',
						'callback' => [ 'string_replace', [ '%', 'vh' ] ],
					],
				],
				'output'      => [
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'choice'            => 'top',
						'callback'          => [
							'toggle_class',
							[
								'condition' => [ '', 'is-zero-or-empty' ],
								'element'   => 'body',
								'className' => 'avada-has-zero-margin-offset-top',
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
					// Trigger events.
					[
						'element'           => 'helperElement',
						'property'          => 'bottom',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'dummy',
								'id'        => 'dummy',
								'trigger'   => [ 'fusion-reinit-sticky-header' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'scroll_offset'               => [
				'label'       => esc_html__( 'Boxed Mode Offset Scroll Mode', 'Avada' ),
				'description' => esc_html__( 'Choose how the page will scroll. Framed scrolling will keep the offset in place, while Full scrolling removes the offset when scrolling the page.', 'Avada' ),
				'id'          => 'scroll_offset',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'framed' => esc_html__( 'Framed Scrolling', 'Avada' ),
					'full'   => esc_html__( 'Full Scrolling', 'Avada' ),
				],
				'default'     => 'full',
				'required'    => [
					[
						'setting'  => 'layout',
						'operator' => '==',
						'value'    => 'boxed',
					],
				],
				'output'      => [
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'callback'          => [
							'toggle_class',
							[
								'condition' => [ '===', 'framed' ],
								'element'   => 'html',
								'className' => 'avada-html-layout-framed',
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
					[
						'element'       => 'body',
						'function'      => 'attr',
						'attr'          => 'class',
						'toLowerCase'   => true,
						'value_pattern' => 'layout-scroll-offset-$',
						'remove_attrs'  => [ 'layout-scroll-offset-framed', 'layout-scroll-offset-full' ],
					],
					// Change avadaHeaderVars.scroll_offset.
					[
						'element'           => 'helperElement',
						'property'          => 'bottom',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'avadaHeaderVars',
								'id'        => 'scroll_offset',
								'trigger'   => [ 'scroll' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'boxed_modal_shadow'          => [
				'label'       => esc_html__( 'Boxed Mode Shadow Type', 'Avada' ),
				'description' => esc_html__( 'Controls the type of shadow your boxed mode displays.', 'Avada' ),
				'id'          => 'boxed_modal_shadow',
				'default'     => 'None',
				'type'        => 'select',
				'choices'     => [
					'none'   => esc_html__( 'No Shadow', 'Avada' ),
					'light'  => esc_html__( 'Light Shadow', 'Avada' ),
					'medium' => esc_html__( 'Medium Shadow', 'Avada' ),
					'hard'   => esc_html__( 'Hard Shadow', 'Avada' ),
				],
				'required'    => [
					[
						'setting'  => 'layout',
						'operator' => '==',
						'value'    => 'boxed',
					],
				],
				'output'      => [
					[
						'element'       => 'body',
						'function'      => 'attr',
						'attr'          => 'class',
						'value_pattern' => 'avada-has-boxed-modal-shadow-$',
						'remove_attrs'  => [ 'avada-has-boxed-modal-shadow-none', 'avada-has-boxed-modal-shadow-light', 'avada-has-boxed-modal-shadow-medium', 'avada-has-boxed-modal-shadow-hard' ],
					],
					// Trigger events.
					[
						'element'           => 'helperElement',
						'property'          => 'bottom',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'dummy',
								'id'        => 'dummy',
								'trigger'   => [ 'fusion-reinit-sticky-header' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'main_padding'                => [
				'label'       => esc_html__( 'Page Content Padding', 'Avada' ),
				'description' => esc_html__( 'Controls the top/bottom padding for page content.', 'Avada' ),
				'id'          => 'main_padding',
				'choices'     => [
					'top'    => true,
					'bottom' => true,
				],
				'default'     => [
					'top'    => '60px',
					'bottom' => '60px',
				],
				'type'        => 'spacing',
				'css_vars'    => [
					[
						'name'   => '--main_padding-top',
						'choice' => 'top',
					],
					[
						'name'     => '--main_padding-top-or-55px',
						'choice'   => 'top',
						'callback' => [ 'fallback_to_value_if_empty', '55px' ],
					],
					[
						'name'   => '--main_padding-bottom',
						'choice' => 'bottom',
					],
				],
			],
			'page_template'               => [
				'label'       => esc_html__( 'Default Page Template', 'Avada' ),
				'description' => esc_html__( 'Choose the default page template.', 'Avada' ),
				'id'          => 'page_template',
				'default'     => '100_width',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'100_width'  => esc_html__( '100% Width', 'Avada' ),
					'site_width' => esc_html__( 'Site Width', 'Avada' ),
				],
				// No need to refresh the page.
				'transport'   => 'postMessage',
			],
			'hundredp_padding'            => [
				'label'       => esc_html__( '100% Width Padding', 'Avada' ),
				'description' => esc_html__( 'Controls the left and right padding for page content when using 100% site width, 100% width page template or 100% width post option. This does not affect Avada Builder containers.', 'Avada' ),
				'id'          => 'hundredp_padding',
				'default'     => '30px',
				'type'        => 'dimension',
				'choices'     => [ 'px', '%' ],
				'css_vars'    => [
					[
						'name' => '--hundredp_padding',
					],
					[
						'name'     => '--hundredp_padding-fallback_to_zero',
						'callback' => 'fallback_to_zero',
					],
					[
						'name'     => '--hundredp_padding-fallback_to_zero_px',
						'callback' => [ 'fallback_to_value_if_empty', '0px' ],
					],
					[
						'name'     => '--hundredp_padding-hundred_percent_negative_margin',
						'callback' => [ 'hundred_percent_negative_margin', '' ],
					],
				],
			],
			'single_sidebar_layouts_info' => [
				'label'       => esc_html__( 'Single Sidebar Layouts', 'Avada' ),
				'description' => '',
				'id'          => 'single_sidebar_layouts_info',
				'type'        => 'info',
			],
			'sidebar_width'               => [
				'label'       => esc_html__( 'Single Sidebar Width', 'Avada' ),
				'description' => esc_html__( 'Controls the width of the sidebar when only one sidebar is present.', 'Avada' ),
				'id'          => 'sidebar_width',
				'default'     => '24%',
				'type'        => 'dimension',
				'choices'     => [ 'px', '%' ],
				'css_vars'    => [
					[
						'name' => '--sidebar_width',
					],
				],
			],
			'sidebar_gutter'              => [
				'label'       => esc_html__( 'Single Sidebar Gutter', 'Avada' ),
				'description' => esc_html__( 'Controls the space between the main content and a single sidebar.', 'Avada' ),
				'id'          => 'sidebar_gutter',
				'default'     => '6%',
				'type'        => 'dimension',
				'css_vars'    => [
					[
						'name' => '--sidebar_gutter',
					],
				],
			],
			'dual_sidebar_layouts_info'   => [
				'label'       => esc_html__( 'Dual Sidebar Layouts', 'Avada' ),
				'description' => '',
				'id'          => 'dual_sidebar_layouts_info',
				'type'        => 'info',
			],
			'sidebar_2_1_width'           => [
				'label'       => esc_html__( 'Dual Sidebar Width 1', 'Avada' ),
				'description' => esc_html__( 'Controls the width of sidebar 1 when dual sidebars are present.', 'Avada' ),
				'id'          => 'sidebar_2_1_width',
				'default'     => '20%',
				'type'        => 'dimension',
				'choices'     => [ 'px', '%' ],
				'css_vars'    => [
					[
						'name' => '--sidebar_2_1_width',
					],
				],
			],
			'sidebar_2_2_width'           => [
				'label'       => esc_html__( 'Dual Sidebar Width 2', 'Avada' ),
				'description' => esc_html__( 'Controls the width of sidebar 2 when dual sidebars are present.', 'Avada' ),
				'id'          => 'sidebar_2_2_width',
				'default'     => '20%',
				'type'        => 'dimension',
				'choices'     => [ 'px', '%' ],
				'css_vars'    => [
					[
						'name' => '--sidebar_2_2_width',
					],
				],
			],
			'dual_sidebar_gutter'         => [
				'label'       => esc_html__( 'Dual Sidebar Gutter', 'Avada' ),
				'description' => esc_html__( 'Controls the space between the main content and the sidebar when dual sidebars are present.', 'Avada' ),
				'id'          => 'dual_sidebar_gutter',
				'default'     => '4%',
				'type'        => 'dimension',
				'css_vars'    => [
					[
						'name' => '--dual_sidebar_gutter',
					],
				],
			],
		],
	];

	return $sections;

}

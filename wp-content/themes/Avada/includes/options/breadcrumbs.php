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
 * Page Title Bar
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_breadcrumbs( $sections ) {

	$sections['breadcrumbs'] = [
		'label'    => esc_html__( 'Breadcrumbs', 'Avada' ),
		'id'       => 'heading_breadcrumbs',
		'priority' => 7,
		'icon'     => 'el-icon-chevron-right',
		'alt_icon' => 'fusiona-breadcrumb',
		'fields'   => [
			'breadcrumb_mobile'                 => [
				'label'           => esc_html__( 'Breadcrumbs on Mobile Devices', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display breadcrumbs on mobile devices.', 'Avada' ),
				'id'              => 'breadcrumb_mobile',
				'default'         => '0',
				'type'            => 'switch',
				'soft_dependency' => true,
				'output'          => [
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'callback'          => [
							'toggle_class',
							[
								'condition' => [ '', 'false' ],
								'element'   => 'body',
								'className' => 'avada-has-breadcrumb-mobile-hidden',
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'breacrumb_prefix'                  => [
				'label'           => esc_html__( 'Breadcrumbs Prefix', 'Avada' ),
				'description'     => esc_html__( 'Controls the text before the breadcrumb menu.', 'Avada' ),
				'id'              => 'breacrumb_prefix',
				'default'         => '',
				'type'            => 'text',
				'soft_dependency' => true,
				'partial_refresh' => [
					'page_title_bar_contents_breacrumb_prefix' => [
						'selector'              => '.avada-page-titlebar-wrapper',
						'container_inclusive'   => false,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'page_titlebar_wrapper' ],
						'success_trigger_event' => 'fusion-ptb-refreshed',
					],
				],
			],
			'breadcrumb_separator'              => [
				'label'           => esc_html__( 'Breadcrumbs Separator', 'Avada' ),
				'description'     => esc_html__( 'Controls the type of separator between each breadcrumb.', 'Avada' ),
				'id'              => 'breadcrumb_separator',
				'default'         => '/',
				'type'            => 'text',
				'soft_dependency' => true,
				'partial_refresh' => [
					'page_title_bar_contents_breadcrumb_separator' => [
						'selector'              => '.avada-page-titlebar-wrapper',
						'container_inclusive'   => false,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'page_titlebar_wrapper' ],
						'success_trigger_event' => 'fusion-ptb-refreshed',
					],
				],
			],
			'breadcrumbs_font_size'             => [
				'label'           => esc_html__( 'Breadcrumbs Font Size', 'Avada' ),
				'description'     => esc_html__( 'Controls the font size for the breadcrumbs text.', 'Avada' ),
				'id'              => 'breadcrumbs_font_size',
				'default'         => '14px',
				'type'            => 'dimension',
				'choices'         => [
					'units' => [ 'px', 'em' ],
				],
				'soft_dependency' => true,
				'css_vars'        => [
					[
						'name'    => '--breadcrumbs_font_size',
						'element' => '.fusion-page-title-bar',
					],
				],
			],
			'breadcrumbs_text_color'            => [
				'label'           => esc_html__( 'Breadcrumbs Text Color', 'Avada' ),
				'description'     => esc_html__( 'Controls the text color of the breadcrumbs font.', 'Avada' ),
				'id'              => 'breadcrumbs_text_color',
				'default'         => 'var(--awb-color8)',
				'type'            => 'color-alpha',
				'soft_dependency' => true,
				'css_vars'        => [
					[
						'name'     => '--breadcrumbs_text_color',
						'element'  => '.fusion-page-title-bar',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'breadcrumbs_text_hover_color'      => [
				'label'           => esc_html__( 'Breadcrumbs Text Hover Color', 'Avada' ),
				'description'     => esc_html__( 'Controls the text hover color of the breadcrumbs font.', 'Avada' ),
				'id'              => 'breadcrumbs_text_hover_color',
				'default'         => 'var(--awb-color4)',
				'type'            => 'color-alpha',
				'soft_dependency' => true,
				'css_vars'        => [
					[
						'name'     => '--breadcrumbs_text_hover_color',
						'element'  => '.fusion-page-title-bar',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'breadcrumb_show_categories'        => [
				'label'           => esc_html__( 'Post Categories on Breadcrumbs', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display the post categories in the breadcrumbs path.', 'Avada' ),
				'id'              => 'breadcrumb_show_categories',
				'default'         => '1',
				'type'            => 'switch',
				'soft_dependency' => true,
				'partial_refresh' => [
					'page_title_bar_contents_breadcrumb_show_categories' => [
						'selector'              => '.avada-page-titlebar-wrapper',
						'container_inclusive'   => false,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'page_titlebar_wrapper' ],
						'success_trigger_event' => 'fusion-ptb-refreshed',
					],
				],
			],
			'breadcrumb_show_post_type_archive' => [
				'label'           => esc_html__( 'Post Type Archives on Breadcrumbs', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display post type archives in the breadcrumbs path.', 'Avada' ),
				'id'              => 'breadcrumb_show_post_type_archive',
				'default'         => '0',
				'type'            => 'switch',
				'soft_dependency' => true,
				'partial_refresh' => [
					'page_title_bar_contents_breadcrumb_show_post_type_archive' => [
						'selector'              => '.avada-page-titlebar-wrapper',
						'container_inclusive'   => false,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'page_titlebar_wrapper' ],
						'success_trigger_event' => 'fusion-ptb-refreshed',
					],
				],
			],
		],
	];

	return $sections;

}

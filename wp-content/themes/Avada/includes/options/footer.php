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
 * Footer settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_footer( $sections ) {

	// Check if we have a global footer override.
	$has_global_footer = false;
	if ( class_exists( 'Fusion_Template_Builder' ) ) {
		$default_layout    = Fusion_Template_Builder::get_default_layout();
		$has_global_footer = isset( $default_layout['data']['template_terms'] ) && isset( $default_layout['data']['template_terms']['footer'] ) && $default_layout['data']['template_terms']['footer'];
	}

	$sections['footer'] = [
		'label'    => esc_html__( 'Footer', 'Avada' ),
		'id'       => 'heading_footer',
		'priority' => 9,
		'icon'     => 'el-icon-arrow-down',
		'alt_icon' => 'fusiona-footer',
		'class'    => 'hidden-section-heading',
		'fields'   => [],
	];

	if ( $has_global_footer ) {
		$sections['footer']['fields']['footer_options_template_override_notice'] = [
			'id'          => 'footer_options_template_override_notice',
			'label'       => '',
			'description' => class_exists( 'Fusion_Template_Builder' ) ? sprintf(
				/* translators: 1: Content|Footer|Page Title Bar. 2: URL. */
				'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The options on this tab are not available because a global %1$s override is currently used. To edit your global layout please visit <a href="%2$s" target="_blank">this page</a>.', 'Avada' ) . '</div>',
				Fusion_Template_Builder::get_instance()->get_template_terms()['footer']['label'],
				admin_url( 'admin.php?page=avada-layouts' )
			) : '',
			'type'        => 'custom',
			'hidden'      => ! $has_global_footer,
		];
	}

	$fields = [
		'footer_content_options_subsection'          => [
			'label'  => esc_html__( 'Footer Content', 'Avada' ),
			'id'     => 'footer_content_options_subsection',
			'icon'   => true,
			'type'   => 'sub-section',
			'hidden' => $has_global_footer,
			'fields' => [
				'footer_options_template_content_notice' => [
					'id'          => 'footer_options_template_content_notice',
					'label'       => '',
					'description' => class_exists( 'Fusion_Template_Builder' ) ? sprintf(
						/* translators: %1$s: Content|Footer|Page Title Bar. %2$s: Layout selection URL. */
						'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> For more flexibility and a more modern, performant setup, we recommend using the %1$s Builder. To create a custom %1$s Layout, <a href="%2$s" target="_blank">visit this page</a>.', 'Avada' ) . '</div>',
						Fusion_Template_Builder::get_instance()->get_template_terms()['footer']['label'],
						admin_url( 'admin.php?page=avada-layouts' )
					) : '',
					'type'        => 'custom',
				],
				'footer_widgets'                         => [
					'label'           => esc_html__( 'Footer Widgets', 'Avada' ),
					'description'     => esc_html__( 'Turn on to display footer widgets.', 'Avada' ),
					'id'              => 'footer_widgets',
					'default'         => '1',
					'type'            => 'switch',
					'partial_refresh' => [
						'footer_content_footer_widgets' => [
							'selector'            => '.fusion-footer',
							'container_inclusive' => false,
							'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'footer' ],
							'skip_for_template'   => [ 'footer' ],
						],
					],
					'edit_shortcut'   => [
						'selector'  => [ '.fusion-footer' ],
						'shortcuts' => [
							[
								'aria_label'  => esc_html__( 'Edit Footer', 'Avada' ),
								'icon'        => 'fusiona-footer',
								'open_parent' => true,
								'link_to_template_if_override_active' => 'footer',
							],
							[
								'aria_label' => esc_html__( 'Edit Footer Widgets', 'Avada' ),
								'css_class'  => 'fusion-edit-sidebar',
								'link'       => admin_url( 'widgets.php' ),
								'disable_on_template_override' => 'footer',
							],
						],
					],
				],
				'footer_widgets_columns'                 => [
					'label'           => esc_html__( 'Number of Footer Columns', 'Avada' ),
					'description'     => esc_html__( 'Controls the number of columns in the footer.', 'Avada' ),
					'id'              => 'footer_widgets_columns',
					'default'         => '4',
					'choices'         => [
						'min'  => '1',
						'max'  => '6',
						'step' => '1',
					],
					'type'            => 'slider',
					'soft_dependency' => true,
					'partial_refresh' => [
						'footer_content_footer_widgets_columns' => [
							'selector'            => '.fusion-footer',
							'container_inclusive' => false,
							'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'footer' ],
							'skip_for_template'   => [ 'footer' ],
						],
					],
				],
				'footer_widgets_center_content'          => [
					'label'           => esc_html__( 'Center Footer Widgets Content', 'Avada' ),
					'description'     => esc_html__( 'Turn on to center the footer widget content.', 'Avada' ),
					'id'              => 'footer_widgets_center_content',
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
									'condition' => [ '', 'true' ],
									'element'   => '.fusion-footer-widget-area.fusion-widget-area',
									'className' => 'fusion-footer-widget-area-center',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'footer_special_effects'                 => [
					'label'           => 'Footer Special Effects',
					'description'     => esc_html__( 'Select a special effect for the footer area.', 'Avada' ),
					'id'              => 'footer_special_effects',
					'default'         => 'none',
					'type'            => 'radio',
					'choices'         => [
						'none'                    => esc_html__( 'None', 'Avada' ),
						'footer_parallax_effect'  => [
							esc_html__( 'Footer Parallax Effect', 'Avada' ),
							esc_html__( 'This enables a fixed footer with parallax scrolling effect.', 'Avada' ),
						],
						'footer_area_bg_parallax' => [
							esc_html__( 'Parallax Background Image', 'Avada' ),
							esc_html__( 'This enables a parallax effect on the background image selected in "Background Image For Footer Widget Area" field.', 'Avada' ),
						],
						'footer_sticky'           => [
							esc_html__( 'Sticky Footer', 'Avada' ),
							esc_html__( 'This enables a sticky footer. On short pages, the footer will always stick at the bottom, just "above the fold". On long enough pages, it will act just like a normal footer. IMPORTANT: This will not work properly when using a Left or Right Side Header layout and the side header is larger than the viewport.', 'Avada' ),
						],
						'footer_sticky_with_parallax_bg_image' => [
							esc_html__( 'Sticky Footer and Parallax Background Image', 'Avada' ),
							esc_html__( 'This enables a sticky footer together with a parallax effect on the background image. On short pages, the footer will always stick at the bottom, just "above the fold". On long enough pages, it will act just like a normal footer.', 'Avada' ),
						],
					],
					'output'          => [
						[
							'element'       => 'html',
							'property'      => 'height',
							'value_pattern' => '100%',
							'exclude'       => [ 'none', 'footer_parallax_effect', 'footer_area_bg_parallax' ],
						],
						// This is for the avadaSideHeaderVars.footer_special_effects var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'globalVar' => 'avadaSideHeaderVars',
									'id'        => 'footer_special_effects',
									'trigger'   => [ 'fusionSideHeaderScroll' ],
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
									'condition' => [ 'footer_parallax_effect', '===' ],
									'element'   => '.fusion-footer',
									'className' => 'fusion-footer-parallax',
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
									'condition' => [ 'none', '===' ],
									'element'   => 'body',
									'className' => 'avada-footer-fx-none',
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
									'condition' => [ 'footer_parallax_effect', '===' ],
									'element'   => 'body',
									'className' => 'avada-footer-fx-parallax-effect',
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
									'condition' => [ 'footer_area_bg_parallax', '===' ],
									'element'   => 'body',
									'className' => 'avada-footer-fx-bg-parallax',
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
									'condition' => [ 'footer_sticky', '===' ],
									'element'   => 'body',
									'className' => 'avada-footer-fx-sticky',
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
									'condition' => [ 'footer_sticky_with_parallax_bg_image', '===' ],
									'element'   => 'body',
									'className' => 'avada-footer-sticky-with-parallax-bg-image',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
					'partial_refresh' => [
						'footer_content_footer_special_effects' => [
							'selector'            => '.fusion-footer',
							'container_inclusive' => false,
							'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'footer' ],
							'skip_for_template'   => [ 'footer' ],
						],
					],
				],
				'footer_copyright'                       => [
					'label'           => esc_html__( 'Copyright Bar', 'Avada' ),
					'description'     => esc_html__( 'Turn on to display the copyright bar.', 'Avada' ),
					'id'              => 'footer_copyright',
					'default'         => '1',
					'type'            => 'switch',
					'partial_refresh' => [
						'footer_content_footer_copyright' => [
							'selector'            => '.fusion-footer',
							'container_inclusive' => false,
							'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'footer' ],
							'skip_for_template'   => [ 'footer' ],
						],
					],
				],
				'footer_copyright_center_content'        => [
					'label'           => esc_html__( 'Center Copyright Content', 'Avada' ),
					'description'     => esc_html__( 'Turn on to center the copyright bar content.', 'Avada' ),
					'id'              => 'footer_copyright_center_content',
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
									'condition' => [ '', 'true' ],
									'element'   => '.fusion-footer-copyright-area',
									'className' => 'fusion-footer-copyright-center',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'footer_text'                            => [
					'label'             => esc_html__( 'Copyright Text', 'Avada' ),
					'description'       => esc_html__( 'Enter the text that displays in the copyright bar. HTML markup can be used.', 'Avada' ),
					'id'                => 'footer_text',
					/* translators: %1$s: Years. %2$s: Avada Product Link. %3$s: ThemeFusion link. %4$s: WordPress link. */
					'default'           => sprintf( esc_html__( 'Copyright %1$s | %2$s by %3$s | All Rights Reserved | Powered by %4$s', 'Avada' ), '2012 - ' . date( 'Y' ), '<a href="https://bit.ly/2YJkGCr">Avada Website Builder</a>', '<a href="https://theme-fusion.com">ThemeFusion</a>', '<a href="https://wordpress.org">WordPress</a>' ),
					'type'              => 'code',
					'choices'           => [
						'language' => 'html',
						'height'   => 300,
						'theme'    => 'chrome',
					],
					'sanitize_callback' => [ 'Avada_Output_Callbacks', 'unfiltered' ],
					'soft_dependency'   => true,
					'partial_refresh'   => [
						'footer_text' => [
							'selector'            => '.fusion-copyright-notice',
							'container_inclusive' => true,
							'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'copyright' ],
							'skip_for_template'   => [ 'footer' ],
						],
					],
				],
			],
		],
		'footer_background_image_options_subsection' => [
			'label'  => esc_html__( 'Footer Background Image', 'Avada' ),
			'id'     => 'footer_background_image_options_subsection',
			'icon'   => true,
			'type'   => 'sub-section',
			'hidden' => $has_global_footer,
			'fields' => [
				'footer_options_template_bg_notice' => [
					'id'          => 'footer_options_template_bg_notice',
					'label'       => '',
					'description' => class_exists( 'Fusion_Template_Builder' ) ? sprintf(
						/* translators: %1$s: Content|Footer|Page Title Bar. %2$s: Layout selection URL. */
						'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> For more flexibility and a more modern, performant setup, we recommend using the %1$s Builder. To create a custom %1$s Layout, <a href="%2$s" target="_blank">visit this page</a>.', 'Avada' ) . '</div>',
						Fusion_Template_Builder::get_instance()->get_template_terms()['footer']['label'],
						admin_url( 'admin.php?page=avada-layouts' )
					) : '',
					'type'        => 'custom',
				],
				'footerw_bg_image'                  => [
					'label'           => esc_html__( 'Background Image For Footer Widget Area', 'Avada' ),
					'description'     => esc_html__( 'Select an image for the footer widget background. If left empty, the footer background color will be used.', 'Avada' ),
					'id'              => 'footerw_bg_image',
					'default'         => '',
					'mod'             => '',
					'type'            => 'media',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'     => '--footerw_bg_image',
							'choice'   => 'url',
							'element'  => '.fusion-footer-widget-area',
							'callback' => [ 'fallback_to_value', [ 'url("$")', '' ] ],
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
									'element'   => 'body',
									'className' => 'avada-has-footer-widget-bg-image',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'footerw_bg_full'                   => [
					'label'           => esc_html__( '100% Background Image', 'Avada' ),
					'description'     => esc_html__( 'Turn on to have the footer background image display at 100% in width and height according to the window size.', 'Avada' ),
					'id'              => 'footerw_bg_full',
					'default'         => '0',
					'type'            => 'switch',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'     => '--footerw_bg_full-size',
							'element'  => '.fusion-footer-widget-area',
							'callback' => [
								'conditional_return_value',
								[
									'value_pattern' => [ 'cover', 'initial' ],
									'conditions'    => [
										[ 'footerw_bg_full', 'true' ],
									],
								],
							],
						],
						[
							'name'     => '--footerw_bg_full-position',
							'element'  => '.fusion-footer-widget-area',
							'callback' => [
								'conditional_return_value',
								[
									'value_pattern' => [ 'center center', 'var(--footerw_bg_pos)' ],
									'conditions'    => [
										[ 'footerw_bg_full', 'true' ],
									],
								],
							],
						],
					],
				],
				'footerw_bg_repeat'                 => [
					'label'           => esc_html__( 'Background Repeat', 'Avada' ),
					'description'     => esc_html__( 'Controls how the background image repeats.', 'Avada' ),
					'id'              => 'footerw_bg_repeat',
					'default'         => 'no-repeat',
					'type'            => 'select',
					'choices'         => [
						'repeat'    => esc_html__( 'Repeat All', 'Avada' ),
						'repeat-x'  => esc_html__( 'Repeat Horizontally', 'Avada' ),
						'repeat-y'  => esc_html__( 'Repeat Vertically', 'Avada' ),
						'no-repeat' => esc_html__( 'No Repeat', 'Avada' ),
					],
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'    => '--footerw_bg_repeat',
							'element' => '.fusion-footer-widget-area',
						],
					],
				],
				'footerw_bg_pos'                    => [
					'label'           => esc_html__( 'Background Position', 'Avada' ),
					'description'     => esc_html__( 'Controls how the background image is positioned.', 'Avada' ),
					'id'              => 'footerw_bg_pos',
					'default'         => 'center center',
					'type'            => 'select',
					'choices'         => [
						'top left'      => esc_html__( 'top left', 'Avada' ),
						'top center'    => esc_html__( 'top center', 'Avada' ),
						'top right'     => esc_html__( 'top right', 'Avada' ),
						'center left'   => esc_html__( 'center left', 'Avada' ),
						'center center' => esc_html__( 'center center', 'Avada' ),
						'center right'  => esc_html__( 'center right', 'Avada' ),
						'bottom left'   => esc_html__( 'bottom left', 'Avada' ),
						'bottom center' => esc_html__( 'bottom center', 'Avada' ),
						'bottom right'  => esc_html__( 'bottom right', 'Avada' ),
					],
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'    => '--footerw_bg_pos',
							'element' => '.fusion-footer-widget-area',
						],
					],
				],
			],
		],
		'footer_styling_options_subsection'          => [
			'label'  => esc_html__( 'Footer Styling', 'Avada' ),
			'id'     => 'footer_styling_options_subsection',
			'icon'   => true,
			'type'   => 'sub-section',
			'hidden' => $has_global_footer,
			'fields' => [
				'footer_options_template_styling_notice' => [
					'id'          => 'footer_options_template_styling_notice',
					'label'       => '',
					'description' => class_exists( 'Fusion_Template_Builder' ) ? sprintf(
						/* translators: %1$s: Content|Footer|Page Title Bar. %2$s: Layout selection URL. */
						'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> For more flexibility and a more modern, performant setup, we recommend using the %1$s Builder. To create a custom %1$s Layout, <a href="%2$s" target="_blank">visit this page</a>.', 'Avada' ) . '</div>',
						Fusion_Template_Builder::get_instance()->get_template_terms()['footer']['label'],
						admin_url( 'admin.php?page=avada-layouts' )
					) : '',
					'type'        => 'custom',
				],
				'footer_100_width'                       => [
					'label'           => esc_html__( '100% Footer Width', 'Avada' ),
					'description'     => esc_html__( 'Turn on to have the footer area display at 100% width according to the window size. Turn off to follow site width.', 'Avada' ),
					'id'              => 'footer_100_width',
					'default'         => '0',
					'type'            => 'switch',
					'class'           => 'fusion-or-gutter',
					'soft_dependency' => true,
					'output'          => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'true' ],
									'element'   => 'body',
									'className' => 'avada-has-100-footer',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'footer_area_padding'                    => [
					'label'           => esc_html__( 'Footer Padding', 'Avada' ),
					'description'     => esc_html__( 'Controls the top/right/bottom/left padding for the footer.', 'Avada' ),
					'id'              => 'footer_area_padding',
					'choices'         => [
						'top'    => true,
						'bottom' => true,
						'left'   => true,
						'right'  => true,
					],
					'default'         => [
						'top'    => '60px',
						'bottom' => '64px',
						'left'   => '0px',
						'right'  => '0px',
					],
					'type'            => 'spacing',
					'class'           => 'fusion-or-gutter',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'    => '--footer_area_padding-top',
							'element' => '.fusion-footer',
							'choice'  => 'top',
						],
						[
							'name'    => '--footer_area_padding-bottom',
							'element' => '.fusion-footer',
							'choice'  => 'bottom',
						],
						[
							'name'    => '--footer_area_padding-left',
							'element' => '.fusion-footer',
							'choice'  => 'left',
						],
						[
							'name'    => '--footer_area_padding-right',
							'element' => '.fusion-footer',
							'choice'  => 'right',
						],
					],
				],
				'footer_bg_color'                        => [
					'label'           => esc_html__( 'Footer Background Color', 'Avada' ),
					'description'     => esc_html__( 'Controls the background color of the footer.', 'Avada' ),
					'id'              => 'footer_bg_color',
					'default'         => 'var(--awb-color7)',
					'type'            => 'color-alpha',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'     => '--footer_bg_color',
							'element'  => '.fusion-footer-widget-area',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'footer_border_size'                     => [
					'label'           => esc_html__( 'Footer Border Size', 'Avada' ),
					'description'     => esc_html__( 'Controls the size of the top footer border.', 'Avada' ),
					'id'              => 'footer_border_size',
					'default'         => '0',
					'type'            => 'slider',
					'choices'         => [
						'min'  => '0',
						'max'  => '50',
						'step' => '1',
					],
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'          => '--footer_border_size',
							'element'       => '.fusion-footer-widget-area',
							'value_pattern' => '$px',
						],
					],
				],
				'footer_border_color'                    => [
					'label'           => esc_html__( 'Footer Border Color', 'Avada' ),
					'description'     => esc_html__( 'Controls the border colors of the footer.', 'Avada' ),
					'id'              => 'footer_border_color',
					'default'         => 'var(--awb-color3)',
					'type'            => 'color-alpha',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'     => '--footer_border_color',
							'element'  => '.fusion-footer-widget-area',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'footer_divider_line'                    => [
					'label'           => esc_html__( 'Footer Widgets Area Vertical Divider Line', 'Avada' ),
					'description'     => esc_html__( 'Turn on to have the footer widget area display vertical divider line between columns.', 'Avada' ),
					'id'              => 'footer_divider_line',
					'default'         => '0',
					'type'            => 'switch',
					'class'           => 'fusion-or-gutter',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'     => '--footer_divider_line-flex',
							'element'  => '.fusion-footer',
							'callback' => [
								'conditional_return_value',
								[
									'value_pattern' => [ 'flex', 'block' ],
									'conditions'    => [
										[ 'footer_divider_line', 'true' ],
									],
								],
							],
						],
					],
					'partial_refresh' => [
						'footer_divider_line_partial' => [
							'selector'            => '.fusion-footer',
							'container_inclusive' => false,
							'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'footer' ],
							'skip_for_template'   => [ 'footer' ],
						],
					],
				],
				'footer_divider_line_size'               => [
					'label'           => esc_html__( 'Footer Widgets Area Vertical Divider Line Size', 'Avada' ),
					'description'     => esc_html__( 'Controls the size of the vertical divider line between footer widget area columns.', 'Avada' ),
					'id'              => 'footer_divider_line_size',
					'default'         => '1',
					'type'            => 'slider',
					'choices'         => [
						'min'  => '0',
						'max'  => '50',
						'step' => '1',
					],
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'          => '--footer_divider_line_size',
							'element'       => '.fusion-footer',
							'value_pattern' => '$px',
						],
					],
				],
				'footer_divider_line_style'              => [
					'label'           => esc_html__( 'Footer Widgets Area Vertical Divider Line Style', 'Avada' ),
					'description'     => esc_html__( 'Controls the style of the vertical divider line between footer widget area columns.', 'Avada' ),
					'id'              => 'footer_divider_line_style',
					'default'         => 'solid',
					'choices'         => [
						'none'   => esc_html__( 'None', 'fusion-builder' ),
						'solid'  => esc_html__( 'Solid', 'fusion-builder' ),
						'dashed' => esc_html__( 'Dashed', 'fusion-builder' ),
						'dotted' => esc_html__( 'Dotted', 'fusion-builder' ),
						'double' => esc_html__( 'Double', 'fusion-builder' ),
						'groove' => esc_html__( 'Groove', 'fusion-builder' ),
						'ridge'  => esc_html__( 'Ridge', 'fusion-builder' ),
					],
					'type'            => 'select',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'    => '--footer_divider_line_style',
							'element' => '.fusion-footer',
						],
					],
				],
				'footer_divider_color'                   => [
					'label'           => esc_html__( 'Footer Widget Divider Color', 'Avada' ),
					'description'     => esc_html__( 'Controls the divider color in the footer widgets and also the vertical divider lines between widget areas.', 'Avada' ),
					'id'              => 'footer_divider_color',
					'default'         => 'var(--awb-color6)',
					'type'            => 'color-alpha',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'     => '--footer_divider_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'footer_widgets_padding'                 => [
					'label'           => esc_html__( 'Footer Widgets Area Padding', 'Avada' ),
					'description'     => esc_html__( 'Controls the right/left padding for the footer widget areas.', 'Avada' ),
					'id'              => 'footer_widgets_padding',
					'default'         => '16px',
					'type'            => 'dimension',
					'choices'         => [
						'units' => [ 'px', '%' ],
					],
					'class'           => 'fusion-or-gutter',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'    => '--footer_widgets_padding',
							'element' => '.fusion-footer',
						],
					],
				],
				'copyright_padding'                      => [
					'label'           => esc_html__( 'Copyright Padding', 'Avada' ),
					'description'     => esc_html__( 'Controls the top/bottom padding for the copyright area.', 'Avada' ),
					'id'              => 'copyright_padding',
					'default'         => [
						'top'    => '20px',
						'bottom' => '20px',
					],
					'choices'         => [
						'top'    => true,
						'bottom' => true,
					],
					'type'            => 'spacing',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'    => '--copyright_padding-top',
							'element' => '.fusion-footer-copyright-area',
							'choice'  => 'top',
						],
						[
							'name'    => '--copyright_padding-bottom',
							'element' => '.fusion-footer-copyright-area',
							'choice'  => 'bottom',
						],
					],
				],
				'copyright_bg_color'                     => [
					'label'           => esc_html__( 'Copyright Background Color', 'Avada' ),
					'description'     => esc_html__( 'Controls the background color of the footer copyright area.', 'Avada' ),
					'id'              => 'copyright_bg_color',
					'default'         => 'var(--awb-color8)',
					'type'            => 'color-alpha',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'     => '--copyright_bg_color',
							'element'  => '.fusion-footer-copyright-area',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'copyright_border_size'                  => [
					'label'           => esc_html__( 'Copyright Border Size', 'Avada' ),
					'description'     => esc_html__( 'Controls the size of the top copyright border.', 'Avada' ),
					'id'              => 'copyright_border_size',
					'default'         => '0',
					'type'            => 'slider',
					'choices'         => [
						'min'  => '0',
						'max'  => '50',
						'step' => '1',
					],
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'          => '--copyright_border_size',
							'element'       => '.fusion-footer-copyright-area',
							'value_pattern' => '$px',
						],
					],
				],
				'copyright_border_color'                 => [
					'label'           => esc_html__( 'Copyright Border Color', 'Avada' ),
					'description'     => esc_html__( 'Controls the border colors for the footer copyright area.', 'Avada' ),
					'id'              => 'copyright_border_color',
					'default'         => 'var(--awb-color8)',
					'type'            => 'color-alpha',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'     => '--copyright_border_color',
							'element'  => '.fusion-footer-copyright-area',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'footer_typography_info'                 => [
					'label'       => esc_html__( 'Footer Typography', 'Avada' ),
					'description' => '',
					'id'          => 'footer_typography_info',
					'type'        => 'info',
				],
				'footer_headings_typography'             => [
					'id'                        => 'footer_headings_typography',
					'label'                     => esc_html__( 'Footer Headings Typography', 'Avada' ),
					'description'               => esc_html__( 'These settings control the typography for the footer headings.', 'Avada' ),
					'global'                    => true,
					'type'                      => 'typography',
					'text_transform_no_inherit' => true,
					'choices'                   => [
						'font-family'    => true,
						'font-size'      => true,
						'font-weight'    => true,
						'line-height'    => true,
						'letter-spacing' => true,
						'color'          => true,
						'text-transform' => true,
					],
					'default'                   => [
						'font-family'    => 'var(--awb-typography2-font-family)',
						'font-size'      => 'var(--awb-typography2-font-size)',
						'font-weight'    => '600',
						'line-height'    => 'var(--awb-typography2-line-height)',
						'letter-spacing' => 'var(--awb-typography2-letter-spacing)',
						'color'          => 'var(--awb-color1)',
						'text-transform' => 'none',
					],
					'soft_dependency'           => true,
					'css_vars'                  => [
						[
							'name'     => '--footer_headings_typography-font-family',
							'choice'   => 'font-family',
							'callback' => [ 'combined_font_family', 'footer_headings_typography' ],
						],
						[
							'name'   => '--footer_headings_typography-font-size',
							'choice' => 'font-size',
						],
						[
							'name'     => '--footer_headings_typography-font-weight',
							'choice'   => 'font-weight',
							'callback' => [ 'font_weight_no_regular', '' ],
						],
						[
							'name'   => '--footer_headings_typography-line-height',
							'choice' => 'line-height',
						],
						[
							'name'   => '--footer_headings_typography-font-style',
							'choice' => 'font-style',
						],
						[
							'name'     => '--footer_headings_typography-letter-spacing',
							'choice'   => 'letter-spacing',
							'callback' => [ 'maybe_append_px', '' ],
						],
						[
							'name'   => '--footer_headings_typography-color',
							'choice' => 'color',
						],
						[
							'name'   => '--footer_headings_typography-text-transform',
							'choice' => 'text-transform',
						],
					],
				],
				'footer_text_color'                      => [
					'label'           => esc_html__( 'Footer Font Color', 'Avada' ),
					'description'     => esc_html__( 'Controls the text color of the footer font.', 'Avada' ),
					'id'              => 'footer_text_color',
					'default'         => 'hsla(var(--awb-color1-h),var(--awb-color1-s),var(--awb-color1-l),calc(var(--awb-color1-a) - 40%))',
					'type'            => 'color-alpha',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'     => '--footer_text_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'footer_link_color'                      => [
					'label'           => esc_html__( 'Footer Link Color', 'Avada' ),
					'description'     => esc_html__( 'Controls the text color of the footer link font.', 'Avada' ),
					'id'              => 'footer_link_color',
					'default'         => 'hsla(var(--awb-color1-h),var(--awb-color1-s),var(--awb-color1-l),calc(var(--awb-color1-a) - 20%))',
					'type'            => 'color-alpha',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'     => '--footer_link_color',
							'element'  => '.fusion-footer-widget-area',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'footer_link_color_hover'                => [
					'label'           => esc_html__( 'Footer Link Hover Color', 'Avada' ),
					'description'     => esc_html__( 'Controls the text hover color of the footer link font.', 'Avada' ),
					'id'              => 'footer_link_color_hover',
					'default'         => 'var(--awb-color4)',
					'type'            => 'color-alpha',
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'     => '--footer_link_color_hover',
							'element'  => '.fusion-footer-widget-area',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'copyright_text_color'                   => [
					'label'       => esc_html__( 'Copyright Text Color', 'Avada' ),
					'description' => esc_html__( 'Controls the text color of the footer copyright area.', 'Avada' ),
					'id'          => 'copyright_text_color',
					'default'     => 'hsla(var(--awb-color1-h),var(--awb-color1-s),var(--awb-color1-l),calc(var(--awb-color1-a) - 60%))',
					'type'        => 'color-alpha',
					'required'    => [
						[
							'setting'  => 'footer_copyright',
							'operator' => '==',
							'value'    => '1',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--copyright_text_color',
							'element'  => '.fusion-copyright-notice',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'copyright_link_color'                   => [
					'label'       => esc_html__( 'Copyright Link Color', 'Avada' ),
					'description' => esc_html__( 'Controls the link color of the footer copyright area.', 'Avada' ),
					'id'          => 'copyright_link_color',
					'default'     => 'hsla(var(--awb-color1-h),var(--awb-color1-s),var(--awb-color1-l),calc(var(--awb-color1-a) - 20%))',
					'type'        => 'color-alpha',
					'required'    => [
						[
							'setting'  => 'footer_copyright',
							'operator' => '==',
							'value'    => '1',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--copyright_link_color',
							'element'  => '.fusion-copyright-notice',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'copyright_link_color_hover'             => [
					'label'       => esc_html__( 'Copyright Link Hover Color', 'Avada' ),
					'description' => esc_html__( 'Controls the link hover color of the footer copyright area.', 'Avada' ),
					'id'          => 'copyright_link_color_hover',
					'default'     => 'var(--awb-color4)',
					'type'        => 'color-alpha',
					'required'    => [
						[
							'setting'  => 'footer_copyright',
							'operator' => '==',
							'value'    => '1',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--copyright_link_color_hover',
							'element'  => '.fusion-footer-copyright-area',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'copyright_font_size'                    => [
					'label'           => esc_html__( 'Copyright Font Size', 'Avada' ),
					'description'     => esc_html__( 'Controls the font size for the copyright text.', 'Avada' ),
					'id'              => 'copyright_font_size',
					'default'         => '13px',
					'type'            => 'dimension',
					'choices'         => [
						'units' => [ 'px', 'em' ],
					],
					'soft_dependency' => true,
					'css_vars'        => [
						[
							'name'    => '--copyright_font_size',
							'element' => '.fusion-copyright-notice',
						],
					],
				],
			],
		],
	];

	$sections['footer']['fields'] = array_merge( $sections['footer']['fields'], $fields );

	return $sections;

}

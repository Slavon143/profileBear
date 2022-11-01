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
 * Advanced settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_advanced( $sections ) {

	$sections['advanced'] = [
		'label'    => esc_html__( 'Advanced', 'Avada' ),
		'id'       => 'heading_advanced',
		'is_panel' => true,
		'priority' => 25,
		'icon'     => 'el-icon-puzzle',
		'alt_icon' => 'fusiona-dashboard',
		'fields'   => [
			'theme_features_section'     => [
				'label'  => esc_html__( 'Features', 'Avada' ),
				'id'     => 'theme_features_section',
				'icon'   => true,
				'type'   => 'sub-section',
				'fields' => [
					'enable_language_updates'            => [
						'label'       => esc_html__( 'Enable Language Updates', 'Avada' ),
						'description' => esc_html__( 'If your site is using a language other than English, enabling this option will allow you to get updated language files for your locale as soon as they are available.', 'Avada' ),
						'id'          => 'enable_language_updates',
						'default'     => '1',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'dependencies_status'                => [
						'label'       => esc_html__( "Avada's Option Network Dependencies", 'Avada' ),
						'description' => esc_html__( "Avada's Option Network consists of Global Options, Page Options and Builder Options and each of them have dependent options ON by default. This means the only options you see are the only ones currently available for your selection. However, if you wish to disable this feature, simply turn this option off, and all dependencies will be disabled (requires save & refresh).", 'Avada' ),
						'id'          => 'dependencies_status',
						'default'     => '1',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'disable_code_block_encoding'        => [
						'label'       => esc_html__( 'Code Block Encoding', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable encoding in the Avada Builder code block and syntax highlighting elements.', 'Avada' ),
						'id'          => 'disable_code_block_encoding',
						'default'     => '1',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'disable_megamenu'                   => [
						'label'           => esc_html__( 'Mega Menu', 'Avada' ),
						'description'     => esc_html__( 'Turn on to enable Avada\'s mega menu.', 'Avada' ),
						'id'              => 'disable_megamenu',
						'default'         => '1',
						'type'            => 'switch',
						'partial_refresh' => [
							'theme_features_disable_megamenu' => [
								'selector'              => '.fusion-header-wrapper',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
								'success_trigger_event' => 'header-rendered',
							],
						],
					],
					'status_avada_studio'                => [
						'label'       => esc_html__( 'Avada Studio', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable Avada studio.', 'Avada' ),
						'id'          => 'status_avada_studio',
						'default'     => '1',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'avada_rev_styles'                   => [
						'label'       => esc_html__( 'Avada Styles For Slider Revolution', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the Avada styles and use the default Slider Revolution styles.', 'Avada' ),
						'id'          => 'avada_rev_styles',
						'default'     => '1',
						'type'        => 'switch',
						'css_vars'    => [
							[
								'name'          => '--avada-rev-image-shadow-top',
								'value_pattern' => 'url("' . Fusion_Sanitize::css_asset_url( Avada::$template_dir_url . '/assets/images/shadow-top.png' ) . '")',
								'element'       => '.shadow-left',
							],
							[
								'name'          => '--avada-rev-image-shadow-bottom',
								'value_pattern' => 'url("' . Fusion_Sanitize::css_asset_url( Avada::$template_dir_url . '/assets/images/shadow-bottom.png' ) . '")',
								'element'       => '.shadow-right',
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
										'className' => 'avada-has-rev-slider-styles',
									],

								],
								'sanitize_callback' => '__return_empty_string',
							],

							// Change the avadaRevVars.avada_rev_styles var.
							[
								'element'           => 'helperElement',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'avadaRevVars',
										'id'        => 'avada_rev_styles',
										'trigger'   => [ 'DestoryRevStyle', 'AddRevStyles' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'avada_styles_dropdowns'             => [
						'label'       => esc_html__( 'Avada Dropdown Styles', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the Avada styles for dropdown/select fields site wide. This should be done if you experience any issues with 3rd party plugin dropdowns.', 'Avada' ),
						'id'          => 'avada_styles_dropdowns',
						'default'     => '1',
						'type'        => 'switch',
						'output'      => [
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ '', 'true' ],
										'element'   => 'body',
										'className' => 'avada-dropdown-styles',
									],

								],
								'sanitize_callback' => '__return_empty_string',
							],

							// Change the avadaSelectVars.avada_drop_down var.
							[
								'element'           => 'helperElement',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'avadaSelectVars',
										'id'        => 'avada_drop_down',
										'trigger'   => [ 'DestoryAvadaSelect', 'AddAvadaSelect' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'disable_mobile_image_hovers'        => [
						'label'       => esc_html__( 'CSS Image Hover Animations on Mobiles', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable CSS image hover animations on mobiles.', 'Avada' ),
						'id'          => 'disable_mobile_image_hovers',
						'default'     => '1',
						'type'        => 'switch',
						'output'      => [
							[
								'element'           => 'helperElement',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'avadaMobileImageVars',
										'id'        => 'disable_mobile_image_hovers',
										'trigger'   => [ 'fusionDeactivateMobileImagHovers' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'status_yt'                          => [
						'label'       => esc_html__( 'YouTube API Scripts', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable YouTube API scripts.', 'Avada' ),
						'id'          => 'status_yt',
						'default'     => '1',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
						'output'      => [
							// This is for the fusionVideoBgVars.status_yt var.
							[
								'element'           => 'helperElement',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'fusionVideoBgVars',
										'id'        => 'status_yt',
										'trigger'   => [ 'load' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
							[
								'element'           => 'helperElement',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'fusionVideoGeneralVars',
										'id'        => 'status_yt',
										'trigger'   => [ 'load' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
							[
								'element'           => 'helperElement',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'fusionFlexSliderVars',
										'id'        => 'status_yt',
										'trigger'   => [ 'load' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
							[
								'element'           => 'helperElement',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'fusionBlogVars',
										'id'        => 'status_yt',
										'trigger'   => [ 'load' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'status_vimeo'                       => [
						'label'       => esc_html__( 'Vimeo API Scripts', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable Vimeo API scripts.', 'Avada' ),
						'id'          => 'status_vimeo',
						'default'     => '1',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
						'output'      => [
							// This is for the fusionVideoBgVars.status_vimeo var.
							[
								'element'           => 'helperElement',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'fusionVideoBgVars',
										'id'        => 'status_vimeo',
										'trigger'   => [ 'load' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
							[
								'element'           => 'helperElement',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'fusionVideoGeneralVars',
										'id'        => 'status_vimeo',
										'trigger'   => [ 'load' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
							[
								'element'           => 'helperElement',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'fusionFlexSliderVars',
										'id'        => 'status_vimeo',
										'trigger'   => [ 'load' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'status_gmap'                        => [
						'label'       => esc_html__( 'Google Map Scripts', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable google map.', 'Avada' ),
						'id'          => 'status_gmap',
						'default'     => '1',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'status_totop'                       => [
						'label'       => esc_html__( 'ToTop Script', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the ToTop script which adds the scrolling to top functionality.', 'Avada' ),
						'id'          => 'status_totop',
						'default'     => 'desktop',
						'type'        => 'radio-buttonset',
						'choices'     => [
							'desktop_and_mobile' => esc_html__( 'Desktop & Mobile', 'Avada' ),
							'desktop'            => esc_html__( 'Desktop', 'Avada' ),
							'mobile'             => esc_html__( 'Mobile', 'Avada' ),
							'off'                => esc_html__( 'Off', 'Avada' ),
						],
						'output'      => [
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ 'desktop', 'does-not-contain' ],
										'element'   => 'body',
										'className' => 'no-desktop-totop',
									],

								],
								'sanitize_callback' => '__return_empty_string',
							],
							[
								'element'           => 'helperElement',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'avadaToTopVars',
										'id'        => 'status_totop',
										'trigger'   => [ 'scroll' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'status_fontawesome'                 => [
						'label'       => esc_html__( 'Font Awesome', 'Avada' ),
						'description' => esc_html__( 'Choose which Font Awesome icon subsets you want to load. Note that Light subset can only be used if Font Awesome Pro is enabled.', 'Avada' ),
						'id'          => 'status_fontawesome',
						'default'     => [ 'fab', 'far', 'fas' ],
						'type'        => 'select',
						'multi'       => true,
						'choices'     => [
							'fab' => esc_html__( 'Brands', 'Avada' ),
							'far' => esc_html__( 'Regular', 'Avada' ),
							'fas' => esc_html__( 'Solid', 'Avada' ),
							'fal' => esc_html__( 'Light', 'Avada' ),
						],
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'fontawesome_v4_compatibility'       => [
						'label'       => esc_html__( 'Font Awesome v4 Compatibility', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable support for Font Awesome 4 icon code format.', 'Avada' ),
						'id'          => 'fontawesome_v4_compatibility',
						'default'     => '0',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'status_fontawesome_pro'             => [
						'label'       => esc_html__( 'Font Awesome Pro', 'Avada' ),
						/* translators: %1$s = license text & link. %2$s: whitelist text & link. */
						'description' => sprintf( esc_html__( 'Font Awesome Pro %1$s is required and you need to %2$s your domain.', 'Avada' ), '<a href="https://fontawesome.com/buy/standard" target="_blank" rel="noopener noreferrer">license</a>', '<a href="https://fontawesome.com/account/cdn" target="_blank" rel="noopener noreferrer">whitelist</a>' ),
						'id'          => 'status_fontawesome_pro',
						'default'     => '0',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'status_outline'                     => [
						'label'       => esc_html__( 'CSS Outlines', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable browser specific CSS element outlines used to improve accessibility.', 'Avada' ),
						'id'          => 'status_outline',
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
										'element'   => 'body',
										'className' => 'fusion-disable-outline',
									],

								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'status_opengraph'                   => [
						'label'       => esc_html__( 'Open Graph Meta Tags', 'Avada' ),
						'description' => __( 'Turn on to enable open graph meta tags which are mainly used when sharing pages on social networking sites like Facebook. <strong>IMPORTANT:</strong> Some optimization plugins, like e.g. Yoast SEO, add their own implementation of this, and if you want to use that, this option should be disabled.', 'Avada' ),
						'id'          => 'status_opengraph',
						'default'     => '1',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'disable_date_rich_snippet_pages'    => [
						'label'       => esc_html__( 'Rich Snippets', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable rich snippets data site wide. If set to "On", you can also control individual items below. If set to "Off" all items will be disabled.', 'Avada' ),
						'id'          => 'disable_date_rich_snippet_pages',
						'default'     => '1',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'disable_rich_snippet_title'         => [
						'label'       => esc_html__( 'Rich Snippets Title', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable title rich snippet data site wide.', 'Avada' ),
						'id'          => 'disable_rich_snippet_title',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => [
							[
								'setting'  => 'disable_date_rich_snippet_pages',
								'operator' => '==',
								'value'    => '1',
							],
						],
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'disable_rich_snippet_author'        => [
						'label'       => esc_html__( 'Rich Snippets Author Info', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable author rich snippet data site wide.', 'Avada' ),
						'id'          => 'disable_rich_snippet_author',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => [
							[
								'setting'  => 'disable_date_rich_snippet_pages',
								'operator' => '==',
								'value'    => '1',
							],
						],
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'disable_rich_snippet_date'          => [
						'label'       => esc_html__( 'Rich Snippets Last Update Date', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable udate date rich snippet data site wide.', 'Avada' ),
						'id'          => 'disable_rich_snippet_date',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => [
							[
								'setting'  => 'disable_date_rich_snippet_pages',
								'operator' => '==',
								'value'    => '1',
							],
						],
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'disable_rich_snippet_faq'           => [
						'label'       => esc_html__( 'Rich Snippets FAQ', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the FAQ rich snippet data site wide.', 'Avada' ),
						'id'          => 'disable_rich_snippet_faq',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => [
							[
								'setting'  => 'disable_date_rich_snippet_pages',
								'operator' => '==',
								'value'    => '1',
							],
						],
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'enable_block_editor_backend_styles' => [
						'label'       => esc_html__( 'Enable WP Block Editor Backend Styles', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable Avada\'s backend style support for the WP block editor.', 'Avada' ),
						'id'          => 'enable_block_editor_backend_styles',
						'default'     => '1',
						'type'        => 'switch',

						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'load_block_styles'                  => [
						'label'       => esc_html__( 'Load Frontend Block Styles', 'Avada' ),
						'description' => esc_html__( 'Select "Auto" to automatically detect if there are blocks present in your page, and load block-styles in the footer.', 'Avada' ),
						'id'          => 'load_block_styles',
						'default'     => 'on',
						'type'        => 'radio-buttonset',
						'choices'     => [
							'auto' => esc_html__( 'Auto', 'Avada' ),
							'on'   => esc_html__( 'On', 'Avada' ),
							'off'  => esc_html__( 'Off', 'Avada' ),
						],
						'transport'   => 'refresh',
					],
				],
			],
			'tracking_head_body_section' => [
				'label'  => esc_html__( 'Code Fields (Tracking etc.)', 'Avada' ),
				'id'     => 'tracking_head_body_section',
				'icon'   => true,
				'type'   => 'sub-section',
				'fields' => [
					'google_analytics' => [
						'label'       => esc_html__( 'Tracking Code', 'Avada' ),
						'description' => esc_html__( 'Paste your tracking code here. This will be added into the header template of your theme. Place code inside &lt;script&gt; tags.', 'Avada' ),
						'id'          => 'google_analytics',
						'default'     => '',
						'type'        => 'code',
						'choices'     => [
							'language' => 'html',
							'height'   => 300,
							'theme'    => 'chrome',
						],
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'space_head'       => [
						'label'       => esc_html__( 'Space before &lt;/head&gt;', 'Avada' ),
						'description' => esc_html__( 'Only accepts JavaScript code wrapped with &lt;script&gt; tags and HTML markup that is valid inside the &lt;/head&gt; tag.', 'Avada' ),
						'id'          => 'space_head',
						'default'     => '',
						'type'        => 'code',
						'choices'     => [
							'language' => 'html',
							'height'   => 350,
							'theme'    => 'chrome',
						],
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'space_body'       => [
						'label'       => esc_html__( 'Space before &lt;/body&gt;', 'Avada' ),
						'description' => esc_html__( 'Only accepts JavaScript code, wrapped with &lt;script&gt; tags and valid HTML markup inside the &lt;/body&gt; tag.', 'Avada' ),
						'id'          => 'space_body',
						'default'     => '',
						'type'        => 'code',
						'choices'     => [
							'language' => 'html',
							'height'   => 350,
							'theme'    => 'chrome',
						],
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
				],
			],
			'post_types_section'         => [
				'label'  => esc_html__( 'Post Types', 'Avada' ),
				'id'     => 'post_types_section',
				'icon'   => true,
				'type'   => 'sub-section',
				'fields' => [
					'status_fusion_slider'    => [
						'label'       => esc_html__( 'Avada Slider', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the Avada Slider.', 'Avada' ),
						'id'          => 'status_fusion_slider',
						'default'     => '1',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'status_eslider'          => [
						'label'       => esc_html__( 'Elastic Slider', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the elastic slider.', 'Avada' ),
						'id'          => 'status_eslider',
						'default'     => '0',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'status_fusion_forms'     => [
						'label'       => esc_html__( 'Avada Forms', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the Avada Forms.', 'Avada' ),
						'id'          => 'status_fusion_forms',
						'default'     => '1',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'status_awb_Off_Canvas'   => [
						'label'       => esc_html__( 'Avada Off Canvas', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the Avada Off Canvas.', 'Avada' ),
						'id'          => 'status_awb_Off_Canvas',
						'default'     => '1',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'status_fusion_portfolio' => [
						'label'       => esc_html__( 'Avada Portfolio', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the Avada Portfolio.', 'Avada' ),
						'id'          => 'status_fusion_portfolio',
						'default'     => '1',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
					'status_fusion_faqs'      => [
						'label'       => esc_html__( 'Avada FAQs', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the Avada Faqs.', 'Avada' ),
						'id'          => 'status_fusion_faqs',
						'default'     => '1',
						'type'        => 'switch',
						// No need to refresh the page.
						'transport'   => 'postMessage',
					],
				],
			],
		],
	];

	return $sections;

}

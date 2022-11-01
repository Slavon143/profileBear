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
function avada_options_section_sliding_bar( $sections ) {

	$sections['sliding_bar'] = [
		'label'    => esc_html__( 'Sliding Bar', 'Avada' ),
		'id'       => 'heading_sliding_bar',
		'priority' => 8,
		'icon'     => 'el-icon-chevron-down',
		'alt_icon' => 'fusiona-arrow-down',
		'fields'   => [
			'slidingbar_widgets'           => [
				'label'           => esc_html__( 'Sliding Bar on Desktops', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display the sliding bar on desktops.', 'Avada' ),
				'id'              => 'slidingbar_widgets',
				'default'         => '0',
				'type'            => 'switch',
				'edit_shortcut'   => [
					'selector'  => [ '.fusion-sliding-bar-area' ],
					'shortcuts' => [
						[
							'aria_label' => esc_html__( 'Edit Sliding Bar', 'Avada' ),
							'icon'       => 'fusiona-arrow-down',
						],
						[
							'aria_label' => esc_html__( 'Edit Sliding Bar Widgets', 'Avada' ),
							'link'       => admin_url( 'widgets.php' ),
						],
					],
				],
				'partial_refresh' => [
					'sliding_bar_content_slidingbar_widgets' => [
						'selector'            => '.fusion-sliding-bar-wrapper',
						'container_inclusive' => false,
						'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'sliding_bar' ],
					],
					'header_content_slidingbar_widgets' => [
						'selector'              => '.fusion-header-wrapper',
						'container_inclusive'   => true,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
						'success_trigger_event' => 'header-rendered',
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
								'className' => 'avada-has-slidingbar-widgets',
							],

						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'mobile_slidingbar_widgets'    => [
				'label'       => esc_html__( 'Sliding Bar On Mobile', 'Avada' ),
				'description' => __( 'Turn on to display the sliding bar on mobiles. <strong>IMPORTANT:</strong> Due to mobile screen sizes and overlapping issues, when this option is enabled the triangle toggle style in the top right position will be forced for square and circle desktop styles.', 'Avada' ),
				'id'          => 'mobile_slidingbar_widgets',
				'default'     => '0',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
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
								'className' => 'no-mobile-slidingbar',
							],

						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'slidingbar_open_on_load'      => [
				'label'       => esc_html__( 'Sliding Bar Open On Page Load', 'Avada' ),
				'description' => esc_html__( 'Turn on to have the sliding bar open when the page loads.', 'Avada' ),
				'id'          => 'slidingbar_open_on_load',
				'default'     => '0',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
				// No need to refresh the page.
				'transport'   => 'postMessage',
			],
			'slidingbar_position'          => [
				'label'           => esc_html__( 'Sliding Bar Position', 'Avada' ),
				'description'     => esc_html__( 'Controls the position of the sliding bar to be in the top, right, bottom or left of the site.', 'Avada' ),
				'id'              => 'slidingbar_position',
				'default'         => 'top',
				'type'            => 'radio-buttonset',
				'choices'         => [
					'top'    => esc_html__( 'Top', 'Avada' ),
					'right'  => esc_html__( 'Right', 'Avada' ),
					'bottom' => esc_html__( 'Bottom', 'Avada' ),
					'left'   => esc_html__( 'Left', 'Avada' ),
				],
				'required'        => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'partial_refresh' => [
					'sliding_bar_content_slidingbar_position' => [
						'selector'            => '.fusion-sliding-bar-wrapper',
						'container_inclusive' => false,
						'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'sliding_bar' ],
					],
				],
				'output'          => [
					// Change classes in <body>.
					[
						'element'       => 'body',
						'function'      => 'attr',
						'attr'          => 'class',
						'value_pattern' => 'avada-has-slidingbar-position-$',
						'remove_attrs'  => [ 'avada-has-slidingbar-position-top', 'avada-has-slidingbar-position-right', 'avada-has-slidingbar-position-bottom', 'avada-has-slidingbar-position-left' ],
					],
				],
			],
			'slidingbar_width'             => [
				'label'       => esc_html__( 'Sliding Bar Width', 'Avada' ),
				'description' => esc_html__( 'Controls the width of the sliding bar on left/right layouts.', 'Avada' ),
				'id'          => 'slidingbar_width',
				'default'     => '300px',
				'type'        => 'dimension',
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
					[
						'setting'  => 'slidingbar_position',
						'operator' => '!=',
						'value'    => 'top',
					],
					[
						'setting'  => 'slidingbar_position',
						'operator' => '!=',
						'value'    => 'bottom',
					],
				],
				'css_vars'    => [
					[
						'name' => '--slidingbar_width',
					],
					[
						'name'     => '--slidingbar_width-percent_to_vw',
						'callback' => [ 'string_replace', [ '%', 'vw' ] ],
					],
				],
				'output'      => [
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'callback'          => [
							'toggle_class',
							[
								'condition' => [ '%', 'contains' ],
								'element'   => 'body',
								'className' => 'avada-has-slidingbar-width-percent',
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'slidingbar_sticky'            => [
				'label'       => esc_html__( 'Sticky Sliding Bar', 'Avada' ),
				'description' => esc_html__( 'Turn on to enable a sticky sliding bar.', 'Avada' ),
				'id'          => 'slidingbar_sticky',
				'default'     => 1,
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
					[
						'setting'  => 'slidingbar_position',
						'operator' => '!=',
						'value'    => 'right',
					],
					[
						'setting'  => 'slidingbar_position',
						'operator' => '!=',
						'value'    => 'left',
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
								'className' => 'avada-has-slidingbar-sticky',
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
								'element'   => '#slidingbar-area',
								'className' => 'fusion-sliding-bar-sticky',
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'slidingbar_widgets_columns'   => [
				'label'           => esc_html__( 'Number of Sliding Bar Columns', 'Avada' ),
				'description'     => esc_html__( 'Controls the number of columns in the sliding bar.', 'Avada' ),
				'id'              => 'slidingbar_widgets_columns',
				'default'         => '2',
				'type'            => 'slider',
				'choices'         => [
					'min'  => '1',
					'max'  => '6',
					'step' => '1',
				],
				'required'        => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'partial_refresh' => [
					'sliding_bar_content_slidingbar_widgets_columns' => [
						'selector'            => '.fusion-sliding-bar-wrapper',
						'container_inclusive' => false,
						'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'sliding_bar' ],
					],
				],
			],
			'slidingbar_column_alignment'  => [
				'label'           => esc_html__( 'Sliding Bar Column Alignment', 'Avada' ),
				'description'     => esc_html__( 'Allows your sliding bar columns to be stacked (one above the other) or floated (side by side) when using the left or right position.', 'Avada' ),
				'id'              => 'slidingbar_column_alignment',
				'default'         => 'stacked',
				'type'            => 'radio-buttonset',
				'choices'         => [
					'stacked' => esc_html__( 'Stacked', 'Avada' ),
					'floated' => esc_html__( 'Floated', 'Avada' ),
				],
				'required'        => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
					[
						'setting'  => 'slidingbar_position',
						'operator' => '!=',
						'value'    => 'top',
					],
					[
						'setting'  => 'slidingbar_position',
						'operator' => '!=',
						'value'    => 'bottom',
					],
				],
				'partial_refresh' => [
					'sliding_bar_content_slidingbar_column_alignment' => [
						'selector'            => '.fusion-sliding-bar-wrapper',
						'container_inclusive' => false,
						'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'sliding_bar' ],
					],
				],
			],
			'slidingbar_content_padding'   => [
				'label'       => esc_html__( 'Sliding Bar Content Padding', 'Avada' ),
				'description' => esc_html__( 'Controls the top/right/bottom/left paddings of the sliding bar area.', 'Avada' ),
				'id'          => 'slidingbar_content_padding',
				'default'     => [
					'top'    => '60px',
					'bottom' => '60px',
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
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'   => '--slidingbar_content_padding-top',
						'choice' => 'top',
					],
					[
						'name'   => '--slidingbar_content_padding-bottom',
						'choice' => 'bottom',
					],
					[
						'name'   => '--slidingbar_content_padding-left',
						'choice' => 'left',
					],
					[
						'name'   => '--slidingbar_content_padding-right',
						'choice' => 'right',
					],
				],
			],
			'slidingbar_content_align'     => [
				'label'       => esc_html__( 'Sliding Bar Content Alignment', 'Avada' ),
				'description' => esc_html__( 'Controls sliding bar content alignment.', 'Avada' ),
				'id'          => 'slidingbar_content_align',
				'default'     => is_rtl() ? 'right' : 'left',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'left'   => esc_html__( 'Left', 'Avada' ),
					'center' => esc_html__( 'Center', 'Avada' ),
					'right'  => esc_html__( 'Right', 'Avada' ),
				],
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'    => '--slidingbar_content_align',
						'element' => '.fusion-sliding-bar',
					],
				],
			],
			'sliding_bar_styling_title'    => [
				'label'       => esc_html__( 'Sliding Bar Styling', 'Avada' ),
				'description' => '',
				'id'          => 'sliding_bar_styling_title',
				'type'        => 'info',
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
			],
			'slidingbar_toggle_style'      => [
				'label'           => esc_html__( 'Sliding Bar Toggle Style', 'Avada' ),
				'description'     => esc_html__( 'Controls the appearance of the sliding bar toggle.', 'Avada' ),
				'id'              => 'slidingbar_toggle_style',
				'default'         => 'circle',
				'type'            => 'radio-buttonset',
				'choices'         => [
					'triangle'  => esc_html__( 'Triangle', 'Avada' ),
					'rectangle' => esc_html__( 'Rectangle', 'Avada' ),
					'circle'    => esc_html__( 'Circle', 'Avada' ),
					'menu'      => esc_html__( 'Main Menu Icon', 'Avada' ),
				],
				'icons'           => [
					'triangle'  => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><g transform="translate(-54.320053,-196.29156)"><path d="m 54.320053,196.29156 h 24 v 24 z" style="stroke-width:0" /></g></svg>',
					'rectangle' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path d="M24 0h-24v24h24v-24z"/></svg>',
					'circle'    => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><circle cx="12" cy="12" r="12"/></svg>',
					'menu'      => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path d="M24 10h-10v-10h-4v10h-10v4h10v10h4v-10h10z"/></svg><span class="screen-reader-text">',
				],
				'required'        => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'partial_refresh' => [
					'sliding_bar_content_slidingbar_toggle_style'       => [
						'selector'            => '.fusion-sliding-bar-wrapper',
						'container_inclusive' => false,
						'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'sliding_bar' ],
					],
					'slidingbar_toggle_style_header_remove_before_hook' => [
						'selector'            => '.avada-hook-before-header-wrapper, .fusion-header-wrapper, #side-header-sticky, #side-header, #sliders-container',
						'container_inclusive' => true,
						'render_callback'     => '__return_null',
					],
					'slidingbar_toggle_style_header_replace_after_hook' => [
						'selector'              => '.avada-hook-after-header-wrapper',
						'container_inclusive'   => true,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header_position' ],
						'success_trigger_event' => 'header-rendered fusion-partial-wooslider',
					],
				],
				'output'          => [
					// Change classes in <body>.
					[
						'element'       => 'body',
						'function'      => 'attr',
						'attr'          => 'class',
						'value_pattern' => 'avada-slidingbar-toggle-style--$',
						'remove_attrs'  => [ 'avada-slidingbar-toggle-style-triangle', 'avada-slidingbar-toggle-style-rectangle', 'avada-slidingbar-toggle-style-circle', 'avada-slidingbar-toggle-style-menu' ],
					],
				],
			],
			'slidingbar_bg_color'          => [
				'label'       => esc_html__( 'Sliding Bar Background Color', 'Avada' ),
				'description' => esc_html__( 'Controls the background color of the sliding bar.', 'Avada' ),
				'id'          => 'slidingbar_bg_color',
				'type'        => 'color-alpha',
				'default'     => 'var(--awb-color7)',
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--slidingbar_bg_color',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'slidingbar_divider_color'     => [
				'label'       => esc_html__( 'Sliding Bar Item Divider Color', 'Avada' ),
				'description' => esc_html__( 'Controls the divider color in the sliding bar.', 'Avada' ),
				'id'          => 'slidingbar_divider_color',
				'default'     => 'var(--awb-color6)',
				'type'        => 'color-alpha',
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--slidingbar_divider_color',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'slidingbar_toggle_icon_color' => [
				'label'       => esc_html__( 'Sliding Bar Toggle/Close Icon Color', 'Avada' ),
				'description' => esc_html__( 'Controls the color of the sliding bar toggle icon and of the close icon when using the main menu icon as toggle style.', 'Avada' ),
				'id'          => 'slidingbar_toggle_icon_color',
				'default'     => 'var(--awb-color1)',
				'type'        => 'color-alpha',
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--slidingbar_toggle_icon_color',
						'element'  => '.fusion-sb-toggle-wrapper',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'slidingbar_font_size'         => [
				'label'       => esc_html__( 'Sliding Bar Heading Font Size', 'Avada' ),
				'description' => esc_html__( 'Controls the font size for the sliding bar heading text.', 'Avada' ),
				'id'          => 'slidingbar_font_size',
				'default'     => '14px',
				'type'        => 'dimension',
				'choices'     => [
					'units' => [ 'px', 'em' ],
				],
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'    => '--slidingbar_font_size',
						'element' => '#slidingbar',
					],
				],
			],
			'slidingbar_headings_color'    => [
				'label'       => esc_html__( 'Sliding Bar Headings Color', 'Avada' ),
				'description' => esc_html__( 'Controls the text color of the sliding bar heading font.', 'Avada' ),
				'id'          => 'slidingbar_headings_color',
				'default'     => 'var(--awb-color1)',
				'type'        => 'color-alpha',
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--slidingbar_headings_color',
						'element'  => '#slidingbar-area',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'slidingbar_text_color'        => [
				'label'       => esc_html__( 'Sliding Bar Font Color', 'Avada' ),
				'description' => esc_html__( 'Controls the text color of the sliding bar font.', 'Avada' ),
				'id'          => 'slidingbar_text_color',
				'default'     => 'hsla(var(--awb-color1-h),var(--awb-color1-s),var(--awb-color1-l),calc(var(--awb-color1-a) - 40%))',
				'type'        => 'color-alpha',
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--slidingbar_text_color',
						'element'  => '#slidingbar-area',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'slidingbar_link_color'        => [
				'label'       => esc_html__( 'Sliding Bar Link Color', 'Avada' ),
				'description' => esc_html__( 'Controls the text color of the sliding bar link font.', 'Avada' ),
				'id'          => 'slidingbar_link_color',
				'default'     => 'hsla(var(--awb-color1-h),var(--awb-color1-s),var(--awb-color1-l),calc(var(--awb-color1-a) - 14%))',
				'type'        => 'color-alpha',
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--slidingbar_link_color',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'slidingbar_link_color_hover'  => [
				'label'       => esc_html__( 'Sliding Bar Link Hover Color', 'Avada' ),
				'description' => esc_html__( 'Controls the text hover color of the sliding bar link font.', 'Avada' ),
				'id'          => 'slidingbar_link_color_hover',
				'default'     => 'var(--awb-color1)',
				'type'        => 'color-alpha',
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--slidingbar_link_color_hover',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'slidingbar_border'            => [
				'label'       => esc_html__( 'Border on Sliding Bar', 'Avada' ),
				'description' => esc_html__( 'Turn on to display a border line on the sliding bar which makes it stand out more.', 'Avada' ),
				'id'          => 'slidingbar_border',
				'default'     => '0',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'slidingbar_widgets',
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
								'condition' => [ '', 'true' ],
								'element'   => 'body',
								'className' => 'avada-has-slidingbar-border',
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
								'element'   => '#slidingbar-area',
								'className' => 'fusion-sliding-bar-border',
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
		],
	];

	return $sections;
}

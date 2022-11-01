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
function avada_options_section_events_calendar( $sections ) {

	if ( ! Avada::$is_updating && ! class_exists( 'Tribe__Events__Main' ) ) {
		return $sections;
	}

	$ec_version_greater_than_4_6_18 = false;
	if ( class_exists( 'Tribe__Events__Main' ) && version_compare( Tribe__Events__Main::VERSION, '4.6.19', '>=' ) ) {
		$ec_version_greater_than_4_6_18 = true;
	}

	$sections['ec'] = [
		'label'    => esc_html__( 'Events Calendar', 'Avada' ),
		'id'       => 'heading_events_calendar',
		'is_panel' => true,
		'priority' => 30,
		'icon'     => 'el-icon-calendar',
		'alt_icon' => 'fusiona-calendar-alt-regular',
		'fields'   => [
			'ec_general_tab'                         => [
				'label'       => esc_html__( 'General Events Calendar', 'Avada' ),
				'description' => '',
				'id'          => 'ec_general_tab',
				'default'     => '',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'ec_display_page_title'        => ( ! $ec_version_greater_than_4_6_18 || Fusion_Helper::tribe_is_v2_views_enabled() ) ? [] : [
						'type'        => 'radio-buttonset',
						'label'       => esc_html__( 'Display Events Page Title', 'Avada' ),
						'description' => esc_html__( 'Controls if the native page title for events calendar archive pages should be displayed above or below the filter bar, or if it should be disabled.', 'Avada' ),
						'id'          => 'ec_display_page_title',
						'default'     => 'below',
						'choices'     => [
							'above'   => esc_html__( 'Above', 'Avada' ),
							'below'   => esc_html__( 'Below', 'Avada' ),
							'disable' => esc_html__( 'Disable', 'Avada' ),
						],
					],
					'primary_overlay_text_color'   => [
						'label'       => esc_html__( 'Events Primary Color Overlay Text Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of text when primary color is the background.', 'Avada' ),
						'id'          => 'primary_overlay_text_color',
						'default'     => 'var(--awb-color1)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--primary_overlay_text_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'ec_bar_bg_color'              => [
						'label'       => esc_html__( 'Events Filter Bar Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color for the events calendar filter bar.', 'Avada' ),
						'id'          => 'ec_bar_bg_color',
						'default'     => 'var(--awb-color2)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--ec_bar_bg_color',
								'callback' => [ 'sanitize_color' ],
							],
							[
								'name'     => '--ec_bar_bg_color-25l',
								'callback' => [ 'lightness_adjust', -.25 ],
							],
							[
								'name'     => '--ec_bar_bg_color-15l',
								'callback' => [ 'lightness_adjust', -.15 ],
							],
							[
								'name'     => '--ec_bar_bg_color-1l',
								'callback' => [ 'lightness_adjust', .1 ],
							],
						],
					],
					'ec_bar_text_color'            => [
						'label'       => esc_html__( 'Event Filter Bar Text Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of the event filter bar text.', 'Avada' ),
						'id'          => 'ec_bar_text_color',
						'default'     => 'var(--awb-color8)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--ec_bar_text_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'ec_calendar_heading_bg_color' => ( Fusion_Helper::tribe_is_v2_views_enabled() ) ? [] : [
						'label'       => esc_html__( 'Events Monthly Calendar Heading Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of the numbered heading in the calendar.', 'Avada' ),
						'id'          => 'ec_calendar_heading_bg_color',
						'default'     => 'var(--awb-color3)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--ec_calendar_heading_bg_color',
								'callback' => [ 'sanitize_color' ],
							],
							[
								'name'     => '--ec_calendar_heading_bg_color-4l',
								'callback' => [ 'lightness_adjust', .4 ],
							],
						],
					],
					'ec_calendar_bg_color'         => ( Fusion_Helper::tribe_is_v2_views_enabled() ) ? [] : [
						'label'       => esc_html__( 'Events Monthly Calendar Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of each day in the calendar.', 'Avada' ),
						'id'          => 'ec_calendar_bg_color',
						'default'     => 'var(--awb-color3)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--ec_calendar_bg_color',
								'callback' => [ 'sanitize_color' ],
							],
							[
								'name'     => '--ec_calendar_bg_color-6l',
								'callback' => [ 'lightness_adjust', .6 ],
							],
							[
								'name'     => '--ec_calendar_bg_color-7l',
								'callback' => [ 'lightness_adjust', .7 ],
							],
							[
								'name'     => '--ec_calendar_bg_color-8l',
								'callback' => [ 'lightness_adjust', .8 ],
							],
						],
					],
					'ec_tooltip_bg_color'          => [
						'label'       => esc_html__( 'Events Popover/Drop-down Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color for the event popover/drop-down background.', 'Avada' ),
						'id'          => 'ec_tooltip_bg_color',
						'default'     => 'var(--awb-color1)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--ec_tooltip_bg_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'ec_tooltip_bg_hover_color'    => ( ! Fusion_Helper::tribe_is_v2_views_enabled() ) ? [] : [
						'label'       => esc_html__( 'Events Popover/Drop-down Background Hover Color', 'Avada' ),
						'description' => esc_html__( 'Controls the hover color for the event popover/drop-down background.', 'Avada' ),
						'id'          => 'ec_tooltip_bg_hover_color',
						'default'     => 'var(--awb-color2)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--ec_tooltip_bg_hover_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'ec_tooltip_body_color'        => [
						'label'       => esc_html__( 'Events Popover/Drop-down Body Text Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of the popover/drop-down text.', 'Avada' ),
						'id'          => 'ec_tooltip_body_color',
						'default'     => 'var(--awb-color8)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--ec_tooltip_body_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'ec_border_color'              => [
						'label'       => esc_html__( 'Events Border Color', 'Avada' ),
						'description' => esc_html__( 'Controls the various border colors around the calendar.', 'Avada' ),
						'id'          => 'ec_border_color',
						'default'     => 'var(--awb-color3)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--ec_border_color',
								'callback' => [ 'sanitize_color' ],
							],
							[
								'name'     => '--ec_border_color-2d',
								'callback' => [ 'lightness_adjust', -0.2 ],
							],
						],
					],
					'ec_hover_type'                => [
						'label'       => esc_html__( 'Events Featured Image Hover Type', 'Avada' ),
						'description' => esc_html__( 'Controls the hover type for event featured images.', 'Avada' ),
						'id'          => 'ec_hover_type',
						'default'     => 'none',
						'type'        => 'select',
						'choices'     => [
							'none'    => 'none',
							'zoomin'  => esc_html__( 'Zoom In', 'Avada' ),
							'zoomout' => esc_html__( 'Zoom Out', 'Avada' ),
							'liftup'  => esc_html__( 'Lift Up', 'Avada' ),
						],
						'output'      => [
							// Change classes in the DOM.
							[
								'element'       => '.fusion-ec-hover-type',
								'function'      => 'attr',
								'attr'          => 'class',
								'value_pattern' => 'hover-type-$',
								'remove_attrs'  => [ 'hover-type-none', 'hover-type-zoomin', 'hover-type-zoomout', 'hover-type-liftup' ],
							],
						],
					],
					'ec_bg_list_view'              => ( Fusion_Helper::tribe_is_v2_views_enabled() ) ? [] : [
						'label'       => esc_html__( 'Events Image Background Size For List View', 'Avada' ),
						'description' => esc_html__( 'Controls if the image is set to auto or covered for list view layout. All other layouts use auto.', 'Avada' ),
						'id'          => 'ec_bg_list_view',
						'default'     => 'cover',
						'type'        => 'radio-buttonset',
						'choices'     => [
							'cover' => 'Cover',
							'auto'  => 'Auto',
						],
					],
					'ec_sep_heading_font_size'     => [
						'label'       => esc_html__( 'Events Separator Heading Font Size', 'Avada' ),
						'description' => esc_html__( 'Controls the font size of the month and day separator headings on events archive pages.', 'Avada' ),
						'id'          => 'ec_sep_heading_font_size',
						'type'        => 'dimension',
						'default'     => '18px',
						'css_vars'    => [
							[
								'name' => '--ec_sep_heading_font_size',
							],
						],
					],
				],
			],
			'ec_single_event_detail_section_heading' => [
				'label'  => esc_html__( 'Events Single Posts', 'Avada' ),
				'id'     => 'ec_single_event_detail_section_heading',
				'type'   => 'sub-section',
				'fields' => [
					'events_social_sharing_box'       => [
						'label'           => esc_html__( 'Events Social Sharing Box', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the social sharing box on single event posts.', 'Avada' ),
						'id'              => 'events_social_sharing_box',
						'default'         => 1,
						'type'            => 'switch',
						'update_callback' => [
							[
								'condition' => 'is_singular',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'ec_meta_layout'                  => [
						'type'        => 'radio-buttonset',
						'label'       => esc_html__( 'Events Single Post Meta Layout', 'Avada' ),
						'description' => esc_html__( 'Sets the layout of the single events meta data.', 'Avada' ),
						'id'          => 'ec_meta_layout',
						'default'     => 'sidebar',
						'choices'     => [
							'sidebar'       => esc_html__( 'Sidebar', 'Avada' ),
							'below_content' => esc_html__( 'Below Content', 'Avada' ),
							'disabled'      => esc_html__( 'Disabled', 'Avada' ),
						],
					],
					'ec_sidebar_layouts_info'         => [
						'label'       => esc_html__( 'Events Single Sidebar Layout', 'Avada' ),
						'description' => '',
						'id'          => 'ec_sidebar_layouts_info',
						'type'        => 'info',
					],
					'ec_sidebar_width'                => [
						'label'       => esc_html__( 'Events Single Sidebar Width', 'Avada' ),
						'description' => esc_html__( 'Controls the width of the sidebar when only one sidebar is present.', 'Avada' ),
						'id'          => 'ec_sidebar_width',
						'default'     => '32%',
						'type'        => 'dimension',
						'choices'     => [ 'px', '%' ],
						'css_vars'    => [
							[
								'name' => '--ec_sidebar_width',
							],
						],
					],
					'ec_dual_sidebar_layouts_info'    => [
						'label'       => esc_html__( 'Events Dual Sidebar Layout', 'Avada' ),
						'description' => '',
						'id'          => 'ec_dual_sidebar_layouts_info',
						'type'        => 'info',
					],
					'ec_sidebar_2_1_width'            => [
						'label'       => esc_html__( 'Events Dual Sidebar Width 1', 'Avada' ),
						'description' => esc_html__( 'Controls the width of sidebar 1 when dual sidebars are present.', 'Avada' ),
						'id'          => 'ec_sidebar_2_1_width',
						'default'     => '21%',
						'type'        => 'dimension',
						'choices'     => [ 'px', '%' ],
						'css_vars'    => [
							[
								'name' => '--ec_sidebar_2_1_width',
							],
						],
					],
					'ec_sidebar_2_2_width'            => [
						'label'       => esc_html__( 'Events Dual Sidebar Width 2', 'Avada' ),
						'description' => esc_html__( 'Controls the width of sidebar 2 when dual sidebars are present.', 'Avada' ),
						'id'          => 'ec_sidebar_2_2_width',
						'default'     => '21%',
						'type'        => 'dimension',
						'choices'     => [ 'px', '%' ],
						'css_vars'    => [
							[
								'name' => '--ec_sidebar_2_2_width',
							],
						],
					],
					'ec_sidebar_sidebar_styling_info' => [
						'label'       => esc_html__( 'Events Single Post Sidebar / Meta Content Styling', 'Avada' ),
						'description' => '',
						'id'          => 'ec_sidebar_sidebar_styling_info',
						'type'        => 'info',
					],
					'ec_sidebar_bg_color'             => [
						'label'       => esc_html__( 'Events Sidebar / Meta Content Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of the single event post sidebar(s) / meta content.', 'Avada' ),
						'id'          => 'ec_sidebar_bg_color',
						'default'     => 'var(--awb-color2)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--ec_sidebar_bg_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'ec_sidebar_padding'              => [
						'label'       => esc_html__( 'Events Sidebar / Meta Content Padding', 'Avada' ),
						'description' => esc_html__( 'Controls the padding for the single event post sidebar(s) / meta content.', 'Avada' ),
						'id'          => 'ec_sidebar_padding',
						'default'     => '4%',
						'type'        => 'dimension',
						'choices'     => [ 'px', '%' ],
						'css_vars'    => [
							[
								'name' => '--ec_sidebar_padding',
							],
							[
								'name'     => '--ec_sidebar_padding-no-vw',
								'callback' => [ 'string_replace', [ '%', 'vw' ] ],
							],
						],
					],
					'ec_sidew_font_size'              => [
						'label'       => esc_html__( 'Events Sidebar Widget / Meta Content Heading Font Size', 'Avada' ),
						'description' => esc_html__( 'Controls the size of the sidebar widget / meta content heading for single event posts.', 'Avada' ),
						'id'          => 'ec_sidew_font_size',
						'default'     => '17px',
						'type'        => 'dimension',
						'css_vars'    => [
							[
								'name' => '--ec_sidew_font_size',
							],
						],
					],
					'ec_sidebar_widget_bg_color'      => [
						'label'       => esc_html__( 'Events Sidebar Widget / Meta Content Title Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of the sidebar widget / meta content title for single event posts.', 'Avada' ),
						'id'          => 'ec_sidebar_widget_bg_color',
						'default'     => 'var(--awb-color4)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--ec_sidebar_widget_bg_color',
								'callback' => [ 'sanitize_color' ],
							],
							[
								'name'     => '--ec_sidebar_widget_bg_color-opaque-padding',
								'callback' => [
									'return_string_if_transparent',
									[
										'transparent' => '',
										'opaque'      => '9px 15px',
									],
								],
							],
						],
					],
					'ec_sidebar_heading_color'        => [
						'label'       => esc_html__( 'Events Sidebar Widget / Meta Content Headings Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of the sidebar widget / meta content heading for single event posts.', 'Avada' ),
						'id'          => 'ec_sidebar_heading_color',
						'default'     => 'var(--awb-color8)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--ec_sidebar_heading_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'ec_text_font_size'               => [
						'label'       => esc_html__( 'Events Sidebar / Meta Content Text Font Size', 'Avada' ),
						'description' => esc_html__( 'Controls the size of the text in the single event post sidebar / meta content.', 'Avada' ),
						'id'          => 'ec_text_font_size',
						'default'     => '14',
						'type'        => 'slider',
						'choices'     => [
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						],
						'css_vars'    => [
							[
								'name'          => '--ec_text_font_size',
								'value_pattern' => '$px',
							],
						],
					],
					'ec_sidebar_text_color'           => [
						'label'       => esc_html__( 'Events Sidebar / Meta Content Text Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of the text in the single event post sidebar / meta content.', 'Avada' ),
						'id'          => 'ec_sidebar_text_color',
						'default'     => 'var(--awb-color8)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--ec_sidebar_text_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'ec_sidebar_link_color'           => [
						'label'       => esc_html__( 'Events Sidebar / Meta Content Link Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of the link text in the single event post sidebar / meta content.', 'Avada' ),
						'id'          => 'ec_sidebar_link_color',
						'default'     => 'var(--awb-color5)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--ec_sidebar_link_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'ec_sidebar_divider_color'        => [
						'label'       => esc_html__( 'Events Sidebar / Meta Content Divider Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of the dividers in the single event post sidebar / meta content.', 'Avada' ),
						'id'          => 'ec_sidebar_divider_color',
						'default'     => 'var(--awb-color3)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--ec_sidebar_divider_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
				],
			],
		],
	];

	return $sections;

}

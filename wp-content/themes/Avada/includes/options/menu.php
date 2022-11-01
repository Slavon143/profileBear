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
 * Menu
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_menu( $sections ) {

	$option_name = Avada::get_option_name();
	$settings    = (array) get_option( $option_name );
	if ( ! isset( $settings['header_position'] ) ) {
		$settings['header_position'] = 'top';
	}
	if ( ! isset( $settings['side_header_break_point'] ) ) {
		$settings['side_header_break_point'] = 800;
	}

	/* translators: value. */
	$menu_height_hint = '<span id="fusion-menu-height-hint" style="display: none">' . sprintf( esc_html__( '  To match the logo height set to %s.', 'Avada' ), '<strong>Unknown</strong>' ) . '</span>';

	// If we can get logo height and the logo margins are in pixels, then we can provide a hint.
	if ( is_admin() ) {
		$logo_data = Avada()->images->get_logo_data( 'logo' );
		if ( isset( $logo_data['height'] ) && '' !== $logo_data['height'] && isset( $settings['logo_margin']['top'] ) && isset( $settings['logo_margin']['bottom'] ) ) {
			$logo_top_margin    = Fusion_Sanitize::size( $settings['logo_margin']['top'] );
			$logo_bottom_margin = Fusion_Sanitize::size( $settings['logo_margin']['bottom'] );
			if ( strpos( $logo_top_margin, 'px' ) && strpos( $logo_bottom_margin, 'px' ) ) {
				$total_logo_height = intval( $logo_top_margin ) + intval( $logo_bottom_margin ) + intval( $logo_data['height'] );
				/* translators: value. */
				$menu_height_hint = '<span id="fusion-menu-height-hint" style="display:inline">' . sprintf( esc_html__( '  To match the logo height set to %s.', 'Avada' ), '<strong>' . $total_logo_height . '</strong>' ) . '</span>';
			}
		}
	}

	$menu_edit_link = '';

	// Only needed in front end builder.
	if ( function_exists( 'fusion_is_preview_frame' ) ) {
		$menu_locations = get_nav_menu_locations();
		$menu_edit_link = isset( $menu_locations['main_navigation'] ) ? admin_url( 'nav-menus.php?action=edit&menu=' . $menu_locations['main_navigation'] ) : admin_url( 'nav-menus.php' );
	}

	// Check if we have a global header override.
	$has_global_header = false;
	if ( class_exists( 'Fusion_Template_Builder' ) ) {
		$default_layout    = Fusion_Template_Builder::get_default_layout();
		$has_global_header = isset( $default_layout['data']['template_terms'] ) && isset( $default_layout['data']['template_terms']['header'] ) && $default_layout['data']['template_terms']['header'];
	}

	$sections['menu'] = [
		'label'    => esc_html__( 'Menu', 'Avada' ),
		'id'       => 'heading_menu_section',
		'priority' => 1,
		'icon'     => 'el-icon-lines',
		'alt_icon' => 'fusiona-bars',
		'fields'   => [],
	];

	if ( $has_global_header ) {
		$sections['menu']['fields'] = [
			'custom_menu' => [
				'label'    => esc_html__( 'Main Menu', 'Avada' ),
				'id'       => 'custom_menu',
				'priority' => 6,
				'type'     => 'sub-section',
				'fields'   => [
					'menu_global_header_override_template_notice' => [
						'id'          => 'menu_global_header_override_template_notice',
						'label'       => '',
						'hidden'      => ! $has_global_header,
						'description' => sprintf(
							/* translators: 1: Content|Footer|Page Title Bar. 2: URL. */
							'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The options on this tab are not available because a global %1$s override is currently used. To edit your global layout please visit <a href="%2$s" target="_blank">this page</a>.', 'Avada' ) . '</div>',
							Fusion_Template_Builder::get_instance()->get_template_terms()['header']['label'],
							admin_url( 'admin.php?page=avada-layouts' )
						),
						'type'        => 'custom',
					],
				],
			],
		];
	}

	$fields = [
		'heading_menu'               => [
			'label'    => esc_html__( 'Main Menu', 'Avada' ),
			'id'       => 'heading_menu',
			'priority' => 6,
			'type'     => 'sub-section',
			'hidden'   => $has_global_header,
			'fields'   => [
				'menu_global_header_template_notice' => [
					'id'          => 'menu_global_header_template_notice',
					'label'       => '',
					'description' => class_exists( 'Fusion_Template_Builder' ) ? sprintf(
						/* translators: %1$s: Content|Footer|Page Title Bar. %2$s: Layout selection URL. */
						'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> For more flexibility and a more modern, performant setup, we recommend using the %1$s Builder. To create a custom %1$s Layout, <a href="%2$s" target="_blank">visit this page</a>.', 'Avada' ) . '</div>',
						Fusion_Template_Builder::get_instance()->get_template_terms()['header']['label'],
						admin_url( 'admin.php?page=avada-layouts' )
					) : '',
					'type'        => 'custom',
				],
				'nav_height'                         => [
					'label'         => esc_html__( 'Main Menu Height', 'Avada' ),
					'description'   => esc_html__( 'Controls the menu height.', 'Avada' ) . $menu_height_hint,
					'id'            => 'nav_height',
					'default'       => '94',
					'type'          => 'slider',
					'choices'       => [
						'min'  => '0',
						'max'  => '300',
						'step' => '1',
					],
					'class'         => 'fusion-or-gutter',
					'required'      => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'      => [
						[
							'name'          => '--nav_height',
							'element'       => '.fusion-main-menu',
							'value_pattern' => '$px',
						],
					],
					'output'        => [
						// This is for the avadaHeaderVars.nav_height var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'globalVar' => 'avadaHeaderVars',
									'id'        => 'nav_height',
									'trigger'   => [ 'fusion-reinit-sticky-header' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
					'edit_shortcut' => [
						'selector'  => [ '.fusion-header', '#side-header .side-header-wrapper' ],
						'shortcuts' => [
							[
								'aria_label' => esc_html__( 'Edit Main Menu', 'Avada' ),
								'link'       => $menu_edit_link,
								'order'      => 3,
							],
						],
					],
				],
				'menu_highlight_style'               => [
					'label'           => esc_html__( 'Main Menu Highlight Style', 'Avada' ),
					'description'     => __( 'Controls the highlight style for main menu links and also affects the look of menu dropdowns. Arrow style cannot work with a transparent header background. Bar highlights will display vertically on side header layouts. <strong>IMPORTANT:</strong> Arrow & Background style can require configuration of other options depending on desired effect.', 'Avada' ) . ' <a href="https://theme-fusion.com/documentation/avada/main-menu-highlight-styles/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'See this post for more information', 'Avada' ) . '</a>.',
					'id'              => 'menu_highlight_style',
					'default'         => 'bar',
					'choices'         => [
						'bar'        => esc_html__( 'Top Bar', 'Avada' ),
						'bottombar'  => esc_html__( 'Bottom Bar', 'Avada' ),
						'arrow'      => esc_html__( 'Arrow', 'Avada' ),
						'background' => esc_html__( 'Background', 'Avada' ),
						'textcolor'  => esc_html__( 'Color Only', 'Avada' ),
					],
					'type'            => 'radio-buttonset',
					'class'           => 'fusion-or-gutter',
					'required'        => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'output'          => [

						// Change body class.
						[
							'element'       => 'body',
							'function'      => 'attr',
							'attr'          => 'class',
							'value_pattern' => 'avada-menu-highlight-style-$',
							'remove_attrs'  => [ 'avada-menu-highlight-style-bar', 'avada-menu-highlight-style-bottombar', 'avada-menu-highlight-style-arrow', 'avada-menu-highlight-style-background', 'avada-menu-highlight-style-textcolor' ],
						],

						// Change the avadaHeaderVars.nav_highlight_style var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'choice'    => 'top',
									'globalVar' => 'avadaHeaderVars',
									'id'        => 'nav_highlight_style',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],

					// Partial refresh for the header.
					'partial_refresh' => [
						'menu_highlight_style_header_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'menu_highlight_style_header_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'menu_highlight_style_header' => [
							'selector'              => '.fusion-header-wrapper',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'header-rendered',
						],
					],
				],
				'menu_highlight_background'          => [
					'label'       => esc_html__( 'Main Menu Highlight Background Color', 'Avada' ),
					'description' => esc_html__( 'Controls the background color of main menu highlight.', 'Avada' ),
					'id'          => 'menu_highlight_background',
					'default'     => 'var(--awb-color4)',
					'type'        => 'color-alpha',
					'class'       => 'fusion-gutter-and-or-and',
					'required'    => [
						[
							'setting'  => 'menu_highlight_style',
							'operator' => '==',
							'value'    => 'background',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'menu_highlight_style',
							'operator' => '==',
							'value'    => 'background',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--menu_highlight_background',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'menu_arrow_size'                    => [
					'label'       => esc_html__( 'Main Menu Arrow Size', 'Avada' ),
					'description' => esc_html__( 'Controls the width and height of the main menu arrow.', 'Avada' ),
					'id'          => 'menu_arrow_size',
					'units'       => false,
					'default'     => [
						'width'  => '23px',
						'height' => '12px',
					],
					'type'        => 'dimensions',
					'class'       => 'fusion-gutter-and-or-and',
					'required'    => [
						[
							'setting'  => 'menu_highlight_style',
							'operator' => '==',
							'value'    => 'arrow',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'menu_highlight_style',
							'operator' => '==',
							'value'    => 'arrow',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name'   => '--menu_arrow_size-width',
							'choice' => 'width',
						],
						[
							'name'   => '--menu_arrow_size-height',
							'choice' => 'height',
						],
						[
							'name'     => '--menu_arrow_size-width-header_border_color_condition_5',
							'choice'   => 'width',
							'callback' => [ 'header_border_color_condition_5', '' ],
						],
					],
				],
				'nav_highlight_border'               => [
					'label'       => esc_html__( 'Main Menu Highlight Bar Size', 'Avada' ),
					'description' => esc_html__( 'Controls the size of the menu highlight bar.', 'Avada' ),
					'id'          => 'nav_highlight_border',
					'default'     => '3',
					'type'        => 'slider',
					'choices'     => [
						'min'  => '0',
						'max'  => '40',
						'step' => '1',
					],
					'class'       => 'fusion-gutter-and-or-and-or-and-or-and',
					'required'    => [
						[
							'setting'  => 'menu_highlight_style',
							'operator' => '==',
							'value'    => 'bar',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'menu_highlight_style',
							'operator' => '==',
							'value'    => 'bar',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'menu_highlight_style',
							'operator' => '==',
							'value'    => 'bottombar',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'menu_highlight_style',
							'operator' => '==',
							'value'    => 'bottombar',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name'          => '--nav_highlight_border',
							'value_pattern' => '$px',
							'callback'      => [ 'fallback_to_value', '0' ],
						],
					],
					'output'      => [
						// Change the avadaHeaderVars.nav_height var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'choice'    => 'top',
									'globalVar' => 'avadaHeaderVars',
									'id'        => 'nav_highlight_border',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
						// Change the avadaHeaderVars.nav_height var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'choice'    => 'top',
									'globalVar' => 'avadaHeaderVars',
									'id'        => 'nav_height',
									'trigger'   => [ 'fusion-reinit-sticky-header' ],
									'condition' => [ 'menu_highlight_style', '===', 'bar', '$', '0' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'nav_padding'                        => [
					'label'       => esc_html__( 'Main Menu Item Padding', 'Avada' ),
					'description' => esc_html__( 'Controls the right padding for menu text (left on RTL).', 'Avada' ),
					'id'          => 'nav_padding',
					'default'     => '48',
					'type'        => 'slider',
					'choices'     => [
						'min'  => '0',
						'max'  => '200',
						'step' => '1',
					],
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '==',
							'value'    => 'top',
						],
					],
					'css_vars'    => [
						[
							'name'          => '--nav_padding',
							'value_pattern' => '$px',
						],
						[
							'name'     => '--nav_padding-no-zero',
							'callback' => [
								'conditional_return_value',
								[
									'value_pattern' => [ '$px', '' ],
									'conditions'    => [
										[ 'nav_padding', '==', '0' ],
									],
								],
							],
						],
					],
				],
				'mobile_nav_padding'                 => [
					'label'       => esc_html__( 'Main Menu Item Padding On Mobile', 'Avada' ),
					'description' => esc_html__( 'Controls the right padding for menu text (left on RTL) when the normal desktop menu is used on mobile devices.', 'Avada' ),
					'id'          => 'mobile_nav_padding',
					'default'     => '25',
					'type'        => 'slider',
					'choices'     => [
						'min'  => '0',
						'max'  => '200',
						'step' => '1',
					],
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '==',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name'          => '--mobile_nav_padding',
							'element'       => '.fusion-main-menu',
							'value_pattern' => '$px',
						],
					],
				],
				'megamenu_shadow'                    => [
					'label'       => esc_html__( 'Main Menu Drop Shadow', 'Avada' ),
					'description' => esc_html__( 'Turn on to display a drop shadow on menu dropdowns.', 'Avada' ),
					'id'          => 'megamenu_shadow',
					'default'     => '1',
					'type'        => 'switch',
					'class'       => 'fusion-or-gutter',
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
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
									'className' => 'avada-has-megamenu-shadow',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'main_menu_sub_menu_animation'       => [
					'label'       => esc_html__( 'Main Menu Dropdown / Mega Menu Animation', 'Avada' ),
					'description' => esc_html__( 'Controls the animation type for all sub-menus.', 'Avada' ),
					'id'          => 'main_menu_sub_menu_animation',
					'type'        => 'radio-buttonset',
					'default'     => 'fade',
					'choices'     => [
						'fade'  => esc_html__( 'Fade', 'Avada' ),
						'slide' => esc_html__( 'Slide', 'Avada' ),
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
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					// Change body class.
					[
						'element'       => 'body',
						'function'      => 'attr',
						'attr'          => 'class',
						'value_pattern' => 'fusion-sub-menu-$',
						'remove_attrs'  => [ 'fusion-sub-menu-fade', 'fusion-sub-menu-slide' ],
					],
				],
				'dropdown_menu_top_border_size'      => [
					'label'       => esc_html__( 'Main Menu Dropdown Top Border Size', 'Avada' ),
					'description' => esc_html__( 'Controls top border size of dropdown menus and mega menus.', 'Avada' ),
					'id'          => 'dropdown_menu_top_border_size',
					'default'     => '3',
					'type'        => 'slider',
					'choices'     => [
						'min'  => '0',
						'max'  => '50',
						'step' => '1',
					],
					'class'       => 'fusion-gutter-and-or-and-or-and-or-and',
					'required'    => [
						[
							'setting'  => 'menu_highlight_style',
							'operator' => '==',
							'value'    => 'bar',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'menu_highlight_style',
							'operator' => '==',
							'value'    => 'bar',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'menu_highlight_style',
							'operator' => '==',
							'value'    => 'bottombar',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'menu_highlight_style',
							'operator' => '==',
							'value'    => 'bottombar',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name'          => '--dropdown_menu_top_border_size',
							'value_pattern' => '$px',
						],
					],
				],
				'dropdown_menu_width'                => [
					'label'       => esc_html__( 'Main Menu Dropdown Width', 'Avada' ),
					'description' => esc_html__( 'Controls the width of the dropdown.', 'Avada' ),
					'id'          => 'dropdown_menu_width',
					'default'     => '200',
					'type'        => 'slider',
					'choices'     => [
						'min'  => '0',
						'max'  => '500',
						'step' => '1',
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
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name'          => '--dropdown_menu_width',
							'value_pattern' => '$px',
						],
					],
				],
				'mainmenu_dropdown_vertical_padding' => [
					'label'       => esc_html__( 'Main Menu Dropdown Item Padding', 'Avada' ),
					'description' => esc_html__( 'Controls the top/bottom padding for dropdown menu items.', 'Avada' ),
					'id'          => 'mainmenu_dropdown_vertical_padding',
					'default'     => '12',
					'type'        => 'slider',
					'choices'     => [
						'min'  => '0',
						'max'  => '50',
						'step' => '1',
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
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name'          => '--mainmenu_dropdown_vertical_padding',
							'value_pattern' => '$px',
						],
					],
				],
				'mainmenu_dropdown_display_divider'  => [
					'label'       => esc_html__( 'Main Menu Dropdown Divider', 'Avada' ),
					'description' => esc_html__( 'Turn on to display a divider line on dropdown menu items.', 'Avada' ),
					'id'          => 'mainmenu_dropdown_display_divider',
					'default'     => '0',
					'type'        => 'switch',
					'class'       => 'fusion-or-gutter',
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
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
									'className' => 'avada-has-mainmenu-dropdown-divider',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'menu_display_dropdown_indicator'    => [
					'label'           => esc_html__( 'Main Menu Dropdown Indicator', 'Avada' ),
					'description'     => esc_html__( 'Turn on to display arrow indicators next to parent level menu items.', 'Avada' ),
					'id'              => 'menu_display_dropdown_indicator',
					'default'         => 'none',
					'choices'         => [
						'parent'       => esc_html__( 'Parent', 'Avada' ),
						'parent_child' => esc_html__( 'Parent + Child', 'Avada' ),
						'none'         => esc_html__( 'None', 'Avada' ),
					],
					'type'            => 'radio-buttonset',
					'class'           => 'fusion-or-gutter',
					'required'        => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					// Partial refresh for the header.
					'partial_refresh' => [
						'header_menu_display_dropdown_indicator_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_menu_display_dropdown_indicator_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_menu_display_dropdown_indicator' => [
							'selector'              => '.fusion-header-wrapper',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'header-rendered',
						],
					],
				],
				'main_nav_search_icon'               => [
					'label'           => esc_html__( 'Main Menu Search Icon', 'Avada' ),
					'description'     => esc_html__( 'Turn on to display the search icon in the main menu.', 'Avada' ),
					'id'              => 'main_nav_search_icon',
					'default'         => '1',
					'type'            => 'switch',
					// Partial refresh for the header.
					'partial_refresh' => [
						'header_main_nav_search_icon_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_main_nav_search_icon_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'header_main_nav_search_icon' => [
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
									'className' => 'avada-has-main-nav-search-icon',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'main_nav_search_layout'             => [
					'label'           => esc_html__( 'Main Menu Search Layout', 'Avada' ),
					'description'     => esc_html__( 'Controls the layout of the search bar in the main menu.', 'Avada' ),
					'id'              => 'main_nav_search_layout',
					'default'         => 'overlay',
					'choices'         => [
						'dropdown' => esc_html__( 'Drop-Down', 'Avada' ),
						'overlay'  => esc_html__( 'Menu Overlay', 'Avada' ),
					],
					'type'            => 'radio-buttonset',
					'required'        => [
						[
							'setting'  => 'main_nav_search_icon',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_position',
							'operator' => '==',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'partial_refresh' => [
						'main_nav_search_layout_refresh' => [
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
									'condition' => [ 'dropdown', 'overlay' ],
									'element'   => 'body',
									'className' => [ 'fusion-main-menu-search-dropdown', 'fusion-main-menu-search-overlay' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'main_nav_icon_circle'               => [
					'label'       => esc_html__( 'Main Menu Icon Circle Borders', 'Avada' ),
					'description' => esc_html__( 'Turn on to display a circle border on the cart and search icons.', 'Avada' ),
					'id'          => 'main_nav_icon_circle',
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
									'className' => 'fusion-has-main-nav-icon-circle',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'main_nav_highlight_radius'          => [
					'label'       => esc_html__( 'Menu Highlight Label Radius', 'Avada' ),
					'description' => esc_html__( 'Controls the border radius of all your menu highlight labels.', 'Avada' ),
					'id'          => 'main_nav_highlight_radius',
					'default'     => '2px',
					'type'        => 'dimension',
					'css_vars'    => [
						[
							'name'    => '--main_nav_highlight_radius',
							'element' => '.fusion-menu-highlight-label',
						],
					],
				],
				'menu_sub_bg_color'                  => [
					'label'       => esc_html__( 'Main Menu Dropdown Background Color', 'Avada' ),
					'description' => esc_html__( 'Controls the background color of the main menu dropdown.', 'Avada' ),
					'id'          => 'menu_sub_bg_color',
					'default'     => 'var(--awb-color1)',
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
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--menu_sub_bg_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'menu_bg_hover_color'                => [
					'label'       => esc_html__( 'Main Menu Dropdown Background Hover Color', 'Avada' ),
					'description' => esc_html__( 'Controls the background hover color of the main menu dropdown.', 'Avada' ),
					'id'          => 'menu_bg_hover_color',
					'default'     => 'var(--awb-color3)',
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
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--menu_bg_hover_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'menu_sub_sep_color'                 => [
					'label'       => esc_html__( 'Main Menu Dropdown Separator Color', 'Avada' ),
					'description' => esc_html__( 'Controls the color of the separators in the main menu dropdown.', 'Avada' ),
					'id'          => 'menu_sub_sep_color',
					'default'     => 'var(--awb-color3)',
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
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--menu_sub_sep_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
					'output'      => [
						[
							'element'           => [ '.fusion-main-menu .fusion-main-menu-search .fusion-custom-menu-item-contents', '.fusion-main-menu .fusion-main-menu-cart .fusion-custom-menu-item-contents', '.fusion-main-menu .fusion-menu-login-box .fusion-custom-menu-item-contents' ],
							'property'          => 'border',
							'js_callback'       => [
								'fusionReturnStringIfTransparent',
								[
									'transparent' => '0',
									'opaque'      => '',
								],
							],
							'sanitize_callback' => [ 'Avada_Output_Callbacks', 'menu_sub_sep_color' ],
						],
					],
				],
				'menu_h45_bg_color'                  => [
					'label'       => esc_html__( 'Main Menu Background Color For Header 4 & 5', 'Avada' ),
					'description' => esc_html__( 'Controls the background color of the main menu when using header 4 or 5.', 'Avada' ),
					'id'          => 'menu_h45_bg_color',
					'default'     => 'var(--awb-color1)',
					'type'        => 'color-alpha',
					'class'       => 'fusion-or-gutter',
					'required'    => [
						[
							'setting'  => 'header_layout',
							'operator' => '==',
							'value'    => 'v4',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '==',
							'value'    => 'v5',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--menu_h45_bg_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'main_menu_typography_info'          => [
					'label'       => esc_html__( 'Main Menu Typography', 'Avada' ),
					'description' => '',
					'id'          => 'main_menu_typography_info',
					'type'        => 'info',
				],
				'nav_typography'                     => [
					'id'                        => 'nav_typography',
					'label'                     => esc_html__( 'Menus Typography', 'Avada' ),
					'description'               => esc_html__( 'These settings control the typography for all main menu top-level items.', 'Avada' ),
					'type'                      => 'typography',
					'global'                    => true,
					'text_transform_no_inherit' => true,
					'class'                     => 'avada-no-fontsize',
					'choices'                   => [
						'font-family'    => true,
						'font-weight'    => true,
						'font-size'      => true,
						'letter-spacing' => true,
						'color'          => true,
						'text-transform' => true,
					],
					'default'                   => [
						'font-family'    => 'var(--awb-typography3-font-family)',
						'font-weight'    => '400',
						'font-size'      => 'var(--awb-typography3-font-size)',
						'letter-spacing' => 'var(--awb-typography3-letter-spacing)',
						'color'          => 'var(--awb-color8)',
						'text-transform' => 'none',
					],
					'css_vars'                  => [
						[
							'name'     => '--nav_typography-font-family',
							'choice'   => 'font-family',
							'callback' => [ 'combined_font_family', 'nav_typography' ],
						],
						[
							'name'     => '--nav_typography-font-weight',
							'choice'   => 'font-weight',
							'callback' => [ 'font_weight_no_regular', '' ],
						],
						[
							'name'   => '--nav_typography-font-size',
							'choice' => 'font-size',
						],
						[
							'name'   => '--nav_typography-font-style',
							'choice' => 'font-style',
						],
						[
							'name'     => '--nav_typography-letter-spacing',
							'choice'   => 'letter-spacing',
							'callback' => [ 'maybe_append_px', '' ],
						],
						[
							'name'   => '--nav_typography-color',
							'choice' => 'color',
						],
						[
							'name'     => '--nav_typography-color-65a',
							'choice'   => 'color',
							'callback' => [ 'color_alpha_set', .65 ],
						],
						[
							'name'     => '--nav_typography-color-35a',
							'choice'   => 'color',
							'callback' => [ 'color_alpha_set', .35 ],
						],
						[
							'name'   => '--nav_typography-text-transform',
							'choice' => 'text-transform',
						],
					],
				],
				'menu_text_align'                    => [
					'label'           => esc_html__( 'Main Menu Text Align', 'Avada' ),
					'description'     => esc_html__( 'Controls the main menu text alignment for top headers 4-5 and side headers.', 'Avada' ),
					'id'              => 'menu_text_align',
					'default'         => 'center',
					'choices'         => [
						'left'   => esc_html__( 'Left', 'Avada' ),
						'center' => esc_html__( 'Center', 'Avada' ),
						'right'  => esc_html__( 'Right', 'Avada' ),
					],
					'type'            => 'radio-buttonset',
					'class'           => 'fusion-or-gutter',
					'required'        => [
						[
							'setting'  => 'header_layout',
							'operator' => '==',
							'value'    => 'v4',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '==',
							'value'    => 'v5',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
					],
					'css_vars'        => [
						[
							'name'    => '--menu_text_align',
							'element' => '.fusion-main-menu',
						],
					],
					'output'          => [
						( class_exists( 'SitePress' ) ) ? [
							'element'       => [ '#side-header .fusion-main-menu .wpml-ls-item > a', '#side-header .fusion-main-menu .wpml-ls-item .menu-text' ],
							'property'      => 'justify-content',
							'value_pattern' => ( is_rtl() ) ? 'flex-end' : 'flex-start',
							'exclude'       => [ 'right', 'center' ],
						] : [],
						( class_exists( 'SitePress' ) ) ? [
							'element'       => [ '#side-header .fusion-main-menu .wpml-ls-item > a', '#side-header .fusion-main-menu .wpml-ls-item .menu-text' ],
							'property'      => 'justify-content',
							'value_pattern' => ( is_rtl() ) ? 'flex-start' : 'flex-end',
							'exclude'       => [ 'left', 'center' ],
						] : [],
						[
							'element'       => 'body',
							'function'      => 'attr',
							'attr'          => 'class',
							'value_pattern' => 'menu-text-align-$',
							'remove_attrs'  => [ 'menu-text-align-left', 'menu-text-align-center', 'menu-text-align-right' ],
						],
					],
					// Partial refresh for the header.
					'partial_refresh' => [
						'menu_text_align_header_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'menu_text_align_header_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'menu_text_align_header' => [
							'selector'              => '.fusion-header-wrapper',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'header-rendered',
						],
					],
				],
				'menu_hover_first_color'             => [
					'label'       => esc_html__( 'Main Menu Font Hover/Active Color', 'Avada' ),
					'description' => esc_html__( 'Controls the color for main menu text hover and active states, highlight bar and dropdown border.', 'Avada' ),
					'id'          => 'menu_hover_first_color',
					'default'     => 'var(--awb-color4)',
					'type'        => 'color-alpha',
					'css_vars'    => [
						[
							'name'     => '--menu_hover_first_color',
							'callback' => [ 'sanitize_color' ],
						],
						[
							'name'     => '--menu_hover_first_color-65a',
							'callback' => [ 'color_alpha_set', '0.65' ],
						],
					],
				],
				'menu_sub_color'                     => [
					'label'       => esc_html__( 'Main Menu Dropdown Font Color', 'Avada' ),
					'description' => esc_html__( 'Controls the color for main menu dropdown text.', 'Avada' ),
					'id'          => 'menu_sub_color',
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
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--menu_sub_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'nav_dropdown_font_size'             => [
					'label'       => esc_html__( 'Main Menu Dropdown Font Size', 'Avada' ),
					'description' => esc_html__( 'Controls the font size for main menu dropdown text.', 'Avada' ),
					'id'          => 'nav_dropdown_font_size',
					'default'     => '14px',
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
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
					'css_vars'    => [
						[
							'name' => '--nav_dropdown_font_size',
						],
					],
				],
				'side_nav_font_size'                 => [
					'label'       => esc_html__( 'Side Navigation Font Size', 'Avada' ),
					'description' => esc_html__( 'Controls the font size for the menu text when using the side navigation page template.', 'Avada' ),
					'id'          => 'side_nav_font_size',
					'default'     => '16px',
					'type'        => 'dimension',
					'choices'     => [
						'units' => [ 'px', 'em' ],
					],
					'css_vars'    => [
						[
							'name'    => '--side_nav_font_size',
							'element' => '.side-nav',
						],
					],
				],
			],
		],
		'flyout_menu_subsection'     => [
			'label'  => esc_html__( 'Flyout Menu', 'Avada' ),
			'id'     => 'flyout_menu_subsection',
			'type'   => 'sub-section',
			'hidden' => $has_global_header,
			'fields' => [
				'flyout_menu_important_note_info' => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? [] : [
					'label'       => '',
					'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Flyout Menu Options are only available when using Header Layout #6 or Mobile Flyout Menu. Your current setup does not utilize the flyout menu.', 'Avada' ) . '</div>',
					'id'          => 'flyout_menu_important_note_info',
					'type'        => 'custom',
					'class'       => 'fusion-gutter-and-or-and',
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
					],
				],
				'flyout_menu_icon_font_size'      => [
					'label'       => esc_html__( 'Flyout Menu Icon Font Size', 'Avada' ),
					'description' => esc_html__( 'Controls the font size for the flyout menu icons.', 'Avada' ),
					'id'          => 'flyout_menu_icon_font_size',
					'default'     => '20px',
					'type'        => 'dimension',
					'class'       => 'fusion-gutter-and-or',
					'choices'     => [
						'units' => [ 'px', 'em' ],
					],
					'required'    => [
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'header_position',
							'operator' => '=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '=',
							'value'    => 'flyout',
						],
					],
					'css_vars'    => [
						[
							'name' => '--flyout_menu_icon_font_size',
						],
						[
							'name'     => '--flyout_menu_icon_font_size_px',
							'callback' => [ 'units_to_px' ],
						],
					],
				],
				'flyout_nav_icons_padding'        => [
					'label'       => esc_html__( 'Flyout Menu Icon Padding', 'Avada' ),
					'description' => esc_html__( 'Controls the right padding for flyout menu icons (left on RTL).', 'Avada' ),
					'id'          => 'flyout_nav_icons_padding',
					'default'     => '32',
					'type'        => 'slider',
					'class'       => 'fusion-gutter-and-or',
					'choices'     => [
						'min'  => '0',
						'max'  => '200',
						'step' => '1',
					],
					'required'    => [
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'header_position',
							'operator' => '=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '=',
							'value'    => 'flyout',
						],
					],
					'css_vars'    => [
						[
							'name'          => '--flyout_nav_icons_padding',
							'element'       => '.fusion-flyout-menu-icons',
							'value_pattern' => '$px',
						],
					],
				],
				'flyout_menu_icon_color'          => [
					'label'       => esc_html__( 'Flyout Menu Icon Color', 'Avada' ),
					'description' => esc_html__( 'Controls the color of the flyout menu icons.', 'Avada' ),
					'id'          => 'flyout_menu_icon_color',
					'default'     => 'var(--awb-color8)',
					'type'        => 'color-alpha',
					'class'       => 'fusion-gutter-and-or',
					'required'    => [
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'header_position',
							'operator' => '=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '=',
							'value'    => 'flyout',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--flyout_menu_icon_color',
							'element'  => '.fusion-flyout-menu-icons',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'flyout_menu_icon_hover_color'    => [
					'label'       => esc_html__( 'Flyout Menu Icon Hover Color', 'Avada' ),
					'description' => esc_html__( 'Controls the hover color of the flyout menu icons.', 'Avada' ),
					'id'          => 'flyout_menu_icon_hover_color',
					'default'     => 'var(--awb-color4)',
					'type'        => 'color-alpha',
					'class'       => 'fusion-gutter-and-or',
					'required'    => [
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'header_position',
							'operator' => '=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '=',
							'value'    => 'flyout',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--flyout_menu_icon_hover_color',
							'element'  => '.fusion-flyout-menu-icons',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'flyout_menu_background_color'    => [
					'label'       => esc_html__( 'Flyout Menu Background Color', 'Avada' ),
					'description' => esc_html__( 'Controls the background color of the flyout menu', 'Avada' ),
					'id'          => 'flyout_menu_background_color',
					'default'     => 'hsla(var(--awb-color1-h),var(--awb-color1-s),var(--awb-color1-l),calc(var(--awb-color1-a) - 6%))',
					'type'        => 'color-alpha',
					'class'       => 'fusion-gutter-and-or',
					'required'    => [
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'header_position',
							'operator' => '=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '=',
							'value'    => 'flyout',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--flyout_menu_background_color',
							'element'  => '.fusion-flyout-menu-bg',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'flyout_menu_direction'           => [
					'label'       => esc_html__( 'Flyout Menu Direction', 'Avada' ),
					'description' => esc_html__( 'Controls the direction the flyout menu starts from.', 'Avada' ),
					'id'          => 'flyout_menu_direction',
					'default'     => 'fade',
					'type'        => 'select',
					'class'       => 'fusion-gutter-and-or',
					'choices'     => [
						'fade'   => esc_html__( 'Fade', 'Avada' ),
						'left'   => esc_html__( 'Left', 'Avada' ),
						'right'  => esc_html__( 'Right', 'Avada' ),
						'bottom' => esc_html__( 'Bottom', 'Avada' ),
						'top'    => esc_html__( 'Top', 'Avada' ),
					],
					'required'    => [
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'header_position',
							'operator' => '=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '=',
							'value'    => 'flyout',
						],
					],
					'output'      => [
						[
							'element'       => '.fusion-logo-alignment',
							'function'      => 'attr',
							'attr'          => 'class',
							'value_pattern' => 'avada-flyout-menu-direction-$',
							'remove_attrs'  => [ 'avada-flyout-menu-direction-fade', 'avada-flyout-menu-direction-left', 'avada-flyout-menu-direction-right', 'avada-flyout-menu-direction-bottom', 'avada-flyout-menu-direction-top' ],
						],
					],
				],
				'flyout_menu_item_padding'        => [
					'label'       => esc_html__( 'Flyout Menu Item Padding', 'Avada' ),
					'description' => esc_html__( 'Controls the padding between flyout menu items.', 'Avada' ),
					'id'          => 'flyout_menu_item_padding',
					'default'     => '32',
					'type'        => 'slider',
					'choices'     => [
						'min'  => '0',
						'max'  => '100',
						'step' => '1',
					],
					'class'       => 'fusion-gutter-and-or',
					'required'    => [
						[
							'setting'  => 'header_layout',
							'operator' => '=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'header_position',
							'operator' => '=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '=',
							'value'    => 'flyout',
						],
					],
					'css_vars'    => [
						[
							'name'          => '--flyout_menu_item_padding',
							'element'       => '.fusion-flyout-menu',
							'value_pattern' => '$px',
						],
					],
				],
			],
		],
		'heading_secondary_top_menu' => [
			'label'    => esc_html__( 'Secondary Top Menu', 'Avada' ),
			'id'       => 'heading_secondary_top_menu',
			'priority' => 6,
			'type'     => 'sub-section',
			'hidden'   => $has_global_header,
			'fields'   => [
				'no_secondary_menu_note'          => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? [] : [
					'label'       => '',
					'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Secondary Top Menu Options are only available when using Header Layouts #2-5. Your current Header Layout does not utilize the secondary top menu.', 'Avada' ) . '</div>',
					'id'          => 'no_secondary_menu_note',
					'type'        => 'custom',
					'required'    => [
						[
							'setting'  => 'header_position',
							'operator' => '==',
							'value'    => 'top',
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
							'value'    => 'v4',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v5',
						],
					],
				],
				'topmenu_dropwdown_width'         => [
					'label'       => esc_html__( 'Secondary Menu Dropdown Width', 'Avada' ),
					'description' => esc_html__( 'Controls the width of the secondary menu dropdown.', 'Avada' ),
					'id'          => 'topmenu_dropwdown_width',
					'default'     => '200',
					'type'        => 'slider',
					'choices'     => [
						'min'  => '0',
						'max'  => '500',
						'step' => '1',
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
							'name'          => '--topmenu_dropwdown_width',
							'element'       => '.fusion-secondary-menu',
							'value_pattern' => '$px',
						],
					],
				],
				'header_top_first_border_color'   => [
					'label'       => esc_html__( 'Secondary Menu Divider Color', 'Avada' ),
					'description' => esc_html__( 'Controls the divider color of the first level secondary menu.', 'Avada' ),
					'id'          => 'header_top_first_border_color',
					'default'     => 'var(--awb-color6)',
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
							'name'     => '--header_top_first_border_color',
							'element'  => '.fusion-secondary-menu',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'header_top_sub_bg_color'         => [
					'label'       => esc_html__( 'Secondary Menu Dropdown Background Color', 'Avada' ),
					'description' => esc_html__( 'Controls the background color of the secondary menu dropdown.', 'Avada' ),
					'id'          => 'header_top_sub_bg_color',
					'default'     => 'var(--awb-color1)',
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
							'name'     => '--header_top_sub_bg_color',
							'element'  => '.fusion-secondary-menu',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'header_top_menu_bg_hover_color'  => [
					'label'       => esc_html__( 'Secondary Menu Dropdown Background Hover Color', 'Avada' ),
					'description' => esc_html__( 'Controls the background hover color of the secondary menu dropdown.', 'Avada' ),
					'id'          => 'header_top_menu_bg_hover_color',
					'default'     => 'var(--awb-color2)',
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
							'name'     => '--header_top_menu_bg_hover_color',
							'element'  => '.fusion-secondary-menu',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'header_top_menu_sub_sep_color'   => [
					'label'       => esc_html__( 'Secondary Menu Dropdown Separator Color', 'Avada' ),
					'description' => esc_html__( 'Controls the color of the separators in the secondary menu dropdown.', 'Avada' ),
					'id'          => 'header_top_menu_sub_sep_color',
					'default'     => 'var(--awb-color3)',
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
							'name'     => '--header_top_menu_sub_sep_color',
							'element'  => '.fusion-secondary-menu',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'secondary_menu_typography_info'  => [
					'label'    => esc_html__( 'Secondary Top Menu Typography', 'Avada' ),
					'id'       => 'secondary_menu_typography_info',
					'type'     => 'info',
					'class'    => 'fusion-or-gutter',
					'required' => [
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
				],
				'snav_font_size'                  => [
					'label'       => esc_html__( 'Secondary Menu Font Size', 'Avada' ),
					'description' => esc_html__( 'Controls the font size for secondary menu text.', 'Avada' ),
					'id'          => 'snav_font_size',
					'default'     => '12px',
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
							'name' => '--snav_font_size',
						],
					],
				],
				'sec_menu_lh'                     => [
					'label'       => esc_html__( 'Secondary Menu Line Height', 'Avada' ),
					'description' => esc_html__( 'Controls the line height for secondary menu.', 'Avada' ),
					'id'          => 'sec_menu_lh',
					'default'     => '48px',
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
							'name' => '--sec_menu_lh',
						],
						[
							'name'     => '--top-bar-height',
							'element'  => '.fusion-header',
							'callback' => [
								'conditional_return_value',
								[
									'value_pattern' => [ 'calc($ / 2)', '21.5px' ],
									'conditions'    => [
										[ 'sec_menu_lh', '>', '43' ],
									],
								],
							],
						],
					],
				],
				'snav_color'                      => [
					'label'       => esc_html__( 'Secondary Menu Font Color', 'Avada' ),
					'description' => esc_html__( 'Controls the color for secondary menu text.', 'Avada' ),
					'id'          => 'snav_color',
					'default'     => 'var(--awb-color1)',
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
							'name'     => '--snav_color',
							'element'  => '.fusion-secondary-header',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'header_top_menu_sub_color'       => [
					'label'       => esc_html__( 'Secondary Menu Dropdown Font Color', 'Avada' ),
					'description' => esc_html__( 'Controls the color for secondary menu dropdown text.', 'Avada' ),
					'id'          => 'header_top_menu_sub_color',
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
							'name'     => '--header_top_menu_sub_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'header_top_menu_sub_hover_color' => [
					'label'       => esc_html__( 'Secondary Menu Dropdown Font Hover Color', 'Avada' ),
					'description' => esc_html__( 'Controls the hover color for secondary menu dropdown text.', 'Avada' ),
					'id'          => 'header_top_menu_sub_hover_color',
					'default'     => 'var(--awb-color4)',
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
							'name'     => '--header_top_menu_sub_hover_color',
							'element'  => '.fusion-secondary-menu',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
			],
		],
		'heading_mobile_menu'        => [
			'label'    => esc_html__( 'Mobile Menu', 'Avada' ),
			'id'       => 'heading_mobile_menu',
			'priority' => 6,
			'type'     => 'sub-section',
			'hidden'   => $has_global_header,
			'fields'   => [
				'no_responsive_mode_info_1'      => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? [] : [
					'label'       => '',
					'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Please enable responsive mode. Mobile menus are only available when you\'re using the responsive mode. To enable it please go to the "Responsive" section and set the "Responsive Design" option to ON.', 'Avada' ) . '</div>',
					'id'          => 'no_responsive_mode_info_1',
					'type'        => 'custom',
					'required'    => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '0',
						],
					],
				],
				'no_mobile_menu_note'            => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? [] : [
					'label'       => '',
					'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Because of the design of your Header Layout #6, only a few options are available here. More options are available when using Header Layouts #1-5 or 7. The rest of the options for Header Layout #6 are on the Flyout Menu and Main Menu tab.', 'Avada' ) . '</div>',
					'id'          => 'no_mobile_menu_note',
					'type'        => 'custom',
					'required'    => [
						[
							'setting'  => 'header_layout',
							'operator' => '==',
							'value'    => 'v6',
						],
						[
							'setting'  => 'header_position',
							'operator' => '==',
							'value'    => 'top',
						],
					],
				],
				'mobile_menu_design'             => [
					'label'           => esc_html__( 'Mobile Menu Design Style', 'Avada' ),
					'description'     => esc_html__( 'Controls the design of the mobile menu. Flyout design style only allows parent level menu items.', 'Avada' ),
					'id'              => 'mobile_menu_design',
					'default'         => 'classic',
					'type'            => 'radio-buttonset',
					'class'           => 'fusion-gutter-and-or-and',
					'choices'         => [
						'classic' => esc_html__( 'Classic', 'Avada' ),
						'modern'  => esc_html__( 'Modern', 'Avada' ),
						'flyout'  => esc_html__( 'Flyout', 'Avada' ),
					],
					'required'        => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
					],
					'output'          => [
						[
							'element'           => 'body',
							'function'          => 'attr',
							'attr'              => 'class',
							'value_pattern'     => 'mobile-menu-design-$',
							'remove_attrs'      => [ 'mobile-menu-design-classic', 'mobile-menu-design-modern', 'mobile-menu-design-flyout' ],
							'sanitize_callback' => '__return_empty_string',
						],
					],
					// Partial refresh for the header.
					'partial_refresh' => [
						'mobile_menu_design_header_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'mobile_menu_design_header_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'mobile_menu_design_header' => [
							'selector'              => '.fusion-header-wrapper',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'header-rendered',
						],
					],
				],
				'mobile_menu_icons_top_margin'   => [
					'label'       => esc_html__( 'Mobile Menu Icons Top Margin', 'Avada' ),
					'description' => esc_html__( 'Controls the top margin for the icons in the modern and flyout mobile menu design.', 'Avada' ),
					'id'          => 'mobile_menu_icons_top_margin',
					'default'     => '2',
					'type'        => 'slider',
					'class'       => 'fusion-gutter-and-and-or-and-and',
					'choices'     => [
						'min'  => '0',
						'max'  => '200',
						'step' => '1',
					],
					'required'    => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'classic',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'classic',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
					],
					'css_vars'    => [
						[
							'name'          => '--mobile_menu_icons_top_margin',
							'value_pattern' => '$px',
						],
					],
				],
				'mobile_menu_nav_height'         => [
					'label'       => esc_html__( 'Mobile Menu Dropdown Item Height', 'Avada' ),
					'description' => esc_html__( 'Controls the height of each dropdown menu item.', 'Avada' ),
					'id'          => 'mobile_menu_nav_height',
					'default'     => '42',
					'type'        => 'slider',
					'class'       => 'fusion-gutter-and-and-or-and-and',
					'choices'     => [
						'min'  => '0',
						'max'  => '200',
						'step' => '1',
					],
					'required'    => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
					],
					'css_vars'    => [
						[
							'name'          => '--mobile_menu_nav_height',
							'value_pattern' => '$px',
						],
					],
				],
				'mobile_nav_submenu_slideout'    => [
					'label'       => esc_html__( 'Mobile Menu Dropdown Slide Outs', 'Avada' ),
					'description' => esc_html__( 'Turn on to allow dropdown sections to slide out when tapped.', 'Avada' ),
					'id'          => 'mobile_nav_submenu_slideout',
					'default'     => '1',
					'type'        => 'switch',
					'class'       => 'fusion-gutter-and-and-or-and-and',
					'required'    => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
					],
					'output'      => [
						// This is for the avadaMenuVars.submenu_slideout var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'globalVar' => 'avadaMenuVars',
									'id'        => 'submenu_slideout',
									'trigger'   => [ 'fusionMobileMenu' ],
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'mobile_menu_search'             => [
					'label'           => esc_html__( 'Display Mobile Menu Search Icon/Field', 'Avada' ),
					'description'     => esc_html__( 'Turn on to display the search icon/field in the mobile menu.', 'Avada' ),
					'id'              => 'mobile_menu_search',
					'default'         => '1',
					'type'            => 'switch',
					'required'        => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
					],
					// Partial refresh for the header.
					'partial_refresh' => [
						'mobile_menu_search_header_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'mobile_menu_search_header_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'mobile_menu_search_header' => [
							'selector'              => '.fusion-header-wrapper',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'header-rendered',
						],
					],
				],
				'mobile_menu_submenu_indicator'  => [
					'label'       => esc_html__( 'Mobile Menu Sub-Menu Indicator', 'Avada' ),
					'description' => esc_html__( 'Turn on to display the mobile menu sub-menu indicator: "-".', 'Avada' ),
					'id'          => 'mobile_menu_submenu_indicator',
					'default'     => '1',
					'type'        => 'switch',
					'required'    => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
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
									'element'   => '.fusion-mobile-nav-holder',
									'className' => 'fusion-mobile-menu-indicator-hide',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
				'mobile_header_bg_color'         => [
					'label'           => esc_html__( 'Mobile Header Background Color', 'Avada' ),
					'description'     => esc_html__( 'Controls the background color of the header on mobile devices.', 'Avada' ),
					'id'              => 'mobile_header_bg_color',
					'default'         => 'var(--awb-color1)',
					'type'            => 'color-alpha',
					'required'        => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
					],
					'css_vars'        => [
						[
							'name'     => '--mobile_header_bg_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
					'output'          => [
						[
							'element'           => 'helperElement',
							'property'          => 'dummy',
							'callback'          => [
								'toggle_class',
								[
									'condition' => [ '', 'not-opaque' ],
									'element'   => 'html',
									'className' => 'avada-mobile-header-color-not-opaque',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
					'update_callback' => [
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => false,
						],
					],
				],
				'mobile_archive_header_bg_color' => [
					'label'           => esc_html__( 'Mobile Archive Header Background Color', 'Avada' ),
					'description'     => esc_html__( 'Controls the background color of the archive page header on mobile devices.', 'Avada' ),
					'id'              => 'mobile_archive_header_bg_color',
					'type'            => 'color-alpha',
					'default'         => 'var(--awb-color1)',
					'required'        => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
					],
					'css_vars'        => [
						[
							'name'     => '--mobile_header_bg_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
					'update_callback' => [
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
				'mobile_menu_background_color'   => [
					'label'       => esc_html__( 'Mobile Menu Background Color', 'Avada' ),
					'description' => esc_html__( 'Controls the background color of the mobile menu dropdown and classic mobile menu box.', 'Avada' ),
					'id'          => 'mobile_menu_background_color',
					'default'     => 'var(--awb-color1)',
					'type'        => 'color-alpha',
					'class'       => 'fusion-gutter-and-and-or-and-and',
					'required'    => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--mobile_menu_background_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'mobile_menu_hover_color'        => [
					'label'       => esc_html__( 'Mobile Menu Background Hover Color', 'Avada' ),
					'description' => esc_html__( 'Controls the background hover color of the mobile menu dropdown.', 'Avada' ),
					'id'          => 'mobile_menu_hover_color',
					'default'     => 'var(--awb-color1)',
					'type'        => 'color-alpha',
					'class'       => 'fusion-gutter-and-and-or-and-and',
					'required'    => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--mobile_menu_hover_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'mobile_menu_border_color'       => [
					'label'       => esc_html__( 'Mobile Menu Border Color', 'Avada' ),
					'description' => esc_html__( 'Controls the border and divider colors of the mobile menu dropdown and classic mobile menu box.', 'Avada' ),
					'id'          => 'mobile_menu_border_color',
					'default'     => 'var(--awb-color3)',
					'type'        => 'color-alpha',
					'class'       => 'fusion-gutter-and-and-or-and-and',
					'required'    => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--mobile_menu_border_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'mobile_menu_toggle_color'       => [
					'label'       => esc_html__( 'Mobile Menu Toggle Color', 'Avada' ),
					'description' => esc_html__( 'Controls the color of the mobile menu toggle icon.', 'Avada' ),
					'id'          => 'mobile_menu_toggle_color',
					'default'     => 'var(--awb-color6)',
					'type'        => 'color-alpha',
					'class'       => 'fusion-gutter-and-and-or-and-and',
					'required'    => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--mobile_menu_toggle_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'mobile_menu_typography_info'    => [
					'label'       => esc_html__( 'Mobile Menu Typography', 'Avada' ),
					'description' => '',
					'id'          => 'mobile_menu_typography_info',
					'type'        => 'info',
					'required'    => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
					],
				],
				'mobile_menu_typography'         => [
					'id'                        => 'mobile_menu_typography',
					'label'                     => esc_html__( 'Mobile Menu Typography', 'Avada' ),
					'description'               => esc_html__( 'These settings control the typography for mobile menu.', 'Avada' ),
					'type'                      => 'typography',
					'global'                    => true,
					'text_transform_no_inherit' => true,
					'class'                     => 'fusion-gutter-and-or-and',
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
						'font-family'    => 'var(--awb-typography3-font-family)',
						'font-size'      => 'var(--awb-typography3-font-size)',
						'font-weight'    => '400',
						'line-height'    => 'var(--awb-typography3-line-height)',
						'letter-spacing' => 'var(--awb-typography3-letter-spacing)',
						'color'          => 'var(--awb-color8)',
						'text-transform' => 'none',
					],
					'required'                  => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
					],
					'css_vars'                  => [
						[
							'name'     => '--mobile_menu_typography-font-family',
							'choice'   => 'font-family',
							'callback' => [ 'combined_font_family', 'mobile_menu_typography' ],
						],
						[
							'name'   => '--mobile_menu_typography-font-size',
							'choice' => 'font-size',
						],
						[
							'name'     => '--mobile_menu_typography-font-weight',
							'choice'   => 'font-weight',
							'callback' => [ 'font_weight_no_regular', '' ],
						],
						[
							'name'   => '--mobile_menu_typography-line-height',
							'choice' => 'line-height',
						],
						[
							'name'     => '--mobile_menu_typography-letter-spacing',
							'choice'   => 'letter-spacing',
							'callback' => [ 'maybe_append_px', '' ],
						],
						[
							'name'   => '--mobile_menu_typography-color',
							'choice' => 'color',
						],
						[
							'name'   => '--mobile_menu_typography-font-style',
							'choice' => 'font-style',
						],
						[
							'name'   => '--mobile_menu_typography-font-weight',
							'choice' => 'font-weight',
						],
						[
							'name'   => '--mobile_menu_typography-text-transform',
							'choice' => 'text-transform',
						],
						[
							'name'     => '--mobile_menu_typography-font-size-30-or-24px',
							'choice'   => 'font-size',
							'callback' => [
								'conditional_return_value',
								[
									'value_pattern' => [ '30px', '24px' ],
									'conditions'    => [
										[ 'mobile_menu_typography[font-size]', '>', '35' ],
									],
								],
							],
						],
						[
							'name'     => '--mobile_menu_typography-font-size-open-submenu',
							'choice'   => 'font-size',
							'callback' => [
								'conditional_return_value',
								[
									'value_pattern' => [ '20px', 'var(--mobile_menu_typography-font-size, 13px)' ],
									'conditions'    => [
										[ 'mobile_menu_typography[font-size]', '>', '30' ],
									],
								],
							],
						],
					],
				],
				'mobile_menu_font_hover_color'   => [
					'label'       => esc_html__( 'Mobile Menu Hover Color', 'Avada' ),
					'description' => esc_html__( 'Controls the hover color of the mobile menu item. Also, used to highlight current mobile menu item.', 'Avada' ),
					'id'          => 'mobile_menu_font_hover_color',
					'default'     => 'var(--awb-color8)',
					'type'        => 'color-alpha',
					'class'       => 'fusion-gutter-and-or-and',
					'required'    => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
					],
					'css_vars'    => [
						[
							'name'     => '--mobile_menu_font_hover_color',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'mobile_menu_text_align'         => [
					'label'       => esc_html__( 'Mobile Menu Text Align', 'Avada' ),
					'description' => esc_html__( 'Controls the mobile menu text alignment.', 'Avada' ),
					'id'          => 'mobile_menu_text_align',
					'default'     => 'left',
					'choices'     => [
						'left'   => esc_html__( 'Left', 'Avada' ),
						'center' => esc_html__( 'Center', 'Avada' ),
						'right'  => esc_html__( 'Right', 'Avada' ),
					],
					'type'        => 'radio-buttonset',
					'class'       => 'fusion-gutter-and-and-or-and-and',
					'required'    => [
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_position',
							'operator' => '!=',
							'value'    => 'top',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
						[
							'setting'  => 'responsive',
							'operator' => '==',
							'value'    => '1',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'mobile_menu_design',
							'operator' => '!=',
							'value'    => 'flyout',
						],
					],
					'output'      => [
						( class_exists( 'SitePress' ) ) ? [
							'element'       => [ '.fusion-mobile-nav-holder .wpml-ls-item .menu-text', '.wpml-ls-item .menu-text, .wpml-ls-item .sub-menu a > span', '.fusion-mobile-nav-holder .wpml-ls-item > a' ],
							'property'      => 'justify-content',
							'value_pattern' => 'center',
							'exclude'       => [ 'left', 'right' ],
							'media_query'   => 'fusion-max-sh-shbp',
						] : [],
						( class_exists( 'SitePress' ) ) ? [
							'element'       => [ '.fusion-mobile-nav-holder .wpml-ls-item .menu-text', '.wpml-ls-item .menu-text, .wpml-ls-item .sub-menu a > span', '.fusion-mobile-nav-holder .wpml-ls-item > a' ],
							'property'      => 'justify-content',
							'value_pattern' => ( is_rtl() ) ? 'flex-end' : 'flex-start',
							'exclude'       => [ 'center', 'right' ],
							'media_query'   => 'fusion-max-sh-shbp',
						] : [],
						( class_exists( 'SitePress' ) ) ? [
							'element'       => [ '.fusion-mobile-nav-holder .wpml-ls-item .menu-text', '.wpml-ls-item .menu-text, .wpml-ls-item .sub-menu a > span', '.fusion-mobile-nav-holder .wpml-ls-item > a' ],
							'property'      => 'justify-content',
							'value_pattern' => ( is_rtl() ) ? 'flex-start' : 'flex-end',
							'exclude'       => [ 'left', 'center' ],
							'media_query'   => 'fusion-max-sh-shbp',
						] : [],
						[
							'element'       => 'nav.fusion-mobile-nav-holder',
							'function'      => 'attr',
							'attr'          => 'class',
							'value_pattern' => 'fusion-mobile-menu-text-align-$',
							'remove_attrs'  => [ 'fusion-mobile-menu-text-align-left', 'fusion-mobile-menu-text-align-center', 'fusion-mobile-menu-text-align-right' ],
							'callback'      => [
								'conditional_return_value',
								[
									'conditions' => [
										[ 'mobile_menu_design', '!==', 'flyout' ],
									],
								],
							],
						],
					],
				],
			],
		],
		'mega_menu_subsection'       => [
			'label'  => esc_html__( 'Mega Menu', 'Avada' ),
			'id'     => 'mega_menu_subsection',
			'type'   => 'sub-section',
			'fields' => [
				'header_v6_used_note'             => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? [] : [
					'label'       => '',
					'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Mega Menu Options are only available when using Header Layouts #1-5. Your current Header Layout #6 does not utilize the mega menu.', 'Avada' ) . '</div>',
					'id'          => 'header_v6_used_note',
					'type'        => 'custom',
					'required'    => [
						[
							'setting'  => 'header_layout',
							'operator' => '==',
							'value'    => 'v6',
						],
					],
				],
				'megamenu_disabled_note'          => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? [] : [
					'label'       => '',
					'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Mega Menu is disabled in Advanced > Theme Features section. Please enable it to see the options.', 'Avada' ) . '</div>',
					'id'          => 'megamenu_disabled_note',
					'type'        => 'custom',
					'required'    => [
						[
							'setting'  => 'disable_megamenu',
							'operator' => '=',
							'value'    => '0',
						],
					],
				],
				'megamenu_width'                  => [
					'label'           => esc_html__( 'Mega Menu Wrapper Max Width', 'Avada' ),
					'description'     => esc_html__( 'Controls the max width of the mega menu. On boxed side header layouts, "Viewport Width" will match "Site Width".', 'Avada' ),
					'id'              => 'megamenu_width',
					'type'            => 'radio-buttonset',
					'default'         => 'site_width',
					'choices'         => [
						'site_width'     => esc_html__( 'Site Width', 'Avada' ),
						'viewport_width' => esc_html__( '100% Width', 'Avada' ),
						'custom_width'   => esc_html__( 'Custom Width', 'Avada' ),
					],
					'required'        => [
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'disable_megamenu',
							'operator' => '=',
							'value'    => '1',
						],
					],
					'output'          => [
						// This is for the avadaMenuVars.megamenu_base_width var.
						[
							'element'           => 'helperElement',
							'property'          => 'bottom',
							'js_callback'       => [
								'fusionGlobalScriptSet',
								[
									'globalVar' => 'avadaMenuVars',
									'id'        => 'megamenu_base_width',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
					// See https://github.com/Theme-Fusion/Fusion-Builder/issues/4785.
					'transport'       => 'refresh',
					// Partial refresh for the header.
					'partial_refresh' => [
						'megamenu_width_header_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'megamenu_width_header_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'mmegamenu_width_header' => [
							'selector'              => '.fusion-header-wrapper',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'header-rendered',
						],
					],
				],
				'megamenu_max_width'              => [
					'label'           => esc_html__( 'Mega Menu Max-Width', 'Avada' ),
					'description'     => esc_html__( 'Controls the max width of the mega menu.', 'Avada' ),
					'id'              => 'megamenu_max_width',
					'default'         => '1200',
					'type'            => 'slider',
					'choices'         => [
						'min'  => '0',
						'max'  => '4096',
						'step' => '1',
					],
					'required'        => [
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'disable_megamenu',
							'operator' => '=',
							'value'    => '1',
						],
						[
							'setting'  => 'megamenu_width',
							'operator' => '=',
							'value'    => 'custom_width',
						],
					],
					// Partial refresh for the header.
					'partial_refresh' => [
						'megamenu_max_width_header_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'megamenu_max_width_header_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'megamenu_max_width_header' => [
							'selector'              => '.fusion-header-wrapper',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'header-rendered',
						],
					],
				],
				'megamenu_interior_content_width' => [
					'label'           => esc_html__( 'Mega Menu Interior Content Width', 'Avada' ),
					'description'     => esc_html__( 'For full width mega menus select if the interior menu content is contained to site width or 100% width.', 'Avada' ),
					'id'              => 'megamenu_interior_content_width',
					'type'            => 'radio-buttonset',
					'default'         => 'viewport_width',
					'choices'         => [
						'site_width'     => esc_html__( 'Site Width', 'Avada' ),
						'viewport_width' => esc_html__( '100% Width', 'Avada' ),
					],
					'required'        => [
						[
							'setting'  => 'header_position',
							'operator' => '=',
							'value'    => 'top',
						],
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'disable_megamenu',
							'operator' => '=',
							'value'    => '1',
						],
						[
							'setting'  => 'megamenu_width',
							'operator' => '=',
							'value'    => 'viewport_width',
						],
					],
					// Partial refresh for the header.
					'partial_refresh' => [
						'megamenu_width_header_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'megamenu_width_header_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'mmegamenu_width_header' => [
							'selector'              => '.fusion-header-wrapper',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'header-rendered',
						],
					],
				],
				'megamenu_title_size'             => [
					'label'       => esc_html__( 'Mega Menu Column Title Size', 'Avada' ),
					'description' => esc_html__( 'Controls the font size for mega menu column titles.', 'Avada' ),
					'id'          => 'megamenu_title_size',
					'default'     => '18px',
					'hidden'      => $has_global_header,
					'type'        => 'dimension',
					'required'    => [
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'disable_megamenu',
							'operator' => '=',
							'value'    => '1',
						],
					],
					'css_vars'    => [
						[
							'name'    => '--megamenu_title_size',
							'element' => '.fusion-megamenu-title',
						],
					],
				],
				'megamenu_item_vertical_padding'  => [
					'label'       => esc_html__( 'Mega Menu Dropdown Item Padding', 'Avada' ),
					'description' => esc_html__( 'Controls the top/bottom padding for mega menu dropdown items.', 'Avada' ),
					'id'          => 'megamenu_item_vertical_padding',
					'default'     => '7',
					'type'        => 'slider',
					'choices'     => [
						'min'  => '0',
						'max'  => '50',
						'step' => '1',
					],
					'required'    => [
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'disable_megamenu',
							'operator' => '=',
							'value'    => '1',
						],
					],
					'css_vars'    => [
						[
							'name'          => '--megamenu_item_vertical_padding',
							'element'       => '.fusion-megamenu-submenu',
							'value_pattern' => '$px',
						],
					],
				],
				'megamenu_item_display_divider'   => [
					'label'       => esc_html__( 'Mega Menu Item Divider', 'Avada' ),
					'description' => esc_html__( 'Turn on to display a divider between mega menu dropdown items.', 'Avada' ),
					'id'          => 'megamenu_item_display_divider',
					'default'     => '0',
					'type'        => 'switch',
					'required'    => [
						[
							'setting'  => 'header_layout',
							'operator' => '!=',
							'value'    => 'v6',
						],
						[
							'setting'  => 'disable_megamenu',
							'operator' => '=',
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
									'className' => 'avada-has-megamenu-item-divider',
								],
							],
							'sanitize_callback' => '__return_empty_string',
						],
					],
				],
			],
		],
		'menu_icons_subsection'      => [
			'label'  => esc_html__( 'Main Menu Icons', 'Avada' ),
			'id'     => 'menu_icons_subsection',
			'type'   => 'sub-section',
			'hidden' => $has_global_header,
			'fields' => [
				'menu_icons_note'       => [
					'label'       => '',
					'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Icons are available for both the main and dropdown menus. However, the options below only apply to the main menu. Dropdown menu icons do not use these options below, they follow the dropdown font size and color. The icons themselves can be added to your menu items in the Appearance > Menus section.', 'Avada' ) . '</div>',
					'id'          => 'menu_icons_note',
					'type'        => 'custom',
				],
				'menu_icon_position'    => [
					'label'           => esc_html__( 'Main Menu Icon Position', 'Avada' ),
					'description'     => esc_html__( 'Controls the main menu icon position.', 'Avada' ),
					'id'              => 'menu_icon_position',
					'default'         => 'left',
					'choices'         => [
						'top'    => esc_html__( 'Top', 'Avada' ),
						'right'  => esc_html__( 'Right', 'Avada' ),
						'bottom' => esc_html__( 'Bottom', 'Avada' ),
						'left'   => esc_html__( 'Left', 'Avada' ),
					],
					'type'            => 'radio-buttonset',
					'output'          => [
						[
							'element'       => 'body',
							'function'      => 'attr',
							'attr'          => 'class',
							'value_pattern' => 'avada-menu-icon-position-$',
							'remove_attrs'  => [ 'avada-menu-icon-position-top', 'avada-menu-icon-position-right', 'avada-menu-icon-position-bottom', 'avada-menu-icon-position-left' ],
						],
					],
					// Partial refresh for the header.
					'partial_refresh' => [
						'menu_icon_position_header_remove_before_hook' => [
							'selector'            => '.avada-hook-before-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'menu_icon_position_header_remove_after_hook' => [
							'selector'            => '.avada-hook-after-header-wrapper',
							'container_inclusive' => true,
							'render_callback'     => '__return_null',
						],
						'menu_icon_position_header' => [
							'selector'              => '.fusion-header-wrapper',
							'container_inclusive'   => true,
							'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
							'success_trigger_event' => 'header-rendered',
						],
					],
				],
				'menu_icon_size'        => [
					'label'       => esc_html__( 'Main Menu Icon Size', 'Avada' ),
					'description' => esc_html__( 'Controls the size of the top-level menu icons.', 'Avada' ),
					'id'          => 'menu_icon_size',
					'default'     => '14',
					'type'        => 'slider',
					'choices'     => [
						'min'  => '0',
						'max'  => '100',
						'step' => '1',
					],
					'css_vars'    => [
						[
							'name'          => '--menu_icon_size',
							'value_pattern' => '$px',
						],
					],
				],
				'menu_icon_color'       => [
					'label'       => esc_html__( 'Main Menu Icon Color', 'Avada' ),
					'description' => esc_html__( 'Controls the color of the top-level main menu icons.', 'Avada' ),
					'id'          => 'menu_icon_color',
					'default'     => 'var(--awb-color6)',
					'type'        => 'color-alpha',
					'css_vars'    => [
						[
							'name'     => '--menu_icon_color',
							'element'  => '.fusion-megamenu-icon',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'menu_icon_hover_color' => [
					'label'       => esc_html__( 'Main Menu Icon Hover Color', 'Avada' ),
					'description' => esc_html__( 'Controls the hover color of the top-level main menu icons.', 'Avada' ),
					'id'          => 'menu_icon_hover_color',
					'default'     => 'var(--awb-color4)',
					'type'        => 'color-alpha',
					'css_vars'    => [
						[
							'name'     => '--menu_icon_hover_color',
							'element'  => '.fusion-megamenu-icon',
							'callback' => [ 'sanitize_color' ],
						],
					],
				],
				'menu_thumbnail_size'   => [
					'label'       => esc_html__( 'Mega Menu Thumbnail Size', 'Avada' ),
					'description' => esc_html__( 'Controls the width and height of the top-level mega menu thumbnails. Use "auto" for automatic resizing if you added either width or height.', 'Avada' ),
					'id'          => 'menu_thumbnail_size',
					'units'       => false,
					'default'     => [
						'width'  => '26px',
						'height' => '14px',
					],
					'type'        => 'dimensions',
					'required'    => [
						[
							'setting'  => 'disable_megamenu',
							'operator' => '==',
							'value'    => '1',
						],
					],
					'css_vars'    => [
						[
							'name'    => '--menu_thumbnail_size-width',
							'choice'  => 'width',
							'element' => '.fusion-main-menu',
						],
						[
							'name'    => '--menu_thumbnail_size-height',
							'choice'  => 'height',
							'element' => '.fusion-main-menu',
						],
					],
				],
			],
		],
	];

	$sections['menu']['fields'] = array_merge( $sections['menu']['fields'], $fields );

	return $sections;

}

<?php
/**
 * Header Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

/**
 * Header page settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_header( $sections ) {

	$sections['header'] = [
		'label'    => esc_html__( 'Header', 'Avada' ),
		'id'       => 'header',
		'alt_icon' => 'fusiona-header',
		'fields'   => [],
	];

	// Template override, add notice and hide rest.
	$override = function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_override( 'header' ) : false;
	if ( $override ) {
		$sections['header']['fields']['header_info'] = [
			'id'          => 'headerinfo',
			'label'       => '',
			/* translators: The edit link. Text of link is the title. */
			'description' => '<div class="fusion-redux-important-notice">' . Fusion_Template_Builder()->get_override_text( $override, 'header' ) . '</div>',
			'dependency'  => [],
			'type'        => 'custom',
		];
		return $sections;
	}

	$menus                  = get_terms(
		'nav_menu',
		[
			'hide_empty' => false,
		]
	);
	$menu_select['default'] = 'Default Menu';

	foreach ( $menus as $menu ) {
		$menu_select[ $menu->term_id ] = $menu->name;
	}

	$header_bg_color = Fusion_Color::new_color(
		[
			'color'    => Avada()->settings->get( 'header_bg_color' ),
			'fallback' => '#ffffff',
		]
	);

	$sections['header']['fields'] = [
		'display_header'         => [
			'id'          => 'display_header',
			'label'       => esc_html__( 'Display Header', 'Avada' ),
			'choices'     => [
				'yes' => esc_html__( 'Yes', 'Avada' ),
				'no'  => esc_html__( 'No', 'Avada' ),
			],
			'default'     => 'yes',
			'description' => esc_html__( 'Choose to show or hide the header.', 'Avada' ),
			'dependency'  => [],
			'type'        => 'radio-buttonset',
		],
		'header_100_width'       => [
			'id'          => 'header_100_width',
			'label'       => esc_html__( '100% Header Width', 'Avada' ),
			'choices'     => [
				'default' => esc_html__( 'Default', 'Avada' ),
				'yes'     => esc_html__( 'Yes', 'Avada' ),
				'no'      => esc_html__( 'No', 'Avada' ),
			],
			'default'     => 'default',
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose to set header width to 100&#37; of the browser width. Select "No" for site width. %s', 'Avada' ), Avada()->settings->get_default_description( 'header_100_width', '', 'yesno' ) ),
			'dependency'  => [
				[
					'field'      => 'display_header',
					'value'      => 'yes',
					'comparison' => '==',
				],
			],
			'type'        => 'radio-buttonset',
			'map'         => 'yesno',
		],
		'header_bg_color'        => [
			'id'          => 'header_bg_color',
			'label'       => esc_html__( 'Background Color', 'Avada' ),
			'default'     => Avada()->settings->get( 'header_bg_color' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Controls the background color for the header. Hex code or rgba value, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'header_bg_color' ) ),
			'dependency'  => [
				[
					'field'      => 'display_header',
					'value'      => 'yes',
					'comparison' => '==',
				],
			],
			'type'        => 'color-alpha',
		],
		'mobile_header_bg_color' => [
			'id'          => 'mobile_header_bg_color',
			'label'       => esc_html__( 'Mobile Header Background Color', 'Avada' ),
			'default'     => Avada()->settings->get( 'mobile_header_bg_color' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Controls the background color for the header on mobile devices. Hex code or rgba value, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'mobile_header_bg_color' ) ),
			'dependency'  => [
				[
					'field'      => 'display_header',
					'value'      => 'yes',
					'comparison' => '==',
				],
			],
			'type'        => 'color-alpha',
		],
		'header_bg_image'        => [
			'id'          => 'header_bg_image',
			'label'       => esc_html__( 'Background Image', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Select an image for the header background. If left empty, the header background color will be used. For top headers the image displays on top of the header background color and will only display if header opacity is set to 1. For side headers the image displays behind the header background color so the header opacity must be set below 1 to see the image. %s', 'Avada' ), Avada()->settings->get_default_description( 'header_bg_image', 'url' ) ),
			'dependency'  => [
				[
					'field'      => 'display_header',
					'value'      => 'yes',
					'comparison' => '==',
				],
			],
			'type'        => 'media',
		],
		'header_bg_full'         => [
			'id'          => 'header_bg_full',
			'label'       => esc_html__( '100% Background Image', 'Avada' ),
			'description' => esc_html__( 'Choose to have the background image display at 100%.', 'Avada' ),
			'choices'     => [
				'no'  => esc_html__( 'No', 'Avada' ),
				'yes' => esc_html__( 'Yes', 'Avada' ),
			],
			'dependency'  => [
				[
					'field'      => 'display_header',
					'value'      => 'yes',
					'comparison' => '==',
				],
				[
					'field'      => 'header_bg_image',
					'value'      => '',
					'comparison' => '!=',
				],
			],
			'type'        => 'radio-buttonset',
		],
		'header_bg_repeat'       => [
			'id'          => 'header_bg_repeat',
			'label'       => esc_html__( 'Background Repeat', 'Avada' ),
			'description' => esc_html__( 'Select how the background image repeats.', 'Avada' ),
			'choices'     => [
				'repeat'    => esc_html__( 'Tile', 'Avada' ),
				'repeat-x'  => esc_html__( 'Tile Horizontally', 'Avada' ),
				'repeat-y'  => esc_html__( 'Tile Vertically', 'Avada' ),
				'no-repeat' => esc_html__( 'No Repeat', 'Avada' ),
			],
			'dependency'  => [
				[
					'field'      => 'display_header',
					'value'      => 'yes',
					'comparison' => '==',
				],
				[
					'field'      => 'header_bg_image',
					'value'      => '',
					'comparison' => '!=',
				],
			],
			'type'        => 'select',
		],
		'displayed_menu'         => [
			'id'          => 'displayed_menu',
			'label'       => esc_html__( 'Main Navigation Menu', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Select which menu displays on this page. %s', 'Avada' ), Avada()->settings->get_default_description( 'main_navigation', '', 'menu' ) ),
			'choices'     => $menu_select,
			'dependency'  => [
				[
					'field'      => 'display_header',
					'value'      => 'yes',
					'comparison' => '==',
				],
			],
			'type'        => 'select',
		],
	];
	return $sections;
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

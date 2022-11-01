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
 * Color settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_colors( $sections ) {

	$sections['colors'] = [
		'label'    => esc_html__( 'Colors', 'Avada' ),
		'id'       => 'colors',
		'priority' => 3,
		'icon'     => 'el-icon-brush',
		'alt_icon' => 'fusiona-color-dropper',
		'fields'   => [
			'colors_important_note_info' => [
				'label'         => '',
				'description'   => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> This tab contains general color options. Additional color options for specific areas, can be found within other tabs. Example: For menu color options go to the menu tab.', 'Avada' ) . '</div>',
				'id'            => 'colors_important_note_info',
				'type'          => 'custom',
				'hide_on_front' => true,
			],
			'color_palette'              => [
				'label'       => esc_html__( 'Color Palette', 'Avada' ),
				'description' => __( 'Set your global color palette. The colors defined here can be used from other global options, from page options and element options. For best results, the 8 colors of the core palette should be set from lightest to darkest. <strong>IMPORTANT NOTE:</strong> If a global color that is used by other options gets deleted, these corresponding options will display a default color instead. Colors are internally stored with a fixed counter. Thus, adding a new color after deleting an old one, will set the same internal name to the new color.', 'Avada' ),
				'id'          => 'color_palette',
				'default'     => AWB_Global_Colors()->get_defaults(),
				'type'        => 'color-palette',
			],
			'primary_color'              => [
				'label'       => esc_html__( 'Primary Color', 'Avada' ),
				'description' => esc_html__( 'Controls the main highlight color throughout the website.', 'Avada' ),
				'id'          => 'primary_color',
				'default'     => 'var(--awb-color5)',
				'type'        => 'color-alpha',
				'css_vars'    => [
					[
						'name'     => '--primary_color',
						'callback' => [ 'sanitize_color' ],
					],
					[
						'name'     => '--primary_color-85a',
						'callback' => [ 'color_alpha_set', '0.85' ],
					],
					[
						'name'     => '--primary_color-7a',
						'callback' => [ 'color_alpha_set', '0.7' ],
					],
					[
						'name'     => '--primary_color-5a',
						'callback' => [ 'color_alpha_set', '0.5' ],
					],
					[
						'name'     => '--primary_color-35a',
						'callback' => [ 'color_alpha_set', '0.35' ],
					],
					[
						'name'     => '--primary_color-2a',
						'callback' => [ 'color_alpha_set', '0.2' ],
					],
				],
			],
		],
	];

	return $sections;

}

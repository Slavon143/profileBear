<?php
/**
 * Studio Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

/**
 * Studio Page Settings.
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_studio( $sections ) {

	$off_canvas_items = class_exists( 'AWB_Off_Canvas_Front_End' ) ? AWB_Off_Canvas_Front_End()->get_available_items() : [];

	$default_colors = [
		1 => '#ffffff',
		2 => '#ffffff',
		3 => '#ffffff',
		4 => '#ffffff',
		5 => '#ffffff',
		6 => '#ffffff',
		7 => '#ffffff',
		8 => '#ffffff',
	];
	if ( class_exists( 'AWB_Global_Colors' ) && class_exists( 'Avada_Studio_Colors' ) ) {
		// Make sure that palette filter run, and original colors are saved.
		$global_colors_class = AWB_Global_Colors();
		$global_colors_class->get_palette();
		// Get original colors.
		$original_colors = Avada_Studio_Colors::$original_colors;

		for ( $i = 1; $i <= 8; $i++ ) {
			if ( isset( $original_colors[ 'color' . $i ]['color'] ) ) {
				$default_colors[ $i ] = $original_colors[ 'color' . $i ]['color'];
			}
		}
	}

	$typography_names = [ '', '', '', '', '', '' ];
	if ( class_exists( 'AWB_Global_Typography' ) && class_exists( 'Avada_Studio_Typography' ) ) {
		// Make sure that palette filter run, and original colors are saved.
		$global_typo_class = AWB_Global_Typography();
		$global_typo_data  = $global_typo_class->get_typography();

		foreach ( $global_typo_data as $typo_slug => $typo_data ) {
			$matches = [];
			preg_match( '/^typography(\d)$/', $typo_slug, $matches );
			if ( ! isset( $matches[1] ) ) {
				continue;
			}
			$typo_number = $matches[1];
			if ( isset( $global_typo_data[ $typo_slug ]['label'] ) ) {
				$typography_names[ $typo_number ] = $global_typo_data[ $typo_slug ]['label'];
			}
		}
	}

	$sections['studio'] = [
		'label'    => esc_attr__( 'Studio', 'Avada' ),
		'id'       => 'studio',
		'alt_icon' => 'fusiona-footer',
		'fields'   => [
			'exclude_form_studio'   => [
				'id'          => 'exclude_form_studio',
				'label'       => esc_html__( 'Exclude from Studio', 'Avada' ),
				'choices'     => [
					'yes' => esc_attr__( 'Yes', 'Avada' ),
					'no'  => esc_attr__( 'No', 'Avada' ),
				],
				'description' => esc_html__( 'Choose to include or exclude this template from studio content.', 'Avada' ),
				'type'        => 'radio-buttonset',
				'map'         => 'yesno',
				'transport'   => 'postMessage',
				'default'     => 'no',
			],
			'setup_content'         => [
				'id'          => 'setup_content',
				'label'       => esc_html__( 'Setup Wizard Content', 'Avada' ),
				'choices'     => [
					'yes' => esc_attr__( 'Yes', 'Avada' ),
					'no'  => esc_attr__( 'No', 'Avada' ),
				],
				'description' => esc_html__( 'Select if this is special setup wizard content.', 'Avada' ),
				'type'        => 'radio-buttonset',
				'map'         => 'yesno',
				'transport'   => 'postMessage',
				'default'     => 'no',
			],
			'off_canvases'          => [
				'type'        => 'multiple_select',
				'label'       => esc_html__( 'Select Referenced Off Canvases', 'Avada' ),
				'description' => esc_html__( 'Select off canvases which are referenced in this item. Leaving blank if none.', 'Avada' ),
				'id'          => 'off_canvases',
				'choices'     => $off_canvas_items,
				'transport'   => 'postMessage',
			],
			'preview_off_canvas'    => [
				'type'        => 'select',
				'label'       => esc_html__( 'Preview Off Canvas', 'Avada' ),
				'description' => esc_html__( 'Select off canvas which is added to the item markup.', 'Avada' ),
				'id'          => 'preview_off_canvas',
				'choices'     => [ '' => esc_attr__( 'None', 'Avada' ) ] + $off_canvas_items,
				'transport'   => 'postMessage',
			],
			'color1_overwrite'      => [
				'id'            => 'color1_overwrite',
				'label'         => esc_html__( 'Color 1 Overwrite', 'Avada' ),
				'description'   => esc_html__( 'Overwrite with a new color.', 'Avada' ),
				'type'          => 'color-alpha',
				'transport'     => 'postMessage',
				'allow_globals' => false,
				'default'       => $default_colors[1],
			],
			'color2_overwrite'      => [
				'id'            => 'color2_overwrite',
				'label'         => esc_html__( 'Color 2 Overwrite', 'Avada' ),
				'description'   => esc_html__( 'Overwrite with a new color.', 'Avada' ),
				'type'          => 'color-alpha',
				'transport'     => 'postMessage',
				'allow_globals' => false,
				'default'       => $default_colors[2],
			],
			'color3_overwrite'      => [
				'id'            => 'color3_overwrite',
				'label'         => esc_html__( 'Color 3 Overwrite', 'Avada' ),
				'description'   => esc_html__( 'Overwrite with a new color.', 'Avada' ),
				'type'          => 'color-alpha',
				'transport'     => 'postMessage',
				'allow_globals' => false,
				'default'       => $default_colors[3],
			],
			'color4_overwrite'      => [
				'id'            => 'color4_overwrite',
				'label'         => esc_html__( 'Color 4 Overwrite', 'Avada' ),
				'description'   => esc_html__( 'Overwrite with a new color.', 'Avada' ),
				'type'          => 'color-alpha',
				'transport'     => 'postMessage',
				'allow_globals' => false,
				'default'       => $default_colors[4],
			],
			'color5_overwrite'      => [
				'id'            => 'color5_overwrite',
				'label'         => esc_html__( 'Color 5 Overwrite', 'Avada' ),
				'description'   => esc_html__( 'Overwrite with a new color.', 'Avada' ),
				'type'          => 'color-alpha',
				'transport'     => 'postMessage',
				'allow_globals' => false,
				'default'       => $default_colors[5],
			],
			'color6_overwrite'      => [
				'id'            => 'color6_overwrite',
				'label'         => esc_html__( 'Color 6 Overwrite', 'Avada' ),
				'description'   => esc_html__( 'Overwrite with a new color.', 'Avada' ),
				'type'          => 'color-alpha',
				'transport'     => 'postMessage',
				'allow_globals' => false,
				'default'       => $default_colors[6],
			],
			'color7_overwrite'      => [
				'id'            => 'color7_overwrite',
				'label'         => esc_html__( 'Color 7 Overwrite', 'Avada' ),
				'description'   => esc_html__( 'Overwrite with a new color.', 'Avada' ),
				'type'          => 'color-alpha',
				'transport'     => 'postMessage',
				'allow_globals' => false,
				'default'       => $default_colors[7],
			],
			'color8_overwrite'      => [
				'id'            => 'color8_overwrite',
				'label'         => esc_html__( 'Color 8 Overwrite', 'Avada' ),
				'description'   => esc_html__( 'Overwrite with a new color.', 'Avada' ),
				'type'          => 'color-alpha',
				'transport'     => 'postMessage',
				'allow_globals' => false,
				'default'       => $default_colors[8],
			],
			'h1_size'               => [
				'type'        => 'text',
				'label'       => esc_html__( 'H1 Font Size', 'fusion-builder' ),
				'description' => esc_html__( 'Enter font size for H1s.', 'fusion-builder' ),
				'id'          => 'h1_size',
				'transport'   => 'postMessage',
			],
			'h2_size'               => [
				'type'        => 'text',
				'label'       => esc_html__( 'H2 Font Size', 'fusion-builder' ),
				'description' => esc_html__( 'Enter font size for H2s.', 'fusion-builder' ),
				'id'          => 'h2_size',
				'transport'   => 'postMessage',
			],
			'h3_size'               => [
				'type'        => 'text',
				'label'       => esc_html__( 'H3 Font Size', 'fusion-builder' ),
				'description' => esc_html__( 'Enter font size for H3s.', 'fusion-builder' ),
				'id'          => 'h3_size',
				'transport'   => 'postMessage',
			],
			'h4_size'               => [
				'type'        => 'text',
				'label'       => esc_html__( 'H4 Font Size', 'fusion-builder' ),
				'description' => esc_html__( 'Enter font size for H4s.', 'fusion-builder' ),
				'id'          => 'h4_size',
				'transport'   => 'postMessage',
			],
			'h5_size'               => [
				'type'        => 'text',
				'label'       => esc_html__( 'H5 Font Size', 'fusion-builder' ),
				'description' => esc_html__( 'Enter font size for H5s.', 'fusion-builder' ),
				'id'          => 'h5_size',
				'transport'   => 'postMessage',
			],
			'h6_size'               => [
				'type'        => 'text',
				'label'       => esc_html__( 'H6 Font Size', 'fusion-builder' ),
				'description' => esc_html__( 'Enter font size for H6s.', 'fusion-builder' ),
				'id'          => 'h6_size',
				'transport'   => 'postMessage',
			],
			'typography1_overwrite' => [
				'id'            => 'typography1_overwrite',
				/* translators: %s - Name of typography. */
				'label'         => esc_html( sprintf( __( '(%s) Typography 1 Overwrite', 'Avada' ), $typography_names[1] ) ),
				'description'   => esc_html__( 'Overwrite with a new typography.', 'Avada' ),
				'type'          => 'typography',
				'transport'     => 'postMessage',
				'allow_globals' => false,
				'choices'       => [
					'font-family'    => true,
					'font-size'      => true,
					'font-weight'    => true,
					'line-height'    => true,
					'letter-spacing' => true,
					'text-transform' => true,
				],
				'default'       => [
					'font-family'    => '',
					'font-size'      => '',
					'font-weight'    => '',
					'line-height'    => '',
					'letter-spacing' => '',
					'text-transform' => '',
				],
			],
			'typography2_overwrite' => [
				'id'            => 'typography2_overwrite',
				/* translators: %s - Name of typography. */
				'label'         => esc_html( sprintf( __( '(%s) Typography 2 Overwrite', 'Avada' ), $typography_names[2] ) ),
				'description'   => esc_html__( 'Overwrite with a new typography.', 'Avada' ),
				'type'          => 'typography',
				'transport'     => 'postMessage',
				'allow_globals' => false,
				'choices'       => [
					'font-family'    => true,
					'font-size'      => true,
					'font-weight'    => true,
					'line-height'    => true,
					'letter-spacing' => true,
					'text-transform' => true,
				],
				'default'       => [
					'font-family'    => '',
					'font-size'      => '',
					'font-weight'    => '',
					'line-height'    => '',
					'letter-spacing' => '',
					'text-transform' => '',
				],
			],
			'typography3_overwrite' => [
				'id'            => 'typography3_overwrite',
				/* translators: %s - Name of typography. */
				'label'         => esc_html( sprintf( __( '(%s) Typography 3 Overwrite', 'Avada' ), $typography_names[3] ) ),
				'description'   => esc_html__( 'Overwrite with a new typography.', 'Avada' ),
				'type'          => 'typography',
				'transport'     => 'postMessage',
				'allow_globals' => false,
				'choices'       => [
					'font-family'    => true,
					'font-size'      => true,
					'font-weight'    => true,
					'line-height'    => true,
					'letter-spacing' => true,
					'text-transform' => true,
				],
				'default'       => [
					'font-family'    => '',
					'font-size'      => '',
					'font-weight'    => '',
					'line-height'    => '',
					'letter-spacing' => '',
					'text-transform' => '',
				],
			],
			'typography4_overwrite' => [
				'id'            => 'typography4_overwrite',
				/* translators: %s - Name of typography. */
				'label'         => esc_html( sprintf( __( '(%s) Typography 4 Overwrite', 'Avada' ), $typography_names[4] ) ),
				'description'   => esc_html__( 'Overwrite with a new typography.', 'Avada' ),
				'type'          => 'typography',
				'transport'     => 'postMessage',
				'allow_globals' => false,
				'choices'       => [
					'font-family'    => true,
					'font-size'      => true,
					'font-weight'    => true,
					'line-height'    => true,
					'letter-spacing' => true,
					'text-transform' => true,
				],
				'default'       => [
					'font-family'    => '',
					'font-size'      => '',
					'font-weight'    => '',
					'line-height'    => '',
					'letter-spacing' => '',
					'text-transform' => '',
				],
			],
			'typography5_overwrite' => [
				'id'            => 'typography5_overwrite',
				/* translators: %s - Name of typography. */
				'label'         => esc_html( sprintf( __( '(%s) Typography 5 Overwrite', 'Avada' ), $typography_names[5] ) ),
				'description'   => esc_html__( 'Overwrite with a new typography.', 'Avada' ),
				'type'          => 'typography',
				'transport'     => 'postMessage',
				'allow_globals' => false,
				'choices'       => [
					'font-family'    => true,
					'font-size'      => true,
					'font-weight'    => true,
					'line-height'    => true,
					'letter-spacing' => true,
					'text-transform' => true,
				],
				'default'       => [
					'font-family'    => '',
					'font-size'      => '',
					'font-weight'    => '',
					'line-height'    => '',
					'letter-spacing' => '',
					'text-transform' => '',
				],
			],
		],
	];

	return $sections;
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

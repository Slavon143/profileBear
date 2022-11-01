<?php
/**
 * Footer Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

/**
 * Footer page settings.
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_footer( $sections ) {

	$sections['footer'] = [
		'label'    => esc_attr__( 'Footer', 'Avada' ),
		'id'       => 'footer',
		'alt_icon' => 'fusiona-footer',
		'fields'   => [],
	];

	// Template override, add notice and hide rest.
	$override = function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_override( 'footer' ) : false;
	if ( $override ) {
		$sections['footer']['fields']['footer_info'] = [
			'id'          => 'footerinfo',
			'label'       => '',
			/* translators: The edit link. Text of link is the title. */
			'description' => '<div class="fusion-redux-important-notice">' . Fusion_Template_Builder()->get_override_text( $override, 'footer' ) . '</div>',
			'dependency'  => [],
			'type'        => 'custom',
		];
		return $sections;
	}

	$sections['footer']['fields']['footer_widgets'] = [
		'id'              => 'footer_widgets',
		'label'           => esc_attr__( 'Display Widgets Areas', 'Avada' ),
		'choices'         => [
			'default' => esc_attr__( 'Default', 'Avada' ),
			'yes'     => esc_attr__( 'Yes', 'Avada' ),
			'no'      => esc_attr__( 'No', 'Avada' ),
		],
		/* translators: Additional description (defaults). */
		'description'     => sprintf( esc_html__( 'Choose to show or hide the footer widget areas. %s', 'Avada' ), Avada()->settings->get_default_description( 'footer_widgets', '', 'yesno' ) ),
		'type'            => 'radio-buttonset',
		'map'             => 'yesno',
		'default'         => 'default',
		'transport'       => 'postMessage',
		'partial_refresh' => [
			'footer_content_footer_widgets' => [
				'selector'            => '.fusion-footer',
				'container_inclusive' => false,
				'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'footer' ],
			],
		],
	];
	$sections['footer']['fields']['footer_copyright'] = [
		'id'              => 'footer_copyright',
		'label'           => esc_attr__( 'Display Copyright Area', 'Avada' ),
		'choices'         => [
			'default' => esc_attr__( 'Default', 'Avada' ),
			'yes'     => esc_attr__( 'Yes', 'Avada' ),
			'no'      => esc_attr__( 'No', 'Avada' ),
		],
		/* translators: Additional description (defaults). */
		'description'     => sprintf( esc_html__( 'Choose to show or hide the copyright area. %s', 'Avada' ), Avada()->settings->get_default_description( 'footer_copyright', '', 'yesno' ) ),
		'type'            => 'radio-buttonset',
		'map'             => 'yesno',
		'default'         => 'default',
		'transport'       => 'postMessage',
		'partial_refresh' => [
			'footer_content_footer_widgets' => [
				'selector'            => '.fusion-footer',
				'container_inclusive' => false,
				'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'footer' ],
			],
		],
	];
	$sections['footer']['fields']['footer_100_width'] = [
		'id'          => 'footer_100_width',
		'label'       => esc_html__( '100% Footer Width', 'Avada' ),
		'choices'     => [
			'default' => esc_attr__( 'Default', 'Avada' ),
			'yes'     => esc_attr__( 'Yes', 'Avada' ),
			'no'      => esc_attr__( 'No', 'Avada' ),
		],
		/* translators: Additional description (defaults). */
		'description' => sprintf( esc_html__( 'Choose to set footer width to 100&#37; of the browser width. Select "No" for site width. %s', 'Avada' ), Avada()->settings->get_default_description( 'footer_100_width', '', 'yesno' ) ),
		'type'        => 'radio-buttonset',
		'map'         => 'yesno',
		'default'     => 'default',
	];
	return $sections;
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

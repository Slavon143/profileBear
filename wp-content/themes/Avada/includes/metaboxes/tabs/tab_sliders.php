<?php
/**
 * Sliders Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

/**
 * Sliders page settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_sliders( $sections ) {
	global $wpdb;

	$active_slider_types = avada_get_available_sliders_dropdown();
	$sliders_array       = avada_get_available_sliders_array();

	$sections['sliders'] = [
		'label'    => esc_html__( 'Sliders', 'Avada' ),
		'id'       => 'sliders',
		'alt_icon' => 'fusiona-slider',
		'fields'   => [],
	];

	if ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) {
		// Click here for Avada Slider, Revolution Slider or Layer Slider.
		$sections['sliders']['fields']['sliders_note'] = [
			'label'       => '',
			'description' => '<div class="fusion-redux-important-notice">' . avada_get_sliders_note( $sliders_array, $active_slider_types ) . '</div>',
			'id'          => 'sliders_note',
			'type'        => 'custom',
		];
	}

	$sections['sliders']['fields']['slider_type'] = [
		'id'              => 'slider_type',
		'label'           => esc_attr__( 'Slider Type', 'Avada' ),
		'description'     => esc_html__( 'Select the type of slider that displays.', 'Avada' ),
		'choices'         => $active_slider_types,
		'default'         => 'no',
		'dependency'      => [],
		'type'            => 'select',
		'transport'       => 'postMessage',
		'partial_refresh' => [
			'fusion_slider_change' => [
				'selector'            => '#sliders-container',
				'container_inclusive' => false,
				'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'avada_slider' ],
			],
		],
	];

	if ( class_exists( 'LS_Sliders' ) ) {
		$sections['sliders']['fields']['slider'] = [
			'id'              => 'slider',
			'label'           => esc_attr__( 'Select LayerSlider', 'Avada' ),
			'description'     => esc_html__( 'Select the unique name of the slider.', 'Avada' ),
			'choices'         => $sliders_array['layer_sliders'],
			'dependency'      => [
				[
					'field'      => 'slider_type',
					'value'      => 'layer',
					'comparison' => '==',
				],
			],
			'type'            => 'select',
			'transport'       => 'postMessage',
			'partial_refresh' => [
				'fusion_slider_change' => [
					'selector'            => '#sliders-container',
					'container_inclusive' => false,
					'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'avada_slider' ],
				],
			],
		];
	}

	if ( method_exists( 'FusionCore_Plugin', 'get_fusion_sliders' ) ) {

		$sections['sliders']['fields']['wooslider'] = [
			'id'              => 'wooslider',
			'label'           => esc_attr__( 'Select Avada Slider', 'Avada' ),
			'description'     => esc_html__( 'Select the unique name of the slider.', 'Avada' ),
			'choices'         => $sliders_array['fusion_sliders'],
			'dependency'      => [
				[
					'field'      => 'slider_type',
					'value'      => 'flex',
					'comparison' => '==',
				],
			],
			'type'            => 'select',
			'transport'       => 'postMessage',
			'partial_refresh' => [
				'fusion_slider_change' => [
					'selector'            => '#sliders-container',
					'container_inclusive' => false,
					'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'avada_slider' ],
				],
			],
		];
	}

	if ( function_exists( 'rev_slider_shortcode' ) ) {
		$sections['sliders']['fields']['revslider'] = [
			'id'              => 'revslider',
			'label'           => esc_attr__( 'Select Slider Revolution Slider', 'Avada' ),
			'description'     => esc_html__( 'Select the unique name of the slider.', 'Avada' ),
			'choices'         => $sliders_array['rev_sliders'],
			'dependency'      => [
				[
					'field'      => 'slider_type',
					'value'      => 'rev',
					'comparison' => '==',
				],
			],
			'type'            => 'select',
			'transport'       => 'postMessage',
			'partial_refresh' => [
				'fusion_slider_change' => [
					'selector'            => '#sliders-container',
					'container_inclusive' => false,
					'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'avada_slider' ],
				],
			],
		];
	}

	if ( true === taxonomy_exists( 'themefusion_es_groups' ) ) {
		$sections['sliders']['fields']['elasticslider'] = [
			'id'              => 'elasticslider',
			'label'           => esc_attr__( 'Select Elastic Slider', 'Avada' ),
			'description'     => esc_html__( 'Select the unique name of the slider.', 'Avada' ),
			'choices'         => $sliders_array['elastic_sliders'],
			'dependency'      => [
				[
					'field'      => 'slider_type',
					'value'      => 'elastic',
					'comparison' => '==',
				],
			],
			'type'            => 'select',
			'transport'       => 'postMessage',
			'partial_refresh' => [
				'fusion_slider_change' => [
					'selector'            => '#sliders-container',
					'container_inclusive' => false,
					'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'avada_slider' ],
				],
			],
		];
	}

	$sections['sliders']['fields']['slider_position'] = [
		'id'          => 'slider_position',
		'label'       => esc_attr__( 'Slider Position', 'Avada' ),
		/* translators: Additional description (defaults). */
		'description' => sprintf( esc_html__( 'Select if the slider shows below or above the header. Only works for top header position. %s', 'Avada' ), Avada()->settings->get_default_description( 'slider_position', '', 'select' ) ),
		'choices'     => [
			'default' => esc_attr__( 'Default', 'Avada' ),
			'below'   => esc_attr__( 'Below', 'Avada' ),
			'above'   => esc_attr__( 'Above', 'Avada' ),
		],
		'default'     => 'default',
		'dependency'  => [
			[
				'field'      => 'slider_type',
				'value'      => 'no',
				'comparison' => '!=',
			],
		],
		'type'        => 'radio-buttonset',
	];

	$sections['sliders']['fields']['avada_rev_styles'] = [
		'id'          => 'avada_rev_styles',
		'label'       => esc_attr__( 'Disable Avada Styles For Slider Revolution', 'Avada' ),
		/* translators: Additional description (defaults). */
		'description' => sprintf( esc_html__( 'Choose to enable or disable Avada styles for Slider Revolution. %s', 'Avada' ), Avada()->settings->get_default_description( 'avada_rev_styles', '', 'reverseyesno' ) ),
		'choices'     => [
			'default' => esc_attr__( 'Default', 'Avada' ),
			'yes'     => esc_attr__( 'Yes', 'Avada' ),
			'no'      => esc_attr__( 'No', 'Avada' ),
		],
		'default'     => 'default',
		'dependency'  => [
			[
				'field'      => 'slider_type',
				'value'      => 'rev',
				'comparison' => '==',
			],
		],
		'type'        => 'radio-buttonset',
		'map'         => 'reverseyesno',
	];

	$sections['sliders']['fields']['fallback'] = [
		'id'          => 'fallback',
		'label'       => esc_attr__( 'Slider Fallback Image', 'Avada' ),
		'description' => esc_html__( 'This image will override the slider on mobile devices.', 'Avada' ),
		'dependency'  => [
			[
				'field'      => 'slider_type',
				'value'      => 'no',
				'comparison' => '!=',
			],
			[
				'field'      => 'slider_type',
				'value'      => '',
				'comparison' => '!=',
			],
		],
		'type'        => 'media',
	];
	$is_builder                                = isset( $_GET['builder'] ) && isset( $_GET['builder_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$sections['sliders']['fields']['slider_visibility'] = [
		'id'          => 'slider_visibility',
		'label'       => esc_attr__( 'Slider Visibility', 'Avada' ),
		/* translators: Global Options link. */
		'description' => sprintf( esc_html__( 'Choose to show or hide the slider on small, medium or large screens. You can choose more than one at a time. Each of the 3 sizes has a custom width setting on the Responsive tab in the. %s.', 'Avada' ), '<a href="' . Avada()->settings->get_setting_link( 'visibility_small' ) . '" target="_blank" rel="noopener noreferrer">' . apply_filters( 'fusion_options_label', esc_html__( 'Element Options', 'Avada' ) ) . '</a>' ),
		'value'       => [
			'small-visibility'  => $is_builder ? '<span class="fusiona-mobile"></span>|' . esc_attr__( 'Small Screen', 'Avada' ) : esc_attr__( 'Small Screen', 'Avada' ),
			'medium-visibility' => $is_builder ? '<span class="fusiona-tablet"></span>|' . esc_attr__( 'Medium Screen', 'Avada' ) : esc_attr__( 'Medium Screen', 'Avada' ),
			'large-visibility'  => $is_builder ? '<span class="fusiona-desktop"></span>|' . esc_attr__( 'Large Screen', 'Avada' ) : esc_attr__( 'Large Screen', 'Avada' ),
		],
		'default'     => 'small-visibility,medium-visibility,large-visibility',
		'dependency'  => [
			[
				'field'      => 'slider_type',
				'value'      => 'no',
				'comparison' => '!=',
			],
		],
		'transport'   => 'postMessage',
		'output'      => [
			[
				'element'           => 'helperElement',
				'property'          => 'dummy',
				'callback'          => [
					'change_slider_visibility',
					[
						'element' => '#sliders-container',
					],
				],
				'sanitize_callback' => '__return_empty_string',
			],
		],
		'type'        => 'checkbox-buttonset',
	];

	$sections['sliders']['fields']['demo_slider'] = [
		'id'      => 'demo_slider',
		'default' => '',
		'type'    => 'hidden',
	];

	return $sections;
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

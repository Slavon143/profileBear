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
 * Elastic Slider
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_elastic_slider( $sections ) {

	$sections['elastic_slider'] = [
		'label'    => esc_html__( 'Elastic Slider', 'Avada' ),
		'id'       => 'heading_elastic_slider',
		'priority' => 20,
		'icon'     => 'el-icon-photo-alt',
		'alt_icon' => 'fusiona-images',
		'fields'   => [
			'tfes_disabled_note'   => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? [] : [
				'label'       => '',
				'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Elastic Slider is disabled in Advanced > Post Types section. Please enable it to see the options.', 'Avada' ) . '</div>',
				'id'          => 'tfes_disabled_note',
				'type'        => 'custom',
				'required'    => [
					[
						'setting'  => 'status_eslider',
						'operator' => '=',
						'value'    => '0',
					],
				],
			],
			'tfes_dimensions'      => [
				'label'       => esc_html__( 'Elastic Slider Dimensions', 'Avada' ),
				'description' => esc_html__( 'Controls the width and height for the elastic slider.', 'Avada' ),
				'id'          => 'tfes_dimensions',
				'units'       => false,
				'default'     => [
					'width'  => '100%',
					'height' => '400px',
				],
				'type'        => 'dimensions',
				'required'    => [
					[
						'setting'  => 'status_eslider',
						'operator' => '=',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'    => '--tfes_dimensions-width',
						'choice'  => 'width',
						'element' => '.ei-slider',
					],
					[
						'name'    => '--tfes_dimensions-height',
						'choice'  => 'height',
						'element' => '.ei-slider',
					],
				],
			],
			'tfes_animation'       => [
				'label'       => esc_html__( 'Animation Type', 'Avada' ),
				'description' => esc_html__( 'Controls if the elastic slides animate from the sides or center.', 'Avada' ),
				'id'          => 'tfes_animation',
				'default'     => 'sides',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'sides'  => esc_html__( 'Sides', 'Avada' ),
					'center' => esc_html__( 'Center', 'Avada' ),
				],
				'required'    => [
					[
						'setting'  => 'status_eslider',
						'operator' => '=',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the avadaElasticSliderVars.tfes_animation var.
					[
						'element'           => 'helperElement',
						'property'          => 'bottom',
						'choice'            => 'top',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'choice'    => 'top',
								'globalVar' => 'avadaElasticSliderVars',
								'id'        => 'tfes_animation',
								'trigger'   => [ 'load' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'tfes_autoplay'        => [
				'label'       => esc_html__( 'Autoplay', 'Avada' ),
				'description' => esc_html__( 'Turn on to autoplay the elastic slides.', 'Avada' ),
				'id'          => 'tfes_autoplay',
				'default'     => '1',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'status_eslider',
						'operator' => '=',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the avadaElasticSliderVars.tfes_autoplay var.
					[
						'element'           => 'helperElement',
						'property'          => 'bottom',
						'choice'            => 'top',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'choice'    => 'top',
								'globalVar' => 'avadaElasticSliderVars',
								'id'        => 'tfes_autoplay',
								'trigger'   => [ 'load' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'tfes_interval'        => [
				'label'       => esc_html__( 'Slideshow Interval', 'Avada' ),
				'description' => esc_html__( 'Controls how long each elastic slide is visible. ex: 1000 = 1 second.', 'Avada' ),
				'id'          => 'tfes_interval',
				'default'     => '3000',
				'type'        => 'slider',
				'choices'     => [
					'min'  => '0',
					'max'  => '30000',
					'step' => '50',
				],
				'required'    => [
					[
						'setting'  => 'status_eslider',
						'operator' => '=',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the avadaElasticSliderVars.tfes_interval var.
					[
						'element'           => 'helperElement',
						'property'          => 'bottom',
						'choice'            => 'top',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'choice'    => 'top',
								'globalVar' => 'avadaElasticSliderVars',
								'id'        => 'tfes_interval',
								'trigger'   => [ 'load' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'tfes_speed'           => [
				'label'       => esc_html__( 'Sliding Speed', 'Avada' ),
				'description' => esc_html__( 'Controls the speed of the elastic slider slideshow. ex: 1000 = 1 second.', 'Avada' ),
				'id'          => 'tfes_speed',
				'default'     => '800',
				'type'        => 'slider',
				'choices'     => [
					'min'  => '0',
					'max'  => '5000',
					'step' => '50',
				],
				'required'    => [
					[
						'setting'  => 'status_eslider',
						'operator' => '=',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the avadaElasticSliderVars.tfes_speed var.
					[
						'element'           => 'helperElement',
						'property'          => 'bottom',
						'choice'            => 'top',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'choice'    => 'top',
								'globalVar' => 'avadaElasticSliderVars',
								'id'        => 'tfes_speed',
								'trigger'   => [ 'load' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'tfes_width'           => [
				'label'       => esc_html__( 'Thumbnail Width', 'Avada' ),
				'description' => esc_html__( 'Controls the width of the elastic slider thumbnail images.', 'Avada' ),
				'id'          => 'tfes_width',
				'default'     => '150',
				'type'        => 'slider',
				'choices'     => [
					'min'  => '0',
					'step' => '1',
					'max'  => '500',
					'edit' => 'yes',
				],
				'required'    => [
					[
						'setting'  => 'status_eslider',
						'operator' => '=',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the avadaElasticSliderVars.tfes_width var.
					[
						'element'           => 'helperElement',
						'property'          => 'bottom',
						'choice'            => 'top',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'choice'    => 'top',
								'globalVar' => 'avadaElasticSliderVars',
								'id'        => 'tfes_width',
								'trigger'   => [ 'load' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'es_title_font_size'   => [
				'label'       => esc_html__( 'Title Font Size', 'Avada' ),
				'description' => esc_html__( 'Controls the font size for elastic slider title.', 'Avada' ),
				'id'          => 'es_title_font_size',
				'default'     => '42px',
				'type'        => 'dimension',
				'required'    => [
					[
						'setting'  => 'status_eslider',
						'operator' => '=',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'    => '--es_title_font_size',
						'element' => '.ei-slider',
					],
				],
			],
			'es_caption_font_size' => [
				'label'       => esc_html__( 'Caption Font Size', 'Avada' ),
				'description' => esc_html__( 'Controls the font size for elastic slider caption.', 'Avada' ),
				'id'          => 'es_caption_font_size',
				'default'     => '20px',
				'type'        => 'dimension',
				'required'    => [
					[
						'setting'  => 'status_eslider',
						'operator' => '=',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name' => '--es_caption_font_size',
					],
				],
			],
			'es_title_color'       => [
				'label'       => esc_html__( 'Title Color', 'Avada' ),
				'description' => esc_html__( 'Controls the color of the elastic slider title.', 'Avada' ),
				'id'          => 'es_title_color',
				'default'     => 'var(--awb-color8)',
				'type'        => 'color-alpha',
				'required'    => [
					[
						'setting'  => 'status_eslider',
						'operator' => '=',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--es_title_color',
						'element'  => '.ei-slider',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'es_caption_color'     => [
				'label'       => esc_html__( 'Caption Color', 'Avada' ),
				'description' => esc_html__( 'Controls the color of the elastic slider caption.', 'Avada' ),
				'id'          => 'es_caption_color',
				'default'     => 'var(--awb-color8)',
				'type'        => 'color-alpha',
				'required'    => [
					[
						'setting'  => 'status_eslider',
						'operator' => '=',
						'value'    => '1',
					],
				],
				'css_vars'    => [
					[
						'name'     => '--es_caption_color',
						'element'  => '.ei-slider',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
		],
	];

	return $sections;

}

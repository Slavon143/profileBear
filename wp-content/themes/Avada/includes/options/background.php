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
 * Background settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_background( $sections ) {

	$sections['background'] = [
		'label'    => esc_html__( 'Background', 'Avada' ),
		'id'       => 'heading_background',
		'priority' => 11,
		'icon'     => 'el-icon-photo',
		'alt_icon' => 'fusiona-image',
		'fields'   => [
			'page_bg_subsection'         => [
				'label'       => esc_html__( 'Page Background', 'Avada' ),
				'description' => '',
				'id'          => 'page_bg_subsection',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'bg_image'          => [
						'label'       => esc_html__( 'Background Image For Page', 'Avada' ),
						'description' => esc_html__( 'Select an image to use for a full page background.', 'Avada' ),
						'id'          => 'bg_image',
						'default'     => '',
						'mod'         => '',
						'type'        => 'media',
						'css_vars'    => [
							[
								'name'     => '--bg_image',
								'choice'   => 'url',
								'callback' => [ 'fallback_to_value', [ 'url("$")', 'none' ] ],
							],
						],
						'output'      => [
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ '', 'has-image' ],
										'element'   => 'html',
										'className' => 'avada-html-has-bg-image',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'bg_full'           => [
						'label'       => esc_html__( '100% Background Image', 'Avada' ),
						'description' => esc_html__( 'Turn on to have the page background image display at 100% in width and height according to the window size.', 'Avada' ),
						'id'          => 'bg_full',
						'default'     => '0',
						'type'        => 'switch',
						'required'    => [
							[
								'setting'  => 'bg_image',
								'operator' => '!=',
								'value'    => '',
							],
							[
								'setting'  => 'bg_image',
								'operator' => '!=',
								'value'    => [
									'url' => '',
								],
							],
							[
								'setting'  => 'bg_image',
								'operator' => '!=',
								'value'    => [
									'url'       => '',
									'id'        => '',
									'height'    => '',
									'width'     => '',
									'thumbnail' => '',
								],
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
										'className' => 'avada-has-bg-image-full',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'bg_repeat'         => [
						'label'       => esc_html__( 'Background Repeat', 'Avada' ),
						'description' => esc_html__( 'Controls how the background image repeats.', 'Avada' ),
						'id'          => 'bg_repeat',
						'default'     => 'no-repeat',
						'type'        => 'select',
						'choices'     => [
							'repeat'    => esc_html__( 'Repeat All', 'Avada' ),
							'repeat-x'  => esc_html__( 'Repeat Horizontally', 'Avada' ),
							'repeat-y'  => esc_html__( 'Repeat Vertically', 'Avada' ),
							'no-repeat' => esc_html__( 'No Repeat', 'Avada' ),
						],
						'required'    => [
							[
								'setting'  => 'bg_image',
								'operator' => '!=',
								'value'    => '',
							],
							[
								'setting'  => 'bg_image',
								'operator' => '!=',
								'value'    => [
									'url' => '',
								],
							],
							[
								'setting'  => 'bg_image',
								'operator' => '!=',
								'value'    => [
									'url'       => '',
									'id'        => '',
									'height'    => '',
									'width'     => '',
									'thumbnail' => '',
								],
							],
						],
						'css_vars'    => [
							[
								'name' => '--bg_repeat',
							],
						],
					],
					'bg_color'          => [
						'label'       => esc_html__( 'Background Color For Page', 'Avada' ),
						'description' => esc_html__( 'Controls the background color for the page. When the color value is set to anything below 100% opacity, the color will overlay the background image if one is uploaded.', 'Avada' ),
						'id'          => 'bg_color',
						'default'     => 'var(--awb-color3)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--bg_color',
								'callback' => [ 'sanitize_color' ],
							],
							[
								'name'     => '--bg-color-overlay',
								'callback' => [
									'return_color_if_opaque',
									[
										'transparent' => 'overlay',
										'opaque'      => 'normal',
									],
								],
							],
						],
					],
					'bg_pattern_option' => [
						'label'       => esc_html__( 'Background Pattern', 'Avada' ),
						'description' => esc_html__( 'Turn on to display a pattern in the page background.', 'Avada' ),
						'id'          => 'bg_pattern_option',
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
										'element'   => 'body,html',
										'className' => 'avada-has-page-background-pattern',
									],

								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'bg_pattern'        => [
						'label'    => esc_html__( 'Select a Background Pattern', 'Avada' ),
						'id'       => 'bg_pattern',
						'default'  => 'pattern1',
						'type'     => 'radio-image',
						'choices'  => [
							'pattern1'  => Avada::$template_dir_url . '/assets/images/patterns/pattern1.png',
							'pattern2'  => Avada::$template_dir_url . '/assets/images/patterns/pattern2.png',
							'pattern3'  => Avada::$template_dir_url . '/assets/images/patterns/pattern3.png',
							'pattern4'  => Avada::$template_dir_url . '/assets/images/patterns/pattern4.png',
							'pattern5'  => Avada::$template_dir_url . '/assets/images/patterns/pattern5.png',
							'pattern6'  => Avada::$template_dir_url . '/assets/images/patterns/pattern6.png',
							'pattern7'  => Avada::$template_dir_url . '/assets/images/patterns/pattern7.png',
							'pattern8'  => Avada::$template_dir_url . '/assets/images/patterns/pattern8.png',
							'pattern9'  => Avada::$template_dir_url . '/assets/images/patterns/pattern9.png',
							'pattern10' => Avada::$template_dir_url . '/assets/images/patterns/pattern10.png',
							'pattern11' => Avada::$template_dir_url . '/assets/images/patterns/pattern11.png',
							'pattern12' => Avada::$template_dir_url . '/assets/images/patterns/pattern12.png',
							'pattern13' => Avada::$template_dir_url . '/assets/images/patterns/pattern13.png',
							'pattern14' => Avada::$template_dir_url . '/assets/images/patterns/pattern14.png',
							'pattern15' => Avada::$template_dir_url . '/assets/images/patterns/pattern15.png',
							'pattern16' => Avada::$template_dir_url . '/assets/images/patterns/pattern16.png',
							'pattern17' => Avada::$template_dir_url . '/assets/images/patterns/pattern17.png',
							'pattern18' => Avada::$template_dir_url . '/assets/images/patterns/pattern18.png',
							'pattern19' => Avada::$template_dir_url . '/assets/images/patterns/pattern19.png',
							'pattern20' => Avada::$template_dir_url . '/assets/images/patterns/pattern20.png',
							'pattern21' => Avada::$template_dir_url . '/assets/images/patterns/pattern21.png',
							'pattern22' => Avada::$template_dir_url . '/assets/images/patterns/pattern22.png',
						],
						'required' => [
							[
								'setting'  => 'bg_pattern_option',
								'operator' => '==',
								'value'    => '1',
							],
						],
						'css_vars' => [
							[
								'name'     => '--bg_pattern',
								'callback' => [ 'fallback_to_value', [ 'url("' . Avada::$template_dir_url . '/assets/images/patterns/$.png")', '' ] ],
							],
						],
					],
				],
			],
			'main_content_bg_subsection' => [
				'label'       => esc_html__( 'Main Content Background', 'Avada' ),
				'description' => '',
				'id'          => 'main_content_bg_subsection',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'content_bg_color'  => [
						'label'       => esc_html__( 'Main Content Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of the main content area.', 'Avada' ),
						'id'          => 'content_bg_color',
						'default'     => 'var(--awb-color1)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--content_bg_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
						'output'      => [
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ '', 'not-opaque' ],
										'element'   => 'html',
										'className' => 'avada-content-bg-not-opaque',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'content_bg_image'  => [
						'label'       => esc_html__( 'Background Image For Main Content Area', 'Avada' ),
						'description' => esc_html__( 'Select an image to use for the main content area background.', 'Avada' ),
						'id'          => 'content_bg_image',
						'default'     => '',
						'mod'         => '',
						'type'        => 'media',
						'css_vars'    => [
							[
								'name'     => '--content_bg_image',
								'choice'   => 'url',
								'callback' => [ 'fallback_to_value', [ 'url("$")', 'none' ] ],
							],
						],
					],
					'content_bg_full'   => [
						'label'       => esc_html__( '100% Background Image', 'Avada' ),
						'description' => esc_html__( 'Turn on to have the main content background image display at 100% in width and height according to the window size.', 'Avada' ),
						'id'          => 'content_bg_full',
						'default'     => '0',
						'type'        => 'switch',
						'required'    => [
							[
								'setting'  => 'content_bg_image',
								'operator' => '!=',
								'value'    => '',
							],
							[
								'setting'  => 'content_bg_image',
								'operator' => '!=',
								'value'    => [
									'url' => '',
								],
							],
							[
								'setting'  => 'content_bg_image',
								'operator' => '!=',
								'value'    => [
									'url'       => '',
									'id'        => '',
									'height'    => '',
									'width'     => '',
									'thumbnail' => '',
								],
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
										'element'   => '#main',
										'className' => 'full-bg',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'content_bg_repeat' => [
						'label'       => esc_html__( 'Background Repeat', 'Avada' ),
						'description' => esc_html__( 'Controls how the background image repeats.', 'Avada' ),
						'id'          => 'content_bg_repeat',
						'default'     => 'no-repeat',
						'type'        => 'select',
						'choices'     => [
							'repeat'    => esc_html__( 'Repeat All', 'Avada' ),
							'repeat-x'  => esc_html__( 'Repeat Horizontally', 'Avada' ),
							'repeat-y'  => esc_html__( 'Repeat Vertically', 'Avada' ),
							'no-repeat' => esc_html__( 'No Repeat', 'Avada' ),
						],
						'required'    => [
							[
								'setting'  => 'content_bg_image',
								'operator' => '!=',
								'value'    => '',
							],
							[
								'setting'  => 'content_bg_image',
								'operator' => '!=',
								'value'    => [
									'url' => '',
								],
							],
							[
								'setting'  => 'content_bg_image',
								'operator' => '!=',
								'value'    => [
									'url'       => '',
									'id'        => '',
									'height'    => '',
									'width'     => '',
									'thumbnail' => '',
								],
							],
						],
						'css_vars'    => [
							[
								'name' => '--content_bg_repeat',
							],
						],
					],
				],
			],
		],
	];

	return $sections;

}

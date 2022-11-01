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
 * Lightbox
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_lightbox( $sections ) {

	$sections['lightbox'] = [
		'label'    => esc_html__( 'Lightbox', 'Avada' ),
		'id'       => 'heading_lightbox',
		'priority' => 21,
		'icon'     => 'el-icon-info-circle',
		'alt_icon' => 'fusiona-uniF602',
		'fields'   => [
			'status_lightbox'           => [
				'label'       => esc_html__( 'Lightbox', 'Avada' ),
				'description' => esc_html__( 'Turn on to enable the lightbox throughout the theme.', 'Avada' ),
				'id'          => 'status_lightbox',
				'default'     => '1',
				'type'        => 'switch',
			],
			'status_lightbox_single'    => [
				'label'       => esc_html__( 'Lightbox For Featured Images On Single Post Pages', 'Avada' ),
				'description' => esc_html__( 'Turn on to enable the lightbox on single blog and portfolio posts for the main featured images.', 'Avada' ),
				'id'          => 'status_lightbox_single',
				'default'     => '1',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
			],
			'lightbox_behavior'         => [
				'label'       => esc_html__( 'Lightbox Behavior', 'Avada' ),
				'description' => esc_html__( 'Controls what the lightbox displays for single blog and portfolio posts.', 'Avada' ),
				'id'          => 'lightbox_behavior',
				'default'     => 'all',
				'type'        => 'select',
				'choices'     => [
					'all'        => esc_html__( 'First featured image of every post', 'Avada' ),
					'individual' => esc_html__( 'Only featured images of individual post', 'Avada' ),
				],
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
			],
			'lightbox_skin'             => [
				'label'       => esc_html__( 'Lightbox Skin', 'Avada' ),
				'description' => esc_html__( 'Controls the lightbox skin design.', 'Avada' ),
				'id'          => 'lightbox_skin',
				'default'     => 'metro-white',
				'type'        => 'select',
				'choices'     => [
					'light'       => esc_html__( 'Light', 'Avada' ),
					'dark'        => esc_html__( 'Dark', 'Avada' ),
					'mac'         => esc_html__( 'Mac', 'Avada' ),
					'metro-black' => esc_html__( 'Metro Black', 'Avada' ),
					'metro-white' => esc_html__( 'Metro White', 'Avada' ),
					'parade'      => esc_html__( 'Parade', 'Avada' ),
					'smooth'      => esc_html__( 'Smooth', 'Avada' ),
				],
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the fusionLightboxVars.lightbox_skin var.
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionLightboxVars',
								'id'        => 'lightbox_skin',
								'trigger'   => [ 'avadaLightBoxInitializeLightbox' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'lightbox_path'             => [
				'label'       => esc_html__( 'Thumbnails Position', 'Avada' ),
				'description' => esc_html__( 'Controls the position of the lightbox thumbnails.', 'Avada' ),
				'id'          => 'lightbox_path',
				'default'     => 'vertical',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'vertical'   => esc_html__( 'Right', 'Avada' ),
					'horizontal' => esc_html__( 'Bottom', 'Avada' ),
				],
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the fusionLightboxVars.lightbox_path var.
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionLightboxVars',
								'id'        => 'lightbox_path',
								'trigger'   => [ 'avadaLightBoxInitializeLightbox' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'lightbox_animation_speed'  => [
				'label'       => esc_html__( 'Animation Speed', 'Avada' ),
				'description' => esc_html__( 'Controls the animation speed of the lightbox.', 'Avada' ),
				'id'          => 'lightbox_animation_speed',
				'default'     => 'normal',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'fast'   => esc_html__( 'Fast', 'Avada' ),
					'normal' => esc_html__( 'Normal', 'Avada' ),
					'slow'   => esc_html__( 'Slow', 'Avada' ),
				],
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the fusionLightboxVars.lightbox_animation_speed var.
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionLightboxVars',
								'id'        => 'lightbox_animation_speed',
								'trigger'   => [ 'avadaLightBoxInitializeLightbox' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'lightbox_arrows'           => [
				'label'       => esc_html__( 'Arrows', 'Avada' ),
				'description' => esc_html__( 'Turn on to display arrows in the lightbox', 'Avada' ),
				'id'          => 'lightbox_arrows',
				'default'     => '1',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
					[
						'setting'  => 'lightbox_skin',
						'operator' => '!=',
						'value'    => 'parade',
					],
				],
				'output'      => [
					// This is for the fusionLightboxVars.lightbox_arrows var.
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionLightboxVars',
								'id'        => 'lightbox_arrows',
								'trigger'   => [ 'avadaLightBoxInitializeLightbox' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'lightbox_gallery'          => [
				'label'       => esc_html__( 'Gallery Start/Stop Button', 'Avada' ),
				'description' => esc_html__( 'Turn on to display the gallery start and stop button.', 'Avada' ),
				'id'          => 'lightbox_gallery',
				'default'     => '1',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the fusionLightboxVars.lightbox_gallery var.
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionLightboxVars',
								'id'        => 'lightbox_gallery',
								'trigger'   => [ 'avadaLightBoxInitializeLightbox' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'lightbox_autoplay'         => [
				'label'       => esc_html__( 'Autoplay the Lightbox Gallery', 'Avada' ),
				'description' => esc_html__( 'Turn on to autoplay the lightbox gallery.', 'Avada' ),
				'id'          => 'lightbox_autoplay',
				'default'     => '0',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the fusionLightboxVars.lightbox_autoplay var.
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionLightboxVars',
								'id'        => 'lightbox_autoplay',
								'trigger'   => [ 'avadaLightBoxInitializeLightbox' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'lightbox_slideshow_speed'  => [
				'label'       => esc_html__( 'Slideshow Speed', 'Avada' ),
				'description' => esc_html__( 'Controls the slideshow speed if autoplay is turned on. ex: 1000 = 1 second.', 'Avada' ),
				'id'          => 'lightbox_slideshow_speed',
				'default'     => '5000',
				'type'        => 'slider',
				'choices'     => [
					'min'  => '1000',
					'max'  => '20000',
					'step' => '50',
				],
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the fusionLightboxVars.lightbox_slideshow_speed var.
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionLightboxVars',
								'id'        => 'lightbox_slideshow_speed',
								'trigger'   => [ 'avadaLightBoxInitializeLightbox' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'lightbox_opacity'          => [
				'label'       => esc_html__( 'Background Opacity', 'Avada' ),
				'description' => esc_html__( 'Controls the opacity level for the background behind the lightbox.', 'Avada' ),
				'id'          => 'lightbox_opacity',
				'default'     => '0.9',
				'type'        => 'slider',
				'choices'     => [
					'min'  => '0.1',
					'max'  => '1',
					'step' => '0.01',
				],
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the fusionLightboxVars.lightbox_opacity var.
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionLightboxVars',
								'id'        => 'lightbox_opacity',
								'trigger'   => [ 'avadaLightBoxInitializeLightbox' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'lightbox_title'            => [
				'label'       => esc_html__( 'Title', 'Avada' ),
				'description' => esc_html__( 'Turn on to display the image title in the lightbox.', 'Avada' ),
				'id'          => 'lightbox_title',
				'default'     => '1',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the fusionLightboxVars.lightbox_title var.
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionLightboxVars',
								'id'        => 'lightbox_title',
								'trigger'   => [ 'avadaLightBoxInitializeLightbox' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'lightbox_desc'             => [
				'label'       => esc_html__( 'Caption', 'Avada' ),
				'description' => esc_html__( 'Turn on to display the image caption in the lightbox.', 'Avada' ),
				'id'          => 'lightbox_desc',
				'default'     => '1',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the fusionLightboxVars.lightbox_desc var.
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionLightboxVars',
								'id'        => 'lightbox_desc',
								'trigger'   => [ 'avadaLightBoxInitializeLightbox' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'lightbox_social'           => [
				'label'       => esc_html__( 'Social Sharing', 'Avada' ),
				'description' => esc_html__( 'Turn on to display social sharing buttons on lightbox.', 'Avada' ),
				'id'          => 'lightbox_social',
				'default'     => '1',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the fusionLightboxVars.lightbox_social var.
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionLightboxVars',
								'id'        => 'lightbox_social',
								'trigger'   => [ 'avadaLightBoxInitializeLightbox' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'lightbox_deeplinking'      => [
				'label'       => esc_html__( 'Deeplinking', 'Avada' ),
				'description' => esc_html__( 'Turn on to deeplink images in the lightbox.', 'Avada' ),
				'id'          => 'lightbox_deeplinking',
				'default'     => '1',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
				// No need to refresh the page.
				'transport'   => 'postMessage',
			],
			'lightbox_post_images'      => [
				'label'       => esc_html__( 'Show Post Images in Lightbox', 'Avada' ),
				'description' => esc_html__( 'Turn on to display post images in the lightbox that are inside the post content area.', 'Avada' ),
				'id'          => 'lightbox_post_images',
				'default'     => '1',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the fusionLightboxVideoVars.lightbox_post_images var.
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionLightboxVideoVars',
								'id'        => 'lightbox_post_images',
								'trigger'   => [ 'avadaLightBoxInitializeLightbox' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
				],
			],
			'lightbox_video_dimensions' => [
				'label'       => esc_html__( 'Slideshow Video Dimensions', 'Avada' ),
				'description' => esc_html__( 'Controls the width and height for videos inside the lightbox.', 'Avada' ),
				'id'          => 'lightbox_video_dimensions',
				'units'       => false,
				'default'     => [
					'width'  => '1280px',
					'height' => '720px',
				],
				'type'        => 'dimensions',
				'required'    => [
					[
						'setting'  => 'status_lightbox',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'output'      => [
					// This is for the fusionLightboxVideoVars.lightbox_video_width var.
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionLightboxVideoVars',
								'id'        => 'lightbox_video_width',
								'choice'    => 'width',
								'trigger'   => [ 'load', 'ready', 'resize', 'avadaLightBoxInitializeLightbox' ],
							],
						],
						'sanitize_callback' => '__return_empty_string',
					],
					// This is for the fusionLightboxVideoVars.lightbox_video_height var.
					[
						'element'           => 'helperElement',
						'property'          => 'dummy',
						'js_callback'       => [
							'fusionGlobalScriptSet',
							[
								'globalVar' => 'fusionLightboxVideoVars',
								'id'        => 'lightbox_video_height',
								'choice'    => 'height',
								'trigger'   => [ 'load', 'ready', 'resize', 'avadaLightBoxInitializeLightbox' ],
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

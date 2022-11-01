<?php
/**
 * Elements settings functions.
 *
 * @package fusion-builder
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Element settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function fusion_builder_options_section_elements( $sections ) {

	$option_name = Fusion_Settings::get_option_name();
	$settings    = get_option( $option_name, [] );

	$sections['shortcode_styling'] = [
		'label'    => esc_html__( 'Avada Builder Elements', 'fusion-builder' ),
		'id'       => 'fusion_builder_elements',
		'is_panel' => true,
		'priority' => 14,
		'icon'     => 'el-icon-check',
		'fields'   => [
			'animations_shortcode_section' => [
				'label'       => esc_html__( 'Animations', 'fusion-builder' ),
				'description' => '',
				'id'          => 'animations_shortcode_section',
				'default'     => '',
				'type'        => 'accordion',
				'icon'        => 'fusiona-play-circle',
				'fields'      => [
					'animation_offset'      => [
						'label'       => esc_html__( 'Animation Offset', 'fusion-builder' ),
						'description' => esc_html__( 'Controls when the animation should start.', 'fusion-builder' ),
						'id'          => 'animation_offset',
						'default'     => 'top-into-view',
						'type'        => 'select',
						'option_name' => $option_name,
						'choices'     => [
							'top-into-view'   => esc_html__( 'Top of element hits bottom of viewport', 'fusion-builder' ),
							'top-mid-of-view' => esc_html__( 'Top of element hits middle of viewport', 'fusion-builder' ),
							'bottom-in-view'  => esc_html__( 'Bottom of element enters viewport', 'fusion-builder' ),
						],
						'transport'   => 'postMessage',
					],
					'status_css_animations' => [
						'label'       => esc_html__( 'Element Appearance Animations', 'fusion-builder' ),
						'description' => esc_html__( 'Select to enable animations for elements appearance.', 'fusion-builder' ),
						'id'          => 'status_css_animations',
						'default'     => 'desktop',
						'type'        => 'radio-buttonset',
						'choices'     => [
							'desktop_and_mobile' => esc_html__( 'Desktop & Mobile', 'fusion-builder' ),
							'desktop'            => esc_html__( 'Desktop Only', 'fusion-builder' ),
							'off'                => esc_html__( 'Off', 'fusion-builder' ),
						],
						'output'      => [

							// Change the fusionAnimationsVars.status_css_animations var.
							[
								'element'           => 'helperElement',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'fusionAnimationsVars',
										'id'        => 'status_css_animations',
										'trigger'   => [ 'CSSAnimations' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
				],
			],
			'carousel_shortcode_section'   => [
				'label'       => esc_html__( 'Carousel', 'fusion-builder' ),
				'description' => '',
				'id'          => 'carousel_shortcode_section',
				'type'        => 'accordion',
				'icon'        => 'fusiona-images',
				'fields'      => [
					'carousel_nav_color'   => [
						'label'       => esc_html__( 'Carousel Navigation Box Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the navigation box for carousel sliders.', 'fusion-builder' ),
						'id'          => 'carousel_nav_color',
						'default'     => 'hsla(var(--awb-color8-h),var(--awb-color8-s),var(--awb-color8-l),calc(var(--awb-color8-a) - 30%))',
						'type'        => 'color-alpha',
						'option_name' => $option_name,
						'css_vars'    => [
							[
								'name'     => '--carousel_nav_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'carousel_hover_color' => [
						'label'       => esc_html__( 'Carousel Hover Navigation Box Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the hover navigation box for carousel sliders.', 'fusion-builder' ),
						'id'          => 'carousel_hover_color',
						'default'     => 'hsla(var(--awb-color5-h),var(--awb-color5-s),var(--awb-color5-l),calc(var(--awb-color5-a) - 20%))',
						'type'        => 'color-alpha',
						'option_name' => $option_name,
						'css_vars'    => [
							[
								'name'     => '--carousel_hover_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'carousel_speed'       => [
						'label'       => esc_html__( 'Carousel Speed', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the speed of all carousel elements. ex: 1000 = 1 second.', 'fusion-builder' ),
						'id'          => 'carousel_speed',
						'default'     => '2500',
						'type'        => 'slider',
						'option_name' => $option_name,
						'choices'     => [
							'min'  => '1000',
							'max'  => '20000',
							'step' => '250',
						],
						'output'      => [
							// This is for the fusionCarouselVars.carousel_speed var.
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'globalVar' => 'fusionCarouselVars',
										'id'        => 'carousel_speed',
										'trigger'   => [ 'fusion-reinit-carousels' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
				],
			],
			'visibility_shortcode_section' => [
				'label'       => esc_html__( 'Visibility Size Options', 'fusion-builder' ),
				'id'          => 'visibility_shortcode_section',
				'description' => '',
				'type'        => 'accordion',
				'icon'        => 'fusiona-mobile',
				'fields'      => [
					'visibility_moved' => [
						'id'          => 'visibility_moved',
						'label'       => '',
						'type'        => 'custom',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> You can now find the visibility breakpoints on the main <a href="#" class="fusion-quick-option" data-fusion-option="visibility_small">responsive tab</a>.', 'fusion-builder' ) . '</div>',
					],
				],
			],
		],
	];

	return $sections;

}

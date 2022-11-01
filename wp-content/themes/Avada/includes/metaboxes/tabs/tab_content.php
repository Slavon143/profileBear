<?php
/**
 * Content Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

/**
 * Background page settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_content( $sections ) {
	global $post;
	$override  = function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_override( 'content' ) : false;
	$post_type = get_post_type();

	$sections['content'] = [
		'label'    => esc_html__( 'Content', 'Avada' ),
		'id'       => 'content',
		'alt_icon' => 'fusiona-content',
		'fields'   => [],
	];

	$skip_fields = false;
	if ( $override ) {
		$is_template = 'fusion_tb_section' === $post_type;
		$layout_cats = is_object( $post ) ? get_the_terms( $post->ID, 'fusion_tb_category' ) : false;

		if ( ! $is_template ) {
			$skip_fields = true;
		}

		if ( $is_template && is_array( $layout_cats ) ) {
			foreach ( $layout_cats as $layout_cat ) {
				if ( isset( $layout_cat->slug ) && 'content' !== $layout_cat->slug ) {
					$skip_fields = true;
				}
			}
		}
	}

	// Template override, add notice and hide rest.
	if ( $skip_fields ) {
		$sections['content']['fields']['content_info'] = [
			'id'          => 'content_info',
			'label'       => '',
			/* translators: The edit link. Text of link is the title. */
			'description' => '<div class="fusion-redux-important-notice">' . Fusion_Template_Builder()->get_override_text( $override ) . '</div>',
			'dependency'  => [],
			'type'        => 'custom',
		];
	}

	// Page uses template, others use PO.
	if ( 'page' !== $post_type ) {
		$full_width_option = 'blog_width_100';
		if ( 'product' === $post_type ) {
			$full_width_option = 'product_width_100';
		} elseif ( 'avada_portfolio' === $post_type ) {
			$full_width_option = 'portfolio_width_100';
		} elseif ( 'fusion_tb_section' === $post_type ) {
			$full_width_option = 'fusion_tb_section_width_100';
		}

		$full_width_option_default = 'default';
		if ( 'tribe_events' === $post_type ) {
			$full_width_option_default = 'no';
		} elseif ( ! in_array( $post_type, [ 'post', 'product', 'avada_portfolio' ], true ) ) {
			$full_width_option_default = ( 1 === (int) Avada()->settings->get( $full_width_option ) ) ? 'yes' : 'no';
		}

		if ( ! $skip_fields ) {
			$sections['content']['fields'][ $full_width_option ] = [
				'id'          => $full_width_option,
				'type'        => 'radio-buttonset',
				'map'         => 'yesno',
				'label'       => esc_attr__( 'Use 100% Width Content', 'Avada' ),
				'description' => sprintf(
					/* translators: Additional description (defaults). */
					esc_html__( 'Choose to set this page content to 100&#37; browser width. %s', 'Avada' ),
					in_array( $post_type, [ 'post', 'product', 'avada_portfolio' ], true ) ? Avada()->settings->get_default_description( $full_width_option, '', 'yesno' ) : ''
				),
				'default'     => $full_width_option_default,
				'dependency'  => [],
				'choices'     => in_array( $post_type, [ 'post', 'product', 'avada_portfolio' ], true ) ? [
					'default' => esc_attr__( 'Default', 'Avada' ),
					'yes'     => esc_attr__( 'Yes', 'Avada' ),
					'no'      => esc_attr__( 'No', 'Avada' ),
				] : [
					'yes' => esc_attr__( 'Yes', 'Avada' ),
					'no'  => esc_attr__( 'No', 'Avada' ),
				],
				'map'         => 'yesno',
			];
		}

		if ( 'fusion_tb_section' === $post_type ) {
			$sections['content']['fields'][ $full_width_option ]['description'] = esc_html__( 'Choose to set this post to 100&#37; browser width.', 'Avada' );
			$sections['content']['fields'][ $full_width_option ]['default']     = 'yes';
			$sections['content']['fields'][ $full_width_option ]['choices']     = [
				'yes' => esc_attr__( 'Yes', 'Avada' ),
				'no'  => esc_attr__( 'No', 'Avada' ),
			];
		}
	} elseif ( ! $skip_fields ) {
		$sections['content']['fields']['show_first_featured_image'] = [
			'id'          => 'show_first_featured_image',
			'label'       => esc_attr__( 'Show First Featured Image', 'Avada' ),
			'description' => esc_html__( 'Show the 1st featured image on page.', 'Avada' ),
			'dependency'  => [],
			'type'        => 'radio-buttonset',
			'default'     => 'yes',
			'choices'     => [
				'yes' => esc_attr__( 'Yes', 'Avada' ),
				'no'  => esc_attr__( 'No', 'Avada' ),
			],
		];
	}

	// Check if we're on WooCommerce Shop page.
	if ( is_object( $post ) && fusion_is_shop( $post->ID ) ) {
		$sections['content']['fields']['show_wc_shop_loop'] = [
			'id'          => 'show_wc_shop_loop',
			'label'       => esc_attr__( 'Show WooCommerce Shop Loop', 'Avada' ),
			'description' => esc_html__( 'Show WooCommerce Shop Loop on page.', 'Avada' ),
			'dependency'  => [],
			'type'        => 'radio-buttonset',
			'default'     => 'yes',
			'choices'     => [
				'yes' => esc_attr__( 'Yes', 'Avada' ),
				'no'  => esc_attr__( 'No', 'Avada' ),
			],
		];
	}

	$sections['content']['fields']['hundredp_padding'] = [
		'id'          => 'hundredp_padding',
		'label'       => esc_html__( '100% Width Padding', 'Avada' ),
		/* translators: Additional description (defaults). */
		'description' => sprintf( esc_html__( 'Controls the left and right padding for page content when using 100&#37; site width, 100&#37; width page template or 100&#37; width post option. This does not affect Avada Builder containers.  Enter value including any valid CSS unit, ex: 30px. %s', 'Avada' ), Avada()->settings->get_default_description( 'hundredp_padding' ) ),
		'dependency'  => [],
		'type'        => 'text',
	];

	$sections['content']['fields']['main_padding'] = [
		'id'          => 'main_padding',
		'value'       => [
			'top'    => '',
			'bottom' => '',
		],
		'label'       => esc_attr__( 'Content Padding', 'Avada' ),
		/* translators: Additional description (defaults). */
		'description' => sprintf( esc_html__( 'In pixels ex: 20px. %s', 'Avada' ), Avada()->settings->get_default_description( 'main_padding', [ 'top', 'bottom' ] ) ),
		'dependency'  => [],
		'type'        => 'dimensions',
	];

	$content_bg_color                                   = Fusion_Color::new_color(
		[
			'color'    => Avada()->settings->get( 'content_bg_color' ),
			'fallback' => '#ffffff',
		]
	);
	$sections['content']['fields']['content_bg_color']  = [
		'id'          => 'content_bg_color',
		'label'       => esc_attr__( 'Background Color for Main Content Area', 'Avada' ),
		/* translators: Additional description (defaults). */
		'description' => sprintf( esc_html__( 'Controls the background color for the main content area. Hex code, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'content_bg_color' ) ),
		'dependency'  => [],
		'default'     => $content_bg_color->color,
		'type'        => 'color-alpha',
	];
	$sections['content']['fields']['content_bg_image']  = [
		'id'          => 'content_bg_image',
		'label'       => esc_attr__( 'Background Image for Main Content Area', 'Avada' ),
		'alpha'       => true,
		/* translators: Additional description (defaults). */
		'description' => sprintf( esc_html__( 'Select an image to use for the main content area. %s', 'Avada' ), Avada()->settings->get_default_description( 'content_bg_image', 'url' ) ),
		'dependency'  => [],
		'type'        => 'media',
	];
	$sections['content']['fields']['content_bg_full']   = [
		'id'          => 'content_bg_full',
		'label'       => esc_html__( '100% Background Image', 'Avada' ),
		/* translators: Additional description (defaults). */
		'description' => sprintf( esc_html__( 'Choose to have the background image display at 100&#37;. %s', 'Avada' ), Avada()->settings->get_default_description( 'content_bg_full', '', 'yesno' ) ),
		'choices'     => [
			'default' => esc_attr__( 'Default', 'Avada' ),
			'no'      => esc_attr__( 'No', 'Avada' ),
			'yes'     => esc_attr__( 'Yes', 'Avada' ),
		],
		'dependency'  => [
			[
				'field'      => 'content_bg_repeat',
				'value'      => '',
				'comparison' => '!=',
			],
		],
		'type'        => 'radio-buttonset',
		'map'         => 'yesno',
		'default'     => 'no',
	];
	$sections['content']['fields']['content_bg_repeat'] = [
		'id'          => 'content_bg_repeat',
		'label'       => esc_attr__( 'Background Repeat', 'Avada' ),
		/* translators: Additional description (defaults). */
		'description' => sprintf( esc_html__( 'Select how the background image repeats. %s', 'Avada' ), Avada()->settings->get_default_description( 'content_bg_repeat', '', 'select' ) ),
		'choices'     => [
			'default'   => esc_attr__( 'Default', 'Avada' ),
			'repeat'    => esc_attr__( 'Tile', 'Avada' ),
			'repeat-x'  => esc_attr__( 'Tile Horizontally', 'Avada' ),
			'repeat-y'  => esc_attr__( 'Tile Vertically', 'Avada' ),
			'no-repeat' => esc_attr__( 'No Repeat', 'Avada' ),
		],
		'dependency'  => [
			[
				'field'      => 'content_bg_repeat',
				'value'      => '',
				'comparison' => '!=',
			],
		],
		'type'        => 'select',
	];

	if ( 'tribe_events' === $post_type ) {
		$sections['content']['fields']['events_social_sharing_box'] = [
			'id'          => 'events_social_sharing_box',
			'label'       => esc_attr__( 'Show Social Share Box', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose to show or hide the social share box. %s', 'Avada' ), Avada()->settings->get_default_description( 'events_social_sharing_box', '', 'showhide' ) ),
			'dependency'  => [],
			'type'        => 'radio-buttonset',
			'map'         => 'showhide',
			'default'     => 'default',
			'choices'     => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Show', 'Avada' ),
				'no'      => esc_attr__( 'Hide', 'Avada' ),
			],
		];
	}

	if ( $skip_fields ) {
		$template_override_options = Fusion_Data_PostMeta::get_template_options();
		foreach ( $template_override_options as $template_override_option ) {
			if ( isset( $sections['content']['fields'][ $template_override_option ] ) ) {
				unset( $sections['content']['fields'][ $template_override_option ] );
			}
		}
	}
	return $sections;
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

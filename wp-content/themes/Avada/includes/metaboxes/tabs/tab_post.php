<?php
/**
 * Post Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

/**
 * Post page settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_post( $sections ) {
	$override = function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_override() : false;

	$sections['post'] = [
		'label'    => esc_html__( 'Post', 'Avada' ),
		'id'       => 'post',
		'alt_icon' => 'fusiona-feather',
		'fields'   => [],
	];

	// Template override, add notice.
	if ( $override ) {
		$sections['post']['fields']['post_info'] = [
			'id'          => 'post_info',
			'label'       => '',
			/* translators: The edit link. Text of link is the title. */
			'description' => '<div class="fusion-redux-important-notice">' . Fusion_Template_Builder()->get_override_text( $override ) . '</div>',
			'dependency'  => [],
			'type'        => 'custom',
		];
	}

	// Only use without content template override.
	if ( ! $override ) {
		$sections['post']['fields']['show_first_featured_image'] = [
			'id'          => 'show_first_featured_image',
			'label'       => esc_attr__( 'Show First Featured Image', 'Avada' ),
			'description' => esc_html__( 'Show the 1st featured image on single post pages.', 'Avada' ),
			'choices'     => [
				'yes' => esc_attr__( 'Yes', 'Avada' ),
				'no'  => esc_attr__( 'No', 'Avada' ),
			],
			'default'     => 'yes',
			'dependency'  => [],
			'type'        => 'radio-buttonset',
		];
		$sections['post']['fields']['fimg']                      = [
			'id'          => 'fimg',
			'label'       => esc_attr__( 'Featured Image Dimensions', 'Avada' ),
			'description' => esc_html__( 'In pixels or percentage, ex: 100% or 100px. Or Use "auto" for automatic resizing if you added either width or height.', 'Avada' ),
			'dependency'  => [],
			'value'       => [
				'width'  => '',
				'height' => '',
			],
			'type'        => 'dimensions',
		];
	}

	$sections['post']['fields']['video'] = [
		'id'          => 'video',
		'label'       => esc_attr__( 'Video Embed Code', 'Avada' ),
		'description' => esc_attr__( 'Insert Youtube or Vimeo embed code.', 'Avada' ),
		'dependency'  => [],
		'type'        => 'textarea',
	];

	// Pagination only use without content template override.
	if ( ! $override ) {
		$sections['post']['fields']['blog_pn_nav'] = [
			'id'          => 'blog_pn_nav',
			'label'       => esc_html__( 'Show Previous/Next Pagination', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose to show or hide the post navigation. %s', 'Avada' ), Avada()->settings->get_default_description( 'blog_pn_nav', '', 'showhide' ) ),
			'dependency'  => [],
			'type'        => 'radio-buttonset',
			'map'         => 'showhide',
			'choices'     => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Show', 'Avada' ),
				'no'      => esc_attr__( 'Hide', 'Avada' ),
			],
			'default'     => 'default',
		];
	}
	$post_type = get_post_type();

	// Rollover options but not for FAQs.
	if ( 'avada_faq' !== $post_type ) {
		$sections['post']['fields']['image_rollover_icons'] = [
			'id'          => 'image_rollover_icons',
			'label'       => esc_attr__( 'Image Rollover Icons', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose which icons display on this post. %s', 'Avada' ), Avada()->settings->get_default_description( 'image_rollover', '', 'rollover' ) ),
			'to_default'  => [
				'id' => 'image_rollover',
			],
			'dependency'  => [],
			'default'     => 'default',
			'choices'     => [
				'default'  => esc_attr__( 'Default', 'Avada' ),
				'linkzoom' => esc_html__( 'Link + Zoom', 'Avada' ),
				'link'     => esc_attr__( 'Link', 'Avada' ),
				'zoom'     => esc_attr__( 'Zoom', 'Avada' ),
				'no'       => esc_attr__( 'No Icons', 'Avada' ),
			],
			'type'        => 'select',
		];
		$sections['post']['fields']['link_icon_url']        = [
			'id'          => 'link_icon_url',
			'label'       => esc_attr__( 'Custom Link URL On Archives', 'Avada' ),
			'description' => esc_attr__( 'Link URL that will be used on archives either for the rollover link icon or on the image if rollover icons are disabled. Leave blank for post URL.', 'Avada' ),
			'type'        => 'text',
		];
		$sections['post']['fields']['post_links_target']    = [
			'id'          => 'post_links_target',
			'label'       => esc_html__( 'Open Blog Links In New Window', 'Avada' ),
			'description' => esc_html__( 'Choose to open the single post page link in a new window.', 'Avada' ),
			'dependency'  => [],
			'type'        => 'radio-buttonset',
			'choices'     => [
				'yes' => esc_attr__( 'Yes', 'Avada' ),
				'no'  => esc_attr__( 'No', 'Avada' ),
			],
			'default'     => 'no',
		];
	}

	// Only use without content template override.
	if ( ! $override ) {
		$sections['post']['fields']['post_meta']          = [
			'id'          => 'post_meta',
			'label'       => esc_html__( 'Show Post Meta', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose to show or hide the post meta. %s', 'Avada' ), Avada()->settings->get_default_description( 'post_meta', '', 'showhide' ) ),
			'dependency'  => [],
			'type'        => 'radio-buttonset',
			'map'         => 'showhide',
			'choices'     => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Show', 'Avada' ),
				'no'      => esc_attr__( 'Hide', 'Avada' ),
			],
			'default'     => 'default',
		];
		$sections['post']['fields']['social_sharing_box'] = [
			'id'            => 'social_sharing_box',
			'label'         => esc_attr__( 'Show Social Share Box', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description'   => sprintf( esc_html__( 'Choose to show or hide the social share box. %s', 'Avada' ), Avada()->settings->get_default_description( 'social_sharing_box', '', 'showhide' ) ),
			'dependency'    => [],
			'type'          => 'radio-buttonset',
			'map'           => 'showhide',
			'choices'       => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Show', 'Avada' ),
				'no'      => esc_attr__( 'Hide', 'Avada' ),
			],
			'default'       => 'default',
			'edit_shortcut' => [
				'selector'  => [ '.single-post .fusion-single-sharing-box' ],
				'shortcuts' => [
					[
						'aria_label' => esc_html__( 'Toggle Social Sharing Box', 'Avada' ),
					],
				],
			],
		];
		$sections['post']['fields']['author_info']        = [
			'id'            => 'author_info',
			'label'         => esc_attr__( 'Show Author Info Box', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description'   => sprintf( esc_html__( 'Choose to show or hide the author info box. %s', 'Avada' ), Avada()->settings->get_default_description( 'author_info', '', 'showhide' ) ),
			'dependency'    => [],
			'type'          => 'radio-buttonset',
			'map'           => 'showhide',
			'choices'       => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Show', 'Avada' ),
				'no'      => esc_attr__( 'Hide', 'Avada' ),
			],
			'default'       => 'default',
			'edit_shortcut' => [
				'selector'  => [ '.single-post .about-author' ],
				'shortcuts' => [
					[
						'aria_label' => esc_html__( 'Toggle Author Info Box', 'Avada' ),
					],
				],
			],
		];
		$sections['post']['fields']['related_posts']      = [
			'id'            => 'related_posts',
			'label'         => esc_attr__( 'Show Related Posts', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description'   => sprintf( esc_html__( 'Choose to show or hide related posts on this post. %s', 'Avada' ), Avada()->settings->get_default_description( 'related_posts', '', 'showhide' ) ),
			'dependency'    => [],
			'type'          => 'radio-buttonset',
			'map'           => 'showhide',
			'choices'       => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Show', 'Avada' ),
				'no'      => esc_attr__( 'Hide', 'Avada' ),
			],
			'default'       => 'default',
			'edit_shortcut' => [
				'selector'  => [ '.single-post .single-related-posts' ],
				'shortcuts' => [
					[
						'aria_label' => esc_html__( 'Toggle Related Posts', 'Avada' ),
					],
				],
			],
		];
		$sections['post']['fields']['blog_comments']      = [
			'id'            => 'blog_comments',
			'label'         => esc_attr__( 'Show Comments', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description'   => sprintf( esc_attr__( 'Choose to show or hide comments area. %s', 'Avada' ), Avada()->settings->get_default_description( 'blog_comments', '', 'showhide' ) ),
			'dependency'    => [],
			'type'          => 'radio-buttonset',
			'map'           => 'showhide',
			'choices'       => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Show', 'Avada' ),
				'no'      => esc_attr__( 'Hide', 'Avada' ),
			],
			'default'       => 'default',
			'edit_shortcut' => [
				'selector'  => [ '.single-post #respond' ],
				'shortcuts' => [
					[
						'aria_label' => esc_html__( 'Toggle Comments', 'Avada' ),
					],
				],
			],
		];
	}
	return $sections;
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

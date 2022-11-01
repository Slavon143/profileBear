<?php
/**
 * Portfolio Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

/**
 * Page portfolio post settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_portfolio_post( $sections ) {

	$sections['portfolio_post'] = [
		'label'    => esc_html__( 'Portfolio', 'Avada' ),
		'id'       => 'portfolio_post',
		'alt_icon' => 'fusiona-insertpicture',
		'fields'   => [],
	];

	// Template override, add notice.
	$override = function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_override() : false;
	if ( $override ) {
		$sections['portfolio_post']['fields']['portfolio_info'] = [
			'id'          => 'portfolio_info',
			'label'       => '',
			/* translators: The edit link. Text of link is the title. */
			'description' => '<div class="fusion-redux-important-notice">' . Fusion_Template_Builder()->get_override_text( $override ) . '</div>',
			'dependency'  => [],
			'type'        => 'custom',
		];
	}

	// Component related options, hide because we don't use them.
	if ( ! $override ) {
		$sections['portfolio_post']['fields']['portfolio_pn_nav'] = [
			'id'          => 'portfolio_pn_nav',
			'label'       => esc_attr__( 'Show Previous/Next Pagination', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose to show or hide the post navigation. %s', 'Avada' ), Avada()->settings->get_default_description( 'portfolio_pn_nav', '', 'showhide' ) ),
			'dependency'  => [],
			'default'     => 'default',
			'type'        => 'radio-buttonset',
			'map'         => 'showhide',
			'choices'     => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Show', 'Avada' ),
				'no'      => esc_attr__( 'Hide', 'Avada' ),
			],
		];

		$sections['portfolio_post']['fields']['portfolio_featured_image_width'] = [
			'id'          => 'portfolio_featured_image_width',
			'label'       => esc_html__( 'Width (Content Columns for Featured Image)', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose if the featured image is full or half width. %s', 'Avada' ), Avada()->settings->get_default_description( 'portfolio_featured_image_width', '', 'select' ) ),
			'dependency'  => [],
			'default'     => 'default',
			'choices'     => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'full'    => esc_attr__( 'Full Width', 'Avada' ),
				'half'    => esc_attr__( 'Half Width', 'Avada' ),
			],
			'type'        => 'radio-buttonset',
		];
		$sections['portfolio_post']['fields']['show_first_featured_image']      = [
			'id'          => 'show_first_featured_image',
			'label'       => esc_html__( 'Show First Featured Image', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Show the 1st featured image on single post pages. %s', 'Avada' ), Avada()->settings->get_default_description( 'show_first_featured_image', '', 'yesno' ) ),
			'dependency'  => [],
			'default'     => 'default',
			'choices'     => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Yes', 'Avada' ),
				'no'      => esc_attr__( 'No', 'Avada' ),
			],
			'type'        => 'radio-buttonset',
		];
		$sections['portfolio_post']['fields']['fimg']                           = [
			'id'          => 'fimg',
			'value'       => [
				'width'  => '',
				'height' => '',
			],
			'label'       => esc_attr__( 'Featured Image Dimensions', 'Avada' ),
			'description' => esc_html__( 'In pixels or percentage, ex: 100% or 100px. Or Use "auto" for automatic resizing if you added either width or height.', 'Avada' ),
			'dependency'  => [],
			'type'        => 'dimensions',
		];
	}

	$sections['portfolio_post']['fields']['video']     = [
		'id'          => 'video',
		'label'       => esc_attr__( 'Video Embed Code', 'Avada' ),
		'description' => esc_attr__( 'Insert Youtube or Vimeo embed code.', 'Avada' ),
		'dependency'  => [],
		'type'        => 'textarea',
	];
	$sections['portfolio_post']['fields']['video_url'] = [
		'id'          => 'video_url',
		'label'       => esc_attr__( 'Video URL for Lightbox', 'Avada' ),
		'description' => esc_attr__( 'Insert the video URL that will show in the lightbox. This can be a YouTube, Vimeo or a self-hosted video URL.', 'Avada' ),
		'dependency'  => [],
		'type'        => 'text',
	];

	// Display options, so if template we don't use them.
	if ( ! $override ) {
		$sections['portfolio_post']['fields']['portfolio_project_desc_title'] = [
			'id'          => 'portfolio_project_desc_title',
			'label'       => esc_html__( 'Show Project Description Title', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose to show or hide the project description title. %s', 'Avada' ), Avada()->settings->get_default_description( 'portfolio_project_desc_title', '', 'yesno' ) ),
			'dependency'  => [],
			'default'     => 'default',
			'choices'     => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Yes', 'Avada' ),
				'no'      => esc_attr__( 'No', 'Avada' ),
			],
			'type'        => 'radio-buttonset',
			'map'         => 'yesno',
		];
		$sections['portfolio_post']['fields']['portfolio_project_details']    = [
			'id'          => 'portfolio_project_details',
			'label'       => esc_html__( 'Show Project Details', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose to show or hide the project details text. %s', 'Avada' ), Avada()->settings->get_default_description( 'portfolio_project_details', '', 'yesno' ) ),
			'dependency'  => [],
			'default'     => 'default',
			'choices'     => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Yes', 'Avada' ),
				'no'      => esc_attr__( 'No', 'Avada' ),
			],
			'type'        => 'radio-buttonset',
			'map'         => 'yesno',
		];
	}
	$sections['portfolio_post']['fields']['project_url']                = [
		'id'          => 'project_url',
		'label'       => esc_attr__( 'Project URL', 'Avada' ),
		'description' => esc_attr__( 'The URL the project text links to.', 'Avada' ),
		'dependency'  => [],
		'type'        => 'text',
	];
	$sections['portfolio_post']['fields']['project_url_text']           = [
		'id'          => 'project_url_text',
		'label'       => esc_attr__( 'Project URL Text', 'Avada' ),
		'description' => esc_html__( 'The custom project text that will link.', 'Avada' ),
		'dependency'  => [],
		'type'        => 'text',
	];
	$sections['portfolio_post']['fields']['copy_url']                   = [
		'id'          => 'copy_url',
		'label'       => esc_attr__( 'Copyright URL', 'Avada' ),
		'description' => esc_html__( 'The URL the copyright text links to.', 'Avada' ),
		'dependency'  => [],
		'type'        => 'text',
	];
	$sections['portfolio_post']['fields']['copy_url_text']              = [
		'id'          => 'copy_url_text',
		'label'       => esc_attr__( 'Copyright URL Text', 'Avada' ),
		'description' => esc_html__( 'The custom copyright text that will link.', 'Avada' ),
		'dependency'  => [],
		'type'        => 'text',
	];
	$sections['portfolio_post']['fields']['image_rollover_icons']       = [
		'id'          => 'image_rollover_icons',
		'label'       => esc_attr__( 'Image Rollover Icons', 'Avada' ),
		/* translators: Additional description (defaults). */
		'description' => sprintf( esc_html__( 'Choose which icons display on this post. %s', 'Avada' ), Avada()->settings->get_default_description( 'image_rollover_icons', '', 'rollover' ) ),
		'dependency'  => [],
		'type'        => 'select',
		'choices'     => [
			'default'  => esc_attr__( 'Default', 'Avada' ),
			'linkzoom' => esc_html__( 'Link + Zoom', 'Avada' ),
			'link'     => esc_attr__( 'Link', 'Avada' ),
			'zoom'     => esc_attr__( 'Zoom', 'Avada' ),
			'no'       => esc_attr__( 'No Icons', 'Avada' ),
		],
	];
	$sections['portfolio_post']['fields']['link_icon_url']              = [
		'id'          => 'link_icon_url',
		'label'       => esc_attr__( 'Custom Link URL On Archives', 'Avada' ),
		'description' => esc_attr__( 'Link URL that will be used on archives either for the rollover link icon or on the image if rollover icons are disabled. Leave blank for post URL.', 'Avada' ),
		'type'        => 'text',
	];
	$sections['portfolio_post']['fields']['portfolio_link_icon_target'] = [
		'id'          => 'portfolio_link_icon_target',
		'label'       => esc_attr__( 'Open Portfolio Links In New Window', 'Avada' ),
		/* translators: Additional description (defaults). */
		'description' => sprintf( esc_html__( 'Choose to open the single post page, project url and copyright url links in a new window. %s', 'Avada' ), Avada()->settings->get_default_description( 'portfolio_link_icon_target', '', 'yesno' ) ),
		'dependency'  => [],
		'default'     => 'default',
		'type'        => 'radio-buttonset',
		'map'         => 'yesno',
		'choices'     => [
			'default' => esc_attr__( 'Default', 'Avada' ),
			'yes'     => esc_attr__( 'Yes', 'Avada' ),
			'no'      => esc_attr__( 'No', 'Avada' ),
		],
		// Don't change anything since it's not relavant in builder mode.
		'transport'   => 'postMessage',
	];

	// If template, we hide component related options.
	if ( ! $override ) {
		$sections['portfolio_post']['fields']['portfolio_author'] = [
			'id'            => 'portfolio_author',
			'label'         => esc_attr__( 'Show Author', 'Avada' ),
			'type'          => 'radio-buttonset',
			'map'           => 'showhide',
			'choices'       => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Show', 'Avada' ),
				'no'      => esc_attr__( 'Hide', 'Avada' ),
			],
			/* translators: Additional description (defaults). */
			'description'   => sprintf( esc_html__( 'Choose to show or hide the author in the Project Details. %s', 'Avada' ), Avada()->settings->get_default_description( 'portfolio_author', '', 'showhide' ) ),
			'dependency'    => [],
			'default'       => 'default',
			'edit_shortcut' => [
				'selector'  => [ '.single-avada_portfolio .project-info .project-info-box.vcard' ],
				'shortcuts' => [
					[
						'aria_label' => esc_html__( 'Toggle Author', 'Avada' ),
					],
				],
			],
		];
		$sections['portfolio_post']['fields']['portfolio_social_sharing_box'] = [
			'id'            => 'portfolio_social_sharing_box',
			'label'         => esc_attr__( 'Show Social Share Box', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description'   => sprintf( esc_html__( 'Choose to show or hide the social share box. %s', 'Avada' ), Avada()->settings->get_default_description( 'portfolio_social_sharing_box', '', 'showhide' ) ),
			'dependency'    => [],
			'default'       => 'default',
			'type'          => 'radio-buttonset',
			'map'           => 'showhide',
			'choices'       => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Show', 'Avada' ),
				'no'      => esc_attr__( 'Hide', 'Avada' ),
			],
			'edit_shortcut' => [
				'selector'  => [ '.single-avada_portfolio .fusion-single-sharing-box' ],
				'shortcuts' => [
					[
						'aria_label' => esc_html__( 'Toggle Social Sharing Box', 'Avada' ),
					],
				],
			],
		];
		$sections['portfolio_post']['fields']['portfolio_related_posts']      = [
			'id'            => 'portfolio_related_posts',
			'label'         => esc_attr__( 'Show Related Projects', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description'   => sprintf( esc_html__( 'Choose to show or hide related projects on this post. %s', 'Avada' ), Avada()->settings->get_default_description( 'portfolio_related_posts', '', 'showhide' ) ),
			'dependency'    => [],
			'default'       => 'default',
			'type'          => 'radio-buttonset',
			'map'           => 'showhide',
			'choices'       => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Show', 'Avada' ),
				'no'      => esc_attr__( 'Hide', 'Avada' ),
			],
			'edit_shortcut' => [
				'selector'  => [ '.single-avada_portfolio .single-related-posts' ],
				'shortcuts' => [
					[
						'aria_label' => esc_html__( 'Toggle Related Projects', 'Avada' ),
					],
				],
			],
		];
		$sections['portfolio_post']['fields']['portfolio_comments']           = [
			'id'            => 'portfolio_comments',
			'label'         => esc_attr__( 'Show Comments', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description'   => sprintf( esc_attr__( 'Choose to show or hide comments area. %s', 'Avada' ), Avada()->settings->get_default_description( 'portfolio_comments', '', 'showhide' ) ),
			'dependency'    => [],
			'default'       => 'default',
			'type'          => 'radio-buttonset',
			'map'           => 'showhide',
			'choices'       => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Show', 'Avada' ),
				'no'      => esc_attr__( 'Hide', 'Avada' ),
			],
			'edit_shortcut' => [
				'selector'  => [ '.single-avada_portfolio #respond' ],
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

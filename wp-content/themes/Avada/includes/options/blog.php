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
 * Blog settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_blog( $sections ) {

	// Check if we have a global content override.
	$has_global_content        = false;
	$template_terms['content'] = [];
	if ( class_exists( 'Fusion_Template_Builder' ) ) {
		$default_layout     = Fusion_Template_Builder::get_default_layout();
		$has_global_content = isset( $default_layout['data']['template_terms'] ) && isset( $default_layout['data']['template_terms']['content'] ) && $default_layout['data']['template_terms']['content'];

		// PHP 5.6 compat.
		$template_terms = Fusion_Template_Builder::get_instance()->get_template_terms();
	}

	$blog_general_options = [
		'label'       => esc_html__( 'General Blog', 'Avada' ),
		'description' => '',
		'id'          => 'blog_general_options',
		'icon'        => true,
		'type'        => 'sub-section',
		'fields'      => [
			'general_blog_important_note_info'             => [
				'label'       => '',
				'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The options on this tab only control the assigned blog page in "Settings > Reading", blog archives or the blog single post page, not the blog element. The only options on this tab that work with the blog element are the Date Format options and Load More Post Button Color.', 'Avada' ) . '</div>',
				'id'          => 'general_blog_important_note_info',
				'type'        => 'custom',
			],
			'blog_page_title_bar'                          => [
				'label'           => esc_html__( 'Blog Page Title Bar', 'Avada' ),
				'description'     => esc_html__( 'Controls how the page title bar displays on single blog posts and blog archive pages.', 'Avada' ),
				'id'              => 'blog_page_title_bar',
				'default'         => 'hide',
				'choices'         => [
					'bar_and_content' => esc_html__( 'Show Bar and Content', 'Avada' ),
					'content_only'    => esc_html__( 'Show Content Only', 'Avada' ),
					'hide'            => esc_html__( 'Hide', 'Avada' ),
				],
				'type'            => 'select',
				'partial_refresh' => [
					'page_title_bar_contents_blog_page_title_bar' => [
						'selector'            => '.avada-page-titlebar-wrapper',
						'container_inclusive' => false,
						'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'page_titlebar_wrapper' ],
					],
				],
			],
			'blog_show_page_title_bar'                     => [
				'label'           => esc_html__( 'Blog Assigned Page Title Bar', 'Avada' ),
				'description'     => esc_html__( 'Controls how the page title bar displays on the assigned blog page in "Settings > Reading".', 'Avada' ),
				'id'              => 'blog_show_page_title_bar',
				'default'         => 'hide',
				'choices'         => [
					'bar_and_content' => esc_html__( 'Show Bar and Content', 'Avada' ),
					'content_only'    => esc_html__( 'Show Content Only', 'Avada' ),
					'hide'            => esc_html__( 'Hide', 'Avada' ),
				],
				'type'            => 'select',
				'partial_refresh' => [
					'page_title_bar_contents_blog_show_page_title_bar' => [
						'selector'            => '.avada-page-titlebar-wrapper',
						'container_inclusive' => false,
						'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'page_titlebar_wrapper' ],
					],
				],
			],
			'blog_title'                                   => [
				'label'           => esc_html__( 'Blog Page Title', 'Avada' ),
				'description'     => esc_html__( 'Controls the title text that displays in the page title bar only if your front page displays your latest post in "Settings > Reading".', 'Avada' ),
				'id'              => 'blog_title',
				'default'         => esc_html__( 'Blog', 'Avada' ),
				'type'            => 'text',
				'required'        => [
					[
						'setting'  => 'blog_show_page_title_bar',
						'operator' => '!=',
						'value'    => 'hide',
					],
				],
				'partial_refresh' => [
					'blog_title_partial' => [
						'selector'            => '.avada-page-titlebar-wrapper',
						'container_inclusive' => false,
						'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'page_titlebar_wrapper' ],
					],
				],
			],
			'blog_subtitle'                                => [
				'label'           => esc_html__( 'Blog Page Subtitle', 'Avada' ),
				'description'     => esc_html__( 'Controls the subtitle text that displays in the page title bar only if your front page displays your latest post in "Settings > Reading".', 'Avada' ),
				'id'              => 'blog_subtitle',
				'default'         => '',
				'type'            => 'text',
				'required'        => [
					[
						'setting'  => 'blog_show_page_title_bar',
						'operator' => '!=',
						'value'    => 'hide',
					],
				],
				'partial_refresh' => [
					'blog_subtitle_partial' => [
						'selector'            => '.avada-page-titlebar-wrapper',
						'container_inclusive' => false,
						'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'page_titlebar_wrapper' ],
					],
				],
			],
			'blog_layout'                                  => [
				'label'           => esc_html__( 'Blog Layout', 'Avada' ),
				'description'     => esc_html__( 'Controls the layout for the assigned blog page in "Settings > Reading".', 'Avada' ),
				'id'              => 'blog_layout',
				'default'         => 'large',
				'type'            => 'select',
				'choices'         => [
					'large'            => esc_html__( 'Large', 'Avada' ),
					'medium'           => esc_html__( 'Medium', 'Avada' ),
					'large alternate'  => esc_html__( 'Large Alternate', 'Avada' ),
					'medium alternate' => esc_html__( 'Medium Alternate', 'Avada' ),
					'grid'             => esc_html__( 'Grid', 'Avada' ),
					'timeline'         => esc_html__( 'Timeline', 'Avada' ),
					'masonry'          => esc_html__( 'Masonry', 'Avada' ),
				],
				'update_callback' => [
					[
						'condition' => 'is_home',
						'operator'  => '===',
						'value'     => true,
					],
				],
				'edit_shortcut'   => [
					'selector'  => [ '.blog .fusion-blog-archive' ],
					'shortcuts' => [
						[
							'aria_label' => esc_html__( 'Edit Blog Options', 'Avada' ),
						],
					],
				],
			],
			'blog_archive_layout'                          => [
				'label'           => esc_html__( 'Blog Archive Layout', 'Avada' ),
				'description'     => esc_html__( 'Controls the layout for the blog archive pages.', 'Avada' ),
				'id'              => 'blog_archive_layout',
				'default'         => 'large',
				'type'            => 'select',
				'choices'         => [
					'large'            => esc_html__( 'Large', 'Avada' ),
					'medium'           => esc_html__( 'Medium', 'Avada' ),
					'large alternate'  => esc_html__( 'Large Alternate', 'Avada' ),
					'medium alternate' => esc_html__( 'Medium Alternate', 'Avada' ),
					'grid'             => esc_html__( 'Grid', 'Avada' ),
					'timeline'         => esc_html__( 'Timeline', 'Avada' ),
					'masonry'          => esc_html__( 'Masonry', 'Avada' ),
				],
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_category',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_tag',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_date',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_author',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
			],
			'blog_pagination_type'                         => [
				'label'           => esc_html__( 'Pagination Type', 'Avada' ),
				'description'     => esc_html__( 'Controls the pagination type for the assigned blog page in "Settings > Reading" or blog archive pages.', 'Avada' ),
				'id'              => 'blog_pagination_type',
				'default'         => 'pagination',
				'type'            => 'radio-buttonset',
				'choices'         => [
					'pagination'       => esc_html__( 'Pagination', 'Avada' ),
					'infinite_scroll'  => esc_html__( 'Infinite Scroll', 'Avada' ),
					'load_more_button' => esc_html__( 'Load More Button', 'Avada' ),
				],
				'update_callback' => [
					[
						'condition' => 'is_home',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'blog_load_more_posts_button_bg_color'         => [
				'label'       => esc_attr__( 'Load More Posts Button Background Color', 'fusion-core' ),
				'description' => esc_attr__( 'Controls the background color of the load more button for ajax post loading for blog archives.', 'fusion-core' ),
				'id'          => 'blog_load_more_posts_button_bg_color',
				'default'     => 'var(--awb-color7)',
				'type'        => 'color-alpha',
				'css_vars'    => [
					[
						'name'     => '--blog_load_more_posts_button_bg_color',
						'element'  => '.fusion-load-more-button',
						'callback' => [ 'sanitize_color' ],
					],
				],
				'required'    => [
					[
						'setting'  => 'blog_pagination_type',
						'operator' => '==',
						'value'    => 'load_more_button',
					],
				],
			],
			'blog_load_more_posts_button_text_color'       => [
				'label'       => esc_attr__( 'Load More Posts Button Text Color', 'fusion-core' ),
				'description' => esc_attr__( 'Controls the text color of the load more button for ajax post loading for blog archives.', 'fusion-core' ),
				'id'          => 'blog_load_more_posts_button_text_color',
				'default'     => 'var(--awb-color1)',
				'type'        => 'color-alpha',
				'css_vars'    => [
					[
						'name'     => '--blog_load_more_posts_button_text_color',
						'element'  => '.fusion-load-more-button',
						'callback' => [ 'sanitize_color' ],
					],
				],
				'required'    => [
					[
						'setting'  => 'blog_pagination_type',
						'operator' => '==',
						'value'    => 'load_more_button',
					],
				],
			],
			'blog_load_more_posts_hover_button_bg_color'   => [
				'label'       => esc_attr__( 'Load More Posts Button Hover Background Color', 'fusion-core' ),
				'description' => esc_attr__( 'Controls the hover background color of the load more button for ajax post loading for blog archives.', 'fusion-core' ),
				'id'          => 'blog_load_more_posts_hover_button_bg_color',
				'default'     => 'var(--awb-color5)',
				'type'        => 'color-alpha',
				'css_vars'    => [
					[
						'name'     => '--blog_load_more_posts_hover_button_bg_color',
						'element'  => '.fusion-load-more-button',
						'callback' => [ 'sanitize_color' ],
					],
				],
				'required'    => [
					[
						'setting'  => 'blog_pagination_type',
						'operator' => '==',
						'value'    => 'load_more_button',
					],
				],
			],
			'blog_load_more_posts_hover_button_text_color' => [
				'label'       => esc_attr__( 'Load More Posts Hover Button Text Color', 'fusion-core' ),
				'description' => esc_attr__( 'Controls the hover text color of the load more button for ajax post loading for blog archives.', 'fusion-core' ),
				'id'          => 'blog_load_more_posts_hover_button_text_color',
				'default'     => 'var(--awb-color1)',
				'type'        => 'color-alpha',
				'css_vars'    => [
					[
						'name'     => '--blog_load_more_posts_hover_button_text_color',
						'element'  => '.fusion-load-more-button',
						'callback' => [ 'sanitize_color' ],
					],
				],
				'required'    => [
					[
						'setting'  => 'blog_pagination_type',
						'operator' => '==',
						'value'    => 'load_more_button',
					],
				],
			],
			'blog_archive_grid_columns'                    => [
				'label'           => esc_html__( 'Number of Columns', 'Avada' ),
				'description'     => __( 'Controls the number of columns for grid and masonry layout when using it for the assigned blog page in "Settings > Reading" or blog archive pages. <strong>IMPORTANT:</strong> Masonry layout does not work with 1 column.', 'Avada' ),
				'id'              => 'blog_archive_grid_columns',
				'default'         => 3,
				'type'            => 'slider',
				'class'           => 'fusion-or-gutter',
				'choices'         => [
					'min'  => 1,
					'max'  => 6,
					'step' => 1,
				],
				'required'        => [
					[
						'setting'  => 'blog_layout',
						'operator' => '=',
						'value'    => 'grid',
					],
					[
						'setting'  => 'blog_layout',
						'operator' => '=',
						'value'    => 'masonry',
					],
					[
						'setting'  => 'blog_archive_layout',
						'operator' => '=',
						'value'    => 'grid',
					],
					[
						'setting'  => 'blog_archive_layout',
						'operator' => '=',
						'value'    => 'masonry',
					],
				],
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_home',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
			],
			'blog_archive_grid_column_spacing'             => [
				'label'           => esc_html__( 'Column Spacing', 'Avada' ),
				'description'     => esc_html__( 'Controls the column spacing for blog posts for grid and masonry layout when using it for the assigned blog page in "Settings > Reading" or blog archive pages.', 'Avada' ),
				'id'              => 'blog_archive_grid_column_spacing',
				'default'         => '40',
				'type'            => 'slider',
				'class'           => 'fusion-or-gutter',
				'choices'         => [
					'min'  => '0',
					'step' => '1',
					'max'  => '300',
					'edit' => 'yes',
				],
				'required'        => [
					[
						'setting'  => 'blog_layout',
						'operator' => '=',
						'value'    => 'grid',
					],
					[
						'setting'  => 'blog_layout',
						'operator' => '=',
						'value'    => 'masonry',
					],
					[
						'setting'  => 'blog_archive_layout',
						'operator' => '=',
						'value'    => 'grid',
					],
					[
						'setting'  => 'blog_archive_layout',
						'operator' => '=',
						'value'    => 'masonry',
					],
				],
				'transport'       => 'refresh',
				'css_vars'        => [
					[
						'name'          => '--blog_archive_grid_column_spacing',
						'value_pattern' => '$px',
					],
				],
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_home',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
			],
			'blog_equal_heights'                           => [
				'label'           => esc_html__( 'Equal Heights', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display grid boxes to equal heights per row.', 'Avada' ),
				'id'              => 'blog_equal_heights',
				'default'         => 0,
				'type'            => 'switch',
				'class'           => 'fusion-or-gutter',
				'required'        => [
					[
						'setting'  => 'blog_layout',
						'operator' => '=',
						'value'    => 'grid',
					],
					[
						'setting'  => 'blog_archive_layout',
						'operator' => '=',
						'value'    => 'grid',
					],
				],
				// Masonry prevents us from simply changing the class in the DOM, currently requires a full-refresh.
				'transport'       => 'refresh',
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_home',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
			],
			'blog_archive_grid_padding'                    => [
				'label'       => esc_html__( 'Blog Archive Grid Text Padding', 'Avada' ),
				'description' => esc_html__( 'Controls the top/right/bottom/left padding of the blog text when using grid / masonry or timeline layout. ', 'Avada' ),
				'id'          => 'blog_archive_grid_padding',
				'class'       => 'fusion-or-gutter',
				'choices'     => [
					'top'    => true,
					'bottom' => true,
					'left'   => true,
					'right'  => true,
				],
				'default'     => [
					'top'    => '30px',
					'bottom' => '20px',
					'left'   => '25px',
					'right'  => '25px',
				],
				'type'        => 'spacing',
				'required'    => [
					[
						'setting'  => 'blog_layout',
						'operator' => '=',
						'value'    => 'grid',
					],
					[
						'setting'  => 'blog_layout',
						'operator' => '=',
						'value'    => 'masonry',
					],
					[
						'setting'  => 'blog_layout',
						'operator' => '=',
						'value'    => 'timeline',
					],
					[
						'setting'  => 'blog_archive_layout',
						'operator' => '=',
						'value'    => 'grid',
					],
					[
						'setting'  => 'blog_archive_layout',
						'operator' => '=',
						'value'    => 'masonry',
					],
					[
						'setting'  => 'blog_archive_layout',
						'operator' => '=',
						'value'    => 'timeline',
					],
				],
				'css_vars'    => [
					[
						'name'    => '--blog_archive_grid_padding-top',
						'choice'  => 'top',
						'element' => '.fusion-post-content-wrapper',
					],
					[
						'name'    => '--blog_archive_grid_padding-bottom',
						'choice'  => 'bottom',
						'element' => '.fusion-post-content-wrapper',
					],
					[
						'name'    => '--blog_archive_grid_padding-left',
						'choice'  => 'left',
						'element' => '.fusion-post-content-wrapper',
					],
					[
						'name'    => '--blog_archive_grid_padding-right',
						'choice'  => 'right',
						'element' => '.fusion-post-content-wrapper',
					],
				],
			],
			'blog_layout_alignment'                        => [
				'label'           => esc_html__( 'Blog Archive Grid Content Alignment', 'Avada' ),
				'description'     => esc_html__( 'Controls the content alignment of the blog text when using grid / masonry or timeline layout.', 'Avada' ),
				'id'              => 'blog_layout_alignment',
				'default'         => '',
				'type'            => 'radio-buttonset',
				'choices'         => [
					''       => esc_html__( 'Text Flow', 'fusion-builder' ),
					'left'   => esc_html__( 'Left', 'fusion-builder' ),
					'center' => esc_html__( 'Center', 'fusion-builder' ),
					'right'  => esc_html__( 'Right', 'fusion-builder' ),
				],
				'class'           => 'fusion-or-gutter',
				'required'        => [
					[
						'setting'  => 'blog_layout',
						'operator' => '=',
						'value'    => 'grid',
					],
					[
						'setting'  => 'blog_layout',
						'operator' => '=',
						'value'    => 'masonry',
					],
					[
						'setting'  => 'blog_layout',
						'operator' => '=',
						'value'    => 'timeline',
					],
					[
						'setting'  => 'blog_archive_layout',
						'operator' => '=',
						'value'    => 'grid',
					],
					[
						'setting'  => 'blog_archive_layout',
						'operator' => '=',
						'value'    => 'masonry',
					],
					[
						'setting'  => 'blog_archive_layout',
						'operator' => '=',
						'value'    => 'timeline',
					],
				],
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_home',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
			],
			'content_length'                               => [
				'label'       => esc_html__( 'Blog Content Display', 'Avada' ),
				'description' => esc_html__( 'Controls if the blog content displays an excerpt or full content or is completely disabled for the assigned blog page in "Settings > Reading" or blog archive pages.', 'Avada' ),
				'id'          => 'content_length',
				'default'     => 'excerpt',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'excerpt'      => esc_html__( 'Excerpt', 'Avada' ),
					'full_content' => esc_html__( 'Full Content', 'Avada' ),
					'hide'         => esc_html__( 'No Text', 'Avada' ),
				],
			],
			'excerpt_length_blog'                          => [
				'label'           => esc_html__( 'Excerpt Length', 'Avada' ),
				'description'     => esc_html__( 'Controls post excerts length for the assigned blog page in "Settings > Reading" or blog archive pages. Limit is applied to number of letter or words depending on Basis for Excerpt Length option.', 'Avada' ),
				'id'              => 'excerpt_length_blog',
				'default'         => '10',
				'type'            => 'slider',
				'choices'         => [
					'min'  => '0',
					'max'  => '500',
					'step' => '1',
				],
				'required'        => [
					[
						'setting'  => 'content_length',
						'operator' => '==',
						'value'    => 'excerpt',
					],
				],
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_home',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
			],
			'strip_html_excerpt'                           => [
				'label'           => esc_html__( 'Strip HTML from Excerpt', 'Avada' ),
				'description'     => esc_html__( 'Turn on to strip HTML content from the excerpt for the assigned blog page in "Settings > Reading" or blog archive pages.', 'Avada' ),
				'id'              => 'strip_html_excerpt',
				'default'         => '1',
				'type'            => 'switch',
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_home',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
			],
			'featured_images'                              => [
				'label'           => esc_html__( 'Featured Image / Video on Blog Archive Page', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display featured images and videos on the blog archive pages.', 'Avada' ),
				'id'              => 'featured_images',
				'default'         => '1',
				'type'            => 'switch',
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_home',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
				'edit_shortcut'   => [
					'selector'  => [
						'body:not(.single-avada_portfolio) .fusion-featured-image-wrapper',
						'.single-avada_portfolio .fusion-featured-image-wrapper .fusion-post-slideshow',
					],
					'shortcuts' => [
						[
							'aria_label' => esc_html__( 'Edit Featured Image', 'Avada' ),
							'callback'   => 'fusionEditFeaturedImage',
							'css_class'  => '',
							'icon'       => 'fusiona-image',
						],
					],
				],
			],
			'dates_box_color'                              => [
				'label'       => esc_attr__( 'Blog Alternate Layout Date Box Color', 'fusion-builder' ),
				'description' => esc_attr__( 'Controls the color of the date box in blog alternate and recent posts layouts.', 'fusion-builder' ),
				'id'          => 'dates_box_color',
				'default'     => 'var(--awb-color2)',
				'type'        => 'color-alpha',
				'css_vars'    => [
					[
						'name'     => '--dates_box_color',
						'callback' => [ 'sanitize_color' ],
					],
				],
			],
			'alternate_date_format_month_year'             => [
				'label'       => esc_html__( 'Blog Alternate Layout Month and Year Format', 'Avada' ),
				'description' => __( 'Controls the month and year format for blog alternate layouts. <a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank" rel="noopener noreferrer">Formatting Date and Time</a>', 'Avada' ),
				'id'          => 'alternate_date_format_month_year',
				'default'     => 'm, Y',
				'type'        => 'text',
			],
			'alternate_date_format_day'                    => [
				'label'       => esc_html__( 'Blog Alternate Layout Day Format', 'Avada' ),
				'description' => __( 'Controls the day format for blog alternate layouts. <a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank" rel="noopener noreferrer">Formatting Date and Time</a>', 'Avada' ),
				'id'          => 'alternate_date_format_day',
				'default'     => 'j',
				'type'        => 'text',
			],
			'timeline_date_format'                         => [
				'label'       => esc_html__( 'Blog Timeline Layout Date Format', 'Avada' ),
				'description' => __( 'Controls the timeline label format for blog timeline layouts. <a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank" rel="noopener noreferrer">Formatting Date</a>', 'Avada' ),
				'id'          => 'timeline_date_format',
				'default'     => 'F Y',
				'type'        => 'text',
			],
		],
	];

	$blog_single_post_info_2 = [
		'label'       => esc_html__( 'Blog Single Post', 'Avada' ),
		'description' => '',
		'id'          => 'blog_single_post_info_2',
		'default'     => '',
		'icon'        => true,
		'type'        => 'sub-section',
		'fields'      => [
			'content_blog_single_post_template_override_notice' => [
				'id'          => 'content_blog_single_post_template_override_notice',
				'label'       => '',
				'hidden'      => ! $has_global_content,
				'description' => class_exists( 'Fusion_Template_Builder' ) ? sprintf(
					/* translators: %1$s: Content|Header|Footer|Page Title Bar. %2$s: Layout selection URL. */
					'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The options on this tab are not available because a global %1$s override is currently used. To edit your global layout please visit <a href="%2$s" target="_blank">this page</a>.', 'Avada' ) . '</div>',
					Fusion_Template_Builder::get_instance()->get_template_terms()['content']['label'],
					admin_url( 'admin.php?page=avada-layouts' )
				) : '',
				'type'        => 'custom',
			],
			'content_blog_single_post_template_notice' => [
				'id'          => 'content_blog_single_post_template_notice',
				'label'       => '',
				'hidden'      => $has_global_content,
				'description' => class_exists( 'Fusion_Template_Builder' ) ? sprintf(
					/* translators: %1$s: Live Builder. %2$s: Content|Header|Footer|Page Title Bar. %3$s: Layout selection URL. */
					'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> For more flexibility and a more modern, performant setup, we recommend using the %1$s. To create a custom %2$s Layout, <a href="%3$s" target="_blank">visit this page.</a>', 'Avada' ) . '</div>',
					isset( $template_terms['content']['alias'] ) ? $template_terms['content']['alias'] : '',
					Fusion_Template_Builder::get_instance()->get_template_terms()['content']['label'],
					admin_url( 'admin.php?page=avada-layouts' )
				) : '',
				'type'        => 'custom',
			],
			'blog_width_100'                           => [
				'label'           => esc_html__( '100% Width Page', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display blog posts at 100% browser width according to the window size. Turn off to follow site width.', 'Avada' ),
				'id'              => 'blog_width_100',
				'default'         => 0,
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_singular',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'featured_images_single'                   => [
				'label'           => esc_html__( 'Featured Image / Video on Single Blog Post', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display featured images and videos on single blog posts.', 'Avada' ),
				'id'              => 'featured_images_single',
				'default'         => '1',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_singular',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'blog_pn_nav'                              => [
				'label'           => esc_html__( 'Previous/Next Pagination', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display the previous/next post pagination for single blog posts.', 'Avada' ),
				'id'              => 'blog_pn_nav',
				'default'         => '1',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_singular',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'blog_post_title'                          => [
				'label'           => esc_html__( 'Post Title', 'Avada' ),
				'description'     => esc_html__( 'Controls if the post title displays above or below the featured post image or is disabled.', 'Avada' ),
				'id'              => 'blog_post_title',
				'default'         => 'below',
				'type'            => 'radio-buttonset',
				'hidden'          => $has_global_content,
				'choices'         => [
					'below'    => esc_html__( 'Below ', 'Avada' ),
					'above'    => esc_html__( 'Above', 'Avada' ),
					'disabled' => esc_html__( 'Disabled', 'Avada' ),
				],
				'update_callback' => [
					[
						'condition' => 'is_singular',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'blog_post_meta_position'                  => [
				'label'           => esc_html__( 'Meta Data Position', 'Avada' ),
				'description'     => esc_html__( 'Choose where the meta data is positioned.', 'Avada' ),
				'id'              => 'blog_post_meta_position',
				'default'         => 'below_article',
				'type'            => 'radio-buttonset',
				'hidden'          => $has_global_content,
				'choices'         => [
					'below_article' => esc_html__( 'Below Article', 'Avada' ),
					'below_title'   => esc_html__( 'Below Title', 'Avada' ),
				],
				'required'        => [
					[
						'setting'  => 'blog_post_title',
						'operator' => '!=',
						'value'    => 'disabled',
					],
				],
				'update_callback' => [
					[
						'condition' => 'is_singular',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'social_sharing_box'                       => [
				'label'           => esc_html__( 'Social Sharing Box', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display the social sharing box.', 'Avada' ),
				'id'              => 'social_sharing_box',
				'default'         => '1',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_singular',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'author_info'                              => [
				'label'           => esc_html__( 'Author Info Box', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display the author info box below posts.', 'Avada' ),
				'id'              => 'author_info',
				'default'         => '1',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_singular',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'related_posts'                            => [
				'label'           => esc_html__( 'Related Posts', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display related posts.', 'Avada' ),
				'id'              => 'related_posts',
				'default'         => '1',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_singular',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'blog_comments'                            => [
				'label'           => esc_html__( 'Comments', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display comments.', 'Avada' ),
				'id'              => 'blog_comments',
				'default'         => '1',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_singular',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
		],
	];

	$blog_meta_info = [
		'label'       => esc_html__( 'Blog Meta', 'Avada' ),
		'description' => '',
		'id'          => 'blog_meta',
		'default'     => '',
		'icon'        => true,
		'type'        => 'sub-section',
		'fields'      => [
			'blog_meta_important_note_info' => [
				'label'       => '',
				'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The on/off meta options on this tab only control the assigned blog page in "Settings > Reading" or the blog archives, not the blog element. The only options on this tab that work with the blog element are the Meta Data Font Size and Date Format options.', 'Avada' ) . '</div>',
				'id'          => 'blog_meta_important_note_info',
				'type'        => 'custom',
			],
			'post_meta'                     => [
				'label'       => esc_html__( 'Post Meta', 'Avada' ),
				'description' => esc_html__( 'Turn on to display post meta on blog posts. If set to "On", you can also control individual meta items below. If set to "Off" all meta items will be disabled.', 'Avada' ),
				'id'          => 'post_meta',
				'default'     => '1',
				'type'        => 'switch',
				/**
				 * WIP: Disabled for now because of FB.
				 *
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_home',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
				*/
			],
			'post_meta_author'              => [
				'label'       => esc_html__( 'Post Meta Author', 'Avada' ),
				'description' => esc_html__( 'Turn on to display the post meta author name.', 'Avada' ),
				'id'          => 'post_meta_author',
				'default'     => '1',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'post_meta',
						'operator' => '==',
						'value'    => '1',
					],
				],
				/**
				 * WIP: Disabled for now because of FB.
				 *
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_home',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
				*/
			],
			'post_meta_date'                => [
				'label'       => esc_html__( 'Post Meta Date', 'Avada' ),
				'description' => esc_html__( 'Turn on to display the post meta date.', 'Avada' ),
				'id'          => 'post_meta_date',
				'default'     => '1',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'post_meta',
						'operator' => '==',
						'value'    => '1',
					],
				],
				/**
				 * WIP: Disabled for now because of FB.
				 *
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_home',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
				*/
			],
			'post_meta_cats'                => [
				'label'       => esc_html__( 'Post Meta Categories', 'Avada' ),
				'description' => esc_html__( 'Turn on to display the post meta categories.', 'Avada' ),
				'id'          => 'post_meta_cats',
				'default'     => '1',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'post_meta',
						'operator' => '==',
						'value'    => '1',
					],
				],
				/**
				 * WIP: Disabled for now because of FB.
				 *
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_home',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
				*/
			],
			'post_meta_comments'            => [
				'label'       => esc_html__( 'Post Meta Comments', 'Avada' ),
				'description' => esc_html__( 'Turn on to display the post meta comments.', 'Avada' ),
				'id'          => 'post_meta_comments',
				'default'     => '1',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'post_meta',
						'operator' => '==',
						'value'    => '1',
					],
				],
				/**
				 * WIP: Disabled for now because of FB.
				 *
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_home',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
				*/
			],
			'post_meta_read'                => [
				'label'       => esc_html__( 'Post Meta Read More Link', 'Avada' ),
				'description' => esc_html__( 'Turn on to display the post meta read more link.', 'Avada' ),
				'id'          => 'post_meta_read',
				'default'     => '1',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'post_meta',
						'operator' => '==',
						'value'    => '1',
					],
				],
				/**
				 * WIP: Disabled for now because of FB.
				 *
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_home',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
				*/
			],
			'post_meta_tags'                => [
				'label'       => esc_html__( 'Post Meta Tags', 'Avada' ),
				'description' => esc_html__( 'Turn on to display the post meta tags.', 'Avada' ),
				'id'          => 'post_meta_tags',
				'default'     => '0',
				'type'        => 'switch',
				'required'    => [
					[
						'setting'  => 'post_meta',
						'operator' => '==',
						'value'    => '1',
					],
				],
				/**
				 * WIP: Disabled for now because of FB.
				 *
				'update_callback' => [
					[
						// 2-levels deep because we want this to be OR.
						[
							'condition' => 'is_home',
							'operator'  => '===',
							'value'     => true,
						],
						[
							'condition' => 'is_archive',
							'operator'  => '===',
							'value'     => true,
						],
					],
				],
				*/
			],
			'meta_font_size'                => [
				'label'       => esc_html__( 'Meta Data Font Size', 'Avada' ),
				'description' => esc_html__( 'Controls the font size for meta data text.', 'Avada' ),
				'id'          => 'meta_font_size',
				'default'     => '13px',
				'type'        => 'dimension',
				'css_vars'    => [
					[
						'name' => '--meta_font_size',
					],
				],
			],
			'date_format'                   => [
				'label'       => esc_html__( 'Date Format', 'Avada' ),
				'description' => sprintf(
					/* translators: Link to general settings in the dashboard. */
					__( 'Controls the date format for date meta data.  <a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank" rel="noopener noreferrer">Formatting Date and Time</a>. Leave empty to use the default value from your <a href="%s" target="_blank">WordPress Settings</a>', 'Avada' ),
					esc_url( admin_url( 'options-general.php' ) )
				),
				'id'          => 'date_format',
				'default'     => '',
				'type'        => 'text',
			],
		],
	];

	$sections['blog'] = [
		'label'    => esc_html__( 'Blog', 'Avada' ),
		'id'       => 'blog_section',
		'priority' => 15,
		'icon'     => 'el-icon-file-edit',
		'alt_icon' => 'fusiona-blog',
		'class'    => 'hidden-section-heading',
		'fields'   => [
			'blog_general_options'    => $blog_general_options,
			'blog_single_post_info_2' => $blog_single_post_info_2,
			'blog_meta_info'          => $blog_meta_info,
		],
	];

	return $sections;

}

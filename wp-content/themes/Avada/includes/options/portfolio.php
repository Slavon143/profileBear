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
 * Portfolio settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_portfolio( $sections ) {

	// Check if we have a global content override.
	$has_global_content        = false;
	$template_terms['content'] = [];
	if ( class_exists( 'Fusion_Template_Builder' ) ) {
		$default_layout     = Fusion_Template_Builder::get_default_layout();
		$has_global_content = isset( $default_layout['data']['template_terms'] ) && isset( $default_layout['data']['template_terms']['content'] ) && $default_layout['data']['template_terms']['content'];

		// PHP 5.6 compat.
		$template_terms = Fusion_Template_Builder::get_instance()->get_template_terms();
	}

	$sections['portfolio'] = [
		'label'    => esc_html__( 'Portfolio', 'Avada' ),
		'id'       => 'heading_portfolio',
		'priority' => 16,
		'icon'     => 'el-icon-th',
		'alt_icon' => 'fusiona-insertpicture',
		'class'    => 'hidden-section-heading',
		'fields'   => [
			'general_portfolio_options_subsection' => [
				'label'       => esc_html__( 'General Portfolio', 'Avada' ),
				'description' => '',
				'id'          => 'general_portfolio_options_subsection',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'portfolio_archive_layout'             => [
						'label'           => esc_html__( 'Portfolio Archive Layout', 'Avada' ),
						'description'     => esc_html__( 'Controls the layout for the portfolio archive pages.', 'Avada' ),
						'id'              => 'portfolio_archive_layout',
						'default'         => 'grid',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'grid'    => esc_html__( 'Grid', 'Avada' ),
							'masonry' => esc_html__( 'Masonry', 'Avada' ),
						],
						'update_callback' => [
							[
								'condition' => 'is_portfolio_archive',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'portfolio_archive_featured_image_size' => [
						'label'           => esc_html__( 'Portfolio Archive Featured Image Size', 'Avada' ),
						'description'     => __( 'Controls if the featured image size is fixed (cropped) or auto (full image ratio) for portfolio archive pages. <strong>IMPORTANT:</strong> Fixed works best with a standard 940px site width. Auto works best with larger site widths.', 'Avada' ),
						'id'              => 'portfolio_archive_featured_image_size',
						'default'         => 'full',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'cropped' => esc_html__( 'Fixed', 'Avada' ),
							'full'    => esc_html__( 'Auto', 'Avada' ),
						],
						'required'        => [
							[
								'setting'  => 'portfolio_archive_layout',
								'operator' => '==',
								'value'    => 'grid',
							],
						],
						'update_callback' => [
							[
								'condition' => 'is_portfolio_archive',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'portfolio_archive_columns'            => [
						'label'           => esc_html__( 'Portfolio Archive Number of Columns', 'Avada' ),
						'description'     => __( 'Set the number of columns per row for portfolio archive pages. With Carousel layout this specifies the maximum amount of columns. <strong>IMPORTANT:</strong> Masonry layout does not work with 1 column.', 'Avada' ),
						'id'              => 'portfolio_archive_columns',
						'default'         => 1,
						'type'            => 'slider',
						'choices'         => [
							'min'  => 1,
							'max'  => 6,
							'step' => '1',
						],
						'update_callback' => [
							[
								'condition' => 'is_portfolio_archive',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'portfolio_archive_column_spacing'     => [
						'label'       => esc_html__( 'Portfolio Archive Column Spacing', 'Avada' ),
						'description' => esc_html__( 'Controls the column spacing for portfolio items for archive pages.', 'Avada' ),
						'id'          => 'portfolio_archive_column_spacing',
						'default'     => '20',
						'type'        => 'slider',
						'choices'     => [
							'min'  => '0',
							'max'  => '300',
							'step' => '1',
						],
						'css_vars'    => [
							[
								'name'          => '--portfolio_archive_column_spacing',
								'value_pattern' => '$px',
							],
						],
					],
					'portfolio_equal_heights'              => [
						'label'       => esc_html__( 'Equal Heights', 'Avada' ),
						'description' => esc_html__( 'Turn on to display grid boxes with equal heights per row.', 'Avada' ),
						'id'          => 'portfolio_equal_heights',
						'default'     => 0,
						'type'        => 'switch',
						'class'       => 'fusion-or-gutter',
						'required'    => [
							[
								'setting'  => 'portfolio_archive_layout',
								'operator' => '=',
								'value'    => 'grid',
							],
						],
						'transport'   => 'postMessage',
					],
					'portfolio_archive_one_column_text_position' => [
						'label'           => esc_html__( 'Portfolio Archive Content Position', 'Avada' ),
						'description'     => esc_html__( 'Select if title, terms and excerpts should be displayed below or next to the featured images.', 'Avada' ),
						'id'              => 'portfolio_archive_one_column_text_position',
						'default'         => 'below',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'below'   => esc_html__( 'Below image', 'Avada' ),
							'floated' => esc_html__( 'Next to Image', 'Avada' ),
						],
						'required'        => [
							[
								'setting'  => 'portfolio_archive_layout',
								'operator' => '==',
								'value'    => 'grid',
							],
							[
								'setting'  => 'portfolio_archive_columns',
								'operator' => '==',
								'value'    => '1',
							],
						],
						'update_callback' => [
							[
								'condition' => 'is_portfolio_archive',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'portfolio_archive_items'              => [
						'label'           => esc_html__( 'Number of Portfolio Items Per Archive Page', 'Avada' ),
						'description'     => esc_html__( 'Controls the number of posts that display per page for portfolio archive pages. Set to -1 to display all. Set to 0 to use the number of posts from Settings > Reading.', 'Avada' ),
						'id'              => 'portfolio_archive_items',
						'default'         => '10',
						'type'            => 'slider',
						'choices'         => [
							'min'  => '-1',
							'max'  => '50',
							'step' => '1',
						],
						'update_callback' => [
							[
								'condition' => 'is_portfolio_archive',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'portfolio_archive_text_layout'        => [
						'label'           => esc_html__( 'Portfolio Archive Text Layout', 'Avada' ),
						'description'     => esc_html__( 'Controls if the portfolio text content is displayed boxed or unboxed or is completely disabled for portfolio archive pages.', 'Avada' ),
						'id'              => 'portfolio_archive_text_layout',
						'default'         => 'no_text',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'no_text' => esc_html__( 'No Text', 'Avada' ),
							'boxed'   => esc_html__( 'Boxed', 'Avada' ),
							'unboxed' => esc_html__( 'Unboxed', 'Avada' ),
						],
						'transport'       => 'refresh',
						'css_vars'        => [
							[
								'name'     => '--portfolio_archive_text_layout-padding',
								'element'  => '.fusion-portfolio-content',
								'callback' => [
									'conditional_return_value',
									[
										'value_pattern' => [
											'20px 0',
											'var(--portfolio_archive_layout_padding-top) var(--portfolio_archive_layout_padding-right) var(--portfolio_archive_layout_padding-bottom) var(--portfolio_archive_layout_padding-left)',
										],
										'conditions'    => [
											[ 'portfolio_archive_text_layout', '!==', 'boxed' ],
										],
									],
								],
							],
						],
						'update_callback' => [
							[
								'condition' => 'is_portfolio_archive',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'portfolio_archive_content_length'     => [
						'label'           => esc_html__( 'Portfolio Archive Text Display', 'Avada' ),
						'description'     => esc_html__( 'Choose how to display the post excerpt for portfolio archive pages.', 'Avada' ),
						'id'              => 'portfolio_archive_content_length',
						'default'         => 'excerpt',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'no_text'      => esc_html__( 'No Text', 'Avada' ),
							'excerpt'      => esc_html__( 'Excerpt', 'Avada' ),
							'full_content' => esc_html__( 'Full Content', 'Avada' ),
						],
						'required'        => [
							[
								'setting'  => 'portfolio_archive_text_layout',
								'operator' => '!=',
								'value'    => 'no_text',
							],
						],
						'update_callback' => [
							[
								'condition' => 'is_portfolio_archive',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'portfolio_archive_excerpt_length'     => [
						'label'           => esc_html__( 'Portfolio Archive Excerpt Length', 'Avada' ),
						'description'     => esc_html__( 'Controls the number of words in the excerpts for portfolio archive pages.', 'Avada' ),
						'id'              => 'portfolio_archive_excerpt_length',
						'default'         => '10',
						'type'            => 'slider',
						'choices'         => [
							'min'  => '0',
							'max'  => '500',
							'step' => '1',
						],
						'soft_dependency' => true,
						'update_callback' => [
							[
								'condition' => 'is_portfolio_archive',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'portfolio_archive_strip_html_excerpt' => [
						'label'           => esc_html__( 'Strip HTML from Excerpt', 'Avada' ),
						'description'     => esc_html__( 'Turn on to strip HTML content from the excerpt for portfolio archive pages.', 'Avada' ),
						'id'              => 'portfolio_archive_strip_html_excerpt',
						'default'         => '1',
						'type'            => 'switch',
						'update_callback' => [
							[
								'condition' => 'is_portfolio_archive',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'portfolio_archive_title_display'      => [
						'label'           => esc_html__( 'Portfolio Archive Title Display', 'Avada' ),
						'description'     => esc_html__( 'Controls what displays with the portfolio post title for portfolio archive pages.', 'Avada' ),
						'id'              => 'portfolio_archive_title_display',
						'default'         => 'all',
						'type'            => 'select',
						'choices'         => [
							'all'   => esc_html__( 'Title and Categories', 'Avada' ),
							'title' => esc_html__( 'Only Title', 'Avada' ),
							'cats'  => esc_html__( 'Only Categories', 'Avada' ),
							'none'  => esc_html__( 'None', 'Avada' ),
						],
						'update_callback' => [
							[
								'condition' => 'is_portfolio_archive',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'portfolio_archive_text_alignment'     => [
						'label'       => esc_html__( 'Portfolio Archive Text Alignment', 'Avada' ),
						'description' => esc_html__( 'Controls the alignment of the portfolio title, categories and excerpt text when using the Portfolio Text layouts in portfolio archive pages.', 'Avada' ),
						'id'          => 'portfolio_archive_text_alignment',
						'default'     => 'left',
						'type'        => 'radio-buttonset',
						'choices'     => [
							'left'   => esc_html__( 'Left', 'Avada' ),
							'center' => esc_html__( 'Center', 'Avada' ),
							'right'  => esc_html__( 'Right', 'Avada' ),
						],
						'css_vars'    => [
							[
								'name'    => '--portfolio_archive_text_alignment',
								'element' => '.fusion-portfolio-content-wrapper',
							],
						],
					],
					'portfolio_archive_layout_padding'     => [
						'label'           => esc_html__( 'Portfolio Archive Text Layout Padding', 'Avada' ),
						'description'     => esc_html__( 'Controls the padding for the portfolio text layout when using boxed mode in portfolio archive pages.', 'Avada' ),
						'id'              => 'portfolio_archive_layout_padding',
						'choices'         => [
							'top'    => true,
							'bottom' => true,
							'left'   => true,
							'right'  => true,
						],
						'default'         => [
							'top'    => '25px',
							'bottom' => '25px',
							'left'   => '25px',
							'right'  => '25px',
						],
						'type'            => 'spacing',
						'soft_dependency' => true,
						'css_vars'        => [
							[
								'name'    => '--portfolio_archive_layout_padding-top',
								'element' => '.fusion-portfolio-content',
								'choice'  => 'top',
							],
							[
								'name'    => '--portfolio_archive_layout_padding-bottom',
								'element' => '.fusion-portfolio-content',
								'choice'  => 'bottom',
							],
							[
								'name'    => '--portfolio_archive_layout_padding-left',
								'element' => '.fusion-portfolio-content',
								'choice'  => 'left',
							],
							[
								'name'    => '--portfolio_archive_layout_padding-right',
								'element' => '.fusion-portfolio-content',
								'choice'  => 'right',
							],
						],
					],
					'portfolio_archive_pagination_type'    => [
						'label'           => esc_html__( 'Portfolio Archive Pagination Type', 'Avada' ),
						'description'     => esc_html__( 'Controls the pagination type for portfolio archive pages.', 'Avada' ),
						'id'              => 'portfolio_archive_pagination_type',
						'default'         => 'pagination',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'pagination'       => esc_html__( 'Pagination', 'Avada' ),
							'infinite_scroll'  => esc_html__( 'Infinite Scroll', 'Avada' ),
							'load_more_button' => esc_html__( 'Load More Button', 'Avada' ),
						],
						'update_callback' => [
							[
								'condition' => 'is_portfolio_archive',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'portfolio_archive_load_more_posts_button_bg_color' => [
						'label'       => esc_attr__( 'Load More Posts Button Background Color', 'fusion-core' ),
						'description' => esc_attr__( 'Controls the background color of the load more button for ajax post loading for portfolio archives.', 'fusion-core' ),
						'id'          => 'portfolio_archive_load_more_posts_button_bg_color',
						'default'     => 'var(--awb-color7)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--portfolio_archive_load_more_posts_button_bg_color',
								'element'  => '.fusion-load-more-button',
								'callback' => [ 'sanitize_color' ],
							],
						],
						'required'    => [
							[
								'setting'  => 'portfolio_archive_pagination_type',
								'operator' => '==',
								'value'    => 'load_more_button',
							],
						],
					],
					'portfolio_archive_load_more_posts_button_text_color' => [
						'label'       => esc_attr__( 'Load More Posts Button Text Color', 'fusion-core' ),
						'description' => esc_attr__( 'Controls the text color of the load more button for ajax post loading for portfolio archives.', 'fusion-core' ),
						'id'          => 'portfolio_archive_load_more_posts_button_text_color',
						'default'     => 'var(--awb-color1)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--portfolio_archive_load_more_posts_button_text_color',
								'element'  => '.fusion-load-more-button',
								'callback' => [ 'sanitize_color' ],
							],
						],
						'required'    => [
							[
								'setting'  => 'portfolio_archive_pagination_type',
								'operator' => '==',
								'value'    => 'load_more_button',
							],
						],
					],
					'portfolio_archive_load_more_posts_hover_button_bg_color' => [
						'label'       => esc_attr__( 'Load More Posts Button Hover Background Color', 'fusion-core' ),
						'description' => esc_attr__( 'Controls the hover background color of the load more button for ajax post loading for portfolio archives.', 'fusion-core' ),
						'id'          => 'portfolio_archive_load_more_posts_hover_button_bg_color',
						'default'     => 'var(--awb-color5)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--portfolio_archive_load_more_posts_hover_button_bg_color',
								'element'  => '.fusion-load-more-button',
								'callback' => [ 'sanitize_color' ],
							],
						],
						'required'    => [
							[
								'setting'  => 'portfolio_archive_pagination_type',
								'operator' => '==',
								'value'    => 'load_more_button',
							],
						],
					],
					'portfolio_archive_load_more_posts_hover_button_text_color' => [
						'label'       => esc_attr__( 'Load More Posts Hover Button Text Color', 'fusion-core' ),
						'description' => esc_attr__( 'Controls the hover text color of the load more button for ajax post loading for portfolio archives.', 'fusion-core' ),
						'id'          => 'portfolio_archive_load_more_posts_hover_button_text_color',
						'default'     => 'var(--awb-color1)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--portfolio_archive_load_more_posts_hover_button_text_color',
								'element'  => '.fusion-load-more-button',
								'callback' => [ 'sanitize_color' ],
							],
						],
						'required'    => [
							[
								'setting'  => 'portfolio_archive_pagination_type',
								'operator' => '==',
								'value'    => 'load_more_button',
							],
						],
					],
					'portfolio_slug'                       => [
						'label'       => esc_html__( 'Portfolio Slug', 'Avada' ),
						'description' => esc_html__( 'The slug name cannot be the same name as a page name or the layout will break. This option changes the permalink when you use the permalink type as %postname%. Make sure to regenerate permalinks.', 'Avada' ),
						'id'          => 'portfolio_slug',
						'default'     => 'portfolio-items',
						'type'        => 'text',
					],
					'portfolio_meta_font_size'             => [
						'label'       => esc_html__( 'Meta Data Font Size', 'Avada' ),
						'description' => esc_html__( 'Controls the font size for meta data text.', 'Avada' ),
						'id'          => 'portfolio_meta_font_size',
						'default'     => '13px',
						'type'        => 'dimension',
						'css_vars'    => [
							[
								'name' => '--portfolio_meta_font_size',
							],
						],
					],
				],
			],
		],
	];

	$sections['portfolio']['fields']['portfolio_single_post_page_options_subsection'] = [
		'label'       => esc_html__( 'Portfolio Single Post', 'Avada' ),
		'description' => '',
		'id'          => 'portfolio_single_post_page_options_subsection',
		'icon'        => true,
		'type'        => 'sub-section',
		'fields'      => [
			'portfolio_single_post_template_override_notice' => [
				'id'          => 'portfolio_single_post_template_override_notice',
				'label'       => '',
				'hidden'      => ! $has_global_content,
				'description' => class_exists( 'Fusion_Template_Builder' ) ? sprintf(
					/* translators: 1: Content|Footer|Page Title Bar. 2: URL. */
					'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The options on this tab are not available because a global %1$s override is currently used. To edit your global layout please visit <a href="%2$s" target="_blank">this page</a>.', 'Avada' ) . '</div>',
					Fusion_Template_Builder::get_instance()->get_template_terms()['content']['label'],
					admin_url( 'admin.php?page=avada-layouts' )
				) : '',
				'type'        => 'custom',
			],
			'portfolio_single_post_template_notice' => [
				'id'          => 'portfolio_single_post_template_notice',
				'label'       => '',
				'description' => class_exists( 'Fusion_Template_Builder' ) ? sprintf(
					/* translators: %1$s: Live Builder. %2$s: Content|Header|Footer|Page Title Bar. %3$s: Layout selection URL. */
					'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> For more flexibility and a more modern, performant setup, we recommend using the %1$s. To create a custom %2$s Layout, <a href="%3$s" target="_blank">visit this page.</a>', 'Avada' ) . '</div>',
					isset( $template_terms['content']['alias'] ) ? $template_terms['content']['alias'] : '',
					Fusion_Template_Builder::get_instance()->get_template_terms()['content']['label'],
					admin_url( 'admin.php?page=avada-layouts' )
				) : '',
				'type'        => 'custom',
			],
			'portfolio_pn_nav'                      => [
				'label'           => esc_html__( 'Previous/Next Pagination', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display the previous/next post pagination for single portfolio posts.', 'Avada' ),
				'id'              => 'portfolio_pn_nav',
				'default'         => '1',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_portfolio_single',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'portfolio_width_100'                   => [
				'label'           => esc_html__( '100% Width Page', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display portfolio posts at 100% browser width according to the window size. Turn off to follow site width.', 'Avada' ),
				'id'              => 'portfolio_width_100',
				'default'         => '0',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_portfolio_single',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'portfolio_featured_image_width'        => [
				'label'           => esc_html__( 'Featured Image Column Size', 'Avada' ),
				'description'     => esc_html__( 'Controls if the featured image is half or full width on single portfolio posts.', 'Avada' ),
				'id'              => 'portfolio_featured_image_width',
				'default'         => 'full',
				'type'            => 'radio-buttonset',
				'hidden'          => $has_global_content,
				'choices'         => [
					'full' => esc_html__( 'Full Width', 'Avada' ),
					'half' => esc_html__( 'Half Width', 'Avada' ),
				],
				'required'        => [
					[
						'setting'  => 'portfolio_featured_images',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'update_callback' => [
					[
						'condition' => 'is_portfolio_single',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'portfolio_featured_images'             => [
				'label'           => esc_html__( 'Featured Image / Video on Single Post Page', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display featured images and videos on single portfolio posts.', 'Avada' ),
				'id'              => 'portfolio_featured_images',
				'default'         => '1',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_portfolio_single',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'show_first_featured_image'             => [
				'label'           => esc_html__( 'First Featured Image', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display the 1st featured image on single portfolio posts.', 'Avada' ),
				'id'              => 'show_first_featured_image',
				'default'         => '1',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'required'        => [
					[
						'setting'  => 'portfolio_featured_images',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'partial_refresh' => [
					'show_first_featured_image_partial' => [
						'selector'              => '.fusion-featured-image-wrapper',
						'container_inclusive'   => true,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'singular_featured_image' ],
						'success_trigger_event' => 'fusion-reinit-single-post-slideshow',
					],
				],
			],
			'portfolio_project_desc_title'          => [
				'label'           => esc_html__( 'Project Description Title', 'Avada' ),
				'description'     => esc_html__( 'Turn on to show the project description title on single portfolio posts.', 'Avada' ),
				'id'              => 'portfolio_project_desc_title',
				'default'         => '1',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_portfolio_single',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'portfolio_project_details'             => [
				'label'           => esc_html__( 'Project Details', 'Avada' ),
				'description'     => esc_html__( 'Turn on to show the project details title and content on single portfolio posts.', 'Avada' ),
				'id'              => 'portfolio_project_details',
				'default'         => '1',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_portfolio_single',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'portfolio_link_icon_target'            => [
				'label'       => esc_html__( 'Open Portfolio Links In New Window', 'Avada' ),
				'description' => esc_html__( 'Turn on to open the single post page, project url and copyright url links in a new window.', 'Avada' ),
				'id'          => 'portfolio_link_icon_target',
				'default'     => '0',
				'type'        => 'switch',
				'hidden'      => $has_global_content,
				// Don't change anything since it's not relavant in builder mode.
				'transport'   => 'postMessage',
			],
			'portfolio_author'                      => [
				'label'           => esc_html__( 'Author', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display the author name on single portfolio posts.', 'Avada' ),
				'id'              => 'portfolio_author',
				'default'         => '0',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_portfolio_single',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'portfolio_social_sharing_box'          => [
				'label'           => esc_html__( 'Social Sharing Box', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display the social sharing box on single portfolio posts.', 'Avada' ),
				'id'              => 'portfolio_social_sharing_box',
				'default'         => '1',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_portfolio_single',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'portfolio_related_posts'               => [
				'label'           => esc_html__( 'Related Projects', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display related projects on single portfolio posts.', 'Avada' ),
				'id'              => 'portfolio_related_posts',
				'default'         => '1',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_portfolio_single',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
			'portfolio_comments'                    => [
				'label'           => esc_html__( 'Comments', 'Avada' ),
				'description'     => esc_html__( 'Turn on to display comments on single portfolio posts.', 'Avada' ),
				'id'              => 'portfolio_comments',
				'default'         => '0',
				'type'            => 'switch',
				'hidden'          => $has_global_content,
				'update_callback' => [
					[
						'condition' => 'is_portfolio_single',
						'operator'  => '===',
						'value'     => true,
					],
				],
			],
		],
	];

	return $sections;

}

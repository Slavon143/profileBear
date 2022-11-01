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
 * Extra settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_extra( $sections ) {

	$contact_page_callback = [
		[
			'where'     => 'postMeta',
			'condition' => '_wp_page_template',
			'operator'  => '===',
			'value'     => 'contact.php',
		],
	];

	$sections['extras'] = [
		'label'    => esc_html__( 'Extras', 'Avada' ),
		'id'       => 'extra_section',
		'priority' => 24,
		'icon'     => 'el-icon-cogs',
		'alt_icon' => 'fusiona-cog',
		'fields'   => [
			'misc_options_section'   => [
				'label'       => esc_html__( 'Miscellaneous', 'Avada' ),
				'description' => '',
				'id'          => 'misc_options_section',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'sidenav_behavior'           => [
						'label'       => esc_html__( 'Side Navigation Behavior', 'Avada' ),
						'description' => esc_html__( 'Controls if the child pages show on click or hover for the side navigation page template.', 'Avada' ),
						'id'          => 'sidenav_behavior',
						'default'     => 'hover',
						'type'        => 'radio-buttonset',
						'choices'     => [
							'hover' => esc_html__( 'Hover', 'Avada' ),
							'click' => esc_html__( 'Click', 'Avada' ),
						],
						'output'      => [
							// This is for the avadaSideNavVars.sidenav_behavior var.
							[
								'element'           => 'helperElement',
								'property'          => 'bottom',
								'choice'            => 'top',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'choice'    => 'top',
										'globalVar' => 'avadaSideNavVars',
										'id'        => 'sidenav_behavior',
										'trigger'   => [ 'load' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'post_views'                 => [
						'label'       => esc_html__( 'Post Views Counter', 'Avada' ),
						'description' => esc_html__( 'Set the method to update the post views, or disable them entirely. "Page Load" option will update using PHP when a page is retrieved. "Ajax" will send an additional request after the page loads.', 'Avada' ),
						'id'          => 'post_views',
						'default'     => 'page_load',
						'type'        => 'radio-buttonset',
						'choices'     => [
							'page_load' => esc_html__( 'Page Load', 'Avada' ),
							'ajax'      => esc_html__( 'Ajax', 'Avada' ),
							'disabled'  => esc_html__( 'Disabled', 'Avada' ),
						],
					],
					'post_views_counting'        => [
						'label'       => esc_html__( 'Post Views User Type Counting', 'Avada' ),
						'description' => esc_html__( 'Select which types of users will increase post views on visit.', 'Avada' ),
						'id'          => 'post_views_counting',
						'default'     => 'all',
						'type'        => 'select',
						'choices'     => [
							'all'        => esc_html__( 'All', 'Avada' ),
							'logged_out' => esc_html__( 'Logged Out', 'Avada' ),
							'non_admins' => esc_html__( 'Non-Admins', 'Avada' ),
						],
					],
					'featured_image_placeholder' => [
						'label'           => esc_html__( 'Image Placeholders', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display a placeholder image for posts that do not have a featured image. This allows the post to display on portfolio archives and related posts/projects carousels.', 'Avada' ),
						'id'              => 'featured_image_placeholder',
						'default'         => '1',
						'type'            => 'switch',
						// Partial refresh for related-posts. Full refresh if js_callback returns false.
						'partial_refresh' => [
							'related_posts_layout_partial' => [
								'selector'              => 'section.related-posts',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'related_posts_template' ],
								'success_trigger_event' => 'fusion-reinit-related-posts-carousel',
								'js_callback'           => [ 'noPortfolioOnPage' ],
							],
						],
					],
					'excerpt_base'               => [
						'label'       => esc_html__( 'Basis for Excerpt Length', 'Avada' ),
						'description' => esc_html__( 'Controls if the excerpt length is based on words or characters.', 'Avada' ),
						'id'          => 'excerpt_base',
						'default'     => 'words',
						'type'        => 'radio-buttonset',
						'choices'     => [
							'words'      => esc_html__( 'Words', 'Avada' ),
							'characters' => esc_html__( 'Characters', 'Avada' ),
						],
					],
					'disable_excerpts'           => [
						'label'       => esc_html__( 'Display Excerpt Read More Symbol', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the read more symbol on excerpts throughout the site.', 'Avada' ),
						'id'          => 'disable_excerpts',
						'default'     => '1',
						'type'        => 'switch',
					],
					'blog_subtitle'              => [
						'label'       => esc_html__( 'Excerpt Read More Symbol', 'Avada' ),
						'description' => esc_html__( 'Set the excerpt read more symbol, HTML code is allowed. If left empty it will be set to [...].', 'Avada' ),
						'id'          => 'excerpt_read_more_symbol',
						'default'     => '[...]',
						'type'        => 'text',
						'required'    => [
							[
								'setting'  => 'disable_excerpts',
								'operator' => '==',
								'value'    => '1',
							],
						],
					],
					'link_read_more'             => [
						'label'       => esc_html__( 'Make Excerpt Symbol Link to Single Post Page', 'Avada' ),
						'description' => esc_html__( 'Turn on to have the read more symbol on excerpts link to the single post page.', 'Avada' ),
						'id'          => 'link_read_more',
						'default'     => '0',
						'type'        => 'switch',
						'required'    => [
							[
								'setting'  => 'disable_excerpts',
								'operator' => '==',
								'value'    => '1',
							],
						],
					],
					'avatar_shape'               => [
						'label'       => esc_html__( 'Avatar Shape', 'Avada' ),
						'description' => esc_html__( 'Set the shape for Avatars used in comments, author info and other areas.', 'Avada' ),
						'id'          => 'avatar_shape',
						'default'     => 'circle',
						'type'        => 'radio-buttonset',
						'choices'     => [
							'square' => esc_html__( 'Square', 'Avada' ),
							'circle' => esc_html__( 'Circle', 'Avada' ),
						],
						'output'      => [
							// Change classes in <body>.
							[
								'element'       => 'body',
								'function'      => 'attr',
								'attr'          => 'class',
								'value_pattern' => 'fusion-avatar-$',
								'remove_attrs'  => [ 'fusion-avatar-square', 'fusion-avatar-circle' ],
							],
						],
					],
					'comments_pages'             => [
						'label'       => esc_html__( 'Comments on Pages', 'Avada' ),
						'description' => esc_html__( 'Turn on to allow comments on regular pages.', 'Avada' ),
						'id'          => 'comments_pages',
						'default'     => '0',
						'type'        => 'switch',
					],
					'featured_images_pages'      => [
						'label'           => esc_html__( 'Featured Images on Pages', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display featured images on regular pages.', 'Avada' ),
						'id'              => 'featured_images_pages',
						'default'         => '1',
						'type'            => 'switch',
						'partial_refresh' => [
							'featured_images_pages_partial' => [
								'selector'              => '.fusion-featured-image-wrapper',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'singular_featured_image' ],
								'success_trigger_event' => 'fusion-reinit-single-post-slideshow',
							],
						],
					],
					'nofollow_social_links'      => [
						'label'       => esc_html__( 'Add "nofollow" to social links', 'Avada' ),
						'description' => esc_html__( 'Turn on to add "nofollow" attribute to all social links.', 'Avada' ),
						'id'          => 'nofollow_social_links',
						'default'     => '0',
						'type'        => 'switch',
						// No need to update the preview.
						'transport'   => 'postMessage',
					],
					'social_icons_new'           => [
						'label'       => esc_html__( 'Open Social Icons in a New Window', 'Avada' ),
						'description' => esc_html__( 'Turn on to allow social icons to open in a new window.', 'Avada' ),
						'id'          => 'social_icons_new',
						'default'     => '1',
						'type'        => 'switch',
						// No need to update the preview.
						'transport'   => 'postMessage',
					],
					'totop_position'             => [
						'label'       => esc_html__( 'ToTop Button Position', 'Avada' ),
						'description' => esc_html__( 'Controls the position of the ToTop button. On mobiles also non-floating layouts will be floating.', 'Avada' ),
						'id'          => 'totop_position',
						'default'     => 'right',
						'type'        => 'radio-buttonset',
						'choices'     => [
							'left'           => esc_html__( 'Left', 'Avada' ),
							'left_floating'  => esc_html__( 'Left Floating', 'Avada' ),
							'right'          => esc_html__( 'Right', 'Avada' ),
							'right_floating' => esc_html__( 'Right Floating', 'Avada' ),
						],
						'output'      => [
							// Change the avadaToTopVars.totop_position var.
							[
								'element'           => 'helperElement',
								'property'          => 'bottom',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'choice'    => 'top',
										'globalVar' => 'avadaToTopVars',
										'id'        => 'totop_position',
										'trigger'   => [ 'updateToTopPostion' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'totop_border_radius'        => [
						'label'       => esc_html__( 'ToTop Border Radius', 'Avada' ),
						'description' => esc_html__( 'Controls the border radius of the ToTop button. For non-floating layouts the border radius will only apply to the upper corners.', 'Avada' ),
						'id'          => 'totop_border_radius',
						'default'     => '6',
						'type'        => 'slider',
						'choices'     => [
							'min'  => '0',
							'max'  => '50',
							'step' => '1',
						],
						'css_vars'    => [
							[
								'name'          => '--totop_border_radius',
								'element'       => '#toTop',
								'value_pattern' => '$px',
							],
						],
					],
					'totop_scroll_down_only'     => [
						'label'       => esc_html__( 'ToTop Show on Scroll Down Only', 'Avada' ),
						'description' => esc_html__( 'Turn on to show the ToTop button on scroll down only. Otherwise it will always show if the page is scrolled.', 'Avada' ),
						'id'          => 'totop_scroll_down_only',
						'default'     => '1',
						'type'        => 'switch',
						'output'      => [
							// Change the avadaToTopVars.totop_scroll_down_only var.
							[
								'element'           => 'helperElement',
								'property'          => 'bottom',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'choice'    => 'top',
										'globalVar' => 'avadaToTopVars',
										'id'        => 'totop_scroll_down_only',
										'trigger'   => [ 'ready' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'custom_scrollbar'           => [
						'label'       => esc_html__( 'Custom Scrollbar', 'Avada' ),
						'description' => esc_html__( 'Turn on if you want to change the background and handle color of the scrollbar. Styling and support varies depending on the browser.', 'Avada' ),
						'id'          => 'custom_scrollbar',
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
										'element'   => 'html',
										'className' => 'awb-scroll',
									],

								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'scrollbar_background'       => [
						'label'       => esc_html__( 'Scrollbar Background', 'Avada' ),
						'description' => esc_html__( 'Controls the background color for the scrollbar.', 'Avada' ),
						'id'          => 'scrollbar_background',
						'default'     => 'var(--awb-color2)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--scrollbar_background',
								'callback' => [ 'sanitize_color' ],
							],
						],
						'required'    => [
							[
								'setting'  => 'custom_scrollbar',
								'operator' => '==',
								'value'    => '1',
							],
						],
					],
					'scrollbar_handle'           => [
						'label'       => esc_html__( 'Scrollbar Handle Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color for the scrollbar handle.', 'Avada' ),
						'id'          => 'scrollbar_handle',
						'default'     => 'var(--awb-color4)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--scrollbar_handle',
								'callback' => [ 'sanitize_color' ],
							],
						],
						'required'    => [
							[
								'setting'  => 'custom_scrollbar',
								'operator' => '==',
								'value'    => '1',
							],
						],
					],
					'faq_slug'                   => [
						'label'       => esc_html__( 'FAQ Slug', 'Avada' ),
						'description' => esc_html__( 'The slug name cannot be the same name as a page name or the layout will break. This option changes the permalink when you use the permalink type as %postname%. Make sure to regenerate permalinks.', 'Avada' ),
						'id'          => 'faq_slug',
						'default'     => 'faq-items',
						'type'        => 'text',
					],
				],
			],
			'related_posts_section'  => [
				'label'       => esc_html__( 'Related Posts / Projects', 'Avada' ),
				'description' => '',
				'id'          => 'related_posts_section',
				'type'        => 'sub-section',
				'fields'      => [
					'related_posts_layout'         => [
						'label'           => esc_html__( 'Related Posts / Projects Layout', 'Avada' ),
						'description'     => esc_html__( 'Controls the layout style for related posts and related projects.', 'Avada' ),
						'id'              => 'related_posts_layout',
						'default'         => 'title_on_rollover',
						'type'            => 'select',
						'choices'         => [
							'title_on_rollover' => esc_html__( 'Title on rollover', 'Avada' ),
							'title_below_image' => esc_html__( 'Title below image', 'Avada' ),
						],
						// Partial refresh for related-posts.
						'partial_refresh' => [
							'related_posts_layout_partial' => [
								'selector'              => 'section.related-posts',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'related_posts_template' ],
								'success_trigger_event' => 'fusion-reinit-related-posts-carousel',
							],
						],
					],
					'number_related_posts'         => [
						'label'           => esc_html__( 'Number of Related Posts / Projects', 'Avada' ),
						'description'     => esc_html__( 'Controls the number of related posts and projects that display on a single post.', 'Avada' ),
						'id'              => 'number_related_posts',
						'default'         => '4',
						'type'            => 'slider',
						'choices'         => [
							'min'  => '0',
							'max'  => '30',
							'step' => '1',
						],
						// Partial refresh for related-posts.
						'partial_refresh' => [
							'number_related_posts_partial' => [
								'selector'              => 'section.related-posts',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'related_posts_template' ],
								'success_trigger_event' => 'fusion-reinit-related-posts-carousel',
							],
						],
					],
					'related_posts_columns'        => [
						'label'           => esc_html__( 'Related Posts / Projects Maximum Columns', 'Avada' ),
						'description'     => esc_html__( 'Controls the number of columns for the related posts and projects layout.', 'Avada' ),
						'id'              => 'related_posts_columns',
						'default'         => 4,
						'type'            => 'slider',
						'choices'         => [
							'min'  => 1,
							'max'  => 6,
							'step' => 1,
						],
						// Partial refresh for related-posts.
						'partial_refresh' => [
							'related_posts_columns_partial' => [
								'selector'              => 'section.related-posts',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'related_posts_template' ],
								'success_trigger_event' => 'fusion-reinit-related-posts-carousel',
							],
						],
					],
					'related_posts_column_spacing' => [
						'label'           => esc_html__( 'Related Posts / Projects Column Spacing', 'Avada' ),
						'description'     => esc_html__( 'Controls the amount of spacing between columns for the related posts and projects.', 'Avada' ),
						'id'              => 'related_posts_column_spacing',
						'default'         => '48',
						'type'            => 'slider',
						'choices'         => [
							'min'  => '0',
							'step' => '1',
							'max'  => '300',
							'edit' => 'yes',
						],
						// Partial refresh for related-posts.
						'partial_refresh' => [
							'related_posts_column_spacing_partial' => [
								'selector'              => 'section.related-posts',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'related_posts_template' ],
								'success_trigger_event' => 'fusion-reinit-related-posts-carousel',
							],
						],
					],
					'related_posts_image_size'     => [
						'label'           => esc_html__( 'Related Posts / Projects Image Size', 'Avada' ),
						'description'     => esc_html__( 'Controls if the featured image size is fixed (cropped) or auto (full image ratio) for related posts and projects. IMPORTANT: Fixed works best with a standard 940px site width. Auto works best with larger site widths.', 'Avada' ),
						'id'              => 'related_posts_image_size',
						'default'         => 'cropped',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'cropped' => esc_html__( 'Fixed', 'Avada' ),
							'full'    => esc_html__( 'Auto', 'Avada' ),
						],
						// Partial refresh for related-posts.
						'partial_refresh' => [
							'related_posts_image_size_partial' => [
								'selector'              => 'section.related-posts',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'related_posts_template' ],
								'success_trigger_event' => 'fusion-reinit-related-posts-carousel',
							],
						],
					],
					'related_posts_autoplay'       => [
						'label'           => esc_html__( 'Related Posts / Projects Autoplay', 'Avada' ),
						'description'     => esc_html__( 'Turn on to autoplay the related posts and project carousel.', 'Avada' ),
						'id'              => 'related_posts_autoplay',
						'default'         => '0',
						'type'            => 'switch',
						// Partial refresh for related-posts.
						'partial_refresh' => [
							'related_posts_autoplay_partial' => [
								'selector'              => 'section.related-posts',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'related_posts_template' ],
								'success_trigger_event' => 'fusion-reinit-related-posts-carousel',
							],
						],
					],
					'related_posts_speed'          => [
						'label'       => esc_html__( 'Related Posts / Projects Speed', 'Avada' ),
						'description' => esc_html__( 'Controls the speed of related posts and project carousel. ex: 1000 = 1 second.', 'Avada' ),
						'id'          => 'related_posts_speed',
						'default'     => '2500',
						'type'        => 'slider',
						'choices'     => [
							'min'  => '1000',
							'max'  => '20000',
							'step' => '250',
						],
						'output'      => [
							// Change the fusionCarouselVars.related_posts_speed var.
							[
								'element'           => 'helperElement',
								'property'          => 'bottom',
								'js_callback'       => [
									'fusionGlobalScriptSet',
									[
										'choice'    => 'top',
										'globalVar' => 'fusionCarouselVars',
										'id'        => 'related_posts_speed',
										'trigger'   => [ 'ready' ],
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'related_posts_navigation'     => [
						'label'           => esc_html__( 'Related Posts / Projects Show Navigation', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display navigation arrows on the carousel.', 'Avada' ),
						'id'              => 'related_posts_navigation',
						'default'         => '1',
						'type'            => 'switch',
						// Partial refresh for related-posts.
						'partial_refresh' => [
							'related_posts_navigation_partial' => [
								'selector'              => 'section.related-posts',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'related_posts_template' ],
								'success_trigger_event' => 'fusion-reinit-related-posts-carousel',
							],
						],
					],
					'related_posts_swipe'          => [
						'label'           => esc_html__( 'Related Posts / Projects Mouse Scroll', 'Avada' ),
						'description'     => esc_html__( 'Turn on to enable mouse drag control on the carousel.', 'Avada' ),
						'id'              => 'related_posts_swipe',
						'default'         => '0',
						'type'            => 'switch',
						// Partial refresh for related-posts.
						'partial_refresh' => [
							'related_posts_swipe_partial' => [
								'selector'              => 'section.related-posts',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'related_posts_template' ],
								'success_trigger_event' => 'fusion-reinit-related-posts-carousel',
							],
						],
					],
					'related_posts_swipe_items'    => [
						'label'           => esc_html__( 'Related Posts / Projects Scroll Items', 'Avada' ),
						'description'     => esc_html__( 'Controls the number of items that scroll at one time. Set to 0 to scroll the number of visible items.', 'Avada' ),
						'id'              => 'related_posts_swipe_items',
						'default'         => '0',
						'type'            => 'slider',
						'choices'         => [
							'min'  => '0',
							'max'  => '15',
							'step' => '1',
						],
						// Partial refresh for related-posts.
						'partial_refresh' => [
							'related_posts_swipe_items_partial' => [
								'selector'              => 'section.related-posts',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'related_posts_template' ],
								'success_trigger_event' => 'fusion-reinit-related-posts-carousel',
							],
						],
					],
				],
			],
			'rollover_sub_section'   => [
				'label'       => esc_html__( 'Featured Image Rollover', 'Avada' ),
				'description' => '',
				'id'          => 'rollover_sub_section',
				'type'        => 'sub-section',
				'fields'      => [
					'image_rollover'              => [
						'label'        => esc_html__( 'Image Rollover', 'Avada' ),
						'description'  => esc_html__( 'Turn on to display the rollover graphic on blog and portfolio featured images.', 'Avada' ),
						'id'           => 'image_rollover',
						'default'      => '1',
						'type'         => 'switch',
						// Prevents full refresh if js_callback returns false.
						'full_refresh' => [
							'image_rollover_layout_partial' => [
								'js_callback' => [ 'isRolloverOnPage' ],
							],
						],
					],
					'image_rollover_direction'    => [
						'label'           => esc_html__( 'Image Rollover Direction', 'Avada' ),
						'description'     => esc_html__( 'Controls the direction the rollover starts from.', 'Avada' ),
						'id'              => 'image_rollover_direction',
						'default'         => 'left',
						'type'            => 'select',
						'choices'         => [
							'fade'            => esc_html__( 'Fade', 'Avada' ),
							'left'            => esc_html__( 'Left', 'Avada' ),
							'right'           => esc_html__( 'Right', 'Avada' ),
							'bottom'          => esc_html__( 'Bottom', 'Avada' ),
							'top'             => esc_html__( 'Top', 'Avada' ),
							'center_horiz'    => esc_html__( 'Center Horizontal', 'Avada' ),
							'center_vertical' => esc_html__( 'Center Vertical', 'Avada' ),
						],
						'soft_dependency' => true,
						'output'          => [
							// Change classes in <body>.
							[
								'element'       => 'body',
								'function'      => 'attr',
								'attr'          => 'class',
								'value_pattern' => 'avada-image-rollover-direction-$',
								'remove_attrs'  => [ 'avada-image-rollover-direction-fade', 'avada-image-rollover-direction-left', 'avada-image-rollover-direction-right', 'avada-image-rollover-direction-bottom', 'avada-image-rollover-direction-top', 'avada-image-rollover-direction-center_horiz', 'avada-image-rollover-direction-center_vertical' ],
							],
						],
					],
					'image_rollover_icon_size'    => [
						'label'       => esc_html__( 'Image Rollover Icon Font Size', 'Avada' ),
						'description' => esc_html__( 'Controls the size of the rollover icons.', 'Avada' ),
						'id'          => 'image_rollover_icon_size',
						'default'     => '15px',
						'type'        => 'dimension',
						'required'    => [
							[
								'setting'  => 'image_rollover',
								'operator' => '==',
								'value'    => '1',
							],
						],
						'css_vars'    => [
							[
								'name' => '--image_rollover_icon_size',
							],
						],
					],
					'image_rollover_icons'        => [
						'label'        => esc_html__( 'Image Rollover Icons', 'fusion-builder' ),
						'description'  => esc_html__( 'Choose which icons display.', 'fusion-builder' ),
						'id'           => 'image_rollover_icons',
						'default'      => 'linkzoom',
						'type'         => 'radio-buttonset',
						'choices'      => [
							'linkzoom' => esc_html__( 'Link + Zoom', 'Avada' ),
							'link'     => esc_attr__( 'Link', 'Avada' ),
							'zoom'     => esc_attr__( 'Zoom', 'Avada' ),
							'no'       => esc_attr__( 'No Icons', 'Avada' ),
						],
						'required'     => [
							[
								'setting'  => 'image_rollover',
								'operator' => '==',
								'value'    => '1',
							],
						],
						// Prevents full refresh if js_callback returns false.
						'full_refresh' => [
							'image_rollover_icons_partial' => [
								'js_callback' => [ 'isRolloverOnPage' ],
							],
						],
					],
					'title_image_rollover'        => [
						'label'           => esc_html__( 'Image Rollover Title', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the post title in the image rollover.', 'Avada' ),
						'id'              => 'title_image_rollover',
						'default'         => '1',
						'type'            => 'switch',
						'soft_dependency' => true,
						// Prevents full refresh if js_callback returns false.
						'full_refresh'    => [
							'title_image_rollover_partial' => [
								'js_callback' => [ 'isRolloverOnPage' ],
							],
						],
					],
					'cats_image_rollover'         => [
						'label'           => esc_html__( 'Image Rollover Categories', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the post categories in the image rollover.', 'Avada' ),
						'id'              => 'cats_image_rollover',
						'default'         => '1',
						'type'            => 'switch',
						'soft_dependency' => true,
						// Prevents full refresh if js_callback returns false.
						'full_refresh'    => [
							'cats_image_rollover_partial' => [
								'js_callback' => [ 'isRolloverOnPage' ],
							],
						],
					],
					'icon_circle_image_rollover'  => [
						'label'           => esc_html__( 'Image Rollover Icon Circle', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the icon background circle in the image rollover.', 'Avada' ),
						'id'              => 'icon_circle_image_rollover',
						'default'         => '1',
						'type'            => 'switch',
						'soft_dependency' => true,
						'output'          => [
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ '', 'true' ],
										'element'   => 'body',
										'className' => 'avada-image-rollover-circle-yes',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ '', 'false' ],
										'element'   => 'body',
										'className' => 'avada-image-rollover-circle-no',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'image_gradient_top_color'    => [
						'label'           => esc_html__( 'Image Rollover Gradient Top Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the top color of the image rollover background.', 'Avada' ),
						'id'              => 'image_gradient_top_color',
						'type'            => 'color-alpha',
						'default'         => 'hsla(var(--awb-color4-h),var(--awb-color4-s),var(--awb-color4-l),calc(var(--awb-color4-a) - 20%))',
						'soft_dependency' => true,
						'css_vars'        => [
							[
								'name'     => '--image_gradient_top_color',
								'element'  => '.fusion-image-wrapper',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'image_gradient_bottom_color' => [
						'label'           => esc_html__( 'Image Rollover Gradient Bottom Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the bottom color of the image rollover background.', 'Avada' ),
						'id'              => 'image_gradient_bottom_color',
						'default'         => 'var(--awb-color4)',
						'type'            => 'color-alpha',
						'soft_dependency' => true,
						'css_vars'        => [
							[
								'name'     => '--image_gradient_bottom_color',
								'element'  => '.fusion-rollover',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'image_rollover_text_color'   => [
						'label'           => esc_html__( 'Image Rollover Element Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the color of image rollover text and icon circular backgrounds.', 'Avada' ),
						'id'              => 'image_rollover_text_color',
						'default'         => 'var(--awb-color8)',
						'type'            => 'color-alpha',
						'soft_dependency' => true,
						'css_vars'        => [
							[
								'name'     => '--image_rollover_text_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'image_rollover_icon_color'   => [
						'label'           => esc_html__( 'Image Rollover Icon Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the color of the icons in the image rollover.', 'Avada' ),
						'id'              => 'image_rollover_icon_color',
						'default'         => 'var(--awb-color1)',
						'type'            => 'color-alpha',
						'soft_dependency' => true,
						'css_vars'        => [
							[
								'name'     => '--image_rollover_icon_color',
								'element'  => '.fusion-rollover',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
				],
			],
			'pagination_box_section' => [
				'label'       => esc_html__( 'Pagination', 'Avada' ),
				'description' => '',
				'id'          => 'pagination_box_section',
				'type'        => 'sub-section',
				'fields'      => [
					'pagination_important_note_info' => [
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The options on this tab apply to all pagination throughout the site, including the 3rd party plugins that Avada has design integration with.', 'Avada' ) . '</div>',
						'id'          => 'pagination_important_note_info',
						'type'        => 'custom',
					],
					'pagination_sizing'              => [
						'type'        => 'radio-buttonset',
						'label'       => esc_html__( 'Pagination Sizing', 'Avada' ),
						'description' => esc_html__( 'Set on which dimension the pagination box size should be based.', 'Avada' ),
						'id'          => 'pagination_sizing',
						'default'     => 'width_height',
						'choices'     => [
							'width_height' => esc_html__( 'Width/Height Based', 'Avada' ),
							'padding'      => esc_html__( 'Padding Based', 'Avada' ),
						],
						'output'      => [
							// Change classes in <body>.
							[
								'element'       => 'body',
								'function'      => 'attr',
								'attr'          => 'class',
								'value_pattern' => 'avada-has-pagination-$',
								'remove_attrs'  => [ 'avada-has-pagination-padding', 'avada-has-pagination-width_height' ],
							],
						],
					],
					'pagination_width_height'        => [
						'label'       => esc_html__( 'Pagination Box Width/Height', 'Avada' ),
						'description' => esc_html__( 'Controls the width and height of the displayed page links.', 'Avada' ),
						'id'          => 'pagination_width_height',
						'default'     => '30',
						'type'        => 'slider',
						'choices'     => [
							'min'  => '5',
							'max'  => '100',
							'step' => '1',
						],
						'required'    => [
							[
								'setting'  => 'pagination_sizing',
								'operator' => '!=',
								'value'    => 'padding',
							],
						],
						'css_vars'    => [
							[
								'name'          => '--pagination_width_height',
								'value_pattern' => '$px',
							],
						],
					],
					'pagination_box_padding'         => [
						'label'       => esc_html__( 'Pagination Box Padding', 'Avada' ),
						'description' => esc_html__( 'Controls the padding inside the pagination boxes.', 'Avada' ),
						'id'          => 'pagination_box_padding',
						'units'       => false,
						'default'     => [
							'width'  => '6px',
							'height' => '2px',
						],
						'type'        => 'dimensions',
						'required'    => [
							[
								'setting'  => 'pagination_sizing',
								'operator' => '=',
								'value'    => 'padding',
							],
						],
						'css_vars'    => [
							[
								'name'   => '--pagination_box_padding-width',
								'choice' => 'width',
							],
							[
								'name'   => '--pagination_box_padding-height',
								'choice' => 'height',
							],
						],
					],
					'pagination_border_width'        => [
						'label'       => esc_html__( 'Pagination Border Width', 'Avada' ),
						'description' => esc_html__( 'Controls the border width of the displayed page links.', 'Avada' ),
						'id'          => 'pagination_border_width',
						'default'     => '1',
						'type'        => 'slider',
						'choices'     => [
							'min'  => '0',
							'max'  => '25',
							'step' => '1',
						],
						'css_vars'    => [
							[
								'name'          => '--pagination_border_width',
								'value_pattern' => '$px',
							],
						],
					],
					'pagination_border_radius'       => [
						'label'       => esc_html__( 'Pagination Border Radius', 'Avada' ),
						'description' => esc_html__( 'Controls the border radius of the displayed page links. Values of half the overall width or higher will yield circular links.', 'Avada' ),
						'id'          => 'pagination_border_radius',
						'default'     => '0',
						'type'        => 'slider',
						'choices'     => [
							'min'  => '0',
							'max'  => '50',
							'step' => '1',
						],
						'css_vars'    => [
							[
								'name'          => '--pagination_border_radius',
								'value_pattern' => '$px',
							],
						],
					],
					'pagination_text_display'        => [
						'label'       => esc_html__( 'Pagination Text Display', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the "Previous/Next" text.', 'Avada' ),
						'id'          => 'pagination_text_display',
						'default'     => '1',
						'type'        => 'switch',
						'output'      => [
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ '', 'true' ],
										'element'   => 'body',
										'className' => 'fusion-show-pagination-text',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'pagination_font_size'           => [
						'label'       => esc_html__( 'Pagination Font Size', 'Avada' ),
						'description' => esc_html__( 'Controls the size of the pagination text.', 'Avada' ),
						'id'          => 'pagination_font_size',
						'default'     => '13px',
						'type'        => 'dimension',
						'css_vars'    => [
							[
								'name' => '--pagination_font_size',
							],
						],
					],
					'pagination_range'               => [
						'label'        => esc_html__( 'Pagination Range', 'Avada' ),
						'description'  => esc_html__( 'Controls the number of page links displayed left and right of current page.', 'Avada' ),
						'id'           => 'pagination_range',
						'default'      => '1',
						'type'         => 'slider',
						'choices'      => [
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						],
						// Prevents full refresh if js_callback returns false.
						'full_refresh' => [
							'related_posts_layout_partial' => [
								'js_callback' => [ 'isPaginationOnPage' ],
							],
						],
					],
					'pagination_start_end_range'     => [
						'label'        => esc_html__( 'Pagination Start / End Range', 'Avada' ),
						'description'  => esc_html__( 'Controls the number of page links displayed at the start and at the end of pagination.', 'Avada' ),
						'id'           => 'pagination_start_end_range',
						'default'      => '0',
						'type'         => 'slider',
						'choices'      => [
							'min'  => '0',
							'max'  => '10',
							'step' => '1',
						],
						// Prevents full refresh if js_callback returns false.
						'full_refresh' => [
							'pagination_start_end_range_partial' => [
								'js_callback' => [ 'isPaginationOnPage' ],
							],
						],
					],
				],
			],
			'gridbox_section'        => [
				'label'       => esc_html__( 'Grid / Masonry', 'Avada' ),
				'description' => '',
				'id'          => 'gridbox_section',
				'type'        => 'sub-section',
				'fields'      => [
					'gridbox_styling_important_note_info' => [
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> These are Grid Box Styling global options that apply to grid boxes throughout the site; blog grid and timeline, portfolio boxed layout and WooCommerce boxes. Blog / Portfolio elements also have options to override these.', 'Avada' ) . '</div>',
						'id'          => 'gridbox_styling_important_note_info',
						'type'        => 'custom',
					],
					'timeline_bg_color'                   => [
						'label'       => esc_html__( 'Grid Box Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color for the grid boxes.', 'Avada' ),
						'id'          => 'timeline_bg_color',
						'default'     => 'rgba(255,255,255,0)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--timeline_bg_color',
								'callback' => [ 'sanitize_color' ],
							],
							[
								'name'     => '--timeline_bg_color-20px-transparent',
								'callback' => [
									'return_string_if_transparent',
									[
										'transparent' => '',
										'opaque'      => '20px',
									],
								],
							],
							[
								'name'     => '--timeline_bg_color-not-transparent',
								'callback' => [ 'get_non_transparent_color', '' ],
							],
						],
					],
					'timeline_color'                      => [
						'label'       => esc_html__( 'Grid Element Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of borders/date box/timeline dots and arrows for the grid boxes.', 'Avada' ),
						'id'          => 'timeline_color',
						'default'     => 'var(--awb-color2)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'        => '--timeline_color',
								'js_callback' => [ 'timeLineColorCallback' ],
							],
						],
						'output'      => [
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ '', 'full-transparent' ],
										'element'   => 'html',
										'className' => 'avada-has-transparent-timeline_color',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'grid_separator_style_type'           => [
						'label'       => esc_html__( 'Grid Separator Style', 'Avada' ),
						'description' => __( 'Controls the line style of grid separators. <strong>NOTE:</strong> For blog and portfolio grids at least one meta data field must be enabled and excerpt or full content must be shown in order that the separator will be displayed.', 'Avada' ),
						'id'          => 'grid_separator_style_type',
						'default'     => 'double|solid',
						'type'        => 'select',
						'choices'     => [
							'none'          => esc_html__( 'No Style', 'Avada' ),
							'single|solid'  => esc_html__( 'Single Border Solid', 'Avada' ),
							'double|solid'  => esc_html__( 'Double Border Solid', 'Avada' ),
							'single|dashed' => esc_html__( 'Single Border Dashed', 'Avada' ),
							'double|dashed' => esc_html__( 'Double Border Dashed', 'Avada' ),
							'single|dotted' => esc_html__( 'Single Border Dotted', 'Avada' ),
							'double|dotted' => esc_html__( 'Double Border Dotted', 'Avada' ),
							'shadow'        => esc_html__( 'Shadow', 'Avada' ),
						],
						'output'      => [
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'updateGridSeps',
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'grid_separator_color'                => [
						'label'       => esc_html__( 'Grid Separator Color', 'Avada' ),
						'description' => esc_html__( 'Controls the line style color of grid separators.', 'Avada' ),
						'id'          => 'grid_separator_color',
						'default'     => 'var(--awb-color3)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--grid_separator_color',
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
										'condition' => [ '', 'full-transparent' ],
										'element'   => 'body',
										'className' => 'avada-has-transparent-grid-sep-color',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'grid_masonry_heading'                => [
						'label'       => esc_html__( 'Masonry Options', 'Avada' ),
						'description' => '',
						'id'          => 'grid_masonry_heading',
						'type'        => 'info',
					],
					'gridbox_masonry_important_note_info' => [
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> These are Masonry global options that apply to the Blog / Portfolio / Gallery elements in addition to Blog and Portfolio archives. Blog / Portfolio / Gallery elements also have options to override these.', 'Avada' ) . '</div>',
						'id'          => 'gridbox_masonry_important_note_info',
						'type'        => 'custom',
					],
					'masonry_grid_ratio'                  => [
						'label'        => esc_html__( 'Masonry Image Aspect Ratio', 'Avada' ),
						'description'  => __( 'Set the ratio to decide when an image should become landscape (ratio being width : height) and portrait (ratio being height : width). <strong>IMPORTANT:</strong> The value of "1.0" represents a special case, which will use the auto calculated ratios like in versions prior to Avada 5.5.', 'Avada' ),
						'id'           => 'masonry_grid_ratio',
						'default'      => '1.5',
						'type'         => 'slider',
						'choices'      => [
							'min'  => 1.0,
							'max'  => 4.0,
							'step' => 0.1,
						],
						// Prevents full refresh if js_callback returns false.
						'full_refresh' => [
							'masonry_grid_ratio_partial' => [
								'js_callback' => [ 'isMasonryOnPage' ],
							],
						],
					],
					'masonry_width_double'                => [
						'label'        => esc_html__( 'Masonry 2x2 Width', 'Avada' ),
						'description'  => __( 'This option decides when a square 1x1 image should become 2x2. This will not apply to images that highly favor landscape or portrait layouts. <strong>IMPORTANT:</strong> There is a “Masonry Image Layout” setting for every image in the WP media library that allows you to manually set how an image will appear (1x1, landscape, portrait or 2x2), regardless of the original ratio.', 'Avada' ),
						'id'           => 'masonry_width_double',
						'default'      => '2000',
						'type'         => 'slider',
						'choices'      => [
							'min'  => 200,
							'max'  => 5120,
							'step' => 1,
						],
						// Prevents full refresh if js_callback returns false.
						'full_refresh' => [
							'masonry_width_double_partial' => [
								'js_callback' => [ 'isMasonryOnPage' ],
							],
						],
					],
				],
			],
		],
	];

	return $sections;

}

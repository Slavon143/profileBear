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
 * Sidebar settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_sidebars( $sections ) {

	/**
	 * Register sidebar options for blog/portfolio/woocommerce archive pages.
	 */
	global $wp_registered_sidebars;
	$sidebar_options[] = 'None';

	for ( $i = 0; $i < 1; $i++ ) {

		$sidebars = $wp_registered_sidebars;
		if ( is_array( $sidebars ) && ! empty( $sidebars ) ) {
			foreach ( $sidebars as $sidebar ) {
				$sidebar_options[] = $sidebar['name'];
			}
		}

		$sidebars = Sidebar_Generator::get_sidebars();
		if ( is_array( $sidebars ) && ! empty( $sidebars ) ) {
			foreach ( $sidebars as $key => $value ) {
				$sidebar_options[] = $value;
			}
		}
	}
	$sidebars_array = [];
	foreach ( $sidebar_options as $sidebar_option ) {
		$sidebars_array[ $sidebar_option ] = $sidebar_option;
	}
	$sidebar_options = $sidebars_array;

	$sidebars_update_callback = [
		[
			'where'     => 'postMeta',
			'condition' => '_wp_page_template',
			'operator'  => '!==',
			'value'     => '100-width.php',
		],
	];

	$sections['sidebars'] = [
		'label'    => esc_html__( 'Sidebars', 'Avada' ),
		'id'       => 'heading_sidebars',
		'is_panel' => true,
		'priority' => 10,
		'icon'     => 'el-icon-website',
		'alt_icon' => 'fusiona-sidebar',
		'fields'   => [
			'sidebars_styling'                             => [
				'label'  => esc_html__( 'Sidebar Styling', 'Avada' ),
				'id'     => 'sidebars_styling',
				'icon'   => true,
				'type'   => 'sub-section',
				'fields' => [
					'responsive_sidebar_order' => [
						'id'              => 'responsive_sidebar_order',
						'label'           => esc_html__( 'Sidebar Responsive Order', 'Avada' ),
						'description'     => esc_html__( 'Choose the order of sidebars and main content area on mobile layouts through drag & drop sorting.', 'Avada' ),
						'type'            => 'sortable',
						'choices'         => [
							'content'   => esc_html__( 'Content', 'Avada' ),
							'sidebar'   => esc_html__( 'Sidebar 1', 'Avada' ),
							'sidebar-2' => esc_html__( 'Sidebar 2', 'Avada' ),
						],
						'default'         => 'content,sidebar,sidebar-2',
						'required'        => [
							[
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							],
						],
						'update_callback' => $sidebars_update_callback,
					],
					'sidebar_sticky'           => [
						'label'           => esc_html__( 'Sticky Sidebars', 'Avada' ),
						'description'     => esc_html__( 'Select the sidebar(s) that should remain sticky when scrolling the page. If the sidebar content is taller than the screen, it acts like a normal sidebar until the bottom of the sidebar is within the viewport, which will then remain fixed in place as you scroll down.', 'Avada' ),
						'id'              => 'sidebar_sticky',
						'default'         => 'none',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'none'        => esc_html__( 'None', 'Avada' ),
							'sidebar_one' => esc_html__( 'Sidebar 1', 'Avada' ),
							'sidebar_two' => esc_html__( 'Sidebar 2', 'Avada' ),
							'both'        => esc_html__( 'Both', 'Avada' ),
						],
						'update_callback' => $sidebars_update_callback,
					],
					'sidebar_padding'          => [
						'label'       => esc_html__( 'Sidebar Padding', 'Avada' ),
						'description' => esc_html__( 'Controls the sidebar padding.', 'Avada' ),
						'id'          => 'sidebar_padding',
						'default'     => '0px',
						'type'        => 'dimension',
						'choices'     => [ 'px', '%' ],
						'css_vars'    => [
							[
								'name'    => '--sidebar_padding',
								'element' => '.sidebar',
							],
							[
								'name'     => '--sidebar_padding-percent_to_vw',
								'element'  => '.sidebar',
								'callback' => [ 'string_replace', [ '%', 'vw' ] ],
							],
						],
					],
					'sidebar_bg_color'         => [
						'label'       => esc_html__( 'Sidebar Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of the sidebar.', 'Avada' ),
						'id'          => 'sidebar_bg_color',
						'default'     => 'rgba(255,255,255,0)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--sidebar_bg_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'sidebar_widget_bg_color'  => [
						'label'       => esc_html__( 'Sidebar Widget Heading Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of the widget title box. If left transparent the widget title will be unboxed.', 'Avada' ),
						'id'          => 'sidebar_widget_bg_color',
						'default'     => 'rgba(255,255,255,0)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--sidebar_widget_bg_color',
								'element'  => '.sidebar',
								'callback' => [ 'sanitize_color' ],
							],
							[
								'name'     => '--sidebar_widget_bg_color-opaque-padding',
								'element'  => '.sidebar',
								'callback' => [
									'return_string_if_transparent',
									[
										'transparent' => '',
										'opaque'      => '9px 15px',
									],
								],
							],
						],
					],
					'sidew_font_size'          => [
						'label'       => esc_html__( 'Sidebar Widget Heading Font Size', 'Avada' ),
						'description' => esc_html__( 'Controls the font size of the widget heading text.', 'Avada' ),
						'id'          => 'sidew_font_size',
						'default'     => '18px',
						'type'        => 'dimension',
						'css_vars'    => [
							[
								'name'    => '--sidew_font_size',
								'element' => '.sidebar',
							],
						],
					],
					'sidebar_heading_color'    => [
						'label'       => esc_html__( 'Sidebar Widget Headings Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of the sidebar widget heading text.', 'Avada' ),
						'id'          => 'sidebar_heading_color',
						'default'     => 'var(--awb-color8)',
						'type'        => 'color-alpha',
						'css_vars'    => [
							[
								'name'     => '--sidebar_heading_color',
								'element'  => '.sidebar',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
				],
			],
			'pages_sidebars_section'                       => [
				'label'  => esc_html__( 'Pages', 'Avada' ),
				'id'     => 'pages_sidebars_section',
				'icon'   => true,
				'type'   => 'sub-section',
				'fields' => [
					'pages_sidebar'        => [
						'label'           => esc_html__( 'Global Page Sidebar 1', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 1 that will display on all pages.', 'Avada' ),
						'id'              => 'pages_sidebar',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'pages_sidebar_2'      => [
						'label'           => esc_html__( 'Global Page Sidebar 2', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 2 that will display on all pages. Sidebar 2 can only be used if sidebar 1 is selected.', 'Avada' ),
						'id'              => 'pages_sidebar_2',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'pages_global_sidebar' => [
						'label'           => esc_html__( 'Force Global Sidebars For Pages', 'Avada' ),
						'description'     => esc_html__( 'Turn on if you want to use the same sidebars on all pages. This option overrides the page options.', 'Avada' ),
						'id'              => 'pages_global_sidebar',
						'default'         => '0',
						'type'            => 'switch',
						'update_callback' => $sidebars_update_callback,
					],
					'default_sidebar_pos'  => [
						'label'           => esc_html__( 'Global Page Sidebar Position', 'Avada' ),
						'description'     => esc_html__( 'Controls the position of sidebar 1 for all pages. If sidebar 2 is selected, it will display on the opposite side.', 'Avada' ),
						'id'              => 'default_sidebar_pos',
						'default'         => 'Right',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'Left'  => esc_html__( 'Left', 'Avada' ),
							'Right' => esc_html__( 'Right', 'Avada' ),
						],
						'update_callback' => $sidebars_update_callback,
					],
				],
			],
			'portfolio_posts_sidebars_section'             => [
				'label'       => esc_html__( 'Portfolio Posts', 'Avada' ),
				'description' => '',
				'id'          => 'portfolio_posts_sidebars_section',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'portfolio_sidebar'          => [
						'label'           => esc_html__( 'Global Portfolio Post Sidebar 1', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 1 that will display on all portfolio posts.', 'Avada' ),
						'id'              => 'portfolio_sidebar',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'portfolio_sidebar_2'        => [
						'label'           => esc_html__( 'Global Portfolio Post Sidebar 2', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 2 that will display on all portfolio posts. Sidebar 2 can only be used if sidebar 1 is selected.', 'Avada' ),
						'id'              => 'portfolio_sidebar_2',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'portfolio_global_sidebar'   => [
						'label'           => esc_html__( 'Force Global Sidebars For Portfolio Posts', 'Avada' ),
						'description'     => esc_html__( 'Turn on if you want to use the same sidebars on all portfolio posts. This option overrides the portfolio post options.', 'Avada' ),
						'id'              => 'portfolio_global_sidebar',
						'default'         => '0',
						'type'            => 'switch',
						'update_callback' => $sidebars_update_callback,
					],
					'portfolio_sidebar_position' => [
						'label'           => esc_html__( 'Global Portfolio Sidebar Position', 'Avada' ),
						'description'     => esc_html__( 'Controls the position of sidebar 1 for all portfolio posts and archive pages. If sidebar 2 is selected, it will display on the opposite side.', 'Avada' ),
						'id'              => 'portfolio_sidebar_position',
						'default'         => 'Right',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'Left'  => esc_html__( 'Left', 'Avada' ),
							'Right' => esc_html__( 'Right', 'Avada' ),
						],
						'update_callback' => $sidebars_update_callback,
					],
				],
			],
			'portfolio_archive_category_pages_sidebars_section' => [
				'label'       => esc_html__( 'Portfolio Archive', 'Avada' ),
				'description' => '',
				'id'          => 'portfolio_archive_category_pages_sidebars_section',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'portfolio_archive_important_note_info' => [
						'label'       => '',
						/* translators: "Portfolio Posts sidebar" link. */
						'description' => '<div class="fusion-redux-important-notice">' . sprintf( __( '<strong>IMPORTANT NOTE:</strong> The sidebar position for portfolio archive pages is controlled by the option on the %s tab.', 'Avada' ), '<a href="' . admin_url( 'themes.php?page=avada_options&amp;lang=en#portfolio_sidebar' ) . '" target="_blank">' . esc_html__( 'Portfolio Posts sidebar', 'Avada' ) . '</a>' ) . '</div>',
						'id'          => 'portfolio_archive_important_note_info',
						'type'        => 'custom',
					],
					'portfolio_archive_sidebar'   => [
						'label'           => esc_html__( 'Portfolio Archive Sidebar 1', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 1 that will display on the portfolio archive pages.', 'Avada' ),
						'id'              => 'portfolio_archive_sidebar',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'portfolio_archive_sidebar_2' => [
						'label'           => esc_html__( 'Portfolio Archive Sidebar 2', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 2 that will display on the portfolio archive pages. Sidebar 2 can only be used if sidebar 1 is selected.', 'Avada' ),
						'id'              => 'portfolio_archive_sidebar_2',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
				],
			],
			'blog_posts_sidebars_section'                  => [
				'label'       => esc_html__( 'Blog Posts', 'Avada' ),
				'description' => '',
				'id'          => 'blog_posts_sidebars_section',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'posts_sidebar'         => [
						'label'         => esc_html__( 'Global Blog Post Sidebar 1', 'Avada' ),
						'description'   => esc_html__( 'Select sidebar 1 that will display on all blog posts.', 'Avada' ),
						'id'            => 'posts_sidebar',
						'default'       => 'None',
						'type'          => 'select',
						'choices'       => $sidebar_options,
						'edit_shortcut' => [
							'selector'  => [ '#sidebar.fusion-widget-area' ],
							'shortcuts' => [
								[
									'aria_label'    => esc_html__( 'Edit Global Sidebar Options', 'Avada' ),
									'callback'      => 'fusionEditGlobalSidebar',
									'css_class'     => '',
									'icon'          => 'fusiona-cog',
									'link_to_template_if_override_active' => 'content',
									'override_icon' => 'fusiona-content',
								],
								[
									'aria_label' => esc_html__( 'Edit Sidebar Options', 'Avada' ),
									'css_class'  => '',
									'icon'       => 'fusiona-settings',
									'context'    => 'po',
									'disable_on_template_override' => 'content',
								],
								[
									'aria_label' => esc_html__( 'Edit Sidebar Widgets', 'Avada' ),
									'css_class'  => 'fusion-edit-sidebar',
									'link'       => admin_url( 'widgets.php' ),
									'disable_on_template_override' => 'content',
								],
							],
						],
					],
					'posts_sidebar_2'       => [
						'label'         => esc_html__( 'Global Blog Post Sidebar 2', 'Avada' ),
						'description'   => esc_html__( 'Select sidebar 2 that will display on all blog posts. Sidebar 2 can only be used if sidebar 1 is selected.', 'Avada' ),
						'id'            => 'posts_sidebar_2',
						'default'       => 'None',
						'type'          => 'select',
						'choices'       => $sidebar_options,
						'edit_shortcut' => [
							'selector'  => [ '#sidebar-2.fusion-widget-area' ],
							'shortcuts' => [
								[
									'aria_label' => esc_html__( 'Edit Global Sidebar Options', 'Avada' ),
									'callback'   => 'fusionEditGlobalSidebar',
									'css_class'  => '',
									'icon'       => 'fusiona-cog',
								],
								[
									'aria_label' => esc_html__( 'Edit Sidebar Options', 'Avada' ),
									'css_class'  => '',
									'icon'       => 'fusiona-settings',
									'context'    => 'po',
								],
								[
									'aria_label' => esc_html__( 'Edit Sidebar Widgets', 'Avada' ),
									'link'       => admin_url( 'widgets.php' ),
								],
							],
						],
					],
					'posts_global_sidebar'  => [
						'label'       => esc_html__( 'Force Global Sidebars For Blog Posts', 'Avada' ),
						'description' => esc_html__( 'Turn on if you want to use the same sidebars on all blog posts. This option overrides the blog post options.', 'Avada' ),
						'id'          => 'posts_global_sidebar',
						'default'     => '0',
						'type'        => 'switch',
					],
					'blog_sidebar_position' => [
						'label'       => esc_html__( 'Global Blog Sidebar Position', 'Avada' ),
						'description' => esc_html__( 'Controls the position of sidebar 1 for all blog posts and archive pages. If sidebar 2 is selected, it will display on the opposite side.', 'Avada' ),
						'id'          => 'blog_sidebar_position',
						'default'     => 'Right',
						'type'        => 'radio-buttonset',
						'choices'     => [
							'Left'  => esc_html__( 'Left', 'Avada' ),
							'Right' => esc_html__( 'Right', 'Avada' ),
						],
					],
				],
			],
			'blog_archive_category_pages_sidebars_section' => [
				'label'       => esc_html__( 'Blog Archive', 'Avada' ),
				'description' => '',
				'id'          => 'blog_archive_category_pages_sidebars_section',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'blog_archive_important_note_info' => [
						'label'       => '',
						/* translators: "Blog Posts sidebar" link. */
						'description' => '<div class="fusion-redux-important-notice">' . sprintf( __( '<strong>IMPORTANT NOTE:</strong> The sidebar position for blog archive pages is controlled by the option on the %s tab.', 'Avada' ), '<a href="' . admin_url( 'themes.php?page=avada_options&amp;lang=en#posts_sidebar' ) . '" target="_blank">' . esc_html__( 'Blog Posts sidebar', 'Avada' ) . '</a>' ) . '</div>',
						'id'          => 'blog_archive_important_note_info',
						'type'        => 'custom',
					],
					'blog_archive_sidebar'             => [
						'label'           => esc_html__( 'Blog Archive Sidebar 1', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 1 that will display on the blog archive pages.', 'Avada' ),
						'id'              => 'blog_archive_sidebar',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'blog_archive_sidebar_2'           => [
						'label'           => esc_html__( 'Blog Archive Sidebar 2', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 2 that will display on the blog archive pages. Sidebar 2 can only be used if sidebar 1 is selected.', 'Avada' ),
						'id'              => 'blog_archive_sidebar_2',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
				],
			],
			'search_sidebars_section'                      => [
				'label'       => esc_html__( 'Search Page', 'Avada' ),
				'description' => '',
				'id'          => 'search_only',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'search_sidebar'          => [
						'label'           => esc_html__( 'Search Page Sidebar 1', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 1 that will display on the search results page.', 'Avada' ),
						'id'              => 'search_sidebar',
						'default'         => 'Blog Sidebar',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'search_sidebar_2'        => [
						'label'           => esc_html__( 'Search Page Sidebar 2', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 2 that will display on the search results page. Sidebar 2 can only be used if sidebar 1 is selected.', 'Avada' ),
						'id'              => 'search_sidebar_2',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'search_sidebar_position' => [
						'label'           => esc_html__( 'Search Sidebar Position', 'Avada' ),
						'description'     => esc_html__( 'Controls the position of sidebar 1 for the search results page. If sidebar 2 is selected, it will display on the opposite side.', 'Avada' ),
						'id'              => 'search_sidebar_position',
						'default'         => 'Right',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'Left'  => esc_html__( 'Left', 'Avada' ),
							'Right' => esc_html__( 'Right', 'Avada' ),
						],
						'update_callback' => $sidebars_update_callback,
					],
				],
			],
			'woocommerce_products_sidebars_section'        => ( Avada::$is_updating || class_exists( 'WooCommerce' ) ) ? [
				'label'  => esc_html__( 'WooCommerce Products', 'Avada' ),
				'id'     => 'woocommerce_products_sidebars_section',
				'icon'   => true,
				'type'   => 'sub-section',
				'fields' => [
					'woo_sidebar'          => [
						'label'           => esc_html__( 'Global WooCommerce Product Sidebar 1', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 1 that will display on all WooCommerce products.', 'Avada' ),
						'id'              => 'woo_sidebar',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'woo_sidebar_2'        => [
						'label'           => esc_html__( 'Global WooCommerce Product Sidebar 2', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 2 that will display on all WooCommerce products. Sidebar 2 can only be used if sidebar 1 is selected.', 'Avada' ),
						'id'              => 'woo_sidebar_2',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'woo_global_sidebar'   => [
						'label'           => esc_html__( 'Force Global Sidebars For WooCommerce Products', 'Avada' ),
						'description'     => esc_html__( 'Turn on if you want to use the same sidebars on all WooCommerce products. This option overrides the WooCommerce post options.', 'Avada' ),
						'id'              => 'woo_global_sidebar',
						'default'         => '0',
						'type'            => 'switch',
						'update_callback' => $sidebars_update_callback,
					],
					'woo_sidebar_position' => [
						'label'           => esc_html__( 'Global WooCommerce Sidebar Position', 'Avada' ),
						'description'     => esc_html__( 'Controls the position of sidebar 1 for all WooCommerce products and archive pages. If sidebar 2 is selected, it will display on the opposite side.', 'Avada' ),
						'id'              => 'woo_sidebar_position',
						'default'         => 'Right',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'Left'  => esc_html__( 'Left', 'Avada' ),
							'Right' => esc_html__( 'Right', 'Avada' ),
						],
						'update_callback' => $sidebars_update_callback,
					],
				],
			] : [],
			'woocommerce_archive_category_pages_sidebars_section' => ( Avada::$is_updating || class_exists( 'WooCommerce' ) ) ? [
				'label'       => esc_html__( 'WooCommerce Archive', 'Avada' ),
				'description' => '',
				'id'          => 'woocommerce_archive_category_pages_sidebars_section',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'woocommerce_archive_important_note_info' => [
						'label'       => '',
						/* translators: "WooCommerce Products sidebar" link. */
						'description' => '<div class="fusion-redux-important-notice">' . sprintf( __( '<strong>IMPORTANT NOTE:</strong> The sidebar position for WooCommerce archive pages is controlled by the option on the %s tab.', 'Avada' ), '<a href="' . admin_url( 'themes.php?page=avada_options&amp;lang=en#woo_sidebar' ) . '" target="_blank">' . esc_html__( 'WooCommerce Products sidebar', 'Avada' ) . '</a>' ) . '</div>',
						'id'          => 'woocommerce_archive_important_note_info',
						'type'        => 'custom',
					],
					'woocommerce_archive_sidebar'   => [
						'label'           => esc_html__( 'WooCommerce Archive Sidebar 1', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 1 that will display on the WooCommerce archive pages.', 'Avada' ),
						'id'              => 'woocommerce_archive_sidebar',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'woocommerce_archive_sidebar_2' => [
						'label'           => esc_html__( 'WooCommerce Archive Sidebar 2', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 2 that will display on the WooCommerce archive pages. Sidebar 2 can only be used if sidebar 1 is selected.', 'Avada' ),
						'id'              => 'woocommerce_archive_sidebar_2',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
				],
			] : [],
			'ec_global_sidebar_heading'                    => ( Avada::$is_updating || class_exists( 'Tribe__Events__Main' ) ) ? [
				'label'  => esc_html__( 'Events Calendar', 'Avada' ),
				'id'     => 'ec_global_sidebar_heading',
				'type'   => 'sub-section',
				'fields' => [
					'ec_sidebar'        => [
						'label'           => esc_html__( 'Global Events Calendar Sidebar 1', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 1 that will display on all Events Calendar posts and archives pages.', 'Avada' ),
						'id'              => 'ec_sidebar',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'ec_sidebar_2'      => [
						'label'           => esc_html__( 'Global Events Calendar Sidebar 2', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 2 that will display on all all Events Calendar posts and archive pages. Sidebar 2 can only be used if sidebar 1 is selected.', 'Avada' ),
						'id'              => 'ec_sidebar_2',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'ec_global_sidebar' => [
						'label'           => esc_html__( 'Force Global Sidebars For Events Calendar Posts', 'Avada' ),
						'description'     => esc_html__( 'Turn on if you want to use the same sidebars on all Events Calendar posts. This option overrides the Events Calendar post options.', 'Avada' ),
						'id'              => 'ec_global_sidebar',
						'default'         => 0,
						'type'            => 'switch',
						'update_callback' => $sidebars_update_callback,
					],
					'ec_sidebar_pos'    => [
						'label'           => esc_html__( 'Global Events Calendar Sidebar Position ', 'Avada' ),
						'description'     => esc_html__( 'Controls the position of sidebar 1 for all Events Calendar posts and archive pages. If sidebar 2 is selected, it will display on the opposite side.', 'Avada' ),
						'id'              => 'ec_sidebar_pos',
						'default'         => 'Right',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'Left'  => esc_html__( 'Left', 'Avada' ),
							'Right' => esc_html__( 'Right', 'Avada' ),
						],
						'update_callback' => $sidebars_update_callback,
					],
				],
			] : [],
			'bbpress_sidebars_section'                     => ( Avada::$is_updating || class_exists( 'bbPress' ) || class_exists( 'BuddyPress' ) ) ? [
				'label'       => esc_html__( 'bbPress/BuddyPress', 'Avada' ),
				'description' => '',
				'id'          => 'bbpress_sidebars_section',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'ppbress_sidebar'          => [
						'label'           => esc_html__( 'Global bbPress/BuddyPress Sidebar 1', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 1 that will display on all bbPress/BuddyPress pages.', 'Avada' ),
						'id'              => 'ppbress_sidebar',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'ppbress_sidebar_2'        => [
						'label'           => esc_html__( 'Global bbPress/BuddyPress Sidebar 2', 'Avada' ),
						'description'     => esc_html__( 'Select sidebar 2 that will display on all bbPress/BuddyPress pages. Sidebar 2 can only be used if sidebar 1 is selected.', 'Avada' ),
						'id'              => 'ppbress_sidebar_2',
						'default'         => 'None',
						'type'            => 'select',
						'choices'         => $sidebar_options,
						'update_callback' => $sidebars_update_callback,
					],
					'bbpress_global_sidebar'   => [
						'label'           => esc_html__( 'Force Global Sidebars For bbPress/BuddyPress', 'Avada' ),
						'description'     => esc_html__( 'Turn on if you want to use the same sidebars on all bbPress/BuddyPress pages. Forums index page, profile page and search page does not need this option checked to display the sidebars selected below.', 'Avada' ),
						'id'              => 'bbpress_global_sidebar',
						'default'         => '0',
						'type'            => 'switch',
						'update_callback' => $sidebars_update_callback,
					],
					'bbpress_sidebar_position' => [
						'label'           => esc_html__( 'Global bbPress/BuddyPress Sidebar Position', 'Avada' ),
						'description'     => esc_html__( 'Controls the position of sidebar 1 for all bbPress/BuddyPress pages. If sidebar 2 is selected, it will display on the opposite side.', 'Avada' ),
						'id'              => 'bbpress_sidebar_position',
						'default'         => 'Right',
						'type'            => 'radio-buttonset',
						'choices'         => [
							'Left'  => esc_html__( 'Left', 'Avada' ),
							'Right' => esc_html__( 'Right', 'Avada' ),
						],
						'update_callback' => $sidebars_update_callback,
					],
				],
			] : [],
		],
	];

	return $sections;

}

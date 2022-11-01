<?php
/**
 * Titlebar Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

/**
 * Page page title bar settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_pagetitlebar( $sections ) {

	$sections['pagetitlebar'] = [
		'label'    => esc_html__( 'Page Title Bar', 'Avada' ),
		'id'       => 'pagetitlebar',
		'alt_icon' => 'fusiona-page_title',
		'fields'   => [],
	];
	$page_title_bar_partial   = [
		'page_title_bar_contents_page_title_bar' => [
			'selector'              => '.avada-page-titlebar-wrapper',
			'container_inclusive'   => false,
			'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'page_titlebar_wrapper' ],
			'success_trigger_event' => 'fusion-ptb-refreshed',
		],
	];

	// Template override, add notice and hide rest.
	$override = function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_override( 'page_title_bar' ) : false;
	if ( $override ) {
		$sections['pagetitlebar']['fields']['pagetitlebar_info'] = [
			'id'          => 'pagetitlebar_info',
			'label'       => '',
			/* translators: The edit link. Text of link is the title. */
			'description' => '<div class="fusion-redux-important-notice">' . Fusion_Template_Builder()->get_override_text( $override, 'page_title_bar' ) . '</div>',
			'dependency'  => [],
			'type'        => 'custom',
		];
		$page_title_text_dependency                              = [];
		$page_title_bg_dependency                                = [];
		$retina_dependency                                       = [];
	}

	if ( ! $override ) {
		if ( ! function_exists( 'get_current_screen' ) ) {
			include_once ABSPATH . 'wp-admin/includes/screen.php';
		}
		$screen = get_current_screen();

		// Regular PTB TO.
		$page_title_option_name = 'page_title_bar';

		if ( get_the_id() === (int) get_option( 'page_for_posts' ) ) {

			// Blog page PTB.
			$page_title_option_name = 'blog_show_page_title_bar';
		} elseif ( is_object( $screen ) && 'edit' === $screen->parent_base && 'post' === $screen->post_type || ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() && is_singular( 'post' ) ) ) {

			// Blog archive/post PTB.
			$page_title_option_name = 'blog_page_title_bar';
		}

		$page_title_default = Avada()->settings->get_default_description( $page_title_option_name, '', 'select' );
		$page_title_option  = Avada()->settings->get( $page_title_option_name );

		// Dependency check that page title bar not hidden.
		$page_title_dependency = [
			[
				'field'      => $page_title_option_name,
				'value'      => 'no',
				'comparison' => '!=',
			],
		];
		if ( 'hide' === $page_title_option ) {
			$page_title_dependency[] = [
				'field'      => $page_title_option_name,
				'value'      => 'default',
				'comparison' => '!=',
			];
		}

		$page_title_text_dependency   = $page_title_dependency;
		$page_title_text_dependency[] = [
			'field'      => 'page_title_bar_text',
			'value'      => 'no',
			'comparison' => '!=',
		];
		if ( 0 == Avada()->settings->get( 'page_title_bar_text' ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$page_title_text_dependency[] = [
				'field'      => 'page_title_bar_text',
				'value'      => 'default',
				'comparison' => '!=',
			];
		}

		// Dependency check that background is used.
		$page_title_bg_dependency   = $page_title_dependency;
		$page_title_bg_dependency[] = [
			'field'      => $page_title_option_name,
			'value'      => 'yes_without_bar',
			'comparison' => '!=',
		];
		if ( 'content_only' === $page_title_option ) {
			$page_title_bg_dependency[] = [
				'field'      => $page_title_option_name,
				'value'      => 'default',
				'comparison' => '!=',
			];
		}

		$ptb_bg_color = Fusion_Color::new_color(
			[
				'color'    => Avada()->settings->get( 'page_title_bg_color' ),
				'fallback' => '#F6F6F6',
			]
		);

		$ptb_border_color = Fusion_Color::new_color(
			[
				'color'    => Avada()->settings->get( 'page_title_border_color' ),
				'fallback' => '#d2d3d4',
			]
		);

		// Add check that regular background image has been added.
		$retina_dependency   = $page_title_bg_dependency;
		$retina_dependency[] = [
			'field'      => 'page_title_bg',
			'value'      => '',
			'comparison' => '!=',
		];

		$sections['pagetitlebar']['fields'][ $page_title_option_name ] = [
			'id'          => $page_title_option_name,
			'label'       => esc_attr__( 'Page Title Bar', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose to show or hide the page title bar. %s', 'Avada' ), $page_title_default ),
			'dependency'  => [],
			'type'        => 'select',
			'choices'     => [
				'default'         => esc_attr__( 'Default', 'Avada' ),
				'yes'             => esc_attr__( 'Show Bar and Content', 'Avada' ),
				'yes_without_bar' => esc_attr__( 'Show Content Only', 'Avada' ),
				'no'              => esc_attr__( 'Hide', 'Avada' ),
			],
			'default'     => 'default',
		];

		$sections['pagetitlebar']['fields']['page_title_bar_bs']    = [
			'id'          => 'page_title_bar_bs',
			'label'       => esc_html__( 'Breadcrumbs/Search Bar', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose to display the breadcrumbs, search bar or none. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bar_bs', '', 'select' ) ),
			'dependency'  => $page_title_dependency,
			'type'        => 'radio-buttonset',
			'default'     => 'default',
			'choices'     => [
				'default'     => esc_attr__( 'Default', 'Avada' ),
				'breadcrumbs' => esc_attr__( 'Breadcrumbs', 'Avada' ),
				'searchbar'   => esc_attr__( 'Search Bar', 'Avada' ),
				'none'        => esc_attr__( 'None', 'Avada' ),
			],
		];
		$sections['pagetitlebar']['fields']['page_title_bar_text']  = [
			'id'          => 'page_title_bar_text',
			'label'       => esc_html__( 'Page Title Bar Headings', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose to show or hide the page title bar headings. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bar_text', '', 'showhide' ) ),
			'dependency'  => $page_title_dependency,
			'type'        => 'radio-buttonset',
			'map'         => 'showhide',
			'default'     => 'default',
			'choices'     => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Show', 'Avada' ),
				'no'      => esc_attr__( 'Hide', 'Avada' ),
			],
		];
		$sections['pagetitlebar']['fields']['page_title_alignment'] = [
			'id'          => 'page_title_alignment',
			'label'       => esc_html__( 'Page Title Bar Text Alignment', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_attr__( 'Choose the title and subhead text alignment. Breadcrumbs / search field will be on opposite side for left / right alignment and below the title for center alignment. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_alignment', '', 'select' ) ),
			'dependency'  => $page_title_dependency,
			'type'        => 'radio-buttonset',
			'default'     => 'default',
			'choices'     => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'left'    => esc_attr__( 'Left', 'Avada' ),
				'center'  => esc_attr__( 'Center', 'Avada' ),
				'right'   => esc_attr__( 'Right', 'Avada' ),
			],
		];
	}

	$sections['pagetitlebar']['fields']['page_title_custom_text'] = [
		'id'              => 'page_title_custom_text',
		'label'           => esc_attr__( 'Page Title Bar Heading Custom Text', 'Avada' ),
		'description'     => esc_attr__( 'Insert custom text for the page title bar main heading.', 'Avada' ),
		'dependency'      => $page_title_text_dependency,
		'type'            => 'textarea',
		'partial_refresh' => $page_title_bar_partial,
	];

	if ( ! $override ) {
		$sections['pagetitlebar']['fields']['page_title_font_size']   = [
			'id'          => 'page_title_font_size',
			'label'       => esc_attr__( 'Page Title Bar Heading Font Size', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'In pixels. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_font_size' ) ),
			'dependency'  => $page_title_text_dependency,
			'type'        => 'text',
		];
		$sections['pagetitlebar']['fields']['page_title_color']       = [
			'id'          => 'page_title_color',
			'label'       => esc_attr__( 'Page Title Bar Heading Font Color', 'Avada' ),
			'default'     => Avada()->settings->get( 'page_title_color' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Controls the text color of the page title bar main heading. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_color' ) ),
			'dependency'  => $page_title_text_dependency,
			'type'        => 'color-alpha',
		];
		$sections['pagetitlebar']['fields']['page_title_line_height'] = [
			'id'          => 'page_title_line_height',
			'label'       => esc_attr__( 'Page Title Bar Line Height', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Valid CSS unit. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_line_height' ) ),
			'dependency'  => $page_title_text_dependency,
			'type'        => 'text',
		];
	}
	$sections['pagetitlebar']['fields']['page_title_custom_subheader'] = [
		'id'              => 'page_title_custom_subheader',
		'label'           => esc_attr__( 'Page Title Bar Subheading Custom Text', 'Avada' ),
		'description'     => esc_html__( 'Insert custom text for the page title bar subheading.', 'Avada' ),
		'dependency'      => $page_title_text_dependency,
		'type'            => 'textarea',
		'partial_refresh' => $page_title_bar_partial,
	];
	if ( ! $override ) {
		$sections['pagetitlebar']['fields']['page_title_subheader_font_size'] = [
			'id'          => 'page_title_subheader_font_size',
			'label'       => esc_html__( 'Page Title Bar Subheading Font Size', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_attr__( 'In pixels. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_subheader_font_size' ) ),
			'dependency'  => $page_title_text_dependency,
			'type'        => 'text',
		];
		$sections['pagetitlebar']['fields']['page_title_subheader_color']     = [
			'id'          => 'page_title_subheader_color',
			'label'       => esc_attr__( 'Page Title Bar Subheading Font Color', 'Avada' ),
			'default'     => Avada()->settings->get( 'page_title_subheader_color' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Controls the text color of the page title bar subheading. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_subheader_color' ) ),
			'dependency'  => $page_title_text_dependency,
			'type'        => 'color-alpha',
		];
		$sections['pagetitlebar']['fields']['page_title_100_width']           = [
			'id'          => 'page_title_100_width',
			'label'       => esc_html__( 'Page Title Bar 100% Width', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose "Yes" to have the page title bar area display at 100&#37; width according to the viewport size. Select "No" to follow site width. Only works with wide layout mode. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_100_width', '', 'yesno' ) ),
			'dependency'  => $page_title_dependency,
			'type'        => 'radio-buttonset',
			'map'         => 'yesno',
			'default'     => 'default',
			'choices'     => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'yes'     => esc_attr__( 'Yes', 'Avada' ),
				'no'      => esc_attr__( 'No', 'Avada' ),
			],
		];
		$sections['pagetitlebar']['fields']['page_title_height']              = [
			'id'          => 'page_title_height',
			'label'       => esc_attr__( 'Page Title Bar Height', 'Avada' ),
			/* translators: percetange sign and additional description (defaults). */
			'description' => sprintf( esc_attr__( 'Controls the height of the page title bar on desktop. Enter value including any valid CSS unit besides %1$s which does not work for page title bar, ex: 87px. %2$s', 'Avada' ), '%', Avada()->settings->get_default_description( 'page_title_height' ) ),
			'dependency'  => $page_title_dependency,
			'type'        => 'text',
		];
		$sections['pagetitlebar']['fields']['page_title_mobile_height']       = [
			'id'          => 'page_title_mobile_height',
			'label'       => esc_attr__( 'Page Title Bar Mobile Height', 'Avada' ),
			/* translators: percetange sign and additional description (defaults). */
			'description' => sprintf( esc_attr__( 'Controls the height of the page title bar on mobile. Enter value including any valid CSS unit besides %1$s which does not work for page title bar, ex: 70px. %2$s', 'Avada' ), '%', Avada()->settings->get_default_description( 'page_title_mobile_height' ) ),
			'dependency'  => $page_title_dependency,
			'type'        => 'text',
		];
		$sections['pagetitlebar']['fields']['page_title_bg_color']            = [
			'id'          => 'page_title_bg_color',
			'label'       => esc_attr__( 'Page Title Bar Background Color', 'Avada' ),
			'default'     => $ptb_bg_color->color,
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Controls the background color of the page title bar. Hex code, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bg_color' ) ),
			'dependency'  => $page_title_bg_dependency,
			'type'        => 'color-alpha',
		];
		$sections['pagetitlebar']['fields']['page_title_border_color']        = [
			'id'          => 'page_title_border_color',
			'label'       => esc_attr__( 'Page Title Bar Borders Color', 'Avada' ),
			'default'     => $ptb_border_color->color,
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Controls the border color of the page title bar. Hex code, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_border_color' ) ),
			'dependency'  => $page_title_bg_dependency,
			'type'        => 'color-alpha',
		];
	}

	$sections['pagetitlebar']['fields']['page_title_bg']        = [
		'id'          => 'page_title_bg',
		'label'       => esc_attr__( 'Page Title Bar Background', 'Avada' ),
		/* translators: Additional description (defaults). */
		'description' => sprintf( esc_html__( 'Select an image to use for the page title bar background. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bg', 'url' ) ),
		'dependency'  => $page_title_bg_dependency,
		'type'        => 'media',
	];
	$sections['pagetitlebar']['fields']['page_title_bg_retina'] = [
		'id'          => 'page_title_bg_retina',
		'label'       => esc_attr__( 'Page Title Bar Background Retina', 'Avada' ),
		/* translators: Additional description (defaults). */
		'description' => sprintf( esc_html__( 'Select an image to use for retina devices. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bg_retina', 'url' ) ),
		'dependency'  => $retina_dependency,
		'type'        => 'media',
	];

	if ( ! $override ) {
		$sections['pagetitlebar']['fields']['page_title_bg_full']     = [
			'id'          => 'page_title_bg_full',
			'label'       => esc_html__( '100% Background Image', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose to have the background image display at 100&#37;. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bg_full', '', 'yesno' ) ),
			'dependency'  => $retina_dependency,
			'type'        => 'radio-buttonset',
			'map'         => 'yesno',
			'default'     => 'default',
			'choices'     => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'no'      => esc_attr__( 'No', 'Avada' ),
				'yes'     => esc_attr__( 'Yes', 'Avada' ),
			],
		];
		$sections['pagetitlebar']['fields']['page_title_bg_parallax'] = [
			'id'          => 'page_title_bg_parallax',
			'label'       => esc_html__( 'Parallax Background Image', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Choose a parallax scrolling effect for the background image. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bg_parallax', '', 'yesno' ) ),
			'dependency'  => $retina_dependency,
			'type'        => 'radio-buttonset',
			'map'         => 'yesno',
			'default'     => 'default',
			'choices'     => [
				'default' => esc_attr__( 'Default', 'Avada' ),
				'no'      => esc_attr__( 'No', 'Avada' ),
				'yes'     => esc_attr__( 'Yes', 'Avada' ),
			],
		];
	}
	return $sections;
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

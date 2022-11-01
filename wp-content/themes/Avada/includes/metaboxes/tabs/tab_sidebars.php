<?php
/**
 * Sidebars Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

/**
 * Sidebars page settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_sidebars( $sections ) {
	global $wp_registered_sidebars, $post;
	$override  = function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_override( 'content' ) : false;
	$post_type = get_post_type();

	$sections['sidebars'] = [
		'label'    => esc_html__( 'Sidebars', 'Avada' ),
		'id'       => 'sidebars',
		'alt_icon' => 'fusiona-sidebar',
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
		$sections['sidebars']['fields']['sidebar_info'] = [
			'id'          => 'sidebar_info',
			'label'       => '',
			/* translators: The edit link. Text of link is the title. */
			'description' => '<div class="fusion-redux-important-notice">' . Fusion_Template_Builder()->get_override_text( $override ) . '</div>',
			'dependency'  => [],
			'type'        => 'custom',
		];

		return $sections;
	}

	$global_switch = false;
	switch ( $post_type ) {
		case 'page':
			$global_switch    = 'pages_global_sidebar';
			$sidebar_1_option = 'pages_sidebar';
			$sidebar_2_option = 'pages_sidebar_2';
			$sidebar_position = 'default_sidebar_pos';
			$bg_color_option  = 'sidebar_bg_color';
			break;

		case 'avada_portfolio':
			$global_switch    = 'portfolio_global_sidebar';
			$sidebar_1_option = 'portfolio_sidebar';
			$sidebar_2_option = 'portfolio_sidebar_2';
			$sidebar_position = 'portfolio_sidebar_position';
			$bg_color_option  = 'sidebar_bg_color';
			break;

		case 'product':
			$global_switch    = 'woo_global_sidebar';
			$sidebar_1_option = 'woo_sidebar';
			$sidebar_2_option = 'woo_sidebar_2';
			$sidebar_position = 'woo_sidebar_position';
			$bg_color_option  = 'sidebar_bg_color';
			break;

		case 'tribe_events':
			$global_switch    = 'ec_global_sidebar';
			$sidebar_1_option = 'ec_sidebar';
			$sidebar_2_option = 'ec_sidebar_2';
			$sidebar_position = 'ec_sidebar_pos';
			$bg_color_option  = 'ec_sidebar_bg_color';
			break;

		case 'forum':
		case 'topic':
		case 'reply':
			$global_switch    = 'bbpress_global_sidebar';
			$sidebar_1_option = 'ppbress_sidebar';
			$sidebar_2_option = 'ppbress_sidebar_2';
			$sidebar_position = 'bbpress_sidebar_position';
			$bg_color_option  = 'sidebar_bg_color';
			break;

		// TODO: check if we want different names and TOs here.  If we don't have TOs then option defaults need changed.
		case 'fusion_tb_section':
			$global_switch    = 'template_global_sidebar';
			$sidebar_1_option = 'template_sidebar';
			$sidebar_2_option = 'template_sidebar_2';
			$sidebar_position = 'template_sidebar_position';
			$bg_color_option  = 'sidebar_bg_color';
			break;

		default:
			$global_switch    = 'posts_global_sidebar';
			$sidebar_1_option = 'posts_sidebar';
			$sidebar_2_option = 'posts_sidebar_2';
			$sidebar_position = 'blog_sidebar_position';
			$bg_color_option  = 'sidebar_bg_color';
			break;
	}

	// Check if we're on the Live Builder and the WooCommerce Shop page.
	if ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() && fusion_is_shop( $post->ID ) ) {
		$global_switch    = 'pages_global_sidebar';
		$sidebar_1_option = 'pages_sidebar';
		$sidebar_2_option = 'pages_sidebar_2';
		$sidebar_position = 'default_sidebar_pos';
		$bg_color_option  = 'sidebar_bg_color';
	}

	$sidebars_update_callback = [
		[
			'where'     => 'postMeta',
			'condition' => '_wp_page_template',
			'operator'  => '!=',
			'value'     => '100-width.php',
		],
	];

	if ( $global_switch && 1 !== intval( Avada()->settings->get( $global_switch ) ) ) {
		$sidebar_choices = [
			'' => esc_html__( 'No Sidebar', 'Avada' ),
		];

		$sidebars = $wp_registered_sidebars;

		if ( is_array( $sidebars ) && ! empty( $sidebars ) ) {
			foreach ( $sidebars as $sidebar ) {
				$sidebar_choices[ $sidebar['name'] ] = esc_html( $sidebar['name'] );
			}
		}

		$sidebar_choices_2 = $sidebar_choices;

		// If we are editing a template, then don't have global.
		$default_sidebar = '';
		if ( 'fusion_tb_section' !== $post_type ) {

			$default_sidebar = 'default_sidebar';

			// Format label for default value (1st sidebar).
			$sidebar_choices['default_sidebar'] = sprintf(
				/* translators: The sidebar name. */
				esc_html__( 'Default (%s)', 'Avada' ),
				esc_html( Avada()->settings->get( $sidebar_1_option ) )
			);

			// Format label for default value (2nd sidebar).
			$sidebar_choices_2['default_sidebar'] = sprintf(
				/* translators: The sidebar name. */
				esc_html__( 'Default (%s)', 'Avada' ),
				esc_html( Avada()->settings->get( $sidebar_2_option ) )
			);
		}

		// Add important note in the frontend builder.
		if ( isset( $_GET['builder'] ) && isset( $_GET['builder_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$sections['sidebars']['fields']['sidebars_important_note'] = [
				'id'          => 'sidebars_important_note',
				'label'       => '',
				'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Sidebars cannot be assigned to this page because it is currently set to use the 100% width page template. To change this, go to the Settings tab and change the page template to default.', 'Avada' ) . '</div>',
				'type'        => 'custom',
				'dependency'  => [
					[
						'field'      => '_wp_page_template',
						'comparison' => '==',
						'value'      => '100-width.php',
					],
				],
			];
		}

		$sections['sidebars']['fields'][ $sidebar_1_option ] = [
			'id'              => $sidebar_1_option,
			'label'           => esc_html__( 'Select Sidebar 1', 'Avada' ),
			'description'     => esc_html__( 'Select sidebar 1 that will display on this page. Choose "No Sidebar" for full width.', 'Avada' ),
			'dependency'      => [],
			'type'            => 'select',
			'choices'         => $sidebar_choices,
			'default'         => $default_sidebar,
			'update_callback' => $sidebars_update_callback,
			'dependency'      => [
				[
					'field'      => '_wp_page_template',
					'comparison' => '!=',
					'value'      => '100-width.php',
				],
			],
		];

		$sections['sidebars']['fields'][ $sidebar_2_option ] = [
			'id'              => $sidebar_2_option,
			'label'           => esc_html__( 'Select Sidebar 2', 'Avada' ),
			'description'     => esc_html__( 'Select sidebar 2 that will display on this page. Choose "No Sidebar" for full width.', 'Avada' ),
			'dependency'      => [
				[
					'field'      => $sidebar_1_option,
					'value'      => '',
					'comparison' => '!=',
				],
			],
			'type'            => 'select',
			'choices'         => $sidebar_choices_2,
			'default'         => $default_sidebar,
			'update_callback' => $sidebars_update_callback,
			'dependency'      => [
				[
					'field'      => '_wp_page_template',
					'comparison' => '!=',
					'value'      => '100-width.php',
				],
			],
		];

		if ( 'fusion_tb_section' !== $post_type ) {
			$sections['sidebars']['fields'][ $sidebar_position ] = [
				'id'              => $sidebar_position,
				'label'           => esc_attr__( 'Sidebar 1 Position', 'Avada' ),
				/* translators: Additional description (defaults). */
				'description'     => sprintf( esc_html__( 'Select the sidebar 1 position. If sidebar 2 is selected, it will display on the opposite side. %s', 'Avada' ), ( $global_switch ) ? Avada()->settings->get_default_description( $sidebar_position, '', 'select' ) : '' ),
				'dependency'      => [
					[
						'field'      => '_wp_page_template',
						'comparison' => '!=',
						'value'      => '100-width.php',
					],
					[
						'field'      => $sidebar_1_option,
						'value'      => '',
						'comparison' => '!=',
					],
				],
				'type'            => 'radio-buttonset',
				'choices'         => [
					'default' => esc_attr__( 'Default', 'Avada' ),
					'left'    => esc_attr__( 'Left', 'Avada' ),
					'right'   => esc_attr__( 'Right', 'Avada' ),
				],
				'default'         => 'default',
				'update_callback' => $sidebars_update_callback,
			];
		} else {
			$sections['sidebars']['fields'][ $sidebar_position ] = [
				'id'              => $sidebar_position,
				'label'           => esc_attr__( 'Sidebar 1 Position', 'Avada' ),
				/* translators: Additional description (defaults). */
				'description'     => esc_html__( 'Select the sidebar 1 position. If sidebar 2 is selected, it will display on the opposite side.', 'Avada' ),
				'dependency'      => [
					[
						'field'      => '_wp_page_template',
						'comparison' => '!=',
						'value'      => '100-width.php',
					],
					[
						'field'      => $sidebar_1_option,
						'value'      => '',
						'comparison' => '!=',
					],
				],
				'type'            => 'radio-buttonset',
				'choices'         => [
					'left'  => esc_attr__( 'Left', 'Avada' ),
					'right' => esc_attr__( 'Right', 'Avada' ),
				],
				'default'         => 'right',
				'update_callback' => $sidebars_update_callback,
			];
		}
		$sections['sidebars']['fields']['responsive_sidebar_order'] = [
			'id'              => 'responsive_sidebar_order',
			'label'           => esc_attr__( 'Responsive Sidebar Order', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description'     => sprintf( esc_html__( 'Choose the order of sidebars and main content area on mobile layouts through drag & drop sorting. %s', 'Avada' ), Avada()->settings->get_default_description( 'responsive_sidebar_order', '', 'sortable', 'responsive_sidebar_order' ) ),
			'type'            => 'sortable',
			'default'         => Avada()->settings->get( 'responsive_sidebar_order' ),
			'choices'         => [
				'content'   => esc_html__( 'Content', 'Avada' ),
				'sidebar'   => esc_html__( 'Sidebar 1', 'Avada' ),
				'sidebar-2' => esc_html__( 'Sidebar 2', 'Avada' ),
			],
			'update_callback' => $sidebars_update_callback,
			'dependency'      => [
				[
					'field'      => '_wp_page_template',
					'comparison' => '!=',
					'value'      => '100-width.php',
				],
			],
		];

		$sections['sidebars']['fields']['sidebar_sticky'] = [
			'id'              => 'sidebar_sticky',
			'label'           => esc_attr__( 'Sticky Sidebars', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description'     => sprintf( esc_html__( 'Select the sidebar(s) that should remain sticky when scrolling the page. If the sidebar content is taller than the screen, it acts like a normal sidebar until the bottom of the sidebar is within the viewport, which will then remain fixed in place as you scroll down. %s', 'Avada' ), Avada()->settings->get_default_description( 'sidebar_sticky', '', 'select' ) ),
			'dependency'      => [
				[
					'field'      => '_wp_page_template',
					'comparison' => '!=',
					'value'      => '100-width.php',
				],
				[
					'field'      => $sidebar_1_option,
					'value'      => '',
					'comparison' => '!=',
				],
			],
			'type'            => 'select',
			'choices'         => [
				'default'     => esc_attr__( 'Default', 'Avada' ),
				'none'        => esc_attr__( 'None', 'Avada' ),
				'sidebar_one' => esc_attr__( 'Sidebar 1', 'Avada' ),
				'sidebar_two' => esc_attr__( 'Sidebar 2', 'Avada' ),
				'both'        => esc_attr__( 'Both', 'Avada' ),
			],
			'default'         => 'default',
			'update_callback' => $sidebars_update_callback,
		];

		$sidebar_bg_color = Fusion_Color::new_color(
			[
				'color'    => Avada()->settings->get( $bg_color_option ),
				'fallback' => 'rgba(255,255,255,0)',
			]
		)->color;

		$sections['sidebars']['fields'][ $bg_color_option ] = [
			'id'              => $bg_color_option,
			'label'           => esc_attr__( 'Sidebar Background Color', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description'     => sprintf( esc_html__( 'Controls the background color of the sidebar. Hex code, ex: #000. %s', 'Avada' ), ( 'tribe_events' === $post_type ) ? Avada()->settings->get_default_description( 'ec_sidebar_bg_color' ) : Avada()->settings->get_default_description( 'sidebar_bg_color' ) ),
			'dependency'      => [
				[
					'field'      => '_wp_page_template',
					'comparison' => '!=',
					'value'      => '100-width.php',
				],
				[
					'field'      => $sidebar_1_option,
					'value'      => '',
					'comparison' => '!=',
				],
			],
			'type'            => 'color-alpha',
			'default'         => $sidebar_bg_color,
			'update_callback' => $sidebars_update_callback,
		];

	} else {
		$message = __( '<strong>IMPORTANT NOTE:</strong> The Activate Global Sidebars option is turned on which removes the ability to choose individual sidebars. Turn off that option to assign unique sidebars.', 'Avada' );
		if ( $global_switch ) {
			$message = sprintf(
				/* translators: Additional description (defaults). */
				__( '<strong>IMPORTANT NOTE:</strong> The <a href="%s" target="_blank">Activate Global Sidebars</a> option is turned on which removes the ability to choose individual sidebars. Turn off that option to assign unique sidebars.', 'Avada' ),
				Avada()->settings->get_setting_link( $global_switch )
			);
		}

		$sections['sidebars']['fields']['sidebar_global_to_enabled'] = [
			'id'              => 'sidebar_global_to_enabled',
			'label'           => '',
			'description'     => '<div class="fusion-redux-important-notice">' . $message . '</div>',
			'dependency'      => [],
			'type'            => 'custom',
			'update_callback' => $sidebars_update_callback,
		];
	}

	return $sections;
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

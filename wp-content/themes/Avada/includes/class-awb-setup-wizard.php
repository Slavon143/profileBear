<?php
/**
 * Needed functionality for the setup wizard.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      7.5
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 */

/**
 * Setup wizard handling.
 *
 * @since 7.5
 */
class AWB_Setup_Wizard {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 7.5
	 * @var object
	 */
	private static $instance;

	/**
	 * Color data
	 *
	 * @access public
	 * @since 7.7
	 * @var mixed
	 */
	public $color_data;

	/**
	 * Typo data
	 *
	 * @access public
	 * @since 7.7
	 * @var mixed
	 */
	public $typo_data;

	/**
	 * Logo type or image.
	 *
	 * @access public
	 * @since 7.7
	 * @var mixed
	 */
	public $logo;

	/**
	 * Dark mode or not.
	 *
	 * @access public
	 * @since 7.7
	 * @var bool
	 */
	public $dark;

	/**
	 * Site Title.
	 *
	 * @access public
	 * @since 7.7
	 * @var string
	 */
	public $site_title;

	/**
	 * Import Page Title.
	 *
	 * @access public
	 * @since 7.7
	 * @var string
	 */
	public $page_title = '';

	/**
	 * ID of main menu that has been created.
	 *
	 * @access public
	 * @since 7.7
	 * @var int
	 */
	public $menu_id;

	/**
	 * Main menu name.
	 *
	 * @access public
	 * @since 7.7
	 * @var int
	 */
	public $menu_name = 'Avada Setup Menu';

	/**
	 * Second Main menu name, for headers with splitted menus only.
	 *
	 * @access public
	 * @since 7.7
	 * @var int
	 */
	public $menu_name_2 = 'Avada Second Setup Menu';

	/**
	 * Imported menus count, for headers with splitted menus only.
	 *
	 * @access public
	 * @since 7.7
	 * @var int
	 */
	public $imported_menus = 0;

	/**
	 * Imported pages should added to menu items.
	 *
	 * @access public
	 * @since 7.7
	 * @var int
	 */
	public $menu_items;

	/**
	 * Slugs of menus to ignore from import.
	 *
	 * @access public
	 * @since 7.7
	 * @var arrays
	 */
	public $menu_ignore = [];

	/**
	 * Color flipping replacements
	 *
	 * @access public
	 * @since 7.7
	 * @var mixed
	 */
	public $color_flip = [
		'--awb-color1'                    => '--awo-color1',
		'--awb-color2'                    => '--awo-color2',
		'--awb-color3'                    => '--awo-color3',
		'--awb-color4'                    => '--awo-color4',
		'--awb-color5'                    => '--awo-color5',
		'--awb-color6'                    => '--awo-color6',
		'--awb-color7'                    => '--awo-color7',
		'--awb-color8'                    => '--awo-color8',
		'--awo-color1'                    => '--awb-color8',
		'--awo-color2'                    => '--awb-color7',
		'--awo-color3'                    => '--awb-color6',
		'--awo-color4'                    => '--awb-color5',
		'--awo-color5'                    => '--awb-color4',
		'--awo-color6'                    => '--awb-color3',
		'--awo-color7'                    => '--awb-color2',
		'--awo-color8'                    => '--awb-color1',
		'-l) -'                           => '-x) -',
		'-l) +'                           => '-l) -',
		'-x) -'                           => '-l) +',
		'background_blend_mode="lighten'  => 'xackground_blend_mode="lighten',
		'background_blend_mode="multiply' => 'background_blend_mode="lighten',
		'xackground_blend_mode="lighten'  => 'background_blend_mode="multiply',
	];

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 7.4
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new AWB_Setup_Wizard();
		}
		return self::$instance;
	}

	/**
	 * The class constructor
	 *
	 * @access public
	 */
	public function __construct() {
		// Fetches and saved color scheme globals.
		add_action( 'wp_ajax_awb_save_globals', [ $this, 'ajax_save_globals' ] );

		// Creates layout sections and assigns.
		add_action( 'wp_ajax_awb_save_setup_layouts', [ $this, 'ajax_save_layouts' ] );

		// Imports setup pages.
		add_action( 'wp_ajax_awb_import_setup_pages', [ $this, 'ajax_import_pages' ] );

		// Finish scratch import.
		add_action( 'wp_ajax_awb_finalise_scratch_setup', [ $this, 'ajax_finalise_scratch_setup' ] );

		if ( ! fusion_doing_ajax() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'add_scripts' ] );
		}

		if ( isset( $_GET['page'] ) && 'avada-setup' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_filter( 'awb_po_get_value', [ $this, 'global_value' ], 10, 2 );
			add_filter( 'awb_po_get_option_name', [ $this, 'global_name' ], 10, 2 );

			// Allow SVG upload in the wizard.
			add_filter( 'upload_mimes', [ Avada()->images, 'allow_svg' ] );
			add_filter( 'wp_check_filetype_and_ext', [ Avada()->images, 'correct_svg_filetype' ], 10, 5 );
		}
	}

	/**
	 * Process the content, replacing logo for example.
	 *
	 * @access public
	 * @since 7.4
	 * @param mixed $post_data Post data.
	 */
	public function process_content( $post_data ) {

		// Find logo image and replace with text logo or image selection by user.
		$pattern                   = get_shortcode_regex( [ 'fusion_imageframe' ] );
		$post_data['post_content'] = preg_replace_callback( "/$pattern/", [ $this, 'process_images' ], $post_data['post_content'] );

		// Find contact button and use the contact page link.
		$pattern                   = get_shortcode_regex( [ 'fusion_button' ] );
		$post_data['post_content'] = preg_replace_callback( "/$pattern/", [ $this, 'process_buttons' ], $post_data['post_content'] );

		// Find main menu and replace menu ID with created one if we have one.
		$this->menu_ignore = [];

		$menu_exists = wp_get_nav_menu_object( $this->menu_name );
		if ( $menu_exists ) {
			$pattern                   = get_shortcode_regex( [ 'fusion_menu' ] );
			$post_data['post_content'] = preg_replace_callback( "/$pattern/", [ $this, 'process_menus' ], $post_data['post_content'] );
		}

		// If we have replaced menus in the content, we don't need to import them, so remove from avada_media.
		if ( ! empty( $this->menu_ignore ) && isset( $post_data['avada_media']['menus'] ) ) {
			foreach ( $this->menu_ignore as $menu_slug ) {
				if ( isset( $post_data['avada_media']['menus'][ $menu_slug ] ) ) {
					unset( $post_data['avada_media']['menus'][ $menu_slug ] );
				}
			}

			if ( empty( $post_data['avada_media']['menus'] ) ) {
				unset( $post_data['avada_media']['menus'] );
			}
		}
		return $post_data;
	}

	/**
	 * Look for the main menu and replace with created menu.
	 *
	 * @access public
	 * @since 7.7
	 * @param mixed $m The menu.
	 */
	public function process_menus( $m ) {
		global $shortcode_tags;

		$tag  = $m[2];
		$attr = shortcode_parse_atts( $m[3] );

		// Don't import if menu is flay-out or split.
		if ( isset( $attr['class'] ) && ( 'avada-flyout-menu' === $attr['class'] || 'avada-split-menu' === $attr['class'] ) ) {
			$attr['class'] = 'avada-main-menu';
		}

		// Main menu worked out by CSS class.
		if ( ! isset( $attr['class'] ) || 'avada-main-menu' !== $attr['class'] ) {
			return $m[0];
		}

		$menu_name = $this->menu_name;

		// If first menu used, use the second one, for header with split menus.
		if ( 1 === $this->imported_menus ) {
			$menu_name = $this->menu_name_2;
		}
		$our_menu = wp_get_nav_menu_object( $menu_name );

		if ( ! is_object( $our_menu ) ) {
			return $m[0];
		}

		// No menu set, add it after css class.
		if ( ! isset( $attr['menu'] ) ) {
			return str_replace( 'class="avada-main-menu"', 'class="" menu="' . $our_menu->slug . '"', $m[0] );
		}

		// Flag it so the menu is not imported since we don't need it.
		$this->menu_ignore[] = $attr['menu'];

		// Increases the imported menus count.
		$this->imported_menus++;

		// Menu arg is set, replace.
		$m[0] = str_replace( 'class="avada-main-menu"', 'class=""', $m[0] );
		return str_replace( 'menu="' . $attr['menu'] . '"', 'menu="' . $our_menu->slug . '"', $m[0] );
	}

	/**
	 * Look for buttons and replace markup accordingly.
	 *
	 * @access public
	 * @since 7.7
	 * @param mixed $m The markup.
	 */
	public function process_buttons( $m ) {
		global $shortcode_tags;

		$tag  = $m[2];
		$attr = shortcode_parse_atts( $m[3] );

		// check for class.
		if ( ! isset( $attr['class'] ) || 'avada-contact-button' !== $attr['class'] ) {
			return $m[0];
		}
		$pages             = get_option( 'avada_setup_wizard_new_pages' );
		$contact_page_link = isset( $pages['contact'] ) ? get_page_link( $pages['contact'] ) : '';

		if ( ! $contact_page_link ) {
			return $m[0];
		}

		// No link set, add it after css class.
		if ( ! isset( $attr['link'] ) ) {
			return str_replace( 'class="avada-contact-button"', 'class="" link="' . $contact_page_link . '"', $m[0] );
		}

		// Link arg is set, replace.
		$m[0] = str_replace( 'class="avada-contact-button"', 'class=""', $m[0] );
		return str_replace( 'link="' . $attr['link'] . '"', 'link="' . $contact_page_link . '"', $m[0] );
	}

	/**
	 * Look for logo images and replace markup accordingly.
	 *
	 * @access public
	 * @since 7.7
	 * @param mixed $m The markup.
	 */
	public function process_images( $m ) {
		global $shortcode_tags;

		$tag  = $m[2];
		$attr = shortcode_parse_atts( $m[3] );

		// No dynamic param, its not a logo.
		if ( ! isset( $attr['dynamic_params'] ) ) {
			return $m[0];
		}

		$dynamic_params = json_decode( base64_decode( $attr['dynamic_params'] ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions

		if ( ! isset( $dynamic_params['link']['data'] ) || 'site_url' !== $dynamic_params['link']['data'] ) {
			return $m[0];
		}

		if ( 'text' === $this->logo_url ) {
			$color                = isset( $dynamic_params['link']['color'] ) && '' !== $dynamic_params['link']['color'] ? $dynamic_params['link']['color'] : 'var(--awb-color8)';
			$shortcode_attributes = [
				'font_size'        => '36px',
				'margin_top'       => isset( $attr['margin_top'] ) && '' !== $attr['margin_top'] ? $attr['margin_top'] : '0px',
				'margin_bottom'    => isset( $attr['margin_bottom'] ) && '' !== $attr['margin_bottom'] ? $attr['margin_bottom'] : '0px',
				'content_align'    => isset( $attr['align'] ) && '' !== $attr['align'] ? $attr['align'] : 'left',
				'title_link'       => 'on',
				'text_color'       => $color,
				'link_color'       => $color,
				'link_hover_color' => $color,
				'dynamic_params'   => base64_encode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
					wp_json_encode(
						[
							'element_content' => [ 'data' => 'site_title' ],
							'link_url'        => [ 'data' => 'site_url' ],
						]
					)
				),
			];

			$param_string = '';
			foreach ( $shortcode_attributes as $param => $value ) {
				$param_string .= ' ' . $param . '="' . $value . '"';
			}
			return '[fusion_title' . $param_string . ']' . $this->site_title . '[/fusion_title]';
		}

		$attr['image_id'] = $this->logo_id;
		$param_string     = '';
		foreach ( $attr as $param => $value ) {
			$param_string .= ' ' . $param . '="' . $value . '"';
		}
		return '[fusion_imageframe' . $param_string . ']' . $this->logo_url . '[/fusion_imageframe]';
	}

	/**
	 * Create a menu based on pages that have been created.
	 *
	 * @access public
	 * @since 7.4
	 * @param mixed $pages The pages.
	 * @param mixed $header_id The header id.
	 */
	public function create_menu( $pages = [], $header_id = '' ) {

		// Create the actual menu.
		$menu_exists = wp_get_nav_menu_object( $this->menu_name );
		if ( $menu_exists ) {
			wp_delete_nav_menu( $menu_exists );
		}
		$this->menu_id = wp_create_nav_menu( $this->menu_name );
		$special_menu  = $this->special_menu( $header_id, 'fusion_tb_section' );

		$top_item = '';
		if ( 'flyout' === $special_menu ) {
			$top_item_data = [
				'menu-item-title'  => 'Icon',
				'menu-item-url'    => '#',
				'menu-item-status' => 'publish',
				'menu-item-type'   => 'custom',
			];

			$top_item = wp_update_nav_menu_item( $this->menu_id, 0, $top_item_data );

			if ( $top_item && ! is_wp_error( $top_item ) ) {
				$this->update_fusion_menu_item_meta( $top_item, 'icon', 'fa-bars fas' );
				$this->update_fusion_menu_item_meta( $top_item, 'icononly', 'icononly' );
			}
		}

		if ( 'split' === $special_menu ) {
			$chunk_size     = ceil( count( $pages ) / 2 );
			$splitted_pages = array_chunk( $pages, $chunk_size, true );

			// Use first chunk to the current menu.
			$pages = isset( $splitted_pages[0] ) ? $splitted_pages[0] : $pages;

			// Create new menu for the other side with the second chunk.
			$menu_exists = wp_get_nav_menu_object( $this->menu_name_2 );
			if ( $menu_exists ) {
				wp_delete_nav_menu( $menu_exists );
			}
			$second_menu_id    = wp_create_nav_menu( $this->menu_name_2 );
			$second_menu_pages = isset( $splitted_pages[1] ) ? $splitted_pages[1] : [];
			foreach ( $second_menu_pages as $s_page_id => $s_page_title ) {
				$s_item_data = [
					'menu-item-title'     => esc_html( $s_page_title ),
					'menu-item-object'    => 'page',
					'menu-item-object-id' => (int) str_replace( 'menu-item-', '', $s_page_id ),
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				];

				wp_update_nav_menu_item( $second_menu_id, 0, $s_item_data );
			}
		}

		foreach ( $pages as $page_id => $page_title ) {
			$item_data = [
				'menu-item-title'     => esc_html( $page_title ),
				'menu-item-object'    => 'page',
				'menu-item-object-id' => (int) str_replace( 'menu-item-', '', $page_id ),
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			];

			if ( $top_item && ! is_wp_error( $top_item ) ) {
				$item_data['menu-item-parent-id'] = $top_item;
			}

			wp_update_nav_menu_item( $this->menu_id, 0, $item_data );
		}

	}

	/**
	 * Create layouts and save layout sections.
	 *
	 * @access public
	 * @since 7.7
	 */
	public function ajax_save_layouts() {

		// Early exit if AWB is not active.
		if ( ! class_exists( 'Fusion_Template_Builder' ) || ! class_exists( 'AWB_Studio_Import' ) ) {
			return wp_send_json_error();
		}

		$this->check_nonce();

		$has_setup_run_before = get_option( 'avada_setup_wizard_done', false );

		// If its dark mode flip the content variables.
		$this->dark   = isset( $_POST['dark_light'] ) && 'dark' === $_POST['dark_light']; // phpcs:ignore WordPress.Security.NonceVerification
		$replacements = $this->dark ? $this->color_flip : [];

		$this->logo_url   = isset( $_POST['logo_url'] ) && '' !== $_POST['logo_url'] ? sanitize_text_field( wp_unslash( $_POST['logo_url'] ) ) : 'text'; // phpcs:ignore WordPress.Security.NonceVerification
		$this->logo_id    = isset( $_POST['logo_id'] ) ? sanitize_text_field( wp_unslash( $_POST['logo_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		$this->site_title = isset( $_POST['site_title'] ) ? sanitize_text_field( wp_unslash( $_POST['site_title'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		// Temporary, need to work out how to do this.
		AWB_Studio_Import()->update_post = true;

		// Set import options.
		AWB_Studio_Import()->set_import_options(
			[
				'images' => 'dont-import-images',
			]
		);

		// Layout object.
		$layouts       = Fusion_Template_Builder();
		$global_layout = $layouts::get_default_layout();

		// Import header.
		$header_id      = isset( $_POST['header'] ) && ! empty( $_POST['header'] ) ? sanitize_text_field( wp_unslash( $_POST['header'] ) ) : '2157'; // phpcs:ignore WordPress.Security.NonceVerification
		$header_options = isset( $_POST['header_options'] ) && ! empty( $_POST['header_options'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['header_options'] ) ), true ) : []; // phpcs:ignore WordPress.Security.NonceVerification

			// Set import options.
			AWB_Studio_Import()->set_import_options(
				[
					'type'   => isset( $header_options['overwrite-type'] ) ? $header_options['overwrite-type'] : 'inherit',
					'images' => isset( $header_options['images'] ) ? $header_options['images'] : 'do-import-images',
					'invert' => isset( $header_options['invert'] ) ? $header_options['invert'] : 'dont-invert',
				]
			);

			$post_details                              = AWB_Studio_Import()->import_post(
				[
					'post_id'   => $header_id,
					'post_type' => 'fusion_tb_section',
				],
				[],
				true,
				$replacements
			);
			$global_layout['template_terms']['header'] = $post_details['post_id'];

		// Import footer.
		$footer_id      = isset( $_POST['footer'] ) && ! empty( $_POST['footer'] ) ? sanitize_text_field( wp_unslash( $_POST['footer'] ) ) : '2160'; // phpcs:ignore WordPress.Security.NonceVerification
		$footer_options = isset( $_POST['footer_options'] ) && ! empty( $_POST['footer_options'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['footer_options'] ) ), true ) : []; // phpcs:ignore WordPress.Security.NonceVerification

			// Set import options.
			AWB_Studio_Import()->set_import_options(
				[
					'type'   => isset( $footer_options['overwrite-type'] ) ? $footer_options['overwrite-type'] : 'inherit',
					'images' => isset( $footer_options['images'] ) ? $footer_options['images'] : 'do-import-images',
					'invert' => isset( $footer_options['invert'] ) ? $footer_options['invert'] : 'dont-invert',
				]
			);

			$post_details                              = AWB_Studio_Import()->import_post(
				[
					'post_id'   => $footer_id,
					'post_type' => 'fusion_tb_section',
				],
				[],
				true,
				$replacements
			);
			$global_layout['template_terms']['footer'] = $post_details['post_id'];

		// Import hidden layouts ( search & 404 ).
		$hidden_layouts = [
			'section_layouts' => [
				[
					'title'     => __( 'Search Page Content', 'Avada' ),
					'studio_id' => 2233,
				],
				[
					'title'     => __( '404 Page Content', 'Avada' ),
					'studio_id' => 2232,
				],
			],

			'layouts'         => [
				[
					'title'          => __( 'Search Page', 'Avada' ),
					'template_terms' => [
						'content' => 2233,
					],
					'conditions'     => [
						'search_results' => [
							'label'    => 'Search Results',
							'type'     => 'archives',
							'mode'     => 'include',
							'archives' => 'search_results',
						],
					],
				],
				[
					'title'          => __( '404 Page', 'Avada' ),
					'template_terms' => [
						'content' => 2232,
					],
					'conditions'     => [
						'not_found' => [
							'label'    => '404 Page',
							'type'     => 'singular',
							'mode'     => 'include',
							'singular' => 'not_found',
						],
					],
				],
			],
		];

		if ( isset( $hidden_layouts['section_layouts'] ) && is_array( $hidden_layouts['section_layouts'] ) ) {
			foreach ( $hidden_layouts['section_layouts'] as $hl_section ) {
				$hl_section_title     = isset( $hl_section['title'] ) ? $hl_section['title'] : '';
				$hl_section_studio_id = isset( $hl_section['studio_id'] ) ? $hl_section['studio_id'] : '';
				$hl_section_post_type = 'fusion_tb_section';

				AWB_Studio_Import()->import_post(
					[
						'post_id'   => $hl_section_studio_id,
						'post_type' => $hl_section_post_type,
					],
					[
						'post_title' => $hl_section_title,
					],
					true,
					$replacements
				);
			}
		}

		if ( $hidden_layouts['layouts'] && is_array( $hidden_layouts['layouts'] ) && ! $has_setup_run_before ) {
			foreach ( $hidden_layouts['layouts'] as $hl_layout ) {
				$hl_layout_title          = isset( $hl_layout['title'] ) ? $hl_layout['title'] : '';
				$hl_layout_template_terms = isset( $hl_layout['template_terms'] ) ? $hl_layout['template_terms'] : [];
				$hl_layout_conditions     = isset( $hl_layout['conditions'] ) ? $hl_layout['conditions'] : [];

				// get the curren template terms IDs.
				$hl_updated_template_terms = [];
				foreach ( $hl_layout_template_terms as $template_name => $template_id ) {
					$args           = [
						'post_type'  => 'fusion_tb_section',
						'meta_key'   => '_avada_studio_post', // phpcs:ignore WordPress.DB.SlowDBQuery
						'meta_value' => $template_id, // phpcs:ignore WordPress.DB.SlowDBQuery
					];
					$template_query = new WP_Query( $args );

					if ( $template_query->have_posts() ) {
						$hl_updated_template_terms[ $template_name ] = $template_query->posts[0]->ID;
					}
				}

				$this->add_layout( $hl_layout_title, $hl_updated_template_terms, $hl_layout_conditions );
			}
		}

		// Set content imported to true to avoid multiple imports.
		update_option( 'avada_setup_wizard_done', true );


		// Update global layout selection.
		$layouts::update_default_layout( $global_layout );

		wp_send_json_success();
	}

	/**
	 * Imports selected pages.
	 *
	 * @access public
	 * @since 7.7
	 */
	public function ajax_import_pages() {

		// Early exit if AWB is not active.
		if ( ! class_exists( 'Fusion_Template_Builder' ) || ! class_exists( 'AWB_Studio_Import' ) ) {
			return wp_send_json_error();
		}

		$this->check_nonce();

		// Temporary, need to work out how to do this.
		AWB_Studio_Import()->update_post = true;

		// Set import options.
		AWB_Studio_Import()->set_import_options(
			[
				'images' => 'dont-import-images',
			]
		);

		// For dummy content import.
		$pages                     = isset( $_POST['pages'] ) ? wp_unslash( $_POST['pages'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
		$this->dark                = isset( $_POST['dark_light'] ) && 'dark' === $_POST['dark_light']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
		$dummy_content             = isset( $_POST['dummy_content'] ) ? wp_unslash( $_POST['dummy_content'] ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
		$scheme                    = isset( $_POST['scheme'] ) ? wp_unslash( $_POST['scheme'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
		$header_id                 = isset( $_POST['header'] ) && ! empty( $_POST['header'] ) ? sanitize_text_field( wp_unslash( $_POST['header'] ) ) : '2157'; // phpcs:ignore WordPress.Security.NonceVerification
		$is_dummy_content_imported = get_option( 'avada_setup_wizard_dummy_content', false );
		$has_setup_run_before      = get_option( 'avada_setup_wizard_done', false );
		$this->menu_items          = [];
		$new_pages                 = [];

		// If its dark mode flip the content variables.
		$replacements = $this->dark ? $this->color_flip : [];

		if ( 'true' === $dummy_content ) {
			$this->svg_dynamic_placeholder( $scheme );

			if ( $is_dummy_content_imported ) {
				$this->update_dummy_content_image( $pages );
			} else {
				// Add dummy content.
				$this->import_dummy_content( $pages, $scheme );
			}
		}

		// For pages import.
		foreach ( $pages as $page ) {
			$context         = isset( $page['id'] ) ? $page['id'] : '';
			$title           = isset( $page['title'] ) ? $page['title'] : '';
			$studio_id       = isset( $page['studio_id'] ) ? $page['studio_id'] : '';
			$post_type       = isset( $page['post_type'] ) ? $page['post_type'] : 'page';
			$content_pages   = isset( $page['pages'] ) ? $page['pages'] : '';
			$section_layouts = isset( $page['section_layouts'] ) ? $page['section_layouts'] : '';
			$layouts         = isset( $page['layouts'] ) ? $page['layouts'] : '';
			$content         = isset( $page['content'] ) ? $page['content'] : '';

			if ( $studio_id ) {
				$new_page = AWB_Studio_Import()->import_post(
					[
						'post_id'   => $studio_id,
						'post_type' => 'fusion_template',
					],
					[
						'post_type'  => $post_type,
						'post_title' => $title,
					],
					true,
					$replacements
				);
			} else {
				if ( 'shop' === $context ) {
					$shop_page_id     = get_option( 'woocommerce_shop_page_id' );
					$cart_page_id     = get_option( 'woocommerce_cart_page_id' );
					$checkout_page_id = get_option( 'woocommerce_checkout_page_id' );

					if ( $shop_page_id ) {
						$new_page = [
							'post_id' => $shop_page_id,
						];
					} else {
						// Shop page.
						$new_page     = $this->insert_post( $title, $content, $post_type );
						$shop_page_id = (int) $new_page['post_id'];
						update_option( 'woocommerce_shop_page_id', $shop_page_id );
					}

					if ( ! $cart_page_id ) {
						$cart_page    = $this->insert_post( __( 'Cart', 'Avada' ), '[woocommerce_cart]', 'page' );
						$cart_page_id = (int) $cart_page['post_id'];
						update_option( 'woocommerce_cart_page_id', (int) $cart_page['post_id'] );
					}

					if ( ! $checkout_page_id ) {
						$cart_page        = $this->insert_post( __( 'Checkout', 'Avada' ), '[woocommerce_checkout]', 'page' );
						$checkout_page_id = (int) $cart_page['post_id'];
						update_option( 'woocommerce_checkout_page_id', (int) $cart_page['post_id'] );
					}

					$shop_studio_id     = isset( $page['shop'] ) ? $page['shop'] : '';
					$cart_studio_id     = isset( $page['cart'] ) ? $page['cart'] : '';
					$checkout_studio_id = isset( $page['checkout'] ) ? $page['checkout'] : '';

					// Import cart page.
					if ( $shop_studio_id ) {
						AWB_Studio_Import()->import_post(
							[
								'post_id'   => $shop_studio_id,
								'post_type' => 'fusion_template',
							],
							[
								'post_id'    => $shop_page_id,
								'post_type'  => 'page',
								'post_title' => $title,
							],
							true,
							$replacements
						);

						fusion_data()->post_meta( $shop_page_id )->set( 'show_wc_shop_loop', 'no' );
					}

					// Import cart page.
					if ( $cart_studio_id ) {
						AWB_Studio_Import()->import_post(
							[
								'post_id'   => $cart_studio_id,
								'post_type' => 'fusion_template',
							],
							[
								'post_id'    => $cart_page_id,
								'post_type'  => 'page',
								'post_title' => __( 'Cart', 'Avada' ),
							],
							true,
							$replacements
						);
					}

					// Import checkout page.
					if ( $checkout_studio_id ) {
						AWB_Studio_Import()->import_post(
							[
								'post_id'   => $checkout_studio_id,
								'post_type' => 'fusion_template',
							],
							[
								'post_id'    => $checkout_page_id,
								'post_type'  => 'page',
								'post_title' => __( 'Checkout', 'Avada' ),
							],
							true,
							$replacements
						);
					}
				} else {
					$new_page = $this->insert_post( $title, $content, $post_type );
				}
			}

			if ( 'homepage' === $context ) {
				update_option( 'page_on_front', (int) $new_page['post_id'] );
				update_option( 'show_on_front', 'page' );
			}

			$this->menu_items[ 'menu-item-' . (string) $new_page['post_id'] ] = $title;

			// If starter header don't add contact page to the menu.
			if ( '2157' === $header_id && 'contact' === $context ) {
				unset( $this->menu_items[ 'menu-item-' . (string) $new_page['post_id'] ] );
			}

			$new_pages[ $context ] = (int) $new_page['post_id'];

			// Insert Pages if exists.
			if ( $content_pages && is_array( $content_pages ) ) {
				foreach ( $content_pages as $page ) {
					$page_title     = isset( $page['title'] ) ? $page['title'] : '';
					$page_content   = isset( $page['content'] ) ? $page['content'] : '';
					$page_post_type = 'page';

					$new_page = $this->insert_post( $page_title, $page_content, $page_post_type );
				}
			}

			// Insert Section layouts if exists.
			if ( $section_layouts && is_array( $section_layouts ) ) {
				foreach ( $section_layouts as $section ) {
					$section_title     = isset( $section['title'] ) ? $section['title'] : '';
					$section_studio_id = isset( $section['studio_id'] ) ? $section['studio_id'] : '';
					$section_post_type = 'fusion_tb_section';

					AWB_Studio_Import()->import_post(
						[
							'post_id'   => $section_studio_id,
							'post_type' => $section_post_type,
						],
						[
							'post_title' => $section_title,
						],
						true,
						$replacements
					);
				}
			}

			// Insert Layouts if exists.
			if ( $layouts && is_array( $layouts ) && ! $has_setup_run_before ) {
				foreach ( $layouts as $layout ) {
					$layout_title          = isset( $layout['title'] ) ? $layout['title'] : '';
					$layout_template_terms = isset( $layout['template_terms'] ) ? $layout['template_terms'] : [];
					$layout_conditions     = isset( $layout['conditions'] ) ? $layout['conditions'] : [];

					// get the curren template terms IDs.
					$updated_template_terms = [];
					foreach ( $layout_template_terms as $template_name => $template_id ) {
						$args           = [
							'post_type'  => 'fusion_tb_section',
							'meta_key'   => '_avada_studio_post', // phpcs:ignore WordPress.DB.SlowDBQuery
							'meta_value' => $template_id, // phpcs:ignore WordPress.DB.SlowDBQuery
						];
						$template_query = new WP_Query( $args );

						if ( $template_query->have_posts() ) {
							$updated_template_terms[ $template_name ] = $template_query->posts[0]->ID;
						}
					}

					$this->add_layout( $layout_title, $updated_template_terms, $layout_conditions );
				}
			}
		}

		// Create the menu.
		if ( ! empty( $this->menu_items ) ) {
			$this->create_menu( $this->menu_items, $header_id );
		}

		// Temporary save new pages.
		update_option( 'avada_setup_wizard_new_pages', $new_pages );

		wp_send_json_success();
	}

	/**
	 * Sets site's title and description, clears cache, flushes permalinks and etc.
	 *
	 * @access public
	 * @since 7.7
	 */
	public function ajax_finalise_scratch_setup() {
		$this->check_nonce();

		$this->site_title = isset( $_POST['site_title'] ) ? sanitize_text_field( wp_unslash( $_POST['site_title'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$site_tagline     = isset( $_POST['site_tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['site_tagline'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		// Update site details.
		update_option( 'blogname', $this->site_title );
		update_option( 'blogdescription', $site_tagline );

		// Delete temporary options.
		delete_option( 'avada_setup_wizard_new_pages' );

		// Clear cache.
		fusion_reset_all_caches();

		// Flush permalinks.
		flush_rewrite_rules();

		wp_send_json_success();
	}

	/**
	 * Is string JSON?
	 *
	 * @access public
	 * @since 3.7
	 * @param string $string The string.
	 * @return boolean
	 */
	public function is_json( $string ) {

		if ( is_array( $string ) ) {
			return false;
		}

		json_decode( $string );
		return JSON_ERROR_NONE === json_last_error();
	}

	/**
	 * Save color and typography global options.
	 *
	 * @access public
	 * @since 7.7
	 */
	public function ajax_save_globals() {
		$this->check_nonce();

		$scheme   = isset( $_POST['scheme'] ) ? wp_unslash( $_POST['scheme'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
		$typo_set = isset( $_POST['typo_set'] ) ? wp_unslash( $_POST['typo_set'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
		$ratio    = isset( $_POST['sizing_ratio'] ) ? (float) $_POST['sizing_ratio'] : 1.333; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
		$base     = isset( $_POST['base_size'] ) ? (float) $_POST['base_size'] : 16; // phpcs:ignore WordPress.Security.NonceVerification
		$dark     = ( isset( $_POST['dark_light'] ) && 'dark' === $_POST['dark_light'] ); // phpcs:ignore WordPress.Security.NonceVerification

		$sizes['h6_typography'] = $base;
		$sizes['h5_typography'] = $sizes['h6_typography'] * $ratio;
		$sizes['h4_typography'] = $sizes['h5_typography'] * $ratio;
		$sizes['h3_typography'] = $sizes['h4_typography'] * $ratio;
		$sizes['h2_typography'] = $sizes['h3_typography'] * $ratio;
		$sizes['h1_typography'] = $sizes['h2_typography'] * $ratio;
		$small_size             = 0.8125 * $base;
		$subheading_size        = 1.5 * $base;

		$light_colors_1 = [
			'awb-color1',
			'awb-color2',
			'awb-color3',
			'awb-color4',
		];
		$light_colors_2 = [
			'awb-color5',
			'awb-color6',
			'awb-color7',
			'awb-color8',
		];
		$dark_colors_1  = array_reverse( $light_colors_2 );
		$dark_colors_2  = array_reverse( $light_colors_1 );

		$avada_options           = apply_filters( 'fusion_settings_all_fields', [] );
		$saved_options           = get_option( Fusion_Settings::get_option_name(), [] );
		$saved_options_are_empty = ( empty( $saved_options ) ? true : false );

		// Set typography and colors back to variables.  For dark mode flip and also set heading font size.
		foreach ( $avada_options as $option ) {
			if ( 'typography' === $option['type'] ) {

				// No value saved, it is an array.
				if ( ! isset( $saved_options[ $option['id'] ] ) ) {
					$saved_options[ $option['id'] ] = [];
				}

				// Set each value from default.
				if ( is_array( $option['default'] ) ) {
					foreach ( $option['default'] as $param => $value ) {
						if ( 'color' === $param && $dark ) {
							$saved_options[ $option['id'] ][ $param ] = str_replace( $light_colors_1, $dark_colors_1, $value );

							// Unchanged, try second half of colors. Split to avoid double replacing.
							if ( $saved_options[ $option['id'] ][ $param ] === $value ) {
								$saved_options[ $option['id'] ][ $param ] = str_replace( $light_colors_2, $dark_colors_2, $value );
							}
						} else {
							$saved_options[ $option['id'] ][ $param ] = $value;
						}
					}
				}

				// Set font size based on scale.
				if ( isset( $sizes[ $option['id'] ] ) ) {
					$saved_options[ $option['id'] ]['font-size'] = (string) round( $sizes[ $option['id'] ], 2 ) . 'px';
				}
			} elseif ( ( 'color-alpha' === $option['type'] || 'color' === $option['type'] ) && isset( $option['default'] ) ) {
				if ( $dark ) {
					$saved_options[ $option['id'] ] = str_replace( $light_colors_1, $dark_colors_1, $option['default'] );

					// Unchanged, try second half of colors. Split to avoid double replacing.
					if ( $saved_options[ $option['id'] ] === $option['default'] ) {
						$saved_options[ $option['id'] ] = str_replace( $light_colors_2, $dark_colors_2, $option['default'] );
					}

					// Reverse the lightness if necessary.
					if ( false !== strpos( $saved_options[ $option['id'] ], 'hsla' ) ) {
						$hsla_flip                      = [
							'-l) -' => '-x) -',
							'-l) +' => '-l) -',
							'-x) -' => '-l) +',
						];
						$saved_options[ $option['id'] ] = str_replace( array_keys( $hsla_flip ), array_values( $hsla_flip ), $saved_options[ $option['id'] ] );
					}
				} else {
					$saved_options[ $option['id'] ] = $option['default'];
				}
			} elseif ( isset( $option['default'] ) && $saved_options_are_empty ) {
				$saved_options[ $option['id'] ] = $option['default'];
			}
		}

		// Set the color palette.
		$saved_options['color_palette'] = AWB_Global_Colors()->get_palette();
		foreach ( $scheme as $color_slug => $value ) {
			if ( ! is_array( $value ) ) {
				$value = [];
			}

			if ( ! isset( $value['color'] ) ) {
				$value['color'] = '#ffffff';
			}

			if ( ! isset( $value['label'] ) ) {
				$value['label'] = __( 'Color', 'Avada' );
			}

			$saved_options['color_palette'][ $color_slug ] = $value;
		}

		// Map the option naming.
		$set_data = [
			'headings'    => 'typography1',
			'subheadings' => 'typography2',
			'lead'        => 'typography3',
			'body'        => 'typography4',
			'small'       => 'typography5',
		];

		// Set the typography sets.
		$saved_options['typography_sets'] = AWB_Global_Typography()->get_typography();
		foreach ( $typo_set as $subset => $data ) {
			$set_name = $set_data[ $subset ];

			// We only want italic as style.
			$data['font-style'] = isset( $data['font-style'] ) && 'italic' === $data['font-style'] ? 'italic' : '';

			foreach ( $data as $param => $value ) {
				$set_parameter = str_replace( '_', '-', $param );
				$set_value     = $value;
				if ( 'font-size' === $set_parameter ) {
					if ( 'body' === $subset || 'lead' === $subset ) {
						$set_value = round( $base, 2 ) . 'px';
					}

					if ( 'headings' === $subset ) {
						$set_value = round( $sizes['h1_typography'], 2 ) . 'px';
					}

					if ( 'subheadings' === $subset ) {
						$set_value = round( $subheading_size, 2 ) . 'px';
					}

					if ( 'small' === $subset ) {
						$set_value = round( $small_size, 2 ) . 'px';
					}
				}

				$saved_options['typography_sets'][ $set_name ][ $set_parameter ] = $set_value;
			}
			$saved_options['typography_sets'][ $set_name ]['variant'] = (int) $saved_options['typography_sets'][ $set_name ]['font-weight'] . $data['font-style'];
		}

		// Save the new globals.
		update_option( Fusion_Settings::get_option_name(), $saved_options );

		wp_send_json_success( $saved_options );
	}

	/**
	 * Return global value instead of usual page option.
	 *
	 * @access public
	 * @param string $value The value.
	 * @param int    $id    The ID.
	 * @return string
	 */
	public function global_value( $value, $id ) {
		$settings = awb_get_fusion_settings();
		return $settings->get( $id );
	}

	/**
	 * Override root so IDs are correct.
	 *
	 * @access public
	 * @param string $name The name.
	 * @param int    $id   The ID.
	 * @return string
	 */
	public function global_name( $name, $id ) {
		return str_replace( '[]', '', ltrim( $id, '_' ) );
	}

	/**
	 * Enequeue required scripts.
	 *
	 * @access public
	 * @return void
	 */
	public function add_scripts() {
		$version = Avada::get_theme_version();
		wp_enqueue_style( 'awb_wizard_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/awb-wizard.css', [], $version );
		wp_enqueue_style( 'awb_setup_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/awb-setup-wizard.css', [], $version );
		wp_enqueue_script( 'webfont-loader', FUSION_LIBRARY_URL . '/inc/fusion-app/assets/js/webfontloader.js', [], '1.6.28', false );
		wp_register_script( 'fuse-script', FUSION_LIBRARY_URL . '/assets/min/js/library/fuse.js', [], AVADA_VERSION, true );
		wp_enqueue_script( 'lazysizes', FUSION_LIBRARY_URL . '/assets/min/js/library/lazysizes.js', [], AVADA_VERSION, true );

		wp_register_script(
			'awb-studio-modal',
			Avada::$template_dir_url . '/assets/admin/js/awb-studio-modal.js',
			[ 'jquery', 'fuse-script' ],
			$version,
			false
		);

		AWB_Global_Colors()->enqueue();
		AWB_Global_Typography()->enqueue();

		wp_enqueue_script( 'awb_setup_js', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/awb-setup-wizard.js', [ 'jquery', 'webfont-loader', 'fuse-script', 'wp-util', 'shortcode', 'awb-studio-modal', 'jquery-color' ], $version, true );

		wp_localize_script( 'awb_setup_js', 'fusionBuilderText', fusion_app_textdomain_strings() );

		wp_localize_script(
			'awb_setup_js',
			'awbSetup',
			[
				'saveChange'       => __( 'Do you want to proceed without saving changes?', 'Avada' ),
				'isRegistered'     => Avada()->registration->is_registered(),
				'studioData'       => class_exists( 'AWB_Studio' ) ? AWB_Studio()->get_data() : [],
				'studioURL'        => class_exists( 'AWB_Studio' ) ? AWB_Studio()->get_studio_url() : '',
				'colors404Message' => __( 'Sorry, the color schemes couldn\'t load because the server didn\'t respond.', 'Avada' ),
			]
		);

		wp_enqueue_script(
			'jquery.biscuit',
			Avada::$template_dir_url . '/assets/admin/js/jquery.biscuit.js',
			[ 'jquery' ],
			$version,
			false
		);
		wp_register_script(
			'avada_upload',
			Avada::$template_dir_url . '/assets/admin/js/upload.js',
			[ 'jquery' ],
			$version,
			false
		);
		wp_enqueue_media();

		// Select field assets.
		wp_dequeue_script( 'tribe-events-select2' );

		wp_enqueue_style(
			'select2-css',
			Avada::$template_dir_url . '/assets/admin/css/select2.css',
			[],
			'4.0.3',
			'all'
		);
		wp_enqueue_script(
			'selectwoo-js',
			Avada::$template_dir_url . '/assets/admin/js/selectWoo.full.min.js',
			[ 'jquery' ],
			'1.0.2',
			false
		);

		// Range field assets.
		wp_enqueue_style(
			'avadaredux-nouislider-css',
			FUSION_LIBRARY_URL . '/inc/redux/framework/FusionReduxCore/inc/fields/slider/vendor/nouislider/fusionredux.jquery.nouislider.css',
			[],
			'5.0.0',
			'all'
		);

		wp_enqueue_script(
			'avadaredux-nouislider-js',
			Avada::$template_dir_url . '/assets/admin/js/jquery.nouislider.min.js',
			[ 'jquery' ],
			'5.0.0',
			true
		);
		wp_enqueue_script(
			'wnumb-js',
			Avada::$template_dir_url . '/assets/admin/js/wNumb.js',
			[ 'jquery' ],
			'1.0.2',
			true
		);

		// Color fields.
		if ( function_exists( 'AWB_Global_Colors' ) ) {
			AWB_Global_Colors()->enqueue();
		}

		// Option type JS.
		wp_enqueue_script(
			'avada-fusion-options',
			Avada::$template_dir_url . '/assets/admin/js/avada-fusion-options.js',
			[ 'jquery', 'jquery-ui-sortable', 'avada_upload' ],
			$version,
			false
		);

		// Studio preview.
		wp_enqueue_script(
			'fusion-admin-notices',
			trailingslashit( Fusion_Scripts::$js_folder_url ) . 'general/awb-studio-preview-admin.js',
			[ 'jquery' ],
			$version,
			false
		);
	}

	/**
	 * Check if nonce is valid.
	 *
	 * @access public
	 */
	public function check_nonce() {
		check_admin_referer( 'awb_setup_nonce', 'awb_setup_nonce' );
	}

	/**
	 * Get typography CSS variables for data.
	 *
	 * @access public
	 * @param array $data The data.
	 */
	public function get_typo_vars( $data = [] ) {
		$variables = 'style="';
		foreach ( $data as $key => $values ) {
			if ( ! empty( $values ) && is_array( $values ) ) {
				foreach ( $values as $param => $value ) {
					if ( '' !== $value ) {
						$variables .= '--' . $key . '_' . $param . ':' . $value . ';';
					}
				}
			}
		}
		return $variables . '"';
	}

	/**
	 * Get typography set data.
	 *
	 * @access public
	 */
	public function get_typo_sets() {
		global $wp_filesystem;

		if ( null !== $this->typo_data ) {
			return $this->typo_data;
		}

		$response = wp_remote_get(
			'https://updates.theme-fusion.com/wp-json/setup_data/typography/',
			[
				'user-agent' => 'avada-user-agent',
			]
		);

		// Check for error.
		if ( ! is_wp_error( $response ) ) {
			$response_code = (int) wp_remote_retrieve_response_code( $response );

			if ( 200 !== $response_code ) {
				$this->typo_data = [];
				return $this->typo_data;
			}

			// Parse response.
			$data = wp_remote_retrieve_body( $response );

			// Check for error.
			if ( ! is_wp_error( $data ) ) {
				$this->typo_data = json_decode( $data, true );
				return $this->typo_data;
			}
		}

		return [];
	}

	/**
	 * Render header specifically for wizard.
	 *
	 * @param string $screen_classes Classes for page.
	 * @return void
	 */
	public function render_header( $screen_classes ) {
		?>
		<div class="<?php echo esc_html( $screen_classes ); ?>">
			<header class="avada-db-header-main">
				<div class="avada-db-header-main-container">
					<a class="avada-db-logo" href="<?php echo esc_url( admin_url( 'admin.php?page=avada' ) ); ?>" aria-label="<?php esc_attr_e( 'Link to Avada dashboard', 'Avada' ); ?>">
						<i class="avada-db-logo-icon fusiona-avada-logo"></i>
						<h1><?php esc_html_e( 'Setup Wizard', 'Avada' ); ?></h1>
					</a>
					<div class="wizard-hero-header">
						<a class="button button-primary" target="_blank" href="https://theme-fusion.com/documentation/avada/getting-started/how-to-use-the-avada-setup-wizard/"><?php esc_html_e( 'Documentation', 'Avada' ); ?></a>
					</div>
				</div>
			</header>
			<header class="avada-db-header-sticky avada-db-card awb-wizard-steps awb-swmenu" data-state="intro">
				<ol class="intro-start">
					<li class="awb-wizard-link<?php echo ( ! Avada()->registration->is_registered() || ! AWB_Prebuilt_Websites()->are_avada_plugins_active() ? ' active' : '' ); ?> <?php echo ( Avada()->registration->is_registered() ? ' completed' : '' ); ?>" data-id="1"><span class="awb-wizard-link-text"><?php esc_html_e( 'Registration', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link<?php echo ( Avada()->registration->is_registered() && AWB_Prebuilt_Websites()->are_avada_plugins_active() ? ' active' : '' ); ?> awb-setup-type" data-id="2"><span class="awb-wizard-link-text"><?php esc_html_e( 'Setup Type', 'Avada' ); ?></span></li>
				</ol>
				<ol class="scratch-start">
					<li class="awb-wizard-link" data-id="1"><span class="awb-wizard-link-text"><?php esc_html_e( 'Registration', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="2"><span class="awb-wizard-link-text"><?php esc_html_e( 'Setup Type', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="4"><span class="awb-wizard-link-text"><?php esc_html_e( 'Colors', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="5"><span class="awb-wizard-link-text"><?php esc_html_e( 'Typography', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="6"><span class="awb-wizard-link-text"><?php esc_html_e( 'Layouts', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="7"><span class="awb-wizard-link-text"><?php esc_html_e( 'Content', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="8"><span class="awb-wizard-link-text"><?php esc_html_e( 'Finish', 'Avada' ); ?></span></li>
				</ol>
				<ol class="prebuilt-start">
					<li class="awb-wizard-link" data-id="1"><span class="awb-wizard-link-text"><?php esc_html_e( 'Registration', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="2"><span class="awb-wizard-link-text"><?php esc_html_e( 'Setup Type', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="20"><span class="awb-wizard-link-text"><?php esc_html_e( 'Select A Website', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="21"><span class="awb-wizard-link-text"><?php esc_html_e( 'Finish', 'Avada' ); ?></span></li>
				</ol>
			</header>

			<div class="avada-db-demos-notices"><h1></h1> <?php do_action( 'avada_dashboard_notices' ); ?></div>
		<?php
	}

	/**
	 * Add new layout.
	 *
	 * @access public
	 * @since 7.7
	 * @param string $title Layout title.
	 * @param array  $template_terms the template terms.
	 * @param array  $conditions the layout conditions array.
	 */
	public function add_layout( $title, $template_terms = [], $conditions = [] ) {
		$layout = [
			'post_title'  => $title,
			'post_status' => 'publish',
			'post_type'   => 'fusion_tb_layout',
		];

		$layout_id = wp_insert_post( $layout );

		if ( is_wp_error( $layout_id ) ) {
			$error_string = $layout_id->get_error_message();
			die( wp_json_encode( $error_string ) );
		}

		$content = [
			'template_terms' => $template_terms,
			'conditions'     => $conditions,
		];

		Fusion_Template_Builder::update_layout_content( $layout_id, $content );
		fusion_reset_all_caches();

		return $layout_id;
	}

	/**
	 * Insert post.
	 *
	 * @access public
	 * @since 7.7
	 * @param string $title Post title.
	 * @param string $content Post content.
	 * @param string $post_type Post type.
	 */
	public function insert_post( $title, $content, $post_type ) {

		$args = [
			'post_title'   => $title,
			'post_content' => $content,
			'post_type'    => $post_type,
			'post_status'  => 'publish',
		];

		$post_id = wp_insert_post( $args );

		if ( is_wp_error( $post_id ) ) {
			$error_string = $post_id->get_error_message();
			die( wp_json_encode( $error_string ) );
		}

		$new_post = [
			'post_id' => $post_id,
		];

		return $new_post;
	}

	/**
	 * Import dummy content.
	 *
	 * @access public
	 * @since 7.7
	 * @param array $pages The selected pages.
	 * @param mixed $scheme The scheme.
	 */
	public function import_dummy_content( $pages, $scheme ) {

		// Placeholder Image.
		$placeholder_image    = get_template_directory_uri() . '/assets/images/avada-placeholder.png';
		$placeholder_image_id = get_option( 'avada_setup_placeholder_image', 0 );

		if ( ! $placeholder_image_id ) {
			$image = media_sideload_image( $placeholder_image, 0, null, 'id' );
			if ( ! is_wp_error( $image ) ) {
				$placeholder_image_id = $image;
				update_option( 'avada_setup_placeholder_image', $image );
			}
		}

		// upload placeholder SVG.
		$placeholder_bg_color = isset( $scheme['color2']['color'] ) ? $scheme['color2']['color'] : '#FAFAFA';

		if ( $this->dark ) {
			$placeholder_bg_color = isset( $scheme['color6']['color'] ) ? $scheme['color6']['color'] : '#222222';
		}


		// Blog posts.
		$blog = array_search( 'blog', array_column( $pages, 'id' ), true );
		if ( $blog ) {
			$in_post_image = AWB_Studio_Import()->generate_dynamic_placeholder(
				[
					'width'  => 800,
					'height' => 600,
					'color'  => $placeholder_bg_color,
				]
			);
			$posts_content = '[fusion_builder_container type=\"flex\" hundred_percent=\"no\" hundred_percent_height=\"no\" hundred_percent_height_scroll=\"no\" align_content=\"stretch\" flex_align_items=\"flex-start\" flex_justify_content=\"flex-start\" hundred_percent_height_center_content=\"yes\" equal_height_columns=\"no\" container_tag=\"div\" hide_on_mobile=\"small-visibility,medium-visibility,large-visibility\" status=\"published\" border_style=\"solid\" box_shadow=\"no\" box_shadow_blur=\"0\" box_shadow_spread=\"0\" gradient_start_position=\"0\" gradient_end_position=\"100\" gradient_type=\"linear\" radial_direction=\"center center\" linear_angle=\"180\" background_position=\"center center\" background_repeat=\"no-repeat\" fade=\"no\" background_parallax=\"none\" enable_mobile=\"no\" parallax_speed=\"0.3\" background_blend_mode=\"none\" video_aspect_ratio=\"16:9\" video_loop=\"yes\" video_mute=\"yes\" absolute=\"off\" absolute_devices=\"small,medium,large\" sticky=\"off\" sticky_devices=\"small-visibility,medium-visibility,large-visibility\" sticky_transition_offset=\"0\" scroll_offset=\"0\" animation_direction=\"left\" animation_speed=\"0.3\" filter_hue=\"0\" filter_saturation=\"100\" filter_brightness=\"100\" filter_contrast=\"100\" filter_invert=\"0\" filter_sepia=\"0\" filter_opacity=\"100\" filter_blur=\"0\" filter_hue_hover=\"0\" filter_saturation_hover=\"100\" filter_brightness_hover=\"100\" filter_contrast_hover=\"100\" filter_invert_hover=\"0\" filter_sepia_hover=\"0\" filter_opacity_hover=\"100\" filter_blur_hover=\"0\"][fusion_builder_row][fusion_builder_column type=\"1_1\" layout=\"1_1\" align_self=\"auto\" content_layout=\"column\" align_content=\"flex-start\" valign_content=\"flex-start\" content_wrap=\"wrap\" center_content=\"no\" target=\"_self\" hide_on_mobile=\"small-visibility,medium-visibility,large-visibility\" sticky_display=\"normal,sticky\" order_medium=\"0\" order_small=\"0\" padding_right=\"60px\" padding_left=\"60px\" hover_type=\"none\" border_style=\"solid\" box_shadow=\"no\" box_shadow_blur=\"0\" box_shadow_spread=\"0\" background_type=\"single\" gradient_start_position=\"0\" gradient_end_position=\"100\" gradient_type=\"linear\" radial_direction=\"center center\" linear_angle=\"180\" background_position=\"left top\" background_repeat=\"no-repeat\" background_blend_mode=\"none\" filter_type=\"regular\" filter_hue=\"0\" filter_saturation=\"100\" filter_brightness=\"100\" filter_contrast=\"100\" filter_invert=\"0\" filter_sepia=\"0\" filter_opacity=\"100\" filter_blur=\"0\" filter_hue_hover=\"0\" filter_saturation_hover=\"100\" filter_brightness_hover=\"100\" filter_contrast_hover=\"100\" filter_invert_hover=\"0\" filter_sepia_hover=\"0\" filter_opacity_hover=\"100\" filter_blur_hover=\"0\" animation_direction=\"left\" animation_speed=\"0.3\" margin_bottom=\"65px\" margin_bottom_small=\"35px\" last=\"true\" border_position=\"all\" min_height=\"\" link=\"\" first=\"true\"][fusion_text rule_style=\"default\" animation_direction=\"left\" animation_speed=\"0.3\" hide_on_mobile=\"small-visibility,medium-visibility,large-visibility\" sticky_display=\"normal,sticky\" fusion_font_family_text_font=\"Lexend\" font_size=\"20px\" line_height=\"32px\" fusion_font_variant_text_font=\"300\"]Nam aliquet ante porta, gravida elit interdum, luctus porta sapien justo, at fringilla felis suscipit vestibulum volutpat metus. Praesent eu turpis ac mauris commodo interdum enim enim, bibendum a nisi vel.\r\n\r\nDonec sed mauris et ante tincidunt blandit. Sed quis tristique velit. Donec at convallis leo. Fusce semper hendrerit velit, ac lobortis elit aliquet nec. Mauris vehicula purus nunc, vel finibus velit ornare a. Proin cursus ullamcorper massa, nec laoreet justo malesuada vitae. Praesent dictum ultrices erat, eu rhoncus dolor ultrices ac. Duis accumsan vestibulum nunc quis pellentesque.\r\n\r\nIn ornare faucibus lacus, consequat ultrices arcu iaculis vitae. Suspendisse laoreet vel eros sit amet mollis. Aliquam erat volutpat.[/fusion_text][/fusion_builder_column][/fusion_builder_row][/fusion_builder_container][fusion_builder_container type=\"flex\" hundred_percent=\"no\" hundred_percent_height=\"no\" hundred_percent_height_scroll=\"no\" align_content=\"stretch\" flex_align_items=\"flex-start\" flex_justify_content=\"flex-start\" hundred_percent_height_center_content=\"yes\" equal_height_columns=\"no\" container_tag=\"div\" hide_on_mobile=\"small-visibility,medium-visibility,large-visibility\" status=\"published\" border_style=\"solid\" box_shadow=\"no\" box_shadow_blur=\"0\" box_shadow_spread=\"0\" gradient_start_position=\"0\" gradient_end_position=\"100\" gradient_type=\"linear\" radial_direction=\"center center\" linear_angle=\"180\" background_position=\"center center\" background_repeat=\"no-repeat\" fade=\"no\" background_parallax=\"none\" enable_mobile=\"no\" parallax_speed=\"0.3\" background_blend_mode=\"none\" video_aspect_ratio=\"16:9\" video_loop=\"yes\" video_mute=\"yes\" absolute=\"off\" absolute_devices=\"small,medium,large\" sticky=\"off\" sticky_devices=\"small-visibility,medium-visibility,large-visibility\" sticky_transition_offset=\"0\" scroll_offset=\"0\" animation_direction=\"left\" animation_speed=\"0.3\" filter_hue=\"0\" filter_saturation=\"100\" filter_brightness=\"100\" filter_contrast=\"100\" filter_invert=\"0\" filter_sepia=\"0\" filter_opacity=\"100\" filter_blur=\"0\" filter_hue_hover=\"0\" filter_saturation_hover=\"100\" filter_brightness_hover=\"100\" filter_contrast_hover=\"100\" filter_invert_hover=\"0\" filter_sepia_hover=\"0\" filter_opacity_hover=\"100\" filter_blur_hover=\"0\" margin_bottom=\"45px\"][fusion_builder_row][fusion_builder_column type=\"1_1\" layout=\"1_1\" align_self=\"auto\" content_layout=\"column\" align_content=\"flex-start\" valign_content=\"flex-start\" content_wrap=\"wrap\" center_content=\"no\" target=\"_self\" hide_on_mobile=\"small-visibility,medium-visibility,large-visibility\" sticky_display=\"normal,sticky\" order_medium=\"0\" order_small=\"0\" hover_type=\"none\" border_style=\"solid\" box_shadow=\"no\" box_shadow_blur=\"0\" box_shadow_spread=\"0\" background_type=\"single\" gradient_start_position=\"0\" gradient_end_position=\"100\" gradient_type=\"linear\" radial_direction=\"center center\" linear_angle=\"180\" background_position=\"left top\" background_repeat=\"no-repeat\" background_blend_mode=\"none\" filter_type=\"regular\" filter_hue=\"0\" filter_saturation=\"100\" filter_brightness=\"100\" filter_contrast=\"100\" filter_invert=\"0\" filter_sepia=\"0\" filter_opacity=\"100\" filter_blur=\"0\" filter_hue_hover=\"0\" filter_saturation_hover=\"100\" filter_brightness_hover=\"100\" filter_contrast_hover=\"100\" filter_invert_hover=\"0\" filter_sepia_hover=\"0\" filter_opacity_hover=\"100\" filter_blur_hover=\"0\" animation_direction=\"left\" animation_speed=\"0.3\" spacing_left=\"0%\" spacing_right=\"0%\" margin_bottom=\"65px\" margin_bottom_small=\"50px\" last=\"true\" border_position=\"all\" min_height=\"\" link=\"\" first=\"true\"][fusion_imageframe src="' . $in_post_image . '"][/fusion_imageframe][/fusion_builder_column][fusion_builder_column type=\"1_1\" layout=\"1_1\" align_self=\"auto\" content_layout=\"column\" align_content=\"flex-start\" valign_content=\"flex-start\" content_wrap=\"wrap\" center_content=\"no\" target=\"_self\" hide_on_mobile=\"small-visibility,medium-visibility,large-visibility\" sticky_display=\"normal,sticky\" order_medium=\"0\" order_small=\"0\" padding_right=\"60px\" padding_left=\"60px\" hover_type=\"none\" border_style=\"solid\" box_shadow=\"no\" box_shadow_blur=\"0\" box_shadow_spread=\"0\" background_type=\"single\" gradient_start_position=\"0\" gradient_end_position=\"100\" gradient_type=\"linear\" radial_direction=\"center center\" linear_angle=\"180\" background_position=\"left top\" background_repeat=\"no-repeat\" background_blend_mode=\"none\" filter_type=\"regular\" filter_hue=\"0\" filter_saturation=\"100\" filter_brightness=\"100\" filter_contrast=\"100\" filter_invert=\"0\" filter_sepia=\"0\" filter_opacity=\"100\" filter_blur=\"0\" filter_hue_hover=\"0\" filter_saturation_hover=\"100\" filter_brightness_hover=\"100\" filter_contrast_hover=\"100\" filter_invert_hover=\"0\" filter_sepia_hover=\"0\" filter_opacity_hover=\"100\" filter_blur_hover=\"0\" animation_direction=\"left\" animation_speed=\"0.3\" margin_bottom=\"0px\" last=\"true\" border_position=\"all\" min_height=\"\" link=\"\" first=\"true\"][fusion_text rule_style=\"default\" animation_direction=\"left\" animation_speed=\"0.3\" hide_on_mobile=\"small-visibility,medium-visibility,large-visibility\" sticky_display=\"normal,sticky\" fusion_font_family_text_font=\"Lexend\" font_size=\"20px\" line_height=\"32px\" fusion_font_variant_text_font=\"300\"]Nam aliquet ante porta, gravida elit interdum, luctus porta sapien justo, at fringilla felis suscipit vestibulum volutpat metus. Praesent eu turpis ac mauris commodo interdum enim enim, bibendum a nisi vel.\r\n\r\nDonec sed mauris et ante tincidunt blandit. Sed quis tristique velit. Donec at convallis leo. Fusce semper hendrerit velit, ac lobortis elit aliquet nec. Mauris vehicula purus nunc, vel finibus velit ornare a. Proin cursus ullamcorper massa, nec laoreet justo malesuada vitae. Praesent dictum ultrices erat, eu rhoncus dolor ultrices ac. Duis accumsan vestibulum nunc quis pellentesque.\r\n\r\nIn ornare faucibus lacus, consequat ultrices arcu iaculis vitae. Suspendisse laoreet vel eros sit amet mollis. Aliquam erat volutpat.[/fusion_text][/fusion_builder_column][/fusion_builder_row][/fusion_builder_container]';

			// Add post categories.
			$cats = [
				'Animals',
				'Architecture',
				'Business',
				'Fitness',
				'Nutrition',
				'Real Estate',
				'Sports',
				'Technology',
			];

			$cats_ids = [];
			foreach ( $cats as $cat ) {
				$new_cat = wp_insert_term( $cat, 'category' );
				if ( ! is_wp_error( $new_cat ) ) {
					$cats_ids[ $new_cat['term_id'] ] = $new_cat;
				}
			}

			// Add posts.
			$posts = [
				[
					'title' => 'Aliquam congue semper metus',
				],
				[
					'title' => 'Cras suscipit ante erat eleifend',
				],
				[
					'title' => 'Vivamus ut magna turpis',
				],
				[
					'title' => 'Fusce cursus dolor sit amet',
				],
				[
					'title' => 'Aliquam luctus sem massa',
				],
				[
					'title' => 'Aenean consectetur tempor metus',
				],
				[
					'title' => 'Malesuada Mauris Blandit',
				],
				[
					'title' => 'Nulla porttitor accumsan tincidunt mauris blandit.',
				],
				[
					'title' => 'How Avada Can Become Your Full-Fledged Webinar Hub',
				],
				[
					'title' => 'How to Use Instagram Feeds to Boost Traffic and Conversions',
				],
				[
					'title' => 'Mauris blandit aliquet elit, eget tincidunt nibh',
				],
				[
					'title' => 'Curabitur arcu erat, accumsan id imperdiet et, porttitor',
				],
				[
					'title' => 'Curabitur arcu erat, accumsan id imperdiet et, porttitor',
				],
				[
					'title' => 'Proin eget tortor risus praesent sapien massa',
				],
			];

			foreach ( $posts as $post ) {
				$new_post = wp_insert_post(
					[
						'post_title'    => $post['title'],
						'post_content'  => $posts_content,
						'post_status'   => 'publish',
						'post_type'     => 'post',
						'post_category' => array_rand( $cats_ids, 2 ),
					]
				);

				if ( ! is_wp_error( $new_post ) ) {

					// import flag.
					update_post_meta( $new_post, '_fusion_setup_wizard_import', true );

					// Post Feature Image.
					if ( $placeholder_image_id ) {
						set_post_thumbnail( $new_post, $placeholder_image_id );
					}
				}
			}
		}

		// Shop poroducts.
		$shop = array_search( 'shop', array_column( $pages, 'id' ), true );
		if ( $shop && class_exists( 'WooCommerce' ) ) {
			$products_content    = 'Cras ultricies ligula sed magna dictum porta. Mauris blandit aliquet elit, eget tincidunt nibh pulvinar a. Donec rutrum congue leo eget malesuada.';
			$products_short_desc = 'Donec sollicitudin molestie malesuada. Mauris blandit aliquet elit, eget tincidunt nibh pulvinar a mauris blandit aliquet elit.';

			// Add post categories.
			$cats = [
				'Camping',
				'Clothes',
				'Mat',
				'Shop',
				'Technology',
			];

			$cats_ids = [];
			foreach ( $cats as $cat ) {
				$new_cat = wp_insert_term( $cat, 'product_cat' );
				if ( ! is_wp_error( $new_cat ) ) {
					$cats_ids[ $new_cat['term_id'] ] = $new_cat;
				}
			}

			$products = [
				[
					'title' => 'Bottle',
					'price' => '19',
				],
				[
					'title'    => 'Backpack',
					'price'    => '49',
					'featured' => true,
				],
				[
					'title' => 'Coffee Pot',
					'price' => '39',
				],
				[
					'title'      => 'Camera',
					'price'      => '230',
					'sale_price' => '199',
					'featured'   => true,
				],
				[
					'title'      => 'Computer Monitor',
					'price'      => '499',
					'sale_price' => '399',
					'featured'   => true,
				],
				[
					'title'      => 'Tablet Pro',
					'price'      => '799',
					'sale_price' => '599',
					'fetured'    => true,
				],
				[
					'title'      => 'Smart Watch Pro',
					'price'      => '599',
					'sale_price' => '299',
				],
			];

			foreach ( $products as $product ) {
				$new_product = wp_insert_post(
					[
						'post_title'   => $product['title'],
						'post_content' => $products_content,
						'post_status'  => 'publish',
						'post_type'    => 'product',
						'post_excerpt' => $products_short_desc,
					]
				);

				if ( ! is_wp_error( $new_product ) ) {

					// import flag.
					update_post_meta( $new_product, '_fusion_setup_wizard_import', true );

					// Feature Image.
					if ( $placeholder_image_id ) {
						set_post_thumbnail( $new_product, $placeholder_image_id );
					}

					// Product custom data.
						$product_object = function_exists( 'wc_get_product' ) ? wc_get_product( $new_product ) : '';
						$price          = isset( $product['price'] ) ? $product['price'] : '99.00';
						$sale_price     = isset( $product['sale_price'] ) ? $product['sale_price'] : '';
						$featured       = isset( $product['featured'] ) ? $product['featured'] : '';
						$gallery        = [ $placeholder_image_id, $placeholder_image_id, $placeholder_image_id, $placeholder_image_id ];

					if ( is_object( $product_object ) ) {

						$product_object->set_category_ids( [ array_rand( $cats_ids ) ] );
						$product_object->set_regular_price( $price );
						if ( $sale_price ) {
							$product_object->set_sale_price( $sale_price );
						}
						if ( $featured ) {
							$product_object->set_featured( $featured );
						}
						$product_object->set_gallery_image_ids( $gallery );
						$product_object->save();
					}
				}
			}
		}

		// Portfolio content.
		$portfolio = array_search( 'portfolio', array_column( $pages, 'id' ), true );
		if ( $portfolio ) {
			$projects_content = '[fusion_builder_container type="flex" hundred_percent="no" equal_height_columns="no" hide_on_mobile="small-visibility,medium-visibility,large-visibility" background_position="center center" background_repeat="no-repeat" fade="no" background_parallax="none" parallax_speed="0.3" video_aspect_ratio="16:9" video_loop="yes" video_mute="yes" border_style="solid" padding_left="0px" padding_right="0px" admin_toggled="yes"][fusion_builder_row][fusion_builder_column type="1_1" layout="1_1" background_position="left top" border_style="solid" border_position="all" spacing="yes" background_repeat="no-repeat" margin_top="0px" margin_bottom="0px" animation_speed="0.3" animation_direction="left" hide_on_mobile="small-visibility,medium-visibility,large-visibility" center_content="no" last="true" hover_type="none" first="true" min_height="" link="" background_blend_mode="overlay"][fusion_title title_type="text" rotation_effect="bounceIn" display_time="1200" highlight_effect="circle" loop_animation="off" highlight_width="9" highlight_top_margin="0" title_link="off" link_target="_self" content_align="left" size="2" font_size="29px" text_shadow="no" text_shadow_blur="0" gradient_font="no" gradient_start_position="0" gradient_end_position="100" gradient_type="linear" radial_direction="center center" linear_angle="180" style_type="default" animation_direction="left" animation_speed="0.3" hide_on_mobile="small-visibility,medium-visibility,large-visibility" sticky_display="normal,sticky" margin_bottom="25px" margin_bottom_small="25px"]

			About this project

			[/fusion_title][fusion_text margin_bottom="40px"]

			Quisque velit nisi, pretium ut lacinia in, elementum id enim. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae;

			Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula. Curabitur aliquet quam id dui posuere blandit.

			Nulla quis lorem ut libero malesuada feugiat. Donec sollicitudin molestie malesuada.

			[/fusion_text][fusion_button link="#" target="_self" color="default" button_gradient_top_color="var(--awb-color4)" button_gradient_bottom_color="var(--awb-color4)" button_gradient_top_color_hover="hsla(var(--awb-color4-h),var(--awb-color4-s),var(--awb-color4-l),calc( var(--awb-color4-a) - 95% ))" button_gradient_bottom_color_hover="hsla(var(--awb-color4-h),var(--awb-color4-s),var(--awb-color4-l),calc( var(--awb-color4-a) - 95% ))" accent_color="var(--awb-color1)" accent_hover_color="var(--awb-color4)" stretch="default" icon_position="left" icon_divider="no" animation_direction="left" animation_speed="0.3" hide_on_mobile="medium-visibility,large-visibility" sticky_display="normal,sticky" border_color="var(--awb-color1)" border_hover_color="var(--awb-color4)"]Visit This Project[/fusion_button][/fusion_builder_column][/fusion_builder_row][/fusion_builder_container]';

			// Add portfolio categories.
			$cats = [
				'Branding',
				'Logo Design',
				'Packaging',
				'Strategy',
				'Studio Project',
				'Technology',
				'Website Design',
			];

			$cats_ids = [];
			foreach ( $cats as $cat ) {
				$new_cat = wp_insert_term( $cat, 'portfolio_category' );
				if ( ! is_wp_error( $new_cat ) ) {
					$cats_ids[ $new_cat['term_id'] ] = $new_cat;
				}
			}

			$projects = [
				[
					'title' => 'Aesthetic Interior',
				],
				[
					'title' => 'Building a better website',
				],
				[
					'title' => 'Package Design',
				],
				[
					'title' => 'Creative Pitcher',
				],
				[
					'title' => 'Abstract 3D shape',
				],
				[
					'title' => 'Aesthetic websites',
				],
				[
					'title' => 'Avada Interior Prebuilt Website',
				],
				[
					'title' => 'Avada Fitness Prebuilt Website',
				],
				[
					'title' => 'Avada Builder Prebuilt Website',
				],
			];

			foreach ( $projects as $project ) {
				$new_post = wp_insert_post(
					[
						'post_title'   => $project['title'],
						'post_content' => $projects_content,
						'post_status'  => 'publish',
						'post_type'    => 'avada_portfolio',
					]
				);

				if ( ! is_wp_error( $new_post ) ) {

					// import flag.
					update_post_meta( $new_post, '_fusion_setup_wizard_import', true );

					// portfolio categories.
					wp_set_post_terms( $new_post, array_rand( $cats_ids ), 'portfolio_category' );

					// Post Feature Image.
					if ( $placeholder_image_id ) {
						set_post_thumbnail( $new_post, $placeholder_image_id );
					}
				}
			}
		}

		// Set import flag to avoid multiple content.
		update_option( 'avada_setup_wizard_dummy_content', true );
	}

	/**
	 * Update dummy conent feature images.
	 *
	 * @access public
	 * @since 7.7
	 */
	public function update_dummy_content_image() {
		$placeholder_image_id = get_option( 'avada_setup_placeholder_image', 0 );
		if ( $placeholder_image_id ) {
			// get posts.
			$args  = [
				'post_type'      => 'any',
				'meta_key'       => '_fusion_setup_wizard_import', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => true, // phpcs:ignore WordPress.DB.SlowDBQuery
				'posts_per_page' => -1, // phpcs:ignore WPThemeReview.CoreFunctionality.PostsPerPage
			];
			$query = new WP_Query( $args );

			foreach ( $query->posts as $post ) {
				set_post_thumbnail( $post->ID, $placeholder_image_id );
				if ( 'product' === $post->post_type ) {
					$product_object = function_exists( 'wc_get_product' ) ? wc_get_product( $post->ID ) : '';
					if ( is_object( $product_object ) ) {
						$product_object->set_gallery_image_ids( [ $placeholder_image_id ] );
						$product_object->save();
					}
				}
			}
		}
	}

	/**
	 * Get dynamic ID, basically it used for layout conditions.
	 *
	 * @access public
	 * @since 7.7
	 * @param string $page The selected pages.
	 */
	public function get_dynamic_id( $page ) {
		$id = '';
		if ( 'cart_page' === $page ) {
			$id = get_option( 'woocommerce_cart_page_id' );
		} elseif ( 'checkout_page' === $page ) {
			$id = get_option( 'woocommerce_checkout_page_id' );
		}

		return $id;
	}

	/**
	 * Set permalinks.
	 *
	 * @access public
	 * @since 7.7
	 * @param mixed $scheme The scheme.
	 */
	public function svg_dynamic_placeholder( $scheme ) {
		// upload placeholder SVG.
		$placeholder_bg_color   = isset( $scheme['color2']['color'] ) ? $scheme['color2']['color'] : '#FAFAFA';
		$placeholder_logo_color = isset( $scheme['color4']['color'] ) ? $scheme['color4']['color'] : '#E0E0E0';

		if ( $this->dark ) {
			$placeholder_bg_color = isset( $scheme['color6']['color'] ) ? $scheme['color6']['color'] : '#222222';
		}

		$placeholder_image = '<?xml version="1.0" encoding="UTF-8"?>
		<svg width="1200px" height="1200px" viewBox="0 0 1200 1200" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
			<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
				<g id="Group">
					<rect id="Rectangle" fill="' . $placeholder_bg_color . '" x="0" y="0" width="1200" height="1200"></rect>
					<g id="avada" transform="translate(391.000000, 391.000000)" fill="' . $placeholder_logo_color . '" fill-rule="nonzero">
						<path d="M0.918313734,405.706043 C-2.00418604,411.476457 2.42131362,418 8.93431312,418 C193.970299,418.334049 332.079289,239.116723 230.710296,34.9779032 L215.596797,4.70414119 C212.423798,-1.56804706 203.572798,-1.56804706 200.399799,4.70414119 L0.918313734,405.706043 Z" id="Path"></path>
						<path d="M323.729254,220.712286 C320.553105,214.429238 311.609737,214.429238 308.517171,220.712286 L262.546589,310.601763 C237.806059,360.112182 268.898888,418 328.493478,418 L409.485283,418 C415.753999,418 419.933143,411.381856 417.091325,405.685226 L323.729254,220.712286 Z" id="Path"></path>
					</g>
				</g>
			</g>
		</svg>';

		$placeholder_file = wp_upload_bits( 'avada-placeholder.svg', null, $placeholder_image );

		if ( empty( $placeholder_file['error'] ) && $placeholder_file['url'] ) {
				$placeholder_attachment_id = wp_insert_attachment(
					[
						'guid'           => $placeholder_file['url'],
						'post_mime_type' => 'image/svg+xml',
						'post_title'     => 'Avada-placeholder',
						'post_content'   => '',
						'post_status'    => 'inherit',
					],
					$placeholder_file['file']
				);
			if ( ! is_wp_error( $placeholder_attachment_id ) && $placeholder_attachment_id ) {
				update_option( 'avada_setup_placeholder_image', $placeholder_attachment_id );
			}
		}
	}

	/**
	 * Setup wizard studio import options templates.
	 *
	 * @access public
	 * @since 7.7
	 * @param mixed $layout The layout.
	 * @param mixed $defaults The defaults.
	 */
	public static function studio_import_options_template( $layout = 'header', $defaults = [] ) {
		$type   = isset( $defaults['overWriteType'] ) ? $defaults['overWriteType'] : 'inherit';
		$images = isset( $defaults['images'] ) ? $defaults['images'] : 'do-import-images';
		$invert = isset( $defaults['shouldInvert'] ) ? $defaults['shouldInvert'] : 'dont-invert';
		?>
		<div class="awb-setup-import-options">
			<!-- Overwrite option -->
			<div class="awb-import-option awb-import-style-typo">
				<label for=""><?php esc_html_e( 'Style', 'fusion-builder' ); ?></label>
				<div class="awb-option-button">
					<div class="awb-option-item">
						<input id="<?php echo esc_attr( $layout ); ?>_inherit" type="radio" name="<?php echo esc_attr( $layout ); ?>_overwrite-type" value="inherit" <?php checked( $type, 'inherit' ); ?>>
						<label for="<?php echo esc_attr( $layout ); ?>_inherit" data-tooltip="<?php esc_attr_e( 'Local Colors & Typography', 'fusion-builder' ); ?>">

							<i class="fusiona-fit-import"></i>
						</label>
					</div>
					<div class="awb-option-item">
						<input id="<?php echo esc_attr( $layout ); ?>_replace-pos" type="radio" name="<?php echo esc_attr( $layout ); ?>_overwrite-type" value="replace-pos" <?php checked( $type, 'replace-pos' ); ?>>
						<label for="<?php echo esc_attr( $layout ); ?>_replace-pos" data-tooltip="<?php esc_attr_e( 'WYSIWYG Studio Styles', 'fusion-builder' ); ?>">
							<i class="fusiona-inherit-import"></i>
						</label>
					</div>
				</div>
			</div>
			<!-- Images import -->
				<div class="awb-import-option awb-images-import">
				<label for=""><?php esc_html_e( 'Images', 'fusion-builder' ); ?></label>
				<div class="awb-option-button">
					<div class="awb-option-item">
						<input id="<?php echo esc_attr( $layout ); ?>_do-import-images" type="radio" name="<?php echo esc_attr( $layout ); ?>_images" value="do-import-images" <?php checked( $images, 'do-import-images' ); ?>>
						<label for="<?php echo esc_attr( $layout ); ?>_do-import-images" data-tooltip="<?php esc_attr_e( 'Import Images', 'fusion-builder' ); ?>">
							<i class="fusiona-import-images"></i>
						</label>
					</div>
					<div class="awb-option-item">
						<input id="<?php echo esc_attr( $layout ); ?>_dont-import-images" type="radio" name="<?php echo esc_attr( $layout ); ?>_images" value="dont-import-images" <?php checked( $images, 'dont-import-images' ); ?>>
						<label for="<?php echo esc_attr( $layout ); ?>_dont-import-images" data-tooltip="<?php esc_attr_e( 'Use Placeholders', 'fusion-builder' ); ?>">
							<i class="fusiona-replace-placeholder"></i>
						</label>
					</div>
				</div>
			</div>
			<!-- Invert option -->
			<div class="awb-import-option awb-import-inversion">
				<label for=""><?php esc_html_e( 'Colors', 'fusion-builder' ); ?></label>
				<div class="awb-option-button">
					<div class="awb-option-item">
						<input id="<?php echo esc_attr( $layout ); ?>_dont-invert" type="radio" name="<?php echo esc_attr( $layout ); ?>_invert" value="dont-invert" <?php checked( $invert, 'dont-invert' ); ?>>
						<label for="<?php echo esc_attr( $layout ); ?>_dont-invert" data-tooltip="<?php esc_attr_e( 'Normal', 'fusion-builder' ); ?>">
							<i class="fusiona-dont-invert"></i>
						</label>
					</div>
					<div class="awb-option-item">
						<input id="<?php echo esc_attr( $layout ); ?>_do-invert" type="radio" name="<?php echo esc_attr( $layout ); ?>_invert" value="do-invert"  <?php checked( $invert, 'do-invert' ); ?>>
						<label for="<?php echo esc_attr( $layout ); ?>_do-invert" data-tooltip="<?php esc_attr_e( 'Invert', 'fusion-builder' ); ?>">
							<i class="fusiona-do-invert"></i>
						</label>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Check if given content ( probaply header ) contain special menu and return the special menu type.
	 *
	 * @access public
	 * @since 7.7
	 * @param mixed $id The id.
	 * @param mixed $type The type.
	 */
	public function special_menu( $id, $type ) {
		$content = AWB_Studio_Import()->get_post_content( $id, $type );

		$pattern = get_shortcode_regex( [ 'fusion_menu' ] );
		preg_match( "/$pattern/", $content, $m );

		$attr = shortcode_parse_atts( $m[3] );

		$special_menu = '';

		if ( isset( $attr['class'] ) && 'avada-flyout-menu' === $attr['class'] ) {
			$special_menu = 'flyout';
		}

		if ( isset( $attr['class'] ) && 'avada-split-menu' === $attr['class'] ) {
			$special_menu = 'split';
		}

		return $special_menu;

	}

	/**
	 * Update fusion menu item meta.
	 *
	 * @access public
	 * @since 7.7
	 * @param mixed $id The Id.
	 * @param mixed $key The key.
	 * @param mixed $value The value.
	 */
	public function update_fusion_menu_item_meta( $id, $key, $value ) {
		$fusion_meta         = get_post_meta( $id, '_menu_item_fusion_megamenu', true );
		$fusion_meta[ $key ] = $value;

		return update_post_meta( $id, '_menu_item_fusion_megamenu', $fusion_meta );
	}
}

/**
 * Instantiates the Fusion_Template_Builder class.
 * Make sure the class is properly set-up.
 *
 * @since object 2.2
 * @return object Fusion_App
 */
function AWB_Setup_Wizard() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Setup_Wizard::get_instance();
}
AWB_Setup_Wizard();

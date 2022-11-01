<?php
/**
 * The metaboxes class.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * The Metaboxes class.
 */
class PyreThemeFrameworkMetaboxes {

	/**
	 * An instance of this object.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @var PyreThemeFrameworkMetaboxes
	 */
	public static $instance;

	/**
	 * The settings.
	 *
	 * @access public
	 * @var array
	 */
	public $data;

	/**
	 * The class constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		self::$instance = $this;
		$this->data     = Avada()->settings->get_all();

		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 11 );
		add_action( 'save_post', [ $this, 'save_meta_boxes' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_script_loader' ], 99 );
		add_filter( 'awb_metaboxes_sections', [ $this, 'filter_sections' ] );
		add_filter( 'awb_responsive_params', [ $this, 'add_responsive_params' ], 10, 3 );
	}

	/**
	 * Get value for a setting.
	 *
	 * @access protected
	 * @since 6.2.0
	 * @param string $id The option-ID.
	 * @return mixed
	 */
	protected function get_value( $id ) {
		global $post;

		$override_value = apply_filters( 'awb_po_get_value', null, $id );
		if ( null !== $override_value ) {
			return $override_value;
		}
		if ( ! $post ) {
			return '';
		}

		return fusion_data()->post_meta( $post->ID )->get( $id );
	}

	/**
	 * Format the option-name for use in our $_POST data.
	 *
	 * @access protected
	 * @since 6.2.0
	 * @param string $id The option-ID.
	 * @return string
	 */
	protected function format_option_name( $id ) {

		$override_name = apply_filters( 'awb_po_get_option_name', null, $id );
		if ( null !== $override_name ) {
			return $override_name;
		}

		if ( false !== strpos( $id, '[' ) ) {
			$parts = explode( '[', $id );
			return Fusion_Data_PostMeta::ROOT . '[' . $parts[0] . '][' . $parts[1];
		}
		return Fusion_Data_PostMeta::ROOT . '[' . $id . ']';
	}

	/**
	 * Load backend scripts.
	 *
	 * @access public
	 */
	public function admin_script_loader() {

		$screen = get_current_screen();
		if ( isset( $screen->post_type ) && in_array( $screen->post_type, apply_filters( 'avada_hide_page_options', [] ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
			return;
		}
		$theme_info = wp_get_theme();

		wp_enqueue_script(
			'jquery.biscuit',
			Avada::$template_dir_url . '/assets/admin/js/jquery.biscuit.js',
			[ 'jquery' ],
			$theme_info->get( 'Version' ),
			false
		);
		wp_register_script(
			'avada_upload',
			Avada::$template_dir_url . '/assets/admin/js/upload.js',
			[ 'jquery' ],
			$theme_info->get( 'Version' ),
			false
		);
		wp_enqueue_script( 'avada_upload' );
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'jquery-ui-button' );

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

		wp_enqueue_script(
			'icon-picker',
			Avada::$template_dir_url . '/assets/admin/js/icon-picker.js',
			[ 'jquery' ],
			$theme_info->get( 'Version' ),
			true
		);
		// Color fields.
		if ( function_exists( 'AWB_Global_Colors' ) ) {
			AWB_Global_Colors()->enqueue();
		}

		// General JS for fields.
		wp_enqueue_script(
			'avada-fusion-options',
			Avada::$template_dir_url . '/assets/admin/js/avada-fusion-options.js',
			[ 'jquery', 'jquery-ui-sortable' ],
			$theme_info->get( 'Version' ),
			false
		);

		do_action( 'avada_page_option_scripts', $screen->post_type );
	}

	/**
	 * Gets the tabs for post type.
	 *
	 * @access public
	 * @param string $posttype post type.
	 * @since 6.0
	 */
	public static function get_pagetype_tab( $posttype = false ) {
		$pagetype_data = [
			'page'              => [ 'page', 'header', 'sliders', 'pagetitlebar', 'content', 'sidebars', 'footer' ],
			'post'              => [ 'post', 'page', 'header', 'sliders', 'pagetitlebar', 'content', 'sidebars', 'footer' ],
			'avada_faq'         => [ 'post', 'page', 'header', 'sliders', 'pagetitlebar', 'content', 'sidebars', 'footer' ],
			'avada_portfolio'   => [ 'portfolio_post', 'page', 'header', 'sliders', 'pagetitlebar', 'content', 'sidebars', 'footer' ],
			'product'           => [ 'page', 'header', 'sliders', 'pagetitlebar', 'content', 'sidebars', 'footer' ],
			'tribe_events'      => [ 'page', 'header', 'sliders', 'pagetitlebar', 'content', 'sidebars', 'footer' ],
			'fusion_tb_section' => [ 'template', 'content', 'sidebars' ],
			'fusion_form'       => [ 'form_general', 'form_appearance', 'form_submission', 'form_notifications', 'form_confirmation', 'form_privacy' ],
			'awb_off_canvas'    => [ 'template', 'off_canvas_general', 'off_canvas_design', 'off_canvas_overlay', 'off_canvas_close', 'off_canvas_animation', 'off_canvas_conditions', 'off_canvas_triggers', 'off_canvas_rules' ],
			'fusion_element'    => [],
			'post_card'         => [ 'template' ],
		];

		if ( ! isset( $posttype ) || ! $posttype ) {
			$posttype = get_post_type();
		}

		// If editing a post card, treat separately.
		if ( fusion_is_post_card() ) {
			$posttype = 'post_card';
		}

		$pagetype_data = apply_filters( 'fusion_pagetype_data', $pagetype_data, $posttype );

		if ( isset( $pagetype_data[ $posttype ] ) ) {
			return $pagetype_data[ $posttype ];
		}
		return [ 'post', 'page', 'header', 'sliders', 'pagetitlebar', 'content', 'sidebars', 'footer' ];
	}

	/**
	 * Gets the options for page type.
	 *
	 * @access public
	 * @since 6.0
	 * @return array
	 */
	public function get_options() {
		if ( ! isset( $pagetype ) ) {
			$pagetype = get_post_type();
		}

		$tabs     = $this::get_pagetype_tab( $pagetype );
		$sections = [];

		if ( is_array( $tabs ) ) {
			foreach ( $tabs as $tab_name ) {
				$path = Avada::$template_dir_path . '/includes/metaboxes/tabs/tab_' . $tab_name . '.php';
				require_once wp_normalize_path( $path );
				if ( function_exists( 'avada_page_options_tab_' . $tab_name ) ) {
					$sections = call_user_func( 'avada_page_options_tab_' . $tab_name, $sections );
				}
			}
		}

		return apply_filters( 'awb_metaboxes_sections', $sections );
	}

	/**
	 * Adds the metaboxes.
	 *
	 * @access public
	 */
	public function add_meta_boxes() {
		$post_types = get_post_types(
			[
				'public' => true,
			]
		);

		// Libary is not public but post cards need PO.
		$post_types[] = 'fusion_element';
		$post_types   = apply_filters( 'awb_page_options_post_types', $post_types );

		$disallowed = [ 'page', 'post', 'attachment', 'avada_portfolio', 'themefusion_elastic', 'product', 'wpsc-product', 'slide', 'tribe_events', 'fusion_tb_section', 'fusion_form', 'fusion_element', 'fusion_template', 'awb_off_canvas' ];
		$disallowed = array_merge( $disallowed, apply_filters( 'avada_hide_page_options', [] ) );
		foreach ( $post_types as $post_type ) {
			if ( in_array( $post_type, $disallowed, true ) ) {
				continue;
			}
			$this->add_meta_box(
				'post_options',
				apply_filters( 'avada_page_options_metabox_title', esc_html__( 'Avada Options', 'Avada' ) ),
				$post_type
			);
		}

		$this->add_meta_box( 'post_options', esc_html__( 'Avada Page Options', 'Avada' ), 'avada_faq' );
		$this->add_meta_box( 'post_options', esc_html__( 'Avada Page Options', 'Avada' ), 'post' );
		$this->add_meta_box( 'page_options', esc_html__( 'Avada Page Options', 'Avada' ), 'page' );
		$this->add_meta_box( 'portfolio_options', esc_html__( 'Avada Page Options', 'Avada' ), 'avada_portfolio' );
		$this->add_meta_box( 'es_options', esc_html__( 'Elastic Slide Options', 'Avada' ), 'themefusion_elastic' );
		$this->add_meta_box( 'woocommerce_options', esc_html__( 'Avada Page Options', 'Avada' ), 'product' );
		$this->add_meta_box( 'slide_options', esc_html__( 'Slide Options', 'Avada' ), 'slide' );
		$this->add_meta_box( 'events_calendar_options', esc_html__( 'Events Calendar Options', 'Avada' ), 'tribe_events' );
		$this->add_meta_box( 'fusion_tb_section', esc_html__( 'Layout Section Options', 'Avada' ), 'fusion_tb_section' );
		$this->add_meta_box( 'fusion_form', esc_html__( 'Form Options', 'Avada' ), 'fusion_form' );
		$this->add_meta_box( 'awb_off_canvas', esc_html__( 'Off Canvas Options', 'Avada' ), 'awb_off_canvas' );
		$this->add_meta_box( 'fusion_element', esc_html__( 'Avada Page Options', 'Avada' ), 'fusion_element' );

		if ( class_exists( 'Avada_Studio' ) ) {
			$this->add_meta_box( 'fusion_template', esc_html__( 'Avada Page Options', 'Avada' ), 'fusion_template' );
		}
	}

	/**
	 * Adds a metabox.
	 *
	 * @access public
	 * @param string $id        The metabox ID.
	 * @param string $label     The metabox label.
	 * @param string $post_type The post-type.
	 */
	public function add_meta_box( $id, $label, $post_type ) {
		if ( 'fusion_element' === $post_type && ! fusion_is_post_card() && ! class_exists( 'Avada_Studio' ) ) {
			return;
		}
		$label = '<span class="avada-logo-wrapper"><span class="avada-logo fusiona-avada-logo"></span><span class="avada-option-title">' . esc_html( $label ) . '</span></span>';

		add_meta_box( 'pyre_' . $id, $label, [ $this, $id ], $post_type, 'advanced', 'high' );
	}

	/**
	 * Saves the metaboxes.
	 *
	 * @access public
	 * @param string|int $post_id The post ID.
	 */
	public function save_meta_boxes( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( isset( $_POST[ Fusion_Data_PostMeta::ROOT ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$fusion_meta = $this->sanitize( $_POST[ Fusion_Data_PostMeta::ROOT ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification

			foreach ( $fusion_meta as $key => $val ) {

				if ( 0 === strpos( $key, '_' ) ) {
					$fusion_meta[ ltrim( $key, '_' ) ] = $val;
					unset( $fusion_meta[ $key ] );
				}

				if ( '' === $val || 'default' === $val || ( is_array( $val ) && isset( $val['url'] ) && empty( $val['url'] ) ) ) {
					unset( $fusion_meta[ $key ] );
				}

				if ( empty( $val ) ) {
					unset( $fusion_meta[ $key ] );
				}
			}
			update_post_meta( $post_id, Fusion_Data_PostMeta::ROOT, $fusion_meta );
		}
	}

	/**
	 * Handle rendering options for pages.
	 *
	 * @access public
	 */
	public function page_options() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'page' ) );
	}

	/**
	 * Sanitize post meta.
	 *
	 * @param mixed $meta_array The value.
	 * @access public
	 */
	public function sanitize( $meta_array ) {
		return map_deep( $meta_array, [ $this, 'sanitize_strings_only' ] );
	}

	/**
	 * Sanitize stings_only.
	 *
	 * @param mixed $value The value.
	 * @access public
	 */
	public function sanitize_strings_only( $value ) {
		if ( is_string( $value ) ) {
			if ( current_user_can( 'unfiltered_html' ) ) {
				return $value;
			} else {
				return wp_kses_post( $value );
			}
		} else {
			return $value;
		}
	}

	/**
	 * Handle rendering options for posts.
	 *
	 * @access public
	 */
	public function post_options() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'post' ) );
	}

	/**
	 * Handle rendering options for portfolios.
	 *
	 * @access public
	 */
	public function portfolio_options() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'avada_portfolio' ) );
	}

	/**
	 * Handle rendering options for woocommerce.
	 *
	 * @access public
	 */
	public function woocommerce_options() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'product' ), 'product' );
	}

	/**
	 * Handle rendering options for ES.
	 *
	 * @access public
	 */
	public function es_options() {
		include 'options/options_es.php';
	}

	/**
	 * Handle rendering options for slides.
	 *
	 * @access public
	 */
	public function slide_options() {
		include 'options/options_slide.php';
	}

	/**
	 * Handle rendering options for events.
	 *
	 * @access public
	 */
	public function events_calendar_options() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'tribe_events' ) );
	}

	/**
	 * Handle rendering options for events.
	 *
	 * @access public
	 */
	public function fusion_tb_section() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'fusion_tb_section' ) );
	}

	/**
	 * Handle rendering options for events.
	 *
	 * @access public
	 */
	public function fusion_form() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'fusion_form' ) );
	}

	/**
	 * Handle rendering options for fusion templates.
	 *
	 * @access public
	 */
	public function fusion_template() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'fusion_template' ) );
	}

	/**
	 * Handle rendering options for fusion elements.
	 *
	 * @access public
	 */
	public function fusion_element() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'fusion_element' ) );
	}

	/**
	 * Handle rendering options for events.
	 *
	 * @access public
	 */
	public function awb_off_canvas() {
		$this->render_option_tabs( $this::get_pagetype_tab( 'awb_off_canvas' ) );
	}

	/**
	 * Render fields within tab.
	 *
	 * @access public
	 * @param array  $tab_data The tab map.
	 * @param string $repeater Used for repeater fields.
	 * @since 6.0
	 */
	public function render_tab_fields( $tab_data, $repeater = false ) {
		if ( ! is_array( $tab_data ) ) {
			return;
		}

		foreach ( $tab_data['fields'] as $field ) {
			// Defaults.
			$field['id']            = isset( $field['id'] ) ? $field['id'] : '';
			$field['label']         = isset( $field['label'] ) ? $field['label'] : '';
			$field['choices']       = isset( $field['choices'] ) ? $field['choices'] : [];
			$field['description']   = isset( $field['description'] ) ? $field['description'] : '';
			$field['default']       = isset( $field['default'] ) ? $field['default'] : '';
			$field['dependency']    = isset( $field['dependency'] ) ? $field['dependency'] : [];
			$field['ajax']          = isset( $field['ajax'] ) ? $field['ajax'] : false;
			$field['ajax_params']   = isset( $field['ajax_params'] ) ? $field['ajax_params'] : false;
			$field['max_input']     = isset( $field['max_input'] ) ? $field['max_input'] : 1000;
			$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : 1000;
			$field['responsive']    = isset( $field['responsive'] ) ? $field['responsive']['state'] : false;
			$field['icons']         = isset( $field['icons'] ) ? $field['icons'] : [];
			$field['allow_globals'] = ( isset( $field['allow_globals'] ) && ( false === $field['allow_globals'] ) ? false : true );
			$field['state']         = isset( $field['state'] ) ? $field['state'] : '';
			$field['row_title']     = isset( $field['row_title'] ) ? $field['row_title'] : '';

			switch ( $field['type'] ) {
				case 'radio-buttonset':
					$this->radio_buttonset( $field['id'], $field['label'], $field['choices'], $field['description'], $field['default'], $field['dependency'], null, $field['responsive'], $field['icons'] );
					break;
				case 'checkbox-buttonset':
					$this->checkbox_buttonset( $field['id'], $field['label'], $field['value'], $field['description'], $field['default'], $field['dependency'], $field['responsive'] );
					break;
				case 'color-alpha':
					$this->color( $field['id'], $field['label'], $field['description'], true, $field['dependency'], $field['default'], $field['responsive'], $field['allow_globals'] );
					break;
				case 'color':
					$this->color( $field['id'], $field['label'], $field['description'], false, $field['dependency'], $field['default'], $field['responsive'], $field['allow_globals'] );
					break;
				case 'typography':
					$this->typography( $field['id'], $field['label'], $field['description'] );
					break;
				case 'media':
				case 'media_url':
				case 'upload':
					$this->upload( $field['id'], $field['label'], $field['description'], $field['dependency'], $field['responsive'] );
					break;
				case 'ajax_select':
				case 'multiple_select':
					$this->multiple( $field['id'], $field['label'], $field['choices'], $field['description'], $field['dependency'], $field['ajax'], $field['ajax_params'], $field['max_input'], $field['placeholder'], $repeater, null, $field['responsive'], $field['default'] );
					break;
				case 'select':
					$this->select( $field['id'], $field['label'], $field['choices'], $field['description'], $field['default'], $field['dependency'], $repeater, $field['responsive'] );
					break;
				case 'dimensions':
					$this->dimension( $field['id'], $field['value'], $field['label'], $field['description'], $field['dependency'], $field['responsive'] );
					break;
				case 'text':
					$this->text( $field['id'], $field['label'], $field['description'], $field['dependency'], $field['responsive'] );
					break;
				case 'textarea':
					$this->textarea( $field['id'], $field['label'], $field['description'], $field['default'], $field['dependency'], $field['responsive'] );
					break;
				case 'custom':
					$this->raw( $field['id'], $field['label'], $field['description'], $field['dependency'], $field['responsive'] );
					break;
				case 'hidden':
					$this->hidden( $field['id'], $field['default'] );
					break;
				case 'slider':
					$this->range( $field['id'], $field['label'], $field['description'], $field['choices']['min'], $field['choices']['max'], $field['choices']['step'], $field['default'], '', $field['dependency'], null, $field['responsive'] );
					break;
				case 'sortable':
					$this->sortable( $field['id'], $field['label'], $field['choices'], $field['description'], $field['dependency'], $field['default'], $field['responsive'] );
					break;
				case 'layout_conditions':
					$this->layout_conditions( $field['id'], $field['label'], $field['description'], $field['dependency'], $field['responsive'] );
					break;
				case 'hubspot_map':
					$this->hubspot_map( $field['id'], $field['label'], $field['choices'], $field['description'], $field['dependency'], $field['default'], $field['responsive'] );
					break;
				case 'mailchimp_map':
					$this->mailchimp_map( $field['id'], $field['label'], $field['choices'], $field['description'], $field['dependency'], $field['default'], $field['responsive'] );
					break;
				case 'repeater':
					$labels = [
						'row_add'   => $field['row_add'],
						'row_title' => $field['row_title'],
					];
					$this->repeater( $field['id'], $field['label'], $field['description'], $field['dependency'], $field['fields'], $field['bind_title'], $labels, $field['responsive'], $field['default'] );
					break;
				case 'toggle':
				case 'group':
					$this->toggle( $field['id'], $field['label'], $field['description'], $field['dependency'], $field['fields'], $field['responsive'], $field['row_title'], $field['state'] );
					break;
				case 'iconpicker':
					$this->iconpicker( $field['id'], $field['label'], $field['description'], $field['dependency'], $field['responsive'] );
					break;
			}
		}
	}

	/**
	 * Handle rendering options.
	 *
	 * @access public
	 * @param array  $requested_tabs The requested tabs.
	 * @param string $post_type      The post-type.
	 */
	public function render_option_tabs( $requested_tabs, $post_type = 'default' ) {
		$screen = get_current_screen();

		$preview_types = [ 'fusion_element', 'awb_off_canvas' ];
		$tabs_names    = [
			'sliders'               => esc_html__( 'Sliders', 'Avada' ),
			'page'                  => esc_html__( 'Layout', 'Avada' ),
			'post'                  => 'avada_faq' === $screen->post_type ? esc_html__( 'FAQ', 'Avada' ) : esc_html__( 'Post', 'Avada' ),
			'header'                => esc_html__( 'Header', 'Avada' ),
			'content'               => esc_html__( 'Content', 'Avada' ),
			'sidebars'              => esc_html__( 'Sidebars', 'Avada' ),
			'pagetitlebar'          => esc_html__( 'Page Title Bar', 'Avada' ),
			'portfolio_post'        => esc_html__( 'Portfolio', 'Avada' ),
			'product'               => esc_html__( 'Product', 'Avada' ),
			'template'              => in_array( $screen->post_type, $preview_types, true ) ? esc_html__( 'Preview', 'Avada' ) : esc_html__( 'Layout Section', 'Avada' ),
			'form_general'          => esc_html__( 'General', 'Avada' ),
			'form_submission'       => esc_html__( 'Submission', 'Avada' ),
			'form_notifications'    => esc_html__( 'Notifications', 'Avada' ),
			'form_confirmation'     => esc_html__( 'Confirmation', 'Avada' ),
			'form_appearance'       => esc_html__( 'Appearance', 'Avada' ),
			'form_privacy'          => esc_html__( 'Privacy', 'Avada' ),
			'footer'                => esc_html__( 'Footer', 'Avada' ),
			'studio'                => esc_html__( 'Studio', 'Avada' ),
			'studio_tools'          => esc_html__( 'Studio Tools', 'Avada' ),
			'off_canvas_general'    => esc_html__( 'General', 'Avada' ),
			'off_canvas_design'     => esc_html__( 'Design', 'Avada' ),
			'off_canvas_overlay'    => esc_html__( 'Overlay', 'Avada' ),
			'off_canvas_close'      => esc_html__( 'Close', 'Avada' ),
			'off_canvas_animation'  => esc_html__( 'Animation', 'Avada' ),
			'off_canvas_conditions' => esc_html__( 'Conditions', 'Avada' ),
			'off_canvas_triggers'   => esc_html__( 'Triggers', 'Avada' ),
			'off_canvas_rules'      => esc_html__( 'Rules', 'Avada' ),
		];

		$tabs = [
			'requested_tabs' => $requested_tabs,
			'tabs_names'     => $tabs_names,
			'tabs_path'      => [],
		];

		$tabs = apply_filters( 'avada_metabox_tabs', $tabs, $post_type );
		?>

		<ul class="pyre_metabox_tabs">

			<?php foreach ( $tabs['requested_tabs'] as $key => $tab_name ) : ?>
				<?php $class_active = ( 0 === $key ) ? 'active' : ''; ?>
				<?php if ( 'page' === $tab_name && 'product' === $post_type ) : ?>
					<li class="<?php echo esc_attr( $class_active ); ?>"><a href="<?php echo esc_attr( $tab_name ); ?>"><?php echo esc_attr( $tabs['tabs_names'][ $post_type ] ); ?></a></li>
				<?php else : ?>
					<li class="<?php echo esc_attr( $class_active ); ?>"><a href="<?php echo esc_attr( $tab_name ); ?>"><?php echo esc_attr( $tabs['tabs_names'][ $tab_name ] ); ?></a></li>
				<?php endif; ?>
			<?php endforeach; ?>

		</ul>

		<div class="pyre_metabox">

			<?php foreach ( $tabs['requested_tabs'] as $key => $tab_name ) : ?>
				<div class="pyre_metabox_tab" id="pyre_tab_<?php echo esc_attr( $tab_name ); ?>">
				<?php
				$path = ! empty( $tabs['tabs_path'][ $tab_name ] ) ? $tabs['tabs_path'][ $tab_name ] : dirname( __FILE__ ) . '/tabs/tab_' . $tab_name . '.php';
				if ( $path && file_exists( wp_normalize_path( $path ) ) ) {
					require_once wp_normalize_path( $path );
				}
				if ( function_exists( 'avada_page_options_tab_' . $tab_name ) ) {
					$tab_data = call_user_func( 'avada_page_options_tab_' . $tab_name, [] );
					$tab_data = apply_filters( 'awb_metaboxes_sections', $tab_data );

					if ( isset( $tab_data[ $tab_name ]['responsive'] ) && true === $tab_data[ $tab_name ]['responsive'] ) {
						?>
						<ul class="fusion-viewport-indicator">
							<li class="fusion-viewport-text">
								<?php esc_html_e( 'responsive', 'fusion-builder' ); ?>
							</li>
							<li data-viewport="fusion-small">
								<a  href="JavaScript:void(0);">
									<i class="fusiona-mobile"></i>
								</a>
							</li>
							<li data-viewport="fusion-medium">
								<a href="JavaScript:void(0);">
									<i class="fusiona-tablet"></i>
								</a>
							</li>
							<li data-viewport="fusion-large" class="active">
								<a href="JavaScript:void(0);">
									<i class="fusiona-desktop"></i>
								</a>
							</li>
						</ul>
						<?php
					}

					$this->render_tab_fields( $tab_data[ $tab_name ], false );
				}
				?>
				</div>
			<?php endforeach; ?>

		</div>

		<div class="clear"></div>
		<div id="metaboxes-new-app"></div>
		<?php

	}

	/**
	 * Text controls.
	 *
	 * @access public
	 * @param string $id         The ID.
	 * @param string $label      The label.
	 * @param string $desc       The description.
	 * @param array  $dependency The dependencies array.
	 * @param mixed  $responsive The responsive param data.
	 */
	public function text( $id, $label, $desc = '', $dependency = [], $responsive = false ) {
		global $post;
		?>

		<div class="pyre_metabox_field<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<input type="text" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $this->get_value( $id ) ); ?>" />
			</div>
		</div>
		<?php

	}

	/**
	 * Layout Conditions.
	 *
	 * @access public
	 * @param string $id         The ID.
	 * @param string $label      The label.
	 * @param string $desc       The description.
	 * @param array  $dependency The dependencies array.
	 * @param mixed  $responsive The responsive param data.
	 */
	public function layout_conditions( $id, $label, $desc = '', $dependency = [], $responsive = false ) {
		$value          = $this->get_value( $id );
		$conditions     = json_decode( $value );
		$has_conditions = is_object( $conditions ) && count( (array) $conditions ) ? true : false;
		?>
		<div class="pyre_metabox_field<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<span class="awb-off-canvas-conditions-constoller <?php echo $has_conditions ? 'has-conditions' : ''; ?>">
					<span class="awb-conditions">
						<ul>
							<li class="no-condition-select"><?php echo __( 'Display on Entire Site', 'fusion-builder' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php
						if ( $has_conditions ) {
							foreach ( $conditions as $condition ) {
								?>
								<li class="<?php echo $condition->mode; // phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo $condition->label; // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
								<?php
							}
						}
						?>
					</ul>
				</span>
			</span>
			<div class="awb-manage-conditions-wrapper">
				<a href="#" id="awb-manage-conditions" class="button button-primary awb-manage-conditions"><i class="fusiona-cog"></i> <?php echo __( 'Manage Conditions', 'fusion-builder' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			</div>
				<?php
				if ( is_array( $value ) ) {
					$value = wp_json_encode( $value );
				}
				?>
				<input type="hidden" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $value ); ?>" class="awb-conditions-value">
				<input type="hidden" id="layout-conditions-nonce" value="<?php echo wp_create_nonce( 'fusion_tb_new_layout' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>">
			</div>
		</div>
		<?php

	}

	/**
	 * Select controls.
	 *
	 * @access public
	 * @param string $id         The ID.
	 * @param string $label      The label.
	 * @param array  $options    The options array.
	 * @param string $desc       The description.
	 * @param string $default    The default value..
	 * @param array  $dependency The dependencies array.
	 * @param string $repeater   Used for repeater fields.
	 * @param mixed  $responsive The responsive param data.
	 */
	public function select( $id, $label, $options, $desc = '', $default = '', $dependency = [], $repeater = false, $responsive = false ) {
		global $post;
		$repeater = $repeater ? 'repeater' : '';
		$db_value = $this->get_value( $id );
		$default  = $this->is_meta_data_saved_in_db() ? '' : $default;
		$value    = $db_value ? $db_value : $default;

		?>

		<div class="pyre_metabox_field<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<select id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( "{$repeater}_{$id}" ) ); ?>" style="width:100%">
					<?php foreach ( $options as $key => $option ) : ?>
						<option <?php echo ( (string) $value === (string) $key ) ? 'selected="selected"' : ''; ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $option ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php

	}

	/**
	 * Color picker field.
	 *
	 * @access public
	 * @since 5.0.0
	 * @param string  $id         ID of input field.
	 * @param string  $label      Label of field.
	 * @param string  $desc       Description of field.
	 * @param boolean $alpha      Whether or not to show alpha.
	 * @param array   $dependency The dependencies array.
	 * @param string  $default    Default value from TO.
	 * @param mixed   $responsive The responsive param data.
	 * @param mixed   $allow_globals Whether to allow globals or not.
	 */
	public function color( $id, $label, $desc = '', $alpha = false, $dependency = [], $default = '', $responsive = false, $allow_globals = true ) {
		global $post;
		$styling_class = ( $alpha ) ? 'colorpickeralpha' : 'colorpicker';

		if ( $default ) {
			if ( ! $alpha && ( 'transparent' === $default || ! is_string( $default ) ) ) {
				$default = '#ffffff';
			}
			$desc .= '  <span class="pyre-default-reset"><a href="#" id="default-' . $id . '" class="fusion-range-default fusion-hide-from-atts" type="radio" name="' . $id . '" value="" data-default="' . $default . '">' . esc_attr__( 'Reset to default.', 'Avada' ) . '</a><span>' . esc_attr__( 'Using default value.', 'Avada' ) . '</span></span>';
		}
		?>

		<div class="pyre_metabox_field<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field avada-color <?php echo esc_attr( $styling_class ); ?>">
				<input id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" class="fusion-builder-color-picker-hex color-picker" type="text" value="<?php echo esc_attr( $this->get_value( $id ) ); ?>" <?php echo ( $alpha ) ? 'data-alpha="true"' : ''; ?> <?php echo ( $default ) ? 'data-default="' . esc_attr( $default ) . '"' : ''; ?> <?php echo ( false === $allow_globals ? 'data-globals="false"' : '' ); ?> />
			</div>
		</div>
		<?php

	}

	/**
	 * Typography field.
	 *
	 * @since 7.7
	 * @param string $id         ID of input field.
	 * @param string $label      Label of field.
	 * @param string $desc       Description of field.
	 */
	public function typography( $id, $label, $desc ) {
		$setting = $this->get_value( $id );
		if ( ! is_array( $setting ) ) {
			$setting = [];
		}

		// The hidden input fields approach is used only in Avada Studio by designers.
		if ( class_exists( 'Avada_Studio' ) ) {
			?>
			<div class="pyre_metabox_field">
				<div class="pyre_desc">
					<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
					<?php if ( $desc ) : ?>
						<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<?php endif; ?>
				</div>
				<div class="pyre_field">
					<p><?php echo esc_html__( 'Please use live-editor to change this setting.', 'avada-studio' ); ?></p>
					<?php foreach ( $setting as $setting_id => $value ) : ?>
						<input type="hidden" name="<?php echo esc_attr( $this->format_option_name( $id . '[' . $setting_id . ']' ) ); ?>" value="<?php echo esc_attr( $value ); ?>"></input>
					<?php endforeach; ?>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Range field.
	 *
	 * @since 5.0.0
	 * @param string           $id         ID of input field.
	 * @param string           $label      Label of field.
	 * @param string           $desc       The description.
	 * @param string|int|float $min        The minimum value.
	 * @param string|int|float $max        The maximum value.
	 * @param string|int|float $step       The steps value.
	 * @param string|int|float $default    The default value.
	 * @param string|int|float $value      The value.
	 * @param array            $dependency The dependencies array.
	 * @param mixed            $recommendation The recommended value.
	 * @param mixed            $responsive The responsive param data.
	 */
	public function range( $id, $label, $desc = '', $min = 0, $max = 0, $step = 1, $default = 0, $value = '', $dependency = [], $recommendation = null, $responsive = false ) {
		global $post;
		if ( isset( $default ) && '' !== $default ) {
			$desc .= '  <span class="pyre-default-reset"><a href="#" id="default-' . $id . '" class="fusion-range-default fusion-hide-from-atts" type="radio" name="' . $id . '" value="" data-default="' . $default . '">' . esc_attr__( 'Reset to default.', 'Avada' ) . '</a><span>' . esc_attr__( 'Using default value.', 'Avada' ) . '</span></span>';
		}
		?>

		<div class="pyre_metabox_field<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"<?php echo ( null === $recommendation ? '' : ' data-recommendation="' . esc_attr( $recommendation ) . '"' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field avada-range">
				<?php
					$default_status = ( ( $default ) ? 'fusion-with-default' : '' );
					$is_checked     = ( '' == $this->get_value( $id ) ); // phpcs:ignore WordPress.PHP.StrictComparisons
					$regular_id     = ( ( '' != $this->get_value( $id ) ) ? $id : 'slider' . $id ); // phpcs:ignore WordPress.PHP.StrictComparisons
					$display_value  = ( ( '' == $this->get_value( $id ) ) ? $default : $this->get_value( $id ) ); // phpcs:ignore WordPress.PHP.StrictComparisons
				?>
				<input
					type="text"
					name="<?php echo esc_attr( $id ); ?>"
					id="<?php echo esc_attr( $regular_id ); ?>"
					value="<?php echo esc_attr( $display_value ); ?>"
					class="fusion-slider-input <?php echo esc_attr( $default_status ); ?> <?php echo ( isset( $default ) && '' !== $default ) ? 'fusion-hide-from-atts' : ''; ?>" />
				<div
					class="fusion-slider-container"
					data-id="<?php echo esc_attr( $id ); ?>"
					data-min="<?php echo esc_attr( $min ); ?>"
					data-max="<?php echo esc_attr( $max ); ?>"
					data-step="<?php echo esc_attr( $step ); ?>">
				</div>
				<?php if ( isset( $default ) && '' !== $default ) : ?>
					<input
						type="hidden"
						id="pyre_<?php echo esc_attr( $id ); ?>"
						name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>"
						value="<?php echo esc_attr( $this->get_value( $id ) ); ?>"
						class="fusion-hidden-value" />
				<?php endif; ?>

			</div>
		</div>
		<?php

	}

	/**
	 * Radio button set field.
	 *
	 * @since 5.0.0
	 * @param string           $id             ID of input field.
	 * @param string           $label          Label of field.
	 * @param array            $options        Options to select from.
	 * @param string           $desc           Description of field.
	 * @param string|int|float $default        The default value.
	 * @param array            $dependency     The dependencies array.
	 * @param mixed            $recommendation The recommended value.
	 * @param mixed            $responsive     The responsive param data.
	 * @param array            $icons          List of icons.
	 */
	public function radio_buttonset( $id, $label, $options, $desc = '', $default = '', $dependency = [], $recommendation = null, $responsive = false, $icons = [] ) {
		global $post;
		$options_reset = $options;
		reset( $options_reset );

		if ( '' === $default ) {
			$default = key( $options_reset );
		}

		$value = ( '' == $this->get_value( $id ) ) ? $default : $this->get_value( $id ); // phpcs:ignore WordPress.PHP.StrictComparisons
		?>

		<div class="pyre_metabox_field<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"<?php echo ( null === $recommendation ? '' : ' data-recommendation="' . esc_attr( $recommendation ) . '"' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field avada-buttonset radio">
				<div class="fusion-form-radio-button-set ui-buttonset">
					<input type="hidden" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $value ); ?>" class="button-set-value" />
					<?php
					foreach ( $options as $key => $option ) :
						$classes  = '';
						$classes .= ( $key == $value ) ? ' ui-state-active' : ''; // phpcs:ignore WordPress.PHP.StrictComparisons
						$content  = isset( $icons[ $key ] ) && ! empty( $icons[ $key ] ) ? $icons[ $key ] : esc_attr( $option );
						$tooltip  = isset( $icons[ $key ] ) && ! empty( $icons[ $key ] ) ? $option : '';
						$classes .= '' !== $tooltip ? ' has-tooltip' : '';

						?>
						<a href="#" class="ui-button buttonset-item<?php echo esc_attr( $classes ); ?>" aria-label="<?php echo esc_attr( $tooltip ); ?>" data-value="<?php echo esc_attr( $key ); ?>"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php

	}

	/**
	 * Checkbox button set field.
	 *
	 * @since 5.0.0
	 * @param string           $id         ID of input field.
	 * @param string           $label      Label of field.
	 * @param array            $options    Options to select from.
	 * @param string           $desc       Description of field.
	 * @param string|int|float $default    The default value.
	 * @param array            $dependency The dependencies array.
	 * @param mixed            $responsive The responsive param data.
	 */
	public function checkbox_buttonset( $id, $label, $options, $desc = '', $default = '', $dependency = [], $responsive = false ) {
		global $post;
		$options_reset = $options;

		reset( $options_reset );

		if ( '' === $default ) {
			$default = key( $options_reset );
		}

		$value = ( '' == $this->get_value( $id ) ) ? $default : $this->get_value( $id ); // phpcs:ignore WordPress.PHP.StrictComparisons
		$value = ! is_array( $value ) ? explode( ',', $value ) : $value;
		?>

		<div class="pyre_metabox_field<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field avada-buttonset checkbox">
				<div class="fusion-form-checkbox-button-set ui-buttonset">
					<input type="hidden" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( implode( ',', $value ) ); ?>" class="button-set-value" />
					<?php foreach ( $options as $key => $option ) : ?>
						<?php $selected = in_array( $key, $value, true ) ? ' ui-state-active' : ''; ?>
						<a href="#" class="ui-button buttonset-item<?php echo esc_attr( $selected ); ?>" data-value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $option ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php

	}

	/**
	 * Dimensions field.
	 *
	 * @since 5.0.0
	 * @param array  $main_id    Overall option ID.
	 * @param array  $ids        IDs of input fields.
	 * @param string $label      Label of field.
	 * @param string $desc       Description of field.
	 * @param array  $dependency The dependencies array.
	 * @param mixed  $responsive The responsive param data.
	 */
	public function dimension( $main_id, $ids, $label, $desc = '', $dependency = [], $responsive = false ) {
		global $post;
		?>

		<div class="pyre_metabox_field<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( array_key_first( $ids ) ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput, PHPCompatibility.FunctionUse.NewFunctions.array_key_firstFound ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field avada-dimension">
				<?php foreach ( $ids as $field_id => $default ) : ?>
					<?php
					$display_value = $this->get_value( "{$main_id}[{$field_id}]" );
					$display_value = ( ( '' == $display_value ) ? $default : $display_value ); // phpcs:ignore WordPress.PHP.StrictComparisons
					$icon_class    = 'fusiona-expand width';
					if ( false !== strpos( $field_id, 'height' ) || false !== strpos( $field_id, 'vertical' ) ) {
						$icon_class = 'fusiona-expand  height';
					}
					if ( false !== strpos( $field_id, 'top' ) ) {
						$icon_class = 'dashicons dashicons-arrow-up-alt';
					}
					if ( false !== strpos( $field_id, 'right' ) ) {
						$icon_class = 'dashicons dashicons-arrow-right-alt';
					}
					if ( false !== strpos( $field_id, 'bottom' ) ) {
						$icon_class = 'dashicons dashicons-arrow-down-alt';
					}
					if ( false !== strpos( $field_id, 'left' ) ) {
						$icon_class = 'dashicons dashicons-arrow-left-alt';
					}

					// Border radius icons.
					if ( false !== strpos( $field_id, 'top_left' ) ) {
						$icon_class = 'dashicons dashicons-arrow-up-alt is-top-left';
					}
					if ( false !== strpos( $field_id, 'top_right' ) ) {
						$icon_class = 'dashicons dashicons-arrow-right-alt is-top-right';
					}
					if ( false !== strpos( $field_id, 'bottom_left' ) ) {
						$icon_class = 'dashicons dashicons-arrow-down-alt is-bottom-left';
					}
					if ( false !== strpos( $field_id, 'bottom_right' ) ) {
						$icon_class = 'dashicons dashicons-arrow-left-alt is-bottom-right';
					}

					?>
					<div class="fusion-builder-dimension">
						<span class="add-on"><i class="<?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></i></span>
						<input type="text" name="<?php echo esc_attr( $this->format_option_name( "{$main_id}[{$field_id}]" ) ); ?>" id="pyre_<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( $display_value ); ?>" />
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php

	}

	/**
	 * Multiselect field.
	 *
	 * @param array  $id          IDs of input fields.
	 * @param string $label       Label of field.
	 * @param array  $options     The options to choose from.
	 * @param string $desc        Description of field.
	 * @param array  $dependency  The dependencies array.
	 * @param mixed  $ajax        Ajax callback name if required.
	 * @param array  $ajax_params An array of our AJAX parameters.
	 * @param int    $max_input   Used as an attribute - defines the maximum number of inputs in a select field.
	 * @param string $placeholder The placeholder for our select field.
	 * @param string $repeater    Used for repeater fields.
	 * @param mixed  $recommendation The recommended value.
	 * @param mixed  $responsive     The responsive param data.
	 * @param mixed  $default      The default value.
	 */
	public function multiple( $id, $label, $options, $desc = '', $dependency = [], $ajax = false, $ajax_params = [], $max_input = 1000, $placeholder = '', $repeater = false, $recommendation = null, $responsive = false, $default = [] ) {
		global $post;
		$repeater = $repeater ? 'repeater' : '';
		$value    = '' === $this->get_value( $id ) ? $default : $this->get_value( $id );
		?>

		<div class="pyre_metabox_field<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"<?php echo ( null === $recommendation ? '' : ' data-recommendation="' . esc_attr( $recommendation ) . '"' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<?php if ( $ajax ) : ?>
					<input type="hidden" value="<?php echo esc_attr( wp_json_encode( $ajax_params ) ); ?>" class="params" />
					<input type="hidden" value="<?php echo esc_attr( wp_json_encode( $value ) ); ?>" class="initial-values" />
					<select multiple="multiple" data-max-input="<?php echo esc_attr( $max_input ); ?>" data-placeholder="<?php echo esc_attr( $placeholder ); ?>" <?php echo 'data-ajax="' . esc_attr( $ajax ) . '"'; ?>id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( "{$repeater}_{$id}[]" ) ); ?>">
					</select>
				<?php else : ?>
					<select multiple="multiple" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( "{$repeater}_{$id}[]" ) ); ?>" style="width:100%;">
						<?php foreach ( $options as $key => $option ) : ?>
							<?php $selected = ( is_array( $value ) && in_array( $key, $value ) ) ? 'selected="selected"' : ''; // phpcs:ignore WordPress.PHP.StrictInArray ?>
							<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $option ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
			</div>
		</div>
		<?php

	}

	/**
	 * Textarea field.
	 *
	 * @param array  $id         IDs of input fields.
	 * @param string $label      Label of field.
	 * @param string $desc       Description of field.
	 * @param string $default    The default value.
	 * @param array  $dependency The dependencies array.
	 * @param mixed  $responsive The responsive param data.
	 */
	public function textarea( $id, $label, $desc = '', $default = '', $dependency = [], $responsive = false ) {
		global $post;

		$db_value = $this->get_value( $id );
		$default  = $this->is_meta_data_saved_in_db() ? '' : $default;
		$value    = $db_value ? $db_value : $default;
		$rows     = 10;
		if ( 'heading' === $id || 'caption' === $id ) {
			$rows = 5;
		} elseif ( 'page_title_custom_text' === $id || 'page_title_custom_subheader' === $id ) {
			$rows = 1;
		}
		?>

		<div class="pyre_metabox_field<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<textarea cols="120" rows="<?php echo (int) $rows; ?>" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
			</div>
		</div>
		<?php

	}

	/**
	 * Upload field.
	 *
	 * @param array  $id         IDs of input fields.
	 * @param string $label      Label of field.
	 * @param string $desc       Description of field.
	 * @param array  $dependency The dependencies array.
	 * @param mixed  $responsive The responsive param data.
	 * @param mixed  $style      The style.
	 */
	public function upload( $id, $label, $desc = '', $dependency = [], $responsive = false, $style = '' ) {
		global $post;
		?>

		<div class="pyre_metabox_field<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<div class="pyre_upload">
					<?php
					$image_url = $this->get_value( $id . '[url]' );
					if ( ! $image_url && $this->get_value( $id ) ) {
						$image_url = $this->get_value( $id );
					}

					$image_id = $this->get_value( $id . '[id]' );

					if ( ! $image_id && $image_url ) {
						$image_id = Fusion_Images::get_attachment_id_from_url( $image_url );
					}

					?>
					<?php if ( 'plus' === $style ) { ?>
						<div class="upload-plus-style fusion-featured-image-meta-box">
							<p class="hide-if-no-js">
								<a href="#" id="<?php echo esc_attr( $image_id ); ?>" class="fusion_upload_button">
									<span class="fusion-set-featured-image upload-plus-style-placeholder" style="<?php echo ( ! $image_id ) ? '' : 'display:none;'; ?>">
										<span class="button-icon fusiona-plus"></span>
									</span>

									<?php if ( $image_id ) : ?>
										<?php
										echo wp_get_attachment_image(
											$image_id,
											[ 266, 266 ],
											false,
											[
												'class' => 'fusion-preview-image',
											]
										);
										?>
									<?php else : ?>
										<img class="fusion-preview-image" src="" style="display:none;">
									<?php endif; ?>
								</a>
								<input name="<?php echo esc_attr( $this->format_option_name( $id . '[url]' ) ); ?>" class="upload_field" id="pyre_<?php echo esc_attr( $id ); ?>" type="hidden" value="<?php echo esc_attr( $image_url ); ?>" />
								<input name="<?php echo esc_attr( $this->format_option_name( $id . '[id]' ) ); ?>" class="upload_field_id" id="pyre_<?php echo esc_attr( $id ); ?>_id" type="hidden" value="<?php echo esc_attr( $image_id ); ?>" />
								<br>
							</p>
							<p class="hide-if-no-js fusion-remove-featured-image" style="<?php echo ( ! $image_id ) ? 'display:none;' : ''; ?>">
								<a aria-label="<?php echo esc_attr__( 'Remove', 'Avada' ); ?>" href="#" id="<?php echo esc_attr( $image_id ); ?>" class="fusion-remove-image">
									<?php echo esc_html__( 'Remove', 'Avada' ); ?>
								</a>
							</p>
						</div>
					<?php } else { ?>
						<input name="<?php echo esc_attr( $this->format_option_name( $id . '[url]' ) ); ?>" class="upload_field" id="pyre_<?php echo esc_attr( $id ); ?>" type="text" value="<?php echo esc_attr( $image_url ); ?>" />
						<input name="<?php echo esc_attr( $this->format_option_name( $id . '[id]' ) ); ?>" class="upload_field_id" id="pyre_<?php echo esc_attr( $id ); ?>_id" type="hidden" value="<?php echo esc_attr( $image_id ); ?>" />
						<input class="fusion_upload_button button" type="button" value="<?php esc_attr_e( 'Browse', 'Avada' ); ?>" />
					<?php } ?>
				</div>
			</div>
		</div>
		<?php

	}
	/**
	 * Hidden input.
	 *
	 * @since 5.0.0
	 * @param string $id    id of input field.
	 * @param string $value value of input field.
	 */
	public function hidden( $id, $value ) {
		global $post;
		?>
		<input type="hidden" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $value ); ?>">
		<?php

	}

	/**
	 * Sortable controls.
	 *
	 * @since 5.7
	 * @access public
	 * @param string       $id         The ID.
	 * @param string       $label      The label.
	 * @param array        $options    The options array.
	 * @param string       $desc       The description.
	 * @param array        $dependency The dependencies array.
	 * @param string|array $default    The default value.
	 * @param mixed        $responsive The responsive param data.
	 */
	public function sortable( $id, $label, $options, $desc = '', $dependency = [], $default = '', $responsive = false ) {
		global $post;
		$sort_order_saved = $this->get_value( $id );
		$sort_order_saved = ( ! $sort_order_saved ) ? '' : $sort_order_saved;
		$sort_order       = ( empty( $sort_order_saved ) ) ? $default : $sort_order_saved;
		$sort_order       = ( is_array( $sort_order ) ) ? $sort_order : explode( ',', $sort_order );
		?>

		<div class="pyre_metabox_field<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<ul class="fusion-sortable-options" id="pyre_<?php echo esc_attr( $id ); ?>">
					<?php foreach ( $sort_order as $item ) : ?>
						<?php $item = trim( $item ); ?>
						<?php if ( isset( $options[ $item ] ) ) : ?>
							<div class="fusion-sortable-option" data-value="<?php echo esc_attr( $item ); ?>">
								<span><?php echo esc_html( $options[ $item ] ); ?></span>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
				<input class="sort-order" type="hidden" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $sort_order_saved ); ?>">
			</div>
		</div>
		<?php

	}

	/**
	 * Repeater controls.
	 *
	 * @since 6.2
	 * @access public
	 * @param string $id         The ID.
	 * @param string $label      The label.
	 * @param string $desc       The description.
	 * @param array  $dependency The dependencies array.
	 * @param array  $fields     An array of fields.
	 * @param string $bind_title What should be used for the title.
	 * @param array  $labels     An array of our labels.
	 * @param mixed  $responsive The responsive param data.
	 * @param mixed  $default    The default value.
	 */
	public function repeater( $id, $label, $desc = '', $dependency = [], $fields = [], $bind_title = '', $labels = [], $responsive = false, $default = '' ) {
		global $post;
		$add_label   = isset( $labels['row_add'] ) ? $labels['row_add'] : __( 'Add New', 'Avada' );
		$title_label = isset( $labels['row_title'] ) ? $labels['row_title'] : __( 'Repeater Row', 'Avada' );
		$value       = $post ? fusion_data()->post_meta( $post->ID )->get( $id ) : [];
		$value       = ! empty( $value ) ? $value : $default;
		$value       = 'awb_pages' === $id ? '' : $value;
		if ( is_array( $value ) ) {
			$value = wp_json_encode( $value );
		}
		?>

		<div class="pyre_metabox_field fusion-repeater-wrapper<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<a class="fusion-add-row button button-primary button-large" href="#"><?php echo esc_html( $add_label ); ?></a>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<div class="fusion-repeater-default-fields" style="display:none;">
					<div class="fusion-row-title">
						<span class="repeater-toggle-icon fusiona-pen"></span>
						<h4><?php echo esc_html( $title_label ); ?></h4>
						<span class="repeater-row-remove fusiona-trash-o"></span>
					</div>
					<div class="fusion-row-fields clearfix" style="display:none;">
						<?php $this->render_tab_fields( [ 'fields' => $fields ], true ); ?>
					</div>
				</div>
				<div class="fusion-repeater-rows"></div>
				<input class="repeater-value" data-bind="<?php echo esc_attr( $bind_title ); ?>" type="hidden" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $value ); ?>">
			</div>
		</div>
		<?php

	}

	/**
	 * HubSpot map control.
	 *
	 * @since 7.1
	 * @access public
	 * @param string       $id         The ID.
	 * @param string       $label      The label.
	 * @param array        $options    The options array.
	 * @param string       $desc       The description.
	 * @param array        $dependency The dependencies array.
	 * @param string|array $default    The default value.
	 * @param mixed        $responsive The responsive param data.
	 */
	public function hubspot_map( $id, $label, $options, $desc = '', $dependency = [], $default = '', $responsive = false ) {
		$value = $this->get_value( $id );
		if ( is_array( $value ) ) {
			$value = wp_json_encode( $value );
		}
		?>

		<div class="pyre_metabox_field fusion-hubspot-option<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<div class="hubspot-map-holder">
					<div class="fusion-mapping">
						<span><?php esc_attr_e( 'No form fields or HubSpot properties found.', 'Avada' ); ?></span>
					</div>
				</div>
				<input type="hidden" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $value ); ?>">
			</div>
		</div>
		<?php

	}

	/**
	 * HubSpot map control.
	 *
	 * @since 7.5
	 * @access public
	 * @param string       $id         The ID.
	 * @param string       $label      The label.
	 * @param array        $options    The options array.
	 * @param string       $desc       The description.
	 * @param array        $dependency The dependencies array.
	 * @param string|array $default    The default value.
	 * @param mixed        $responsive The responsive param data.
	 */
	public function mailchimp_map( $id, $label, $options, $desc = '', $dependency = [], $default = '', $responsive = false ) {
		$value = $this->get_value( $id );
		if ( is_array( $value ) ) {
			$value = wp_json_encode( $value );
		}
		?>

		<div class="pyre_metabox_field fusion-mailchimp-option<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
			<div class="pyre_field">
				<div class="mailchimp-map-holder">
					<div class="fusion-mapping">
						<span><?php esc_attr_e( 'No form fields or Mailchimp merge tags found.', 'Avada' ); ?></span>
					</div>
				</div>
				<input type="hidden" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $value ); ?>">
			</div>
		</div>
		<?php

	}
		/**
		 * Icon field.
		 *
		 * @access public
		 * @since 7.6
		 * @param string $id         ID of input field.
		 * @param string $label      Label of field.
		 * @param string $desc       Description of field.
		 * @param array  $dependency The dependencies array.
		 * @param mixed  $responsive The responsive param data.
		 */
	public function iconpicker( $id, $label, $desc = '', $dependency = [], $responsive = false ) {
		?>
			<div class="pyre_metabox_field<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> iconpicker fusion-builder-option field-<?php echo esc_attr( $id ); ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
				<?php if ( $desc ) : ?>
						<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<?php endif; ?>
				</div>
				<div class="pyre_field fusion-iconpicker">
						<input class="fusion-iconpicker-input" type="hidden" id="pyre_<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->format_option_name( $id ) ); ?>" value="<?php echo esc_attr( $this->get_value( $id ) ); ?>" />
						<div class="fusion-iconpicker-preview">
							<input type="text" class="fusion-icon-search fusion-hide-from-atts fusion-dont-update" placeholder="<?php echo esc_attr__( 'Search Icons', 'Avada' ); ?>" />
							<span class="input-icon fusiona-search"></span>
							<span class="add-custom-icons">
								<a href="<?php echo esc_url( admin_url( '/post-new.php?post_type=fusion_icons' ) ); ?>" target="_blank" class="fusiona-plus"></a>
							</span>
						</div>
						<div class="fusion-iconselect-wrapper">
							<div class="icon_select_container"></div>
						</div>
				</div>
			</div>
			<?php
	}

	/**
	 * Toggle / Group.
	 *
	 * @since 7.7
	 * @access public
	 * @param string $id         The ID.
	 * @param string $label      The label.
	 * @param string $desc       The description.
	 * @param array  $dependency The dependencies array.
	 * @param array  $fields     An array of fields.
	 * @param mixed  $responsive The responsive param data.
	 * @param mixed  $row_title  The row title.
	 * @param mixed  $state      The default state for the toggle.
	 */
	public function toggle( $id, $label, $desc = '', $dependency = [], $fields = [], $responsive = false, $row_title = '', $state = '' ) {
		?>

		<div class="pyre_metabox_field fusion-toggle-wrapper fusion-repeater-wrapper<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $label || $desc ) : ?>
				<div class="pyre_desc">
					<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( $label ); ?></label>
					<?php if ( $desc ) : ?>
						<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="pyre_field">
				<div class="fusion-toggle-row">
					<div class="fusion-row-title fusion-toggle-title">
						<span class="repeater-toggle-icon fusiona-pen"></span>
						<h4><?php echo esc_html( $row_title ); ?></h4>
						<span></span>
					</div>
					<div class="fusion-row-fields clearfix" style="display:none">
						<?php $this->render_tab_fields( [ 'fields' => $fields ] ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php

	}

	/**
	 * Dependency markup.
	 *
	 * @since 5.0.0
	 * @param array $dependency dependence options.
	 * @return string $data_dependence markup
	 */
	private function dependency( $dependency = [] ) {

		// Disable dependencies if 'dependencies_status' is set to 0.
		if ( '0' === Avada()->settings->get( 'dependencies_status' ) ) {
			return '';
		}

		$data_dependency = '';
		if ( is_array( $dependency ) && 0 < count( $dependency ) ) {
			$data_dependency .= '<div class="avada-dependency">';
			foreach ( $dependency as $dependence ) {
				$data_dependency .= '<span class="hidden" data-value="' . $dependence['value'] . '" data-field="' . $dependence['field'] . '" data-comparison="' . $dependence['comparison'] . '"></span>';
			}
			$data_dependency .= '</div>';
		}
		return $data_dependency;
	}

	/**
	 * Raw field.
	 *
	 * @since 5.3.0
	 * @param array  $id         IDs of input fields.
	 * @param string $label      Label of field.
	 * @param string $desc       Description of field.
	 * @param array  $dependency The dependencies array.
	 * @param mixed  $responsive The responsive param data.
	 */
	public function raw( $id, $label, $desc = '', $dependency = [], $responsive = false ) {
		global $post;
		?>

		<div class="pyre_metabox_field<?php echo false !== $responsive ? ' has-responsive fusion-' . $responsive : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<?php // No need to sanitize this, we already know what's in here. ?>
			<?php echo $this->dependency( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="pyre_desc_raw">
				<label for="pyre_<?php echo esc_attr( $id ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
				<?php if ( $desc ) : ?>
					<p><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php

	}

	/**
	 * Check if the meta data object has already been saved to database.
	 *
	 * @since 6.2.1
	 * @return bool True if meta data is already in db, false otherwise.
	 */
	public function is_meta_data_saved_in_db() {
		global $post;

		if ( ! $post ) {
			return false;
		}
		return ! empty( fusion_data()->post_meta( $post->ID )->get_all_meta() );
	}

	/**
	 * Filters sections to add responsive params.
	 *
	 * @access public
	 * @since 7.6.0
	 * @param array $sections The existing sections.
	 * @return array
	 */
	public function filter_sections( $sections ) {
		$responsive_atts = [];

		// Check if responsive params exists.
		foreach ( $sections as $key => $section ) {
			if ( isset( $section['fields'] ) && is_array( $section['fields'] ) ) {
				foreach ( $section['fields'] as $field ) {
					if ( isset( $field['responsive'] ) ) {
						$responsive_atts[ $key ][] = [
							'id'          => $field['id'],
							'description' => $field['description'],
							'args'        => $field['responsive'],
						];
					}
				}
			}
		}

		// Add responsive.
		if ( 0 < count( $responsive_atts ) ) {
			$sections = apply_filters( 'awb_responsive_params', $responsive_atts, $sections );
		}

		return $sections;
	}

	/**
	 * Adds responsive params.
	 *
	 * @access public
	 * @since 7.6.0
	 * @param array $responsive_sections The responsive section.
	 * @param array $sections            The params.
	 * @return array
	 */
	public function add_responsive_params( $responsive_sections, $sections ) {

		foreach ( $responsive_sections as $key => $fields ) {
			foreach ( $fields as $att ) {
				$position          = array_search( $att['id'], array_keys( $sections[ $key ]['fields'] ), true );
				$states            = isset( $att['args']['additional_states'] ) ? $att['args']['additional_states'] : [ 'medium', 'small' ];
				$responsive_params = [];

				foreach ( $states as $state ) {
					$param                        = $sections[ $key ]['fields'][ $att['id'] ];
					$param['id']                  = $att['id'] . '_' . $state;
					$param['description']         = $att['description'];
					$param['responsive']['state'] = $state;
					$param                        = self::add_responsive_values_data( $param, $state );

					if ( isset( $att['args']['default_value'] ) && true === $att['args']['default_value'] ) {
						$param['value']   = [ '' => 'Default' ] + $param['value'];
						$param['default'] = '';
					}

					if ( isset( $att['args']['defaults'][ $state ] ) ) {
						$param['default'] = $att['args']['defaults'][ $state ];
					}

					if ( isset( $att['args']['values'][ $state ] ) ) {
						$param['value'] = $att['args']['values'][ $state ];
					}

					if ( isset( $att['args']['descriptions'][ $state ] ) ) {
						$param['description'] = $att['args']['descriptions'][ $state ];
					}

					$responsive_params[ $param['id'] ] = $param;
				}

				$position_2 = $position;

				if ( isset( $att['args']['exclude_main_state'] ) && true === $att['args']['exclude_main_state'] ) {
					$position_2 = $position + 1;
				}

				// Insert responsive params.
				$sections[ $key ]['responsive'] = true;
				$sections[ $key ]['fields']     = array_merge( array_slice( $sections[ $key ]['fields'], 0, $position ), $responsive_params, array_slice( $sections[ $key ]['fields'], $position_2 ) );
			}
		}

		return $sections;
	}

	/**
	 * Adds responsive values data.
	 *
	 * @since 7.6
	 * @access public
	 * @param array  $param Element params.
	 * @param string $state Responsive state.
	 * @return array
	 */
	public function add_responsive_values_data( $param, $state ) {

		if ( isset( $param['type'] ) && isset( $param['value'] ) ) {
			switch ( $param['type'] ) {
				case 'dimensions':
					foreach ( $param['value'] as $key => $value ) {
						$param['value'][ $key . '_' . $state ] = $value;
						unset( $param['value'][ $key ] );
					}
					break;
			}
		}

		return $param;
	}
}

global $pagenow;

if ( is_admin() && ( ( in_array( $pagenow, [ 'post-new.php', 'post.php' ], true ) ) || ! isset( $pagenow ) || apply_filters( 'fusion_page_options_init', false ) ) ) {
	if ( ! PyreThemeFrameworkMetaboxes::$instance ) {
		$metaboxes = new PyreThemeFrameworkMetaboxes();
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

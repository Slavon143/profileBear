<?php
/**
 * Handles the block editor implementation.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      6.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * The block editor handler.
 *
 * @since 6.0
 */
class Avada_Block_Editor {

	/**
	 * Check if there are blocks in the current page or not.
	 *
	 * @static
	 * @access private
	 * @since 6.2.0
	 * @var bool
	 */
	private static $has_blocks = false;

	/**
	 * The class constructor.
	 *
	 * @access public
	 * @since 6.0
	 */
	public function __construct() {

		// Back-end and front-end.
		add_action( 'after_setup_theme', [ $this, 'add_theme_supports' ], 10 );
		add_filter( 'render_block', [ $this, 'adjust_block_classes' ], 10, 2 );

		// Back-end only: block editor admin.
		if ( is_admin() ) {
			add_action( 'enqueue_block_assets', [ $this, 'enqueue_block_editor_assets' ] );
			add_filter( 'admin_body_class', [ $this, 'add_admin_body_classes' ] );

			// Disables the block editor from managing widgets in the Gutenberg plugin.
			add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );

			// Disables the block editor from managing widgets.
			add_filter( 'use_widgets_block_editor', '__return_false' );

		} else { // Front-end only.

			add_filter( 'fusion_dynamic_css_final', [ $this, 'add_css_vars_to_css' ], PHP_INT_MAX );

			// Use fusion_library()->get_option() instead of Avada()->settings->get() or fusion_get_option()
			// to avoid an infinite loop 'caused by the Avada constructor calling this object again.
			$load_block_styles = fusion_library()->get_option( 'load_block_styles' );

			// Remove block styles if not expricitly set to ON.
			// If AUTO we'll be adding them conditionally.
			if ( 'on' !== $load_block_styles ) {
				add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_block_styles' ], 999 );
			}

			// Run checks and conditionally add block-styles
			// if there are blocks present on this page.
			if ( 'auto' === $load_block_styles ) {
				add_filter( 'render_block', [ $this, 'set_has_blocks' ] );
				add_action( 'wp_footer', [ $this, 'maybe_add_block_styles' ], 0 );
			}
		}
	}

	/**
	 * Enqueue admin editor assets.
	 *
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_script( 'fusion-block-editor-scripts', Avada::$template_dir_url . '/assets/admin/js/block-editor.js', [ 'jquery' ], Avada::get_theme_version(), true );
		wp_enqueue_style( 'fusion-block-editor-styles', Avada::$template_dir_url . '/assets/css/dynamic/blocks.min.css', [], Avada::get_theme_version() );

		if ( Avada()->settings->get( 'enable_block_editor_backend_styles' ) ) {
			$this->add_admin_custom_css();
		}
	}

	/**
	 * Add fusion-body and other classes to admin pages.
	 *
	 * @access public
	 * @since 6.0
	 * @param string $body_classes The body classes as a string.
	 * @return string
	 */
	public function add_admin_body_classes( $body_classes ) {

		$body_classes .= ' fusion-body';
		$body_classes .= is_rtl() ? ' rtl' : ' ltr';

		return $body_classes;
	}

	/**
	 * Add custom CSS to the block editor admin.
	 *
	 * @access private
	 * @since 6.0
	 * @return void
	 */
	private function add_admin_custom_css() {
		$custom_css  = '';
		$custom_css .= $this->get_admin_content_width_styles();
		$custom_css .= $this->get_admin_typography_styles();
		$custom_css .= $this->get_admin_general_styles();
		$custom_css .= $this->get_css_vars();

		wp_add_inline_style( 'fusion-block-editor-styles', $custom_css );
	}

	/**
	 * Get content width for block editor admin.
	 *
	 * @access private
	 * @since 6.0
	 * @return string The content width styles.
	 */
	private function get_admin_content_width_styles() {
		$custom_css = '';

		// Site width.
		$site_wdidth = Avada()->settings->get( 'site_width' );

		$custom_css .= '
			.wp-block:not([data-align="wide"]):not([data-align="full"]),
			.wp-block:not([data-align="wide"]):not([data-align="full"]) > * {
				max-width: ' . $site_wdidth . ';
			}';

		// Single-sidebar Layouts.
		$single_sidebar_gutter = Avada()->settings->get( 'sidebar_gutter' );
		$sidebar_width         = Fusion_Sanitize::size( Avada()->settings->get( 'sidebar_width' ) );
		if ( ! Fusion_Sanitize::get_unit( $sidebar_width ) ) {
			$sidebar_width = ( 100 > intval( $sidebar_width ) ) ? $sidebar_width . '%' : $sidebar_width . 'px';
		}

		$custom_css .= '
			.block-editor-page.has-sidebar .wp-block:not([data-align="wide"]):not([data-align="full"]) > * {
				max-width: ' . Fusion_Sanitize::add_css_values( [ '100%', '-' . $sidebar_width, '-' . $single_sidebar_gutter ] ) . ';
			}';

		// Double-Sidebar layouts.
		$dual_sidebar_gutter = Avada()->settings->get( 'dual_sidebar_gutter' );
		$sidebar_2_1_width   = Fusion_Sanitize::size( Avada()->settings->get( 'sidebar_2_1_width' ) );
		if ( ! Fusion_Sanitize::get_unit( $sidebar_2_1_width ) ) {
			$sidebar_2_1_width = ( 100 > intval( $sidebar_2_1_width ) ) ? $sidebar_2_1_width . '%' : $sidebar_2_1_width . 'px';
		}
		$sidebar_2_2_width = Fusion_Sanitize::size( Avada()->settings->get( 'sidebar_2_2_width' ) );
		if ( ! Fusion_Sanitize::get_unit( $sidebar_2_2_width ) ) {
			$sidebar_2_2_width = ( 100 > intval( $sidebar_2_2_width ) ) ? $sidebar_2_2_width . '%' : $sidebar_2_2_width . 'px';
		}

		$custom_css .= '
			.block-editor-page.double-sidebars .wp-block:not([data-align="wide"]):not([data-align="full"]) > * {
				max-width: ' . Fusion_Sanitize::add_css_values( [ '100%', '-' . $sidebar_2_1_width, '-' . $sidebar_2_2_width, '-' . $dual_sidebar_gutter, '-' . $dual_sidebar_gutter ] ) . ';
			}';

		return $custom_css;
	}

	/**
	 * Add dynamic typography styles.
	 *
	 * @access public
	 * @since 6.0
	 * @return string The admin typgography styles.
	 */
	public function get_admin_typography_styles() {
		$dynamic_css         = Fusion_Dynamic_CSS::get_instance();
		$dynamic_css_helpers = $dynamic_css->get_helpers();

		$custom_css = '';

		for ( $i = 0; $i < 7; $i++ ) {
			if ( 0 === $i ) {
				$selector           = '.block-editor .editor-styles-wrapper';
				$typography_setting = 'body_typography';
			} else {
				$selector           = '.fusion-body .wp-block-heading h' . $i;
				$typography_setting = 'h' . $i . '_typography';
			}

			$custom_css .= $selector . '{
				font-family: ' . $dynamic_css_helpers->combined_font_family( Avada()->settings->get( $typography_setting ) ) . ';
				font-weight: ' . intval( Avada()->settings->get( $typography_setting, 'font-weight' ) ) . ';
				letter-spacing: ' . Fusion_Sanitize::size( Avada()->settings->get( $typography_setting, 'letter-spacing' ), 'px' ) . ';
				font-style: ' . Avada()->settings->get( $typography_setting, 'font-style' ) . ';
				line-height: ' . Fusion_Sanitize::size( Avada()->settings->get( $typography_setting, 'line-height' ) ) . ';
				font-size: ' . Fusion_Sanitize::size( Avada()->settings->get( $typography_setting, 'font-size' ) ) . ';
				color: ' . Fusion_Sanitize::color( Avada()->settings->get( $typography_setting, 'color' ) ) . ';
			}';
		}

		$google_fonts = Avada_Google_Fonts::get_instance();
		$custom_css  .= $google_fonts->add_inline_css( $custom_css );

		return $custom_css;
	}

	/**
	 * Get the general admin editor styles.
	 *
	 * @access private
	 * @since 6.0
	 * @return string The dynamic general styles.
	 */
	private function get_admin_general_styles() {
		$custom_css = '';

		$custom_css .= '
			.editor-styles-wrapper a,
			.editor-styles-wrapper a:before,
			.editor-styles-wrapper a:after {
				text-decoration: none;
				color: ' . Fusion_Sanitize::color( Avada()->settings->get( 'link_color' ) ) . ';
		}';

		// Add bg color.
		$bg_color = $this->get_post_bg_color();

		$custom_css .= '.block-editor .editor-styles-wrapper{background-color:' . $bg_color . ';}';

		return $custom_css;
	}

	/**
	 * Get the CSS vars for dynamic styles.
	 *
	 * @access private
	 * @since 6.0
	 * @return string The CSS vars.
	 */
	private function get_css_vars() {
		$dynamic_css         = Fusion_Dynamic_CSS::get_instance();
		$dynamic_css_helpers = $dynamic_css->get_helpers();
		$css_vars_string     = '';

		$css_vars = [
			'link_color'               => Fusion_Sanitize::color( Avada()->settings->get( 'link_color' ) ),
			'primary_color'            => Fusion_Sanitize::color( Avada()->settings->get( 'primary_color' ) ),
			'meta_font_size'           => Fusion_Sanitize::size( Avada()->settings->get( 'meta_font_size' ) ),
			'form_input_height'        => Fusion_Sanitize::size( Avada()->settings->get( 'form_input_height' ) ),
			'form_bg_color'            => Fusion_Sanitize::color( Avada()->settings->get( 'form_bg_color' ) ),
			'form_border_color'        => Fusion_Sanitize::color( Avada()->settings->get( 'form_border_color' ) ),
			'form_focus_border_color'  => Fusion_Sanitize::color( Avada()->settings->get( 'form_focus_border_color' ) ),
			'form_border_width-top'    => Fusion_Sanitize::size( Avada()->settings->get( 'form_border_width', 'top' ) ),
			'form_border_width-right'  => Fusion_Sanitize::size( Avada()->settings->get( 'form_border_width', 'right' ) ),
			'form_border_width-bottom' => Fusion_Sanitize::size( Avada()->settings->get( 'form_border_width', 'bottom' ) ),
			'form_border_width-left'   => Fusion_Sanitize::size( Avada()->settings->get( 'form_border_width', 'left' ) ),
			'form_border_radius'       => Fusion_Sanitize::size( Avada()->settings->get( 'form_border_radius' ), 'px' ),
			'form_text_color'          => Fusion_Sanitize::color( Avada()->settings->get( 'form_text_color' ) ),
			'form_text_size'           => Fusion_Sanitize::size( Avada()->settings->get( 'form_text_size' ) ),
			'testimonial_bg_color'     => Fusion_Sanitize::color( Avada()->settings->get( 'testimonial_bg_color' ) ),
			'sep_color'                => Fusion_Sanitize::color( Avada()->settings->get( 'sep_color' ) ),
		];

		$css_button_vars = [
			'button_accent_color'                => Fusion_Sanitize::color( Avada()->settings->get( 'button_accent_color' ) ),
			'button_accent_hover_color'          => Fusion_Sanitize::color( Avada()->settings->get( 'button_accent_hover_color' ) ),
			'button_border_color'                => Fusion_Sanitize::color( Avada()->settings->get( 'button_border_color' ) ),
			'button_border_hover_color'          => Fusion_Sanitize::color( Avada()->settings->get( 'button_border_hover_color' ) ),
			'button_text_transform'              => Avada()->settings->get( 'button_text_transform' ),
			'button_gradient_top_color'          => Fusion_Sanitize::color( Avada()->settings->get( 'button_gradient_top_color' ) ),
			'button_gradient_top_color_hover'    => Fusion_Sanitize::color( Avada()->settings->get( 'button_gradient_top_color_hover' ) ),
			'button_gradient_bottom_color'       => Fusion_Sanitize::color( Avada()->settings->get( 'button_gradient_bottom_color' ) ),
			'button_gradient_bottom_color_hover' => Fusion_Sanitize::color( Avada()->settings->get( 'button_gradient_bottom_color_hover' ) ),
			'button_border_width-top'            => Fusion_Sanitize::size( Avada()->settings->get( 'button_border_width', 'top' ), 'px' ),
			'button_border_width-right'          => Fusion_Sanitize::size( Avada()->settings->get( 'button_border_width', 'right' ), 'px' ),
			'button_border_width-bottom'         => Fusion_Sanitize::size( Avada()->settings->get( 'button_border_width', 'bottom' ), 'px' ),
			'button_border_width-left'           => Fusion_Sanitize::size( Avada()->settings->get( 'button_border_width', 'left' ), 'px' ),
			'button-border-radius-top-left'      => Fusion_Sanitize::size( Avada()->settings->get( 'button_border_radius', 'top_left' ), 'px' ),
			'button-border-radius-top-right'     => Fusion_Sanitize::size( Avada()->settings->get( 'button_border_radius', 'top_right' ), 'px' ),
			'button-border-radius-bottom-right'  => Fusion_Sanitize::size( Avada()->settings->get( 'button_border_radius', 'bottom_right' ), 'px' ),
			'button-border-radius-bottom-left'   => Fusion_Sanitize::size( Avada()->settings->get( 'button_border_radius', 'bottom_left' ), 'px' ),
			'button_typography-font-family'      => $dynamic_css_helpers->combined_font_family( Avada()->settings->get( 'button_typography' ) ),
			'button_typography-font-weight'      => intval( Avada()->settings->get( 'button_typography', 'font-weight' ) ),
			'button_typography-font-style'       => Avada()->settings->get( 'button_typography', 'font-style' ),
			'button_typography-letter-spacing'   => Fusion_Sanitize::size( Avada()->settings->get( 'button_typography', 'letter-spacing' ) ) . 'px',
			'button_box_shadow'                  => '3d' === Avada()->settings->get( 'button_type' ) ? 'inset 0px 1px 0px #ffffff, 0px 3px 0px ' . Fusion_Sanitize::color( Avada()->settings->get( 'button_bevel_color' ) ) . ', 1px 5px 5px 3px rgba(0, 0, 0, 0.3)' : 'none',
			'button_padding'                     => '11px 23px',
			'button_font_size'                   => '13px',
			'button_line_height'                 => '16px',
		];

		$css_vars = apply_filters( 'fusion_block_editor_css_vars', array_merge( $css_vars, $css_button_vars ) );

		foreach ( $css_vars as $key => $value ) {
			$css_vars_string .= '--' . $key . ':' . $value . ';';
		}

		return ':root{' . $css_vars_string . '}';
	}

	/**
	 * Add theme support.
	 *
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public function add_theme_supports() {

		// Add support for full and wide align images.
		add_theme_support( 'align-wide' );

		// Add support for the basic, native block styles.
		add_theme_support( 'wp-block-styles' );

		if ( Avada()->settings->get( 'enable_block_editor_backend_styles' ) ) {

			// Add the editor styles.
			add_theme_support( 'editor-styles' );

			// Set UI to dark mode if bg is dark.
			$bg_color         = $this->get_post_bg_color();
			$brightness_level = Fusion_Color::new_color( $bg_color )->brightness;

			if ( isset( $brightness_level['total'] ) && $brightness_level['total'] < 140 ) {
				add_theme_support( 'dark-editor-style' );
			}
		}

		// Add custom editor font sizes.
		$body_font_size       = Avada()->settings->get( 'body_typography', 'font-size' );
		$body_font_size_in_px = absint( Fusion_Sanitize::convert_font_size_to_px( $body_font_size, $body_font_size ) );
		$body_font_size_in_px = $body_font_size_in_px ? $body_font_size_in_px : 13;
		$font_size            = [
			'small'  => $body_font_size_in_px * 0.75,
			'normal' => $body_font_size_in_px,
			'large'  => $body_font_size_in_px * 1.5,
			'xlarge' => $body_font_size_in_px * 2,
			'huge'   => $body_font_size_in_px * 3,
		];

		$font_size = apply_filters( 'fusion_block_editor_font_sizes', $font_size, $body_font_size_in_px );

		add_theme_support(
			'editor-font-sizes',
			[
				[
					'name'      => __( 'Small', 'Avada' ),
					'shortName' => __( 'S', 'Avada' ),
					'size'      => $font_size['small'],
					'slug'      => 'small',
				],
				[
					'name'      => __( 'Normal', 'Avada' ),
					'shortName' => __( 'M', 'Avada' ),
					'size'      => $font_size['normal'],
					'slug'      => 'normal',
				],
				[
					'name'      => __( 'Large', 'Avada' ),
					'shortName' => __( 'L', 'Avada' ),
					'size'      => $font_size['large'],
					'slug'      => 'large',
				],
				[
					'name'      => __( 'XLarge', 'Avada' ),
					'shortName' => __( 'XL', 'Avada' ),
					'size'      => $font_size['xlarge'],
					'slug'      => 'xlarge',
				],
				[
					'name'      => __( 'Huge', 'Avada' ),
					'shortName' => __( 'XXL', 'Avada' ),
					'size'      => $font_size['huge'],
					'slug'      => 'huge',
				],
			]
		);
	}

	/**
	 * Adjust some block classes to get Avada specific styling.
	 *
	 * @access public
	 * @since 6.0
	 * @param string $block_content The actual block content.
	 * @param array  $block         Additional block parameter.
	 * @return string The changed block content.
	 */
	public function adjust_block_classes( $block_content, $block ) {
		$block_name = isset( $block['blockName'] ) ? str_replace( 'core/', '', $block['blockName'] ) : '';

		switch ( $block_name ) {
			case 'search':
				$block_content = str_replace( 'wp-block-search__button', 'fusion-button-default fusion-button-default-size wp-block-search__button', $block_content );
				break;
			case 'file':
				$block_content = str_replace( 'wp-block-file__button', 'fusion-button-default fusion-button-default-size wp-block-file__button', $block_content );
				break;
		}

		return $block_content;
	}

	/**
	 * Sets the $has_blocks static var.
	 *
	 * @access public
	 * @since 6.0
	 * @param string $block_content The actual block content.
	 * @return string Returns the $block_content unmodified.
	 */
	public function set_has_blocks( $block_content ) {
		self::$has_blocks = true;
		return $block_content;
	}

	/**
	 * Remove block styles, if no blocks are on the page.
	 *
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public function dequeue_block_styles() {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
	}

	/**
	 * Checks if we need to add the block styles.
	 *
	 * @access public
	 * @since 6.2.0
	 * @return void
	 */
	public function maybe_add_block_styles() {
		if ( self::$has_blocks ) {
			wp_enqueue_style( 'wp-block-library' );
			wp_enqueue_style( 'wp-block-library-theme' );
		}
	}

	/**
	 * Add css-vars to the final CSS.
	 *
	 * @access public
	 * @since 6.0
	 * @param string $css The CSS.
	 * @return string
	 */
	public function add_css_vars_to_css( $css ) {
		return ':root{--button_padding:11px 23px;}' . $css;
	}

	/**
	 * Get the CSS vars for dynamic styles.
	 *
	 * @access private
	 * @since 6.0.2
	 * @param int $post_id The post-ID.
	 * @return string The bg color value.
	 */
	private function get_post_bg_color( $post_id = 0 ) {
		global $post;

		if ( ! $post_id ) {
			if ( isset( $post->ID ) ) {
				$post_id = $post->ID;
			} elseif ( isset( $_GET ) && isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$post_id = wp_unslash( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			}
		}

		$po_bg_color = '';
		if ( $post_id ) {
			$po_bg_color = fusion_get_option( 'content_bg_color' );
		}

		if ( ! empty( $po_bg_color ) ) {
			$bg_color = Fusion_Sanitize::color( $po_bg_color );
		} else {
			$bg_color = Fusion_Sanitize::color( Avada()->settings->get( 'content_bg_color' ) );
		}

		return $bg_color;
	}
}

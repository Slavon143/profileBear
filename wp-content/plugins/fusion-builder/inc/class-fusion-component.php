<?php
/**
 * Builder Component Class.
 *
 * @package fusion-builder
 * @since 2.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}


/**
 * Builder Component Class.
 *
 * @since 2.2
 */
abstract class Fusion_Component extends Fusion_Element {

	/**
	 * Backup post object..
	 *
	 * @access public
	 * @since 2.2
	 * @var object
	 */
	public $backup_post = false;

	/**
	 * Target live editing post if applicable.
	 *
	 * @access public
	 * @since 2.2
	 * @var object
	 */
	public $post_target = false;

	/**
	 * Shortcode handle.
	 *
	 * @access public
	 * @since 2.2
	 * @var string
	 */
	public $shortcode_handle = '';

	/**
	 * Constructor.
	 *
	 * @since 2.2
	 * @param string $shortcode_handle Shortcode Handle.
	 */
	public function __construct( $shortcode_handle ) {
		parent::__construct();

		$this->shortcode_handle = $shortcode_handle;
		add_filter( 'fusion_component_' . $this->shortcode_handle . '_content', [ $this, 'maybe_check_render' ] );
		add_shortcode( $this->shortcode_handle, [ $this, 'render' ] );
	}

	/**
	 * Maybe check render.
	 *
	 * @since 2.2
	 * @param string $content Content.
	 * @return string
	 */
	public function maybe_check_render( $content ) {
		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() || ( fusion_doing_ajax() && isset( $_POST['fusion_load_nonce'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

		if ( $is_builder ) {
			return $content;
		}
		return $this->should_render() ? $content : '';
	}

	/**
	 * Check if component should render.
	 *
	 * @access public
	 * @since 2.2
	 * @return boolean
	 */
	abstract public function should_render();

	/**
	 * Render the shortcode.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param  array  $args    Shortcode parameters.
	 * @param  string $content Content between shortcode.
	 * @return string          HTML output.
	 */
	abstract public function render( $args, $content );

	/**
	 * Returns the post-ID.
	 *
	 * @since 6.2.0
	 * @return int
	 */
	public function get_post_id() {
		$id = get_the_ID();
		if ( isset( $_POST['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.PHP.StrictComparisons.LooseComparison
			$id = (int) sanitize_text_field( wp_unslash( $_POST['post_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.PHP.StrictComparisons.LooseComparison
		}

		return apply_filters( 'fusion_dynamic_post_id', $id );
	}

	/**
	 * Emulates post with another.
	 *
	 * @access public
	 */
	public function get_target_post() {
		if ( fusion_doing_ajax() && isset( $_POST['action'] ) && 'get_fusion_post_cards' === $_POST['action'] ) { // phpcs:ignore WordPress.Security
			return false;
		}

		if ( $this->post_target ) {
			return $this->post_target;
		}

		if ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() && 'fusion_tb_section' === get_post_type() || is_singular( 'fusion_tb_section' ) || FusionBuilder()->editing_post_card ) {
			$target = Fusion_Template_Builder()->get_target_example();

			if ( $target ) {
				return $this->post_target = apply_filters( 'fusion_component_element_target', $target );
			}
		} elseif ( fusion_doing_ajax() && isset( $_POST['target_post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.PHP.StrictComparisons.LooseComparison
			return $this->post_target = get_post( (int) sanitize_text_field( wp_unslash( $_POST['target_post'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.PHP.StrictComparisons.LooseComparison
		} elseif ( fusion_doing_ajax() && isset( $_POST['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.PHP.StrictComparisons.LooseComparison
			if ( -99 === (int) $_POST['post_id'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				return $this->post_target = Fusion_Dummy_Post::get_dummy_post();
			}
			return $this->post_target = get_post( (int) sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.PHP.StrictComparisons.LooseComparison
		}
		return false;
	}


	/**
	 * Emulates post with another.
	 *
	 * @access public
	 */
	public function emulate_post() {
		global $post;

		// Exclude full refresh as content is generated server side anyway.
		$live_request = isset( $_POST['post_id'] ) && isset( $_POST['action'] ) && 'fusion_app_full_refresh' !== $_POST['action'] ? true : false; // phpcs:ignore WordPress.Security.NonceVerification

		// Live editor and editing template.
		if ( $this->get_target_post() ) {
			$this->backup_post = $post;
			$post              = $this->post_target;

			setup_postdata( $post );

			if ( class_exists( 'Fusion_App' ) && $live_request ) {
				Fusion_App()->emulate_wp_query();
			}
		}
	}

	/**
	 * Restores post object.
	 *
	 * @access public
	 */
	public function restore_post() {
		global $post;
		if ( $this->backup_post ) {
			$post = $this->backup_post;
		}
		$this->backup_post = false;

		// Exclude full refresh as content is generated server side anyway.
		$live_request       = isset( $_POST['post_id'] ) && isset( $_POST['action'] ) && 'fusion_app_full_refresh' !== $_POST['action'] ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
		$post_cards_request = isset( $_POST['action'] ) && 'get_fusion_post_cards' === $_POST['action'] ? true : false; // phpcs:ignore WordPress.Security

		if ( class_exists( 'Fusion_App' ) && $live_request && ! $post_cards_request ) {
			Fusion_App()->restore_wp_query();
		}
	}
}

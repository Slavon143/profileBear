<?php
/**
 * The main Patcher class.
 *
 * @package Fusion-Library
 * @subpackage Fusion-Patcher
 */

/**
 * The main Patcher class for Fusion Library.
 *
 * @since 1.0.0
 */
class Fusion_Patcher {

	/**
	 * The arguments used in the constructor.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var array
	 */
	private $args = [];

	/**
	 * An array of all bundled products.
	 * This is used because bundled products should show their patches
	 * as part of the parent item's patcher.
	 *
	 * @static
	 * @access private
	 * @since 1.0.0
	 * @var array
	 */
	private static $bundled = [];

	/**
	 * An instance of the Fusion_Patcher_Apply_Patch class.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var object Fusion_Patcher_Apply_Patch
	 */
	private $apply_patch;

	/**
	 * An instance of the Fusion_Patcher_Admin_Screen class.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var object Fusion_Patcher_Admin_Screen
	 */
	private $admin_screen;

	/**
	 * An instance of the Fusion_Patcher_Checker class.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var object Fusion_Patcher_Checker
	 */
	private $patcher_checker;

	/**
	 * The class constructor.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $args The arguments we want to pass-on to the patcher.
	 */
	public function __construct( $args = [] ) {
		$this->args = $args;

		// Only instantiate the sub-classes if we're on the admin page.
		$slug            = $args['context'] . '-patcher';
		$referer         = fusion_get_referer();
		$referer         = $referer ? $referer : '';
		$is_patcher_page = (bool) ( is_admin() && ( ( isset( $_GET['page'] ) && $slug === $_GET['page'] ) || ( false !== strpos( $referer, $slug ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

		// Add bundled products.
		$this->add_bundled( $args );

		if ( $is_patcher_page ) {
			// Enqueue styles & scripts.
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		}
		$this->args['is_patcher_page'] = $is_patcher_page;

		// Patches handler.
		$this->apply_patch = new Fusion_Patcher_Apply_Patch( $this );

		// Admin-page handler.
		$this->admin_screen = new Fusion_Patcher_Admin_Screen( $this );

		// Checks for patches periodically.
		$this->patcher_checker = new Fusion_Patcher_Checker( $this );

	}

	/**
	 * Get the args.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param false|string $arg Get a specific argument, or get all.
	 * @return array|string
	 */
	public function get_args( $arg = false ) {
		if ( false !== $arg ) {
			if ( isset( $this->args[ $arg ] ) ) {
				return $this->args[ $arg ];
			}
			return null;
		}
		return (array) $this->args;
	}

	/**
	 * Adds any bundled products to self::$bundled.
	 *
	 * @access private
	 * @since 1.0.0
	 * @param array $args Inherited from the constructor.
	 * @return void
	 */
	private function add_bundled( $args ) {
		if ( ! isset( $args['bundled'] ) ) {
			return;
		}
		// Make sure we're dealing with an array.
		$bundled = (array) $args['bundled'];

		foreach ( $bundled as $bundle ) {
			self::$bundled[] = $bundle;
		}
	}

	/**
	 * Get an array of our bundled products.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return array
	 */
	public function get_bundled() {
		return array_unique( self::$bundled );
	}

	/**
	 * Get bundled plugin version.
	 *
	 * @access public
	 * @since 3.4.0
	 * @param string $bundled Bundled plugin slug.
	 * @return mixed null|float
	 */
	public function get_bundled_version( $bundled ) {

		if ( 'fusion-builder' === $bundled && defined( 'FUSION_BUILDER_VERSION' ) ) {
			return FUSION_BUILDER_VERSION;
		}

		if ( 'fusion-core' === $bundled && defined( 'FUSION_CORE_VERSION' ) ) {
			return FUSION_CORE_VERSION;
		}

		if ( 'fusion-white-label-branding' === $bundled && defined( 'FUSION_WHITE_LABEL_BRANDING_VERSION' ) ) {
			return FUSION_WHITE_LABEL_BRANDING_VERSION;
		}

		return null;
	}

	/**
	 * Enqueue any scripts & stylesheets needed.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function admin_scripts() {
		wp_enqueue_style( 'fusion-patcher', FUSION_LIBRARY_URL . '/assets/css/fusion-patcher-style.css', false, time() );

		if ( class_exists( 'Avada' ) ) {
			wp_enqueue_style( 'avada_admin_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/avada-admin.css', [], AVADA_VERSION );
		}

	}

	/**
	 * Check if the product is bundled or not.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param false|string $product If false check the current product.
	 * @return bool
	 */
	public function is_bundled( $product = false ) {
		if ( ! $product ) {
			$product = $this->args['context'];
		}
		return (bool) ( in_array( $product, self::$bundled, true ) );
	}

	/**
	 * Get the $apply_patch private property.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return object
	 */
	public function get_apply_patch() {
		return $this->apply_patch;
	}
	/**
	 * Get the $admin_screen private property.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return object
	 */
	public function get_admin_screen() {
		return $this->admin_screen;
	}

	/**
	 * Get the $patcher_checker private property.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return object
	 */
	public function get_patcher_checker() {
		return $this->patcher_checker;
	}
}

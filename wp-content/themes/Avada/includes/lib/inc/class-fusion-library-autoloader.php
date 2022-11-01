<?php
/**
 * Fusion Library Autoloader.
 * Manages loading other class files.
 *
 * @package Fusion-Library
 */

/**
 * The autoloader class.
 */
class Fusion_Library_Autoloader {

	/**
	 * If there are multiple locations for Fusion-Library,
	 * they are all added here.
	 *
	 * @static
	 * @access private
	 * @var array
	 */
	private static $locations = [];

	/**
	 * An array of the class paths we've located.
	 *
	 * @access private
	 * @var array
	 */
	private $paths = [];

	/**
	 * Since Fusion-Library can be included multiple times
	 * we need this class to be a singleton.
	 * This var holds the one true instance of this object.
	 *
	 * @static
	 * @access private
	 * @var object
	 */
	private static $instance;

	/**
	 * Class path map.
	 *
	 * @static
	 * @access private
	 * @since 2.2.0
	 * @var array
	 */
	private static $path_map;

	/**
	 * Constructor.
	 *
	 * @access private
	 */
	private function __construct() {
		spl_autoload_register( [ $this, 'include_file' ] );
	}

	/**
	 * Gets the one true instance of this class.
	 *
	 * @access public
	 * @return object.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Include the file if found.
	 *
	 * @access private
	 * @param string $class_name The class-name we're looking for.
	 */
	private function include_file( $class_name ) {
		if ( ! isset( $this->paths[ $class_name ] ) ) {
			$this->paths[ $class_name ] = $this->locate_file( $class_name );
		}
		// Only process if file was found.
		// If it doesn't exist, then the locate_file() method returned false.
		if ( $this->paths[ $class_name ] ) {
			include_once $this->paths[ $class_name ];
		}
	}

	/**
	 * Locate the class file
	 *
	 * @access private
	 * @param string $class_name The class-name we're looking for.
	 * @return string|false      If false, file was not located.
	 */
	private function locate_file( $class_name ) {
		// Return false if the class does not start with "Fusion".
		if ( 0 !== stripos( $class_name, 'Fusion' ) ) {
			return false;
		}

		$map = $this->get_class_map();
		if ( isset( $map[ $class_name ] ) ) {
			return $map[ $class_name ];
		}

		// Extrapolate the filename from the class-name.
		$filename = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

		// Go through all instances of the files in case of multiple installations
		// and add their version as key in the array.
		$paths = [];
		foreach ( self::$locations as $version => $location ) {
			$file = wp_normalize_path( $location . '/inc/' . $filename );
			if ( ! file_exists( $file ) ) {
				continue;
			}
			$paths[ $version ] = $file;
		}

		// Reorder versions to make sure newest is first.
		krsort( $paths );

		// This is a pseudo-loop.
		// We're not actually looping though all items here,
		// we're simply returning the 1st element of the array.
		// It only acts as a loop if the 1st element is empty.
		foreach ( $paths as $path ) {
			if ( $path ) {
				// Return 1st (newest version) path.
				return $path;
			}
		}
		return false;
	}

	/**
	 * Add a location to the self::$locations array.
	 *
	 * @static
	 * @access public
	 * @param string $path    The path to add.
	 * @param string $version The fusion-library version.
	 */
	public static function add_location( $path, $version ) {
		if ( ! isset( self::$locations[ $version ] ) ) {
			self::$locations[ $version ] = $path;
		}
	}

	/**
	 * Returns an array of known classnames along with their paths.
	 *
	 * @access public
	 * @since 2.0
	 * @return array
	 */
	public function get_class_map() {
		if ( ! self::$path_map ) {
			self::$path_map = [
				'Fusion'                      => FUSION_LIBRARY_PATH . '/inc/class-fusion.php',
				'Fusion_Images'               => FUSION_LIBRARY_PATH . '/inc/class-fusion-images.php',
				'Fusion_Settings'             => FUSION_LIBRARY_PATH . '/inc/class-fusion-settings.php',
				'Fusion_Multilingual'         => FUSION_LIBRARY_PATH . '/inc/class-fusion-multilingual.php',
				'Fusion_Sanitize'             => FUSION_LIBRARY_PATH . '/inc/class-fusion-sanitize.php',
				'Fusion_Scripts'              => FUSION_LIBRARY_PATH . '/inc/class-fusion-scripts.php',
				'Fusion_Dynamic_JS'           => FUSION_LIBRARY_PATH . '/inc/class-fusion-dynamic-js.php',
				'Fusion_Media_Query_Scripts'  => FUSION_LIBRARY_PATH . '/inc/class-fusion-media-query-scripts.php',
				'Fusion_Font_Awesome'         => FUSION_LIBRARY_PATH . '/inc/class-fusion-font-awesome.php',
				'Fusion_Social_Sharing'       => FUSION_LIBRARY_PATH . '/inc/class-fusion-social-sharing.php',
				'Fusion_Social_Icon'          => FUSION_LIBRARY_PATH . '/inc/class-fusion-social-icon.php',
				'Fusion_Product_Registration' => FUSION_LIBRARY_PATH . '/inc/class-fusion-product-registration.php',
				'Fusion_Updater'              => FUSION_LIBRARY_PATH . '/inc/class-fusion-updater.php',
				'Fusion_WooCommerce'          => FUSION_LIBRARY_PATH . '/inc/class-fusion-woocommerce.php',
				'Fusion_Helper'               => FUSION_LIBRARY_PATH . '/inc/class-fusion-helper.php',
				'Fusion_Social_Icons'         => FUSION_LIBRARY_PATH . '/inc/class-fusion-social-icons.php',
				'Fusion_Dynamic_CSS'          => FUSION_LIBRARY_PATH . '/inc/class-fusion-dynamic-css.php',
				'Fusion_Dynamic_CSS_Helpers'  => FUSION_LIBRARY_PATH . '/inc/class-fusion-dynamic-css-helpers.php',
				'Fusion_Options_Map'          => FUSION_LIBRARY_PATH . '/inc/class-fusion-options-map.php',
				'Fusion_Color'                => FUSION_LIBRARY_PATH . '/inc/class-fusion-color.php',
				'Fusion_Data'                 => FUSION_LIBRARY_PATH . '/inc/class-fusion-data.php',
				'Fusion_Patcher'              => FUSION_LIBRARY_PATH . '/inc/class-fusion-patcher.php',
				'Fusion_Patcher_Apply_Patch'  => FUSION_LIBRARY_PATH . '/inc/class-fusion-patcher-apply-patch.php',
				'Fusion_Patcher_Admin_Screen' => FUSION_LIBRARY_PATH . '/inc/class-fusion-patcher-admin-screen.php',
				'Fusion_Patcher_Checker'      => FUSION_LIBRARY_PATH . '/inc/class-fusion-patcher-checker.php',
				'Fusion_Dynamic_CSS_Inline'   => FUSION_LIBRARY_PATH . '/inc/class-fusion-dynamic-css-inline.php',
				'Fusion_Dynamic_JS_File'      => FUSION_LIBRARY_PATH . '/inc/class-fusion-dynamic-js-file.php',
				'Fusion_Filesystem'           => FUSION_LIBRARY_PATH . '/inc/class-fusion-filesystem.php',
				'Fusion_Panel_Callbacks'      => FUSION_LIBRARY_PATH . '/inc/class-fusion-panel-callbacks.php',
				'Fusion_Featured_Image'       => FUSION_LIBRARY_PATH . '/inc/class-fusion-featured-image.php',
				'Fusion_Breadcrumbs'          => FUSION_LIBRARY_PATH . '/inc/class-fusion-breadcrumbs.php',
				'Fusion_Data_Framework'       => FUSION_LIBRARY_PATH . '/inc/class-fusion-data-framework.php',
				'Fusion_Data_PostMeta'        => FUSION_LIBRARY_PATH . '/inc/class-fusion-data-postmeta.php',
				'Fusion_FusionRedux'          => FUSION_LIBRARY_PATH . '/inc/class-fusion-fusionredux.php',
				'Fusion_Data_TermMeta'        => FUSION_LIBRARY_PATH . '/inc/class-fusion-data-termmeta.php',
				'Fusion_Dynamic_CSS_File'     => FUSION_LIBRARY_PATH . '/inc/class-fusion-dynamic-css-file.php',
				'Fusion_JSON_LD'              => FUSION_LIBRARY_PATH . '/inc/class-fusion-json-ld.php',
			];
		}
		return self::$path_map;
	}
}

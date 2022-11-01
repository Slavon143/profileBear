<?php
/**
 * Autoloader for Avada classes.
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
 * The Autoloader class for Avada.
 */
class Avada_Autoload {

	/**
	 * Hardcoded classmap.
	 *
	 * @static
	 * @access private
	 * @since 6.0
	 * @var array
	 */
	private static $class_map;

	/**
	 * The class constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		// Register our autoloader.
		spl_autoload_register( [ $this, 'include_class_file' ] );
	}

	/**
	 * Gets the path for a specific class-name.
	 *
	 * @access protected
	 * @since 5.0.0
	 * @param string $class_name The class-name we're looking for.
	 * @return false|string      The full path to the class, or false if not found.
	 */
	protected function get_path( $class_name ) {

		// If the class exists in our hardcoded array of classes
		// then get the path and return it immediately.
		if ( ! self::$class_map ) {
			self::$class_map = $this->get_class_map();
		}
		if ( isset( self::$class_map[ $class_name ] ) ) {
			include_once self::$class_map[ $class_name ];
			return;
		}

		$template_dir_path = Avada::$template_dir_path;

		$paths = [];
		if ( 0 === stripos( $class_name, 'Avada' ) || 0 === stripos( $class_name, 'Fusion' ) ) {

			$filename = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

			$paths[] = $template_dir_path . '/includes/' . $filename;

			$substr   = str_replace( [ 'Avada_', 'Fusion_' ], '', $class_name );
			$exploded = explode( '_', $substr );
			$levels   = count( $exploded );

			$previous_path = '';
			for ( $i = 0; $i < $levels; $i++ ) {
				$paths[]        = $template_dir_path . '/includes/' . $previous_path . strtolower( $exploded[ $i ] ) . '/' . $filename;
				$previous_path .= strtolower( $exploded[ $i ] ) . '/';
			}

			foreach ( $paths as $path ) {
				$path = wp_normalize_path( $path );
				if ( file_exists( $path ) ) {
					return $path;
				}
			}
		}
		return false;

	}

	/**
	 * Get the path & include the file for the class.
	 *
	 * @access public
	 * @since 5.0.0
	 * @param string $class_name The class-name we're looking for.
	 * @return void
	 */
	public function include_class_file( $class_name ) {
		$path = $this->get_path( $class_name );

		// Include the path.
		if ( $path ) {
			include_once $path;
		}
	}

	/**
	 * Get a class-map for some standard classes.
	 *
	 * @access public
	 * @since 6.0
	 * @return array
	 */
	public function get_class_map() {
		$template_dir_path = Avada::$template_dir_path;
		return [
			'Fusion_Builder_Redux_Options'    => $template_dir_path . '/includes/class-fusion-builder-redux-options.php',
			'Avada_Upgrade'                   => $template_dir_path . '/includes/class-avada-upgrade.php',
			'Avada_Helper'                    => $template_dir_path . '/includes/class-avada-helper.php',
			'Avada_Upgrade_400'               => $template_dir_path . '/includes/upgrade/class-avada-upgrade-400.php',
			'Avada_Upgrade_Abstract'          => $template_dir_path . '/includes/upgrade/class-avada-upgrade-abstract.php',
			'Avada_AvadaRedux_Migration'      => $template_dir_path . '/includes/class-avada-avadaredux-migration.php',
			'Avada_Migrate'                   => $template_dir_path . '/includes/class-avada-migrate.php',
			'Avada_Upgrade_500'               => $template_dir_path . '/includes/upgrade/class-avada-upgrade-500.php',
			'Fusion_Builder_Migrate'          => $template_dir_path . '/includes/class-fusion-builder-migrate.php',
			'Avada_Upgrade_600'               => $template_dir_path . '/includes/upgrade/class-avada-upgrade-600.php',
			'Avada_Admin'                     => $template_dir_path . '/includes/class-avada-admin.php',
			'Avada_Admin_Bar'                 => $template_dir_path . '/includes/class-avada-admin-bar.php',
			'Avada_Settings'                  => $template_dir_path . '/includes/class-avada-settings.php',
			'Avada_Init'                      => $template_dir_path . '/includes/class-avada-init.php',
			'Avada_Template'                  => $template_dir_path . '/includes/class-avada-template.php',
			'Avada_Blog'                      => $template_dir_path . '/includes/class-avada-blog.php',
			'Avada_Images'                    => $template_dir_path . '/includes/class-avada-images.php',
			'Avada_Head'                      => $template_dir_path . '/includes/class-avada-head.php',
			'Avada_Layout'                    => $template_dir_path . '/includes/class-avada-layout.php',
			'Avada_GoogleMap'                 => $template_dir_path . '/includes/class-avada-googlemap.php',
			'Avada_Remote_Installer'          => $template_dir_path . '/includes/class-avada-remote-installer.php',
			'Avada_Slider_Revolution'         => $template_dir_path . '/includes/class-avada-slider-revolution.php',
			'Avada_Sermon_Manager'            => $template_dir_path . '/includes/class-avada-sermon-manager.php',
			'Avada_Privacy_Embeds'            => $template_dir_path . '/includes/class-avada-privacy-embeds.php',
			'Avada_PWA'                       => $template_dir_path . '/includes/class-avada-pwa.php',
			'Avada_Block_Editor'              => $template_dir_path . '/includes/class-avada-block-editor.php',
			'Avada_Importer_Data'             => $template_dir_path . '/includes/importer/class-avada-importer-data.php',
			'Avada_Multiple_Featured_Images'  => $template_dir_path . '/includes/class-avada-multiple-featured-images.php',
			'Avada_Sidebars'                  => $template_dir_path . '/includes/class-avada-sidebars.php',
			'Avada_Admin_Notices'             => $template_dir_path . '/includes/class-avada-admin-notices.php',
			'Avada_Widget_Style'              => $template_dir_path . '/includes/class-avada-widget-style.php',
			'Avada_Page_Options'              => $template_dir_path . '/includes/class-avada-page-options.php',
			'Avada_Portfolio'                 => $template_dir_path . '/includes/class-avada-portfolio.php',
			'Avada_Scripts'                   => $template_dir_path . '/includes/class-avada-scripts.php',
			'Avada_EventsCalendar'            => $template_dir_path . '/includes/class-avada-eventscalendar.php',
			'Avada_Google_Fonts'              => $template_dir_path . '/includes/class-avada-google-fonts.php',
			'Fusion_Dynamic_CSS_From_Options' => $template_dir_path . '/includes/class-fusion-dynamic-css-from-options.php',
			'Avada_Megamenu_Framework'        => $template_dir_path . '/includes/class-avada-megamenu-framework.php',
			'Avada_Megamenu'                  => $template_dir_path . '/includes/class-avada-megamenu.php',
			'Avada_Nav_Walker_Megamenu'       => $template_dir_path . '/includes/class-avada-nav-walker-megamenu.php',
			'Avada_Dynamic_CSS'               => $template_dir_path . '/includes/class-avada-dynamic-css.php',
			'Avada_Options'                   => $template_dir_path . '/includes/class-avada-options.php',
			'Avada_Output_Callbacks'          => $template_dir_path . '/includes/class-avada-output-callbacks.php',
			'Avada_AvadaRedux'                => $template_dir_path . '/includes/class-avada-avadaredux.php',
			'Fusion_Deprecate_Pyre_PO'        => $template_dir_path . '/includes/class-fusion-deprecate-pyre-po.php',
			'Avada_Server_Rules'              => $template_dir_path . '/includes/class-avada-server-rules.php',
		];
	}
}

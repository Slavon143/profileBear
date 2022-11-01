<?php
/**
 * Bypass the instantiation of Redux in the Avada_AvadaRedux class.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      6.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handle Redux in Avada.
 */
class Avada_AvadaRedux_No_Init extends Avada_AvadaRedux {

	/**
	 * Initializes and triggers all other actions/hooks.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function init_fusionredux() {

		$this->fusion_sections = $this->args['sections'];
		// Add a filter to allow modifying the array.
		$this->fusion_sections = apply_filters( 'fusion_admin_options_injection', $this->fusion_sections );

		self::$is_language_all = $this->args['is_language_all'];

		add_action( 'update_option_' . $this->args['option_name'], [ $this, 'option_name_settings_update' ], 10, 3 );

		$this->key = $this->args['option_name'];

		$version       = $this->args['version'];
		$version_array = explode( '.', $version );

		if ( isset( $version_array[2] ) && '0' === $version_array[2] ) {
			$version = $version_array[0] . '.' . $version_array[1];
		}
		$this->ver = $version;

	}
}

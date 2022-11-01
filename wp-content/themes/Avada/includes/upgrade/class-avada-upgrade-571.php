<?php
/**
 * Upgrades Handler.
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
 * Handle migrations for Avada 5.7.1.
 *
 * @since 5.7.1
 */
class Avada_Upgrade_571 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 5.7.1
	 * @var string
	 */
	protected $version = '5.7.1';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 5.7.1
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 5.7.1
	 * @return void
	 */
	protected function migration_process() {
		delete_option( 'avada_theme_version' );
	}
}

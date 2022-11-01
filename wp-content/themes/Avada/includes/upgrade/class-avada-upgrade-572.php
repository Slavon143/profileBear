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
 * Handle migrations for Avada 5.7.2.
 *
 * @since 5.7.2
 */
class Avada_Upgrade_572 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 5.7.2
	 * @var string
	 */
	protected $version = '5.7.2';

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 5.7.2
	 * @return void
	 */
	protected function migration_process() {}
}

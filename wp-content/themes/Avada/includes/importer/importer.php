<?php
/**
 * Avada Content importer.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Importer
 * @since      5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}


require_once Avada::$template_dir_path . '/includes/importer/avada-import-functions.php';
if ( defined( 'CP_VERSION' ) && fusion_doing_ajax() && isset( $_POST['action'] ) && ( 'fusion_import_demo_data' === $_POST['action'] || 'fusion_remove_demo_data' === $_POST['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
	require_once Avada::$template_dir_path . '/includes/importer/avada-cp-import-functions.php';
}
require_once Avada::$template_dir_path . '/includes/importer/class-avada-demo-import.php';
require_once Avada::$template_dir_path . '/includes/importer/class-avada-demo-remove.php';

new Avada_Demo_Import();

new Avada_Demo_Remove();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

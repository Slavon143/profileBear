<?php
/**
 * Plugins for TGM usage.
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
 * Gets all recommended and required plugins for use in TGM plugin.
 *
 * @since 5.1.6
 */
function avada_get_required_and_recommened_plugins() {
	if ( ! class_exists( 'Avada_Importer_Data' ) ) {
		include_once Avada::$template_dir_path . '/includes/importer/class-avada-importer-data.php';
	}

	$is_plugins_page = ( isset( $_GET['page'] ) && ( 'avada-plugins' === $_GET['page'] || 'install-required-plugins' === $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

	$plugins_info = Avada::get_bundled_plugins();

	if ( is_array( $plugins_info ) ) {
		foreach ( $plugins_info as $plugin ) {
			if ( $plugin['has_package'] ) {
				$plugins_info[ $plugin['slug'] ]['source'] = ( $is_plugins_page ) ? Avada()->remote_install->get_package( $plugin['name'] ) : 'bundled';
			} else {
				$plugins_info[ $plugin['slug'] ]['source'] = 'repo';
			}
		}
	}

	return $plugins_info;
}

/**
 * Require the installation of any required and/or recommended third-party plugins here.
 * See http://tgmpluginactivation.com/ for more details
 */
function avada_register_required_and_recommended_plugins() {

	// Get all required and recommended plugins.
	$plugins = avada_get_required_and_recommened_plugins();

	// Change this to your theme text domain, used for internationalising strings.
	$theme_text_domain = 'Avada';

	/**
	 * Array of configuration settings. Amend each line as needed.
	 * If you want the default strings to be available under your own theme domain,
	 * leave the strings uncommented.
	 * Some of the strings are added into a sprintf, so see the comments at the
	 * end of each line for what each argument will be.
	 */
	$config = [
		'domain'       => $theme_text_domain,
		'default_path' => '',
		'parent_slug'  => 'avada',
		'menu'         => 'avada-plugins',
		'has_notices'  => true,
		'is_automatic' => true,
		'message'      => '',
		'strings'      => [
			'page_title'                      => __( 'Install/Update Required Plugins', 'Avada' ),
			'menu_title'                      => __( 'Install Plugins', 'Avada' ),
			/* translators: %1$s = plugin name(s) */
			'installing'                      => __( 'Installing Plugin: %s', 'Avada' ),
			'oops'                            => __( 'Something went wrong with the plugin API.', 'Avada' ),
			/* translators: %1$s = plugin name(s) */
			'notice_can_install_required'     => _n_noop( 'Avada requires the following plugin installed: %1$s.', 'Avada requires the following plugins installed: %1$s.', 'Avada' ),
			/* translators: %1$s = plugin name(s) */
			'notice_can_install_recommended'  => _n_noop( str_replace( '{{system-status}}', admin_url( 'admin.php?page=avada-status' ), 'This theme recommends the following plugin installed or updated: %1$s.<br />IMPORTANT: If your hosting plan has low resources, activating additional plugins can lead to fatal "out of memory" errors. We recommend at least 128MB of memory. Check your resources on the <a href="{{system-status}}" target="_self">Status</a> page.' ), str_replace( '{{system-status}}', admin_url( 'admin.php?page=avada-status' ), 'Avada recommends the following plugins installed or updated: %1$s.<br />IMPORTANT: If your hosting plan has low resources, activating additional plugins can lead to fatal "out of memory" errors. We recommend at least 128MB of memory. Check your resources on the <a href="{{system-status}}" target="_self">Status</a> page.' ), 'Avada' ), // phpcs:ignore
			/* translators: %1$s = plugin name(s) */
			'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'Avada' ),
			/* translators: %1$s = plugin name(s) */
			'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'Avada' ),
			/* translators: %1$s = plugin name(s) */
			'notice_can_activate_recommended' => _n_noop( str_replace( '{{system-status}}', admin_url( 'admin.php?page=avada-status' ), 'The following recommended plugin is currently inactive: %1$s.<br />IMPORTANT: If your hosting plan has low resources, activating additional plugins can lead to fatal "out of memory" errors. We recommend at least 128MB of memory. Check your resources on the <a href="{{system-status}}" target="_self">Status</a> page.' ), str_replace( '{{system-status}}', admin_url( 'admin.php?page=avada-status' ), 'The following recommended plugins are currently inactive: %1$s.<br />IMPORTANT: If your hosting plan has low resources, activating additional plugins can lead to fatal "out of memory" errors. We recommend at least 128MB of memory. Check your resources on the <a href="{{system-status}}" target="_self">Status</a> page.' ), 'Avada' ), // phpcs:ignore
			/* translators: %1$s = plugin name(s) */
			'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'Avada' ),
			/* translators: %1$s = plugin name(s) */
			'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to ensure maximum compatibility with Avada: %1$s', 'The following plugins need to be updated to ensure maximum compatibility with Avada: %1$s', 'Avada' ),
			/* translators: %1$s = plugin name(s) */
			'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'Avada' ),
			'install_link'                    => _n_noop( 'Go Install Plugin', 'Go Install Plugins', 'Avada' ),
			'activate_link'                   => _n_noop( 'Go Activate Plugin', 'Go Activate Plugins', 'Avada' ),
			'return'                          => __( 'Return to the Avada Plugins page.', 'Avada' ),
			'plugin_activated'                => __( 'Plugin activated successfully.', 'Avada' ),
			/* translators: %1$s = dashboard link. */
			'complete'                        => __( 'All plugins installed and activated successfully. %s', 'Avada' ),
			'nag_type'                        => 'error', // Determines admin notice type - can only be 'updated' or 'error'.
		],
	];

	avada_tgmpa( $plugins, $config );
}
add_action( 'tgmpa_register', 'avada_register_required_and_recommended_plugins' );

/**
 * Returns the user capability for showing the notices.
 *
 * @return string
 */
function avada_tgm_show_admin_notice_capability() {
	return 'switch_themes';
}
add_filter( 'tgmpa_show_admin_notice_capability', 'avada_tgm_show_admin_notice_capability' );

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

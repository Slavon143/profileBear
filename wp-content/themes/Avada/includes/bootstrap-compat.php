<?php
/**
 * WP and PHP compatibility.
 *
 * Functions used to gracefully fail when a theme doesn't meet the minimum WP or PHP versions required.
 * Only call this file after initially checking that the site doesn't meet either the WP or PHP requirement.
 *
 * @package   avada
 * @since 6.0
 */

/**
 * Add actions to fail at certain points in the load process.
 */
add_action( 'after_switch_theme', 'avada_compat_switch_theme' );
add_action( 'load-customize.php', 'avada_compat_load_customize' );
add_action( 'template_redirect', 'avada_compat_preview' );

/**
 * Returns the compatibility messaged based on whether the WP or PHP minimum requirement wasn't met.
 *
 * @since  6.0
 * @access public
 * @return string
 */
function avada_compat_message() {

	if ( version_compare( $GLOBALS['wp_version'], AVADA_MIN_WP_VER_REQUIRED, '<' ) ) {

		return sprintf(
			/* Translators: 1 is the required WordPress version and 2 is the user's current version. */
			esc_html__( 'Avada requires at least WordPress version %1$s. You are running version %2$s. Please upgrade and try again.' ),
			esc_html( AVADA_MIN_WP_VER_REQUIRED ),
			$GLOBALS['wp_version']
		);

	} elseif ( version_compare( PHP_VERSION, AVADA_MIN_PHP_VER_REQUIRED, '<' ) ) {

		return sprintf(
			/* Translators: 1 is the required PHP version and 2 is the user's current version. */
			esc_html__( 'Avada requires at least PHP version %1$s. You are running version %2$s. Please upgrade and try again.' ),
			AVADA_MIN_PHP_VER_REQUIRED,
			PHP_VERSION
		);
	}

	return '';
}

/**
 * Switches to the previously active theme after the theme has been activated.
 *
 * @since  6.0
 * @access public
 * @param  string $old_name  Previous theme name/slug.
 * @return void
 */
function avada_compat_switch_theme( $old_name ) {
	switch_theme( $old_name ? $old_name : WP_DEFAULT_THEME );
	unset( $_GET['activated'] ); // phpcs:ignore WordPress.Security.NonceVerification
	add_action( 'admin_notices', 'avada_compat_upgrade_notice' );
}

/**
 * Outputs an admin notice with the compatibility issue.
 *
 * @since  6.0
 * @return void
 */
function avada_compat_upgrade_notice() {
	printf( '<div class="error"><p>%s</p></div>', esc_html( avada_compat_message() ) );
}

/**
 * Kills the loading of the customizer.
 *
 * @since  6.0
 * @return void
 */
function avada_compat_load_customize() {
	wp_die( esc_html( avada_compat_message() ), '', array( 'back_link' => true ) );
}

/**
 * Kills the customizer previewer on installs prior to WP 4.7.
 *
 * @since  6.0
 * @return void
 */
function avada_compat_preview() {
	if ( isset( $_GET['preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_die( esc_html( avada_compat_message() ) );
	}
}

/**
 * If this is a theme update show the admin notice.
 *
 * @since 6.0
 */
$avada_db_version = get_option( 'avada_version', false );
if ( $avada_db_version ) {
	avada_compat_switch_theme( '' );
}

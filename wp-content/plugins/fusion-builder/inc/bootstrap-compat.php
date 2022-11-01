<?php
/**
 * WP and PHP compatibility.
 *
 * Functions used to gracefully fail when the plugin doesn't meet the minimum WP or PHP versions required.
 * Only call this file after initially checking that the site doesn't meet either the WP or PHP requirement.
 *
 * @package fusion-core
 * @since 2.0
 */

add_action( 'admin_notices', 'fusion_builder_compat_upgrade_notice' );

/**
 * Outputs an admin notice with the compatibility issue.
 *
 * @since  2.0
 * @return void
 */
function fusion_builder_compat_upgrade_notice() {
	echo '<div class="error">';

	if ( version_compare( $GLOBALS['wp_version'], FUSION_BUILDER_MIN_WP_VER_REQUIRED, '<' ) ) {
		printf(
			/* Translators: 1 is the required WordPress version and 2 is the user's current version. */
			'<p>' . esc_html__( 'Avada Builder requires at least WordPress version %1$s. You are running version %2$s. Please upgrade and try again.' ) . '</p>',
			esc_html( FUSION_BUILDER_MIN_WP_VER_REQUIRED ),
			esc_html( $GLOBALS['wp_version'] )
		);
	}

	if ( version_compare( PHP_VERSION, FUSION_BUILDER_MIN_PHP_VER_REQUIRED, '<' ) ) {
		printf(
			/* Translators: 1 is the required PHP version and 2 is the user's current version. */
			'<p>' . esc_html__( 'Avada Builder requires at least PHP version %1$s. You are running version %2$s. Please upgrade and try again.' ) . '</p>',
			esc_html( FUSION_BUILDER_MIN_PHP_VER_REQUIRED ),
			esc_html( PHP_VERSION )
		);
	}
	echo '</div>';
}

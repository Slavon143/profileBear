<?php
/**
 * Apply Avada patches.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage CLI
 * @since      7.3
 *
 * @usage: wp fusion patch apply
 * @todo: make possible to apply only specific patch
 */

$fusion_apply_patches_cmd = function( $args, $assoc_args ) {

	$patches = Fusion_Patcher_Client::get_patches( [] );

	// Make sure we have a unique array.
	$available_patches = array_keys( $patches );
	// Sort the array by value (lowest to highest) and re-index the keys.
	sort( $available_patches );

	foreach ( $available_patches as $key => $patch_id ) {

		// Get an array of the already applied patches.
		$applied_patches = get_site_option( 'fusion_applied_patches', [] );

		// Get an array of patches that failed to be applied.
		$failed_patches = get_site_option( 'fusion_failed_patches', [] );

		// Do not allow applying the patch initially.
		// We'll have to check if they can later.
		$can_apply = false;

		/**
		 * Make sure the patch exists.
		 * if ( ! array_key_exists( $patch_id, $patches ) ) {
		 * continue;
		 * }
		 */

		// Get the patch arguments.
		$patch_args = $patches[ $patch_id ];

		// Has the patch been applied?
		$patch_applied = ( in_array( $patch_id, $applied_patches, true ) );

		// Has the patch failed?
		$patch_failed = ( in_array( $patch_id, $failed_patches, true ) );

		// If there is no previous patch, we can apply it.
		if ( ! isset( $available_patches[ $key - 1 ] ) ) {
			$can_apply = true;
		}

		// If the previous patch exists and has already been applied,
		// then we can apply this one.
		if ( isset( $available_patches[ $key - 1 ] ) ) {
			if ( in_array( $available_patches[ $key - 1 ], $applied_patches, true ) ) {
				$can_apply = true;
			}
		}

		if ( $can_apply && ! $patch_applied ) {
			Fusion_Patcher_Apply_Patch::apply_patch_from_args( $patch_id, Fusion_Patcher_Apply_Patch::format_patch( $patch_args ) );
			WP_CLI::success( '#' . $patch_id . ' patch applied.' );
		} else {
			WP_CLI::log( 'Skip the patch: ' . $patch_id );
		}
	}
};

if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::add_command( 'fusion patch apply', $fusion_apply_patches_cmd );
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

<?php
/**
 * Applies a patch.
 *
 * @package Fusion-Library
 * @subpackage Fusion-Patcher
 */

/**
 * Applies patches.
 *
 * @since 1.0.0
 */
class Fusion_Patcher_Apply_Patch {

	/**
	 * The patch contents.
	 *
	 * @access public
	 * @var bool|array
	 */
	public $setting = false;


	/**
	 * Whether the file-writing was successful or not.
	 *
	 * @access public
	 * @var bool
	 */
	public static $status = true;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param object $patcher The Fusion_Patcher instance.
	 */
	public function __construct( $patcher ) {

		if ( fusion_doing_ajax() ) {
			add_action( 'wp_ajax_awb_apply_patch', [ $this, 'ajax_apply_patch' ] );
			return;
		}

		$is_patcher_page = $patcher->get_args( 'is_patcher_page' );

		if ( null === $is_patcher_page || false === $is_patcher_page ) {
			return;
		}

		// Get patches.
		$patches = Fusion_Patcher_Client::get_patches( $patcher->get_args() );

		// Loop our patches.
		foreach ( $patches as $key => $args ) {

			// Set the $setting property to false.
			// Then run $this->get_setting( $key ) to update the value.
			$this->setting = false;
			$this->get_setting( $key );

			// If $setting property is not false apply the patch.
			if ( false !== $this->setting && ! empty( $this->setting ) ) {
				$this->apply_patch( $key );
			}
		}
	}

	/**
	 * Ajax callback for applying patch.
	 */
	public function ajax_apply_patch() {

		check_ajax_referer( 'awb-bulk-apply-patches', 'awb_patcher_nonce' );

		$patch_id = absint( $_POST['patchID'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		$patches = Fusion_Patcher_Client::get_patches( [] );

		// Make sure we have a unique array.
		$available_patches = array_keys( $patches );
		// Sort the array by value (lowest to highest) and re-index the keys.
		sort( $available_patches );

		// Check if patch is available.
		$key = array_search( $patch_id, $available_patches ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict

		if ( false !== $key ) {

			// Get an array of the already applied patches.
			$applied_patches = get_site_option( 'fusion_applied_patches', [] );

			// Get an array of patches that failed to be applied.
			$failed_patches = get_site_option( 'fusion_failed_patches', [] );

			// Do not allow applying the patch initially.
			// We'll have to check if they can later.
			$can_apply = false;

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
				self::apply_patch_from_args( $patch_id, self::format_patch( $patch_args ) );
				if ( self::$status ) {
					$data = [
						'message'  => 'Patch applied',
						'patch_id' => $patch_id,
					];
					wp_send_json_success( $data, 200 );
				} else {
					$data = [
						'message'  => 'Patch failed',
						'patch_id' => $patch_id,
					];
					wp_send_json_error( $data, 200 );
				}
			} else {
				$data = [
					'message'  => 'Skip the patch',
					'patch_id' => $patch_id,
				];
				wp_send_json_success( $data, 200 );
			}
		}

		die();
	}

	/**
	 * Reformats patch, so it can be passed to apply patch function.
	 *
	 * @param array $patch Patch array.
	 * @return array
	 */
	public static function format_patch( $patch ) {
		global $avada_patcher;

		$patches = [];
		if ( ! isset( $patch['patch'] ) ) {
			return;
		}

		foreach ( $patch['patch'] as $key => $args ) {
			if ( ! isset( $args['context'] ) || ! isset( $args['path'] ) || ! isset( $args['reference'] ) ) {
				continue;
			}

			$product = $avada_patcher->get_args( 'context' );
			$bundled = $avada_patcher->get_args( 'bundled' );
			array_unshift( $bundled, $product );

			foreach ( $bundled as $context ) {
				if ( $context === $args['context'] ) {

					if ( $context === $product ) {
						$v1 = Fusion_Helper::normalize_version( $avada_patcher->get_args( 'version' ) );
					} else {
						$v1 = Fusion_Helper::normalize_version( $avada_patcher->get_bundled_version( $context ) );
					}
					
					$v2 = Fusion_Helper::normalize_version( $args['version'] );

					if ( version_compare( $v1, $v2, '==' ) ) {
						$patches[ $context ][ $args['path'] ] = $args['reference'];
					}
				}
			}
		}

		return $patches;
	}

	/**
	 * Get the setting from the database.
	 * If the setting exists, decode it and set the class's $setting property to an array.
	 *
	 * @access public
	 * @param  int $key The patch ID.
	 * @return void
	 */
	public function get_setting( $key ) {

		// Get the patch contents.
		// This is created when the "apply patch" button is pressed.
		$setting = get_option( 'fusion_patch_contents_' . $key, false );

		// Check we have a value before proceeding.
		if ( false !== $setting && ! empty( $setting ) ) {

			// Decode and prepare tha patch.
			$setting = (array) json_decode( fusion_decode_input( $setting ) );

			// Set the $setting property of the class to the contents of our patch.
			if ( is_array( $setting ) && ! empty( $setting ) ) {
				$this->setting = $setting;
			}
		}
	}

	/**
	 * Applies the patch.
	 * If everything is alright, return true else false.
	 *
	 * @access public
	 * @param  int $key The patch ID.
	 * @return void
	 */
	public function apply_patch( $key ) {

		// Check that the $setting property is properly formatted as an array.
		if ( is_array( $this->setting ) ) {

			// Process the patch.
			foreach ( $this->setting as $target => $args ) {
				$args = (array) $args;
				foreach ( $args as $destination => $source ) {
					$apply_patch  = new Fusion_Patcher_Filesystem( $target, $source, $destination, $key );
					self::$status = (bool) $apply_patch->status;
				}
			}

			// Cleanup.
			$this->remove_setting( $key );
			self::update_applied_patches( $key );
			delete_site_transient( Fusion_Patcher_Checker::$transient_name );
			$fusion_cache = new Fusion_Cache();
			$fusion_cache->reset_all_caches();

		}
	}

	/**
	 * Applies the patch.
	 *
	 * @access public
	 * @param  int   $key The patch ID.
	 * @param  array $patch_args Formatted patch data.
	 * @return void
	 */
	public static function apply_patch_from_args( $key, $patch_args ) {

		// Check that the $setting property is properly formatted as an array.
		if ( is_array( $patch_args ) ) {

			// Process the patch.
			foreach ( $patch_args as $target => $args ) {
				$args = (array) $args;
				foreach ( $args as $destination => $source ) {
					$apply_patch  = new Fusion_Patcher_Filesystem( $target, $source, $destination, $key );
					self::$status = (bool) $apply_patch->status;
				}
			}

			// Cleanup.
			self::update_applied_patches( (int) $key );
			delete_site_transient( Fusion_Patcher_Checker::$transient_name );
			$fusion_cache = new Fusion_Cache();
			$fusion_cache->reset_all_caches();
		}
	}

	/**
	 * Remove the setting from the database.
	 *
	 * @access public
	 * @param  int $key The patch ID.
	 * @return void
	 */
	public function remove_setting( $key ) {
		delete_option( 'fusion_patch_contents_' . $key );
	}

	/**
	 * Update the applied patches array in the db.
	 *
	 * @access public
	 * @param  int $key The patch ID.
	 * @return void
	 */
	public static function update_applied_patches( $key ) {

		// Get an array of existing patches.
		$applied_patches = get_site_option( 'fusion_applied_patches', [] );

		// Get an array of patches that failed to be applied.
		$failed_patches = get_site_option( 'fusion_failed_patches', [] );

		// Add the patch key to the array and save.
		// Save on a different setting depending on whether the patch failed to be applied or not.
		if ( false === self::$status ) {
			// Update the failed patches setting.
			if ( ! in_array( $key, $failed_patches ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				$failed_patches[] = $key;
				$failed_patches   = array_unique( $failed_patches );
				update_site_option( 'fusion_failed_patches', $failed_patches );
			}
			// Update the applied patches setting.
			if ( in_array( $key, $applied_patches ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				$applied_key = array_search( $key, $applied_patches ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				unset( $applied_patches[ $applied_key ] );
				update_site_option( 'fusion_applied_patches', $applied_patches );
			}
			return;
		}
		// If we got this far then the patch has been applied.
		if ( ! in_array( $key, $applied_patches ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			$applied_patches[] = $key;
			$applied_patches   = array_unique( $applied_patches );
			update_site_option( 'fusion_applied_patches', $applied_patches );

			// If the current patch is in the array of failed patches, remove it.
			if ( in_array( $key, $failed_patches ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				$failed_key = array_search( $key, $failed_patches ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				unset( $failed_patches[ $failed_key ] );
				update_site_option( 'fusion_failed_patches', $failed_patches );
			}
		}
		// Remove messages if they exist.
		$messages_option = get_site_option( Fusion_Patcher_Admin_Notices::$option_name );
		$patches         = Fusion_Patcher_Client::get_patches();
		if ( isset( $patches[ $key ] ) ) {
			foreach ( $patches[ $key ]['patch'] as $patch ) {
				$message_id = 'write-permissions-' . $patch['context'];
				if ( isset( $messages_option[ $message_id ] ) ) {
					unset( $messages_option[ $message_id ] );
					update_site_option( Fusion_Patcher_Admin_Notices::$option_name, $messages_option );
				}
			}
		}

		Fusion_Patcher_Checker::reset_cache();
	}
}

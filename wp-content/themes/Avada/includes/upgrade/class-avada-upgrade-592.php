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
 * Handle migrations for Avada 5.9.2.
 *
 * @since 5.9.2
 */
class Avada_Upgrade_592 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 5.9.2
	 * @var string
	 */
	protected $version = '5.9.2';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 5.9.2
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 5.9.2
	 * @return void
	 */
	protected function migration_process() {
		$available_languages       = Fusion_Multilingual::get_available_languages();
		self::$available_languages = ( ! empty( $available_languages ) ) ? $available_languages : [ '' ];

		$this->migrate_options();
		$this->migrate_po_datasets();
	}

	/**
	 * Migrate options.
	 *
	 * @access protected
	 * @since 5.9.2
	 * @return void
	 */
	protected function migrate_options() {
		$this->migrate_registration_data();
	}

	/**
	 * Migrate the Avada registration data.
	 *
	 * @access protected
	 * @since 5.9.2
	 * @return void
	 */
	protected function migrate_registration_data() {
		$registered        = get_option( 'fusion_registered' );
		$tokens            = get_option( 'fusion_registration' );
		$registration_data = [];

		if ( is_array( $tokens ) ) {
			foreach ( $tokens as $product => $token ) {
				$registration_data[ $product ]             = [];
				$registration_data[ $product ]['token']    = $token['token'];
				$registration_data[ $product ]['is_valid'] = array_key_exists( $product, $registered ) ? $registered[ $product ] : false;
				if ( array_key_exists( 'scopes', $registered ) && array_key_exists( $product, $registered['scopes'] ) ) {
					$registration_data[ $product ]['scopes'] = $registered['scopes'][ $product ];
				}
			}
		}

		update_option( 'fusion_registration_data', $registration_data );
		delete_option( 'fusion_registered' );
		delete_option( 'fusion_registration' );
	}

	/**
	 * Migrate the saved page-options CPT to an option.
	 *
	 * @access protected
	 * @since 5.9.2
	 * @return void
	 */
	protected function migrate_po_datasets() {

		// Get all saved page-options from the "avada_page_options" CPT.
		$saved_page_options = get_posts(
			[
				'post_type'      => 'avada_page_options',
				'posts_per_page' => -1, // phpcs:ignore WPThemeReview.CoreFunctionality.PostsPerPage
			]
		);

		// Get already migrated page-options-data from the options.
		$saved_options = get_option( 'avada_page_options', [] );

		// Only run if the "avada_page_options" option is empty - in which case this has never run before.
		if ( empty( $saved_options ) ) {
			foreach ( $saved_page_options as $page_option ) {

				// Get data.
				$custom_fields = get_post_meta( $page_option->ID, 'fusion_page_options', true );
				$id            = md5( $page_option->post_title . wp_json_encode( $custom_fields ) );

				// Add data to the $saved_options array.
				$saved_options[] = [
					'id'    => $id,
					'title' => $page_option->post_title,
					'data'  => $custom_fields,
				];
			}

			// Update the option.
			update_option( 'avada_page_options', $saved_options );
		}
	}
}

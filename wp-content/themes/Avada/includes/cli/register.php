<?php
/**
 * Register Avada theme.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage CLI
 * @since      7.4
 *
 * @usage: wp fusion register --purchase_code=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
 */

$fusion_register_cmd = function( $args, $assoc_args ) {
	set_transient( 'awb_cli_activation', 1 );

	// TODO: improve format check.
	if ( empty( $assoc_args['purchase_code'] ) || 32 === strlen( $assoc_args['purchase_code'] ) ) {
		WP_CLI::error( 'Invalid purchase code.' );
		return;
	}

	$purchase_code = wp_strip_all_tags( trim( $assoc_args['purchase_code'] ) );
	$is_valid      = Avada()->registration->check_purchase( $purchase_code );
	$error         = Avada()->registration->get_errors();

	// Exit if purchase wasn't verified.
	if ( ! $is_valid ) {
		$error_message = is_wp_error( $error ) ? $error->get_error_message() : 'Something went wrong';
		WP_CLI::error( $error_message );
	}

	$registration_data['avada']['purchase_code'] = $purchase_code;
	$registration_data['avada']['is_valid']      = $is_valid;
	$registration_data['avada']['errors']        = $error;

	// Update saved product data.
	Avada()->registration->cli_update_data( $registration_data );

	WP_CLI::success( 'Avada registered successfully.' );
};

if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::add_command( 'fusion register', $fusion_register_cmd );
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

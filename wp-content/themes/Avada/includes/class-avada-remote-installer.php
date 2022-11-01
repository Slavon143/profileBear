<?php
/**
 * Handles remotely installing premium plugins.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Remote installer for premium plugins.
 * This only works with our custom server plugin.
 *
 * @since 5.0.0
 */
class Avada_Remote_Installer {

	/**
	 * The remote API URL.
	 *
	 * @access private
	 * @var string
	 */
	private $api_url;

	/**
	 * The constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->api_url = trailingslashit( FUSION_UPDATES_URL );
	}

	/**
	 * Gets the download URL for a plugin.
	 *
	 * @access public
	 * @since 5.0.0
	 * @param string       $download The plugin to download.
	 * @param string|false $token    Force-use a token, or use default if false.
	 * @return string|false
	 */
	public function get_package( $download, $token = false ) {

		$api_args = [
			'avada_action' => 'get_download',
			'item_name'    => rawurlencode( $download ),
			'ver'          => Avada::get_theme_version(),
		];

		if ( Avada()->registration->is_registered() ) {
			$api_args['code'] = Avada()->registration->get_purchase_code();
		} elseif ( Avada()->registration->legacy_support() ) {
			$api_args['token'] = Avada()->registration->get_token();
		} elseif ( ! Avada()->registration->bypass_active() ) {
			return false;
		}

		return add_query_arg( $api_args, $this->api_url );
	}

	/**
	 * Gets the download URL for a plugin.
	 *
	 * @since 5.3
	 * @access public
	 * @return bool True if subscription code is valid, false otherwise.
	 */
	public function validate_envato_hosted_subscription_code() {
		return true;
	}
}

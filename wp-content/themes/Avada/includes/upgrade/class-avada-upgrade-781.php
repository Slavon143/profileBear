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
 * Handle migrations for Avada 7.8.1
 *
 * @since 7.8.1
 */
class Avada_Upgrade_781 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 7.8
	 * @var string
	 */
	protected $version = '7.8.1';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 7.8.1
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 7.8.1
	 * @return void
	 */
	protected function migration_process() {
		$available_languages       = Fusion_Multilingual::get_available_languages();
		self::$available_languages = ( ! empty( $available_languages ) ) ? $available_languages : [ '' ];

		$this->change_mail_chimp_from_transient_to_option();
	}

	/**
	 * Changes MailChimp from transient to option.
	 *
	 * @access protected
	 * @since 7.8.1
	 * @return void
	 */
	protected function change_mail_chimp_from_transient_to_option() {
		$mailchimp_token = get_transient( 'fusion_mailchimp_token' );
		$mailchimp_dc    = get_transient( 'fusion_mailchimp_dc' );

		if ( $mailchimp_token && ! get_option( 'fusion_mailchimp_token' ) ) {
			update_option( 'fusion_mailchimp_token', $mailchimp_token );
			update_option( 'fusion_mailchimp_dc', $mailchimp_dc );
		}
	}
}

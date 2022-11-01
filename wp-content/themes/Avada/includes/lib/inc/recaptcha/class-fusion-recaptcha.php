<?php
/**
 * A proxy class for recaptche.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada Core
 * @subpackage Core
 * @since      3.9.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * A proxy class for reCAPTCHA.
 *
 * @since 3.9.2
 */
class Fusion_ReCaptcha {

	/**
	 * The reCAPTCHA object.
	 *
	 * @access public
	 * @since 3.9.2
	 * @var object reCAPTCHA
	 */
	public $recaptcha;

	/**
	 * Class constructor.
	 *
	 * @param string $secret         The secret that will be passed-on to reCAPTCHA.
	 * @param null   $request_method Not currently used.
	 */
	public function __construct( $secret, $request_method = null ) {

		if ( ! ini_get( 'allow_url_fopen' ) ) {
			$this->recaptcha = new \ReCaptcha\ReCaptcha( $secret, new \ReCaptcha\RequestMethod\CurlPost() );
		} else {
			$this->recaptcha = new \ReCaptcha\ReCaptcha( $secret );
		}
	}
}

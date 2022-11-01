<?php
/**
 * Fusion Device Detection Class.
 *
 * @package Fusion-Library
 * @since 3.4
 */

/**
 * A collection of device detection methods.
 */
class Fusion_Device_Detection {

	/**
	 * Hold the namespace class.
	 *
	 * @since 3.4
	 * @var object
	 */
	public $detection;

	/**
	 * Class constructor.
	 *
	 * @since 3.4
	 * @return void
	 */
	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'load_dependencies' ] );
	}

	/**
	 * Load Dependencies.
	 *
	 * @since 3.4
	 * @return void
	 */
	public function load_dependencies() {
		if ( ! class_exists( 'Automattic\Jetpack\Device_Detection' ) ) {
			include_once FUSION_LIBRARY_PATH . '/inc/jetpack-device-detection/class-user-agent-info.php';
			include_once FUSION_LIBRARY_PATH . '/inc/jetpack-device-detection/class-device-detection.php';

			$this->detection = 'FusionLibrary\Device_Detection';
		} else {
			$this->detection = 'Automattic\Jetpack\Device_Detection';
		}
	}

	/**
	 * Returns is mobile.
	 *
	 * @access public
	 * @since 3.4
	 * @return boolean
	 */
	public function is_mobile() {
		return apply_filters( 'awb_device_detection_is_mobile', call_user_func( [ $this->detection, 'is_phone' ] ) );
	}

	/**
	 * Returns is tablet.
	 *
	 * @access public
	 * @since 3.4
	 * @return boolean
	 */
	public function is_tablet() {
		return apply_filters( 'awb_device_detection_is_tablet', call_user_func( [ $this->detection, 'is_tablet' ] ) );
	}
}

/* Omit closing PHP tag to avoid 'Headers already sent' issues. */

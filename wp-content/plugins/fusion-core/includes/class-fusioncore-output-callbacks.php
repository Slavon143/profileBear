<?php
/**
 * Output callbacks for options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since 6.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * A wrapper for static methods.
 */
class FusionCore_Output_Callbacks {

	/**
	 * Callback for the portfolio_load_more_posts_button_bg_color option.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @param string $value The value.
	 * @return string
	 */
	public static function portfolio_load_more_posts_button_bg_color_alpha( $value ) {
		return Fusion_Color::new_color( $value )->get_new( 'alpha', .8 )->to_css( 'rgba' );
	}

	/**
	 * Callback for the portfolio_load_more_posts_button_bg_color option.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @param string $value The value.
	 * @return string
	 */
	public static function portfolio_load_more_posts_button_bg_color_readable( $value ) {
		return ( 140 < Fusion_Color::new_color( $value )->brightness['level'] ) ? '#333' : '#fff';
	}
}

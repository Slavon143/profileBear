<?php
/**
 * Enqueues a JavaScript file with some WP Featherlight plugin enhancements
 *
 * In this example, a small JavaScript file is used to enhance the captions displayed
 * by the WP Featherlight lightbox/gallery plugin (https://wordpress.org/plugins/wp-featherlight/).
 *
 * To activate the lightbox nd enhanced captions, add the following parameter to your [mla_gallery] shortcode:
 *
 * mla_link_attributes='data-featherlight="image" mla-caption="{+post_excerpt,attr+}"'
 *
 * Created for support topic "limit caption length"
 * opened on 3/7/2018 by "customle".
 * https://wordpress.org/support/topic/limit-caption-length/
 *
 * @package MLA JavaScript Example
 * @version 1.01
 */

/*
Plugin Name: MLA JavaScript Example
Plugin URI: http://davidlingren.com/
Description: Enqueues a JavaScript file with some WP Featherlight plugin enhancements
Author: David Lingren
Version: 1.01
Author URI: http://davidlingren.com/

Copyright 2018 David Lingren

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You can get a copy of the GNU General Public License by writing to the
	Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
*/

/**
 * Class MLA Copy Item Example adds a "Copy" bulk action and makes copies of existing items.
 *
 * @package MLA Copy Item Example
 * @since 1.00
 */
class MLAJavaScriptExample {
	/**
	 * Uniquely identifies the example plugin
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const MLA_PLUGIN_SLUG = 'mla-javascript-example';

	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 1.00
	 */
	public static function initialize() {
		// The filters are only useful in the front end section
		if ( is_admin() )
			return;

		// Defined in /wp-content/plugins/wp-featherlight/includes/class-scripts.php
 		add_filter( 'wp_featherlight_captions', 'MLAJavaScriptExample::wp_featherlight_captions', 10, 1 );

		// Defined in /wp-includes/script-loader.php
 		add_action( 'wp_enqueue_scripts', 'MLAJavaScriptExample::wp_enqueue_scripts', 10, 0 );
	}

	/**
	 * Disable WP Featherlight's lightbox captions
	 *
	 * @since 1.00
	 *
	 * @return	boolean	true to allow captions, false to disable them
	 */
	public static function wp_featherlight_captions( $captions ) {
		return false;
	}

	/**
	 * Load the plugin's Style Sheet and Javascript files
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function wp_enqueue_scripts() {
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		/* This code loads CSS style sheet(s), if needed * /
		global $wp_locale;
		if ( $wp_locale->is_rtl() ) {
			wp_register_style( self::MLA_PLUGIN_SLUG . '-css', plugin_dir_url( __FILE__ ) . 'mla-javascript-rtl.css', false, MLACore::CURRENT_MLA_VERSION );
		} else {
			wp_register_style( self::MLA_PLUGIN_SLUG . '-css', plugin_dir_url( __FILE__ ) . 'mla-javascript.css', false, MLACore::CURRENT_MLA_VERSION );
		}

		wp_enqueue_style( self::MLA_PLUGIN_SLUG . '-css' );
		// */

		wp_enqueue_script( self::MLA_PLUGIN_SLUG . '-js', plugin_dir_url( __FILE__ ) . "mla-javascript{$suffix}.js", 
			array( 'jquery' ), MLACore::CURRENT_MLA_VERSION, false );

		$script_variables = array(
			'fullCaptionId' => 'mla-caption',
		);

		wp_localize_script( self::MLA_PLUGIN_SLUG . '-js', 'mla_javascript_example_vars', $script_variables );
	} // wp_enqueue_scripts
} // Class MLAJavaScriptExample

// Install the filters at an early opportunity
add_action('init', 'MLAJavaScriptExample::initialize');
?>
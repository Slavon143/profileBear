<?php
/**
 * RevSlider template.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.1
 */

if ( function_exists( 'add_revslider' ) ) {
	add_revslider( $name );
} elseif ( function_exists( 'putRevSlider' ) ) { // Slider Revolution below 6.0.
	putRevSlider( $name );
} elseif ( function_exists( 'rev_slider_shortcode' ) ) {
	echo do_shortcode( '[rev_slider alias="' . $name . '" /]' );
}

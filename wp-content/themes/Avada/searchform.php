<?php
/**
 * The search-form template.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

$config = [
	'design'      => Avada()->settings->get( 'search_form_design' ),
	'live_search' => Avada()->settings->get( 'live_search' ),
];

Fusion_Searchform::get_form( $config );


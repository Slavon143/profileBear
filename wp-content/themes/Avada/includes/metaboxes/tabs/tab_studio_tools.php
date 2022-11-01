<?php
/**
 * Studio Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

/**
 * Studio Page Settings.
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_studio_tools( $sections ) {
	$sections['studio_tools'] = [
		'label'    => esc_attr__( 'Studio Tools', 'Avada' ),
		'id'       => 'studio_tools',
		'alt_icon' => 'fusiona-customize',
		'fields'   => [],
	];

	return $sections;
}

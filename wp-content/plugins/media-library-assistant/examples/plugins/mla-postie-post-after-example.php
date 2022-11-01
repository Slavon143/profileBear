<?php
/**
 * Applies MLA mapping rules to child attachments after Postie creates a parent post from an email
 *
 * Created for support topic "Plugin ‘MLA Postie Post After Example’"
 * opened on  12/7/2020 by "ernstwg":
 * https://wordpress.org/support/topic/plugin-mla-simple-mapping-hooks-example/
 *
 * @package MLA Postie Post After Example
 * @version 1.00
 */

/*
Plugin Name: MLA Postie Post After Example
Plugin URI: http://davidlingren.com/
Description: Applies MLA mapping rules to child attachments after Postie creates a parent post from an email
Author: David Lingren
Version: 1.00
Author URI: http://davidlingren.com/

Copyright 2020 David Lingren

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
 * Class MLA Postie Post After Example hooks the Postie 'postie_post_after' action
 *
 * @package MLA Postie Post After Example
 * @since 1.00
 */
class MLAPostiePostAfterExample {
	/**
	 * Initialization function, similar to __construct()
	 *
	 * Installs filters and actions that handle the MLA hooks for uploading and mapping.
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		// This plugin requires both MLA and Postie
		if ( class_exists( 'MLACore', false ) && class_exists( 'Postie', false ) ) {
			add_action( 'postie_post_after', 'MLAPostiePostAfterExample::postie_post_after', 10, 1 );
		}
	}

	/**
	 * PostieMessage::save_post() action
	 *
	 * Signals end of Postie post insertion.
	 *
	 * @since 1.00
	 *
	 * @param array $details    An array of slashed, sanitized, and processed attachment post data.
	 */
	public static function postie_post_after( $details ) {
		global $wpdb;

		// Build an array of SQL clauses to find Parent/Child relationships
		$query = array();
		$query_parameters = array();

		$query[] = "SELECT p.ID FROM {$wpdb->posts} AS p";
		
		// INNER JOIN removes posts with no attachments
		$query[] = "INNER JOIN {$wpdb->posts} as p2";
		$query[] = "ON (p.post_parent = p2.ID)";

		$query[] = "WHERE p2.post_type = '" . $details['post_type'] . "'";
		$query[] = "AND p2.post_status = '" . $details['post_status'] . "'";
		
		$placeholders = array( '%d' );;
		$query_parameters[] = $details['ID'];
		$query[] = 'AND ( p.post_parent IN (' . join( ',', $placeholders ) . ') )';

		$query[] = "AND p.post_type = 'attachment'";
		$query[] = "AND p.post_status = 'inherit'";

		$query =  join(' ', $query);
		$results = $wpdb->get_results( $wpdb->prepare( $query, $query_parameters ) );

		foreach ( $results as $result ) {
			$item_id = (integer) $result->ID;
			
			do_action( 'mla_begin_mapping', 'single_custom', $item_id );
			$updates = MLAOptions::mla_evaluate_custom_field_mapping( $item_id, 'single_attachment_mapping' );
			do_action( 'mla_end_mapping' );

			if ( !empty( $updates ) ) {
				$item_content = MLAData::mla_update_single_item( $item_id, $updates );
			}

			$item = get_post( $item_id );
			do_action( 'mla_begin_mapping', 'single_iptc_exif', $item_id );
			$updates = MLAOptions::mla_evaluate_iptc_exif_mapping( $item, 'iptc_exif_mapping' );
			do_action( 'mla_end_mapping' );

			if ( !empty( $updates ) ) {
				$item_content = MLAData::mla_update_single_item( $item_id, $updates );
			}
		} // foreach child
	} // postie_post_after
} //MLAPostiePostAfterExample

// Install the filters at an early opportunity
add_action('init', 'MLAPostiePostAfterExample::initialize');
?>
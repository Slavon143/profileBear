<?php
/**
 * Implements a "filename_issues" custom data source to identify potential duplicate files/Media Library items
 *
 * Created for support topic "Find media duplicates based on file name"
 * opened on 9/7/2020 by "bkwineper":
 * https://wordpress.org/support/topic/find-media-duplicates-based-on-file-name/
 *
 * @package MLA Filename Issues Example
 * @version 1.00
 */

/*
Plugin Name: MLA Filename Issues Example
Plugin URI: http://davidlingren.com/
Description: Implements a "filename_issues" custom data source to identify potential duplicate files/Media Library items
Author: David Lingren
Version: 1.00
Author URI: http://davidlingren.com/

Copyright 2021 David Lingren

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
 * Class MLA Filename Issues Example hooks four of the filters provided
 * by the "Field-level substitution parameter filters (Hooks)"
 *
 * Call it anything you want, but give it an unlikely and hopefully unique name. Hiding
 * everything else inside a class means this is the only name you have to worry about.
 *
 * @package MLA Filename Issues Example
 * @since 1.00
 */
class MLAFilenameIssuesExample {
	/**
	 * Initialization function, similar to __construct()
	 *
	 * Installs filters and actions that handle the MLA hooks for the
	 * "Field-level substitution parameters"
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		// Defined in /media-library-assistant/includes/class-mla-data.php
		add_filter( 'mla_expand_custom_data_source', 'MLAFilenameIssuesExample::mla_expand_custom_data_source', 10, 9 );
		add_filter( 'mla_evaluate_custom_data_source', 'MLAFilenameIssuesExample::mla_evaluate_custom_data_source', 10, 5 );
	} // initialize

	/**
	 * Cache root names and counts of items having that root name
	 *
	 * @since 1.00
	 *
	 * @var	array	Item counts for the root name ( rootname => count )
	 */
	private static $rootname_counts = NULL;

	/**
	 * Create root name cache
	 *
	 * @since 1.00
	 *
	 * @param	boolean	$force_refresh True to ignore & refresh the cache
	 */
	private static function _evaluate_root_names( $force_refresh = false ) {
		global $wpdb;

		if ( false === $force_refresh && is_array( self::$rootname_counts ) ) {
			return;
		}
		
		$results = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.meta_key = '_wp_attached_file'" );

		self::$rootname_counts = array();
		foreach( $results as $result ) {
			$rootname = self::_find_root_name( $result->meta_value );
			
			if ( isset( self::$rootname_counts[ $rootname ] ) ) {
				self::$rootname_counts[ $rootname ]++;
			} else {
				self::$rootname_counts[ $rootname ] = 1;
			}
		}
		
		foreach( self::$rootname_counts as $rootname => $count ) {
			if ( 1 === $count ) {
				unset( self::$rootname_counts[ $rootname ] );
			}
		}
//error_log( __LINE__ . " MLAFilenameIssuesExample::_evaluate_root_names rootnames = " . var_export( self::$rootname_counts, true ), 0 );
	}

	/**
	 * Evaluate filename issues
	 *
	 * @since 1.00
	 *
	 * @param	integer $post_id ID of the Media Library item
	 *
	 * @return	string Root name and count if ( count > 1 ) else empty string
	 */
	private static function _evaluate_filename_issues( $post_id ) {
		$attached_file = get_post_meta( $post_id, '_wp_attached_file', true );
		
		if ( false === $attached_file ) {
			return 'INVALID ID';
		} elseif ( empty( $attached_file ) ) {
			return 'NO ATTACHED FILE';
		}
		
		$rootname = self::_find_root_name( $attached_file );
		
		if ( isset( self::$rootname_counts[ $rootname ] ) ) {
			return $rootname . ' (' . self::$rootname_counts[ $rootname ] . ')';
		}
		
		return '';
	}

	/**
	 * Find the root portion of a file name
	 *
	 * @since 1.00
	 *
	 * @param	string $filename Path and file name
	 *
	 * @return	string Root portion of $filename
	 */
	private static function _find_root_name( $filename ) {
		$rootname = strtolower( pathinfo( $filename, PATHINFO_FILENAME ) );
//error_log( __LINE__ . " MLAFilenameIssuesExample::_find_root_name( {$filename} ) rootname = " . var_export( $rootname, true ), 0 );
		
		// Remove abc-scaled suffix
		$scaled_pos = strpos( $rootname, '-scaled' );
		if ( $scaled_pos === ( strlen( $rootname ) - 7 ) ) {
			$rootname = substr( $rootname, 0, $scaled_pos );
//error_log( __LINE__ . " MLAFilenameIssuesExample::_find_root_name rootname = " . var_export( $rootname, true ), 0 );
		}
		
		// Remove -digit(s) suffix
		$rootname = preg_replace( '/-[0-9]+$/', '', $rootname );
//error_log( __LINE__ . " MLAFilenameIssuesExample::_find_root_name rootname = " . var_export( $rootname, true ), 0 );

		// Remove (single) digit suffix (old WordPress convention; won't handle 10+ duplicates)
		$rootname = preg_replace( '/[0-9]$/', '', $rootname );
//error_log( __LINE__ . " MLAFilenameIssuesExample::_find_root_name rootname = " . var_export( $rootname, true ), 0 );
		
		return $rootname;
	}

	/**
	 * MLA Expand Custom Data Source Filter
	 *
	 * For shortcode and Content Template processing, gives you an opportunity to generate a custom data value.
	 *
	 * @since 1.00
	 *
	 * @param	string	NULL, indicating that by default, no custom value is available
	 * @param	string	the entire data-source text including option/format and any arguments 
	 * @param	string	the data-source name 
	 * @param	array	data-source components; prefix (empty), value, option, format and args (if present)
	 * @param	array	values from the query, if any, e.g. shortcode parameters
	 * @param	array	item-level markup template values, if any
	 * @param	integer	attachment ID for attachment-specific values
	 * @param	boolean	for option 'multi', retain existing values
	 * @param	string	default option value
	 */
	public static function mla_expand_custom_data_source( $custom_value, $key, $candidate, $value, $query, $markup_values, $post_id, $keep_existing, $default_option ) {
		// Uncomment the error_log statements in any of the filters to see what's passed in
		//error_log( __LINE__ . " MLAFilenameIssuesExample::mla_expand_custom_data_source( {$key}, {$candidate}, {$post_id}, {$keep_existing}, {$default_option} ) value = " . var_export( $value, true ), 0 );
		//error_log( __LINE__ . " MLAFilenameIssuesExample::mla_expand_custom_data_source( {$candidate}, {$post_id} ) query = " . var_export( $query, true ), 0 );
		//error_log( __LINE__ . " MLAFilenameIssuesExample::mla_expand_custom_data_source( {$candidate}, {$post_id} ) markup_values = " . var_export( $markup_values, true ), 0 );

		if ( 'filename_issues' !== $key ) {
			return $custom_value;
		}
		
		self::_evaluate_root_names();
		
		return self::_evaluate_filename_issues( $post_id );
	} // mla_expand_custom_data_source

	/**
	 * MLA Evaluate Custom Data Source Filter
	 *
	 * For metadata mapping rules, gives you an opportunity to generate a custom data value.
	 *
	 * @since 1.00
	 *
	 * @param	string	NULL, indicating that by default, no custom value is available
	 * @param	integer	attachment ID for attachment-specific values
	 * @param	string 	category/scope to evaluate against: custom_field_mapping or single_attachment_mapping
	 * @param	array	data source specification ( name, *data_source, *keep_existing, *format, mla_column, quick_edit, bulk_edit, *meta_name, *option, no_null )
	 * @param	array 	_wp_attachment_metadata, default NULL (use current postmeta database value)
	 */
	public static function mla_evaluate_custom_data_source( $custom_value, $post_id, $category, $data_value, $attachment_metadata ) {
		//error_log( __LINE__ . " MLAFilenameIssuesExample::mla_expand_custom_data_source( {$post_id}, {$category} ) data_value = " . var_export( $data_value, true ), 0 );
		//error_log( __LINE__ . " MLAFilenameIssuesExample::mla_expand_custom_data_source( {$post_id}, {$category} ) attachment_metadata = " . var_export( $attachment_metadata, true ), 0 );

		if ( 'filename_issues' !== $data_value['data_source'] ) {
			return $custom_value;
		}
		
		self::_evaluate_root_names();
		
		return self::_evaluate_filename_issues( $post_id );
	} // mla_evaluate_custom_data_source
} //MLAFilenameIssuesExample

/*
 * Install the filters at an early opportunity
 */
add_action('init', 'MLAFilenameIssuesExample::initialize');
?>
<?php
/**
 * Extends the Media/Assistant "Search Media" box to "parent" post/page values
 *
 * In this example, a "parent:" prefix is detected in the Media/Assistant "search media" text
 * box and the Media Manager Modal (popup) Window. The search is modified to query posts and
 * pages for matching objects. Then, the ID values of the matching objects are used to find
 * Media Library items by matching the post_parent database field.
 *
 * This example plugin uses four of the many filters available in the Media/Assistant Submenu
 * and illustrates some of the techniques you can use to customize the submenu table display.
 *
 * Created for support topic "Search wordpress media library by attached post title"
 * opened on 2/11/2018 by "norbou".
 * https://wordpress.org/support/topic/search-wordpress-media-library-by-attached-post-title/
 *
 * @package MLA Parent Search Example
 * @version 1.02
 */

/*
Plugin Name: MLA Parent Search Example
Plugin URI: http://davidlingren.com/
Description: Extends the Media/Assistant "Search Media" box the parent elements of attached items
Author: David Lingren
Version: 1.02
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
 * Class MLA Parent Search Example extends the Media/Assistant "Search Media" box
 * to custom field values
 *
 * @package MLA Parent Search Example
 * @since 1.00
 */
class MLAParentSearchExample {
	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		// The filters are only useful for the admin section; exit in the front-end posts/pages
		if ( ! is_admin() )
			return;

		// Defined in /media-library-assistant/includes/class-mla-main.php
		add_filter( 'mla_list_table_new_instance', 'MLAParentSearchExample::mla_list_table_new_instance', 10, 1 );

		// Defined in /media-library-assistant/includes/class-mla-data.php
		add_filter( 'mla_list_table_query_final_terms', 'MLAParentSearchExample::mla_list_table_query_final_terms', 10, 1 );

		// Defined in /media-library-assistant/includes/class-mla-media-modal.php
		add_filter( 'mla_media_modal_query_initial_terms', 'MLAParentSearchExample::mla_media_modal_query_initial_terms', 10, 2 );

		// Defined in /media-library-assistant/includes/class-mla-data.php
		add_filter( 'mla_media_modal_query_final_terms', 'MLAParentSearchExample::mla_media_modal_query_final_terms', 10, 1 );
	}

	/**
	 * Extend the MLA_List_Table class
	 *
	 * This filter gives you an opportunity to extend the MLA_List_Table class.
	 * You can also use this filter to inspect or modify any of the $_REQUEST arguments.
	 *
	 * @since 1.00
	 *
	 * @param	object	$mla_list_table NULL, to indicate no extension/use the base class.
	 *
	 * @return	object	updated mla_list_table object.
	 */
	public static function mla_list_table_new_instance( $mla_list_table ) {
		/*
		 * Look for the special "parent:" prefix in the Search Media text box,
		 * after checking for the "debug" prefixes.
		 */
		if ( isset( $_REQUEST['s'] ) ) {
			switch ( substr( $_REQUEST['s'], 0, 3 ) ) {
				case '}|{':
					self::$parent_search_parameters['debug'] = 'console';
					$start = 3;
					break;
				case '{|}':
					self::$parent_search_parameters['debug'] = 'log';
					$start = 3;
					break;
				default:
					$start = 0;
			}

			if ( 'parent:' == substr( $_REQUEST['s'], $start, 7 ) ) {
				self::$parent_search_parameters['s'] = substr( $_REQUEST['s'], $start + 7 );
				unset( $_REQUEST['s'] );
				self::$parent_search_parameters['mla_search_connector'] = $_REQUEST['mla_search_connector'];
				unset( $_REQUEST['mla_search_connector'] );
				self::$parent_search_parameters['mla_search_fields'] = $_REQUEST['mla_search_fields'];
				unset( $_REQUEST['mla_search_fields'] );

				// alt-text is meaningless for posts and pages
				$alt_index = array_search( 'alt-text', self::$parent_search_parameters['mla_search_fields'] );
				if ( false !== $alt_index ) {
					unset( self::$parent_search_parameters['mla_search_fields'][ $alt_index ] );
				}
			} else {
				self::$parent_search_parameters = array();
			}
		} // isset s=parent:
//error_log( __LINE__ . ' MLAParentSearchExample::mla_list_table_new_instance parent_search_parameters = ' . var_export( self::$parent_search_parameters, true ), 0 );
//error_log( __LINE__ . ' MLAParentSearchExample::mla_list_table_new_instance _REQUEST = ' . var_export( $_REQUEST, true ), 0 );

		return $mla_list_table;
	} // mla_list_table_new_instance

	/**
	 * Custom Field Search "parameters"
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	public static $parent_search_parameters = array();

	/**
	 * Filter the WP_Query request parameters for the prepare_items query
	 *
	 * Gives you an opportunity to change the terms of the prepare_items query
	 * after they are processed by the "Prepare List Table Query" handler.
	 *
	 * @since 1.00
	 *
	 * @param	array	WP_Query request prepared by "Prepare List Table Query"
	 *
	 * @return	array	updated WP_Query request
	 */
	public static function mla_list_table_query_final_terms( $request ) {
		/*
		 * If $request['offset'] and $request['posts_per_page'] are set, this is the "prepare_items" request.
		 * If they are NOT set, this is a "view count" request, i.e., to get the count for a custom view.
		 *
		 * MLAData::$query_parameters and MLAData::$search_parameters contain
		 * additional parameters used in some List Table queries.
		 */
		if ( ! ( isset( $request['offset'] ) && isset( $request['posts_per_page'] ) ) ) {
			return $request;
		}
//error_log( __LINE__ . ' MLAParentSearchExample::mla_list_table_query_final_terms request = ' . var_export( $request, true ), 0 );

		if ( empty( self::$parent_search_parameters ) ) {
			return $request;
		}

		if ( isset( self::$parent_search_parameters['debug'] ) ) {
			MLAData::$query_parameters['debug'] = self::$parent_search_parameters['debug'];
			MLAData::$search_parameters['debug'] = self::$parent_search_parameters['debug'];
			MLACore::mla_debug_mode( self::$parent_search_parameters['debug'] );
		}

		// Construct a search for the parent posts/pages
		$parent_query = array(
			'orderby' => 'ID',
			'order' => 'ASC',
			'post_mime_type' => 'all',
			'post_type' => 'post,page',
			'post_status' => 'publish',
			's' => self::$parent_search_parameters['s'],
			'mla_search_connector' => self::$parent_search_parameters['mla_search_connector'],
			'mla_search_fields' => implode( ',', self::$parent_search_parameters['mla_search_fields'] ),
		);
//error_log( __LINE__ . ' MLAParentSearchExample::mla_list_table_query_final_terms parent_query = ' . var_export( $parent_query, true ), 0 );
		$parents = MLAShortcodes::mla_get_shortcode_attachments( 0, $parent_query );
		$ids = array();
		foreach( $parents as $parent ) {
			$ids[] = $parent->ID;
		}
//error_log( __LINE__ . ' MLAParentSearchExample::mla_list_table_query_final_terms ids = ' . var_export( $ids, true ), 0 );
		$request['post_parent__in'] = $ids;
//error_log( __LINE__ . ' MLAParentSearchExample::mla_list_table_query_final_terms request = ' . var_export( $request, true ), 0 );
		
		return $request;
	} // mla_list_table_query_final_terms

	/**
	 * MLA Edit Media "Query Attachments" initial terms Filter
	 *
	 * Gives you an opportunity to change the terms of the 
	 * Media Manager Modal Window "Query Attachments" query
	 * before they are pre-processed by the MLA handler.
	 *
	 * @since 1.00
	 *
	 * @param	array	WP_Query terms supported for "Query Attachments"
	 * @param	array	All terms passed in the request
	 */
	public static function mla_media_modal_query_initial_terms( $query, $raw_query ) {
		/*
		 * Look for the special "parent:" prefix in the Search Media text box,
		 * after checking for the "debug" prefixes.
		 */
		if ( isset( $query['mla_search_value'] ) ) {
			switch ( substr( $query['mla_search_value'], 0, 3 ) ) {
				case '}|{':
					self::$parent_search_parameters['debug'] = 'console';
					$start = 3;
					break;
				case '{|}':
					self::$parent_search_parameters['debug'] = 'log';
					$start = 3;
					break;
				default:
					$start = 0;
			}

			if ( 'parent:' == substr( $query['mla_search_value'], $start, 7 ) ) {
				self::$parent_search_parameters['s'] = substr( $query['mla_search_value'], $start + 7 );
				unset( $query['mla_search_value'] );
				self::$parent_search_parameters['mla_search_connector'] = $query['mla_search_connector'];
				unset( $query['mla_search_connector'] );
				self::$parent_search_parameters['mla_search_fields'] = $query['mla_search_fields'];
				unset( $query['mla_search_fields'] );
			} else {
				self::$parent_search_parameters = array();
			}
		} // isset mla_search_value=parent:
//error_log( __LINE__ . ' MLAParentSearchExample::mla_media_modal_query_initial_terms query = ' . var_export( $query, true ), 0 );
//error_log( __LINE__ . ' MLAParentSearchExample::mla_media_modal_query_initial_terms parent_search_parameters = ' . var_export( self::$parent_search_parameters, true ), 0 );

		return $query;
	}

	/**
	 * MLA Edit Media "Query Attachments" final terms Filter
	 *
	 * Gives you an opportunity to change the terms of the 
	 * Media Manager Modal Window "Query Attachments" query
	 * after they are processed by the "Prepare List Table Query" handler.
	 *
	 * @since 1.00
	 *
	 * @param	array	WP_Query request prepared by "Prepare List Table Query"
	 */
	public static function mla_media_modal_query_final_terms( $request ) {
		/*
		 * The logic used in the Media/Assistant Search Media box will work here as well
		 */
		return MLAParentSearchExample::mla_list_table_query_final_terms( $request );
	}
} // Class MLAParentSearchExample

/*
 * Install the filters at an early opportunity
 */
add_action('init', 'MLAParentSearchExample::initialize');
?>
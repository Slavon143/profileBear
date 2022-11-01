<?php
/**
 * Provides [mla_gallery] parameters to select parent posts/pages with WP_Query
 *
 * In this example, two custom parameters are added to the [mla_gallery] shortcode.
 *
 * 1) a "parent_wp_query" parameter contains WP_Query arguments for
 *    parent posts/pages, e.g., " parent_wp_query='category_name=environment' ".
 *    The query value generates a list of "post_parent" values for the Media Library items query,
 *    so the shortcode selects items that are attached to the paarents in the list.
 *
 *    You can add most WP_Query parameters to the parent query, e.g.:
 *
 *    [mla_gallery parent_wp_query='category_name=environment post_type=post,page posts_per_page=10']
 *
 *    NOTE: To affect the parent query you must add the parameters INSIDE the parent_wp_query value.
 *
 * 2) a "parent_gallery" parameter contains WP_Query parameters for a second WP_Query that selects
 *    the parent posts of the items returned by the main shortcode query, i.e., the final result is
 *    a display of the parent posts, not the Media Library items returned by the main query.
 *
 *    You can add most WP_Query parameters to the parent query, e.g.:
 *
 *    [mla_gallery parent_gallery='post_type=post,page posts_per_page=10 orderby=title order=ASC']
 *
 *    NOTE: To affect the parent query you must add the parameters INSIDE the parent_gallery value.
 *
 * This example plugin uses two of the many filters available in the [mla_gallery] shortcode
 * and illustrates a technique you can use to customize the gallery display.
 *
 * Created for support topic "Create gallery of all images attached to a list of posts?"
 * opened on 9/4/2016 by "cconstantine".
 * https://wordpress.org/support/topic/create-gallery-of-all-images-attached-to-a-list-of-posts/
 *
 * Enhanced for support topic "Extension of search capabilities"
 * opened on 2/1/2021 by "ernstwg".
 * https://wordpress.org/support/topic/extension-of-search-capabilities/
 *
 * @package MLA Parent WP_Query Example
 * @version 1.02
 */

/*
Plugin Name: MLA Parent WP_Query Example
Plugin URI: http://davidlingren.com/
Description: Selects items attached to parents assigned to a taxonomy term
Author: David Lingren
Version: 1.02
Author URI: http://davidlingren.com/

Copyright 2016-2021 David Lingren

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
 * Class MLA Parent WP_Query Example selects post_parent values with a WP_Query
 *
 * @package MLA Parent WP_Query Example
 * @since 1.00
 */
class MLAParentWPQueryExample {
	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 1.00
	 */
	public static function initialize() {
		// The filters are only useful for front-end posts/pages; exit if in the admin section
		if ( is_admin() )
			return;

		add_filter( 'mla_gallery_attributes', 'MLAParentWPQueryExample::mla_gallery_attributes', 10, 1 );
		add_filter( 'mla_gallery_the_attachments', 'MLAParentWPQueryExample::mla_gallery_the_attachments', 10, 2 );
	}

	/**
	 * Replace the parent_wp_query value with a list of post_parent values
	 *
	 * The $shortcode_attributes array is where you will find any of your own parameters that are coded in the
	 * shortcode, e.g., [mla_gallery parent_wp_query="category_name=environment"].
	 *
	 * @since 1.00
	 *
	 * @param	array	the shortcode parameters passed in to the shortcode
	 */
	public static function mla_gallery_attributes( $shortcode_attributes ) {
		global $wpdb;

		// Process shortcodes with the parent_wp_query parameter
		if ( !empty( $shortcode_attributes['parent_wp_query'] ) ) {
			// Make sure $arguments is an array, even if it's empty
			$arguments = $shortcode_attributes['parent_wp_query'];
			if ( empty( $arguments ) ) {
				$arguments = array();
			} elseif ( is_string( $arguments ) ) {
				$arguments = shortcode_parse_atts( $arguments );
			}
	
			// Multi-value post_type and post_status must be arrays
			if ( isset( $arguments['post_type'] ) ) {
				$arguments['post_type'] = explode( ',', $arguments['post_type'] );
			}
	
			if ( isset( $arguments['post_status'] ) ) {
				$arguments['post_status'] = explode( ',', $arguments['post_status'] );
			}
	
			// Make the query more efficient
			$arguments = array_merge( $arguments, array(
				'no_found_rows' => true,
				'fields' => 'ids',
				'cache_results' => false,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
			) );
//error_log( __LINE__ . " MLAParentWPQueryExample::mla_gallery_attributes() arguments = " . var_export( $arguments, true ), 0 );
	
			$wp_query_object = new WP_Query;
			$parents = $wp_query_object->query( $arguments );
//error_log( __LINE__ . " MLAParentWPQueryExample::mla_gallery_attributes() parents = " . var_export( $parents, true ), 0 );
	
			if ( is_array( $parents ) ) {
				$shortcode_attributes['post_parent'] = implode( ',', $parents );
			}
			
			unset( $shortcode_attributes['parent_wp_query'] );
		}

		// Process shortcodes with the parent_gallery parameter
		self::$parent_gallery_arguments = NULL;
		if ( isset( $shortcode_attributes['parent_gallery'] ) ) {
			// Make sure $arguments is an array, even if it's empty
			$arguments = $shortcode_attributes['parent_gallery'];
			if ( empty( $arguments ) ) {
				$arguments = array();
			} elseif ( is_string( $arguments ) ) {
				$arguments = shortcode_parse_atts( $arguments );
			}
//error_log( __LINE__ . " MLAParentWPQueryExample::mla_gallery_attributes() arguments = " . var_export( $arguments, true ), 0 );
	
			// Multi-value post_type and post_status must be arrays
			if ( isset( $arguments['post_type'] ) ) {
				$arguments['post_type'] = explode( ',', $arguments['post_type'] );
			} else {
				$arguments['post_type'] = 'post';
			}
	
			if ( isset( $arguments['post_status'] ) ) {
				$arguments['post_status'] = explode( ',', $arguments['post_status'] );
			} else {
				$arguments['post_status'] = 'published';
			}
	
			if ( isset( $arguments['post_mime_type'] ) ) {
				$arguments['post_mime_type'] = explode( ',', $arguments['post_mime_type'] );
			} else {
				$arguments['post_mime_type'] = '';
			}

			if ( !isset( $arguments['posts_per_page'] ) ) {
				$arguments['posts_per_page'] = -1;
			}

			// Save the arguments for the second WP_Query
			self::$parent_gallery_arguments = $arguments;
			
			// Make the initial query more efficient
			$shortcode_attributes = array_merge( $shortcode_attributes, array(
				'no_found_rows' => true,
				'cache_results' => false,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
			) );
			unset( $shortcode_attributes['parent_gallery'] );

//error_log( __LINE__ . " MLAParentWPQueryExample::mla_gallery_attributes() shortcode_attributes = " . var_export( $shortcode_attributes, true ), 0 );
		}

		return $shortcode_attributes;
	} // mla_gallery_attributes

	/**
	 * Shortcode Arguments for parent_gallery
	 *
	 * @since 1.02
	 *
	 * @var	array
	 */
	private static $parent_gallery_arguments = NULL;

	/**
	 * MLA Gallery The Attachments
	 *
	 * This filter gives you an opportunity to record or modify the array of items
	 * returned by the query.
	 *
	 * @since 1.09
	 *
	 * @param NULL $filtered_attachments initially NULL, indicating no substitution.
	 * @param array $attachments WP_Post objects returned by WP_Query->query, passed by reference
	 */
	public static function mla_gallery_the_attachments( $filtered_attachments, $attachments ) {
//error_log( __LINE__ . ' MLAParentWPQueryExample::mla_gallery_the_attachments $attachments = ' . var_export( $attachments, true ), 0 );

		// Replace the items with their parents, if requested
		if ( !empty( self::$parent_gallery_arguments ) ) {
			if ( count( $attachments ) ) 
			$ids = array();
			$return_found_rows = false;
			foreach ( $attachments as $index => $attachment ) {
				switch ( $index ) {
					case 'found_rows':
					case 'max_num_pages':
						$return_found_rows = true;
						break;
					default:
						if ( is_object( $attachment ) ) {
							$ids[ $attachment->post_parent ] = $attachment->post_parent;
						}
				} // switch
			} // foreach
//error_log( __LINE__ . ' MLAParentWPQueryExample::mla_gallery_the_attachments ids = ' . var_export( $ids, true ), 0 );
			
			$parents = array();
			if ( !empty( $ids ) ) {
				unset( self::$parent_gallery_arguments['post_mime_type'] );
				self::$parent_gallery_arguments['post__in'] = $ids;
				self::$parent_gallery_arguments['ignore_sticky_posts'] = true;
				self::$parent_gallery_arguments['suppress_filters'] = true;
//error_log( __LINE__ . ' MLAParentWPQueryExample::mla_gallery_the_attachments parent_gallery_arguments = ' . var_export( self::$parent_gallery_arguments, true ), 0 );

				$wp_query_object = new WP_Query;
				$parents = $wp_query_object->query( self::$parent_gallery_arguments );
				
				if ( $return_found_rows ) {
					$parents['found_rows'] = absint( $wp_query_object->found_posts );
					$parents['max_num_pages'] = absint( $wp_query_object->max_num_pages );
				}
				
//error_log( __LINE__ . " MLAParentWPQueryExample::mla_gallery_attributes() parents = " . var_export( $parents, true ), 0 );
			}
			
			if ( !empty( $parents ) ) {
				return $parents;
			}
		}
		
		return $filtered_attachments;
	} // mla_gallery_the_attachments
} // Class MLAParentWPQueryExample

// Install the filters at an early opportunity
add_action('init', 'MLAParentWPQueryExample::initialize');
?>
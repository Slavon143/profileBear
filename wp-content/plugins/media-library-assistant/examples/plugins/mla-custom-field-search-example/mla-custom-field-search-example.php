<?php
/**
 * Extends the Media/Assistant "Search Media" box to custom field values
 *
 * In this example, a "custom:" prefix is detected in the Media/Assistant "search media" text
 * box and the search is modified to query a custom field for a specific value, e.g.,
 * "custom:photo reference=123456". You can also search for partial values:
 *
 *  - To return all items that have a non-NULL value in the field, simply enter the prefix
 *    "custom:" followed by the custom field name, for example, custom:File Size. You can also
 *    enter the custom field name and then "=*", e.g., custom:File Size=*.
 *  - To return all items that have a NULL value in the field, enter the custom field name and
 *    then "=", e.g., custom:File Size=.
 *  - To return all items that match one or more values, enter the prefix "custom:" followed by
 *    the custom field name and then "=" followed by a list of values. For example, custom:Color=red
 *    or custom:Color=red,green,blue. Wildcard specifications are also supported; for example, "*post"
 *    to match anything ending in "post" or "th*da*" to match values like "the date" and "this day".
 *
 * This example plugin uses four of the many filters available in the Media/Assistant Submenu
 * and illustrates some of the techniques you can use to customize the submenu table display.
 *
 * Created for support topic "Searching on custom fields"
 * opened on 5/11/2015 by "BFI-WP".
 * https://wordpress.org/support/topic/searching-on-custom-fields/
 *
 * Enhanced for support topic "Search Media by Custom Field"
 * opened on 12/2/2020 by "icedarkness".
 * https://wordpress.org/support/topic/search-media-by-custom-field/
 *
 * @package MLA Custom Field Search Example
 * @version 1.07
 */

/*
Plugin Name: MLA Custom Field Search Example
Plugin URI: http://davidlingren.com/
Description: Extends the Media/Assistant "Search Media" box to custom field values
Author: David Lingren
Version: 1.07
Author URI: http://davidlingren.com/

Copyright 2014 - 2020 David Lingren

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
 * Class MLA Custom Field Search Example extends the Media/Assistant "Search Media" box
 * to custom field values
 *
 * @package MLA Custom Field Search Example
 * @since 1.00
 */
class MLACustomFieldSearchExample {
	/**
	 * Plugin version number for debug logging
	 *
	 * @since 1.01
	 *
	 * @var	integer
	 */
	const PLUGIN_VERSION = '1.07';

	/**
	 * Constant to log this plugin's debug activity
	 *
	 * @since 1.06
	 *
	 * @var	integer
	 */
	const MLA_DEBUG_CATEGORY = 0x00008000;

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 1.06
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'mlacustomfieldsearch';

	/**
	 * Configuration values for the Settings Management object
	 *
	 * @since 1.06
	 *
	 * @var	array
	 */
	private static $settings_arguments = array(
				'slug_prefix' => self::SLUG_PREFIX,
				'plugin_title' => 'MLA Custom Field Search Example',
				'menu_title' => 'MLA Custom Search',
				'plugin_file_name_only' => 'mla-custom-field-search-example',
				'plugin_version' => self::PLUGIN_VERSION,
				'template_file' => '/admin-settings-page.tpl', // Add the path at runtime, in initialize()
				'options' => array( // 'slug' => array( 'type' => 'text|checkbox', 'default' => 'text|boolean' )
					'media_assistant_support' =>array( 'type' => 'checkbox', 'default' => true ),
					'mmmw_support' =>array( 'type' => 'checkbox', 'default' => true ),
					'prefix' =>array( 'type' => 'text', 'default' => 'custom:' ),
					'default_fields' =>array( 'type' => 'text', 'default' => '' ),
					'all_fields' =>array( 'type' => 'text', 'default' => '*' ),
					'all_fields_support' =>array( 'type' => 'checkbox', 'default' => true ),
				),
				'general_tab_values' => array(), // additional page_values for 'page-level-options' template
				'documentation_tab_values' => array(
					'plugin_title' => 'MLA Custom Field Search Example',
				), // page_values for 'documentation-tab' template
			);

	/**
	 * Settings Management object
	 *
	 * @since 1.06
	 *
	 * @var	array
	 */
	private static $plugin_settings = NULL;

	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 1.06
	 *
	 * @return	void
	 */
	public static function initialize() {
		// This plugin requires MLA
		if ( ! class_exists( 'MLACore', false ) ) {
			return;
		}

		// The filters are only useful for the admin section; exit in the front-end posts/pages
		if ( ! is_admin() ) {
			return;
		}

		// The plugin settings class is shared with other MLA example plugins
		if ( ! class_exists( 'MLAExamplePluginSettings101' ) ) {
			require_once( pathinfo( __FILE__, PATHINFO_DIRNAME ) . '/class-mla-example-plugin-settings-101.php' );
		}

		// Add the run-time values to the arguments
		self::$settings_arguments['template_file'] = dirname( __FILE__ ) . self::$settings_arguments['template_file'];

		// Create our own settings object
		self::$plugin_settings = new MLAExamplePluginSettings101( self::$settings_arguments );

		if ( self::$plugin_settings->get_plugin_option( 'media_assistant_support' ) ) {
			// Defined in /media-library-assistant/includes/class-mla-main.php
			add_filter( 'mla_list_table_new_instance', 'MLACustomFieldSearchExample::mla_list_table_new_instance', 10, 1 );

			// Defined in /media-library-assistant/includes/class-mla-data.php
			add_filter( 'mla_list_table_query_final_terms', 'MLACustomFieldSearchExample::mla_list_table_query_final_terms', 10, 1 );

			// Defined in /media-library-assistant/includes/class-mla-list-table.php
			add_filter( 'mla_list_table_submenu_arguments', 'MLACustomFieldSearchExample::mla_list_table_submenu_arguments', 10, 2 );
		}

		if ( self::$plugin_settings->get_plugin_option( 'mmmw_support' ) ) {
			// Defined in /media-library-assistant/includes/class-mla-media-modal.php
			add_filter( 'mla_media_modal_query_initial_terms', 'MLACustomFieldSearchExample::mla_media_modal_query_initial_terms', 10, 2 );

			// Defined in /media-library-assistant/includes/class-mla-data.php
			add_filter( 'mla_media_modal_query_final_terms', 'MLACustomFieldSearchExample::mla_media_modal_query_final_terms', 10, 1 );
		}
	}

	/**
	 * Filter the URL parameters that will be retained when the submenu page refreshes
	 *
	 * @since 1.06
	 *
	 * @param	array	$submenu_arguments non-empty view, search, filter and sort arguments.
	 * @param	boolean	$include_filters Include the "click filter" values in the results.
	 */
	public static function mla_list_table_submenu_arguments( $submenu_arguments, $include_filters ) {
		if ( $include_filters ) {
			if ( !empty( self::$custom_field_parameters['s'] ) ) {
				$prefix = self::$plugin_settings->get_plugin_option( 'prefix' );
				$submenu_arguments['s'] = $prefix . self::$custom_field_parameters['s'];
			}
		}
		
		return $submenu_arguments;
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
		 * Look for the custom field search prefix in the Search Media text box,
		 * after checking for the "debug" prefixes.
		 */
		if ( isset( $_REQUEST['s'] ) ) {
			switch ( substr( $_REQUEST['s'], 0, 3 ) ) {
				case '}|{':
					self::$custom_field_parameters['debug'] = 'console';
					$start = 3;
					break;
				case '{|}':
					self::$custom_field_parameters['debug'] = 'log';
					$start = 3;
					break;
				default:
					$start = 0;
			}

			$prefix = self::$plugin_settings->get_plugin_option( 'prefix' );
			if ( $prefix === substr( $_REQUEST['s'], $start, strlen( $prefix ) ) ) {
				self::$custom_field_parameters['s'] = substr( $_REQUEST['s'], $start + strlen( $prefix ) );
				unset( $_REQUEST['s'] );
				//self::$custom_field_parameters['mla_search_connector'] = $_REQUEST['mla_search_connector'];
				unset( $_REQUEST['mla_search_connector'] );
				//self::$custom_field_parameters['mla_search_fields'] = $_REQUEST['mla_search_fields'];
				unset( $_REQUEST['mla_search_fields'] );
			} else {
				self::$custom_field_parameters = array();
			}
		} // isset $_REQUEST['s']

		return $mla_list_table;
	} // mla_list_table_new_instance

	/**
	 * Custom Field Search "parameters"
	 *
	 * @since 1.01
	 *
	 * @var	array
	 */
	public static $custom_field_parameters = array();

	/**
	 * Filter the WP_Query request parameters for the prepare_items query
	 *
	 * Gives you an opportunity to change the terms of the prepare_items query
	 * after they are processed by the "Prepare List Table Query" handler.
	 *
	 * @since 1.01
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
		 * MLAQuery::$query_parameters and MLAQuery::$search_parameters contain
		 * additional parameters used in some List Table queries.
		 */
		if ( ! ( isset( $request['offset'] ) && isset( $request['posts_per_page'] ) ) ) {
			return $request;
		}

		if ( empty( self::$custom_field_parameters ) ) {
			return $request;
		}

		if ( isset( self::$custom_field_parameters['debug'] ) ) {
			MLAQuery::$query_parameters['debug'] = self::$custom_field_parameters['debug'];
			MLAQuery::$search_parameters['debug'] = self::$custom_field_parameters['debug'];
			MLACore::mla_debug_mode( self::$custom_field_parameters['debug'] );
		}

		$specification = self::$custom_field_parameters['s'];

		// Check for and reformat special "<field>,null" case
		if ( ( strlen( $specification ) >= strlen(',null') ) && ( false !== strpos( $specification, ',null', -strlen(',null') ) ) ) {
			$field = substr( $specification, 0, strlen( $specification ) -  strlen(',null') );
			$specification = $field . '=';
		}

		// Check for and reformat "field names only" case
		if ( false === strpos( $specification, '=' ) ) {
			$specification .= '=*';
		}

		// Apply default field name?
		$default_fields = self::$plugin_settings->get_plugin_option( 'default_fields' );
		if ( empty( $default_fields ) ) {
			$default_fields = 'ERROR - No Default Field(s) Specified';
		}
		
		if ( '=' == substr( $specification, 0, 1 ) ) {
			$tokens = array( $default_fields, substr( $specification, 1 ) );
		} else {
			$tokens = explode( '=', $specification ) ;
		}

		// See if the custom field name is present, followed by "=" and a value
		if ( 1 < count( $tokens ) ) {
			$field = array_shift( $tokens );
			$value = implode( '=', $tokens );
		} else {
			// Supply a default custom field name
			$field = $default_fields;
			$value = $tokens[0];
		}

		// Look for substitute All Fields value
		if ( self::$plugin_settings->get_plugin_option( 'all_fields_support' ) ) {
			if ( $field === self::$plugin_settings->get_plugin_option( 'all_fields' ) ) {
				$field = '*';
			}
		}

		// Parse the query, remove MLA-specific elements, fix numeric and "commas" format fields
		MLACore::mla_debug_add( __LINE__ . " MLACustomFieldSearchExample::mla_list_table_query_final_terms query = custom:{$field}={$value}" );
		$tokens = MLACore::mla_prepare_view_query( 'custom_field_search', 'custom:' . $field . '=' . $value );
		MLACore::mla_debug_add( __LINE__ . ' MLACustomFieldSearchExample::mla_list_table_query_final_terms tokens = ' . var_export( $tokens, true ) );
		$tokens = $tokens['meta_query'];
		MLACore::mla_debug_add( __LINE__ . ' MLACustomFieldSearchExample::mla_list_table_query_final_terms meta_query = ' . var_export( $tokens, true ) );

		/*
		 * Matching a meta_value to NULL requires a LEFT JOIN to a view and a special WHERE clause;
		 * MLA filters will handle this case.
		 */
		if ( isset( $tokens['key'] ) ) {
			MLAQuery::$query_parameters['use_postmeta_view'] = true;
			MLAQuery::$query_parameters['postmeta_key'] = $tokens['key'];
			MLAQuery::$query_parameters['postmeta_value'] = NULL;
			return $request;
		}

		// Process "normal" meta_query
		$query = array( 'relation' => $tokens['relation'] );
		$padded_values = array();
		$patterns = array();
		foreach ( $tokens as $key => $value ) {
			// The key/value/compare elements are nested within the query array
			if ( ! is_numeric( $key ) ) {
				continue;
			}

			if ( !empty( $value['key'] ) && in_array( $value['key'], array( 'File Size', 'pixels', 'width', 'height' ) ) ) {
				if ( '=' == $value['compare'] ) {
					$value['value'] = str_pad( $value['value'], 15, ' ', STR_PAD_LEFT );
					$padded_values[ trim( $value['value'] ) ] = $value['value'];
				} else {
					$value['value'] = '%' . $value['value'];
				}
			}

			if ( 'LIKE' == $value['compare'] ) {
				$patterns[] = $value['value'];
			}

			$query[] = $value;
		}

		if ( ! empty( $padded_values ) ) {
			MLAQuery::$query_parameters['mla-metavalue'] = $padded_values;
		}

		if ( ! empty( $patterns ) ) {
			MLAQuery::$query_parameters['patterns'] = $patterns;
		}

		// Combine with an existing "custom view" meta_query, if present
		if ( isset( $request['meta_query'] ) ) {
			$request['meta_query'] = array( 'relation' => 'AND', $request['meta_query'], $query );
		} else {
			$request['meta_query'] = $query;
		}

		MLACore::mla_debug_add( __LINE__ . ' mla_list_table_query_final_terms request = ' . var_export( $request, true ) );
		return $request;
	} // mla_list_table_query_final_terms

	/**
	 * MLA Edit Media "Query Attachments" initial terms Filter
	 *
	 * Gives you an opportunity to change the terms of the 
	 * Media Manager Modal Window "Query Attachments" query
	 * before they are pre-processed by the MLA handler.
	 *
	 * @since 1.03
	 *
	 * @param	array	WP_Query terms supported for "Query Attachments"
	 * @param	array	All terms passed in the request
	 */
	public static function mla_media_modal_query_initial_terms( $query, $raw_query ) {
		/*
		 * Look for the custom field search prefix in the Search Media text box,
		 * after checking for the "debug" prefixes.
		 */
		if ( isset( $query['mla_search_value'] ) ) {
			switch ( substr( $query['mla_search_value'], 0, 3 ) ) {
				case '}|{':
					self::$custom_field_parameters['debug'] = 'log'; // = 'console'; won't work for AJAX queries
					$start = 3;
					break;
				case '{|}':
					self::$custom_field_parameters['debug'] = 'log';
					$start = 3;
					break;
				default:
					$start = 0;
			}

			$prefix = self::$plugin_settings->get_plugin_option( 'prefix' );
			if ( $prefix === substr( $query['mla_search_value'], $start, strlen( $prefix ) ) ) {
				self::$custom_field_parameters['s'] = substr( $query['mla_search_value'], $start + strlen( $prefix ) );
				unset( $query['mla_search_value'] );
				//self::$custom_field_parameters['mla_search_connector'] = $query['mla_search_connector'];
				unset( $query['mla_search_connector'] );
				//self::$custom_field_parameters['mla_search_fields'] = $query['mla_search_fields'];
				unset( $query['mla_search_fields'] );
			} else {
				self::$custom_field_parameters = array();
			}
		} // isset mla_search_value=custom:

		return $query;
	}

	/**
	 * MLA Edit Media "Query Attachments" final terms Filter
	 *
	 * Gives you an opportunity to change the terms of the 
	 * Media Manager Modal Window "Query Attachments" query
	 * after they are processed by the "Prepare List Table Query" handler.
	 *
	 * @since 1.03
	 *
	 * @param	array	WP_Query request prepared by "Prepare List Table Query"
	 */
	public static function mla_media_modal_query_final_terms( $request ) {
		// The logic used in the Media/Assistant Search Media box will work here as well
		return MLACustomFieldSearchExample::mla_list_table_query_final_terms( $request );
	}

} // Class MLACustomFieldSearchExample

// Install the filters at an early opportunity
add_action('init', 'MLACustomFieldSearchExample::initialize');
?>
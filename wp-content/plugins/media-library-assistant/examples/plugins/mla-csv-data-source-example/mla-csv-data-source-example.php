<?php
/**
 * Populates one or more data sources from a CSV file for use in mapping rules, etc.
 *
 * Much more information is in the Settings/MLA CSV Data "Documentation" tab.
 *
 * Inspired by several support topics, such as:
 *
 * "Update image meta data from CSV"
 * opened on 5/10/2020 by "eledris".
 * https://wordpress.org/support/topic/update-image-meta-data-from-csv/
 *
 * "Export Settings?"
 * opened on 5/5/2020 by "redkite".
 * https://wordpress.org/support/topic/export-settings-25/
 *
 * "Migrating Data From One Site to Another"
 * opened on 11/26/2019 by "digisavvy".
 * https://wordpress.org/support/topic/migrating-data-from-one-site-to-another/
 *
 * "Export/Import Att. Data"
 * opened on 8/15/2019 by "customle".
 * https://wordpress.org/support/topic/migrating-data-from-one-site-to-another/
 *
 * "Batch upload of media (not bulk upload)"
 * opened on 3/30/2018 by "yassermkali".
 * https://wordpress.org/support/topic/batch-upload-of-media-not-bulk-upload/
 *
 * "Import/Export Funcionality"
 * opened on 10/15/2017 by "polymathy".
 * https://wordpress.org/support/topic/batch-upload-of-media-not-bulk-upload/
 *
 * "Bulk Add Unique Captions"*
 * opened on 2/17/2016 by "hunterscreate".
 * https://wordpress.org/support/topic/batch-upload-of-media-not-bulk-upload/
 *
 * "How to migrate image attributes during site migration"
 * opened on 8/30/2015 by "cliffmama".
 * https://wordpress.org/support/topic/how-to-migrate-image-attributes-during-site-migration/
 *
 * "Export / Import Att. Categories"
 * opened on 6/4/2014 by "thitz" and "vipoxofni".
 * https://wordpress.org/support/topic/export-import-att-categories/
 *
 * "export / import settings"
 * opened on 9/23/2013 by "bhensler".
 * https://wordpress.org/support/topic/batch-upload-of-media-not-bulk-upload/
 *
 * "What about a media library export/import function"
 * opened on 9/25/2012 by "brentwz".
 * https://wordpress.org/support/topic/plugin-media-library-assistant-what-about-a-media-library-exportimport-function/
 *
 * Enhanced (Export Item Values) for support topic "download url"
 * opened on 5/18/2021 by "blogdropper".
 * https://wordpress.org/support/topic/download-url-2/
 *
 * Enhanced (Export Item Values) for support topic "Import / Export to CSV for bulk edit"
 * opened on 5/21/2021 by "cuppacoffee".
 * https://wordpress.org/support/topic/import-export-to-csv-for-bulk-edit/
 *
 * @package MLA CSV Data Source Example
 * @version 1.03
 */

/*
Plugin Name: MLA CSV Data Source Example
Plugin URI: http://davidlingren.com/
Description: Populates one or more data sources from a CSV file
Author: David Lingren
Version: 1.03
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
 * Class MLA CSV Data Source Example hooks one of the filters provided by the IPTC/EXIF and Custom Field mapping features
 *
 * Call it anything you want, but give it an unlikely and hopefully unique name. Hiding everything
 * else inside a class means this is the only name you have to worry about.
 *
 * @package MLA CSV Data Source Example
 * @since 1.00
 */
class MLACSVDataSourceExample {
	/**
	 * Current version number
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const PLUGIN_VERSION = '1.03';

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'mlacsvdatasource';

	/**
	 * Configuration values for the Settings Management object
	 *
	 * @since 1.02
	 *
	 * @var	array
	 */
	private static $settings_arguments = array(
				'slug_prefix' => self::SLUG_PREFIX,
				'plugin_title' => 'MLA CSV Data Source Example',
				'menu_title' => 'MLA CSV Data',
				'plugin_file_name_only' => 'mla-csv-data-source-example',
				'plugin_version' => self::PLUGIN_VERSION,
				'template_file' => '/admin-settings-page.tpl', // Add the path at runtime, in initialize()
				'options' => array( // 'slug' => array( 'type' => 'text|checkbox', 'default' => 'text|boolean' )
					'source' => array( 'type' => 'select', 'default' => 0 ),
					'match' => array( 'type' => 'select', 'default' => 'id' ),
					'delimiter' => array( 'type' => 'text', 'default' => ',' ),
					'enclosure' => array( 'type' => 'text', 'default' => '"' ),
					'escape' => array( 'type' => 'text', 'default' => '\\' ),
					'exports' => array( 'type' => 'textarea', 'default' => '' ),
				),
				'general_tab_values' => array(), // additional page_values for 'page-level-options' template
				'documentation_tab_values' => array(
					'plugin_title' => 'MLA CSV Data Source Example',
					'settingsURL' => '', // Set at runtime in initialize()
				), // page_values for 'documentation-tab' template
			);

	/**
	 * Settings Management object
	 *
	 * @since 1.02
	 *
	 * @var	array
	 */
	private static $plugin_settings = NULL;

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
		// This plugin requires MLA
		if ( ! class_exists( 'MLACore', false ) ) {
			return;
		}

		// The plugin settings class is shared with other MLA example plugins
		if ( ! class_exists( 'MLAExamplePluginSettings101' ) ) {
			require_once( pathinfo( __FILE__, PATHINFO_DIRNAME ) . '/class-mla-example-plugin-settings-101.php' );
		}

		// Add the run-time values to the arguments
		self::$settings_arguments['template_file'] = dirname( __FILE__ ) . self::$settings_arguments['template_file'];
		self::$settings_arguments['documentation_tab_values']['settingsURL'] = admin_url('options-general.php');

		// Create our own settings object
		self::$plugin_settings = new MLAExamplePluginSettings101( self::$settings_arguments );
		
		if ( !empty( $_REQUEST[ self::SLUG_PREFIX . '_options_export'] ) ) {
			// Match Keys download will be handled in the admin_init filter
			check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );
			self::export_item_values_action( ); // Does not return; calls exit()
			return;
		}
		
		// Defined in /media-library-assistant/includes/class-mla-data.php
		add_filter( 'mla_expand_custom_prefix', 'MLACSVDataSourceExample::mla_expand_custom_prefix', 10, 8 );

		// The remaining filters are only useful in the admin section; exit if in the "front-end" posts/pages. 
		if ( ! is_admin() ) {
			return;
		}

		// Add the run-time values to the settings
		// Load template array and initialize page-level values.
		self::$page_template_array = MLACore::mla_load_template( self::$settings_arguments['template_file'], 'path' );

		$general_tab_values = self::$plugin_settings->get_plugin_argument('general_tab_values');
		$general_tab_values['source_options'] = self::_compose_source_file_options( self::$plugin_settings->get_plugin_option('source') );
		$general_tab_values['id_selected'] = 'id' === self::$plugin_settings->get_plugin_option('match') ? 'selected=selected' : '';
		$general_tab_values['base_file_selected'] = 'base_file' === self::$plugin_settings->get_plugin_option('match') ? 'selected=selected' : '';
		$general_tab_values['file_name_selected'] = 'file_name' === self::$plugin_settings->get_plugin_option('match') ? 'selected=selected' : '';
		self::$plugin_settings->update_plugin_argument('general_tab_values', $general_tab_values );
	} // initialize

	/**
	 * Template file for the Settings page(s) and parts
	 *
	 * This array contains all of the template parts for the Settings page(s). The array is built once
	 * each page load and cached for subsequent use.
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	public static $page_template_array = NULL;

	/**
	 * Definitions for Settings page tab ids, titles and handlers
	 * Each tab is defined by an array with the following elements:
	 *
	 * array key => HTML id/name attribute and option database key (OMIT MLA_OPTION_PREFIX)
	 *
	 * title => tab label / heading text
	 * render => rendering function for tab messages and content. Usage:
	 *     $tab_content = ['render']( );
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $mla_tablist = array(
		'general' => array( 'title' => 'General', 'render' => array( 'MLACSVDataSourceExample', '_compose_general_tab' ) ),
		'documentation' => array( 'title' => 'Documentation', 'render' => array( 'MLACSVDataSourceExample', '_compose_documentation_tab' ) ),
		);

	/**
	 * In-memory representation of the option settings
	 *
	 * @since 1.00
	 *
	 * @var array $_settings {
	 *     @type boolean $assign_parents Assign all terms in path, not just the last (leaf) term
	 *     @type boolean $assign_rule_parent Assign the Rule Parent (if any) in addition to terms in path
	 *     @type string  $path_delimiter The delimiter that separates path components
	 *     }
	 */
	private static $_settings = NULL;

	/**
	 * Default processing options
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $_default_settings = array (
						'source' => 0,
						'match' => 'id',
						'delimiter' => ',',
						'enclosure' => '"',
						'escape' => '\\',
						'exports' => '',
					);

	/**
	 * Export Base Name, File Name, ID and optional columns to a CSV file
	 *
	 * @since 1.01
	 *
	 * @return	none	terminates execution with exit();
	 */
	public static function export_item_values_action() {
		global $wpdb;
	
		check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );

		if( ini_get( 'zlib.output_compression' ) ) { 
			ini_set( 'zlib.output_compression', 'Off' );
		}
		
		$message = '';
		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['basedir'];
		$date = date("Ymd_B");
		$filename = "{$upload_dir}/match_keys_{$date}.csv";
		$column_names = array( 'Base Name', 'File Name', 'ID' );
		$export_specifications = array();

		// Prepare optional columns
		$exports = self::$plugin_settings->get_plugin_option('exports');
//error_log( __LINE__ . ' export_item_values_action mla_hex_dump( exports ) = ' . var_export( MLAData::mla_hex_dump( $exports ), true ), 0);
		$exports = explode( "\n", str_replace( "\r", "\n", str_replace( "\r\n", "\n", $exports ) ) );
//error_log( __LINE__ . ' export_item_values_action exports = ' . var_export( $exports, true ), 0);
		foreach ( $exports as $export ) {
			$column_name = $export; // Default value
			// Look for optional column name
			if ( 0 === strpos( $export, '"' ) ) {
				$title_length = strpos( $export, '",' ) + 2;
				// Filter out invalid or empty titles
				if ( 3 < $title_length ) {
					$column_name = substr( $export, 1, $title_length - 3 );
				}
				
				// Separate the specification from the title
				$export = substr( $export, $title_length );
			} // found a name

			if ( 0 !== strpos( $export, '[+' ) ) {
				$export = '[+' . $export . '+]';
			}

			$column_names[] = $column_name;
			$export_specifications[] = array(
				'data_source' => 'template',
				'meta_name' => str_replace( '{+', '[+', str_replace( '+}', '+]', $export ) ),
				'option' => 'text',
				'format' => 'raw',
			);
		}
//error_log( __LINE__ . ' export_item_values_action column_names = ' . var_export( $column_names, true ), 0);
//error_log( __LINE__ . ' export_item_values_action export_specifications = ' . var_export( $export_specifications, true ), 0);

		$file_handle = @fopen( $filename, 'w' );
		if ( ! $file_handle ) {
			$message = sprintf( 'ERROR: The export file ( %1$s ) could not be opened.', $filename );
		} else {
			$column_names = implode( ',', $column_names );
			if ( false === @fwrite( $file_handle, $column_names . "\r\n" ) ) {
				$error_info = error_get_last();

			if ( false !== ( $tail = strpos( $error_info['message'], '</a>]: ' ) ) ) {
				$php_errormsg = ':<br>' . substr( $error_info['message'], $tail + 7 );
			} else {
				$php_errormsg = '.';
			}

			$message = sprintf( 'ERROR: Writing the settings file ( %1$s ) "%2$s".', $filename, $php_errormsg );
			}
		}

		if ( empty( $message ) ) {
			// Compile the data we need to populate the file
			$query = sprintf( 'SELECT post_id, meta_value FROM %1$s WHERE %1$s.meta_key = \'%2$s\'', $wpdb->postmeta, '_wp_attached_file' );
			$items = $wpdb->get_results( $query, OBJECT );

			foreach( $items as $item ) {
				$pathinfo = pathinfo( $item->meta_value );
				$file_name = $pathinfo['basename'];
				$fields = array( $item->meta_value, $file_name, $item->post_id );
				
				// Add optional columns
				foreach ( $export_specifications as $specification ) {
					$fields[] = MLAShortcodes::mla_get_data_source( $item->post_id, 'single_attachment_mapping', $specification, NULL );
				}
				
				@fputcsv( $file_handle, $fields );
			}
		}
		
		fclose($file_handle);

		if ( empty( $message ) ) {
			header('Pragma: public'); 	// required
			header('Expires: 0');		// no cache
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Last-Modified: '.gmdate ( 'D, d M Y H:i:s', filemtime ( $filename ) ).' GMT');
			header('Cache-Control: private',false);
			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="'.basename( $filename ).'"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: ' . filesize( $filename ));
			header('Connection: close');

			readfile( $filename );
		} else {
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			echo '<html xmlns="http://www.w3.org/1999/xhtml">';
			echo '<head>';
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo '<title>Download Error</title>';
			echo '</head>';
			echo '';
			echo '<body>';
			echo $message;
			echo '</body>';
			echo '</html> ';
		}

		@unlink( $filename );
		exit();
	} // export_item_values_action

	/**
	 * Compose HTML markup for the source file options if any text/csv files exist
 	 *
	 * @since 1.00
	 *
	 * @param	integer	Optional item ID value for the current source file, default 0
	 *
	 * @return	string	HTML markup for the Source file <select> list, if any
	 */
	private static function _compose_source_file_options( $current_id = 0 ) {
		// Avoid fatal errors, e.g., for some AJAX calls such as "heartbeat"
		if ( ! class_exists( 'MLAData' ) ) {
			return '';
		}
		
		// Default option if no files exist or there is no current selection
		$option_values = array(
			'value' => '0',
			'text' => '&mdash; select a source (CSV) file &mdash;',
			'selected' => ''
		);
		$select_options = MLAData::mla_parse_template( self::$page_template_array['select-option'], $option_values );

		// Find all CSV files in the Media Library
		$args = array(
			'post_type'  => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'text/csv',
			'nopaging' => true,
		);
		$query = new WP_Query( $args );

		$current_id = absint( $current_id );
		foreach ( $query->posts as $post ) {
			$option_values = array(
				'value' => $post->ID,
				'text' => esc_attr( $post->post_title ),
				'selected' => $current_id === $post->ID ? 'selected=selected' : '',
			);

			$select_options .= MLAData::mla_parse_template( self::$page_template_array['select-option'], $option_values );
		}

		$option_values = array(
			'key' => 'mla-import-settings-file',
			'options' => $select_options
		);

		return $select_options;
	} // _compose_source_file_options

	/**
	 * In-memory representation of the CSV variables,
	 * indexed by the "match" column value; ID, Base Name or File Name.
	 *
	 * @since 1.00
	 *
	 * @var array $_csv_variables { $match_value => 
	 *     @type array  $variables The item's value for each data source, except for $match_value
	 *     }
	 */
	private static $_csv_variables = NULL;

	/**
	 * In-memory representation of the CSV variable namess, from the
	 * first line of the file. Indexed by column position in the CSV file.
	 *
	 * @since 1.00
	 *
	 * @var array $_csv_variables { $index => 
	 *     @type array  $names The variable name for each data source
	 *     }
	 */
	private static $_csv_variable_names = NULL;

	/**
	 * Name of the "match" variable
	 *
	 * @since 1.00
	 *
	 * @var string "ID", ".ID", "Base Name" or "File Name"
	 */
	private static $_csv_match_variable= '';

	/**
	 * Name of the "match" variable
	 *
	 * @since 1.00
	 *
	 * @var mixed	index of CSV match variable, if found, else false.
	 */
	private static $_csv_match_index = false;

	/**
	 * Verify existance of match variable in the CSV file
	 *
	 * @since 1.00
	 *
	 * @param	boolean	Optional, Use existing values, if present. Default true.
	 *
	 * @return	boolean	True if variable exists in file else false.
	 */
	private static function _validate_match_selection( $use_existing = true ) {
		self::_load_csv_file( $use_existing, true );

		if ( !empty( self::$_csv_variable_names ) ) {
			$match = self::$plugin_settings->get_plugin_option('match');

			switch ( $match ) {
				case 'id':
					if ( false !== array_search( 'ID', self::$_csv_variable_names ) ) {
						return true;
					}	elseif ( false !== array_search( '.ID', self::$_csv_variable_names ) ) {
						// Special case; Excel does not allow "ID" as the first column name
						return true;
					}
					break;
				case 'base_file':
					if ( false !== array_search( 'Base File', self::$_csv_variable_names ) ) {
						return true;
					}
					break;
				case 'file_name':
					if ( false !== array_search( 'File Name', self::$_csv_variable_names ) ) {
						return true;
					}
					break;
			}
		}

		return false;
	} // _validate_match_selection

	/**
	 * Load a CSV File into memory
	 *
	 * @since 1.00
	 *
	 * @param	boolean	Optional, Use existing values, if present. Default true.
	 * @param	boolean	Optional, Load names only, from first line of the file. Default false.
	 */
	private static function _load_csv_file( $use_existing = true, $names_only = false ) {
		if ( $use_existing && !empty( self::$_csv_variables ) ) {
			return;
		}

		// Populate if possible else no values available
		self::$_csv_variables = array();
		self::$_csv_variable_names = array();
		self::$_csv_match_variable = '';
		self::$_csv_match_index = false;

		$ID = self::$plugin_settings->get_plugin_option('source');
		$match = self::$plugin_settings->get_plugin_option('match');
		$delimiter = self::$plugin_settings->get_plugin_option('delimiter');
		$enclosure = self::$plugin_settings->get_plugin_option('enclosure');
		$escape = self::$plugin_settings->get_plugin_option('escape');

		// Find the file attached to the selected item
		$path = get_attached_file( $ID );

		if ( !empty( $path ) ) {		
			$handle = fopen( $path, "r" );
			if ( false !== $handle ) {
				$match_index = false;
				self::$_csv_variable_names = fgetcsv( $handle, 0, $delimiter, $enclosure, $escape );

				if ( ( false === $names_only) && ( !empty( self::$_csv_variable_names ) ) ) {
					switch ( $match ) {
						case 'id':
							$match_index = array_search( 'ID', self::$_csv_variable_names );
							if ( false === $match_index ) {
								// Special case; Excel does not allow "ID" as the first column name
								$match_index = array_search( '.ID', self::$_csv_variable_names );
							}
							break;
						case 'base_file':
							$match_index = array_search( 'Base File', self::$_csv_variable_names );
							break;
						case 'file_name':
							$match_index = array_search( 'File Name', self::$_csv_variable_names );
							break;
					} // case $match
				} // have variable names

				// Load the file if we have a valid match variable
				if ( false !== $match_index ) {
					self::$_csv_match_variable = $match;
					self::$_csv_match_index = $match_index;

					while ( false !== ( $data = fgetcsv( $handle, 0, $delimiter, $enclosure, $escape ) ) ) {
						if ( 'id' === $match ) {
							$match_value = absint( $data[ $match_index ] );
						} else {
							$match_value = $data[ $match_index ];
						}

						// Don't store the match value twice
						unset( $data[ $match_index ] );

						self::$_csv_variables[ $match_value ] = $data;
					}
				}

				fclose($handle);
			} // file exists
		} // !empty( $path )
	} // _load_csv_file

	/**
	 * Find CSV variables for a given Media Library item
	 *
	 * @since 1.00
	 *
	 * @param	integer	attachment ID for attachment-specific values
	 *
	 * @return	mixed	array of CSV variables, if found, else false
	 */
	private static function _csv_search( $post_id ) {
		switch ( self::$_csv_match_variable ) {
			case 'id':
				$key = $post_id;
				break;
			case 'base_file':
				$key = get_post_meta( $post_id, '_wp_attached_file', true );
				break;
			case 'file_name':
				$pathinfo = pathinfo( get_post_meta( $post_id, '_wp_attached_file', true ) );
				$key = $pathinfo['basename'];
				break;
			default:
				$key = '';
		} // case $match

		if ( isset( self::$_csv_variables[ $key ] ) ) {
			$values = self::$_csv_variables[ $key ];
			// Restore the match value to the array
			$values[ self::$_csv_match_index ] = (string) $key;
			return $values;
		}

		return false;
	} // _csv_search

	/**
	 * MLA Expand Custom Prefix Filter
	 *
	 * Gives you an opportunity to generate your custom data value when a parameter's prefix value is not recognized.
	 *
	 * @since 1.00
	 *
	 * @param	string	NULL, indicating that by default, no custom value is available
	 * @param	string	the data-source name 
	 * @param	array	data-source components; prefix (empty), value, option, format and args (if present)
	 * @param	array	values from the query, if any, e.g. shortcode parameters
	 * @param	array	item-level markup template values, if any
	 * @param	integer	attachment ID for attachment-specific values
	 * @param	boolean	for option 'multi', retain existing values
	 * @param	string	default option value
	 */
	public static function mla_expand_custom_prefix( $custom_value, $key, $value, $query, $markup_values, $post_id, $keep_existing, $default_option ) {
		static $match_cache = null;

		if ( 'csv' !== strtolower( $value['prefix'] ) ) {
			return $custom_value;
		}

		if ( is_null( $match_cache ) ) {
			self::_load_csv_file();
			$match_cache = array();
		}

		// Look for field/value qualifier
		$match_count = preg_match( '/^(.+)\((.+)\)/', $value['value'], $matches );
		if ( $match_count ) {
			$field = $matches[1];
			$qualifier = $matches[2];
		} else {
			$field = $value['value'];
			$qualifier = '';
		}

		// Set debug mode
		$debug_active = isset( $query['mla_debug'] ) && ( 'false' !== trim( strtolower( $query['mla_debug'] ) ) );
		if ( $debug_active ) {
			$old_mode = MLACore::mla_debug_mode( 'log' );
			MLACore::mla_debug_add( __LINE__ . " MLACSVDataSourceExample::mla_expand_custom_prefix( {$key}, {$post_id}, {$keep_existing}, {$default_option} ) \$_REQUEST = " . var_export( $_REQUEST, true ) );
			MLACore::mla_debug_add( __LINE__ . " MLACSVDataSourceExample::mla_expand_custom_prefix( {$field}, {$qualifier} ) \$value = " . var_export( $value, true ) );
			MLACore::mla_debug_add( __LINE__ . " MLACSVDataSourceExample::mla_expand_custom_prefix() \$query = " . var_export( $query, true ) );
			MLACore::mla_debug_add( __LINE__ . " MLACSVDataSourceExample::mla_expand_custom_prefix() \$markup_values = " . var_export( $markup_values, true ) );
		}

		$match_index = array_search( $field, self::$_csv_variable_names );

		if ( false === $match_index ) {
			return '';
		}

		$csv_variables = false;
		if ( isset( $match_cache[ $post_id ] ) ) {
			$csv_variables = $match_cache[ $post_id ];
		} else {
			$csv_variables = self::_csv_search( $post_id );
			if ( false !== $csv_variables ) {
				$match_cache[ $post_id ] = $csv_variables;
			}
		}

		if ( ( false === $csv_variables ) || empty( $csv_variables[ $match_index ] ) ){
			return '';
		}

		return $csv_variables[ $match_index ];
	} // mla_expand_custom_prefix
} //MLACSVDataSourceExample

// Install the filters at an early opportunity
add_action('init', 'MLACSVDataSourceExample::initialize');
?>
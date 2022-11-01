<?php
/**
 * Adds hierarchical path specification to the IPTC/EXIF taxonomy mapping features
 *
 * For example, "/Root/Parent/Child" creates or locates:
 *  - A root term named "Root"
 *  - A child term named "Parent" under "Root"
 *  - A child term named "Child" under "Parent"
 *
 * Much more information is in the Settings/MLA Path Mapping Documentation tab.
 *
 * Inspired by support topic "Help with Custom Taxonomy"
 * opened on 11/25/2017 by "wesm".
 * https://wordpress.org/support/topic/help-with-custom-taxonomy/
 *
 * @package MLA Path Mapping Example
 * @version 1.02
 */

/*
Plugin Name: MLA Path Mapping Example
Plugin URI: http://davidlingren.com/
Description: Adds hierarchical path specification to the IPTC/EXIF taxonomy mapping features
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
 * Class MLA Path Mapping Example hooks one of the filters provided by the IPTC/EXIF and Custom Field mapping features
 *
 * Call it anything you want, but give it an unlikely and hopefully unique name. Hiding everything
 * else inside a class means this is the only name you have to worry about.
 *
 * @package MLA Path Mapping Example
 * @since 1.00
 */
class MLAPathMappingExample {
	/**
	 * Current version number
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const CURRENT_VERSION = '1.02';

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'mlapathmap';

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
		/*
		 * The filters are only useful in the admin section; exit if in the "front-end" posts/pages. 
		 */
		if ( ! is_admin() )
			return;
		
		// Add submenu page in the "Settings" section
		add_action( 'admin_menu', 'MLAPathMappingExample::admin_menu' );

		// add filters for IPTC/EXIF mapping rule execution
		add_filter( 'mla_mapping_rule', 'MLAPathMappingExample::mla_mapping_rule', 10, 4 );
		add_filter( 'mla_mapping_new_text', 'MLAPathMappingExample::mla_mapping_new_text', 10, 5 );
		add_filter( 'mla_mapping_updates', 'MLAPathMappingExample::mla_mapping_updates', 10, 5 );
	}

	/**
	 * Add submenu page in the "Settings" section
	 *
	 * @since 1.00
	 */
	public static function admin_menu( ) {
		/*
		 * We need a tab-specific page ID to manage the screen options on the General tab.
		 * Use the URL suffix, if present. If the URL doesn't have a tab suffix, use '-general'.
		 * This hack is required to pass the WordPress "referer" validation.
		 */
		 if ( isset( $_REQUEST['page'] ) && is_string( $_REQUEST['page'] ) && ( self::SLUG_PREFIX . '-settings-' == substr( $_REQUEST['page'], 0, strlen( self::SLUG_PREFIX . '-settings-' ) ) ) ) {
			$tab = substr( $_REQUEST['page'], strlen( self::SLUG_PREFIX . '-settings-' ) );
		 } else {
			$tab = 'general';
		 }

		$tab = self::_get_options_tablist( $tab ) ? '-' . $tab : '-general';
		add_submenu_page( 'options-general.php', 'MLA Path Mapping Example', 'MLA Path Mapping', 'manage_options', self::SLUG_PREFIX . '-settings' . $tab, 'MLAPathMappingExample::add_submenu_page' );
		add_filter( 'plugin_action_links', 'MLAPathMappingExample::plugin_action_links', 10, 2 );
	}

	/**
	 * Add the "Settings" and "Guide" links to the Plugins section entry
	 *
	 * @since 1.00
	 *
	 * @param	array 	array of links for the Plugin, e.g., "Activate"
	 * @param	string 	Directory and name of the plugin Index file
	 *
	 * @return	array	Updated array of links for the Plugin
	 */
	public static function plugin_action_links( $links, $file ) {
		if ( $file == 'mla-path-mapping-example/mla-path-mapping-example.php' ) {
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . self::SLUG_PREFIX . '-settings-documentation&mla_tab=documentation' ), 'Guide' );
			array_unshift( $links, $settings_link );
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . self::SLUG_PREFIX . '-settings-general' ), 'Settings' );
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	/**
	 * Render (echo) the "MLA Path Mapping" submenu in the Settings section
	 *
	 * @since 1.00
	 *
	 * @return	void Echoes HTML markup for the submenu page
	 */
	public static function add_submenu_page() {
//error_log( __LINE__ . " MLAPathMappingExample:add_submenu_page _REQUEST = " . var_export( $_REQUEST, true ), 0 );

		if ( !current_user_can( 'manage_options' ) ) {
			echo "<h2>MLA Path Mapping Example - Error</h2>\n";
			wp_die( 'You do not have permission to manage plugin settings.' );
		}

		// Load template array and initialize page-level values.
		self::$page_template_array = MLACore::mla_load_template( dirname( __FILE__ ) . '/admin-settings-page.tpl', 'path' );
		$current_tab_slug = isset( $_REQUEST['mla_tab'] ) ? $_REQUEST['mla_tab']: 'general';
		$current_tab = self::_get_options_tablist( $current_tab_slug );
		$page_values = array(
			'version' => 'v' . self::CURRENT_VERSION,
			'messages' => '',
			'tablist' => self::_compose_settings_tabs( $current_tab_slug ),
			'tab_content' => '',
		);

		// Compose tab content
		if ( $current_tab ) {
			if ( isset( $current_tab['render'] ) ) {
				$handler = $current_tab['render'];
				$page_content = call_user_func( $handler );
			} else {
				$page_content = array( 'message' => 'ERROR: Cannot render content tab', 'body' => '' );
			}
		} else {
			$page_content = array( 'message' => 'ERROR: Unknown content tab', 'body' => '' );
		}

		if ( ! empty( $page_content['message'] ) ) {
			if ( false !== strpos( $page_content['message'], 'ERROR' ) ) {
				$messages_class = 'updated error';
				$dismiss_button = '';
			} else {
				$messages_class = 'updated notice is-dismissible';
				$dismiss_button = "  <button class=\"notice-dismiss\" type=\"button\"><span class=\"screen-reader-text\">[+dismiss_text+].</span></button>\n";
			}

			$page_values['messages'] = MLAData::mla_parse_template( self::$page_template_array['messages'], array(
				 'mla_messages_class' => $messages_class ,
				 'messages' => $page_content['message'],
				 'dismiss_button' => $dismiss_button,
				 'dismiss_text' => 'Dismiss this notice',
			) );
		}

		$page_values['tab_content'] = $page_content['body'];
		echo MLAData::mla_parse_template( self::$page_template_array['page'], $page_values );
	}

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
		'general' => array( 'title' => 'General', 'render' => array( 'MLAPathMappingExample', '_compose_general_tab' ) ),
		'documentation' => array( 'title' => 'Documentation', 'render' => array( 'MLAPathMappingExample', '_compose_documentation_tab' ) ),
		);

	/**
	 * Retrieve the list of options tabs or a specific tab value
	 *
	 * @since 1.00
	 *
	 * @param	string	Tab slug, to retrieve a single entry
	 *
	 * @return	array|false	The entire tablist ( $tab = NULL ), a single tab entry or false if not found/not allowed
	 */
	private static function _get_options_tablist( $tab = NULL ) {
		if ( is_string( $tab ) ) {
			if ( isset( self::$mla_tablist[ $tab ] ) ) {
				$results = self::$mla_tablist[ $tab ];
			} else {
				$results = false;
			}
		} else {
			$results = self::$mla_tablist;
		}

		return $results;
	}

	/**
	 * Compose the navigation tabs for the Settings subpage
	 *
	 * @since 1.00
	 * @uses $page_template_array contains tablist and tablist-item templates
 	 *
	 * @param	string	Optional data-tab-id value for the active tab, default 'general'
	 *
	 * @return	string	HTML markup for the Settings subpage navigation tabs
	 */
	private static function _compose_settings_tabs( $active_tab = 'general' ) {
		$tablist_item = self::$page_template_array['tablist-item'];
		$tabs = '';
		foreach ( self::_get_options_tablist() as $key => $item ) {
			$item_values = array(
				'data-tab-id' => $key,
				'nav-tab-active' => ( $active_tab == $key ) ? 'nav-tab-active' : '',
				'settings-page' => self::SLUG_PREFIX . '-settings-' . $key,
				'title' => $item['title']
			);

			$tabs .= MLAData::mla_parse_template( $tablist_item, $item_values );
		} // foreach $item

		$tablist_values = array( 'tablist' => $tabs );
		return MLAData::mla_parse_template( self::$page_template_array['tablist'], $tablist_values );
	}

	/**
	 * Compose the General tab content for the Settings subpage
	 *
	 * @since 1.00
	 * @uses $page_template_array contains tab content template(s)
 	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private static function _compose_general_tab( ) {
		$page_content = array( 'message' => '', 'body' => '' );

		// Initialize page messages and content, check for page-level Save Changes
		if ( !empty( $_REQUEST['mla-path-mapping-options-save'] ) ) {
			check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );
			$page_content = self::_save_setting_changes( );
		}

		if ( !empty( $page_content['body'] ) ) {
			return $page_content;
		}

		// Display the General tab
		$_SERVER['REQUEST_URI'] = remove_query_arg( array(
			'mla_path_mapping_options',
			'_wpnonce',
			'_wp_http_referer',
			'mla-path-mapping-options-save',
		), $_SERVER['REQUEST_URI'] );

		// Compose page-level options
		$page_values = array(
			'assign_parents_checked' => self::_get_plugin_option('assign_parents') ? 'checked="checked" ' : '',
			'assign_rule_parent_checked' => self::_get_plugin_option('assign_rule_parent') ? 'checked="checked" ' : '',
			'path_delimiter' => self::_get_plugin_option('path_delimiter'),
		);
		$options_list = MLAData::mla_parse_template( self::$page_template_array['page-level-options'], $page_values );

		$form_arguments = '?page=' . self::SLUG_PREFIX . '-settings-general&mla_tab=general';

		$page_values = array(
			'form_url' => admin_url( 'options-general.php' ) . $form_arguments,
			'_wpnonce' => wp_nonce_field( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME, true, false ),
			'options_list' => $options_list,
		);

		$page_content['body'] .= MLAData::mla_parse_template( self::$page_template_array['general-tab'], $page_values );

		return $page_content;
	}

	/**
	 * Compose the General tab content for the Settings subpage
	 *
	 * @since 1.00
	 * @uses $page_template_array contains tab content template(s)
 	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private static function _compose_documentation_tab( ) {
		$page_content = array( 'message' => '', 'body' => '' );
		$page_values = array(
		);

		$page_content['body'] = MLAData::mla_parse_template( self::$page_template_array['documentation-tab'], $page_values );
		return $page_content;
	}

	/**
	 * Save settings as a WordPress wp_options entry
	 *
	 * @since 1.00
	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private static function _save_setting_changes() {
		$page_content = array( 'message' => 'Settings unchanged.', 'body' => '' );
		
		$changed  = self::_update_plugin_option( 'assign_parents', isset( $_REQUEST[ 'mla_path_mapping_options' ]['assign_parents'] ) );
		$changed |= self::_update_plugin_option( 'assign_rule_parent', isset( $_REQUEST[ 'mla_path_mapping_options' ]['assign_rule_parent'] ) );
		$changed |= self::_update_plugin_option( 'path_delimiter', stripslashes( $_REQUEST[ 'mla_path_mapping_options' ]['path_delimiter'] ) );
		
		if ( $changed ) {
			// No reason to save defaults in the database
			if ( self::$_settings === self::$_default_settings ) {
				delete_option( self::SLUG_PREFIX . '-settings' ); 
			} else {
				$changed = update_option( self::SLUG_PREFIX . '-settings', self::$_settings, false );
			}

			if ( $changed ) {
				$page_content['message'] = "Settings have been updated.";
			} else {
				$page_content['message'] = "Settings updated failed.";
			}
		}

		return $page_content;		
	} // _save_setting_changes

	/**
	 * Assemble the in-memory representation of the custom feed settings
	 *
	 * @since 1.00
	 *
	 * @param boolean $force_refresh Optional. Force a reload of rules. Default false.
	 * @return boolean Success (true) or failure (false) of the operation
	 */
	private static function _get_custom_feed_settings( $force_refresh = false ) {
		if ( false == $force_refresh && NULL != self::$_settings ) {
			return true;
		}

		// Update the plugin options from the wp_options table or set defaults
		self::$_settings = get_option( self::SLUG_PREFIX . '-settings' );
		if ( !is_array( self::$_settings ) ) {
			self::$_settings = self::$_default_settings;
		}

		return true;
	}

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
						'assign_parents' => false,
						'assign_rule_parent' => false,
						'path_delimiter' => '/',
					);

	/**
	 * Get a custom feed option setting
	 *
	 * @since 1.00
	 *
	 * @param string	$name Option name
	 *
	 * @return	mixed	Option value, if it exists else NULL
	 */
	private static function _get_plugin_option( $name ) {
		if ( !self::_get_custom_feed_settings() ) {
			return NULL;
		}

		if ( !isset( self::$_settings[ $name ] ) ) {
			return NULL;
		}
		
		return self::$_settings[ $name ];
	}

	/**
	 * Update a custom feed option setting
	 *
	 * @since 1.00
	 *
	 * @param string $name Option name
	 * @param mixed	$new_value Option value
	 *
	 * @return mixed True if option value changed, false if value unchanged, NULL if failure
	 */
	private static function _update_plugin_option( $name, $new_value ) {
		if ( !self::_get_custom_feed_settings() ) {
			return NULL;
		}

		$old_value = isset( self::$_settings[ $name ] ) ? self::$_settings[ $name ] : NULL;
		
		if ( $new_value === $old_value ) {
			return false;
		}
		
		self::$_settings[ $name ] = $new_value;
		return true;
	}

	/**
	 * MLA Mapping Rule Filter
	 *
	 * This filter is called once for each mapping rule, before the rule
	 * is evaluated. You can change the rule parameters, or prevent rule
	 * evaluation by returning $setting_value['data_source'] = 'none'; 
	 *
	 * @since 1.00
	 *
	 * @param	array 	custom_field_mapping rule
	 * @param	integer post ID to be evaluated
	 * @param	string 	category/scope to evaluate against: custom_field_mapping or single_attachment_mapping
	 * @param	array 	attachment_metadata, default NULL
	 *
	 * @return	array	updated custom_field_mapping rule
	 */
	public static function mla_mapping_rule( $setting_value, $post_id, $category, $attachment_metadata ) {
		if ( $setting_value['active'] ) {
			//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_rule_filter( {$post_id}, {$category} ) setting_value = " . var_export( $setting_value, true ), 0 );
			//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_rule_filter( {$post_id}, {$category} ) attachment_metadata = " . var_export( $attachment_metadata, true ), 0 );

			self::$current_rule = $setting_value;
		}
		
		return $setting_value;
	} // mla_mapping_rule_filter

	/**
	 * Save the mapping rule so mla_mapping_new_text can use, e.g., parent
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $current_rule = array();

	/**
	 * MLA Mapping New Text Filter
	 *
	 * This filter is called once for each IPTC/EXIF mapping rule, after the selection
	 * between the IPTC and EXIF values has been made. You can change the new value
	 * produced by the rule.
	 *
	 * @since 1.00
	 *
	 * @param	mixed 	string or array value returned by the rule
	 * @param	string 	rule key - standard field slug, taxonomy slug or custom field name
	 * @param	integer post ID to be evaluated
	 * @param	string 	category/scope to evaluate against: iptc_exif_standard_mapping, iptc_exif_taxonomy_mapping or iptc_exif_custom_mapping
	 * @param	array 	attachment_metadata, default NULL
	 *
	 * @return	array	updated rule EXIF/Template value
	 */
	public static function mla_mapping_new_text( $new_text, $setting_key, $post_id, $category, $attachment_metadata ) {
		// $term_cache = array( [ taxonomy/setting_key ][ parent term_id ][ term name ] => array( term_id, term_taxonomy_id )
		// $unqualified_cache = array( [ taxonomy/setting_key ][ term name ] => array( term_id, term_taxonomy_id )
		// $rule_parent = array( [ taxonomy/setting_key ] => WP_Term object )
		static $term_cache = array(), $unqualified_cache = array(), $rule_parent = array();
		
		// We only care about taxonomies
		if ( 'iptc_exif_taxonomy_mapping' !== $category ) {
			return  $new_text;
		}

		// We only care about hierarchical taxonomies
		if ( !is_taxonomy_hierarchical( $setting_key ) ) {
			return  $new_text;
		}
		
		//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_new_text_filter( {$setting_key}, {$post_id}, {$category} ) new_text = " . var_export( $new_text, true ), 0 );
		//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_new_text_filter( {$setting_key}, {$post_id}, {$category} ) attachment_metadata = " . var_export( $attachment_metadata, true ), 0 );

		// Make sure $new_text is an array, even if it's empty
		if ( empty( $new_text ) ) {
			$new_text = array();
		} elseif ( is_string( $new_text ) ) {
			$new_text = array( $new_text );
		}

		$term_parent = !empty( self::$current_rule['parent'] ) ? absint( self::$current_rule['parent'] ) : 0;
		$assign_rule_parent = $term_parent ? self::_get_plugin_option('assign_rule_parent') : false;
		$assign_parents = self::_get_plugin_option('assign_parents');
		$path_delimiter = self::_get_plugin_option('path_delimiter');

		// Check for and validate Rule Parent
		if ( $assign_rule_parent && empty( $rule_parent[ $setting_key ] ) ) {
			$term = get_term( $term_parent, $setting_key );
			if ( !empty( $term ) && !is_wp_error( $term ) ) {
				$rule_parent[ $setting_key ] = $term;
			} else {
				$term_parent = 0;
			}
		}

		// We must build the "public" cache from scratch for each item
		MLAOptions::$mla_term_cache[ $setting_key ][ $term_parent ] = array();
		
		// Initialize the higher-level cache elements to simplify the code
		if ( !isset( $unqualified_cache[ $setting_key ] ) ) {
			$unqualified_cache[ $setting_key ] = array();
		}
		
		if ( !isset( $term_cache[ $setting_key ] ) ) {
			$term_cache[ $setting_key ] = array();
		}
		
		if ( !isset( $term_cache[ $setting_key ][ $term_parent ] ) ) {
			$term_cache[ $setting_key ][ $term_parent ] = array();
		}

		/*
		 * Every element in $new_text must be added, as entered, to the $mla_term_cache
		 * so it is matched in MLAOptions::_get_term_id.
		 * An internal $term_cache holds each "path component" with the actual parent location.
		 * The $unqualified_cache holds each "path component" when the parent is unknown.
		 */
		foreach ( $new_text as $term_name ) {
			// WordPress encodes special characters, e.g., "&" as HTML entities in term names
			$term_name = _wp_specialchars( $term_name );

			// Is this a simple, unqualified term already in the cache?
			if ( isset( $unqualified_cache[ $setting_key ][ $term_name ] ) ) {
//error_log( __LINE__ . " mla_mapping_new_text found $term_name in unqualified_cache", 0 );
				MLAOptions::$mla_term_cache[ $setting_key ][ $term_parent ][ $term_name ] = $unqualified_cache[ $setting_key ][ $term_name ]['term_id'];
				continue;
			}

			// Break the path, if present, into its component parts
			$path = explode( $path_delimiter, $term_name );
			
			// Check for an absolute path, initialize $current_id
			$unqualified_name = false;
			if ( empty( $path[0] ) ) {
				$current_parent = 0; // Root-level "parent"
			} elseif ( 0 === $term_parent ) {
				// Unqualified name - no parent restriction
				$unqualified_name = true;
				$current_parent = 'unqualified';
			} else {
				$current_parent = $term_parent;
			}
//error_log( __LINE__ . " mla_mapping_new_text parent = " . var_export( $current_parent, true ) . ", path = " . var_export( $path, true ), 0 );

			// Holder for the $assign_parents entries
			$assign_parents_entry = 0;
			
			// Find or create all the path components and add them to the cache
			foreach ( $path as $path_name ) {
				// Check for a parent assignment
				if ( 0 < $assign_parents_entry ) {
					// Generate an unlikely name, create a new entry
					$name = "assign_parent-term-{$assign_parents_entry}";
					MLAOptions::$mla_term_cache[ $setting_key ][ $term_parent ][ $name ] = $assign_parents_entry;
					$new_text[] = $name;
					$assign_parents_entry = 0;
				}
				
				// Ignore initial or duplicate delimiters
				if ( empty( $path_name ) ) {
//error_log( __LINE__ . " mla_mapping_new_text ignoring empty path_name", 0 );
					continue;
				}
				
				// Is this component in the cache?
				if ( isset( $term_cache[ $setting_key ][ $current_parent ][ $path_name ] ) ) {
//error_log( __LINE__ . " mla_mapping_new_text found $path_name under $current_parent in cache", 0 );
					$current_parent = $term_cache[ $setting_key ][ $current_parent ][ $path_name ]['term_id'];
					
					if ( $assign_parents ) {
						$assign_parents_entry = $current_parent;
					}
					
					continue;
				}
				
				// Does this component exist?
				if ( $unqualified_name ) {
					// Is this component in the cache?
					if ( isset( $unqualified_cache[ $setting_key ][ $path_name ] ) ) {
//error_log( __LINE__ . " mla_mapping_new_text found $path_name in unqualified cache", 0 );
						$path_term = $unqualified_cache[ $setting_key ][ $path_name ];
					} else {
						$path_term = term_exists( $path_name, $setting_key );

						if ( $path_term !== 0 && $path_term !== NULL ) {
//error_log( __LINE__ . " mla_mapping_new_text adding $path_name to unqualified cache", 0 );
							$unqualified_cache[ $setting_key ][ $path_name ] = $path_term;
						}
					}

					$unqualified_name = false;
				} else {
					$path_term = term_exists( $path_name, $setting_key, $current_parent );
				}
				
				if ( $path_term !== 0 && $path_term !== NULL ) {
//error_log( __LINE__ . " mla_mapping_new_text found $path_name under $current_parent in database = " . var_export( $path_term, true ), 0 );
					$term_cache[ $setting_key ][ $current_parent ][ $path_name ] = $path_term;
					$current_parent = absint( $path_term['term_id'] );
					
					if ( $assign_parents ) {
						$assign_parents_entry = $current_parent;
					}
					
					continue;
				}

				// Create the term 
				$path_term = wp_insert_term( $path_name, $setting_key, array( 'parent' => $current_parent ) );
				if ( ( ! is_wp_error( $path_term ) ) && isset( $path_term['term_id'] ) ) {
//error_log( __LINE__ . " mla_mapping_new_text created $path_name under $current_parent = " . var_export( $path_term, true ), 0 );
					$term_cache[ $setting_key ][ $current_parent ][ $path_name ] = $path_term;
					$current_parent = absint( $path_term['term_id'] );
					
					if ( $assign_parents ) {
						$assign_parents_entry = $current_parent;
					}
				}
			} // foreach path component

			// Finally, create a public entry based on the (path and) name as entered
			MLAOptions::$mla_term_cache[ $setting_key ][ $term_parent ][ $term_name ] = $current_parent;
		} // foreach term name
		
		// Create an entry with an unlikely name for the Rule Parent, if selected
		if ( $assign_rule_parent && isset( $rule_parent[ $setting_key ] ) ) {
			MLAOptions::$mla_term_cache[ $setting_key ][ $term_parent ]['rule_parent-term'] = $rule_parent[ $setting_key ]->term_id;
			$new_text[] = 'rule_parent-term';			
		}

//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_new_text_filter( {$setting_key}, {$post_id}, {$category} ) mla term cache = " . var_export( MLAOptions::$mla_term_cache, true ), 0 );
		return  $new_text;
	} // mla_mapping_new_text_filter

	/**
	 * MLA Mapping Updates Filter
	 *
	 * This filter is called AFTER all mapping rules are applied.
	 * You can add, change or remove updates for the attachment's
	 * standard fields, taxonomies and/or custom fields.
	 *
	 * @since 1.00
	 *
	 * @param	array	updates for the attachment's standard fields, taxonomies and/or custom fields
	 * @param	integer post ID to be evaluated
	 * @param	string 	category/scope to evaluate against: custom_field_mapping or single_attachment_mapping
	 * @param	array 	mapping rules
	 * @param	array 	attachment_metadata, default NULL
	 *
	 * @return	array	updated attachment's updates
	 */
	public static function mla_mapping_updates( $updates, $post_id, $category, $settings, $attachment_metadata ) {
		//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_updates_filter( {$post_id}, {$category} ) updates = " . var_export( $updates, true ), 0 );
		//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_updates_filter( {$post_id}, {$category} ) settings = " . var_export( $settings, true ), 0 );
		//error_log( __LINE__ . " MLAPathMappingExample::mla_mapping_updates_filter( {$post_id}, {$category} ) attachment_metadata = " . var_export( $attachment_metadata, true ), 0 );

		// To stop this rule's updates, return an empty array, i.e., return array();
		return $updates;
	} // mla_mapping_updates_filter
} //MLAPathMappingExample

/*
 * Install the filters at an early opportunity
 */
add_action('init', 'MLAPathMappingExample::initialize');
?>
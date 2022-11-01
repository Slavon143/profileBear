<?php
/**
 * Uses a Custom Field mapping rule to map data sources for an "attached" item to custom fields of the parent page/post.
 *
 * This plugin looks for a "parent:" prefix in the slug/name field for a custom field rule.
 * When the prefix is present, the rule is applied to the parent page/post (if present)
 * of the item instead of the item itself.
 *
 * Detailed information is in the Settings/MLA Parent Custom Field Mapping Documentation tab.
 *
 * Created for support topic "IPTC/EXIF Mapping on picture upload to parent post’s custom fields"
 * opened on 7/29 2020 by "fusselfrosch":
 * https://wordpress.org/support/topic/iptc-exif-mapping-on-picture-upload-to-parent-posts-custom-fields/
 *
 * @package MLA Parent Custom Field Mapping
 * @version 1.07
 */

/*
Plugin Name: MLA Parent Custom Field Mapping
Plugin URI: http://davidlingren.com/
Description: Uses a Custom Field mapping rule to map data sources for an "attached" item to custom fields of the parent page/post
Author: David Lingren
Version: 1.07
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
 * Class MLA Parent Custom Field Mapping hooks some of the filters provided by the IPTC/EXIF and Custom Field mapping features
 *
 * @package MLA Parent Custom Field Mapping
 * @since 1.00
 */
class MLAParentCustomFieldMapping {
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
	 * @since 1.00
	 *
	 * @var	integer
	 */
	const MLA_DEBUG_CATEGORY = 0x00008000;

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 1.07
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'mlaparentcustomfieldmapping';

	/**
	 * Include support for Advanced Custom Fields
	 *
	 * @since 1.07
	 *
	 * @var	booean
	 */
	public static $acf_active = false;

	/**
	 * Include support for WP/LR Sync
	 *
	 * @since 1.07
	 *
	 * @var	boolean
	 */
	public static $wplr_active = false;

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
		$plugin_version = 'MLAParentCustomFieldMapping::initialize( version ' . self::PLUGIN_VERSION . ' )';
		$skip_filters = true;

		// This plugin requires MLA
		if ( ! class_exists( 'MLACore', false ) ) {
			return;
		}
		
		if ( is_admin() ) {
			// Add submenu page in the "Settings" section
			add_action( 'admin_menu', 'MLAParentCustomFieldMapping::admin_menu' );

			MLACore::mla_debug_add( __LINE__ . " {$plugin_version} is_admin", self::MLA_DEBUG_CATEGORY );
			$skip_filters = false;
		}

		if ( isset( $_SERVER['REQUEST_URI'] ) && false !== strpos( $_SERVER['REQUEST_URI'], '/?wplr-sync-api' ) ) {
			MLACore::mla_debug_add( __LINE__ . " {$plugin_version} is_wplr_sync", self::MLA_DEBUG_CATEGORY );
			$skip_filters = false;
		}

		if ( isset( $_SERVER['REQUEST_URI'] ) && 0 === strpos( $_SERVER['REQUEST_URI'], '/wp-json/' ) ) {
			MLACore::mla_debug_add( __LINE__ . " {$plugin_version} WP REST API", self::MLA_DEBUG_CATEGORY );
			$skip_filters = false;
		}

		if ( true === $skip_filters ) {
			MLACore::mla_debug_add( __LINE__ . " {$plugin_version} override skip_filters", self::MLA_DEBUG_CATEGORY );
			$skip_filters = false;
		}

		// The filters are only useful in the admin section, REST API or wplr-sync-api
		if ( $skip_filters ) {
			return;
		}

		// Determine Advanced Custom Fields support; default false
		if ( class_exists( 'ACF', false ) ) {
			self::$acf_active = self::_get_plugin_option('acf_support');
		}
				
		// Determine WP/LR Sync support; default false
		if ( class_exists( 'Meow_WPLR_Sync_Core', false ) ) {
			self::$wplr_active = self::_get_plugin_option('wplr_support');
		}
		
		add_filter( 'mla_mapping_settings', 'MLAParentCustomFieldMapping::mla_mapping_settings', 10, 4 );
		add_filter( 'mla_purge_custom_field_values', 'MLAParentCustomFieldMapping::mla_purge_custom_field_values', 10, 4 );
	} // initialize

	/**
	 * Add submenu page in the "Settings" section
	 *
	 * @since 1.07
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
		add_submenu_page( 'options-general.php', 'MLA Parent Custom Field Mapping', 'MLA Parent Mapping', 'manage_options', self::SLUG_PREFIX . '-settings' . $tab, 'MLAParentCustomFieldMapping::add_submenu_page' );
		add_filter( 'plugin_action_links', 'MLAParentCustomFieldMapping::plugin_action_links', 10, 2 );
	}

	/**
	 * Add the "Settings" and "Guide" links to the Plugins section entry
	 *
	 * @since 1.07
	 *
	 * @param	array 	array of links for the Plugin, e.g., "Activate"
	 * @param	string 	Directory and name of the plugin Index file
	 *
	 * @return	array	Updated array of links for the Plugin
	 */
	public static function plugin_action_links( $links, $file ) {
		if ( $file == 'mla-parent-custom-field-mapping/mla-parent-custom-field-mapping.php' ) {
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . self::SLUG_PREFIX . '-settings-documentation&mla_tab=documentation' ), 'Guide' );
			array_unshift( $links, $settings_link );
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . self::SLUG_PREFIX . '-settings-general' ), 'Settings' );
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	/**
	 * Render (echo) the "MLA Parent Mapping" submenu in the Settings section
	 *
	 * @since 1.07
	 *
	 * @return	void Echoes HTML markup for the submenu page
	 */
	public static function add_submenu_page() {
		if ( !current_user_can( 'manage_options' ) ) {
			echo "<h2>MLA Parent Custom Field Mapping - Error</h2>\n";
			wp_die( 'You do not have permission to manage plugin settings.' );
		}

		// Load template array and initialize page-level values.
		self::$page_template_array = MLACore::mla_load_template( dirname( __FILE__ ) . '/admin-settings-page.tpl', 'path' );
		$current_tab_slug = isset( $_REQUEST['mla_tab'] ) ? $_REQUEST['mla_tab']: 'general';
		$current_tab = self::_get_options_tablist( $current_tab_slug );
		$page_values = array(
			'version' => 'v' . self::PLUGIN_VERSION,
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
	 * @since 1.07
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
	 * @since 1.07
	 *
	 * @var	array
	 */
	private static $mla_tablist = array(
		'general' => array( 'title' => 'General', 'render' => array( 'MLAParentCustomFieldMapping', '_compose_general_tab' ) ),
		'documentation' => array( 'title' => 'Documentation', 'render' => array( 'MLAParentCustomFieldMapping', '_compose_documentation_tab' ) ),
		);

	/**
	 * Retrieve the list of options tabs or a specific tab value
	 *
	 * @since 1.07
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
	 * @since 1.07
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
	 * @since 1.07
	 * @uses $page_template_array contains tab content template(s)
 	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private static function _compose_general_tab( ) {
		$page_content = array( 'message' => '', 'body' => '' );

		// Initialize page messages and content, check for page-level Save Changes
		if ( !empty( $_REQUEST['mla-parent-custom-field-mapping-options-save'] ) ) {
			check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );
			$page_content = self::_save_setting_changes( );
		}

		if ( !empty( $page_content['body'] ) ) {
			return $page_content;
		}

		// Display the General tab
		$_SERVER['REQUEST_URI'] = remove_query_arg( array(
			'mla_parent_custom_field_mapping_options',
			'_wpnonce',
			'_wp_http_referer',
			'mla-parent-custom-field-mapping-options-save',
		), $_SERVER['REQUEST_URI'] );

		// Compose page-level options
		$page_values = array(
			'acf_support_checked' => self::_get_plugin_option('acf_support') ? 'checked="checked" ' : '',
			'wplr_support_checked' => self::_get_plugin_option('wplr_support') ? 'checked="checked" ' : '',
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
	 * @since 1.07
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
	 * @since 1.07
	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private static function _save_setting_changes() {
		$page_content = array( 'message' => 'Settings unchanged.', 'body' => '' );

		$changed = self::_update_plugin_option( 'acf_support', isset( $_REQUEST[ 'mla_parent_custom_field_mapping_options' ]['acf_support'] ) );
		$changed |= self::_update_plugin_option( 'wplr_support', isset( $_REQUEST[ 'mla_parent_custom_field_mapping_options' ]['wplr_support'] ) );

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
	 * Assemble the in-memory representation of the plugin settings
	 *
	 * @since 1.07
	 *
	 * @param boolean $force_refresh Optional. Force a reload of rules. Default false.
	 *
	 * @return boolean Success (true) or failure (false) of the operation
	 */
	private static function _get_plugin_settings( $force_refresh = false ) {
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
	 * @since 1.07
	 *
	 * @var array $_settings {
	 *     @type boolean $acf_support True to add support for ACF and ACF Pro
	 *     @type boolean $verify_post_type True to add support for WP/LR Sync
	 *     }
	 */
	private static $_settings = NULL;

	/**
	 * Default processing options
	 *
	 * @since 1.07
	 *
	 * @var	array
	 */
	private static $_default_settings = array (
				'acf_support' => true,
				'wplr_support' => true,
			);

	/**
	 * Get a plugin option setting
	 *
	 * @since 1.07
	 *
	 * @param string	$name Option name
	 *
	 * @return	mixed	Option value, if it exists else NULL
	 */
	private static function _get_plugin_option( $name ) {
		if ( !self::_get_plugin_settings() ) {
			return NULL;
		}

		if ( !isset( self::$_settings[ $name ] ) ) {
			return NULL;
		}

		return self::$_settings[ $name ];
	}

	/**
	 * Update a plugin option setting
	 *
	 * @since 1.07
	 *
	 * @param string $name Option name
	 * @param mixed	$new_value Option value
	 *
	 * @return mixed True if option value changed, false if value unchanged, NULL if failure
	 */
	private static function _update_plugin_option( $name, $new_value ) {
		if ( !self::_get_plugin_settings() ) {
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
	 * Cache the "parent:" rules
	 *
	 * Array elements are:
	 *		'name' => string
	 *		'data_source' => string
	 *		'meta_name' => string
	 *		'format' => 'native', 'commas' or 'raw'
	 *		'option' => 'text', 'single', 'export', 'array' or 'multi'
	 *		'keep_existing' => boolean
	 *		'no_null' => boolean
	 *		'mla_column' => boolean
	 *		'quick_edit' => boolean
	 *		'bulk_edit' => boolean
	 *		'active' => boolean
	 *
	 * @since 1.00
	 *
	 * @var	array ( 'rule_slug' => array( rule elements ) )
	 */
	private static $parent_rules = NULL;

	/**
	 * Cache post_parent
	 *
	 * @since 1.00
	 *
	 * @var	integer 
	 */
	private static $post_parent = 0;

	/**
	 * Cache updates for parent post for wplr processing
	 *
	 * @since 1.03
	 *
	 * @var	array	( post_id => array( parent_field => new_value ) )
	 */
	private static $parent_updates = array();

	/**
	 * Copy rules with the "parent:" prefix to the self::$parent_rules array
	 *
	 * @since 1.01
	 *
	 * @param	array 	mapping rules
	 * @param	boolean	Optional, default false. True to add rules to an existing self::$parent_rules array
	 *
 	 * @uses	array 	self::$parent_rules
	 */
	private static function _load_parent_rules( $settings, $append = false ) {
		if ( false === $append ) {
			self::$parent_rules = array();
		}

		foreach( $settings as $slug => $rule ) {
			if ( $rule['active'] && ( 0 === strpos( $rule['name'], 'parent:' ) ) ) {
				$placeholder = current( MLAData::mla_get_template_placeholders( '[+' . $rule['name'] . '+]', $rule['option'] ) );
				//MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::_load_parent_rules( {$slug} ) placeholder = " . var_export( $placeholder, true ), self::MLA_DEBUG_CATEGORY );
				$rule['parent_field'] = $placeholder['value'];
				$rule['parent_format'] = $placeholder['format'];
				$rule['parent_action'] = '';
				$rule['repeater_field'] = '';

				if ( in_array( $placeholder['format'], array( 'acf', ) ) ) {
					if ( !empty( $placeholder['args'] ) ) {
						$args = explode( '.', $placeholder['args'] );
						if ( 1 < count( $args ) ) {
							$rule['parent_action'] = $args[0];
							$rule['repeater_field'] = $args[1];
						} else {
							$rule['parent_action'] = $placeholder['args'];
						}
					}
				}

				self::$parent_rules[ $slug ] = $rule;
			}
		} // foreach rule
	} // _load_parent_rules

	/**
	 * Remove explicit subield rules from self::$parent_rulles and
	 * replace repeater rules with all defined subfield rules
	 *
	 * @since 1.05
	 *
	 * @param	array 	$settings mapping rules
	 * @param	string 	$category scope to evaluate against, e.g., iptc_exif_mapping, custom_field_mapping or single_attachment_mapping
	 *
	 * @uses	array 	self::$parent_rules
	 *
	 * @return	array	updated mapping rules
	 */
	private static function _filter_repeater_rules( $settings, $category ) {
		// Remove explicit subfield rules, extract repeater rules
		$repeater_rules = array();
		$no_changes = true;
		foreach( self::$parent_rules as $slug => $rule ) {
			if ( 'subfield' === $rule['parent_action'] ) {
				MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::_filter_repeater_rules( {$slug} ) removing subfield rule", self::MLA_DEBUG_CATEGORY );
				unset( $settings[ $slug ] );
				$no_changes = false;
			} elseif ( 'repeater' === $rule['parent_action'] ) {
				MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::_filter_repeater_rules( {$slug} ) caching repeater rule", self::MLA_DEBUG_CATEGORY );
				$repeater_rules[ $slug ] = $rule;
				unset( $settings[ $slug ] );
				$no_changes = false;
			}
		} // foreach rule
		MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::_filter_repeater_rules( {$category} ) repeater_rules = " . var_export( $repeater_rules, true ), self::MLA_DEBUG_CATEGORY );

		// If nothing changed, NULL leaves the original rules intact
		if ( $no_changes ) {
			return NULL;
		}
		
		if ( empty( $repeater_rules ) ) {
			self::_load_parent_rules( $settings );
			return $settings;
		}

		// Convert repeater rules to a complete set of subfield rules
		if ( 'iptc_exif_mapping' === $category ) {
			$all_settings = MLACore::mla_get_option( 'iptc_exif_mapping' );
			self::_load_parent_rules( $all_settings['custom'] );
		} else {
			self::_load_parent_rules( MLACore::mla_get_option( 'custom_field_mapping' ) );
		}

		foreach( $repeater_rules as $slug => $rule ) {
			foreach( self::$parent_rules as $parent_slug => $parent_rule ) {
				if ( 'subfield' === $parent_rule['parent_action'] && $parent_rule['repeater_field'] === $rule['parent_field'] ) {
					$settings[ $parent_slug ] = $parent_rule;
				}
			}
		} // foreach parent_rule

	// Reload parent_rules, reflecting our changes
	self::_load_parent_rules( $settings );
	return $settings;
	} // _filter_repeater_rules

	/**
	 * MLA Mapping Settings Filter
	 *
	 * This filter is called before any mapping rules are executed.
	 * You can add, change or delete rules from the array.
	 *
	 * @since 1.00
	 *
	 * @param	array 	mapping rules
	 * @param	integer post ID to be evaluated
	 * @param	string 	category/scope to evaluate against, e.g., iptc_exif_mapping, custom_field_mapping or single_attachment_mapping
	 * @param	array 	attachment_metadata, default NULL
	 *
	 * @return	array	updated mapping rules
	 */
	public static function mla_mapping_settings( $settings, $post_id, $category, $attachment_metadata ) {
		static $current_category = '', $filtered_settings = NULL;

		if ( $current_category !== $category ) {
			//MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::mla_mapping_settings( {$post_id}, {$category} ) settings = " . var_export( $settings, true ), self::MLA_DEBUG_CATEGORY );
			$current_category = $category;
			$filtered_settings = NULL;

			// Prepare to cache updates for wplr processing
			self::$parent_updates = array();

			// Install our filters only if one or more rules contain the "parent:" prefix
			if ( 'iptc_exif_mapping' === $category ) {
				self::_load_parent_rules( $settings['custom'] );
			} else {
				self::_load_parent_rules( $settings );
			}

			if ( !empty( self::$parent_rules ) ) {
				// Modify settings if repeater and/or subfield rules are included
				if ( 'iptc_exif_mapping' === $category ) {
					$filtered_settings = self::_filter_repeater_rules( $settings['custom'], $category );
				} else {
					$filtered_settings = self::_filter_repeater_rules( $settings, $category );
				}
				MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::mla_mapping_settings( {$category} ) filtered_settings = " . var_export( $filtered_settings, true ), self::MLA_DEBUG_CATEGORY );

				//add_filter( 'mla_mapping_custom_value', 'MLAParentCustomFieldMapping::mla_mapping_custom_value', 10, 5 );
				add_filter( 'mla_mapping_old_custom_value', 'MLAParentCustomFieldMapping::mla_mapping_old_custom_value', 10, 5 );
				add_filter( 'mla_mapping_updates', 'MLAParentCustomFieldMapping::mla_mapping_updates', 10, 5 );

				if ( self::$acf_active ) {
					//add_filter( 'acf/update_field', 'MLAParentCustomFieldMapping::acf_update_field', 10, 1 );
					add_filter( 'acf/update_value', 'MLAParentCustomFieldMapping::acf_update_value', 10, 3 );
				}

				if ( self::$wplr_active ) {
					// Use priority 11 to run our filters after wplr runs theirs
					//add_action( 'wplr_add_media', 'MLAParentCustomFieldMapping::wplr_add_media', 11, 1 );
					add_action( 'wplr_add_media_to_collection', 'MLAParentCustomFieldMapping::wplr_add_media_to_collection', 11, 2 );
					//add_action( 'wplr_sync_media', 'MLAParentCustomFieldMapping::wplr_sync_media', 11, 1 );
				}
			} else {
				// Remove filters from a possible previous mapping run
				//remove_filter( 'mla_mapping_custom_value', 'MLAParentCustomFieldMapping::mla_mapping_custom_value', 10 );
				remove_filter( 'mla_mapping_old_custom_value', 'MLAParentCustomFieldMapping::mla_mapping_old_custom_value', 10 );
				remove_filter( 'mla_mapping_updates', 'MLAParentCustomFieldMapping::mla_mapping_updates', 10 );

				if ( self::$acf_active ) {
					//remove_filter( 'acf/update_field', 'MLAParentCustomFieldMapping::acf_update_field', 10 );
					remove_filter( 'acf/update_value', 'MLAParentCustomFieldMapping::acf_update_value', 10 );
				}

				if ( self::$wplr_active ) {
					//remove_action( 'wplr_add_media', 'MLAParentCustomFieldMapping::wplr_add_media', 11 );
					remove_action( 'wplr_add_media_to_collection', 'MLAParentCustomFieldMapping::wplr_add_media_to_collection', 11 );
					//remove_action( 'wplr_sync_media', 'MLAParentCustomFieldMapping::wplr_sync_media', 11 );
				}
			}

			MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::mla_mapping_settings( {$category} ) parent_rules = " . var_export( self::$parent_rules, true ), self::MLA_DEBUG_CATEGORY );
		}

		// Find and cache the post_parent for use in the rule filters
		$item = get_post( $post_id );
		self::$post_parent = $item->post_parent;

		if ( !empty( $filtered_settings ) ) {
			if ( 'iptc_exif_mapping' === $category ) {
				$settings['custom'] = $filtered_settings;
			} else {
				$settings = $filtered_settings;
			}
		}

		//MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::mla_mapping_settings( {$post_id}, {$category} ) settings = " . var_export( $settings, true ), self::MLA_DEBUG_CATEGORY );
		return $settings;
	} // mla_mapping_settings_filter

	/**
	 * MLA Mapping New Value Filter
	 *
	 * This filter is called once for each Custom Field mapping rule, after the Data Source 
	 * portion of the rule is evaluated. You can change the new value produced by the rule.
	 *
	 * @since 1.00
	 *
	 * @param	mixed 	data source value returned by the rule
	 * @param	array 	rule slug
	 * @param	integer post ID to be evaluated
	 * @param	string 	category/scope to evaluate against: custom_field_mapping or single_attachment_mapping
	 * @param	array 	attachment_metadata, default NULL
	 *
	 * @return	array	updated rule data source value
	 */
	public static function mla_mapping_custom_value( $new_value, $setting_key, $post_id, $category, $attachment_metadata ) {
		if ( 0 === strpos( $setting_key, 'parent:' ) ) {
			MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::mla_mapping_custom_value( {$setting_key}, {$post_id}, {$category} ) new_value = " . var_export( $new_value, true ), self::MLA_DEBUG_CATEGORY );
		}

		return $new_value;
	} // mla_mapping_custom_value

	/**
	 * MLA Mapping Old Value Filter
	 *
	 * This filter is called once for each Custom Field mapping rule, after the "old text" 
	 * portion of the rule is evaluated. You can change the old value produced by the rule.
	 *
	 * @since 1.00
	 *
	 * @param	mixed 	current target value returned by the rule
	 * @param	array 	rule slug
	 * @param	integer post ID to be evaluated
	 * @param	string 	category/scope to evaluate against: custom_field_mapping or single_attachment_mapping
	 * @param	array 	attachment_metadata, default NULL
	 *
	 * @return	array	updated current target value
	 */
	public static function mla_mapping_old_custom_value( $old_value, $setting_key, $post_id, $category, $attachment_metadata ) {
		if ( 0 === strpos( $setting_key, 'parent:' ) ) {
			$old_value = '';
			if ( self::$post_parent ) {
				$rule = !empty( self::$parent_rules[ $setting_key ] ) ? self::$parent_rules[ $setting_key ] : NULL;
				if ( !empty( $rule['parent_field'] ) ) {
					if ( 'subfield' === $rule[ 'parent_action' ] ) {
						// There is no sensible way to determine the existing value
					} elseif ( 'repeater' === $rule[ 'parent_action' ] ) {
						if ( self::$acf_active ) {
							$old_value = get_field( $rule['parent_field'], self::$post_parent );
						}
					} elseif ( is_string( $old_value = get_metadata( 'post', self::$post_parent, $rule['parent_field'], true ) ) ) {
						$old_value = trim( $old_value );
					}
				}

				MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::mla_mapping_old_custom_value( {$setting_key}, {$post_id}, {$category} ) parent old_value = " . var_export( $old_value, true ), self::MLA_DEBUG_CATEGORY );
			}
		}

		return $old_value;
	} // mla_mapping_old_custom_value

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
		$results = '';

		if ( !empty( $updates['custom_updates'] ) ) {
			$parent_updates = array();
			foreach( $updates['custom_updates'] as $name => $value ) {
				if ( array_key_exists( $name, self::$parent_rules ) ) {
					//MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::mla_mapping_updates( {$post_id}, {$category}, {$name} ) value = " . var_export( $value, true ), self::MLA_DEBUG_CATEGORY );
					unset( $updates['custom_updates'][ $name ] );
					$parent_updates[ self::$parent_rules[ $name ]['parent_field'] ] = array (
						'value' => $value,
						'option' => self::$parent_rules[ $name ]['option'],
						'keep_existing' => self::$parent_rules[ $name ]['keep_existing'],
						'no_null' => self::$parent_rules[ $name ]['no_null'],
						'parent_action' => self::$parent_rules[ $name ]['parent_action'],
						'repeater_field' => self::$parent_rules[ $name ]['repeater_field'],
					);
				}
			} // foreach

			if ( !empty( $parent_updates ) ) {
				$post_parent = self::$post_parent;
				MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::mla_mapping_updates( {$post_id}, {$category}, {$post_parent} ) parent_updates = " . var_export( $parent_updates, true ), self::MLA_DEBUG_CATEGORY );
				if ( $post_parent ) {
					$results = self::_update_parent_postmeta( $post_parent, $parent_updates );
				} else {
					// Cache updates for later wplr processing
					self::$parent_updates[ $post_id ] = $parent_updates;
				}
			}
		} // !empty

		if ( !empty( $results ) ) {
			// Pass the results to MLAData::mla_update_item_postmeta()
			$updates['custom_updates'][0x80000000] = $results;
		}

		if ( empty( $updates['custom_updates'] ) ) {
			unset( $updates['custom_updates'] );
		}

		MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::mla_mapping_updates( {$post_id}, {$category} ) updates = " . var_export( $updates, true ), self::MLA_DEBUG_CATEGORY );
		return $updates;
	} // mla_mapping_updates

	/**
	 * Purge Custom Field Values Filter
	 *
	 * Custom processing for rules with "parent:" prefix.
	 *
	 * @since 1.00
	 *
	 * @param	string|NULL	Text message describing the results of applying the purge. Default NULL.
	 * @param	string		Category; 'custom_field_mapping' or 'iptc_exif_custom_mapping'
	 * @param	string		Name/slug of the rule
	 * @param	array		Rule parameters
	 *
	 * @return	string|NULL	Updated text message describing the results of applying the purge
	 */
	public static function mla_purge_custom_field_values( $results, $category, $rule_name, $rule ) {
		global $wpdb;
//error_log( __LINE__ . " MLAParentCustomFieldMapping::mla_purge_custom_field_values( {$category}, {$rule_name} ) rule = " . var_export( $rule, true ), 0 );

		if ( 0 === strpos( $rule['name'], 'parent:' ) ) {
			$post_types = "'post','page'";

			// Parse the rule to find format and arguments
			self::_load_parent_rules( array( $rule_name => $rule ) );
			$rule = self::$parent_rules[ $rule_name ];
//error_log( __LINE__ . " MLAParentCustomFieldMapping::mla_purge_custom_field_values( {$category}, {$rule_name} ) parsed rule = " . var_export( $rule, true ), 0 );
			//MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::mla_purge_custom_field_values( {$category}, {$rule_name} ) parsed rule = " . var_export( $rule, true ), self::MLA_DEBUG_CATEGORY );

			// Handle ACF Repeater Field and Sub Fields
			if ( 'acf' === $rule['parent_format'] ) {
				if ( 'repeater' === $rule['parent_action'] ) {
					if ( !self::$acf_active ) {
						return 'ACF Repeater purge is not supported; ACF is not active.';
					}
					
					$WP_Query_args = array(
						'posts_per_page' => -1,
						'post_type' => explode( ',', $post_types ),
						'meta_key' => $rule['parent_field'],
						'ignore_sticky_posts' => true,
						'no_found_rows' => true,
						'fields' => 'ids'
					);
					$get_posts = new WP_Query;
					$post_ids = $get_posts->query( $WP_Query_args );

					$field = acf_maybe_get_field( $rule['parent_field'], false, false );
//error_log( __LINE__ . " MLAParentCustomFieldMapping::mla_purge_custom_field_values( {$category}, {$rule_name} ) field = " . var_export( $field, true ), 0 );
					$field = $field['key'];
//error_log( __LINE__ . " MLAParentCustomFieldMapping::mla_purge_custom_field_values( {$category}, {$rule_name} ) field = " . var_export( $field, true ), 0 );

					$failures = 0;
					$successes = 0;
					foreach( $post_ids as $id ) {
//error_log( __LINE__ . " MLAParentCustomFieldMapping::mla_purge_custom_field_values( {$category}, {$rule_name} ) deleting from {$id}", 0 );
						if ( delete_field( $field, $id ) ) {
							$successes++;
						} else {
							$failures++;
						}
					}
					$count = count( $post_ids );
					$count_text = sprintf( _n( '%s Repeater Field value', '%s Repeater Field values', $count, 'MLAParentCustomFieldMapping' ), $count );
					return sprintf( 'Found %1$s in %2$s items. Deleted %3$d; there were %4$d failures.<br />', $count_text, $post_types, $successes, $failures );
				} elseif ( 'subfield' === $rule['parent_action'] ) {
					return 'ACF Sub Field purge is not supported.';
				}
			}
			
			$post_meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->postmeta} LEFT JOIN {$wpdb->posts} ON ( {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ) WHERE {$wpdb->postmeta}.meta_key = '%s' AND {$wpdb->posts}.post_type IN ( " . $post_types . " )", $rule['parent_field'] ) );
//error_log( __LINE__ . " MLAParentCustomFieldMapping::mla_purge_custom_field_values( {$category}, {$rule_name} ) post_meta_ids = " . var_export( $post_meta_ids, true ), 0 );
			foreach ( $post_meta_ids as $mid ) {
				delete_metadata_by_mid( 'post', $mid );
			}

			$count = count( $post_meta_ids );
			if ( $count ) {
				$count_text = sprintf( _n( '%s custom field value', '%s custom field values', $count, 'MLAParentCustomFieldMapping' ), $count ) . ' from ' . $post_types . ' items';

				return sprintf( 'Deleted %1$s.<br />', $count_text );
			}

			return sprintf( 'No %1$s items contained this custom field.<br />', $post_types );
		}

		// Return processing result or NULL (the default) to allow default processing
		return $results;
	} // mla_purge_custom_field_values_filter

	/**
	 * Fetch and filter meta data for an attachment's parent
	 * Adapted from mla_fetch_attachment_metadata() in /includes/class-mla-data-query.php
	 * 
	 * Returns a filtered array of a post's meta data. Internal values beginning with '_'
	 * are stripped out or converted to an 'mla_' equivalent. 
	 *
	 * @since 0.1
	 *
	 * @param	int		post ID of attachment's parent
	 *
	 * @return	array	Meta data variables
	 */
	private static function _fetch_parent_metadata( $parent_id ) {
/*		static $save_id = -1, $results;

		if ( $save_id == $parent_id ) {
			return $results;
		} elseif ( $parent_id == -1 ) {
			$save_id = -1;
			return NULL;
		} // results are cached in WordPress */

		$results = array();
		$post_meta = get_metadata( 'post', $parent_id );

		if ( is_array( $post_meta ) ) {
			foreach ( $post_meta as $post_meta_key => $post_meta_value ) {
				if ( empty( $post_meta_key ) ) {
					continue;
				}

				/*
				 * At this point, every value is an array; one element per instance of the key.
				 * We'll test anyway, just to be sure, then convert single-instance values to a scalar.
				 * Metadata array values are serialized for storage in the database.
				 */
				if ( is_array( $post_meta_value ) ) {
					if ( count( $post_meta_value ) == 1 ) {
						$post_meta_value = maybe_unserialize( $post_meta_value[0] );
					} else {
						foreach ( $post_meta_value as $single_key => $single_value ) {
							$post_meta_value[ $single_key ] = maybe_unserialize( $single_value );
						}
					}
				}

				$results[ $post_meta_key ] = $post_meta_value;
			} // foreach $post_meta
		} // is_array($post_meta)

		$save_id = $parent_id;
		return $results;
	}

	/**
	 * Update parent's Repeater field data for a single attachment.
	 * 
	 * @since 1.04
	 * 
	 * @param	integer	$parent_id The ID of the attachment whose parent's data are to be updated
	 * @param	array	$subfield_updates ( 
	 *					repeater_field => array( meta_key => new_meta, )
	 *					)
	 *
	 * @return	string	Updates made, if any, else empty string.
	 */
	private static function _apply_subfield_updates( $parent_id, $subfield_updates ) {
		// MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::_apply_subfield_updates( {$parent_id} ) subfield_updates = " . var_export( $subfield_updates, true ), self::MLA_DEBUG_CATEGORY );
		if ( !self::$acf_active ) {
			return 'ACF subfield updates not supported; ACF is not active.';
		}
					
		$message = '';
		foreach ( $subfield_updates as $repeater_field => $updates ) {
			$field_object = acf_get_field( $repeater_field );
			MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::_apply_subfield_updates( {$repeater_field}, {$parent_id} ) field_object = " . var_export( $field_object, true ), self::MLA_DEBUG_CATEGORY );

			// Field may not be defined for the parent's post_type
			if ( empty( $field_object ) ) {
				continue;
			}
			
			// Make value acceptable to foreach
			$field_values = get_field( $repeater_field, $parent_id );
			if ( NULL === $field_values ) {
				$field_values = array();
			}
			MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::_apply_subfield_updates( {$repeater_field}, {$parent_id} ) field_value = " . var_export( $field_values, true ), self::MLA_DEBUG_CATEGORY );
			
			// Assemble a complete, empty row for the repeater
			$new_row = array();
			foreach ( $field_object['sub_fields'] as $index => $subfield_object ) {
				$new_row[ $subfield_object['_name'] ] = '';
			} // foreach $sub_fields

			// Fill in the values we have			
			foreach ( $updates as $name => $update ) {
				$new_row[ $name ] = $update['value'];
			} // foreach $updates
			// MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::_apply_subfield_updates( {$repeater_field}, {$parent_id} ) new_row = " . var_export( $new_row, true ), self::MLA_DEBUG_CATEGORY );
			
			// See if this row is already in the repeater field
			$is_new = true;
			foreach ( $field_values as $index => $row_value ) {
				// MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::_apply_subfield_updates( {$repeater_field}, {$parent_id} ) testing row_value[ {$index} ] = " . var_export( $row_value, true ), self::MLA_DEBUG_CATEGORY );
				if ( $row_value == $new_row ) {
					// MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::_apply_subfield_updates( {$repeater_field}, {$parent_id} ) matched to row_value[ {$index} ] = " . var_export( $row_value, true ), self::MLA_DEBUG_CATEGORY );
					$is_new = false;
					break;
				}
			} // foreach value
			
			if ( $is_new ) {
				$message .= sprintf( __( 'Adding %1$s = %2$s', 'media-library-assistant' ) . '<br>', $repeater_field, var_export( $new_row, true ) );
				$row_count = add_row( $repeater_field, $new_row, $parent_id );
				if ( false === $row_count ) {
					$message .= sprintf( __( 'Adding %1$s = %2$s', 'media-library-assistant' ) . '<br>', $repeater_field, 'add_row() FAILED' );
				}
				$field_values = get_field( $repeater_field, $parent_id );
				// MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::_apply_subfield_updates( {$repeater_field}, {$parent_id} ) field_value = " . var_export( $field_values, true ), self::MLA_DEBUG_CATEGORY );
			} else {
				$message .= sprintf( __( 'Duplicate %1$s = %2$s', 'media-library-assistant' ) . '<br>', $repeater_field, var_export( $new_row, true ) );
			} // add new row
		} // foreach repeater updates
		
		return $message;
	} // _apply_subfield_updates

	/**
	 * Update parent's custom field data for a single attachment.
	 * Adapted from mla_update_item_postmeta() in /includes/class-mla-data.php
	 * 
	 * @since 1.00
	 * 
	 * @param	integer	$parent_id The ID of the attachment whose parent's data are to be updated
	 * @param	array	$new_meta ( meta_key => array(
	 *						'value' => $value,
	 *						'option' => $rule['option'],
	 *						'keep_existing' => $rule['keep_existing'],
	 *						'no_null' => $rule['no_null'],
	 *						'parent_action' => $rule['parent_action'],
	 *						'repeater_field' => $rule['repeater_field'],
	 *						)
	 *					)
	 *
	 * @return	string	Updates made, if any, else empty string.
	 */
	private static function _update_parent_postmeta( $parent_id, $new_meta ) {
		$post_data = self::_fetch_parent_metadata( $parent_id );
		$message = '';
		MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::_update_parent_postmeta( {$parent_id} ) post_data = " . var_export( $post_data, true ), self::MLA_DEBUG_CATEGORY );
		MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::_update_parent_postmeta( {$parent_id} ) new_meta = " . var_export( $new_meta, true ), self::MLA_DEBUG_CATEGORY );

		$subfield_updates = array();
		foreach ( $new_meta as $meta_key => $new_value ) {
			// Accumulate all subfield updates for later, consolidated processing
			if ( 'subfield' === $new_value['parent_action'] ) {
				$subfield_updates[ $new_value['repeater_field'] ][ $meta_key ] = $new_value;
				continue;
			}
			
			$meta_value = $new_value['value'];
			$multi_key = 'multi' === $new_value['option'];
			$keep_existing = (boolean) $new_value['keep_existing'];
			$no_null = (boolean) $new_value['no_null'];

			if ( isset( $post_data[ $meta_key ] ) ) {
				$old_meta_value = $post_data[ $meta_key ];

				if ( $multi_key && $no_null ) {
					if ( is_string( $old_meta_value ) ) {
						$old_meta_value = trim( $old_meta_value );
					}

					$delete = empty( $old_meta_value );
				} else {
					$delete = NULL === $meta_value;
				}

				if ( $delete) {
					if ( delete_post_meta( $parent_id, $meta_key ) ) {
						/* translators: 1: meta_key */
						$message .= sprintf( __( 'Deleting %1$s', 'media-library-assistant' ) . '<br>', $meta_key );
					}

					continue;
				}
			} else {
				if ( NULL !== $meta_value ) {
					if ( $multi_key ) {
						foreach ( $meta_value as $new_value ) {
							if ( add_post_meta( $parent_id, $meta_key, $new_value ) ) {
								/* translators: 1: meta_key 2: new_value */
								$message .= sprintf( __( 'Adding %1$s = %2$s', 'media-library-assistant' ) . '<br>', $meta_key, '[' . $new_value . ']' );
							}
						}
					} else {
						if ( add_post_meta( $parent_id, $meta_key, $meta_value ) ) {
							if ( is_array( $meta_value ) ) {
								$new_text = var_export( $meta_value, true );
							} else {
								$new_text = $meta_value;
							}

							/* translators: 1: meta_key 2: meta_value */
							$message .= sprintf( __( 'Adding %1$s = %2$s', 'media-library-assistant' ) . '<br>', $meta_key, $new_text );
						}
					}
				}

				continue; // no change or message if old and new are both NULL
			} // no old value

			$old_text = ( is_array( $old_meta_value ) ) ? var_export( $old_meta_value, true ) : $old_meta_value;

			// Multi-key change from existing values to new values
			if ( $multi_key ) {
				// Test for "no changes"
				if ( $meta_value == (array) $old_meta_value ) {
					continue;
				}

				if ( ! $keep_existing ) {
					if ( delete_post_meta( $parent_id, $meta_key ) ) {
						/* translators: 1: meta_key */
						$message .= sprintf( __( 'Deleting old %1$s values', 'media-library-assistant' ) . '<br>', $meta_key );
					}

					$old_meta_value = array();
				} elseif ( $old_text == $old_meta_value ) { // single value
					$old_meta_value = array( $old_meta_value );
				}

				$updated = 0;
				foreach ( $meta_value as $new_value ) {
					if ( ! in_array( $new_value, $old_meta_value ) ) {
						add_post_meta( $parent_id, $meta_key, $new_value );
						$old_meta_value[] = $new_value; // prevent duplicates
						$updated++;
					}
				}

				if ( $updated ) {
					$meta_value = get_post_meta( $parent_id, $meta_key );
					if ( is_array( $meta_value ) ) {
						if ( 1 == count( $meta_value ) ) {
							$new_text = $meta_value[0];
						} else {
							$new_text = var_export( $meta_value, true );
						}
					} else {
						$new_text = $meta_value;
					}

					/* translators: 1: meta_key 2: old_value 3: new_value 4: update count*/
					$message .= sprintf( __( 'Changing %1$s from "%2$s" to "%3$s"; %4$d updates', 'media-library-assistant' ) . '<br>', 'meta:' . $meta_key, $old_text, $new_text, $updated );
				}
			} elseif ( $old_meta_value !== $meta_value ) {
				$new_text = ( is_array( $meta_value ) ) ? var_export( $meta_value, true ) : $meta_value;

				if ( $keep_existing ) {
					/* translators: 1: element name 2: old_value 3: new_value */
					$message .= sprintf( __( 'For %1$s retaining "%2$s", discarding "%3$s"', 'media-library-assistant' ) . '<br>', 'meta:' . $meta_key, $old_text, $new_text );
					continue;
				}
					
				if ( is_array( $old_meta_value ) ) {
					delete_post_meta( $parent_id, $meta_key );
				}

				if ( update_post_meta( $parent_id, $meta_key, $meta_value ) ) {
					/* translators: 1: element name 2: old_value 3: new_value */
					$message .= sprintf( __( 'Changing %1$s from "%2$s" to "%3$s"', 'media-library-assistant' ) . '<br>', 'meta:' . $meta_key, $old_text, $new_text );
				}
			}
		} // foreach $new_meta

		// Apply all subfield updates
		if ( !empty( $subfield_updates ) ) {
			$message .= self::_apply_subfield_updates( $parent_id, $subfield_updates );
		}
			
		MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::_update_parent_postmeta( {$parent_id} ) message = " . var_export( $message, true ), self::MLA_DEBUG_CATEGORY );
		return $message;
	} // _update_parent_postmeta

	/**
	 * Used to modify the ACF $field settings array before it is saved into the database
	 * 
	 * @since 1.01
	 * 
	 * @param	array	$field The field array containing all settings
	 *
	 * @return	array	updated $field settings
	 */
	public static function acf_update_field( $field ) {
		MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::acf_update_field field = " . var_export( $field, true ), self::MLA_DEBUG_CATEGORY );
		return $field;
	} // acf_update_field

	/**
	 * Used to modify the field’s $value before it is saved into the database
	 * 
	 * @since 1.01
	 * 
	 * @param	mixed	$value The field value
	 * @param	int|string $post_id The post ID where the value is saved
	 * @param	array	$field  The field array containing all settings
	 *
	 * @return	array	updated field $value
	 */
	public static function acf_update_value( $value, $post_id, $field ) {
		static $parent_fields = array();

		MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::acf_update_value( $post_id} ) value = " . var_export( $value, true ), self::MLA_DEBUG_CATEGORY );
		MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::acf_update_value( $post_id} ) field = " . var_export( $field, true ), self::MLA_DEBUG_CATEGORY );

		// Initialize the parent rules once per page load
		if ( NULL === self::$parent_rules ) {
			self::$parent_rules = array();

			if ( 'checked' === MLACore::mla_get_option( MLACoreOptions::MLA_ALLOW_CUSTOM_FIELD_MAPPING ) ) {
				self::_load_parent_rules( MLACore::mla_get_option( 'custom_field_mapping' ), true );
			}

			if ( 'checked' === MLACore::mla_get_option( MLACoreOptions::MLA_ALLOW_IPTC_EXIF_MAPPING ) ) {
				$settings = MLACore::mla_get_option( 'iptc_exif_mapping' );
				self::_load_parent_rules( $settings['custom'], true );
			}

			MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::acf_update_value( $post_id} ) parent_rules = " . var_export( self::$parent_rules, true ), self::MLA_DEBUG_CATEGORY );
			foreach( self::$parent_rules as $slug => $rule ) {
				$parent_fields[ $rule['parent_field'] ] = $slug;
			}

			MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::acf_update_value( $post_id} ) parent_fields = " . var_export( $parent_fields, true ), self::MLA_DEBUG_CATEGORY );
		}

		// Look for special handling of MLA parent: fields
		if ( array_key_exists( $field['_name'], $parent_fields ) ) {
			$rule = self::$parent_rules[ $parent_fields[ $field['_name'] ] ];
			MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::acf_update_value( $post_id} ) rule = " . var_export( $rule, true ), self::MLA_DEBUG_CATEGORY );
			if ( isset( $rule['parent_format'] ) && ( 'acf' === $rule['parent_format'] ) ) {
				$current_value = get_field( $field['key'], $post_id );
				MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::acf_update_value( {$post_id}, {$field['key']} ) current_value = " . var_export( $current_value, true ), self::MLA_DEBUG_CATEGORY );

				switch ( $rule[ 'parent_action' ] ) {
					case 'repeater':
						break;
					case 'subfield':
						break;
					case 'read_only':
						$value = $current_value;
						break;
					case 'default_value':
						if ( $value === $field['default_value'] ) {
							$value = $current_value;
						}
						break;	
					case 'empty':
						if ( empty( $value ) ) {
							$value = $current_value;
						}
						break;	
					default:
				} // switch parent_action
			}
		}

		// return NULL to delete the field		
		return $value;
	} // acf_update_value

	/**
	 * Called after lysync_core.php function sync_media_add() inserts a new attachment
	 * 
	 * @since 1.02
	 * 
	 * @param	integer	$mediaId The ID of the inserted attachment
	 */
	public static function wplr_add_media( $mediaId ) {
		$post_parent = get_post_field( 'post_parent', $mediaId );
		MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::wplr_add_media mediaId = {$mediaId}, post_parent = {$post_parent}", self::MLA_DEBUG_CATEGORY );
	} // wplr_add_media

	/**
	 * Called after lysync_core.php function add_media_to_collection() adds the attachment ID to the Collection
	 * 
	 * @since 1.02
	 * 
	 * @param	integer	$mediaId The ID of the inserted attachment
	 * @param	integer	$collectionId The ID of the wplr Collection
	 */
	public static function wplr_add_media_to_collection( $mediaId, $collectionId ) {
		// lysync_core.php function set_media_to_collection_one() should have set post_parent by now
		$post_parent = get_post_field( 'post_parent', $mediaId );

		if ( $post_parent && !empty( self::$parent_updates[ $mediaId ] ) ) {
			$message = self::_update_parent_postmeta( $post_parent, self::$parent_updates[ $mediaId ] );
			MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::wplr_add_media_to_collection mediaId = {$mediaId}, collectionId = {$collectionId}, post_parent = {$post_parent} message = " . var_export( $message, true ), self::MLA_DEBUG_CATEGORY );
		} else {
			$parent_updates = NULL;
			MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::wplr_add_media_to_collection mediaId = {$mediaId}, collectionId = {$collectionId}, post_parent = {$post_parent} no parent_updates", self::MLA_DEBUG_CATEGORY );
		}
	} // wplr_add_media_to_collection 

	/**
	 * Called after lysync_core.php function sync_media() completes processing the attachment and the Collection
	 * 
	 * @since 1.02
	 * 
	 * @param	object	$sync The lrsync table row for this item
	 */
	public static function wplr_sync_media( $sync ) {
		MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::wplr_sync_media sync = " . var_export( $sync, true ), self::MLA_DEBUG_CATEGORY );
		$info = Meow_WPLR_LRInfo::fromRow( $sync );
		$post_parent = get_post_field( 'post_parent', $sync->wp_id );
		MLACore::mla_debug_add( __LINE__ . " MLAParentCustomFieldMapping::wplr_sync_media post_parent = {$post_parent}, info = " . var_export( $info, true ), self::MLA_DEBUG_CATEGORY );
	} // wplr_sync_media 
} //MLAParentCustomFieldMapping

// Install the filters at an early opportunity
add_action('init', 'MLAParentCustomFieldMapping::initialize');
?>
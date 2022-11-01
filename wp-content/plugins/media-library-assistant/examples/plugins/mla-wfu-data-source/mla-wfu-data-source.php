<?php
/**
 * Enhanced MLA data sources for Windows File Uploads additional data fields
 *
 * Much more information is in the Settings/MLA CSV Data "Documentation" tab.
 *
 * Created for support topic "Custom fields – Checkbox to tags"
 * opened on  6/29/2022  by "johnsteed".
 * https://wordpress.org/support/topic/custom-fields-checkbox-to-tags/
 *
 * Enhanced (updates) for support topic ""
 * opened on  MM/DD/YYYY  by "".
 * https://wordpress.org/support/topic/
 *
 * @package MLA WFU Data Source
 * @version 1.00
 */

/*
Plugin Name: MLA WFU Data Source
Plugin URI: http://davidlingren.com/
Description: Enhanced MLA data sources for Windows File Uploads additional data fields
Author: David Lingren
Version: 1.00
Author URI: http://davidlingren.com/

Copyright 2022 David Lingren

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
 * Class MLA WFU Data Source implements an empty Settings screen
 *
 * Call it anything you want, but give it an unlikely and hopefully unique name. Hiding everything
 * else inside a class means this is the only name you have to worry about.
 *
 * @package MLA WFU Data Source
 * @since 1.00
 */
class MLAWFUDataSource {
	/**
	 * Current version number
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const PLUGIN_VERSION = '1.00';

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'mlawfudatasource';

	/**
	 * Constant to log this plugin's debug activity
	 *
	 * @since 1.00
	 *
	 * @var	integer
	 */
	const MLA_DEBUG_CATEGORY = 0x00008000;

	/**
	 * Settings Management object
	 *
	 * @since 1.02
	 *
	 * @var	array
	 */
	private static $plugin_settings = NULL;

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
	 * Configuration values for the Settings Management object
	 *
	 * @since 1.02
	 *
	 * @var	array
	 */
	private static $settings_arguments = array(
				'slug_prefix' => self::SLUG_PREFIX,
				'plugin_title' => 'MLA WFU Data Source',
				'menu_title' => 'MLA WFU Data',
				'plugin_file_name_only' => 'mla-wfu-data-source',
				'plugin_version' => self::PLUGIN_VERSION,
				'template_file' => '/admin-settings-page.tpl', // Add the path at runtime, in initialize()
				'options' => array(
					// 'slug' => array( 'type' => 'checkbox|text|select|textarea', 'default' => 'text|boolean(0|1)' )
					// See the default values in $_default_settings below
					'conversion_rules' => array( 'type' => 'textarea', 'default' => '' ),
				),
				'general_tab_values' => array(), // additional page_values for 'page-level-options' template
				'documentation_tab_values' => array(
					'plugin_title' => 'MLA WFU Data Source',
					'settingsURL' => '', // Set at runtime in initialize()
				), // page_values for 'documentation-tab' template
			);

	/**
	 * Default processing options
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $_default_settings = array (
						'conversion_rules' => '',
					);

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
		'general' => array( 'title' => 'General', 'render' => array( 'MLAWFUDataSource', '_compose_general_tab' ) ),
		'documentation' => array( 'title' => 'Documentation', 'render' => array( 'MLAWFUDataSource', '_compose_documentation_tab' ) ),
		);

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
		
		// Defined in /media-library-assistant/includes/class-mla-data.php
		add_filter( 'mla_expand_custom_prefix', 'MLAWFUDataSource::mla_expand_custom_prefix', 10, 8 );
		// Defined in /media-library-assistant/includes/class-mla-options.php
		add_filter( 'mla_update_attachment_metadata_prefilter', 'MLAWFUDataSource::mla_update_attachment_metadata_prefilter', 10, 3 );

		// The remaining filters are only useful in the admin section; exit if in the "front-end" posts/pages. 
		if ( ! is_admin() ) {
			return;
		}

		// Load template array and initialize page-level values.
		self::$page_template_array = MLACore::mla_load_template( self::$settings_arguments['template_file'], 'path' );

		// Add the run-time values to the settings
		// $general_tab_values = self::$plugin_settings->get_plugin_argument('general_tab_values');
		// $general_tab_values['option_one_selected'] = 'one' === self::$plugin_settings->get_plugin_option('static_select_slug') ? 'selected=selected' : '';
		// self::$plugin_settings->update_plugin_argument('general_tab_values', $general_tab_values );
	} // initialize

	/**
	 * Internal form of Conversion Rules
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $_conversion_rules = NULL;

	/**
	 * Parse Conversion Rules, building self::$_conversion_rules
	 *
	 * @since 1.00
	 *
	 * @return	array Error messages, if any, or empty for success.
	 */
	private static function _load_conversion_rules() {
		$messages = array ();

		if ( NULL !== self::$_conversion_rules ) {
			return $messages;
		}

		$conversion_rules = self::$plugin_settings->get_plugin_option('conversion_rules');
		MLACore::mla_debug_add( __LINE__ . " MLAWFUDataSource::_load_conversion_rules() \$conversion_rules = " . var_export( $conversion_rules, true ), self::MLA_DEBUG_CATEGORY );
		$conversion_rules = explode( "\n", str_replace( "\r", "\n", str_replace( "\r\n", "\n", $conversion_rules ) ) );
		//MLACore::mla_debug_add( __LINE__ . " MLAWFUDataSource::_load_conversion_rules() \$conversion_rules = " . var_export( $conversion_rules, true ), self::MLA_DEBUG_CATEGORY );

		foreach ( $conversion_rules as $conversion_rule ) {
			$rule = MLAData::mla_parse_substitution_parameter( $conversion_rule );
			//MLACore::mla_debug_add( __LINE__ . " MLAWFUDataSource::_load_conversion_rules( {$conversion_rule} ) \$rule = " . var_export( $rule, true ), self::MLA_DEBUG_CATEGORY );

			$arguments = array();
			if ( !empty( trim( $rule['value'] ) ) ) {
				$data_source = trim( $rule['value'] );
			} else {
				$messages[] = "Invalid or missing data source name in '{$rule}'";
				continue;
			}

			if ( !empty( trim( $rule['format'] ) ) ) {
				$arguments['rule_type'] = trim( $rule['format'] );
			} else {
				$messages[] = "Missing rule type in '{$rule}'";
				continue;
			}

			switch ( $rule['format'] ) {
				case 'boolean':
					if ( isset( $rule['args'][0] ) && !empty( trim( $rule['args'][0] ) ) ) {
						$arguments['wfu_field'] = trim( $rule['args'][0] );
					} else {
						$messages[] = "Invalid or missing WFU Field Name in '{$rule}'";
						break;
					}

					if ( isset( $rule['args'][1] ) && !empty( trim( $rule['args'][1] ) ) ) {
						$arguments['true_value'] = trim( $rule['args'][1] );
					} else {
						$arguments['true_value'] = ' ';
					}

					if ( isset( $rule['args'][2] ) && !empty( trim( $rule['args'][2] ) ) ) {
						$arguments['false_value'] = trim( $rule['args'][2] );
					} else {
						$arguments['false_value'] = ' ';
					}

					self::$_conversion_rules[ $data_source ] = $arguments;
					break;
				case 'element':
					$element = array();
					if ( isset( $rule['args'][0] ) && !empty( trim( $rule['args'][0] ) ) ) {
						$element['wfu_field'] = trim( $rule['args'][0] );
					} else {
						$messages[] = "Invalid or missing WFU Field Name in '{$rule}'";
						break;
					}

					if ( isset( $rule['args'][1] ) && !empty( trim( $rule['args'][1] ) ) ) {
						$element['true_value'] = trim( $rule['args'][1] );
					} else {
						$element['true_value'] = ' ';
					}

					if ( isset( $rule['args'][2] ) && !empty( trim( $rule['args'][2] ) ) ) {
						$element['false_value'] = trim( $rule['args'][2] );
					} else {
						$element['false_value'] = ' ';
					}
					
					if ( isset( self::$_conversion_rules[ $data_source ] ) ) {
						$arguments = self::$_conversion_rules[ $data_source ];
					} else {
						$arguments = array( 'rule_type' => 'list', 'elements' => array(), 'delimiter' => ',' );
					}
					
					$arguments['elements'][] = $element;
					self::$_conversion_rules[ $data_source ] = $arguments;
					break;
				case 'list':
					if ( isset( self::$_conversion_rules[ $data_source ] ) ) {
						$arguments = self::$_conversion_rules[ $data_source ];
					} else {
						$arguments = array( 'rule_type' => 'list', 'elements' => array(), 'delimiter' => ',' );
					}
					
					if ( isset( $rule['args'][0] ) && !empty( trim( $rule['args'][0] ) ) ) {
						$arguments['delimiter'] = trim( $rule['args'][0] );
					}

					self::$_conversion_rules[ $data_source ] = $arguments;
					break;
				case 'equal':
					$element = array();
					if ( isset( $rule['args'][0] ) && !empty( trim( $rule['args'][0] ) ) ) {
						$element['wfu_field'] = trim( $rule['args'][0] );
					} else {
						$messages[] = "Invalid or missing WFU Field Name in '{$rule}'";
						break;
					}

					if ( isset( $rule['args'][1] ) && !empty( trim( $rule['args'][1] ) ) ) {
						$element['specified_value'] = trim( $rule['args'][1] );
					} else {
						$element['specified_value'] = '';
					}

					if ( isset( $rule['args'][2] ) && !empty( trim( $rule['args'][2] ) ) ) {
						$element['true_value'] = trim( $rule['args'][2] );
					} else {
						$element['true_value'] = ' ';
					}

					if ( isset( $rule['args'][3] ) && !empty( trim( $rule['args'][3] ) ) ) {
						$element['false_value'] = trim( $rule['args'][3] );
					} else {
						$element['false_value'] = ' ';
					}

					if ( isset( self::$_conversion_rules[ $data_source ] ) ) {
						$arguments = self::$_conversion_rules[ $data_source ];
					} else {
						$arguments = array( 'rule_type' => 'equal', 'elements' => array() );
					}
					
					$arguments['elements'][] = $element;
					self::$_conversion_rules[ $data_source ] = $arguments;
					break;
				default:
					$messages[] = "Unknown rule type in '{$rule}'";
			} // $rule['format']
		} // foreach $conversion_rule

		MLACore::mla_debug_add( __LINE__ . " MLAWFUDataSource::_load_conversion_rules() \$_conversion_rules = " . var_export( self::$_conversion_rules, true ), self::MLA_DEBUG_CATEGORY );
		//MLACore::mla_debug_add( __LINE__ . " MLAWFUDataSource::_load_conversion_rules() \$messages = " . var_export( $messages, true ), self::MLA_DEBUG_CATEGORY );
		return $messages;
	}
	

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
		if ( 'wfu' !== strtolower( $value['prefix'] ) ) {
			return $custom_value;
		}

		MLACore::mla_debug_add( __LINE__ . " MLAWFUDataSource::mla_expand_custom_prefix( {$key}, {$post_id}, {$keep_existing}, {$default_option} ) \$value = " . var_export( $value, true ), self::MLA_DEBUG_CATEGORY );
		MLACore::mla_debug_add( __LINE__ . " MLAWFUDataSource::mla_expand_custom_prefix() \$query = " . var_export( $query, true ), self::MLA_DEBUG_CATEGORY );

		$messages = self::_load_conversion_rules();
		if ( !empty( $messages ) ) {
			MLACore::mla_debug_add( __LINE__ . " MLAWFUDataSource::mla_expand_custom_prefix( {$post_id} ) \$messages = " . var_export( $messages, true ), self::MLA_DEBUG_CATEGORY );
		}

		if ( isset( self::$_wfu_user_data_cache[ $post_id ] ) ) {
			$post_meta = self::$_wfu_user_data_cache[ $post_id ];
		} else {
			$post_meta = get_metadata( 'post', $post_id, '_wp_attachment_metadata', true );
			//MLACore::mla_debug_add( __LINE__ . " MLAWFUDataSource::mla_expand_custom_prefix( {$post_id} ) \$post_meta = " . var_export( $post_meta, true ), self::MLA_DEBUG_CATEGORY );
			if ( empty( $post_meta ) ) {
				$post_meta = array();
			}
			
			if ( empty( $post_meta['WFU User Data'] ) ) {
				$post_meta = array();
			} else {
				$post_meta = $post_meta['WFU User Data'];
			}
			
			self::$_wfu_user_data_cache[ $post_id ] = $post_meta;
			MLACore::mla_debug_add( __LINE__ . " MLAWFUDataSource::mla_expand_custom_prefix( {$post_id} ) \$post_meta = " . var_export( $post_meta, true ), self::MLA_DEBUG_CATEGORY );
		}

		// Match against derived fields first, then WFU additional fields
		$data_source = $value['value'];
		if ( array_key_exists( $data_source, self::$_conversion_rules ) ) {
			$rule = self::$_conversion_rules[ $data_source ];

			$wfu_value = '';
			if ( isset( $rule['wfu_field'] ) ) {
				if ( isset( $post_meta[ $rule['wfu_field'] ] ) ) {
					$wfu_value = trim( $post_meta[ $rule['wfu_field'] ] );
					if ( 'false' === strtolower( $wfu_value ) ) {
						$wfu_value = '';
					}
				}
			}

			switch ( $rule['rule_type'] ) {
				case 'boolean':
					if ( empty( $wfu_value ) ) {
						$custom_value = $rule['false_value'];
					} else {
						$custom_value = $rule['true_value'];
					}
					break;
				case 'list':
					$custom_value = array();
					
					foreach ( $rule['elements'] as $element ) {
						$wfu_value = '';
						if ( isset( $element['wfu_field'] ) ) {
							if ( isset( $post_meta[ $element['wfu_field'] ] ) ) {
								$wfu_value = trim( $post_meta[ $element['wfu_field'] ] );
								if ( 'false' === strtolower( $wfu_value ) ) {
									$wfu_value = '';
								}
							}
						}
			
						if ( empty( $wfu_value ) ) {
							$element_value = $element['false_value'];
						} else {
							$element_value = $element['true_value'];
						}
						
						if ( ' ' !== $element_value ) {
							$custom_value[] = $element_value;
						}
					} // foreach $element
					
					if ( 'array' !== $value['option'] ) {
						$custom_value = implode( $rule['delimiter'], $custom_value );
						if ( empty( $custom_value ) ) {
							$custom_value = ' ';
						}
					}
					
					break;
				case 'equal':
					$false_value = '';
					foreach ( $rule['elements'] as $element ) {
						$wfu_value = '';
						
						if ( isset( $element['wfu_field'] ) ) {
							if ( isset( $post_meta[ $element['wfu_field'] ] ) ) {
								$wfu_value = trim( $post_meta[ $element['wfu_field'] ] );
								if ( 'false' === strtolower( $wfu_value ) ) {
									$wfu_value = '';
								}
							}
						}
			
						if ( $wfu_value === $element['specified_value'] ) {
							$custom_value = $element['true_value'];
							break;
						}
						
						if ( empty( $false_value ) && ( ' ' !== $element['false_value'] ) ) {
							$false_value = $element['false_value'];
						}
					}
					
					if ( ( NULL === $custom_value ) && !empty( $false_value ) ) {
						$custom_value = $false_value;
					}
					
					break;
				default:
					$custom_value = NULL;
			} // $rule_type
		}
		
		if ( ( NULL === $custom_value ) && array_key_exists( $data_source, $post_meta ) ) {
			$custom_value = $post_meta[ $data_source ];
		}
		
		MLACore::mla_debug_add( __LINE__ . " MLAWFUDataSource::mla_expand_custom_prefix( {$post_id}, {$key} ) \$custom_value = " . var_export( $custom_value, true ), self::MLA_DEBUG_CATEGORY );
		return $custom_value;
	} // mla_expand_custom_prefix

	/**
	 * Cache of WFU User Data values, required to avoid WP bug during uploads
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $_wfu_user_data_cache = array();

	/**
	 * Capture WFU User Data during file uploads
	 *
	 * @since 1.00
	 *
	 * @param	array	Attachment metadata for just-inserted attachment
	 * @param	integer	ID of just-inserted attachment
	 * @param	array	MLA mapping option settings
	 */

	public static function mla_update_attachment_metadata_prefilter( $data, $post_id, $options ) {
		MLACore::mla_debug_add( __LINE__ . " MLAWFUDataSource::mla_update_attachment_metadata_prefilter( {$post_id} ) \$data = " . var_export( $data, true ), self::MLA_DEBUG_CATEGORY );
		//MLACore::mla_debug_add( __LINE__ . " MLAWFUDataSource::mla_update_attachment_metadata_prefilter( {$post_id} ) \$options = " . var_export( $options, true ), self::MLA_DEBUG_CATEGORY );

		if ( isset( $data['WFU User Data'] ) ) {
			self::$_wfu_user_data_cache[ $post_id ] = $data['WFU User Data'];
		}

		return $data;
	} // mla_update_attachment_metadata_prefilter
} // MLAWFUDataSource

// Install the filters at an early opportunity
add_action('init', 'MLAWFUDataSource::initialize');
?>
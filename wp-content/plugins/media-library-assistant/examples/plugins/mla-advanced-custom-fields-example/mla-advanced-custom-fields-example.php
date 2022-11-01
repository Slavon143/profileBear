<?php
/**
 * Supports an ACF checkbox, "where-used" in an ACF repeater, one or more ACF "image" fields and one or more ACF "select" fields
 *
 * In this example:
 *
 * 	1. an Advanced Custom Fields "checkbox" custom field is added to the
 * 	   Media/Assistant submenu table, Quick Edit and Bulk Edit areas.
 *
 * 	2. an Advanced Custom Fields "repeater" custom field is analyzed to display
 * 	   "where used" information in the Media/Assistant submenu table.
 *
 * 	3. Advanced Custom Fields "image" custom field(s) are analyzed to display
 * 	   "where used" information in the Media/Assistant submenu table.
 *
 * 	4. Advanced Custom Fields "image" custom field(s) are made available as custom data substitution
 *     parameters, using the prefix "acf:", e.g., "acf:search_bar_image". Three format/option values
 *     can be added: 1) "acf:search_bar_image(count)" returns the number of item references,
 *     2) "acf:search_bar_image(present)" returns 1 if there are references present, and 3) a numeric
 *     value, e.g., "acf:search_bar_image(3)" returns the count only if it is equal to or greater than
 *     the number of references.
 *
 *  5. Advanced Custom Fields "select" custom field(s) are made available in the Media/Assistant submenu
 *     table as columns that display the ACF label (Vs the value) ssigned to each choice. You can edit
 *     the fields by changing the label; the plugin will convert this to the corresponding value and
 *     update the field appropriately.
 *
 * You can turn each of the four field types on or off by setting the corresponding "Enable" option on the
 * Settings/MLA AC Fields "General" tab. You can change the field names and titles/labels by editing the 
 * corresponding options.
 *
 * Much more information is in the Settings/MLA AC Fields "Documentation" tab.
 *
 * Created (ACF Checkbox Field) as the "MLA ACF Checkbox Example" plugin for support topic "Bulk edit a custom field value"
 * opened on 12/18/2013 by "saqwild"
 * https://wordpress.org/support/topic/bulk-edit-a-custom-field-value/#post-4428935
 *
 * Enhanced (ACF Repeater Field) for support topic "Advanced Custom Fields repeater"
 * opened on 3/1/2015 by "ncj"
 * https://wordpress.org/support/topic/advanced-custom-fields-repeater/
 *
 * Enhanced (ACF Image Fields) for support topic "finding “where used” in custom field"
 * opened on 4/19/2020 by "maven1129"
 * https://wordpress.org/support/topic/finding-where-used-in-custom-field/
 *
 * Enhanced (ACF Select Fields) for support topic "Bulk edit ACF custom field"
 * opened on 6/15/2021 by "andreatkc"
 * https://wordpress.org/support/topic/bulk-edit-acf-custom-field/
 *
 * @package MLA Advanced Custom Fields Example
 * @version 1.09
 */

/*
Plugin Name: MLA Advanced Custom Fields Example
Plugin URI: http://davidlingren.com/
Description: Supports an ACF checkbox, "where-used" in an ACF repeater, one or more ACF "image" fields and one or more ACF "select" fields.
Author: David Lingren
Version: 1.09
Author URI: http://davidlingren.com/

Copyright 2014 - 2021 David Lingren

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
 * Class MLA Advanced Custom Fields Example hooks some of the filters provided by the MLA_List_Table and MLAData classes
 *
 * Call it anything you want, but give it an unlikely and hopefully unique name. Hiding everything
 * else inside a class means this is the only name you have to worry about.
 *
 * @package MLA Advanced Custom Fields Example
 * @since 1.00
 */
class MLAACFExample {
	/**
	 * Plugin version number for debug logging
	 *
	 * @since 1.01
	 *
	 * @var	integer
	 */
	const PLUGIN_VERSION = '1.09';

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
	const SLUG_PREFIX = 'mlaacfexample';

	/**
	 * Configuration values for the Settings Management object
	 *
	 * @since 1.06
	 *
	 * @var	array
	 */
	private static $settings_arguments = array(
				'slug_prefix' => self::SLUG_PREFIX,
				'plugin_title' => 'MLA Advanced Custom Fields Example',
				'menu_title' => 'MLA AC Fields',
				'plugin_file_name_only' => 'mla-advanced-custom-fields-example',
				'plugin_version' => self::PLUGIN_VERSION,
				'template_file' => '/admin-settings-page.tpl', // Add the path at runtime, in initialize()
				'options' => array( // 'slug' => array( 'type' => 'text|checkbox', 'default' => 'text|boolean' )
					'acf_checkbox_enabled' =>array( 'type' => 'checkbox', 'default' => false ),
					'acf_checkbox_fields' =>array( 'type' => 'text', 'default' => '' ),
					'acf_checkbox_titles' =>array( 'type' => 'text', 'default' => '' ),
					'acf_repeater_enabled' =>array( 'type' => 'checkbox', 'default' => false ),
					'acf_repeater_fields' =>array( 'type' => 'text', 'default' => '' ),
					'acf_repeater_titles' =>array( 'type' => 'text', 'default' => '' ),
					'acf_repeater_subfields' =>array( 'type' => 'text', 'default' => '' ),
					'acf_image_enabled' =>array( 'type' => 'checkbox', 'default' => false ),
					'acf_image_fields' =>array( 'type' => 'text', 'default' => '' ),
					'acf_image_titles' =>array( 'type' => 'text', 'default' => '' ),
					'acf_select_enabled' =>array( 'type' => 'checkbox', 'default' => false ),
					'acf_select_fields' =>array( 'type' => 'text', 'default' => '' ),
					'acf_select_titles' =>array( 'type' => 'text', 'default' => '' ),
				),
				'general_tab_values' => array(), // additional page_values for 'page-level-options' template
				'documentation_tab_values' => array(
					'plugin_title' => 'MLA Advanced Custom Fields Example',
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
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		// This plugin requires MLA
		if ( ! class_exists( 'MLACore', false ) ) {
			return;
		}

		// This plugin requires ACF to be active
		if ( !function_exists('acf_get_field') ) {
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
		MLACore::mla_debug_add( __LINE__ . ' MLAACFExample::initialize plugin_settings = ' . var_export( self::$plugin_settings->get_plugin_option( 'current_settings' ), true ), self::MLA_DEBUG_CATEGORY );

		// Defined in /media-library-assistant/includes/class-mla-data.php
		add_filter( 'mla_expand_custom_prefix', 'MLAACFExample::mla_expand_custom_prefix', 10, 8 );

		// The remaining filters are only useful for the admin section; exit in the front-end posts/pages
		if ( ! is_admin() )
			return;

		// Build a cache of ACF select field "objects" (actually arrays)
		self::_build_select_field_objects();

		// Defined in /media-library-assistant/includes/class-mla-main.php
		add_filter( 'mla_list_table_inline_fields', 'MLAACFExample::mla_list_table_inline_fields', 10, 1 );
		add_filter( 'mla_list_table_inline_action', 'MLAACFExample::mla_list_table_inline_action', 10, 2 );
		add_filter( 'mla_list_table_bulk_action_initial_request', 'MLAACFExample::mla_list_table_bulk_action_initial_request', 10, 3 );
		add_filter( 'mla_list_table_bulk_action', 'MLAACFExample::mla_list_table_bulk_action', 10, 3 );
		add_filter( 'mla_list_table_inline_values', 'MLAACFExample::mla_list_table_inline_values', 10, 1 );

		add_filter( 'mla_list_table_inline_initial_values', 'MLAACFExample::mla_list_table_inline_values', 10, 1 );
		add_filter( 'mla_list_table_inline_blank_values', 'MLAACFExample::mla_list_table_inline_values', 10, 1 );
		add_filter( 'mla_list_table_inline_preset_values', 'MLAACFExample::mla_list_table_inline_values', 10, 1 );

		// Defined in /media-library-assistant/includes/class-mla-list-table.php
		add_filter( 'mla_list_table_get_columns', 'MLAACFExample::mla_list_table_get_columns', 10, 1 );
		add_filter( 'mla_list_table_get_hidden_columns', 'MLAACFExample::mla_list_table_get_hidden_columns', 10, 1 );
		add_filter( 'mla_list_table_get_sortable_columns', 'MLAACFExample::mla_list_table_get_sortable_columns', 10, 1 );
		add_filter( 'mla_list_table_column_default', 'MLAACFExample::mla_list_table_column_default', 10, 3 );
		add_filter( 'mla_list_table_query_final_terms', 'MLAACFExample::mla_list_table_query_final_terms', 10, 1 );

		add_filter( 'mla_list_table_build_inline_data', 'MLAACFExample::mla_list_table_build_inline_data', 10, 2 );
	}

	/**
	 * Process an MLA_List_Table inline action, i.e., Quick Edit 
	 *
	 * This filter gives you an opportunity to pre-process an MLA_List_Table "Quick Edit"
	 * action before the MLA handler.
	 *
	 * @since 1.00
	 *
	 * @param	array	$fields	slug(s) of Quick/Bulk Edit eligible fields.
	 */
	public static function mla_list_table_inline_fields( $fields ) {
		// Add columns of our own for the select information
		if ( self::$plugin_settings->get_plugin_option( 'acf_select_enabled' ) ) {
			foreach( self::$field_objects as $field_name => $field_object ) {
				$fields[] = 'acf_' . $field_name;
			}
		} // acf_select_enabled

		return $fields;
	} // mla_list_table_inline_action

	/**
	 * Convert an ACF select field label to its field value 
	 *
	 * @since 1.06
	 *
	 * @param	string $field_name ACF field name
	 * @param	string $field_label ACF field name (candidate)
	 *
	 * @return	string ACF field value, if valid else empty.
	 */
	private static function _convert_acf_label_to_value( $field_name, $field_label ) {
		if ( isset( self::$field_objects[ $field_name ] ) ) {
			// choices -> array( value => label )
			$choices = self::$field_objects[ $field_name ]['choices'];

			// Multi-select dropdowns are allowed, returning an array of choices
			if ( self::$field_objects[ $field_name ]['multiple'] ) {
				$values = array();				
				foreach ( explode( ',', $field_label ) as $label ) {
					$value = array_search( $label, $choices );

					if ( false !== $value ) {
						$values[] = $value;
					}
				}

				return $values;
			} else {
				$value = array_search( $field_label, $choices );

				if ( false !== $value ) {
					return $value;
				}
			}
		}

		return '';
	} // _convert_acf_label_to_value

	/**
	 * Process an MLA_List_Table inline action, i.e., Quick Edit 
	 *
	 * This filter gives you an opportunity to pre-process an MLA_List_Table "Quick Edit"
	 * action before the MLA handler.
	 *
	 * @since 1.00
	 *
	 * @param	array	$item_content	NULL, to indicate no handler.
	 * @param	integer	$post_id		the affected attachment.
	 *
	 * @return	object	updated $item_content. NULL if no handler, otherwise
	 *					( 'message' => error or status message(s), 'body' => '',
	 *					  'prevent_default' => true to bypass the MLA handler )
	 */
	public static function mla_list_table_inline_action( $item_content, $post_id ) {
		if ( self::$plugin_settings->get_plugin_option( 'acf_checkbox_enabled' ) ) {
			$field = self::$plugin_settings->get_plugin_option( 'acf_checkbox_fields' );
			// Convert the comma-delimited string of "checked" checkbox values back to an ACF-compatible array
			if ( isset( $_REQUEST['custom_updates'] ) && isset( $_REQUEST['custom_updates'][ $field ] ) ) {
				if ( ! empty( $_REQUEST['custom_updates'][ $field ] ) ) {
					$_REQUEST['custom_updates'][ $field ] = explode( ',', $_REQUEST['custom_updates'][ $field ] );
				}
			}
		} // acf_checkbox_enabled

		// Convert select field updates from label to value, move to 'custom_updates'
		if ( self::$plugin_settings->get_plugin_option( 'acf_select_enabled' ) ) {
			$select_updates = array();

			foreach( self::$field_objects as $field_name => $field_object ) {
				$acf_field = 'acf_' . $field_name;
				if ( isset( $_REQUEST[ $acf_field ] ) ) {
					$new_value = self::_convert_acf_label_to_value( $field_name, $_REQUEST[ $acf_field ] );
					
					if ( !empty( $new_value ) ) {
						$select_updates[ $field_name ] = $new_value;
					}

					unset( $_REQUEST[ $acf_field ] );
				}
			}

			if ( !empty( $select_updates ) ) {
				if ( isset( $_REQUEST['custom_updates'] ) ) {
					$_REQUEST['custom_updates'] = array_merge( $_REQUEST['custom_updates'], $select_updates );
				} else {
					$_REQUEST['custom_updates'] = $select_updates;
				}
			}
		} // acf_select_enabled

		if ( isset( $_REQUEST['custom_updates'] ) ) {
			MLACore::mla_debug_add( __LINE__ . ' MLAACFExample::mla_list_table_inline_action custom_updates = ' . var_export( $_REQUEST['custom_updates'], true ), self::MLA_DEBUG_CATEGORY );
		} else {
			MLACore::mla_debug_add( __LINE__ . ' MLAACFExample::mla_list_table_inline_action NO custom_updates', self::MLA_DEBUG_CATEGORY );
		}
		
		return $item_content;
	} // mla_list_table_inline_action

	/**
	 * Pre-filter MLA_List_Table bulk action request parameters
	 *
	 * This filter gives you an opportunity to pre-process the request parameters for a bulk action
	 * before the action begins. DO NOT assume parameters come from the $_REQUEST super array!
	 *
	 * @since 1.01
	 *
	 * @param	array	$request		bulk action request parameters, including ['mla_bulk_action_do_cleanup'].
	 * @param	string	$bulk_action	the requested action.
	 * @param	array	$custom_field_map	[ slug => field_name ]
	 *
	 * @return	array	updated bulk action request parameters
	 */
	public static function mla_list_table_bulk_action_initial_request( $request, $bulk_action, $custom_field_map ) {
		MLACore::mla_debug_add( __LINE__ . ' MLAACFExample::mla_list_table_bulk_action_initial_request request = ' . var_export( $request, true ), self::MLA_DEBUG_CATEGORY );
		MLACore::mla_debug_add( __LINE__ . ' MLAACFExample::mla_list_table_bulk_action_initial_request custom_field_map = ' . var_export( $custom_field_map, true ), self::MLA_DEBUG_CATEGORY );

		if ( self::$plugin_settings->get_plugin_option( 'acf_checkbox_enabled' ) ) {
			/*
			 * If the field is present, save the field value for our own update process and remove it
			 * from the $request array to prevent MLA's default update processing.
			 */
			$acf_checkbox_field = self::$plugin_settings->get_plugin_option( 'acf_checkbox_fields' );
			foreach( $custom_field_map as $slug => $custom_field ) {
				if ( $acf_checkbox_field === $custom_field['name'] ) {
					if ( ! empty( $request[ $slug ] ) ) {
						// Convert the comma-delimited string of "checked" checkbox values back to an ACF-compatible array
						self::$acf_checkbox_value =  explode( ',', trim( $request[ $slug ] ) );
						$request[ $slug ] = '';
					}
					
					break;
				}
			}

			MLACore::mla_debug_add( __LINE__ . ' MLAACFExample::mla_list_table_bulk_action_initial_request self::$acf_checkbox_value = ' . var_export( self::$acf_checkbox_value, true ), self::MLA_DEBUG_CATEGORY );
		} // acf_checkbox_enabled

		// Save select field values
		if ( self::$plugin_settings->get_plugin_option( 'acf_select_enabled' ) ) {
			self::$field_objects['MLAACFExample_bulk_edit_values'] = array();
			foreach( self::$field_objects as $field_name => $field_object ) {
				if ( !empty( $request[ 'acf_' . $field_name ] ) ) {
					self::$field_objects['MLAACFExample_bulk_edit_values'][ $field_name ] = $request[ 'acf_' . $field_name ];
				}

				unset( $request[ 'acf_' . $field_name ] );
			}

			MLACore::mla_debug_add( __LINE__ . ' MLAACFExample::mla_list_table_bulk_action_initial_request bulk_edit_values = ' . var_export( self::$field_objects['MLAACFExample_bulk_edit_values'], true ), self::MLA_DEBUG_CATEGORY );
		} // acf_select_enabled

		return $request;
	} // mla_list_table_bulk_action_initial_request

	/**
	 * Holds the new ACF checkbox value for the duration of a Bulk Edit action
	 *
	 * @since 1.01
	 *
	 * @var	string
	 */
	private static $acf_checkbox_value = NULL;

	/**
	 * Process an MLA_List_Table bulk action
	 *
	 * This filter gives you an opportunity to pre-process an MLA_List_Table page-level
	 * or single-item action, standard or custom, before the MLA handler.
	 * The filter is called once for each of the items in $_REQUEST['cb_attachment'].
	 *
	 * @since 1.00
	 *
	 * @param	array	$item_content	NULL, to indicate no handler.
	 * @param	string	$bulk_action	the requested action.
	 * @param	integer	$post_id		the affected attachment.
	 *
	 * @return	object	updated $item_content. NULL if no handler, otherwise
	 *					( 'message' => error or status message(s), 'body' => '',
	 *					  'prevent_default' => true to bypass the MLA handler )
	 */
	public static function mla_list_table_bulk_action( $item_content, $bulk_action, $post_id ) {
		// Accumulate multiple messages
		$messages = '';
		MLACore::mla_debug_add( __LINE__ . " MLAACFExample::mla_list_table_bulk_action( {$bulk_action}, {$post_id} )", self::MLA_DEBUG_CATEGORY );

		if ( self::$plugin_settings->get_plugin_option( 'acf_checkbox_enabled' ) ) {
			/*
			 * If the field is present, apply our own update process. Note the
			 * special 'empty' value to bulk-delete the custom field entirely.
			 */
			if ( ! empty( self::$acf_checkbox_value ) ) {
				$field = self::$plugin_settings->get_plugin_option( 'acf_checkbox_fields' );
				if ( 'empty' === self::$acf_checkbox_value ) {
					delete_post_meta( $post_id, $field );
					$messages .= sprintf( __( 'Deleting %1$s', 'media-library-assistant' ) . '<br>', $field );
				} else {
					update_post_meta( $post_id, $field, self::$acf_checkbox_value );
					$messages .= sprintf( __( 'Adding %1$s = %2$s', 'media-library-assistant' ) . '<br>', $field, implode( ',', self::$acf_checkbox_value ) );
				}
			}
		} // acf_checkbox_enabled

		// Apply select field values
		if ( self::$plugin_settings->get_plugin_option( 'acf_select_enabled' ) ) {
			foreach( self::$field_objects['MLAACFExample_bulk_edit_values'] as $field_name => $new_label ) {
				if ( 'empty' === $new_label ) {
					delete_post_meta( $post_id, $field_name );
					$messages .= sprintf( __( 'Deleting %1$s', 'media-library-assistant' ) . '<br>', $field_name );
				} else {
					$new_value = self::_convert_acf_label_to_value( $field_name, $new_label );
					
					if ( !empty( $new_value ) ) {
						update_post_meta( $post_id, $field_name, $new_value );
						$messages .= sprintf( __( 'Adding %1$s = %2$s', 'media-library-assistant' ) . '<br>', $field_name, $new_value );
					}
				}
			}

			// unset( self::$field_objects['MLAACFExample_bulk_edit_values'] );
		} // acf_select_enabled

		MLACore::mla_debug_add( __LINE__ . ' MLAACFExample::mla_list_table_bulk_action message = ' . var_export( $messages, true ), self::MLA_DEBUG_CATEGORY );
		if ( !empty( $messages ) ) {
			$item_content = array( 'message' => $messages );
		}
		
		return $item_content;
	} // mla_list_table_bulk_action

	/**
	 * MLA_List_Table inline edit item values
	 *
	 * This filter gives you a chance to modify and extend the substitution values
	 * for the Quick and Bulk Edit forms.
	 *
	 * @since 1.00
	 *
	 * @param	array	$item_values parameter_name => parameter_value pairs
	 *
	 * @return	array	updated substitution parameter name => value pairs
	 */
	public static function mla_list_table_inline_values( $item_values ) {
		//MLACore::mla_debug_add( __LINE__ . ' MLAACFExample::mla_list_table_inline_values item_values = ' . var_export( $item_values, true ), self::MLA_DEBUG_CATEGORY );
		if ( self::$plugin_settings->get_plugin_option( 'acf_checkbox_enabled' ) ) {
			$field = self::$plugin_settings->get_plugin_option( 'acf_checkbox_fields' );
			$title = self::$plugin_settings->get_plugin_option( 'acf_checkbox_titles' );
			// Replace the ACF Field Name with a more friendly Field Label
			$item_values['custom_fields'] = str_replace( '>' . $field . '<', '>' . $title . '<', $item_values['custom_fields'] );
			// $item_values['bulk_custom_fields'] = str_replace( '>acf_checkbox<', '>' . $title . '<', $item_values['bulk_custom_fields'] );
		} // acf_checkbox_enabled

		if ( self::$plugin_settings->get_plugin_option( 'acf_select_enabled' ) ) {
			// Append the ACF Select Field(s) to the Quick and Bulk lists
			foreach( self::$field_objects as $field_name => $field_object ) {
				MLACore::mla_debug_add( __LINE__ . " MLAACFExample::mla_list_table_inline_values( {$field_name} ) field_object = " . var_export( $field_object, true ), self::MLA_DEBUG_CATEGORY );
				if ( isset( $field_object['mla_field_label'] ) ) {
					$custom_fields_item  = '              <label class="inline-edit-acf_' . $field_name . '" style="clear:both"> ';
					$custom_fields_item .= '<span class="title">' . $field_object['mla_field_label'] . '</span> ';
					$custom_fields_item .= '<span class="input-text-wrap">' . "\n";
					$custom_fields_item .= '                <input type="text" name="acf_' . $field_name . '" value="" />' . "\n";
					$custom_fields_item .= '                </span> </label>' . "\n";
					$item_values['custom_fields'] .= $custom_fields_item;
					// $item_values['bulk_custom_fields'] .= $custom_fields_item;
				}
			}
		} // acf_select_enabled

		return $item_values;
	} // mla_list_table_inline_values

	/**
	 * Holds the ISC custom field name to column "slug" mapping values
	 *
	 * @since 1.01
	 *
	 * @var	array
	 */
	private static $field_slugs = array();

	/**
	 * Caches the ACF field definitions 
	 *
	 * @since 1.06
	 *
	 * @var	array ( field_name => field_object_array )
	 */
	private static $field_objects = NULL;

	/**
	 * Populate the select fields array
	 *
	 * @since 1.06
	 */
	private static function _build_select_field_objects() {
		// Build a cache of ACF select field "objects" (actually arrays)
		if ( self::$plugin_settings->get_plugin_option( 'acf_select_enabled' ) && is_null( self::$field_objects ) ) {
			self::$field_objects = array();
			$select_fields = explode( ',', self::$plugin_settings->get_plugin_option( 'acf_select_fields' ) );
			$select_titles = explode( ',', self::$plugin_settings->get_plugin_option( 'acf_select_titles' ) );

			if ( count( $select_fields ) ) {
				foreach( $select_fields as $index => $field_name ) {
					$field_object = acf_get_field( $field_name );
					if ( false === $field_object ) {
						continue;
					}

					if ( isset( $select_titles[ $index ] ) && !empty( $select_titles[ $index ] ) ) {
						$field_object[ 'mla_field_label' ] = $select_titles[ $index ];
					} else {
						$field_object[ 'mla_field_label' ] = $field_object['label'];
					}

					self::$field_objects[ $field_name ] = $field_object;
				}
			}
		} // acf_select_enabled
		MLACore::mla_debug_add( __LINE__ . ' MLAACFExample::_build_select_field_objects field_objects = ' . var_export( self::$field_objects, true ), self::MLA_DEBUG_CATEGORY );
	} // _build_select_field_objects

	/**
	 * Holds the select column sort specification
	 *
	 * @since 2.98
	 *
	 * @var	array
	 */
	private static $acf_sort_specification = array();

	/**
	 * Filter the MLA_List_Table columns
	 *
	 * This MLA-specific filter gives you an opportunity to filter the list table columns.
	 *
	 * @since 1.00
	 *
	 * @param	array	$columns An array of columns.
	 *					format: column_slug => Column Label
	 *
	 * @return	array	updated array of columns.
	 */
	public static function mla_list_table_get_columns( $columns ) {
		if ( self::$plugin_settings->get_plugin_option( 'acf_checkbox_enabled' ) ) {
			/*
			 * The Quick and Bulk Edit forms substitute arbitrary "slugs" for the
			 * custom field names. Remember them for table column and bulk update processing.
			 */
			if ( false !== $slug = array_search( self::$plugin_settings->get_plugin_option( 'acf_checkbox_fields' ), $columns ) ) {
				self::$field_slugs[ self::$plugin_settings->get_plugin_option( 'acf_checkbox_fields' ) ] = $slug;

				/*
				 * Change the column slug so we can provide our own friendly content.
				 * Replace the entry for the column we're capturing, preserving its place in the list
				 */
				$new_columns = array();

				foreach ( $columns as $key => $value ) {
					if ( $key === $slug ) {
						$new_columns[ self::$plugin_settings->get_plugin_option( 'acf_checkbox_fields' ) ] = self::$plugin_settings->get_plugin_option( 'acf_checkbox_titles' );
					} else {
						$new_columns[ $key ] = $value;
					}
				} // foreach column

				$columns = $new_columns;
			}
		} // acf_checkbox_enabled

		// Add a column of our own for the repeater "where-used" information
		if ( self::$plugin_settings->get_plugin_option( 'acf_repeater_enabled' ) ) {
			$columns[ 'acf_' . self::$plugin_settings->get_plugin_option( 'acf_repeater_fields' ) ] = self::$plugin_settings->get_plugin_option( 'acf_repeater_titles' );
		}

		// Add columns of our own for the image "where-used" information
		if ( self::$plugin_settings->get_plugin_option( 'acf_image_enabled' ) ) {
			$image_fields = explode( ',', self::$plugin_settings->get_plugin_option( 'acf_image_fields' ) );
			$image_titles = explode( ',', self::$plugin_settings->get_plugin_option( 'acf_image_titles' ) );

			if ( count( $image_fields ) ) {
				foreach( $image_fields as $index => $field_name ) {
					$field_object = acf_get_field( $field_name );
					if ( false === $field_object ) {
						continue;
					}

					if ( isset( $image_titles[ $index ] ) && !empty( $image_titles[ $index ] ) ) {
						$field_label = $image_titles[ $index ];
					} else {
						$field_label = $field_object['label'];
					}

					self::$field_slugs[ 'acf_' . $field_name ] = $field_name;
					$columns[ 'acf_' . $field_name ] = $field_label;
				}
			}
		} // acf_image_enabled

		if ( self::$plugin_settings->get_plugin_option( 'acf_select_enabled' ) ) {
			self::$acf_sort_specification = array();
			foreach( self::$field_objects as $field_name => $field_object ) {
				$acf_field_name = 'acf_' . $field_name;
				// Add columns of our own for the select information
				self::$field_slugs[ $acf_field_name ] = $field_name;
				$columns[ $acf_field_name ] = $field_object['mla_field_label'];

				// Reformat Media/Assistant submenu table sort specificaion for final terms filter
				if ( isset( $_REQUEST['orderby'] ) && ( $field_name === $_REQUEST['orderby'] ) ) {
					self::$acf_sort_specification[MLAQuery::MLA_ORDERBY_SUBQUERY] = true;
					self::$acf_sort_specification['orderby'] = 'c_' . $field_name;
					self::$acf_sort_specification['orderby_key'] = $field_name;
					unset($_REQUEST['orderby']);
				}
			}
		} // acf_select_enabled

		return $columns;
	} // mla_list_table_get_columns

	/**
	 * Filter the MLA_List_Table hidden columns
	 *
	 * This MLA-specific filter gives you an opportunity to filter the hidden list table columns.
	 *
	 * @since 1.00
	 *
	 * @param	array	$hidden_columns An array of columns.
	 *					format: index => column_slug
	 *
	 * @return	array	updated array of columns.
	 */
	public static function mla_list_table_get_hidden_columns( $hidden_columns ) {
		if ( self::$plugin_settings->get_plugin_option( 'acf_checkbox_enabled' ) ) {
			$field = self::$plugin_settings->get_plugin_option( 'acf_checkbox_fields' );
			// Replace the MLA custom field slug with our own slug value
			if ( isset( self::$field_slugs[ $field ] ) ) {
				$index = array_search( self::$field_slugs[ $field ], $hidden_columns );
				if ( false !== $index ) {
					$hidden_columns[ $index ] = $field;
				}
			}
		} // acf_checkbox_enabled

		return $hidden_columns;
	} // mla_list_table_get_hidden_columns

	/**
	 * Filter the MLA_List_Table sortable columns
	 *
	 * This MLA-specific filter gives you an opportunity to filter the sortable list table
	 * columns; a good alternative to the 'manage_media_page_mla_menu_sortable_columns' filter.
	 *
	 * @since 1.00
	 *
	 * @param	array	$sortable_columns	An array of columns.
	 *										Format: 'column_slug' => 'orderby'
	 *										or 'column_slug' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending.
	 *
	 * @return	array	updated array of columns.
	 */
	public static function mla_list_table_get_sortable_columns( $sortable_columns ) {
		if ( self::$plugin_settings->get_plugin_option( 'acf_checkbox_enabled' ) ) {
			$field = self::$plugin_settings->get_plugin_option( 'acf_checkbox_fields' );
			// Replace the slug for the column we've captured, preserving its place in the list
			if ( isset( self::$field_slugs[ $field ] ) ) {
				$slug = self::$field_slugs[ $field ];
				if ( isset( $sortable_columns[ $slug ] ) ) {
					$new_columns = array();
	
					foreach ( $sortable_columns as $key => $value ) {
						if ( $key == $slug ) {
							$new_columns[ $field ] = $value;
						} else {
							$new_columns[ $key ] = $value;
						}
					} // foreach column
	
					$sortable_columns = $new_columns;
				} // slug found
			} // slug exists
		} // acf_checkbox_enabled

		// Add columns of our own for the select information
		if ( self::$plugin_settings->get_plugin_option( 'acf_select_enabled' ) ) {
			foreach( self::$field_objects as $field_name => $field_object ) {
					$sortable_columns[ 'acf_' . $field_name ] = array( $field_name, true );
			}
		} // acf_select_enabled

		return $sortable_columns;
	} // mla_list_table_get_sortable_columns

	/**
	 * Translate post_status 'future', 'pending', 'draft' and 'trash' to label
	 *
	 * @since 1.02
	 * 
	 * @param	string	post_status
	 *
	 * @return	string	Status label or empty string
	 */
	protected static function _format_post_status( $post_status ) {
		$flag = ',<br>';
		switch ( $post_status ) {
			case 'draft' :
				$flag .= __('Draft');
				break;
			case 'future' :
				$flag .= __('Scheduled');
				break;
			case 'pending' :
				$flag .= _x('Pending', 'post state');
				break;
			case 'trash' :
				$flag .= __('Trash');
				break;
			default:
				$flag = '';
		}

	return $flag;
	}

	/**
	 * Supply a column value if no column-specific function has been defined
	 *
	 * Called when the MLA_List_Table can't find a value for a given column.
	 *
	 * @since 1.00
	 *
	 * @param	string	NULL, indicating no default content
	 * @param	array	A singular item (one full row's worth of data)
	 * @param	array	The name/slug of the column to be processed
	 * @return	string	Text or HTML to be placed inside the column
	 */
	public static function mla_list_table_column_default( $content, $item, $column_name ) {

		if ( self::$plugin_settings->get_plugin_option( 'acf_select_enabled' ) ) {
			if ( 0 === strpos( $column_name, 'acf_' ) ) {
				$acf_name = substr( $column_name, 4 );
				if ( isset( self::$field_objects[ $acf_name ] ) ) {
					$acf_field_value = get_field( $acf_name, $item->ID, false );
					if ( !empty( $acf_field_value ) ) {
						// choices -> array( value => label )
						$acf_choices = self::$field_objects[ $acf_name ]['choices'];

						// Multi-select dropdowns are allowed, returning an array of choices
						$inline_value = array();
						foreach ( (array) $acf_field_value as $choice ) {
							if ( isset( $acf_choices[ $choice ] ) ) {
								$inline_value[] = $acf_choices[ $choice ];
							} else {
								$inline_value[] = $choice;
							}
						}
						return implode( ',', $inline_value );
					}

					return '';
				} // found field_object
			} // found acf_ column
		} // acf_select_enabled

		if ( self::$plugin_settings->get_plugin_option( 'acf_checkbox_enabled' ) ) {
			// Convert the ACF-compatible array to a comma-delimited list of "checked" checkbox values.
			if ( self::$plugin_settings->get_plugin_option( 'acf_checkbox_fields' ) == $column_name ) {
				$mla_name = 'mla_item_' . $column_name;
				$values = isset( $item->$mla_name ) ? $item->$mla_name : '';
				if ( empty( $values ) ) {
					return '';
				} elseif ( is_array( $values ) ) {
//					return '[' . implode( '],[', $values ) . ']';
					return implode( ',', $values );
				}
	
				return $values;
			}
		} // acf_checkbox_enabled

		if ( self::$plugin_settings->get_plugin_option( 'acf_repeater_enabled' ) ) {
			$field = self::$plugin_settings->get_plugin_option( 'acf_repeater_fields' );
			// Retrieve and format the repeater field "where-used" information
			if ( ( 'acf_' . $field ) == $column_name ) {
				global $wpdb;
	
				$where_clause = $field . '_%_' . self::$plugin_settings->get_plugin_option( 'acf_repeater_subfields' );
				$references = $wpdb->get_results( 
					"
					SELECT *
					FROM {$wpdb->postmeta}
					WHERE meta_key LIKE '{$where_clause}' AND meta_value = {$item->ID}
					"
				);
	
				$content = '';
				if ( ! empty( $references ) ) {
					$parents = array();
					foreach ( $references as $reference ) {
						// key on post_id to remove duplicates
						$parents[ $reference->post_id ] = $reference->post_id;
					}
	
					$parents = implode( ',', $parents );
					$references = $wpdb->get_results(
						"
						SELECT ID, post_type, post_status, post_title
						FROM {$wpdb->posts}
						WHERE ( post_type <> 'revision' ) AND ( ID IN ({$parents}) )
						"
					);
	
					foreach ( $references as $reference ) {
						$status = self::_format_post_status( $reference->post_status );
	
						if ( $reference->ID == $item->post_parent ) {
							$parent = ',<br>' . __( 'PARENT', 'media-library-assistant' );
						} else {
							$parent = '';
						}
	
						$content .= sprintf( '<a href="%1$s" title="' . __( 'Edit', 'media-library-assistant' ) . ' &#8220;%2$s&#8221;">%2$s</a> (%3$s %4$s%5$s%6$s), ',
							/*%1$s*/ esc_url( add_query_arg( array('post' => $reference->ID, 'action' => 'edit'), 'post.php' ) ),
							/*%2$s*/ esc_attr( $reference->post_title ),
							/*%3$s*/ esc_attr( $reference->post_type ),
							/*%4$s*/ $reference->ID,
							/*%5$s*/ $status,
							/*%6$s*/ $parent ) . "<br>\r\n";
					} // foreach $reference
				} // found $references
	
				return $content;
			} // found repeater column
		} // acf_repeater_enabled

		if ( self::$plugin_settings->get_plugin_option( 'acf_image_enabled' ) ) {
			// Retrieve and format the image field(s) "where-used" information
			if ( array_key_exists( $column_name, self::$field_slugs ) ) {
				$content = '';
	
				$posts = self::_find_field_references( self::$field_slugs[ $column_name ], $item->ID );
				foreach ( $posts as $post_id => $post ) {
					$reference = self::$field_parents[ $post ];
					$status = self::_format_post_status( $reference->post_status );
	
					if ( $post_id == $item->post_parent ) {
						$parent = ',<br>' . __( 'PARENT', 'media-library-assistant' );
					} else {
						$parent = '';
					}
	
					$content .= sprintf( '<a href="%1$s" title="' . __( 'Edit', 'media-library-assistant' ) . ' &#8220;%2$s&#8221;">%2$s</a> (%3$s %4$s%5$s%6$s), ',
						/*%1$s*/ esc_url( add_query_arg( array('post' => $post_id, 'action' => 'edit'), 'post.php' ) ),
						/*%2$s*/ esc_attr( $reference->post_title ),
						/*%3$s*/ esc_attr( $reference->post_type ),
						/*%4$s*/ $post_id,
						/*%5$s*/ $status,
						/*%6$s*/ $parent ) . "<br>\r\n";
				} // foreach $reference

				return $content;
			} // found image column
		} // acf_image_enabled

		return $content;
	} // mla_list_table_column_default

	/**
	 * Filter the data for inline (Quick and Bulk) editing
	 *
	 * This filter gives you an opportunity to filter the data passed to the
	 * JavaScript functions for Quick and Bulk editing.
	 *
	 * @since 1.00
	 *
	 * @param	string	$inline_data	The HTML markup for inline data.
	 * @param	object	$item			The current Media Library item.
	 *
	 * @return	string	updated HTML markup for inline data.
	 */
	public static function mla_list_table_build_inline_data( $inline_data, $item ) {

		if ( self::$plugin_settings->get_plugin_option( 'acf_select_enabled' ) ) {
			foreach ( self::$field_objects as $acf_name => $acf_field_object ) {
				$inline_value = '';
				$acf_field_value = get_field( $acf_name, $item->ID, false );
//error_log( __LINE__ . ' mla_list_table_build_inline_data acf_field_object = ' . var_export( $acf_field_object, true ), 0 );
//error_log( __LINE__ . ' mla_list_table_build_inline_data acf_field_value = ' . var_export( $acf_field_value, true ), 0 );

				if ( !empty( $acf_field_value ) ) {
					// choices -> array( value => label )
					$acf_choices = $acf_field_object['choices'];
					
					// Multi-select dropdowns are allowed, returning an array of choices
					$inline_value = array();
					foreach ( (array) $acf_field_value as $choice ) {
						if ( isset( $acf_choices[ $choice ] ) ) {
							$inline_value[] = $acf_choices[ $choice ];
						} else {
							$inline_value[] = $choice;
						}
					}
					$inline_value = implode( ',', $inline_value );
				}
				
				$inline_data .= '	<div class="acf_' . $acf_name . '">' . esc_html( $inline_value ) . "</div>\r\n";
			} // foreach acf field
		} // acf_select_enabled

		if ( self::$plugin_settings->get_plugin_option( 'acf_checkbox_enabled' ) ) {
			$acf_checkbox_field =  self::$plugin_settings->get_plugin_option( 'acf_checkbox_fields' );
			// See if the field is present
			if ( ! isset( self::$field_slugs[ $acf_checkbox_field ] ) ) {
				return $inline_data;
			}
	
			// Convert the ACF-compatible array to a comma-delimited list of "checked" checkbox values.
			$match_count = preg_match_all( '/\<div class="' . self::$field_slugs[ $acf_checkbox_field ] . '"\>(.*)\<\/div\>/', $inline_data, $matches, PREG_OFFSET_CAPTURE );
			if ( ( $match_count == false ) || ( $match_count == 0 ) ) {
				return $inline_data;
			}

			$mla_checkbox_field = 'mla_item_' . $acf_checkbox_field;
			if ( isset( $item->$mla_checkbox_field ) ) {
				$value = $item->$mla_checkbox_field;
				if ( is_array( $value ) ) {
					$head = substr( $inline_data, 0, $matches[1][0][1] );
					$value = esc_html( implode( ',', $value ) );
					$tail = substr( $inline_data, ( $matches[1][0][1] + strlen( $matches[1][0][0] ) ) );
					$inline_data = $head . $value . $tail;
				}
			}
		} // acf_checkbox_enabled

		MLACore::mla_debug_add( __LINE__ . ' MLAACFExample::mla_list_table_build_inline_data inline_data = ' . var_export( $inline_data, true ), self::MLA_DEBUG_CATEGORY );
		return $inline_data;
	} // mla_list_table_build_inline_data

	/**
	 * MLA List Table Query Final Terms Filter
	 *
	 * Gives you an opportunity to modify the query just before it is executed.
	 *
	 * @since 1.06
	 *
	 * @param	array	WP_Query parameters
	 */
	public static function mla_list_table_query_final_terms( $request ) {

		// Set in self::mla_list_table_get_columns()
		if ( !empty( self::$acf_sort_specification ) ) { 
			MLAQuery::$query_parameters = array_merge( MLAQuery::$query_parameters, self::$acf_sort_specification );
			unset( $request['orderby'] );
			unset( $request['order'] );
		}
		
		return $request;
	} // mla_list_table_query_final_terms

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
		if ( 'acf' !== strtolower( $value['prefix'] ) ) {
			return $custom_value;
		}
		
		// Parse the original text to separate out the qualifier
		$value = MLAData::mla_parse_substitution_parameter( $key, $default_option );
		$field = $value['value'];
		$qualifier = $value['qualifier'];
		//error_log( __LINE__ . " MLAACFExample::mla_expand_custom_prefix( {$key}, {$post_id}, {$keep_existing}, {$default_option} ) value = " . var_export( $value, true ), 0 );
		//error_log( __LINE__ . " MLAACFExample::mla_expand_custom_prefix( {$key}, {$post_id} ) query = " . var_export( $query, true ), 0 );
		//error_log( __LINE__ . " MLAACFExample::mla_expand_custom_prefix( {$key}, {$post_id} ) markup_values = " . var_export( $markup_values, true ), 0 );

		// Set debug mode
		$debug_active = isset( $query['mla_debug'] ) && ( 'false' !== trim( strtolower( $query['mla_debug'] ) ) );
		if ( $debug_active ) {
			$old_mode = MLACore::mla_debug_mode( 'log' );
			MLACore::mla_debug_add( __LINE__ . " MLAACFExample::mla_expand_custom_prefix( {$key}, {$post_id}, {$keep_existing}, {$default_option} ) \$_REQUEST = " . var_export( $_REQUEST, true ) );
			MLACore::mla_debug_add( __LINE__ . " MLAACFExample::mla_expand_custom_prefix() \$value = " . var_export( $value, true ) );
			MLACore::mla_debug_add( __LINE__ . " MLAACFExample::mla_expand_custom_prefix() \$query = " . var_export( $query, true ) );
			MLACore::mla_debug_add( __LINE__ . " MLAACFExample::mla_expand_custom_prefix() \$markup_values = " . var_export( $markup_values, true ) );
		}

		if ( self::$plugin_settings->get_plugin_option( 'acf_image_enabled' ) ) {
			$posts = self::_find_field_references( $field, $post_id );
			//error_log( __LINE__ . " MLAACFExample::mla_expand_custom_prefix( {$key}, {$post_id} ) posts = " . var_export( $posts, true ), 0 );
			if (  !empty( $posts ) ) {
				switch ( $qualifier ) {
					case 'count':
						$custom_value = count( $posts );
						break;
					case 'present':
						$custom_value = ( count( $posts ) ) ? 1 : 0;
						break;
					default:
						$low_bound = absint( $qualifier );
						if ( $low_bound ) {
							$custom_value = count( $posts );
							if ( $low_bound > $custom_value ) {
								$custom_value = NULL;
							}
	
							break;
						}
						
						$custom_value = '';
						$item = get_post( $post_id );
			
						foreach ( $posts as $post_id => $post ) {
							$reference = self::$field_parents[ $post ];
							$status = self::_format_post_status( $reference->post_status );
			
							if ( $post_id == $item->post_parent ) {
								$parent = ', ' . __( 'PARENT', 'media-library-assistant' );
							} else {
								$parent = '';
							}
			
							$custom_value .= sprintf( '%1$s (%2$s %3$s%4$s%5$s), ',
								/*%1$s*/ esc_attr( $reference->post_title ),
								/*%2$s*/ esc_attr( $reference->post_type ),
								/*%3$s*/ $post_id,
								/*%4$s*/ $status,
								/*%5$s*/ $parent );
						} // foreach $reference
				}
			}
		} // acf_image_enabled

		if ( self::$plugin_settings->get_plugin_option( 'acf_select_enabled' ) ) {
			// Build a cache of ACF select field "objects" (actually arrays)
			self::_build_select_field_objects();

			if ( isset( self::$field_objects[ $field ] ) ) {
				$acf_field_object = self::$field_objects[ $field ];
				$acf_field_value = get_field( $field, $post_id, false );

				if ( empty( $acf_field_value ) ) {
					$acf_field_value = '';
				}

				// choices -> array( value => label )
				$acf_choices = $acf_field_object['choices'];

				switch ( $qualifier ) {
					case 'choices':
						$custom_value = $acf_field_object['choices'];
						break;
					case 'value':
						$custom_value = $acf_field_value;
						break;
					case 'label':
						if ( isset( $acf_choices[ $acf_field_value ] ) ) {
							$custom_value = $acf_choices[ $acf_field_value ];
						} else {
							$custom_value = $acf_field_value;
						}
						break;
					case 'mla_field_label':
						$custom_value = $acf_field_object['mla_field_label'];
						break;
				}

				$custom_value = MLAData::mla_apply_field_level_format( $custom_value, $value );
			} // found select field
		} // acf_select_enabled

		MLACore::mla_debug_add( __LINE__ . " MLAACFExample::mla_expand_custom_prefix( {$key}, {$post_id} ) custom_value = " . var_export( $custom_value, true ) );
		return $custom_value;
	} // mla_expand_custom_prefix

	/**
	 * Cached values for the ACF Image fields
	 *
	 * @since 1.05
	 *
	 * @var	array( field_slug => array( item_ID => array( reference_ID => reference_ID ) ) )
	 */
	private static $field_instances = array();

	/**
	 * Cached "parent post" values for the ACF Image fields
	 *
	 * @since 1.05
	 *
	 * @var	array( post_ID => array( post_type, post_status, post_title ) )
	 */
	private static $field_parents = array();

	/**
	 * Find references to Media Library items in ACF Image fields
	 *
	 * @since 1.00
	 *
	 * @param	string	$field_slug	ACF Image variable slug.
	 * @param	integer	$item_ID	Media Library item ID.
	 *
	 * @return	array	post_id values referencing the item.
	 */
	private static function _find_field_references( $field_slug, $item_ID ) {
		global $wpdb;

		if ( !isset( self::$field_instances[ $field_slug ] ) ) {
			$references = $wpdb->get_results( 
				"
				SELECT post_id, meta_value
				FROM {$wpdb->postmeta}
				WHERE meta_key LIKE '{$field_slug}'
				"
			);

			$post_ids = array();
			foreach ( $references as $reference ) {
				$post_id = (int) $reference->post_id;
				$meta_value = (int) $reference->meta_value;
				self::$field_instances[ $field_slug ][ $meta_value ][ $post_id ] = $post_id;
				$post_ids[ $post_id ] = $post_id;
			}

			// Find the post information, excluding revisions
			$parents = implode( ',', $post_ids );
			$references = $wpdb->get_results(
				"
				SELECT ID, post_type, post_status, post_title
				FROM {$wpdb->posts}
				WHERE ( post_type <> 'revision' ) AND ( ID IN ({$parents}) )
				"
			);

			foreach ( $references as $reference ) {
				$post_id = (int) $reference->ID;
				unset( $reference->ID );
				self::$field_parents[ $post_id ] = $reference;
			}
			MLACore::mla_debug_add( __LINE__ . " MLAACFExample::_find_field_references( {$field_slug} ) field_parents = " . var_export( self::$field_parents, true ) );

			// Remove the revisions from the field references
			foreach ( self::$field_instances[ $field_slug ] as $meta_value => $post_ids ) {
				$references = array();
				foreach ( $post_ids as $post_id ) {
					if ( array_key_exists(  $post_id, self::$field_parents ) ) {
						$references[ $post_id ] = $post_id;
					}
				}

				if ( count( $references ) ) {
					self::$field_instances[ $field_slug ][ $meta_value ] = $references;
				} else {
					unset( self::$field_instances[ $field_slug ][ $meta_value ] );
				}
			}
		MLACore::mla_debug_add( __LINE__ . " MLAACFExample::_find_field_references( {$field_slug} ) field_instances = " . var_export( self::$field_instances, true ) );
		} // !isset( self::$field_instances[ $field_slug ] )
		
		if ( isset( self::$field_instances[ $field_slug ][ $item_ID ] ) ) {
			return self::$field_instances[ $field_slug ][ $item_ID ];
		}

		return array();
	} // _find_field_references
} // Class MLAACFExample

// Install the filters at an early opportunity
add_action('init', 'MLAACFExample::initialize');
?>
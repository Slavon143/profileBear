<?php
/**
 * Adds "Preset Terms" rollover action to Media/Assistant rollover actions, redirecting to Media/Add New (Upload New Media) screen
 *
 * In this example, the "Preset Terms" rollover action is detected and, if found,
 * copies the taxonomy terms from the associated item to populate the controls on
 * the Media/Add New (Upload New Media) screen.
 *
 * This example plugin uses two of the many filters available in the Media/Assistant submenu screen
 * and illustrates a technique you can use to customize the submenu rollover actions.
 *
 * Created for support topic "Cloning category/tags settings from uploades file to upload page."
 * opened on 11/17/2021 by "poolfactory".
 * https://wordpress.org/support/topic/cloning-category-tags-settings-from-uploades-file-to-upload-page/
 *
 * @package MLA Preset Terms Example
 * @version 1.00
 */

/*
Plugin Name: MLA Preset Terms Example
Plugin URI: http://davidlingren.com/
Description: Adds "Preset Terms" to Media/Assistant rollover actions, redirecting to Media/Add New (Upload New Media) screen with terms copied from the selected item
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
 * Class MLA Preset Terms Example adds a "Preset Terms" action to
 * Media/Assistant rollover actions.
 *
 * @package MLA Preset Terms Example
 * @since 1.00
 */
class MLAPresetTermsExample {
	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 1.00
	 */
	public static function initialize() {
		// The filters are only useful in the admin section
		if ( !is_admin() ) {
			return;
		}

		add_filter( 'mla_list_table_build_rollover_actions', 'MLAPresetTermsExample::mla_list_table_build_rollover_actions', 10, 3 );

		// This action is run before any Media/Assistant output is generated
		add_action( 'mla_list_table_custom_admin_action', 'MLAPresetTermsExample::mla_list_table_custom_admin_action', 10, 2 ); // sanitize_text_field( wp_unslash( $_REQUEST['mla_admin_action'] ) ), $mla_item_id );
		
		add_filter( 'mla_upload_bulk_edit_form_initial_fieldset_values', 'MLAPresetTermsExample::mla_upload_bulk_edit_form_fieldset_values', 10, 2 );
		//add_filter( 'mla_upload_bulk_edit_form_preset_fieldset_values', 'MLAPresetTermsExample::mla_upload_bulk_edit_form_fieldset_values', 10, 2 );
		add_filter( 'mla_upload_bulk_edit_form_initial_values', 'MLAPresetTermsExample::mla_upload_bulk_edit_form_values', 10, 1 );
	}

	/**
	 * Add Preset Terms to the list of item "Rollover" actions
	 *
	 * @since 1.00
	 *
	 * @param	array	$actions	The list of item "Rollover" actions.
	 * @param	object	$item		The current Media Library item.
	 * @param	string	$column		The List Table column slug.
	 */
	public static function mla_list_table_build_rollover_actions( $actions, $item, $column ) {
		//error_log( __LINE__ . " MLAPresetTermsExample::mla_list_table_build_rollover_actions( {$item->ID}, {$column} ) \$actions = " . var_export( $actions, true ), 0 );
		//error_log( __LINE__ . " MLAPresetTermsExample::mla_list_table_build_rollover_actions ( {$item->ID}, {$column} ) \$item = " . var_export( $item, true ), 0 );

		$query_args = array( 'page' => MLACore::ADMIN_PAGE_SLUG, 'mla_admin_action' => 'preset_terms', 'mla_item_ID' => $item->ID );
		$actions['presetterms'] = '<a href="' . add_query_arg( $query_args, MLACore::mla_nonce_url( 'upload.php', MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME ) ) . '" title="Copy terms for Upload">Preset Terms</a>';

		return $actions;
	} // mla_list_table_build_rollover_actions

	/**
	 * Process the Preset Terms rollover action
	 *
	 * @since 1.00
	 *
	 * @param	string	$mla_admin_action	The requested action.
	 * @param	integer	$mla_item_ID		Zero (0), or the affected attachment.
	 */
	public static function mla_list_table_custom_admin_action( $mla_admin_action, $mla_item_ID ) {
		error_log( __LINE__ . " MLAPresetTermsExample::mla_list_table_custom_admin_action( {$mla_admin_action}, {$mla_item_ID} ) \$_REQUEST = " . var_export( $_REQUEST, true ), 0 );
		
		if ( ( 'preset_terms' === $mla_admin_action ) && ( 'mla-menu' === $_REQUEST['page'] ) ) {
			$query_args = array( 'mla_preset_terms_ID' => $mla_item_ID );
			wp_redirect( add_query_arg( $query_args, admin_url( 'media-new.php' ) ), 302 );
			exit;
		}
	} // mla_list_table_custom_admin_action

	/**
	 * MLAEdit bulk edit on upload fieldset values
	 *
	 * This filter gives you a chance to modify the raw data used to populate
	 * the Bulk Edit on Upload form.
	 *
	 * @since 1.00
	 *
	 * @param	array	$fieldset_values data values to populate the form
	 * @param	string	$filter_root identify the blank, initial and preset fieldsets
	 */
	public static function mla_upload_bulk_edit_form_fieldset_values( $fieldset_values, $filter_root ) {
		//error_log( __LINE__ . ' MLAPresetTermsExample::mla_upload_bulk_edit_form_fieldset_values $_REQUEST = ' . var_export( $_REQUEST, true ), 0 );
		//error_log( __LINE__ . " MLAPresetTermsExample::mla_upload_bulk_edit_form_fieldset_values( {$filter_root} ) \$fieldset_values = " . var_export( $fieldset_values, true ), 0 );

		if ( isset( $_REQUEST['mla_preset_terms_ID'] ) && ( 'mla_upload_bulk_edit_form_initial' === $filter_root ) ) {
			$mla_preset_terms_ID = (integer) $_REQUEST['mla_preset_terms_ID'];
		} else {
			return $fieldset_values;
		}
		
		$tax_input = array();
		$taxonomies = get_object_taxonomies( 'attachment', 'objects' );

		foreach ( $taxonomies as $tax_name => $tax_object ) {
			if ( $tax_object->show_ui && MLACore::mla_taxonomy_support( $tax_name, 'quick-edit' ) ) {
				$terms = get_object_term_cache( $mla_preset_terms_ID, $tax_name );
				if ( false === $terms ) {
					$terms = wp_get_object_terms( $mla_preset_terms_ID, $tax_name );
					wp_cache_add( $mla_preset_terms_ID, $terms, $tax_name . '_relationships' );
				}

				if ( is_wp_error( $terms ) || empty( $terms ) ) {
					$terms = array();
				}

				if ( $tax_object->hierarchical || MLACore::mla_taxonomy_support( $tax_name, 'flat-checklist' ) ) {
					foreach( $terms as $term ) {
						$tax_input[ $tax_name ][] = $term->term_id;
					}
				} else {
					$term_array = array();
					foreach( $terms as $term ) {
						$term_array[] = $term->name;
					}
					$tax_input[ $tax_name ] = implode( ',', $term_array );
				}
			}
		}
		//error_log( __LINE__ . ' MLAPresetTermsExample::mla_upload_bulk_edit_form_fieldset_values $tax_input = ' . var_export( $tax_input, true ), 0 );

		$tax_action = array();
		foreach ( $tax_input as $tax_name => $term_assignments ) {
			$tax_action[ $tax_name ] = 'add';
		}
		//error_log( __LINE__ . ' MLAPresetTermsExample::mla_upload_bulk_edit_form_fieldset_values $tax_action = ' . var_export( $tax_action, true ), 0 );
		
		$fieldset_values['tax_input'] = $tax_input;
		$fieldset_values['tax_action'] = $tax_action;
		
		return $fieldset_values;
	} // mla_upload_bulk_edit_form_fieldset_values

	/**
	 * MLAEdit bulk edit on upload item values
	 *
	 * This filter gives you a chance to modify and extend the substitution values
	 * for the Bulk Edit on Upload form.
	 *
	 * @since 1.00
	 *
	 * @param	array	$item_values [ parameter_name => parameter_value ] pairs
	 */
	public static function mla_upload_bulk_edit_form_values( $item_values ) {
		//error_log( __LINE__ . ' MLAPresetTermsExample::mla_upload_bulk_edit_form_values $_REQUEST = ' . var_export( $_REQUEST, true ), 0 );
		//error_log( __LINE__ . ' MLAPresetTermsExample::mla_upload_bulk_edit_form_values $item_values = ' . var_export( $item_values, true ), 0 );

		return $item_values;
	} // mla_upload_bulk_edit_form_values
} // Class MLAPresetTermsExample

// Install the filters at an early opportunity
add_action('init', 'MLAPresetTermsExample::initialize');
?>
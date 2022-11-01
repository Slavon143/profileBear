<?php
/**
 * THIS PLUGIN IS OBSOLETE! PLEASE USE "MLA Advanced Custom Fields Example" INSTEAD.
 *
 * @package MLA ACF Checkbox Example
 * @version 1.03
 */

/*
Plugin Name: MLA ACF Checkbox Example
Plugin URI: http://davidlingren.com/
Description: THIS PLUGIN IS OBSOLETE! PLEASE USE "MLA Advanced Custom Fields Example" INSTEAD.
Author: David Lingren
Version: 1.03
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
 * Class MLA ACF Checkbox Example hooks some of the filters provided by the MLA_List_Table class
 *
 * Call it anything you want, but give it an unlikely and hopefully unique name. Hiding everything
 * else inside a class means this is the only name you have to worry about.
 *
 * @package MLA ACF Checkbox Example
 * @since 1.00
 */
class MLAACFCheckboxExample {
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

		// Defined in /media-library-assistant/includes/class-mla-list-table.php
		add_filter( 'mla_list_table_get_columns', 'MLAACFCheckboxExample::mla_list_table_get_columns', 10, 1 ); //
		add_filter( 'mla_list_table_get_hidden_columns', 'MLAACFCheckboxExample::mla_list_table_get_hidden_columns', 10, 1 ); //
		add_filter( 'mla_list_table_get_sortable_columns', 'MLAACFCheckboxExample::mla_list_table_get_sortable_columns', 10, 1 ); //
		add_filter( 'mla_list_table_column_default', 'MLAACFCheckboxExample::mla_list_table_column_default', 10, 3 ); //
	}

	/**
	 * Holds the ISC custom field name to column "slug" mapping values
	 *
	 * @since 1.01
	 *
	 * @var	array
	 */
	private static $field_slugs = array();

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
		/*
		 * The Quick and Bulk Edit forms substitute arbitrary "slugs" for the
		 * custom field names. Remember them for table column and bulk update processing.
		 */
		if ( false !== $slug = array_search( 'acf_checkbox', $columns ) ) {
			self::$field_slugs['acf_checkbox'] = $slug;

			/*
			 * Change the column slug so we can provide our own friendly content.
			 * Replace the entry for the column we're capturing, preserving its place in the list
			 */
			$new_columns = array();

			foreach ( $columns as $key => $value ) {
				if ( $key === $slug ) {
					$new_columns['acf_checkbox'] = 'acf_checkbox';
				} else {
					$new_columns[ $key ] = $value;
				}
			} // foreach column

			$columns = $new_columns;
		}

		return $columns;
	} // mla_list_table_get_columns_filter

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
		/*
		 * Replace the MLA custom field slug with our own slug value
		 */
		if ( isset( self::$field_slugs['acf_checkbox'] ) ) {
			$index = array_search( self::$field_slugs['acf_checkbox'], $hidden_columns );
			if ( false !== $index ) {
				$hidden_columns[ $index ] = 'acf_checkbox';
			}
		}

		return $hidden_columns;
	} // mla_list_table_get_hidden_columns_filter

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
		// Replace the slug for the column we've captured, preserving its place in the list
		if ( isset( self::$field_slugs['acf_checkbox'] ) ) {
			$slug = self::$field_slugs['acf_checkbox'];
			if ( isset( $sortable_columns[ $slug ] ) ) {
				$new_columns = array();

				foreach ( $sortable_columns as $key => $value ) {
					if ( $key == $slug ) {
						$new_columns['acf_checkbox'] = $value;
					} else {
						$new_columns[ $key ] = $value;
					}
				} // foreach column

				$sortable_columns = $new_columns;
			} // slug found
		} // slug exists

		return $sortable_columns;
	} // mla_list_table_get_sortable_columns_filter

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
		// Display the notice.
		if ( 'acf_checkbox' === $column_name ) {
			return 'THIS PLUGIN IS OBSOLETE! PLEASE USE "MLA Advanced Custom Fields Example" INSTEAD.';
		}

		return $content;
	} // mla_list_table_column_default_filter
} // Class MLAACFCheckboxExample

// Install the filters at an early opportunity
add_action('init', 'MLAACFCheckboxExample::initialize');
?>
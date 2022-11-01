<?php
/**
 * Provides an example of hooking the filters provided by the MLA_List_Table class
 *
 * In this example, Phoenix Media Rename's "Filename" column is added to the Media/Assistant submenu table.
 * 
 * Created for support topic "support Phoenix Media Rename"
 * opened on 8/7/2019 by "cyberchicken":
 * https://wordpress.org/support/topic/support-phoenix-media-rename/
 *
 * @package MLA Phoenix Media Rename Example
 * @version 1.00
 */

/*
Plugin Name: MLA Phoenix Media Rename Example
Plugin URI: http://davidlingren.com/
Description: Adds support for Phoenix Media Rename's "Filename" column in the Media/Assistant submenu table.
Author: David Lingren
Version: 1.00
Author URI: http://davidlingren.com/

Copyright 2019 David Lingren

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
class MLAPhoenixMediaRenameExample {
	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		// The filters are only useful for the admin section; exit in the front-end posts/pages
		if ( ! is_admin() ) {
			return;
		}

		// The filters are only useful for the Phoenix Media Rename plugin is installed and active
		if ( class_exists( 'Phoenix_Media_Rename' ) ) {
			self::$pmr = new Phoenix_Media_Rename;
		} else {
			return;
		}

		/*
		 * add_filter parameters:
		 * $tag - name of the hook you're filtering; defined by [mla_gallery]
		 * $function_to_add - function to be called when [mla_gallery] applies the filter
		 * $priority - default 10; lower runs earlier, higher runs later
		 * $accepted_args - number of arguments your function accepts
		 */
		 
		// Defined in /media-library-assistant/includes/class-mla-main.php
		add_filter( 'mla_list_table_custom_bulk_action', 'MLAPhoenixMediaRenameExample::mla_list_table_custom_bulk_action', 10, 3 ); //

		// Defined in /media-library-assistant/includes/class-mla-list-table.php
		add_filter( 'mla_list_table_get_columns', 'MLAPhoenixMediaRenameExample::mla_list_table_get_columns', 10, 1 ); //
		add_filter( 'mla_list_table_column_default', 'MLAPhoenixMediaRenameExample::mla_list_table_column_default', 10, 3 ); //
	}

	/**
	 * Makes the Phoeniz Media Rename functions accessible to this plugin
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $pmr = NULL;

	/**
	 * Process an MLA_List_Table custom bulk action
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
	public static function mla_list_table_custom_bulk_action( $item_content, $bulk_action, $post_id ) {
		// Apply the Phoenix Media Rename action, if requested.
		$retitle = constant("actionRenameRetitle") === $bulk_action;
		if ( $retitle || constant("actionRename") === $bulk_action ) {
			$new_filename = isset( $_REQUEST['mla-media-rename'][$post_id] ) ? $_REQUEST['mla-media-rename'][$post_id] : false;
			if ( $new_filename ) {
				$result = self::$pmr->do_rename($post_id, $new_filename, $retitle);
			} else {
				$result = 0;
			}
			
			$result = ( 1 === $result ) ? __( 'succeeded', 'media-library-assistant' ) : __( 'failed', 'media-library-assistant' );
			$action = $retitle ? __( 'Rename & Retitle', 'media-library-assistant' ) : __( 'Rename', 'media-library-assistant' );
			$item_content = array( 'message' => sprintf( __( '%1$s for item %2$d %3$s', 'media-library-assistant' ), $action, $post_id, $result ) );
		}

		return $item_content;
	} // mla_list_table_custom_bulk_action

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
		return self::$pmr->add_filename_column( $columns );
	} // mla_list_table_get_columns_filter

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
		if ( 'filename' == $column_name ) {
			ob_start();
			self::$pmr->add_filename_column_content( $column_name, $item->ID );
			$content = ob_get_clean();

			// Add a "name" attribute so we can access the updated "filename" column content
			$name_addition = '<input name="mla-media-rename[' . $item->ID . ']" type';
			$content = str_replace( '<input type', $name_addition, $content );
			return $content;
		}

		return $content;
	} // mla_list_table_column_default_filter
} // Class MLAPhoenixMediaRenameExample

// Install the filters at an early opportunity
add_action('init', 'MLAPhoenixMediaRenameExample::initialize');
?>
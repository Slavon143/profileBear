<?php
/**
 * Reformats Media/Assistant column display for Uploaded on and Modified dates
 *
 * In this example the MLA_List_Table class is extended to replace the default content for two
 * of the columns in the Media/Assistant submenu table.
 *
 * The example plugin uses one of the many filters available in the Media/Assistant Submenu
 * and illustrates one of the techniques you can use to customize the submenu table display.
 *
 * Created for support topic "Sorting a gallery, once again"
 * opened on 1/14/2018 by "dima-stefantsov)".
 * https://wordpress.org/support/topic/sorting-a-gallery-once-again/
 *
 * @package MLA Uploaded on Example
 * @version 1.01
 */

/*
Plugin Name: MLA Uploaded on Example
Plugin URI: http://davidlingren.com/
Description: Reformats Media/Assistant column display for Uploaded on and Modified dates
Author: David Lingren
Version: 1.01
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
 * Class MLA Custom Field Search Example extends the Media/Assistant "Search Media" box
 * to custom field values
 *
 * @package MLA Custom Field Search Example
 * @since 1.00
 */
class MLAUploadedOnExample {
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

		// Defined in /media-library-assistant/includes/class-mla-main.php
		add_filter( 'mla_list_table_new_instance', 'MLAUploadedOnExample::mla_list_table_new_instance', 10, 1 );
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
		return new Uploaded_On_MLA_List_Table;
	} // mla_list_table_new_instance
} // Class MLAUploadedOnExample

// This code only works if MLA is installed and active!
if ( defined( 'MLA_PLUGIN_PATH' ) ) {
	// The MLA_List_Table class isn't automatically available to plugins
	if ( !class_exists( 'MLA_List_Table' ) ) {
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-list-table.php' );
	}
	
	/**
	 * Class MLA (Media Library Assistant) List Table implements the "Assistant" admin submenu
	 *
	 * Extends the core WP_List_Table class.
	 *
	 * @package Media Library Assistant
	 * @since 0.1
	 */
	class Uploaded_On_MLA_List_Table extends MLA_List_Table {
		/**
		 * Supply a fixed-format Uploaded on date/time
		 *
		 * @since 1.00
		 * 
		 * @param	array	A singular attachment (post) object
		 * @return	string	HTML markup to be placed inside the column
		 */
		function column_date( $item ) {
			global $post;
	
			$post = $item; // Resolve issue with "The Events Calendar"
			$date = mysql2date( 'Y-m-d', $item->post_date );
			$time = mysql2date( 'H:i:s', $item->post_date );
	
			return $date . '<br />&nbsp;&nbsp;' . $time;
		} // column_date
	
		/**
		 * Supply the content for a custom column
		 *
		 * @since 1.00
		 * 
		 * @param	array	A singular attachment (post) object
		 * @return	string	HTML markup to be placed inside the column
		 */
		function column_modified( $item ) {
			$date = mysql2date( 'Y-m-d', $item->post_date );
			$time = mysql2date( 'H:i:s', $item->post_date );
	
			return $date . '<br />&nbsp;&nbsp;' . $time;
		} // column_modified
	} // Uploaded_On_MLA_List_Table
	
	// Install the filters at an early opportunity
	add_action('init', 'MLAUploadedOnExample::initialize');
} // defined( 'MLA_PLUGIN_PATH' ) )
?>
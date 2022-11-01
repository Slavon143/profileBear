<?php
/**
 * Media Library Assistant Admin Columns Pro (plugin) Support
 *
 * @package Media Library Assistant
 * @since 2.71
 */
defined( 'ABSPATH' ) or die();

/**
 * Class Admin Columns Addon MLA (Media Library Assistant) List Screen supports the Admin Columns plugin
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_ListScreen extends AC_Addon_MLA_ListScreen
	implements ACP\Editing\ListScreen, ACP\Export\ListScreen {

	/**
	 * Initializes some properties, installs filters and then
	 * calls the parent constructor to set some default configs.
	 *
	 * @since 2.71
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'ac/table/list_screen', array( $this, 'export_table_global' ) );
		
		if ( version_compare( ACP()->get_version(), '4.5.4', '>=' ) ) {
			add_filter('acp/editing/bulk/active', array( $this, 'disable_bulk_editing' ), 10, 2 );
		}
	}

	/**
	 * Set MLA-specific inline editing strategy for Admin Columns Pro
	 *
	 * @since 2.71
	 */
	public function editing() {
		return new ACP_Addon_MLA_Editing_Strategy( 'attachment' );
	}

	/**
	 * Set MLA-specific inline editing strategy for Admin Columns Pro
	 *
	 * @since 2.79
	 *
	 * @param boolean $active
	 * @param AC_ListScreen $list_screen
	 */
	public function disable_bulk_editing( $active, $list_screen ){
		if( $list_screen instanceof ACP_Addon_MLA_ListScreen ){
			return false;
		}

		return $active;
	}

	/**
	 * Create and populate an MLA_List_Table object in the $wp_list_table global variable
	 *
	 * When exporting the same page is being requested by an ajax request and triggers the filter 'the_posts'.
	 * The callback for this filter will print a json string. This needs to be done before any rendering of the page.
	 *
	 *  Also, export needs the $GLOBALS['wp_list_table'] to be populated for displaying the export button.
	 *
	 * @since 2.71
	 *
	 * @param AC_ListScreen $list_screen
	 */
	public function export_table_global( AC_ListScreen $list_screen ) {
		global $wp_list_table;

		if ( ! $list_screen instanceof ACP_Addon_MLA_ListScreen ) {
			return;
		}

		if ( ! class_exists( 'MLA_List_Table' ) ) {
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-list-table.php' );
			MLA_List_Table::mla_admin_init_action();
		}

		if ( $wp_list_table instanceof MLA_List_Table ) {
			return;
		}

		$wp_list_table = new MLA_List_Table();
		$wp_list_table->prepare_items();
	}

	/**
	 * Set MLA-specific export strategy for Admin Columns Pro
	 *
	 * @since 2.71
	 */
	public function export() {
		return new ACP_Addon_MLA_Export_Strategy( $this );
	}

	/**
	 * Register MLA-specific column definitions for Admin Columns Pro
	 *
	 * @since 2.71
	 */
	public function register_column_types() {
		parent::register_column_types();

		// Autoload the CustomField class if necessary
		if ( class_exists( 'ACP\Column\CustomField' ) ) {
            $this->register_column_type( new ACP\Column\CustomField );
		}

		$this->register_column_type( new ACP_Addon_MLA_Column_ID_Parent );
		$this->register_column_type( new ACP_Addon_MLA_Column_Title_Name() );
		$this->register_column_type( new ACP_Addon_MLA_Column_Title() );
		$this->register_column_type( new ACP_Addon_MLA_Column_Name() );
		$this->register_column_type( new ACP_Addon_MLA_Column_Parent() );
		$this->register_column_type( new ACP_Addon_MLA_Column_MenuOrder() );
		$this->register_column_type( new ACP_Addon_MLA_Column_Features() );
		$this->register_column_type( new ACP_Addon_MLA_Column_Inserts() );
		$this->register_column_type( new ACP_Addon_MLA_Column_Galleries() );
		$this->register_column_type( new ACP_Addon_MLA_Column_MLA_Galleries() );
		$this->register_column_type( new ACP_Addon_MLA_Column_AltText() );
		$this->register_column_type( new ACP_Addon_MLA_Column_Caption() );
		$this->register_column_type( new ACP_Addon_MLA_Column_Description() );
		$this->register_column_type( new ACP_Addon_MLA_Column_MimeType() );
		$this->register_column_type( new ACP_Addon_MLA_Column_FileURL() );
		$this->register_column_type( new ACP_Addon_MLA_Column_Base_File() );
		$this->register_column_type( new ACP_Addon_MLA_Column_Date() );
		$this->register_column_type( new ACP_Addon_MLA_Column_Modified() );
		$this->register_column_type( new ACP_Addon_MLA_Column_Author() );
		$this->register_column_type( new ACP_Addon_MLA_Column_Attached() );

		// Set up the taxonomy column definitions
		$taxonomies = get_taxonomies( array ( 'show_ui' => true ), 'names' );
		foreach ( $taxonomies as $tax_name ) {
			if ( MLACore::mla_taxonomy_support( $tax_name ) ) {
				$tax_object = get_taxonomy( $tax_name );
				$column = new ACP_Addon_MLA_Column_Taxonomy();
				$column->set_type( 't_' . $tax_object->name );

				$this->register_column_type( $column );
			} // supported taxonomy
		} // foreach $tax_name

		// Set up the custom field definitions
		$custom_columns = MLACore::mla_custom_field_support( 'custom_columns' );
		foreach ( $custom_columns as $type => $label ) {
			$column = new ACP_Addon_MLA_Column_CustomField();
			$column->set_type( $type );
			$column->set_name( $type );
			$column->set_label( $label );

			$this->register_column_type( $column );
		} // foreach custom column
	}

	/**
	 * Return list table item object given ID
	 *
	 * @param integer $id List table item ID
	 *
	 * @since 2.71
	 */
	public static function get_item( $id ) {
		global $wp_list_table;

		if ( $wp_list_table instanceof MLA_List_Table ) {
			foreach ( $wp_list_table->items as $index => $item ) {
				if ( $id == $item->ID ) {
					return $item;
				}
			}
		}

		return get_post( $id );
	}

	/**
	 * Translate post_status 'future', 'pending', 'draft' and 'trash' to label
	 *
	 * @since 2.01
	 * 
	 * @param	string	post_status
	 *
	 * @return	string	Status label or empty string
	 */
	public static function format_post_status( $post_status ) {
		$flag = ', ';
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

} // class ACP_Addon_MLA_ListScreen

/**
 * Exportability class for posts list screen
 *
 * @since 2.71
 */
class ACP_Addon_MLA_Export_Strategy extends ACP\Export\Strategy {

	/**
	 * Call parent constructor
	 *
	 * @param AC_ListScreen $list_screen
	 */
	public function __construct( $list_screen ) {
		parent::__construct( $list_screen );
	}

	/**
	 * Creates and returns the MLA_List_Table object
	 *
	 * @return ListTable
	 */
	protected function get_list_table() {
		global $wp_list_table;

		if ( ! class_exists( 'MLA_List_Table' ) ) {
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-list-table.php' );
			MLA_List_Table::mla_admin_init_action();
		}

		if ( $wp_list_table instanceof MLA_List_Table ) {
			return $wp_list_table;
		}

		$wp_list_table = new MLA_List_Table();
		$wp_list_table->prepare_items();

		return $wp_list_table;
	}

	/**
	 * Retrieve the headers for the columns
	 *
	 * @param AC\Column[] $columns
	 *
	 * @since 1.0
	 * @return string[] Associative array of header labels for the columns.
	 */
	public function get_headers( array $columns ) {
		$headers = parent::get_headers( $columns );

		// Fix the first header to avoid MS Excel SYLK file format error
		foreach ( $headers as $name => $label ) {
			if ( 'ID' === substr( $label, 0, 2) ) {
				$headers[ $name ] = '.' . $label;
			}
			
			break; // Only the first label matters
		}

		return $headers;
	}

	/**
	 * Add hooks for MLA_List_table to support AJAX export operation
	 *
	 * @since 2.71
	 * @see   ACP_Export_ExportableListScreen::ajax_export()
	 */
	protected function ajax_export() {
		// Hooks
		add_filter( 'mla_list_table_query_final_terms', array( $this, 'mla_list_table_query_final_terms' ), 1e6 );
		add_action( 'mla_list_table_prepare_items', array( $this, 'mla_list_table_prepare_items' ), 10, 2 );
	}

	/**
	 * Modify the main posts query to use the correct pagination arguments. This should be attached
	 * to the pre_get_posts hook when an AJAX request is sent
	 *
	 * @param WP_Query $request
	 *
	 * @since 2.71
	 * @see   action:pre_get_posts
	 */
	public function mla_list_table_query_final_terms( $request ) {
		$per_page = $this->get_num_items_per_iteration();
		$request['offset'] = $this->get_export_counter() * $per_page;
		$request['posts_per_page'] = $per_page;
		$request['posts_per_archive_page'] = $per_page;

		return $request;
	}

	/**
	 * Run the actual export when the posts query is finalized. This should be attached to the
	 * the_posts filter when an AJAX request is run
	 *
	 * @param WP_Query $query
	 *
	 * @since 2.71
	 * @see   action:the_posts
	 */
	public function mla_list_table_prepare_items( $query ) {
			$this->export( wp_list_pluck( $query->items, 'ID' ) );
	}
} // class ACP_Addon_MLA_Export_Strategy

/**
 * Class Admin Columns Addon MLA (Media Library Assistant) Editing Strategy supports the Admin Columns plugin
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_Editing_Strategy extends ACP\Editing\Strategy\Post {

	/**
	 * Get the available items on the current page for passing them to JS
	 *
	 * @since 2.71
	 *
	 * @return array Items on the current page ([entry_id] => (array) [entry_data])
	 */
	public function get_rows() {
		global $wp_list_table;

		// Re-execute the query because the table object can be shared with custom plugins using the MLA filters/actions
		$wp_list_table->prepare_items();

		return $this->get_editable_rows( $wp_list_table->items );
	}
} // class ACP_Addon_MLA_Editing_Strategy

/**
 * Provides view_settings for MLA's post_title
 *
 * @package Media Library Assistant
 * @since 2.52
 */
class ACP_Addon_MLA_Editing_Model_Media_Title extends ACP\Editing\Model\Media\Title {

	/**
	 * Remove JavaScript selector settings
	 */
	public function get_view_settings() {
		return array(
			'type'         => 'text',
			'display_ajax' => false,
		);
	}
}

/**
 * Provides export for ID portion of ID/Parent
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_Column_ID_Parent extends AC\Column
	implements ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		// Mark as an existing column
		$this->set_original( true );

		// Type of column
		$this->set_type( 'ID_parent' );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );
		return $item->ID;
	}
}

/**
 * Provides export for Name (slug)
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_Column_Title_Name extends AC\Column
	implements ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'title_name' );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );
		$errors = $item->mla_references['parent_errors'];
		if ( '(' . __( 'NO REFERENCE TESTS', 'media-library-assistant' ) . ')' == $errors ) {
			$errors = '';
		}
		$format = "%1\$s\n%2\$s\n%3\$s";
		return sprintf( $format, _draft_or_post_title( $item ), esc_attr( $item->post_name ), $errors );
	}
}

/**
 * Provides inline-editing, export for post_title
 *
 * @package Media Library Assistant
 * @since 2.52
 * @since 2.71 Added export
 */
class ACP_Addon_MLA_Column_Title extends AC\Column\Media\Title
	implements ACP\Editing\Editable, ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'post_title' );
	}

	/**
	 * Add inline editing support
	 *
	 * @return ACP\Editing\Model\Media\Title
	 */
	public function editing() {
		return new ACP_Addon_MLA_Editing_Model_Media_Title( $this );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );
		return (string) $item->post_title;
	}
}

/**
 * Provides export for Name (slug)
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_Column_Name extends AC\Column
	implements ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'post_name' );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );
		return (string) $item->post_name;
	}
}

/**
 * Removes ACP defaults, provides export for post_parent
 *
 * @package Media Library Assistant
 * @since 2.52
 * @since 2.71 Added export
 */
class ACP_Addon_MLA_Column_Parent extends AC\Column\Media\MediaParent
	implements ACP\Export\Exportable {
	/**
	 * Remove default column width
	 */
	public function register_settings() {
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );
		return (string) $item->post_parent;
	}
}

/**
 * Provides inline-editing, export for menu_order
 *
 * @package Media Library Assistant
 * @since 2.52
 * @since 2.71 Added export
 */
class ACP_Addon_MLA_Column_MenuOrder extends AC\Column
	implements ACP\Editing\Editable, ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'menu_order' );
	}

	/**
	 * Add inline editing support
	 *
	 * @return ACP\Editing\Model\Post\Order
	 */
	public function editing() {
		return new ACP\Editing\Model\Post\Order( $this );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}
	
	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );
		return (string) $item->menu_order;
	}
}

/**
 * Provides export for Featured in
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_Column_Features extends AC\Column
	implements ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'featured' );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		if ( !MLACore::$process_featured_in ) {
			return __( 'Disabled', 'media-library-assistant' );
		}

		$item = ACP_Addon_MLA_ListScreen::get_item( $id );

		// Move parent to the top of the list
		$features = $item->mla_references['features'];
		if ( isset( $features[ $item->post_parent ] ) ) {
			$parent = $features[ $item->post_parent ];
			unset( $features[ $item->post_parent ] );
			array_unshift( $features, $parent );
		}

		$format = "%1\$s (%2\$s %3\$s%4\$s%5\$s),\n";
		$value = '';
		foreach ( $features as $feature ) {
			$status = ACP_Addon_MLA_ListScreen::format_post_status( $feature->post_status );

			if ( $feature->ID == $item->post_parent ) {
				$parent = ", " . __( 'PARENT', 'media-library-assistant' );
			} else {
				$parent = '';
			}

			$value .= sprintf( $format, esc_attr( $feature->post_title ), esc_attr( $feature->post_type ), $feature->ID, $status, $parent );
		} // foreach $feature

		return $value;
	}
}

/**
 * Provides export for Inserted in
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_Column_Inserts extends AC\Column
	implements ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'inserted' );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		if ( !MLACore::$process_inserted_in ) {
			return __( 'Disabled', 'media-library-assistant' );
		}

		$item = ACP_Addon_MLA_ListScreen::get_item( $id );

		$format = "%1\$s (%2\$s %3\$s%4\$s%5\$s),\n";
		$value = '';
		foreach ( $item->mla_references['inserts'] as $file => $inserts ) {
			if ( 'base' != $item->mla_references['inserted_option'] ) {
				$value .= $file . "\n";
			}

			// Move parent to the top of the list
			if ( isset( $inserts[ $item->post_parent ] ) ) {
				$parent = $inserts[ $item->post_parent ];
				unset( $inserts[ $item->post_parent ] );
				array_unshift( $inserts, $parent );
			}

			foreach ( $inserts as $insert ) {
				$status = ACP_Addon_MLA_ListScreen::format_post_status( $insert->post_status );

				if ( $insert->ID == $item->post_parent ) {
					$parent = ", " . __( 'PARENT', 'media-library-assistant' );
				} else {
					$parent = '';
				}

				$value .= sprintf( $format, esc_attr( $insert->post_title ), esc_attr( $insert->post_type ), $insert->ID, $status, $parent );
			} // foreach $insert
		} // foreach $file

		return $value;
	}
}

/**
 * Provides export for Gallery in
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_Column_Galleries extends AC\Column
	implements ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'galleries' );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		if ( !MLACore::$process_gallery_in ) {
			return __( 'Disabled', 'media-library-assistant' );
		}

		$item = ACP_Addon_MLA_ListScreen::get_item( $id );

		// Move parent to the top of the list
		$galleries = $item->mla_references['galleries'];
		if ( isset( $galleries[ $item->post_parent ] ) ) {
			$parent = $galleries[ $item->post_parent ];
			unset( $galleries[ $item->post_parent ] );
			array_unshift( $galleries, $parent );
		}

		$format = "%1\$s (%2\$s %3\$s%4\$s%5\$s),\n";
		$value = '';
		foreach ( $galleries as $ID => $gallery ) {
			$status = ACP_Addon_MLA_ListScreen::format_post_status( $gallery['post_status'] );

			if ( $gallery['ID'] == $item->post_parent ) {
				$parent = ", " . __( 'PARENT', 'media-library-assistant' );
			} else {
				$parent = '';
			}

			$value .= sprintf( $format, esc_attr( $gallery['post_title'] ), esc_attr( $gallery['post_type'] ), $gallery['ID'], $status, $parent );
		} // foreach $gallery

		return $value;
	}
}

/**
 * Provides export for Gallery in
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_Column_MLA_Galleries extends AC\Column
	implements ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'mla_galleries' );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		if ( !MLACore::$process_mla_gallery_in ) {
			return __( 'Disabled', 'media-library-assistant' );
		}

		$item = ACP_Addon_MLA_ListScreen::get_item( $id );

		// Move parent to the top of the list
		$mla_galleries = $item->mla_references['mla_galleries'];
		if ( isset( $mla_galleries[ $item->post_parent ] ) ) {
			$parent = $mla_galleries[ $item->post_parent ];
			unset( $mla_galleries[ $item->post_parent ] );
			array_unshift( $mla_galleries, $parent );
		}

		$format = "%1\$s (%2\$s %3\$s%4\$s%5\$s),\n";
		$value = '';
		foreach ( $mla_galleries as $gallery ) {
			$status = ACP_Addon_MLA_ListScreen::format_post_status( $gallery['post_status'] );

			if ( $gallery['ID'] == $item->post_parent ) {
				$parent = ", " . __( 'PARENT', 'media-library-assistant' );
			} else {
				$parent = '';
			}

			$value .= sprintf( $format, esc_attr( $gallery['post_title'] ), esc_attr( $gallery['post_type'] ), $gallery['ID'], $status, $parent );
		} // foreach $gallery

		return $value;
	}
}

/**
 * Provides inline-editing, export for alt_text
 *
 * @package Media Library Assistant
 * @since 2.52
 * @since 2.71 Added export
 */
class ACP_Addon_MLA_Column_AltText extends ACP\Column\Media\AlternateText
	implements ACP\Editing\Editable, ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'alt_text' );
	}

	/**
	 * Add inline editing support
	 *
	 * @return ACP\Editing\Model\Media\AlternateText
	 */
	public function editing() {
		return new ACP\Editing\Model\Media\AlternateText( $this );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );

		if ( isset( $item->mla_wp_attachment_image_alt ) ) {
			if ( is_array( $item->mla_wp_attachment_image_alt ) ) {
				$alt_text = $item->mla_wp_attachment_image_alt[0];
			} else {
				$alt_text = $item->mla_wp_attachment_image_alt;
			}
			
			return $alt_text;
		}

		return '';
	}
}

/**
 * Provides inline-editing, export for Caption/Excerpt
 *
 * @package Media Library Assistant
 * @since 2.52
 * @since 2.71 Added export
 */
class ACP_Addon_MLA_Column_Caption extends ACP\Column\Media\Caption
	implements ACP\Editing\Editable, ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'caption' );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );
		return (string) $item->post_excerpt;
	}
}

/**
 * Provides inline-editing, export for Description
 *
 * @package Media Library Assistant
 * @since 2.52
 * @since 2.71 Added export
 */
class ACP_Addon_MLA_Column_Description extends AC\Column\Media\Description
	implements ACP\Editing\Editable, ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'description' );
	}

	/**
	 * Add inline editing and provides export for Description
	 *
	 * @return ACP\Editing\Model\Post\Content
	 */
	public function editing() {
		return new ACP\Editing\Model\Post\Content( $this );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}
	
	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );
		return (string) $item->post_content;
	}
}

/**
 * Provides inline-editing, export for MIME Type
 *
 * @package Media Library Assistant
 * @since 2.52
 * @since 2.71 Added export
 */
class ACP_Addon_MLA_Column_MimeType extends AC\Column\Media\MimeType
	implements ACP\Editing\Editable, ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'post_mime_type' );
	}

	/**
	 * Add inline editing support
	 *
	 * @return ACP\Editing\Model\Media\MimeType
	 */
	public function editing() {
		return new ACP\Editing\Model\Media\MimeType( $this );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}
	
	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );
		return (string) $item->post_mime_type;
	}
}

/**
 * Provides export for File URL
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_Column_FileURL extends AC\Column
	implements ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'file_url' );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$attachment_url = wp_get_attachment_url( $id );
		return $attachment_url ? $attachment_url : __( 'None', 'media-library-assistant' );
	}
}

/**
 * Provides export for Base File
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_Column_Base_File extends AC\Column
	implements ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'base_file' );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );
		if ( isset( $item->mla_wp_attached_file ) ) {
			return (string) $item->mla_wp_attached_file;
		}

		return 'ERROR';
	}
}

/**
 * Provides export for Attached to
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_Column_Attached extends AC\Column
	implements ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'attached_to' );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );

		if ( isset( $item->parent_title ) ) {
			$parent_type = get_post_type_object( $item->parent_type );
			if ( $parent_type ) {
				if (  $parent_type->show_ui ) {
					$user_can_read_parent = current_user_can( 'read_post', $item->post_parent );
				} else {
					$user_can_read_parent = true;
				}
			} else {
				$user_can_read_parent = false;
			}

			if ( $user_can_read_parent ) {
				$parent_title = esc_attr( $item->parent_title );
			} else {
				$parent_title = __( '(Private post)' );
			}

			if ( isset( $item->parent_date ) && $user_can_read_parent ) {
				$parent_date = "\n" . mysql2date( __( 'Y/m/d', 'media-library-assistant' ), $item->parent_date );
			} else {
				$parent_date = '';
			}

			if ( isset( $item->parent_type ) && $user_can_read_parent ) {
				$parent_type = "\n" . '(' . $item->parent_type . ' ' . (string) $item->post_parent . ACP_Addon_MLA_ListScreen::format_post_status( $item->parent_status ) . ')';
			} else {
				$parent_type = '';
			}

			$parent =  sprintf( '%1$s%2$s%3$s', /*%1$s*/ $parent_title, /*%2$s*/ $parent_date, /*%3$s*/ $parent_type );
		} else {
			$parent = '(' . _x( 'Unattached', 'table_view_singular', 'media-library-assistant' ) . ')';
		}

		return $parent;
	}
}

/**
 * Removes ACP defaults and provides export for date
 *
 * @package Media Library Assistant
 * @since 2.52
 * @since 2.71 Added export
 */
class ACP_Addon_MLA_Column_Date extends ACP\Column\Media\Date
	implements ACP\Export\Exportable {

	/**
	 * Remove default column width
	 */
	public function register_settings() {
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );
		return (string) $item->post_date;
	}
}

/**
 * Provides export for Last Modified date
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_Column_Modified extends AC\Column
	implements ACP\Export\Exportable {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'modified' );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );
		return (string) $item->post_modified;
	}
}

/**
 * Removes ACP defaults & provides inline-editing, export for Author name
 *
 * @package Media Library Assistant
 * @since 2.52
 * @since 2.71 Added export
 */
class ACP_Addon_MLA_Column_Author extends AC\Column\Media\Author
	implements ACP\Editing\Editable, ACP\Export\Exportable {

	/**
	 * Remove default column width
	 */
	public function register_settings() {
	}

	/**
	 * Add inline editing support
	 *
	 * @return ACP\Editing\Model\Post\Author
	 */
	public function editing() {
		return new ACP\Editing\Model\Post\Author( $this );
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );
		$user = get_user_by( 'id', $item->post_author );

		if ( isset( $user->data->display_name ) ) {
			return $user->data->display_name;
		}

		return 'unknown';
	}
}

/**
 * Provides export for supported taxonomies
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_Column_Taxonomy extends AC\Column
	implements ACP\Export\Exportable {

	/**
	 * Define column properties
	 *
	 * set_type( 't_' . taxonomy ) is done by the calling function.
	 */
	public function __construct() {
		$this->set_original( true );
	}

	/**
	 * Extract taxonomy slug from type name
	 *
	 * @return string Taxonomy name/slug
	 */
	public function get_taxonomy() {
		return substr( $this->get_type(), 2 );
	}

	/**
	 * Does this post type have registered taxonomies
	 *
	 * @return bool True when post type has associated taxonomies
	 */
	public function is_valid() {
		return true;
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		$terms = wp_get_post_terms( $id, $this->get_taxonomy(), array( 'fields' => 'names' ) );

		if ( ! $terms || is_wp_error( $terms ) ) {
			return '';
		}

		return implode( ', ', $terms );
	}
}

/**
 * Provides export for supported custom fields
 *
 * @package Media Library Assistant
 * @since 2.71
 */
class ACP_Addon_MLA_Column_CustomField extends AC\Column
	implements ACP\Export\Exportable {

	/**
	 * Define column properties
	 *
	 * set_type( 'c_' . field number ) is done by the calling function.
	 */
	public function __construct() {
		$this->set_original( true );
	}

	/**
	 * Does this post type support custom fields
	 *
	 * @return bool True when post type has custom fields
	 */
	public function is_valid() {
		return true;
	}

	/**
	 * Support export
	 */
	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	/**
	 * Supply value for export
	 *
	 * @param integer $id MLA_List_table item ID
	 */
	public function get_raw_value( $id ) {
		static $custom_columns = NULL;
		
		if ( NULL === $custom_columns ) {
			$custom_columns = MLACore::mla_custom_field_support( 'custom_columns' );
		}

		$column_name = $this->get_name();
		$item = ACP_Addon_MLA_ListScreen::get_item( $id );

		if ( 'meta:' == substr( $custom_columns[ $column_name ], 0, 5 ) ) {
			$is_meta = true;
			$meta_key = substr( $custom_columns[ $column_name ], 5 );

			if ( !empty( $item->mla_wp_attachment_metadata ) ) {
				$values = MLAData::mla_find_array_element( $meta_key, $item->mla_wp_attachment_metadata, 'array' );

				if ( is_scalar( $values ) ) {
					$values = array( $values );
				}
			} else {
				$values = NULL;
			}
		} else {
			$is_meta = false;
			$values = get_post_meta( $item->ID, $custom_columns[ $column_name ], false );
		}

		if ( empty( $values ) ) {
			return '';
		}

		$list = array();
		foreach ( $values as $index => $value ) {
			/*
			 * For display purposes, convert array values.
			 * Use "@" because embedded arrays throw PHP Warnings from implode.
			 */
			if ( is_array( $value ) ) {
				$list[] = 'array( ' . @implode( ', ', $value ) . ' )'; // TODO PHP 7 error handling
			} elseif ( $is_meta ) {
				$list[] = $value;
			} else {
				$list[] = esc_html( $value );
			}
		}

		if ( count( $list ) > 1 ) {
			return '[' . join( '], [', $list ) . ']';
		} else {
			return $list[0];
		}
	}
}
?>
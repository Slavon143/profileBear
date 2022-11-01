<?php
/**
 * Media Library Assistant Admin Columns Pro (plugin) Support
 *
 * @package Media Library Assistant
 * @since 2.50
 */
defined( 'ABSPATH' ) or die();

/**
 * Class Admin Columns Addon MLA (Media Library Assistant) List Screen supports the Admin Columns plugin
 *
 * @package Media Library Assistant
 * @since 2.50
 */
class ACP_Addon_MLA_ListScreen extends AC_Addon_MLA_ListScreen 
implements ACP_Editing_ListScreen {

	/**
	 * Initializes some properties, installs filters and then
	 * calls the parent constructor to set some default configs.
	 *
	 * @since 2.50
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'ac/column_types', 'ACP_Addon_MLA_ListScreen::inline_column_types', 10, 1 );
	}

	/**
	 * Add inline editing columns to Media/Assistant submenu table
	 *
	 * @since 2.52
	 *
	 * @param AC_ListScreen $listscreen
	 */
	public static function inline_column_types( $listscreen ) {
		$listscreen->register_column_type( new ACP_Addon_MLA_Column_Title() );
		$listscreen->register_column_type( new ACP_Addon_MLA_Column_Parent() );
		$listscreen->register_column_type( new ACP_Addon_MLA_Column_MenuOrder() );
		$listscreen->register_column_type( new ACP_Addon_MLA_Column_AltText() );
		$listscreen->register_column_type( new ACP_Addon_MLA_Column_Caption() );
		$listscreen->register_column_type( new ACP_Addon_MLA_Column_Description() );
		$listscreen->register_column_type( new ACP_Addon_MLA_Column_MimeType() );
		$listscreen->register_column_type( new ACP_Addon_MLA_Column_Date() );
		$listscreen->register_column_type( new ACP_Addon_MLA_Column_Author() );
	}

	/**
	 * Set MLA-specific inline editing strategy for Admin Columns Pro
	 *
	 * @since 2.71
	 *
	 * @param ACP_Editing_Model $model
	 */
	public function editing( $model ) {
		return new ACP_Addon_MLA_Editing_Strategy( $model );
	}
} // class ACP_Addon_MLA_ListScreen

/**
 * Class Admin Columns Addon MLA (Media Library Assistant) Editing Strategy supports the Admin Columns plugin
 *
 * @package Media Library Assistant
 * @since 2.50
 */
class ACP_Addon_MLA_Editing_Strategy extends ACP_Editing_Strategy_Post {

	/**
	 * Get the available items on the current page for passing them to JS
	 *
	 * @since 2.50
	 *
	 * @return array Items on the current page ([entry_id] => (array) [entry_data])
	 */
	public function get_rows() {
		$table = $this->get_column()->get_list_screen()->get_list_table();
		$table->prepare_items();

		return $this->get_editable_rows( $table->items );
	}
} // class ACP_Addon_MLA_Editing_Strategy

/**
 * Provides view_settings for MLA's post_title
 *
 * @package Media Library Assistant
 * @since 2.52
 */
class ACP_Addon_MLA_Editing_Model_Media_Title extends ACP_Editing_Model_Media_Title {

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
 * Provides inline-editing for post_title
 *
 * @package Media Library Assistant
 * @since 2.52
 */
class ACP_Addon_MLA_Column_Title extends AC_Column_Media_Title
	implements ACP_Column_EditingInterface {

	/**
	 * Define column properties
	 */
	public function __construct() {

		// Mark as an existing column
		$this->set_original( true );

		// Type of column
		$this->set_type( 'post_title' );
	}

	/**
	 * Add inline editing support
	 *
	 * @return ACP_Editing_Model_Media_Title
	 */
	public function editing() {
		return new ACP_Addon_MLA_Editing_Model_Media_Title( $this );
	}
}

/**
 * Removes ACP defaults for parent
 *
 * @package Media Library Assistant
 * @since 2.52
 */
class ACP_Addon_MLA_Column_Parent extends AC_Column_Media_Parent {
	/**
	 * Remove default column width
	 */
	public function register_settings() {
	}
}

/**
 * Provides inline-editing for menu_order
 *
 * @package Media Library Assistant
 * @since 2.52
 */
class ACP_Addon_MLA_Column_MenuOrder extends AC_Column
	implements ACP_Column_EditingInterface {

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
	 * @return ACP_Editing_Model_Post_Order
	 */
	public function editing() {
		return new ACP_Editing_Model_Post_Order( $this );
	}

}

/**
 * Provides inline-editing for alt_text
 *
 * @package Media Library Assistant
 * @since 2.52
 */
class ACP_Addon_MLA_Column_AltText extends ACP_Column_Media_AlternateText
	implements ACP_Column_EditingInterface {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'alt_text' );
	}
}

/**
 * Provides inline-editing for caption
 *
 * @package Media Library Assistant
 * @since 2.52
 */
class ACP_Addon_MLA_Column_Caption extends ACP_Column_Media_Caption
	implements ACP_Column_EditingInterface {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'caption' );
	}
}

/**
 * Provides inline-editing for caption
 *
 * @package Media Library Assistant
 * @since 2.52
 */
class ACP_Addon_MLA_Column_Description extends AC_Column_Media_Description
	implements ACP_Column_EditingInterface {

	/**
	 * Define column properties
	 */
	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'description' );
	}

	/**
	 * Add inline editing support
	 *
	 * @return ACP_Editing_Model_Post_Content
	 */
	public function editing() {
		return new ACP_Editing_Model_Post_Content( $this );
	}
}

/**
 * Provides inline-editing for caption
 *
 * @package Media Library Assistant
 * @since 2.52
 */
class ACP_Addon_MLA_Column_MimeType extends AC_Column_Media_MimeType
	implements ACP_Column_EditingInterface {

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
	 * @return ACP_Editing_Model_Post_Content
	 */
	public function editing() {
		return new ACP_Editing_Model_Media_MimeType( $this );
	}
}

/**
 * Removes ACP defaults for date
 *
 * @package Media Library Assistant
 * @since 2.52
 */
class ACP_Addon_MLA_Column_Date extends ACP_Column_Media_Date {
	/**
	 * Remove default column width
	 */
	public function register_settings() {
	}
}

/**
 * Removes ACP defaults & provides inline-editing for caption
 *
 * @package Media Library Assistant
 * @since 2.52
 */
class ACP_Addon_MLA_Column_Author extends AC_Column_Media_Author
	implements ACP_Column_EditingInterface {

	/**
	 * Remove default column width
	 */
	public function register_settings() {
	}

	/**
	 * Add inline editing support
	 *
	 * @return ACP_Editing_Model_Post_Content
	 */
	public function editing() {
		return new ACP_Editing_Model_Post_Author( $this );
	}
}

<?php
/**
 * Media Library Assistant Admin Columns (plugin) Support
 *
 * @package Media Library Assistant
 * @since 2.50
 */
defined( 'ABSPATH' ) or die();

// Accomodate class namespace introduction in Admin Columns 3.2.x
if ( class_exists( 'AC\ListScreen\Media' ) ) {
	/**
	 * Class Admin Columns List Screen Stub for Admin Columns 3.2.x+
	 */
	class AC_Addon_MLA_ListScreen_Stub extends AC\ListScreen\Media {
	}
} else {
	/**
	 * Class Admin Columns List Screen Stub for Admin Columns 3.1.x-
	 */
	class AC_Addon_MLA_ListScreen_Stub extends AC_ListScreen_Media {
	}
}

/**
 * Class Admin Columns Addon MLA (Media Library Assistant) List Screen supports the Admin Columns plugin
 *
 * @package Media Library Assistant
 * @since 2.50
 */
class AC_Addon_MLA_ListScreen extends AC_Addon_MLA_ListScreen_Stub {

	/**
	 * Initializes some properties, installs filters and then
	 * calls the parent constructor to set some default configs.
	 *
	 * @since 2.50
	 */
	public function __construct() {
		parent::__construct();

		$this->set_key( 'mla-media-assistant' );
		$this->set_label( __( 'Media Library Assistant' ) );
		$this->set_singular_label( __( 'Assistant' ) );
		$this->set_screen_id( 'media_page_' . MLACore::ADMIN_PAGE_SLUG );
		$this->set_page( MLACore::ADMIN_PAGE_SLUG );

		add_filter( 'ac/column/custom_field/meta_keys', 'AC_Addon_MLA_ListScreen::remove_custom_columns', 10, 1 );
	}

	/**
	 * Contains the hook that contains the manage_value callback
	 *
	 * @since 2.50
	 */
	public function set_manage_value_callback() {
		add_filter( 'mla_list_table_column_default', array( $this, 'column_default_value' ), 100, 3 );
	}

	/**
	 * Create and return a new MLA List Table object
	 *
	 * @param array $args
	 *
	 * @return WP_List_Table|false
	 */
	public function get_list_table( $args = array() ) {
		global $wp_list_table;
		
		if ( ! class_exists( 'MLA_List_Table' ) ) {
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-list-table.php' );
			MLA_List_Table::mla_admin_init_action();
		}

		if ( $wp_list_table instanceof MLA_List_Table ) {
			return $wp_list_table;
		}
		
		$list_table = new MLA_List_Table();
		$list_table->prepare_items();
		
		// Don't show the Export button before 4.2.3
		if ( function_exists( 'ACP' ) && version_compare( ACP()->get_version(), '4.2.3', '<' ) ) {
			$wp_list_table = NULL;
		}
		
		return $list_table;
	}

	/**
	 * Test for current screen = the Media/Assistant submenu screen,
	 * For Admin Columns 2.4.9+
	 *
	 * @since 2.23
	 *
	 * @param object $wp_screen
	 *
	 * @return boolean true if the Media/Assistant submenu is the current screen
	 */
	public function is_current_screen( $wp_screen ) {
		return $wp_screen && $wp_screen->id === $this->get_screen_id();
	}

	/**
	 * Remove duplicate columns from the Admin Columns "Custom" section
	 *
	 * @since 2.50
	 *
	 * @param AC_ListScreen $listscreen
	 */
	public function register_column_types() {
		parent::register_column_types();
//error_log( __LINE__ . ' AC_Addon_MLA_ListScreen::register_column_types ' . var_export( array_keys( $this->get_column_types() ), true ), 0 );

		$exclude = array(
			'comments',
			'title',
			'column-actions',
			'column-alternate_text',
			'column-attached_to',
			'column-author_name',
			'column-caption',
			'column-description',
			'column-file_name',
			'column-full_path',
			'column-mediaid',
			'column-mime_type',
			'column-taxonomy',

			/*
			'column-meta',
			'column-available_sizes',
			'column-dimensions',
			'column-exif_data',
			'column-file_size',
			'column-height',
			'column-image',
			'column-used_by_menu',
			'column-width',
			 */
		);

		foreach ( $exclude as $column_type ) {
			$this->deregister_column_type( $column_type );
		}
	}

	/**
	 * Remove duplicate columns from the Admin Columns "Custom" section
	 *
	 * @since 2.52
	 *
	 * @param array $keys Distinct meta keys from DB
	 */
	public static function remove_custom_columns( $keys ) {
		// Find the fields already present in the submenu table
		$mla_columns = apply_filters( 'mla_list_table_get_columns', MLAQuery::$default_columns );
		$mla_custom = array();
		foreach ( $mla_columns as $slug => $heading ) {
			if ( 'c_' === substr( $slug, 0, 2 ) ) {
				$mla_custom[] = $heading;
			}
		}

		// Remove the fields already present in the submenu table
		foreach ( $keys as $index => $value ) {
			if ( in_array( esc_html( $value ), $mla_custom ) ) {
				unset( $keys[ $index ] );
			}
		}

		return $keys;
	}

	/**
	 * Default column headers
	 *
	 * @since 2.50
	 *
	 * @return array
	 */
	public function get_column_headers() {
		if ( ! class_exists( 'MLAQuery' ) ) {
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-data-query.php' );
			MLAQuery::initialize();
		}

		return apply_filters( 'mla_list_table_get_columns', MLAQuery::$default_columns );
	}

	/**
	 * Return the column value for AC/ACP custom columns, e.g., EXIF values
	 *
	 * @param string|null $content
	 * @param WP_Post $post
	 * @param string $column_name
	 *
	 * @return string|false
	 */
	public function column_default_value( $content, $post, $column_name ) {
		if ( is_null( $content ) ) {
			$content = $this->get_display_value_by_column_name( $column_name, $post->ID );
		}

		return $content;
	}

	/**
	 * Return an MLA version of a Media Library item
	 *
	 * @since 2.71
	 *
	 * @param integer $post_id
	 *
	 * @return object attachment object
	 */
	public function get_object( $post_id ) {
		// Author column depends on this global to be set.
		global $authordata;

		$authordata = get_userdata( get_post_field( 'post_author', $post_id ) );

		if ( ! class_exists( 'MLAData' ) ) {
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-data.php' );
			MLAData::initialize();
		}

		return (object) MLAData::mla_get_attachment_by_id( $post_id );
	}

	/**
	 * Return an MLA version of a Media Library item for older Admin Columns versions
	 *
	 * @since 2.52
	 *
	 * @param integer $post_id
	 *
	 * @return object attachment object
	 */
	protected function get_object_by_id( $post_id ) {
		return $this->get_object( $post_id );
	}
} // class AC_Addon_MLA_ListScreen

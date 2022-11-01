<?php
/**
 * Top-level functions for the Media Library Assistant
 *
 * @package Media Library Assistant
 * @since 0.1
 */

// The Meta Boxes functions are't automatically available to plugins.
if ( !function_exists( 'post_categories_meta_box' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/meta-boxes.php' );
}

/**
 * Class MLA (Media Library Assistant) provides several enhancements to the handling
 * of images and files held in the WordPress Media Library.
 *
 * @package Media Library Assistant
 * @since 0.1
 */
class MLA {
	/**
	 * Object name for localizing JavaScript - MLA List Table
	 *
	 * @since 0.20
	 *
	 * @var	string
	 */
	const JAVASCRIPT_INLINE_EDIT_OBJECT = 'mla_inline_edit_vars';

	/**
	 * Holds screen ids to match help text to corresponding screen
	 *
	 * @since 0.1
	 *
	 * @var	array
	 */
	private static $page_hooks = array();

	/**
	 * Accumulates error messages from name conflict tests
	 *
	 * @since 1.14
	 */
	private static $mla_language_support_error_messages = '';
	 
	/**
	 * Displays name conflict error messages at the top of the Dashboard
	 *
	 * @since 2.11
	 */
	public static function mla_name_conflict_reporting_action () {
		$messages = self::$mla_language_support_error_messages;

		echo '<div class="error"><p><strong>The Media Library Assistant cannot activate multi-language support.</strong> Another plugin or theme has declared conflicting class, function or constant names:</p>'."\r\n";
		echo "<ul>" . esc_html( $messages ) . "</ul>\r\n";
		echo '<p>You must resolve these conflicts before multi-language support can be activated.</p></div>'."\r\n";
	}

	/**
	 * Initialization function, similar to __construct()
	 *
	 * This function contains add_action and add_filter calls
	 * to set up the Ajax handlers, enqueue JavaScript and CSS files, and 
	 * set up the Assistant submenu.
	 *
	 * @since 0.1
	 *
	 * @return	void
	 */
	public static function initialize( ) {
		global $sitepress, $polylang;

		if ( 'checked' == MLACore::mla_get_option( 'enable_featured_image_generation' ) ) {
			if ( class_exists( 'MLA_Thumbnail' ) ) {
				self::$mla_language_support_error_messages .= "<li>class MLA_Thumbnail</li>";
			} else {
				require_once( MLA_PLUGIN_PATH . 'includes/class-mla-thumbnail-generation.php' );
				MLA_Thumbnail::initialize();
			}
		}

		/*
		 * Check for WPML/Polylang presence before loading language support class,
		 * then immediately initialize it since we're already in the "init" action.
		 */
		if ( is_object( $sitepress ) ) {
			if ( class_exists( 'MLA_WPML' ) ) {
				self::$mla_language_support_error_messages .= "<li>class MLA_WPML</li>";
			}

			if ( class_exists( 'MLA_WPML_List_Table' ) ) {
				self::$mla_language_support_error_messages .= "<li>class MLA_WPML_List_Table</li>";
			}

			if ( class_exists( 'MLA_WPML_Table' ) ) {
				self::$mla_language_support_error_messages .= "<li>class MLA_WPML_Table</li>";
			}

			if ( empty( self::$mla_language_support_error_messages ) ) {
				require_once( MLA_PLUGIN_PATH . 'includes/class-mla-wpml-support.php' );
				MLA_WPML::initialize();
			}
		} elseif ( is_object( $polylang ) ) {
			if ( class_exists( 'MLA_Polylang' ) ) {
				self::$mla_language_support_error_messages .= "<li>class MLA_Polylang</li>";
			}

			if ( empty( self::$mla_language_support_error_messages ) ) {
				require_once( MLA_PLUGIN_PATH . 'includes/class-mla-polylang-support.php' );
				MLA_Polylang::initialize();
			}
		}

		if ( ! empty( self::$mla_language_support_error_messages ) ) {
			add_action( 'admin_notices', 'MLA::mla_name_conflict_reporting_action' );
		}

		add_action( 'admin_init', 'MLA::mla_admin_init_action' );
		add_action( 'admin_enqueue_scripts', 'MLA::mla_admin_enqueue_scripts_action' );
		add_action( 'admin_menu', 'MLA::mla_admin_menu_action' );
		add_filter( 'set-screen-option', 'MLA::mla_set_screen_option_filter', 10, 3 ); // $status, $option, $value
		add_filter( 'set_screen_option_' . MLA_OPTION_PREFIX . 'entries_per_page', 'MLA::mla_set_screen_option_filter', 10, 3 );
		add_filter( 'screen_options_show_screen', 'MLA::mla_screen_options_show_screen_filter', 10, 2 ); // $show_screen, $this
	}

	/**
	 * Load the plugin's Ajax handler or process Edit Media update actions
	 *
	 * @since 0.20
	 *
	 * @return	void
	 */
	public static function mla_admin_init_action() {
		//static $count = 0;
		//error_log( __LINE__ . ' MLA::mla_admin_init_action $count = ' . var_export( $count++, true ), 0 );
		//error_log( __LINE__ . ' MLA::mla_admin_init_action referer = ' . var_export( wp_get_referer(), true ), 0 );
		//error_log( __LINE__ . ' MLA::mla_admin_init_action $_REQUEST = ' . var_export( $_REQUEST, true ), 0 );
		//error_log( __LINE__ . ' MLA::mla_admin_init_action $_POST = ' . var_export( $_POST, true ), 0 );
		//error_log( __LINE__ . ' MLA::mla_admin_init_action $_GET = ' . var_export( $_GET, true ), 0 );

		// Process secure file download requests
		if ( isset( $_REQUEST['mla_download_file'] ) && isset( $_REQUEST['mla_download_type'] ) ) {
			check_admin_referer( MLACore::MLA_DOWNLOAD_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );
			self::_process_mla_download_file( $_REQUEST, true );
			exit();
		}

		// Process example plugin download requests from the Documentation tab
		if ( isset( $_REQUEST['mla_download_example_plugin'] ) ) {
			check_admin_referer( MLACore::MLA_DOWNLOAD_EXAMPLE_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );

			$request = array (
				'mla_download_file' => str_replace( '\\', '/', MLA_PLUGIN_PATH . 'examples/plugins/' . sanitize_text_field( wp_unslash( $_REQUEST['mla_download_example_plugin'] ) ) ),
				'mla_download_type' => 'text/plain',
			);

			self::_process_mla_download_file( $request, false );
			exit();
		}

		// Process error log download requests from the Debug tab
		if ( isset( $_REQUEST['mla_download_error_log'] ) ) {
			check_admin_referer( MLACore::MLA_ERROR_LOG_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );

			// Find the appropriate error log file
			$error_log_name = MLACore::mla_get_option( MLACoreOptions::MLA_DEBUG_FILE );
			if ( empty( $error_log_name ) ) {
				$error_log_name =  ini_get( 'error_log' );
			} else {
				$first = substr( $error_log_name, 0, 1 );
				if ( ( '/' != $first ) && ( '\\' != $first ) ) {
					$error_log_name = '/' . $error_log_name;
				}

				$error_log_name = WP_CONTENT_DIR . $error_log_name;
			}

			if ( file_exists ( $error_log_name ) ) {
				$request = array (
					'mla_download_file' => addslashes( $error_log_name ),
					'mla_download_type' => 'text/plain',
				);

				self::_process_mla_download_file( $request, false );
			}

			exit();
		}

		$bulk_action = '';
		if ( isset( $_REQUEST['action'] ) && 'download-zip' == $_REQUEST['action']) {
			$bulk_action = 'download-zip';
		} elseif ( isset( $_REQUEST['action2'] ) && 'download-zip' == $_REQUEST['action2']) {
			$bulk_action = 'download-zip';
		}

		if ( 'download-zip' === $bulk_action ) {
			check_admin_referer( 'bulk-attachments' );
			// Exits after redirect unless it returns an error
			$_REQUEST['mla_zip_archive_error_message'] =  self::_process_zip_archive_download( $_REQUEST );
			MLACore::mla_debug_add( __LINE__ . " MLA::_process_zip_archive_download message = " . var_export( $_REQUEST['mla_zip_archive_error_message'], true ), MLACore::MLA_DEBUG_CATEGORY_ANY ); // phpcs:ignore
			return;
		}

		// Process row-level actions from the Edit Media screen
		if ( !empty( $_REQUEST['mla_admin_action'] ) ) {
			if ( isset( $_REQUEST['mla-set-parent-ajax-nonce'] ) ) {
				check_admin_referer( MLACore::JAVASCRIPT_FIND_POSTS_SLUG, 'mla-set-parent-ajax-nonce' );
			} else {
				check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );
			}

			$mla_item_id = isset( $_REQUEST['mla_item_ID'] ) ? absint( $_REQUEST['mla_item_ID'] ) : 0;
			if ( apply_filters( 'mla_list_table_admin_action', true, sanitize_text_field( wp_unslash( $_REQUEST['mla_admin_action'] ) ), $mla_item_id ) ) {
				switch ( $_REQUEST['mla_admin_action'] ) {
					case MLACore::MLA_ADMIN_SINGLE_CUSTOM_FIELD_MAP:
						if ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ALLOW_CUSTOM_FIELD_MAPPING ) ) {
							do_action( 'mla_begin_mapping', 'single_custom', $mla_item_id );
							$updates = MLAOptions::mla_evaluate_custom_field_mapping( $mla_item_id, 'single_attachment_mapping' );
							do_action( 'mla_end_mapping' );

							if ( !empty( $updates ) ) {
								$item_content = MLAData::mla_update_single_item( $mla_item_id, $updates );
							}

							$message = '101';
						} else {
							$message = '103';
						}

						$view_args = isset( $_REQUEST['mla_source'] ) ? array( 'mla_source' => sanitize_text_field( wp_unslash( $_REQUEST['mla_source'] ) ) ) : array();
						wp_redirect( add_query_arg( $view_args, admin_url( 'post.php' ) . '?post=' . $mla_item_id . '&action=edit&message=' . $message ), 302 );
						exit;
					case MLACore::MLA_ADMIN_SINGLE_MAP:
						if ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ALLOW_IPTC_EXIF_MAPPING ) ) {
							$item = get_post( $mla_item_id );
							do_action( 'mla_begin_mapping', 'single_iptc_exif', $mla_item_id );
							$updates = MLAOptions::mla_evaluate_iptc_exif_mapping( $item, 'iptc_exif_mapping' );
							do_action( 'mla_end_mapping' );
							$page_content = MLAData::mla_update_single_item( $mla_item_id, $updates );
							$message = '102';
						} else {
							$message = '104';
						}

						$view_args = isset( $_REQUEST['mla_source'] ) ? array( 'mla_source' => sanitize_text_field( wp_unslash( $_REQUEST['mla_source'] ) ) ) : array();
						wp_redirect( add_query_arg( $view_args, admin_url( 'post.php' ) . '?post=' . $mla_item_id . '&action=edit&message=' . $message ), 302 );
						exit;
					default:
						do_action( 'mla_list_table_custom_admin_action', sanitize_text_field( wp_unslash( $_REQUEST['mla_admin_action'] ) ), $mla_item_id );
						// ignore the rest
				} // switch ($_REQUEST['mla_admin_action'])
			} // apply_filters mla_list_table_admin_action
		} // (!empty($_REQUEST['mla_admin_action'])

		if ( is_admin() ) {
			if ( defined('DOING_AJAX') && DOING_AJAX ) {
				add_action( 'wp_ajax_' . MLACore::JAVASCRIPT_INLINE_EDIT_SLUG, 'MLA::mla_inline_edit_ajax_action' );
			}
		}
	}

	/**
	 * Print optional in-lne styles for Media/Assistant submenu table
	 *
	 * @since 2.13
	 */
	public static function mla_admin_print_styles_action() {
		echo "<style type='text/css'>\n";

		// Optional - limit width of the views list
		$width_value = MLACore::mla_get_option( MLACoreOptions::MLA_TABLE_VIEWS_WIDTH );
		if ( !empty( $width_value ) ) {
			if ( is_numeric( $width_value ) ) {
				$width_value .= 'px';
			}

			echo "  ul.subsubsub {\n";
			echo "    width: " . esc_html( $width_value ) . ";\n";
			echo "    max-width: " . esc_html( $width_value ) . ";\n";
			echo "  }\n";
		}

		echo "  img.mla_media_thumbnail {\n";

		// Optional - change the size of the thumbnail/icon images
		$icon_value = MLACore::mla_get_option( MLACoreOptions::MLA_TABLE_ICON_SIZE );
		$set_column_width = !empty( $icon_value ) && is_numeric( $icon_value ) && ( 64 < $icon_value );

		if ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ENABLE_MLA_ICONS ) ) {
			if ( empty( $icon_value ) ) {
				$icon_value = 64;
			} else {
				if ( is_numeric( $icon_value ) ) {
					$icon_value = absint( $icon_value );
				}
			}

			$icon_width = $icon_height = $icon_value . 'px';

			echo "    height: auto;\n";
			echo "    max-width: " . esc_html( $icon_width ) . ";\n";
			echo "    max-height: " . esc_html( $icon_height ) . ";\n";
		} else {
			if ( empty( $icon_value ) ) {
				if ( MLATest::$wp_4dot3_plus ) {
					$icon_value = 60;
				} else {
					$icon_value = 80;
				}
			}

			$icon_width = absint( $icon_value ) . 'px';
			echo "    max-width: " . esc_html( $icon_width ) . ";\n";

			if ( MLATest::$wp_4dot3_plus ) {
				echo "    max-height: auto;\n";
			} else {
				$icon_height = ( absint( .75 * (float) $icon_value ) ) . 'px';
				echo "    max-height: " . esc_html( $icon_height ) . ";\n";
			}
		}

		echo "  }\n";

		if ( MLATest::$wp_4dot3_plus ) {
			// Emulate WordPress styles in list-tables.css
			echo "  table.attachments .column-primary strong {\n";
			echo "    display: block;\n";
			echo "    margin-bottom: .2em;\n";
			echo "    font-size: 14px;\n";
			echo "  }\n";

			echo "  table.attachments .column-primary .media-icon {\n";
			echo "    float: left;\n";
			echo "    margin: 0 9px 0 0;\n";
			echo "    font-size: 14px;\n";
			echo "  }\n";

			echo "  table.attachments .column-primary div.row-actions {\n";
			echo "    clear: both;\n";
			echo "  }\n";

			// Explicit primary column width including icon and some margin
			if ( $set_column_width ) {
				$column_width = ( $icon_value + 30 ) . 'px';

				echo "  table.attachments th.column-primary {\n";
				echo "    width: " . esc_html( $column_width ) . ";\n";
				echo "  }\n";
			}

			echo "  table.attachments td.column-primary {\n";
			echo "    position: relative;\n";

			if ( $set_column_width ) {
				echo "    width: " . esc_html( $column_width ) . ";\n";
			}

			echo "  }\n";
		} else {
			/*
			 * Override defaults in /wp-admin/load-styles.php
			 */
			echo "  .fixed td.column-icon, .fixed th.column-icon {\n";
			echo "    width: " . esc_html( $icon_width ) . ";\n";
			echo "  }\n";

			/*
			 * Separate ID_parent column
			 */
			echo "  td.column-ID_parent, th.column-ID_parent {\n";
			echo "  	width: 100px;\n";
			echo "  	max-width: 100px;\n";
			echo "  }\n";
		}

		echo "</style>\n";
	}

	/**
	 * Load the plugin's Style Sheet and Javascript files
	 *
	 * @since 0.1
	 *
	 * @param	string	Name of the page being loaded
	 *
	 * @return	void
	 */
	public static function mla_admin_enqueue_scripts_action( $page_hook ) {
		global $wp_locale;

		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		if ( 'checked' != MLACore::mla_get_option( MLACoreOptions::MLA_SCREEN_DISPLAY_LIBRARY ) ) {
			wp_register_style( MLACore::STYLESHEET_SLUG . '-nolibrary', MLA_PLUGIN_URL . 'css/mla-nolibrary.css', false, MLACore::mla_script_version() );
			wp_enqueue_style( MLACore::STYLESHEET_SLUG . '-nolibrary' );
		}

		if ( 'edit-tags.php' == $page_hook ) {
			wp_register_style( MLACore::STYLESHEET_SLUG, MLA_PLUGIN_URL . 'css/mla-edit-tags-style.css', false, MLACore::mla_script_version() );
			wp_enqueue_style( MLACore::STYLESHEET_SLUG );
			return;
		}

		if ( 'media_page_' . MLACore::ADMIN_PAGE_SLUG != $page_hook ) {
			return;
		}

		// Add the styles for variable-size icon and WP 4.3 primary column display 
		add_action( 'admin_print_styles', 'MLA::mla_admin_print_styles_action' );

		if ( $wp_locale->is_rtl() ) {
			wp_register_style( MLACore::STYLESHEET_SLUG, MLA_PLUGIN_URL . 'css/mla-style-rtl.css', false, MLACore::mla_script_version() );
		} else {
			wp_register_style( MLACore::STYLESHEET_SLUG, MLA_PLUGIN_URL . 'css/mla-style.css', false, MLACore::mla_script_version() );
		}

		wp_enqueue_style( MLACore::STYLESHEET_SLUG );

		wp_register_style( MLACore::STYLESHEET_SLUG . '-set-parent', MLA_PLUGIN_URL . 'css/mla-style-set-parent.css', false, MLACore::mla_script_version() );
		wp_enqueue_style( MLACore::STYLESHEET_SLUG . '-set-parent' );

		wp_enqueue_script( MLACore::JAVASCRIPT_INLINE_EDIT_SLUG, MLA_PLUGIN_URL . "js/mla-inline-edit-scripts{$suffix}.js", 
			array( 'wp-lists', 'suggest', 'jquery' ), MLACore::mla_script_version(), false );

		if ( MLACore::mla_supported_taxonomies( 'checklist-add-term' ) ) {
			wp_enqueue_script( MLACore::JAVASCRIPT_INLINE_EDIT_SLUG . '-add-term', MLA_PLUGIN_URL . "js/mla-add-term-scripts{$suffix}.js", 
				array( 'wp-ajax-response', 'jquery', MLACore::JAVASCRIPT_INLINE_EDIT_SLUG ), MLACore::mla_script_version(), false );
		}
		
		wp_enqueue_script( MLACore::JAVASCRIPT_INLINE_EDIT_SLUG . '-set-parent', MLA_PLUGIN_URL . "js/mla-set-parent-scripts{$suffix}.js", 
			array( 'wp-lists', 'suggest', 'jquery', MLACore::JAVASCRIPT_INLINE_EDIT_SLUG ), MLACore::mla_script_version(), false );

		MLAModal::mla_add_terms_search_scripts();

		$fields = array( 'post_title', 'post_name', 'post_excerpt', 'post_content', 'image_alt', 'jj', 'mm', 'aa', 'hh', 'mn', 'ss', 'post_parent', 'post_parent_title', 'menu_order', 'post_author' );
		$custom_fields = MLACore::mla_custom_field_support( 'quick_edit' );
		$custom_fields = array_merge( $custom_fields, MLACore::mla_custom_field_support( 'bulk_edit' ) );
		foreach ( $custom_fields as $slug => $details ) {
			$fields[] = $slug;
		}

		$fields = apply_filters( 'mla_list_table_inline_fields', $fields );

		$script_variables = array(
			'fields' => $fields,
			'error' => __( 'Error while saving the changes.', 'media-library-assistant' ),
			'ntdelTitle' => __( 'Remove From Bulk Edit', 'media-library-assistant' ),
			'noTitle' => __( '(no title)', 'media-library-assistant' ),
			'bulkTitle' => __( 'Bulk Edit items', 'media-library-assistant' ),
			'bulkWaiting' => __( 'Waiting', 'media-library-assistant' ),
			'bulkComplete' => __( 'Complete', 'media-library-assistant' ),
			'bulkUnchanged' => __( 'Unchanged', 'media-library-assistant' ),
			'bulkSuccess' => __( 'Succeeded', 'media-library-assistant' ),
			'bulkFailure' => __( 'Failed', 'media-library-assistant' ),
			'bulkCanceled' => __( 'CANCELED', 'media-library-assistant' ),
			'bulkChunkSize' => MLACore::mla_get_option( MLACoreOptions::MLA_BULK_CHUNK_SIZE ),
			'comma' => _x( ',', 'tag_delimiter', 'media-library-assistant' ),
			'useSpinnerClass' => false,
			'ajax_action' => MLACore::JAVASCRIPT_INLINE_EDIT_SLUG,
			'ajax_nonce' => wp_create_nonce( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME ),
			'ajaxFailError' => __( 'An ajax.fail error has occurred. Please reload the page and try again.', 'media-library-assistant' ),
			'ajaxDoneError' => __( 'An ajax.done error has occurred. Please reload the page and try again.', 'media-library-assistant' ),
			'setParentAction' => MLACore::JAVASCRIPT_FIND_POSTS_SLUG,
			'exportPresetsAction' => MLACore::JAVASCRIPT_EXPORT_PRESETS_SLUG,
			'exportPresetsOption' => MLACoreOptions::MLA_BULK_EDIT_PRESETS,
			'deleteAcpBulkEdit' => false,
		);

		if ( version_compare( get_bloginfo( 'version' ), '4.2', '>=' ) ) {
			$script_variables['useSpinnerClass'] = true;
		}

		if ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_BULK_EDITOR ) ) {
			$script_variables['quickTagsInit'] = array( 'post_content' => array( 'id' => 'post_content', 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close', 'active' => true, ) );
		}

		if ( function_exists( 'ACP' ) && ( version_compare( ACP()->get_version(), '4.5', '>=' ) ) ) {
			if ( version_compare( ACP()->get_version(), '4.5.4', '<' ) ) {
				$script_variables['deleteAcpBulkEdit'] = true;
			}
		}
		
		wp_localize_script( MLACore::JAVASCRIPT_INLINE_EDIT_SLUG, self::JAVASCRIPT_INLINE_EDIT_OBJECT, $script_variables );
	}

	/**
	 * Add the submenu pages
	 *
	 * Add a submenu page in the "Media" section,
	 * add settings page in the "Settings" section.
	 * add settings link in the Plugins section entry for MLA.
	 *
	 * @since 0.1
	 *
	 * @return	void
	 */
	public static function mla_admin_menu_action( ) {
		global $submenu;

		add_action( 'load-upload.php', 'MLA::mla_load_media_action' );

		// Disable the MLA Download ZIP Example plugin, if necessary
		if ( isset( $_REQUEST['page'] ) && MLACore::ADMIN_PAGE_SLUG === $_REQUEST['page'] ) {
			$bulk_action = '';
			if ( isset( $_REQUEST['action'] ) && 'download-zip' === $_REQUEST['action']) {
				$bulk_action = 'download-zip';
			} elseif ( isset( $_REQUEST['action2'] ) && 'download-zip' === $_REQUEST['action2']) {
				$bulk_action = 'download-zip';
			}
	
			if ( 'download-zip' === $bulk_action ) {
				if ( is_plugin_active( 'mla-zip-archive-example/mla-zip-archive-example.php' ) ) {
					deactivate_plugins( 'mla-zip-archive-example/mla-zip-archive-example.php', true );
				}

				if ( is_plugin_active( 'mla-zip-archive-example.php' ) ) {
					deactivate_plugins( 'mla-zip-archive-example.php', true );
				}
			}
		}

		$page_title = MLACore::mla_get_option( MLACoreOptions::MLA_SCREEN_PAGE_TITLE );
		if ( empty( $page_title ) ) {
			$page_title = MLACore::mla_get_option( MLACoreOptions::MLA_SCREEN_PAGE_TITLE, true );
		}

		$menu_title = MLACore::mla_get_option( MLACoreOptions::MLA_SCREEN_MENU_TITLE );
		if ( empty( $menu_title ) ) {
			$menu_title = MLACore::mla_get_option( MLACoreOptions::MLA_SCREEN_MENU_TITLE, true );
		}

		$hook = add_submenu_page( 'upload.php', $page_title, $menu_title, 'upload_files', MLACore::ADMIN_PAGE_SLUG, 'MLA::mla_render_admin_page' );
		add_action( 'load-' . $hook, 'MLA::mla_add_menu_options' );
		add_action( 'load-' . $hook, 'MLA::mla_add_help_tab' );
		self::$page_hooks[ $hook ] = $hook;

		$taxonomies = get_object_taxonomies( 'attachment', 'objects' );
		if ( !empty( $taxonomies ) ) {
			foreach ( $taxonomies as $tax_name => $tax_object ) {
				/*
				 * The page_hook we need for taxonomy edits is slightly different
				 */
				$hook = 'edit-' . $tax_name;
				self::$page_hooks[ $hook ] = 't_' . $tax_name;
			} // foreach $taxonomies

			/*
			 * Load here, not 'load-edit-tags.php', to put our tab after the defaults
			 */
			add_action( 'admin_head-edit-tags.php', 'MLA::mla_add_help_tab' );
		}

		/*
		 * If we are suppressing the Media/Library submenu, force Media/Assistant to come first
		 */
		if ( 'checked' != MLACore::mla_get_option( MLACoreOptions::MLA_SCREEN_DISPLAY_LIBRARY ) ) {
			$menu_position = 4;
		} else {
			$menu_position = (integer) MLACore::mla_get_option( MLACoreOptions::MLA_SCREEN_ORDER );
		}

		if ( $menu_position && !empty( $submenu['upload.php'] ) ) {
			foreach ( $submenu['upload.php'] as $menu_order => $menu_item ) {
				if ( MLACore::ADMIN_PAGE_SLUG == $menu_item[2] ) {
					$menu_item[2] = 'upload.php?page=' . MLACore::ADMIN_PAGE_SLUG;
					$submenu['upload.php'][$menu_position] = $menu_item;
					unset( $submenu['upload.php'][$menu_order] );
					ksort( $submenu['upload.php'] );
					break;
				}
			}
		}

		add_filter( 'parent_file', 'MLA::mla_parent_file_filter', 10, 1 );
	}

	/**
	 * Redirect to Media/Assistant if Media/Library is hidden or a trash/delete
	 * returns from Media/Edit Media initiated from Media/Assistant
	 *
	 * @since 1.60
	 *
	 * @return	void
	 */
	public static function mla_load_media_action( ) {
		$mla_source = isset( $_REQUEST['mla_source'] ) && in_array( $_REQUEST['mla_source'], array ( 'trash', 'delete' ) );

		if ( $mla_source || ( 'checked' != MLACore::mla_get_option( MLACoreOptions::MLA_SCREEN_DISPLAY_LIBRARY ) ) ) {
			// Allow "grid" view even if the list view is suppressed
			if ( isset( $_REQUEST['mode'] ) && 'grid' === $_REQUEST['mode'] ) {
				return;
			}

			$query_args = '?page=' . MLACore::ADMIN_PAGE_SLUG;

			// Compose a message if returning from the Edit Media screen
			if ( ! empty( $_GET['deleted'] ) && $deleted = absint( $_GET['deleted'] ) ) {
				$query_args .= '&mla_admin_message=' . urlencode( sprintf( _n( 'Item permanently deleted.', '%d items permanently deleted.', $deleted, 'media-library-assistant' ), number_format_i18n( $deleted ) ) );
			}

			if ( ! empty( $_GET['trashed'] ) && absint( $_GET['trashed'] ) ) {
				/* translators: 1: post ID */
				$query_args .= '&mla_admin_message=' . urlencode( sprintf( __( 'Item %1$d moved to Trash.', 'media-library-assistant' ), isset( $_GET['ids'] ) ? absint( $_GET['ids'] ) : 0 ) );
			}

			wp_redirect( admin_url( 'upload.php' ) . $query_args, 302 );
			exit;
		}
	}

	/**
	 * Add the "XX Entries per page" filter to the Screen Options tab
	 *
	 * @since 0.1
	 *
	 * @return	void
	 */
	public static function mla_add_menu_options( ) {
		$option = 'per_page';

		$args = array(
			 'label' => __( 'Entries per page', 'media-library-assistant' ),
			'default' => 10,
			'option' => MLA_OPTION_PREFIX . 'entries_per_page' 
		);

		add_screen_option( $option, $args );
	}

	/**
	 * Add contextual help tabs to all the MLA pages
	 *
	 * @since 0.1
	 *
	 * @return	void
	 */
	public static function mla_add_help_tab( ) {
		$screen = get_current_screen();
		// Is this one of our pages?
		if ( !array_key_exists( $screen->id, self::$page_hooks ) ) {
			return;
		}

		if ( 'edit-tags' == $screen->base && 'attachment' != $screen->post_type ) {
			return;
		}

		$file_suffix = $screen->id;

		// Use a generic page for edit taxonomy screens
		if ( 't_' == substr( self::$page_hooks[ $file_suffix ], 0, 2 ) ) {
			$taxonomy = substr( self::$page_hooks[ $file_suffix ], 2 );
			switch ( $taxonomy ) {
				case 'attachment_category':
				case 'attachment_tag':
					break;
				default:
					$tax_object = get_taxonomy( $taxonomy );

					if ( $tax_object->hierarchical ) {
						$file_suffix = 'edit-hierarchical-taxonomy';
					} else {
						$file_suffix = 'edit-flat-taxonomy';
					}
			} // $taxonomy switch
		} // is taxonomy

		$template_array = apply_filters( 'mla_list_table_help_template', NULL, 'help-for-' . $file_suffix . '.tpl', $file_suffix );
		if ( is_null( $template_array ) ) {
			$template_array = MLACore::mla_load_template( 'help-for-' . $file_suffix . '.tpl' );
		}

		if ( empty( $template_array ) ) {
			return;
		}

		// Don't add sidebar to the WordPress category and post_tag screens
		if ( ! ( 'edit-tags' == $screen->base && in_array( $screen->taxonomy, array( 'post_tag', 'category' ) ) ) ) {
			if ( !empty( $template_array['sidebar'] ) ) {
				$page_values = array( 'settingsURL' => admin_url('options-general.php') );
				$content = MLAData::mla_parse_template( $template_array['sidebar'], $page_values );
				$screen->set_help_sidebar( $content );
			}
		}
		unset( $template_array['sidebar'] );

		// Provide explicit control over tab order
		$tab_array = array();

		foreach ( $template_array as $id => $content ) {
			$match_count = preg_match( '#\<!-- title="(.+)" order="(.+)" --\>#', $content, $matches, PREG_OFFSET_CAPTURE );

			if ( $match_count > 0 ) {
				$tab_array[ $matches[ 2 ][ 0 ] ] = array(
					 'id' => $id,
					'title' => $matches[ 1 ][ 0 ],
					'content' => $content 
				);
			} else {
				/* translators: 1: ERROR tag 2: function name 3: template key */
				MLACore::mla_debug_add( sprintf( _x( '%1$s: %2$s discarding "%3$s"; no title/order', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), 'mla_add_help_tab', $id ), MLACore::MLA_DEBUG_CATEGORY_ANY );
			}
		}

		ksort( $tab_array, SORT_NUMERIC );
		foreach ( $tab_array as $indx => $value ) {
			// Don't add duplicate tabs to the WordPress category and post_tag screens
			if ( 'edit-tags' == $screen->base && in_array( $screen->taxonomy, array( 'post_tag', 'category' ) ) ) {
				if ( 'mla-attachments-column' != $value['id'] ) {
					continue;
				}
			}

			$page_values = array( 'settingsURL' => admin_url('options-general.php') );
			$value = MLAData::mla_parse_template( $value, $page_values );
			$screen->add_help_tab( $value );
		}
	}

	/**
	 * Only show screen options on the table-list screen
	 *
	 * @since 0.1
	 *
	 * @param	boolean	True to display "Screen Options", false to suppress them
	 * @param	string	Name of the page being loaded
	 *
	 * @return	boolean	True to display "Screen Options", false to suppress them
	 */
	public static function mla_screen_options_show_screen_filter( $show_screen, $this_screen ) {
		return $show_screen;
	}

	/**
	 * Save the "Entries per page" option set by this user
	 *
	 * @since 0.1
	 *
	 * @param	mixed	false or value returned by previous filter
	 * @param	string	Name of the option being changed
	 * @param	string	New value of the option
	 *
	 * @return	mixed	New value if this is our option, otherwise original status
	 */
	public static function mla_set_screen_option_filter( $status, $option, $value ) {
		MLACore::mla_debug_add( __LINE__ . " MLA::mla_set_screen_option_filter( {$option} ) status = " . var_export( $status, true ), MLACore::MLA_DEBUG_CATEGORY_ANY );
		MLACore::mla_debug_add( __LINE__ . " MLA::mla_set_screen_option_filter( {$option} ) value = " . var_export( $value, true ), MLACore::MLA_DEBUG_CATEGORY_ANY );
		MLACore::mla_debug_add( __LINE__ . " MLA::mla_set_screen_option_filter( {$option} ) wp_filter = " . MLACore::mla_decode_wp_filter('set-screen-option'), MLACore::MLA_DEBUG_CATEGORY_ANY );

		if ( ( MLA_OPTION_PREFIX . 'entries_per_page' ) == $option ) {
		MLACore::mla_debug_add( __LINE__ . " MLA::mla_set_screen_option_filter( {$option} ) return value = " . var_export( $value, true ), MLACore::MLA_DEBUG_CATEGORY_ANY );
			return absint( $value );
		}

		return $status;
	}

	/**
	 * Cleanup menus for Edit Tags/Categories page
	 *
	 * Fixes the submenu bolding when going to the Edit Media screen.
	 *
	 * @since 0.1
	 *
	 * @param	array	The top-level menu page
	 *
	 * @return	string	The updated top-level menu page
	 */
	public static function mla_parent_file_filter( $parent_file ) {
		global $submenu_file, $submenu, $hook_suffix;

		// Make sure the "Assistant" submenu line is bolded if it's been moved
		if ( 'media_page_' . MLACore::ADMIN_PAGE_SLUG == $hook_suffix ) {
			if ( 'checked' != MLACore::mla_get_option( MLACoreOptions::MLA_SCREEN_DISPLAY_LIBRARY ) ) {
				$menu_position = 4;
			} else {
				$menu_position = (integer) MLACore::mla_get_option( MLACoreOptions::MLA_SCREEN_ORDER );
			}

			if ( $menu_position ) {
				$submenu_file = 'upload.php?page=' . MLACore::ADMIN_PAGE_SLUG;
			}
		}

		// Make sure the "Assistant" submenu line is bolded if the Media/Library submenu is hidden
		if ( 'checked' != MLACore::mla_get_option( MLACoreOptions::MLA_SCREEN_DISPLAY_LIBRARY ) &&
		     'upload.php' == $parent_file && ( empty( $submenu_file ) || 'upload.php' == $submenu_file ) ) {
			$submenu_file = 'upload.php?page=' . MLACore::ADMIN_PAGE_SLUG;
		}

		// Make sure the "Assistant" submenu line is bolded when we go to the Edit Media page
		if ( isset( $_REQUEST['mla_source'] ) ) {
			$submenu_file = 'upload.php?page=' . MLACore::ADMIN_PAGE_SLUG;
		}

		return $parent_file;
	}

	/**
	 * Process secure file download
	 *
	 * @since 2.00
	 *
	 * @param	array	$request The download parameters mla_download_file and mla_download_type; mla_download_disposition is optional.
	 * @param	boolean	$test_path True to restrict downloads to the "uploads" area, false to download anywhere.
	 *
	 * @return	void	echos file contents and calls exit();
	 */
	private static function _process_mla_download_file( $request, $test_path ) {
		if ( isset( $request['mla_error'] ) ) {
			$message = $request['mla_error'];
		} else {
			$message = '';
		
			if ( isset( $request['mla_download_file'] ) && isset( $request['mla_download_type'] ) ) {
				if( ini_get( 'zlib.output_compression' ) ) { 
					ini_set( 'zlib.output_compression', 'Off' );
				}
	
				$file_name = stripslashes( $request['mla_download_file'] );
				$match_name = str_replace( '\\', '/', $file_name );
	
				if ( $test_path ) {
					$upload_dir = wp_upload_dir();
					$allowed_path = str_replace( '\\', '/', $upload_dir['basedir'] );
				}
	
				if ( $test_path && ( 0 !== strpos( $match_name, $allowed_path ) ) ) {
					$message = __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'download path out of bounds.', 'media-library-assistant' );
				} elseif ( false !== strpos( $match_name, '..' ) ) {
					$message = __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'download path invalid.', 'media-library-assistant' );
				}
			} else {
				$message = __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'download argument(s) not set.', 'media-library-assistant' );
			}
		} // no error message

		if ( empty( $message ) ) {
			if ( file_exists( $file_name ) ) {
				$filemtime = filemtime ( $file_name );
				$filesize = filesize( $file_name );
			} else {
				$filemtime = time();
				$filesize = 0;
			}
			
			header('Pragma: public'); 	// required
			header('Expires: 0');		// no cache
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Last-Modified: '.gmdate ( 'D, d M Y H:i:s', $filemtime ).' GMT');
			header('Cache-Control: private',false);
			header('Content-Type: '.$request['mla_download_type']);
			header('Content-Disposition: attachment; filename="'.basename( $file_name ).'"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: '.$filesize);	// provide file size
			header('Connection: close');

			if ( 0 < $filesize ) {
				readfile( $file_name );
			}

			if ( isset( $request['mla_download_disposition'] ) && 'delete' == $request['mla_download_disposition'] ) {
				@unlink( $file_name );
			}
		} else {
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			echo '<html xmlns="http://www.w3.org/1999/xhtml">';
			echo '<head>';
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo '<title>Download Error</title>';
			echo '</head>';
			echo '';
			echo '<body>';
			echo esc_html( $message );
			echo '</body>';
			echo '</html> ';
		}

		exit();
	}

	/**
	 * Process the 'download-zip' bulk action
	 *
	 * @since 2.79
	 *
 	 * @param	array Form elements, e.g., from $_REQUEST
	 *
	 * @return	array success/failure message and NULL content
	 */
	private static function _process_zip_archive_download( $request ) {
		// Make sure we have ZIP support
		if ( !class_exists( 'ZipArchive' ) ) {
			return __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'no ZipArchive support.', 'media-library-assistant' );
		}

		// Make sure we have attachments to process
		if ( empty( $request['cb_attachment'] ) ) {
			return __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'Could not retrieve Attachment.', 'media-library-assistant' );
		}

		// Create unique local names in case the same file name appears in multiple year/month/ directories.
		$file_names = array();
		foreach ( $request['cb_attachment'] as $index => $post_id ) {
			$file_name = get_attached_file( $post_id );
			$path_info = pathinfo( $file_name  );
			$local_name = $path_info['basename'];
			$suffix = 0;
			while( array_key_exists( $local_name, $file_names ) ) {
				$suffix++;
				$local_name = $path_info['filename'] . $suffix . '.' . $path_info['extension'];
			}

			$file_names[ $local_name ] = $file_name;
		}

		// Create the ZIP archive
		$upload_dir = wp_upload_dir();
		$prefix = ( defined( MLA_OPTION_PREFIX ) ) ? MLA_OPTION_PREFIX : 'mla_';
		$date = date("Ymd_B");
		$archive_name = $upload_dir['basedir'] . '/' . "{$prefix}attachments_{$date}.zip";

		// Clean up an obsolete file
		if ( file_exists( $archive_name ) ) {
			@unlink( $archive_name );
		}

		$zip = new ZipArchive();
		if ( true !== $zip->open( $archive_name, ZIPARCHIVE::CREATE ) ) {
			/* translators: 1: ZIP archive file name */
			$text = sprintf( __( 'The ZIP archive ( %1$s ) could not be created.', 'media-library-assistant' ), $archive_name );
			return array(
				'message' => __( 'ERROR', 'media-library-assistant' ) . ': ' . $text,
				'body' => '' 
			);
		}

		foreach( $file_names as $local_name => $file_name ) {
			if ( true !== $zip->addFile( $file_name, $local_name ) ) {
				/* translators: 1: ZIP archive file name */
				$text = sprintf( __( 'The file ( %1$s ) could not be added to the ZIP archive.', 'media-library-assistant' ), $file_name );
				return __( 'ERROR', 'media-library-assistant' ) . ': ' . $text;
			}
		}

		if ( true !== $zip->close() ) {
			/* translators: 1: ZIP archive file name */
			$text = sprintf( __( 'The ZIP archive ( %1$s ) could not be closed.', 'media-library-assistant' ), $archive_name );
			return __( 'ERROR', 'media-library-assistant' ) . ': ' . $text;
		}

		$download_args = array( 'page' => MLACore::ADMIN_PAGE_SLUG, 'mla_download_file' => urlencode( $archive_name ), 'mla_download_type' => 'application/zip', 'mla_download_disposition' => 'delete' );

		wp_redirect( add_query_arg( $download_args, wp_nonce_url( 'upload.php', MLACore::MLA_DOWNLOAD_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME ) ), 302 );
		exit;
	}

	/**
	 * Process bulk edit area fields, which may contain a Content Template
	 *
	 * @since 1.80
	 *
	 * @param	integer	Current post ID
	 * @param	string	Field value as entered
	 *
	 * @return	string	Empty, or new value for the field
	 */
	private static function _process_bulk_value( $post_id, $bulk_value ) {
		$new_value = stripslashes( trim( $bulk_value ) );

		if ( 'template:[+empty+]' == $new_value ) {
			return NULL;
		} elseif ( 'template:' == substr( $new_value, 0, 9 ) ) {
			$data_value = array(
				'data_source' => 'template',
				'meta_name' => substr( $new_value, 9 ),
				'keep_existing' => false,
				'format' => 'raw',
				'option' => 'text' );

			$new_value =  MLAOptions::mla_get_data_source( $post_id, 'single_attachment_mapping', $data_value );
			if ( ' ' == $new_value ) {
				$new_value = '';
			}
		} elseif ( strlen( $new_value ) ) {
			// preserve leading/trailing whitespace on non-empty entered values
			return $bulk_value;
		}

		return $new_value;
	}

	/**
	 * Prepare Bulk Edit field-level updates
	 *
	 * @since 2.11
	 *
	 * @param	integer	$post_id Current post ID
	 * @param	array	$request Form elements, e.g., from $_REQUEST
	 * @param	array	$custom_field_map Form id to field name mapping
	 *
	 * @return	array	Non-empty form elements
	 */
	public static function mla_prepare_bulk_edits( $post_id, $request, $custom_field_map ) {
		/*
		 * Copy the edit form contents to $new_data
		 * Trim text values for testing purposes only
		 */
		$new_data = array() ;
		if ( isset( $request['post_title'] ) ) {
			$test_value = self::_process_bulk_value( $post_id, $request['post_title'] );
			if ( strlen( $test_value ) ) {
				$new_data['post_title'] = $test_value;
			} elseif ( is_null( $test_value ) ) {
				$new_data['post_title'] = '';
			}
		}

		if ( isset( $request['post_excerpt'] ) ) {
			$test_value = self::_process_bulk_value( $post_id, $request['post_excerpt'] );
			if ( strlen( $test_value ) ) {
				$new_data['post_excerpt'] = $test_value;
			} elseif ( is_null( $test_value ) ) {
				$new_data['post_excerpt'] = '';
			}
		}

		if ( isset( $request['post_content'] ) ) {
			$test_value = self::_process_bulk_value( $post_id, $request['post_content'] );
			if ( strlen( $test_value ) ) {
				$new_data['post_content'] = $test_value;
			} elseif ( is_null( $test_value ) ) {
				$new_data['post_content'] = '';
			}
		}

		/*
		 * image_alt requires a separate key because some attachment types should
		 * not get a value, e.g., text or PDF documents. OBSOLETE as of v2.84 -
		 * allow all application/ MIME types; see class-mla-data.php
		 */
		if ( isset( $request['image_alt'] ) ) {
			$test_value = self::_process_bulk_value( $post_id, $request['image_alt'] );
			if ( strlen( $test_value ) ) {
				$new_data['bulk_image_alt'] = $test_value;
			} elseif ( is_null( $test_value ) ) {
				$new_data['bulk_image_alt'] = '';
			}
		}

		if ( isset( $request['post_date'] ) ) {
			$test_value = self::_process_bulk_value( $post_id, $request['post_date'] );
			if ( strlen( $test_value ) ) {
				// User input is in local time, not UTC
				$tz = get_option( 'timezone_string' );
				if ( !$tz ) {
					$tz = get_option( 'gmt_offset' );
					if ( $tz ) {
						// Adapted from /wp-includes/functions.php wp_timezone_string() added in WP 5.3.0
						$offset  = (float) $tz;
						$hours   = (int) $offset;
						$minutes = ( $offset - $hours );
					
						$sign      = ( $offset < 0 ) ? '-' : '+';
						$abs_hour  = abs( $hours );
						$abs_mins  = abs( $minutes * 60 );
						$tz = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );
					} else {
						$tz = 'UTC';
					}
				}

				$datetime = date_create( $test_value, new DateTimeZone( $tz ) );
				if ( $datetime ) {
					$new_text = $datetime->format( 'Y-m-d H:i:s' );
					$new_data['post_date'] = $new_text;
					$new_data['post_date_gmt'] = get_gmt_from_date( $new_text );
				} else {
					// strtotime() will "Parse about any English textual datetime description into a Unix timestamp"
					$timestamp = strtotime( $test_value );
					if( false !== $timestamp ) {
						// date() "Returns a string formatted according to the format string using the integer timestamp"
						$new_text = date( 'Y-m-d H:i:s', $timestamp );
						$new_data['post_date'] = $new_text;
						$new_data['post_date_gmt'] = get_gmt_from_date( $new_text );
					}
				}
			} elseif ( is_null( $test_value ) ) {
				// empty post_date value is not acceptable
			}
		}

		if ( isset( $request['post_parent'] ) ) {
			if ( is_numeric( $request['post_parent'] ) ) {
				$new_data['post_parent'] = $request['post_parent'];
			}
		}

		if ( isset( $request['post_author'] ) ) {
			if ( -1 != $request['post_author'] ) {
					$new_data['post_author'] = $request['post_author'];
			}
		}

		if ( isset( $request['comment_status'] ) ) {
			if ( -1 != $request['comment_status'] ) {
					$new_data['comment_status'] = $request['comment_status'];
			}
		}

		if ( isset( $request['ping_status'] ) ) {
			if ( -1 != $request['ping_status'] ) {
					$new_data['ping_status'] = $request['ping_status'];
			}
		}

		// Custom field support
		$custom_fields = array();

		if ( is_array( $custom_field_map ) ) {
			foreach ( $custom_field_map as $slug => $details ) {
				if ( isset( $request[ $slug ] ) ) {
					$test_value = self::_process_bulk_value( $post_id, $request[ $slug ] );
					if ( strlen( $test_value ) ) {
						$custom_fields[ $details['name'] ] = $test_value;
					} elseif ( is_null( $test_value ) ) {
						if ( $details['no_null'] ) {
							$custom_fields[ $details['name'] ] = NULL;
						} else {
							$custom_fields[ $details['name'] ] = '';
						}
					}
				}
			} // foreach
		}

		if ( ! empty( $custom_fields ) ) {
			$new_data[ 'custom_updates' ] = $custom_fields;
		}

		// Taxonomy Support
		$tax_inputs = array();
		$tax_actions = array();
		MLACore::mla_debug_add( __LINE__ . " MLA::mla_prepare_bulk_edits( {$post_id} ) tax_input = " . var_export( $request['tax_input'], true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );

		if ( isset( $request['tax_input'] ) && is_array( $request['tax_input'] ) ) {
			foreach ( $request['tax_input'] as $taxonomy => $terms ) {
				if ( ! empty( $request['tax_action'] ) ) {
					$tax_action = $request['tax_action'][ $taxonomy ];
				} else {
					$tax_action = 'replace';
				}

				MLACore::mla_debug_add( __LINE__ . " MLA::mla_prepare_bulk_edits( {$post_id}, {$taxonomy}, {$tax_action} ) terms = " . var_export( $terms, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );

				// Ignore empty updates
				if ( $hierarchical = is_array( $terms ) ) {
					// Make sure term_id values are integers, not strings, for flat-checklist
					$terms = array_map( 'absint', $terms );
					if ( false !== ( $index = array_search( 0, $terms ) ) ) {
						unset( $terms[ $index ] );
					}
				} else {
					// Parse out individual terms
					$comma = _x( ',', 'tag_delimiter', 'media-library-assistant' );
					if ( ',' !== $comma ) {
						$tags = str_replace( $comma, ',', $terms );
					}

					$fragments = explode( ',', trim( $terms, " \n\t\r\0\x0B," ) );
					$terms = array();
					foreach( $fragments as $fragment ) {
						// WordPress encodes special characters, e.g., "&" as HTML entities in term names
						if ( MLATest::$wp_3dot5 ) {
							$fragment = _wp_specialchars( trim( stripslashes_deep( $fragment ) ) );
						} else {
							$fragment = _wp_specialchars( trim( wp_unslash( $fragment ) ) );
						}

						if ( ! empty( $fragment ) ) {
							$terms[] = $fragment;
						}
					} // foreach fragment

					$terms = array_unique( $terms );
				}

				MLACore::mla_debug_add( __LINE__ . " MLA::mla_prepare_bulk_edits( {$post_id}, {$taxonomy}, {$tax_action} ) terms = " . var_export( $terms, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );
				if ( empty( $terms ) && 'replace' != $tax_action ) {
					continue;
				}

				$post_terms = get_object_term_cache( $post_id, $taxonomy );
				if ( false === $post_terms ) {
					$post_terms = wp_get_object_terms( $post_id, $taxonomy );
					wp_cache_add( $post_id, $post_terms, $taxonomy . '_relationships' );
				}

				$current_terms = array();
				foreach( $post_terms as $new_term ) {
					if ( $hierarchical ) {
						$current_terms[ $new_term->term_id ] =  $new_term->term_id;
					} else {
						$current_terms[ $new_term->name ] =  $new_term->name;
					}
				}
				MLACore::mla_debug_add( __LINE__ . " MLA::mla_prepare_bulk_edits( {$post_id}, {$taxonomy}, {$tax_action} ) current_terms = " . var_export( $current_terms, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );

				if ( 'add' == $tax_action ) {
					// Add new terms; remove existing terms
					foreach ( $terms as $index => $new_term ) {
						if ( isset( $current_terms[ $new_term ] ) ) {
							unset( $terms[ $index ] );
						}
					}

					$do_update = ! empty( $terms );
				} elseif ( 'remove' == $tax_action ) {
					// Remove only the existing terms
					foreach ( $terms as $index => $new_term ) {
						if ( ! isset( $current_terms[ $new_term ] ) ) {
							unset( $terms[ $index ] );
						}
					}

					$do_update = ! empty( $terms );
				} else { 
					/*
					 * Replace all terms; if the new terms match the term
					 * cache, we can skip the update
					 */
					foreach ( $terms as $new_term ) {
						if ( isset( $current_terms[ $new_term ] ) ) {
							unset( $current_terms[ $new_term ] );
						} else {
							$current_terms[ $new_term ] = $new_term;
							break; // not a match; stop checking
						}
					}

					$do_update = ! empty( $current_terms );
				}

				MLACore::mla_debug_add( __LINE__ . " MLA::mla_prepare_bulk_edits( {$post_id}, {$taxonomy}, {$tax_action} ) do_update = " . var_export( $do_update, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );
				MLACore::mla_debug_add( __LINE__ . " MLA::mla_prepare_bulk_edits( {$post_id}, {$taxonomy}, {$tax_action} ) new terms = " . var_export( $terms, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );

				if ( $do_update ) {
					$tax_inputs[ $taxonomy ] = $terms;
					$tax_actions[ $taxonomy ] = $tax_action;
				}
			} // foreach taxonomy
		}

		$new_data[ 'tax_input' ] = $tax_inputs;
		$new_data[ 'tax_action' ] = $tax_actions;

		return $new_data;
	}

	/**
	 * Holds [+index+] and [+found_rows+] values for MLAData_Source::_evaluate_data_source
	 *
	 * @since 2.52
	 *
	 * @var	array
	 */
	public static $bulk_edit_data_source = array();

	/**
	 * Process bulk action for one or more attachments
	 *
	 * @since 2.00
	 *
	 * @param	string	Bulk action slug: delete, edit, restore, trash, custom action
	 * @param	array	Form elements, e.g., from $_REQUEST
	 *
	 * @return	array	messages and page content: ( 'message', 'body', 'unchanged', 'success', 'failure', 'item_results' )
	 */
	public static function mla_process_bulk_action( $bulk_action, $request = NULL ) {
		$page_content = array( 'message' => '', 'body' => '', 'unchanged' => 0, 'success' => 0, 'failure' => 0, 'item_results' => array() );
		$custom_field_map = MLACore::mla_custom_field_support( 'bulk_edit' );

		// Check for failed ZIP Archive download request
		if ( !empty( $_REQUEST['mla_zip_archive_error_message'] ) ) {
			$page_content['message'] = sanitize_text_field( wp_unslash( $_REQUEST['mla_zip_archive_error_message'] ) );
			return $page_content;
		}
		
		/*
		 * do_cleanup will remove the bulk edit elements from the $_REQUEST super array.
		 * It is passed in the $request so it can be filtered.
		 */
		if ( NULL == $request ) {
			// Bulk Edit actions have their own NONCE check
			if ( isset( $_REQUEST['_wpnonce'] ) ) {
				check_admin_referer( 'bulk-attachments' );
			}

			$request = $_REQUEST;
			$request['mla_bulk_action_do_cleanup'] = true;
		} else {
			$request['mla_bulk_action_do_cleanup'] = false;
		}

		$request = apply_filters( 'mla_list_table_bulk_action_initial_request', $request, $bulk_action, $custom_field_map );
		MLACore::mla_debug_add( __LINE__ . " MLA::mla_process_bulk_action( {$bulk_action} ) request = " . var_export( $request, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );

		if ( isset( $request['cb_attachment'] ) ) {
			if ( !empty( $request['cb_offset'] ) ) {
				self::$bulk_edit_data_source['cb_offset'] = absint( $request['cb_offset'] );
				self::$bulk_edit_data_source['cb_count'] = absint( $request['cb_count'] );
				self::$bulk_edit_data_source['cb_index'] = self::$bulk_edit_data_source['cb_offset'];
			} else {
				self::$bulk_edit_data_source['cb_offset'] = 0;
				self::$bulk_edit_data_source['cb_count'] = count( $request['cb_attachment'] );
				self::$bulk_edit_data_source['cb_index'] = 0;
			}

			$item_content = apply_filters( 'mla_list_table_begin_bulk_action', NULL, $bulk_action );
			if ( is_null( $item_content ) ) {
				$prevent_default = false;
			} else {
				$prevent_default = isset( $item_content['prevent_default'] ) ? $item_content['prevent_default'] : false;
			}

			if ( $prevent_default ) {
				if ( isset( $item_content['message'] ) ) {
					$page_content['message'] = $item_content['message'];
				}

				if ( isset( $item_content['body'] ) ) {
					$page_content['body'] = $item_content['body'];
				}

				return $page_content;
			}

			if ( !empty( $request['bulk_custom_field_map'] ) ) {
				do_action( 'mla_begin_mapping', 'bulk_custom', NULL );
			} elseif ( !empty( $request['bulk_map'] ) ) {
				do_action( 'mla_begin_mapping', 'bulk_iptc_exif', NULL );
			}


			foreach ( $request['cb_attachment'] as $index => $post_id ) {
				self::$bulk_edit_data_source['cb_index']++;

				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					$page_content['message'] .= __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'You are not allowed to edit Attachment: ', 'media-library-assistant' ) . $post_id . '<br>';
					continue;
				}

				$request = apply_filters( 'mla_list_table_bulk_action_item_request', $request, $bulk_action, $post_id, $custom_field_map );

				$item_content = apply_filters( 'mla_list_table_bulk_action', NULL, $bulk_action, $post_id );
				if ( is_null( $item_content ) ) {
					$prevent_default = false;
					$custom_message = '';
				} else {
					$prevent_default = isset( $item_content['prevent_default'] ) ? $item_content['prevent_default'] : false;
					$custom_message = isset( $item_content['message'] ) ? $item_content['message'] : '';
				}

				if ( ! $prevent_default ) {
					switch ( $bulk_action ) {
						case 'delete':
							$item_content = self::_delete_single_item( $post_id );
							break;
						case 'edit':
							if ( !empty( $request['bulk_custom_field_map'] ) ) {
								$updates = MLAOptions::mla_evaluate_custom_field_mapping( $post_id, 'single_attachment_mapping' );
								$item_content = MLAData::mla_update_single_item( $post_id, $updates );
								break;
							}

							if ( !empty( $request['bulk_map'] ) ) {
								$item = get_post( $post_id );
								$updates = MLAOptions::mla_evaluate_iptc_exif_mapping( $item, 'iptc_exif_mapping' );
								$item_content = MLAData::mla_update_single_item( $post_id, $updates );
								break;
							}

							$new_data = self::mla_prepare_bulk_edits( $post_id, $request, $custom_field_map );
							MLACore::mla_debug_add( __LINE__ . " MLA::mla_process_bulk_action( {$post_id} ) new_data = " . var_export( $new_data, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );
							$tax_input = $new_data['tax_input'];
							$tax_action = $new_data['tax_action'];
							unset( $new_data['tax_input'] );
							unset( $new_data['tax_action'] );

							$item_content = MLAData::mla_update_single_item( $post_id, $new_data, $tax_input, $tax_action );
							MLACore::mla_debug_add( __LINE__ . " MLA::mla_process_bulk_action( {$post_id} ) item_content = " . var_export( $item_content, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );
							break;
						case 'restore':
							$item_content = self::_restore_single_item( $post_id );
							break;
						case 'trash':
							$item_content = self::_trash_single_item( $post_id );
							break;
						default:
							$item_content = apply_filters( 'mla_list_table_custom_bulk_action', NULL, $bulk_action, $post_id );

							if ( is_null( $item_content ) ) {
								$prevent_default = false;
								/* translators: 1: ERROR tag 2: bulk action */
								$custom_message = sprintf( __( '%1$s: Unknown bulk action %2$s', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), $bulk_action );
							} else {
								$prevent_default = isset( $item_content['prevent_default'] ) ? $item_content['prevent_default'] : false;
							}
					} // switch $bulk_action
				} // ! $prevent_default

				// Custom action can set $prevent_default, so test again.
				if ( ! $prevent_default ) {
					if ( ! empty( $custom_message ) ) {
						$no_changes = sprintf( __( 'Item %1$d, no changes detected.', 'media-library-assistant' ), $post_id );
						if ( $no_changes == $item_content['message'] ) {
							$item_content['message'] = $custom_message;
						} else {
							$item_content['message'] = $custom_message . '<br>' . $item_content['message'];
						}
					}

					$page_content['item_results'][ $post_id ] = array( 'result' => 'unknown', 'message' => $item_content['message'] );
					if ( ! empty( $item_content['message'] ) ) {
						$page_content['message'] .= $item_content['message'] . '<br>';

						if ( false !== strpos( $item_content['message'], __( 'no changes detected', 'media-library-assistant' ) ) ) {
							$page_content['unchanged'] += 1;
							$page_content['item_results'][ $post_id ]['result'] = 'unchanged';
						} elseif (	 false !== strpos( $item_content['message'], __( 'ERROR', 'media-library-assistant' ) ) ) {
							$page_content['failure'] += 1;
							$page_content['item_results'][ $post_id ]['result'] = 'failure';
						} else {
							$page_content['success'] += 1;
							$page_content['item_results'][ $post_id ]['result'] = 'success';
						}
					}
				} // ! $prevent_default
			} // foreach cb_attachment

			if ( !empty( $request['bulk_custom_field_map'] ) || !empty( $request['bulk_map'] ) ) {
				do_action( 'mla_end_mapping' );
			}

			$item_content = apply_filters( 'mla_list_table_end_bulk_action', NULL, $bulk_action );
			if ( isset( $item_content['message'] ) ) {
				$page_content['message'] .= $item_content['message'];
			}

			if ( isset( $item_content['body'] ) ) {
				$page_content['body'] = $item_content['body'];
			}

			if ( $request['mla_bulk_action_do_cleanup'] ) {
				unset( $_REQUEST['post_title'] );
				unset( $_REQUEST['post_excerpt'] );
				unset( $_REQUEST['post_content'] );
				unset( $_REQUEST['image_alt'] );
				unset( $_REQUEST['comment_status'] );
				unset( $_REQUEST['ping_status'] );
				unset( $_REQUEST['post_parent'] );
				unset( $_REQUEST['post_author'] );
				unset( $_REQUEST['tax_input'] );
				unset( $_REQUEST['tax_action'] );

				foreach ( MLACore::mla_custom_field_support( 'bulk_edit' ) as $slug => $details ) {
					unset( $_REQUEST[ $slug ] );
				}

				unset( $_REQUEST['cb_attachment'] );
			}
		} else { // isset cb_attachment
			/* translators: 1: action name, e.g., edit */
			$page_content['message'] = sprintf( __( 'Bulk Action %1$s - no items selected.', 'media-library-assistant' ), $bulk_action );
		}

		if ( $request['mla_bulk_action_do_cleanup'] ) {
			unset( $_REQUEST['action'] );
			unset( $_REQUEST['bulk_custom_field_map'] );
			unset( $_REQUEST['bulk_map'] );
			unset( $_REQUEST['bulk_edit'] );
			unset( $_REQUEST['action2'] );
		}

		MLACore::mla_debug_add( __LINE__ . " MLA::mla_process_bulk_action returning page_content = " . var_export( $page_content, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );
		return $page_content;
	}

	/**
	 * Clear the Media/Assistant submenu Filter-by variables 
	 *
	 * @since 2.13
	 *
	 * @param	array	$preserves Filters to be retained
	 *
	 * @return	void
	 */
	public static function mla_clear_filter_by( $preserves = array() ) {
		$filters = array( 'author', 'heading_suffix', 'ids', 'mla-metakey', 'mla-metavalue', 'mla-tax', 'mla-term', 'parent', 'clear_filter_by' );

		$filters = apply_filters( 'mla_list_table_clear_filter_by_filters', $filters, $preserves );
		$preserves = apply_filters( 'mla_list_table_clear_filter_by_preserves', $preserves, $filters );

		foreach ( $filters as $filter ) {
			if ( ! in_array( $filter, $preserves ) ) {
				unset( $_REQUEST[ $filter ] );
			}
		}

		do_action( 'mla_list_table_clear_filter_by' );
	}

	/**
	 * Render the "Assistant" subpage in the Media section, using the list_table package
	 *
	 * @since 0.1
	 *
	 * @return	void
	 */
	public static function mla_render_admin_page( ) {
		// Prevent _wp_http_referer recursion with method="get", preserve WPML/Polylang language selection
		$_SERVER['REQUEST_URI'] = parse_url( admin_url( 'upload.php' ), PHP_URL_PATH ) . '?page=mla-menu';

		$query_string = strval( isset( $_SERVER[ 'QUERY_STRING' ] ) ? esc_url_raw( wp_unslash( $_SERVER[ 'QUERY_STRING' ] ) ) : '' );
		$query = array();
		parse_str( $query_string, $query );
		if ( !empty( $query['lang'] ) ) {
			$_SERVER['REQUEST_URI'] .= '&lang=' . $query['lang'];
		}

		/*
		 * WordPress class-wp-list-table.php doesn't look in hidden fields to set
		 * the month filter dropdown or sorting parameters
		 */
		if ( isset( $_REQUEST['m'] ) ) {
			$_GET['m'] = absint( $_REQUEST['m'] );
		}

		if ( isset( $_REQUEST['order'] ) ) {
			$_GET['order'] = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
		}

		if ( isset( $_REQUEST['orderby'] ) ) {
			$_GET['orderby'] = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
		}

		// bulk_refresh simply refreshes the page, ignoring other bulk actions
		if ( ! empty( $_REQUEST['bulk_refresh'] ) ) {
			unset( $_REQUEST['action'] );
			unset( $_POST['action'] );
			unset( $_GET['action'] );
			unset( $_REQUEST['action2'] );
			unset( $_POST['action2'] );
			unset( $_GET['action2'] );
		}

		$bulk_action = self::_current_bulk_action();

		$page_title = MLACore::mla_get_option( MLACoreOptions::MLA_SCREEN_PAGE_TITLE );
		if ( empty( $page_title ) ) {
			$page_title = MLACore::mla_get_option( MLACoreOptions::MLA_SCREEN_PAGE_TITLE, true );
		}

		echo "<div class=\"wrap\">\n";
		echo "<h1 class=\"wp-heading-inline\">" . esc_html( $page_title ); // trailing </h1> is action-specific

		if ( !current_user_can( 'upload_files' ) ) {
			echo ' - ' . esc_html__( 'ERROR', 'media-library-assistant' ) . "</h1>\n";
			wp_die( esc_html__( 'You do not have permission to manage attachments.', 'media-library-assistant' ) );
		}

		$heading_tail = "</h1>\n<a href=\"media-new.php\" class=\"page-title-action\">" . esc_html_x('Add New', 'file') . "</a>\n<hr class=\"wp-header-end\">\n";

		$page_content = array(
			'message' => '',
			'body' => '' 
		);

		if ( !empty( $_REQUEST['mla_admin_message'] ) ) {
			$page_content['message'] = sanitize_text_field( wp_unslash( $_REQUEST['mla_admin_message'] ) );
		}

		/*
		 * The category taxonomy (edit screens) is a special case because 
		 * post_categories_meta_box() changes the input name
		 */
		if ( !isset( $_REQUEST['tax_input'] ) ) {
			$_REQUEST['tax_input'] = array();
		}

		if ( isset( $_REQUEST['post_category'] ) ) {
			$_REQUEST['tax_input']['category'] = array_map ( 'absint', $_REQUEST['post_category'] );
			unset ( $_REQUEST['post_category'] );
		}

		// Process bulk actions that affect an array of items
		if ( $bulk_action && ( $bulk_action != 'none' ) ) {
			// bulk_refresh simply refreshes the page, ignoring other bulk actions
			if ( empty( $_REQUEST['bulk_refresh'] ) ) {
				$item_content = self::mla_process_bulk_action( $bulk_action );
				$page_content['message'] .= $item_content['message'] . '<br>';
			}
		} // $bulk_action

		if ( isset( $_REQUEST['clear_filter_by'] ) ) {
			self::mla_clear_filter_by();
		}

		// Empty the Trash?
		if ( isset( $_REQUEST['delete_all'] ) ) {
			global $wpdb;

			$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type=%s AND post_status = %s", 'attachment', 'trash' ) );
			$delete_count = 0;
			foreach ( $ids as $post_id ) {
				$item_content = self::_delete_single_item( $post_id );

				if ( false !== strpos( $item_content['message'], __( 'ERROR', 'media-library-assistant' ) ) ) {
					$page_content['message'] .= $item_content['message'] . '<br>';
				} else {
					$delete_count++;
				}
			}

			if ( $delete_count ) {
				/* translators: 1: number of items */
				$page_content['message'] .= sprintf( _nx( '%s item deleted.', '%s items deleted.', $delete_count, 'deleted items', 'media-library-assistant' ), number_format_i18n( $delete_count ) );
			} else {
				$page_content['message'] .= __( 'No items deleted.', 'media-library-assistant' );
			}
		}

		// Process row-level actions that affect a single item
		if ( !empty( $_REQUEST['mla_admin_action'] ) ) {
			check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );

			$mla_item_id = isset( $_REQUEST['mla_item_ID'] ) ? absint( $_REQUEST['mla_item_ID'] ) : 0;
			$page_content = apply_filters( 'mla_list_table_single_action', NULL, sanitize_text_field( wp_unslash( $_REQUEST['mla_admin_action'] ) ), $mla_item_id );
			if ( is_null( $page_content ) ) {
				$prevent_default = false;
				$custom_message = '';
			} else {
				$prevent_default = isset( $page_content['prevent_default'] ) ? $page_content['prevent_default'] : false;
				$custom_message = isset( $page_content['message'] ) ? $page_content['message'] : '';
			}

			if ( ! $prevent_default ) {
				switch ( $_REQUEST['mla_admin_action'] ) {
					case MLACore::MLA_ADMIN_SINGLE_DELETE:
						$page_content = self::_delete_single_item( $mla_item_id );
						break;
					case MLACore::MLA_ADMIN_SINGLE_RESTORE:
						$page_content = self::_restore_single_item( $mla_item_id );
						break;
					case MLACore::MLA_ADMIN_SINGLE_TRASH:
						$page_content = self::_trash_single_item( $mla_item_id );
						break;
					case MLACore::MLA_ADMIN_SET_PARENT:
						$new_data = array( 'post_parent' => isset( $_REQUEST['found_post_id'] ) ? absint( $_REQUEST['found_post_id'] ) : 0 );

						$children = isset( $_REQUEST['children'] ) ? array_map ( 'absint', $_REQUEST['children'] ) : array();
						foreach( $children as $child ) {
							$item_content = MLAData::mla_update_single_item( $child, $new_data );
							$page_content['message'] .= $item_content['message'] . '<br>';
						}

						unset( $_REQUEST['parent'] );
						unset( $_REQUEST['children'] );
						unset( $_REQUEST['mla-set-parent-ajax-nonce'] );
						unset( $_REQUEST['mla_set_parent_search_text'] );
						unset( $_REQUEST['found_post_id'] );
						unset( $_REQUEST['mla-set-parent-submit'] );
						break;
					case MLACore::MLA_ADMIN_TERMS_SEARCH:
						/*
						 * This will be handled as a database query argument,
						 * but validate the arguments here
						 */
						$mla_terms_search = array( 'phrases' => '', 'taxonomies' => array() );
						if ( isset( $_REQUEST['mla_terms_search'] ) ) {
							$mla_terms_search['phrases'] = isset( $_REQUEST['mla_terms_search']['phrases'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mla_terms_search']['phrases'] ) )  : '';
							$mla_terms_search['taxonomies'] = isset( $_REQUEST['mla_terms_search']['taxonomies'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['mla_terms_search']['taxonomies'] ) ) : array();
						}

						if ( ! is_array( $mla_terms_search ) || empty( $mla_terms_search['phrases'] ) || empty( $mla_terms_search['taxonomies'] ) ) {
							unset( $_REQUEST['mla_terms_search'] );
							$page_content = array(
								'message' => __( 'Empty Terms Search; ignored', 'media-library-assistant' ),
								'body' => '' 
							);
						} else {
							unset( $_REQUEST['mla_terms_search']['submit'] );
						}
						break;
					default:
						$page_content = apply_filters( 'mla_list_table_custom_single_action', NULL, sanitize_text_field( wp_unslash( $_REQUEST['mla_admin_action'] ) ), $mla_item_id );
						if ( is_null( $page_content ) ) {
							$page_content = array(
								/* translators: 1: row-level action, e.g., single_item_delete, single_item_edit */
								'message' => sprintf( __( 'Unknown mla_admin_action - "%1$s"', 'media-library-assistant' ), sanitize_text_field( wp_unslash( $_REQUEST['mla_admin_action'] ) ) ),
								'body' => '' 
							);
						} // Unknown mla_admin_action
				} // switch ($_REQUEST['mla_admin_action'])
			} // ! $prevent_default

			if ( ! empty( $custom_message ) ) {
				$page_content['message'] = $custom_message . $page_content['message'];
			}
		} // (!empty($_REQUEST['mla_admin_action'])
			
		// Don't let WPML copy these to language-specific URLs
		foreach ( array( 'action', 'action2', 'cb_attachment', 'clear_filter_by', 'delete_all', 'mla_admin_action' ) as $argument ) {
			unset( $_REQUEST[ $argument ] );
			unset( $_POST[ $argument ] );
			unset( $_GET[ $argument ] );
		}

		// Avoid "headers already sent" error with JoomUnited's WP Media Folders
		if (isset($_REQUEST['s']) && $_REQUEST['s'] === '') {
			unset( $_REQUEST['s'] );
			unset( $_POST['s'] );
			unset( $_GET['s'] );
		}

		if ( !empty( $page_content['body'] ) ) {
			if ( !empty( $page_content['message'] ) ) {
				if ( false !== strpos( $page_content['message'], __( 'ERROR', 'media-library-assistant' ) ) ) {
					$messages_class = 'updated error';
				} else {
					$messages_class = 'updated notice is-dismissible';
				}

				echo "  <div class=\"" . esc_html( $messages_class ) . "\"><p>\n";
				echo '    ' . wp_kses( $page_content['message'], 'post' ) . "\n";
				echo "  </p></div>\n"; // id="message"
			}

			echo $page_content['body']; // phpcs:ignore
		} else {
			// Display Attachments list
			if ( !empty( $_REQUEST['heading_suffix'] ) ) {
				echo ' - ' . esc_html( urldecode( wp_kses( wp_unslash( $_REQUEST['heading_suffix'] ), 'post' ) ) ) . wp_kses( $heading_tail, 'post' );
			} elseif ( !empty( $_REQUEST['mla_terms_search'] ) && is_array( $_REQUEST['mla_terms_search'] ) ) {
					echo ' - ' . esc_html__( 'term search results for', 'media-library-assistant' ) . ' "' . esc_html( trim( sanitize_text_field( wp_unslash( $_REQUEST['mla_terms_search']['phrases'] ), 'post' ) ) ). "\"" . wp_kses( $heading_tail, 'post' );
			} elseif ( !empty( $_REQUEST['s'] ) ) {
				if ( empty( $_REQUEST['mla_search_fields'] ) ) {
					echo ' - ' . esc_html__( 'post/parent results for', 'media-library-assistant' ) . ' "' . esc_html( trim( wp_kses( wp_unslash( $_REQUEST['s'] ) , 'post' ) ) ) . "\"" . wp_kses( $heading_tail, 'post' );
				} else {
					echo ' - ' . esc_html__( 'search results for', 'media-library-assistant' ) . ' "' . esc_html( trim( wp_kses( wp_unslash( $_REQUEST['s'] ), 'post' ) ) ) . "\"" . wp_kses( $heading_tail, 'post' );
				}
			} else {
				echo wp_kses( $heading_tail, 'post' );
			}

			if ( !empty( $page_content['message'] ) ) {
				if ( false !== strpos( $page_content['message'], __( 'ERROR', 'media-library-assistant' ) ) ) {
					$messages_class = 'updated error';
				} else {
					$messages_class = 'updated notice is-dismissible';
				}

				echo "  <div class=\"" . esc_html( $messages_class ) . "\"><p>\n";
				echo '    ' . wp_kses( $page_content['message'], 'post' ) . "\n";
				echo "  </p></div>\n"; // id="message"
			}

			// Create an instance of our package class...
			$MLAListTable = apply_filters( 'mla_list_table_new_instance', NULL );
			if ( is_null( $MLAListTable ) ) {
				$MLAListTable = new MLA_List_Table();
			}

			// Fetch, prepare, sort, and filter our data...
			$MLAListTable->prepare_items();
			$MLAListTable->views();

			$view_arguments = MLA_List_Table::mla_submenu_arguments();
			if ( isset( $view_arguments['lang'] ) ) { // from WPML/Polylang
				$form_url = 'upload.php?page=' . MLACore::ADMIN_PAGE_SLUG . '&lang=' . $view_arguments['lang'];
			} else {
				$form_url = 'upload.php?page=' . MLACore::ADMIN_PAGE_SLUG;
			}

			// Real Media Library compatibility
			if ( isset( $_REQUEST['rml_folder'] ) ) {
				$form_url .= '&rml_folder=' . urlencode( sanitize_text_field( wp_unslash( $_REQUEST['rml_folder'] ) ) );
			}
	
			// Forms are NOT created automatically, wrap the table in one to use features like bulk actions
			echo "\n" . '<form action="' . esc_url_raw( admin_url( $form_url ) ) . '" method="get" id="mla-filter">' . "\n";

			// Include the Search Media box
			require_once MLA_PLUGIN_PATH . 'includes/mla-main-search-box-template.php';

			// We also need to ensure that the form posts back to our current page and remember all the view arguments
			echo sprintf( '<input type="hidden" name="page" value="%1$s" />', esc_html( isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : MLACore::ADMIN_PAGE_SLUG ) ) . "\n";

			// Pass the WPML/Polylang language in the form
			if ( isset( $view_arguments['lang'] ) ) {
				echo sprintf( '<input type="hidden" name="lang" value="%1$s" />', esc_html( $view_arguments['lang'] ) ) . "\n";
			}

			foreach ( $view_arguments as $key => $value ) {
				if ( 'meta_query' == $key && isset( $_REQUEST['meta_query'] ) ) {
					$value = wp_kses( wp_unslash( $_REQUEST['meta_query'] ), 'post' );
				}

				/*
				 * Search box elements are already set up in the above "search-box"
				 * 'lang' has already been added to the form action attribute
				 */
				if ( in_array( $key, array( 's', 'mla_search_connector', 'mla_search_fields', 'lang' ) ) ) {
					continue;
				}

				if ( is_array( $value ) ) {
					foreach ( $value as $element_key => $element_value ) {
						echo sprintf( '<input type="hidden" name="%1$s[%2$s]" value="%3$s" />', esc_attr( $key ), esc_attr( $element_key ), esc_attr( $element_value ) ) . "\n";
					}
				} else {
					echo sprintf( '<input type="hidden" name="%1$s" value="%2$s" />', esc_attr( $key ), esc_attr( urldecode( $value ) ) ) . "\n";
				}
			}

			// Now we can render the completed list table
			$MLAListTable->display();
			echo "</form><!-- id=mla-filter -->\n";

			// Insert the hidden form and table for inline edits (quick & bulk)
			echo self::_build_inline_edit_form( $MLAListTable ); // phpcs:ignore

			echo "<div id=\"ajax-response\"></div>\n";
			echo "<br class=\"clear\" />\n";
			echo "</div><!-- class=wrap -->\n";
		} // display attachments list
	}

	/**
	 * Ajax handler for bulk editing and mapping
	 *
	 * @since 2.00
	 *
	 * @return	void	echo json results or error message, then die()
	 */
	private static function _bulk_edit_ajax_handler() {
		/*
		 * The category taxonomy (edit screens) is a special case because 
		 * post_categories_meta_box() changes the input name
		 */
		if ( !isset( $_REQUEST['tax_input'] ) ) {
			$_REQUEST['tax_input'] = array();
		}

		if ( isset( $_REQUEST['post_category'] ) ) {
			$_REQUEST['tax_input']['category'] = array_map( 'absint', $_REQUEST['post_category'] );
			unset ( $_REQUEST['post_category'] );
		}

		// Convert bulk_action to the old button name/value variables
		if ( isset( $_REQUEST['bulk_action'] ) ) {
			switch ( $_REQUEST['bulk_action'] ) {
				case 'bulk_custom_field_map':
					$_REQUEST['bulk_custom_field_map'] = __( 'Map Custom Field metadata', 'media-library-assistant' );
					break;
				case 'bulk_map':
					$_REQUEST['bulk_map'] = __( 'Map IPTC/EXIF metadata', 'media-library-assistant' );
					break;
				case 'bulk_edit':
					$_REQUEST['bulk_edit'] = __( 'Update', 'media-library-assistant' );
			}
		}
		
		$item_content = (object) self::mla_process_bulk_action( 'edit' );
		wp_send_json_success( $item_content );
	}

	/**
	 * Ajax handler for inline editing
	 *
	 * Adapted for Quick Edit from wp_ajax_inline_save in /wp-admin/includes/ajax-actions.php
	 *
	 * @since 0.20
	 *
	 * @return	void	echo HTML <tr> markup for updated row or error message, then die()
	 */
	public static function mla_inline_edit_ajax_action() {
		if ( isset( $_REQUEST['screen'] ) ) {
			set_current_screen( sanitize_text_field( wp_unslash( $_REQUEST['screen'] ) ) );
		}

		check_ajax_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );

		if ( ! empty( $_REQUEST['bulk_action'] ) ) {
			self::_bulk_edit_ajax_handler(); // calls wp_send_json_success and die()
		}

		if ( empty( $_REQUEST['post_ID'] ) ) {
			echo esc_html__( 'ERROR', 'media-library-assistant' ) . ': ' . esc_html__( 'No post ID found', 'media-library-assistant' );
			die();
		} else {
			$post_id = absint( $_REQUEST['post_ID'] );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'ERROR', 'media-library-assistant' ) . ': ' . esc_html__( 'You are not allowed to edit this Attachment.', 'media-library-assistant' ) );
		}

		// Custom field support
		$custom_fields = array();
		foreach ( MLACore::mla_custom_field_support( 'quick_edit' ) as $slug => $details ) {
			if ( isset( $_REQUEST[ $slug ] ) ) {
				if ( current_user_can( 'unfiltered_html' ) ) {
					$value = trim( wp_unslash( $_REQUEST[ $slug ] ) ); // phpcs:ignore
				} else {
					$value = trim( wp_kses( wp_unslash( $_REQUEST[ $slug ] ), 'post' ) );
				}
				unset ( $_REQUEST[ $slug ] );

				// '(Array)' indicates an existing array value in the field, which we preserve
				if ( 'array' == $details['option'] ) {
					$value = explode( ',', $value );
				} elseif ( '(Array)' == $value ) {
					continue;
				}

				if ( $details['no_null'] && empty( $value ) ) {
					$custom_fields[ $details['name'] ] = NULL;
				} else {
					$custom_fields[ $details['name'] ] = $value;
				}
			}
		}

		if ( ! empty( $custom_fields ) ) {
			$_REQUEST[ 'custom_updates' ] = $custom_fields;
		}

		// The category taxonomy is a special case because post_categories_meta_box() changes the input name
		if ( !isset( $_REQUEST['tax_input'] ) ) {
			$_REQUEST['tax_input'] = array();
		}

		if ( isset( $_REQUEST['post_category'] ) ) {
			$_REQUEST['tax_input']['category'] = array_map( 'absint', $_REQUEST['post_category'] );
			unset ( $_REQUEST['post_category'] );
		}

		$tax_output = array();
		if ( ! empty( $_REQUEST['tax_input'] ) ) {
			// Flat taxonomy strings must be cleaned up and duplicates removed
			$taxonomies = array_keys( array_map( 'absint', wp_unslash( $_REQUEST['tax_input'] ) ) );
			foreach ( $taxonomies as $tax_name ) {
				if ( isset( $_REQUEST['tax_input'][ $tax_name ] ) ) {
					if ( is_array( $_REQUEST['tax_input'][ $tax_name ] ) ) {
						// Make sure term_id values are integers, not strings, for flat-checklist
						$tax_value = array_map( 'absint', $_REQUEST['tax_input'][ $tax_name ] );
					} else {
						$tax_value = sanitize_text_field( wp_unslash( $_REQUEST['tax_input'][ $tax_name ] ) );
						$comma = _x( ',', 'tag_delimiter', 'media-library-assistant' );
						if ( ',' != $comma ) {
							$tax_value = str_replace( $comma, ',', $tax_value );
						}
	
						$tax_value = preg_replace( '#\s*,\s*#', ',', $tax_value );
						$tax_value = preg_replace( '#,+#', ',', $tax_value );
						$tax_value = preg_replace( '#[,\s]+$#', '', $tax_value );
						$tax_value = preg_replace( '#^[,\s]+#', '', $tax_value );
	
						if ( ',' != $comma ) {
							$tax_value = str_replace( ',', $comma, $tax_value );
						}
	
						$tax_array = array();
						$dedup_array = explode( $comma, $tax_value );
						foreach ( $dedup_array as $tax_value )
							$tax_array [$tax_value] = $tax_value;
	
						$tax_value = implode( $comma, $tax_array );
					} // ! array( $tax_value )
				} // isset 
				
				$tax_output[$tax_name] = $tax_value;
			} // foreach tax_input

			$_REQUEST['tax_input'] = $tax_output;
		} // ! empty( $_REQUEST['tax_input'] )

		$item_content = apply_filters( 'mla_list_table_inline_action', NULL, $post_id );
		if ( is_null( $item_content ) ) {
			$prevent_default = false;
			$custom_message = '';
		} else {
			$prevent_default = isset( $item_content['prevent_default'] ) ? $item_content['prevent_default'] : false;
			$custom_message = isset( $item_content['message'] ) ? $page_content['message'] : '';
		}

		if ( ! $prevent_default ) {
			MLACore::mla_debug_add( __LINE__ . " MLA::mla_inline_edit_ajax_action( {$post_id} ) \$_REQUEST = " . var_export( $_REQUEST, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );

			$date = array();
			foreach( array( 'aa', 'mm', 'jj', 'hh', 'mn', 'ss' ) as $index ) {
				$date[ $index ] = isset( $_REQUEST[ $index ] ) ? absint( $_REQUEST[ $index ] ) : 0;
			}
			
			$new_date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $date['aa'], $date['mm'], $date['jj'], $date['hh'], $date['mn'], $date['ss'] );
			if ( wp_checkdate( $date['mm'], $date['jj'], $date['aa'], $new_date ) ) {
				$_REQUEST['post_date'] = $new_date;
				$_REQUEST['post_date_gmt'] = get_gmt_from_date( $new_date );
			}

//			$results = MLAData::mla_update_single_item( $post_id, $_REQUEST, $tax_output );
			$results = MLAData::mla_update_single_item( $post_id, $_REQUEST, $_REQUEST['tax_input'] );
			MLACore::mla_debug_add( __LINE__ . " MLA::mla_inline_edit_ajax_action( {$post_id} ) results = " . var_export( $results, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );
		}

		$new_item = (object) MLAData::mla_get_attachment_by_id( $post_id );
		MLACore::mla_debug_add( __LINE__ . " MLA::mla_inline_edit_ajax_action( {$post_id} ) new_item = " . var_export( $new_item, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );

		// Create an instance of our package class and echo the new HTML
		$MLAListTable = apply_filters( 'mla_list_table_new_instance', NULL );
		if ( is_null( $MLAListTable ) ) {
			$MLAListTable = new MLA_List_Table();
		}

		$MLAListTable->single_row( $new_item );
		die(); // this is required to return a proper result
	}

	/**
	 * Compose a Post Type Options list with current selection
 	 *
	 * @since 1.90
	 *
	 * @param	array 	template parts
	 * @param	string 	current selection or 'all' (default)
	 *
	 * @return	string	HTML markup with select field options
	 */
	private static function _compose_post_type_select( &$templates, $selection = 'all' ) {
		$option_template = $templates['post-type-select-option'];
		$option_values = array (
			'selected' => ( 'all' == $selection ) ? 'selected="selected"' : '',
			'value' => 'all',
			'text' => '&mdash; ' . __( 'All Post Types', 'media-library-assistant' ) . ' &mdash;'
		);
		$options = MLAData::mla_parse_template( $option_template, $option_values );

		$post_types = get_post_types( array( 'public' => true ), 'objects' );	
		unset( $post_types['attachment'] );

		foreach ( $post_types as $key => $value ) {
			$option_values = array (
				'selected' => ( $key == $selection ) ? 'selected="selected"' : '',
				'value' => $key,
				'text' => $value->labels->name
			);

			$options .= MLAData::mla_parse_template( $option_template, $option_values );					
		} // foreach post_type

		$select_template = $templates['post-type-select'];
		$select_values = array (
			'options' => $options,
		);
		$select = MLAData::mla_parse_template( $select_template, $select_values );
		return $select;
	} // _compose_post_type_select

	/**
	 * Build the hidden form for the "Set Parent" popup modal window
	 *
	 * @since 1.90
	 *
	 * @param	boolean	true to return complete form, false to return mla-set-parent-div
	 *
	 * @return	string	HTML <form> markup for hidden form
	 */
	public static function mla_set_parent_form( $return_form = true ) {
		$set_parent_template = MLACore::mla_load_template( 'admin-set-parent-form.tpl' );
		if ( ! is_array( $set_parent_template ) ) {
			/* translators: 1: ERROR tag 2: function name 3: non-array value */
			MLACore::mla_debug_add( sprintf( _x( '%1$s: %2$s non-array "%3$s"', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), 'MLA::_build_inline_edit_form', var_export( $set_parent_template, true ) ), MLACore::MLA_DEBUG_CATEGORY_ANY );
			return '';
		}

		$page_values = array(
			'Select Parent' => __( 'Select Parent', 'media-library-assistant' ),
			'Search' => __( 'Search', 'media-library-assistant' ),
			'post_type_dropdown' => self::_compose_post_type_select( $set_parent_template, 'all' ),
			'For' => __( 'For', 'media-library-assistant' ),
			'Previous' => '&laquo;',
			'Next' => '&raquo;',
			'count' => '50',
			'paged' => '1',
			'found' => '0',
			'Title' => __( 'Title', 'media-library-assistant' ),
			'Type' => __( 'Type', 'media-library-assistant' ),
			'Date' => __( 'Date', 'media-library-assistant' ),
			'Status' => __( 'Status', 'media-library-assistant' ),
			'Unattached' => __( 'Unattached', 'media-library-assistant' ),
			'mla_find_posts_nonce' => wp_nonce_field( MLACore::JAVASCRIPT_FIND_POSTS_SLUG, 'mla-set-parent-ajax-nonce', false, false ),
		);

		ob_start();
		submit_button( __( 'Cancel', 'media-library-assistant' ), 'button-secondary cancel alignleft', 'mla-set-parent-cancel', false );
		$page_values['mla_set_parent_cancel'] = ob_get_clean();

		ob_start();
		submit_button( __( 'Update', 'media-library-assistant' ), 'button-primary alignright', 'mla-set-parent-submit', false );
		$page_values['mla_set_parent_update'] = ob_get_clean();

		$set_parent_div = MLAData::mla_parse_template( $set_parent_template['mla-set-parent-div'], $page_values );

		if ( ! $return_form ) {
			return $set_parent_div;
		}

		$page_values = array(
			'mla_set_parent_url' => esc_url( add_query_arg( array_merge( MLA_List_Table::mla_submenu_arguments( false ), array( 'page' => MLACore::ADMIN_PAGE_SLUG ) ), admin_url( 'upload.php' ) ) ),
			'mla_set_parent_action' => MLACore::MLA_ADMIN_SET_PARENT,
			'wpnonce' => wp_nonce_field( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME, true, false ),
			'mla_set_parent_div' => $set_parent_div,
		);

		$set_parent_form = MLAData::mla_parse_template( $set_parent_template['mla-set-parent-form'], $page_values );

		return $set_parent_form;
	}

	/**
	 * Generate HTML form elements for editing uploaded on date
	 *
	 * Adapted from /wp-admin/includes/template.php function touch_time()
	 *
	 * @since 2.71
	 *
	 * @global WP_Locale $wp_locale for month name abbreviations
	 *
	 * @return string HTML markup for the Uploaded on edit form
	 */
	private static function _generate_time_edit_form() {
		global $wp_locale;

		$current = current_time('timestamp');
		$jj = gmdate( 'd', $current );
		$mm = gmdate( 'm', $current );
		$aa = gmdate( 'Y', $current );
		$hh = gmdate( 'H', $current );
		$mn = gmdate( 'i', $current );
		$ss = gmdate( 's', $current );

		$month = '<label><span class="screen-reader-text">' . __( 'Month' ) . '</span><select name="mm" ' . ">\n";
		for ( $i = 1; $i < 13; $i = $i +1 ) {
			$monthnum = zeroise($i, 2);
			$monthtext = $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
			$month .= '  <option value="' . $monthnum . '" data-text="' . $monthtext . '" ' . selected( $monthnum, $mm, false ) . '>';
			/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
			$month .= sprintf( __( '%1$s-%2$s' ), $monthnum, $monthtext ) . "</option>\n";
		}
		$month .= '</select></label>';

		$day = '<label><span class="screen-reader-text">' . __( 'Day' ) . '</span><input type="text" name="jj" value="' . $jj . '" size="2" maxlength="2" autocomplete="off" /></label>';
		$year = '<label><span class="screen-reader-text">' . __( 'Year' ) . '</span><input type="text" name="aa" value="' . $aa . '" size="4" maxlength="4" autocomplete="off" /></label>';
		$hour = '<label><span class="screen-reader-text">' . __( 'Hour' ) . '</span><input type="text" name="hh" value="' . $hh . '" size="2" maxlength="2" autocomplete="off" /></label>';
		$minute = '<label><span class="screen-reader-text">' . __( 'Minute' ) . '</span><input type="text" name="mn" value="' . $mn . '" size="2" maxlength="2" autocomplete="off" /></label>';

		$time_edit_form  = '<fieldset class="inline-edit-date">' . "\n";
		$time_edit_form .= '<legend><span class="title">' . sprintf( __( 'Uploaded on: %s' ), '' ) . '</span></legend>' . "\n";
		$time_edit_form .= '<div class="timestamp-wrap">' . "\n";
		$time_edit_form .= sprintf( __( '%1$s %2$s, %3$s @ %4$s:%5$s' ), $month, $day, $year, $hour, $minute );

		$time_edit_form .= "</div>\n";
		$time_edit_form .= '<input type="hidden" name="ss" value="' . $ss . '" />' . "\n";
		$time_edit_form .= "</fieldset>\n";

		return $time_edit_form;
	}

	/**
	 * Suppress term_meta cache update for taxonomy checklists
	 *
	 * @since 2.94
	 *
	 * @param array    $args       An array of get_terms() arguments.
	 * @param string[] $taxonomies An array of taxonomy names.
	 */
	public static function mla_get_terms_args_filter( $args, $taxonomies ) {
		$args['update_term_meta_cache'] = false;
		return $args;
	}

	/**
	 * Build the hidden row templates for inline editing (quick and bulk edit)
	 *
	 * inspired by inline_edit() in wp-admin\includes\class-wp-posts-list-table.php.
	 *
	 * @since 0.20
	 *
	 * @param	object	MLA List Table object
	 *
	 * @return	string	HTML <form> markup for hidden rows
	 */
	private static function _build_inline_edit_form( $MLAListTable ) {
		$taxonomies = get_object_taxonomies( 'attachment', 'objects' );

		$hierarchical_taxonomies = array();
		$flat_taxonomies = array();
		foreach ( $taxonomies as $tax_name => $tax_object ) {
			if ( $tax_object->show_ui && MLACore::mla_taxonomy_support($tax_name, 'quick-edit') ) {
				if ( $tax_object->hierarchical ) {
					$hierarchical_taxonomies[$tax_name] = $tax_object;
				} else {
					$flat_taxonomies[$tax_name] = $tax_object;
				}
			}
		}

		$page_template_array = MLACore::mla_load_template( 'admin-inline-edit-form.tpl' );
		if ( ! is_array( $page_template_array ) ) {
			/* translators: 1: ERROR tag 2: function name 3: non-array value */
			MLACore::mla_debug_add( sprintf( _x( '%1$s: %2$s non-array "%3$s"', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), 'MLA::_build_inline_edit_form', var_export( $page_template_array, true ) ), MLACore::MLA_DEBUG_CATEGORY_ANY );
			return '';
		}

		$fieldset_template_array = MLACore::mla_load_template( 'mla-bulk-edit-fieldsets.tpl' );
		if ( ! is_array( $fieldset_template_array ) ) {
			/* translators: 1: ERROR tag 2: function name 3: non-array value */
			MLACore::mla_debug_add( sprintf( _x( '%1$s: %2$s non-array "%3$s"', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), 'MLA::_build_inline_edit_form', var_export( $fieldset_template_array, true ) ), MLACore::MLA_DEBUG_CATEGORY_ANY );
			return '';
		}

		if ( $authors = self::mla_authors_dropdown() ) {
			$authors_dropdown  = '              <label class="inline-edit-author">' . "\n";
			$authors_dropdown .= '                <span class="title">' . __( 'Author', 'media-library-assistant' ) . '</span>' . "\n";
			$authors_dropdown .= $authors . "\n";
			$authors_dropdown .= '              </label>' . "\n";
		} else {
			$authors_dropdown = '';
		}

		$custom_fields = '';
		foreach ( MLACore::mla_custom_field_support( 'quick_edit' ) as $slug => $details ) {
			  $page_values = array(
				  'slug' => $slug,
				  'label' => esc_attr( $details['name'] ),
				  'value' => '',
			  );
			  $custom_fields .= MLAData::mla_parse_template( $fieldset_template_array['custom_field'], $page_values );
		}

		// The middle column contains the hierarchical taxonomies, e.g., Att. Category
		$quick_middle_column = '';

		if ( count( $hierarchical_taxonomies ) ) {
			$quick_category_blocks = '';

			foreach ( $hierarchical_taxonomies as $tax_name => $tax_object ) {
				if ( current_user_can( $tax_object->cap->assign_terms ) ) {
					ob_start();
					add_filter( 'get_terms_args', 'MLA::mla_get_terms_args_filter', 10, 2 );
					wp_terms_checklist( NULL, array( 'taxonomy' => $tax_name, 'popular_cats' => array(), ) );
					remove_filter( 'get_terms_args', 'MLA::mla_get_terms_args_filter', 10, 2 );
					$tax_checklist = ob_get_contents();
					ob_end_clean();
					
					if ( MLACore::mla_taxonomy_support( $tax_name, 'checklist-add-term' ) ) {
						$page_values = array(
							'tax_attr' => esc_attr( $tax_name ),
							'Add New Term' => __( '+&nbsp;Add&nbsp;New&nbsp;Term', 'media-library-assistant' ),
							'Add Reader' => __( 'Add New', 'media-library-assistant' ) . ' ' . esc_html( $tax_object->labels->singular_name ),
							'tax_parents' => wp_dropdown_categories( array( 'taxonomy' => $tax_name, 'hide_empty' => 0, 'name' => "new{$tax_name}_parent", 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => '&mdash; ' . $tax_object->labels->parent_item . ' &mdash;', 'echo' => 0, 'update_term_meta_cache' => false ) ),
							'Add Button' => esc_html( $tax_object->labels->add_new_item ),
							'ajax_nonce_field' => wp_nonce_field( 'add-'.$tax_name, '_ajax_nonce-add-'.$tax_name, false, false ),
						);
					
						$category_add_link = MLAData::mla_parse_template( $fieldset_template_array['category_add_link'], $page_values );
						$category_adder = MLAData::mla_parse_template( $fieldset_template_array['category_adder'], $page_values );
					} else {
						$category_add_link = '';
						$category_adder = '';
					}
					
					$page_values = array(
						'tax_html' => esc_html( $tax_object->labels->name ),
						'tax_attr' => esc_attr( $tax_name ),
						'tax_checklist' => $tax_checklist,
						'category_add_link' => $category_add_link,
						'Search' => __( '?&nbsp;Search', 'media-library-assistant' ),
						'category_adder' => $category_adder,
						'Search Reader' => __( 'Search', 'media-library-assistant' ) . ' ' . esc_html( $tax_object->labels->name ),
						'tax_add_checked' => 'checked="checked"',
						'tax_remove_checked' => '',
						'tax_replace_checked' => '',
						'Add' => __( 'Add', 'media-library-assistant' ),
						'Remove' => __( 'Remove', 'media-library-assistant' ),
						'Replace' => __( 'Replace', 'media-library-assistant' ),
					);
					$category_block = MLAData::mla_parse_template( $fieldset_template_array['category_block'], $page_values );
					$taxonomy_options = MLAData::mla_parse_template( $fieldset_template_array['taxonomy_options'], $page_values );
					
					$quick_category_blocks .= $category_block;
				} // current_user_can
			} // foreach $hierarchical_taxonomies

			$page_values = array(
				'category_fieldset_column' => 'center',
				'category_blocks' => $quick_category_blocks
			);
			$quick_middle_column = MLAData::mla_parse_template( $fieldset_template_array['category_fieldset'], $page_values );
		} // count( $hierarchical_taxonomies )

		// The right-hand column contains the flat taxonomies, e.g., Att. Tag
		$quick_right_column = '';

		if ( count( $flat_taxonomies ) ) {
			$quick_tag_blocks = '';

			foreach ( $flat_taxonomies as $tax_name => $tax_object ) {
				if ( current_user_can( $tax_object->cap->assign_terms ) ) {
					if ( MLACore::mla_taxonomy_support( $tax_name, 'flat-checklist' ) ) {
						ob_start();
						add_filter( 'get_terms_args', 'MLA::mla_get_terms_args_filter', 10, 2 );
						wp_terms_checklist( NULL, array( 'taxonomy' => $tax_name, 'popular_cats' => array(), ) );
						remove_filter( 'get_terms_args', 'MLA::mla_get_terms_args_filter', 10, 2 );
						$tax_checklist = ob_get_contents();
						ob_end_clean();
						
						if ( MLACore::mla_taxonomy_support( $tax_name, 'checklist-add-term' ) ) {
							$page_values = array(
								'tax_attr' => esc_attr( $tax_name ),
								'Add New Term' => __( '+&nbsp;Add&nbsp;New&nbsp;Term', 'media-library-assistant' ),
								'Add Reader' => __( 'Add New', 'media-library-assistant' ) . ' ' . esc_html( $tax_object->labels->singular_name ),
								'tax_parents' => "<input type='hidden' name='new{$tax_name}_parent' id='new{$tax_name}_parent' value='-1' />",
								'Add Button' => esc_html( $tax_object->labels->add_new_item ),
								'ajax_nonce_field' => wp_nonce_field( 'add-'.$tax_name, '_ajax_nonce-add-'.$tax_name, false, false ),
							);
						
							$category_add_link = MLAData::mla_parse_template( $fieldset_template_array['category_add_link'], $page_values );
							$category_adder = MLAData::mla_parse_template( $fieldset_template_array['category_adder'], $page_values );
						} else {
							$category_add_link = '';
							$category_adder = '';
						}
					
						$page_values = array(
							'tax_html' => esc_html( $tax_object->labels->name ),
							'tax_attr' => esc_attr( $tax_name ),
							'tax_checklist' => $tax_checklist,
							'category_add_link' => $category_add_link,
							'Search' => __( '?&nbsp;Search', 'media-library-assistant' ),
							'category_adder' => $category_adder,
							'Search Reader' => __( 'Search', 'media-library-assistant' ) . ' ' . esc_html( $tax_object->labels->name ),
							'tax_add_checked' => 'checked="checked"',
							'tax_remove_checked' => '',
							'tax_replace_checked' => '',
							'Add' => __( 'Add', 'media-library-assistant' ),
							'Remove' => __( 'Remove', 'media-library-assistant' ),
							'Replace' => __( 'Replace', 'media-library-assistant' ),
						);
						$tag_block = MLAData::mla_parse_template( $fieldset_template_array['category_block'], $page_values );
					} else {
						$page_values = array(
							'tax_html' => esc_html( $tax_object->labels->name ),
							'tax_attr' => esc_attr( $tax_name ),
							'tax_value' => '',
							'tax_add_checked' => 'checked="checked"',
							'tax_remove_checked' => '',
							'tax_replace_checked' => '',
							'Add' => __( 'Add', 'media-library-assistant' ),
							'Remove' => __( 'Remove', 'media-library-assistant' ),
							'Replace' => __( 'Replace', 'media-library-assistant' ),
						);
						$tag_block = MLAData::mla_parse_template( $fieldset_template_array['tag_block'], $page_values );
					}

					$taxonomy_options = MLAData::mla_parse_template( $fieldset_template_array['taxonomy_options'], $page_values );

				$quick_tag_blocks .= $tag_block;
				} // current_user_can
			} // foreach $flat_taxonomies

			$page_values = array(
				'tag_fieldset_column' => 'right',
				'tag_blocks' => $quick_tag_blocks
			);
			$quick_right_column = MLAData::mla_parse_template( $fieldset_template_array['tag_fieldset'], $page_values );
		} // count( $flat_taxonomies )

		// Get a "blank" presets array for the blank and initial fieldsets
		$fieldset_values = MLAEdit::mla_get_bulk_edit_form_presets( MLACoreOptions::MLA_BULK_EDIT_PRESETS, true );

		// Format and filter the blank/reset fieldset values
		$blank_div_content = MLAEdit::mla_generate_bulk_edit_form_fieldsets( $fieldset_values, 'mla_list_table_inline_blank' );

		// Format and filter the initial fieldset values
		$initial_div_content = MLAEdit::mla_generate_bulk_edit_form_fieldsets( $fieldset_values, 'mla_list_table_inline_initial' );
//error_log( __LINE__ . ' MLA::_build_inline_edit_form initial_div_content = ' . var_export( $initial_div_content, true ), 0 );

		// Populate the import/export saved fieldset values, if any
		$fieldset_values = MLAEdit::mla_get_bulk_edit_form_presets( MLACoreOptions::MLA_BULK_EDIT_PRESETS );
//$fieldset_values['post_title'] = 'Preset Title';
//error_log( __LINE__ . ' MLA::_build_inline_edit_form preset_values = ' . var_export( $fieldset_values, true ), 0 );

		$preset_div_content = MLAEdit::mla_generate_bulk_edit_form_fieldsets( $fieldset_values, 'mla_list_table_inline_preset' );
//error_log( __LINE__ . ' MLA::_build_inline_edit_form preset_div_content = ' . var_export( $preset_div_content, true ), 0 );

		$set_parent_form = MLA::mla_set_parent_form();

		if ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_BULK_EDITOR ) ) {
			$quicktags_settings = array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close' );
			ob_start();
			wp_editor( '', 'post_content', array( 'media_buttons' => false, 'tinymce' => false, 'textarea_rows' => 5, 'quicktags' => $quicktags_settings ) );
			$description_field = ob_get_contents();
			ob_end_clean();
		} else {
			$description_field = '<textarea class="widefat" name="post_content"></textarea>';
		}

		$page_values = array(
			'filter_root' => 'mla_list_table_inline',
			'colspan' => $MLAListTable->get_column_count(),
			'Quick Edit' => __( 'Quick Edit', 'media-library-assistant' ),
			'Title' => __( 'Title', 'media-library-assistant' ),
			'Name/Slug' => __( 'Name/Slug', 'media-library-assistant' ),
			'Caption' => __( 'Caption', 'media-library-assistant' ),
			'Description' => __( 'Description', 'media-library-assistant' ),
			'description_field' => $description_field,
			'ALT Text' => __( 'ALT Text', 'media-library-assistant' ),
			'Uploaded on' => self::_generate_time_edit_form(),
			'Bulk Uploaded on' => __( 'Uploaded on', 'media-library-assistant' ),
			'Parent ID' => __( 'Parent ID', 'media-library-assistant' ),
			'Select' => __( 'Select', 'media-library-assistant' ),
			'Menu Order' => __( 'Menu Order', 'media-library-assistant' ),
			'authors' => $authors_dropdown,
			'quick_middle_column' => $quick_middle_column,
			'quick_right_column' => $quick_right_column,
			'custom_fields' => $custom_fields,
			'Cancel' => __( 'Cancel', 'media-library-assistant' ),
			'Update' => __( 'Update', 'media-library-assistant' ),
			'preset_div_content' => $preset_div_content,
			'blank_div_content' => $blank_div_content,
			'initial_div_content' => $initial_div_content,
			'Bulk Edit' => __( 'Bulk Edit', 'media-library-assistant' ),
			'Reset' => __( 'Reset', 'media-library-assistant' ),
			'Import' => __( 'Import', 'media-library-assistant' ),
			'Export' => __( 'Export', 'media-library-assistant' ),
			'bulk_map_style' => '',
			'Map IPTC/EXIF metadata' =>  __( 'Map IPTC/EXIF metadata', 'media-library-assistant' ),
			'bulk_custom_field_map_style' => '',
			'Map Custom Field metadata' =>  __( 'Map Custom Field metadata', 'media-library-assistant' ),
			'Bulk Waiting' =>  __( 'Waiting', 'media-library-assistant' ),
			'Bulk Running' =>  __( 'In-process', 'media-library-assistant' ),
			'Bulk Complete' =>  __( 'Complete', 'media-library-assistant' ),
			'Refresh' =>  __( 'Refresh', 'media-library-assistant' ),
			'set_parent_form' => $set_parent_form,
		);

		if ( 'checked' != MLACore::mla_get_option( MLACoreOptions::MLA_ALLOW_CUSTOM_FIELD_MAPPING ) ) {
			$page_values['bulk_custom_field_map_style'] = 'display: none';
		}

		if ( 'checked' != MLACore::mla_get_option( MLACoreOptions::MLA_ALLOW_IPTC_EXIF_MAPPING ) ) {
			$page_values['bulk_map_style'] = 'display: none';
		}

		$page_values = apply_filters( 'mla_list_table_inline_values', $page_values );
		$page_template = apply_filters( 'mla_list_table_inline_template', $page_template_array['page'] );
		$parse_value = MLAData::mla_parse_template( $page_template, $page_values );
		return apply_filters( 'mla_list_table_inline_parse', $parse_value, $page_template, $page_values );
	}

	/**
	 * Get the edit Authors dropdown box, if user has suitable permissions
	 *
	 * @since 0.20
	 *
	 * @param	integer	Optional User ID of the current author, default 0
	 * @param	string	Optional HTML name attribute, default 'post_author'
	 * @param	string	Optional HTML class attribute, default 'authors'
	 *
	 * @return string|false HTML markup for the dropdown field or False
	 */
	public static function mla_authors_dropdown( $author = 0, $name = 'post_author', $class = 'authors' ) {
		$post_type_object = get_post_type_object('attachment');
		if ( is_super_admin() || current_user_can( $post_type_object->cap->edit_others_posts ) ) {
			$users_opt = array(
				'hide_if_only_one_author' => false,
				'name' => $name,
				'class'=> $class,
				'multi' => 1,
				'echo' => 0
			);

			// 'who' => 'authors', deprecated in WP 5.9
			if ( version_compare( get_bloginfo('version'), '5.8.99', '>' ) ) {
				$users_opt['capability'] = array( $post_type_object->cap->edit_posts );
			} else {
				$users_opt['who'] = 'authors';
			}

			if ( $author > 0 ) {
				$users_opt['selected'] = $author;
				$users_opt['include_selected'] = true;
			} elseif ( -1 == $author ) {
				$users_opt['show_option_none'] = '&mdash; ' . __( 'No Change', 'media-library-assistant' ) . ' &mdash;';
			}

			if ( $authors = wp_dropdown_users( $users_opt ) ) {
				return $authors;
			}
		}

		return false;
	}

	/**
	 * Get the current action selected from the bulk actions dropdown
	 *
	 * @since 0.1
	 *
	 * @return string|false The action name or False if no action was selected
	 */
	private static function _current_bulk_action( )	{
		$action = false;

		if ( isset( $_REQUEST['action'] ) ) {
			if ( -1 != $_REQUEST['action'] ) {
				return sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
			} else {
				$action = 'none';
			}
		} // isset action

		if ( isset( $_REQUEST['action2'] ) ) {
			if ( -1 != $_REQUEST['action2'] ) {
				return sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) );
			} else {
				$action = 'none';
			}
		} // isset action2

		return $action;
	}

	/**
	 * Delete a single item permanently
	 * 
	 * @since 0.1
	 * 
	 * @param	array The form POST data
	 *
	 * @return	array success/failure message and NULL content
	 */
	private static function _delete_single_item( $post_id ) {
		if ( !current_user_can( 'delete_post', $post_id ) ) {
			return array(
				'message' => __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'You are not allowed to delete this item.', 'media-library-assistant' ),
				'body' => '' 
			);
		}

		if ( !wp_delete_attachment( $post_id, true ) ) {
			return array(
				/* translators: 1: ERROR tag 2: post ID */
				'message' => sprintf( __( '%1$s: Item %2$d could NOT be deleted.', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), $post_id ),
				'body' => '' 
			);
		}

		return array(
			/* translators: 1: post ID */
			'message' => sprintf( __( 'Item %1$d permanently deleted.', 'media-library-assistant' ), $post_id ),
			'body' => '' 
		);
	}

	/**
	 * Restore a single item from the Trash
	 * 
	 * @since 0.1
	 * 
	 * @param	integer	The WordPress Post ID of the attachment item
	 *
	 * @return	array	success/failure message and NULL content
	 */
	private static function _restore_single_item( $post_id ) {
		if ( !current_user_can( 'delete_post', $post_id ) ) {
			return array(
				'message' => __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'You are not allowed to move this item out of the Trash.', 'media-library-assistant' ),
				'body' => '' 
			);
		}

		if ( !wp_untrash_post( $post_id ) ) {
			return array(
				/* translators: 1: ERROR tag 2: post ID */
				'message' => sprintf( __( '%1$s: Item %2$d could NOT be restored from Trash.', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), $post_id ),
				'body' => '' 
			);
		}

		/*
		 * Posts are restored to "draft" status, so this must be updated.
		 */
		$update_post = array();
		$update_post['ID'] = $post_id;
		$update_post['post_status'] = 'inherit';
		wp_update_post( $update_post );

		return array(
			/* translators: 1: post ID */
			'message' => sprintf( __( 'Item %1$d restored from Trash.', 'media-library-assistant' ), $post_id ),
			'body' => '' 
		);
	}

	/**
	 * Move a single item to Trash
	 * 
	 * @since 0.1
	 * 
	 * @param	integer	The WordPress Post ID of the attachment item
	 *
	 * @return	array	success/failure message and NULL content
	 */
	private static function _trash_single_item( $post_id ) {
		if ( !current_user_can( 'delete_post', $post_id ) ) {
			return array(
				'message' => __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'You are not allowed to move this item to the Trash.', 'media-library-assistant' ),
				'body' => '' 
			);
		}

		if ( !wp_trash_post( $post_id, false ) ) {
			return array(
				/* translators: 1: ERROR tag 2: post ID */
				'message' => sprintf( __( '%1$s: Item %2$d could NOT be moved to Trash.', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), $post_id ),
				'body' => '' 
			);
		}

		return array(
			/* translators: 1: post ID */
			'message' => sprintf( __( 'Item %1$d moved to Trash.', 'media-library-assistant' ), $post_id ),
			'body' => '' 
		);
	}
} // class MLA
?>
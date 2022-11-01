<?php
/**
 * Media Library Assistant Ajax Handlers
 *
 * @package Media Library Assistant
 * @since 2.20
 */

/**
 * Class MLA (Media Library Assistant) Ajax contains handlers for simple Ajax requests
 *
 * @package Media Library Assistant
 * @since 2.20
 */
class MLA_Ajax {
	/**
	 * True if limiting MLA to AJAX support, false if loading all of MLA
	 *
	 * Recorded here for debug logging purposes; set in mla-plugin-loader.php.
	 *
	 * @since 2.50
	 *
	 * @var	boolean
	 */
	public static $ajax_only = NULL;

	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 2.20
	 *
	 * @return	void
	 */
	public static function initialize() {
		if ( ! ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'heartbeat' ) ) {
			$ajax_only = var_export( self::$ajax_only, true );
			MLACore::mla_debug_add( __LINE__ . " MLA_Ajax::initialize( {$ajax_only} ) \$_REQUEST = " . var_export( $_REQUEST, true ), ( MLACore::MLA_DEBUG_CATEGORY_AJAX | MLACore::MLA_DEBUG_CATEGORY_MMMW ) );
		}
		
		// If there's no action variable, we have nothing more to do
		if ( ! isset( $_REQUEST['action'] ) ) {
			return;
		}

		// Defined here because the "admin_init" action is not called for item transfers
		if ( 'mla_named_transfer' ==  $_REQUEST['action'] ) {
			add_action( 'wp_ajax_' . 'mla_named_transfer', 'MLA_Ajax::mla_named_transfer_ajax_action' );
			add_action( 'wp_ajax_nopriv_' . 'mla_named_transfer', 'MLA_Ajax::mla_named_transfer_ajax_action' );
		} else {
			add_action( 'admin_init', 'MLA_Ajax::mla_admin_init_action' );
		}

		if ( 'query-attachments' ==  $_REQUEST['action'] ) {
			if ( ( MLACore::$mla_debug_level & 1 ) && ( MLACore::$mla_debug_level & MLACore::MLA_DEBUG_CATEGORY_MMMW ) ) {
				add_filter( 'posts_clauses', 'MLA_Ajax::mla_mmmw_query_posts_clauses_filter', 0x7FFFFFFF, 1 );
				add_filter( 'posts_clauses_request', 'MLA_Ajax::mla_mmmw_query_posts_clauses_request_filter', 0x7FFFFFFF, 1 );
				add_filter( 'posts_request', 'MLA_Ajax::mla_mmmw_query_posts_request_filter', 0x7FFFFFFF, 1 );
				add_filter( 'posts_results', 'MLA_Ajax::mla_mmmw_query_posts_results_filter', 0x7FFFFFFF, 2 );
				add_filter( 'the_posts', 'MLA_Ajax::mla_mmmw_query_the_posts_filter', 0x7FFFFFFF, 2 );
				MLACore::mla_debug_mode( 'log' );
			} // debug
		} // query_attachments
	}

	/**
	 * Filters all clauses for shortcode queries, pre caching plugins
	 * 
	 * This is for debug purposes only.
	 * Defined as public because it's a filter.
	 *
	 * @since 2.84
	 *
	 * @param	array	query clauses before modification
	 */
	public static function mla_mmmw_query_posts_clauses_filter( $pieces ) {
		/* translators: 1: DEBUG tag 2: SQL clauses */
		MLACore::mla_debug_add( sprintf( _x( '%1$s: mla_mmmw_query_posts_clauses_filter = "%2$s".', 'error_log', 'media-library-assistant' ), __( 'DEBUG', 'media-library-assistant' ), var_export( $pieces, true ) ) );

		return $pieces;
	}

	/**
	 * Filters all clauses for shortcode queries, post caching plugins
	 * 
	 * This is for debug purposes only.
	 * Defined as public because it's a filter.
	 *
	 * @since 2.84
	 *
	 * @param	array	query clauses before modification
	 */
	public static function mla_mmmw_query_posts_clauses_request_filter( $pieces ) {
		/* translators: 1: DEBUG tag 2: SQL clauses */
		MLACore::mla_debug_add( sprintf( _x( '%1$s: mla_mmmw_query_posts_clauses_request_filter = "%2$s".', 'error_log', 'media-library-assistant' ), __( 'DEBUG', 'media-library-assistant' ), var_export( $pieces, true ) ) );

		return $pieces;
	}

	/**
	 * Filters the completed SQL query before sending
	 * 
	 * This is for debug purposes only.
	 * Defined as public because it's a filter.
	 *
	 * @since 2.84
	 *
	 * @param	array	SQL query before sending
	 */
	public static function mla_mmmw_query_posts_request_filter( $request ) {
		/* translators: 1: DEBUG tag 2: SQL clauses */
		MLACore::mla_debug_add( sprintf( _x( '%1$s: mla_mmmw_query_posts_request_filter = "%2$s".', 'error_log', 'media-library-assistant' ), __( 'DEBUG', 'media-library-assistant' ), var_export( $request, true ) ) );

		return $request;
	}

	/**
	 * Filters the raw post results array, prior to status checks.
	 * 
	 * This is for debug purposes only.
	 * Defined as public because it's a filter.
	 *
	 * @since 2.84
	 *
	 * @param WP_Post[] $posts Array of post objects.
	 * @param WP_Query  $wp_query  The WP_Query instance (passed by reference).
	 */
	public static function mla_mmmw_query_posts_results_filter( $posts, $wp_query ) {
		/* translators: 1: DEBUG tag 2: SQL clauses */
		MLACore::mla_debug_add( sprintf( _x( '%1$s: mla_mmmw_query_posts_results_filter post_count = "%2$d", found_posts = "%3$d" count = %4$d.', 'error_log', 'media-library-assistant' ), __( 'DEBUG', 'media-library-assistant' ), $wp_query->post_count, $wp_query->found_posts, count( $posts ) ) );

		return $posts;
	}

	/**
	 * Filters the array of retrieved posts after they've been fetched and
	 * internally processed.
	 * 
	 * This is for debug purposes only.
	 * Defined as public because it's a filter.
	 *
	 * @since 2.84
	 *
	 * @param WP_Post[] $posts Array of post objects.
	 * @param WP_Query  $wp_query  The WP_Query instance (passed by reference).
	 */
	public static function mla_mmmw_query_the_posts_filter( $posts, $wp_query ) {
		/* translators: 1: DEBUG tag 2: SQL clauses */
		MLACore::mla_debug_add( sprintf( _x( '%1$s: mla_mmmw_query_the_posts_filter post_count = "%2$d", found_posts = "%3$d" count = %4$d.', 'error_log', 'media-library-assistant' ), __( 'DEBUG', 'media-library-assistant' ), $wp_query->post_count, $wp_query->found_posts, count( $posts ) ) );

		return $posts;
	}

	/**
	 * Adds flat checklist taxonomy support to the Media Manager Modal Window.
	 * Declared public because it is an action.
	 *
	 * @since 2.20
	 */
	public static function mla_admin_init_action( ) {
		/*
		 * For flat taxonomies that use the checklist meta box, substitute our own handler
		 * for /wp-admin/includes/ajax-actions.php function _wp_ajax_add_hierarchical_term().
		 */
		if ( defined('DOING_AJAX') && DOING_AJAX ) {
			$action = sanitize_text_field( isset( $_REQUEST['action'] ) ? wp_unslash( $_REQUEST['action'] ) : '' );
			if (  'add-' === substr( $action, 0, 4 ) ) {
				$key = substr( $action, 4 );
				if ( MLACore::mla_taxonomy_support( $key, 'flat-checklist' ) ) {
					// note: this function sends an Ajax response and then dies; no return
					self::_mla_ajax_add_flat_term( $key );
				}
			}
		}

		add_action( 'wp_ajax_' . MLACore::JAVASCRIPT_EXPORT_PRESETS_SLUG, 'MLA_Ajax::mla_bulk_edit_form_presets_action' );
		add_action( 'wp_ajax_' . MLACore::JAVASCRIPT_FIND_POSTS_SLUG, 'MLA_Ajax::mla_find_posts_ajax_action' );
		add_action( 'wp_ajax_' . MLACore::JAVASCRIPT_INLINE_EDIT_SLUG . '-set-parent', 'MLA_Ajax::mla_set_parent_ajax_action' );
	}

	/**
	 * Add flat taxonomy term from "checklist" meta box on the Media Manager Modal Window
	 *
	 * Adapted from the WordPress post_categories_meta_box() in /wp-admin/includes/meta-boxes.php.
	 *
	 * @since 2.20
	 *
	 * @param string The taxonomy name, from $_REQUEST['action']
	 *
	 * @return void Sends JSON response with updated HTML for the checklist
	 */
	private static function _mla_ajax_add_flat_term( $key ) {
		$taxonomy = get_taxonomy( $key );
		check_ajax_referer( sanitize_text_field( isset( $_REQUEST['action'] ) ? wp_unslash( $_REQUEST['action'] ) : '' ), '_ajax_nonce-add-' . $key, true );

		if ( !current_user_can( $taxonomy->cap->edit_terms ) ) {
			wp_die( -1 );
		}

		$new_names = sanitize_text_field( isset( $_POST[ 'new' . $key ] ) ? wp_unslash( $_POST[ 'new' . $key ] ) : '' );
		$new_names = explode( ',', $new_names );
		$new_terms_markup = '';
		foreach( $new_names as $name ) {
			if ( '' === sanitize_title( $name ) ) {
				continue;
			}

			if ( ! $id = term_exists( $name, $key ) ) {
				$id = wp_insert_term( $name, $key );
			}

			if ( is_wp_error( $id ) ) {
				continue;
			}

			if ( is_array( $id ) ) {
				$id = absint( $id['term_id'] );
			} else {
				continue;
			}
			$term = get_term( $id, $key );
			$name = $term->name;
			$new_terms_markup .= "<li id='{$key}-{$id}'><label class='selectit'><input value='{$name}' type='checkbox' name='tax_input[{$key}][]' id='in-{$key}-{$id}' checked='checked' />{$name}</label></li>\n";
		} // foreach new_name

		$input_new_parent_name = "new{$key}_parent";
		$supplemental = "<input type='hidden' name='{$input_new_parent_name}' id='{$input_new_parent_name}' value='-1' />";	

		$add = array(
			'what' => $key,
			'id' => $id,
			'data' => $new_terms_markup,
			'position' => -1,
			'supplemental' => array( 'newcat_parent' => $supplemental )
		);

		$x = new WP_Ajax_Response( $add );
		$x->send();
	} // _mla_ajax_add_flat_term

	/**
	 * Ajax handler to stream/view or download a Media Library item
	 *
	 * @since 2.63
	 *
	 * @return	void	echo HTML for file streaming or download, then exit()
	 */
	public static function mla_named_transfer_ajax_action() {
		if ( !class_exists( 'MLAFileDownloader' ) ) {
			require_once( pathinfo( __FILE__, PATHINFO_DIRNAME ) . '/class-mla-file-downloader.php' );
		}

		$download_args = array();
			
		if ( empty( $_REQUEST['mla_item'] ) ) {
			$download_args['error'] = 'ERROR: mla_item argument not set.';
		} else {
			$item_name = sanitize_title( isset( $_REQUEST['mla_item'] ) ? wp_unslash( $_REQUEST['mla_item'] ) : '' );
			$args = array(
				'name'           => $item_name,
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1
			);

			$items = get_posts( $args );

			if( $items ) {
				$file = get_attached_file( $items[0]->ID );
				if ( !empty( $file ) ) {
					$download_args['mla_download_file'] = $file;
					$download_args['mla_download_type'] = $items[0]->post_mime_type;
					
					if ( !empty( $_REQUEST['mla_disposition'] ) ) {
						$download_args['mla_disposition'] = sanitize_text_field( wp_unslash( $_REQUEST['mla_disposition'] ) );
					}
				} else {
					$download_args['error'] = 'ERROR: mla_item no attached file.';
				}
			} else {
				$download_args['error'] = 'ERROR: mla_item not found.';
			}
		}
		
		MLAFileDownloader::$mla_debug = 'log' === sanitize_text_field( isset( $_REQUEST['mla_debug'] ) ? wp_unslash( $_REQUEST['mla_debug'] ) : 'false' );
		MLAFileDownloader::mla_process_download_file( $download_args );

		MLACore::mla_debug_add( __LINE__ . " MLA_Ajax::mla_named_transfer_ajax_action failed. \$_REQUEST = " . var_export( $_REQUEST, true ), MLACore::MLA_DEBUG_CATEGORY_AJAX );
		echo "MLA_Ajax::mla_named_transfer_ajax_action failed.";
		die();
	} // mla_named_transfer_ajax_action

	/**
	 * Ajax handler to fetch candidates for the "Set Parent" popup window
	 *
	 * Adapted from wp_ajax_find_posts in /wp-admin/includes/ajax-actions.php.
	 * Adds filters for post type and pagination.
	 *
	 * @since 1.90
	 *
	 * @return	void	passes results to wp_send_json_success() for JSON encoding and transmission
	 */
	public static function mla_bulk_edit_form_presets_action() {
//error_log( __LINE__ . ' MLA_Ajax::mla_bulk_edit_form_presets_action _REQUEST = ' . var_export( $_REQUEST, true ), 0 );
		check_ajax_referer( MLACore::JAVASCRIPT_EXPORT_PRESETS_SLUG, MLACore::MLA_ADMIN_NONCE_NAME );

		if ( empty( $_REQUEST['mla_preset_values'] ) ) {
			wp_send_json_error( 'ERROR: MLA Preset Values String is empty' );
		}

		if ( empty( $_REQUEST['mla_preset_option'] ) ) {
			wp_send_json_error( 'ERROR: MLA Preset Option Name is empty' );
		}

		/*
		 * Clean up the inputs, which have everything from the enclosing <form>.
		 * wp_parse_args converts plus signs to spaces, which we must avoid.
		 */
		if ( current_user_can( 'unfiltered_html' ) ) {
			$args = trim( wp_unslash( $_REQUEST['mla_preset_values'] ) ); // phpcs:ignore
		} else {
			$args = trim( wp_kses( wp_unslash( $_REQUEST['mla_preset_values'] ), 'post' ) );
		}

		$args = wp_parse_args( str_replace( array( '%2B', '&amp;' ), array( 'urlencodedmlaplussign', '&' ), $args ) );
		foreach ( $args as $key => $arg ) {
			if ( is_string( $arg ) ) {
				$args[ $key ] = str_replace( 'urlencodedmlaplussign', '+', $arg );
			}
		}

		/*
		 * The category taxonomy (edit screens) is a special case because 
		 * post_categories_meta_box() changes the input name
		 */
		if ( !isset( $args['tax_input'] ) ) {
			$args['tax_input'] = array();
		}

		if ( isset( $args['post_category'] ) ) {
			$args['tax_input']['category'] = $args['post_category'];
			unset ( $args['post_category'] );
		}

		foreach( $args['tax_input'] as $taxonomy => $terms ) {
			if ( is_array( $terms ) ) {
				$clean_terms = array();
				
				foreach( $terms as $term ) {
					if ( (integer) $term ) {
						$clean_terms[] = (integer) $term;
					}
				}

				if ( !empty( $clean_terms ) ) {
					$args['tax_input'][ $taxonomy ] = $clean_terms;
				} else {
					unset( $args['tax_input'][ $taxonomy ] );
				}
			} else {
				$clean_terms = trim( $terms, ', ' );

				if ( !empty( $clean_terms ) ) {
					$args['tax_input'][ $taxonomy ] = $clean_terms;
				} else {
					unset( $args['tax_input'][ $taxonomy ] );
				}
			}
		}

		$args['custom_fields'] = array();
		foreach (MLACore::mla_custom_field_support( 'bulk_edit' ) as $slug => $details ) {
//error_log( __LINE__ . " MLA_Ajax::mla_bulk_edit_form_presets_action {$slug} = " . var_export( $details, true ), 0 );
			if ( !empty( $args[ $slug ] ) ) {
				$args['custom_fields'][ esc_attr( $details['name'] ) ] = $args[ $slug ];
			}
		}
//error_log( __LINE__ . ' MLA_Ajax::mla_bulk_edit_form_presets_action args = ' . var_export( $args, true ), 0 );

		// Get a "blank" presets array we can fill in with current settings
		$presets = MLAEdit::mla_get_bulk_edit_form_presets( $_REQUEST['mla_preset_option'], true );
//error_log( __LINE__ . ' MLA_Ajax::mla_bulk_edit_form_presets_action presets = ' . var_export( $presets, true ), 0 );

		foreach ( $presets as $key => $value ) {
			if ( !empty( $args[ $key ] ) ) {
				$presets[ $key ] = $args[ $key ];
			}
		}
//error_log( __LINE__ . ' MLA_Ajax::mla_bulk_edit_form_presets_action presets = ' . var_export( $presets, true ), 0 );

		if ( false === MLAEdit::mla_update_bulk_edit_form_presets( $_REQUEST['mla_preset_option'], $presets ) ) {
			// false may simply mean that the value didn't change, so ignore it
			// wp_send_json_error( 'ERROR: Presets update failed' );
		}
	
		$html = MLAEdit::mla_generate_bulk_edit_form_fieldsets( $presets, 'mla_upload_bulk_edit_form_preset' );
		wp_send_json_success( $html );
  	} // mla_bulk_edit_form_presets_action

	/**
	 * Ajax handler to fetch candidates for the "Set Parent" popup window
	 *
	 * Adapted from wp_ajax_find_posts in /wp-admin/includes/ajax-actions.php.
	 * Adds filters for post type and pagination.
	 *
	 * @since 1.90
	 *
	 * @return	void	passes results to wp_send_json_success() for JSON encoding and transmission
	 */
	public static function mla_find_posts_ajax_action() {
		global $wpdb;

		check_ajax_referer( MLACore::JAVASCRIPT_FIND_POSTS_SLUG, MLACore::MLA_ADMIN_NONCE_NAME );

		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		unset( $post_types['attachment'] );

		$s = sanitize_text_field( isset( $_REQUEST['mla_set_parent_search_text'] ) ? wp_unslash( $_REQUEST['mla_set_parent_search_text'] ) : '' );
		$count = isset( $_REQUEST['mla_set_parent_count'] ) ? absint( $_REQUEST['mla_set_parent_count'] ) : 50;
		$paged = isset( $_REQUEST['mla_set_parent_paged'] ) ? absint( $_REQUEST['mla_set_parent_paged'] ) : 1;

		$post_type = sanitize_text_field( isset( $_REQUEST['mla_set_parent_post_type'] ) ? wp_unslash( $_REQUEST['mla_set_parent_post_type'] ) : 'all' );
		$args = array(
			'post_type' => ( 'all' == $post_type ) ? array_keys( $post_types ) : $post_type,
			'post_status' => 'any',
			'posts_per_page' => $count,
			'paged' => $paged,
		);

		if ( '' !== $s )
			$args['s'] = $s;

		$posts = get_posts( $args );

		if ( ( ! $posts ) && $paged > 1 ) {
			$args['paged'] = $paged = 1;
			$posts = get_posts( $args );
		}

		$found = count( $posts );

		$html = '<input name="mla_set_parent_count" id="mla-set-parent-count" type="hidden" value="' . $count . "\">\n";
		$html .= '<input name="mla_set_parent_paged" id="mla-set-parent-paged" type="hidden" value="' . $paged . "\">\n";
		$html .= '<input name="mla_set_parent_found" id="mla-set-parent-found" type="hidden" value="' . $found . "\">\n";

		$html .= '<table class="widefat"><thead><tr><th class="found-radio"><br /></th><th>'.__('Title').'</th><th class="no-break">'.__('Type').'</th><th class="no-break">'.__('Date').'</th><th class="no-break">'.__('Status').'</th></tr></thead><tbody>' . "\n";
		if ( $found ) {
			$alt = '';
			foreach ( $posts as $post ) {
				$title = trim( $post->post_title ) ? $post->post_title : __( '(no title)' );
				$alt = ( 'alternate' == $alt ) ? '' : 'alternate';

				switch ( $post->post_status ) {
					case 'publish' :
					case 'private' :
						$stat = __('Published');
						break;
					case 'future' :
						$stat = __('Scheduled');
						break;
					case 'pending' :
						$stat = __('Pending Review');
						break;
					case 'draft' :
						$stat = __('Draft');
						break;
					default:
						$stat = sanitize_text_field( $post->post_status );
				}

				if ( '0000-00-00 00:00:00' == $post->post_date ) {
					$time = '';
				} else {
					/* translators: date format in table columns, see http://php.net/date */
					$time = mysql2date(__('Y/m/d'), $post->post_date);
				}

				$html .= '<tr class="' . trim( 'found-posts ' . $alt ) . '"><td class="found-radio"><input type="radio" id="found-'.$post->ID.'" name="found_post_id" value="' . esc_attr($post->ID) . '"></td>';
				$html .= '<td><label for="found-'.$post->ID.'">' . esc_html( $title ) . '</label></td><td class="no-break">' . esc_html( $post_types[$post->post_type]->labels->singular_name ) . '</td><td class="no-break">'.esc_html( $time ) . '</td><td class="no-break">' . esc_html( $stat ). ' </td></tr>' . "\n";
			} // foreach post
		} else {
				$html .= '<tr class="' . trim( 'found-posts ' ) . '"><td class="found-radio">&nbsp;</td>';
				$html .= '<td colspan="4">No results found.</td></tr>' . "\n";
		}

		$html .= "</tbody></table>\n";

		wp_send_json_success( $html );
	} // mla_find_posts_ajax_action

	/**
	 * Ajax handler to set post_parent for a single attachment
	 *
	 * Adapted from wp_ajax_inline_save in /wp-admin/includes/ajax-actions.php
	 *
	 * @since 0.20
	 *
	 * @return	void	echo HTML <td> innerHTML for updated call or error message, then die()
	 */
	public static function mla_set_parent_ajax_action() {
		check_ajax_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );

		if ( empty( $_REQUEST['post_ID'] ) ) {
			echo esc_html( __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'No post ID found', 'media-library-assistant' ) );
			die();
		} else {
			$post_id = absint( $_REQUEST['post_ID'] );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html( __( 'ERROR', 'media-library-assistant' ) . ': ' . __( 'You are not allowed to edit this Attachment.', 'media-library-assistant' ) ) );
		}

		if ( ! class_exists( 'MLAData' ) ) {
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-data.php' );
			MLAData::initialize();
		}

		$results = MLAData::mla_update_single_item( $post_id, $_REQUEST );
		if ( false !== strpos( $results['message'], __( 'ERROR', 'media-library-assistant' ) ) ) {
			wp_die( esc_html( $results['message'] ) );
		}

		$new_item = (object) MLAData::mla_get_attachment_by_id( $post_id );

		if ( ! class_exists( 'MLA_List_Table' ) ) {
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-list-table.php' );
			MLA_List_Table::mla_admin_init_action();

			// Check for multi-language table column support
			global $sitepress, $polylang;
			
			if ( is_object( $sitepress ) ) {
				require_once( MLA_PLUGIN_PATH . 'includes/class-mla-wpml-support.php' );
				MLA_WPML::initialize();
				MLA_WPML::admin_init(); // This action has already passed.
			} elseif ( is_object( $polylang ) ) {
				require_once( MLA_PLUGIN_PATH . 'includes/class-mla-polylang-support.php' );
				MLA_Polylang::initialize();
				MLA_Polylang::admin_init();
			}
		}

		//	Create an instance of our package class and echo the new HTML
		$MLAListTable = apply_filters( 'mla_list_table_new_instance', NULL );
		if ( is_null( $MLAListTable ) ) {
			$MLAListTable = new MLA_List_Table();
		}

		$MLAListTable->single_row( $new_item );
		die(); // this is required to return a proper result
	} // mla_set_parent_ajax_action
} // Class MLA_Ajax

// Check for Media Manager Enhancements
if ( ( ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_MEDIA_MODAL_TOOLBAR ) ) || ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_MEDIA_GRID_TOOLBAR ) ) ) ) {
	require_once( MLA_PLUGIN_PATH . 'includes/class-mla-media-modal-ajax.php' );
	add_action( 'init', 'MLAModal_Ajax::initialize', 0x7FFFFFFF );
}
?>
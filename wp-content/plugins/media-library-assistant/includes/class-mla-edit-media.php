<?php
/**
 * Media Library Assistant Edit Media screen enhancements
 *
 * @package Media Library Assistant
 * @since 0.80
 */

/**
 * Class MLA (Media Library Assistant) Edit contains meta boxes for the Edit Media (advanced-form-edit.php) screen
 *
 * @package Media Library Assistant
 * @since 0.80
 */
class MLAEdit {
	/**
	 * Slug for localizing and enqueueing CSS - Add Media and related dialogs
	 *
	 * @since 1.20
	 *
	 * @var	string
	 */
	const JAVASCRIPT_EDIT_MEDIA_STYLES = 'mla-edit-media-style';

	/**
	 * Slug for localizing and enqueueing JavaScript - Add Media and related dialogs
	 *
	 * @since 1.20
	 *
	 * @var	string
	 */
	const JAVASCRIPT_EDIT_MEDIA_SLUG = 'mla-edit-media-scripts';

	/**
	 * Object name for localizing JavaScript - Add Media and related dialogs
	 *
	 * @since 1.20
	 *
	 * @var	string
	 */
	const JAVASCRIPT_EDIT_MEDIA_OBJECT = 'mla_edit_media_vars';

	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 0.80
	 *
	 * @return	void
	 */
	public static function initialize() {
		// do_action( 'admin_init' ) in wp-admin/admin.php
		add_action( 'admin_init', 'MLAEdit::mla_admin_init_action' );

		// do_action( 'admin_enqueue_scripts', $hook_suffix ) in wp-admin/admin-header.php
		add_action( 'admin_enqueue_scripts', 'MLAEdit::mla_admin_enqueue_scripts_action' );

		// do_action( 'add_meta_boxes', $post_type, $post ) in wp-admin/edit-form-advanced.php
		add_action( 'add_meta_boxes', 'MLAEdit::mla_add_meta_boxes_action', 10, 2 );

		// apply_filters( 'post_updated_messages', $messages ) in wp-admin/edit-form-advanced.php
		add_filter( 'post_updated_messages', 'MLAEdit::mla_post_updated_messages_filter', 10, 1 );

		// do_action in wp-admin/includes/meta-boxes.php function attachment_submit_meta_box
		add_action( 'attachment_submitbox_misc_actions', 'MLAEdit::mla_attachment_submitbox_action' );

		// do_action in wp-includes/post.php function wp_insert_post
		add_action( 'edit_attachment', 'MLAEdit::mla_edit_attachment_action', 10, 1 );

		// apply_filters( 'admin_title', $admin_title, $title ) in /wp-admin/admin-header.php
		add_filter( 'admin_title', 'MLAEdit::mla_edit_add_help_tab', 10, 2 );
	}

	/**
	 * Adds Custom Field support to the Edit Media screen.
	 * Declared public because it is an action.
	 *
	 * @since 0.80
	 *
	 * @return	void	echoes the HTML markup for the label and value
	 */
	public static function mla_admin_init_action( ) {
		$edit_media_support = array( 'custom-fields' );
		if ( ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ENABLE_FEATURED_IMAGE ) ) && current_theme_supports( 'post-thumbnails', 'attachment' ) ) {
			$edit_media_support[] = 'thumbnail';
		}

		add_post_type_support( 'attachment', apply_filters( 'mla_edit_media_support', $edit_media_support ) );

		// Check for Media/Add New bulk edit area updates
		if ( ! empty( $_REQUEST['mlaAddNewBulkEditFormString'] ) && ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ADD_NEW_BULK_EDIT ) ) ) {
			/*
			 * If any of the mapping rule options is enabled, use the MLA filter so this
			 * filter is called after mapping rules have run. If none are enabled,
			 * use the WordPress filters directly.
			 */
			if ( ( 'checked' == MLACore::mla_get_option( 'enable_iptc_exif_mapping' ) ) ||
				( 'checked' == MLACore::mla_get_option( 'enable_custom_field_mapping' ) ) ||
				( 'checked' == MLACore::mla_get_option( 'enable_iptc_exif_update' ) ) ||
				( 'checked' == MLACore::mla_get_option( 'enable_custom_field_update' ) ) ) {
				// Fires after MLA mapping in wp_update_attachment_metadata() processing.
				add_filter( 'mla_update_attachment_metadata_postfilter', 'MLAEdit::mla_update_attachment_metadata_postfilter', 10, 3 );
			} else {
				add_action( 'add_attachment', 'MLAEdit::mla_add_attachment_action', 0x7FFFFFFF, 1 );
			}
		}

		// If there's no action variable, we have nothing more to do
		if ( ! isset( $_REQUEST['action'] ) ) {
			return;
		}

		/*
		 * For flat taxonomies that use the checklist meta box, convert the term array
		 * back into a string of slug values.
		 */
		if ( 'editpost' == $_REQUEST['action']  ) {
			if ( isset( $_POST['tax_input'] ) && is_array( $_POST['tax_input'] ) ) {
				$taxonomies = array_keys( array_map( 'absint', wp_unslash( $_POST['tax_input'] ) ) );
				foreach( $taxonomies as $key ) {
					if ( isset( $_POST['tax_input'][ $key ] ) && is_array( $_POST['tax_input'][ $key ] ) ) {
						$tax = get_taxonomy( sanitize_text_field( $key ) );
						if ( $tax->hierarchical ) {
							continue;
						}
			
						$value = array_map( 'sanitize_text_field', wp_unslash( $_POST['tax_input'][ $key ] ) );
						if ( false !== ( $bad_term = array_search( '0', $value ) ) ) { 
							unset( $value[ $bad_term ] );
						}
			
						$comma = _x( ',', 'tag_delimiter', 'media-library-assistant' );
						$_POST['tax_input'][ $key ] = implode( $comma, $value );
						$_REQUEST['tax_input'][ $key ] = implode( $comma, $value );
					} // array value
				} // foreach tax_input
			} // array tax_input
		} // action editpost
	}

	/**
	 * Load the plugin's Style Sheet and Javascript files
	 *
	 * @since 1.71
	 *
	 * @param	string	Name of the page being loaded
	 *
	 * @return	void
	 */
	public static function mla_admin_enqueue_scripts_action( $page_hook ) {
		global $wp_locale;

		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		// Add New Bulk Edit Area
		if ( 'media-new.php' == $page_hook && ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ADD_NEW_BULK_EDIT ) ) ) {
			if ( $wp_locale->is_rtl() ) {
				wp_register_style( 'mla-add-new-bulk-edit', MLA_PLUGIN_URL . 'css/mla-add-new-bulk-edit-rtl.css', false, MLACore::mla_script_version() );
				wp_register_style( 'mla-add-new-bulk-edit' . '-set-parent', MLA_PLUGIN_URL . 'css/mla-style-set-parent-rtl.css', false, MLACore::mla_script_version() );
			} else {
				wp_register_style( 'mla-add-new-bulk-edit', MLA_PLUGIN_URL . 'css/mla-add-new-bulk-edit.css', false, MLACore::mla_script_version() );
				wp_register_style( 'mla-add-new-bulk-edit' . '-set-parent', MLA_PLUGIN_URL . 'css/mla-style-set-parent.css', false, MLACore::mla_script_version() );
			}

			wp_enqueue_style( 'mla-add-new-bulk-edit' );
			wp_enqueue_style( 'mla-add-new-bulk-edit' . '-set-parent' );

			// 'suggest' loads the script for flat taxonomy auto-complete/suggested matches
			wp_enqueue_script( 'mla-add-new-bulk-edit-scripts', MLA_PLUGIN_URL . "js/mla-add-new-bulk-edit-scripts{$suffix}.js", 
				array( 'suggest', 'jquery' ), MLACore::mla_script_version(), false );

			if ( MLACore::mla_supported_taxonomies( 'checklist-add-term' ) ) {
				wp_enqueue_script( 'mla-add-new-bulk-edit-scripts' . '-add-term', MLA_PLUGIN_URL . "js/mla-add-term-scripts{$suffix}.js", 
					array( 'wp-ajax-response', 'jquery', 'mla-add-new-bulk-edit-scripts' ), MLACore::mla_script_version(), false );
			}
		
			wp_enqueue_script( 'mla-add-new-bulk-edit-scripts' . '-set-parent', MLA_PLUGIN_URL . "js/mla-set-parent-scripts{$suffix}.js", 
				array( 'mla-add-new-bulk-edit-scripts', 'jquery' ), MLACore::mla_script_version(), false );

			$script_variables = array(
				'uploadTitle' => __( 'Upload New Media items', 'media-library-assistant' ),
				'toggleOpen' => __( 'Open Bulk Edit area', 'media-library-assistant' ),
				'toggleClose' => __( 'Close Bulk Edit area', 'media-library-assistant' ),
				'areaOnTop' => ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ADD_NEW_BULK_EDIT_ON_TOP ) ),
				'areaOpen' => ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ADD_NEW_BULK_EDIT_AUTO_OPEN ) ),
				'comma' => _x( ',', 'tag_delimiter', 'media-library-assistant' ),
				'ajaxFailError' => __( 'An ajax.fail error has occurred. Please reload the page and try again.', 'media-library-assistant' ),
				'ajaxDoneError' => __( 'An ajax.done error has occurred. Please reload the page and try again.', 'media-library-assistant' ),
				'setParentAction' => MLACore::JAVASCRIPT_FIND_POSTS_SLUG,
				'exportPresetsAction' => MLACore::JAVASCRIPT_EXPORT_PRESETS_SLUG,
				'exportPresetsOption' => MLACoreOptions::MLA_UPLOAD_BULK_EDIT_PRESETS,
				'useDashicons' => false,
				'useSpinnerClass' => false,
			);

			if ( version_compare( get_bloginfo( 'version' ), '3.8', '>=' ) ) {
				$script_variables['useDashicons'] = true;
			}

			if ( version_compare( get_bloginfo( 'version' ), '4.2', '>=' ) ) {
				$script_variables['useSpinnerClass'] = true;
			}

			wp_localize_script( 'mla-add-new-bulk-edit-scripts', 'mla_add_new_bulk_edit_vars', $script_variables );

			// Filter the media upload post parameters.
			// @param array $post_params An array of media upload parameters used by Plupload.
			add_filter( 'upload_post_params', 'MLAEdit::mla_upload_post_params', 10, 1 );

			// Fires on the post upload UI screen; legacy (pre-3.5.0) upload interface.
			add_action( 'post-upload-ui', 'MLAEdit::mla_post_upload_ui' );

			return;
		} // media-new.php

		if ( ( 'post.php' != $page_hook ) || ( ! isset( $_REQUEST['post'] ) ) || ( ! isset( $_REQUEST['action'] ) ) || ( 'edit' != $_REQUEST['action'] ) ) {
			return;
		}

		$post = get_post( absint( wp_unslash( $_REQUEST['post'] ) ) );
		if ( 'attachment' != $post->post_type ) {
			return;
		}

		/*
		 * Media/Edit Media submenu
		 * Register and queue the style sheet, if needed
		 */
		wp_register_style( self::JAVASCRIPT_EDIT_MEDIA_STYLES, MLA_PLUGIN_URL . 'css/mla-edit-media-style.css', false, MLACore::mla_script_version() );
		wp_enqueue_style( self::JAVASCRIPT_EDIT_MEDIA_STYLES );

		wp_register_style( self::JAVASCRIPT_EDIT_MEDIA_STYLES . '-set-parent', MLA_PLUGIN_URL . 'css/mla-style-set-parent.css', false, MLACore::mla_script_version() );
		wp_enqueue_style( self::JAVASCRIPT_EDIT_MEDIA_STYLES . '-set-parent' );

		wp_enqueue_script( self::JAVASCRIPT_EDIT_MEDIA_SLUG, MLA_PLUGIN_URL . "js/mla-edit-media-scripts{$suffix}.js", 
			array( 'post', 'wp-lists', 'suggest', 'jquery' ), MLACore::mla_script_version(), false );

		wp_enqueue_script( self::JAVASCRIPT_EDIT_MEDIA_SLUG . '-set-parent', MLA_PLUGIN_URL . "js/mla-set-parent-scripts{$suffix}.js", 
			array( 'post', 'wp-lists', 'suggest', 'jquery', self::JAVASCRIPT_EDIT_MEDIA_SLUG ), MLACore::mla_script_version(), false );

		$script_variables = array(
			'comma' => _x( ',', 'tag_delimiter', 'media-library-assistant' ),
			'Ajax_Url' => admin_url( 'admin-ajax.php' ),
			'ajaxFailError' => __( 'An ajax.fail error has occurred. Please reload the page and try again.', 'media-library-assistant' ),
			'ajaxDoneError' => __( 'An ajax.done error has occurred. Please reload the page and try again.', 'media-library-assistant' ),
			'setParentAction' => MLACore::JAVASCRIPT_FIND_POSTS_SLUG,
			'uploadLabel' => sprintf( __( 'Uploaded on: %s' ), '' ),
			'modifyLabel' => __( 'Last modified', 'media-library-assistant' ) . ': ',
			'useDashicons' => false,
			'useSpinnerClass' => false,
		);

		if ( version_compare( get_bloginfo( 'version' ), '3.8', '>=' ) ) {
			$script_variables['useDashicons'] = true;
		}

		if ( version_compare( get_bloginfo( 'version' ), '4.2', '>=' ) ) {
			$script_variables['useSpinnerClass'] = true;
		}

		wp_localize_script( self::JAVASCRIPT_EDIT_MEDIA_SLUG, self::JAVASCRIPT_EDIT_MEDIA_OBJECT, $script_variables );
	}

	/**
	 * Filter the Media/Add New post parameters.
	 *
	 * @since 2.02
	 *
	 * @param	array	$post_parms An array of media upload parameters used by Plupload.
	 */
	public static function mla_upload_post_params( $post_parms ) {
		/*
		 * You can add elements to the array. It will end up in the client global
		 * variable: wpUploaderInit.multipart_params and is then copied to
		 * uploader.settings.multipart_params
		 *
		 * The elements of this array come back as $_REQUEST elements when the
		 * upload is submitted.
		 */
		//$post_parms['mlaAddNewBulkEdit'] = array ( 'formData' => array() );
		return $post_parms;
	}

	/**
	 * Page template array used by mla_generate_bulk_edit_form_fieldsets()
	 *
	 * @since 2.99
	 *
	 * @var	array
	 */
	private static $fieldset_template_array = NULL;

	/**
	 * Loads the self::$fieldset_template_array
	 *
	 * @since 2.99
	 *
	 */
	private static function _load_fieldset_template_array() {
		if ( NULL === self::$fieldset_template_array ) {
			self::$fieldset_template_array = MLACore::mla_load_template( 'mla-bulk-edit-fieldsets.tpl' );
			if ( ! is_array( self::$fieldset_template_array ) ) {
				/* translators: 1: ERROR tag 2: function name 3: non-array value */
				MLACore::mla_debug_add( sprintf( _x( '%1$s: %2$s non-array "%3$s"', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), 'MLAEdit::_load_fieldset_template_array', var_export( self::$fieldset_template_array, true ) ), MLACore::MLA_DEBUG_CATEGORY_ANY );
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Gets preset values from the wp_options or wp_usermeta table
	 *
	 * @since 2.99
	 *
	 * @param	string 	Name of the desired option
	 * @param	boolean	True to ignore current setting and return default values
	 *
	 * @return	mixed	Value(s) for the option or false if the option is not a defined MLA option
	 */
	public static function mla_get_bulk_edit_form_presets( $option, $get_default = false ) {
//error_log( __LINE__ . " MLAEdit::mla_get_bulk_edit_form_presets( {$option}, {$get_default} ) _per_user = " . var_export( MLACore::mla_get_option( $option . '_per_user' ), true ), 0 );
		if ( $get_default || ( 'checked' !== MLACore::mla_get_option( $option . '_per_user' ) ) ) {
			$option_value = MLACore::mla_get_option( $option, $get_default );
		} else {
			// Handle per-user option
			$option_value = get_user_meta( get_current_user_id(), $option, true );
		}
//error_log( __LINE__ . " MLAEdit::mla_get_bulk_edit_form_presets( {$option}, {$get_default} ) option_value = " . var_export( $option_value, true ), 0 );

		if ( empty( $option_value ) ) {
			$option_value = MLACore::mla_get_option( $option, true );
		}
		$option_value = apply_filters( 'mla_get_bulk_edit_form_presets', $option_value, $option, $get_default );

//error_log( __LINE__ . " MLAEdit::mla_get_bulk_edit_form_presets( {$option}, {$get_default} ) option_value = " . var_export( $option_value, true ), 0 );
		return $option_value;
	}

	/**
	 * Stores preset values in the wp_options or wp_usermeta table
	 *
	 * @since 2.99
	 *
	 * @param	string 	Name of the desired option
	 * @param	mixed 	New value for the desired option
	 *
	 * @return	boolean	True if the value was changed or false if the update failed
	 */
	public static function mla_update_bulk_edit_form_presets( $option, $new_values ) {
		if ( 'checked' !== MLACore::mla_get_option( $option . '_per_user' ) ) {
			return MLACore::mla_update_option( $option, $new_values );
		}
	
		// Handle per-user option
		return update_user_meta( get_current_user_id(), $option, $new_values );
	}

	/**
	 * Generates the bulk edit area fieldsets HTML for the Media/Assistant and Media/Add New screens
	 *
	 * For Media/Assistant, fires in the _build_inline_edit_form function that generates the Bulk Edit Area.
	 
	 * For Media/Add New, fires on the post upload UI screen; legacy (pre-3.5.0) upload interface.
	 * Anything echoed here goes below the "Maximum upload file size" message
	 * and above the id="media-items" div.
	 *
	 * @param	array	$fieldset_values Initial taxonomy terms and field values
	 * @param	string	$filter_root Root portion of '_fieldset_values', '_values' and '_template' filter names:
	 * 					'mla_upload_bulk_edit_form_blank', 'mla_upload_bulk_edit_form_initial', 'mla_upload_bulk_edit_form_preset', 
	 * 					'mla_list_table_inline_blank', 'mla_list_table_inline_initial', 'mla_list_table_inline_preset',
	 *
	 * @since 2.99
	 *
	 */
	public static function mla_generate_bulk_edit_form_fieldsets( $fieldset_values, $filter_root ) {
		if ( false === self::_load_fieldset_template_array() ) {
			/* translators: 1: ERROR tag 2: function name 3: non-array value */
			$message = sprintf( _x( '%1$s: %2$s non-array "%3$s"', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), 'MLAEdit::mla_generate_bulk_edit_form_fieldsets', var_export( self::$fieldset_template_array, true ) );
			MLACore::mla_debug_add( $message, MLACore::MLA_DEBUG_CATEGORY_ANY );
			return $message;
		}

		$fieldset_values = apply_filters( $filter_root . '_fieldset_values', $fieldset_values, $filter_root );

		// Initialize blank/default fieldset values
		$page_values = array(
			'filter_root' => $filter_root,
			'category_fieldset' => '',
			'tag_fieldset' => '',
			'Title' => __( 'Title', 'media-library-assistant' ),
			'post_title_value' => '',
			'Caption' => __( 'Caption', 'media-library-assistant' ),
			'post_excerpt_value' => '',
			'Description' => __( 'Description', 'media-library-assistant' ),
			'post_content_value' => '',
			'ALT Text' => __( 'ALT Text', 'media-library-assistant' ),
			'image_alt_value' => '',
			'Uploaded on' => __( 'Uploaded on', 'media-library-assistant' ),
			'post_date_value' => '',
			'Parent ID' => __( 'Parent ID', 'media-library-assistant' ),
			'post_parent_value' => '',
			'Select' => __( 'Select', 'media-library-assistant' ),
			'authors' => '',
			'Comments' => __( 'Comments', 'media-library-assistant' ),
			'comments_no_change' => '',
			'comments_open' => '',
			'comments_closed' => '',
			'No Change' => __( 'No Change', 'media-library-assistant' ),
			'Allow' => __( 'Allow', 'media-library-assistant' ),
			'Do not allow' => __( 'Do not allow', 'media-library-assistant' ),
			'Pings' => __( 'Pings', 'media-library-assistant' ),
			'pings_no_change' => '',
			'pings_open' => '',
			'pings_closed' => '',
			'custom_fields' => '',
		);

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

		// The left-hand or center column contains the hierarchical taxonomies,e.g., Att. Category
		// The center or right-hand column contains the flat taxonomies, e.g., Att. Tag
		$category_fieldset = '';

		if ( false !== strpos( $filter_root, 'upload' ) ) {
				$category_fieldset_column = 'left';
				$tag_fieldset_column = 'center';
		} else {
				$category_fieldset_column = 'center';
				$tag_fieldset_column = 'right';
		}

		if ( count( $hierarchical_taxonomies ) ) {
			$bulk_category_blocks = '';

			foreach ( $hierarchical_taxonomies as $tax_name => $tax_object ) {
				if ( current_user_can( $tax_object->cap->assign_terms ) ) {
					// Apply presets
					$selected_cats = false;
					if ( !empty( $fieldset_values['tax_input'][ $tax_name ] ) && is_array( $fieldset_values['tax_input'][ $tax_name ] ) ) {
						$selected_cats = $fieldset_values['tax_input'][ $tax_name ];
					}
					
					ob_start();
					wp_terms_checklist( NULL, array( 'taxonomy' => $tax_name, 'selected_cats' => $selected_cats, 'popular_cats' => array(), ) );
					$tax_checklist = ob_get_contents();
					ob_end_clean();
					
					if ( MLACore::mla_taxonomy_support( $tax_name, 'checklist-add-term' ) ) {
						$element_values = array(
							'tax_attr' => esc_attr( $tax_name ),
							'Add New Term' => __( '+&nbsp;Add&nbsp;New&nbsp;Term', 'media-library-assistant' ),
							'Add Reader' => __( 'Add New', 'media-library-assistant' ) . ' ' . esc_html( $tax_object->labels->singular_name ),
							'tax_parents' => wp_dropdown_categories( array( 'taxonomy' => $tax_name, 'hide_empty' => 0, 'name' => "new{$tax_name}_parent", 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => '&mdash; ' . $tax_object->labels->parent_item . ' &mdash;', 'echo' => 0 ) ),
							'Add Button' => esc_html( $tax_object->labels->add_new_item ),
							'ajax_nonce_field' => wp_nonce_field( 'add-'.$tax_name, '_ajax_nonce-add-'.$tax_name, false, false ),
						);
					
						$category_add_link = MLAData::mla_parse_template( self::$fieldset_template_array['category_add_link'], $element_values );
						$category_adder = MLAData::mla_parse_template( self::$fieldset_template_array['category_adder'], $element_values );
					} else {
						$category_add_link = '';
						$category_adder = '';
					}
					
					$element_values = array(
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
					
					// Apply tax_action presets
					if ( !empty( $fieldset_values['tax_action'][ $tax_name ] )  ) {
						$element_values['tax_add_checked'] = '';
						
						switch ( $fieldset_values['tax_action'][ $tax_name ] ) {
							case 'remove':
								$element_values['tax_remove_checked'] = 'checked="checked"';
								break;
							case 'replace':
								$element_values['tax_replace_checked'] = 'checked="checked"';
								break;
							default:
								$element_values['tax_add_checked'] = 'checked="checked"';
						}
					}
					
					$category_block = MLAData::mla_parse_template( self::$fieldset_template_array['category_block'], $element_values );
					$taxonomy_options = MLAData::mla_parse_template( self::$fieldset_template_array['taxonomy_options'], $element_values );
					
					$bulk_category_blocks .= $category_block . $taxonomy_options;
				} // current_user_can
			} // foreach $hierarchical_taxonomies

			$element_values = array(
				'category_fieldset_column' => $category_fieldset_column,
				'category_blocks' => $bulk_category_blocks
			);
			$page_values['category_fieldset'] = MLAData::mla_parse_template( self::$fieldset_template_array['category_fieldset'], $element_values );
		} // count( $hierarchical_taxonomies )

		// The middle column contains the flat taxonomies, e.g., Att. Tag
		$tag_fieldset = '';

		if ( count( $flat_taxonomies ) ) {
			$bulk_tag_blocks = '';

			foreach ( $flat_taxonomies as $tax_name => $tax_object ) {
				if ( current_user_can( $tax_object->cap->assign_terms ) ) {
					if ( MLACore::mla_taxonomy_support( $tax_name, 'flat-checklist' ) ) {
						// Apply presets
						$selected_cats = false;
						if ( !empty( $fieldset_values['tax_input'][ $tax_name ] ) && is_array( $fieldset_values['tax_input'][ $tax_name ] ) ) {
							$selected_cats = $fieldset_values['tax_input'][ $tax_name ];
						}
						
						ob_start();
						wp_terms_checklist( NULL, array( 'taxonomy' => $tax_name, 'selected_cats' => $selected_cats, 'popular_cats' => array(), ) );
						$tax_checklist = ob_get_contents();
						ob_end_clean();
						
						if ( MLACore::mla_taxonomy_support( $tax_name, 'checklist-add-term' ) ) {
							$element_values = array(
								'tax_attr' => esc_attr( $tax_name ),
								'Add New Term' => __( '+&nbsp;Add&nbsp;New&nbsp;Term', 'media-library-assistant' ),
								'Add Reader' => __( 'Add New', 'media-library-assistant' ) . ' ' . esc_html( $tax_object->labels->singular_name ),
								'tax_parents' => "<input type='hidden' name='new{$tax_name}_parent' id='new{$tax_name}_parent' value='-1' />",
								'Add Button' => esc_html( $tax_object->labels->add_new_item ),
								'ajax_nonce_field' => wp_nonce_field( 'add-'.$tax_name, '_ajax_nonce-add-'.$tax_name, false, false ),
							);
						
							$category_add_link = MLAData::mla_parse_template( self::$fieldset_template_array['category_add_link'], $element_values );
							$category_adder = MLAData::mla_parse_template( self::$fieldset_template_array['category_adder'], $element_values );
						} else {
							$category_add_link = '';
							$category_adder = '';
						}
					
						$element_values = array(
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

						// Apply tax_action presets
						if ( !empty( $fieldset_values['tax_action'][ $tax_name ] )  ) {
							$element_values['tax_add_checked'] = '';
							
							switch ( $fieldset_values['tax_action'][ $tax_name ] ) {
								case 'remove':
									$element_values['tax_remove_checked'] = 'checked="checked"';
									break;
								case 'replace':
									$element_values['tax_replace_checked'] = 'checked="checked"';
									break;
								default:
									$element_values['tax_add_checked'] = 'checked="checked"';
							}
						}
						
						$tag_block = MLAData::mla_parse_template( self::$fieldset_template_array['category_block'], $element_values );
					} else {
						// Apply presets
						$selected_tags = '';
						if ( !empty( $fieldset_values['tax_input'][ $tax_name ] ) && is_string( $fieldset_values['tax_input'][ $tax_name ] ) ) {
							$selected_tags = $fieldset_values['tax_input'][ $tax_name ];
						}
						
						$element_values = array(
							'tax_html' => esc_html( $tax_object->labels->name ),
							'tax_attr' => esc_attr( $tax_name ),
							'tax_value' => esc_textarea( $selected_tags ),
							'tax_add_checked' => 'checked="checked"',
							'tax_remove_checked' => '',
							'tax_replace_checked' => '',
							'Add' => __( 'Add', 'media-library-assistant' ),
							'Remove' => __( 'Remove', 'media-library-assistant' ),
							'Replace' => __( 'Replace', 'media-library-assistant' ),
						);

						// Apply tax_action presets
						if ( !empty( $fieldset_values['tax_action'][ $tax_name ] )  ) {
							$element_values['tax_add_checked'] = '';
							
							switch ( $fieldset_values['tax_action'][ $tax_name ] ) {
								case 'remove':
									$element_values['tax_remove_checked'] = 'checked="checked"';
									break;
								case 'replace':
									$element_values['tax_replace_checked'] = 'checked="checked"';
									break;
								default:
									$element_values['tax_add_checked'] = 'checked="checked"';
							}
						}
						
						$tag_block = MLAData::mla_parse_template( self::$fieldset_template_array['tag_block'], $element_values );
					}

					$taxonomy_options = MLAData::mla_parse_template( self::$fieldset_template_array['taxonomy_options'], $element_values );
					$bulk_tag_blocks .= $tag_block . $taxonomy_options;
				} // current_user_can
			} // foreach $flat_taxonomies

			$element_values = array(
				'tag_fieldset_column' => $tag_fieldset_column,
				'tag_blocks' => $bulk_tag_blocks
			);
			$page_values['tag_fieldset'] = MLAData::mla_parse_template( self::$fieldset_template_array['tag_fieldset'], $element_values );
		} // count( $flat_taxonomies )

		// The right-hand column contains the standard and custom fields
		if ( !empty( $fieldset_values['post_title'] ) ) {
			$page_values['post_title_value'] = $fieldset_values['post_title'];
		}
		
		if ( !empty( $fieldset_values['post_excerpt'] ) ) {
			$page_values['post_excerpt_value'] = $fieldset_values['post_excerpt'];
		}
		
		if ( !empty( $fieldset_values['post_content'] ) ) {
			$page_values['post_content_value'] = $fieldset_values['post_content'];
		}
		
		if ( !empty( $fieldset_values['image_alt'] ) ) {
			$page_values['image_alt_value'] = $fieldset_values['image_alt'];
		}
		
		if ( !empty( $fieldset_values['post_date'] ) ) {
			$page_values['post_date_value'] = $fieldset_values['post_date'];
		}
		
		if ( !empty( $fieldset_values['post_parent'] ) ) {
			$page_values['post_parent_value'] = $fieldset_values['post_parent'];
		}
		
		// Apply authors preset
		$selected_author = -1;
		if ( !empty( $fieldset_values['post_author'] ) ) {
			$selected_author = $fieldset_values['post_author'];
		}
		
		if ( $authors = MLA::mla_authors_dropdown( $selected_author ) ) {
			$authors_dropdown  = '              <label class="inline-edit-author alignright">' . "\n";
			$authors_dropdown .= '                <span class="title">' . __( 'Author', 'media-library-assistant' ) . '</span>' . "\n";
			$authors_dropdown .= $authors . "\n";
			$authors_dropdown .= '              </label>' . "\n";
		} else {
			$authors_dropdown = '';
		}

		$page_values['authors'] = $authors_dropdown;

		switch ( $fieldset_values['comment_status'] ) {
			case 'open':
				$page_values['comments_open'] = 'selected="selected"';
				break;
			case 'closed':
				$page_values['comments_closed'] = 'selected="selected"';
				break;
			default:
				$page_values['comments_no_change'] = 'selected="selected"';
		}
		
		switch ( $fieldset_values['ping_status'] ) {
			case 'open':
				$page_values['pings_open'] = 'selected="selected"';
				break;
			case 'closed':
				$page_values['pings_closed'] = 'selected="selected"';
				break;
			default:
				$page_values['pings_no_change'] = 'selected="selected"';
		}
		
		$custom_fields = '';
		foreach (MLACore::mla_custom_field_support( 'bulk_edit' ) as $slug => $details ) {
			  $element_values = array(
				  'slug' => $slug,
				  'label' => esc_attr( $details['name'] ),
				  'value' => '',
			  );
			  
			  if ( !empty( $fieldset_values['custom_fields'][ $details['name'] ] ) ) {
				  $element_values['value'] = $fieldset_values['custom_fields'][ $details['name'] ];
			  }

			  $custom_fields .= MLAData::mla_parse_template( self::$fieldset_template_array['custom_field'], $element_values );
		}

		$page_values['custom_fields'] = $custom_fields;

		$page_values = apply_filters( $filter_root . '_values', $page_values );
		$page_template = apply_filters( $filter_root . '_template', self::$fieldset_template_array['form_fieldsets'], $page_values );
		$preset_fieldsets = MLAData::mla_parse_template( $page_template, $page_values );

		if ( false !== strpos( $filter_root, 'preset' ) ) {
			$presets = wp_nonce_field( MLACore::JAVASCRIPT_EXPORT_PRESETS_SLUG, 'mla-export-presets-ajax-nonce', false, false ) . "\n";
		} else {
			$presets = '';
		}
		
		$presets .= $preset_fieldsets;
//error_log( __LINE__ . ' MLAEdit::mla_generate_bulk_edit_form_fieldsets presets = ' . var_export( $presets, true ), 0 );

 		return $presets;
	}

	/**
	 * Echoes bulk edit area HTML to the Media/Add New screen
	 *
	 * Fires on the post upload UI screen; legacy (pre-3.5.0) upload interface.
	 * Anything echoed here goes below the "Maximum upload file size" message
	 * and above the id="media-items" div.
	 *
	 * @since 2.02
	 *
	 */
	public static function mla_post_upload_ui() {
		/*
		 * Only add our form to the Media/Add New screen. In particular,
		 * do NOT add it to the Media Manager Modal Window
		 */
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
		} else {
			$screen = NULL;
		}

		if ( is_object( $screen ) && ( 'add' != $screen->action || 'media' != $screen->base ) ) {
			return;
		}

		$page_template_array = MLACore::mla_load_template( 'mla-add-new-bulk-edit.tpl' );
		if ( ! is_array( $page_template_array ) ) {
			/* translators: 1: ERROR tag 2: function name 3: non-array value */
			MLACore::mla_debug_add( sprintf( _x( '%1$s: %2$s non-array "%3$s"', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), 'MLAEdit::mla_post_upload_ui', var_export( $page_template_array, true ) ), MLACore::MLA_DEBUG_CATEGORY_ANY );
			return;
		}

		// Get a "blank" presets array for the blank and initial fieldsets
		$fieldset_values = MLAEdit::mla_get_bulk_edit_form_presets( MLACoreOptions::MLA_UPLOAD_BULK_EDIT_PRESETS, true );

		// Format and filter the blank/reset fieldset values
		$blank_div_content = self::mla_generate_bulk_edit_form_fieldsets( $fieldset_values, 'mla_upload_bulk_edit_form_blank' );

		// Format and filter the initial fieldset values
		$initial_div_content = self::mla_generate_bulk_edit_form_fieldsets( $fieldset_values, 'mla_upload_bulk_edit_form_initial' );

		// Populate the import/export saved fieldset values, if any
		$fieldset_values = MLAEdit::mla_get_bulk_edit_form_presets( MLACoreOptions::MLA_UPLOAD_BULK_EDIT_PRESETS );
//error_log( __LINE__ . ' MLAEdit::mla_generate_bulk_edit_form_fieldsets preset_values = ' . var_export( $fieldset_values, true ), 0 );

		$preset_div_content = self::mla_generate_bulk_edit_form_fieldsets( $fieldset_values, 'mla_upload_bulk_edit_form_preset' );

		$set_parent_form = MLA::mla_set_parent_form( false );

		$page_values = array(
			'filter_root' => 'mla_upload_bulk_edit_form',
			'preset_div_content' => $preset_div_content,
			'blank_div_content' => $blank_div_content,
			'Toggle' => __( 'Open Bulk Edit area', 'media-library-assistant' ),
			'Reset' => __( 'Reset', 'media-library-assistant' ),
			'Import' => __( 'Import', 'media-library-assistant' ),
			'Export' => __( 'Export', 'media-library-assistant' ),
			'NOTE' => __( 'IMPORTANT: Make your entries BEFORE uploading new items. Pull down the Help menu for more information.', 'media-library-assistant' ),
			'initial_div_content' => $initial_div_content,
			'set_parent_form' => $set_parent_form,
		);

		$page_values = apply_filters( 'mla_upload_bulk_edit_form_values', $page_values );
		$page_template = apply_filters( 'mla_upload_bulk_edit_form_template', $page_template_array['page'] );
		$parse_value = MLAData::mla_parse_template( $page_template, $page_values );
		echo apply_filters( 'mla_upload_bulk_edit_form_parse', $parse_value, $page_template, $page_values ); // phpcs:ignore
	}

	/**
	 * Attachment ID passed from mla_add_attachment_action to mla_update_attachment_metadata_filter
	 *
	 * Ensures that IPTC/EXIF and Custom Field mapping is only performed when the attachment is first
	 * added to the Media Library.
	 *
	 * @since 2.96
	 *
	 * @var	integer
	 */
	private static $add_attachment_id = 0;

	/**
	 * Set $add_attachment_id to just-inserted attachment
 	 *
	 * All of the actual processing is done later, in mla_update_attachment_metadata_filter.
	 * This function is called only if Custom FIeld AND IPTC/EXIF mapping on new attachments are disabled
	 *
	 * The filter is applied by function wp_insert_post() in /wp-includes/post.php
	 *
	 * @since 2.96
	 *
	 * @param	integer	ID of just-inserted attachment
	 *
	 * @return	void
	 */
	public static function mla_add_attachment_action( $post_ID ) {
		MLACore::mla_debug_add( __LINE__ . " MLAEdit::mla_add_attachment_action( $post_ID )", MLACore::MLA_DEBUG_CATEGORY_METADATA );
		MLAEdit::$add_attachment_id = $post_ID;

		add_filter( 'wp_generate_attachment_metadata', 'MLAEdit::mla_generate_attachment_metadata_filter', 0x7FFFFFFF, 2 );

		do_action( 'mla_add_attachment', $post_ID );
 	} // mla_add_attachment_action

	/**
	 * This filter tests the MLAEdit::$add_attachment_id variable set by the mla_add_attachment_action
	 * to ensure that mapping is only performed after the generation of all intermediate sizes is complete.
	 *
	 * The filter is applied by function wp_generate_attachment_metadata() in /wp-includes/image.php
	 * This function is called only if Custom Field AND IPTC/EXIF mapping on new attachments are disabled
	 *
	 * @since 2.96
	 *
	 * @param	array	Attachment metadata for just-inserted attachment
	 * @param	integer	ID of just-inserted attachment
	 *
	 * @return	array	Updated attachment metadata
	 */
	public static function mla_generate_attachment_metadata_filter( $data, $post_id ) {
		$add_attachment_id = MLAEdit::$add_attachment_id;
		if ( $add_attachment_id === $post_id ) {
			add_filter( 'wp_update_attachment_metadata', 'MLAEdit::mla_update_attachment_metadata_postfilter', 0x7FFFFFFF, 2 );
			remove_filter( 'mla_generate_attachment_metadata_filter', 'MLAEdit::mla_generate_attachment_metadata_filter', 0x7FFFFFFF );
		}

		MLACore::mla_debug_add( __LINE__ . " MLAEdit::mla_generate_attachment_metadata_filter( {$post_id}, {$add_attachment_id} ) \$data = " . var_export( $data, true ), MLACore::MLA_DEBUG_CATEGORY_METADATA );
		return $data;
 	} // mla_generate_attachment_metadata_filter

	/**
	 * Apply Media/Add New bulk edit area updates, if any
	 *
	 * This filter is called AFTER MLA mapping rules are applied during
	 * wp_update_attachment_metadata() processing. If none of the mapping rules
	 * is enabled it is called from the 'wp_update_attachment_metadata' filter
	 * with just two arguments.
	 *
	 * @since 2.02
	 *
	 * @param	array	attachment metadata
	 * @param	integer	The Post ID of the new/updated attachment
	 * @param	array	Processing options, e.g., 'is_upload'
	 *
	 * @return	array	updated attachment metadata
	 */
	public static function mla_update_attachment_metadata_postfilter( $data, $post_id, $options = array( 'is_upload' => true ) ) {
		// If mapping on upload is disabled, reset the alternative trigger
		if ( MLAEdit::$add_attachment_id === $post_id ) {
			// Only do this once per attachment
			MLAEdit::$add_attachment_id = 0;
			remove_filter( 'mla_update_attachment_metadata_filter', 'MLAEdit::mla_update_attachment_metadata_filter', 0x7FFFFFFF );
		}

		// Check for active debug setting
		if ( ( MLACore::$mla_debug_level & 1 ) && ( MLACore::$mla_debug_level & MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL ) ) {
			$post = get_post( $post_id );
			MLACore::mla_debug_add( __LINE__ . " MLAEdit::mla_update_attachment_metadata_postfilter( $post_id ) post = " . var_export( $post, true ), MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL );
			MLACore::mla_debug_add( __LINE__ . " MLAEdit::mla_update_attachment_metadata_postfilter( $post_id ) data = " . var_export( $data, true ), MLACore::MLA_DEBUG_CATEGORY_THUMBNAIL );
		}
		
		if ( ( true == $options['is_upload'] ) && ! empty( $_REQUEST['mlaAddNewBulkEditFormString'] ) ) {
			/*
			 * Clean up the inputs, which have everything from the enclosing <form>.
			 * Double slashes in the URL-encoded string must be doubled again to survive the
			 * stripslashes() call in MLA::mla_process_bulk_action().
			 * wp_parse_args converts plus signs to spaces, which we must avoid.
			 */
//			$args = stripslashes( str_replace( '%5C%5C', '%5C%5C%5C%5C', $_REQUEST['mlaAddNewBulkEditFormString'] ) );
			$args = str_replace( '&amp;', '&', str_replace( '%5C%5C', '%5C%5C%5C%5C', wp_kses( wp_unslash( $_REQUEST['mlaAddNewBulkEditFormString'] ), 'post' ) ) );
			$args = wp_parse_args( str_replace( '%2B', 'urlencodedmlaplussign', $args ) );
			foreach ( $args as $key => $arg ) {
//				if ( is_string( $arg ) && 0 === strpos( $arg, 'template:' ) ) {
				if ( is_string( $arg ) ) {
					$args[ $key ] = str_replace( 'urlencodedmlaplussign', '+', $arg );
				}
			}

			unset( $args['parent'] );
			unset( $args['children'] );
			unset( $args['mla-set-parent-ajax-nonce'] );
			unset( $args['mla_set_parent_search_text'] );
			unset( $args['mla_set_parent_post_type'] );
			unset( $args['mla_set_parent_count'] );
			unset( $args['mla_set_parent_paged'] );
			unset( $args['mla_set_parent_found'] );
			unset( $args['post_id'] );
			unset( $args['_wpnonce'] );
			unset( $args['_wp_http_referer'] );

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

			// Pass the ID
			$args['cb_attachment'] = array( $post_id );
			$item_content = MLA::mla_process_bulk_action( 'edit', $args );
		}

		return $data;
	} // mla_update_attachment_metadata_postfilter

	/**
	 * Adds mapping update messages for display at the top of the Edit Media screen.
	 * Declared public because it is a filter.
	 *
	 * @since 1.10
	 *
	 * @param	array	messages for the Edit screen
	 *
	 * @return	array	updated messages
	 */
	public static function mla_post_updated_messages_filter( $messages ) {
	if ( isset( $messages['attachment'] ) ) {
		$messages['attachment'][101] = __( 'Custom field mapping updated.', 'media-library-assistant' );
		$messages['attachment'][102] = __('IPTC/EXIF mapping updated.', 'media-library-assistant' );
		$messages['attachment'][103] = __( 'Custom field mapping is disabled.', 'media-library-assistant' );
		$messages['attachment'][104] = __('IPTC/EXIF mapping is disabled.', 'media-library-assistant' );
	}

	return $messages;
	} // mla_post_updated_messages_filter

	/**
	 * Print out HTML form elements for editing uploaded on, last modified date
	 *
	 * Adapted from /wp-admin/includes/template.php function touch_time()
	 *
	 * @since 2.71
	 *
	 * @global WP_Locale $wp_locale for month name abbreviations
	 * @global WP_Post   $post
	 *
	 * @param int|bool $upload    Accepts 1|true for editing the upload date, 0|false for editing the modify date.
	 * @param int      $tab_index The tabindex attribute to add. Default 0.
	 */
	private static function _generate_time_edit_form( $upload = 1, $tab_index = 0 ) {
		global $wp_locale, $post;

		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 )
			$tab_index_attribute = " tabindex=\"$tab_index\"";

		$field = $upload ? 'upload' : 'modify';
		$date = $upload ? $post->post_date : $post->post_modified;
		$jj = mysql2date( 'd', $date, false );
		$mm = mysql2date( 'm', $date, false );
		$aa = mysql2date( 'Y', $date, false );
		$hh = mysql2date( 'H', $date, false );
		$mn = mysql2date( 'i', $date, false );
		$ss = mysql2date( 's', $date, false );
	
		$month = '<label><span class="screen-reader-text">' . __( 'Month' ) . '</span><select id="mm" name="mla_' . $field . '[mm]"' . $tab_index_attribute . ">\n";
		for ( $i = 1; $i < 13; $i = $i +1 ) {
			$monthnum = zeroise($i, 2);
			$monthtext = $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
			$month .= "\t\t\t" . '<option value="' . $monthnum . '" data-text="' . $monthtext . '" ' . selected( $monthnum, $mm, false ) . '>';
			/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
			$month .= sprintf( __( '%1$s-%2$s' ), $monthnum, $monthtext ) . "</option>\n";
		}
		$month .= '</select></label>';
	
		$day = '<label><span class="screen-reader-text">' . __( 'Day' ) . '</span><input type="text" id="jj" name="mla_' . $field . '[jj]" value="' . $jj . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
		$year = '<label><span class="screen-reader-text">' . __( 'Year' ) . '</span><input type="text" id="aa" name="mla_' . $field . '[aa]" value="' . $aa . '" size="4" maxlength="4"' . $tab_index_attribute . ' autocomplete="off" /></label>';
		$hour = '<label><span class="screen-reader-text">' . __( 'Hour' ) . '</span><input type="text" id="hh" name="mla_' . $field . '[hh]" value="' . $hh . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
		$minute = '<label><span class="screen-reader-text">' . __( 'Minute' ) . '</span><input type="text" id="mn" name="mla_' . $field . '[mn]" value="' . $mn . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
	
		echo '<div class="timestamp-wrap">';
		/* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
		printf( __( '%1$s %2$s, %3$s @ %4$s:%5$s' ), $month, $day, $year, $hour, $minute ); // phpcs:ignore
	
		echo "</div>\n";
		echo '<input type="hidden" id="ss" name="mla_' . esc_html( $field ) . '[ss]" value="' . esc_html( $ss ) . '" />' . "\n";
		echo '<input type="hidden" id="original" name="mla_' . esc_html( $field ) . '[original]" value="' . esc_html( $date ) . '" />' . "\n";

		$time_adj = current_time('timestamp');
		$map = array(
			'mm' => array( $mm, gmdate( 'm', $time_adj ) ),
			'jj' => array( $jj, gmdate( 'd', $time_adj ) ),
			'aa' => array( $aa, gmdate( 'Y', $time_adj ) ),
			'hh' => array( $hh, gmdate( 'H', $time_adj ) ),
			'mn' => array( $mn, gmdate( 'i', $time_adj ) ),
		);
		foreach ( $map as $timeunit => $value ) {
			list( $unit, $curr ) = $value;
	
			echo '<input type="hidden" id="hidden_' . $timeunit . '" name="hidden[' . $timeunit . ']" value="' . $unit . '" />' . "\n"; // phpcs:ignore
			$cur_timeunit = 'cur_' . $timeunit;
			echo '<input type="hidden" id="' . $cur_timeunit . '" name="' . $cur_timeunit . '" value="' . $curr . '" />' . "\n"; // phpcs:ignore
		}

		echo "<p>\n";
		echo '<a href="#edit_' . esc_html( $field ) . 'timestamp" class="save-timestamp hide-if-no-js button">' . esc_html__('OK') . "</a>\n";
		echo '<a href="#edit_' . esc_html( $field ) . 'timestamp" class="cancel-timestamp hide-if-no-js button-cancel">' . esc_html__('Cancel') . "</a>\n";
		echo "<p>\n";
	}

	/**
	 * Adds Last Modified date to the Submit box on the Edit Media screen.
	 * Declared public because it is an action.
	 *
	 * @since 0.80
	 *
	 * @return	void	echoes the HTML markup for the label and value
	 */
	public static function mla_attachment_submitbox_action( ) {
		global $post;

		/* translators: date_i18n format for uploaded on, last modified date and time */
		$date_format = __( 'M j, Y @ H:i', 'media-library-assistant' );

		$uploaded_date = date_i18n( $date_format, strtotime( $post->post_date ) );
		echo '<div class="misc-pub-section uploadtime misc-pub-uploadtime">' . "\n";
		echo '<span id="upload-timestamp">' . sprintf( esc_html__( 'Uploaded on', 'media-library-assistant' ) . ":\n <b>%1\$s</b></span>\n", esc_html( $uploaded_date ) );

		echo '<a href="#edit_uploadtime" class="edit-timestamp edit-uploadtime hide-if-no-js" role="button"><span aria-hidden="true">' . esc_html__( 'Edit' ) . "</span>\n";
		echo '<span class="screen-reader-text">' . esc_html__( 'Edit upload date and time' ) . "</span></a>\n";
		echo '<fieldset id="timestampdiv" class="hide-if-js">' . "\n";
		echo '<legend class="screen-reader-text">' . esc_html__( 'Upload Date and time' ) . "</legend>\n";
		self::_generate_time_edit_form( true ) . "\n";
		echo "</fieldset>\n";
		echo "</div><!-- .misc-pub-section -->\n";

		$modified_date = date_i18n($date_format, strtotime( $post->post_modified ) );
		echo '<div class="misc-pub-section modifytime misc-pub-modifytime">' . "\n";
		echo '<span id="modify-timestamp">' . sprintf( esc_html__( 'Last modified', 'media-library-assistant' ) . ":\n <b>%1\$s</b></span>\n", esc_html( $modified_date ) );
		echo "</div><!-- .misc-pub-section -->\n";

		echo '<div class="misc-pub-section mla-links">' . "\n";

		$view_args = array( 'page' => MLACore::ADMIN_PAGE_SLUG, 'mla_item_ID' => $post->ID );
		if ( isset( $_REQUEST['mla_source'] ) ) {
			$view_args['mla_source'] = sanitize_text_field( wp_unslash( $_REQUEST['mla_source'] ) );
		
			// apply_filters( 'get_delete_post_link', wp_nonce_url( $delete_link, "$action-post_{$post->ID}" ), $post->ID, $force_delete ) in /wp-includes/link-template.php
			add_filter( 'get_delete_post_link', 'MLAEdit::get_delete_post_link_filter', 10, 3 );
		}
		
		if ( isset( $_REQUEST['lang'] ) ) {
			$view_args['lang'] = sanitize_text_field( wp_unslash( $_REQUEST['lang'] ) );
		}

		echo '<span id="mla_metadata_links" style="font-weight: bold; line-height: 2em">';

		if ( isset( $_REQUEST['mla_source'] ) ) {
			echo '<input name="mla_source" type="hidden" id="mla_source" value="' . esc_html( sanitize_text_field( wp_unslash( $_REQUEST['mla_source'] ) ) ) . '" />';
		}

		if ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ALLOW_CUSTOM_FIELD_MAPPING ) ) {
			echo '<a href="' . add_query_arg( $view_args, MLACore::mla_nonce_url( 'upload.php?mla_admin_action=' . MLACore::MLA_ADMIN_SINGLE_CUSTOM_FIELD_MAP, MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME ) ) . '" title="' . __( 'Map Custom Field metadata for this item', 'media-library-assistant' ) . '">' . __( 'Map Custom Field metadata', 'media-library-assistant' ) . '</a><br>'; // phpcs:ignore
		}

		if ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_ALLOW_IPTC_EXIF_MAPPING ) ) {
			echo '<a href="' . add_query_arg( $view_args, MLACore::mla_nonce_url( 'upload.php?mla_admin_action=' . MLACore::MLA_ADMIN_SINGLE_MAP, MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME ) ) . '" title="' . __( 'Map IPTC/EXIF metadata for this item', 'media-library-assistant' ) . '">' . __( 'Map IPTC/EXIF metadata', 'media-library-assistant' ) . '</a>'; // phpcs:ignore
		}

		echo "</span>\n";
		echo "</div><!-- .misc-pub-section -->\n";
	} // mla_attachment_submitbox_action

	/**
	 * Adds mla_source argument to Trash/Delete link.
	 * Declared public because it is a filter.
	 *
	 * @since 2.25
	 *
	 * @param string $link         The delete link.
	 * @param int    $post_id      Post ID.
	 * @param bool   $force_delete Whether to bypass the trash and force deletion. Default false.
	 */
	public static function get_delete_post_link_filter( $link, $post_id, $force_delete ) {
		/*
		 * Add mla_source to force return to the Media/Assistant submenu
		 */
		if ( $force_delete ) {
			$link = add_query_arg( 'mla_source', 'delete', $link );
		} else {
			$link = add_query_arg( 'mla_source', 'trash', $link );
		}

		return $link;
	} // get_delete_post_link_filter

	/**
	 * Registers meta boxes for the Edit Media screen.
	 * Declared public because it is an action.
	 *
	 * @since 0.80
	 *
	 * @param	string	type of the current post, e.g., 'attachment' (optional, default 'unknown') 
	 * @param	object	current post (optional, default (object) array ( 'ID' => 0 ))
	 *
	 * @return	void
	 */
	public static function mla_add_meta_boxes_action( $post_type = 'unknown', $post = NULL ) {
		/*
		 * Plugins call this action with varying numbers of arguments!
		 */
		if ( NULL == $post ) {
			$post = (object) array ( 'ID' => 0 );
		}

		if ( 'attachment' != $post_type ) {
			return;
		}

		/*
		 * Use the mla_checklist_meta_box callback function for MLA supported taxonomies
		 */
		global $wp_meta_boxes;
		$screen = convert_to_screen( 'attachment' );
		$page = $screen->id;

		if ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_EDIT_MEDIA_SEARCH_TAXONOMY ) ) {
			$taxonomies = get_taxonomies( array ( 'show_ui' => true ), 'objects' );
			foreach ( $taxonomies as $key => $value ) {
				if ( MLACore::mla_taxonomy_support( $key ) ) {
					if ( $value->hierarchical ) {
						foreach ( array_keys( $wp_meta_boxes[$page] ) as $a_context ) {
							foreach ( array('high', 'sorted', 'core', 'default', 'low') as $a_priority ) {
								if ( isset( $wp_meta_boxes[$page][$a_context][$a_priority][ $key . 'div' ] ) ) {
									$box = &$wp_meta_boxes[$page][$a_context][$a_priority][ $key . 'div' ];
									if ( 'post_categories_meta_box' == $box['callback'] ) {
										$box['callback'] = 'MLACore::mla_checklist_meta_box';
									}
								} // isset $box
							} // foreach priority
						} // foreach context
					} /* hierarchical */ elseif ( MLACore::mla_taxonomy_support( $key, 'flat-checklist' ) ) {
						foreach ( array_keys( $wp_meta_boxes[$page] ) as $a_context ) {
							foreach ( array('high', 'sorted', 'core', 'default', 'low') as $a_priority ) {
								if ( isset( $wp_meta_boxes[$page][$a_context][$a_priority][ 'tagsdiv-' . $key ] ) ) {
									$box = &$wp_meta_boxes[$page][$a_context][$a_priority][ 'tagsdiv-' . $key ];
									if ( 'post_tags_meta_box' == $box['callback'] ) {
										$box['callback'] = 'MLACore::mla_checklist_meta_box';
									}
								} // isset $box
							} // foreach priority
						} // foreach context
					} // flat checklist
				} // is supported
			} // foreach
		} // MLA_EDIT_MEDIA_SEARCH_TAXONOMY

		if ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_EDIT_MEDIA_META_BOXES ) ) {
			$active_boxes = apply_filters( 'mla_edit_media_meta_boxes', array( 
			'mla-parent-info' => 'mla-parent-info', 'mla-menu-order' => 'mla-menu-order', 'mla-image-metadata' => 'mla-image-metadata', 'mla-file-metadata' => 'mla-file-metadata', 'mla-featured-in' => 'mla-featured-in', 'mla-inserted-in' => 'mla-inserted-in', 'mla-gallery-in' => 'mla-gallery-in', 'mla-mla-gallery-in' => 'mla-mla-gallery-in' ) );

			if ( isset( $active_boxes['mla-parent-info'] ) ) {
				add_meta_box( 'mla-parent-info', __( 'Parent Info', 'media-library-assistant' ), 'MLAEdit::mla_parent_info_handler', 'attachment', 'normal', 'core' );
			}

			if ( isset( $active_boxes['mla-menu-order'] ) ) {
				add_meta_box( 'mla-menu-order', __( 'Menu Order', 'media-library-assistant' ), 'MLAEdit::mla_menu_order_handler', 'attachment', 'normal', 'core' );
			}

			if ( isset( $active_boxes['mla-image-metadata'] ) ) {
				$image_metadata = get_metadata( 'post', $post->ID, '_wp_attachment_metadata', true );
				if ( !empty( $image_metadata ) ) {
					add_meta_box( 'mla-image-metadata', __( 'Attachment Metadata', 'media-library-assistant' ), 'MLAEdit::mla_image_metadata_handler', 'attachment', 'normal', 'core' );
				}
			}

			if ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_FILE_METADATA_META_BOX ) ) {
				if ( isset( $active_boxes['mla-file-metadata'] ) ) {
					add_meta_box( 'mla-file-metadata', __( 'Attachment File Metadata', 'media-library-assistant' ), 'MLAEdit::mla_file_metadata_handler', 'attachment', 'normal', 'core' );
				}
			}

			if ( isset( $active_boxes['mla-featured-in'] ) && MLACore::$process_featured_in ) {
				add_meta_box( 'mla-featured-in', __( 'Featured in', 'media-library-assistant' ), 'MLAEdit::mla_featured_in_handler', 'attachment', 'normal', 'core' );
			}

			if ( isset( $active_boxes['mla-inserted-in'] ) && MLACore::$process_inserted_in ) {
				add_meta_box( 'mla-inserted-in', __( 'Inserted in', 'media-library-assistant' ), 'MLAEdit::mla_inserted_in_handler', 'attachment', 'normal', 'core' );
			}

			if ( isset( $active_boxes['mla-gallery-in'] ) && MLACore::$process_gallery_in ) {
				add_meta_box( 'mla-gallery-in', __( 'Gallery in', 'media-library-assistant' ), 'MLAEdit::mla_gallery_in_handler', 'attachment', 'normal', 'core' );
			}

			if ( isset( $active_boxes['mla-mla-gallery-in'] ) && MLACore::$process_mla_gallery_in ) {
				add_meta_box( 'mla-mla-gallery-in', __( 'MLA Gallery in', 'media-library-assistant' ), 'MLAEdit::mla_mla_gallery_in_handler', 'attachment', 'normal', 'core' );
			}

		}
	} // mla_add_meta_boxes_action

	/**
	 * Add contextual help tabs to the WordPress Edit Media page
	 *
	 * @since 0.90
	 *
	 * @param	string	title as shown on the screen
	 * @param	string	title as shown in the HTML header
	 *
	 * @return	void
	 */
	public static function mla_edit_add_help_tab( $admin_title, $title ) {
		$screen = get_current_screen();

		/*
		 * Upload New Media Bulk Edit Area
		 */
		if ( ( 'media' == $screen->id ) && ( 'add' == $screen->action ) ) {
			$template_array = MLACore::mla_load_template( 'help-for-upload-new-media.tpl' );
			if ( empty( $template_array ) ) {
				return $admin_title;
			}

			/*
			 * Replace sidebar content
			 */
			if ( !empty( $template_array['sidebar'] ) ) {
				$page_values = array( 'settingsURL' => admin_url('options-general.php') );
				$content = MLAData::mla_parse_template( $template_array['sidebar'], $page_values );
				$screen->set_help_sidebar( $content );
			}
			unset( $template_array['sidebar'] );

			/*
			 * Provide explicit control over tab order
			 */
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
					MLACore::mla_debug_add( sprintf( _x( '%1$s: %2$s discarding "%3$s"; no title/order', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), 'mla_edit_add_help_tab', $id ), MLACore::MLA_DEBUG_CATEGORY_ANY );
				}
			}

			ksort( $tab_array, SORT_NUMERIC );
			foreach ( $tab_array as $indx => $value ) {
				$page_values = array( 'settingsURL' => admin_url('options-general.php') );
				$value = MLAData::mla_parse_template( $value, $page_values );
				$screen->add_help_tab( $value );
			}

			return $admin_title;
		}

		/*
		 * Media/Edit Media submenu
		 */
		if ( ( 'attachment' != $screen->id ) || ( 'attachment' != $screen->post_type ) || ( 'post' != $screen->base ) ) {
			return $admin_title;
		}

		$template_array = MLACore::mla_load_template( 'help-for-edit_attachment.tpl' );
		if ( empty( $template_array ) ) {
			return $admin_title;
		}

		/*
		 * Provide explicit control over tab order
		 */
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
				MLACore::mla_debug_add( sprintf( _x( '%1$s: %2$s discarding "%3$s"; no title/order', 'error_log', 'media-library-assistant' ), __( 'ERROR', 'media-library-assistant' ), 'mla_edit_add_help_tab', $id ), MLACore::MLA_DEBUG_CATEGORY_ANY );
			}
		}

		ksort( $tab_array, SORT_NUMERIC );
		foreach ( $tab_array as $indx => $value ) {
			$screen->add_help_tab( $value );
		}

	return $admin_title;
	}

	/**
	 * Where-used values for the current item
	 *
	 * This array contains the Featured/Inserted/Gallery/MLA Gallery references for the item.
	 * The array is built once each page load and cached for subsequent calls.
	 *
	 * @since 0.80
	 *
	 * @var	array
	 */
	private static $mla_references = NULL;

	/**
	 * Renders the Parent Info meta box on the Edit Media page.
	 * Declared public because it is a callback function.
	 *
	 * @since 0.80
	 *
	 * @param	object	current post
	 *
	 * @return	void	echoes the HTML markup for the meta box content
	 */
	public static function mla_parent_info_handler( $post ) {
		if ( is_null( self::$mla_references ) ) {
			self::$mla_references = MLAQuery::mla_fetch_attachment_references( $post->ID, $post->post_parent );
		}

		if ( is_array( self::$mla_references ) ) {
			if ( empty(self::$mla_references['parent_title'] ) ) {
				$parent_info = self::$mla_references['parent_errors'];
			} else {
				$flag = ', ';
				switch ( self::$mla_references['parent_status'] ) {
					case 'future' :
						$flag .= __('Scheduled');
						break;
					case 'pending' :
						$flag .= _x('Pending', 'post state');
						break;
					case 'draft' :
						$flag .= __('Draft');
						break;
					default:
						$flag = '';
				}

				$parent_info = sprintf( '%1$s (%2$s%3$s) %4$s', self::$mla_references['parent_title'], self::$mla_references['parent_type'], $flag, self::$mla_references['parent_errors'] );
			}
		} else {
			$parent_info = '';
		}

		$parent_info = apply_filters( 'mla_parent_info_meta_box', $parent_info, self::$mla_references, $post );

		echo '<label class="screen-reader-text" for="mla_post_parent">' . esc_html__( 'Post Parent', 'media-library-assistant' ) . '</label><input name="mla_post_parent" id="mla_post_parent" type="text" value="' . esc_html( $post->post_parent ) . "\" />\n";
		echo '<label class="screen-reader-text" for="mla_parent_info">' . esc_html__( 'Select Parent', 'media-library-assistant' ) . '</label><input name="post_parent_set" id="mla_set_parent" class="button-primary parent" type="button" value="' . esc_html__( 'Select', 'media-library-assistant' ) . '" />';
		echo '<label class="screen-reader-text" for="mla_parent_info">' . esc_html__( 'Parent Info', 'media-library-assistant' ) . '</label><input name="mla_parent_info" id="mla_parent_info" type="text" readonly="readonly" disabled="disabled" value="' . esc_attr( $parent_info ) . "\" /></span>\n";

		echo MLA::mla_set_parent_form( false ); // phpcs:ignore
	}

	/**
	 * Renders the Menu Order meta box on the Edit Media page.
	 * Declared public because it is a callback function.
	 *
	 * @since 0.80
	 *
	 * @param	object	current post
	 *
	 * @return	void	echoes the HTML markup for the meta box content
	 */
	public static function mla_menu_order_handler( $post ) {

		$menu_order = apply_filters( 'mla_menu_order_meta_box', $post->menu_order, $post );

		echo '<label class="screen-reader-text" for="mla_menu_order">' . esc_html__( 'Menu Order', 'media-library-assistant' ) . '</label><input name="mla_menu_order" type="text" size="4" id="mla_menu_order" value="' . esc_attr( $menu_order ) . "\" />\n";
	}

	/**
	 * Renders the Image Metadata meta box on the Edit Media page.
	 * Declared public because it is a callback function.
	 *
	 * @since 0.80
	 *
	 * @param	object	current post
	 *
	 * @return	void	echoes the HTML markup for the meta box content
	 */
	public static function mla_image_metadata_handler( $post ) {
		$metadata = MLAQuery::mla_fetch_attachment_metadata( $post->ID );

		if ( isset( $metadata['mla_wp_attachment_metadata'] ) ) {
			$value = var_export( $metadata['mla_wp_attachment_metadata'], true );
		} else {
			$value = '';
		}

		$value = apply_filters( 'mla_image_metadata_meta_box', array( 'value' => $value, 'rows' => 5, 'cols' => 80 ), $metadata, $post );

		$html =  '<label class="screen-reader-text" for="mla_image_metadata">' . __( 'Attachment Metadata', 'media-library-assistant' ) . '</label><textarea class="readonly" id="mla_image_metadata" rows="' . absint( $value['rows'] ) . '" cols="' . absint( $value['cols'] ) . '" readonly="readonly" name="mla_image_metadata" >' . esc_textarea( $value['value'] ) . "</textarea>\n";
		echo apply_filters( 'mla_image_metadata_meta_box_html', $html, $value, $metadata, $post ); // phpcs:ignore
	}

	/**
	 * Renders the IPTC, EXIF, XMP, PDF and/or MSO Metadata meta box on the Edit Media page.
	 * Declared public because it is a callback function.
	 *
	 * @since 0.80
	 *
	 * @param	object	current post
	 *
	 * @return	void	echoes the HTML markup for the meta box content
	 */
	public static function mla_file_metadata_handler( $post ) {
		$value = MLAData::mla_compose_attachment_metadata( $post->ID );
		$value = apply_filters( 'mla_file_metadata_meta_box', array( 'value' => $value, 'rows' => 5, 'cols' => 80, 'flags' => ENT_SUBSTITUTE ), $post );

		// Can't use esc_textarea( $value['value'] ) because the value might contain invalid code unit sequences
		$html =  '<label class="screen-reader-text" for="mla_file_metadata">' . __( 'Attachment File Metadata', 'media-library-assistant' ) . '</label><textarea class="readonly" id="mla_file_metadata" rows="' . absint( $value['rows'] ) . '" cols="' . absint( $value['cols'] ) . '" readonly="readonly" name="mla_file_metadata" >' . htmlspecialchars( $value['value'], $value['flags'] ) . "</textarea>\n";
		echo apply_filters( 'mla_file_metadata_meta_box_html', $html, $value, $post ); // phpcs:ignore
	}

	/**
	 * Renders the Featured in meta box on the Edit Media page.
	 * Declared public because it is a callback function.
	 *
	 * @since 0.80
	 *
	 * @param	object	current post
	 *
	 * @return	void	echoes the HTML markup for the meta box content
	 */
	public static function mla_featured_in_handler( $post ) {
		if ( is_null( self::$mla_references ) ) {
			self::$mla_references = MLAQuery::mla_fetch_attachment_references( $post->ID, $post->post_parent );
		}

		$features = '';
		if ( is_array( self::$mla_references ) ) {
			foreach ( self::$mla_references['features'] as $feature_id => $feature ) {
				if ( $feature_id == $post->post_parent ) {
					$parent = __( 'PARENT', 'media-library-assistant' ) . ' ';
				} else {
					$parent = '';
				}

				$features .= sprintf( '%1$s (%2$s %3$s), %4$s', /*$1%s*/ $parent, /*$2%s*/ $feature->post_type, /*$3%s*/ $feature_id, /*$4%s*/ $feature->post_title ) . "\n";
			} // foreach $feature
		}

		$features = apply_filters( 'mla_featured_in_meta_box', array( 'features' => $features, 'rows' => 5, 'cols' => 80 ), self::$mla_references, $post );

		$html = '<label class="screen-reader-text" for="mla_featured_in">' . __( 'Featured in', 'media-library-assistant' ) . '</label><textarea class="readonly" id="mla_featured_in" rows="' . absint( $features['rows'] ) . '" cols="' . absint( $features['cols'] ) . '" readonly="readonly" name="mla_featured_in" >' . esc_textarea( $features['features'] ) . "</textarea>\n";
		echo apply_filters( 'mla_featured_in_meta_box_html', $html, $features, self::$mla_references, $post ); // phpcs:ignore
	}

	/**
	 * Renders the Inserted in meta box on the Edit Media page.
	 * Declared public because it is a callback function.
	 *
	 * @since 0.80
	 *
	 * @param	object	current post
	 *
	 * @return	void	echoes the HTML markup for the meta box content
	 */
	public static function mla_inserted_in_handler( $post ) {
		if ( is_null( self::$mla_references ) ) {
			self::$mla_references = MLAQuery::mla_fetch_attachment_references( $post->ID, $post->post_parent );
		}

		$inserts = '';
		if ( is_array( self::$mla_references ) ) {
			foreach ( self::$mla_references['inserts'] as $file => $insert_array ) {
				$inserts .= $file . "\n";

				foreach ( $insert_array as $insert ) {
					if ( $insert->ID == $post->post_parent ) {
						$parent = '  ' . __( 'PARENT', 'media-library-assistant' ) . ' ';
					} else {
						$parent = '  ';
					}

					$inserts .= sprintf( '%1$s (%2$s %3$s), %4$s', /*$1%s*/ $parent, /*$2%s*/ $insert->post_type, /*$3%s*/ $insert->ID, /*$4%s*/ $insert->post_title ) . "\n";
				} // foreach $insert
			} // foreach $file
		} // is_array

		$inserts = apply_filters( 'mla_inserted_in_meta_box', array( 'inserts' => $inserts, 'rows' => 5, 'cols' => 80 ), self::$mla_references, $post );

		$html = '<label class="screen-reader-text" for="mla_inserted_in">' . __( 'Inserted in', 'media-library-assistant' ) . '</label><textarea class="readonly" id="mla_inserted_in" rows="' . absint( $inserts['rows'] ) . '" cols="' . absint( $inserts['cols'] ) . '" readonly="readonly" name="mla_inserted_in" >' . esc_textarea( $inserts['inserts'] ) . "</textarea>\n";
		echo apply_filters( 'mla_inserted_in_meta_box_html', $html, $inserts, self::$mla_references, $post ); // phpcs:ignore
	}

	/**
	 * Renders the Gallery in meta box on the Edit Media page.
	 * Declared public because it is a callback function.
	 *
	 * @since 0.80
	 *
	 * @param	object	current post
	 *
	 * @return	void	echoes the HTML markup for the meta box content
	 */
	public static function mla_gallery_in_handler( $post ) {
		if ( is_null( self::$mla_references ) ) {
			self::$mla_references = MLAQuery::mla_fetch_attachment_references( $post->ID, $post->post_parent );
		}

		$galleries = '';
		if ( is_array( self::$mla_references ) ) {
			foreach ( self::$mla_references['galleries'] as $gallery_id => $gallery ) {
				if ( $gallery_id == $post->post_parent ) {
					$parent = __( 'PARENT', 'media-library-assistant' ) . ' ';
				} else {
					$parent = '';
				}

				$galleries .= sprintf( '%1$s (%2$s %3$s), %4$s', /*$1%s*/ $parent, /*$2%s*/ $gallery['post_type'], /*$3%s*/ $gallery_id, /*$4%s*/ $gallery['post_title'] ) . "\n";
			} // foreach $feature
		}

		$galleries = apply_filters( 'mla_gallery_in_meta_box', array( 'galleries' => $galleries, 'rows' => 5, 'cols' => 80 ), self::$mla_references, $post );

		$html = '<label class="screen-reader-text" for="mla_gallery_in">' . __( 'Gallery in', 'media-library-assistant' ) . '</label><textarea class="readonly" id="mla_gallery_in" rows="' . absint( $galleries['rows'] ) . '" cols="' . absint( $galleries['cols'] ) . '" readonly="readonly" name="mla_gallery_in" >' . esc_textarea( $galleries['galleries'] ) . "</textarea>\n";
		echo apply_filters( 'mla_gallery_in_meta_box_html', $html, $galleries, self::$mla_references, $post ); // phpcs:ignore
	}

	/**
	 * Renders the MLA Gallery in meta box on the Edit Media page.
	 * Declared public because it is a callback function.
	 *
	 * @since 0.80
	 *
	 * @param	object	current post
	 *
	 * @return	void	echoes the HTML markup for the meta box content
	 */
	public static function mla_mla_gallery_in_handler( $post ) {
		if ( is_null( self::$mla_references ) ) {
			self::$mla_references = MLAQuery::mla_fetch_attachment_references( $post->ID, $post->post_parent );
		}

		$galleries = '';
		if ( is_array( self::$mla_references ) ) {
			foreach ( self::$mla_references['mla_galleries'] as $gallery_id => $gallery ) {
				if ( $gallery_id == $post->post_parent ) {
					$parent = __( 'PARENT', 'media-library-assistant' ) . ' ';
				} else {
					$parent = '';
				}

				$galleries .= sprintf( '%1$s (%2$s %3$s), %4$s', /*$1%s*/ $parent, /*$2%s*/ $gallery['post_type'], /*$3%s*/ $gallery_id, /*$4%s*/ $gallery['post_title'] ) . "\n";
			} // foreach $feature
		}

		$galleries = apply_filters( 'mla_mla_gallery_in_meta_box', array( 'galleries' => $galleries, 'rows' => 5, 'cols' => 80 ), self::$mla_references, $post );

		$html = '<label class="screen-reader-text" for="mla_mla_gallery_in">' . __( 'MLA Gallery in', 'media-library-assistant' ) . '</label><textarea class="readonly" id="mla_mla_gallery_in" rows="' . absint( $galleries['rows'] ) . '" cols="' . absint( $galleries['cols'] ) . '" readonly="readonly" name="mla_mla_gallery_in" >' . esc_textarea( $galleries['galleries'] ) . "</textarea>\n";
		echo apply_filters( 'mla_mla_gallery_in_meta_box_html', $html, $galleries, self::$mla_references, $post ); // phpcs:ignore
	}

	/**
	 * Saves updates from the Edit Media screen.
	 * Declared public because it is an action.
	 *
	 * @since 0.80
	 *
	 * @param	integer	ID of the current post
	 *
	 * @return	void
	 */
	public static function mla_edit_attachment_action( $post_ID ) {
		$new_data = array();
		if ( isset( $_REQUEST['mla_post_parent'] ) ) {
			$new_data['post_parent'] = absint( wp_unslash( $_REQUEST['mla_post_parent'] ) );
		}

		if ( isset( $_REQUEST['mla_menu_order'] ) ) {
			$new_data['menu_order'] = absint( wp_unslash( $_REQUEST['mla_menu_order'] ) );
		}

		if ( isset( $_REQUEST['mla_upload'] ) ) {
			$date = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['mla_upload'] ) );
			$new_date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $date['aa'], $date['mm'], $date['jj'], $date['hh'], $date['mn'], $date['ss'] );
			if ( wp_checkdate( $date['mm'], $date['jj'], $date['aa'], $new_date ) ) {
				if ( $date['original'] !== $new_date ) {
					$new_data['post_date'] = $new_date;
					$new_data['post_date_gmt'] = get_gmt_from_date( $new_date );
				}
			}
		}

		if ( !empty( $new_data ) ) {
			MLAData::mla_update_single_item( $post_ID, $new_data );
		}
	} // mla_edit_attachment_action
} //Class MLAEdit

?>
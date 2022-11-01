<?php
/**
 * Helper functions.
 *
 * @since 2.0
 * @package fusion-library
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse str without max input vars.
 *
 * @since 2.0
 * @param string $string The string we want to convert.
 * @param bool   $strip_slashes Decides if slashes should be stripped.
 * @return array
 */
function fusion_string_to_array( $string, $strip_slashes = true ) {

	// If already an array, return early.
	if ( is_array( $string ) ) {
		return wp_unslash( $string );
	}

	$string = $strip_slashes ? stripslashes( $string ) : $string;

	if ( empty( $string ) ) {
		return false;
	}

	$result = [];
	$pairs  = explode( '&', $string );

	foreach ( $pairs as $key => $pair ) {
		// Use the original parse_str() on each element.
		parse_str( $pair, $params );

		$k = key( $params );

		if ( ! isset( $result[ $k ] ) ) {
			$result += $params;
		} else {
			$result[ $k ] = fusion_array_merge_recursive( $result[ $k ], $params[ $k ] );
		}
	}

	return $result;
}

/**
 * Merge arrays without converting values with duplicate keys to arrays as array_merge_recursive does.
 *
 * @since 2.0
 * @author  harunbasic
 * @param   array $array1 The 1st array.
 * @param   array $array2 The 2nd array.
 * @return  array         The 2 arrays, merged.
 */
function fusion_array_merge_recursive( array $array1, array $array2 ) {
	$merged = $array1;

	foreach ( $array2 as $key => $value ) {
		if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
			$merged[ $key ] = fusion_array_merge_recursive( $merged[ $key ], $value );
		} elseif ( is_numeric( $key ) && isset( $merged[ $key ] ) ) {
			$merged[] = $value;
		} else {
			$merged[ $key ] = $value;
		}
	}

	return $merged;
}

/**
 * Get front-edit permalink.
 *
 * @since 2.0
 * @param object $admin_bar Admin bar if available.
 * @return string
 */
function fusion_app_get_permalink( $admin_bar = false ) {
	global $post;

	$customize_url = '';

	if ( is_admin() ) {

		$page_id = get_the_ID();

		if ( $page_id ) {
			$customize_url = get_permalink( $page_id );
		}

		if ( ! $customize_url && is_object( $admin_bar ) ) {
			$view = $admin_bar->get_node( 'view' );
			if ( is_object( $view ) ) {
				$customize_url = $view->href;
			}
		}

		if ( ! $customize_url ) {
			$customize_url = home_url();
		}
	} else {

		$page_id = fusion_library()->get_page_id();
		$page_id = ( ! $page_id && isset( $post->ID ) ) ? $post->ID : $page_id;

		if ( is_home() && get_option( 'page_for_posts' ) === $page_id ) {

			// Blog page.
			$customize_url = get_permalink( $page_id );
		} elseif ( is_home() ) {
			$customize_url = home_url();
		} elseif ( is_tax() ) {
			$customize_url = get_term_link( get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
		} elseif ( is_category() ) {
			$customize_url = get_category_link( get_queried_object_id() );
		} elseif ( is_tag() ) {
			$customize_url = get_tag_link( get_queried_object_id() );
		} elseif ( function_exists( 'tribe_get_events_link' ) && function_exists( 'tribe_is_events_home' ) && tribe_is_events_home() ) {
			$customize_url = tribe_get_events_link();
		} elseif ( true === fusion_should_add_fe_edit_link() ) {
			$customize_url = get_permalink( $page_id );
		}
	}

	$customize_url = is_wp_error( $customize_url ) ? false : $customize_url;

	return $customize_url;
}

/**
 * Checks if 'Fusion Edit' link should be added to admin toolbar.
 *
 * @since 2.0
 * @return bool
 */
function fusion_should_add_fe_edit_link() {
	if ( 0 === fusion_library()->get_page_id() || false === fusion_library()->get_page_id() || '0-archive' === fusion_library()->get_page_id() || is_preview_only() ) {
		return false;
	}

	return true;
}

/**
 * Are we on the builder frame?
 *
 * @since 2.0
 * @return bool
 */
function fusion_is_builder_frame() {
	if ( class_exists( 'Fusion_App' ) ) {
		$fusion_app = Fusion_App::get_instance();
		return $fusion_app->get_builder_status();
	}
	return false;
}

/**
 * Are we on the preview frame?
 *
 * @since 2.0
 * @return bool
 */
function fusion_is_preview_frame() {
	if ( class_exists( 'Fusion_App' ) ) {
		$fusion_app = Fusion_App::get_instance();
		return $fusion_app->get_preview_status();
	}
	return false;
}

/**
 * Check if app is only for preview.
 *
 * @since 1.0
 */
function is_preview_only() {
	if ( class_exists( 'Fusion_App' ) ) {
		$fusion_app = Fusion_App::get_instance();
		return $fusion_app->get_preview_only_status();
	}
	return false;
}

/**
 * Fusion app text strings.
 *
 * @since 1.0
 */
function fusion_app_textdomain_strings() {

	$fusion_settings = awb_get_fusion_settings();

	$text_strings = [

		'custom_css'                                  => esc_html__( 'Custom CSS', 'fusion-builder' ),
		'builder'                                     => esc_html__( 'Builder', 'fusion-builder' ),
		'library'                                     => esc_html__( 'Library', 'fusion-builder' ),
		'add_css_code_here'                           => esc_html__( 'Add your CSS code here...', 'fusion-builder' ),
		'delete_page_layout'                          => esc_html__( 'Delete page layout', 'fusion-builder' ),
		'undo'                                        => esc_html__( 'Undo', 'fusion-builder' ),
		'redo'                                        => esc_html__( 'Redo', 'fusion-builder' ),
		'save'                                        => esc_html__( 'Save', 'fusion-builder' ),
		'dont_save'                                   => esc_html__( 'Don\'t Save', 'fusion-builder' ),
		'leave'                                       => esc_html__( 'Save & Leave', 'fusion-builder' ),
		'just_leave'                                  => esc_html__( 'Just Leave', 'fusion-builder' ),
		'delete_item'                                 => esc_html__( 'Delete item', 'fusion-builder' ),
		'clone_item'                                  => esc_html__( 'Clone item', 'fusion-builder' ),
		'edit_item'                                   => esc_html__( 'Edit item', 'fusion-builder' ),
		'element_settings'                            => esc_html__( 'Element Options', 'fusion-builder' ),
		/* translators: Element settings. */
		'custom_element_settings'                     => esc_html__( '%s Options', 'fusion-builder' ),
		'full_width_section'                          => esc_html__( 'Container', 'fusion-builder' ),
		'section_settings'                            => esc_html__( 'Container Options', 'fusion-builder' ),
		'insert_section'                              => esc_html__( 'Insert Container', 'fusion-builder' ),
		'clone_section'                               => esc_html__( 'Clone Container', 'fusion-builder' ),
		'save_section'                                => esc_html__( 'Save Container', 'fusion-builder' ),
		'delete_section'                              => esc_html__( 'Delete Container', 'fusion-builder' ),
		'builder_sections'                            => esc_html__( 'Builder Containers', 'fusion-builder' ),
		'click_to_toggle'                             => esc_html__( 'Click to toggle', 'fusion-builder' ),
		'save_custom_section'                         => esc_html__( 'Save Custom Container', 'fusion-builder' ),
		'save_custom_template'                        => esc_html__( 'Save Custom Template', 'fusion-builder' ),
		'save_custom_section_info'                    => esc_html__( 'Custom containers will be stored and managed on the Library tab', 'fusion-builder' ),
		'enter_name'                                  => esc_html__( 'Enter Name...', 'fusion-builder' ),
		'column'                                      => esc_html__( 'Column', 'fusion-builder' ),
		'columns'                                     => esc_html__( 'Columns', 'fusion-builder' ),
		'resize_column'                               => esc_html__( 'Resize column', 'fusion-builder' ),
		'resized_column'                              => esc_html__( 'Resized Column to', 'fusion-builder' ),
		'column_library'                              => esc_html__( 'Column Options', 'fusion-builder' ),
		'clone_column'                                => esc_html__( 'Clone column', 'fusion-builder' ),
		'save_column'                                 => esc_html__( 'Save column', 'fusion-builder' ),
		'delete_column'                               => esc_html__( 'Delete column', 'fusion-builder' ),
		'delete_row'                                  => esc_html__( 'Delete row', 'fusion-builder' ),
		'clone_column'                                => esc_html__( 'Clone column', 'fusion-builder' ),
		'save_custom_column'                          => esc_html__( 'Save Custom Column', 'fusion-builder' ),
		'save_custom_column_info'                     => esc_html__( 'Custom elements will be stored and managed on the Library tab', 'fusion-builder' ),
		'add_element'                                 => esc_html__( 'Add element', 'fusion-builder' ),
		'element'                                     => esc_html__( 'Element', 'fusion-builder' ),
		'insert_columns'                              => esc_html__( 'Insert Columns', 'fusion-builder' ),
		'search'                                      => esc_html__( 'Search', 'fusion-builder' ),
		'search_elements'                             => esc_html__( 'Search Elements', 'fusion-builder' ),
		'search_containers'                           => esc_html__( 'Search Containers', 'fusion-builder' ),
		'search_columns'                              => esc_html__( 'Search Columns', 'fusion-builder' ),
		'builder_columns'                             => esc_html__( 'Builder Columns', 'fusion-builder' ),
		'library_columns'                             => esc_html__( 'Library Columns', 'fusion-builder' ),
		'library_sections'                            => esc_html__( 'Library Containers', 'fusion-builder' ),
		'cancel'                                      => esc_html__( 'Cancel', 'fusion-builder' ),
		'select_element'                              => esc_html__( 'Select Element', 'fusion-builder' ),
		'builder_elements'                            => esc_html__( 'Design Elements', 'fusion-builder' ),
		'layout_section_elements'                     => esc_html__( 'Layout Elements', 'fusion-builder' ),
		'form_elements'                               => esc_html__( 'Form Elements', 'fusion-builder' ),
		'library_elements'                            => esc_html__( 'Library Elements', 'fusion-builder' ),
		'generator_elements_tooltip'                  => esc_html__( 'Inline element for usage in the Avada Builder Generator.', 'fusion-builder' ),
		'template_max_use_limit'                      => esc_html__( 'This element can be added only', 'fusion-builder' ),
		'time'                                        => esc_html__( 'time.', 'fusion-builder' ),
		'times'                                       => esc_html__( 'times.', 'fusion-builder' ),
		'inner_columns'                               => esc_html__( 'Nested Columns', 'fusion-builder' ),
		'element_settings'                            => esc_html__( 'Element Options', 'fusion-builder' ),
		'clone_element'                               => esc_html__( 'Clone Element', 'fusion-builder' ),
		'save_element'                                => esc_html__( 'Save Element', 'fusion-builder' ),
		'save_global'                                 => esc_html__( 'Save As Global', 'fusion-builder' ),
		'delete_element'                              => esc_html__( 'Delete Element', 'fusion-builder' ),
		'save_custom_element'                         => esc_html__( 'Save Custom Element', 'fusion-builder' ),
		'save_custom_element_info'                    => esc_html__( 'Custom elements will be stored and managed on the Library tab', 'fusion-builder' ),
		'add_edit_items'                              => esc_html__( 'Add / Edit Items', 'fusion-builder' ),
		'sortable_items_info'                         => esc_html__( 'Add or edit new items for this element.  Drag and drop them into the desired order.', 'fusion-builder' ),
		'delete_inner_columns'                        => esc_html__( 'Delete inner columns', 'fusion-builder' ),
		'clone_inner_columns'                         => esc_html__( 'Clone inner columns', 'fusion-builder' ),
		'save_inner_columns'                          => esc_html__( 'Save inner columns', 'fusion-builder' ),
		'delete_inner_columns'                        => esc_html__( 'Delete inner columns', 'fusion-builder' ),
		'save_nested_columns'                         => esc_html__( 'Save Nested Columns', 'fusion-builder' ),
		'select_options_or_leave_blank_for_all'       => esc_html__( 'Select or Leave Blank for All', 'fusion-builder' ),
		'select_or_leave_blank_for_global'            => esc_html__( 'Select or Leave Blank for Global', 'fusion-builder' ),
		'select_categories_or_leave_blank_for_all'    => esc_html__( 'Select or Leave Blank for All', 'fusion-builder' ),
		'select_categories_or_leave_blank_for_none'   => esc_html__( 'Select or Leave Blank for None', 'fusion-builder' ),
		'select_post_status_leave_blank_for_publish'  => esc_html__( 'Select or Leave Blank for Published', 'fusion-builder' ),
		'please_enter_element_name'                   => esc_html__( 'Please enter element name', 'fusion-builder' ),
		'are_you_sure_you_want_to_delete_this_layout' => esc_html__( 'You are about to remove all page layout. Do you still want to proceed?', 'fusion-builder' ),
		'are_you_sure_you_want_to_delete_this'        => esc_html__( 'Are you sure you want to delete this ?', 'fusion-builder' ),
		'are_you_sure_you_want_to_delete_global'      => esc_html__( 'This is a global item. Deleting this element will remove it from every page you have it on. Are you sure you want to remove it?', 'fusion-builder' ),
		'global_element'                              => __( 'Global element<br>Click to disable global status', 'fusion-builder' ),
		'global_column'                               => __( 'Global column<br>Click to disable global status', 'fusion-builder' ),
		'global_container'                            => __( 'Global container<br>Click to disable global status', 'fusion-builder' ),
		'duplicate_element_name_error'                => esc_html__( 'An element with this name already exists. Please enter different name.', 'fusion-builder' ),
		'please_enter_template_name'                  => esc_html__( 'Please enter template name', 'fusion-builder' ),
		'save_page_layout'                            => esc_html__( 'Save page layout', 'fusion-builder' ),
		'upload'                                      => esc_html__( 'Upload', 'fusion-builder' ),
		'upload_image'                                => esc_html__( 'Upload Image', 'fusion-builder' ),
		'upload_audio'                                => esc_html__( 'Upload Audio', 'fusion-builder' ),
		'edit'                                        => esc_html__( 'Edit', 'fusion-builder' ),
		'remove'                                      => esc_html__( 'Remove', 'fusion-builder' ),
		'attach_images'                               => esc_html__( 'Attach Images to Gallery', 'fusion-builder' ),
		'insert'                                      => esc_html__( 'Insert', 'fusion-builder' ),
		'avada_studio'                                => esc_html__( 'Avada Studio', 'fusion-builder' ),
		'to_get_started'                              => esc_html__( 'To get started, add a Container, or add a prebuilt page.', 'fusion-builder' ),
		'to_get_started_header'                       => esc_html__( 'To get started, add a Container, or add a prebuilt header.', 'fusion-builder' ),
		'to_get_started_content'                      => esc_html__( 'To get started, add a Container, or add prebuilt content.', 'fusion-builder' ),
		'to_get_started_ptb'                          => esc_html__( 'To get started building your Page Title Bar, add a container.', 'fusion-builder' ),
		'to_get_started_footer'                       => esc_html__( 'To get started building your Footer, add a container.', 'fusion-builder' ),
		'to_get_started_form'                         => esc_html__( 'To get started building your Form, add a container.', 'fusion-builder' ),
		'to_get_started_off_canvas'                   => esc_html__( 'To get started building your Off Canvas, add a container.', 'fusion-builder' ),
		'to_get_started_sub'                          => esc_html__( 'The building process always starts with a container, then columns, then elements.', 'fusion-builder' ),
		'watch_the_video'                             => esc_html__( 'Watch The Video!', 'fusion-builder' ),
		'edit_settings'                               => esc_html__( 'Edit Settings', 'fusion-builder' ),
		'backward_history'                            => esc_html__( 'Backward History', 'fusion-builder' ),
		'duplicate_content'                           => esc_html__( 'Duplicate Content', 'fusion-builder' ),
		'forward_history'                             => esc_html__( 'Forward History', 'fusion-builder' ),
		'save_custom_content'                         => esc_html__( 'Save Custom Content', 'fusion-builder' ),
		'delete_content'                              => esc_html__( 'Delete Content', 'fusion-builder' ),
		'add_content'                                 => esc_html__( 'Add Content', 'fusion-builder' ),
		'additional_docs'                             => esc_html__( 'Click the ? icon to access Avada documentation', 'fusion-builder' ),
		'getting_started_video'                       => esc_html__( 'Getting Started Video', 'fusion-builder' ),
		'icon_control_description'                    => esc_html__( 'Icon Control Descriptions:', 'fusion-builder' ),
		'history'                                     => esc_html__( 'History', 'fusion-builder' ),
		'collapse_sections'                           => esc_html__( 'Collapse Sections', 'fusion-builder' ),
		'history_states'                              => esc_html__( 'History States', 'fusion-builder' ),
		'empty'                                       => esc_html__( 'Start', 'fusion-builder' ),
		'moved_column'                                => esc_html__( 'Moved Column', 'fusion-builder' ),
		'added_custom_element'                        => esc_html__( 'Added Custom Element: ', 'fusion-builder' ),
		'added_custom_column'                         => esc_html__( 'Added Custom Column: ', 'fusion-builder' ),
		'added_columns'                               => esc_html__( 'Added Columns', 'fusion-builder' ),
		'added_custom_section'                        => esc_html__( 'Added Custom Container: ', 'fusion-builder' ),
		'added_studio_element'                        => esc_html__( 'Added Studio Element: ', 'fusion-builder' ),
		'added_studio_column'                         => esc_html__( 'Added Studio Column: ', 'fusion-builder' ),
		'added_studio_section'                        => esc_html__( 'Added Studio Container: ', 'fusion-builder' ),
		'added_special_item'                          => esc_html__( 'Added Special Item: ', 'fusion-builder' ),
		'deleted'                                     => esc_html__( 'Deleted', 'fusion-builder' ),
		'cloned'                                      => esc_html__( 'Cloned', 'fusion-builder' ),
		'pasted'                                      => esc_html__( 'Pasted', 'fusion-builder' ),
		'moved'                                       => esc_html__( 'Moved', 'fusion-builder' ),
		'edited'                                      => esc_html__( 'Edited', 'fusion-builder' ),
		'invert'                                      => esc_html__( 'Invert', 'fusion-builder' ),
		'reset_to_default'                            => esc_html__( 'Reset to Default', 'fusion-builder' ),
		'added_nested_columns'                        => esc_html__( 'Added Nested Columns', 'fusion-builder' ),
		'edited_nested_columns'                       => esc_html__( 'Edited Nested Columns', 'fusion-builder' ),
		'deleted_nested_columns'                      => esc_html__( 'Deleted Nested Columns', 'fusion-builder' ),
		'moved_nested_column'                         => esc_html__( 'Moved Nested Column', 'fusion-builder' ),
		'head_title'                                  => esc_html__( 'Head Title', 'fusion-builder' ),
		'currency'                                    => esc_html__( 'Currency', 'fusion-builder' ),
		'price'                                       => esc_html__( 'Price', 'fusion-builder' ),
		'period'                                      => esc_html__( 'Period', 'fusion-builder' ),
		'enter_text'                                  => esc_html__( 'Enter Text', 'fusion-builder' ),
		'added'                                       => esc_html__( 'Added', 'fusion-builder' ),
		'added_section'                               => esc_html__( 'Added Container', 'fusion-builder' ),
		'cloned_nested_columns'                       => esc_html__( 'Cloned Nested Columns', 'fusion-builder' ),
		'content_imported'                            => esc_html__( 'Content Imported', 'fusion-builder' ),
		'table_intro'                                 => esc_html__( 'Visually create your table below, add or remove rows and columns', 'fusion-builder' ),
		'add_table_column'                            => esc_html__( 'Add Column', 'fusion-builder' ),
		'add_table_row'                               => esc_html__( 'Add Row', 'fusion-builder' ),
		'column_title'                                => esc_html__( 'Column', 'fusion-builder' ),
		'standout_design'                             => esc_html__( 'Standout', 'fusion-builder' ),
		'add_button'                                  => esc_html__( 'Add Button', 'fusion-builder' ),
		'yes'                                         => esc_html__( 'Yes', 'fusion-builder' ),
		'no'                                          => esc_html__( 'No', 'fusion-builder' ),
		'table_options'                               => esc_html__( 'Table Options', 'fusion-builder' ),
		'table'                                       => esc_html__( 'Table', 'fusion-builder' ),
		'toggle_all_sections'                         => esc_html__( 'Toggle All Containers', 'fusion-builder' ),
		'cloned_section'                              => esc_html__( 'Cloned Container', 'fusion-builder' ),
		'deleted_section'                             => esc_html__( 'Deleted Container', 'fusion-builder' ),
		'image'                                       => esc_html__( 'Image', 'fusion-builder' ),
		'audio'                                       => esc_html__( 'Audio', 'fusion-builder' ),
		'select_image'                                => esc_html__( 'Select Image', 'fusion-builder' ),
		'select_audio'                                => esc_html__( 'Select Image', 'fusion-builder' ),
		'select_images'                               => esc_html__( 'Select Images', 'fusion-builder' ),
		'select_video'                                => esc_html__( 'Select Video', 'fusion-builder' ),
		'select_audio'                                => esc_html__( 'Select Audio', 'fusion-builder' ),
		'select_icon'                                 => esc_html__( 'Select Icon', 'fusion-builder' ),
		'search_icons'                                => esc_html__( 'Search Icons', 'fusion-builder' ),
		'empty_section'                               => esc_html__( 'To Add Elements, You Must First Add a Column', 'fusion-builder' ),
		'empty_section_with_bg'                       => esc_html__( 'This is an empty container with a background image. To add elements, you must first add a column', 'fusion-builder' ),
		'required'                                    => esc_html__( 'Required', 'fusion-builder' ),
		'add_new_option'                              => esc_html__( 'Add New Option', 'fusion-builder' ),
		'add_new_logic'                               => esc_html__( 'Add New Logic', 'fusion-builder' ),
		'no_logic_field'                              => esc_html__( 'There are no fields available for conditional logic.', 'fusion-builder' ),
		'condition_value'                             => esc_html__( 'Condition Value', 'fusion-builder' ),
		'logic_separator_text'                        => esc_html__( 'OR', 'fusion-builder' ),
		'option_label'                                => esc_html__( 'Label', 'fusion-builder' ),
		'option_field'                                => esc_html__( 'Field', 'fusion-builder' ),
		'select_field'                                => esc_html__( 'Select Field', 'fusion-builder' ),
		'option_value'                                => esc_html__( 'Value', 'fusion-builder' ),
		'bulk_add_button'                             => esc_html__( 'Bulk / Predefined', 'fusion-builder' ),
		'option_show_values'                          => esc_html__( 'Show values', 'fusion-builder' ),
		'bulk_add_instructions'                       => esc_html__( 'Select a predefined list or paste your own list bulk add choices.', 'fusion-builder' ),
		'bulk_add'                                    => esc_html__( 'Bulk Add', 'fusion-builder' ),
		'bulk_add_predefined'                         => esc_html__( 'Predefined Choices', 'fusion-builder' ),
		'bulk_add_insert_choices'                     => esc_html__( 'Insert Choices', 'fusion-builder' ),
		/* translators: Child element name. */
		'empty_parent'                                => esc_html__( 'Empty %s element, please add child elements here.', 'fusion-builder' ),
		'post_views_counter_disabled'                 => esc_html__( 'The views counter option is disabled. Enable it in the global options to use this element.', 'fusion-builder' ),
		'to_add_images'                               => esc_html__( 'To add images to this post or page for attachments layout, navigate to "Upload Files" tab in media manager and upload new images.', 'fusion-builder' ),
		'importing_single_page'                       => esc_html__( 'WARNING: Importing a single prebuilt page will remove all other page content, Avada Page Options and page template. Avada Global Options and images are not imported. Click OK to continue or cancel to stop.', 'fusion-builder' ),
		'content_error_title'                         => esc_html__( 'Content Error', 'fusion-builder' ),
		/* translators: Link URL. */
		'content_error_description'                   => sprintf( __( 'Your page content could not be displayed as an Avada Builder layout. Most likely that means, there is some invalid markup or shortcode in it. Please check the contents in the text editor. <a href="%s" target="_blank">See here for more information</a>.', 'fusion-builder' ), 'https://theme-fusion.com/documentation/avada/how-to/how-to-fix-page-content-not-parsable-avada-builder/' ),
		'unknown_error_title'                         => esc_html__( 'Unknown Error Occurred', 'fusion-builder' ),
		/* translators: Link URL. */
		'unknown_error_link'                          => sprintf( __( '<a href="%s" target="_blank">Click here to learn more.</a>', 'fusion-builder' ), '#' ),
		'unknown_error_copy'                          => esc_html__( 'Click here to copy the full error message.', 'fusion-builder' ),
		'unknown_error_copied'                        => esc_html__( 'Full error message copied.', 'fusion-builder' ),
		'moved_container'                             => esc_html__( 'Moved Container', 'fusion-builder' ),
		'currency_before'                             => esc_html__( 'Before', 'fusion-builder' ),
		'currency_after'                              => esc_html__( 'After', 'fusion-builder' ),
		'delete_nextpage'                             => esc_html__( 'Delete Next Page Divider', 'fusion-builder' ),
		'toggle_element'                              => esc_html__( 'Toggle Element', 'fusion-builder' ),
		'drag_element'                                => esc_html__( 'Drag Element', 'fusion-builder' ),
		'deleted_nextpage'                            => esc_html__( 'Deleted Next Page Divider', 'fusion-builder' ),
		'deleted_checkout_form'                       => esc_html__( 'Deleted Checkout Form Divider', 'fusion-builder' ),
		'added_nextpage'                              => esc_html__( 'Added Next Page Divider', 'fusion-builder' ),
		'nextpage'                                    => esc_html__( 'Next Page', 'fusion-builder' ),
		'checkout_form'                               => esc_html__( 'Checkout Form', 'fusion-builder' ),
		'library_misc'                                => esc_html__( 'Special', 'fusion-builder' ),
		'special_title'                               => esc_html__( 'Special Items', 'fusion-builder' ),
		'next_page_description'                       => esc_html__( 'The next page item allows you to break your page into several pages. Simply insert it onto the page, and automatic pagination will show on the frontend.', 'fusion-builder' ),
		'checkout_form_description'                   => esc_html__( 'The checkout form items allows you to add opening and closing WooCommerce checkout form tags.', 'fusion-builder' ),
		'select_link'                                 => esc_html__( 'Select Link', 'fusion-builder' ),
		'color_palette_options'                       => esc_html__( 'Color palette options', 'fusion-builder' ),
		'background_color'                            => esc_html__( 'Background Color', 'fusion-builder' ),
		'border_color'                                => esc_html__( 'Border Color', 'fusion-builder' ),
		'legend_text_color'                           => esc_html__( 'Legend Value Text Color', 'fusion-builder' ),
		'enter_value'                                 => esc_html__( 'Enter Value', 'fusion-builder' ),
		'legend_label'                                => esc_html__( 'Legend Label', 'fusion-builder' ),
		'x_axis_label'                                => esc_html__( 'X Axis Label', 'fusion-builder' ),
		/* translators: Toggle column width option. */
		'toggle_column_custom_width'                  => esc_html__( 'Use Custom Width', 'fusion-builder' ),
		'toggle_column_default_widths'                => esc_html__( 'Use Preset Sizes', 'fusion-builder' ),
		/* translators: Search type. */
		'search_placeholder'                          => esc_html__( 'Search %s', 'fusion-builder' ),
		'chart_bg_color_title'                        => esc_html__( 'Chart Background Color', 'fusion-builder' ),
		/* translators: Default value description & value. */
		'chart_bg_color_desc'                         => sprintf( __( 'Controls the background of the chart. %s', 'fusion-builder' ), $fusion_settings->get_default_description( 'chart_bg_color', '', 'color-alpha', true, '' ) ),
		'chart_axis_text_color_title'                 => esc_html__( 'Chart Axis Text Color', 'fusion-builder' ),
		/* translators: Default value description & value. */
		'chart_axis_text_color_desc'                  => sprintf( __( 'Controls the text color of the x-axis and y-axis. %s', 'fusion-builder' ), $fusion_settings->get_default_description( 'chart_axis_text_color', '', 'color-alpha', true, '' ) ),
		'chart_gridline_color_title'                  => esc_html__( 'Chart Gridline Color', 'fusion-builder' ),
		/* translators: Default value description & value. */
		'chart_gridline_color_desc'                   => sprintf( __( 'Controls the color of the chart background grid lines and values. %s', 'fusion-builder' ), $fusion_settings->get_default_description( 'chart_gridline_color', '', 'color-alpha', true, '' ) ),
		'chart_padding_title'                         => esc_html__( 'Chart Padding Options', 'fusion-builder' ),
		'chart_padding_desc'                          => esc_html__( 'Controls the top/right/bottom/left padding of the chart.', 'fusion-builder' ),
		'chart_options'                               => esc_html__( 'Chart Options', 'fusion-builder' ),
		'chart'                                       => esc_html__( 'Chart Data', 'fusion-builder' ),
		'chart_border_size_heading'                   => esc_html__( 'Border Size', 'fusion-builder' ),
		'chart_border_size_desc '                     => esc_html__( 'Set chart border size in pixels.', 'fusion-builder' ),
		'chart_intro'                                 => esc_html__( 'Visually create your chart data below, add or remove data sets and their styling', 'fusion-builder' ),
		'chart_intro_fe'                              => esc_html__( 'Visually create your chart data below', 'fusion-builder' ),
		'chart_bars_note'                             => __( '<strong>IMPORTANT NOTE:</strong> If you are using a <strong>Bar</strong> or <strong>Horizontal Bar Chart</strong>, the table interface below and available options will change depending on the number of datasets added. This setup is needed in order to ensure maximum flexibility for your chart styling.', 'fusion-builder' ),
		'add_chart_column'                            => esc_html__( '+ Add Value Column', 'fusion-builder' ),
		'add_chart_row'                               => esc_html__( '+ Add Data Set', 'fusion-builder' ),
		'chart_dataset'                               => esc_html__( 'Data Set', 'fusion-builder' ),
		'chart_dataset_styling'                       => esc_html__( 'Data Set Styling', 'fusion-builder' ),
		'chart_value_set_styling'                     => esc_html__( 'Value Set Styling', 'fusion-builder' ),
		'chart_table_button_desc'                     => esc_html__( 'Build your chart data visually', 'fusion-builder' ),
		'chart_table_button_text'                     => esc_html__( 'Edit Chart Data Table', 'fusion-builder' ),
		'user_login_register_note'                    => esc_html__( 'Registration confirmation will be emailed to you.', 'fusion-builder' ),
		'are_you_sure_you_want_to_remove_global'      => esc_html__( 'Are you sure you want to remove global property?', 'fusion-builder' ),
		'remove_global'                               => esc_html__( 'Remove Global?', 'fusion-builder' ),
		'removed_global'                              => esc_html__( 'Removed Global Status', 'fusion-builder' ),
		'container_draft'                             => esc_html__( 'Draft container.', 'fusion-builder' ),
		'container_scheduled'                         => esc_html__( 'Scheduled container.', 'fusion-builder' ),
		'container_publish'                           => esc_html__( 'Click to remove sheduling.', 'fusion-builder' ),
		'are_you_sure_you_want_to_publish'            => esc_html__( 'Are you sure you want to remove sheduling? This will set the container to be a normally published element.', 'fusion-builder' ),
		'container_published'                         => esc_html__( 'Container published.', 'fusion-builder' ),
		'on'                                          => esc_html__( 'On', 'fusion-builder' ),
		'off'                                         => esc_html__( 'Off', 'fusion-builder' ),
		'get_started_video'                           => esc_html__( 'Watch Our Get Started Video', 'fusion-builder' ),
		'get_started_video_description'               => esc_html__( 'Do you need a helping hand? Let us guide you.', 'fusion-builder' ),
		'watch_the_video_link'                        => esc_html__( 'Watch The Video', 'fusion-builder' ),
		'fusion_builder_docs'                         => esc_html__( 'Avada Builder Docs', 'fusion-builder' ),
		'fusion_builder_docs_description'             => esc_html__( 'Videos not for you? That\'s ok! We have you covered.', 'fusion-builder' ),
		'fusion_panel_desciption_toggle'              => esc_html__( 'Toggle Description', 'fusion-builder' ),
		'fusion_panel_responsive_toggle'              => esc_html__( 'Toggle Responsive Options', 'fusion-builder' ),
		'fusion_dimension_top_label'                  => esc_html__( 'Top', 'fusion-builder' ),
		'fusion_dimension_bottom_label'               => esc_html__( 'Bottom', 'fusion-builder' ),
		'fusion_dimension_left_label'                 => esc_html__( 'Left', 'fusion-builder' ),
		'fusion_dimension_right_label'                => esc_html__( 'Right', 'fusion-builder' ),
		'fusion_dimension_height_label'               => esc_html__( 'Height', 'fusion-builder' ),
		'fusion_dimension_width_label'                => esc_html__( 'Width', 'fusion-builder' ),
		'fusion_dimension_top_left_label'             => esc_html__( 'Top/Left', 'fusion-builder' ),
		'fusion_dimension_top_right_label'            => esc_html__( 'Top/Right', 'fusion-builder' ),
		'fusion_dimension_bottom_left_label'          => esc_html__( 'Bot/Left', 'fusion-builder' ),
		'fusion_dimension_bottom_right_label'         => esc_html__( 'Bot/Right', 'fusion-builder' ),
		'fusion_dimension_all_label'                  => esc_html__( 'All', 'fusion-builder' ),
		'fusion_dimension_horizontal'                 => esc_html__( 'Horizontal', 'fusion-builder' ),
		'fusion_dimension_vertical'                   => esc_html__( 'Vertical', 'fusion-builder' ),
		'confirm'                                     => esc_html__( 'Confirm', 'fusion-builder' ),
		'unsaved_changes'                             => esc_html__( 'Unsaved Changes', 'fusion-builder' ),
		'changes_will_be_lost'                        => esc_html__( 'Your changes will be lost, do you want to save changes before leaving?', 'fusion-builder' ),
		'reset'                                       => esc_html__( 'Reset', 'fusion-builder' ),
		'reset_element_options'                       => esc_html__( 'Reset to Defaults', 'fusion-builder' ),
		'reset_element_options_confirmation'          => esc_html__( 'Are you sure you want to reset this element\'s options to default?', 'fusion-builder' ),
		'remove_element_options_confirmation'         => esc_html__( 'Are you sure you want to delete this element?', 'fusion-builder' ),
		'i_agree'                                     => esc_html__( 'I Agree', 'fusion-builder' ),
		'are_you_sure'                                => esc_html__( 'Are You Sure?', 'fusion-builder' ),
		'im_sure'                                     => esc_html__( 'I\'m sure', 'fusion-builder' ),
		'ok'                                          => esc_html__( 'Ok', 'fusion-builder' ),
		'import_demo_page'                            => esc_html__( 'Import Prebuilt Website Page', 'fusion-builder' ),
		'import_demo_layout'                          => esc_html__( 'Import Prebuilt Website Layout', 'fusion-builder' ),
		'extended_options'                            => esc_html__( 'Extended options', 'fusion-builder' ),
		'align_text'                                  => esc_html__( 'Align text', 'fusion-builder' ),
		'align_left'                                  => esc_html__( 'Align left', 'fusion-builder' ),
		'align_center'                                => esc_html__( 'Align center', 'fusion-builder' ),
		'align_right'                                 => esc_html__( 'Align right', 'fusion-builder' ),
		'align_justify'                               => esc_html__( 'Justify', 'fusion-builder' ),
		'indent'                                      => esc_html__( 'Indent', 'fusion-builder' ),
		'outdent'                                     => esc_html__( 'Outdent', 'fusion-builder' ),
		'accept'                                      => esc_html__( 'Accept', 'fusion-builder' ),
		'typography'                                  => esc_html__( 'Typography', 'fusion-builder' ),
		'typography_settings'                         => esc_html__( 'Settings', 'fusion-builder' ),
		'typography_family'                           => esc_html__( 'Family', 'fusion-builder' ),
		'typography_tag'                              => esc_html__( 'Tag', 'fusion-builder' ),
		'typography_fontsize'                         => esc_html__( 'Font Size', 'fusion-builder' ),
		'typography_lineheight'                       => esc_html__( 'Line Height', 'fusion-builder' ),
		'typography_letterspacing'                    => esc_html__( 'Letter Spacing', 'fusion-builder' ),
		'typography_variant'                          => esc_html__( 'Variant', 'fusion-builder' ),
		'typography_default'                          => esc_html__( 'Default', 'fusion-builder' ),
		'font_color'                                  => esc_html__( 'Font color', 'fusion-builder' ),
		'inline_element_edit'                         => esc_html__( 'Edit Inline Element', 'fusion-builder' ),
		'inline_element_remove'                       => esc_html__( 'Remove Inline Element', 'fusion-builder' ),
		'inline_element_delete'                       => esc_html__( 'Delete All', 'fusion-builder' ),
		'delete'                                      => esc_html__( 'Delete', 'fusion-builder' ),
		'preview_mode'                                => esc_html__( 'Preview Mode', 'fusion-builder' ),
		'preview_mode_notice'                         => esc_html__( 'Please beware that editing options are limited on this mode. All open dialogs and panels will be closed.', 'fusion-builder' ),
		'multi_dialogs'                               => esc_html__( 'Multiple Dialogs', 'fusion-builder' ),
		'multi_dialogs_notice'                        => esc_html__( 'Please close other open dialogs first to use this option.', 'fusion-builder' ),
		'widget'                                      => esc_html__( 'Widget', 'fusion-builder' ),
		'select_widget'                               => esc_html__( 'No widget selected. Edit to select widget type.', 'fusion-builder' ),

		/* translators: Add unknown element type. */
		'add_unknown'                                 => esc_html__( 'Add %s', 'fusion-builder' ),
		'link_options'                                => esc_html__( 'Link Options', 'fusion-builder' ),
		'open_in_new_tab'                             => esc_html__( 'Open Link in New Tab', 'fusion-builder' ),
		'clear'                                       => esc_html__( 'Clear', 'fusion-builder' ),
		'remove_format'                               => esc_html__( 'Remove formatting', 'fusion-builder' ),
		'gallery_placeholder'                         => esc_html__( 'Please add a gallery image here.', 'fusion-builder' ),
		'slider_placeholder'                          => esc_html__( 'Please select a slider for it to display here.', 'fusion-builder' ),
		'form_placeholder'                            => esc_html__( 'Please select a form for it to display here.', 'fusion-builder' ),
		'video_placeholder'                           => esc_html__( 'Please add a video here.', 'fusion-builder' ),
		'search_results'                              => esc_html__( 'Search Results', 'fusion-builder' ),
		'problem_saving'                              => esc_html__( 'Page Save Incomplete', 'fusion-builder' ),
		'changes_not_saved'                           => esc_html__( 'Not all content has saved correctly. Click ok to return to the page editor and try again.', 'fusion-builder' ),
		'page_save_failed'                            => esc_html__( 'Page Save Failed', 'fusion-builder' ),
		'authentication_no_heartbeat'                 => esc_html__( 'Security nonce check failed.  Attempts to reconnect were also unsuccessful.  Please ensure WordPress Heartbeat is not disabled.', 'fusion-builder' ),
		'layout_cleared'                              => esc_html__( 'Layout cleared.', 'fusion-builder' ),
		'remove'                                      => esc_html__( 'Remove', 'fusion-builder' ),
		'enter_value'                                 => esc_attr__( 'Enter value', 'fusion-builder' ),
		'import_failed'                               => esc_attr__( 'Import Failed', 'fusion-builder' ),
		'import_failed_description'                   => esc_attr__( 'Please check that the file selected is valid JSON. Click ok to return to the page editor and try again.', 'fusion-builder' ),
		'saved'                                       => esc_attr__( 'Saved', 'fusion-builder' ),
		'as_global'                                   => esc_attr__( 'As global', 'fusion-builder' ),
		'front_end_redirect_confirm'                  => esc_html__( 'This action will redirect you to the front-end builder. All unsaved changes will be lost. Are you sure?', 'fusion-builder' ),
		'duplicate_slider_revolution'                 => esc_html__( 'Duplicate Slider Revolution detected.  Duplicate sliders will not be rendered whilst in the live editor.', 'fusion-builder' ),
		/* Translators: List of tags. */
		'tags'                                        => esc_html__( 'Tags: %s', 'fusion-builder' ),
		'other'                                       => esc_html__( 'Other', 'fusion-builder' ),
		'dynamic_data'                                => esc_html__( 'Dynamic Data', 'fusion-builder' ),
		'select_dynamic_content'                      => esc_html__( 'Select Dynamic Content', 'fusion-builder' ),
		'custom_fonts'                                => esc_html__( 'Custom Font(s)', 'fusion-builder' ),
		'add_new'                                     => esc_html__( 'Add New', 'fusion-builder' ),
		'add'                                         => esc_html__( 'Add', 'fusion-builder' ),
		'separate_with_comma'                         => esc_html__( 'Separate with Commas', 'fusion-builder' ),
		'previous'                                    => esc_html__( 'Previous', 'fusion-builder' ),
		'next'                                        => esc_html__( 'Next', 'fusion-builder' ),
		'post_title'                                  => esc_html__( 'Post Title', 'fusion-builder' ),
		'related_posts'                               => esc_html__( 'Related Posts', 'fusion-builder' ),
		'related_projects'                            => esc_html__( 'Related Projects', 'fusion-builder' ),
		'related_faqs'                                => esc_html__( 'Related Faqs', 'fusion-builder' ),
		'related_products'                            => apply_filters( 'woocommerce_product_related_products_heading', __( 'Related products', 'fusion-builder' ) ),
		'upsells_products'                            => apply_filters( 'woocommerce_product_upsells_products_heading', __( 'You may also like&hellip;', 'fusion-builder' ) ),
		'cross_sells_products'                        => apply_filters( 'woocommerce_product_cross_sells_products_heading', __( 'You may be interested in&hellip;', 'fusion-builder' ) ),
		'project_details'                             => esc_html__( 'Project Details', 'fusion-builder' ),
		'add_custom_icon_set'                         => esc_html__( 'Add Custom Icon Set', 'fusion-builder' ),
		'edit_layout_section'                         => esc_html__( 'Edit Layout Section', 'fusion-builder' ),
		'edit_content_layout_section'                 => esc_html__( 'Edit Content Layout Section', 'fusion-builder' ),
		'edit_footer_layout_section'                  => esc_html__( 'Edit Footer Layout Section', 'fusion-builder' ),
		'edit_header_layout_section'                  => esc_html__( 'Edit Header Layout Section', 'fusion-builder' ),
		'dynamic_source'                              => esc_html__( 'Set dynamic content to update preview source.', 'fusion-builder' ),
		'builder_ibrary_title'                        => esc_html__( 'Avada Builder Library', 'fusion-builder' ),
		/* translators: The iconset name. */
		'no_results_in'                               => esc_html__( 'No Results in "%s"', 'fusion-builder' ),
		'main_menu'                                   => esc_html__( 'Main Menu', 'fusion-builder' ),
		'background'                                  => esc_html__( 'Background', 'fusion-builder' ),
		'logo'                                        => esc_html__( 'Logo', 'fusion-builder' ),
		'phone_pattern_text'                          => esc_html__( 'Only numbers and phone characters are accepted', 'fusion-builder' ),
		'all'                                         => esc_html__( 'All', 'fusion-builder' ),
		'studio_importing_content'                    => esc_html__( 'Importing Studio Content', 'fusion-builder' ),
		'studio_importing_content_failed'             => esc_html__( 'Importing Studio Content Failed', 'fusion-builder' ),
		'studio_importing_media'                      => esc_html__( 'Importing Studio Media:', 'fusion-builder' ),
		'api_error_text'                              => esc_html__( 'Failed to retrieve data from API', 'fusion-builder' ),
		'demo_importing_content'                      => esc_html__( 'Importing Prebuilt Page Content', 'fusion-builder' ),
		'demo_importing_content_failed'               => esc_html__( 'Importing Prebuilt Page Content Failed', 'fusion-builder' ),
		'demo_importing_media'                        => esc_html__( 'Importing Prebuilt Page Media:', 'fusion-builder' ),
		'setup_plugin_error_title'                    => esc_html__( 'Plugin Installation Failed', 'fusion-builder' ),
		'setup_prebuilt_error_title'                  => esc_html__( 'Importing Prebuilt Website Failed', 'fusion-builder' ),
		'setup_general_error_title'                   => esc_html__( 'Something Went Wrong', 'fusion-builder' ),
		'setup_general_error_message'                 => esc_html__( 'An error occured and the process could not be completed. Please check your error log for details.', 'fusion-builder' ),
		'content'                                     => esc_html__( 'Content', 'fusion-builder' ),
		'gallery_loading_message'                     => esc_html__( 'Loading the next set of gallery items...', 'fusion-builder' ),
	];

	return $text_strings;
}

/**
 * Get current location.
 *
 * @since 1.0
 */
function fusion_app_get_current_page() {

	if ( is_date() ) {
		return 'date';

	} elseif ( is_author() ) {
		return 'author';

	} elseif ( is_tax() ) {
		return 'taxonomy';

	} elseif ( is_archive() && ! ( class_exists( 'WooCommerce' ) && is_shop() ) ) {
		return 'archive';

	} elseif ( is_search() ) {
		return 'search';

	} elseif ( is_404() ) {
		return '404';

	} elseif ( is_attachment() ) {
		return 'attachment';

	} elseif ( is_single() ) {
		return 'single';

	} elseif ( is_page() || get_option( 'page_for_posts' ) === fusion_library()->get_page_id() || ( class_exists( 'WooCommerce' ) && is_shop() ) ) {
		return 'page';
	}

	return 'index';
}

/**
 * Get current location.
 *
 * @since 1.0.3
 */
function fusion_set_live_data() {
	if ( class_exists( 'Fusion_App' ) ) {
		Fusion_App()->set_data();
		do_action( 'fusion_filter_data' );
	}
}

/**
 * Renders the title.
 *
 * @param string|int $size            The heading size.
 * @param string     $heading_content Section title.
 * @return strint HTML content.
 */
function fusion_render_title( $size, $heading_content ) {
	$fusion_settings = awb_get_fusion_settings();

	// Set vars.
	$content_align = is_rtl() ? 'right' : 'left';
	$size_array    = [
		'1' => 'one',
		'2' => 'two',
		'3' => 'three',
		'4' => 'four',
		'5' => 'five',
		'6' => 'six',
	];

	$margin_top            = $fusion_settings->get( 'title_margin', 'top' );
	$margin_bottom         = $fusion_settings->get( 'title_margin', 'bottom' );
	$sep_color             = $fusion_settings->get( 'title_border_color' );
	$style_type            = $fusion_settings->get( 'title_style_type' );
	$responsive_typography = $fusion_settings->get( 'typography_sensitivity' );

	$underline_or_none = false !== strpos( $style_type, 'underline' ) || false !== strpos( $style_type, 'none' );

	// Render title.
	$classes        = '';
	$styles         = '';
	$heading_styles = '';

	$classes_array = explode( ' ', $style_type );
	foreach ( $classes_array as $class ) {
		$classes .= ' sep-' . $class;
	}

	$wrapper_classes = ' fusion-title fusion-title-size-' . $size_array[ $size ] . $classes;

	if ( $margin_top ) {
		$styles .= sprintf( 'margin-top:%s;', Fusion_Sanitize::get_value_with_unit( $margin_top ) );
	}
	if ( $margin_bottom ) {
		$styles .= sprintf( 'margin-bottom:%s;', Fusion_Sanitize::get_value_with_unit( $margin_bottom ) );
	}

	if ( '' !== $margin_top || '' !== $margin_bottom ) {
		$heading_styles .= 'margin:0;';
	}

	if ( false !== $underline_or_none ) {

		if ( false !== strpos( $style_type, 'underline' ) && $sep_color ) {
			$styles .= 'border-bottom-color:' . $sep_color;
		} elseif ( false !== strpos( $style_type, 'none' ) ) {
			$classes .= ' fusion-sep-none';
		}
	}

	$output = '';

	$output .= '<div class="' . esc_attr( $wrapper_classes ) . '" style="' . esc_attr( $styles ) . '">';
	if ( false === $underline_or_none && 'right' === $content_align ) {
		$output .= '<div class="title-sep-container">';
		$output .= '<div class="title-sep' . esc_attr( $classes ) . '"></div>';
		$output .= '</div>';
	}

	$responsive_class = '';
	if ( $responsive_typography ) {
		$font_size        = $fusion_settings->get( 'h' . $size . '_typography', 'font-size' );
		$line_height      = $fusion_settings->get( 'h' . $size . '_typography', 'line-height' );
		$data             = awb_get_responsive_type_data( 'h' . $size, $font_size, $line_height );
		$responsive_class = ' ' . $data['class'];
		$heading_styles  .= $data['font_size'];
		$heading_styles  .= $data['line_height'];
	}

	$output .= '<h' . $size . ' class="title-heading-' . esc_attr( $content_align ) . esc_attr( $responsive_class ) . '" style="' . esc_attr( $heading_styles ) . '">';
	$output .= $heading_content;
	$output .= '</h' . $size . '>';

	if ( false === $underline_or_none && 'left' === $content_align ) {
		$output .= '<div class="title-sep-container">';
		$output .= '<div class="title-sep' . esc_attr( $classes ) . '"></div>';
		$output .= '</div>';
	}
	$output .= '</div>';

	return $output;
}


// If PHP version older than 7.3.0.
if ( ! function_exists( 'array_key_first' ) ) {

	/**
	 * Gets first key of array.
	 *
	 * @access public
	 * @since  3.0.0
	 * @param  array $arr Input array.
	 * @return string
	 */
	function array_key_first( $arr ) {
		foreach ( $arr as $key => $unused ) {
			return $key;
		}
		return '';
	}
}

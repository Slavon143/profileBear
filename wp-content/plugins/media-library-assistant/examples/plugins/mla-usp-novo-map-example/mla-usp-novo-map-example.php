<?php
/**
 * Fills posts created by the "User Submitted Posts" plugin with information for the "Novo Map" plugin
 *
 * Much more information is in the Settings/MLA USP Novo-Map Documentation tab.
 *
 * Created for by support topic "GPS field(s)"
 * opened on 8/7/2018 by "jrpmedia".
 * https://wordpress.org/support/topic/gps-fields/
 *
 * @package MLA USP Novo-Map Example
 * @version 1.03
 */

/*
Plugin Name: MLA USP Novo-Map Example
Plugin URI: http://davidlingren.com/
Description: Fills posts created by the "User Submitted Posts" plugin with information for the "Novo Map" plugin
Author: David Lingren
Version: 1.03
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
 * Class MLA USP Novo-Map Example hooks one of the filters provided by the IPTC/EXIF and Custom Field mapping features
 *
 * Call it anything you want, but give it an unlikely and hopefully unique name. Hiding everything
 * else inside a class means this is the only name you have to worry about.
 *
 * @package MLA USP Novo-Map Example
 * @since 1.00
 */
class MLAUSPNovoMapExample {
	/**
	 * Current version number
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const CURRENT_VERSION = '1.03';

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'mlauspnovomap';

	/**
	 * Initialization function, similar to __construct()
	 *
	 * Installs filters and actions that handle the MLA hooks for uploading and mapping.
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		//error_log( __LINE__ . " MLAUSPNovoMapExample:initialize _REQUEST = " . var_export( $_REQUEST, true ), 0 );

		// The admin_menu action is only useful in the admin section; other filters are for the "front-end" posts/pages. 
		if ( is_admin() ) {
			// Add submenu page in the "Settings" section
			add_action( 'admin_menu', 'MLAUSPNovoMapExample::admin_menu' );
			return;
		}

		// We only care about USP submissions when the Process option is set
		if ( isset( $_REQUEST['usp-nonce'] ) && self::_get_plugin_option('process_usp_posts') ) {
			self::$usp_values['usp-nonce'] = $_REQUEST['usp-nonce'];
		} else {
			return;
		}
		//error_log( __LINE__ . " MLAUSPNovoMapExample:initialize _REQUEST = " . var_export( $_REQUEST, true ), 0 );
		
		// user-submitted-posts.php
		// function usp_checkForPublicSubmission() {
		// do_action('usp_submit_success', $redirect);
		// do_action('usp_submit_error', $redirect);

		// function usp_attach_images($post_id, $newPost, $files, $file_count) {
		// do_action('usp_files_before', $files);
		// $append = apply_filters('usp_filename_append', $append);
		// $upload_dir = apply_filters('usp_upload_directory', wp_upload_dir());
		// $params = apply_filters('wp_handle_upload', array('file' => $file, 'url' => $guid, 'type' => $file_type)); 
		// $attachment = apply_filters('usp_insert_attachment_data', $attachment);
		// do_action('usp_files_after', $attach_ids);
		add_action( 'usp_files_before', 'MLAUSPNovoMapExample::usp_files_before', 10, 1 );
		add_filter( 'usp_insert_attachment_data', 'MLAUSPNovoMapExample::usp_insert_attachment_data', 10, 1 );
		add_action( 'usp_files_after', 'MLAUSPNovoMapExample::usp_files_after', 10, 1 );

		// function usp_prepare_post($title, $content, $author_id, $author, $ip) {
		// $postData['post_status']  = apply_filters('usp_post_status', 'pending');
		// $postData['post_type'] = apply_filters('usp_post_type', $postType);
		// $postData['post_status'] = apply_filters('usp_post_publish', 'publish');
		// $postData['post_status'] = apply_filters('usp_post_moderate', 'pending');
		// $postData['post_status'] = apply_filters('usp_post_draft', 'draft');
		// $posts = get_posts(array('post_status' => 'publish', 'meta_key' => 'user_submit_name', 'meta_value' => $author));
		// if ($counter >= $numberApproved) $postData['post_status'] = apply_filters('usp_post_approve', 'publish
		// return apply_filters('usp_post_data', $postData);
		add_filter( 'usp_post_data', 'MLAUSPNovoMapExample::usp_post_data', 10, 1 );

		// function usp_createPublicSubmission($title, $files, $ip, $author, $url, $email, $tags, $captcha, $verify, $content, $category, $custom, $checkbox) {
		// do_action('usp_insert_before', $postData);
		// do_action('usp_insert_after', $newPost);
		// return apply_filters('usp_new_post', $newPost);
		add_action( 'usp_insert_before', 'MLAUSPNovoMapExample::usp_insert_before', 10, 1 );
		add_action( 'usp_insert_after', 'MLAUSPNovoMapExample::usp_insert_after', 10, 1 );
		add_filter( 'usp_new_post', 'MLAUSPNovoMapExample::usp_new_post', 10, 1 );

		// Supplies substitution parameter values for the "usp:" prefix
		add_filter( 'mla_expand_custom_prefix', 'MLAUSPNovoMapExample::mla_expand_custom_prefix', 10, 8 );

	}

	/**
	 * Add submenu page in the "Settings" section
	 *
	 * @since 1.00
	 */
	public static function admin_menu( ) {
		/*
		 * We need a tab-specific page ID to manage the screen options on the General tab.
		 * Use the URL suffix, if present. If the URL doesn't have a tab suffix, use '-general'.
		 * This hack is required to pass the WordPress "referer" validation.
		 */
		 if ( isset( $_REQUEST['page'] ) && is_string( $_REQUEST['page'] ) && ( self::SLUG_PREFIX . '-settings-' == substr( $_REQUEST['page'], 0, strlen( self::SLUG_PREFIX . '-settings-' ) ) ) ) {
			$tab = substr( $_REQUEST['page'], strlen( self::SLUG_PREFIX . '-settings-' ) );
		 } else {
			$tab = 'general';
		 }

		$tab = self::_get_options_tablist( $tab ) ? '-' . $tab : '-general';
		add_submenu_page( 'options-general.php', 'MLA USP Novo-Map Example', 'MLA USP Novo-Map', 'manage_options', self::SLUG_PREFIX . '-settings' . $tab, 'MLAUSPNovoMapExample::add_submenu_page' );
		add_filter( 'plugin_action_links', 'MLAUSPNovoMapExample::plugin_action_links', 10, 2 );
	}

	/**
	 * Add the "Settings" and "Guide" links to the Plugins section entry
	 *
	 * @since 1.00
	 *
	 * @param	array 	array of links for the Plugin, e.g., "Activate"
	 * @param	string 	Directory and name of the plugin Index file
	 *
	 * @return	array	Updated array of links for the Plugin
	 */
	public static function plugin_action_links( $links, $file ) {
		if ( $file == 'mla-usp-novo-map-example/mla-usp-novo-map-example.php' ) {
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . self::SLUG_PREFIX . '-settings-documentation&mla_tab=documentation' ), 'Guide' );
			array_unshift( $links, $settings_link );
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . self::SLUG_PREFIX . '-settings-general' ), 'Settings' );
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	/**
	 * Render (echo) the "MLA USP Novo-Map" submenu in the Settings section
	 *
	 * @since 1.00
	 *
	 * @return	void Echoes HTML markup for the submenu page
	 */
	public static function add_submenu_page() {
		//error_log( __LINE__ . " MLAUSPNovoMapExample:add_submenu_page _REQUEST = " . var_export( $_REQUEST, true ), 0 );

		if ( !current_user_can( 'manage_options' ) ) {
			echo "<h2>MLA USP Novo-Map Example - Error</h2>\n";
			wp_die( 'You do not have permission to manage plugin settings.' );
		}

		// Load template array and initialize page-level values.
		self::$page_template_array = MLACore::mla_load_template( dirname( __FILE__ ) . '/admin-settings-page.tpl', 'path' );
		$current_tab_slug = isset( $_REQUEST['mla_tab'] ) ? $_REQUEST['mla_tab']: 'general';
		$current_tab = self::_get_options_tablist( $current_tab_slug );
		$page_values = array(
			'version' => 'v' . self::CURRENT_VERSION,
			'messages' => '',
			'tablist' => self::_compose_settings_tabs( $current_tab_slug ),
			'tab_content' => '',
		);

		// Compose tab content
		if ( $current_tab ) {
			if ( isset( $current_tab['render'] ) ) {
				$handler = $current_tab['render'];
				$page_content = call_user_func( $handler );
			} else {
				$page_content = array( 'message' => 'ERROR: Cannot render content tab', 'body' => '' );
			}
		} else {
			$page_content = array( 'message' => 'ERROR: Unknown content tab', 'body' => '' );
		}

		if ( ! empty( $page_content['message'] ) ) {
			if ( false !== strpos( $page_content['message'], 'ERROR' ) ) {
				$messages_class = 'updated error';
				$dismiss_button = '';
			} else {
				$messages_class = 'updated notice is-dismissible';
				$dismiss_button = "  <button class=\"notice-dismiss\" type=\"button\"><span class=\"screen-reader-text\">[+dismiss_text+].</span></button>\n";
			}

			$page_values['messages'] = MLAData::mla_parse_template( self::$page_template_array['messages'], array(
				 'mla_messages_class' => $messages_class ,
				 'messages' => $page_content['message'],
				 'dismiss_button' => $dismiss_button,
				 'dismiss_text' => 'Dismiss this notice',
			) );
		}

		$page_values['tab_content'] = $page_content['body'];
		echo MLAData::mla_parse_template( self::$page_template_array['page'], $page_values );
	}

	/**
	 * Template file for the Settings page(s) and parts
	 *
	 * This array contains all of the template parts for the Settings page(s). The array is built once
	 * each page load and cached for subsequent use.
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	public static $page_template_array = NULL;

	/**
	 * Definitions for Settings page tab ids, titles and handlers
	 * Each tab is defined by an array with the following elements:
	 *
	 * array key => HTML id/name attribute and option database key (OMIT MLA_OPTION_PREFIX)
	 *
	 * title => tab label / heading text
	 * render => rendering function for tab messages and content. Usage:
	 *     $tab_content = ['render']( );
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $mla_tablist = array(
		'general' => array( 'title' => 'General', 'render' => array( 'MLAUSPNovoMapExample', '_compose_general_tab' ) ),
		'documentation' => array( 'title' => 'Documentation', 'render' => array( 'MLAUSPNovoMapExample', '_compose_documentation_tab' ) ),
		);

	/**
	 * Retrieve the list of options tabs or a specific tab value
	 *
	 * @since 1.00
	 *
	 * @param	string	Tab slug, to retrieve a single entry
	 *
	 * @return	array|false	The entire tablist ( $tab = NULL ), a single tab entry or false if not found/not allowed
	 */
	private static function _get_options_tablist( $tab = NULL ) {
		if ( is_string( $tab ) ) {
			if ( isset( self::$mla_tablist[ $tab ] ) ) {
				$results = self::$mla_tablist[ $tab ];
			} else {
				$results = false;
			}
		} else {
			$results = self::$mla_tablist;
		}

		return $results;
	}

	/**
	 * Compose the navigation tabs for the Settings subpage
	 *
	 * @since 1.00
	 * @uses $page_template_array contains tablist and tablist-item templates
 	 *
	 * @param	string	Optional data-tab-id value for the active tab, default 'general'
	 *
	 * @return	string	HTML markup for the Settings subpage navigation tabs
	 */
	private static function _compose_settings_tabs( $active_tab = 'general' ) {
		$tablist_item = self::$page_template_array['tablist-item'];
		$tabs = '';
		foreach ( self::_get_options_tablist() as $key => $item ) {
			$item_values = array(
				'data-tab-id' => $key,
				'nav-tab-active' => ( $active_tab == $key ) ? 'nav-tab-active' : '',
				'settings-page' => self::SLUG_PREFIX . '-settings-' . $key,
				'title' => $item['title']
			);

			$tabs .= MLAData::mla_parse_template( $tablist_item, $item_values );
		} // foreach $item

		$tablist_values = array( 'tablist' => $tabs );
		return MLAData::mla_parse_template( self::$page_template_array['tablist'], $tablist_values );
	}

	/**
	 * Compose the General tab content for the Settings subpage
	 *
	 * @since 1.00
	 * @uses $page_template_array contains tab content template(s)
 	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private static function _compose_general_tab( ) {
		$page_content = array( 'message' => '', 'body' => '' );

		// Initialize page messages and content, check for page-level Save Changes
		if ( !empty( $_REQUEST['mla-path-mapping-options-save'] ) ) {
			check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );
			$page_content = self::_save_setting_changes( );
		}

		if ( !empty( $page_content['body'] ) ) {
			return $page_content;
		}

		// Display the General tab
		$_SERVER['REQUEST_URI'] = remove_query_arg( array(
			'mla_usp_novo_map_options',
			'_wpnonce',
			'_wp_http_referer',
			'mla-path-mapping-options-save',
		), $_SERVER['REQUEST_URI'] );

		// Compose page-level options
		$usp_category_slugs = self::_get_plugin_option('usp_category_slugs');
		if ( empty( $usp_category_slugs ) ) {
			$usp_category_slugs = self::$_default_settings['usp_category_slugs'];
		}

		$usp_tag_slugs = self::_get_plugin_option('usp_tag_slugs');
		if ( empty( $usp_tag_slugs ) ) {
			$usp_tag_slugs = self::$_default_settings['usp_tag_slugs'];
		}

		$usp_title_template = self::_get_plugin_option('usp_title_template');
		if ( empty( $usp_title_template ) ) {
			$usp_title_template = self::$_default_settings['usp_title_template'];
		}

		$usp_excerpt_template = self::_get_plugin_option('usp_excerpt_template');
		if ( empty( $usp_excerpt_template ) ) {
			$usp_excerpt_template = self::$_default_settings['usp_excerpt_template'];
		}

		$usp_content_template = self::_get_plugin_option('usp_content_template');
		if ( empty( $usp_content_template ) ) {
			$usp_content_template = self::$_default_settings['usp_content_template'];
		}

		$infobox_template = self::_get_plugin_option('novo_map_infobox_template');
		if ( empty( $infobox_template ) ) {
			$infobox_template = self::$_default_settings['novo_map_infobox_template'];
		}

		$usp_marker_index = self::_get_plugin_option('usp_marker_index');
		if ( empty( $usp_marker_index ) ) {
			$usp_marker_index = self::$_default_settings['usp_marker_index'];
		}

		$page_values = array(
			'process_usp_posts_checked' => self::_get_plugin_option('process_usp_posts') ? 'checked="checked" ' : '',

			'assign_usp_categories_checked' => self::_get_plugin_option('assign_usp_categories') ? 'checked="checked" ' : '',
			'usp_categories_rows' => '1',
			'usp_categories_cols' => '80',
			'usp_category_slugs' => $usp_category_slugs,

			'assign_usp_tags_checked' => self::_get_plugin_option('assign_usp_tags') ? 'checked="checked" ' : '',
			'usp_tags_rows' => '1',
			'usp_tags_cols' => '80',
			'usp_tag_slugs' => $usp_tag_slugs,

			'create_usp_title_checked' => self::_get_plugin_option('create_usp_title') ? 'checked="checked" ' : '',
			'usp_title_rows' => '1',
			'usp_title_cols' => '80',
			'usp_title_template' => $usp_title_template,

			'create_usp_excerpt_checked' => self::_get_plugin_option('create_usp_excerpt') ? 'checked="checked" ' : '',
			'usp_excerpt_rows' => '3',
			'usp_excerpt_cols' => '80',
			'usp_excerpt_template' => $usp_excerpt_template,

			'create_usp_content_checked' => self::_get_plugin_option('create_usp_content') ? 'checked="checked" ' : '',
			'usp_content_rows' => '5',
			'usp_content_cols' => '80',
			'usp_content_template' => $usp_content_template,

			'create_novo_map_marker_checked' => self::_get_plugin_option('create_novo_map_marker') ? 'checked="checked" ' : '',
			'novo_map_infobox_rows' => '5',
			'novo_map_infobox_cols' => '80',
			'novo_map_infobox_template' => $infobox_template,

			'usp_marker_rows' => '1',
			'usp_marker_cols' => '1',
			'usp_marker_index' => $usp_marker_index,
		);
		$options_list = MLAData::mla_parse_template( self::$page_template_array['page-level-options'], $page_values );

		$form_arguments = '?page=' . self::SLUG_PREFIX . '-settings-general&mla_tab=general';

		$page_values = array(
			'form_url' => admin_url( 'options-general.php' ) . $form_arguments,
			'_wpnonce' => wp_nonce_field( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME, true, false ),
			'options_list' => $options_list,
		);

		$page_content['body'] .= MLAData::mla_parse_template( self::$page_template_array['general-tab'], $page_values );

		return $page_content;
	}

	/**
	 * Compose the General tab content for the Settings subpage
	 *
	 * @since 1.00
	 * @uses $page_template_array contains tab content template(s)
 	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private static function _compose_documentation_tab( ) {
		$page_content = array( 'message' => '', 'body' => '' );
		$page_values = array(
		);

		$page_content['body'] = MLAData::mla_parse_template( self::$page_template_array['documentation-tab'], $page_values );
		return $page_content;
	}

	/**
	 * Save settings as a WordPress wp_options entry
	 *
	 * @since 1.00
	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private static function _save_setting_changes() {
		$page_content = array( 'message' => 'Settings unchanged.', 'body' => '' );
		
		$changed  = self::_update_plugin_option( 'process_usp_posts', isset( $_REQUEST[ 'mla_usp_novo_map_options' ]['process_usp_posts'] ) );

		$changed  |= self::_update_plugin_option( 'assign_usp_categories', isset( $_REQUEST[ 'mla_usp_novo_map_options' ]['assign_usp_categories'] ) );
		$changed |= self::_update_plugin_option( 'usp_category_slugs', stripslashes( $_REQUEST[ 'mla_usp_novo_map_options' ]['usp_category_slugs'] ) );

		$changed  |= self::_update_plugin_option( 'assign_usp_tags', isset( $_REQUEST[ 'mla_usp_novo_map_options' ]['assign_usp_tags'] ) );
		$changed |= self::_update_plugin_option( 'usp_tag_slugs', stripslashes( $_REQUEST[ 'mla_usp_novo_map_options' ]['usp_tag_slugs'] ) );

		$changed  |= self::_update_plugin_option( 'create_usp_title', isset( $_REQUEST[ 'mla_usp_novo_map_options' ]['create_usp_title'] ) );
		$changed |= self::_update_plugin_option( 'usp_title_template', stripslashes( $_REQUEST[ 'mla_usp_novo_map_options' ]['usp_title_template'] ) );

		$changed  |= self::_update_plugin_option( 'create_usp_excerpt', isset( $_REQUEST[ 'mla_usp_novo_map_options' ]['create_usp_excerpt'] ) );
		$changed |= self::_update_plugin_option( 'usp_excerpt_template', stripslashes( $_REQUEST[ 'mla_usp_novo_map_options' ]['usp_excerpt_template'] ) );

		$changed  |= self::_update_plugin_option( 'create_usp_content', isset( $_REQUEST[ 'mla_usp_novo_map_options' ]['create_usp_content'] ) );
		$changed |= self::_update_plugin_option( 'usp_content_template', stripslashes( $_REQUEST[ 'mla_usp_novo_map_options' ]['usp_content_template'] ) );

		$changed  |= self::_update_plugin_option( 'create_novo_map_marker', isset( $_REQUEST[ 'mla_usp_novo_map_options' ]['create_novo_map_marker'] ) );
		$changed |= self::_update_plugin_option( 'novo_map_infobox_template', stripslashes( $_REQUEST[ 'mla_usp_novo_map_options' ]['novo_map_infobox_template'] ) );
		
		$usp_marker_index = absint( $_REQUEST[ 'mla_usp_novo_map_options' ]['usp_marker_index'] );
		if ( 9 < $usp_marker_index || 1 > $usp_marker_index ) {
			$usp_marker_index = self::$_default_settings['usp_marker_index'];
		}
		$changed |= self::_update_plugin_option( 'usp_marker_index', $usp_marker_index );
		
		if ( $changed ) {
			// No reason to save defaults in the database
			if ( self::$_settings === self::$_default_settings ) {
				delete_option( self::SLUG_PREFIX . '-settings' ); 
			} else {
				$changed = update_option( self::SLUG_PREFIX . '-settings', self::$_settings, false );
			}

			if ( $changed ) {
				$page_content['message'] = "Settings have been updated.";
			} else {
				$page_content['message'] = "Settings updated failed.";
			}
		}

		return $page_content;		
	} // _save_setting_changes

	/**
	 * Assemble the in-memory representation of the custom feed settings
	 *
	 * @since 1.00
	 *
	 * @param boolean $force_refresh Optional. Force a reload of rules. Default false.
	 * @return boolean Success (true) or failure (false) of the operation
	 */
	private static function _get_plugin_option_settings( $force_refresh = false ) {
		if ( false == $force_refresh && NULL != self::$_settings ) {
			return true;
		}

		// Update the plugin options from the wp_options table or set defaults
		self::$_settings = get_option( self::SLUG_PREFIX . '-settings' );
		if ( !is_array( self::$_settings ) ) {
			self::$_settings = self::$_default_settings;
		}

		return true;
	}

	/**
	 * In-memory representation of the option settings
	 *
	 * @since 1.00
	 *
	 * @var array $_settings {
	 *     @type boolean $process_usp_posts Process the Featured Images for User Submitted Posts
	 *
	 *     @type boolean $assign_usp_categories Assign USP Post Category terms
	 *     @type string  $usp_category_slugs Slug(s) for the USP Post Category term(s)
	 *
	 *     @type boolean $assign_usp_tags Assign USP Post Tag terms
	 *     @type string  $usp_tag_slugs Slug(s) for the USP Post Tag term(s)
	 *
	 *     @type boolean $create_usp_title Automatically populate the USP Post Title
	 *     @type string  $usp_title_template Content Template for the USP Post Title
	 *
	 *     @type boolean $create_usp_excerpt Automatically populate the USP Post Excerpt
	 *     @type string  $usp_excerpt_template Content Template for the USP Post Excerpt
	 *
	 *     @type boolean $create_usp_content Automatically populate the USP Post Content
	 *     @type string  $usp_content_template Content Template for the USP Post Content
	 *
	 *     @type boolean $create_novo_map_marker Automatically create the Novo Map marker
	 *     @type string  $novo_map_infobox_template Content Template for the Novo Map Infobox
	 *     @type integer $usp_marker_index Content Template for the Novo Map Infobox
	 *     }
	 */
	private static $_settings = NULL;

	/**
	 * Default processing options
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $_default_settings = array (
						'process_usp_posts' => false,

						'assign_usp_categories' => false,
						'usp_category_slugs' => '',

						'assign_usp_tags' => false,
						'usp_tag_slugs' => '',

						'create_usp_title' => false,
						'usp_title_template' => '',

						'create_usp_excerpt' => false,
						'usp_excerpt_template' => '',

						'create_usp_content' => false,
						'usp_content_template' => '',

						'create_novo_map_marker' => false,
						'novo_map_infobox_template' => '(Location: [+exif:GPS.LatitudeDM+],  [+exif:GPS.LongitudeDM+])',
						'usp_marker_index' => 2
					);

	/**
	 * Get a custom feed option setting
	 *
	 * @since 1.00
	 *
	 * @param string	$name Option name
	 *
	 * @return	mixed	Option value, if it exists else NULL
	 */
	private static function _get_plugin_option( $name ) {
		if ( !self::_get_plugin_option_settings() ) {
			return NULL;
		}

		if ( !isset( self::$_settings[ $name ] ) ) {
			return NULL;
		}
		
		return self::$_settings[ $name ];
	}

	/**
	 * Update a custom feed option setting
	 *
	 * @since 1.00
	 *
	 * @param string $name Option name
	 * @param mixed	$new_value Option value
	 *
	 * @return mixed True if option value changed, false if value unchanged, NULL if failure
	 */
	private static function _update_plugin_option( $name, $new_value ) {
		if ( !self::_get_plugin_option_settings() ) {
			return NULL;
		}

		$old_value = isset( self::$_settings[ $name ] ) ? self::$_settings[ $name ] : NULL;
		
		if ( $new_value === $old_value ) {
			return false;
		}
		
		self::$_settings[ $name ] = $new_value;
		return true;
	}

	/**
	 * Accumulate the USP values 
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $usp_values = array();

	/**
	 * Evaluate parent_terms: or page_terms: values
	 *
	 * @since 1.01
	 *
	 * @param	mixed	String or array - initial value
	 * @param	integer	The Post ID of the new/updated attachment
	 * @param	string	Taxonomy slug
	 * @param	string	Field name in term object
	 * @param	string	Format/option; text,single,export,unpack,array
	 *
	 * @return	mixed	String or array 
	 */
	private static function _evaluate_terms( $custom_value, $post_id, $taxonomy, $qualifier, $option ) {
		if ( 0 == $post_id ) {
			return $custom_value;
		}

		if ( empty( $qualifier ) ) {
			$qualifier = 'name';
		}

		$terms = get_object_term_cache( $post_id, $taxonomy );
		if ( false === $terms ) {
			$terms = wp_get_object_terms( $post_id, $taxonomy );
			wp_cache_add( $post_id, $terms, $taxonomy . '_relationships' );
		}

		if ( 'array' == $option ) {
			$custom_value = array();
		} else {
			$custom_value = '';
		}

		if ( is_wp_error( $terms ) ) {
			$custom_value = implode( ',', $terms->get_error_messages() );
		} elseif ( ! empty( $terms ) ) {
			if ( 'single' == $option || 1 == count( $terms ) ) {
				reset( $terms );
				$term = current( $terms );
				$fields = get_object_vars( $term );
				$custom_value = isset( $fields[ $qualifier ] ) ? $fields[ $qualifier ] : $fields['name'];
				$custom_value = sanitize_term_field( $qualifier, $custom_value, $term->term_id, $taxonomy, 'display' );
			} elseif ( ( 'export' == $option ) || ( 'unpack' == $option ) ) {
				$custom_value = sanitize_text_field( var_export( $terms, true ) );
			} else {
				foreach ( $terms as $term ) {
					$fields = get_object_vars( $term );
					$field_value = isset( $fields[ $qualifier ] ) ? $fields[ $qualifier ] : $fields['name'];
					$field_value = sanitize_term_field( $qualifier, $field_value, $term->term_id, $taxonomy, 'display' );

					if ( 'array' == $option ) {
						$custom_value[] = $field_value;
					} else {
						$custom_value .= strlen( $custom_value ) ? ', ' . $field_value : $field_value;
					}
				}
			}
		}

		return $custom_value;
	} // _evaluate_terms

	/**
	 * MLA Expand Custom Prefix Filter
	 *
	 * Gives you an opportunity to generate your custom data value when a parameter's prefix value is not recognized.
	 *
	 * @since 1.01
	 *
	 * @param	string	$custom_value NULL, indicating that by default, no custom value is available
	 * @param	string	$key the data-source name 
	 * @param	array	$value data-source components; prefix (empty), value, option, format and args (if present)
	 * @param	array	$query values from the query, if any, e.g. shortcode parameters
	 * @param	array	$markup_values item-level markup template values, if any
	 * @param	integer	$post_id attachment ID for attachment-specific values
	 * @param	boolean	$keep_existing for option 'multi', retain existing values
	 * @param	string	$default_option default option value
	 */
	public static function mla_expand_custom_prefix( $custom_value, $key, $value, $query, $markup_values, $post_id, $keep_existing, $default_option ) {
		static $parent_cache = array(), $author_cache = array();

		//error_log( __LINE__ . " MLAUSPNovoMapExample::mla_expand_custom_prefix( {$key}, {$post_id}, {$keep_existing}, {$default_option} ) value = " . var_export( $value, true ), 0 );
		//error_log( __LINE__ . " MLAUSPNovoMapExample::mla_expand_custom_prefix( {$key}, {$post_id} ) query = " . var_export( $query, true ), 0 );
		//error_log( __LINE__ . " MLAUSPNovoMapExample::mla_expand_custom_prefix( {$key}, {$post_id} ) markup_values = " . var_export( $markup_values, true ), 0 );

		// Look for field/value qualifier
		$match_count = preg_match( '/^(.+)\((.+)\)/', $value['value'], $matches );
		if ( $match_count ) {
			$field = $matches[1];
			$qualifier = $matches[2];
		} else {
			$field = $value['value'];
			$qualifier = '';
		}

		if ( 0 == absint( $post_id ) ) {
			return $custom_value;
		}

		if ( 'usp_terms' == $value['prefix'] ) {
			if ( isset( $markup_values['parent'] ) ) {
				$post_parent = absint( $markup_values['parent'] );
			} else {
				$item = get_post( $post_id );
				$post_parent = absint( $item->post_parent );
			}

			$custom_value = self::_evaluate_terms( $custom_value, $post_parent, $field, $qualifier, $value['option'] );
		} elseif ( 'usp' == $value['prefix'] ) {
			if ( isset( $markup_values['parent'] ) ) {
				$parent_id = absint( $markup_values['parent'] );
			} else {
				$item = get_post( $post_id );
				$parent_id = absint( $item->post_parent );
			}

			if ( 0 == $parent_id ) {
				return $custom_value;
			}

			if ( isset( $parent_cache[ $parent_id ] ) ) {
				$parent = $parent_cache[ $parent_id ];
			} else {
				$parent = get_post( $parent_id );

				if ( $parent instanceof WP_Post && $parent->ID == $parent_id ) {
					$parent_cache[ $parent_id ] = $parent;
				} else {
					return $custom_value;
				}
			}

			if ( property_exists( $parent, $value['value'] ) ) {
				$custom_value = $parent->{$value['value']};
			} elseif ( 'permalink' == $value['value'] ) {
				$custom_value = get_permalink( $parent );
			} else {
				// Look for a custom field match
				$meta_value = get_metadata( 'post', $parent_id, $value['value'], false );
//error_log( __LINE__ . " MLAUSPNovoMapExample::mla_expand_custom_prefix( {$key}, {$post_id}, {$parent_id} ) meta_value = " . var_export( $meta_value, true ), 0 );
				if ( !empty( $meta_value ) ) {
					$custom_value = $meta_value;
				}
			}

//error_log( __LINE__ . " MLAUSPNovoMapExample::mla_expand_custom_prefix( {$key}, {$post_id}, {$parent_id} ) custom_value = " . var_export( $custom_value, true ), 0 );

			if ( is_array( $custom_value ) ) {
				if ( 'single' == $value['option'] || 1 == count( $custom_value ) ) {
					$custom_value = sanitize_text_field( reset( $custom_value ) );
				} elseif ( ( 'export' == $value['option'] ) || ( 'unpack' == $value['option'] ) ) {
					$custom_value = sanitize_text_field( var_export( $custom_value, true ) );
				} else {
					if ( 'array' == $value['option'] ) {
						$new_value = array();
					} else {
						$new_value = '';
					}

					foreach ( $custom_value as $element ) {
						$field_value = sanitize_text_field( $element );

						if ( 'array' == $value['option'] ) {
							$new_value[] = $field_value;
						} else {
							$new_value .= strlen( $custom_value ) ? ', ' . $field_value : $field_value;
						}
					}

					$custom_value = $new_value;
				}
			}
		}

		return $custom_value;
	} // mla_expand_custom_prefix

	/**
	 * USP Files Before Action
	 *
	 * @since 1.00
	 *
	 * @param	array 	$files List of files
	 */
	public static function usp_files_before( $files ) {
		//error_log( __LINE__ . " MLAUSPNovoMapExample::usp_files_before files = " . var_export( $files, true ), 0 );
		self::$usp_values['usp_files_before'] = $files;
	} // usp_files_before

	/**
	 * USP Insert Attachment Data Filter
	 *
	 * @since 1.00
	 *
	 * @param	array 	$attachment Attachment data values
	 */
	public static function usp_insert_attachment_data( $attachment ) {
		//error_log( __LINE__ . " MLAUSPNovoMapExample::usp_insert_attachment_data attachment = " . var_export( $attachment, true ), 0 );
		self::$usp_values['usp_insert_attachment_data'] = $attachment;
		
		return $attachment;
	} // usp_insert_attachment_data

	/**
	 * USP Files After Action
	 *
	 * @since 1.00
	 *
	 * @param	array 	$attach_ids List of attachment IDs
	 */
	public static function usp_files_after( $attach_ids ) {
		//error_log( __LINE__ . " MLAUSPNovoMapExample::usp_files_after attach_ids = " . var_export( $attach_ids, true ), 0 );
		self::$usp_values['usp_insert_attachment_data']['id'] = reset( $attach_ids );
	} // usp_files_after

	/**
	 * USP Post Data Filter
	 *
	 * @since 1.00
	 *
	 * @param	array 	$postData Post data values
	 */
	public static function usp_post_data( $postData ) {
		//error_log( __LINE__ . " MLAUSPNovoMapExample::usp_post_data postData = " . var_export( $postData, true ), 0 );
		self::$usp_values['usp_post_data'] = $postData;
		
		return $postData;
	} // usp_post_data

	/**
	 * USP Insert (Post) Before Action
	 *
	 * @since 1.00
	 *
	 * @param	array 	$postData Post data values
	 */
	public static function usp_insert_before( $postData ) {
		//error_log( __LINE__ . " MLAUSPNovoMapExample::usp_insert_before postData = " . var_export( $postData, true ), 0 );
	} // usp_insert_before

	/**
	 * USP Insert (Post) After Action
	 *
	 * @since 1.00
	 *
	 * @param	array 	$newPost New Post ['id']
	 */
	public static function usp_insert_after( $newPost ) {
		//error_log( __LINE__ . " MLAUSPNovoMapExample::usp_insert_after newPost = " . var_export( $newPost, true ), 0 );
		if (isset($newPost['error'][0]) && empty($newPost['error'][0])) {
			self::$usp_values['usp_post_data']['id'] = $newPost['id'];
		}
	} // usp_insert_after

	/**
	 * USP Post Category Slug(s) - append categories to the USP Post
	 *
	 * @since 1.01
	 *
	 * @uses self::$usp_values ['usp_post_data']
	 */
	private static function _post_category_slugs() {
		$usp_category_slugs = self::_get_plugin_option('usp_category_slugs');
		if ( empty( $usp_category_slugs ) ) {
			$usp_category_slugs = self::$_default_settings['usp_category_slugs'];
		}

		if ( empty( $usp_category_slugs ) ) {
			return;
		}
		
		if ( isset( self::$usp_values['usp_post_data']['id'] ) ) {
			$post_id = self::$usp_values['usp_post_data']['id'];
		} else {
			return;
		}
		
		$usp_category_slugs = explode( ',', $usp_category_slugs );
		//error_log( __LINE__ . " MLAUSPNovoMapExample::_post_category_slugs usp_category_slugs = " . var_export( $usp_category_slugs, true ), 0 );
		$term_taxonomy_ids = wp_set_object_terms( $post_id, $usp_category_slugs, 'category', true );
		//error_log( __LINE__ . " MLAUSPNovoMapExample::_post_category_slugs term_taxonomy_ids = " . var_export( $term_taxonomy_ids, true ), 0 );
	} // _post_category_slugs

	/**
	 * USP Post Tag Slug(s) - append tagss to the USP Post
	 *
	 * @since 1.01
	 *
	 * @uses self::$usp_values ['usp_post_data']
	 */
	private static function _post_tag_slugs() {
		$usp_tag_slugs = self::_get_plugin_option('usp_tag_slugs');
		if ( empty( $usp_tag_slugs ) ) {
			$usp_tag_slugs = self::$_default_settings['usp_tag_slugs'];
		}

		if ( empty( $usp_tag_slugs ) ) {
			return;
		}
		
		if ( isset( self::$usp_values['usp_post_data']['id'] ) ) {
			$post_id = self::$usp_values['usp_post_data']['id'];
		} else {
			return;
		}
		
		$usp_tag_slugs = explode( ',', $usp_tag_slugs );
		//error_log( __LINE__ . " MLAUSPNovoMapExample::_post_tag_slugs usp_tag_slugs = " . var_export( $usp_tag_slugs, true ), 0 );
		$term_taxonomy_ids = wp_set_object_terms( $post_id, $usp_tag_slugs, 'post_tag', true );
		//error_log( __LINE__ . " MLAUSPNovoMapExample::_post_tag_slugs term_taxonomy_ids = " . var_export( $term_taxonomy_ids, true ), 0 );
	} // _post_tag_slugs

	/**
	 * USP Post Title Template - return new value for post_title 
	 *
	 * @since 1.01
	 *
	 * @uses self::$usp_values ['usp_post_data'], ['usp_insert_attachment_data']
	 *
	 * @return string New value for post_title else empty string
	 */
	private static function _apply_post_title_template() {
		$usp_title_template = self::_get_plugin_option('usp_title_template');
		if ( empty( $usp_title_template ) ) {
			$usp_title_template = self::$_default_settings['usp_title_template'];
		}

		if ( empty( $usp_title_template ) ) {
			return '';
		}
		
		if ( isset( self::$usp_values['usp_post_data']['id'] ) ) {
			$post_id = self::$usp_values['usp_post_data']['id'];
		} else {
			return '';
		}

		if ( isset( self::$usp_values['usp_insert_attachment_data']['id'] ) ) {
			$attachment_id = self::$usp_values['usp_insert_attachment_data']['id'];
		} else {
			return '';
		}

		//error_log( __LINE__ . " MLAUSPNovoMapExample::_apply_post_title_template( $post_id, $attachment_id ) usp_title_template = " . var_export( $usp_title_template, true ), 0 );

		$data_source = array(
			'data_source' => 'template',
			'meta_name' => $usp_title_template,
			'option' => 'text',
			'format' => 'raw',
		);
		$usp_title_value = MLAShortcodes::mla_get_data_source( $attachment_id, 'single_attachment_mapping', $data_source, NULL );
		//error_log( __LINE__ . " MLAUSPNovoMapExample::_apply_post_title_template( $post_id, $attachment_id ) usp_title_value = " . var_export( $usp_title_value, true ), 0 );

		return $usp_title_value;
	} // _apply_post_title_template

	/**
	 * USP Post Excerpt Template - return new value for post_excerpt 
	 *
	 * @since 1.01
	 *
	 * @uses self::$usp_values ['usp_post_data'], ['usp_insert_attachment_data']
	 *
	 * @return string New value for post_excerpt else empty string
	 */
	private static function _apply_post_excerpt_template() {
		$usp_excerpt_template = self::_get_plugin_option('usp_excerpt_template');
		if ( empty( $usp_excerpt_template ) ) {
			$usp_excerpt_template = self::$_default_settings['usp_excerpt_template'];
		}

		if ( empty( $usp_excerpt_template ) ) {
			return '';
		}
		
		if ( isset( self::$usp_values['usp_post_data']['id'] ) ) {
			$post_id = self::$usp_values['usp_post_data']['id'];
		} else {
			return '';
		}

		if ( isset( self::$usp_values['usp_insert_attachment_data']['id'] ) ) {
			$attachment_id = self::$usp_values['usp_insert_attachment_data']['id'];
		} else {
			return '';
		}

		//error_log( __LINE__ . " MLAUSPNovoMapExample::_apply_post_excerpt_template( $post_id, $attachment_id ) usp_excerpt_template = " . var_export( $usp_excerpt_template, true ), 0 );

		$data_source = array(
			'data_source' => 'template',
			'meta_name' => $usp_excerpt_template,
			'option' => 'text',
			'format' => 'raw',
		);
		$usp_excerpt_value = MLAShortcodes::mla_get_data_source( $attachment_id, 'single_attachment_mapping', $data_source, NULL );
		//error_log( __LINE__ . " MLAUSPNovoMapExample::_apply_post_excerpt_template( $post_id, $attachment_id ) usp_excerpt_value = " . var_export( $usp_excerpt_value, true ), 0 );

		return $usp_excerpt_value;
	} // _apply_post_excerpt_template

	/**
	 * USP Post Content Template - return new value for post_content
	 *
	 * @since 1.01
	 *
	 * @uses self::$usp_values ['usp_post_data'], ['usp_insert_attachment_data']
	 *
	 * @return string New value for post_content else empty string
	 */
	private static function _apply_post_content_template() {
		$usp_content_template = self::_get_plugin_option('usp_content_template');
		if ( empty( $usp_content_template ) ) {
			$usp_content_template = self::$_default_settings['usp_content_template'];
		}

		if ( empty( $usp_content_template ) ) {
			return '';
		}
		
		if ( isset( self::$usp_values['usp_post_data']['id'] ) ) {
			$post_id = self::$usp_values['usp_post_data']['id'];
		} else {
			return '';
		}

		if ( isset( self::$usp_values['usp_insert_attachment_data']['id'] ) ) {
			$attachment_id = self::$usp_values['usp_insert_attachment_data']['id'];
		} else {
			return '';
		}

		//error_log( __LINE__ . " MLAUSPNovoMapExample::_apply_post_excerpt_template( $post_id, $attachment_id ) usp_content_template = " . var_export( $usp_content_template, true ), 0 );

		$data_source = array(
			'data_source' => 'template',
			'meta_name' => $usp_content_template,
			'option' => 'text',
			'format' => 'raw',
		);
		$usp_content_value = MLAShortcodes::mla_get_data_source( $attachment_id, 'single_attachment_mapping', $data_source, NULL );
		//error_log( __LINE__ . " MLAUSPNovoMapExample::_apply_post_excerpt_template( $post_id, $attachment_id ) usp_content_value = " . var_export( $usp_content_value, true ), 0 );

		return $usp_content_value;
	} // _apply_post_content_template

	/**
	 * Create Novo Map Marker
	 *
	 * @since 1.01
	 *
	 * @uses self::$usp_values ['usp_insert_attachment_data']
	 */
	private static function _create_novomap_marker() {
		global $wpdb;

		$data_source = array(
			'data_source' => 'template',
			'meta_name' => '([+exif:GPS.LatitudeSDD+])',
			'option' => 'text',
			'format' => 'raw',
		);
		$LatitudeSDD = MLAShortcodes::mla_get_data_source( self::$usp_values['usp_insert_attachment_data']['id'], 'single_attachment_mapping', $data_source, NULL );
		//error_log( __LINE__ . " MLAUSPNovoMapExample::_create_novomap_marker data_value = " . var_export( $LatitudeSDD, true ), 0 );
		
		$data_source = array(
			'data_source' => 'template',
			'meta_name' => '([+exif:GPS.LongitudeSDD+])',
			'option' => 'text',
			'format' => 'raw',
		);
		$LongitudeSDD = MLAShortcodes::mla_get_data_source( self::$usp_values['usp_insert_attachment_data']['id'], 'single_attachment_mapping', $data_source, NULL );
		//error_log( __LINE__ . " MLAUSPNovoMapExample::_create_novomap_marker data_value = " . var_export( $LongitudeSDD, true ), 0 );
		
		$infobox_template = self::_get_plugin_option('novo_map_infobox_template');
		if ( empty( $infobox_template ) ) {
			$infobox_template = self::$_default_settings['novo_map_infobox_template'];
		}

		$data_source = array(
			'data_source' => 'template',
			'meta_name' => $infobox_template,
			'option' => 'text',
			'format' => 'raw',
		);
		$infobox_value = MLAShortcodes::mla_get_data_source( self::$usp_values['usp_insert_attachment_data']['id'], 'single_attachment_mapping', $data_source, NULL );
		//error_log( __LINE__ . " MLAUSPNovoMapExample::_create_novomap_marker infobox_value = " . var_export( $infobox_value, true ), 0 );

		$usp_marker_index = self::_get_plugin_option('usp_marker_index');
		if ( empty( $usp_marker_index ) ) {
			$usp_marker_index = self::$_default_settings['usp_marker_index'];
		}

		// Load the Novo Map parts we need
		$dir = plugin_dir_path( __DIR__ );
		require_once $dir . 'novo-map/admin/helpers/admin-helpers.php';
		require_once $dir . 'novo-map/includes/class-novo-map-marker.php';
		require_once $dir . 'novo-map/includes/class-novo-map-marker-manager.php';
		$marker_manager = new Marker_manager( $wpdb );

		$novo_post = array (
			'novo-map-marker-title' => self::$usp_values['usp_post_data']['post_title'],
			'novo-map-marker-marker_logo_id' => (string) $usp_marker_index,
			'novo-map-marker-latitude' => $LatitudeSDD,
			'novo-map-marker-longitude' => $LongitudeSDD,
			'novo-map-marker-infobox_image' => self::$usp_values['usp_insert_attachment_data']['id'],
			'novo-map-marker-infobox_description' => $infobox_value,
			'novo-map-marker-categories' => get_post_categories_name_list( self::$usp_values['usp_post_data']['id'] ),
			'novo-map-marker-tags' => get_post_tags_name_list( self::$usp_values['usp_post_data']['id'] ),
			'novo-map-marker-post_id' => self::$usp_values['usp_post_data']['id'],
		);
		
		$marker = new Marker( $novo_post );
		$marker_id = $marker_manager->add( $marker );
		//error_log( __LINE__ . " MLAUSPNovoMapExample::_create_novomap_marker marker_id = " . var_export( $marker_id, true ), 0 );
	} // _create_novomap_marker

	/**
	 * USP New Post Filter
	 *
	 * @since 1.00
	 *
	 * @param	array 	$newPost Post data values
	 */
	public static function usp_new_post( $newPost ) {
		global $wpdb;
		
		//error_log( __LINE__ . " MLAUSPNovoMapExample::usp_new_post newPost = " . var_export( $newPost, true ), 0 );
		//error_log( __LINE__ . " MLAUSPNovoMapExample::usp_new_post self::usp_values = " . var_export( self::$usp_values, true ), 0 );

		// Nothing to do if we don't have a Featured Image
		if ( empty( self::$usp_values['usp_insert_attachment_data'] ) ) {
			return $newPost;
		}

		// At this point we have everything we need/will get
		
		//Start by adding term assignments so they are available for later template processing
		if ( self::_get_plugin_option('assign_usp_categories') ) {
			self::_post_category_slugs();
		}

		if ( self::_get_plugin_option('assign_usp_tags') ) {
			self::_post_tag_slugs();
		}

		// Accumulate changes to apply all at once
		$usp_post_updates = array();
		
		if ( self::_get_plugin_option('create_usp_title') ) {
			$post_title = self::_apply_post_title_template();
			if ( !empty( $post_title ) ) {
				$usp_post_updates['post_title'] = $post_title;
			}
		}

		if ( self::_get_plugin_option('create_usp_excerpt') ) {
			$post_excerpt = self::_apply_post_excerpt_template();
			if ( !empty( $post_excerpt ) ) {
				$usp_post_updates['post_excerpt'] = $post_excerpt;
			}
		}

		if ( self::_get_plugin_option('create_usp_content') ) {
			$post_content = self::_apply_post_content_template();
			if ( !empty( $post_content ) ) {
				$usp_post_updates['post_content'] = $post_content;
			}
		}

		//error_log( __LINE__ . " MLAUSPNovoMapExample::usp_new_post usp_post_updates = " . var_export( $usp_post_updates, true ), 0 );
		// Apply changes, if any
		if ( !empty( $usp_post_updates ) ) {
			if ( isset( self::$usp_values['usp_post_data']['id'] ) ) {
				$usp_post_updates['ID'] = self::$usp_values['usp_post_data']['id'];
				//error_log( __LINE__ . " MLAUSPNovoMapExample::usp_new_post usp_post_updates = " . var_export( $usp_post_updates, true ), 0 );
				$result = wp_update_post( $usp_post_updates );
				//error_log( __LINE__ . " MLAUSPNovoMapExample::usp_new_post result = " . var_export( $result, true ), 0 );
			}
		}
		
		if ( self::_get_plugin_option('create_novo_map_marker') ) {
			self::_create_novomap_marker();
		}

		return $newPost;
	} // usp_new_post
} //MLAUSPNovoMapExample

/*
 * Install the filters at an early opportunity
 */
add_action('init', 'MLAUSPNovoMapExample::initialize');
?>
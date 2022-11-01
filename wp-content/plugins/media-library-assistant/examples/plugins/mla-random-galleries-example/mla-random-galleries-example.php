<?php
/**
 * Provides [mla_gallery] parameters to select random items from a collection of taxonomy terms
 *
 * Detailed information is in the Settings/MLA Random Galleries Documentation tab.
 *
 * Created for support topic "multiple calls to a smaller amount"
 * opened on 1/16/2016 by "luigsm".
 * https://wordpress.org/support/topic/multiple-calls-to-a-smaller-amount/
 *
 * Enhanced for support topic "Gallery incl control break"
 * opened on 9/17/2020 by "ernstwg".
 * https://wordpress.org/support/topic/gallery-incl-control-break/
 *
 * @package MLA Random Galleries Example
 * @version 1.05
 */

/*
Plugin Name: MLA Random Galleries Example
Plugin URI: http://davidlingren.com/
Description: High performance queries for random items from a list of taxonomy terms
Author: David Lingren
Version: 1.05
Author URI: http://davidlingren.com/

Copyright 2016-2020 David Lingren

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
 * Class MLA Random Galleries Example supplies random items from a collection of taxonomy terms
 *
 * @package MLA Random Galleries Example
 * @since 1.00
 */
class MLARandomGalleriesExample {
	/**
	 * Current version number
	 *
	 * @since 1.02
	 *
	 * @var	string
	 */
	const PLUGIN_VERSION = '1.05';

	/**
	 * Constant to log this plugin's debug activity
	 *
	 * @since 1.02
	 *
	 * @var	integer
	 */
	const MLA_DEBUG_CATEGORY = 0x00008000;

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 1.02
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'mlarandomgalleries';

	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 1.00
	 */
	public static function initialize() {
		// This plugin requires MLA
		if ( ! class_exists( 'MLACore', false ) ) {
			return;
		}

		if ( is_admin() ) {
			// Add submenu page in the "Settings" section
			add_action( 'admin_menu', 'MLARandomGalleriesExample::admin_menu' );
			return;
		}

		// These filters are only useful for front-end posts/pages
		add_filter( 'mla_gallery_attributes', 'MLARandomGalleriesExample::mla_gallery_attributes_filter', 10, 1 );
	}

	/**
	 * Add submenu page in the "Settings" section
	 *
	 * @since 1.02
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
		add_submenu_page( 'options-general.php', 'MLA Random Galleries Example', 'MLA Random Galleries', 'manage_options', self::SLUG_PREFIX . '-settings' . $tab, 'MLARandomGalleriesExample::add_submenu_page' );
		add_filter( 'plugin_action_links', 'MLARandomGalleriesExample::plugin_action_links', 10, 2 );
	}

	/**
	 * Add the "Settings" and "Guide" links to the Plugins section entry
	 *
	 * @since 1.02
	 *
	 * @param	array 	array of links for the Plugin, e.g., "Activate"
	 * @param	string 	Directory and name of the plugin Index file
	 *
	 * @return	array	Updated array of links for the Plugin
	 */
	public static function plugin_action_links( $links, $file ) {
		if ( $file == 'mla-random-galleries-example/mla-random-galleries-example.php' ) {
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . self::SLUG_PREFIX . '-settings-documentation&mla_tab=documentation' ), 'Guide' );
			array_unshift( $links, $settings_link );
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . self::SLUG_PREFIX . '-settings-general' ), 'Settings' );
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	/**
	 * Render (echo) the "MLA Random Galleries" submenu in the Settings section
	 *
	 * @since 1.02
	 *
	 * @return	void Echoes HTML markup for the submenu page
	 */
	public static function add_submenu_page() {
		if ( !current_user_can( 'manage_options' ) ) {
			echo "<h2>MLA Random Galleries Example - Error</h2>\n";
			wp_die( 'You do not have permission to manage plugin settings.' );
		}

		// Load template array and initialize page-level values.
		self::$page_template_array = MLACore::mla_load_template( dirname( __FILE__ ) . '/admin-settings-page.tpl', 'path' );
		$current_tab_slug = isset( $_REQUEST['mla_tab'] ) ? $_REQUEST['mla_tab']: 'general';
		$current_tab = self::_get_options_tablist( $current_tab_slug );
		$page_values = array(
			'version' => 'v' . self::PLUGIN_VERSION,
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
	 * @since 1.02
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
	 * @since 1.02
	 *
	 * @var	array
	 */
	private static $mla_tablist = array(
		'general' => array( 'title' => 'General', 'render' => array( 'MLARandomGalleriesExample', '_compose_general_tab' ) ),
		'documentation' => array( 'title' => 'Documentation', 'render' => array( 'MLARandomGalleriesExample', '_compose_documentation_tab' ) ),
		);

	/**
	 * Retrieve the list of options tabs or a specific tab value
	 *
	 * @since 1.02
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
	 * @since 1.02
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
	 * @since 1.02
	 * @uses $page_template_array contains tab content template(s)
 	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private static function _compose_general_tab( ) {
		$page_content = array( 'message' => '', 'body' => '' );

		// Initialize page messages and content, check for page-level Save Changes
		if ( !empty( $_REQUEST['mla-random-galleries-options-save'] ) ) {
			check_admin_referer( MLACore::MLA_ADMIN_NONCE_ACTION, MLACore::MLA_ADMIN_NONCE_NAME );
			$page_content = self::_save_setting_changes( );
		}

		if ( !empty( $page_content['body'] ) ) {
			return $page_content;
		}

		// Display the General tab
		$_SERVER['REQUEST_URI'] = remove_query_arg( array(
			'mla_random_galleries_options',
			'_wpnonce',
			'_wp_http_referer',
			'mla-random-galleries-options-save',
		), $_SERVER['REQUEST_URI'] );

		// Compose page-level options
		$page_values = array(
			'random_taxonomy' => self::_get_plugin_option('random_taxonomy'),
			'random_term_list' => self::_get_plugin_option('random_term_list'),
			'verify_post_attributes_checked' => self::_get_plugin_option('verify_post_attributes') ? 'checked="checked" ' : '',
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
	 * @since 1.02
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
	 * @since 1.02
	 *
	 * @return	array	'message' => status/error messages, 'body' => tab content
	 */
	private static function _save_setting_changes() {
		$page_content = array( 'message' => 'Settings unchanged.', 'body' => '' );

		// random_taxonomy cannot be empty
		if ( empty( $_REQUEST[ 'mla_random_galleries_options' ]['random_taxonomy'] ) ) {
			$_REQUEST[ 'mla_random_galleries_options' ]['random_taxonomy'] = self::$_default_settings['random_taxonomy'];
		}

		$changed  = self::_update_plugin_option( 'random_taxonomy', $_REQUEST[ 'mla_random_galleries_options' ]['random_taxonomy'] );
		$changed |= self::_update_plugin_option( 'random_term_list', $_REQUEST[ 'mla_random_galleries_options' ]['random_term_list'] );
		$changed |= self::_update_plugin_option( 'verify_post_attributes', isset( $_REQUEST[ 'mla_random_galleries_options' ]['verify_post_attributes'] ) );

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
	 * Assemble the in-memory representation of the plugin settings
	 *
	 * @since 1.02
	 *
	 * @param boolean $force_refresh Optional. Force a reload of rules. Default false.
	 *
	 * @return boolean Success (true) or failure (false) of the operation
	 */
	private static function _get_plugin_settings( $force_refresh = false ) {
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
	 * @since 1.02
	 *
	 * @var array $_settings {
	 *     @type string $random_taxonomy The taxonomy to select terms from
	 *     @type string $random_term_list complete comma-delimited list of terms to select from.
	 *                 If empty, all terms in the taxonomy are processed.
	 *     @type boolean $verify_post_attributes True to add a post_type=attachment check to the database query
	 *     }
	 */
	private static $_settings = NULL;

	/**
	 * Default processing options
	 *
	 * @since 1.02
	 *
	 * @var	array
	 */
	private static $_default_settings = array (
				'random_taxonomy' => 'attachment_category',
				'random_term_list' => 'Colorado River,admin',
				'verify_post_attributes' => false,
			);

	/**
	 * Get a plugin option setting
	 *
	 * @since 1.02
	 *
	 * @param string	$name Option name
	 *
	 * @return	mixed	Option value, if it exists else NULL
	 */
	private static function _get_plugin_option( $name ) {
		if ( !self::_get_plugin_settings() ) {
			return NULL;
		}

		if ( !isset( self::$_settings[ $name ] ) ) {
			return NULL;
		}

		return self::$_settings[ $name ];
	}

	/**
	 * Update a plugin option setting
	 *
	 * @since 1.02
	 *
	 * @param string $name Option name
	 * @param mixed	$new_value Option value
	 *
	 * @return mixed True if option value changed, false if value unchanged, NULL if failure
	 */
	private static function _update_plugin_option( $name, $new_value ) {
		if ( !self::_get_plugin_settings() ) {
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
	 * Save the random galleries
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $random_galleries = NULL;

	/**
	 * List of term names/slugs for the galleries
	 *
	 * Populated from the plugin option settings or a shortcode parameter.
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $gallery_terms = array();

	/**
	 * MLA Gallery (Display) Attributes
	 *
	 * The $shortcode_attributes array is where you will find any of your own parameters that are coded in the
	 * shortcode, e.g., [mla_gallery random_term="abc"].
	 *
	 * @since 1.00
	 *
	 * @param	array	the shortcode parameters passed in to the shortcode
	 */
	public static function mla_gallery_attributes_filter( $shortcode_attributes ) {
		global $wpdb;
		static $current_taxonomy = NULL, $current_term_list = NULL;

		MLACore::mla_debug_add( __LINE__ . ' MLARandomGalleriesExample::mla_gallery_attributes_filter shortcode_attributes = ' . var_export( $shortcode_attributes, true ), self::MLA_DEBUG_CATEGORY );

		$primary_arguments = array(
			'random_taxonomy' => NULL, // If not supplied, a plugin option gives a default value
			'random_term_list' =>  NULL, // If the parameter is present but empty, all terms in the taxonomy are processed.
			'verify_post_attributes' => NULL, // Add database validation of wp_posts attributes 
			'random_term' => NULL, // If the parameter is present but empty, all terms in the taxonomy are displayed.
			'posts_per_term' => '0', // how many images per term to display.
			'shuffle_gallery' => NULL, // apply random order to final item list
		);

		$secondary_arguments = array(
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'post_mime_type' => 'image',
		);

		// Find the plugin's unique arguments and supply defaults
		$arguments = shortcode_atts( $primary_arguments, $shortcode_attributes );

		if ( NULL !== $arguments['verify_post_attributes'] ) {
			// Convert checkbox option to boolean
			if ( $arguments['verify_post_attributes'] ) {
				$arguments['verify_post_attributes'] = 'true' === strtolower( trim( $arguments['verify_post_attributes'] ) );;
			} else {
				$arguments['verify_post_attributes'] = false;
			}
		}

		if ( NULL !== $arguments['shuffle_gallery'] ) {
			// Convert checkbox option to boolean
			if ( $arguments['shuffle_gallery'] ) {
				$arguments['shuffle_gallery'] = 'true' === strtolower( trim( $arguments['shuffle_gallery'] ) );;
			} else {
				$arguments['shuffle_gallery'] = false;
			}
		} else {
			$arguments['shuffle_gallery'] = true;
		}

		// ignore shortcodes without any of the "random galleries" parameters
		if ( $arguments === $primary_arguments ) {
			return $shortcode_attributes;
		}

		// Add the additional, optional, arguments used by the plugin
		$arguments = array_merge( $arguments, shortcode_atts( $secondary_arguments, $shortcode_attributes ) );
//error_log( __LINE__ . ' MLARandomGalleriesExample::mla_gallery_attributes_filter complete arguments = ' . var_export( $arguments, true ), 0 );

		if ( is_null( $arguments['verify_post_attributes'] ) ) {
			$arguments['verify_post_attributes'] = self::_get_plugin_option( 'verify_post_attributes' );
		}

		MLACore::mla_debug_add( __LINE__ . ' MLARandomGalleriesExample::mla_gallery_attributes_filter arguments = ' . var_export( $arguments, true ), self::MLA_DEBUG_CATEGORY );

		// Get the taxonomy
		if ( empty( $arguments['random_taxonomy'] ) ) {
			if ( NULL === $current_taxonomy ) {
				$random_taxonomy = self::_get_plugin_option( 'random_taxonomy' );
			} else {
				$random_taxonomy = $current_taxonomy;
			}
		} else {
			$random_taxonomy = $arguments['random_taxonomy'];
		}

		// Get term list slug values
		if ( NULL === $arguments['random_term_list'] ) {
			if ( NULL === $current_term_list ) {
				$new_term_list = self::_get_plugin_option( 'random_term_list' );
			} else {
				$new_term_list = $current_term_list;
			}
		} else {
			$new_term_list = $arguments['random_term_list'];
		}
			
		// For each new combination of taxonomy and term list, compute the random galleries just once
		if ( ( $current_taxonomy !== $random_taxonomy ) || ( $current_term_list !== $new_term_list ) ) {
			$current_taxonomy = $random_taxonomy;
			$current_term_list = $new_term_list;
			self::$random_galleries = array();

			if ( empty( trim( $new_term_list ) ) ) {
				self::$gallery_terms = MLAQuery::mla_wp_get_terms( $random_taxonomy, array( 'fields' => 'slugs', 'hide_empty' => false ) );
			} else {
				self::$gallery_terms = explode( ',', $new_term_list );
			}

			foreach ( self::$gallery_terms as $index => $term ) {
				self::$gallery_terms[ $index ] = esc_sql( sanitize_term_field( 'slug', $term, 0, $random_taxonomy, 'db' ) );
			}
			$slugs = "'" . implode( "','", self::$gallery_terms ) . "'";
			self::$gallery_terms = array_flip( self::$gallery_terms );
			
			// Prepare the array for term validation after the database query
			foreach ( self::$gallery_terms as $term => $index ) {
				self::$gallery_terms[ $term ] = 0;
			}
			
//error_log( __LINE__ . " MLARandomGalleriesExample::mla_gallery_attributes_filter gallery_terms ({$random_taxonomy}) = " . var_export( self::$gallery_terms, true ), 0 );

			// Build an array of ( slug => term_taxonomy_id )
			$terms_query = sprintf( 'SELECT term_id, slug FROM %1$s WHERE ( slug IN ( %2$s ) ) ORDER BY term_id', $wpdb->terms, $slugs );
			$_terms_taxonomy_query = sprintf( 'SELECT term_taxonomy_id, term_id FROM %1$s WHERE ( taxonomy=\'%2$s\' ) ORDER BY term_id', $wpdb->term_taxonomy, $random_taxonomy );

			$query = sprintf( 'SELECT tt.term_taxonomy_id, t.term_id, t.slug FROM ( %1$s ) as tt JOIN ( %2$s ) as t ON ( tt.term_id = t.term_id ) ORDER BY term_taxonomy_id', $_terms_taxonomy_query, $terms_query, $slugs );
			$results = $wpdb->get_results( $query );
//error_log( __LINE__ . ' MLARandomGalleriesExample::mla_gallery_attributes_filter results = ' . var_export( $results, true ), 0 );

			// self::$gallery_terms = array();
			foreach ( $results as $result ) {
				self::$gallery_terms[ $result->slug ] = absint( $result->term_taxonomy_id );
			}
			unset( $results );

			// Discard terms that do not appear in the taxonomy
			foreach ( self::$gallery_terms as $term => $index ) {
				if ( 0 === self::$gallery_terms[ $term ] ) {
					unset ( self::$gallery_terms[ $term ] );
				}
			}
			
			MLACore::mla_debug_add( __LINE__ . ' MLARandomGalleriesExample::mla_gallery_attributes_filter gallery_terms = ' . var_export( self::$gallery_terms, true ), self::MLA_DEBUG_CATEGORY );

			if ( !empty( self::$gallery_terms ) ) {
				// Build an array of ( term_taxonomy_id => array( IDs of items assigned to the term )
				$term_taxonomy_ids = implode( ',', array_values( self::$gallery_terms ) );

				// Verifying the post attributes requires a slower database query involving the wp_posts table
				if ( $arguments['verify_post_attributes'] ) {
					$post_type = "'" . implode( "','", explode( ',', $arguments['post_type'] ) ) . "'";
					$post_status = "'" . implode( "','", explode( ',', $arguments['post_status'] ) ) . "'";
					
					if ( 'all' !== strtolower( $arguments['post_mime_type'] ) ) {
						$post_mime_type = str_replace( '%', '%%', wp_post_mime_type_where( $arguments['post_mime_type'], 'p' ) );
					}

					$query = sprintf( 'SELECT object_id, term_taxonomy_id FROM %1$s AS tr INNER JOIN %2$s AS p ON tr.object_id = p.ID WHERE ( tr.term_taxonomy_id IN ( %3$s ) AND ( p.post_type IN  ( %4$s ) )  AND ( p.post_status IN  ( %5$s ) ) %6$s ) ORDER BY RAND()', $wpdb->term_relationships, $wpdb->posts, $term_taxonomy_ids, $post_type, $post_status, $post_mime_type );
				} else {
					$query = sprintf( 'SELECT object_id, term_taxonomy_id FROM %1$s WHERE ( term_taxonomy_id IN ( %2$s ) ) ORDER BY RAND()', $wpdb->term_relationships, $term_taxonomy_ids );
				}

				$results = $wpdb->get_results( $query );
//error_log( __LINE__ . ' MLARandomGalleriesExample::mla_gallery_attributes_filter results = ' . var_export( $results, true ), 0 );

				foreach ( $results as $result ) {
					self::$random_galleries[ $result->term_taxonomy_id ][] = absint( $result->object_id );
				}
				unset( $results );
			}

			$version = self::PLUGIN_VERSION;
			MLACore::mla_debug_add( __LINE__ . " MLARandomGalleriesExample::mla_gallery_attributes_filter v{$version} random_galleries = " . var_export( self::$random_galleries, true ), self::MLA_DEBUG_CATEGORY );
		} // New combination of taxonomy and term list

		if ( NULL === $arguments['random_term'] ) {
			$ids = '0'; // display an empty gallery
		} else {
			// Present but empty => use all of the random gallery terms
			if ( empty( $arguments['random_term'] ) ) {
				$arguments['random_term'] = implode( ',', array_keys( self::$gallery_terms ) );
			}

			$ids = array();
			$posts_per_term = absint( $arguments['posts_per_term'] );

			foreach ( explode( ',', $arguments['random_term'] ) as $random_term ) {
				// Convert the parameter value to a sanitized slug value
				$random_slug = esc_sql( sanitize_term_field( 'slug', $random_term, 0, $random_taxonomy, 'db' ) );

				// Make sure the parameter matches a value in the random galleries
				if ( isset( self::$gallery_terms[ $random_slug ] ) ) {
					$term_taxonomy_id = self::$gallery_terms[ $random_slug ];
					// Some terms may not have any assigned items
					$random_gallery = isset( self::$random_galleries[ $term_taxonomy_id ] ) ? self::$random_galleries[ $term_taxonomy_id ] : array();
					
					// Filter out duplicate items, e.g. assigned to multiple terms
					foreach ( $random_gallery as $index => $item ) {
						if ( in_array( $item, $ids ) ) {
							unset( $random_gallery[ $index ] );
						}
					}
					
					if ( $posts_per_term ) {
						$ids = array_merge( $ids, array_slice( $random_gallery, 0, $posts_per_term, true ) );
					} else {
						$ids = array_merge( $ids, $random_gallery );
					}
				}
//error_log( __LINE__ . " MLARandomGalleriesExample::mla_gallery_attributes_filter ids after {$random_slug} = " . var_export( $ids, true ), 0 );
			} // foreach random_term

			// Randomize the result set
			if ( $arguments['shuffle_gallery'] ) {
				shuffle( $ids );
			}
//error_log( __LINE__ . " MLARandomGalleriesExample::mla_gallery_attributes_filter ids after shuffle = " . var_export( $ids, true ), 0 );
		}

		foreach ( $primary_arguments as $key => $value ) {
			unset( $shortcode_attributes[ $key ] );
		}

		$shortcode_attributes['ids'] = !empty( $ids ) ? implode( ',', $ids ) : '0';

		MLACore::mla_debug_add( __LINE__ . ' MLARandomGalleriesExample::mla_gallery_attributes_filter shortcode_attributes = ' . var_export( $shortcode_attributes, true ), self::MLA_DEBUG_CATEGORY );
		return $shortcode_attributes;
	} // mla_gallery_attributes_filter
} // Class MLARandomGalleriesExample

// Install the filters at an early opportunity
add_action('init', 'MLARandomGalleriesExample::initialize');
?>
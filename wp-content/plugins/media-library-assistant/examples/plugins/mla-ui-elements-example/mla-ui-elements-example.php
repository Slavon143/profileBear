<?php
/**
 * Provides shortcodes to improve user experience for [mla_term_list],
 * [mla_tag_cloud] and [mla_gallery] shortcodes
 *
 * Detailed information is in the Settings/MLA UI Elements Documentation tab.
 *
 * Created for support topic "How do I provide a front-end search of my media items using Custom Fields?"
 * opened on 4/15/2016 by "direys".
 * https://wordpress.org/support/topic/how-do-i-provide-a-front-end-search-of-my-media-items-using-custom-fields
 *
 * Enhanced for support topic "Dynamic search and filters"
 * opened on 5/28/2016 by "ghislainsc".
 * https://wordpress.org/support/topic/dynamic-search-and-filters
 *
 * Enhanced for support topic "Very new to this, need help"
 * opened on 6/15/2016 by "abronk".
 * https://wordpress.org/support/topic/very-new-to-this-need-help/
 *
 * Enhanced for support topic "Limiting search results to attachment tags/'Justifying' gallery grids"
 * opened on 7/2/2016 by "ceophoetography".
 * https://wordpress.org/support/topic/limiting-search-results-to-attachment-tagsjustifying-gallery-grids
 *
 * Enhanced for support topic "Shortcode"
 * opened on 10/18/2016 by "trinitaa".
 * https://wordpress.org/support/topic/shortcode-456/
 *
 * Enhanced for support topic "Search solution"
 * opened on 3/28/2019 by "fabrizioarnone".
 * https://wordpress.org/support/topic/search-solution/
 *
 * Enhanced (bug fixes) for support topic "Drop down not sticking"
 * opened on 12/10/2019 by "ageingdj".
 * https://wordpress.org/support/topic/drop-down-not-sticking/
 *
 * Enhanced (default_empty_gallery) for support topic "Search fields and presentation of results"
 * opened on 6/2/2020 by "ernstwg".
 * https://wordpress.org/support/topic/search-fields-and-presentation-of-results/
 *
 * Enhanced ([mla_archive_list] shortcode) for support topic "Pages by date"
 * opened on 1/18/2021 by "cirks".
 * https://wordpress.org/support/topic/pages-by-date/
 *
 * Enhanced ([mla_text_box] shortcode) for support topic "Checklist behaviour, my_custom_sql, muie_terms_search"
 * opened on 5/18/2021 by "heb51".
 * https://wordpress.org/support/topic/checklist-behaviour-my_custom_sql-muie_terms_search/
 *
 * Enhanced (named control fixes) for support topic "how to split 2 types of tags?"
 * opened on 5/29/2022 by "agdagan".
 * https://wordpress.org/support/topic/how-to-split-2-types-of-tags/
 *
 * Enhanced (attributes parameters on keyword and terms search) for support topic "How to paginate 2 separate gallery"
 * opened on 6/12/2022 by "jejela19".
 * https://wordpress.org/support/topic/how-to-paginate-2-separate-gallery/
 *
 * @package MLA UI Elements Example
 * @version 2.04
 */

/*
Plugin Name: MLA UI Elements Example
Plugin URI: http://davidlingren.com/
Description: Provides shortcodes to improve user experience for [mla_term_list], [mla_tag_cloud] and [mla_gallery] shortcodes. Adds [muie_archive_list] for date-based archive lists.
Author: David Lingren
Version: 2.04
Author URI: http://davidlingren.com/

Copyright 2016-2022 David Lingren

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
 * Class MLA UI Elements Example provides shortcodes to improve user experience for
 * [mla_term_list], [mla_tag_cloud] and [mla_gallery] shortcodes
 *
 * @package MLA UI Elements Example
 * @since 1.00
 */
class MLAUIElementsExample {
	/**
	 * Plugin version number for debug logging
	 *
	 * @since 1.14
	 *
	 * @var	integer
	 */
	const PLUGIN_VERSION = '2.04';

	/**
	 * Constant to log this plugin's debug activity
	 *
	 * @since 1.14
	 *
	 * @var	integer
	 */
	const MLA_DEBUG_CATEGORY = 0x00008000;

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets, scripts and settings
	 *
	 * @since 1.14
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'mlauielementsexample';

	/**
	 * Configuration values for the Settings Management object
	 *
	 * @since 1.14
	 *
	 * @var	array
	 */
	private static $settings_arguments = array(
				'slug_prefix' => self::SLUG_PREFIX,
				'plugin_title' => 'MLA UI Elements Example',
				'menu_title' => 'MLA UI Elements',
				'plugin_file_name_only' => 'mla-ui-elements-example',
				'plugin_version' => self::PLUGIN_VERSION,
				'template_file' => '/admin-settings-page.tpl', // Add the path at runtime, in initialize()
				'options' => array( // 'slug' => array( 'type' => 'text|checkbox', 'default' => 'text|boolean' )
					'checkbox' =>array( 'type' => 'checkbox', 'default' => true ),
					'text' =>array( 'type' => 'text', 'default' => 'custom:' ),
				),
				'general_tab_values' => array(), // additional page_values for 'page-level-options' template
				'documentation_tab_values' => array(
					'plugin_title' => 'MLA UI Elements Example',
				), // page_values for 'documentation-tab' template
			);

	/**
	 * Settings Management object
	 *
	 * @since 1.14
	 *
	 * @var	array
	 */
	private static $plugin_settings = NULL;

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

		// The plugin settings class is shared with other MLA example plugins
		if ( ! class_exists( 'MLAExamplePluginSettings101' ) ) {
			require_once( pathinfo( __FILE__, PATHINFO_DIRNAME ) . '/class-mla-example-plugin-settings-101.php' );
		}

		// Add the run-time values to the arguments
		self::$settings_arguments['template_file'] = dirname( __FILE__ ) . self::$settings_arguments['template_file'];

		// Create our own settings object
		self::$plugin_settings = new MLAExamplePluginSettings101( self::$settings_arguments );

		// The remaining filters are only useful for front-end posts/pages; exit if in the admin section
		if ( is_admin() )
			return;

		add_filter( 'mla_term_list_attributes', 'MLAUIElementsExample::mla_term_list_attributes', 10, 1 );
		add_filter( 'mla_gallery_attributes', 'MLAUIElementsExample::mla_gallery_attributes', 10, 1 );
		add_filter( 'mla_gallery_pagination_values', 'MLAUIElementsExample::mla_gallery_pagination_values', 10, 1 );

		// Add the custom shortcode for generating "sticky" term search text box
		add_shortcode( 'muie_terms_search', 'MLAUIElementsExample::muie_terms_search' );

		// Add the custom shortcode for generating "sticky" keyword search text box
		add_shortcode( 'muie_keyword_search', 'MLAUIElementsExample::muie_keyword_search' );

		// Add the custom shortcode for generating the items per page text box
		add_shortcode( 'muie_per_page', 'MLAUIElementsExample::muie_per_page' );

		// Add the custom shortcode for generating the order by dropdown control
		add_shortcode( 'muie_orderby', 'MLAUIElementsExample::muie_orderby' );

		// Add the custom shortcode for generating the order radio buttons
		add_shortcode( 'muie_order', 'MLAUIElementsExample::muie_order' );

		// Add the custom shortcode for generating assigned terms counts
		add_shortcode( 'muie_assigned_items_count', 'MLAUIElementsExample::muie_assigned_items_count' );

		// Add the custom shortcode for generic text boxes
		add_shortcode( 'muie_text_box', 'MLAUIElementsExample::muie_text_box' );

		// Add the custom shortcode for generating archive lists
		add_shortcode( 'muie_archive_list', 'MLAUIElementsExample::muie_archive_list_shortcode' );
	}

	/**
	 * Pass mla_control_name parameters from [mla_term_list] to [mla_gallery] for muie_filters
	 *
	 * @since 1.05
	 *
	 * @var	array [ $mla_control_name ] = $_REQUEST[ $mla_control_name ]
	 */
	private static $mla_control_names = array();

	/**
	 * Pass term_id/slug choices from [mla_term_list] to [mla_gallery] for muie_filters
	 *
	 * @since 1.07
	 *
	 * @var	array [ taxonomy ] = 'term_id' or 'slug'
	 */
	private static $mla_option_values = array();

	/**
	 * Look for 'muie_filters' that pass the selected parameters from page to page of a paginated gallery
	 *
	 * @since 1.00
	 *
	 * @param	array	the shortcode parameters passed in to the shortcode
	 */
	public static function mla_term_list_attributes( $shortcode_attributes ) {
		// Exit if this is not a "filtered" term list
		$use_filters = !empty( $shortcode_attributes['use_filters'] ) ? trim ( strtolower( $shortcode_attributes['use_filters'] ) ) : '';;
		unset( $shortcode_attributes['use_filters'] );

		$local_filters = 'local' === $use_filters;
		$use_filters = $local_filters || 'true' === $use_filters;
		if ( !$use_filters ) {
			return $shortcode_attributes;
		}

		$muie_debug = ( !empty( $shortcode_attributes['muie_debug'] ) ) ? trim( strtolower( $shortcode_attributes['muie_debug'] ) ) : false;
		unset( $shortcode_attributes['muie_debug'] );
		if ( $muie_debug ) {
			if ( 'true' === $muie_debug ) {
				MLACore::mla_debug_mode( 'buffer' );
			} elseif ( 'log' === $muie_debug ) {
				MLACore::mla_debug_mode( 'log' );
			} else {
				$muie_debug = false;
			}
		}

		if ( $muie_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' MLAUIElementsExample::mla_term_list_attributes input = ' . var_export( $shortcode_attributes, true ) );
		}

		// Pass "slug" overides to mla_gallery_attributes; using the slug is a common practice
		if ( empty( $shortcode_attributes['mla_option_value'] ) ) {
			$mla_option_value =  'term_id';
		} else {
			$mla_option_value = in_array( $shortcode_attributes['mla_option_value'], array( '{+slug+}', '[+slug+]' ) ) ? 'slug' : 'term_id';
		}

		foreach( explode( ',', $shortcode_attributes['taxonomy'] ) as $taxonomy ) {
			self::$mla_option_values[ $taxonomy ] = $mla_option_value;
		}

		// Allow for multiple taxonomies and named controls
		$taxonomy = implode( '-', explode( ',', $shortcode_attributes['taxonomy'] ) );
		$mla_control_name = !empty( $shortcode_attributes['mla_control_name'] ) ? $shortcode_attributes['mla_control_name'] : false;
		if ( $mla_control_name  ) {
			// Handle default 'tax_input[[+taxonomy+]][]' values
			if ( $index = strpos( $mla_control_name, '[]' ) ) {
				$mla_control_name = substr( $mla_control_name, 0, $index );
			} elseif ( $index = strpos( $mla_control_name, '{}' ) ) {
				$mla_control_name = substr( $mla_control_name, 0, $index );
			}
		}

		// Pagination links, e.g. Previous or Next, have muie_filters that encode the form parameters
		if ( !empty( $_REQUEST['muie_filters'] ) ) {
			$filters = json_decode( trim( stripslashes( $_REQUEST['muie_filters'] ), '"' ), true );

			if ( !empty( $filters['tax_input'] ) ) {
				$_REQUEST['tax_input'] = $filters['tax_input'];
			}

			if ( $mla_control_name && !empty( $filters[ $mla_control_name ] ) ) {
				$_REQUEST[ $mla_control_name ] = $filters[ $mla_control_name ];
			}
		}

		$terms = array();
		if ( $mla_control_name && !empty( $_REQUEST[ $mla_control_name ] ) ) {
			self::$mla_control_names[ $mla_control_name ] = $_REQUEST[ $mla_control_name ];
			$terms = $_REQUEST[ $mla_control_name ];
			
			// Copy named control terms to tax_input by default
			if ( !$local_filters ) {
				if ( is_scalar( $terms ) ) {
					$input = array( $terms );
				} else {
					$input = $terms;
				}
	
				// Check for with possible taxonomy.term values from "combined" taxonomies
				foreach( $input as $input_element ) {
					$value = explode( '.', $input_element );
	
					if ( 2 === count( $value ) ) {
						$taxonomy = $value[0];
						$_REQUEST['tax_input'][ $taxonomy ][] = $value[1];
					} else {
						$_REQUEST['tax_input'][ $taxonomy ][] = $input_element;
					}
				}
			} // !$local_filters
		} elseif ( !empty( $_REQUEST['tax_input'] ) && array_key_exists( $taxonomy, $_REQUEST['tax_input'] ) ) {
			$terms = $_REQUEST['tax_input'][ $taxonomy ];
		}
		
		// If nothing is set for this taxonomy we're done
		if ( empty( $terms ) ) {
			if ( $muie_debug ) {
				MLACore::mla_debug_add( __LINE__ . ' MLAUIElementsExample::mla_term_list_attributes no terms present' );
			}

			return $shortcode_attributes;
		}

		if ( is_string( $terms ) ) {
			$terms = (array) trim( stripslashes( $terms ), ' \'"' );
		}

		// Check for a dropdown control with "All Terms" selected
		if ( empty( $shortcode_attributes['option_all_value'] ) ) {
			$option_all = array_search( '0', $terms );
		} else {
			$option_all = array_search( $shortcode_attributes['option_all_value'], $terms );
		}

		if ( false !== $option_all ) {
			unset( $terms[ $option_all ] );
		}

		if ( empty( $shortcode_attributes['option_all_text'] ) ) {
			$option_all = array_search( '', $terms );
		} else {
			$option_all = array_search( sanitize_title( $shortcode_attributes['option_all_text'] ), $terms );
		}

		if ( false !== $option_all ) {
			unset( $terms[ $option_all ] );
		}

		// Reflect option_all changes in the query arguments
		if ( $mla_control_name ) {
			$_REQUEST[ $mla_control_name ] = $terms;
		} else {
			$_REQUEST['tax_input'][ $taxonomy ] = $terms;
		}

		if ( $muie_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' MLAUIElementsExample::mla_term_list_attributes _REQUEST = ' . var_export( $_REQUEST, true ) );
		}

		// Pass selected terms to the shortcode
		if ( !empty( $terms ) ) {
			if ( $mla_control_name && !empty( $_REQUEST[ $mla_control_name ] ) ) {
				$shortcode_attributes[ $shortcode_attributes['mla_item_parameter'] ] = $_REQUEST[ $mla_control_name ];
			} else {
				$shortcode_attributes[ $shortcode_attributes['mla_item_parameter'] ] = implode( ',', $_REQUEST['tax_input'][ $taxonomy ] );
			}
		}

		if ( $muie_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' MLAUIElementsExample::mla_term_list_attributes returns = ' . var_export( $shortcode_attributes, true ) );
		}

		return $shortcode_attributes;
	} // mla_term_list_attributes

	/**
	 * Add the taxonomy, terms, keyword queries and sort parameters to the shortcode,
	 * limit posts_per_page and encode filters for pagination links
	 *
	 * @since 1.00
	 *
	 * @param	array	the shortcode parameters passed in to the shortcode
	 */
	public static function mla_gallery_attributes( $shortcode_attributes ) {
		// Look for date archive option
		$archive_parameter_name = !empty( $shortcode_attributes['archive_parameter_name'] ) ? $shortcode_attributes['archive_parameter_name'] : false;
		if ( $archive_parameter_name ) {
			if ( empty( $shortcode_attributes['add_filters_to'] ) ) {
				$shortcode_attributes['add_filters_to'] = 'any';
			}
			
			unset( $shortcode_attributes['archive_parameter_name'] );
		}
			
		// Only process shortcodes that allow filters
		if ( empty( $shortcode_attributes['add_filters_to'] ) ) {
			return $shortcode_attributes;
		}

		$muie_debug = ( !empty( $shortcode_attributes['muie_debug'] ) ) ? trim( strtolower( $shortcode_attributes['muie_debug'] ) ) : false;
		unset( $shortcode_attributes['muie_debug'] );
		if ( $muie_debug ) {
			if ( 'true' === $muie_debug ) {
				MLACore::mla_debug_mode( 'buffer' );
			} elseif ( 'log' === $muie_debug ) {
				MLACore::mla_debug_mode( 'log' );
			} else {
				$muie_debug = false;
			}
		}

		if ( $muie_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' MLAUIElementsExample::mla_gallery_attributes raw _REQUEST = ' . var_export( $_REQUEST, true ) );
			MLACore::mla_debug_add( __LINE__ . ' MLAUIElementsExample::mla_gallery_attributes raw shortcode_attributes = ' . var_export( $shortcode_attributes, true ) );
		}

		// Unpack filter values encoded for pagination links
		if ( !empty( $_REQUEST['muie_filters'] ) ) {
			$filters = json_decode( trim( stripslashes( $_REQUEST['muie_filters'] ), '"' ), true );

			foreach( $filters as $filter_key => $filter_value ) {
				$_REQUEST[ $filter_key ] = $filter_value;
			}
		}

		// Adjust posts_per_page/numberposts
		if ( !empty( $_REQUEST['muie_per_page'] ) ) {
			unset( $shortcode_attributes['numberposts'] );
			$shortcode_attributes['posts_per_page'] = $_REQUEST['muie_per_page'];
		}

		if ( $muie_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' MLAUIElementsExample::mla_gallery_attributes filtered _REQUEST = ' . var_export( $_REQUEST, true ) );
			MLACore::mla_debug_add( __LINE__ . ' MLAUIElementsExample::mla_gallery_attributes filtered shortcode_attributes = ' . var_export( $shortcode_attributes, true ) );
		}

		// Fill these in from $_REQUEST parameters
		$muie_filters = array();

		// Flag for the "empty_default_gallery" parameter
		$default_gallery = true;

		/*
		 * Special handling of the current archive parameter to make archive processing easier.
		 * Look for this parameter in $_REQUEST if it's not present in the shortcode itself.
		 */
		if ( empty( $shortcode_attributes[ $archive_parameter_name ] ) && !empty( $_REQUEST[ $archive_parameter_name ] ) ) {
			$shortcode_attributes[ $archive_parameter_name ] = $_REQUEST[ $archive_parameter_name ];
		}

		if ( !empty( $shortcode_attributes[ $archive_parameter_name ] ) ) {
			$muie_filters[ $archive_parameter_name ] = $shortcode_attributes[ $archive_parameter_name ];
			$shortcode_attributes = self::_translate_current_archive( $shortcode_attributes, $archive_parameter_name );
			unset( $shortcode_attributes[ $archive_parameter_name ] );
		}

		$mla_control_names = !empty( $shortcode_attributes['mla_control_name'] ) ? explode( ',', $shortcode_attributes['mla_control_name'] ) : array();
		foreach ( $mla_control_names as $control_name ) {
			if ( !empty( $_REQUEST[ $control_name ] ) ) {
				$default_gallery = false;
			}
		}

		// Add the orderby & order parameters
		if ( !empty( $_REQUEST['muie_orderby'] ) ) {
			$muie_filters['muie_orderby'] = $shortcode_attributes['orderby'] = $_REQUEST['muie_orderby'];
		}

		if ( !empty( $_REQUEST['muie_meta_key'] ) ) {
			$muie_filters['muie_meta_key'] = $shortcode_attributes['meta_key'] = $_REQUEST['muie_meta_key'];
		}

		if ( !empty( $_REQUEST['muie_order'] ) ) {
			$muie_filters['muie_order'] = $shortcode_attributes['order'] = $_REQUEST['muie_order'];
		}

		// Add the terms search parameters, if present
		if ( !empty( $_REQUEST['muie_terms_search'] ) && is_array( $_REQUEST['muie_terms_search'] ) && !empty( $_REQUEST['muie_terms_search']['mla_terms_phrases'] ) ) {
			$default_gallery = false;
			$muie_filters['muie_terms_search'] =  $_REQUEST['muie_terms_search'];
			foreach( $muie_filters['muie_terms_search'] as $key => $value ) {
				if ( !empty( $value ) ) {
					$shortcode_attributes[ $key ] = $value;
				}
			}
		}

		// Add the keyword search parameters, if present
		$muie_keyword_search = array( 's' => '', );
		if ( !empty( $_REQUEST['muie_keyword_search'] ) && is_array( $_REQUEST['muie_keyword_search'] ) ) {
			if ( !empty( $shortcode_attributes['muie_keyword_parameter'] ) && isset( $_REQUEST['muie_keyword_search'][ $shortcode_attributes['muie_keyword_parameter'] ] ) ) {
				$muie_keyword_search = $_REQUEST['muie_keyword_search'][ $shortcode_attributes['muie_keyword_parameter'] ];
			} else {
				// Skip any named muie_keyword_parameter arrays and copy default values
				foreach ( $_REQUEST['muie_keyword_search'] as $key => $value ) {
					if ( !is_array( $value ) ) {
						$muie_keyword_search[ $key ] = $value;
					}
				}
			}
		}

		if ( !empty( $muie_keyword_search['s'] ) ) {
			$default_gallery = false;
			$muie_filters['muie_keyword_search'] = $_REQUEST['muie_keyword_search'];
			foreach( $muie_keyword_search as $key => $value ) {
				if ( !empty( $value ) ) {
					$shortcode_attributes[ $key ] = $value;
				}
			}
		}

		// Add the taxonomy filter(s), if present
		$filter_taxonomy = $shortcode_attributes['add_filters_to'];
		if ( !empty( $_REQUEST['tax_input'] ) ) {
			$muie_filters['tax_input'] = $tax_input = $_REQUEST['tax_input'];
		} else {
			$tax_input = array();
		}

		// Add in any simple taxonomy query shortcode parameters
		if ( !empty( $shortcode_attributes ) ) {
			$all_taxonomies = get_taxonomies( array ( 'show_ui' => true ), 'names' );
			$simple_tax_queries = array();
			foreach ( $shortcode_attributes as $key => $value ) {
				if ( 'tax_input' === $key ) {
					$tax_queries = array();
					$compound_values = array_filter( array_map( 'trim', explode( ',', $value ) ) );
					foreach ( $compound_values as $compound_value ) {
						$value = explode( '.', $compound_value );
						if ( 2 === count( $value ) ) {
							if ( array_key_exists( $value[0], $all_taxonomies ) ) {
								$tax_queries[ $value[0] ][] = $value[1];
							} // valid taxonomy
						} // valid coumpound value
					} // foreach compound_value

					foreach( $tax_queries as $key => $value ) {
						$simple_tax_queries[ $key ] = implode(',', $value );
					}
				} // tax_input
				elseif ( array_key_exists( $key, $all_taxonomies ) ) {
					$simple_tax_queries[ $key ] = implode(',', array_filter( array_map( 'trim', explode( ',', $value ) ) ) );
					if ( 'no.terms.assigned' === $simple_tax_queries[ $key ] ) {
						$no_terms_assigned_query = true;
					}
				} // array_key_exists
			} //foreach $shortcode_attributes

			if ( !empty( $simple_tax_queries ) ) {
				foreach ( $simple_tax_queries as $key => $value ) {
					$tax_input[ $key ] = explode( ',', $value );
					unset( $shortcode_attributes[ $key ] );
				}
			}
			if ( $muie_debug ) {
				MLACore::mla_debug_add( __LINE__ . ' MLAUIElementsExample::mla_gallery_attributes simple_tax_queries = ' . var_export( $simple_tax_queries, true ) );
			}
		}

		if ( $muie_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' MLAUIElementsExample::mla_gallery_attributes filtered tax_input = ' . var_export( $tax_input, true ) );
		}

		// Add the [mla_term_list mla_control_name=] parameter(s)
		if ( !empty( self::$mla_control_names ) ) {
			$muie_filters = array_merge( $muie_filters, self::$mla_control_names );
		}

		if ( ! ( empty( $shortcode_attributes[ $filter_taxonomy ] ) && empty( $tax_input ) ) ) {
			$tax_query = '';

			// Validate other tax_query parameters or set defaults
			$tax_relation = 'AND';
			if ( isset( $shortcode_attributes['tax_relation'] ) ) {
				$attr_value = strtoupper( $shortcode_attributes['tax_relation'] );
				if ( in_array( $attr_value, array( 'AND', 'OR' ) ) ) {
					$tax_relation = $attr_value;
				}
			}

			$default_operator = 'IN';
			if ( isset( $shortcode_attributes['tax_operator'] ) ) {
				$attr_value = strtoupper( $shortcode_attributes['tax_operator'] );
				if ( in_array( $attr_value, array( 'IN', 'NOT IN', 'AND' ) ) ) {
					$default_operator = $attr_value;
				}
			}

			$default_children = 'true';
			if ( isset( $shortcode_attributes[ 'tax_include_children' ] ) ) {
				$attr_value = strtolower( $shortcode_attributes[ 'tax_include_children' ] );
				if ( in_array( $attr_value, array( 'false', 'true' ) ) ) {
					$default_children = $attr_value;
				}
			}

			// Look for the optional "simple taxonomy query" as an initial filter
			if ( !empty( $shortcode_attributes[ $filter_taxonomy ] ) ) {
				if ( 'muie-no-terms' !== $shortcode_attributes[ $filter_taxonomy ] ) {
					// Check for a dropdown control with "All Terms" selected
					$terms = explode( ',', $shortcode_attributes[ $filter_taxonomy ] );
					if ( empty( $shortcode_attributes['option_all_value'] ) ) {
						$option_all = array_search( '0', $terms );
					} else {
						$option_all = array_search( $shortcode_attributes['option_all_value'], $terms );
					}

					if ( false !== $option_all ) {
						unset( $terms[ $option_all ] );
					}

					if ( !empty( $terms ) ) {
						$values = "array( '" . implode( "', '", $terms ) . "' )";
						$tax_query .= "array('taxonomy' => '{$filter_taxonomy}' ,'field' => 'slug','terms' => {$values}, 'operator' => '{$default_operator}', 'include_children' => {$default_children} ), ";
					}
				}

				unset( $shortcode_attributes[ $filter_taxonomy ] );
			}

			foreach ( $tax_input as $taxonomy => $terms ) {
				// simple filter_taxonomy query already processed; overrides tax_input
				if ( $taxonomy === $filter_taxonomy ) {
					continue;
				}

				// Check for a dropdown control with "All Terms" selected
				if ( empty( $shortcode_attributes['option_all_value'] ) ) {
					$option_all = array_search( '0', $terms );
				} else {
					$option_all = array_search( $shortcode_attributes['option_all_value'], $terms );
				}

				if ( false !== $option_all ) {
					unset( $terms[ $option_all ] );
				}

				if ( !empty( $terms ) ) {
					// Numeric values could still be a slug
					$field = ( !empty( self::$mla_option_values[ $taxonomy ] ) ) ? self::$mla_option_values[ $taxonomy ] : 'term_id';
					foreach ( $terms as $term ) {
						if ( ! ctype_digit( $term ) ) {
							$field = 'slug';
							break;
						}
					}

					if ( 'term_id' === $field ) {
						$values = 'array( ' . implode( ',', $terms ) . ' )';
					} else {
						$values = "array( '" . implode( "','", $terms ) . "' )";
					}

					// Taxonomy-specific "operator"					
					$tax_operator = $default_operator;
					if ( isset( $shortcode_attributes[ $taxonomy . '_operator' ] ) ) {
						$attr_value = strtoupper( $shortcode_attributes[ $taxonomy . '_operator' ] );
						if ( in_array( $attr_value, array( 'IN', 'NOT IN', 'AND' ) ) ) {
							$tax_operator = $attr_value;
						}
					}

					// Taxonomy-specific "include_children"					
					$tax_children = $default_children;
					if ( isset( $shortcode_attributes[ $taxonomy . '_children' ] ) ) {
						$attr_value = strtolower( $shortcode_attributes[ $taxonomy . '_children' ] );
						if ( in_array( $attr_value, array( 'false', 'true' ) ) ) {
							$tax_children = $attr_value;
						}
					}

					$tax_query .= "array('taxonomy' => '{$taxonomy}' ,'field' => '{$field}','terms' => {$values}, 'operator' => '{$tax_operator}', 'include_children' => {$tax_children} ), ";
				}
			}

			if ( !empty( $tax_query ) ) {
				$default_gallery = false;
				$shortcode_attributes['tax_query'] = "array( 'relation' => '" . $tax_relation . "', " . $tax_query . ')';
			}
		}

		// Check for an initial display of an empty gallery	instead of all images.
		if ( $default_gallery && !empty( $shortcode_attributes['default_empty_gallery'] ) ) {
			if ( 'true' === trim( strtolower( $shortcode_attributes['default_empty_gallery'] ) ) ) {
				$shortcode_attributes['s'] = 'mla-default-empty-gallery-keyword-search-string';
				$shortcode_attributes['mla_search_fields'] = 'title';
			}
		}

		// Add the filter settings to pagination URLs
		MLAUIElementsExample::$muie_filters = array();
		if ( !empty( $shortcode_attributes['mla_output'] ) ) {
			MLAUIElementsExample::$muie_filters['muie_filters'] = urlencode( json_encode( $muie_filters ) );

			if ( !empty( $shortcode_attributes['posts_per_page'] ) ) {
				MLAUIElementsExample::$muie_filters['muie_per_page'] = $shortcode_attributes['posts_per_page'];
			}
		}

		unset( $shortcode_attributes['add_filters_to'] );

		if ( $muie_debug ) {
			MLACore::mla_debug_add( __LINE__ . ' MLAUIElementsExample::mla_gallery_attributes returns = ' . var_export( $shortcode_attributes, true ) );
		}

		return $shortcode_attributes;
	} // mla_gallery_attributes

	/**
	 * Pass muie_filters from mla_gallery_attributes to mla_gallery_pagination_values
	 *
	 * @since 2.02
	 *
	 * @var	array
	 */
	private static $muie_filters = NULL;

	/**
	 * Pagination control substitution values
	 *
	 * @since 2.02
	 *
	 * @param	array	substitution para,eters and values
	 */
	public static function mla_gallery_pagination_values( $markup_values ) {
		// Add or replace the filter parameters
		if ( !empty( MLAUIElementsExample::$muie_filters ) ) {
			$old_query = $markup_values['query_string'];
			$new_query = remove_query_arg( MLAUIElementsExample::$muie_filters, $old_query );
			$new_query = add_query_arg( MLAUIElementsExample::$muie_filters, $new_query );	

			if ( '?' !== $new_query[0] ) {
				$new_query = '?' . $new_query;
			}
	
			$markup_values['query_string'] = $new_query;
	
			if ( !empty( $old_query ) ) {
				$markup_values['request_uri'] = str_replace( $old_query, $new_query, $markup_values['request_uri'] );
				$markup_values['new_url'] = str_replace( $old_query, $new_query, $markup_values['new_url'] );
			} else {
				$markup_values['request_uri'] .= $new_query;
				$markup_values['new_url'] .= $new_query;
			}
		}

		return $markup_values;
	} // mla_gallery_pagination_values

	/**
	 * Terms search generator shortcode
	 *
	 * This shortcode generates an HTML text box with a default mla_terms_phrases value,
	 * and adds hidden parameters for the other Terms Search parameters
	 *
	 * @since 1.00
	 *
	 * @param	array	the shortcode parameters
	 *
	 * @return	string	HTML markup for the generated form
	 */
	public static function muie_terms_search( $attr, $content = NULL ) {
		$default_arguments = array(
			'muie_terms_parameter' => '',
			'muie_attributes' => '',
			'mla_terms_phrases' => '',
			'mla_terms_taxonomies' => '',
			'mla_phrase_delimiter' => '',
			'mla_phrase_connector' => '',
			'mla_term_delimiter' => '',
			'mla_term_connector' => '',
		);

		// Make sure $attr is an array, even if it's empty
		$attr = MLAShortcodes::mla_validate_attributes( $attr, $content );
		
		// Accept only the attributes we need and supply defaults
		$arguments = shortcode_atts( $default_arguments, $attr );
		$qualifier = $arguments['muie_terms_parameter'];
		unset( $arguments['muie_terms_parameter'] );
		$muie_attributes = MLAShortcodes::mla_validate_attributes( $arguments['muie_attributes'] );
		unset( $arguments['muie_attributes'] );

		// Pagination links, e.g. Previous or Next, have muie_filters that encode the form parameters
		if ( !empty( $_REQUEST['muie_filters'] ) ) {
			$filters = json_decode( trim( stripslashes( $_REQUEST['muie_filters'] ), '"' ), true );

			if ( !empty( $filters['muie_terms_search'] ) ) {
				$_REQUEST['muie_terms_search'] = $filters['muie_terms_search'];
			}
		}

		// muie_terms_search has settings from the form or pagination link
		if ( !empty( $_REQUEST['muie_terms_search'] ) && is_array( $_REQUEST['muie_terms_search'] ) ) {
			if ( empty( $qualifier ) ) {
				foreach ( $arguments as $key => $value ) {
					if ( !empty( $_REQUEST['muie_terms_search'][ $key ] ) ) {
						$arguments[ $key ] = stripslashes( $_REQUEST['muie_terms_search'][ $key ] );
					}
				}
			} else {
				foreach ( $arguments as $key => $value ) {
					if ( !empty( $_REQUEST['muie_terms_search'][$qualifier][ $key ] ) ) {
						$arguments[ $key ] = stripslashes( $_REQUEST['muie_terms_search'][$qualifier][ $key ] );
					}
				}
			}
		}

		// Always supply the terms phrases text box, with the appropriate quoting
		if ( false !== strpos( $arguments['mla_terms_phrases'], '"' ) ) {
			$delimiter = '\'';
		} else {
			$delimiter = '"';
		}

		$added_attributes = array_merge( array( 'type' => 'text', 'size' => '20' ), $muie_attributes );
		$attributes = ' ';
		foreach ( $added_attributes as $key => $value ) {
			$attributes .= $key . '="' . $value . '" ';
		}
		
		if ( empty( $qualifier ) ) {
			$return_value = '<input name="muie_terms_search[mla_terms_phrases]" id="muie-terms-phrases"' . $attributes . 'value=' . $delimiter . $arguments['mla_terms_phrases'] . $delimiter . " />\n";		
		} else {
			$return_value = '<input name="muie_terms_search[' . $qualifier . '][mla_terms_phrases]" id="muie-terms-phrases-' . $qualifier . '"' . $attributes . 'value=' . $delimiter . $arguments['mla_terms_phrases'] . $delimiter . " />\n";
		}
		
		unset( $arguments['mla_terms_phrases'] );

		// Add optional parameters
		foreach( $arguments as $key => $value ) {
			if ( !empty( $value ) ) {
				$id_value = str_replace( '_', '-', substr( $key, 4 ) );

				if ( empty( $qualifier ) ) {
					$return_value .= sprintf( '<input name="muie_terms_search[%1$s]" id="muie-%2$s" type="hidden" value="%3$s" />%4$s', $key, $id_value, $value, "\n" );
				} else {
					$id_value .= '-' . $qualifier;
					$return_value .= sprintf( '<input name="muie_terms_search[' . $qualifier . '][%1$s]" id="muie-%2$s" type="hidden" value="%3$s" />%4$s', $key, $id_value, $value, "\n" );	
				}
			}
		}

		return $return_value;
	} // muie_terms_search

	/**
	 * Keyword search generator shortcode
	 *
	 * This shortcode generates an HTML text box with a default "s" (search string) value,
	 * and adds hidden parameters for the other Keyword Search parameters
	 *
	 * @since 1.00
	 *
	 * @param	array	the shortcode parameters
	 *
	 * @return	string	HTML markup for the generated form
	 */
	public static function muie_keyword_search( $attr, $content = NULL ) {
		$default_arguments = array(
			'muie_keyword_parameter' => '',
			'muie_attributes' => '',
			's' => '',
			'mla_search_fields' => '',
			'mla_search_connector' => '',
			'sentence' => '',
			'exact' => '',
		);

		// Make sure $attr is an array, even if it's empty
		$attr = MLAShortcodes::mla_validate_attributes( $attr, $content );

		// Accept only the attributes we need and supply defaults
		$arguments = shortcode_atts( $default_arguments, $attr );
		$qualifier = $arguments['muie_keyword_parameter'];
		unset( $arguments['muie_keyword_parameter'] );
		$muie_attributes = MLAShortcodes::mla_validate_attributes( $arguments['muie_attributes'] );
		unset( $arguments['muie_attributes'] );

		// Pagination links, e.g. Previous or Next, have muie_filters that encode the form parameters
		if ( !empty( $_REQUEST['muie_filters'] ) ) {
			$filters = json_decode( trim( stripslashes( $_REQUEST['muie_filters'] ), '"' ), true );

			if ( !empty( $filters['muie_keyword_search'] ) ) {
				$_REQUEST['muie_keyword_search'] = $filters['muie_keyword_search'];
			}
		}

		// muie_keyword_search has settings from the form or pagination link
		if ( !empty( $_REQUEST['muie_keyword_search'] ) && is_array( $_REQUEST['muie_keyword_search'] ) ) {
			if ( empty( $qualifier ) ) {
				foreach ( $arguments as $key => $value ) {
					if ( !empty( $_REQUEST['muie_keyword_search'][ $key ] ) ) {
						$arguments[ $key ] = stripslashes( $_REQUEST['muie_keyword_search'][ $key ] );
					}
				}
			} else {
				foreach ( $arguments as $key => $value ) {
					if ( !empty( $_REQUEST['muie_keyword_search'][$qualifier][ $key ] ) ) {
						$arguments[ $key ] = stripslashes( $_REQUEST['muie_keyword_search'][$qualifier][ $key ] );
					}
				}
			}
		}

		// Always supply the search text box, with the appropriate quoting
		if ( false !== strpos( $arguments['s'], '"' ) ) {
			$delimiter = '\'';
		} else {
			$delimiter = '"';
		}

		$added_attributes = array_merge( array( 'type' => 'text', 'size' => '20' ), $muie_attributes );
		$attributes = ' ';
		foreach ( $added_attributes as $key => $value ) {
			$attributes .= $key . '="' . $value . '" ';
		}
		
		if ( empty( $qualifier ) ) {
			$return_value = '<input name="muie_keyword_search[s]" id="muie-s"' . $attributes . 'value=' . $delimiter . $arguments['s'] . $delimiter . " />\n";
		} else {
			$return_value = '<input name="muie_keyword_search[' . $qualifier . '][s]" id="muie-s-' . $qualifier . '"' . $attributes . 'value=' . $delimiter . $arguments['s'] . $delimiter . " />\n";
		}
		
		unset( $arguments['s'] );

		// Add optional parameters
		foreach( $arguments as $key => $value ) {
			if ( !empty( $value ) ) {
				if ( 0 === strpos( $key, 'mla' ) ) {
					$id_value = str_replace( '_', '-', substr( $key, 4 ) );
				} else {
					$id_value = str_replace( '_', '-', $key );
				}
				
				if ( empty( $qualifier ) ) {
					$return_value .= sprintf( '<input name="muie_keyword_search[%1$s]" id="muie-%2$s" type="hidden" value="%3$s" />%4$s', $key, $id_value, $value, "\n" );
				} else {
					$id_value .= '-' . $qualifier;
					$return_value .= sprintf( '<input name="muie_keyword_search[' . $qualifier . '][%1$s]" id="muie-%2$s" type="hidden" value="%3$s" />%4$s', $key, $id_value, $value, "\n" );	
				}
			}
		}

		return $return_value;
	} // muie_keyword_search

	/**
	 * Items per page shortcode
	 *
	 * This shortcode generates an HTML text box with a default muie_per_page value.
	 *
	 * @since 1.00
	 *
	 * @param	array	the shortcode parameters
	 *
	 * @return	string	HTML markup for the generated form
	 */
	public static function muie_per_page( $attr ) {
		if ( isset( $attr['numberposts'] ) && ! isset( $attr['posts_per_page'] )) {
			$attr['posts_per_page'] = $attr['numberposts'];
			unset( $attr['numberposts'] );
		}

		if ( !empty( $_REQUEST['muie_per_page'] ) ) {
			$posts_per_page = $_REQUEST['muie_per_page'];
		} else {
			$posts_per_page = isset( $attr['posts_per_page'] ) ? $attr['posts_per_page'] : 6;
		}

		return '<input name="muie_per_page" id="muie-per-page" type="text" size="2" value="' . $posts_per_page . '" />';
	} // muie_per_page

	/**
	 * Order by shortcode
	 *
	 * This shortcode generates a dropdown control with sort order values.
	 *
	 * @since 1.03
	 *
	 * @param array $attr the shortcode parameters
	 * @param string $content Optional content for enclosing shortcodes
	 *
	 * @return	string	HTML markup for the generated control(s)
	 */
	public static function muie_orderby( $attr, $content = NULL  ) {
		$default_arguments = array(
			'shortcode' => 'mla_gallery',
			'sort_fields' => '',
			'meta_value_num' => '',
			'meta_value' => '',
		);

		// Make sure $attr is an array, even if it's empty
		$attr = MLAShortcodes::mla_validate_attributes( $attr, $content );

		// Accept only the attributes we need and supply defaults
		$arguments = shortcode_atts( $default_arguments, $attr );

		switch ( $arguments['shortcode'] ) {
			case 'mla_gallery':
				$allowed_fields = array(
					'empty' => '- select -',
					'ID'  => 'item ID', 
					'author'  => 'Author',
					'date'  => 'Date uploaded', 
					'description' => 'Description',
					'title' => 'Title',
					'caption' => 'Caption',  
					'slug' => 'name/slug', 
					'parent' => 'Parent ID', 
					'menu_order' => 'Menu order', 
					'mime_type' => 'MIME type', 
					'none' => 'No order', 
					'rand' => 'Random', 
				);
				break;
			case 'mla_tag_cloud':
			case 'mla_term_list':
				$allowed_fields = array(
					'empty' => '- select -',
					'count' => 'Assigned items',
					'id' => 'Term ID',
					'name' => 'Term name',
					'slug' => 'Term slug',
					'none' => 'No order', 
					'random' => 'Random', 
				);
				break;
			default:
				$allowed_fields = array();
		}

		if ( empty( $arguments['sort_fields'] ) ) {
			$sort_fields = $allowed_fields;
		} else {
			$sort_fields = array();

			if ( 0 === strpos( $arguments['sort_fields'], 'array' ) ) {
				$function = @create_function('', 'return ' . $arguments['sort_fields'] . ';' );
				if ( is_callable( $function ) ) {
					$field_array = $function();
				}

				if ( is_array( $field_array ) ) {
					$sort_fields = $field_array;
				}
			} else {
				foreach( explode( ',', $arguments['sort_fields'] ) as $field ) {
					if ( array_key_exists( $field, $allowed_fields ) ) {
						$sort_fields[ $field ] = $allowed_fields[ $field ];
					}
				}
			}
		}

		// Check for custom field sorting
		if ( !empty( $arguments['meta_value_num'] ) ) {
			$custom_key = 'meta_value_num';
			$custom_spec = $arguments['meta_value_num'];
		} elseif ( !empty( $arguments['meta_value'] ) ) {
			$custom_key = 'meta_value';
			$custom_spec = $arguments['meta_value'];
		} else {
			$custom_key = '';
			$custom_spec = '';
		}

		if ( !empty( $custom_spec ) ) {
			$spec_parts = explode( '=>', $custom_spec );
			$spec_key = trim( $spec_parts[0], ' \'"' );
			$spec_suffix = '';

			$tail = strrpos( $spec_key, ' DESC' );
			if ( ! ( false === $tail ) ) {
				$spec_key = substr( $spec_key, 0, $tail );
				$spec_suffix = ' DESC';
			} else {
				$tail = strrpos( $spec_key, ' ASC' );
				if ( ! ( false === $tail ) ) {
					$spec_key = substr( $spec_key, 0, $tail );
					$spec_suffix = ' ASC';
				}
			}

			$spec_label = !empty( $spec_parts[1] ) ? trim( $spec_parts[1], ' \'"' ) : $spec_key;
			$sort_fields[ $custom_key . $spec_suffix ] = $spec_label;
		}

		if ( empty( $sort_fields ) ) {
			return '';
		}

		// Unpack filter values encoded for pagination links
		if ( !empty( $_REQUEST['muie_filters'] ) ) {
			$filters = json_decode( trim( stripslashes( $_REQUEST['muie_filters'] ), '"' ), true );

			if ( isset( $filters['muie_orderby'] ) ) {
				$_REQUEST['muie_orderby'] = $filters['muie_orderby'];
			}
		}

		if ( !empty( $_REQUEST['muie_orderby'] ) ) {
			$current_value = $_REQUEST['muie_orderby'];
		} else {
			$current_value = '';
		}

		if ( !empty( $spec_key ) ) {
			$output = '<input name="muie_meta_key" id="muie-meta-key" type="hidden" value="' . $spec_key . '">' . "\n";
		} else {
			$output = '';
		}

		$output .= '<select name="muie_orderby" id="muie-orderby">' . "\n";

		foreach ( $sort_fields as $value => $label ) {
			$value = 'empty' === $value ? '' : $value;

			$selected = ( $current_value === $value ) ? ' selected=selected ' : ' ';

			$output .= '  <option' . $selected . 'value="' . $value . '">' . $label . "</option>\n";
		}

		$output .= "</select>\n";

		return $output;
	} // muie_orderby

	/**
	 * Order (ASC/DESC) shortcode
	 *
	 * This shortcode generates ascending/descending radio buttons.
	 *
	 * @since 1.03
	 *
	 * @param array $attr the shortcode parameters
	 * @param string $content Optional content for enclosing shortcodes
	 *
	 * @return	string	HTML markup for the generated control(s)
	 */
	public static function muie_order( $attr, $content = NULL  ) {
		$default_arguments = array(
			'default_order' => 'ASC',
			'asc_label' => 'Ascending',
			'desc_label' => 'Descending',
		);

		// Make sure $attr is an array, even if it's empty
		$attr = MLAShortcodes::mla_validate_attributes( $attr, $content );

		// Accept only the attributes we need and supply defaults
		$arguments = shortcode_atts( $default_arguments, $attr );

		// Unpack filter values encoded for pagination links
		if ( !empty( $_REQUEST['muie_filters'] ) ) {
			$filters = json_decode( trim( stripslashes( $_REQUEST['muie_filters'] ), '"' ), true );

			if ( isset( $filters['muie_order'] ) ) {
				$_REQUEST['muie_order'] = $filters['muie_order'];
			}
		}

		if ( !empty( $_REQUEST['muie_order'] ) ) {
			$current_value = $_REQUEST['muie_order'];
		} else {
			$current_value = $arguments['default_order'];
		}

		if ( 'DESC' === $current_value ) {
			$asc_selected = '';
			$desc_selected = ' checked="checked"';
		} else {
			$asc_selected = ' checked="checked"';
			$desc_selected = '';
		}

		$output  = '<input name="muie_order" id="muie-order-asc" type="radio"' . $asc_selected . ' value="ASC"> ' .  $arguments['asc_label'] . '&nbsp;&nbsp;';
		$output .= '<input name="muie_order" id="muie-order-desc" type="radio"' . $desc_selected . ' value="DESC">' .  $arguments['desc_label'] . "&nbsp;&nbsp\n";

		return $output;
	} // muie_order

	/**
	 * Assigned items count shortcode
	 *
	 * This shortcode returns the number of items assigned to any term(s) in the selected taxonomy
	 *
	 * @since 1.01
	 *
	 * @param	array	the shortcode parameters
	 *
	 * @return	string	HTML markup for the generated form
	 */
	public static function muie_assigned_items_count( $attr, $content = NULL ) {
		global $wpdb;

		$default_arguments = array(
			'taxonomy' => '',
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'post_mime_type' => 'image',
		);

		// Make sure $attr is an array, even if it's empty
		$attr = MLAShortcodes::mla_validate_attributes( $attr, $content );

		// Accept only the attributes we need and supply defaults
		$arguments = shortcode_atts( $default_arguments, $attr );

		// Build an array of individual clauses that can be filtered
		$clauses = array( 'fields' => '', 'join' => '', 'where' => '', 'order' => '', 'orderby' => '', 'limits' => '', );

		$clause_parameters = array();

		$clause[] = 'LEFT JOIN `' . $wpdb->term_relationships . '` AS tr ON tt.term_taxonomy_id = tr.term_taxonomy_id';
		$clause[] = 'LEFT JOIN `' . $wpdb->posts . '` AS p ON tr.object_id = p.ID';

		// Add type and status constraints
		if ( is_array( $arguments['post_type'] ) ) {
			$post_types = $arguments['post_type'];
		} else {
			$post_types = array( $arguments['post_type'] );
		}

		$placeholders = array();
		foreach ( $post_types as $post_type ) {
			$placeholders[] = '%s';
			$clause_parameters[] = $post_type;
		}

		$clause[] = 'AND p.post_type IN (' . join( ',', $placeholders ) . ')';

		if ( is_array( $arguments['post_status'] ) ) {
			$post_stati = $arguments['post_status'];
		} else {
			$post_stati = array( $arguments['post_status'] );
		}

		$placeholders = array();
		foreach ( $post_stati as $post_status ) {
			if ( ( 'private' != $post_status ) || is_user_logged_in() ) {
				$placeholders[] = '%s';
				$clause_parameters[] = $post_status;
			}
		}
		$clause[] = 'AND p.post_status IN (' . join( ',', $placeholders ) . ')';

		$clause =  join(' ', $clause);
		$clauses['join'] = $wpdb->prepare( $clause, $clause_parameters );

		// Start WHERE clause with a taxonomy constraint
		if ( is_array( $arguments['taxonomy'] ) ) {
			$taxonomies = $arguments['taxonomy'];
		} else {
			$taxonomies = array( $arguments['taxonomy'] );
		}

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				$error = new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy', 'media-library-assistant' ), $taxonomy );
				return $error;
			}
		}

		$clause_parameters = array();
		$placeholders = array();
		foreach ($taxonomies as $taxonomy) {
		    $placeholders[] = '%s';
			$clause_parameters[] = $taxonomy;
		}

		$clause = array( 'tt.taxonomy IN (' . join( ',', $placeholders ) . ')' );
		if ( 'all' !== strtolower( $arguments['post_mime_type'] ) ) {
			$clause[] = str_replace( '%', '%%', wp_post_mime_type_where( $arguments['post_mime_type'], 'p' ) );
		}

		$clause =  join(' ', $clause);
		$clauses['where'] = $wpdb->prepare( $clause, $clause_parameters );

		// Build the final query
		$query = array( 'SELECT' );
		$query[] = 'COUNT(*)'; // 'p.ID'; // $clauses['fields'];
		$query[] = 'FROM ( SELECT DISTINCT p.ID FROM `' . $wpdb->term_taxonomy . '` AS tt';
		$query[] = $clauses['join'];
		$query[] = 'WHERE (';
		$query[] = $clauses['where'];
		$query[] = ') ) as subquery';

		$query =  join(' ', $query);
		$count = $wpdb->get_var( $query );
		return number_format( (float) $count );
	} // muie_assigned_items_count

	/**
	 * Sticky text box shortcode
	 *
	 * This shortcode generates an HTML text box with a sticky value that survives pagination.
	 *
	 * @since 1.14
	 *
	 * @param	array	the shortcode parameters
	 *
	 * @return	string	HTML markup for the generated input control
	 */
	public static function muie_text_box( $attr, $content = NULL ) {
		$default_arguments = array(
			'name' => 'muie_text_box',
			'id' => '',
			'type' => 'text',
			'value' => '',
		);

		// Make sure $attr is an array, even if it's empty
		$attr = MLAShortcodes::mla_validate_attributes( $attr, $content );

		// Accept only the attributes we need and supply defaults
		$arguments = shortcode_atts( $default_arguments, $attr );

		// Pagination links, e.g. Previous or Next, have muie_filters that encode the form parameters
		if ( !empty( $_REQUEST['muie_filters'] ) ) {
			$filters = json_decode( trim( stripslashes( $_REQUEST['muie_filters'] ), '"' ), true );

			if ( !empty( $filters[ $arguments['name'] ] ) ) {
				$_REQUEST[ $arguments['name'] ] = $filters[ $arguments['name'] ];
			}
		}

		// Find the id value
		if ( empty( $arguments['id'] ) ) {
			$arguments['id'] = str_replace( '_', '-', $arguments['name'] );
		}

		// Find the "sticky" value, save for pagination
		if ( !empty( $_REQUEST[ $arguments['name'] ] ) ) {
			$arguments['value'] = $_REQUEST[ $arguments['name'] ];
			self::$mla_control_names[ $arguments['name'] ] = $arguments['value'];
		}

		// Assemble the standard control attributes
		$attributes = array();
		foreach ( $arguments as $key => $value ) {
			$attributes[] = $key . '="' . $value . '"';
			unset( $attr[ $key ] );
		}

		// Add the optional control attributes
		foreach ( $attr as $key => $value ) {
			$attributes[] = $key . '="' . $value . '"';
		}

		// Convert the attributes to a string string
		$attributes = implode( ' ', $attributes );

		return '<input ' . $attributes . ' />';
	} // muie_text_box

	/**
	 * Translates muie_current_archive value to [mla_gallery] query arguments 
	 *
	 * @since 1.14
	 *
	 * @param array  raw shortcode attributes
	 * @param string current parameter name, e.g., muie_current_archive
	 *
	 * @return array updated shortcode attributes
	 */
	private static function _translate_current_archive( $shortcode_attributes, $archive_parameter_name ) {
		$current_value = MLAData::mla_get_template_placeholders( '[+' . $shortcode_attributes[ $archive_parameter_name ] . '+]' );
		$current_value = $current_value[ $shortcode_attributes[ $archive_parameter_name ] ];
		MLACore::mla_debug_add( __LINE__ . " _translate_current_archive() current_value = " . var_export( $current_value, true ), self::$muie_debug_category );

		$date_query = array( 'column' => $current_value['value'], 'compare' => '=' );
		switch ( $current_value['format'] ) {
			case 'D':
				$date_query['year'] = substr( $current_value['args'], 0, 4 );
				$date_query['month'] = substr( $current_value['args'], 4, 2 );
				$date_query['day'] = substr( $current_value['args'], 6, 2 );
				break;
			case 'W':
				$date_query['year'] = substr( $current_value['args'], 0, 4 );
				$date_query['week'] = substr( $current_value['args'], 4, 2 );
				break;
			case 'M':
				$date_query['year'] = substr( $current_value['args'], 0, 4 );
				$date_query['month'] = substr( $current_value['args'], 4, 2 );
				break;
			case 'Y':
				$date_query['year'] = substr( $current_value['args'], 0, 4 );
		}

		// Wrap our query in an outer array to allow for others
		$date_query = array( $date_query );

		/*/ Add existing queries to our date_query TODO
		if ( !empty( $shortcode_attributes['date_query'] ) ) {
			foreach( $clean_request['tax_query'] as $key => $value ) {
				if ( is_integer( $key ) ) {
					$mla_tax_query[] = $value;
				} else {
					$mla_tax_query[ $key ] = $value;
				}
			}

			if ( empty( $mla_tax_query['relation'] ) ) {
				$mla_tax_query['relation'] = 'AND';
			}
		} // existing query */

		if ( 'custom' === $current_value['prefix'] ) {
			$shortcode_attributes['muie_custom_date_query'] = $date_query;
			add_filter( 'mla_gallery_query_attributes', 'MLAUIElementsExample::mla_gallery_query_attributes', 10, 1 );
		} else {
			$shortcode_attributes['date_query'] = $date_query;
		}

		MLACore::mla_debug_add( __LINE__ . " _translate_current_archive( $archive_parameter_name ) shortcode_attributes = " . var_export( $shortcode_attributes, true ), self::$muie_debug_category );
		return $shortcode_attributes;
	}

	/**
	 * MLA Gallery Query Attributes
	 *
	 * This filter gives you an opportunity to record or modify the arguments passed in to the shortcode
	 * before they are merged with the default arguments used to select the attachments for the gallery.
	 *
	 * The query attributes passed in to this filter are the same as those passed through the
	 * "MLA Gallery (Display) Attributes" filter above. This filter is provided so you can modify
	 * the data selection attributes without disturbing the attributes used for gallery display.
	 *
	 * @since 1.00
	 *
	 * @param	array	the shortcode parameters passed in to the shortcode
	 *
	 * @return	array	updated shortcode attributes
	 */
	public static function mla_gallery_query_attributes( $query_attributes ) {
		remove_filter( 'mla_gallery_query_attributes', 'MLAUIElementsExample::mla_gallery_query_attributes', 10 );

		if ( !empty( $query_attributes['muie_custom_date_query'] ) ) {
			self::$custom_date_query = $query_attributes['muie_custom_date_query'];
			unset( $query_attributes['muie_custom_date_query'] );
			add_filter( 'posts_clauses_request', 'MLAUIElementsExample::mla_gallery_posts_clauses_request', 10, 2 );
		}

		return $query_attributes;
	} // mla_gallery_query_attributes

	/**
	 * Explicit Shortcode Attributes for muie_archive_list
	 *
	 * @since 1.14
	 *
	 * @var	array
	 */
	private static $custom_date_query = NULL;

	/**
	 * Filters all query clauses at once, for convenience.
	 *
	 * For use by caching plugins.
	 *
	 * Covers the WHERE, GROUP BY, JOIN, ORDER BY, DISTINCT,
	 * fields (SELECT), and LIMITS clauses.
	 *
	 * @since 1.14
	 *
	 * @param string[] $pieces Associative array of the pieces of the query.
	 * @param WP_Query $wp_query   The WP_Query instance (passed by reference).
	 */
	public static function mla_gallery_posts_clauses_request( $pieces, $wp_query ) {
		global $wpdb;

		remove_filter( 'posts_clauses_request', 'MLAUIElementsExample::mla_gallery_posts_clauses_request', 10 );

		MLACore::mla_debug_add( __LINE__ . " mla_gallery_posts_clauses_request() original pieces = " . var_export( $pieces, true ), self::$muie_debug_category );
		MLACore::mla_debug_add( __LINE__ . " custom_date_query = " . var_export( self::$custom_date_query, true ), self::$muie_debug_category );

		if ( empty( self::$custom_date_query ) ) {
			return $pieces;
		}

		$where = isset( $pieces['where'] ) ? $pieces['where'] : '';
		$join  = isset( $pieces['join'] ) ? $pieces['join'] : '';
		$query = self::$custom_date_query[0];

		$key = $query['column'];

		$field = $wpdb->postmeta . '.meta_value';
		$join .= " INNER JOIN {$wpdb->postmeta} ON ( $wpdb->posts.ID = {$wpdb->postmeta}.post_id )";
		$where .= " AND ( {$wpdb->postmeta}.meta_key = '{$key}' )";

		$compare = $query['compare'];
		$date_where = '';
		$prefix = ' AND ( ( ';
		
		if ( !empty( $query['year'] ) ) {
			$date_where .= $prefix . " YEAR( {$field} ) {$compare} " . absint( $query['year'] ) . ' )';
			$prefix = ' AND ( ';
		}

		if ( !empty( $query['month'] ) ) {
			$date_where .= $prefix . " MONTH( {$field} ) {$compare} " . absint( $query['month'] ) . ' )';
			$prefix = ' AND ( ';
		}

		if ( !empty( $query['week'] ) ) {
			$date_where .= $prefix . " WEEK( {$field}, 1 ) {$compare} " . absint( $query['week'] ) . ' )';
			$prefix = ' AND ( ';
		}

		if ( !empty( $query['day'] ) ) {
			$date_where .= $prefix . " DAYOFMONTH( {$field} ) {$compare} " . absint( $query['day'] ) . ' )';
			$prefix = ' AND ( ';
		}

		if ( !empty( $date_where ) ) {
			$where = $date_where . ' )' . $where;
		}
		
		$pieces['where'] = $where;
		$pieces['join'] = $join;

		self::$custom_date_query = NULL;
		MLACore::mla_debug_add( __LINE__ . " mla_gallery_posts_clauses_request() final pieces = " . var_export( $pieces, true ), self::$muie_debug_category );
		return $pieces;
	} // mla_gallery_posts_clauses_request

	/**
	 * Replaces or removes a query argument, preserving url encoding
	 *
	 * @since 1.15
	 *
	 * @param string argument name
	 * @param mixed argument value (string) or false to remove argument
	 * @param string url
	 *
	 * @return string url with argument replaced
	 */
	private static function _replace_query_parameter( $key, $value, $url ) {
		$parts = wp_parse_url( $url );
		if ( empty( $parts['path'] ) ) {
			$parts['path'] = '';
		}

		// Fragments must come at the end of the URL and be preceded by a #
		if ( !empty( $parts['fragment'] ) ) {
			$parts['fragment'] = '#' . $parts['fragment'];
		} else {
			$parts['fragment'] = '';
		}

		$clean_query = array();
		if ( empty( $parts['query'] ) ) {
			// No existing query arguments; create query if requested
			if ( false !== $value ) {
				$clean_query[ $key ] = $value;
			}
		} else {
			parse_str( $parts['query'], $query );

			$add_it = true;
			foreach ( $query as $query_key => $query_value ) {
				// Query argument names cannot have URL special characters
				if ( $query_key === urldecode( $query_key ) ) {
					if ( $key === $query_key ) {
						$add_it = false;
						// Deleting argument?
						if ( false === $value ) {
							continue;
						}

						$query_value = $value;
					}

					$clean_query[ $query_key ] = $query_value;
				}
			}

			if ( $add_it && ( false !== $value ) ) {
				$clean_query[ $key ] = $value;
			}
		}

		$clean_query = urlencode_deep( $clean_query );
		$clean_query = build_query( $clean_query );

		// Query arguments must come before the fragment, if any
		if ( !empty( $clean_query ) ) {
			return $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . '?' . $clean_query . $parts['fragment'];
		} else {
			return $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . $parts['fragment'];
		}
	}

	/**
	 * Handles 'paginate_archive' output type
	 *
	 * @since 1.15
	 *
	 * @uses array	self::$archive_list_atttr Shortcode parameters, explicit, used by reference
	 * @uses array	self::$archive_list_items List item objects, used by reference
	 *
	 * @param array $markup_values Style and list-level substitution parameters, by reference
	 * @param integer $target_key Index in self::$archive_list_items of the current archive item
	 * @param string $list Shortcode output text, by reference
	 *
	 * @return boolean false; appends to &$list
	 */
	private static function _paginate_links( $markup_values, $target_key, &$list ) {
//error_log( __LINE__ . " _paginate_links( {$target_key} ) markup_values = " . var_export( $markup_values, true ), 0 );

		$items = &self::$archive_list_items;
//error_log( __LINE__ . " _paginate_links( {$target_key} ) items = " . var_export( $items, true ), 0 );
		$attr = &self::$archive_list_attr;
//error_log( __LINE__ . " _paginate_links( {$target_key} ) attr = " . var_export( $attr, true ), 0 );

		if ( ( 0 > $target_key ) || ( 2 > count( $items ) ) ) {
			if ( ! empty( $markup_values['option_none_label'] ) ) {
				$list = self::_process_shortcode_parameter( $markup_values['option_none_label'], $markup_values );
			}

			return false;
		}

		$show_all = $prev_next = false;
		if ( ! empty( $markup_values['archive_qualifier'] ) ) {
				switch ( $markup_values['archive_qualifier'] ) {
				case 'show_all':
					$show_all = true;
					break;
				case 'prev_next':
					$prev_next = true;
			}
		}

		$last_key = count( $items ) - 1;
		$end_size = absint( $markup_values['end_size'] );
		$mid_size = absint( $markup_values['mid_size'] );
		$archive_parameter_name = $markup_values['archive_parameter_name'];

//		$mla_page_parameter = $arguments['mla_page_parameter'];
//		$current_page = $markup_values['current_page'];
//		$last_page = $markup_values['last_page'];
//		$posts_per_page = $markup_values['posts_per_page'];

//		$new_target = ( ! empty( $arguments['mla_target'] ) ) ? 'target="' . $arguments['mla_target'] . '" ' : '';

		// these will add to the default classes
		$new_class = ( ! empty( $arguments['link_class'] ) ) ? ' ' . esc_attr( self::_process_shortcode_parameter( $arguments['link_class'], $markup_values ) ) : '';

		$new_attributes = ( ! empty( $arguments['link_attributes'] ) ) ? esc_attr( self::_process_shortcode_parameter( $arguments['link_attributes'], $markup_values ) ) . ' ' : '';

		$new_base =  ( ! empty( $arguments['link_href'] ) ) ? self::_process_shortcode_parameter( $arguments['link_href'], $markup_values ) : $markup_values['page_url'];

		// Build the array of page links
		$page_links = array();
		$dots = false;

		if ( $prev_next && $target_key ) {
			$item_values = array_merge( $markup_values, (array) $items[ $target_key - 1 ] );
			$new_title = ( ! empty( $item_values['rollover_text'] ) ) ? 'title="' . esc_attr( self::_process_shortcode_parameter( $item_values['rollover_text'], $item_values ) ) . '" ' : '';
			$new_url = self::_replace_query_parameter( $archive_parameter_name, $item_values['current_value'], $new_base );
			$prev_text = ( ! empty( $item_values['prev_text'] ) ) ? esc_attr( self::_process_shortcode_parameter( $item_values['prev_text'], $item_values ) ) : '&laquo; ' . __( 'Previous', 'media-library-assistant' );
			$page_links[] = sprintf( '<a class="prev paginate-archive%1$s" %2$s%3$shref="%4$s">%5$s</a>',
				/* %1$s */ $new_class,
				/* %2$s */ $new_attributes,
				/* %3$s */ $new_title,
				/* %4$s */ $new_url,
				/* %5$s */ $prev_text );
		}

//		for ( $new_page = 1; $new_page <= $last_page; $new_page++ ) {
		foreach ( $items as $key => $item ) {
			$item_values = array_merge( $markup_values, (array) $item );
			$new_title = ( ! empty( $item_values['rollover_text'] ) ) ? 'title="' . esc_attr( self::_process_shortcode_parameter( $item_values['rollover_text'], $item_values ) ) . '" ' : '';

			if ( $key === $target_key ) {
				// build current item span
				$page_links[] = sprintf( '<span class="paginate-archive current%1$s">%2$s</span>',
					/* %1$s */ $new_class,
					/* %2$s */ $item_values['current_label'] );
				$dots = true;
			} else {
				if ( $show_all || ( $key < $end_size || ( $key >= $target_key - $mid_size && $key <= $target_key + $mid_size ) || $key > $last_key - $end_size ) ) {
					// build link
					$new_url = self::_replace_query_parameter( $archive_parameter_name, $item_values['current_value'], $new_base );
					$page_links[] = sprintf( '<a class="paginate-archive%1$s" %2$s%3$shref="%4$s">%5$s</a>',
						/* %1$s */ $new_class,
						/* %2$s */ $new_attributes,
						/* %3$s */ $new_title,
						/* %4$s */ $new_url,
						/* %5$s */ $item_values['current_label'] );
					$dots = true;
				} elseif ( $dots && ! $show_all ) {
					// build link
					$page_links[] = sprintf( '<span class="paginate-archive dots%1$s">&hellip;</span>',
						/* %1$s */ $new_class );
					$dots = false;
				}
			} // ! current
		} // foreach $item

		if ( $prev_next && ( 0 <= $target_key ) && ( $target_key < $last_key ) ) {
			// build next link
			$item_values = array_merge( $markup_values, (array) $items[ $target_key + 1 ] );
			$new_title = ( ! empty( $item_values['rollover_text'] ) ) ? 'title="' . esc_attr( self::_process_shortcode_parameter( $item_values['rollover_text'], $item_values ) ) . '" ' : '';
			$new_url = self::_replace_query_parameter( $archive_parameter_name, $item_values['current_value'], $new_base );
			$next_text = ( ! empty( $item_values['next_text'] ) ) ? esc_attr( self::_process_shortcode_parameter( $item_values['next_text'], $item_values ) ) : __( 'Next', 'media-library-assistant' ) . ' &raquo;';
			$page_links[] = sprintf( '<a class="next paginate-archive%1$s" %2$s%3$shref="%4$s">%5$s</a>',
				/* %1$s */ $new_class,
				/* %2$s */ $new_attributes,
				/* %3$s */ $new_title,
				/* %4$s */ $new_url,
				/* %5$s */ $next_text );
		}

		$list .= join("\n", $page_links);
		return false;
	}

	/**
	 * Handles brace/bracket escaping and parses template for a shortcode parameter
	 *
	 * @since 1.14
	 *
	 * @param string raw shortcode parameter, e.g., "text {+field+} {brackets} \\{braces\\}"
	 * @param array  template substitution values, e.g., ('instance' => '1', ...  )
	 *
	 * @return string parameter with brackets, braces, substitution parameters and templates processed
	 */
	private static function _process_shortcode_parameter( $text, $markup_values ) {
		$new_text = str_replace( '{\+', '\[\+', str_replace( '+\}', '+\\\\]', $text ) );
		$new_text = str_replace( '{', '[', str_replace( '}', ']', $new_text ) );
		$new_text = str_replace( '\[', '{', str_replace( '\]', '}', $new_text ) );
		return MLAData::mla_parse_template( $new_text, $markup_values );
	}

	/**
	 * Compose an mla_archive_list
	 *
	 * Adds shortcode output text and item-specific links to arrays passed by reference.
	 *
	 * @since 1.14
	 *
	 * @uses array	self::$archive_list_atttr Shortcode parameters, explicit, used by reference
	 * @uses array	self::$archive_list_items List item objects, used by reference
	 *
	 * @param string $list Shortcode output text, by reference
	 * @param array $links Term-specific links for flat/array output, by reference
	 * @param array $markup_values Style and list-level substitution parameters, by reference
	 *
	 * @return boolean True if the list contains the "current_item"; appends to &$list, &$links
	 */
	public static function compose_archive_list( &$list, &$links, &$markup_values ) {
		static $page_content = NULL; // for mla_debug_add

		$items = &self::$archive_list_items;
		$attr = &self::$archive_list_attr;

		$archive_parameter_name = $markup_values['archive_parameter_name'];
		$is_dropdown = 'dropdown' === $markup_values['archive_output'];
		$is_list = 'list' === $markup_values['archive_output'];
		$is_link = in_array( $markup_values['archive_output'], array( 'paginate_archive', 'next_archive', 'current_archive', 'previous_archive' ) );

		// Handle an empty, hidden archive
		if ( ( 0 === count( $items ) ) && $markup_values['hide_if_empty'] ) {
			$option_none_label = self::_process_shortcode_parameter( $markup_values['option_none_label'], $markup_values );

			if ( $is_list || $is_dropdown ) {
				$list .= $option_none_label;
			} else {
				if ( !empty( $option_none_label ) ) {
					$links[] = $option_none_label;
				}
			}
			
			return false;
		} // empty archive

		// Load the appropriate templates
		$open_template = '';
		$item_template = '';
		$close_template = '';
		if ( $is_list || $is_dropdown ) {
			if ( $is_list ) {
				if ( isset( self::$muie_archive_templates['muie-archive-list-open-markup'] ) ) {
					$open_template = self::$muie_archive_templates['muie-archive-list-open-markup'];
				}
				if ( isset( self::$muie_archive_templates['muie-archive-list-item-markup'] ) ) {
					$item_template = self::$muie_archive_templates['muie-archive-list-item-markup'];
				}
				if ( isset( self::$muie_archive_templates['muie-archive-list-close-markup'] ) ) {
					$close_template = self::$muie_archive_templates['muie-archive-list-close-markup'];
				}
			} elseif ( $is_dropdown ) {
				if ( isset( self::$muie_archive_templates['muie-archive-dropdown-open-markup'] ) ) {
					$open_template = self::$muie_archive_templates['muie-archive-dropdown-open-markup'];
				}
				if ( isset( self::$muie_archive_templates['muie-archive-dropdown-item-markup'] ) ) {
					$item_template = self::$muie_archive_templates['muie-archive-dropdown-item-markup'];
				}
				if ( isset( self::$muie_archive_templates['muie-archive-dropdown-close-markup'] ) ) {
					$close_template = self::$muie_archive_templates['muie-archive-dropdown-close-markup'];
				}
			}

			// Look for gallery-level markup substitution parameters
			$new_text = $open_template . $close_template;
			$markup_values = MLAData::mla_expand_field_level_parameters( $new_text, $attr, $markup_values );

			if ( !empty( $open_template ) ) {
				$list .= MLAData::mla_parse_template( $open_template, $markup_values );
			}
		} // is_list || is_dropdown
//error_log( __LINE__ . " compose_archive_list() list = " . var_export( $list, true ), 0 );

		// Find delimiter for currentlink, currentlink_url
		if ( strpos( $markup_values['page_url'], '?' ) ) {
			$current_item_delimiter = '&';
		} else {
			$current_item_delimiter = '?';
		}

		// Item values vary by archive type; make sure all elements are defined
		$item_default_values = array_merge( $markup_values, array(
			'current_value' => '',
			'current_label_short' => '',
			'current_label_long' => '',
			'current_label' => '',
			'items' => 0,
			'year' => '',
			'month' => '',
			'week' => '',
			'day' => '',
			'm' => '',
			'yyyymmdd'  => '',
			'month_long' => '',
			'month_short' => '',
			'week_start_raw' => 0,
			'week_start_short' => '',
			'week_start' => '',
			'week_end_raw' => 0,
			'week_end_short' => '',
			'week_end' => '',

			'item_id' => '',
			'item_class' => '',
			'item_attributes' => '',
			'item_selected' => '',
			'item_value' => '',
			'item_label' => '',

			'item_link_id' => '',
			'item_link_class' => '',
			'item_link_rollover' => '',
			'item_link_attributes' => '',
			'item_link_text' => '',

			'thelink' => '',
			'currentlink' => '',
			'viewlink' => '',
			'link_url' => '',
			'currentlink_url' => '',
			'viewlink_url' => '',
		) );

		$has_active = false;

		// Handle an empty, visible archive
		if ( 0 === count( $items ) ) {
			// Apply the Display Content parameters
			$attributes = array();
			
			if ( !empty( $item_default_values['itemtag_id'] ) ) {
				$item_default_values['item_id'] = self::_process_shortcode_parameter( $item_default_values['itemtag_id'], $item_default_values );
				$attributes[] = 'id="' . $item_default_values['item_id'] . '"';
			}

			if ( !empty( $item_default_values['itemtag_class'] ) ) {
				$item_default_values['item_class'] = self::_process_shortcode_parameter( $item_default_values['itemtag_class'], $item_default_values );
				if ( !empty( $item_default_values['item_selected'] ) ) {
					$item_default_values['item_class'] .= ' ' . $item_default_values['current_archive_class'];
				}
				
				$attributes[] = 'class="' . $item_default_values['item_class'] . '"';
			}

			if ( !empty( $item_default_values['itemtag_attributes'] ) ) {
				$attributes[] = self::_process_shortcode_parameter( $item_default_values['itemtag_attributes'], $item_default_values );
			}

			if ( !empty( $attributes ) ) {
				$item_default_values['item_attributes'] = implode( ' ', $attributes ) . ' ';
			}

			if ( !empty( $item_default_values['option_none_value'] ) ) {
				$item_default_values['item_value'] = self::_process_shortcode_parameter( $item_default_values['option_none_value'], $item_default_values );
			} else {
				$item_default_values['item_value'] = 'no-archives';
			}

			if ( !empty( $item_default_values['option_none_label'] ) ) {
				$item_default_values['item_label'] = self::_process_shortcode_parameter( $item_default_values['option_none_label'], $item_default_values );
			} else {
				$item_default_values['item_label'] = 'No archives';
			}

			$item_default_values['thelink'] = $item_default_values['item_label'];

			if ( $is_list || $is_dropdown ) {
				$list .= MLAData::mla_parse_template( $item_template, $item_default_values );
			} else {
				$links[] = $item_default_values['item_label'];
			}
			
			MLACore::mla_debug_add( __LINE__ . " compose_archive_list() empty archive, item_default_values = " . var_export( $item_default_values, true ), self::$muie_debug_category );
			return false;
		} else { // Empty, visible archive
	
			// Add the "option all" element, if specified
			if ( !empty( $item_default_values['option_all_label'] ) ) {
				// Apply the Display Content parameters
				$attributes = array();
				
				if ( !empty( $item_default_values['itemtag_id'] ) ) {
					$item_default_values['item_id'] = self::_process_shortcode_parameter( $item_default_values['itemtag_id'], $item_default_values );
					$attributes[] = 'id="' . $item_default_values['item_id'] . '"';
				}
	
				if ( !empty( $item_default_values['itemtag_class'] ) ) {
					$item_default_values['item_class'] = self::_process_shortcode_parameter( $item_default_values['itemtag_class'], $item_default_values );
					if ( !empty( $item_default_values['item_selected'] ) ) {
						$item_default_values['item_class'] .= ' ' . $item_default_values['current_archive_class'];
					}
					
					$attributes[] = 'class="' . $item_default_values['item_class'] . '"';
				}
	
				if ( !empty( $item_default_values['itemtag_attributes'] ) ) {
					$attributes[] = self::_process_shortcode_parameter( $item_default_values['itemtag_attributes'], $item_default_values );
				}
	
				if ( !empty( $attributes ) ) {
					$item_default_values['item_attributes'] = implode( ' ', $attributes ) . ' ';
				}
	
				if ( !empty( $item_default_values['option_all_value'] ) ) {
					$item_default_values['item_value'] = self::_process_shortcode_parameter( $item_default_values['option_all_value'], $item_default_values );
				} else {
					$item_default_values['item_value'] = '';
				}
	
				if ( !empty( $item_default_values['option_all_label'] ) ) {
					$item_default_values['item_label'] = self::_process_shortcode_parameter( $item_default_values['option_all_label'], $item_default_values );
				} else {
					$item_default_values['item_label'] = 'Select archive';
				}

				$item_default_values['thelink'] = $item_default_values['item_label'];
	
				if ( $is_list || $is_dropdown ) {
					$list .= MLAData::mla_parse_template( $item_template, $item_default_values );
				} else {
					$links[] = $item_default_values['item_label'];
				} 
			} // Option all value
		} // non-empty, visible archive

		MLACore::mla_debug_add( __LINE__ . " compose_archive_list() markup_values[ $archive_parameter_name ] = " . var_export( $markup_values[ $archive_parameter_name ], true ), self::$muie_debug_category );

		// Do this once per page load only through MLA Reporting
		if ( NULL === $page_content ) {
			$page_content = $item_default_values['page_content'];
			MLACore::mla_debug_add( __LINE__ . " compose_archive_list() page content = " . var_export( $page_content, true ), self::MLA_DEBUG_CATEGORY );
		}

		// Expand the items with values needed for all output types including $is_link types
		foreach ( $items as $key => $item ) {
			$month_stamp = 0;
			if ( !empty( $item->month ) ) {
				$item->m = sprintf( '%1$04d%2$02d', (integer) $item->year, (integer) $item->month );
				
				if ( !empty( $item->day ) ) {
					$item->yyyymmdd = sprintf( '%1$04d-%2$02d-%3$02d', (integer) $item->year, (integer) $item->month, (integer) $item->day );
					$month_stamp = strtotime( $item->yyyymmdd );
				} else {
					$month_stamp = strtotime( $item->m . '01' );
				}
			} elseif ( !empty( $item->week_start_short ) ) {
				$month_stamp = strtotime( $item->week_start_short );
			}
			
			if ( $month_stamp ) {
				$item->month_long = date( 'F', $month_stamp );
				$item->month_short = date( 'M', $month_stamp );
			}

			// Compute the current_value and current_labels based on the archive type
			switch ( $markup_values['archive_type'] ) {
				case 'daily':
					$item->current_value = sprintf( 'D(%1$04d%2$02d%3$02d)', (integer) $item->year, (integer) $item->month, (integer) $item->day );
					$item->current_label_short = sprintf( '%1$04d/%2$02d/%3$02d', (integer) $item->year, (integer) $item->month, (integer) $item->day );
					$item->current_label_long = sprintf( '%1$s %2$02d, %3$04d', $item->month_long, (integer) $item->day, (integer) $item->year );
					$item->viewlink_url = get_day_link( (integer) $item->year, (integer) $item->month, (integer) $item->day );
					break;
				case 'weekly':
					$item->current_value = sprintf( 'W(%1$04d%2$02d)', (integer) $item->year, (integer) $item->week );
					$item->current_label_short = $item->week_start_short;
					$item->current_label_long = $item->week_start;
					$item->viewlink_url = add_query_arg(
						array(
							'm' => $item->year,
							'w' => $item->week,
						),
						home_url( '/' )
					);
					break;
				case 'monthly':
					$item->current_value = sprintf( 'M(%1$04d%2$02d)', (integer) $item->year, (integer) $item->month );
					$item->current_label_short = sprintf( '%1$s %2$s', $item->month_short, $item->year );
					$item->current_label_long = sprintf( '%1$s %2$s', $item->month_long, $item->year );
					$item->viewlink_url = get_month_link( (integer) $item->year, (integer) $item->month );
					break;
				case 'yearly':
				default:
					$item->current_value = sprintf( 'Y(%1$04d)', (integer) $item->year );
					$item->current_label_short = sprintf( '%1$04d', (integer) $item->year );
					$item->current_label_long = sprintf( '%1$04d', (integer) $item->year );
					$item->viewlink_url = get_year_link( (integer) $item->year );
			}

			// Add the archive source to the current value
			$current_args = ',' . $item->current_value;
			if ( 'custom' === $markup_values['archive_source'] ) {
				$item->current_value = 'custom:' . $markup_values['archive_key'] . $current_args;
			} else {
				$item->current_value = $markup_values['archive_source'] . $current_args;
			}

			$item->current_label = ( 'short' === $markup_values['archive_label'] ) ? $item->current_label_short : $item->current_label_long;
			$item->currentlink_url = sprintf( '%1$s%2$s%3$s=%4$s', $markup_values['page_url'], $current_item_delimiter, $archive_parameter_name, urlencode( $item->current_value ) );
			$items[ $key ] = $item;
		}
//error_log( __LINE__ . " compose_archive_list() items = " . var_export( $items, true ), 0 );

		// For link outputs, discard all of the items except the appropriate choice
		if ( $is_link ) {
			// Remove the style template from the $list
			$list = ''; 
			$link_type = $item_default_values['archive_output'];
			$is_wrap = in_array( $item_default_values['archive_qualifier'], array( 'wrap', 'always_wrap' ) );
			
			$target_value = $item_default_values[ $archive_parameter_name ];
			$target_key = NULL;
			if ( !empty( $target_value ) ) {
				foreach ( $items as $key => $item ) {
//error_log( __LINE__ . " compose_archive_list( {$key} ) item = " . var_export( $item, true ), 0 );
					if ( $item->current_value === $target_value ) {
						$target_key = $key;
						break;
					}
				} // foreach $item
			}

			// Default to empty/option_none_label
			if ( NULL === $target_key ) {
				$target_key = -2;
			}
			
			switch ( $link_type ) {
				case 'paginate_archive':
					return self::_paginate_links( $item_default_values, $target_key, $list );
				case 'previous_archive':
					$target_key = $target_key - 1;
					break;
				case 'next_archive':
					$target_key = $target_key + 1;
					break;
				case 'current_archive':
				default:
					// no change
			} // link_type

			$item = NULL;
			if ( isset( $items[ $target_key ] ) ) {
				$item = $items[ $target_key ];
			} elseif ( $is_wrap && ( !empty( $target_value ) || ( 'always_wrap' === $item_default_values['archive_qualifier'] ) ) ) {
				switch ( $link_type ) {
					case 'previous_archive':
						$item = array_pop( $items );
						break;
					case 'next_archive':
						$item = array_shift( $items );
				} // link_type
			} // is_wrap

			if ( !empty( $item ) ) {
				$items = array( $item );
			} elseif ( ! empty( $item_default_values['option_none_label'] ) ) {
				$list = self::_process_shortcode_parameter( $item_default_values['option_none_label'], $item_default_values );
				return false;
			} else {
				return false;
			}
		}// $is_link
		
		foreach ( $items as $key => $item ) {
//error_log( __LINE__ . " compose_archive_list( {$key} ) item = " . var_export( $item, true ), 0 );
			// fill in item-specific elements
			$item_values = array_merge( $item_default_values, (array) $item );
//error_log( __LINE__ . " compose_archive_list( {$key} ) item_values = " . var_export( $item_values, true ), 0 );

/*			$month_stamp = 0;
			if ( !empty( $item_values['month'] ) ) {
				$item_values['m'] = sprintf( '%1$04d%2$02d', (integer) $item_values['year'], (integer) $item_values['month'] );
				
				if ( !empty( $item_values['day'] ) ) {
					$item_values['yyyymmdd'] = sprintf( '%1$04d-%2$02d-%3$02d', (integer) $item_values['year'], (integer) $item_values['month'], (integer) $item_values['day'] );
					$month_stamp = strtotime( $item_values['yyyymmdd'] );
				} else {
					$month_stamp = strtotime( $item_values['m'] . '01' );
				}
			} elseif ( !empty( $item_values['week_start_short'] ) ) {
				$month_stamp = strtotime( $item_values['week_start_short'] );
			}
			
			if ( $month_stamp ) {
				$item_values['month_long'] = date( 'F', $month_stamp );
				$item_values['month_short'] = date( 'M', $month_stamp );
			}

			// Compute the current_value and current_labels based on the archive type
			switch ( $item_values['archive_type'] ) {
				case 'daily':
					$item_values['current_value'] = sprintf( 'D(%1$04d%2$02d%3$02d)', (integer) $item_values['year'], (integer) $item_values['month'], (integer) $item_values['day'] );
					$item_values['current_label_short'] = sprintf( '%1$04d/%2$02d/%3$02d', (integer) $item_values['year'], (integer) $item_values['month'], (integer) $item_values['day'] );
					$item_values['current_label_long'] = sprintf( '%1$s %2$02d, %3$04d', $item_values['month_long'], (integer) $item_values['day'], (integer) $item_values['year'] );
					$item_values['viewlink_url'] = get_day_link( (integer) $item_values['year'], (integer) $item_values['month'], (integer) $item_values['day'] );
					break;
				case 'weekly':
					$item_values['current_value'] = sprintf( 'W(%1$04d%2$02d)', (integer) $item_values['year'], (integer) $item_values['week'] );
					$item_values['current_label_short'] = $item_values['week_start_short'];
					$item_values['current_label_long'] = $item_values['week_start'];
					$item_values['viewlink_url'] = add_query_arg(
						array(
							'm' => $item_values['year'],
							'w' => $item_values['week'],
						),
						home_url( '/' )
					);
					break;
				case 'monthly':
					$item_values['current_value'] = sprintf( 'M(%1$04d%2$02d)', (integer) $item_values['year'], (integer) $item_values['month'] );
					$item_values['current_label_short'] = sprintf( '%1$s %2$s', $item_values['month_short'], $item_values['year'] );
					$item_values['current_label_long'] = sprintf( '%1$s %2$s', $item_values['month_long'], $item_values['year'] );
					$item_values['viewlink_url'] = get_month_link( (integer) $item_values['year'], (integer) $item_values['month'] );
					break;
				case 'yearly':
				default:
					$item_values['current_value'] = sprintf( 'Y(%1$04d)', (integer) $item_values['year'] );
					$item_values['current_label_short'] = sprintf( '%1$04d', (integer) $item_values['year'] );
					$item_values['current_label_long'] = sprintf( '%1$04d', (integer) $item_values['year'] );
					$item_values['viewlink_url'] = get_year_link( (integer) $item_values['year'] );
			}

			// Add the archive source to the current value
			$current_args = ',' . $item_values['current_value'];
			if ( 'custom' === $item_values['archive_source'] ) {
				$item_values['current_value'] = 'custom:' . $item_values['archive_key'] . $current_args;
			} else {
				$item_values['current_value'] = $item_values['archive_source'] . $current_args;
			}

			$item_values['current_label'] = ( 'short' === $item_values['archive_label'] ) ? $item_values['current_label_short'] : $item_values['current_label_long'];
			$item_values['currentlink_url'] = sprintf( '%1$s%2$s%3$s=%4$s', $item_values['page_url'], $current_item_delimiter, $archive_parameter_name, urlencode( $item_values['current_value'] ) ); // */

			if ( !empty( $item_default_values['itemtag_id'] ) ) {
				$item_values['itemtag_id'] = esc_attr( $item_default_values['itemtag_id'] );
			} else {
				$item_values['itemtag_id'] = esc_attr( $item_values['listtag_id'] . '-' . $item_values['current_value'] );
			}

//error_log( __LINE__ . " compose_archive_list() item_values['current_value'] = " . var_export( $item_values['current_value'], true ), 0 );
			if ( !empty( $item_values[ $archive_parameter_name ] ) ) {
				if ( $item_values['current_value'] === $item_values[ $archive_parameter_name ] ) {
					$has_active = true;
					$item_values['item_selected'] = 'selected=selected';
				}
			}

			// Add item_specific field-level substitution parameters
			$new_text = isset( $item_template ) ? $item_template : '';
			foreach( self::$archive_list_item_specific_defaults as $index => $value ) {
				$new_text .= str_replace( '{+', '[+', str_replace( '+}', '+]', $item_values[ $index ] ) );
			}
//error_log( __LINE__ . " compose_archive_list() new_text = " . var_export( $new_text, true ), 0 );

			$item_values = MLAData::mla_expand_field_level_parameters( $new_text, $attr, $item_values );

			// Apply the Display Content parameters
			$attributes = array();
			
			if ( !empty( $item_values['itemtag_id'] ) ) {
				$item_values['item_id'] = self::_process_shortcode_parameter( $item_values['itemtag_id'], $item_values );
				$attributes[] = 'id="' . $item_values['item_id'] . '"';
			}

			if ( !empty( $item_values['itemtag_class'] ) ) {
				$item_values['item_class'] = self::_process_shortcode_parameter( $item_values['itemtag_class'], $item_values );
				if ( !empty( $item_values['item_selected'] ) ) {
					$item_values['item_class'] .= ' ' . $item_values['current_archive_class'];
				}
				
				$attributes[] = 'class="' . $item_values['item_class'] . '"';
			}

			if ( !empty( $item_values['itemtag_attributes'] ) ) {
				$attributes[] = self::_process_shortcode_parameter( $item_values['itemtag_attributes'], $item_values );
			}

			if ( !empty( $attributes ) ) {
				$item_values['item_attributes'] = implode( ' ', $attributes ) . ' ';
			}

			if ( !empty( $item_values['itemtag_value'] ) ) {
				$item_values['item_value'] = self::_process_shortcode_parameter( $item_values['itemtag_value'], $item_values );
			} else {
				$item_values['item_value'] = $item_values['current_value'];
			}

			if ( !empty( $item_values['itemtag_label'] ) ) {
				$item_values['item_label'] = self::_process_shortcode_parameter( $item_values['itemtag_label'], $item_values );
			} else {
				$item_values['item_label'] = $item_values['current_label'];
			}

			// Build the link components
			$attributes = array();

			if ( !empty( $item_values['link_id'] ) ) {
				$item_values['item_link_id'] = self::_process_shortcode_parameter( $item_values['link_id'], $item_values );
				$attributes[] = 'id="' . $item_values['item_link_id'] . '"';
			}

			if ( !empty( $item_values['link_class'] ) ) {
				$item_values['item_link_class'] = self::_process_shortcode_parameter( $item_values['link_class'], $item_values );
				$attributes[] = 'class="' . $item_values['item_link_class'] . '"';
			}

			if ( !empty( $item_values['rollover_text'] ) ) {
				$item_values['item_link_rollover'] = self::_process_shortcode_parameter( $item_values['rollover_text'], $item_values );
				$attributes[] = 'title="' . $item_values['item_link_rollover'] . '"';
			}

			if ( !empty( $item_values['link_attributes'] ) ) {
				$attributes[] = self::_process_shortcode_parameter( $item_values['link_attributes'], $item_values );
			}

			if ( !empty( $attributes ) ) {
				$item_values['item_link_attributes'] = implode( ' ', $attributes ) . ' ';
			}

			if ( !empty( $item_values['link_text'] ) ) {
				$item_values['item_link_text'] = esc_attr( self::_process_shortcode_parameter( $item_values['link_text'], $item_values ) );
			} else {
				$item_values['item_link_text'] = $item_values['current_label'];
			}

			if ( !empty( $item_values['show_count'] ) && ( 'true' === strtolower( $item_values['show_count'] ) ) ) {
				// Ignore option-all
				if ( -1 !== $item_values['items'] ) {
					$item_values['item_label'] .= ' (' . $item_values['items'] . ')';
					$item_values['item_link_text'] .= ' (' . $item_values['items'] . ')';
				}
			}

			if ( !empty( $item_values['link_href'] ) ) {
				$link_href = self::_process_shortcode_parameter( $item_values['link_href'], $item_values );
				$item_values['link_url'] = $link_href;
			} else {
				$link_href = '';
			}

			// currentlink, viewlink and thelink
			$item_values['currentlink'] = sprintf( '<a %1$shref="%2$s">%3$s</a>', $item_values['item_link_attributes'], $item_values['currentlink_url'], $item_values['item_link_text'] );
			$item_values['viewlink'] = sprintf( '<a %1$shref="%2$s">%3$s</a>', $item_values['item_link_attributes'], $item_values['viewlink_url'], $item_values['item_link_text'] );

			if ( !empty( $item_values['link_url'] ) ) {
				$item_values['thelink'] = sprintf( '<a %1$shref="%2$s">%3$s</a>', $item_values['item_link_attributes'], $item_values['link_url'], $item_values['item_link_text'] );
			} elseif ( 'current' === $item_values['link'] ) {
				$item_values['link_url'] = $item_values['currentlink_url'];
				$item_values['thelink'] = $item_values['currentlink'];
			} elseif ( 'view' === $item_values['link'] ) {
				$item_values['thelink'] = $item_values['viewlink'];
			} elseif ( 'span' === $item_values['link'] ) {
				$item_values['thelink'] = sprintf( '<span %1$s>%2$s</span>', $item_values['item_link_attributes'], $item_values['item_link_text'] );
			} else {
				$item_values['thelink'] = $item_values['link_text'];
			}

			// Page content has already been logged and cached
			$item_values['page_content'] = '';
			//MLACore::mla_debug_add( __LINE__ . " compose_archive_list() item_values = " . var_export( $item_values, true ), self::$muie_debug_category );
			$item_values['page_content'] = $page_content;

			if ( $is_list || $is_dropdown ) {
				$list .= MLAData::mla_parse_template( $item_template, $item_values );
			} else {
				$links[] = $item_values['thelink'];
			} 
		} // foreach item

		if ( $is_list || $is_dropdown ) {
			$list .= MLAData::mla_parse_template( $close_template, $markup_values );
		} else {
			switch ( $markup_values['archive_output'] ) {
			case 'array' :
				$list =& $links;
				break;
			case 'flat' :
			default :
				$list .= join( $markup_values['separator'], $links );
				break;
			} // switch format
		}

//error_log( __LINE__ . " compose_archive_list() links = " . var_export( $links, true ), 0 );
		if ( 'true' === $markup_values['muie_debug'] ) {
			MLACore::mla_debug_add( __LINE__ . " compose_archive_list() list = " . var_export( esc_html( $list ), true ), self::$muie_debug_category );
		} else {
			MLACore::mla_debug_add( __LINE__ . " compose_archive_list() list = " . var_export( $list, true ), self::$muie_debug_category );
		}
		
		return $has_active;
	} // compose_archive_list

	/**
	 * Explicit Shortcode Attributes for muie_archive_list
	 *
	 * @since 1.14
	 *
	 * @var	array
	 */
	private static $archive_list_attr = NULL;

	/**
	 * Shortcode Arguments for muie_archive_list
	 *
	 * @since 1.14
	 *
	 * @var	array
	 */
	private static $archive_list_arguments = NULL;

	/**
	 * Query results for muie_archive_list
	 *
	 * @since 1.14
	 *
	 * @var	array

  yearly => 
  (object) array(
     'year' => '2019',
     'items' => '2',
  ),
  
  monthly => 
  (object) array(
     'year' => '2019',
     'month' => '9',
     'items' => '2',
  ),
  
  weekly => 
  (object) array(
     'year' => '2019',
     'week' => '38',
     'yyyymmdd' => '2019-09-21',
     'items' => '2',
     'week_start_raw' => 1568592000,
     'week_start_short' => '2019-09-16',
     'week_start' => 'September 16, 2019',
     'week_end_raw' => 1569196799,
     'week_end_short' => '2019-09-22',
     'week_end' => 'September 22, 2019',
  ),

  daily => 
  (object) array(
     'year' => '2019',
     'month' => '9',
     'day' => '21',
     'items' => '2',
  ),

	 */
	private static $archive_list_items = NULL;

	/**
	 * Filters all query clauses at once, for convenience.
	 *
	 * For use by caching plugins.
	 *
	 * Covers the WHERE, GROUP BY, JOIN, ORDER BY, DISTINCT,
	 * fields (SELECT), and LIMITS clauses.
	 *
	 * @since 1.14
	 *
	 * @param string[] $pieces Associative array of the pieces of the query.
	 * @param WP_Query $wp_query   The WP_Query instance (passed by reference).
	 */
	public static function muie_archive_posts_clauses_request( $pieces, $wp_query ) {
		global $wpdb;

		MLACore::mla_debug_add( __LINE__ . " muie_archive_posts_clauses_request() pieces = " . var_export( $pieces, true ), self::$muie_debug_category );

		$where    = isset( $pieces['where'] ) ? $pieces['where'] : '';
		$join     = isset( $pieces['join'] ) ? $pieces['join'] : '';

		// exif:DateTimeOriginal  YYYY:MM:DD HH:MM:SS
		// iptc:DateCreated YYYYMMDD
		// xmp:CreateDate YYYY-MM-DD HH:MM:SS  two digit month and day, 24-hour clock

		// These will come from shortcode attributes
		if ( 'custom' === self::$archive_list_arguments['archive_source'] ) {
			$key = self::$archive_list_arguments['archive_key'];
			$field = $wpdb->postmeta . '.meta_value';
			$join .= " INNER JOIN {$wpdb->postmeta} ON ( $wpdb->posts.ID = {$wpdb->postmeta}.post_id )";
			$where .= " AND ( {$wpdb->postmeta}.meta_key = '{$key}' )";
		} else {
			$field = self::$archive_list_arguments['archive_source'];
		}
		
		$order = self::$archive_list_arguments['archive_order'];
		
		if ( self::$archive_list_arguments['archive_limit'] ) {
			$limit = 'LIMIT ' . (string) self::$archive_list_arguments['archive_limit'];
		} else {
			$limit = '';
		}

		// Initialize clauses that vary by archive type
		switch ( self::$archive_list_arguments['archive_type'] ) {
			case 'daily':
				$main_select = "sq.year AS `year`, sq.month AS `month`, sq.day AS `day`";
				$main_group_by = "sq.year, sq.month, sq.day";
				$main_order_by = "sq.year {$order}, sq.month {$order}, sq.day {$order}";
				$sq_select = "YEAR({$field}) AS `year`, MONTH({$field}) AS `month`, DAYOFMONTH({$field}) AS `day`";
				$sq_group_by = "YEAR({$field}), MONTH({$field}), DAYOFMONTH({$field})";
				$sq_order_by = "{$field} {$order}";
				break;
			case 'weekly':
				$week = _wp_mysql_week( "{$field}" );
				$main_select = "DISTINCT sq.year AS `year`, sq.week as `week`, sq.yyyymmdd AS `yyyymmdd`";
				$main_group_by = "sq.year, sq.week";
				$main_order_by = "sq.year {$order}, sq.week {$order}";
				$sq_select = "DISTINCT YEAR({$field}) AS `year`, {$week} as `week`, DATE_FORMAT( {$field}, '%Y-%m-%d' ) AS `yyyymmdd`";
				$sq_group_by = "YEAR({$field}), {$week}";
				$sq_order_by = "{$field} {$order}";
				break;
			case 'monthly':
				$main_select = "sq.year AS `year`, sq.month AS `month`";
				$main_group_by = "sq.year, sq.month";
				$main_order_by = "sq.year {$order}, sq.month {$order}";
				$sq_select = "YEAR({$field}) AS `year`, MONTH({$field}) AS `month`";
				$sq_group_by = "YEAR({$field}), MONTH({$field})";
				$sq_order_by = "{$field} {$order}";
				break;
			case 'yearly':
			default:
				$main_select = "sq.year AS `year`";
				$main_group_by = "sq.year";
				$main_order_by = "sq.year {$order}";
				$sq_select = "YEAR({$field}) AS `year`";
				$sq_group_by = "YEAR({$field})";
				$sq_order_by = "{$field} {$order}";
		}
		
//		$query   = "SELECT {$sq_select}, ID FROM $wpdb->posts {$join} WHERE 1=1 {$where} GROUP BY {$sq_group_by}, ID ORDER BY {$sq_order_by}, ID {$order}";
//error_log( __LINE__ . " muie_archive_posts_clauses_request() query = " . var_export( $query, true ), 0 );
//		self::$archive_list_items = $wpdb->get_results( $query );
//error_log( __LINE__ . " muie_archive_posts_clauses_request() archive_list_items = " . var_export( self::$archive_list_items, true ), 0 );

		$query   = "SELECT {$main_select}, count(sq.ID) as items FROM ( SELECT {$sq_select}, ID FROM $wpdb->posts {$join} WHERE 1=1 {$where} GROUP BY {$sq_group_by}, ID ORDER BY {$sq_order_by}) as sq GROUP BY {$main_group_by} ORDER BY {$main_order_by} {$limit}";
		MLACore::mla_debug_add( __LINE__ . " muie_archive_posts_clauses_request() query = " . var_export( $query, true ), self::$muie_debug_category );

//		$key     = md5( $query );
//		$key     = "wp_get_archives:$key:$last_changed";
//		$results = wp_cache_get( $key, 'posts' );
//		if ( ! $results ) {
			self::$archive_list_items = $wpdb->get_results( $query );
			
			if ( 'weekly' === self::$archive_list_arguments['archive_type'] ) {
				foreach( self::$archive_list_items as $index => $item ) {
					$arc_week = get_weekstartend( $item->yyyymmdd, get_option( 'start_of_week' ) );
					self::$archive_list_items[ $index ]->week_start_raw = $arc_week['start'];
					self::$archive_list_items[ $index ]->week_start_short = date_i18n( 'Y-m-d', $arc_week['start'] );
					self::$archive_list_items[ $index ]->week_start = date_i18n( get_option( 'date_format' ), $arc_week['start'] );
					self::$archive_list_items[ $index ]->week_end_raw = $arc_week['end'];
					self::$archive_list_items[ $index ]->week_end_short = date_i18n( 'Y-m-d', $arc_week['end'] );
					self::$archive_list_items[ $index ]->week_end = date_i18n( get_option( 'date_format' ), $arc_week['end'] );
				}
			}
		MLACore::mla_debug_add( __LINE__ . " muie_archive_posts_clauses_request() archive_list_items = " . var_export( self::$archive_list_items, true ), self::$muie_debug_category );
//		}

		return $pieces;
	}

	/**
	 * Filters the posts array before the query takes place.
	 *
	 * Return a non-null value to bypass WordPress' default post queries.
	 *
	 * Filtering functions that require pagination information are encouraged to set
	 * the `found_posts` and `max_num_pages` properties of the WP_Query object,
	 * passed to the filter by reference. If WP_Query does not perform a database
	 * query, it will not have enough information to generate these values itself.
	 *
	 * @since 4.6.0
	 *
	 * @param array|null $posts Return an array of post data to short-circuit WP's query,
	 *                          or null to allow WP to run its normal queries.
	 * @param WP_Query   $wp_query  The WP_Query instance (passed by reference).
	 */
	public static function muie_archive_posts_pre_query( $posts, $wp_query ) {
		return array( 0 );
	}

	/**
	 * The MLA Term List shortcode.
	 *
	 * This is an interface to the muie_archive_list function.
	 *
	 * @since 1.14
	 *
	 * @param array $attr Attributes of the shortcode.
	 * @param string $content Optional content for enclosing shortcodes
	 *
	 * @return string HTML content to display the archive list.
	 */
	public static function muie_archive_list_shortcode( $attr, $content = NULL ) {
		MLACore::mla_debug_add( __LINE__ . " muie_archive_list_shortcode() _REQUEST = " . var_export( $_REQUEST, true ), self::$muie_debug_category );
		MLACore::mla_debug_add( __LINE__ . " muie_archive_list_shortcode() raw attr = " . var_export( $attr, true ), self::$muie_debug_category );
		MLACore::mla_debug_add( __LINE__ . " muie_archive_list_shortcode() content = " . var_export( $content, true ), self::$muie_debug_category );

		/*
		 * Make sure $attr is an array, even if it's empty,
		 * and repair damage caused by link-breaks in the source text
		 */
		$attr = MLAShortcodes::mla_validate_attributes( $attr, $content );

		// The 'array' format makes no sense in a shortcode
		if ( isset( $attr['archive_output'] ) && 'array' === $attr['archive_output'] ) {
			$attr['archive_output'] = 'dropdown';
		}

		// The current_archive parameter can be changed to support multiple lists per page
		if ( isset( $attr['archive_parameter_name'] ) ) {
			$archive_parameter_name = $attr['archive_parameter_name'];
		} else {
			$archive_parameter_name = 'muie_current_archive';
		}

		// Pagination links, e.g. Previous or Next, have muie_filters that encode the form parameters
		if ( !empty( $_REQUEST['muie_filters'] ) ) {
			$filters = json_decode( trim( stripslashes( $_REQUEST['muie_filters'] ), '"' ), true );

			if ( !empty( $filters[ $archive_parameter_name ] ) ) {
				$attr[ $archive_parameter_name ] = $filters[ $archive_parameter_name ];
			}
		}

		if ( empty( $attr[ $archive_parameter_name ] ) && !empty( $_REQUEST[ $archive_parameter_name ] ) ) {
			$attr[ $archive_parameter_name ] = $_REQUEST[ $archive_parameter_name ];
		}
	
		// A shortcode must return its content to the caller, so "echo" makes no sense
		$attr['echo'] = false;

		MLACore::mla_debug_add( __LINE__ . " muie_archive_list_shortcode() validated attr = " . var_export( $attr, true ), self::$muie_debug_category );
		return self::muie_archive_list( $attr );
	}

	/**
	 * Turn [muie_archive_list] debug collection and display on or off
	 *
	 * @since 1.14
	 *
	 * @var	mixed	MLA_DEBUG_CATEGORY for normal operation, NULL for unconditional logging, false to suppress logging
	 */
	private static $muie_debug_category = self::MLA_DEBUG_CATEGORY;

	/**
	 * Default values when global $post is not set
	 *
	 * @since 1.14
	 *
	 * @var	array
	 */
	private static $empty_post = array( 
				'ID' => 0,
				'post_author' => 0,
				'post_date' => '0000-00-00 00:00:00',
				'post_date_gmt' => '0000-00-00 00:00:00',
				'post_content' => '',
				'post_title' => '',
				'post_excerpt' => '',
				'post_status' => 'publish',
				'comment_status' => 'open',
				'ping_status' => 'open',
				'post_name' => '',
				'to_ping' => 'None',
				'pinged' => 'None',
				'post_modified' => '0000-00-00 00:00:00',
				'post_modified_gmt' => '0000-00-00 00:00:00',
				'post_content_filtered' => 'None',
				'post_parent' => 0,
				'guid' => '',
				'menu_order' => 0,
				'post_type' => 'post',
				'post_mime_type' => '',
				'comment_count' => 0,
			);

	/**
	 * Style and Markup templates
	 *
	 * @since 1.14
	 *
	 * @var	array
	 */
	private static $muie_archive_templates = NULL;

	/**
	 * These are the default parameters for srchive list display
	 *
	 * @since 1.14
	 *
	 * @var	array
	 */
	private static $archive_list_item_specific_defaults = array(
		'itemtag_id' => '',
		'itemtag_class' => 'muie-archive-list-item',
		'itemtag_attributes' => '',
		'itemtag_value' => '',
		'itemtag_label' => '',
//		'itemtag_default_value' => '',
//		'itemtag_default_label' => '',

		'separator' => "\n",
		'link' => 'current',
		'link_id' => '',
		'link_class' => '',
		'rollover_text' => '',
		'link_attributes' => '',
		'link_href' => '',
		'link_text' => '',
	);

	/**
	 * Archive list support function
	 *
	 * This function generates a 'daily', 'weekly', 'monthly', or 'yearly' dropdown control, list or array
	 *
	 * @since 1.14
	 *
	 * @param	array	$attr the shortcode parameters
	 *
	 * @return	mixed	HTML markup (or array) for the generated control/list
	 */
	public static function muie_archive_list( $attr ) {
		global $wpdb, $post;
//error_log( __LINE__ . " muie_archive_list() attr = " . var_export( $attr, true ), 0 );

		// Make sure $attr is an array, even if it's empty
		if ( is_string( $attr ) ) {
			$attr = shortcode_parse_atts( $attr );
		}

		if ( empty( $attr ) ) {
			$attr = array();
		}

		// Save the validated arguments for processing in posts_clauses_request, compose_archive_list
		self::$archive_list_attr = $attr;

		// Some do_shortcode callers may not have a specific post in mind
		if ( ! is_object( $post ) ) {
			$post = (object) self::$empty_post;
		}

		// $instance supports multiple lists in one page/post	
		static $instance = 0;
		$instance++;

		// Some values are already known, and can be used in data selection parameters
		$upload_dir = wp_upload_dir();
		$page_values = array(
			'instance' => $instance,
			'selector' => "muie_archive_list-{$instance}",
			'site_url' => site_url(),
			'base_url' => $upload_dir['baseurl'],
			'base_dir' => $upload_dir['basedir'],
			'id' => $post->ID,
			'page_ID' => $post->ID,
			'page_author' => $post->post_author,
			'page_date' => $post->post_date,
			'page_content' => $post->post_content,
			'page_title' => $post->post_title,
			'page_excerpt' => $post->post_excerpt,
			'page_status' => $post->post_status,
			'page_name' => $post->post_name,
			'page_modified' => $post->post_modified,
			'page_guid' => $post->guid,
			'page_type' => $post->post_type,
			'page_url' => get_page_link(),
		);

		$default_arguments = array_merge(
			array(
			'archive_type' => 'yearly',
			'archive_source' => 'post_date',
			'archive_key' => '', // for source = custom
			'archive_output' => 'dropdown',
			'archive_qualifier' => '',

			'archive_parameter_name' => 'muie_current_archive',
			'archive_order' => 'DESC',
			'archive_limit' => '0',
			'archive_label' => '', // 'short', 'long'
			'show_count' => 'true',
			'hide_if_empty' => 'false',

			'listtag' => '',
			'listtag_name' => 'muie_current_archive',
			'listtag_id' => $page_values['selector'],
			'listtag_class' => 'muie-archive-list',
			'listtag_attributes' => '',
			'itemtag' => '',
			'current_archive_class' => 'muie-current-archive',
			
			'option_all_value' => '',
			'option_all_label' => '',
			'option_none_value' => '',
			'option_none_label' => '',

			'end_size'=> 1,
			'mid_size' => 1,
			'prev_text' => '&laquo; ' . __( 'Previous', 'media-library-assistant' ),
			'next_text' => __( 'Next', 'media-library-assistant' ) . ' &raquo;',

			'muie_debug' => '',
			'echo' => 'false',
			),
			self::$archive_list_item_specific_defaults
		);

		// Accept only the attributes we need, supply defaults and validate
		$arguments = shortcode_atts( $default_arguments, $attr );

		// Separate output type from qualifier
		$value = array_map( 'strtolower', array_map( 'trim', explode( ',', $arguments['archive_output'] ) ) );
		$qualifier = isset( $value[1] ) ? $value[1] : '';
		if ( in_array( $qualifier, array( 'wrap', 'always_wrap', 'show_all', 'prev_next' ) ) ) {
			$arguments['archive_qualifier'] = $qualifier;
		} else {
			$arguments['archive_qualifier'] = '';
		}
			
		$value = $value[0];
		if ( in_array( $value, array( 'dropdown', 'list', 'flat', 'array', 'next_archive', 'current_archive', 'previous_archive', 'paginate_archive', ) ) ) {
			$arguments['archive_output'] = $value;
			$attr['archive_output'] = $value; // Fix for array_diff_assoc() below
		} else {
			$arguments['archive_output'] = 'dropdown';
			$arguments['archive_qualifier'] = '';
		}

		if ( empty( $arguments['archive_label'] ) ) {
			if ( 'paginate_archive' === $arguments['archive_output'] ) {
				$arguments['archive_label'] = 'short';
			} else {
				$arguments['archive_label'] = 'long';
			}
		} else {
			$value = trim( strtolower( $arguments['archive_label'] ) );
			if ( 'short' === $value ) {
				$arguments['archive_label'] = $value;
			} else {
				$arguments['archive_label'] = 'long';
			}
		}

		if ( empty( $arguments['listtag'] ) ) {
			if ( 'list' === $value ) {
				$arguments['listtag'] = 'ul';
			} else {
				$arguments['listtag'] = 'select';
			}
		}
		
		if ( empty( $arguments['itemtag'] ) ) {
			if ( 'list' === $value ) {
				$arguments['itemtag'] = 'li';
			} else {
				$arguments['itemtag'] = 'option';
			}
		}
		
		$value = trim( strtolower( $arguments['hide_if_empty'] ) );
		$arguments['hide_if_empty'] = 'true' === $value;
		
		$value = trim( strtolower( $arguments['link'] ) );
		if ( in_array( $value, array( 'current', 'view', 'span', 'none', ) ) ) {
			$arguments['link'] = $value;
		} else {
			$arguments['link'] = 'current';
		}

		// muie_debug controls output from this shortcode
		$old_debug_category = self::$muie_debug_category;
		$old_debug_mode = MLACore::mla_debug_mode();
		
		$value = trim( strtolower( $arguments['muie_debug'] ) );
		if ( in_array( $value, array( 'false', 'log', 'true', ) ) ) {
			self::$muie_debug_category = NULL; // Unconditional logging
			
			if ( 'true' === $value ) {
				MLACore::mla_debug_mode( 'buffer' );
			} elseif ( 'log' === $value ) {
				MLACore::mla_debug_mode( 'log' );
			} else {
				self::$muie_debug_category = $old_debug_category;
			}

			$arguments['muie_debug'] = $value;
		} else {
			$arguments['muie_debug'] = '';
		}

		$value = trim( strtolower( $arguments['echo'] ) );
		$arguments['echo'] = 'true' === $value;
		
		$value = trim( strtolower( $arguments['archive_type'] ) );
		if ( in_array( $value, array( 'daily', 'weekly', 'monthly', 'yearly', ) ) ) {
			$arguments['archive_type'] = $value;
		} else {
			$arguments['archive_type'] = 'yearly';
		}

		$value = trim( strtolower( $arguments['archive_source'] ) );
		if ( in_array( $value, array( 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt', 'custom', ) ) ) {
			$arguments['archive_source'] = $value;
		} else {
			$arguments['archive_source'] = 'post_date';
		}

		if ( 'custom' === $value ) {
			$value = trim( $arguments['archive_key'] );
			if ( !empty( $value ) ) {
				$arguments['archive_key'] = $value;
			} else {
				$arguments['archive_key'] = '';
				$arguments['archive_source'] = 'post_date';
			}
		} else {
			$arguments['archive_key'] = '';
		}

		$value = trim( strtoupper( $arguments['archive_order'] ) );
		if ( in_array( $value, array( 'ASC', 'DESC', ) ) ) {
			$arguments['archive_order'] = $value;
		} else {
			$arguments['archive_order'] = 'DESC';
		}

		$arguments['archive_limit'] = absint( $arguments['archive_limit'] );

		// The current_archive parameter can be changed to support multiple lists per page
		if ( ! isset( $attr['archive_parameter_name'] ) ) {
			$attr['archive_parameter_name'] = $default_arguments['archive_parameter_name'];
		}

		// The archive_parameter_name can contain page_level parameters like {+page_ID+}
		$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr['archive_parameter_name'] ) );
		$archive_parameter_name = MLAData::mla_parse_template( $attr_value, $page_values );
		 
		/*
		 * Special handling of archive_parameter_name to make multiple lists per page easier.
		 * Look for this parameter in $_REQUEST if it's not present in the shortcode itself.
		 */
		if ( ! isset( $attr[ $archive_parameter_name ] ) ) {
			if ( isset( $_REQUEST[ $archive_parameter_name ] ) ) {
				$attr[ $archive_parameter_name ] = sanitize_text_field( wp_unslash( $_REQUEST[ $archive_parameter_name ] ) );
			}
		}
		 
		/*
		 * $archive_parameter_name, if non-default, doesn't make it through the shortcode_atts
		 * filter, so we handle it separately
		 */
		if ( isset( $attr[ $archive_parameter_name ] ) ) {
			$arguments[ $archive_parameter_name ] = $attr[ $archive_parameter_name ];
		} else {
			$arguments[ $archive_parameter_name ] = '';
		}

		$arguments['archive_parameter_name'] = $archive_parameter_name;

		// Save the validated arguments for processing in posts_clauses_request, compose_archive_list
		self::$archive_list_arguments = $arguments;
		MLACore::mla_debug_add( __LINE__ . " muie_archive_list() _REQUEST = " . var_export( $_REQUEST, true ), self::$muie_debug_category );
		MLACore::mla_debug_add( __LINE__ . " muie_archive_list() self::\$archive_list_attr = " . var_export( self::$archive_list_attr, true ), self::$muie_debug_category );
		MLACore::mla_debug_add( __LINE__ . " muie_archive_list() self::\$archive_list_arguments = " . var_export( self::$archive_list_arguments, true ), self::$muie_debug_category );

		$other_arguments = array_diff_assoc( $attr, self::$archive_list_arguments );
		MLACore::mla_debug_add( __LINE__ . " muie_archive_list() other_arguments = " . var_export( $other_arguments, true ), self::$muie_debug_category );

		// The other arguments can contain page_level parameters like {+page_ID+}, request: or query: parameters
		$markup_values = $page_values;
		foreach ( $other_arguments as $key => $value ) {
			$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $value ) );
			$markup_values = MLAData::mla_expand_field_level_parameters( $attr_value, $other_arguments, $markup_values );
			$value = MLAData::mla_parse_array_template( $attr_value, $markup_values, 'array' );
			if ( empty( $value ) ) {
				unset( $other_arguments[ $key ] );
			} else {
				$other_arguments[ $key ] = $value;
			}
		}
 
 		if ( empty( $other_arguments['post_parent'] ) ) {
			$other_arguments['post_parent'] = 'all';
		}
		
		$shortcode_arguments = array_merge( $other_arguments, array(
			'no_found_rows' => true,
			'fields' => 'ids',
			'cache_results' => false,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		) );
		MLACore::mla_debug_add( __LINE__ . " muie_archive_list() shortcode_arguments = " . var_export( $shortcode_arguments, true ), self::$muie_debug_category );

		// posts_clauses_request will perform the actual query, and posts_pre_query will short-circuit WP_Query
		add_filter( 'posts_clauses_request', 'MLAUIElementsExample::muie_archive_posts_clauses_request', 10, 2 );
		add_filter( 'posts_pre_query', 'MLAUIElementsExample::muie_archive_posts_pre_query', 10, 2 );


		// Some do_shortcode callers may not have a specific post in mind
		$ID = is_object( $post ) ? $post->ID : 0;

		if ( NULL === self::$muie_debug_category ) {
			$mla_debug = ( ! empty( $attr['mla_debug'] ) ) ? ( 'true' === trim( strtolower( $attr['mla_debug'] ) ) ) : false;
		} else {
			$mla_debug = false;
		}
		
		$attachments = MLAShortcodes::mla_get_shortcode_attachments( $ID, $shortcode_arguments, false, $mla_debug );
//error_log( __LINE__ . " muie_archive_list() attachments = " . var_export( $attachments, true ), 0 );

		remove_filter( 'posts_clauses_request', 'MLAUIElementsExample::muie_archive_posts_clauses_request', 10 );
		remove_filter( 'posts_pre_query', 'MLAUIElementsExample::muie_archive_posts_pre_query', 10 );

		$list_values = array_merge( $page_values, $arguments );
//error_log( __LINE__ . " muie_archive_list() list_values = " . var_export( $list_values, true ), 0 );

		// Expand list-level parameters
		$list_values['listtag_name'] = self::_process_shortcode_parameter( $list_values['listtag_name'], $list_values );
		$list_values['listtag_id'] = self::_process_shortcode_parameter( $list_values['listtag_id'], $list_values );
		$list_values['listtag_class'] = self::_process_shortcode_parameter( $list_values['listtag_class'], $list_values );
		$list_values['listtag_attributes'] = self::_process_shortcode_parameter( $list_values['listtag_attributes'], $list_values );

		// Load template array and initialize page-level values.
		if ( empty( self::$muie_archive_templates ) ) {
			self::$muie_archive_templates = MLACore::mla_load_template( dirname( __FILE__ ) . '/mla-ui-custom-templates.tpl' , 'path' );
//error_log( __LINE__ . " muie_archive_list() muie_archive_templates = " . var_export( self::$muie_archive_templates, true ), 0 );
		}
		
		$list_values = MLAData::mla_expand_field_level_parameters( self::$muie_archive_templates['muie-archive-list-style'], $attr, $list_values );
//error_log( __LINE__ . " muie_archive_list() list_values = " . var_export( $list_values, true ), 0 );
		$list = MLAData::mla_parse_template( self::$muie_archive_templates['muie-archive-list-style'], $list_values );
//error_log( __LINE__ . " muie_archive_list() list = " . var_export( $list, true ), 0 );
		$links = array();
		self::compose_archive_list( $list, $links, $list_values );
//error_log( __LINE__ . " muie_archive_list() list = " . var_export( $list, true ), 0 );

		if ( 'true' === $arguments['muie_debug'] ) {
			$output = MLACore::mla_debug_flush();
		} else {
			$output = '';
		}

		// restore debug settings
		self::$muie_debug_category = $old_debug_category;
		MLACore::mla_debug_mode( $old_debug_mode );
		return $output . $list;
	} // muie_archive_list
} // Class MLAUIElementsExample

// Install the filters at an early opportunity
add_action('init', 'MLAUIElementsExample::initialize');
?>
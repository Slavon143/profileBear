<?php
/**
 * Adds "product:" and "product_terms:" custom substitution prefixes and
 * adds a Tools/Woo Fixit submenu with buttons to perform a variety of
 * MLA/WooCommerce repair and enhancement operations.
 *
 * This example supports several "tools"/operations:
 * 
 *  - Delete ALL Title fields for Product Image/Product Gallery items.
 *  - Fill empty item Title field with re-formatted file name.
 *  - Replace ALL item Title fields with re-formatted file name.
 *  - Replace ALL item Name/Slug fields with (unique) file name.
 *
 *  - Delete ALL ALT Text fields for Product Image/Product Gallery items.
 *  - Fill empty ALT Text field with first top-level Product Category.
 *  - Replace ALL ALT Text field with first top-level Product Category.
 *  - Fill empty ALT Text field with Product Title.
 *  - Replace ALL ALT Text field with Product Title.
 *  - Fill empty item Title field with Product Title.
 *  - Replace ALL item Title field with Product Title.
 *
 *  - Remove Product/Featured Image from the Product Gallery.
 *  - Restore Product/Featured Image to the Product Gallery.
 *  - Reverse the image order in the Product Gallery.
 *  - Replace "where_used" information in custom field "Woo Used In".
 *
 *  - Delete ALL Product Tags assignments where a Product Image exists.
 *  - Fill empty Product Tags assignments from Product Image Att. Tags
 *    where a Product Image exists. 
 *  - Append Product Tags assignments to ALL Products from Product Image
 *    Att. Tags where a Product Image exists.  
 *  - Replace ALL Product Tags assignments from Product Image Att. Tags
 *    where a Product Image exists.
 *
 *  - Delete ALL Products' Product Categories assignments where a Product Image exists.
 *  - Fill empty Product Categories assignments from Product Image Att. Tags,
 *    where the Product Image Att. Tag matches an existing Product Category. 
 *  - Append Product Categories assignments to ALL Products from Product Image
 *    Att. Tags, where the Product Image Att. Tag matches an existing Product Category.
 *  - Replace ALL Product Categories assignments from Product Image Att. Tags,
 *    where the Product Image Att. Tag matches an existing Product Category. 
 * 
 *  - Delete ALL Att. Categories assignments. 
 *  - Fill empty Att. Categories assignments from Att. Tags, where the
 *    Att. Tag matches an existing Att. Category. 
 *  - Append Att. Categories assignments to ALL items from Att. Tags,
 *    where the Att. Tag matches an existing Att. Category. 
 *  - Replace ALL Att. Categories assignments from Att. Tags, where the
 *    Att. Tag matches an existing Att. Category.
 *
 *  - Delete product_category and/or product_tag term assignments to Media Library items.
 *  - Copy product_category and/or product_tag term assignments to Media Library items
 *    for items used as Product Image or in the Product Gallery
 *
 *  - Populate Product values from Content Template(s) using Product Image data sources
 *    Name, Description, Short Description, Categories, SKU
 *
 * Created for support topic "Remove first image in all product galleries"
 * opened on 5/23/2014 by "Dana S".
 * https://wordpress.org/support/topic/remove-first-image-in-all-product-galleries/
 *
 * and for support topic "set the product category as alt and title tag for all images"
 * opened on 5/23/2014 by "Dana S".
 * https://wordpress.org/support/topic/set-the-product-category-as-alt-and-title-tag-for-all-images/
 *
 * Enhanced for support topic "Woocommerce product category"
 * opened on 9/17/2015 by "vnp_nl".
 * https://wordpress.org/support/topic/woocommerce-product-category-2/
 *
 * Enhanced for support topic "Bulk addition of image alt tags to WooCommerce Product Images"
 * opened on 11/18/2015 by "Thrive Internet Marketing".
 * https://wordpress.org/support/topic/bulk-addition-of-image-alt-tags-to-woocommerce-product-images/
 *
 * Enhanced for support topic "Regenerate Bulk ALT TEXT with Product Name + Product Category + Keyword"
 * opened on 2/21/2017 by "bueyfx".
 * https://wordpress.org/support/topic/regenerate-bulk-alt-text-with-product-name-product-category-keyword/
 *
 * Enhanced for support topic "WC Fixit Tools: Replace ALL item Name/Slug"
 * opened on 10/18/2018 by "alx359".
 * https://wordpress.org/support/topic/wc-fixit-tools-replace-all-item-name-slug/
 *
 * Enhanced for support topic "Product categories thumbnails"
 * opened on 9/18/2019 by "wimvl".
 * https://wordpress.org/support/topic/product-categories-thumbnails-2/
 *
 * Enhanced for support topic "Populating WooCommerce Fields when Uploading Media"
 * opened on 11/26/2019 by "mrphil".
 * https://wordpress.org/support/topic/populating-woocommerce-fields-when-uploading-media/
 *
 * Enhanced for support topic "Image keywords (tags) into a product tags"
 * opened on 1/19/2020 by "kuassar".
 * https://wordpress.org/support/topic/image-keywords-tags-into-a-product-tags/
 *
 * Enhanced for support topic "Adding images to product"
 * opened on 2/25/2021 by "fireskyresale".
 * https://wordpress.org/support/topic/adding-images-to-product/
 *
 * @package WooCommerce Fixit
 * @version 2.10
 */

/*
Plugin Name: WooCommerce Fixit
Plugin URI: http://davidlingren.com/
Description: Adds "product:" and "product_terms:" custom substitution prefixes and adds a Tools/Woo Fixit submenu with buttons to perform a variety of MLA/WooCommerce repair and enhancement operations.
Author: David Lingren
Version: 2.10
Author URI: http://davidlingren.com/

Copyright 2014-2021 David Lingren

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
 * Class Woo Fixit implements a Tools submenu page with several image-fixing tools.
 *
 * @package WooCommerce Fixit
 * @since 1.00
 */
class Woo_Fixit {
	/**
	 * Current version number
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const CURRENT_VERSION = '2.10';

	/**
	 * Slug prefix for registering and enqueueing submenu pages, style sheets and scripts
	 *
	 * @since 1.00
	 *
	 * @var	string
	 */
	const SLUG_PREFIX = 'woofixit-';

	/**
	 * Lowest product ID to be processed
	 *
	 * @since 1.26
	 *
	 * @var	string
	 */
	private static $first_product = '';
	const INPUT_FIRST_PRODUCT = 'lower';

	/**
	 * Highest product ID to be processed
	 *
	 * @since 1.26
	 *
	 * @var	string
	 */
	private static $last_product = '';
	const INPUT_LAST_PRODUCT = 'upper';

	/**
	 * Append Item ID to make file name slug unique
	 *
	 * @since 2.04
	 *
	 * @var	boolean
	 */
	private static $append_item_id = false;
	const APPEND_ITEM_ID = 'append-item-id';

	/**
	 * Append Item ID checkbox attribute
	 *
	 * @since 2.06
	 *
	 * @var	string
	 */
	private static $append_item_id_attr = ' ';

	/**
	 * Use WordPress unique slug function
	 *
	 * @since 2.04
	 *
	 * @var	boolean
	 */
	private static $check_unique_slug = false;
	const CHECK_UNIQUE_SLUG = 'check-unique-slug';

	/**
	 * Use WordPress unique slug checkbox attribute
	 *
	 * @since 2.06
	 *
	 * @var	string
	 */
	private static $check_unique_slug_attr = ' ';

	/**
	 * Add Product Image to Product Gallery
	 *
	 * @since 2.10
	 *
	 * @var	boolean
	 */
	private static $add_image_to_gallery = false;
	const ADD_IMAGE_TO_GALLERY = 'add-image-to-gallery';

	/**
	 * Append Item ID checkbox attribute
	 *
	 * @since 2.10
	 *
	 * @var	string
	 */
	private static $add_image_to_gallery_attr = ' ';

	/**
	 * Use WordPress unique slug function
	 *
	 * @since 2.10
	 *
	 * @var	boolean
	 */
	private static $delete_no_children = false;
	const DELETE_NO_CHILDREN = 'delete-no-children';

	/**
	 * Use WordPress unique slug checkbox attribute
	 *
	 * @since 2.10
	 *
	 * @var	string
	 */
	private static $delete_no_children_attr = ' ';

	/**
	 * Populate Product Image and Gallery with children from MLA Bulk Edit on Upload
	 *
	 * @since 2.10
	 *
	 * @var	boolean
	 */
	const POPULATE_PI_PG_ON_UPLOAD = 'populate-pi-pg-on-upload';
	const DEFAULT_POPULATE_PI_PG_ON_UPLOAD = false;

	/**
	 * Append Item ID checkbox attribute
	 *
	 * @since 2.10
	 *
	 * @var	string
	 */
	private static $populate_pi_pg_on_upload_attr = ' ';

	/**
	 * Populate Product Image and Gallery with children from WP MMMW
	 *
	 * @since 2.10
	 *
	 * @var	boolean
	 */
	const POPULATE_PI_PG_ON_MMMW = 'populate-pi-pg-on-mmmw';
	const DEFAULT_POPULATE_PI_PG_ON_MMMW = false;

	/**
	 * Append Item ID checkbox attribute
	 *
	 * @since 2.10
	 *
	 * @var	string
	 */
	private static $populate_pi_pg_on_mmmw_attr = ' ';

	/**
	 * Content Template for Product Image/Product Gallery Images
	 *
	 * @since 1.28
	 *
	 * @var	string
	 */
	private static $content_template = '';
	const INPUT_CONTENT_TEMPLATE = 'template';

	/**
	 * Process term assignments for Product Category
	 *
	 * @since 1.26
	 *
	 * @var	boolean
	 */
	private static $process_category = false;
	const INPUT_PROCESS_CATEGORY = 'category';

	/**
	 * Process term assignments for Product Category checkbox attribute
	 *
	 * @since 2.06
	 *
	 * @var	string
	 */
	private static $category_attr = ' ';

	/**
	 * Process term assignments for Product Tag
	 *
	 * @since 1.26
	 *
	 * @var	boolean
	 */
	private static $process_tag = false;
	const INPUT_PROCESS_TAG = 'tag';

	/**
	 * Process term assignments for Product Tag checkbox attribute
	 *
	 * @since 2.06
	 *
	 * @var	string
	 */
	private static $tag_attr = ' ';

	/**
	 * Chunk (offset) to start term assignments at
	 *
	 * @since 1.26
	 *
	 * @var	integer
	 */
	private static $start_chunk = 1;
	const INPUT_FIRST_CHUNK = 'first';

	/**
	 * Chunk (offset) to stop term assignments at
	 *
	 * @since 1.26
	 *
	 * @var	integer
	 */
	private static $stop_chunk = 999;
	const INPUT_LAST_CHUNK = 'last';

	/**
	 * Chunk size (limit) for term assignment processing
	 *
	 * @since 1.26
	 *
	 * @var	integer
	 */
	private static $chunk_size = 1000;
	const INPUT_CHUNK_SIZE = 'size';

	/**
	 * Product Name (post_title) Template for Populate Product from Product Image
	 *
	 * @since 2.06
	 *
	 * @var	string
	 */
	const NAME_TEMPLATE = 'name-template';
	const DEFAULT_NAME_TEMPLATE = '[+alt_text+]';

	/**
	 * Product Description (post_content)  Template for Populate Product from Product Image
	 *
	 * @since 2.06
	 *
	 * @var	string
	 */
	const DESCRIPTION_TEMPLATE = 'description-template';
	const DEFAULT_DESCRIPTION_TEMPLATE = '[+post_content+]';

	/**
	 * Product Short Description (post_excerpt) Template for Populate Product from Product Image
	 *
	 * @since 2.06
	 *
	 * @var	string
	 */
	const SHORT_DESCRIPTION_TEMPLATE = 'short-description-template';
	const DEFAULT_SHORT_DESCRIPTION_TEMPLATE = '[+post_content+]';

	/**
	 * Product Categories ( taxonomy product_cat ) Template for Populate Product from Product Image
	 *
	 * @since 2.06
	 *
	 * @var	string
	 */
	const CATEGORIES_TEMPLATE = 'categories-template';
	const DEFAULT_CATEGORIES_TEMPLATE = '[+terms:attachment_category+]';

	/**
	 * Product Tags ( taxonomy product_tag ) Template for Populate Product from Product Image
	 *
	 * @since 2.09
	 *
	 * @var	string
	 */
	const TAGS_TEMPLATE = 'tags-template';
	const DEFAULT_TAGS_TEMPLATE = '';

	/**
	 * Product SKU ( postmeta _sku ) Template for Populate Product from Product Image
	 *
	 * @since 2.06
	 *
	 * @var	string
	 */
	const SKU_TEMPLATE = 'sku-template';
	const DEFAULT_SKU_TEMPLATE = '[+name_only+]';

	/**
	 * Populate when Product Image is set
	 *
	 * @since 2.06
	 *
	 * @var	boolean
	 */
	const POPULATE_ON_ADD = 'populate-on-add';
	const DEFAULT_POPULATE_ON_ADD = false;

	/**
	 * Append Item ID checkbox attribute
	 *
	 * @since 2.06
	 *
	 * @var	string
	 */
	private static $populate_on_add_attr = ' ';

	/**
	 * Populate when Product Image is changed
	 *
	 * @since 2.06
	 *
	 * @var	boolean
	 */
	const POPULATE_ON_UPDATE = 'populate-on-update';
	const DEFAULT_POPULATE_ON_UPDATE = false;

	/**
	 * Append Item ID checkbox attribute
	 *
	 * @since 2.06
	 *
	 * @var	string
	 */
	private static $populate_on_update_attr = ' ';

	/**
	 * Processing options
	 *
	 * This array specifies the components used in the pretty links and whether
	 * the mla_debug=log argument is appended to the links
	 *
	 * @since 2.06
	 *
	 * @var	array
	 */
	private static $settings = array ();

	/**
	 * Default processing options
	 *
	 * @since 2.06
	 *
	 * @var	array
	 */
	private static $default_settings = array (
						self::POPULATE_PI_PG_ON_UPLOAD => self::DEFAULT_POPULATE_PI_PG_ON_UPLOAD,
						self::POPULATE_PI_PG_ON_MMMW => self::DEFAULT_POPULATE_PI_PG_ON_MMMW,
						self::NAME_TEMPLATE => self::DEFAULT_NAME_TEMPLATE,
						self::DESCRIPTION_TEMPLATE => self::DEFAULT_DESCRIPTION_TEMPLATE,
						self::SHORT_DESCRIPTION_TEMPLATE => self::DEFAULT_SHORT_DESCRIPTION_TEMPLATE,
						self::CATEGORIES_TEMPLATE => self::DEFAULT_CATEGORIES_TEMPLATE,
						self::TAGS_TEMPLATE => self::DEFAULT_TAGS_TEMPLATE,
						self::SKU_TEMPLATE => self::DEFAULT_SKU_TEMPLATE,
						self::POPULATE_ON_ADD => self::DEFAULT_POPULATE_ON_ADD,
						self::POPULATE_ON_UPDATE => self::DEFAULT_POPULATE_ON_UPDATE,
					);

	/**
	 * Update the plugin options from the wp_options table or set defaults
	 *
	 * @since 2.06
	 */
	private static function _load_settings() {
		$settings = get_option( self::SLUG_PREFIX . 'settings' );
		if ( is_array( $settings ) ) {
			self::$settings = $settings;
			// Adapt settings added in version 2.09
			if ( !isset( self::$settings[self::TAGS_TEMPLATE] ) ) {
				self::$settings[self::TAGS_TEMPLATE] = self::$default_settings[self::TAGS_TEMPLATE];
			}

			// Adapt settings added in version 2.10
			if ( !isset( self::$settings[self::POPULATE_PI_PG_ON_UPLOAD] ) ) {
				self::$settings[self::POPULATE_PI_PG_ON_UPLOAD] = self::$default_settings[self::POPULATE_PI_PG_ON_UPLOAD];
			}

			if ( !isset( self::$settings[self::POPULATE_PI_PG_ON_MMMW] ) ) {
				self::$settings[self::POPULATE_PI_PG_ON_MMMW] = self::$default_settings[self::POPULATE_PI_PG_ON_MMMW];
			}

			self::$populate_pi_pg_on_upload_attr = self::$settings[self::POPULATE_PI_PG_ON_UPLOAD] ? ' checked="checked" ' : ' ';
			self::$populate_pi_pg_on_mmmw_attr = self::$settings[self::POPULATE_PI_PG_ON_MMMW] ? ' checked="checked" ' : ' ';
			self::$populate_on_add_attr = self::$settings[self::POPULATE_ON_ADD] ? ' checked="checked" ' : ' ';
			self::$populate_on_update_attr = self::$settings[self::POPULATE_ON_UPDATE] ? ' checked="checked" ' : ' ';

			return 'Settings loaded from database.';
		} else {
			self::$settings = self::$default_settings;
			self::$populate_pi_pg_on_upload_attr = self::$settings[self::POPULATE_PI_PG_ON_UPLOAD] ? ' checked="checked" ' : ' ';
			self::$populate_pi_pg_on_mmmw_attr = self::$settings[self::POPULATE_PI_PG_ON_MMMW] ? ' checked="checked" ' : ' ';
			self::$populate_on_add_attr = self::$settings[self::POPULATE_ON_ADD] ? ' checked="checked" ' : ' ';
			self::$populate_on_update_attr = self::$settings[self::POPULATE_ON_UPDATE] ? ' checked="checked" ' : ' ';

			return 'No settings in database; defaults loaded.';
		}
	}

	/**
	 * Save settings as a WordPress wp_options entry
	 *
	 * @since 2.06
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _save_setting_changes() {
		$new_settings = self::$settings;

		// Load old settings from the database or defaults
		self::_load_product_templates();

		$new_settings[self::POPULATE_PI_PG_ON_UPLOAD] = isset( $_REQUEST[ self::SLUG_PREFIX . self::POPULATE_PI_PG_ON_UPLOAD ] ) ? true : false;
		$new_settings[self::POPULATE_PI_PG_ON_MMMW] = isset( $_REQUEST[ self::SLUG_PREFIX . self::POPULATE_PI_PG_ON_MMMW ] ) ? true : false;

		$new_settings[self::NAME_TEMPLATE] = trim( stripslashes( $_REQUEST[ self::SLUG_PREFIX . self::NAME_TEMPLATE ] ) );
		$new_settings[self::DESCRIPTION_TEMPLATE] = trim( stripslashes( $_REQUEST[ self::SLUG_PREFIX . self::DESCRIPTION_TEMPLATE ] ) );
		$new_settings[self::SHORT_DESCRIPTION_TEMPLATE] = trim( stripslashes( $_REQUEST[ self::SLUG_PREFIX . self::SHORT_DESCRIPTION_TEMPLATE ] ) );
		$new_settings[self::CATEGORIES_TEMPLATE] = trim( stripslashes( $_REQUEST[ self::SLUG_PREFIX . self::CATEGORIES_TEMPLATE ] ) );
		$new_settings[self::TAGS_TEMPLATE] = trim( stripslashes( $_REQUEST[ self::SLUG_PREFIX . self::TAGS_TEMPLATE ] ) );
		$new_settings[self::SKU_TEMPLATE] = trim( stripslashes( $_REQUEST[ self::SLUG_PREFIX . self::SKU_TEMPLATE ] ) );
		$new_settings[self::POPULATE_ON_ADD] = isset( $_REQUEST[ self::SLUG_PREFIX . self::POPULATE_ON_ADD ] ) ? true : false;
		$new_settings[self::POPULATE_ON_UPDATE] = isset( $_REQUEST[ self::SLUG_PREFIX . self::POPULATE_ON_UPDATE ] ) ? true : false;

		if ( $new_settings === self::$settings ) {
			return "Settings unchanged.\n";
		}

		$success = update_option( self::SLUG_PREFIX . 'settings', $new_settings, false );
		if ( $success )  {
			self::$settings = $new_settings;
			self::$populate_pi_pg_on_upload_attr = self::$settings[self::POPULATE_PI_PG_ON_UPLOAD] ? ' checked="checked" ' : ' ';
			self::$populate_pi_pg_on_mmmw_attr = self::$settings[self::POPULATE_PI_PG_ON_MMMW] ? ' checked="checked" ' : ' ';
			self::$populate_on_add_attr = self::$settings[self::POPULATE_ON_ADD] ? ' checked="checked" ' : ' ';
			self::$populate_on_update_attr = self::$settings[self::POPULATE_ON_UPDATE] ? ' checked="checked" ' : ' ';
			return "Settings have been updated.\n";
		}

		return "Settings update failed.\n";
	} // _save_setting_changes

	/**
	 * Delete WordPress wp_options entry
	 *
	 * @since 1.00
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _delete_settings() {
		delete_option( self::SLUG_PREFIX . 'settings' ); 
		self::$settings = self::$default_settings;

		return "Settings removed from database and reset to default values.\n";
	} // _delete_settings

	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		// Defined in /media-library-assistant/includes/class-mla-data.php
		add_filter( 'mla_expand_custom_prefix', 'Woo_Fixit::mla_expand_custom_prefix', 10, 8 );

		// The other filters are only useful in the admin section; exit if in front-end posts/pages
		if ( !is_admin() )
			return;

		self::_load_product_templates();
//error_log( __LINE__ . " Woo_Fixit::initialize settings = " . var_export( self::$settings, true ), 0 );
//error_log( __LINE__ . " Woo_Fixit::initialize populate_pi_pg_on_upload_attr = " . var_export( self::$populate_pi_pg_on_upload_attr, true ), 0 );
//error_log( __LINE__ . " Woo_Fixit::initialize populate_pi_pg_on_mmmw_attr = " . var_export( self::$populate_pi_pg_on_mmmw_attr, true ), 0 );
//error_log( __LINE__ . " Woo_Fixit::initialize populate_on_add_attr = " . var_export( self::$populate_on_add_attr, true ), 0 );
//error_log( __LINE__ . " Woo_Fixit::initialize populate_on_update_attr = " . var_export( self::$populate_on_update_attr, true ), 0 );

		if ( self::$settings[self::POPULATE_PI_PG_ON_UPLOAD] || self::$settings[self::POPULATE_PI_PG_ON_MMMW] ) {
			// Defined in class-mla-options.php
			add_action( 'add_attachment', 'Woo_Fixit::add_attachment', 10, 1 );
		}

		if ( self::$settings[self::POPULATE_ON_ADD] ) {
			// Defined in /wp-includes/meta.php
			add_action( 'added_post_meta', 'Woo_Fixit::added_post_meta', 10, 4 );
		}

		if ( self::$settings[self::POPULATE_ON_UPDATE] ) {
			// Defined in /wp-includes/meta.php
			add_action( 'updated_postmeta', 'Woo_Fixit::updated_postmeta', 10, 4 );
		}

		add_action( 'admin_menu', 'Woo_Fixit::admin_menu' );
	}

	/**
	 * Evaluate product_terms: values
	 *
	 * @since 2.00
	 *
	 * @param mixed String or array - initial value
	 * @param mixed Integer or array; the Post ID(s) of the product(s)
	 * @param string Taxonomy slug
	 * @param string Field name in term object
	 * @param string Format/option; text,single,export,unpack,array
	 *
	 * @return mixed String or array; values or error messages
	 */
	private static function _evaluate_terms( $custom_value, $products, $taxonomy, $qualifier, $option ) {
		if ( empty( $products ) ) {
			return $custom_value;
		}

		if ( is_scalar( $products ) ) {
			$products = array( absint( $products ) => absint( $products ) );
		}

		if ( empty( $qualifier ) ) {
			$qualifier = 'name';
		}

		$all_terms = array();
		foreach ( $products as $product ) {
			$terms = get_object_term_cache( $product, $taxonomy );
			if ( false === $terms ) {
				$terms = wp_get_object_terms( $product, $taxonomy );
				if ( is_wp_error( $terms ) ) {
					return implode( ',', $terms->get_error_messages() );
				}
//error_log( __LINE__ . " Woo_Fixit::_evaluate_terms( {$product}, {$taxonomy}, {$qualifier}, {$option} ) terms = " . var_export( $terms, true ), 0 );

				wp_cache_add( $product, $terms, $taxonomy . '_relationships' );
			}

			$all_terms = array_merge( $all_terms, $terms );
		}
//error_log( __LINE__ . " Woo_Fixit::_evaluate_terms( {$product}, {$taxonomy}, {$qualifier}, {$option} ) all_terms = " . var_export( $all_terms, true ), 0 );

		if ( 'array' == $option ) {
			$custom_value = array();
		} else {
			$custom_value = '';
		}

		if ( ! empty( $all_terms ) ) {
			if ( 'single' == $option || 1 == count( $all_terms ) ) {
				reset( $all_terms );
				$term = current( $all_terms );
				$fields = get_object_vars( $term );
				$custom_value = isset( $fields[ $qualifier ] ) ? $fields[ $qualifier ] : $fields['name'];
				$custom_value = sanitize_term_field( $qualifier, $custom_value, $term->term_id, $taxonomy, 'display' );
			} elseif ( ( 'export' == $option ) || ( 'unpack' == $option ) ) {
				$custom_value = sanitize_text_field( var_export( $all_terms, true ) );
			} else {
				foreach ( $all_terms as $term ) {
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

//error_log( __LINE__ . " Woo_Fixit::_evaluate_terms( {$product}, {$taxonomy}, {$qualifier}, {$option} ) custom_value = " . var_export( $custom_value, true ), 0 );
		return $custom_value;
	} // _evaluate_terms

	/**
	 * Add the "product:" and "product_terms:" custom substitution prefixes.
	 * For any Media Library item that is used as a Product Image or Product Gallery item,
	 * these will return values from the associated Product.
	 *
	 * @since 2.00
	 *
	 * @param	string	NULL, indicating that by default, no custom value is available
	 * @param	string	the data-source name 
	 * @param	array	data-source components; prefix (empty), value, option, format and args (if present)
	 * @param	array	values from the query, if any, e.g. shortcode parameters
	 * @param	array	item-level markup template values, if any
	 * @param	integer	attachment ID for attachment-specific values
	 * @param	boolean	for option 'multi', retain existing values
	 * @param	string	default option value
	 */
	public static function mla_expand_custom_prefix( $custom_value, $key, $value, $query, $markup_values, $post_id, $keep_existing, $default_option ) {
		static $product_cache = array();

		//error_log( __LINE__ . " Woo_Fixit::mla_expand_custom_prefix( {$key}, {$post_id}, {$keep_existing}, {$default_option} ) value = " . var_export( $value, true ), 0 );
		//error_log( __LINE__ . " Woo_Fixit::mla_expand_custom_prefix( {$key}, {$post_id} ) query = " . var_export( $query, true ), 0 );
		//error_log( __LINE__ . " Woo_Fixit::mla_expand_custom_prefix( {$key}, {$post_id} ) markup_values = " . var_export( $markup_values, true ), 0 );

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

		// Find all the attachments associated with products
		if ( empty( self::$attachment_products ) ) {
			self::_build_product_attachments( true );
		}

		// What product(s) are associated with this item?
		$products = array();
		if ( isset( self::$attachment_products[ $post_id ] ) ) {
			if ( isset( self::$attachment_products[ $post_id ]['_thumbnail_id'] ) ) {
				foreach ( self::$attachment_products[ $post_id ]['_thumbnail_id'] as $product ) {
					$products[ $product ] = $product;
				}
			}

			if ( !empty( self::$attachment_products[ $post_id ]['_product_image_gallery'] ) ) {
				foreach ( self::$attachment_products[ $post_id ]['_product_image_gallery'] as $product ) {
					$products[ $product ] = $product;
				}
			}
		} else {
			return $custom_value;
		}
//error_log( __LINE__ . " Woo_Fixit::mla_expand_custom_prefix( {$key}, {$post_id} ) products = " . var_export( $products, true ), 0 );

		if ( 'product_terms' == $value['prefix'] ) {
			$custom_value = self::_evaluate_terms( $custom_value, $products, $field, $qualifier, $value['option'] );
		} elseif ( 'product' == $value['prefix'] ) {
			$custom_value = array();
			foreach ( $products as $product_id ) {
				if ( isset( $product_cache[ $product_id ] ) ) {
					$product = $product_cache[ $product_id ];
				} else {
					$product = get_post( $product_id );

					if ( $product instanceof WP_Post && $product->ID == $product_id ) {
						$product_cache[ $product_id ] = $product;
					} else {
						continue;
					}
				}

				if ( property_exists( $product, $value['value'] ) ) {
					$custom_value[] = $product->{$value['value']};
				} elseif ( 'permalink' == $value['value'] ) {
					$custom_value[] = get_permalink( $product );
				} else {
					// Look for a custom field match
					$meta_value = get_metadata( 'post', $product_id, $value['value'], false );
//error_log( __LINE__ . " Woo_Fixit::mla_expand_custom_prefix( {$key}, {$post_id}, {$product_id} ) meta_value = " . var_export( $meta_value, true ), 0 );
					if ( !empty( $meta_value ) ) {
						if ( is_array( $meta_value ) ) {
							$custom_value = array_merge( $custom_value, $meta_value );
						} else {
							$custom_value[] = $meta_value;
						}
					}
				}
			}
//error_log( __LINE__ . " Woo_Fixit::mla_expand_custom_prefix( {$key}, {$post_id} ) custom_value = " . var_export( $custom_value, true ), 0 );

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
							$new_value .= strlen( $new_value ) ? ', ' . $field_value : $field_value;
						}
					} // foreach element

					$custom_value = $new_value;
				}
			}
		} // prefix = product:

		return $custom_value;
	} // mla_expand_custom_prefix

	/**
	 * Attachment ID passed from add_attachment_action to mla_list_table_end_bulk_action
	 *
	 * Ensures that product population is only performed when the attachment is first
	 * added to the Media Library.
	 *
	 * @since 2.10
	 *
	 * @var	integer
	 */
	private static $add_attachment_id = 0;

	/**
	 * After a new attachment is added, arm the filters to populate Priduct Image and Gallery.
	 *
	 * @since 2.10
	 *
	 * @param int    $object_id  Post ID.
	 */
	public static function add_attachment( $object_id ) {
//error_log( __LINE__ . " Woo_Fixit::add_attachment( $object_id )", 0 );
		self::$add_attachment_id = $object_id;

		// If MLA's Bulk Edit on Upload isn't present, use the WP filters instead
		if ( empty( $_REQUEST['mlaAddNewBulkEditFormString'] ) ) {
			if ( self::$settings[self::POPULATE_PI_PG_ON_MMMW] ) {
				add_filter( 'wp_generate_attachment_metadata', 'Woo_Fixit::generate_attachment_metadata', 0x7FFFFFFF, 2 );
			}
		} else {
			if ( self::$settings[self::POPULATE_PI_PG_ON_UPLOAD] ) {
				add_filter( 'mla_list_table_end_bulk_action', 'Woo_Fixit::mla_list_table_end_bulk_action', 10, 2 );
			}
		}
	}

	/**
	 * This filter tests the $add_attachment_id variable set by the add_attachment_action
	 * to ensure that population is only performed once, after the generation of all intermediate sizes is complete.
	 *
	 * The filter is applied by function wp_generate_attachment_metadata() in /wp-includes/image.php
	 * This function is called only Bulk Edit on Upload is not in process.
	 *
	 * @since 2.96
	 *
	 * @param	array	Attachment metadata for just-inserted attachment
	 * @param	integer	ID of just-inserted attachment
	 *
	 * @return	array	Updated attachment metadata
	 */
	public static function generate_attachment_metadata( $data, $post_id ) {
		$add_attachment_id = self::$add_attachment_id;
//error_log( __LINE__ . " Woo_Fixit::generate_attachment_metadata( {$post_id}, {$add_attachment_id} ) \$data = " . var_export( $data, true ), 0 );
		if ( $add_attachment_id === $post_id ) {
			add_filter( 'wp_update_attachment_metadata', 'Woo_Fixit::update_attachment_metadata', 0x7FFFFFFF, 2 );
			remove_filter( 'wp_generate_attachment_metadata', 'Woo_Fixit::generate_attachment_metadata', 0x7FFFFFFF );
		}

//error_log( __LINE__ . " Woo_Fixit::generate_attachment_metadata( {$post_id}, {$add_attachment_id} )", 0 );
		return $data;
 	} // generate_attachment_metadata

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
	public static function update_attachment_metadata( $data, $post_id ) {
		$add_attachment_id = self::$add_attachment_id;
//error_log( __LINE__ . " Woo_Fixit::update_attachment_metadata( {$post_id}, {$add_attachment_id} ) \$data = " . var_export( $data, true ), 0 );
		if ( $add_attachment_id === $post_id ) {
			// Only do this once per attachment
			self::mla_list_table_end_bulk_action( NULL, 'edit' );

			self::$add_attachment_id = 0;
			remove_filter( 'wp_update_attachment_metadata', 'Woo_Fixit::update_attachment_metadata', 0x7FFFFFFF );
		}

//error_log( __LINE__ . " Woo_Fixit::update_attachment_metadata( {$post_id}, {$add_attachment_id} )", 0 );
		return $data;
 	} // mla_generate_attachment_metadata_filter

	/**
	 * After a new attachment is added, populate the Product Image and Product Gallery.
	 *
	 * @since 2.10
	 *
	 * @param string    $content      Default message and page content.
	 * @param string    $bulk_action  Should be "edit".
	 */
	public static function mla_list_table_end_bulk_action( $content, $bulk_action ) {
//error_log( __LINE__ . " Woo_Fixit::mla_list_table_end_bulk_action( $bulk_action )", 0 );

		if (  0 < self::$add_attachment_id ) {
			$attachment = get_post( self::$add_attachment_id );
//error_log( __LINE__ . " Woo_Fixit::mla_list_table_end_bulk_action( $bulk_action ) attachment = " . var_export( $attachment, true ), 0 );
			if ( isset( $attachment->post_parent ) && ( 0 < $attachment->post_parent ) ) {
				if ( 0 === strpos( $attachment->post_mime_type, 'image' ) ) {
					$parent = get_post( $attachment->post_parent );				}
//error_log( __LINE__ . " Woo_Fixit::mla_list_table_end_bulk_action( $bulk_action ) parent = " . var_export( $parent, true ), 0 );
					if ( isset( $parent->post_type ) && ( 'product' === $parent->post_type ) ) {
						$product_id = $parent->ID;
						$thumbnail = get_post_meta( $product_id, '_thumbnail_id', true );
						$gallery = get_post_meta( $product_id, '_product_image_gallery', true );
//error_log( __LINE__ . " Woo_Fixit::mla_list_table_end_bulk_action( $bulk_action ) thumbnail = " . var_export( $thumbnail, true ), 0 );
//error_log( __LINE__ . " Woo_Fixit::mla_list_table_end_bulk_action( $bulk_action ) gallery = " . var_export( $gallery, true ), 0 );

					// First new item becomes the thumbnail, if needed
					if ( empty( $thumbnail ) ) {
						$result = update_post_meta( $product_id, '_thumbnail_id', self::$add_attachment_id );
//error_log( __LINE__ . " Woo_Fixit::mla_list_table_end_bulk_action( $bulk_action ) _thumbnail_id result = " . var_export( $result, true ), 0 );
						$thumbnail = self::$add_attachment_id;
					}

					// Add the new item at the end of the gallery
					if ( ( self::$add_attachment_id !== $thumbnail ) || self::$add_image_to_gallery ) {
						$gallery .= empty( $gallery ) ? self::$add_attachment_id : ',' . self::$add_attachment_id;
						$result = update_post_meta( $product_id, '_product_image_gallery', $gallery );
//error_log( __LINE__ . " Woo_Fixit::mla_list_table_end_bulk_action( $bulk_action ) _product_image_gallery result = " . var_export( $result, true ), 0 );
					}

					} // parent is product
			} // attachment is image
		} // adding an attachment
		
		remove_filter( 'mla_list_table_end_bulk_action', 'Woo_Fixit::mla_list_table_end_bulk_action', 10 );
		return $content;
	}

	/**
	 * After adding a post's metadata, check for Product Image add.
	 *
	 * @since 2.06
	 *
	 * @param int    $meta_id    ID of updated metadata entry.
	 * @param int    $object_id  Post ID.
	 * @param string $meta_key   Meta key.
	 * @param mixed  $meta_value Meta value. This will be a PHP-serialized string representation of the value if
	 *                           the value is an array, an object, or itself a PHP-serialized string.
	 * @return	void
	 */
	public static function added_post_meta( $meta_id, $object_id, $meta_key, $meta_value ) {
		global $post;

//error_log( __LINE__ . " Woo_Fixit::added_post_meta( $meta_id, $object_id, $meta_key ) meta_value = " . var_export( $meta_value, true ), 0 );
		if ( '_thumbnail_id' === $meta_key ) {
//error_log( __LINE__ . " Woo_Fixit::added_post_meta( $meta_id, $object_id, $meta_key ) post = " . var_export( $post, true ), 0 );
			if ( !empty( $post->post_type ) && ( 'product' === $post->post_type ) ) {
				self::$first_product = self::$last_product = $object_id;
				$result = self::_populate_product_from_product_image();
//error_log( __LINE__ . " Woo_Fixit::added_post_meta( $meta_id, $object_id, $meta_key ) result = " . var_export( $result, true ), 0 );
			}
		}
	}

	/**
	 * After updating a post's metadata, check for Product Image add/update.
	 *
	 * @since 2.06
	 *
	 * @param int    $meta_id    ID of updated metadata entry.
	 * @param int    $object_id  Post ID.
	 * @param string $meta_key   Meta key.
	 * @param mixed  $meta_value Meta value. This will be a PHP-serialized string representation of the value if
	 *                           the value is an array, an object, or itself a PHP-serialized string.
	 * @return	void
	 */
	public static function updated_postmeta( $meta_id, $object_id, $meta_key, $meta_value ) {
		global $post;

//error_log( __LINE__ . " Woo_Fixit::updated_postmeta( $meta_id, $object_id, $meta_key ) meta_value = " . var_export( $meta_value, true ), 0 );
		if ( '_thumbnail_id' === $meta_key ) {
//error_log( __LINE__ . " Woo_Fixit::updated_postmeta( $meta_id, $object_id, $meta_key ) post = " . var_export( $post, true ), 0 );
			if ( !empty( $post->post_type ) && ( 'product' === $post->post_type ) ) {
				self::$first_product = self::$last_product = $object_id;
				$result = self::_populate_product_from_product_image();
//error_log( __LINE__ . " Woo_Fixit::updated_postmeta( $meta_id, $object_id, $meta_key ) result = " . var_export( $result, true ), 0 );
			}
		}
	}

	/**
	 * Add submenu page in the "Tools" section
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function admin_menu( ) {
		$current_page_hook = add_submenu_page( 'tools.php', 'WooCommerce Fixit Tools', 'Woo Fixit', 'manage_options', self::SLUG_PREFIX . 'tools', 'Woo_Fixit::render_tools_page' );
		add_filter( 'plugin_action_links', 'Woo_Fixit::add_plugin_links_filter', 10, 2 );
	}

	/**
	 * Add the "Tools" link to the Plugins section entry
	 *
	 * @since 1.00
	 *
	 * @param	array 	array of links for the Plugin, e.g., "Activate"
	 * @param	string 	Directory and name of the plugin Index file
	 *
	 * @return	array	Updated array of links for the Plugin
	 */
	public static function add_plugin_links_filter( $links, $file ) {
		if ( $file == 'woofixit.php' ) {
			$tools_link = sprintf( '<a href="%s">%s</a>', admin_url( 'tools.php?page=' . self::SLUG_PREFIX . 'tools' ), 'Tools' );
			array_unshift( $links, $tools_link );
		}

		return $links;
	}

	/**
	 * Compose the Settings Actions table, with the woofixit-acton handlers
	 *
	 * @since 2.06
	 *
	 * @return	array Actions, handlers, comments, input tables, etc.
	 */
	private static function _compose_settings_actions() {
		return array(
			'help' => array( 'handler' => '', 'comment' => 'Enter first/last Product ID values above to restrict tool application range. You can find ID values by hovering over the "Name" column in the WooCommerce/Products submenu table.' ),
			'warning' => array( 'handler' => '', 'comment' => '<strong>These tools make permanent updates to your database.</strong> Make a backup before you use the tools so you can restore your old values if you don&rsquo;t like the results.' ),

			'c0' => array( 'handler' => '', 'comment' => '<h3>Operations on ALL Media Library Images</h3>' ),
			'warning2' => array( 'handler' => '', 'comment' => '<strong>The tools in this section are not restricted</strong> by the First &amp; Last Product IDs above. They operate on <strong>ALL</strong> of the items in your Media Library.' ),
			'Clear Title' => array( 'handler' => '_clear_title',
				'comment' => '<strong>Delete ALL</strong> item Title fields.' ),
			'c1' => array( 'handler' => '', 'comment' => '<hr>' ),
			'Fill Title' => array( 'handler' => '_fill_title',
				'comment' => 'Fill empty item Title field with re-formatted file name.' ),
			'Replace Title' => array( 'handler' => '_replace_title',
				'comment' => '<strong>Replace ALL</strong> item Title fields with re-formatted file name.' ),
			'c1a' => array( 'handler' => '', 'comment' => '<hr>' ),
			't0301' => array( 'open' => '<table><tr>' ),
			't0302' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle"><input name="' . self::SLUG_PREFIX . self::APPEND_ITEM_ID . '" type="checkbox"' . self::$append_item_id_attr . 'value="' . self::APPEND_ITEM_ID . '"></td>' ),
			't0303' => array( 'continue' => '  <td style="text-align: left; padding-right: 5px" valign="middle">Append Item ID</td>' ),
			't0304' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle"><input name="' . self::SLUG_PREFIX . self::CHECK_UNIQUE_SLUG . '" type="checkbox"' . self::$check_unique_slug_attr . 'value="' . self::CHECK_UNIQUE_SLUG . '"></td>' ),
			't0305' => array( 'continue' => '  <td style="text-align: left; padding-right: 5px" valign="middle">Ensure Unique Slug</td>' ),
			't0306' => array( 'continue' => '  <td colspan=2 style="text-align: right; padding-right: 5px" valign="middle">&nbsp;</td>' ),
			't0307' => array( 'continue' => '</tr><tr>' ),
			't0308' => array( 'continue' => '<td>&nbsp;</td><td colspan="5">Check Append Item ID to add the ID to the slug value for uniqueness.<br>Check Ensure Unique Slug to activate WordPress slug validation (may be slow).</td>' ),
			't0309' => array( 'close' => '</tr></table>&nbsp;<br>' ),
			'Replace Name/Slug' => array( 'handler' => '_replace_slug',
				'comment' => '<strong>Replace ALL</strong> item Name/Slug fields with file name.' ),

			'c2' => array( 'handler' => '', 'comment' => '<h3>Operations on Product Image/Product Gallery Images</h3>' ),
			'Clear ALT Text' => array( 'handler' => '_clear_alt_text',
				'comment' => '<strong>Delete ALL</strong> ALT Text fields for Product Image/Product Gallery items.' ),
			'Clear Title' => array( 'handler' => '_clear_title',
				'comment' => '<strong>Delete ALL</strong> Title fields for Product Image/Product Gallery items.' ),
			'c3' => array( 'handler' => '', 'comment' => '<hr>' ),
			'Fill ALT Text (PC)' => array( 'handler' => '_fill_alt_text',
				'comment' => 'Fill empty ALT Text field with first top-level <strong>Product Category</strong>.' ),
			'Replace ALT Text (PC)' => array( 'handler' => '_replace_alt_text',
				'comment' => '<strong>Replace ALL</strong> ALT Text field with first top-level <strong>Product Category</strong>.' ),
			'c4' => array( 'handler' => '', 'comment' => '<hr>' ),
			'Fill ALT Text (T)' => array( 'handler' => '_fill_alt_text_t',
				'comment' => 'Fill empty ALT Text field with <strong>Product Title</strong>.' ),
			'Replace ALT Text (T)' => array( 'handler' => '_replace_alt_text_t',
				'comment' => '<strong>Replace ALL</strong> ALT Text field with <strong>Product Title</strong>.' ),
			'c4a' => array( 'handler' => '', 'comment' => '<hr>' ),
			'Fill Title (T)' => array( 'handler' => '_fill_title_t',
				'comment' => 'Fill empty item Title field with <strong>Product Title</strong>.' ),
			'Replace Title (T)' => array( 'handler' => '_replace_title_t',
				'comment' => '<strong>Replace ALL</strong> item Title field with <strong>Product Title</strong>.' ),

			'c4b' => array( 'handler' => '', 'comment' => '<h3>Apply Template to Product Image/Product Gallery Images</h3>' ),
			't0101' => array( 'open' => '<table><tr>' ),
			't0110' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">Template</td>' ),
			't0111' => array( 'continue' => '  <td style="text-align: left; padding-right: 20px">' ),
			't0112' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . self::INPUT_CONTENT_TEMPLATE . '" type="text" size="40" value="' . self::$content_template . '">' ),
			't0113' => array( 'continue' => '  </td>' ),
			't0122' => array( 'continue' => '</tr><tr>' ),
			't0123' => array( 'continue' => '<td>&nbsp;</td><td colspan="1">Enter a Content Template (without the "template:" prefix).</td>' ),
			't0124' => array( 'close' => '</tr></table>&nbsp;<br>' ),
			'Fill ALT Text (CT)' => array( 'handler' => '_fill_alt_text_ct',
				'comment' => 'Apply non-empty Content Template values to empty ALT Text fields.' ),
			'Replace ALT Text (CT)' => array( 'handler' => '_replace_alt_text_ct',
				'comment' => 'Apply non-empty Content Template values to <strong>ALL</strong> ALT Text fields.' ),

			'c5' => array( 'handler' => '', 'comment' => '<h3>Operations on the Product Image and Product Gallery</h3>' ),
			'Remove Feature' => array( 'handler' => '_remove_feature',
				'comment' => 'Remove Product/Featured Image from the Product Gallery.' ),
			'Restore Feature' => array( 'handler' => '_restore_feature',
				'comment' => 'Restore Product/Featured Image to the Product Gallery.' ),
			'Reverse Gallery' => array( 'handler' => '_reverse_gallery',
				'comment' => 'Reverse the image order in the Product Gallery.' ),
			'c5a' => array( 'handler' => '', 'comment' => '<hr>' ),
			'c5b' => array( 'handler' => '', 'comment' => 'Options for the &ldquo;... from Children&rdquo; tools:' ),
			't0401' => array( 'open' => '<table><tr>' ),
			't0402' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle"><input name="' . self::SLUG_PREFIX . self::ADD_IMAGE_TO_GALLERY . '" type="checkbox"' . self::$add_image_to_gallery_attr . 'value="' . self::ADD_IMAGE_TO_GALLERY . '"></td>' ),
			't0403' => array( 'continue' => '  <td style="text-align: left; padding-right: 5px" valign="middle">Add Product Image to Gallery</td>' ),
			't0404' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle"><input name="' . self::SLUG_PREFIX . self::DELETE_NO_CHILDREN . '" type="checkbox"' . self::$delete_no_children_attr . 'value="' . self::DELETE_NO_CHILDREN . '"></td>' ),
			't0405' => array( 'continue' => '  <td style="text-align: left; padding-right: 5px" valign="middle">Delete P.I. and P.G. if no children</td>' ),
			't0406' => array( 'continue' => '  <td colspan=2 style="text-align: right; padding-right: 5px" valign="middle">&nbsp;</td>' ),
			't0407' => array( 'continue' => '</tr><tr>' ),
			't0408' => array( 'continue' => '<td>&nbsp;</td><td colspan="5">Check Add Product Image to Gallery to add the Product Image to the Product Gallery.<br>For the Replace tool, check Delete P.I. and P.G. ... to delete the Product Image and Product Gallery<br>if there are no attached children.</td>' ),
			't0409' => array( 'close' => '</tr></table>&nbsp;<br>' ),
			'Fill from Children' => array( 'handler' => '_fill_from_children',
				'comment' => 'Add product\'s children to the Product/Featured Image and Product Gallery.' ),
			'Replace from Children' => array( 'handler' => '_replace_from_children',
				'comment' => 'Replace Product/Featured Image and Product Gallery from product\'s children.' ),
			'c5c' => array( 'handler' => '', 'comment' => '<hr>' ),
			't0501' => array( 'open' => '<table><tr>' ),
			't0502' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle"><input name="' . self::SLUG_PREFIX . self::POPULATE_PI_PG_ON_UPLOAD . '" type="checkbox"' . self::$populate_pi_pg_on_upload_attr . 'value="' . self::POPULATE_PI_PG_ON_UPLOAD . '"></td>' ),
			't0503' => array( 'continue' => '  <td style="text-align: left; padding-right: 5px" valign="middle">Populate P.I. and P.G. with children<br>from MLA&rsquo;s Bulk Edit on Upload</td>' ),
			't0504' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle"><input name="' . self::SLUG_PREFIX . self::POPULATE_PI_PG_ON_MMMW . '" type="checkbox"' . self::$populate_pi_pg_on_mmmw_attr . 'value="' . self::POPULATE_PI_PG_ON_MMMW . '"></td>' ),
			't0505' => array( 'continue' => '  <td style="text-align: left; padding-right: 5px" valign="middle">Populate P.I. and P.G. with children<br>from WordPress MMMW Add Files</td>' ),
			't0506' => array( 'continue' => '  <td colspan=2 style="text-align: right; padding-right: 5px" valign="middle">&nbsp;</td>' ),
			't0507' => array( 'continue' => '</tr><tr>' ),
			't0508' => array( 'continue' => '<td colspan="5">Check this options to populate the Product Image and Product Gallery when images are attached to a Product during the Media/Add New Bulk Edit on Upload processing and/or the WordPress "Add Files" popup window tab.</td>' ),
			't0509' => array( 'close' => '</tr></table>&nbsp;<br>' ),
			'Save Changes' => array( 'handler' => '_save_upload_children_option', 'comment' => 'Click here to record the new Populate P.I. and P.G. with children option settings.' ),
			'c5d' => array( 'handler' => '', 'comment' => '<hr>' ),
			'Where-used' => array( 'handler' => '_where_used',
				'comment' => 'Replace &quot;where_used&quot; information in custom field &quot;Woo Used In&quot;.' ),
			'c6' => array( 'handler' => '', 'comment' => '<h3>Operations on Products, using the Product Image, Product Tags and Att. Tags</h3>' ),
			'Clear Product Tags' => array( 'handler' => '_clear_product_tags',
				'comment' => '<strong>Delete ALL</strong> Product Tags assignments where a Product Image exists.' ),
			'Fill Product Tags' => array( 'handler' => '_fill_product_tags',
				'comment' => 'Fill empty Product Tags assignments from Product Image Att. Tags where a Product Image exists.' ),
			'Add Product Tags' => array( 'handler' => '_append_product_tags',
				'comment' => 'Append Product Tags assignments to <strong>ALL Products</strong> from Product Image Att. Tags where a Product Image exists.' ),
			'Replace Product Tags' => array( 'handler' => '_replace_product_tags',
				'comment' => '<strong>Replace ALL</strong> Product Tags assignments from Product Image Att. Tags where a Product Image exists.' ),

			'c7' => array( 'handler' => '', 'comment' => '<h3>Operations on Products, using the Product Image, Product Categories and Att. Tags</h3>' ),
			'Clear Product Cats' => array( 'handler' => '_clear_product_categories',
				'comment' => '<strong>Delete ALL Products&rsquo;</strong> Product Categories assignments where a Product Image exists.' ),
			'Fill Product Cats' => array( 'handler' => '_fill_product_categories',
				'comment' => 'Fill empty Product Categories assignments from Product Image Att. Tags,<br>where the Product Image Att. Tag matches an existing Product Category.' ),
			'Add Product Cats' => array( 'handler' => '_append_product_categories',
				'comment' => 'Append Product Categories assignments to <strong>ALL Products</strong> from Product Image Att. Tags,<br>where the Product Image Att. Tag matches an existing Product Category.' ),
			'Replace Product Cats' => array( 'handler' => '_replace_product_categories',
				'comment' => '<strong>Replace ALL</strong> Product Categories assignments from Product Image Att. Tags,<br>where the Product Image Att. Tag matches an existing Product Category.' ),

			'c8' => array( 'handler' => '', 'comment' => '<h3>Operations on the Att. Categories Taxonomy</h3>' ),
			'Clear Att. Cats' => array( 'handler' => '_clear_attachment_categories',
				'comment' => '<strong>Delete ALL</strong> Att. Categories assignments.' ),
			'Fill Att. Cats' => array( 'handler' => '_fill_attachment_categories',
				'comment' => 'Fill empty Att. Categories assignments from Att. Tags, where the Att. Tag matches an existing Att. Category.' ),
			'Add Att. Cats' => array( 'handler' => '_append_attachment_categories',
				'comment' => 'Append Att. Categories assignments to <strong>ALL items</strong> from Att. Tags,<br>where the Att. Tag matches an existing Att. Category.' ),
			'Replace Att. Cats' => array( 'handler' => '_replace_attachment_categories',
				'comment' => '<strong>Replace ALL</strong> Att. Categories assignments from Att. Tags, where the Att. Tag matches an existing Att. Category.' ),
			'c9' => array( 'handler' => '', 'comment' => '<h3>Term Assignments for Media Library Items</h3>' ),
			't0201' => array( 'open' => '<table><tr>' ),
			't0202' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle"><input name="' . self::SLUG_PREFIX . self::INPUT_PROCESS_CATEGORY . '" type="checkbox"' . self::$category_attr . 'value="' . self::INPUT_PROCESS_CATEGORY . '"></td>' ),
			't0203' => array( 'continue' => '  <td style="text-align: left; padding-right: 5px" valign="middle">product_category</td>' ),
			't0204' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle"><input name="' . self::SLUG_PREFIX . self::INPUT_PROCESS_TAG . '" type="checkbox"' . self::$tag_attr . 'value="' . self::INPUT_PROCESS_TAG . '"></td>' ),
			't0205' => array( 'continue' => '  <td style="text-align: left; padding-right: 5px" valign="middle">product_tag</td>' ),
			't0206' => array( 'continue' => '  <td colspan=2 style="text-align: right; padding-right: 5px" valign="middle">&nbsp;</td>' ),
			't0207' => array( 'continue' => '</tr><tr>' ),
			't0208' => array( 'continue' => '<td>&nbsp;</td><td colspan="5">Check a box above to include the taxonomy in the processing.</td>' ),
			't0209' => array( 'continue' => '</tr><tr style="display: none">' ),
			't0210' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">Start Chunk</td>' ),
			't0211' => array( 'continue' => '  <td style="text-align: left; padding-right: 20px">' ),
			't0212' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . self::INPUT_FIRST_CHUNK . '" type="text" size="5" value="' . self::$start_chunk . '">' ),
			't0213' => array( 'continue' => '  </td>' ),
			't0214' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">Stop Chunk</td>' ),
			't0215' => array( 'continue' => '  <td style="text-align: left;">' ),
			't0216' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . self::INPUT_LAST_CHUNK . '" type="text" size="5" value="' . self::$stop_chunk . '">' ),
			't0217' => array( 'continue' => '  </td>' ),
			't0218' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">Chunk Size</td>' ),
			't0219' => array( 'continue' => '  <td style="text-align: left;">' ),
			't0220' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . self::INPUT_CHUNK_SIZE . '" type="text" size="5" value="' . self::$chunk_size . '">' ),
			't0221' => array( 'continue' => '  </td>' ),
			't0222' => array( 'continue' => '</tr><tr style="display: none">' ),
			't0223' => array( 'continue' => '<td>&nbsp;</td><td colspan="5">Enter start and stop chunks to restrict processing range;<br>chunk size is number of proucts/chunk.</td>' ),
			't0224' => array( 'close' => '</tr></table>&nbsp;<br>' ),
			'Clear Terms' => array( 'handler' => '_clear_term_assignments',
				'comment' => '<strong>Delete ALL</strong> product_category and/or product_tag term assignments to Media Library items.' ),
			'Assign Terms' => array( 'handler' => '_copy_term_assignments',
				'comment' => 'Copy product_category and/or product_tag term assignments to Media Library items for items used as Product Image or in the Product Gallery.' ),

			'c10' => array( 'handler' => '', 'comment' => '<h3>Populate Product from Product Image</h3>' ),
			't1001' => array( 'open' => '<table><tr>' ),
			't1002' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">Name</td>' ),
			't1003' => array( 'continue' => '  <td colspan="4" style="text-align: left; padding-right: 20px">' ),
			't1004' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . self::NAME_TEMPLATE . '" type="text" size="60" value="' . self::$settings[self::NAME_TEMPLATE] . '">' ),
			't1005' => array( 'continue' => '  </td>' ),
			't1006' => array( 'continue' => '</tr><tr>' ),
			't1007' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">Description</td>' ),
			't1008' => array( 'continue' => '  <td colspan="4" style="text-align: left; padding-right: 20px">' ),
			't1009' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . self::DESCRIPTION_TEMPLATE . '" type="text" size="60" value="' . self::$settings[self::DESCRIPTION_TEMPLATE] . '">' ),
			't1010' => array( 'continue' => '  </td>' ),
			't1011' => array( 'continue' => '</tr><tr>' ),
			't1012' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">Short Description</td>' ),
			't1013' => array( 'continue' => '  <td colspan="4" style="text-align: left; padding-right: 20px">' ),
			't1014' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . self::SHORT_DESCRIPTION_TEMPLATE . '" type="text" size="60" value="' . self::$settings[self::SHORT_DESCRIPTION_TEMPLATE] . '">' ),
			't1015' => array( 'continue' => '  </td>' ),
			't1016' => array( 'continue' => '</tr><tr>' ),
			't1017' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">Categories</td>' ),
			't1018' => array( 'continue' => '  <td colspan="4" style="text-align: left; padding-right: 20px">' ),
			't1019' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . self::CATEGORIES_TEMPLATE . '" type="text" size="60" value="' . self::$settings[self::CATEGORIES_TEMPLATE] . '">' ),
			't1020' => array( 'continue' => '  </td>' ),
			't1021' => array( 'continue' => '</tr><tr>' ),
			't1022' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">Tags</td>' ),
			't1023' => array( 'continue' => '  <td colspan="4" style="text-align: left; padding-right: 20px">' ),
			't1024' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . self::TAGS_TEMPLATE . '" type="text" size="60" value="' . self::$settings[self::TAGS_TEMPLATE] . '">' ),
			't1025' => array( 'continue' => '  </td>' ),
			't1026' => array( 'continue' => '</tr><tr>' ),
			't1027' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle">SKU</td>' ),
			't1028' => array( 'continue' => '  <td colspan="4"  style="text-align: left; padding-right: 20px">' ),
			't1029' => array( 'continue' => '    <input name="' . self::SLUG_PREFIX . self::SKU_TEMPLATE . '" type="text" size="60" value="' . self::$settings[self::SKU_TEMPLATE] . '">' ),
			't1030' => array( 'continue' => '  </td>' ),
			't1031' => array( 'continue' => '</tr><tr>' ),
			't1032' => array( 'continue' => '<td>&nbsp;</td><td colspan="4" >Enter Content Template(s) (without the "template:" prefix). To leave a field unchanged, delete the template from the corresponding text box.<br>&nbsp;</td>' ),
			't1033' => array( 'continue' => '</tr><tr>' ),
			't1034' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle"><input name="' . self::SLUG_PREFIX . self::POPULATE_ON_ADD . '" type="checkbox"' . self::$populate_on_add_attr . 'value="' . self::POPULATE_ON_ADD . '"></td>' ),
			't1035' => array( 'continue' => '  <td style="text-align: left; padding-right: 5px" valign="middle">Active for adding Product Image</td>' ),
			't1036' => array( 'continue' => '  <td style="text-align: right; padding-right: 5px" valign="middle"><input name="' . self::SLUG_PREFIX . self::POPULATE_ON_UPDATE . '" type="checkbox"' . self::$populate_on_update_attr . 'value="' . self::POPULATE_ON_UPDATE . '"></td>' ),
			't1037' => array( 'continue' => '  <td style="text-align: left; padding-right: 5px" valign="middle">Active for updating Product Image</td>' ),
			't1038' => array( 'continue' => '  <td colspan=2 style="text-align: right; padding-right: 5px" valign="middle">&nbsp;</td>' ),
			't1039' => array( 'continue' => '</tr><tr>' ),
			't1040' => array( 'continue' => '<td>&nbsp;</td><td colspan="5">Check Active for adding... to populate when Product Image is set.<br>Check Active for updating... to populate when Product Image is changed.</td>' ),
			't1041' => array( 'close' => '</tr></table>&nbsp;<br>' ),
			'Populate Products' => array( 'handler' => '_populate_product_from_product_image',
				'comment' => 'Populate Product values from the attached Product Image item.' ),
			'Save Templates' => array( 'handler' => '_save_product_templates',
				'comment' => 'Save current template values to the database.' ),
			'Load Templates' => array( 'handler' => '_load_product_templates',
				'comment' => 'Load template values from the database.' ),
			'Restore Defaults' => array( 'handler' => '_restore_product_template_defaults',
				'comment' => 'Erase stored values from the database and set the default template values.' ),
 		);
	}

	/**
	 * Render (echo) the "WooCommerce Fixit" submenu in the Tools section
	 *
	 * @since 1.00
	 *
	 * @return	void Echoes HTML markup for the submenu page
	 */
	public static function render_tools_page() {
//error_log( __LINE__ . ' Woo_Fixit::render_tools_page() $_REQUEST = ' . var_export( $_REQUEST, true ), 0 );
		if ( !current_user_can( 'manage_options' ) ) {
			echo "WooCommerce Fixit - Error</h2>\n";
			wp_die( 'You do not have permission to manage plugin settings.' );
		}

		// Load the Product from Product Image templates from the database or set defaults
		//self::_load_settings();

		// Extract relevant query arguments
		self::$first_product = isset( $_REQUEST[ self::SLUG_PREFIX . self::INPUT_FIRST_PRODUCT ] ) ? $_REQUEST[ self::SLUG_PREFIX . self::INPUT_FIRST_PRODUCT ] : '';
		self::$last_product = isset( $_REQUEST[ self::SLUG_PREFIX . self::INPUT_LAST_PRODUCT ] ) ? $_REQUEST[ self::SLUG_PREFIX . self::INPUT_LAST_PRODUCT ] : '';

		// Operations on ALL Media Library Images
		self::$append_item_id = isset( $_REQUEST[ self::SLUG_PREFIX . self::APPEND_ITEM_ID ] ) ? true : false;
		self::$append_item_id_attr = self::$append_item_id ? ' checked="checked" ' : ' ';
		self::$check_unique_slug = isset( $_REQUEST[ self::SLUG_PREFIX . self::CHECK_UNIQUE_SLUG ] ) ? true : false;
		self::$check_unique_slug_attr = self::$check_unique_slug ? ' checked="checked" ' : ' ';

		// Operations on the Product Image and Product Gallery
		self::$add_image_to_gallery = isset( $_REQUEST[ self::SLUG_PREFIX . self::ADD_IMAGE_TO_GALLERY ] ) ? true : false;
		self::$add_image_to_gallery_attr = self::$add_image_to_gallery ? ' checked="checked" ' : ' ';
		self::$delete_no_children = isset( $_REQUEST[ self::SLUG_PREFIX . self::DELETE_NO_CHILDREN ] ) ? true : false;
		self::$delete_no_children_attr = self::$delete_no_children ? ' checked="checked" ' : ' ';
		self::$delete_no_children = isset( $_REQUEST[ self::SLUG_PREFIX . self::DELETE_NO_CHILDREN ] ) ? true : false;
		self::$delete_no_children_attr = self::$delete_no_children ? ' checked="checked" ' : ' ';

		// No checkbox settings on initial page load
		if ( isset( $_REQUEST[ self::SLUG_PREFIX . 'action' ] ) ) {
			self::$settings[self::POPULATE_PI_PG_ON_UPLOAD] = isset( $_REQUEST[ self::SLUG_PREFIX . self::POPULATE_PI_PG_ON_UPLOAD ] );
			self::$settings[self::POPULATE_PI_PG_ON_MMMW] = isset( $_REQUEST[ self::SLUG_PREFIX . self::POPULATE_PI_PG_ON_MMMW ] );
		}

		self::$populate_pi_pg_on_upload_attr = self::$settings[self::POPULATE_PI_PG_ON_UPLOAD] ? ' checked="checked" ' : ' ';
		self::$populate_pi_pg_on_mmmw_attr = self::$settings[self::POPULATE_PI_PG_ON_MMMW] ? ' checked="checked" ' : ' ';

		// Apply Template to Product Image/Product Gallery Images
		self::$content_template = isset( $_REQUEST[ self::SLUG_PREFIX . self::INPUT_CONTENT_TEMPLATE ] ) ? trim( stripslashes( $_REQUEST[ self::SLUG_PREFIX . self::INPUT_CONTENT_TEMPLATE ] ) ) : self::$content_template;

		// Term Assignments for Media Library Items
		self::$process_category = isset( $_REQUEST[ self::SLUG_PREFIX . self::INPUT_PROCESS_CATEGORY ] ) ? true : false;
		self::$category_attr = self::$process_category ? ' checked="checked" ' : ' ';
		self::$process_tag = isset( $_REQUEST[ self::SLUG_PREFIX . self::INPUT_PROCESS_TAG ] ) ? true : false;
		self::$tag_attr = self::$process_tag ? ' checked="checked" ' : ' ';

		self::$start_chunk = isset( $_REQUEST[ self::SLUG_PREFIX . self::INPUT_FIRST_CHUNK ] ) ? absint( $_REQUEST[ self::SLUG_PREFIX . self::INPUT_FIRST_CHUNK ] ) : self::$start_chunk;
		self::$stop_chunk = isset( $_REQUEST[ self::SLUG_PREFIX . self::INPUT_LAST_CHUNK ] ) ? absint( $_REQUEST[ self::SLUG_PREFIX . self::INPUT_LAST_CHUNK ] ) : self::$stop_chunk;
		self::$chunk_size = isset( $_REQUEST[ self::SLUG_PREFIX . self::INPUT_CHUNK_SIZE ] ) ? absint( $_REQUEST[ self::SLUG_PREFIX . self::INPUT_CHUNK_SIZE ] ) : self::$chunk_size;

		// Populate Product from Product Image
		self::$settings[self::NAME_TEMPLATE] = isset( $_REQUEST[ self::SLUG_PREFIX . self::NAME_TEMPLATE ] ) ? trim( stripslashes( $_REQUEST[ self::SLUG_PREFIX . self::NAME_TEMPLATE ] ) ) : self::$settings[self::NAME_TEMPLATE];
		self::$settings[self::DESCRIPTION_TEMPLATE] = isset( $_REQUEST[ self::SLUG_PREFIX . self::DESCRIPTION_TEMPLATE ] ) ? trim( stripslashes( $_REQUEST[ self::SLUG_PREFIX . self::DESCRIPTION_TEMPLATE ] ) ) : self::$settings[self::DESCRIPTION_TEMPLATE];
		self::$settings[self::SHORT_DESCRIPTION_TEMPLATE] = isset( $_REQUEST[ self::SLUG_PREFIX . self::SHORT_DESCRIPTION_TEMPLATE ] ) ? trim( stripslashes( $_REQUEST[ self::SLUG_PREFIX . self::SHORT_DESCRIPTION_TEMPLATE ] ) ) : self::$settings[self::SHORT_DESCRIPTION_TEMPLATE];
		self::$settings[self::CATEGORIES_TEMPLATE] = isset( $_REQUEST[ self::SLUG_PREFIX . self::CATEGORIES_TEMPLATE ] ) ? trim( stripslashes( $_REQUEST[ self::SLUG_PREFIX . self::CATEGORIES_TEMPLATE ] ) ) : self::$settings[self::CATEGORIES_TEMPLATE];
		self::$settings[self::TAGS_TEMPLATE] = isset( $_REQUEST[ self::SLUG_PREFIX . self::TAGS_TEMPLATE ] ) ? trim( stripslashes( $_REQUEST[ self::SLUG_PREFIX . self::TAGS_TEMPLATE ] ) ) : self::$settings[self::TAGS_TEMPLATE];
		self::$settings[self::SKU_TEMPLATE] = isset( $_REQUEST[ self::SLUG_PREFIX . self::SKU_TEMPLATE ] ) ? trim( stripslashes( $_REQUEST[ self::SLUG_PREFIX . self::SKU_TEMPLATE ] ) ) : self::$settings[self::SKU_TEMPLATE];

		// No checkbox settings on initial page load
		if ( isset( $_REQUEST[ self::SLUG_PREFIX . 'action' ] ) ) {
			self::$settings[self::POPULATE_ON_ADD] = isset( $_REQUEST[ self::SLUG_PREFIX . self::POPULATE_ON_ADD ] );
			self::$settings[self::POPULATE_ON_UPDATE] = isset( $_REQUEST[ self::SLUG_PREFIX . self::POPULATE_ON_UPDATE ] );
		}

		self::$populate_on_add_attr = self::$settings[self::POPULATE_ON_ADD] ? ' checked="checked" ' : ' ';
		self::$populate_on_update_attr = self::$settings[self::POPULATE_ON_UPDATE] ? ' checked="checked" ' : ' ';

		echo '<div class="wrap">' . "\n";
		echo "\t\t" . '<div id="icon-tools" class="icon32"><br/></div>' . "\n";
		echo "\t\t" . '<h2>WooCommerce Fixit Tools v' . self::CURRENT_VERSION . '</h2>' . "\n";

		if ( isset( $_REQUEST[ self::SLUG_PREFIX . 'action' ] ) ) {
			$setting_actions = self::_compose_settings_actions();
			$label = $_REQUEST[ self::SLUG_PREFIX . 'action' ];
			if( isset( $setting_actions[ $label ] ) ) {
				$action = $setting_actions[ $label ]['handler'];
				if ( ! empty( $action ) ) {
					if ( method_exists( 'Woo_Fixit', $action ) ) {
						$message = self::$action();

						if ( !empty( $message ) ) {
							$is_error = ( false !== strpos( $message, __( 'ERROR', 'media-library-assistant' ) ) );
							if ( $is_error ) {
								$messages_class = 'updated error';
							} else {
								$messages_class = 'updated notice is-dismissible';
							}

							echo "  <div class=\"{$messages_class}\" id=\"message\"><p>\n";
							echo '    ' . $message . "\n";
							echo "  </p>\n";

							if ( !$is_error ) {
								// Obsolete as of WP 4.4.0
								// echo "  <button class=\"notice-dismiss\" type=\"button\"><span class=\"screen-reader-text\">Dismiss this notice.</span></button>\n";
							}

							echo "  </div>\n";
						}
					} else {
						echo "\t\t<br><strong>ERROR</strong>: handler \"{$action}\" does not exist for action: \"{$label}\"\n";
					}
				} else {
					echo "\t\t<br><strong>ERROR</strong>: no handler for action: \"{$label}\"\n";
				}
			} else {
				echo "\t\t<br>ERROR: unknown action: \"{$label}\"\n";
			}
		}

		echo "\t\t" . '<p><strong>NOTE:</strong> This plugin adds "product:" and "product_terms:" custom substitution prefixes.<br />' . "\n";
		echo "\t\t" . 'For any Media Library item that is used as a Product Image or Product Gallery item,<br />' . "\n";
		echo "\t\t" . 'these will return values from the associated Product.</p>' . "\n";

		echo "\t\t" . '<div style="width:700px">' . "\n";
		echo "\t\t" . '<form action="' . admin_url( 'tools.php?page=' . self::SLUG_PREFIX . 'tools' ) . '" method="post" class="' . self::SLUG_PREFIX . 'tools-form-class" id="' . self::SLUG_PREFIX . 'tools-form-id">' . "\n";
		echo "\t\t" . '  <p class="submit" style="padding-bottom: 0;">' . "\n";
		echo "\t\t" . '    <table>' . "\n";

		echo "\t\t" . '      <tr valign="top"><th valign="middle" style="text-align: right;" scope="row">First Product</th><td style="text-align: left;">' . "\n";
		echo "\t\t" . '        <input name="' . self::SLUG_PREFIX . self::INPUT_FIRST_PRODUCT . '" type="text" size="5" value="' . self::$first_product . '">' . "\n";
		echo "\t\t" . '      </td></tr>' . "\n";

		echo "\t\t" . '      <tr valign="top"><th valign="middle" style="text-align: right;" scope="row">Last Product</th><td style="text-align: left;">' . "\n";
		echo "\t\t" . '        <input name="' . self::SLUG_PREFIX . self::INPUT_LAST_PRODUCT . '" type="text" size="5" value="' . self::$last_product . '">' . "\n";
		echo "\t\t" . '      </td></tr>' . "\n";

		// Refresh the settings actions, which may be modified by the just-executed action.
		$setting_actions = self::_compose_settings_actions();
		foreach ( $setting_actions as $label => $action ) {
			if ( isset( $action['open'] ) ) {
				echo "\t\t" . '      <tr><td colspan=2 style="padding: 2px 0px;">' . "\n";
				echo "\t\t" . '        ' . $action['open'] . "\n";
			} elseif ( isset( $action['continue'] ) ) {
				echo "\t\t" . '        ' . $action['continue'] . "\n";
			} elseif ( isset( $action['close'] ) ) {
				echo "\t\t" . '        ' . $action['close'] . "\n";
				echo "\t\t" . '      </td></tr>' . "\n";
			} else {
				if ( empty( $action['handler'] ) ) {
					echo "\t\t" . '      <tr><td colspan=2 style="padding: 2px 0px;">' . $action['comment'] . "</td></tr>\n";
				} else {
					echo "\t\t" . '      <tr><td width="160px">' . "\n";
					echo "\t\t" . '        <input name="' . self::SLUG_PREFIX . 'action" type="submit" class="button-primary" style="width: 150px;" value="' . $label . '" />&nbsp;&nbsp;' . "\n";
					echo "\t\t" . '      </td><td>' . "\n";
					echo "\t\t" . '        ' . $action['comment'] . "\n";
					echo "\t\t" . '      </td></tr>' . "\n";
				}
			}
		}

		echo "\t\t" . '    </table>' . "\n";
		echo "\t\t" . '  </p>' . "\n";
		echo "\t\t" . '</form>' . "\n";
		echo "\t\t" . '</div>' . "\n";
		echo "\t\t" . '</div><!-- wrap -->' . "\n";
	}

	/**
	 * Array of Products giving Product Image and Product Gallery attachments:
	 * product_id => array( 'post_title' => product Title, '_thumbnail_id' => image_id, '_product_image_gallery' => gallery_ids (comma-delimited string), 'children' => array( child IDs ) )
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $product_attachments = array();

	/**
	 * Array of Attachments giving Product assignments:
	 * attachment_id => array( '_thumbnail_id' => array( thumbnail_ids ), '_product_image_gallery' => array( gallery_ids ), 'category_thumbnail' => array( term_id => name ) )
	 *
	 * @since 1.00
	 *
	 * @var	array
	 */
	private static $attachment_products = array();

	/**
	 * Compile array of Product Image and Product Gallery attachments
 	 *
	 * @since 1.00
	 *
	 * @param boolean $build_pa Optional. Build the product_attachments array. Default: true.
	 * @param boolean $add_children Optional. Add product children to the product_attachments array. Default: false.
	 */
	private static function _build_product_attachments( $build_pa = true, $add_children = false ) {
		global $wpdb;

		if ( ! empty( self::$first_product ) ) {
			$lower_bound = (integer) self::$first_product;
		} else {
			$lower_bound = 0;
		}

		if ( ! empty( self::$last_product ) ) {
			$upper_bound = (integer) self::$last_product;
		} elseif ( $lower_bound ) {
			$upper_bound = $lower_bound;
		} else {
			$upper_bound = 0x7FFFFFFF;
		}

		self::$product_attachments = array();
		self::$attachment_products = array();

		$query = sprintf( 'SELECT t.term_id, t.name, tm.meta_value FROM %1$s as t INNER JOIN %2$s as tt ON t.term_id = tt.term_id INNER JOIN %3$s as tm ON t.term_id = tm.term_id  WHERE ( ( tt.taxonomy = \'product_cat\' ) AND ( tm.meta_key = \'thumbnail_id\' ) AND ( tm.meta_value > 0 ) AND ( tm.meta_value >= %4$d ) AND ( tm.meta_value <= %5$d) ) ORDER BY tm.meta_value, t.term_id', $wpdb->terms, $wpdb->term_taxonomy, $wpdb->termmeta, $lower_bound, $upper_bound );
		$results = $wpdb->get_results( $query );
//error_log( __LINE__ . ' Woo_Fixit::_build_product_attachments() $results = ' . var_export( $results, true ), 0 );
		foreach ( $results as $result ) {
			$key = (integer) $result->meta_value;
			if ( isset( self::$attachment_products[ $key ] ) ) {
				self::$attachment_products[ $key ]['category_thumbnail'][ (integer) $result->term_id ] = $result->name;
			} else {
				self::$attachment_products[ $key ]['category_thumbnail'] = array( (integer) $result->term_id => $result->name );
			}
		}
//error_log( __LINE__ . ' Woo_Fixit::_build_product_attachments() self::$attachment_products = ' . var_export( self::$attachment_products, true ), 0 );

		unset( $results );

		$query = sprintf( 'SELECT m.*, p.post_title FROM %1$s as m INNER JOIN %2$s as p ON m.post_id = p.ID WHERE ( p.post_type = \'product\' ) AND ( p.ID >= %3$d ) AND ( p.ID <= %4$d) AND ( m.meta_key IN ( \'_product_image_gallery\', \'_thumbnail_id\' ) ) GROUP BY m.post_id, m.meta_id ORDER BY m.post_id', $wpdb->postmeta, $wpdb->posts, $lower_bound, $upper_bound );
		$results = $wpdb->get_results( $query );
//error_log( __LINE__ . ' Woo_Fixit::_build_product_attachments() $results = ' . var_export( $results, true ), 0 );

		foreach ( $results as $result ) {
			if ( $build_pa ) {
				self::$product_attachments[ $result->post_id ]['post_title'] = trim( $result->post_title );
				self::$product_attachments[ $result->post_id ][ $result->meta_key ] = trim( $result->meta_value );
			}

			if ( '_thumbnail_id' == $result->meta_key ) {
				$key = (integer) $result->meta_value;

				if ( isset( self::$attachment_products[ $key ] ) ) {
					self::$attachment_products[ $key ]['_thumbnail_id'][] = (integer) $result->post_id;
				} else {
					self::$attachment_products[ $key ]['_thumbnail_id'] = array( (integer) $result->post_id );
				}
			} else {
				foreach( explode( ',', $result->meta_value ) as $key ) {
					$key = (integer) trim( $key );
					if ( isset( self::$attachment_products[ $key ] ) ) {
						self::$attachment_products[ $key ]['_product_image_gallery'][] = (integer) $result->post_id;
					} else {
						self::$attachment_products[ $key ]['_product_image_gallery'] = array( (integer) $result->post_id );
					}
				}
			}
		} // foreach attachment_products thumbnail/gallery

		if ( $build_pa && $add_children ) {
			// Exclude unattached items
			if ( 0 === $lower_bound ) {
				$lower_bound = 1;
			}
			
			$query = sprintf( 'SELECT p.ID, p.post_mime_type, p.post_parent FROM %1$s as p INNER JOIN %1$s as parent ON parent.ID = p.post_parent WHERE ( parent.post_type = \'product\' ) AND ( p.post_type = \'attachment\' ) AND ( p.post_status = \'inherit\' ) AND ( p.post_parent >= %2$d ) AND ( p.post_parent <= %3$d ) ORDER BY p.ID', $wpdb->posts, $lower_bound, $upper_bound );
			$results = $wpdb->get_results( $query );
//error_log( __LINE__ . ' Woo_Fixit::_build_product_attachments() add_children $results = ' . var_export( $results, true ), 0 );

			foreach ( $results as $result ) {
				if ( 0 === strpos( $result->post_mime_type, 'image' ) ) {
					$key = (integer) trim( $result->post_parent );
					$ID = (integer) $result->ID;
					if ( isset( self::$product_attachments[ $key ] ) ) {
						self::$product_attachments[ $key ]['children'][ $ID ] = $ID;
					} else {
						self::$product_attachments[ $key ]['children'] = array( $ID => $ID );
					}
				} // image child
			} // foreach product child
		}
		
//error_log( __LINE__ . ' Woo_Fixit::_build_product_attachments() self::$product_attachments = ' . var_export( self::$product_attachments, true ), 0 );
//error_log( __LINE__ . ' Woo_Fixit::_build_product_attachments() self::$attachment_products = ' . var_export( self::$attachment_products, true ), 0 );
	} // _build_product_attachments

	/**
	 * Replace ALL item Title fields with empty value
 	 *
	 * @since 1.00
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _clear_title() {
		global $wpdb;

		$results = $wpdb->query( "UPDATE {$wpdb->posts} SET post_title = '' WHERE post_type = 'attachment'" );
		return "_clear_title() performed {$results} update(s).\n";
	} // _clear_title

	/**
	 * Fill empty item Title field with re-formatted file name
 	 *
	 * @since 1.00
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _fill_title() {
		global $wpdb;

		$query = sprintf( 'SELECT m.post_id, m.meta_value FROM %1$s as m INNER JOIN %2$s as p ON m.post_id = p.ID WHERE ( p.post_title = \'\' ) AND ( p.post_type = \'attachment\' ) AND ( m.meta_key IN ( \'_wp_attached_file\' ) )', $wpdb->postmeta, $wpdb->posts );
		$results = $wpdb->get_results( $query );

		$update_count = 0;
		$select_bits = '';
		$where_bits = array();
		$chunk_count = 0;
		foreach( $results as $result ) {
			$path_info = pathinfo( $result->meta_value );  
			$new_title = str_replace( array( '-', '_', '.' ), ' ', $path_info['filename'] );
			$select_bits .= " WHEN ID = {$result->post_id} THEN '{$new_title}'";
			$where_bits[] = $result->post_id;

			// Run an update when the chunk is full
			if ( 25 <= ++$chunk_count ) {
				$where_bits = implode( ',', $where_bits );
				$update_query = "UPDATE {$wpdb->posts} SET post_title = CASE{$select_bits} ELSE post_title END WHERE ID IN ( {$where_bits} )";
				$query_result = $wpdb->query( $update_query );
				$update_count += $chunk_count;
				$select_bits = '';
				$where_bits = array();
				$chunk_count = 0;
			}
		}

		// Run a final update if the chunk is partially filled
		if ( $chunk_count ) {
			$where_bits = implode( ',', $where_bits );
			$update_query = "UPDATE {$wpdb->posts} SET post_title = CASE{$select_bits} ELSE post_title END WHERE ID IN ( {$where_bits} )";
			$query_result = $wpdb->query( $update_query );
			$update_count += $chunk_count;
		}

		return "_fill_title() performed {$update_count} update(s).\n";
	} // _fill_title

	/**
	 * Replace ALL item Title fields with re-formatted file name
 	 *
	 * @since 1.00
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _replace_title() {
		global $wpdb;

		$query = sprintf( 'SELECT m.post_id, m.meta_value FROM %1$s as m INNER JOIN %2$s as p ON m.post_id = p.ID WHERE ( p.post_mime_type LIKE \'%3$s\' ) AND ( p.post_type = \'attachment\' ) AND ( m.meta_key IN ( \'_wp_attached_file\' ) )', $wpdb->postmeta, $wpdb->posts, 'image/%' );
		$results = $wpdb->get_results( $query );

		$update_count = 0;
		$select_bits = '';
		$where_bits = array();
		$chunk_count = 0;
		foreach( $results as $result ) {
			$path_info = pathinfo( $result->meta_value );  
			$new_title = str_replace( array( '-', '_', '.' ), ' ', $path_info['filename'] );
			$select_bits .= " WHEN ID = {$result->post_id} THEN '{$new_title}'";
			$where_bits[] = $result->post_id;

			// Run an update when the chunk is full
			if ( 25 <= ++$chunk_count ) {
				$where_bits = implode( ',', $where_bits );
				$update_query = "UPDATE {$wpdb->posts} SET post_title = CASE{$select_bits} ELSE post_title END WHERE ID IN ( {$where_bits} )";
				$query_result = $wpdb->query( $update_query );
				$update_count += $chunk_count;
				$select_bits = '';
				$where_bits = array();
				$chunk_count = 0;
			}
		}

		// Run a final update if the chunk is partially filled
		if ( $chunk_count ) {
			$where_bits = implode( ',', $where_bits );
			$update_query = "UPDATE {$wpdb->posts} SET post_title = CASE{$select_bits} ELSE post_title END WHERE ID IN ( {$where_bits} )";
			$query_result = $wpdb->query( $update_query );
			$update_count += $chunk_count;
		}

		return "_replace_title() performed {$update_count} update(s).\n";
	} // _replace_title

	/**
	 * Replace ALL item Name/Slug fields with file name
	 *
	 * @since 2.04
	 *
	 * @return string HTML markup for results/messages
	 */
	private static function _replace_slug() {
		global $wpdb;
//error_log( __LINE__ . " Woo_Fixit::_replace_slug append_item_id = " . var_export( self::$append_item_id, true ), 0 );
//error_log( __LINE__ . " Woo_Fixit::_replace_slug check_unique_slug = " . var_export( self::$check_unique_slug, true ), 0 );

		$query = sprintf( 'SELECT m.post_id, m.meta_value, p.post_status FROM %1$s as m INNER JOIN %2$s as p ON m.post_id = p.ID WHERE ( p.post_mime_type LIKE \'%3$s\' ) AND ( p.post_type = \'attachment\' ) AND ( m.meta_key IN ( \'_wp_attached_file\' ) )', $wpdb->postmeta, $wpdb->posts, 'image/%' );
		$results = $wpdb->get_results( $query );

		$update_count = 0;
		$select_bits = '';
		$where_bits = array();
		$chunk_count = 0;
		foreach( $results as $result ) {
//error_log( __LINE__ . " Woo_Fixit::_replace_slug result = " . var_export( $result, true ), 0 );
			$path_info = pathinfo( $result->meta_value );
			$new_slug = sanitize_title( $path_info['filename'] );

			if ( self::$append_item_id ) {
				$new_slug .= '-' . $result->post_id;
			}

			if ( self::$check_unique_slug ) {
				$new_slug = wp_unique_post_slug( $new_slug, $result->post_id, $result->post_status, 'attachment', 0 );
			}

			$select_bits .= " WHEN ID = {$result->post_id} THEN '{$new_slug}'";
			$where_bits[] = $result->post_id;

			// Run an update when the chunk is full
			if ( 25 <= ++$chunk_count ) {
				$where_bits = implode( ',', $where_bits );
				$update_query = "UPDATE {$wpdb->posts} SET post_name = CASE{$select_bits} ELSE post_name END WHERE ID IN ( {$where_bits} )";
//error_log( __LINE__ . " Woo_Fixit::_replace_slug update_query = " . var_export( $update_query, true ), 0 );
				$query_result = $wpdb->query( $update_query );
				$update_count += $chunk_count;
				$select_bits = '';
				$where_bits = array();
				$chunk_count = 0;
			}
		}

		// Run a final update if the chunk is partially filled
		if ( $chunk_count ) {
			$where_bits = implode( ',', $where_bits );
			$update_query = "UPDATE {$wpdb->posts} SET post_name = CASE{$select_bits} ELSE post_name END WHERE ID IN ( {$where_bits} )";
//error_log( __LINE__ . " Woo_Fixit::_replace_slug update_query = " . var_export( $update_query, true ), 0 );
			$query_result = $wpdb->query( $update_query );
			$update_count += $chunk_count;
		}

		return "_replace_slug() performed {$update_count} update(s).\n";
	} // _replace_slug

	/**
	 * Empty ALT Text field in all Product Image/Product Gallery items
 	 *
	 * @since 1.00
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _clear_alt_text() {
		global $wpdb;

		self::_build_product_attachments();
		ksort( self::$attachment_products );
		$update_count = 0;
		foreach ( array_chunk( self::$attachment_products, 25, true ) as $chunk ) {
			$keys = implode( ',', array_keys( $chunk ) );
			$delete_query = "DELETE FROM {$wpdb->postmeta} WHERE ( post_id IN ( {$keys} ) ) AND ( meta_key = '_wp_attachment_image_alt' )";
			$query_result = $wpdb->query( $delete_query );
			$update_count += $query_result;
		}

		return "_clear_alt_text() performed {$update_count} delete(s).\n";
	} // _clear_alt_text

	/**
	 * Fill empty ALT Text field with first top-level Product Category
 	 *
	 * @since 1.00
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _fill_alt_text() {
		global $wpdb;

		self::_build_product_attachments();
		$delete_count = 0;
		$insert_count = 0;
		foreach ( array_chunk( self::$product_attachments, 25, true ) as $chunk ) {
			$terms = wp_get_object_terms( array_keys( $chunk ), 'product_cat', array( 'orderby' => 'name', 'fields' => 'all_with_object_id' ) );

			if ( empty( $terms ) ) {
				continue;
			}

			// Build an array of "first product category" names
			$product_terms = array();
			foreach ( $terms as $term ) {
				if ( isset( $product_terms[ $term->object_id ] ) ) {
					// The first top-level term wins
					if ( 0 == $product_terms[ $term->object_id ]['parent'] ) {
						continue;
					} elseif ( ( (integer) $term->parent ) < $product_terms[ $term->object_id ]['parent'] ) {
						$product_terms[ $term->object_id ] = array( 'name' => $term->name, 'parent' => (integer) $term->parent );
					}
				} else {
					$product_terms[ $term->object_id ] = array( 'name' => $term->name, 'parent' => (integer) $term->parent );
				}
			}

			// Assign the names to each attachment
			$attachment_values = array();
			foreach ( $chunk as $key => $value ) {
				if ( empty( $product_terms[ $key ] ) ) {
					continue;
				}

				if ( ! empty( $value['_thumbnail_id'] ) ) {
					$attachment_values[ $value['_thumbnail_id'] ] = $product_terms[ $key ]['name'];
				}

				if ( ! empty( $value['_product_image_gallery'] ) ) {
					$ids = explode( ',', $value['_product_image_gallery'] );
					foreach( $ids as $id ) {
						$attachment_values[ $id ] = $product_terms[ $key ]['name'];
					}
				}
			}

			// Find the existing ALT Text values and remove them from the update
			$keys = implode( ',', array_keys( $attachment_values ) );
			$select_query = "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE ( post_id IN ( {$keys} ) ) AND ( meta_key = '_wp_attachment_image_alt' )";
			$empty_values = array();
			foreach( $wpdb->get_results( $select_query ) as $existing_value ) {
				$trim =  trim( $existing_value->meta_value );
				if ( empty( $trim ) ) {
					$empty_values[] = $existing_value->post_id;
					continue;
				}
				unset( $attachment_values[ (integer) $existing_value->post_id ] );
			}

			// Delete empty ALT Text values
			if ( ! empty( $empty_values ) ) {
				$keys = implode( ',', $empty_values );
				$delete_query = "DELETE FROM {$wpdb->postmeta} WHERE ( post_id IN ( {$keys} ) ) AND ( meta_key = '_wp_attachment_image_alt' )";
				$query_result = $wpdb->query( $delete_query );
				$delete_count += $query_result;
			}

			// Insert the new values
			foreach ( $attachment_values as $attachment => $text ) {
				$insert_query = "INSERT INTO {$wpdb->postmeta} ( `post_id`,`meta_key`,`meta_value` )
VALUES ( {$attachment},'_wp_attachment_image_alt','{$text}' )";
				$query_result = $wpdb->query( $insert_query );
				$insert_count += $query_result;
			}
		}

		return "_fill_alt_text() performed {$delete_count} delete(s), {$insert_count} inserts(s).\n";
	} // _fill_alt_text

	/**
	 * Replace ALL ALT Text field with first top-level Product Category
 	 *
	 * @since 1.00
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _replace_alt_text() {
		global $wpdb;

		self::_build_product_attachments();
		$delete_count = 0;
		$insert_count = 0;
		foreach ( array_chunk( self::$product_attachments, 25, true ) as $chunk ) {
			$terms = wp_get_object_terms( array_keys( $chunk ), 'product_cat', array( 'orderby' => 'name', 'fields' => 'all_with_object_id' ) );

			if ( empty( $terms ) ) {
				continue;
			}

			// Build an array of "first product category" names
			$product_terms = array();
			foreach ( $terms as $term ) {
				if ( isset( $product_terms[ $term->object_id ] ) ) {
					// The first top-level term wins
					if ( 0 == $product_terms[ $term->object_id ]['parent'] ) {
						continue;
					} elseif ( ( (integer) $term->parent ) < $product_terms[ $term->object_id ]['parent'] ) {
						$product_terms[ $term->object_id ] = array( 'name' => $term->name, 'parent' => (integer) $term->parent );
					}
				} else {
					$product_terms[ $term->object_id ] = array( 'name' => $term->name, 'parent' => (integer) $term->parent );
				}
			}

			// Assign the names to each attachment
			$attachment_values = array();
			foreach ( $chunk as $key => $value ) {
				if ( isset( $value['_thumbnail_id'] ) ) {
					$attachment_values[ $value['_thumbnail_id'] ] = $product_terms[ $key ]['name'];
				}

				if ( !empty( $value['_product_image_gallery'] ) ) {
					$ids = explode( ',', $value['_product_image_gallery'] );
					foreach( $ids as $id ) {
						$attachment_values[ $id ] = $product_terms[ $key ]['name'];
					}
				}
			}

			// Remove the old ALT Text values
			$keys = implode( ',', array_keys( $attachment_values ) );
			$delete_query = "DELETE FROM {$wpdb->postmeta} WHERE ( post_id IN ( {$keys} ) ) AND ( meta_key = '_wp_attachment_image_alt' )";
			$query_result = $wpdb->query( $delete_query );
			$delete_count += $query_result;

			// Insert the new values
			foreach ( $attachment_values as $attachment => $text ) {
				$insert_query = "INSERT INTO {$wpdb->postmeta} ( `post_id`,`meta_key`,`meta_value` )
VALUES ( {$attachment},'_wp_attachment_image_alt','{$text}' )";
				$query_result = $wpdb->query( $insert_query );
				$insert_count += $query_result;
			}
		}

		return "_replace_alt_text() performed {$delete_count} delete(s), {$insert_count} inserts(s).\n";
	} // _replace_alt_text

	/**
	 * Fill empty ALT Text field with Product Title
 	 *
	 * @since 1.20
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _fill_alt_text_t() {
		global $wpdb;

		self::_build_product_attachments();
		$delete_count = 0;
		$insert_count = 0;
		foreach ( array_chunk( self::$product_attachments, 25, true ) as $chunk ) {
			// Assign the Product Title to each attachment
			$attachment_values = array();
			foreach ( $chunk as $key => $value ) {
				if ( ! empty( $value['_thumbnail_id'] ) ) {
					$attachment_values[ $value['_thumbnail_id'] ] = $value['post_title'];
				}

				if ( ! empty( $value['_product_image_gallery'] ) ) {
					$ids = explode( ',', $value['_product_image_gallery'] );
					foreach( $ids as $id ) {
						$attachment_values[ $id ] = $value['post_title'];
					}
				}
			}

			// Find the existing ALT Text values and remove them from the update
			$keys = implode( ',', array_keys( $attachment_values ) );
			$select_query = "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE ( post_id IN ( {$keys} ) ) AND ( meta_key = '_wp_attachment_image_alt' )";
			$empty_values = array();
			foreach( $wpdb->get_results( $select_query ) as $existing_value ) {
				$trim =  trim( $existing_value->meta_value );
				if ( empty( $trim ) ) {
					$empty_values[] = $existing_value->post_id;
					continue;
				}
				unset( $attachment_values[ (integer) $existing_value->post_id ] );
			}

			// Delete empty ALT Text values
			if ( ! empty( $empty_values ) ) {
				$keys = implode( ',', $empty_values );
				$delete_query = "DELETE FROM {$wpdb->postmeta} WHERE ( post_id IN ( {$keys} ) ) AND ( meta_key = '_wp_attachment_image_alt' )";
				$query_result = $wpdb->query( $delete_query );
				$delete_count += $query_result;
			}

			// Insert the new values
			foreach ( $attachment_values as $attachment => $text ) {
				$insert_query = "INSERT INTO {$wpdb->postmeta} ( `post_id`,`meta_key`,`meta_value` )
VALUES ( {$attachment},'_wp_attachment_image_alt','{$text}' )";
				$query_result = $wpdb->query( $insert_query );
				$insert_count += $query_result;
			}
		}

		return "_fill_alt_text_t() performed {$delete_count} delete(s), {$insert_count} inserts(s).\n";
	} // _fill_alt_text_t

	/**
	 * Replace ALL ALT Text field with Product Title
 	 *
	 * @since 1.20
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _replace_alt_text_t() {
		global $wpdb;

		self::_build_product_attachments();
		$delete_count = 0;
		$insert_count = 0;
		foreach ( array_chunk( self::$product_attachments, 25, true ) as $chunk ) {
			// Assign the Product Title to each attachment
			$attachment_values = array();
			foreach ( $chunk as $key => $value ) {
				if ( isset( $value['_thumbnail_id'] ) ) {
					$attachment_values[ $value['_thumbnail_id'] ] = $value['post_title'];
				}

				if ( !empty( $value['_product_image_gallery'] ) ) {
					$ids = explode( ',', $value['_product_image_gallery'] );
					foreach( $ids as $id ) {
						$attachment_values[ $id ] = $value['post_title'];
					}
				}
			}

			// Remove the old ALT Text values
			$keys = implode( ',', array_keys( $attachment_values ) );
			$delete_query = "DELETE FROM {$wpdb->postmeta} WHERE ( post_id IN ( {$keys} ) ) AND ( meta_key = '_wp_attachment_image_alt' )";
			$query_result = $wpdb->query( $delete_query );
			$delete_count += $query_result;

			// Insert the new values
			foreach ( $attachment_values as $attachment => $text ) {
				$insert_query = "INSERT INTO {$wpdb->postmeta} ( `post_id`,`meta_key`,`meta_value` )
VALUES ( {$attachment},'_wp_attachment_image_alt','{$text}' )";
				$query_result = $wpdb->query( $insert_query );
				$insert_count += $query_result;
			}
		}

		return "_replace_alt_text_t() performed {$delete_count} delete(s), {$insert_count} inserts(s).\n";
	} // _replace_alt_text_t

	/**
	 * Fill empty item Title field with Product Title
 	 *
	 * @since 1.20
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _fill_title_t() {
		global $wpdb;

		self::_build_product_attachments();
		$update_count = 0;
		foreach ( array_chunk( self::$product_attachments, 25, true ) as $chunk ) {
			// Assign the Product Title to each attachment
			$attachment_values = array();
			foreach ( $chunk as $key => $value ) {
				if ( ! empty( $value['_thumbnail_id'] ) ) {
					$attachment_values[ $value['_thumbnail_id'] ] = $value['post_title'];
				}

				if ( ! empty( $value['_product_image_gallery'] ) ) {
					$ids = explode( ',', $value['_product_image_gallery'] );
					foreach( $ids as $id ) {
						$attachment_values[ $id ] = $value['post_title'];
					}
				}
			}

			// Find the non-empty Title values and remove them from the update
			$keys = implode( ',', array_keys( $attachment_values ) );
			$select_query = "SELECT ID, post_title FROM {$wpdb->posts} WHERE ( ID IN ( {$keys} ) )";
//error_log( __LINE__ . ' Woo_Fixit::_fill_title_t() $select_query = ' . var_export( $select_query, true ), 0 );
//error_log( __LINE__ . ' Woo_Fixit::_fill_title_t() get_results = ' . var_export( $wpdb->get_results( $select_query ), true ), 0 );
			foreach( $wpdb->get_results( $select_query ) as $existing_value ) {
				$trim =  trim( $existing_value->post_title );
				if ( ! empty( $trim ) ) {
					unset( $attachment_values[ (integer) $existing_value->ID ] );
				}
			}

			// Update with new values, if any
			if ( empty( $attachment_values ) ) {
				continue;
			}

			$select_bits = '';
			$where_bits = array();
			foreach ( $attachment_values as $attachment => $text ) {
				$select_bits .= " WHEN ID = {$attachment} THEN '{$text}'";
				$where_bits[] = $attachment;
			}

			$where_bits = implode( ',', $where_bits );
			$update_query = "UPDATE {$wpdb->posts} SET post_title = CASE{$select_bits} ELSE post_title END WHERE ID IN ( {$where_bits} )";
			$query_result = $wpdb->query( $update_query );
			$update_count += absint( $query_result );
		} // foreach $chunk

		return "_fill_title_t() performed performed {$update_count} update(s).\n";
	} // _fill_title_t

	/**
	 * Replace ALL item Title field with Product Title
 	 *
	 * @since 1.20
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _replace_title_t() {
		global $wpdb;

		self::_build_product_attachments();
		$update_count = 0;
		foreach ( array_chunk( self::$product_attachments, 25, true ) as $chunk ) {
			// Assign the Product Title to each attachment
			$attachment_values = array();
			foreach ( $chunk as $key => $value ) {
				if ( isset( $value['_thumbnail_id'] ) ) {
					$attachment_values[ $value['_thumbnail_id'] ] = $value['post_title'];
				}

				if ( !empty( $value['_product_image_gallery'] ) ) {
					$ids = explode( ',', $value['_product_image_gallery'] );
					foreach( $ids as $id ) {
						$attachment_values[ $id ] = $value['post_title'];
					}
				}
			}

			// Update with new values, if any
			if ( empty( $attachment_values ) ) {
				continue;
			}
			$select_bits = '';
			$where_bits = array();
			foreach ( $attachment_values as $attachment => $text ) {
				$select_bits .= " WHEN ID = {$attachment} THEN '{$text}'";
				$where_bits[] = $attachment;
			}

			$where_bits = implode( ',', $where_bits );
			$update_query = "UPDATE {$wpdb->posts} SET post_title = CASE{$select_bits} ELSE post_title END WHERE ID IN ( {$where_bits} )";
			$query_result = $wpdb->query( $update_query );
			$update_count += absint( $query_result );
		} // foreach $chunk

		return "_replace_title_t() performed {$update_count} update(s).\n";
	} // _replace_title_t

	/**
	 * Process the "Apply a Content Template to ALT Text fields" actions
 	 *
	 * @since 1.28
	 *
	 * @param boolean $retain_existing Retain existing ALT Text values
	 *
	 * @return array ( 'error' => $error_message, 'delete_count' => $delete_count,
	 *                 'insert_count' => $insert_count )
	 */
	private static function _process_alt_text_ct( $retain_existing ) {
		global $wpdb;

		$results = array( 'error' => '', 'delete_count' => 0, 'insert_count' => 0 );

		$content_template = self::$content_template;
		if ( 'template:' == substr( $content_template, 0, 9 ) ) {
			$content_template = substr( $content_template, 9 );
		}

		if ( empty( $content_template ) ) {
			$results['error'] =  "Content Template is empty; no updates done.\n";
			return $results;
		}

		// Define the template
		$my_setting = array(
			'data_source' => 'template',
			'meta_name' => '(' . $content_template . ')',
			'option' => 'raw'
		);

		self::_build_product_attachments();
		$delete_count = 0;
		$insert_count = 0;
		foreach ( array_chunk( self::$product_attachments, 25, true ) as $chunk ) {
			$all_values = array();
			$delete_values = array();
			$replace_values = array();

			// Evaluate the template for each attachment
			foreach ( $chunk as $key => $value ) {
				if ( ! empty( $value['_thumbnail_id'] ) ) {
					$all_values[] = $id = $value['_thumbnail_id'];

					// Evaluate the template for the Product Image
					$template_value = trim( MLAOptions::mla_get_data_source( $id, 'single_attachment_mapping', $my_setting, NULL ) );
					if ( !empty( $template_value ) ) {
						$replace_values[ $id ] = $template_value;
					}
				}

				if ( ! empty( $value['_product_image_gallery'] ) ) {
					$ids = explode( ',', $value['_product_image_gallery'] );
					foreach( $ids as $id ) {
						$all_values[] = $id;

						// Evaluate the template for a Product Gallery Image
						$template_value = trim( MLAOptions::mla_get_data_source( $id, 'single_attachment_mapping', $my_setting, NULL ) );
						if ( !empty( $template_value ) ) {
							$replace_values[ $id ] = $template_value;
						} 
					} // each Product Gallery image
				} // has gallery
			} // each Product

			if ( $retain_existing ) {
				// Find the existing ALT Text values and remove them from the update
				$keys = implode( ',', array_keys( $replace_values ) );
				$select_query = "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE ( post_id IN ( {$keys} ) ) AND ( meta_key = '_wp_attachment_image_alt' )";
				$delete_values = array();
				foreach( $wpdb->get_results( $select_query ) as $existing_value ) {
					$trim =  trim( $existing_value->meta_value );
					if ( empty( $trim ) ) {
						$delete_values[] = $existing_value->post_id;
						continue;
					}
					unset( $replace_values[ (integer) $existing_value->post_id ] );
				}
			} else {
				// Delete all of the existing values
				$delete_values = $all_values;
			}

			// Delete ALT Text values that are empty or will be replaced
			if ( ! empty( $delete_values ) ) {
				$keys = implode( ',', $delete_values );
				$delete_query = "DELETE FROM {$wpdb->postmeta} WHERE ( post_id IN ( {$keys} ) ) AND ( meta_key = '_wp_attachment_image_alt' )";
				$query_result = $wpdb->query( $delete_query );
				$delete_count += $query_result;
			}

			// Insert the new values
			foreach ( $replace_values as $attachment => $text ) {
				$insert_query = "INSERT INTO {$wpdb->postmeta} ( `post_id`,`meta_key`,`meta_value` )
VALUES ( {$attachment},'_wp_attachment_image_alt','{$text}' )";
				$query_result = $wpdb->query( $insert_query );
				$insert_count += $query_result;
			}
		} // each chunk

		$results['delete_count'] =  $delete_count;
		$results['insert_count'] =  $insert_count;
		return $results;
	} // _process_alt_text_ct

	/**
	 * Apply a Content Template to empty ALT Text fields
 	 *
	 * @since 1.28
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _fill_alt_text_ct() {
		$results = self::_process_alt_text_ct( true );
		if ( empty( $results['error'] ) ) {
			$delete_count = $results['delete_count'];
			$insert_count = $results['insert_count'];
			return "_fill_alt_text_ct() performed {$delete_count} delete(s), {$insert_count} inserts(s).\n";
		}

		return "_fill_alt_text_ct() " . $results['error'];
	} // _fill_alt_text_ct

	/**
	 * Apply a Content Template to ALL ALT Text fields
 	 *
	 * @since 1.28
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _replace_alt_text_ct() {
		$results = self::_process_alt_text_ct( false );
		if ( empty( $results['error'] ) ) {
			$delete_count = $results['delete_count'];
			$insert_count = $results['insert_count'];
			return "_replace_alt_text_ct() performed {$delete_count} delete(s), {$insert_count} inserts(s).\n";
		}

		return "_replace_alt_text_ct() " . $results['error'];
	} // _replace_alt_text_ct

	/**
	 * Remove Product/Featured Image from the Product Gallery
 	 *
	 * @since 1.10
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _remove_feature() {
		global $wpdb;

		self::_build_product_attachments();

		$update_count = 0;
		$select_bits = '';
		$where_bits = array();
		$chunk_count = 0;
		foreach( self::$product_attachments as $post_id => $result ) {
			if ( empty( $result['_thumbnail_id'] ) ) {
				continue;
			}

			$feature = (integer) $result['_thumbnail_id'];
			$gallery = array();
			$feature_found = false;

			if ( ! empty( $result['_product_image_gallery'] ) ) {
				foreach ( explode( ',', $result['_product_image_gallery'] ) as $item ) {
					if ( $feature == (integer) $item ) {
						$feature_found = true;
					} else {
						$gallery[] = $item;
					}
				} // foreach gallery item
			}

			if ( $feature_found ) {
				$new_gallery = implode( ',', $gallery );
				$select_bits .= " WHEN post_id = {$post_id} THEN '{$new_gallery}'";
				$where_bits[] = $post_id;

				// Run an update when the chunk is full
				if ( 25 <= ++$chunk_count ) {
					$where_bits = implode( ',', $where_bits );
					$update_query = "UPDATE {$wpdb->postmeta} SET meta_value = CASE{$select_bits} ELSE meta_value END WHERE post_id IN ( {$where_bits} ) AND meta_key = '_product_image_gallery'";
					$query_result = $wpdb->query( $update_query );
					$update_count += $chunk_count;
					$select_bits = '';
					$where_bits = array();
					$chunk_count = 0;
				}
			} // feature removed
		} // foreach product

		// Run a final update if the chunk is partially filled
		if ( $chunk_count ) {
			$where_bits = implode( ',', $where_bits );
			$update_query = "UPDATE {$wpdb->postmeta} SET meta_value = CASE{$select_bits} ELSE meta_value END WHERE post_id IN ( {$where_bits} ) AND meta_key = '_product_image_gallery'";
			$query_result = $wpdb->query( $update_query );
			$update_count += $chunk_count;
		}

		return "_remove_feature() performed {$update_count} update(s).\n";
	} // _remove_feature

	/**
	 * Restore Product/Featured Image to the Product Gallery
 	 *
	 * @since 1.10
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _restore_feature() {
		global $wpdb;

		self::_build_product_attachments();

		$update_count = 0;
		$select_bits = '';
		$where_bits = array();
		$chunk_count = 0;
		foreach( self::$product_attachments as $post_id => $result ) {
			if ( empty( $result['_thumbnail_id'] ) ) {
				continue;
			}

			$feature = (integer) $result['_thumbnail_id'];
			$gallery = array();
			$feature_found = false;
			if ( ! empty( $result['_product_image_gallery'] ) ) {
				foreach ( explode( ',', $result['_product_image_gallery'] ) as $item ) {
					if ( $feature == (integer) $item ) {
						$feature_found = true;
					} else {
						$gallery[] = $item;
					}
				} // foreach gallery item
			}

			if ( ! $feature_found ) {
				if ( count( $gallery ) ) {
					$new_gallery = implode( ',', $gallery ) . ',' . $feature;
				} else {
					$new_gallery = (string) $feature;
				}

				$select_bits .= " WHEN post_id = {$post_id} THEN '{$new_gallery}'";
				$where_bits[] = $post_id;

				// Run an update when the chunk is full
				if ( 25 <= ++$chunk_count ) {
					$where_bits = implode( ',', $where_bits );
					$update_query = "UPDATE {$wpdb->postmeta} SET meta_value = CASE{$select_bits} ELSE meta_value END WHERE post_id IN ( {$where_bits} ) AND meta_key = '_product_image_gallery'";
					$query_result = $wpdb->query( $update_query );
					$update_count += $chunk_count;
					$select_bits = '';
					$where_bits = array();
					$chunk_count = 0;
				}
			} // feature restored
		} // foreach product

		// Run a final update if the chunk is partially filled
		if ( $chunk_count ) {
			$where_bits = implode( ',', $where_bits );
			$update_query = "UPDATE {$wpdb->postmeta} SET meta_value = CASE{$select_bits} ELSE meta_value END WHERE post_id IN ( {$where_bits} ) AND meta_key = '_product_image_gallery'";
			$query_result = $wpdb->query( $update_query );
			$update_count += $chunk_count;
		}

		return "_restore_feature() performed {$update_count} update(s).\n";
	} // _restore_feature

	/**
	 * Reverse the image order in the Product Gallery
 	 *
	 * @since 1.10
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _reverse_gallery() {
		global $wpdb;

		self::_build_product_attachments();

		$update_count = 0;
		$select_bits = '';
		$where_bits = array();
		$chunk_count = 0;
		foreach( self::$product_attachments as $post_id => $result ) {
			if ( empty( $result['_product_image_gallery'] ) ) {
				$gallery = array();
			} else {
				$gallery = explode( ',', $result['_product_image_gallery'] );
			}

			if ( 1 < count( $gallery ) ) {
				$new_gallery = implode( ',', array_reverse( $gallery ) );
				$select_bits .= " WHEN post_id = {$post_id} THEN '{$new_gallery}'";
				$where_bits[] = $post_id;

				// Run an update when the chunk is full
				if ( 25 <= ++$chunk_count ) {
					$where_bits = implode( ',', $where_bits );
					$update_query = "UPDATE {$wpdb->postmeta} SET meta_value = CASE{$select_bits} ELSE meta_value END WHERE post_id IN ( {$where_bits} ) AND meta_key = '_product_image_gallery'";
					$query_result = $wpdb->query( $update_query );
					$update_count += $chunk_count;
					$select_bits = '';
					$where_bits = array();
					$chunk_count = 0;
				}
			} // gallery reversed
		} // foreach product

		// Run a final update if the chunk is partially filled
		if ( $chunk_count ) {
			$where_bits = implode( ',', $where_bits );
			$update_query = "UPDATE {$wpdb->postmeta} SET meta_value = CASE{$select_bits} ELSE meta_value END WHERE post_id IN ( {$where_bits} ) AND meta_key = '_product_image_gallery'";
			$query_result = $wpdb->query( $update_query );
			$update_count += $chunk_count;
		}

		return "_reverse_gallery() performed {$update_count} update(s).\n";
	} // _reverse_gallery

	/**
	 * Updates the postmeta table one chunk at a time.
 	 *
	 * @since 2.10
	 *
	 * @param string $meta_key Meta value name
	 * @param array  $updates  ( $post_id => $meta_value )
	 * @param array  $inserts  optional. ( $post_id => $meta_value )
	 *
	 * @return	integer	number of rows updated
	 */
	private static function _update_postmeta( $meta_key, $updates, $inserts = array() ) {
		global $wpdb;
//error_log( __LINE__ . " _update_postmeta( $meta_key ) updates = " . var_export( $updates, true ), 0 );
//error_log( __LINE__ . " _update_postmeta( $meta_key ) inserts = " . var_export( $inserts, true ), 0 );

		$update_count = 0;
		$select_bits = '';
		$where_bits = array();
		$chunk_count = 0;
		foreach( $updates as $post_id => $meta_value ) {
				$select_bits .= " WHEN post_id = {$post_id} THEN '{$meta_value}'";
				$where_bits[] = $post_id;

				// Run an update when the chunk is full
				if ( 25 <= ++$chunk_count ) {
					$where_bits = implode( ',', $where_bits );
					$query = "UPDATE {$wpdb->postmeta} SET meta_value = CASE{$select_bits} ELSE meta_value END WHERE post_id IN ( {$where_bits} ) AND meta_key = '{$meta_key}'";
					$query_result = $wpdb->query( $query );
					$update_count += $chunk_count;
					$select_bits = '';
					$where_bits = array();
					$chunk_count = 0;
				}
		} // foreach product

		// Run a final update if the chunk is partially filled
		if ( $chunk_count ) {
			$where_bits = implode( ',', $where_bits );
			$query = "UPDATE {$wpdb->postmeta} SET meta_value = CASE{$select_bits} ELSE meta_value END WHERE post_id IN ( {$where_bits} ) AND meta_key = '{$meta_key}'";
			$query_result = $wpdb->query( $query );
			$update_count += $chunk_count;
		}

		// Insertsd are done one row at a time
		foreach( $inserts as $post_id => $meta_value ) {
			$query = "INSERT INTO {$wpdb->postmeta} ( `post_id`,`meta_key`,`meta_value` )
	VALUES ( {$post_id},'{$meta_key}','{$meta_value}' )";
			$query_result = $wpdb->query( $query );
			$update_count++;
		} // foreach product

		return $update_count;
	} // _update_postmeta

	/**
	 * Add product's children to Product Image and Product Gallery.
 	 *
	 * @since 2.10
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _fill_from_children() {
		self::_build_product_attachments( true, true );

		$product_count = count( self::$product_attachments );
		$update_count = 0;
		$thumbnail_count = 0;
		$gallery_count = 0;

		$thumbnail_updates = array();
		$thumbnail_inserts = array();
		$gallery_updates = array();
		$gallery_inserts = array();

		foreach ( self::$product_attachments as $ID => $images ) {
			$thumbnail = isset( $images['_thumbnail_id'] ) ? (integer) $images['_thumbnail_id'] : 0;
			$gallery = isset( $images['_product_image_gallery'] ) ? array_map( 'absint', explode( ',', $images['_product_image_gallery'] ) ) : array();
			$children = isset( $images['children'] ) ? $images['children'] : array();
			$updated = false;

			if ( empty( $children ) ) {
				continue;
			}

			// Use the earliest child for a missing product image			
			if ( 0 === $thumbnail ) {
				$thumbnail = reset( $children );

				if ( !empty( $images['_thumbnail_id'] ) ) {
					$thumbnail_updates[ $ID ] = $thumbnail;
				} else {
					$thumbnail_inserts[ $ID ] = $thumbnail;
				}
				
				$updated = true;
			}

			// Make sure all children are in the gallery
			$gallery_additions = array();
			$child_thumbnail_in_gallery = false;
			foreach ( $children as $child ) {
				if ( in_array( $child, $gallery ) ) {
					if ( $child === $thumbnail ) {
						$child_thumbnail_in_gallery = true;
					}
					
					continue;
				}
				
				if ( ( $child !== $thumbnail ) || self::$add_image_to_gallery ) {
					$gallery_additions[] = $child;
				}
			} // foreach child

			// See if we must remove the "thumbnail child" from the gallery
			if ( $child_thumbnail_in_gallery && !self::$add_image_to_gallery ) {
				unset( $gallery[ array_search( $thumbnail, $gallery ) ] );
				$gallery_additions = array_merge( $gallery, $gallery_additions );
				$gallery = array();
			}

			if ( !empty( $gallery_additions ) ) {
				if ( !empty( $images['_product_image_gallery'] ) ) {
					$gallery_updates[ $ID ] = implode( ',', array_merge( $gallery, $gallery_additions ) );
				} else {
					$gallery_inserts[ $ID ] = implode( ',', $gallery_additions );
				}
				
				$updated = true;
			}
			
			if ( $updated ) {
				$update_count++;
			}
		} // foreach product

		// Apply the updates, if any
		if ( !empty( $thumbnail_updates ) || !empty( $thumbnail_inserts ) ) {
			$thumbnail_count = self::_update_postmeta( '_thumbnail_id', $thumbnail_updates, $thumbnail_inserts );
		}

		if ( !empty( $gallery_updates ) || !empty( $gallery_inserts ) ) {
			$gallery_count = self::_update_postmeta( '_product_image_gallery', $gallery_updates, $gallery_inserts );
		}

		return "_fill_from_children examined {$product_count} products, updated {$update_count}, filling {$thumbnail_count} product images and {$gallery_count} galleries";
	} // _fill_from_children

	/**
	 * Use product's children to replace Product Image and Product Gallery.
 	 *
	 * @since 2.10
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _replace_from_children() {
		self::_build_product_attachments( true, true );

		$product_count = count( self::$product_attachments );
		$update_count = 0;
		$thumbnail_count = 0;
		$gallery_count = 0;

		$thumbnail_updates = array();
		$thumbnail_inserts = array();
		$gallery_updates = array();
		$gallery_inserts = array();

		foreach ( self::$product_attachments as $ID => $images ) {
			$thumbnail = isset( $images['_thumbnail_id'] ) ? (integer) $images['_thumbnail_id'] : 0;
			$gallery = isset( $images['_product_image_gallery'] ) ? array_map( 'absint', explode( ',', $images['_product_image_gallery'] ) ) : array();
			$children = isset( $images['children'] ) ? $images['children'] : array();
			$updated = false;

			if ( empty( $children ) ) {
				if ( self::$delete_no_children ) {
					if ( delete_post_meta( $ID, '_thumbnail_id' ) ) {
						$updated = true;
						$thumbnail_count++;
					}
					
					if ( delete_post_meta( $ID, '_product_image_gallery' ) ) {
						$updated = true;
						$gallery_count++;
					}
					
					if ( $updated ) {
						$update_count++;
					}
				}

				continue;
			}

			// Use the earliest child for the product image
			$first_child = reset( $children );
			if ( $first_child !== $thumbnail ) {
				if ( !empty( $images['_thumbnail_id'] ) ) {
					$thumbnail_updates[ $ID ] = $first_child;
				} else {
					$thumbnail_inserts[ $ID ] = $first_child;
				}
				
				$updated = true;
			}

			if ( !self::$add_image_to_gallery ) {
				unset( $children[ $first_child ] );
			}
			
			// See if the galleries match, ignoring the order of the current gallery.
			$current_gallery = $gallery;
			$gallery_additions = $children;
			
			foreach ( $current_gallery as $index => $image ) {
				if ( in_array( $image, $gallery_additions ) ) {
					unset( $current_gallery[ $index ] );
					unset( $gallery_additions[ $image ] );
				}
			} // foreach current gallery image

			// If they match, both arrays will be empty
			if ( !empty( $current_gallery ) || !empty( $gallery_additions ) ) {
				if ( !empty( $images['_product_image_gallery'] ) ) {
					$gallery_updates[ $ID ] = implode( ',', $children );
				} else {
					$gallery_inserts[ $ID ] = implode( ',', $children );
				}
				
				$updated = true;
			}
			
			if ( $updated ) {
				$update_count++;
			}
		} // foreach product

		// Apply the updates, if any
		if ( !empty( $thumbnail_updates ) || !empty( $thumbnail_inserts ) ) {
			$thumbnail_count = self::_update_postmeta( '_thumbnail_id', $thumbnail_updates, $thumbnail_inserts );
		}

		if ( !empty( $gallery_updates ) || !empty( $gallery_inserts ) ) {
			$gallery_count = self::_update_postmeta( '_product_image_gallery', $gallery_updates, $gallery_inserts );
		}

		return "_replace_from_children examined {$product_count} products, updated {$update_count}, replacing {$thumbnail_count} product images and {$gallery_count} galleries";
	} // _replace_from_children

	/**
	 * Save the Populate Product from Product Image templates to the database
 	 *
	 * @since 2.06
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _save_upload_children_option() {
		return self::_save_setting_changes();
	} // _save_upload_children_option

	/**
	 * Replace "where_used" information in custom field "Woo Used In".
 	 *
	 * @since 1.24
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _where_used() {
		global $wpdb;

		$post_meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->postmeta} LEFT JOIN {$wpdb->posts} ON ( {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ) WHERE {$wpdb->postmeta}.meta_key = '%s' AND {$wpdb->posts}.post_type = 'attachment'", 'Woo Used In' ));

		if ( $delete_count = count( $post_meta_ids ) ) {
			foreach ( $post_meta_ids as $mid ) {
				delete_metadata_by_mid( 'post', $mid );
			}
		}

		self::_build_product_attachments();

		$insert_count = 0;
		$thumbnail_count = 0;
		$gallery_count = 0;
		$category_count = 0;

		foreach( self::$attachment_products as $post_id => $result ) {
			if ( empty( $result['_thumbnail_id'] ) ) {
				$thumbnails = array();
			} else {
				$thumbnails = $result['_thumbnail_id'];
			}

			if ( empty( $result['_product_image_gallery'] ) ) {
				$galleries = array();
			} else {
				$galleries = $result['_product_image_gallery'];
			}

			if ( empty( $result['category_thumbnail'] ) ) {
				$categories = array();
			} else {
				$categories = $result['category_thumbnail'];
			}

			// Compose references
			$references = '';
			$thumbnail_text = '';
			foreach ( $thumbnails as $thumbnail ) {
				$thumbnail_text .= sprintf( '(%1$d) %2$s,', $thumbnail, self::$product_attachments[ $thumbnail ]['post_title'] );
			}
			if ( !empty( $thumbnail_text ) ) {
				$references .= 'Thumbnails: ' . $thumbnail_text;
			}

			$gallery_text = '';
			foreach ( $galleries as $gallery ) {
				$gallery_text .= sprintf( '(%1$d) %2$s,', $gallery, self::$product_attachments[ $gallery ]['post_title'] );
			}
			if ( !empty( $gallery_text ) ) {
				if ( !empty( $references ) ) {
					$references .= '; ';
				}

				$references .= 'Galleries: ' . $gallery_text;
			}

			$category_text = '';
			foreach ( $categories as $term_id => $category ) {
				$category_text .= sprintf( '(%1$d) %2$s,', $term_id, $category );
			}
			if ( !empty( $category_text ) ) {
				if ( !empty( $references ) ) {
					$references .= '; ';
				}

				$references .= 'Categories: ' . $category_text;
			}

			if ( !empty( $references ) ) {
				$thumbnail_count += count( $thumbnails );
				$gallery_count += count( $galleries );
				$category_count += count( $categories );

				// Insert the new values
				$insert_query = "INSERT INTO {$wpdb->postmeta} ( `post_id`,`meta_key`,`meta_value` )
	VALUES ( {$post_id},'Woo Used In','{$references}' )";
				$query_result = $wpdb->query( $insert_query );
				$insert_count += $query_result;
			} // found references
		} // foreach product

		return "_where_used() deleted {$delete_count} items(s) in &quot;Woo Used In&quot;, then inserted {$insert_count} items(s) with {$thumbnail_count} thumbnail(s), {$gallery_count} gallery(s) and {$category_count} category thumbnails.\n";
	} // _where_used

	/**
	 * Delete ALL Products' Product Tags assignments where a Product Image exists
 	 *
	 * @since 1.11
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _clear_product_tags() {
		return self::_update_product_tags( 'clear' );
	} // _clear_product_tags

	/**
	 * Fill empty Product Tags assignments from Product Image Att. Tags,
	 * where the Product Image exists
 	 *
	 * @since 1.11
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _fill_product_tags() {
		return self::_update_product_tags( 'fill' );
	} // _fill_product_tags

	/**
	 * Append Product Tags assignments to ALL Products from Product Image Att. Tags,
	 * where the Product Image exists
 	 *
	 * @since 1.11
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _append_product_tags() {
		return self::_update_product_tags( 'append' );
	} // _append_product_tags

	/**
	 * Replace ALL Product Tags assignments from Product Image Att. Tags,
	 * where the Product Image exists
 	 *
	 * @since 1.11
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _replace_product_tags() {
		return self::_update_product_tags( 'replace' );
	} // _replace_product_tags

	/**
	 * Common code for the Product Category operations
 	 *
	 * @since 1.11
	 *
	 * @param	string	Action to be taken - clear, fill, append, replace
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _update_product_tags( $action ) {
		global $wpdb;

		self::_build_product_attachments();

		$update_count = 0;
		$delete_count = 0;
		$terms_added = 0;
		$terms_removed = 0;

		foreach ( array_chunk( self::$product_attachments, 25, true ) as $chunk ) {
			// Find the Products that have Product Images
			$products = array();
			foreach ( $chunk as $product_id => $attachments ) {
				if ( ! empty( $attachments['_thumbnail_id'] ) ) {
					$id = absint( $attachments['_thumbnail_id'] );
					$products[ $product_id ] = $id;
				}
			}

			if ( count( $products ) == 0 ) {
				continue;
			}

			switch ( $action ) {
				case 'clear':
				case 'fill':
					// Select the attachments that have product_tag term assignments
					$ids = implode( ',', array_keys( $products ) );
					$query = sprintf( 'SELECT DISTINCT tr.object_id FROM %1$s as tr INNER JOIN %2$s as tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE ( tr.object_id IN ( %3$s ) AND tt.taxonomy = \'product_tag\' ) ', $wpdb->term_relationships, $wpdb->term_taxonomy, $ids );
					$assignments = $wpdb->get_col( $query );

					if ( 'clear' == $action ) {
						foreach ( $assignments as $assignment ) {
							wp_delete_object_term_relationships( $assignment, 'product_tag' ); 
							$update_count++;
						} // each assignment
					} else {
						// Find the products that have no assignments
						$assignments = array_diff_key( $products, array_flip( $assignments ) );

						foreach ( $assignments as $product_id => $assignment ) {
							$attachment_tags = wp_get_object_terms( $assignment, 'attachment_tag', array( 'orderby' => 'none', 'fields' => 'names' ) );

							if ( ! empty( $attachment_tags ) ) {
								$term_taxonomy_ids = wp_set_object_terms( $product_id, $attachment_tags, 'product_tag' );
								$terms_added += count( $term_taxonomy_ids );
								$update_count++;
							}
						} // each assignment
					}
					break;
				case 'append':
				case 'replace':
					foreach ( $products as $product_id => $assignment ) {
						$attachment_tags = wp_get_object_terms( $assignment, 'attachment_tag', array( 'orderby' => 'none', 'fields' => 'names' ) );

						if ( 'append' == $action ) {
							if ( ! empty( $attachment_tags ) ) {
								$old_term_taxonomy_ids = wp_get_object_terms( $product_id, 'product_tag', array( 'orderby' => 'none', 'fields' => 'tt_ids' ) );
								$term_taxonomy_ids = wp_set_object_terms( $product_id, $attachment_tags, 'product_tag', true );
								$new_terms = count( array_diff( $term_taxonomy_ids, $old_term_taxonomy_ids ) );
								if ( 0 < $new_terms ) {
									$terms_added += $new_terms;
									$update_count++;
								}
							} // common terms
						} else {
							if ( ! empty( $attachment_tags ) ) {
								$term_taxonomy_ids = wp_set_object_terms( $product_id, $attachment_tags, 'product_tag', false );
								$new_terms = count( $term_taxonomy_ids );
								if ( 0 < $new_terms ) {
									$terms_added += $new_terms;
									$update_count++;
								}
							} else {
								$term_taxonomy_ids = wp_get_object_terms( $product_id, 'product_tag', array( 'orderby' => 'none', 'fields' => 'tt_ids' ) );

								$old_terms = count( $term_taxonomy_ids );
								if ( 0 < $old_terms ) {
									$terms_removed += $old_terms;
									$delete_count++;
									$term_taxonomy_ids = wp_set_object_terms( $product_id, NULL, 'product_tag', false );
								}
							} // no common terms
						}
					} // each assignment
			} // action
		} // each chunk

		switch ( $action ) {
			case 'clear':
				return "_clear_product_tags() cleared {$update_count} Product(s).\n";
				break;
			case 'fill':
				return "_fill_product_tags() added {$terms_added} term(s) to {$update_count} Product(s).\n";
				break;
			case 'append':
				return "_append_product_tags() added {$terms_added} term(s) to {$update_count} Product(s).\n";
				break;
			case 'replace':
				return "_replace_product_tags() replaced {$terms_added} term(s) in {$update_count} Product(s), and deleted {$terms_removed} term(s) from {$delete_count} Product(s).\n";
		}

		return "ERROR: Unknown _update_product_tags action: {$action}";
	} // _update_product_tags

	/**
	 * Delete ALL Products' Product Categories assignments where a Product Image exists
 	 *
	 * @since 1.11
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _clear_product_categories() {
		return self::_update_product_categories( 'clear' );
	} // _clear_product_categories

	/**
	 * Fill empty Product Categories assignments from Product Image Att. Tags,
	 * where the Product Image Att. Tag matches an existing Product Category
 	 *
	 * @since 1.11
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _fill_product_categories() {
		return self::_update_product_categories( 'fill' );
	} // _fill_product_categories

	/**
	 * Append Product Categories assignments to ALL Products from Product Image Att. Tags,
	 * where the Product Image Att. Tag matches an existing Product Category
 	 *
	 * @since 1.11
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _append_product_categories() {
		return self::_update_product_categories( 'append' );
	} // _append_product_categories

	/**
	 * Replace ALL Product Categories assignments from Product Image Att. Tags,
	 * where the Product Image Att. Tag matches an existing Product Category
 	 *
	 * @since 1.11
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _replace_product_categories() {
		return self::_update_product_categories( 'replace' );
	} // _replace_product_categories

	/**
	 * Common code for the Product Category operations
 	 *
	 * @since 1.11
	 *
	 * @param	string	Action to be taken - clear, fill, append, replace
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _update_product_categories( $action ) {
		global $wpdb;

		self::_build_product_attachments();

		if ( 'clear' != $action ) {
			// Get the array of the Product Category term objects for comparison
			$product_categories = MLAQuery::mla_wp_get_terms( 'product_cat', array( 'orderby' => 'none', 'hide_empty' => 0, 'fields' => 'id=>name' ) );
		} else {
			$product_categories = array();
		}

		$update_count = 0;
		$delete_count = 0;
		$terms_added = 0;
		$terms_removed = 0;

		foreach ( array_chunk( self::$product_attachments, 25, true ) as $chunk ) {
			// Find the Products that have Product Images
			$products = array();
			foreach ( $chunk as $product_id => $attachments ) {
				if ( ! empty( $attachments['_thumbnail_id'] ) ) {
					$id = absint( $attachments['_thumbnail_id'] );
					$products[ $product_id ] = $id;
				}
			}

			if ( count( $products ) == 0 ) {
				continue;
			}

			switch ( $action ) {
				case 'clear':
				case 'fill':
					// Select the attachments that have product_cat term assignments
					$ids = implode( ',', array_keys( $products ) );
					$query = sprintf( 'SELECT DISTINCT tr.object_id FROM %1$s as tr INNER JOIN %2$s as tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE ( tr.object_id IN ( %3$s ) AND tt.taxonomy = \'product_cat\' ) ', $wpdb->term_relationships, $wpdb->term_taxonomy, $ids );
					$assignments = $wpdb->get_col( $query );

					if ( 'clear' == $action ) {
						foreach ( $assignments as $assignment ) {
							wp_delete_object_term_relationships( $assignment, 'product_cat' ); 
							$update_count++;
						} // each assignment
					} else {
						// Find the products that have no assignments
						$assignments = array_diff_key( $products, array_flip( $assignments ) );

						foreach ( $assignments as $product_id => $assignment ) {
							$attachment_tags = wp_get_object_terms( $assignment, 'attachment_tag', array( 'orderby' => 'none', 'fields' => 'names' ) );
							$common_terms = array_keys( array_intersect( $product_categories, $attachment_tags ) );

							if ( ! empty( $common_terms ) ) {
								$term_taxonomy_ids = wp_set_object_terms( $product_id, $common_terms, 'product_cat' );
								$terms_added += count( $term_taxonomy_ids );
								$update_count++;
							} // common terms
						} // each assignment
					}
					break;
				case 'append':
				case 'replace':
					foreach ( $products as $product_id => $assignment ) {
						$attachment_tags = wp_get_object_terms( $assignment, 'attachment_tag', array( 'orderby' => 'none', 'fields' => 'names' ) );
						$common_terms = array_keys( array_intersect( $product_categories, $attachment_tags ) );

						if ( 'append' == $action ) {
							if ( ! empty( $common_terms ) ) {
								$old_term_taxonomy_ids = wp_get_object_terms( $product_id, 'product_cat', array( 'orderby' => 'none', 'fields' => 'tt_ids' ) );
								$term_taxonomy_ids = wp_set_object_terms( $product_id, $common_terms, 'product_cat', true );
								$new_terms = count( array_diff( $term_taxonomy_ids, $old_term_taxonomy_ids ) );
								if ( 0 < $new_terms ) {
									$terms_added += $new_terms;
									$update_count++;
								}
							} // common terms
						} else {
							if ( ! empty( $common_terms ) ) {
								$term_taxonomy_ids = wp_set_object_terms( $product_id, $common_terms, 'product_cat', false );
								$new_terms = count( $term_taxonomy_ids );
								if ( 0 < $new_terms ) {
									$terms_added += $new_terms;
									$update_count++;
								}
							} else {
								$term_taxonomy_ids = wp_get_object_terms( $product_id, 'product_cat', array( 'orderby' => 'none', 'fields' => 'tt_ids' ) );

								$old_terms = count( $term_taxonomy_ids );
								if ( 0 < $old_terms ) {
									$terms_removed += $old_terms;
									$delete_count++;
									$term_taxonomy_ids = wp_set_object_terms( $product_id, NULL, 'product_cat', false );
								}
							} // no common terms
						}
					} // each assignment
			} // action
		} // each chunk

		switch ( $action ) {
			case 'clear':
				return "_clear_product_categories() cleared {$update_count} Product(s).\n";
				break;
			case 'fill':
				return "_fill_product_categories() added {$terms_added} term(s) to {$update_count} Product(s).\n";
				break;
			case 'append':
				return "_append_product_categories() added {$terms_added} term(s) to {$update_count} Product(s).\n";
				break;
			case 'replace':
				return "_replace_product_categories() replaced {$terms_added} term(s) in {$update_count} Product(s), and deleted {$terms_removed} term(s) from {$delete_count} Product(s).\n";
		}

		return "ERROR: Unknown _update_product_categories action: {$action}";
	} // _update_product_categories

	/**
	 * Delete ALL Att. Categories assignments
 	 *
	 * @since 1.11
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _clear_attachment_categories() {
		global $wpdb;

		$update_count = 0;
		$offset = 0;
		$limit = 25;

		do {
			// Select a chunk of attachment IDs
			$query = sprintf( 'SELECT p.ID FROM %1$s as p WHERE ( p.post_type = \'attachment\' ) ORDER BY p.ID LIMIT %2$d, %3$d', $wpdb->posts, $offset, $limit );
			$results = $wpdb->get_col( $query );

			if ( is_array( $results ) && count( $results ) > 0 ) {
				// Select the attachments that have attachment_category term assignments
				$ids = implode( ',', $results );
				$query = sprintf( 'SELECT DISTINCT tr.object_id FROM %1$s as tr INNER JOIN %2$s as tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE ( tr.object_id IN ( %3$s ) AND tt.taxonomy = \'attachment_category\' ) ', $wpdb->term_relationships, $wpdb->term_taxonomy, $ids );
				$assignments = $wpdb->get_col( $query );

				foreach ( $assignments as $assignment ) {
					wp_delete_object_term_relationships( $assignment, 'attachment_category' ); 
					$update_count++;
				}
			} else {
				$results = array();
			}

			$offset += $limit;
		} while ( count( $results ) == $limit );

		return "_clear_attachment_categories() cleared {$update_count} items(s).\n";
	} // _clear_attachment_categories

	/**
	 * Fill empty Att. Categories assignments from Att. Tags,
	 * where the Att. Tag matches an existing Att. Category
 	 *
	 * @since 1.11
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _fill_attachment_categories() {
		global $wpdb;

		// Get the array of the Att. Category term objects for comparison
		$attachment_categories = MLAQuery::mla_wp_get_terms( 'attachment_category', array( 'orderby' => 'none', 'hide_empty' => 0, 'fields' => 'id=>name' ) );

		$update_count = 0;
		$terms_added = 0;
		$offset = 0;
		$limit = 25;

		do {
			// Select a chunk of attachment IDs
			$query = sprintf( 'SELECT p.ID FROM %1$s as p WHERE ( p.post_type = \'attachment\' ) ORDER BY p.ID LIMIT %2$d, %3$d', $wpdb->posts, $offset, $limit );
			$results = $wpdb->get_col( $query );

			if ( is_array( $results ) && count( $results ) > 0 ) {
				// Select the attachments that have attachment_category term assignments
				$ids = implode( ',', $results );
				$query = sprintf( 'SELECT DISTINCT tr.object_id FROM %1$s as tr INNER JOIN %2$s as tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE ( tr.object_id IN ( %3$s ) AND tt.taxonomy = \'attachment_category\' ) ', $wpdb->term_relationships, $wpdb->term_taxonomy, $ids );
				$assignments = $wpdb->get_col( $query );

				// find the attachments that have no assignments
				$assignments = array_diff( $results, $assignments );

				foreach ( $assignments as $assignment ) {
					$attachment_tags = wp_get_object_terms( $assignment, 'attachment_tag', array( 'orderby' => 'none', 'fields' => 'names' ) );
					$common_terms = array_keys( array_intersect( $attachment_categories, $attachment_tags ) );
					if ( ! empty( $common_terms ) ) {
						$term_taxonomy_ids = wp_set_object_terms( $assignment, $common_terms, 'attachment_category' );
						$terms_added += count( $term_taxonomy_ids );
						$update_count++;
					}
				}
			} else {
				$results = array();
			}

			$offset += $limit;
		} while ( count( $results ) == $limit );

		return "_fill_attachment_categories() added {$terms_added} term(s) to {$update_count} item(s).\n";
	} // _fill_attachment_categories

	/**
	 * Append Att. Categories assignments to ALL items from Att. Tags,
	 * where the Att. Tag matches an existing Att. Category
 	 *
	 * @since 1.11
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _append_attachment_categories() {
		global $wpdb;

		// Get the array of the Att. Category term objects for comparison
		$attachment_categories = MLAQuery::mla_wp_get_terms( 'attachment_category', array( 'orderby' => 'none', 'hide_empty' => 0, 'fields' => 'id=>name' ) );

		$update_count = 0;
		$terms_added = 0;
		$offset = 0;
		$limit = 25;

		do {
			// Select a chunk of attachment IDs
			$query = sprintf( 'SELECT p.ID FROM %1$s as p WHERE ( p.post_type = \'attachment\' ) ORDER BY p.ID LIMIT %2$d, %3$d', $wpdb->posts, $offset, $limit );
			$results = $wpdb->get_col( $query );

			if ( is_array( $results ) && count( $results ) > 0 ) {
				// Select the attachments that have attachment_tag term assignments
				$ids = implode( ',', $results );
				$query = sprintf( 'SELECT DISTINCT tr.object_id FROM %1$s as tr INNER JOIN %2$s as tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE ( tr.object_id IN ( %3$s ) AND tt.taxonomy = \'attachment_tag\' ) ', $wpdb->term_relationships, $wpdb->term_taxonomy, $ids );
				$assignments = $wpdb->get_col( $query );

				foreach ( $assignments as $assignment ) {
					$attachment_tags = wp_get_object_terms( $assignment, 'attachment_tag', array( 'orderby' => 'none', 'fields' => 'names' ) );
					$common_terms = array_keys( array_intersect( $attachment_categories, $attachment_tags ) );
					if ( ! empty( $common_terms ) ) {
						$old_term_taxonomy_ids = wp_get_object_terms( $assignment, 'attachment_category', array( 'orderby' => 'none', 'fields' => 'tt_ids' ) );
						$term_taxonomy_ids = wp_set_object_terms( $assignment, $common_terms, 'attachment_category', true );

						$new_terms = count( array_diff( $term_taxonomy_ids, $old_term_taxonomy_ids ) );
						if ( 0 < $new_terms ) {
							$terms_added += $new_terms;
							$update_count++;
						}
					}
				}
			} else {
				$results = array();
			}

			$offset += $limit;
		} while ( count( $results ) == $limit );

		return "_append_attachment_categories() added {$terms_added} term(s) to {$update_count} item(s).\n";
	} // _append_attachment_categories

	/**
	 * Replace ALL Att. Categories assignments from Att. Tags,
	 * where the Att. Tag matches an existing Att. Category
 	 *
	 * @since 1.11
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _replace_attachment_categories() {
		global $wpdb;

		// Get the array of the Att. Category term objects for comparison
		$attachment_categories = MLAQuery::mla_wp_get_terms( 'attachment_category', array( 'orderby' => 'none', 'hide_empty' => 0, 'fields' => 'id=>name' ) );

		$update_count = 0;
		$delete_count = 0;
		$terms_added = 0;
		$terms_removed = 0;

		$offset = 0;
		$limit = 25;

		do {
			// Select a chunk of attachment IDs
			$query = sprintf( 'SELECT p.ID FROM %1$s as p WHERE ( p.post_type = \'attachment\' ) ORDER BY p.ID LIMIT %2$d, %3$d', $wpdb->posts, $offset, $limit );
			$results = $wpdb->get_col( $query );

			if ( is_array( $results ) && count( $results ) > 0 ) {
				foreach ( $results as $assignment ) {
					$attachment_tags = wp_get_object_terms( $assignment, 'attachment_tag', array( 'orderby' => 'none', 'fields' => 'names' ) );
					$common_terms = array_keys( array_intersect( $attachment_categories, $attachment_tags ) );

					if ( ! empty( $common_terms ) ) {
						$term_taxonomy_ids = wp_set_object_terms( $assignment, $common_terms, 'attachment_category' );
						$new_terms = count( $term_taxonomy_ids );
						if ( 0 < $new_terms ) {
							$terms_added += $new_terms;
							$update_count++;
						}
					} else {
						$term_taxonomy_ids = wp_get_object_terms( $assignment, 'attachment_category', array( 'orderby' => 'none', 'fields' => 'tt_ids' ) );
						$old_terms = count( $term_taxonomy_ids );
						if ( 0 < $old_terms ) {
							$terms_removed += $old_terms;
							$delete_count++;
							$term_taxonomy_ids = wp_set_object_terms( $assignment, NULL, 'attachment_category' );
						}
					} // no common terms
				}
			} else {
				$results = array();
			}

			$offset += $limit;
		} while ( count( $results ) == $limit );

		return "_replace_attachment_categories() replaced {$terms_added} term(s) in {$update_count} item(s), and deleted {$terms_removed} term(s) from {$delete_count} item(s).\n";
	} // _replace_attachment_categories

	/**
	 * Delete ALL product_category and/or product_tag term assignments
	 * for Media Library items.
 	 *
	 * @since 1.26
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _clear_term_assignments() {
		self::_build_product_attachments( false );

		$item_count = 0;
		$cat_count = 0;
		$cat_removed = 0;
		$tag_count = 0;
		$tag_removed = 0;

		foreach ( self::$attachment_products as $ID => $used_in ) {
			$item_count++;

			if ( self::$process_category ) {
				$term_taxonomy_ids = wp_get_object_terms( $ID, 'product_cat', array( 'orderby' => 'none', 'fields' => 'tt_ids' ) );
				$old_terms = count( $term_taxonomy_ids );
				if ( 0 < $old_terms ) {
					$cat_removed += $old_terms;
					$cat_count++;
					$term_taxonomy_ids = wp_set_object_terms( $ID, NULL, 'product_cat' );
				}
			}

			if ( self::$process_tag ) {
				$term_taxonomy_ids = wp_get_object_terms( $ID, 'product_tag', array( 'orderby' => 'none', 'fields' => 'tt_ids' ) );
				$old_terms = count( $term_taxonomy_ids );
				if ( 0 < $old_terms ) {
					$tag_removed += $old_terms;
					$tag_count++;
					$term_taxonomy_ids = wp_set_object_terms( $ID, NULL, 'product_tag' );
				}
			}
		} // foreach ID

		$cat_text = ( 1 == $cat_removed ) ? 'category' : 'categories';
		$tag_text = ( 1 == $tag_removed ) ? 'tag' : 'tags';
		return "_clear_term_assignments() processed {$item_count} item(s), deleted {$cat_removed} {$cat_text} from {$cat_count} item(s), and deleted {$tag_removed} {$tag_text} from {$tag_count} item(s).\n";
	} // _clear_term_assignments

	/**
	 * Append ALL product_category and/or product_tag term assignments
	 * for Media Library items.
 	 *
	 * @since 1.26
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _copy_term_assignments() {
		self::_build_product_attachments( false );

		$item_count = 0;
		$cat_count = 0;
		$cat_added = 0;
		$tag_count = 0;
		$tag_added = 0;

		$current_product_cat = array();
		$current_product_tag = array();

		foreach ( self::$attachment_products as $ID => $used_in ) {
			$item_count++;

			if ( self::$process_category ) {
				$current_product_cat = wp_get_object_terms( $ID, 'product_cat', array( 'orderby' => 'none', 'fields' => 'tt_ids' ) );
			}

			if ( self::$process_tag ) {
				$current_product_tag = wp_get_object_terms( $ID, 'product_tag', array( 'orderby' => 'none', 'fields' => 'tt_ids' ) );
			}

			$new_product_cat = array();
			$new_product_tag = array();

			foreach ( $used_in as $usage => $products ) {
				foreach ( $products as $product_id ) {
					if ( self::$process_category ) {
						$term_taxonomy_ids = wp_get_object_terms( $product_id, 'product_cat', array( 'orderby' => 'none', 'fields' => 'tt_ids' ) );
						foreach( $term_taxonomy_ids as $new_cat ) {
							if ( in_array( $new_cat, $current_product_cat ) ) {
								continue;
							}

							$new_product_cat[ $new_cat ] = $new_cat;
						}
					}

					if ( self::$process_tag ) {
						$term_taxonomy_ids = wp_get_object_terms( $product_id, 'product_tag', array( 'orderby' => 'none', 'fields' => 'tt_ids' ) );
						foreach( $term_taxonomy_ids as $new_tag ) {
							if ( in_array( $new_tag, $current_product_tag ) ) {
								continue;
							}

							$new_product_tag[ $new_tag ] = $new_tag;
						}
					}
				} // foreach product_id
			} // foreach usage

			if ( 0 < ( $new_terms = count( $new_product_cat ) ) ) {
				$cat_added += $new_terms;
				$cat_count++;
				$term_taxonomy_ids = wp_set_object_terms( $ID, $new_product_cat, 'product_cat', true );
			}

			if ( 0 < ( $new_terms = count( $new_product_tag ) ) ) {
				$tag_added += $new_terms;
				$tag_count++;
				$term_taxonomy_ids = wp_set_object_terms( $ID, $new_product_tag, 'product_tag', true );
			}
		} // foreach ID

		$cat_text = ( 1 == $cat_added ) ? 'category' : 'categories';
		$tag_text = ( 1 == $tag_added ) ? 'tag' : 'tags';
		return "_copy_term_assignments() processed {$item_count} item(s), added {$cat_added} {$cat_text} to {$cat_count} item(s), and added {$tag_added} {$tag_text} to {$tag_count} item(s).\n";
	} // _copy_term_assignments

	/**
	 * Restore default values for the Populate Product from Product Image templates
 	 *
	 * @since 2.06
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _populate_product_from_product_image() {
		global $wpdb;

		if ( ! empty( self::$first_product ) ) {
			$lower_bound = (integer) self::$first_product;
		} else {
			$lower_bound = 0;
		}

		if ( ! empty( self::$last_product ) ) {
			$upper_bound = (integer) self::$last_product;
		} elseif ( $lower_bound ) {
			$upper_bound = $lower_bound;
		} else {
			$upper_bound = 0x7FFFFFFF;
		}

		$query = sprintf( 'SELECT m.post_id, m.meta_key, m.meta_value, p.post_title, p.post_content, p.post_excerpt FROM %1$s as m INNER JOIN %2$s as p ON m.post_id = p.ID WHERE ( p.post_type = \'product\' ) AND ( p.ID >= %3$d ) AND ( p.ID <= %4$d) AND ( m.meta_key IN ( \'_thumbnail_id\', \'_sku\' ) ) GROUP BY m.post_id, m.meta_id ORDER BY m.post_id', $wpdb->postmeta, $wpdb->posts, $lower_bound, $upper_bound );
		$results = $wpdb->get_results( $query );
//error_log( __LINE__ . ' Woo_Fixit::_populate_product_from_product_image() $results = ' . var_export( $results, true ), 0 );

		$old_values = array();
		foreach ( $results as $result ) {
			$value = isset( $old_values[ $result->post_id ] ) ? $old_values[ $result->post_id ] : array();
			$value['post_title'] = $result->post_title;
			$value['post_content'] = $result->post_content;
			$value['post_excerpt'] = $result->post_excerpt;
			$value[$result->meta_key] = $result->meta_value;
			$old_values[ $result->post_id ] = $value;
		}

		unset( $reaults, $result );

		foreach( $old_values as $product_id => $value ) {
			// Find existing product_cat terms
			$terms = get_object_term_cache( $product_id, 'product_cat' );
			if ( false === $terms ) {
				$terms = wp_get_object_terms( $product_id, 'product_cat' );
				if ( is_wp_error( $terms ) ) {
//error_log( __LINE__ . " Woo_Fixit::_populate_product_from_product_image( $product_id, 'product_cat' ) terms error = " . var_export( $terms->get_error_messages(), true ), 0 );
					continue;
				}
//error_log( __LINE__ . " Woo_Fixit::_populate_product_from_product_image( $product_id, 'product_cat' ) terms = " . var_export( $terms, true ), 0 );

				wp_cache_add( $product_id, $terms, 'product_cat' . '_relationships' );
			}

			foreach ( $terms as $term ) {
				$old_values[ $product_id ]['product_cat_terms'][] = $term->term_id;
			}
			sort( $old_values[ $product_id ]['product_cat_terms'] );

			// Find existing product_tag terms
			$terms = get_object_term_cache( $product_id, 'product_tag' );
			if ( false === $terms ) {
				$terms = wp_get_object_terms( $product_id, 'product_tag' );
				if ( is_wp_error( $terms ) ) {
//error_log( __LINE__ . " Woo_Fixit::_populate_product_from_product_image( $product_id, 'product_cat' ) terms error = " . var_export( $terms->get_error_messages(), true ), 0 );
					continue;
				}
//error_log( __LINE__ . " Woo_Fixit::_populate_product_from_product_image( $product_id, 'product_cat' ) terms = " . var_export( $terms, true ), 0 );

				wp_cache_add( $product_id, $terms, 'product_tag' . '_relationships' );
			}

			foreach ( $terms as $term ) {
				$old_values[ $product_id ]['product_tag_terms'][] = $term->term_id;
			}
			sort( $old_values[ $product_id ]['product_tag_terms'] );
		} // foreach old_values ID
//error_log( __LINE__ . ' Woo_Fixit::_populate_product_from_product_image() $old_values = ' . var_export( $old_values, true ), 0 );

		$product_count = 0;
		$updated_count= 0;

		// Define the template
		$my_setting = array(
			'data_source' => 'template',
			'meta_name' => '',
			'option' => 'raw'
		);

		foreach ( $old_values as $product_id => $value ) {
			$product_count++;
			$update_count = 0;
			$attachment_id = $value['_thumbnail_id'];
			$replace_values = array();

			// Evaluate the template for the Product Name (post_title)
			if ( !empty( self::$settings[self::NAME_TEMPLATE] ) ) {
				$my_setting['meta_name'] = '(' . self::$settings[self::NAME_TEMPLATE] . ')';

				$template_value = trim( MLAOptions::mla_get_data_source( $attachment_id, 'single_attachment_mapping', $my_setting, NULL ) );
				if ( !empty( $template_value ) && ( $template_value !== $value['post_title'] ) ) {
					$replace_values[ 'post_title' ] = $template_value;
				}
			}

			// Evaluate the template for the Product Description (post_content)
			if ( !empty( self::$settings[self::DESCRIPTION_TEMPLATE] ) ) {
				$my_setting['meta_name'] = '(' . self::$settings[self::DESCRIPTION_TEMPLATE] . ')';

				$template_value = trim( MLAOptions::mla_get_data_source( $attachment_id, 'single_attachment_mapping', $my_setting, NULL ) );
				if ( !empty( $template_value ) && ( $template_value !== $value['post_content'] ) ) {
					$replace_values[ 'post_content' ] = $template_value;
				}
			}

			// Evaluate the template for the Product Short Description (post_excerpt)
			if ( !empty( self::$settings[self::SHORT_DESCRIPTION_TEMPLATE] ) ) {
				$my_setting['meta_name'] = '(' . self::$settings[self::SHORT_DESCRIPTION_TEMPLATE] . ')';

				$template_value = trim( MLAOptions::mla_get_data_source( $attachment_id, 'single_attachment_mapping', $my_setting, NULL ) );
				if ( !empty( $template_value ) && ( $template_value !== $value['post_excerpt'] ) ) {
					$replace_values[ 'post_excerpt' ] = $template_value;
				}
			}

//error_log( __LINE__ . ' Woo_Fixit::_populate_product_from_product_image() $replace_values = ' . var_export( $replace_values, true ), 0 );
			$update_count = count( $replace_values );
			if ( $update_count ) {
				$replace_values['ID'] = $product_id;
				$result = wp_update_post( $replace_values );
			} else {
				$result = NULL;
			}
//error_log( __LINE__ . " Woo_Fixit::_populate_product_from_product_image( $update_count ) result = " . var_export( $result, true ), 0 );

			// Evaluate the template for the Product Categories ( taxonomy product_cat )
			if ( !empty( self::$settings[self::CATEGORIES_TEMPLATE] ) ) {
				$my_setting['meta_name'] = '(' . self::$settings[self::CATEGORIES_TEMPLATE] . ')';

				$template_value = trim( MLAOptions::mla_get_data_source( $attachment_id, 'single_attachment_mapping', $my_setting, NULL ) );
//error_log( __LINE__ . " Woo_Fixit::_populate_product_from_product_image( $update_count ) template_value = " . var_export( $template_value, true ), 0 );
				if ( !empty( $template_value ) ) {
					/*
					 * Convert term names to term IDs, to avoid ambiguity.
					 * Adapted from edit_post() in /wp-admin/includes/post.php
					 */
					$comma = _x( ',', 'tag delimiter' );
					if ( ',' !== $comma ) {
						$template_value = str_replace( $comma, ',', $template_value );
					}
					$tags = explode( ',', trim( $template_value, " \n\t\r\0\x0B," ) );

					$clean_terms = array();
					foreach ( $tags as $tag ) {
						// Empty terms are invalid input.
						if ( empty( $tag ) ) {
							continue;
						}

						$tag = trim( $tag );
						$_term = MLAQuery::mla_wp_get_terms( 'product_cat', array(
							'name' => $tag,
							'fields' => 'ids',
							'hide_empty' => false,
						) );

						if ( ! empty( $_term ) ) {
							$clean_terms[] = intval( $_term[0] );
						} else {
							// No existing term was found, so pass the string. A new term will be created.
							$clean_terms[] = $tag;
						}
					}

					sort( $clean_terms );
//error_log( __LINE__ . " Woo_Fixit::_populate_product_from_product_image( $update_count ) clean_terms = " . var_export( $clean_terms, true ), 0 );
					if ( $clean_terms !== $value['product_cat_terms'] ) {
						$result = wp_set_object_terms( $product_id, $clean_terms, 'product_cat' );
						if ( is_wp_error( $result ) ) {
//error_log( __LINE__ . " Woo_Fixit::_populate_product_from_product_image( $product_id, 'product_cat' ) wp_get_object_terms error = " . var_export( $result->get_error_messages(), true ), 0 );
						} else {
							$update_count++;
						}
					} // terms unequal
				} // $template_value
			} // CATEGORIES_TEMPLATE

			// Evaluate the template for the Product Tags ( taxonomy product_tag )
			if ( !empty( self::$settings[self::TAGS_TEMPLATE] ) ) {
				$my_setting['meta_name'] = '(' . self::$settings[self::TAGS_TEMPLATE] . ')';

				$template_value = trim( MLAOptions::mla_get_data_source( $attachment_id, 'single_attachment_mapping', $my_setting, NULL ) );
//error_log( __LINE__ . " Woo_Fixit::_populate_product_from_product_image( $update_count ) template_value = " . var_export( $template_value, true ), 0 );
				if ( !empty( $template_value ) ) {
					/*
					 * Convert term names to term IDs, to avoid ambiguity.
					 * Adapted from edit_post() in /wp-admin/includes/post.php
					 */
					$comma = _x( ',', 'tag delimiter' );
					if ( ',' !== $comma ) {
						$template_value = str_replace( $comma, ',', $template_value );
					}
					$tags = explode( ',', trim( $template_value, " \n\t\r\0\x0B," ) );

					$clean_terms = array();
					foreach ( $tags as $tag ) {
						// Empty terms are invalid input.
						if ( empty( $tag ) ) {
							continue;
						}

						$tag = trim( $tag );
						$_term = MLAQuery::mla_wp_get_terms( 'product_tag', array(
							'name' => $tag,
							'fields' => 'ids',
							'hide_empty' => false,
						) );

						if ( ! empty( $_term ) ) {
							$clean_terms[] = intval( $_term[0] );
						} else {
							// No existing term was found, so pass the string. A new term will be created.
							$clean_terms[] = $tag;
						}
					}

					sort( $clean_terms );
//error_log( __LINE__ . " Woo_Fixit::_populate_product_from_product_image( $update_count ) clean_terms = " . var_export( $clean_terms, true ), 0 );
					if ( $clean_terms !== $value['product_tag_terms'] ) {
						$result = wp_set_object_terms( $product_id, $clean_terms, 'product_tag' );
						if ( is_wp_error( $result ) ) {
//error_log( __LINE__ . " Woo_Fixit::_populate_product_from_product_image( $product_id, 'product_tag' ) wp_get_object_terms error = " . var_export( $result->get_error_messages(), true ), 0 );
						} else {
							$update_count++;
						}
					} // terms unequal
				} // $template_value
			} // TAGS_TEMPLATE

			// Evaluate the template for the Product SKU ( postmeta _sku )
			if ( !empty( self::$settings[self::SKU_TEMPLATE] ) ) {
				$my_setting['meta_name'] = '(' . self::$settings[self::SKU_TEMPLATE] . ')';

				$template_value = trim( MLAOptions::mla_get_data_source( $attachment_id, 'single_attachment_mapping', $my_setting, NULL ) );
//error_log( __LINE__ . " Woo_Fixit::_populate_product_from_product_image( $update_count ) template_value = " . var_export( $template_value, true ), 0 );
				if ( !empty( $template_value ) && ( $template_value !== $value['_sku'] ) ) {
					$result = update_post_meta( $product_id, '_sku', $template_value );
					$update_count++;
				}
//error_log( __LINE__ . " Woo_Fixit::_populate_product_from_product_image( $update_count ) SKU result = " . var_export( $result, true ), 0 );
			} // SKU_TEMPLATE

			if ( $update_count ) {
				$updated_count++;
			}
		} // foreach product

		return "Populate Product examined {$product_count} Product(s) and updated {$updated_count} Product(s).";
	} // _populate_product_from_product_image

	/**
	 * Save the Populate Product from Product Image templates to the database
 	 *
	 * @since 2.06
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _save_product_templates() {
		return self::_save_setting_changes();
	} // _save_product_templates

	/**
	 * Load the Populate Product from Product Image templates from the database
 	 *
	 * @since 2.06
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _load_product_templates() {
		$result = self::_load_settings();

		return $result;
	} // _load_product_templates

	/**
	 * Restore default values for the Populate Product from Product Image templates
 	 *
	 * @since 2.06
	 *
	 * @return	string	HTML markup for results/messages
	 */
	private static function _restore_product_template_defaults() {
		self::$populate_pi_pg_on_upload_attr = self::DEFAULT_POPULATE_PI_PG_ON_UPLOAD ? ' checked="checked" ' : ' ';
		self::$populate_pi_pg_on_mmmw_attr = self::DEFAULT_POPULATE_PI_PG_ON_MMMW ? ' checked="checked" ' : ' ';

		self::$populate_on_add_attr = self::DEFAULT_POPULATE_ON_ADD ? ' checked="checked" ' : ' ';
		self::$populate_on_update_attr = self::DEFAULT_POPULATE_ON_UPDATE ? ' checked="checked" ' : ' ';

		return self::_delete_settings();
	} // _restore_product_template_defaults
} //Woo_Fixit

// Install the submenu at an early opportunity
add_action('init', 'Woo_Fixit::initialize');
?>
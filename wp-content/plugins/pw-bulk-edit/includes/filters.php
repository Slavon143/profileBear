<?php

/*
Copyright (C) Pimwick, LLC

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'PWBE_Filters' ) ) :

final class PWBE_Filters {

	public static function get() {
		global $wpdb;

		$filter_types = array(
			'categories'				=> array( 'name' => __( 'Category', 'woocommerce' ), 'type' => 'categories' ),
			'post_content'				=> array( 'name' => __( 'Description', 'woocommerce' ), 'type' => 'string' ),
			'post_title'				=> array( 'name' => __( 'Product name', 'woocommerce' ), 'type' => 'string' ),
			'regular_price'				=> array( 'name' => __( 'Regular price', 'woocommerce' ), 'type' => 'currency' ),
			'sale_price'				=> array( 'name' => __( 'Sale price', 'woocommerce' ), 'type' => 'currency' ),
			'sku'						=> array( 'name' => __( 'SKU', 'woocommerce' ), 'type' => 'string' ),
			'tags'						=> array( 'name' => __( 'Tag', 'woocommerce' ), 'type' => 'tags' ),
		);

		// Add any Attributes
		foreach ( wc_get_attribute_taxonomies() as $wc_attribute ) {
			$name = $wc_attribute->attribute_label;
			$value = 'pa_' . $wc_attribute->attribute_name;

			$filter_types[$value] = array( 'name' => $name, 'type' => 'attributes' );
		}

		$filter_types = apply_filters( 'pwbe_filter_types', $filter_types );

		PWBE_Filters::sort( $filter_types );

		return $filter_types;
	}

	private static function sort( &$filter_types ) {
		uasort( $filter_types, 'PWBE_Filters::name_compare');
	}

	private static function name_compare( $a, $b ) {
		return strnatcmp( $a['name'], $b['name'] );
	}
}

endif;

?>
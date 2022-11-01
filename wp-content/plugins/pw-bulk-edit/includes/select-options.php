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

if ( ! class_exists( 'PWBE_Select_Options' ) ) :

final class PWBE_Select_Options {

    private static $select_options = null;

    public static function get() {
        if ( PWBE_Select_Options::$select_options === null ) {
            PWBE_Select_Options::load();
        }

        return PWBE_Select_Options::$select_options;
    }

    private static function load() {
        global $wpdb;
        global $wp_post_statuses;

        // Split this into 2 queries due to a customer's DB having different collations.
        $wc_taxonomy_results = PWBE_DB::query( "
            SELECT
                DISTINCT
                taxonomy.taxonomy,
                taxonomy.term_id
            FROM
                {$wpdb->term_taxonomy} AS taxonomy
            WHERE
                CONVERT(taxonomy.taxonomy USING utf8) IN (SELECT CONVERT(CONCAT('pa_', attribute_name) USING utf8) FROM {$wpdb->prefix}woocommerce_attribute_taxonomies)
            ORDER BY
                taxonomy.taxonomy
        " );

        while ( $wc_taxonomy = PWBE_DB::fetch_object( $wc_taxonomy_results ) ) {
            $wc_term_results = PWBE_DB::query( $wpdb->prepare( "
                SELECT
                    DISTINCT
                    terms.name,
                    terms.slug
                FROM
                    {$wpdb->terms} AS terms
                WHERE
                    terms.term_id = %d
                    AND terms.slug NOT IN ('pwbe_null_value')
                ORDER BY
                    terms.name
            ", $wc_taxonomy->term_id ) );

            while ( $wc_term = PWBE_DB::fetch_object( $wc_term_results ) ) {
                $taxonomy = $wc_taxonomy->taxonomy;
                $slug = $wc_term->slug;
                $name = $wc_term->name;

                $select_options[$taxonomy][$slug]['name'] = $name;
                $select_options[$taxonomy][$slug]['visibility'] = 'both';
                $select_options['attribute_' . $taxonomy][$slug]['name'] = $name;
                $select_options['attribute_' . $taxonomy][$slug]['visibility'] = 'both';
            }

            PWBE_DB::free_result( $wc_term_results );
        }

        PWBE_DB::free_result( $wc_taxonomy_results );

        $select_options['_tax_status'] = array();
        $select_options['_tax_class'] = array();

        if ( function_exists( 'wc_tax_enabled' ) ) {
            $tax_enabled = wc_tax_enabled();
        } else {
            $tax_enabled = apply_filters( 'wc_tax_enabled', get_option( 'woocommerce_calc_taxes' ) === 'yes' );
        }

        if ( $tax_enabled ) {

            $select_options['_tax_status']['taxable']['name'] = __( 'Taxable', 'woocommerce' );
            $select_options['_tax_status']['taxable']['visibility'] = 'both';
            $select_options['_tax_status']['shipping']['name'] = __( 'Shipping only', 'woocommerce' );
            $select_options['_tax_status']['shipping']['visibility'] = 'both';
            $select_options['_tax_status']['none']['name'] = __( 'None', 'woocommerce' );
            $select_options['_tax_status']['none']['visibility'] = 'both';


            $select_options['_tax_class']['parent']['name'] = __( 'Same as parent', 'woocommerce' );
            $select_options['_tax_class']['parent']['visibility'] = 'variation';
            $select_options['_tax_class']['']['name'] = __( 'Standard', 'woocommerce' );
            $select_options['_tax_class']['']['visibility'] = 'both';

            $tax_classes = array();
            if ( method_exists( 'WC_Tax', 'get_tax_classes' ) ) {
                $tax_classes = WC_Tax::get_tax_classes();
            } else {
                $tax_classes = array_filter( array_map( 'trim', explode( "\n", get_option('woocommerce_tax_classes' ) ) ) );
            }

            if ( ! empty( $tax_classes ) ) {
                foreach ( $tax_classes as $class ) {
                    $select_options['_tax_class'][ sanitize_title( $class )]['name'] = esc_html( $class );
                    $select_options['_tax_class'][ sanitize_title( $class )]['visibility'] = 'both';
                }
            }
        }

        if ( PW_Bulk_Edit::wc_min_version( '3.0' ) ) {
            $stock_status_options = wc_get_product_stock_status_options();
            foreach ( $stock_status_options as $key => $value ) {
                $select_options['_stock_status'][ $key ]['name'] = $value;
                $select_options['_stock_status'][ $key ]['visibility'] = 'both';
            }

        } else {
            $select_options['_stock_status']['instock']['name'] = __( 'In stock', 'woocommerce' );
            $select_options['_stock_status']['instock']['visibility'] = 'both';
            $select_options['_stock_status']['outofstock']['name'] = __( 'Out of stock', 'woocommerce' );
            $select_options['_stock_status']['outofstock']['visibility'] = 'both';
        }

        $select_options['_backorders']['no']['name'] = __( 'Do not allow', 'woocommerce' );
        $select_options['_backorders']['no']['visibility'] = 'both';
        $select_options['_backorders']['notify']['name'] = __( 'Allow, but notify customer', 'woocommerce' );
        $select_options['_backorders']['notify']['visibility'] = 'both';
        $select_options['_backorders']['yes']['name'] = __( 'Allow', 'woocommerce' );
        $select_options['_backorders']['yes']['visibility'] = 'both';

        if ( PW_Bulk_Edit::wc_min_version( '3.0' ) ) {
            $visibility_options = wc_get_product_visibility_options();
        } else {
            $visibility_options = apply_filters( 'woocommerce_product_visibility_options', array(
                'visible' => __( 'Catalog/search', 'woocommerce' ),
                'catalog' => __( 'Catalog', 'woocommerce' ),
                'search'  => __( 'Search', 'woocommerce' ),
                'hidden'  => __( 'Hidden', 'woocommerce' )
            ) );
        }

        foreach ( $visibility_options as $key => $visibility ) {
            $select_options['_visibility'][$key]['name'] = esc_html( $visibility );
            $select_options['_visibility'][$key]['visibility'] = 'parent';
        }

        foreach ( $wp_post_statuses as $key => $post_status ) {
            if ( '1' == $post_status->show_in_admin_status_list ) {
                $select_options['post_status'][$key]['name'] = esc_html( $post_status->label );
                $select_options['post_status'][$key]['visibility'] = 'both';
            }
        }

        $select_options = apply_filters( 'pwbe_select_options', $select_options );

        PWBE_Select_Options::sort_select_options( $select_options );

        PWBE_Select_Options::$select_options = $select_options;
    }

    private static function sort_select_options( &$select_options ) {
        $ignore_options = apply_filters( 'pwbe_skip_sorting_options', array( 'product_type', '_visibility', '_stock_status', 'stock_statuses' ) );

        foreach ( $select_options as $f => &$values ) {
            if ( !in_array( $f, $ignore_options ) ) {
                uasort( $values, 'PWBE_Select_Options::name_compare');
                uksort( $values, 'PWBE_Select_Options::blanks_first' );
            }
        }
    }

    private static function name_compare( $a, $b ) {
        return strnatcmp( $a['name'], $b['name'] );
    }

    private static function blanks_first( $a, $b ) {
        if ( $a === PW_Bulk_Edit::NULL ) {
            return -2;
        } else if ( $a === '') {
            return -1;
        } else {
            return 0;
        }
    }
}

endif;

?>
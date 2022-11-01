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

if ( ! class_exists( 'PWBE_Save_Products' ) ) :

final class PWBE_Save_Products {

    public function save( $fields ) {
        global $wpdb;

        $wpdb->show_errors();

        $columns = PWBE_Columns::get();

        $products_with_price_changes = array();
        $updated_variable_products = array();

        $updated_product_ids = array();
        $updated_variation_ids = array();

        $featured_products_changed = false;

        foreach( $fields as $field ) {

            if ( !isset( $field['post_id'] ) ) {
                continue;
            }

            // Reset the time limit for each product to give time to process everything.
            @set_time_limit( 0 );

            $table = $this->get_column_value( $columns, $field['field'], 'table' );
            $product = wc_get_product( $field['post_id'] );

            if ( $table == 'post' ) {
                $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET $field[field] = %s WHERE ID = %d", stripslashes( $field['value'] ), $field['post_id'] ) );
                $db_err = PWBE_DB::error();
                if ( !empty( $db_err ) ) { return $db_err; }

            } else if ( $table == 'meta' ) {
                if ( $field['field'] == '_featured' ) {
                    $featured_products_changed = true;
                    if ( is_a( $product, 'WC_Product' ) ) {
                        $product->set_featured( $field['value'] );
                        $product->save();
                    }
                }

                if ( $field['field'] == '_visibility' && is_a( $product, 'WC_Product' ) ) {
                    $product->set_catalog_visibility( $field['value'] );
                    $product->save();

                } else {
                    if ( $field['value'] == PW_Bulk_Edit::NULL ) {
                        delete_post_meta( $field['post_id'], $field['field'] );
                    } else {
                        $set_property = 'set' . $field['field'];
                    	if ( method_exists( $product, $set_property ) ) {
                        	$product->$set_property( $field['value'] );
                            $product->save();
                        } else {
                        	update_post_meta( $field['post_id'], $field['field'], $field['value'] );
                        }
                    }

                    if ( $field['field'] == '_regular_price' ) {
                        if ( strpos($field['product_type'], 'subscription') !== false ) {
                            update_post_meta( 'post', $field['post_id'], '_subscription_price', $field['value'] );
                        }
                    }

                    // Keep track of any products whose prices have changed. We'll need to reprocess them.
                    if ( in_array( $field['field'], array( '_regular_price' ) ) ) {
                        if ( !in_array( $field['post_id'], $products_with_price_changes ) ) {
                            $products_with_price_changes[] = $field['post_id'];
                        }
                    }
                }
            }

            if ( $field['post_id'] != $field['parent_post_id'] ) {
                if ( !in_array( $field['parent_post_id'], $updated_variable_products ) ) {
                    $updated_variable_products[] = $field['parent_post_id'];
                }

                $updated_variation_ids[] = $field['post_id'];
                $updated_product_ids[] = $field['parent_post_id'];
            } else {
                $updated_product_ids[] = $field['post_id'];
            }

            wc_delete_product_transients( $field['post_id'] );
            wp_cache_delete( 'product-' . $field['post_id'], 'products' );
        }

        if ( count( $products_with_price_changes ) > 0 ) {
            //
            // Ensure product price is accurate for any changed prices.
            //
            $placeholders = implode( ', ', array_fill( 0, count( $products_with_price_changes ), '%d' ) );
            $where_clause = $wpdb->prepare( "post.ID IN ($placeholders) ", $products_with_price_changes );

            $wpdb->query("SET SQL_BIG_SELECTS=1");

            $price_changes = $wpdb->get_results("
                SELECT
                    post.ID,
                    regular_price.meta_value AS _regular_price,
                    sale_price.meta_value AS _sale_price,
                    sale_price_dates_from.meta_value AS _sale_price_dates_from,
                    sale_price_dates_to.meta_value AS _sale_price_dates_to
                FROM
                    {$wpdb->posts} AS post
                LEFT JOIN
                    {$wpdb->postmeta} AS regular_price ON (regular_price.post_id = post.ID AND regular_price.meta_key = '_regular_price')
                LEFT JOIN
                    {$wpdb->postmeta} AS sale_price ON (sale_price.post_id = post.ID AND sale_price.meta_key = '_sale_price')
                LEFT JOIN
                    {$wpdb->postmeta} AS sale_price_dates_from ON (sale_price_dates_from.post_id = post.ID AND sale_price_dates_from.meta_key = '_sale_price_dates_from')
                LEFT JOIN
                    {$wpdb->postmeta} AS sale_price_dates_to ON (sale_price_dates_to.post_id = post.ID AND sale_price_dates_to.meta_key = '_sale_price_dates_to')
                WHERE
                    $where_clause
            ");
            $db_err = PWBE_DB::error();
            if ( !empty( $db_err ) ) { return $db_err; }

            foreach ( $price_changes as $pc ) {
                $this->save_product_price(
                    $pc->ID,
                    $pc->_regular_price,
                    $pc->_sale_price,
                    $pc->_sale_price_dates_from ? date_i18n( 'Y-m-d', $pc->_sale_price_dates_from ) : '',
                    $pc->_sale_price_dates_to ? date_i18n( 'Y-m-d', $pc->_sale_price_dates_to ) : ''
                );
            }
        }

        foreach ( $updated_variable_products as $product_id ) {
            WC_Product_Variable::sync( $product_id );
            WC_Product_Variable::sync_stock_status( $product_id );
        }

        $updated_product_ids = array_unique( $updated_product_ids );
        foreach ( $updated_product_ids as $post_id ) {
            // This will sync things like "Stock Status", etc.
            if ( PW_Bulk_Edit::wc_min_version( '3.0' ) ) {
                $product = wc_get_product( $post_id );
                if ( $product ) {
                    $product->save();
                }
            }
            wc_delete_product_transients( $post_id );
            wp_cache_delete( 'product-' . $post_id, 'products' );
            clean_post_cache( $post_id );

            if ( isset( $product ) ) {
                do_action( 'woocommerce_update_product', $post_id, $product );
            } else {
                do_action( 'woocommerce_update_product', $post_id );
            }
        }

        $updated_variation_ids = array_unique( $updated_variation_ids );
        foreach ( $updated_variation_ids as $post_id ) {
            // This will sync things like "Stock Status", etc.
            $product = wc_get_product( $post_id );
            if ( PW_Bulk_Edit::wc_min_version( '3.0' ) ) {
                if ( $product ) {
                    $product->save();
                }
            }
            wc_delete_product_transients( $post_id );
            wp_cache_delete( 'product-' . $post_id, 'products' );
            clean_post_cache( $post_id );
            if ( PW_Bulk_Edit::wc_min_version( '3.7' ) ) {
                do_action( 'woocommerce_update_product_variation', $post_id, $product );
            } else {
                do_action( 'woocommerce_update_product_variation', $post_id );
            }
        }

        if ( $featured_products_changed ) {
            delete_transient( 'wc_featured_products' );
        }

        $wpdb->hide_errors();

        if ( class_exists( 'LiteSpeed_Cache_API' ) && method_exists( 'LiteSpeed_Cache_API', 'purge_all' ) ) {
            LiteSpeed_Cache_API::purge_all();
        }

        do_action( 'pwbe_after_save_products', $fields );

        return 'success';
    }

    function get_column_value( $columns, $field, $value ) {
        foreach ( $columns as $column ) {
            if ( is_array( $column ) && isset( $column['field'] ) ) {
                if ( $column['field'] === $field ) {
                    return $column[$value];
                }
            }
        }

        return null;
    }

    function save_product_price( $product_id, $regular_price, $sale_price = '', $date_from = '', $date_to = '' ) {
        $product_id    = absint( $product_id );
        $regular_price = wc_format_decimal( $regular_price );
        $sale_price    = $sale_price === '' ? '' : wc_format_decimal( $sale_price );
        $date_from     = wc_clean( $date_from );
        $date_to       = wc_clean( $date_to );

        update_post_meta( $product_id, '_regular_price', $regular_price );
        update_post_meta( $product_id, '_sale_price', $sale_price );

        // Save Dates
        update_post_meta( $product_id, '_sale_price_dates_from', $date_from ? strtotime( $date_from ) : '' );
        update_post_meta( $product_id, '_sale_price_dates_to', $date_to ? strtotime( $date_to ) : '' );

        if ( $date_to && ! $date_from ) {
            $date_from = strtotime( 'NOW', current_time( 'timestamp' ) );
            update_post_meta( $product_id, '_sale_price_dates_from', $date_from );
        }

        // Update price if on sale
        if ( '' !== $sale_price && '' === $date_to && '' === $date_from ) {
            update_post_meta( $product_id, '_price', $sale_price );
        } else {
            update_post_meta( $product_id, '_price', $regular_price );
        }

        if ( '' !== $sale_price && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
            update_post_meta( $product_id, '_price', $sale_price );
        }

        if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
            update_post_meta( $product_id, '_price', $regular_price );
            update_post_meta( $product_id, '_sale_price_dates_from', '' );
            update_post_meta( $product_id, '_sale_price_dates_to', '' );
        }
    }
}

endif;

?>
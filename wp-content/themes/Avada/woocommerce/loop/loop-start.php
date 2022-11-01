<?php
/**
 * Product Loop Start
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/loop-start.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Reset the column amount correctly for shop and archive pages.
if ( ! wc_get_loop_prop( 'is_shortcode' ) ) {
	if ( is_shop() ) {
		wc_set_loop_prop( 'columns', Avada()->settings->get( 'woocommerce_shop_page_columns' ) );
	}
	if ( is_product_category() ||
		is_product_tag() ||
		is_tax()
	) {
		$columns = Avada()->settings->get( 'woocommerce_archive_page_columns' );
		wc_set_loop_prop( 'columns', $columns );
	}
}

$column_class = ' products-' . wc_get_loop_prop( 'columns' );
?>
<ul class="products clearfix<?php echo esc_attr( $column_class ); ?>">

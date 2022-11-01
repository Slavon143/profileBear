<?php
/**
 * Admin page to mount Stock Manager.
 *
 * @package  woocommerce-stock-manager/admin/views
 * @version  2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stock = $this->stock();

/**
 * Save all data.
 */
$product_id = ( ! empty( $_POST['product_id'] ) ) ? wc_clean( wp_unslash( $_POST['product_id'] ) ) : 0; // phpcs:ignore
$product    = ( ! empty( $_POST ) ) ? wc_clean( wp_unslash( $_POST ) ) : array(); // phpcs:ignore
if ( ! empty( $product_id ) ) {
	$stock->save_all( $product );
	// add redirect.
}

/**
 * Save display option.
 */
$page_filter_display = ( ! empty( $_POST['page-filter-display'] ) ) ? wc_clean( wp_unslash( $_POST['page-filter-display'] ) ) : ''; // phpcs:ignore
if ( ! empty( $page_filter_display ) ) {
	$stock->save_filter_display( $product );
}

?>


<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<div id="woocommerce-stock-manager-app"></div>  

</div>
<?php

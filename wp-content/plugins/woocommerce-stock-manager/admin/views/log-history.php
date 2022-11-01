<?php
/**
 * Stock Log History detailed page.
 *
 * @package  woocommerce-stock-manager/admin/views/
 * @version  2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$product_id = ( ! empty( $_GET['product-history'] ) ) ? intval( wc_clean( wp_unslash( $_GET['product-history'] ) ) ) : 0; // phpcs:ignore
if ( ! empty( $product_id ) ) {
	$product = wc_get_product( $product_id );
	if ( ! $product instanceof WC_Product ) {
		return;
	}
} else {
	return;
}
?>

<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<div class="t-col-12">
		<div class="toret-box box-info">
			<div class="box-header">
				<h3 class="box-title"><?php echo wp_kses_post( $product->get_name() ); ?></h3>
			</div>
			<div class="box-body">
				<?php
				$data = $wpdb->get_results( // phpcs:ignore
							$wpdb->prepare( // phpcs:ignore
								"SELECT *
								FROM {$wpdb->prefix}stock_log
								WHERE product_id = %d",
								$product_id
							)
				);

				if ( ! empty( $data ) ) {
					?>
							<div class="clear"></div>
							<table class="table-bordered">
								<tr>
									<th><?php esc_html_e( 'Date', 'woocommerce-stock-manager' ); ?></th>
									<th><?php esc_html_e( 'Stock', 'woocommerce-stock-manager' ); ?></th>						        
								</tr>
							<?php
							foreach ( $data as $item ) {
								?>
								<tr>
									<td>
										<?php
										$offset = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
										echo esc_html( date_i18n( 'F j, Y @ h:i A', ( strtotime( $item->date_created ) + $offset ) ) );
										?>
									</td>
									<td><?php echo intval( $item->qty ); ?></td>
								</tr>
								<?php
							}
							?>
							</table>
							<?php
				} else {
					echo '<p>' . esc_html__( 'No result', 'woocommerce-stock-manager' ) . '</p>';
				}
				?>
			</div>
		</div>
	</div>   	
</div>
<?php

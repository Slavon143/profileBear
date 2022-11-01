<?php
/**
 * Stock Log page.
 *
 * @package  woocommerce-stock-manager/admin/views/
 * @version  2.8.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stock = $this->stock();

?>

<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<div class="t-col-12">
		<div class="toret-box box-info">
			<div class="box-header">
				<h3 class="box-title"></h3>
			</div>
			<div class="box-body">
				<div class="clear"></div>
					<table class="table-bordered">
						<tr>
							<th><?php esc_html_e( 'Product ID', 'woocommerce-stock-manager' ); ?></th>
							<th><?php esc_html_e( 'SKU', 'woocommerce-stock-manager' ); ?></th>
							<th><?php esc_html_e( 'Name', 'woocommerce-stock-manager' ); ?></th>
							<th><?php esc_html_e( 'Product type', 'woocommerce-stock-manager' ); ?></th>
							<th><?php esc_html_e( 'Parent ID', 'woocommerce-stock-manager' ); ?></th>
							<th><?php esc_html_e( 'Stock', 'woocommerce-stock-manager' ); ?></th>
							<th></th>
						</tr>
						<?php
							$get = ( ! empty( $_GET ) ) ? wc_clean( wp_unslash( $_GET ) ) : array(); // phpcs:ignore
							$products = ( ! empty( $get ) ) ? $stock->get_products( $get ) : array();
						if ( ! empty( $products->posts ) ) {
							foreach ( $products->posts as $item ) {
								$item_product = wc_get_product( $item->ID );
								if ( ! $item_product instanceof WC_Product ) {
									continue;
								}
								$product_type = $item_product->get_type();
								?>
									<tr>
										<td class="td_center"><?php echo esc_html( $item_product->get_id() ); ?></td>
										<td><?php echo esc_html( $item_product->get_sku() ); ?></td>
										<td>
											<a href="<?php echo esc_url( admin_url() . 'post.php?post=' . $item_product->get_id() . '&action=edit' ); ?>" target="_blank">
										<?php echo wp_kses_post( get_the_title( $item_product->get_id() ) ); ?>  
											</a>
										</td>
										<td><?php echo esc_html( $product_type ); ?></td>
										<td></td>
										<td class="td_center"><?php echo esc_html( $item_product->get_stock_quantity() ); ?></td>
										<td class="td_center">
									<?php if ( 'variable' !== $product_type ) { ?>
												<a class="btn btn-success" href="<?php echo esc_url( admin_url() . 'admin.php?page=stock-manager-log&product-history=' . $item_product->get_id() ); ?>"><?php esc_html_e( 'History', 'woocommerce-stock-manager' ); ?></a>
											<?php } ?>
										</td>
									</tr>
									<?php
									if ( 'variable' === $product_type ) {
										$args = array(
											'post_parent' => $item->ID,
											'post_type'   => 'product_variation',
											'numberposts' => -1,
											'post_status' => 'publish',
											'order_by'    => 'menu_order',
										);

										$variations_array = $item_product->get_children();
										foreach ( $variations_array as $vars ) {
											$item_product = wc_get_product( $vars );
											if ( ! $item_product instanceof WC_Product ) {
												continue;
											}
											$product_type = 'product variation';
											?>
												<tr>
													<td class="td_center"><?php echo esc_html( $item_product->get_id() ); ?></td>
													<td><?php echo esc_html( $item_product->get_sku() ); ?></td>
													<td>
														<a href="<?php echo esc_url( admin_url() . 'post.php?post=' . $item_product->get_parent_id() . '&action=edit' ); ?>" target="_blank">
														<?php echo wp_kses_post( get_the_title( $item_product->get_id() ) ); ?>  
														</a>
													</td>
													<td><?php echo esc_html( $product_type ); ?></td>
													<td class="td_center"><?php echo esc_html( $item->ID ); ?></td>
													<td class="td_center"><?php echo esc_html( $item_product->get_stock_quantity() ); ?></td>
													<td class="td_center">
														<a class="btn btn-success" href="<?php echo esc_url( admin_url() . 'admin.php?page=stock-manager-log&product-history=' . $item_product->get_id() ); ?>"><?php esc_html_e( 'History', 'woocommerce-stock-manager' ); ?></a>
													</td>
												</tr>
												<?php
										}
									}
							}
						}
						?>
					</table>
					<div class="clear"></div>
					<?php echo wp_kses_post( $stock->pagination( $products ) ); ?>
				</div>
			</div>
		</div>
<?php

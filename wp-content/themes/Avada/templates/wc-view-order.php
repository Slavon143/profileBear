<?php
/**
 * WooCommere View Order template.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.1
 */

// The $order_id is inherited from the Avada_Woocommerce::view_order() method.
$order = wc_get_order( $order_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
if ( ! $order ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	return;
}

$order_items           = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', [ 'completed', 'processing' ] ) );
$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
$downloads             = $order->get_downloadable_items();
$show_downloads        = $order->has_downloadable_item() && $order->is_download_permitted();
?>
<section class="avada-order-details woocommerce-content-box full-width">
	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h2>

	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
		<thead>
		<tr>
			<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
			<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
		</tr>
		</thead>
		<tbody>
			<?php
			do_action( 'woocommerce_order_details_before_order_table_items', $order );

			foreach ( $order_items as $item_id => $item ) :
				$product       = apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );
				$purchase_note = ( $product ) ? $product->get_purchase_note() : '';
				$is_visible    = $product && $product->is_visible();

				$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

				if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
					continue;
				}
				?>
				<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order ) ); ?>">
					<td class="woocommerce-table__product-name product-name">
						<div class="fusion-product-name-wrapper">
							<?php if ( $is_visible ) : ?>
								<span class="product-thumbnail">
									<?php $thumbnail = $product->get_image(); ?>
									<?php if ( ! $product_permalink ) : ?>
										<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<?php else : ?>
										<a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
									<?php endif; ?>
								</span>
							<?php endif; ?>

							<div class="product-info">
								<?php
								echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', esc_html( $item->get_quantity() ) ) . '</strong>', $item ); // phpcs:ignore WordPress.Security.EscapeOutput

								// Meta data.
								do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

								wc_display_item_meta( $item );
								wc_display_item_downloads( $item );

								do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
								?>
							</div>
						</div>
					</td>

					<td class="woocommerce-table__product-total product-total">
						<?php echo $order->get_formatted_line_subtotal( $item ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</td>
				</tr>

				<?php if ( $show_purchase_note && $purchase_note ) : ?>
					<tr class="woocommerce-table__product-purchase-note product-purchase-note">
						<td colspan="3"><?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php do_action( 'woocommerce_order_details_after_order_table_items', $order ); ?>
		</tbody>
		<tfoot>
			<?php foreach ( $order->get_order_item_totals() as $key => $total ) : ?>
				<tr>
					<th scope="row"><?php echo $total['label']; // phpcs:ignore WordPress.Security.EscapeOutput ?></th>
					<td class="product-total"><?php echo $total['value']; // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
				</tr>
			<?php endforeach; ?>
		</tfoot>
	</table>
	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
</section>

<?php if ( $show_customer_details ) : ?>
	<section class="avada-customer-details woocommerce-content-box full-width">
		<header>
			<h2><?php esc_attr_e( 'Customer details', 'woocommerce' ); ?></h2>
		</header>
		<dl class="customer_details">
			<?php $billing_email = $order->get_billing_email(); ?>
			<?php if ( $billing_email ) : ?>
				<dt><?php esc_attr_e( 'Email:', 'Avada' ); ?></dt> <dd><?php echo esc_attr( $billing_email ); ?> </dd><br />
			<?php endif; ?>

			<?php $billing_phone = $order->get_billing_phone(); ?>
			<?php if ( $billing_phone ) : ?>
				<dt><?php esc_attr_e( 'Phone:', 'Avada' ); ?></dt> <dd><?php echo esc_html( $billing_phone ); ?></dd>
			<?php endif; ?>

			<?php $customer_note = $order->get_customer_note(); ?>
			<?php if ( $customer_note ) : ?>
				<dt><?php esc_html_e( 'Note:', 'Avada' ); ?></dt> <dd><?php echo wptexturize( $customer_note ); // phpcs:ignore WordPress.Security.EscapeOutput ?></dd>
			<?php endif; ?>

			<?php
			// Additional customer details hook.
			do_action( 'woocommerce_order_details_after_customer_details', $order );
			?>
		</dl>

		<section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">
			<div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">

				<header class="title">
					<h3><?php esc_attr_e( 'Billing address', 'woocommerce' ); ?></h3>
				</header>

				<address>
					<p>
						<?php $address = $order->get_formatted_billing_address(); ?>
						<?php echo ( $address ) ? $address : esc_attr__( 'N/A', 'woocommerce' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</p>
				</address>
			</div>

			<?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() ) : ?>
				<div class="woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2">
					<header class="title">
						<h3><?php esc_attr_e( 'Shipping address', 'woocommerce' ); ?></h3>
					</header>
					<address>
						<p>
							<?php $address = $order->get_formatted_shipping_address(); ?>
							<?php echo ( $address ) ? $address : esc_attr__( 'N/A', 'woocommerce' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</p>
					</address>
				</div>
			<?php endif; ?>

			</section>
		<div class="clear"></div>

	</section>
<?php endif; ?>

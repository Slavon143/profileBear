<?php
/**
 * Setting page.
 *
 * @package  woocommerce-stock-manager/admin/views/
 * @version  2.8.0
 */

$save = ( ! empty( $_POST['save'] ) ) ? wc_clean( wp_unslash( $_POST['save'] ) ) : ''; // phpcs:ignore
if ( ! empty( $save ) ) {
	$old_styles = ( ! empty( $_POST['old_styles'] ) ) ? wc_clean( wp_unslash( $_POST['old_styles'] ) ) : ''; // phpcs:ignore
	if ( ! empty( $old_styles ) ) {
		update_option( 'woocommerce_stock_old_styles', $old_styles, 'no' );
	} else {
		delete_option( 'woocommerce_stock_old_styles' );
	}
}

$old_styles = get_option( 'woocommerce_stock_old_styles', 'no' );
?>

<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<div class="t-col-6">
		<div class="toret-box box-info">
			<div class="box-header">
				<h3 class="box-title"></h3>
			</div>
			<div class="box-body">
				<div class="clear"></div>
				<form method="post" action="" style="position:relative;">
					<table class="table-bordered">
						<tr>
							<th><?php esc_html_e( 'Active old styles', 'woocommerce-stock-manager' ); ?></th>
							<td><input type="checkbox" name="old_styles" value="ok" 
							<?php
							if ( 'ok' === $old_styles ) {
								echo 'checked="checked"'; }
							?>
							/></td>
						</tr>
					</table>
					<br>
					<input type="submit" name="save" class="btn btn-danger" />
				</form>
			</div>
		</div>
	</div>
</div>
<?php

<?php
/**
 * Import & Export product data
 *
 * @package   woocommerce-stock-manager/admin/views/
 * @version   2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stock = $this->stock();

/**
 * Function to convert stock file.
 *
 * @param string $s CSV file row headers.
 * @return converted
 */
function stock_auto_utf( $s ) {
	if ( preg_match( '#[\x80-\x{1FF}\x{2000}-\x{3FFF}]#u', $s ) ) {
		return $s;
	}

	if ( preg_match( '#[\x7F-\x9F\xBC]#', $s ) ) {
		return iconv( 'WINDOWS-1250', 'UTF-8', $s );
	}

	return iconv( 'ISO-8859-2', 'UTF-8', $s );
}

?>
<script type="text/javascript">
	jQuery( function() {
		function convertArrayOfObjectsToCSV(args) {  
			var result, ctr, keys, columnDelimiter, lineDelimiter, data;
			data = args.datas || null;
			if (data == null || !data.length) {
				return null;
			}
			columnDelimiter = args.columnDelimiter || ',';
			lineDelimiter = args.lineDelimiter || '\n';
			keys = Object.keys(data[0]);
			result = '';
			result += keys.join(columnDelimiter);
			result += lineDelimiter;
			data.forEach(function(item) {
				ctr = 0;
				keys.forEach(function(key) {
					if (ctr > 0) result += columnDelimiter;

					result += item[key];
					ctr++;
				});
				result += lineDelimiter;
			});
			return result;
		}
		jQuery('.product-export').on( 'click', function(e){  
			e.preventDefault();
			jQuery( '.export-output' ).empty();
			jQuery( '#csv' ).append( 'Generating csv file' );
			jQuery( '#csv' ).css( 'display','block' );
			var offset = '0';
			wsm_export_products( offset );        
			function wsm_export_products( offset ) {
				var data = {
					'action'   : 'wsm_get_products_or_export',
					'security' : '<?php echo esc_html( wp_create_nonce( 'sa-wsm-export' ) ); ?>',
					'offset'   : offset
				};
				jQuery.post(ajaxurl, data, function( response ) {
					var result = jQuery.parseJSON( response );
					if( result.status != 'finish' ){
						var jsonObject = JSON.stringify(result.data);
						jsonObject = jsonObject.slice( 1 );
						jsonObject = jsonObject.slice(0, -1);
						jQuery( '.export-output' ).append( jsonObject + ',' );
						wsm_export_products( result.offset );
					} else {
						var string = jQuery( '.export-output' ).text();
						string = string.slice(0, -1);
						string = '{' + string + '}';
						jQuery( '#csv' ).empty();
						jQuery( '#csv' ).append( 'All done!' );
						var data = {
							'action'   : 'wsm_get_csv_file',
							'security' : '<?php echo esc_html( wp_create_nonce( 'sa-wsm-get-csv' ) ); ?>',
							'data'     : string
						};
						jQuery.post(ajaxurl, data, function( response ) {
							var data, filename, link;
							var csv = convertArrayOfObjectsToCSV({
								datas: JSON.parse( response )
							});
							if (csv == null) return;
							filename = 'stock-manager-export.csv';
							if (!csv.match(/^data:text\/csv/i)) {
								csv = 'data:text/csv;charset=utf-8,' + csv;
							}
							data = encodeURI(csv);
							link = document.createElement('a');
							link.setAttribute('href', data);
							link.setAttribute('download', filename);
							link.click();
						});
					}
				});
			}
		});
	});
</script>
<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<div class="t-col-6">
		<div class="toret-box box-info">
			<div class="box-header">
				<h3 class="box-title"><?php esc_html_e( 'Import', 'woocommerce-stock-manager' ); ?></h3>
			</div>
			<div class="box-body">
				<h4><?php esc_html_e( 'You can upload csv file, with your stock data. ', 'woocommerce-stock-manager' ); ?></h4>
				<p><?php esc_html_e( 'CSV file must be in this format, or you can export file with exist data and edit them. ', 'woocommerce-stock-manager' ); ?></p>
				<p><?php esc_html_e( 'If you have a lot of products and export/import not working, increase memory limit.. ', 'woocommerce-stock-manager' ); ?></p>
				<h3><?php esc_html_e( 'File format', 'woocommerce-stock-manager' ); ?></h3>
				<table class="table-bordered">
					<tr>
						<td><?php esc_html_e( 'ID', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( 'SKU', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( 'Product name', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( 'Manage stock', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( 'Stock status', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( 'Backorders', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( 'Stock', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( 'Product type', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( 'Parent ID', 'woocommerce-stock-manager' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( '123', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( 'abc111', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( 'T-shirt', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( 'yes', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( 'instock', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( 'yes', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( '10', 'woocommerce-stock-manager' ); ?></td>
						<td><?php esc_html_e( 'simple', 'woocommerce-stock-manager' ); ?></td>
						<td></td>
					</tr>
				</table>
				<ul>
					<li><strong><?php esc_html_e( 'ID', 'woocommerce-stock-manager' ); ?></strong> <?php esc_html_e( 'product id, required. Neccessary for import and export.', 'woocommerce-stock-manager' ); ?></li>
					<li><strong><?php esc_html_e( 'SKU', 'woocommerce-stock-manager' ); ?></strong> <?php esc_html_e( 'product unique identificator.', 'woocommerce-stock-manager' ); ?></li>
					<li><strong><?php esc_html_e( 'Manage stock', 'woocommerce-stock-manager' ); ?></strong> <?php esc_html_e( 'values: "yes", "notify", "no". If is empty "no" will be save.', 'woocommerce-stock-manager' ); ?></li>
					<li><strong><?php esc_html_e( 'Stock status', 'woocommerce-stock-manager' ); ?></strong> <?php esc_html_e( 'values: "instock", "outofstock". If is empty "outofstock" will be save.', 'woocommerce-stock-manager' ); ?></li>
					<li><strong><?php esc_html_e( 'Backorders', 'woocommerce-stock-manager' ); ?></strong> <?php esc_html_e( 'values: "yes", "notify", "no". If is empty "no" will be save.', 'woocommerce-stock-manager' ); ?></li>
					<li><strong><?php esc_html_e( 'Stock', 'woocommerce-stock-manager' ); ?></strong> <?php esc_html_e( 'quantity value. If is empty, 0 will be save.', 'woocommerce-stock-manager' ); ?></li>
				</ul>
				<form method="post" action="" class="setting-form" enctype="multipart/form-data">	
					<table class="table-bordered">
						<tr>
							<th><?php esc_html_e( 'Upload csv file', 'woocommerce-stock-manager' ); ?></th>
							<td>
								<input type="file" name="uploadFile">
							</td>
						</tr>
					</table>
					<div class="clear"></div>
					<input type="hidden" name="upload" value="ok" />
					<?php wp_nonce_field( 'sa-wsm-import', 'sa_wsm_nonce' ); ?>
					<input type="submit" class="btn btn-info" value="<?php esc_html_e( 'Upload', 'woocommerce-stock-manager' ); ?>" />
				</form>
				<?php
				if ( isset( $_POST['upload'] ) && ! empty( $_FILES ) ) {
					$post_sa_wsm_nonce = ( ! empty( $_POST['sa_wsm_nonce'] ) ) ? wc_clean( wp_unslash( $_POST['sa_wsm_nonce'] ) ) : ''; // phpcs:ignore
					if ( ! empty( $post_sa_wsm_nonce ) && wp_verify_nonce( $post_sa_wsm_nonce, 'sa-wsm-import' ) ) {
						// Allowed filetypes for import.
						$valid_filetypes = array(
							'csv' => 'text/csv',
						);

						// Retrieve the file type from the file name.
						$uploaded_file = $_FILES['uploadFile']['name']; // phpcs:ignore
						$filetype      = wp_check_filetype( wc_clean( wp_unslash( $uploaded_file ) ), $valid_filetypes );

						// Check if file type is valid.
						if ( in_array( $filetype['type'], $valid_filetypes, true ) ) {
							$target_dir = STOCKDIR . 'admin/views/upload/';
							$target_dir = $target_dir . basename( $uploaded_file );

							if ( move_uploaded_file( $_FILES['uploadFile']['tmp_name'], $target_dir ) ) { // phpcs:ignore

								/* translators: 1: Uploaded file name */
								echo sprintf( esc_html__( 'The file %1$s has been uploaded', 'woocommerce-stock-manager' ), basename( $uploaded_file ) ); // phpcs:ignore

								$row    = 1;
								$handle = fopen( $target_dir, 'r' ); // phpcs:ignore
								if ( false !== $handle ) {

									// assigning to $data to not consider first row of CSV file.
									while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) { // phpcs:ignore
										$num = count( $data );

										$product_id   = stock_auto_utf( $data[0] );
										$sku          = stock_auto_utf( $data[1] );
										$manage_stock = stock_auto_utf( $data[3] );
										$stock_status = stock_auto_utf( $data[4] );
										$backorders   = stock_auto_utf( $data[5] );
										$stock        = stock_auto_utf( $data[6] );

										if ( 1 !== $row ) {
											if ( ! empty( $product_id ) ) {
												$values = array(
													'sku' => $sku,
													'manage_stock' => $manage_stock,
													'stock_status' => $stock_status,
													'backorders' => $backorders,
													'stock' => $stock,
												);

												WSM_Save::save_one_item( $values, $product_id );

												/* translators: 1: P tag opening 2: Updated Product ID 3. Closing p tag */
												echo sprintf( esc_html__( '%1$s Product with ID %2$s was updated. %3$s', 'woocommerce-stock-manager' ), '<p>', wp_kses_post( $product_id ), '</p>' );
											}
										}
										$row++;

									}
									fclose( $handle ); // phpcs:ignore
								}
							} else {
								echo '<p>' . esc_html__( 'Sorry, there was an error uploading your file.', 'woocommerce-stock-manager' ) . '</p>';
							}
						} else {
							echo '<h3 class="wsm-upload-failed">' . esc_html__( 'Error: You have not uploaded a CSV file.', 'woocommerce-stock-manager' ) . '</h3>';
						}
					} else {
						wp_die( 'Could not verify nonce' );
					}
				}
				?>
			</div>
		</div>
	</div>

	<div class="t-col-6">
		<div class="toret-box box-info">
			<div class="box-header">
				<h3 class="box-title"><?php esc_html_e( 'Export', 'woocommerce-stock-manager' ); ?></h3>
			</div>
			<div class="box-body">
				<h4><?php esc_html_e( 'You can download csv file, with your stock data. ', 'woocommerce-stock-manager' ); ?></h4>
				<p><a href="#" class="btn btn-danger product-export"><?php esc_html_e( 'Create export file', 'woocommerce-stock-manager' ); ?></a></p> 
				<div class="export-output" style="display:none;"></div>
				<div id="csv" style="display:none;"></div>
			</div>
		</div>
	</div>

</div>
<?php

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

global $wpdb;

ob_implicit_flush(true);
ob_end_flush();

$sql_builder = new PWBE_SQL_Builder();
$products = $sql_builder->get_products( $_POST );
$views = PWBE_Views::get();

$product_columns = PWBE_Columns::get();

$hidden_columns = array();
$selected_view = get_option( 'pwbe_selected_view', 'pwbeview_all' );
if ( isset( $views[ $selected_view ] ) ) {
	$hidden_columns = $views[ $selected_view ];
}

?>
<input type="hidden" id="pwbe-price-thousand-separator" value="<?php echo wc_get_price_thousand_separator(); ?>" />
<input type="hidden" id="pwbe-price-decimal-separator" value="<?php echo wc_get_price_decimal_separator(); ?>" />
<input type="hidden" id="pwbe-price-decimal-places" value="<?php echo wc_get_price_decimals(); ?>" />
<div id="pwbe-results-error" class="pwbe-error pwbe-hidden"></div>
<div id="pwbe-results-container">
<?php
	if ( !is_string( $products ) ) {
		if ( PWBE_DB::num_rows( $products ) > 0 ) {
			?>
			<p class="pwbe-results-buttons">
				<button id="pwbe-product-save-button" class="button button-primary" title="<?php _e( 'Save Products', 'pw-bulk-edit' ); ?>" disabled="disabled"><i class='fa fa-floppy-o fa-fw' aria-hidden='true'></i> <?php _e( 'Save Changes', 'pw-bulk-edit' ); ?></button>
				<button id="pwbe-product-undo-button" class="button button-secondary pwbe-product-undo-button" title="<?php _e( 'Undo', 'pw-bulk-edit' ); ?>" disabled="disabled"><i class='fa fa-undo fa-fw' aria-hidden='true'></i></button>
				<button id="pwbe-product-redo-button" class="button button-secondary" title="<?php _e( 'Redo', 'pw-bulk-edit' ); ?>" disabled="disabled"><i class='fa fa-repeat fa-fw' aria-hidden='true'></i></button>
				<button id="pwbe-product-discard-button" class="button pwbe-button-red" title="<?php _e( 'Discard All Changes', 'pw-bulk-edit' ); ?>" disabled="disabled"><i class='fa fa-refresh fa-fw' aria-hidden='true'></i></button>
				<span id="pwbe-records-found"></span>
				<span id="pwbe-view-container">
					<strong>View </strong>
					<select id="pwbe-view" name="pwbe_view">
						<?php
							foreach( $views as $key => $value ) {
								$view = htmlspecialchars( $key, ENT_COMPAT );
								$view_name = $view;

								if ( $view == 'pwbeview_all' ) {
									$view_name = __( 'All Columns', 'pw-bulk-edit' );
								} else if ( $view == 'pwbeview_default' ) {
									$view_name = __( 'Standard Columns', 'pw-bulk-edit' );
								}

								echo "<option value=\"$view\" " . selected( $selected_view, $key, false ) . ">$view_name</option>\n";
							}

						?>
					</select>
                    <span id="pwbe-view-copy" class="pwbe-link pwbe-filter-toolbar-button" title="<?php _e( 'Copy View', 'pw-bulk-edit' ); ?>"><i class="fa fa-files-o fa-fw"></i></span>
					<span id="pwbe-view-edit" class="pwbe-link pwbe-filter-toolbar-button <?php if ( empty( $selected_view ) || PW_Bulk_Edit::starts_with( 'pwbeview_', $selected_view ) ) { echo 'pwbe-hidden'; } ?>" title="<?php _e( 'Edit View', 'pw-bulk-edit' ); ?>"><i class="fa fa-pencil-square-o fa-fw"></i></span>
					<span id="pwbe-view-delete" class="pwbe-link pwbe-filter-toolbar-button <?php if ( empty( $selected_view ) || PW_Bulk_Edit::starts_with( 'pwbeview_', $selected_view ) ) { echo 'pwbe-hidden'; } ?>" title="<?php _e( 'Delete View', 'pw-bulk-edit' ); ?>"><i class="fa fa-trash-o fa-fw"></i></span>
				</span>
			</p>
			<form id="pwbe-results-form">
				<div class="pwbe-table pwbe-results-table">
					<div id="pwbe-header-fixed" class="pwbe-thead"></div>
					<div id="pwbe-header-results" class="pwbe-thead">
						<div class="pwbe-tr">
							<div class="pwbe-td pwbe-results-table-td pwbe-results-table-header-td pwbe-row-checkbox"><input type="checkbox" class="pwbe-checkall" checked="checked" /></div>
							<div class="pwbe-td pwbe-results-table-td pwbe-results-table-header-td pwbe-view-in-woo">&nbsp;</div>
							<?php
								foreach ( $product_columns as $column ) {
									if ( $column['visibility'] != 'none' ) {

										if ( in_array( $column['field'], $hidden_columns ) ) {
											$hidden = 'pwbe-hidden-column';
										} else {
											$hidden = '';
										}

										echo "
											<div class='pwbe-td pwbe-results-table-td pwbe-results-table-header-td $hidden' data-field='$column[field]'>
												<span class='pwbe-header' data-type='$column[type]' data-field='$column[field]' data-readonly='$column[readonly]' data-sortable='$column[sortable]'>$column[name]</span>&nbsp;";

												if ( $_POST['order_by'] == $column['field'] ) {
													if ( empty( $_POST['order_by_desc'] ) ) {
														echo "<i class='fa fa-sort-asc' aria-hidden='true'></i>";
													} else {
														echo "<i class='fa fa-sort-desc' aria-hidden='true'></i>";
													}
												}

										echo '</div>';
									}
								}
							?>
						</div>
					</div>
					<div class="pwbe-tbody">
						<?php

							$i = 0;
							$result_limit_exceeded = false;

							while ( $product = PWBE_DB::fetch_object( $products ) ) {
								if ( $i > PWBE_MAX_RESULTS ) {
									$result_limit_exceeded = true;
									break;
								} else {
									$i++;
								}

								$meta_rows = $wpdb->get_results( $wpdb->prepare( "
									SELECT
										DISTINCT
										postmeta.meta_key AS name,
										CASE
											WHEN postmeta.meta_key = '_tax_status' THEN COALESCE(NULLIF(postmeta.meta_value, ''), 'taxable')
											WHEN postmeta.meta_key = '_manage_stock' THEN COALESCE(NULLIF(postmeta.meta_value, ''), 'no')
											WHEN postmeta.meta_key = '_stock' THEN CAST(postmeta.meta_value AS SIGNED)
											WHEN postmeta.meta_key = '_backorders' THEN COALESCE(NULLIF(postmeta.meta_value, ''), 'no')
											WHEN postmeta.meta_key = '_stock_status' THEN COALESCE(NULLIF(postmeta.meta_value, ''), 'instock')
											WHEN postmeta.meta_key = '_variation_description' THEN COALESCE(NULLIF(postmeta.meta_value, ''), '')
											WHEN postmeta.meta_key = '_featured' THEN COALESCE(NULLIF(postmeta.meta_value, ''), 'no')
											WHEN postmeta.meta_key = '_tax_class' THEN COALESCE(postmeta.meta_value, '" . PW_Bulk_Edit::NULL . "')
											WHEN postmeta.meta_key = '_visibility' THEN COALESCE(NULLIF(postmeta.meta_value, ''), '" . apply_filters( 'woocommerce_product_visibility_default' , 'visible' ) . "')
											ELSE postmeta.meta_value
										END AS value
									FROM
										{$wpdb->postmeta} AS postmeta
									WHERE
										postmeta.post_id = %d
								", $product->post_id ) );

								foreach ( $meta_rows as $meta ) {
									if ( !empty( $meta->name ) ) {
										$product->{$meta->name} = $meta->value;
									}
								}

								if ( PW_Bulk_Edit::wc_min_version( '3.0' ) ) {
									$p = wc_get_product( $product->post_id );
									$product->_featured = $p->get_featured() ? 'yes' : 'no';
									$product->_visibility = $p->get_catalog_visibility();
								}

								if ( !property_exists( $product, '_visibility' ) || empty( $product->_visibility ) ) {
									$product->_visibility = apply_filters( 'woocommerce_product_visibility_default' , 'visible' );
								}

								?>
								<div class="pwbe-tr pwbe-product-tr pwbe-product-tr-selected <?php if ( $product->product_type == 'variation' ) { echo 'pwbe-tr-variation'; } else { echo 'pwbe-tr-product'; } ?>">
									<div class="pwbe-td pwbe-results-table-td pwbe-row-checkbox"><input class="pwbe-product-checkbox" id="pwbe-product-<?php echo $product->post_id; ?>" name="post[]" type="checkbox" value="<?php echo $product->post_id; ?>" checked="checked"></div>
									<div class="pwbe-td pwbe-results-table-td pwbe-view-in-woo">
										<?php
											if ( $product->product_type != 'variation' ) {
												?>
												<a class="pwbe-view-in-woo-link" target="_blank" title="<?php _e( 'View Product in WooCommerce', 'pw-bulk-edit' ); ?>" href="<?php echo get_edit_post_link( $product->post_id, 'edit' ); ?>"><i class="fa fa-external-link fa-fw" aria-hidden="true"></i></a>
												<?php
											}
										?>
									</div>
									<?php
										foreach ( $product_columns as $column ) {
											if ( $column['visibility'] != 'none' ) {

												if ( in_array( $column['field'], $hidden_columns ) ) {
													$hidden = 'pwbe-hidden-cell';
												} else {
													$hidden = '';
												}

												echo pwbe_field( $product, $column, $hidden );
											}
										}
									?>
								</div>
								<?php
							}

							PWBE_DB::free_result( $products );
						?>
						<script>
							<?php
								if ( true === $result_limit_exceeded ) {
                                    ?>
                                    jQuery('#pwbe-records-found').html('<?php printf( __( 'Maximum %s records found', 'pw-bulk-edit' ), number_format( PWBE_MAX_RESULTS ) ); ?> [<a href="#" onClick="alert(\'<?php _e( 'If you would like to increase this limit, set PWBE_MAX_RESULTS in pw-bulk-edit.php. NOTE: this limit is in place due to browser limitations. Increasing this value may cause unexpected behavior! Instead, we suggest adding additional filters to lower the number of products found.', 'pw-bulk-edit' ); ?>\'); return false;">?</a>] ');
                                    <?php
                                } else {
                                    ?>
                                    jQuery('#pwbe-records-found').html('<?php printf( __( '%s records found', 'pw-bulk-edit' ), number_format( $i ) ); ?>');
                                    <?php
								}
							?>
						</script>
					</div>
				</div>
			</form>
			<div id="pwbe-bulkedit-dialog" class="pwbe-dialog" tabindex="0">
				<div class="pwbe-dialog-heading">
					<i class="fa fa-database"></i> Bulk Edit <span class="pwbe-bulkedit-field-name"></span>
				</div>
				<div class="pwbe-dialog-container">
					<p>
						The Bulk Editor will make changes to all checked items in the grid.
					</p>
					<?php
						require_once( 'bulk_editors/checkbox.php' );
						require_once( 'bulk_editors/currency.php' );
						require_once( 'bulk_editors/number.php' );
						require_once( 'bulk_editors/select.php' );
						require_once( 'bulk_editors/text.php' );
					?>
					<div class="pwbe-dialog-button-container">
						<button id="pwbe-bulkedit-dialog-button-apply" class="button button-primary pwbe-dialog-button-apply">Apply</button>
						<button id="pwbe-bulkedit-dialog-button-cancel" class="button button-secondary pwbe-dialog-button-cancel">Cancel</button>
					</div>
				</div>
			</div>
			<?php
		} else {
			?>
			<h3>No products found matching the filter criteria. <i class="fa fa-frown-o" aria-hidden="true"></i></h3>
			<?php
		}
	} else {
		?>
		<div class="pwbe-filter-error-heading">There was an error while filtering. Please send an email to <a href="mailto:us@pimwick.com">us@pimwick.com</a> with the following information:</div>
		<div class="pwbe-filter-error-message"><?php echo $products; ?></div>
		<?php
	}
?>
</div>
<div id="pwbe-edit-view-dialog" class="pwbe-dialog">
	<div class="pwbe-dialog-heading">
		<i class="fa fa-filter"></i> <span class="pwbe-filter-manager-dialog-name">Edit View</span>
		<a href="#" id="pwbe-edit-view-dialog-button-cancel" class="pwbe-dialog-close-x">X</a>
	</div>
	<div class="pwbe-dialog-container">
		<?php
			require( dirname( __FILE__ ) . '/view_manager/edit.php' );
		?>
	</div>
</div>
<?php

function pwbe_field( $product, $column, $hidden ) {

	$field = $column['field'];
	$input_type = $column['type'];
	$visibility = $column['visibility'];

	$readonly = '';

	if ( $column['readonly'] == 'true' ) {
		$readonly = 'pwbe-field-readonly';
	}

	if ( property_exists( $product, $field ) ) {
		$field_value = $product->{$field};
	} else {
		$field_value = null;
	}

	$display_value = $field_value;

	switch ( $input_type ) {
		case 'select':
			$select_options = PWBE_Select_Options::get();
			if ( isset( $select_options[$field][$field_value] ) ) {
				$display_value = htmlspecialchars( $select_options[$field][$field_value]['name'], ENT_QUOTES );
				$field_value = htmlspecialchars( $field_value, ENT_QUOTES );
			} else {
				$display_value = 'n/a';
			}
		break;

		case 'currency':
			$field_value = wc_format_localized_price( $field_value );
			$display_value = wc_format_localized_price( $display_value );

			if ( $display_value === '' ) {
				$display_value = 'n/a';
			}
		break;

		case 'checkbox':
			if ( !isset( $field_value ) || empty( $field_value ) ) {
				$display_value = 'no';
				$field_value = 'no';
			}
		break;

		case 'number':
			if ( !isset( $display_value ) || $display_value == '' ) {
				$display_value = 'n/a';
			}
		break;

		default:
			if ( !isset( $display_value ) || empty( $display_value ) ) {
				$display_value = 'n/a';
			}

			if ( !empty( $field_value ) ) {
				$field_value = htmlspecialchars( $field_value, ENT_QUOTES );
				$display_value = htmlspecialchars( $display_value, ENT_QUOTES );
			}
		break;
	}

	// Some fields are always hidden.
	switch ( $product->product_type ) {
		case 'variable':
			if ( $visibility != 'parent' && $visibility != 'both' ) {
				$readonly = 'pwbe-field-readonly';
				$field_value = '';
				$display_value = '<div class="pwbe-field-variable-product">Variable product</div>';
			}
		break;

		case 'variation':
			if ( $visibility != 'variation' && $visibility != 'both' ) {
				$readonly = 'pwbe-field-readonly';
				$field_value = '';

				if ( $field == 'post_title' ) {
					$display_value = '<div class="pwbe-field-variation" data-post-id="' . $product->parent_post_id . '">Variation of ' . substr( $product->post_title, 0, 100 ) . '</div>';
				} else {
					$display_value = '<div class="pwbe-field-variable-product">Same as parent</div>';
				}
			}
		break;
	}

	if ( $product->product_type == 'variation' && $field == 'post_title' ) {
		$variation = wc_get_product( $product->post_id );
		$display_value = $variation->get_formatted_name();
	}

	$html = "
		<div class='pwbe-td pwbe-results-table-td pwbe-results-table-cell-td $hidden' data-field='$field'>
			<div class='pwbe-field pwbe-field-$field $readonly'>
				<input type='hidden' name='pwbe_field_{$field}_{$product->post_id}' value='$field_value' class='pwbe-field-value' data-input-type='$input_type' data-original-value='$field_value' data-field='$field' data-post-id='{$product->post_id}' data-parent-post-id='{$product->parent_post_id}' data-product-type='{$product->product_type}' />
				<div class='pwbe-field-label'>$display_value</div>
			</div>
		</div>
	";

	return $html;
}

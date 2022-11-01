<?php

defined( 'ABSPATH' ) || exit;

?>

<p>
		<div id="body_content_inner" style="color: #636363; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 150%; text-align: left;">

			<p style="margin: 0 0 16px;"><?php esc_html_e('You’ve received the following offer:', 'b2bking');?></p>

			<h2 style="color: #96588a; display: block; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 18px; font-weight: bold; line-height: 130%; margin: 0 0 18px; text-align: left;">
				<?php if ($message == '1'){?>
				<a class="link" href="<?php echo $offerlink;?>" style="font-weight: normal; text-decoration: underline; color: #96588a;" target="_blank">[<?php esc_html_e('Offer', 'b2bking'); echo ' #'.$offerid;?>]</a><?php } ?>

				 <?php 

				$year = date("Y");
				$day = date("j");
				$month = date("m");

				if ($month === '01'){
					$month = esc_html__('January', 'b2bking');
				} else if ($month === '02'){
					$month = esc_html__('February', 'b2bking');
				} else if ($month === '03'){
					$month = esc_html__('March', 'b2bking');
				} else if ($month === '04'){
					$month = esc_html__('April', 'b2bking');
				} else if ($month === '05'){
					$month = esc_html__('May', 'b2bking');
				} else if ($month === '06'){
					$month = esc_html__('June', 'b2bking');
				} else if ($month === '07'){
					$month = esc_html__('July', 'b2bking');
				} else if ($month === '08'){
					$month = esc_html__('August', 'b2bking');
				} else if ($month === '09'){
					$month = esc_html__('September', 'b2bking');
				} else if ($month === '10'){
					$month = esc_html__('October', 'b2bking');
				} else if ($month === '11'){
					$month = esc_html__('November', 'b2bking');
				} else if ($month === '12'){
					$month = esc_html__('December', 'b2bking');
				}
				
				echo '('.$month.' '.$day.', '.$year.')';


				?></h2>

			<div style="margin-bottom: 40px;">
				<table class="td" cellspacing="0" cellpadding="6" border="1" style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
					<thead>
						<tr>
							<th class="td" scope="col" style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left;"><?php esc_html_e('Product', 'b2bking');?></th>
							<th class="td" scope="col" style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left;"><?php esc_html_e('Quantity', 'b2bking');?></th>
							<th class="td" scope="col" style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left;"><?php esc_html_e('Price', 'b2bking');?></th>
						</tr>
					</thead>
					<tbody>

						<?php

						$details = get_post_meta(apply_filters( 'wpml_object_id', $offerid, 'post' , true),'b2bking_offer_details', true);
						$offer_products = explode('|',$details);
						$custom_text = get_post_meta(apply_filters( 'wpml_object_id', $offerid, 'post' , true), 'b2bking_offer_customtext_textarea', true);

						$offer_price = 0;
		            	foreach ($offer_products as $product){
		            		$product_details = explode(';', $product);
		            		// if item is in the form product_id, change title
		            		$isproductid = explode('_', $product_details[0]); 
		            		if ($isproductid[0] === 'product'){
		            			// it is a product+id, get product title
		            			$newproduct = wc_get_product($isproductid[1]);

		            			if (is_a($newproduct,'WC_Product_Variation') || is_a($newproduct,'WC_Product')){
			            			$product_details[0] = $newproduct->get_name();
			            		}

		            			//if product is a variation with 3 or more attributes, need to change display because get_name doesnt 
		            			// show items correctly
		            			if (is_a($newproduct,'WC_Product_Variation')){
		            				$attributes = $newproduct->get_variation_attributes();
		            				$number_of_attributes = count($attributes);
		            				if ($number_of_attributes > 2){
		            					$product_details[0].=' - ';
		            					foreach ($attributes as $attribute){
		            						$product_details[0].=$attribute.', ';
		            					}
		            					$product_details[0] = substr($product_details[0], 0, -2);
		            				}
		            			}
		            			
		            			
		            		}

	            			$unit_price_display = $product_details[2];
	            			// get offer product
	            			$offerid = intval(get_option('b2bking_offer_product_id_setting', 0));
	            			$offer_product = wc_get_product($offerid);

	            			if (is_a($offer_product,'WC_Product')){
		            			if (is_a(WC()->customer, 'WC_Customer')){
			            			if( wc_prices_include_tax() && ('incl' !== get_option( 'woocommerce_tax_display_shop') || WC()->customer->is_vat_exempt())) {
			            				// if prices are entered including tax, but display is without tax, remove tax 
			            				// get tax rate for the offer product
			            				$tax_rates = WC_Tax::get_base_tax_rates( $offer_product->get_tax_class( 'unfiltered' ) ); 
			            				$taxes = WC_Tax::calc_tax( $unit_price_display, $tax_rates, true ); 
			            				$unit_price_display = WC_Tax::round( $unit_price_display - array_sum( $taxes ) ); 

			            			} else if ( !wc_prices_include_tax() && ('incl' === get_option( 'woocommerce_tax_display_shop') && !WC()->customer->is_vat_exempt())){
			            				// if prices are entered excluding tax, but display is with tax, add tax
			            				$tax_rates = WC_Tax::get_rates( $offer_product->get_tax_class() );
			            				$taxes     = WC_Tax::calc_tax( $unit_price_display, $tax_rates, false );
			            				$unit_price_display = WC_Tax::round( $unit_price_display + array_sum( $taxes ) );
			            			} else {
			            				// no adjustment
			            			}
			            		}
	            			}

	            			$offer_price+=$unit_price_display*$product_details[1];

	            			?>

	            			<tr class="order_item">
	            				<td class="td" style="color: #636363; border: 1px solid #e5e5e5; padding: 12px; text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap: break-word;">
	            				<?php echo esc_html(strip_tags($product_details[0])); ?>	</td>
	            				<td class="td" style="color: #636363; border: 1px solid #e5e5e5; padding: 12px; text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
	            					<?php echo esc_html($product_details[1]); ?>		</td>
	            				<td class="td" style="color: #636363; border: 1px solid #e5e5e5; padding: 12px; text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
	            					<?php echo wc_price($unit_price_display*$product_details[1]);?>		</td>
	            			</tr>
	            			<?php
	            	
		            	}

		            	?>
				
					</tbody>
					<tfoot>
											
							<tr>
									<th class="td" scope="row" colspan="2" style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left;"><?php esc_html_e('Total','b2bking');?>:</th>
									<td class="td" style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left;"><?php echo wc_price($offer_price);?>
			</td>
								</tr>
										</tfoot>
				</table>
				<br><br><p>
				<?php
				
				if (!empty($custom_text) && $custom_text !== NULL){
					echo nl2br($custom_text);
				}
					?></p>
			</div>
		</div>
</p>
<?php

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );



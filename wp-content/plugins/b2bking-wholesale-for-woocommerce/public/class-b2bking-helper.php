<?php

class B2bkingcore_Helper{

	public static function b2bking_wc_get_price_to_display( $product, $args = array() ) {

		// Modify WC function to consider user's vat exempt status
		$customer = WC()->customer;
	    $args = wp_parse_args(
	        $args,
	        array(
	            'qty'   => 1,
	            'price' => $product->get_price(),
	        )
	    );

	    $price = $args['price'];
	    $qty   = $args['qty'];

	    if ( 'incl' === get_option( 'woocommerce_tax_display_cart' ) && !$customer->is_vat_exempt() ){
	    	return 
	        wc_get_price_including_tax(
	            $product,
	            array(
	                'qty'   => $qty,
	                'price' => $price,
	            )
	        );
	    } else {
	    	return
	        wc_get_price_excluding_tax(
	            $product,
	            array(
	                'qty'   => $qty,
	                'price' => $price,
	            )
	        );
	    }
	}

}
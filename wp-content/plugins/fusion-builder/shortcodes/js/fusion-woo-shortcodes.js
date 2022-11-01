/* global FusionPageBuilderApp */
( function() {

	jQuery( document ).ready( function() {

		// WooCommerce shortocodes handler
		jQuery( 'body' ).on( 'change', '#fusion_woo_shortcode', function() {
			var shortoCodes = [
					' ',
					'[woocommerce_order_tracking]',
					'[add_to_cart id="" sku=""]',
					'[product id="" sku=""]',
					'[products ids="" skus=""]',
					'[product_categories number=""]',
					'[product_category category="" limit="12" columns="4" orderby="date" order="desc"]',
					'[recent_products limit="12" columns="4" orderby="date" order="desc"]',
					'[featured_products limit="12" columns="4" orderby="date" order="desc"]',
					'[shop_messages]'
				],
				selected = jQuery( this ).val();

			// Update content
			if ( true === FusionPageBuilderApp.shortcodeGenerator ) {
				jQuery( '#generator_element_content' ).val( shortoCodes[ selected ] );
			} else {
				jQuery( '#element_content' ).val( shortoCodes[ selected ] );
			}

		} );
	} );

}( jQuery ) );

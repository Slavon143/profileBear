/* global FusionApp, FusionEvents */
( function() {

	jQuery( document ).ready( function() {
		FusionApp.listenTo( FusionEvents, 'fusion-settings-modal-open', fusionWooShortcodesSettingsHandler );

		function fusionWooShortcodesSettingsHandler() {
			setTimeout( function() { // Add a small delay in case the browser takes more than expected to render things.
				var fusionWooShortcodeSelector = jQuery( '#fusion_woo_shortcode' ),
					fusionWooShortcodeTextarea;
				if ( fusionWooShortcodeSelector.length ) {

					fusionWooShortcodeTextarea = jQuery( fusionWooShortcodeSelector.parents( 'li' )[ 0 ] ).parent().find( 'textarea' );
					fusionWooShortcodeSelector.on( 'change', function() {
						var wooShortCodes = [
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
							selected = wooShortCodes[ jQuery( this ).val() ];

						// Update content.
						fusionWooShortcodeTextarea.val( selected ).html( selected ).trigger( 'change' );
					} );
				}
			}, 50 );
		}
	} );
}( jQuery ) );

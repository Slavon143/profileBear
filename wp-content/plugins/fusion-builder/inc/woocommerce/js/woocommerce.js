jQuery( document ).ready( function() {
	jQuery( 'body' ).on( 'click', '.add_to_cart_button', function() {
		var $addToCartButton = jQuery( this );

		$addToCartButton.closest( '.product, li' ).find( '.cart-loading' ).find( 'i' ).removeClass( 'awb-icon-check-square-o' ).addClass( 'awb-icon-spinner' );
		$addToCartButton.closest( '.product, li' ).find( '.cart-loading' ).fadeIn();
		setTimeout( function() {
			$addToCartButton.closest( '.product, li' ).find( '.cart-loading' ).find( 'i' ).hide().removeClass( 'awb-icon-spinner' ).addClass( 'awb-icon-check-square-o' ).fadeIn();
			jQuery( $addToCartButton ).parents( '.fusion-clean-product-image-wrapper, li' ).addClass( 'fusion-item-in-cart' );
		}, 2000 );
	} );
} );

/* global FusionPageBuilderViewManager, FusionPageBuilderEvents */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		// Builder Checkout Form View
		FusionPageBuilder.checkoutForm = window.wp.Backbone.View.extend( {

			className: 'fusion-checkout-form',
			template: FusionPageBuilder.template( $( '#fusion-checkout-form-template' ).html() ),
			events: {
				'click .fusion-builder-delete-checkout-form': 'removeContainer'
			},

			render: function() {
				this.$el.html( this.template( this.model.toJSON() ) );

				return this;
			},

			removeContainer: function( event ) {
				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

				this.model.destroy();

				this.remove();

				if ( event ) {
					FusionPageBuilderEvents.trigger( 'fusion-element-removed' );
				}
			}
		} );
	} );
}( jQuery ) );

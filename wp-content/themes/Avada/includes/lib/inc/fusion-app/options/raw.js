/* global FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionRawField = {
	optionRaw: function( $element ) {
		var self = this,
			$rawFields;

		$element   = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$rawFields = $element.find( '.fusion-builder-option.raw' );

		if ( $rawFields.length ) {
			$rawFields.each( function() {
				if ( 'function' === typeof self[ jQuery( this ).data( 'option-id' ) ] ) {
					self[ jQuery( this ).data( 'option-id' ) ]( jQuery( this ) );
				}
			} );
		}
	},

	visibility_large: function( $el ) {
		var $box = $el.find( 'span' );
		$box.html( FusionApp.settings.visibility_medium );
		$el.prev().find( '#slidervisibility_medium' ).on( 'change', function() {
			$box.html( jQuery( this ).val() );
		} );
	}
};

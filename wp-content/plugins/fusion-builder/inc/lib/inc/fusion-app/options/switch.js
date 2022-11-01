var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionSwitchField = {
	optionSwitch: function( $element ) {
		var $checkboxes;

		$element    = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$checkboxes = jQuery( $element.find( '.fusion-builder-option.switch input[type="checkbox"]' ) );

		_.each( $checkboxes, function( checkbox ) {
			jQuery( checkbox ).on( 'click', function() {
				var value = jQuery( this ).is( ':checked' ) ? '1' : '0';
				jQuery( this ).attr( 'value', value );
				jQuery( this ).trigger( 'change' );
			} );
		} );

	}
};

var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionTextFieldPlaceholder = {
	textFieldPlaceholder: function( $element ) {
		var $textField;
		$element   = $element || this.$el;
		$textField = $element.find( '[data-placeholder]' );

		if ( $textField.length ) {
			$textField.on( 'focus', function( event ) {
				if ( jQuery( event.target ).data( 'placeholder' ) === jQuery( event.target ).val() ) {
					jQuery( event.target ).val( '' );
				}
			} );
		}
	}
};

var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionColorPicker = {
	optionColorpicker: function( $element ) {
		var $colorPicker,
			self = this;

		$element     = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$colorPicker = $element.find( '.fusion-builder-color-picker-hex' );

		if ( $colorPicker.length ) {
			$colorPicker.each( function() {
				var $picker       = jQuery( this ),
					$defaultReset = $picker.closest( '.fusion-builder-option' ).find( '.fusion-builder-default-reset' ),
					parentValue   = 'undefined' !== typeof self.parentValues && 'undefined' !== typeof self.parentValues[ $picker.attr( 'id' ) ] ? self.parentValues[ $picker.attr( 'id' ) ] : false;

				// Child element inheriting default from parent.
				if ( parentValue ) {
					$picker.attr( 'data-default', parentValue );
				}

				$picker.awbColorPicker().on( 'blur', function() {
					if ( jQuery( this ).hasClass( 'iris-error' ) ) {
						jQuery( this ).removeClass( 'iris-error' );
						jQuery( this ).val( '' );
					}
				} );

				// Default reset icon, set value to empty.
				$defaultReset.on( 'click', function( event ) {
					var dataDefault,
						$input = jQuery( this ).closest( '.fusion-builder-option' ).find( '.color-picker' );

					event.preventDefault();
					dataDefault = $input.attr( 'data-default' ) || $input.attr( 'data-default-color' );

					// Make the color picker to start from the default color on open.
					if ( dataDefault ) {
						$input.val( dataDefault ).trigger( 'change' );
					}
					$input.val( '' ).trigger( 'change' );
				} );
			} );
		}
	}
};

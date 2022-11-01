var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionCheckboxButtonSet = {
	optionCheckboxButtonSet: function( $element ) {
		var $checkboxbuttonset,
			$visibility,
			$choice,
			$checkboxsetcontainer;

		$element = $element || this.$el;

		$checkboxbuttonset = $element.find( '.fusion-form-checkbox-button-set' );

		if ( $checkboxbuttonset.length ) {

			// For the visibility option check if choice is no or yes then convert to new style
			$visibility = $element.find( '.fusion-form-checkbox-button-set.hide_on_mobile' );
			if ( $visibility.length ) {
				$choice = $visibility.find( '.button-set-value' ).val();
				if ( 'no' === $choice || '' === $choice ) {
					$visibility.find( 'a' ).addClass( 'ui-state-active' );
				}
				if ( 'yes' === $choice ) {
					$visibility.find( 'a:not([data-value="small-visibility"])' ).addClass( 'ui-state-active' );
				}
			}

			$checkboxbuttonset.find( 'a' ).on( 'click', function( e ) {
				e.preventDefault();
				$checkboxsetcontainer = jQuery( this ).closest( '.fusion-form-checkbox-button-set' );
				jQuery( this ).toggleClass( 'ui-state-active' );
				$checkboxsetcontainer.find( '.button-set-value' ).val( $checkboxsetcontainer.find( '.ui-state-active' ).map( function( _, el ) {
					return jQuery( el ).data( 'value' );
				} ).get() ).trigger( 'change' );
			} );
		}
	}
};

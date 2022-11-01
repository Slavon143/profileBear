var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionDateTimePicker = {
	optionDateTimePicker: function( element ) {
		var datePicker, timePicker;

		element    = element || this.$el;
		datePicker = element.find( '.fusion-datetime-datepicker' );
		timePicker = element.find( '.fusion-datetime-timepicker' );

		if ( datePicker.length ) {
			jQuery( datePicker ).fusiondatetimepicker( {
				format: 'yyyy-MM-dd',
				pickTime: false
			} );
		}

		if ( timePicker.length ) {
			jQuery( timePicker ).fusiondatetimepicker( {
				format: 'hh:mm:ss',
				pickDate: false
			} );
		}

		jQuery( datePicker ).on( 'updateDateTime', function() {
			var date = '',
				time = '',
				dateAndTime = '';

			time = jQuery( this ).closest( '.fusion-datetime-container' ).find( '.fusion-time-picker' ).val();
			date = jQuery( this ).find( '.fusion-date-picker' ).val();

			dateAndTime += date ? date + ' ' : '';
			dateAndTime += time ? time : '';

			jQuery( this ).closest( '.option-field' ).find( '.fusion-date-time-picker' ).val( dateAndTime.trim() ).trigger( 'change' );
		} );

		jQuery( timePicker ).on( 'updateDateTime', function() {
			var date = '',
				time = '',
				dateAndTime = '';

			date = jQuery( this ).closest( '.fusion-datetime-container' ).find( '.fusion-date-picker' ).val();
			time = jQuery( this ).find( '.fusion-time-picker' ).val();

			dateAndTime += date ? date + ' ' : '';
			dateAndTime += time ? time : '';

			jQuery( this ).closest( '.option-field' ).find( '.fusion-date-time-picker' ).val( dateAndTime.trim() ).trigger( 'change' );
		} );
	}
};

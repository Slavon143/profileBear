var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Time View.
		FusionPageBuilder.fusion_form_time = FusionPageBuilder.FormComponentView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.1
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Create attribute objects;
				attributes.styles = this.buildStyles( atts.values );
				attributes.html   = this.generateFormFieldHtml( this.generateTimeField( atts.values ) );

				return attributes;
			},

			generateTimeField: function( values ) {
				var elementData,
					elementHtml,
					html = '',
					input_type = 'type="time"';

				elementData = this.elementData( values );

				this.generateTooltipHtml( values, elementData );

				if ( 'custom' === values.picker || 'desktop' === values.picker ) {
					input_type = 'type="text"';
				}

				elementHtml = '<input id="time-' + this.model.get( 'cid' ) + '" ' + input_type + ' data-type="' + values.picker + '" data-clock="' + values.clock + '" name="' + values.name + '" ' + elementData.holds_private_data + elementData[ 'class' ] + elementData.required + elementData.placeholder + '/>';

				elementHtml = this.generateIconHtml( values, elementHtml );

				html = this.generateLabelHtml( html, elementHtml, elementData.label );

				return html;
			},

			onRender: function() {
				this.afterPatch();
			},

			beforePatch: function() {
				var picker;
				if ( 'function' === typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.flatpickr ) {
					picker = jQuery( '#fb-preview' )[ 0 ].contentWindow.flatpickr( '#time-' + this.model.get( 'cid' ), {} );
					if ( picker && 'function' === typeof picker.destroy ) {
						picker.destroy();
					}
				}
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var self = this;
				setTimeout( function() {
					var $item 	  = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( self.$el.find( 'input[type="time"]' ) ),
						type      = $item.attr( 'data-type' ),
						useMobile = 'custom' === type,
						fullClock = 'full' === $item.attr( 'data-clock' );

					// Native, do not init.
					if ( 'native' === type || 'function' !== typeof $item.flatpickr ) {
						return;
					}
					$item.flatpickr( {
						allowInput: true,
						disableMobile: useMobile,
						enableTime: true,
						noCalendar: true,
						dateFormat: 'H:i',
						time_24hr: fullClock
					} );
				}, 200 );
			}

		} );
	} );
}( jQuery ) );

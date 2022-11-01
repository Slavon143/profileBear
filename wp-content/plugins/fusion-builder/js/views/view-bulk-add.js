/* eslint-disable no-mixed-operators */
/* eslint no-useless-concat: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		FusionPageBuilder.BulkAddView = window.wp.Backbone.View.extend( {

			className: FusionPageBuilder.ElementSettingsView.prototype.className + ' fusion-builder-bulk-add-dialog',

			template: FusionPageBuilder.template( $( '#fusion-builder-bulk-add-template' ).html() ),

			events: {
				'click .predefined-choice': 'predefinedChoices'
			},

			getChoices: function() {
				var textarea 	= this.$el.find( 'textarea' ).val(),
					choices 	= [];

				if ( textarea ) {
					_.each( textarea.split( /\n/ ), function( line ) {
						var choice = line.trim();
						if ( choice ) {
							choices.push( choice );
						}
					} );
				}

				return choices;
			},

			predefinedChoices: function( event ) {
				var $element 	= jQuery( event.target ),
					value 		= $element.data( 'value' ),
					choices;

				if ( ! isNaN( value ) ) {
					choices = this.options.choices[ value ].values;
					choices = choices.join( '\n' );
					this.$el.find( 'textarea' ).val( choices );
				}
			}

		} );

	} );

}( jQuery ) );

/* global FusionPageBuilderApp */

window.mailchimpOption = {

	/**
	 * Run actions on load.
	 *
	 * @since 3.5
	 *
	 * @return {void}
	 */
	onReady: function() {
		var self = this;

		// Cut off check.
		if ( 'undefined' === typeof window.fusionMailchimp ) {
			return;
		}

		// Set reusable vars.
		this.fields     = window.fusionMailchimp.fields;
		this.$el        = jQuery( '.mailchimp-map-holder .fusion-mapping' );
		this.options    = false;
		this.$input     = jQuery( '#pyre_mailchimp_map' );
		this.values     = {};

		try {
			self.values = JSON.parse( self.$input.val() );
		} catch ( e ) {
			console.warn( 'Error triggered - ' + e );
		}

		// Add listeners.
		jQuery( document ).on( 'fusion-builder-content-updated', function() {
			self.updateMap();
		} );

		jQuery( '#refresh-mailchimp-map' ).on( 'click', function( event ) {
			event.preventDefault();

			FusionPageBuilderApp.builderToShortcodes();
		} );

		this.$el.on( 'change', 'select', function() {
			self.updateValues();
		} );
	},

	/**
	 * Update the map with new data.
	 *
	 * @since 3.5
	 *
	 * @return {void}
	 */
	updateValues: function() {
		var values = {};

		this.$el.find( 'select' ).each( function() {
			values[ jQuery( this ).attr( 'name' ) ] = jQuery( this ).val();
		} );

		this.values = values;
		this.$input.val( JSON.stringify( values ) ).change();
	},

	/**
	 * Update the map with new data.
	 *
	 * @since 3.5
	 *
	 * @return {void}
	 */
	updateMap: function() {
		var formElements = false,
			self         = this,
			options      = this.getOptions();

		// Mark old ones.
		self.$el.find( '> div' ).addClass( 'fusion-old' );

		if ( 'object' !== typeof FusionPageBuilderApp.simplifiedMap ) {
			self.$el.empty();
			return;
		}

		// Filter map to only get form elements.
		formElements = _.filter( FusionPageBuilderApp.simplifiedMap, function( element ) {
			return element.type.includes( 'fusion_form' ) && 'fusion_form_submit' !== element.type && ( 'string' === typeof element.params.label || 'string' === typeof element.params.name );
		} );

		// Add entries.
		_.each( formElements, function( formElement ) {
			var inputLabel = 'string' === typeof formElement.params.label && '' !== formElement.params.label ? formElement.params.label : formElement.params.name;

			// If we don't already have this, add it.
			if ( ! self.$el.find( '#fusionmap-' + formElement.params.name ).length ) {
				self.$el.append( '<div><label for="fusionmap-' + formElement.params.name + '">' + inputLabel + '</label><select name="' + formElement.params.name + '" id="fusionmap-' + formElement.params.name + '">' + options + '</select></div>' );
			} else {
				self.$el.find( '#fusionmap-' + formElement.params.name ).closest( 'div' ).removeClass( 'fusion-old' ).find( 'label' ).text( inputLabel );
			}

			// Make sure value is selected.
			if ( 'string' === typeof self.values[ formElement.params.name ] ) {
				self.$el.find( '#fusionmap-' + formElement.params.name ).val( self.values[ formElement.params.name ] );
			}
		} );

		// Remove any extras still marked as old.
		self.$el.find( '.fusion-old' ).remove();
	},

	getOptions: function() {
		var options       = '',
			otherOptions  = '',
			selection     = jQuery( '#pyre_mailchimp_lists' ).val(),
			commonOptions = '',
			common        = [
				'EMAIL',
				'FNAME',
				'LNAME',
				'ADDRESS',
				'PHONE',
				'BIRTHDAY'
			];

		if ( 'object' === typeof this.options ) {
			return this.options;
		}

		this.fields = 'undefined' !== typeof this.fields[ selection ] ? this.fields[ selection ].fields : this.fields;
		this.fields = _.sortBy( this.fields, 'name' );

		// Automatic field match.
		options += '<optgroup label="' + window.fusionMailchimp.common + '">';
		options += '<option value="">' + window.fusionMailchimp.automatic + '</option>';
		options += '<option value="fusion-none">' + window.fusionMailchimp.none + '</option>';

		// Add actual fields.
		_.each( this.fields, function( field ) {
			if ( common.includes( field.tag ) ) {
				commonOptions += '<option value="' + field.tag + '">' + field.name + '</option>';
			} else {
				otherOptions  += '<option value="' + field.tag + '">' + field.name + '</option>';
			}
		} );

		options += commonOptions;
		options += '</optgroup>';

		if ( '' !== otherOptions ) {
			options += '<optgroup label="' + window.fusionMailchimp.other + '">';
			options += otherOptions;
			options += '</optgroup>';
		}
		this.options = options;

		return this.options;
	}
};

( function( jQuery ) {

	'use strict';

	jQuery( document ).ready( function() {

		// Trigger actions on ready event.
		jQuery( document ).ready( function() {
			window.mailchimpOption.onReady();
		} );

	} );
}( jQuery ) );

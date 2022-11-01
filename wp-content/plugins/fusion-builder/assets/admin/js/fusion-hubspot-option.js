/* global FusionPageBuilderApp */

window.hubspotOption = {

	/**
	 * Run actions on load.
	 *
	 * @since 3.1
	 *
	 * @return {void}
	 */
	onReady: function() {
		var self = this;

		// Cut off check.
		if ( 'undefined' === typeof window.fusionHubspot ) {
			return;
		}

		// Set reusable vars.
		this.properties = window.fusionHubspot.properties;
		this.$el        = jQuery( '.hubspot-map-holder .fusion-mapping' );
		this.options    = false;
		this.$input     = jQuery( '#pyre_hubspot_map' );
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

		jQuery( '#refresh-hubspot-map' ).on( 'click', function( event ) {
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
	 * @since 3.1
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
	 * @since 3.1
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
			commonOptions = '',
			common        = [
				'email',
				'firstname',
				'lastname',
				'phone',
				'company'
			];

		if ( 'object' === typeof this.options ) {
			return this.options;
		}

		this.properties = _.sortBy( this.properties, 'label' );

		// Automatic propery match.
		options += '<optgroup label="' + window.fusionHubspot.common + '">';
		options += '<option value="">' + window.fusionHubspot.automatic + '</option>';
		options += '<option value="fusion-none">' + window.fusionHubspot.none + '</option>';

		// Add actual properties.
		_.each( this.properties, function( property ) {
			if ( common.includes( property.name ) ) {
				commonOptions += '<option value="' + property.name + '">' + property.label + '</option>';
			} else {
				otherOptions  += '<option value="' + property.name + '">' + property.label + '</option>';
			}
		} );

		options += commonOptions;
		options += '</optgroup>';

		if ( '' !== otherOptions ) {
			options += '<optgroup label="' + window.fusionHubspot.other + '">';
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
			window.hubspotOption.onReady();
		} );

	} );
}( jQuery ) );

/* globals FusionPageBuilderApp, FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

function fusionHubSpotMapOption( $element ) {
	var self = this;

	// Cut off check.
	if ( 'object' !== typeof FusionApp.data.hubspot || 'undefined' === typeof FusionApp.data.hubspot.properties ) {
		return;
	}

	// Set reusable vars.
	this.properties = FusionApp.data.hubspot.properties;
	this.$el        = $element.find( '.hubspot_map .fusion-mapping' );
	this.options    = false;
	this.$input     = $element.find( 'input#hubspot_map' );
	this.values     = {};

	try {
		self.values = JSON.parse( self.$input.val() );
	} catch ( e ) {
		console.warn( 'Error triggered - ' + e );
	}

	// Initial build.
	this.updateMap();

	// Add listeners.
	FusionPageBuilderApp.collection.on( 'change reset add remove', function() {
		self.updateMap();
	} );

	this.$el.on( 'change', 'select', function() {
		self.updateValues();
	} );
}

fusionHubSpotMapOption.prototype.updateValues  = function() {
	var values = {};

	this.$el.find( 'select' ).each( function() {
		values[ jQuery( this ).attr( 'name' ) ] = jQuery( this ).val();
	} );

	this.values = values;

	this.$input.val( JSON.stringify( values ) );
	setTimeout( () => {
		this.$input.trigger( 'change' );
	}, 10 );
};

fusionHubSpotMapOption.prototype.updateMap  = function() {
	var formElements = false,
		self         = this,
		options      = this.getOptions();

	// Mark old ones.
	self.$el.find( '.form-input-entry' ).addClass( 'fusion-old' );

	if ( 'object' !== typeof FusionPageBuilderApp.collection ) {
		self.$el.empty();
		return;
	}

	// Filter map to only get form elements.
	formElements = _.filter( FusionPageBuilderApp.collection.models, function( element ) {
		var params = element.get( 'params' );
		if ( 'object' !== typeof params ) {
			return false;
		}
		return element.get( 'element_type' ).includes( 'fusion_form' ) && 'fusion_form_submit' !== element.get( 'element_type' ) && ( 'string' === typeof params.label || 'string' === typeof params.name );
	} );

	// Add entries.
	_.each( formElements, function( formElement ) {
		var params     = formElement.get( 'params' ),
			inputLabel = 'string' === typeof params.label && '' !== params.label ? params.label : params.name;

		// If we don't already have this, add it.
		if ( ! self.$el.find( '#fusionmap-' + params.name ).length ) {
			self.$el.append( '<div class="form-input-entry"><label for="fusionmap-' + params.name + '">' + inputLabel + '</label><div class="fusion-select-wrapper"><select class="fusion-dont-update" name="' + params.name + '" id="fusionmap-' + params.name + '">' + options + '</select><span class="fusiona-arrow-down"></span></div></div>' );
		} else {
			self.$el.find( '#fusionmap-' + params.name ).closest( '.form-input-entry' ).removeClass( 'fusion-old' ).find( 'label' ).html( inputLabel );
		}

		// Make sure value is selected.
		if ( 'string' === typeof self.values[ params.name ] ) {
			self.$el.find( '#fusionmap-' + params.name ).val( self.values[ params.name ] );
		}
	} );

	// Remove any extras still marked as old.
	self.$el.find( '.fusion-old' ).remove();
};

fusionHubSpotMapOption.prototype.getOptions = function() {
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
	options += '<optgroup label="' + FusionApp.data.hubspot.common + '">';
	options += '<option value="">' + FusionApp.data.hubspot.automatic + '</option>';
	options += '<option value="fusion-none">' + FusionApp.data.hubspot.none + '</option>';

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
		options += '<optgroup label="' + FusionApp.data.hubspot.other + '">';
		options += otherOptions;
		options += '</optgroup>';
	}
	this.options = options;

	return this.options;
};

FusionPageBuilder.options.fusionHubSpotMap = {

	/**
	 * Run actions on load.
	 *
	 * @since 3.1
	 *
	 * @return {void}
	 */
	optionHubSpotMap: function( $element ) {
		if ( 'undefined' === typeof this.hubspotMap ) {
			this.hubspotMap = new fusionHubSpotMapOption( $element );
		}
	}
};

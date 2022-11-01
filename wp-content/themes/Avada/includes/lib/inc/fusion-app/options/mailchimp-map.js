/* globals FusionPageBuilderApp, FusionApp, fusionSanitize */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

function fusionMailchimpMapOption( $element ) {
	var self = this;

	// Cut off check.
	if ( 'object' !== typeof FusionApp.data.mailchimp || 'undefined' === typeof FusionApp.data.mailchimp.fields || 'undefined' === typeof FusionApp.data.fusionPageOptions.form_submission.fields.mailchimp_options || 'undefined' === typeof FusionApp.data.fusionPageOptions.form_submission.fields.mailchimp_options.fields.mailchimp_lists ) {
		return;
	}

	// Set reusable vars.
	this.fields  = FusionApp.data.mailchimp.fields;
	this.$el     = $element.find( '.mailchimp_map .fusion-mapping' );
	this.options = false;
	this.$input  = $element.find( 'input#mailchimp_map' );
	this.values  = {};

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

fusionMailchimpMapOption.prototype.updateValues  = function() {
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

fusionMailchimpMapOption.prototype.updateMap  = function() {
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

fusionMailchimpMapOption.prototype.getOptions = function() {
	var options       = '',
		selection     = '',
		defaultVal    = Object.keys( FusionApp.data.fusionPageOptions.form_submission.fields.mailchimp_options.fields.mailchimp_lists.choices )[ 0 ],
		otherOptions  = '',
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

	selection   = '' === fusionSanitize.getPageOption( 'mailchimp_lists' ) ? defaultVal : fusionSanitize.getPageOption( 'mailchimp_lists' );

	this.fields = 'undefined' !== typeof this.fields[ selection ] ? this.fields[ selection ].fields : this.fields;
	this.fields = _.sortBy( this.fields, 'name' );

	// Automatic field match.
	options += '<optgroup label="' + FusionApp.data.mailchimp.common + '">';
	options += '<option value="">' + FusionApp.data.mailchimp.automatic + '</option>';
	options += '<option value="fusion-none">' + FusionApp.data.mailchimp.none + '</option>';

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
		options += '<optgroup label="' + FusionApp.data.mailchimp.other + '">';
		options += otherOptions;
		options += '</optgroup>';
	}
	this.options = options;

	return this.options;
};

FusionPageBuilder.options.fusionMailchimpMap = {

	/**
	 * Run actions on load.
	 *
	 * @since 3.1
	 *
	 * @return {void}
	 */
	optionMailchimpMap: function( $element ) {
		if ( 'undefined' === typeof this.mailchimpMap ) {
			this.mailchimpMap = new fusionMailchimpMapOption( $element );
		}
	}
};

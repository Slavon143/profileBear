/* global fusionBuilderText */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.DynamicValues = Backbone.Model.extend( {
		defaults: {
			values: {},
			options: {},
			optionTypes: {},
			orderedParams: false
		},

		getOrderedParams: function() {
			var params  = this.get( 'orderedParams' ),
				options = this.getOptions();

			if ( ! params ) {
				params = {};
				_.each( options, function( object, id ) {
					var group,
						groupText;

					if ( 'object' !== typeof object ) {
						return;
					}

					group     = object.group;
					groupText = group;

					if ( 'string' !== typeof object.group ) {
						group     = 'other';
						groupText = fusionBuilderText.other;
					}

					group = group.replace( /\s+/g, '_' ).toLowerCase();

					if ( 'object' !== typeof params[ group ] ) {
						params[ group ] = {
							label: '',
							params: {}
						};
					}

					params[ group ].label        = groupText;
					params[ group ].params[ id ] = object;
				} );
			}
			return params;
		},

		addData: function( data, options, optionTypes ) {
			this.set( 'values', data );
			this.set( 'options', options );
			this.set( 'optionTypes', optionTypes );
		},

		supportsType: function( type ) {
			var types = _.values( this.getOptionTypes() );

			return -1 !== _.indexOf( types, type );
		},

		getOptionTypes: function() {
			var optionTypes = this.get( 'optionTypes' );

			return jQuery.extend( true, {}, optionTypes );
		},

		getOptions: function() {
			var options = this.get( 'options' );

			return jQuery.extend( true, {}, options );
		},

		getOption: function( param ) {
			var options = this.getOptions();

			return 'undefined' !== typeof options[ param ] ? options[ param ] : false;
		},

		getAll: function() {
			var values = this.get( 'values' );

			return jQuery.extend( true, {}, values );
		}
	} );
}( jQuery ) );

/* global FusionPageBuilderApp, FusionPageBuilderEvents */
/* eslint no-empty-function: off */
/* eslint no-unused-vars: off */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.DynamicParams = Backbone.Model.extend( {
		defaults: {
			params: {},
			elementView: false,
			backup: {}
		},

		initialize: function() {
		},

		setData: function( data ) {
			if ( 'object' === typeof data ) {
				this.set( 'params', data );
			}
		},

		getAll: function() {
			var params = this.get( 'params' );

			return jQuery.extend( true, {}, params );
		},

		createBackup: function() {
			this.set( 'backup', this.getAll() );
		},

		restoreBackup: function() {
			this.set( 'params', this.get( 'backup' ) );
			this.set( 'backup', {} );
		},

		hasDynamicParam: function( param ) {
			var params = this.getAll();

			if ( 'undefined' !== typeof params[ param ] ) {
				return true;
			}
			return false;
		},

		addParam: function( param, data ) {
			var self    = this,
				params  = this.getAll(),
				options = FusionPageBuilderApp.dynamicValues.getOptions(),
				option  = false;

			if ( 'object' !== typeof data ) {
				data = {
					data: data
				};
			}

			// // Set default values.
			_.each( options[ data.data ].fields, function( field, key ) {
				if ( 'undefined' === typeof data[ key ] ) {
					if ( 'undefined' !== typeof field[ 'default' ] ) {
						data[ key ] = field[ 'default' ];
					} else if ( 'undefined' !== typeof field.value ) {
						data[ key ] = field.value;
					}
				}
			} );

			params[ param ] = data;

			this.set( 'params', params );

			FusionPageBuilderEvents.trigger( 'fusion-dynamic-data-added', param );
		},

		updateParam: function( param, subParam, value ) {
			var params      = this.getAll();

			if ( 'object' === typeof params[ param ] ) {
				params[ param ][ subParam ] = value;
				this.set( 'params', params );
			}
		},

		removeParam: function( param ) {
			var params      = this.getAll(),
				elementView = this.get( 'elementView' );

			delete params[ param ];

			this.set( 'params', params );

			FusionPageBuilderEvents.trigger( 'fusion-dynamic-data-removed', param );
		}
	} );
}( jQuery ) );

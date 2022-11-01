/* global FusionEvents, FusionPageBuilderApp, fusionAllElements, fusionBuilderText */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.DynamicParams = Backbone.Model.extend( {
		defaults: {
			params: {},
			elementView: false,
			listeners: []
		},

		initialize: function() {
			this._historyPush = _.debounce( _.bind( this.historyPush, this ), 300 );
		},

		setData: function( data ) {
			if ( 'object' === typeof data ) {
				this.set( 'params', data );
				this.setListeners();
			}
		},

		getAll: function() {
			var params = this.get( 'params' );

			return jQuery.extend( true, {}, params );
		},

		setListeners: function() {
			var params = this.getAll(),
				self   = this;

			_.each( params, function( param ) {
				var option = FusionPageBuilderApp.dynamicValues.getOption( param.data );

				if ( option && 'object' === typeof option.listeners ) {
					_.each( option.listeners, function( listenerData, listenerId ) {
						self.setListener( listenerId, listenerData, param );
					} );
				}
			} );
		},

		setListener: function( id, data, args ) {
			var location  = 'undefined' !== typeof data.location ? data.location : false,
				self      = this,
				cid       = this.cid,
				listeners = this.get( 'listeners' );

			if ( ! location ) {
				return;
			}

			switch ( location ) {

			case 'postDetails':
				FusionEvents.on( 'fusion-' + id + '-changed', function() {
					FusionPageBuilderApp.dynamicValues.removeValue( args.data );

					self.getValueAndUpdate( args );
				}, cid );
				listeners.push( 'fusion-' + id + '-changed' );
				self.set( 'listeners', listeners );
				break;
			case 'postMeta':
				FusionEvents.on( 'fusion-po-' + id + '-changed', function() {
					FusionPageBuilderApp.dynamicValues.removeValue( args.data );

					self.getValueAndUpdate( args );
				}, cid );
				listeners.push( 'fusion-po-' + id + '-changed' );
				self.set( 'listeners', listeners );
				break;
			}
		},

		hasDynamicParam: function( param ) {
			var params = this.getAll();

			if ( 'undefined' !== typeof params[ param ] ) {
				return true;
			}
			return false;
		},

		getParamValue: function( data ) {
			var value        = FusionPageBuilderApp.dynamicValues.getValue( data ),
				beforeString = 'string' === typeof data.before ? data.before : '',
				afterString  = 'string' === typeof data.after ? data.after : '',
				fallback     = 'undefined' !== typeof data.fallback ? data.fallback : false,
				hasValue     = 'undefined' !== typeof value && null !== value && false !== value && '' !== value,
				elementView  = this.get( 'elementView' );

			if ( ! hasValue && fallback ) {
				return fallback;
			}
			if ( ! hasValue ) {
				return undefined;
			}

			if ( 'object' === typeof value && 'function' === typeof value.then ) {
				value.then( function() {
					elementView.reRender();
				} );
				return false;
			} else if ( 'string' !== typeof value ) {
				return value;
			}
			return beforeString + value + afterString;
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

			option = FusionPageBuilderApp.dynamicValues.getOption( data.data );
			if ( option && 'object' === typeof option.listeners ) {
				_.each( option.listeners, function( listenerData, listenerId ) {
					self.setListener( listenerId, listenerData, param );
				} );
			}

			this.set( 'params', params );

			this.saveData();

			FusionEvents.trigger( 'fusion-dynamic-data-added', param );

			this.getValueAndUpdate( params[ param ] );
		},

		updateParam: function( param, subParam, value ) {
			var params      = this.getAll();

			if ( 'object' === typeof params[ param ] ) {
				params[ param ][ subParam ] = value;
				this.set( 'params', params );

				FusionEvents.trigger( 'fusion-dynamic-data-updated', param );

				this.saveData();

				this.getValueAndUpdate( params[ param ] );
			}
		},

		getValueAndUpdate: function( args ) {
			var elementView = this.get( 'elementView' ),
				valueReturn = FusionPageBuilderApp.dynamicValues.getValue( args, elementView );

			if ( 'object' === typeof valueReturn && 'function' === typeof valueReturn.then ) {
				elementView.addLoadingOverlay();
				valueReturn.then(
					function() {
						elementView.reRender();
					},
					function() {
						elementView.removeLoadingOverlay();
					}
				);
			} else {
				elementView.reRender();
			}
		},

		updateListeners: function() {
			var cid = this.cid;

			_.each( this.get( 'listeners' ), function( listener ) {
				FusionEvents.off( listener, null, cid );
			} );
			this.setListeners();
		},

		removeParam: function( param ) {
			var params      = this.getAll(),
				elementView = this.get( 'elementView' );

			delete params[ param ];

			this.set( 'params', params );

			this.updateListeners();

			this.saveData();

			elementView.reRender();

			FusionEvents.trigger( 'fusion-dynamic-data-removed', param );
		},

		historyPush: function() {
			var elementView   = this.get( 'elementView' ),
				elementMap    = fusionAllElements[ elementView.model.get( 'element_type' ) ];

			// TODO: refactor history.
			FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.edited + ' ' + elementMap.name + ' - ' + fusionBuilderText.dynamic_data );
		},

		saveData: function() {
			var elementView   = this.get( 'elementView' ),
				elementParams = elementView.model.get( 'params' ),
				originalValue = elementParams.dynamic_params;

			elementParams.dynamic_params = FusionPageBuilderApp.base64Encode( JSON.stringify( this.getAll() ) );

			elementView.model.set( 'params', elementParams );

			// Make sure that parent is updated, usually done in base view changeParam.
			if ( 'function' === typeof elementView.forceUpdateParent ) {
				elementView.forceUpdateParent();
			}

			if ( originalValue !== elementParams.dynamic_params ) {
				this._historyPush();
			}
		}
	} );
}( jQuery ) );

/* global ajaxurl */
/* eslint no-empty-function: ["error", { "allow": ["functions"] }] */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {
	var model,
		conditionsInUse = {};

	// Layouts model.
	FusionPageBuilder.Layouts = Backbone.Model.extend( {
		defaults: {
			type: 'layouts'
		}
	} );

	// Layout model.
	FusionPageBuilder.Layout = Backbone.Model.extend( {
		defaults: {
			type: 'layout',
			templates: {}
		},

		initialize: function() {
			var self = this;
			_.each( this.getConditions(), function( condition, id ) {
				self.registerCondition( id, condition.mode );
			} );
		},

		/**
		 * Returns a global registered condition if exist else returns false
		 *
		 * @since 2.2
		 * @param {String} id - The condition id
		 * @param {String} mode - The condition mode: exclude/include
		 * @return {Boolean|Object}
		 */
		getConditionLayout: function( id, mode ) {
			return conditionsInUse[ id + '-' + mode ] ? conditionsInUse[ id + '-' + mode ] : false;
		},

		/**
		 * Removes a global registered condition
		 *
		 * @since 2.2
		 * @param {String} id - The condition id
		 * @param {String} mode - The condition mode: exclude/include
		 * @return {void}
		 */
		unregisterCondition: function( id, mode ) {
			if ( conditionsInUse[ id + '-' + mode ] ) {
				delete conditionsInUse[ id + '-' + mode ];
			}
		},

		/**
		 * Registers a condition as global if mode is include
		 *
		 * @since 2.2
		 * @param {String} id - The condition id
		 * @param {String} mode - The condition mode: exclude/include
		 * @return {void}
		 */
		registerCondition: function( id, mode ) {
			// Bypass exclude conditions
			if ( 'include' === mode ) {
				conditionsInUse[ id + '-' + mode ] = this.cid;
			}
		},

		/**
		 * Return all registered templates or filtered by type
		 *
		 * @since 2.2
		 * @param {String} [type]
		 * @return {Object}.
		 */
		getTemplates( type ) {
			if ( type ) {
				return window.fusionTemplates[ type ];
			}
			return window.fusionTemplates;
		},

		/**
		 * Return layout post_content
		 *
		 * @since 2.2
		 * @return {Object}.
		 */
		getContent: function() {
			return {
				conditions: _.clone( this.get( 'data' ).conditions ),
				template_terms: _.clone( this.get( 'data' ).template_terms )
			};
		},

		/**
		 * Return layout selected conditions
		 *
		 * @since 2.2
		 * @return {Object}.
		 */
		getConditions: function() {
			var data = this.get( 'data' );
			return data && data.conditions ? data.conditions : {};
		},

		/**
		 * Return layout selected templates
		 *
		 * @since 2.2
		 * @return {Object}.
		 */
		getTemplateTerms: function() {
			var data = this.get( 'data' );
			return data && data.template_terms ? data.template_terms : {};
		},

		/**
		 * Returns selected templates
		 *
		 * @since 2.2
		 * @return {Object}.
		 */
		getAssignedTemplates: function() {
			var templateTerms = this.getTemplateTerms(),
				templates = {},
				self = this;

			_.each( templateTerms, function( templateTerm, termType ) {
				templates[ termType ] = _.find( self.getTemplates( termType ), function ( t ) {
					return t.ID == templateTerm;
				} );
			} );
			return templates;
		},

		/**
		 * Ajax handler
		 *
		 * @since 2.2
		 * @param {Object} data
		 * @param {Function} callback
		 * @return {Void}.
		 */
		doAjax: function( data, callback ) {
			jQuery.ajax( {
				type: 'POST',
				url: ajaxurl,
				dataType: 'json',
				data: data,
				complete: function( response ) {
					if ( response.success ) {
						return callback( response.responseJSON );
					}
					return callback( null, response );
				}
			} );
		}
	} );

	// Layouts View
	FusionPageBuilder.LayoutsView = Backbone.View.extend( {
		template: FusionPageBuilder.template( jQuery( '#fusion-layouts-template' ).html() ),
		events: {},

		/**
		 * Initialize the layouts
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {
			this.layouts = {};
		},

		/**
		 * Render the template.
		 *
		 * @since 2.0.0
		 * @return {Object} this.
		 */
		render: function() {
			this.$el.html( this.template() );
			this.addLayouts();
			return this;
		},

		/**
		 * Create view for each  layout and append.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		addLayouts: function() {
			var $layouts = this.$el.find( '.fusion-layouts-grid' ),
				data     = this.model.get( 'layouts' ),
				self	 = this;

			_.each( data, function( layout ) {
				var layoutSettings, view;

				layoutSettings 						= new FusionPageBuilder.Layout( layout );
				view           						= new FusionPageBuilder.LayoutView( { model: layoutSettings } );
				self.layouts[ layoutSettings.cid ] 	= layoutSettings;
				$layouts.append( view.render().el );
			} );

			setTimeout( function() {
				if ( jQuery( '.awb-layout-highlight' ).length ) {
					jQuery( 'html, body' ).animate( {
						scrollTop: jQuery( '.awb-layout-highlight' ).offset().top - jQuery( '#wpadminbar' ).height() - jQuery( '.avada-db-menu-sticky' ).height() - 15
					}, 250 );
				}
			}, 100 );
		}
	} );

	// Init the layout builder.
	jQuery( document ).ready( function() {
		if ( 'object' === typeof window.fusionLayouts ) {
			model                = new FusionPageBuilder.Layouts( { layouts: window.fusionLayouts } );
			window.layoutBuilder = new FusionPageBuilder.LayoutsView( { model: model } );
			jQuery( '.fusion-layouts' ).append( window.layoutBuilder.render().el );
		}
	} );
}( jQuery ) );

/* global FusionApp, fusionAppConfig, FusionEvents */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		FusionPageBuilder.fusion_widget_content = window.wp.Backbone.View.extend( {

			template: FusionPageBuilder.template( jQuery( '#tmpl-fusion_widget_content' ).html() ),

			className: 'fusion-widget-content-view',

			events: {
			},

			/**
			 * First filters applied to widget markup when retrieved via ajax.
			 *
			 * @since 2.2.0
			 * @return {String}
			 */
			filterRenderContent: function ( output ) {
				return output;
			},

			/**
			 * Before Widget Content View actions.
			 *
			 * @since 2.2.0
			 * @return {void}
			 */
			beforeRemove: function () { // eslint-disable-line no-empty-function
			},

			/**
			 * Remove Widget Content View.
			 *
			 * @since 2.2.0
			 * @return {void}
			 */
			removeElement: function() {
				FusionApp.deleteScripts( this.cid );
				this.beforeRemove();
				this.remove();
			},

			/**
			 * Init function.
			 *
			 * @since 2.2.0
			 * @return {void}
			 */
			initialize: function() {
				// Set markup
				if ( this.model.attributes.markup && this.model.attributes.markup.output ) {
					this.model.attributes.markup = FusionApp.removeScripts( this.filterRenderContent( this.model.attributes.markup.output ), this.cid );
					this.injectScripts();
				}
				this.onInit();
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.2.0
			 * @return {Object} this
			 */
			render: function() {
				if ( !this.isAjax && ( 'undefined' === typeof this.model.attributes.markup || '' === this.model.attributes.markup ) ) {
					FusionApp.deleteScripts( this.cid );
					this.getHTML( this );
				}
				this.$el.html( this.template( this.model.attributes ) );

				this.onRender();

				return this;
			},

			onInit: function() {
				this.isAjax = false;
			},

			onRender: function() { // eslint-disable-line no-empty-function
			},

			/**
			 * Calls getHTML().
			 *
			 * @since 2.2.0
			 * @param {Object} view
			 * @return {void}
			 */
			getMarkup: function( view ) {
				this.getHTML( view );
			},

			/**
			 * Add selected widget scripts to Fusion App.
			 *
			 * @since 2.2.0
			 * @return {void}
			 */
			injectScripts: function() {
				var self, dfd;
				self = this;
				dfd	 = jQuery.Deferred();

				setTimeout( function() {
					FusionApp.injectScripts( self.cid );
					dfd.resolve();
				}, 100 );
				return dfd.promise();
			},

			/**
			 * Fetch selected widget markup.
			 *
			 * @since 2.2.0
			 * @param {Object} view
			 * @return {void}
			 */
			getHTML: function( view ) {
				var self = this,
					params;

				params = view.model.get( 'params' );
				self.isAjax = true;

				this.beforeGetHTML();

				jQuery.ajax( {
					type: 'POST',
					url: fusionAppConfig.ajaxurl,
					dataType: 'JSON',
					data: {
						action: 'fusion_get_widget_markup',
						fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
						type: view.model.attributes.params.type,
						params: params,
						widget_id: view.model.cid
					}
				} )
				.done( function( response ) {
					self.isAjax = false;
					FusionApp.deleteScripts( self.cid );

					view.model.attributes.markup = FusionApp.removeScripts( self.filterRenderContent( response ), self.cid );
					view.render();
					self.injectScripts()
					.then( function() {
						self.afterGetHTML();
						// Remove parent loading overlay
						FusionEvents.trigger( 'fusion-widget-rendered' );
					} );
				} );
			},

			beforeGetHTML: function() { // eslint-disable-line no-empty-function
			},

			afterGetHTML: function() { // eslint-disable-line no-empty-function
			}

		} );

	} );

}() );

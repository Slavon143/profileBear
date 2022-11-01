/* global fusionAllElements */
/* eslint no-shadow: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		FusionPageBuilder.ModuleSettingsWidget = FusionPageBuilder.BaseWidgetSettingsView.extend( {
			events: function() {
				return _.extend( {}, FusionPageBuilder.ElementSettingsView.prototype.events, {
					'change #type': 'widgetChanged'
				} );
			},

			onInit: function() {
                this.formTemplate = FusionPageBuilder.template( jQuery( '#fusion-builder-widget-settings-template' ).html() );
				this.registerWidgets();
			},

			beforeRender: function() {
				this.setWidgetFields();
			},

			onRender: function () {
				if ( this.getWidget() && this.getWidget().isInvalid ) {
					this.insertForm();
				}
			},

			widgetChanged: function( e ) {
				if ( e.target.value ) {
					this.model.attributes.params.type = e.target.value;
					fusionAllElements.fusion_widget.params.type[ 'default' ] = e.target.value;
				}
				this.render();
			},

			beforeRemove: function() {
				fusionAllElements.fusion_widget.params.type[ 'default' ] = '';
			},

			/**
			 * Delete the models.
			 *
			 * @since 2.0.0
			 * @returns {void}
			 */
			deleteWpModels: function() {
				if ( 'undefined' !== typeof wp.mediaWidgets.widgetControls && 'undefined' !== typeof wp.mediaWidgets.modelCollection ) {
					wp.mediaWidgets.modelCollection.reset();
					wp.mediaWidgets.widgetControls = {};
				}
				if ( 'undefined' !== typeof wp.textWidgets.widgetControls ) {
					wp.textWidgets.widgetControls = {};
				}
			}
		} );
	} );

}( jQuery ) );

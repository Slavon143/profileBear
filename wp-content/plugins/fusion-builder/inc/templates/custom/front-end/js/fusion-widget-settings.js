/* global FusionEvents */
/* eslint no-shadow: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		FusionPageBuilder.ModuleSettingsWidget = FusionPageBuilder.BaseWidgetSettingsView.extend( {
			events: function() {
				return _.extend( {}, FusionPageBuilder.ElementSettingsView.prototype.events, {
					'change .fusion-widget-settings-form select': 'formOptionChange',
					'change .fusion-widget-settings-form input': 'formOptionChange',
					'change .fusion-widget-settings-form textarea': 'formOptionChange',
					'change .fusion-widget-settings-form checkbox': 'formOptionChange'
				} );
			},

			onInit: function() {
				this.formTemplate = FusionPageBuilder.template( jQuery( '#fusion-builder-widget-settings-template' ).html() );
				this.listenTo( FusionEvents, 'fusion-widget-changed', this.widgetChanged );
				this._getWidgetMarkup = _.debounce( _.bind( this.getWidgetMarkup, this ), 500 );
				this.registerWidgets();
			},

			onRender: function() {
				if ( this.getWidget() && this.getWidget().isInvalid ) {
					this.insertForm();
				}
			},

			/**
			 * Debounce formOptionChange if no template.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The JS event.
			 * @return {void}
			 */
			formOptionChange: function( event ) {
				var $target,
					$option,
					paramName,
					value,
					widget;

				if ( this.changesPaused ) {
					return;
				}

				$target   = jQuery( event.target ),
				$option   = $target.closest( '.fusion-builder-option' ),
				paramName = this.getParamName( $target, $option );
				widget    = this.getWidget();

				// Specific actions for invalid widgets
				if ( widget.isInvalid ) {
					paramName = this.createWidgetFieldName( widget.className, event.target.name );
				}

				if ( ! paramName ) {
					return;
				}

				if ( $target.is( ':checkbox' ) ) {
					value = $target.is( ':checked' ) ? $target.val() : '';
				} else {
					value = $target.val();
				}

				this.elementView.changeParam( paramName, value, '' );
				this.elementView.addLoadingOverlay();

				this._getWidgetMarkup();
			},

			/**
			 * Triggers calls to get the correct selected widget markup.
			 *
			 * @since 2.2.0
			 * @return {void}
			 */
			getWidgetMarkup: function() {
				this.elementView.contentView.getMarkup( this.elementView );
			},

			/**
			 * Handles widget event change.
			 *
			 * @since 2.2.0
			 * @return {void}
			 */
			widgetChanged: function() {
				this.clean();
				this.setWidgetFields();
				this.reRender();
			},

			/**
			 * Destroy the options.
			 *
			 * @since 2.0.0
			 * @returns {void}
			 */
			onDestroyOptions: function() {
				this.destroyWidgetOptions();
			}

		} );

	} );

}( jQuery ) );

/* global FusionPageBuilderApp, fusionAllElements */
( function( $ ) {

	$( document ).ready( function() {

		FusionPageBuilderApp.widgetShortcodeFilter = function( attributes, view ) {
			var newAttributes,
				defaults,
				widgetParams,
				createWidgetFieldName,
				widget; // eslint-disable-line no-unused-vars

			widget = view.settingsView.getWidget();

			// If no widget is selected return default attributes
			if ( ! widget ) {
				return attributes;
			}

			widgetParams = Object.keys( widget.fields );
			defaults = Object.keys( fusionAllElements.fusion_widget.defaults );
			newAttributes = {
				params: {}
			};

			// Creates a formatted widget form field name
			createWidgetFieldName = function ( className, name ) {
				var prefix = className.toLowerCase().replace( /\\/g, '_' ) + '__';

				try {
					prefix += name.match( /\[(.*?)\]/g ).slice( -1 )[ 0 ].replace( /\[|(\])/g, '' );
				} catch ( e ) {
					return prefix;
				}

				return prefix;
			};

			// Check if selected widget has params/input fields
			if ( widgetParams ) {
				// Make sure we're only passing params that correspond to selected widget
				_.each( attributes.params, function( param, key ) {
					if ( widgetParams.includes( key ) || defaults.includes( key ) ) {
						newAttributes.params[ key ] = param;
					}
				} );
			}

			// If widget is invalid find the appended form and retrieve it's values
			if ( widget.isInvalid ) {
				view.$el
				.find( '.fusion-widget-settings-form' )
				.find( 'fieldset, input, select, textarea' )
				.not( '[type="button"]' )
				.each( function() {
					var key = createWidgetFieldName( attributes.params.type, this.name );
					if ( widgetParams.includes( key ) ) {
						newAttributes.params[ key ] = attributes.params[ this.id ];
						if ( 'checkbox' === this.type ) {
							newAttributes.params[ key ] =  this.checked ? this.value : '';
						}
					}
				} );
			}

			// HTML and Text widget especial escape.
			if ( newAttributes.params.wp_widget_custom_html__content ) {
				newAttributes.params.wp_widget_custom_html__content = _.escape( newAttributes.params.wp_widget_custom_html__content );
			} else if ( newAttributes.params.wp_widget_text__text ) {
				newAttributes.params.wp_widget_text__text = _.escape( newAttributes.params.wp_widget_text__text );
			}

			return newAttributes;
		};

	} );

}( jQuery ) );

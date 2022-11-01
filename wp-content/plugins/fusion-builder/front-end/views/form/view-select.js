/* global FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Select View.
		FusionPageBuilder.fusion_form_select = FusionPageBuilder.FormComponentView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.1
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Create attribute objects;
				attributes.styles = this.buildStyles( atts.values );
				attributes.html   = this.generateFormFieldHtml( this.generateSelectField( atts.values ) );

				return attributes;
			},

			generateSelectField: function( values ) {
				var elementData,
					elementHtml,
					options    = '',
					html       = '';

				if ( 'undefined' === typeof values.options || ! values.options ) {
					return html;
				}

				values.options = JSON.parse( FusionPageBuilderApp.base64Decode( values.options ) );

				elementData = this.elementData( values );

				if ( '' !== values.tooltip ) {
					elementData.label += this.getFieldTooltip( values );
				}

				if ( 'undefined' !== typeof values.placeholder && '' !== values.placeholder ) {
					options += '<option value="" selected disabled>' + values.placeholder + '</option>';
				}

				_.each( values.options, function( option ) {
					var selected = option[ 0 ] ? ' selected ' : '';
					var label    = option[ 1 ].trim();
					var value    = '' !== option[ 2 ] ? option[ 2 ].trim() : label;

					options += '<option value="' + value + '" ' + selected + '>' + label + '</option>';
				} );

				elementHtml  = '<div class="fusion-select-wrapper">';
				elementHtml += '<select class="fusion-form-input" name="' + values.name + '"' + elementData[ 'class' ] + elementData.required + elementData.style + elementData.holds_private_data + '>';
				elementHtml += options;
				elementHtml += '</select>';
				elementHtml += '<div class="select-arrow"><svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M1.5 1.75L6 6.25L10.5 1.75" stroke="#6D6D6D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/> </svg>';
				elementHtml += '</div>';

				elementHtml = this.generateIconHtml( values, elementHtml );

				html = this.generateLabelHtml( html, elementHtml, elementData.label );

				return html;
			}

		} );
	} );
}( jQuery ) );

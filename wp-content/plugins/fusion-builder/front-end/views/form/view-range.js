var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Range View.
		FusionPageBuilder.fusion_form_range = FusionPageBuilder.FormComponentView.extend( {

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
				attributes.html   = this.generateFormFieldHtml( this.generateRangeField( atts.values ) );

				return attributes;
			},

			generateRangeField: function ( atts ) {
				var elementData,
					elementHtml,
					containerClass = 'fusion-form-range-field-container',
					html = '';

				elementData = this.elementData( atts );

				elementData = this.generateTooltipHtml( atts, elementData );

				if ( 'right' === atts.orientation ) {
					containerClass += ' orientation-right';
				}

				elementHtml = '<div class="' + containerClass + '">';
				if ( 'right' !== atts.orientation ) {
					elementHtml += '<input type="text" disabled class="fusion-form-range-value" value="' + atts.value + '"/>';
				}
				elementHtml += '<input type="range" name="' + atts.name + '" min="' + atts.min + '" max="' + atts.max + '" step="' + atts.step + '" value="' + atts.value + '"' + elementData[ 'class' ] + elementData.required + elementData.placeholder + elementData.holds_private_data + '/>';
				if ( 'right' === atts.orientation ) {
					elementHtml += '<input type="text" disabled class="fusion-form-range-value" value="' + atts.value + '"/>';
				}
				elementHtml += '</div>';

				html = this.generateLabelHtml( html, elementHtml, elementData.label );

				return html;
			}

		} );
	} );
}( jQuery ) );

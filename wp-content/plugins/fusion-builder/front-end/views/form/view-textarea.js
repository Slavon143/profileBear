var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Textarea View.
		FusionPageBuilder.fusion_form_textarea = FusionPageBuilder.FormComponentView.extend( {

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
				attributes.html   = this.generateFormFieldHtml( this.generateTextareaField( atts.values ) );

				return attributes;
			},

			generateTextareaField: function ( atts ) {
				var elementData,
					elementHtml,
					value,
					html = '';

				elementData = this.elementData( atts );

				elementData = this.generateTooltipHtml( atts, elementData );

				value       = 'undefined' !== typeof elementData.value ? elementData.value : '';

				elementHtml = '<textarea cols="40" rows="' + atts.rows + '" name="' + atts.name + '"' + elementData[ 'class' ] + elementData.required + elementData.disabled + elementData.placeholder + elementData.holds_private_data + '>' + value + '</textarea>';

				elementHtml = this.generateIconHtml( atts, elementHtml );

				html = this.generateLabelHtml( html, elementHtml, elementData.label );

				return html;
			}

		} );
	} );
}( jQuery ) );

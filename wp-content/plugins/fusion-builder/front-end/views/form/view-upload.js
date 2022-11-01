/* global fusionBuilderText */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Upload View.
		FusionPageBuilder.fusion_form_upload = FusionPageBuilder.FormComponentView.extend( {

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
				attributes.html   = this.generateFormFieldHtml( this.generateUploadField( atts.values ) );

				return attributes;
			},

			generateUploadField: function ( atts ) {
				var elementData,
					elementHtml,
					style,
					html = '';

				atts[ 'class' ] = ( '' !== atts[ 'class' ] ) ? atts[ 'class' ] + ' fusion-form-file-upload' : 'fusion-form-file-upload';

				elementData = this.elementData( atts );

				elementData = this.generateTooltipHtml( atts, elementData );

				elementData.multiple = 'yes' === atts.multiple ? ' multiple' : '';

				elementData.name     = atts.name;
				elementData.multiple = '';
				if ( 'yes' === atts.multiple ) {
					elementData.name     += '[]';
					elementData.multiple = ' multiple';
				}

				elementData.accept = 'undefined' !== typeof atts.extensions && '' !== atts.extensions ? 'accept="' + atts.extensions + '"' : '';

				elementHtml  = '<div class="fusion-form-upload-field-container" style="' + style + '">';
				elementHtml += '<input type="file" name="' + elementData.name + '" value="" ' + elementData[ 'class' ] + elementData.accept + elementData.required + elementData.placeholder + elementData.upload_size + elementData.multiple + '/>';
				elementHtml += '<input type="text" disabled value="" class="fusion-form-upload-field" ' + elementData.required + elementData.placeholder + elementData.holds_private_data + '/>';
				elementHtml += '<a class="fusion-button button-flat button-medium button-default button-1 fusion-button-default-span fusion-button-default-type fusion-form-upload-field-button" style="border-radius:0;"><span class="fusion-button-text">' + fusionBuilderText.choose_file + '</span></a>';

				elementHtml += '</div>';

				elementHtml = this.generateIconHtml( atts, elementHtml );

				html = this.generateLabelHtml( html, elementHtml, elementData.label );

				return html;
			}

		} );
	} );
}( jQuery ) );

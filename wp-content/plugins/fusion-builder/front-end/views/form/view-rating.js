/* global fusionSanitize, FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Rating View.
		FusionPageBuilder.fusion_form_rating = FusionPageBuilder.FormComponentView.extend( {

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
				attributes.html   = this.generateFormFieldHtml( this.generateRatingField( atts.values ) );

				return attributes;
			},

			generateRatingField: function( values ) {
				var elementData,
					elementName,
					elementHtml = '',
					limit,
					styles     = '',
					options    = '',
					hoverColor = '',
					html       = '',
					option;

				elementData = this.elementData( values );
				limit 		= fusionSanitize.number( values.limit );
				elementName = values.name;

				while ( 0 < limit ) {
					option = limit;
					options += '<input id="' + option + '-' + this.model.get( 'cid' ) + '" type="radio" value="' + option + '" name="' + elementName + '"' + elementData[ 'class' ] + elementData.required + elementData.checked + elementData.holds_private_data + '/>';
					options += '<label for="' + option + '-' + this.model.get( 'cid' ) + '" class="fusion-form-rating-icon">';
					options += '<i class="' + values.icon + '"></i>';
					options +=
					'</label>';
					limit--;
				}

				// CSS for .rating-icon
				if ( values.icon_color || ( 'undefined' !== typeof values.icon_size && '' !== values.icon_size ) ) {
					styles += '.fusion-form-form-wrapper .fusion-form-field .fusion-form-rating-area-' + this.model.get( 'cid' ) + '.fusion-form-rating-area .fusion-form-rating-icon { ';
					if ( values.icon_color ) {
						styles += 'color: ' + values.icon_color + ';';
					}
					if ( 'undefined' !== typeof values.icon_size && '' !== values.icon_size ) {
						styles += 'font-size: ' + values.icon_size + ';';
					}
					styles += '}';
				}

				// CSS for .rating-icon:hover, .rating-icon:checked
				if ( values.active_icon_color ) {
					hoverColor = jQuery.AWB_Color( values.active_icon_color ).alpha( 0.5 ).toVarOrRgbaString();
					styles += '.fusion-form-form-wrapper .fusion-form-field .fusion-form-rating-area-' + this.model.get( 'cid' ) + '.fusion-form-rating-area .fusion-form-input:checked~label i{ color: ' + values.active_icon_color + ';}';

					styles += '.fusion-form-form-wrapper .fusion-form-field .fusion-form-rating-area-' + this.model.get( 'cid' ) + '.fusion-form-rating-area .fusion-form-input:checked:hover ~ label i,';
					styles += '.fusion-form-form-wrapper .fusion-form-field .fusion-form-rating-area-' + this.model.get( 'cid' ) + '.fusion-form-rating-area .fusion-form-rating-icon:hover i,';
					styles += '.fusion-form-form-wrapper .fusion-form-field .fusion-form-rating-area-' + this.model.get( 'cid' ) + '.fusion-form-rating-area .fusion-form-rating-icon:hover ~ label i,';
					styles += '.fusion-form-form-wrapper .fusion-form-field .fusion-form-rating-area-' + this.model.get( 'cid' ) + '.fusion-form-rating-area .fusion-form-input:hover ~ label i{ color: ' + hoverColor + ';}';
				}

				if ( '' !== styles ) {
					elementHtml += '<style type="text/css">' + styles + '</style>';
				}

				elementHtml += '<fieldset class="fusion-form-rating-area fusion-form-rating-area-' + this.model.get( 'cid' ) + ( FusionPageBuilderApp.$el.hasClass( 'rtl' ) ? ' rtl' : '' ) + '">';
				elementHtml += options;
				elementHtml += '</fieldset>';

				if ( '' !== values.tooltip ) {
					elementData.label += this.getFieldTooltip( values );
				}

				html = this.generateLabelHtml( html, elementHtml, elementData.label );

				return html;
			}

		} );
	} );
}( jQuery ) );

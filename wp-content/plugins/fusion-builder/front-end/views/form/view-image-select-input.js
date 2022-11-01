/* global FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Select Image View.
		FusionPageBuilder.fusion_form_image_select_input = FusionPageBuilder.ChildElementView.extend( {

			onInit: function() {
				this.formData = FusionApp.data.postMeta;
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 3.1
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {},
					parentView = window.FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) ),
					params     = this.model.get( 'params' );

				// Create attribute objects;
				attributes.imageUrl      = atts.values.image;
				attributes.labelId       = 'HTML+ ' + this.model.get( 'cid' );
				attributes.value         = '' === atts.values.name ? atts.values.label.toLowerCase().replace( ' ', '_' ) : atts.values.name;
				attributes.inputName     = 'input' + this.model.get( 'cid' );
				attributes.elementData   = parentView.elementDataValues;
				attributes.checked       = 'yes' === atts.values.checked ? ' checked ' : '';
				attributes.labelPosition = 'undefined' !== typeof this.formData._fusion.label_position ? this.formData._fusion.label_position : 'above';
				attributes.label         = 'undefined' !== typeof params.label && '' !== params.label ? atts.values.label : '';
				attributes.inputType     = 'undefined' !== typeof parentView.model.attributes.params.multiple_select && 'yes' === parentView.model.attributes.params.multiple_select ? 'checkbox' : 'radio';

				return attributes;
			}

		} );
	} );
}( jQuery ) );

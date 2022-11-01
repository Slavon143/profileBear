var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Modal Text Link View.
		FusionPageBuilder.fusion_modal_text_link = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				attributes.modalTextShortcode = this.buildShortcodeAttr( atts.values );

				// Any extras that need passed on.
				attributes.output = atts.values.element_content;
				attributes.name   = atts.values.name;
				attributes.inline = 'undefined' !== typeof atts.inlineElement;
				attributes.label  = window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				attributes.icon   = window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;
				attributes.cid    = this.model.get( 'cid' );

				return attributes;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildShortcodeAttr: function( values ) {
				var modalTextShortcode = {
					class: 'fusion-modal-text-link'
				};

				if ( '' !== values.name ) {
					modalTextShortcode[ 'data-toggle' ] = 'modal';
					modalTextShortcode[ 'data-target' ] = '.fusion-modal.' + values.name;
				}

				if ( 'undefined' !== typeof values[ 'class' ] && '' !== values[ 'class' ] ) {
					modalTextShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( 'undefined' !==  typeof values.id && '' !== values.id ) {
					modalTextShortcode.id = values.id;
				}
				modalTextShortcode.href = '#';

				// Additional attributes for editable.
				modalTextShortcode = _.fusionInlineEditor( {
					cid: this.model.get( 'cid' ),
					'disable-return': true,
					'disable-extra-spaces': true,
					toolbar: true
				}, modalTextShortcode );

				return modalTextShortcode;
			}
		} );
	} );
}( jQuery ) );

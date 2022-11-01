var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// One Page Link view.
		FusionPageBuilder.fusion_one_page_text_link = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Create attribute objects.
				attributes.onePageTextLinkShortcode = this.buildShortcodeAttr( atts.values );
				attributes.elementContent           = atts.values.element_content;
				attributes.inline                   = 'undefined' !== typeof atts.inlineElement;
				attributes.cid                      = atts.cid;
				attributes.label                    = window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				attributes.icon                     = window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;

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
				var onePageTextLinkShortcode = {
					class: 'fusion-one-page-text-link'
				};

				if ( '' !== values[ 'class' ] ) {
					onePageTextLinkShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					onePageTextLinkShortcode.id = values.id;
				}

				onePageTextLinkShortcode.href = values.link;

				return onePageTextLinkShortcode;
			}

		} );
	} );
}( jQuery ) );

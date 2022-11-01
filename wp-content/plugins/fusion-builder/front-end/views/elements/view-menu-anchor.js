var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Menu Anchor Element View.
		FusionPageBuilder.fusion_menu_anchor = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Create attribute objects
				attributes.attr  = this.buildAttr( atts.values );
				attributes.name  = atts.values.name;
				attributes.label = window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				attributes.icon  = window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;

				return attributes;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = {
					class: 'fusion-menu-anchor',
					id: values.name
				};

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				return attr;
			}
		} );
	} );
}( jQuery ) );

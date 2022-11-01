var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Tooltip View.
		FusionPageBuilder.fusion_tooltip = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				this._refreshJs();
			},

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
				attributes.attr = this.buildAttr( atts.values );

				// Any extras that need passed on.
				attributes.cid     = this.model.get( 'cid' );
				attributes.content = atts.values.element_content;

				return attributes;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object} - Returns the attributes.
			 */
			buildAttr: function( values ) {
				var attr = {
					class: 'fusion-tooltip tooltip-shortcode ' + values[ 'class' ]
				};

				attr.id = values.id;

				attr[ 'data-animation' ] = values.animation;
				attr[ 'data-delay' ]     = values.delay;
				attr[ 'data-placement' ] = values.placement;
				attr.title             = values.title;
				attr[ 'data-title' ]     = values.title;
				attr[ 'data-toggle' ]    = 'tooltip';
				attr[ 'data-trigger' ]   = values.trigger;

				return attr;
			}
		} );
	} );
}( jQuery ) );

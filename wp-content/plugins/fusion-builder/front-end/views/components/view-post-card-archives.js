/* global fusionAllElements, FusionApp, FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Related Component View.
		FusionPageBuilder.fusion_tb_post_card_archives = FusionPageBuilder.fusion_post_cards.extend( {

			onInit: function() {
				var output, markupIsEmpty, markupIsPlaceholder;

				this.filterTemplateAtts = this._filterTemplateAtts( this.filterTemplateAtts );

				output				= this.model.attributes.markup && this.model.attributes.markup.output;
				markupIsEmpty 		= '' === output;
				markupIsPlaceholder = output && output.includes( 'fusion-builder-placeholder' );

				if ( markupIsEmpty || markupIsPlaceholder ) {
					this.model.attributes.markup.output = this.getComponentPlaceholder();
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 3.3
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			_filterTemplateAtts: function( filterTemplateAtts ) {
				var self = this;
				return function( atts ) {
					atts.params.show_title = 'yes';
					atts = filterTemplateAtts.call( self, atts );
					atts.placeholder = self.getComponentPlaceholder();
					return atts;
				};
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 3.3
			 * @return {void}
			 */
			afterPatch: function() {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-element-render-fusion_post_cards', this.model.attributes.cid );
			},

			/**
			 * Runs just after render on cancel.
			 *
			 * @since 3.5
			 * @return null
			 */
			beforeGenerateShortcode: function() {
				var elementType = this.model.get( 'element_type' ),
					options     = fusionAllElements[ elementType ].params,
					values      = jQuery.extend( true, {}, fusionAllElements[ elementType ].defaults, _.fusionCleanParameters( this.model.get( 'params' ) ) );

				if ( 'object' !== typeof options ) {
					return;
				}

				// If images needs replaced lets check element to see if we have media being used to add to object.
				if ( 'undefined' !== typeof FusionApp.data.replaceAssets && FusionApp.data.replaceAssets && ( 'undefined' !== typeof FusionApp.data.fusion_element_type || 'fusion_template' === FusionApp.getPost( 'post_type' ) ) ) {

					this.mapStudioImages( options, values );

					if ( '' !== values.post_card ) {
						// If its not within object already, add it.
						if ( 'undefined' === typeof FusionPageBuilderApp.mediaMap.post_cards[ values.post_card ] ) {
							FusionPageBuilderApp.mediaMap.post_cards[ values.post_card ] = true;
						}
					}

				}
			}

		} );
	} );
}( jQuery ) );

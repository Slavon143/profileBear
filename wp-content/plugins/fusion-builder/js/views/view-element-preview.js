/* global fusionAllElements, FusionApp, fusionDynamicData, fusionAllElements  */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Element Preview View
		FusionPageBuilder.ElementPreviewView = window.wp.Backbone.View.extend( {

			className: 'fusion_module_block_preview ',

			dynamicParams: {},

			// Elements which use element_content for preview.
			contentPreviewElements: [ 'fusion_text', 'fusion_title', 'fusion_alert', 'fusion_button', 'fusion_imageframe', 'fusion_sharing' ],

			globalIconPlaceholder: '#fusion_dynamic_data_icon#',

			initialize: function() {
				this.dynamicParams = this.options.dynamicParams;

				if ( jQuery( '#' + fusionAllElements[ this.model.attributes.element_type ].preview_id ).length ) {
					this.template = FusionPageBuilder.template( jQuery( '#' + fusionAllElements[ this.model.attributes.element_type ].preview_id ).html() );
				} else {
					this.template = FusionPageBuilder.template( jQuery( '#fusion-builder-block-module-default-preview-template' ).html() );
				}
			},

			render: function() {
				var html = this.template( this.getTemplateAttributes() );

				this.$el.html( this.updatePreview( html ) );

				return this;
			},

			/**
			 * Filter template attributes.
			 *
			 * @since 2.1
			 * @return {object}
			 */
			getTemplateAttributes: function() {
				var atts        = jQuery.extend( true, {}, this.model.attributes ),
					dynamicData = this.getDynamicData(),
					label       = '';

				// If element preview could be updated.
				if ( -1 !== this.contentPreviewElements.indexOf( this.model.attributes.element_type ) ) {

					// And there is dynamic content.
					if ( ! _.isEmpty( dynamicData ) && 'undefined' !== typeof dynamicData.element_content ) {

						// Elements which use element_content for preview, for example text element.
						label = '';
						if ( 'undefined' !== typeof FusionApp && 'undefined' !== typeof FusionApp.data.dynamicOptions[ dynamicData.element_content.data ] ) {
							label = FusionApp.data.dynamicOptions[ dynamicData.element_content.data ].label;
						} else if ( 'undefined' !== typeof fusionDynamicData.dynamicOptions[ dynamicData.element_content.data ] ) {
							label = fusionDynamicData.dynamicOptions[ dynamicData.element_content.data ].label;
						}
						atts.params.element_content = this.globalIconPlaceholder + label;
					}

				}

				return atts;
			},

			/**
			 * Updates preview with dynamic data if needed.
			 *
			 * @since 2.1
			 * @param {string} html
			 * @return {string}
			 */
			updatePreview: function( html ) {
				var dynamicData         = this.getDynamicData(),
					elDynamicParams     = [],
					childHasDynamicData = false,
					iconHTML            = '<span class="fusiona-dynamic-data"></span>',
					label               = '',
					childLabel          = '',
					$dynamicPreview;

				// Check if element children use dynamic data.
				if ( 'undefined' !== typeof this.model.attributes.multi && 'multi_element_parent' === this.model.attributes.multi &&
					'undefined' !== typeof this.model.attributes.params.element_content && -1 !== this.model.attributes.params.element_content.indexOf( 'dynamic_params' )
				) {
					childHasDynamicData = true;
				}

				// Update preview if element or it's child uses dynamic data.
				if ( -1 === this.contentPreviewElements.indexOf( this.model.attributes.element_type ) && ( ! _.isEmpty( dynamicData ) || childHasDynamicData ) ) {
					$dynamicPreview = jQuery( '<div />', { html: html } );

					// If children use dynamic content remove their preview.
					if ( childHasDynamicData ) {
						$dynamicPreview.find( 'ul' ).remove();

						// Set child preview.
						if ( 'undefined' !== typeof fusionAllElements[ this.model.attributes.element_type ] && 'undefined' !== typeof fusionAllElements[ fusionAllElements[ this.model.attributes.element_type ].element_child ] ) {
							childLabel = fusionAllElements[ fusionAllElements[ this.model.attributes.element_type ].element_child ].name;
							elDynamicParams.push( childLabel );
						}
					}

					_.each( dynamicData, function( dynamic ) {
						label = '';
						if ( 'undefined' !== typeof FusionApp && 'undefined' !== typeof FusionApp.data.dynamicOptions[ dynamic.data ] ) {
							label = FusionApp.data.dynamicOptions[ dynamic.data ].label;
						} else if ( 'undefined' !== typeof fusionDynamicData.dynamicOptions[ dynamic.data ] ) {
							label = fusionDynamicData.dynamicOptions[ dynamic.data ].label;
						}
						elDynamicParams.push( label );
					} );

					$dynamicPreview.append( '<div class="fusion-builder-dynamic-data-preview fusion-builder-dynamic-data-preview-inline">' + iconHTML + elDynamicParams.join( ', ' ) + '</div>' );

					html = $dynamicPreview.html();
				}

				// Replace placeholders if added during attributes filtering.
				return html.replace( this.globalIconPlaceholder, iconHTML );
			},

			/**
			 * Get element dynamic data.
			 *
			 * @since 2.1
			 * @return {object}
			 */
			getDynamicData: function() {
				var dynamicData = {};

				if ( 'undefined' !== typeof this.dynamicParams ) {

					// Get dynamic data in live editor.
					dynamicData = this.dynamicParams.getAll();
				} else if ( 'undefined' !== typeof this.model.attributes.dynamic_params ) {

					// Get dynamic data in backend editor.
					dynamicData = this.model.attributes.dynamic_params;
				}

				return dynamicData;
			}

		} );
	} );
}( jQuery ) );

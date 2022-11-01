/* global FusionApp */
/* jshint -W024 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Portfolio View.
		FusionPageBuilder.fusion_privacy = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0.0
			 * @returns null
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Build attributes.
				attributes.attr        = this.buildAttr( atts.values );
				attributes.contentAttr = this.buildContentAttr( atts.values );
				attributes.formAttr    = this.buildFormAttr( atts.values );

				attributes.embeds       = this.buildEmbeds( atts.extras.embed_types );
				attributes.buttonString = atts.extras.button_string;

				// Any extras that need passed on.
				attributes.cid    = this.model.get( 'cid' );
				attributes.output = atts.values.element_content;

				return attributes;
			},

			buildAttr: function( values ) {
				var attr = {
					class: 'fusion-privacy-element fusion-privacy-element-' + this.model.get( 'cid' )
				};

				attr = _.fusionVisibilityAtts( values.hide_on_mobile, attr );

				// Add custom class.
				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				// Add custom id.
				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				attr = _.fusionAnimations( values, attr );

				return attr;
			},

			buildContentAttr: function() {
				var self = this,
					contentAttr = {
						class: 'fusion-privacy-form-intro'
					};

				contentAttr = _.fusionInlineEditor( {
					cid: self.model.get( 'cid' )
				}, contentAttr );

				return contentAttr;
			},

			buildFormAttr: function( values ) {
				return {
					id: 'fusion-privacy-form-' + this.model.get( 'cid' ),
					action: '',
					method: '',
					class: 'fusion-privacy-form fusion-privacy-form-' + values.form_field_layout
				};

			},

			buildEmbeds: function( embeds ) {
				var builtEmbeds = [],
					selection   = FusionApp.settings.privacy_embed_defaults,
					flatTos     = 'undefined' !== typeof FusionApp.sidebarView ? FusionApp.sidebarView.getFlatToObject() : false,
					embedLabel,
					embedSelected;

				if ( 'object' === typeof embeds && embeds.length ) {
					_.each( embeds, function( embed ) {
						embedSelected = '';
						if ( 'object' === typeof selection && -1 !== selection.indexOf( embed ) ) {
							embedSelected = 'checked';
						}
						if ( flatTos ) {
							embedLabel = flatTos.privacy_embed_types.choices[ embed ];
						}

						builtEmbeds.push( {
							id: embed,
							label: embedLabel,
							selected: embedSelected
						} );
					} );
					return builtEmbeds;
				}
				return false;
			}
		} );
	} );
}( jQuery ) );

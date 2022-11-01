var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Lightbox View.
		FusionPageBuilder.fusion_lightbox = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs on render.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				this.afterPatch();
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var item = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '[data-rel="iLightbox"]' ) );

				if ( 'object' === typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaLightBox ) {
					if ( 'undefined' !== typeof this.iLightbox ) {
						this.iLightbox.destroy();
					}

					if ( item.length ) {
						this.iLightbox = item.iLightBox( jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaLightBox.prepare_options( 'single' ) );
					}
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				// Create attribute objects.
				atts.name    = atts.params.alt_text;
				atts.label   = window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				atts.icon    = window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;
				atts.attr    = this.buildAttr( atts.params );
				atts.imgAttr = this.buildImgAttr( atts.params );
				atts.values  = atts.params;

				return atts;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 3.5
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {

				// Main wrapper attributes
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'awb-lightbox awb-lightbox-' + this.model.get( 'cid' ),
						style: ''
					} );

				if ( values.title ) {
					attr.title = attr[ 'data-title' ] = values.title; // eslint-disable-line no-multi-assign
				}

				if ( 'undefined' !== typeof values.type && 'video' === values.type ) {
					attr.href = values.video_url;
				} else if ( 'link' === values.type ) {
					attr.href = values.link_url;
				} else {
					attr.href = values.full_image;
				}

				if ( values.description ) {
					attr[ 'data-caption' ] = values.description;
				}

				if ( attr.href ) {
					attr[ 'data-rel' ] = 'iLightbox';
				}

				if ( 'undefined' !== typeof values[ 'class' ] && '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( 'undefined' !== typeof values.id && '' !== values.id ) {
					attr.id = values.id;
				}

				return attr;
			},

			/**
			 * Builds image attributes.
			 *
			 * @since 3.5
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildImgAttr: function( values ) {

				var attr = {};

				if ( values.thumbnail_image ) {
					attr.src = values.thumbnail_image;
				}

				if ( values.alt_text ) {
					attr.alt = values.alt_text;
				}

				return attr;
			}

		} );
	} );
}( jQuery ) );

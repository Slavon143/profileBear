/* global FusionApp, fusionAppConfig */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Post Slider Element View.
		FusionPageBuilder.fusion_postslider = FusionPageBuilder.ElementView.extend( {

			/**
			 * Vars for the flexslider initialization.
			 *
			 * @since 2.0
			 */
			flexsliderVars: {},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				this.afterPatch();
			},

			/**
			 * Runs before view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforePatch: function() {
				var element = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.fusion-post-slider.fusion-flexslider' ) );

				// Get the flexslider init vars, so that we can re-init after DOM patch.
				if ( 'undefined' !== typeof element.data( 'flexslider' ) ) {
					this.flexsliderVars = element.data( 'flexslider' ).vars;
					element.flexslider( 'destroy' );
				}
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var self = this,
					element = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.fusion-post-slider.fusion-flexslider' ) ),
					smoothHeight = '0' === FusionApp.settings.slideshow_smooth_height ? false : true;

				// Needed in case the layout was changed.
				if ( ! _.isEmpty( self.flexsliderVars ) ) {
					self.flexsliderVars.controlNav = ( 'attachments' === self.model.attributes.params.layout ) ? 'thumbnails' : true;
				}

				self.flexsliderVars.smoothHeight = smoothHeight;

				if ( 0 < element.length ) {

					// Re-init flexslider.
					setTimeout( function() {
						if ( 'undefined' !== typeof element.flexslider ) {
							element.flexslider(
								self.flexsliderVars
							);
						}
					}, 300 );
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
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );

				// Create attribute objects
				attributes.sliderAttr = this.buildSliderAttr( atts.values );
				if ( 'undefined' !== typeof atts.query_data ) {
					attributes.datasets = this.buildDatasetAttr( atts.values, atts.query_data );
				}
				attributes.thumbAttr  = this.buildThumbAttr( atts.values );

				// Any extras that need passed on.
				attributes.cid        = this.model.get( 'cid' );
				attributes.query_data = atts.query_data;
				attributes.values     = atts.values;

				return attributes;
			},

			/**
			 * Modify the values.
			 *
			 * @since 3.8
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildSliderAttr: function( values ) {

				// FlexsliderShortcode Attributes.
				var flexsliderShortcode = {
					class: 'fusion-flexslider fusion-post-slider fusion-flexslider-loading flexslider-' + values.layout,
					style: ''
				};

				flexsliderShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, flexsliderShortcode );

				if ( 'yes' === values.lightbox && 'attachments' === values.layout ) {
					flexsliderShortcode[ 'class' ] += ' flexslider-lightbox';
				}

				if ( '' !== values.margin_top ) {
					flexsliderShortcode.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_right ) {
					flexsliderShortcode.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( '' !== values.margin_bottom ) {
					flexsliderShortcode.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( '' !== values.margin_left ) {
					flexsliderShortcode.style += 'margin-left:' + values.margin_left + ';';
				}

				if ( '' !== values[ 'class' ] ) {
					flexsliderShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					flexsliderShortcode.id = values.id;
				}

				return flexsliderShortcode;
			},

			/**
			 * Builds image attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @param {Object} queryData - The AJAX query data.
			 * @return {Object}
			 */
			buildDatasetAttr: function( values, queryData ) {
				var view = this,
					datasetAttr = {};

				_.each( queryData.datasets, function( dataset, index ) {
					datasetAttr[ index ] = {};

					datasetAttr[ index ].link_attributes  = view.buildLinkAttr( dataset, values.layout );
					datasetAttr[ index ].image_attributes = view.buildImageAttr( dataset );

					if ( 'attachments' === values.layout ) {
						datasetAttr[ index ].li_attributes = view.buildListElementAttr( dataset );
					} else {
						datasetAttr[ index ].title_link_attributes = view.buildTitleLinkAttr( dataset );
					}
				} );

				return datasetAttr;
			},

			/**
			 * Builds link attributes.
			 *
			 * @since 2.0
			 * @param {Object} dataset - The dataset values.
			 * @param string layout - The slider layout.
			 * @return {Object}
			 */
			buildLinkAttr: function( dataset, layout ) {
				var linkAttr = {};

				if ( 'attachments' === layout ) {
					linkAttr.href = dataset.image;
					linkAttr[ 'data-title' ] = dataset.title;
					linkAttr[ 'data-caption' ] = dataset.caption;
					linkAttr.title = dataset.title;
					linkAttr[ 'data-rel' ] = 'prettyPhoto[flex_' + this.model.get( 'cid' ) + ']';
				} else if ( 'posts' === layout || 'posts-with-excerpt' === layout ) {
					linkAttr.href = dataset.permalink;
					linkAttr[ 'data-title' ] = dataset.title_attribute;
				}

				return linkAttr;
			},

			/**
			 * Builds image attributes.
			 *
			 * @since 2.0
			 * @param {Object} dataset - The dataset values.
			 * @return {Object}
			 */
			buildImageAttr: function( dataset ) {
				var imageAttr = {};

				imageAttr.src = dataset.image;
				imageAttr.alt = dataset.alt;

				return imageAttr;
			},

			/**
			 * Builds list elemet attributes.
			 *
			 * @since 2.0
			 * @param {Object} dataset - The dataset values.
			 * @return {Object}
			 */
			buildListElementAttr: function( dataset ) {
				var liElementAttr = {};

				liElementAttr[ 'data-thumb' ] = dataset.thumb;

				return liElementAttr;
			},

			/**
			 * Builds title link attributes.
			 *
			 * @since 2.0
			 * @param {Object} dataset - The dataset values.
			 * @return {Object}
			 */
			buildTitleLinkAttr: function( dataset ) {
				var titleLinkAttr = {};

				titleLinkAttr.href = dataset.permalink;

				return titleLinkAttr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildThumbAttr: function( values ) {

				// FlexsliderShortcodeThumbnails Attributes.
				var flexsliderShortcodeThumbnails = {
					class: 'flexslider'
				};
				if ( 'attachments' === values.layout ) {
					flexsliderShortcodeThumbnails[ 'class' ] += ' fat';
				}

				return flexsliderShortcodeThumbnails;
			}
		} );
	} );

	_.extend( FusionPageBuilder.Callback.prototype, {
		fusion_post_slider_query: function( name, value, modelData, args, cid, action, model, view ) { // jshint ignore: line

			// First update value in model.
			view.changeParam( name, value );

			modelData.params.post_id = '';
			if ( 'attachments' === modelData.params.layout ) {
				modelData.params.post_id = FusionApp.data.postDetails.post_id;
			}

			// Send this data with ajax or rest.
			jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'get_fusion_post_slider',
					params: modelData.params
				}
			} )
			.done( function( response ) {
				model.set( 'query_data', response );

				view.reRender();
			} );
		}
	} );
}( jQuery ) );

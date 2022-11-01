/* jshint -W024 */
/* global fusionAllElements, FusionApp, FusionPageBuilderApp, awbPalette */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Video View.
		FusionPageBuilder.fusion_video = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.1
			 * @return {void}
			 */
			onRender: function() {
				this.afterPatch();
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.1
			 * @return {void}
			 */
			afterPatch: function() {
				var params = this.model.get( 'params' );
				var video  = this.$el.find( 'video' );

				this.$el.removeClass( 'fusion-element-alignment-right fusion-element-alignment-left' );
				if ( 'undefined' !== typeof params.alignment && ( 'right' === params.alignment || 'left' === params.alignment ) ) {
					this.$el.addClass( 'fusion-element-alignment-' + params.alignment );
				}

				this.refreshVideo( video, params );
			},

			/**
			 * Refreshes video functions.
			 *
			 * @since 2.1
			 * @param {Object} video -  The video object.
			 * @param {Object} params - The params.
			 * @return {void}
			 */
			refreshVideo: function( video, params ) {
				if ( video.length && 'undefined' !== typeof video.get( 0 ) ) {

					// Source change.
					video.get( 0 ).load();

					// Auto play.
					( 'yes' === params.autoplay ) ? video.get( 0 ).play() : video.get( 0 ).pause();

					// Mute.
					video.get( 0 ).muted = ( 'yes' === params.mute ) ? true : false;
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.1
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				this.validateValues( atts.values );

				attributes.attr        = this.buildAttr( atts.values );
				attributes.wrapperAttr = this.buildWrapperAttr( atts.values );
				attributes.videoAttr   = this.buildVideoAttr( atts.values );
				attributes.video_webm  = atts.values.video_webm;
				attributes.video       = atts.values.video;

				return attributes;
			},

			/**
			 * Validates values.
			 *
			 * @since 2.1
			 * @param {Object} values - The values.
			 * @return {void}
			 */
			validateValues: function( values ) {
				var borderRadiusTopLeft     = 'undefined' !== typeof values.border_radius_top_left && '' !== values.border_radius_top_left ? _.fusionGetValueWithUnit( values.border_radius_top_left ) : '0px',
					borderRadiusTopRight    = 'undefined' !== typeof values.border_radius_top_right && '' !== values.border_radius_top_right ? _.fusionGetValueWithUnit( values.border_radius_top_right ) : '0px',
					borderRadiusBottomRight = 'undefined' !== typeof values.border_radius_bottom_right && '' !== values.border_radius_bottom_right ? _.fusionGetValueWithUnit( values.border_radius_bottom_right ) : '0px',
					borderRadiusBottomLeft  = 'undefined' !== typeof values.border_radius_bottom_left && '' !== values.border_radius_bottom_left ? _.fusionGetValueWithUnit( values.border_radius_bottom_left ) : '0px';

				values.border_radius = borderRadiusTopLeft + ' ' + borderRadiusTopRight + ' ' + borderRadiusBottomRight + ' ' + borderRadiusBottomLeft;
				values.border_radius = ( '0px 0px 0px 0px' === values.border_radius ) ? '' : values.border_radius;

				// Box shadow.
				if ( 'yes' === values.box_shadow ) {
					values.box_shadow = _.fusionGetBoxShadowStyle( values ) + ';';
				}
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.1
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = {
					class: 'fusion-video fusion-selfhosted-video fusion-video-' + this.model.get( 'cid' ),
					style: ''
				};

				attr = _.fusionVisibilityAtts( values.hide_on_mobile, attr );

				if ( '' !== values.alignment ) {
					attr[ 'class' ] += ' fusion-align' + values.alignment;
				}
				if ( '' !== values.margin_top ) {
					attr.style += 'margin-top:' + _.fusionGetValueWithUnit( values.margin_top ) + ';';
				}
				if ( '' !== values.margin_bottom ) {
					attr.style += 'margin-bottom:' + _.fusionGetValueWithUnit( values.margin_bottom ) + ';';
				}
				if ( '' !== values.width ) {
					attr.style += 'max-width:' + values.width + ';';
				}

				// Add custom class.
				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				// Add custom id.
				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				return attr;
			},

			/**
			 * Builds wrapper attributes.
			 *
			 * @since 2.1
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildWrapperAttr: function( values ) {
				var alpha = 1,
					attr  = {
						class: 'video-wrapper',
						style: 'width:100%;'
					};

				if ( values.border_radius && '' !== values.border_radius ) {
					attr.style += 'border-radius:' + values.border_radius + ';';
				}
				if ( 'no' !== values.box_shadow ) {
					attr.style += 'box-shadow:' + values.box_shadow + ';';
				}

				if ( '' !== values.overlay_color ) {
					values.overlay_color = awbPalette.getRealColor( values.overlay_color );
					alpha = jQuery.AWB_Color( values.overlay_color ).alpha();
					if ( 1 === alpha ) {
						values.overlay_color = jQuery.AWB_Color( values.overlay_color ).alpha( 0.5 ).toVarOrRgbaString();
					}
					attr[ 'class' ] += ' fusion-video-overlay';
					attr.style += 'background-color:' + values.overlay_color + ';';
				}

				return attr;
			},

			/**
			 * Builds video attributes.
			 *
			 * @since 2.1
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildVideoAttr: function( values ) {
				var attr  = {
					playsinline: 'true',
					width: '100%',
					style: 'object-fit: cover;'
				};

				if ( 'yes' === values.autoplay ) {
					attr.autoplay = 'true';
				}

				if ( 'yes' === values.mute ) {
					attr.muted = 'true';
				}

				if ( 'yes' === values.loop ) {
					attr.loop = 'true';
				}

				if ( '' !== values.preview_image ) {
					attr.poster = values.preview_image;
				}

				if ( '' !== values.preload ) {
					attr.preload = values.preload;
				}
				if ( 'yes' === values.controls ) {
					attr.controls = true;
				}

				return attr;
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

					if ( '' !== values.video ) {
						// If its not within object already, add it.
						if ( 'undefined' === typeof FusionPageBuilderApp.mediaMap.videos[ values.video ] ) {
							FusionPageBuilderApp.mediaMap.videos[ values.video ] = true;
						}

					}
				}
			}
		} );
	} );
}( jQuery ) );

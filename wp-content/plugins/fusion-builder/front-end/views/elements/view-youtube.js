/* eslint no-useless-escape: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Vimeo Element View.
		FusionPageBuilder.fusion_youtube = FusionPageBuilder.ElementView.extend( {

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

				// Validate values.
				this.validateValues( atts.values, atts.params );

				// Create attribute objects
				attributes.attr       = this.buildAttr( atts.values );
				attributes.attrSrc    = this.buildAttrSrc( atts.values );

				// Any extras that need passed on.
				attributes.id              = atts.values.id;
				attributes.api_params      = atts.values.api_params;
				attributes.title_attribute = ! _.isEmpty( atts.values.title_attribute ) ? atts.values.title_attribute : 'YouTube video player ' + this.model.get( 'cid' );
				attributes.width           = atts.values.width;
				attributes.height          = atts.values.height;

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values, params ) {
				var regExp = /^.*(youtu\.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/,
					match;

				// Make videos 16:9 by default, values.width already set to params.width.
				if ( 'undefined' !== typeof params.width && '' !== params.width && ( 'undefined' === typeof params.height || '' === params.height ) ) {
					values.height = Math.round( params.width * 9 / 16 );
				}

				// values.height already set to params.height.
				if ( 'undefined' !== typeof params.height && '' !== params.height && ( 'undefined' === typeof params.width || '' === params.width ) ) {
					values.width = Math.round( params.height * 16 / 9 );
				}

				values.height = _.fusionValidateAttrValue( values.height, '' );
				values.width  = _.fusionValidateAttrValue( values.width, '' );

				// Make sure only the video ID is passed to the iFrame.
				match = values.id.match( regExp );
				if ( match && 11 === match[ 2 ].length ) {
					values.id = match[ 2 ];
				}

				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {

				// Attributes.
				var attrYoutube = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-video fusion-youtube',
					style: ''
				} );

				if ( 'yes' === values.center ) {
					attrYoutube[ 'class' ] += ' center-video';
				} else {
					attrYoutube.style += 'max-width:' + values.width + 'px;max-height:' + values.height + 'px;';
				}

				if ( '' !== values.alignment ) {
					attrYoutube[ 'class' ] += ' fusion-align' + values.alignment;
					attrYoutube.style += ' width:100%;';
				}

				if ( 'true' == values.autoplay || 'yes' === values.autoplay ) {
					attrYoutube[ 'data-autoplay' ] = '1';
				}

				if ( '' !== values.margin_top ) {
					attrYoutube.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_bottom ) {
					attrYoutube.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( values[ 'class' ] && '' !== values[ 'class' ] ) {
					attrYoutube[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.css_id ) {
					attrYoutube.id = values.css_id;
				}

				return attrYoutube;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttrSrc: function( values ) {
				var videoSCAttr = {
					class: 'video-shortcode'
				};

				if ( 'yes' === values.center ) {
					videoSCAttr.style = 'max-width:' + values.width + 'px;max-height:' + values.height + 'px;';
				}

				return videoSCAttr;
			}
		} );
	} );
}( jQuery ) );

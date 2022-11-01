/* globals fusionAllElements, FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion flickr.
		FusionPageBuilder.fusion_flickr = FusionPageBuilder.FormComponentView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};


				// Create attribute objects
				attributes.atts   = this.buildAttr( atts.values );

				// Any extras that need passed on.
				attributes.cid    = this.model.get( 'cid' );
				attributes.values = atts.values;
				attributes.columnStyles  = this.buildColumnStyles( atts );
				attributes.marginStyles  = this.buildMarginStyles( atts );
				attributes.aspectRatio  = this.buildAspectRatioStyles( atts.values );

				attributes.flickrItems  = FusionApp.previewWindow.fusionFlickrItems;
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
				var attr = {};
				attr[ 'class' ] = 'fusion-flickr-element flickr-' + this.model.get( 'cid' ) + ' ' + values[ 'class' ];

				attr  = _.fusionVisibilityAtts( values.hide_on_mobile, attr );


				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				if ( '' !== values.hover_type ) {
					attr[ 'class' ] += ' hover-' + values.hover_type;
				}

				attr.style = '';
				if ( '' !== values.columns ) {
					attr.style += '--flickr-grid-columns:' + values.columns + ';';
				}
				if ( '' !== values.columns_medium ) {
					attr.style += '--flickr-grid-md-columns:' + values.columns_medium + ';';
				}
				if ( '' !== values.columns_small ) {
					attr.style += '--flickr-grid-sm-columns:' + values.columns_small + ';';
				}

				if ( '' !== values.columns_spacing ) {
					attr.style += '--flickr-grid-gap:' + _.fusionGetValueWithUnit( values.columns_spacing ) + ';';
				}
				if ( '' !== values.columns_spacing_medium ) {
					attr.style += '--flickr-grid-md-gap:' + _.fusionGetValueWithUnit( values.columns_spacing_medium ) + ';';
				}
				if ( '' !== values.columns_spacing_small ) {
					attr.style += '--flickr-grid-sm-gap:' + _.fusionGetValueWithUnit( values.columns_spacing_small ) + ';';
				}

				if ( '' !== values.flickr_id ) {
					attr[ 'data-id' ] = values.flickr_id;
				}
				if ( '' !== values.type ) {
					attr[ 'data-type' ] = values.type;
				}
				if ( '' !== values.album_id ) {
					attr[ 'data-album_id' ] = values.album_id;
				}
				if ( '' !== values.count ) {
					attr[ 'data-count' ] = values.count;
				}
				if ( '' !== values.api_key ) {
					attr[ 'data-api_key' ] = values.api_key;
				}
				if ( '' !== values.link_type ) {
					attr[ 'data-link_type' ] = values.link_type;
				}
				if ( 'page' === values.link_type && '_blank' === values.link_target ) {
					attr[ 'data-link_target' ] = values.link_target;
				}

				//Animation
				attr = _.fusionAnimations( values, attr );

				return attr;
			},

			/**
			 * Builds margin styles.
			 *
			 * @since 3.5
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildMarginStyles: function( atts ) {
				var extras = jQuery.extend( true, {}, fusionAllElements.fusion_imageframe.extras ),
					elementSelector = '.fusion-flickr-element.flickr-' + this.model.get( 'cid' ),
					responsiveStyles = '';

				_.each( [ 'large', 'medium', 'small' ], function( size ) {
					var marginStyles = '',
						marginKey;

					_.each( [ 'top', 'right', 'bottom', 'left' ], function( direction ) {

						// Margin.
						marginKey = 'margin_' + direction + ( 'large' === size ? '' : '_' + size );
						if ( '' !== atts.values[ marginKey ] ) {
							marginStyles += 'margin-' + direction + ' : ' + _.fusionGetValueWithUnit( atts.values[ marginKey ] ) + ';';
						}

					} );

					if ( '' === marginStyles ) {
						return;
					}

					// Wrap CSS selectors
					if ( '' !== marginStyles ) {
						marginStyles = elementSelector + ' {' + marginStyles + '}';
					}

					// Large styles, no wrapping needed.
					if ( 'large' === size ) {
						responsiveStyles += marginStyles;
					} else {
						// Medium and Small size screen styles.
						responsiveStyles += '@media only screen and (max-width:' + extras[ 'visibility_' + size ] + 'px) {' + marginStyles + '}';
					}
				} );

				if ( '' !== responsiveStyles ) {
					responsiveStyles = '<style>' + responsiveStyles + '</style>';
				}

				return responsiveStyles;
			},

			/**
			 * Builds column styles.
			 *
			 * @since 3.5
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildColumnStyles: function( atts ) {
				var extras = jQuery.extend( true, {}, fusionAllElements.fusion_imageframe.extras ),
					elementSelector = '.fusion-flickr-element.flickr-' + this.model.get( 'cid' ),
					responsiveStyles = '';

				_.each( [ 'large', 'medium', 'small' ], function( size ) {
					var columns 		= ( 'large' === size ) ?  atts.values.columns :  atts.values[ 'columns_' + size ],
						columns_spacing = ( 'large' === size ) ?  atts.values.columns_spacing :  atts.values[ 'columns_spacing_' + size ],
						columns_style 	= '';

					if ( '' !== columns ) {
						columns_style += 'grid-template-columns: repeat(' + columns + ', 1fr);';
					}

					if ( '' !== columns_spacing ) {
						columns_style += 'grid-gap:' +  _.fusionGetValueWithUnit( columns_spacing ) + ';';
					}

					if ( '' !== columns_style ) {
						columns_style = elementSelector + '{' + columns_style + '}';
					}

					if ( 'large' === size ) {
						responsiveStyles += columns_style;
					} else {
						// Medium and Small size screen styles.
						responsiveStyles += '@media only screen and (max-width:' + extras[ 'visibility_' + size ] + 'px) {' + columns_style + '}';
					}
				} );

				if ( '' !== responsiveStyles ) {
					responsiveStyles = '<style>' + responsiveStyles + '</style>';
				}

				return responsiveStyles;
			},

			/**
			 * Builds aspect ratio styles.
			 *
			 * @since 7.6
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildAspectRatioStyles: function( values ) {
				var selectors, aspectRatio, width, height, padding;

				if ( '' ===  values.aspect_ratio ) {
					return '';
				}

				this.dynamic_css = {};
				this.baseSelector = '.fusion-flickr-element.flickr-' +  this.model.get( 'cid' ) + ' .flickr-image';
				selectors = [ this.baseSelector ];

				// Calc Ratio
				if ( 'custom' ===  values.aspect_ratio && '' !==  values.custom_aspect_ratio ) {
					this.addCssProperty( selectors, 'padding-top', values.custom_aspect_ratio + '%' );
				} else {
					aspectRatio = values.aspect_ratio.split( '-' );
					width 		= aspectRatio[ 0 ] || '';
					height 		= aspectRatio[ 1 ] || '';
					padding 	= '' !== width && '' !== height ?  ( height / width ) * 100 : '';

					this.addCssProperty( selectors, 'padding-top', padding + '%' );
				}

				//Ratio Position
				selectors = [ this.baseSelector + ' img' ];
				const x = '' !==  values.aspect_ratio_position_x ? values.aspect_ratio_position_x + '%' : '50%';
				const y = '' !==  values.aspect_ratio_position_y ? values.aspect_ratio_position_y + '%' : '50%';

				this.addCssProperty( selectors, 'object-position', x + ' ' + y );

				const css = this.parseCSS();

				return '<style>' + css + '</style>';
			},

			/**
			 * Things to do, places to go when options change.
			 *
			 * @since 2.0.0
			 * @param {string} paramName - The name of the parameter that changed.
			 * @param {mixed}  paramValue - The value of the option that changed.
			 * @param {Object} event - The event triggering the option change.
			 * @return {void}
			 */
			onOptionChange: function( paramName ) {
				if ( 'flickr_id' === paramName || 'count' === paramName ) {
					FusionApp.previewWindow.fusionFlickrItems = '';
				}
			}
		} );
	} );
}( jQuery ) );

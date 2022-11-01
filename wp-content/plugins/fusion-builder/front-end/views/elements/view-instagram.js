/* globals FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion instagram.
		FusionPageBuilder.fusion_instagram = FusionPageBuilder.FormComponentView.extend( {

			onRender: function() {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-element-render-fusion_instagram', this.$el.find( '.awb-instagram-element' ) );
			},

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
				attributes.atts   = this.buildAttr( atts );

				// Any extras that need passed on.
				attributes.cid    = this.model.get( 'cid' );
				attributes.values = atts.values;

				attributes.instagramItems  = FusionApp.previewWindow.fusionInstagramItems;
				return attributes;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( atts ) {
				var attr = {};
				const values = atts.values;

				attr[ 'class' ] = 'awb-instagram-element loading instagram-' + this.model.get( 'cid' ) + ' ' + values[ 'class' ];

				attr  = _.fusionVisibilityAtts( values.hide_on_mobile, attr );
				attr.style = '';

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				if ( '' !== values.layout ) {
					attr[ 'class' ] += ' layout-' + values.layout;
				}

				if ( '' !== values.hover_type ) {
					attr[ 'class' ] += ' hover-' + values.hover_type;
				}


				if ( '' !== values.limit ) {
					attr[ 'data-limit' ] = values.limit;
				}
				if ( '' !== values.counter ) {
					attr[ 'data-counter' ] = this.model.get( 'cid' );
				}
				if ( '' !== values.album_id ) {
					attr[ 'data-album_id' ] = values.album_id;
				}
				if ( 'lightbox' !== values.link_type ) {
					attr[ 'data-lightbox' ] = 'true';
				}
				if ( '' !== values.link_type ) {
					attr[ 'data-link_type' ] = values.link_type;
				}
				if ( 'page' === values.link_type && '_blank' === values.link_target ) {
					attr[ 'data-link_target' ] = values.link_target;
				}

				// Margin.
				attr.style += this.buildMarginStyles( atts );

				// Columns.
				attr.style += this.buildColumnStyles( atts );

				// Aspect ratio.
				attr.style += this.buildAspectRatioStyles( values );

				// Columns.
				attr.style += this.buildBorderStyles( values );

				// buttons.
				attr.style += this.buildButtonsStyles( values );

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
				var styles = '';

				_.each( [ 'large', 'medium', 'small' ], function( size ) {
					var marginStyles = '',
						device_abbr = '',
						marginKey;

						if ( 'small' === size ) {
							device_abbr = 'sm-';
						}

						if ( 'medium' === size ) {
							device_abbr = 'md-';
						}

					_.each( [ 'top', 'right', 'bottom', 'left' ], function( direction ) {

						// Margin.
						marginKey = 'margin_' + direction + ( 'large' === size ? '' : '_' + size );
						if ( '' !== atts.values[ marginKey ] ) {
							marginStyles += '--awb-margin-' + device_abbr + direction + ' : ' + _.fusionGetValueWithUnit( atts.values[ marginKey ] ) + ';';
						}

					} );

					if ( '' === marginStyles ) {
						return;
					}

					styles += marginStyles;
				} );

				return styles;
			},

			/**
			 * Builds column styles.
			 *
			 * @since 3.5
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildColumnStyles: function( atts ) {
				var styles = '';

				_.each( [ 'large', 'medium', 'small' ], function( size ) {
					var columns 		= ( 'large' === size ) ?  atts.values.columns :  atts.values[ 'columns_' + size ],
						columns_spacing = ( 'large' === size ) ?  atts.values.columns_spacing :  atts.values[ 'columns_spacing_' + size ],
						device_abbr = '';

					if ( 'small' === size ) {
						device_abbr = 'sm-';
					}

					if ( 'medium' === size ) {
						device_abbr = 'md-';
					}

					if ( '' !== columns ) {
						styles += '--awb-' + device_abbr + 'column-width:' + ( 100 / parseInt( columns ) ) + '%;';
					}

					if ( '' !== columns_spacing ) {
						styles += '--awb-' + device_abbr + 'column-space:' + columns_spacing + ';';
					}

				} );

				return styles;
			},

			/**
			 * Builds aspect ratio styles.
			 *
			 * @since 3.8
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildAspectRatioStyles: function( values ) {
				var style = '',
					aspectRatio,
					width,
					height;

				if ( '' ===  values.aspect_ratio ) {
					return '';
				}

				// Calc Ratio
				if ( 'custom' ===  values.aspect_ratio && '' !==  values.custom_aspect_ratio ) {
					style += '--awb-aspect-ratio: 100 / ' + values.custom_aspect_ratio + '%;';
				} else {
					aspectRatio = values.aspect_ratio.split( '-' );
					width 		= aspectRatio[ 0 ] || '';
					height 		= aspectRatio[ 1 ] || '';
					style += `--awb-aspect-ratio: ${width / height};`;
				}

				//Ratio Position
				if ( '' !== values.aspect_ratio_position ) {
					style += '--awb-object-position:' + values.aspect_ratio_position + ';';
				}

				return style;
			},

			/**
			 * Builds border styles.
			 *
			 * @since 3.8
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildBorderStyles: function( values ) {
				var style = '';

				if ( '' !== values.border_radius && '0' !== values.border_radius && '0px' !== values.border_radius && 0 !== values.border_radius ) {
					style += `--awb-bd-radius:${'round' === values.border_radius ? '50%' : _.fusionGetValueWithUnit( values.border_radius )};`;
				}
				if ( '' !== values.bordersize && '0' !== values.bordersize && '0px' !== values.bordersize && 0 !== values.bordersize ) {
					style += `--awb-bd-width:${_.fusionGetValueWithUnit( values.bordersize )};`;
				}
				if ( '' !== values.bordercolor ) {
					style += `--awb-bd-color:${values.bordercolor};`;
				}

				return style;
			},

			/**
			 * Builds buttons styles.
			 *
			 * @since 3.8
			 * @param {Object} values - The values object.
			 * @return {string}
			 */
			buildButtonsStyles: function( values ) {
				var style = '';

				if ( '' !== values.buttons_alignment ) {
					style += `--awb-buttons-alignment:${values.buttons_alignment};`;
				}

				if ( '' !== values.load_more_btn_color ) {
					style += `--awb-more-btn-color:${values.load_more_btn_color};`;
				}

				if ( '' !== values.load_more_btn_bg_color ) {
					style += `--awb-more-btn-bg:${values.load_more_btn_bg_color};`;
				}

				if ( '' !== values.load_more_btn_hover_color ) {
					style += `--awb-more-btn-hover-color:${values.load_more_btn_hover_color};`;
				}

				if ( '' !== values.load_more_btn_hover_bg_color ) {
					style += `--awb-more-btn-hover-bg:${values.load_more_btn_hover_bg_color};`;
				}

				if ( '' !== values.follow_btn_color ) {
					style += `--awb-follow-btn-color:${values.follow_btn_color};`;
				}

				if ( '' !== values.follow_btn_bg_color ) {
					style += `--awb-follow-btn-bg:${values.follow_btn_bg_color};`;
				}

				if ( '' !== values.follow_btn_hover_color ) {
					style += `--awb-follow-btn-hover-color:${values.follow_btn_hover_color};`;
				}

				if ( '' !== values.follow_btn_hover_bg_color ) {
					style += `--awb-follow-btn-hover-bg:${values.follow_btn_hover_bg_color};`;
				}

				return style;
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
				if ( 'limit' === paramName ) {
					FusionApp.previewWindow.fusionInstagramItems = '';
				}
			}
		} );
	} );
}( jQuery ) );

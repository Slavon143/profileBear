/* global fusionAllElements */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		FusionPageBuilder.fusion_tagcloud = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.5
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes  = {};

				this.values = atts.values;

				attributes.tagcloudAttr   = this.buildtagcloudAttr( atts.values );
				attributes.marginStyles   = this.buildMarginStyles( atts );
				attributes.styles         = this.getStyleTag( atts.values );
				attributes.tagCloudItems  = atts.query_data;


				return attributes;
			},

			/**
			 * Build the tagcloud element attributes.
			 *
			 * @since 3.5
			 * @param {Object} values
			 * @return {Object}
			 */
			buildtagcloudAttr: function( values ) {
				var attr = {
					'class': 'fusion-tagcloud-element fusion-tagcloud-cid-' + this.model.get( 'cid' )
				};
				attr  = _.fusionVisibilityAtts( values.hide_on_mobile, attr );
				if ( '' !== values.id ) {
					attr.id = values.id;
				}
				if ( '' !== values.style ) {
					attr[ 'class' ] += ' style-' + values.style;
				}

				if ( 'variable' === values.font_size_type ) {
					attr[ 'class' ] += ' variable-font-size';
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
					elementSelector = '.fusion-tagcloud-cid-' + this.model.get( 'cid' ),
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
			 * Get style element.
			 *
			 * @since 3.5
			 * @param {object} values
			 * @param {object} extras
			 * @return string
			 */
			getStyleTag: function( values ) {
				var selectors;

				this.dynamic_css = {};
				this.baseSelector = '.fusion-tagcloud-cid-' +  this.model.attributes.cid;

				selectors = [ this.baseSelector ];

				if ( '' !==  values.alignment ) {
					this.addCssProperty( selectors, 'justify-content',  values.alignment, true );
				}

				if ( '' !==  values.tags_spacing ) {
					this.addCssProperty( selectors, 'gap',  _.fusionGetValueWithUnit( values.tags_spacing ), true );
				}

				selectors = [ this.baseSelector + ' a.tag-cloud-link' ];
				if ( '' !==  values.font_size && 'variable' !==  values.font_size_type ) {
					this.addCssProperty( selectors, 'font-size',  _.fusionGetValueWithUnit( values.font_size ), true );
				}

				if ( '' !==  values.letter_spacing ) {
					this.addCssProperty( selectors, 'letter-spacing',  _.fusionGetValueWithUnit( values.letter_spacing ), true );
				}

			//padding
			if ( 'arrows' !== values.style ) {
				if ( '' !==  values.padding_top ) {
					this.addCssProperty( selectors, 'padding-top',  _.fusionGetValueWithUnit( values.padding_top ), true );
				}

				if ( '' !==  values.padding_right ) {
					this.addCssProperty( selectors, 'padding-right',  _.fusionGetValueWithUnit( values.padding_right ), true );
				}

				if ( '' !==  values.padding_bottom ) {
					this.addCssProperty( selectors, 'padding-bottom',  _.fusionGetValueWithUnit( values.padding_bottom ), true );
				}

				if ( '' !==  values.padding_left ) {
					this.addCssProperty( selectors, 'padding-left',  _.fusionGetValueWithUnit( values.padding_left ), true );
				}
			}

			//borders
			if ( 'arrows' !== values.style ) {
				if ( '' !==  values.border_top ) {
					this.addCssProperty( selectors, 'border-top-width',  _.fusionGetValueWithUnit( values.border_top ), true );
				}

				if ( '' !==  values.border_right ) {
					this.addCssProperty( selectors, 'border-right-width',  _.fusionGetValueWithUnit( values.border_right ), true );
				}

				if ( '' !==  values.border_bottom ) {
					this.addCssProperty( selectors, 'border-bottom-width',  _.fusionGetValueWithUnit( values.border_bottom ), true );
				}

				if ( '' !==  values.border_left ) {
					this.addCssProperty( selectors, 'border-left-width',  _.fusionGetValueWithUnit( values.border_left ), true );
				}

				if ( '' !==  values.border_radius_top_left ) {
					this.addCssProperty( selectors, 'border-top-left-radius',  _.fusionGetValueWithUnit( values.border_radius_top_left ), true );
				}

				if ( '' !==  values.border_radius_top_right ) {
					this.addCssProperty( selectors, 'border-top-right-radius',  _.fusionGetValueWithUnit( values.border_radius_top_right ), true );
				}

				if ( '' !==  values.border_radius_bottom_left ) {
					this.addCssProperty( selectors, 'border-bottom-left-radius',  _.fusionGetValueWithUnit( values.border_radius_bottom_left ), true );
				}

				if ( '' !==  values.border_radius_bottom_right ) {
					this.addCssProperty( selectors, 'border-bottom-right-radius',  _.fusionGetValueWithUnit( values.border_radius_bottom_right ), true );
				}
			}
			if ( 'arrows' === values.style ) {
				if ( '' !==  values.arrows_border_radius_top_right ) {
					this.addCssProperty( selectors, 'border-top-right-radius',  _.fusionGetValueWithUnit( values.arrows_border_radius_top_right ), true );
				}

				if ( '' !==  values.arrows_border_radius_bottom_right ) {
					this.addCssProperty( selectors, 'border-bottom-right-radius',  _.fusionGetValueWithUnit( values.arrows_border_radius_bottom_right ), true );
				}
			}

			//colors
			const randomColors = '' !== values.random_colors ? values.random_colors : '';
			if ( '' !==  values.background_color && !randomColors.includes( 'background' ) ) {
				this.addCssProperty( selectors, '--tag-color',  values.background_color, true );
			}

			if ( '' !==  values.text_color && !randomColors.includes( 'text' ) ) {
				this.addCssProperty( selectors, '--tag-text-color',  values.text_color, true );
			}

			if ( '' !==  values.border_color && 'arrows' !== values.style && !randomColors.includes( 'background' ) ) {
				this.addCssProperty( selectors, 'border-color',  values.border_color, true );
			}

			selectors = [ this.baseSelector + ' a.tag-cloud-link:hover' ];

			if ( '' !==  values.background_hover_color && !randomColors.includes( 'background' ) ) {
				this.addCssProperty( selectors, '--tag-color-hover',  values.background_hover_color, true );
			}

			if ( '' !==  values.text_hover_color && !randomColors.includes( 'text' ) ) {
				this.addCssProperty( selectors, '--tag-text-color-hover',  values.text_hover_color, true );
			}
			if ( '' !==  values.border_hover_color && 'arrows' !== values.style && !randomColors.includes( 'background' ) ) {
				this.addCssProperty( selectors, 'border-color',  values.border_hover_color, true );
			}

			// padding for arrows style.
			if ( 'arrows' === values.style ) {
				selectors = [ this.baseSelector + '.style-arrows a.tag-cloud-link .text' ];

				if ( '' !==  values.padding_top ) {
					this.addCssProperty( selectors, 'padding-top',  _.fusionGetValueWithUnit( values.padding_top ), true );
				}
				if ( '' !==  values.padding_right ) {
					this.addCssProperty( selectors, 'padding-right',  _.fusionGetValueWithUnit( values.padding_right ), true );
				}
				if ( '' !==  values.padding_bottom ) {
					this.addCssProperty( selectors, 'padding-bottom',  _.fusionGetValueWithUnit( values.padding_bottom ), true );
				}
				if ( '' !==  values.padding_left ) {
					this.addCssProperty( selectors, 'padding-left',  _.fusionGetValueWithUnit( values.padding_left ), true );
				}

				if ( '' !== values.padding_top || '' !== values.padding_bottom ) {
					selectors = [ this.baseSelector + '.style-arrows a.tag-cloud-link' ];

					let tags_height = 'calc(2.4em'; // 2.4em the default height from the css file.
					if ( '' !== values.padding_top ) {
						tags_height += ' + ' + _.fusionGetValueWithUnit( values.padding_top );
					}
					if ( '' !== values.padding_bottom ) {
						tags_height += ' + ' + _.fusionGetValueWithUnit( values.padding_bottom );
					}
					tags_height += ')';
					this.addCssProperty( selectors, 'height',  tags_height, true );
				}
			}

			const style = this.parseCSS();

			return style ? '<style>' + style + '</style>' : '';

			},

			/**
			 * Init.
			 *
			 * @since 3.5
			 * @return {void}
			 */
			onInit: function() {
				// Also refresh on init, since the onPageLoad event don't trigger sometimes.
				var previewBody = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' );
				previewBody.trigger( 'fusion-element-render-fusion_tagcloud', this.model.attributes.cid );
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 3.5
			 * @return {void}
			 */
			afterPatch: function() {
				var previewBody = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' );
				previewBody.trigger( 'fusion-element-render-fusion_tagcloud', this.model.attributes.cid );
			}

		} );
	} );
}( jQuery ) );

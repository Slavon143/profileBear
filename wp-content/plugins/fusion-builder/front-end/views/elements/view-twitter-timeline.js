/* global fusionAllElements, FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion twitter timeline.
		FusionPageBuilder.fusion_twitter_timeline = FusionPageBuilder.FormComponentView.extend( {

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
				attributes.iframeAtts   = this.buildIframeAttr( atts.values );

				// Any extras that need passed on.
				attributes.values = atts.values;
				attributes.styles  = this.buildStyles( atts );


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
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-twitter-timeline fusion-twitter-timeline-' + this.model.get( 'cid' ) + ' ' + values[ 'class' ]
				} );

				if ( '' !== values.id ) {
					attr.id = values.id;
				}


				//Animation
				attr = _.fusionAnimations( values, attr );

				return attr;
			},

			/**
			 * Builds Iframe attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildIframeAttr: function( values ) {
				var attr         = {};

				attr[ 'class' ] = 'twitter-timeline';

				attr.href = 'https://twitter.com/' + values.username;

				if ( '' !== values.language ) {
					attr[ 'data-lang' ] = values.language;
				}

				if ( '' !== values.width ) {
					attr[ 'data-width' ] = values.width;
				}

				if ( '' !== values.height ) {
					attr[ 'data-height' ] = values.height;
				}

				if ( '' !== values.theme ) {
					attr[ 'data-theme' ] = values.theme;
				}

				if ( 'hide' !== values.borders && '' !== values.border_color ) {
					attr[ 'data-border-color' ] = values.border_color;
				}

				let chrome = '';
				if ( 'hide' === values.header ) {
					chrome += ' noheader';
				}
				if ( 'hide' === values.footer ) {
					chrome += ' nofooter';
				}
				if ( 'hide' === values.borders ) {
					chrome += ' noborders';
				}
				if ( 'hide' === values.scrollbar ) {
					chrome += ' noscrollbar';
				}
				if ( 'yes' === values.transparent ) {
					chrome += ' transparent';
				}

				if ( '' !== chrome ) {
					attr[ 'data-chrome' ] = chrome;
				}
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
					elementSelector = '.fusion-twitter-timeline-' + this.model.get( 'cid' ),
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


				return responsiveStyles;
			},

			/**
			 * Builds styles.
			 *
			 * @since 3.5
			 * @param {Object} atts - The atts object
			 * @return {string}
			 */
			buildStyles: function( atts ) {
				var selectors;
				var style;
				var values = atts.values;
				this.dynamic_css = {};
				this.baseSelector = '.fusion-twitter-timeline-' + this.model.get( 'cid' );

				selectors = [ this.baseSelector ];

				if ( '' !==  values.alignment ) {
					this.addCssProperty( selectors, 'display',  'flex' );
					this.addCssProperty( selectors, 'justify-content',  values.alignment );
				}

				style = this.parseCSS();
				style += this.buildMarginStyles( atts );

				return style ? '<style>' + style + '</style>' : '';
			},

			/**
			 * Triggers a refresh.
			 *
			 * @since 2.0.0
			 * @return void
			 */
			refreshJs: function() {
				if ( 'undefined' !== typeof FusionApp.previewWindow.twttr ) {
					FusionApp.previewWindow.twttr.widgets.load();
				}
			},
			onInit: function() {
				this._refreshJs();
			},
			onRender: function() {
				this._refreshJs();
			},
			afterPatch: function() {
				this._refreshJs();
			}
		} );
	} );
}( jQuery ) );

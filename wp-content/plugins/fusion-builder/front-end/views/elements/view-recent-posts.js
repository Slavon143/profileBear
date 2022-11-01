var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Accordion View.
		FusionPageBuilder.fusion_recent_posts = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				var self = this;

				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( document ).ready( function() {
					self.afterPatch();
				} );
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforePatch: function() {
				var elements = this.$el.find( '.fusion-recent-posts .flexslider' );

				_.each( elements, function( element ) {
					element = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( element );

					if ( 'undefined' !== typeof element.data( 'flexslider' ) ) {
						element.flexslider( 'destroy' );
					}
				} );
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var elements = this.$el.find( '.fusion-recent-posts .flexslider' );

				// Re-init flexsliders.
				setTimeout( function() {
					_.each( elements, function( element ) {
						element = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( element );

						// TODO: check timing here, after patch shouldn't really fire on initial load, otherwise duplicate flexislider.
						if ( 'function' === typeof element.flexslider ) {
							element.flexslider();
						}
					} );
				}, 300 );

			},

			/**
			 * Modifies the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {

				// Deprecated 5.2.1 hide value, mapped to no.
				values.excerpt = 'hide' === values.excerpt ? 'no' : values.excerpt;

				values.margin_bottom    = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left      = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right     = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top       = _.fusionValidateAttrValue( values.margin_top, 'px' );
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0.0
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var columns                       = 3,
					attributes                    = {},
					recentPostsShortcode          = {},
					recentPostsShortcodeColumn    = {},
					recentPostsShortcodeImgLink   = {},
					recentPostsShortcodeSection   = {},
					recentPostsShortcodeSlideshow = {
						class: 'fusion-flexslider flexslider'
					};

				this.validateValues( atts.values );

				if ( 'undefined' !== typeof this.model.attributes.query_data && 'undefined' !== typeof this.model.attributes.query_data.posts ) {
					if ( '' !== atts.values.columns ) {
						columns = 12 / parseInt( atts.values.columns, 10 );
					}

					recentPostsShortcodeColumn[ 'class' ] = 'fusion-column column col col-lg-' + columns + ' col-md-' + columns + ' col-sm-' + columns + '';

					if ( '5' === atts.values.columns ) {
						recentPostsShortcodeColumn[ 'class' ] = 'fusion-column column col-lg-2 col-md-2 col-sm-2';
					}

					recentPostsShortcodeColumn = _.fusionAnimations( atts.values, recentPostsShortcodeColumn );

					if ( 'thumbnails-on-side' === atts.values.layout ) {
						recentPostsShortcodeSlideshow[ 'class' ] += ' floated-slideshow';
					}

					if ( '' !== atts.values.hover_type ) {
						recentPostsShortcodeSlideshow[ 'class' ] += ' flexslider-hover-type-' + atts.values.hover_type;
					}

					if ( '' !== atts.values.hover_type ) {
						recentPostsShortcodeImgLink[ 'class' ] = 'hover-type-' + atts.values.hover_type;
					}

					// recentPostsShortcode Attributes.
					recentPostsShortcode = _.fusionVisibilityAtts( atts.values.hide_on_mobile, {
						class: 'fusion-recent-posts fusion-recent-posts-' + this.model.get( 'cid' ) + ' avada-container layout-' + atts.values.layout + ' layout-columns-' + atts.values.columns,
						style: ''
					} );

					if ( '' !== atts.values.margin_top ) {
						recentPostsShortcode.style += 'margin-top:' + atts.values.margin_top + ';';
					}

					if ( '' !== atts.values.margin_right ) {
						recentPostsShortcode.style += 'margin-right:' + atts.values.margin_right + ';';
					}

					if ( '' !== atts.values.margin_bottom ) {
						recentPostsShortcode.style += 'margin-bottom:' + atts.values.margin_bottom + ';';
					}

					if ( '' !== atts.values.margin_left ) {
						recentPostsShortcode.style += 'margin-left:' + atts.values.margin_left + ';';
					}

					if ( '' !== atts.values[ 'class' ] ) {
						recentPostsShortcode[ 'class' ] += ' ' + atts.values[ 'class' ];
					}

					if ( '' !== atts.values.id ) {
						recentPostsShortcode.id = atts.values.id;
					}

					// recentPostsShortcodeSection Attributes.
					recentPostsShortcodeSection[ 'class' ] = 'fusion-columns columns fusion-columns-' + atts.values.columns + ' columns-' + atts.values.columns;
				}

				if ( 'auto' === atts.values.picture_size ) {
					atts.values.image_size = 'full';
				} else if ( 'default' === atts.values.layout ) {
					atts.values.image_size = 'recent-posts';
				} else {
					atts.values.image_size = 'portfolio-five';
				}

				attributes.metaInfoSettings = {};
				attributes.metaInfoSettings.post_meta                       = ( 'yes' === atts.values.meta );
				attributes.metaInfoSettings.post_meta_author                = ( 'yes' === atts.values.meta_author );
				attributes.metaInfoSettings.post_meta_date                  = ( 'yes' === atts.values.meta_date );
				attributes.metaInfoSettings.post_meta_cats                  = ( 'yes' === atts.values.meta_categories );
				attributes.metaInfoSettings.post_meta_tags                  = ( 'yes' === atts.values.meta_tags );
				attributes.metaInfoSettings.post_meta_comments              = ( 'yes' === atts.values.meta_comments );
				attributes.metaInfoSettings.disable_date_rich_snippet_pages = atts.extras.disable_date_rich_snippet_pages;

				attributes.query_data                    = atts.query_data;
				attributes.extras                        = atts.extras;
				attributes.values                        = atts.values;
				attributes.style                         = this.getStyleElement( atts.values );
				attributes.titleTag                      = this.getTitleTag( atts.values );
				attributes.recentPostsShortcode          = recentPostsShortcode;
				attributes.recentPostsShortcodeColumn    = recentPostsShortcodeColumn;
				attributes.recentPostsShortcodeImgLink   = recentPostsShortcodeImgLink;
				attributes.recentPostsShortcodeSection   = recentPostsShortcodeSection;
				attributes.recentPostsShortcodeSlideshow = recentPostsShortcodeSlideshow;

				return attributes;
			},

			getTitleTag: function( values ) {
				if ( ! values.title_size ) {
					return 'h4';
				}

				if ( !isNaN( values.title_size ) && !isNaN( parseFloat( values.title_size ) ) ) {
					return 'h' + values.title_size;
				}

				return values.title_size;
			},

			/**
			 * Create the style HTML element.
			 *
			 * @since 3.5
			 * @param {Object} values - The values.
			 * @returns {string}
			 */
			getStyleElement: function( values ) {
				var style,
					baseSelector = '.fusion-recent-posts.fusion-recent-posts-' + this.model.get( 'cid' ),
					self = this,
					titleSelector = baseSelector + ' .columns .column .entry-title';

				this.dynamic_css = {};
				this.values = values;

				jQuery.each( _.fusionGetFontStyle( 'title_font', values, 'object' ), function( rule, value ) {
					self.addCssProperty( titleSelector, rule, value );
				} );

				if ( ! _.isEmpty( values.title_font_size ) ) {
					this.addCssProperty( titleSelector, 'font-size', values.title_font_size );
				}

				if ( ! _.isEmpty( values.title_line_height ) ) {
					this.addCssProperty( titleSelector, 'line-height', values.title_line_height );
				}

				if ( ! _.isEmpty( values.title_letter_spacing ) ) {
					this.addCssProperty( titleSelector, 'letter-spacing', values.title_letter_spacing );
				}

				if ( ! _.isEmpty( values.title_text_transform ) ) {
					this.addCssProperty( titleSelector, 'text-transform', values.title_text_transform );
				}

				style = this.parseCSS();

				return style ? '<style>' + style + '</style>' : '';
			}
		} );
	} );
}( jQuery ) );

/* global fusionBuilderText, FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Related Component View.
		FusionPageBuilder.fusion_tb_related = FusionPageBuilder.ElementView.extend( {

			onInit: function() {
				if ( this.model.attributes.markup && '' === this.model.attributes.markup.output ) {
					this.model.attributes.markup.output = this.getComponentPlaceholder();
				}
			},

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
				this.validateValues( atts.values );

				// Create attribute objects.
				attributes.attr         = this.buildAttr( atts.values );
				attributes.titleElement = 'yes' === atts.values.heading_enable ? _.buildTitleElement( atts.values, atts.extras, this.getSectionTitle() ) : '';
				attributes.query_data   = atts.query_data;

				// add placeholder.
				attributes.query_data.placeholder = this.getComponentPlaceholder();

				if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.related_items ) {
					attributes.relatedCarousel = this.buildRelatedCarousel( atts );
					attributes.carouselAttrs   = this.buildCarouselAttrs( atts.values );
					attributes.carouselNav     = true === atts.values.related_posts_navigation ? this.buildCarouselNav() : '';
				}

				return attributes;
			},

			/**
			 * Modify values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {void}
			 */
			validateValues: function( values ) {

				if ( 'undefined' !== typeof values.margin_top && '' !== values.margin_top ) {
					values.margin_top = _.fusionGetValueWithUnit( values.margin_top );
				}

				if ( 'undefined' !== typeof values.margin_right && '' !== values.margin_right ) {
					values.margin_right = _.fusionGetValueWithUnit( values.margin_right );
				}

				if ( 'undefined' !== typeof values.margin_bottom && '' !== values.margin_bottom ) {
					values.margin_bottom = _.fusionGetValueWithUnit( values.margin_bottom );
				}

				if ( 'undefined' !== typeof values.margin_left && '' !== values.margin_left ) {
					values.margin_left = _.fusionGetValueWithUnit( values.margin_left );
				}

				values.related_posts_navigation = ( 'yes' === values.related_posts_navigation || '1' === values.related_posts_navigation ) ? true : false;
				values.related_posts_autoplay   = ( 'yes' === values.related_posts_autoplay || '1' === values.related_posts_autoplay ) ? true : false;
				values.related_posts_swipe      = ( 'yes' === values.related_posts_swipe || '1' === values.related_posts_swipe ) ? true : false;
			},

			/**
			 * Builds related posts carousel.
			 *
			 * @since 2.0
			 * @param {Object} atts - The Attributes.
			 * @return {string}
			 */
			buildRelatedCarousel: function( atts ) {
				var queryData = atts.query_data,
					values    = atts.values,
					html      = '';

				_.each( queryData.related_items, function( item ) {
					var carouselItemCss = '';

					if ( queryData.related_items.length < values.related_posts_columns ) {
						carouselItemCss = ( atts.extras.content_width - parseInt( values.related_posts_column_spacing ) * ( values.related_posts_columns - 1 ) ) / values.related_posts_columns; // eslint-disable-line no-mixed-operators
						carouselItemCss = ' style="max-width: ' + carouselItemCss + 'px;"';
					}

					html += '<li class="fusion-carousel-item"' + carouselItemCss + '>';
					html += '<div class="fusion-carousel-item-wrapper">';

					html += item.featured_image;

					if ( 'title_below_image' === values.related_posts_layout ) {
						html += '<h4 class="fusion-carousel-title">';
						html += '<a class="fusion-related-posts-title-link" href="' + item.link + '" target="_self" title="' + item.title_attr + '">' + item.title + '</a>';
						html += '</h4>';

						html += '<div class="fusion-carousel-meta">';
						html += '<span class="fusion-date">' + item.date + '</span>';

						if ( true === item.comments_open ) {
							html += '<span class="fusion-inline-sep">|</span>';
							html += '<span>';
							html += item.comments;
							html += '</span>';
						}

						html += '</div>';
					}

					html += '</div>';
					html += '</li>';
				} );

				return html;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr      = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'related-posts single-related-posts fusion-related-tb',
						style: ''
					} ),
					cid = this.model.get( 'cid' );

				attr = _.fusionAnimations( values, attr );

				if ( values.margin_top ) {
					attr.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( values.margin_right ) {
					attr.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( values.margin_bottom ) {
					attr.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( values.margin_left ) {
					attr.style += 'margin-left:' + values.margin_left + ';';
				}

				attr[ 'class' ] += ' fusion-related-tb-' + cid;

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				return attr;
			},

			/**
			 * Builds carousel nav.
			 *
			 * @since 2.2
			 * @return {string}
			 */
			buildCarouselNav: function() {
				var output = '';

				output += '<div class="fusion-carousel-nav">';
				output += '<button class="fusion-nav-prev" aria-label="Previous"></button>';
				output += '<button class="fusion-nav-next" aria-label="Next"></button>';
				output += '</div>';

				return output;
			},

			/**
			 * Builds carousel attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildCarouselAttrs: function( values ) {
				var attr = {
					class: 'fusion-carousel'
				};

				if ( 'title_below_image' === values.related_posts_layout ) {
					attr[ 'class' ] += ' fusion-carousel-title-below-image';
				}

				attr[ 'data-imagesize' ] = ( 'cropped' === values.related_posts_image_size ) ? 'fixed' : 'auto';

				/**
				 * Set the meta content variable.
				 */
				attr[ 'data-metacontent' ] = ( 'title_on_rollover' === values.related_posts_layout ) ? 'no' : 'yes';

				/**
				 * Set the autoplay variable.
				 */
				attr[ 'data-autoplay' ] = ( values.related_posts_autoplay ) ? 'yes' : 'no';

				/**
				 * Set the touch scroll variable.
				 */
				attr[ 'data-touchscroll' ] = ( values.related_posts_swipe ) ? 'yes' : 'no';

				attr[ 'data-columns' ]     = values.related_posts_columns;
				attr[ 'data-itemmargin' ]  = parseInt( values.related_posts_column_spacing ) + 'px';
				attr[ 'data-itemwidth' ]   = 180;
				attr[ 'data-touchscroll' ] = 'yes';

				attr[ 'data-scrollitems' ] = ( 0 == values.related_posts_swipe_items ) ? '' : values.related_posts_swipe_items;

				return attr;
			},

			/**
			 * Get section title based on the post type.
			 *
			 * @since 2.2
			 * @return {string}
			 */
			getSectionTitle: function() {
				var sectionTitle = fusionBuilderText.related_posts;

				if ( 'undefined' !== typeof FusionApp.data.examplePostDetails ) {

					if ( 'avada_portfolio' === FusionApp.data.examplePostDetails.post_type ) {
						sectionTitle = fusionBuilderText.related_projects;
					} else if ( 'avada_faq' === FusionApp.data.examplePostDetails.post_type ) {
						sectionTitle = fusionBuilderText.related_faqs;
					}
				}

				return sectionTitle;
			}

		} );
	} );
}( jQuery ) );

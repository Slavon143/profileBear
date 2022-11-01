var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Woo Product Slider View.
		FusionPageBuilder.fusion_products_slider = FusionPageBuilder.ElementView.extend( {

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

				attributes.wooProductSliderShortcode         = {};
				attributes.wooProductSliderShortcodeCarousel = {};
				attributes.productList                       = false;
				attributes.placeholder                       = false;
				attributes.showNav                           = atts.values.show_nav;

				// Validate values.
				this.validateValues( atts.values );

				// Create attribute objects.
				if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.products ) {
					attributes.wooProductSliderShortcode         = this.buildWooProductSliderShortcodeAttr( atts.values );
					attributes.wooProductSliderShortcodeCarousel = this.buildWooProductSliderShortcodeCarousel( atts.values );
					attributes.productList                       = this.buildProductList( atts.values, atts.extras, atts.query_data );
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.placeholder ) {
					attributes.placeholder = atts.query_data.placeholder;
				}

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.column_spacing = _.fusionValidateAttrValue( values.column_spacing, '' );

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
			buildWooProductSliderShortcodeAttr: function( values ) {
				var wooProductSliderShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-woo-product-slider fusion-woo-slider',
					style: ''
				} );

				if ( '' !== values.margin_top ) {
					wooProductSliderShortcode.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_right ) {
					wooProductSliderShortcode.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( '' !== values.margin_bottom ) {
					wooProductSliderShortcode.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( '' !== values.margin_left ) {
					wooProductSliderShortcode.style += 'margin-left:' + values.margin_left + ';';
				}

				if ( '' !== values[ 'class' ] ) {
					wooProductSliderShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					wooProductSliderShortcode.id = values.id;
				}

				return wooProductSliderShortcode;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildWooProductSliderShortcodeCarousel: function( values ) {
				var wooProductSliderShortcodeCarousel = {
					class: 'fusion-carousel'
				};

				if ( 'title_below_image' === values.carousel_layout ) {
					wooProductSliderShortcodeCarousel[ 'class' ] += ' fusion-carousel-title-below-image';
					wooProductSliderShortcodeCarousel[ 'data-metacontent' ] = 'yes';
				} else {
					wooProductSliderShortcodeCarousel[ 'class' ] += ' fusion-carousel-title-on-rollover';
				}

				wooProductSliderShortcodeCarousel[ 'data-autoplay' ]    = values.autoplay;
				wooProductSliderShortcodeCarousel[ 'data-columns' ]     = values.columns;
				wooProductSliderShortcodeCarousel[ 'data-itemmargin' ]  = values.column_spacing;
				wooProductSliderShortcodeCarousel[ 'data-itemwidth' ]   = 180;
				wooProductSliderShortcodeCarousel[ 'data-touchscroll' ] = values.mouse_scroll;
				wooProductSliderShortcodeCarousel[ 'data-imagesize' ]   = values.picture_size;
				wooProductSliderShortcodeCarousel[ 'data-scrollitems' ] = values.scroll_items;

				return wooProductSliderShortcodeCarousel;
			},

			/**
			 * Builds the product list and returns the HTML.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @param {Object} extras - Extra args.
			 * @param {Object} queryData - The query data.
			 * @return {string}
			 */
			buildProductList: function( values, extras, queryData ) {
				var productList       = '',
					designClass       = 'fusion-' + extras.box_design + '-product-image-wrapper',
					featuredImageSize = 'full',
					showCats,
					showPrice,
					showButtons;

				( 'yes' === values.show_cats ) ? ( showCats = 'enable' ) : ( showCats = 'disable' );
				( 'yes' === values.show_price ) ? ( showPrice = true ) : ( showPrice = false );
				( 'yes' === values.show_buttons ) ? ( showButtons = true ) : ( showButtons = false );

				if ( 'fixed' === values.picture_size ) {
					featuredImageSize = 'portfolio-five';
				}

				_.each( queryData.products, function( product ) {
					var inCart    = jQuery.inArray( product.id, queryData.items_in_cart ),
						image     = '',
						imageData = product.image_data;

					imageData.image_size       = featuredImageSize;
					imageData.display_woo_sale = 'yes' === values.show_sale;

					// Title on rollover layout.
					if ( 'title_on_rollover' === values.carousel_layout ) {
						imageData.image_size              = featuredImageSize;
						imageData.display_woo_price       = showPrice;
						imageData.display_woo_buttons     = showButtons;
						imageData.display_post_categories = showCats;
						imageData.display_post_title      = 'enable';
						imageData.display_rollover        = 'yes';

						image = _.fusionFeaturedImage( imageData );

						// Title below image layout.
					} else {
						imageData.image_size              = featuredImageSize;
						imageData.display_woo_price       = false;
						imageData.display_woo_buttons     = showButtons;
						imageData.display_post_categories = 'disable';
						imageData.display_post_title      = 'disable';
						imageData.display_rollover        = 'yes';

						if ( 'yes' === values.show_buttons ) {
							image = _.fusionFeaturedImage( imageData );
						} else {
							imageData.display_rollover = 'no';
							image = _.fusionFeaturedImage( imageData );
						}

						// Get the post title.
						image += '<h4 class="fusion-carousel-title">';
						image += '<a href="' + product.permalink + '" target="_self">' + product.title + '</a>';
						image += '</h4>';
						image += '<div class="fusion-carousel-meta">';

						// Get the terms.
						if ( true === showCats || 'enable' === showCats ) {
							image += product.terms;
						}

						// Check if we should render the woo product price.
						if ( true === showPrice || 'enable' === showPrice ) {
							image += '<div class="fusion-carousel-price">' + product.price + '</div>';
						}

						image += '</div>';
					}

					if ( -1 !== inCart ) {
						productList += '<li class="fusion-carousel-item"><div class="' + designClass + ' fusion-item-in-cart"><div class="fusion-carousel-item-wrapper">' + image + '</div></div></li>';
					} else {
						productList += '<li class="fusion-carousel-item"><div class="' + designClass + '"><div class="fusion-carousel-item-wrapper">' + image + '</div></div></li>';
					}
				} );

				return productList;
			}

		} );
	} );
}( jQuery ) );

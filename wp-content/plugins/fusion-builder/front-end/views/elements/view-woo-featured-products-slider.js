var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Woo Featured Product Slider View.
		FusionPageBuilder.fusion_featured_products_slider = FusionPageBuilder.ElementView.extend( {

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
				attributes.wooFeaturedProductsSliderShortcode         = {};
				attributes.wooFeaturedProductsSliderShortcodeCarousel = {};
				attributes.product_list                               = false;
				attributes.placeholder                                = false;

				if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.products ) {
					attributes.wooFeaturedProductsSliderShortcode         = this.buildWooFeaturedProductsSliderShortcodeAttr( atts.values );
					attributes.wooFeaturedProductsSliderShortcodeCarousel = this.buildWooFeaturedProductsSliderShortcodeCarousel( atts.values );
					attributes.product_list                               = this.buildProductList( atts.values, atts.extras, atts.query_data );
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.placeholder ) {
					attributes.placeholder = atts.query_data.placeholder;
				}

				// Any extras that need passed on.
				attributes.show_nav   = atts.values.show_nav;

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
			 * Builds main attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildWooFeaturedProductsSliderShortcodeAttr: function( values ) {

				// WooFeaturedProductsSliderShortcode attributes.
				var wooFeaturedProductsSliderShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-woo-featured-products-slider fusion-woo-slider',
					style: ''
				} );

				if ( '' !== values.margin_top ) {
					wooFeaturedProductsSliderShortcode.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_right ) {
					wooFeaturedProductsSliderShortcode.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( '' !== values.margin_bottom ) {
					wooFeaturedProductsSliderShortcode.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( '' !== values.margin_left ) {
					wooFeaturedProductsSliderShortcode.style += 'margin-left:' + values.margin_left + ';';
				}

				if ( '' !== values[ 'class' ] ) {
					wooFeaturedProductsSliderShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					wooFeaturedProductsSliderShortcode.id = values.id;
				}

				return wooFeaturedProductsSliderShortcode;
			},

			/**
			 * Builds carousel attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildWooFeaturedProductsSliderShortcodeCarousel: function( values ) {

				// WooFeaturedProductsSliderShortcodeCarousel Attributes.
				var wooFeaturedProductsSliderShortcodeCarousel = {
					class: 'fusion-carousel'
				};

				if ( 'title_below_image' === values.carousel_layout ) {
					wooFeaturedProductsSliderShortcodeCarousel[ 'class' ] += ' fusion-carousel-title-below-image';
					wooFeaturedProductsSliderShortcodeCarousel[ 'data-metacontent' ] = 'yes';
				} else {
					wooFeaturedProductsSliderShortcodeCarousel[ 'class' ] += ' fusion-carousel-title-on-rollover';
				}

				wooFeaturedProductsSliderShortcodeCarousel[ 'data-autoplay' ]    = values.autoplay;
				wooFeaturedProductsSliderShortcodeCarousel[ 'data-columns' ]     = values.columns;
				wooFeaturedProductsSliderShortcodeCarousel[ 'data-itemmargin' ]  = values.column_spacing;
				wooFeaturedProductsSliderShortcodeCarousel[ 'data-itemwidth' ]   = 180;
				wooFeaturedProductsSliderShortcodeCarousel[ 'data-touchscroll' ] = values.mouse_scroll;
				wooFeaturedProductsSliderShortcodeCarousel[ 'data-imagesize' ]   = values.picture_size;
				wooFeaturedProductsSliderShortcodeCarousel[ 'data-scrollitems' ] = values.scroll_items;

				return wooFeaturedProductsSliderShortcodeCarousel;
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
				var showCats           = ( 'yes' === values.show_cats ) ? 'enable' : 'disable',
					showPrice          = ( 'yes' === values.show_price ),
					showButtons        = ( 'yes' === values.show_buttons ),
					designClass        = 'fusion-' + extras.design_class + '-product-image-wrapper',
					featuredImageSize  = ( 'fixed' === values.picture_size ) ? 'shop_single' : 'full',
					productList        = '';

				_.each( queryData.products, function( product ) {

					var imageData = product.image_data,
						inCart    = jQuery.inArray( product.id, queryData.items_in_cart ),
						image     = '';

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

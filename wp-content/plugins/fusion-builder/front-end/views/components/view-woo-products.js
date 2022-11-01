var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Product Component View.
		FusionPageBuilder.WooProductsView = FusionPageBuilder.ElementView.extend( {

			onInit: function() {
				if ( this.model.attributes.markup && '' === this.model.attributes.markup.output ) {
					this.model.attributes.markup.output = this.getComponentPlaceholder();
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 3.2
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );

				this.values = atts.values;
				this.extras = atts.extras;
				this.query_data = atts.query_data;

				// Any extras that need passed on.
				attributes.cid    = this.model.get( 'cid' );
				attributes.attr   = this.buildAttr( atts.values );
				attributes.styles = this.buildStyleBlock();
				attributes.output = this.buildOutput( atts );
				attributes.layout = atts.values.products_layout;
				attributes.titleElement  = 'yes' === atts.values.heading_enable ? _.buildTitleElement( atts.values, atts.extras, this.getSectionTitle() ) : '';
				attributes.carouselAttrs = this.buildCarouselAttrs( atts.values );
				attributes.carouselNav   = 'yes' === atts.values.products_navigation ? this.buildCarouselNav() : '';
				attributes.productsAttrs = this.buildProductsAttrs( atts.values );
				attributes.query_data    = atts.query_data;
				// add placeholder.
				attributes.query_data.placeholder = this.getComponentPlaceholder();

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
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
			},

			/**
			 * Builds attributes.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-woo-products-tb ' + this.shortcode_classname + ' ' + this.shortcode_classname + '-' + this.model.get( 'cid' ),
						style: ''
					} );

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

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				attr = _.fusionAnimations( values, attr );

				return attr;
			},

			/**
			 * Builds carousel nav.
			 *
			 * @since 3.2
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
			 * @since 3.2
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildCarouselAttrs: function( values ) {
				var attr = {
					class: 'fusion-carousel'
				};

				/**
				 * Set the autoplay variable.
				 */
				attr[ 'data-autoplay' ] = values.products_autoplay;

				/**
				 * Set the touch scroll variable.
				 */
				attr[ 'data-touchscroll' ] = values.products_swipe;

				attr[ 'data-columns' ]     = values.products_columns;
				attr[ 'data-itemmargin' ]  = parseInt( values.products_column_spacing ) + 'px';
				attr[ 'data-itemwidth' ]   = 180;

				attr[ 'data-scrollitems' ] = ( 0 == values.products_swipe_items ) ? '' : values.products_swipe_items;

				return attr;
			},

			/**
			 * Builds products UL attributes.
			 *
			 * @since 3.2
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildProductsAttrs: function( values ) {
				var attr = {
					class: 'products products-' + values.products_columns
				};

				return attr;
			},

			/**
			 * Builds output.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output = '';

				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data[ this.shortcode_handle ] ) {
					output = atts.query_data[ this.shortcode_handle ];
				}

				return output;
			},

			/**
			 * Get section title based on the post type.
			 *
			 * @since 3.2
			 * @return {string}
			 */
			getSectionTitle: function() {
				return '';
			},

			/**
			 * Builds styles.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildStyleBlock: function() {
				var css, selectors;

				this.baseSelector = '.' + this.shortcode_classname + '.' + this.shortcode_classname + '-' +  this.model.get( 'cid' );
				this.dynamic_css  = {};

				if ( ! this.isDefault( 'products_layout' ) ) {
					selectors = [
						'body:not(.fusion-woocommerce-equal-heights):not(.fusion-woo-archive-page-columns-1) ' + this.baseSelector + ' .fusion-carousel .fusion-carousel-item .fusion-carousel-item-wrapper',
						'.fusion-woocommerce-equal-heights:not(.fusion-woo-archive-page-columns-1) ' + this.baseSelector + ' .products .product'
					];
					this.addCssProperty( selectors, 'display', 'block' );
					selectors = [ '.fusion-woocommerce-equal-heights:not(.fusion-woo-archive-page-columns-1) ' + this.baseSelector + ' .fusion-carousel .fusion-carousel-item .fusion-carousel-item-wrapper' ];
					this.addCssProperty( selectors, 'vertical-align', 'top' );
				}

				css = this.parseCSS();
				return ( css ) ? '<style>' + css + '</style>' : '';

			}

		} );
	} );
}( jQuery ) );

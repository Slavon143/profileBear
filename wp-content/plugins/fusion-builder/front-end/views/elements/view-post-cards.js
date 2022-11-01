/* eslint no-mixed-spaces-and-tabs: 0 */
/* global fusionAllElements, FusionApp, FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Fusion Post Cards View.
		FusionPageBuilder.fusion_post_cards = FusionPageBuilder.ElementView.extend( {

			onInit: function() {
				if ( this.model.attributes.markup && '' === this.model.attributes.markup.output ) {
					this.model.attributes.markup.output = this.getComponentPlaceholder();
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 3.3
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );
				this.values = atts.values;
				this.extras = atts.extras;

				// Any extras that need passed on.
				attributes.cid           = this.model.get( 'cid' );
				attributes.attr          = this.buildAttr( atts.values );
				attributes.styles        = this.buildStyleBlock( atts );
				attributes.productsLoop  = this.buildOutput( atts );
				attributes.filters       = this.buildFilters( atts );
				attributes.productsAttrs = this.buildProductsAttrs( atts.values );
				attributes.query_data    = atts.query_data;
				attributes.values        = atts.values;
				attributes.loadMoreText  = _.has( atts.extras, 'load_more_text_' + atts.values.post_type ) ? atts.extras[ 'load_more_text_' + atts.values.post_type ] : atts.extras.load_more_text;

				// carousel.
				if ( 'carousel' === atts.values.layout ) {
					attributes.carouselNav = this.buildCarouselNav();
				}

				if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.max_num_pages ) {
					if ( 'undefined' !== typeof atts.query_data.paged ) {
						attributes.pagination = this.buildPagination( atts );
					}
				}

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since  3.3
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

				if ( 'undefined' !== typeof values.filters_font_size && '' !== values.filters_font_size ) {
					values.filters_font_size = _.fusionGetValueWithUnit( values.filters_font_size );
				}

				if ( 'undefined' !== typeof values.filters_letter_spacing && '' !== values.filters_letter_spacing ) {
					values.filters_letter_spacing = _.fusionGetValueWithUnit( values.filters_letter_spacing );
				}

				if ( 'undefined' !== typeof values.active_filter_border_size && '' !== values.active_filter_border_size ) {
					values.active_filter_border_size = _.fusionGetValueWithUnit( values.active_filter_border_size );
				}

				if ( 'undefined' !== typeof values.filters_border_bottom && '' !== values.filters_border_bottom ) {
					values.filters_border_bottom = _.fusionGetValueWithUnit( values.filters_border_bottom );
				}

				if ( 'undefined' !== typeof values.filters_border_top && '' !== values.filters_border_top ) {
					values.filters_border_top = _.fusionGetValueWithUnit( values.filters_border_top );
				}

				if ( 'undefined' !== typeof values.filters_border_left && '' !== values.filters_border_left ) {
					values.filters_border_left = _.fusionGetValueWithUnit( values.filters_border_left );
				}

				if ( 'undefined' !== typeof values.filters_height && '' !== values.filters_height ) {
					values.filters_height = _.fusionGetValueWithUnit( values.filters_height );
				}

				if ( 'undefined' !== typeof values.filters_border_right && '' !== values.filters_border_right ) {
					values.filters_border_right = _.fusionGetValueWithUnit( values.filters_border_right );
				}

				if ( 'undefined' !== typeof values.nav_margin_top && '' !== values.nav_margin_top ) {
					values.nav_margin_top = _.fusionGetValueWithUnit( this.getReverseNum( values.nav_margin_top ) );
				}

				if ( 1 === parseInt( values.columns ) && ( 'grid' === values.layout || 'masonry' === values.layout ) ) {
					values.column_spacing = '0';
				}

				// No delay offering for carousels and sliders.
				if ( 'grid' !== values.layout ) {
					values.animation_delay = 0;
				}
			},

			/**
			 * Builds attributes.
			 *
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-post-cards fusion-post-cards-' + this.model.get( 'cid' )
					} );


				if ( '' !== values.animation_type ) {

					// Grid and has delay, set parent args here, otherwise it will be on children.
					if ( 'grid' === values.layout && 0 !== parseInt( values.animation_delay ) ) {
						attr = _.fusionAnimations( values, attr, false );
						attr[ 'data-animation-delay' ] = values.animation_delay;
						attr[ 'class' ]               += ' fusion-delayed-animation';
					} else {

						// Not grid always no delay, add to parent.
						attr = _.fusionAnimations( values, attr );
					}
				}

				if ( 'slider' === values.layout ) {
					attr[ 'class' ] += ' fusion-slider-sc fusion-flexslider-loading flexslider';

					attr[ 'data-slideshow_autoplay' ]  = 'no' === values.autoplay ? false : true;
					attr[ 'data-slideshow_animation' ] = values.slider_animation;
					attr[ 'data-slideshow_control_nav' ]  = 'no' === values.show_nav ? false : true;
				} else if ( 'carousel' === values.layout ) {
					attr[ 'class' ] += ' fusion-carousel fusion-carousel-responsive';

					attr[ 'data-autoplay' ]      = values.autoplay;
					attr[ 'data-columns' ]       = values.columns;
					attr[ 'data-columnsmedium' ] = values.columns_medium;
					attr[ 'data-columnssmall' ]  = values.columns_small;
					attr[ 'data-itemmargin' ]    = values.column_spacing;
					attr[ 'data-itemwidth' ]     = 180;
					attr[ 'data-touchscroll' ]   = values.mouse_scroll;
					attr[ 'data-imagesize' ]     = 'auto';
					attr[ 'data-scrollitems' ]   = values.scroll_items;
				} else if ( ( 'grid' === values.layout || 'masonry' === values.layout ) && 'terms' !== values.source ) {
					attr[ 'class' ] += ' fusion-grid-archive';
					attr[ 'class' ] += 'masonry' === values.layout ? ' fusion-post-cards-masonry' : '';
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
			 * @since 3.3
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
			 * Builds items UL attributes.
			 *
			 * @since 3.3
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildProductsAttrs: function( values ) {
				var attr = {
					class: ''
				};

				if ( 'grid' === values.layout || 'masonry' === values.layout ) {
					attr[ 'class' ] += 'fusion-grid fusion-grid-' + values.columns + ' fusion-flex-align-items-' + values.flex_align_items + ' fusion-' + values.layout + '-posts-cards';
				} else if ( 'slider' === values.layout ) {
					attr[ 'class' ] += 'slides';
				} else if ( 'carousel' === values.layout ) {
					attr[ 'class' ] += 'fusion-carousel-holder';
				}

				if ( this.isLoadMore() ) {
					attr[ 'class' ] += ' fusion-grid-container-infinite';
				}

				if ( 'load_more_button' === values.scrolling ) {
					attr[ 'class' ] += ' fusion-grid-container-load-more';
				}
				return attr;
			},

			/**
			 * Builds columns classes.
			 *
			 * @since 3.3
			 * @param {Object} atts - The attributes.
			 * @return {string}
			 */
			buildColumnClasses: function( atts ) {
				var classes = '';

				if ( 'grid' === atts.values.layout || 'masonry' === atts.values.layout ) {
					classes += 'fusion-grid-column fusion-post-cards-grid-column';
				} else if ( 'carousel' === atts.values.layout ) {
					classes += 'fusion-carousel-item';
				}

				if ( 'product' === atts.values.post_type && 'posts' === atts.values.source ) {
					classes += ' product';
				}
				return classes;
			},

			/**
			 * Builds columns wrapper.
			 *
			 * @since 3.3
			 * @param {Object} atts - The attributes.
			 * @return {string}
			 */
			buildColumnWrapper: function( atts ) {
				var classes = '';

				if ( 'carousel' === atts.values.layout ) {
					classes += 'fusion-carousel-item-wrapper';
				}
				return classes;
			},

			/**
			 * Builds the pagination.
			 *
			 * @since 3.3
			 * @param {Object} atts - The attributes.
			 * @return {string}
			 */
			buildPagination: function( atts ) {
				var globalPagination  = atts.extras.pagination_global,
					globalStartEndRange = atts.extras.pagination_start_end_range_global,
					range            = atts.extras.pagination_range_global,
					paged            = '',
					pages            = '',
					paginationCode   = '',
					queryData        = atts.query_data,
					values           = atts.values;

				if ( -1 == values.number_posts ) {
					values.scrolling = 'no';
				}

				if ( 'no' !== values.scrolling ) {
					paged = queryData.paged;
					pages = queryData.max_num_pages;

					paginationCode = _.fusionPagination( pages, paged, range, values.scrolling, globalPagination, globalStartEndRange );
				}
				return paginationCode;
			},

			/**
			 * Check is load more.
			 *
			 * @since 3.3
			 * @return {boolean}
			 */
			isLoadMore: function() {
				return -1 !== jQuery.inArray( this.values.scrolling, [ 'infinite', 'load_more_button' ] );
			},

			/**
			 * Get reverse number.
			 *
			 * @since 3.3
			 * @param {String} value - the number value.
			 * @return {String}
			 */
			getReverseNum: function( value ) {
				return -1 !== value.indexOf( '-' ) ? value.replace( '-', '' ) : '-' + value;
			},

			/**
			 * Get grid width value.
			 *
			 * @since 3.3
			 * @param {String} columns - the columns count.
			 * @return {String}
			 */
			getGridWidthVal: function( columns ) {
				var cols = [ '100%', '50%', '33.3333%', '25%', '20%', '16.6666%' ];
				return cols[ columns - 1 ];
			},

			/**
			 * Builds output.
			 *
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output = '',
					_self = this,
					lists;

				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.loop_product ) {
					lists = jQuery( '<ul>' + atts.query_data.loop_product + '</ul>' );
					lists.find( 'li' ).each( function() {
						jQuery( this ).removeClass( 'fusion-grid-column fusion-post-cards-grid-column fusion-carousel-item product' )
						.addClass( _self.buildColumnClasses( atts ) )
						.find( '.fusion-column-wrapper' ).removeClass( 'fusion-carousel-item-wrapper' )
						.addClass( _self.buildColumnWrapper( atts ) );

						// Separators are always added into data, just remove if not valid.
						if ( 'grid' !== _self.values.layout || 1 < parseInt( _self.values.columns ) ) {
							jQuery( this ).find( '.fusion-absolute-separator' ).remove();
						} else {
							jQuery( this ).find( '.fusion-absolute-separator' ).css( { display: 'block' } );
						}
					} );
					output = lists.html();
				}
				return output;
			},

			buildFilters: function( atts ) {
				var output = '';
				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) );
					output = ( 'undefined' === typeof output ) ? '' : '<div role="menubar">' + output.find( 'div[role="menubar"]' ).html() + '</div>';
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.filters ) {
					output = atts.query_data.filters;
				}

				return output;
			},

			/**
			 * Builds styles.
			 *
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildStyleBlock: function( atts ) {
				var css, selectors, media, column_spacing, row_spacing, text_styles,
					self                = this,
					values              = atts.values,
					responsive_style    = '',
					buttonSelector      = '',
					buttonHoverSelector = '',
					nestedCSS           = 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.nested_css ? atts.query_data.nested_css : null;

				this.baseSelector = '.fusion-post-cards.fusion-post-cards-' +  this.model.get( 'cid' );
				this.dynamic_css  = {};

				if ( 'no' !== values.filters && ( 'grid' === values.layout || 'masonry' === values.layout ) && 'posts' === values.source ) {

					selectors   = [ this.baseSelector + ' ul.fusion-filters li a' ];
					text_styles = _.fusionGetFontStyle( 'filters_font', values, 'object' );

					jQuery.each( text_styles, function( rule, value ) {
						self.addCssProperty( selectors, rule, value );
					} );

					if ( ! this.isDefault( 'filters_font_size' ) ) {
						this.addCssProperty( selectors, 'font-size', values.filters_font_size );
					}

					if ( ! this.isDefault( 'filters_line_height' ) ) {
						this.addCssProperty( selectors, 'line-height', values.filters_line_height );
					}

					if ( ! this.isDefault( 'filters_letter_spacing' ) ) {
						this.addCssProperty( selectors, 'letter-spacing', values.filters_letter_spacing );
					}

					if ( ! this.isDefault( 'filters_text_transform' ) ) {
						this.addCssProperty( selectors, 'text-transform', values.filters_text_transform );
					}

					if ( ! this.isDefault( 'filters_color' ) ) {
						this.addCssProperty( selectors, 'color', values.filters_color );
					}

					selectors = [ this.baseSelector + ' ul.fusion-filters li a:hover' ];

					if ( '' !== values.filters_hover_color ) {
						this.addCssProperty( selectors, 'color', values.filters_hover_color );
					}

					selectors = [ this.baseSelector + ' ul.fusion-filters li.fusion-active a' ];

					if ( '' !== values.filters_active_color ) {
						this.addCssProperty( selectors, 'color', values.filters_active_color );
					}

					if ( ! this.isDefault( 'active_filter_border_size' ) ) {
						this.addCssProperty( selectors, 'border-top-width', values.active_filter_border_size );
					}

					if ( ! this.isDefault( 'active_filter_border_color' ) ) {
						this.addCssProperty( selectors, 'border-color', values.active_filter_border_color );
					}

					selectors = [ this.baseSelector + ' ul.fusion-filters' ];

					if ( ! this.isDefault( 'filters_border_bottom' ) ) {
						this.addCssProperty( selectors, 'border-bottom-width', values.filters_border_bottom );
					}

					if ( ! this.isDefault( 'filters_border_top' ) ) {
						this.addCssProperty( selectors, 'border-top-width', values.filters_border_top );
					}

					if ( ! this.isDefault( 'filters_border_left' ) ) {
						this.addCssProperty( selectors, 'border-left-width', values.filters_border_left );
						this.addCssProperty( selectors, 'border-left-style', 'solid' );
					}

					if ( ! this.isDefault( 'filters_border_right' ) ) {
						this.addCssProperty( selectors, 'border-right-width', values.filters_border_right );
						this.addCssProperty( selectors, 'border-right-style', 'solid' );
					}

					if ( ! this.isDefault( 'filters_border_color' ) ) {
						this.addCssProperty( selectors, 'border-color', values.filters_border_color );
					}

					if ( ! this.isDefault( 'filters_height' ) ) {
						this.addCssProperty( selectors, 'min-height', values.filters_height );
					}

					if ( ! this.isDefault( 'filters_alignment' ) ) {
						this.addCssProperty( selectors, 'justify-content', values.filters_alignment );
					}
				}

				selectors = [ this.baseSelector + ' .infinite-scroll-hide' ];
				if ( this.isLoadMore() ) {
					this.addCssProperty( selectors, 'display', 'none' );
				}
				if ( 1 < parseInt( values.columns ) && '0' !== this.values.column_spacing ) {
					selectors = [ this.baseSelector + ' ul.fusion-grid' ];
					column_spacing = _.fusionGetValueWithUnit( this.values.column_spacing );
					  this.addCssProperty( selectors, 'margin-right', 'calc((' + column_spacing + ')/ -2)' );
					  this.addCssProperty( selectors, 'margin-left', 'calc((' + column_spacing + ')/ -2)' );
					  selectors = [ this.baseSelector + ' ul.fusion-grid > .fusion-grid-column' ];
					  this.addCssProperty( selectors, 'padding-left', 'calc((' + column_spacing + ')/ 2)' );
					  this.addCssProperty( selectors, 'padding-right', 'calc((' + column_spacing + ')/ 2)' );
					  selectors = [ this.baseSelector + ' ul.fusion-grid > .fusion-grid-column .fusion-column-inner-bg' ];
					  this.addCssProperty( selectors, 'margin-left', 'calc((' + column_spacing + ')/ 2)' );
					  this.addCssProperty( selectors, 'margin-right', 'calc((' + column_spacing + ')/ 2)' );
				}

				if ( ( 'grid' === this.values.layout || 'masonry' === this.values.layout ) && '0' !== this.values.row_spacing ) {
				  row_spacing =  _.fusionGetValueWithUnit( this.values.row_spacing );
				  selectors = [ this.baseSelector + ' ul.fusion-grid' ];
				  this.addCssProperty( selectors, 'margin-top', 'calc((' + row_spacing + ')/ -2)' );
				  selectors = [ this.baseSelector + ' ul.fusion-grid > .fusion-grid-column' ];
				  this.addCssProperty( selectors, 'padding-top', 'calc((' + row_spacing + ')/ 2)' );
				  this.addCssProperty( selectors, 'padding-bottom', 'calc((' + row_spacing + ')/ 2)' );
				  selectors = [ this.baseSelector + ' ul.fusion-grid > .fusion-grid-column > .fusion-column-inner-bg' ];
				  this.addCssProperty( selectors, 'margin-top', 'calc((' + row_spacing + ')/ 2)' );
				  this.addCssProperty( selectors, 'margin-bottom', 'calc((' + row_spacing + ')/ 2)' );
				}

				selectors = [ this.baseSelector ];
				// Margin styles.
				if ( ! this.isDefault( 'margin_top' ) ) {
				  this.addCssProperty( selectors, 'margin-top', values.margin_top );
				}
				if ( ! this.isDefault( 'margin_right' ) ) {
				  this.addCssProperty( selectors, 'margin-right', values.margin_right );
				}
				if ( ! this.isDefault( 'margin_bottom' ) ) {
				  this.addCssProperty( selectors, 'margin-bottom', values.margin_bottom );
				}
				if ( ! this.isDefault( 'margin_left' ) ) {
				  this.addCssProperty( selectors, 'margin-left', values.margin_left );
				}

				selectors = [ this.baseSelector + ' .flex-control-nav' ];
				if ( 'slider' === values.layout ) {
					this.addCssProperty( selectors, 'bottom', values.nav_margin_top );
				}

				if ( 'load_more_button' === values.scrolling ) {
					buttonSelector = this.baseSelector + ' .fusion-load-more-button';
					buttonHoverSelector = [
						this.baseSelector + ' .fusion-load-more-button:hover',
						this.baseSelector + ' .fusion-load-more-button:focus'
					];

					if ( values.load_more_btn_color ) {
						this.addCssProperty( buttonSelector, 'color', values.load_more_btn_color );
					}

					if ( values.load_more_btn_bg_color ) {
						this.addCssProperty( buttonSelector, 'background-color', values.load_more_btn_bg_color );
					}

					if ( values.load_more_btn_hover_color ) {
						this.addCssProperty( buttonHoverSelector, 'color', values.load_more_btn_hover_color );
					}

					if ( values.load_more_btn_hover_bg_color ) {
						this.addCssProperty( buttonHoverSelector, 'background-color', values.load_more_btn_hover_bg_color );
					}
				}

				// Process children css if it's there.
				if ( Array.isArray( nestedCSS ) ) {
					jQuery.each( nestedCSS, function( index, rules ) {

						if ( Array.isArray( rules ) ) {
							jQuery.each( rules, function( key, rule ) {
								var important = 'undefined' !== typeof rule.important ? rule.important : false;
								self.addCssProperty( self.baseSelector + ' ' + rule.selector, rule.rule, rule.value, important );
							} );
						}

					} );
				}

				css = this.parseCSS();

				if ( 'grid' === values.layout || 'masonry' === values.layout ) {
					_.each( [ 'medium', 'small' ], function( size ) {
						var key = 'columns_' + size;

						// Check for default value.
						if ( this.isDefault( key ) ) {
							return;
						}

						this.dynamic_css  = {};

						// Build responsive styles.
						selectors = [ this.baseSelector + ' .fusion-grid .fusion-grid-column' ];
						this.addCssProperty( selectors, 'width', this.getGridWidthVal( values[ key ] ) + '!important' );

						media            = '@media only screen and (max-width:' + this.extras[ 'visibility_' + size ] + 'px)';
						responsive_style += media + '{' + this.parseCSS() + '}';
					}, this );
					css += responsive_style;
				}

				// Responsive Filters Alignment.
				if ( 'no' !== values.filters && ( 'grid' === values.layout || 'masonry' === values.layout ) && 'posts' === values.source ) {
					_.each( [ 'medium', 'small' ], function( size ) {
						var key = 'filters_alignment_' + size;
						media = '@media only screen and (max-width:' + this.extras[ 'visibility_' + size ] + 'px)';

						if ( '' === this.values[ key ] ) {
							return;
						}

						this.dynamic_css = {};
						this.addCssProperty( [ this.baseSelector + ' ul.fusion-filters' ], 'justify-content', this.values[ key ] );
						css += media + '{' + this.parseCSS() + '}';
					}, this );
				}
				return ( css ) ? '<style>' + css + '</style>' : '';
			},

			/**
			 * Runs just after render on cancel.
			 *
			 * @since 3.5
			 * @return null
			 */
			 beforeGenerateShortcode: function() {
				var elementType = this.model.get( 'element_type' ),
					options     = fusionAllElements[ elementType ].params,
					values      = jQuery.extend( true, {}, fusionAllElements[ elementType ].defaults, _.fusionCleanParameters( this.model.get( 'params' ) ) );

				if ( 'object' !== typeof options ) {
					return;
				}

				// If images needs replaced lets check element to see if we have media being used to add to object.
				if ( 'undefined' !== typeof FusionApp.data.replaceAssets && FusionApp.data.replaceAssets && ( 'undefined' !== typeof FusionApp.data.fusion_element_type || 'fusion_template' === FusionApp.getPost( 'post_type' ) ) ) {

					this.mapStudioImages( options, values );

					if ( '' !== values.post_card ) {
						// If its not within object already, add it.
						if ( 'undefined' === typeof FusionPageBuilderApp.mediaMap.post_cards[ values.post_card ] ) {
							FusionPageBuilderApp.mediaMap.post_cards[ values.post_card ] = true;
						}
					}

				}
			}
		} );
	} );
}( jQuery ) );

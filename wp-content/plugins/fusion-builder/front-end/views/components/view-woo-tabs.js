var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Rating Component View.
		FusionPageBuilder.fusion_tb_woo_tabs = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs during render() call.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				var $this = this;

				jQuery( window ).on( 'load', function() {
					$this._refreshJs();
				} );
			},

			refreshJs: function() {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).find( '.fusion-builder-live-element[data-cid="' + this.model.get( 'cid' ) + '"] ' ).find( '.wc-tabs-wrapper, .woocommerce-tabs, .comment-form-rating select[name="rating"]:visible' ).trigger( 'init' );
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

				this.values = atts.values;

				// Any extras that need passed on.
				attributes.cid         = this.model.get( 'cid' );
				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.styles      = this.buildStyleBlock( atts.values );
				attributes.output      = this.buildOutput( atts );

				return attributes;
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
						class: 'fusion-woo-tabs-tb fusion-woo-tabs-tb-' + this.model.get( 'cid' ),
						style: ''
					} );

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( 'horizontal' === values.layout ) {
					attr[ 'class' ] += ' woo-tabs-horizontal';
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				attr = _.fusionAnimations( values, attr );

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
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-woo-tabs-tb' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.woo_tabs ) {
					output = atts.query_data.woo_tabs;
				}

				return this.disableInlineScripts( output );
			},

			/**
			 * Disables inline scripts.
			 *
			 * @since  3.2
			 * @param  {String} output - The output string.
			 * @return {String}
			 */
			disableInlineScripts: function( output ) {
				if ( -1 !== output.indexOf( '<script' ) && -1 !== output.indexOf( '</script>' ) ) {
					output = output.replace( '<script', '<!--<script' ).replace( '</script>', '</script>-->' );
				}
				return output;
			},

			/**
			 * Builds styles.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildStyleBlock: function( values ) {
				var self = this,
					titleSelectors,
					css,
					headingStyles = {},
					textStyles = {};

				this.baseSelector = '.fusion-woo-tabs-tb.fusion-woo-tabs-tb-' + this.model.get( 'cid' );
				this.dynamic_css  = {};

				jQuery.each( [ 'top', 'right', 'bottom', 'left' ], function( index, side ) {
					var marginName = 'margin_' + side;

					// Element margin.
					if ( '' !==  values[ marginName ] ) {
						self.addCssProperty( self.baseSelector, 'margin-' + side,  _.fusionGetValueWithUnit( values[ marginName ] ) );
					}
				} );

				if ( ! this.isDefault( 'backgroundcolor' ) ) {
					this.addCssProperty( this.baseSelector + ' .wc-tabs > li.active > a', 'background-color',  this.values.backgroundcolor );
					this.addCssProperty( this.baseSelector + ' .wc-tabs > li > a:hover', 'background-color',  this.values.backgroundcolor );
					this.addCssProperty( this.baseSelector + ' .woocommerce-Tabs-panel', 'background-color',  this.values.backgroundcolor );
				}

				if ( ! this.isDefault( 'inactivebackgroundcolor' ) ) {
					this.addCssProperty( this.baseSelector + ' .wc-tabs > li > a', 'background-color',  this.values.inactivebackgroundcolor );
				}

				if ( ! this.isDefault( 'active_nav_text_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .wc-tabs > li.active > a', 'color',  this.values.active_nav_text_color );
					this.addCssProperty( this.baseSelector + ' .wc-tabs > li > a:hover', 'color',  this.values.active_nav_text_color );
				}

				if ( ! this.isDefault( 'inactive_nav_text_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .wc-tabs > li > a', 'color',  this.values.inactive_nav_text_color );
				}

				if ( ! this.isDefault( 'bordercolor' ) ) {

					if ( 'horizontal' === values.layout ) {
						this.addCssProperty( this.baseSelector + '.woo-tabs-horizontal .woocommerce-tabs > .tabs .active', 'border-color',  this.values.bordercolor );
						this.addCssProperty( this.baseSelector + '.woo-tabs-horizontal .woocommerce-tabs > .tabs', 'border-color',  this.values.bordercolor );
					} else {
						this.addCssProperty( this.baseSelector + ' .woocommerce-tabs .tabs li a', 'border-color',  this.values.bordercolor );
					}
					this.addCssProperty( this.baseSelector + ' .woocommerce-tabs .panel', 'border-color',  this.values.bordercolor );
					this.addCssProperty( this.baseSelector + ' .woocommerce-tabs .panel .shop_attributes tr', 'border-color',  this.values.bordercolor );
				}

				// Text styles.
				if ( ! this.isDefault( 'text_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-tabs .panel', 'color',  this.values.text_color );
					this.addCssProperty( this.baseSelector + ' .woocommerce-tabs .panel .shop_attributes th', 'color',  this.values.text_color );
					this.addCssProperty( '#wrapper ' + this.baseSelector + ' .meta', 'color',  this.values.text_color );
					this.addCssProperty( [ this.baseSelector + ' .stars a', this.baseSelector + ' .stars a:after' ], 'color',  this.values.text_color );
				}

				if ( ! this.isDefault( 'text_font_size' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-tabs .panel', 'font-size',  _.fusionGetValueWithUnit( this.values.text_font_size ) );
				}

				if ( !this.isDefault( 'text_line_height' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-tabs .panel', 'line-height', this.values.text_line_height );
				}

				if ( !this.isDefault( 'text_text_transform' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-tabs .panel', 'text-transform', this.values.text_text_transform );
				}

				if ( !this.isDefault( 'text_letter_spacing' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-tabs .panel', 'letter-spacing',  _.fusionGetValueWithUnit( this.values.text_letter_spacing ) );
				}

				// Text typography styles.
				textStyles = _.fusionGetFontStyle( 'text_font', values, 'object' );
				jQuery.each( textStyles, function( rule, value ) {
					self.addCssProperty( self.baseSelector + ' .woocommerce-tabs .panel', rule, value );
				} );

				// Title styles.
				titleSelectors = [
					'#wrapper ' + this.baseSelector + ' #tab-reviews #reviews .woocommerce-Reviews-title',
					'#wrapper ' + this.baseSelector + ' .woocommerce-Tabs-panel .fusion-woocommerce-tab-title'
				];
				if ( ! this.isDefault( 'title_color' ) ) {
					this.addCssProperty( titleSelectors, 'color',  this.values.title_color );
				}

				if ( ! this.isDefault( 'title_font_size' ) ) {
					this.addCssProperty( titleSelectors, 'font-size', _.fusionGetValueWithUnit( this.values.title_font_size ) );
				}

				if ( !this.isDefault( 'title_line_height' ) ) {
					this.addCssProperty( titleSelectors, 'line-height', this.values.title_line_height );
				}

				if ( !this.isDefault( 'title_text_transform' ) ) {
					this.addCssProperty( titleSelectors, 'text-transform', this.values.title_text_transform );
				}

				if ( !this.isDefault( 'title_letter_spacing' ) ) {
					this.addCssProperty( titleSelectors, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.title_letter_spacing ) );
				}

				// Title typography styles.
				headingStyles = _.fusionGetFontStyle( 'title_font', values, 'object' );
				jQuery.each( headingStyles, function( rule, value ) {
					self.addCssProperty( titleSelectors, rule, value );
				} );

				if ( 'vertical' === this.values.layout && ! this.isDefault( 'nav_content_space' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-tabs .panel', 'margin-left', 'calc(220px + ' + _.fusionGetValueWithUnit( this.values.nav_content_space ) + ')' );
				}

				// Stars color.
				if ( ! this.isDefault( 'stars_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .comment-text .star-rating:before', 'color',  this.values.stars_color );
					this.addCssProperty( this.baseSelector + ' .comment-text .star-rating span:before', 'color',  this.values.stars_color );
				}

				// Get padding.
				jQuery.each( [ 'top', 'right', 'bottom', 'left' ], function( index, padding ) {
					var content_padding_name = 'content_padding_' + padding,
						nav_padding_name = 'nav_padding_' + padding;

					// Add content padding to style.
					if ( '' !==  self.values[ content_padding_name ] ) {
						self.addCssProperty( self.baseSelector + ' .woocommerce-tabs .panel', 'padding-' + padding,  _.fusionGetValueWithUnit( self.values[ content_padding_name ] ) );
					}

					if ( '' !==  self.values[ nav_padding_name ] ) {
						self.addCssProperty( self.baseSelector + ' .woocommerce-tabs .tabs li a', 'padding-' + padding,  _.fusionGetValueWithUnit( self.values[ nav_padding_name ] ) );
					}
				} );

				css = this.parseCSS();
				return ( css ) ? '<style>' + css + '</style>' : '';
			}
		} );
	} );
}( jQuery ) );

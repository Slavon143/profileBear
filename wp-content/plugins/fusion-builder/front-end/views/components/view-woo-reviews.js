/* eslint no-mixed-spaces-and-tabs: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Rating Component View.
		FusionPageBuilder.fusion_tb_woo_reviews = FusionPageBuilder.ElementView.extend( {

			onInit: function() {
				var params = this.model.get( 'params' );

				// Check for newer margin params.  If unset but regular is, copy from there.
				if ( 'object' === typeof params ) {

					// Split border width into 4.
					if ( 'undefined' === typeof params.button_border_top && 'undefined' !== typeof params.button_border_width && '' !== params.button_border_width ) {
						params.button_border_top    = parseInt( params.button_border_width ) + 'px';
						params.button_border_right  = params.button_border_top;
						params.button_border_bottom = params.button_border_top;
						params.button_border_left   = params.button_border_top;
						delete params.button_border_width;
					}
					this.model.set( 'params', params );
				}
			},

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
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).find( '.fusion-builder-live-element[data-cid="' + this.model.get( 'cid' ) + '"] ' ).find( '.comment-form-rating select[name="rating"]:visible' ).trigger( 'init' );
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
				this.params = this.model.get( 'params' );

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
						class: 'fusion-woo-reviews-tb fusion-woo-reviews-tb-' + this.model.get( 'cid' ),
						style: ''
					} );

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( 'no' == values.show_tab_title ) {
					attr[ 'class' ] += ' woo-reviews-hide-heading';
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
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-woo-reviews-tb' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.woo_reviews ) {
					output = atts.query_data.woo_reviews;
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
					textStyles = {},
					css = '',
					button,
					button_hover,
					button_size_map,
					button_dimensions,
					hasBorder = false;

				this.baseSelector = '.fusion-woo-reviews-tb.fusion-woo-reviews-tb-' + this.model.get( 'cid' );
				this.dynamic_css  = {};

				jQuery.each( [ 'top', 'right', 'bottom', 'left' ], function( index, side ) {
					var marginName = 'margin_' + side;

					// Element margin.
					if ( '' !==  values[ marginName ] ) {
						self.addCssProperty( self.baseSelector, 'margin-' + side,  _.fusionGetValueWithUnit( values[ marginName ] ) );
					}
				} );

				// Text styles.
				if ( ! this.isDefault( 'text_color' ) ) {
					this.addCssProperty( this.baseSelector, 'color',  this.values.text_color );
					this.addCssProperty( '#wrapper ' + this.baseSelector + ' .meta', 'color',  this.values.text_color );
					this.addCssProperty( [ this.baseSelector + ' .stars a', this.baseSelector + ' .stars a:after' ], 'color',  this.values.text_color );
				}

				if ( ! this.isDefault( 'text_font_size' ) ) {
					this.addCssProperty( this.baseSelector, 'font-size',  _.fusionGetValueWithUnit( this.values.text_font_size ) );
				}

				if ( !this.isDefault( 'text_line_height' ) ) {
					this.addCssProperty( this.baseSelector, 'line-height', this.values.text_line_height );
				}

				if ( !this.isDefault( 'text_text_transform' ) ) {
					this.addCssProperty( this.baseSelector, 'text-transform', this.values.text_text_transform );
				}

				if ( !this.isDefault( 'text_letter_spacing' ) ) {
					this.addCssProperty( this.baseSelector, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.text_letter_spacing ) );
				}

				// Text typography styles.
				textStyles = _.fusionGetFontStyle( 'text_font', values, 'object' );
				jQuery.each( textStyles, function( rule, value ) {
					self.addCssProperty( self.baseSelector, rule, value );
				} );

				// Border.
				if ( ! this.isDefault( 'border_size' ) ) {
					this.addCssProperty( this.baseSelector + ' #reviews li .comment-text', 'border-width',  this.values.border_size + 'px' );
				}

				if ( ! this.isDefault( 'border_color' ) ) {
					this.addCssProperty( this.baseSelector + ' #reviews li .comment-text', 'border-color',  this.values.border_color );
				}

				// Stars color.
				if ( ! this.isDefault( 'stars_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .comment-text .star-rating:before', 'color',  this.values.stars_color );
					this.addCssProperty( this.baseSelector + ' .comment-text .star-rating span:before', 'color',  this.values.stars_color );
				}

				if ( ! this.isDefault( 'rating_box_bg_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .stars > span > a', 'background-color',  this.values.rating_box_bg_color );
				}

				if ( ! this.isDefault( 'rating_box_active_bg_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .stars > span > a:hover', 'background-color',  this.values.rating_box_active_bg_color );
					this.addCssProperty( this.baseSelector + ' .stars > span > a.active', 'background-color',  this.values.rating_box_active_bg_color );
				}

				if ( !this.isDefault( 'button_style' ) ) {
					button = '.fusion-body ' +  this.baseSelector + ' #reviews input#submit.submit';
					// Button size.
					if (  !  this.isDefault( 'button_size' ) ) {
					  button_size_map = {
						  small: {
							  padding: '9px 20px',
							  line_height: '14px',
							  font_size: '12px'
						  },
						  medium: {
							  padding: '11px 23px',
							  line_height: '16px',
							  font_size: '13px'
						  },
						  large: {
							  padding: '13px 29px',
							  line_height: '17px',
							  font_size: '14px'
						  },
						  xlarge: {
							  padding: '17px 40px',
							  line_height: '21px',
							  font_size: '18px'
						  }
					  };

					  if ( 'object' === typeof button_size_map[ this.values.button_size ] ) {
						button_dimensions = button_size_map[ this.values.button_size ];
						this.addCssProperty( button, 'padding', button_dimensions.padding );
						this.addCssProperty( button, 'line-height', button_dimensions.line_height );
						this.addCssProperty( button, 'font-size', button_dimensions.font_size );
					  }

					}

					if (  !  this.isDefault( 'button_stretch' ) ) {
					  this.addCssProperty( button, 'flex', '1' );
					  this.addCssProperty( button, 'width', '100%' );
					}

					if (  !  this.isDefault( 'button_border_top' ) ) {
						hasBorder = true;
					   this.addCssProperty( button, 'border-top-width',  _.fusionGetValueWithUnit( this.values.button_border_top ) );
					}
					if (  !  this.isDefault( 'button_border_right' ) ) {
						hasBorder = true;
						this.addCssProperty( button, 'border-right-width',  _.fusionGetValueWithUnit( this.values.button_border_right ) );
					}
					if (  !  this.isDefault( 'button_border_bottom' ) ) {
						hasBorder = true;
					this.addCssProperty( button, 'border-bottom-width',  _.fusionGetValueWithUnit( this.values.button_border_bottom ) );
					}
					if (  !  this.isDefault( 'button_border_left' ) ) {
						hasBorder = true;
						this.addCssProperty( button, 'border-left-width',  _.fusionGetValueWithUnit( this.values.button_border_left ) );
					}
					if ( hasBorder ) {
 						this.addCssProperty( button, 'border-style', 'solid' );
 					}

					if (  !  this.isDefault( 'button_color' ) ) {
					  this.addCssProperty( button, 'color',  this.values.button_color );
					}

					if ( ( 'string' === typeof this.params.button_gradient_top && '' !==  this.params.button_gradient_top ) ||  ( 'string' === typeof this.params.button_gradient_bottom && '' !==  this.params.button_gradient_bottom ) ) {
					  this.addCssProperty( button, 'background', this.values.button_gradient_top );
					  this.addCssProperty( button, 'background-image', 'linear-gradient( to top, ' +  this.values.button_gradient_bottom + ', ' +  this.values.button_gradient_top + ' )' );
					}

					if (  !  this.isDefault( 'button_border_color' ) ) {
					  this.addCssProperty( button, 'border-color',  this.values.button_border_color );
					}

					button_hover = button + ':hover';
					// Button hover text color
					if (  !  this.isDefault( 'button_color_hover' ) ) {
					  this.addCssProperty( button_hover, 'color',  this.values.button_color_hover );
					}

					if ( ( 'string' === typeof this.params.button_gradient_top_hover && '' !== this.params.button_gradient_top_hover ) ||  ( 'string' === typeof this.params.button_gradient_bottom_hover && '' !== this.params.button_gradient_bottom_hover ) ) {
					  this.addCssProperty( button_hover, 'background',  this.values.button_gradient_top_hover );
					  this.addCssProperty( button_hover, 'background-image', 'linear-gradient( to top, ' +  this.values.button_gradient_bottom_hover + ', ' +  this.values.button_gradient_top_hover + ' )' );
					}

					if ( ! this.isDefault( 'button_border_color_hover' ) ) {
					  this.addCssProperty( button_hover, 'border-color',  this.values.button_border_color_hover );
					}
				  }

				css = this.parseCSS();

				return ( css ) ? '<style>' + css + '</style>' : '';
			}
		} );
	} );
}( jQuery ) );

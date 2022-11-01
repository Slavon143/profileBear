/* eslint no-mixed-spaces-and-tabs: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Notices Component View.
		FusionPageBuilder.fusion_tb_woo_notices = FusionPageBuilder.ElementView.extend( {

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
						class: 'fusion-woo-notices-tb fusion-woo-notices-tb-' + this.model.get( 'cid' ),
						style: ''
					} );

				if ( '' !== values.alignment ) {
					attr[ 'class' ] += ' alignment-text-' + values.alignment;
				}

				if ( '' !== values.show_button ) {
					attr[ 'class' ] += ' show-button-' + values.show_button;
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
			 * Builds output.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output = '';

				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-woo-notices-tb' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.woo_notices ) {
					output = atts.query_data.woo_notices;
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
				var css, selectors, selectorMessage, selectorError, selectorNotices;

				this.baseSelector = '.fusion-woo-notices-tb.fusion-woo-notices-tb-' +  this.model.get( 'cid' );
				this.dynamic_css  = {};
				selectorMessage = [
					this.baseSelector + ' .woocommerce-info',
					this.baseSelector + ' .woocommerce-message'
				];
				selectorError = [ this.baseSelector + ' .woocommerce-error li' ];
				selectorNotices = _.union( selectorMessage, selectorError );

				// Margin styles.
				if ( ! this.isDefault( 'margin_top' ) ) {
				  this.addCssProperty( selectorNotices, 'margin-top',  _.fusionGetValueWithUnit( values.margin_top ) );
				}
				if ( ! this.isDefault( 'margin_right' ) ) {
				  this.addCssProperty( selectorNotices, 'margin-right',  _.fusionGetValueWithUnit( values.margin_right ) );
				}
				if ( ! this.isDefault( 'margin_bottom' ) ) {
				  this.addCssProperty( selectorNotices, 'margin-bottom',  _.fusionGetValueWithUnit( values.margin_bottom ) );
				}
				if ( ! this.isDefault( 'margin_left' ) ) {
				  this.addCssProperty( selectorNotices, 'margin-left',  _.fusionGetValueWithUnit( values.margin_left ) );
				}

				// Padding styles.
				if ( ! this.isDefault( 'padding_top' ) ) {
				  this.addCssProperty( selectorNotices, 'padding-top',  _.fusionGetValueWithUnit( values.padding_top ) );
				}
				if ( ! this.isDefault( 'padding_right' ) ) {
				  this.addCssProperty( selectorNotices, 'padding-right',  _.fusionGetValueWithUnit( values.padding_right ) );
				}
				if ( ! this.isDefault( 'padding_bottom' ) ) {
				  this.addCssProperty( selectorNotices, 'padding-bottom',  _.fusionGetValueWithUnit( values.padding_bottom ) );
				}
				if ( ! this.isDefault( 'padding_left' ) ) {
				  this.addCssProperty( selectorNotices, 'padding-left',  _.fusionGetValueWithUnit( values.padding_left ) );
				}

				if ( ! this.isDefault( 'font_size' ) ) {
					this.addCssProperty( selectorNotices, 'font-size', values.font_size );
				}

				if ( ! this.isDefault( 'font_color' ) ) {
					this.addCssProperty( selectorNotices, 'color', values.font_color );
				}

				// Border styles.
				if ( ! this.isDefault( 'border_sizes_top' ) ) {
				  this.addCssProperty( selectorNotices, 'border-top-width',  _.fusionGetValueWithUnit( values.border_sizes_top ) );
				}
				if ( ! this.isDefault( 'border_sizes_right' ) ) {
				  this.addCssProperty( selectorNotices, 'border-right-width',  _.fusionGetValueWithUnit( values.border_sizes_right ) );
				}
				if ( ! this.isDefault( 'border_sizes_bottom' ) ) {
				  this.addCssProperty( selectorNotices, 'border-bottom-width',  _.fusionGetValueWithUnit( values.border_sizes_bottom ) );
				}
				if ( ! this.isDefault( 'border_sizes_left' ) ) {
				  this.addCssProperty( selectorNotices, 'border-left-width',  _.fusionGetValueWithUnit( values.border_sizes_left ) );
				}
				if ( ! this.isDefault( 'border_radius_top_left' ) ) {
				  this.addCssProperty( selectorNotices, 'border-top-left-radius',  _.fusionGetValueWithUnit( values.border_radius_top_left ) );
				}
				if ( ! this.isDefault( 'border_radius_top_right' ) ) {
				  this.addCssProperty( selectorNotices, 'border-top-right-radius',  _.fusionGetValueWithUnit( values.border_radius_top_right ) );
				}
				if ( ! this.isDefault( 'border_radius_bottom_right' ) ) {
				  this.addCssProperty( selectorNotices, 'border-bottom-right-radius',  _.fusionGetValueWithUnit( values.border_radius_bottom_right ) );
				}
				if ( ! this.isDefault( 'border_radius_bottom_left' ) ) {
				  this.addCssProperty( selectorNotices, 'border-bottom-left-radius',  _.fusionGetValueWithUnit( values.border_radius_bottom_left ) );
				}
				if ( ! this.isDefault( 'border_style' ) ) {
					this.addCssProperty( selectorNotices, 'border-style', values.border_style );
				}
				if ( ! this.isDefault( 'border_color' ) ) {
					this.addCssProperty( selectorNotices, 'border-color', values.border_color );
				}

				if ( ! this.isDefault( 'background_color' ) ) {
					this.addCssProperty( selectorNotices, 'background-color', values.background_color );
				}

				// Icon styles.
				selectors = [
					this.baseSelector + ' .woocommerce-info .fusion-woo-notices-tb-icon',
					this.baseSelector + ' .woocommerce-message .fusion-woo-notices-tb-icon',
					this.baseSelector + ' .woocommerce-error .fusion-woo-notices-tb-icon'
				];
				if ( ! this.isDefault( 'icon_size' ) ) {
					this.addCssProperty( selectors, 'font-size', values.icon_size + 'px' );
				}
				if ( ! this.isDefault( 'icon_color' ) ) {
					this.addCssProperty( selectors, 'color', values.icon_color );
				}

				// Remove default cart icon styles.
				selectors = [
					this.baseSelector + ' .woocommerce-message .wc-forward:before',
					this.baseSelector + ' .woocommerce-info .wc-forward:before'
				];
				if ( ! this.isDefault( 'cart_icon_style' ) ) {
					this.addCssProperty( selectors, 'content', '""' );
					this.addCssProperty( selectors, 'margin-right', '0' );
				}

				// Link & Hover styles.
				selectors = [
					this.baseSelector + ' .woocommerce-info .wc-forward',
					this.baseSelector + ' .woocommerce-message .wc-forward',
					this.baseSelector + ' .woocommerce-error .wc-forward'
				];
				if ( ! this.isDefault( 'link_color' ) ) {
					this.addCssProperty( selectors, 'color', values.link_color );
				}
				selectors = [
					this.baseSelector + ' .woocommerce-info .wc-forward:hover',
					this.baseSelector + ' .woocommerce-message .wc-forward:hover',
					this.baseSelector + ' .woocommerce-error .wc-forward:hover'
				];
				if ( ! this.isDefault( 'link_hover_color' ) ) {
					this.addCssProperty( selectors, 'color', values.link_hover_color );
				}

				// Success styles.
				selectors = [ this.baseSelector + ' .woocommerce-message' ];
				if ( ! this.isDefault( 'success_border_color' ) ) {
					this.addCssProperty( selectors, 'border-color', values.success_border_color );
				}
				if ( ! this.isDefault( 'success_background_color' ) ) {
					this.addCssProperty( selectors, 'background-color', values.success_background_color );
				}
				if ( ! this.isDefault( 'success_text_color' ) ) {
					this.addCssProperty( selectors, 'color', values.success_text_color );
				}
				selectors = [ this.baseSelector + ' .woocommerce-message .fusion-woo-notices-tb-icon' ];
				if ( ! this.isDefault( 'success_icon_color' ) ) {
					this.addCssProperty( selectors, 'color', values.success_icon_color );
				}

				// Success Link & Hover styles.
				selectors = [ this.baseSelector + ' .woocommerce-message .wc-forward' ];
				if ( ! this.isDefault( 'success_link_color' ) ) {
					this.addCssProperty( selectors, 'color', values.success_link_color );
				}
				selectors = [ this.baseSelector + ' .woocommerce-message .wc-forward:hover' ];
				if ( ! this.isDefault( 'success_link_hover_color' ) ) {
					this.addCssProperty( selectors, 'color', values.success_link_hover_color );
				}

				// Error styles.
				if ( ! this.isDefault( 'error_border_color' ) ) {
					this.addCssProperty( selectorError, 'border-color', values.error_border_color );
				}
				if ( ! this.isDefault( 'error_background_color' ) ) {
					this.addCssProperty( selectorError, 'background-color', values.error_background_color );
				}
				if ( ! this.isDefault( 'error_text_color' ) ) {
					this.addCssProperty( selectorError, 'color', values.error_text_color );
				}
				selectors = [ this.baseSelector + ' .woocommerce-error .fusion-woo-notices-tb-icon' ];
				if ( ! this.isDefault( 'error_icon_color' ) ) {
					this.addCssProperty( selectors, 'color', values.error_icon_color );
				}

				// Error Link & Hover styles.
				selectors = [ this.baseSelector + ' .woocommerce-error .wc-forward' ];
				if ( ! this.isDefault( 'error_link_color' ) ) {
					this.addCssProperty( selectors, 'color', values.error_link_color );
				}
				selectors = [ this.baseSelector + ' .woocommerce-error .wc-forward:hover' ];
				if ( ! this.isDefault( 'error_link_hover_color' ) ) {
					this.addCssProperty( selectors, 'color', values.error_link_hover_color );
				}

				css = this.parseCSS();
				return ( css ) ? '<style>' + css + '</style>' : '';

			}

		} );
	} );
}( jQuery ) );

/* global fusionBuilderText */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Woo Filters View.
		FusionPageBuilder.WooFiltersView = FusionPageBuilder.ElementView.extend( {

			/**
			 * CSS Vars prefix.
			 *
			 * @since  3.8
			 */
			css_vars_prefix: '--awb-',

			/**
			 * CSS Vars default prefix.
			 *
			 * @since  3.8.1
			 */
			css_vars_prefix_default: '--awb-',

			onInit: function() {
				if ( this.model.attributes.markup && ! this.model.attributes.markup.output ) {
					this.model.attributes.markup.output = '' === this.model.attributes.markup.output ? this.getComponentPlaceholder( this.model.attributes ) : this.model.attributes.markup.output;
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 3.8
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
				attributes.output = this.buildOutput( atts );

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since  3.8
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

				if ( 'undefined' !== typeof values.title_font_size && '' !== values.title_font_size ) {
					values.title_font_size = _.fusionGetValueWithUnit( values.title_font_size );
				}

				if ( 'undefined' !== typeof values.title_letter_spacing && '' !== values.title_letter_spacing ) {
					values.title_letter_spacing = _.fusionGetValueWithUnit( values.title_letter_spacing );
				}

				_.each( [ 'top', 'bottom' ], function( direction ) {
					var marginKey = 'title_margin_' + direction;
					if ( 'undefined' !== typeof values[ marginKey ] && '' !== values[ marginKey ] ) {
						values[ marginKey ] = _.fusionGetValueWithUnit( values[ marginKey ] );
					}
				} );
			},

			/**
			 * Builds attributes.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'awb-woo-filters ' + this.shortcode_classname + ' ' + this.shortcode_classname + '-' + this.model.get( 'cid' ),
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

				attr.style += this.getStyleVariables( values );

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				if ( 'yes' !== values.show_title ) {
					attr[ 'class' ] += ' hide-show_title';
				}

				attr = _.fusionAnimations( values, attr );

				return attr;
			},

			/**
			 * Builds output.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output = '';

				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.output ) {
					output = atts.query_data.output;
				}

				if ( '' === output ) {
					output = this.getComponentPlaceholder( atts );
				}

				return output;
			},

			/**
			 * Gets style variables.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			getStyleVariables: function( values ) {
				var styles = '',
					marginKey = '';

				// Title Typo.
				styles += this.getTypoVars( {
					'title_font': 'font',
					'title_font_size': 'size',
					'title_line_height': 'line_height',
					'title_letter_spacing': 'letter_spacing',
					'title_text_transform': 'text_transform',
					'title_color': 'color'
				}, values );

				// Title Margin.
				_.each( [ 'top', 'bottom' ], function( direction ) {
					marginKey = 'title_margin_' + direction;
					if ( ! this.isDefault( marginKey ) ) {
						styles += `${this.css_vars_prefix}title-margin-${direction}:${values[ marginKey ]};`;
					}
				}, this );

				return styles;
			},

			/**
			 * Get dimension variable css.
			 *
			 * @since  3.8
			 */
			getDimensionVars: function( option_name, values ) {
				var style = '',
					key;

				_.each( [ 'top', 'right', 'bottom', 'left' ], function( direction ) {
					key = `${option_name}_${direction}`;

					if ( ! this.isDefault( key ) ) {
						style += `${this.css_vars_prefix}${key.replaceAll( '_', '-' )}: ${values[ key ]};`;
					}
				}, this );

				return style;
			},

			/**
			 * Gets style variables.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			getTypoVars: function( params, values ) {
				var style = '',
					fontStyles = '',
					cssPrefix,
					prefix;

				_.each( params, function( type, key ) {
					cssPrefix = -1 !== key.indexOf( 'title' ) ? this.css_vars_prefix_default : this.css_vars_prefix;
					prefix    = `${cssPrefix}${key.replaceAll( '_', '-' )}`;

					if ( 'font' === type ) {
						fontStyles = _.fusionGetFontStyle( key, values, 'array' );
						_.each( fontStyles, function( value, rule ) {
							style += `${prefix}-${rule.replaceAll( '_', '-' )}: ${fontStyles[ rule ]};`;
						}, this );
					} else if ( ! this.isDefault( key ) ) {
						style += `${prefix}: ${values[ key ]};`;
					}
				}, this );

				return style;
			},

			/**
			 * Get the placeholder.
			 *
			 * @since 3.8
			 * @return {string}
			 */
			getPlaceholder: function() {
				var label  		= window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				var icon   		= window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;

				var placeholder = _.template( '<div class="fusion-builder-placeholder-preview awb-narrow-placeholder-preview"><i class="<%= icon %>" aria-hidden="true"></i> <%= label %></div>' );
				return placeholder( { icon: icon, label: label } );
			},

			/**
			 * Get component placeholder.
			 *
			 * @since 3.8
			 * @return {string}
			 */
			getComponentPlaceholder: function( atts ) {
				var placeholder,
					msg = fusionBuilderText.dynamic_source;

				if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.msg && '' !== atts.query_data.msg ) {
					msg = atts.query_data.msg;
				}

				placeholder = jQuery( this.getPlaceholder() ).append( '<a href="#" class="fusion-tb-source">' + msg + '</a>' );
				return placeholder[ 0 ].outerHTML;
			}
		} );

        // Woo Filters Active View.
		FusionPageBuilder.fusion_tb_woo_filters_active = FusionPageBuilder.WooFiltersView.extend( {

			/**
			 * Define shortcode handle.
			 *
			 * @since  3.8
			 */
			shortcode_handle: 'fusion_tb_woo_filters_active',

			/**
			 * Define shortcode classname.
			 *
			 * @since  3.8
			 */
			shortcode_classname: 'awb-woo-filters-active',

			/**
			 * CSS Vars prefix.
			 *
			 * @since  3.8
			 */
			css_vars_prefix: '--awb-woo-filter-',

			/**
			 * Builds attributes.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = FusionPageBuilder.WooFiltersView.prototype.buildAttr.call( this, values );

				return attr;
			},

			/**
			 * Modifies the values.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				var units = [
					'item_font_size',
					'item_letter_spacing',
					'item_padding_top',
					'item_padding_right',
					'item_padding_bottom',
					'item_padding_left'
				];

				FusionPageBuilder.WooFiltersView.prototype.validateValues.call( this, values );

				_.each( units, function( unit ) {
					if ( 'undefined' !== typeof values[ unit ] && '' !== values[ unit ] ) {
						values[ unit ] = _.fusionGetValueWithUnit( values[ unit ] );
					}
				}, this );
			},

			/**
			 * Gets style variables.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			getStyleVariables: function( values ) {
				var styles = FusionPageBuilder.WooFiltersView.prototype.getStyleVariables.call( this, values );

				if ( ! this.isDefault( 'item_color' ) ) {
					styles += `${this.css_vars_prefix}item-color: ${values.item_color};`;
				}

				if ( ! this.isDefault( 'item_bgcolor' ) ) {
					styles += `${this.css_vars_prefix}item-bgcolor: ${values.item_bgcolor};`;
				}

				if ( ! this.isDefault( 'item_hover_color' ) ) {
					styles += `${this.css_vars_prefix}item-hover-color: ${values.item_hover_color};`;
				}

				if ( ! this.isDefault( 'item_hover_bgcolor' ) ) {
					styles += `${this.css_vars_prefix}item-hover-bgcolor: ${values.item_hover_bgcolor};`;
				}

				// Item Typo.
				styles += this.getTypoVars( {
					'item_font': 'font',
					'item_font_size': 'size',
					'item_line_height': 'line_height',
					'item_letter_spacing': 'letter_spacing',
					'item_text_transform': 'text_transform'
				}, values );

				// Item Padding.
				styles += this.getDimensionVars( 'item_padding', values );

				return styles;
			}

		} );

        // Woo Filters Price View.
		FusionPageBuilder.fusion_tb_woo_filters_price = FusionPageBuilder.WooFiltersView.extend( {

			/**
			 * Define shortcode handle.
			 *
			 * @since  3.8
			 */
			shortcode_handle: 'fusion_tb_woo_filters_price',

			/**
			 * Define shortcode classname.
			 *
			 * @since  3.8
			 */
			shortcode_classname: 'awb-woo-filters-price',

			/**
			 * Builds attributes.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = FusionPageBuilder.WooFiltersView.prototype.buildAttr.call( this, values );

				return attr;
			},

			/**
			 * Modifies the values.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				var units = [
					'price_font_size',
					'price_letter_spacing'
				];

				FusionPageBuilder.WooFiltersView.prototype.validateValues.call( this, values );

				_.each( units, function( unit ) {
					if ( 'undefined' !== typeof values[ unit ] && '' !== values[ unit ] ) {
						values[ unit ] = _.fusionGetValueWithUnit( values[ unit ] );
					}
				}, this );
			},

			/**
			 * Gets style variables.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			getStyleVariables: function( values ) {
				var styles = FusionPageBuilder.WooFiltersView.prototype.getStyleVariables.call( this, values );

				if ( ! this.isDefault( 'range_filled_color' ) ) {
					styles += `${this.css_vars_prefix}range-filled-color: ${values.range_filled_color};`;
				}

				if ( ! this.isDefault( 'range_unfilled_color' ) ) {
					styles += `${this.css_vars_prefix}range-unfilled-color: ${values.range_unfilled_color};`;
				}

				if ( ! this.isDefault( 'range_button_color' ) ) {
					styles += `${this.css_vars_prefix}range-button-color: ${values.range_button_color};`;
				}

				if ( ! this.isDefault( 'range_button_bgcolor' ) ) {
					styles += `${this.css_vars_prefix}range-button-bgcolor: ${values.range_button_bgcolor};`;
				}

				if ( ! this.isDefault( 'range_button_hover_color' ) ) {
					styles += `${this.css_vars_prefix}range-button-hover-color: ${values.range_button_hover_color};`;
				}

				if ( ! this.isDefault( 'range_button_hover_bgcolor' ) ) {
					styles += `${this.css_vars_prefix}range-button-hover-bgcolor: ${values.range_button_hover_bgcolor};`;
				}

				if ( ! this.isDefault( 'range_handle_bgcolor' ) ) {
					styles += `${this.css_vars_prefix}range-handle-bgcolor: ${values.range_handle_bgcolor};`;
				}

				if ( ! this.isDefault( 'range_handle_border_color' ) ) {
					styles += `${this.css_vars_prefix}range-handle-border-color: ${values.range_handle_border_color};`;
				}

				// Price Typo.
				styles += this.getTypoVars( {
					'price_font': 'font',
					'price_font_size': 'size',
					'price_line_height': 'line_height',
					'price_letter_spacing': 'letter_spacing',
					'price_text_transform': 'text_transform',
					'price_color': 'color'
				}, values );

				return styles;
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 3.8
			 * @return null
			 */
			afterPatch: function() {

				// This will trigger a JS event on the preview frame.
				this._refreshJs();

				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'init_price_filter' );
			}

		} );

        // Woo Filters Rating View.
		FusionPageBuilder.fusion_tb_woo_filters_rating = FusionPageBuilder.WooFiltersView.extend( {

			/**
			 * Define shortcode handle.
			 *
			 * @since  3.8
			 */
			shortcode_handle: 'fusion_tb_woo_filters_rating',

			/**
			 * Define shortcode classname.
			 *
			 * @since  3.8
			 */
			shortcode_classname: 'awb-woo-filters-rating',

			/**
			 * Builds attributes.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = FusionPageBuilder.WooFiltersView.prototype.buildAttr.call( this, values );

				return attr;
			},

			/**
			 * Gets style variables.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			getStyleVariables: function( values ) {
				var styles = FusionPageBuilder.WooFiltersView.prototype.getStyleVariables.call( this, values );

				if ( ! this.isDefault( 'text_color' ) ) {
					styles += `${this.css_vars_prefix}text-color: ${values.text_color};`;
				}

				if ( ! this.isDefault( 'text_hover_color' ) ) {
					styles += `${this.css_vars_prefix}text-hover-color: ${values.text_hover_color};`;
				}

				if ( ! this.isDefault( 'star_color' ) ) {
					styles += `${this.css_vars_prefix}star-color: ${values.star_color};`;
				}

				if ( ! this.isDefault( 'star_hover_color' ) ) {
					styles += `${this.css_vars_prefix}star-hover-color: ${values.star_hover_color};`;
				}

				return styles;
			}

		} );

        // Woo Filters Attribute View.
		FusionPageBuilder.fusion_tb_woo_filters_attribute = FusionPageBuilder.WooFiltersView.extend( {

			/**
			 * Define shortcode handle.
			 *
			 * @since  3.8
			 */
			shortcode_handle: 'fusion_tb_woo_filters_attribute',

			/**
			 * Define shortcode classname.
			 *
			 * @since  3.8
			 */
			shortcode_classname: 'awb-woo-filters-attribute',

			/**
			 * Builds attributes.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = FusionPageBuilder.WooFiltersView.prototype.buildAttr.call( this, values );

				if ( 'undefined' !== typeof this.query_data && 'undefined' !== typeof this.query_data.extra_classes ) {
					attr[ 'class' ] += ` ${this.query_data.extra_classes}`;
				}
				return attr;
			},

			/**
			 * Modifies the values.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				var units = [
					'list_padding_top',
					'list_padding_right',
					'list_padding_bottom',
					'list_padding_left',
					'list_item_font_size',
					'list_item_letter_spacing',
					'attr_padding_top',
					'attr_padding_right',
					'attr_padding_bottom',
					'attr_padding_left',
					'count_font_size'
				];

				FusionPageBuilder.WooFiltersView.prototype.validateValues.call( this, values );

				_.each( units, function( unit ) {
					if ( 'undefined' !== typeof values[ unit ] && '' !== values[ unit ] ) {
						values[ unit ] = _.fusionGetValueWithUnit( values[ unit ] );
					}
				}, this );
			},

			/**
			 * Gets style variables.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			getStyleVariables: function( values ) {
				var styles = FusionPageBuilder.WooFiltersView.prototype.getStyleVariables.call( this, values ),
					colors;

				if ( ! this.isDefault( 'list_color' ) ) {
					styles += `${this.css_vars_prefix}list-color: ${values.list_color};`;
				}

				if ( ! this.isDefault( 'list_hover_color' ) ) {
					styles += `${this.css_vars_prefix}list-hover-color: ${values.list_hover_color};`;
				}

				if ( ! this.isDefault( 'list_sep_color' ) ) {
					styles += `${this.css_vars_prefix}sep-color: ${values.list_sep_color};`;
				}

				// List Item Padding.
				styles += this.getDimensionVars( 'list_padding', values );

				// List Item Typo.
				styles += this.getTypoVars( {
					'list_item_font': 'font',
					'list_item_font_size': 'size',
					'list_item_line_height': 'line_height',
					'list_item_letter_spacing': 'letter_spacing',
					'list_item_text_transform': 'text_transform'
				}, values );

				// Colors style.
				colors = [
					'list_color',
					'list_hover_color',
					'list_bgcolor',
					'list_hover_bgcolor',
					'list_sep_color',
					'attr_color',
					'attr_bgcolor',
					'attr_border_color',
					'attr_hover_color',
					'attr_hover_bgcolor',
					'attr_border_hover_color',
					'dd_color',
					'dd_bgcolor',
					'dd_hover_color',
					'dd_hover_bgcolor',
					'dd_border_color',
					'count_color',
					'count_hover_color'
				];

				_.each( colors, function( color ) {
					if ( ! this.isDefault( color ) ) {
						styles += `${this.css_vars_prefix}${color.replaceAll( '_', '-' )}: ${values[ color ]};`;
					}
				}, this );

				// Attribute Padding.
				styles += this.getDimensionVars( 'attr_padding', values );

				if ( ! this.isDefault( 'list_align' ) ) {
					styles += `${this.css_vars_prefix}list-align: ${values.list_align};`;
				}

				// Count Typo.
				styles += this.getTypoVars( {
					'count_font': 'font',
					'count_font_size': 'size',
					'count_line_height': 'line_height',
					'count_letter_spacing': 'letter_spacing',
					'count_text_transform': 'text_transform'
				}, values );

				if ( ! this.isDefault( 'show_count' ) ) {
					styles += `${this.css_vars_prefix}show-count: none;`;
				}

				return styles;
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 3.8
			 * @return null
			 */
			afterPatch: function() {
				var self = this,
					$obj;

				// This will trigger a JS event on the preview frame.
				this._refreshJs();

				if ( 'function' === typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery.fn.selectWoo ) {
					$obj = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.awb-woo-filters .dropdown_layered_nav_' + this.model.attributes.params.attribute );
					$obj.selectWoo( {
						minimumResultsForSearch: 5,
						width: '100%',
						allowClear: true
					} );

					$obj.on( 'select2:open', function() {
						$obj.data( 'select2' ).$dropdown.addClass( 'awb-woo-filters' )[ 0 ].style.cssText += self.getStyleVariables( self.model.attributes.params );
					} );
				}
			}
		} );
	} );
}( jQuery ) );

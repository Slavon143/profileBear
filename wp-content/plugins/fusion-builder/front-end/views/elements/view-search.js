/* global */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {
	jQuery( document ).ready( function() {
		// Button Element View.
		FusionPageBuilder.fusion_search = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.2
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				this.validateValues( atts.values );

				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.formAttr    = this.buildFormAttr( atts.values );
				attributes.formStyles  = this.buildStyles( atts.values );

				// Any extras that need passed on.
				attributes.cid = this.model.get( 'cid' );

				// Any extras that need passed on.
				attributes.values = atts.values;

				return attributes;
			},

			/**
			 * Validates the values.
			 *
			 * @since 3.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {

				// Old value check.
				if ( values.border_width ) {
					values.border_width       = _.fusionValidateAttrValue( values.border_width, 'px' );
					values.border_size_top    = '' !== values.border_size_top ? values.border_width : values.border_size_top;
					values.border_size_right  = '' !== values.border_size_right ? values.border_width : values.border_size_right;
					values.border_size_bottom = '' !== values.border_size_bottom ? values.border_width : values.border_size_bottom;
					values.border_size_left   = '' !== values.border_size_left ? values.border_width : values.border_size_left;
					delete values.border_width;
				}

				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.input_height  = _.fusionValidateAttrValue( values.input_height, 'px' );
				values.border_radius = _.fusionValidateAttrValue( values.border_radius, 'px' );
			},

			buildFormAttr: function( values ) {
				var attr = {
					class: 'searchform fusion-search-form fusion-live-search'
				};

				if ( values.design ) {
					attr[ 'class' ] += ' fusion-search-form-' + values.design;
				}

				return attr;
			},

			buildStyles: function( values ) {
				var styles = '<style type="text/css">';

				if ( '' !== values.input_height ) {
					styles += '.fusion-search-element-' + this.model.get( 'cid' ) + ' .searchform .fusion-search-form-content .fusion-search-field input,';
					styles += '.fusion-search-element-' + this.model.get( 'cid' ) + ' .searchform .fusion-search-form-content .fusion-search-button input[type=submit] {';
					styles += 'height: ' + values.input_height + ';';
					styles += '}';

					styles += '.fusion-search-element-' + this.model.get( 'cid' ) + ' .searchform .fusion-search-form-content .fusion-search-button input[type=submit] {';
					styles += 'line-height: ' + values.input_height + ';';
					styles += '}';

					styles += '.fusion-search-element-' + this.model.get( 'cid' ) + ' .searchform.fusion-search-form-clean .fusion-search-form-content .fusion-search-field input {';
					styles += 'padding-left: ' + values.input_height + ';';
					styles += '}';

					styles += '.fusion-search-element-' + this.model.get( 'cid' ) + ' .searchform .fusion-search-form-content .fusion-search-button input[type=submit] {';
					styles += 'width: ' + values.input_height + ';';
					styles += '}';
				}

				if ( '' !== values.text_color ) {
					styles += '.fusion-search-element-' + this.model.get( 'cid' ) + ' .searchform .fusion-search-form-content .fusion-search-field input,';
					styles += '.fusion-search-element-' + this.model.get( 'cid' ) + ' .searchform .fusion-search-form-content .fusion-search-field input::placeholder,';
					styles += '.fusion-search-element-' + this.model.get( 'cid' ) + ' .searchform.fusion-search-form-clean .fusion-search-form-content .fusion-search-button input[type=submit] {';
					styles += 'color: ' + values.text_color + ';';
					styles += '}';
				}

				if ( '' !== values.focus_border_color ) {
					styles += '.fusion-search-element-' + this.model.get( 'cid' ) + ' .searchform .fusion-search-form-content .fusion-search-field input:focus {';
					styles += 'border-color: ' + values.focus_border_color + ';';
					styles += '}';
				}

				if ( '' !== values.text_size ) {
					styles += '.fusion-search-element-' + this.model.get( 'cid' ) + ' .searchform .fusion-search-form-content .fusion-search-field input,';
					styles += '.fusion-search-element-' + this.model.get( 'cid' ) + ' .searchform.fusion-search-form-clean .fusion-search-form-content .fusion-search-button input[type=submit] {';
					styles += 'font-size: ' + values.text_size + ';';
					styles += '}';
				}

				styles += '.fusion-search-element-' + this.model.get( 'cid' ) + ' .searchform .fusion-search-form-content .fusion-search-field input {';

				if ( '' !== values.bg_color ) {
					styles += 'background-color: ' + values.bg_color + ';';
				}

				if ( '' !== values.border_size_top ) {
					styles += 'border-top-width:' + _.fusionGetValueWithUnit( values.border_size_top ) + ';';
				}
				if ( '' !== values.border_size_right ) {
					styles += 'border-right-width:' + _.fusionGetValueWithUnit( values.border_size_right ) + ';';
				}
				if ( '' !== values.border_size_bottom ) {
					styles += 'border-bottom-width:' + _.fusionGetValueWithUnit( values.border_size_bottom ) + ';';
				}
				if ( '' !== values.border_size_left ) {
					styles += 'border-left-width:' + _.fusionGetValueWithUnit( values.border_size_left ) + ';';
				}

				if ( '' !== values.border_color ) {
					styles += 'border-color: ' + values.border_color + ';';
				}

				styles += '}';

				if ( '' !== values.border_radius ) {
					styles += '.fusion-search-element-' + this.model.get( 'cid' ) + ' .searchform.fusion-search-form-classic .fusion-search-form-content, .fusion-search-form-classic .searchform:not(.fusion-search-form-clean) .fusion-search-form-content {';
					styles += 'border-radius: ' + values.border_radius + ';';
					styles += 'overflow: hidden;';
					styles += '}';
					styles += '.fusion-search-element-' + this.model.get( 'cid' ) + ' .fusion-search-form-content input.s {';
					styles += 'border-radius: ' + values.border_radius + ';';
					styles += '}';
				}

				styles += '</style>';

				return styles;
			},

			buildAttr: function( values ) {
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-search-element fusion-search-element-' + this.model.get( 'cid' ),
						style: ''
					} );

				attr[ 'class' ] += _.fusionGetStickyClass( values.sticky_display );

				if ( values.margin_top ) {
					attr.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( values.margin_bottom ) {
					attr.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( values.margin_right ) {
					attr.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( values.margin_left ) {
					attr.style += 'margin-left:' + values.margin_left + ';';
				}

				if ( values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				attr.id = values.id;

				attr = _.fusionAnimations( values, attr );
				return attr;
			}

		} );
	} );
}( jQuery ) );

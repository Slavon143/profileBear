var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Accordion View.
		FusionPageBuilder.fusion_accordion = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				this.validateValues( atts.values );

				// Create attribute objects.
				attributes.toggleShortcode           = this.buildToggleAttr( atts.values );
				attributes.toggleShortcodePanelGroup = this.buildPanelGroupAttr( atts.values );
				attributes.styles                    = this.buildStyles( atts.values );

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
				values.icon_size       = _.fusionValidateAttrValue( values.icon_size, 'px' );
				values.border_size     = _.fusionValidateAttrValue( values.border_size, 'px' );
				values.title_font_size = _.fusionValidateAttrValue( values.title_font_size, 'px' );
				values.margin_bottom   = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_top      = _.fusionValidateAttrValue( values.margin_top, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildToggleAttr: function( values ) {
				var toggleShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'accordian fusion-accordian',
					style: ''
				} );

				if ( '' !== values.margin_top ) {
					toggleShortcode.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_bottom ) {
					toggleShortcode.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( ' ' !== values[ 'class' ] ) {
					toggleShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					toggleShortcode.id = values.id;
				}

				return toggleShortcode;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildPanelGroupAttr: function( values ) {
				var toggleShortcodePanelGroup = {
					class: 'panel-group fusion-child-element',
					id: 'accordion-cid' + this.model.get( 'cid' )
				};

				if ( 'right' === values.icon_alignment ) {
					toggleShortcodePanelGroup[ 'class' ] += ' fusion-toggle-icon-right';
				}

				if ( '0' === values.icon_boxed_mode || 'no' === values.icon_boxed_mode ) {
					toggleShortcodePanelGroup[ 'class' ] += ' fusion-toggle-icon-unboxed';
				}

				toggleShortcodePanelGroup[ 'data-empty' ] = this.emptyPlaceholderText;

				return toggleShortcodePanelGroup;
			},

			/**
			 * Builds the stylesheet.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {string}
			 */
			buildStyles: function( values ) {
				var styles = '',
					title_styles,
					cid = this.model.get( 'cid' );

				// Title typography.
				styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a {';

				if ( '' !== values.title_font_size ) {
					styles += 'font-size: ' + values.title_font_size + ';';
				}

				if ( ! _.isEmpty( values.title_text_transform ) ) {
					styles += 'text-transform:' + values.title_text_transform + ';';
				}

				if ( ! _.isEmpty( values.title_line_height ) ) {
					styles += 'line-height:' + values.title_line_height + ';';
				}

				if ( ! _.isEmpty( values.title_letter_spacing ) ) {
					styles += 'letter-spacing:' + _.fusionGetValueWithUnit( values.title_letter_spacing ) + ';';
				}

				if ( ! _.isEmpty( values.title_color ) ) {
					styles += 'color:' + values.title_color + ';';
				}

				title_styles = _.fusionGetFontStyle( 'title_font', values, 'object' );
				jQuery.each( title_styles, function( rule, value ) {
					styles += rule + ':' + value + ';';
				} );

				styles += '}';

				// Content typography.
				styles += '.fusion-accordian  #accordion-cid' + cid + ' .toggle-content {';

				if ( '' !== values.content_font_size ) {
					styles += 'font-size: ' + values.content_font_size + ';';
				}

				if ( ! _.isEmpty( values.content_text_transform ) ) {
					styles += 'text-transform:' + values.content_text_transform + ';';
				}

				if ( ! _.isEmpty( values.content_line_height ) ) {
					styles += 'line-height:' + values.content_line_height + ';';
				}

				if ( ! _.isEmpty( values.content_letter_spacing ) ) {
					styles += 'letter-spacing:' + _.fusionGetValueWithUnit( values.content_letter_spacing ) + ';';
				}

				if ( ! _.isEmpty( values.content_color ) ) {
					styles += 'color:' + values.content_color + ';';
				}

				title_styles = _.fusionGetFontStyle( 'content_font', values, 'object' );
				jQuery.each( title_styles, function( rule, value ) {
					styles += rule + ':' + value + ';';
				} );

				styles += '}';

				if ( '' !== values.icon_size ) {
					styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a .fa-fusion-box:before{ font-size: ' + values.icon_size + '; width: ' + values.icon_size + ';}';
				}

				if ( '' !== values.icon_color ) {
					styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a .fa-fusion-box{ color: ' + values.icon_color + ';}';
				}

				if ( 'right' === values.icon_alignment ) {
					styles += '.fusion-accordian  #accordion-cid' + cid + '.fusion-toggle-icon-right .fusion-toggle-heading{ margin-right: ' + ( parseInt( values.icon_size, 10 ) + 18 ) + 'px;}';
				}

				if ( ( '1' === values.icon_boxed_mode || 'yes' === values.icon_boxed_mode ) && ! _.isEmpty( values.icon_box_color ) ) {
					styles += '.fusion-accordian  #accordion-cid' + cid + ' .fa-fusion-box { background-color: ' + values.icon_box_color + ';border-color: ' + values.icon_box_color + ';}';
				}

				if ( ! _.isEmpty( values.toggle_hover_accent_color ) ) {
					styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a:not(.active):hover, #accordion-cid' + cid + ' .fusion-toggle-boxed-mode:hover .panel-title a { color: ' + values.toggle_hover_accent_color + ';}';
					styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a:not(.active).hover, #accordion-cid' + cid + ' .fusion-toggle-boxed-mode.hover .panel-title a { color: ' + values.toggle_hover_accent_color + ';}';

					if ( '1' === values.icon_boxed_mode || 'yes' === values.icon_boxed_mode ) {
						if ( _.isEmpty( values.toggle_active_accent_color ) ) {
							styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title .active .fa-fusion-box,';
						}
						styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a:not(.active):hover .fa-fusion-box { background-color: ' + values.toggle_hover_accent_color + '!important;border-color: ' + values.toggle_hover_accent_color + '!important;}';
						styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a:not(.active).hover .fa-fusion-box { background-color: ' + values.toggle_hover_accent_color + '!important;border-color: ' + values.toggle_hover_accent_color + '!important;}';
					} else {
						styles += '.fusion-accordian  #accordion-cid' + cid + ' .fusion-toggle-boxed-mode:hover .panel-title a .fa-fusion-box{ color: ' + values.toggle_hover_accent_color + ';}';
						styles += '.fusion-accordian  #accordion-cid' + cid + '.fusion-toggle-icon-unboxed .fusion-panel .panel-title a:not(.active):hover .fa-fusion-box{ color: ' + values.toggle_hover_accent_color + ' !important;}';
						styles += '.fusion-accordian  #accordion-cid' + cid + ' .fusion-toggle-boxed-mode.hover .panel-title a .fa-fusion-box{ color: ' + values.toggle_hover_accent_color + ';}';
						styles += '.fusion-accordian  #accordion-cid' + cid + '.fusion-toggle-icon-unboxed .fusion-panel .panel-title a:not(.active).hover .fa-fusion-box{ color: ' + values.toggle_hover_accent_color + ' !important;}';
					}
				}

				if ( '1' == values.boxed_mode || 'yes' === values.boxed_mode ) {

					if ( '' !== values.hover_color ) {
						styles += '#accordion-cid' + cid + ' .fusion-panel:hover, #accordion-cid' + cid + ' .fusion-panel.hover{ background-color: ' + values.hover_color + ' }';
					}

					styles += '#accordion-cid' + cid + ' .fusion-panel {';
					if ( '' !== values.border_color ) {
						styles += ' border-color:' + values.border_color + ';';
					}

					if ( '' !== values.border_size ) {
						styles += ' border-width:' + values.border_size + ';';
					}

					if ( '' !== values.background_color ) {
						styles += ' background-color:' + values.background_color + ';';
					}
					styles += ' }';
				} else if ( '0' !== values.divider_line || 0 !== values.divider_line || 'no' !== values.divider_line ) {
					if ( ! _.isEmpty( values.divider_hover_color ) ) {
						styles += '#accordion-cid' + cid + ' .fusion-panel:hover{ border-color: ' + values.divider_hover_color + ' }';
					}

					styles += ' #accordion-cid' + cid + ' .fusion-panel {';

					if ( ! _.isEmpty( values.divider_color ) ) {
						styles += ' border-color:' + values.divider_color + ';';
					}

					styles += ' }';
				}

				if ( ! _.isEmpty( values.toggle_active_accent_color ) ) {
					styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title a.active{ color: ' + values.toggle_active_accent_color + ' !important;}';

					if ( '1' === values.icon_boxed_mode || 'yes' === values.icon_boxed_mode ) {
						styles += '.fusion-accordian  #accordion-cid' + cid + ' .panel-title .active .fa-fusion-box { background-color: ' + values.toggle_active_accent_color + '!important;border-color: ' + values.toggle_active_accent_color + '!important;}';
					} else {
						styles += '.fusion-accordian  #accordion-cid' + cid + '.fusion-toggle-icon-unboxed .fusion-panel .panel-title a.active .fa-fusion-box{ color: ' + values.toggle_active_accent_color + ' !important;}';
					}
				}

				return styles;
			}
		} );
	} );
}( jQuery ) );

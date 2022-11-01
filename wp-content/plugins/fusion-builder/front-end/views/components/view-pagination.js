var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Pagination view.
		FusionPageBuilder.fusion_tb_pagination = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 3.2
			 * @return {void}
			 */
			afterPatch: function() {
				var $pagination = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.fusion-live-pagination-tb.layout-sticky' ) );

				if ( jQuery( '.fusion-builder-module-settings[data-element-cid="' + this.model.get( 'cid' ) + '"]' ).length ) {
					$pagination.addClass( 'show-live' );
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.2
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {

				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );

				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.styles      = this.buildStyleBlock( atts.values );
				attributes.label       = window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				attributes.icon        = window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;

				// Any extras that need passed on.
				attributes.values = atts.values;

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since  2.2
			 * @param  {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.border_size           = _.fusionValidateAttrValue( values.border_size, 'px' );
				values.height                = _.fusionValidateAttrValue( values.height, 'px' );
				values.preview_height        = _.fusionValidateAttrValue( values.preview_height, 'px' );
				values.preview_wrapper_width = _.fusionValidateAttrValue( values.preview_wrapper_width, 'px' );
				values.preview_width         = _.fusionValidateAttrValue( values.preview_width, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since  2.2
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-live-pagination-tb fusion-pagination-tb fusion-pagination-tb-' + this.model.get( 'cid' ),
						style: ''
					} );

				if ( '' !== values.margin_top ) {
					attr.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_right ) {
					attr.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( '' !== values.margin_bottom ) {
					attr.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( '' !== values.margin_left ) {
					attr.style += 'margin-left:' + values.margin_left + ';';
				}

				if ( '' !== values.height && 'sticky' !== values.layout ) {
					attr.style += 'min-height:' + values.height + ';';
				}

				if ( '' !== values.font_size ) {
					attr.style += 'font-size:' + values.font_size + ';';
				}

				if ( 'sticky' !== values.layout ) {
					attr[ 'class' ] += ' single-navigation clearfix ';
				}

				if ( values.layout ) {
					attr[ 'class' ] += ' layout-' + values.layout;
				}

				if ( values.preview_position && 'preview' === values.layout ) {
					attr[ 'class' ] += ' position-' + values.preview_position;
				}

				if ( 'yes' === values.box_shadow ) {
					attr[ 'class' ] += ' has-box-shadow';
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.alignment && 'sticky' !== values.layout ) {
					attr[ 'class' ] += ' align-' + values.alignment;
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				attr = _.fusionAnimations( values, attr );

				return attr;
			},

			/**
			 * Builds styles.
			 *
			 * @since  2.2
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildStyleBlock: function( values ) {
				var styles = '<style type="text/css">';

				if ( '' !== values.border_size ) {
					styles += '.fusion-pagination-tb-' + this.model.get( 'cid' ) + '.single-navigation:not(.layout-sticky){border-width:' + values.border_size + ';}';

					if ( 'preview' === values.layout ) {
						styles += '.fusion-pagination-tb-' + this.model.get( 'cid' ) + '.single-navigation.layout-preview .fusion-pagination-preview-wrapper{';

						if ( 'top' === values.preview_position ) {
							styles += 'margin-bottom: calc(' + values.border_size + ' + 1px);';
						} else {
							styles += 'margin-top: calc(' + values.border_size + ' + 1px);';
						}

						styles += '}';
					}
				}

				if ( '' !== values.border_color ) {
					styles += '.fusion-pagination-tb-' + this.model.get( 'cid' ) + '.single-navigation:not(.layout-sticky){border-color:' + values.border_color + ';}';
				}

				if ( '' !== values.text_color ) {
					styles += '.fusion-fullwidth .fusion-builder-row.fusion-row .fusion-pagination-tb-' + this.model.get( 'cid' ) + '.single-navigation:not(.layout-sticky) a,';
					styles += '.fusion-fullwidth .fusion-builder-row.fusion-row .fusion-pagination-tb-' + this.model.get( 'cid' ) + '.single-navigation:not(.layout-sticky) a::before,';
					styles += '.fusion-fullwidth .fusion-builder-row.fusion-row .fusion-pagination-tb-' + this.model.get( 'cid' ) + '.single-navigation:not(.layout-sticky) a::after {';
					styles += 'color:' + values.text_color + ';';
					styles += '}';
				}

				if ( '' !== values.text_hover_color ) {
					styles += '.fusion-fullwidth .fusion-builder-row.fusion-row .fusion-pagination-tb-' + this.model.get( 'cid' ) + '.single-navigation:not(.layout-sticky) a:hover,';
					styles += '.fusion-fullwidth .fusion-builder-row.fusion-row .fusion-pagination-tb-' + this.model.get( 'cid' ) + '.single-navigation:not(.layout-sticky) a:hover::before,';
					styles += '.fusion-fullwidth .fusion-builder-row.fusion-row .fusion-pagination-tb-' + this.model.get( 'cid' ) + '.single-navigation:not(.layout-sticky) a:hover::after {';
					styles += 'color:' + values.text_hover_color + ';';
					styles += '}';
				}

				if ( '' !== values.bg_color && 'text' !== values.layout ) {
					styles += '.fusion-body .fusion-pagination-tb-' + this.model.get( 'cid' ) + '.layout-sticky .fusion-control-navigation,';
					styles += '.fusion-body .fusion-pagination-tb-' + this.model.get( 'cid' ) + ':not(.layout-sticky).layout-preview .fusion-pagination-preview-wrapper{';
					styles += 'background:' + values.bg_color + ';}';
				}

				if ( 'yes' === values.box_shadow && 'text' !== values.layout ) {
					styles += '.fusion-body .fusion-pagination-tb-' + this.model.get( 'cid' ) + '.layout-sticky.has-box-shadow .fusion-control-navigation:before,';
					styles += '.fusion-body .fusion-pagination-tb-' + this.model.get( 'cid' ) + ':not(.layout-sticky).layout-preview.has-box-shadow .fusion-pagination-preview-wrapper{';
					styles += 'box-shadow:' + _.fusionGetBoxShadowStyle( values ) + ' !important;}';
				}

				styles += '.fusion-fullwidth .fusion-builder-row.fusion-row .fusion-pagination-tb-' + this.model.get( 'cid' ) + '.layout-sticky .fusion-control-navigation a,';
				styles += '.fusion-fullwidth .fusion-builder-row.fusion-row .fusion-pagination-tb-' + this.model.get( 'cid' ) + ':not(.layout-sticky).layout-preview .fusion-pagination-preview-wrapper .fusion-item-title {';

				if ( '' !== values.preview_text_color && 'text' !== values.layout ) {
					styles += 'color:' + values.preview_text_color + ';';
				}

				if ( '' !== values.preview_font_size && 'text' !== values.layout ) {
					styles += 'font-size:' + values.preview_font_size + ';';
				}

				styles += '}';

				if ( '' !== values.preview_height && 'sticky' === values.layout ) {
					styles += '.fusion-body .fusion-pagination-tb-' + this.model.get( 'cid' ) + '.layout-sticky .fusion-control-navigation{';
					styles += 'height:' + values.preview_height + ';';
					styles += '}';
				}

				if ( '' !== values.preview_wrapper_width && 'sticky' === values.layout ) {
					styles += '.fusion-body .fusion-pagination-tb-' + this.model.get( 'cid' ) + '.layout-sticky .fusion-control-navigation{';
					styles += 'min-width:' + values.preview_wrapper_width + ';';
					styles += '}';
				}

				if ( '' !== values.preview_width && 'sticky' === values.layout ) {
					styles += '.fusion-body .fusion-pagination-tb-' + this.model.get( 'cid' ) + '.layout-sticky .fusion-control-navigation.next{';
					if ( jQuery( 'body' ).hasClass( 'rtl' ) ) {
						styles += 'transform:translate( calc( max( -' + values.preview_wrapper_width + ', -50vw ) + ' + values.preview_width + '), -50% ) !important;';
					} else {
						styles += 'transform:translate( calc( min( ' + values.preview_wrapper_width + ', 50vw ) - ' + values.preview_width + '), -50% );';
					}
					styles += '}';
					styles += '.fusion-body .fusion-pagination-tb-' + this.model.get( 'cid' ) + '.layout-sticky .fusion-control-navigation.prev{';
					if ( jQuery( 'body' ).hasClass( 'rtl' ) ) {
						styles += 'transform:translate( calc( min( ' + values.preview_wrapper_width + ', 50vw ) - ' + values.preview_width + '), -50% ) !important;';
					} else {
						styles += 'transform:translate( calc( max( -' + values.preview_wrapper_width + ', -50vw ) + ' + values.preview_width + '), -50% );';
					}
					styles += '}';
				}

				if ( '' !== values.z_index && 'sticky' === values.layout ) {
					styles += '.fusion-body .fusion-pagination-tb-' + this.model.get( 'cid' ) + '.layout-sticky{';
					styles += 'z-index:' + parseInt( values.z_index ) + ';';
					styles += '}';
				}

				styles += '</style>';

				return styles;
			},

			/**
			 * Open actual modal.
			 *
			 * @since 2.0
			 * @return {void}
			 */

			onSettingsOpen: function() {
				var $pagination = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.fusion-live-pagination-tb' ) );

				if ( $pagination.hasClass( 'layout-sticky' ) ) {
					$pagination.addClass( 'show-live' );
				}
			},

			/**
			 * Close the modal.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onSettingsClose: function() {
				var $pagination = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.fusion-live-pagination-tb' ) );
				if ( $pagination.hasClass( 'layout-sticky' ) ) {
					$pagination.removeClass( 'show-live' );
				}
			}
		} );
	} );
}( jQuery ) );

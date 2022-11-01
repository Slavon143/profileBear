/* global FusionEvents */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Image Before After Element View.
		FusionPageBuilder.fusion_image_before_after = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs when element is first init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			onInit: function() {
				this.listenTo( FusionEvents, 'fusion-preview-toggle', this.previewToggle );
				this.listenTo( FusionEvents, 'fusion-iframe-loaded', this.initElement );
			},

			/**
			 * Init Element.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initElement: function() {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-element-render-fusion_image_before_after', this.model.attributes.cid );
			},

			/**
			 * Preview mode toggled.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			previewToggle: function() {
				if ( jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).hasClass( 'fusion-builder-preview-mode' ) ) {
					this.disableDroppableElement();
				} else {
					this.enableDroppableElement();
				}
			},

			/**
			 * Runs before view DOM is patched.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			beforePatch: function() {
				this.$el.css( 'min-height', this.$el.outerHeight() + 'px' );
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var self = this;

				this._refreshJs();

				setTimeout( function() {
					self.$el.css( 'min-height', '0px' );
				}, 300 );
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

				if ( '' !== atts.values.before_image || '' !== atts.values.after_image ) {

					// Validate values.
					this.validateValues( atts.values );

					// Create attribute objects
					attributes.attr            = this.buildAttr( atts.values );
					attributes.attrWrapper     = this.buildWrapperAttr( atts.values );
					attributes.attrLink        = this.buildLinkAttr( atts.values );
					attributes.attrBeforeImage = this.buildBeforeImageAttr( atts.values );
					attributes.attrAfterImage  = this.buildAfterImageAttr( atts.values );
					attributes.attrOverlay     = this.buildOverlayAttr( atts.values );
					attributes.attrHandle      = this.buildHandleAttr( atts.values );
					attributes.styles          = this.buildStyles( atts.values );

					// Any extras that need passed on.
					attributes.values = atts.values;
				}

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

				values.offset        = parseInt( values.offset, 10 ) / 100;
				values.font_size     = _.fusionValidateAttrValue( values.font_size, 'px' );
				values.borderradius  = _.fusionValidateAttrValue( values.borderradius, 'px' );
				values.bordersize    = _.fusionValidateAttrValue( values.bordersize, 'px' );
				values.max_width     = _.fusionValidateAttrValue( values.max_width, 'px' );
				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = {
						class: 'fusion-image-before-after-element',
						style: ''
					},
					cid = this.model.get( 'cid' );

				if ( 'switch' === values.type ) {
					attr[ 'class' ] += ' fusion-image-switch';
				} else if ( 'before_after' === values.type ) {
					attr[ 'class' ] += ' fusion-image-before-after fusion-image-before-after-container';

					if ( values.offset || 0 == values.offset ) {
						attr[ 'data-offset' ] = values.offset.toString();
					}

					if ( values.orientation ) {
						attr[ 'data-orientation' ] = values.orientation;
					}

					if ( values.handle_movement ) {
						if ( 'drag_click' === values.handle_movement ) {
							attr[ 'data-move-with-handle-only' ] = 'true';
							attr[ 'data-click-to-move' ]         = 'true';
						} else if ( 'drag' === values.handle_movement ) {
							attr[ 'data-move-with-handle-only' ] = 'true';
						} else if ( 'hover' === values.handle_movement ) {
							attr[ 'data-move-slider-on-hover' ] = 'true';
						}
					}
				}

				if ( '' !== values.max_width ) {
					attr.style += 'max-width:' + values.max_width + ';';
				}

				attr[ 'class' ] += ' fusion-image-before-after-cid' + cid;

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildWrapperAttr: function( values ) {
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-image-before-after-wrapper',
						style: ''
					} ),
					cid = this.model.get( 'cid' );

				if ( values.orientation && 'before_after' === values.type ) {
					attr[ 'class' ] += ' fusion-image-before-after-' + values.orientation;
				}

				if ( '' !== values.type ) {
					attr[ 'class' ] += ' type-' + values.type;
				}

				if ( '' !== values.alignment ) {
					attr[ 'class' ] += ' has-alignment';
					attr[ 'class' ] += ' align-' + values.alignment;
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

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

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				attr = _.fusionAnimations( values, attr );

				attr[ 'class' ] += ' fusion-image-before-after-wrapper-cid' + cid;

				return attr;
			},

			/**
			 * Builds link attributes.
			 *
			 * @since 2.2
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildLinkAttr: function( values ) {
				var attr = {
						class: 'fusion-image-switch-link',
						href: values.link,
						target: values.target,
						rel: ''
					};

					if ( '_blank' === values.target ) {
						attr.rel = 'noopener noreferrer';
					}

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildBeforeImageAttr: function( values ) {
				var attr = {
					class: 'before_after' === values.type ? 'fusion-image-before-after-before' : 'fusion-image-switch-before',
					src: values.before_image,
					alt: ''
				};

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAfterImageAttr: function( values ) {
				var attr = {
					class: 'before_after' === values.type ? 'fusion-image-before-after-after' : 'fusion-image-switch-after',
					src: values.after_image,
					alt: ''
				};

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildOverlayAttr: function( values ) {
				var attr = {
					class: 'fusion-image-before-after-overlay'
				};

				if ( values.label_placement && '' !== values.label_placement ) {
					attr[ 'class' ] += ' before-after-overlay-' + values.label_placement;
				}

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildHandleAttr: function( values ) {
				var attr = {
					class: 'fusion-image-before-after-handle'
				};

				if ( values.handle_type && 'default' !== values.handle_type ) {
					attr[ 'class' ] += ' fusion-image-before-after-handle-' + values.handle_type;
				}

				return attr;
			},

			/**
			 * Builds the styles.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {string}
			 */
			buildStyles: function( values ) {
				var styles   = '',
					color    = '',
					colorObj = '',
					addBorderSize,
					addBorderRadius,
					bgColor  = '',
					cid      = this.model.get( 'cid' );

				if ( '' !== values.handle_color && 'before_after' === values.type ) {
					color   = values.handle_color;
					styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-handle {';
					styles += 'border-color:' + color + ';';
					styles += '}';
					if ( 'horizontal' === values.orientation ) {
						styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-left-arrow {';
						styles += 'border-right-color:' + color + ';';
						styles += '}';
						styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-right-arrow {';
						styles += 'border-left-color:' + color + ';';
						styles += '}';

						if ( values.handle_type && '' !== values.handle_type && 'diamond' === values.handle_type ) {
							styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-handle-diamond .fusion-image-before-after-left-arrow::before {';
							styles += 'border-color:' + color + ' !important;';
							styles += '}';
						} else if ( values.handle_type && '' !== values.handle_type && 'circle' === values.handle_type ) {
							colorObj = jQuery.AWB_Color( color );

							styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-handle-circle {';
							styles += 'background:' + color + ' !important;';
							styles += '}';
							styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-handle-circle .fusion-image-before-after-left-arrow::before {';
							styles += 'border-color:' + colorObj.alpha( 0.6 ).toVarOrRgbaString() + ' !important;';
							styles += '}';
							styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-handle-circle .fusion-image-before-after-left-arrow {';
							styles += 'border-right-color:' + _.fusionAutoCalculateAccentColor( color ) + ' !important;';
							styles += '}';
							styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-handle-circle .fusion-image-before-after-right-arrow {';
							styles += 'border-left-color:' + _.fusionAutoCalculateAccentColor( color ) + ' !important;';
							styles += '}';
						}
					} else if ( 'vertical' === values.orientation ) {
						styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-up-arrow {';
						styles += 'border-bottom-color:' + color + ';';
						styles += '}';
						styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-down-arrow {';
						styles += 'border-top-color:' + color + ';';
						styles += '}';

						if ( values.handle_type && '' !== values.handle_type && 'diamond' === values.handle_type ) {
							styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-handle-diamond .fusion-image-before-after-left-arrow::before {';
							styles += 'border-color:' + color + ' !important;';
							styles += '}';
						} else if ( values.handle_type && '' !== values.handle_type && 'circle' === values.handle_type ) {
							colorObj = jQuery.AWB_Color( color );

							styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-handle-circle {';
							styles += 'background:' + color + ' !important;';
							styles += '}';
							styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-handle-circle .fusion-image-before-after-down-arrow::before {';
							styles += 'border-color:' + colorObj.alpha( 0.6 ).toVarOrRgbaString() + ' !important;';
							styles += '}';
							styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-handle-circle .fusion-image-before-after-up-arrow {';
							styles += 'border-bottom-color:' + _.fusionAutoCalculateAccentColor( color ) + ' !important;';
							styles += '}';
							styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-handle-circle .fusion-image-before-after-down-arrow {';
							styles += 'border-top-color:' + _.fusionAutoCalculateAccentColor( color ) + ' !important;';
							styles += '}';
						}
					}
					styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-handle::after {';
					styles += 'background:' + color + ';';
					if ( 'vertical' !== values.orientation ) {
						styles += 'box-shadow: 0 3px 0 ' + color + ', 0 0 12px rgba(51,51,51,.5);';
					}
					styles += '}';
					styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-handle::before {';
					styles += 'background:' + color + ';';
					if ( 'vertical' !== values.orientation ) {
						styles += 'box-shadow: 0 3px 0 ' + color + ', 0 0 12px rgba(51,51,51,.5);';
					}
					styles += '}';
				}

				if ( values.handle_bg && '' !== values.handle_bg && 'before_after' === values.type ) {
					bgColor = values.handle_bg;
					if ( 'circle' !== values.handle_type && 'arrows' !== values.handle_type ) {
						if ( 'diamond' !== values.handle_type ) {
							styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-handle {';
							styles += 'background:' + bgColor + ';';
							styles += '}';
						} else {
							styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-down-arrow:before,';
							styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-left-arrow:before {';
							styles += 'background:' + bgColor + ';';
							styles += '}';
						}
					}
				}

				if ( values.font_size && '' !== values.font_size && 'before_after' === values.type ) {
					styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-before-label:before';
					styles += ',.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-after-label:before';
					if ( 'out-image-up-down' === values.label_placement ) {
						styles += ',.fusion-image-before-after-wrapper-cid' + cid + ' .fusion-image-before-after-before-label:before';
						styles += ',.fusion-image-before-after-wrapper-cid' + cid + ' .fusion-image-before-after-after-label:before';
					}
					styles += '{';
					styles += 'font-size:' + values.font_size + ';';
					styles += '}';
				}

				if ( values.accent_color && '' !== values.accent_color && 'before_after' === values.type ) {

					color     = values.accent_color;
					colorObj = jQuery.AWB_Color( color );
					styles += '.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-before-label:before';
					styles += ',.fusion-image-before-after-cid' + cid + ' .fusion-image-before-after-after-label:before';
					if ( 'out-image-up-down' === values.label_placement ) {
						styles += ',.fusion-image-before-after-wrapper-cid' + cid + ' .fusion-image-before-after-before-label:before';
						styles += ',.fusion-image-before-after-wrapper-cid' + cid + ' .fusion-image-before-after-after-label:before';
					}
					styles += '{';
					styles += 'color:' + color + ';';
					if ( 'out-image-up-down' !== values.label_placement ) {
						styles += 'background:' + colorObj.alpha( 0.15 ).toVarOrRgbaString() + ';';
					}
					styles += '}';
				}

				if ( 'switch' === values.type && values.transition_time ) {
					styles += '.fusion-image-switch.fusion-image-before-after-cid' + cid + ' img{';
					styles += 'transition: ' + values.transition_time + 's ease-in-out;';
					styles += '}';

					if ( -1 !== values.before_image.indexOf( '.png' ) && -1 !== values.after_image.indexOf( '.png' )  ) {
						styles += '.fusion-image-switch.fusion-image-before-after-cid' + cid + ':hover img:first-child{';
						styles += 'opacity: 1;';
						styles += '}';
					}
				}

				addBorderSize = ( '0' !== values.bordersize && 0 !== values.bordersize && '0px' !== values.bordersize );
				addBorderRadius = ( '0' !== values.borderradius && 0 !== values.borderradius && '0px' !== values.borderradius );
				if ( addBorderSize || addBorderRadius ) {
					styles += '.fusion-image-before-after-cid' + cid + ':not(.fusion-image-switch).initialized,';
					styles += '.fusion-image-before-after-cid' + cid + '.fusion-image-switch img{';

					if ( addBorderSize ) {
						styles += 'border: ' + values.bordersize + ' solid ' + values.bordercolor + ';';
					}

					if ( addBorderRadius ) {
						styles += 'border-radius:' + values.borderradius + ';';
					}

					styles += '}';
				}

				return styles;
			}
		} );
	} );
}( jQuery ) );

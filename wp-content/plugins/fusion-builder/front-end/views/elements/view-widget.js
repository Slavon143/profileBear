/* global FusionEvents, fusionBuilderText */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		FusionPageBuilder.fusion_widget = FusionPageBuilder.ElementView.extend( {

			onInit: function() {
				this.contentView = false;
				this.listenTo( FusionEvents, 'fusion-widget-rendered', this.removeLoadingOverlay );
				this.deprecatedParams();

			},

			sanitizeValue: function( param, value ) {
				// HTML and Text widget especial escape.
				if ( 'wp_widget_custom_html__content' === param || 'wp_widget_text__text' === param ) {
					return _.escape( value );
				}
				return value;
			},

			onRender: function() {
				this.renderWidgetContent();
			},

			/**
			 * Removes loading overlay
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			removeLoadingOverlay: function() {
				var contentType = 'element',
					$elementContent;

				if ( _.isObject( this.model.attributes ) ) {
					if ( 'fusion_builder_container' === this.model.attributes.element_type ) {
						contentType = 'container';
					} else if ( 'fusion_builder_column' === this.model.attributes.element_type ) {
						contentType = 'columns';
					}
				}

				$elementContent = this.$el.find( '.fusion-builder-' + contentType + '-content' );

				$elementContent.removeClass( 'fusion-loader' );
				$elementContent.find( '.fusion-builder-loader' ).remove();
			},

			beforeRemove: function() {
				if ( this.contentView ) {
					this.contentView.removeElement();
				}
			},

			renderWidgetContent: function() {
				var view,
					viewSettings = {
						model: this.model
					},
					widgetType = this.model.attributes.params.type;

				if ( ! this.model.get( 'params' ).type ) {
					return;
				}
				if ( this.contentView ) {
					this.$el.find( '.fusion-widget-content' ).html( this.contentView.render().el );

				} else {
					if ( 'undefined' !== typeof FusionPageBuilder[ widgetType ] ) {
						view = new FusionPageBuilder[ widgetType ]( viewSettings );
					} else {
						view = new FusionPageBuilder.fusion_widget_content( viewSettings );
					}

					this.contentView = view;

					this.$el.find( '.fusion-widget-content' ).html( view.render().el );
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );

				// Create attribute objects
				attributes.attr   	= this.buildAttr( atts.values );
				attributes.styles 	= this.buildStyles( atts.values );

				// Any extras that need passed on.
				attributes.cid    = this.model.get( 'cid' );
				attributes.values = atts.values;
				attributes.placeholder = this.getWidgetPlaceholder();

				return attributes;
			},

			/**
			 * Modify the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );
			},

			/**
			 * Get widget placeholder.
			 *
			 * @since 2.0.0
			 * @return {string}
			 */
			getWidgetPlaceholder: function() {
				var placeholder = jQuery( this.getPlaceholder() ).append( '<span class="fusion-tb-source-separator"> - </span><br/><span>' + fusionBuilderText.select_widget + '</span>' );
				return placeholder[ 0 ].outerHTML;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-widget fusion-widget-element fusion-widget-area fusion-content-widget-area widget fusion-live-widget fusion-widget-cid' + this.model.get( 'cid' ),
						style: ''
					} );

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				if ( values.fusion_align ) {
					attr[ 'class' ] += ' fusion-widget-align-' + values.fusion_align;
				}

				if ( values.fusion_align_mobile ) {
					attr[ 'class' ] += ' fusion-widget-mobile-align-' + values.fusion_align_mobile;
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

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildStyles: function( values ) {
				var styles       = '',
					cid          = this.model.get( 'cid' );

				styles = '<style type="text/css">';
				styles += '.fusion-widget.fusion-widget-cid' + cid + '{';
				styles += 'background-color:' + values.fusion_bg_color + ';';

				if ( 'undefined' !== typeof values.fusion_padding_color ) {
					styles += 'padding:' + _.fusionCheckValue( values.fusion_padding_color ) + ';';
				}
				if ( 'undefined' !== typeof values.fusion_bg_radius_size ) {
					styles += 'border-radius:' + _.fusionCheckValue( values.fusion_bg_radius_size ) + ';';
				}
				if ( 'undefined' !== typeof values.fusion_margin ) {
					styles += 'margin:' + _.fusionCheckValue( values.fusion_margin ) + ';';
				}
				styles += 'border-color:' + values.fusion_border_color + ';';
				styles += 'border-width:' + _.fusionValidateAttrValue( values.fusion_border_size, 'px' ) + ';';

				if ( '' !== values.fusion_border_size ) {
					styles += 'border-style:' + values.fusion_border_style + ';';
				}

				styles += '}';

				if ( 'no' === values.fusion_display_title ) {
					styles += '.fusion-widget.fusion-widget-cid' + cid + ' .widget-title, .fusion-widget.fusion-widget-cid' + cid + ' .tribe-events-widget-events-list__header{display:none;}';
				}


				if ( 'undefined' !== typeof values.fusion_divider_color && '' !== values.fusion_divider_color ) {
					styles += '#wrapper .fusion-widget.fusion-widget-cid' + cid + ' li { border-color:' + values.fusion_divider_color + ';}';

					if ( 'WP_Widget_Tag_Cloud' === values.type ) {
						styles += '#wrapper .fusion-widget.fusion-widget-cid' + cid + ' .tagcloud a { border-color:' + values.fusion_divider_color + ';}';
					} else if ( 'Fusion_Widget_Menu' === values.type ) {
						styles += '#wrapper .fusion-widget.fusion-widget-cid' + cid + ' .fusion-widget-menu ul li a:after { color:' + values.fusion_divider_color + ';}';
					}
				}

				// Special handling for vertical menu widget, to take into account the deprecated border_color option.
				if ( 'Fusion_Widget_Vertical_Menu' === values.type ) {
					if ( 'undefined' !== typeof values.border_color && 'undefined' === typeof values.fusion_divider_color ) {
						values.fusion_divider_color = values.border_color;
					}

					if ( '' !== values.fusion_divider_color ) {
						styles += '#wrapper .fusion-widget.fusion-widget-cid' + cid + ' .menu { border-right-color:' + values.fusion_divider_color + ' !important; border-top-color:' + values.fusion_divider_color + ' !important;}';
						styles += '#wrapper .fusion-widget.fusion-widget-cid' + cid + ' .menu li a { border-bottom-color:' + values.fusion_divider_color + ' !important; }';
						styles += '#wrapper .fusion-widget.fusion-widget-cid' + cid + ' .right .menu { border-left-color:' + values.fusion_divider_color + ' !important; }';
					} else {
						styles += '#wrapper .fusion-widget.fusion-widget-cid' + cid + ' > ul.menu { margin-top: -8px; }';
					}
				}

				styles += '</style>';

				return styles;
			},

			/**
			 * Filter out DOM before patching.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			patcherFilter: function( diff ) {
				var filteredDiff = [];

				_.each( diff, function( info ) {
					if ( 'replaceElement' === info.action ) {

						if ( 'undefined' !== typeof info.oldValue.attributes && -1 !== info.oldValue.attributes[ 'class' ].indexOf( 'fusion-widget-content-view' ) ) {

							// Ignore.
						} else {
							filteredDiff.push( info );
						}
					} else if ( 'addElement' === info.action ) {
						if ( -1 !== info.element.attributes[ 'class' ].indexOf( 'fusion-widget-content-view' ) || -1 !== info.element.attributes[ 'class' ].indexOf( 'fusion-widget' ) ) {

							// Ignore.
						} else {
							filteredDiff.push( info );
						}
					} else if ( 'removeElement' === info.action ) {
						if ( -1 !== info.element.attributes[ 'class' ].indexOf( 'fusion-widget-content' ) || -1 !== info.element.attributes[ 'class' ].indexOf( 'fusion-widget' ) ) {

							// Ignore.
						} else {
							filteredDiff.push( info );
						}
					} else {
						filteredDiff.push( info );
					}
				} );

				return filteredDiff;
			},

			deprecatedParams: function() {
				var params = this.model.get( 'params' );

				// Ensures backwards compatibility for the border_color option of the vertical menu.
				if ( 'Fusion_Widget_Vertical_Menu' === params.type && 'undefined' === typeof params.fusion_divider_color ) {
					params.fusion_divider_color = params.fusion_widget_vertical_menu__border_color;
					delete params.fusion_widget_vertical_menu__border_color;
				}

				this.model.set( 'params', params );
			}

		} );

		_.extend( FusionPageBuilder.Callback.prototype, {
			fusion_get_widget_markup: function( name, value, modelData, args, cid, action, model, view ) {
				view.changeParam( name, value );
				view.contentView.getHTML( view );
			},

			fusion_widget_changed: function( name, value, args, view ) {
				view.changeParam( name, value );
				view.model.attributes.markup = '';
				FusionEvents.trigger( 'fusion-widget-changed' );
				view.render();
				view.addLoadingOverlay();
				// prevent another re-render
				return {
					render: false
				};
			}
		} );

	} );

}() );

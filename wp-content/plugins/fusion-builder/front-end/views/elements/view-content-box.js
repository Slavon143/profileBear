var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Content Boxes Child View.
		FusionPageBuilder.fusion_content_box = FusionPageBuilder.ChildElementView.extend( {

			resetTypography: function() {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-typography-reset', this.model.get( 'cid' ) );

				if ( 800 > jQuery( '#fb-preview' ).width() ) {
					setTimeout( function() {
						jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'resize' );
					}, 50 );
				}
			},

			/**
			 * Runs during render() call.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				var parentView,
					queryData = this.model.get( 'query_data' );

				// Update the parent image map with latest query data images.
				if ( 'undefined' !== typeof queryData ) {
					parentView = window.FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
					parentView.updateImageMap( queryData );
				}

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var parentView,
					queryData = this.model.get( 'query_data' );

				// Update the parent image map with latest query data images.
				if ( 'undefined' !== typeof queryData ) {
					parentView = window.FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
					parentView.updateImageMap( queryData );
				}

				this.resetTypography();

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}

				// Using non debounced version for smoothness.
				this.refreshJs();
			},

			onCancel: function() {
				this.resetTypography();
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

				// Validate values.
				this.validateValues( atts );
				this.extras = atts.extras;

				// Create attribute objects.
				this.buildAttr( atts );

				attributes.attrShortcodeIcon        = this.buildShortcodeIconAttr( atts );
				attributes.attrShortcodeIconParent  = this.buildShortcodeIconParentAttr( atts );
				attributes.attrShortcodeIconWrapper = this.buildShortcodeIconWrapperAttr( atts );
				attributes.attrContentBoxHeading    = this.buildContentBoxHeadingAttr( atts );
				attributes.attrHeadingWrapper       = this.buildContentBoxHeadingWrapperAttr( atts );
				attributes.attrContentContainer     = this.buildContentContainerAttr( atts );
				attributes.attrShortcodeTimeline    = this.buildShortcodeTimelineAttr( atts );
				attributes.attrContentWrapper       = this.buildContentWrapperAttr( atts );

				attributes.attrHeadingLink          = this.contentBoxShortcodeAttrs( false, 'heading-link', atts );
				attributes.attrReadMore             = this.contentBoxShortcodeAttrs( true, 'fusion-read-more', atts );
				attributes.attrButton               = this.contentBoxShortcodeAttrs( true, false, atts );

				// Build styles.
				attributes.styles = this.buildStyles( atts );

				// Any extras that need passed on.
				attributes.cid          = this.model.get( 'cid' );
				attributes.output       = atts.values.element_content;
				attributes.parentCid    = atts.parent;
				attributes.values       = atts.values;
				attributes.parentValues = atts.parentValues;

				return attributes;
			},

			/**
			 * Modify values.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {void}
			 */
			validateValues: function( atts ) {
				var values       = atts.values,
					parentValues = atts.parentValues,
					params       = this.model.get( 'params' ),
					queryData    = atts.query_data,
					parentView   = window.FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) ),
					imageData    = false;

				// Case when image is set on parent element and icon on child element.
				if ( ( 'undefined' === typeof params.image || '' === params.image ) && ( 'undefined' !== typeof params.icon && '' !== params.icon ) ) {
					values.image = '';
				}

				// Backwards compatibility for when we had image width and height params.
				if ( 'undefined' !== typeof params.image_width && params.image_width ) {
					values.image_width = params.image_width;
				} else {
					values.image_width = values.image_max_width;
				}

				values.image_width  = _.fusionValidateAttrValue( values.image_width, '' );

				if ( 'undefined' !== typeof values.image && ( '' !== values.image || '' !== values.image_id ) ) {

					if ( 'undefined' !== typeof queryData && 'undefined' !== typeof queryData[ values.image ] ) {
						imageData = queryData[ values.image ];
					} else if ( 'undefined' !== typeof queryData && 'undefined' !== typeof queryData[ values.image_id ] ) {
						imageData = queryData[ values.image_id ];
					} else if ( 'undefined' !== typeof parentView.imageMap[ values.image ] ) {
						imageData = parentView.imageMap[ values.image ];
					} else if ( 'undefined' !== typeof parentView.imageMap[ values.image_id ] ) {
						imageData = parentView.imageMap[ values.image_id ];
					}

					if ( imageData ) {
						if ( -1 === parseInt( values.image_width ) || '' === values.image_width ) {
							values.image_width = 'undefined' !== typeof imageData.width ? imageData.width : '35';
						}

						values.image_height = 'undefined' !== typeof imageData.width ? Math.round( ( parseFloat( values.image_width ) / parseFloat( imageData.width ) ) * parseFloat( imageData.height ) * 100 ) / 100 : values.image_width;
					} else {
						if ( -1 === parseInt( values.image_width ) ) {
							values.image_width = '35';
						}

						values.image_height = values.image_width;
					}

				} else {
					values.image_width  = '' === values.image_width ? '35' : values.image_width;
					values.image_height = '35';
				}

				if ( values.linktarget ) {
					values.link_target = values.linktarget;
				}

				if ( 'parent' === parentValues.settings_lvl ) {
					values.backgroundcolor        = parentValues.backgroundcolor;
					values.circlecolor            = parentValues.circlecolor;
					values.circlebordercolor      = parentValues.circlebordercolor;
					values.circlebordersize       = parentValues.circlebordersize;
					values.outercirclebordercolor = parentValues.outercirclebordercolor;
					values.outercirclebordersize  = parentValues.outercirclebordersize;
					values.iconcolor              = parentValues.iconcolor;
					values.animation_type         = parentValues.animation_type;
					values.animation_direction    = parentValues.animation_direction;
					values.animation_speed        = parentValues.animation_speed;
					values.link_target            = parentValues.link_target;
				}

				if ( 'timeline-vertical' === parentValues.layout ) {
					parentValues.columns       = 1;
				}

				if ( 'timeline-vertical' === parentValues.layout || 'timeline-horizontal' === parentValues.layout ) {
					values.animation_speed     = 0.25;
					values.animation_type      = 'fade';
					values.animation_direction = '';
				}

				values.circlebordersize = _.fusionValidateAttrValue( values.circlebordersize, 'px' );
			},

			/**
			 * Set attributes in the model.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {void}
			 */
			buildAttr: function( atts ) {
				var attr              = {
						class: 'fusion-column content-box-column',
						style: ''
					},
					cid               = this.model.get( 'cid' ),
					values            = atts.values,
					parentValues      = atts.parentValues,
					rowCounter        = 1,
					numOfColumns      = parentValues.columns,
					columns           = '',
					borderColor       = '';

				if ( '' === numOfColumns || '0' === numOfColumns ) {
					numOfColumns = 1;
				} else if ( 6 < numOfColumns ) {
					numOfColumns = 6;
				}

				numOfColumns = parseInt( numOfColumns, 10 );

				columns = 12 / numOfColumns;

				if ( rowCounter > numOfColumns ) {
					rowCounter = 1;
				}

				attr[ 'class' ] += ' content-box-column content-box-column-cid-' + cid;
				attr[ 'class' ] += ' col-lg-' + columns;
				attr[ 'class' ] += ' col-md-' + columns;
				attr[ 'class' ] += ' col-sm-' + columns;

				if ( 5 === numOfColumns ) {
					attr[ 'class' ] = 'fusion-column content-box-column content-box-column-cid-' + cid + ' col-lg-2 col-md-2 col-sm-2';
				}

				attr[ 'class' ] += ' fusion-content-box-hover ';

				if ( 'timeline-vertical' === parentValues.layout || 'timeline-horizontal' === parentValues.layout ) {
					attr[ 'class' ] += ' fusion-appear';
				}

				if ( values.circlebordercolor ) {
					borderColor = values.circlebordercolor;
				}

				if ( values.outercirclebordercolor ) {
					borderColor = values.outercirclebordercolor;
				}

				if ( ! values.circlebordercolor && ! values.outercirclebordercolor ) {
					borderColor = '#f6f6f6';
				}

				if ( 1 === parseFloat( cid ) / parseFloat( numOfColumns ) ) {
					attr[ 'class' ] += ' content-box-column-first-in-row';
				}

				if ( atts.last ) {
					attr[ 'class' ] += ' content-box-column-last';
				}

				if ( parseFloat( cid ) === parseFloat( numOfColumns ) ) {
					attr[ 'class' ] += ' content-box-column-last-in-row';
				}

				if ( borderColor && -1 !== jQuery.inArray( parentValues.layout, [ 'clean-vertical', 'clean-horizontal' ] ) ) {
					attr.style += 'border-color:' + borderColor + ';';
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				this.model.set( 'selectors', attr );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			buildShortcodeIconAttr: function( atts ) {
				var attr         = {
						class: '',
						style: '',
						'aria-hidden': 'true'
					},
					values       = atts.values,
					parentValues = atts.parentValues;

				if ( values.image ) {
					attr[ 'class' ] = 'image';

					if ( 'icon-boxed' === parentValues.layout && values.image_width && values.image_height ) {
						attr.style  = 'margin-left:-' + ( parseFloat( values.image_width ) / 2 ) + 'px;';
						attr.style += 'top:-' + ( ( parseFloat( values.image_height ) / 2 ) + 50 ) + 'px;';
					}
				} else if ( values.icon ) {

					attr[ 'class' ] = 'fontawesome-icon ' + _.fusionFontAwesome( values.icon );

					// Set parent values if child values are unset to get downwards compatibility.
					if ( ! values.circle ) {
						values.circle = parentValues.circle;
					}

					if ( 'yes' === parentValues.icon_circle ) {

						attr[ 'class' ] += ' circle-yes';

						if ( values.circlebordercolor ) {
							attr.style += 'border-color:' + values.circlebordercolor + ';';
						}

						if ( values.circlebordersize ) {
							attr.style += 'border-width:' + values.circlebordersize + ';';
						}

						if ( values.circlecolor ) {
							attr.style += 'background-color:' + values.circlecolor + ';';
						}

						attr.style += 'height:' + ( parseFloat( parentValues.icon_size ) * 2 ) + 'px;width:' + ( parseFloat( parentValues.icon_size ) * 2 ) + 'px;line-height:' + ( parseFloat( parentValues.icon_size ) * 2 ) + 'px;';

						if ( 'icon-boxed' === parentValues.layout && ( '' === values.outercirclebordercolor || '' === values.outercirclebordersize || '' !== parseFloat( values.outercirclebordersize ) ) ) {
							attr.style += 'top:-' + ( 50 + parseFloat( parentValues.icon_size ) ) + 'px;margin-left:-' + parseFloat( parentValues.icon_size ) + 'px;';
						}

						if ( 'round' === parentValues.icon_circle_radius ) {
							parentValues.icon_circle_radius = '100%';
						}

						attr.style += 'border-radius:' + parentValues.icon_circle_radius + ';';

						if ( values.outercirclebordercolor && values.outercirclebordersize && 0 !== parseFloat( values.outercirclebordersize ) ) {

							// If there is a thick border, kill border width and make it center aligned positioned.
							attr.style += 'position:relative;';
							attr.style += 'top:auto;';
							attr.style += 'left:auto;';
							attr.style += 'margin:0;';
							attr.style += 'box-sizing: content-box;';
						}
					} else {

						attr[ 'class' ] += ' circle-no';

						attr.style += 'background-color:transparent;border-color:transparent;height:auto;width: ' + _.fusionGetValueWithUnit( parentValues.icon_size ) + ';line-height:normal;';

						if ( 'icon-boxed' === parentValues.layout ) {
							attr.style += 'position:relative;left:auto;right:auto;top:auto;margin-left:auto;margin-right:auto;';
						}
					}

					if ( values.iconcolor ) {
						attr.style += 'color:' + values.iconcolor + ';';
					}

					if ( values.iconflip ) {
						attr[ 'class' ] += ' fa-flip-' + values.iconflip;
					}

					if ( values.iconrotate ) {
						attr[ 'class' ] += ' fa-rotate-' + values.iconrotate;
					}

					if ( 'yes' === values.iconspin ) {
						attr[ 'class' ] += ' fa-spin';
					}

					attr.style += 'font-size:' + parentValues.icon_size + ';';
				}

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			buildShortcodeIconParentAttr: function( atts ) {
				var attr           = {
						class: 'icon',
						style: ''
					},
					values         = atts.values,
					parentValues   = atts.parentValues,
					animationDelay = '';

				if ( 'yes' !== parentValues.icon_circle && 'icon-boxed' === parentValues.layout ) {
					attr.style += 'position:absolute;width: 100%;top:-' + ( 50 + ( parseFloat( parentValues.icon_size ) / 2 ) ) + 'px;';
				}

				if ( 'timeline-vertical' === parentValues.layout && 'right' === parentValues.icon_align && ( ! values.outercirclebordercolor || ! values.circlebordersize ) ) {
					attr.style += 'padding-left:20px;';
				}

				if ( parentValues.animation_delay ) {
					animationDelay = parentValues.animation_delay;
					attr.style += '-webkit-animation-duration: ' + animationDelay + 'ms;';
					attr.style += 'animation-duration: ' + animationDelay + 'ms;';
				}

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			buildShortcodeIconWrapperAttr: function( atts ) {
				var attr              = {
						class: 'icon',
						style: ''
					},
					values            = atts.values,
					parentValues      = atts.parentValues,
					marginDirection   = '',
					margin            = '',
					transparentCircle = 'transparent' === values.circlecolor || 0 === jQuery.AWB_Color( values.circlecolor ).alpha();

				if ( values.icon ) {

					attr[ 'class' ] = '';

					if ( 'yes' === parentValues.icon_circle ) {
						attr.style += 'height:' + ( ( parseFloat( parentValues.icon_size ) + parseFloat( values.circlebordersize ) ) * 2 ) + 'px;';
						attr.style += 'width:' + ( ( parseFloat( parentValues.icon_size ) + parseFloat( values.circlebordersize ) ) * 2 ) + 'px;';
						attr.style += 'line-height:' + ( parseFloat( parentValues.icon_size ) + ( parseFloat( values.circlebordersize ) * 2 ) ) + 'px;';

						if ( values.outercirclebordercolor ) {
							attr.style += 'border-color:' + values.outercirclebordercolor + ';';
						}

						if ( values.outercirclebordersize && 0 !== parseFloat( values.outercirclebordersize ) ) {
							attr.style += 'border-width:' + parseFloat( values.outercirclebordersize ) + 'px;';
						}

						attr.style += 'border-style:solid;';

						if ( values.circlebordercolor && 0 !== parseFloat( values.circlebordersize ) ) {
							attr.style += 'background-color:' + values.circlebordercolor + ';';
						} else if ( values.outercirclebordersize && 0 !== parseFloat( values.outercirclebordersize ) && ! transparentCircle ) {
							attr.style += 'background-color:' + values.outercirclebordercolor + ';';
						}

						if ( 'icon-boxed' === parentValues.layout ) {
							attr.style += 'position:absolute;';
							attr.style += 'top:-' + ( 50 + parseFloat( parentValues.icon_size ) + parseFloat( values.circlebordersize ) ) + 'px;';
							attr.style += 'margin-left:-' + ( parseFloat( parentValues.icon_size ) + parseFloat( values.circlebordersize ) ) + 'px;';
						}

						if ( 'round' === parentValues.icon_circle_radius ) {
							parentValues.icon_circle_radius = '100%';
						}

						if ( -1 !== jQuery.inArray( parentValues.layout, [ 'icon-on-side', 'timeline-vertical', 'clean-horizontal' ] ) ) {
							marginDirection = 'margin-right';
							if ( 'right' === parentValues.icon_align ) {
								marginDirection = 'margin-left';
							}

							margin = '20px';
							if ( 'timeline-vertical' === parentValues.layout && 'right' === parentValues.icon_align ) {
								margin = '10px';
							}

							attr.style += marginDirection + ':' + margin + ';';
						}

						attr.style += 'box-sizing:content-box;';
						attr.style += 'border-radius:' + parentValues.icon_circle_radius + ';';
					}
				}

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			buildContentBoxHeadingAttr: function( atts ) {
				var attr           = {
						class: 'content-box-heading'
					},
					values         = atts.values,
					parentValues   = atts.parentValues,
					fontSize       = '',
					fullIconSize   = '';

				if ( parentValues.title_size ) {
					fontSize = parseFloat( parentValues.title_size );

					attr.style = 'font-size:' + fontSize + 'px;line-height:' + ( fontSize + 5 ) + 'px;';
					attr[ 'data-fontsize' ]          = fontSize;
					attr[ 'data-lineheight' ]        = ( fontSize + 5 );
					attr[ 'data-inline-fontsize' ]   = fontSize + 'px';
					attr[ 'data-inline-lineheight' ] =  ( fontSize + 5 ) + 'px';
				}

				if ( 'right' === parentValues.icon_align && '' !== attr.style && ( -1 !== jQuery.inArray( parentValues.layout, [ 'icon-on-side', 'icon-with-title', 'timeline-vertical', 'clean-horizontal' ] ) ) ) {
					attr.style += ' text-align:' + parentValues.icon_align + ';';
				} else if ( 'left' === parentValues.icon_align && jQuery( 'body' ).hasClass( 'rtl' ) && '' === attr.style && ( -1 !== jQuery.inArray( parentValues.layout, [ 'icon-on-side', 'icon-with-title', 'timeline-vertical', 'clean-horizontal' ] ) ) ) {
					attr.style += ' text-align:' + parentValues.icon_align + ';';
				}

				if ( 'icon-on-side' === parentValues.layout || 'clean-horizontal' === parentValues.layout ) {

					if ( '' !== values.image && '' !== values.image_width && '' !== values.image_height ) {

						if ( 'right' === parentValues.icon_align ) {
							attr.style += 'padding-right:' + ( parseFloat( values.image_width ) + 20 ) + 'px;';
						} else {
							attr.style += 'padding-left:' + ( parseFloat( values.image_width ) + 20 ) + 'px;';
						}
					} else if ( '' !== values.icon ) {
						if ( 'yes' === parentValues.icon_circle ) {
							fullIconSize = ( parseFloat( parentValues.icon_size ) + parseFloat( values.circlebordersize ) + parseFloat( values.outercirclebordersize ) ) * 2;
						} else {
							fullIconSize = parentValues.icon_size;
						}

						if ( 'right' === parentValues.icon_align ) {
							attr.style += 'padding-right:' + ( parseFloat( fullIconSize ) + 20 ) + 'px;';
						} else {
							attr.style += 'padding-left:' + ( parseFloat( fullIconSize ) + 20 ) + 'px;';
						}
					}
				}

				attr = _.fusionInlineEditor( {
					cid: this.model.get( 'cid' ),
					param: 'title',
					'disable-return': true,
					'disable-extra-spaces': true,
					toolbar: false
				}, attr );

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			buildContentBoxHeadingWrapperAttr: function( atts ) {
				var attr         = {
						class: 'heading'
					},
					values       = atts.values,
					parentValues = atts.parentValues;

				if ( '' !== values.icon || '' !== values.image ) {
					attr[ 'class' ] += ' heading-with-icon';
				}

				if ( '' !== parentValues.icon_align ) {
					attr[ 'class' ] += ' icon-' + parentValues.icon_align;
				}

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			buildContentContainerAttr: function( atts ) {
				var attr           = {
						class: 'content-container',
						style: ''
					},
					values         = atts.values,
					parentValues   = atts.parentValues,
					imageHeight    = '',
					fullIconSize   = '';

				if ( -1 !== jQuery.inArray( parentValues.layout, [ 'icon-on-side', 'timeline-vertical', 'clean-horizontal' ] ) && values.image && values.image_width && values.image_height ) {
					if ( 'right' === parentValues.icon_align ) {
						attr.style += 'padding-right:' + ( parseFloat( values.image_width ) + 20 ) + 'px;';
					} else {
						attr.style += 'padding-left:' + ( parseFloat( values.image_width ) + 20 ) + 'px;';
					}

					if ( 'timeline-vertical' === parentValues.layout ) {
						imageHeight = values.image_height;
						if ( imageHeight > parseFloat( parentValues.title_size ) && 0 < imageHeight - parseFloat( parentValues.title_size ) - 15 ) {
							attr.style += 'margin-top:-' + ( imageHeight - parseFloat( parentValues.title_size ) ) + 'px;';
						}
					}
				} else if ( -1 !== jQuery.inArray( parentValues.layout, [ 'icon-on-side', 'timeline-vertical', 'clean-horizontal' ] ) && values.icon ) {
					if ( 'yes' === parentValues.icon_circle ) {
						fullIconSize = ( parseFloat( parentValues.icon_size ) + parseFloat( values.circlebordersize ) + parseFloat( values.outercirclebordersize ) ) * 2;
					} else {
						fullIconSize = parentValues.icon_size;
					}

					if ( 'right' === parentValues.icon_align ) {
						attr.style += 'padding-right:' + ( parseFloat( fullIconSize ) + 20 ) + 'px;';
					} else {
						attr.style += 'padding-left:' + ( parseFloat( fullIconSize ) + 20 ) + 'px;';
					}

					if ( 'timeline-vertical' === parentValues.layout ) {
						if ( fullIconSize > parseFloat( parentValues.title_size ) && 0 < fullIconSize - parseFloat( parentValues.title_size ) - 15 ) {
							if ( 'timeline-vertical' === parentValues.layout ) {
								attr.style += 'margin-top:-' + ( ( parseFloat( fullIconSize ) - parseFloat( parentValues.title_size ) ) / 2 ) + 'px;';
							} else {
								attr.style += 'margin-top:-' + ( parseFloat( fullIconSize ) - parseFloat( parentValues.title_size ) ) + 'px;';
							}
						}
					}
				}

				if ( 'right' === parentValues.icon_align && '' !== attr.style && ( -1 !== jQuery.inArray( parentValues.layout, [ 'icon-on-side', 'icon-with-title', 'timeline-vertical', 'clean-horizontal' ] ) ) ) {
					attr.style += ' text-align:' + parentValues.icon_align + ';';
				} else if ( 'right' === parentValues.icon_align && '' === attr.style && ( -1 !== jQuery.inArray( parentValues.layout, [ 'icon-on-side', 'icon-with-title', 'timeline-vertical', 'clean-horizontal' ] ) ) ) {
					attr.style += ' text-align:' + parentValues.icon_align + ';';
				}

				if ( parentValues.body_color ) {
					attr.style += 'color:' + parentValues.body_color + ';';
				}

				attr = _.fusionInlineEditor( {
					cid: this.model.get( 'cid' )
				}, attr );

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			buildShortcodeTimelineAttr: function( atts ) {
				var attr                = {
						class: ''
					},
					values              = atts.values,
					parentValues        = atts.parentValues,
					borderColor         = '',
					fullIconSize        = '',
					positionTop         = '',
					positionHorizontal  = '',
					animationDelay      = '';

				if ( 'timeline-horizontal' === parentValues.layout ) {
					attr[ 'class' ] = 'content-box-shortcode-timeline';
					attr.style = '';

					if ( 'yes' === parentValues.icon_circle ) {
						if ( 0 !== parseFloat( values.outercirclebordersize ) ) {
							fullIconSize = ( parseFloat( parentValues.icon_size ) + parseFloat( values.circlebordersize ) + parseFloat( values.outercirclebordersize ) ) * 2;
						} else {
							fullIconSize = parseFloat( parentValues.icon_size ) * 2;
						}
					} else {
						fullIconSize = parseFloat( parentValues.icon_size );
					}

					positionTop = fullIconSize / 2;

					if ( values.backgroundcolor && 'transparent' !== values.backgroundcolor && 0 !== jQuery.AWB_Color( values.backgroundcolor ).alpha() ) {
						positionTop += 35;
					}

					if ( values.circlebordercolor ) {
						borderColor = values.circlebordercolor;
					}

					if ( values.outercirclebordercolor && values.outercirclebordersize ) {
						borderColor = values.outercirclebordercolor;
					}

					if ( ! values.circlebordercolor && ! values.outercirclebordercolor ) {
						borderColor = '#f6f6f6';
					}

					if ( borderColor ) {
						attr.style += 'border-color:' + borderColor + ';';
					}

					if ( positionTop ) {
						attr.style += 'top:' + parseFloat( positionTop ) + 'px;';
					}
				} else if ( 'timeline-vertical' === parentValues.layout ) {
					attr[ 'class' ] = 'content-box-shortcode-timeline-vertical';
					attr.style = '';

					if ( 'yes' === parentValues.icon_circle ) {
						if ( parseFloat( values.outercirclebordersize ) ) {
							fullIconSize = ( parseFloat( parentValues.icon_size ) + parseFloat( values.circlebordersize ) + parseFloat( values.outercirclebordersize ) ) * 2;
						} else {
							fullIconSize = parseFloat( parentValues.icon_size ) * 2;
						}
					} else {
						fullIconSize = parseFloat( parentValues.icon_size );
					}

					positionTop        = fullIconSize;
					positionHorizontal = fullIconSize / 2;
					if ( values.backgroundcolor && 'transparent' !== values.backgroundcolor && 0 !== jQuery.AWB_Color( values.backgroundcolor ).alpha() ) {
						positionTop        += 35;
						positionHorizontal += 35;
					}

					if ( values.circlebordercolor ) {
						borderColor = values.circlebordercolor;
					}

					if ( values.outercirclebordercolor && values.outercirclebordersize ) {
						borderColor = values.outercirclebordercolor;
					}

					if ( ! values.circlebordercolor && ! values.outercirclebordercolor ) {
						borderColor = '#f6f6f6';
					}

					if ( borderColor ) {
						attr.style += 'border-color:' + borderColor + ';';
					}

					if ( positionHorizontal ) {
						if ( 'right' === parentValues.icon_align ) {
							attr.style += 'right:' + parseFloat( positionHorizontal ) + 'px;';
						} else {
							attr.style += 'left:' + parseFloat( positionHorizontal ) + 'px;';
						}
					}

					if ( positionTop ) {
						attr.style += 'top:' + positionTop + 'px;';
					}
				}

				if ( parentValues.animationDelay ) {
					animationDelay = parentValues.animation_delay;
					attr.style += '-webkit-transition-duration: ' + animationDelay + 'ms;';
					attr.style += 'animation-duration: ' + animationDelay + 'ms;';
				}

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			buildContentWrapperAttr: function( atts ) {
				var attr         = {
						class: 'col content-box-wrapper content-wrapper'
					},
					values       = atts.values,
					parentValues = atts.parentValues;

				// Set parent values if child values are unset to get downwards compatibility.
				if ( ! values.backgroundcolor ) {
					values.backgroundcolor = parentValues.backgroundcolor;
				}

				if ( values.backgroundcolor ) {
					attr.style = 'background-color:' + values.backgroundcolor + ';';

					if ( 'transparent' !== values.backgroundcolor && 0 !== jQuery.AWB_Color( values.backgroundcolor ).alpha() ) {
						attr[ 'class' ] += '-background';
					}
				}

				if ( 'icon-boxed' === parentValues.layout ) {
					attr[ 'class' ] += ' content-wrapper-boxed';
				}

				if ( values.link && 'box' === parentValues.link_area ) {
					attr[ 'data-link' ] = values.link;
					attr[ 'data-link-target' ] = values.link_target;
				}

				attr[ 'class' ] += ' link-area-' + parentValues.link_area;

				if ( values.link && parentValues.link_type ) {
					attr[ 'class' ] += ' link-type-' + parentValues.link_type;
				}

				if ( values.outercirclebordercolor && values.outercirclebordersize && parseFloat( values.outercirclebordersize ) ) {
					attr[ 'class' ] += ' content-icon-wrapper-yes';
				}
				if ( values.outercirclebordercolor && values.outercirclebordersize && 0 !== parseFloat( values.outercirclebordersize ) && 'pulsate' === parentValues.icon_hover_type ) {
					attr[ 'class' ] += ' icon-wrapper-hover-animation-' + parentValues.icon_hover_type;
				} else {
					attr[ 'class' ] += ' icon-hover-animation-' + parentValues.icon_hover_type;
				}

				if ( values.textcolor ) {
					attr.style += 'color:' + values.textcolor + ';';
				}

				if ( 'none' !== values.animation_type ) {
					if ( 'undefined' !== typeof values.animation_class ) {
						attr[ 'class' ] += ' ' + values.animation_class;
					}
				}

				if ( '' === values.animation_type ) {
					attr = _.fusionAnimations( parentValues, attr );
				} else {
					attr = _.fusionAnimations( values, attr );
				}

				return attr;
			},

			/**
			 * Builds styles.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {string}
			 */
			buildStyles: function( atts ) {

				var styles                 = '',
					cid                    = this.model.get( 'cid' ), // eslint-disable-line no-unused-vars
					values                 = atts.values,
					parentValues           = atts.parentValues,
					parentCid              = atts.parentCid,
					circleHoverAccentColor = '',
					transparentChild       = '',
					hoverAccentColor       = '';

				if ( 'transparent' === values.circlecolor || 0 === jQuery.AWB_Color( values.backgroundcolor ).alpha() || 'no' === parentValues.icon_circle ) {
					transparentChild = true;
				}

				if ( true === transparentChild ) {
					hoverAccentColor       = parentValues.hover_accent_color;
					circleHoverAccentColor = 'transparent';

					styles += '.fusion-content-boxes-cid' + parentCid + ' .fusion-content-box-hover .heading-link:hover .icon i.circle-yes,.fusion-content-boxes-cid' + parentCid + ' .fusion-content-box-hover .link-area-box:hover .heading-link .icon i.circle-yes,.fusion-content-boxes-cid' + parentCid + ' .fusion-content-box-hover .link-area-link-icon-hover .heading .icon i.circle-yes,.fusion-content-boxes-cid' + parentCid + ' .fusion-content-box-hover .link-area-box-hover .heading .icon i.circle-yes { background-color: ' + circleHoverAccentColor + ' !important; border-color: ' + hoverAccentColor + ' !important;}';
				} else if ( false === transparentChild ) {
					hoverAccentColor = parentValues.hover_accent_color;

					styles += '.fusion-content-boxes-cid' + parentCid + ' .fusion-content-box-hover .heading-link:hover .icon i.circle-yes,.fusion-content-boxes-cid' + parentCid + ' .fusion-content-box-hover .link-area-box:hover .heading-link .icon i.circle-yes,.fusion-content-boxes-cid' + parentCid + ' .fusion-content-box-hover .link-area-link-icon-hover .heading .icon i.circle-yes,.fusion-content-boxes-cid' + parentCid + ' .fusion-content-box-hover .link-area-box-hover .heading .icon i.circle-yes {background-color: ' + hoverAccentColor + ' !important;border-color: ' + hoverAccentColor + ' !important;}';
				}

				return styles;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {boolean} readmore - Whether we want a readmore link or not.
			 * @param {string} extraClass - Any extra classes that we want to add.
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			contentBoxShortcodeAttrs: function( readmore, extraClass, atts ) {

				// contentBoxShortcodeLink Attributes.
				var contentBoxShortcodeLink = {
						class: '',
						style: ''
					},
					additionMargin          = '',
					fullIconSize            = '',
					values                  = atts.values,
					parentValues            = atts.parentValues,
					extras                  = atts.extras;

				if ( values.link ) {
					contentBoxShortcodeLink.href = values.link;
				}

				if ( values.link_target ) {
					contentBoxShortcodeLink.target = values.link_target;
				}
				if ( '_blank' === values.link_target ) {
					contentBoxShortcodeLink.rel = 'noopener noreferrer';
				}

				if ( readmore ) {
					if ( 'text' === parentValues.link_type || 'button-bar' === parentValues.link_type ) {
						contentBoxShortcodeLink[ 'class' ] += ' fusion-read-more';
						if ( 'button-bar' === parentValues.link_type ) {
							contentBoxShortcodeLink[ 'class' ] += ' fusion-button-bar';
						}

					} else if ( 'button' === parentValues.link_type ) {
						contentBoxShortcodeLink[ 'class' ] += 'fusion-read-more-button fusion-content-box-button fusion-button button-default fusion-button-default-size button-' + extras.button_type.toLowerCase();
					}
				}

				if ( 'button-bar' === parentValues.link_type && 'timeline-vertical' === parentValues.layout && readmore ) {

					additionMargin = 20 + 15;
					if ( values.backgroundcolor && 'transparent' !== values.backgroundcolor && 0 !== jQuery.AWB_Color( values.backgroundcolor ).alpha() ) {
						additionMargin += 35;
					}

					if ( values.image && values.image_width && values.image_height ) {
						fullIconSize = values.image_width;
					} else if ( values.icon ) {
						if ( 'yes' === parentValues.icon_circle ) {
							fullIconSize = ( parentValues.icon_size + parseFloat( values.circlebordersize ) + parseFloat( values.outercirclebordersize ) ) * 2;
						} else {
							fullIconSize = parentValues.icon_size;
						}
					}

					if ( 'right' === parentValues.icon_align ) {
						contentBoxShortcodeLink.style += 'margin-right:' + ( parseFloat( fullIconSize ) + parseFloat( additionMargin ) ) + 'px;';
					} else {
						contentBoxShortcodeLink.style += 'margin-left:' + ( parseFloat( fullIconSize ) + parseFloat( additionMargin ) ) + 'px;';
					}

					contentBoxShortcodeLink.style += 'width:calc(100% - ' + ( fullIconSize + additionMargin + 15 ) + 'px);';
				} else if ( -1 !== jQuery.inArray( parentValues.layout, [ 'icon-on-side', 'clean-horizontal', 'timeline-vertical' ] ) && -1 !== jQuery.inArray( parentValues.link_type, [ 'text', 'button' ] ) && readmore ) {

					additionMargin = 20;

					if ( values.image && values.image_width && values.mage_height ) {
						fullIconSize = values.image_width;
					} else if ( values.icon ) {
						if ( 'yes' === parentValues.icon_circle ) {
							fullIconSize = ( parseFloat( parentValues.icon_size ) + parseFloat( values.circlebordersize ) + parseFloat( values.outercirclebordersize ) ) * 2;
						} else {
							fullIconSize = parentValues.icon_size;
						}
					}

					if ( 'text' === parentValues.link_type || 'button' === parentValues.link_type ) {
						if ( 'right' === parentValues.icon_align ) {
							contentBoxShortcodeLink.style += 'float:' + parentValues.icon_align + ';';
							contentBoxShortcodeLink.style += 'margin-right:' + ( parseFloat( fullIconSize ) + parseFloat( additionMargin ) ) + 'px;';
						} else {
							contentBoxShortcodeLink.style += 'margin-left:' + ( parseFloat( fullIconSize ) + additionMargin ) + 'px;';
						}

						if ( 'yes' === parentValues.button_span ) {
							contentBoxShortcodeLink.style += 'width:calc( 100% - ' + ( parseFloat( fullIconSize ) + parseFloat( additionMargin ) ) + 'px );';
						}
					} else if ( 'right' === parentValues.icon_align ) {
						contentBoxShortcodeLink.style += 'margin-right:' + ( parseFloat( fullIconSize ) + parseFloat( additionMargin ) ) + 'px;';
					} else {
						contentBoxShortcodeLink.style += 'margin-left:' + ( parseFloat( fullIconSize ) + parseFloat( additionMargin ) ) + 'px;';
					}
				} else if ( 'icon-with-title' === parentValues.layout ) {
					contentBoxShortcodeLink.style += 'float:' + parentValues.icon_align + ';';
				}

				if ( -1 === jQuery.inArray( parentValues.layout, [ 'icon-on-side', 'clean-horizontal', 'timeline-vertical' ] ) && 'button' === parentValues.link_type && 'yes' === parentValues.button_span ) {
					contentBoxShortcodeLink.style += 'width: 100%;';
				}

				if ( extraClass ) {
					contentBoxShortcodeLink[ 'class' ] += ' ' + extraClass;
				}
				return contentBoxShortcodeLink;
			}
		} );
	} );
}( jQuery ) );

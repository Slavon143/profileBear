/* global FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Title View
		FusionPageBuilder.fusion_tagline_box = FusionPageBuilder.ElementView.extend( {

			onInit: function() {
				var params = this.model.get( 'params' );
				if ( 'object' === typeof params ) {
					// Split border radius into 4.
					if ( 'undefined' === typeof params.button_border_radius_top_left && 'undefined' !== typeof params.button_border_radius && '' !== params.button_border_radius ) {
						params.button_border_radius_top_left     = parseInt( params.button_border_radius ) + 'px';
						params.button_border_radius_top_right    = params.button_border_radius_top_left;
						params.button_border_radius_bottom_right = params.button_border_radius_top_left;
						params.button_border_radius_bottom_left  = params.button_border_radius_top_left;
						delete params.button_border_radius;
					}
					this.model.set( 'params', params );
				}
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
				this.validateValues( atts.values );

				// Shared base object.
				this.extras         = atts.extras;
				this.attrButton     = this.buildButtonAttr( atts.values );

				// Create attribute objects
				attributes.attr              = this.buildAttr( atts.values );
				attributes.attrReadingBox    = this.buildReadingBoxAttr( atts.values );
				attributes.desktopAttrButton = this.buildDesktopButtonAttr( atts.values );
				attributes.mobileAttrButton  = this.buildMobileButtonAttr( atts.values );
				attributes.titleAttr         = this.buildTitleAttr( atts.values );
				attributes.buttonSpanAttr    = this.buildButtonSpanAttr( atts.values );
				attributes.descriptionAttr   = this.buildDescriptionAttr( atts.values );
				attributes.contentAttr       = this.buildContentAttr( atts.values );

				// Any extras that need passed on.
				attributes.cid    = this.model.get( 'cid' );
				attributes.values = atts.values;

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
				values.border = _.fusionValidateAttrValue( values.border, 'px' );

				if ( values.modal ) {
					values.link = '#';
				}

				if ( values.button_type ) {
					values.button_type = values.button_type.toLowerCase();
				}

				// BC compatibility for button shape.
				if ( 'undefined' !== typeof values.button_shape && 'undefined' === typeof values.button_border_radius ) {
					values.button_border_radius = '0';
					if ( 'square' === values.button_shape ) {
						values.button_border_radius = '0';
					} else if ( 'round' === values.button_shape ) {
						values.button_border_radius = '2';

						if ( '3d' === values.button_type ) {
							values.button_border_radius = '4';
						}
					} else if ( 'pill' === values.button_shape ) {
						values.button_border_radius = '25';
					} else if ( '' === values.button_shape ) {
						values.button_border_radius = '';
					}
					values.button_border_radius_top_left     = values.button_border_radius;
					values.button_border_radius_top_right    = values.button_border_radius_top_left;
					values.button_border_radius_bottom_right = values.button_border_radius_top_left;
					values.button_border_radius_bottom_left  = values.button_border_radius_top_left;
				} else if ( 'string' === typeof values.buton_border_radius && 'undefined' === typeof values.button_border_radius_top_left ) {
					values.button_border_radius_top_left     = values.button_button_border_radius;
					values.button_border_radius_top_right    = values.button_border_radius_top_left;
					values.button_border_radius_bottom_right = values.button_border_radius_top_left;
					values.button_border_radius_bottom_left  = values.button_border_radius_top_left;
				}

				values.button_border_radius = _.fusionGetValueWithUnit( values.button_border_radius_top_left ) + ' ' + _.fusionGetValueWithUnit( values.button_border_radius_top_right ) + ' ' + _.fusionGetValueWithUnit( values.button_border_radius_bottom_right ) + ' ' + _.fusionGetValueWithUnit( values.button_border_radius_bottom_left );

				try {
					if ( FusionPageBuilderApp.base64Encode( FusionPageBuilderApp.base64Decode( values.description ) ) === values.description ) {
						values.description = FusionPageBuilderApp.base64Decode( values.description );
						values.description = _.unescape( values.description );
					}
					if ( FusionPageBuilderApp.base64Encode( FusionPageBuilderApp.base64Decode( values.title ) ) === values.title ) {
						values.title = FusionPageBuilderApp.base64Decode( values.title );
						values.title = _.unescape( values.title );
					}
				} catch ( error ) {
					console.log( error ); // jshint ignore:line
				}

				values.padding_bottom = _.fusionValidateAttrValue( values.padding_bottom, 'px' );
				values.padding_left   = _.fusionValidateAttrValue( values.padding_left, 'px' );
				values.padding_right  = _.fusionValidateAttrValue( values.padding_right, 'px' );
				values.padding_top    = _.fusionValidateAttrValue( values.padding_top, 'px' );
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
					class: 'fusion-reading-box-container reading-box-container-' + this.model.get( 'cid' ),
					style: ''
				} );

				attr = _.fusionAnimations( values, attr );

				if ( values.margin_top || '0' === values.margin_top ) {
					attr.style += 'margin-top:' + _.fusionGetValueWithUnit( values.margin_top ) + ';';
				}

				if ( values.margin_bottom || '0' === values.margin_bottom ) {
					attr.style += 'margin-bottom:' + _.fusionGetValueWithUnit( values.margin_bottom ) + ';';
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
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
			buildReadingBoxAttr: function( values ) {
				var attrReadingBox = {
					class: 'reading-box'
				};

				if ( 'right' === values.content_alignment ) {
					attrReadingBox[ 'class' ] += ' reading-box-right';
				} else if ( 'center' === values.content_alignment ) {
					attrReadingBox[ 'class' ] += ' reading-box-center';
				}

				if ( 'yes' === values.shadow ) {
					attrReadingBox[ 'class' ] += ' element-bottomshadow';
				}

				attrReadingBox.style  = 'background-color:' + values.backgroundcolor + ';';
				attrReadingBox.style += 'border-width:' + values.border + ';';
				attrReadingBox.style += 'border-color:' + values.bordercolor + ';';
				if ( 'none' !== values.highlightposition ) {
					if ( 3 < parseInt( values.border, 10 ) ) {
						attrReadingBox.style += 'border-' + values.highlightposition + '-width:' + values.border + ';';
					} else {
						attrReadingBox.style += 'border-' + values.highlightposition + '-width:3px;';
					}
					attrReadingBox.style += 'border-' + values.highlightposition + '-color:' + this.extras.primary_color + ';';
				}
				attrReadingBox.style += 'border-style:solid;';

				if ( '' !== values.padding_top ) {
					attrReadingBox.style += 'padding-top:' + values.padding_top + ';';
				}

				if ( '' !== values.padding_right ) {
					attrReadingBox.style += 'padding-right:' + values.padding_right + ';';
				}

				if ( '' !== values.padding_bottom ) {
					attrReadingBox.style += 'padding-bottom:' + values.padding_bottom + ';';
				}

				if ( '' !== values.padding_left ) {
					attrReadingBox.style += 'padding-left:' + values.padding_left + ';';
				}

				return attrReadingBox;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildButtonAttr: function( values ) {
				var attrButton = {
					class: 'button fusion-button button-' + values.buttoncolor + ' fusion-button-' + values.button_size + ' button-' + values.button_size + ' button-' + values.button_type,
					style: ''
				};

				attrButton[ 'class' ] = attrButton[ 'class' ].toLowerCase();

				if ( 'right' === values.content_alignment ) {
					attrButton[ 'class' ] += ' continue-left';
				} else if ( 'center' === values.content_alignment ) {
					attrButton[ 'class' ] += ' continue-center';
				} else {
					attrButton[ 'class' ] += ' continue-right';
				}

				if ( 'flat' === values.button_type ) {
					attrButton.style += '-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none;';
				}

				attrButton.href   = values.link;
				attrButton.target = values.linktarget;

				if ( '_blank' === attrButton.target ) {
					attrButton.rel = 'noopener noreferrer';
				}

				if ( '' !== values.modal ) {
					attrButton[ 'data-toggle' ] = 'modal';
					attrButton[ 'data-target' ] = '.' + values.modal;
				}

				if ( '' !== values.button_border_radius ) {
					attrButton.style += 'border-radius:' + values.button_border_radius;
				}

				return attrButton;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			buildTitleAttr: function() {
				var self = this;

				return _.fusionInlineEditor( {
					cid: self.model.get( 'cid' ),
					param: 'title',
					'disable-return': true,
					'disable-extra-spaces': true,
					encoding: true,
					toolbar: false
				}, {} );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			buildButtonSpanAttr: function() {
				var self = this;

				return _.fusionInlineEditor( {
					cid: self.model.get( 'cid' ),
					param: 'button',
					'disable-return': true,
					'disable-extra-spaces': true,
					toolbar: false
				}, {} );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildDescriptionAttr: function( values ) {
				var descriptionAttr = {
						class: 'reading-box-description'
					},
					self = this;

				if ( '' !== values.title ) {
					descriptionAttr[ 'class' ] += ' fusion-reading-box-additional';
				}

				descriptionAttr = _.fusionInlineEditor( {
					cid: self.model.get( 'cid' ),
					param: 'description',
					'disable-return': true,
					'disable-extra-spaces': true,
					encoding: true,
					toolbar: 'simple'
				}, descriptionAttr );

				return descriptionAttr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildContentAttr: function( values ) {
				var self = this,
					contentAttr = {
						class: 'reading-box-additional'
					};

				if ( '' === values.description && '' !== values.title ) {
					contentAttr[ 'class' ] += ' fusion-reading-box-additional';
				}

				contentAttr = _.fusionInlineEditor( {
					cid: self.model.get( 'cid' )
				}, contentAttr );

				return contentAttr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildDesktopButtonAttr: function( values ) {
				var attrButton        = jQuery.extend( true, {}, this.attrButton ),
					buttonMarginClass = '';

				if ( '' !== values.description && 'undefined' !== typeof values.element_content && '' !== values.element_content ) {
					buttonMarginClass = ' fusion-desktop-button-margin';
				}

				attrButton[ 'class' ] += ' fusion-desktop-button fusion-tagline-button continue' + buttonMarginClass;

				return attrButton;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			buildMobileButtonAttr: function() {
				var attrButton = jQuery.extend( true, {}, this.attrButton );

				attrButton[ 'class' ] += ' fusion-mobile-button';

				return attrButton;
			}
		} );
	} );
}( jQuery ) );

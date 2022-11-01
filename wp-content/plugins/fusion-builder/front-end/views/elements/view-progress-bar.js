var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Progress Bar Element View.
		FusionPageBuilder.fusion_progress = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );

				// Create attribute objects
				attributes.attr        = this.buildAttr( atts.values );
				attributes.attrBar     = this.buildBarAttr( atts.values );
				attributes.attrSpan    = this.buildSpanAttr( atts.values, atts.extras );
				attributes.attrEditor  = this.buildInlineEditorAttr( atts.values );
				attributes.attrContent = this.buildContentAttr( atts.values );

				// Any extras that need passed on.
				attributes.cid    = this.model.get( 'cid' );
				attributes.values = atts.values;
				attributes.percentage = this.sanitizePercentage( atts.values.percentage );

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
				values.filledbordersize = _.fusionValidateAttrValue( values.filledbordersize, 'px' );
				values.margin_bottom    = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left      = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right     = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top       = _.fusionValidateAttrValue( values.margin_top, 'px' );
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
					class: 'fusion-progressbar',
					style: ''
				} );

				if ( 'above_bar' === values.text_position ) {
					attr[ 'class' ] += ' fusion-progressbar-text-above-bar';
				} else if ( 'below_bar' === values.text_position ) {
					attr[ 'class' ] += ' fusion-progressbar-text-below-bar';
				} else {
					attr[ 'class' ] += ' fusion-progressbar-text-on-bar';
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				if	( values.text_align ) {
					attr.style += 'text-align:' + values.text_align;
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
			 * @return {Object}
			 */
			buildInlineEditorAttr: function() {
				var attr = {
					class: 'fusion-progressbar-text',
					id: 'awb-progressbar-label-' + this.model.get( 'cid' )
				};

				attr = _.fusionInlineEditor( {
					cid: this.model.get( 'cid' ),
					'disable-return': true,
					'disable-extra-spaces': true,
					toolbar: 'simple'
				}, attr );

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildContentAttr: function( values ) {
				var attr = {
					class: 'progress progress-bar-content',
					role: 'progressbar',
					style: ''
				};

				attr.style += 'width:' + this.sanitizePercentage( values.percentage ) + '%;';
				attr.style += 'background-color:' + values.filledcolor + ';';

				if ( '' !== values.filledbordersize && '' !== values.filledbordercolor ) {
					attr.style += 'border: ' + values.filledbordersize + ' solid ' + values.filledbordercolor + ';';
				}

				if ( '' !== values.border_radius_top_left ) {
					attr.style += 'border-top-left-radius:' + values.border_radius_top_left + ';';
				}

				if ( '' !== values.border_radius_top_right ) {
					attr.style += 'border-top-right-radius:' + values.border_radius_top_right + ';';
				}

				if ( '' !== values.border_radius_bottom_left ) {
					attr.style += 'border-bottom-left-radius:' + values.border_radius_bottom_left + ';';
				}

				if ( '' !== values.border_radius_bottom_right ) {
					attr.style += 'border-bottom-right-radius:' + values.border_radius_bottom_right + ';';
				}

				attr.role               = 'progressbar';
				attr[ 'aria-labelledby' ] = 'awb-progressbar-label-' + this.model.get( 'cid' );
				attr[ 'aria-valuemin' ]   = '0';
				attr[ 'aria-valuemax' ]   = '100';
				attr[ 'aria-valuenow' ]   = values.percentage;

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildBarAttr: function( values ) {
				var attr = {
					class: 'fusion-progressbar-bar progress-bar',
					style: ''
				};

				attr.style += 'background-color:' + values.unfilledcolor + ';';

				if ( 'yes' === values.striped ) {
					attr[ 'class' ] += ' progress-striped';
				}

				if ( 'yes' === values.animated_stripes ) {
					attr[ 'class' ] += ' active';
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				if ( '' !== values.height ) {
					attr.style += 'height:' + values.height + ';';
				}

				if ( '' !== values.border_radius_top_left ) {
					attr.style += 'border-top-left-radius:' + values.border_radius_top_left + ';';
				}

				if ( '' !== values.border_radius_top_right ) {
					attr.style += 'border-top-right-radius:' + values.border_radius_top_right + ';';
				}

				if ( '' !== values.border_radius_bottom_left ) {
					attr.style += 'border-bottom-left-radius:' + values.border_radius_bottom_left + ';';
				}

				if ( '' !== values.border_radius_bottom_right ) {
					attr.style += 'border-bottom-right-radius:' + values.border_radius_bottom_right + ';';
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
			buildSpanAttr: function( values, extras ) {
				var attr = {
					class: 'progress-title',
					style: ''
				};
				var empty_percentage = 0;

				attr.style += 'color:' + values.textcolor + ';';

				if ( 'on_bar' === values.text_position ) {
					empty_percentage = 100 - this.sanitizePercentage( values.percentage );
					if ( 66 > empty_percentage ) {
						if ( ! extras.is_rtl ) {
							attr.style += 'right: calc(15px + ' + empty_percentage + '%);';
						} else {
							attr.style += 'left: calc(15px + ' + empty_percentage + '%);';
						}
					}
				}

				if ( '' !== values.text_line_height ) {
					attr.style += 'line-height:' + values.text_line_height + ';';
				}

				if ( '' !== values.text_letter_spacing ) {
					attr.style += 'letter-spacing:' + _.fusionGetValueWithUnit( values.text_letter_spacing ) + ';';
				}

				if ( '' !== values.text_text_transform ) {
					attr.style += 'text-transform:' + values.text_text_transform + ';';
				}

				if ( '' !== values.text_font_size ) {
					attr.style += 'font-size:' + _.fusionGetValueWithUnit( values.text_font_size ) + ';';
				}

				attr.style += _.fusionGetFontStyle( 'text_font', values );

				return attr;
			},

			/**
			 * Sanitize the percentage value, because this can come also from a
			 * dynamic data which can be a string or a float.
			 *
			 * @since 3.6
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			sanitizePercentage: function( percentage ) {
				percentage = parseFloat( percentage );

				// percentage can be NaN if parseFloat failed.
				if ( ! percentage ) {
					percentage = 0;
				}

				percentage = Math.round( percentage );

				if ( 0 > percentage ) {
					percentage = 0;
				}

				if ( 100 < percentage ) {
					percentage = 100;
				}

				return percentage;
			}
		} );
	} );
}( jQuery ) );

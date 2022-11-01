var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Alert Element View.
		FusionPageBuilder.fusion_audio = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.1
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );

				// Create attribute objects
				attributes.attr = this.buildAttr( atts.values );

				// Any extras that need passed on.
				attributes.cid    = this.model.get( 'cid' );
				attributes.values = atts.values;

				return attributes;
			},

			/**
			 * Modify values.
			 *
			 * @since 2.1
			 * @param {Object} values - The values.
			 * @return {void}
			 */
			validateValues: function( values ) {
				var corners = [
					'top_left',
					'top_right',
					'bottom_right',
					'bottom_left'
				];

				_.each( corners, function( corner ) {
					if ( 'undefined' !== typeof values[ 'border_radius_' + corner ] && '' !== values[ 'border_radius_' + corner ] ) {
						values[ 'border_radius_' + corner ] = _.fusionGetValueWithUnit( values[ 'border_radius_' + corner ] );
					} else {
						values[ 'border_radius_' + corner ] = '0px';
					}
				} );

				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.1
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var style,
					attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-audio',
						style: ''
					} ),
					corners = [
						'top_left',
						'top_right',
						'bottom_right',
						'bottom_left'
					];

				if ( values.progress_color ) {
					style  = '--fusion-audio-accent-color:' + values.progress_color + ';';
				}
				if ( values.border_size ) {
					style += '--fusion-audio-border-size:' + values.border_size + ';';
				}
				if ( values.border_color ) {
					style += '--fusion-audio-border-color:' + values.border_color + ';';
				}

				_.each( corners, function( corner ) {
					if ( values[ 'border_radius_' + corner ] ) {
						style += '--fusion-audio-border-' + corner.replace( '_', '-' ) + '-radius:' + values[ 'border_radius_' + corner ] + ';';
					}
				} );

				if ( values.background_color ) {
					style += '--fusion-audio-background-color:' + values.background_color + ';';
				}
				if ( values.max_width ) {
					style += '--fusion-audio-max-width:' + values.max_width + ';';
				}

				// Box shadow.
				if ( 'yes' === values.box_shadow ) {
					style += '--fusion-audio-box-shadow:' + _.fusionGetBoxShadowStyle( values ) + ';';
				}

				if ( '' !== values.margin_top ) {
					style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_right ) {
					style += 'margin-right:' + values.margin_right + ';';
				}

				if ( '' !== values.margin_bottom ) {
					style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( '' !== values.margin_left ) {
					style += 'margin-left:' + values.margin_left + ';';
				}

				attr.style = style;

				if ( 'dark' === values.controls_color_scheme ) {
					attr[ 'class' ] += ' dark-controls';
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				attr = _.fusionAnimations( values, attr );

				attr.values = values;

				return attr;
			}
		} );
	} );
}( jQuery ) );

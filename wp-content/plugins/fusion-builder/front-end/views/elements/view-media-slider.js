var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Slider parent View.
		FusionPageBuilder.fusion_slider = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {

				// TODO: save DOM and apply instead of generating
				this.generateChildElements();

				this._refreshJs();
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {},
					slides = window.FusionPageBuilderApp.findShortcodeMatches( atts.params.element_content, 'fusion_slide' ),
					slideElement;

				this.model.attributes.showPlaceholder = false;

				if ( 1 <= slides.length ) {
					slideElement = slides[ 0 ].match( window.FusionPageBuilderApp.regExpShortcode( 'fusion_slide' ) );
					this.model.attributes.showPlaceholder = ( 'undefined' === typeof slideElement[ 5 ] || '' === slideElement[ 5 ] || 'undefined' ===  slideElement[ 5 ] ) ? true : false;
				}

				// Validate values.
				this.validateValues( atts.values );

				// Create attribute objects.
				attributes.sliderShortcode = this.buildSliderAttr( atts.values );

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
				values.width  = _.fusionValidateAttrValue( values.width, 'px' );
				values.height = _.fusionValidateAttrValue( values.height, 'px' );

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

				values.slideshow_autoplay      = ( 'yes' === values.slideshow_autoplay || '1' === values.slideshow_autoplay ) ? true : false;
				values.slideshow_smooth_height = ( 'yes' === values.slideshow_smooth_height || '1' === values.slideshow_smooth_height ) ? true : false;
			},

			buildSliderAttr: function( values ) {
				var sliderShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-slider-sc flexslider'
				}
				);

				if ( true === this.model.attributes.showPlaceholder ) {
					sliderShortcode[ 'class' ] += ' fusion-show-placeholder';
				}

				if ( '' !== values.hover_type ) {
					sliderShortcode[ 'class' ] += ' flexslider-hover-type-' + values.hover_type;
				}

				if ( '' !== values.alignment ) {
					sliderShortcode[ 'class' ] += ' fusion-align' + values.alignment;
				}

				if ( -1 !== values.width.indexOf( 'px' ) &&  -1 !== values.height.indexOf( 'px' ) ) {
					sliderShortcode[ 'class' ] += ' fusion-slider-sc-cover';
				}

				if ( '' !== values.slideshow_autoplay ) {
					sliderShortcode[ 'data-slideshow_autoplay' ] = values.slideshow_autoplay ? '1' : '0';
				}

				if ( '' !== values.slideshow_smooth_height ) {
					sliderShortcode[ 'data-slideshow_smooth_height' ] = values.slideshow_smooth_height ? '1' : '0';
				}

				if ( '' !== values.slideshow_speed ) {
					sliderShortcode[ 'data-slideshow_speed' ] = values.slideshow_speed;
				}

				sliderShortcode.style = 'max-width:' + values.width + ';height:' + values.height + ';';

				if ( values.margin_top ) {
					sliderShortcode.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( values.margin_right ) {
					sliderShortcode.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( values.margin_bottom ) {
					sliderShortcode.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( values.margin_left ) {
					sliderShortcode.style += 'margin-left:' + values.margin_left + ';';
				}

				if ( '' !== values[ 'class' ] ) {
					sliderShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}
				if ( '' !== values.id ) {
					sliderShortcode.id = values.id;
				}

				return sliderShortcode;
			}

		} );
	} );
}( jQuery ) );

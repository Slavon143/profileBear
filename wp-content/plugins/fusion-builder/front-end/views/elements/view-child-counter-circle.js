var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Counter circle child View
		FusionPageBuilder.fusion_counter_circle = FusionPageBuilder.ChildElementView.extend( {

			/**
			 * Runs during render() call.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}

				if ( 'undefined' !== typeof this.model.attributes.childSelectors ) {

					this.model.attributes.childSelectors[ 'class' ] += ' fusion-builder-child-element-content';

					this.setElementAttributes( this.$el.find( '.fusion-builder-child-element-content' ), this.model.attributes.childSelectors );
				}
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}

				if ( 'undefined' !== typeof this.model.attributes.childSelectors ) {

					this.model.attributes.childSelectors[ 'class' ] += ' fusion-builder-child-element-content';

					this.setElementAttributes( this.$el.find( '.fusion-builder-child-element-content' ), this.model.attributes.childSelectors );
				}

				// Using non debounced version for smoothness.
				this.refreshJs();
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

				this.computeAtts( atts.values );

				attributes.cid    = this.model.get( 'cid' );
				attributes.parent = this.model.get( 'parent' );
				attributes.output = atts.values.element_content;

				return attributes;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			computeAtts: function( values ) {
				var scales                        = '',
					countdown                     = '',
					counterCircleWrapperShortcode = '',
					counterCircleShortcode        = {
						class: 'fusion-counter-circle counter-circle counter-circle-content'
					},
					multiplicator,
					strokeSize,
					fontSize;

				values.size = _.fusionValidateAttrValue( values.size, '' );

				if ( 'yes' === values.scales ) {
					scales = true;
				}

				if ( 'yes' === values.countdown ) {
					countdown = true;
				}

				if ( '' !== values[ 'class' ] ) {
					counterCircleShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					counterCircleShortcode.id = values.id;
				}

				values.size = parseFloat( values.size );

				multiplicator = values.size / 220;
				strokeSize    = 11 * multiplicator;
				fontSize      = 50 * multiplicator;

				counterCircleShortcode[ 'data-percent' ]       = this.sanitizePercentage( values.value );
				counterCircleShortcode[ 'data-countdown' ]     = countdown;
				counterCircleShortcode[ 'data-filledcolor' ]   = jQuery.AWB_Color( values.filledcolor ).toRgbaString();
				counterCircleShortcode[ 'data-unfilledcolor' ] = jQuery.AWB_Color( values.unfilledcolor ).toRgbaString();
				counterCircleShortcode[ 'data-scale' ]         = scales;
				counterCircleShortcode[ 'data-size' ]          = values.size.toString();
				counterCircleShortcode[ 'data-speed' ]         = values.speed.toString();
				counterCircleShortcode[ 'data-strokesize' ]    = strokeSize.toString();

				counterCircleShortcode.style = 'font-size:' + fontSize + 'px;height:' + values.size + 'px;width:' + values.size + 'px;';

				// counterCircleWrapperShortcode Attributes.
				counterCircleWrapperShortcode = {
					class: 'counter-circle-wrapper',
					style: 'height:' + values.size + 'px;width:' + values.size + 'px;'
				};

				counterCircleWrapperShortcode[ 'data-originalsize' ] = values.size.toString();

				this.model.set( 'selectors', counterCircleWrapperShortcode );
				this.model.set( 'childSelectors', counterCircleShortcode );
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

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Chart Dataset child View.
		FusionPageBuilder.fusion_chart_dataset = FusionPageBuilder.ChildElementView.extend( {

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
				var attributes = {},
					element = window.fusionAllElements[ this.model.get( 'element_type' ) ];

				atts.values = jQuery.extend( true, {}, element.defaults, _.fusionCleanParameters( atts.params ) );

				attributes.chartDatasetShortcode = this.buildDatasetAttr( atts.values );

				return attributes;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildDatasetAttr: function( values ) {
				var chartDatasetShortcode = {
					class: 'fusion-chart-dataset'
				};

				if ( values.title ) {
					chartDatasetShortcode[ 'data-label' ] = values.title;
				} else {
					chartDatasetShortcode[ 'data-label' ] = ' ';
				}

				if ( '' !== values.values ) {
					chartDatasetShortcode[ 'data-values' ] = values.values;
				}

				if ( '' !== values.background_color ) {
					chartDatasetShortcode[ 'data-background_color' ] = jQuery.AWB_Color( values.background_color ).toRgbaString();
				}

				if ( '' !== values.border_color ) {
					chartDatasetShortcode[ 'data-border_color' ] = jQuery.AWB_Color( values.border_color ).toRgbaString();
				}

				return chartDatasetShortcode;
			}

		} );
	} );
}( jQuery ) );

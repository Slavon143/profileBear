var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Scroll Progress Element View.
		FusionPageBuilder.fusion_scroll_progress = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.3
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );

				this.values = atts.values;
				this.params = this.model.get( 'params' );
				this.extras = atts.extras;

				// Any extras that need passed on.
				attributes.cid         = this.model.get( 'cid' );
				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.styles      = this.getStyles();
				attributes.position    = atts.values.position;
				attributes.label       = window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				attributes.icon        = window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;

				return attributes;
			},


			/**
			 * Modify values.
			 *
			 * @since 3.3
			 * @param {Object} values - The values.
			 * @return {void}
			 */
			validateValues: function( values ) {
				var borderRadius = values.border_radius_top_left + ' ' + values.border_radius_top_right + ' ' + values.border_radius_bottom_right + ' ' + values.border_radius_bottom_left;

				values.border_radius = ( '0px 0px 0px 0px' === borderRadius ) ? '' : borderRadius;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 3.3
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-scroll-progress fusion-scroll-progress-' + this.model.get( 'cid' ),
					max: '100',
					value: ''
				} );

				if ( 'flow' !== values.position ) {
					attr[ 'class' ] += ' fusion-fixed-' + values.position;
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
			 * Builds inline styles.
			 *
			 * @since 3.3
			 * @return {Object}
			 */
			getStyles: function () {
				var css;

				this.baseSelector = '.fusion-scroll-progress-' +  this.model.get( 'cid' );
				this.dynamic_css  = {};

				if ( ! _.isEmpty( this.values.z_index ) && 'flow' !== this.values.position ) {
					this.addCssProperty( this.baseSelector, 'z-index', this.values.z_index, true );
				}

				if ( ! _.isEmpty( this.values.height ) ) {
					this.addCssProperty( this.baseSelector, 'height', this.values.height );
					this.addCssProperty( this.baseSelector + '::-moz-progress-bar', 'height', this.values.height );
					this.addCssProperty( this.baseSelector + '::-webkit-progress-bar', 'height', this.values.height );
					this.addCssProperty( this.baseSelector + '::-webkit-progress-value', 'height', this.values.height );
				}

				if ( ! _.isEmpty( this.values.background_color ) ) {
					this.addCssProperty( this.baseSelector, 'background-color', this.values.background_color );
					this.addCssProperty( this.baseSelector + '::-webkit-progress-bar', 'background-color', this.values.background_color );
				}

				if ( ! _.isEmpty( this.values.progress_color ) ) {
					this.addCssProperty( this.baseSelector + '::-moz-progress-bar', 'background-color', this.values.progress_color );
					this.addCssProperty( this.baseSelector + '::-webkit-progress-value', 'background-color', this.values.progress_color );
				}

				if ( ! _.isEmpty( this.values.border_size ) && ! _.isEmpty( this.values.border_color ) ) {
					this.addCssProperty( this.baseSelector + '::-moz-progress-bar', 'border', _.fusionGetValueWithUnit( this.values.border_size ) + ' solid ' + this.values.border_color );
					this.addCssProperty( this.baseSelector + '::-webkit-progress-value', 'border', _.fusionGetValueWithUnit( this.values.border_size ) + ' solid ' + this.values.border_color );
				}

				if ( ! _.isEmpty( this.values.border_radius ) ) {
					this.addCssProperty( this.baseSelector, 'border-radius', this.values.border_radius );
					this.addCssProperty( this.baseSelector + '::-moz-progress-bar', 'border-radius', this.values.border_radius );
					this.addCssProperty( this.baseSelector + '::-webkit-progress-bar', 'border-radius', this.values.border_radius );
					this.addCssProperty( this.baseSelector + '::-webkit-progress-value', 'border-radius', this.values.border_radius );
				}

				css = this.parseCSS();
				return ( css ) ? '<style>' + css + '</style>' : '';
			}
		} );
	} );
}( jQuery ) );

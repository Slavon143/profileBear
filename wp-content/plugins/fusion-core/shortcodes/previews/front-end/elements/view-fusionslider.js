/* jshint -W024 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Avada Slider Element View.
		FusionPageBuilder.fusion_fusionslider = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs when element is first init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			onInit: function() {
				this.listenTo( window.FusionEvents, 'fusion-iframe-loaded', this.initElement );
			},

			/**
			 * Init Element.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initElement: function() {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-element-render-fusion_fusionslider', this.model.attributes.cid );
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0.0
			 * @return null
			 */
			afterPatch: function() {
				this._refreshJs();
			},

			/**
			 * Runs before view DOM is patched.
			 *
			 * @since 2.0.0
			 * @return null
			 */
			beforePatch: function() {
				var $slider = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.tfs-slider' ) );

				if ( $slider.length && 'undefined' !== typeof $slider.data( 'flexslider' ) ) {
					$slider.flexslider( 'destroy' );
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0.0
			 * @param {Object} atts - The attributes.
			 * @return {void}
			 */
			filterTemplateAtts: function( atts ) {

				// Validate values.
				this.validateValues( atts.values );

				atts.attr   = this.buildAttr( atts.values );
				atts.slider = '';

				if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.sliders ) {
					atts.slider = this.buildSlider( atts );
				}

				// Any extras that need passed on.
				atts.cid    = this.model.get( 'cid' );
				atts.output = atts.values.element_content;

				return atts;
			},

			validateValues: function( values ) {
				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );

			},

			buildAttr: function( values ) {
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-fusionslider-placeholder',
					style: ''
				} );

				attr[ 'class' ] += ' fusion-slider-' + values.name;

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				attr[ 'data-full_height' ]	= 'yes' === values.full_height ? 1 : 0;
				attr[ 'data-offset' ] 		= values.offset;

				if ( '' !== values.margin_top ) {
					attr.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_bottom ) {
					attr.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				return attr;
			},

			buildSlider: function( atts ) {
				var queryData = atts.query_data,
					values    = atts.values,
					slider    = '';

				slider = queryData.sliders[ values.name ].content;

				return slider;
			}
		} );
	} );
}( jQuery ) );

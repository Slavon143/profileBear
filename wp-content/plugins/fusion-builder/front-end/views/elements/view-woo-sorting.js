var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Sorting Component View.
		FusionPageBuilder.fusion_woo_sorting = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.2
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );
				this.values = atts.values;
				this.extras = atts.extras;

				// Any extras that need passed on.
				attributes.cid    = this.model.get( 'cid' );
				attributes.attr   = this.buildAttr( atts.values );
				attributes.styles = this.buildStyleBlock( atts.values );
				attributes.output = this.buildOutput( atts );
				attributes.query_data = atts.query_data;
				attributes.values = atts.values;

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
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
			},

			/**
			 * Builds attributes.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'catalog-ordering fusion-woo-sorting fusion-woo-sorting-' + this.model.get( 'cid' )
					} );

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				attr = _.fusionAnimations( values, attr );

				return attr;
			},

			/**
			 * Builds output.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output = '';

				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.output ) {
					output = atts.query_data.output;
				}

				return output;
			},

			/**
			 * Builds styles.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildStyleBlock: function( values ) {
				var css, selectors;

				this.baseSelector = '.fusion-woo-sorting.fusion-woo-sorting-' +  this.model.get( 'cid' );
				this.dynamic_css  = {};

				selectors = [ this.baseSelector ];

				// Fix z-index issue.
				this.addCssProperty( selectors, 'z-index', '100' );
				this.addCssProperty( selectors, 'position', 'relative' );

				// Margin styles.
				if ( ! this.isDefault( 'margin_top' ) ) {
					this.addCssProperty( selectors, 'margin-top', values.margin_top );
				}
				if ( ! this.isDefault( 'margin_right' ) ) {
					this.addCssProperty( selectors, 'margin-right', values.margin_right );
				}
				if ( ! this.isDefault( 'margin_bottom' ) ) {
					this.addCssProperty( selectors, 'margin-bottom', values.margin_bottom );
				} else {
					this.addCssProperty( selectors, 'margin-bottom', '0px' );
				}
				if ( ! this.isDefault( 'margin_left' ) ) {
					this.addCssProperty( selectors, 'margin-left', values.margin_left );
				}

				selectors = [
					this.baseSelector + ' .order-dropdown .current-li',
					this.baseSelector + ' .order-dropdown ul li a:not(:hover)',
					this.baseSelector + '.catalog-ordering .order li a:not(:hover)',
					this.baseSelector + ' .fusion-grid-list-view li:not(.active-view):not(:hover)'
				];

				// Dropdown bg color.
				if ( ! this.isDefault( 'dropdown_bg_color' ) ) {
					this.addCssProperty( selectors, 'background-color', values.dropdown_bg_color );
				}

				selectors = [
					this.baseSelector + ' .order-dropdown ul li a:hover',
					this.baseSelector + '.catalog-ordering .order li a:hover',
					this.baseSelector + ' .fusion-grid-list-view li:hover',
					this.baseSelector + ' .fusion-grid-list-view li.active-view'
				];

				// Dropdown hover / active bg color.
				if ( ! this.isDefault( 'dropdown_hover_bg_color' ) ) {
					this.addCssProperty( selectors, 'background-color', values.dropdown_hover_bg_color );
				}

				selectors = [
					this.baseSelector + ' .order-dropdown',
					this.baseSelector + ' .order-dropdown a',
					this.baseSelector + ' .order-dropdown ul li a',
					this.baseSelector + ' .order-dropdown a:hover',
					this.baseSelector + ' .order-dropdown > li:after',
					this.baseSelector + ' .order-dropdown ul li a:hover',
					this.baseSelector + '.catalog-ordering .order li a',
					this.baseSelector + ' .fusion-grid-list-view a',
					this.baseSelector + ' .fusion-grid-list-view li:hover',
					this.baseSelector + ' .fusion-grid-list-view li.active-view a i'
				];

				// Dropdown text color.
				if ( ! this.isDefault( 'dropdown_text_color' ) ) {
					this.addCssProperty( selectors, 'color', values.dropdown_text_color );
				}

				selectors = [
					this.baseSelector + ' .order-dropdown > li:after',
					this.baseSelector + ' .order-dropdown .current-li',
					this.baseSelector + ' .order-dropdown ul li a',
					this.baseSelector + '.catalog-ordering .order li a',
					this.baseSelector + ' .fusion-grid-list-view',
					this.baseSelector + ' .fusion-grid-list-view li'
				];

				// Dropdown border color.
				if ( ! this.isDefault( 'dropdown_border_color' ) ) {
					this.addCssProperty( selectors, 'border-color', values.dropdown_border_color );
				}

				css = this.parseCSS();

				return ( css ) ? '<style>' + css + '</style>' : '';

			}

		} );
	} );
}( jQuery ) );

/* eslint no-shadow: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Pricing table stylesiew
		FusionPageBuilder.fusion_pricing_table = FusionPageBuilder.ParentElementView.extend( {

			onInit: function() {
				var params = this.model.get( 'params' );
				if ( 'undefined' === typeof params.background_color_hover && 'undefined' !== typeof params.bordercolor && '' !== params.bordercolor ) {
					params.background_color_hover = params.bordercolor;
				}
			},

			beforeGenerateShortcode: function() {
				this.updateElementContent();
			},

			onRender: function() {
				var columns = 6,
					params  = this.model.get( 'params' );

				if ( 'undefined' === typeof params.columns && 'undefined' !== typeof this.model.children && this.model.children.length ) {
					if ( 6 > this.model.children.length ) {
						columns = this.model.children.length;
					}
					params.columns = columns;
					this.model.set( 'params', params );
					this.updateColumnWidths();
				}
			},

			childViewAdded: function() {
				this.updateColumnWidths();
			},

			childViewRemoved: function() {
				this.updateColumnWidths();
			},

			childViewCloned: function() {
				this.updateColumnWidths();
			},

			updateColumnWidths: function() {
				var params  = this.model.get( 'params' ),
					columns = 'undefined' !== typeof this.model.children ? this.model.children.length : 0,
					values,
					attr;

				// Calculate columns.
				if ( 6 < columns ) {
					columns = 6;
				}

				params.columns = columns;
				this.model.set( 'params', params );

				// Update classes on parent.
				values = jQuery.extend( true, {}, window.fusionAllElements[ this.model.get( 'element_type' ) ].defaults, _.fusionCleanParameters( params ) );
				attr   = this.computeTableData( values );
				this.$el.find( '.fusion-child-element' ).attr( 'class', attr[ 'class' ] );

				// Update classes on each child.
				this.model.children.each( function( child ) {
					var cid    = child.attributes.cid,
						view   = window.FusionPageBuilderViewManager.getView( cid ),
						values = jQuery.extend( true, {}, window.fusionAllElements[ view.model.get( 'element_type' ) ].defaults, _.fusionCleanParameters( view.model.get( 'params' ) ) );

					view.buildColumnWrapperAttr( values, columns );
					view.onRender();
				} );

			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				// Validate values.
				this.validateValues( atts.values );

				return {
					styles: this.computeStyles( atts.values ),
					tableData: this.computeTableData( atts.values )
				};
			},

			/**
			 * Modifies values.
			 *
			 * @since 3.8
			 * @param {Object} values - The values.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );
			},

			/**
			 * Builds the data for the table.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			computeTableData: function( values ) {
				var type      = 'sep',
					cid       = this.model.get( 'cid' ),
					tableData = {
						class: '',
						style: ''
					};

				if ( '1' == values.type ) {
					type = 'full';
				}

				if ( 6 < values.columns ) {
					values.columns = 6;
				}

				tableData[ 'class' ] = 'fusion-child-element fusion-pricing-table pricing-table-cid' + cid + ' ' + type + '-boxed-pricing row fusion-columns-' + values.columns + ' columns-' + values.columns + ' fusion-clearfix';

				tableData[ 'data-empty' ] = this.emptyPlaceholderText;

				tableData = _.fusionVisibilityAtts( values.hide_on_mobile, tableData );

				if ( 'undefined' !== typeof values[ 'class' ] && '' !== values[ 'class' ] ) {
					tableData[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.margin_top ) {
					tableData.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_right ) {
					tableData.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( '' !== values.margin_bottom ) {
					tableData.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( '' !== values.margin_left ) {
					tableData.style += 'margin-left:' + values.margin_left + ';';
				}

				if (  'undefined' !== typeof values.id && '' !== values.id ) {
					tableData.id = values.id;
				}

				return tableData;
			},

			/**
			 * Builds the styles.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {string}
			 */
			computeStyles: function( values ) {
				var styles,
					cid = this.model.get( 'cid' );

				styles = '<style type="text/css">.pricing-table-cid' + cid + ' .panel-container, .pricing-table-cid' + cid + ' .standout .panel-container,.pricing-table-cid' + cid + '.full-boxed-pricing { background-color: ' + values.bordercolor + ';}.pricing-table-cid' + cid + ' .list-group .list-group-item,.pricing-table-cid' + cid + ' .list-group .list-group-item:last-child{ background-color:' + values.backgroundcolor + '; border-color:' + values.dividercolor + ';}.pricing-table-cid' + cid + '.full-boxed-pricing .panel-wrapper:hover .panel-heading,.full-boxed-pricing .panel-wrapper.hover .panel-heading,.pricing-table-cid' + cid + ' .panel-wrapper:hover .list-group-item,.pricing-table-cid' + cid + ' .panel-wrapper.hover .list-group-item { background-color:' + values.background_color_hover + ';}.pricing-table-cid' + cid + '.full-boxed-pricing .panel-heading{ background-color:' + values.backgroundcolor + ';}.pricing-table-cid' + cid + ' .fusion-panel, .pricing-table-cid' + cid + ' .panel-wrapper:last-child .fusion-panel,.pricing-table-cid' + cid + ' .standout .fusion-panel, .pricing-table-cid' + cid + '  .panel-heading,.pricing-table-cid' + cid + ' .panel-body, .pricing-table-cid' + cid + ' .panel-footer{ border-color:' + values.dividercolor + ';}.pricing-table-cid' + cid + ' .panel-body,.pricing-table-cid' + cid + ' .panel-footer{ background-color:' + values.bordercolor + ';}.pricing-table-cid' + cid + '.sep-boxed-pricing .panel-heading h3{color:' + values.heading_color_style_2 + ';}.pricing-table-cid' + cid + '.full-boxed-pricing.fusion-pricing-table .panel-heading h3{color:' + values.heading_color_style_1 + ';}.pricing-table-cid' + cid + '.fusion-pricing-table .panel-body .price .decimal-part{color:' + values.pricing_color + ';}.pricing-table-cid' + cid + '.fusion-pricing-table .panel-body .price .integer-part{color:' + values.pricing_color + ';}.pricing-table-cid' + cid + ' ul.list-group li{color:' + values.body_text_color + ';}</style>';

				return styles;
			}

		} );
	} );
}( jQuery ) );

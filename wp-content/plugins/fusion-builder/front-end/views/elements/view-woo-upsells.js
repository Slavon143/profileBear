/* global fusionBuilderText */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Related Component View.
		FusionPageBuilder.fusion_tb_woo_upsells = FusionPageBuilder.WooProductsView.extend( {

			/**
			 * Define shortcode handle.
			 *
			 * @since  3.2
			 */
			shortcode_handle: 'fusion_tb_woo_upsells',

			/**
			 * Define shortcode classname.
			 *
			 * @since  3.2
			 */
			shortcode_classname: 'fusion-woo-upsells-tb',

			/**
			 * Builds attributes.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {

				var attr = FusionPageBuilder.WooProductsView.prototype.buildAttr.call( this, values );

				if ( 'up-sells' === this.query_data.query_type ) {
					attr[ 'class' ] += ' up-sells upsells products';
				} else {
					attr[ 'class' ] += ' fusion-woo-cross-sells products cross-sells';
				}

				return attr;
			},

			/**
			 * Get section title based on the post type.
			 *
			 * @since 3.2
			 * @return {string}
			 */
			getSectionTitle: function() {
				if ( 'up-sells' === this.query_data.query_type ) {
					return fusionBuilderText.upsells_products;
				}
					return fusionBuilderText.cross_sells_products;

			}

		} );
	} );
}( jQuery ) );

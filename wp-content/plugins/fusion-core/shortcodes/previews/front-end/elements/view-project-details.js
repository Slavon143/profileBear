/* global fusionBuilderText */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Content Component View.
		FusionPageBuilder.fusion_tb_project_details = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				this._refreshJs();
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.2
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Any extras that need passed on.
				attributes.cid          = this.model.get( 'cid' );
				attributes.wrapperAttr  = this.buildAttr( atts.values );
				attributes.titleElement = 'yes' === atts.values.heading_enable ? _.buildTitleElement( atts.values, atts.extras, this.getSectionTitle() ) : '';
				attributes.author 		= 'yes' === atts.values.author || 1 == atts.values.author;

				attributes.query_data   = atts.query_data;

				return attributes;
			},

			/**
			 * Builds attributes.
			 *
			 * @since  2.2
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-project-details-tb fusion-project-details-tb-' + this.model.get( 'cid' ),
						style: ''
					} );

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
			 * Get section title.
			 *
			 * @since 2.2
			 * @return {string}
			 */
			getSectionTitle: function() {
				return fusionBuilderText.project_details;
			}
		} );
	} );
}( jQuery ) );

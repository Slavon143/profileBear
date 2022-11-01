var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Counter circle child View
		FusionPageBuilder.fusion_flip_boxes = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
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
				var flipBoxesShortcode = this.computeAtts( atts.values );

				atts = {};
				atts.flipBoxesShortcode = flipBoxesShortcode;

				return atts;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			computeAtts: function( values ) {
				var flipBoxesShortcode;

				// Backwards compatibility for when we had image width and height params.
				if ( 'undefined' !== typeof values.image_width ) {
					values.image_width = values.image_width ? values.image_width : '35';
				} else {
					values.image_width = values.image_max_width;
				}

				values.columns = Math.min( 6, values.columns );

				flipBoxesShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-flip-boxes flip-boxes row fusion-columns-' + values.columns
				} );

				flipBoxesShortcode[ 'class' ] += ' flip-effect-' + values.flip_effect;

				if ( 'yes' === values.equal_heights ) {
					flipBoxesShortcode[ 'class' ] += ' equal-heights';
				}

				if ( '' !== values[ 'class' ] ) {
					flipBoxesShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					flipBoxesShortcode.id += ' ' + values.id;
				}

				flipBoxesShortcode.style = '';
				if ( '' !== values.margin_top ) {
					flipBoxesShortcode.style += 'margin-top:' + values.margin_top + ';';
				}
				if ( '' !== values.margin_right ) {
					flipBoxesShortcode.style += 'margin-right:' + values.margin_right + ';';
				}
				if ( '' !== values.margin_bottom ) {
					flipBoxesShortcode.style += 'margin-bottom:' + values.margin_bottom + ';';
				}
				if ( '' !== values.margin_left ) {
					flipBoxesShortcode.style += 'margin-left:' + values.margin_left + ';';
				}

				flipBoxesShortcode[ 'class' ] += ' fusion-child-element';
				flipBoxesShortcode[ 'data-empty' ] = this.emptyPlaceholderText;

				return flipBoxesShortcode;
			}

		} );
	} );
}( jQuery ) );

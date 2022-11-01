var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Counter circles parent View
		FusionPageBuilder.fusion_counters_circle = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				this.appendChildren( '.fusion-counters-circle' );

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
				var countersCircleAtts;

				// Validate values.
				this.validateValues( atts.values );

				countersCircleAtts = this.computeAtts( atts.values );

				atts = {};
				atts.countersCircleAtts = countersCircleAtts;

				return atts;
			},

			/**
			 * Modifies values.
			 *
			 * @since 3.8
			 * @param {Object} values - The values.
			 * @param {Object} params - The parameters.
			 * @return {void}
			 */
			validateValues: function( values ) {

				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			computeAtts: function( values ) {
				var countersCircleAtts = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-counters-circle counters-circle',
					style: ''
				} );

				if ( '' !== values[ 'class' ] ) {
					countersCircleAtts[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					countersCircleAtts.id += ' ' + values.id;
				}

				if ( '' !== values.margin_top ) {
					countersCircleAtts.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_right ) {
					countersCircleAtts.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( '' !== values.margin_bottom ) {
					countersCircleAtts.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( '' !== values.margin_left ) {
					countersCircleAtts.style += 'margin-left:' + values.margin_left + ';';
				}

				countersCircleAtts[ 'class' ] += ' fusion-child-element';

				countersCircleAtts[ 'data-empty' ] = this.emptyPlaceholderText;

				return countersCircleAtts;
			}

		} );
	} );
}( jQuery ) );

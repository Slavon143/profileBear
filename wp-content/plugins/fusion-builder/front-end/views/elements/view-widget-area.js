/* global FusionApp */
/* eslint no-unused-vars: 0 */
/* eslint no-shadow: 0 */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Widget Area Element View.
		FusionPageBuilder.fusion_widget_area = FusionPageBuilder.ElementView.extend( {

			/**
			 * Fires during element render() function.
			 *
			 * @since 2.0.4
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			onRender: function() {
				var elementContent = this.$el.html();

				this.$el.html( FusionApp.removeScripts( elementContent ) );
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
					name       = 'undefined' !== typeof atts.values.name ? atts.values.name.replace( /-/g, '_' ) : '';

				// Validate values.
				this.validateValues( atts.values );

				// Create attribute objects
				attributes.attr   = this.buildAttr( atts.values );
				attributes.styles = this.buildStyling( atts.values );

				attributes.widgetArea = false;
				if ( 'undefined' !== atts.query_data && 'undefined' !== typeof atts.query_data[ name ] ) {
					attributes.widgetArea = atts.query_data[ name ];
				}

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values = _.fusionGetPadding( values );

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
			buildAttr: function( values ) {
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-widget-area fusion-content-widget-area',
						style: ''
					} ),
					cid = this.model.get( 'cid' );

				attr[ 'class' ] += ' fusion-widget-area-cid' + cid;

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

				return attr;
			},

			/**
			 * Builds styles.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {string}
			 */
			buildStyling: function( values ) {
				var styles  = '',
					padding = '',
					cid     = this.model.get( 'cid' );

				if ( '' !== values.background_color ) {
					styles += '.fusion-widget-area-cid' + cid + ' {background-color:' + values.background_color + ';}';
				}

				if ( '' !== values.padding ) {
					if ( -1 === values.padding.indexOf( '%' ) && -1 === values.padding.indexOf( 'px' ) ) {
						values.padding = values.padding + 'px';
					}

					padding = _.fusionGetValueWithUnit( values.padding );
					styles += '.fusion-widget-area-cid' + cid + ' {padding:' + padding + ';}';
				}

				if ( '' !== values.title_color ) {

					styles += '.fusion-widget-area-cid' + cid + ' .widget h4 {color:' + values.title_color + ';}';
					styles += '.fusion-widget-area-cid' + cid + ' .widget .heading h4 {color:' + values.title_color + ';}';
				}

				if ( '' !== values.title_size ) {

					styles += '.fusion-widget-area-cid' + cid + ' .widget h4 {font-size:' + values.title_size + ';}';
					styles += '.fusion-widget-area-cid' + cid + ' .widget .heading h4 {font-size:' + values.title_size + ';}';
				}

				return styles;
			}
		} );

		// Widget Area Callback.
		_.extend( FusionPageBuilder.Callback.prototype, {
			fusion_widget_area: function( name, value, modelData, args, cid, action, model, view ) { // jshint ignore: line
				var queryName,
					params   = jQuery.extend( true, {}, modelData.params ),
					ajaxData = {};

				if ( 'undefined' !== typeof name && ! args.skip ) {
					params[ name ] = value;
				}
				ajaxData.params = jQuery.extend( true, {}, window.fusionAllElements[ modelData.element_type ].defaults, _.fusionCleanParameters( params ) );

				if ( 'undefined' !== typeof name && 'undefined' !== typeof value ) {
					queryName = value.replace( /-/g, '_' );
				}

				if ( 'undefined' !== typeof model.query_data && 'undefined' !== typeof model.query_data[ queryName ] && 'undefined' !== typeof view ) {
					view.reRender();
					return true;
				}

				// Send this data with ajax or rest.
				jQuery.ajax( {
					url: window.fusionAppConfig.ajaxurl,
					type: 'post',
					dataType: 'json',
					data: {
						action: 'get_widget_area',
						model: ajaxData,
						fusion_load_nonce: window.fusionAppConfig.fusion_load_nonce
					}
				} )
				.done( function( response ) {
					var queryData;

					if ( 'undefined' !== typeof model && 'undefined' !== typeof model.get( 'query_data' ) ) {
						queryData              = model.get( 'query_data' );
						queryData[ queryName ] = response[ queryName ];
					} else {
						queryData = response;
					}

					model.set( 'query_data', queryData );

					if ( ! args.skip && 'undefined' !== typeof name ) {
						view.changeParam( name, value );
					}

					if ( 'generated_element' !== model.get( 'type' ) ) {
						view.reRender();
					}
				} );
			}
		} );
	} );
}( jQuery ) );

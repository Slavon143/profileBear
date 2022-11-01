/* global FusionApp, FusionPageBuilderApp, fusionAllElements, FusionPageBuilderViewManager */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Notice View.
		FusionPageBuilder.fusion_form_notice = FusionPageBuilder.FormComponentView.extend( {
			alertInstance: {},

			onInit: function() {
				this.formData = FusionApp.data.postMeta;
				this.listenTo( window.FusionEvents, 'fusion-render-form-notices', this.reRender );
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 3.1
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
                var attributes = {};

                this.values = atts.values;

                // Whether we should show warning instead.
				attributes.showNotices  = 'undefined' === typeof this.formData._fusion.form_confirmation_type || 'redirect' !== this.formData._fusion.form_confirmation_type;
                attributes.successAlert = '';
                attributes.errorAlert   = '';
				if ( ! attributes.showNotices ) {
					return attributes;
				}

                /*
            	attributes.name   = atts.values.label;
				attributes.label  = window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				attributes.icon   = window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;
				*/

				atts.values.margin_bottom = _.fusionValidateAttrValue( atts.values.margin_bottom, 'px' );
				atts.values.margin_left   = _.fusionValidateAttrValue( atts.values.margin_left, 'px' );
				atts.values.margin_right  = _.fusionValidateAttrValue( atts.values.margin_right, 'px' );
				atts.values.margin_top    = _.fusionValidateAttrValue( atts.values.margin_top, 'px' );

				// Default alert bottom margin.
				if ( '' === atts.values.margin_bottom ) {
					atts.values.margin_bottom = '20px';
				}
				attributes.style = '';
				if ( '' !== atts.values.margin_top ) {
					attributes.style += 'margin-top:' + atts.values.margin_top + ';';
				}
				if ( '' !== atts.values.margin_right ) {
					attributes.style += 'margin-right:' + atts.values.margin_right + ';';
				}
				if ( '' !== atts.values.margin_bottom ) {
					attributes.style += 'margin-bottom:' + atts.values.margin_bottom + ';';
				}
				if ( '' !== atts.values.margin_left ) {
					attributes.style += 'margin-left:' + atts.values.margin_left + ';';
				}

				attributes.successAlert = '' !== atts.values.success ? this.renderAlert( atts.values.success, 'success' ) : '';
				attributes.errorAlert   = '' !== atts.values.error ? this.renderAlert( atts.values.error, 'error' ) : '';
				return attributes;
			},

			renderAlert: function( content, alertType ) {
				var shortcodeType    = 'fusion_alert',
					newParams,
					shortcodeContent = content,
					defaultParams,
					params,
					type,
					elementSettings,
					elementModel;

				try {
					if ( FusionPageBuilderApp.base64Encode( FusionPageBuilderApp.base64Decode( shortcodeContent ) ) === shortcodeContent ) {
						shortcodeContent = FusionPageBuilderApp.base64Decode( shortcodeContent );
						shortcodeContent = _.unescape( shortcodeContent );
					}
				} catch ( error ) {
					console.log( error ); // jshint ignore:line
				}

				if ( 'undefined' === typeof this.alertInstance[ alertType ] ) {
					if ( shortcodeType in fusionAllElements ) {
						defaultParams  = fusionAllElements[ shortcodeType ].params;
						type           = fusionAllElements[ shortcodeType ].shortcode;
					}

					params = {};

					// Process default parameters from shortcode
					_.each( defaultParams, function( param )  {
						params[ param.param_name ] = ( _.isObject( param.value ) ) ? param[ 'default' ] : param.value;
					} );

					// Used as a flag for opening on first render.
					params.open_settings   = 'false';
					params.element_content = shortcodeContent;
					params.type            = alertType;
					params.margin_top      = this.values.margin_top;
					params.margin_right    = this.values.margin_right;
					params.margin_bottom   = this.values.margin_bottom;
					params.margin_left     = this.values.margin_left;

					elementSettings = {
						type: 'element',
						added: 'manually',
						element_type: type,
						params: params,
						parent: this.model.get( 'cid' ),
						multi: false,
						cid: FusionPageBuilderViewManager.generateCid(),
						silent: true
					};

					elementModel = new FusionPageBuilder.Element( elementSettings );

					this.alertInstance[ alertType ] = new FusionPageBuilder.fusion_alert( {
						model: elementModel
					} );
				} else {
					newParams = this.alertInstance[ alertType ].model.get( 'params' );

					newParams.element_content = shortcodeContent;
					newParams.margin_top      = this.values.margin_top;
					newParams.margin_right    = this.values.margin_right;
					newParams.margin_bottom   = this.values.margin_bottom;
					newParams.margin_left     = this.values.margin_left;

					this.alertInstance[ alertType ].model.set( 'params', newParams );
				}
				return this.alertInstance[ alertType ].render().$el.html();
			}

		} );
	} );
}( jQuery ) );

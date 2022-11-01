/* global FusionApp, FusionPageBuilderApp, fusionBuilderText */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Password View.
		FusionPageBuilder.FormComponentView = FusionPageBuilder.ElementView.extend( {

			onInit: function() {
				this.formData = FusionApp.data.postMeta;
				this.listenTo( window.FusionEvents, 'fusion-rerender-form-inputs', this.reRender );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 3.1
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildStyles: function() {
				return '';
			},

			elementData: function( values ) {
				var data  = {},
					patternFields = [ 'fusion_form_email', 'fusion_form_password', 'fusion_form_phone_number' ];

				data.checked               = '';
				data.required              = '';
				data.required_label        = '';
				data.required_placeholder  = '';
				data.disabled              = '';
				data[ 'class' ]            = '';
				data.id                    = '';
				data.placeholder           = '';
				data.label                 = '';
				data.label_class           = '';
				data.holds_private_data    = 'no';
				data.upload_size           = '';
				data.pattern			         = '';

				if ( 'undefined' === typeof values ) {
					return data;
				}

				data.pattern = 'fusion_form_text' === this.model.get( 'element_type' ) && 'undefined' !== typeof values.pattern && '' !== values.pattern ? this.addPattern( values ) : '';
				data.pattern = patternFields.includes( this.model.get( 'element_type' ) ) && 'undefined' !== typeof values.pattern && '' !== values.pattern ? ' pattern="' + this.decodePattern( values.pattern ) + '" ' : '';

				if ( 'fusion_form_checkbox' === this.model.get( 'element_type' ) && 'undefined' !== typeof values.checked && values.checked ) {
					data.checked = ' checked="checked"';
				}

				if ( 'fusion_form_upload' === this.model.get( 'element_type' ) && 'undefined' !== typeof values.upload_size && values.upload_size ) {
					data.upload_size = ' data-size="' + values.upload_size + '"';
				}

				if ( 'undefined' !== typeof values.required && ( 'yes' === values.required || 'selection' === values.required ) ) {
					if ( 'selection' !== values.required ) {
						data.required             = ' required="true" aria-required="true"';
					}
					data.required_label       = ' <abbr class="fusion-form-element-required" title="' + fusionBuilderText.required + '">*</abbr>';
					data.required_placeholder = '*';
				}

				if ( 'undefined' !== typeof values.disabled && 'yes' === values.disabled ) {
					data.disabled = ' disabled';

					if ( 'undefined' !== typeof values.placeholder && '' !== values.placeholder ) {
						data.value = values.placeholder;
					}
				}

				data[ 'class' ] = ' class="fusion-form-input"';

				if ( 'undefined' !== typeof values.placeholder && '' !== values.placeholder ) {
					if ( 'fusion_form_dropdown' === this.model.get( 'element_type' ) ) {
						data.placeholder = values.placeholder + data.required_placeholder;
					} else {
						data.placeholder = ' placeholder="' + values.placeholder + data.required_placeholder + '"';
					}
				}

				if ( 'fusion_form_checkbox' === this.model.get( 'element_type' ) ) {
					data.label_class = ' class="fusion-form-checkbox-label"';
				}

				if ( 'undefined' !== typeof values.label && '' !== values.label ) {
					data.label = '<label for="' + values.name + '"' + data.label_class + '>' + values.label + data.required_label + '</label>';
				}

				data.holds_private_data = ' data-holds-private-data="false"';

				if ( 'undefined' !== typeof values.holds_private_data && '' !== values.holds_private_data ) {
					data.holds_private_data = ' data-holds-private-data="true"';
				}

				return data;
			},

			decodePattern: function( content ) {
				let decodedPattern = '';

				try {
					if ( FusionPageBuilderApp.base64Encode( FusionPageBuilderApp.base64Decode( content ) ) === content ) {
						decodedPattern = FusionPageBuilderApp.base64Decode( content );
						decodedPattern = _.unescape( decodedPattern );
					}
				} catch ( error ) {
					console.log( error ); // jshint ignore:line
				}

				return decodedPattern;
			},

			checkbox: function( values, type ) {
				var options	= '',
					elementData,
					elementName,
					elementHtml,
					checkboxClass,
					html = '';

				if ( 'undefined' === typeof values.options || ! values.options ) {
					return html;
				}

				values.options = JSON.parse( FusionPageBuilderApp.base64Decode( values.options ) );

				elementData = this.elementData( values );

				_.each( values.options, function( option, key ) {
					var checked = option[ 0 ] ? ' checked ' : '',
						label   = ( 'undefined' !== typeof option[ 1 ] ) ? option[ 1 ].trim() : '',
						value   = ! _.isEmpty( option[ 2 ] ) ? option[ 2 ].trim() : label, // eslint-disable-line no-unused-vars
						labelId;

					elementName   = ( 'checkbox' === type ) ? values.name + '[]' : values.name;
					checkboxClass = ( 'floated' === values.form_field_layout ) ? 'fusion-form-' + type + ' option-inline' : 'fusion-form-' + type;
					labelId       = type + '-' + label.replace( ' ', '-' ).toLowerCase() + '-' + key;

					options       += '<div class="' + checkboxClass + '">';
					options       += '<input id="' + labelId + '" type="' + type + '" value="' + label + '" name="' + elementName + '"' + elementData[ 'class' ] + elementData.id + elementData.required + checked + elementData.holds_private_data + '/>';
					options       += '<label for="' + labelId + '">';
					options       += label + '</label>';
					options       += '</div>';
				} );

				elementHtml = '<fieldset>';
				elementHtml += options;
				elementHtml += '</fieldset>';

				if ( '' !== values.tooltip ) {
					elementData.label += this.getFieldTooltip( values );
				}

				html = this.generateLabelHtml( html, elementHtml, elementData.label );

				return html;
			},

			generateInputField: function( values, type ) {
				var elementData,
					elementHtml,
					html = '';

				elementData = this.elementData( values );

				if ( '' !== values.tooltip ) {
					elementData.label += this.getFieldTooltip( values );
				}

				values.value = 'undefined' !== typeof values.value && '' !== values.value ? values.value : '';
				values.value = 'undefined' !== typeof elementData.value && '' !== elementData.value ? elementData.value : values.value;

				elementHtml = '<input type="' + type + '" name="' + values.name + '" value="' + values.value + '" ' + elementData[ 'class' ] + elementData.id + elementData.required + elementData.disabled + elementData.placeholder + elementData.holds_private_data + elementData.pattern + '/>';

				elementHtml = this.generateIconHtml( values, elementHtml );

				html = this.generateLabelHtml( html, elementHtml, elementData.label );

				return html;
			},

			addPattern: function( values ) {
				var patterns = {
					'letters': '[a-zA-Z]+',
					'alpha_numeric': '[a-zA-Z0-9]+',
					'number': '[0-9]+',
					'credit_card_number': '[0-9]{13,16}',
					'phone': '[0-9()#&+*-=.]+',
					// eslint-disable-next-line no-useless-escape
					'url': '(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)'
				};

				return 'undefined' !== typeof patterns[ values.pattern ] ? ' pattern="' + patterns[ values.pattern ] + '"' : ' pattern="' + this.decodePattern( values.custom_pattern ) + '"';
			},

			getFieldTooltip: function( values ) {
				var html = '';

				if ( '' !== values.tooltip ) {
					html = '<div class="fusion-form-tooltip">';
					html += '<i class="awb-icon-question-circle"></i>';
					html += '<span class="fusion-form-tooltip-content">' + values.tooltip + '</span>';
					html += '</div>';
				}

				return html;
			},

			addFieldWrapperHtml: function() {
				var html,
					labelPosition = 'above',
					params = this.model.get( 'params' );


				if ( 'undefined' !== typeof this.formData._fusion.label_position ) {
					labelPosition = this.formData._fusion.label_position;
				}

				html = '<div ';

				// Add custom ID if it's there.
				if ( 'undefined' !== typeof params.id && '' !== params.id ) {
					html += 'id="' + params.id + '" ';
				}

				// Start building class.
				html += 'class="fusion-form-field ' + this.model.get( 'element_type' ).replace( /_/g, '-' ) + '-field ' + this.model.get( 'cid' ) + ' ' + this.model.get( 'element_type' ).replace( /_/g, '-' ) + '-field fusion-form-label-' + labelPosition;

				// Add custom class if it's there.
				if ( 'undefined' !== typeof params[ 'class' ] && '' !== params[ 'class' ] ) {
					html += ' ' + params[ 'class' ];
				}

				// Close class quotes.
				html += '"';

				html += ' data-form-id="' + FusionApp.data.postDetails.post_id + '">';

				return html;
			},

			generateFormFieldHtml: function( fieldHtml ) {
				var html = this.addFieldWrapperHtml();
				html += fieldHtml;
				html += '</div>';

				return html;
			},

			generateIconHtml: function( atts, html ) {
				var icon;

				if ( 'undefined' !== typeof atts.input_field_icon && '' !== atts.input_field_icon ) {
					icon = '<div class="fusion-form-input-with-icon">';
					icon += '<i class="' + _.fusionFontAwesome( atts.input_field_icon ) + '"></i>';
					html = icon + html;
					html += '</div>';
				}

				return html;
			},

			generateLabelHtml: function( html, elementHtml, label ) {

				if ( '' !== label ) {
					label = '<div class="fusion-form-label-wrapper">' + label + '</div>';
				}

				if ( 'undefined' === typeof this.formData._fusion.label_position || 'above' === this.formData._fusion.label_position ) {
					html += label + elementHtml;
				} else {
					html += elementHtml + label;
				}

				return html;
			},

			generateTooltipHtml: function( values, elementData ) {
				if ( '' !== values.tooltip ) {
					elementData.label += this.getFieldTooltip( values );
				}

				return elementData;
			}

		} );
	} );
}( jQuery ) );

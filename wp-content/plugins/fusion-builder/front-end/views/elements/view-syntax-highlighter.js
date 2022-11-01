/* global FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Syntax Highlighter Element View.
		FusionPageBuilder.fusion_syntax_highlighter = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				var self = this;

				setTimeout( function() {
					self.fusionSyntaxHighlighter( self.$el.find( 'textarea' )[ 0 ] );
				}, 500 );
			},

			/**
			 * Runs before view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforePatch: function() {
				this.syntaxHighlighter.toTextArea();
				this.$el.find( '.CodeMirror' ).remove();
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				jQuery( this.$el.find( 'textarea' )[ 0 ] ).val( this.output );
				this.fusionSyntaxHighlighter( this.$el.find( 'textarea' )[ 0 ] );
			},

			/**
			 * Init highlighter.
			 *
			 * @since 2.0
			 * @param {Object} syntaxHighlighterTextarea - selector object.
			 * @return {void}
			 */
			fusionSyntaxHighlighter: function( syntaxHighlighterTextarea ) {
				var syntaxHighlighter,
					syntaxHighlighterSettings;

				// Set settings to empty for each highlighter.
				syntaxHighlighterSettings = {};

				// Set custom values as per the settings set by user.
				syntaxHighlighterSettings.readOnly     = ( 'undefined' !== typeof jQuery( syntaxHighlighterTextarea ).attr( 'data-readonly' ) ) ? jQuery( syntaxHighlighterTextarea ).attr( 'data-readonly' ) : false;
				syntaxHighlighterSettings.lineNumbers  = ( 'undefined' !== typeof jQuery( syntaxHighlighterTextarea ).attr( 'data-linenumbers' ) ) ? jQuery( syntaxHighlighterTextarea ).attr( 'data-linenumbers' ) : false;
				syntaxHighlighterSettings.lineWrapping = ( 'undefined' !== typeof jQuery( syntaxHighlighterTextarea ).attr( 'data-linewrapping' ) ) ? jQuery( syntaxHighlighterTextarea ).attr( 'data-linewrapping' ) : false;
				syntaxHighlighterSettings.theme        = ( 'undefined' !== typeof jQuery( syntaxHighlighterTextarea ).attr( 'data-theme' ) ) ? jQuery( syntaxHighlighterTextarea ).attr( 'data-theme' ) : 'default';
				syntaxHighlighterSettings.mode         = ( 'undefined' !== typeof jQuery( syntaxHighlighterTextarea ).attr( 'data-mode' ) ) ? jQuery( syntaxHighlighterTextarea ).attr( 'data-mode' ) : 'text/html';

				// Instantiate new CodeMirror for each highlighter.
				syntaxHighlighter = wp.CodeMirror.fromTextArea( syntaxHighlighterTextarea, syntaxHighlighterSettings );
				jQuery( syntaxHighlighterTextarea ).addClass( 'code-mirror-initialized' );
				jQuery( syntaxHighlighterTextarea ).data( 'code-mirror', syntaxHighlighter );

				// Make sure the highlighter don't add extra lines.
				syntaxHighlighter.setSize( '100%', 'auto' );
				jQuery( document ).trigger( 'resize' );
				jQuery( syntaxHighlighterTextarea ).closest( '.fusion-syntax-highlighter-container' ).css( 'opacity', '1' );

				this.syntaxHighlighter = syntaxHighlighter;
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );
				this.extras = atts.extras;

				// Create attribute objects
				attributes.syntaxAttr                         = this.buildSyntaxAttr( atts.values );
				attributes.textareaAttr                       = this.buildTextareaAttr( atts.values );
				attributes.syntaxHighlighterCopyCodeTitleAttr = this.buildSyntaxHighlighterCopyCodeTitleAttr( atts.values );
				attributes.styles                             = this.buildStyles( atts.values );

				// Any extras that need passed on.
				attributes.cid                    = this.model.get( 'cid' );
				attributes.output                 = atts.values.element_content;
				this.output                       = atts.values.element_content;
				attributes.wp_enqueue_code_editor = atts.extras.wp_enqueue_code_editor;
				attributes.copy_to_clipboard      = atts.values.copy_to_clipboard;
				attributes.copy_to_clipboard_text = atts.values.copy_to_clipboard_text;

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

				// Validate margin values.
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );

				// Validate border size value.
				values.border_size = _.fusionValidateAttrValue( values.border_size, 'px' );

				// Validate font size value.
				values.font_size = _.fusionValidateAttrValue( values.font_size, 'px' );

				// Set background color to TO value if theme is set to TO default.
				if ( '' === values.theme ) {
					values.background_color = this.extras.syntax_highlighter_background_color;
				}

				if ( 'undefined' !== typeof values.element_content && '' !== values.element_content ) {
					if ( values.element_content && FusionPageBuilderApp.base64Encode( FusionPageBuilderApp.base64Decode( values.element_content ) ) === values.element_content ) {
						values.element_content = FusionPageBuilderApp.base64Decode( values.element_content );
					}

					// Remove br tags added by WP from the code.
					values.element_content = values.element_content.replace( /<br \/>/g, '' );
				}
			},

			/**
			 * Builds main attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildSyntaxAttr: function( values ) {
				var theme      = '',
					syntaxAttr = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-syntax-highlighter-container',
						style: ''
					} );

				syntaxAttr[ 'class' ] += ' fusion-syntax-highlighter-cid' + this.model.get( 'cid' );

				theme = ( 'default' === values.theme || 'elegant' === values.theme ) ? 'light' : 'dark';
				syntaxAttr[ 'class' ] += ' fusion-syntax-highlighter-theme-' + theme;

				if ( 'undefined' !== typeof values[ 'class' ] && '' !== values[ 'class' ] ) {
					syntaxAttr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( 'undefined' !== typeof values.id && '' !== values.id ) {
					syntaxAttr.id = values.id;
				}

				if ( 'undefined' !== typeof values.margin_top && '' !== values.margin_top ) {
					syntaxAttr.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( 'undefined' !== typeof values.margin_left && '' !== values.margin_left ) {
					syntaxAttr.style += 'margin-left:' + values.margin_left + ';';
				}

				if ( 'undefined' !== typeof values.margin_bottom && '' !== values.margin_bottom ) {
					syntaxAttr.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( 'undefined' !== typeof values.margin_right && '' !== values.margin_right ) {
					syntaxAttr.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( 'undefined' !== typeof values.font_size && '' !== values.font_size ) {
					syntaxAttr.style += 'font-size:' + values.font_size + ';';
				}

				if ( 'undefined' !== typeof values.border_size && '' !== values.border_size ) {
					syntaxAttr.style += 'border-width:' + values.border_size + ';';

					if ( '' !== values.border_style ) {
						syntaxAttr.style += 'border-style:' + values.border_style + ';';
					}

					if ( '' !== values.border_color ) {
						syntaxAttr.style += 'border-color:' + values.border_color + ';';
					}
				}

				// Compatibility for WP < 4.9.
				if ( ! this.extras.wp_enqueue_code_editor && '' !== values.background_color ) {
					syntaxAttr.style += 'background-color:' + values.background_color + ';';
					syntaxAttr.style += 'padding: 0 1em';
				}

				return syntaxAttr;
			},

			/**
			 * Builds text area attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildTextareaAttr: function( values ) {

				// Text area attr.
				var textareaAttr = {
						class: 'fusion-syntax-highlighter-textarea',
						id: 'fusion_syntax_highlighter_' + this.model.get( 'cid' ),
						style: ''
					},
					languageType,
					settings = {
						readOnly: 'nocursor',
						lineNumbers: 'yes' === values.line_numbers ? true : '',
						lineWrapping: 'break' === values.line_wrapping ? true : '',
						theme: values.theme
					};

				if ( '' !== values.language ) {
					languageType = 'json' === values.language || 'xml' === values.language ? 'application' : 'text';
					settings.mode = languageType + '/' + values.language;
				}

				_.each( settings, function( value, setting ) {
					textareaAttr[ 'data-' + setting ] = value;
				} );

				return textareaAttr;
			},

			/**
			 * Builds copy code area attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildSyntaxHighlighterCopyCodeTitleAttr: function( values ) {
				var syntaxHighlighterCopyCodeTitle = {
					class: 'syntax-highlighter-copy-code-title',
					style: ''
				};
				syntaxHighlighterCopyCodeTitle[ 'data-id' ] = 'fusion_syntax_highlighter_' + this.model.get( 'cid' );

				if ( values.font_size ) {
					syntaxHighlighterCopyCodeTitle.style += 'font-size:' + values.font_size + ';';
				}

				return syntaxHighlighterCopyCodeTitle;
			},

			/**
			 * Builds styles.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {string}
			 */
			buildStyles: function( values ) {
				var style  = '<style type="text/css" scopped="scopped">',
					cid = this.model.get( 'cid' );

				if ( values.background_color && '' !== values.background_color ) {
					style += '.fusion-syntax-highlighter-cid' + cid + ' > .CodeMirror, .fusion-syntax-highlighter-cid' + cid + ' > .CodeMirror .CodeMirror-gutters { background-color:' + values.background_color + ';}';
				}

				if ( 'no' !== values.line_numbers ) {
					style += '.fusion-syntax-highlighter-cid' + cid + ' > .CodeMirror .CodeMirror-gutters { background-color: ' + values.line_number_background_color + '; }';
					style += '.fusion-syntax-highlighter-cid' + cid + ' > .CodeMirror .CodeMirror-linenumber { color: ' + values.line_number_text_color + '; }';
				}

				style += '</style>';

				return style;
			}
		} );
	} );
}( jQuery ) );

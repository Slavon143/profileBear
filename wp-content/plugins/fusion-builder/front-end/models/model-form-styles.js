/* global FusionApp, FusionEvents */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.FormStyles = Backbone.Model.extend( {

		initialize: function() {
			this.baseSelector = '.fusion-form.fusion-form-form-wrapper';
			this.dynamic_css  = {};
			this.formData     = FusionApp.data.postMeta._fusion;

			this.buildStyles();

			this.listenTo( FusionEvents, 'fusion-form-styles', this.buildStyles );
			this.listenTo( FusionEvents, 'fusion-builder-loaded', this.buildStyles );
			this.listenTo( FusionEvents, 'fusion-preview-refreshed', this.updateFormData );
		},

		updateFormData: function() {
			this.formData = FusionApp.data.postMeta._fusion;
		},

		addCssProperty: function ( selectors, property, value, important ) {

			if ( 'object' === typeof selectors ) {
				selectors = Object.values( selectors );
			}

			if ( 'object' === typeof selectors ) {
				selectors = selectors.join( ',' );
			}

			if ( 'object' !== typeof this.dynamic_css[ selectors ] ) {
				this.dynamic_css[ selectors ] = {};
			}

			if ( 'undefined' !== typeof important && important ) {
				value += ' !important';
			}
			if ( 'undefined' === typeof this.dynamic_css[ selectors ][ property ] || ( 'undefined' !== typeof important && important ) || ! this.dynamic_css[ selectors ][ property ].includes( 'important' ) ) {
				this.dynamic_css[ selectors ][ property ] = value;
			}
		},

		isDefault: function( param, subset ) {
			if ( 'string' === typeof subset ) {
				return 'undefined' === typeof this.formData[ param ] || 'undefined' === typeof this.formData[ param ][ subset ] || '' === this.formData[ param ][ subset ];
			}
			return 'undefined' === typeof this.formData[ param ] || '' === this.formData[ param ];
		},

		parseCSS: function () {
			var css = '';

			if ( 'object' !== typeof this.dynamic_css ) {
				return '';
			}

			_.each( this.dynamic_css, function ( properties, selector ) {
				if ( 'object' === typeof properties ) {
					css += selector + '{';
					_.each( properties, function ( value, property ) {
						css += property + ':' + value + ';';
					} );
					css += '}';
				}
			} );

			return css;
		},

		buildStyles: function() {
			var selectors,
				css              = '',
				placeholderColor = '',
				hoverColor       = '',
				borderTop,
				borderBottom;

			var textInputs = [
					this.baseSelector + ' input[type="date"]',
					this.baseSelector + ' input[type="datetime-local"]',
					this.baseSelector + ' input[type="email"]',
					this.baseSelector + ' input[type="month"]',
					this.baseSelector + ' input[type="number"]',
					this.baseSelector + ' input[type="password"]',
					this.baseSelector + ' input[type="search"]',
					this.baseSelector + ' input[type="tel"]',
					this.baseSelector + ' input[type="text"]',
					this.baseSelector + ' input[type="time"]',
					this.baseSelector + ' input[type="url"]',
					this.baseSelector + ' input[type="week"]',
					this.baseSelector + ' input[type="datetime"]',
				];

			var uploadInput = this.baseSelector + ' input[type="upload"]';
			var rangeInput  = this.baseSelector + ' input[type="range"]';

			var selectInput = this.baseSelector + ' select';
			var textareaInput = this.baseSelector + ' textarea';

			var customizableInputs = textInputs.concat( [ selectInput ], [ textareaInput ], [ uploadInput ] );

			this.dynamic_css  = {};

			if ( 'fusion_form' !== FusionApp.getPost( 'post_type' ) ) {
				return;
			}

			// Help tooltips.
			this.addCssProperty( this.baseSelector + ' .fusion-form-tooltip .fusion-form-tooltip-content', 'color',  this.formData['tooltip_text_color'], true);
			this.addCssProperty( this.baseSelector + ' .fusion-form-tooltip .fusion-form-tooltip-content', 'background-color',  this.formData['tooltip_background_color'], true);
			this.addCssProperty( this.baseSelector + ' .fusion-form-tooltip .fusion-form-tooltip-content', 'border-color',  this.formData['tooltip_background_color'], true);
			// Field margin.
			if (!this.isDefault('field_margin', 'top')) {
			  this.addCssProperty( this.baseSelector + ' .fusion-form-field', 'margin-top',  this.formData['field_margin']['top']);
			}

			if (!this.isDefault('field_margin', 'bottom')) {
			  this.addCssProperty( this.baseSelector + ' .fusion-form-field', 'margin-bottom',  this.formData['field_margin']['bottom']);
			}

			if (!this.isDefault('form_input_height')) {
			  heightInputs = textInputs.concat( [selectInput], [rangeInput] );
			  this.addCssProperty(heightInputs, 'height',  this.formData['form_input_height']);
			  this.addCssProperty( this.baseSelector + ' .fusion-form-input-with-icon > i', 'line-height',  this.formData['form_input_height']);
			}

			if (!this.isDefault('form_bg_color')) {
			  this.addCssProperty(customizableInputs, 'background-color',  this.formData['form_bg_color']);
			}

			if ( !this.isDefault( 'form_font_size' ) ) {
				this.addCssProperty( customizableInputs, 'font-size',  this.formData.form_font_size );
				this.addCssProperty( this.baseSelector + ' .fusion-form-field .fusion-form-input-with-icon>i', 'font-size',  this.formData.form_font_size );
			}

			if ( '' !== this.formData.form_placeholder_color ) {
				placeholderColor = this.formData.form_placeholder_color;
			} else if ( ! this.isDefault( 'form_text_color' ) ) {
				placeholderColor = jQuery.AWB_Color( this.formData.form_text_color ).alpha( 0.5 ).toRgbaString();
			}

			if ( placeholderColor ) {

			  // Regular browser placeholders.
			  selectors = [ this.baseSelector + ' input::placeholder', this.baseSelector + ' textarea::placeholder', this.baseSelector + ' textarea::placeholder', this.baseSelector + ' select:invalid' ];
			  this.addCssProperty(selectors, 'color', placeholderColor);
			}

			if (!this.isDefault('form_text_color')) {

			  // Select field.
			  this.addCssProperty( this.baseSelector + ' option', 'color',  this.formData['form_text_color']);
			  // Upload field.
			  this.addCssProperty( this.baseSelector + ' input.fusion-form-upload-field::placeholder', 'color',  this.formData['form_text_color']);

			  // Icon color.
			  this.addCssProperty( this.baseSelector + ' .fusion-form-input-with-icon > i', 'color',  this.formData['form_text_color'], true);
			  // Input text color.
			  this.addCssProperty(customizableInputs, 'color',  this.formData['form_text_color']);

			  // Select stroke color.
			  this.addCssProperty( this.baseSelector + ' .fusion-select-wrapper .select-arrow path', 'stroke', this.formData['form_text_color'], true );
			}

			if ( !this.isDefault( 'form_label_color' ) ) {
				this.addCssProperty( this.baseSelector + ' label, ' + this.baseSelector + ' .label, ' + this.baseSelector + ' .fusion-form-tooltip > i', 'color',  this.formData.form_label_color );
			}

			if (!this.isDefault('form_border_width', 'top')) {
			  this.addCssProperty(customizableInputs, 'border-top-width', _.fusionGetValueWithUnit( this.formData['form_border_width']['top'], 'px'));
			  this.addCssProperty( this.baseSelector + ' .fusion-form-field .fusion-form-image-select label', 'border-top-width', _.fusionGetValueWithUnit( this.formData['form_border_width']['top'], 'px'));
			}

			if (!this.isDefault('form_border_width', 'bottom')) {
			  this.addCssProperty(customizableInputs, 'border-bottom-width', _.fusionGetValueWithUnit( this.formData['form_border_width']['bottom'], 'px'));
			  this.addCssProperty( this.baseSelector + ' .fusion-form-field .fusion-form-image-select label', 'border-bottom-width', _.fusionGetValueWithUnit( this.formData['form_border_width']['bottom'], 'px'));
			}

			if (!this.isDefault('form_border_width', 'right')) {
			  this.addCssProperty(customizableInputs, 'border-right-width', _.fusionGetValueWithUnit( this.formData['form_border_width']['right'], 'px'));
			  this.addCssProperty( this.baseSelector + ' .fusion-form-field .fusion-form-image-select label', 'border-right-width', _.fusionGetValueWithUnit( this.formData['form_border_width']['right'], 'px'));
			  if (jQuery( 'body' ).hasClass( 'rtl' )) {
			    this.addCssProperty( this.baseSelector + ' .fusion-form-field .fusion-form-input-with-icon > i', 'right', 'calc( 1em + ' + _.fusionGetValueWithUnit( this.formData['form_border_width']['right'], 'px') + ')', true);
			  }
			  else {
			    this.addCssProperty( this.baseSelector + ' .fusion-select-wrapper .select-arrow', 'right', 'calc( 1em + ' + _.fusionGetValueWithUnit( this.formData['form_border_width']['right'], 'px') + ')', true);
			  }

			}

			if (!this.isDefault('form_border_width', 'left')) {
			  this.addCssProperty(customizableInputs, 'border-left-width', _.fusionGetValueWithUnit( this.formData['form_border_width']['left'], 'px'));
			  this.addCssProperty( this.baseSelector + ' .fusion-form-field .fusion-form-image-select label', 'border-left-width', _.fusionGetValueWithUnit( this.formData['form_border_width']['left'], 'px'));
			  if (jQuery( 'body' ).hasClass( 'rtl' )) {
			    this.addCssProperty( this.baseSelector + ' .fusion-select-wrapper .select-arrow', 'left', 'calc( 1em + ' + _.fusionGetValueWithUnit( this.formData['form_border_width']['left'], 'px') + ')', true);
			  }
			  else {
			    this.addCssProperty( this.baseSelector + ' .fusion-form-field .fusion-form-input-with-icon > i', 'left', 'calc( 1em + ' + _.fusionGetValueWithUnit( this.formData['form_border_width']['left'], 'px') + ')', true);
			  }

			}

			if (!this.isDefault('form_border_width', 'bottom') || !this.isDefault('form_border_width', 'top')) {
				borderTop    = this.isDefault('form_border_width', 'top') ? 'var(--form_border_width-top)' : _.fusionGetValueWithUnit( this.formData['form_border_width']['top'], 'px');
				borderBottom = this.isDefault('form_border_width', 'bottom') ? 'var(--form_border_width-bottom)' : _.fusionGetValueWithUnit( this.formData['form_border_width']['bottom'], 'px');
				formFontSize = this.isDefault( 'form_font_size' ) ? '1em' : this.formData.form_font_size;
				this.addCssProperty( this.baseSelector + ' .fusion-form-field:not( .fusion-form-upload-field ) .fusion-form-input-with-icon > i', 'top', 'calc( 50% + (' + borderTop + ' - ' + borderBottom + ' ) / 2 )', true );
				this.addCssProperty( this.baseSelector + ' .fusion-form-field.fusion-form-textarea-field .fusion-form-input-with-icon > i', 'top', 'calc( ' + borderTop + ' + ' + formFontSize + ' )', true );
			}

			if (!this.isDefault('form_border_color')) {
			  selectors = [ this.baseSelector + ' .fusion-form-field .fusion-form-checkbox label:before', this.baseSelector + ' .fusion-form-field .fusion-form-radio label:before', this.baseSelector + ' .fusion-form-field .fusion-form-image-select label' ];
			  this.addCssProperty(customizableInputs, 'border-color',  this.formData['form_border_color']);
			  this.addCssProperty(selectors, 'border-color',  this.formData['form_border_color']);
			  selectors = [ this.baseSelector + ' .fusion-form-field .fusion-form-rating-area .fusion-form-rating-icon' ];
			  this.addCssProperty(selectors, 'color',  this.formData['form_border_color']);

				// Range input type.
			  this.addCssProperty( this.baseSelector + ' .fusion-form-field input[type=range]::-webkit-slider-runnable-track', 'background',  this.formData['form_border_color']);
			  this.addCssProperty( this.baseSelector + ' .fusion-form-field input[type=range]::-moz-range-track', 'background',  this.formData['form_border_color']);
			}

			if (!this.isDefault('form_focus_border_color')) {
				hoverColor = jQuery.AWB_Color( this.formData.form_focus_border_color ).alpha( 0.5 ).toRgbaString();

				selectors = [
					this.baseSelector + ' input:not([type="submit"]):focus',
				this.baseSelector + ' select:focus',
					this.baseSelector + ' textarea:focus',
					this.baseSelector + ' .fusion-form-field.focused.fusion-form-upload-field .fusion-form-upload-field',
					this.baseSelector + ' .fusion-form-field .fusion-form-radio input:checked + label:before',
					this.baseSelector + ' .fusion-form-field .fusion-form-radio input:hover + label:before',
					this.baseSelector + ' .fusion-form-field .fusion-form-checkbox input:checked + label:before',
					this.baseSelector + ' .fusion-form-field .fusion-form-checkbox input:hover + label:before',
					this.baseSelector + ' .fusion-form-field .fusion-form-image-select .fusion-form-input:checked + label',
					this.baseSelector + ' .fusion-form-field .fusion-form-image-select .fusion-form-input:hover + label',
					this.baseSelector + ' .fusion-form-field .fusion-form-checkbox input:focus + label:before',
					this.baseSelector + ' .fusion-form-field .fusion-form-radio input:focus + label:before',
					this.baseSelector + ' .fusion-form-field .fusion-form-image-select .fusion-form-input:focus + label',

				];

			  this.addCssProperty(selectors, 'border-color',  this.formData['form_focus_border_color']);

				selectors = [
					this.baseSelector + ' .fusion-form-field .fusion-form-radio input:hover:not(:checked) + label:before',
					this.baseSelector + ' .fusion-form-field .fusion-form-checkbox input:hover:not(:checked) + label:before',
					this.baseSelector + ' .fusion-form-field .fusion-form-image-select .fusion-form-input:hover:not(:checked) + label',
					this.baseSelector + ' .fusion-form-field .fusion-form-upload-field-container:hover .fusion-form-upload-field',
					this.baseSelector + ' .fusion-form-field .fusion-form-range-field-container .fusion-form-range-value:hover:not(:focus)',
					this.baseSelector + ' .fusion-form-field .fusion-form-input:hover:not(:focus)'
				];

				this.addCssProperty(selectors, 'border-color',  hoverColor);

				selectors = [
					this.baseSelector + ' .fusion-form-field .fusion-form-rating-area .fusion-form-input:checked ~ label i',
				];

				this.addCssProperty(selectors, 'color',  this.formData['form_focus_border_color']);

				selectors = [
					this.baseSelector + ' .fusion-form-field .fusion-form-rating-area .fusion-form-input:checked:hover ~ label i',
					this.baseSelector + ' .fusion-form-field .fusion-form-rating-area .fusion-form-rating-icon:hover i',
					this.baseSelector + ' .fusion-form-field .fusion-form-rating-area .fusion-form-rating-icon:hover ~ label i',
					this.baseSelector + ' .fusion-form-field .fusion-form-rating-area .fusion-form-input:hover ~ label i',
				];

				this.addCssProperty(selectors, 'color',  hoverColor);

				selectors = [
					this.baseSelector + ' .fusion-form-field .fusion-form-checkbox input:checked + label:after',
					this.baseSelector + ' .fusion-form-field .fusion-form-radio input:checked + label:after',
				];

				this.addCssProperty(selectors, 'background',  this.formData['form_focus_border_color']);

				// Range input type.
				this.addCssProperty( this.baseSelector + ' .fusion-form-field input[type=range]::-webkit-slider-thumb', 'background',  this.formData['form_focus_border_color']);
				this.addCssProperty( this.baseSelector + ' .fusion-form-field input[type=range]::-moz-range-thumb', 'background',  this.formData['form_focus_border_color']);
			}

			if (!this.isDefault('form_border_radius')) {
			  this.addCssProperty(customizableInputs, 'border-radius', _.fusionGetValueWithUnit( this.formData['form_border_radius'], 'px'));
			  this.addCssProperty( this.baseSelector + ' .fusion-form-field .fusion-form-image-select label', 'border-radius', _.fusionGetValueWithUnit( this.formData['form_border_radius'], 'px'));
			}

			css = this.parseCSS();

			if ( jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'head' ).find( '#fusion-form-style-block' ).length ) {
				 jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'head' ).find( '#fusion-form-style-block' ).html( css );
				 return;
			}

			jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'head' ).append( '<style id="fusion-form-style-block">' + css + '</style>' );
		}
	} );
}( jQuery ) );

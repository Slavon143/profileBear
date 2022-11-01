/* global rangy, MediumEditor, FusionApp, fusionAllElements, fusionHistoryManager, fusionBuilderText, awbTypographySelect */
/* eslint no-unused-vars: 0 */
/* eslint no-shadow: 0 */
/* eslint no-undef: 0 */
/* eslint no-mixed-operators: 0 */
/* eslint no-empty: 0 */
/* eslint no-redeclare: 0 */
/* eslint no-unreachable: 0 */
/* eslint no-extend-native: 0 */
/* eslint no-native-reassign: 0 */
/* eslint radix: 0 */
/* eslint no-global-assign: 0 */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.inlineEditor = Backbone.Model.extend( {

		/**
		 * Init.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {
			rangy.init();
			this.createExtended();
			this.createTypography();
			this.createFontColor();
			this.createInlineShortcode();
			this.createAlign();
			this.createAnchor();
			this.createRemove();
			this.createIndent();
			this.createOutdent();

			Number.prototype.countDecimals = function() {
				if ( Math.floor( this.valueOf() ) === this.valueOf() ) {
					return 0;
				}
				return this.toString().split( '.' )[ 1 ].length || 0;
			};
		},

		/**
		 * Creates the font-size extension for MediumEditor and adds the form.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		createExtended: function( event ) { // jshint ignore: line
			var FusionExtendedForm = MediumEditor.extensions.form.extend( {
				name: 'fusionExtended',
				action: 'fusionExtended',
				aria: fusionBuilderText.extended_options,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusiona-ellipsis" aria-hidden="true"></i>',
				hasForm: false,

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );
					this.subscribe( 'editableDrop', this.dragDisable.bind( this ) );
					this.subscribe( 'editableDrag', this.dragDisable.bind( this ) );
				},

				handleClick: function( event ) {
					var toolbar  = this.base.getExtensionByName( 'toolbar' );

					event.preventDefault();
					event.stopPropagation();

					toolbar.toolbar.querySelector( '.medium-editor-toolbar-actions' ).classList.toggle( 'alternative-active' );

					this.setToolbarPosition();

					return false;
				},

				dragDisable: function( event ) {
					if ( jQuery( event.target ).hasClass( '.fusion-inline-element' ) || jQuery( event.target ).find( '.fusion-inline-element' ).length ) {
						event.preventDefault();
						event.stopPropagation();
					}
				}
			} );

			MediumEditor.extensions.fusionExtended = FusionExtendedForm;
		},

		/**
		 * Creates the alignment extension.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		createAlign: function( event ) { // jshint ignore: line
			var FusionAlignForm = MediumEditor.extensions.form.extend( {
				name: 'fusionAlign',
				action: 'fusionAlign',
				aria: fusionBuilderText.align_text,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusiona-align-center" aria-hidden="true"></i>',
				hasForm: true,

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );
				},

				checkState: function( node ) {
					var nodes     = MediumEditor.selection.getSelectedElements( this.document ),
						align     = this.getExistingValue( nodes ),
						iconClass = 'fusiona-align-';

					if ( 'undefined' !== typeof align && nodes.length ) {
						align = 'start' === align ? 'left' : align.replace( '-moz-', '' );
						jQuery( this.button ).find( 'i' ).attr( 'class', iconClass + align );
					}
				},

				// Called when the button the toolbar is clicked
				// Overrides ButtonExtension.handleClick
				handleClick: function( event ) {
					var toolbar  = this.base.getExtensionByName( 'toolbar' );

					event.preventDefault();
					event.stopPropagation();

					if ( ! this.isDisplayed() ) {
						toolbar.hideExtensionForms();
						this.showForm();
					}

					return false;
				},

				// Get text alignment.
				getExistingValue: function( nodes ) {
					var nodeIndex,
						el,
						align = 'left';

					// If there are no nodes, use the parent el.
					if ( ! nodes.length ) {
						nodes = this.base.elements;
					}

					for ( nodeIndex = 0; nodeIndex < nodes.length; nodeIndex++ ) {
						el    = nodes[ nodeIndex ];
						align = jQuery( el ).css( 'text-align' );
					}

					return align;
				},

				// Called by medium-editor to append form to the toolbar
				getForm: function() {
					if ( ! this.form ) {
						this.form = this.createForm();
					}
					return this.form;
				},

				// Used by medium-editor when the default toolbar is to be displayed
				isDisplayed: function() {
					return this.getForm().classList.contains( 'visible' );
				},

				hideForm: function() {
					var $form = jQuery( this.getForm() );
					$form.find( '.medium-editor-button-active' ).removeClass( 'medium-editor-button-active' );
					$form.removeClass( 'visible' ).addClass( 'hidden' );
					setTimeout( function() {
						$form.removeClass( 'hidden' );
					}, 400 );
				},

				showForm: function() {
					var nodes = MediumEditor.selection.getSelectedElements( this.document ),
						value = this.getExistingValue( nodes ),
						form  = this.getForm(),
						targetEl;

					value = 'start' === value ? 'left' : value;

					this.base.saveSelection();
					this.hideToolbarDefaultActions();
					form.classList.add( 'visible' );
					form.classList.remove( 'hidden' );

					targetEl = form.querySelector( '.fusion-align-' + value );
					if ( targetEl ) {
						targetEl.classList.add( 'medium-editor-button-active' );
					}
					this.setToolbarPosition();
				},

				// Called by core when tearing down medium-editor (destroy)
				destroy: function() {
					if ( ! this.form ) {
						return false;
					}

					if ( this.form.parentNode ) {
						this.form.parentNode.removeChild( this.form );
					}

					delete this.form;
				},

				// Form creation and event handling
				createForm: function() {
					var doc           = this.document,
						form          = doc.createElement( 'div' ),
						ul            = doc.createElement( 'ul' ),
						alignLeft     = doc.createElement( 'button' ),
						alignCenter   = doc.createElement( 'button' ),
						alignRight    = doc.createElement( 'button' ),
						alignJustify  = doc.createElement( 'button' ),
						closeForm     = doc.createElement( 'button' ),
						li            = doc.createElement( 'li' ),
						icon          = doc.createElement( 'i' );

					this.base.saveSelection();

					// Font Name Form (div)
					form.className = 'medium-editor-toolbar-form medium-editor-alternate-toolbar';
					form.id        = 'medium-editor-toolbar-form-align-' + this.getEditorId();
					ul.className   = 'medium-editor-toolbar-actions';

					// Left align.
					icon.className      = 'fusiona-align-left';
					alignLeft.className = 'fusion-align-left';
					alignLeft.setAttribute( 'title', fusionBuilderText.align_left );
					alignLeft.setAttribute( 'aria-label', fusionBuilderText.align_left );
					alignLeft.setAttribute( 'data-action', 'justifyLeft' );
					alignLeft.appendChild( icon );
					li.appendChild( alignLeft );
					ul.appendChild( li );
					this.on( alignLeft, 'click', this.applyAlignment.bind( this ), true );

					// Center align.
					li                  = doc.createElement( 'li' );
					icon                = doc.createElement( 'i' );
					icon.className      = 'fusiona-align-center';
					alignCenter.className = 'fusion-align-center';
					alignCenter.setAttribute( 'title', fusionBuilderText.align_center );
					alignCenter.setAttribute( 'aria-label', fusionBuilderText.align_center );
					alignCenter.setAttribute( 'data-action', 'justifyCenter' );
					alignCenter.appendChild( icon );
					li.appendChild( alignCenter );
					ul.appendChild( li );
					this.on( alignCenter, 'click', this.applyAlignment.bind( this ), true );

					// Right align.
					li                   = doc.createElement( 'li' );
					icon                 = doc.createElement( 'i' );
					icon.className       = 'fusiona-align-right';
					alignRight.className = 'fusion-align-right';
					alignRight.setAttribute( 'title', fusionBuilderText.align_right );
					alignRight.setAttribute( 'aria-label', fusionBuilderText.align_right );
					alignRight.setAttribute( 'data-action', 'justifyRight' );
					alignRight.appendChild( icon );
					li.appendChild( alignRight );
					ul.appendChild( li );
					this.on( alignRight, 'click', this.applyAlignment.bind( this ), true );

					// Justify align.
					li                     = doc.createElement( 'li' );
					icon                   = doc.createElement( 'i' );
					icon.className         = 'fusiona-align-justify';
					alignJustify.className = 'fusion-align-justify';
					alignJustify.setAttribute( 'title', fusionBuilderText.align_justify );
					alignJustify.setAttribute( 'aria-label', fusionBuilderText.align_justify );
					alignJustify.setAttribute( 'data-action', 'justifyFull' );
					alignJustify.appendChild( icon );
					li.appendChild( alignJustify );
					ul.appendChild( li );
					this.on( alignJustify, 'click', this.applyAlignment.bind( this ), true );

					// Close icon.
					li                     = doc.createElement( 'li' );
					icon                   = doc.createElement( 'i' );
					icon.className         = 'fusiona-check';
					closeForm.setAttribute( 'title', fusionBuilderText.accept );
					closeForm.setAttribute( 'aria-label', fusionBuilderText.accept );
					closeForm.appendChild( icon );
					li.appendChild( closeForm );
					ul.appendChild( li );
					this.on( closeForm, 'click', this.closeForm.bind( this ), true );

					form.appendChild( ul );

					return form;
				},

				applyAlignment: function( event ) {
					var action  = event.currentTarget.getAttribute( 'data-action' ),
						$target = jQuery( event.currentTarget ),
						iconClass = $target.find( 'i' ).attr( 'class' );

					$target.closest( 'ul' ).find( '.medium-editor-button-active' ).removeClass( 'medium-editor-button-active' );
					$target.addClass( 'medium-editor-button-active' );

					jQuery( this.button ).find( 'i' ).attr( 'class', iconClass );

					this.base.restoreSelection();

					this.execAction( action, { skipCheck: true } );
				},

				closeForm: function() {
					this.hideForm();
					this.base.checkSelection();
				}
			} );

			MediumEditor.extensions.fusionAlign = FusionAlignForm;
		},

		/**
		 * Creates the typography extension for MediumEditor and adds the form.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		createTypography: function( event ) { // jshint ignore: line
			var fusionTypographyForm = MediumEditor.extensions.form.extend( {

				name: 'fusionTypography',
				action: 'fusionTypography',
				aria: fusionBuilderText.typography,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusiona-font-solid" aria-hidden="true"></i>',
				hasForm: true,
				fonts: [],
				loadPreviews: false,
				override: false,
				parentCid: false,
				searchFonts: [],
				overrideParams: [
					'font-size',
					'line-height',
					'letter-spacing',
					'tag',
					'font-family'
				],
				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );
					this.classApplier = rangy.createClassApplier( 'fusion-editing', {
						elementTagName: 'span',
						tagNames: [ 'span', 'b', 'strong', 'a', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ],
						normalize: true
					} );

					this._handleInputChange = _.debounce( _.bind( this.handleInputChange, this ), 100 );
				},

				// Overrides ButtonExtension.handleClick
				handleClick: function( event ) {
					var nodes,
						font;

					event.preventDefault();
					event.stopPropagation();

					if ( ! this.isDisplayed() ) {

						this.showForm();
					}

					return false;
				},

				// Called by medium-editor to append form to the toolbar
				getForm: function() {
					if ( ! this.form ) {
						this.form = this.createForm();
					}
					return this.form;
				},

				// Used by medium-editor when the default toolbar is to be displayed
				isDisplayed: function() {
					return this.getForm().classList.contains( 'visible' );
				},

				hideForm: function() {
					var self         = this,
						form         = this.getForm(),
						toolbar      = this.base.getExtensionByName( 'toolbar' ),
						timeoutValue = 50;

					if ( toolbar.toolbar.classList.contains( 'medium-toolbar-arrow-over' ) ) {
						timeoutValue = 300;
					}

					form.classList.add( 'hidden' );
					jQuery( form ).find( '.fusion-options-wrapper' ).removeClass( 'visible' );
					form.classList.remove( 'visible' );
					setTimeout( function() {
						form.classList.remove( 'hidden' );
					}, 400 );

					setTimeout( function() {
						self.setToolbarPosition();
						self.base.checkSelection();
					}, timeoutValue );

				},

				showForm: function() {
					var self    = this,
						form    = this.getForm(),
						actives = form.querySelectorAll( '.active' ),
						link    = form.querySelector( '[href="#settings"]' ),
						tab     = form.querySelector( '[data-id="settings"]' );

					this.base.saveSelection();
					this.hideToolbarDefaultActions();

					form.classList.add( 'visible' );
					form.classList.remove( 'hidden' );

					if ( actives ) {
						_.each( actives, function( active ) {
							active.classList.remove( 'active' );
						} );
					}
					if ( link ) {
						link.classList.add( 'active' );
					}
					if ( tab ) {
						tab.classList.add( 'active' );
					}

					if ( _.isUndefined( window.awbTypographySelect ) || _.isUndefined( window.awbTypographySelect.webfonts ) ) {
						jQuery.when( window.awbTypographySelect.getWebFonts() ).done( function() {
							self.insertFamilyChoices();
							self.setFontFamilyValues();
						} );
					} else {
						this.insertFamilyChoices();
						this.setFontFamilyValues();
					}

					this.setToolbarPosition();

					this.setTagValue();
					this.setFontStyleValues();
				},

				// Get font size which is set.
				getExistingTag: function() {
					var nodes          = MediumEditor.selection.getSelectedElements( this.document ),
						selectionRange = MediumEditor.selection.getSelectionRange( this.document ),
						parentEl       = MediumEditor.selection.getSelectedParentElement( selectionRange ),
						tag            = 'p',
						nodeIndex,
						el;

					if ( 'undefined' !== typeof FusionPageBuilderApp ) {
						FusionPageBuilderApp.inlineEditorHelpers.setOverrideParams( this, this.overrideParams );
					}

					// Check for parent el first.
					if ( parentEl ) {
						nodes = [ parentEl ];
					}

					// If there are no nodes, use the base el.
					if ( ! nodes.length ) {
						nodes = this.base.elements;
					}

					for ( nodeIndex = 0; nodeIndex < nodes.length; nodeIndex++ ) {
						el  = nodes[ nodeIndex ];
						tag = el.nodeName.toLowerCase();
					}

					return tag;
				},
				setTagValue: function() {
					var tag       = this.getExistingTag(),
						form      = this.getForm(),
						tagsHold  = form.querySelector( '.typography-tags' ),
						newTag    = form.querySelector( '[data-val="' + tag + '"]' );

					if ( newTag ) {
						newTag.classList.add( 'active' );
					}
				},

				// Get font size which is set.
				getExistingStyleValues: function( ) {
					var nodes          = MediumEditor.selection.getSelectedElements( this.document ),
						selectionRange = MediumEditor.selection.getSelectionRange( this.document ),
						parentEl       = MediumEditor.selection.getSelectedParentElement( selectionRange ),
						nodeIndex,
						el,
						values;

					// Check for parent el first.
					if ( parentEl ) {
						nodes = [ MediumEditor.selection.getSelectedParentElement( selectionRange ) ];
					}

					// If there are no nodes, use the base el.
					if ( ! nodes.length ) {
						nodes = this.base.elements;
					}

					for ( nodeIndex = 0; nodeIndex < nodes.length; nodeIndex++ ) {
						el                   = nodes[ nodeIndex ];
						values               = {};
						values.size          = window.getComputedStyle( el, null ).getPropertyValue( 'font-size' );
						values.lineHeight    = window.getComputedStyle( el, null ).getPropertyValue( 'line-height' );
						values.letterSpacing = window.getComputedStyle( el, null ).getPropertyValue( 'letter-spacing' );

						// If it is set in the style attribute, use that.
						if ( 'undefined' !== typeof el.style.fontSize && el.style.fontSize && -1 === el.style.fontSize.indexOf( 'var(' ) ) {
							values.size = el.style.fontSize;
						}

						if ( 'undefined' !== typeof el.style.lineHeight && el.style.lineHeight && -1 === el.style.lineHeight.indexOf( 'var(' ) ) {
							values.lineHeight = el.style.lineHeight;
						}
						if ( 'undefined' !== typeof el.style.letterSpacing && el.style.letterSpacing && -1 === el.style.letterSpacing.indexOf( 'var(' ) ) {
							values.letterSpacing = el.style.letterSpacing;
						}

						// If it is data-fusion-font then prioritise that.
						if ( el.hasAttribute( 'data-fusion-font' ) ) {
							return values;
						}
					}

					return values;
				},

				getExistingFamilyValues: function() {
					var self           = this,
						nodes          = MediumEditor.selection.getSelectedElements( this.document ),
						selectionRange = MediumEditor.selection.getSelectionRange( this.document ),
						parentEl       = MediumEditor.selection.getSelectedParentElement( selectionRange ),
						values         = {
							variant: 'regular',
							variantLabel: 'Default',
							family: ''
						},
						nodeIndex,
						el;

					// Check for parent el first.
					if ( parentEl ) {
						nodes = [ MediumEditor.selection.getSelectedParentElement( selectionRange ) ];
					}

					// If there are no nodes, use the base el.
					if ( ! nodes.length ) {
						nodes = this.base.elements;
					}

					for ( nodeIndex = 0; nodeIndex < nodes.length; nodeIndex++ ) {
						el = nodes[ nodeIndex ];
						values.family = window.getComputedStyle( el, null ).getPropertyValue( 'font-family' );
						if ( -1 !== values.family.indexOf( ',' ) ) {
							values.family = values.family.split( ',' )[ 0 ];
						}

						// If it is set in the style attribute, use that.
						if ( 'undefined' !== typeof el.style.fontFamily && el.style.fontFamily ) {
							values.family = el.style.fontFamily;
						}
						if ( el.hasAttribute( 'data-fusion-google-font' ) ) {
							values.family = el.getAttribute( 'data-fusion-google-font' );
						}
						values.family = values.family.replace( /"/g, '' ).replace( /'/g, '' );

						if ( el.hasAttribute( 'data-fusion-google-variant' ) ) {
							values.variant = el.getAttribute( 'data-fusion-google-variant' );
							if ( ! _.isUndefined( window.awbTypographySelect ) && ! _.isUndefined( window.awbTypographySelect.webfonts ) ) {
								variants = self.getVariants( values.family );
								_.each( variants, function( variant ) {
									if ( values.variant === variant.id ) {
										values.variantLabel = variant.label;
									}
								} );
							}
						}

						// If it is data-fusion-font then prioritise that.
						if ( el.hasAttribute( 'data-fusion-font' ) ) {
							return values;
						}
					}

					return values;

				},

				setFontFamilyValues: function( form ) {
					var values        = this.getExistingFamilyValues(),
						form          = this.getForm(),
						familyHold    = form.querySelector( '.typography-family' ),
						family        = familyHold.querySelector( '[data-value="' + values.family + '"]' ),
						variant       = form.querySelector( '#fusion-variant' ),
						variants      = form.querySelector( '.fuson-options-holder.variant' ),
						rect;

					if ( family ) {
						family.classList.add( 'active' );
					}
					if ( variant ) {
						variant.setAttribute( 'data-value', values.variant );
						variant.innerHTML = values.variantLabel;
					}
					if ( variants ) {
						this.updateVariants( values.family );
					}
				},

				setFontStyleValues: function() {
					var values        = this.getExistingStyleValues(),
						form          = this.getForm(),
						fontSize      = form.querySelector( '#font_size' ),
						lineHeight    = form.querySelector( '#line_height' ),
						letterSpacing = form.querySelector( '#letter_spacing' );

					if ( fontSize ) {
						fontSize.setAttribute( 'value', values.size );
						fontSize.value = values.size;
					}
					if ( lineHeight ) {
						lineHeight.setAttribute( 'value', values.lineHeight );
						lineHeight.value = values.lineHeight;
					}
					if ( letterSpacing ) {
						letterSpacing.setAttribute( 'value', values.letterSpacing );
						letterSpacing.value = values.letterSpacing;
					}
				},

				// Called by core when tearing down medium-editor (destroy)
				destroy: function() {
					if ( ! this.form ) {
						return false;
					}

					if ( this.form.parentNode ) {
						this.form.parentNode.removeChild( this.form );
					}

					delete this.form;
				},

				doFormSave: function() {
					this.hideForm();
				},

				visibleY: function( el, rectTop, rectBottom ) {
					var rect   = el.getBoundingClientRect(),
						top    = rect.top,
						height = rect.height;

					if ( el.classList.contains( 'visible' ) ) {
						return false;
					}

					rect = familyHold.getBoundingClientRect();

					if ( false === top <= rectBottom ) {
						return false;
					}
					if ( ( top + height ) <= rectTop ) {
						return false;
					}

					return true;
				},

				getClosest: function( elem, selector ) {

					// Element.matches() polyfill
					if ( ! Element.prototype.matches ) {
						Element.prototype.matches =
							Element.prototype.matchesSelector ||
							Element.prototype.mozMatchesSelector ||
							Element.prototype.msMatchesSelector ||
							Element.prototype.oMatchesSelector ||
							Element.prototype.webkitMatchesSelector ||
							function( s ) {
								var matches = ( this.document || this.ownerDocument ).querySelectorAll( s ),
									i       = matches.length;

								while ( this !== 0 <= --i && matches.item( i ) ) {}
								return -1 < i;
							};
					}

					// Get the closest matching element
					for ( ; elem && elem !== document; elem = elem.parentNode ) {
						if ( elem.matches( selector ) ) {
							return elem;
						}
					}
					return null;
				},

				// Form creation and event handling
				createForm: function() {
					var self   = this,
						doc    = this.document,
						form   = doc.createElement( 'div' ),
						select = doc.createElement( 'select' ),
						close  = doc.createElement( 'a' ),
						save   = doc.createElement( 'a' ),
						option,
						i,
						navHold,
						settingsLink,
						familyLink,
						closeButton,
						tabHold,
						typographyTags,
						tags,
						typographyStyling,
						styles,
						familyTab,
						familyOptions,
						familyVariant,
						familyVariantSelect,
						familyVariantVisible,
						familyVariantOptionsHolder,
						familyVariantOptions;

					form.className = 'medium-editor-toolbar-form fusion-inline-typography';
					form.id        = 'medium-editor-toolbar-form-fontname-' + this.getEditorId();

					// Create the typography tab nav.
					navHold           = doc.createElement( 'div' );
					navHold.className = 'fusion-typography-nav';

					settingsLink = doc.createElement( 'a' );
					settingsLink.setAttribute( 'href', '#settings' );
					settingsLink.innerHTML = fusionBuilderText.typography_settings;
					settingsLink.className = 'active';
					navHold.appendChild( settingsLink );

					familyLink = doc.createElement( 'a' );
					familyLink.setAttribute( 'href', '#family' );
					familyLink.innerHTML = fusionBuilderText.typography_family;
					navHold.appendChild( familyLink );

					closeButton           = doc.createElement( 'button' );
					closeButton.className = 'fusion-inline-editor-close';
					closeButton.innerHTML = '<i class="fusiona-check" aria-hidden="true"></i>';
					navHold.appendChild( closeButton );

					tabHold = doc.createElement( 'div' );
					tabHold.className = 'fusion-typography-tabs';

					// Settings tab.
					settingsTab = doc.createElement( 'div' );
					settingsTab.setAttribute( 'data-id', 'settings' );
					settingsTab.className = 'active';
					tabHold.appendChild( settingsTab );

					// Tags bar.
					typographyTags           = doc.createElement( 'div' );
					typographyTags.className = 'typography-tags';
					typographyTags.innerHTML = '<span>' + fusionBuilderText.typography_tag + '</span>';

					tags = [ 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];

					_.each( tags, function( tag ) {
						var button = doc.createElement( 'button' );

						if ( 1 === tag.length ) {
							button.innerHTML = tag;
						} else if ( 2 === tag.length ) {
							button.className = 'fusiona-' + tag;
						}
						button.setAttribute( 'data-val', tag );

						self.on( button, 'click', function() {
							var actives  = typographyTags.querySelectorAll( '.active' ),
								isActive = button.classList.contains( 'active' ),
								value    = 'p' === tag ? undefined : tag.replace( 'h', '' );

							if ( actives ) {
								_.each( actives, function( active ) {
									active.classList.remove( 'active' );
								} );
							}

							self.base.restoreSelection();

							// If we have an element override, update view param instead.
							if ( 'undefined' === typeof FusionPageBuilderApp || ! FusionPageBuilderApp.inlineEditorHelpers.updateParentElementParam( self.parentCid, self.override.tag, value ) ) {
								self.execAction( 'append-' + tag, { skipCheck: true } );
							} else {

								// Tag changes editor, which means toolbar must close and reinit.
								self.base.checkSelection();
							}

							if ( ! isActive || tag === self.getExistingTag() ) {
								button.classList.add( 'active' );
							}
						} );

						typographyTags.appendChild( button );
					} );
					settingsTab.appendChild( typographyTags );

					// Styling bar.
					typographyStyling           = doc.createElement( 'div' );
					typographyStyling.className = 'typography-styling';

					styles = [
						{ label: fusionBuilderText.typography_fontsize, id: 'font_size', name: 'fontSize' },
						{ label: fusionBuilderText.typography_lineheight, id: 'line_height', name: 'lineHeight' },
						{ label: fusionBuilderText.typography_letterspacing, id: 'letter_spacing', name: 'letterSpacing' }
					];

					_.each( styles, function( style ) {
						var wrapper  = doc.createElement( 'div' ),
							label    = doc.createElement( 'label' ),
							input    = doc.createElement( 'input' ),
							inputUp  = doc.createElement( 'button' ),
							inputDown = doc.createElement( 'button' );

						label.setAttribute( 'for', style.id );
						label.innerHTML = style.label;

						input.setAttribute( 'type', 'text' );
						input.setAttribute( 'name', style.name );
						input.setAttribute( 'id', style.id );
						input.value = '';

						inputUp.className   = 'fusiona-arrow-up';
						inputDown.className = 'fusiona-arrow-down';

						wrapper.appendChild( label );
						wrapper.appendChild( input );
						wrapper.appendChild( inputUp );
						wrapper.appendChild( inputDown );

						self.on( input, 'change',  self._handleInputChange.bind( self ) );
						self.on( input, 'blur',  self._handleInputChange.bind( self ) );
						self.on( input, 'fusion-change',  self.handleInputChange.bind( self ) );

						self.on( inputUp, 'click', function() {
							var value  = input.value,
								number,
								unit,
								decimals;

							if ( ! value && 0 !== value ) {
								return;
							}

							number = parseFloat( value, 10 );

							if ( ! number && 0 !== number ) {
								return;
							}

							unit       = value.replace( number, '' );
							decimals   = number.countDecimals();
							increment  = 0 === decimals ? 1 : 1 / Math.pow( 10, decimals );
							number     = ( number + increment ).toFixed( decimals );

							input.value = number + unit;
							input.dispatchEvent( new Event( 'fusion-change' ) );
						} );

						self.on( inputDown, 'click', function() {
							var value  = input.value,
								number,
								unit,
								decimals;

							if ( ! value ) {
								return;
							}

							number = parseFloat( value, 10 );

							if ( ! number ) {
								return;
							}

							unit       = value.replace( number, '' );
							decimals   = number.countDecimals();
							increment  = 0 === decimals ? 1 : 1 / Math.pow( 10, decimals );
							number     = ( number - increment ).toFixed( decimals );

							input.value = number + unit;
							input.dispatchEvent( new Event( 'fusion-change' ) );
						} );

						typographyStyling.appendChild( wrapper );
					} );
					settingsTab.appendChild( typographyStyling );

					// Family tab.
					familyTab = doc.createElement( 'div' );
					familyTab.setAttribute( 'data-id', 'family' );
					tabHold.appendChild( familyTab );

					// Family selector.
					familyHold = doc.createElement( 'div' );
					familyHold.className = 'typography-family';

					if ( this.loadPreviews ) {
						this.on( familyHold, 'scroll', function() {
							var options    = familyHold.getElementsByTagName( 'div' ),
								rect       = familyHold.getBoundingClientRect(),
								rectTop    = rect.top,
								rectBottom = rect.bottom;

							_.each( options, function( option ) {
								var family = option.getAttribute( 'data-value' );
								if ( self.visibleY( option, rectTop, rectBottom ) ) {
									option.classList.add( 'visible' );
									self.webFontLoad( family );
								}
							} );
						} );
					}

					familyTab.appendChild( familyHold );

					// Right sidebar for family tab.
					familyOptions = doc.createElement( 'div' );
					familyOptions.className = 'typography-family-options';

					// Family variant.
					familyVariant = doc.createElement( 'div' );
					familyVariantVisible = doc.createElement( 'div' );
					familyVariantVisible.className = 'fusion-select-wrapper';
					familyVariantVisible.innerHTML = '<label for="variant">' + fusionBuilderText.typography_variant + '</label>';

					familyVariantSelect = doc.createElement( 'div' );
					familyVariantSelect.className = 'fusion-select fusion-selected-value';
					familyVariantSelect.id        = 'fusion-variant';
					familyVariantSelect.setAttribute( 'data-name', 'variant' );
					familyVariantSelect.setAttribute( 'data-id', 'variant' );

					familyVariantVisible.appendChild( familyVariantSelect );

					familyVariantOptions                 = doc.createElement( 'div' );
					familyVariantOptions.className       = 'fusion-options-wrapper variant';
					familyVariantOptions.innerHTML       = '<label for="variant">' + fusionBuilderText.typography_variant + '</label>';
					familyVariantOptionsHolder           = doc.createElement( 'div' );
					familyVariantOptionsHolder.className = 'fuson-options-holder variant';
					familyVariantOptions.appendChild( familyVariantOptionsHolder );

					familyVariant.appendChild( familyVariantVisible );
					familyVariant.appendChild( familyVariantOptions );

					familyOptions.appendChild( familyVariant );

					familyTab.appendChild( familyOptions );

					form.appendChild( navHold );
					form.appendChild( tabHold );

					// Handle clicks on the form itself
					this.on( form, 'click', this.handleFormClick.bind( this ) );

					// Tab clicks.
					this.on( settingsLink, 'click', this.handleTabClick.bind( this ) );
					this.on( familyLink, 'click', this.handleTabClick.bind( this ) );

					// Variant clicks.
					this.on( familyVariantVisible, 'click', this.handleVariantClick.bind( this ) );
					this.on( familyVariantOptionsHolder, 'click', this.handleOptionClick.bind( this ) );

					// Form saves.
					this.on( closeButton, 'click', this.doFormSave.bind( this ) );

					return form;
				},

				handleVariantClick: function( event ) {
					var form        = this.getForm(),
						selected    = event.currentTarget.querySelector( '.fusion-selected-value' ),
						active      = selected.getAttribute( 'data-value' ),
						type        = selected.getAttribute( 'data-id' ),
						activesHold = form.querySelector( '.fuson-options-holder.' + type ),
						actives     = activesHold.querySelectorAll( '.active' ),
						target      = activesHold.querySelector( '[data-value="' + active + '"]' ),
						dropdowns   = form.querySelectorAll( '.fusion-options-wrapper' ),
						targetDrop  = form.querySelector( '.fusion-options-wrapper.' + type );

					if ( actives ) {
						_.each( actives, function( active ) {
							active.classList.remove( 'active' );
						} );
					}

					if ( target ) {
						target.classList.add( 'active' );
					}

					if ( dropdowns ) {
						_.each( dropdowns, function( dropdown ) {
							dropdown.classList.remove( 'visible' );
						} );
					}

					if ( targetDrop ) {
						targetDrop.classList.add( 'visible' );
					}
				},

				handleOptionClick: function( event ) {
					var targetParent;

					if ( event.target.classList.contains( 'fusion-select' ) ) {
						targetParent = this.getClosest( event.target, '.fusion-options-wrapper' );
						if ( targetParent ) {
							targetParent.classList.remove( 'visible' );
						}
					}
				},

				insertFamilyChoices: function( familyHold ) {
					var self        = this,
						familyHold  = 'undefined' === typeof familyHold ? this.getForm().querySelector( '.typography-family' ) : familyHold,
						doc         = this.document,
						searchHold  = doc.createElement( 'div' ),
						search      = doc.createElement( 'input' ),
						searchIcon  = doc.createElement( 'span' ),
						searchFonts = [];

					if ( familyHold.hasChildNodes() || 'undefined' === typeof window.awbTypographySelect.webfonts ) {
						return;
					}

					// Add the search.
					searchIcon.classList.add( 'fusiona-search' );

					self.on( searchIcon, 'click', function( event ) {
						var parent = event.target.parentNode,
							searchInput;

						parent.classList.toggle( 'open' );
						if ( parent.classList.contains( 'open' ) ) {
							parent.querySelector( 'input' ).focus();
						} else {
							searchInput = parent.querySelector( 'input' );
							searchInput.value = '';
							searchInput.dispatchEvent( new Event( 'change' ) );
						}
						self.getForm().querySelector( '.typography-family' ).classList.remove( 'showing-results' );
					} );

					searchHold.appendChild( searchIcon );

					search.setAttribute( 'type', 'search' );
					search.setAttribute( 'name', 'fusion-ifs' );
					search.setAttribute( 'id', 'fusion-ifs' );
					search.placeholder = fusionBuilderText.search;

					self.on( search, 'keydown',  self.handleFontSearch.bind( self ) );
					self.on( search, 'input',  self.handleFontSearch.bind( self ) );
					self.on( search, 'change',  self.handleFontSearch.bind( self ) );

					searchHold.classList.add( 'fusion-ifs-hold' );
					searchHold.appendChild( search );

					familyHold.parentNode.appendChild( searchHold );

					// Add the custom fonts.
					if ( 'object' === typeof window.awbTypographySelect.webfonts.custom && ! _.isEmpty( window.awbTypographySelect.webfonts.custom ) ) {

						// Extra check for different empty.
						if ( 1 !== window.awbTypographySelect.webfonts.custom.length || ! ( 'object' === typeof window.awbTypographySelect.webfonts.custom[ 0 ] && '' === window.awbTypographySelect.webfonts.custom[ 0 ].family ) ) {
							option           = doc.createElement( 'div' );
							option.innerHTML = fusionBuilderText.custom_fonts;
							option.classList.add( 'fusion-cfh' );
							familyHold.appendChild( option );

							_.each( window.awbTypographySelect.webfonts.custom, function( font, index ) {
								if ( font.family && '' !== font.family ) {
									searchFonts.push( {
										id: font.family.replace( /&quot;/g, '&#39' ),
										text: font.label
									} );
								}

								option = doc.createElement( 'div' );
								option.innerHTML = font.label;
								option.setAttribute( 'data-value', font.family );
								option.setAttribute( 'data-id', font.family.replace( /"/g, '' ).replace( /'/g, '' ).toLowerCase() );
								option.setAttribute( 'data-type', 'custom-font' );

								self.on( option, 'click',  self.handleFontChange.bind( self ) );

								familyHold.appendChild( option );
							} );
						}
					}

					// Add the google fonts.
					_.each( window.awbTypographySelect.webfonts.google, function( font, index ) {
						searchFonts.push( {
							id: font.family,
							text: font.label
						} );

						option = doc.createElement( 'div' );
						option.innerHTML = font.label;
						option.setAttribute( 'data-value', font.family );
						option.setAttribute( 'data-id', font.family.replace( /"/g, '' ).replace( /'/g, '' ).toLowerCase() );

						if ( self.loadPreviews ) {
							option.setAttribute( 'style', 'font-family:' + font.family );
							if ( 5 > index ) {
								self.webFontLoad( font.family );
								option.classList.add( 'visible' );
							}
						}

						self.on( option, 'click',  self.handleFontChange.bind( self ) );

						familyHold.appendChild( option );
					} );

					this.searchFonts = searchFonts;
				},

				handleFontSearch: function( event ) {
					var form          = this.getForm(),
						value         = event.target.value,
						$searchHold   = jQuery( form ).find( '.fusion-ifs-hold' ),
						$selectField  = jQuery( form ).find( '.typography-family' ),
						fuseOptions,
						fuse,
						result;

					$selectField.scrollTop( 0 );

					if ( 3 > value.length ) {
						$selectField.find( '> div' ).css( 'display', 'block' );
						return;
					}

					$selectField.find( '> div' ).css( 'display', 'none' );

					fuseOptions = {
						threshold: 0.2,
						location: 0,
						distance: 100,
						maxPatternLength: 32,
						minMatchCharLength: 3,
						keys: [ 'text' ]
					};

					fuse   = new Fuse( jQuery.extend( true, this.searchFonts, {} ), fuseOptions );
					result = fuse.search( value );

					_.each( result, function( resultFont ) {
						$selectField.find( 'div[data-id="' + resultFont.id.replace( /"/g, '' ).replace( /'/g, '' ).toLowerCase() + '"]' ).css( 'display', 'block' );
					} );

					$selectField.addClass( 'showing-results' );
				},

				handleTabClick: function( event ) {
					var form         = this.getForm(),
						link         = event.currentTarget,
						target       = link.getAttribute( 'href' ).replace( '#', '' ),
						tabHold      = form.querySelector( '.fusion-typography-tabs' ),
						navHold      = form.querySelector( '.fusion-typography-nav' ),
						familyHold   = form.querySelector( '.typography-family' ),
						activeFamily = familyHold.querySelector( '.active' ),
						scrollAmount;

					_.each( tabHold.children, function( tab ) {
						if ( target !== tab.getAttribute( 'data-id' ) ) {
							if ( tab.classList.contains( 'active' ) ) {
								tab.classList.remove( 'active' );
							}
						} else {
							tab.classList.add( 'active' );
						}
					} );

					_.each( navHold.querySelectorAll( '.active' ), function( nav ) {
						nav.classList.remove( 'active' );
					} );
					link.classList.add( 'active' );

					if ( ! familyHold.firstChild ) {
						this.insertFamilyChoices();
					} else if ( 'family' === target && activeFamily ) {
						scrollAmount = ( activeFamily.getBoundingClientRect().top + familyHold.scrollTop ) - familyHold.getBoundingClientRect().top;
						scrollAmount = 0 === scrollAmount ? 0 : scrollAmount - 6 - activeFamily.getBoundingClientRect().height;
						familyHold.scrollTop = scrollAmount;
					}
				},

				getParamFromTarget: function( target ) {
					switch ( target ) {
					case 'letterSpacing':
						return 'letter-spacing';
						break;

					case 'lineHeight':
						return 'line-height';
						break;

					case 'fontSize':
						return 'font-size';
						break;

					default:
						return target;
						break;
					}
				},

				handleInputChange: function( event ) { // jshint ignore: line
					var form       = this.getForm(),
						value      = event.target.value,
						target     = event.target.name,
						action     = {},
						iframe     = document.getElementById( 'fb-preview' ),
						iframeWin  = rangy.dom.getIframeWindow( iframe ),
						lineHeight = false,
						param      = this.getParamFromTarget( target ),
						element;

					this.base.restoreSelection();

					element = MediumEditor.selection.getSelectionElement( this.document );

					if ( ! element ) {
						return;
					}

					// If we have an element override, update view param instead.
					if ( 'undefined' !== typeof FusionPageBuilderApp && FusionPageBuilderApp.inlineEditorHelpers.updateParentElementParam( this.parentCid, this.override[ param ], value ) ) {
						return;
					}

					this.classApplier.applyToSelection( iframeWin );

					action[ target ] = value;

					element.querySelectorAll( '.fusion-editing' ).forEach( function( el ) {
						jQuery( el ).css( action );
						el.setAttribute( 'data-fusion-font', true );
						el.classList.remove( 'fusion-editing' );
						if ( 0 === el.classList.length ) {
							el.removeAttribute( 'class' );
						}

						// If font size is changed and line-height not set, update input.
						if ( 'fontSize' === target && ! lineHeight ) {
							lineHeight = form.querySelector( '#line_height' );
							lineHeight.value = 'undefined' !== typeof el.style.lineHeight && el.style.lineHeight ? el.style.lineHeight : window.getComputedStyle( el, null ).getPropertyValue( 'line-height' );
						}
					} );

					this.base.saveSelection();

					this.base.trigger( 'editableInput', {}, element );
				},
				handleFontChange: function( event ) {
					var value    = event.target.getAttribute( 'data-value' ),
						font     = event.target.classList.contains( 'fusion-select' ) ? this.getFontFamily() : value,
						self     = this,
						variant  = value;

					if ( event.target.classList.contains( 'fusion-variant-select' ) ) {
						this.updateSingleVariant( value, event.target.innerHTML );
					} else {
						this.updateSingleFamily();
						variant = this.updateVariants( font );
					}

					event.target.classList.add( 'active' );

					if ( ! font ) {
						return;
					}

					if ( -1 !== window.awbTypographySelect.webfontsStandardArray.indexOf( font ) || this.isCustomFont( font ) ) {
						this.changePreview( font, false, variant );

					} else if ( this.webFontLoad( font, variant, false ) ) {
						self.changePreview( font, true, variant );
					} else {
						jQuery( window ).one( 'fusion-font-loaded', function() {
							self.changePreview( font, true, variant );
						} );
					}
				},

				getFontFamily: function() {
					var form        = this.getForm(),
						familyHold  = form.querySelector( '.typography-family' ),
						active      = familyHold.querySelector( '.active' );

					if ( active ) {
						return active.getAttribute( 'data-value' );
					}
					return false;
				},

				getFontVariant: function() {
					var form   = this.getForm(),
						input  = form.querySelector( '#fusion-variant' );

					if ( input ) {
						return input.getAttribute( 'data-value' );
					}
					return false;
				},

				updateSingleVariant: function( value, label ) {
					var form        = this.getForm(),
						inputDiv    = form.querySelector( '#fusion-variant' ),
						optionsHold = form.querySelector( '.fuson-options-holder.variant' ),
						actives     = optionsHold.querySelectorAll( '.active' );

					inputDiv.setAttribute( 'data-value', value );
					inputDiv.innerHTML = label;

					if ( actives ) {
						_.each( actives, function( active ) {
							active.classList.remove( 'active' );
						} );
					}
				},

				updateSingleFamily: function( value, label ) {
					var form       = this.getForm(),
						familyHold = form.querySelector( '.typography-family' ),
						actives    = familyHold.querySelectorAll( '.active' );

					if ( actives ) {
						_.each( actives, function( active ) {
							active.classList.remove( 'active' );
						} );
					}
				},

				updateVariants: function( font ) {
					var self         = this,
						variants     = this.getVariants( font ),
						form         = this.getForm(),
						holder       = form.querySelector( '.fuson-options-holder.variant' ),
						inputDiv     = form.querySelector( '#fusion-variant' ),
						doc          = this.document,
						hasSelection = false,
						defaultVal   = 'regular',
						currentVal   = inputDiv.getAttribute( 'data-value' );

					while ( holder.firstChild ) {
						holder.removeChild( holder.firstChild );
					}

					if ( ! variants ) {
						variants = [
							{
								id: 'regular',
								label: fusionBuilderText.typography_default
							}
						];
					}

					// If currentVal is within variants, then use as default.
					if ( _.contains( _.pluck( variants, 'id' ), currentVal ) ) {
						defaultVal = currentVal;
					}

					_.each( variants, function( variant ) {
						var option = doc.createElement( 'div' );

						option.className = 'fusion-select fusion-variant-select';
						option.innerHTML = variant.label;
						option.setAttribute( 'data-value', variant.id );

						if ( defaultVal === variant.id ) {
							hasSelection = true;
							option.classList.add( 'active' );
							inputDiv.setAttribute( 'data-value', variant.id );
							inputDiv.innerHTML = variant.label;
						}

						self.on( option, 'click',  self.handleFontChange.bind( self ) );
						holder.appendChild( option );
					} );

					if ( ! hasSelection && holder.firstChild ) {
						holder.firstChild.classList.add( 'active' );
						defaultVal = holder.firstChild.getAttribute( 'data-value' );
						inputDiv.setAttribute( 'data-value', defaultVal );
						inputDiv.innerHTML = holder.firstChild.innerHTML;
					}

					return defaultVal;
				},

				changePreview: function( font, googleFont, variant ) {
					var iframe     = document.getElementById( 'fb-preview' ),
						iframeWin  = rangy.dom.getIframeWindow( iframe ),
						fontWeight = '',
						fontStyle  = '',
						element;

					if ( googleFont && variant ) {
						fontWeight = awbTypographySelect.getFontWeightFromVariant( variant );
						fontStyle  = awbTypographySelect.getFontStyleFromVariant( variant );
					}

					this.base.restoreSelection();

					element = MediumEditor.selection.getSelectionElement( this.document );

					if ( ! element ) {
						return;
					}

					this.classApplier.applyToSelection( iframeWin );

					element.querySelectorAll( '.fusion-editing' ).forEach( function( el ) {
						el.style[ 'font-family' ] = font;
						el.style[ 'font-style' ]  = fontStyle;
						el.style[ 'font-weight' ] = fontWeight;

						el.setAttribute( 'data-fusion-font', true );

						if ( googleFont ) {
							el.setAttribute( 'data-fusion-google-font', font );

							// Variant handling.
							if ( '' !== variant ) {
								el.setAttribute( 'data-fusion-google-variant', variant );
							} else {
								el.removeAttribute( 'data-fusion-google-variant' );
							}

						} else {
							el.removeAttribute( 'data-fusion-google-font' );
							el.removeAttribute( 'data-fusion-google-variant' );
						}

						el.classList.remove( 'fusion-editing' );
						if ( 0 === el.classList.length ) {
							el.removeAttribute( 'class' );
						}
					} );

					this.base.saveSelection();
					this.base.trigger( 'editableInput', {}, element );
				},

				handleFormClick: function( event ) {

					// Make sure not to hide form when clicking inside the form
					event.stopPropagation();
				},

				// TODO: refactor this so its easier to lookup.
				getVariants: function( fontFamily ) {
					var variants = false;

					// Family is a variable, variant only has that selection.
					if ( -1 !== fontFamily.indexOf( 'var(' ) ) {
						return [
							{
								id: fontFamily.replace( '-font-family)', ')' ),
								label: awbTypoData.strings.global
							}
						];
					}

					if ( this.isCustomFont( fontFamily ) ) {
						return [
							{
								id: '400',
								label: 'Normal 400'
							}
						];
					}

					_.each( window.awbTypographySelect.webfonts.standard, function( font ) {
						if ( fontFamily && font.family === fontFamily ) {
							variants = font.variants;
							return font.variants;
						}
					} );

					_.each( window.awbTypographySelect.webfonts.google, function( font ) {
						if ( font.family === fontFamily ) {
							variants = font.variants;
							return font.variants;
						}
					} );
					return variants;
				},

				isCustomFont: function( family ) {
					var isCustom = false;

					// Figure out if this is a google-font.
					_.each( window.awbTypographySelect.webfonts.custom, function( font ) {
						if ( font.family === family ) {
							isCustom = true;
						}
					} );

					return isCustom;
				},
				webFontLoad: function( family, variant ) {
					var isGoogleFont = this.isGoogleFont( family ),
						scriptID,
						script;

					// Early exit if there is no font-family defined.
					if ( _.isUndefined( family ) || '' === family || ! family ) {
						return;
					}

					// Get a valid variant.
					variant = this.getValidVariant( family, variant );

					// Early exit if not a google-font.
					if ( false === isGoogleFont ) {
						return;
					}

					variant = ( _.isUndefined( variant ) || ! variant ) ? ':regular' : ':' + variant;
					family  = family.replace( /"/g, '&quot' );

					script  = family;
					script += ( variant ) ? variant : '';

					scriptID = script.replace( /:/g, '' ).replace( /"/g, '' ).replace( /'/g, '' ).replace( / /g, '' ).replace( /,/, '' );

					if ( ! jQuery( 'head' ).find( '#' + scriptID ).length ) {
						jQuery( 'head' ).first().append( '<script id="' + scriptID + '">WebFont.load({google:{families:["' + script + '"]},context:FusionApp.previewWindow,active: function(){ jQuery( window ).trigger( "fusion-font-loaded"); },});</script>' );
						return false;
					}
					return true;
				},

				isGoogleFont: function( family ) {
					var isGoogleFont = false;

					// Figure out if this is a google-font.
					_.each( window.awbTypographySelect.webfonts.google, function( font ) {
						if ( font.family === family ) {
							isGoogleFont = true;
						}
					} );

					return isGoogleFont;
				},

				getValidVariant: function( family, variant ) {
					var variants   = this.getVariants( family ),
						isValid    = false,
						hasRegular = false,
						first      = ( ! _.isUndefined( variants[ 0 ] ) && ! _.isUndefined( variants[ 0 ].id ) ) ? variants[ 0 ].id : '400';

					if ( 'string' !== typeof variant || '' === variant ) {
						variant = '400';
					}

					// Variable family, set variant value as same variable.
					if ( -1 !== family.indexOf( 'var(' ) ) {
						return family.replace( '-font-family)', ')' );
					}
					if ( this.isCustomFont( family ) ) {
						return '400';
					}

					_.each( variants, function( v ) {
						if ( variant === v.id ) {
							isValid = true;
						}
						if ( 'regular' === v.id || '400' === v.id || 400 === v.id ) {
							hasRegular = true;
						}
					} );

					if ( isValid ) {
						return variant;
					} else if ( hasRegular ) {
						return '400';
					}
					return first;
				}
			} );
			MediumEditor.extensions.fusionTypography = fusionTypographyForm;
		},

		/**
		 * Creates the font-color extension for MediumEditor and adds the form.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		createFontColor: function( event ) { // jshint ignore: line
			var FusionFontColorForm = MediumEditor.extensions.form.extend( {
				name: 'fusionFontColor',
				action: 'fusionFontColor',
				aria: fusionBuilderText.font_color,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusion-color-preview" aria-hidden="true"></i>',
				hasForm: true,
				override: false,
				parentCid: false,

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );
					this.classApplier = rangy.createClassApplier( 'fusion-editing', {
						elementTagName: 'span',
						tagNames: [ 'span', 'b', 'strong', 'a', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ],
						normalize: true
					} );

					this._triggerUpdate = _.debounce( _.bind( this.triggerUpdate, this ), 300 );
				},
				checkState: function( node ) {
					var nodes = MediumEditor.selection.getSelectedElements( this.document ),
						color = this.getExistingValue( nodes );

					if ( 'undefined' !== typeof color ) {
						this.button.querySelector( '.fusion-color-preview' ).style.backgroundColor = color;
					}
				},

				// Called when the button the toolbar is clicked
				// Overrides ButtonExtension.handleClick
				handleClick: function( event ) {
					var nodes,
						font;

					event.preventDefault();
					event.stopPropagation();

					if ( ! this.isDisplayed() ) {

						// Get FontName of current selection (convert to string since IE returns this as number)
						nodes = MediumEditor.selection.getSelectedElements( this.document );
						font  = this.getExistingValue( nodes );
						font  = 'undefined' !== typeof font ? font : '';
						this.showForm( font );
					}

					return false;
				},

				// Get font size which is set.
				getExistingValue: function( nodes ) {
					var nodeIndex,
						color,
						el;

					if ( 'undefined' !== typeof FusionPageBuilderApp ) {
						FusionPageBuilderApp.inlineEditorHelpers.setOverrideParams( this, 'color' );
					}

					// If there are no nodes, use the parent el.
					if ( ! nodes.length ) {
						nodes = this.base.elements;
					}

					for ( nodeIndex = 0; nodeIndex < nodes.length; nodeIndex++ ) {
						el    = nodes[ nodeIndex ];
						color = 'string' == typeof el.style.color && '' !== el.style.color ? el.style.color : jQuery( el ).css( 'color' );
						if ( jQuery( el ).data( 'fusion-font' ) ) {
							return color;
						}
					}

					return color;
				},

				// Called by medium-editor to append form to the toolbar
				getForm: function() {
					if ( ! this.form ) {
						this.form = this.createForm();
					}
					this.on( this.form, 'click', this.handleFormClick.bind( this ) );
					return this.form;
				},

				// Used by medium-editor when the default toolbar is to be displayed
				isDisplayed: function() {
					return this.getForm().classList.contains( 'visible' );
				},

				hideForm: function() {
					var self         = this,
						form         = this.getForm(),
						toolbar      = this.base.getExtensionByName( 'toolbar' ),
						timeoutValue = 50;

					if ( toolbar.toolbar.classList.contains( 'medium-toolbar-arrow-over' ) ) {
						timeoutValue = 300;
					}

					form.classList.add( 'hidden' );
					form.classList.remove( 'visible' );

					setTimeout( function() {
						form.classList.remove( 'hidden' );
					}, 400 );

					this.getInput().value = '';

					setTimeout( function() {
						self.setToolbarPosition();
						self.base.checkSelection();
					}, timeoutValue );
				},

				showForm: function( fontColor ) {
					var self   = this,
						input  = this.getInput(),
						$input = jQuery( input ),
						form   = this.getForm();

					this.base.saveSelection();
					this.hideToolbarDefaultActions();
					form.classList.add( 'visible' );
					form.classList.remove( 'hidden' );

					this.setToolbarPosition();

					$input.val( fontColor || '' ).trigger( 'change' );

					if ( 'undefined' === typeof $input.awbColorPicker( 'instance' ) ) {
						$input.awbColorPicker( {
							width: 250,
							hide: true,
							allowToggle: false,
							change: function( event, ui, value ) {
								self.handleColorChange( value );
							},
							clear: function( event, ui ) {
								self.clearFontColor();
							}
						} );
					}

					if ( -1 === $input.val().indexOf( '--' ) ) {
						$input.awbColorPicker( 'open' );
					} else {
						$input.awbColorPicker( 'openGlobals' );
					}
				},

				// Called by core when tearing down medium-editor (destroy)
				destroy: function() {
					if ( ! this.form ) {
						return false;
					}

					if ( this.form.parentNode ) {
						this.form.parentNode.removeChild( this.form );
					}

					delete this.form;
				},

				doFormSave: function() {

					this.hideForm();
				},

				// Form creation and event handling
				createForm: function() {
					var self   = this,
						doc    = this.document,
						form   = doc.createElement( 'div' ),
						input  = doc.createElement( 'input' ),
						close  = doc.createElement( 'button' );

					// Font Color Form (div)
					this.on( form, 'click', this.handleFormClick.bind( this ) );
					form.className = 'medium-editor-toolbar-form fusion-inline-color-picker';
					form.id        = 'medium-editor-toolbar-form-fontcolor-' + this.getEditorId();

					input.className = 'medium-editor-toolbar-input fusion-builder-color-picker-hex';
					input.type      = 'text';
					input.setAttribute( 'data-alpha', true );
					form.appendChild( input );

					close.className = 'fusion-inline-editor-close';
					close.innerHTML = '<i class="fusiona-check" aria-hidden="true"></i>';
					form.appendChild( close );

					// Handle save button clicks (capture)
					this.on( close, 'click', this.handleSaveClick.bind( this ), true );

					return form;
				},

				getInput: function() {
					return this.getForm().querySelector( 'input.medium-editor-toolbar-input' );
				},

				clearFontColor: function() {

					this.base.restoreSelection();

					// If we have an element override, update view param instead.
					if ( 'undefined' !== typeof FusionPageBuilderApp && FusionPageBuilderApp.inlineEditorHelpers.updateParentElementParam( this.parentCid, this.override, '' ) ) {
						return;
					}

					MediumEditor.selection.getSelectedElements( this.document ).forEach( function( el ) {
						if ( 'undefined' !== typeof el.style && 'undefined' !== typeof el.style.color ) {
							el.style.color = '';
						}
					} );

					this.base.trigger( 'editableInput', {}, MediumEditor.selection.getSelectionElement( this.document ) );

				},

				handleColorChange: function( color ) {
					var iframe    = document.getElementById( 'fb-preview' ),
						iframeWin = rangy.dom.getIframeWindow( iframe ),
						element,
						color = 'undefined' === color || 'undefined' === typeof color ? this.getInput().value : color;

					this.base.restoreSelection();

					// If we have an element override, update view param instead.
					if ( 'undefined' !== typeof FusionPageBuilderApp && FusionPageBuilderApp.inlineEditorHelpers.updateParentElementParam( this.parentCid, this.override, color, true ) ) {
						return;
					}

					element = MediumEditor.selection.getSelectionElement( this.document );

					if ( ! element ) {
						return;
					}

					this.classApplier.applyToSelection( iframeWin );

					element.querySelectorAll( '.fusion-editing' ).forEach( function( el ) {
						if ( el.classList.contains( 'fusion-editing' ) ) {
							jQuery( el ).css( { color: color } );
							el.classList.remove( 'fusion-editing' );

							if ( 0 === el.classList.length ) {
								el.removeAttribute( 'class' );
							}
						}
					} );

					this._triggerUpdate( element );
				},

				triggerUpdate: function( element ) {
					this.base.trigger( 'editableInput', {}, element );
				},

				handleFormClick: function( event ) {

					// Make sure not to hide form when clicking inside the form
					event.stopPropagation();
				},

				handleSaveClick: function( event ) {

					// Clicking Save -> create the font size
					event.preventDefault();
					this.doFormSave();
				}
			} );

			MediumEditor.extensions.fusionFontColor = FusionFontColorForm;
		},

		/**
		 * Creates the drop-cap extension for MediumEditor and adds the form.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		createInlineShortcode: function( event ) { // jshint ignore: line
			var FusionInlineShortcodeForm = MediumEditor.extensions.form.extend( {
				name: 'fusionInlineShortcode',
				action: 'fusionInlineShortcode',
				aria: fusionBuilderText.add_element,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusiona-plus" aria-hidden="true"></i>',
				hasForm: true,

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );

					// Class applier for drop cap element.
					this.dropCapClassApplier = rangy.createClassApplier( 'fusion-inline-shortcode', {
						elementTagName: 'span',
						elementAttributes: {
							'data-inline-shortcode': 'true',
							'data-element': 'fusion_dropcap'
						},
						normalize: true
					} );

					// Class applier for popover element.
					this.popoverClassApplier = rangy.createClassApplier( 'fusion-inline-shortcode', {
						elementTagName: 'span',
						elementAttributes: {
							'data-inline-shortcode': 'true',
							'data-element': 'fusion_popover'
						},
						normalize: true
					} );

					// Class applier for highlight element.
					this.highlightClassApplier = rangy.createClassApplier( 'fusion-inline-shortcode', {
						elementTagName: 'span',
						elementAttributes: {
							'data-inline-shortcode': 'true',
							'data-element': 'fusion_highlight'
						},
						normalize: true
					} );

					// Class applier for tooltip element.
					this.tooltipClassApplier = rangy.createClassApplier( 'fusion-inline-shortcode', {
						elementTagName: 'span',
						elementAttributes: {
							'data-inline-shortcode': 'true',
							'data-element': 'fusion_tooltip'
						},
						normalize: true
					} );

					// Class applier for one page text link element.
					this.onepageClassApplier = rangy.createClassApplier( 'fusion-inline-shortcode', {
						elementTagName: 'span',
						elementAttributes: {
							'data-inline-shortcode': 'true',
							'data-element': 'fusion_one_page_text_link'
						},
						normalize: true
					} );

					// Class applier for modal text link element.
					this.modalLinkClassApplier = rangy.createClassApplier( 'fusion-inline-shortcode', {
						elementTagName: 'span',
						elementAttributes: {
							'data-inline-shortcode': 'true',
							'data-element': 'fusion_modal_text_link'
						},
						normalize: true
					} );
				},

				// Called when the button the toolbar is clicked
				// Overrides ButtonExtension.handleClick
				handleClick: function( event ) {

					event.preventDefault();
					event.stopPropagation();

					if ( this.isDisplayed() ) {
						this.hideForm();
					} else {
						this.showForm();
					}

					return false;
				},

				// Called by medium-editor to append form to the toolbar
				getForm: function() {
					if ( ! this.form ) {
						this.form = this.createForm();
					}
					return this.form;
				},

				// Used by medium-editor when the default toolbar is to be displayed
				isDisplayed: function() {
					return this.getForm().classList.contains( 'visible' );
				},

				hideForm: function() {
					var form = this.getForm();

					form.classList.add( 'hidden' );
					form.classList.remove( 'visible' );
					setTimeout( function() {
						form.classList.remove( 'hidden' );
					}, 400 );
					this.setToolbarPosition();
				},

				showForm: function() {
					var form    = this.getForm();

					this.base.saveSelection();

					form.classList.add( 'visible' );
					form.classList.remove( 'hidden' );

					this.setToolbarPosition();

				},

				// Called by core when tearing down medium-editor (destroy)
				destroy: function() {
					if ( ! this.form ) {
						return false;
					}

					if ( this.form.parentNode ) {
						this.form.parentNode.removeChild( this.form );
					}

					delete this.form;
				},

				// Form creation and event handling
				createForm: function() {
					var doc           = this.document,
						form          = doc.createElement( 'div' ),
						ul            = doc.createElement( 'ul' ),
						dropcap       = doc.createElement( 'button' ),
						highlight     = doc.createElement( 'button' ),
						popover       = doc.createElement( 'button' ),
						tooltip       = doc.createElement( 'button' ),
						onepage       = doc.createElement( 'button' ),
						modalLink     = doc.createElement( 'button' ),
						li            = doc.createElement( 'li' ),
						icon          = doc.createElement( 'i' ),
						tooltipText   = false,
						onepageText   = false,
						popoverText   = false,
						highlightText = false,
						dropcapText   = false,
						modalLinkText = false;

					if ( 'undefined' !== typeof fusionAllElements.fusion_tooltip ) {
						tooltipText = fusionBuilderText.add_unknown.replace( '%s', fusionAllElements.fusion_tooltip.name );
					}
					if ( 'undefined' !== typeof fusionAllElements.fusion_one_page_text_link ) {
						onepageText = fusionBuilderText.add_unknown.replace( '%s', fusionAllElements.fusion_one_page_text_link.name );
					}
					if ( 'undefined' !== typeof fusionAllElements.fusion_popover ) {
						popoverText = fusionBuilderText.add_unknown.replace( '%s', fusionAllElements.fusion_popover.name );
					}
					if ( 'undefined' !== typeof fusionAllElements.fusion_highlight ) {
						highlightText = fusionBuilderText.add_unknown.replace( '%s', fusionAllElements.fusion_highlight.name );
					}
					if ( 'undefined' !== typeof fusionAllElements.fusion_dropcap ) {
						dropcapText = fusionBuilderText.add_unknown.replace( '%s', fusionAllElements.fusion_dropcap.name );
					}
					if ( 'undefined' !== typeof fusionAllElements.fusion_modal_text_link ) {
						modalLinkText = fusionBuilderText.add_unknown.replace( '%s', fusionAllElements.fusion_modal_text_link.name );
					}

					this.base.saveSelection();

					// Font Name Form (div)
					form.className = 'medium-editor-toolbar-form medium-editor-dropdown-toolbar';
					form.id        = 'medium-editor-toolbar-form-shortcode-' + this.getEditorId();
					ul.className   = 'fusion-shortcode-form';

					li.innerHTML = 'Inline Elements';
					ul.appendChild( li );

					// Dropcap element.
					if ( dropcapText ) {
						li                = doc.createElement( 'li' );
						icon.className    = 'fusiona-font';
						dropcap.className = 'fusion-dropcap-add';
						dropcap.setAttribute( 'data-element', 'fusion_dropcap' );
						dropcap.setAttribute( 'title', dropcapText );
						dropcap.setAttribute( 'aria-label', dropcapText );
						dropcap.appendChild( icon );
						dropcap.innerHTML += fusionAllElements.fusion_dropcap.name;
						li.appendChild( dropcap );
						ul.appendChild( li );
						this.on( dropcap, 'click', this.addShortcodeElement.bind( this ), true );
					}

					// Highlight element.
					if ( highlightText ) {
						li                  = doc.createElement( 'li' );
						icon                = doc.createElement( 'i' );
						icon.className      = 'fusiona-H';
						highlight.className = 'fusion-highlight-add';
						highlight.setAttribute( 'data-element', 'fusion_highlight' );
						highlight.setAttribute( 'title', highlightText );
						highlight.setAttribute( 'aria-label', highlightText );
						highlight.appendChild( icon );
						highlight.innerHTML += fusionAllElements.fusion_highlight.name;
						li.appendChild( highlight );
						ul.appendChild( li );
						this.on( highlight, 'click', this.addShortcodeElement.bind( this ), true );
					}

					// Popover element.
					if ( popoverText ) {
						li                = doc.createElement( 'li' );
						icon              = doc.createElement( 'i' );
						icon.className    = 'fusiona-uniF61C';
						popover.className = 'fusion-popover-add';
						popover.setAttribute( 'data-element', 'fusion_popover' );
						popover.setAttribute( 'title', popoverText );
						popover.setAttribute( 'aria-label', popoverText );
						popover.appendChild( icon );
						popover.innerHTML += fusionAllElements.fusion_popover.name;
						li.appendChild( popover );
						ul.appendChild( li );
						this.on( popover, 'click', this.addShortcodeElement.bind( this ), true );
					}

					// Tooltip element.
					if ( tooltipText ) {
						li                = doc.createElement( 'li' );
						icon              = doc.createElement( 'i' );
						icon.className    = 'fusiona-exclamation-sign';
						tooltip.className = 'fusion-tooltip-add';
						tooltip.setAttribute( 'data-element', 'fusion_tooltip' );
						tooltip.setAttribute( 'title', tooltipText );
						tooltip.setAttribute( 'aria-label', tooltipText );
						tooltip.appendChild( icon );
						tooltip.innerHTML += fusionAllElements.fusion_tooltip.name;
						li.appendChild( tooltip );
						ul.appendChild( li );
						this.on( tooltip, 'click', this.addShortcodeElement.bind( this ), true );
					}

					// One Page Text Link element.
					if ( onepageText ) {
						li                = doc.createElement( 'li' );
						icon              = doc.createElement( 'i' );
						icon.className    = 'fusiona-external-link';
						onepage.className = 'fusion-onepage-add';
						onepage.setAttribute( 'data-element', 'fusion_one_page_text_link' );
						onepage.setAttribute( 'title', onepageText );
						onepage.setAttribute( 'aria-label', onepageText );
						onepage.appendChild( icon );
						onepage.innerHTML += fusionAllElements.fusion_one_page_text_link.name;
						li.appendChild( onepage );
						ul.appendChild( li );
						this.on( onepage, 'click', this.addShortcodeElement.bind( this ), true );
					}

					// Modal Text Link element.
					if ( modalLinkText ) {
						li                = doc.createElement( 'li' );
						icon              = doc.createElement( 'i' );
						icon.className    = 'fusiona-external-link';
						modalLink.className = 'fusion-modallink-add';
						modalLink.setAttribute( 'data-element', 'fusion_modal_text_link' );
						modalLink.setAttribute( 'title', modalLinkText );
						modalLink.setAttribute( 'aria-label', modalLinkText );
						modalLink.appendChild( icon );
						modalLink.innerHTML += fusionAllElements.fusion_modal_text_link.name;
						li.appendChild( modalLink );
						ul.appendChild( li );
						this.on( modalLink, 'click', this.addShortcodeElement.bind( this ), true );
					}

					form.appendChild( ul );

					this.on( form, 'click', this.handleFormClick.bind( this ) );

					return form;
				},

				addShortcodeElement: function( element ) {
					var iframe    = document.getElementById( 'fb-preview' ),
						iframeWin = rangy.dom.getIframeWindow( iframe ),
						label     = element.currentTarget.getAttribute( 'data-element' );

					this.base.restoreSelection();

					switch ( label ) {
					case 'fusion_dropcap':
						this.dropCapClassApplier.applyToSelection( iframeWin );
						break;

					case 'fusion_highlight':
						this.highlightClassApplier.applyToSelection( iframeWin );
						break;

					case 'fusion_popover':
						this.popoverClassApplier.applyToSelection( iframeWin );
						break;

					case 'fusion_tooltip':
						this.tooltipClassApplier.applyToSelection( iframeWin );
						break;

					case 'fusion_one_page_text_link':
						this.onepageClassApplier.applyToSelection( iframeWin );
						break;

					case 'fusion_modal_text_link':
						this.modalLinkClassApplier.applyToSelection( iframeWin );
						break;
					}
					this.doFormSave( label );
				},

				handleFormClick: function( event ) {

					// Make sure not to hide form when clicking inside the form
					event.stopPropagation();
				},

				doFormSave: function( label ) {
					var name = '';
					if ( 'undefined' !== typeof label && 'undefined' !== typeof fusionAllElements[ label ].name ) {
						name = fusionAllElements[ label ].name;
					}

					// Make sure editableInput is triggered.
					this.base.trigger( 'editableInput', {}, MediumEditor.selection.getSelectionElement( this.document ) );

					// If auto open is on, pause history.  It will be resumed on element settings close.
					if ( 'undefined' === typeof FusionApp || 'off' === FusionApp.preferencesData.open_settings ) {
						FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.added + ' ' + name + ' ' + fusionBuilderText.element );
					} else if ( 'undefined' !== typeof FusionPageBuilderApp ) {
						FusionPageBuilderApp.inlineEditors.shortcodeAdded = true;
					}

					this.base.checkSelection();
					this.hideForm();
				}
			} );

			MediumEditor.extensions.fusionInlineShortcode = FusionInlineShortcodeForm;
		},

		/**
		 * Creates the font-color extension for MediumEditor and adds the form.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		createAnchor: function( event ) { // jshint ignore: line
			var FusionAnchorForm = MediumEditor.extensions.form.extend( {
				name: 'fusionAnchor',
				action: 'createLink',
				aria: fusionBuilderText.link_options,
				contentDefault: '<b>#</b>;',
				contentFA: '<i class="fusiona-link-solid" aria-hidden="true"></i>',
				hasForm: true,
				tagNames: [ 'a' ],

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );

					this._handleInputChange = _.debounce( _.bind( this.handleInputChange, this ), 500 );
					this._keyUpInputChange  = _.debounce( _.bind( this.keyUpInputChange, this ), 1500 );
				},

				handleClick: function( event ) {
					var nodes,
						font;

					event.preventDefault();
					event.stopPropagation();

					if ( ! this.isDisplayed() ) {
						this.showForm();
					}

					return false;
				},

				// Get font size which is set.
				getExistingValues: function() {
					var values = {
							href: '',
							target: ''
						},
						range = MediumEditor.selection.getSelectionRange( this.document ),
						el    = false;

					if ( 'a' === range.startContainer.nodeName.toLowerCase() ) {
						el = range.startContainer;
					} else if ( 'a' === range.endContainer.nodeName.toLowerCase() ) {
						el = range.endContainer;
					} else if ( MediumEditor.util.getClosestTag( MediumEditor.selection.getSelectedParentElement( range ), 'a' ) ) {
						el = MediumEditor.util.getClosestTag( MediumEditor.selection.getSelectedParentElement( range ), 'a' );
					}

					if ( el ) {
						values.href = el.getAttribute( 'href' );
						values.target = el.getAttribute( 'target' );
					}

					this.href = values.href;

					return values;
				},

				// Called by medium-editor to append form to the toolbar
				getForm: function() {
					if ( ! this.form ) {
						this.form = this.createForm();
					}
					return this.form;
				},

				// Used by medium-editor when the default toolbar is to be displayed
				isDisplayed: function() {
					return this.getForm().classList.contains( 'visible' );
				},

				hideForm: function() {
					var self         = this,
						form         = this.getForm(),
						toolbar      = this.base.getExtensionByName( 'toolbar' ),
						timeoutValue = 50;

					if ( toolbar.toolbar.classList.contains( 'medium-toolbar-arrow-over' ) ) {
						timeoutValue = 300;
					}

					form.classList.add( 'hidden' );
					form.classList.remove( 'visible' );

					setTimeout( function() {
						form.classList.remove( 'hidden' );
					}, 400 );

					this.getHrefInput().value     = '';
					this.getTargetInput().value   = '';
					this.getTargetInput().checked = false;

					setTimeout( function() {
						self.setToolbarPosition();
						self.base.checkSelection();
					}, timeoutValue );
				},

				getHrefInput: function() {
					return this.getForm().querySelector( '#fusion-anchor-href' );
				},

				getTargetInput: function() {
					return this.getForm().querySelector( '.switch-input' );
				},

				showForm: function( fontColor ) {
					var self  = this,
						form  = this.getForm();

					this.base.saveSelection();
					this.hideToolbarDefaultActions();

					form.classList.add( 'visible' );
					form.classList.remove( 'hidden' );

					this.setExistingValues();

					this.setToolbarPosition();

				},

				doFormSave: function() {

					this.hideForm();
				},

				setExistingValues: function() {
					var self   = this,
						values = this.getExistingValues();

					this.getHrefInput().value = values.href;
					this.getTargetInput().value = values.target;

					if ( '_blank' === values.target ) {
						this.getTargetInput().checked = true;
					}

					this.setClearVisibility();
				},

				setClearVisibility: function() {
					var form = this.getForm();

					if ( this.href && '' !== this.href ) {
						form.classList.add( 'has-link' );
					} else {
						form.classList.remove( 'has-link' );
					}
				},

				// Form creation and event handling
				createForm: function() {
					var self        = this,
						doc         = this.document,
						form        = doc.createElement( 'div' ),
						input       = doc.createElement( 'input' ),
						linkSearch  = doc.createElement( 'span' ),
						linkClear   = doc.createElement( 'button' ),
						close       = doc.createElement( 'button' ),
						label       = doc.createElement( 'label' ),
						targetHold  = doc.createElement( 'div' ),
						targetLabel = doc.createElement( 'label' ),
						targetInput = doc.createElement( 'input' ),
						labelSpan   = doc.createElement( 'span' ),
						handleSpan  = doc.createElement( 'span' ),
						helperOn    = doc.createElement( 'span' ),
						helperOff   = doc.createElement( 'span' );

					// Font Color Form (div)
					form.className = 'medium-editor-toolbar-form fusion-inline-anchor fusion-link-selector';
					form.id        = 'medium-editor-toolbar-form-anchor-' + this.getEditorId();

					input.className   = 'medium-editor-toolbar-input fusion-builder-link-field';
					input.id          = 'fusion-anchor-href';
					input.type        = 'text';
					input.placeholder = fusionBuilderText.select_link;
					form.appendChild( input );

					linkSearch.className = 'fusion-inline-anchor-search button-link-selector fusion-builder-link-button fusiona-search';
					form.appendChild( linkSearch );

					linkClear.className = 'button button-small wp-picker-clear';
					linkClear.innerHTML = '<i class="fusiona-eraser-solid" aria-hidden="true"></i>';
					form.appendChild( linkClear );

					label.className = 'switch';
					label.setAttribute( 'for', 'fusion-anchor-target-' + this.getEditorId() );

					targetHold.className = 'fusion-inline-target';

					targetLabel.innerHTML = fusionBuilderText.open_in_new_tab;

					targetHold.appendChild( targetLabel );

					targetInput.className = 'switch-input screen-reader-text';
					targetInput.name      = 'fusion-anchor-target';
					targetInput.id        = 'fusion-anchor-target-' + this.getEditorId();
					targetInput.type      = 'checkbox';
					targetInput.value     = '0';

					labelSpan.className = 'switch-label';
					labelSpan.setAttribute( 'data-on', fusionBuilderText.on );
					labelSpan.setAttribute( 'data-off', fusionBuilderText.off );

					handleSpan.className = 'switch-handle';

					helperOn.className = 'label-helper-calc-on fusion-anchor-target';
					helperOn.innerHTML = fusionBuilderText.on;

					helperOff.className = 'label-helper-calc-off fusion-anchor-target';
					helperOff.innerHTML = fusionBuilderText.off;

					label.appendChild( targetInput );
					label.appendChild( labelSpan );
					label.appendChild( handleSpan );
					label.appendChild( helperOn );
					label.appendChild( helperOff );

					targetHold.appendChild( label );

					form.appendChild( targetHold );

					close.className = 'fusion-inline-editor-close';
					close.innerHTML = '<i class="fusiona-check" aria-hidden="true"></i>';
					form.appendChild( close );

					this.on( input, 'change', this.handleInputChange.bind( this ), true );
					this.on( input, 'blur', this.handleInputChange.bind( this ), true );
					this.on( input, 'keyup', this._keyUpInputChange.bind( this ), true );
					this.on( targetInput, 'change', this._handleInputChange.bind( this ), true );

					// Handle save button clicks (capture)
					this.on( close, 'click', this.handleSaveClick.bind( this ), true );

					this.on( form, 'click', this.handleFormClick.bind( this ) );

					this.on( linkClear, 'click', this.clearLink.bind( this ) );

					setTimeout( function() {
						self.optionSwitch( jQuery( form ) );
						self.optionLinkSelector( jQuery( form ).parent() );
					}, 300 );

					return form;
				},

				clearLink: function( event ) {
					var anchor = this.getHrefInput();

					event.preventDefault();

					anchor.value = '';
					anchor.dispatchEvent( new Event( 'change' ) );
				},

				getFormOpts: function() {
					var targetCheckbox = this.getTargetInput(),
						hrefInput      = this.getHrefInput(),
						opts = {
							value: hrefInput.value.trim(),
							target: '_self',
							skipCheck: true
						};

					this.href = hrefInput.value.trim();

					if ( targetCheckbox && targetCheckbox.checked ) {
						opts.target = '_blank';
					}

					return opts;
				},

				keyUpInputChange: function( event ) {
					this.handleInputChange( event );

					// Return the focus back to the input.
					jQuery( this.getForm() ).find( '.fusion-builder-link-field' ).focus();
				},

				handleInputChange: function( event ) {
					var opts = this.getFormOpts();

					// If form is hidden, do not try to save again.
					if ( ! jQuery( this.getForm() ).hasClass( 'visible' ) ) {
						return;
					}

					this.base.restoreSelection();

					if ( '' === opts.value ) {
						this.execAction( 'unlink', opts );
					} else {
						this.execAction( this.action, opts );
					}

					this.setClearVisibility();
				},

				handleFormClick: function( event ) {

					// Make sure not to hide form when clicking inside the form
					event.stopPropagation();
				},

				handleSaveClick: function( event ) {
					this.handleInputChange( event );

					// Clicking Save -> create the font size
					event.preventDefault();

					this.doFormSave();
				}
			} );

			_.extend( FusionAnchorForm.prototype, FusionPageBuilder.options.fusionSwitchField );
			_.extend( FusionAnchorForm.prototype, FusionPageBuilder.options.fusionLinkSelector );

			MediumEditor.extensions.fusionAnchor = FusionAnchorForm;
		},

		/**
		 * Creates customized version of remove format.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		createRemove: function( event ) { // jshint ignore: line
			var FusionRemoveForm = MediumEditor.extensions.form.extend( {
				name: 'fusionRemoveFormat',
				action: 'fusionRemoveFormat',
				aria: fusionBuilderText.remove_format,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusiona-undo" aria-hidden="true"></i>',
				hasForm: false,

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );
				},

				handleClick: function( event ) {
					var nodes          = MediumEditor.selection.getSelectedElements( this.document ),
						selectionRange = MediumEditor.selection.getSelectionRange( this.document ),
						parentEl       = MediumEditor.selection.getSelectedParentElement( selectionRange );

					event.preventDefault();
					event.stopPropagation();

					// Check for parent el first.
					if ( ! nodes.length && parentEl ) {
						nodes = [ parentEl ];
					}

					nodes.forEach( function( el ) {
						el.removeAttribute( 'data-fusion-font' );
						el.removeAttribute( 'data-fusion-google-font' );
						el.removeAttribute( 'data-fusion-google-variant' );
						el.style[ 'line-height' ]    = '';
						el.style[ 'font-size' ]      = '';
						el.style[ 'font-family' ]    = '';
						el.style[ 'letter-spacing' ] = '';

						if ( '' === el.getAttribute( 'style' ) ) {
							el.removeAttribute( 'style' );
						}
						if ( 0 === el.classList.length ) {
							el.removeAttribute( 'class' );
						}
					} );

					this.execAction( 'removeFormat', { skipCheck: true } );

					this.base.checkSelection();

					return false;
				}
			} );

			MediumEditor.extensions.fusionRemoveFormat = FusionRemoveForm;
		},

		/*
		 * Creates customized version of remove format.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @returns {void}
		 */
		createIndent: function( event ) { // jshint ignore: line
			var fusionIndent = MediumEditor.extensions.form.extend( {
				name: 'fusionIndent',
				action: 'fusionIndent',
				aria: fusionBuilderText.indent,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusiona-indent" aria-hidden="true"></i>',
				hasForm: false,

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );
					this.classApplier = rangy.createClassApplier( 'fusion-editing', {
						elementTagName: 'p',
						tagNames: [ 'blockquote', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ],
						normalize: true
					} );
				},

				handleClick: function( event ) {
					var element   = MediumEditor.selection.getSelectionElement( this.document ),
						iframe    = document.getElementById( 'fb-preview' ),
						iframeWin = rangy.dom.getIframeWindow( iframe ),
						paddingLeft;

					event.preventDefault();
					event.stopPropagation();

					this.classApplier.applyToSelection( iframeWin );

					element.querySelectorAll( '.fusion-editing' ).forEach( function( el ) {
						el.classList.remove( 'fusion-editing' );
						if ( 0 === el.classList.length ) {
							el.removeAttribute( 'class' );
						}
						paddingLeft = ( Math.round( parseInt( jQuery( el ).css( 'padding-left' ) ) / 40 ) * 40 ) + 40;
						jQuery( el ).css( { 'padding-left': paddingLeft } );
					} );

					this.base.saveSelection();

					this.base.trigger( 'editableInput', {}, element );

					this.base.checkSelection();

					return false;
				}
			} );

			MediumEditor.extensions.fusionIndent = fusionIndent;
		},

		/*
		 * Creates customized version of remove format.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @returns {void}
		 */
		createOutdent: function( event ) { // jshint ignore: line
			var fusionOutdent = MediumEditor.extensions.form.extend( {
				name: 'fusionOutdent',
				action: 'fusionOutdent',
				aria: fusionBuilderText.outdent,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusiona-outdent" aria-hidden="true"></i>',
				hasForm: false,

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );
					this.classApplier = rangy.createClassApplier( 'fusion-editing', {
						elementTagName: 'p',
						tagNames: [ 'blockquote', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ],
						normalize: true
					} );
				},

				handleClick: function( event ) {
					var element   = MediumEditor.selection.getSelectionElement( this.document ),
						iframe    = document.getElementById( 'fb-preview' ),
						iframeWin = rangy.dom.getIframeWindow( iframe ),
						paddingLeft;

					event.preventDefault();
					event.stopPropagation();

					this.classApplier.applyToSelection( iframeWin );

					element.querySelectorAll( '.fusion-editing' ).forEach( function( el ) {
						el.classList.remove( 'fusion-editing' );
						if ( 0 === el.classList.length ) {
							el.removeAttribute( 'class' );
						}
						paddingLeft = ( Math.round( parseInt( jQuery( el ).css( 'padding-left' ) ) / 40 ) * 40 ) - 40;
						jQuery( el ).css( { 'padding-left': paddingLeft } );
					} );

					this.base.saveSelection();

					this.base.trigger( 'editableInput', {}, element );

					this.base.checkSelection();

					return false;
				}
			} );

			MediumEditor.extensions.fusionOutdent = fusionOutdent;
		}

	} );
}( jQuery ) );

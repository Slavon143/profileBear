/* global awbSetup, ajaxurl, fusionBuilderText, WebFont, awbPrebuilts, avadaAdminL10nStrings, awbSetupWizard */
window.awbSetupWizard = {
	shortcodes: {},
	schemes: {},
	header: '',
	headerOptions: {},
	footer: '',
	counter: 0,
	content: '',
	regExpShortcode: _.memoize( function( tag ) {
		return new RegExp( '\\[(\\[?)(' + tag + ')(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*(?:\\[(?!\\/\\2\\])[^\\[]*)*)(\\[\\/\\2\\]))?)(\\]?)' );
	} ),
	_keyStr: 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=',
	globalOptions: {},
	loadedFonts: {},
	colors: [],
	_colorsCache: [], // Don't use this variable to schemes colors, use the function instead.
	schemesLoaded: false,

	activeTypoSetData: {},
	_typographyCache: [], // Don't use this variable to schemes colors, use the function instead.
	typographyLoaded: false,

	// Stores current import stage, ie installing or activating plugin or importing prebuilt.
	currentImportStage: '',


	/* eslint-disable no-bitwise, no-mixed-operators */
	base64Encode: function( data ) {
		var b64 = this._keyStr,
			o1,
			o2,
			o3,
			h1,
			h2,
			h3,
			h4,
			bits,
			i      = 0,
			ac     = 0,
			enc    = '',
			tmpArr = [],
			r;

		if ( ! data ) {
			return data;
		}

		data = unescape( encodeURIComponent( data ) );

		do {

			// Pack three octets into four hexets
			o1 = data.charCodeAt( i++ );
			o2 = data.charCodeAt( i++ );
			o3 = data.charCodeAt( i++ );

			bits = o1 << 16 | o2 << 8 | o3;

			h1 = bits >> 18 & 0x3f;
			h2 = bits >> 12 & 0x3f;
			h3 = bits >> 6 & 0x3f;
			h4 = bits & 0x3f;

			// Use hexets to index into b64, and append result to encoded string.
			tmpArr[ ac++ ] = b64.charAt( h1 ) + b64.charAt( h2 ) + b64.charAt( h3 ) + b64.charAt( h4 );
		} while ( i < data.length );

		enc = tmpArr.join( '' );
		r   = data.length % 3;

		return ( r ? enc.slice( 0, r - 3 ) : enc ) + '==='.slice( r || 3 );
	},

	base64Decode: function( input ) {
		var output = '',
			chr1,
			chr2,
			chr3,
			enc1,
			enc2,
			enc3,
			enc4,
			i = 0;

		input = input.replace( /[^A-Za-z0-9+/=]/g, '' );

		while ( i < input.length ) {

			enc1 = this._keyStr.indexOf( input.charAt( i++ ) );
			enc2 = this._keyStr.indexOf( input.charAt( i++ ) );
			enc3 = this._keyStr.indexOf( input.charAt( i++ ) );
			enc4 = this._keyStr.indexOf( input.charAt( i++ ) );

			chr1 = ( enc1 << 2 ) | ( enc2 >> 4 );
			chr2 = ( ( enc2 & 15 ) << 4 ) | ( enc3 >> 2 );
			chr3 = ( ( enc3 & 3 ) << 6 ) | enc4;

			output = output + String.fromCharCode( chr1 );

			if ( 64 !== enc3 ) {
				output = output + String.fromCharCode( chr2 );
			}
			if ( 64 !== enc4 ) {
				output = output + String.fromCharCode( chr3 );
			}

		}

		output = this.utf8Decode( output );

		return output;
	},

	utf8Decode: function( utftext ) {
		var string = '',
			i  = 0,
			c  = 0,
			c2 = 0,
			c3;

		while ( i < utftext.length ) {

			c = utftext.charCodeAt( i );

			if ( 128 > c ) {
				string += String.fromCharCode( c );
				i++;
			} else if ( ( 191 < c ) && ( 224 > c ) ) {
				c2 = utftext.charCodeAt( i + 1 );
				string += String.fromCharCode( ( ( c & 31 ) << 6 ) | ( c2 & 63 ) );
				i += 2;
			} else {
				c2 = utftext.charCodeAt( i + 1 );
				c3 = utftext.charCodeAt( i + 2 );
				string += String.fromCharCode( ( ( c & 15 ) << 12 ) | ( ( c2 & 63 ) << 6 ) | ( c3 & 63 ) );
				i += 3;
			}
		}
		return string;
	},
	/* eslint-enable no-bitwise, no-mixed-operators */

	/**
	 * Run actions on load.
	 *
	 * @since 7.5
	 *
	 * @return {void}
	 */
	init: function() {
		this.$el          = jQuery( '.avada-dashboard' );
		this.isRegistered = awbSetup.isRegistered;
		this.activeStep   = this.isRegistered ? 2 : 1;
		this.$steps       = this.$el.find( '.awb-wizard-steps' );
		this.saveChange   = awbSetup.saveChange;
		this.studioURL    = awbSetup.studioURL;

		// Studio option init.
		this.modal = window.awbStudioModal.init( this.$el, awbSetup.studioData );

		// All elements for processing content.
		this.shortcodeTags = _.keys( awbSetup.allElements ).join( '|' );

		// Cache the colors from received from remote, to make the website faster.
		this.getColorSchemasFromRemote();

		// Cache the typography from received from remote, to make the website faster.
		this.getTypographiesFromRemote();

		// Listeners for events.
		this.addListeners();

	},

	/**
	 * Add needed listeners.
	 *
	 * @since 7.5
	 *
	 * @return {void}
	 */
	addListeners: function() {
		var self = this;

		// Listen for step change click.
		this.$el.find( '.awb-wizard-link' ).on( 'click', function( event ) {
			var target      = jQuery( this ).attr( 'data-id' ),
				$activeStep = self.$el.find( '.awb-setup-wizard-section[data-step="' + self.activeStep + '"]' );

			event.preventDefault();

			// Check if active step has unsaved changes.
			if ( '1' === $activeStep.attr( 'data-save' ) ) {
				if ( ! window.confirm( self.saveChange ) ) { // eslint-disable-line no-alert
					return;
				}
			}

			if ( target ) {
				// add tab attr to the avada-dashboard parent for styling if needed.
				const dbWrap = jQuery( this ).parents( '.avada-dashboard' );
				dbWrap.attr( 'data-current-tab', target );

				if ( 'setup_type' === target ) {
					if ( 'scratch' === self.$el.find( 'input#setup_type' ).val() ) {
						self.changeStep( 4 );
						self.setMenuState( 'scratch' );
					} else {
						self.changeStep( 20 );
						self.setMenuState( 'prebuilt' );
					}
				} else {
					self.changeStep( target );
				}
			}


			// layouts
			if ( 6 == target ) { // 6 is layouts tab.
				// calc layouts iframe.
				layoutIframeScale();

				const headerGroup = self.$el.find( '.awb-wizard-option-group[data-layout="header"]' );
				const headerIframe = headerGroup.find( 'iframe' );
				const headerOptions = headerGroup.find( '.awb-setup-import-options' );
				self.updatePreviewIframe( headerOptions, headerIframe );

				const footerGroup = self.$el.find( '.awb-wizard-option-group[data-layout="footer"]' );
				const footerIframe = footerGroup.find( 'iframe' );
				const footerOptions = footerGroup.find( '.awb-setup-import-options' );
				self.updatePreviewIframe( footerOptions, footerIframe );
			}
		} );

		// Studio modal link.
		this.$el.find( '.awb-studio-link' ).on( 'click', function( event ) {
			var defaultModalOpenOptions = {
				'overwrite-type': 'inherit'
			};

			event.preventDefault();

			self.modal.openModal( jQuery( this ).attr( 'data-context' ), jQuery( this ).closest( '.pyre_metabox_field' ), defaultModalOpenOptions );
		} );

		// Start type selection.
		this.$el.find( '.avada-setup-select > div' ).on( 'click', function( event ) {
			var $element = jQuery( this ),
				$option  = $element.parent();

			event.preventDefault();

			if ( $element.hasClass( 'active' ) ) {
				return;
			}

			$option.find( '.active' ).removeClass( 'active' );

			$element.addClass( 'active' );
			$option.find( 'input' ).val( $element.attr( 'data-value' ) );

			self.setMenuState( 'intro' );
		} );

		// Listen for color scheme selection.
		this.$el.find( '.schemes' ).on( 'click', 'li', showColorPreviewSection );

		this.$el.find( '.awb-setup-wizard-custom-colors-btn' ).on( 'click', function() {
			showColorPreviewSection();
			setTimeout( function() {
				if ( ! jQuery( '.awb-setup-wizard-custom-colors-wrapper' ).is( ':visible' ) ) {
					jQuery( '.awb-setup-wizard-toggle-custom-palette' ).trigger( 'click' );
				}
			}, 300 );
		} );

		function showColorPreviewSection() {
			var $sectionContent = jQuery( '.awb-setup-wizard-section[data-step="4"] .awb-setup-wizard-content' );
			jQuery( this ).addClass( 'active' ).siblings().removeClass( 'active' );
			self.setColorVars();
			self.scrollTo();

			self.$el.find( '#color-selection' ).fadeOut( 300, function() {
				jQuery( '.awb-setup-wizard-color-buttons' ).addClass( 'hidden' );
				$sectionContent.addClass( 'awb-setup-wizard-content-secondary' );
				self.$el.find( '.color-confirm' ).fadeIn( 300 );
			} );
		}

		// Listen for color mode change.
		this.$el.find( '#dark_light' ).on( 'change', function() {
			self.setGlobalColorsForPreview();
		} );

		// Listen for color back button click.
		this.$el.find( '.color-confirm .awb-choices .awb-button[href="#selection"]' ).on( 'click', function( event ) {
			var $sectionContent = jQuery( '.awb-setup-wizard-section[data-step="4"] .awb-setup-wizard-content' );
			event.preventDefault();

			self.disableCustomPalette();
			self.$el.find( '.color-confirm' ).fadeOut( 300, function() {
				jQuery( '.awb-setup-wizard-color-buttons' ).removeClass( 'hidden' );
				$sectionContent.removeClass( 'awb-setup-wizard-content-secondary' );
				self.$el.find( '#color-selection' ).fadeIn( 300 );
			} );
		} );

		// Next or previous scheme shuffle.
		this.$el.find( '.scheme-nav .awb-button' ).on( 'click', function( event ) {
			var $target = false;

			event.preventDefault();

			if ( '#next' === jQuery( this ).attr( 'href' ) ) {
				$target = self.$el.find( '.schemes li.active' ).next();
			} else {
				$target = self.$el.find( '.schemes li.active' ).prev();
			}

			if ( $target && $target.length ) {
				self.$el.find( '.schemes li.active' ).removeClass( 'active' );
				$target.addClass( 'active' );
				self.setColorVars();
			}
		} );

		// Listen for color filter selection.
		this.$el.find( '.color-categories' ).on( 'click', 'li', function() {
			if ( jQuery( this ).hasClass( 'active' ) ) {
				jQuery( this ).removeClass( 'active' );
				self.filterColors();
			} else {
				self.$el.find( '.color-categories li.active' ).removeClass( 'active' );
				jQuery( this ).addClass( 'active' );

				self.filterColors();
			}
		} );

		// Listen for option change and enable save.
		this.$el.find( '#pyre_base_size, #pyre_sizing_type' ).on( 'change keyup fusion-changed', function() {
			self.updateFontSizePreview();
		} );

		// Need plugins check.
		this.$el.find( '.awb-feature' ).on( 'change', function() {
			const settings = jQuery( this ).data( 'settings' );
			const plugins = settings.plugins;

			if ( plugins && Array.isArray( plugins ) ) {
				plugins.forEach( ( plugin ) => {
					plugin = self.$el.find( '.awb-db-feature:not(.activated) [name="awb-needed-plugin-' +  plugin + '"]' );
					if ( plugin.length ) {
						plugin.prop( 'checked', jQuery( this ).prop( 'checked' ) );
					}
				} );
			}
		} );

		// toggle icon.
		this.$el.find( '.awb-toggle-info-icon' ).on( 'click', function( event ) {
			event.preventDefault();
			event.stopPropagation();

			const selector = jQuery( this ).data( 'selector' );
			const speed = jQuery( this ).data( 'speed' ) || 200;
			jQuery( selector ).slideToggle( speed );
		} );

		// Layouts Iframe scale.
		function layoutIframeScale() {
			self.$el.find( '.awb-iframe-preview' ).each( function() {
				var height;

				const 	wrap = jQuery( this ),
						wrapWidth = wrap.width(),
						iframe = wrap.children( 'iframe' ),
						iframeWidth = iframe.attr( 'width' ),
						iframeHeight = iframe.attr( 'height' );

				let scale = 1;

				if ( wrapWidth < iframeWidth ) {
					scale = ( wrapWidth / iframeWidth * 100 ) / 100;
					height = parseInt( ( iframeHeight * scale ) + 100 ) + 'px';  // import options height + nice empty space.

					wrap.css( {
						height
					} );
				}
				iframe.css( {
					transform: 'scale(' + scale + ')'
				} );

			} );
		}

		layoutIframeScale();
		jQuery( window ).on( 'resize', function() {
			layoutIframeScale();
		} );

		// Studio options listener ( header and footer ).
		this.$el.find( '.awb-setup-import-options .awb-import-option input[type=radio]' ).on( 'change', function( event ) {
			const 	parent = jQuery( this ).parents( '.awb-wizard-option-group' ),
					$element = parent.find( '.awb-setup-import-options' ),
					iframe = parent.find( '.awb-iframe-preview iframe' );

			self.updatePreviewIframe( $element, iframe, event );
			self.setLayoutOptions( parent );
		} );

		this.$el.find( '.awb-wizard-option-group input.layout-input-id' ).on( 'change', function( event, url ) {
			const 	val = jQuery( this ).val(),
					parent = jQuery( this ).parents( '.awb-wizard-option-group' ),
					iframe = parent.find( '.awb-iframe-preview iframe' ),
					defaultButton = parent.find(  'a.set-layout-to-default' );

			if ( val ) {
				iframe.attr( 'src', url + '?template-only=1' );
				defaultButton.show();
			} else {
				iframe.attr( 'src', iframe.attr( 'data-default' ) );
				defaultButton.hide();
			}
		} );

		this.$el.find( '.awb-wizard-option-group a.set-layout-to-default' ).on( 'click', function( event ) {
			event.preventDefault();
			const 	parent = jQuery( this ).parents( '.awb-wizard-option-group' ),
					context = jQuery( this ).data( 'context' );

			parent.find( 'input[name=' + context + ']' ).val( '' ).trigger( 'change' );
		} );

		this.$el.find( '.awb-wizard-option-group .awb-iframe-preview iframe' ).on( 'load', function() {
			const iframe = jQuery( this );
			const $element = iframe.parents( '.awb-iframe-preview' ).find( '.awb-setup-import-options' );
			self.updatePreviewIframe( $element, iframe );
		} );

		window.addEventListener( 'message', ( event ) => {

			const data = event.data;
			if ( data.setup_wizard_layouts ) {
				let height = data.content_height;
				const type = data.content_type;

				// defaults is the starter header and footer height.
				if ( !parseInt( height ) ) {
					if ( 'header' === type ) {
						height = 100;
					} else {
						height = 520;
					}
				}

				self.$el.find( '.awb-wizard-option-group[data-layout="' + type + '"] .awb-iframe-preview iframe' ).attr( 'height', height );
				layoutIframeScale();

				if ( data.side_header ) {
					self.$el.find( '.awb-wizard-option-group[data-layout="header"] .awb-iframe-preview' ).addClass( 'has-side-header' );
				} else {
					self.$el.find( '.awb-wizard-option-group[data-layout="header"] .awb-iframe-preview' ).removeClass( 'has-side-header' );
				}
			}

		}, false );

		// Dependency listener.
		this.$el.find( '#pyre_ptb_selection' ).on( 'change', function() {
			if ( 'none' === jQuery( this ).val() ) {
				self.$el.find( '.awb-ptb-layout' ).addClass( 'hidden' );
			} else {
				self.$el.find( '.awb-ptb-layout' ).removeClass( 'hidden' );
			}
		} );

		// Listen for prebuilt filter selection.
		this.$el.find( '.avada-db-demos-themes .fusion-admin-box .button-select-prebuilt' ).on( 'click', function() {
			// Add class to mark selected prebuilt.
			jQuery( this ).closest( '.fusion-admin-box' ).addClass( 'active' );

			// Advance to next step.
			self.changeStep( 21 );
		} );

		// TODO: Check once prebuilt path is completed.
		this.$el.find( '.awb-setup-build[data-confirm="prebuilt"]' ).on( 'click', function( event ) {
			event.preventDefault();

			jQuery( this ).addClass( 'awb-build-working' );

			self.prepareSetupImport();
		} );

		// Complete the build process.
		this.$el.find( '.awb-setup-build[data-confirm="scratch"]' ).on( 'click', function( event ) {
			event.preventDefault();

			jQuery( this ).addClass( 'awb-build-working' );

			// Clear previously checked items (except plugins).
			self.$el.find( '.confirm-setup.scratch-confirm-step .checklist li:not([data-plugin])' ).removeClass( 'checked' );

			self.scratchBuild();
		} );

		this.$el.find( '#activate-plugins' ).on( 'click', function( event ) {
			event.preventDefault();

			jQuery( this ).addClass( 'disabled' ).css( 'pointer-events', 'none' );
			jQuery( '#awb-activate-plugins-loader' ).css( 'display', 'inline-block' );
			self.activateAvadaPlugins();
		} );

		jQuery( 'body' ).on( 'click', '.awb-setup-dialog-close', function( event ) {
			event.preventDefault();

			jQuery( this ).siblings( '.ui-dialog-titlebar-close' ).trigger( 'click' );
		} );

		this.$el.find( '.awb-setup-wizard-toggle-custom-palette' ).on( 'click', this.toggleCustomPalette.bind( this ) );

		this.$el.find( '.awb-setup-wizard-change-color-toggler' ).on( 'click', this.toggleCustomColor.bind( this ) );

		this.$el.find( '.awb-setup-wizard-change-color-name' ).on( 'keyup change', this.handleColorNameChange.bind( this ) );

		window.addEventListener( 'awb-setup-success', this.setupSuccess );
		window.addEventListener( 'awb-setup-error', this.setupError );
	},

	setColorVars: function() {
		var	$scheme  = this.$el.find( '.schemes li.active' ),
			id       = 1,
			colors   = {};

		$scheme.find( '.color-swatch span' ).each( function() {
			var $togglerName = jQuery( '.awb-setup-wizard-change-color-toggler[data-color-id="' + id + '"] .awb-setup-wizard-color-name' );
			colors[ 'color' + id ] = { color: jQuery( this ).attr( 'data-value' ), label: $togglerName.html() };
			id++;
		} );

		this.colors = colors;
		this.setGlobalColorsForPreview();
	},

	setGlobalColorsForPreview: function() {
		var $preview = this.$el.find( '.color-preview, .awb-setup-wizard-scheme-mini-preview' ),
			$typos   = this.$el.find( '.awb-setup-wizard-style-preview-typography, .awb-setup-wizard-typography-list, .awb-db-preview-grid' ),
			elementsToModify = $preview.add( $typos ),
			mode    = this.$el.find( '#dark_light' ).val(),
			i,
			colorId,
			colors = this.colors,
			colorSlug,
			color,
			colorObj;

		for ( colorSlug in colors ) {
			if ( 'string' === typeof colors[ colorSlug ].color ) {
				color = colors[ colorSlug ].color;
				colorId = colorSlug.match( /\d/ );
				colorId = colorId[ 0 ];

				for ( i = 0; i < elementsToModify.length; i++ ) {
					colorObj = jQuery.Color( color );
					elementsToModify[ i ].style.setProperty( '--awb-color' + colorId, color );
					elementsToModify[ i ].style.setProperty( '--awb-color' + colorId + '-h', colorObj.hue() );
					elementsToModify[ i ].style.setProperty( '--awb-color' + colorId + '-s', ( colorObj.saturation() * 100 ) + '%' );
					elementsToModify[ i ].style.setProperty( '--awb-color' + colorId + '-l', ( colorObj.lightness() * 100 ) + '%' );
					elementsToModify[ i ].style.setProperty( '--awb-color' + colorId + '-a', ( colorObj.alpha() * 100 ) + '%' );
				}
			}
		}

		if ( 'dark' === mode ) {
			elementsToModify = elementsToModify.filter( '.awb-setup-wizard-allow-invert' );
			for ( colorSlug in colors ) {
				if ( 'string' === typeof colors[ colorSlug ].color ) {
					color = colors[ colorSlug ].color;
					colorId = colorSlug.match( /\d/ );
					colorId = 9 - parseInt( colorId[ 0 ] );

					for ( i = 0; i < elementsToModify.length; i++ ) {
						colorObj = jQuery.Color( color );
						elementsToModify[ i ].style.setProperty( '--awb-color' + colorId, color );
						elementsToModify[ i ].style.setProperty( '--awb-color' + colorId + '-h', colorObj.hue() );
						elementsToModify[ i ].style.setProperty( '--awb-color' + colorId + '-s', ( colorObj.saturation() * 100 ) + '%' );
						elementsToModify[ i ].style.setProperty( '--awb-color' + colorId + '-l', ( colorObj.lightness() * 100 ) + '%' );
						elementsToModify[ i ].style.setProperty( '--awb-color' + colorId + '-a', ( colorObj.alpha() * 100 ) + '%' );
					}
				}
			}
		}
	},

	/**
	 * For a color id, set either the color, label, or both.
	 *
	 * Ex: setCustomColorVal(1, {color: '#fff'}), setCustomColorVal(1, {label: 'Color 1'}).
	 *
	 * @param {int} id
	 * @param {Object} color
	 */
	setCustomColorVal: function( id, color ) {
		var newColor = Object.assign( {}, this.colors[ 'color' + id ], color ); // eslint-disable-line prefer-object-spread

		this.colors[ 'color' + id ] = newColor;
		this.setGlobalColorsForPreview();
	},

	/**
	 * Filter visible color schemes.
	 *
	 * @since 7.5
	 *
	 * @return {void}
	 */
	filterColors: function() {
		var $schemes    = this.$el.find( '.schemes li' ),
			$categories = this.$el.find( '.color-categories li.active' ),
			targets     = [];

		$categories.each( function() {
			targets.push( jQuery( this ).attr( 'data-id' ) );
		} );

		if ( $categories.length ) {
			$schemes.addClass( 'hidden' );
			setTimeout( function() {
				var toDisplay  = jQuery();
				$schemes.css( 'display', 'none' );
				$schemes.each( function() {
					var $scheme    = jQuery( this ),
						categories = $scheme.attr( 'data-categories' );

					targets.forEach( function( target ) {
						if ( categories.includes( target ) ) {
							toDisplay = toDisplay.add( $scheme );
						}
					} );
				} );

				toDisplay.css( 'display', 'block' );
				$schemes.height(); // force repaint.
				toDisplay.removeClass( 'hidden' );
			}, 260 );

		} else {
			$schemes.addClass( 'hidden' );
			setTimeout( function() {
				$schemes.css( 'display', 'block' );
				$schemes.height(); // force repaint.
				$schemes.removeClass( 'hidden' );
			}, 300 );
		}
	},

	/**
	 * Import Global Options.
	 *
	 * @since 7.7
	 *
	 * @return {promise}
	 */
	setGlobalOptions: function() {
		var self = this,
			typoSet = {};

		this.currentImportStage = 'set-global-options';

		jQuery( '.awb-setup-wizard-section[data-step="' + self.activeStep + '"] li[data-action="set-globals"]' ).addClass( 'awb-working' );

		typoSet = jQuery.extend( true, {}, self.activeTypoSetData );
		delete typoSet.name;

		// Add an endpoint to updates site for this.
		return jQuery.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'awb_save_globals',
				scheme: this.colors,
				typo_set: typoSet,
				sizing_ratio: self.$el.find( '#pyre_sizing_type' ).val(),
				base_size: parseFloat( self.$el.find( 'input[name="base_size"]' ).val() ),
				awb_setup_nonce: jQuery( '#awb-setup-nonce' ).val(),
				dark_light: jQuery( '#dark_light' ).val()
			},
			dataType: 'json'
		} )
		.done( function( response ) {
			self.globalOptions = response.data;

			jQuery( '.awb-setup-wizard-section[data-step="' + self.activeStep + '"] li[data-action="set-globals"]' ).removeClass( 'awb-working' ).addClass( 'checked' );
		} );
	},

	/**
	 * Import selected pages.
	 *
	 * @since 7.7
	 *
	 * @return {promise}
	 */
	importPages: function() {
		const 	dummy_content = this.$el.find( '.dummy-content-button input[type=checkbox]' ).is( ':checked' ),
				header = this.$el.find( '.layout-input input[name="header"]' ).val(),
				pages         = [],
				self          = this;

		jQuery( '.awb-setup-wizard-section[data-step="' + self.activeStep + '"] li[data-action="import-pages"]' ).addClass( 'awb-working' );

		this.$el.find( '.awb-db-preview-grid:not( .awb-db-needed-plugins ) .awb-db-preview' ).each( function() {
			if ( jQuery( this ).find( '.awb-feature' ).is( ':checked' ) ) {
				const settings = jQuery( this ).find( '.awb-feature' ).data( 'settings' );
				settings.title = jQuery( this ).find( '.awb-feature-title' ).val();

				pages.push( settings  );
			}
		} );

		return jQuery.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'awb_import_setup_pages',
				scheme: this.colors,
				awb_setup_nonce: jQuery( '#awb-setup-nonce' ).val(),
				dark_light: jQuery( '#dark_light' ).val(),
				pages,
				dummy_content,
				header,
				overwriteType: 'inherit',
				imagesImport: 'dont-import-images'
			},
			dataType: 'json'
		} )
		.done( function( response ) {
			jQuery( '.awb-setup-wizard-section[data-step="' + self.activeStep + '"] li[data-action="import-pages"]' ).removeClass( 'awb-working' ).addClass( 'checked' );
			if ( response.pages ) {
				self.pages = response.pages;
			}
		} )
		.fail( function() {
			// Page import failed.
		} );
	},

	/**
	 * Save the header content to a new header layout section set to global.
	 *
	 * @since 7.7
	 *
	 * @return {promise}
	 */
	saveLayouts: function() {
		var self = this;

		this.currentImportStage = 'save-layouts';

		jQuery( '.awb-setup-wizard-section[data-step="' + self.activeStep + '"] li[data-action="save-layouts"]' ).addClass( 'awb-working' );

		return jQuery.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'awb_save_setup_layouts',
				header: self.$el.find( '.layout-input input[name="header"]' ).val(),
				header_options: self.$el.find( '.layout-input input[name="header-options"]' ).val(),
				footer: self.$el.find( '.layout-input input[name="footer"]' ).val(),
				footer_options: self.$el.find( '.layout-input input[name="footer-options"]' ).val(),
				logo_url: self.$el.find( 'input[name="awb_logo[url]"]' ).val(),
				logo_id: self.$el.find( 'input[name="awb_logo[id]"]' ).val(),
				site_title: self.$el.find( '#site_title' ).val(),
				awb_setup_nonce: jQuery( '#awb-setup-nonce' ).val(),
				overwriteType: 'inherit',
				imagesImport: 'dont-import-images',
				pages: self.pages
			},
			dataType: 'json'
		} )
		.done( function() {

			jQuery( '.awb-setup-wizard-section[data-step="' + self.activeStep + '"] li[data-action="save-layouts"]' ).removeClass( 'awb-working' ).addClass( 'checked' );

		} );
	},

	/**
	 * Finalise scratch import.
	 *
	 * @since 7.7
	 *
	 * @return {promise}
	 */
	finaliseScratchSetup: function() {
		var self = this;

		this.currentImportStage = 'finalise-scratch-setup';

		jQuery( '.awb-setup-wizard-section[data-step="' + self.activeStep + '"] li[data-action="finalise-scratch-setup"]' ).addClass( 'awb-working' );

		return jQuery.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'awb_finalise_scratch_setup',
				site_title: self.$el.find( '#site_title' ).val(),
				site_tagline: self.$el.find( '#site_tagline' ).val(),
				awb_setup_nonce: jQuery( '#awb-setup-nonce' ).val()
			},
			dataType: 'json'
		} )
		.done( function() {

			jQuery( '.awb-setup-wizard-section[data-step="' + self.activeStep + '"] li[data-action="finalise-scratch-setup"]' ).removeClass( 'awb-working' ).addClass( 'checked' );

		} );
	},

	/**
	 * Init font family/variant select fields.
	 *
	 * @since 7.5
	 *
	 * @return {void}
	 */
	setMenuState: function( state ) {
		this.$steps.attr( 'data-state', state );
	},

	/**
	 * Change which step is active.
	 *
	 * @since 7.5
	 *
	 * @return {void}
	 */
	changeStep: function( step ) {
		var $step = this.$el.find( '.awb-setup-wizard-section[data-step="' + step + '"]' );

		if ( $step.length ) {

			$step.removeClass( 'hidden' );
			this.$el.find( '.awb-setup-wizard-section:not([data-step="' + step + '"])' ).addClass( 'hidden' );
			this.activeStep = parseInt( step );

			this.$steps.find( '.completed' ).removeClass( 'completed' );
			this.$steps.find( 'li:not([data-id="' + step + '"])' ).removeClass( 'active' );
			this.$steps.find( '[data-id="' + step + '"]' ).addClass( 'active' ).prevAll().addClass( 'completed' );
			this.scrollTo();

			if ( 4 === this.activeStep ) {
				this.initColorsTab();
			} else if ( 5 === this.activeStep ) {
				this.initFontCards();
			} else if ( 6 === this.activeStep ) {
				window.dispatchEvent( new Event( 'awb-studio-update-palettes' ) );
			} else if ( 7 === this.activeStep ) {
				this.modal.renderContents( 'header', 'minimal', '.awb-db-preview-grid' );
			} else if ( 8 === this.activeStep ) {
				this.updateLayouts();
				this.scratchDisplaySelectedPlugins();
			} else if ( 20 === this.activeStep ) {
				this.initPrebuilts();
			} else if ( 21 === this.activeStep ) {
				this.displayPrebuiltSelection();
			}
		}
	},

	/**
	 * Update the layouts that will be displayed.
	 *
	 * @since 7.7
	 *
	 * @return {void}
	 */
	updateLayouts: function() {
		var self = this;

		this.$el.find( '.awb-feature' ).each( function() {
			if ( jQuery( this ).is( ':checked' ) ) {
				self.$el.find( '.awb-wizard-option-group[data-layout="' + jQuery( this ).val()  + '"]' ).show();
			} else {
				self.$el.find( '.awb-wizard-option-group[data-layout="' + jQuery( this ).val()  + '"]' ).hide();
			}
		} );
	},

	/**
	 * Display plugins which will be activated.
	 *
	 * @since 7.7
	 *
	 * @return {void}
	 */
	scratchDisplaySelectedPlugins: function() {
		var htmlActive = '',
			htmlOther  = '';

		this.$el.find( '.awb-db-needed-plugins .awb-needed-plugin:checked' ).each( function() {
			var pluginCSSClasses = 'awb-setup-stage',
				plugin = jQuery( this ).val();

		if ( 'undefined' !== typeof awbPrebuilts.plugins[ plugin ] ) {

				if ( true === awbPrebuilts.plugins[ plugin ].installed ) {
					pluginCSSClasses += ' awb-plugin-installed';
				}

				if ( true === awbPrebuilts.plugins[ plugin ].active ) {
					pluginCSSClasses += ' awb-plugin-active checked';
				}

				if ( -1 !== pluginCSSClasses.indexOf( 'checked' ) ) {
					htmlActive += '<li class="' + pluginCSSClasses + '" data-plugin="' + plugin + '" data-plugin_name="' + awbPrebuilts.plugins[ plugin ].plugin_name + '"><span class="awb-setup-stage-check"><i></i><span class="avada-db-loader"></span></span><span class="awb-setup-stage-label">' + awbPrebuilts.plugins[ plugin ].plugin_name + '</span></li>';
				} else {
					htmlOther += '<li class="' + pluginCSSClasses + '" data-plugin="' + plugin + '" data-plugin_name="' + awbPrebuilts.plugins[ plugin ].plugin_name + '"><span class="awb-setup-stage-check"><i></i><span class="avada-db-loader"></span></span><span class="awb-setup-stage-label">' + awbPrebuilts.plugins[ plugin ].plugin_name + '</span></li>';
				}
			}
		} );

		// Remove possibly previously added plugins.
		this.$el.find( '.awb-setup-wizard-section[data-step="8"] .awb-setup-wizard-content .checklist li[data-plugin]' ).remove();

		if ( '' !== htmlActive || '' !== htmlOther ) {
			jQuery( '.awb-setup-wizard-section[data-step="8"] .awb-setup-wizard-content .checklist' ).prepend( htmlActive + htmlOther );
		}
	},


	/**
	 * Activate selected plugins.
	 *
	 * @since 7.7
	 *
	 * @return {void}
	 */
	scratchBuild: function() {
		var setupImportStages = [];

		jQuery.each( this.$el.find( '.awb-setup-wizard-section[data-step="8"] .awb-setup-wizard-content .checklist li[data-plugin]' ), function( index, element ) {

			// Plugin installed and active.
			if ( jQuery( element ).hasClass( 'awb-plugin-active' ) ) {
				return; // Continue.
			}

			// Plugin installed but not active.
			if ( jQuery( element ).hasClass( 'awb-plugin-installed' ) && ! jQuery( element ).hasClass( 'awb-plugin-active' ) ) {
				setupImportStages.push( { type: 'plugin-activate', plugin: jQuery( element ).data( 'plugin' ), plugin_name: jQuery( element ).data( 'plugin_name' ) } );

				return; // Continue.
			}

			// If we're here it means plugin is not installed.
			setupImportStages.push( { type: 'plugin-install', plugin: jQuery( element ).data( 'plugin' ), plugin_name: jQuery( element ).data( 'plugin_name' ) } );
		} );

		// Import globals.
		setupImportStages.push( { type: 'set-globals' } );

		// Import layouts.
		setupImportStages.push( { type: 'import-pages' } );

		// Import layouts.
		setupImportStages.push( { type: 'save-layouts' } );

		// Set site title and description, clear cache, flush permalinks.
		setupImportStages.push( { type: 'finalise-scratch-setup' } );

		this.processImportStages( setupImportStages );
	},

	/**
	 * Deep merge together objects.
	 *
	 * @since 7.5
	 *
	 * @return {object}
	 */
	deepAssign: function( target, ...sources ) {
		var self = this,
			source;

		for ( source of sources ) {
			for ( const k in source ) { // eslint-disable-line guard-for-in
				const vs = source[ k ],
					vt = target[ k ];
				if ( Object( vs ) == vs && Object( vt ) === vt ) {
					target[ k ] = self.deepAssign( vt, vs );
					continue; // eslint-disable-line no-continue
				}
				target[ k ] = source[ k ];
			}
		}
		return target;
	},

	webLoadFont: function( $card ) {
		var	self         = this,
			types        = [ 'headings', 'subheadings', 'lead', 'body', 'small' ],
			fontFamilies = {},
			familyArray  = [];

		if ( $card.hasClass( 'loaded' ) ) {
			return;
		}

		$card.addClass( 'loaded' );

		types.forEach( function( type ) {
			var family    = $card[ 0 ].style.getPropertyValue( '--' + type + '_font_family' ),
				weight    = $card[ 0 ].style.getPropertyValue( '--' + type + '_font_weight' ),
				fontStyle = $card[ 0 ].style.getPropertyValue( '--' + type + '_font_style' );

			if ( 'string' !== typeof family || '' === family || 'initial' === family ) {
				return;
			}

			if ( 'string' !== typeof fontStyle || '' === fontStyle ) {
				fontStyle = 'normal';
			}

			if ( 'italic' === fontStyle ) {
				weight += fontStyle;
			}
			if ( 'object' !== typeof fontFamilies[ family ] ) {
				fontFamilies[ family ] = [];
			}

			if ( ! fontFamilies[ family ].includes( weight ) ) {
				fontFamilies[ family ].push( weight );
			}
		} );

		// Check if any have already been loaded.
		_.each( fontFamilies, function( weights, family ) {
			var newWeights = [];
			if ( 'object' === typeof self.loadedFonts[ family ] ) {
				weights.forEach( function( weight ) {
					if ( ! self.loadedFonts[ family ].includes( weight ) ) {
						newWeights.push( weight );
					}
				} );

				if ( newWeights.length ) {
					fontFamilies[ family ] = newWeights;
				} else {
					delete fontFamilies[ family ];
				}
			}
		} );

		// No variants to load.
		if ( 0 === Object.keys( fontFamilies ).length ) {
			return;
		}

		// Process required fonts.
		_.each( fontFamilies, function( weights, family ) {
			familyArray.push( family + ':' + weights.join( ',' ) );
		} );

		// Load the required fonts.
		WebFont.load( {
			google: {
				families: familyArray
			}
		} );

		// Add into main object so they are not loaded again.
		self.loadedFonts = self.deepAssign( self.loadedFonts, fontFamilies );
	},

	/**
	 * Init font cards.
	 *
	 * @since 7.5
	 *
	 * @return {void}
	 */
	initFontCards: async function() {
		var self  = this,
			options    = {
				root: null,
				rootMargin: '0px',
				threshold: 0
			},
			typographies = await this.getTypographiesFromRemote(),
			typography,
			i,
			html,
			isFirstTypography = true,
			$typoListWrapper = jQuery( '.awb-setup-wizard-typography-list' ),
			typoTranslations = JSON.parse( $typoListWrapper.attr( 'data-item-translations' ) ),
			typoStyle = '',
			propName,
			typoStyleObj,
			cardObserver;

		// Create typo cards.
		for ( i = 0; i < typographies.length; i++ ) {
			typography = typographies[ i ];

			typoStyleObj = this.getTypoStyle( typographies[ i ] );
			typoStyle = '';
			for ( propName in typoStyleObj ) { //eslint-disable-line
				typoStyle += propName + ':' + typoStyleObj[ propName ] + ';';
			}

			html = jQuery( '<li data-id="' + typography.name + '" class="awb-setup-wizard-style-preview inherit-color typo-card' + ( isFirstTypography ? ' active' : '' ) + '" style="' + typoStyle + '"></li>' );
			html.append( jQuery( '<h2></h2>' ).text( typoTranslations.heading ) );
			html.append( jQuery( '<div class="subheading"></div>' ).text( typoTranslations.subheading ) );
			html.append( jQuery( '<p></p>' ).text( typoTranslations.text1 ) );
			html.append( jQuery( '<hr>' ) );
			html.append( jQuery( '<span></span>' ).text( typoTranslations.text2 ) );
			html.append( jQuery( '<a class="button"></a>' ).text( typoTranslations.button_text ) );

			isFirstTypography = false;
			$typoListWrapper.append( html );
		}

		if ( 'IntersectionObserver' in window ) {
			cardObserver = new IntersectionObserver( function( entries ) {
				jQuery.each( entries, function( key, entry ) {
					if ( entry.isIntersecting ) {
						self.webLoadFont( jQuery( entry.target ) );
						cardObserver.unobserve( entry.target );
					}
				} );
			}, options );

			this.$el.find( '.typo-card' ).each( function() {
				cardObserver.observe( this );
			} );

			this.$el.find( '.awb-setup-wizard-typography-list li' ).on( 'click', function() {
				jQuery( this ).addClass( 'active' ).siblings().removeClass( 'active' );

				self.setFontVars();
				self.scrollTo();

				self.$el.find( '#typo-selection' ).fadeOut( 300, function() {
					self.$el.find( '.awb-setup-wizard-typography-preview-wrapper' ).fadeIn( 300 );
				} );
			} );

			// Listen for typo back button click.
			this.$el.find( '.typography-options .awb-button[href="#selection"]' ).on( 'click', function( event ) {
				event.preventDefault();

				self.$el.find( '.awb-setup-wizard-typography-preview-wrapper' ).fadeOut( 300, function() {
					self.$el.find( '#typo-selection' ).fadeIn( 300 );
				} );
			} );

			// Next or previous typos.
			this.$el.find( '.typo-nav .awb-button' ).on( 'click', function( event ) {
				var $target = false,
					$preview = self.$el.find( '.awb-setup-wizard-style-preview-typography' );

				event.preventDefault();

				if ( '#next' === jQuery( this ).attr( 'href' ) ) {
					$target = self.$el.find( '.awb-setup-wizard-typography-list li.active' ).next();
				} else {
					$target = self.$el.find( '.awb-setup-wizard-typography-list li.active' ).prev();
				}

				if ( $target && $target.length ) {
					self.$el.find( '.awb-setup-wizard-typography-list li.active' ).removeClass( 'active' );
					$target.addClass( 'active' );

					$preview.css( 'opacity', 0 );
					setTimeout( function() {
						self.setFontVars();
					}, 250 );
					setTimeout( function() {
						$preview.css( 'opacity', 1 );
					}, 300 );

					// If not already, get necessary webfonts.
					self.webLoadFont( $target );
				}
			} );
		}
	},

	getTypoStyle: function( typographySet = [] ) {
		var variables = {},
			typoProperty,
			property;

		for ( property in typographySet ) {
			if ( ! typographySet[ property ] || 'object' !== typeof ( typographySet[ property ] ) || ! typographySet[ property ].font_family ) {
				continue; // eslint-disable-line no-continue
			}

			for ( typoProperty in typographySet[ property ] ) {
				if ( typographySet[ property ][ typoProperty ] ) {
					// filter font-size, because they have custom font size.
					if ( 'font_size' === typoProperty ) {
						continue; // eslint-disable-line no-continue
					}
					variables[ '--' + property + '_' + typoProperty ] = typographySet[ property ][ typoProperty ];
				}
			}
		}

		return variables;
	},

	setFontVars: async function() {
		var $target  = this.$el.find( '.awb-setup-wizard-typography-list li.active' ),
			$preview = this.$el.find( '.awb-setup-wizard-style-preview-typography' ),
			typographies = await this.getTypographiesFromRemote(),
			typoName,
			i;

		// Update active typo set and data.
		typoName = $target.attr( 'data-id' );
		for ( i = 0; i < typographies.length; i++ ) {
			if ( typographies[ i ].name && typographies[ i ].name === typoName ) {
				this.activeTypoSetData = typographies[ i ];
				break;
			}
		}

		$preview.css( this.getTypoStyle( this.activeTypoSetData ) );
	},

	updateFontSizePreview: function() {
		var baseFont = parseFloat( this.$el.find( '#pyre_base_size' ).val() ),
			sizing   = parseFloat( this.$el.find( '#pyre_sizing_type' ).val() ),
			data     = {};

		if ( ! baseFont ) {
			baseFont = 16;
		}

		data[ '--awb_base_size' ] = ( baseFont + 'px' );
		data[ '--awb_heading_ratio' ] = sizing;

		this.$el.find( '.awb-setup-wizard-style-preview-typography' ).css( data );
	},

	/**
	 * Init prebuilts.
	 *
	 * @since 7.5
	 *
	 * @return {void}
	 */
	initPrebuilts: function() {
		var options    = {
			root: null,
			rootMargin: '0px',
			threshold: 0
		},
		prebuiltObserver;

		// Clear any previously selected prebuilt.
		this.$el.find( '.fusion-admin-box.active' ).removeClass( 'active' );

		if ( 'IntersectionObserver' in window ) {
			prebuiltObserver = new IntersectionObserver( function( entries ) {
				jQuery.each( entries, function( key, entry ) {
					if ( entry.isIntersecting ) {
						const attr = 1 < window.devicePixelRatio ? 'data-src-retina' : 'data-src';
						entry.target.src = entry.target.getAttribute( attr );

						prebuiltObserver.unobserve( entry.target );
					}
				} );
			}, options );

			this.$el.find( '.theme-screenshot img' ).each( function() {
				prebuiltObserver.observe( this );
			} );
		}
	},

	/**
	 * Display selected prebuilt site and it's features.
	 *
	 * @since 7.5
	 *
	 * @return {void}
	 */
	displayPrebuiltSelection: function() {
		var prebuiltID       = this.$el.find( '.fusion-admin-box.active' ).data( 'demo-id' ),
			requiredPlugins  = '',
			prebuiltFeatures = '',
			firstContentItem = false,
			prebuiltLabel    = '',
			prebuiltClass    = '';

		// Use classic if user didn't make selection.
		if ( ! prebuiltID ) {
			prebuiltID = 'classic';
		}

		if ( 'undefined' !== typeof awbPrebuilts.websites[ prebuiltID ] ) {
			// Prebuilt thumb.
			jQuery( '#awb-selected-prebuilt-thumb' ).html( '<img src="' + awbPrebuilts.websites[ prebuiltID ].previewImage + '" />' );

			// Required plugins, fusion core and fusion builder are always required and (assumed) activate at this point.
			jQuery.each( awbPrebuilts.websites[ prebuiltID ].plugin_dependencies, function( plugin, required ) {
				var pluginCSSClasses = 'awb-setup-stage',
					pluginStatus     = '';
				if ( true === required && 'undefined' !== typeof awbPrebuilts.plugins[ plugin ] ) {

					if ( true === awbPrebuilts.plugins[ plugin ].installed ) {
						pluginStatus = 'installed';
					}

					if ( true === awbPrebuilts.plugins[ plugin ].active ) {
						pluginStatus      = 'active';
						pluginCSSClasses += ' checked';
					}

					requiredPlugins += '<li class="' + pluginCSSClasses + '" data-plugin-status="' + pluginStatus + '" data-plugin="' + plugin + '" data-plugin_name="' + awbPrebuilts.plugins[ plugin ].plugin_name + '"><span class="awb-setup-stage-check"><i></i><span class="avada-db-loader"></span></span><span class="awb-setup-stage-label">' + awbPrebuilts.plugins[ plugin ].plugin_name + '</span></li>';
				}
			} );

			// Prebuilt features, feature either has feature_dependency or plugin_dependency (not both).
			jQuery.each( awbPrebuilts.import_stages, function( index, feature ) {
				var dataType = '';

				if ( 'undefined' !== typeof feature.plugin_dependency && ( 'undefined' === typeof awbPrebuilts.websites[ prebuiltID ].plugin_dependencies[ feature.plugin_dependency ] || false === awbPrebuilts.websites[ prebuiltID ].plugin_dependencies[ feature.plugin_dependency ] ) ) {
					return; // Continue.
				}

				if ( 'undefined' !== typeof feature.feature_dependency && -1 === awbPrebuilts.websites[ prebuiltID ].features.indexOf( feature.feature_dependency ) ) {
					return; // Continue.
				}

				if ( 'undefined' !== typeof feature.data ) {
					dataType = ' data-type="' + feature.data + '"';
				}

				prebuiltLabel = feature.label;
				prebuiltClass = 'awb-setup-stage';

				// We want only first content item to be visible and to change it's label.
				if ( 'content' === feature.data && false === firstContentItem ) {
					prebuiltLabel    = fusionBuilderText.content;
					prebuiltClass   += ' awb-visible';
					firstContentItem = true;
				}

				prebuiltFeatures += '<li class="' + prebuiltClass + '" data-value="' + feature.value + '"' + dataType + '><span class="awb-setup-stage-check"><i></i><span class="avada-db-loader"></span></span><span class="awb-setup-stage-label">' + prebuiltLabel + '</span></li>';
			} );

			jQuery( '#awb-selected-prebuilt-features-list' ).html( requiredPlugins + prebuiltFeatures );
		}
	},

	/**
	 * Prepares data for prebuilt import.
	 *
	 * @since 7.7
	 *
	 * @return {void}
	 */
	prepareSetupImport: function() {
		var importStagesCount,
			i,
			setupImportStages = [],
			prebuiltData = {
				action: 'fusion_import_demo_data',
				security: awbPrebuilts.nonce_import_prebuilt,
				demoType: this.$el.find( '.fusion-admin-box.active' ).data( 'demo-id' ),
				importStages: [ 'download' ],
				contentTypes: [],
				fetchAttachments: true,
				allImport: true,
				siteTitle: this.$el.find( '#site_title' ).val()
			};

		// Plugins, then prebuilt import.
		jQuery( '#awb-selected-prebuilt-features-list li' ).each( function() {

			// Plugins.
			if ( 'undefined' !== typeof jQuery( this ).data( 'plugin' ) ) {
				// Plugin installed and active.
				if ( 'active' === jQuery( this ).data( 'plugin-status' ) ) {
					return; // Continue.
				}

				// Plugin installed but not active.
				if ( 'installed' === jQuery( this ).data( 'plugin-status' ) ) {
					setupImportStages.push( { type: 'plugin-activate', plugin: jQuery( this ).data( 'plugin' ), plugin_name: jQuery( this ).data( 'plugin_name' ) } );

					return; // Continue.
				}

				// If we're here it means plugin is not installed.
				setupImportStages.push( { type: 'plugin-install', plugin: jQuery( this ).data( 'plugin' ), plugin_name: jQuery( this ).data( 'plugin_name' ) } );

				return; // Continue.
			}

			// If we're here it means we're iterating through prebuilt part.

			if ( 'content' === this.getAttribute( 'data-type' ) ) {
				prebuiltData.contentTypes.push( this.getAttribute( 'data-value' ) );

				if ( -1 === prebuiltData.importStages.indexOf( 'content' ) ) {
					prebuiltData.importStages.push( 'content' );
				}
			} else {
				prebuiltData.importStages.push( this.getAttribute( 'data-value' ) );
			}

		} );

		prebuiltData.importStages.push( 'general_data' );

		importStagesCount = prebuiltData.importStages.length;

		for ( i = 0; i < importStagesCount; i++ ) {
			setupImportStages.push( jQuery.extend( true, {}, { type: 'prebuilt-import', prebuiltData: prebuiltData } ) );

			prebuiltData.importStages.shift();
		}

		this.processImportStages( setupImportStages );
	},

	/**
	 * Goes through import stages and processes them.
	 * Import stage can be pluging install, plugin activation or importing prebuilt website.
	 *
	 * @since 7.7
	 *
	 * @return {void}
	 */
	processImportStages: function( setupImportStages ) {
		var self         = this,
			promises     = [],
			i,
			dfd          = jQuery.Deferred(),  // Master deferred.
			dfdNext      = dfd; // Next deferred in the chain,

		dfd.resolve();

		// Install / Activate plugins and import prebuilt.
		if ( 0 < setupImportStages.length ) {

			for ( i = 0; i < setupImportStages.length; i++ ) {

				// IIFE to freeze the value of i.
				( function( k ) { // eslint-disable-line no-loop-func

					dfdNext = dfdNext.then( function() {
						if ( 'plugin-activate' === setupImportStages[ k ].type ) {
							return self.activatePlugin( setupImportStages[ k ] );
						} else if ( 'plugin-install' === setupImportStages[ k ].type  ) {
							return self.installPlugin( setupImportStages[ k ] );
						} else if ( 'prebuilt-import' === setupImportStages[ k ].type  ) {
							return self.importPrebuiltSite( setupImportStages[ k ].prebuiltData );
						} else if ( 'set-globals' === setupImportStages[ k ].type  ) {
							return self.setGlobalOptions();
						} else if ( 'import-pages' === setupImportStages[ k ].type  ) {
							return self.importPages();
						} else if ( 'save-layouts' === setupImportStages[ k ].type  ) {
							return self.saveLayouts();
						} else if ( 'finalise-scratch-setup' === setupImportStages[ k ].type  ) {
							return self.finaliseScratchSetup();
						}
					} );

					promises.push( dfdNext );
				}( i ) );
			}

			jQuery.when.apply( null, promises ).then(
				function() {

					// Success.
					window.dispatchEvent( new Event( 'awb-setup-success' ) );
				},
				function() {

					// Fail.
					window.dispatchEvent( new Event( 'awb-setup-error' ) );
				}
			);
		} else {
			// Dispatch success event if we have nothing to import, to indicate we're done.
			window.dispatchEvent( new Event( 'awb-setup-success' ) );
		}

	},

	/**
	 * Imports prebuilt website.
	 *
	 * @since 7.7
	 *
	 * @return {void}
	 */
	importPrebuiltSite: function( data ) {
		var dataType = 'content' === data.importStages[ 0 ] ? 'type' : 'value';

		this.currentImportStage = 'prebuilt-import';

		jQuery( '#awb-selected-prebuilt-features-list li[data-' + dataType + '="' + data.importStages[ 0 ] + '"]:first' ).addClass( 'awb-working' );

		return jQuery.ajax( {
			method: 'POST',
			url: ajaxurl,
			data: data
		} ).then(
			function( response ) {
				var completedImportStage = '';

				if ( ( 0 < response.indexOf( 'partially completed' ) || 0 <= response.indexOf( '{"status"' ) ) && 0 < data.importStages.length ) {

					completedImportStage = response.substring( 'import partially completed: '.length );

					// Mark imported stage.
					if ( 'download' !== completedImportStage ) {
						if ( 'content' === completedImportStage ) {
							jQuery( '#awb-selected-prebuilt-features-list li[data-type="content"]:first' ).removeClass( 'awb-working' ).addClass( 'checked' );
						} else {
							jQuery( '#awb-selected-prebuilt-features-list li[data-value="' + completedImportStage + '"]' ).removeClass( 'awb-working' ).addClass( 'checked' );
						}
					}

					return new jQuery.Deferred().resolve().promise();
				} else if ( -1 === response && response.indexOf( 'imported' ) ) { // eslint-disable-line no-empty
					return new jQuery.Deferred().reject().promise();
				} else if ( 1 < response.indexOf( avadaAdminL10nStrings.file_does_not_exist ) ) { // eslint-disable-line no-empty
					return new jQuery.Deferred().reject().promise();
				}

				return new jQuery.Deferred().resolve().promise();
			},
			function() {
				// Need to return rejected promise, to stop subsequent import steps.
				return new jQuery.Deferred().reject().promise();
			}
		);
	},

	setupSuccess: function() {

		// Remove working class if it was added.
		if ( 0 < awbSetupWizard.$el.find( '.awb-setup-build.awb-build-working' ).length ) {
			awbSetupWizard.$el.find( '.awb-setup-build.awb-build-working' ).removeClass( 'awb-build-working' );
		}

		// Reload page after successful registration.
		if ( jQuery( 'body' ).hasClass( 'avada_page_avada-setup' ) && jQuery( '.intro-start > .awb-wizard-link:first-child' ).hasClass( 'active' ) && jQuery( '#avada-db-registration' ).find( '.avada-db-reg-heading i' ).hasClass( 'fusiona-verified' ) ) {
			window.location.reload( true );
		}

		// Prebuilt path: open Congrats step.
		if ( jQuery( 'body' ).hasClass( 'avada_page_avada-setup' ) && jQuery( '.prebuilt-confirm-step' ).is( ':visible' ) ) {
			awbSetupWizard.changeStep( 9 );
		}

		// Scratch path:  open Congrats step.
		if ( jQuery( 'body' ).hasClass( 'avada_page_avada-setup' ) && jQuery( '.scratch-confirm-step' ).is( ':visible' ) ) {
			awbSetupWizard.changeStep( 9 );
		}
	},

	setupError: function() {
		var error = {
			title: fusionBuilderText.setup_general_error_title,
			content: fusionBuilderText.setup_general_error_message
		};

		if ( 'plugin-activate' === window.awbSetupWizard.currentImportStage || 'plugin-install' === window.awbSetupWizard.currentImportStage ) {
			error.title = fusionBuilderText.setup_plugin_error_title;
		}

		if ( 'prebuilt-import' === window.awbSetupWizard.currentImportStage ) {
			error.title = fusionBuilderText.setup_prebuilt_error_title;
		}

		window.awbSetupWizard.errorDialog( error );
	},

	errorDialog: function( error ) {
		var contentErrorMarkup = jQuery( '<div>' +  error.content + '</div>' );

		contentErrorMarkup.dialog( {
			title: error.title,
			dialogClass: 'fusion-builder-dialog fusion-builder-error-dialog fusion-builder-settings-dialog',
			autoOpen: true,
			modal: true,
			width: 400,
			minHeight: 50,
			open: function() {}, // eslint-disable-line no-empty-function
			create: function() {
				// Add Title.
				jQuery( this ).siblings().find( 'span.ui-dialog-title' ).prepend( '<span class="icon type-warning"><i class="fusiona-exclamation-sign" aria-hidden="true"></i></span>' );

				// Add Close button.
				jQuery( this ).siblings( '.ui-dialog-titlebar' ).append( '<a href="#" class="awb-setup-dialog-close"><span class="dashicons dashicons-no-alt"></span></a>' );
			},
			close: function() {} // eslint-disable-line no-empty-function
		} );

	},

	/**
	 * Activates required plugin.
	 *
	 * @since 7.7
	 *
	 * @return {void}
	 */
	activatePlugin: function( pluginData ) {
		var data = {
				action: 'fusion_activate_plugin',
				avada_activate: 'activate-plugin',
				plugin: pluginData.plugin,
				plugin_name: pluginData.plugin_name,
				avada_activate_nonce: awbPrebuilts.nonce_activate_plugin
			},
			self = this;

		self.currentImportStage = 'plugin-activate';

		jQuery( '.awb-setup-wizard-section[data-step="' + self.activeStep + '"] li[data-plugin="' + data.plugin + '"]' ).addClass( 'awb-working' );

		return jQuery.get( ajaxurl, data, function( response ) {

			if ( true !== response.error ) {
				jQuery( '.awb-setup-wizard-section[data-step="' + self.activeStep + '"] li[data-plugin="' + data.plugin + '"]' ).removeClass( 'awb-working' ).addClass( 'checked' ).attr( 'data-plugin-status', 'active' );
			} else {
				// Plugin activation failed.
			}
		}, 'json' );
	},

	/**
	 * Installs required plugin.
	 *
	 * @since 7.7
	 *
	 * @return {void}
	 */
	installPlugin: function( pluginData ) {
		var data = {
				action: 'fusion_install_plugin',
				avada_activate: 'activate-plugin',
				plugin: pluginData.plugin,
				plugin_name: pluginData.plugin_name,
				avada_activate_nonce: awbPrebuilts.nonce_activate_plugin,
				page: 'install-required-plugins'
			},
			self = this;

		self.currentImportStage = 'plugin-install';

		jQuery( '.awb-setup-wizard-section[data-step="' + self.activeStep + '"] li[data-plugin="' + data.plugin + '"]' ).addClass( 'awb-working' );

		// 'page' arg needed so 'avada_get_required_and_recommened_plugins' sets proper plugin URL.

		data[ 'tgmpa-install' ] = 'install-plugin';
		data[ 'tgmpa-nonce' ]   = awbPrebuilts.nonce_install_plugin;

		return jQuery.get( ajaxurl, data, function( response ) {

			if ( true !== response.error ) {
				jQuery( '.awb-setup-wizard-section[data-step="' + self.activeStep + '"] li[data-plugin="' + data.plugin + '"]' ).removeClass( 'awb-working' ).addClass( 'checked' ).attr( 'data-plugin-status', 'active' );

			} else {
				// Plugin install failed.
			}
		}, 'html' );
	},

	/**
	 * If needed installs and activates Avada Core and Avada Builder plugins.
	 *
	 * @since 7.7
	 *
	 * @return {void}
	 */
	activateAvadaPlugins: function() {
		var requiredPlugins = [],
			pluginAction    = '';

		// Check FC status.
		if ( true !== awbPrebuilts.plugins[ 'fusion-core' ].active ) {
			pluginAction = 'plugin-activate';

			if ( true !== awbPrebuilts.plugins[ 'fusion-core' ].installed ) {
				pluginAction = 'plugin-install';
			}

			requiredPlugins.push( {
				plugin: awbPrebuilts.plugins[ 'fusion-core' ].slug,
				plugin_name: awbPrebuilts.plugins[ 'fusion-core' ].plugin_name,
				type: pluginAction
			} );
		}

		// Check FB status.
		if ( true !== awbPrebuilts.plugins[ 'fusion-builder' ].active ) {
			pluginAction = 'plugin-activate';

			if ( true !== awbPrebuilts.plugins[ 'fusion-builder' ].installed ) {
				pluginAction = 'plugin-install';
			}

			requiredPlugins.push( {
				plugin: awbPrebuilts.plugins[ 'fusion-builder' ].slug,
				plugin_name: awbPrebuilts.plugins[ 'fusion-builder' ].plugin_name,
				type: pluginAction
			} );
		}

		this.processImportStages( requiredPlugins );
	},

	/**
	 * Scroll to top when step changed.
	 *
	 * @since 7.5
	 *
	 * @return {void}
	 */
	scrollTo: function() {
		jQuery( 'html, body' ).animate( {
			scrollTop: 0
		}, 300 );
	},

	initColorsTab: async function() {
		var colorSchemes = await this.getColorSchemasFromRemote(),
			$colorConfirm = jQuery( '.color-confirm' ),
			$colorSchemesWrapper = jQuery( '#awb-setup-wizard-color-schemes-wrapper' ),
			$colorCategoriesWrapper = jQuery( '#awb-setup-wizard-color-categories-wrapper' ),
			$colorSelectButtons = jQuery( '.awb-setup-wizard-color-buttons' ),
			$section = jQuery( '.awb-setup-wizard-section[data-step="4"] .awb-setup-wizard-content' ),
			html = '',
			counts = {},
			categoryName,
			i;

		this.initColorPickers();

		if ( ! colorSchemes.length ) {
			$colorSchemesWrapper.html( '<p>' + awbSetup.colors404Message + '</p>' );
			return;
		}

		for ( i = 0; i < colorSchemes.length; i++ ) {
			html += getSchemaHtmlAndCountCategories( colorSchemes[ i ] );
		}

		$colorSchemesWrapper.html( html );

		if ( $colorConfirm.is( ':visible' ) ) {
			$colorSelectButtons.addClass( 'hidden' );
			$section.addClass( 'awb-setup-wizard-content-secondary' );
		} else {
			$colorSelectButtons.removeClass( 'hidden' );
			$section.removeClass( 'awb-setup-wizard-content-secondary' );
		}

		$colorCategoriesWrapper.html( getCategoriesHtml() );

		// Set the default colors if is the first time this tab is initialized.
		if ( ! this.colors.color1 ) {
			this.setColorVars();
		}

		function getSchemaHtmlAndCountCategories( schema ) {
			var schemaHtml = '',
				additionalClass = ( 'classic' === schema.name ? ' class="active"' : '' ),
				j;

			for ( j = 0; j < schema.color_categories.length; j++ ) {
				categoryName = schema.color_categories[ j ];
				if ( 'undefined' !== typeof counts[ categoryName ] ) {
					counts[ categoryName ]++;
				} else {
					counts[ categoryName ] = 1;
				}
			}

			schemaHtml += '<li data-id="' + schema.name + '" data-categories="' + schema.color_categories.join( ' ' ) + '"' + additionalClass + '>';

			schemaHtml += '<div class="color-swatch">';
			for ( j = 1; 8 >= j; j++ ) {
				schemaHtml += '<span class="color-swatch-' + j + '" data-value="' + schema[ 'color_' + j ] + '" style="background-color:' + schema[ 'color_' + j ] + ';"></span>';
			}
			schemaHtml += '</div>';
			schemaHtml += '</li>';

			return schemaHtml;
		}

		function getCategoriesHtml() {
			var colors = $colorCategoriesWrapper.attr( 'data-awb-color-names' );
			var categoriesHtml = '';
			var colorSlug;
			colors = JSON.parse( colors );

			for ( colorSlug in colors ) {
				if ( 'undefined' !== typeof counts[ colorSlug ] && 0 < counts[ colorSlug ] ) {
					categoriesHtml += '<li data-id="' + colorSlug + '" aria-label="' + colors[ colorSlug ] + '"><span style="background-color:' + colorSlug + ';"></span></li>';
				}
			}

			return categoriesHtml;
		}
	},

	initColorPickers: function() {
		var $pickers = jQuery( '.awb-setup-wizard-custom-colors-wrapper .awb-picker' ),
			self     = this;

		$pickers.awbColorPicker( {
			globals: false,
			hide: true,
			allowToggle: false,
			change: _.debounce( handleColorPickerChange, 300 )
		} );

		$pickers.awbColorPicker( 'open' );

		function handleColorPickerChange() {
			var $el = jQuery( this.element );
			var colorId = $el.closest( '.awb-setup-wizard-change-color-setting' ).attr( 'data-color-id' );
			var toSetColors = self.setCustomColorVal.bind( self );
			var refreshAcc  = self.refreshColorAccessibility.bind( self );


			toSetColors( colorId, { color: this.color() } );
			refreshAcc();
		}
	},

	toggleCustomPalette: function() {
		var $colorSection = jQuery( '.awb-setup-wizard-section[data-step="4"]' );

		if ( $colorSection.hasClass( 'awb-setup-wizard-custom-palette-enabled' ) ) {
			this.disableCustomPalette();
		} else {
			this.enableCustomPalette();
		}
	},

	enableCustomPalette: function() {
		var $colorSection      = jQuery( '.awb-setup-wizard-section[data-step="4"]' ),
			$colorSettings     = jQuery( '.awb-setup-wizard-change-color-setting' ),
			$customizeBtn      = jQuery( '.awb-setup-wizard-toggle-custom-palette' ),
			$schemeNav         = jQuery( '.scheme-nav' ),
			$customPalette     = jQuery( '.awb-setup-wizard-custom-colors-wrapper' ),
			$allTogglers       = jQuery( '.awb-setup-wizard-change-color-toggler' ),
			$firstColorToggler = jQuery( '.awb-setup-wizard-change-color-toggler[data-color-id="1"]' );


		$customizeBtn.addClass( 'active' );
		$colorSection.addClass( 'awb-setup-wizard-custom-palette-enabled' );
		$customPalette.slideDown( {
			duration: 600,
			start: function () {
				jQuery( this ).css( {
					display: 'flex'
				} );
			}
		}, 600 );

		$schemeNav.css( 'opacity', 0 ).css( 'visibility', 'hidden' ).attr( 'aria-hidden', 'true' );

		$colorSettings.each( function() {
			var $section = jQuery( this ),
				colorId = jQuery( this ).attr( 'data-color-id' ),
				currentColor = jQuery( '.awb-setup-wizard-change-color-toggler[data-color-id="' + colorId + '"] .awb-setup-wizard-change-color-preview' ).css( 'background-color' ),
				colorPicker = $section.find( 'input.awb-picker' );

			if ( 'string' === typeof currentColor && currentColor && 'transparent' !== currentColor ) {
				colorPicker.awbColorPicker( 'color', currentColor );
			}
		} );

		// Open first color if necessary.
		if ( ! $allTogglers.hasClass( 'active' ) ) {
			$firstColorToggler.trigger( 'click' );
		}

		this.refreshColorAccessibility();
	},

	disableCustomPalette: function() {
		var $colorSection  = jQuery( '.awb-setup-wizard-section[data-step="4"]' ),
			$customizeBtn  = jQuery( '.awb-setup-wizard-toggle-custom-palette' ),
			$customPalette = jQuery( '.awb-setup-wizard-custom-colors-wrapper' ),
			$schemeNav     = jQuery( '.scheme-nav' );

		$customizeBtn.removeClass( 'active' );
		$colorSection.removeClass( 'awb-setup-wizard-custom-palette-enabled' );
		$customPalette.slideUp( 600 );
		$schemeNav.css( 'opacity', 1 ).css( 'visibility', '' ).attr( 'aria-hidden', 'false' );
	},

	toggleCustomColor: function( e ) {
		var $el = jQuery( e.currentTarget );
		var colorId = $el.attr( 'data-color-id' );
		var allSettingElements = jQuery( '.awb-setup-wizard-change-color-setting' );
		var allSettingTogglers = jQuery( '.awb-setup-wizard-change-color-toggler' );
		var $targetEl = jQuery( '.awb-setup-wizard-change-color-setting[data-color-id="' + colorId + '"]' );
		var needToWait = false;

		// Hide any setting that is visible, and set to wait for it to become hidden.
		allSettingTogglers.removeClass( 'active' );
		allSettingElements.each( function() {
			var settingEl = jQuery( this );
			if ( settingEl.attr( 'data-color-id' ) !== colorId && settingEl.is( ':visible' ) ) {
				settingEl.slideUp( 300 );
				needToWait = true;
			}
		} );

		// Toggle the hidden/show actions.
		if ( $targetEl.is( ':visible' ) ) {
			$el.removeClass( 'active' );
		} else {
			$el.addClass( 'active' );
		}

		// Toggle the hidden/show actions that needs waiting if necessary.
		if ( needToWait ) {
			setTimeout( toggleColorView.bind( null, $targetEl ), 250 );
		} else {
			toggleColorView( $targetEl );
		}

		function toggleColorView( settingToToggle ) {
			if ( settingToToggle.is( ':visible' ) ) {
				settingToToggle.slideUp( 300 );
			} else {
				settingToToggle.slideDown( 300 );
			}
		}
	},

	handleColorNameChange: function( e ) {
		var $el = jQuery( e.currentTarget );
		var colorName = $el.val();
		var colorId = $el.closest( '.awb-setup-wizard-change-color-setting' ).attr( 'data-color-id' );
		var $togglerName = jQuery( '.awb-setup-wizard-change-color-toggler[data-color-id="' + colorId + '"] .awb-setup-wizard-color-name' );
		var $accessibilityNames = jQuery( '.awb-setup-wizard-accessibility [data-color-id="' + colorId + '"]' );

		$togglerName.text( colorName );
		$accessibilityNames.text( colorName );

		this.setCustomColorVal( colorId, { label: colorName } );
	},

	getColorSchemasFromRemote: async function() {
		var urlData;

		if ( Array.isArray( this._colorsCache ) && this._colorsCache.length ) {
			return this._colorsCache;
		}

		urlData = await fetch( 'https://updates.theme-fusion.com/wp-json/setup_data/colors/', { cache: 'no-store' } ).then( function( response ) {
			if ( response.ok ) {
				return response.json();
			}
			return [];
		} ).catch( function() { // eslint-disable-line dot-notation
			return [];
		} );

		if ( Array.isArray( urlData ) ) {
			this._colorsCache = urlData;
		}

		this.schemesLoaded = true;
		return this._colorsCache;
	},

	getTypographiesFromRemote: async function() {
		var urlData;

		if ( Array.isArray( this._typographyCache ) && this._typographyCache.length ) {
			return this._typographyCache;
		}

		urlData = await fetch( 'https://updates.theme-fusion.com/wp-json/setup_data/typography/', { cache: 'no-store' } ).then( function( response ) {
			if ( response.ok ) {
				return response.json();
			}
			return [];
		} ).catch( function() { // eslint-disable-line dot-notation
			return [];
		} );

		if ( Array.isArray( urlData ) ) {
			this._typographyCache = urlData;
		}

		this.typographyLoaded = true;

		// Set the default typoSet.
		if ( ! this.activeTypoSetData.name && this._typographyCache[ 0 ] ) {
			this.activeTypoSetData = this._typographyCache[ 0 ];
		}

		return this._typographyCache;
	},

	refreshColorAccessibility: function() {
		var color15Wrapper = jQuery( '.awb-setup-wizard-accessibility-item[data-awb-check="color-1-5-contrast"]' ),
			color_1_5_contrast = jQuery.Color( this.colors.color1.color ).contrast( this.colors.color5.color ),
			color48Wrapper = jQuery( '.awb-setup-wizard-accessibility-item[data-awb-check="color-4-8-contrast"]' ),
			color_4_8_contrast = jQuery.Color( this.colors.color4.color ).contrast( this.colors.color8.color ),
			$colorsLuminance = jQuery( '.awb-setup-wizard-accessibility-item[data-awb-check="color-luminance-order"]' ),
			luminances,
			firstColorWrong,
			orderIsGood,
			i;

		color15Wrapper.find( '.awb-setup-wizard-accessibility-item-badge span' ).removeClass( 'active' );
		if ( 4.5 < color_1_5_contrast ) {
			color15Wrapper.find( '.awb-setup-wizard-accessibility-item-content' ).slideUp( 300 );
			color15Wrapper.find( '[data-awb-badge="excellent"]' ).addClass( 'active' );
		} else if ( 3 < color_1_5_contrast ) {
			color15Wrapper.find( '.awb-setup-wizard-accessibility-item-content' ).filter( ':not(.awb-setup-wizard-accessibility-acceptable)' ).slideUp( 300 );
			color15Wrapper.find( '.awb-setup-wizard-accessibility-acceptable' ).filter( ':not(:visible)' ).slideDown( 300 );
			color15Wrapper.find( '[data-awb-badge="acceptable"]' ).addClass( 'active' );
		} else if ( 2.5 < color_1_5_contrast ) {
			color15Wrapper.find( '.awb-setup-wizard-accessibility-item-content' ).filter( ':not(.awb-setup-wizard-accessibility-poor)' ).slideUp( 300 );
			color15Wrapper.find( '.awb-setup-wizard-accessibility-poor' ).filter( ':not(:visible)' ).slideDown( 300 );
			color15Wrapper.find( '[data-awb-badge="poor"]' ).addClass( 'active' );
		} else {
			color15Wrapper.find( '.awb-setup-wizard-accessibility-item-content' ).filter( ':not(.awb-setup-wizard-accessibility-very-poor)' ).slideUp( 300 );
			color15Wrapper.find( '.awb-setup-wizard-accessibility-very-poor' ).filter( ':not(:visible)' ).slideDown( 300 );
			color15Wrapper.find( '[data-awb-badge="very-poor"]' ).addClass( 'active' );
		}
		color15Wrapper.find( '.awb-setup-wizard-accessibility-contrast' ).html( '(' + color_1_5_contrast.toFixed( 1 ) + ')' );

		// Check color 4-8 contrast.
		color48Wrapper.find( '.awb-setup-wizard-accessibility-item-badge span' ).removeClass( 'active' );
		if ( 4.5 < color_4_8_contrast ) {
			color48Wrapper.find( '.awb-setup-wizard-accessibility-item-content' ).slideUp( 300 );
			color48Wrapper.find( '[data-awb-badge="excellent"]' ).addClass( 'active' );
		} else if ( 3 < color_4_8_contrast ) {
			color48Wrapper.find( '.awb-setup-wizard-accessibility-item-content' ).filter( ':not(.awb-setup-wizard-accessibility-acceptable)' ).slideUp( 300 );
			color48Wrapper.find( '.awb-setup-wizard-accessibility-acceptable' ).filter( ':not(:visible)' ).slideDown( 300 );
			color48Wrapper.find( '[data-awb-badge="acceptable"]' ).addClass( 'active' );
		} else if ( 2.5 < color_4_8_contrast ) {
			color48Wrapper.find( '.awb-setup-wizard-accessibility-item-content' ).filter( ':not(.awb-setup-wizard-accessibility-poor)' ).slideUp( 300 );
			color48Wrapper.find( '.awb-setup-wizard-accessibility-poor' ).filter( ':not(:visible)' ).slideDown( 300 );
			color48Wrapper.find( '[data-awb-badge="poor"]' ).addClass( 'active' );
		} else {
			color48Wrapper.find( '.awb-setup-wizard-accessibility-item-content' ).filter( ':not(.awb-setup-wizard-accessibility-very-poor)' ).slideUp( 300 );
			color48Wrapper.find( '.awb-setup-wizard-accessibility-very-poor' ).filter( ':not(:visible)' ).slideDown( 300 );
			color48Wrapper.find( '[data-awb-badge="very-poor"]' ).addClass( 'active' );
		}
		color48Wrapper.find( '.awb-setup-wizard-accessibility-contrast' ).html( '(' + color_4_8_contrast.toFixed( 1 ) + ')' );

		// Check color luminance order.
		luminances = [];
		orderIsGood = true;
		firstColorWrong = 0;
		for ( i = 1; 8 >= i; i++ ) {
			luminances[ i ] = jQuery.Color( this.colors[ 'color' + i ].color ).calcLuminance();

			if ( 1 < i && luminances[ i ] > luminances[ i - 1 ] && orderIsGood ) {
				orderIsGood = false;
				firstColorWrong = i;
			}
		}

		$colorsLuminance.find( '.awb-setup-wizard-accessibility-item-badge span' ).removeClass( 'active' );
		if ( orderIsGood ) {
			$colorsLuminance.find( '.awb-setup-wizard-accessibility-item-content' ).slideUp( 300 );
			$colorsLuminance.find( '[data-awb-badge="excellent"]' ).addClass( 'active' );
		} else {
			$colorsLuminance.find( '.awb-setup-wizard-accessibility-item-content' ).filter( ':not(.awb-setup-wizard-accessibility-very-poor)' ).slideUp( 300 );
			$colorsLuminance.find( '.awb-setup-wizard-accessibility-very-poor' ).filter( ':not(:visible)' ).slideDown( 300 );
			$colorsLuminance.find( '.awb-setup-wizard-accessibility-very-poor [data-color-id]' ).eq( 0 ).text( this.colors[ 'color' + ( firstColorWrong - 1 ) ].label ).attr( 'data-color-id', firstColorWrong - 1 );
			$colorsLuminance.find( '.awb-setup-wizard-accessibility-very-poor [data-color-id]' ).eq( 1 ).text( this.colors[ 'color' + firstColorWrong ].label ).attr( 'data-color-id', firstColorWrong );
			$colorsLuminance.find( '[data-awb-badge="very-poor"]' ).addClass( 'active' );
		}

	},

	/**
   * Gets setup wizard color palette.
   *
   * @since 3.7
   * @return {object}
   */
	getColorPallete: function() {
			var palette = {},
				i,
				colorObject;

			for ( i = 0; i <= Object.keys( this.colors ).length; i++ ) {
				if ( 'undefined' !== typeof this.colors[ 'color' + i ] ) {

					// Get color object;
					colorObject = jQuery.Color( this.colors[ 'color' + i ].color );

					palette[ '--awb-color' + i ]       = this.colors[ 'color' + i ].color;
					palette[ '--awb-color' + i + '-h' ] = colorObject.hue();
					palette[ '--awb-color' + i + '-s' ] = ( colorObject.saturation() * 100 ) + '%';
					palette[ '--awb-color' + i + '-l' ] = ( colorObject.lightness() * 100 ) + '%';
					palette[ '--awb-color' + i + '-a' ] = ( colorObject.alpha() * 100 ) + '%';
				}
			}

			return palette;
	},

	/**
   * Inverts color palette.
   *
   * @since 3.7
   * @param {Object} palette - The color palette to invert.
   * @return {Boolean}
   */
	getInvertedColorPalette: function( palette ) {
		var reversedPalette = {},
			i,
			revI;

		for ( i = 1, revI = 8; 8 >= i; i++, revI-- ) {
			if ( 'undefined' !== typeof palette[ '--awb-color' + revI ] ) {
				reversedPalette[ '--awb-color' + i ]        = palette[ '--awb-color' + revI ];
				reversedPalette[ '--awb-color' + i + '-h' ] = palette[ '--awb-color' + revI + '-h' ];
				reversedPalette[ '--awb-color' + i + '-s' ] = palette[ '--awb-color' + revI + '-s' ];
				reversedPalette[ '--awb-color' + i + '-l' ] = palette[ '--awb-color' + revI + '-l' ];
				reversedPalette[ '--awb-color' + i + '-a' ] = palette[ '--awb-color' + revI + '-a' ];
			}
		}

		return reversedPalette;
	},

	/**
   * Gets local typo sets.
   *
   * @since 3.7
   * @return {object}
   */
	getTypoSets: function() {
		return this.getSetupWizardTypography();
	},

	/**
   * Gets setup wizard typo sets.
   *
   * @since 3.7
   * @return {object}
   */
	getSetupWizardTypography: function() {
		var ratio    = jQuery( '#pyre_sizing_type' ).val(),
			baseSize = parseFloat( jQuery( 'input[name="base_size"]' ).val() ),
			typoSet  = {},
			self	 = this,
			i,
			subsets  = [
				'font-family',
				'font-size',
				'font-weight',
				'font-style',
				'line-height',
				'letter-spacing',
				'text-transform'
			],
			setData  = {
				'small': '--awb-typography5',
				'body': '--awb-typography4',
				'lead': '--awb-typography3',
				'subheadings': '--awb-typography2',
				'headings': '--awb-typography1'
			};

		// default values.
		if ( 'undefined' === typeof ratio || '' === ratio ) {
			ratio = 1.33;
		}

		if ( 'undefined' === typeof baseSize || '' === baseSize ) {
			baseSize = 16;
		}

		jQuery.each( setData, function( name, cssSlug ) {
			subsets.forEach( function( subsetName ) {
				var typoProperty = subsetName.replace( '-', '_' ),
					typoValue;
				if ( 'undefined' !== typeof self.activeTypoSetData[ name ] ) {
					typoValue = self.activeTypoSetData[ name ][ typoProperty ];
					if ( 'font_size' === typoProperty ) {
						if ( 'body' === name || 'lead' === name ) {
							typoValue = parseFloat( baseSize ).toFixed( 2 ) + 'px';
						} else if ( 'small' === name ) {
							typoValue = parseFloat( baseSize * 0.8125 ).toFixed( 2 ) + 'px';
						} else if ( 'subheadings' === name ) {
							typoValue = parseFloat( baseSize * 1.5 ).toFixed( 2 ) + 'px';
						} else if ( 'headings' === name ) {
							typoValue = parseFloat( baseSize * Math.pow( ratio, 5 ) ).toFixed( 2 ) + 'px';
						}
					}

					typoSet[ cssSlug + '-' + subsetName ] = typoValue;
				}
			} );
		} );

		for ( i = 1; 6 >= i; i++ ) {
			typoSet[ '--h' + i + '_typography-font-size' ] = ( baseSize * Math.pow( ratio, 6 - i ) ).toFixed( 2 ) + 'px';
		}

		return typoSet;
	},

	/**
   * Update preview iframe.
   *
   * @since 3.7
   * @return {object}
   */
	updatePreviewIframe( $element, iframe, event ) {
		var overWriteType    = $element.find( 'input[name$="overwrite-type"]:checked' ).val(),
			shouldInvert     = $element.find( 'input[name$="invert"]:checked' ).val(),
			varData          = {
				color_palette: {},
				typo_sets: {},
				shouldInvert: shouldInvert,
				dark_light: jQuery( '#dark_light' ).val()
			};

		if ( 'inherit' === overWriteType ) {
			varData.color_palette = 'do-invert' === shouldInvert ? this.getInvertedColorPalette( this.getColorPallete() ) : this.getColorPallete();
			varData.typo_sets     = this.getTypoSets();
		}

		if ( event && jQuery( event.target ).is( 'input[name$="invert"]' ) ) {
			varData.is_invert_button = true;
		}

		iframe[ 0 ].contentWindow.postMessage( varData, '*' );
	},

	/**
   * Set layout options.
   *
   * @since 3.7
   * @return {object}
   */
	setLayoutOptions( $element ) {
		const 	optionsWrap = $element.find( '.awb-setup-import-options' ),
				input		= $element.find( 'input.layout-input-options' ),
				options = {};

		optionsWrap.find( 'input[type=radio]' ).each( function () {
			if ( jQuery( this ).is( ':checked' ) ) {
				const name = jQuery( this ).attr( 'name' ).split( '_' )[ 1 ];
				options[ name ] = jQuery( this ).val();
			}
		} );

		input.val( JSON.stringify( options ) ).trigger( 'change' );
	}
};

( function( jQuery ) {

	'use strict';

	jQuery( document ).ready( function() {
		window.awbSetupWizard.init();
	} );
}( jQuery ) );

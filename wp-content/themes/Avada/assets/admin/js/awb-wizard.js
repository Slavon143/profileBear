/* global awbPerformance, AwbTypography, ajaxurl, awbTypoData */
window.awbWizard = {

	/**
	 * Run actions on load.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	init: function() {
		this.$el        = jQuery( '.avada-dashboard' );
		this.activeStep = 1;
		this.$steps     = this.$el.find( '.awb-wizard-steps' );
		this.homeURL    = awbPerformance.homeURL;
		this.apiKey     = awbPerformance.apiKey;
		this.saveChange = awbPerformance.saveChange;
		this.accessible = this.checkSiteAccessible();

		// Font variant notice.
		this.$vnote     = this.$el.find( '.variant-analysis' );
		this.$vcount    = this.$el.find( '.variant-count' );

		// Lighthouse data is turned on.
		this.lighthouse = {
			before: false,
			after: false
		};
		this.runTests   = 'none';

		// Listeners for events.
		this.addListeners();

		// Init typography fields, could be moved.
		this.fontsRendered = false;
		this.initFontOptions();
	},


	/**
	 * Init font family/variant select fields.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	initFontOptions: function() {
		var self        = this,
			$fontFamily = this.$el.find( '.fusion-builder-font-family' );

		if ( _.isUndefined( window.awbTypographySelect ) || _.isUndefined( window.awbTypographySelect.webfonts ) ) {
			jQuery.when( window.awbTypographySelect.getWebFonts() ).done( function() {
				self.initAfterWebfontsLoaded( $fontFamily );
			} );
		} else {
			this.initAfterWebfontsLoaded( $fontFamily );
		}

		this.maybeShowPreload();
	},

	/**
	 * Handle dependency for preloading options.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	maybeShowPreload: function() {
		var preloadVal = this.$el.find( 'input[name="preload_fonts"]' ).val();
		if ( 'all' === preloadVal || 'google_fonts' === preloadVal ) {
			this.$el.find( '[name="preload_fonts_variants"], [name="preload_fonts_subsets"]' ).closest( '.pyre_metabox_field' ).show();
		} else {
			this.$el.find( '[name="preload_fonts_variants"], [name="preload_fonts_subsets"]' ).closest( '.pyre_metabox_field' ).hide();
		}
	},

	/**
	 * Check if site is accessible.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	checkSiteAccessible: function() {
		if ( this.homeURL.includes( 'localhost' ) ) {
			return false;
		}
		if ( ! this.homeURL.includes( '//' ) ) {
			return false;
		}
		return true;
	},

	/**
	 * Change which step is active.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	changeStep: function( step ) {
		var $step = this.$el.find( '.awb-wizard-section[data-step="' + step + '"]' );

		if ( $step.length ) {

			// If this is the final step.
			if ( 'finish' === $step.attr( 'data-id' ) ) {
				this.finishSteps();
				this.maybeFinishGPS();
			}

			$step.removeClass( 'hidden' );
			this.$el.find( '.awb-wizard-section:not([data-step="' + step + '"])' ).addClass( 'hidden' );
			this.activeStep = parseInt( step );

			this.$steps.find( '.completed' ).removeClass( 'completed' );
			this.$steps.find( 'li:not([data-id="' + step + '"])' ).removeClass( 'active' );
			this.$steps.find( '[data-id="' + step + '"]' ).addClass( 'active' ).prevAll().addClass( 'completed' );

			this.scrollTo();
		}
	},

	/**
	 * Perform finishing steps, cache, homepage load and test if desired.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	finishSteps: function() {
		var self          = this,
			$progress     = this.$el.find( '.finish-progress' ),
			$heading      = $progress.find( '.avada-db-card-notice-heading' ),
			$improvements = this.$el.find( '.awb-possible-improvements' );

		$progress.addClass( 'fetching' );

		// Clear cache and maybe run lighthouse.
		this.clearCache().done( function() {
			$heading.html( awbPerformance.loadingHome );

			jQuery.ajax( {
				type: 'GET',
				url: self.homeURL
			} )
			.done( function() {
				if ( ! self.maybeFinishGPS() ) {
					$progress.removeClass( 'fetching' ).addClass( 'success' );
					$heading.html( awbPerformance.wizardComplete );
				} else {
					$heading.html( awbPerformance.performLighthouse );
				}
			} )
			.fail( function() {
				$progress.removeClass( 'fetching' );
				self.addTemporaryClass( $progress, 'error' );
				$heading.html( awbPerformance.errorLoadingPage );
			} );
		} ).fail( function() {
			$progress.removeClass( 'fetching' );
			self.addTemporaryClass( $progress, 'error' );
			$heading.html( awbPerformance.errorClearingCache );
		} );

		// Check if there are any recommendations to highlight.
		if ( this.$el.find( '.pyre_metabox_field.value-bad' ).length ) {
			$improvements.html( '' ).closest( '.awb-recommendation-holder' ).addClass( 'show-recommendations' );
			this.$el.find( '.pyre_metabox_field.value-bad' ).each( function() {
				var $element = jQuery( this );
				$improvements.append( '<li><strong>' + $element.find( 'label' ).text() + '</strong> - ' + $element.next( '.wizard-recommendation' ).text() + '</li>' );
			} );
		}
	},

	/**
	 * Clear the cache.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	clearCache: function() {
		return jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'awb_performance_cache',
				awb_performance_nonce: jQuery( '#awb-performance-nonce' ).val()
			}
		} );
	},

	/**
	 * Scroll to top when step changed.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	scrollTo: function() {
		jQuery( 'html, body' ).animate( {
			scrollTop: 0
		}, 300 );
	},

	/**
	 * Check which test to run.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	checkTest: function() {
		if ( ! awbPerformance.lighthouse ) {
			return 'none';
		}
		return this.$el.find( '#awb_wizard_tests' ).length ? this.$el.find( '#awb_wizard_tests' ).val() : 'none';
	},

	/**
	 * Check if we should start a before lighthouse test and run it.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	maybeStartGPS: function() {
		this.runTests = this.checkTest();

		// If user wants testing and nothing retrieved already, run test in  background.
		if ( 'none' !== this.runTests && false === this.lighthouse.before ) {
			this.runPageSpeed( 'before' );
		}
	},

	/**
	 * Check if we should start an after lighthouse test and run it.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	maybeFinishGPS: function() {
		// If user wants testing and nothing retrieved already, run test in  background.
		if ( 'none' !== this.runTests && false === this.lighthouse.after ) {

			// TODO: clear cache (always), then if testing score load page first to ensure CSS & JS generated.
			this.runPageSpeed( 'after' );

			this.$el.find( '.awb-wizard-score-holder' ).removeClass( 'hidden' );
			return true;
		}

		this.$el.find( '.awb-wizard-score-holder' ).addClass( 'hidden' );
		return false;
	},

	/**
	 * Add needed listeners.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	addListeners: function() {
		var self = this;

		// Listen for step change click.
		this.$el.find( '.awb-wizard-link' ).on( 'click', function( event ) {
			var target      = jQuery( this ).attr( 'data-id' ),
				$activeStep = self.$el.find( '.awb-wizard-section[data-step="' + self.activeStep + '"]' );

			event.preventDefault();

			// Check if active step has unsaved changes.
			if ( '1' === $activeStep.attr( 'data-save' ) ) {
				if ( ! window.confirm( self.saveChange ) ) { // eslint-disable-line no-alert
					return;
				}
			}

			if ( target ) {
				self.changeStep( target );
			}

			// If its the starting step.
			if ( jQuery( this ).hasClass( 'awb-get-started' ) ) {
				self.maybeStartGPS();
			}
		} );

		// Listen for test radio change and set to el to control what is shown on final step.
		this.$el.find( '#awb_wizard_tests' ).on( 'change', function() {
			self.$el.attr( 'data-test', jQuery( this ).val() );
		} );

		// Listen for option change and enable save.
		this.$el.find( '.button-set-value:not( #awb_wizard_tests ), select, input[type="checkbox"], .fusion-slider-input, .fusion-select-option-value' ).on( 'change', function() {
			var $field      = jQuery( this ).closest( '.pyre_metabox_field' ),
				value       = jQuery( this ).val(),
				recommended = $field.attr( 'data-recommendation' );

			// If its peload fonts changing, update the dependencies.
			if ( 'preload_fonts' === jQuery( this ).attr( 'name' ) ) {
				self.maybeShowPreload();
			}

			// If its an initial font option render, don't trigger save state.
			if ( 4 == parseInt( jQuery( this ).closest( '.awb-wizard-section' ).attr( 'data-step' ) ) && ! self.fontsRendered ) {
				return;
			}

			// Check if it has recommendation and compare.
			if ( recommended ) {
				if ( 'object' === typeof value && 'function' === typeof value.join ) {
					value = value.join( ',' );
				}
				if ( recommended === value ) {
					$field.removeClass( 'value-bad' ).addClass( 'value-good' );
				} else {
					$field.removeClass( 'value-good' ).addClass( 'value-bad' );
				}
			}

			// Set step change to requiring a save.
			jQuery( this ).closest( '.awb-wizard-section' ).attr( 'data-save', '1' );
		} );

		// Listen stage save click.
		this.$el.find( '.awb-wizard-save' ).on( 'click', function( event ) {
			var $button  = jQuery( this ),
				saveData = {},
				$step    = $button.closest( '.awb-wizard-section' );

			// Get the data we want to save.
			if ( 'elements' === $button.attr( 'data-id' ) ) {
				saveData = [];
				$step.find( ':checkbox:checked' ).each( function() {
					saveData.push( jQuery( this ).val() );
				} );
			} else {
				$step.find( '.pyre_field input, .pyre_field select' ).each( function() {
					var value = jQuery( this ).val(),
						name  = jQuery( this ).attr( 'name' );

					if ( 'string' !== typeof name ) {
						return;
					}

					if ( 'object' === typeof value && ! value.length ) {
						value = [ '' ];
					}

					// Empty extra is pointless, remove it.
					name = name.replace( '[]', '' );

					// If its an array subvalue.
					if ( name.includes( '[' ) ) {
						name = name.split( '[' );
						if ( 'object' !== typeof saveData[ name[ 0 ] ] ) {
							saveData[ name[ 0 ] ] = {};
						}
						saveData[ name[ 0 ] ][ name[ 1 ].replace( ']', '' ) ] = value;
						return;
					}

					// Not a sub value.
					saveData[ name ] = value;
				} );
			}

			event.preventDefault();

			if ( $button.hasClass( 'disabled' ) ) {
				return;
			}

			$button.addClass( 'disabled saving' );

			jQuery.ajax( {
				type: 'POST',
				url: ajaxurl,
				dataType: 'json',
				data: {
					action: 'awb_performance_save',
					save_data: saveData,
					step: $button.attr( 'data-id' ),
					awb_performance_nonce: jQuery( '#awb-performance-nonce' ).val()
				}
			} )
			.done( function( response ) {
				$button.trigger( 'blur' );

				if ( 'object' !== typeof response ) {
					$button.removeClass( 'disabled saving' );
					self.addTemporaryClass( $button, 'error' );
					return;
				}

				if ( ! response.success ) {
					self.displayError( response.data );
					$button.removeClass( 'disabled saving' );
					self.addTemporaryClass( $button, 'error' );
					return;
				}

				$button.removeClass( 'disabled saving' );
				self.addTemporaryClass( $button, 'success' );
				$button.closest( '.awb-wizard-section' ).attr( 'data-save', '0' );
			} )
			.fail( function() {
				$button.removeClass( 'disabled saving' );
				self.addTemporaryClass( $button, 'error' );
			} );
		} );

		// Check all elements.
		this.$el.find( '.awb-wizard-checkall' ).on( 'click', function( event ) {
			event.preventDefault();
			self.$el.find( '.fusion-builder-option-field input' ).prop( 'checked', true );
			jQuery( this ).closest( '.awb-wizard-section' ).attr( 'data-save', '1' );
		} );

		// Uncheck all elements.
		this.$el.find( '.awb-wizard-uncheckall' ).on( 'click', function( event ) {
			event.preventDefault();
			self.$el.find( '.fusion-builder-option-field input' ).prop( 'checked', false );
			jQuery( this ).closest( '.awb-wizard-section' ).attr( 'data-save', '1' );
		} );

		// Apply recommendations
		this.$el.find( '.awb-wizard-apply' ).on( 'click', function( event ) {
			event.preventDefault();
			self.applyRecommendations();
			self.addTemporaryClass( jQuery( this ), 'success' );
		} );

		// Listen for scan button clicks.
		this.$el.find( '.awb-wizard-scan-button' ).on( 'click', function( event ) {
			var $button  = jQuery( this ),
				scanType = $button.attr( 'data-id' );

			event.preventDefault();

			if ( $button.hasClass( 'disabled' ) ) {
				return;
			}

			$button.addClass( 'disabled saving' );

			if ( 'fonts' === scanType || 'optimize' === scanType ) {
				$button.removeClass( 'disabled saving' ).trigger( 'blur' );
				$button.closest( '.awb-wizard-section' ).addClass( 'show-recommendations' );
				self.updateRecommendations();
				self.addTemporaryClass( $button, 'success' );
				return;
			}

			console.log( 'Scanning for ' +  scanType );
			jQuery.ajax( {
				type: 'GET',
				url: ajaxurl,
				dataType: 'json',
				data: {
					action: 'awb_performance_scan',
					scan_type: scanType,
					awb_performance_nonce: jQuery( '#awb-performance-nonce' ).val()
				}
			} )
			.done( function( response ) {
				$button.trigger( 'blur' );
				$button.closest( '.awb-wizard-section' ).addClass( 'show-recommendations' );

				if ( 'object' !== typeof response ) {
					$button.removeClass( 'disabled saving' );
					self.addTemporaryClass( $button, 'error' );
					return;
				}

				if ( ! response.success ) {
					self.displayError( response.data );
					$button.removeClass( 'disabled saving' );
					self.addTemporaryClass( $button, 'error' );
					return;
				}

				if ( 'icons' === scanType ) {
					self.updateIcons( response.data.markup );
					self.updateRecommendations( response.data.recommendations );
				} else if ( 'features' === scanType ) {
					self.updateRecommendations( response.data );
				} else if ( 'elements' === scanType ) {
					self.updateElements( response.data );
				}
				$button.removeClass( 'disabled saving' );
				self.addTemporaryClass( $button, 'success' );
			} )
			.fail( function() {
				$button.removeClass( 'disabled saving' );
				self.addTemporaryClass( $button, 'error' );

				if ( 'icons' === scanType || 'elements' === scanType ) {
					alert( awbPerformance.scanError ); // eslint-disable-line no-alert
				}
			} );
		} );

	},

	/**
	 * Uncheck elements not being used.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	updateElements: function( elements ) {
		var $checkboxes = this.$el.find( '.fusion-builder-option-field input' ),
			self        = this;

		if ( 'object' === typeof elements ) {

			// Check all first.
			$checkboxes.prop( 'checked', true );

			// Disable for each unused.
			jQuery.each( elements, function( element, disable ) { // eslint-disable-line no-unused-vars
				var $checkbox = self.$el.find( 'input[value="' + element + '"]' );
				if ( ! $checkbox.closest( 'li' ).hasClass( 'hidden' ) ) {
					$checkbox.prop( 'checked', false );
				}
			} );

			this.$el.find( '.awb-wizard-section[data-step="' + this.activeStep + '"]' ).attr( 'data-save', '1' );
		}
	},

	/**
	 * Update icon scan markup.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	updateRecommendations: function( data ) {
		var self        = this,
			$activeStep = this.$el.find( '.awb-wizard-section[data-step="' + this.activeStep + '"]' );

		// Add dynamic recommendation text.
		if ( 'object' === typeof data ) {
			jQuery.each( data, function( optionID, optionData ) {
				var $element = self.$el.find( '[name="' + optionID + '"]' ).closest( '.pyre_metabox_field' ),
					dynamic  = 'undefined' === typeof optionData.dynamic ? false : optionData.dynamic;

				if ( $element.length ) {
					$element.next( '.wizard-recommendation' ).remove();

					if ( 'string' === typeof optionData.message && '' !== optionData.message ) {
						$element.after( '<p class="wizard-recommendation"><i class="fusiona-af-rating"></i> ' + optionData.message + '</p>' );
					}
					if ( dynamic && 'undefined' !== typeof optionData.value ) {
						$element.attr( 'data-recommendation', optionData.value );
					}
				}
			} );
		}

		// Loop inputs and change value/add coloring.
		$activeStep.find( '.pyre_metabox_field' ).each( function() {
			var $element    = jQuery( this ),
				recommended = $element.attr( 'data-recommendation' ),
				$input      = $element.find( 'input, select' ),
				value       = $input.val();

			if ( ! recommended ) {
				return;
			}

			if ( 'object' === typeof value && 'function' === typeof value.join ) {
				value = value.join( ',' );
			}

			if ( recommended === value ) {
				$element.addClass( 'value-good' ).removeClass( 'value-bad' );
			} else {
				$element.addClass( 'value-bad' ).removeClass( 'value-good' );
			}
		} );

		// Make apply all button visibile.
		$activeStep.addClass( 'show-apply-all' );
	},

	/**
	 * Apply recommendations on current step.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	applyRecommendations: function() {
		var $activeStep = this.$el.find( '.awb-wizard-section[data-step="' + this.activeStep + '"]' );

		$activeStep.find( '.pyre_metabox_field' ).each( function() {
			var $element    = jQuery( this ),
				recommended = $element.attr( 'data-recommendation' ),
				$input      = $element.find( 'input, select' ),
				value       = $input.length ? $input.val() : null;

			// Allow empty recommended value for FA select field.
			if ( ! recommended && 'status_fontawesome' !== $input.attr( 'name' ) ) {
				return;
			}

			if ( $element.find( 'select[multiple]' ).length && 'string' === typeof recommended ) {
				recommended = recommended.split( ',' );
				$element.find( 'select[multiple]' ).val( recommended ).trigger( 'change' );
				return;
			}

			if ( null !== value && recommended === value ) {
				return;
			}

			$element.find( '.buttonset-item.ui-state-active' ).removeClass( 'ui-state-active' );
			$element.find( '.buttonset-item[data-value="' + recommended + '"]' ).addClass( 'ui-state-active' );
			$element.find( 'input, select' ).val( recommended ).trigger( 'change' );
		} );

		// Hide button, no longer needed.
		setTimeout( function() {
			$activeStep.removeClass( 'show-apply-all' );
		}, 2000 );
	},

	/**
	 * Update icon scan markup.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	updateIcons: function( data ) {

		if ( '' !== data ) {
			this.$el.find( '#fusion-used-icons-table' ).removeClass( 'hidden' );
			this.$el.find( '#fusion-used-icons-table tbody' ).html( data );
		}
	},

	/**
	 * Add a class, wait and then remove.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	addTemporaryClass: function( $element, classname ) {
		$element.addClass( classname );
		setTimeout( function() {
			$element.removeClass( classname );
		}, 2000 );
	},

	/**
	 * Display an error message to user.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	displayError: function( message ) {
		window.alert( message ); // eslint-disable-line no-alert
	},

	/**
	 * Run GPS test.
	 *
	 * @since 7.4
	 *
	 * @return {void}
	 */
	runPageSpeed: function( status ) {
		var self       = this,
			api        = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed',
			parameters = {
				url: encodeURIComponent( this.homeURL ),
				category: 'PERFORMANCE',
				strategy: this.runTests.toUpperCase()
			},
			query     = api;

		status = 'undefined' === typeof status ? 'before' : status;

		// Site is not accessible, don't try.
		if ( ! this.accessible || 'none' === this.runTests ) {
			self.$el.find( '.awb-wizard-score-holder' ).addClass( 'hidden' );
			return;
		}

		console.log( 'Running Test ', this.runTests );

		// Which type of analysis to run.
		query += '?url=' + parameters.url;
		query += '&category=' + parameters.category;
		query += '&strategy=' + parameters.strategy;

		if ( this.apiKey ) {
			query += '&key=' + this.apiKey;
		}

		// Fetch results.
		fetch( query ).then( ( response ) => response.json() ).then( ( json ) => {
			var lighthouse, lighthouseMetrics;

			// Hide message saying it is fetching scores.
			if ( 'after' === status ) {
				self.$el.find( '.finish-progress' ).removeClass( 'fetching' ).addClass( 'success' ).find( '.avada-db-card-notice-heading' ).html( awbPerformance.wizardComplete );
			}

			// Results failed in some way, set it so no further tests run.
			if ( 'object' !== typeof json || 'undefined' === typeof json.lighthouseResult ) {
				console.log( 'Lighthouse failed' );
				self.$el.find( '.awb-wizard-score-holder' ).addClass( 'hidden' );
				self.accessible = false;
				return;
			}

			lighthouse        = json.lighthouseResult;

			console.log( 'lighthouse' );

			lighthouseMetrics = {
				score: lighthouse.categories.performance.score * 100,
				fcp: lighthouse.audits[ 'first-contentful-paint' ].score * 100,
				lcp: lighthouse.audits[ 'largest-contentful-paint' ].score * 100,
				cls: lighthouse.audits[ 'cumulative-layout-shift' ].score * 100,
				fid: lighthouse.audits[ 'max-potential-fid' ].score * 100
			};

			// Store data with status.
			self[ status ] = lighthouseMetrics;

			console.log( 'Lighthouse completed', lighthouseMetrics );

			// Update actual markup
			jQuery.each( lighthouseMetrics, function( metricId, metricValue ) {
				var $score     = self.$el.find( '.awb-score-' + status + '[data-type="' + metricId + '"]' ),
					scoreClass = 'good';

				if ( $score.length ) {
					if ( 50 > metricValue ) {
						scoreClass = 'poor';
					} else if ( 90 > metricValue ) {
						scoreClass = 'okay';
					}

					$score.removeClass( 'score-good score-okay score-poor score-fetching' ).addClass( 'score-' + scoreClass ).attr( 'data-score', metricValue );
					$score.find( '.lh-gauge__percentage' ).html( Math.round( metricValue ) );
					$score.find( '.lh-gauge-arc' ).css( 'stroke-dasharray',  ( 360 * metricValue / 100 ) + ', 360' );
				}
			} );
		} );
	},

	/**
	 * Create the data for font family and render select field.
	 *
	 * @since 2.2
	 * @param {object} $fontFamily - The option jQuery elements.
	 * @return {Void}
	 */
	initAfterWebfontsLoaded: function( $fontFamily ) { // eslint-disable-line no-unused-vars
		var self = this;

		if ( 'object' !== typeof this.typoSets ) {
			this.typoSets = {};
		}

		this.$el.find( '.fusion-builder-font-family' ).each( function() {
			self.typoSets[ jQuery( this ).attr( 'data-id' ) ] = new AwbTypography( jQuery( this ), self );
		} );

		this.$el.on( 'change', '.input-variant', function() {
			self.updateVariantCount();
		} );

		setTimeout( function() {
			self.updateVariantCount();
		}, 100 );

		this.fontsRendered = true;
	},

	/**
	 * Count unique variants.
	 *
	 * @since 7.4
	 * @return {Void}
	 */
	updateVariantCount: function() {
		var variants      = {},
			variantLength = 0;

		this.$el.find( '.fusion-builder-font-family' ).each( function() {
			var $family  = jQuery( this ).find( '.input-font_family' ),
				$variant = jQuery( this ).find( '.input-variant' ),
				family   = $family.val(),
				variant  = $variant.val(),
				search   = '';

			if ( null === family ) {
				family = $family.attr( 'data-default' );
			}

			if ( null === variant ) {
				variant = $variant.attr( 'data-default' );
			}

			// Get font name from global typo set to avoid count the same font twice.
			if ( 'string' === typeof family && family.startsWith( 'var(--awb-' ) ) {
				const typoSetKey = family.replace( 'var(--awb-', '' ).replace( ')', '' ).replace( /\s+/g, '' ).split( '-' )[ 0 ];
				family = awbTypoData.data[ typoSetKey ][ 'font-family' ];
				variant = awbTypoData.data[ typoSetKey ][ 'font-weight' ] + awbTypoData.data[ typoSetKey ][ 'font-style' ];
			}
			search = family + variant;

			if ( 'undefined' !== typeof variants[ search ] ) {
				return;
			}

			variants[ search ] = true;
		} );

		variantLength = Object.keys( variants ).length;
		this.$vnote.attr( 'data-count', variantLength );
		this.$vcount.html( variantLength );
	}
};

( function( jQuery ) {

	'use strict';

	jQuery( document ).ready( function() {
		window.awbWizard.init();
	} );
}( jQuery ) );

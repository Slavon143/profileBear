/* global avadaPTBSlidersL10n */
var FusionPageBuilder = FusionPageBuilder || {},
	resizeTimeout;

( function() {

	var avadaSlidersButtonsVars = {

		/**
		 * Fusion-Slider.
		 * Contains specifics for the fusion-sliders implementation.
		 *
		 * @since 6.0
		 * @member {Object}
		 */
		fusionSlider: {

			/**
			 * The CSS selector for the sliders-container.
			 *
			 * @since 6.0
			 * @member {string}
			 */
			sliderContainerSelector: '#sliders-container .fusion-slider-container',

			/**
			 * The value we should set in the slider-type selector.
			 *
			 * @since 6.0
			 * @member {string}
			 */
			sliderType: 'flex',

			/**
			 * The option-name (ID) for the slider-type.
			 *
			 * @since 6.0
			 * @member {string}
			 */
			id: 'wooslider',

			/**
			 * Get the slider-ID.
			 *
			 * @since 6.0
			 * @return {number} - Returns the post ID for the slider.
			 */
			getSliderID: function() {
				var slider = jQuery( '#sliders-container > .fusion-slider-container' ),
					id     = slider.length ? slider.attr( 'id' ).replace( 'fusion-slider-', '' ) : 0;

				return ( isNaN( id ) ) ? 0 : Number( id );
			},

			/**
			 * Get the slide-ID.
			 *
			 * @since 6.0
			 * @return {number} - Returns the post-ID for the slide.
			 */
			getSlideID: function() {
				var slide     = document.querySelectorAll( '#sliders-container .flex-active-slide' ),
					classList = slide[ 0 ] ? slide[ 0 ].classList : 0,
					id        = 0,
					i;

				// Return 0 if there's no active slides.
				if ( 0 === classList || 0 === classList.length ) {
					return 0;
				}

				// Find the slide-ID from the css-classes.
				for ( i = 0; i < classList.length; i++ ) {
					if ( 0 === classList[ i ].indexOf( 'slide-id-' ) ) {
						id = classList[ i ].replace( 'slide-id-', '' );
					}
				}
				return ( isNaN( id ) ) ? 0 : Number( id );
			},

			/**
			 * Get the edit-slider URL.
			 *
			 * @since 6.0
			 * @return {string} - Returns URL.
			 */
			getSliderEditURL: function() {
				var sliderID = avadaSlidersButtonsVars.fusionSlider.getSliderID();

				// Fallback in case we couldn't find the slider-ID.
				if ( 0 === sliderID ) {
					return window.fusionSiteVars.adminUrl + 'edit-tags.php?taxonomy=slide-page&post_type=slide';
				}
				return window.fusionSiteVars.adminUrl + 'term.php?taxonomy=slide-page&tag_ID=' + sliderID;
			},

			/**
			 * Get the edit-slide URL.
			 *
			 * @since 6.0
			 * @return {string} - Returns URL.
			 */
			getSlideEditUrl: function() {
				var slideID = avadaSlidersButtonsVars.fusionSlider.getSlideID();

				// Fallback in case we couldn't find the slide-ID.
				if ( 0 === slideID ) {
					return window.fusionSiteVars.adminUrl + 'edit.php?post_type=slide';
				}
				return window.fusionSiteVars.adminUrl + 'post.php?post=' + slideID + '&action=edit';
			}
		},

		/**
		 * Slider-Revolution.
		 * Contains specifics for the revSlider implementation.
		 *
		 * @since 6.0
		 * @member {Object}
		 */
		revSlider: {

			/**
			 * The CSS selector for the sliders-container.
			 *
			 * @since 6.0
			 * @member {string}
			 */
			sliderContainerSelector: '#sliders-container .rev_slider_wrapper',

			/**
			 * The value we should set in the slider-type selector.
			 *
			 * @since 6.0
			 * @member {string}
			 */
			sliderType: 'rev',

			/**
			 * The option-name (ID) for the slider-type.
			 *
			 * @since 6.0
			 * @member {string}
			 */
			id: 'revslider',

			/**
			 * Get the edit-slider URL.
			 *
			 * @since 6.0
			 * @return {string} - Returns URL.
			 */
			getSliderEditURL: function() {
				var $slide = jQuery( '#sliders-container rs-slide' ).first(),
					id     = $slide.length ? parseInt( $slide.attr( 'data-key' ).replace( 'rs-', '' ) ) : false;

				if ( id ) {
					return window.fusionSiteVars.adminUrl + 'admin.php?page=revslider&view=slide&id=' + id;
				}

				// Fallback in case we could not find the ID.
				return window.fusionSiteVars.adminUrl + 'admin.php?page=revslider';
			},

			/**
			 * Get the edit-slide URL.
			 *
			 * @since 6.0
			 * @return {string} - Returns URL.
			 */
			getSlideEditUrl: function() {
				var $slide = jQuery( '#sliders-container .active-rs-slide:visible' ).filter( function() {
						return 0 !== jQuery( this ).css( 'opacity' ) && 'hidden' !== jQuery( this ).css( 'visibility' );
					} ),
					id = $slide.length ? parseInt( $slide.attr( 'data-key' ).replace( 'rs-', '' ) ) : false;

				if ( id ) {
					return window.fusionSiteVars.adminUrl + 'admin.php?page=revslider&view=slide&id=' + id;
				}

				// Fallback in case we could not find the ID.
				return window.fusionSiteVars.adminUrl + 'admin.php?page=revslider';
			}
		},

		/**
		 * LayerSlider
		 * Contains specifics for the layerSlider implementation.
		 *
		 * @since 6.0
		 * @member {Object}
		 */
		layerSlider: {

			/**
			 * The CSS selector for the sliders-container.
			 *
			 * @since 6.0
			 * @member {string}
			 */
			sliderContainerSelector: '#sliders-container > #layerslider-container',

			/**
			 * The value we should set in the slider-type selector.
			 *
			 * @since 6.0
			 * @member {string}
			 */
			sliderType: 'layer',

			/**
			 * The option-name (ID) for the slider-type.
			 *
			 * @since 6.0
			 * @member {string}
			 */
			id: 'slider',

			/**
			 * Get the slider-ID.
			 *
			 * @since 6.0
			 * @return {number} - Returns the post ID for the slider.
			 */
			getSliderID: function() {
				var slider = document.querySelectorAll( '#layerslider-container .ls-wp-container' ),
					id     = ( slider[ 0 ] ) ? slider[ 0 ].getAttribute( 'id' ).replace( 'layerslider_', '' ) : 0;

				return ( isNaN( id ) ) ? 0 : Number( id );
			},

			/**
			 * Get the edit-slider URL.
			 *
			 * @since 6.0
			 * @return {string} - Returns URL.
			 */
			getSliderEditURL: function() {
				var sliderID = avadaSlidersButtonsVars.layerSlider.getSliderID();

				// Fallback in case we didn't find any sliders.
				if ( 0 === sliderID ) {
					return window.fusionSiteVars.adminUrl + 'admin.php?page=layerslider';
				}
				return window.fusionSiteVars.adminUrl + 'admin.php?page=layerslider&action=edit&id=' + sliderID;
			},

			/**
			 * Since layerSlider doesn't have a dedicated URL to edit the slides, return false.
			 *
			 * @since 6.0
			 * @return {false} - There is no URL, so return false.
			 */
			getSlideEditUrl: function() {
				return false;
			},

			/**
			 * Get the edit-slide URL.
			 *
			 * @since 6.0
			 * @return {string} - Returns URL.
			 */
			getSliderEditUrl: function() {
				return window.fusionSiteVars.adminUrl + 'admin.php?page=revslider';
			}
		},

		/**
		 * Elastic-Slider.
		 * Contains specifics for the elastic-slider implementation.
		 *
		 * @since 6.0
		 * @member {Object}
		 */
		elastic: {

			/**
			 * The CSS selector for the sliders-container.
			 *
			 * @since 6.0
			 * @member {string}
			 */
			sliderContainerSelector: '#sliders-container #ei-slider',

			/**
			 * The value we should set in the slider-type selector.
			 *
			 * @since 6.0
			 * @member {string}
			 */
			sliderType: 'elastic',

			/**
			 * The option-name (ID) for the slider-type.
			 *
			 * @since 6.0
			 * @member {string}
			 */
			id: 'elasticslider',

			/**
			 * Get the slider-ID.
			 *
			 * @since 6.0
			 * @return {number} - Returns the post ID for the slider.
			 */
			getSliderID: function() {
				var slider    = document.querySelectorAll( '#sliders-container #ei-slider' ),
					classList = slider[ 0 ] ? slider[ 0 ].classList : 0,
					id        = 0,
					i;

				// Return 0 if no sliders were found.
				if ( 0 === classList || 0 === classList.length ) {
					return 0;
				}

				// Find the slider-ID.
				for ( i = 0; i < classList.length; i++ ) {
					if ( 0 === classList[ i ].indexOf( 'ei-slider-' ) ) {
						id = classList[ i ].replace( 'ei-slider-', '' );
					}
				}

				return ( isNaN( id ) ) ? 0 : Number( id );
			},

			/**
			 * Get the slide-ID.
			 *
			 * @since 6.0
			 * @return {number} - Returns the post ID for the slide.
			 */
			getSlideID: function() {
				var slides = document.querySelectorAll( '#ei-slider .ei-slider-large > li' ),
					id     = 0,
					classList,
					i,
					j;

				// Loop slides.
				for ( i = 0; i < slides.length; i++ ) {

					// Check if the slide is active.
					if ( '1' === getComputedStyle( slides[ i ] ).opacity ) {
						classList = slides[ i ].classList;

						// Loop classes and get the ID.
						for ( j = 0; j < classList.length; j++ ) {
							if ( 0 === classList[ j ].indexOf( 'ei-slide-' ) ) {
								id = classList[ j ].replace( 'ei-slide-', '' );
							}
						}
					}
				}
				return isNaN( id ) ? 0 : Number( id );
			},

			/**
			 * Get the edit-slider URL.
			 *
			 * @since 6.0
			 * @return {string} - Returns URL.
			 */
			getSliderEditURL: function() {
				if ( avadaSlidersButtonsVars.elastic.getSliderID() ) {
					return window.fusionSiteVars.adminUrl + 'term.php?taxonomy=themefusion_es_groups&tag_ID=' + avadaSlidersButtonsVars.elastic.getSliderID() + '&post_type=themefusion_elastic';
				}

				// Fallback in case no slider-ID was found.
				return window.fusionSiteVars.adminUrl + 'edit-tags.php?taxonomy=themefusion_es_groups&post_type=themefusion_elastic';
			},

			/**
			 * Get the edit-slide URL.
			 *
			 * @since 6.0
			 * @return {string} - Returns URL.
			 */
			getSlideEditUrl: function() {
				if ( avadaSlidersButtonsVars.elastic.getSlideID() ) {
					return window.fusionSiteVars.adminUrl + 'post.php?post=' + avadaSlidersButtonsVars.elastic.getSlideID() + '&action=edit';
				}

				// Fallback in case no slide-ID was found.
				return window.fusionSiteVars.adminUrl + 'edit.php?post_type=themefusion_elastic';
			}
		}
	};

	var avadaPTBAndSlider = {
		iconsHTML: {
			editPTBLayoutSection: '<a href="#" class="edit-template has-tooltip" aria-label="' + avadaPTBSlidersL10n.editPTBLayoutSection + '" target="_top"><i class="fusiona-page_title" aria-hidden="true"></i></a>',
			editPTB: '<a href="#" id="fusion-edit-ptb-to-action-button" class="edit has-tooltip" aria-label="' + avadaPTBSlidersL10n.editPTB + '"><i class="fusiona-cog" aria-hidden="true"></i></a>',
			editPTBOptions: '<a href="#" id="fusion-edit-ptb-action-button" class="edit-options has-tooltip" aria-label="' + avadaPTBSlidersL10n.editPTBOptions + '"><i class="fusiona-settings" aria-hidden="true"></i></a>',
			removePTB: '<a href="#" id="fusion-remove-ptb-action-button" class="remove has-tooltip" aria-label="' + avadaPTBSlidersL10n.removePTB + '"><i class="fusiona-trash-o" aria-hidden="true"></i></a>',
			editSlider: '<a href="#" id="fusion-edit-slider-action-button" class="edit has-tooltip" aria-label="' + avadaPTBSlidersL10n.editSlider + '"><i class="fusiona-pen" aria-hidden="true"></i></a>',
			editSliderOptions: '<a href="#" id="fusion-edit-slider-options-action-button" class="edit-options has-tooltip" aria-label="' + avadaPTBSlidersL10n.editSliderOptions + '"><i class="fusiona-cog" aria-hidden="true"></i></a>',
			removeSlider: '<a href="#" id="fusion-remove-slider" class="remove has-tooltip" aria-label="' + avadaPTBSlidersL10n.removeSlider + '"><i class="fusiona-trash-o" aria-hidden="true"></i></a>'
		},

		/**
		 * Init.
		 *
		 * @since 6.0
		 * @return {void}
		 */
		init: function() {

			// PTB Buttons & Actions.
			this.thePTBButtons();
			this.thePTBButtonsActions();

			// Slider Buttons & Actions.
			this.theSliderButtons();
			this.theSliderButtonsActions();

			// CSS tweaks.
			this.cssTweaks();
		},

		/**
		 * Watch for button clicks on the PTB buttons.
		 *
		 * @since 6.0
		 * @return {void}
		 */
		thePTBButtonsActions: function() {
			var self = this;

			// Add PTB Button.
			jQuery( this.thePTBButtons().find( 'a.add-ptb' ) ).on( 'click', function( event ) {
				if ( 'to' === self.getPTBContext() ) {
					self.focusOnPTBSectionTO();
				} else {
					self.focusOnPTBSection();
				}
				self.setPTBVal( 'bar_and_content' );
				self.refreshButtons();
				event.preventDefault();
			} );

			// Remove PTB.
			jQuery( this.thePTBButtons().find( 'a.remove' ) ).on( 'click', function( event ) {
				if ( 'to' === self.getPTBContext() ) {
					self.focusOnPTBSectionTO();
				} else {
					self.focusOnPTBSection();
				}
				self.setPTBVal( 'hide' );
				self.refreshButtons();
				event.preventDefault();
			} );

			// Edit PTB Options.
			jQuery( this.thePTBButtons().find( 'a.edit' ) ).on( 'click', function( event ) {
				self.focusOnPTBSectionTO();
				event.preventDefault();
			} );
			jQuery( this.thePTBButtons().find( 'a.edit-options' ) ).on( 'click', function( event ) {
				self.focusOnPTBSection();
				event.preventDefault();
			} );
		},

		/**
		 * Watch for button clicks on the Slider buttons.
		 *
		 * @since 6.0
		 * @return {void}
		 */
		theSliderButtonsActions: function() {
			var self = this;

			// Add Slider Button.
			jQuery( this.theSliderButtons().find( 'a.add-slider' ) ).on( 'click', function( event ) {
				event.preventDefault();
				self.focusOnSliderSection();
				self.setSliderVal( 'add' );
				self.refreshButtons();
			} );

			// Remove Slider Button.
			jQuery( this.theSliderButtons().find( 'a.remove' ) ).on( 'click', function( event ) {
				event.preventDefault();
				self.focusOnSliderSection();
				self.setSliderVal( 'remove' );
				self.refreshButtons();
			} );

			// Edit Slider Options Button.
			jQuery( this.theSliderButtons().find( 'a.edit-options' ) ).on( 'click', function( event ) {
				event.preventDefault();
				self.focusOnSliderSection();
			} );

			// Edit Slider Button.
			jQuery( this.theSliderButtons().find( 'a.edit' ) ).on( 'click', function( event ) {
				var modalArgs;

				event.preventDefault();

				modalArgs = {

					title: self.getSlider().sliderType && avadaPTBSlidersL10n.types[ self.getSlider().sliderType ] ? avadaPTBSlidersL10n.types[ self.getSlider().sliderType ] : avadaPTBSlidersL10n.fusionSlider,
					type: 'info',
					icon: '<span class="fusiona-uniF61C"></span>',
					content: avadaPTBSlidersL10n.editSelectedSlider,
					actions: [
						{
							label: '<span class="screen-reader-text">' + avadaPTBSlidersL10n.cancel + '</span>',
							classes: 'x-close',
							callback: function() {

								// Close the popup.
								window.parent.FusionApp.confirmationPopup( {
									action: 'hide'
								} );
							}
						}
					]
				};

				if ( self.getSlider().getSliderEditURL() ) {
					modalArgs.actions.push( {
						label: avadaPTBSlidersL10n.editSlider,
						classes: 'edit-slider yes',
						callback: function() {

							// Open the URL in a new tab.
							var win = window.open( self.getSlider().getSliderEditURL(), '_blank' );

							// Focus on the new tab.
							win.focus();

							// Close the popup.
							window.parent.FusionApp.confirmationPopup( {
								action: 'hide'
							} );
						}
					} );
				}

				if ( self.getSlider().getSlideEditUrl() ) {
					modalArgs.actions.push( {
						label: avadaPTBSlidersL10n.editSlide,
						classes: 'edit-slide yes',
						callback: function() {

							// Open the URL in a new tab.
							var win = window.open( self.getSlider().getSlideEditUrl(), '_blank' );

							// Focus on the new tab.
							win.focus();

							// Close the popup.
							window.parent.FusionApp.confirmationPopup( {
								action: 'hide'
							} );
						}
					} );
				}

				window.parent.FusionApp.confirmationPopup( modalArgs );
			} );
		},

		/**
		 * Get PTB theme-option & page-option.
		 *
		 * @since 6.0
		 * @return {Array} - [TO, PO]
		 */
		getPTBContext: function() {
			return ( 'default' === window.parent.FusionApp.data.postMeta._fusion.page_title_bar ) ? 'to' : 'po';
		},

		/**
		 * Focus on the PTB section in the sidebar.
		 *
		 * @since 6.0
		 * @return {void}
		 */
		focusOnPTBSection: function() {
			window.parent.FusionApp.sidebarView.openOption( 'pagetitlebar', 'po', true );
		},

		/**
		 * Focus on the PTB section in the sidebar.
		 *
		 * @since 6.0
		 * @return {void}
		 */
		focusOnPTBSectionTO: function() {
			var optionName = 'page_title_bar';
			if ( window.parent.FusionApp.data.is_home ) {
				optionName = 'blog_show_page_title_bar';
			}
			window.parent.FusionApp.sidebarView.openOption( optionName );
		},

		/**
		 * Focus on the slider section.
		 *
		 * @since 6.0
		 * @return {void}
		 */
		focusOnSliderSection: function() {
			window.parent.FusionApp.sidebarView.openOption( 'sliders_note', 'po' );
		},

		/**
		 * Set the PTB value.
		 * Changes wither the PO or TO depending on the current settings.
		 *
		 * @param {string} val - The value we want to set.
		 * @return {void}
		 */
		setPTBVal: function( val ) {
			var self = this,
				select;

			// Get the select element.
			setTimeout( function() {
				select = 'to' === self.getPTBContext() ? self.theParentWindowSidebar().find( '[data-type="TO"] [name="page_title_bar"]' ) : self.theParentWindowSidebar().find( '[data-type="PO"] [name="page_title_bar"]' );

				if ( 'to' === self.getPTBContext() ) {

					// We want to change the PTB in theme-options.
					select.val( val ).trigger( 'change' );
				} else {
					switch ( val ) {
					case 'hide':
						val = 'no';
						break;
					case 'bar_and_content':
						val = 'yes';
						break;
					}
					select.val( val ).trigger( 'change' );
				}
			}, 50 );
		},

		/**
		 * Set the Slider value.
		 * Changes wither the PO or TO depending on the current settings.
		 *
		 * @param {string} val - The value we want to set.
		 * @return {void}
		 */
		setSliderVal: function( val ) {
			var self          = this,
				sliderType    = this.theParentWindowSidebar().find( '[name="slider_type"]' ),
				sliderSelect  = this.theParentWindowSidebar().find( '[name="' + self.getSlider().id + '"]' ),
				sliderOptions = 'undefined' !== typeof sliderSelect ? sliderSelect.find( 'option' ) : [],
				sliderVal     = 'undefined' !== typeof sliderSelect ? sliderSelect.val() : '';

			// Set slider-type to FusionSlider.
			if ( 'add' === val ) {
				sliderType.val( self.getSlider().sliderType ).trigger( 'change' );

				// If the current value is set to '' (no slider selected)
				// then select the 1st available slider if it exists.
				if ( '' === sliderVal ) {
					if ( sliderOptions[ 1 ] ) {
						sliderSelect.val( jQuery( sliderOptions[ 1 ] ).attr( 'value' ) ).trigger( 'change' );
					}
				}
			} else if ( 'remove' === val ) {
				sliderType.val( 'no' ).trigger( 'change' );
			}
		},

		/**
		 * Get the jQuery object for the parent-window sidebar.
		 *
		 * @since 6.0
		 * @return {Object} - Returns the jQuery object for #customize-controls.
		 */
		theParentWindowSidebar: function() {
			return window.parent.jQuery( '#customize-controls' );
		},

		/**
		 * Get the jQuery object for the PTB.
		 * If no PTB exists then return false.
		 *
		 * @since 6.0
		 * @return {Object|false} - Returns a jQuery object, or false if there is no PTB.
		 */
		thePTB: function() {
			var PTB;
			if ( this.$ptb ) {
				return this.$ptb;
			}
			PTB = jQuery( '.fusion-page-title-bar' );
			return ( PTB.length ) ? PTB : false;
		},

		/**
		 * Get the jQuery object for the slider.
		 * If no slider exists then return false.
		 *
		 * @since 6.0
		 * @return {Object|false} - Returns a jQuery object or false if there is no slider.
		 */
		theSlider: function() {
			var sliderContainer;
			if ( this.$sliderContainer ) {
				return this.$sliderContainer;
			}
			sliderContainer = jQuery( this.getSlider().sliderContainerSelector );
			return ( sliderContainer.length ) ? sliderContainer : false;
		},

		/**
		 * Get the jQuery object for the add-ptb icon.
		 * If no icon exists then we create one and then return it.
		 *
		 * @since 6.0
		 * @return {Object} - Returns a jQuery object.
		 */
		thePTBButtons: function() {
			var buttonHTML       = this.iconsHTML.addPTB,
				wrapperID        = 'fusion-builder-ptb-actions',
				buttonsContainer = this.thePTB(),
				link             = '';

			// If PTB is added use edit link instead.
			if ( this.thePTB() ) {

				// Check if PTB template override is active.
				if ( 'undefined' !== typeof window.parent.FusionApp.data.template_override && 'undefined' !== typeof window.parent.FusionApp.data.template_override.page_title_bar && false !== window.parent.FusionApp.data.template_override.page_title_bar ) {

					// Construct link.
					if ( window.parent.FusionApp.data.template_override.page_title_bar.permalink && -1 !== window.parent.FusionApp.data.template_override.page_title_bar.permalink.indexOf( '?' ) ) {
						link = window.parent.FusionApp.data.template_override.page_title_bar.permalink + '&fb-edit=1&target_post_id=' + window.parent.FusionApp.data.postDetails.post_id;
					} else {
						link = window.parent.FusionApp.data.template_override.page_title_bar.permalink + '?fb-edit=1&target_post_id=' + window.parent.FusionApp.data.postDetails.post_id;
					}

					buttonHTML = this.iconsHTML.editPTBLayoutSection.replace( 'href="#"', 'href="' + link + '"' );
				} else {
					buttonHTML = this.iconsHTML.editPTB;

					// Add PO / Tax and Remove links only on pages where it makes sense.
					if ( window.parent.FusionApp && window.parent.FusionApp.data && ! _.isEmpty( window.parent.FusionApp.data.fusionPageOptions ) ) {
						buttonHTML += this.iconsHTML.editPTBOptions + this.iconsHTML.removePTB;
					}
				}
			}

			if ( ! jQuery( '#fusion-builder-ptb-actions' ).length && buttonsContainer ) {
				buttonsContainer.append( '<div id="' + wrapperID + '" class="has-ptb">' + buttonHTML + '</div>' );
				return jQuery( '#fusion-builder-ptb-actions' );
			}

			if ( ! jQuery( 'body' ).hasClass( 'avada-has-titlebar-hide' ) ) {
				return jQuery( '#fusion-builder-ptb-actions' );
			}

			return jQuery( 'body' ).hasClass( 'side-header' ) ? jQuery( '#side-header' ) : jQuery( '.fusion-header' );
		},

		/**
		 * Get the jQuery object for the add-slider icon.
		 * If no icon exists then we create one and then return it.
		 *
		 * @since 6.0
		 * @return {Object} - Returns a jQuery object.
		 */
		theSliderButtons: function() {
			var buttonHTML   = this.iconsHTML.editSliderOptions + this.iconsHTML.editSlider + this.iconsHTML.removeSlider,
				wrapperID    = 'fusion-builder-slider-actions';

			if ( ! jQuery( '#fusion-builder-slider-actions' ).length && 0 < jQuery( '#sliders-container' ).length ) {
				jQuery( '#sliders-container' ).append( '<div id="' + wrapperID + '" class="has-slider">' + buttonHTML + '</div>' );
			}

			if ( this.theSlider() ) {
				return jQuery( '#fusion-builder-slider-actions' );
			}

			return jQuery( 'body' ).hasClass( 'side-header' ) ? jQuery( '#side-header' ) : jQuery( '.fusion-header' );
		},

		/**
		 * Refreshes the buttons.
		 *
		 * @since 6.0
		 * @return {void}
		 */
		refreshButtons: function() {
			var self = this;

			// Remove event listeners.
			this.thePTBButtons().find( 'a.add-ptb' ).off();
			this.thePTBButtons().find( 'a.remove' ).off();
			this.theSliderButtons().find( 'a.add-slider' ).off();
			this.theSliderButtons().find( 'a.remove' ).off();
			jQuery( document.body ).off( 'fusion-partial-slider_type' );
			_.each( avadaSlidersButtonsVars, function( sliderType ) {
				jQuery( document.body ).off( 'fusion-partial-' + sliderType );
			} );

			// Remove existing buttons.
			jQuery( '#fusion-builder-slider-actions' ).remove();
			jQuery( '#fusion-builder-ptb-actions' ).remove();

			// Re-init the buttons.
			self.init();

			// Add extra listeners for the slider partial-refreshes.
			jQuery( document.body ).on( 'fusion-partial-slider_type', function() {
				self.refreshButtons();
			} );
			_.each( avadaSlidersButtonsVars, function( sliderType ) {
				jQuery( document.body ).on( 'fusion-partial-' + sliderType, function() {
					self.refreshButtons();
				} );
			} );
		},

		/**
		 * Return the slider object.
		 * Allows us to accomodate all our slider-types.
		 *
		 * @since 6.0
		 * @return {Object} - Returns a jQuery object.
		 */
		getSlider: function() {
			var self       = this,
				sliderType = false;

			// Check for existing sliders.
			jQuery.each( avadaSlidersButtonsVars, function( type ) {
				var selector = avadaSlidersButtonsVars[ type ].sliderContainerSelector;
				if ( selector && jQuery( selector ) && jQuery( selector ).length ) {
					sliderType = type;

					// Slider found, exit the loop.
					return false;
				}
			} );

			// If no existing slider was detected, find one that is available.
			if ( ! sliderType ) {
				jQuery.each( avadaSlidersButtonsVars, function( type ) {
					if ( self.hasSliders( type ) ) {
						sliderType = type;

						// Slider found, exit the loop.
						return false;
					}
				} );
			}

			// Return the slider-type we found. Fallback to fusionSlider if none was found.
			return ( sliderType ) ? avadaSlidersButtonsVars[ sliderType ] : avadaSlidersButtonsVars.fusionSlider;
		},

		/**
		 * Check if there are sliders available for the defined slider-type.
		 *
		 * @since 6.0
		 * @param {string} type - The slider type.
		 * @return {boolean} - Whether there are sliders or not.
		 */
		hasSliders: function( type ) {

			// Check if we've got sliders.
			if (
				avadaSlidersButtonsVars[ type ] &&
				avadaSlidersButtonsVars[ type ].id &&
				window.parent.FusionApp &&
				window.parent.FusionApp.data &&
				window.parent.FusionApp.data.fusionPageOptions &&
				window.parent.FusionApp.data.fusionPageOptions.sliders &&
				window.parent.FusionApp.data.fusionPageOptions.sliders.fields &&
				window.parent.FusionApp.data.fusionPageOptions.sliders.fields[ avadaSlidersButtonsVars[ type ].id ] &&
				window.parent.FusionApp.data.fusionPageOptions.sliders.fields[ avadaSlidersButtonsVars[ type ].id ].choices &&
				1 < _.size( window.parent.FusionApp.data.fusionPageOptions.sliders.fields[ avadaSlidersButtonsVars[ type ].id ].choices )
			) {
				return true;
			}

			// Fallback to false.
			return false;
		},

		/**
		 * CSS Tweaks that take care of properly positioning the buttons.
		 *
		 * @since 6.0
		 * @return {void}
		 */
		cssTweaks: function() {
			var $headerElement = jQuery( 'body' ).hasClass( 'side-header' ) ? jQuery( '#side-header' ) : jQuery( '.fusion-header' );

			if ( this.theSlider() ) {
				$headerElement.find( '.add-slider' ).hide();
			} else {
				$headerElement.find( '.add-slider' ).show();
			}

			if ( ! jQuery( 'body' ).hasClass( 'avada-has-titlebar-hide' ) ) {
				$headerElement.find( '.add-ptb' ).hide();
			} else {
				$headerElement.find( '.add-ptb' ).show();
			}

			$headerElement.find( '.fusion-panel-shortcuts-wrapper .fusion-panel-shortcut' ).removeClass( 'fusion-shortcut-last' );
			$headerElement.find( '.fusion-panel-shortcuts-wrapper .fusion-panel-shortcut:visible:last' ).addClass( 'fusion-shortcut-last' );

			this.thePTBButtons().on( 'mouseenter', function() {
				var $pageTitleBar = jQuery( this ).closest( '.fusion-page-title-bar' );

				$pageTitleBar.data( 'original-z', $pageTitleBar.css( 'z-index' ) );
				$pageTitleBar.css( 'z-index', '15' );
			} );

			this.thePTBButtons().on( 'mouseleave', function() {
				var $pageTitleBar = jQuery( this ).closest( '.fusion-page-title-bar' ),
					zIndex        = '';

				if ( $pageTitleBar.data( 'original-z' ) ) {
					zIndex = $pageTitleBar.data( 'original-z' );
					$pageTitleBar.removeData( 'original-z' );
				}
				$pageTitleBar.css( 'z-index', zIndex );
			} );
		}
	};

	if ( 'undefined' !== typeof window.parent.FusionApp && 'undefined' === typeof window.parent.FusionApp.data.template_category ) {
		avadaPTBAndSlider.init();

		jQuery( window ).on( 'resize fusion-ptb-refreshed fusion-partial-slider_type fusion-partial-wooslider fusion-partial-fusion_tax_wooslider fusion-partial-slider fusion-partial-fusion_tax_slider fusion-partial-revslider fusion-partial-fusion_tax_revslider fusion-partial-elasticslider fusion-partial-fusion_tax_elasticslider', function() {

			// Ignore resize events as long as an actualResizeHandler execution is in the queue.
			if ( ! resizeTimeout ) {
				resizeTimeout = setTimeout( function() {
					resizeTimeout = null;
					avadaPTBAndSlider.refreshButtons();
				}, 60 );
			}
		} );
	} else if ( 'undefined' !== typeof window.parent.FusionApp && 'undefined' !== typeof window.parent.FusionApp.data.template_category && 'page_title_bar' === window.parent.FusionApp.data.template_category ) {
		jQuery( '.avada-footer-fx-parallax-effect .fusion-tb-page-title-bar' ).on( 'mouseenter', function() {
			var $pageTitleBar = jQuery( this ).closest( '.fusion-page-title-bar' );

			$pageTitleBar.data( 'original-z', $pageTitleBar.css( 'z-index' ) );
			$pageTitleBar.css( 'z-index', 'inherit' );
		} );

		jQuery( '.avada-footer-fx-parallax-effect .fusion-tb-page-title-bar' ).on( 'mouseleave', function() {
			var $pageTitleBar = jQuery( this ).closest( '.fusion-page-title-bar' ),
				zIndex        = '';

			if ( $pageTitleBar.data( 'original-z' ) ) {
				zIndex = $pageTitleBar.data( 'original-z' );
				$pageTitleBar.removeData( 'original-z' );
			}
			$pageTitleBar.css( 'z-index', zIndex );
		} );
	}

}( jQuery ) );

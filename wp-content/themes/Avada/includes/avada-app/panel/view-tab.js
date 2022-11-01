/* global FusionApp, fusionBuilderTabL10n, fusionAllElements, FusionEvents, fusionBuilderText, avadaPanelIFrame, awbTypographySelect */
/* jshint -W024 */
/* eslint max-depth: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	/**
	 * Builder Container View.
	 *
	 * @since 2.0.0
	 */
	FusionPageBuilder.TabView = Backbone.View.extend( {

		/**
		 * The template.
		 *
		 * @since 2.0.0
		 */
		template: FusionPageBuilder.template( jQuery( '#fusion-builder-tab-template' ).html() ),

		/**
		 * An object containing events and the method
		 * each one of them triggers.
		 *
		 * @since 2.0.0
		 */
		events: {
			'click .fusion-builder-go-back': 'showSections',
			'change input': 'optionChange',
			'fusion-change input': 'optionChange',
			'keyup input:not(.fusion-slider-input)': 'optionChange',
			'change select': 'optionChange',
			'keyup textarea': 'optionChange',
			'change textarea': 'optionChange',
			'click .upload-image-remove': 'removeImage',
			'click .option-preview-toggle': 'previewToggle',
			'click .fusion-panel-description': 'showHideDescription',
			'click .fusion-panel-shortcut': 'defaultPreview',
			'click .fusion-quick-option': 'quickOption',
			'click .option-has-responsive': 'showResponsiveOptions',
			'click .fusion-responsive-options li a': 'changeResponsiveOption',
			'mouseleave .fusion-builder-option': 'hideResponsiveOptions'
		},

		/**
		 * The class-name.
		 *
		 * @since 2.0.0
		 */
		className: 'fusion-builder-custom-tab',

		/**
		 * Initialization method.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {
			this.$el.attr( 'id', 'tab-' + this.model.get( 'id' ) );
			this.$el.attr( 'data-cid', this.model.get( 'cid' ) );
			this.$el.attr( 'data-type', this.model.get( 'type' ) );
			this._updatePreview  = _.debounce( _.bind( this.updatePreview, this ), 1000 );
			this._validateOption = _.debounce( _.bind( this.validateOption, this ), 1000 );
			this.options         = this.model.get( 'fields' );
			this.type            = this.model.get( 'type' );

			this.initialCheckDependencies();

			// Active states selected for element.
			this.activeStates     = {};
			this.$targetEl        = false;
			this._tempStateRemove = _.debounce( _.bind( this.tempStateRemove, this ), 3000 );
			this.hasSlug          = true;

			if ( 'import_export' === this.model.get( 'id' ) ) {
				this.listenTo( FusionEvents, 'fusion-to-changed', this.updateExportCode );
			}
			if ( 'import_export_po' === this.model.get( 'id' ) ) {
				this.listenTo( FusionEvents, 'fusion-po-changed', this.updateExportCode );
				this.listenTo( FusionEvents, 'fusion-ps-changed', this.updateExportCode );
			}
		},

		/**
		 * Render the model.
		 *
		 * @since 2.0.0
		 * @return {Object} this
		 */
		render: function() {
			this.$el.html( this.template( this.model.attributes ) );
			this.initOptions();
			FusionApp.sidebarView.$el.find( '.fusion-sidebar-section:visible' ).scrollTop( 0 );
			return this;
		},

		/**
		 * Navigates to a separate tab.
		 *
		 * @since 7.0
		 * @param object event The click event.
		 * @return {void}
		 */
		quickOption: function( event ) {
			var $trigger = jQuery( event.target );

			event.preventDefault();

			FusionApp.sidebarView.openOption( $trigger.data( 'fusion-option' ) );
		},

		/**
		 * Show tab.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		showTab: function() {
			this.$el.show();
			FusionApp.sidebarView.$el.find( '.fusion-sidebar-section:visible' ).scrollTop( 0 );
		},

		/**
		 * Checks the dependencies for this tab.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialCheckDependencies: function() {
			var self = this;

			// Initialize option dependencies
			setTimeout( function() {

				// Only check dependencies when global option or page option.
				// Ignore dependencies on search.
				if ( 'TO' === self.type || 'PO' === self.type || 'FBE' === self.type ) {
					self.dependencies = new FusionPageBuilder.Dependencies( self.options, self );
				}
			}, 10 );
		},

		/**
		 * Trigger actions when an option changes.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The JS event.
		 * @return {void}
		 */
		optionChange: function( event ) {

			// Validation.
			var result = this.validateOption( event ); // jshint ignore:line

			if ( result ) {
				if ( this.needsDebounce( event ) ) {
					this._updatePreview( event );
				} else {
					this.updatePreview( event );
				}
			}
		},

		/**
		 * Removes tab.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		removeTab: function() {

			// Remove view from manager.
			FusionApp.sidebarView.viewManager.removeView( this.model.get( 'cid' ) );

			this.remove();
		},

		showHideDescription: function( event ) {
			var $element = jQuery( event.currentTarget );
			var $tooltip = $element.closest( '.fusion-builder-option' ).find( '.fusion-tooltip-description' );
			var $text    = $tooltip.text();

			$element.closest( '.fusion-builder-option' ).find( '.description' ).first().slideToggle( 250 );
			$tooltip.text( $text === fusionBuilderText.fusion_panel_desciption_show ? fusionBuilderText.fusion_panel_desciption_hide : fusionBuilderText.fusion_panel_desciption_show );
			$element.toggleClass( 'active' );
		},

		defaultPreview: function( event ) {
			var $element = jQuery( event.currentTarget );

			if ( event ) {
				event.preventDefault();
			}

			if ( FusionApp.sidebarView ) {
				jQuery( '.fusion-builder-toggles a' ).first().trigger( 'click' );
				FusionApp.sidebarView.openOption( $element.data( 'fusion-option' ), 'to' );
			}
		},

		/**
		 * Initialize the options.
		 *
		 * @since 2.0.0
		 * @param {Object} $element - The jQuery element.
		 * @return {void}
		 */
		initOptions: function( $element ) {
			var $thisEl = 'undefined' !== typeof $element && $element.length ? $element : this.$el;

			this.optionColorpicker( $thisEl );
			this.optionRadioButtonSet( $thisEl );
			this.optionCheckboxButtonSet( $thisEl );
			this.optionDimension( $thisEl );
			this.optionSelect( $thisEl );
			this.optionAjaxSelect( $thisEl );
			this.optionMultiSelect();
			this.optionRange( $thisEl );
			this.optionUpload( $thisEl );
			this.optionMultiUpload( $thisEl );
			this.optionCodeBlock( $thisEl );
			this.optionTypography( $thisEl );
			this.optionTypographySets( $thisEl );
			this.optionSwitch( $thisEl );
			this.optionImport( $thisEl );
			this.optionExport( $thisEl );
			this.optionSortable( $thisEl );
			this.optionColorPalette( $thisEl );
			this.optionRaw( $thisEl );
			this.optionLinkSelector( $thisEl );
			this.optionHubSpotMap( $thisEl );
			this.optionMailchimpMap( $thisEl );
			this.optionIconpicker( $thisEl );
			this.optionLayoutConditions( $thisEl );

			if ( 'undefined' === typeof $element ) {
				this.optionRepeater( this.type );
				this.optionToggle( this.type );
			}
		},

		/**
		 * Checks if option update should use debounce.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The JS event.
		 * @return {void}
		 */
		needsDebounce: function( event ) {
			var option     = jQuery( event.currentTarget ).closest( '.fusion-builder-option' ),
				id         = option.data( 'option-id' ),
				fields     = this.model.get( 'fields' ),
				field      = fields[ id ],
				debouncers = [ 'primary_color' ];

			if ( 'undefined' === typeof field && option.parent().hasClass( 'repeater-fields' ) ) {
				id    = option.parent().closest( '.fusion-builder-option' ).data( 'option-id' );
				field = fields[ id ];
			}

			if ( debouncers.includes( id ) ) {
				return true;
			}
			if ( 'undefined' !== typeof field && ( 'undefined' !== typeof field.output || 'undefined' !== typeof field.css_vars || ( 'undefined' !== typeof field.transport && 'postMessage' === field.transport ) ) ) {
				return false;
			}
			if ( 'undefined' !== typeof field && ( 'select' === field.type || 'typography' === field.type ) ) {
				return false;
			}
			return true;
		},

		/**
		 * Handles switching between the
		 * theme-options, page-options and search views.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The JS event.
		 * @return {void}
		 */
		showSections: function( event ) {
			var context,
				$section = this.$el.closest( '.fusion-sidebar-section' );

			if ( event ) {
				event.preventDefault();
			}

			if ( 'search' === this.model.get( 'type' ) ) {
				context = this.model.get( 'context' );
				if ( 'PO' === context || 'PS' === context ) {
					FusionApp.sidebarView.setActiveTab( 'po', context );
					jQuery( '#fusion-builder-sections-po .fusion-panels' ).show();
				} else {
					FusionApp.sidebarView.setActiveTab( 'to', context );
					jQuery( '#fusion-builder-sections-to .fusion-panels' ).show();
				}
				FusionApp.sidebarView.clearSearch();
			} else if ( 'TO' === this.model.get( 'type' ) ) {
				jQuery( '#fusion-builder-sections-to .fusion-panels' ).show();
			} else if ( 'FBE' === this.model.get( 'type' ) ) {
				FusionApp.sidebarView.switchActiveContext( '#fusion-builder-sections-to', 'FBE' );
				$section.find( '.fusion-tabs' ).hide();
				$section.find( '.fusion-panels' ).show();
			} else if ( 'FBAO' === this.model.get( 'type' ) ) {
				FusionApp.sidebarView.switchActiveContext( '#fusion-builder-sections-to', 'FBAO' );
				$section.find( '.fusion-tabs' ).hide();
				$section.find( '.fusion-panels' ).show();
			} else {
				jQuery( '#fusion-builder-sections-po .fusion-panels' ).show();
			}
			this.$el.closest( '.fusion-tabs' ).hide();

			// Remove view since it is always recreated anyway.
			if ( 'fusion-builder-results' === this.model.get( 'id' ) ) {
				this.remove();
			} else {
				FusionApp.sidebarView.scrollToElement( FusionApp.sidebarView.$el.find( 'a#' + this.model.get( 'id' ) ).closest( '.fusion-builder-custom-panel' ), false );
			}
		},

		/**
		 * Gets value if not regular.
		 *
		 * @since 2.0.0
		 * @param {Object} $target - jQuery object.
		 * @param {Object} event - The JS event.
		 * @param {mixed} value - The value.
		 * @return {mixed} - Returns value.
		 */
		getValue: function( $target, event, value ) {
			var $realInput;

			// Tweak for multi selects.
			if ( 'checkbox' === $target.attr( 'type' ) && $target.hasClass( 'fusion-select-option' ) ) {
				value = [];
				_.each( $target.parent().find( '.fusion-select-option:checked' ), function( selectedOption ) {
					value.push( jQuery( selectedOption ).val() );
				} );
				return value;
			}

			// Tweak for checkboxes.
			if ( 'checkbox' === $target.attr( 'type' ) ) {
				return $target.is( ':checked' ) ? '1' : '0';
			}

			// Changed URL preview of upload object, update object only.
			if ( $target.hasClass( 'fusion-url-only-input' ) ) {
				$realInput = $target.closest( '.fusion-upload-area' ).find( '.fusion-image-as-object' );
				if ( $realInput.length ) {
					$realInput.val( JSON.stringify( { url: value } ) ).trigger( 'change' );
				}
			}

			// If code block element then need to use method to get val.
			if ( jQuery( event.currentTarget ).parents( '.fusion-builder-option.code' ).length ) {
				return this.codeEditorOption[ jQuery( event.currentTarget ).parents( '.fusion-builder-option.code' ).attr( 'data-index' ) ].getValue();
			}

			// Slider with default.
			if ( $target.hasClass( 'fusion-with-default' ) ) {
				value = $target.parents( '.fusion-builder-option' ).find( '.fusion-hidden-value' ).val();
				value = value || '';
			}

			// Repeater value.
			if ( $target.hasClass( 'fusion-repeater-value' ) && '' !== value ) {
				value = JSON.parse( value );
			}

			return value;
		},

		/**
		 * Checks whether we need to update or not.
		 *
		 * @since 2.0.0
		 * @param {Object} $target - jQuery object.
		 * @param {mixed} value - The value.
		 * @param {string} id - The control ID.
		 * @param {Object} save - The saved data.
		 * @return {void}
		 */
		needsUpdate: function( $target, value, id, save ) {
			var parts,
				base,
				optionName;

			// If value hasn't changed.
			if ( value === save[ id ] ) {
				return false;
			}

			// If its a file upload for import.
			if ( $target.hasClass( 'fusion-dont-update' ) || $target.hasClass( 'fusion-import-file-input' ) || 'demo_import' === id ) {
				return false;
			}

			// Repeater value being changed, trigger on parent only.
			if ( $target.parents( '.fusion-builder-option.repeater' ).length && ! $target.hasClass( 'fusion-repeater-value' ) ) {
				if ( $target.hasClass( 'fusion-image-as-object' ) ) {
					if ( 'undefined' === typeof value || '' === value ) {
						value = {
							url: ''
						};
					} else {
						value = JSON.parse( value );
					}
				}
				this.setRepeaterValue( $target.parents( '.fusion-builder-option.repeater' ).find( '.fusion-repeater-value' ), id, $target.parents( '.repeater-row' ).index(), value );
				return false;
			}

			// If value is empty and option doesn't exist (PO).
			if ( '' === value && _.isUndefined( save[ id ] ) && _.isUndefined( save[ id ] ) ) {
				return false;
			}

			// If it's a colorpicker that hasn't been instantiated yet or it's color palette, early exit.
			if ( ( $target && $target.hasClass( 'color-picker' ) && ! $target.hasClass( 'fusion-color-created' ) ) || $target.hasClass( 'fusion-color-palette-color-picker' ) ) {
				return false;
			}

			if ( _.isObject( save[ id ] ) && $target.parents( '.fusion-builder-dimension' ).length && value === save[ id ][ $target.attr( 'name' ) ] ) {
				return false;
			}

			if ( _.isObject( save[ id ] ) && ( $target.closest( '.fusion-builder-option.typography' ).length || $target.closest( '.fusion-builder-option.typography-sets' ).length ) ) {

				if ( -1 !== $target.attr( 'name' ).indexOf( '[' ) ) {

					// Split the key in parts.
					parts = $target.attr( 'name' ).split( '[' );

					// Set the option base.
					base = save[ parts.shift().replace( ']', '' ) ];

					optionName = parts.pop().replace( ']', '' );

					// if we still have extra parts, add them.
					if ( parts.length ) {
						_.each( parts, function( part ) {
							part = part.replace( ']', '' );

							if ( 'undefined' === typeof base[ part ] ) {
								base[ part ] = {};
							}
							base = base[ part ];
						} );
					}
				}

				if ( 'undefined' !== typeof base[ optionName ] &&  value === base[ optionName ] ) {
					return false;
				}
			}

			return true;
		},

		/**
		 * Saves the change.
		 *
		 * @since 2.0.0
		 * @param {Object} $target - jQuery object.
		 * @param {mixed} value - The value.
		 * @param {string} id - The setting ID.
		 * @param {Object} save - Saved data.
		 * @param {string} type - TO/FBE etc.
		 * @return {void}
		 */
		saveChange: function( $target, value, id, save, type ) {
			var parts,
				base,
				optionName;

			// Update the settings object.
			if ( $target.closest( '.fusion-builder-option' ).hasClass( 'typography' ) || $target.closest( '.fusion-builder-option' ).hasClass( 'typography-sets' ) || (
				( _.isObject( save[ id ] ) || _.isUndefined( save[ id ] ) ) && (
				$target.parents( '.fusion-builder-dimension' ).length ||
				$target.parents( '.fusion-builder-repeater' ).length
			) ) ) {

				if ( _.isUndefined( save[ id ] ) ) {
					save[ id ] = {};
				}

				if ( -1 !== $target.attr( 'name' ).indexOf( '[' ) ) {

					// Split the key in parts.
					parts = $target.attr( 'name' ).split( '[' );

					// Set the option base.
					base = save[ parts.shift().replace( ']', '' ) ];

					// Se the final option name.
					optionName = parts.pop().replace( ']', '' );

					// if we still have extra parts, add them.
					if ( parts.length ) {
						_.each( parts, function( part ) {
							part = part.replace( ']', '' );

							if ( 'undefined' === typeof base[ part ] ) {
								base[ part ] = {};
							}
							base = base[ part ];
						} );
					}

					if ( 'variant' === optionName ) {
						if ( base[ 'font-weight' ] === awbTypographySelect.getFontWeightFromVariant( value ) && base[ 'font-style' ] === awbTypographySelect.getFontStyleFromVariant( value ) ) {
							return;
						}
						base.variant = value;
						base[ 'font-weight' ] = awbTypographySelect.getFontWeightFromVariant( value );
						base[ 'font-style' ]  = awbTypographySelect.getFontStyleFromVariant( value );
					} else {
						base[ optionName ] = value;
					}
				} else {
					save[ id ][ $target.attr( 'name' ) ] = value;
				}

			} else if ( $target.hasClass( 'fusion-image-as-object' ) || $target.hasClass( 'awb-palette-save' ) ) {
				value = JSON.parse( value );
				save[ id ] = value;
			} else {
				save[ id ] = value;
			}

			// Trigger relevant content change event.
			if ( 'undefined' !== typeof FusionApp.contentChange ) {
				if ( 'TO' === type || 'FBE' === type ) {
					FusionEvents.trigger( 'fusion-to-changed' );
					FusionEvents.trigger( 'fusion-to-' + id + '-changed' );
					FusionApp.contentChange( 'global', 'theme-option' );
					window.dispatchEvent( new Event( 'fusion-to-' + id + '-changed' ) );
				} else if ( 'PO' === type || 'TAXO' === type ) {
					FusionEvents.trigger( 'fusion-po-changed' );
					FusionEvents.trigger( 'fusion-po-' + id + '-changed' );
					FusionApp.contentChange( 'page', 'page-option' );
					window.dispatchEvent( new Event( 'fusion-po-' + id + '-changed' ) );
				} else if ( 'PS' === type ) {
					FusionEvents.trigger( 'fusion-ps-changed' );
					FusionEvents.trigger( 'fusion-' + id + '-changed' );
					FusionApp.contentChange( 'page', 'page-setting' );
					window.dispatchEvent( new Event( 'fusion-page-' + id + '-changed' ) );
				}
			}
		},

		/**
		 * Get save id.
		 *
		 * @since 2.0.0
		 * @param {Object} $target - jQuery object.
		 * @param {string} id - The setting ID.
		 * @param {string} type - TO/FBE etc.
		 * @return {void}
		 */
		getSaveId: function( $target, id, type ) {
			var fields = this.model.get( 'fields' );
			if ( 'PO' === type ) {
				if ( $target.hasClass( 'fusion-po-dimension' ) ) {
					return $target.attr( 'id' );
				} else if ( ( ! _.isUndefined( fields[ id ] ) && _.isUndefined( fields[ id ].not_pyre ) ) && FusionApp.data.singular ) {
					return id;
				}
			}
			return id;
		},

		getSaveLocation: function( type, id ) {
			switch ( type ) {
				case 'FBE':
				case 'TO':
					return FusionApp.settings;

				case 'PS':
					return FusionApp.data.postDetails;

				case 'PO':
				case 'TAXO':
					if ( '_wp_page_template' === id || '_thumbnail_id' === id || '_fusion_builder_custom_css' === id ) {
						return FusionApp.data.postMeta;
					}
					FusionApp.data.postMeta._fusion = FusionApp.data.postMeta._fusion || {};
					return FusionApp.data.postMeta._fusion;
			}
		},

		/**
		 * Updates the preview iframe.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event triggering the update.
		 * @param {boolean} forced - If set to true, then skips the needsUpdate check.
		 * @param {Array} alreadyTriggeredFields - An array of fields that have already been triggered.
		 *                                         Avoid infinite loops in case of fields inter-dependencies.
		 * @return {void}
		 */
		updatePreview: function( event, forced, alreadyTriggeredFields ) {
			var self    = this,
				$target = jQuery( event.currentTarget ),
				$option = $target.parents( '.fusion-builder-option' ),
				id      = $option.data( 'option-id' ),
				value   = $target.val(),
				type    = $option.data( 'type' ),
				save    = this.getSaveLocation( type, id ),
				preview = $option.find( '.option-preview-toggle' ).length,
				updated = false,
				saveId  = this.getSaveId( $target, id, type ),
				fields  = this.model.get( 'fields' );

			value = this.getValue( $target, event, value );

			if ( true !== forced && ! this.needsUpdate( $target, value, saveId, save ) ) {
				return;
			}

			this.saveChange( $target, value, saveId, save, type );

			if ( 'TO' === type || 'FBE' === type || 'PO' === type ) {
				FusionApp.createMapObjects();
				if ( 'object' === typeof save[ saveId ] ) {
					this.updateSettingsToParams( id + '[' + $target.attr( 'name' ) + ']', value );
					this.updateSettingsToExtras( id + '[' + $target.attr( 'name' ) + ']', value );
					this.updateSettingsToParams( id, save[ saveId ] );
					this.updateSettingsToExtras( id, save[ saveId ] );
					if ( 'TO' === type || 'FBE' === type ) {
						this.updateSettingsToPo( id, save[ saveId ] );
					}
				} else {
					this.updateSettingsToParams( id, value );
					this.updateSettingsToExtras( id, value );
					if ( 'TO' === type || 'FBE' === type ) {
						this.updateSettingsToPo( id, value );
					}
				}

				FusionEvents.trigger( 'fusion-preview-update', id, value );
			}

			// Custom events defined.
			if ( fields[ id ] && 'object' === typeof fields[ id ].events ) {
				_.each( fields[ id ].events, function( customEvent ) {
					FusionEvents.trigger( customEvent, id, value );
				} );
			}

			// Check update_callback args.
			if ( false === this.checkUpdateCallbacks( fields[ id ] ) ) {
				return;
			}

			if ( 'post_title' === id ) {
				this.maybeUpdateSlug( value );
			}

			// Early exit if it is multi select add new field.
			if ( 'undefined' !== typeof jQuery( event.currentTarget ).attr( 'class' ) && 'fusion-multiselect-input' === jQuery( event.currentTarget ).attr( 'class' ) ) {
				return;
			}

			// Early exit if we don't have a field.
			if ( ! fields[ id ] ) {
				return;
			}

			// Check how to update preview. partial_refresh, output;
			if ( fields[ id ].id && 'color_palette' === fields[ id ].id ) {

				// No need to update preview.
				return;
			}

			// Check how to update preview. partial_refresh, output;
			if ( fields[ id ].id && 'custom_css' === fields[ id ].id ) {
				avadaPanelIFrame.liveUpdateCustomCSS( value );
				return;
			}

			if ( fields[ id ].id && '_fusion_builder_custom_css' === fields[ id ].id ) {
				avadaPanelIFrame.liveUpdatePageCustomCSS( value );
				return;
			}

			if ( fields[ id ].id && ( 'visibility_small' === fields[ id ].id || 'visibility_medium' === fields[ id ].id ) ) {
				jQuery( '#fusion-parent-window-css-vars' ).html( ':root{--small_screen_width:' + FusionApp.settings.visibility_small + 'px;}:root{--medium_screen_width:' + FusionApp.settings.visibility_medium + 'px;}' );
				return;
			}

			if ( fields[ id ].output || fields[ id ].css_vars ) {

				// Apply any functions defined in js_callback.
				if ( false !== avadaPanelIFrame.applyRefreshCallbacks( fields[ id ].css_vars, value ) ) {

					// Trigger temporary active state if exists.
					if ( preview ) {
						this.triggerTemporaryState( $option );
					}

					// Live update.
					avadaPanelIFrame.generateCSS( saveId, fields[ id ].output, fields[ id ].css_vars, type, preview, fields[ id ].type );

					// Handle hard-coded output_fields_trigger_change.
					if ( ! _.isUndefined( fields[ id ].output_fields_trigger_change ) ) {
						if ( ! alreadyTriggeredFields ) {
							alreadyTriggeredFields = [ id ];
						}
						_.each( fields[ id ].output_fields_trigger_change, function( triggerFieldID ) {
							if ( -1 === alreadyTriggeredFields.indexOf( triggerFieldID ) ) {
								alreadyTriggeredFields.push( triggerFieldID );
								self.updatePreview( {
									currentTarget: jQuery( '.fusion-builder-option[data-option-id="' + triggerFieldID + '"] input' )
								}, true, alreadyTriggeredFields );
							}
						} );
					}

					// Handle output-dependencies.
					avadaPanelIFrame.populateFieldOutputDependencies();
					if ( avadaPanelIFrame.fieldOutputDependencies[ id ] ) {
						if ( ! alreadyTriggeredFields ) {
							alreadyTriggeredFields = [ id ];
						}
						_.each( avadaPanelIFrame.fieldOutputDependencies[ id ], function( triggerFieldID ) {
							if ( -1 === alreadyTriggeredFields.indexOf( triggerFieldID ) ) {
								alreadyTriggeredFields.push( triggerFieldID );
								self.updatePreview( {
									currentTarget: jQuery( '.fusion-builder-option[data-option-id="' + triggerFieldID + '"] input' )
								}, true, alreadyTriggeredFields );
							}
						} );
					}
					updated = true;
				}
			}

			if ( ! _.isUndefined( fields[ id ].partial_refresh ) && ! _.isEmpty( fields[ id ].partial_refresh ) ) {

				// Apply any functions defined in js_callback.
				if ( false !== avadaPanelIFrame.applyRefreshCallbacks( fields[ id ].partial_refresh, value ) ) {

					// Partial refresh.
					avadaPanelIFrame.partialRefresh( saveId, fields[ id ].partial_refresh, value, this.model.get( 'cid' ) );
					updated = true;
				}
			}

			if ( ! _.isUndefined( fields[ id ].transport ) && 'postMessage' === fields[ id ].transport ) {
				updated = true;
				FusionEvents.trigger( 'fusion-postMessage-' + id );
				window.dispatchEvent( new Event( 'fusion-postMessage-' + id ) );
			}

			if ( ! updated || ( ! _.isUndefined( fields[ id ].transport ) && 'refresh' === fields[ id ].transport ) ) {

				if ( false !== avadaPanelIFrame.applyRefreshCallbacks( fields[ id ].full_refresh, value ) ) {

					// Full refresh.
					$option.addClass( 'full-refresh-active' );
					FusionApp.fullRefresh();
					FusionEvents.once( 'fusion-app-setup', function() {
						$option.removeClass( 'full-refresh-active' );
					} );
				}
			}
		},

		maybeUpdateSlug: function( value ) {
			var from,
				to,
				$input = this.$el.find( '#post_name' ),
				i,
				l;

			if ( ! $input.length ) {
				return;
			}

			if ( ! $input.val() || '' === $input.val() ) {
				this.hasSlug = false;
			}

			if ( ! value || '' === value || this.hasSlug ) {
				return;
			}

			value = value.replace( /^\s+|\s+$/g, '' ).toLowerCase(),
			from  = 'àáäâèéëêìíïîòóöôùúüûñç·/_,:;',
			to    = 'aaaaeeeeiiiioooouuuunc------';

			for ( i = 0, l = from.length; i < l; i++ ) {
				value = value.replace( new RegExp( from.charAt( i ), 'g' ), to.charAt( i ) );
			}
			value = value.replace( '.', '-' ).replace( /[^a-z0-9 -]/g, '' ).replace( /\s+/g, '-' ).replace( /-+/g, '-' );
			$input.val( value ).trigger( 'change' );
		},

		/**
		 * Clones a value.
		 * Used to avoid JS references.
		 *
		 * @param {mixed} value - A value. Can be anything.
		 * @return {mixed} - Returns the value.
		 */
		cloneValue: function( value ) {
			return value;
		},

		/**
		 * Update settings (TO) and trigger update.
		 *
		 * @since 2.0.0
		 * @param {string} id - The setting-ID.
		 * @param {mixed} value - The value.
		 * @param {boolean} skipRender - Whether we should skip render or not.
		 * @return {void}
		 */
		updateSettingsToParams: function( id, value, skipRender ) {
			var self         = this,
				initialValue = self.cloneValue( value ),
				$target,
				defaultText,
				args,
				type;

			skipRender = 'undefined' === typeof skipRender ? false : skipRender;

			if ( _.isUndefined( FusionApp.settingsToParams[ id ] ) ) {
				return;
			}
			_.each( FusionApp.settingsToParams[ id ], function( rule ) {

				if ( ! _.isUndefined( fusionAllElements[ rule.element ] ) ) {
					if ( rule.callback ) {

						args = {
							id: id,
							type: self.model.get( 'type' )
						};
						value = avadaPanelIFrame.applyCallback( initialValue, rule.callback, args );
					}

					// Update default for element render.
					fusionAllElements[ rule.element ].defaults[ rule.param ] = value;

					if ( ! _.isUndefined( fusionAllElements[ rule.element ].params[ rule.param ] ) && ! _.isUndefined( fusionAllElements[ rule.element ].params[ rule.param ][ 'default' ] ) ) {

						// Only option that uses visual default value should update.
						if ( 'colorpickeralpha' === fusionAllElements[ rule.element ].params[ rule.param ].type || 'color' === fusionAllElements[ rule.element ].params[ rule.param ].type || 'range' === fusionAllElements[ rule.element ].params[ rule.param ].type ) {
							fusionAllElements[ rule.element ].params[ rule.param ][ 'default' ] = value;
							$target = jQuery( '.' + rule.element + ' [data-option-id="' + rule.param + '"]' );

							if ( 1 === $target.length ) {
								FusionEvents.trigger( 'fusion-default-changed-' + $target.closest( '.fusion-builder-module-settings' ).attr( 'data-element-cid' ), rule.param, value );
							}
						}

						// Update the default text value if open.
						if ( jQuery( '.description [data-fusion-option="' + id + '"]' ).length ) {
							type        = jQuery( '.description [data-fusion-option="' + id + '"]' ).closest( '.fusion-builder-option' ).attr( 'class' ).split( ' ' ).pop();
							defaultText = FusionApp.sidebarView.fixToValueName( id, value, type );
							jQuery( '.description [data-fusion-option="' + id + '"]' ).html( defaultText );
						}
					}

					FusionEvents.trigger( 'fusion-param-default-update-' + rule.param, value );

					// Update default for color picker/range.
					if ( ! skipRender ) {

						// Make sure that element type re-renders.
						FusionEvents.trigger( 'fusion-global-update-' + rule.element, rule.param, value );
						self.triggerActiveStates();
					}
				}
			} );
		},

		/**
		 * Update builder element extras (TO) and trigger update.
		 *
		 * @since 2.0.0
		 * @param {string} id - The setting-ID.
		 * @param {mixed} value - The value.
		 * @param {boolean} skipRender - Whether we should skip render or not.
		 * @return {void}
		 */
		updateSettingsToExtras: function( id, value, skipRender ) {
			var self         = this,
				initialValue = self.cloneValue( value );

			if ( 'object' !== typeof FusionApp.settingsToExtras || _.isUndefined( FusionApp.settingsToExtras[ id ] ) ) {
				return;
			}

			skipRender = 'undefined' === typeof skipRender ? false : skipRender;

			_.each( FusionApp.settingsToExtras[ id ], function( rule ) {

				if ( ! _.isUndefined( fusionAllElements[ rule.element ] ) ) {
					if ( rule.callback ) {
						value = avadaPanelIFrame.applyCallback( initialValue, rule.callback, false );
					}

					// Update extra for element render.
					fusionAllElements[ rule.element ].extras[ rule.param ] = value;

					// Make sure that element type re-renders.
					if ( ! skipRender ) {
						FusionEvents.trigger( 'fusion-extra-update-' + rule.element, rule.param, value );
						self.triggerActiveStates();
					}
				}
			} );
		},

		/**
		 * Update settings (PO).
		 *
		 * @since 2.0.0
		 * @param {string} id - The setting-ID.
		 * @param {mixed}  value - The value.
		 * @return {void}
		 */
		updateSettingsToPo: function( id, value ) {
			var initialValue = this.cloneValue( value ),
				option;

			if ( _.isUndefined( FusionApp.settingsToPo[ id ] ) || _.isUndefined( FusionApp.data.fusionPageOptions ) || _.isEmpty( FusionApp.data.fusionPageOptions ) ) {
				return;
			}
			_.each( FusionApp.settingsToPo[ id ], function( rule ) {
				if ( ! _.isUndefined( FusionApp.data.fusionPageOptions[ rule.tab ] ) && ! _.isUndefined( FusionApp.data.fusionPageOptions[ rule.tab ].fields[ rule.option ] ) ) {

					option = FusionApp.data.fusionPageOptions[ rule.tab ].fields[ rule.option ];

					if ( rule.callback ) {
						value = avadaPanelIFrame.applyCallback( initialValue, rule.callback, false );
					}

					// Remove relevant tab.
					FusionApp.sidebarView.clearTabs( 'po', false, rule.option );

					// Only update option types which use a TO as default, not a hardcoded string.
					if ( 'color-alpha' === option.type || 'color' === option.type  || 'slider' === option.type || 'sortable' === option.type ) {
						option[ 'default' ] = value;
					}
				}
			} );
		},

		/**
		 * Handle validation calls for option changes.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The jQuery event.
		 * @return {boolean} - If the vadidation succeeded or failed.
		 */
		validateOption: function( event ) {
			var $target   = jQuery( event.currentTarget ),
				value     = $target.val(),
				$optionEl = $target.parents( '.fusion-builder-option' ),
				id        = $optionEl.data( 'option-id' ),
				valid     = true,
				message   = '';

			if ( $target.hasClass( 'awb-ignore' ) ) {
				return;
			}

			if ( 'checkbox' === ( $target ).attr( 'type' ) ) {
				value = $target.is( ':checked' ) ? '1' : '0';
			}

			// Do not validate variable within value.
			if ( 'string' === typeof value && -1 !== value.indexOf( 'var(' ) ) {
				FusionApp.validate.message( 'remove', id, $target );
				return true;
			}
			if ( $optionEl.hasClass( 'spacing' ) || $optionEl.hasClass( 'dimension' ) ) {
				valid   = FusionApp.validate.cssValue( value );
				message = fusionBuilderTabL10n.invalidCssValue;
			} else if ( $optionEl.hasClass( 'color' ) ) {
				valid   = FusionApp.validate.validateColor( value, 'hex' );
				message = fusionBuilderTabL10n.invalidColor;
			} else if ( $optionEl.hasClass( 'color-alpha' ) ) {
				valid   = FusionApp.validate.validateColor( value );
				message = fusionBuilderTabL10n.invalidColor;
			} else if ( $optionEl.hasClass( 'typography' ) ) {
				if ( 'font-size' === $target.attr( 'name' ) ) {
					valid   = FusionApp.validate.cssValue( value );
					message = fusionBuilderTabL10n.invalidCssValueVar.replace( '%s', $target.attr( 'name' ) );
				} else if ( 'line-height' === $target.attr( 'name' ) || 'letter-spacing' === $target.attr( 'name' ) ) {
					valid = FusionApp.validate.cssValue( value, true );
					message = fusionBuilderTabL10n.invalidCssValueVar.replace( '%s', $target.attr( 'name' ) );
				}
			}

			if ( false === valid ) {
				FusionApp.validate.message( 'add', id, $target, message );
				return false;
			}
			FusionApp.validate.message( 'remove', id, $target );
			return true;
		},

		/**
		 * Show responsive options.
		 *
		 * @param {Object} event
		 */
		showResponsiveOptions: function( event ) {
			var $element = jQuery( event.currentTarget ).parent();

			$element.toggleClass( 'active-item' );
		},

		/**
		 * Change responsive option.
		 *
		 * @param {Object} event
		 */
		changeResponsiveOption: function( event ) {
			var $element   = jQuery( event.currentTarget );
			var $parent    = jQuery( event.currentTarget ).closest( 'li.active-item' );

			jQuery( '.fusion-builder-preview-' + $element.data( 'indicator' ) ).trigger( 'click' );
			$parent.removeClass( 'active-item' );

		},

		/**
		 * Hide responsive option.
		 *
		 * @param {Object} event
		 */
		hideResponsiveOptions: function( event ) {
			var $element   = jQuery( event.currentTarget );

			$element.find( '.fusion-panel-options li.active-item' ).removeClass( 'active-item' );
		},

		/**
		 * Check update_callback arguments and return true|false
		 * depending on the context of the preview pane.
		 *
		 * @param {Object} field
		 * @return {boolean} - If we should update or not.
		 */
		checkUpdateCallbacks: function( field ) {
			var result   = true,
				results  = [],
				subCheck = false;

			if ( field && field.id && field.update_callback ) {
				_.each( field.update_callback, function( updateCallback ) {
					var where;
					if ( updateCallback.operator ) {

						// 1st level chacks are AND.
						where = updateCallback.where ? FusionApp.data[ updateCallback.where ] : FusionApp.data;
						switch ( updateCallback.operator ) {
						case '===':
							if ( where[ updateCallback.condition ] !== updateCallback.value ) {
								results.push( false );
							}
							break;
						case '!==':
							if ( where[ updateCallback.condition ] === updateCallback.value ) {
								results.push( false );
							}
							break;
						}
					} else {

						// Nested checks function as OR conditions.
						_.each( updateCallback, function( subCallback ) {
							if ( subCallback.operator ) {
								where = subCallback.where ? FusionApp.data[ subCallback.where ] : FusionApp.data;
								switch ( subCallback.operator ) {
								case '===':
									if ( where[ subCallback.condition ] === subCallback.value ) {
										subCheck = true;
									}
									break;
								case '!==':
									if ( where[ subCallback.condition ] !== subCallback.value ) {
										subCheck = true;
									}
									break;
								}
							}
						} );
						results.push( subCheck );
					}
				} );
			}
			_.each( results, function( subResult ) {
				if ( ! subResult ) {
					result = false;
				}
			} );
			return result;
		}

	} );

	// Options
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionTypographyField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionTypographySetsField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionCodeBlock );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionColorPicker );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionDimensionField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionOptionUpload );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.radioButtonSet );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionCheckboxButtonSet );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionRangeField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionRepeaterField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionSelectField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionAjaxSelect );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionMultiSelect );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionSwitchField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionImportUpload );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionExport );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionSortable );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionColorPalette );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionRawField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionLinkSelector );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionHubSpotMap );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionMailchimpMap );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionIconPicker );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionLayoutConditions );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionToggleField );

	// Active states.
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.fusionActiveStates );

}( jQuery ) );

/* global FusionApp, FusionPageBuilderViewManager, FusionEvents, fusionAllElements, FusionPageBuilderApp, fusionBuilderText, fusionGlobalManager, fusionBuilderInsertIntoEditor, openShortcodeGenerator, awbPalette */
/* eslint no-unused-vars: 0 */
/* eslint no-alert: 0 */
/* eslint no-empty-function: 0 */
/* eslint no-shadow: 0 */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.ElementSettingsView = window.wp.Backbone.View.extend( {

		className: 'fusion_builder_module_settings',
		template: FusionPageBuilder.template( jQuery( '#fusion-builder-block-module-settings-template' ).html() ),
		optionHasChanged: false,

		events: {

			'click [id$="fusion_shortcodes_text_mode"]': 'activateSCgenerator',
			'change input': 'optionChange',
			'keyup input:not(.fusion-slider-input)': 'optionChange',
			'change select': 'optionChange',
			'keyup textarea': 'optionChange',
			'change textarea': 'optionChange',
			'paste textarea': 'optionChangePaste',
			'fusion-change input': 'optionChange',
			'click .upload-image-remove': 'removeImage',
			'click .option-preview-toggle': 'previewToggle',
			'click .insert-slider-video': 'addSliderVideo',
			'click .fusion-panel-shortcut:not(.dialog-more-menu-item)': 'defaultPreview',
			'click .fusion-panel-description': 'showHideDescription',
			'click #fusion-close-element-settings': 'saveSettings',
			'click .fusion-builder-go-back': 'openParent',
			'click .option-dynamic-content': 'addDynamicContent',
			'click .option-has-responsive': 'showResponsiveOptions',
			'click .fusion-responsive-options li a': 'changeResponsiveOption',
			'mouseleave .fusion-builder-option': 'hideResponsiveOptions',
			'click .fusion-builder-toggle-subgroup': 'toggleSubGroup'
		},

		/**
		 * Init.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {

			// Manipulate model attributes via custom function if provided by the element.
			this.onSettingsCallback();

			// Store element view.
			this.elementView = FusionPageBuilderViewManager.getView( this.model.get( 'cid' ) );

			this.loadComplete     = false;
			this.codeEditorOption = false;
			this.changesPaused    = false;

			// JQuery trigger.
			this._refreshJs = _.debounce( _.bind( this.refreshJs, this ), 300 );

			// Fetch query_data if not present only. dont save
			if ( 'undefined' !== typeof fusionAllElements[ this.model.get( 'element_type' ) ].has_ajax ) {
				if ( 'undefined' === typeof this.model.get( 'query_data' ) ) {
					this.elementView.triggerAjaxCallbacks( true );
				}
			}

			// When tab is changed we init options.
			this.tabsRendered = {};
			this.initOptions = _.debounce( _.bind( this.debouncedInitOptions, this ), 50 );
			this.listenTo( FusionEvents, 'fusion-tab-changed', this.initOptions );
			this.listenTo( FusionEvents, 'fusion-inline-edited', this.forceChange );

			this.childSortableView = false;

			// Active states selected for element.
			this.activeStates     = {};
			this.$targetEl        = 'undefined' !== typeof this.elementView ? this.elementView.$el : false;
			this._tempStateRemove = _.debounce( _.bind( this.tempStateRemove, this ), 3000 );

			this.parentValues     = this.getParentValues() ? this.getParentValues() : false;

			this.$el.attr( 'data-cid', this.model.get( 'cid' ) );

			this.$el.addClass( this.containerIsFlex() ? 'has-flex' : '' );
			this.$el.addClass( this.columIsBlockLayout() ? 'has-block-column' : '' );

			this.type = 'EO';

			this.onInit();

			this.listenTo( FusionEvents, 'fusion-element-removed', this.removeView );
			this.listenTo( FusionEvents, 'fusion-preview-refreshed', this.saveSettings );
			this.listenTo( FusionEvents, 'fusion-close-settings-' + this.model.get( 'cid' ), this.saveSettings );
			this.listenTo( FusionEvents, 'fusion-param-changed-' + this.model.get( 'cid' ), this.paramChanged );
			this.listenTo( FusionEvents, 'fusion-default-changed-' + this.model.get( 'cid' ), this.defaultChanged );
			this.listenTo( FusionEvents, 'fusion-row-nested-edit', this.saveSettings );

			if ( 'dialog' !== FusionApp.preferencesData.editing_mode && 'generated_element' !== this.model.get( 'type' ) && ! this.$el.hasClass( 'fusion-builder-settings-chart-table-dialog' ) ) {
				this.$el.addClass( 'fusion-builder-custom-tab' );
			}

			if ( 'generated_element' === this.model.get( 'type' ) ) {
				FusionEvents.trigger( 'fusion-history-pause-tracking' );
			}

			this.newElement = false;
			if ( 'undefined' !== typeof this.model.get( 'added' ) ) {
				this.newElement = true;
			}

			// Dynamic content.
			this.listenTo( FusionEvents, 'fusion-dynamic-data-removed', this.removeDynamicStatus );
			this.listenTo( FusionEvents, 'fusion-dynamic-data-added', this.addDynamicStatus );
			this.dynamicSelection = false;

			this.debouncedOptionChanges = {};
		},

		/**
		 * Renders the view.
		 *
		 * @since 2.0.0
		 * @return {Object} this
		 */
		render: function() {

			this.renderOptions();

			this.initOptions();

			this.model.unset( 'added' );

			// Check option dependencies
			this.dependencies = new FusionPageBuilder.Dependencies( fusionAllElements[ this.model.get( 'element_type' ) ].params, this );

			FusionApp.dialog.dialogTabs( this.$el );

			this.loadComplete = true;

			if ( 'undefined' !== this.model.get( 'multi' ) && 'multi_element_parent' === this.model.get( 'multi' ) ) {
				this.appendChildSortables();
			}

			FusionEvents.trigger( 'fusion-settings-modal-open' );

			if (  -1 !== this.model.get( 'element_type' ).indexOf( 'fusion_builder_column' ) ) {
				this.setDirectionAttribute();
			}

			this.onRender();

			this.checkPageTemplate();

			this.inlineHistoryListener();

			return this;

		},

		setDirectionAttribute: function() {
			var $option = this.$el.find( '[data-option-id="content_layout"]' ),
				value   = 'column';

			if ( ! $option.length ) {
				return;
			}

			value = $option.find( '#content_layout' ).val();
			$option.attr( 'data-direction', value );
		},

		containerIsFlex: function() {
			var container = FusionPageBuilderApp.getParentContainer( this.model.get( 'cid' ) );
			return container && container.isFlex();
		},

		columIsBlockLayout: function() {
			var column = FusionPageBuilderApp.getParentColumn( this.model.get( 'cid' ) );

			return column && column.isBlockLayout() && 'fusion_builder_column' !== this.model.get( 'element_type' ) && 'fusion_builder_column_inner' !== this.model.get( 'element_type' );
		},

		addDynamicContent: function( event ) {
			var self         = this,
				$option      = jQuery( event.target ).closest( '.fusion-builder-option' ),
				param        = $option.attr( 'data-option-id' ),
				sameParam    = false,
				viewSettings;

			if ( this.dynamicSelection ) {
				if ( param === this.dynamicSelection.model.get( 'param' ) ) {
					sameParam = true;
				}
				this.dynamicSelection.removeView();
			}

			if ( sameParam ) {
				return;
			}

			viewSettings = {
				model: new FusionPageBuilder.Element( {
					param: param,
					option: $option,
					parent: this
				} )
			};

			// On select or cancel or event we destroy.
			this.dynamicSelection = new FusionPageBuilder.DynamicSelection( viewSettings );
			$option.find( '.fusion-dynamic-selection' ).html( this.dynamicSelection.render().el );
		},

		removeDynamicStatus: function( param ) {
			this.$el.find( '.fusion-builder-option[data-option-id="' + param + '"]' ).attr( 'data-dynamic', false );

			// Needed for dependencies.
			this.$el.find( '#' + param ).trigger( 'fusion-change' );
		},

		addDynamicStatus: function( param ) {
			this.$el.find( '.fusion-builder-option[data-option-id="' + param + '"]' ).attr( 'data-dynamic', true );

			// Needed for dependencies.
			this.$el.find( '#' + param ).trigger( 'fusion-change' );
		},

		onRender: function() {
		},

		reRender: function() {
			var $parentDialog = this.$el.closest( '.ui-dialog' ),
				$dialogTopContainer;

			this.tabsRendered = {};
			this.destroyOptions();
			this.render();

			if ( $parentDialog.length ) {
				$parentDialog.find( '.ui-dialog-titlebar + .fusion-builder-modal-top-container' ).remove();
				$parentDialog.find( '.ui-dialog-titlebar' ).after( this.$el.find( '.fusion-builder-modal-top-container' ) );
			} else {
				FusionPageBuilderApp.SettingsHelpers.renderDialogMoreOptions( this );
			}
		},

		/**
		 * Listens for change in parent and destroys settings view since no longer valid.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		inlineHistoryListener: function() {
			var self = this,
				parentCid;

			if ( this.model.get( 'inlineElement' ) && 'undefined' !== typeof this.model.parentView ) {
				parentCid = this.model.parentView.model.get( 'cid' );

				// Timeout so addition of inline does not trigger.
				setTimeout( function() {
					self.listenTo( FusionEvents, 'fusion-param-changed-' + parentCid, function() {
						self.removeView( parentCid );
					} );
				}, 1000 );
			}
		},

		/**
		 * Simply sets optionHasChanged to true.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		forceChange: function() {
			this.optionHasChanged = true;
		},

		/**
		 * Append child sortables.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		appendChildSortables: function() {
			var viewSettings = {
					model: this.model,
					collection: this.collection,
					attributes: {
						settingsView: this
					}
				},
				view;

			view = new FusionPageBuilder.ElementSettingsParent( viewSettings );
			this.$el.find( '.fusion-child-sortables' ).html( view.render().el );
			this.childSortableView = view;
		},

		/**
		 * Execute Callbacks.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		onSettingsCallback: function() {
			var functionName,
				params,
				processedParams;

			if ( 'undefined' !== typeof fusionAllElements[ this.model.get( 'element_type' ) ].on_settings ) {

				functionName = fusionAllElements[ this.model.get( 'element_type' ) ].on_settings;

				if ( 'function' === typeof FusionPageBuilderApp[ functionName ] ) {
					params          = this.model.get( 'params' );
					processedParams = FusionPageBuilderApp[ functionName ]( params, this );

					this.model.set( 'params', processedParams );
				}
			}
		},

		/**
		 * Trigger optionChange when pasting.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		optionChangePaste: function( event ) {
			var self = this;

			setTimeout( function() {
				self.optionChange( event );
			}, 200 );
		},

		/**
		 * Debounce optionChanged if no template.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The JS event.
		 * @return {void}
		 */
		optionChange: function( event, data ) {
			var $target          = jQuery( event.target ),
				$option          = $target.closest( '.fusion-builder-option' ),
				paramName        = this.getParamName( $target, $option ),
				$dynamicWrapper  = $option.closest( '.dynamic-wrapper' ),
				ajaxDynamicParam = false,
				debounceTimeout  = 'tinymce' === $option.data( 'option-type' ) ? 300 : 500,
				element,
				option;

			if ( this.changesPaused ) {
				return;
			}

			// Fix range with default value not triggering properly.
			if ( $target.is( '.fusion-slider-input.fusion-with-default' ) || $target.is( '.awb-ignore' ) ) {
				return;
			}

			// Check if it is a dynamic param being changed which will result in an ajax request.
			if ( $dynamicWrapper.length && $dynamicWrapper.attr( 'data-ajax' ) && 'before' !== paramName && 'after' !== paramName && 'fallback' !== paramName ) {
				ajaxDynamicParam = $option.closest( '.dynamic-wrapper' ).attr( 'data-ajax' );
				if ( $target.closest( '.fusion-builder-option' ).hasClass( 'text' ) ) {
					debounceTimeout = 500;
				}
			}

			// Forced default reset on select field.
			if ( 'object' === typeof data && 'undefined' !== typeof data.silent && data.silent && 'undefined' !== typeof this.elementView ) {
				element = fusionAllElements[ this.elementView.model.get( 'element_type' ) ];
				option  = element.params[ paramName ];

				// If it has a callback then just set the param value directly.
				if ( 'undefined' !== typeof option.callback ) {
					this.elementView.changeParam( paramName, $target.val() );
					return;
				}
			}

			if ( ! jQuery( event.target ).hasClass( 'fusion-skip-debounce' ) && ( this.model.get( 'noTemplate' ) || jQuery( event.target ).hasClass( 'fusion-debounce-change' ) || ajaxDynamicParam || 'tinymce' === $option.data( 'option-type' ) ) ) {
				if ( ! this.debouncedOptionChanges[ paramName ] ) {
					this.debouncedOptionChanges[ paramName ] = _.debounce( _.bind( this.optionChanged, this ), debounceTimeout );
				}

				this.debouncedOptionChanges[ paramName ]( event );
			} else {
				this.optionChanged( event );
			}
		},

		/**
		 * Custom callback on option change.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		customOnChangeCallback: function() {
			var functionName;

			// Manupulate model attributes via custom function if provided by element
			if ( 'undefined' !== typeof fusionAllElements[ this.model.get( 'element_type' ) ].on_change ) {
				functionName = fusionAllElements[ this.model.get( 'element_type' ) ].on_change;
				if ( 'function' === typeof FusionApp.callback[ functionName ] ) {
					this.model.attributes = FusionApp.callback[ functionName ]( jQuery.extend( true, {}, this.model.attributes ), this );
				}
			}
		},

		/**
		 * Get real param name.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		getParamName: function( $target, $option ) {
			var paramName = $option.data( 'option-id' ),
				$wrapper  = $target.closest( '.fusion-builder-option' );

			// Non single dimension fields or font family input.
			if ( $wrapper.hasClass( 'typography' ) || ( $wrapper.hasClass( 'dimension' ) && ! $target.closest( '.single-builder-dimension' ).length ) ) {
				paramName = $target.attr( 'name' );
			}

			return paramName;
		},

		/**
		 * Get param value.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		getParamValue: function( $target ) {
			var paramValue = $target.val();

			// If code block element then need to use method to get val.
			if ( $target.closest( '.fusion-builder-option.code' ).length ) {
				paramValue = this.codeEditorOption[ $target.closest( '.fusion-builder-option.code' ).attr( 'data-index' ) ].getValue();

				// Base64 encode for Code option type.
				if ( 1 === Number( FusionApp.settings.disable_code_block_encoding ) ) {
					paramValue = FusionPageBuilderApp.base64Encode( paramValue );
				}
			}

			if ( $target.hasClass( 'fusion-builder-raw-textarea' ) || $target.hasClass( 'fusion-builder-raw-text' ) ) {
				paramValue = FusionPageBuilderApp.base64Encode( paramValue );
			}

			// Escape input fields.
			if ( $target.is( 'input' ) && '' !== paramValue ) {
				if ( ! $target.hasClass( 'fusion-builder-upload-field' ) && ! $target.hasClass( 'awb-typo-input' ) && ! $target.is( '#generator_element_content' ) && ! $target.is( '#generator_multi_child_content' ) && false === $target.closest( 'li' ).data( 'dynamic' ) ) {
					paramValue = _.escape( paramValue );
				}
			}

			if ( $target.closest( '.fusion-builder-option' ).hasClass( 'escape_html' ) ) {
				paramValue = _.escape( paramValue );
			}

			if ( $target.hasClass( 'fusion-multi-select-option' ) ) {
				paramValue = [];
				jQuery.each( $target.parent().find( '> .fusion-multi-select-option:checked' ), function( index, elem ) {
					paramValue.push( jQuery( elem ).val() );
				} );

				paramValue = paramValue.join( ',' );
			}

			// Ajax select option type.
			if ( $target.closest( '.fusion-builder-option' ).hasClass( 'ajax_select' ) ) {
				paramValue = [];
				jQuery.each( $target.parent().find( '.fusion-select-option:checked' ), function( index, elem ) {
					paramValue.push( jQuery( elem ).val() );
				} );

				paramValue = paramValue.join( ',' );
			}

			return paramValue;
		},

		/**
		 * Whether or not option change is valid.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		shouldContinue: function( $target, paramName, paramValue, params ) {
			var tabId      = this.$el.find( '.fusion-tab-content.active' ).length ? this.$el.find( '.fusion-tab-content.active' ).attr( 'id' ) : false,
				rowIndex,
				pricing,
				callbackFunction;

			// Filter value being changed.
			if ( $target.closest( '.fusion-builder-option.subgroup' ).length ) {
				return false;
			}

			// Repeater value being changed.
			if ( $target.closest( '.fusion-builder-option.repeater' ).length && ! $target.hasClass( 'fusion-repeater-value' ) ) {
				rowIndex = $target.closest( '.repeater-row' ).index();
				this.setRepeaterValue( $target.closest( '.fusion-builder-option.repeater' ).find( '.fusion-repeater-value' ), paramName, rowIndex, paramValue );
				return false;
			}

			// Dynamic value being changed.
			if ( $target.closest( '.fusion-dynamic-content' ).length ) {
				this.setDynamicParamValue( $target.closest( '.fusion-builder-option' ), paramName, paramValue );
				return false;
			}

			if ( $target.hasClass( 'fusion-always-update' ) ) {
				return true;
			}

			if ( $target.hasClass( 'fusion-hide-from-atts' ) ) {
				return false;
			}

			// If its a tab and its not fully rendered yet.
			if ( tabId && ( 'undefined' === typeof this.tabsRendered[ tabId ] || true !== this.tabsRendered[ tabId ] ) ) {
				return false;
			}

			// Layout not complete.
			if ( false === this.loadComplete ) {
				return false;
			}

			if ( ! paramName ) {
				return false;
			}

			// If value hasnt changed.
			if ( paramValue === params[ paramName ] || ( '' === paramValue && 'undefined' === typeof params[ paramName ] ) ) {

				if ( 'fusion_pricing_column' !== this.model.get( 'element_type' ) ) {
					return false;
				}

				callbackFunction = FusionPageBuilderApp.getCallbackFunction( this.model.attributes, paramName, paramValue, this.elementView );

				if ( 'fusionPricingTablePrice' !== callbackFunction[ 'function' ] ) {
					return false;
				}

				pricing = this.model.get( 'priceParams' );

				if ( '' === paramValue && 'undefined' === typeof pricing[ paramName ] ) {
					return false;
				}
			}

			// If its a color picker with fusion using default set but the value its trying to use is not empty, then return.
			if ( $target.hasClass( 'fusion-using-default' ) && '' !== paramValue && 'undefined' !== typeof paramValue ) {
				return false;
			}

			return true;
		},

		/**
		 * Things to do, places to go when options change.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event triggering the option change.
		 * @return {void}
		 */
		optionChanged: function( event ) {

			var $target    = jQuery( event.target ),
				$option    = $target.closest( '.fusion-builder-option' ),
				reRender   = true,
				params     = this.model.get( 'params' ),
				modelData  = jQuery.extend( this.model.attributes, {} ),
				paramName,
				initialVal,
				MultiGlobalArgs,
				paramValue,
				parentView;

			this.customOnChangeCallback();

			paramName  = this.getParamName( $target, $option );
			paramValue = this.getParamValue( $target, paramName, paramValue, params );
			initialVal = 'undefined' === typeof params[ paramName ] ? '' : params[ paramName ];

			if ( ! this.shouldContinue( $target, paramName, paramValue, params ) ) {
				return;
			}

			this.optionHasChanged = true;

			if ( ! this.model.get( 'inlineElement' ) ) {
				if ( 'undefined' !== typeof this.elementView ) {
					reRender = this.elementView.updateParam( paramName, paramValue, event );
				}
			}

			if ( 'undefined' !== typeof this.elementView && 'function' === typeof this.elementView.onOptionChange ) {
				this.elementView.onOptionChange( paramName, paramValue, event );
			}

			// Trigger temporary active state if exists.
			this.triggerTemporaryState( $option );

			// Trigger Option change for any other option to listen for it.
			FusionEvents.trigger( 'awb-options-change-' + paramName, paramValue, this.model.get( 'cid' ) );

			if ( 'generated_element' === this.model.get( 'type' ) ) {
				return;
			}

			// Update inline element which has no separate view.
			if ( this.model.get( 'inlineElement' ) ) {
				params[ paramName ] = paramValue;
				this.model.set( 'params', params );
				FusionPageBuilderApp.inlineEditorHelpers.processInlineElement( this.model, paramName );
			}

			// Re render view, right now that is auto done on model change.
			if ( reRender && 'undefined' !== typeof this.elementView && ! $target.hasClass( 'skip-update' ) ) {

				// Re-render if element should.
				if ( -1 === this.model.get( 'element_type' ).indexOf( 'fusion_builder_column' ) || ( 'spacing' !== paramName ) ) {
					this.elementView.reRender();
				}
			}

			// JS trigger for option specific refreshes.
			this._refreshJs( paramName );


			// Trigger active states.
			this.triggerActiveStates();

			// A setting of some kind has been changed.
			this.settingChanged = true;

			if ( this.childSortableView ) {
				this.childSortableView.render();
			}

			// Handle multiple global elements.
			MultiGlobalArgs = {
				currentModel: this.model,
				handleType: 'changeOption',
				Name: paramName,
				Value: paramValue
			};
			fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );
		},

		getParentValues: function() {
			var parentView;

			if ( 'multi_element_child' === this.model.get( 'multi' ) ) {
				parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
				if ( 'undefined' === parentView ) {
					return false;
				}
				return parentView.model.get( 'params' );
			}
			return false;
		},

		/**
		 * Triggers a refresh.
		 *
		 * @since 2.0.0
		 * @return void
		 */
		refreshJs: function( paramName ) {
			jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-option-change-' + paramName, this.model.attributes.cid );
		},

		/**
		 * Destroys the options.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		destroyOptions: function() {
			var self = this;

			// Close colorpickers before saving
			this.$el.find( '.fusion-color-created' ).each( function() {
				if ( 'undefined' !== typeof jQuery( this ).awbColorPicker( 'instance' ) ) {
					jQuery( this ).awbColorPicker( 'close' );
				}
			} );

			// Destroy each CodeMirror editor instance
			this.$el.find( '.fusion-builder-code-block' ).each( function( index ) {
				if ( self.codeEditorOption[ index ] ) {
					self.codeEditorOption[ index ].toTextArea();
				}
			} );

			// Remove each instance of tinyMCE editor from this view if it has been init.
			this.$el.find( '.fusion-editor-field' ).each( function() {
				var editorID = jQuery( this ).attr( 'id' );
				if ( jQuery( this ).hasClass( 'fusion-init' ) ) {
					self.fusionBuilderMCEremoveEditor( editorID );
				}
			} );

			this.onDestroyOptions();
		},

		/**
		 * Destroy options callback.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		onDestroyOptions: function() {

		},

		/**
		 * Activate shortcode generator.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		activateSCgenerator: function( event ) {
			openShortcodeGenerator( jQuery( event.target ) );
		},

		/**
		 * Init the view options.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		renderOptions: function() {
			var thisModel      = this.model,
				fixSettingsLvl = false,
				parentAtts,
				attributes = jQuery.extend( true, {}, this.model.attributes );

			// Fix for deprecated 'settings_lvl' attribute
			if ( 'undefined' !== thisModel.attributes.params.settings_lvl && 'parent' === thisModel.attributes.params.settings_lvl ) {
				fixSettingsLvl = true;
				parentAtts     = thisModel.attributes.params;
			}

			if ( 'object' === typeof this.elementView ) {
				attributes.dynamic_params = this.elementView.dynamicParams.getAll();
			}

			// Set correct type param based on TO setting in case 'old' container is edited.
			if ( 'fusion_builder_container' === this.model.get( 'element_type' ) && 'undefined' === typeof attributes.params.type ) {
				attributes.params.type = 1 === parseInt( fusionAllElements.fusion_builder_container.extras.container_legacy_support ) ? 'legacy' : 'flex';
			}

			// We don't allow 'legacy' containers in header layout sections.
			if ( 'fusion_builder_container' === this.model.get( 'element_type' ) && 'undefined' !== typeof FusionApp.data.template_category && 'header' === FusionApp.data.template_category ) {
				attributes.params.type = 'flex';
			}

			if ( 'function' === typeof this.filterAttributes ) {
				attributes = this.filterAttributes( attributes );
			}

			// if fusion gallery image ( child ) add parents value to atts.
			if ( 'fusion_gallery_image' === this.model.get( 'element_type' ) ) {
				attributes.parentValues = this.getParentValues();
			}

			this.$el.html( this.template( { atts: attributes } ) );
		},

		/**
		 * Init the view options.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		debouncedInitOptions: function( $element ) {
			var tabId   = this.$el.find( '.fusion-tab-content.active' ).length ? this.$el.find( '.fusion-tab-content.active' ).attr( 'id' ) : false,
				$baseEl = tabId ? this.$el.find( '.fusion-tab-content.active' ) : this.$el,
				$thisEl = 'undefined' !== typeof $element && $element.length ? $element : $baseEl,
				self    = this;

			// Check if tab has already been init.
			if ( 'undefined' === typeof $element && ( ( tabId && true === this.tabsRendered ) || ( 'undefined' !== typeof this.tabsRendered[ tabId ] && this.tabsRendered[ tabId ] ) || true === this.tabsRendered ) ) {
				return;
			}
			this.optionDynamicData( $thisEl );
			this.textFieldPlaceholder( $thisEl );
			this.optionDateTimePicker( $thisEl );
			this.optionColorpicker( $thisEl );
			this.optionColumnWidth( $thisEl );
			this.optionFormOptions( $thisEl );
			this.optionLogics( $thisEl );
			this.optionIconpicker( $thisEl );
			this.optionCodeBlock( $thisEl );
			this.optionDimension( $thisEl );
			this.optionSelect( $thisEl );
			this.optionMultiSelect( $thisEl );
			this.optionUpload( $thisEl );
			this.optionMultiUpload( $thisEl );
			this.optionEditor( $thisEl );
			this.optionCheckboxButtonSet( $thisEl );
			this.optionRadioButtonSet( $thisEl );
			this.optionLinkSelector( $thisEl );
			this.optionRange( $thisEl );
			this.optionSortable( $thisEl );
			this.optionSortableText( $thisEl );
			this.optionConnectedSortable( $thisEl );
			this.optionAjaxSelect( $thisEl );
			this.optionFocusImage( $thisEl );
			this.optionTypography( $thisEl );

			// TODO: fix for WooCommerce element.
			if ( 'fusion_woo_shortcodes' === this.model.get( 'element_type' ) ) {
				if ( true === FusionPageBuilderApp.shortcodeGenerator ) {
					$thisEl.find( '#element_content' ).attr( 'id', 'generator_element_content' );
				}
			}

			// Attachment upload alert.
			$thisEl.find( '.uploadattachment .fusion-builder-upload-button' ).on( 'click', function() {
				alert( fusionBuilderText.to_add_images ); // jshint ignore: line
			} );

			// Fusion Form label update.
			if ( this.model.get( 'element_type' ).includes( 'fusion_form_' ) ) {
				setTimeout( function() {
					var elem = $thisEl.find( 'input[name="label"]' )[ 0 ],
						elemLen,
						oSel;

					if ( elem ) {
						elemLen = elem.value.length;
						// For IE Only
						if ( document.selection ) {
							// Set focus
							elem.focus();
							// Use IE Ranges
							oSel = document.selection.createRange();
							// Reset position to 0 & then set at end
							oSel.moveStart( 'character', -elemLen );
							oSel.moveStart( 'character', elemLen );
							oSel.moveEnd( 'character', 0 );
							oSel.select();
						} else if ( elem.selectionStart || '0' == elem.selectionStart ) {
							// Firefox/Chrome
							elem.selectionStart = elemLen;
							elem.selectionEnd = elemLen;
							elem.focus();
						} // if
					}
				}, 200 );

				$thisEl.on( 'change', '[name="label"]', function( event ) {
					var label = ( event.target && event.target.value ) || '';
					var $name = jQuery( this ).parents().siblings( '[data-option-id="name"]' ).find( 'input' );
					if ( '' === $name.val() && label ) {
						$name.val( _.fusionSanitize( label ) );
						$name.trigger( 'change' );
					}
				} );

				$thisEl.on( 'keydown', '[name="name"], [name$="field_name"]', function( e ) {
					var c = e.which;
					var ascii = {
						'109': '45',
						'173': '45',
						'186': '59',
						'189': '45'
					};
					if ( ascii.hasOwnProperty( c ) ) {
						c = ascii[ c ];
					}
					if ( ( 65 <= c && 90 >= c ) || ( !e.shiftKey && 48 <= c && 57 >= c ) || 45 == c || ( e.shiftKey && 59 == c ) || 8 == c || ( 37 <= c && 40 >= c ) ) {
						return;
					}
					return event.preventDefault();
				} );
			}

			if ( 'undefined' === typeof $element ) {
				this.optionRepeater( 'builder' );
			}

			setTimeout( function() {
				$thisEl.find( 'select, input, textarea, radio' ).filter( ':eq(0)' ).not( '[data-placeholder], .awb-color-picker' ).focus();
			}, 1 );

			// If rendering a specific tab, save this fact to prevent reinit.
			if ( tabId ) {
				setTimeout( function() {
					self.tabsRendered[ tabId ] = true;
				}, 500 );
			} else {
				this.tabsRendered = true;
			}

		},

		/**
		 * Inserts shortcode from generator.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		insertGeneratedShortcode: function( event ) {

			var attributes,
				functionName,
				parentView,
				element;

			if ( event ) {
				event.preventDefault();
			}

			// Remove activee states.
			this.removeActiveStates();

			attributes = this.model.attributes;

			// Escapes &, <, >, ", `, and ' characters
			if ( 'undefined' !== typeof fusionAllElements[ this.model.get( 'element_type' ) ].escape_html && true === fusionAllElements[ this.model.get( 'element_type' ) ].escape_html ) {
				attributes.params.element_content = _.escape( attributes.params.element_content );
			}

			// Manupulate model attributes via custom function if provided by element
			if ( 'undefined' !== typeof fusionAllElements[ this.model.get( 'element_type' ) ].on_save ) {

				functionName = fusionAllElements[ this.model.get( 'element_type' ) ].on_save;

				if ( 'function' === typeof FusionApp.callback[ functionName ] ) {
					attributes = FusionApp.callback[ functionName ]( attributes, this );
				}
			}

			element = FusionPageBuilderApp.generateElementShortcode( this.model, false, true );

			FusionEvents.trigger( 'fusion-history-resume-tracking' );

			this.openGeneratorTarget();

			fusionBuilderInsertIntoEditor( element, FusionPageBuilderApp.shortcodeGeneratorEditorID );

			// Destroy option fields
			this.destroyOptions();

			if ( 'multi_element_child' === this.model.get( 'multi' ) ) {

				// Set element/model attributes
				this.model.set( attributes );

				FusionEvents.trigger( 'fusion-multi-element-edited' );
				FusionEvents.trigger( 'fusion-multi-child-update-preview' );

				parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
				if ( 'undefined' !== typeof parentView ) {
					parentView.updateElementContent();
				}

			} else if ( 'multi_element_parent' === this.model.get( 'multi' ) ) {

				// TODO: this.mode.set( 'params' );
				this.model.set( attributes );
			}

			if ( FusionPageBuilderApp.manuallyAdded ) {
				FusionPageBuilderApp.shortcodeGenerator         = FusionPageBuilderApp.manualGenerator;
				FusionPageBuilderApp.shortcodeGeneratorEditorID = FusionPageBuilderApp.manualEditor;
				FusionPageBuilderApp.manuallyAdded              = false;
			}
			if ( 'undefined' !== typeof this.model && 'undefined' !== typeof this.model.get( 'multi' ) && 'multi_element_child' === this.model.get( 'multi' )  ) {
				FusionEvents.trigger( 'fusion-child-changed' );
			}

			this.remove();

			FusionPageBuilderApp.shortcodeGenerator = '';

			FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

			FusionEvents.trigger( 'fusion-settings-modal-save' );
		},

		removeView: function( cid ) {

			if ( cid !== this.model.get( 'cid' ) && ( 'undefined' === typeof this.model.parentView || ! this.model.parentView || cid !== this.model.parentView.model.get( 'cid' ) ) ) {
				return;
			}

			if ( this.dynamicSelection ) {
				this.dynamicSelection.removeView();
			}

			// Destroy option fields
			this.destroyOptions();

			FusionEvents.trigger( 'fusion-settings-removed', this.model.get( 'cid' ) );

			jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'fusion-dialog-ui-active' );

			this.remove();

			FusionApp.sidebarView.changeTabTitle();
		},

		/**
		 * Saves the settings.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		saveSettings: function( event ) {

			var attributes,
				functionName,
				parentView,
				MultiGlobalArgs;

			if ( event ) {
				event.preventDefault();
			}

			// Destroy option fields
			this.destroyOptions();

			// Remove activee states.
			this.removeActiveStates();

			attributes = this.model.attributes;

			// Column and container spacing.
			if ( 'fusion_builder_container' === this.model.get( 'element_type' ) || 'fusion_builder_column' === this.model.get( 'element_type' ) || 'fusion_builder_column_inner' === this.model.get( 'element_type' ) ) {
				this.elementView.destroyResizable();
				this.elementView.$el.removeClass( 'fusion-builder-element-edited' );
			}

			// Escapes &, <, >, ", `, and ' characters
			if ( 'undefined' !== typeof fusionAllElements[ this.model.get( 'element_type' ) ].escape_html && true === fusionAllElements[ this.model.get( 'element_type' ) ].escape_html ) {
				attributes.params.element_content = _.escape( attributes.params.element_content );
			}

			// Manipulate model attributes via custom function if provided by element
			if ( 'undefined' !== typeof fusionAllElements[ this.model.get( 'element_type' ) ].on_save ) {

				functionName = fusionAllElements[ this.model.get( 'element_type' ) ].on_save;

				if ( 'function' === typeof FusionApp.callback[ functionName ] ) {
					attributes = FusionApp.callback[ functionName ]( attributes, this );
				}
			}

			if ( 'multi_element_child' === this.model.get( 'multi' ) ) {

				// Set element/model attributes
				this.model.set( attributes );

				FusionEvents.trigger( 'fusion-multi-element-edited' );
				FusionEvents.trigger( 'fusion-multi-child-update-preview' );

				parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
				if ( 'undefined' !== typeof parentView ) {
					parentView.updateElementContent();

					// Close parent's settings dialog.
					if ( event && 'undefined' !== typeof event.currentTarget && ( jQuery( event.currentTarget ).hasClass( 'fusiona-close-fb' ) || jQuery( event.currentTarget ).hasClass( 'ui-dialog-titlebar-close' ) ) )  {
						FusionEvents.trigger( 'fusion-close-settings-' + this.model.get( 'parent' ) );
					}
				}

				this.remove();

			} else if ( 'multi_element_parent' === this.model.get( 'multi' ) ) {

				// TODO: this.mode.set( 'params' );
				this.model.set( attributes );

				this.remove();

			} else { // Regular element

				this.remove();
			}

			// Change Heading text.
			FusionApp.sidebarView.changeTabTitle();

			if ( 'undefined' !== typeof this.elementView ) {
				this.elementView.onSettingsClose();
			}

			// Handle multiple global elements.
			MultiGlobalArgs = {
				currentModel: this.model,
				handleType: 'save',
				attributes: this.model.attributes
			};
			fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );

			if ( FusionPageBuilderApp.manuallyAdded ) {
				FusionPageBuilderApp.shortcodeGenerator         = FusionPageBuilderApp.manualGenerator;
				FusionPageBuilderApp.shortcodeGeneratorEditorID = FusionPageBuilderApp.manualEditor;
				FusionPageBuilderApp.manuallyAdded              = false;
			}
			if ( 'undefined' !== typeof this.model && 'undefined' !== typeof this.model.get( 'multi' ) && 'multi_element_child' === this.model.get( 'multi' )  ) {
				FusionEvents.trigger( 'fusion-child-changed' );
			}

			FusionEvents.trigger( 'fusion-settings-modal-save', this.model.get( 'cid' ) );
		},

		/**
		 * Saves the child and opens parent settings.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		openParent: function( event ) {
			var parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );

			if ( parentView ) {
				parentView.settings();
			}

			this.saveSettings( event );
		},

		/**
		 * Opens target dialog for generator insert/cancel.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		openGeneratorTarget: function() {
			var targetView = FusionPageBuilderViewManager.getView( this.model.get( 'target' ) );

			if ( targetView && 'dialog' === FusionApp.preferencesData.editing_mode ) {
				targetView.settings();
			}
		},

		/**
		 * Closes the generator modal.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		closeGeneratorModal: function() {

			// Destroy element model
			this.model.destroy();

			FusionEvents.trigger( 'fusion-history-resume-tracking' );

			FusionEvents.trigger( 'fusion-settings-modal-cancel' );

			this.openGeneratorTarget();

			this.remove();
		},

		/**
		 * Remove an MCE Editor.
		 *
		 * @since 2.0.0
		 * @param {string} id - The editor ID.
		 * @return {void}
		 */
		fusionBuilderMCEremoveEditor: function( id ) {

			if ( 'undefined' !== typeof window.tinyMCE ) {
				window.tinyMCE.execCommand( 'mceRemoveEditor', false, id );
				if ( 'undefined' !== typeof window.tinyMCE.get( id ) ) {
					window.tinyMCE.remove( '#' + id );
				}
			}
		},

		/**
		 * Runs before we start processing element settings dependencies.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		beforeProcessDependencies: function() {
			var view      = this,
				thisEl    = view.$el,
				shortcode = view.model.get( 'element_type' ),
				dividerType,
				upAndDown,
				centerOption;

			// Special check for section separator.
			if ( 'undefined' !== typeof shortcode && 'fusion_section_separator' === shortcode ) {
				dividerType  = thisEl.find( '#divider_type' );
				upAndDown    = dividerType.closest( 'ul' ).find( 'li[data-option-id="divider_candy"]' ).find( '.fusion-option-divider_candy' ).find( '.ui-button[data-value="bottom,top"]' );
				centerOption = dividerType.closest( 'ul' ).find( 'li[data-option-id="divider_position"]' ).find( '.fusion-option-divider_position' ).find( '.ui-button[data-value="center"]' );

				if ( 'triangle' !== dividerType.val() ) {
					upAndDown.hide();
				} else {
					upAndDown.show();
				}

				if ( 'bigtriangle' !== dividerType.val() ) {
					centerOption.hide();
				} else {
					centerOption.show();
				}

				dividerType.on( 'change paste keyup', function() {

					if ( 'triangle' !== jQuery( this ).val() ) {
						upAndDown.hide();
					} else {
						upAndDown.show();
					}

					if ( 'bigtriangle' !== jQuery( this ).val() ) {
						centerOption.hide();
						if ( centerOption.hasClass( 'ui-state-active' ) ) {
							centerOption.prev().click();
						}
					} else {
						centerOption.show();
					}
				} );
			}
		},

		addSliderVideo: function( event ) {

			var defaultParams,
				elementType,
				targetCid = this.model.get( 'cid' );

			if ( event ) {
				event.preventDefault();
			}
			FusionPageBuilderApp.manualGenerator            = FusionPageBuilderApp.shortcodeGenerator;
			FusionPageBuilderApp.manualEditor               = FusionPageBuilderApp.shortcodeGeneratorEditorID;
			FusionPageBuilderApp.manuallyAdded              = true;
			FusionPageBuilderApp.shortcodeGenerator         = true;
			FusionPageBuilderApp.shortcodeGeneratorEditorID = 'video';

			elementType = jQuery( event.currentTarget ).data( 'type' );

			// Get default options
			defaultParams = fusionAllElements[ elementType ].defaults;

			this.collection.add( [
				{
					type: 'generated_element',
					added: 'manually',
					element_type: elementType,
					params: defaultParams,
					target: targetCid
				}
			] );
		},

		defaultPreview: function( event ) {
			var $element = jQuery( event.currentTarget );

			if ( event ) {
				event.preventDefault();
			}

			if ( FusionApp.sidebarView ) {
				FusionApp.sidebarView.shortcutClick( $element );
			}
		},

		showHideDescription: function( event ) {
			var $element = jQuery( event.currentTarget );

			$element.closest( '.fusion-builder-option' ).find( '.description' ).first().slideToggle( 250 );
			$element.toggleClass( 'active' );
		},

		toggleSubGroup: function( event ) {
			var $element = jQuery( event.currentTarget );
			const $tabs = $element.closest( '.fusion-builder-option' ).find( '.fusion-builder-option-container' );
			const $content = $element.closest( '.fusion-builder-option' ).next( '.fusion-subgroups-content-wrap' );

			if ( $element.hasClass( 'active' ) ) {
				$tabs.slideDown( 250, function() {
					$content.slideDown( 500 );
				} );
			} else {
				$content.slideUp( 500, function() {
					$tabs.slideUp( 250 );
				} );
			}

			$element.toggleClass( 'active' );
		},

		showResponsiveOptions: function( event ) {
			var $element = jQuery( event.currentTarget ).parent();

			$element.toggleClass( 'active-item' );
		},

		changeResponsiveOption: function( event ) {
			var $element   = jQuery( event.currentTarget );
			var $parent    = jQuery( event.currentTarget ).closest( 'li.active-item' );

			jQuery( '.fusion-builder-preview-' + $element.data( 'indicator' ) ).trigger( 'click' );
			$parent.removeClass( 'active-item' );

		},

		hideResponsiveOptions: function( event ) {
			var $element   = jQuery( event.currentTarget );

			$element.find( '.fusion-panel-options li.active-item' ).removeClass( 'active-item' );
		},

		checkPageTemplate: function() {
			var option  = this.$el.find( 'li[data-option-id="hundred_percent"]' ),
				postTypes = [ 'post', 'fusion_template', 'fusion_element' ];

			if ( 'fusion_builder_container' === this.model.get( 'element_type' ) ) {
				option.show();

				// Normal post and content override is not active.
				if ( 'fusion_tb_section' !== FusionApp.data.postDetails.post_type && 'object' !== typeof FusionApp.data.template_override.content ) {

					// Check the post type.
					if ( postTypes.includes( FusionApp.data.postDetails.post_type ) ) {

						// Blog post.
						if ( 'no' === FusionApp.data.postMeta._fusion.blog_width_100 || ( 'default' === FusionApp.data.postMeta._fusion.blog_width_100 && '0' === FusionApp.settings.blog_width_100 ) ) {
							option.hide();
						}

					} else if ( 'avada_portfolio' === FusionApp.data.postDetails.post_type ) {

						// Portfolio post.
						if ( 'no' === FusionApp.data.postMeta._fusion.portfolio_width_100 || ( 'default' === FusionApp.data.postMeta._fusion.portfolio_width_100 && '0' === FusionApp.settings.portfolio_width_100 ) ) {
							option.hide();
						}

					} else if ( '100-width.php' !== FusionApp.data.postMeta._wp_page_template ) {

						// Page with default template.
						option.hide();
					}

				} else if ( 'undefined' !== typeof FusionApp.data.postMeta._fusion.fusion_tb_section_width_100 && 'no' === FusionApp.data.postMeta._fusion.fusion_tb_section_width_100 ) { // Template Builder.
					option.hide();
				}
			}
		},

		onInit: function() {
		},

		onCancel: function() {
		},

		defaultChanged: function( param, value ) {
			var self       = this,
				$option    = 0 < this.$el.find( 'li[data-option-id="' + param + '"]' ).length ? this.$el.find( 'li[data-option-id="' + param + '"]' ) : this.$el.find( '#' + param ).closest( '.fusion-builder-option' ),
				optionType = false,
				oldValue,
				$target,
				validColorValue,
				values,
				$datePicker,
				$timePicker;

			if ( ! $option.length ) {
				return;
			}

			if ( $option.attr( 'data-option-type' ) ) {
				optionType = $option.attr( 'data-option-type' );
			}

			if ( ! optionType ) {
				optionType = $option.attr( 'class' ).replace( 'fusion-builder-option', '' ).trim();
			}

			switch ( optionType ) {
			case 'colorpicker':
			case 'colorpickeralpha':
				$target = $option.find( '.awb-color-picker' );
				if ( $target.length ) {
					if ( 'undefined' === typeof $target.awbColorPicker( 'instance' ) ) {
						break;
					}
					$target.awbColorPicker( 'instance' ).defaultColor( value );

					if ( '' === $target.val() ) {
						// Refresh the preview to global color.
						$target.awbColorPicker( 'color', value );
						$target.awbColorPicker( 'color', '' );
					}
				}
				break;

			case 'range':
				if ( 'object' === typeof self.$rangeSlider && 'undefined' !== typeof self.$rangeSlider[ $option.attr( 'data-index' ) ] ) {
					if ( $option.find( '.fusion-range-default' ).length ) {
						oldValue = $option.find( '.fusion-range-default' ).attr( 'data-default' );
						$option.find( '.fusion-range-default' ).attr( 'data-default', value );
					}
					self.$rangeSlider[ $option.attr( 'data-index' ) ].noUiSlider.options[ 'default' ] = value;
					if ( oldValue === self.$rangeSlider[ $option.attr( 'data-index' ) ].noUiSlider.get() ) {
						$option.find( '.fusion-range-default' ).trigger( 'click' );
					}
				}
				break;
			}
		},

		paramChanged: function( param, value ) {
			var self       = this,
				$option    = 0 < this.$el.find( 'li[data-option-id="' + param + '"]' ).length ? this.$el.find( 'li[data-option-id="' + param + '"]' ) : this.$el.find( '[name=' + param + ']' ).closest( '.fusion-builder-option' ),
				optionType = false,
				$target,
				values,
				$datePicker,
				$timePicker;

			if ( jQuery( '.fusion-table-builder-chart' ).length ) {
				jQuery( '.fusion-table-builder-chart' ).closest( '.ui-dialog-content' ).dialog( 'close' );
			}

			if ( ! $option.length ) {
				return;
			}

			if ( $option.attr( 'data-option-type' ) ) {
				optionType = $option.attr( 'data-option-type' );
			}

			this.changesPaused = true;

			if ( ! optionType ) {
				optionType = $option.attr( 'class' ).replace( 'fusion-builder-option', '' ).trim();
			}

			switch ( optionType ) {
			case 'iconpicker':
				$option.find( '.icon_preview.selected-element' ).removeClass( 'selected-element' );
				if ( value && 2 === value.split( ' ' ).length ) {
					$option.find( '.icon-' + value.split( ' ' )[ 0 ] ).addClass( 'selected-element' );
				}
				$option.find( '#' + param ).val( value ).trigger( 'change' );
				break;
			case 'upload':
				$option.find( '#' + param ).val( value ).trigger( 'change' );
				$option.find( '.fusion-builder-upload-preview img' ).remove();

				if ( value && '' !== value ) {
					$option.find( '.fusion-upload-area:not( .fusion-uploaded-image )' ).addClass( 'fusion-uploaded-image' );
					$option.find( '.fusion-builder-upload-preview' ).prepend( '<img src="' + value + '" />' );
				} else {
					$option.find( '.fusion-upload-area' ).removeClass( 'fusion-uploaded-image' );
				}
				break;
			case 'multiple_select':
				$option.find( '.fusion-select-preview' ).empty();
				$option.find( 'input[type="checkbox"]' ).prop( 'checked', false );

				if ( value && '' !== value ) {
					values = value.split( ',' );
					_.each( values, function( value ) {
						$option.find( 'input[value="' + value + '"]' ).prop( 'checked', true );
						$option.find( '.fusion-select-preview' ).append( '<span class="fusion-preview-selected-value" data-value="' + value + '">' + $option.find( 'input[value="' + value + '"]' ).attr( 'data-label' ) + '<span class="fusion-option-remove">x</span></span>' );
					} );
				}

				if ( 0 === $option.find( '.fusion-select-preview .fusion-preview-selected-value' ).length ) {
					$option.find( '.fusion-select-preview-wrap' ).addClass( 'fusion-select-show-placeholder' );
				} else {
					$option.find( '.fusion-select-preview-wrap' ).removeClass( 'fusion-select-show-placeholder' );
				}

				$option.find( '#' + param ).val( value ).trigger( 'change' );
				break;
			case 'tinymce':
				if ( $option.find( '#child_' + param ).length ) {
					param = 'child_' + param;
				}
				$option.find( '#' + param ).val( value );
				if ( $option.find( '#' + param ).hasClass( 'fusion-editor-field' ) && 'undefined' !== typeof window.tinyMCE && window.tinyMCE.get( param ) && ! window.tinyMCE.get( param ).isHidden() ) {
					if ( window.tinyMCE.get( param ).getParam( 'wpautop', true ) && 'undefined' !== typeof window.switchEditors ) {
						value = window.switchEditors.wpautop( value );
					}
					window.tinyMCE.get( param ).setContent( value, { format: 'html' } );
				}
				break;
			case 'date_time_picker':
				$option.find( '#' + param ).val( value ).trigger( 'change' );
				$datePicker = $option.find( '.fusion-date-picker' );
				$timePicker = $option.find( '.fusion-time-picker' );

				if ( -1 !== value.indexOf( ' ' ) && $datePicker.length && $timePicker.length ) {
					values = value.split( ' ' );
					$datePicker.val( values[ 0 ] );
					$timePicker.val( values[ 1 ] );
				} else if ( $datePicker.length ) {
					$datePicker.val( value );
				} else if ( $timePicker.length ) {
					$timePicker.val( value );
				}
				break;
			case 'raw_textarea':
			case 'raw_text':
				try {
					value = FusionPageBuilderApp.base64Decode( value );
					$option.find( '#' + param ).val( value ).trigger( 'change' );
				} catch ( e ) {
					console.warn( 'Something went wrong! Error triggered - ' + e );
				}
				break;
			case 'code':
				if ( 'undefined' !== typeof self.codeEditorOption[ $option.attr( 'data-index' ) ] ) {
					try {
						value = FusionPageBuilderApp.base64Decode( value );
						self.codeEditorOption[ $option.attr( 'data-index' ) ].setValue( value );
					} catch ( e ) {
						console.warn( 'Something went wrong! Error triggered - ' + e );
					}
				}
				break;
			case 'range':
				if ( 'undefined' !== typeof self.$rangeSlider[ $option.attr( 'data-index' ) ] ) {
					if ( 'undefined' === typeof value || '' === value ) {
						value = self.$rangeSlider[ $option.attr( 'data-index' ) ].noUiSlider.options[ 'default' ];
					}
					self.$rangeSlider[ $option.attr( 'data-index' ) ].noUiSlider.set( value );
				}
				break;
			case 'checkbox_button_set':
				$option.find( '.button-set-value' ).val( value ).trigger( 'change' );
				$option.find( '.ui-state-active' ).removeClass( 'ui-state-active' );
				if ( 'string' === typeof value && '' !== value ) {
					values = value.split( ',' );
					_.each( values, function( value ) {
						$option.find( '[data-value="' + value + '"]' ).addClass( 'ui-state-active' );
					} );
				} else if ( 'hide_on_mobile' === param ) {
					$option.find( '.buttonset-item' ).addClass( 'ui-state-active' );
				}
				break;
			case 'select':
				$target = $option.find( '.fusion-select-options .fusion-select-label[data-value="' + value + '"]' );
				if ( $target.length ) {
					$option.find( '.fusion-option-selected' ).removeClass( 'fusion-option-selected' );
					$target.addClass( 'fusion-option-selected' );
					$option.find( '.fusion-select-preview' ).html( $target.html() );
					$option.find( '#' + param ).val( value ).trigger( 'fusion-change' );
				}
				break;
			case 'colorpicker':
			case 'colorpickeralpha':
				$target = $option.find( '.fusion-builder-color-picker-hex' );
				if ( $target.length ) {
					$target.val( value ).trigger( 'change' );
				}
				break;
			case 'radio_button_set':
				$target = $option.find( '.buttonset-item[data-value="' + value + '"]' );
				if ( $target.length ) {
					$option.find( '.ui-state-active' ).removeClass( 'ui-state-active' );
					$target.addClass( 'ui-state-active' );
					$option.find( '#' + param ).val( value ).trigger( 'change' );
				}
				break;
			case 'dimension':
				$target = $option.find( '#' + param );
				if ( $target.length ) {
					$target.val( value ).trigger( 'change' );
				}
				break;
			case 'sortable':
				self.reRender();
				break;
			case 'sortable_text':
				self.reRender();
				break;
			case 'connected_sortable':
				self.reRender();
				break;
			default:
				$option.find( '[name=' + param + ']' ).val( value ).trigger( 'change' );
				break;
			}

			this.changesPaused = false;
		}
	} );

	// Options
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionCodeBlock );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionColorPicker );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionDimensionField );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionIconPicker );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionOptionUpload );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.radioButtonSet );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionRangeField );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionSelectField );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionCheckboxButtonSet );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionDateTimePicker );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionEditor );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionAjaxSelect );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionMultiSelect );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionSwitchField );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionTextFieldPlaceholder );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionLinkSelector );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionRepeaterField );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionSortable );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionSortableText );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionConnectedSortable );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionDynamicData );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionTypographyField );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionColumnWidth );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionFormOptions );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionLogics );
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.options.fusionImageFocusPoint );

	// Active states.
	_.extend( FusionPageBuilder.ElementSettingsView.prototype, FusionPageBuilder.fusionActiveStates );
}( jQuery ) );

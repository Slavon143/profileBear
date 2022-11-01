/* global FusionApp, fusionAllElements, fusionAppConfig, FusionPageBuilderViewManager, FusionPageBuilderElements, FusionEvents, fusionMultiElements, FusionPageBuilderApp, fusionBuilderText, diffDOM, tinyMCE, fusionGetPercentPaddingHorizontalNegativeMargin, fusionGetPercentPaddingHorizontalNegativeMarginIfSiteWidthPercent, fusionTriggerEvent, fusionVendorShortcodes, fusionSanitize */
/* eslint no-useless-escape: 0 */
/* eslint no-shadow: 0 */
/* eslint max-depth: 0 */
/* eslint no-unused-vars: 0 */
/* eslint guard-for-in: 0 */
/* eslint no-continue: 0 */
/* eslint no-bitwise: 0 */
/* eslint no-mixed-operators: 0 */
/* eslint no-empty-function: 0 */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	var fusionElements          = [],
		fusionGeneratorElements = [],
		fusionComponents        = [],
		fusionFormComponents    = [],
		sortedElements;

	jQuery.fn.outerHTML = function() {
		return ( ! this.length ) ? this : ( this[ 0 ].outerHTML || ( function( el ) {
			var div = document.createElement( 'div' ),
				contents;

			div.appendChild( el.cloneNode( true ) );
			contents = div.innerHTML;
			div = null;
			return contents;
		}( this[ 0 ] ) ) );
	};

	// Loop over all available elements and add them to Avada Builder.
	sortedElements = _.sortBy( fusionAllElements, function( element ) {
		return element.name.toLowerCase();
	} );

	_.each( sortedElements, function( element ) {
		var newElement,
			targetObject = fusionGeneratorElements;

		if ( 'undefined' === typeof element.hide_from_builder ) {

			newElement = {
				title: element.name,
				label: element.shortcode
			};

			if ( 'undefined' !== typeof element.component && element.component ) {
				targetObject = fusionComponents;
			}
			if ( 'undefined' === typeof element.generator_only && 'undefined' === typeof element.form_component ) {
				fusionElements.push( newElement );
			}

			if ( ( 'undefined' !== typeof element.form_component && element.form_component ) || ( 'undefined' !== typeof element.allow_in_form && element.allow_in_form ) ) {
				fusionFormComponents.push( newElement );
				return;
			}

			targetObject.push(
				Object.assign(
					{},
					newElement,
					{
						generator_only: 'undefined' !== typeof element.generator_only ? true : element.generator_only,
						templates: 'undefined' !== typeof element.templates ? element.templates : false,
						components_per_template: 'undefined' !== typeof element.components_per_template ? element.components_per_template : false,
						template_tooltip: 'undefined' !== typeof element.template_tooltip ? element.template_tooltip : false
					}
				)
			);
		}
	} );

	window.FusionPageBuilderViewManager = jQuery.extend( true, {}, new FusionPageBuilder.ViewManager() );

	// Avada Builder App View
	FusionPageBuilder.AppView = Backbone.View.extend( {

		model: FusionPageBuilder.Element,

		collection: FusionPageBuilderElements,

		elements: {
			modules: fusionElements,
			generator_elements: fusionGeneratorElements,
			components: fusionComponents,
			componentsCounter: 0,
			usedComponents: [],
			form_components: fusionFormComponents
		},

		events: {
			contextmenu: 'contextMenu'
		},

		template: FusionPageBuilder.template( jQuery( '#fusion-builder-front-end-template' ).html() ),

		/**
		 * Init.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {
			this.extraShortcodes     = new FusionPageBuilder.ExtraShortcodes();
			this.inlineEditors       = new FusionPageBuilder.InlineEditorManager();
			this.inlineEditorHelpers = new FusionPageBuilder.InlineEditorHelpers();
			this.DraggableHelpers    = new FusionPageBuilder.DraggableHelpers();
			this.SettingsHelpers     = new FusionPageBuilder.SettingsHelpers();
			this.navigator           = new FusionPageBuilder.Navigator();
			this.navigatorView       = new FusionPageBuilder.NavigatorView( { model: this.navigator } );
			this.dynamicValues       = new FusionPageBuilder.DynamicValues();
			this.studio              = new FusionPageBuilder.Studio();
			this.website             = new FusionPageBuilder.Website();
			this.formStyles          = false;
			this.offCanvasStyles     = false;
			this.activeStudio        = false;

			// Post contents
			this.postContent = false;

			// Post Id
			this.postID = false;

			// Listen for new elements
			this.listenTo( this.collection, 'add', this.addBuilderElement );

			// Listen for data update
			this.listenTo( FusionEvents, 'fusion-data-updated', this.updateData );

			// Listen for preview toggle.
			this.listenTo( FusionEvents, 'fusion-preview-toggle', this.previewToggle );
			this.previewMode = false;

			// Listen for preview update to set some global styles.
			this.listenTo( FusionEvents, 'fusion-preview-update', this.setGlobalStyles );

			// Listen for frame resizes and sets helper class for CSS.
			this.listenTo( FusionEvents, 'fusion-preview-resize', this.setStackedContentClass );
			this.listenTo( FusionEvents, 'fusion-to-content_break_point-changed', this.setStackedContentClass );

			// Listen for header_position option change.
			this.listenTo( FusionEvents, 'fusion-to-header_position-changed', this.reInitScrollingSections );

			this.listenTo( window.FusionEvents, 'fusion-preferences-droppables_visible-updated', this.toggleDroppablesVisibility );
			this.listenTo( window.FusionEvents, 'fusion-preferences-sticky_header-updated', this.toggleStickyHeader );
			this.listenTo( window.FusionEvents, 'fusion-preferences-tooltips-updated', this.toggleTooltips );
			this.listenTo( window.FusionEvents, 'fusion-preferences-element_filters-updated', this.toggleElementFilters );
			this.listenTo( window.FusionEvents, 'fusion-preferences-transparent_header-updated', this.toggleTransparentHeader );
			this.listenTo( window.FusionEvents, 'fusion-preferences-styling_mode-updated', this.toggleDarkMode );
			this.listenTo( window.FusionEvents, 'fusion-preferences-element_transform-updated', this.toggleElementTransform );
			this.listenTo( window.FusionEvents, 'fusion-preferences-options_subtabs-updated', this.optionsSubTabs );

			// Listen to the fusion-content-changed event and re-trigger sticky header resize.
			this.listenTo( FusionEvents, 'fusion-content-changed', function() {
				fusionTriggerEvent( 'fusion-resize-stickyheader' );
			} );

			this.listenTo( FusionEvents, 'fusion-builder-loaded', this.fusionLibraryUI );

			// Base64 encode/decode.
			this._keyStr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';

			// Make sure to delay ajax requests to prevent duplicates.
			this._fusion_do_shortcode = _.debounce( _.bind( FusionApp.callback.fusion_do_shortcode, this ), 300 );

			this.blankPage = false;

			this.render();

			this.reRenderElements = false;

			this.contextMenuView = false;
			this.clipboard = {};

			// Stored latest shortcode content to avoid unnecessary ajax.
			this.lastAjaxCid         = false;
			this.ajaxContentRequests = [];

			// DiffDOM
			this._diffdom = new diffDOM( { valueDiffing: false } );

			jQuery( jQuery( '#fb-preview' )[ 0 ].contentWindow ).on( 'resize', function() {
				FusionEvents.trigger( 'fusion-preview-resize' );
			} );

			jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).on( 'click', function( event ) {
				FusionPageBuilderApp.sizesHide( event );
			} );

			this.correctTooltipPosition();

			this.loaded            = false;
			this.shortcodeAjax     = false;

			this.inlineElements = [ 'fusion_highlight', 'fusion_tooltip', 'fusion_dropcap', 'fusion_popover', 'fusion_one_page_text_link', 'fusion_modal_text_link' ];

			this.documentWrite        = false;
			this.previewDocumentWrite = false;

			this.viewsToRerender  = [];

			this.listenTo( FusionEvents, 'fusion-data-updated', this.resetRenderVariable );

			this.mediaMap = {
				images: {},
				menus: {},
				forms: {},
				post_cards: {},
				videos: {},
				icons: {},
				off_canvases: {}
			};
			this.listenTo( FusionEvents, 'fusion-content-preview-width', this.contentPreviewWidth );
		},

		resetRenderVariable: function() {
			this.reRenderElements = false;
		},

		/**
		 * Gets callback function for option change.
		 *
		 * @since 2.0.0
		 * @param {Object} modelData - The model data.
		 * @param {string} paramName - Parameter name.
		 * @param {mixed} paramValue - The value of the defined parameter.
		 * @param {Object} view - The view object.
		 * @param {boolean} skip - If set to true we bypass changing the parameter in this view.
		 * @return {void}
		 */
		getCallbackFunction: function( modelData, paramName, paramValue, view, skip ) {
			var element    = fusionAllElements[ view.model.get( 'element_type' ) ],
				option     = element.params[ paramName ],
				callbackFunction,
				thisView;

			// Check if it is subfield.
			if ( 'undefined' === typeof option && 'undefined' !== typeof element.subparam_map && 'undefined' !== typeof element.subparam_map[ paramName ] ) {
				option = element.params[ element.subparam_map[ paramName ] ];
			}

			if ( 'undefined' !== typeof modelData.noTemplate && modelData.noTemplate ) {

				// No template, we need to use fusion_do_shortcode.
				callbackFunction          = {};
				callbackFunction.ajax     = true;
				callbackFunction[ 'function' ] = 'fusion_do_shortcode';
				skip                      = 'undefined' === typeof skip ? false : skip;
				thisView                  = FusionPageBuilderViewManager.getView( modelData.cid );

				if ( ! skip ) {
					thisView.changeParam( paramName, paramValue );
				}

				if ( 'undefined' !== typeof modelData.multi && false !== modelData.multi ) {

					// Parent or child element, get the parent total content.
					callbackFunction.parent  = this.getParentElementCid( modelData );
					callbackFunction.content = this.getParentElementContent( modelData, view );
				} else {

					// Regular element, just get element content.
					callbackFunction.parent  = false;
					callbackFunction.content = FusionPageBuilderApp.generateElementShortcode( thisView.$el );
				}

			} else {
				callbackFunction = this.CheckIfCallback( element, option, view.model );
			}

			return callbackFunction;
		},

		getParentElementCid: function( modelData ) {
			if ( 'multi_element_child' === modelData.multi ) {

				// Child, return parent value.
				return modelData.parent;
			}

			// Parent, return cid.
			return modelData.cid;
		},

		getParentElementContent: function( modelData, view ) {
			var parentView;

			if ( 'multi_element_child' === modelData.multi ) {

				// Child, update parent and get total content.
				parentView = FusionPageBuilderViewManager.getView( modelData.parent );
				parentView.updateElementContent();
				return parentView.getContent();
			}

			// Already on parent, get full content.
			return view.getContent();
		},

		/**
		 * Check if the element has a callback.
		 *
		 * @since 2.0.0
		 * @param {Object} element - The element.
		 * @param {Object} option - The option.
		 * @param {Object} model - The model.
		 * @return {Object|false} - Returns the callback, or false if none is defined.
		 */
		CheckIfCallback: function( element, option, model ) {

			// First check if the option has a callback
			if ( 'undefined' !== typeof option && 'undefined' !== typeof option.callback ) {
				return option.callback;
			}

			// Check if the element itself has a callback and query_data is empty.
			if ( 'undefined' !== typeof element && 'undefined' !== typeof element.callback && 'undefined' === typeof model.attributes.query_data ) {
				return element.callback;
			}
			return false;
		},

		/**
		 * Set some global styles in a style tag added to body.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		setGlobalStyles: function( id, setAll ) {
			var styles = '',
				setAllStyles = false,
				margin = 0;

			if ( 'undefined' !== typeof setAll ) {
				setAllStyles = setAll;
			} else if ( 'undefined' === typeof id ) {
				setAllStyles = true;
			}

			// Container outline and controls positioning.
			if ( 'hundredp_padding' === id || setAllStyles ) {
				margin = fusionGetPercentPaddingHorizontalNegativeMargin();
				margin = fusionGetPercentPaddingHorizontalNegativeMarginIfSiteWidthPercent( 0, margin );

				// If we are editing content nested inside a layout section, then no negative margins on containers.
				if ( 'object' === typeof FusionApp.data.template_override && 'object' === typeof FusionApp.data.template_override.content && 'fusion_tb_section' !== FusionApp.data.postDetails.post_type ) {
					margin = 0;
				}
				styles += 'body:not(.has-sidebar) .width-100 .fusion-builder-container:before,';
				styles += 'body:not(.has-sidebar) .width-100 .fusion-builder-container:after,';
				styles += '.fusion-builder-live .width-100 .fusion-builder-container:hover > .fusion-builder-module-controls-container-wrapper,';
				styles += '.fusion-builder-live .width-100 .fusion-builder-container .fusion-container-spacing.fusion-container-margin-top,';
				styles += '.fusion-builder-live .width-100 .fusion-builder-container .fusion-container-spacing.fusion-container-margin-bottom,';
				styles += '.fusion-builder-live .width-100 .fusion-builder-container .fusion-container-spacing.fusion-container-padding-top,';
				styles += '.fusion-builder-live .width-100 .fusion-builder-container .fusion-container-spacing.fusion-container-padding-bottom,';
				styles += '.fusion-builder-live .width-100 .fusion-builder-container .fusion-container-spacing.fusion-container-padding-right';
				styles += '{margin-left:' + margin + ';margin-right:' + margin + '}';

				styles += '.fusion-builder-live .width-100 .fusion-builder-container .fusion-container-spacing.fusion-container-padding-left{margin-left:' + margin + ';}';
				styles += '.fusion-builder-live .width-100 .fusion-builder-container .fusion-container-spacing.fusion-container-padding-right{margin-right:' + margin + ';}';


				// Flex
				styles += '.fusion-builder-live .width-100 .fusion-builder-container .fusion-flex-container .fusion-container-spacing.fusion-container-padding-left{margin-left:0;}';
				styles += '.fusion-builder-live .width-100 .fusion-builder-container .fusion-flex-container .fusion-container-spacing.fusion-container-padding-right{margin-right:0;}';

				styles += 'body:not(.has-sidebar) .width-100 .fusion-builder-container:before,';
				styles += 'body:not(.has-sidebar) .width-100 .fusion-builder-container:after,';
				styles += '.fusion-builder-live .width-100 .fusion-builder-container:hover > .fusion-builder-module-controls-container-wrapper,';
				styles += '.fusion-builder-live .width-100 .fusion-builder-container .fusion-flex-container .fusion-container-spacing.fusion-container-margin-top,';
				styles += '.fusion-builder-live .width-100 .fusion-builder-container .fusion-flex-container .fusion-container-spacing.fusion-container-margin-bottom,';
				styles += '.fusion-builder-live .width-100 .fusion-builder-container .fusion-flex-container .fusion-container-spacing.fusion-container-padding-top,';
				styles += '.fusion-builder-live .width-100 .fusion-builder-container .fusion-flex-container .fusion-container-spacing.fusion-container-padding-bottom,';
				styles += '.fusion-builder-live .width-100 .fusion-builder-container .fusion-flex-container .fusion-container-spacing.fusion-container-padding-right';
				styles += '{margin-left:' + 0 + ';margin-right:' + 0 + '}';
			}

			if ( styles ) {
				if ( ! this.$el.children( 'style' ).length ) {
					this.$el.prepend( '<style></style>' );
				}

				this.$el.children( 'style' ).html( styles );
			}
		},

		/**
		 * Corrects the position of tooltips that would overflow the viewport.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		correctTooltipPosition: function() {
			var self = this;

			this.$el.on( 'mouseenter', '.fusion-builder-module-controls-type-container a, .fusion-builder-column-controls a, .fusion-builder-module-controls a, a.fusion-builder-add-element', function() {
				var anchorWidth = jQuery( this ).outerWidth(),
					tooltip = jQuery( this ).children( '.fusion-container-tooltip, .fusion-column-tooltip, .fusion-element-tooltip' ),
					tooltipWidth,
					tooltipOffset,
					tooltipOffsetLeft,
					tooltipOffsetRight,
					referenceWrapper,
					referenceWrapperOffsetLeft,
					referenceWrapperOffsetRight;

				if ( ! tooltip.length ) {
					return;
				}
				if ( jQuery( this ).closest( '.fusion-has-filters, .awb-sticky' ).length ) {
					return;
				}

				tooltip.children( '.fusion-tooltip-text' ).removeAttr( 'style' );

				tooltipWidth                = tooltip.outerWidth();
				tooltipOffset               = tooltip.offset();
				tooltipOffsetLeft           = tooltipOffset.left;
				tooltipOffsetRight          = tooltipOffsetLeft + tooltipWidth;
				referenceWrapperOffsetLeft  = 0;
				referenceWrapperOffsetRight = self.$el.width();

				jQuery( this ).closest( '.fusion-fullwidth:not(.video-background):not(.has-pattern-background):not(.has-mask-background) .fusion-row' ).css( 'z-index', 'auto' );
				jQuery( this ).closest( '.fusion-fullwidth:not(.video-background):not(.has-pattern-background):not(.has-mask-background)' ).children( '.fullwidth-faded' ).css( 'z-index', 'auto' );

				if ( ! jQuery( this ).closest( '.fusion-element-alignment-left' ).length && ! jQuery( this ).closest( '.fusion-element-alignment-right' ).length ) {
					jQuery( this ).closest( '.fusion-builder-container' ).css( 'z-index', 'auto' );
				}

				// Carousels need different positioning.
				referenceWrapper = tooltip.closest( '.fusion-carousel-wrapper' );
				if ( referenceWrapper.length ) {
					referenceWrapperOffsetLeft  = referenceWrapper.offset().left;
					referenceWrapperOffsetRight = referenceWrapperOffsetLeft + referenceWrapper.outerWidth();
				}

				if ( tooltipOffsetLeft < referenceWrapperOffsetLeft ) {
					tooltip.children( '.fusion-tooltip-text' ).css( 'margin-left', ( ( tooltipWidth / 2 ) + anchorWidth ) + 'px' );
				} else if ( tooltipOffsetRight > referenceWrapperOffsetRight ) {
					tooltip.children( '.fusion-tooltip-text' ).css( 'margin-left', 'calc(' + anchorWidth + 'px - ' + tooltipWidth + 'px)' );
				}

				if ( jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-footer-parallax' ).length ) {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#main' ).css( 'z-index', 'auto' );

					if ( 'fixed' === jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-footer-parallax' ).css( 'position' ) ) {
						jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-footer-parallax' ).css( 'z-index', '-1' );

						if ( jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#sliders-container' ).find( '.tfs-slider[data-parallax="1"]' ).length ) {
							jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#sliders-container' ).css( 'z-index', 'auto' );
						}
					}

				}
			} );

			this.$el.on( 'mouseleave', '.fusion-builder-module-controls-container a', function() {
				var parentElement = jQuery( this ).closest( '.fusion-builder-module-controls-container' ).parent( '.fusion-builder-live-element' );

				if ( ! parentElement.length || ! parentElement.find( '.fusion-modal.in' ).length ) {
					jQuery( this ).closest( '.fusion-row' ).css( 'z-index', '' );
					jQuery( this ).closest( '.fusion-builder-container' ).css( 'z-index', '' );
				}
				if ( jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-footer-parallax' ).length ) {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#main' ).css( 'z-index', '' );
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-footer-parallax' ).css( 'z-index', '' );
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#sliders-container' ).css( 'z-index', '' );
				}
			} );
		},

		/**
		 * Renders the view.
		 *
		 * @since 2.0.0
		 * @return {Object} this
		 */
		render: function() {
			this.$el.find( '.fusion-builder-live-editor' ).html( this.template() );

			// Make sure context menu is available.
			this.delegateEvents();

			this.setStackedContentClass();

			return this;
		},

		/**
		 * Store shortcode data.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		updateData: function() {
			var self = this;

			this.elements.components.forEach( function( component, index ) {
				var re = new RegExp( '\\[' + component.label, 'g' );

				if ( 'string' === typeof FusionApp.data.template_category && ( 'object' !== typeof component.templates || component.templates.includes( FusionApp.data.template_category ) ) ) {
					self.elements.componentsCounter++;
				}
			} );

			this.extraShortcodes.addData( FusionApp.data.shortcodeMap );
			this.dynamicValues.addData( FusionApp.initialData.dynamicValues, FusionApp.data.dynamicOptions );
		},

		convertGalleryElement: function( content ) {
			var regExp      = window.wp.shortcode.regexp( 'fusion_gallery' ),
				innerRegExp = this.regExpShortcode( 'fusion_gallery' ),
				matches     = content.match( regExp ),
				newContent  = content,
				fetchIds    = [];

			if ( matches ) {
				_.each( matches, function( shortcode ) {
					var shortcodeElement    = shortcode.match( innerRegExp ),
						shortcodeAttributes = '' !== shortcodeElement[ 3 ] ? window.wp.shortcode.attrs( shortcodeElement[ 3 ] ) : '',
						children     = '',
						newShortcode = '',
						ids;

					// Check for the old format shortcode
					if ( 'undefined' !== typeof shortcodeAttributes.named.image_ids ) {
						ids = shortcodeAttributes.named.image_ids.split( ',' );

						// Add new children shortcodes
						_.each( ids, function( id ) {
							children += '[fusion_gallery_image image="" image_id="' + id + '" /]';
							fetchIds.push( id );
						} );

						// Add children shortcodes, remove image_ids attribute.
						newShortcode = shortcode.replace( '/]', ']' + children + '[/fusion_gallery]' ).replace( 'image_ids="' + shortcodeAttributes.named.image_ids + '" ', '' );

						// Replace the old shortcode with the new one
						newContent = newContent.replace( shortcode, newShortcode );
					}
				} );

				// Fetch attachment data
				if ( 0 < fetchIds.length ) {
					wp.media.query( { post__in: fetchIds, posts_per_page: fetchIds.length } ).more();
				}
			}

			return newContent;
		},

		/**
		 * Converts the shortcodes to builder elements.
		 *
		 * @since 2.0.0
		 * @param {string} content - The content.
		 * @return {void}
		 */
		createBuilderLayout: function( content ) {
			var self = this;

			if ( FusionApp.data.is_fusion_element ) {
				content = self.validateLibraryContent( content );
			}

			content = this.convertGalleryElement( content );

			this.shortcodesToBuilder( content );

			this.builderToShortcodes();

			setTimeout( function() {
				self.scrollingContainers();
				self.maybeFormStyles();
				self.maybeOfCanvasStyles();
			}, 100 );
		},

		/**
		 * Validate library content.
		 *
		 * @since 2.0.0
		 * @param {string} content - The content.
		 * @return {string}
		 */
		validateLibraryContent: function( content ) {
			var contentIsEmpty = '' === content,
				openContainer  = '[fusion_builder_container type="flex" hundred_percent="no" flex_column_spacing="' + FusionApp.settings.col_spacing + '" equal_height_columns="no" menu_anchor="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" class="" id="" background_color="" background_image="" background_position="center center" background_repeat="no-repeat" fade="no" background_parallax="none" parallax_speed="0.3" video_mp4="" video_webm="" video_ogv="" video_url="" video_aspect_ratio="16:9" video_loop="yes" video_mute="yes" overlay_color="" overlay_opacity="0.5" video_preview_image="" border_size="" border_color="" border_style="solid" padding_top="" padding_bottom="" padding_left="" padding_right=""][fusion_builder_row]',
				closeContainer = '[/fusion_builder_row][/fusion_builder_container]',
				openColumn     = '[fusion_builder_column type="1_1" background_position="left top" background_color="" border_size="" border_color="" border_style="solid" border_position="all" spacing="yes" background_image="" background_repeat="no-repeat" padding="" margin_top="0px" margin_bottom="0px" class="" id="" animation_type="" animation_speed="0.3" animation_direction="left" hide_on_mobile="small-visibility,medium-visibility,large-visibility" center_content="no" last="no" min_height="" hover_type="none" link=""]',
				closeColumn    = '[/fusion_builder_column]',
				columnEdit     = 'columns' === FusionApp.data.fusion_element_type || 'post_cards' === FusionApp.data.fusion_element_type,
				elementEdit    = 'elements' === FusionApp.data.fusion_element_type,
				containerEdit  = 'sections' === FusionApp.data.fusion_element_type;

			// The way it is setup now, we dont want blank page template on library items.
			if ( columnEdit && '[fusion_builder_blank_page][/fusion_builder_blank_page]' === content ) {
				content        = openColumn + closeColumn;
				contentIsEmpty = false;
			}

			if ( elementEdit && '[fusion_builder_blank_page][/fusion_builder_blank_page]' === content ) {
				content        = '';
				contentIsEmpty = true;
			}

			if ( containerEdit && '[fusion_builder_blank_page][/fusion_builder_blank_page]' === content ) {
				content        = openContainer + closeContainer;
				contentIsEmpty = false;
			}

			if ( ! contentIsEmpty ) {
				// Editing element
				if ( elementEdit ) {
					content = openContainer + openColumn + content + closeColumn + closeContainer;
				} else if ( columnEdit ) {
					content = openContainer + content + closeContainer;
				}
			} else {
				// If library element is blank
				if ( elementEdit ) { // eslint-disable-line no-lonely-if
					content = openContainer + openColumn + closeColumn + closeContainer;
				}
			}

			function replaceDollars() {
				return '$$';
			}

			content = content.replace( /&#36;&#36;/g, replaceDollars );

			return content;
		},

		/**
		 * Convert library content to shortcodes.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		libraryBuilderToShortcodes: function() {
			var shortcode = '',
				cid,
				view,
				element;

			// Editing an element.
			if ( 'elements' === FusionApp.data.fusion_element_type ) {
				// Inner row.
				element = this.$el.find( '.fusion-builder-column-outer .fusion_builder_row_inner' );

				if ( element.length ) {
					cid       = element.data( 'cid' );
					view      = FusionPageBuilderViewManager.getView( cid );
					shortcode = view.getInnerRowContent();

				// Regular element.
				} else if ( this.$el.find( '.fusion-builder-live-element' ).length ) {
					element = this.$el.find( '.fusion-builder-live-element' );
					shortcode = FusionPageBuilderApp.generateElementShortcode( this.$el.find( '.fusion-builder-live-element' ), false );
				}
				if ( element.length ) {
					this.$el.find( '.fusion-builder-column .fusion-builder-add-element' ).hide();
				}

			// Editing a column.
			} else if ( 'columns' === FusionApp.data.fusion_element_type || 'post_cards' === FusionApp.data.fusion_element_type ) {
				element = this.$el.find( '.fusion-builder-column-outer' );

				if ( element.length ) {
					cid       = element.data( 'cid' );
					view      = FusionPageBuilderViewManager.getView( cid );
					shortcode = view.getColumnContent();
				}

			// Editing a container.
			} else if ( 'sections' === FusionApp.data.fusion_element_type ) {
				element = this.$el.find( '.fusion-builder-container' );

				if ( element.length ) {
					cid       = element.data( 'cid' );
					view      = FusionPageBuilderViewManager.getView( cid );
					shortcode = view.getContent();
				}
			}

			FusionApp.setPost( 'post_content', shortcode );

			this.navigator.update();
		},

		/**
		 * Build the initial layout for the builder.
		 *
		 * @since 2.0.0
		 * @param {Object} data - The data.
		 * @param {Object} data.fusionGlobalManager - The FusionPageBuilder.Global object.
		 * @param {string} data.postContent - The post-content.
		 * @return {void}
		 */
		initialBuilderLayout: function( data ) {
			var self = this;

			// Clear all views
			FusionPageBuilderViewManager.removeViews();

			this.postContent = data.postDetails.post_content;
			this.postID      = data.postDetails.post_id;

			// Add data for exta shortcodes.
			self.updateData( data );

			setTimeout( function() {

				var content            = self.postContent,
					contentErrorMarkup = '',
					contentErrorTitle  = '',
					moreDetails        = fusionBuilderText.unknown_error_link;

				try {
					self.setGlobalStyles( '', true );

					content = self.convertGalleryElement( content );

					if ( ! FusionApp.data.is_fusion_element ) {
						content = self.validateContent( content );
					} else {
						content = self.validateLibraryContent( content );
					}

					self.shortcodesToBuilder( content );

					// Add data for exta shortcodes.
					self.updateData( data );

					setTimeout( function() {
						self.scrollingContainers();
						self.reRenderElements = true;
						self.maybeFormStyles();
						self.maybeOfCanvasStyles();

						if ( 0 < FusionPageBuilderViewManager.countElementsByType( 'fusion_builder_next_page' ) ) {
							FusionEvents.trigger( 'fusion-next-page' );
						}

						self.loaded = true;
						FusionEvents.trigger( 'fusion-builder-loaded' );

					}, 100 );

				} catch ( error ) {
					console.log( error ); // jshint ignore:line

					if ( 'undefined' !== error.name && 'ContentException' === error.name ) {
						contentErrorTitle  = fusionBuilderText.content_error_title;
						contentErrorMarkup = jQuery( '<div>' + fusionBuilderText.content_error_description + '</div>' );
					} else {
						contentErrorTitle  = fusionBuilderText.unknown_error_title;

						// If we have full stack use that rather than external link.
						if ( 'string' === typeof error.stack ) {
							moreDetails = '<a href="#" class="copy-full-description">' + fusionBuilderText.unknown_error_copy + '</a>';
						}
						contentErrorMarkup = jQuery( '<div>' + error + '<p>' + moreDetails + '</p></div>' );
					}

					contentErrorMarkup.dialog( {
						title: contentErrorTitle,
						dialogClass: 'fusion-builder-dialog fusion-builder-error-dialog fusion-builder-settings-dialog',
						autoOpen: true,
						modal: true,
						width: 400,
						open: function() {
							if ( jQuery( this ).find( '.copy-full-description' ).length ) {
								jQuery( this ).find( '.copy-full-description' ).on( 'click', function( event ) {
									var $temp     = jQuery( '<textarea>' ),
										errorText = '';

									if ( 'string' === typeof error.message ) {
										errorText += error.message + '\n';
									}

									if ( 'string' === typeof error.stack ) {
										errorText += error.stack;
									}
									event.preventDefault();

									jQuery( this ).after( $temp );
									$temp.val( errorText ).focus().select();
									document.execCommand( 'copy' );
									$temp.remove();

									jQuery( this ).html( '<i class="fusiona-check" aria-hidden="true"></i> ' + fusionBuilderText.unknown_error_copied );
								} );
							}
						},
						create: function( event, ui ) {
							// Add Title.
							jQuery( this ).siblings().find( 'span.ui-dialog-title' ).prepend( '<span class="icon type-warning"><i class="fusiona-exclamation" aria-hidden="true"></i></span>' );
						},
						close: function() {} // eslint-disable-line no-empty-function
					} );

					// Remove all views.
					self.fusionBuilderReset();
				}

			}, 50 );

			// TODO - are the checks here necessary?  I don't see any data.fusionGlobalManager.
			window.fusionGlobalManager = 'undefined' !== typeof data.fusionGlobalManager && false !== data.fusionGlobalManager ? data.fusionGlobalManager : new FusionPageBuilder.Globals( ); // jshint ignore: line
		},

		FBException: function( message, name ) {
			this.message = message;
			this.name = name;
		},

		validateContent: function( content ) {
			var contentIsEmpty = '' === content,
				textNodes      = '',
				columns        = [],
				containers     = [],
				shortcodeTags,
				columnwrapped,
				insertionFlag;

			// Content clean up.
			content = content.replace( /\r?\n/g, '\r\n' );
			content = content.replace( /<p>\[/g, '[' );
			content = content.replace( /\]<\/p>/g, ']' );
			if ( 'undefined' !== typeof content ) {
				content = content.trim();
			}

			// Throw exception with the fullwidth shortcode.
			if ( -1 !== content.indexOf( '[fullwidth' ) ) {
				throw new this.FBException( 'Avada 4.0.3 or earlier fullwidth container used!', 'ContentException' );
			}

			if ( ! contentIsEmpty ) {

				// Fixes [fusion_text /] instances, which were created in 5.0.1 for empty text blocks.
				content = content.replace( /\[fusion\_text \/\]/g, '[fusion_text][/fusion_text]' ).replace(  /\[\/fusion\_text\]\[\/fusion\_text\]/g, '[/fusion_text]' );

				content = content.replace( /\$\$/g, '&#36;&#36;' );
				textNodes = content;

				// Add container if missing.
				textNodes = wp.shortcode.replace( 'fusion_builder_container', textNodes, function() {
					return '@|@';
				} );
				textNodes = wp.shortcode.replace( 'fusion_builder_next_page', textNodes, function() {
					return '@|@';
				} );
				textNodes = wp.shortcode.replace( 'fusion_woo_checkout_form', textNodes, function() {
					return '@|@';
				} );
				textNodes = textNodes.trim().split( '@|@' );

				_.each( textNodes, function( textNodes ) {
					if ( '' !== textNodes.trim() ) {
						content = content.replace( textNodes, '[fusion_builder_container type="flex" hundred_percent="no" equal_height_columns="no" menu_anchor="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" class="" id="" background_color="" background_image="" background_position="center center" background_repeat="no-repeat" fade="no" background_parallax="none" parallax_speed="0.3" video_mp4="" video_webm="" video_ogv="" video_url="" video_aspect_ratio="16:9" video_loop="yes" video_mute="yes" overlay_color="" overlay_opacity="0.5" video_preview_image="" border_size="" border_color="" border_style="solid" padding_top="" padding_bottom="" padding_left="" padding_right=""][fusion_builder_row]' + textNodes + '[/fusion_builder_row][/fusion_builder_container]' );
					}
				} );

				textNodes = wp.shortcode.replace( 'fusion_builder_container', content, function( tag ) {
					containers.push( tag.content );
				} );

				_.each( containers, function( textNodes ) {

					// Add column if missing.
					textNodes = wp.shortcode.replace( 'fusion_builder_row', textNodes, function( tag ) {
						return tag.content;
					} );

					textNodes = wp.shortcode.replace( 'fusion_builder_column', textNodes, function() {
						return '@|@';
					} );

					textNodes = textNodes.trim().split( '@|@' );
					_.each( textNodes, function( textNodes ) {
						if ( '' !== textNodes.trim() && '[fusion_builder_row][/fusion_builder_row]' !== textNodes.trim() ) {
							columnwrapped = '[fusion_builder_column type="1_1" background_position="left top" background_color="" border_size="" border_color="" border_style="solid" border_position="all" spacing="yes" background_image="" background_repeat="no-repeat" padding="" margin_top="0px" margin_bottom="0px" class="" id="" animation_type="" animation_speed="0.3" animation_direction="left" hide_on_mobile="small-visibility,medium-visibility,large-visibility" center_content="no" last="no" min_height="" hover_type="none" link=""]' + textNodes + '[/fusion_builder_column]';
							content = content.replace( textNodes, columnwrapped );

						}
					} );
				} );

				textNodes = wp.shortcode.replace( 'fusion_builder_column_inner', content, function( tag ) {
					columns.push( tag.content );
				} );
				textNodes = wp.shortcode.replace( 'fusion_builder_column', content, function( tag ) {
					columns.push( tag.content );
				} );
				_.each( columns, function( textNodes ) {

					// Wrap non fusion elements.
					shortcodeTags = fusionAllElements;
					_.each( shortcodeTags, function( shortcode ) {
						if ( 'undefined' === typeof shortcode.generator_only ) {
							textNodes = wp.shortcode.replace( shortcode.shortcode, textNodes, function() {
								return '@|@';
							} );
						}
					} );

					textNodes = textNodes.trim().split( '@|@' );
					_.each( textNodes, function( textNodes ) {
						if ( '' !== textNodes.trim() ) {
							insertionFlag = '@=%~@';
							if ( '@' === textNodes.slice( -1 ) ) {
								insertionFlag = '#=%~#';
							}
							content = content.replace( textNodes, '[fusion_text]' + textNodes.slice( 0, -1 ) + insertionFlag + textNodes.slice( -1 ) + '[/fusion_text]' );
						}
					} );
				} );
				content = content.replace( /@=%~@/g, '' ).replace( /#=%~#/g, '' );

				// Check for once deactivated elements in text blocks that are active again.
				content = wp.shortcode.replace( 'fusion_text', content, function( tag ) {
					shortcodeTags = fusionAllElements;
					textNodes = tag.content;
					_.each( shortcodeTags, function( shortcode ) {
						if ( 'undefined' === typeof shortcode.generator_only ) {
							textNodes = wp.shortcode.replace( shortcode.shortcode, textNodes, function() {
								return '|';
							} );
						}
					} );
					if ( ! textNodes.replace( /\|/g, '' ).length ) {
						return tag.content;
					}
				} );
			}

			function replaceDollars() {
				return '$$';
			}

			content = content.replace( /&#36;&#36;/g, replaceDollars );

			return content;
		},

		/**
		 * Regex for shortcodes
		 *
		 * @since 2.0.0
		 * @param {string} tag - The element.
		 * @returns {mixed}
		 */
		regExpShortcode: _.memoize( function( tag ) {
			return new RegExp( '\\[(\\[?)(' + tag + ')(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*(?:\\[(?!\\/\\2\\])[^\\[]*)*)(\\[\\/\\2\\]))?)(\\]?)' );
		} ),

		findShortcodeMatches: function( content, match ) {

			var shortcodeMatches,
				shortcodeRegExp;

			if ( _.isObject( content ) ) {
				content = content.value;
			}

			shortcodeMatches     = '';
			content              = 'undefined' !== typeof content ? content : '';
			shortcodeRegExp      = window.wp.shortcode.regexp( match );

			if ( 'undefined' !== typeof content && '' !== content ) {
				shortcodeMatches = content.match( shortcodeRegExp );
			}

			return shortcodeMatches;
		},

		/**
		 * Reset the builder.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		fusionBuilderReset: function() {

			// Clear all models and views
			FusionPageBuilderElements.reset( {} );
			this.collection.reset( {} );
			FusionPageBuilderViewManager.clear();

			// Clear layout
			this.$el.find( '#fusion_builder_container' ).html( '' );

			FusionEvents.trigger( 'fusion-builder-reset' );

		},

		/**
		 * Clears the layout.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The jQuery event.
		 * @return {void}
		 */
		clearLayout: function( event ) {
			if ( event ) {
				event.preventDefault();
			}

			if ( '[fusion_builder_blank_page][/fusion_builder_blank_page]' !== this.postContent ) {
				this.blankPage = true;
				this.clearBuilderLayout( true );
			}
		},

		/**
		 * Trigger context menu.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The jQuery event.
		 * @return {void}
		 */
		contextMenu: function( event ) {
			var self          = this,
				$clickTarget  = jQuery( event.target ),
				$target       = $clickTarget.closest( '[data-cid]' ),
				inlineElement = $clickTarget.hasClass( 'fusion-disable-editing' ) || $clickTarget.parents( '.fusion-disable-editing' ).length,
				shortcodeId,
				view,
				viewSettings,
				model,
				elementType;

			// Remove any existing.
			this.removeContextMenu();

			// Disable on blank template element.
			if ( $clickTarget.hasClass( 'fusion-builder-blank-page' ) || $clickTarget.parents( '.fusion-builder-blank-page' ).length ) {
				return;
			}

			// Disable on row.
			if ( $clickTarget.hasClass( 'fusion-builder-row-container' ) ) {
				$clickTarget = $clickTarget.closest( '.fusion-builder-container:not( .fusion-builder-row-container )' );
				$target      = $clickTarget;
			}

			// Disable context menu if right clicking on text block.
			if ( ! $target.length || ( 'fusion_text' === $target.data( 'type' ) && ! $clickTarget.parents( '.fusion-builder-module-controls-container' ).length && ! inlineElement ) ) {
				return;
			}

			// If we are not editing nested columns element, but clicking on a child, only use the nested columns element.
			if ( ! jQuery( 'body' ).hasClass( 'nested-ui-active' ) && $clickTarget.closest( '.fusion_builder_row_inner' ).length ) {
				$target = $clickTarget.closest( '.fusion_builder_row_inner' );
			}


			view = FusionPageBuilderViewManager.getView( $target.data( 'cid' ) );

			elementType = this.getElementType( view.model.attributes.element_type );

			switch ( FusionApp.data.fusion_element_type ) {

				case 'elements':
					if ( 'child_element' !== elementType && 'parent_element' !== elementType && 'element' !== elementType ) {
						return;
					}

					break;

				case 'columns':
				case 'post_cards':
					if ( 'fusion_builder_container' === elementType ) {
						return;
					}

					break;
			}

			if ( ! inlineElement ) {

				// This is not an inline element, things are simple.
				event.preventDefault();

				viewSettings = {
					model: {
						parent: view.model,
						event: event,
						parentView: view
					}
				};

				this.contextMenuView = new FusionPageBuilder.ContextMenuView( viewSettings );

			} else {

				// This is an inline element, so we have to create the data and so checks.
				$target     = $clickTarget.hasClass( 'fusion-disable-editing' ) ? $clickTarget : $clickTarget.parents( '.fusion-disable-editing' ).first();

				// Disable on inline ajax shortcodes.
				if ( $target.hasClass( 'fusion-inline-ajax' ) ) {
					return;
				}

				shortcodeId = $target.data( 'id' );
				model       = view.model.inlineCollection.find( function( model ) {
					return model.get( 'cid' ) == shortcodeId; // jshint ignore: line
				} );

				if ( 'undefined' !== typeof model ) {
					event.preventDefault();

					model.event      = event;
					model.$target    = $target;
					model.parentView = view;

					viewSettings = {
						model: model
					};

					this.contextMenuView = new FusionPageBuilder.ContextMenuInlineView( viewSettings );
				}
			}

			// Check context menu is not undefined and is not false.
			if ( 'undefined' !== typeof this.contextMenuView && this.contextMenuView ) {

				// Add context menu to builder.
				this.$el.append( this.contextMenuView.render().el );

				// Add listener to remove.
				this.$el.one( 'click', function() {
					self.removeContextMenu();
				} );
			}
		},

		/**
		 * Remove any contextMenu.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		removeContextMenu: function() {
			if ( this.contextMenuView && 'function' === typeof this.contextMenuView.removeMenu ) {
				this.contextMenuView.removeMenu();
			}
		},

		/**
		 * Shows saved elements.
		 *
		 * @since 2.0.0
		 * @param {string} elementType - The element-type.
		 * @param {Object} container - The jQuery element of the container.
		 * @return {void}
		 */
		showSavedElements: function( elementType, container ) {

			var data    = jQuery( '#fusion-builder-layouts-' + elementType ).find( '.fusion-page-layouts' ).clone(),
				spacers = '<li class="spacer fusion-builder-element"></li><li class="spacer fusion-builder-element"></li><li class="spacer fusion-builder-element"></li><li class="spacer fusion-builder-element"></li>',
				postId;

			data.find( 'li' ).each( function() {
				postId = jQuery( this ).find( '.fusion-builder-demo-button-load' ).attr( 'data-post-id' );
				jQuery( this ).find( '.fusion-layout-buttons' ).remove();
				jQuery( this ).find( 'h4' ).attr( 'class', 'fusion_module_title' );
				jQuery( this ).attr( 'data-layout_id', postId );
				jQuery( this ).addClass( 'fusion_builder_custom_' + elementType + '_load' );
				if ( '' !== jQuery( this ).attr( 'data-layout_type' ) ) {
					jQuery( this ).addClass( 'fusion-element-type-' + jQuery( this ).attr( 'data-layout_type' ) );
				}
			} );

			container.append( '<div id="fusion-loader"><span class="fusion-builder-loader"></span></div>' );
			container.append( '<ul class="fusion-builder-all-modules fusion-builder-library-list fusion-builder-library-list-' + elementType + '">' + data.html() + spacers + '</div>' );
		},

		/**
		 * Renders the content, converting all shortcodes and loading their templates.
		 *
		 * @since 2.0.0
		 * @param {string}  content - The content we want to convert & render.
		 * @param {number} cid - A Unique ID.
		 * @return {string} The content.
		 */
		renderContent: function( content, cid, parent ) {
			var shortcodes,
				self = this,
				elParent = 'undefined' === typeof parent ? false : parent,
				ajaxShortcodes = [],
				insideInlineEditor,
				hasFusionClients = false;

			parent = FusionPageBuilderViewManager.getView( cid );

			if ( parent && 'function' === typeof parent.filterRenderContent ) {
				content = parent.filterRenderContent( content );
			}

			// If no signs of a shortcode return early, avoid any unnecessary checks.
			if ( 'undefined' === typeof content ) {
				return '';
			}
			if ( -1 === content.indexOf( '[' ) ) {
				return content;
			}

			if ( 'undefined' !== typeof parent ) {
				// Reset inlines collection
				parent.model.inlineCollection.reset();

				// Check if shortcode allows generator
				insideInlineEditor = this.inlineEditorHelpers.inlineEditorAllowed( parent.model.get( 'element_type' ) );
			}

			shortcodes = this.shortcodesToBuilder( content, false, false, true );

			_.each( shortcodes, function( shortcode ) {
				var markupContent,
					newModel,
					newViewOutput;

				// Check for deprecated shortcode
				if ( 'fusion_clients' === shortcode.settings.element_type ) {
					hasFusionClients = true;
				}

				if ( -1 !== jQuery.inArray( shortcode.settings.element_type, FusionPageBuilderApp.inlineElements ) ) {

					// Create new model
					shortcode.settings.cid           = FusionPageBuilderViewManager.generateCid();
					shortcode.settings.parent        = cid;
					shortcode.settings.inlineElement = shortcode.content;
					newModel                         = new FusionPageBuilder.Element( shortcode.settings );
					parent.model.inlineCollection.add( newModel );

					newViewOutput = self.inlineEditorHelpers.getInlineElementMarkup( newModel );

					if ( insideInlineEditor ) {
						content = content.replace( shortcode.content, '<span class="fusion-disable-editing fusion-inline-element" contenteditable="false" data-id="' + shortcode.settings.cid + '">' + newViewOutput.trim() + '</span>' );
					} else {
						content = content.replace( shortcode.content, newViewOutput );
					}

				} else {
					markupContent = FusionPageBuilderApp.extraShortcodes.byShortcode( shortcode.content );

					if ( 'undefined' === typeof markupContent ) {
						ajaxShortcodes.push( shortcode.content );
					} else if ( insideInlineEditor ) {
						content = content.replace( shortcode.content, self.inlineEditorHelpers.getInlineHTML( markupContent.output, markupContent.id ) );
					} else {
						content = content.replace( shortcode.content, markupContent.output );
					}
				}

			} );

			if ( ajaxShortcodes.length ) {
				content = self.ajaxRenderShortcode( cid, content, elParent, ajaxShortcodes );
			}

			content = FusionApp.removeScripts( content, cid );

			setTimeout( function() {
				FusionApp.injectScripts( cid );

				if ( hasFusionClients ) {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-element-render-fusion_images', cid );
				}
			}, 200 );

			if ( 'undefined' !== content ) {
				return content;
			}
			return '<div id="fusion-loader"><span class="fusion-builder-loader"></span></div>';
		},

		ajaxRenderShortcode: function( cid, content, elParent, ajaxShortcodes ) {
			var model, modelcid, markup;

			// If content is identical to another request we do not want to loop ajax.
			if ( -1 !== this.ajaxContentRequests.indexOf( content ) ) {
				modelcid = 'undefined' !== typeof elParent && elParent ? elParent : cid;
				model    = FusionPageBuilderElements.find( function( model ) {
					return model.get( 'cid' ) == modelcid; // jshint ignore: line
				} );
				markup  = 'undefined' !== typeof model ? model.get( 'markup' ) : false;

				// Collect views that need to be re-rendered
				this.viewsToRerender.push( cid );

				// Check for model markup being set.
				if ( markup ) {
					return markup.output;
				}

				// Just return unchanged.
				return content;
			}

			// If cid is the same debounce, otherwise do not.
			if ( cid === this.lastAjaxCid ) {
				this._fusion_do_shortcode( cid, content, elParent, ajaxShortcodes );
			} else {
				FusionApp.callback.fusion_do_shortcode( cid, content, elParent, ajaxShortcodes );
			}

			this.ajaxContentRequests.push( content );
			this.lastAjaxCid = cid;
		},

		addPlaceholder: function( content, output ) {
			var atts,
				notice;

			// Add placeholder if no option is selected.
			if ( -1 !== content.indexOf( 'rev_slider' ) ) {
				atts = content.match( /\[rev_slider .*?alias\=\"(.*?)\".*?\]/ );
				notice = fusionBuilderText.slider_placeholder;
			} else if ( -1 !== content.indexOf( 'layerslider' ) ) {
				atts = content.match( /\[layerslider .*?id\=\"(.*?)\".*?\]/ );
				notice = fusionBuilderText.slider_placeholder;
			} else if ( -1 !== content.indexOf( 'contact-form-7' ) ) {
				atts = content.match( /\[contact-form-7 .*?id\=\"(.*?)\".*?\]/ );
				notice = fusionBuilderText.form_placeholder;
			} else if ( -1 !== content.indexOf( 'gravityform' ) ) {
				atts = content.match( /\[gravityform .*?id\=\"(.*?)\".*?\]/ );
				notice = fusionBuilderText.form_placeholder;
			}

			if ( 'undefined' !== typeof atts && atts && ( '0' === atts[ 1 ] || '' === atts[ 1 ] ) ) {
				output    = '<div class="fusion-builder-placeholder">' + notice + '</div>';
			}

			return output;
		},

		/**
		 * Get the shortcode structure from HTML.
		 *
		 * @since 2.0.0
		 * @param {string}  content - The content we want to convert & render.
		 * @param {number} cid - A Unique ID.
		 * @return {string} The content.
		 */
		htmlToShortcode: function( content, cid ) { // jshint ignore:line
			var $content = jQuery( '<div>' + content + '</div>' ),
				self = this;

			$content.find( '.fusion-disable-editing' ).each( function() {
				var shortcodeId = jQuery( this ).data( 'id' ),
					shortcodeContent,
					shortCode,
					parent,
					model;

				if ( jQuery( this ).hasClass( 'fusion-inline-ajax' ) ) {
					shortcodeContent = FusionPageBuilderApp.extraShortcodes.byId( shortcodeId );
					shortCode = shortcodeContent.shortcode;
				} else {
					parent = FusionPageBuilderViewManager.getView( cid );
					model = parent.model.inlineCollection.find( function( model ) {
						return model.get( 'cid' ) == shortcodeId; // jshint ignore: line
					} );
					shortCode = model.get( 'inlineElement' );
				}

				if ( 'undefined' === typeof shortCode ) {
					alert( 'Problem encountered. This content cannot be live edited.' ); // eslint-disable-line no-alert
				} else {
					jQuery( this ).replaceWith( shortCode );
				}
			} );

			$content.find( '[data-inline-shortcode]' ).each( function() {
				var shortcodeType    = jQuery( this ).data( 'element' ),
					shortcodeContent = jQuery( this ).html(),
					defaultParams,
					multi,
					type,
					params,
					elementSettings,
					elementModel,
					elementShortcode;

				if ( shortcodeType in fusionAllElements ) {

					defaultParams  = fusionAllElements[ shortcodeType ].params;
					multi          = fusionAllElements[ shortcodeType ].multi;
					type           = fusionAllElements[ shortcodeType ].shortcode;

				} else {
					defaultParams = '';
					multi   = '';
					type   = '';
				}

				params = {};

				// Process default parameters from shortcode
				_.each( defaultParams, function( param )  {
					params[ param.param_name ] = ( _.isObject( param.value ) ) ? param[ 'default' ] : param.value;
				} );

				// Used as a flag for opening on first render.
				params.open_settings   = 'true';

				params.element_content = shortcodeContent;

				elementSettings = {
					type: 'element',
					added: 'manually',
					element_type: type,
					params: params,
					parent: cid,
					multi: multi
				};

				elementModel = new FusionPageBuilder.Element( elementSettings );

				elementShortcode = self.generateElementShortcode( elementModel, false, true );

				jQuery( this ).replaceWith( elementShortcode );
			} );
			return $content.html();
		},

		renderElement: function( shortcodeType, shortcodeArgs, shortcodeContent, parentCID ) {
			var defaultParams,
				params,
				multi,
				type,
				elementSettings,
				elementModel,
				elementShortcode,
				generatedViewSettings,
				markup,
				generatedView;

			if ( 'undefined' === typeof shortcodeType ) {
				return '';
			}

			if ( shortcodeType in fusionAllElements ) {
				defaultParams  = fusionAllElements[ shortcodeType ].params;
				type           = fusionAllElements[ shortcodeType ].shortcode;
			}

			params = {};

			// Process default parameters from shortcode
			_.each( defaultParams, function( param )  {
				params[ param.param_name ] = ( _.isObject( param.value ) ) ? param[ 'default' ] : param.value;
			} );

			// Process args passing in.
			if ( 'object' === typeof shortcodeArgs ) {
				_.each( shortcodeArgs, function( value, param_name )  {
					params[ param_name ] = value;
				} );
			}

			// Used as a flag for opening on first render.
			params.open_settings   = 'false';

			// Add content if passed in.
			params.element_content = 'string' === typeof shortcodeContent ? shortcodeContent : '';

			elementSettings = {
				type: 'element',
				added: 'manually',
				element_type: type,
				params: params,
				parent: parentCID
			};

			elementModel = new FusionPageBuilder.Element( elementSettings );

			generatedViewSettings = {
				model: elementModel
			};

			generatedView = new FusionPageBuilder[ elementModel.get( 'element_type' ) ]( generatedViewSettings );

			markup = generatedView.getTemplate();

			generatedView.remove();

			return markup;
		},

		/**
		 * Convert shortcodes for the builder.
		 *
		 * @since 2.0.0
		 * @param {string}  content - The content.
		 * @param {number} parentCID - The parent CID.
		 * @param {string}  childShortcode - The shortcode.
		 * @param {boolean} noCollection - To collect or not collect.
		 * @param {string} targetEl - If we want to add in relation to a specific element.
		 * @param {string} targetPosition - Whether we want to be before or after specific element.
		 * @return {string|null}
		 */
		shortcodesToBuilder: function( content, parentCID, childShortcode, noCollection, targetEl, targetPosition ) {
			var thisEl,
				regExp,
				innerRegExp,
				matches,
				shortcodeTags,
				renderElements = [];

			noCollection = ( 'undefined' !== typeof noCollection && true === noCollection );

			// Show blank page layout
			if ( '' === content && ! this.$el.find( '.fusion-builder-blank-page-content' ).length ) {
				this.blankPage = true;
				// TODO: add fix for new blank library element
				this.createBuilderLayout( '[fusion_builder_blank_page][/fusion_builder_blank_page]' );
				jQuery( '.fusion-builder-live' ).addClass( 'fusion-builder-blank-page-active' );

				if ( false === FusionApp.initialData.samePage && 'undefined' !== typeof FusionApp.sidebarView.openOption ) {
					FusionApp.sidebarView.openOption( 'post_title', 'po' );
				}

				return;
			}
			jQuery( '.fusion-builder-live' ).removeClass( 'fusion-builder-blank-page-active' );

			thisEl        = this;
			shortcodeTags = _.keys( fusionAllElements ).join( '|' );

			// TEMP.  Example of non FB shortcode, used for the renderContent function.
			// TODO:  Add a new function for finding shortcodes instead of hijacking shortcodesToBuilder.
			if ( noCollection && 'undefined' !== typeof fusionVendorShortcodes ) {
				shortcodeTags += '|' + _.keys( fusionVendorShortcodes ).join( '|' );
			}
			regExp      = window.wp.shortcode.regexp( shortcodeTags );
			innerRegExp = this.regExpShortcode( shortcodeTags );
			matches     = content.match( regExp );

			_.each( matches, function( shortcode ) {

				var shortcodeElement    = shortcode.match( innerRegExp ),
					shortcodeName       = shortcodeElement[ 2 ],
					shortcodeAttributes = '' !== shortcodeElement[ 3 ] ? window.wp.shortcode.attrs( shortcodeElement[ 3 ] ) : '',
					shortcodeContent    = 'undefined' !== typeof shortcodeElement[ 5 ] ? shortcodeElement[ 5 ] : '',
					elementCID          = ( ! noCollection ) ? FusionPageBuilderViewManager.generateCid() : '',
					prefixedAttributes  = { params: ( {} ) },
					elementSettings,
					key,
					prefixedKey,
					dependencyOption,
					dependencyOptionValue,
					elementContent,
					alpha,
					paging,
					markupContent,
					values,
					atIndex,
					buttonPrefix,
					textType,
					fontFamily,
					fontVariant,
					elementFonts,

					// Check for shortcodes inside shortcode content
					shortcodesInContent = 'undefined' !== typeof shortcodeContent && '' !== shortcodeContent && shortcodeContent.match( regExp ),

					// Check if shortcode allows generator
					allowGenerator = 'undefined' !== typeof fusionAllElements[ shortcodeName ] && 'undefined' !== typeof fusionAllElements[ shortcodeName ].allow_generator ? fusionAllElements[ shortcodeName ].allow_generator : '';

				elementSettings = {
					type: shortcodeName,
					element_type: shortcodeName,
					cid: elementCID,
					created: 'manually',
					multi: '',
					params: {},
					container: false,
					allow_generator: allowGenerator,
					inline_editor: thisEl.inlineEditorHelpers.inlineEditorAllowed( shortcodeName )
				};

				// Get markup from map if set.  Add further checks here so only necessary elements do this check.
				if ( -1 === shortcodeName.indexOf( 'fusion_builder_' ) ) {
					markupContent = FusionPageBuilderApp.extraShortcodes.byShortcode( shortcodeElement[ 0 ] );
					if ( 'undefined' !== typeof markupContent ) {
						elementSettings.markup = markupContent;
					} else {
						elementSettings.shortcode = shortcodeElement[ 0 ];
					}
				}
				if ( 'fusion_builder_container' === shortcodeName || 'fusion_builder_row' === shortcodeName || 'fusion_builder_row_inner' === shortcodeName || 'fusion_builder_column' === shortcodeName || 'fusion_builder_column_inner' === shortcodeName || 'fusion_builder_inline' === shortcodeName ) {
					elementSettings.container = true;
				}

				if ( 'fusion_builder_container' !== shortcodeName || 'fusion_builder_next_page' !== shortcodeName || 'fusion_woo_checkout_form' !== shortcodeName ) {
					elementSettings.parent = parentCID;
				}

				if ( 'undefined' !== typeof targetEl && targetEl ) {
					atIndex = FusionPageBuilderApp.getCollectionIndex( targetEl );

					if ( 'undefined' !== typeof targetPosition && 'before' === targetPosition ) {
						atIndex = atIndex - 1;
					}
					elementSettings.targetElement = targetEl;
					elementSettings.at_index = atIndex;
				}
				if ( 'undefined' !== typeof targetPosition && targetPosition ) {
					elementSettings.targetElementPosition = targetPosition;
				}
				if ( false === elementSettings.container && 'fusion_builder_next_page' !== shortcodeName && 'fusion_woo_checkout_form' !== shortcodeName ) {

					if ( -1 !== shortcodeName.indexOf( 'fusion_' ) ||
						-1 !== shortcodeName.indexOf( 'layerslider' ) ||
						-1 !== shortcodeName.indexOf( 'rev_slider' ) ||
						'undefined' !== typeof fusionAllElements[ shortcodeName ] ) {
						elementSettings.type = 'element';
					}
				}

				if ( 'fusion_builder_blank_page' === shortcodeName ) {
					elementSettings.type = 'fusion_builder_blank_page';
				}

				if ( 'fusion_tb_meta' === shortcodeName ) {
					// Border sizes.
					if ( ( 'undefined' === typeof shortcodeAttributes.named.border_top ||
						'undefined' === typeof shortcodeAttributes.named.border_bottom ||
						'undefined' === typeof shortcodeAttributes.named.border_left ||
						'undefined' === typeof shortcodeAttributes.named.border_right ) &&
						'string' === typeof shortcodeAttributes.named.border_size ) {
						shortcodeAttributes.named.border_top    = shortcodeAttributes.named.border_size + 'px';
						shortcodeAttributes.named.border_bottom = shortcodeAttributes.named.border_size + 'px';
					}
					delete shortcodeAttributes.named.border_size;
				}

				if ( _.isObject( shortcodeAttributes.named ) ) {
					for ( key in shortcodeAttributes.named ) {

						prefixedKey = key;

						prefixedAttributes.params[ prefixedKey ] = shortcodeAttributes.named[ key ];
						if ( 'fusion_products_slider' === shortcodeName && 'cat_slug' === key ) {
							prefixedAttributes.params.cat_slug = shortcodeAttributes.named[ key ].replace( /\|/g, ',' );
						}
						if ( 'gradient_colors' === key ) {
							delete prefixedAttributes.params[ prefixedKey ];
							if ( -1 !== shortcodeAttributes.named[ key ].indexOf( '|' ) ) {
								prefixedAttributes.params.button_gradient_top_color    = shortcodeAttributes.named[ key ].split( '|' )[ 0 ].replace( 'transparent', 'rgba(255,255,255,0)' );
								prefixedAttributes.params.button_gradient_bottom_color = shortcodeAttributes.named[ key ].split( '|' )[ 1 ] ? shortcodeAttributes.named[ key ].split( '|' )[ 1 ].replace( 'transparent', 'rgba(255,255,255,0)' ) : shortcodeAttributes.named[ key ].split( '|' )[ 0 ].replace( 'transparent', 'rgba(255,255,255,0)' );
							} else {
								prefixedAttributes.params.button_gradient_bottom_color = shortcodeAttributes.named[ key ].replace( 'transparent', 'rgba(255,255,255,0)' );
								prefixedAttributes.params.button_gradient_top_color    = prefixedAttributes.params.button_gradient_bottom_color;
							}
						}
						if ( 'gradient_hover_colors' === key ) {
							delete prefixedAttributes.params[ prefixedKey ];
							if ( -1 !== shortcodeAttributes.named[ key ].indexOf( '|' ) ) {
								prefixedAttributes.params.button_gradient_top_color_hover    = shortcodeAttributes.named[ key ].split( '|' )[ 0 ].replace( 'transparent', 'rgba(255,255,255,0)' );
								prefixedAttributes.params.button_gradient_bottom_color_hover = shortcodeAttributes.named[ key ].split( '|' )[ 1 ] ? shortcodeAttributes.named[ key ].split( '|' )[ 1 ].replace( 'transparent', 'rgba(255,255,255,0)' ) : shortcodeAttributes.named[ key ].split( '|' )[ 0 ].replace( 'transparent', 'rgba(255,255,255,0)' );
							} else {
								prefixedAttributes.params.button_gradient_bottom_color_hover = shortcodeAttributes.named[ key ].replace( 'transparent', 'rgba(255,255,255,0)' );
								prefixedAttributes.params.button_gradient_top_color_hover    = prefixedAttributes.params.button_gradient_bottom_color_hover;
							}
						}
						if ( 'overlay_color' === key && '' !== shortcodeAttributes.named[ key ] && 'fusion_builder_container' === shortcodeName ) {
							delete prefixedAttributes.params[ prefixedKey ];
							alpha = ( 'undefined' !== typeof shortcodeAttributes.named.overlay_opacity ) ? shortcodeAttributes.named.overlay_opacity : 1;
							prefixedAttributes.params.background_color = jQuery.AWB_Color( shortcodeAttributes.named[ key ] ).alpha( alpha ).toRgbaString();
						}
						if ( 'overlay_opacity' === key ) {
							delete prefixedAttributes.params[ prefixedKey ];
						}
						if ( 'scrolling' === key && 'fusion_blog' === shortcodeName ) {
							delete prefixedAttributes.params.paging;
							paging = ( 'undefined' !== typeof shortcodeAttributes.named.paging ) ? shortcodeAttributes.named.paging : '';
							if ( 'no' === paging && 'pagination' === shortcodeAttributes.named.scrolling ) {
								prefixedAttributes.params.scrolling = 'no';
							}
						}

						// The grid-with-text layout was removed in Avada 5.2, so layout has to
						// be converted to grid. And boxed_layout was replaced by new text_layout.
						if ( 'fusion_portfolio' === shortcodeName ) {
							if ( 'layout' === key ) {
								if ( 'grid' === shortcodeAttributes.named[ key ] && shortcodeAttributes.named.hasOwnProperty( 'boxed_text' ) ) {
									shortcodeAttributes.named.boxed_text = 'no_text';
								} else if ( 'grid-with-text' === shortcodeAttributes.named[ key ] ) {
									prefixedAttributes.params[ key ] = 'grid';
								}
							}

							if ( 'boxed_text' === key ) {
								prefixedAttributes.params.text_layout = shortcodeAttributes.named[ key ];
								delete prefixedAttributes.params[ key ];
							}

							if ( 'content_length' === key && 'full-content' === shortcodeAttributes.named[ key ] ) {
								prefixedAttributes.params[ key ] = 'full_content';
							}

						}

						// Make sure the background hover color is set to border color, if it does not exist already.
						if ( 'fusion_pricing_table' === shortcodeName ) {
							if ( 'backgroundcolor' === key && ! shortcodeAttributes.named.hasOwnProperty( 'background_color_hover' ) ) {
								prefixedAttributes.params.background_color_hover = shortcodeAttributes.named.bordercolor;
							}
						}

						if ( 'type' === key && ( 'fusion_widget' === shortcodeName ) && -1 !== prefixedAttributes.params[ key ].indexOf( 'Tribe' ) ) {
							prefixedAttributes.params[ key ] = prefixedAttributes.params[ key ].replace( /\\/g, '' ).split( /(?=[A-Z])/ ).join( '\\' ).replace( '_\\', '_' );
						}

						if ( 'padding' === key && ( 'fusion_widget_area' === shortcodeName || 'fusion_builder_column' === shortcodeName || 'fusion_builder_column_inner' === shortcodeName ) ) {
							values = shortcodeAttributes.named[ key ].split( ' ' );

							if ( 1 === values.length ) {
								prefixedAttributes.params.padding_top = values[ 0 ];
								prefixedAttributes.params.padding_right = values[ 0 ];
								prefixedAttributes.params.padding_bottom = values[ 0 ];
								prefixedAttributes.params.padding_left = values[ 0 ];
							}

							if ( 2 === values.length ) {
								prefixedAttributes.params.padding_top = values[ 0 ];
								prefixedAttributes.params.padding_right = values[ 1 ];
								prefixedAttributes.params.padding_bottom = values[ 0 ];
								prefixedAttributes.params.padding_left = values[ 1 ];
							}

							if ( 3 === values.length ) {
								prefixedAttributes.params.padding_top = values[ 0 ];
								prefixedAttributes.params.padding_right = values[ 1 ];
								prefixedAttributes.params.padding_bottom = values[ 2 ];
								prefixedAttributes.params.padding_left = values[ 1 ];
							}

							if ( 4 === values.length ) {
								prefixedAttributes.params.padding_top = values[ 0 ];
								prefixedAttributes.params.padding_right = values[ 1 ];
								prefixedAttributes.params.padding_bottom = values[ 2 ];
								prefixedAttributes.params.padding_left = values[ 3 ];
							}

							delete prefixedAttributes.params[ key ];
						}
					}

					// Fix old values of image_width in content boxes and flip boxes and children.
					if ( 'fusion_content_boxes' === shortcodeName || 'fusion_flip_boxes' === shortcodeName ) {
						if ( 'undefined' !== typeof shortcodeAttributes.named.image_width ) {
							prefixedAttributes.params.image_max_width = shortcodeAttributes.named.image_width;
						}

						shortcodeContent = shortcodeContent.replace( /image_width/g, 'image_max_width' );
					}
					if ( 'fusion_button' === shortcodeName || 'fusion_tagline_box' === shortcodeName ) {
						buttonPrefix = 'fusion_tagline_box' === shortcodeName ? 'button_' : '';

						// Ensures backwards compatibility for button shape.
						if ( 'undefined' !== typeof shortcodeAttributes.named[ buttonPrefix + 'shape' ] ) {
							if ( 'square' === shortcodeAttributes.named[ buttonPrefix + 'shape' ] ) {
								prefixedAttributes.params[ buttonPrefix + 'border_radius' ] = '0';
							} else if ( 'round' === shortcodeAttributes.named[ buttonPrefix + 'shape' ] ) {
								prefixedAttributes.params[ buttonPrefix + 'border_radius' ] = '2';

								if ( '3d' === shortcodeAttributes.named.type ) {
									prefixedAttributes.params[ buttonPrefix + 'border_radius' ] = '4';
								}
							} else if ( 'pill' === shortcodeAttributes.named[ buttonPrefix + 'shape' ] ) {
								prefixedAttributes.params[ buttonPrefix + 'border_radius' ] = '25';
							} else if ( '' === shortcodeAttributes.named[ buttonPrefix + 'shape' ] ) {
								prefixedAttributes.params[ buttonPrefix + 'border_radius' ] = '';
							}

							delete prefixedAttributes.params[ buttonPrefix + 'shape' ];
						}
					}

					if ( 'fusion_button' === shortcodeName ) {
						// Ensures backwards compatibility for button border color.
						if ( 'undefined' === typeof shortcodeAttributes.named.border_color && 'undefined' !== typeof shortcodeAttributes.named.accent_color && '' !== shortcodeAttributes.named.accent_color ) {
							prefixedAttributes.params.border_color = shortcodeAttributes.named.accent_color;
						}

						if ( 'undefined' === typeof shortcodeAttributes.named.border_hover_color && 'undefined' !== typeof shortcodeAttributes.named.accent_hover_color && '' !== shortcodeAttributes.named.accent_hover_color ) {
							prefixedAttributes.params.border_hover_color = shortcodeAttributes.named.accent_hover_color;
						}
					}

					elementSettings = _.extend( elementSettings, prefixedAttributes );
				}

				if ( ! shortcodesInContent ) {
					elementSettings.params.element_content = shortcodeContent;
				}

				// Add contents to row element_content.  Used for working out data.
				if ( 'fusion_builder_row' === shortcodeName || 'fusion_builder_row_inner' === shortcodeName || 'fusion_builder_inline' === shortcodeName ) {
					elementSettings.element_content = shortcodeContent;
				}
				if ( 'fusion_builder_column' === shortcodeName || 'fusion_builder_column_inner' === shortcodeName ) {
					elementSettings.column_shortcode = shortcodeElement[ 0 ];
				}

				// Compare shortcode name to multi elements object / array
				if ( shortcodeName in fusionMultiElements ) {
					elementSettings.multi = 'multi_element_parent';

					thisEl.checkChildUI( shortcodeName, elementSettings );
				}

				// Set content for elements with dependency options
				if ( 'undefined' !== typeof fusionAllElements[ shortcodeName ] &&  'undefined' !== typeof fusionAllElements[ shortcodeName ].option_dependency ) {

					dependencyOption      = fusionAllElements[ shortcodeName ].option_dependency;
					dependencyOptionValue = prefixedAttributes.params[ dependencyOption ];
					elementContent        = prefixedAttributes.params.element_content;
					prefixedAttributes.params[ dependencyOptionValue ] = elementContent;
				}

				if ( shortcodesInContent ) {
					if ( false === elementSettings.container && 'fusion_builder_next_page' !== shortcodeName && 'fusion_woo_checkout_form' !== shortcodeName ) {
						elementSettings.params.element_content = shortcodeContent;
					}
				}

				// Check if child element.
				if ( 'undefined' !== typeof childShortcode && shortcodeName === childShortcode ) {
					elementSettings.multi = 'multi_element_child';

					// Checks if map has set selectors. If so needs to be set prior to render.
					if ( 'undefined' !== typeof fusionAllElements[ shortcodeName ].selectors ) {
						elementSettings.selectors = jQuery.extend( true, {}, fusionAllElements[ shortcodeName ].selectors );
					}
				}

				// Legacy checklist integration.
				if ( 'fusion_checklist' === shortcodeName && 'undefined' !== typeof elementSettings.params.element_content && -1 !== elementSettings.params.element_content.indexOf( '<li>' ) && -1 === elementSettings.params.element_content.indexOf( '[fusion_li_item' ) ) {
					elementSettings.params.element_content = elementSettings.params.element_content.replace( /<ul>/g, '' );
					elementSettings.params.element_content = elementSettings.params.element_content.replace( /<\/ul>/g, '' );
					elementSettings.params.element_content = elementSettings.params.element_content.replace( /<li>/g, '[fusion_li_item]' );
					elementSettings.params.element_content = elementSettings.params.element_content.replace( /<\/li>/, '[/fusion_li_item]' );
				}

				if ( ! noCollection ) {
					if ( 'multi_element_child' !== elementSettings.multi ) {
						thisEl.collection.add( [ elementSettings ] );
					}
				} else {
					renderElements.push( {
						content: shortcodeElement[ 0 ],
						settings: elementSettings
					} );
				}

				if ( true === FusionPageBuilderApp.layoutIsLoading && 'object' === typeof FusionPageBuilder.options.fusionTypographyField && 'object' === typeof fusionAllElements[ elementSettings.element_type ] ) {
					elementFonts = [];

					// Find typography options and then get values.
					_.each( fusionAllElements[ elementSettings.element_type ].params, function( optionParam ) {
						if ( 'typography' === optionParam.type ) {
							// If we have a family value, add to array.
							if ( 'string' === typeof elementSettings.params[ 'fusion_font_family_' + optionParam.choices[ 'font-family' ] ] && '' !== elementSettings.params[ 'fusion_font_family_' + optionParam.choices[ 'font-family' ] ] ) {
								elementFonts.push( {
									family: elementSettings.params[ 'fusion_font_family_' + optionParam.choices[ 'font-family' ] ],
									variant: elementSettings.params[ 'fusion_font_variant_' + optionParam.choices[ 'font-family' ] ]
								} );
							}
						}
					} );

					if ( ! _.isEmpty( elementFonts ) ) {
						// If webfonts are not defined, init them and re-run this method.
						if ( _.isUndefined( window.awbTypographySelect ) || _.isUndefined( window.awbTypographySelect.webfonts ) ) {
							jQuery.when( window.awbTypographySelect.getWebFonts() ).done( function() {
								_.each( elementFonts, function( font ) {
									window.awbTypographySelect.webFontLoad( font.family, font.variant );
								} );
							} );
							return this;
						}
						_.each( elementFonts, function( font ) {
							window.awbTypographySelect.webFontLoad( font.family, font.variant );
						} );
					}
				}


				if ( shortcodesInContent ) {

					if ( shortcodeName in fusionMultiElements ) {

						// If this is a parent element, we pass this on to make sure children are proper children for that element.
						thisEl.shortcodesToBuilder( shortcodeContent, elementCID, fusionMultiElements[ shortcodeName ] );
					} else if ( true === elementSettings.container ) {
						if ( ( 'fusion_builder_row_inner' === shortcodeName || 'fusion_builder_column_inner' === shortcodeName || 'fusion_builder_inline' === shortcodeName ) && ! elementSettings.parent ) {
							thisEl.shortcodesToBuilder( shortcodeContent, false, false, true );
						} else {
							thisEl.shortcodesToBuilder( shortcodeContent, elementCID );
						}
					}
				}

			} );

			this.navigator.update();

			if ( noCollection ) {
				return renderElements;
			}
		},

		/**
		 * Checks if map has set child_ui. If so child UI shows on parent element settings.
		 *
		 * @since 2.0.0
		 * @param {string} shortcodeName - Shortcode tag.
		 * @param {Object} attributes - Element model attributes.
		 * @return {void}
		 */
		checkChildUI: function( shortcodeName, attributes ) {
			if ( 'undefined' !== typeof fusionAllElements[ shortcodeName ].child_ui ) {
				attributes.child_ui = fusionAllElements[ shortcodeName ].child_ui;
			}
		},

		/**
		 * Add an element.
		 *
		 * @since 2.0.0
		 * @param {Object} element - The element we're adding.
		 * @return {void}
		 */
		addBuilderElement: function( element ) {

			var view,
				self         = this,
				viewSettings = {
					model: element,
					collection: FusionPageBuilderElements
				},
				generatedView,
				generatedViewSettings,
				parentModel;

			switch ( element.get( 'type' ) ) {

			case 'fusion_builder_blank_page':

				if ( 'undefined' !== typeof FusionApp.data.postDetails.post_type && 'fusion_form' === FusionApp.data.postDetails.post_type ) {
					viewSettings.className = 'fusion-builder-blank-page';
					view = new FusionPageBuilder.BlankFormView( viewSettings );
				} else {
					view = new FusionPageBuilder.BlankPageView( viewSettings );
				}

				FusionPageBuilderViewManager.addView( element.get( 'cid' ), view );

				if ( ! _.isUndefined( element.get( 'view' ) ) ) {
					element.get( 'view' ).$el.after( view.render().el );

				} else {
					this.$el.find( '#fusion_builder_container' ).append( view.render().el );
				}

				break;

			case 'fusion_builder_container':

				// Check custom container position
				if ( '' !== FusionPageBuilderApp.targetContainerCID ) {
					element.attributes.view = FusionPageBuilderViewManager.getView( FusionPageBuilderApp.targetContainerCID );

					FusionPageBuilderApp.targetContainerCID = '';
				}

				view = new FusionPageBuilder.ContainerView( viewSettings );

				FusionPageBuilderViewManager.addView( element.get( 'cid' ), view );

				if ( ! _.isUndefined( element.get( 'view' ) ) ) {
					if ( 'undefined' === typeof element.get( 'targetElementPosition' ) || 'after' === element.get( 'targetElementPosition' ) ) {
						element.get( 'view' ).$el.after( view.render().el );
					} else {
						element.get( 'view' ).$el.before( view.render().el );
					}

				} else {
					this.$el.find( '#fusion_builder_container' ).append( view.render().el );
					this.$el.find( '.fusion-builder-blank-page' ).remove();
				}

				break;

			case 'element':
			case 'fusion_builder_column':
			case 'fusion_builder_column_inner':
			case 'fusion_builder_row_inner':
				self.addToChildCollection( element );

				// TODO: check
				if ( 'fusion_builder_row_inner' === element.get( 'type' ) ) {
					if ( 'manually' === element.get( 'created' ) && 'row_inner' === element.get( 'element_type' ) ) {
						element.set( 'view', FusionPageBuilderViewManager.getView( element.get( 'parent' ) ), { silent: true } );
					}
				}

				break;

			case 'fusion_builder_row':

				// Get element parent
				parentModel = this.collection.find( function( model ) {
					return model.get( 'cid' ) == element.get( 'parent' ); // jshint ignore:line
				} );

				// Add child element to column's children collection
				parentModel.children.add( [ element ] );

				break;

			case 'generated_element':

				// Ignore modals for columns inserted with generator
				if ( 'fusion_builder_column_inner' !== element.get( 'element_type' ) && 'fusion_builder_column' !== element.get( 'element_type' ) ) {

					viewSettings.attributes = {
						'data-modal_view': 'element_settings'
					};

					generatedViewSettings = {
						model: element,
						collection: FusionPageBuilderElements,
						attributes: {
							'data-cid': element.get( 'cid' )
						}
					};

					if ( 'undefined' !== typeof element.get( 'multi' ) && 'multi_element_parent' === element.get( 'multi' ) ) {

						if ( 'undefined' !== typeof FusionPageBuilder[ element.get( 'element_type' ) ] ) {

							self.checkChildUI( element.get( 'element_type' ), generatedViewSettings.model.attributes );

							generatedView = new FusionPageBuilder[ element.get( 'element_type' ) ]( generatedViewSettings );

						} else {

							self.checkChildUI( element.get( 'element_type' ), generatedViewSettings.model.attributes );

							generatedView = new FusionPageBuilder.ParentElementView( generatedViewSettings );
						}

					} else if ( 'undefined' !== typeof FusionPageBuilder[ element.get( 'element_type' ) ] ) {
						generatedView = new FusionPageBuilder[ element.get( 'element_type' ) ]( generatedViewSettings );
					} else if ( 'fusion_builder_row_inner' === element.get( 'element_type' ) ) {
						generatedView = new FusionPageBuilder.InnerRowView( generatedViewSettings );
					} else {
						generatedView = new FusionPageBuilder.ElementView( generatedViewSettings );
					}

					// Add new view to manager
					FusionPageBuilderViewManager.addView( element.get( 'cid' ), generatedView );

					generatedView.render().el;

					view = fusionAllElements[ element.get( 'element_type' ) ].custom_settings_view_name;

					if ( 'undefined' !== typeof view && '' !== view ) {
						view = new FusionPageBuilder[ view ]( viewSettings );
					} else {
						view = new FusionPageBuilder.ElementSettingsView( viewSettings );
					}

					this.generateElementSettings( view );
				}

				break;

			case 'fusion_builder_next_page':
				view = new FusionPageBuilder.NextPage( viewSettings );

				FusionPageBuilderViewManager.addView( element.get( 'cid' ), view );

				if ( ! _.isUndefined( element.get( 'appendAfter' ) ) ) {

					if ( ! element.get( 'appendAfter' ).next().next().hasClass( 'fusion-builder-next-page' ) ) {
						element.get( 'appendAfter' ).after( view.render().el );
					}
				} else {
					this.$el.find( '.fusion-builder-container:last-child' ).after( view.render().el );
				}

				break;
			case 'fusion_woo_checkout_form':
				view = new FusionPageBuilder.checkoutForm( viewSettings ),
				self = this;

				FusionPageBuilderViewManager.addView( element.get( 'cid' ), view );

				if ( ! _.isUndefined( element.get( 'appendAfter' ) ) ) {

					if ( 2 > this.$el.find( '.fusion-checkout-form' ).length ) {
						element.get( 'appendAfter' ).after( view.render().el );
					}
				} else if ( ! this.$el.find( '.fusion-builder-container:last-child' ).length ) {
					setTimeout( function() {
						self.$el.find( '.fusion-builder-container:first-child' ).before( view.render().el );
					}, 200 );
				} else {
					this.$el.find( '.fusion-builder-container:last-child' ).after( view.render().el );
				}
				break;
			}

			// Unset target element if it exists.
			if ( ! _.isUndefined( element.get( 'targetElement' ) ) ) {
				element.unset( 'targetElement' );
			}
		},

		/**
		 * Add a child to it's parent collection.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		addToChildCollection: function( child ) {
			var parentModel,
				atIndex;

			if ( child instanceof Backbone.Model ) {
				parentModel = this.collection.find( function( model ) {
					return model.get( 'cid' ) == child.get( 'parent' ); // jshint ignore:line
				} );
				atIndex = child.get( 'at_index' );
				child.unset( 'at_index' );
			} else {
				parentModel = this.collection.find( function( model ) {
					return model.get( 'cid' ) == child.parent; // jshint ignore:line
				} );
				atIndex = child.at_index;
				delete child.at_index;
			}
			if ( 'new' !== atIndex && 'undefined' !== typeof atIndex && ! isNaN( atIndex ) ) {
				parentModel.children.add( [ child ], { at: atIndex } );
			} else {
				parentModel.children.add( [ child ] );
			}
		},

		/**
		 * Calculate collection index.
		 *
		 * @since 2.0.0
		 * @param {Object} element view.
		 * @return {string}
		 */
		getCollectionIndex: function( targetView ) {
			var index = 'new',
				targetModel,
				parentModel,
				targetIndex;

			if ( 'undefined' !== typeof targetView && false !== targetView ) {

				// Child element
				if ( 'undefined' !== typeof targetView.data( 'parent-cid' ) ) {
					parentModel = FusionPageBuilderApp.collection.find( function( model ) {
						return model.get( 'cid' ) === targetView.data( 'parent-cid' );
					} );

					targetModel = parentModel.children.find( function( model ) {
						return model.get( 'cid' ) === targetView.data( 'cid' );
					} );

				// Regular element
				} else {
					targetModel = FusionPageBuilderApp.collection.find( function( model ) {
						return model.get( 'cid' ) === targetView.data( 'cid' );
					} );

					if ( 'undefined' !== typeof targetModel.get( 'parent' ) && targetModel.get( 'parent' ) ) {
						parentModel = FusionPageBuilderApp.collection.find( function( model ) {
							return model.get( 'cid' ) === targetModel.get( 'parent' );
						} );
					}
				}

				if ( parentModel ) {
					targetIndex = parentModel.children.indexOf( targetModel );
					index = targetIndex + 1;
				}
			}

			return index;
		},

		onDropCollectionUpdate: function( model, index, senderCid ) {
			var parentView = FusionPageBuilderViewManager.getView( model.get( 'parent' ) ),
				senderView;

			// Remove model from the old collection
			parentView.model.children.remove( model, { silent: true } );

			// Add model to the new collection
			if ( senderCid !== model.get( 'parent' ) ) {
				model.set( 'parent', senderCid );
				senderView = FusionPageBuilderViewManager.getView( senderCid );
				senderView.model.children.add( model, { at: index, silent: true } );
			} else {
				parentView.model.children.add( model, { at: index, silent: true } );
			}
			parentView.model.children.trigger( 'sort' );
		},

		generateElementSettings: function( view ) {

			// No need to render if it already is.
			if ( ! FusionPageBuilderApp.SettingsHelpers.shouldRenderSettings( view ) ) {
				return;
			}

			// If we want dialog.
			jQuery( view.render().el ).dialog( {
				title: fusionAllElements[ view.model.get( 'element_type' ) ].name,
				width: FusionApp.dialog.dialogData.width,
				height: FusionApp.dialog.dialogData.height,
				position: FusionApp.dialog.dialogData.position,
				dialogClass: 'fusion-builder-dialog fusion-builder-settings-dialog',
				minWidth: 360,

				dragStop: function( event, ui ) {
					FusionApp.dialog.saveDialogPosition( ui.offset );
				},

				resizeStop: function( event, ui ) {
					FusionApp.dialog.saveDialogSize( ui.size );
				},

				open: function( event, ui ) { // jshint ignore: line
					var $dialogContent = jQuery( event.target ),
						$dialog        = $dialogContent.closest( '.ui-dialog' );

					if ( view.$el.find( '.has-group-options' ).length ) {
						$dialog.addClass( 'fusion-builder-group-options' );
					}
				},
				dragStart: function( event, ui ) { // jshint ignore: line

					// Used to close any open drop-downs in TinyMce.
					jQuery( event.target ).trigger( 'click' );
				},

				beforeClose: function( event, ui ) { // jshint ignore: line
					view.closeGeneratorModal();
				},

				buttons: {
					Insert: function() {
						view.insertGeneratedShortcode();
					},
					Cancel: function() {
						view.closeGeneratorModal();
					}
				}
			} );
		},

		/**
		 * Convert builder to shortcodes.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		builderToShortcodes: function() {
			var shortcode = '',
				thisEl    = this,
				plugins   = 'object' === typeof FusionApp.data.plugins_active ? FusionApp.data.plugins_active : false,
				referencedOffCanvases = {},
				offCanvases;

			// Reset the media map.
			this.mediaMap = {
				images: {},
				menus: {},
				forms: {},
				post_cards: {},
				videos: {},
				icons: {},
				off_canvases: {}
			};

			if ( FusionApp.data.is_fusion_element ) {
				this.libraryBuilderToShortcodes();
			} else {
				this.$el.find( '.fusion-builder-container' ).each( function( index, value ) {
					var $thisContainer = jQuery( this ).find( '.fusion-builder-container-content' );

					shortcode += thisEl.generateElementShortcode( jQuery( this ), true );

					$thisContainer.find( '.fusion_builder_row' ).each( function() {
						var $thisRow = jQuery( this );

						shortcode += '[fusion_builder_row]';

						$thisRow.find( '.fusion-builder-column-outer' ).each( function() {
							var $thisColumn = jQuery( this ),
								columnCID   = $thisColumn.data( 'cid' ),
								columnView  = FusionPageBuilderViewManager.getView( columnCID );

							shortcode += columnView.getColumnContent();
						} );
						shortcode += '[/fusion_builder_row]';
					} );

					shortcode += '[/fusion_builder_container]';

					// Check for next page shortcode
					if ( jQuery( this ).next().hasClass( 'fusion-builder-next-page' ) ) {
						shortcode += '[fusion_builder_next_page]';
					}

					// Check for checkuot page shortcode
					if ( jQuery( this ).next().hasClass( 'fusion-checkout-form' ) ) {
						shortcode += '[fusion_woo_checkout_form]';
					}

					if ( jQuery( this ).prev().hasClass( 'fusion-checkout-form' ) && 0 === index ) {
						shortcode = '[fusion_woo_checkout_form]' + shortcode;
					}
				} );

				FusionApp.setPost( 'post_content', shortcode );
			}

			// Add referenced Off Canvases.
			if ( false !== plugins && true === plugins.awb_studio ) {
				offCanvases = 'undefined' !== typeof FusionApp.data.postMeta._fusion.off_canvases ? FusionApp.data.postMeta._fusion.off_canvases : [];

				if ( 'object' === typeof offCanvases && Object.keys( offCanvases ).length ) {
					_.each( offCanvases, function( key, value ) {
						referencedOffCanvases[ key ] = true;
					} );
				}

				this.mediaMap.off_canvases = referencedOffCanvases;
			}

			// If media map exists, add to post meta for saving.
			if ( ! _.isEmpty( this.mediaMap ) && 'undefined' !== typeof FusionApp.data.replaceAssets && FusionApp.data.replaceAssets ) {
				FusionApp.data.postMeta.avada_media = this.mediaMap; // eslint-disable-line camelcase
				FusionApp.contentChange( 'page', 'page-option' );
			}

			this.navigator.update();
		},

		/**
		 * Generate the shortcode for an element.
		 *
		 * @since 2.0.0
		 * @param {Object}  $element - The jQuery object of the element.
		 * @param {boolean} openTagOnly - Should we only include an opening tag? Or a closing tag as well?
		 * @param {boolean} generator - Generate?
		 * @return {string}
		 */
		generateElementShortcode: function( $element, openTagOnly, generator ) {
			var attributes = '',
				content    = '',
				element,
				$thisElement,
				elementCID,
				elementType,
				elementSettings = '',
				shortcode,
				ignoredAtts,
				optionDependency,
				optionDependencyValue,
				key,
				setting,
				settingName,
				settingValue,
				param,
				keyName,
				optionValue,
				ignored,
				paramDependency,
				paramDependencyElement,
				paramDependencyValue,
				parentModel,
				parentCID,
				elementView;

			// Check if added from Shortcode Generator
			if ( true === generator ) {
				element = $element;
			} else {
				$thisElement = $element;

				// Get cid from html element
				elementCID = 'undefined' === typeof $thisElement.data( 'cid' ) ? $thisElement.find( '.fusion-builder-data-cid' ).data( 'cid' ) : $thisElement.data( 'cid' );

				if ( $thisElement.hasClass( 'fusion-builder-live-child-element' ) ) {

					parentCID = 'undefined' === typeof $thisElement.data( 'parent-cid' ) ? $thisElement.find( '.fusion-builder-data-cid' ).data( 'parent-cid' ) : $thisElement.data( 'parent-cid' );

					// Get parent model
					parentModel = FusionPageBuilderElements.find( function( model ) {
						return model.get( 'cid' ) == parentCID; // jshint ignore:line
					} );

					// Get model from parent collection
					element = parentModel.children.find( function( model ) {
						return model.get( 'cid' ) == elementCID; // jshint ignore:line
					} );

				} else {

					// Get model by cid
					element = FusionPageBuilderElements.find( function( model ) {
						return model.get( 'cid' ) == elementCID; // jshint ignore: line
					} );
				}
			}

			// Useful function can be utilized to change save data.
			elementView = FusionPageBuilderViewManager.getView( elementCID );
			if ( 'undefined' !== typeof elementView && 'function' === typeof elementView.beforeGenerateShortcode ) {
				elementView.beforeGenerateShortcode();
			}

			elementType     = 'undefined' !== typeof element ? element.get( 'element_type' ) : 'undefined';
			elementSettings = '';
			shortcode       = '';
			elementSettings = element.attributes;

			// Ignored shortcode attributes
			ignoredAtts = 'undefined' !== typeof fusionAllElements[ elementType ].remove_from_atts ? fusionAllElements[ elementType ].remove_from_atts : [];
			ignoredAtts.push( 'undefined' );

			// Option dependency
			optionDependency = 'undefined' !== typeof fusionAllElements[ elementType ].option_dependency ? fusionAllElements[ elementType ].option_dependency : '';

			if ( 'params' in elementSettings ) {
				settingName = 'params';
				settingValue = 'undefined' !== typeof element.get( settingName ) ? element.get( settingName ) : '';

				// Loop over params
				for ( param in settingValue ) {

					keyName = param;

					if ( 'element_content' === keyName ) {

						optionValue = 'undefined' !== typeof settingValue[ param ] ? settingValue[ param ] : '';

						content = optionValue;

						if ( 'undefined' !== typeof settingValue[ optionDependency ] ) {
							optionDependency = fusionAllElements[ elementType ].option_dependency;
							optionDependencyValue = 'undefined' !== typeof settingValue[ optionDependency ] ? settingValue[ optionDependency ] : '';

							// Set content
							content = 'undefined' !== typeof settingValue[ optionDependencyValue ] ? settingValue[ optionDependencyValue ] : '';
						}

					} else {

						ignored = '';

						if ( '' !== optionDependency ) {

							setting = keyName;

							// Get option dependency value ( value for type )
							optionDependencyValue = ( 'undefined' !== typeof settingValue[ optionDependency ] ) ? settingValue[ optionDependency ] : '';

							// Check for old fusion_map array structure
							if ( 'undefined' !== typeof fusionAllElements[ elementType ].params[ setting ] ) {

								// Dependency exists
								if ( 'undefined' !== typeof fusionAllElements[ elementType ].params[ setting ].dependency ) {

									paramDependency = fusionAllElements[ elementType ].params[ setting ].dependency;

									paramDependencyElement = ( 'undefined' !== typeof paramDependency.element ) ? paramDependency.element : '';

									paramDependencyValue = ( 'undefined' !== typeof paramDependency.value ) ? paramDependency.value : '';

									if ( paramDependencyElement === optionDependency ) {

										if ( paramDependencyValue !== optionDependencyValue ) {

											ignored = '';
											ignored = setting;

										}
									}
								}
							}
						}

						// Ignore shortcode attributes tagged with "remove_from_atts"
						if ( -1 < jQuery.inArray( param, ignoredAtts ) || ignored === param ) {

							// This attribute should be ignored from the shortcode

						} else {

							optionValue = 'undefined' !== typeof settingValue[ param ] ? settingValue[ param ] : '';

							// Check if attribute value is null
							if ( null === optionValue ) {
								optionValue = '';
							}

							// Sanitizes values before saving it in the shortcode.
							if ( 'undefined' !== typeof elementView && 'function' === typeof elementView.sanitizeValue ) {
								optionValue = elementView.sanitizeValue( param, optionValue );
							}

							if ( ( 'on' === fusionAppConfig.removeEmptyAttributes && '' !== optionValue ) || 'off' === fusionAppConfig.removeEmptyAttributes ) {
								attributes += ' ' + param + '="' + optionValue + '"';
							}
						}
					}
				}
			}

			shortcode = '[' + elementType + attributes;

			if ( '' === content && 'fusion_text' !== elementType && 'fusion_code' !== elementType && ( 'undefined' !== typeof elementSettings.type && 'element' === elementSettings.type ) ) {
				openTagOnly = true;
				shortcode += ' /]';
			} else {
				shortcode += ']';
			}

			if ( ! openTagOnly ) {
				shortcode += content + '[/' + elementType + ']';
			}

			return shortcode;
		},

		/**
		 * Should exlude param replacement?
		 *
		 * @since 3.6
		 * @return {Boolean}
		 */
		shouldExclude: function( param, elementType ) {
			var excluded = {
				'link_color': 'fusion_builder_container',
				'link_hover_color': 'fusion_builder_container'
			};

			if ( 'undefined' !== typeof excluded[ param ] && elementType === excluded[ param ] ) {
				return true;
			}

			return false;
		},

		/**
		 * Define the layout as loaded.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		layoutLoaded: function() {
			FusionEvents.trigger( 'fusion-history-clear' );
			this.reRenderElements = true;
		},

		/**
		 * Get element type, split up element.
		 *
		 * @since 2.0.0
		 * @param {string} elementType - The element type/name.
		 * @return {void}
		 */
		getElementType: function( elementType ) {
			var childElements;

			if ( 'fusion_builder_container' === elementType || 'fusion_builder_column' === elementType || 'fusion_builder_column_inner' === elementType ) {
				return elementType;
			}

			// First check if its a parent.
			if ( elementType in fusionMultiElements ) {
				return 'parent_element';
			}

			// Check if its a child.
			childElements = _.values( fusionMultiElements );
			if ( -1 !== childElements.indexOf( elementType ) ) {
				return 'child_element';
			}

			if ( 'fusion_builder_row_inner' === elementType ) {
				return 'fusion_builder_row_inner';
			}

			// Made it this far it must be regular.
			return 'element';
		},

		/**
		 * Clears the builder layout.
		 *
		 * @since 2.0.0
		 * @param {boolean} blankPageLayout - Should we use the blankPageLayout?
		 * @return {void}
		 */
		clearBuilderLayout: function( blankPageLayout ) {

			// Remove blank page layout.
			this.$el.find( '.fusion-builder-blank-page' ).each( function() {
				var $that    = jQuery( this ),
					thisView = FusionPageBuilderViewManager.getView( $that.data( 'cid' ) );

				if ( 'undefined' !== typeof thisView ) {
					thisView.removeBlankPageHelper();
				}
			} );

			// Remove all containers.
			this.$el.find( '.fusion-builder-container' ).each( function() {
				var $that    = jQuery( this ),
					thisView = FusionPageBuilderViewManager.getView( $that.data( 'cid' ) );

				if ( 'undefined' !== typeof thisView ) {
					thisView.removeContainer( false, true );
				}
			} );

			// Remove all next page elements.
			this.$el.find( '.fusion-builder-next-page' ).each( function() {
				var thisView = FusionPageBuilderViewManager.getView( jQuery( this ).data( 'cid' ) );

				if ( 'undefined' !== typeof thisView ) {
					thisView.removeNextPage();
				}
			} );

			// Remove all checkout form elements.
			this.$el.find( '.fusion-checkout-form' ).each( function() {
				var thisView = FusionPageBuilderViewManager.getView( jQuery( this ).data( 'cid' ) );

				if ( 'undefined' !== typeof thisView ) {
					thisView.removeCheckoutForm();
				}
			} );

			// Create blank page layout.
			if ( blankPageLayout ) {

				if ( true === this.blankPage ) {
					if ( ! this.$el.find( '.fusion-builder-blank-page' ).length ) {
						this.createBuilderLayout( '[fusion_builder_blank_page][/fusion_builder_blank_page]' );
						jQuery( '.fusion-builder-live' ).addClass( 'fusion-builder-blank-page-active' );
					}
					this.blankPage = false;
				}
			}
		},

		/**
		 * Encodes data using Base64.
		 *
		 * @since 2.0.0
		 * @param {string} data - The data to encode.
		 * @return {string}
		 */
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

		/**
		 * Decodes data using Base64.
		 *
		 * @since 2.0.0
		 * @param {string} data - The data to decode.
		 * @return {string}
		 */
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

			if ( 'undefined' === typeof input || ! input || '' === input ) {
				return input;
			}

			input = input.replace( /[^A-Za-z0-9\+\/\=]/g, '' );

			while ( i < input.length ) {

				enc1 = this._keyStr.indexOf( input.charAt( i++ ) );
				enc2 = this._keyStr.indexOf( input.charAt( i++ ) );
				enc3 = this._keyStr.indexOf( input.charAt( i++ ) );
				enc4 = this._keyStr.indexOf( input.charAt( i++ ) );

				chr1 = ( enc1 << 2 ) | ( enc2 >> 4 );
				chr2 = ( ( enc2 & 15 ) << 4 ) | ( enc3 >> 2 );
				chr3 = ( ( enc3 & 3 ) << 6 ) | enc4;

				output = output + String.fromCharCode( chr1 );

				if ( 64 != enc3 ) { // jshint ignore: line
					output = output + String.fromCharCode( chr2 );
				}
				if ( 64 != enc4 ) { // jshint ignore: line
					output = output + String.fromCharCode( chr3 );
				}

			}

			output = this.utf8Decode( output );

			return output;
		},

		/**
		 * Decodes data using utf8.
		 *
		 * @since 2.0.0
		 * @param {string} utftext - The data to decode.
		 * @return {string}
		 */
		utf8Decode: function( utftext ) {
			var string = '',
				i  = 0,
				c  = 0,
				c1 = 0, // jshint ignore: line
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

		setContent: function( textareaID, content ) {
			if ( 'undefined' !== typeof window.tinyMCE && window.tinyMCE.get( textareaID ) && ! window.tinyMCE.get( textareaID ).isHidden() ) {

				if ( window.tinyMCE.get( textareaID ).getParam( 'wpautop', true ) && 'undefined' !== typeof window.switchEditors ) {
					content = window.switchEditors.wpautop( content );
				}

				window.tinyMCE.get( textareaID ).setContent( content, { format: 'html' } );

			}

			// In both cases, update the textarea as well.
			jQuery( '#' + textareaID + ':visible' ).val( content ).trigger( 'change' );
		},

		isTinyMceActive: function() {
			var isActive = ( 'undefined' !== typeof tinyMCE ) && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();

			return isActive;
		},

		previewToggle: function() {
			var $body = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ),
				self  = this;

			this.previewMode = $body.hasClass( 'fusion-builder-preview-mode' );

			if ( this.$el.find( '.fusion-scrolling-section-edit' ).length ) {
				this.toggleScrollingSections();
			}
		},

		toggleScrollingSections: function() {
			if ( this.previewMode ) {
				this.createScrollingSections();
			} else {
				this.destroyScrollingSections();
			}
		},

		reInitScrollingSections: function() {
			this.destroyScrollingSections();
			this.createScrollingSections();
		},

		scrollingContainers: function() {
			var $containers              = this.$el.find( '.fusion-builder-container ' ),
				scrollingSections        = {},
				scrollingActive          = false,
				scrollingIndex           = 0;

			if ( ! this.$el.find( '.fusion-scrolling-section-edit' ).length ) {
				return;
			}

			$containers.each( function() {
				if ( jQuery( this ).find( '.fusion-scrolling-section-edit' ).length ) {
					scrollingActive = true;
					if ( 'undefined' === typeof scrollingSections[ scrollingIndex ] ) {
						scrollingSections[ scrollingIndex ] = [];
					}
					scrollingSections[ scrollingIndex ].push( jQuery( this ) );
				} else if ( scrollingActive ) {
					scrollingIndex++;
					scrollingActive = false;
				}
			} );

			_.each( scrollingSections, function( $scrollingContainers ) {
				var navigation = '',
					i;

				for ( i = $scrollingContainers.length; 0 < i; i-- ) {
					navigation += '<li><a href="#" class="fusion-scroll-section-link"><span class="fusion-scroll-section-link-bullet"></span></a></li>';
				}

				_.each( $scrollingContainers, function( $scrollingContainer ) {
					$scrollingContainer.find( '.fusion-scroll-section-nav ul' ).html( navigation );
				} );
			} );
		},

		createScrollingSections: function() {
			var $containers              = this.$el.find( '.fusion-builder-container' ),
				scrollNavigationPosition = ( 'right' === FusionApp.settings.header_position.toLowerCase() || jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).hasClass( 'rtl' ) ) ? 'scroll-navigation-left' : 'scroll-navigation-right',
				scrollingSections        = {},
				scrollingActive          = false,
				scrollingIndex           = 0;

			if ( this.previewMode ) {
				$containers = $containers.not( '.fusion-builder-container-status-draft' );
			}

			$containers.each( function() {
				if ( jQuery( this ).find( '.fusion-scrolling-section-edit' ).length ) {
					scrollingActive = true;
					if ( 'undefined' === typeof scrollingSections[ scrollingIndex ] ) {
						scrollingSections[ scrollingIndex ] = [];
					}
					scrollingSections[ scrollingIndex ].push( jQuery( this ) );
				} else if ( scrollingActive ) {
					scrollingIndex++;
					scrollingActive = false;
				}
			} );

			_.each( scrollingSections, function( $scrollingContainers, sectionIndex ) {
				var $scrollSectionContainer  = '<div id="fusion-scroll-section-' + sectionIndex + '" class="fusion-scroll-section" data-section="' + sectionIndex + '">',
					$scrollingNav            = '<nav id="fusion-scroll-section-nav-' + sectionIndex + '" class="fusion-scroll-section-nav ' + scrollNavigationPosition + '" data-section="' + sectionIndex + '"><ul>',
					$targetContainer         = false;

				_.each( $scrollingContainers, function( $scrollingContainer, containerIndex ) {
					var active,
						$parent,
						$clone,
						adminLabel,
						containerId,
						$containerLink;

					containerIndex = containerIndex + 1;
					active         = 1 === containerIndex ? ' active' : '';
					$parent        = $scrollingContainer;
					$clone         = $scrollingContainer.find( '.fusion-scrolling-section-edit' ).clone();
					adminLabel     = $parent.find( '.fusion-builder-section-name' ).val();
					containerId    = 'fusion-scroll-section-element-' + sectionIndex + '-' + containerIndex;
					$containerLink = '<li><a href="#' + containerId + '" class="fusion-scroll-section-link" data-name="' + adminLabel + '" data-element="' + containerIndex + '"><span class="fusion-scroll-section-link-bullet"></span></a></li>';

					if ( 1 === containerIndex ) {
						$targetContainer = $parent;
					}
					$clone.find( '.fusion-scroll-section-nav, .fusion-builder-insert-column, .fusion-builder-container-add' ).remove();
					$clone.find( '.hundred-percent-height' ).removeClass( 'hundred-percent-height' ).css( { height: '', 'min-height': '' } );
					$clone.addClass( 'hundred-percent-height-scrolling hundred-percent-height' );

					$scrollingNav           += $containerLink;
					$scrollSectionContainer += '<div class="fusion-scroll-section-element' + active + '" data-section="' + sectionIndex + '" data-element="' + containerIndex +  '">' + $clone.outerHTML() + '</div>';

					$parent.addClass( 'fusion-temp-hidden-container' );
					$parent.hide();
				} );

				$scrollingNav += '</ul></nav>';

				$scrollSectionContainer += $scrollingNav;
				$scrollSectionContainer += '</div>';

				if ( $targetContainer  ) {
					$targetContainer.before( $scrollSectionContainer );
				}
			} );

			jQuery( '#fb-preview' )[ 0 ].contentWindow.initScrollingSections();
			jQuery( '#fb-preview' ).contents().scrollTop( 0 );
		},

		destroyScrollingSections: function() {
			this.$el.find( '.fusion-scroll-section' ).remove();
			this.$el.find( '.fusion-temp-hidden-container' ).show().removeClass( 'fusion-temp-hidden-container' );
		},

		/**
		 * Toggles visibility of droppable areas on hover.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		toggleDroppablesVisibility: function() {

			// Droppables.
			if ( 'undefined' !== typeof FusionApp && 'on' === FusionApp.preferencesData.droppables_visible ) {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'fusion-hide-droppables' );
			} else {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).addClass( 'fusion-hide-droppables' );
			}
		},

		/**
		 * Toggles tooltips.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		toggleStickyHeader: function() {

			// Sticky header.
			if ( 'undefined' !== typeof FusionApp && 'off' === FusionApp.preferencesData.sticky_header ) {
				fusionTriggerEvent( 'fusion-disable-sticky-header' );
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).addClass( 'fusion-disable-sticky' );
			} else  {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'fusion-disable-sticky' );
				if ( 1 === Number( FusionApp.settings.header_sticky ) ) {
					fusionTriggerEvent( 'fusion-init-sticky-header' );
				}
			}

			jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-sticky-header-reinit' );
		},

		/**
		 * Toggles visibility of droppable areas on hover.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		toggleTooltips: function() {

			// Do nothing for Off Canvas.
			if ( 'awb_off_canvas' === FusionApp.data.postDetails.post_type ) {
				return;
			}

			// Tooltips.
			if ( 'undefined' !== typeof FusionApp && 'off' === FusionApp.preferencesData.tooltips ) {
				jQuery( 'body' ).addClass( 'fusion-hide-all-tooltips' );
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).addClass( 'fusion-hide-all-tooltips' );
			} else {
				jQuery( 'body' ).removeClass( 'fusion-hide-all-tooltips' );
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'fusion-hide-all-tooltips' );
			}
		},

		/**
		 * Toggles element filter options preview.
		 *
		 * @since 2.2
		 * @return {void}
		 */
		toggleElementFilters: function() {
			if ( 'undefined' !== typeof FusionApp && 'off' === FusionApp.preferencesData.element_filters ) {
				jQuery( 'body' ).addClass( 'fusion-disable-element-filters' );
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).addClass( 'fusion-disable-element-filters' );
			} else {
				jQuery( 'body' ).removeClass( 'fusion-disable-element-filters' );
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'fusion-disable-element-filters' );
			}
		},

		/**
		 * Toggles transparent header and absolute containers.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		toggleTransparentHeader: function() {
			var HeaderBGColor = '' === fusionSanitize.getPageOption( 'header_bg_color' ) ? FusionApp.settings.header_bg_color : fusionSanitize.getPageOption( 'header_bg_color' ),
				$html         = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'html' ),
				$body         = $html.find( 'body' );

			// Transparent Header.
			if ( 'undefined' !== typeof FusionApp && 'off' === FusionApp.preferencesData.transparent_header ) {
				$html.removeClass( 'avada-header-color-not-opaque' );
			} else if ( 1 > jQuery.AWB_Color( HeaderBGColor ).alpha() ) {
				$html.addClass( 'avada-header-color-not-opaque' );
			}

			// Absolute containers.
			if ( 'undefined' !== typeof FusionApp && 'off' === FusionApp.preferencesData.transparent_header ) {
				$body.addClass( 'fusion-no-absolute-containers' );
			} else {
				$html.removeClass( 'fusion-no-absolute-containers' );
			}
		},

		/**
		 * Toggles dark mode.
		 *
		 * @since 3.8.0
		 * @return {void}
		 */
		toggleDarkMode: function() {
			if ( 'undefined' !== typeof FusionApp && 'dark' === FusionApp.preferencesData.styling_mode ) {
				jQuery( 'body' ).addClass( 'dark-mode' );
			} else {
				jQuery( 'body' ).removeClass( 'dark-mode' );
			}
		},

		/**
		 * Toggles element transform options preview.
		 *
		 * @since 3.8.0
		 * @return {void}
		 */
		toggleElementTransform: function() {
			if ( 'undefined' !== typeof FusionApp && 'never' === FusionApp.preferencesData.element_transform ) {

				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).addClass( 'fusion-disable-element-transform' );
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'fusion-element-transform-on-edit' );

			} else if ( 'undefined' !== typeof FusionApp && 'editing' === FusionApp.preferencesData.element_transform ) {

				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).addClass( 'fusion-element-transform-on-edit' );
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'fusion-disable-element-transform' );

			} else {

				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'fusion-disable-element-transform' );
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'fusion-element-transform-on-edit' );

			}
		},

		/**
		 * Options sub tabs.
		 *
		 * @since 3.8.0
		 * @return {void}
		 */
		optionsSubTabs: function() {
			if ( 'undefined' !== typeof FusionApp && 'collapsed' === FusionApp.preferencesData.options_subtabs ) {
				jQuery( 'body' ).addClass( 'fusion-options-subtabs-collapsed' );
			} else {
				jQuery( 'body' ).removeClass( 'fusion-options-subtabs-collapsed' );
			}
		},

		/**
		 * Hides extra open sizes panels.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		sizesHide: function( event ) {
			var $targetClass = jQuery( event.target ).parent().attr( 'class' );
			if ( 'fusion-builder-column-size' !== $targetClass ) {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body .column-sizes:visible' ).each( function() {
					jQuery( this ).parent().find( '.fusion-builder-column-size' ).trigger( 'click' );
				} );
			}
		},

		setStackedContentClass: function() {
			if ( 'undefined' !== typeof FusionApp.settings && this.$el.width() < FusionApp.settings.content_break_point ) {
				this.$el.addClass( 'fusion-stacked-content' );
			} else {
				this.$el.removeClass( 'fusion-stacked-content' );
			}
		},

		disableDocumentWrite: function() {
			if ( false === this.previewDocumentWrite ) {
				this.previewDocumentWrite = document.getElementById( 'fb-preview' ).contentWindow.document.write;
				document.getElementById( 'fb-preview' ).contentWindow.document.write = function() {};
			}
			if ( false === this.documentWrite ) {
				this.documentWrite = document.write;
				document.write     = function() {};
			}
		},

		enableDocumentWrite: function() {
			var self = this;

			setTimeout( function() {
				if ( false !== self.documentWrite ) {
					document.write = self.documentWrite;
				}
				if ( false !== self.previewDocumentWrite ) {
					document.getElementById( 'fb-preview' ).contentWindow.document.write = self.previewDocumentWrite;
				}

				self.documentWrite        = false;
				self.previewDocumentWrite = false;
			}, 500 );
		},

		fusionLibraryUI: function() {
			if ( 'elements' === FusionApp.data.fusion_element_type ) {
				if ( this.$el.find( '.fusion-builder-column-outer .fusion_builder_row_inner, .fusion-builder-live-element' ).length ) {
					this.$el.find( '.fusion-builder-column .fusion-builder-add-element' ).hide();
				}
			}
		},

		sameContainerTypes: function( original, target ) {

			if ( original === target ) {
				return true;
			}

			if ( 'object' !== typeof original ) {
				original = FusionPageBuilderViewManager.getView( original );
			}
			if ( 'object' !== typeof target ) {
				target = FusionPageBuilderViewManager.getView( target );
			}

			// Don't have problem views, so we can't tell.
			if ( 'object' !== typeof original || 'object' !== typeof target || 'function' !== typeof original.isFlex || 'function' !== typeof target.isFlex ) {
				return false;
			}
			return original.isFlex() === target.isFlex();
		},

		getParentContainer: function( target ) {
			var view = target;

			// Not passing view directly, get it assuming cid is passed.
			if ( 'object' !== typeof target ) {
				view = FusionPageBuilderViewManager.getView( target );
			}

			// No view, return false.
			if ( ! view ) {
				return false;
			}

			// View found and is container, return that.
			if ( 'fusion_builder_container' === view.model.get( 'element_type' ) ) {
				return view;
			}

			// Not container but parent cid exists, try that.
			if ( view.model.get( 'parent' ) ) {
				return this.getParentContainer( view.model.get( 'parent' ) );
			}

			// Got here, that means no parent, no match, return false.
			return false;
		},

		getParentColumn: function( target ) {
			var view = target;

			// Not passing view directly, get it assuming cid is passed.
			if ( 'object' !== typeof target ) {
				view = FusionPageBuilderViewManager.getView( target );
			}

			// No view, return false.
			if ( ! view ) {
				return false;
			}

			// View found and is column, return that.
			if ( 'fusion_builder_column' === view.model.get( 'element_type' ) || 'fusion_builder_column_inner' === view.model.get( 'element_type' ) ) {
				return view;
			}

			// Not column but parent cid exists, try that.
			if ( view.model.get( 'parent' ) ) {
				return this.getParentColumn( view.model.get( 'parent' ) );
			}

			// Got here, that means no parent, no match, return false.
			return false;
		},

		/**
		 * If appropriate builds styles for form being edited.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		maybeFormStyles: function() {

			// Not editing a form then skip.
			if ( 'fusion_form' !== FusionApp.getPost( 'post_type' ) ) {
				return;
			}

			if ( false === this.formStyles ) {
				this.formStyles = new FusionPageBuilder.FormStyles();
				return;
			}

			this.formStyles.buildStyles();
		},

		/**
		 * If editing off canvas.
		 *
		 * @since 3.6
		 * @return {void}
		 */
		maybeOfCanvasStyles: function() {

			// Not editing a form then skip.
			if ( 'awb_off_canvas' !== FusionApp.getPost( 'post_type' ) ) {
				return;
			}

			if ( false === this.offCanvasStyles ) {
				this.offCanvasStyles = new FusionPageBuilder.offCanvasStyles();
				return;
			}

			this.offCanvasStyles.buildStyles();
		},

		contentPreviewWidth: function() {
			if ( 'object' === typeof FusionApp && 'object' === typeof FusionApp.data && ( ( 'string' === typeof FusionApp.data.fusion_element_type && 'post_cards' === FusionApp.data.fusion_element_type ) || 'fusion_form' === FusionApp.data.postDetails.post_type ) && 'object' === typeof FusionApp.data.postMeta && 'object' === typeof FusionApp.data.postMeta._fusion && 'undefined' !== typeof FusionApp.data.postMeta._fusion.preview_width ) {
				this.$el.find( '#fusion_builder_container' ).first().css( { width: parseInt( FusionApp.data.postMeta._fusion.preview_width ) + '%' } );
			}
		}

	} );

}( jQuery ) );

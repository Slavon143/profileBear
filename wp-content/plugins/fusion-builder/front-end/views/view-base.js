/* global fusionBuilderText, fusionGlobalManager, FusionApp, FusionPageBuilderViewManager, fusionAllElements, FusionPageBuilderApp, FusionEvents */
/* eslint no-empty-function: 0 */
/* eslint no-shadow: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Element View
		FusionPageBuilder.BaseView = window.wp.Backbone.View.extend( {

			modalDialogMoreView: null,

			events: {
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {
			},

			/**
			 * Before initial render.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			beforeRender: function() {
			},

			/**
			 * Filters render markup.
			 *
			 * @since 2.0.0
			 * @return null
			 */
			filterRender: function( $markup ) {
				return $markup;
			},

			/**
			 * Runs during render() call.
			 *
			 * @since 2.0.0
			 * @return null
			 */
			onRender: function() {
			},

			/**
				* Runs during initialize() call.
			 *
			 * @since 2.0.0
			 * @return null
			 */
			onInit: function() {
			},

			/**
			 * Runs just before view is removed.
			 *
			 * @since 2.0.0
			 * @return null
			 */
			beforeRemove: function() {
			},

			/**
			 * Runs just after render on cancel.
			 *
			 * @since 2.0.0
			 * @return null
			 */
			onCancel: function() {
			},

			/**
			 * Runs just after render on cancel.
			 *
			 * @since 3.0.2
			 * @return null
			 */
			beforeGenerateShortcode: function() {
				var elementType = this.model.get( 'element_type' ),
					options     = fusionAllElements[ elementType ].params,
					values      = jQuery.extend( true, {}, fusionAllElements[ elementType ].defaults, _.fusionCleanParameters( this.model.get( 'params' ) ) ),
					self        = this;

				if ( 'object' !== typeof options ) {
					return;
				}

				// If images needs replaced lets check element to see if we have media being used to add to object.
				if ( 'undefined' !== typeof FusionApp.data.replaceAssets && FusionApp.data.replaceAssets && ( 'undefined' !== typeof FusionApp.data.fusion_element_type || 'fusion_template' === FusionApp.getPost( 'post_type' ) ) ) {

					this.mapStudioImages( options, values );

					if ( 'undefined' !== typeof this.model.get( 'multi' ) && 'multi_element_parent' === this.model.get( 'multi' ) ) {
						this.model.children.each( function( child ) {
							var elementType = child.attributes.element_type,
								childOptions = fusionAllElements[ elementType ].params,
								childValues  = jQuery.extend( true, {}, fusionAllElements[ elementType ].defaults, _.fusionCleanParameters( child.attributes.params ) );

							self.mapStudioImages( childOptions, childValues );
						} );
					}

					if ( 'fusion_form' === elementType && '' !== values.form_post_id ) {
						// If its not within object already, add it.
						if ( 'undefined' === typeof FusionPageBuilderApp.mediaMap.forms[ values.form_post_id ] ) {
							FusionPageBuilderApp.mediaMap.forms[ values.form_post_id ] = true;
						}
					}

				}
			},

			/**
			 * Add studio images to media map.
			 * @param {Object} options
			 * @param {Object} values
			 * @returns void
			 */
			mapStudioImages: function( options, values ) {

				if ( 'object' !== typeof options ) {
					return;
				}

				// If images needs replaced lets check element to see if we have media being used to add to object.
				_.each( options, function( option ) {
					var value;
					if ( 'upload' === option.type && 'undefined' !== typeof values[ option.param_name ] && '' !== values[ option.param_name ] ) {
						value = values[ option.param_name ];

						if ( 'undefined' === typeof value || 'undefined' === value ) {
							return;
						}

						// If its not within object already, add it.
						if ( 'undefined' === typeof FusionPageBuilderApp.mediaMap.images[ value ] ) {
							FusionPageBuilderApp.mediaMap.images[ value ] = true;
						}

						// Check if we have an image ID for this param.
						if ( 'undefined' !== typeof values[ option.param_name + '_id' ] && '' !== values[ option.param_name + '_id' ] )	{
							if ( 'object' !== typeof FusionPageBuilderApp.mediaMap.images[ value ] ) {
								FusionPageBuilderApp.mediaMap.images[ value ] = {};
							}
							FusionPageBuilderApp.mediaMap.images[ value ][ option.param_name + '_id' ] = values[ option.param_name + '_id' ];
						}
					}
				} );

			},

			/**
			 * Triggers a refresh.
			 *
			 * @since 2.0.0
			 * @return void
			 */
			refreshJs: function() {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-element-render-' + this.model.attributes.element_type, this.model.attributes.cid );
			},

			/**
			 * Triggers responsive typography to recalculate.
			 *
			 * @since 2.0.0
			 * @return void
			 */
			updateResponsiveTypography: function() {
				document.querySelector( '#fb-preview' ).contentWindow.document.body.dispatchEvent( new Event( 'fusion-force-typography-update', { 'bubbles': true, 'cancelable': true } ) );
			},

			/**
			 * Re-Renders the view.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event triggering the rerender.
			 * @return {void}
			 */
			reRender: function( event ) {
				if ( event && 'object' === typeof event ) {
					event.preventDefault();
				}

				this.patchView( event );

				if ( this.model.get( 'inline_editor' ) && ! this.activeInlineEditing ) {
					FusionPageBuilderApp.inlineEditorHelpers.liveEditorEvent( this );
					this.activeInlineEditing = false;
				}
			},

			patchView: function() {
				var self            = this,
					$oldContent     = '',
					$newContent     = '',
					MultiGlobalArgs = {},
					diff,
					heightBeforePatch;

				if ( 'generated_element' === this.model.get( 'type' ) ) {
					return;
				}

				heightBeforePatch = this.$el.outerHeight();
				this.beforePatch();
				FusionPageBuilderApp.disableDocumentWrite();

				$oldContent = this.getElementContent();
				$newContent = $oldContent.clone();

				$newContent.html( self.getTemplate() );

				// Find the difference
				diff = FusionPageBuilderApp._diffdom.diff( $oldContent[ 0 ], $newContent[ 0 ] );

				// Columns. Skip resizable patching.
				if ( 'function' === typeof this.patcherFilter ) {
					diff = this.patcherFilter( diff );
				}

				// Apply the difference.
				FusionPageBuilderApp._diffdom.apply( $oldContent[ 0 ], diff );

				if ( 'fusion_builder_column' !== this.model.get( 'element_type' ) ) {

					// Handle multiple global elements.
					MultiGlobalArgs = {
						currentModel: this.model,
						handleType: 'changeView',
						difference: diff
					};
					fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );
				}

				$oldContent.removeClass( 'fusion-loader' );

				FusionPageBuilderApp.enableDocumentWrite();
				this.afterPatch();

				// So equalHeights columns are updated.
				if ( heightBeforePatch !== this.$el.outerHeight() && 'function' === typeof this._triggerColumn ) {
					this._triggerColumn();
				}
			},

			/**
			 * Filter out DOM before patching.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			patcherFilter: function( diffs ) {
				var filteredDiffs = [],
					ignoreList    = [
						'aria-multiline',
						'contenteditable',
						'data-inline-fontsize',
						'data-medium-editor-index',
						'data-medium-editor-element',
						'data-medium-focused',
						'data-placeholder',
						'medium-editor-index',
						'role',
						'spellcheck'
					],
					skipReInit = false;

				if ( this.activeInlineEditing ) {
					_.each( diffs, function( diff ) {
						if ( 'removeAttribute' === diff.action && -1 !== jQuery.inArray( diff.name, ignoreList ) ) {
							skipReInit = true;
							return;
						} else if ( 'modifyAttribute' === diff.action && -1 !== diff.oldValue.indexOf( 'medium-editor-element' ) && -1 === diff.oldValue.indexOf( 'medium-editor-element' ) ) {
							diff.newValue = diff.newValue + ' medium-editor-element';
							filteredDiffs.push( diff );
							skipReInit = true;
							return;
						}

						filteredDiffs.push( diff );
					} );
					diffs = filteredDiffs;

					// If we are not just removing/modifying attributes then inline needs recreated.
					this.activeInlineEditing = skipReInit;
					this.autoSelectEditor    = ! skipReInit;
				}
				return diffs;
			},

			/**
			 * Runs before view DOM is patched.
			 *
			 * @since 2.0.0
			 * @return null
			 */
			beforePatch: function() {
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0.0
			 * @return null
			 */
			afterPatch: function() {

				// This will trigger a JS event on the preview frame.
				this._refreshJs();
			},

			/**
			 * Runs after render to open any newly added inline element settings.
			 *
			 * @since 2.0.0
			 * @return null
			 */
			renderInlineSettings: function() {
				var newlyAdded;

				if ( 'undefined' === typeof FusionPageBuilderApp.inlineEditors || ! FusionPageBuilderApp.inlineEditors.shortcodeAdded ) {
					return;
				}

				newlyAdded = this.model.inlineCollection.find( function( model ) {
					return 'true' == model.get( 'params' ).open_settings; // jshint ignore: line
				} );

				if ( 'undefined' !== typeof newlyAdded ) {
					newlyAdded.parentView = this;
					newlyAdded.$target    = this.$el.find( '.fusion-disable-editing[data-id="' + newlyAdded.get( 'cid' ) + '"]' );
					delete newlyAdded.attributes.params.open_settings;

					if ( 'undefined' !== typeof FusionApp && 'off' !== FusionApp.preferencesData.open_settings ) {
						newlyAdded.set( 'added',  true );
						FusionPageBuilderApp.inlineEditorHelpers.getInlineElementSettings( newlyAdded );
					}
				}
			},

			/**
			 * Get the template.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			getTemplate: function() {
				var atts = this.getTemplateAtts();

				if ( 'undefined' !== typeof this.elementTemplate ) {
					return this.elementTemplate( atts );
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0.0
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				return atts;
			},

			/**
			 * Get dynamic values.
			 *
			 * @since 2.0.0
			 * @return {Object}
			 */
			getDynamicAtts: function( atts ) {
				var self = this;

				if ( 'undefined' !== typeof this.dynamicParams && this.dynamicParams && ! _.isEmpty( this.dynamicParams.getAll() ) ) {
					_.each( this.dynamicParams.getAll(), function( data, id ) {
						var value = self.dynamicParams.getParamValue( data );

						if ( 'undefined' !== typeof value && false !== value ) {
							atts.values[ id ] = value;
						}
					} );
				}
				return atts;
			},

			/**
			 * Gets element DOM for patching.
			 *
			 * @since 2.1
			 * @return {Object}
			 */
			getValues: function() {
				var elementType = this.model.get( 'element_type' ),
					element     = fusionAllElements[ elementType ];

				return this.getDynamicAtts( jQuery.extend( true, {}, element.defaults, _.fusionCleanParameters( this.model.get( 'params' ) ) ) );
			},

			/**
			 * Gets element DOM for patching.
			 *
			 * @since 2.0.0
			 * @return {Object}
			 */
			getElementContent: function() {
				var self = this;

				switch ( this.model.get( 'type' ) ) {

				case 'fusion_builder_column':
				case 'fusion_builder_container':
				case 'fusion_builder_column_inner':
					return self.$el;
				case 'element':
					if ( 'multi_element_child' !== self.model.get( 'multi' ) ) {
						return self.$el.find( '.fusion-builder-element-content' );
					}
					return self.$el.find( '.fusion-builder-child-element-content' );
				}
			},

			/**
			 * Settings handler.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			settings: function( event ) {

				var self = this,
					viewSettings = {
						model: this.model,
						collection: this.collection
					},
					customSettingsViewName,
					modalView,
					parentView,
					generated         = 'generated_element' === this.model.get( 'type' ),
					childElementClass = '',
					dialogTitle       = '',
					resizePopupClass  = localStorage.getItem( 'resizePopupClass' );

				if ( event ) {
					event.preventDefault();
				}

				this.onSettingsOpen();

				customSettingsViewName = fusionAllElements[ this.model.get( 'element_type' ) ].custom_settings_view_name;

				// Check for generated element child.
				if ( 'multi_element_child' === this.model.get( 'multi' ) ) {
					parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
					if ( parentView && 'generated_element' === parentView.model.get( 'type' ) ) {
						generated = true;
						viewSettings.model.set( 'type', 'generated_element' );
						viewSettings.model.set( 'display', 'dialog' );
					}

				}

				if ( 'undefined' !== typeof customSettingsViewName && '' !== customSettingsViewName ) {
					modalView = new FusionPageBuilder[ customSettingsViewName ]( viewSettings );
				} else {
					modalView = new FusionPageBuilder.ElementSettingsView( viewSettings );
				}

				// Activate column spacing.
				if ( 'fusion_builder_column' === this.model.get( 'element_type' ) || 'fusion_builder_column_inner' === this.model.get( 'element_type' ) ) {
					this.columnSpacing();
					this.paddingDrag();
					this.marginDrag();

					// Hides column size popup.
					this.$el.removeClass( 'active' );
					this.$el.closest( '.fusion-builder-container' ).removeClass( 'fusion-column-sizer-active' );
				}

				// Activate resize handles.
				if ( 'fusion_builder_container' === this.model.get( 'element_type' ) ) {
					this.paddingDrag();
					this.marginDrag();
				}

				if ( 'fusion_builder_container' === this.model.get( 'element_type' ) || 'fusion_builder_column' === this.model.get( 'element_type' ) || 'fusion_builder_column_inner' === this.model.get( 'element_type' ) ) {
					this.$el.addClass( 'fusion-builder-element-edited' );
				}

				childElementClass = 'undefined' !== this.model.get( 'multi' ) && 'multi_element_child' === this.model.get( 'multi' ) ? ' fusion-builder-child-element' : '';
				dialogTitle       = this.getDialogTitle();

				// No need to render if it already is.
				if ( ! FusionPageBuilderApp.SettingsHelpers.shouldRenderSettings( modalView ) ) {
					return;
				}

				// If we want dialog.
				if ( 'dialog' === FusionApp.preferencesData.editing_mode || generated ) {
					jQuery( modalView.render().el ).dialog( {
						title: dialogTitle,
						width: FusionApp.dialog.dialogData.width,
						height: FusionApp.dialog.dialogData.height,
						position: FusionApp.dialog.dialogData.position,
						dialogClass: 'fusion-builder-dialog fusion-builder-settings-dialog' + childElementClass,
						minWidth: 327,
						type: this.model.get( 'type' ),

						dragStop: function( event, ui ) {
							FusionApp.dialog.saveDialogPosition( ui.offset );
						},

						resizeStart: function() {
							FusionApp.dialog.addResizingClasses();
						},

						resizeStop: function( event, ui ) {
							var $dialog = jQuery( event.target ).closest( '.ui-dialog' ),
								width   = $dialog.find( '.fusion-tabs-menu > li' ).length;

							if ( width ) {
								width = 100 * width;
							}
							if ( width && ui.size.width > width ) {
								$dialog.find( '.fusion-tabs-menu' ).addClass( 'show-text' );
							} else {
								$dialog.find( '.fusion-tabs-menu' ).removeClass( 'show-text' );
							}

							FusionApp.dialog.saveDialogSize( ui.size );

							if ( 450 > ui.size.width && ! $dialog.hasClass( 'fusion-builder-dialog-narrow' ) ) {
								$dialog.addClass( 'fusion-builder-dialog-narrow' );
							} else if ( 450 <= ui.size.width && $dialog.hasClass( 'fusion-builder-dialog-narrow' ) ) {
								$dialog.removeClass( 'fusion-builder-dialog-narrow' );
							}

							FusionApp.dialog.removeResizingClasses();
						},

						open: function( event ) {
							var $dialogContent = jQuery( event.target ),
								$dialog        = $dialogContent.closest( '.ui-dialog' ),
								width;

							// On start can sometimes be laggy/late.
							FusionApp.dialog.addResizingHoverEvent();

							if ( modalView.$el.find( '.has-group-options' ).length ) {
								$dialog.addClass( 'fusion-builder-group-options' );
							}

							$dialogContent.find( '.fusion-builder-section-name' ).blur();

							jQuery( '.ui-dialog' ).not( $dialog ).hide();

							jQuery( '.fusion-back-menu-item' ).on( 'click', function() {
								modalView.openParent();

								self.onSettingsClose();
							} );

							self.modalDialogMoreView = new FusionPageBuilder.modalDialogMore( { model: self.model } );

							// We need to render context submenu on open.
							FusionPageBuilderApp.SettingsHelpers.renderDialogMoreOptions( modalView );

							if ( null !== resizePopupClass ) {
								jQuery( 'body' ).addClass( resizePopupClass );
								self.modalDialogMoreView.resizePopup( resizePopupClass );
							}

							jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).addClass( 'fusion-dialog-ui-active' );

							if ( 450 > $dialog.width() && ! $dialog.hasClass( 'fusion-builder-dialog-narrow' ) ) {
								$dialog.addClass( 'fusion-builder-dialog-narrow' );
							} else if ( 450 <= $dialog.width() && $dialog.hasClass( 'fusion-builder-dialog-narrow' ) ) {
								$dialog.removeClass( 'fusion-builder-dialog-narrow' );
							}

							// Check if dialog is positioned outside of viewport and reposition it if needed.
							if ( FusionApp.dialog.maybeRepositionDialog( $dialog ) ) {
								FusionApp.dialog.saveDialogPosition( $dialog.offset() );
							}

							width = $dialog.find( '.fusion-tabs-menu > li' ).length;
							if ( width ) {
								width = 100 * width;
							}
							if ( width && $dialog.width() > width ) {
								$dialog.find( '.fusion-tabs-menu' ).addClass( 'show-text' );
							}

						},

						dragStart: function( event ) {

							// Used to close any open drop-downs in TinyMce.
							jQuery( event.target ).trigger( 'click' );
						},

						beforeClose: function( event ) {

							FusionApp.dialogCloseResets( modalView );
							self.modalDialogMoreView = null;
							modalView.saveSettings( event );

							FusionEvents.trigger( 'fusion-content-changed' );
						}

					} );
				} else {

					// Adding into sidebar view instead.
					modalView.model.set( 'title', dialogTitle );
					modalView.model.set( 'display', 'sidebar' );
					FusionApp.sidebarView.renderElementSettings( modalView );
				}
			},

			getDialogTitle: function() {
				var dialogTitle = fusionAllElements[ this.model.get( 'element_type' ) ].name,
					params;

				if ( 'multi_element_child' === this.model.get( 'multi' ) ) {
					params = jQuery.extend( true, {}, this.model.get( 'params' ) );
					dialogTitle = 'Item';
					if ( 'undefined' !== typeof params.title && params.title.length ) {
						dialogTitle = params.title;
					} else if ( 'undefined' !== typeof params.title_front && params.title_front.length ) {
						dialogTitle = params.title_front;
					} else if ( 'undefined' !== typeof params.name && params.name.length ) {
						dialogTitle = params.name;
					} else if ( 'undefined' !== typeof params.image && params.image.length ) {
						dialogTitle = params.image;

						// If contains backslash, retrieve only last part.
						if ( -1 !== dialogTitle.indexOf( '/' ) && -1 === dialogTitle.indexOf( '[' ) ) {
							dialogTitle = dialogTitle.split( '/' );
							dialogTitle = dialogTitle.slice( -1 )[ 0 ];
						}
					} else if ( 'image' === this.model.attributes.element_name && 'undefined' !== typeof params.element_content && params.element_content.length ) {
						dialogTitle = params.element_content;

						// If contains backslash, retrieve only last part.
						if ( -1 !== dialogTitle.indexOf( '/' ) && -1 === dialogTitle.indexOf( '[' ) ) {
							dialogTitle = dialogTitle.split( '/' );
							dialogTitle = dialogTitle.slice( -1 )[ 0 ];
						}
					} else if ( 'undefined' !== typeof params.video && params.video.length ) {
						dialogTitle = params.video;
					} else if ( 'undefined' !== typeof params.element_content && params.element_content.length ) {
						dialogTitle = params.element_content;
					}

					// Remove HTML tags but keep quotation marks etc.
					dialogTitle = jQuery( '<div/>' ).html( dialogTitle ).text();
					dialogTitle = jQuery( '<div/>' ).html( dialogTitle ).text();
					dialogTitle = ( dialogTitle && 15 < dialogTitle.length ) ? dialogTitle.substring( 0, 15 ) + '...' : dialogTitle;

					dialogTitle = _.fusionUcFirst( dialogTitle );
				}
				return dialogTitle;
			},

			/**
			 * Extendable function for when settings is opened.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			onSettingsOpen: function() {
			},

			/**
			 * Extendable function for when settings is closed.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			onSettingsClose: function() {
				var $dialog = jQuery( '.ui-dialog:not( .fusion-video-dialog ):not( .fusion-builder-preferences-dialog )' ).first();

				// If there are opened dialogs which are resizable.
				if ( 0 < $dialog.length && ! jQuery( 'body' ).hasClass( 'fusion-settings-dialog-large' ) ) {

					// Change it's size.
					jQuery( $dialog ).css( 'width', FusionApp.dialog.dialogData.width + 'px' );
					jQuery( $dialog ).css( 'height', FusionApp.dialog.dialogData.height + 'px' );

					// Reposition it.
					jQuery( $dialog ).position( {
						my: FusionApp.dialog.dialogData.position.my,
						at: FusionApp.dialog.dialogData.position.at,
						of: window
					} );
				}
			},

			/**
			 * Renders the content.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			renderContent: function() {
			},

			/**
			 * Adds loading overlay while ajax is performing.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			addLoadingOverlay: function() {
				var contentType = 'element',
					$elementContent;

				if ( _.isObject( this.model.attributes ) ) {
					if ( 'fusion_builder_container' === this.model.attributes.element_type ) {
						contentType = 'container';
					} else if ( 'fusion_builder_column' === this.model.attributes.element_type ) {
						contentType = 'columns';
					}
				}

				$elementContent = this.$el.find( '.fusion-builder-' + contentType + '-content' );

				if ( ! $elementContent.hasClass( 'fusion-loader' ) ) {
					$elementContent.addClass( 'fusion-loader' );
					$elementContent.append( '<span class="fusion-builder-loader"></span>' );
				}
			},

			/**
			 * Removes loading overlay after ajax is done.
			 *
			 * @since 3.5
			 * @return {void}
			 */
			removeLoadingOverlay: function() {
				var contentType = 'element',
					$elementContent;

				if ( _.isObject( this.model.attributes ) ) {
					if ( 'fusion_builder_container' === this.model.attributes.element_type ) {
						contentType = 'container';
					} else if ( 'fusion_builder_column' === this.model.attributes.element_type ) {
						contentType = 'columns';
					}
				}

				$elementContent = this.$el.find( '.fusion-builder-' + contentType + '-content' );

				if ( $elementContent.hasClass( 'fusion-loader' ) ) {
					$elementContent.removeClass( 'fusion-loader' );
					$elementContent.find( '.fusion-builder-loader' ).remove();
				}
			},

			/**
			 * Removes an element.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event triggering the element removal.
			 * @return {void}
			 */
			removeElement: function( event ) {
				var parentCid = this.model.get( 'parent' );

				if ( event ) {
					event.preventDefault();
					FusionEvents.trigger( 'fusion-content-changed' );
				}

				// Remove element view
				FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

				// Destroy element model
				this.model.destroy();

				FusionEvents.trigger( 'fusion-element-removed', this.model.get( 'cid' ) );

				// Update column trigger.
				this.triggerColumn( parentCid );

				// Destroy dynamic param model.
				if ( this.dynamicParam ) {
					this.dynamicParam.destroy();
				}

				this.remove();
			},

			/**
			 * Opens the library. Builds the settings for this view
			 * and then calls FusionPageBuilder.LibraryView and renders it.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The js event.
			 * @return {void}
			 */
			openLibrary: function( event ) {
				var view,
					libraryModel = {
						target: jQuery( event.currentTarget ).data( 'target' ),
						focus: jQuery( event.currentTarget ).data( 'focus' ),
						element_cid: this.model.get( 'cid' ),
						element_name: 'undefined' !== typeof this.model.get( 'admin_label' ) && '' !== this.model.get( 'admin_label' ) ? this.model.get( 'admin_label' ) : ''
					},
					viewSettings = {
						model: libraryModel
					};

				if ( event ) {
					event.preventDefault();
					event.stopPropagation();
					FusionPageBuilderApp.sizesHide( event );
				}

				view = new FusionPageBuilder.LibraryView( viewSettings );
				view.render();

				// Make sure to close any context menus which may be open.
				FusionPageBuilderApp.removeContextMenu();
			},

			/**
			 * Disable external links.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			disableLink: function( event ) {
				if ( ! jQuery( event.target ).closest( '.fusion-builder-module-controls-container' ).length && 'lightbox' !== jQuery( event.currentTarget ).attr( 'target' ) ) {
					event.preventDefault();

					if ( FusionApp.modifierActive && ! jQuery( event.target ).parent().hasClass( 'fusion-lightbox' ) ) {
						FusionApp.checkLink( event );
					}
				}
			},

			/**
			 * Creates droppable zone and makes element draggable.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			droppableElement: function() {
				var self  = this,
					$el   = this.$el,
					cid   = this.model.get( 'cid' ),
					$body = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' );

				if ( ! $el ) {
					return;
				}
				if ( 'undefined' === typeof this.elementTarget || ! this.elementTarget.length ) {
					this.elementTarget = this.$el.find( '.fusion-element-target' );
				}

				$el.draggable( {
					appendTo: FusionPageBuilderApp.$el,
					zIndex: 999999,
					delay: 100,
					cursorAt: { top: 15, left: 15 },
					iframeScroll: true,
					containment: $body,
					cancel: '.fusion-live-editable, .fusion-builder-live-child-element:not( [data-fusion-no-dragging] ), .variations select',
					helper: function() {
						var $classes = FusionPageBuilderApp.DraggableHelpers.draggableClasses( cid );
						return jQuery( '<div class="fusion-element-helper ' + $classes + '" data-cid="' + cid + '"><span class="' + fusionAllElements[ self.model.get( 'element_type' ) ].icon + '"></span></div>' );
					},
					start: function() {
						$body.addClass( 'fusion-element-dragging fusion-active-dragging' );
						$el.addClass( 'fusion-being-dragged' );
						$el.prev( '.fusion-builder-live-element' ).find( '.target-after' ).addClass( 'target-disabled' );
					},
					stop: function() {
						setTimeout( function() {
							$body.removeClass( 'fusion-element-dragging fusion-active-dragging' );
						}, 10 );
						$el.removeClass( 'fusion-being-dragged' );
						FusionPageBuilderApp.$el.find( '.target-disabled' ).removeClass( 'target-disabled' );
					}
				} );

				this.elementTarget.droppable( {
					tolerance: 'touch',
					hoverClass: 'ui-droppable-active',
					accept: '.fusion-builder-live-element, .fusion_builder_row_inner',
					drop: function( event, ui ) {
						var handleDropElement = self.handleDropElement.bind( self );
						handleDropElement( ui.draggable, $el, jQuery( event.target ) );
					}
				} );

			},

			handleDropElement: function( $element, $targetEl, $dropTarget ) {
				var parentCid      = $dropTarget.closest( '.fusion-builder-column' ).data( 'cid' ),
					columnView     = FusionPageBuilderViewManager.getView( parentCid ),
					elementCid     = $element.data( 'cid' ),
					elementView    = FusionPageBuilderViewManager.getView( elementCid ),
					MultiGlobalArgs,
					newIndex;

				// Move the actual html.
				if ( $dropTarget.hasClass( 'target-after' ) ) {
					$targetEl.after( $element );
				} else {
					$targetEl.before( $element );
				}

				newIndex = $element.parent().children( '.fusion-builder-live-element, .fusion_builder_row_inner' ).index( $element );

				FusionPageBuilderApp.onDropCollectionUpdate( elementView.model, newIndex, parentCid );

				// Save history state
				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.moved + ' ' + fusionAllElements[ elementView.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element );

				// Handle multiple global elements.
				MultiGlobalArgs = {
					currentModel: elementView.model,
					handleType: 'save',
					attributes: elementView.model.attributes
				};
				fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );

				FusionEvents.trigger( 'fusion-content-changed' );

				columnView._equalHeights();
			},

			/**
			 * Destroy or disable the droppable and draggable.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			disableDroppableElement: function() {
				var $el = this.$el;

				// If its been init, just disable.
				if ( 'undefined' !== typeof $el.draggable( 'instance' ) ) {
					$el.draggable( 'disable' );
				}

				// If its been init, just disable.
				if ( 'undefined' !== typeof this.elementTarget && this.elementTarget.length && 'undefined' !== typeof this.elementTarget.droppable( 'instance' ) ) {
					this.elementTarget.droppable( 'disable' );
				}
			},

			/**
			 * Enable the droppable and draggable.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			enableDroppableElement: function() {
				var $el = this.$el;

				// If they have been init, then just disable.
				if ( 'undefined' !== typeof $el.draggable( 'instance' ) && 'undefined' !== typeof this.elementTarget && this.elementTarget.length && 'undefined' !== typeof this.elementTarget.droppable( 'instance' ) ) {
					$el.draggable( 'enable' );
					this.elementTarget.droppable( 'enable' );
				} else {

					// No sign of init, then need to call it.
					this.droppableElement();
				}
			},

			/**
			 * Gets edit label.
			 *
			 * @since 2.0.0
			 * @return {string}
			 */
			getEditLabel: function() {
				var editLabel   = fusionBuilderText.element_settings,
					elementType = this.model.get( 'element_type' );
				if ( 'undefined' !== typeof fusionAllElements[ elementType ] ) {
					editLabel = fusionBuilderText.custom_element_settings;
					editLabel = editLabel.replace( '%s', fusionAllElements[ elementType ].name );
				}
				return editLabel;
			},

			/**
			 * Simple prevent default function.
			 *
			 * @since 2.0.0
			 * @param {Object} event - Click event object.
			 * @return {void}
			 */
			preventDefault: function( event ) {
				event.preventDefault();
			},

			/**
			 * Update element settings on drag (columns and containers).
			 *
			 * @since 2.0.0
			 * @param {string} selector - Selector of option.
			 * @param {string} value - Value to update to.
			 * @return {void}
			 */
			updateDragSettings: function( selector, value ) {
				var $option = jQuery( '[data-element-cid="' + this.model.get( 'cid' ) + '"] ' + selector ),
					$elementSettings,
					$section;

				if ( $option.length ) {
					$elementSettings = $option.closest( '.fusion_builder_module_settings' );
					if ( ! $elementSettings.find( '.fusion-tabs-menu a[href="#design"]' ).parent().hasClass( 'current' ) ) {
						$elementSettings.find( '.fusion-tabs-menu a[href="#design"]' ).parent().trigger( 'click' );
					}
					$section = $elementSettings.find( '.fusion-tabs-menu a[href="#design"]' ).closest( '.fusion-sidebar-section, .ui-dialog-content' );
					$section.scrollTop(  $option.position().top + $section.scrollTop() );
					$option.val( value ).trigger( 'change' );
				}
			},

			baseInit: function() {
				var elementType = this.model.get( 'element_type' );

				this.initialValue = {};
				this.logHistory   = {};
				if ( 'string' === typeof elementType && -1 === jQuery.inArray( elementType, FusionPageBuilderApp.inlineElements ) ) {
					this.listenTo( FusionEvents, 'fusion-global-update-' + elementType, this.updateDefault );
					this.listenTo( FusionEvents, 'fusion-extra-update-' + elementType, this.updateExtra );
				}

				this.initDynamicParams();
			},

			initDynamicParams: function() {
				var self        = this,
					params      = this.model.get( 'params' ),
					dynamicData = params.dynamic_params;

				this.dynamicParams = new FusionPageBuilder.DynamicParams( { elementView: this } );

				if ( 'string' === typeof params.dynamic_params && '' !== params.dynamic_params ) {
					try {
						if ( FusionPageBuilderApp.base64Encode( FusionPageBuilderApp.base64Decode( dynamicData ) ) === dynamicData ) {
							dynamicData = FusionPageBuilderApp.base64Decode( dynamicData );
							dynamicData = _.unescape( dynamicData );
							dynamicData = JSON.parse( dynamicData );
						}
						self.dynamicParams.setData( dynamicData );
					} catch ( error ) {
						console.log( error ); // jshint ignore:line
					}
				}
			},

			/**
			 * Check for element ajax callbacks and run them.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			triggerAjaxCallbacks: function( skip ) {
				var self          = this,
					AjaxCallbacks = {},
					args          = {
						skip: 'undefined' === typeof skip ? false : skip
					};

				if ( 'undefined' !== typeof fusionAllElements[ this.model.get( 'element_type' ) ].has_ajax ) {

					// Collect callbacks. Do not fire the same action twice.
					_.each( fusionAllElements[ this.model.get( 'element_type' ) ].has_ajax, function( callback ) {
						AjaxCallbacks = {};
						AjaxCallbacks[ callback.action ] = {};
						AjaxCallbacks[ callback.action ][ 'function' ]   = callback[ 'function' ];
						AjaxCallbacks[ callback.action ].param_name = callback.param_name;
						AjaxCallbacks[ callback.action ].action     = callback.action;
					} );

					// Trigger ajax callbacks to populate query_data attribute
					_.each( AjaxCallbacks, function( callback ) {
						FusionApp.callback[ callback[ 'function' ] ]( callback.param_name, self.model.attributes.params[ callback.param_name ], self.model.attributes, args, self.model.get( 'cid' ), callback.action, self.model, self );
					} );
				}
			},

			updateExtra: function() {
				this.reRender();
			},

			updateDefault: function( param, value ) {
				var modelData        = jQuery.extend( this.model.attributes, {} ),
					reRender         = true,
					callbackFunction = false,
					params           = this.model.get( 'params' );

				// Only re-render if actually using default.
				if ( ( 'undefined' === typeof params[ param ] || '' === params[ param ] || 'default' === params[ param ] ) && ! this.dynamicParams.hasDynamicParam( param ) ) {

					callbackFunction = FusionPageBuilderApp.getCallbackFunction( modelData, param, value, this, true );
					if ( false !== callbackFunction && 'function' === typeof FusionApp.callback[ callbackFunction[ 'function' ] ] ) {
						reRender = this.doCallbackFunction( callbackFunction, false, param, value, modelData, true );
					}

					if ( reRender ) {
						this.reRender();
					}
				}
			},

			historyUpdateParam: function( param, value ) {
				var modelData        = jQuery.extend( this.model.attributes, {} ),
					reRender         = true,
					callbackFunction = false;

				this.changeParam( param, value, false, true );

				callbackFunction = FusionPageBuilderApp.getCallbackFunction( modelData, param, value, this, true );
				if ( false !== callbackFunction && 'function' === typeof FusionApp.callback[ callbackFunction[ 'function' ] ] ) {
					reRender = this.doCallbackFunction( callbackFunction, false, param, value, modelData, true );
				}

				if ( reRender ) {
					this.reRender();
				}
			},

			updateParam: function( param, value, event ) {
				var modelData        = jQuery.extend( this.model.attributes, {} ),
					reRender         = true,
					callbackFunction = FusionPageBuilderApp.getCallbackFunction( modelData, param, value, this );

				if ( false !== callbackFunction && 'function' === typeof FusionApp.callback[ callbackFunction[ 'function' ] ] ) {
					reRender = this.doCallbackFunction( callbackFunction, event, param, value, modelData );
				} else {
					this.changeParam( param, value );
				}

				return reRender;
			},

			setInitialValue: function( param ) {
				if ( 'undefined' !== typeof this.initialValue && 'undefined' === typeof this.initialValue[ param ] && 'undefined' !== typeof param ) {
					this.initialValue[ param ] = 'undefined' !== typeof this.model.get( 'params' )[ param ] ? this.model.get( 'params' )[ param ] : '';
				}
			},

			logChangeEvent: function( param, value, label ) {
				this.logHistory._param = this.logHistory._param || {};
				if ( ! ( param in this.logHistory._param ) ) {
					this.logHistory._param[ param ] = _.debounce( _.bind( function( param, value, label ) {
						var state = {
								type: 'param',
								param: param,
								newValue: value,
								cid: this.model.get( 'cid' )
							},
							elementMap  = fusionAllElements[ this.model.get( 'element_type' ) ],
							paramObject = elementMap.params[ param ],
							paramTitle  = 'object' === typeof paramObject ? paramObject.heading : param;

						if ( 'undefined' !== typeof label ) {
							paramTitle = label;
						} else if ( 'object' !== typeof paramObject && jQuery( '.multi-builder-dimension #' + param ).length ) {
							paramObject = elementMap.params[ jQuery( '.multi-builder-dimension #' + param ).closest( '.multi-builder-dimension' ).attr( 'id' ) ];
							if ( 'object' === typeof paramObject && 'string' === typeof paramObject.heading ) {
								paramTitle = paramObject.heading;
							}
						} else if ( 'object' !== typeof paramObject && jQuery( '.font_family #' + param ).length ) {
							paramObject = elementMap.params[ jQuery( '.font_family #' + param ).closest( '.fusion-builder-option' ).attr( 'data-option-id' ) ];
							if ( 'object' === typeof paramObject && 'string' === typeof paramObject.heading ) {
								paramTitle = paramObject.heading;
							}
						} else if (  'object' !== typeof paramObject && jQuery( '.typography [name="' + param + '"]' ).length ) {
							paramObject = elementMap.params[ jQuery( '.typography [name="' + param + '"]' ).closest( '.fusion-builder-option' ).attr( 'data-option-id' ) ];
							if ( 'object' === typeof paramObject && 'string' === typeof paramObject.heading ) {
								paramTitle = paramObject.heading;
							}
						}

						state.oldValue = this.initialValue[ param ];
						delete this.initialValue[ param ];

						this.model.trigger( 'change' );
						FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.edited + ' ' + elementMap.name + ' - ' + paramTitle, state );
					}, this ), 500 );
				}
				this.logHistory._param[ param ]( param, value, label );
			},

			changeParam: function( param, value, label, silent ) {
				var parentView;
				if ( ! silent && ! this.model.get( 'inlineElement' ) ) {
					this.setInitialValue( param );
					this.model.attributes.params[ param ] = value;

					// Update parent after param has been changed.
					if ( 'multi_element_child' === this.model.get( 'multi' ) ) {
						parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
						if ( parentView && 'function' === typeof parentView.updateElementContent ) {
							parentView.updateElementContent();
						}
					}
					this.logChangeEvent( param, value, label );
				} else {
					this.model.attributes.params[ param ] = value;
				}
			},

			/**
			 * Gets callback function for option change.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			doCallbackFunction: function( callbackFunction, event, paramName, paramValue, modelData, skipChange ) {
				var reRender = true,
					returnData;

				callbackFunction.args   = 'undefined' === typeof callbackFunction.args ? {} : callbackFunction.args;
				callbackFunction.ajax   = 'undefined' === typeof callbackFunction.ajax ? false : callbackFunction.ajax;
				callbackFunction.action = 'undefined' === typeof callbackFunction.action ? false : callbackFunction.action;
				skipChange              = 'undefined' === typeof skipChange ? false : skipChange;

				// If skip is set then param will not be changed.
				callbackFunction.args.skip = skipChange;

				// If ajax trigger via debounce, else do it here and retun data.
				if ( callbackFunction.ajax ) {
					reRender = false;
					this.addLoadingOverlay();
					this._triggerCallback( event, callbackFunction, paramName, paramValue, modelData.cid, modelData );
				} else {
					returnData = FusionApp.callback[ callbackFunction[ 'function' ] ]( paramName, paramValue, callbackFunction.args, this );
				}
				if ( 'undefined' !== typeof returnData && 'undefined' !== typeof returnData.render ) {
					reRender = returnData.render;
				}

				return reRender;
			},

			/**
			 * Triggers a callback function.
			 *
			 * @since 2.0.0
			 * @param {Object}        event - The event.
			 * @param {string|Object} callbackFunction - The callback function.
			 * @return {void}
			 */
			triggerCallback: function( event, callbackFunction, paramName, paramValue, cid, modelData ) {

				if ( 'undefined' === typeof cid && 'undefined' !== typeof callbackFunction.cid ) {
					cid = callbackFunction.cid;
				}

				if ( 'undefined' === typeof modelData ) {
					modelData = jQuery.extend( this.model.attributes, {} );
				}

				// This is added due to the new elements causing max call stack.  Not sure why but it shouldn't be necessary in any case.
				if ( 'undefined' !== typeof modelData ) {
					delete modelData.view;
				}
				if ( 'fusion_do_shortcode' !== callbackFunction[ 'function' ] ) {
					FusionApp.callback[ callbackFunction[ 'function' ] ]( paramName, paramValue, modelData, callbackFunction.args, cid, callbackFunction.action, this.model, this );
				} else {
					FusionApp.callback[ callbackFunction[ 'function' ] ]( cid, callbackFunction.content, callbackFunction.parent );
				}
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

			isDefault: function( param ) {
				return this.values[ param ] === fusionAllElements[ this.model.get( 'element_type' ) ].defaults[ param ];
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

			// Scroll to element and highlight it.
			scrollHighlight: function( scroll = true, highlight = true ) {
				var $trigger       = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-one-page-text-link' ),
					$el            = this.$el,
					elementIdAdded = false,
					$highlightedEl,
					elId           = $el.attr( 'id' );

				scroll = 'undefined' === typeof scroll ? true : scroll;

				if ( ! elId ) {
					$el.attr( 'id', 'fusion-temporary-id-' + this.cid );
					elId = 'fusion-temporary-id-' + this.cid;
					elementIdAdded = true;
				}

				setTimeout( function() {
					if ( scroll && $trigger.length && 'function' === typeof $trigger.fusion_scroll_to_anchor_target ) {
						$trigger.attr( 'href', '#' + elId ).fusion_scroll_to_anchor_target( 15 );
					}

					if ( elementIdAdded ) {
						setTimeout( function() {
							$el.removeAttr( 'id' );
						}, 10 );
					}

					if ( highlight ) {
						$highlightedEl = $el;
						// This is intended to be only for columns.
						if ( $el.find( '> .fusion-column-wrapper' ).length ) {
							$highlightedEl = $el.find( '> .fusion-column-wrapper' );
						}

						$highlightedEl.addClass( 'fusion-active-highlight' );
						setTimeout( function() {
							$highlightedEl.removeClass( 'fusion-active-highlight' );
						}, 6000 );
					}
				}, 10 );
			}

		} );
	} );
}( jQuery ) );

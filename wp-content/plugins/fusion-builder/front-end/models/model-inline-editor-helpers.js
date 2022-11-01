/* global MediumEditor, FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.InlineEditorHelpers = Backbone.Model.extend( {
		initialize: function() {
			this._reRenderElement = _.debounce( _.bind( this.reRenderElement, this ), 300 );
			this._logChangeEvent  = _.debounce( _.bind( this.logChangeEvent, this ), 500 );
			this.initialValue     = null;
		},

		logChangeEvent: function( param, value, tempModel, paramName ) {
			var label = paramName,
				state = {
					type: 'param',
					param: param,
					newValue: value,
					cid: tempModel.parentView.model.get( 'cid' )
				},
				elementMap = window.fusionAllElements[ tempModel.get( 'element_type' ) ],
				paramTitle = 'object' === typeof elementMap.params[ label ] ? elementMap.params[ label ].heading : param;

			state.oldValue    = this.initialValue;
			this.initialValue = null;

			window.FusionEvents.trigger( 'fusion-history-save-step', window.fusionBuilderText.edited + ' ' + elementMap.name + ' - ' + paramTitle, state );
		},

		updateInlineShortcode: function( oldShortcode, newShortcode, tempModel, paramName ) {
			var oldContent = tempModel.parentView.model.attributes.params.element_content,
				newContent = oldContent.replace( oldShortcode, newShortcode );

			tempModel.parentView.model.attributes.params.element_content = newContent;
			tempModel.set( 'inlineElement', newShortcode );

			if ( null === this.initialValue ) {
				this.initialValue = oldContent;
			}
			this._logChangeEvent( 'element_content', newContent, tempModel, paramName );
		},

		processInlineElement: function( model, paramName ) {
			var newViewOutput,
				tooltipElements = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( model.parentView.$el ).find( '[data-id="' + model.get( 'cid' ) + '"]' ).find( '[data-toggle="tooltip"]' );

			if ( tooltipElements.length ) {
				tooltipElements.tooltip( 'destroy' );
			}

			// Update shortcode
			this.updateInlineShortcode( model.get( 'inlineElement' ), FusionPageBuilderApp.generateElementShortcode( model, false, true ), model, paramName );

			// Get markup
			newViewOutput = this.getInlineElementMarkup( model );

			// Append html
			model.parentView.$el.find( '[data-id="' + model.get( 'cid' ) + '"]:not(.fusion-inline-ajax)' ).html( '' ).append( newViewOutput );

			// Trigger js
			jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-element-render-fusion_text', model.parentView.model.attributes.cid );
		},

		getInlineElementMarkup: function( model ) {
			var viewSettings = {
					model: model
				},
				newView,
				newViewOutput;

			if ( 'undefined' !== typeof FusionPageBuilder[ model.get( 'element_type' ) ] ) {
				newView = new FusionPageBuilder[ model.get( 'element_type' ) ]( viewSettings );
			} else {
				newView = new FusionPageBuilder.ElementView( viewSettings );
			}

			newViewOutput = newView.getTemplate();

			return newViewOutput;
		},

		getInlineElementSettings: function( model ) {
			var viewSettings = {
					model: model
				},
				modalView    = new FusionPageBuilder.ElementSettingsView( viewSettings );

			// No need to render if it already is.
			if ( ! FusionPageBuilderApp.SettingsHelpers.shouldRenderSettings( modalView ) ) {
				return;
			}

			// If we want dialog.
			if ( 'dialog' === window.FusionApp.preferencesData.editing_mode ) {
				jQuery( modalView.render().el ).dialog( {
					title: window.fusionAllElements[ model.get( 'element_type' ) ].name,
					width: window.FusionApp.dialog.dialogData.width,
					height: window.FusionApp.dialog.dialogData.height,
					position: window.FusionApp.dialog.dialogData.position,
					dialogClass: 'fusion-builder-dialog fusion-builder-settings-dialog',

					dragStop: function( event, ui ) {
						window.FusionApp.dialog.saveDialogPosition( ui.offset );
					},

					resizeStop: function( event, ui ) {
						window.FusionApp.dialog.saveDialogSize( ui.size );
					},

					open: function( event ) {
						var $dialogContent = jQuery( event.target ),
							$tabMenu = $dialogContent.find( '.fusion-builder-modal-top-container' );

						$dialogContent.closest( '.ui-dialog' ).find( '.ui-dialog-titlebar' ).append( $tabMenu );
						FusionPageBuilderApp.$el.addClass( 'fusion-builder-no-ui' );
					},
					dragStart: function( event ) {

						// Used to close any open drop-downs in TinyMce.
						jQuery( event.target ).trigger( 'click' );
					},

					beforeClose: function() {
						modalView.saveSettings();
						window.FusionEvents.trigger( 'fusion-content-changed' );
						FusionPageBuilderApp.$el.removeClass( 'fusion-builder-no-ui' );
					}

				} );
			} else {

				// Adding into sidebar view instead.
				modalView.model.set( 'title', window.fusionAllElements[ model.get( 'element_type' ) ].name );
				modalView.model.set( 'display', 'sidebar' );
				window.FusionApp.sidebarView.renderElementSettings( modalView );
			}
		},

		removeStyle: function( model ) {
			var $editor,
				content,
				param,
				params = model.parentView.model.get( 'params' ),
				editorInstance;

			$editor = model.$target.closest( '.fusion-live-editable' );

			model.$target.replaceWith( model.attributes.params.element_content );

			editorInstance = FusionPageBuilderApp.inlineEditors.getEditor( $editor.data( 'medium-editor-editor-index' ) );
			if ( 'undefined' !== typeof editorInstance ) {
				content = editorInstance.getContent();
			} else {
				content = $editor.html();
			}

			param   = $editor.data( 'param' ),

			// Fix for inline font family style.
			content = content.replace( /&quot;/g, '\'' );

			// Adds in any inline shortcodes.
			content = FusionPageBuilderApp.htmlToShortcode( content, model.parentView.model.get( 'cid' ) );

			params[ param ] = content;
			model.parentView.model.set( 'params', params );
		},

		/**
		 * Init the Medium Editor for elements that can be live-edited.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		liveEditor: function( view ) {
			var liveElements = view.$el.find( '.fusion-live-editable:not([data-medium-editor-element="true"])' );

			if ( liveElements.length ) {
				liveElements.each( function() {
					FusionPageBuilderApp.inlineEditors.addEditorInstance( jQuery( this ), view );
				} );
			}
		},

		/**
		 * Destroys each instance of live editor.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		removeLiveEditors: function( view ) {
			var liveElements = view.$el.find( '[data-medium-editor-element="true"]' ),
				viewEditors  = view.model.get( 'inlineEditors' );

			// First remove IDs stored on view.
			if ( 'undefined' !== typeof viewEditors && viewEditors.length ) {
				_.each( viewEditors, function( inlineEditor ) {
					FusionPageBuilderApp.inlineEditors.destroyEditor( inlineEditor );
				} );
			}

			// Check if there are still more IDs in DOM.
			if ( liveElements.length ) {
				liveElements.each( function() {
					FusionPageBuilderApp.inlineEditors.destroyEditor( jQuery( this ).data( 'medium-editor-editor-index' ) );
				} );
			}

			view.model.set( 'inlineEditors', [] );
		},

		liveEditorEvent: function( view ) {
			var self     = this,
				$editors = view.$el.find( '.fusion-live-editable' );

			// Remove any we already have, to prevent duplicates.
			self.removeLiveEditors( view );

			if ( true === view.model.get( 'inline_editor' ) && view.$el ) {
				if ( 1 === $editors.length && view.autoSelectEditor ) {
					FusionPageBuilderApp.inlineEditors.addEditorInstance( $editors, view, true );
				} else {
					$editors.on( 'mouseenter.inline-editor', function() {
						self.liveEditor( view );
					} );
				}
				view.autoSelectEditor = false;
			}
		},

		getInlineHTML: function( content, id ) {
			var $newContent;

			if ( '' === content || 'undefined' === typeof content ) {
				return '';
			}

			try {
				$newContent = jQuery( content );
			} catch ( error ) {
				console.log( error ); // jshint ignore:line

				return content;
			}

			// If no length, meaning no wrapping tag in this case then we wrap.
			if ( 0 === $newContent.length ) {
				$newContent = jQuery( '<span />' ).html( content );
			} else if ( 1 < $newContent.length ) {
				$newContent = jQuery( '<div />' ).html( content );
			}

			$newContent.addClass( 'fusion-disable-editing fusion-inline-ajax' ).attr( 'contenteditable', 'false' ).attr( 'data-id', id );

			// Span is added for content which is just a string and used as a selector #2609.
			return $newContent[ 0 ].outerHTML;
		},

		/**
		 * Checks whether the inline editor is enabled.
		 *
		 * @since 2.0.0
		 * @param {string} shortcodeTag - Shortcode tag.
		 * @return {boolean}
		 */
		inlineEditorAllowed: function( shortcodeTag ) {
			var inlineEditor = 'undefined' !== typeof window.fusionAllElements[ shortcodeTag ] && 'undefined' !== typeof window.fusionAllElements[ shortcodeTag ].inline_editor ? window.fusionAllElements[ shortcodeTag ].inline_editor : false;

			return inlineEditor;
		},

		/**
		 * Used to update model param if exists as override.
		 *
		 * @since 2.0.0
		 * @param {string} cid - The element cid.
		 * @param {string} param - The parameter name.
		 * @param {string} value - The new parameter value.
		 * @param {boolean} debounced - To debounce update or not.
		 * @return {void}
		 */
		updateParentElementParam: function( cid, param, value, debounced ) {
			var view      = window.FusionPageBuilderViewManager.getView( cid ),
				params    = 'undefined' !== typeof view ? view.model.get( 'params' ) : false;

			debounced = 'undefined' !== typeof debounced ? debounced : false;

			if ( 'undefined' === typeof cid || 'undefined' === typeof param || 'undefined' === typeof value || ! params ) {
				return false;
			}

			if ( ! param ) {
				return false;
			}

			if ( value === params[ param ] ) {
				return true;
			}

			view.activeInlineEditing = true;

			if ( debounced ) {
				this._reRenderElement( view, param, value );
			} else {
				this.reRenderElement( view, param, value );
			}
			return true;
		},

		reRenderElement: function( view, param, value  ) {
			var reRender = true;

			if ( view ) {
				reRender = view.updateParam( param, value );

				if ( reRender ) {
					view.reRender();
				}
			}
		},

		setOverrideParams: function( control, option ) {
			var selectionHtml  = MediumEditor.selection.getSelectionHtml( control.document ),
				el             = MediumEditor.selection.getSelectionElement( control.document ),
				innerHTML      = el ? el.innerHTML.trim() : '',
				overrideObject = {};

			// Default for not full content or overwritable.
			control.parentCid = false;
			control.override  = false;

			// Element has not override for option.
			if ( ! el || ! el.classList.contains( 'fusion-live-editable' ) ) {
				return;
			}

			if ( 'string' === typeof option ) {

				// Selection is not full.
				if ( ! el.getAttribute( 'data-inline-override-' + option ) || ( selectionHtml.trim() !== innerHTML && jQuery( '<div>' + selectionHtml + '</div>' ).text().trim() !== jQuery( '<div>' + innerHTML + '</div>' ).text().trim() ) ) {
					return;
				}
				control.override  = el.getAttribute( 'data-inline-override-' + option );
				control.parentCid = el.getAttribute( 'data-inline-parent-cid' );

			} else if ( 'object' == typeof option ) {
				_.each( option, function( scopedOption ) {
					if ( ! el.getAttribute( 'data-inline-override-' + scopedOption ) || ( selectionHtml.trim() !== innerHTML && jQuery( '<div>' + selectionHtml + '</div>' ).text().trim() !== jQuery( '<div>' + innerHTML + '</div>' ).text().trim() ) ) {
						overrideObject[ scopedOption ] = false;
					} else {
						overrideObject[ scopedOption ] = el.getAttribute( 'data-inline-override-' + scopedOption );
						control.parentCid        = el.getAttribute( 'data-inline-parent-cid' );
					}
					control.override = overrideObject;
				} );
			}
		}

	} );

}( jQuery ) );

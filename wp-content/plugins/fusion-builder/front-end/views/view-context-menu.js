/* global FusionPageBuilderApp, FusionPageBuilderViewManager, fusionBuilderText, fusionAllElements, FusionEvents */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Container View
		FusionPageBuilder.ContextMenuView = window.wp.Backbone.View.extend( {

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-context-menu' ).html() ),
			className: 'fusion-builder-context-menu',
			events: {
				'click [data-action="edit"]': 'editTrigger',
				'click [data-action="save"]': 'saveTrigger',
				'click [data-action="clone"]': 'cloneTrigger',
				'click [data-action="remove"]': 'removeTrigger',
				'click [data-action="copy"]': 'copy',
				'click [data-action="paste-before"]': 'pasteBefore',
				'click [data-action="paste-after"]': 'pasteAfter',
				'click [data-action="paste-start"]': 'pasteStart',
				'click [data-action="paste-end"]': 'pasteEnd',
				'click [data-action="invert"]': 'invertTrigger'
			},

			/**
			 * Initialize the builder sidebar.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {
				this.copyData = {
					data: {
						type: false,
						content: false
					}
				};
				this.getCopy();

				this.elWidth  = 130;
				this.elHeight = 257;
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				var leftOffset = this.model.event.pageX,
					topOffset = this.model.event.pageY;

				this.$el.html( this.template( jQuery.extend( true, this.copyData, this.model.parent.attributes ) ) );

				if ( this.model.event.pageX + this.elWidth > jQuery( '#fb-preview' ).width() ) {
					leftOffset = jQuery( '#fb-preview' ).width() - this.elWidth;
				}
				if ( this.model.event.pageY + this.elHeight > jQuery( jQuery( '#fb-preview' )[ 0 ].contentWindow.document ).height() ) {
					topOffset = jQuery( jQuery( '#fb-preview' )[ 0 ].contentWindow.document ).height() - this.elHeight;
				}

				this.$el.css( { top: ( topOffset ) + 'px', left: ( leftOffset ) + 'px' } );

				return this;
			},

			/**
			 * Trigger edit on relevant element.
			 *
			 * @since 2.0.0
			 */
			editTrigger: function( event ) {
				if ( 'fusion_builder_row_inner' === this.model.parent.attributes.element_type ) {
					this.model.parentView.editRow( event );
				} else {
					this.model.parentView.settings( event );
				}
			},

			/**
			 * Trigger save on relavent element.
			 *
			 * @since 2.0.0
			 */
			saveTrigger: function( event ) {
				this.model.parentView.openLibrary( event );
			},

			/**
			 * Trigger clone on relavent element.
			 *
			 * @since 2.0.0
			 */
			cloneTrigger: function( event ) {

				switch ( this.model.parent.attributes.element_type ) {
				case 'fusion_builder_container':
					this.model.parentView.cloneContainer( event );
					break;
				case 'fusion_builder_column_inner':
				case 'fusion_builder_column':
					this.model.parentView.cloneColumn( event );
					break;
				case 'fusion_builder_row_inner':
					this.model.parentView.cloneNestedRow( event );
					break;
				default:
					this.model.parentView.cloneElement( event );
					break;
				}
			},

			/**
			 * Trigger remove on relavent element.
			 *
			 * @since 2.0.0
			 */
			removeTrigger: function( event ) {

				switch ( this.model.parent.attributes.element_type ) {
				case 'fusion_builder_container':
					this.model.parentView.removeContainer( event );
					break;
				case 'fusion_builder_column_inner':
				case 'fusion_builder_column':
					this.model.parentView.removeColumn( event );
					break;
				case 'fusion_builder_row_inner':
					this.model.parentView.removeRow( event );
					break;
				default:
					this.model.parentView.removeElement( event );
					break;
				}
			},

			/**
			 * Copy the element.
			 *
			 * @since 2.0.0
			 */
			copy: function() {
				var type    = this.model.parent.attributes.element_type,
					content = this.model.parentView.getContent(),
					$temp   = jQuery( '<textarea>' ),
					data;

				// Copy to actual clipboard, handy for pasting.
				jQuery( 'body' ).append( $temp );
				$temp.val( content ).select();
				document.execCommand( 'copy' );
				$temp.remove();

				data = {
					type: type,
					content: content
				};

				this.storeCopy( data );
			},

			/**
			 * Stored copy data.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			storeCopy: function( data ) {
				if ( 'undefined' !== typeof Storage ) {
					localStorage.setItem( 'fusionCopyContent', data.content );
					localStorage.setItem( 'fusionCopyType', data.type );
					this.getCopy();
				}
			},

			/**
			 * Get stored data.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			getCopy: function() {
				if ( 'undefined' !== typeof Storage ) {
					if ( localStorage.getItem( 'fusionCopyContent' ) ) {
						this.copyData.data.content = localStorage.getItem( 'fusionCopyContent' );
						this.copyData.data.type = localStorage.getItem( 'fusionCopyType' );
					}
				}
			},

			/**
			 * Paste after element.
			 *
			 * @since 2.0.0
			 */
			pasteAfter: function() {
				this.paste( 'after' );
			},

			/**
			 * Paste before element.
			 *
			 * @since 2.0.0
			 */
			pasteBefore: function() {
				this.paste( 'before' );
			},

			/**
			 * Paste child to start.
			 *
			 * @since 2.0.0
			 */
			pasteStart: function() {
				this.paste( 'start' );
			},

			/**
			 * Paste child to end.
			 *
			 * @since 2.0.0
			 */
			pasteEnd: function() {
				this.paste( 'end' );
			},

			/**
			 * Paste after element.
			 *
			 * @since 2.0.0
			 */
			paste: function( position ) {
				var data    = this.copyData.data,
					type    = data.type,
					content = data.content,
					elType  = FusionPageBuilderApp.getElementType( type ),
					target  = false,
					parentId,
					parentView,
					rowView;

				if ( 'after' === position || 'before' === position ) {
					parentId = this.model.parent.attributes.parent;
					target   = this.model.parentView.$el;

					// If container, the parentId is self.
					if ( 'fusion_builder_container' === this.model.parent.attributes.type ) {
						parentId                                = this.model.parent.attributes.cid;
						FusionPageBuilderApp.targetContainerCID = this.model.parent.attributes.cid;
					}
				} else {
					parentId = this.model.parent.attributes.cid;
					target   = false;

					// If this is a container and we are inserting a column, the parent is actually the row.
					if ( 'fusion_builder_container' === this.model.parent.attributes.type ) {
						parentId = this.model.parentView.$el.find( '.fusion-builder-row-container' ).first().data( 'cid' );
					}
				}

				FusionPageBuilderApp.shortcodesToBuilder( content, parentId, false, false, target, position );

				// Save history state
				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.pasted + ' ' + fusionAllElements[ type ].name + ' ' + fusionBuilderText.element );

				FusionEvents.trigger( 'fusion-content-changed' );

				// If its a column, get the parent column and update.
				if ( 'fusion_builder_column' === type || 'fusion_builder_column_inner' === type ) {
					rowView = FusionPageBuilderViewManager.getView( parentId );
					if ( rowView ) {
						rowView.createVirtualRows();
						rowView.updateColumnsPreview();
					}
				}

				// If its a child element, the parent need to re-render.
				if ( 'child_element' === elType ) {
					if ( 'after' === position || 'before' === position ) {
						parentView = FusionPageBuilderViewManager.getView( parentId );
						parentView.render();
					} else {
						this.model.parentView.render();
					}
				}

				// If its an element the column needs rebuilt.
				if ( 'element' === elType || 'parent_element' === elType ) {
					parentView = FusionPageBuilderViewManager.getView( parentId );
					if ( parentView ) {
						parentView._equalHeights( parentView.model.attributes.parent );
					}
				}

				// Handle multiple global elements.
				window.fusionGlobalManager.handleMultiGlobal( {
					currentModel: this.model.parentView.model,
					handleType: 'save',
					attributes: this.model.parentView.model
				} );
			},

			/**
			 * Remove context meny..
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event triggering the element removal.
			 * @return {void}
			 */
			removeMenu: function( event ) {

				if ( event ) {
					event.preventDefault();
				}

				// Remove reference in builder app.
				FusionPageBuilderApp.contextMenuView = false;

				this.remove();

			},

			/**
			 * Invert trigger.
			 *
			 * @since 3.7
			 */
			invertTrigger: function() {
				var type    = this.model.parent.attributes.element_type,
					content = this.model.parentView.getContent(),
					target  = false,
					parentId;

				parentId                                = this.model.parent.attributes.cid;
				FusionPageBuilderApp.targetContainerCID = this.model.parent.attributes.cid;
				target                                  = this.model.parentView.$el;

				// get all the colors and hold them.
				const reversedColors = {};

				for ( let i = 1, revI = 8; 8 >= i; i++, revI-- ) {
					reversedColors[ '--awb-color' + i ] = '--awb-color' + revI;
					reversedColors[ '--awb-color' + i + '-h' ] = '--awb-color' + revI + '-h';
					reversedColors[ '--awb-color' + i + '-s' ] = '--awb-color' + revI + '-s';
					reversedColors[ '--awb-color' + i + '-l' ] = '--awb-color' + revI + '-l';
					reversedColors[ '--awb-color' + i + '-a' ] = '--awb-color' + revI + '-a';
				}
				reversedColors[ 'background_blend_mode="multiply"' ] = 'background_blend_mode="lighten"';
				reversedColors[ 'background_blend_mode="lighten"' ] = 'background_blend_mode="multiply"';

				const patterns = new RegExp( Object.keys( reversedColors ).join( '|' ), 'g' );
				content = content.replace( patterns, function( matched ) {
					return reversedColors[ matched ];
				} );

				FusionPageBuilderApp.shortcodesToBuilder( content, parentId, false, false, target, 'after' );

				this.model.parentView.removeContainer( false, true );

				// Save history state
				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.invert + ' ' + fusionAllElements[ type ].name + ' ' + fusionBuilderText.element );

				FusionEvents.trigger( 'fusion-content-changed' );
			}
		} );
	} );
}( jQuery ) );

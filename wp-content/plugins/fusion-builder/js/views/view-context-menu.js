/* global FusionPageBuilderApp, fusionHistoryManager, fusionBuilderText, fusionAllElements, FusionPageBuilderEvents */
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
				'click [data-action="paste-end"]': 'pasteEnd'
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
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				var offset = jQuery( '#fusion_builder_layout .inside' ).offset();

				this.$el.html( this.template( jQuery.extend( true, this.copyData, this.model.parent.attributes, { pageType: this.model.pageType } ) ) );

				this.$el.css( { top: ( this.model.event.pageY - offset.top  ) + 'px', left: ( this.model.event.pageX - offset.left ) + 'px' } );

				return this;
			},

			/**
			 * Trigger edit on relavent element.
			 *
			 * @since 2.0.0
			 */
			editTrigger: function( event ) {
				if ( 'fusion_builder_row_inner' === this.model.parent.attributes.element_type ) {
					this.model.parentView.showInnerRowDialog( event );
				} else {
					this.model.parentView.showSettings( event );
				}
			},

			/**
			 * Trigger save on relavent element.
			 *
			 * @since 2.0.0
			 */
			saveTrigger: function( event ) {
				if ( 'fusion_builder_column' === this.model.parent.attributes.element_type ) {
					this.model.parentView.saveColumnDialog( event );
				} else {
					this.model.parentView.saveElementDialog( event );
				}
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
					$temp   = jQuery( '<textarea>' ),
					content,
					data;

				switch ( this.model.parent.attributes.element_type ) {
				case 'fusion_builder_container':
					content = this.model.parentView.getContainerContent();
					break;
				case 'fusion_builder_column_inner':
				case 'fusion_builder_column':
					content = this.model.parentView.getColumnContent( this.model.parentView.$el );
					break;
				case 'fusion_builder_row_inner':
					content = this.model.parentView.getInnerRowContent();
					break;
				default:
					content = this.model.parentView.getElementContent();
					break;
				}

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
				var data        = this.copyData.data,
					type        = data.type,
					content     = data.content,
					target      = false,
					elementText = '',
					parentId;

				if ( 'after' === position || 'before' === position ) {
					parentId = this.model.parent.attributes.parent;
					target   = this.model.parentView.$el;

					// If container, the parentId is self.
					if ( 'fusion_builder_container' === this.model.parent.attributes.type ) {
						parentId = this.model.parent.attributes.cid;
						FusionPageBuilderApp.targetContainerCID = this.model.parent.attributes.cid;
					}
				} else {
					parentId = this.model.parent.attributes.cid;
					target   = false;

					// If this is a container and we are inserting a column, the parent is actually the row.
					if ( 'fusion_builder_container' === this.model.parent.attributes.type ) {
						parentId = this.model.parentView.$el.find( '.fusion-builder-row-content' ).first().data( 'cid' );
					}
				}

				// TODO: check. Arguments dont match the function:
				//
				//
				// shortcodesToBuilder: function( content, parentCID, childShortcode, noCollection, targetEl, targetPosition )
				FusionPageBuilderApp.shortcodesToBuilder( content, parentId, target, position );

				if ( -1 === type.indexOf( 'fusion_builder_' ) ) {
					elementText = ' ' + fusionBuilderText.element;
				}

				// Save history state
				fusionHistoryManager.turnOnTracking();
				window.fusionHistoryState = fusionBuilderText.pasted + ' ' + fusionAllElements[ type ].name + elementText;

				FusionPageBuilderEvents.trigger( 'fusion-element-cloned' ); // jshint ignore:line
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

			}
		} );
	} );
}( jQuery ) );

/* global FusionPageBuilderApp, fusionAllElements, FusionEvents, fusionBuilderText */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Container View
		FusionPageBuilder.ContextMenuInlineView = window.wp.Backbone.View.extend( {

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-context-menu-inline' ).html() ),
			className: 'fusion-builder-context-menu fusion-builder-inline-context-menu',
			events: {
				'click [data-action="edit"]': 'editShortcodeInline',
				'click [data-action="remove-node"]': 'removeNode',
				'click [data-action="remove-style"]': 'removeStyle'
			},

			/**
			 * Initialize inline context menu.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {

				this.elWidth  = 105;
				this.elHeight = 36;
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				var leftOffset = this.model.event.pageX,
					topOffset = this.model.event.pageY,
					$element  = this.model.$target.find( ' > *' ),
					elementOffset = $element.offset(),
					self = this;

				topOffset  = elementOffset.top - this.elHeight - 20;
				leftOffset = elementOffset.left + ( ( $element.width() - this.elWidth ) / 2 );

				this.$el.html( this.template( this.model.attributes ) );

				this.$el.css( { top: ( topOffset ) + 'px', left: ( leftOffset ) + 'px' } );

				setTimeout( function() {
					self.$el.addClass( 'fusion-builder-inline-context-menu-loaded' );
				}, 50 );

				return this;
			},

			/**
			 * Edit a shortcode within this element content.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event triggering the callback.
			 * @return {void}
			 */
			editShortcodeInline: function( event ) {

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderApp.inlineEditorHelpers.getInlineElementSettings( this.model );
			},

			/**
			 * Remove entire node.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event triggering the element removal.
			 * @return {void}
			 */
			removeNode: function( event ) {

				var $editor,
					content,
					param,
					params = this.model.parentView.model.get( 'params' ),
					editorInstance;

				if ( event ) {
					event.preventDefault();
				}

				$editor = this.model.$target.closest( '.fusion-live-editable' );

				this.model.$target.remove();

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
				content = FusionPageBuilderApp.htmlToShortcode( content, this.model.parentView.model.get( 'cid' ) );

				params[ param ] = content;
				this.model.parentView.model.set( 'params', params );

				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.deleted + ' ' + fusionAllElements[ this.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element );
			},

			/**
			 * Remove styling only.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event triggering the element removal.
			 * @return {void}
			 */
			removeStyle: function( event ) {

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderApp.inlineEditorHelpers.removeStyle( this.model );
				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.deleted + ' ' + fusionAllElements[ this.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element );
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

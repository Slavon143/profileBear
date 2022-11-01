/* global FusionPageBuilderViewManager, FusionEvents, FusionPageBuilderApp, fusionBuilderText */
/* eslint no-shadow: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Checkout Form View.
		FusionPageBuilder.checkoutForm = FusionPageBuilder.BaseView.extend( {

			className: 'fusion-checkout-form fusion-special-item',
			template: FusionPageBuilder.template( jQuery( '#fusion-checkout-form-template' ).html() ),
			events: {
				'click .fusion-builder-delete-checkout-form': 'removeCheckoutForm'
			},

			initialize: function() {
				this.$el.attr( 'data-cid', this.model.get( 'cid' ) );
			},

			render: function() {
				var self = this,
					data = this.getTemplateAtts();

				this.$el.html( this.template( data ) );

				setTimeout( function() {
					self.droppableContainer();
				}, 100 );

				return this;
			},

			/**
			 * Adds drop zones for continers and makes container draggable.
			 *
			 * @since 3.3
			 * @return {void}
			 */
			droppableContainer: function() {

				var $el   = this.$el,
					cid   = this.model.get( 'cid' ),
					$body = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' );

				if ( ! $el ) {
					return;
				}

				$el.draggable( {
					appendTo: FusionPageBuilderApp.$el,
					zIndex: 999999,
					delay: 100,
					cursorAt: { top: 15, left: 15 },
					iframeScroll: true,
					containment: $body,
					cancel: '.fusion-builder-column',
					helper: function() {
						var $classes = FusionPageBuilderApp.DraggableHelpers.draggableClasses( cid );
						return jQuery( '<div class="fusion-container-helper ' + $classes + '" data-cid="' + cid + '"><span class="fusiona-container"></span></div>' );
					},
					start: function() {
						$body.addClass( 'fusion-container-dragging fusion-active-dragging' );
						$el.addClass( 'fusion-being-dragged' );

						//  Add a class to hide the unnecessary target after.
						if ( $el.prev( '.fusion-builder-container' ).length ) {
							$el.prev( '.fusion-builder-container' ).addClass( 'hide-target-after' );
						}
					},
					stop: function() {
						setTimeout( function() {
							$body.removeClass( 'fusion-container-dragging fusion-active-dragging' );
						}, 10 );
						$el.removeClass( 'fusion-being-dragged' );
						FusionPageBuilderApp.$el.find( '.hide-target-after' ).removeClass( 'hide-target-after' );
					}
				} );

				$el.find( '.fusion-container-target' ).droppable( {
					tolerance: 'touch',
					hoverClass: 'ui-droppable-active',
					accept: '.fusion-builder-container, .fusion-checkout-form',
					drop: function( event, ui ) {

						// Move the actual html.
						if ( jQuery( event.target ).hasClass( 'target-after' ) ) {
							$el.after( ui.draggable );
						} else {
							$el.before( ui.draggable );
						}

						FusionEvents.trigger( 'fusion-content-changed' );

						FusionPageBuilderApp.scrollingContainers();

						FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.checkout_form + ' Element Order Changed' );
					}
				} );
			},

			/**
			 * Get template attributes.
			 *
			 * @since 3.3
			 * @return {void}
			 */
			getTemplateAtts: function() {
				var templateAttributes = {};

				templateAttributes = this.filterTemplateAtts( templateAttributes );

				return templateAttributes;
			},

			removeCheckoutForm: function( event ) {

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

				this.model.destroy();

				this.remove();

				FusionEvents.trigger( 'fusion-content-changed' );

				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.deleted_checkout_form );
			}

		} );

	} );

}( jQuery ) );

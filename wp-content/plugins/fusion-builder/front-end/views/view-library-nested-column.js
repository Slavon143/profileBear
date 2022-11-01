/* global FusionPageBuilderViewManager, fusionBuilderText, FusionEvents, FusionApp */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Elements View
		FusionPageBuilder.NestedColumnLibraryView = window.wp.Backbone.View.extend( {

			className: 'fusion-builder-modal-settings-container',

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-column-library-template' ).html() ),

			events: {
				'click .fusion-builder-column-layouts li': 'addNestedColumns',
				'click .fusion-builder-modal-close': 'closeModal'
			},

			initialize: function( attributes ) {
				this.options = attributes;
				this.listenTo( FusionEvents, 'fusion-modal-view-removed', this.remove );
			},

			render: function() {
				this.$el.html( this.template( this.options ) );
				this.$el.addClass( 'fusion-add-to-nested' );

				FusionApp.elementSearchFilter( this.$el );

				return this;
			},

			addNestedColumns: function( event ) {
				var $layoutEl,
					layout,
					layoutElementsNum,
					appendAfter,
					innerRow,
					innerColumn,
					targetElement,
					parent = this.attributes[ 'data-parent_cid' ],
					atIndex,
					lastCreatedCid,
					lastCreatedView;

				if ( event ) {
					event.preventDefault();
				}

				innerRow = FusionPageBuilderViewManager.getView( parent );

				if ( 'undefined' !== typeof this.attributes[ 'data-nested_column_cid' ] ) {
					innerColumn = FusionPageBuilderViewManager.getView( this.attributes[ 'data-nested_column_cid' ] );
					appendAfter = innerColumn.$el;
					targetElement = innerColumn.$el;
				} else {
					appendAfter = ( this.$el ).closest( '.fusion-builder-row-content' ).find( '.fusion-builder-row-container-inner' );
				}

				atIndex = window.FusionPageBuilderApp.getCollectionIndex( targetElement );

				$layoutEl         = jQuery( event.target ).is( 'li' ) ? jQuery( event.target ) : jQuery( event.target ).closest( 'li' );
				layout            = $layoutEl.data( 'layout' ).split( ',' );
				layoutElementsNum = _.size( layout );

				_.each( layout, function( element, index ) { // jshint ignore:line
					lastCreatedCid  = innerRow.addNestedColumn( element, appendAfter, targetElement, atIndex );
					lastCreatedView = FusionPageBuilderViewManager.getView( lastCreatedCid );
					targetElement   = lastCreatedView.$el;
					atIndex++;
				} );

				innerRow.createVirtualRows();
				innerRow.updateColumnsPreview();

				this.remove();

				FusionEvents.trigger( 'fusion-columns-added' );

				if ( event ) {

					// Save history state
					FusionEvents.trigger( 'fusion-history-turn-on-tracking' );
					window.fusionHistoryState = fusionBuilderText.added_nested_columns;

					FusionEvents.trigger( 'fusion-content-changed' );
				}
			},

			closeModal: function( event ) {
				event.preventDefault();

				this.remove();
			}
		} );
	} );
}( jQuery ) );

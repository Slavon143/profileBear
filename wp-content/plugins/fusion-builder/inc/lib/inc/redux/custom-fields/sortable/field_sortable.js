/*global jQuery, document, fusionredux_change, fusionredux*/

( function() {
	'use strict';

	var scroll = '';

	fusionredux.field_objects          = fusionredux.field_objects || {};
	fusionredux.field_objects.sortable = fusionredux.field_objects.sortable || {};

	fusionredux.field_objects.sortable.init = function( selector ) {

		if ( ! selector ) {
			selector = jQuery( document ).find( '.fusionredux-group-tab:visible' ).find( '.fusionredux-container-sortable:visible' );
		}

		jQuery( selector ).each( function() {
			var el     = jQuery( this ),
				parent = el;

			if ( ! el.hasClass( 'fusionredux-field-container' ) ) {
				parent = el.parents( '.fusionredux-field-container:first' );
			}

			if ( parent.is( ':hidden' ) || ! parent.hasClass( 'fusionredux-field-init' ) ) {
				return;
			}

			parent.removeClass( 'fusionredux-field-init' );
			el.find( '.fusionredux-sortable' ).sortable( {
				opacity: 0.7,
				scroll: false,
				update: function( event ) {
					var items      = jQuery( event.target ).find( '.item' ),
					itemsArray = [],
					value      = '';

					jQuery( items ).each( function( i, item ) {
						var itemVal = jQuery( item ).data( 'sortable-item' );
						if ( itemVal ) {
							itemsArray.push( itemVal );
						}
					} );
					value = itemsArray.join( ',' );

					jQuery( '#' + jQuery( this ).data( 'sortable-id' ) + '-hidden-value-csv ' ).val( value );

					fusionredux_change( jQuery( this ) );
				}
			} );
		} );
	};

	fusionredux.field_objects.sortable.scrolling = function( selector ) {
		var scrollable;
		if ( selector === undefined ) {
			return;
		}

		scrollable = selector.find( '.fusionredux-sorter' );

		if ( 'up' === scroll ) {
			scrollable.scrollTop( scrollable.scrollTop() - 20 );
			setTimeout( fusionredux.field_objects.sortable.scrolling, 50 );
		} else if ( 'down' === scroll ) {
			scrollable.scrollTop( scrollable.scrollTop() + 20 );
			setTimeout( fusionredux.field_objects.sortable.scrolling, 50 );
		}
	};

} ( jQuery ) );

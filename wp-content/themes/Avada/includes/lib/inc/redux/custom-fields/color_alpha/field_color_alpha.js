/*
 Field Color (color)
 */

/*global jQuery, document, fusionredux_change, fusionredux*/

(function( $ ) {
    'use strict';

    fusionredux.field_objects = fusionredux.field_objects || {};
    fusionredux.field_objects.color = fusionredux.field_objects.color || {};

    $( document ).ready(
        function() {

        }
    );

    fusionredux.field_objects.color.init = function( selector ) {

        if ( !selector ) {
            selector = $( document ).find( ".fusionredux-group-tab:visible" ).find( '.fusionredux-container-color:visible' );
        }

        $( selector ).each(
            function() {
            	debugger;
                var el = $( this );
                var parent = el;

                if ( !el.hasClass( 'fusionredux-field-container' ) ) {
                    parent = el.parents( '.fusionredux-field-container:first' );
                }
                if ( parent.is( ":hidden" ) ) { // Skip hidden fields
                    return;
                }
                if ( parent.hasClass( 'fusionredux-field-init' ) ) {
                    parent.removeClass( 'fusionredux-field-init' );
                } else {
                    return;
                }

                console.log( 'Init AWB'. el.find( '.fusionredux-color-init' ).length );
                el.find( '.fusionredux-color-init' ).awbColorPicker(
                    {
                        change: function( e, ui ) {
                            $( this ).val( ui.color.toString() );
                            fusionredux_change( $( this ) );
                            el.find( '#' + e.target.getAttribute( 'data-id' ) + '-transparency' ).removeAttr( 'checked' );
                        },
                        clear: function( e, ui ) {
                            $( this ).val( '' );
                            fusionredux_change( $( this ).parent().find( '.fusionredux-color-init' ) );
                        },
                        palettes: ['#000000', '#ffffff', '#f44336', '#E91E63', '#03A9F4', '#00BCD4', '#8BC34A', '#FFEB3B', '#FFC107', '#FF9800', '#607D8B']
                    }
                );
            }
        );
    };
})( jQuery );

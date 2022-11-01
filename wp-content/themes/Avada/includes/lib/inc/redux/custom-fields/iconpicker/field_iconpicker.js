
/*global jQuery, document, fusionredux*/

(function( $ ) {
    "use strict";

    fusionredux.field_objects = fusionredux.field_objects || {};
    fusionredux.field_objects.iconpicker = fusionredux.field_objects.iconpicker || {};

    $( document ).ready( function() {
            fusionredux.field_objects.iconpicker.init();
        },

		jQuery( '.fusionredux-group-tab-link-a, .fusionredux-repeater-accordion-repeater h3' ).on( 'click', function() {
			setTimeout( function() {
				fusionredux.field_objects.iconpicker.init();
			}, 100);
		}),
    );

    fusionredux.field_objects.iconpicker.init = function( selector ) {

        if ( !selector ) {
            selector = $( document ).find( '.fusionredux-container-iconpicker:visible' );
        }
        $( selector ).each(
            function() {
                var el = $( this );
                var parent = el;
				if ( jQuery(el).find('.fusion-icon-picker-nav').length > 0 ) {
					return;
				}
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

				FusionPageBuilder.options.fusionIconPicker.optionIconpicker( el );

            }
        );


    };
})( jQuery );

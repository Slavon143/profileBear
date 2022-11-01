( function( $ ) {
    "use strict";
    var typoSets = {};
    fusionredux.field_objects            = fusionredux.field_objects || {};
    fusionredux.field_objects.typography = fusionredux.field_objects.typography || {};

    fusionredux.field_objects.typography.init = function( selector, skipCheck) {

        if ( !selector ) {
            selector = $( document ).find( ".fusionredux-group-tab:visible" ).find( '.fusionredux-container-typography:visible' );
        }
        $( selector ).each(
            function() {
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

                el.each(
                    function() {
                    	var $el = jQuery( this );

						if ( $el.find( '.awb-typography' ).length ) {
							if ( 'undefined' === typeof window.awbTypographySelect || 'undefined' === typeof window.awbTypographySelect.webfonts ) {
								jQuery.when( window.awbTypographySelect.getWebFonts() ).done( function() {
									$el.find( '.awb-typography' ).each( function() {
										typoSets[ jQuery( this ).attr( 'data-id' ) ] = new AwbTypography( jQuery( this ).parent()[0], fusionredux );
									} );
								} );
							} else {
								$el.find( '.awb-typography' ).each( function() {
									typoSets[ jQuery( this ).attr( 'data-id' ) ] = new AwbTypography( jQuery( this ).parent()[0], fusionredux );
								} );
							}
						}
                    }
                );
            }
        );
    };
})( jQuery );

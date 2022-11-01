/*global jQuery */

( function() {
	'use strict';

	fusionredux.field_objects                 = fusionredux.field_objects || {};
	fusionredux.field_objects.typography_sets = fusionredux.field_objects.typography_sets || {};

	fusionredux.field_objects.typography_sets.init = function( selector ) {

		if ( ! selector ) {
			selector = jQuery( document ).find( '.fusionredux-group-tab' ).find( '.fusionredux-container-typography_sets' );
		}

		jQuery( selector ).each( function() {
			var $set = jQuery( this ),
				typoSets;

			if ( $set.hasClass( 'typo-init' ) ) {
				return;
			}
			// Init sets.
			if ( _.isUndefined( window.awbTypographySelect ) || _.isUndefined( window.awbTypographySelect.webfonts ) ) {
				jQuery.when( window.awbTypographySelect.getWebFonts() ).done( function() {
					typoSets = new AwbTypographySet( $set[ 0 ], this );
					$set.addClass( 'typo-init' )
				} );
			} else {
				typoSets = new AwbTypographySet( $set[ 0 ], this );
				$set.addClass( 'typo-init' )
			}

		} );
	};

} ( jQuery ) );

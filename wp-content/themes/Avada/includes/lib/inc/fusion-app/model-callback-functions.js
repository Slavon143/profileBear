/* global FusionApp, fusionSanitize */
/* eslint no-unused-vars: 0 */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.Callback = Backbone.Model.extend( {
		fusionOption: function( value, args ) {
			var poValue = false;
			if ( 'object' === typeof args && 'string' === typeof args.id && 'string' === typeof args.type ) {
				if ( 'PO' === args.type && '' !== value ) {
					return value;
				}  else if ( 'PO' === args.type ) {
					return FusionApp.settings[ args.id ];
				}
				poValue = 'undefined' !== typeof FusionApp.data.postMeta._fusion && 'undefined' !== typeof FusionApp.data.postMeta._fusion[ args.id ] ? FusionApp.data.postMeta._fusion[ args.id ] : false;
				if ( poValue && '' !== poValue ) {
					return poValue;
				}
				return value;
			}
			return value;
		},

		awbHeaderBreakpoint: function( value, args ) {
			var $contents     = jQuery( '#fb-preview' ).contents(),
				breakpointVal = 1;

			if ( 'medium' === value || 'small' === value ) {
				breakpointVal = fusionSanitize.getOption( 'visibility_' + value );
			} else if ( 'custom' === value ) {
				breakpointVal = 'undefined' !== typeof FusionApp.data.postMeta._fusion.header_custom_breakpoint ? FusionApp.data.postMeta._fusion.header_custom_breakpoint : 800;
			}
			$contents.find( '#awb-side-header-css' ).attr( 'media', 'only screen and (min-width: ' +  parseInt( breakpointVal, 10 ) + 'px)' );
		},

		awbCustomHeaderBreakpoint: function( value, args ) {
			if ( 'undefined' !== typeof FusionApp.data.postMeta._fusion.header_breakpoint && 'custom' === FusionApp.data.postMeta._fusion.header_breakpoint ) {
				jQuery( '#fb-preview' ).contents().find( '#awb-side-header-css' ).attr( 'media', 'only screen and (min-width: ' +  parseInt( value, 10 ) + 'px)' );
			}
		},

		awbHeaderPosition: function( value, args ) {
			var $body = jQuery( '#fb-preview' ).contents().find( 'body' );

			if ( 'left' === value || 'right' === value ) {
				$body.removeClass( 'awbh-left awbh-right' ).addClass( 'side-header awbh-' + value );
				if ( 'undefined' === typeof FusionApp.data.postMeta._fusion.header_breakpoint ) {
					FusionApp.data.postMeta._fusion.header_breakpoint = 'small';
				}
				this.awbHeaderBreakpoint( FusionApp.data.postMeta._fusion.header_breakpoint, args );
			} else {
				$body.removeClass( 'side-header awbh-left awbh-right' );
			}
		}
	} );

}( jQuery ) );

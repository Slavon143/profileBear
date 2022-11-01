/* global ajaxurl */

var jQuery,
	mla_javascript_example_vars,
	mlaJavaScriptExample = {
		// Properties
		// mlaJavaScriptExample.settings.fullCaptionId
		settings: {},

		// Utility functions, if any
		utility: {
			example : function() {
			}
		},

		// Components
		featherlightCaption: null
	};

( function( $ ) {
	"use strict";

	/**
	 * Localized settings and strings
	 */
	mlaJavaScriptExample.settings = typeof mla_javascript_example_vars === 'undefined' ? {} : mla_javascript_example_vars;
	mla_javascript_example_vars = void 0; // delete won't work on Globals

	mlaJavaScriptExample.featherlightCaption = {
		init : function(){
			$.featherlight.defaults.afterContent = mlaJavaScriptExample.featherlightCaption.mlaCaption;
		},

		mlaCaption : function() {
			var object    = this.$instance,
				target    = this.$currentTarget,
				caption   = target.attr( mlaJavaScriptExample.settings.fullCaptionId );

			object.find( '.caption' ).remove();
			if ( ( typeof caption !== 'undefined' ) && ( 0 !== caption.length ) ) {
				var $captionElm = $( '<div class="caption">' ).appendTo( object.find( '.featherlight-content' ) );
				$captionElm[0].innerHTML = caption;
			}
		}
	}; // mlaJavaScriptExample.featherlightCaption

	$( document ).ready( function() {
		mlaJavaScriptExample.featherlightCaption.init();
	});
})( jQuery );

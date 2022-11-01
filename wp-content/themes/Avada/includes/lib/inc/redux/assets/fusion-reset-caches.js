/* global fusionReduxResetCaches */
function fusionResetCaches( e ) { // jshint ignore:line
	var data = {
			action: 'fusion_reset_all_caches'
		},
		$el     = jQuery( e.target ).closest( '.fusion_options' ),
		confirm = window.confirm( fusionReduxResetCaches.general.confirm );

	e.preventDefault();

	if ( true === confirm ) {
		$el.find( '.spinner.fusion-spinner' ).addClass( 'is-active' );
		jQuery.post( fusionReduxResetCaches.ajaxurl, data, function() {
			$el.find( '.spinner.fusion-spinner' ).removeClass( 'is-active' );
			alert( fusionReduxResetCaches.general.success ); // jshint ignore: line
		} );
	}
}

function fusionResetMailchimpCache( e ) { // jshint ignore:line
	var data = {
			action: 'fusion_reset_mailchimp_caches'
		},
		$el     = jQuery( e.target ).closest( '.fusion_options' ),
		confirm = window.confirm( fusionReduxResetCaches.mailchimp.confirm );

	e.preventDefault();

	if ( true === confirm ) {
		$el.find( '.spinner.fusion-spinner' ).addClass( 'is-active' );
		jQuery.post( fusionReduxResetCaches.ajaxurl, data, function() {
			$el.find( '.spinner.fusion-spinner' ).removeClass( 'is-active' );
			alert( fusionReduxResetCaches.mailchimp.success ); // jshint ignore: line
		} );
	}
}

function fusionResetHubSpotCache( e ) { // jshint ignore:line
	var data = {
			action: 'fusion_reset_hubspot_caches'
		},
		$el     = jQuery( e.target ).closest( '.fusion_options' ),
		confirm = window.confirm( fusionReduxResetCaches.hubspot.confirm );

	e.preventDefault();

	if ( true === confirm ) {
		$el.find( '.spinner.fusion-spinner' ).addClass( 'is-active' );
		jQuery.post( fusionReduxResetCaches.ajaxurl, data, function() {
			$el.find( '.spinner.fusion-spinner' ).removeClass( 'is-active' );
			alert( fusionReduxResetCaches.hubspot.success ); // jshint ignore: line
		} );
	}
}

function fusionResetInstagramCache( e ) { // jshint ignore:line
	var data = {
			action: 'fusion_reset_instagram_caches'
		},
		$el     = jQuery( e.target ).closest( '.fusion_options' ),
		confirm = window.confirm( fusionReduxResetCaches.instagram.confirm );

	e.preventDefault();

	if ( true === confirm ) {
		$el.find( '.spinner.fusion-spinner' ).addClass( 'is-active' );
		jQuery.post( fusionReduxResetCaches.ajaxurl, data, function() {
			$el.find( '.spinner.fusion-spinner' ).removeClass( 'is-active' );
			alert( fusionReduxResetCaches.instagram.success ); // jshint ignore: line
		} );
	}
}

function fusionResetAdobeFontsCache( e ) { // jshint ignore:line
	var data = {
			action: 'fusion_reset_avada_fonts_cache'
		},
		$el     = jQuery( e.target ).closest( '.fusion_options' ),
		confirm = window.confirm( fusionReduxResetCaches.adobe.confirm );

	e.preventDefault();

	if ( true === confirm ) {
		$el.find( '.spinner.fusion-spinner' ).addClass( 'is-active' );
		jQuery.post( fusionReduxResetCaches.ajaxurl, data, function() {
			$el.find( '.spinner.fusion-spinner' ).removeClass( 'is-active' );
			alert( fusionReduxResetCaches.adobe.success ); // jshint ignore: line
		} );
	}
}

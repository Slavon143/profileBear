var $avadaVersion;
window.$versionSuffix = ' beta';

if ( window.jQuery ) {
	jQuery( document ).ready( function() {

		// Main Dashboard Version Area.
		jQuery( '.avada-dashboard .avada-db-version span' ).text( jQuery( '.avada-dashboard .avada-db-version span' ).text() + window.$versionSuffix );

		// Avada Plugins page
		jQuery( '.avada-install-plugins' ).find( '.fusion-admin-box:nth-child(1), .fusion-admin-box:nth-child(2)' ).each( function() {
			var $versionContainer = jQuery( this ).find( '.plugin-info' ),
				$html = $versionContainer.html().replace( '|', window.$versionSuffix + ' |' );

			$versionContainer.html( $html );
		} );

		// WP Plugins page
		jQuery( 'table.plugins #the-list' ).find( '[data-slug="avada-core"], [data-slug="avada-builder"]' ).each( function() {
			var $versionContainer = jQuery( this ).find( '.plugin-version-author-uri' ),
				$html = $versionContainer.html().replace( '| By', window.$versionSuffix + ' | By' );

			$versionContainer.html( $html );
		} );

	} );
} else {

	// Splash Screens
	$avadaVersion = document.getElementsByClassName( 'avada-version-inner' );
	$avadaVersion[ '0' ].textContent = $avadaVersion[ '0' ].textContent + window.$versionSuffix;
}

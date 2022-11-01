jQuery( window ).on( 'load', function() {
	var sidebar1Option,
		sidebar2Option;

	// Find the 1st sidebar depending on the post-type and available options.
	[
		'pages_sidebar',
		'posts_sidebar',
		'portfolio_sidebar',
		'woo_sidebar',
		'ec_sidebar',
		'ppbress_sidebar'
	].forEach( function( option ) {
		if ( jQuery( '#pyre_' + option ).length ) {
			sidebar1Option = option;
		}
	} );

	// Find the 2nd sidebar depending on the post-type and available options.
	[
		'pages_sidebar_2',
		'posts_sidebar_2',
		'portfolio_sidebar_2',
		'woo_sidebar_2',
		'ec_sidebar_2',
		'ppbress_sidebar_2'
	].forEach( function( option ) {
		if ( jQuery( '#pyre_' + option ).length ) {
			sidebar2Option = option;
		}
	} );

	// Early exit if we didn't find sidebars options.
	if ( ! sidebar1Option || ! sidebar2Option ) {
		return;
	}

	// Initial classes set on page load.
	setSidebarClasses();

	// Change classes when the sidebar-1 option changes.
	jQuery( '.block-editor-page' ).on( 'change', '#pyre_' + sidebar1Option, function() {
		setSidebarClasses();
	} );

	// Change classes when the sidebar-2 option changes.
	jQuery( '.block-editor-page' ).on( 'change', '#pyre_' + sidebar2Option, function() {
		setSidebarClasses();
	} );

	function setSidebarClasses() {
		var sidebarOneValue = jQuery( '#pyre_' + sidebar1Option ).children( 'option:selected' ).val(),
			sidebarOneText  = jQuery( '#pyre_' + sidebar1Option ).children( 'option:selected' ).text(),
			sidebarTwoValue = jQuery( '#pyre_' + sidebar2Option ).children( 'option:selected' ).val(),
			sidebarTwoText  = jQuery( '#pyre_' + sidebar2Option ).children( 'option:selected' ).text();

		// No sidebar.
		if ( ! sidebarOneValue || ( 'default_sidebar' === sidebarOneValue && -1 !== sidebarOneText.indexOf( 'None' ) ) ) {
			jQuery( '.block-editor-page' ).removeClass( 'has-sidebar' ).removeClass( 'double-sidebars' );
			return;
		}

		// Single sidebar.
		jQuery( '.block-editor-page' ).addClass( 'has-sidebar' );

		if ( ! sidebarTwoValue || ( 'default_sidebar' === sidebarTwoValue && -1 !== sidebarTwoText.indexOf( 'None' ) ) ) {
			jQuery( '.block-editor-page' ).removeClass( 'double-sidebars' );
			return;
		}

		// Double sidebars.
		jQuery( '.block-editor-page' ).addClass( 'double-sidebars' );
	}
} );

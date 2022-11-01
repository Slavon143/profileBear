jQuery( document ).ready( function() {
	var $parentElement,
		$fusionMenu;

	jQuery( '.fusionredux-action_bar .spinner' ).addClass( 'avada-db-loader' );

	jQuery( '#fusionredux-import' ).on( 'click', function( e ) {

		var loader = '<span class="spinner" style="visibility: visible;float: none;display: inline-block;"></span>';
 		e.preventDefault();

 		if ( '' !== jQuery( '#import-code-value' ).val() || '' !== jQuery( '#import-link-value' ).val() ) {
 			jQuery( loader ).insertAfter( jQuery( this ) );
 		}

		jQuery.ajax({
			type:     'post',
			dataType: 'json',
			url:       ajaxurl,
			data: {
				action: 'custom_option_import_code',
 					security: jQuery( '#ajaxsecurity' ).val(),
 					data: { import_code: jQuery( '#import-code-value' ).val(), import_link: jQuery( '#import-link-value' ).val() }
			}
		} )
		.done( function( result ) {
			if ( 'success' == result.status ) {
				jQuery.ajax({
					type:     'post',
					dataType: 'json',
					url:       ajaxurl,
					data: {
						action: 'custom_option_import',
						data: jQuery( '#import-code-value' ).val()
					}
				} )
				.done( function() {
					window.location = window.location;
				} );
			}
		} );
	});

	// Style the update selections.
	jQuery( 'select.update-select.fusionredux-select-item' ).each(
		function() {
			var defaultParams = {
				width: '180px',
				triggerChange: true,
				allowClear: true,
				minimumResultsForSearch: Infinity
			};
			jQuery( this ).select3( defaultParams );
			jQuery( this ).on( 'change', function() {

				// jscs:disable
				/* jshint ignore:start */
				fusionredux_change( jQuery( jQuery( this ) ) );


				// jscs:enable
				/* jshint ignore:end */
				jQuery( this ).select3SortableOrder();
			});
		}
	);

	// Activate the Fusion admin menu Global Option entry when Global Options are active.
	if ( jQuery( 'a[href="themes.php?page=fusion_options"]' ).hasClass( 'current' ) ) {
		$fusionMenu = jQuery( '#toplevel_page_fusion' );

		$fusionMenu.addClass( 'wp-has-current-submenu wp-menu-open' );
		$fusionMenu.children( 'a' ).addClass( 'wp-has-current-submenu wp-menu-open' );
		$fusionMenu.children( '.wp-submenu' ).find( 'li' ).last().addClass( 'current' );
		$fusionMenu.children( '.wp-submenu' ).find( 'li' ).last().children().addClass( 'current' );

		// Do not show the appearance menu as active
		jQuery( '#menu-appearance a[href="themes.php"]' ).removeClass( 'wp-has-current-submenu wp-menu-open' );
		jQuery( '#menu-appearance' ).removeClass( 'wp-has-current-submenu wp-menu-open' );
		jQuery( '#menu-appearance' ).addClass( 'wp-not-current-submenu' );
		jQuery( '#menu-appearance a[href="themes.php"]' ).addClass( 'wp-not-current-submenu' );
		jQuery( '#menu-appearance' ).children( '.wp-submenu' ).find( 'li' ).removeClass( 'current' );
	}

	$parentElement = jQuery( '#' + fusionFusionreduxVars.option_name + '-social_media_icons .fusionredux-repeater-accordion' );

	// Initialize fusionredux color fields, even when they are insivible
	fusionredux.field_objects.color.init( $parentElement.find( '.fusionredux-container-color ' ) );

	$parentElement.set_social_media_repeater_custom_field_logic();

	jQuery( '.fusionredux-repeaters-add' ).on( 'click', function() {
		setTimeout( function() {
			$parentElement = jQuery( '#' + fusionFusionreduxVars.option_name + '-social_media_icons .fusionredux-repeater-accordion' );
			$parentElement.set_social_media_repeater_custom_field_logic();
			fusionredux.field_objects.iconpicker.init( $parentElement.eq(0).find('.fusionredux-repeater-accordion-repeater').last() );
		}, 50 );
	});

	// Make sure the sub menu flyouts are closed, when a new menu item is activated
	jQuery( '.fusionredux-group-tab-link-li a' ).on( 'click', function() {
		jQuery( '.fusionredux-group-tab-link-li' ).removeClass( 'fusion-section-hover' );
		jQuery.fusionredux.required();
		jQuery.fusionredux.check_active_tab_dependencies();
	});

	// Make submenus flyout when a main menu item is hovered
	jQuery( '.fusionredux-group-tab-link-li.hasSubSections' ).each( function() {
		jQuery( this ).on( 'mouseenter', function() {
			if ( ! jQuery( this ).hasClass( 'activeChild' ) ) {
				jQuery( this ).addClass( 'fusion-section-hover' );
			}
		});

		jQuery( this ).on( 'mouseleave', function() {
			jQuery( this ).removeClass( 'fusion-section-hover' );
		});
	});

	// Add a pattern preview container to show off the background patterns
	jQuery( '.fusion_theme_options-bg_pattern' ).append( '<div class="fusion-pattern-preview"></div>' );

	// On pattern image click update the preview
	jQuery( '.fusion_theme_options-bg_pattern' ).find( 'ul li img' ).on( 'click', function() {
		var $background = 'url("' + jQuery( this ).attr( 'src' ) + '") repeat';
		jQuery( '.fusion-pattern-preview' ).css( 'background', $background );
	});

	// Color picker fallback for pre WP 4.4 versions
	jQuery( '.wp-color-result' ).on( 'click', function() {
		jQuery( this ).parent().addClass( 'wp-picker-active' );
	});

	jQuery( '.fusion_theme_options-header_layout img' ).on( 'click', function() {

		// Auto adjust main menu height
		var $headerVersion = jQuery( this ).attr( 'alt' ),
		    $mainMenuHeight = '0';

		if ( 'v1' === $headerVersion || 'v2' === $headerVersion || 'v3' === $headerVersion || 'v7' === $headerVersion ) {
			$mainMenuHeight = '84';
		} else {
			$mainMenuHeight = '40';
		}

		jQuery( 'input#nav_height' ).val( $mainMenuHeight );

		// Auto adjust logo margin
		if ( 'v4' === $headerVersion ) {
			jQuery( '.fusion_theme_options-logo_margin .fusionredux-spacing-bottom, .fusion_theme_options-logo_margin #logo_margin-bottom' ).val( '0px' );
		} else {
			jQuery( '.fusion_theme_options-logo_margin .fusionredux-spacing-bottom, .fusion_theme_options-logo_margin #logo_margin-bottom' ).val( '31px' );
		}
		jQuery( '.fusion_theme_options-logo_margin .fusionredux-spacing-top, .fusion_theme_options-logo_margin #logo_margin-top' ).val( '31px' );

		// Auto adjust header v2 topbar color
		if ( 'v2' === $headerVersion ) {
			jQuery( '.fusion_theme_options-header_top_bg_color #header_top_bg_color-color' ).val( '#fff' );
		} else {
			jQuery( '.fusion_theme_options-header_top_bg_color #header_top_bg_color-color' ).val( jQuery( '#primary_color-color' ).val() );
		}
	});

	jQuery( '#fusion_options-header_position label' ).on( 'click', function() {
		var $headerPosition = jQuery( this ).find( 'span' ).text(),
		    $headerVersion  = jQuery( '.fusion_theme_options-header_layout' ).find( '.fusionredux-image-select-selected img' ).attr( 'alt' ),
		    $mainMenuHeight;

		// Auto adjust main menu height
		if ( 'top' === $headerPosition.toLowerCase() ) {
			if ( 'v1' === $headerVersion || 'v2' === $headerVersion || 'v3' === $headerVersion ) {
				$mainMenuHeight = '84';
			} else {
				$mainMenuHeight = '40';
			}
		} else {
			$mainMenuHeight = '40';
		}
		jQuery( 'input#nav_height' ).val( $mainMenuHeight );

		// Auto set header padding
		jQuery( '.fusion_theme_options-header_padding input' ).val( '0px' );
		if ( 'top' !== $headerPosition.toLowerCase() ) {
			jQuery( '.fusion_theme_options-header_padding input.fusionredux-spacing-left, .fusion_theme_options-header_padding #header_padding-left, .fusion_theme_options-header_padding input.fusionredux-spacing-right, .fusion_theme_options-header_padding #header_padding-right' ).val( '60px' );
		}

		// Auto adjust logo margin
		jQuery( '.fusion_theme_options-logo_margin .fusionredux-spacing-top, .fusion_theme_options-logo_margin #logo_margin-top, .fusion_theme_options-logo_margin .fusionredux-spacing-bottom, .fusion_theme_options-logo_margin #logo_margin-bottom' ).val( '31px' );
		if ( 'top' === $headerPosition.toLowerCase() && 'v4' === $headerVersion ) {
			jQuery( '.fusion_theme_options-logo_margin .fusionredux-spacing-bottom, .fusion_theme_options-logo_margin #logo_margin-bottom' ).val( '0px' );
		}
	});

	// Listen for changes to header position and reset to 1 if changing away from top.
	jQuery( '.fusion_theme_options-header_position' ).on( 'change', function() {
		var $widthVal = jQuery( '#menu_arrow_size-width' ).val(),
		    $heightVal = jQuery( '#menu_arrow_size-height' ).val(),
			$widthDimension = jQuery( '#menu_arrow_size .fusionredux-dimensions-width, #menu_arrow_size-width' ),
		    $heightDimension = jQuery( '#menu_arrow_size .fusionredux-dimensions-height, #menu_arrow_size-height' );

		if ( 'top' !== jQuery( this ).find( '.ui-state-active' ).prev( 'input' ).val() ) {
			if ( parseInt( $widthVal ) > parseInt( $heightVal ) ) {
				$widthDimension.val( $heightVal );
				$heightDimension.val( $widthVal );
			}
		} else if ( parseInt( $heightVal ) > parseInt( $widthVal ) ) {
			$widthDimension.val( $heightVal );
			$heightDimension.val( $widthVal );
		}
	});

	function fusionMenuHint() {
		var $logoHeight = jQuery( '.fusion_theme_options-logo .upload-height' ).val(),
		    $logoTopMargin = ( '' === jQuery( 'input[rel="logo_margin-top"]' ).val() ) ? '0' : jQuery( 'input[rel="logo_margin-top"]' ).val(),
		    $logoBottomMargin = ( '' === jQuery( 'input[rel="logo_margin-bottom"]' ).val() ) ? '0' : jQuery( 'input[rel="logo_margin-bottom"]' ).val(),
		    $fullLogoHeight = '',
		    $headerVersion = jQuery( '.fusion_theme_options-header_layout' ).find( '.fusionredux-image-select-selected img' ).attr( 'alt' );

		if ( 'undefined' !== typeof $logoTopMargin && ( -1 !== $logoTopMargin.indexOf( 'px' ) || '0' === $logoTopMargin ) && ( -1 !== $logoBottomMargin.indexOf( 'px' ) || '0' === $logoBottomMargin ) && $logoHeight && 'v4' !== $headerVersion && 'v5' !== $headerVersion && 'v6' !== $headerVersion ) {
			$fullLogoHeight = parseInt( $logoHeight ) + parseInt( $logoTopMargin  ) + parseInt( $logoBottomMargin  );
			jQuery( '#fusion-menu-height-hint strong' ).html( $fullLogoHeight );
			jQuery( '#fusion-menu-height-hint' ).fadeIn( 'fast' );
			jQuery( '#fusion-menu-height-hint' ).css( 'display', 'inline' );
		} else {
			jQuery( '#fusion-menu-height-hint' ).hide();
		}
	}

	// Trigger on load.
	fusionMenuHint();

	// When we load the menu tab, recalculate menu hint.
	jQuery( 'a[data-css-id="heading_menu_section"], a[data-css-id="heading_menu"]' ).on( 'click', function() {
		fusionMenuHint();
	});

	// Listen for changes to medium and update large description.
	jQuery( '#visibility_medium, .fusion_theme_options-visibility_medium noUi-handle' ).on( 'change update click', function() {
		jQuery( '#fusion-visibility-large span' ).html( jQuery( this ).val() );
	});

	jQuery( '#animations_shortcode_section_start_accordion' ).prev( '.form-table' ).remove();

});

jQuery( window ).on( 'load', function() {

	// If search field is not empty, make sidebar accessible again when an item is clicked and clear the search field
	jQuery( '.fusionredux-sidebar a' ).on( 'click', function() {

		var $tabToActivate,
		    $tabToActivateID,
		    $fusionreduxOptionTabExtras;

		if ( '' !== jQuery( '.fusionredux_field_search' ).val() ) {
			if ( jQuery( this ).parent().hasClass( 'hasSubSections' ) ) {
				$tabToActivateID = jQuery( this ).data( 'rel' ) + 1;
			} else {
				$tabToActivateID = jQuery( this ).data( 'rel' );
			}

			$tabToActivate = '#' + $tabToActivateID + '_section_group';
			$fusionreduxOptionTabExtras = jQuery( '.fusionredux-container' ).find( '.fusionredux-section-field, .fusionredux-info-field, .fusionredux-notice-field, .fusionredux-container-group, .fusionredux-section-desc, .fusionredux-group-tab h3, .fusionredux-accordion-field' );

			// Show the correct tab

			jQuery( '.fusionredux-main' ).find( '.fusionredux-group-tab' ).not( $tabToActivate ).hide();
			jQuery( '.fusionredux-accordian-wrap' ).hide();
			$fusionreduxOptionTabExtras.show();
			jQuery( '.form-table tr' ).show();
			jQuery( '.form-table tr.hide' ).hide();
			jQuery( '.fusionredux-notice-field.hide' ).hide();

			jQuery( '.fusionredux-container' ).removeClass( 'fusion-redux-search' );
			jQuery( '.fusionredux_field_search' ).val( '' );
			jQuery( '.fusionredux_field_search' ).trigger( 'change' );
		}
	});

	jQuery( '.fusionredux_field_search' ).typeWatch({

		callback: function( $searchString ) {
			var $tab;

			$searchString = $searchString.toLowerCase();

			if ( '' !== $searchString && null !== $searchString && 'undefined' !== typeof $searchString && $searchString.length > 2 ) {
				jQuery( '.fusionredux-sidebar .fusionredux-group-menu' ).find( 'li' ).removeClass( 'activeChild' ).removeClass( 'active' );
				jQuery( '.fusionredux-sidebar .fusionredux-group-menu' ).find( '.submenu' ).hide();
				jQuery( '.fusionredux-sidebar .fusionredux-group-menu' ).find( '.subsection' ).hide();

			} else {
				$tab = jQuery.cookie( 'fusionredux_current_tab' );

				if ( jQuery( '#' + $tab + '_section_group_li' ).parents( '.hasSubSections' ).length ) {
					jQuery( '#' + $tab + '_section_group_li' ).parents( '.hasSubSections' ).addClass( 'activeChild' );
					jQuery( '#' + $tab + '_section_group_li' ).parents( '.hasSubSections' ).find( '.submenu' ).show();
					jQuery( '#' + $tab + '_section_group_li' ).parents( '.hasSubSections' ).find( '.subsection' ).show();
				}
				jQuery( '#' + $tab + '_section_group_li' ).addClass( 'active' );
			}
		},

		wait: 500,
		highlight: false,
		captureLength: 0

	} );
});

jQuery.fn.set_social_media_repeater_custom_field_logic = function() {
	jQuery( this ).each( function( i, obj ) {

		var $iconSelect    = jQuery( '#icon-' + i + '-select' ),
		    $customFields  = jQuery( '#' + fusionFusionreduxVars.option_name + '-custom_title-' + i + ', #' + fusionFusionreduxVars.option_name + '-custom_source-' + i );

		// Get the initial value of the select input and depending on its value
		// show or hide the custom icon input elements
		if ( 'custom' == $iconSelect.val() ) {

			// Show input fields & headers
			$customFields.show();
			$customFields.prev().show();
		} else {

			// Hide input fields & headers
			$customFields.hide();
			$customFields.prev().hide();
		}

		if ( ! $iconSelect.val() ) {
			$iconSelect.parents( '.ui-accordion-content' ).css( 'height', '' );
		}

		// Check if the value of the select has changed and show/hide the elements conditionally.
		$iconSelect.on( 'change', function() {
			$iconSelect.parents( '.ui-accordion-content' ).css( 'height', '' );

			if ( 'custom' == jQuery( this ).val() ) {

				// Show input fields & headers
				$customFields.show();
				$customFields.prev().show();
			} else {

				// Hide input fields & headers
				$customFields.hide();
				$customFields.prev().hide();
			}
		});
	});
};

function fusionOpenOption( $hrefTarget ) {
	var $optionTarget,
	    $tabTarget,
	    $adminbarHeight,
	    $theTarget;

	 if ( 'object' === typeof $hrefTarget ) {
	 	$hrefTarget = $hrefTarget[1];
	 }
	// If it doesn't contains tab- then assume as option.
	if ($hrefTarget.indexOf( 'tab-' ) == -1 ) {
		$optionTarget   = '.fusion_theme_options-' + $hrefTarget;
		$tabTarget      = jQuery( $optionTarget ).parents( '.fusionredux-group-tab' ).data( 'rel' );
		$adminbarHeight = 0;

		if ( $tabTarget ) {

			// Check if target element exists.
			$theTarget = jQuery( 'a[data-key="' + $tabTarget + '"]' );
			if ( $theTarget ) {
				setTimeout( function() {

					// Open desired tab.
					jQuery( 'a[data-key="' + $tabTarget + '"]' ).click();
					if ( 'heading_shortcode_styling' == $theTarget.data( 'css-id' ) || 'fusion_builder_elements' == $theTarget.data( 'css-id' ) || 'fusion_builder_addons' == $theTarget.data( 'css-id' ) ) {
						jQuery( $optionTarget ).parents( '.fusionredux-accordian-wrap' ).prev( 'div' ).click();
					}
					setTimeout( function() {

						// Scroll to the desired option.
						if ( jQuery( '#wpadminbar' ).length ) {
							$adminbarHeight = parseInt( jQuery( '#wpadminbar' ).outerHeight() );
						}
						jQuery( 'html, body' ).animate({
							scrollTop: jQuery( $optionTarget ).closest( 'tr' ).offset().top - $adminbarHeight }, 450
						);
					}, 200 );
				}, 100 );
			}

		}
	} else {
		$tabTarget = $hrefTarget.split( '-' );
		$theTarget = jQuery( 'a[data-css-id="' + $hrefTarget + '"]' );

		// Check if desired tab exists.
		if ( $theTarget.length ) {

			// Open desired tab.
			setTimeout( function() {
				$theTarget.click();
			}, 100 );
		}
	}
}

jQuery( window ).on( 'load', function() {

	var $hrefTarget;

	// Check option name and open relevant tab.
	if ( location.hash ) {
		$hrefTarget = window.location.href.split( '#' );

		fusionOpenOption( $hrefTarget );
	}

	jQuery( '.fusionredux-container' ).on( 'click', '.fusion-quick-option', function( event ) {
		var option = jQuery( event.target ).data( 'fusion-option' );

		event.preventDefault();

		fusionOpenOption( option );
	} );

	// If we are in a modern browser cleanup HubSpot API parameters.
	if ( 'function' === typeof URL && 'function' === typeof URLSearchParams && window.location.href.includes( 'hubspot' ) ) {
		let url    = new URL( window.location.href  );
		let params = new URLSearchParams( url.search.slice( 1 ) );

		params.delete( 'revoke_hubspot' );
		params.delete( 'hubspot' );
		params.delete( 'token' );
		params.delete( 'refresh' );
		params.delete( 'expires' );
		params.delete( 'error' );
		window.history.replaceState({}, document.title, window.location.pathname + '?' + params.toString() );
	}

});

jQuery( document ).ready( function() {

	// Check to see if the Ajax Notification is visible.
	if ( jQuery( '#remote-media-found-in-fusion-options' ).length > 0 ) {

		jQuery( '#dismiss-fusion-redux-ajax-notification' ).on( 'click', function( event ) {

			event.preventDefault();

			// Initiate a request to the server-side
			jQuery.post( ajaxurl, {
				action: 'fusionredux_hide_remote_media_admin_notification',
				nonce: jQuery.trim( jQuery( '#fusion-redux-remote-media-ajax-notification-nonce' ).text() )
			}, function( response ) {
				if ( '1' === response || 1 === response || true === response ) {
					jQuery( '#remote-media-found-in-fusion-options' ).hide();
				}
				console.log( response );
			});
		});
	}
});

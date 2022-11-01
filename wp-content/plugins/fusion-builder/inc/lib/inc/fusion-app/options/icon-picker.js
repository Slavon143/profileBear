/* global Fuse, FusionApp, fusionIconSearch, fusionBuilderText */
var FusionPageBuilder = FusionPageBuilder || {},
	FusionDelay;

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionDelay = ( function() {
	var timer = 0;

	return function( callback, ms ) {
		clearTimeout( timer );
		timer = setTimeout( callback, ms );
	};
}() );

FusionPageBuilder.options.fusionIconPicker = {
	optionIconpicker: function( $element ) {
		var $iconPicker;

		$element    = $element || this.$el;
		$iconPicker = $element.find( '.fusion-iconpicker' );

		if ( $iconPicker.length ) {
			$iconPicker.each( function() {
				var $input     = jQuery( this ).find( '.fusion-iconpicker-input' ),
					value      = $input.val(),
					splitVal,
					$container       = jQuery( this ).find( '.icon_select_container' ),
					$containerParent = $container.parent(),
					$search          = jQuery( this ).find( '.fusion-icon-search' ),
					output           = jQuery( '.fusion-icons-rendered' ).length ? jQuery( '.fusion-icons-rendered' ).html() : '',
					outputNav        = jQuery( '.fusion-icon-picker-nav-rendered' ).length ? jQuery( '.fusion-icon-picker-nav-rendered' ).html() : '',
					selectedSetId    = '',
					customIcon       = -1 !== value.indexOf( 'fusion-prefix-' );

				$container.append( output ).before( '<div class="fusion-icon-picker-nav-wrapper"><a href="#" class="fusion-icon-picker-nav-left fusiona-arrow-left"></a><div class="fusion-icon-picker-nav">' + outputNav + '</div><a href="#" class="fusion-icon-picker-nav-right fusiona-arrow-right"></a></div>' );

				if ( '' !== value && -1 === value.indexOf( ' ' ) ) {
					if ( 'undefined' !== typeof FusionApp ) {
						value = FusionApp.checkLegacyAndCustomIcons( value );
					}

					// If custom icon we don't need to update input, just value needs converted for below.
					if ( ! customIcon ) {

						// Wait until options tab is rendered.
						setTimeout( function() {

							// Update form field with new values.
							$input.attr( 'value', value ).trigger( 'change' );
						}, 1000 );
					}
				}

				// Icon navigation link is clicked.
				$containerParent.find( '.fusion-icon-picker-nav > .fusion-icon-picker-nav-item' ).on( 'click', function( e ) {
					e.preventDefault();

					jQuery( '.fusion-icon-picker-nav-active' ).removeClass( 'fusion-icon-picker-nav-active' );
					jQuery( this ).addClass( 'fusion-icon-picker-nav-active' );
					$container.find( '.fusion-icon-set' ).css( 'display', 'none' );
					$container.find( jQuery( this ).attr( 'href' ) ).css( 'display', 'grid' );
				} );

				// Scroll nav div to right.
				$containerParent.find( '.fusion-icon-picker-nav-wrapper > .fusion-icon-picker-nav-right' ).on( 'click', function( e ) {
					e.preventDefault();

					$containerParent.find( '.fusion-icon-picker-nav' ).animate( {
						scrollLeft: '+=100'
					}, 250 );
				} );

				// Scroll nav div to left.
				$containerParent.find( '.fusion-icon-picker-nav-wrapper > .fusion-icon-picker-nav-left' ).on( 'click', function( e ) {
					e.preventDefault();

					$containerParent.find( '.fusion-icon-picker-nav' ).animate( {
						scrollLeft: '-=100'
					}, 250 );
				} );

				if ( value && '' !== value ) {
					splitVal = value.split( ' ' );

					if ( 2 === splitVal.length ) {

						// FA.
						$container.find( '.' + splitVal[ 0 ] + '.' + splitVal[ 1 ] ).parent().addClass( 'selected-element' );
					} else if ( 1 === splitVal.length ) {

						// Custom icon.
						$container.find( '.' + splitVal ).parent().addClass( 'selected-element' );
					}

					// Trigger click on parent nav tab item.
					selectedSetId = $container.find( '.selected-element' ).closest( '.fusion-icon-set' ).prepend( $container.find( '.selected-element' ) ).attr( 'id' );
					$containerParent.find( '.fusion-icon-picker-nav a[href="#' + selectedSetId + '"]' ).trigger( 'click' );
				}

				// Icon click.
				$container.find( '.icon_preview' ).on( 'click', function( event ) {
					var $icon      = jQuery( this ).find( 'i' ),
						subset     = 'fas',
						$scopedContainer = jQuery( this ).closest( '.fusion-iconpicker' ),
						fontName   = 'fa-' + $icon.attr( 'data-name' ),
						inputValue = '';


					if ( ! $icon.hasClass( 'fas' ) && ! $icon.hasClass( 'fab' ) && ! $icon.hasClass( 'far' ) && ! $icon.hasClass( 'fal' ) ) {

						// Custom icon set, so we need to add prefix.
						inputValue = 'fusion-prefix-' + $icon.attr( 'class' );
					} else if ( $icon.hasClass( 'fab' ) ) {
						subset = 'fab';
					} else if ( $icon.hasClass( 'far' ) ) {
						subset = 'far';
					} else if ( $icon.hasClass( 'fal' ) ) {
						subset = 'fal';
					}

					// FA icon.
					if ( '' === inputValue ) {
						inputValue = fontName + ' ' + subset;
					}

					if ( jQuery( this ).hasClass( 'selected-element' ) ) {
						jQuery( this ).removeClass( 'selected-element' );
						$scopedContainer.find( 'input.fusion-iconpicker-input' ).attr( 'value', '' ).trigger( 'change' );
						$scopedContainer.find( '.fusion-iconpicker-icon > span' ).attr( 'class', '' );
					} else {
						$scopedContainer.find( '.selected-element' ).removeClass( 'selected-element' );
						jQuery( event.currentTarget ).addClass( 'selected-element' );
						$scopedContainer.find( 'input.fusion-iconpicker-input' ).attr( 'value', inputValue ).trigger( 'change' );
						$scopedContainer.find( '.fusion-iconpicker-icon > span' ).attr( 'class', inputValue );
					}
				} );

				// Icon Search bar
				$search.on( 'change paste keyup', function() {
					var $searchInput = jQuery( this );

					FusionDelay( function() {
						var options,
							fuse,
							result;

						if ( $searchInput.val() && '' !== $searchInput.val() ) {
							value = $searchInput.val().toLowerCase();

							if ( 3 > value.length ) {
								return;
							}

							$container.find( '.icon_preview' ).css( 'display', 'none' );
							options = {
								threshold: 0.2,
								location: 0,
								distance: 100,
								maxPatternLength: 32,
								minMatchCharLength: 3,
								keys: [
									'name',
									'keywords',
									'categories'
								]
							};
							fuse   = new Fuse( fusionIconSearch, options );
							result = fuse.search( value );

							// Show icons.
							_.each( result, function( resultIcon ) {
								$container.find( '.icon_preview.' + resultIcon.name ).css( 'display', 'inline-flex' );
							} );

							// Add attributes to iconset containers.
							_.each( $container.find( '.fusion-icon-set' ), function( subContainer ) {
								var hasSearchResults = false;
								subContainer.classList.add( 'no-search-results' );
								subContainer.querySelectorAll( '.icon_preview' ).forEach( function( icon ) {
									if ( 'none' !== icon.style.display && subContainer.classList.contains( 'no-search-results' ) ) {
										hasSearchResults = true;
									}
								} );

								if ( ! hasSearchResults && ! subContainer.querySelector( '.no-search-results-notice' ) ) {
									jQuery( subContainer ).append( '<div class="no-search-results-notice">' + fusionBuilderText.no_results_in.replace( '%s', jQuery( 'a[href="#' + subContainer.id + '"]' ).html() ) + '</div>' );
								} else if ( hasSearchResults ) {
									subContainer.classList.remove( 'no-search-results' );
								}
							} );
						} else {
							$container.find( '.icon_preview' ).css( 'display', 'inline-flex' );
							_.each( $container.find( '.fusion-icon-set' ), function( subContainer ) {
								subContainer.classList.remove( 'no-search-results' );
							} );
						}
					}, 100 );
				} );
			} );
		}
	}
};

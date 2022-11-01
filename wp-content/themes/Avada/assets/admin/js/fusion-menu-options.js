/* global fusionMenuConfig, Fuse, fusionIconSearch, noUiSlider, wNumb */
var FusionDelay = ( function() {
	var timer = 0;

	return function( callback, ms ) {
		clearTimeout( timer );
		timer = setTimeout( callback, ms );
	};
}() );

function fusionIconPicker( value, id, container, search ) {
	var output           = jQuery( '.fusion-icons-rendered' ).html(),
		outputNav        = jQuery( '.fusion-icon-picker-nav-rendered' ).html(),
		$container       = jQuery( container ),
		$containerParent = $container.parent(),
		oldIconName      = '',
		valueSelector    = '',
		selectedSetId   = '';

	if ( ! container.children().length ) {

		if ( 'undefined' !== typeof value && '' !== value ) {

			if ( 'fusion-prefix-' === value.substr( 0, 14 ) ) {

				// Custom icon, we need to remove prefix.
				value = value.replace( 'fusion-prefix-', '' );
			} else {
				value = value.split( ' ' );

				// Legacy FontAwesome 4.x icon, so we need check if it needs to be updated.
				if ( 'undefined' === typeof value[ 1 ] ) {
					value[ 1 ] = 'fas';

					if ( 'undefined' !== typeof window[ 'fusion-fontawesome-free-shims' ] ) { // eslint-disable-line max-depth
						oldIconName = value[ 0 ].substr( 3 );

						jQuery.each( window[ 'fusion-fontawesome-free-shims' ], function( i, shim ) {

							if ( shim[ 0 ] === oldIconName ) {

								// Update icon name.
								if ( null !== shim[ 2 ] ) {
									value[ 0 ] = 'fa-' + shim[ 2 ];
								}

								// Update icon subset.
								if ( null !== shim[ 1 ] ) {
									value[ 1 ] = shim[ 1 ];
								}
								return false;
							}
						} );

						// Update form field with new values.
						$containerParent.find( '.fusion-iconpicker-input' ).attr( 'value', value[ 0 ] + ' ' + value[ 1 ] );
					}
				}
			}
		}

		// Add icon container and icon navigation.
		$container.html( output ).before( '<div class="fusion-icon-picker-nav">' + outputNav + '</div>' );

		// Icon navigation link is clicked.
		$containerParent.find( '.fusion-icon-picker-nav > a' ).on( 'click', function( e ) {
			e.preventDefault();

			jQuery( '.fusion-icon-picker-nav-active' ).removeClass( 'fusion-icon-picker-nav-active' );
			jQuery( this ).addClass( 'fusion-icon-picker-nav-active' );
			$container.find( '.fusion-icon-set' ).css( 'display', 'none' );
			$container.find( jQuery( this ).attr( 'href' ) ).css( 'display', 'grid' );
		} );

		if ( '' !== value ) {

			// FA or custom icon.
			valueSelector = '.' + ( Array.isArray( value ) ? value.join( '.' ) : value );
			$container.find( valueSelector ).parent().addClass( 'selected-element' ).css( 'display', 'flex' );

			// Trigger click on parent nav tab item.
			selectedSetId = $container.find( '.selected-element' ).closest( '.fusion-icon-set' ).prepend( $container.find( '.selected-element' ) ).attr( 'id' );
			$containerParent.find( '.fusion-icon-picker-nav a[href="#' + selectedSetId + '"]' ).trigger( 'click' );
		}

	}

	// Icon Search bar
	jQuery( search ).on( 'change paste keyup', function() {
		var thisEl = jQuery( this );

		FusionDelay( function() {
			var options,
				fuse,
				result;

			if ( thisEl.val() ) {
				value = thisEl.val().toLowerCase();

				if ( 3 > value.length ) {
					return;
				}

				$container.find( '.fusion-icon-set .icon_preview' ).css( 'display', 'none' );
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
				fuse = new Fuse( fusionIconSearch, options );
				result = fuse.search( value );

				_.each( result, function( resultIcon ) {
					$container.find( '.icon_preview.' + resultIcon.name ).css( 'display', 'flex' );
				} );

				// Add attributes to iconset containers.
				_.each( $container.find( '.fusion-icon-set' ), function( subContainer ) {
					var hasSearchResults = false;
					subContainer.classList.add( 'no-search-results' );
					jQuery( '.icon_preview' ).each( function( i, icon ) {
						if ( 'none' !== icon.style.display && subContainer.classList.contains( 'no-search-results' ) ) {
							hasSearchResults = true;
						}
					} );

					if ( ! hasSearchResults && ! subContainer.querySelector( '.no-search-results-notice' ) ) {
						jQuery( subContainer ).append( '<div class="no-search-results-notice">' + fusionMenuConfig.no_results_in.replace( '%s', jQuery( 'a[href="#' + subContainer.id + '"]' ).html() ) + '</div>' );
					} else if ( hasSearchResults ) {
						subContainer.classList.remove( 'no-search-results' );
					}
				} );

			} else {
				$container.find( '.fusion-icon-set .icon_preview' ).css( 'display', 'flex' );
				_.each( $container.find( '.fusion-icon-set' ), function( subContainer ) {
					subContainer.classList.remove( 'no-search-results' );
				} );
			}
		}, 100 );
	} );

	// Iconpicker select/deselect handler.
	jQuery( container ).find( 'span' ).off();
	jQuery( container ).find( 'span' ).on( 'click', function( e ) {

		var fontName,
			subset = 'fas',
			$i     = jQuery( this ).find( 'i' ),
			fielDValue  = '';

		e.preventDefault();

		fontName = 'fa-' + jQuery( this ).find( 'i' ).attr( 'data-name' );

		if ( ! $i.hasClass( 'fas' ) && ! $i.hasClass( 'fab' ) && ! $i.hasClass( 'far' ) && ! $i.hasClass( 'fal' ) ) {

			// Custom icon set, so we need to add prefix.
			fielDValue = 'fusion-prefix-' + jQuery( this ).find( 'i' ).attr( 'class' );
		} else if ( $i.hasClass( 'fab' ) ) {
			subset = 'fab';
		} else if ( $i.hasClass( 'far' ) ) {
			subset = 'far';
		} else if ( $i.hasClass( 'fal' ) ) {
			subset = 'fal';
		}

		// FA icon.
		if ( '' === fielDValue ) {
			fielDValue = fontName + ' ' + subset;
		}

		if ( jQuery( this ).hasClass( 'selected-element' ) ) {
			$containerParent.find( '.selected-element' ).removeClass( 'selected-element' );
			$containerParent.find( '.fusion-iconpicker-input' ).attr( 'value', '' ).trigger( 'change' );

		} else {
			$containerParent.find( '.selected-element' ).removeClass( 'selected-element' );
			jQuery( this ).find( 'i' ).parent().addClass( 'selected-element' );
			$containerParent.find( '.fusion-iconpicker-input' ).attr( 'value', fielDValue ).trigger( 'change' );
		}
	} );

}

jQuery( window ).on( 'load', function() {

	var $wrapEl,
		itemWrapEl;

	if ( jQuery( 'body' ).hasClass( 'widgets-php' ) ) {
		$wrapEl =  jQuery( 'body' ).hasClass( 'widgets_access' ) ? jQuery( '.editwidget' ) : jQuery( '.widget-liquid-right' );
		itemWrapEl = '.widget-inside';
	} else {
		$wrapEl    = jQuery( '#post-body' );
		itemWrapEl = '.menu-item-settings';
	}

	// Backup holder in case of cancel.
	jQuery( 'body' ).append( '<div class="fusion-menu-clone" style="display:none !important"></div>' );

	// On open.
	$wrapEl.on( 'click', '.fusion-menu-option-trigger', function( event ) {
		var $value       = jQuery( this ).parent().find( '.fusion-iconpicker-input' ).val(),
			$id          = jQuery( this ).parent().find( '.fusion-iconpicker-input' ).attr( 'id' ),
			$container   = jQuery( this ).parent().find( '.icon_select_container' ),
			$search      = jQuery( this ).parent().find( '.fusion-icon-search' ),
			$options     = jQuery( this ).parent().find( '.fusion-options-holder' ),
			$holder      = jQuery( this ).parents( '.menu-item-settings' ),
			$modal       = jQuery( this ).parent().find( '.fusion-builder-modal-settings-container' ),
			$colorPicker = jQuery( this ).parent().find( '.fusion-builder-color-picker-hex' ),
			$clone,
			$rangeSlider,
			$i;

		event.preventDefault();
		if ( 'undefined' !== typeof $id ) {
			fusionIconPicker( $value, $id, $container, $search );
		}

		jQuery( $holder ).addClass( 'fusion-active' );
		jQuery( this ).parent().find( '.fusion_builder_modal_overlay' ).show();
		jQuery( $modal ).show();
		jQuery( 'body' ).addClass( 'fusion_builder_no_scroll' );

		// Button set functionality.
		jQuery( $modal ).find( '.fusion-form-radio-button-set a' ).on( 'click', function( scopedEvent ) {
			var $radiosetcontainer;

			scopedEvent.preventDefault();
			$radiosetcontainer = jQuery( this ).parents( '.fusion-form-radio-button-set' );
			$radiosetcontainer.find( '.ui-state-active' ).removeClass( 'ui-state-active' );
			jQuery( this ).addClass( 'ui-state-active' );
			$radiosetcontainer.find( '.button-set-value' ).val( $radiosetcontainer.find( '.ui-state-active' ).data( 'value' ) ).trigger( 'change' );
		} );

		// Save as a backup.
		$clone = jQuery( $options ).clone( true, true );
		jQuery( '.fusion-menu-clone' ).append( $clone );

		// Select field functionality.
		jQuery( $modal ).find( '.fusion-builder-option select' ).selectWoo( {
			minimumResultsForSearch: 10,
			dropdownCssClass: 'avada-select2',
			width: '100%'
		} );

		if ( $colorPicker.length ) {
			$colorPicker.each( function() {
				if ( ! jQuery( this ).closest( '.wp-picker-container' ).length ) {
					jQuery( this ).awbColorPicker();
				}
			} );
		}

		if ( '.widget-inside' === itemWrapEl  && 'auto' !== jQuery( this ).closest( '.widget' ).css( 'z-index' ) ) {
			jQuery( this ).closest( '.widget' ).css( 'z-index', '99999' );
		}

		$rangeSlider = jQuery( $modal ).find( '.fusion-builder-option.avada-range .fusion-slider-container' );

		if ( $rangeSlider.length ) {

			// Counter variable for sliders
			$i = 0;

			// Method for retreiving decimal places from step
			Number.prototype.countDecimals = function() { // eslint-disable-line no-extend-native
				if ( Math.floor( this.valueOf() ) === this.valueOf() ) {
					return 0;
				}
				return this.toString().split( '.' )[ 1 ].length || 0;
			};

			// Each slider on page, determine settings and create slider
			$rangeSlider.each( function() {

				var $targetId     = jQuery( this ).data( 'id' ),
					$rangeInput   = jQuery( this ).prev( '.fusion-slider-input' ),
					$min          = jQuery( this ).data( 'min' ),
					$max          = jQuery( this ).data( 'max' ),
					$step         = jQuery( this ).data( 'step' ),
					$direction    = jQuery( this ).data( 'direction' ),
					scopedVal     = $rangeInput.val(),
					$decimals     = $step.countDecimals(),
					$rangeDefault = ( jQuery( this ).parents( '.fusion-builder-option' ).find( '.fusion-range-default' ).length ) ? jQuery( this ).parents( '.fusion-builder-option' ).find( '.fusion-range-default' ) : false,
					$hiddenValue  = ( $rangeDefault ) ? jQuery( this ).parent().find( '.fusion-hidden-value' ) : false,
					$defaultValue = ( $rangeDefault ) ? jQuery( this ).parents( '.fusion-builder-option' ).find( '.fusion-range-default' ).data( 'default' ) : false;

				createSlider( $i, $targetId, $rangeSlider, $rangeInput, $min, $max, $step, scopedVal, $decimals, $rangeDefault, $hiddenValue, $defaultValue, $direction );

				$i++;
			} );

		}

		// Trigger special links.
		jQuery( $modal ).find( '.fusion-megamenu-special-link' ).trigger( 'change' );
		jQuery( $modal ).find( '.edit-menu-item-megamenu-show-woo-cart-counter .button-set-value' ).trigger( 'change' );

	} );

	// On cancel.
	$wrapEl.on( 'click', '.fusion-builder-modal-close', function( event ) {
		var $backup = jQuery( '.fusion-menu-clone' ).find( '.fusion-builder-modal-settings-container' ).hide(),
			colorPickers = jQuery( this ).closest( '.fusion-builder-modal-settings-container' ).find( '.fusion-builder-option .wp-color-picker' );

		event.preventDefault();

		if ( '.widget-inside' === itemWrapEl  && 'auto' !== jQuery( this ).closest( '.widget' ).css( 'z-index' ) ) {
			jQuery( this ).closest( '.widget' ).css( 'z-index', '100' );
		}

		if ( colorPickers.length ) {
			colorPickers.each( function() {
				if ( jQuery( this ).closest( '.wp-picker-container' ).hasClass( 'wp-picker-active' ) ) {
					jQuery( this ).awbColorPicker( 'close' );
				}
			} );
		}

		jQuery( '.fusion-builder-option select.select2-hidden-accessible' ).selectWoo( 'destroy' );
		jQuery( '.fusion-active' ).removeClass( 'fusion-active' );
		jQuery( this ).parents( '.fusion-builder-modal-settings-container' ).replaceWith( $backup );
		jQuery( this ).parents( '.fusion-builder-modal-settings-container' ).hide();
		jQuery( '.fusion_builder_modal_overlay' ).hide();
		jQuery( 'body' ).removeClass( 'fusion_builder_no_scroll' );
		jQuery( '.fusion-menu-clone' ).html( '' );

	} );

	// On outside click.
	$wrapEl.on( 'click', itemWrapEl + ' .fusion_builder_modal_overlay', function( event ) {
		var $backup = jQuery( '.fusion-menu-clone' ).find( '.fusion-builder-modal-settings-container' ).hide(),
			settingsContainer = jQuery( this ).next( '.fusion-builder-modal-settings-container' ),
			colorPickers = settingsContainer.find( '.fusion-builder-option .wp-color-picker' ),
			rangeSlider = settingsContainer.find( '.fusion-builder-option.avada-range .fusion-slider-container' );

		event.preventDefault();

		if ( '.widget-inside' === itemWrapEl  && 'auto' !== jQuery( this ).closest( '.widget' ).css( 'z-index' ) ) {
			jQuery( this ).closest( '.widget' ).css( 'z-index', '100' );
		}

		if ( 'undefined' !== typeof rangeSlider && 0 < rangeSlider.length ) {
			rangeSlider.each( function() {
				this.noUiSlider.destroy();
			} );
		}

		if ( colorPickers.length ) {
			colorPickers.each( function() {
				if ( jQuery( this ).closest( '.wp-picker-container' ).hasClass( 'wp-picker-active' ) ) {
					jQuery( this ).awbColorPicker( 'close' );
				}
			} );
		}

		jQuery( '.fusion-builder-option select.select2-hidden-accessible' ).selectWoo( 'destroy' );
		jQuery( '.fusion-active' ).removeClass( 'fusion-active' );
		jQuery( this ).next().replaceWith( $backup );
		jQuery( this ).next().hide();
		jQuery( '.fusion_builder_modal_overlay' ).hide();
		jQuery( 'body' ).removeClass( 'fusion_builder_no_scroll' );
		jQuery( '.fusion-menu-clone' ).html( '' );
	} );

	// On save,
	$wrapEl.on( 'click', '.fusion-builder-modal-save', function( event ) {
		var settingsContainer = jQuery( this ).closest( '.fusion-builder-modal-settings-container' ),
			colorPickers = jQuery( this ).closest( '.fusion-builder-modal-settings-container' ).find( '.fusion-builder-option .wp-color-picker' ),
			rangeSlider = settingsContainer.find( '.fusion-builder-option.avada-range .fusion-slider-container' );

		event.preventDefault();

		if ( '.widget-inside' === itemWrapEl  && 'auto' !== jQuery( this ).closest( '.widget' ).css( 'z-index' ) ) {
			jQuery( this ).closest( '.widget' ).css( 'z-index', '100' );
		}

		if ( 'undefined' !== typeof rangeSlider && 0 < rangeSlider.length ) {
			rangeSlider.each( function() {
				this.noUiSlider.destroy();
			} );
		}

		if ( colorPickers.length ) {
			colorPickers.each( function() {
				if ( jQuery( this ).closest( '.wp-picker-container' ).hasClass( 'wp-picker-active' ) ) {
					jQuery( this ).awbColorPicker( 'close' );
				}
			} );
		}

		jQuery( '.fusion-builder-option select.select2-hidden-accessible' ).selectWoo( 'destroy' );
		jQuery( '.fusion-active' ).removeClass( 'fusion-active' );
		jQuery( this ).parents( '.fusion-builder-modal-settings-container' ).hide();
		jQuery( '.fusion_builder_modal_overlay' ).hide();
		jQuery( 'body' ).removeClass( 'fusion_builder_no_scroll' );
		jQuery( '.fusion-menu-clone' ).html( '' );

		fusionMenuOptionsResetWooFragments();

	} );

	function createSlider( $slide, $targetId, $rangeSlider, $rangeInput, $min, $max, $step, $value, $decimals, $rangeDefault, $hiddenValue, $defaultValue, $direction ) {

		// Create slider with values passed on in data attributes.
		var $slider = noUiSlider.create( $rangeSlider[ $slide ], {
				start: [ $value ],
				step: $step,
				direction: $direction,
				range: {
					min: $min,
					max: $max
				},
				format: wNumb( {
					decimals: $decimals
				} )
			} ),
			$notFirst = false;

		// Check if default is currently set.
		if ( $rangeDefault && '' === $hiddenValue.val() ) {
			$rangeDefault.parent().addClass( 'checked' );
		}

		// If this range has a default option then if checked set slider value to data-value.
		if ( $rangeDefault ) {
			$rangeDefault.on( 'click', function( e ) {
				e.preventDefault();
				$rangeSlider[ $slide ].noUiSlider.set( $defaultValue );
				$hiddenValue.val( '' );
				jQuery( this ).parent().addClass( 'checked' );

				// Specific for Widget modals.
				if ( '.widget-inside' === itemWrapEl ) {
					jQuery( this ).closest( '.widget' ).find( '.widget-control-save' ).prop( 'disabled', false );
				}
			} );
		}

		// On slider move, update input
		$slider.on( 'update', function( values, handle ) {
			if ( $rangeDefault && $notFirst ) {
				$rangeDefault.parent().removeClass( 'checked' );
				$hiddenValue.val( values[ handle ] );
			}
			$notFirst = true;
			jQuery( this.target ).closest( '.fusion-slider-container' ).prev().val( values[ handle ] );
			jQuery( '#' + $targetId ).trigger( 'change' );
			if ( jQuery( '#' + $targetId ).length ) {
				jQuery( '#' + $targetId ).trigger( 'fusion-changed' );
			} else {
				jQuery( '#slider' + $targetId ).trigger( 'fusion-changed' );
			}

			// Specific for Widget modals.
			if ( '.widget-inside' === itemWrapEl ) {
				jQuery( this.target ).closest( '.widget' ).find( '.widget-control-save' ).prop( 'disabled', false );

				if ( '0' === jQuery( this.target ).siblings( '.fusion-slider-input' ).val() ) {
					jQuery( this.target ).closest( '.fusion-options-holder' ).addClass( 'fusion-widget-no-border' );
				} else {
					jQuery( this.target ).closest( '.fusion-options-holder' ).removeClass( 'fusion-widget-no-border' );
				}
			}
		} );

		// On manual input change, update slider position
		$rangeInput.on( 'keyup', function() {
			var rangeSlider = jQuery( this ).next( '.fusion-slider-container' );

			if ( this.value !== rangeSlider[ $slide ].noUiSlider.get() ) {

				// This triggers 'update' event.
				rangeSlider[ $slide ].noUiSlider.set( this.value );
			}
		} );
	}
} );

jQuery( document ).ready( function() {

	( function initIconPicker() {
		var icons       = fusionMenuConfig.fontawesomeicons,
			output      = '<div class="fusion-icons-rendered" style="height:0px; overflow:hidden;">',
			outputSets  = {
				fas: '',
				fab: '',
				far: '',
				fal: ''
			},
			iconSubsets = {
				fas: 'Solid',
				far: 'Regular',
				fal: 'Light',
				fab: 'Brands'
			},
			outputNav = '<div class="fusion-icon-picker-nav-rendered" style="height:0px; overflow:hidden;">',
			isSearchDefined = 'undefined' !== typeof fusionIconSearch && Array.isArray( fusionIconSearch );

		// Iterate through all FA icons and divide them into sets (one icon can belong to multiple sets).
		_.each( icons, function( icon, key ) {

			_.each( icon[ 1 ], function( iconSubset ) {
				if ( -1 !== fusionMenuConfig.fontawesomesubsets.indexOf( iconSubset ) ) {
					outputSets[ iconSubset ] += '<span class="icon_preview ' + key + '" title="' + key + ' - ' + iconSubsets[ iconSubset ] + '"><i class="' + icon[ 0 ] + ' ' + iconSubset + '" data-name="' + icon[ 0 ].substr( 3 ) + '" aria-hidden="true"></i></span>';
				}
			} );
		} );

		// Add FA sets to output.
		_.each( iconSubsets, function( label, key ) {
			if ( -1 !== fusionMenuConfig.fontawesomesubsets.indexOf( key ) ) {
				outputNav += '<a href="#fusion-' + key + '" class="fusion-icon-picker-nav-item">' + label + '</a>';
				output    += '<div id="fusion-' + key + '" class="fusion-icon-set">' + outputSets[ key ] + '</div>';
			}
		} );

		// WIP: Add custom icons.
		icons = fusionMenuConfig.customIcons;
		_.each( icons, function( iconSet, IconSetKey ) {
			outputNav += '<a href="#' + IconSetKey + '" class="fusion-icon-picker-nav-item">' + iconSet.name + '</a>';
			output    += '<div id="' + IconSetKey + '" class="fusion-icon-set fusion-custom-icon-set">';
			_.each( iconSet.icons, function( icon ) {

				if ( isSearchDefined ) {
					fusionIconSearch.push( { name: icon } );
				}

				output += '<span class="icon_preview ' + icon + '" title="' + iconSet.css_prefix + icon + '"><i class="' + iconSet.css_prefix + icon + '" data-name="' + icon + '" aria-hidden="true"></i></span>';
			} );
			output += '</div>';
		} );

		outputNav += '</div>';
		output    += '</div>';

		jQuery( 'body' ).append( output + outputNav );

	}() );
} );

// Delete cart fragments.
function fusionMenuOptionsResetWooFragments() {
	var keys = Object.keys( window.sessionStorage );
	jQuery( keys ).each( function( i, key ) {
		if ( -1 < key.indexOf( 'wc_fragments_' ) ) {
			window.sessionStorage.removeItem( key );
		}
	} );
}

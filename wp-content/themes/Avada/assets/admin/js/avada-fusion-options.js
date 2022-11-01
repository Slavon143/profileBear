/* global noUiSlider, wNumb, avadaPOMessages, ajaxurl */

var fusionPageOptions;

jQuery( document ).ready( function() {

	var $rangeSlider,
		$i,
		fusionSelect2;

	fusionSelect2 = jQuery( '.pyre_field select:not(.hidden-sidebar):not([data-ajax]):not(.skip-select2)' ).filter( function() {
		return ! jQuery( this ).closest( '.fusion-repeater-wrapper' ).length;
	} ).select2( {
		minimumResultsForSearch: 10
	} );

	jQuery.each( fusionSelect2, function() {
		if ( 'undefined' !== typeof jQuery( this ).data( 'select2' ).dropdown ) {
			if ( 'undefined' !== typeof jQuery( this ).data( 'select2' ).dropdown.$dropdown ) {
				jQuery( this ).data( 'select2' ).dropdown.$dropdown.addClass( 'avada-select2' );
			} else if ( 'undefined' !== typeof jQuery( this ).data( 'select2' ).dropdown.selector ) {
				jQuery( jQuery( this ).data( 'select2' ).dropdown.selector ).addClass( 'avada-select2' );
			}
		}
	} );

	if ( 'undefined' !== typeof ajaxurl ) {

		jQuery( '.pyre_field select[data-ajax]' ).filter( function() {
			return ! jQuery( this ).closest( '.fusion-repeater-wrapper' ).length;
		} ).each( function() {
			var $select, ajax, ajaxParams, labels, initAjaxSelect, maxInput;

			$select = jQuery( this );
			ajax = $select.data( 'ajax' );
			maxInput = $select.data( 'max-input' );
			ajaxParams = $select.siblings( '.params' ).val();
			labels = $select.siblings( '.initial-values' ).val();

			ajaxParams = JSON.parse( ajaxParams );
			labels = JSON.parse( _.unescape( labels ) );
			initAjaxSelect 	= function() {
				var ajaxSelect = $select.select2( {
					width: '100%',
					delay: 250,
					minimumInputLength: 3,
					maximumSelectionLength: maxInput,
					ajax: {
						url: ajaxurl,
						dataType: 'json',
						data: function ( params ) {
							return {
								action: ajax,
								search: params.term,
								params: ajaxParams,
								fusion_po_nonce: jQuery( '#fusion-page-options-nonce' ).val()
							};
						}
					}
				} );

				if ( 'undefined' !== typeof ajaxSelect.data( 'select2' ).dropdown ) {
					if ( 'undefined' !== typeof ajaxSelect.data( 'select2' ).dropdown.$dropdown ) {
						ajaxSelect.data( 'select2' ).dropdown.$dropdown.addClass( 'avada-select2' );
					} else if ( 'undefined' !== typeof ajaxSelect.data( 'select2' ).dropdown.selector ) {
						jQuery( ajaxSelect.data( 'select2' ).dropdown.selector ).addClass( 'avada-select2' );
					}
				}

				ajaxSelect.data( 'select2' ).on( 'results:message', function() {
					this.dropdown._resizeDropdown();
					this.dropdown._positionDropdown();
				} );

				fusionSelect2.add( ajaxSelect );
			};

			// If there are initial values get labels else init ajax-select.
			if ( labels ) {
				jQuery.post( ajaxurl, {
					action: ajax,
					labels: labels,
					params: ajaxParams,
					fusion_po_nonce: jQuery( '#fusion-page-options-nonce' ).val()
				}, function( data ) {
					data = JSON.parse( data );
					labels  = data.labels || [];

					_.each( labels, function( label ) {
						$select.append(
							'<option value="' + label.id + '" selected="selected">' + label.text + '</option>'
						);
					} );

					initAjaxSelect();

				} );
			} else {
				initAjaxSelect();
			}
		} );

	}

	jQuery( '.pyre_field.avada-buttonset.radio a' ).on( 'click', function( e ) {
		var $radiosetcontainer;

		e.preventDefault();
		$radiosetcontainer = jQuery( this ).parents( '.fusion-form-radio-button-set' );
		$radiosetcontainer.find( '.ui-state-active' ).removeClass( 'ui-state-active' );
		jQuery( this ).addClass( 'ui-state-active' );
		$radiosetcontainer.find( '.button-set-value' ).val( $radiosetcontainer.find( '.ui-state-active' ).data( 'value' ) ).trigger( 'change' );
	} );

	jQuery( '.pyre_field.avada-buttonset.checkbox a' ).on( 'click', function( e ) {
		var $checkboxsetcontainer;

		e.preventDefault();
		$checkboxsetcontainer = jQuery( this ).closest( '.fusion-form-checkbox-button-set' );
		jQuery( this ).toggleClass( 'ui-state-active' );
		$checkboxsetcontainer.find( '.button-set-value' ).val( $checkboxsetcontainer.find( '.ui-state-active' ).map( function( _, el ) {
			return jQuery( el ).data( 'value' );
		} ).get() ).trigger( 'change' );
	} );

	jQuery( '.pyre_field.avada-color input' ).each( function() {
		var self = jQuery( this ),
			$defaultReset = self.parents( '.pyre_metabox_field' ).find( '.pyre-default-reset' );

		// Picker with default.
		if ( jQuery( this ).data( 'default' ) &&  jQuery( this ).data( 'default' ).length ) {
			jQuery( this ).awbColorPicker( {
				change: function( event ) {
					colorChange( jQuery( event.target ), $defaultReset );
				},
				clear: function( event ) {
					colorClear( jQuery( event.target ) );
				}
			} );

			if ( ! self.val() ) {
				$defaultReset.addClass( 'checked' );
			} else {
				$defaultReset.removeClass( 'checked' );
			}

			// Default reset icon, set value to empty.
			$defaultReset.on( 'click', 'a', function( event ) {
				var dataDefault,
					$input = jQuery( this ).closest( '.pyre_metabox_field' ).find( '.color-picker' );

				event.preventDefault();
				dataDefault = $input.attr( 'data-default' ) || $input.attr( 'data-default-color' );

				// Make the color picker to start from the default color on open.
				if ( dataDefault ) {
					$input.val( dataDefault ).trigger( 'change' );
				}
				$input.val( '' ).trigger( 'change' );
			} );

		// Picker without default.
		} else {
			jQuery( this ).awbColorPicker();
		}
	} );

	jQuery( '.fusion-sortable-options' ).each( function() {
		if ( '' === jQuery( this ).siblings( '.sort-order' ).val() ) {
			jQuery( this ).parents( '.pyre_metabox_field' ).find( '.fusion-builder-default-reset' ).addClass( 'checked' );
		}

		jQuery( this ).sortable();
		jQuery( this ).on( 'sortupdate', function( event ) {
			var sortContainer = jQuery( event.target ),
				sortOrder = '';

			sortContainer.children( '.fusion-sortable-option' ).each( function() {
				sortOrder += jQuery( this ).data( 'value' ) + ',';
			} );

			sortOrder = sortOrder.slice( 0, -1 );

			sortContainer.siblings( '.sort-order' ).val( sortOrder );

			sortContainer.parents( '.pyre_metabox_field' ).find( '.fusion-builder-default-reset' ).removeClass( 'checked' );
		} );

		jQuery( this ).parents( '.pyre_metabox_field' ).find( '.fusion-reset-to-default' ).on( 'click', function( e ) {
			var order    = jQuery( this ).data( 'default' ).split( ',' ),
				sortable = jQuery( this ).parents( '.pyre_metabox_field' ).find( '.fusion-sortable-options' ),
				first    = sortable.find( '[data-value="' + order[ 0 ] + '"]' ),
				second   = sortable.find( '[data-value="' + order[ 1 ] + '"]' ),
				third    = sortable.find( '[data-value="' + order[ 2 ] + '"]' );

			sortable.prepend( first );
			sortable.append( second );
			sortable.append( third );
			sortable.sortable( 'refresh' );
			sortable.parent().find( 'input' ).val( '' );

			jQuery( this ).parent().addClass( 'checked' );

			e.preventDefault();
		} );
	} );

	function avadaCheckDependency( $currentValue, $desiredValue, $comparison ) {
		if ( '==' === $comparison || '=' === $comparison ) {
			if ( $currentValue == $desiredValue ) { // jshint ignore:line
				return true;
			}
		} else if ( '>=' === $comparison ) {
			if ( $currentValue >= $desiredValue ) {
				return true;
			}
		} else if ( '<=' === $comparison ) {
			if ( $currentValue <= $desiredValue ) {
				return true;
			}
		} else if ( '>' === $comparison ) {
			if ( $currentValue > $desiredValue ) {
				return true;
			}
		} else if ( '<' === $comparison ) {
			if ( $currentValue < $desiredValue ) {
				return true;
			}
		} else if ( '!=' === $comparison ) {
			if ( $currentValue != $desiredValue ) { // jshint ignore:line
				return true;
			}
		} else if ( 'contains' === $comparison )  {
			if ( $currentValue && $currentValue.includes( $desiredValue ) ) {
				return true;
			}
		}

		return false;
	}

	function avadaLoopDependencies( $container ) {
		var $passed = false;

		$container.find( 'span' ).each( function() {

			var $value      = jQuery( this ).data( 'value' ),
				$comparison = jQuery( this ).data( 'comparison' ),
				$field      = jQuery( this ).data( 'field' ),
				$target     = $container.closest( '.fusion-repeater-row' ).length ? $container.closest( '.fusion-repeater-row' ).find( '#pyre_' + $field ) : jQuery( '#pyre_' + $field );

			$passed = avadaCheckDependency( $target.val(), $value, $comparison );
			return $passed;
		} );
		if ( $passed ) {
			$container.closest( '.pyre_metabox_field' ).fadeIn( 300 );
		} else {
			$container.closest( '.pyre_metabox_field' ).hide();
		}
	}

	jQuery( '.avada-dependency' ).filter( function() {
		return ! jQuery( this ).closest( '.fusion-repeater-row' ).length;
	} ).each( function() {
		avadaLoopDependencies( jQuery( this ) );
	} );
	jQuery( '[id*="pyre"]' ).on( 'change.dependency', function() {
		var $id    = jQuery( this ).attr( 'id' ),
			$field = $id.replace( 'pyre_', '' );
		jQuery( 'span[data-field="' + $field + '"]' ).each( function() {
			avadaLoopDependencies( jQuery( this ).parents( '.avada-dependency' ) );
		} );
	} );

	function createSlider( $slide, $targetId, $rangeInput, $min, $max, $step, $value, $decimals, $rangeDefault, $hiddenValue, $defaultValue, $direction ) {

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
				$hiddenValue.val( '' ).trigger( 'fusion-changed' );
				jQuery( this ).parent().addClass( 'checked' );
			} );
		}

		// On slider move, update input
		$slider.on( 'update', function( values, handle ) {
			if ( $rangeDefault && $notFirst ) {
				$rangeDefault.parent().removeClass( 'checked' );
				$hiddenValue.val( values[ handle ] ).trigger( 'fusion-changed' );
			}
			$notFirst = true;
			jQuery( this.target ).closest( '.fusion-slider-container' ).prev().val( values[ handle ] );
			jQuery( '#' + $targetId ).trigger( 'change' );
			if ( jQuery( '#' + $targetId ).length ) {
				jQuery( '#' + $targetId ).trigger( 'fusion-changed' );
			} else {
				jQuery( '#slider' + $targetId ).trigger( 'fusion-changed' );
			}
		} );

		// On manual input change, update slider position
		$rangeInput.on( 'keyup', function( values, handle ) {
			if ( $rangeDefault ) {
				$rangeDefault.parent().removeClass( 'checked' );
				$hiddenValue.val( values[ handle ] );
			}

			if ( this.value !== $rangeSlider[ $slide ].noUiSlider.get() ) {
				$rangeSlider[ $slide ].noUiSlider.set( this.value );
			}
		} );
	}

	$rangeSlider = jQuery( '.pyre_field.avada-range .fusion-slider-container' );

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
				$value        = $rangeInput.val(),
				$decimals     = $step.countDecimals(),
				$rangeDefault = ( jQuery( this ).parents( '.pyre_metabox_field' ).find( '.fusion-range-default' ).length ) ? jQuery( this ).parents( '.pyre_metabox_field' ).find( '.fusion-range-default' ) : false,
				$hiddenValue  = ( $rangeDefault ) ? jQuery( this ).parent().find( '.fusion-hidden-value' ) : false,
				$defaultValue = ( $rangeDefault ) ? jQuery( this ).parents( '.pyre_metabox_field' ).find( '.fusion-range-default' ).data( 'default' ) : false;

			createSlider( $i, $targetId, $rangeInput, $min, $max, $step, $value, $decimals, $rangeDefault, $hiddenValue, $defaultValue, $direction );

			$i++;
		} );

	}

	function colorChange( input, defaultReset ) {
		if ( ! input.val() ) {
			defaultReset.addClass( 'checked' );
		} else {
			defaultReset.removeClass( 'checked' );
		}

		input.trigger( 'fusion-changed' );
	}

	function colorClear( input ) {
		var defaultColor = input.data( 'default' );

		if ( null !== defaultColor ) {
			input.val( defaultColor );
			input.change();
			input.val( '' );
			input.parent().parent().find( '.wp-color-result' ).css( 'background-color', defaultColor );
		}

		input.trigger( 'fusion-changed' );
	}

	/* PO export / import tab */

	fusionPageOptions = {

		init: function() {

			var self =  this;

			jQuery( '#fusion-page-options-save' ).on( 'click', self.saveOptions );
			jQuery( '#fusion-page-options-import-saved' ).on( 'click', self.importSavedOptions );
			jQuery( '#fusion-page-options-delete-saved' ).on( 'click', self.deleteSaved );

			jQuery( '#fusion-saved-page-options-select' ).on( 'change', self.showHideButtons );

			jQuery( '#fusion-page-options-import' ).on( 'click', self.importOptions );
			jQuery( '#fusion-page-options-file-input' ).on( 'change', self.prepareUpload );

			jQuery( '#fusion-page-options-export' ).on( 'click', self.exportOptions );

			this.initRepeaters();
			this.initToggles();

		},

		initToggles: function() {
			var self = this;
			jQuery( '.fusion-toggle-wrapper' ).each( function() {
				self.initToggle( jQuery( this ) );
			} );
		},

		initToggle: function( $element ) {
			$element.off( 'click.toggle' ).on( 'click.toggle', '.fusion-row-title.fusion-toggle-title', function( event ) {
				// Toggle visibility of fields.
				jQuery( event.target ).closest( '.fusion-toggle-row' ).find( '.fusion-row-fields' ).slideToggle( 300 );
			} );
		},

		initRepeaters: function() {
			var self = this;
			jQuery( '.fusion-repeater-wrapper:not(.fusion-toggle-wrapper)' ).each( function() {
				self.initRepeater( jQuery( this ) );
			} );
		},

		initRepeater: function( $element ) {
			var self       = this,
				$addButton = $element.find( '.fusion-add-row' ),
				$rows      = $element.find( '.fusion-repeater-rows' ),
				$value     = $element.find( '.repeater-value' ),
				values     = $value.val(),
				titleBind  = $value.data( 'bind' );

			// empty defaults field
			$element.find( '.fusion-repeater-default-fields' ).find( 'input, select, textarea' ).each( function() {
				jQuery( this ).removeAttr( 'value' );
				jQuery( this ).attr( 'name', jQuery( this ).attr( 'name' ).replace( '_fusion[', '' ).replace( ']', '' ) );
				if ( jQuery( this ).is( 'textarea' ) ) {
					jQuery( this ).text( '' );
				}

			} );

			const $defaults  = $element.find( '.fusion-repeater-default-fields' ).html();

			$rows.empty();
			if ( 'string' === typeof values && '' !== values ) {
				try {
					values = JSON.parse( values );
					self.insertOptionsWithValues( $element, values, titleBind );
				} catch ( e ) {
					console.warn( 'Something went wrong! Error triggered - ' + e );
				}
			}

			// Add a repeater row on click.
			$addButton.on( 'click', function( event ) {

				event.preventDefault();

				// Add the markup.
				$rows.append( '<div class="fusion-repeater-row fusion-needs-init">' + $defaults + '</div>' );

				// Auto open new row.
				$rows.find( '.fusion-needs-init .fusion-row-fields' ).css( { display: 'block' } );

				// Init the options and dependencies.
				self.initOptions( $element );

				// Updates value.
				self.setRepeaterValue( $element );
			} );

			// Row remove button click.
			$element.off( 'click.remove' ).on( 'click.remove', '.repeater-row-remove', function( event ) {

				event.preventDefault();

				// Remove visible row.
				jQuery( event.target ).closest( '.fusion-repeater-row' ).remove();

				// Update the hidden input value.
				self.setRepeaterValue( $element );
			} );

			// Row remove button click.
			$element.off( 'click.toggle' ).on( 'click.toggle', '.fusion-row-title:not(.fusion-toggle-title)', function( event ) {
				if ( jQuery( event.target ).hasClass( 'repeater-row-remove' ) ) {
					return;
				}

				// Toggle visibility of fields.
				jQuery( event.target ).closest( '.fusion-repeater-row' ).find( '.fusion-row-fields' ).slideToggle( 300 );
			} );

			// Bind title to option if set.
			if ( '' !== titleBind ) {
				$element.on( 'change', '[id="pyre_' + titleBind + '"]', function( event ) {
					self.setInputLabel( jQuery( event.target ) );
				} );
			}

			// Any option change, need to update repeater value.
			$element.on( 'change', '[id*="pyre"]', function() {
				self.setRepeaterValue( $element );
			} );
		},

		setInputLabel: function( $input ) {
			var value  = $input.val(),
				$title = $input.closest( '.fusion-repeater-row' ).find( 'h4' );

			if ( $input.is( 'select' ) ) {
				value = $input.find( 'option:selected' ).text();
			}
			$title.text( value );
		},

		insertOptionsWithValues: function( $wrapper, values, titleBind ) {
			var self      = this,
				$defaults = $wrapper.find( '.fusion-repeater-default-fields' ),
				$rows     = $wrapper.find( '.fusion-repeater-rows' );

			if ( 'object' === typeof values ) {
				jQuery.each( values, function( key, valueObject ) {
					var $newrow = jQuery( '<div class="fusion-repeater-row fusion-needs-init">' + $defaults.clone().html() + '</div>' );

					jQuery.each( valueObject, function( valueKey, value ) {
						var $input = $newrow.find( '#pyre_' + valueKey );
						if ( '' !== value ) {
							// Save values for ajax-select call
							if ( $input.data( 'ajax' ) ) {
								$input.siblings( '.initial-values' ).val( _.escape( JSON.stringify( value ) ) );
							}
							if ( $input.hasClass( 'button-set-value' ) ) {
								$input.siblings( '.ui-state-active' ).removeClass( 'ui-state-active' );
								$input.siblings( '[data-value="' + value + '"]' ).addClass( 'ui-state-active' );
							}
							$input.val( value );
						}
					} );
					self.setInputLabel( $newrow.find( '#pyre_' + titleBind ) );
					$rows.append( $newrow );
				} );

				this.initOptions( $wrapper );
			}
		},

		setRepeaterValue: function( $wrapper ) {
			var $value = $wrapper.find( '.repeater-value' ),
				value  = [];

			$wrapper.find( '.fusion-repeater-row' ).each( function() {
				var values = {};

				jQuery( this ).find( '[id*="pyre"]' ).each( function() {
					var id   = jQuery( this ).attr( 'id' ).replace( 'pyre_', '' ),
						val  = jQuery( this ).val();

					if ( null !== val && '' !== val ) {
						values[ id ] = jQuery( this ).val();
					}
				} );
				value.push( values );
			} );

			$value.val( JSON.stringify( value ) );
		},

		initOptions: function( $wrapper ) {
			var self = this;
			$wrapper.find( '.fusion-repeater-rows .fusion-needs-init' ).each( function() {
				jQuery( this ).removeClass( 'fusion-needs-init' );
				self.initDependencies( jQuery( this ) );
				self.initSelect( jQuery( this ) );
				self.initRadios( jQuery( this ) );
				self.initCheckBoxes( jQuery( this ) );
			} );
		},

		initDependencies: function( $wrapper ) {
			$wrapper.find( '.avada-dependency' ).each( function() {
				avadaLoopDependencies( jQuery( this ) );
			} );
			$wrapper.find( '[id*="pyre"]' ).off( 'change.dependency' ).on( 'change.dependency', function() {
				var $id    = jQuery( this ).attr( 'id' ),
					$field = $id.replace( 'pyre_', '' );

				$wrapper.find( 'span[data-field="' + $field + '"]' ).each( function() {
					avadaLoopDependencies( jQuery( this ).closest( '.avada-dependency' ) );
				} );
			} );
		},

		initRadios: function( $wrapper ) {
			$wrapper.find( '.pyre_field.avada-buttonset.radio a' ).on( 'click', function( e ) {
				var $radiosetcontainer;

				e.preventDefault();
				$radiosetcontainer = jQuery( this ).parents( '.fusion-form-radio-button-set' );
				$radiosetcontainer.find( '.ui-state-active' ).removeClass( 'ui-state-active' );
				jQuery( this ).addClass( 'ui-state-active' );
				$radiosetcontainer.find( '.button-set-value' ).val( $radiosetcontainer.find( '.ui-state-active' ).data( 'value' ) ).trigger( 'change' );
			} );
		},

		initCheckBoxes: function( $wrapper ) {
			$wrapper.find( '.pyre_field.avada-buttonset.checkbox a' ).on( 'click', function( e ) {
				var $checkboxsetcontainer;

				e.preventDefault();
				$checkboxsetcontainer = jQuery( this ).closest( '.fusion-form-checkbox-button-set' );
				jQuery( this ).toggleClass( 'ui-state-active' );
				$checkboxsetcontainer.find( '.button-set-value' ).val( $checkboxsetcontainer.find( '.ui-state-active' ).map( function( _, el ) {
					return jQuery( el ).data( 'value' );
				} ).get() ).trigger( 'change' );
			} );
		},

		initSelect: function( $wrapper ) {
			fusionSelect2 = $wrapper.find( '.pyre_field select:not(.hidden-sidebar):not([data-ajax])' ).select2( {
				minimumResultsForSearch: 10,
				width: '100%'
			} );


			if ( 'undefined' !== typeof ajaxurl ) {

				$wrapper.find( '.pyre_field select[data-ajax]' ).each( function() {
					var $select, ajax, ajaxParams, labels, initAjaxSelect;

					$select 		= jQuery( this );
					ajax    		= $select.data( 'ajax' );
					ajaxParams 		= $select.siblings( '.params' ).val();
					labels 			= $select.siblings( '.initial-values' ).val();

					ajaxParams 	= JSON.parse( ajaxParams );
					labels 		= JSON.parse( _.unescape( labels ) );
					initAjaxSelect 	= function() {
						var ajaxSelect = $select.select2( {
							width: '100%',
							delay: 250,
							minimumInputLength: 3,
							ajax: {
								url: ajaxurl,
								dataType: 'json',
								data: function ( params ) {
									return {
										action: ajax,
										search: params.term,
										params: ajaxParams,
										fusion_po_nonce: jQuery( '#fusion-page-options-nonce' ).val()
									};
								}
							}
						} );

						if ( 'undefined' !== typeof ajaxSelect.data( 'select2' ).dropdown ) {
							if ( 'undefined' !== typeof ajaxSelect.data( 'select2' ).dropdown.$dropdown ) {
								ajaxSelect.data( 'select2' ).dropdown.$dropdown.addClass( 'avada-select2' );
							} else if ( 'undefined' !== typeof ajaxSelect.data( 'select2' ).dropdown.selector ) {
								jQuery( ajaxSelect.data( 'select2' ).dropdown.selector ).addClass( 'avada-select2' );
							}
						}

						ajaxSelect.data( 'select2' ).on( 'results:message', function() {
							this.dropdown._resizeDropdown();
							this.dropdown._positionDropdown();
						} );

						fusionSelect2.add( ajaxSelect );
					};

					// If there are initial values get labels else init ajax-select.
					if ( labels ) {
						jQuery.post( ajaxurl, {
							action: ajax,
							labels: labels,
							params: ajaxParams,
							fusion_po_nonce: jQuery( '#fusion-page-options-nonce' ).val()
						}, function( data ) {
							data = JSON.parse( data );
							labels  = data.labels || [];

							_.each( labels, function( label ) {
								$select.append(
									'<option value="' + label.id + '" selected="selected">' + label.text + '</option>'
								);
							} );

							initAjaxSelect();

						} );
					} else {
						initAjaxSelect();
					}
				} );

			}

			jQuery.each( fusionSelect2, function() {
				if ( 'undefined' !== typeof jQuery( this ).data( 'select2' ).dropdown ) {
					if ( 'undefined' !== typeof jQuery( this ).data( 'select2' ).dropdown.$dropdown ) {
						jQuery( this ).data( 'select2' ).dropdown.$dropdown.addClass( 'avada-select2' );
					} else if ( 'undefined' !== typeof jQuery( this ).data( 'select2' ).dropdown.selector ) {
						jQuery( jQuery( this ).data( 'select2' ).dropdown.selector ).addClass( 'avada-select2' );
					}
				}
			} );
		},

		exportOptions: function() {
			var tempBeforeUnload = jQuery._data( window, 'events' ).beforeunload;
			jQuery._data( window, 'events' ).beforeunload = null;

			setTimeout( function() {
				jQuery._data( window, 'events' ).beforeunload = tempBeforeUnload;
			}, 350 );
		},

		saveOptions: function( e ) {
			var data = {
					action: 'fusion_page_options_save',
					fusion_po_nonce: jQuery( '#fusion-page-options-nonce' ).val(),
					post_id: jQuery( this ).data( 'post_id' ),
					post_type: jQuery( this ).data( 'post_type' ),
					options_title: jQuery( '#fusion-new-page-options-name' ).val()
				},
				poDialog = jQuery( '#avada-po-dialog' );

			e.preventDefault();

			if ( '' === jQuery( '#fusion-new-page-options-name' ).val().trim() ) {
				poDialog.html( avadaPOMessages.saveTitleWarning );

				jQuery( '#' + poDialog.attr( 'id' ) ).dialog( {
					dialogClass: 'avada-po-dialog',
					resizable: false,
					draggable: false,
					height: 'auto',
					width: 400,
					modal: true,
					buttons: {
						OK: function() {
							poDialog.html( '' );
							jQuery( this ).dialog( 'close' );
						}
					}
				} );

				return;
			}

			jQuery( '#fusion-page-options-loader' ).show();

			jQuery.get( {
				url: ajaxurl,
				data: data,
				dataType: 'json'
			} ).done( function( response ) {
				var html;

				html  = '<option value="' + response.saved_po_dataset_id + '">';
				html += response.saved_po_dataset_title;
				html += '</option>';

				jQuery( '#fusion-saved-page-options-select' ).append( html );

				jQuery( '#fusion-new-page-options-name' ).val( '' );

				jQuery( '#fusion-page-options-loader' ).hide();

			} );
		},

		importSavedOptions: function( e ) {
			var data = {
				action: 'fusion_page_options_import_saved',
				fusion_po_nonce: jQuery( '#fusion-page-options-nonce' ).val(),
				post_id: jQuery( '#fusion-saved-page-options-select' ).data( 'post_id' ),
				saved_po_dataset_id: jQuery( '#fusion-saved-page-options-select' ).val()
			};

			e.preventDefault();

			jQuery( '#fusion-page-options-loader' ).show();

			jQuery.get( {
				url: ajaxurl,
				data: data,
				dataType: 'json'
			} ).done( function( response ) {
				awbUpdatePOPanel( response.custom_fields );
				jQuery( '#fusion-page-options-loader' ).hide();
			} );
		},

		deleteSaved: function( e ) {
			var savedPageOptionsDatasetID = jQuery( '#fusion-saved-page-options-select' ).val(),
				data        = {
					action: 'fusion_page_options_delete',
					fusion_po_nonce: jQuery( '#fusion-page-options-nonce' ).val(),
					saved_po_dataset_id: savedPageOptionsDatasetID
				};

			e.preventDefault();

			jQuery( '#fusion-page-options-loader' ).show();

			jQuery.get( {
				url: ajaxurl,
				data: data
			} ).done( function() {
				jQuery( '#fusion-saved-page-options-select option[value="' +  savedPageOptionsDatasetID + '"]' ).remove();
				jQuery( '#fusion-page-options-loader' ).hide();

				jQuery( '#fusion-page-options-buttons-wrap' ).fadeOut();
			} );

		},

		prepareUpload: function( e ) {
			var file = e.target.files,
				data = new FormData(),
				poDialog = jQuery( '#avada-po-dialog' );

			jQuery( '#fusion-page-options-loader' ).show();

			data.append( 'action', 'fusion_page_options_import' );
			data.append( 'fusion_po_nonce', jQuery( '#fusion-page-options-nonce' ).val() );
			data.append( 'post_id', jQuery( '#fusion-page-options-import' ).data( 'post_id' ) );

			jQuery.each( file, function( key, value ) {

				if ( 'json' !== value.name.substr( value.name.lastIndexOf( '.' ) + 1 ) ) {
					poDialog.html( avadaPOMessages.importJSONWarning );

					jQuery( '#' + poDialog.attr( 'id' ) ).dialog( {
						dialogClass: 'avada-po-dialog',
						resizable: false,
						draggable: false,
						height: 'auto',
						width: 400,
						modal: true,
						buttons: {
							OK: function() {
								poDialog.html( '' );
								jQuery( this ).dialog( 'close' );
							}
						}
					} );
					return false;
				}
				data.append( 'po_file_upload', value );
			} );

			jQuery.ajax( {
				url: ajaxurl,
				type: 'POST',
				data: data,
				cache: false,
				dataType: 'json',
				processData: false, // Don't process the files
				contentType: false // Set content type to false as jQuery will tell the server its a query string request
			} ).done( function( response ) {
				awbUpdatePOPanel( response.custom_fields );
				jQuery( '#fusion-page-options-loader' ).hide();
			} );
		},

		showHideButtons: function() {

			if ( '' !== jQuery( this ).val() ) {
				jQuery( '#fusion-page-options-buttons-wrap' ).fadeIn();
			} else {
				jQuery( '#fusion-page-options-buttons-wrap' ).fadeOut();
			}

		},

		importOptions: function( e ) {
			e.preventDefault();
			jQuery( '#fusion-page-options-file-input' ).trigger( 'click' );
		}

	};

	fusionPageOptions.init();

	jQuery( '.pyre_metabox_tab:not(#pyre_tab_avada_page_options)' ).on( 'change fusion-changed',
		'input, textarea, select, radio, input[type=checkbox], input[type=hidden]',
		function() {
			jQuery( '.avada-po-warning' ).slideDown();
			jQuery( '#pyre_tab_avada_page_options' ).addClass( 'fusion-options-changed' );
		}
	);

	jQuery( '.pyre_metabox_tab:not(#pyre_tab_avada_page_options)' ).on( 'change fusion-changed',
		'input.upload_field',
		function() {
			if ( '' === jQuery( this ).val() ) {
				jQuery( this ).next().val( '' );
			}
		}
	);

// content layout icons.
const contentLayoutVal = jQuery( '#pyre_content_layout' ).val();
jQuery( '#pyre_align_content' ).parent().attr( 'data-direction', contentLayoutVal );

jQuery( document ).on( 'change', '#pyre_content_layout', function( e ) { // eslint-disable-line no-unused-vars
	const alignmentField = jQuery( '#pyre_align_content' ).parent();
	alignmentField.attr( 'data-direction', this.value );
} );

} );

function awbUpdatePOPanel( customFields ) {

	jQuery.each( customFields, function( id, value ) {
		var $el;

		if ( ! id.includes( 'pyre_' ) ) {
			id = 'pyre_' + id;
		}

		$el = jQuery( '#' + id );

		if ( $el.hasClass( 'button-set-value' ) ) {

			$el.siblings( '[data-value="' + value + '"]' ).trigger( 'click' );

			// Continue.
			return true;
		}

		if ( $el.hasClass( 'repeater-value' ) ) {

			$el.val( value );

			fusionPageOptions.initRepeater( $el.closest( '.fusion-repeater-wrapper:not(.fusion-toggle-wrapper)' ) );

			// Continue.
			return true;
		}

		if ( $el.hasClass( 'upload_field' ) ) {

			$el.val( value.url );

			// Continue.
			return true;
		}


		$el.val( value );

		// Range field.
		if ( $el.is( ':hidden' ) && $el.parent( '.pyre_field' ).hasClass( 'avada-range' ) ) {
			$el.siblings( '.fusion-slider-input' ).attr( 'value', value ).trigger( 'keyup' );
		} else {
			$el.trigger( 'change' );
		}

	} );
}

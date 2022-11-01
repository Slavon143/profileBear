/* global fusionAppConfig */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionMultiSelect = {
	optionMultiSelect: function( $element ) {
		var $multiselect;

		$element     = $element || this.$el;
		$multiselect = $element.find( '.fusion-form-multiple-select:not(.fusion-select-inited)' );

		if ( $multiselect.length ) {

			$multiselect.each( function() {
				var $self              = jQuery( this ),
					$selectPreview     = $self.find( '.fusion-select-preview-wrap' ),
					$selectSearchInput = $self.find( '.fusion-select-search input' ),
					$selectAddNew      = $self.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-addnew' ),
					$selectSave        = $self.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-save' ),
					$selectCancel      = $self.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-cancel' ),
					$selectInput       = $self.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-input' );

				$self.addClass( 'fusion-select-inited' );

				// Open select dropdown.
				$selectPreview.on( 'click', function( event ) {
					var open = $self.hasClass( 'fusion-open' );

					if ( event.currentTarget !== this ) {
						return;
					}

					event.preventDefault();

					if ( ! open ) {
						$self.addClass( 'fusion-open' );
						if ( $selectSearchInput.length ) {
							$selectSearchInput.focus();
						}
					} else {
						$self.removeClass( 'fusion-open' );
						if ( $selectSearchInput.length ) {
							$selectSearchInput.val( '' ).blur();
						}
					}
				} );

				// Option is selected.
				$self.on( 'click', '.fusion-select-label', function( event ) {

					// Add / remove selected option from preview box.
					if ( 0 === $self.find( '.fusion-select-preview .fusion-preview-selected-value[data-value="' + jQuery( this ).attr( 'for' ) + '"]' ).length ) {
						$self.find( '.fusion-select-preview' ).append( '<span class="fusion-preview-selected-value" data-value="' + jQuery( this ).attr( 'for' ) + '">' + jQuery( this ).html() + '<span class="fusion-option-remove">x</span></span>' );
					} else {
						$self.find( '.fusion-select-preview .fusion-preview-selected-value[data-value="' + jQuery( this ).attr( 'for' ) + '"]' ).remove();
					}

					// Show / hide placeholder text, ie: 'Select Categories or Leave Blank for All'
					if ( 0 === $self.find( '.fusion-select-preview .fusion-preview-selected-value' ).length ) {
						$selectPreview.addClass( 'fusion-select-show-placeholder' );
					} else {
						$selectPreview.removeClass( 'fusion-select-show-placeholder' );
					}

					// Click event triggered by user pressing 'Enter'.
					if ( 'click' === event.type && 'undefined' !== typeof event.isTrigger && event.isTrigger ) {
						$selectPreview.trigger( 'click' );
					}
				} );

				// Clicked on Add New.
				$selectAddNew.on( 'click', function() {
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( '.fusion-form-multiple-select.fusion-select-inited' ).hide();
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-addnew' ).hide();
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-addnew-section' ).show();
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).focus();
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).off( 'change keyup' );
				} );

				// Clicked on Cancel.
				$selectCancel.on( 'click', function() {
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-addnew-section' ).hide();
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( '.fusion-form-multiple-select.fusion-select-inited' ).show();
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-addnew' ).show();
				} );

				// Add with enter.
				$selectInput.on( 'keypress', function( event ) {
					if ( 13 === event.which ) {
						$selectSave.trigger( 'click' );
					}
				} );

				// Clicked on Save.
				$selectSave.on( 'click', function() {
					var terms    = [],
						ajaxData = {
							action: 'fusion_multiselect_addnew',
							fusion_load_nonce: fusionAppConfig.fusion_load_nonce
						},
						$current = jQuery( this ),
						$options = jQuery( this ).closest( 'li.fusion-builder-option' ).find( '.fusion-select-options' ),
						values   = jQuery( this ).closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).val();

					// early exit if empty field.
					if ( '' === values || 0 === values.trim().length ) {
						return;
					}

					values            = values.split( ',' );
					ajaxData.taxonomy = $current.closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).data( 'id' );

					// Remove existing terms.
					jQuery.each( values, function( index, value ) {
						var term_exists = false;
						value           = value.trim();

						jQuery.each( $options.find( ':checkbox' ), function() {
							var label   = jQuery( this ).data( 'label' ).toString(),
								checked = jQuery( this ).is( ':checked' );
							label = label.trim();

							if ( value.toLowerCase() === label.toLowerCase() ) {
								term_exists = true;

								if ( ! checked ) {
									$current.closest( 'li.fusion-builder-option' ).find( '.fusion-select-label[for="' + ajaxData.taxonomy + '-' + jQuery( this ).val() + '"]' ).trigger( 'click' );
								}
							}
						} );

						if ( ! term_exists ) {
							terms.push( value );
						}
					} );

					// early exit if duplicate values.
					if ( '' === terms || 0 === terms.length ) {
						$current.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-cancel' ).trigger( 'click' );
						$current.closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).val( '' );
						$current.closest( 'li.fusion-builder-option' ).find( '.fusion-form-multiple-select' ).removeClass( 'fusion-open' );
						return;
					}

					ajaxData.values = terms;

					// Add loader.
					$current.closest( 'li.fusion-builder-option' ).addClass( 'partial-refresh-active' );

					// Send data.
					jQuery.post( fusionAppConfig.ajaxurl, ajaxData, function( response ) {
						response = JSON.parse( response );
						if ( 'object' === typeof response ) {
							jQuery.each( response, function( term, term_id ) {
								$options.append( '<input type="checkbox" id="' + ajaxData.taxonomy + '-' + term_id + '" name="' + ajaxData.taxonomy + '[]" value="' + term_id + '" data-label="' + term + '" class="fusion-select-option fusion-multi-select-option">' );
								$options.append( '<label for="' + ajaxData.taxonomy + '-' + term_id + '" class="fusion-select-label">' + term + '</label>' );
								$current.closest( 'li.fusion-builder-option' ).find( '.fusion-select-label[for="' + ajaxData.taxonomy + '-' + term_id + '"]' ).trigger( 'click' );
								$current.closest( 'li.fusion-builder-option' ).find( '.fusion-form-multiple-select' ).removeClass( 'fusion-open' );
							} );

							// Show / hide placeholder text, ie: 'Select Categories or Leave Blank for All'
							if ( 0 === $self.find( '.fusion-select-preview .fusion-preview-selected-value' ).length ) {
								$selectPreview.addClass( 'fusion-select-show-placeholder' );
							} else {
								$selectPreview.removeClass( 'fusion-select-show-placeholder' );
							}

							// Remove Loader.
							$current.closest( 'li.fusion-builder-option' ).removeClass( 'partial-refresh-active' );

							$current.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-cancel' ).trigger( 'click' );
							$current.closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).val( '' );
						}
					} );
				} );

				// Remove option from preview box.
				$selectPreview.find( '.fusion-select-preview' ).on( 'click', '.fusion-option-remove', function( event ) {
					event.preventDefault();

					const el = $self.find( '.fusion-select-label[for="' + jQuery( this ).parent().data( 'value' ) + '"]' );

					if ( el.length )  {
						el.trigger( 'click' );
					} else {

						// Option label not found so recall same function in line 49 with minor change.

						// Stop propagation to prevent toggle the select dropdown.
						event.stopPropagation();

						// Remove parent from preview box.
						jQuery( this ).parent().remove();

						// Show / hide placeholder text, ie: 'Select Categories or Leave Blank for All'
						if ( 0 === $self.find( '.fusion-select-preview .fusion-preview-selected-value' ).length ) {
							$selectPreview.addClass( 'fusion-select-show-placeholder' );
						} else {
							$selectPreview.removeClass( 'fusion-select-show-placeholder' );
						}

						// Click event triggered by user pressing 'Enter'.
						if ( 'click' === event.type && 'undefined' !== typeof event.isTrigger && event.isTrigger ) {
							$selectPreview.trigger( 'click' );
						}

					}
				} );

				// Search field.
				$selectSearchInput.on( 'keyup change paste', function( event ) {
					var val = jQuery( this ).val(),
						optionInputs = $self.find( '.fusion-select-option' );

					// Select option on "Enter" press if only 1 option is visible.
					if ( 'keyup' === event.type && 13 === event.keyCode && 1 === $self.find( '.fusion-select-label:visible' ).length ) {
						$self.find( '.fusion-select-label:visible' ).trigger( 'click' );
						return;
					}

					_.each( optionInputs, function( optionInput ) {
						if ( -1 === jQuery( optionInput ).data( 'label' ).toLowerCase().indexOf( val.toLowerCase() ) ) {
							jQuery( optionInput ).siblings( '.fusion-select-label[for="' + jQuery( optionInput ).attr( 'id' ) + '"]' ).css( 'display', 'none' );
						} else {
							jQuery( optionInput ).siblings( '.fusion-select-label[for="' + jQuery( optionInput ).attr( 'id' ) + '"]' ).css( 'display', 'block' );
						}
					} );
				} );

			} );

		}
	}
};

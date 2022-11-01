/* global FusionPageBuilderApp, fusionAppConfig */

var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionFormOptions = {
	optionFormOptions: function ( $element ) {
		var $valuesToggle = $element.find( '#form-options-settings' ),
			$optionsGrid = $element.find( '.options-grid' ),
			$addBtn = $element.find( '.fusion-builder-add-sortable-child' ),
			$formOptions = $optionsGrid.find( '.fusion-form-options' ),
			$template = jQuery( '<li class="fusion-form-option">' + $element.find( '.fusion-form-option-template' ).html() + '</li>' ),
			$values = $optionsGrid.find( '.option-values' ),
			$bulkAdd = $element.find( '.bulk-add-modal' ),
			allowMultiple = 'yes' === $optionsGrid.data( 'multiple' ),
			updateValues;

		updateValues = function () {
			var options = [];
			$formOptions.children( 'li' ).each( function () {
				var option 		= [],
					isChecked 	= jQuery( this ).find( '.fusiona-check_circle' ).length;

				option.push( isChecked ? 1 : 0 );

				jQuery( this ).find( 'input' ).each( function () {
					option.push( this.value );
				} );
				options.push( option );
			} );
			$values
				.val( FusionPageBuilderApp.base64Encode( JSON.stringify( options ) ) )
				.trigger( 'change' );
		};

		// Init sortable
		$formOptions.sortable( {
			handle: '.fusion-sortable-move'
		} );

		// Bindings
		$formOptions.on( 'sortupdate', function () {
			updateValues();
		} );
		$formOptions.on( 'change keyup', 'input', function ( event ) {
			event.preventDefault();
			updateValues();
		} );

		$valuesToggle.on( 'click', function () {
			$optionsGrid.toggleClass( 'show-values' );
		} );

		$formOptions.on( 'click', '.fusion-sortable-remove', function ( event ) {
			event.preventDefault();
			jQuery( event.target ).closest( '.fusion-form-option' ).remove();
			updateValues();
		} );

		$formOptions.on( 'click', '.fusion-sortable-check', function( event ) {
			var $el 		= jQuery( this ).find( '.fusiona-check_circle_outline' ),
				isChecked 	= $el.hasClass( 'fusiona-check_circle' );

			event.preventDefault();

			if ( ! allowMultiple ) {
				$formOptions.find( '.fusion-sortable-check .fusiona-check_circle' ).removeClass( 'fusiona-check_circle' );
			}

			if ( isChecked ) {
				$el.removeClass( 'fusiona-check_circle' );
			} else {
				$el.addClass( 'fusiona-check_circle' );
			}
			updateValues();
		} );

		$addBtn.on( 'click', function( event ) {
			var $newEl = $template.clone( true );

			event.preventDefault();

			$formOptions.append( $newEl );
			setTimeout( function () {
				$newEl.find( '.form-option-label input' ).focus();
			}, 100 );
		} );

		$bulkAdd.on( 'click', function( event ) {
			var modalView;

			event.preventDefault();

			if ( jQuery( '.fusion-builder-settings-dialog.bulk-add-dialog' ).length ) {
				return;
			}

			modalView = new FusionPageBuilder.BulkAddView( {
				choices: fusionAppConfig.predefined_choices
			} );

			jQuery( modalView.render().el ).dialog( {
				title: 'Bulk Add / Predefined Choices',
				dialogClass: 'fusion-builder-dialog fusion-builder-settings-dialog bulk-add-dialog',
				resizable: false,
				width: 450,
				buttons: {
					'Insert Choices': function() {
						var choices = modalView.getChoices(),
							$newEl;
						_.each( choices, function( choice ) {
							$newEl 	= $template.clone( true );
							if ( choice.includes( '|' ) ) {
								choice = choice.split( '|' );
								$newEl.find( 'input.label' ).val( choice[ 0 ] );
								$newEl.find( 'input.value' ).val( choice[ 1 ] );
								$valuesToggle.prop( 'checked', true );
								$optionsGrid.addClass( 'show-values' );
							} else {
								$newEl.find( 'input.label' ).val( choice );
							}
							$formOptions.append( $newEl );
						} );

						updateValues();
						jQuery( this ).dialog( 'close' );
					},
					Cancel: function() {
						jQuery( this ).dialog( 'close' );
					}
				},
				beforeClose: function() {
					jQuery( this ).remove();
				}

			} );
		} );
	}
};

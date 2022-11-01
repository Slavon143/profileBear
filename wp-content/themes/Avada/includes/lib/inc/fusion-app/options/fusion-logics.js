/* global FusionPageBuilderApp, fusionBuilderText */

var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionLogics = {
	optionLogics: function ( $element ) {
		var $optionsGrid = $element.find( '.options-grid' ),
			$addBtn = $element.find( '.fusion-builder-add-sortable-child' ),
			$fusionLogics = $optionsGrid.find( '.fusion-logics' ),
			$template = jQuery( '<li class="fusion-logic">' + $element.find( '.fusion-logic-template' ).html() + '</li>' ),
			$values = $optionsGrid.find( '.logic-values' ),
			updateValues;

		updateValues = function () {
			var options = [];
			$fusionLogics.children( 'li' ).each( function () {
				var option 		= {},
					operator 	 = jQuery( this ).find( '.fusion-sortable-operator' );

				// operator.
				option.operator  =  operator.hasClass( 'and' ) ? 'and' : 'or';
				// comparison.
				option.comparison = jQuery( this ).find( '.logic-comparison-selection' ).val();
				// field.
				option.field = jQuery( this ).find( 'select.fusion-logic-choices' ).val();
				// desired value.
				option.value = jQuery( this ).find( '.fusion-logic-option' ).val();
				// additinals.
				if ( jQuery( this ).find( '.logic-additionals' ).length ) {
					option.additionals = jQuery( this ).find( '.fusion-logic-additionals-field' ).val();
				}
				options.push( option );
			} );
			$values
				.val( FusionPageBuilderApp.base64Encode( JSON.stringify( options ) ) )
				.trigger( 'change' );
		};

		// Init sortable
		$fusionLogics.sortable( {
			items: '.fusion-logic',
			tolerance: 'pointer',
			cursor: 'move',
			connectWith: '.fusion-logics',
			handle: '.fusion-logic-controller-head',
			axis: 'y'
		} );

		// Bindings
		$fusionLogics.on( 'sortupdate', function () {
			updateValues();
		} );

		$fusionLogics.on( 'change keyup', 'input', function ( event ) {
			event.preventDefault();
			updateValues();
		} );

		$fusionLogics.on( 'change', 'select.fusion-logic-option', function( event ) {
			event.preventDefault();
			updateValues();
		} );

		$fusionLogics.on( 'change', 'select.fusion-logic-choices', function( event ) {
			var allChoices  = $fusionLogics.closest( '.fusion-builder-option-logics' ).find( '.fusion-logics-all-choices' ).text(),
				selection     = jQuery( this ).val(),
				selectionText = jQuery( this ).closest( 'select' ).find( 'option:selected' ).text(),
				$wrapper      = jQuery( this ).closest( '.fusion-logic' ),
				$comparisons  = '',
				$options      = '',
				isSelected,
				currentChoice;

			event.preventDefault();

			try {
				allChoices = JSON.parse( allChoices );
			} catch ( e ) {
				allChoices = [];
			}

			$wrapper.find( 'h4.logic-title' ).text( selectionText );

			currentChoice = allChoices.find( ( { id } ) => id === selection );

			if ( 'object' === typeof currentChoice ) {
				if ( 'object' === typeof currentChoice.comparisons ) {
					jQuery.each( currentChoice.comparisons, function( comparisonValue, comparisonName ) {
						isSelected    = 'equal' === comparisonValue ? 'active' : '';
						$comparisons   += '<option value="' + comparisonValue + '" ' + isSelected + '>' + comparisonName + '</select>';
					} );
				}

				$wrapper.find( '.logic-comparison-selection' ).empty().append( $comparisons );

				switch ( currentChoice.type ) {
				case 'select':
					if ( 'object' === typeof currentChoice.options ) {
						$options += '<div class="fusion-select-wrapper">';
						$options += '<select class="fusion-logic-option fusion-hide-from-atts">';
						jQuery.each( currentChoice.options, function( key, choice ) {
							$options += '<option value="' + key + '">' + choice + '</option>';
						} );
						$options += '</select>';
						$options += '<span class="fusiona-arrow-down"></span>';
						$options += '</div>';
					}

					$wrapper.find( '.logic-value-field' ).html( $options );
					break;

				case 'text':
					$options = `<input type="text" value="" placeholder="${currentChoice.placeholder || fusionBuilderText.condition_value}" class="fusion-hide-from-atts fusion-logic-option" />`;
					$wrapper.find( '.logic-value-field' ).html( $options );
					break;
				}

				$wrapper.find( '.logic-additionals' ).remove();
				if ( 'undefined' !== typeof currentChoice.additionals ) {
					switch ( currentChoice.additionals.type ) {
					case 'select':
						if ( 'object' === typeof currentChoice.additionals.options ) {
							$options = '<div class="logic-additionals">';
							$options += '<div class="select_arrow"></div>';
							$options += '<select class="fusion-logic-additionals fusion-hide-from-atts fusion-select-field">';
							jQuery.each( currentChoice.additionals, function( key, choice ) {
								$options += '<option value="' + key + '">' + choice + '</option>';
							} );
							$options += '</select>';
							$options += '</div>';
						}

						$wrapper.find( '.logic-field' ).append( $options );
						break;

					case 'text':
						$options = '<div class="logic-additionals">';
						$options += '<input type="text" value="" placeholder="' + currentChoice.additionals.placeholder + '" class="fusion-hide-from-atts fusion-logic-additionals-field" />';
						$options += '</div>';
						$wrapper.find( '.logic-field' ).append( $options );
						break;
					}
				}
			}

			updateValues();
		} );

		$fusionLogics.on( 'click', '.fusion-sortable-remove', function ( event ) {
			event.preventDefault();
			jQuery( event.target ).closest( '.fusion-logic' ).remove();

			updateValues();
		} );

		$fusionLogics.on( 'click', '.fusion-sortable-edit, h4.logic-title', function( event ) {
			var $parent = jQuery( this ).closest( '.fusion-logic' );
			event.preventDefault();

			$parent.find( '.fusion-logic-controller-content' ).slideToggle( 'fast' );
		} );


		$fusionLogics.on( 'click', '.logic-operator', function() {
			var $el = jQuery( this ).find( '.fusion-sortable-operator' );

			if ( $el.hasClass( 'and' ) ) {
				$el.removeClass( 'and' ).addClass( 'or' );
				$el.closest( '.fusion-logic' ).addClass( 'has-or' ).attr( 'aria-label-or', fusionBuilderText.logic_separator_text );
			} else {
				$el.removeClass( 'or' ).addClass( 'and' );
				$el.closest( '.fusion-logic' ).removeClass( 'has-or' );
			}
			updateValues();
		} );

		$fusionLogics.on( 'change', '.logic-comparison-selection', function() {
			event.preventDefault();
			updateValues();
		} );

		$addBtn.on( 'click', function( event ) {
			var $newEl = $template.clone( true );

			event.preventDefault();

			$fusionLogics.find( '.fusion-logic-controller-content' ).hide();

			$fusionLogics.append( $newEl );
			updateValues();
		} );
	}
};

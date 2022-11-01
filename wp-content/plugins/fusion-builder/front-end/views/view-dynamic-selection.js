/* global FusionPageBuilderApp */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Dynamic Selection.
		FusionPageBuilder.DynamicSelection = window.wp.Backbone.View.extend( {

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-dynamic-selection' ).html() ),

			className: 'fusion-builder-dynamic-selection option-field',

			events: {
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.1
			 * @return {Object} this
			 */
			render: function() {
				var $option = this.model.get( 'option' ),
					templateData;

				if ( $option.length ) {
					$option.attr( 'data-dynamic-selection', true );
				}

				templateData = {
					params: FusionPageBuilderApp.dynamicValues.getOrderedParams(),
					option: $option.attr( 'data-option-type' )
				};

				this.$el.html( this.template( templateData ) );

				this.initSelect();

				return this;
			},

			initSelect: function() {
				var self               = this,
					parent             = this.model.get( 'parent' ),
					$option            = this.model.get( 'option' ),
					param              = this.model.get( 'param' ),
					$selectField       = this.$el.find( '.fusion-select-field' ),
					$selectPreview     = $selectField.find( '.fusion-select-preview-wrap' ),
					$selectSearchInput = $selectField.find( '.fusion-select-search input' );

				if ( $selectField.hasClass( 'fusion-select-inited' ) ) {
					return;
				}

				$selectField.addClass( 'fusion-select-inited' );

				// Hide empty option groups.
				$selectField.find( '.fusion-select-optiongroup' ).each( function() {
					if ( jQuery( this ).next().hasClass( 'fusion-select-optiongroup' ) || 0 === jQuery( this ).next().length ) {
						jQuery( this ).remove();
					}
				} );

				// Open select dropdown.
				$selectPreview.on( 'click', function( event ) {
					var open = $selectField.hasClass( 'fusion-open' );

					event.preventDefault();

					if ( ! open ) {
						$selectField.addClass( 'fusion-open' );
						if ( $selectSearchInput.length ) {
							$selectSearchInput.focus();
						}
					} else {
						$selectField.removeClass( 'fusion-open' );
						if ( $selectSearchInput.length ) {
							$selectSearchInput.val( '' ).blur();
						}
						$selectField.find( '.fusion-select-label' ).css( 'display', 'block' );
					}
				} );

				// Option is selected.
				$selectField.on( 'click', '.fusion-select-label', function() {
					parent.elementView.dynamicParams.addParam( param, jQuery( this ).data( 'value' ) );
					parent.initEditDynamic( $option.find( '.fusion-dynamic-content' ), true );
					self.removeView();
				} );

				$selectSearchInput.on( 'keyup change paste', function() {
					var val          = jQuery( this ).val(),
						optionInputs = $selectField.find( '.fusion-select-label' );

					// Select option on "Enter" press if only 1 option is visible.
					if ( 'keyup' === event.type && 13 === event.keyCode && 1 === $selectField.find( '.fusion-select-label:visible' ).length ) {
						$selectField.find( '.fusion-select-label:visible' ).trigger( 'click' );
						return;
					}

					_.each( optionInputs, function( optionInput ) {
						if ( -1 === jQuery( optionInput ).html().toLowerCase().indexOf( val.toLowerCase() ) ) {
							jQuery( optionInput ).css( 'display', 'none' );
						} else {
							jQuery( optionInput ).css( 'display', 'block' );
						}
					} );
				} );
			},

			removeView: function() {
				var $option = this.model.get( 'option' ),
					parent  = this.model.get( 'parent' );

				this.$el.remove();

				if ( parent ) {
					parent.dynamicSelection = false;
				}
				if ( $option.length ) {
					$option.attr( 'data-dynamic-selection', false );
				}

				// Destroy element model
				this.model.destroy();

				this.remove();
			}

		} );
	} );
}( jQuery ) );

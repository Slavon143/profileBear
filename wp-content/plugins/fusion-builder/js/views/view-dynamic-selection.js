/* global FusionPageBuilderApp, fusionBuilderText */

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
					$selectField       = this.$el.find( '.fusion-select-field' );

				if ( $selectField.hasClass( 'fusion-select-inited' ) ) {
					return;
				}

				// Remove empty option groups.
				$selectField.find( 'optgroup' ).each( function() {
					if ( 0 === jQuery( this ).find( 'option' ).length ) {
						jQuery( this ).remove();
					}
				} );

				$selectField.select2( {
					placeholder: fusionBuilderText.select_dynamic_content
				} );

				// Option is selected.
				$selectField.on( 'change.select2', function( event ) {
					$selectField.select2( 'close' );
					parent.dynamicParams.addParam( param, jQuery( event.target ).val() );
					parent.initEditDynamic( $option.find( '.fusion-dynamic-content' ), true );
					self.removeView();
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

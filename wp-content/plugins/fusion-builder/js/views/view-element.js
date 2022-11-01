/* global FusionPageBuilderApp, fusionBuilderText, FusionPageBuilderEvents, FusionPageBuilderViewManager, fusionHistoryManager, fusionBuilderText, fusionAllElements */
/* eslint no-alert: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		// Builder Element View
		FusionPageBuilder.ElementView = window.wp.Backbone.View.extend( {

			className: 'fusion_module_block fusion_builder_column_element',
			template: FusionPageBuilder.template( $( '#fusion-builder-block-module-template' ).html() ),
			events: {
				'click .fusion-builder-settings': 'showSettings',
				'click .fusion-builder-clone-module': 'cloneElement',
				'click .fusion-builder-remove': 'removeElement',
				'click .fusion-builder-save-module-dialog': 'saveElementDialog'
			},

			initialize: function() {
				this.elementIsCloning = false;
				this.initDynamicParams();
			},

			initDynamicParams: function() {
				var self        = this,
					params      = 'object' === typeof this.model.get( 'params' ) ? this.model.get( 'params' ) : {},
					dynamicData = params.dynamic_params;

				if ( 'string' === typeof params.dynamic_params && '' !== params.dynamic_params ) {
					try {
						if ( FusionPageBuilderApp.base64Encode( FusionPageBuilderApp.base64Decode( dynamicData ) ) === dynamicData ) {
							dynamicData = FusionPageBuilderApp.base64Decode( dynamicData );
							dynamicData = _.unescape( dynamicData );
							dynamicData = JSON.parse( dynamicData );
						}
						self.model.set( 'dynamic_params', dynamicData );
					} catch ( error ) {
						console.log( error ); // jshint ignore:line
					}
				}
			},

			render: function() {
				this.$el.html( this.template( this.model.attributes ) );

				// If Global, make it.
				if ( 'undefined' !== typeof this.model.attributes.params.fusion_global ) {
					FusionPageBuilderApp.addClassToElement( this.$el, 'fusion-global-element', this.model.attributes.params.fusion_global, this.model.get( 'cid' ) );
				}

				return this;
			},

			saveElementDialog: function( event ) {
				if ( event ) {
					event.preventDefault();
				}
				FusionPageBuilderApp.showLibrary();

				// Change to elements tab
				$( '#fusion-builder-layouts-elements-trigger' ).click();

				$( '#fusion-builder-layouts-elements .fusion-builder-layouts-header-element-fields' ).append( '<div class="fusion-save-element-fields"><input type="text" value="" id="fusion-builder-save-element-input" class="fusion-builder-save-element-input" placeholder="' + fusionBuilderText.enter_name + '" /><div class="save-as-global"><label><input type="checkbox" id="fusion_save_global" name="fusion_save_global">' + fusionBuilderText.save_global + '</label><a href="#" class="fusion-builder-save-column fusion-builder-element-button-save" data-element-cid="' + this.model.get( 'cid' ) + '">' + fusionBuilderText.save_element + '</a></div></div>' );
			},

			saveElement: function( event ) {

				var elementContent   = this.getElementContent(),
					$mainContainer   = $( '#fusion_builder_main_container' ),
					elementName      = $( '#fusion-builder-save-element-input' ).val(),
					saveGlobal       = $( '#fusion_save_global' ).is( ':checked' ),
					layoutsContainer = $( '#fusion-builder-layouts-elements .fusion-page-layouts' ),
					emptyMessage     = $( '#fusion-builder-layouts-elements .fusion-page-layouts .fusion-empty-library-message' ),
					thisModel        = this.model,
					isDuplicate      = false,
					oldGLobalID      = null,
					params           = {};

				if ( event ) {
					event.preventDefault();
				}

				if ( 'undefined' !== typeof this.model.attributes.params && 'undefined' !== typeof this.model.attributes.params.fusion_global && 0 < $mainContainer.find( '[fusion-global-layout="' + this.model.attributes.params.fusion_global + '"]' ).length ) {

					// Make a copy.
					oldGLobalID = this.model.attributes.params.fusion_global;
					params      = this.model.get( 'params' );

					// Remove temporarily and update model
					delete params.fusion_global;
					this.model.set( 'params', params );

					// Get content.
					elementContent = this.getElementContent();

					// Add it back.
					params.fusion_global = oldGLobalID;
					this.model.set( 'params', params );
				}

				$.each( jQuery( 'ul.fusion-page-layouts.fusion-layout-elements li' ), function() {
					var templateName = jQuery( this ).find( 'h4.fusion-page-layout-title' ).html().split( '<div ' )[ 0 ];
					templateName     = templateName.replace( /\u2013|\u2014/g, '-' );
					if ( elementName.toLowerCase().trim() === templateName.toLowerCase().trim() ) {
						alert( fusionBuilderText.duplicate_element_name_error );
						isDuplicate = true;
						return false;
					}
				} );

				if ( true === FusionPageBuilderApp.layoutIsSaving || true === isDuplicate ) {
					return;
				}
				FusionPageBuilderApp.layoutIsSaving = true;

				if ( '' !== elementName ) {

					$.ajax( {
						type: 'POST',
						url: FusionPageBuilderApp.ajaxurl,
						dataType: 'json',
						data: {
							action: 'fusion_builder_save_layout',
							fusion_load_nonce: FusionPageBuilderApp.fusion_load_nonce,
							fusion_layout_name: elementName,
							fusion_save_global: saveGlobal,
							fusion_layout_content: elementContent,
							fusion_layout_post_type: 'fusion_element',
							fusion_layout_new_cat: 'elements'
						},
						complete: function( data ) {
							FusionPageBuilderApp.layoutIsSaving = false;
							layoutsContainer.prepend( data.responseText );
							$( '.fusion-save-element-fields' ).remove();
							emptyMessage.hide();

							// If global, make it.
							if ( saveGlobal ) {
								thisModel.attributes.params.fusion_global = $( data.responseText ).attr( 'data-layout_id' );
								$( 'div.fusion_builder_column_element[data-cid="' + thisModel.get( 'cid' ) + '"]' ).addClass( 'fusion-global-element' );
								$( 'div.fusion_builder_column_element[data-cid="' + thisModel.get( 'cid' ) + '"]' ).attr( 'fusion-global-layout', $( data.responseText ).attr( 'data-layout_id' ) );
								$( 'div.fusion_builder_column_element[data-cid="' + thisModel.get( 'cid' ) + '"]' ).append( '<div class="fusion-builder-global-tooltip" data-cid="' + thisModel.get( 'cid' ) + '"><span>' + fusionBuilderText.global_element + '</span></div>' );
								FusionPageBuilderEvents.trigger( 'fusion-element-added' );
								FusionPageBuilderApp.saveGlobal = true;

								// Check for globals.
								setTimeout( FusionPageBuilderApp.checkGlobalParents, 500, thisModel.get( 'parent' ) );
							}
						}
					} );

				} else {
					FusionPageBuilderApp.layoutIsSaving = false;
					alert( fusionBuilderText.please_enter_element_name );
				}
			},

			getElementContent: function() {
				return FusionPageBuilderApp.generateElementShortcode( this.$el, false );
			},

			removeElement: function( event ) {
				var parentCID;

				if ( event ) {
					event.preventDefault();
				}

				parentCID = this.model.get( 'parent' );

				// Remove element view
				FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

				// Destroy element model
				this.model.destroy();

				this.remove();

				// If element is removed manually
				if ( event ) {

					// Save history state
					fusionHistoryManager.turnOnTracking();
					window.fusionHistoryState = fusionBuilderText.deleted + ' ' + fusionAllElements[ this.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element;

					FusionPageBuilderEvents.trigger( 'fusion-element-removed' );
				}

				// Check for globals.
				setTimeout( FusionPageBuilderApp.checkGlobalParents, 500, parentCID );

			},

			cloneElement: function( event, parentCID ) {
				var elementAttributes;

				if ( event ) {
					event.preventDefault();
				}

				if ( this.$el.children( '.fusion-builder-module-controls-container' ).hasClass( 'cloning-disabled' ) || true === this.elementIsCloning ) {
					return;
				}

				this.elementIsCloning = true;

				elementAttributes = $.extend( true, {}, this.model.attributes );
				elementAttributes.created = 'manually';
				elementAttributes.cid = FusionPageBuilderViewManager.generateCid();
				elementAttributes.targetElement = this.$el;
				if ( 'undefined' !== elementAttributes.from ) {
					delete elementAttributes.from;
				}

				if ( parentCID ) {
					elementAttributes.parent = parentCID;
				}

				FusionPageBuilderApp.collection.add( elementAttributes );

				if ( ! parentCID ) {

					// Save history state
					fusionHistoryManager.turnOnTracking();
					window.fusionHistoryState = fusionBuilderText.cloned + ' ' + fusionAllElements[ this.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element;
				}

				this.elementIsCloning = false;

				if ( event ) {
					FusionPageBuilderEvents.trigger( 'fusion-element-cloned' );
				}

				// Check for globals.
				if ( elementAttributes.parent ) {
					setTimeout( FusionPageBuilderApp.checkGlobalParents, 500, elementAttributes.parent );
				}
			},

			showSettings: function( event ) {
				var modalView,
					viewSettings = {
						model: this.model,
						collection: this.collection,
						attributes: {
							'data-modal_view': 'element_settings'
						}
					};

				if ( event ) {
					event.preventDefault();
				}

				modalView = new FusionPageBuilder.ModalView( viewSettings );

				$( 'body' ).append( modalView.render().el );
			}
		} );
	} );
}( jQuery ) );

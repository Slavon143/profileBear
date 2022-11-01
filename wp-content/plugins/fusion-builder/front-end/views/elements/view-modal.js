var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Modal view.
		FusionPageBuilder.fusion_modal = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs during initialize() call.
			 *
			 * @since 2.0.0
			 * @return null
			 */
			onInit: function() {
				var $modal = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el );

				$modal.on( 'shown.bs.modal', function() {
					jQuery( 'body' ).addClass( 'fusion-builder-no-ui fusion-dialog-ui-active' );
					$modal.closest( '.fusion-builder-column' ).css( 'z-index', 'auto' ); // Because of animated items getting z-index 2000.
					$modal.closest( '#main' ).css( 'z-index', 'auto' );
					$modal.closest( '.fusion-row' ).css( 'z-index', 'auto' );
					$modal.closest( '.fusion-builder-container' ).css( 'z-index', 'auto' );
				} );

				$modal.on( 'hide.bs.modal', function() {
					jQuery( 'body' ).removeClass( 'fusion-builder-no-ui fusion-dialog-ui-active' );
					$modal.closest( '.fusion-builder-column' ).css( 'z-index', '' );
					$modal.closest( '#main' ).css( 'z-index', '' );
					$modal.closest( '.fusion-row' ).css( 'z-index', '' );
					$modal.closest( '.fusion-builder-container' ).css( 'z-index', '' );
				} );

			},

			/**
			 * Open actual modal.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onSettingsOpen: function() {
				var self   = this,
					$modal = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.fusion-modal' ) );

				this.disableDroppableElement();
				jQuery( this.$el ).closest( '.fusion-builder-live-element' ).css( 'cursor', 'default' );
				jQuery( this.$el ).closest( '.fusion-builder-column' ).css( 'z-index', 'auto' ); // Because of animated items getting z-index 2000.
				jQuery( this.$el ).closest( '.fusion-row' ).css( 'z-index', 'auto' );
				jQuery( this.$el ).closest( '.fusion-builder-container' ).css( 'z-index', 'auto' );

				setTimeout( function() {
					if ( jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-footer-parallax' ).length ) {
						jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#main' ).css( 'z-index', 'auto' );

						if ( 'fixed' === jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-footer-parallax' ).css( 'position' ) ) {
							jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-footer-parallax' ).css( 'z-index', '-1' );

							if ( jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#sliders-container' ).find( '.tfs-slider[data-parallax="1"]' ).length ) {
								jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#sliders-container' ).css( 'z-index', 'auto' );
							}
						}

					}
				}, 100 );

				$modal.addClass( 'in' ).show();

				$modal.find( 'button[data-dismiss="modal"], .fusion-button[data-dismiss="modal"]' ).one( 'click', function() {
					window.FusionEvents.trigger( 'fusion-close-settings-' + self.model.get( 'cid' ) );
				} );
			},

			/**
			 * Close the modal.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onSettingsClose: function() {
				var $modal = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.fusion-modal' ) );

				$modal.find( 'button[data-dismiss="modal"], .fusion-button[data-dismiss="modal"]' ).off( 'click' );

				this.enableDroppableElement();
				jQuery( this.$el ).closest( '.fusion-builder-live-element' ).css( 'cursor', '' );
				jQuery( this.$el ).closest( '.fusion-builder-column' ).css( 'z-index', '' );
				jQuery( this.$el ).closest( '.fusion-row' ).css( 'z-index', '' );
				jQuery( this.$el ).closest( '.fusion-builder-container' ).css( 'z-index', '' );

				if ( jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-footer-parallax' ).length ) {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#main' ).css( 'z-index', '' );
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-footer-parallax' ).css( 'z-index', '' );
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#sliders-container' ).css( 'z-index', '' );
				}

				$modal.removeClass( 'in' ).hide();
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var $modal = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.fusion-modal' ) );

				if ( jQuery( '.fusion-builder-module-settings[data-element-cid="' + this.model.get( 'cid' ) + '"]' ).length ) {
					$modal.addClass( 'in' ).show();
					$modal.find( '.full-video, .video-shortcode, .wooslider .slide-content' ).fitVids();
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Create attribute objects
				attributes.attrModal        = this.buildModalAttr( atts.values );
				attributes.attrDialog       = this.buildDialogAttr( atts.values );
				attributes.attrContent      = this.buildContentAttr( atts.values );
				attributes.attrButton       = this.buildButtonAttr( atts.values );
				attributes.attrHeading      = this.buildHeadingAttr( atts.values );
				attributes.attrFooterButton = this.buildHFooterButtonAttr( atts.values );
				attributes.attrBody         = this.buildBodyAttr( atts.values );
				attributes.borderColor      = atts.values.border_color;
				attributes.title            = atts.values.title;
				attributes.showFooter       = atts.values.show_footer;
				attributes.closeText        = atts.extras.close_text;
				attributes.elementContent   = atts.values.element_content;
				attributes.name             = atts.values.name;
				attributes.label            = window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				attributes.icon             = window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;

				// Any extras that need passed on.
				attributes.cid = this.model.get( 'cid' );

				return attributes;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildModalAttr: function( values ) {
				var attrModal = {
					class: 'fusion-modal modal fade modal-' + this.model.get( 'cid' ),
					tabindex: '-1',
					role: 'dialog',
					style: 'z-index: 9999999; background: rgba(0,0,0,0.5);',
					'aria-labelledby': 'modal-heading-' + this.model.get( 'cid' ),
					'aria-hidden': 'true'
				};

				if ( '' !== values.name ) {
					attrModal[ 'class' ] += ' ' + values.name;
				}

				if ( '' !== values[ 'class' ] ) {
					attrModal[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attrModal.id = values.id;
				}

				return attrModal;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildDialogAttr: function( values ) {
				var attrDialog = {
					class: 'modal-dialog',
					role: 'document'
				};
				attrDialog[ 'class' ] += ( 'small' === values.size ) ? ' modal-sm' : ' modal-lg';

				return attrDialog;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildContentAttr: function( values ) {
				var attrContent = {
					class: 'modal-content fusion-modal-content'
				};
				if ( '' !== values.background ) {
					attrContent.style = 'background-color:' + values.background;
				}

				return attrContent;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @return {Object} - Body attributes.
			 */
			buildBodyAttr: function() {
				var attrBody = {
					class: 'modal-body'
				};

				attrBody = _.fusionInlineEditor( {
					cid: this.model.get( 'cid' )
				}, attrBody );

				return attrBody;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			buildButtonAttr: function() {
				var attrButton = {
					class: 'close',
					type: 'button',
					'data-dismiss': 'modal',
					'aria-hidden': 'true'
				};

				return attrButton;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			buildHeadingAttr: function() {
				var attrHeading = {
					class: 'modal-title',
					id: 'modal-heading-' + this.model.get( 'cid' ),
					'data-dismiss': 'modal',
					'aria-hidden': 'true',
					'aria-label': 'Close'
				};

				attrHeading = _.fusionInlineEditor( {
					cid: this.model.get( 'cid' ),
					param: 'title',
					'disable-return': true,
					'disable-extra-spaces': true,
					toolbar: false
				}, attrHeading );

				return attrHeading;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			buildHFooterButtonAttr: function() {
				var attrFooterButton = {
					class: 'fusion-button button-default button-medium button default medium',
					type: 'button',
					'data-dismiss': 'modal'
				};

				return attrFooterButton;
			}

		} );
	} );
}( jQuery ) );

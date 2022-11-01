/* global FusionEvents */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.Dialog = Backbone.Model.extend( {

		initialize: function() {
			var self = this;

			// Dialog as percentage.
			this.dialogWidth  = 0.85 * jQuery( window ).width(),
			this.dialogHeight = 0.9 * jQuery( window ).height();

			// Initial dialog settings.
			this.setDialogData();

			jQuery( window ).on( 'resize', function() {
				self.resizeDialog();
			} );

			this.extendDialog();
		},

		extendDialog: function() {
			jQuery.widget( 'ui.dialog', jQuery.extend( {}, jQuery.ui.dialog.prototype, {
				_title: function( title ) {
					var $dialogContent = this.element,
						$tabMenu       = $dialogContent.find( '.fusion-builder-modal-top-container' ),
						$titleBar      = title.closest( '.ui-dialog-titlebar' );

					$dialogContent.before( $tabMenu );

					if ( $titleBar.parent( '.fusion-builder-child-element' ).length ) {
						$titleBar.find( '.ui-dialog-title' ).before( '<span class="ui-dialog-close fusion-back-menu-item"><svg version="1.1" width="18" height="18" viewBox="0 0 32 32"><path d="M12.586 27.414l-10-10c-0.781-0.781-0.781-2.047 0-2.828l10-10c0.781-0.781 2.047-0.781 2.828 0s0.781 2.047 0 2.828l-6.586 6.586h19.172c1.105 0 2 0.895 2 2s-0.895 2-2 2h-19.172l6.586 6.586c0.39 0.39 0.586 0.902 0.586 1.414s-0.195 1.024-0.586 1.414c-0.781 0.781-2.047 0.781-2.828 0z"></path></svg></span>' );
					} else if ( 'undefined' !== typeof this.options.type ) {
						$titleBar.find( '.ui-dialog-titlebar-close' ).before( '<div class="fusion-utility-menu-wrap"><span class="fusion-utility-menu fusiona-ellipsis"></span></div>' );
					}

					if ( ! this.options.title ) {
						title.html( '&#160;' );
					} else {
						title.text( this.options.title );
					}
				},
				_hide: function( event ) {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'fusion-dialog-ui-active' );

					this._trigger( 'close', event );
				}
			} ) );
		},

		/**
		 * Resizes dialogs.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		resizeDialog: function() {
			var titleBar = jQuery( '.fusion-builder-large-library-dialog .ui-dialog-titlebar' ),
				titleBarHeight = titleBar.length ? titleBar.height() : 0;

			this.dialogWidth  = 0.85 * jQuery( window ).width(),
			this.dialogHeight = ( 0.9 * ( jQuery( window ).height() - 54 ) ) - titleBarHeight;

			jQuery( '.fusion_builder_modal_settings:ui-dialog, #fusion-builder-front-end-library:ui-dialog, .fusion-builder-keyboard-shortcuts-dialog .ui-dialog-content:ui-dialog, .fusion-builder-preferences-dialog .ui-dialog-content:ui-dialog' ).dialog( 'option', 'width', this.dialogWidth );
			jQuery( '.fusion_builder_modal_settings:ui-dialog, #fusion-builder-front-end-library:ui-dialog, .fusion-builder-keyboard-shortcuts-dialog .ui-dialog-content:ui-dialog, .fusion-builder-preferences-dialog .ui-dialog-content:ui-dialog' ).dialog( 'option', 'height', this.dialogHeight );
		},

		/**
		 * Sets the dialog data from browser if it exists.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		setDialogData: function() {
			if ( 'undefined' !== typeof Storage && 'undefined' !== localStorage.getItem( 'dialogData' ) && localStorage.getItem( 'dialogData' ) ) {
				this.dialogData        = JSON.parse( localStorage.getItem( 'dialogData' ) );
				this.dialogData.of     = window;
				this.dialogData.width  = this.dialogData.width > jQuery( window ).width() ? jQuery( window ).width() : this.dialogData.width;
				this.dialogData.height = this.dialogData.height > jQuery( window ).height() ? jQuery( window ).height() : this.dialogData.height;
			} else {
				this.dialogData = {
					width: 450,
					height: 400,
					position: { my: 'right bottom', at: 'right-50 bottom-100', of: window }
				};
			}
		},

		/**
		 * Saves the position of a dialog.
		 *
		 * @since 2.0.0
		 * @param {Object} [offset] Contains the position left & top args.
		 * @return {void}
		 */
		saveDialogPosition: function( offset ) {
			this.dialogData.position = {
				my: 'left top',
				at: 'left+' + offset.left + ' top+' + offset.top + ''
			};
			this.storeDialogData();
		},

		/**
		 * Saves the dialog size.
		 *
		 * @since 2.0.0
		 * @param {Object} [size] Contains the width & height params.
		 * @return {void}
		 */
		saveDialogSize: function( size ) {
			this.dialogData.width  = size.width;
			this.dialogData.height = size.height;
			this.storeDialogData();
		},

		/**
		 * Checks if dialog is positioned out of viewport.
		 *
		 * @since 2.0.0
		 * @param {Object} [offset] Contains the position left & top args.
		 * @return {boolean}
		 */
		maybeRepositionDialog: function( $dialog ) {

			if ( jQuery( window ).width() < $dialog.offset().left + $dialog.width() ) {
				jQuery( $dialog ).position( {
					my: 'center',
					at: 'center',
					of: window
				} );

				return true;
			}

			return false;
		},

		/**
		 * Stored dialog data in browser.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		storeDialogData: function() {
			var saveData = jQuery.extend( true, {}, this.dialogData );

			delete saveData.of;
			delete saveData.position.of;

			if ( 'undefined' !== typeof Storage ) {
				localStorage.setItem( 'dialogData', JSON.stringify( saveData ) );
			}
		},

		/**
		 * Handle tabs in dialogs.
		 *
		 * @since 2.0.0
		 * @param {Object} [thisEl] The element.
		 * @return {void}
		 */
		dialogTabs: function( thisEl ) {
			thisEl.find( '.fusion-tabs-menu a' ).on( 'click', function( event ) {

				var target = jQuery( this ).attr( 'href' ) + '.fusion-tab-content';

				jQuery( this ).parent( 'li' ).siblings().removeClass( 'current' );
				jQuery( this ).parent( 'li' ).addClass( 'current' );
				event.preventDefault();

				thisEl.find( '.fusion-tab-content' ).hide().removeClass( 'active' );
				thisEl.find( target ).show().addClass( 'active' );

				if ( jQuery( '.fusion-builder-modal-top-container' ).find( '.fusion-elements-filter' ).length ) {
					setTimeout( function() {
						jQuery( '.fusion-builder-modal-top-container' ).find( '.fusion-elements-filter' ).focus();
					}, 50 );
				}

				FusionEvents.trigger( 'fusion-tab-changed' );

				if ( 0 < thisEl.closest( '.fusion-sidebar-section' ).length ) {
					jQuery( target ).closest( '.fusion-tabs' ).scrollTop( 0 );
				} else {
					thisEl.closest( '.ui-dialog-content' ).scrollTop( 0 );
				}
			} );

			thisEl.find( '.fusion-tabs-menu > li:first-child a' ).trigger( 'click' );
		},

		/**
		 * Adds classes necessary to prevent iframe from catching pointer events.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		addResizingClasses: function() {
			jQuery( 'body' ).addClass( 'fusion-preview-block fusion-dialog-resizing' );
		},

		/**
		 * Removes classes necessary to prevent iframe from catching pointer events.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		removeResizingClasses: function() {
			jQuery( 'body' ).removeClass( 'fusion-preview-block fusion-dialog-resizing' );
		},

		/**
		 * Adds modal hover event necessary to prevent iframe from catching pointer events.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		addResizingHoverEvent: function() {
			jQuery( '.ui-dialog .ui-resizable-handle' ).hover(
				function() {
					jQuery( 'body' ).addClass( 'fusion-preview-block' );
				}, function() {
					if ( ! jQuery( 'body' ).hasClass( 'fusion-dialog-resizing' ) ) {
						jQuery( 'body' ).removeClass( 'fusion-preview-block' );
					}
				}
			);
		}

	} );

}( jQuery ) );

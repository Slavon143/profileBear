/* global fusionAppConfig, FusionApp, FusionEvents, fusionBuilderText */
/* jshint -W024, -W117 */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionImportUpload = {

	optionImport: function( $element ) {
		var self = this,
			$import,
			$importMode,
			$codeArea,
			$demoImport,
			$poImport,
			$fileUpload,
			context,
			$importButton,
			$deleteButton;

		$element = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$import  = $element.find( '.fusion-builder-option.import' );

		if ( $import.length ) {
			$importMode   = $import.find( '#fusion-import-mode' );
			$codeArea     = $import.find( '#import-code-value' );
			$demoImport   = $import.find( '#fusion-demo-import' );
			$poImport     = $import.find( '#fusion-page-options-import' );
			$fileUpload   = $import.find( '.fusion-import-file-input' );
			$importButton = $import.find( '.fusion-builder-import-button' );
			$deleteButton = $import.find( '.fusion-builder-delete-button' );
			context       = $importButton.attr( 'data-context' );

			$importMode.on( 'change', function( event ) {
				event.preventDefault();
				$import.find( '.fusion-import-options > div' ).hide();
				$import.find( '.fusion-import-options > div[data-id="' + jQuery( event.target ).val() + '"]' ).show();
				$deleteButton.hide();

				if ( 'saved-page-options' === jQuery( event.target ).val() ) {
					$deleteButton.show();
				}
			} );

			$importButton.on( 'click', function( event ) {
				var uploadMode = $importMode.val();

				if ( event ) {
					event.preventDefault();
				}

				if ( 'paste' === uploadMode ) {
					$import.addClass( 'partial-refresh-active' );
					self.importCode( $codeArea.val(), context, $import );
				} else if ( 'demo' === uploadMode ) {
					$import.addClass( 'partial-refresh-active' );
					self.ajaxUrlImport( $demoImport.val(), $import );
				} else if ( 'saved-page-options' === uploadMode ) {
					$import.addClass( 'partial-refresh-active' );
					self.ajaxPOImport( $poImport.val(), $import );
				} else {
					$fileUpload.trigger( 'click' );
				}
			} );

			$deleteButton.on( 'click', function( event ) {

				if ( event ) {
					event.preventDefault();
				}

				if ( '' !== $poImport.val() ) {
					$import.addClass( 'partial-refresh-active' );
					self.ajaxPODelete( $poImport.val(), $import );
				}

			} );

			$fileUpload.on( 'change', function( event ) {
				self.prepareUpload( event, context, self );
			} );
		}
	},

	colorSchemeImport: function( $target, $option ) {
		var themeOptions,
			optionId = $option.length ? $option.attr( 'data-option-id' ) : false;

		if ( 'object' === typeof this.options[ optionId ] && 'object' === typeof this.options[ optionId ].choices[ $target.attr( 'data-value' ) ] ) {
			$option.addClass( 'partial-refresh-active' );
			themeOptions = jQuery.extend( true, {}, FusionApp.settings, this.options[ optionId ].choices[ $target.attr( 'data-value' ) ].settings );
			this.importCode( themeOptions, 'TO', $option, true, this.options[ optionId ].choices[ $target.attr( 'data-value' ) ].settings );
		}
	},

	importCode: function( code, context, $import, valid, scheme ) {
		var newOptions = code;

		context = 'undefined' === typeof context ? 'TO' : context;
		valid   = 'undefined' === typeof valid ? false : valid;
		scheme  = 'undefined' === typeof scheme ? false : scheme;

		if ( ! code || '' === code ) {
			$import.removeClass( 'partial-refresh-active' );
			return;
		}

		if ( ! valid ) {
			newOptions = JSON.parse( newOptions );
		}

		if ( 'TO' === context ) {
			FusionApp.settings    = newOptions;
			FusionApp.storedToCSS = {};
			FusionApp.contentChange( 'global', 'theme-option' );
			FusionEvents.trigger( 'fusion-to-changed' );
			FusionApp.sidebarView.clearInactiveTabs( 'to' );
			this.updateValues( scheme );
		} else {
			FusionPageBuilder.options.fusionExport.setFusionMeta( newOptions );
			FusionApp.storedPoCSS   = {};
			FusionApp.contentChange( 'page', 'page-option' );
			FusionEvents.trigger( 'fusion-po-changed' );
			FusionApp.sidebarView.clearInactiveTabs( 'po' );
		}

		$import.removeClass( 'partial-refresh-active' );
		FusionApp.fullRefresh();
	},

	ajaxUrlImport: function( toUrl, $import ) {
		var self = this;

		jQuery.ajax( {
			type: 'POST',
			url: fusionAppConfig.ajaxurl,
			dataType: 'JSON',
			data: {
				action: 'fusion_panel_import',
				fusion_load_nonce: fusionAppConfig.fusion_load_nonce, // eslint-disable-line camelcase
				toUrl: toUrl
			}
		} )
			.done( function( response ) {
				self.importCode( response, 'TO', $import );
			} )
			.fail( function() {
				$import.removeClass( 'partial-refresh-active' );
			} );
	},

	ajaxPOImport: function( poID, $import ) {
		var self = this,
			data = {
				action: 'fusion_page_options_import_saved',
				fusion_po_nonce: jQuery( '#fusion-page-options-nonce' ).val(),
				post_id: FusionApp.data.postDetails.post_id,
				saved_po_dataset_id: poID
			};

		jQuery.get( {
			url: fusionAppConfig.ajaxurl,
			data: data,
			dataType: 'json'
		} )
			.done( function( response ) {
				self.importCode( JSON.stringify( response.custom_fields ), 'PO', $import );
			} )
			.fail( function() {
				$import.removeClass( 'partial-refresh-active' );
			} );
	},

	ajaxPODelete: function( poID, $import ) {
		var data = {
			action: 'fusion_page_options_delete',
			fusion_po_nonce: jQuery( '#fusion-page-options-nonce' ).val(),
			saved_po_dataset_id: poID
		};

		jQuery.get( {
			url: fusionAppConfig.ajaxurl,
			data: data
		} )
			.done( function() {
				$import.find( '.fusion-select-label[data-value="' +  poID + '"]' ).closest( '.fusion-select-label' ).remove();
				$import.find( '.fusion-select-preview' ).html( '' );
				$import.removeClass( 'partial-refresh-active' );

				jQuery.each( FusionApp.data.savedPageOptions, function( index, value )  {
					if ( poID === value.id ) {
						delete FusionApp.data.savedPageOptions[ index ];
						return false;
					}
				} );
			} )
			.fail( function() {
				$import.removeClass( 'partial-refresh-active' );
			} );
	},

	updateValues: function( scheme ) {
		var self = this,
			options = 'undefined' === typeof scheme ? FusionApp.settings : scheme;

		_.each( options, function( value, id ) {
			self.updateValue( id, value );
		} );
	},

	updateValue: function( id, value ) {
		if ( 'primary_color' === id && this.$el.find( 'input[name="primary_color"]' ).length ) {
			this.$el.find( 'input[name="primary_color"]' ).val( value );
			this.$el.find( '[data-option-id="primary_color"] .wp-color-result' ).css( { backgroundColor: value } );
		}

		FusionApp.createMapObjects();
		this.updateSettingsToParams( id, value, true );
		this.updateSettingsToExtras( id, value, true );
		this.updateSettingsToPo( id, value );
	},

	prepareUpload: function( event, context, self ) {
		var file        = event.target.files,
			data        = new FormData(),
			$import     = jQuery( event.target ).closest( '.fusion-builder-option.import' ),
			invalidFile = false;

		$import.addClass( 'partial-refresh-active' );

		data.append( 'action', 'fusion_panel_import' );
		data.append( 'fusion_load_nonce', fusionAppConfig.fusion_load_nonce );

		jQuery.each( file, function( key, value ) {
			if ( 'json' !== value.name.substr( value.name.lastIndexOf( '.' ) + 1 ) ) {
				invalidFile = true;
			} else {
				data.append( 'po_file_upload', value );
			}
		} );

		if ( invalidFile ) {
			FusionApp.confirmationPopup( {
				title: fusionBuilderText.import_failed,
				content: fusionBuilderText.import_failed_description,
				actions: [
					{
						label: fusionBuilderText.ok,
						classes: 'yes',
						callback: function() {
							FusionApp.confirmationPopup( {
								action: 'hide'
							} );
						}
					}
				]
			} );
			$import.removeClass( 'partial-refresh-active' );
			return;
		}

		jQuery.ajax( {
			url: fusionAppConfig.ajaxurl,
			type: 'POST',
			data: data,
			cache: false,
			dataType: 'json',
			processData: false, // Don't process the files
			contentType: false // Set content type to false as jQuery will tell the server its a query string request
		} )
			.done( function( response ) {
				self.importCode( response, context, $import );
			} );
	}
};

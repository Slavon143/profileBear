/* global FusionApp, fusionAppConfig */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionExport = {

	optionExport: function( $element ) {
		var self = this,
			$export,
			$exportMode,
			$fileDownload,
			$copyButton,
			$saveButton;

		$element = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$export  = $element.find( '.fusion-builder-option.export' );

		if ( $export.length ) {
			$exportMode   = $export.find( '#fusion-export-mode' );
			$fileDownload = $export.find( '#fusion-export-file' );
			$copyButton   = $export.find( '#fusion-export-copy' );
			$saveButton   = $export.find( '#fusion-page-options-save' );

			$exportMode.on( 'change', function( event ) {
				event.preventDefault();
				$export.find( '.fusion-export-options > div' ).hide();
				$export.find( '.fusion-export-options > div[data-id="' + jQuery( event.target ).val() + '"]' ).show();
			} );

			$copyButton.on( 'click', function( event ) {
				event.preventDefault();
				jQuery( event.target ).prev( 'textarea' )[ 0 ].select();
				document.execCommand( 'copy' );
			} );

			$fileDownload.on( 'click', function( event ) {
				event.preventDefault();
				self.exportOptions( event );
			} );

			$saveButton.on( 'click', function( event ) {
				if ( event ) {
					event.preventDefault();
				}

				if ( '' !== jQuery( '#fusion-new-page-options-name' ).val() ) {
					$export.addClass( 'partial-refresh-active' );
					self.ajaxPOSave( $export );
				}
			} );
		}
	},

	updateExportCode: function() {
		var $textArea = this.$el.find( '.fusion-builder-option.export #export-code-value' ),
			context   = $textArea.attr( 'data-context' ),
			data      = 'TO' === context ? JSON.stringify( FusionApp.settings ) : JSON.stringify( this.getFusionMeta() );

		$textArea.val( data );
	},

	exportOptions: function( event ) {
		var dataStr,
			dlAnchorElem,
			context = jQuery( event.target ).attr( 'data-context' ),
			data,
			today    = new Date(),
			date     = today.getFullYear() + '-' + ( today.getMonth() + 1 ) + '-' + today.getDate(),
			fileName = 'fusion-theme-options-' + date;

		if ( 'TO' === context || 'FBE' === context ) {
			data = FusionApp.settings;

			// So import on back-end works.
			data.fusionredux_import_export = '';
			data[ 'fusionredux-backup' ]     = 1;
		} else {
			data     = this.getFusionMeta();
			fileName = 'avada-page-options-' + date;
		}

		dataStr = 'data:text/json;charset=utf-8,' + encodeURIComponent( JSON.stringify( data ) );

		dlAnchorElem = document.createElement( 'a' );
		dlAnchorElem.setAttribute( 'href', dataStr );
		dlAnchorElem.setAttribute( 'download', fileName + '.json' );
		dlAnchorElem.click();
		dlAnchorElem.remove();
	},

	ajaxPOSave: function( $export ) {
		var data = {
			action: 'fusion_page_options_save',
			fusion_po_nonce: jQuery( '#fusion-page-options-nonce' ).val(),
			post_id: FusionApp.data.postDetails.post_id,
			custom_fields: this.getFusionMeta(),
			options_title: jQuery( '#fusion-new-page-options-name' ).val()
		};

		jQuery.get( {
			url: fusionAppConfig.ajaxurl,
			data: data,
			dataType: 'json'
		} )
			.done( function( response ) {
				jQuery( '.fusion-select-options' ).append( '<label class="fusion-select-label" data-value="' + response.saved_po_dataset_id + '">' + response.saved_po_dataset_title  + '</label>' );
				jQuery( '#fusion-new-page-options-name' ).val( '' );
				$export.removeClass( 'partial-refresh-active' );

				// This is temp ID, not used anywhere really.
				FusionApp.data.savedPageOptions[ response.saved_po_dataset_id ] = {
					id: response.saved_po_dataset_id,
					title: response.saved_po_dataset_title,
					data: response.saved_po_data
				};
			} )
			.fail( function() {
				$export.removeClass( 'partial-refresh-active' );
			} );
	},

	getFusionMeta: function() {
		return {
			_fusion: FusionApp.data.postMeta._fusion
		};
	},

	setFusionMeta: function( newMeta ) {

		jQuery.each( newMeta, function( key, value ) {
			FusionApp.data.postMeta[ key ] = value;
		} );

	}
};

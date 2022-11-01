/* global FusionEvents, FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionImageFocusPoint = {
	optionFocusImage: function( el ) {
		var points = el.find( '.fusion-image-focus-point' );
		var model = this.model;

		points.each( function() {
			var point 	= jQuery( this ).find( '.point' );
			var field 	= jQuery( this ).find( 'input.fusion-builder-focus-point-field' );
			var preview = jQuery( this ).find( '.preview' );
			var previewImg = preview.find( '.image' );
			var placeHolder = preview.find( '.no-image-holder' );
			var paramName	= previewImg.data( 'image' );
			var image 	= el.find( `[data-option-id="${paramName}"]` ).find( '.fusion-builder-upload-preview img' );
			var imageValue = model.attributes.params[ paramName ];
			var dynamicData = model.attributes.params.dynamic_params;
			var lazy = jQuery( this ).data( 'lazy' );

			if ( dynamicData ) {
				dynamicData = FusionPageBuilderApp.base64Decode( dynamicData );
			}
			if ( dynamicData && '' !== dynamicData[ paramName ] ) {
				imageValue = false;
			}
			if ( imageValue ) {
				placeHolder.hide();
				previewImg.show();
				previewImg.append( image.clone() );
			} else {
				previewImg.hide();
				placeHolder.show();
			}
			FusionEvents.on( 'awb-image-upload-url-' + paramName, function( url ) {
				if ( url ) {
					image 	= '<img src="' + url + '" alt="">';
					previewImg.find( 'img' ).remove();
					previewImg.append( image );
					previewImg.show();
					placeHolder.hide();
				} else {
					previewImg.find( 'img' ).remove();
					previewImg.hide();
					placeHolder.show();
				}
			} );

			point.draggable( {
				containment: 'parent',
				scroll: false,
				snap: '.position-point',
				snapMode: 'inner',
				snapTolerance: 10,
				drag: function () {
					var top = parseInt( 100 * parseFloat( jQuery( this ).css( 'top' ) ) / parseFloat( jQuery( this ).parent().css( 'height' ) ) );
					var left = parseInt( 100 * parseFloat( jQuery( this ).css( 'left' ) ) / parseFloat( jQuery( this ).parent().css( 'width' ) ) );

					if ( !lazy ) {
						field.val( `${left}% ${top}%` ).trigger( 'change' );
					}

				},
				stop: function () {
					var top = parseInt( 100 * parseFloat( jQuery( this ).css( 'top' ) ) / parseFloat( jQuery( this ).parent().css( 'height' ) ) );
					var left = parseInt( 100 * parseFloat( jQuery( this ).css( 'left' ) ) / parseFloat( jQuery( this ).parent().css( 'width' ) ) );
					field.val( `${left}% ${top}%` ).trigger( 'change' );
				}
			} );

			const $defaultReset = point.closest( '.fusion-builder-option' ).find( '.fusion-builder-default-reset' );

			// Default reset icon, set value to empty.
			$defaultReset.on( 'click', function( event ) {
				var dataDefault,
					top = '50%',
					left = '50%';

				event.preventDefault();
				dataDefault = jQuery( this ).find( '.fusion-range-default' ).attr( 'data-default' ) || '';

				if ( dataDefault && 'string' === typeof dataDefault ) {
					top = dataDefault.split( ' ' )[ 1 ];
					left = dataDefault.split( ' ' )[ 0 ];
				}
				point.css( {
					top,
					left
				} );
				field.val( dataDefault ).trigger( 'change' );

			} );

			jQuery( '.position-point' ).on( 'click', function( event ) {
				var top = '50%',
					left = '50%';
				event.preventDefault();

				const $el = jQuery( this );
				if ( $el.hasClass( 'top-left' ) ) {
					top = 0;
					left = 0;
				}
				if ( $el.hasClass( 'top-center' ) ) {
					top = 0;
					left = '50%';
				}
				if ( $el.hasClass( 'top-right' ) ) {
					top = 0;
					left = '100%';
				}
				if ( $el.hasClass( 'center-left' ) ) {
					top = '50%';
					left = 0;
				}
				if ( $el.hasClass( 'center-center' ) ) {
					top = '50%';
					left = '50%';
				}
				if ( el.hasClass( 'center-right' ) ) {
					top = '50%';
					left = '100%';
				}
				if ( $el.hasClass( 'bottom-left' ) ) {
					top = '100%';
					left = 0;
				}
				if ( $el.hasClass( 'bottom-center' ) ) {
					top = '100%';
					left = '50%';
				}
				if ( $el.hasClass( 'bottom-right' ) ) {
					top = '100%';
					left = '100%';
				}
				point.css( {
					top,
					left
				} );
				field.val( `${left} ${top}` ).trigger( 'change' );

			} );
		} );


	}
};

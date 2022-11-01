( function( jQuery ) {

	'use strict';

	jQuery( document ).ready( function() {

		window.AwbTypographySet = function( el, parentScope ) {
			var self = this;
			this.$option     = jQuery( el );
			this.$el         = this.$option.find( '.awb-typography-sets-wrapper' );
			this.$list       = this.$el.find( '.awb-typography-sets' );
			this.parentScope = parentScope;
			this.location    = 'undefined' === typeof FusionApp ? 'redux' : 'live';

			if ( 'live' === this.location ) {
				this.optionId = 'undefined' === typeof this.$option.attr( 'data-option-id' ) ? this.$option.attr( 'data-id' ) : this.$option.attr( 'data-option-id' );
				this.options  = FusionApp.sidebarView.getFlatToObject();
				this.defaults = this.options[ this.optionId ].default;
			} else {
				this.optionId = this.$list.attr( 'data-option' );
			}
			this.typoSets = {};
			this.init();
		};

		AwbTypographySet.prototype.init = function() {
			var self   = this,
				values = this.getValues();

			this.generateSlug = this.generateSlug.bind( this );

			if ( 'object' === typeof values ) {
				_.each( values, function( data, key ) {
					self.createRow( key, data );
				} );
			}

			this.$el.on( 'click', '.fusiona-pen', this.toggleSet.bind( this ) );
			this.$el.on( 'click', '.fusiona-trash-o', this.removeSet.bind( this ) );
			this.$el.on( 'keyup', '[data-subset="label"]', this.bindLabel.bind( this ) );
			this.$el.on( 'change', '[data-subset]', this.inputChange.bind( this ) );
			this.$el.find( '.awb-typography-set-add-btn' ).on( 'click', this.addNewSet.bind( this ) );
		};

		AwbTypographySet.prototype.toggleSet = function( event ) {
			jQuery( event.target ).closest( '.awb-typo-set-item' ).find( '.awb-typo-set-content' ).slideToggle( 'fast' );
		};
		AwbTypographySet.prototype.bindLabel = function( event ) {
			var $input = jQuery( event.target ),
				value  = $input.val();

			if ( '' !== value ) {
				$input.closest( '.awb-typo-set-item' ).find( '.label' ).html( value );
			}
		};
		AwbTypographySet.prototype.inputChange = function( event ) {
			var $input     = jQuery( event.target ),
				value      = $input.val(),
				slug       = $input.closest( '.awb-typo-set-item' ).attr( 'data-slug' ),
				subset     = $input.attr( 'data-subset' );

			// If its variant updating, we want to update all 3 in the data.
			window.awbTypographySelect.updateData( slug, subset, value );
		};
		AwbTypographySet.prototype.getValues = function() {
			var values = {},
				source = 'redux' === this.location ? awbSetValue : FusionApp.settings[ this.optionId ];

			if ( 'string' === typeof source ) {
				values =  JSON.parse( source );
			} else if ( 'object' === typeof source ) {
				values = source;
			}

			if ( 'redux' === this.location ) {
				return values;
			}

			// Uses jQuery because it does deep merge.
			return jQuery.extend( true, this.defaults, values );
		};

		AwbTypographySet.prototype.addNewSet = function( event ) {
			var typoSet = FusionPageBuilder.template( jQuery( '#awb-typo-set-template' ).html() ),
				matches,
				customFontNumber = 1,
				label = window.awbTypographySelect.stringMap.new_font_name,
				$row,
				templateData,
				slug = this.generateSlug();

			// Change the number in the name.
			if ( /\d+$/.test( slug ) ) {
				matches = slug.match( /\d+$/ );
				customFontNumber = matches[0];
			}
			label = label.replace( '%s', customFontNumber );

			templateData = {
				data: {
					'label': label,
					'font-size' : '24px',
					'font-family' : '',
					'font-weight': '400',
					'font-style': '',
					'font-backup': '',
					'variant' : '400',
					'line-height': '1.1',
					'letter-spacing': '',
					'text-transform': 'none',
					'not_removable': false,
				},
				slug: slug,
				id: this.optionId + '[' + slug + ']'
			};

			event.preventDefault();

			this.$list.append( jQuery( typoSet( templateData ) ) );

			// Init row.
			$row = this.$list.find( '.awb-typo-set-item[data-slug="' + slug + '"]' );
			if ( $row.length ) {
				this.typoSets[ $row.attr( 'data-slug' ) ] = new AwbTypography( $row[ 0 ], this );
			}

			window.awbTypographySelect.updateData( slug, 'new', templateData.data );
			$row.find( '.fusiona-pen' ).trigger( 'click' );

			// On live-editor, a change event is needed to save the settings in the fusionApp.settings.
			this.$list.find( '.awb-typo-set-item[data-slug="' + slug + '"] input' ).trigger( 'change' );
		};

		AwbTypographySet.prototype.createRow = function( slug, data ) {
			var typoSet = FusionPageBuilder.template( jQuery( '#awb-typo-set-template' ).html() ),
				templateData = {
					data: data,
					slug: slug,
					id: this.optionId + '[' + slug + ']'
				},
				$row;

			this.$list.append( jQuery( typoSet( templateData ) ) );

			// Init row.
			$row = this.$list.find( '.awb-typo-set-item[data-slug="' + slug + '"]' );
			if ( $row.length ) {
				this.typoSets[ $row.attr( 'data-slug' ) ] = new AwbTypography( $row[ 0 ], this );
			}
		};

		AwbTypographySet.prototype.removeSet = function( event ) {
			var $itemWrapper = jQuery( event.target ).closest( '.awb-typography-sets' ),
				$item = jQuery( event.target ).closest( '.awb-typo-set-item' ),
				slug = $item.attr( 'data-slug' ),
				resultConfirm;

			resultConfirm = window.confirm( window.awbTypographySelect.stringMap.set_removed_alert ); // eslint-disable-line no-alert

			if ( ! resultConfirm ) {
				return;
			}

			$item.slideUp( 'fast', function() {
				jQuery( this ).remove()
			} );

			window.awbTypographySelect.removeData( slug );

			// In live-editor, also remove the settings from the settings array.
			// This is needed to save the settings.
			if ( 'live' === this.location ) {
				if ( FusionApp.settings[ this.optionId ][ slug ] ) {
					delete FusionApp.settings[ this.optionId ][ slug ];
				}
				// Make the save button available.
				$itemWrapper.find( 'input' ).first().trigger( 'change' );
			}
		}

		AwbTypographySet.prototype.generateSlug = function() {
			var setsSlugs = [],
			number,
			slugWithoutAppendedNumber,
			slug = "custom_typography_1";

			// Make an array with existing slugs.
			this.$el.find( '.awb-typo-set-item' ).each( function() {
				var itemSlug = jQuery( this ).attr( 'data-slug' );
				if ( itemSlug ) {
					setsSlugs.push( itemSlug );
				}
			} );

			// // Append a number to the end of the slug, if the slug already exists.
			if ( setsSlugs.includes( slug ) ) {
				number = 2;
				slugWithoutAppendedNumber = slug.replace( /_(\d+)$/, '' );

				while ( setsSlugs.includes( slugWithoutAppendedNumber + '_' + number ) ) {
					number++;
				}

				slug = slugWithoutAppendedNumber + '_' + number;
			}

			return slug;
		}

	} );
}( jQuery ) );

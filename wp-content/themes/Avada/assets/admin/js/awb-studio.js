/* global ajaxurl, awbStudioData, Fuse, fusionBuilderText */
window.awbStudio = {

	// Data of posts for thumbnails.
	data: {},

	// Current context of previews.
	context: {
		type: 'fusion_template',
		tag: 'all'
	},

	// Timeouts for filter reveal.
	timeouts: [],

	// Keys of avada_media properties which are not empty (those will be imported).
	mediaImportKeys: [],

	$disablePreview: jQuery( '.awb-modal-overlay' ),

	$modal: jQuery( '.awb-admin-modal-wrap' ),

	$modalMessage: jQuery( '.awb-admin-modal-wrap .awb-admin-modal-status-bar .awb-admin-modal-status-bar-label' ),

	$modalProgressBar: jQuery( '.awb-admin-modal-wrap .awb-admin-modal-status-bar .awb-admin-modal-status-bar-progress-bar' ),

	/**
	 * Run actions on load.
	 *
	 * @since 7.5
	 *
	 * @return {void}
	 */
	init: function() {
		this.$el  = jQuery( '.avada_page_avada-studio' );
		this.data = awbStudioData;

		// Listeners for events.
		this.addListeners();

		// Lazy load from selector, could just use theme lazy load.
		this.initLazyLoad();

		// Icon bar filters.
		this.initIconBar();

		// Tag filters
		this.initTagFilter();

		// Preview and save.
		this.initPreviewListener();

		// Iframe load.
		this.initIframeListener();

		// Modal close.
		this.initModalEvents();

		// Init search.
		this.initSearch();
	},

	/**
	 * Iframe events.
	 *
	 * @since 7.5
	 * @return {void}
	 */
	initIframeListener: function() {
		this.$el.find( '.awb-studio-preview-frame' ).on( 'load', function() {

			// Trigger event for preview update.
			window.dispatchEvent( new Event( 'awb-studio-update-preview' ) );

			jQuery( '#fusion-loader' ).hide();

			jQuery( '.awb-import-studio-item-in-preview' ).off( 'click' );

			jQuery( '.awb-import-studio-item-in-preview' ).on( 'click', function( event ) {
				var dataID = jQuery( this ).data( 'id' );

				event.preventDefault();

				jQuery( '.fusion-studio-preview-back' ).trigger( 'click' );
				jQuery( '.awb-save[data-id="' + dataID + '"]' ).trigger( 'click' );
			} );
		} );
	},

	/**
	 * Modal events.
	 *
	 * @since 7.5
	 * @return {void}
	 */
	initModalEvents: function() {
		var self = this;

		this.$el.find( '.fusion-studio-preview-back, .post-modal-bg' ).on( 'click', function() {
			self.closeModal( self.$el );
		} );

		jQuery( 'body' ).on( 'keydown', function( event ) {
			if ( ( 27 === event.keyCode || '27' === event.keyCode ) && jQuery( 'body' ).hasClass( 'fusion-studio-preview-active' ) ) {
				self.closeModal( self.$el );
			}
			return true;
		} );
	},

	/**
	 * Closes Modal.
	 *
	 * @since 7.5
	 * @return {void}
	 */
	closeModal: function( element ) {
		element.find( '.awb-studio-modal' ).css( 'visibility', 'hidden' );
		element.find( '.awb-studio-modal' ).css( 'opacity',  '0' );
		jQuery( 'body' ).removeClass( 'fusion-studio-preview-active' );
	},

	/**
	 * Update the tag list for the current context.
	 *
	 * @since 3.1
	 * @return {void}
	 */
	tagsUpdate: function() {
		var $nav = this.$el.find( '#filter-bar nav' );

		// Clear out old tags.
		$nav.empty();

		// Early exit if context is not set.
		if ( 'undefined' === typeof this.data[ this.context.type ] ) {
			return;
		}

		// Add all link with new count.
		$nav.prepend( '<a href="#" class="active" data-tag="all">' + fusionBuilderText.all + ' <span>' + Object.keys( this.data[ this.context.type ] ).length + '</span></a>' );

		// Each tag of type add in.
		jQuery.each( this.data.studio_tags[ this.context.type ], function( index, tag ) {
			$nav.append( '<a href="#" data-tag="' + tag.slug + '">' + tag.name + '<span>' + tag.count + '</span></a>' );
		} );

		// Add click listener.
		this.initTagFilter();
	},

	/**
	 * Main category filtering.
	 *
	 * @since 3.1
	 * @return {void}
	 */
	initIconBar: function() {
		var self = this;
		this.$el.find( '.awb-studio-categories li' ).on( 'click', function( event ) {
			event.preventDefault();

			if ( self.context.type === jQuery( this ).data( 'type' ) ) {
				return;
			}

			// Update title.
			jQuery( '#filter-bar h2' ).text( jQuery( this ).attr( 'aria-label' ) );

			// Active styling.
			jQuery( '.awb-studio-categories li.active' ).removeClass( 'active' );
			jQuery( this ).addClass( 'active' );

			// Context change.
			self.context.type = jQuery( this ).data( 'type' );

			// TODO, potentially check if same filter is in the other category instead.
			self.context.tag = 'all';

			// Update tags for new category.
			self.tagsUpdate();

			// Update preview for new category and tag combination.
			self.previewsUpdate();
		} );
	},

	/**
	 * Click listener for tag links.
	 *
	 * @since 3.1
	 * @return {void}
	 */
	initTagFilter: function() {
		var self = this;

		this.$el.find( '#filter-bar nav a' ).on( 'click', function( event ) {
			event.preventDefault();

			if ( self.context.tag === jQuery( this ).data( 'tag' ) ) {
				return;
			}

			// Active styling.
			jQuery( '#filter-bar nav .active' ).removeClass( 'active' );
			jQuery( this ).addClass( 'active' );

			// Update context tag.
			self.context.tag = jQuery( this ).data( 'tag' );

			// Update preview for new tag.
			self.previewsUpdate();
		} );
	},

	/**
	 * Lazy load images.
	 *
	 * @since 3.1
	 * @return {void}
	 */
	initLazyLoad: function() {
		var imageObserver,
			$container  = this.$el.find( '#main-content .previews' ),
			$demoImages = $container.find( '.lazy-load' ),
			options     = {
				root: null,
				rootMargin: '0px',
				threshold: 0.1
			};

		// TODO, make this more efficient when re-init.
		if ( 'IntersectionObserver' in window ) {
			imageObserver = new IntersectionObserver( function( entries ) {
				jQuery.each( entries, function( key, entry ) {
					var $demo  = jQuery( entry.target ),
						$image = $demo.find( 'img' );

					if ( 'undefined' === typeof $image.data( 'src' ) || '' === $image.data( 'src' ) || 'undefined' === $image.data( 'src' ) ) {
						imageObserver.unobserve( entry.target );
						return;
					}

					if ( entry.isIntersecting ) {
						$image.attr( 'src', $image.data( 'src' ) );

						$image.imagesLoaded().done( function() {
							$demo.removeClass( 'lazy-load' ).addClass( 'lazy-loaded' );
							$image.attr( 'alt', $image.data( 'alt' ) );
						} );

						imageObserver.unobserve( entry.target );
					}
				} );
			}, options );

			$demoImages.each( function() {
				imageObserver.observe( this );
			} );
		}
	},

	/**
	* Get import options.
	*
	* @since 3.7
	* @return {object}
	*/
	getImportOptions: function() {
		var overWriteType    = jQuery( 'input[name="overwrite-type"]:checked' ).val(),
			shouldInvert     = jQuery( 'input[name="invert"]:checked' ).val(),
			imagesImport     = jQuery( 'input[name="images"]:checked' ).val(),
			options;

			options = {
				'overWriteType': 'undefined' !== typeof overWriteType ? overWriteType : 'replace-pos',
				'shouldInvert': 'undefined' !== typeof shouldInvert ? shouldInvert  : 'dont-invert',
				'imagesImport': 'undefined' !== typeof imagesImport ? imagesImport : 'do-import-images'
			};

			return options;
	},

	/**
	 * Click listener for opening previews.
	 *
	 * @since 3.1
	 * @return {void}
	 */
	initPreviewListener: function() {
		var self = this;

		// Remove any existing.
		self.$el.find( '.awb-studio-content article img, .awb-save' ).off( 'click' );

		// Studio content import.
		self.$el.find( '.awb-save' ).on( 'click', function( event ) {
			var $button        = jQuery( this ),
				dataType       = $button.closest( 'article' ).data( 'type' ),
				dataID         = $button.closest( 'article' ).data( 'id' ),
				importOptions  = self.getImportOptions( event ),
				dataStudioType = jQuery( '.awb-studio-categories li.active' ).data( 'type' );

			event.preventDefault();

			if ( $button.hasClass( 'disabled' ) || ! dataType || ! dataID ) {
				return;
			}

			$button.addClass( 'disabled progress' );

			// Filter out empty properties (now those are empty arrays).
			if ( 'object' === typeof self.data[ dataStudioType ][ 'item-' + dataID ].avada_media && 0 === self.mediaImportKeys.length ) {
				Object.keys( self.data[ dataStudioType ][ 'item-' + dataID ].avada_media ).forEach( function( key ) {
					// We expect and object.
					if ( 'object' === typeof self.data[ dataStudioType ][ 'item-' + dataID ].avada_media[ key ] &&  ! Array.isArray( self.data[ dataStudioType ][ 'item-' + dataID ].avada_media[ key ] ) ) {
						self.mediaImportKeys.push( key );
					}
				} );
			}

			// Open modal.
			self.openImportModal();

			self.$modalMessage.html( 'Importing Studio Content' );

			jQuery.ajax( {
				type: 'GET',
				url: ajaxurl,
				dataType: 'JSON',
				data: {
					action: 'awb_studio_import',
					overWriteType: importOptions.overWriteType,
					shouldInvert: importOptions.shouldInvert,
					imagesImport: importOptions.imagesImport,
					data: {
						dataType: dataType,
						dataID: dataID
					},
					awb_studio_nonce: jQuery( '#awb-studio-nonce' ).val()
				}
			} )
			.done( function( data ) {
				$button.trigger( 'blur' );

				$button.removeClass( 'disabled progress' );
				self.addTemporaryClass( $button, 'success' );

				if ( 0 < self.mediaImportKeys.length && ( 'undefined' === typeof data.was_imported || false === data.was_imported ) ) {
					self.importAvadaMedia( data, importOptions );
				} else {

					self.closeImportModal();
				}
			} )
			.fail( function() {

				self.$modalMessage.html( 'Importing Studio Content Failed' );

				$button.removeClass( 'disabled progress' );
				self.addTemporaryClass( $button, 'error' );
			} );

		} );

		// Add for each.
		self.$el.find( '.awb-studio-content article img' ).on( 'click', function( event ) {
			var $wrapper        = jQuery( event.currentTarget ).closest( 'article' ),
				dataID         = $wrapper.data( 'id' );

			event.preventDefault();

			jQuery( '.awb-studio-modal' ).css( 'visibility', 'visible' );
			jQuery( 'body' ).addClass( 'fusion-studio-preview-active' );
			jQuery( '.awb-studio-modal' ).animate( { opacity: 1 }, 250 );
			jQuery( '#fusion-loader' ).show();
			self.loadIframePreview( jQuery( this ).closest( 'article' ).attr( 'data-url' ) );
			self.setOptions( dataID );
		} );
	},

	/**
	 * Sets options.
	 *
	 * @since 7.7
	 * @return {void}
	 */
	setOptions: function( dataID ) {
		var $wrapper = jQuery( '.awb-studio-modal' ),
			options  = { // Object of option name and default value.
				'overwrite-type': 'replace-pos',
				'invert': 'dont-invert',
				'images': 'do-import-images'
			};

		jQuery( '.awb-import-studio-item-in-preview' ).data( 'id', dataID );

		jQuery.each( options, function( name, value ) {
			if ( ! $wrapper.find( 'input[name="' + name + '"]' ).is( ':checked' ) ) {
				jQuery( '#' +  value ).prop( 'checked', true );
			}
		} );
	},

	/**
	 * Import studio content assets.
	 *
	 * @since 3.1
	 * @return {void}
	 */
	importAvadaMedia: function( postData, importOptions ) {
		var self = this,
			mediaKeys = Object.keys( postData.avada_media ),
			progress = ( mediaKeys.length - self.mediaImportKeys.length + 1 ) / mediaKeys.length;

		self.$modalMessage.html( 'Importing Studio Media: ' + self.mediaImportKeys[ 0 ].replace( '_', ' ' ) );
		self.$modalProgressBar.css( 'width', ( 100 * progress ) + '%' );

		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			dataType: 'JSON',
			data: {
				action: 'awb_studio_admin_import_media',
				overWriteType: importOptions.overWriteType,
				shouldInvert: importOptions.shouldInvert,
				imagesImport: importOptions.imagesImport,
				data: {
					mediaImportKey: self.mediaImportKeys[ 0 ],
					postData: postData
				},
				awb_studio_nonce: jQuery( '#awb-studio-nonce' ).val()
			}
		} )
		.done( function( data ) {

			// Remove the media key which was just imported.
			self.mediaImportKeys.shift();

			if ( 0 < self.mediaImportKeys.length ) {
				self.importAvadaMedia( data, importOptions );
			} else {
				self.closeImportModal();
			}
		} )
		.fail( function() {
			self.$modalMessage.html( 'Failed Importing Studio Media: ' + self.mediaImportKeys[ 0 ] );
		} );
	},

	/**
	 * Opens import modal.
	 */
	openImportModal: function() {
		jQuery( 'body' ).addClass( 'fusion_builder_no_scroll' );
		this.$disablePreview.show();
		jQuery( '.awb-admin-modal-wrap' ).css( 'display', 'block' );
	},

	/**
	 * Closes import modal.
	 */
	closeImportModal: function() {
		this.$modal.find( '.awb-admin-modal-status-bar-label span' ).html( '' );
		jQuery( 'body' ).removeClass( 'fusion_builder_no_scroll' );
		this.$disablePreview.hide();
		this.$modal.css( 'display', 'none' );
	},

	/**
	 * Update the preview area.
	 *
	 * @since 3.1
	 * @return {void}
	 */
	previewsUpdate: function() {
		var self        = this,
			counter     = 1,
			postType    = 'fusion_tb_section',
			order       = [],
			markup      = '',
			mainTimeout = 0,
			postMatches = [];

		// Clear all timeouts to prevent animations still running.
		jQuery.each( this.timeouts, function( index, value ) {
			clearTimeout( value );
		} );

		// Hide all.
		jQuery( '.previews article' ).css( { display: 'none' } ).addClass( 'hidden' );

		// Post type for rest endpoint.
		if ( 'elements' === self.context.type || 'sections' === self.context.type || 'columns' === self.context.type || 'post_cards' === self.context.type ) {
			postType = 'fusion_element';
		} else if ( 'fusion_template' === self.context.type ) {
			postType = 'fusion_template';
		} else if ( 'icons' === self.context.type ) {
			postType = 'fusion_icons';
		} else if ( 'forms' === self.context.type ) {
			postType = 'fusion_form';
		} else if ( 'awb_off_canvas' === self.context.type ) {
			postType = 'awb_off_canvas';
		}

		// Get data of posts we need.
		jQuery.each( self.data[ self.context.type ], function( key, post ) {

			// Post is not within active tag then no need to show it.
			if ( 'all' !== self.context.tag && -1 === jQuery.inArray( self.context.tag, post.tags ) ) {
				return;
			}

			// We already have preview loaded, show it.  TODO, avoid searching DOM.
			if ( jQuery( 'article[data-id="' + post.ID + '"]' ).length ) {
				jQuery( 'article[data-id="' + post.ID + '"]' ).css( { display: 'inline-block' } );
			} else {

				// We need to create markup for preview.
				markup += '<article class="hidden" data-type="' + postType + '" data-id="' + post.ID + '" data-url="' + post.url + '">';
				if ( post.thumbnail ) {
					markup += '<div class="preview lazy-load"><img src="data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%27' + post.thumbnail.width + '%27%20height%3D%27' + post.thumbnail.height + '%27%20viewBox%3D%270%200%20' + post.thumbnail.width + '%20' + post.thumbnail.height + '%27%3E%3Crect%20width%3D%27' + post.thumbnail.width + '>%27%20height%3D%273' + post.thumbnail.height + '%27%20fill-opacity%3D%220%22%2F%3E%3C%2Fsvg%3E" alt="" width="' + post.thumbnail.width + '" height="' + post.thumbnail.height + '" data-src="' + post.thumbnail.url + '" data-alt="' + post.post_title + '"/></div>';
				}
				markup += '<div class="bar"><span class="fusion_module_title"><span class="awb-preview-title-text">' + post.post_title + '</span></span><span class="awb-studio-actions"><a href="#" data-id="' + post.ID + '" class="awb-save"><i class="fusiona-plus"></i></a></span></div></article>';
			}

			postMatches.push( post );
		} );

		// Add all needing added.
		if ( '' !== markup ) {
			mainTimeout = 50;
			jQuery( '.previews' ).append( markup );
		}

		// Give delay for paint.
		setTimeout( function() {
			var i;

			// Loop afer all have been added to get position.
			jQuery.each( postMatches, function( key, post ) {
				var position = jQuery( 'article[data-id="' + post.ID + '"]' ).position();

				position.id = post.ID;
				order.push( position );
			} );

			// Sort top to bottom.
			order.sort( self.SortByTop );

			// Reveal top to bottom.
			for ( i = 0;  i < order.length; i++ ) {
				self.timeouts.push(
					self.doSetTimeout( i, order, counter )
				);
				counter++;
			}

			// Reinit click listeners.
			self.initPreviewListener();

			// Lazy load of any new images.
			self.initLazyLoad();

		}, mainTimeout );
	},

	/**
	 * Delay between showing items.
	 *
	 * @since 7.5
	 *
	 * @return {void}
	 */
	doSetTimeout: function( i, order, counter ) {
		setTimeout( function() {
			jQuery( 'article[data-id="' + order[ i ].id + '"]' ).removeClass( 'hidden' );
		}, counter * 50 );
	},

	/**
	 * Sort elements by vertical position.
	 *
	 * @since 7.5
	 *
	 */
	SortByTop: function( a, b ) {
		return ( ( a.top < b.top ) ? -1 : ( ( a.top > b.top ) ? 1 : 0 ) ); // eslint-disable-line no-nested-ternary
    },

	/**
	 * Update data and preview.
	 *
	 * @since 7.5
	 *
	 * @param data {object}
	 * @return {void}
	 */
	updateData: function( data ) {
		if ( 'object' !== typeof data ) {
			return;
		}

		this.data         = data;
		this.context.type = '';

		jQuery( '#filter-bar nav' ).empty();
		jQuery( '#main-content .previews' ).empty();
		jQuery( '.awb-studio-categories li.active' ).click();

		jQuery( 'html, body' ).animate( {
			scrollTop: jQuery( '.awb-studio-categories' ).offset().top
		}, 1000 );
	},

	/**
	 * Add needed listeners.
	 *
	 * @since 7.5
	 *
	 * @return {void}
	 */
	addListeners: function() {
		var self = this;

		// Listen for syn button clicks.
		this.$el.find( '.awb-studio-sync' ).on( 'click', function( event ) {
			var $button  = jQuery( this );

			event.preventDefault();

			if ( $button.hasClass( 'disabled' ) ) {
				return;
			}

			$button.addClass( 'disabled progress' );

			jQuery.ajax( {
				type: 'GET',
				url: ajaxurl,
				dataType: 'JSON',
				data: {
					action: 'awb_studio_sync',
					awb_studio_nonce: jQuery( '#awb-studio-nonce' ).val()
				}
			} )
			.done( function( data ) {
				if ( null === data ) {
					$button.removeClass( 'disabled progress' );
					self.addTemporaryClass( $button, 'error' );
					return;
				}
				$button.trigger( 'blur' );
				self.updateData( data );
				$button.removeClass( 'disabled progress' );
				self.addTemporaryClass( $button, 'success' );
			} )
			.fail( function() {
				$button.removeClass( 'disabled progress' );
				self.addTemporaryClass( $button, 'error' );
			} );
		} );

	},

	loadIframePreview: function( url ) {
		this.$el.find( '.post-preview iframe' ).attr( 'src', url );
	},

	/**
	 * Add a class, wait and then remove.
	 *
	 * @since 7.5
	 *
	 * @return {void}
	 */
	addTemporaryClass: function( $element, classname ) {
		$element.addClass( classname );
		setTimeout( function() {
			$element.removeClass( classname );
		}, 2000 );
	},

	/**
	 * Init Search panel.
	 *
	 * @since 7.5
	 * @return {void}
	 */
	initSearch: function() {
		var self = this,
			previewEl = jQuery( '.previews' ),
			options,
			fuse,
			result,
			value;

		jQuery( '#search-input' ).on( 'change paste keyup search', _.debounce( function() {
			var thisEl = jQuery( this ),
				data,
				hasValue = false;

			// Hide all.
			jQuery( 'article', previewEl ).css( { display: 'none' } ).addClass( 'hidden' );

			if ( thisEl.val() ) {
				value = thisEl.val().toLowerCase();

				options = {
					threshold: 0.2,
					location: 0,
					distance: 100,
					maxPatternLength: 32,
					minMatchCharLength: 3,
					keys: [ 'post_title' ]
				};

				data = _.map( self.data[ self.context.type ], function( post ) {
					return post;
				} );

				fuse = new Fuse( data, options );
				result = fuse.search( value );
				hasValue = true;
			} else {
				result = self.data[ self.context.type ];
			}

			// Show items.
			_.each( result, function( post ) {
				// Post is not within active tag then no need to show it.
				if ( ! hasValue && 'all' !== self.context.tag && -1 === jQuery.inArray( self.context.tag, post.tags ) ) {
					return;
				}
				previewEl.find( 'article[data-id="' + post.ID + '"]' ).css( { display: 'inline-block' } ).removeClass( 'hidden' );
			} );
		}, 100 ) );
	}
};

( function( jQuery ) {

	'use strict';

	jQuery( document ).ready( function() {
		window.awbStudio.init();

		// Modal.
		jQuery( '.awb-admin-modal-corner-close' ).on( 'click', function( e ) {
			e.preventDefault();

			window.awbStudio.closeImportModal();
		} );

	} );
}( jQuery ) );

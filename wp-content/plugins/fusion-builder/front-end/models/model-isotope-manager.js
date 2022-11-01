var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.IsotopeManager = Backbone.Model.extend( {
		defaults: {
			selector: '',
			container: '',
			itemSelector: '',
			layoutMode: 'packery',
			isOriginLeft: jQuery( 'body.rtl' ).length ? false : true,
			resizable: true,
			initLayout: true,
			view: false,
			sortBy: 'number',
			sortAscending: true
		},

		initialize: function() {
			this.listenTo( window.FusionEvents, 'fusion-frame-size-changed', this.updateLayout );
			this.listenTo( window.FusionEvents, 'fusion-column-resized', this.updateLayout );
		},

		init: function() {
			var self      = this,
				container = self.get( 'view' ).$el.find( self.get( 'selector' ) ),
				sortBy        = 'rand' === container.attr( 'data-order' ) ? 'random' : 'number',
				sortAscending = 'number' === sortBy && 'desc' === container.attr( 'data-order' ) ? true : false;

			self.set( 'container', container );

			if ( ! container.data( 'isotope' ) ) {
				container.isotope( {
					layoutMode: self.get( 'layoutMode' ),
					itemSelector: self.get( 'itemSelector' ),
					isOriginLeft: jQuery( 'body.rtl' ).length ? false : true,
					resizable: true,
					initLayout: true,
					sortBy: sortBy,
					sortAscending: sortAscending
				} );
			}
		},

		reInit: function( delay ) {
			var self = this;

			if ( 'undefined' === typeof delay ) {
				delay = 300;
			}

			self.destroyIsotope();

			setTimeout( function() {
				self.init();
			}, delay );
		},

		destroyIsotope: function() {
			if ( '' !== this.get( 'container' ) && this.get( 'container' ).data( 'isotope' ) ) {
				this.get( 'container' ).isotope( 'destroy' );
				this.get( 'container' ).removeData( 'isotope' );
			}
		},

		append: function( content ) {
			if ( '' !== this.get( 'container' ) && this.get( 'container' ).data( 'isotope' ) ) {
				this.get( 'container' ).isotope( 'appended', content ).isotope( 'layout' );
			}
		},

		remove: function( content ) {
			if ( '' !== this.get( 'container' ) && this.get( 'container' ).data( 'isotope' ) ) {
				this.get( 'container' ).isotope( 'remove', content ).isotope( 'layout' );
			}
		},

		reloadItems: function() {
			if ( '' !== this.get( 'container' ) && this.get( 'container' ).data( 'isotope' ) ) {
				this.get( 'container' ).isotope( 'reloadItems' ).isotope( 'layout' );
			}
		},

		updateLayout: function() {
			if ( '' !== this.get( 'container' ) && this.get( 'container' ).data( 'isotope' ) ) {
				this.get( 'container' ).isotope( 'layout' );
			}
		}

	} );
}( jQuery ) );

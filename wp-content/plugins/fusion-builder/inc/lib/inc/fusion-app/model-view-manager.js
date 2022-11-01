var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.ViewManager = Backbone.Model.extend( {
		defaults: {
			elementCount: 0,
			views: {}
		},

		getViews: function() {
			return this.get( 'views' );
		},

		getView: function( cid ) {
			return this.get( 'views' )[ cid ];
		},

		getChildViews: function( parentID ) {
			var views      = this.get( 'views' ),
				childViews = {};

			_.each( views, function( view, key ) {
				if ( parentID === view.model.attributes.parent ) {
					childViews[ key ] = view;
				}
			} );

			return childViews;
		},

		generateCid: function() {
			var elementCount = this.get( 'elementCount' ) + 1;
			this.set( { elementCount: elementCount } );

			return elementCount;
		},

		addView: function( cid, view ) {
			var views = this.get( 'views' );

			views[ cid ] = view;
			this.set( { views: views } );
		},

		removeView: function( cid ) {
			var views    = this.get( 'views' ),
				updatedViews = {};

			_.each( views, function( value, key ) {
				if ( key != cid ) { // jshint ignore: line
					updatedViews[ key ] = value;
				}
			} );

			this.set( { views: updatedViews } );
		},

		removeViews: function() {
			var updatedViews = {};
			this.set( { views: updatedViews } );
		},

		countElementsByType: function( elementType ) {
			var views = this.get( 'views' ),
				num   = 0;

			_.each( views, function( view ) {
				if ( view.model.attributes.element_type === elementType ) {
					num++;
				}
			} );

			return num;
		},

		clear: function() {
			var views = this.get( 'views' );

			_.each( views, function( view ) {
				view.unbind();
				view.remove();
				delete view.$el;
				delete view.el;
			} );

			this.set( 'elementCount', 0 );
			this.set( 'views', {} );
		}

	} );

}( jQuery ) );

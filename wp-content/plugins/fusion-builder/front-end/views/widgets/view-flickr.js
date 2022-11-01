/* globals FusionEvents */

var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	jQuery( document ).ready( function() {

		FusionPageBuilder.Fusion_Widget_Flickr = FusionPageBuilder.fusion_widget_content.extend( {

			onInit: function() {
				this._getFlickrContent = _.debounce( this.getFlickrContent, 500 );
				// NOTE: reset markup so DOM html is not used ( it requires js to be fired );
				this.model.attributes.markup = '';
			},

			/**
			 * Get Flickr images
			 *
			 * @since 6.0
			 * @param {Object} view
			 * @return {void}
			 */
			getFlickrContent: function ( view, userID, apiKey, perPage ) {

				$.ajax( {
					type: 'GET',
					url: 'https://api.flickr.com/services/rest/?format=json&method=flickr.photos.search&user_id=' + userID + '&api_key=' + apiKey + '&nojsoncallback=1&per_page=' + perPage + '&media=photos'
				} )
				.done( function( reponse ) {
					var images = '';

					if ( 'ok' != reponse.stat ) {
						// If this executes, something broke!
						return;
					}

					_.each( reponse.photos.photo, function( photo ) {
						var t_url, p_url;

						//notice that "t.jpg" is where you change the
						//size of the image
						t_url = '//farm' + photo.farm +
						'.static.flickr.com/' + photo.server + '/' +
						photo.id + '_' + photo.secret + '_s.jpg';

						p_url = '//www.flickr.com/photos/' +
						photo.owner + '/' + photo.id;

						images +=  '<div class="flickr_badge_image"><a href="' + p_url + '"><img alt="' +
						photo.title + '"src="' + t_url + '"/></a></div>';
					} );

					view.model.attributes.markup = images;

					view.render();
					FusionEvents.trigger( 'fusion-widget-rendered' );
				} );

			},

			getHTML: function( view ) {
				var self = this,
					params;

				params = view.model.get( 'params' );

				if ( params.fusion_widget_flickr__screen_name && params.fusion_widget_flickr__api && params.fusion_widget_flickr__number ) {
					self._getFlickrContent( view, params.fusion_widget_flickr__screen_name, params.fusion_widget_flickr__api, params.fusion_widget_flickr__number );
				}
			}

		} );

	} );

}( jQuery ) );

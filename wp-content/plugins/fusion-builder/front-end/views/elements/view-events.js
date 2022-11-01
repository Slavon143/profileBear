var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Events Element View.
		FusionPageBuilder.fusion_events = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				this._refreshJs();
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

				// Validate values.
				this.validateValues( atts.values );

				// Create attribute objects
				attributes.attr             = this.buildAttr( atts.values );
				attributes.attrEventsColumn = this.buildattrEventsColumn( atts.values );
				attributes.eventsList       = {};

				if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.posts ) {
					attributes.eventsList = this.buildEventsList( atts );
				}

				if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.max_num_pages && 'undefined' !== typeof atts.query_data.paged ) {
					attributes.paginationCode = this.buildPagination( atts );
				}

				// Any extras that need passed on.
				attributes.query_data     = atts.query_data;
				attributes.load_more_text = atts.extras.load_more_text;
				attributes.load_more      = atts.values.load_more && -1 != atts.values.posts_per_page;

				return attributes;
			},

			/**
			 * Modify values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {void}
			 */
			validateValues: function( values ) { // eslint-disable-line no-unused-vars

				values = _.fusionGetPadding( values ); // eslint-disable-line no-unused-vars
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-events-shortcode',
					style: ''
				} );

				if ( 'no' !== values.pagination ) {
					attr[ 'class' ] += ' fusion-events-pagination-' + values.pagination.replace( '_', '-' );
				}

				if ( '-1' !== values.column_spacing ) {
					attr.style += 'margin-left: -' + ( values.column_spacing / 2 ) + 'px;';
					attr.style += 'margin-right: -' + ( values.column_spacing / 2 ) + 'px;';
				}

				if ( values.content_alignment ) {
					attr[ 'class' ] += ' fusion-events-layout-' + values.content_alignment;
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				return attr;
			},

			/**
			 * Builds the pagination.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {string}
			 */
			buildPagination: function( atts ) {
				var globalPagination  = atts.extras.pagination_global,
					globalStartEndRange = atts.extras.pagination_start_end_range_global,
					range            = atts.extras.pagination_range_global,
					paged            = '',
					pages            = '',
					paginationCode   = '',
					queryData        = atts.query_data,
					values           = atts.values;

				if ( -1 == values.number_posts ) {
					values.pagination = 'no';
				}

				values.load_more = false;
				if ( 'no' !== values.pagination ) {
					if ( 'load_more_button' === values.pagination ) {
						values.load_more = true;
						values.pagination = 'infinite';
					}
				}

				if ( 'no' !== values.pagination ) {
					paged = queryData.paged;
					pages = queryData.max_num_pages;

					paginationCode = _.fusionPagination( pages, paged, range, values.pagination, globalPagination, globalStartEndRange );
				}

				return paginationCode;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildattrEventsColumn: function( values ) {
				var columnClass  = '',
					attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-layout-column',
						style: ''
					} );

				switch ( values.columns ) {
				case '1':
					columnClass = 'full-one';
					break;
				case '2':
					columnClass = 'one-half';
					break;
				case '3':
					columnClass = 'one-third';
					break;
				case '4':
					columnClass = 'one-fourth';
					break;
				case '5':
					columnClass = 'one-fifth';
					break;
				case '6':
					columnClass = 'one-sixth';
					break;
				}

				columnClass += ( '-1' !== values.column_spacing ) ? ' fusion-spacing-no' : ' fusion-spacing-yes';

				if ( '-1' !== values.column_spacing || -1 !== values.column_spacing ) {
					attr.style  += 'padding: ' + ( values.column_spacing / 2 ) + 'px;';
				}

				attr[ 'class' ] += ' fusion-' + columnClass;
				return attr;
			},

			/**
			 * Builds the events list HTML.
			 *
			 * @since 2.0
			 * @param {Object} atts - The values.
			 * @return {string}
			 */
			buildEventsList: function( atts ) {
				var html             = '',
					queryData        = atts.query_data,
					values           = atts.values,
					last             = false,
					lastClass        = '',
					stripHTML        = ( 'yes' === values.strip_html ),
					columns          = parseInt( values.columns, 10 ),
					i                = 1,
					attrEventsColumn = {},
					$this            = this;

				_.each( queryData.posts, function( post ) {

					attrEventsColumn = $this.buildattrEventsColumn( atts.values );

					if ( i === columns ) {
						last = true;
					}

					if ( i > columns ) {
						i    = 1;
						last = false;
					}

					if ( 1 === columns ) {
						last = true;
					}

					lastClass = last ? ' fusion-column-last' : '';

					if ( '' !== lastClass ) {
						attrEventsColumn[ 'class' ] += lastClass;
					}

					html += '<div ' + _.fusionGetAttributes( attrEventsColumn ) + '>';
					html += '<div class="fusion-column-wrapper">';

					html += '<div class="fusion-events-thumbnail hover-type-' + queryData.ec_hover_type + '">';
					html += '<a href="' + post.permalink + '" class="url" rel="bookmark" aria-label="' + post.title + '">';

					html += post.thumbnail;

					html += '</a>';
					html += '</div>';
					html += '<div class="fusion-events-content-wrapper" style="padding:' + values.padding + ';">';
					html += '<div class="fusion-events-meta">';
					html += '<h2><a href="' + post.permalink + '" class="url" rel="bookmark">' + post.title + '</a></h2>';
					html += '<h4>' + post.tribe_events_event_schedule_details + '</h4>';
					html += '</div>';

					if ( 'no_text' !== values.content_length ) {
						html += '<div class="fusion-events-content">';
						if ( 'excerpt' === values.content_length ) {
							html += _.fusionGetFixedContent( post.content, 'yes', values.excerpt_length, stripHTML );
						} else {
							html += _.fusionGetFixedContent( post.content, 'no' );
						}
						html += '</div>';
					}

					html += '</div>';
					html += '</div>';
					html += '</div>';

					if ( last ) {
						html += '<div class="fusion-clearfix"></div>';
						last = false;
					}
					i++;
				} );

				return html;
			}
		} );
	} );
}( jQuery ) );

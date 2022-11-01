/* global FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Blog Element View.
		FusionPageBuilder.fusion_blog = FusionPageBuilder.ElementView.extend( {

			/**
			* Are there any non landscape images, used for masonry layout.
			*/
			regularImagesFound: false,

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
				this.extras = atts.extras;

				// Create attribute objects.
				attributes.attr      = this.buildAttr( atts.values );
				attributes.styles    = this.buildStyles( atts.values );
				attributes.blogStyles = this.getStyleElement( atts.values );
				attributes.blogPosts = '';

				this.regularImagesFound = false;

				if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.posts ) {
					attributes.blogPosts = this.buildBlogPosts( atts );

					// Add class if regular size images were found.
					if ( true === this.regularImagesFound ) {
						attributes.attr[ 'class' ] += ' fusion-masonry-has-vertical';
					}
				}

				if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.max_num_pages ) {
					attributes.attrPostsContainer = this.buildPostsContainerAttr( atts );

					if ( 'undefined' !== typeof atts.query_data.paged ) {
						attributes.paginationCode = this.buildPagination( atts );
					}
				}

				attributes.load_more_text = atts.extras.load_more_text;
				attributes.load_more      = atts.values.load_more && -1 !== atts.values.number_posts;

				// Any extras that need passed on.
				attributes.cid        = this.model.get( 'cid' );
				attributes.values     = atts.values;
				attributes.query_data = atts.query_data;

				return attributes;
			},

			/**
			 * Modify values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.blog_grid_column_spacing = _.fusionValidateAttrValue( values.blog_grid_column_spacing, '' );
				values.scrolling = ( 'undefined' !== values.paging && 'no' == values.paging && 'pagination' === values.scrolling ) ? 'no' : values.scrolling;

				if ( -1 == values.number_posts ) {
					values.scrolling = 'no';
				}

				// Add hyphens for alternate layout options.
				if ( 'large alternate' === values.layout ) {
					values.layout = 'large-alternate';
				} else if ( 'medium alternate' === values.layout ) {
					values.layout = 'medium-alternate';
				}

				values.load_more = false;
				if ( 'no' !== values.scrolling ) {
					if ( 'load_more_button' === values.scrolling ) {
						values.load_more = true;
						values.scrolling = 'infinite';
					}
				}

				if ( 'undefined' !== typeof values.excerpt_length ) {
					values.excerpt_words = values.excerpt_length;
				}

				if ( '0' === values.blog_grid_column_spacing ) {
					values.blog_grid_column_spacing = '0.0';
				}

				if ( 'object' !== typeof values.blog_grid_padding ) {
					values.blog_grid_padding = {
						top: '',
						right: '',
						bottom: '',
						left: ''
					};
				}

				if ( 'undefined' !== typeof values.padding_top && '' !== values.padding_top ) {
					values.blog_grid_padding.top = values.padding_top;
				}
				if ( 'undefined' !== typeof values.padding_right && '' !== values.padding_right ) {
					values.blog_grid_padding.right = values.padding_right;
				}
				if ( 'undefined' !== typeof values.padding_bottom && '' !== values.padding_bottom ) {
					values.blog_grid_padding.bottom = values.padding_bottom;
				}
				if ( 'undefined' !== typeof values.padding_left && '' !== values.padding_left ) {
					values.blog_grid_padding.left = values.padding_left;
				}
				values.blog_grid_padding = [
					_.fusionGetValueWithUnit( values.blog_grid_padding.top ),
					_.fusionGetValueWithUnit( values.blog_grid_padding.right ),
					_.fusionGetValueWithUnit( values.blog_grid_padding.bottom ),
					_.fusionGetValueWithUnit( values.blog_grid_padding.left )
				];
				values.padding = 'object' === typeof values.blog_grid_padding ? values.blog_grid_padding.join( ' ' ) : values.blog_grid_padding;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr      = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-blog-shortcode fusion-blog-archive',
						style: ''
					} ),
					blogLayout  = '',
					cid         = this.model.get( 'cid' );

				// Set the correct layout class.
				blogLayout = 'fusion-blog-layout-' + values.layout;
				if ( 'timeline' === values.layout ) {
					blogLayout = 'fusion-blog-layout-timeline-wrapper';
				} else if ( 'grid' === values.layout || 'masonry' === values.layout ) {
					blogLayout = 'fusion-blog-layout-grid-wrapper';
				}

				if ( values.content_alignment && ( 'grid' === values.layout || 'masonry' === values.layout || 'timeline' === values.layout ) ) {
					attr[ 'class' ] += ' fusion-blog-layout-' + values.content_alignment;
				}

				attr[ 'class' ] += ' fusion-blog-shortcode-cid' + cid;
				attr[ 'class' ] += ' ' + blogLayout;
				attr[ 'class' ] += ' fusion-blog-' + values.scrolling;

				if ( 'yes' !== values.thumbnail ) {
					attr[ 'class' ] += ' fusion-blog-no-images';
				}

				if ( '0' == values.blog_grid_column_spacing || '0px' === values.blog_grid_column_spacing ) {
					attr[ 'class' ] += ' fusion-no-col-space';
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.margin_top ) {
					attr.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_right ) {
					attr.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( '' !== values.margin_bottom ) {
					attr.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( '' !== values.margin_left ) {
					attr.style += 'margin-left:' + values.margin_left + ';';
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				return attr;
			},

			/**
			 * Builds attributes for blog post container.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			buildPostsContainerAttr: function( atts ) {
				var attr        = {
						class: 'fusion-posts-container',
						style: ''
					},
					values          = atts.values,
					extras          = atts.extras,
					queryData       = atts.query_data,
					negativeMargin  = '';

				attr[ 'class' ] += ' fusion-posts-container-' + values.scrolling;

				if ( ! this.metaInfoCombined ) {
					attr[ 'class' ] += ' fusion-no-meta-info';
				}

				if ( values.load_more ) {
					attr[ 'class' ] += ' fusion-posts-container-load-more';
				}

				// Add class if rollover is enabled.
				if ( extras.image_rollover ) {
					attr[ 'class' ] += ' fusion-blog-rollover';
				}

				attr[ 'data-pages' ] = queryData.max_num_pages;

				if ( 'grid' === values.layout || 'masonry' === values.layout ) {
					attr[ 'class' ] += ' fusion-blog-layout-grid fusion-blog-layout-grid-' + values.blog_grid_columns + ' isotope';

					if ( 'masonry' === values.layout ) {
						attr[ 'class' ] += ' fusion-blog-layout-masonry';

						if ( queryData.regular_images_found ) {
							attr[ 'class' ] += ' fusion-blog-layout-masonry-has-vertical';
						}
					}

					if ( 'grid' === values.layout ) {
						if ( 'yes' === values.equal_heights ) {
							attr[ 'class' ] += ' fusion-blog-equal-heights';
						}
					}

					if ( 'undefined' !== typeof values.blog_grid_column_spacing || 0 === parseInt( values.blog_grid_column_spacing, 10 ) ) {
						attr[ 'data-grid-col-space' ] = values.blog_grid_column_spacing;
					}

					negativeMargin = ( -1 ) * parseFloat( values.blog_grid_column_spacing ) / 2;

					attr.style = 'margin: ' + negativeMargin + 'px ' + negativeMargin + 'px 0;height:500px;';
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
					values.scrolling = 'no';
				}

				if ( 'timeline' === values.layout ) {
					values.post_count = 1;
				}

				if ( 'no' !== values.scrolling ) {
					paged = queryData.paged;
					pages = queryData.max_num_pages;

					paginationCode = _.fusionPagination( pages, paged, range, values.scrolling, globalPagination, globalStartEndRange );
				}

				return paginationCode;
			},

			/**
			 * Builds the blog posts HTML.
			 *
			 * @since 2.0
			 * @param {Object} atts - The values.
			 * @return {string}
			 */
			buildBlogPosts: function( atts ) {
				var html                            = '',
					queryData                       = atts.query_data,
					values                          = atts.values,
					extras                          = atts.extras,
					imageSize                       = '',
					dateValues                      = {},
					postCount                       = 1,
					prevPostMonth                   = null,
					prevPostYear                    = null,
					timelineDate                    = '',
					blogShortcodeLoop               = {},
					timelineAlign                   = '',
					beforeLoopAction                = '',
					blogFusionPostWrapper           = {},
					color                           = '',
					colorCSS                        = '',
					metaInfoCombined                = '',
					isZeroExcerpt                   = '',
					metaAll                         = ( 'yes' === values.meta_all ),
					metaAuthor                      = ( 'yes' === values.meta_author ),
					metaCategories                  = ( 'yes' === values.meta_categories ),
					metaComments                    = ( 'yes' === values.meta_comments ),
					metaDate                        = ( 'yes' === values.meta_date ),
					metaLink                        = ( 'yes' === values.meta_link ),
					mataTags                        = ( 'yes' === values.meta_tags ),
					stripHTML                       = ( 'yes' === values.strip_html ),
					thumbnail                       = ( 'yes' === values.thumbnail ),
					showTitle                       = ( 'yes' === atts.params.show_title || 'yes' === atts.params.title ),
					titleLink                       = ( 'yes' === values.title_link ),
					metaInfoSettings                = {},
					preTitleContent                 = '',
					metaData                        = '',
					contentSep                      = '',
					dateAndFormat                   = '',
					formatClass                     = '',
					blogShortcodePostContentWrapper = {},
					link                            = '',
					linkTarget                      = '',
					linkIconTarget                  = '',
					postLinksTarget                 = '',
					blogShortcodePostTitle          = {},
					headerContent                   = '',
					content                         = '',
					readMoreContent                 = '',
					readMoreWrapperClass            = 'fusion-alignright',
					gridTimeLineContent             = '',
					readMoreLinkAttributes          = {},
					contentSepAttr                  = {},
					contentSepTypes                 = '',
					postTitleTag                    = '',
					timelineTitleTag                = '',
					self                            = this,
					isThereMetaAbove                = false,
					isThereMetaBelow                = false,
					isThereContent                  = false;

				// Initialize the time stamps for timeline month/year check.
				if ( 'timeline' === values.layout ) {
					postCount = 1;

					prevPostMonth     = null;
					prevPostYear      = null;
				}

				// Combine meta info into one variable.
				this.metaInfoCombined = metaAll * ( metaAuthor + metaDate + metaCategories + mataTags + metaComments + metaLink );
				metaInfoCombined      = this.metaInfoCombined;

				// Create boolean that holds info whether content should be excerpted.
				isZeroExcerpt = ( 'yes' === values.excerpt && 1 > values.excerpt_words ) ? 1 : 0;

				metaInfoSettings.post_meta                       = metaAll;
				metaInfoSettings.post_meta_author                = metaAuthor;
				metaInfoSettings.post_meta_date                  = metaDate;
				metaInfoSettings.post_meta_cats                  = metaCategories;
				metaInfoSettings.post_meta_tags                  = mataTags;
				metaInfoSettings.post_meta_comments              = metaComments;
				metaInfoSettings.disable_date_rich_snippet_pages = extras.disable_date_rich_snippet_pages;

				isThereMetaAbove = metaInfoCombined * ( metaAuthor + metaDate + metaCategories + mataTags );
				isThereMetaBelow = metaInfoCombined * ( metaComments || metaLink );
				isThereContent   = 'no' === values.excerpt || ( 'yes' === values.excerpt && ! isZeroExcerpt );

				_.each( queryData.posts, function( post ) {
					var footerContent = '',
						metaFooterContent,
						borderColor;

					readMoreContent = '';
					headerContent   = '';
					preTitleContent = '';

					// Work out correct image size.
					imageSize = 'blog-large';
					imageSize = ( ! FusionPageBuilderApp.$el.hasClass( 'has-sidebar' ) ) ? 'full' : 'blog-large';
					imageSize = ( 'medium' === values.layout || 'medium-alternate' === values.layout ) ? 'blog-medium' : imageSize;
					imageSize = ( 'undefined' !== typeof post.slideshow.featured_image_height && 'undefined' !== typeof post.slideshow.featured_image_width && '' !== post.slideshow.featured_image_height && '' !== post.slideshow.featured_image_width && 'auto' !== post.slideshow.featured_image_height && 'auto' !== post.slideshow.featured_image_width ) ? 'full' : imageSize;
					imageSize = ( 'auto' === post.slideshow.featured_image_height || 'auto' === post.slideshow.featured_image_width ) ? 'full' : imageSize;
					imageSize = ( 'grid' === values.layout || 'timeline' === values.layout ) ? 'full' : imageSize;

					post.slideshow.image_size = imageSize;

					if ( 'timeline' === values.layout ) {
						dateValues                 = {};
						dateValues.prev_post_month = prevPostMonth;
						dateValues.post_month      = post.post_month;
						dateValues.prev_post_year  = prevPostYear;
						dateValues.post_year       = post.post_year;
						timelineDate               = '';

						if ( dateValues.prev_post_month != dateValues.post_month || dateValues.prev_post_year != dateValues.post_year ) {

							if ( 1 < postCount ) {
								timelineDate = '</div>';
							}

							timelineTitleTag = self.getTitleTag( values, 'timeline_title' );
							timelineDate += '<' + timelineTitleTag + ' class="fusion-timeline-date" style="background-color:' + values.grid_element_color + ';">' + post.timeline_date_format + '</' + timelineTitleTag + '>';
							timelineDate += '<div class="fusion-collapse-month">';
						}

						html += timelineDate;
					}

					// BlogShortcodeLoop Attributes.
					blogShortcodeLoop       = {};
					blogShortcodeLoop.id    = 'post-' + post.id;
					blogShortcodeLoop[ 'class' ] = 'post fusion-post-' + values.layout;

					if ( 'masonry' === values.layout ) {

						if ( true !== post.slideshow.image_data.masonry_data.specific_element_orientation_class ) {
							post.slideshow.image_data.masonry_data.element_orientation_class = _.fusionGetElementOrientationClass( { imageWidth: post.slideshow.image_data.masonry_data.image_width, imageHeight: post.slideshow.image_data.masonry_data.image_height }, values.blog_masonry_grid_ratio, values.blog_masonry_width_double );
						}
						post.slideshow.image_data.masonry_data.element_base_padding = _.fusionGetElementBasePadding( post.slideshow.image_data.masonry_data.element_orientation_class );

						if ( 'fusion-element-landscape' !== post.slideshow.image_data.masonry_data.element_orientation_class ) {
							this.regularImagesFound = true;
						}

						// Additional grid class needed for masonry layout.
						blogShortcodeLoop[ 'class' ] += ' fusion-post-grid';

						blogShortcodeLoop[ 'class' ] += ' ' + post.slideshow.image_data.masonry_data.element_orientation_class;
					}

					// Set the correct column class for every post.
					if ( 'timeline' === values.layout ) {

						timelineAlign = ' fusion-right-column';
						if ( 0 < ( postCount % 2 ) ) {
							timelineAlign = ' fusion-left-column';
						}

						blogShortcodeLoop[ 'class' ] += ' fusion-clearfix' + timelineAlign;
						blogShortcodeLoop.style = 'border-color:' + values.grid_element_color + ';';
					}

					// Set the has-post-thumbnail if a video is used. This is needed if no featured image is present.
					if ( false !== post.post_video ) {
						blogShortcodeLoop[ 'class' ] += ' has-post-thumbnail';
					}

					if ( false !== post.post_class ) {
						blogShortcodeLoop[ 'class' ] += post.post_class;
					}

					beforeLoopAction = '<article ' + _.fusionGetAttributes( blogShortcodeLoop ) + '>\n';

					html += beforeLoopAction;

					blogFusionPostWrapper = {
						class: 'fusion-post-wrapper',
						style: ''
					};

					if ( 'masonry' === values.layout ) {
						color    = jQuery.AWB_Color( values.grid_box_color );
						colorCSS = color.toVarOrRgbaString();
						if ( 0 === color.alpha() ) {
							color = color.alpha( 1 );
							colorCSS = color.toVarOrRgbaString();
						}

						if ( 0 === color.alpha() || 'transparent' === values.grid_element_color ) {
							blogFusionPostWrapper[ 'class' ] += ' fusion-masonary-is-transparent ';
							blogFusionPostWrapper.style += 'border:none;';
						} else {
							blogFusionPostWrapper.style += 'border:1px solid ' + values.grid_element_color + ';border-bottom-width:3px;';
						}

						blogFusionPostWrapper.style += 'background-color:' + colorCSS + ';';
						blogFusionPostWrapper.style += 'border-color:' + values.grid_element_color + ';';

					} else if ( 'grid' === values.layout ) {
						color       = jQuery.AWB_Color( values.grid_box_color );
						colorCSS    = color.toVarOrRgbaString();
						borderColor = jQuery.AWB_Color( values.grid_element_color );

						if ( 0 === borderColor.alpha() || 'transparent' === values.grid_element_color ) {
							blogFusionPostWrapper.style += 'border:none;';
						} else {
							blogFusionPostWrapper.style += 'border:1px solid ' + values.grid_element_color + ';border-bottom-width:3px;';
						}

						blogFusionPostWrapper.style += 'background-color:' + colorCSS + ';';
						blogFusionPostWrapper.style += 'border-color:' + values.grid_element_color + ';';

					} else if ( 'timeline' === values.layout ) {
						color    = jQuery.AWB_Color( values.grid_box_color );
						colorCSS = color.toVarOrRgbaString();
						blogFusionPostWrapper.style = 'background-color:' + colorCSS + ';';
					}

					if ( 'grid' === values.layout || 'timeline' === values.layout || 'masonry' === values.layout ) {
						html += '<div ' + _.fusionGetAttributes( blogFusionPostWrapper ) + '>';
					}

					if ( thumbnail && 'medium-alternate' !== values.layout ) {
						if ( 'masonry' !== values.layout ) {
							preTitleContent = _.fusionGetBlogSlideshow( post.slideshow );
						} else {
							post.slideshow.image_data.layout = values.layout;
							post.slideshow.image_data.masonry_data.blog_grid_column_spacing = parseFloat( values.blog_grid_column_spacing );

							preTitleContent = _.fusionFeaturedImage( post.slideshow.image_data );
						}
					}

					if ( 'medium-alternate' === values.layout || 'large-alternate' === values.layout ) {
						preTitleContent += '<div class="fusion-date-and-formats">';

						dateAndFormat  = '<div class="fusion-date-box updated">';
						dateAndFormat += '<span class="fusion-date">' + post.alternate_date_format_day + '</span>';
						dateAndFormat += '<span class="fusion-month-year">' + post.alternate_date_format_month_year + '</span>';
						dateAndFormat += '</div>';

						switch ( post.format ) {
						case 'gallery':
							formatClass = 'images';
							break;
						case 'link':
							formatClass = 'link';
							break;
						case 'image':
							formatClass = 'image';
							break;
						case 'quote':
							formatClass = 'quotes-left';
							break;
						case 'video':
							formatClass = 'film';
							break;
						case 'audio':
							formatClass = 'headphones';
							break;
						case 'chat':
							formatClass = 'bubbles';
							break;
						default:
							formatClass = 'pen';
							break;
						}

						dateAndFormat += '<div class="fusion-format-box">';
						dateAndFormat += '<i class="awb-icon-' + formatClass + '" aria-hidden="true"></i>';
						dateAndFormat += '</div>';

						preTitleContent += dateAndFormat;
						preTitleContent += '</div>';

						if ( thumbnail && 'medium-alternate' === values.layout ) {
							preTitleContent += _.fusionGetBlogSlideshow( post.slideshow );
						}

						if ( metaAll ) {
							metaData = _.fusionRenderPostMetadata( 'alternate', metaInfoSettings, post.meta_data );
						}
					}

					if ( 'grid' === values.layout || 'timeline' === values.layout || 'masonry' === values.layout ) {

						if ( 'masonry' !== values.layout && ( ( showTitle && isThereMetaAbove && ( isThereContent || isThereMetaBelow ) ) || ( showTitle && ! isThereMetaAbove && isThereMetaBelow ) ) ) {
							contentSepAttr = {
								class: 'fusion-content-sep'
							};

							contentSepTypes = values.grid_separator_style_type;
							contentSepTypes = contentSepTypes.split( '|' );
							jQuery.each( contentSepTypes, function( index, type ) {
								contentSepAttr[ 'class' ] += ' sep-' + type;
							} );

							contentSepAttr.style = 'border-color:' + values.grid_separator_color;

							contentSep = '<div ' + _.fusionGetAttributes( contentSepAttr ) + '></div>';
						}

						if ( metaAll ) {
							metaData = _.fusionRenderPostMetadata( 'grid_timeline', metaInfoSettings, post.meta_data );
						}

						blogShortcodePostContentWrapper[ 'class' ] = 'fusion-post-content-wrapper';

						if ( 'masonry' === values.layout ) {
							blogShortcodePostContentWrapper.style  = 'background-color:inherit;';
							blogShortcodePostContentWrapper.style += 'padding: ' + values.padding + ';';
							if ( ! metaInfoCombined && isZeroExcerpt && ! showTitle ) {
								blogShortcodePostContentWrapper.style += ' display:none;';
							}
						} else if ( 'grid' === values.layout ) {
							blogShortcodePostContentWrapper.style  = 'background-color:inherit;';
							blogShortcodePostContentWrapper.style += 'padding: ' + values.padding + ';';

							if ( ! metaInfoCombined && ! showTitle && ( isZeroExcerpt || 'hide' === values.excerpt ) ) {
								blogShortcodePostContentWrapper.style += ' display:none;';
							}
						}

						preTitleContent += '<div ' + _.fusionGetAttributes( blogShortcodePostContentWrapper ) + '>';
					}

					preTitleContent += '<div class="fusion-post-content post-content">';

					if ( showTitle ) {
						if ( titleLink ) {
							linkIconTarget  = post.link_icon_target;
							postLinksTarget = post.post_links_target;

							if ( 'yes' === linkIconTarget || 'yes' === postLinksTarget ) {
								linkTarget = ' target="_blank" rel="noopener noreferrer"';
							}

							link = '<a href="' + post.permalink + '"' + linkTarget + '>' + post.title + '</a>';
						} else {
							link = post.title;
						}
					}

					if ( 'timeline' === values.layout ) {
						preTitleContent += '<div class="fusion-timeline-circle" style="background-color:' + values.grid_element_color + ';"></div>';
						preTitleContent += '<div class="fusion-timeline-arrow" style="color:' + values.grid_element_color + ';"></div>';
					}

					blogShortcodePostTitle[ 'class' ] = 'blog-shortcode-post-title';

					if ( extras.disable_date_rich_snippet_pages ) {
						blogShortcodePostTitle[ 'class' ] += ' entry-title';
					}

					postTitleTag = self.getTitleTag( values, 'post' );
					headerContent = preTitleContent + '<' + postTitleTag + ' ' + _.fusionGetAttributes( blogShortcodePostTitle ) + '>' + link + '</' + postTitleTag + '>' + metaData + contentSep;

					html += headerContent;

					if ( 'hide' !== values.excerpt ) {
						if ( post.content.has_custom_excerpt ) {
							content = _.fusionGetFixedContent( post.content, values.excerpt, Number.MAX_SAFE_INTEGER, stripHTML );
						} else {
							content = _.fusionGetFixedContent( post.content, values.excerpt, values.excerpt_words, stripHTML );
						}
					}
					html += '<div class="fusion-post-content-container">' + content + '</div>';

					if ( metaLink ) {
						if ( values.meta_read ) {

							readMoreWrapperClass = 'fusion-alignright';
							if ( 'grid' === values.layout || 'timeline' === values.layout || 'masonry' === values.layout ) {
								readMoreWrapperClass = 'fusion-alignleft';
							}

							linkIconTarget  = post.link_icon_target;
							postLinksTarget = post.post_links_target;

							if ( 'yes' === linkIconTarget || 'yes' === postLinksTarget ) {
								readMoreLinkAttributes.target = '_blank';
								readMoreLinkAttributes.rel    = 'noopener noreferrer';
							}

							readMoreLinkAttributes.href  = post.permalink;

							readMoreContent += '<div class="' + readMoreWrapperClass + '">';
							readMoreContent += '<a ' + _.fusionGetAttributes( readMoreLinkAttributes ) + '>';
							readMoreContent += '<span class="fusion-read-more">' + extras.read_more_text + '</span>';
							readMoreContent += '</a>';
							readMoreContent += '</div>';

							if ( 'large-alternate' === values.layout || 'medium-alternate' === values.layout ) {
								readMoreContent = '<div class="fusion-meta-info">' + readMoreContent + '</div>';
							}
						}
					}

					if ( metaComments ) {
						gridTimeLineContent = '<div class="fusion-alignright">' + post.timeline_comments + '</div>';
					}

					if ( 'grid' === values.layout || 'timeline' === values.layout || 'masonry' === values.layout ) {
						footerContent += '</div>';

						if ( 0 < metaInfoCombined ) {
							metaFooterContent  = readMoreContent;
							metaFooterContent += gridTimeLineContent;

							footerContent += '<div class="fusion-meta-info">' + metaFooterContent + '</div>';
						}
					}

					footerContent += '</div>';
					footerContent += '<div class="fusion-clearfix"></div>';

					if ( 0 < metaInfoCombined && -1 !== jQuery.inArray( values.layout, [ 'large', 'medium' ] ) ) {
						footerContent += '<div class="fusion-meta-info">' + _.fusionRenderPostMetadata( 'standard', metaInfoSettings, post.meta_data ) + readMoreContent + '</div>';
					}

					if ( metaAll && -1 !== jQuery.inArray( values.layout, [ 'large-alternate', 'medium-alternate' ] ) ) {
						footerContent += readMoreContent;
					}

					html += footerContent;

					if ( 'grid' === values.layout || 'timeline' === values.layout || 'masonry' === values.layout ) {
						html += '</div>\n';
						html += '</article>\n';
					} else {
						html += '</article>\n';
					}

					if ( 'timeline' === values.layout ) {
						prevPostMonth = post.post_month;
						prevPostYear  = post.post_year;
						postCount++;
						values.post_count++;
					}
				} );

				return html;
			},

			/**
			 * Get the title HTML tag.
			 *
			 * @param {Array} values
			 * @param {string} title Either 'post' or 'timeline_title'.
			 * @returns
			 */
			getTitleTag: function( values, title ) {
				var title_value;
				if ( 'post' === title ) {
					title_value = values.title_size;
					if ( ! title_value ) {
						return 'h2';
					}
				} else if ( 'timeline_title' === title ) {
					title_value = values.timeline_title_size;
					if ( ! title_value ) {
						return 'h3';
					}
				}

				if ( !isNaN( title_value ) && !isNaN( parseFloat( title_value ) ) ) {
					return 'h' + title_value;
				}

				return title_value;
			},

			/**
			 * Build the styles.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {string}
			 */
			buildStyles: function( values ) {
				var styles = '',
					cid = this.model.get( 'cid' );

				styles += '.fusion-blog-shortcode-cid' + cid + ' .fusion-blog-layout-grid .fusion-post-grid{padding:' + ( parseFloat( values.blog_grid_column_spacing ) / 2 ) + 'px;}';
				styles += '.fusion-blog-shortcode-cid' + cid + ' .fusion-posts-container{margin-left: -' + ( parseFloat( values.blog_grid_column_spacing ) / 2 ) + 'px !important; margin-right:-' + ( parseFloat( values.blog_grid_column_spacing ) / 2 ) + 'px !important;}';

				return styles;
			},

			/**
			 * Create the style HTML element.
			 *
			 * @since 3.5
			 * @param {Object} values - The values.
			 * @returns {string}
			 */
			getStyleElement: function( values ) {
			var style,
				baseSelector = '.fusion-blog-shortcode.fusion-blog-shortcode-cid' + this.model.get( 'cid' ),
				self = this,
				titleSelector         = baseSelector + ' .entry-title',
				timelineTitleSelector = baseSelector + ' .fusion-timeline-date';

			this.dynamic_css = {};
			this.values = values;

			jQuery.each( _.fusionGetFontStyle( 'title_font', values, 'object' ), function( rule, value ) {
				self.addCssProperty( titleSelector, rule, value, true );
			} );

			if ( ! _.isEmpty( values.title_font_size ) ) {
				this.addCssProperty( titleSelector, 'font-size', values.title_font_size, true );
			}

			if ( ! _.isEmpty( values.title_line_height ) ) {
				this.addCssProperty( titleSelector, 'line-height', values.title_line_height, true );
			}

			if ( ! _.isEmpty( values.title_letter_spacing ) ) {
				this.addCssProperty( titleSelector, 'letter-spacing', values.title_letter_spacing, true );
			}

			if ( ! _.isEmpty( values.title_text_transform ) ) {
				this.addCssProperty( titleSelector, 'text-transform', values.title_text_transform, true );
			}

			jQuery.each( _.fusionGetFontStyle( 'timeline_title_font', values, 'object' ), function( rule, value ) {
				self.addCssProperty( timelineTitleSelector, rule, value, true );
			} );

			if ( ! _.isEmpty( values.timeline_title_font_size ) ) {
				this.addCssProperty( timelineTitleSelector, 'font-size', values.timeline_title_font_size, true );
			}

			if ( ! _.isEmpty( values.timeline_title_line_height ) ) {
				this.addCssProperty( timelineTitleSelector, 'line-height', values.timeline_title_line_height, true );
			}

			if ( ! _.isEmpty( values.timeline_title_letter_spacing ) ) {
				this.addCssProperty( timelineTitleSelector, 'letter-spacing', values.timeline_title_letter_spacing, true );
			}

			if ( ! _.isEmpty( values.timeline_title_text_transform ) ) {
				this.addCssProperty( timelineTitleSelector, 'text-transform', values.timeline_title_text_transform, true );
			}

			style = this.parseCSS();

			return style ? '<style>' + style + '</style>' : '';
		}

		} );
	} );
}( jQuery ) );

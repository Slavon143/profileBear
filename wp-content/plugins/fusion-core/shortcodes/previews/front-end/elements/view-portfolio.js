/* global portfolioShortcode */
/* eslint no-unused-vars: 0 */
/* jshint -W024, -W098 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Portfolio View.
		FusionPageBuilder.fusion_portfolio = FusionPageBuilder.ElementView.extend( {

			columnsWords: [ 'one', 'one', 'two', 'three', 'four', 'five', 'six' ],

			regularImagesFound: false,

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0.0
			 * @returns null
			 */
			afterPatch: function() {
				this._refreshJs();
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0.0
			 * @returns null
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values, atts.extras );

				attributes.portfolioShortcode                 = {};
				attributes.portfolioShortcodePortfolioWrapper = {};
				attributes.portfolioShortcodeCarousel         = {};
				attributes.filters                            = '';
				attributes.portfolio_posts                    = false;
				attributes.pagination                         = '';
				attributes.alignPaddingStyle                  = '';
				attributes.columnSpacingStyle                 = '';
				attributes.query_data                         = atts.query_data;
				attributes.layout                             = atts.values.layout;
				attributes.show_nav                           = atts.values.show_nav;
				attributes.placeholder                        = false;

				this.regularImagesFound = false;

				// Create attribute objects.
				if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.portfolios ) {
					attributes.portfolioShortcode                 = this.buildPortfolioAttr( atts.values, atts.extras, atts.query_data );
					attributes.portfolioShortcodePortfolioWrapper = this.buildWrapperAttr( atts.values, atts.query_data );
					attributes.portfolioShortcodeCarousel         = this.buildCarouselAttr( atts.values );
					attributes.portfolio_posts                    = this.buildPortfolioPosts( atts.values, atts.extras, atts.query_data );

					if ( 'carousel' !== atts.values.layout ) {
						attributes.filters            = this.buildFilters( atts.values, atts.extras, atts.query_data );
						attributes.pagination         = this.buildPagination( atts.values, atts.extras, atts.query_data );
						attributes.alignPaddingStyle  = this.buildAlignPaddingStyle( atts.values );
						attributes.columnSpacingStyle = this.buildColumnSpacingStyle( atts.values );
					}

					// Add class if regular size images were found.
					if ( true === this.regularImagesFound ) {
						portfolioShortcode[ 'class' ] += ' fusion-masonry-has-vertical';
					}
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.placeholder ) {
					attributes.placeholder = atts.query_data.placeholder;
				}

				return attributes;
			},

			validateValues: function( values, extras ) {
				values.column_spacing = _.fusionValidateAttrValue( values.column_spacing, '' );
				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );
				if ( '0' === values.column_spacing ) {
					values.column_spacing = '0.0';
				}

				if ( '0' === values.offset ) {
					values.offset = '';
				}

				if ( 'grid' === values.layout && 'undefined' === typeof values.text_layout ) {
					values.boxed_text = 'no_text';
				}

				if ( 'grid-with-excerpts' === values.layout || 'grid-with-text' === values.layout ) {
					values.layout = 'grid';
				}

				if ( values.boxed_text && 'undefined' === typeof values.text_layout ) {
					values.text_layout = values.boxed_text;
				}

				if ( 'default' === values.text_layout ) {
					values.text_layout = extras.portfolio_text_layout;
				}

				if ( 'full-content' === values.content_length ) {
					values.content_length = 'full_content';
				}

				if ( 'default' === values.content_length ) {
					values.content_length = extras.portfolio_content_length.replace( / /g, '-' ).toLowerCase();
				}

				if ( 'default' === values.portfolio_title_display ) {
					values.portfolio_title_display = extras.portfolio_title_display;
				}

				if ( 'default' === values.portfolio_text_alignment ) {
					values.portfolio_text_alignment = extras.portfolio_text_alignment;
				}

				if ( 'default' === values.boxed_text ) {
					values.boxed_text = extras.portfolio_text_layout;
				}

				if ( 'default' === values.picture_size ) {
					if ( 'full' === extras.portfolio_featured_image_size ) {
						values.picture_size = 'auto';
					} else {
						values.picture_size = 'fixed';
					}
				}

				if ( 'default' === values.pagination_type ) {
					values.pagination_type = extras.grid_pagination_type;
				}

				if ( 'carousel' !== values.layout && 'no_text' !== values.text_layout && 'boxed' === values.text_layout ) {
					if ( ( 'undefined' === typeof values.padding_top || '' === values.padding_top ) && 'undefined' !== typeof extras.portfolio_layout_padding.top ) {
						values.padding_top = extras.portfolio_layout_padding.top;
					}

					if ( ( 'undefined' === typeof values.padding_right || '' === values.padding_right ) && 'undefined' !== typeof extras.portfolio_layout_padding.right ) {
						values.padding_right = extras.portfolio_layout_padding.right;
					}

					if ( ( 'undefined' === typeof values.padding_bottom || '' === values.padding_bottom ) && 'undefined' !== typeof extras.portfolio_layout_padding.bottom ) {
						values.padding_bottom = extras.portfolio_layout_padding.bottom;
					}

					if ( ( 'undefined' === typeof values.padding_left || '' === values.padding_left ) && 'undefined' !== typeof extras.portfolio_layout_padding.left ) {
						values.padding_left = extras.portfolio_layout_padding.left;
					}
				}
			},

			buildPortfolioAttr: function( values, extras, queryData ) {
				var portfolioShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-recent-works fusion-portfolio fusion-portfolio-cid' + this.model.get( 'cid' ) + ' fusion-portfolio-' + values.layout + ' fusion-portfolio-paging-' + values.pagination_type,
					style: ''
				} );

				portfolioShortcode = _.fusionAnimations( values, portfolioShortcode );

				portfolioShortcode.data_id = '-rw-' + this.model.get( 'cid' );

				// Add classes for carousel layout.
				if ( 'carousel' === values.layout ) {
					portfolioShortcode[ 'class' ] += ' recent-works-carousel portfolio-carousel';
					if ( 'auto' === values.picture_size ) {
						portfolioShortcode[ 'class' ] += ' picture-size-auto';
					}
				} else {

					// Add classes for grid layouts.
					portfolioShortcode[ 'class' ] += ' fusion-portfolio fusion-portfolio-' + this.columnsWords[ parseInt( values.columns, 10 ) ] + ' fusion-portfolio-' + values.text_layout;

					if ( ( 'grid' === values.layout || 'masonry' === values.layout ) && 'no_text' !== values.text_layout ) {
						portfolioShortcode[ 'class' ] += ' fusion-portfolio-text';

						if ( '1' === values.columns && 'floated' === values.one_column_text_position ) {
							portfolioShortcode[ 'class' ] += ' fusion-portfolio-text-floated';
						}

						if ( 'grid' === values.layout  ) {
							if ( 'yes' === values.equal_heights ) {
								portfolioShortcode[ 'class' ] += ' fusion-portfolio-equal-heights';
							}
						}
					}

					portfolioShortcode.data_columns = this.columnsWords[ parseInt( values.columns, 10 ) ];
				}

				// Add class for no spacing.
				if ( -1 !== jQuery.inArray( values.column_spacing, [ 0, '0', '0px' ] ) ) {
					portfolioShortcode[ 'class' ] += ' fusion-no-col-space';
				}

				// Add class if rollover is enabled.
				if ( extras.image_rollover ) {
					portfolioShortcode[ 'class' ] += ' fusion-portfolio-rollover';
				}

				// Add custom class.
				if ( '' !== values[ 'class' ] ) {
					portfolioShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				// Add custom id.
				if ( '' !== values.id ) {
					portfolioShortcode.id = values.id;
				}

				if ( '' !== values.margin_top ) {
					portfolioShortcode.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_right ) {
					portfolioShortcode.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( '' !== values.margin_bottom ) {
					portfolioShortcode.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( '' !== values.margin_left ) {
					portfolioShortcode.style += 'margin-left:' + values.margin_left + ';';
				}

				return portfolioShortcode;
			},

			buildWrapperAttr: function( values, queryData ) {
				var portfolioShortcodePortfolioWrapper = {
						class: 'fusion-portfolio-wrapper',
						id: 'fusion-portfolio-cid' + this.model.get( 'cid' ),
						data_picturesize: values.picture_size
					},
					margin;

				portfolioShortcodePortfolioWrapper.data_pages = queryData.number_of_pages;

				if ( '0' !== values.column_spacing && 0 !== values.column_spacing && '' !== values.column_spacing ) {
					margin = -1 * parseFloat( values.column_spacing ) / 2;
					portfolioShortcodePortfolioWrapper.style = 'margin:' + margin + 'px;';
				}

				return portfolioShortcodePortfolioWrapper;
			},

			buildCarouselAttr: function( values ) {
				var portfolioShortcodeCarousel = {
					class: 'fusion-carousel'
				};

				if ( 'title_below_image' === values.carousel_layout ) {
					portfolioShortcodeCarousel.data_metacontent = 'yes';
					portfolioShortcodeCarousel[ 'class' ]           += ' fusion-carousel-title-below-image';
				}

				if ( 'fixed' === values.picture_size ) {
					portfolioShortcodeCarousel[ 'class' ] += ' fusion-portfolio-carousel-fixed';
				}

				portfolioShortcodeCarousel[ 'data-autoplay' ]    = values.autoplay;
				portfolioShortcodeCarousel[ 'data-columns' ]     = values.columns;
				portfolioShortcodeCarousel[ 'data-itemmargin' ]  = values.column_spacing;
				portfolioShortcodeCarousel[ 'data-itemwidth' ]   = 180;
				portfolioShortcodeCarousel[ 'data-touchscroll' ] = values.mouse_scroll;
				portfolioShortcodeCarousel[ 'data-imagesize' ]   = values.picture_size;
				portfolioShortcodeCarousel[ 'data-scrollitems' ] = values.scroll_items;

				return portfolioShortcodeCarousel;
			},

			buildFilters: function( values, extras, queryData ) {
				var filterWrapper = '',
					filter        = '',
					catSlugs      = [],
					tagSlugs      = [],
					catsToExclude = [],
					tagsToExclude = [],
					firstFilter,
					portfolioShortcodeFilterLink;

				if ( 'tag' !== values.pull_by ) {
					if ( '' !== values.cat_slug ) {
						catSlugs = values.cat_slug.replace( /\s+/g, '' );
						catSlugs = catSlugs.split( ',' );
					}
				}

				if ( 'category' !== values.pull_by ) {
					if ( '' !== values.tag_slug ) {
						tagSlugs = values.tag_slug.replace( /\s+/g, '' );
						tagSlugs = tagSlugs.split( ',' );
					}
				}

				if ( 'tag' !== values.pull_by ) {
					if ( '' !== values.exclude_cats ) {
						catsToExclude = values.exclude_cats.replace( /\s+/g, '' );
						catsToExclude = catsToExclude.split( ',' );
					}
				}

				if ( 'category' !== values.pull_by ) {
					if ( '' !== values.exclude_tags ) {
						tagsToExclude = values.exclude_tags.replace( /\s+/g, '' );
						tagsToExclude = tagsToExclude.split( ',' );
					}
				}

				// Check if filters should be displayed.
				if ( queryData.portfolio_categories && 'no' !== values.filters ) {

					// portfolioShortcodeFilterLink Attributes.
					portfolioShortcodeFilterLink = {
						href: '#'
					};

					// Check if the "All" filter should be displayed.
					firstFilter = true;
					if ( 'yes-without-all' !== values.filters ) {
						filter      = '<li role="menuitem" class="fusion-filter fusion-filter-all fusion-active"><a ' + _.fusionGetAttributes( portfolioShortcodeFilterLink ) + ' data-filter="*">' + extras.all_text + '</a></li>';
						firstFilter = false;
					}

					// Loop through categories.
					_.each( queryData.portfolio_categories, function( portfolioCategory ) {
						var activeClass;

						// Only display filters of non excluded categories.
						if ( -1 === jQuery.inArray( portfolioCategory.slug, catsToExclude ) ) {

							// Check if categories have been chosen.
							if ( '' !== values.cat_slug ) {

								// Only display filters for explicitly included categories.
								if ( -1 !== jQuery( decodeURI( portfolioCategory.slug ), catSlugs ) ) {

									// Set the first category filter to active, if the all filter isn't shown.
									activeClass = '';
									if ( firstFilter ) {
										activeClass = ' fusion-active';
										firstFilter = false;
									}

									filter += '<li role="menuitem" class="fusion-filter fusion-hidden' + activeClass + '"><a ' + _.fusionGetAttributes( portfolioShortcodeFilterLink ) + ' data-filter=".' + decodeURI( portfolioCategory.slug ) + '">' + portfolioCategory.name + '</a></li>';
								}
							} else {

								// Display all categories.
								// Set the first category filter to active, if the all filter isn't shown.
								activeClass = '';
								if ( firstFilter ) {
									activeClass = ' fusion-active';
									firstFilter = false;
								}

								filter += '<li role="menuitem" class="fusion-filter fusion-hidden' + activeClass + '"><a ' + _.fusionGetAttributes( portfolioShortcodeFilterLink ) + ' data-filter=".' + decodeURI( portfolioCategory.slug ) + '">' + portfolioCategory.name + '</a></li>';
							}
						}
					} ); // End foreach().

					// Wrap filters.
					filterWrapper = '<div role="menubar">';
					filterWrapper += '<ul class="fusion-filters" role="menu" aria-label="filters">' + filter + '</ul>';
					filterWrapper += '</div>';

				} // End if().

				return filterWrapper;
			},

			buildPagination: function( values, extras, queryData ) {
				var pagination = '',
					infinitePagination;

				if ( 'none' !== values.pagination_type && 1 < queryData.number_of_pages ) {

					// Pagination is set to "load more" button.
					if ( 'load-more-button' === values.pagination_type && -1 !== parseInt( queryData.number_of_pages, 10 ) ) {
						pagination += '<button class="fusion-load-more-button fusion-portfolio-button fusion-clearfix">' + extras.load_more_posts + '</button>';
					}

					infinitePagination = 'false';
					if ( 'load-more-button' === values.pagination_type || 'infinite' === values.pagination_type ) {
						infinitePagination = 'true';
					}

					pagination += queryData.pagination[ infinitePagination ];
				}

				return pagination;
			},

			buildPortfolioPosts: function( values, extras, queryData ) {
				var portfolioPosts       = '',
					richSnippets         = '',
					postClasses          = '',
					titleTerms           = '',
					image                = '',
					postTitle            = '',
					postTerms            = '',
					separator            = '',
					buttons              = '',
					learnMoreButton      = '',
					viewProjectButton    = '',
					postSeparator        = '',
					separatorStylesArray = '',
					galleryID            = '-rw-' + this.model.get( 'cid' ),
					title                = true,
					categories           = true,
					stripHTML            = '',
					excerptLength        = values.excerpt_length,
					excerpt              = 'no',
					self                 = this,
					that                 = self,
					singlePostContent,
					postContent,
					showTitle,
					fusionPortfolioCarouselTitle,
					fusionPortfolioSeparator,
					fusionPortfolioContentWrapper,
					fusionPortfolioContent,
					videoMaxWidth,
					featuredImageSizeDimensions,
					videoMarkup,
					imageData,
					color,
					colorCSS,
					classes;

				// Check the title and category display options.
				if ( values.portfolio_title_display ) {
					title      = ( 'all' === values.portfolio_title_display || 'title' === values.portfolio_title_display );
					categories = ( 'all' === values.portfolio_title_display || 'cats' === values.portfolio_title_display );
				}

				if ( 'default' === values.strip_html ) {
					stripHTML = extras.portfolio_strip_html_excerpt;
				} else {
					stripHTML = ( 'yes' === values.strip_html );
				}

				// As excerpt_words is deprecated, only use it when explicity set.
				if ( 'undefined' !== typeof values.excerpt_words && '' !== values.excerpt_words ) {
					excerptLength = values.excerpt_words;
				}

				if ( 'excerpt' === values.content_length ) {
					excerpt = 'yes';
				}

				_.each( queryData.portfolios, function( portfolio ) {

					if ( 'no_text' === values.content_length ) {
						singlePostContent = '';
					} else if ( true === portfolio.has_manual_excerpt ) {
						singlePostContent = stripHTML ? portfolio.content.excerpt_stripped : portfolio.content.excerpt;
					} else {
						singlePostContent = _.fusionGetFixedContent( portfolio.content, excerpt, excerptLength, stripHTML );
					}

					// Only add post if it has a featured image, or a video, or if placeholders are activated.
					if ( false !== portfolio.thumbnail_type ) {

						richSnippets      = '';
						postClasses       = '';
						titleTerms        = '';
						image             = '';
						postTitle         = '';
						postTerms         = '';
						separator         = '';
						postContent       = '';
						buttons           = '';
						learnMoreButton   = '';
						viewProjectButton = '';
						postSeparator     = '';

						// For carousels we only need the image and a li wrapper.
						if ( 'carousel' === values.layout ) {

							showTitle = 'enable';
							if ( 'title_on_rollover' !== values.carousel_layout ) {
								showTitle = 'disable';

								// Get the post title.
								fusionPortfolioCarouselTitle = '<h4 class="fusion-carousel-title"><a href="' + portfolio.permalink + '" target="_self">' + portfolio.title + '</a></h4>';
								titleTerms += fusionPortfolioCarouselTitle;

								if ( false !== portfolio.term_list ) {
									titleTerms += portfolio.term_list;
								}
							}

							// Render the video set in page options if no featured image is present.
							if ( 'video' === portfolio.thumbnail_type ) {

								// For the portfolio one column layout we need a fixed max-width.
								if ( '1' === values.columns || 1 === values.columns ) {
									videoMaxWidth = '540px';

									// For all other layouts get the calculated max-width from the image size.
								} else {
									featuredImageSizeDimensions = portfolio.image_size_dimensions;
									videoMaxWidth = featuredImageSizeDimensions.width;
								}

								videoMarkup = '<div class="fusion-image-wrapper fusion-video" style="max-width:' + videoMaxWidth + ';">' + portfolio.video + '</div>';
								image       = videoMarkup;

							} else if ( 'image' === portfolio.thumbnail_type ) {
								imageData = portfolio.image_data;

								imageData.image_size         = that.getImageSize( values );
								imageData.display_post_title = showTitle;
								imageData.gallery_id         = galleryID;

								image = _.fusionFeaturedImage( imageData );
							}

							portfolioPosts += '<li class="fusion-carousel-item"><div class="fusion-carousel-item-wrapper">' + portfolio.rich_snippets[ 'false' ] + image + titleTerms + '</div></li>';

						} else {

							if ( portfolio.post_categories ) {
								_.each( portfolio.post_categories, function( postCategory ) {
									postClasses += decodeURI( postCategory.slug ) + ' ';
								} );
							}

							// Add the col-spacing class if needed.
							if ( '0' !== values.column_spacing && 0 !== values.column_spacing && '' !== values.column_spacing ) {
								postClasses += 'fusion-col-spacing';
							}

							// Render the video set in page options if no featured image is present.
							if ( 'video' === portfolio.thumbnail_type ) {

								// For the portfolio one column layout we need a fixed max-width.
								if ( '1' === values.columns || 1 === values.columns ) {
									videoMaxWidth = '540px';

								} else { // For all other layouts get the calculated max-width from the image size.
									featuredImageSizeDimensions = portfolio.image_size_dimensions;
									videoMaxWidth               = featuredImageSizeDimensions.width;
								}

								videoMarkup = '<div class="fusion-image-wrapper fusion-video" style="max-width:' + videoMaxWidth + ';">' + portfolio.video + '</div>';
								image       = videoMarkup;

							} else if ( 'image' === portfolio.thumbnail_type ) {
								imageData = portfolio.image_data;

								imageData.image_size = that.getImageSize( values );
								imageData.gallery_id = galleryID;
								if ( 'masonry' === values.layout ) {
									imageData.layout = values.layout;
									imageData.masonry_data.blog_grid_column_spacing  = parseFloat( values.column_spacing );
									if ( true !== imageData.masonry_data.specific_element_orientation_class ) {
										imageData.masonry_data.element_orientation_class = _.fusionGetElementOrientationClass( { imageWidth: imageData.masonry_data.image_width, imageHeight: imageData.masonry_data.image_height }, values.portfolio_masonry_grid_ratio, values.portfolio_masonry_width_double );
									}
									imageData.masonry_data.element_base_padding = _.fusionGetElementBasePadding( imageData.masonry_data.element_orientation_class );

									postClasses += ' ' + imageData.masonry_data.element_orientation_class;

									if ( 'fusion-element-landscape' !== imageData.masonry_data.element_orientation_class ) {
										this.regularImagesFound = true;
									}
								}

								image = _.fusionFeaturedImage( imageData );
							}

							// Additional content for grid-with-text layout.
							if ( 'no_text' !== values.text_layout ) {

								richSnippets = portfolio.rich_snippets[ 'false' ];

								// Get the post title.
								if ( title ) {
									postTitle = portfolio.post_title;
								}

								// Get the post terms.
								if ( categories && portfolio.post_terms ) {
									postTerms = portfolio.post_terms;
								}

								// For boxed layouts add a content separator if there is a post content.
								fusionPortfolioSeparator = {
									class: 'fusion-content-sep',
									style: ''
								};
								if ( 'boxed' === values.text_layout && '' !== singlePostContent && 'masonry' !== values.layout ) {
									color = jQuery.AWB_Color( values.grid_separator_color );
									colorCSS = color.toVarOrRgbaString();
									if ( 0 === color.alpha() ) {
										colorCSS = color.alpha( 1 );
										colorCSS = color.toVarOrRgbaString();
									}

									if ( 0 === color.alpha() || 'transparent' === values.grid_separator_color ) {
										fusionPortfolioSeparator[ 'class' ] += ' sep-transparent';
									} else {
										fusionPortfolioSeparator.style += 'border-color:' + colorCSS + ';';
									}

									if ( '' !== values.grid_separator_style_type ) {
										separatorStylesArray = values.grid_separator_style_type.split( '|' );

										_.each( separatorStylesArray, function( sepStyle ) {
											fusionPortfolioSeparator[ 'class' ] += ' sep-' + sepStyle;
										} );
									}

									separator = '<div ' + _.fusionGetAttributes( fusionPortfolioSeparator ) + '></div>';
								}

								// On one column layouts render the "Learn More" and "View Project" buttons.
								if ( ( '1' === values.columns || 1 === values.columns ) && 'masonry' !== values.layout ) {
									classes = 'fusion-button fusion-button-small fusion-button-default fusion-button-' + extras.button_type;

									// Add the "Learn More" button.
									learnMoreButton = '<a href="' + portfolio.permalink + '" class="' + classes + '">' + extras.learn_more + '</a>';

									// If there is a project url, add the "View Project" button.
									viewProjectButton = '';
									if ( false !== portfolio.project_url && '' !== portfolio.project_url ) {
										viewProjectButton = '<a href="' + portfolio.project_url + '" class="' + classes + '">' + extras.view_project + '</a>';
									}

									// Wrap buttons.
									buttons = '<div class="fusion-portfolio-buttons">' + learnMoreButton + viewProjectButton + '</div>';

								}

								fusionPortfolioContentWrapper = self.buildPortfolioContentWrapperAttr( values );

								fusionPortfolioContent = self.buildPortfolioContentAttr( values );

								postContent  = '<div ' + _.fusionGetAttributes( fusionPortfolioContent ) + '>';
								postContent += postTitle;
								postContent += postTerms;
								postContent += separator;
								postContent += '<div class="fusion-post-content">';

								postContent += singlePostContent;

								postContent += buttons;
								postContent += '</div></div>';
							} else {

								richSnippets = portfolio.rich_snippets[ 'true' ];
							}

							// Post separator for one column layouts.
							if ( ( '1' === values.columns || 1 === values.columns ) && 'boxed' !== values.text_layout && 'grid' === values.layout ) {
								postSeparator = '<div class="fusion-clearfix"></div><div class="fusion-separator sep-double"></div>';
							}

							portfolioPosts += '<article class="fusion-portfolio-post ' + postClasses + '"><div ' + _.fusionGetAttributes( fusionPortfolioContentWrapper ) + '>' + richSnippets + image + postContent + '</div>' + postSeparator + '</article>';
						} // End if().
					}
				} );

				return portfolioPosts;
			},

			buildPortfolioContentWrapperAttr: function( values ) {
				var fusionPortfolioContentWrapper = {
						class: 'fusion-portfolio-content-wrapper',
						style: ''
					},
					elementColor,
					color,
					colorCSS;

				if ( 'grid' === values.layout || 'masonry' === values.layout ) {
					elementColor = jQuery.AWB_Color( values.grid_element_color );
					if ( 'boxed' !== values.text_layout || 0 === elementColor.alpha() || 'transparent' === values.grid_element_color ) {
						fusionPortfolioContentWrapper.style += 'border:none;';
					} else {
						fusionPortfolioContentWrapper.style += 'border:1px solid ' + values.grid_element_color + ';border-bottom-width:3px;';
					}
				}

				if ( 'grid' === values.layout && 'boxed' === values.text_layout ) {
					color    = jQuery.AWB_Color( values.grid_box_color );
					colorCSS = color.toVarOrRgbaString();
					fusionPortfolioContentWrapper.style += 'background-color:' + colorCSS + ';';
				}
				return fusionPortfolioContentWrapper;
			},

			buildPortfolioContentAttr: function( values ) {
				var fusionPortfolioContent = {
						class: 'fusion-portfolio-content',
						style: ''
					},
					color,
					colorCSS;

				if ( 'masonry' === values.layout ) {

					if ( 'boxed' === values.text_layout ) {
						fusionPortfolioContent.style += 'bottom:0px;';
						fusionPortfolioContent.style += 'left:0px;';
						fusionPortfolioContent.style += 'right:0px;';
					} else {
						fusionPortfolioContent.style += 'padding:20px 0px;';
						fusionPortfolioContent.style += 'bottom:0px;';
						fusionPortfolioContent.style += 'left:0px;';
						fusionPortfolioContent.style += 'right:0px;';
					}

					color    = jQuery.AWB_Color( values.grid_box_color );
					colorCSS = color.toVarOrRgbaString();
					if ( 0 === color.alpha() ) {
						colorCSS = color.alpha( 1 );
						colorCSS = color.toVarOrRgbaString();
					}
					fusionPortfolioContent.style += 'background-color:' + colorCSS + ';';
					fusionPortfolioContent.style += 'z-index:1;';
					fusionPortfolioContent.style += 'position:absolute;';
					fusionPortfolioContent.style += 'margin:0;';

				} else if ( 'grid' === values.layout && 'boxed' === values.text_layout ) {
					color    = jQuery.AWB_Color( values.grid_box_color );
					colorCSS = color.toVarOrRgbaString();
					fusionPortfolioContent.style += 'background-color:' + colorCSS + ';';
				}
				return fusionPortfolioContent;
			},

			buildAlignPaddingStyle: function( values ) {
				var styling         = '',
					layoutPadding   = '',
					layoutAlignment = '';

				if ( 'carousel' !== values.layout && 'no_text' !== values.text_layout ) {
					if ( 'boxed' === values.text_layout ) {

						if ( 'undefined' !== typeof values.padding_top ) {
							layoutPadding += 'padding-top: ' + values.padding_top + ';';
						}

						if ( 'undefined' !== typeof values.padding_right ) {
							layoutPadding += 'padding-right: ' + values.padding_right + ';';
						}

						if ( 'undefined' !== typeof values.padding_bottom ) {
							layoutPadding += 'padding-bottom: ' + values.padding_bottom + ';';
						}

						if ( 'undefined' !== typeof values.padding_left ) {
							layoutPadding += 'padding-left: ' + values.padding_left + ';';
						}
					}

					layoutAlignment  = 'text-align: ' + values.portfolio_text_alignment + ';';
					styling         += '<style type="text/css">.fusion-portfolio-wrapper#fusion-portfolio-cid' + this.model.get( 'cid' ) + ' .fusion-portfolio-content{ ' + layoutPadding + ' ' + layoutAlignment + ' }</style>';
				}

				return styling;
			},

			buildColumnSpacingStyle: function( values ) {
				var styling = '';

				// For column spacing set needed css.
				if ( '0' !== values.column_spacing && 0 !== values.column_spacing && '' !== values.column_spacing ) {
					styling = '<style type="text/css">.fusion-portfolio-cid' + this.model.get( 'cid' ) + ' .fusion-portfolio-wrapper .fusion-col-spacing{padding:' + ( values.column_spacing / 2 ) + 'px;}</style>';
				}

				return styling;
			},

			getImageSize: function( values ) {
				var imageSize = 'full',
					columns   = this.columnsWords[ parseInt( values.columns, 10 ) ];

				if ( 'fixed' === values.picture_size ) {
					if ( 'carousel' === values.layout ) {
						imageSize = 'portfolio-two';
						if ( 'six' === columns || 'five' === columns || 'four' === columns ) {
							imageSize = 'blog-medium';
						}
					} else {
						imageSize = 'portfolio-' + columns;
						if ( 'six' === columns ) {
							imageSize = 'portfolio-five';
						} else if ( 'four' === columns ) {
							imageSize = 'portfolio-three';
						}
					}
				}

				return imageSize;
			}

		} );
	} );
}( jQuery ) );

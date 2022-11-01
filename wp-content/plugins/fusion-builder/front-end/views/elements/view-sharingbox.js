/* global cssua, FusionApp */
/* jshint -W107 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Sharing Box View.
		FusionPageBuilder.fusion_sharing = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforePatch: function() {
				var tooltips = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el ).find( '.fusion-social-networks [data-toggle="tooltip"]' );

				tooltips.tooltip( 'destroy' );
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var tooltips = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el ).find( '.fusion-social-networks [data-toggle="tooltip"]' );

				setTimeout( function() {
					tooltips.tooltip( {
						container: 'body'
					} );
				}, 150 );

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

				// Validate values and extras.
				this.validateValuesExtras( atts.values, atts.extras );
				this.values = atts.values;

				// Create attribute objects.
				attributes.cid         = this.model.get( 'cid' );
				this.counter = this.model.get( 'cid' );
				attributes.shortcodeAttr      = this.buildShortcodeAttr( atts.values );
				attributes.socialNetworksAttr = this.buildSocialNetworksAttr( atts.values );
				attributes.taglineAttr        = this.buildTaglineAttr( atts.values );
				attributes.icons              = this.buildIcons( atts.values );
				attributes.tagline            = atts.values.tagline;
				attributes.taglineVisibility  = atts.values.tagline_visibility;
				attributes.styles             = this.buildStyleBlock();
				attributes.taglineTag         = this.getTaglineTag( atts.values );

				return attributes;
			},

			/**
			 * Builds styles.
			 *
			 * @since  2.4
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildStyleBlock: function() {
				var selector, large_layout, css, layout_medium, layout_small;
				this.baseSelector = '.sharingbox-shortcode-icon-wrapper-' +  this.counter + '';
				this.wrapper_selector = '.fusion-sharing-box-' +  this.counter;
				this.selectors = [ this.baseSelector, this.wrapper_selector ];
				this.dynamic_css = {};

				if ( 'hide' ===  this.values.tagline_visibility ) {
					this.values.layout = 'floated';
					this.values.layout_medium = 'floated';
					this.values.layout_small = 'floated';
				}

				if ( ! this.values.layout_medium ) {
					this.values.layout_medium =  this.values.layout;
				}

				if ( ! this.values.layout_small ) {
					this.values.layout_small =  this.values.layout;
				}

				if ( this.values.icon_taglines ) {
					if ( 'before' ===  this.values.tagline_placement ) {
						this.addCssProperty( this.wrapper_selector + ' .fusion-social-network-icon-tagline', 'margin-right', '0.5em', true );
					} else {
						this.addCssProperty( this.wrapper_selector + ' .fusion-social-network-icon-tagline', 'margin-left', '0.5em', true );
					}

					this.addCssProperty( this.baseSelector + ' span a', 'align-items', 'center', true );
					this.addCssProperty( this.baseSelector + ' span a', 'display', 'flex', true );
				}

				if ( ! this.values.stacked_align_medium ) {
					this.values.stacked_align_medium =  this.values.stacked_align;
				}

				if ( ! this.values.stacked_align_small ) {
					this.values.stacked_align_small =  this.values.stacked_align;
				}

				if ( ! this.values.alignment_medium ) {
					this.values.alignment_medium =  this.values.alignment;
				}

				if ( ! this.values.alignment_small ) {
					this.values.alignment_small =  this.values.alignment;
				}

				if ( !this.isDefault( 'alignment' ) ) {
					this.addCssProperty( [ this.baseSelector ], 'justify-content',  this.values.alignment, true );
				}

				selector = [ this.wrapper_selector ];
				if ( 'floated' ===  this.values.layout ) {
					this.addCssProperty( [ this.wrapper_selector + ' .tagline' ], 'margin-bottom', '0', true );
				} else {
					this.addCssProperty( selector, 'align-items',  this.values.stacked_align, true );
					this.addCssProperty( selector, 'justify-content', 'space-around', true );
					this.addCssProperty( [ this.baseSelector ], 'width', '100%', true );
				}

				large_layout = ( 'stacked' ===  this.values.layout ) ? ' column' : 'row';
				this.addCssProperty( selector, 'flex-direction', large_layout, true );
				if ( !this.isDefault( 'border_color' ) ) {
					this.addCssProperty( selector, 'border-color',  this.values.border_color, true );
				}

				if ( !this.isDefault( 'wrapper_padding_top' ) ) {
					this.addCssProperty( selector, 'padding-top',  _.fusionGetValueWithUnit( this.values.wrapper_padding_top ), true );
				}

				if ( !this.isDefault( 'wrapper_padding_bottom' ) ) {
					this.addCssProperty( selector, 'padding-bottom',  _.fusionGetValueWithUnit( this.values.wrapper_padding_bottom ), true );
				}

				if ( !this.isDefault( 'wrapper_padding_left' ) ) {
					this.addCssProperty( selector, 'padding-left',  _.fusionGetValueWithUnit( this.values.wrapper_padding_left ), true );
				}

				if ( !this.isDefault( 'wrapper_padding_right' ) ) {
					this.addCssProperty( selector, 'padding-right',  _.fusionGetValueWithUnit( this.values.wrapper_padding_right ), true );
				}

				if ( !this.isDefault( 'border_bottom' ) ) {
					this.addCssProperty( selector, 'border-bottom-width',  _.fusionGetValueWithUnit( this.values.border_bottom ), true );
				}

				if ( !this.isDefault( 'border_top' ) ) {
					this.addCssProperty( selector, 'border-top-width',  _.fusionGetValueWithUnit( this.values.border_top ), true );
				}

				if ( !this.isDefault( 'border_left' ) ) {
					this.addCssProperty( selector, 'border-left-width',  _.fusionGetValueWithUnit( this.values.border_left ), true );
				}

				if ( !this.isDefault( 'border_right' ) ) {
					this.addCssProperty( selector, 'border-right-width',  _.fusionGetValueWithUnit( this.values.border_right ), true );
				}

				if ( !this.isDefault( 'border_radius_top_left' ) ) {
					this.addCssProperty( selector, 'border-top-left-radius',  _.fusionGetValueWithUnit( this.values.border_radius_top_left ), true );
				}

				if ( !this.isDefault( 'border_radius_top_right' ) ) {
					this.addCssProperty( selector, 'border-top-right-radius',  _.fusionGetValueWithUnit( this.values.border_radius_top_right ), true );
				}

				if ( !this.isDefault( 'border_radius_bottom_right' ) ) {
					this.addCssProperty( selector, 'border-bottom-right-radius',  _.fusionGetValueWithUnit( this.values.border_radius_bottom_right ), true );
				}

				if ( !this.isDefault( 'border_radius_bottom_left' ) ) {
					this.addCssProperty( selector, 'border-bottom-left-radius',  _.fusionGetValueWithUnit( this.values.border_radius_bottom_left ), true );
				}

				selector = [ this.baseSelector + ' span:not(.sharingbox-shortcode-icon-separator)' ];
				if ( !this.isDefault( 'padding_top' ) ) {
					this.addCssProperty( selector, 'padding-top',  this.values.padding_top, true );
				}

				if ( !this.isDefault( 'padding_bottom' ) ) {
					this.addCssProperty( selector, 'padding-bottom',  this.values.padding_bottom, true );
				}

				if ( !this.isDefault( 'padding_left' ) ) {
					this.addCssProperty( selector, 'padding-left',  this.values.padding_left, true );
				}

				if ( !this.isDefault( 'padding_right' ) ) {
					this.addCssProperty( selector, 'padding-right',  this.values.padding_right, true );
				}

				if ( !this.isDefault( 'icon_tagline_color' ) ) {
					this.addCssProperty( this.baseSelector + ' a', 'color',  this.values.icon_tagline_color, true );
				}

				if ( !this.isDefault( 'icon_tagline_color_hover' ) ) {
					this.addCssProperty( this.baseSelector + ' a:hover', 'color',  this.values.icon_tagline_color_hover, true );
				}

				if ( !this.isDefault( 'tagline_text_size' ) ) {
					this.addCssProperty( this.baseSelector + ' a', 'font-size',  this.values.tagline_text_size, true );
				}

				if ( !this.isDefault( 'icon_size' ) ) {
					this.addCssProperty( this.baseSelector + ' a i', 'font-size',  this.values.icon_size, true );
				}

				selector = [ this.baseSelector + ' span.sharingbox-shortcode-icon-separator' ];
				if ( !this.isDefault( 'separator_border_color' ) ) {
					this.addCssProperty( selector, 'border-color',  this.values.separator_border_color, true );
				}

				if ( !this.isDefault( 'separator_border_sizes' ) ) {
					this.values.separator_border_sizes = this.values.separator_border_sizes + 'px';
					this.addCssProperty( selector, 'border-right-width',  this.values.separator_border_sizes, true );
				}

				css = this.parseCSS();
				this.dynamic_css = {};
				layout_medium = ( 'stacked' ===  this.values.layout_medium ) ? ' column' : 'row';
				selector = [ this.wrapper_selector ];
				this.addCssProperty( selector, 'flex-direction', layout_medium, true );
				if ( 'floated' !==  this.values.layout_medium ) {
					this.addCssProperty( [ this.wrapper_selector + ' .tagline' ], 'margin-bottom', 'revert', true );
					this.addCssProperty( [ this.baseSelector ], 'width', '100%', true );
				} else {
					this.addCssProperty( [ this.baseSelector ], 'width', 'auto', true );
					this.addCssProperty( selector, 'align-items', 'center', true );
					this.addCssProperty( [ this.wrapper_selector + ' .tagline' ], 'margin-bottom', '0', true );
					this.addCssProperty( this.wrapper_selector + ' .tagline', 'margin-right', '0.5em', true );
				}

				if ( this.values.alignment_medium ) {
					this.addCssProperty( [ this.baseSelector ], 'justify-content',  this.values.alignment_medium, true );
					if ( 'floated' !==  this.values.layout_medium ) {
						this.addCssProperty( selector, 'align-items',  this.values.stacked_align_medium, true );
					}

				}

				css += '@media only screen and (max-width:' + FusionApp.settings.visibility_medium + 'px){' + this.parseCSS() + ' }';
				layout_small = ( 'stacked' ===  this.values.layout_small ) ? ' column' : 'row';
				this.dynamic_css = {};
				this.addCssProperty( selector, 'flex-direction', layout_small, true );
				if ( 'floated' !==  this.values.layout_small ) {
					this.addCssProperty( [ this.wrapper_selector + ' .tagline' ], 'margin-bottom', 'revert', true );
					this.addCssProperty( [ this.baseSelector ], 'width', '100%', true );
				} else {
					this.addCssProperty( [ this.wrapper_selector + ' .tagline' ], 'margin-bottom', '0', true );
					this.addCssProperty( selector, 'align-items', 'center', true );
					this.addCssProperty( [ this.baseSelector ], 'width', 'auto', true );
					this.addCssProperty( this.wrapper_selector + ' .tagline', 'margin-right', '0.5em', true );
				}

				if ( this.values.alignment_small ) {
					this.addCssProperty( this.baseSelector, 'justify-content',  this.values.alignment_small, true );
					if ( 'floated' !==  this.values.layout_small ) {
						this.addCssProperty( selector, 'align-items',  this.values.stacked_align_small, true );
					}

				}

				css += '@media only screen and (max-width:' + FusionApp.settings.visibility_small + 'px){' + this.parseCSS() + ' }';
				return ( css ) ? '<style type="text/css">' + css + '</style>' : '';
			},

			/**
			 * Modifies the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @param {Object} extras - Extra args.
			 * @return {void}
			 */
			validateValuesExtras: function( values, extras ) {
				extras.linktarget         = extras.linktarget ? '_blank' : '_self';
				values.icons_boxed_radius = _.fusionValidateAttrValue( values.icons_boxed_radius, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildShortcodeAttr: function( values ) {
				var sharingboxShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-sharing-box fusion-sharing-box-' + this.model.get( 'cid' ),
					style: ''
				} );

				sharingboxShortcode[ 'class' ] += _.fusionGetStickyClass( values.sticky_display );

				if ( 'yes' === values.icons_boxed ) {
					sharingboxShortcode[ 'class' ] += ' boxed-icons';
				}

				if ( '' !== values.backgroundcolor ) {
					sharingboxShortcode.style = 'background-color:' + values.backgroundcolor + ';';

					if ( 'transparent' === values.backgroundcolor || 0 === jQuery.AWB_Color( values.backgroundcolor ).alpha() ) {
						sharingboxShortcode.style += 'padding:0;';
					}
				}

				if ( '' !== values[ 'class' ] ) {
					sharingboxShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					sharingboxShortcode[ 'class' ] += ' ' + values.id;
				}

				if ( '' !== values.margin_top ) {
					sharingboxShortcode.style += 'margin-top: ' + values.margin_top + ';';
				}

				if ( '' !== values.margin_bottom ) {
					sharingboxShortcode.style += 'margin-bottom: ' + values.margin_bottom + ';';
				}

				if ( '' !== values.margin_left ) {
					sharingboxShortcode.style += 'margin-left: ' + values.margin_left + ';';
				}

				if ( '' !== values.margin_right ) {
					sharingboxShortcode.style += 'margin-right: ' + values.margin_right + ';';
				}

				sharingboxShortcode[ 'data-title' ]       = values.title;
				sharingboxShortcode[ 'data-description' ] = values.description;
				sharingboxShortcode[ 'data-link' ]        = values.link;
				sharingboxShortcode[ 'data-image' ]       = values.pinterest_image;

				return sharingboxShortcode;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildSocialNetworksAttr: function( values ) {
				var sharingboxShortcodeSocialNetworks = {
					class: 'fusion-social-networks sharingbox-shortcode-icon-wrapper sharingbox-shortcode-icon-wrapper-' + this.model.get( 'cid' )
				};

				if ( 'yes' === values.icons_boxed ) {
					sharingboxShortcodeSocialNetworks[ 'class' ] += ' boxed-icons';
				}

				if ( '' !== values.alignment ) {
					sharingboxShortcodeSocialNetworks.style = 'text-align: ' + values.alignment + ';';
				}

				return sharingboxShortcodeSocialNetworks;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildTaglineAttr: function( values ) {
				var sharingboxShortcodeTagline = {
						class: 'tagline'
					},
					that = this;

				if ( '' !== values.tagline_color ) {
					sharingboxShortcodeTagline.style = 'color:' + values.tagline_color + ';';
				}

				sharingboxShortcodeTagline.style += _.fusionGetFontStyle( 'tagline_font', values );

				if ( 'undefined' !== typeof values.tagline_font_size && '' !== values.tagline_font_size ) {
					sharingboxShortcodeTagline.style += 'font-size:' + values.tagline_font_size + ';';
				}

				if ( 'undefined' !== typeof values.tagline_line_height && '' !== values.tagline_line_height ) {
					sharingboxShortcodeTagline.style += 'line-height:' + values.tagline_line_height + ';';
				}

				if ( 'undefined' !== typeof values.tagline_letter_spacing && '' !== values.tagline_letter_spacing ) {
					sharingboxShortcodeTagline.style += 'letter-spacing:' + values.tagline_letter_spacing + ';';
				}

				if ( 'undefined' !== typeof values.tagline_text_transform && '' !== values.tagline_text_transform ) {
					sharingboxShortcodeTagline.style += 'text-transform:' + values.tagline_text_transform + ';';
				}

				sharingboxShortcodeTagline = _.fusionInlineEditor( {
					param: 'tagline',
					cid: that.model.get( 'cid' ),
					toolbar: false
				}, sharingboxShortcodeTagline );

				return sharingboxShortcodeTagline;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildIconAttr: function( values ) {
				var sharingboxShortcodeTagline = {
					class: 'tagline'
				};

				if ( '' !== values.tagline_color ) {
					sharingboxShortcodeTagline.style = 'color:' + values.tagline_color + ';';
				}

				return sharingboxShortcodeTagline;
			},

			/**
			 * Builds HTML for the icons.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @param {Object} extras - Extra args.
			 * @return {string}
			 */
			buildIcons: function( values ) {
				var icons            = '',
					iconColors       = values.icon_colors,
					boxColors        = values.box_colors,
					itemTagline	 = values.icon_taglines,
					useBrandColors   = false,
					numOfIconColors,
					numOfBoxColors,
					socialNetworks,
					socialNetworksCount,
					i,
					description,
					link,
					title,
					image,
					socialLink,
					sharingboxShortcodeIcon,
					sharingboxShortcodeIconLink,
					iconOptions,
					socialIconBoxedColors,
					network,
					tooltip,
					numOfTaglines;

				if ( 'brand' === values.color_type ) {
					useBrandColors = true;

					// Get a list of all the available social networks.
					socialIconBoxedColors = _.fusionSocialIcons( false, true );
					socialIconBoxedColors.mail = {
						label: 'Email Address',
						color: '#000000'
					};

				}

				iconColors = iconColors.split( '|' );
				boxColors  = boxColors.split( '|' );
				itemTagline = itemTagline.split( '|' );

				numOfIconColors     = iconColors.length;
				numOfBoxColors      = boxColors.length;
				numOfTaglines      = itemTagline.length;
				socialNetworks = values.social_share_links;

				if ( 'string' === typeof socialNetworks ) {
					socialNetworks = socialNetworks.split( ',' );
				}
				socialNetworksCount = socialNetworks.length;

				for ( i = 0; i < socialNetworksCount; i++ ) {
					network = socialNetworks[ i ];

					if ( true === useBrandColors ) {
						iconOptions = {
							social_network: network,
							icon_color: ( 'yes' === values.icons_boxed ) ? '#ffffff' : socialIconBoxedColors[ network ].color,
							box_color: ( 'yes' === values.icons_boxed ) ? socialIconBoxedColors[ network ].color : ''
						};

					} else {
						iconOptions = {
							social_network: network,
							icon_color: i < iconColors.length ? iconColors[ i ] : '',
							box_color: i < boxColors.length ? boxColors[ i ] : ''
						};

						if ( 1 === numOfIconColors ) {
							iconOptions.icon_color = iconColors[ 0 ];
						}
						if ( 1 === numOfBoxColors ) {
							iconOptions.box_color = boxColors[ 0 ];
						}
					}
					if ( 1 === numOfTaglines ) {
						iconOptions.icon_tagline =  itemTagline[ 0 ];
					} else {
						iconOptions.icon_tagline = i < itemTagline.length ? itemTagline[ i ] : '';
					}
					iconOptions.social_network = 'email' === iconOptions.social_network ? 'mail' : iconOptions.social_network;
					// sharingboxShortcodeIcon attributes
					description = values.description;
					link        = values.link;
					title       = values.title;
					image       = _.fusionRawUrlEncode( values.pinterest_image );

					sharingboxShortcodeIcon = {
						class: 'fusion-social-network-icon fusion-tooltip fusion-' + iconOptions.social_network + ' awb-icon-' + iconOptions.social_network
					};
					sharingboxShortcodeIconLink = {};

					socialLink = '';
					switch ( iconOptions.social_network ) {
					case 'facebook':
						socialLink = 'https://m.facebook.com/sharer.php?u=' + link;
						if ( cssua.ua.mobile ) {
							socialLink = 'https://www.facebook.com/sharer.php?m2w&s=100&p&#91;url&#93;=' + link + '&p&#91;images&#93;&#91;title&#93;=' + _.fusionRawUrlEncode( title );
						}
						break;
					case 'twitter':
						socialLink = 'https://twitter.com/share?text=' + _.fusionRawUrlEncode( title ) + '&url=' + _.fusionRawUrlEncode( link );
						break;
					case 'linkedin':
						socialLink = 'https://www.linkedin.com/shareArticle?mini=true&url=' + _.fusionRawUrlEncode( link ) + '&amp;title=' + _.fusionRawUrlEncode( title ) + '&amp;summary=' + _.fusionRawUrlEncode( description );
						break;
					case 'reddit':
						socialLink = 'https://reddit.com/submit?url=' + link + '&amp;title=' + title;
						break;
					case 'telegram':
						socialLink = 'https://t.me/share/url?url=' + _.fusionRawUrlEncode( link ) + '&text=' + _.fusionRawUrlEncode( title );
						break;
					case 'tumblr':
						socialLink = 'https://www.tumblr.com/share/link?url=' + _.fusionRawUrlEncode( link ) + '&amp;name=' + _.fusionRawUrlEncode( title ) + '&amp;description=' + _.fusionRawUrlEncode( description );
						break;
					case 'pinterest':
						socialLink = 'https://pinterest.com/pin/create/button/?url=' + _.fusionRawUrlEncode( link ) + '&amp;description=' + _.fusionRawUrlEncode( description ) + '&amp;media=' + image;
						break;
					case 'vk':
						socialLink = 'https://vk.com/share.php?url=' + _.fusionRawUrlEncode( link ) + '&amp;title=' + _.fusionRawUrlEncode( title ) + '&amp;description=' + _.fusionRawUrlEncode( description );
						break;
					case 'mail':
						socialLink = 'mailto:?subject=' + _.fusionRawUrlEncode( title ) + '&body=' + _.fusionRawUrlEncode( link );
						break;
					}

					sharingboxShortcodeIconLink.href   = socialLink;
					sharingboxShortcodeIconLink.target = ( values.linktarget && 'mail' !== iconOptions.social_network ) ? '_blank' : '_self';

					if ( '_blank' === sharingboxShortcodeIcon.target ) {
						sharingboxShortcodeIconLink.rel = 'noopener noreferrer';
					}

					sharingboxShortcodeIcon.style = ( iconOptions.icon_color ) ? 'color:' + iconOptions.icon_color + ';' : '';

					if ( values.icons_boxed && 'yes' === values.icons_boxed && iconOptions.box_color ) {
						sharingboxShortcodeIcon.style += 'background-color:' + iconOptions.box_color + ';border-color:' + iconOptions.box_color + ';';
					}

					if ( ( 'yes' === values.icons_boxed && values.icons_boxed_radius ) || '0' === values.icons_boxed_radius ) {
						if ( 'round' === values.icons_boxed_radius ) {
							values.icons_boxed_radius = '50%';
						}
						sharingboxShortcodeIcon.style += 'border-radius:' + values.icons_boxed_radius + ';';
					}

					sharingboxShortcodeIconLink[ 'data-placement' ] = values.tooltip_placement;
					tooltip = iconOptions.social_network;

					sharingboxShortcodeIconLink[ 'data-title' ] = _.fusionUcFirst( tooltip );
					sharingboxShortcodeIconLink.title         = _.fusionUcFirst( tooltip );
					sharingboxShortcodeIconLink[ 'aria-label' ] = _.fusionUcFirst( tooltip );


					if ( 'none' !== values.tooltip_placement ) {
						sharingboxShortcodeIconLink[ 'data-toggle' ] = 'tooltip';
					}
					icons += '<span><a ' + _.fusionGetAttributes( sharingboxShortcodeIconLink ) + '>';
					icons += 'before' === values.tagline_placement && '' !== iconOptions.icon_tagline ? '<div class="fusion-social-network-icon-tagline">' + iconOptions.icon_tagline + '</div>' : '';
					icons += '<i  ' + _.fusionGetAttributes( sharingboxShortcodeIcon ) + ' aria-hidden="true"></i>';
					icons += 'after' === values.tagline_placement && '' !== iconOptions.icon_tagline ? '<div class="fusion-social-network-icon-tagline">' + iconOptions.icon_tagline + '</div>' : '';
					icons += '</a></span>';

					if ( 0 < values.separator_border_sizes && i < socialNetworks.length - 1 ) {
						icons += '<span class="sharingbox-shortcode-icon-separator"></span>';
					}

				}

				return icons;
			},

			getTaglineTag: function( values ) {
				if ( ! values.tagline_size ) {
					return 'h4';
				}

				if ( !isNaN( values.tagline_size ) && !isNaN( parseFloat( values.tagline_size ) ) ) {
					return 'h' + values.tagline_size;
				}

				return values.tagline_size;
			}

		} );
	} );
}( jQuery ) );

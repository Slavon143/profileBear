/* global FusionPageBuilderViewManager */
/* eslint no-useless-escape: 0 */
/* eslint max-depth: 0 */
/* eslint no-continue: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

_.mixin( {

	/**
	 * The FusionBuilder::attributes() function from PHP, translated to JS.
	 *
	 * @since 2.0.0
	 * @param {Object|string} attributes - The attributes.
	 * @return {string} Ready to use in templates/HTML.
	 */
	fusionGetAttributes: function( attributes ) {
		var out = '';
		if ( 'string' === typeof attributes ) {
			return 'class="' + attributes + '"';
		}
		_.each( attributes, function( value, name ) {
			if ( 'undefined' !== typeof value ) {
				value = value.toString();

				if ( 'valueless_attribute' === value ) {
					out += ' ' + name;
				} else if ( 0 < value.length ) {
					value = value.replace( /\s\s+/g, ' ' );
					out += ' ' + name + '="' + value + '"';
				}
			}
		} );
		return out;
	},

	/**
	 * Remove empty values from params so when merging with defaults, the defaults are used.
	 *
	 * @since 2.0.0
	 * @param {Object} params - The parameters.
	 * @return {Object} - Returns the params.
	 */
	fusionCleanParameters: function( params ) {
		Object.keys( params ).forEach( function( key ) {
			if ( params[ key ] && 'object' === typeof params[ key ] ) {
				_.fusionCleanParameters( params[ key ] );
			} else if ( ( null === params[ key ] || '' === params[ key ] ) && 'element_content' !== key ) {
				delete params[ key ];
			}
		} );
		return params;
	},

	/**
	 * Builds section title element.
	 *
	 * @since 2.2
	 * @param {Object} values - The values.
	 * @param {Object} extras - The extras.
	 * @return {string}
	 */
	buildTitleElement: function( values, extras, headingContent ) {
		var contentAlign = jQuery( 'body' ).hasClass( 'rtl' ) ? 'right' : 'left',
			size         = parseInt( values.heading_size ),
			sizeArray  = [
				'one',
				'two',
				'three',
				'four',
				'five',
				'six'
			],
			output = '',
			styles = '',
			headingStyles   = '',
			classes         = '',
			wrapperClasses  = '',
			marginTop       = extras.title_margin.top,
			marginBottom    = extras.title_margin.bottom,
			sepColor        = extras.title_border_color,
			styleType       = extras.title_style_type,
			underlineOrNone = -1 !== styleType.indexOf( 'underline' ) || -1 !== styleType.indexOf( 'none' );

		// Render title.

		_.each( styleType.split( ' ' ), function( className ) {
			classes += ' sep-' + className;
		} );

		wrapperClasses = ' fusion-title fusion-title-size-' + sizeArray[ size - 1 ] + classes;

		if ( marginTop ) {
			styles += 'margin-top:' + _.fusionGetValueWithUnit( marginTop ) + ';';
		}
		if ( marginBottom ) {
			styles += 'margin-bottom:' +  _.fusionGetValueWithUnit( marginBottom ) + ';';
		}

		if ( '' !== marginTop || '' !== marginBottom ) {
			headingStyles += 'margin:0;';
		}

		if ( false !== underlineOrNone ) {

			if ( -1 !== styleType.indexOf( 'underline' ) && sepColor ) {
				styles += 'border-bottom-color:' + sepColor + ';';
			} else if ( -1 !== styleType.indexOf( 'none' ) ) {
				classes += ' fusion-sep-none';
			}
		}

		output += '<div class="' + wrapperClasses + '" style="' + styles + '">';
		if ( false === underlineOrNone && 'right' === contentAlign ) {
			output += '<div class="title-sep-container">';
			output += '<div class="title-sep' + classes + '"></div>';
			output += '</div>';
		}

		output += '<h' + size + ' class="title-heading-' + contentAlign + '" style="' + headingStyles + '">';
		output += headingContent;
		output += '</h' + size + '>';

		if ( false === underlineOrNone && 'left' === contentAlign ) {
			output += '<div class="title-sep-container">';
			output += '<div class="title-sep' + classes + '"></div>';
			output += '</div>';
		}
		output += '</div>';

		return output;
	},

	/**
	 * Copy of our fusion_builder_visibility_atts() function in PHP.
	 *
	 * @since 2.0.0
	 * @param {string}        selection - The selection.
	 * @param {Object|string} attr - The attributes.
	 * @return {Object} The attributes modified to accomodate visibility options from the selection parameter.
	 */
	fusionVisibilityAtts: function( selection, attr ) {

		var allVisibilityValues = [
				'small-visibility',
				'medium-visibility',
				'large-visibility'
			],
			visibilityValues = allVisibilityValues,
			visibilityOptions;

		// If empty, show all.
		if ( '' === selection ) {
			selection = visibilityValues;
		}

		// If no is used, change that to all options selected, as fallback.
		if ( 'no' === selection ) {
			selection = visibilityValues;
		}

		// If yes is used, use all selections with mobile visibility removed.
		if ( 'yes' === selection ) {
			visibilityValues = visibilityValues.filter( function( e ) {
				return 'small-visibility' !== e;
			} );
			selection = visibilityValues;
		}

		// Make sure the selection is an array.
		if ( 'string' === typeof selection ) {
			selection = selection.split( ',' );
			_.each( selection, function( value, key ) {
				selection[ key ] = value.replace( new RegExp( ' ', 'g' ), '' );
			} );
		}

		visibilityOptions = allVisibilityValues;
		_.each( visibilityOptions, function( visibilityOption ) {
			if ( selection && -1 === selection.indexOf( visibilityOption ) ) {
				if ( 'object' === typeof attr ) {
					attr[ 'class' ] += ' fusion-no-' + visibilityOption;
				} else {
					attr += ' fusion-no-' + visibilityOption;
				}
			}
		} );

		return attr;
	},

	/**
	 * Returns the available animation types.
	 *
	 * @since 2.1
	 * @return {Object} Animation types.
	 */
	fusionGetAnimationTypes: function() {
		var animations = [
			'bounce',
			'bouncIn',
			'bounceInUp',
			'bounceInDown',
			'bounceInLeft',
			'bounceInRight',
			'fadeIn',
			'fadeInUp',
			'fadeInDown',
			'fadeInLeft',
			'fadeInRight',
			'flash',
			'rubberBand',
			'shake',
			'slideIn',
			'slideInUp',
			'slideInDown',
			'slideInLeft',
			'slideInRight',
			'zoomIn',
			'zoomInUp',
			'zoomInDown',
			'zoomInRight',
			'zoomInLeft',
			'flipinx',
			'flipiny',
			'lightspeedin'
		];

		return animations;
	},

	/**
	 * The FusionBuilder::animations() function from PHP, translated to JS.
	 *
	 * @since 2.0.0
	 * @param {Object}       args - The arguments.
	 * @param {string}       args.type - The animation type.
	 * @param {string}       args.direction - The animation direction.
	 * @param {string|number} args.speed - The animation speed, in seconds.
	 * @param {string}       args.offset - The animation offset.
	 * @return {Object} Animation attributes.
	 */
	fusionGetAnimations: function( args ) {
		var animationAttributes = {},
			directionSuffix,
			offset;

		args = _.defaults( args, {
			type: '',
			direction: 'left',
			speed: '0.1',
			offset: 'bottom-in-view'
		} );

		if ( args.type ) {

			animationAttributes.animation_class = 'fusion-animated';

			if ( 'static' === args.direction ) {
				args.direction = '';
			}

			if ( 'flash' !== args.type && 'shake' !== args.type && 'rubberBand' !== args.type && 'flipinx' !== args.type && 'flipiny' !== args.type && 'lightspeedin' !== args.type ) {
				directionSuffix = 'In' + args.direction.charAt( 0 ).toUpperCase() + args.direction.slice( 1 );
				args.type += directionSuffix;
			}

			animationAttributes[ 'data-animationType' ] = args.type;

			if ( args.speed ) {
				animationAttributes[ 'data-animationDuration' ] = args.speed;
			}
		}

		if ( args.offset ) {
			offset = args.offset;
			if ( 'top-into-view' === args.offset ) {
				offset = '100%';
			} else if ( 'top-mid-of-view' === args.offset ) {
				offset = '50%';
			}
			animationAttributes[ 'data-animationOffset' ] = offset;
		}

		return animationAttributes;

	},

	/**
	 * The FusionBuilder::font_awesome_name_handler() function from PHP, translated to JS.
	 *
	 * @since 2.0.0
	 * @param {string} icon - The icon we want.
	 * @return {string} - Returns the icon.
	 */
	fusionFontAwesome: function( icon ) {
		var oldIcons = {
				arrow: 'angle-right',
				asterik: 'asterisk',
				cross: 'times',
				'ban-circle': 'ban',
				'bar-chart': 'bar-chart-o',
				beaker: 'flask',
				bell: 'bell-o',
				'bell-alt': 'bell',
				'bitbucket-sign': 'bitbucket-square',
				'bookmark-empty': 'bookmark-o',
				building: 'building-o',
				'calendar-empty': 'calendar-o',
				'check-empty': 'square-o',
				'check-minus': 'minus-square-o',
				'check-sign': 'check-square',
				check: 'check-square-o',
				'chevron-sign-down': 'chevron-circle-down',
				'chevron-sign-left': 'chevron-circle-left',
				'chevron-sign-right': 'chevron-circle-right',
				'chevron-sign-up': 'chevron-circle-up',
				'circle-arrow-down': 'arrow-circle-down',
				'circle-arrow-left': 'arrow-circle-left',
				'circle-arrow-right': 'arrow-circle-right',
				'circle-arrow-up': 'arrow-circle-up',
				'circle-blank': 'circle-o',
				cny: 'rub',
				'collapse-alt': 'minus-square-o',
				'collapse-top': 'caret-square-o-up',
				collapse: 'caret-square-o-down',
				'comment-alt': 'comment-o',
				'comments-alt': 'comments-o',
				copy: 'files-o',
				cut: 'scissors',
				dashboard: 'tachometer',
				'double-angle-down': 'angle-double-down',
				'double-angle-left': 'angle-double-left',
				'double-angle-right': 'angle-double-right',
				'double-angle-up': 'angle-double-up',
				download: 'arrow-circle-o-down',
				'download-alt': 'download',
				'edit-sign': 'pencil-square',
				edit: 'pencil-square-o',
				'ellipsis-horizontal': 'ellipsis-h',
				'ellipsis-vertical': 'ellipsis-v',
				'envelope-alt': 'envelope-o',
				'exclamation-sign': 'exclamation-circle',
				'expand-alt': 'plus-square-o',
				expand: 'caret-square-o-right',
				'external-link-sign': 'external-link-square',
				'eye-close': 'eye-slash',
				'eye-open': 'eye',
				'facebook-sign': 'facebook-square',
				'facetime-video': 'video-camera',
				'file-alt': 'file-o',
				'file-text-alt': 'file-text-o',
				'flag-alt': 'flag-o',
				'folder-close-alt': 'folder-o',
				'folder-close': 'folder',
				'folder-open-alt': 'folder-open-o',
				food: 'cutlery',
				frown: 'frown-o',
				fullscreen: 'arrows-alt',
				'github-sign': 'github-square',
				group: 'users',
				'h-sign': 'h-square',
				'hand-down': 'hand-o-down',
				'hand-left': 'hand-o-left',
				'hand-right': 'hand-o-right',
				'hand-up': 'hand-o-up',
				hdd: 'hdd-o',
				'heart-empty': 'heart-o',
				hospital: 'hospital-o',
				'indent-left': 'outdent',
				'indent-right': 'indent',
				'info-sign': 'info-circle',
				keyboard: 'keyboard-o',
				legal: 'gavel',
				lemon: 'lemon-o',
				lightbulb: 'lightbulb-o',
				'linkedin-sign': 'linkedin-square',
				meh: 'meh-o',
				'microphone-off': 'microphone-slash',
				'minus-sign-alt': 'minus-square',
				'minus-sign': 'minus-circle',
				'mobile-phone': 'mobile',
				moon: 'moon-o',
				move: 'arrows',
				off: 'power-off',
				'ok-circle': 'check-circle-o',
				'ok-sign': 'check-circle',
				ok: 'check',
				'paper-clip': 'paperclip',
				paste: 'clipboard',
				'phone-sign': 'phone-square',
				picture: 'picture-o',
				'pinterest-sign': 'pinterest-square',
				'play-circle': 'play-circle-o',
				'play-sign': 'play-circle',
				'plus-sign-alt': 'plus-square',
				'plus-sign': 'plus-circle',
				pushpin: 'thumb-tack',
				'question-sign': 'question-circle',
				'remove-circle': 'times-circle-o',
				'remove-sign': 'times-circle',
				remove: 'times',
				reorder: 'bars',
				'resize-full': 'expand',
				'resize-horizontal': 'arrows-h',
				'resize-small': 'compress',
				'resize-vertical': 'arrows-v',
				'rss-sign': 'rss-square',
				save: 'floppy-o',
				screenshot: 'crosshairs',
				'share-alt': 'share',
				'share-sign': 'share-square',
				share: 'share-square-o',
				'sign-blank': 'square',
				signin: 'sign-in',
				signout: 'sign-out',
				smile: 'smile-o',
				'sort-by-alphabet-alt': 'sort-alpha-desc',
				'sort-by-alphabet': 'sort-alpha-asc',
				'sort-by-attributes-alt': 'sort-amount-desc',
				'sort-by-attributes': 'sort-amount-asc',
				'sort-by-order-alt': 'sort-numeric-desc',
				'sort-by-order': 'sort-numeric-asc',
				'sort-down': 'sort-asc',
				'sort-up': 'sort-desc',
				stackexchange: 'stack-overflow',
				'star-empty': 'star-o',
				'star-half-empty': 'star-half-o',
				sun: 'sun-o',
				'thumbs-down-alt': 'thumbs-o-down',
				'thumbs-up-alt': 'thumbs-o-up',
				time: 'clock-o',
				trash: 'trash-o',
				'tumblr-sign': 'tumblr-square',
				'twitter-sign': 'twitter-square',
				unlink: 'chain-broken',
				upload: 'arrow-circle-o-up',
				'upload-alt': 'upload',
				'warning-sign': 'exclamation-triangle',
				'xing-sign': 'xing-square',
				'youtube-sign': 'youtube-square',
				'zoom-in': 'search-plus',
				'zoom-out': 'search-minus'
			},
			faIcon = icon;

		if ( '' !== icon ) {

			// Custom icon is used so we need to remove our prefix.
			if ( 'fusion-prefix-' === icon.substr( 0, 14 ) ) {
				return icon.replace( 'fusion-prefix-', '' );
			}

			// AWB icon is used.
			if ( 'awb-icon-' === icon.substr( 0, 9 ) ) {
				return icon;
			}

			// FA icon, but we need to handle BC.
			if ( 'icon-' === icon.substr( 0, 5 ) || 'fa-' !== icon.substr( 0, 3 ) ) {
				icon = icon.replace( 'icon-', 'fa-' );

				if ( 'undefined' !== typeof oldIcons[ icon.replace( 'fa-', '' ) ] ) {
					faIcon = 'fa-' + oldIcons[ icon.replace( 'fa-', '' ) ];
				} else if ( 'fa-' !== icon.substr( 0, 3 ) ) {
					faIcon = 'fa-' + icon;
				}
			}

			if ( -1 === icon.trim().indexOf( ' ' ) ) {
				faIcon = 'fa ' + icon;
			}
		}

		return faIcon;
	},

	/**
	 * The FusionBuilder::validate_shortcode_attr_value() function from PHP, translated to JS.
	 *
	 * @since 2.0.0
	 * @param {string} value - The value.
	 * @param {string} acceptedUnit - The unit we're accepting.
	 * @param {boolean}   bcSupport - Should we add backwards-compatibility support?
	 * @return {string|false} - Returns the validated value.
	 */
	fusionValidateAttrValue: function( value, acceptedUnit, bcSupport ) {
		var validatedValue = '',
			numericValue,
			unit;

		bcSupport = 'undefined' !== typeof bcSupport ? bcSupport : true;
		value = String( value );
		if ( '' !== value ) {
			value        = value.trim();
			numericValue = parseFloat( value );
			unit         = value.replace( numericValue, '' );

			if ( 'undefined' === typeof acceptedUnit || '' === acceptedUnit ) {
				validatedValue = numericValue;

			} else if ( '' === unit ) {

				// Add unit if it's required.
				validatedValue = numericValue + acceptedUnit;
			} else if ( bcSupport || unit === acceptedUnit ) {

				// If unit was found use original value. BC support.
				validatedValue = value;
			} else {
				validatedValue = false;
			}
		}

		return validatedValue;
	},

	/**
	 * Clone of fusion_builder_get_video_provider.
	 *
	 * @since 2.0.0
	 * @param {string} videoString - The URL of the video.
	 * @return {Object} - Returns an object formatted {type: (string) The video-type, id: (string) The video ID }.
	 */
	fusionGetVideoProvider: function( videoString ) {

		var videoId,
			match;

		videoString = videoString.trim();

		// Check for YouTube.
		videoId = false;

		if ( match = videoString.match( /youtube\.com\/watch\?v=([^\&\?\/]+)/ ) ) { // eslint-disable-line no-cond-assign
			if ( 'undefined' !== typeof match[ 1 ] ) {
				videoId =  match[ 1 ];
			}
		} else if ( match = videoString.match( /youtube\.com\/embed\/([^\&\?\/]+)/ ) ) { // eslint-disable-line no-cond-assign
			if ( 'undefined' !== typeof match[ 1 ] ) {
				videoId =  match[ 1 ];
			}
		} else if ( match = videoString.match( /youtube\.com\/v\/([^\&\?\/]+)/ ) ) { // eslint-disable-line no-cond-assign
			if ( 'undefined' !== typeof match[ 1 ] ) {
				videoId =  match[ 1 ];
			}
		} else if ( match = videoString.match( /youtu\.be\/([^\&\?\/]+)/ ) ) { // eslint-disable-line no-cond-assign
			if ( 'undefined' !== typeof match[ 1 ] ) {
				videoId =  match[ 1 ];
			}
		}

		if ( false !== videoId ) {
			return {
				type: 'youtube',
				id: videoId
			};
		}

		// Check for Vimeo.
		if ( match = videoString.match( /vimeo\.com\/(\w*\/)*(\d+)/ ) ) { // eslint-disable-line no-cond-assign
			if ( 1 < match.length ) {
				return {
					type: 'vimeo',
					id: match[ match.length - 1 ]
				};
			}
		}
		if ( match = videoString.match( /^\d+$/ ) ) { // eslint-disable-line no-cond-assign
			if ( 'undefined' !== typeof match[ 0 ] ) {
				return {
					type: 'vimeo',
					id: match[ 0 ]
				};
			}
		}

		return {
			type: 'youtube',
			id: videoString
		};
	},

	/**
	 * JS clone of fusion_builder_check_value.
	 * If value is not in pixels or percent, appends 'px'.
	 *
	 * @since 2.0.0
	 * @param {string} value - The value.
	 * @return {string} - Returns the value.
	 */
	fusionCheckValue: function( value ) {
		if ( -1 === value.indexOf( '%' ) && -1 === value.indexOf( 'px' ) ) {
			value = value + 'px';
		}
		return value;
	},

	/**
	 * JS clone of get_value_with_unit.
	 *
	 * @param {string|number} value - The value.
	 * @param {string}           unit - The unit.
	 * @param {string}           unitHandling - Can be 'add'(default) or 'force_replace'.
	 * @return {string} - Returns the value with units.
	 */
	fusionGetValueWithUnit: function( value, unit, unitHandling ) {

		var rawValues,
			rawValue,
			values;

		unit         = 'undefined' !== typeof unit ? unit : 'px';
		unitHandling = 'undefined' !== typeof unitHandling ? unitHandling : 'add';

		rawValues = [];

		// Trim the value.
		value = 'undefined' === typeof value ? '' : value;
		value = value.toString().trim();
		if ( -1 !== jQuery.inArray( value, [ 'auto', 'inherit', 'initial', '' ] ) ) {
			return value;
		}

		// Return empty if there are no numbers in the value.
		// Prevents some CSS errors.
		if ( ! ( /\d/ ).test( value ) ) {
			return;
		}

		// Explode if has multiple values.
		values = value.split( ' ' );
		if ( 1 < values.length ) {
			_.each( values, function( val ) {
				rawValue = parseFloat( val );

				if ( ! isNaN( rawValue ) ) {
					// Only == here deliberately.
					if ( val == rawValue ) {
						val = rawValue + unit;
					} else if ( 'force_replace' === unitHandling ) {
						val = rawValue + unit;
					}
				}
				rawValues.push( val );
			} );

			return rawValues.join( ' ' );

		}
		rawValue = parseFloat( value );

		// Only == here deliberately.
		if ( value == rawValue ) {
			return rawValue + unit;
		}
		if ( 'force_replace' === unitHandling ) {
			return rawValue + unit;
		}

		return value;
	},

	/**
	 * Returns a single side dimension.
	 *
	 * Copy of the PHP fusion_builder_single_dimension function.
	 *
	 * @param {Object} dimensions - The dimensions object{top:'',buttom:'',left:'',right:''}.
	 * @param {string} direction - Which one do we want? left/right/top/bottom.
	 * @return {string} - Returns a single dimension from the array.
	 */
	fusionSingleDimension: function( dimensions, direction ) {
		dimensions = dimensions.split( ' ' );

		if ( 4 === dimensions.length ) {
			if ( 'top' === direction ) {
				return dimensions[ 0 ];
			} else if ( 'right' === direction ) {
				return dimensions[ 1 ];
			} else if ( 'bottom' === direction ) {
				return dimensions[ 2 ];
			} else if ( 'left' === direction ) {
				return dimensions[ 3 ];
			}
		} else if ( 3 === dimensions.length ) {
			if ( 'top' === direction ) {
				return dimensions[ 0 ];
			} else if ( 'right' === direction || 'left' === direction ) {
				return dimensions[ 1 ];
			} else if ( 'bottom' === direction ) {
				return dimensions[ 2 ];
			}
		} else if ( 2 === dimensions.length ) {
			if ( 'top' === direction || 'bottom' === direction ) {
				return dimensions[ 0 ];
			} else if ( 'right' === direction || 'left' === direction ) {
				return dimensions[ 1 ];
			}
		}
		return dimensions[ 0 ];
	},

	/**
	 * Get the attributes for masonry.
	 *
	 * @since 2.0.0
	 * @param {Object}       data - The data.
	 * @param {string|number} data.blog_grid_column_spacing - Column spacing in pixels.
	 * @param {string}       data.element_orientation_class - The orientation class (fusion-element-portrain, fusion-element-landscape etc).
	 * @param {string}       data.timeline_color - The timeline color.
	 * @param {string}       data.masonry_attribute_style - Masonry styles.
	 * @return {Object} - Returns the masonry attributes.
	 */
	fusionGetMasonryAttribute: function( data ) {
		var masonryColumnOffset,
			masonryColumnSpacing,
			masonryAttributes = {};

		masonryColumnOffset = ' - ' + ( parseFloat( data.blog_grid_column_spacing ) / 2 ) + 'px';
		if ( 'string' === typeof data.element_orientation_class && -1 !== data.element_orientation_class.indexOf( 'fusion-element-portrait' ) ) {
			masonryColumnOffset = '';
		}

		masonryColumnSpacing = ( parseFloat( data.blog_grid_column_spacing ) ) + 'px';

		// Calculate the correct size of the image wrapper container, based on orientation and column spacing.
		if ( 'transparent' !== data.timeline_color && 0 !== jQuery.AWB_Color( data.timeline_color ).alpha() ) {

			masonryColumnOffset = ' - ' + ( parseFloat( data.blog_grid_column_spacing ) / 2 ) + 'px';
			if ( 'string' === typeof data.element_orientation_class && -1 !== data.element_orientation_class.indexOf( 'fusion-element-portrait' ) ) {
				masonryColumnOffset = ' + 4px';
			}

			masonryColumnSpacing = ( parseFloat( data.blog_grid_column_spacing ) - 2 ) + 'px';
			if ( 'string' === typeof data.element_orientation_class && -1 !== data.element_orientation_class.indexOf( 'fusion-element-landscape' ) ) {
				masonryColumnSpacing = ( parseFloat( data.blog_grid_column_spacing ) - 6 ) + 'px';
			}
		}

		// Calculate the correct size of the image wrapper container, based on orientation and column spacing.
		masonryAttributes[ 'class' ] = 'fusion-masonry-element-container';
		masonryAttributes.style = data.masonry_attribute_style + 'padding-top:calc((100% + ' + masonryColumnSpacing + ') * ' + data.element_base_padding + masonryColumnOffset + ');';

		return masonryAttributes;
	},

	/**
	 * Combination of first featured image and rollover.
	 *
	 * @since 2.0.0
	 * @param {Object}         data - The data.
	 * @param {string}         data.layout - The layout.
	 * @param {string}         data.masonry_data - The masonry data.
	 * @param {string|boolean} data.enable_rollover - Should we enable the rollover?
	 * @param {string}         data.display_rollover - Should we display the rollover? (yes|no|force_yes).
	 * @param {Object}         data.featured_images - The featured images.
	 * @param {string}         data.image_rollover_icons - no|zoom|link|linkzoom.
	 * @param {string}         data.post_type - The post-type.
	 * @param {string|number}  data.post_id - The post-ID.
	 * @param {string}         data.icon_permalink - URL.
	 * @param {string}         data.link_target - Leave empty or use target="_blank".
	 * @param {string}         data.icon_permalink_title - The icon permalink title.
	 * @param {string}         data.full_image - URL.
	 * @param {string}         data.data_rel - Used in data-rel="".
	 * @param {string}         data.data_title - Used in data-title="".
	 * @param {string}         data.data_caption - Used in data-caption="".
	 * @param {string}         data.lightbox_content - The contents of the lightbox.
	 * @param {string|boolean} data.display_post_title - Should we display the post-title?
	 * @param {string}         data.permalink - URL.
	 * @param {string}         data.title - The title.
	 * @param {string|boolean} data.display_post_categories - Should we display the post categories?
	 * @param {string}         data.terms - The post category terms (HTML).
	 * @param {boolean}        data.display_woo_rating - SHould we display Woo rating?
	 * @param {string}         data.rating - The rating (HTML).
	 * @param {boolean}        data.display_woo_price - Should we display Woo Prices?
	 * @param {string}         data.price - The price (HTML).
	 * @param {boolean}        data.display_woo_buttons - Should we display the Woo buttons?
	 * @param {string}         data.buttons - The buttons (HTML).
	 * @return {string} - Returns the template.
	 */
	fusionFeaturedImage: function( data ) {
		var featuredImageTemplate = FusionPageBuilder.template( jQuery( '#tmpl-featured-image' ).html() ),
			attributes = {};

		if ( 'object' !== typeof data || 'undefined' === typeof data.featured_images ) {
			return '';
		}
		attributes.data = data;
		return featuredImageTemplate( attributes );
	},

	/**
	 * Get element orientation class based on image dimensions and ratio and widthDouble params.
	 *
	 * @since 2.0.0
	 * @param {Object} attachment - Image object.
	 * @param {number} attachment.imageWidth - Image width.
	 * @param {number} attachment.imageHeight - Image height.
	 * @param {number} ratio - Height / Width ratio. Portrait images have larger height / width ratio.
	 * @param {number} widthDouble - Wider images are considered as 2x2.
	 * @return {string} - Returns the element class.
	 */
	fusionGetElementOrientationClass: function( attachment, ratio, widthDouble ) {
		var elementClass = 'fusion-element-grid',
			fallbackRatio = 0.8,
			lowerLimit,
			upperLimit;

		if ( 'undefined' !== typeof attachment.imageWidth && 'undefined' !== typeof attachment.imageHeight ) {

			// Fallback to legacy calcs of Avada 5.4.2 or earlier.
			if ( '1.0' === ratio ) {
				lowerLimit = ( fallbackRatio / 2 ) + ( fallbackRatio / 4 );
				upperLimit = ( fallbackRatio * 2 ) - ( fallbackRatio / 2 );

				if ( lowerLimit > attachment.imageHeight / attachment.imageWidth ) {

					// Landscape image.
					elementClass = 'fusion-element-landscape';
				} else if ( upperLimit < attachment.imageHeight / attachment.imageWidth ) {

					// Portrait image.
					elementClass = 'fusion-element-portrait';
				} else if ( attachment.imageWidth > widthDouble ) {

					// 2x2 image.
					elementClass = 'fusion-element-landscape fusion-element-portrait';
				}
			} else if ( ratio < attachment.imageWidth / attachment.imageHeight ) {

				// Landscape image.
				elementClass = 'fusion-element-landscape';

			} else if ( ratio < attachment.imageHeight / attachment.imageWidth ) {

				// Portrait image.
				elementClass = 'fusion-element-portrait';
			} else if ( attachment.imageWidth > widthDouble ) {

				// 2x2 image.
				elementClass = 'fusion-element-landscape fusion-element-portrait';
			}
		}

		return elementClass;
	},

	/**
	 * Get base element padding based on orientation CSS class.
	 *
	 * @since 2.0.0
	 * @param {string} elementOrientationClass - CSS class
	 * @return {number} - Returns the padding.
	 */
	fusionGetElementBasePadding: function( elementOrientationClass ) {
		var fusionElementGridPadding = 0.8,
			masonryElementPadding = {
				'fusion-element-grid': fusionElementGridPadding,
				'fusion-element-landscape': fusionElementGridPadding / 2,
				'fusion-element-portrait': fusionElementGridPadding * 2
			};

		if ( 'undefined' !== typeof masonryElementPadding[ elementOrientationClass ] ) {
			fusionElementGridPadding = masonryElementPadding[ elementOrientationClass ];
		}

		return fusionElementGridPadding;
	},

	/**
	 * JS copy of fusion_builder_render_post_metadata.
	 *
	 * @since 2.0.0
	 * @param {string}         layout - The layout.
	 * @param {Object}         settings - The settings.
	 * @param {boolean|string} settings.post_meta - Should we display the post-meta?
	 * @param {boolean|string} settings.post_meta_author - Should we display the author?
	 * @param {boolean|string} settings.post_meta_date - Should we display the date?
	 * @param {boolean|string} settings.post_meta_cats - Should we display the categories?
	 * @param {boolean|string} settings.post_meta_tags - Should we display the tags?
	 * @param {boolean|string} settings.post_meta_comments - Should we display comments?
	 * @param {boolean|string} settings.disable_date_rich_snippet_pages - Should we disable the date rich snippet?
	 * @param {Object}         data - The data.
	 * @param {string}         data.post_meta - yes|no.
	 * @param {string}         data.author_post_link - The link to the post-author (HTML, not just URL).
	 * @param {string}         data.formatted_date - Formatted date (HTML).
	 * @param {string}         data.categories - The categories (HTML).
	 * @param {string}         data.tags - The Tags (HTML)
	 * @param {string}         data.comments - The comments (HTML)
	 * @param {string}         data.disable_date_rich_snippet_pages - Disable date rich snippets?
	 * @return {string} - Returns HTML.
	 */
	fusionRenderPostMetadata: function( layout, settings, data ) {

		var metadata = '',
			author   = '',
			date     = '',
			output   = '',
			dateMarkup;

		// Check if meta data is enabled.
		if ( 'undefined' === typeof data ) {
			return;
		}

		if ( ( settings.post_meta && 'no' !== data.post_meta ) || ( ! settings.post_meta && 'yes' === data.post_meta ) ) {

			// For alternate, grid and timeline layouts return empty single-line-meta if all meta data for that position is disabled.
			if ( -1 !== jQuery.inArray( layout, [ 'alternate', 'grid_timeline' ] ) && ! settings.post_meta_author && ! settings.post_meta_date && ! settings.post_meta_cats && ! settings.post_meta_tags && ! settings.post_meta_comments ) {
				return '';
			}

			// Render author meta data.
			if ( settings.post_meta_author ) {

				// Check if rich snippets are enabled.
				if ( ! settings.disable_date_rich_snippet_pages ) {
					metadata += 'By <span>' + data.author_post_link + '</span>';
				} else {
					metadata += 'By <span class="vcard"><span class="fn">' + data.author_post_link + '</span></span>';
				}
				metadata += '<span class="fusion-inline-sep">|</span>';
			}

			// Render the updated meta data or at least the rich snippet if enabled.
			if ( settings.post_meta_date ) {
				metadata  += _.fusionRenderRichSnippets( data, false, false, true );
				dateMarkup = '<span>' + data.formatted_date + '</span><span class="fusion-inline-sep">|</span>';
				metadata  += dateMarkup;
			}

			// Render rest of meta data.
			// Render categories.
			if ( settings.post_meta_cats ) {

				if ( data.categories ) {
					metadata += ( settings.post_meta_tags ) ? 'Categories: ' + data.categories : data.categories;
					metadata += '<span class="fusion-inline-sep">|</span>';
				}
			}

			// Render tags.
			if ( settings.post_meta_tags ) {

				if ( data.tags ) {
					metadata += '<span class="meta-tags">' + window.fusionBuilderText.tags.replace( '%s', data.tags ) + '</span><span class="fusion-inline-sep">|</span>';
				}
			}

			// Render comments.
			if ( settings.post_meta_comments && 'grid_timeline' !== layout ) {
				metadata += '<span class="fusion-comments">' + data.comments + '</span>';
			}

			// Render the HTML wrappers for the different layouts.
			if ( metadata ) {
				metadata = author + date + metadata;

				if ( 'single' === layout ) {
					output += '<div class="fusion-meta-info"><div class="fusion-meta-info-wrapper">' + metadata + '</div></div>';
				} else if ( -1 !== jQuery.inArray( layout, [ 'alternate', 'grid_timeline' ] ) ) {
					output += '<p class="fusion-single-line-meta">' + metadata + '</p>';
				} else if ( 'recent_posts' === layout ) {
					output += metadata;
				} else {
					output += '<div class="fusion-alignleft">' + metadata + '</div>';
				}
			} else {
				output += author + date;
			}
		} else if ( data.disable_date_rich_snippet_pages ) {

			// Render author and updated rich snippets for grid and timeline layouts.
			output += _.fusionRenderRichSnippets( data, false );
		}

		return output;
	},

	/**
	 * JS Copy of fusion_builder_render_rich_snippets_for_pages.
	 *
	 * @since 2.0.0
	 * @param {Object}  data - The data.
	 * @param {boolean} data.disable_date_rich_snippet_pages Should we display the rich snippets?
	 * @param {string}  data.title - The title.
	 * @param {string}  data.the_author_posts_link The link to the author (HTML, not just the URL).
	 * @param {string}  data.get_the_modified_time - The modified timestamp.
	 * @return {string} - Returns the output.
	 */
	fusionRenderRichSnippets: function( data ) {
		var output = '';

		if ( 'undefined' === typeof data ) {
			return;
		}
		if ( data.disable_date_rich_snippet_pages ) {
			output = '';
		}
		return output;
	},

	/**
	 * JS copy of new-slideshow-blog-shortcode.
	 *
	 * @since 2.0.0
	 * @param {Object} data - The data.
	 * @param {string} data.layout - The layout.
	 * @param {string} data.featured_image_width - The featured image width.
	 * @param {string} data.id - The ID.
	 * @param {string} data.featured_image_height - The featured image height.
	 * @param {string} data.thumbnail - The thumbnail.
	 * @param {string} data.video - The video
	 * @param {Object} data.image_data - The image data.
	 * @param {Object} data.multiple_featured - Multiple featured images data.
	 * @param {string} data.permalink - The permalink (URL).
	 * @param {string} data.title - The title.
	 * @param {string} data.image_size - The image size.
	 * @return {string}
	 */
	fusionGetBlogSlideshow: function( data ) {
		var slideshowTemplate = FusionPageBuilder.template( jQuery( '#tmpl-new-slideshow-blog-shortcode' ).html() ),
			attributes        = {};

		if ( 'object' !== typeof data ) {
			return '';
		}
		attributes.data = data;
		return slideshowTemplate( attributes );
	},

	/**
	 * Ability to change length of content and display correct contents.
	 *
	 * @since 2.0.0
	 * @param {Object}  data - The data.
	 * @param {string}  data.read_more - The read more text.
	 * @param {string}  data.full_content - The full content.
	 * @param {string}  data.excerpt - The excerpt.
	 * @param {string}  data.excerpt_stripped - Stripped excerpt.
	 * @param {string}  data.excerpt_base - Defaults to 'characters'.
	 * @param {string}  excerpt - Do we want excerpt (yes/no)?
	 * @param {number} excerptLength - How long?
	 * @param {boolean} stripHtml - Should we strip HTML?
	 * @return {string}
	 */
	fusionGetFixedContent: function( data, excerpt, excerptLength, stripHtml ) {
		var content,
			readMoreContent = '';

		excerpt        = 'undefined' !== typeof excerpt ? excerpt : 'no';
		excerptLength  = 'undefined' !== typeof excerptLength ? excerptLength : 55;
		stripHtml      = 'undefined' !== typeof stripHtml ? stripHtml : false;
		stripHtml      = ( 'yes' === stripHtml || stripHtml || '1' == stripHtml );
		data.read_more = data.hasOwnProperty( 'read_more' ) ? data.read_more : '';

		// Return full contents.
		if ( 'no' === excerpt ) {
			return data.full_content;
		}

		// Set correct stripped data.
		content = ( stripHtml ) ? data.excerpt_stripped : data.excerpt;

		// It has a read more, remove it.
		content = content
			.replace( /\[/g, '&#91;' )
			.replace( /\]/g, '&#93;' )
			.replace( /\.\.\./g, '&#8230;' );

		readMoreContent = ' ' + data.read_more
			.replace( /\[/g, '&#91;' )
			.replace( /\]/g, '&#93;' )
			.replace( /\.\.\./g, '&#8230;' )
			.trim();

		if ( -1 !== content.indexOf( readMoreContent ) ) {
			content  = content.replace( readMoreContent, '' );
		}

		if ( 'characters' === data.excerpt_base.toLowerCase() ) {
			if ( excerptLength < content.length ) {
				content = content.substring( 0, excerptLength );
			}
		} else {
			content = content.split( ' ' ).splice( 0, excerptLength ).join( ' ' );
		}

		// Add read more.
		content += readMoreContent;

		return _.fusionFixHtml( content );
	},

	/**
	 * Helper method used in getFixedContent.
	 *
	 * @since 2.0.0
	 * @param {string} html - The html string.
	 * @return {string}
	 */
	fusionFixHtml: function( html ) {
		var div = document.createElement( 'div' );
		div.innerHTML = html;
		return ( div.innerHTML );
	},

	/**
	 * Capitalize the 1st letter.
	 *
	 * @since 2.0.0
	 * @param {string} string - The string we want to modify.
	 * @return {string}
	 */
	fusionUcFirst: function( string ) {
		return string.charAt( 0 ).toUpperCase() + string.slice( 1 );
	},

	/**
	 * JS port of PHP's rawurlencode function.
	 *
	 * @since 2.0.0
	 * @param {string} string - The URL.
	 * @return {string}
	 */
	fusionRawUrlEncode: function( string ) {
		string = ( string + '' );

		return encodeURIComponent( string )
			.replace( /!/g, '%21' )
			.replace( /'/g, '%27' )
			.replace( /\(/g, '%28' )
			.replace( /\)/g, '%29' )
			.replace( /\*/g, '%2A' );
	},

	/**
	 * Auto calculate accent color.
	 * copy of fusion_auto_calculate_accent_color from PHP.
	 *
	 * @since 2.0.0
	 * @param {string} color - The color.
	 * @return {string}
	 */
	fusionAutoCalculateAccentColor: function( color ) {
		var colorObj  = jQuery.AWB_Color( color ),
			lightness = parseInt( colorObj.lightness() * 100, 10 );

		if ( 0 < lightness ) { // Not black.
			if ( 50 <= lightness ) {
				return colorObj.lightness( lightness / 200 ).toRgbaString();
			}
			return colorObj.lightness( lightness / 50 ).toRgbaString();
		}
		return colorObj.lightness( 70 ).toRgbaString();
	},

	/**
	 * JS copy of fusion_builder_build_social_links.
	 *
	 * @since 2.0.0
	 * @param {Array|Object} socialNetworks - The social networks array.
	 * @param {string}       functionName - Callable function-name.
	 * @param {Object}       params - The parameters.
	 * @param {number}      i - Not used?
	 * @return {string}
	 */
	fusionBuildSocialLinks: function( socialNetworks, functionName, params, i ) {

		var useBrandColors    = false,
			icons             = '',
			shortcodeDefaults = {},
			boxColors,
			iconColors,
			numOfIconColors,
			numOfBoxColors,
			socialNetworksCount,
			k = 0;

		socialNetworks = ! _.isUndefined( socialNetworks ) ? socialNetworks : '';
		i              = ! _.isUndefined( i ) ? i : 0;

		if ( ! _.isUndefined( params.social_icon_boxed ) ) {
			params.icons_boxed = params.social_icon_boxed;
		}

		if ( '' != socialNetworks && Array.isArray( socialNetworks ) ) {

			// Add compatibility for different key names in shortcodes.
			_.each( params, function( value, key ) {
				key = ( 'social_icon_boxed'        === key ) ? 'icons_boxed' : key;
				key = ( 'social_icon_colors'       === key ) ? 'icon_colors' : key;
				key = ( 'social_icon_boxed_colors' === key ) ? 'box_colors'  : key;
				key = ( 'social_icon_color_type'   === key ) ? 'color_type'  : key;

				shortcodeDefaults[ key ] = value;
			} );

			// Check for icon color type.
			if ( 'brand' === shortcodeDefaults.color_type ) {
				useBrandColors = true;

				boxColors = _.fusionSocialIcons( true, true );

				// Backwards compatibility for old social network names.
				boxColors.mail = {
					label: 'Email Address',
					color: '#000000'
				};
				iconColors = {};

			} else {

				// Custom social icon colors.
				iconColors = ( 'undefined' !== typeof shortcodeDefaults.icon_colors ) ? shortcodeDefaults.icon_colors.split( '|' ) : '';
				boxColors  = ( 'undefined' !== typeof shortcodeDefaults.box_colors ) ? shortcodeDefaults.box_colors.split( '|' ) : '';

				numOfIconColors = iconColors.length;
				numOfBoxColors  = boxColors.length;

				socialNetworksCount = socialNetworks.length;

				for ( k = 0; k < socialNetworksCount; k++ ) {
					if ( 1 === numOfIconColors ) {
						iconColors[ k ] = iconColors[ 0 ];
					}
					if ( 1 === numOfBoxColors ) {
						boxColors[ k ] = boxColors[ 0 ];
					}
				}
			}

			// Process social networks.
			_.each( socialNetworks, function( value ) {

				_.each( value, function( link, network ) {
					var iconOptions;

					if ( 'custom' === network && link ) {

						_.each( link, function( url, customKey ) {
							var customIconBoxColor = '',
								socialMediaIcons,
								width,
								height;

							if ( 'yes' === params.icons_boxed ) {

								customIconBoxColor = i < boxColors.length ? boxColors[ i ] : '';
								if ( true === useBrandColors ) {
									customIconBoxColor = ( boxColors[ network ].color ) ? boxColors[ network ].color : '';
								}
							}

							socialMediaIcons = params.social_media_icons;

							if ( ! _.isObject( socialMediaIcons ) ) {
								socialMediaIcons = {};
							}
							if ( _.isUndefined( socialMediaIcons.custom_title ) ) {
								socialMediaIcons.custom_title = {};
							}
							if ( _.isUndefined( socialMediaIcons.custom_source ) ) {
								socialMediaIcons.custom_source = {};
							}
							if ( _.isUndefined( socialMediaIcons.custom_title[ customKey ] ) ) {
								socialMediaIcons.custom_title[ customKey ] = '';
							}
							if ( _.isUndefined( socialMediaIcons.custom_source[ customKey ] ) ) {
								socialMediaIcons.custom_source[ customKey ] = '';
							}
							if ( _.isUndefined( socialMediaIcons.icon_mark[ customKey ] ) ) {
								socialMediaIcons.icon_mark[ customKey ] = '';
							}

							iconOptions = {
								social_network: socialMediaIcons.custom_title[ customKey ],
								social_link: url,
								icon_color: i < iconColors.length ? iconColors[ i ] : '',
								box_color: customIconBoxColor,
								icon_mark: socialMediaIcons.icon_mark[ customKey ].replace( 'fusion-prefix-', '' )
							};
							if ( _.isFunction( functionName ) ) {
								iconOptions = functionName( iconOptions, params );
							}
							icons += '<a ' + _.fusionGetAttributes( iconOptions ) + '>';

							if ( _.isEmpty( socialMediaIcons.icon_mark[ customKey ] ) ) {
								icons += '<img';

								if ( ! _.isUndefined( socialMediaIcons.custom_source[ customKey ].url ) ) {
									icons += ' src="' + socialMediaIcons.custom_source[ customKey ].url + '"';
								}
								if ( ! _.isUndefined( socialMediaIcons.custom_title[ customKey ] ) && '' != socialMediaIcons.custom_title[ customKey ] ) {
									icons += ' alt="' + socialMediaIcons.custom_title[ customKey ] + '"';
								}
								if ( ! _.isUndefined( socialMediaIcons.custom_source[ customKey ].width ) && socialMediaIcons.custom_source[ customKey ].width ) {
									width = parseInt( socialMediaIcons.custom_source[ customKey ].width, 10 );
									icons += ' width="' + width + '"';
								}
								if ( 'undefined' !== socialMediaIcons.custom_source[ customKey ].height && socialMediaIcons.custom_source[ customKey ].height ) {
									height = parseInt( socialMediaIcons.custom_source[ customKey ].height, 10 );
									icons += ' height="' + height + '"';
								}
								icons += ' />';
							}

							icons += '</a>';
						} );
					} else {
						if ( true == useBrandColors ) {
							iconOptions = {
								social_network: network,
								social_link: link,
								icon_color: ( 'yes' === params.icons_boxed ) ? '#ffffff' : boxColors[ network ].color,
								box_color: ( 'yes' === params.icons_boxed ) ? boxColors[ network ].color : ''
							};

						} else {
							iconOptions = {
								social_network: network,
								social_link: link,
								icon_color: i < iconColors.length ? iconColors[ i ] : '',
								box_color: i < boxColors.length ? boxColors[ i ] : ''
							};
						}
						if ( _.isFunction( functionName ) ) {
							iconOptions = functionName( iconOptions, params );
						}
						icons += '<a ' + _.fusionGetAttributes( iconOptions ) + '></a>';
					}
					i++;
				} );
			} );
		}

		return icons;
	},

	/**
	 * JS copy of Fusion_Data::fusion_social_icons
	 *
	 * @since 2.0.0
	 * @param {boolean} custom - Do we want the custom network?
	 * @param {boolean} colors - Do we want the colors?
	 * @return {Object}
	 */
	fusionSocialIcons: function( custom, colors ) {

		var networks,
			simpleNetworks;

		custom = ! _.isUndefined( custom ) ? custom : true;
		colors = ! _.isUndefined( colors ) ? colors : false;

		networks = {
			blogger: {
				label: 'Blogger',
				color: '#f57d00'
			},
			deviantart: {
				label: 'Deviantart',
				color: '#4dc47d'
			},
			discord: {
				label: 'Discord',
				color: '#26262B'
			},
			digg: {
				label: 'Digg',
				color: '#000000'
			},
			dribbble: {
				label: 'Dribbble',
				color: '#ea4c89'
			},
			dropbox: {
				label: 'Dropbox',
				color: '#007ee5'
			},
			facebook: {
				label: 'Facebook',
				color: '#3b5998'
			},
			flickr: {
				label: 'Flickr',
				color: '#0063dc'
			},
			forrst: {
				label: 'Forrst',
				color: '#5b9a68'
			},
			instagram: {
				label: 'Instagram',
				color: '#3f729b'
			},
			linkedin: {
				label: 'LinkedIn',
				color: '#0077b5'
			},
			myspace: {
				label: 'Myspace',
				color: '#000000'
			},
			paypal: {
				label: 'Paypal',
				color: '#003087'
			},
			pinterest: {
				label: 'Pinterest',
				color: '#bd081c'
			},
			reddit: {
				label: 'Reddit',
				color: '#ff4500'
			},
			rss: {
				label: 'RSS',
				color: '#f26522'
			},
			skype: {
				label: 'Skype',
				color: '#00aff0'
			},
			soundcloud: {
				label: 'Soundcloud',
				color: '#ff8800'
			},
			spotify: {
				label: 'Spotify',
				color: '#2ebd59'
			},
			teams: {
				label: 'Teams',
				color: '#505AC9'
			},
			telegram: {
				label: 'Telegram',
				color: '#0088cc'
			},
			tiktok: {
				label: 'Tiktok',
				color: '#010101'
			},
			tumblr: {
				label: 'Tumblr',
				color: '#35465c'
			},
			twitch: {
				label: 'Twitch',
				color: '#6441a5'
			},
			twitter: {
				label: 'Twitter',
				color: '#55acee'
			},
			vimeo: {
				label: 'Vimeo',
				color: '#1ab7ea'
			},
			vk: {
				label: 'VK',
				color: '#45668e'
			},
			wechat: {
				label: 'WeChat',
				color: '#7bb22e'
			},
			whatsapp: {
				label: 'WhatsApp',
				color: '#77e878'
			},
			xing: {
				label: 'Xing',
				color: '#026466'
			},
			yahoo: {
				label: 'Yahoo',
				color: '#410093'
			},
			yelp: {
				label: 'Yelp',
				color: '#af0606'
			},
			youtube: {
				label: 'Youtube',
				color: '#cd201f'
			},
			email: {
				label: 'Email Address',
				color: '#000000'
			},
			phone: {
				label: 'Phone',
				color: '#000000'
			}
		};

		// Add a "custom" entry.
		if ( custom ) {
			networks.custom = {
				label: 'Custom',
				color: ''
			};
		}

		if ( ! colors ) {
			simpleNetworks = {};
			_.each( networks, function( networkArgs ) {
				simpleNetworks.network_id = networkArgs.label;
			} );
			networks = simpleNetworks;
		}

		return networks;

	},

	/**
	 * JS copy of fusion_builder_sort_social_networks.
	 *
	 * @param {Object} socialNetworksOriginal - The original object.
	 * @param {Object} params - Any parameters we want to pass.
	 * @return {Object}
	 */
	fusionSortSocialNetworks: function( socialNetworksOriginal, params ) {

		var socialNetworks = [],
			iconOrder      = '',
			newNetwork,
			newCustom;

		// Get social networks order from  optionthemes.
		if ( params.social_media_icons_icon && Array.isArray( params.social_media_icons_icon ) ) {
			iconOrder = params.social_media_icons_icon.join( '|' );
		}

		if ( ! Array.isArray( iconOrder ) ) {
			iconOrder = iconOrder.split( '|' );
		}

		if ( Array.isArray( iconOrder ) ) {

			// First put the icons that exist in the Global Options,
			// and order them using tha same order as in Global Options.
			_.each( iconOrder, function( value, key ) {
				var newKey;

				// Backwards compatibility for old social network names.
				newKey = ( 'email' === value ) ? 'mail' : value;

				// Check if social network from TO exists in element.
				if ( ! _.isUndefined( socialNetworksOriginal[ value ] ) ) {
					newNetwork = {};
					if ( 'custom' === value ) {
						if ( socialNetworksOriginal[ value ] ) {
							newNetwork[ key ]  = socialNetworksOriginal[ value ][ key ];
							newCustom          = {};
							newCustom[ value ] = newNetwork;
							socialNetworks.push( newCustom );
						}
					} else {
						newNetwork[ newKey ] = socialNetworksOriginal[ value ];
						socialNetworks.push( newNetwork );
						delete socialNetworksOriginal[ value ];
					}
				}
			} );

			// Put any remaining icons after the ones from the Global Options.
			_.each( socialNetworksOriginal, function( networkurl, name ) {
				if ( 'custom' !== name ) {
					newNetwork         = {};
					newNetwork[ name ] = networkurl;
					socialNetworks.push( newNetwork );
				}
			} );
		} else {
			console.warn( 'OUT' );
		}

		return socialNetworks;
	},

	/**
	 * JS copy of fusion_builder_get_social_networks.
	 * Gets the social networks.
	 *
	 * @since 2.0.0
	 * @param {Object} params - The parameters.
	 * @return {Object}
	 */
	fusionGetSocialNetworks: function( params ) {

		// Careful! The icons are also ordered by these.
		var socialLinksArray = {},
			socialLinks      = {
				facebook: 'facebook',
				twitch: 'twitch',
				tiktok: 'tiktok',
				twitter: 'twitter',
				instagram: 'instagram',
				youtube: 'youtube',
				linkedin: 'linkedin',
				dribbble: 'dribbble',
				rss: 'rss',
				pinterest: 'pinterest',
				flickr: 'flickr',
				vimeo: 'vimeo',
				tumblr: 'tumblr',
				discord: 'discord',
				digg: 'digg',
				blogger: 'blogger',
				skype: 'skype',
				teams: 'teams',
				myspace: 'myspace',
				deviantart: 'deviantart',
				yahoo: 'yahoo',
				reddit: 'reddit',
				forrst: 'forrst',
				paypal: 'paypal',
				dropbox: 'dropbox',
				soundcloud: 'soundcloud',
				vk: 'vk',
				wechat: 'wechat',
				whatsapp: 'whatsapp',
				telegram: 'telegram',
				xing: 'xing',
				yelp: 'yelp',
				spotify: 'spotify',
				email: 'mail',
				phone: 'phone'
			};

		_.each( socialLinks, function( val, key ) {
			if ( 'undefined' !== typeof params[ key ] && '' !== params[ key ] ) {
				socialLinksArray[ val ] = params[ key ];
			}
		} );

		if ( params.show_custom && 'yes' === params.show_custom ) {
			socialLinksArray.custom = {};
			if ( Array.isArray( params.social_media_icons_icon ) ) {
				_.each( params.social_media_icons_icon, function( icon, key ) {

					if ( 'custom' === icon && Array.isArray( params.social_media_icons_url ) && ! _.isUndefined( params.social_media_icons_url[ key ] ) && '' !== params.social_media_icons_url[ key ] ) {

						// Check if there is a default set for this, if so use that rather than TO link.
						if ( params[ 'custom_' + key ] && '' !== params[ 'custom_' + key ] ) {
							socialLinksArray.custom[ key ] = params[ 'custom_' + key ];
						} else {
							socialLinksArray.custom[ key ] = params.social_media_icons_url[ key ];
						}
					}
				} );
			}
		}

		return socialLinksArray;
	},

	// WIP: If padding (combined all 4) is not set in params, then use individual variables.
	fusionGetPadding: function( values ) {
		values.padding_top    = 'undefined' !== typeof values.padding_top ? _.fusionGetValueWithUnit( values.padding_top ) : '0px';
		values.padding_right  = 'undefined' !== typeof values.padding_right ? _.fusionGetValueWithUnit( values.padding_right ) : '0px';
		values.padding_bottom = 'undefined' !== typeof values.padding_bottom ? _.fusionGetValueWithUnit( values.padding_bottom ) : '0px';
		values.padding_left   = 'undefined' !== typeof values.padding_left ? _.fusionGetValueWithUnit( values.padding_left ) : '0px';
		values.padding = values.padding_top + ' ' + values.padding_right + ' ' + values.padding_bottom + ' ' + values.padding_left;

		return values;
	},

	fusionGetMargin: function( values ) {
		if ( '' !== values.margin_bottom ) {
			values.margin_bottom = _.fusionGetValueWithUnit( values.margin_bottom );
		}
		if ( '' !== values.margin_top ) {
			values.margin_top = _.fusionGetValueWithUnit( values.margin_top );
		}

		return values;
	},

	fusionAnimations: function( values, attributes, parent ) {
		var animations = false;

		if ( values && 'undefined' !== typeof values.animation_type && '' !== values.animation_type ) {
			animations = _.fusionGetAnimations( {
				type: values.animation_type,
				direction: values.animation_direction,
				speed: values.animation_speed,
				offset: values.animation_offset
			} );

			attributes = jQuery.extend( attributes, animations );

			// Class to mark as editor.
			if ( 'undefined' === typeof parent || ! parent ) {
				if ( 'undefined' !== typeof attributes[ 'class' ] ) {
					attributes[ 'class' ] += ' ' + attributes.animation_class;
				} else {
					attributes[ 'class' ] = attributes.animation_class;
				}
			}

			delete attributes.animation_class;
		}

		return attributes;
	},

	fusionPagination: function( maxPages, currentPage, range, pagination, globalPagination, globalStartEndRange ) {
		var paginationCode = '',
			i,
			globalStartRange,
			globalEndRange,
			start,
			end;

		globalStartEndRange = ( 'undefined' !== typeof globalStartEndRange ) ? parseInt( globalStartEndRange, 10 ) : 2;
		currentPage         = ( 'undefined' !== typeof currentPage ) ? parseInt( currentPage, 10 ) : 1;
		range               = parseInt( range, 10 );
		maxPages            = parseInt( maxPages, 10 );

		globalStartRange = globalStartEndRange;
		globalEndRange   = globalStartEndRange;

		if ( 1 !== maxPages ) {

			if ( ( 'pagination' !== pagination && 'pagination' !== globalPagination.toLowerCase() ) ) {
				paginationCode += '<div class="fusion-infinite-scroll-trigger"></div>';
				paginationCode += '<div class="pagination infinite-scroll clearfix">';
			} else {
				paginationCode += '<div class="pagination clearfix">';
			}

			start = currentPage - range;
			end   = currentPage + range;
			if ( 0 >= start ) {
				start = ( 0 < currentPage - 1 ) ? currentPage - 1 : 1;
			}

			if ( maxPages < end ) {
				end = maxPages;
			}

			if ( 'pagination' === pagination ) {
				if ( 1 < currentPage ) {
					paginationCode += '<a class="pagination-prev" href="#"><span class="page-prev"></span><span class="page-text">Previous</span></a>';

					if ( 0 < globalStartRange ) {
						if ( globalStartRange >= start ) {
							globalStartRange = start - 1;
						}

						for ( i = 1; i <= globalStartRange; i++ ) {
							paginationCode += '<a href="#" class="inactive">' + i + '</a>';
						}

						if ( 0 < globalStartRange && globalStartRange < start - 1 ) {
							paginationCode += '<span class="pagination-dots paginations-dots-start">&middot;&middot;&middot;</span>';
						}
					}
				}

				for ( i = start; i <= end; i++ ) {
					if ( currentPage == i ) {
						paginationCode += '<span class="current">' + i + '</span>';
					} else {
						paginationCode += '<a href="#" class="inactive">' + i + '</a>';
					}
				}

				if ( currentPage < maxPages ) {

					if ( 0 < globalEndRange ) {

						if ( maxPages - globalEndRange <= end ) {
							globalEndRange = maxPages - end;
						}

						globalEndRange--;

						if ( end + 1 < maxPages - globalEndRange ) {
							paginationCode += '<span class="pagination-dots paginations-dots-end">&middot;&middot;&middot;</span>';
						}

						for ( i = maxPages - globalEndRange; i <= maxPages; i++ ) {
							paginationCode += '<a href="#" class="inactive">' + i + '</a>';
						}
					}

					paginationCode += '<a class="pagination-next" href="#"><span class="page-text">Next</span><span class="page-next"></span></a>';
				}
			}

			paginationCode += '</div>';
			paginationCode += '<div class="fusion-clearfix"></div>';
		}

		return paginationCode;
	},

	fusionInlineEditor: function( args, attributes ) {
		var defaults = {
				cid: false,
				param: 'element_content',
				encoding: false,
				'disable-return': false,
				'disable-extra-spaces': false,
				toolbar: 'full',
				overrides: false
			},
			config = _.extend( defaults, args ),
			view   = FusionPageBuilderViewManager.getView( config.cid );

		// If cid is not a number then this is a nested render and do not use live editor.
		if ( 'number' !== typeof config.cid ) {
			return attributes;
		}

		attributes[ 'data-inline-parent-cid' ] = config.cid;

		// Class to mark as editor.
		if ( 'undefined' !== typeof attributes[ 'class' ] ) {
			attributes[ 'class' ] += ' fusion-live-editable';
		} else {
			attributes[ 'class' ] = 'fusion-live-editable';
		}

		if ( config[ 'disable-return' ] ) {
			attributes[ 'data-disable-return' ] = 'true';
		}

		if ( config[ 'disable-extra-spaces' ] ) {
			attributes[ 'data-disable-extra-spaces' ] = 'true';
		}

		if ( config.encoding ) {
			attributes[ 'data-encoding' ] = 'true';
		}

		if ( 'object' === typeof config.overrides ) {
			_.each( config.overrides, function( elementParam, inlineParam ) {
				attributes[ 'data-inline-override-' + inlineParam ] = elementParam;
			} );
		}
		attributes[ 'data-toolbar' ] = config.toolbar.toString();
		attributes[ 'data-param' ]   = config.param;

		if ( 'object' === typeof view && 'object' === typeof view.dynamicParams ) {
			if ( view.dynamicParams.hasDynamicParam( config.param ) ) {
				attributes[ 'data-dynamic-content-overriding' ] = 'true';
			}
		}

		return attributes;
	},

	/**
	 * JS copy of fusion_section_deprecated_args.
	 * Maps the dprecated container args.
	 *
	 * @since 2.0.0
	 * @param {Object} args - The parameters.
	 * @return {Object}
	 */
	fusionContainerMapDeprecatedArgs: function( args ) {
		var paramMapping = {
			backgroundposition: 'background_position',
			backgroundattachment: 'background_parallax',
			background_attachment: 'background_parallax',
			bordersize: 'border_size',
			bordercolor: 'border_color',
			borderstyle: 'border_style',
			paddingtop: 'padding_top',
			paddingbottom: 'padding_bottom',
			paddingleft: 'padding_left',
			paddingright: 'padding_right',
			backgroundcolor: 'background_color',
			backgroundimage: 'background_image',
			backgroundrepeat: 'background_repeat',
			paddingBottom: 'padding_bottom',
			paddingTop: 'padding_top'
		};

		if ( ( 'undefined' !== typeof args.backgroundattachment  && 'scroll' === args.backgroundattachment ) || ( 'undefined' !== typeof args.background_attachment && 'scroll' === args.background_attachment ) ) {
			args.backgroundattachment  = 'none';
			args.background_attachment = 'none';
		}

		_.each( paramMapping, function( newName, oldName ) {
			if ( 'undefined' === typeof args[ newName ] && 'undefined' !== typeof args[ oldName ] ) {
				args[ newName ] = args[ oldName ];
				delete args[ oldName ];
			}
		} );

		return args;
	},

	/**
	 * Replaces double line-breaks with paragraph elements.
	 *
	 * JS version of the wpautop() PHP function and based on the portation
	 * for the Gutenberg block editor.
	 *
	 * @since 2.0.0
	 * @param  {string}    text The text which has to be formatted.
	 * @param  {boolean}   br   Optional. If set, will convert all remaining line-
	 *                          breaks after paragraphing. Default true.
	 * @return {string}         Text which has been converted into paragraph tags.
	 */
	autop: function( text, br ) {
		var preTags = [],
			textParts,
			lastText,
			i,
			textPart,
			start,
			name,
			allBlocks,
			texts;

		if ( 'string' !== typeof text || '' === text.trim() ) {
			return '';
		}

		br = ( 'undefined' === typeof br ) ? true : br;

		// Just to make things a little easier, pad the end.
		text = text + '\n';

		/*
		 * Pre tags shouldn't be touched by autop.
		 * Replace pre tags with placeholders and bring them back after autop.
		 */
		if ( -1 !== text.indexOf( '<pre' ) ) {
			textParts = text.split( '</pre>' );
			lastText = textParts.pop();
			text = '';

			for ( i = 0; i < textParts.length; i++ ) {
				textPart = textParts[ i ];
				start = textPart.indexOf( '<pre' );

				// Malformed html?
				if ( -1 === start ) {
					text += textPart;
					continue;
				}

				name = '<pre wp-pre-tag-' + i + '></pre>';
				preTags.push( [ name, textPart.substr( start ) + '</pre>' ] );

				text += textPart.substr( 0, start ) + name;
			}

			text += lastText;
		}

		// Change multiple <br>s into two line breaks, which will turn into paragraphs.
		text = text.replace( /<br\s*\/?>\s*<br\s*\/?>/g, '\n\n' );

		allBlocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

		// Add a double line break above block-level opening tags.
		text = text.replace( new RegExp( '(<' + allBlocks + '[\\s\/>])', 'g' ), '\n\n$1' );

		// Add a double line break below block-level closing tags.
		text = text.replace( new RegExp( '(<\/' + allBlocks + '>)', 'g' ), '$1\n\n' );

		// Standardize newline characters to "\n".
		text = text.replace( /\r\n|\r/g, '\n' );

		// Find newlines in all elements and add placeholders.
		text = this.replaceInHtmlTags( text, { '\n': ' <!-- wpnl --> ' } );

		// Collapse line breaks before and after <option> elements so they don't get autop'd.
		if ( -1 !== text.indexOf( '<option' ) ) {
			text = text.replace( /\s*<option/g, '<option' );
			text = text.replace( /<\/option>\s*/g, '</option>' );
		}

		/*
		 * Collapse line breaks inside <object> elements, before <param> and <embed> elements
		 * so they don't get autop'd.
		 */
		if ( -1 !== text.indexOf( '</object>' ) ) {
			text = text.replace( /(<object[^>]*>)\s*/g, '$1' );
			text = text.replace( /\s*<\/object>/g, '</object>' );
			text = text.replace( /\s*(<\/?(?:param|embed)[^>]*>)\s*/g, '$1' );
		}

		/*
		 * Collapse line breaks inside <audio> and <video> elements,
		 * before and after <source> and <track> elements.
		 */
		if ( -1 !== text.indexOf( '<source' ) || -1 !== text.indexOf( '<track' ) ) {
			text = text.replace( /([<\[](?:audio|video)[^>\]]*[>\]])\s*/g, '$1' );
			text = text.replace( /\s*([<\[]\/(?:audio|video)[>\]])/g, '$1' );
			text = text.replace( /\s*(<(?:source|track)[^>]*>)\s*/g, '$1' );
		}

		// Collapse line breaks before and after <figcaption> elements.
		if ( -1 !== text.indexOf( '<figcaption' ) ) {
			text = text.replace( /\s*(<figcaption[^>]*>)/, '$1' );
			text = text.replace( /<\/figcaption>\s*/, '</figcaption>' );
		}

		// Remove more than two contiguous line breaks.
		text = text.replace( /\n\n+/g, '\n\n' );

		// Split up the contents into an array of strings, separated by double line breaks.
		texts = text.split( /\n\s*\n/ ).filter( Boolean );

		// Reset text prior to rebuilding.
		text = '';

		// Rebuild the content as a string, wrapping every bit with a <p>.
		texts.forEach( function( textPiece ) {
			text += '<p>' + textPiece.replace( /^\n*|\n*$/g, '' ) + '</p>\n';
		} );

		// Under certain strange conditions it could create a P of entirely whitespace.
		text = text.replace( /<p>\s*<\/p>/g, '' );

		// Add a closing <p> inside <div>, <address>, or <form> tag if missing.
		text = text.replace( /<p>([^<]+)<\/(div|address|form)>/g, '<p>$1</p></$2>' );

		// If an opening or closing block element tag is wrapped in a <p>, unwrap it.
		text = text.replace( new RegExp( '<p>\\s*(<\/?' + allBlocks + '[^>]*>)\\s*<\/p>', 'g' ), '$1' );

		// In some cases <li> may get wrapped in <p>, fix them.
		text = text.replace( /<p>(<li.+?)<\/p>/g, '$1' );

		// If a <blockquote> is wrapped with a <p>, move it inside the <blockquote>.
		text = text.replace( /<p><blockquote([^>]*)>/gi, '<blockquote$1><p>' );
		text = text.replace( /<\/blockquote><\/p>/g, '</p></blockquote>' );

		// If an opening or closing block element tag is preceded by an opening <p> tag, remove it.
		text = text.replace( new RegExp( '<p>\\s*(<\/?' + allBlocks + '[^>]*>)', 'g' ), '$1' );

		// If an opening or closing block element tag is followed by a closing <p> tag, remove it.
		text = text.replace( new RegExp( '(<\/?' + allBlocks + '[^>]*>)\\s*<\/p>', 'g' ), '$1' );

		// Optionally insert line breaks.
		if ( br ) {

			// Replace newlines that shouldn't be touched with a placeholder.
			text = text.replace( /<(script|style).*?<\/\\1>/g, function( match ) {
				return match[ 0 ].replace( /\n/g, '<WPPreserveNewline />' );
			} );

			// Normalize <br>
			text = text.replace( /<br>|<br\/>/g, '<br />' );

			// Replace any new line characters that aren't preceded by a <br /> with a <br />.
			text = text.replace( /(<br \/>)?\s*\n/g, function( a, b ) {
				return b ? a : '<br />\n';
			} );

			// Replace newline placeholders with newlines.
			text = text.replace( /<WPPreserveNewline \/>/g, '\n' );
		}

		// If a <br /> tag is after an opening or closing block tag, remove it.
		text = text.replace( new RegExp( '(<\/?' + allBlocks + '[^>]*>)\\s*<br \/>', 'g' ), '$1' );

		// If a <br /> tag is before a subset of opening or closing block tags, remove it.
		text = text.replace( /<br \/>(\s*<\/?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)/g, '$1' );
		text = text.replace( /\n<\/p>$/g, '</p>' );

		// Replace placeholder <pre> tags with their original content.
		if ( Object.keys( preTags ).length ) {
			text = text.replace( new RegExp( Object.keys( preTags ).join( '|' ), 'gi' ), function( matched ) {
				return preTags[ matched ];
			} );
		}

		// Restore newlines in all elements.
		if ( -1 !== text.indexOf( '<!-- wpnl -->' ) ) {
			text = text.replace( /\s?<!-- wpnl -->\s?/g, '\n' );
		}

		return text;
	},

	/**
	 * Separate HTML elements and comments from the text.
	 *
	 * JS version of the html_split() PHP function and based on the portation
	 * for the Gutenberg block editor.
	 *
	 * @since 2.0
	 * @param  {string} input The text which has to be formatted.
	 * @return {Array}        The formatted text.
	 */
	htmlSplit: function( input ) {
		var parts = [],
			workingInput = input,
			match,
			htmlSplitRegex = ( function() {

				/* eslint-disable no-multi-spaces */
				var comments =
					'!' +           // Start of comment, after the <.
					'(?:' +         // Unroll the loop: Consume everything until --> is found.
						'-(?!->)' + // Dash not followed by end of comment.
						'[^\\-]*' + // Consume non-dashes.
					')*' +          // Loop possessively.
					'(?:-->)?',     // End of comment. If not found, match all input.

					cdata =
					'!\\[CDATA\\[' + // Start of comment, after the <.
					'[^\\]]*' +      // Consume non-].
					'(?:' +          // Unroll the loop: Consume everything until ]]> is found.
						'](?!]>)' +  // One ] not followed by end of comment.
						'[^\\]]*' +  // Consume non-].
					')*?' +          // Loop possessively.
					'(?:]]>)?',      // End of comment. If not found, match all input.

					escaped =
					'(?=' +              // Is the element escaped?
						'!--' +
					'|' +
						'!\\[CDATA\\[' +
					')' +
					'((?=!-)' +          // If yes, which type?
						comments +
					'|' +
						cdata +
					')',

					regex =
					'(' +               // Capture the entire match.
						'<' +           // Find start of element.
						'(' +           // Conditional expression follows.
							escaped +   // Find end of escaped element.
						'|' +           // ... else ...
							'[^>]*>?' + // Find end of normal element.
						')' +
					')';

				return new RegExp( regex );
				/* eslint-enable no-multi-spaces */
			}() );

		while ( ( match = workingInput.match( htmlSplitRegex ) ) ) {
			parts.push( workingInput.slice( 0, match.index ) );
			parts.push( match[ 0 ] );
			workingInput = workingInput.slice( match.index + match[ 0 ].length );
		}

		if ( workingInput.length ) {
			parts.push( workingInput );
		}

		return parts;
	},

	/**
	 * Replace characters or phrases within HTML elements only.
	 *
	 * JS version of the replace_html_tags() PHP function and based on the portation
	 * for the Gutenberg block editor.
	 *
	 * @since 2.0
	 * @param  {string} haystack     The text which has to be formatted.
	 * @param  {Object} replacePairs In the form {from: 'to', ...}.
	 * @return {string}              The formatted text.
	 */
	replaceInHtmlTags: function( haystack, replacePairs ) {

		// Find all elements.
		var textArr = this.htmlSplit( haystack ),
			changed = false,
			needles = Object.keys( replacePairs ), // Extract all needles.
			i, j,
			needle;

		// Loop through delimiters (elements) only.
		for ( i = 1; i < textArr.length; i += 2 ) {
			for ( j = 0; j < needles.length; j++ ) {
				needle = needles[ j ];

				if ( -1 !== textArr[ i ].indexOf( needle ) ) {
					textArr[ i ] = textArr[ i ].replace( new RegExp( needle, 'g' ), replacePairs[ needle ] );
					changed = true;

					// After one strtr() break out of the foreach loop and look at next element.
					break;
				}
			}
		}

		if ( changed ) {
			haystack = textArr.join( '' );
		}

		return haystack;
	},

	/**
	 * Generates filter CSS.
	 *
	 * @since 2.1
	 * @param {Object} values - The values.
	 * @return {String}
	 */
	fusionGetFilterStyle: function( values, state ) {
		var filters = {
			filter_hue: { property: 'hue-rotate', unit: 'deg', default: '0' },
			filter_saturation: { property: 'saturate', unit: '%', default: '100' },
			filter_brightness: { property: 'brightness', unit: '%', default: '100' },
			filter_contrast: { property: 'contrast', unit: '%', default: '100' },
			filter_invert: { property: 'invert', unit: '%', default: '0' },
			filter_sepia: { property: 'sepia', unit: '%', default: '0' },
			filter_opacity: { property: 'opacity', unit: '%', default: '100' },
			filter_blur: { property: 'blur', unit: 'px', default: '0' }
		},
		stateSuffix        = 'regular' === state ? '' : '_hover',
		otherStateSuffix   = 'regular' === state ? '_hover' : '',
		filter_id_state = '',
		filter_id_other = '',
		filter_style    = '';

		_.each( filters, function( filter, filter_id ) {
			filter_id_state = filter_id + stateSuffix;
			filter_id_other = filter_id + otherStateSuffix;
			if ( filter[ 'default' ] !== values[ filter_id_state ] || filter[ 'default' ] !== values[ filter_id_other ] ) {
				filter_style += filter.property + '(' + values[ filter_id_state ] + filter.unit + ') ';
			}
		} );

		return filter_style.trim();
	},

	/**
	 * Generates filter style element.
	 *
	 * @since 2.1
	 * @param {Object} values - The values.
	 * @param {string|object} selector - Element selector.
	 * @param {integer} cid - Element cid.
	 * @return {String}
	 */
	fusionGetFilterStyleElem: function( values, selector, cid ) {
		var filter_style       = '',
			filter_style_hover = '',
			regularSelector    = 'body:not(.fusion-disable-element-filters) ',
			hoverSelector      = 'body:not(.fusion-disable-element-filters) ';

		if ( 'object' === typeof selector ) {
			regularSelector += selector.regular;
			hoverSelector   += selector.hover;
		} else {
			regularSelector += selector;
			hoverSelector   += selector + ':hover';
		}

		// Get filter CSS.
		filter_style = this.fusionGetFilterStyle( values, 'regular' );
		if ( '' !== filter_style ) {
			filter_style = regularSelector + '{filter: ' + filter_style + ';}';
		}

		filter_style_hover = this.fusionGetFilterStyle( values, 'hover' );
		if ( '' !== filter_style_hover ) {

			// Add transition.
			filter_style = filter_style.replace( '}', 'transition: filter 0.3s ease-in-out;}' );

			// Hover state.
			filter_style += hoverSelector + '{filter: ' + filter_style_hover + ';}';
		}

		// We need empty style element as well.
		return '<style id="fusion-filter-' + cid + '-style">' + filter_style + '</style>';
	},

	/**
	 * Generates transform CSS.
	 *
	 * @since 3.8
	 * @param {Object} values - The values.
	 * @return {String}
	 */
	fusionGetTransformStyle: function( values, state ) {
		var props = {
			transform_scale_x: { property: 'scaleX', unit: '', default: '1' },
			transform_scale_y: { property: 'scaleY', unit: '', default: '1' },
			transform_translate_x: { property: 'translateX', unit: 'px', default: '0' },
			transform_translate_y: { property: 'translateY', unit: 'px', default: '0' },
			transform_rotate: { property: 'rotate', unit: 'deg', default: '0' },
			transform_skew_x: { property: 'skewX', unit: 'deg', default: '0' },
			transform_skew_y: { property: 'skewY', unit: 'deg', default: '0' }
		},
		stateSuffix        = 'regular' === state ? '' : '_hover',
		otherStateSuffix   = 'regular' === state ? '_hover' : '',
		transform_id_state = '',
		transform_id_other = '',
		transform_style    = '';

		_.each( props, function( transform, transform_id ) {
			transform_id_state = transform_id + stateSuffix;
			transform_id_other = transform_id + otherStateSuffix;
			if ( transform[ 'default' ] !== values[ transform_id_state ] || transform[ 'default' ] !== values[ transform_id_other ] ) {
				transform_style += transform.property + '(' + values[ transform_id_state ] + transform.unit + ') ';
			}
		} );

		return transform_style.trim();
	},

	/**
	 * Generates transform style element.
	 *
	 * @since 3.8
	 * @param {Object} values - The values.
	 * @param {string|object} selector - Element selector.
	 * @param {integer} cid - Element cid.
	 * @return {String}
	 */
	fusionGetTransformStyleElem: function( values, selector, editSelector, cid ) {
		var transform_style       = '',
			transform_style_hover = '',
			regularSelector    = 'body:not(.fusion-disable-element-transform):not(.fusion-element-transform-on-edit) ',
			hoverSelector      = 'body:not(.fusion-disable-element-transform):not(.fusion-element-transform-on-edit) ',
			regularEditSelector    = 'body.fusion-element-transform-on-edit ',
			hoverEditSelector      = 'body.fusion-element-transform-on-edit ';

		if ( 'object' === typeof selector ) {
			regularSelector += selector.regular;
			hoverSelector   += selector.hover;
			regularEditSelector += editSelector.regular;
			hoverEditSelector   += editSelector.hover;
		} else {
			regularSelector += selector;
			hoverSelector   += selector + ':hover';
			regularEditSelector += editSelector;
			hoverEditSelector   += editSelector + ':hover';
		}

		let output = '';
		// Get transform CSS.
		transform_style = this.fusionGetTransformStyle( values, 'regular' );
		if ( '' !== transform_style ) {
			let transform_origin = '';
			if ( '' !== values.transform_origin ) {
				transform_origin = 'transform-origin:' + values.transform_origin + ';';
			}
			output += regularSelector + '{transform: ' + transform_style + '; ' + transform_origin + '}';
			output += regularEditSelector + '{transform: ' + transform_style + '; ' + transform_origin + '}';

			// Hide element control when apply transform effect.
			// output += regularSelector + ' .fusion-builder-module-controls-container { display: none !important; }';
			// output += regularSelector + ' .fusion-column-spacers { display: none !important; }';
			output += regularEditSelector + ' .fusion-builder-module-controls-container { display: none !important; }';
			output += regularEditSelector + ' .fusion-column-spacers { display: none !important; }';
		}

		transform_style_hover = this.fusionGetTransformStyle( values, 'hover' );
		if ( '' !== transform_style_hover ) {

			// Add transition.
			output += regularSelector + '{ transition: transform 0.3s ease-in-out;}';
			output += regularEditSelector + '{ transition: transform 0.3s ease-in-out;}';

			// Hover state.
			output += hoverSelector + '{transform: ' + transform_style_hover + ';}';
			output += hoverEditSelector + '{transform: ' + transform_style_hover + ';}';

		}

		// We need empty style element as well.
		return '<style id="fusion-transform-' + cid + '-style">' + output + '</style>';
	},

	/**
	 * Generates transform style vars.
	 *
	 * @since 3.8
	 * @param {Object} values - The values.
	 * @return {String}
	 */
	fusionGetTransformVars: function( values, hover ) {
		if ( 'undefined' !== typeof window.FusionApp && 'off' === window.FusionApp.preferencesData.element_transform ) {
			return '--awb-transform:none; --awb-transform-hover:none;';
		}
		let output = '';

		if ( hover ) {
			const transform_style_hover = this.fusionGetTransformStyle( values, 'hover' );
			if ( '' !== transform_style_hover ) {
				// Hover state.
				output += '--awb-transform-hover: ' + transform_style_hover + ';';

				// Add transition.
				output += '--awb-transform-transition: transform 0.3s ease-in-out;';

			} else {
				output += '--awb-transform-hover:none;';
			}
		} else {
			// Get transform CSS.
			const transform_style = this.fusionGetTransformStyle( values, 'regular' );
			if ( '' !== transform_style ) {
				output += '--awb-transform: ' + transform_style + ';';
			} else {
				output += '--awb-transform:none;';
			}
		}

		if ( '' !== values.transform_origin ) {
			output += '--awb-transform-origin:' + values.transform_origin + ';';
		}

		return output;
	},

	/**
	 * Get pattern.
	 *
	 * @since 3.8
	 * @param {String} name - The selected pattern name.
	 * @param {String} color - The pattern color.
	 * @param {String} style - The pattern style default|inverted.
	 * @return {String}
	 */
	fusionGetPattern( name, color, style ) {
		style = style || 'default';
		color = jQuery.AWB_Color( color ).toRgbaString() || 'rgba(0,0,0,0.3)';
		const patterns = {
			'abstract': {
				'default': '<svg width="120" height="120" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_44_400)" fill="' + color + '"><path d="M4.5 61.92A1.08 1.08 0 003.42 63H4.5v-1.08zM.9 60.48c.375 0 .704-.19.898-.48H.002c.194.29.523.48.898.48zM18.9 61.92A1.08 1.08 0 0017.82 63h1.08v-1.08zM11.7 61.92A1.08 1.08 0 0010.62 63h2.16a1.08 1.08 0 00-1.08-1.08zM5.58 63a1.08 1.08 0 00-1.08-1.08V63h1.08zM15.3 60.48c.375 0 .703-.19.898-.48h-1.796c.194.29.523.48.898.48zM8.1 60.48c.375 0 .704-.19.898-.48H7.202c.194.29.523.48.898.48zM33.3 63v-1.08A1.08 1.08 0 0032.22 63h1.08zM26.1 61.92A1.08 1.08 0 0025.02 63h2.16a1.08 1.08 0 00-1.08-1.08zM19.98 63a1.08 1.08 0 00-1.08-1.08V63h1.08zM29.7 60.48c.375 0 .704-.19.898-.48h-1.796c.194.29.523.48.898.48zM22.5 60.48c.375 0 .703-.19.898-.48h-1.796c.194.29.523.48.898.48zM46.62 63h1.08v-1.08A1.08 1.08 0 0046.62 63zM40.5 61.92A1.08 1.08 0 0039.42 63h2.16a1.08 1.08 0 00-1.08-1.08zM34.38 63a1.08 1.08 0 00-1.08-1.08V63h1.08zM44.1 60.48c.375 0 .705-.19.898-.48h-1.796c.195.29.523.48.898.48zM36.9 60.48c.375 0 .705-.19.898-.48h-1.796c.193.29.523.48.898.48zM55.98 63a1.08 1.08 0 00-2.16 0h2.16zM48.78 63a1.08 1.08 0 00-1.08-1.08V63h1.08zM58.5 60.48c.375 0 .704-.19.898-.48h-1.796c.195.29.523.48.898.48zM51.3 60.48c.375 0 .705-.19.898-.48h-1.796c.193.29.523.48.898.48zM4.5 76.32a1.08 1.08 0 00-1.08 1.08H4.5v-1.08zM3.42 70.2c0 .596.484 1.08 1.08 1.08v-2.16a1.08 1.08 0 00-1.08 1.08zM3.42 63c0 .596.484 1.08 1.08 1.08V63H3.42zM1.98 66.6a1.08 1.08 0 00-1.707-.879c.06.627.125 1.252.204 1.872a1.08 1.08 0 001.504-.995v.002zM1.38 72.834c.118.543.245 1.084.377 1.621a1.075 1.075 0 00-.377-1.621zM18.9 76.32a1.08 1.08 0 00-1.08 1.08h1.08v-1.08zM17.82 70.2c0 .596.483 1.08 1.08 1.08v-2.16a1.08 1.08 0 00-1.08 1.08zM17.82 63c0 .596.483 1.08 1.08 1.08V63h-1.08zM11.7 76.32a1.08 1.08 0 00-1.08 1.08h2.16a1.08 1.08 0 00-1.08-1.08zM12.78 70.2a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM11.7 64.08A1.08 1.08 0 0012.78 63h-2.16c0 .596.484 1.08 1.08 1.08zM4.5 76.32v1.08h1.08a1.08 1.08 0 00-1.08-1.08zM5.58 70.2a1.08 1.08 0 00-1.08-1.08v2.16a1.08 1.08 0 001.08-1.08zM5.58 63H4.5v1.08A1.08 1.08 0 005.58 63zM16.38 66.6a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM16.38 73.8a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM9.18 66.6a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zM9.18 73.8a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zM33.3 77.4v-1.08a1.08 1.08 0 00-1.08 1.08h1.08zM32.22 70.2c0 .596.483 1.08 1.08 1.08v-2.16a1.08 1.08 0 00-1.08 1.08zM33.3 64.08V63h-1.08c0 .596.483 1.08 1.08 1.08zM26.1 76.32a1.08 1.08 0 00-1.08 1.08h2.16a1.08 1.08 0 00-1.08-1.08zM27.18 70.2a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM26.1 64.08A1.08 1.08 0 0027.18 63h-2.16c0 .596.483 1.08 1.08 1.08zM18.9 76.32v1.08h1.08a1.08 1.08 0 00-1.08-1.08zM19.98 70.2a1.08 1.08 0 00-1.08-1.08v2.16a1.08 1.08 0 001.08-1.08zM19.98 63H18.9v1.08A1.08 1.08 0 0019.98 63zM30.78 66.6a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zM30.78 73.8a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zM23.58 66.6a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM23.58 73.8a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM46.62 77.4h1.08v-1.08a1.081 1.081 0 00-1.08 1.08zM46.62 70.2a1.08 1.08 0 001.08 1.08v-2.16a1.081 1.081 0 00-1.08 1.08zM46.62 63a1.081 1.081 0 001.08 1.08V63h-1.08zM40.5 76.32a1.08 1.08 0 00-1.08 1.08h2.16a1.08 1.08 0 00-1.08-1.08zM41.58 70.2a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM40.5 64.08A1.08 1.08 0 0041.58 63h-2.16c0 .596.483 1.08 1.08 1.08zM34.38 77.4a1.08 1.08 0 00-1.08-1.08v1.08h1.08zM34.38 70.2a1.08 1.08 0 00-1.08-1.08v2.16a1.08 1.08 0 001.08-1.08zM33.3 64.08A1.08 1.08 0 0034.38 63H33.3v1.08zM43.02 66.6a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zM43.02 73.8a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zM35.82 66.6a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zM35.82 73.8a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zM54.9 76.32a1.08 1.08 0 00-1.08 1.08h2.16a1.08 1.08 0 00-1.08-1.08zM54.9 69.12a1.08 1.08 0 100 2.161 1.08 1.08 0 000-2.161zM54.9 64.08A1.08 1.08 0 0055.98 63h-2.16c0 .596.484 1.08 1.08 1.08zM48.78 77.4a1.08 1.08 0 00-1.08-1.08v1.08h1.08zM48.78 70.2a1.08 1.08 0 00-1.08-1.08v2.16a1.08 1.08 0 001.08-1.08zM47.7 64.08A1.08 1.08 0 0048.78 63H47.7v1.08zM57.42 66.6a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zM57.42 73.8a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zM52.38 66.6a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM50.22 73.8a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zM3.42 77.4c0 .596.484 1.08 1.08 1.08V77.4H3.42zM18.9 90.72a1.08 1.08 0 00-1.08 1.08h1.08v-1.08zM17.82 84.6c0 .597.483 1.08 1.08 1.08v-2.16a1.08 1.08 0 00-1.08 1.08zM17.82 77.4c0 .596.483 1.08 1.08 1.08V77.4h-1.08zM11.7 90.72a1.08 1.08 0 00-1.08 1.08h2.16a1.08 1.08 0 00-1.08-1.08zM12.78 84.6a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zM11.7 78.48a1.08 1.08 0 001.08-1.08h-2.16c0 .596.484 1.08 1.08 1.08zM4.807 83.564c.216.504.44 1.004.668 1.5a1.082 1.082 0 00-.668-1.5zM4.5 78.48a1.08 1.08 0 001.08-1.08H4.5v1.08zM16.38 81a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM16.38 88.2a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM9.18 81a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM9.18 88.2a1.08 1.08 0 00-2.159-.02c.168.315.338.625.51.936.165.104.36.165.569.165a1.08 1.08 0 001.08-1.08zM33.3 91.8v-1.08a1.08 1.08 0 00-1.08 1.08h1.08zM32.22 84.6c0 .597.483 1.08 1.08 1.08v-2.16a1.08 1.08 0 00-1.08 1.08zM32.22 77.4c0 .596.483 1.08 1.08 1.08V77.4h-1.08zM26.1 90.72a1.08 1.08 0 00-1.08 1.08h2.16a1.08 1.08 0 00-1.08-1.08zM27.18 84.6a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zM26.1 78.48a1.08 1.08 0 001.08-1.08h-2.16c0 .596.483 1.08 1.08 1.08zM18.9 90.72v1.08h1.08a1.08 1.08 0 00-1.08-1.08zM19.98 84.6a1.08 1.08 0 00-1.08-1.08v2.16a1.08 1.08 0 001.08-1.08zM19.98 77.4H18.9v1.08a1.08 1.08 0 001.08-1.08zM30.78 81a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zM30.78 88.2a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zM23.58 81a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM23.58 88.2a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM46.62 91.8h1.08v-1.08a1.081 1.081 0 00-1.08 1.08zM46.62 84.6a1.081 1.081 0 001.08 1.08v-2.16a1.08 1.08 0 00-1.08 1.08zM46.62 77.4a1.08 1.08 0 001.08 1.08V77.4h-1.08zM40.5 90.72a1.08 1.08 0 00-1.08 1.08h2.16a1.08 1.08 0 00-1.08-1.08zM39.42 84.6a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zM40.5 78.48a1.08 1.08 0 001.08-1.08h-2.16c0 .596.483 1.08 1.08 1.08zM33.3 90.72v1.08h1.08a1.08 1.08 0 00-1.08-1.08zM34.38 84.6a1.08 1.08 0 00-1.08-1.08v2.16a1.08 1.08 0 001.08-1.08zM34.38 77.4H33.3v1.08a1.08 1.08 0 001.08-1.08zM45.18 81a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM43.02 88.2a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zM35.82 81a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zM35.82 88.2a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zM54.9 90.72a1.08 1.08 0 00-1.08 1.08h2.16a1.08 1.08 0 00-1.08-1.08zM53.82 84.6a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zM54.9 78.48a1.08 1.08 0 001.08-1.08h-2.16c0 .596.484 1.08 1.08 1.08zM48.78 91.8a1.08 1.08 0 00-1.08-1.08v1.08h1.08zM48.78 84.6a1.08 1.08 0 00-1.08-1.08v2.16a1.08 1.08 0 001.08-1.08zM47.7 78.48a1.08 1.08 0 001.08-1.08H47.7v1.08zM59.58 81a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM57.42 88.2a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zM50.22 81a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zM50.22 88.2a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zM17.82 99c0 .596.483 1.08 1.08 1.08v-2.16A1.08 1.08 0 0017.82 99zM17.82 91.8c0 .596.483 1.08 1.08 1.08V91.8h-1.08zM11.7 92.88a1.08 1.08 0 001.08-1.08h-2.16c0 .596.484 1.08 1.08 1.08zM16.38 95.4a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM33.3 106.2v-1.08a1.08 1.08 0 00-1.08 1.08h1.08zM32.22 99c0 .596.483 1.08 1.08 1.08v-2.16A1.08 1.08 0 0032.22 99zM32.22 91.8c0 .596.483 1.08 1.08 1.08V91.8h-1.08zM26.1 105.12a1.08 1.08 0 00-1.08 1.08h2.16a1.08 1.08 0 00-1.08-1.08zM27.18 99a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM26.1 92.88a1.08 1.08 0 001.08-1.08h-2.16c0 .596.483 1.08 1.08 1.08zM19.98 99a1.08 1.08 0 00-1.08-1.08v2.16A1.08 1.08 0 0019.98 99zM18.9 92.88a1.08 1.08 0 001.08-1.08H18.9v1.08zM30.78 95.4a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zM30.78 102.6a1.08 1.08 0 10-2.161.001 1.08 1.08 0 002.161-.001zM23.58 95.4a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM23.58 102.6a1.08 1.08 0 10-2.16.001 1.08 1.08 0 002.16-.001zM47.7 106.2v-1.08a1.08 1.08 0 00-1.08 1.08h1.08zM46.62 99a1.08 1.08 0 001.08 1.08v-2.16A1.08 1.08 0 0046.62 99zM46.62 91.8a1.08 1.08 0 001.08 1.08V91.8h-1.08zM40.5 105.12a1.08 1.08 0 00-1.08 1.08h2.16a1.08 1.08 0 00-1.08-1.08zM41.58 99a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM40.5 92.88a1.08 1.08 0 001.08-1.08h-2.16c0 .596.483 1.08 1.08 1.08zM33.3 105.12v1.08h1.08a1.08 1.08 0 00-1.08-1.08zM34.38 99a1.08 1.08 0 00-1.08-1.08v2.16A1.08 1.08 0 0034.38 99zM34.38 91.8H33.3v1.08a1.08 1.08 0 001.08-1.08zM45.18 95.4a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM45.18 102.6a1.08 1.08 0 10-2.16.001 1.08 1.08 0 002.16-.001zM37.98 95.4a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zM37.98 102.6a1.08 1.08 0 10-2.16.001 1.08 1.08 0 002.16-.001zM54.9 105.12a1.08 1.08 0 00-1.08 1.08h2.16a1.08 1.08 0 00-1.08-1.08zM53.82 99a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zM54.9 92.88a1.08 1.08 0 001.08-1.08h-2.16c0 .596.484 1.08 1.08 1.08zM47.7 105.12v1.08h1.08a1.08 1.08 0 00-1.08-1.08zM48.78 99a1.08 1.08 0 00-1.08-1.08v2.16A1.08 1.08 0 0048.78 99zM47.7 92.88a1.08 1.08 0 001.08-1.08H47.7v1.08zM57.42 95.4a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zM57.42 102.6a1.08 1.08 0 102.161-.001 1.08 1.08 0 00-2.161.001zM50.22 95.4a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zM50.22 102.6a1.08 1.08 0 102.16-.001 1.08 1.08 0 00-2.16.001zM32.24 113.2c.351.184.705.364 1.06.541v-1.421c-.529 0-.968.38-1.06.88zM32.22 106.2c0 .596.483 1.08 1.08 1.08v-1.08h-1.08zM26.1 107.28a1.08 1.08 0 001.08-1.08h-2.16c0 .596.483 1.08 1.08 1.08zM30.78 109.8a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM46.62 113.4a1.08 1.08 0 001.08 1.08v-2.16a1.08 1.08 0 00-1.08 1.08zM46.62 106.2a1.08 1.08 0 001.08 1.08v-1.08h-1.08zM41.58 113.4a1.08 1.08 0 10-2.161.001 1.08 1.08 0 002.161-.001zM40.5 107.28a1.08 1.08 0 001.08-1.08h-2.16c0 .596.483 1.08 1.08 1.08z"/><path d="M33.3 112.32v1.421c.264.13.527.263.793.391a1.08 1.08 0 00-.793-1.812zM33.3 107.28a1.08 1.08 0 001.08-1.08H33.3v1.08zM45.18 109.8a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM45.18 117a1.08 1.08 0 10-1.966.616c.441.129.882.25 1.327.37a1.08 1.08 0 00.64-.986zM37.98 109.8a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM54.9 119.52c-.236 0-.455.076-.632.205.466.045.934.084 1.403.118a1.073 1.073 0 00-.77-.323H54.9zM53.82 113.4a1.08 1.08 0 102.16-.001 1.08 1.08 0 00-2.16.001zM54.9 107.28a1.08 1.08 0 001.08-1.08h-2.16c0 .596.483 1.08 1.08 1.08zM48.78 113.4a1.08 1.08 0 00-1.08-1.08v2.16a1.08 1.08 0 001.08-1.08zM47.7 107.28a1.08 1.08 0 001.08-1.08H47.7v1.08zM59.58 109.8a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM59.58 117a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zM50.22 109.8a1.08 1.08 0 102.16-.001 1.08 1.08 0 00-2.16.001zM50.22 117a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zM74.341 19.8h.72V1.907c-.24-.062-.48-.118-.72-.177v18.072-.002zM67.141 19.8h.72V.512c-.24-.032-.48-.059-.72-.087V19.8zM60 19.8h.66V.009c-.219-.002-.439-.01-.66-.01v19.8zM70.741 19.8h.72V1.098c-.24-.046-.479-.095-.72-.137V19.8zM63.541 19.8h.72V.152l-.72-.047V19.8zM95.941 19.8h.72v-7.296c-.24-.186-.479-.372-.72-.552V19.8zM88.741 19.8h.72V7.723c-.24-.135-.479-.271-.72-.401v12.48-.002zM81.541 19.8h.72V4.268c-.24-.097-.48-.19-.72-.28V19.8zM92.341 19.8h.72V9.927c-.24-.16-.479-.32-.72-.473V19.8zM85.141 19.8h.72V5.847c-.24-.115-.48-.225-.72-.336V19.8zM77.941 19.8h.72V2.962c-.24-.078-.479-.159-.72-.234V19.8zM103.141 19.8h.72v-.736a62.632 62.632 0 00-.72-.759V19.8zM99.541 19.8h.72v-4.286c-.238-.216-.479-.427-.72-.64V19.8z"/><path d="M74.341 41.325v.075h.72V19.8h-.72v21.525zM67.141 41.325v.075h.72V19.8h-.72v21.525zM60.66 41.325V19.8H60v21.6h.66v-.075zM70.741 41.325v.075h.72V19.8h-.72v21.525zM63.541 41.325v.075h.72V19.8h-.72v21.525zM95.941 41.325v.075h.72V19.8h-.72v21.525zM88.741 41.325v.075h.72V19.8h-.72v21.525zM81.541 22.05V41.4h.72V19.8h-.72v2.25zM92.341 41.325v.075h.72V19.8h-.72v21.525zM85.141 41.325v.075h.72V19.8h-.72v21.525zM77.941 22.05V41.4h.72V19.8h-.72v2.25zM110.341 41.4h.72V28.482c-.236-.382-.475-.76-.72-1.136V41.4zM103.141 22.05V41.4h.72V19.8h-.72v2.25zM113.941 41.4h.72v-6.177a57.484 57.484 0 00-.72-1.523v7.7zM106.741 41.4h.72V23.293a73.002 73.002 0 00-.72-.911V41.4zM99.541 22.05V41.4h.72V19.8h-.72v2.25z"/><path d="M75.06 41.4h-.719V60h.72V41.4zM67.86 41.4h-.719V60h.72V41.4zM60.66 43.65V41.4H60V60h.66V43.65zM71.46 41.4h-.719V60h.72V41.4zM64.26 41.4h-.719V60h.72V41.4zM96.66 41.4h-.719V60h.72V41.4zM89.46 41.4h-.719V60h.72V41.4zM82.26 43.65V41.4h-.719V60h.72V43.65zM93.06 41.4h-.719V60h.72V41.4zM85.86 41.4h-.719V60h.72V41.4zM78.66 43.65V41.4h-.719V60h.72V43.65zM118.261 60V45.616a59.776 59.776 0 00-.72-2.659V60h.72zM111.061 41.4h-.72V60h.72V41.4zM103.861 43.65V41.4h-.72V60h.72V43.65zM114.661 41.4h-.72V60h.72V41.4zM107.461 41.4h-.72V60h.72V41.4zM100.261 43.65V41.4h-.72V60h.72V43.65zM120 120c-33.138 0-60-26.862-60-60h60v60zM60 60H0V0c33.138 0 60 26.864 60 60z"/></g><defs><clipPath id="prefix__clip0_44_400"><path fill="#fff" d="M0 0h120v120H0z"/></clipPath></defs></svg>',
				'inverted': '<svg width="120" height="120" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_44_635)"><path fill-rule="evenodd" clip-rule="evenodd" d="M0 0h60V60h.66V.009c-.093 0-.187-.003-.281-.004h-.006A20.234 20.234 0 0060.009 0H120v120H0V0zm67.141 19.8V.425l.19.022c.177.021.354.042.53.065V60h-.72V19.8zm7.2 0V1.73l.25.061h.002c.156.038.312.076.468.116V60h-.72V19.8zm-2.88-18.702V60h-.72V.96c.241.044.48.092.72.138zM64.26 19.8V.152l-.72-.047V60h.72V19.8zm32.4-7.296V60h-.72V11.952c.241.18.48.366.72.552zM82.26 19.8V4.268c-.24-.097-.48-.19-.72-.28V60h.72V19.8zm10.8-9.873V60h-.72V9.454c.241.153.48.314.72.473zm-7.2-4.08V60h-.72V5.51l.115.054c.202.093.404.187.605.283zm-7.2-2.885V60h-.72V2.729c.185.057.369.118.552.178l.168.055zm24.48 15.343V60h.72V19.064a61.91 61.91 0 00-.72-.759zm-3.6 1.495v-4.925l.124.11c.2.175.399.35.596.53V60h-.72V19.8zm11.52 21.6V28.482c-.236-.382-.475-.76-.72-1.136V60h.72V41.4zm3.6 0v-6.177a57.484 57.484 0 00-.72-1.523V60h.72V41.4zm-7.2-18.107V60h-.72V22.382c.243.302.482.605.72.91zM89.461 60V7.723a41.03 41.03 0 00-.72-.401V60h.72zm28.8-14.384V60h-.72V42.957c.259.877.5 1.764.72 2.66zM.9 60.48c.375 0 .704-.19.898-.48H.002c.194.29.523.48.898.48zm3.6 1.44a1.08 1.08 0 100 2.16 1.08 1.08 0 000-2.16zM17.82 63a1.08 1.08 0 112.16 0 1.08 1.08 0 01-2.16 0zm-6.12-1.08a1.08 1.08 0 100 2.16 1.08 1.08 0 000-2.16zm3.6-1.44c.375 0 .703-.19.898-.48h-1.796c.194.29.523.48.898.48zM8.998 60a1.08 1.08 0 01-1.796 0h1.796zM33.3 61.92a1.08 1.08 0 100 2.16 1.08 1.08 0 000-2.16zM25.02 63a1.08 1.08 0 112.16 0 1.08 1.08 0 01-2.16 0zm5.578-3a1.08 1.08 0 01-1.796 0h1.796zm-8.098.48c.375 0 .703-.19.898-.48h-1.796c.194.29.523.48.898.48zm25.2 1.44a1.08 1.08 0 110 2.16 1.08 1.08 0 010-2.16zm-7.2 0a1.08 1.08 0 100 2.16 1.08 1.08 0 000-2.16zm3.6-1.44c.375 0 .705-.19.898-.48h-1.796c.194.29.523.48.898.48zM37.798 60a1.078 1.078 0 01-1.796 0h1.796zm18.182 3a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zm2.52-2.52c.375 0 .703-.19.898-.48h-1.796c.194.29.523.48.898.48zM52.198 60a1.078 1.078 0 01-1.796 0h1.796zM4.5 76.32a1.08 1.08 0 100 2.16 1.08 1.08 0 000-2.16zm0-5.04a1.08 1.08 0 110-2.161 1.08 1.08 0 010 2.161zM.9 65.52c.596 0 1.08.483 1.08 1.08v-.002a1.08 1.08 0 01-1.503.995 58.2 58.2 0 01-.204-1.872c.177-.127.393-.202.627-.202zm.48 7.314c.118.543.245 1.084.377 1.621a1.075 1.075 0 00-.377-1.621zM17.82 77.4a1.08 1.08 0 112.16 0 1.08 1.08 0 01-2.16 0zm0-7.2a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zm-6.12 6.12a1.08 1.08 0 100 2.16 1.08 1.08 0 000-2.16zm0-7.2a1.08 1.08 0 110 2.16 1.08 1.08 0 010-2.16zm4.68-2.52a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zm-1.08 6.12a1.08 1.08 0 110 2.16 1.08 1.08 0 010-2.16zM9.18 66.6a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zM8.1 72.72a1.08 1.08 0 110 2.16 1.08 1.08 0 010-2.16zm25.2 3.6a1.08 1.08 0 100 2.16 1.08 1.08 0 000-2.16zm0-5.04a1.08 1.08 0 110-2.161 1.08 1.08 0 010 2.161zm-8.28 6.12a1.08 1.08 0 112.16 0 1.08 1.08 0 01-2.16 0zm2.16-7.2a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zm2.52-4.68a1.08 1.08 0 110 2.16 1.08 1.08 0 010-2.16zm1.08 8.28a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zm-8.28-8.28a1.08 1.08 0 110 2.16 1.08 1.08 0 010-2.16zm1.08 8.28a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zm23.04 3.6a1.08 1.08 0 112.161 0 1.08 1.08 0 01-2.161 0zm0-7.2a1.081 1.081 0 102.162 0 1.081 1.081 0 00-2.162 0zm-6.12 6.12a1.08 1.08 0 100 2.16 1.08 1.08 0 000-2.16zm0-7.2a1.08 1.08 0 110 2.16 1.08 1.08 0 010-2.16zm2.52-2.52a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zm1.08 8.28a1.08 1.08 0 110-2.161 1.08 1.08 0 010 2.161zm-8.28-8.28a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zm1.08 8.28a1.08 1.08 0 110-2.161 1.08 1.08 0 010 2.161zm18 1.44a1.08 1.08 0 100 2.16 1.08 1.08 0 000-2.16zm-1.08-6.12a1.08 1.08 0 112.16 0 1.08 1.08 0 01-2.16 0zm3.6-3.6a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zm1.08 8.28a1.08 1.08 0 110-2.161 1.08 1.08 0 010 2.161zm-6.12-8.28a1.08 1.08 0 10-2.16 0 1.08 1.08 0 002.16 0zm-1.08 8.28a1.08 1.08 0 110-2.161 1.08 1.08 0 010 2.161zM17.82 91.8a1.08 1.08 0 112.161 0 1.08 1.08 0 01-2.161 0zm0-7.2a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zm-6.12 6.12a1.08 1.08 0 100 2.161 1.08 1.08 0 000-2.161zm0-7.2a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zm-6.225 1.544a62.995 62.995 0 01-.668-1.5 1.082 1.082 0 01.668 1.5zM15.3 79.92a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zm1.08 8.28a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zM8.1 79.92a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zm1.08 8.28a1.08 1.08 0 00-2.159-.02c.168.315.338.625.51.936a1.08 1.08 0 001.65-.916zm23.04 3.6a1.08 1.08 0 112.161 0 1.08 1.08 0 01-2.161 0zm0-7.2a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zm-6.12 6.12a1.08 1.08 0 100 2.161 1.08 1.08 0 000-2.161zm0-7.2a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zM30.78 81a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zm-1.08 6.12a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zM23.58 81a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zm-1.08 6.12a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zm25.09 3.605a1.08 1.08 0 10.222 2.15 1.08 1.08 0 00-.223-2.15zm0-5.05a1.081 1.081 0 11.222-2.15 1.081 1.081 0 01-.223 2.15zM39.42 91.8a1.08 1.08 0 112.161 0 1.08 1.08 0 01-2.161 0zm0-7.2a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zm4.68-4.68a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zm-1.08 8.28a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zm-6.12-6.12a1.08 1.08 0 110-2.161 1.08 1.08 0 010 2.161zm-1.08 6.12a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zm18 3.6a1.08 1.08 0 112.161 0 1.08 1.08 0 01-2.161 0zm0-7.2a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zm4.68-4.68a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zm-1.08 8.28a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zm-6.12-6.12a1.08 1.08 0 110-2.16 1.08 1.08 0 010 2.16zm-1.08 6.12a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zM18.9 100.08a1.08 1.08 0 110-2.16 1.08 1.08 0 010 2.16zm-2.52-4.68a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zm15.84 10.8a1.08 1.08 0 112.16.001 1.08 1.08 0 01-2.16-.001zm0-7.2a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zm-6.12 6.12a1.08 1.08 0 100 2.161 1.08 1.08 0 000-2.161zm0-7.2a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zm3.6-3.6a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zm1.08 8.28a1.08 1.08 0 10-2.16.001 1.08 1.08 0 002.16-.001zm-8.28-8.28a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zm1.08 8.28a1.08 1.08 0 10-2.16.001 1.08 1.08 0 002.16-.001zm23.04 3.6a1.08 1.08 0 112.16 0 1.08 1.08 0 01-2.16 0zm0-7.2a1.08 1.08 0 102.161 0 1.08 1.08 0 00-2.161 0zm-6.12 6.12a1.08 1.08 0 100 2.161 1.08 1.08 0 000-2.161zm0-7.2a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zm4.68-2.52a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zm-1.08 6.12a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zm-6.12-6.12a1.08 1.08 0 10-2.161 0 1.08 1.08 0 002.161 0zm-1.08 6.12a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zm18 3.6a1.08 1.08 0 100 2.161 1.08 1.08 0 000-2.161zm0-5.04a1.08 1.08 0 110-2.16 1.08 1.08 0 010 2.16zm2.52-4.68a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zm1.08 8.28a1.08 1.08 0 110-2.161 1.08 1.08 0 010 2.161zm-8.28-8.28a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zm1.08 8.28a1.08 1.08 0 110-2.161 1.08 1.08 0 010 2.161zm-19.06 9.52a60.04 60.04 0 001.323.672c.176.087.352.174.53.26a1.08 1.08 0 10-1.853-.932zm-2.54-4.48a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zm16.92 4.68a1.08 1.08 0 102.16 0 1.08 1.08 0 00-2.16 0zm-5.04 0a1.08 1.08 0 10-2.16.001 1.08 1.08 0 002.16-.001zm3.6-3.6a1.08 1.08 0 10-2.16.001 1.08 1.08 0 002.16-.001zm-1.08 6.12a1.08 1.08 0 01.441 2.066 65.45 65.45 0 01-1.327-.37 1.08 1.08 0 01.886-1.696zm-6.12-6.12a1.08 1.08 0 10-2.16.001 1.08 1.08 0 002.16-.001zm16.288 9.925c.177-.129.396-.205.632-.205h.002c.302 0 .573.123.77.323-.47-.034-.938-.073-1.404-.118zm-.448-6.325a1.08 1.08 0 102.16-.001 1.08 1.08 0 00-2.16.001zm5.76-3.6a1.08 1.08 0 10-2.16.001 1.08 1.08 0 002.16-.001zm-1.08 6.12a1.08 1.08 0 110 2.161 1.08 1.08 0 010-2.161zm-8.28-6.12a1.08 1.08 0 102.16-.001 1.08 1.08 0 00-2.16.001zm1.08 8.28a1.08 1.08 0 110-2.161 1.08 1.08 0 010 2.161zM0 60h60C60 26.864 33.138 0 0 0v60zm60 0c0 33.138 26.862 60 60 60V60H60z" fill="' + color + '"/></g><defs><clipPath id="prefix__clip0_44_635"><path fill="#fff" d="M0 0h120v120H0z"/></clipPath></defs></svg>'
			},

			'bricks': {
				'default': '<svg width="42" height="44" viewBox="0 0 42 44" xmlns="http://www.w3.org/2000/svg"><g id="Page-1" fill="none" fill-rule="evenodd"><g id="brick-wall" fill="' + color + '"><path d="M0 0h42v44H0V0zm1 1h40v20H1V1zM0 23h20v20H0V23zm22 0h20v20H22V23z"/></g></g></svg>',
				'inverted': '<svg width="42" height="44" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M42 43V23H22v20h20zM0 23v20h20V23H0zM41 1H1v20h40V1z" fill="' + color + '"/></svg>'
			},

			'circles': {
				'default': '<svg width="120" height="120" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.488 30c0 15.747 12.765 28.512 28.512 28.512S58.512 45.747 58.512 30 45.747 1.488 30 1.488 1.488 14.253 1.488 30zM30 60C13.431 60 0 46.569 0 30 0 13.431 13.431 0 30 0c16.569 0 30 13.431 30 30 0 16.569-13.431 30-30 30z" fill="' + color + '"/><path fill-rule="evenodd" clip-rule="evenodd" d="M11.47 30c0 10.234 8.296 18.53 18.53 18.53 10.234 0 18.53-8.296 18.53-18.53 0-10.234-8.296-18.53-18.53-18.53-10.234 0-18.53 8.296-18.53 18.53zM30 50c-11.046 0-20-8.954-20-20s8.954-20 20-20 20 8.954 20 20-8.954 20-20 20zM1.488 90c0 15.747 12.765 28.512 28.512 28.512S58.512 105.747 58.512 90 45.747 61.488 30 61.488 1.488 74.253 1.488 90zM30 120c-16.569 0-30-13.431-30-30 0-16.569 13.431-30 30-30 16.569 0 30 13.431 30 30 0 16.569-13.431 30-30 30z" fill="' + color + '"/><path fill-rule="evenodd" clip-rule="evenodd" d="M11.47 90c0 10.234 8.296 18.531 18.53 18.531 10.234 0 18.53-8.297 18.53-18.531 0-10.234-8.296-18.53-18.53-18.53-10.234 0-18.53 8.296-18.53 18.53zM30 110c-11.046 0-20-8.954-20-20s8.954-20 20-20 20 8.954 20 20-8.954 20-20 20zM61.488 30c0 15.747 12.765 28.512 28.512 28.512S118.512 45.747 118.512 30 105.747 1.488 90 1.488 61.488 14.253 61.488 30zM90 60c-16.569 0-30-13.431-30-30C60 13.431 73.431 0 90 0c16.569 0 30 13.431 30 30 0 16.569-13.431 30-30 30z" fill="' + color + '"/><path fill-rule="evenodd" clip-rule="evenodd" d="M71.47 30c0 10.234 8.296 18.53 18.53 18.53 10.234 0 18.531-8.296 18.531-18.53 0-10.234-8.297-18.53-18.531-18.53-10.234 0-18.53 8.296-18.53 18.53zM90 50c-11.046 0-20-8.954-20-20s8.954-20 20-20 20 8.954 20 20-8.954 20-20 20zM61.488 90c0 15.747 12.765 28.512 28.512 28.512S118.512 105.747 118.512 90 105.747 61.488 90 61.488 61.488 74.253 61.488 90zM90 120c-16.569 0-30-13.431-30-30 0-16.569 13.431-30 30-30 16.569 0 30 13.431 30 30 0 16.569-13.431 30-30 30z" fill="' + color + '"/><path fill-rule="evenodd" clip-rule="evenodd" d="M71.47 90c0 10.234 8.296 18.531 18.53 18.531 10.234 0 18.531-8.297 18.531-18.531 0-10.234-8.297-18.53-18.531-18.53-10.234 0-18.53 8.296-18.53 18.53zM90 110c-11.046 0-20-8.954-20-20s8.954-20 20-20 20 8.954 20 20-8.954 20-20 20z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="120" height="120" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M120 0H0v120h120V0zm0 30c0 16.569-13.431 30-30 30-16.569 0-30-13.431-30-30C60 13.431 73.431 0 90 0c16.569 0 30 13.431 30 30zm0 60c0 16.569-13.431 30-30 30-16.564 0-29.993-13.425-30-29.987C59.993 106.575 46.564 120 30 120c-16.569 0-30-13.431-30-30 0-16.569 13.431-30 30-30 16.564 0 29.993 13.425 30 29.987C60.007 73.425 73.436 60 90 60c16.569 0 30 13.431 30 30zM0 30C0 13.431 13.431 0 30 0c16.569 0 30 13.431 30 30 0 16.569-13.431 30-30 30C13.431 60 0 46.569 0 30zm90 28.512c-15.747 0-28.512-12.765-28.512-28.512S74.253 1.488 90 1.488 118.512 14.253 118.512 30 105.747 58.512 90 58.512zM1.488 30c0 15.747 12.765 28.512 28.512 28.512S58.512 45.747 58.512 30 45.747 1.488 30 1.488 1.488 14.253 1.488 30zm9.981 0c0 10.234 8.297 18.53 18.531 18.53 10.234 0 18.53-8.296 18.53-18.53 0-10.234-8.296-18.53-18.53-18.53-10.234 0-18.53 8.296-18.53 18.53zM30 50c-11.046 0-20-8.954-20-20s8.954-20 20-20 20 8.954 20 20-8.954 20-20 20zm0 68.512c-15.747 0-28.512-12.765-28.512-28.512S14.253 61.488 30 61.488 58.512 74.253 58.512 90 45.747 118.512 30 118.512zM11.47 90c0 10.234 8.296 18.531 18.53 18.531 10.234 0 18.53-8.297 18.53-18.531 0-10.234-8.296-18.53-18.53-18.53-10.234 0-18.53 8.296-18.53 18.53zM30 110c-11.046 0-20-8.954-20-20s8.954-20 20-20 20 8.954 20 20-8.954 20-20 20zm41.47-80c0 10.234 8.296 18.53 18.53 18.53 10.234 0 18.531-8.296 18.531-18.53 0-10.234-8.297-18.53-18.531-18.53-10.234 0-18.53 8.296-18.53 18.53zM90 50c-11.046 0-20-8.954-20-20s8.954-20 20-20 20 8.954 20 20-8.954 20-20 20zm0 68.512c-15.747 0-28.512-12.765-28.512-28.512S74.253 61.488 90 61.488 118.512 74.253 118.512 90 105.747 118.512 90 118.512zM71.47 90c0 10.234 8.296 18.531 18.53 18.531 10.234 0 18.531-8.297 18.531-18.531 0-10.234-8.297-18.53-18.531-18.53-10.234 0-18.53 8.296-18.53 18.53zM90 110c-11.046 0-20-8.954-20-20s8.954-20 20-20 20 8.954 20 20-8.954 20-20 20z" fill="' + color + '"/></svg>'
			},

			'dots': {
				'default': '<svg width="60" height="60" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 4a2 2 0 11-4 0 2 2 0 014 0" fill="' + color + '"/></svg>',
				'inverted': '<svg width="60" height="60" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M60 0H0v60h60V0zM5.414 5.414a2 2 0 10-2.828-2.828 2 2 0 002.828 2.828z" fill="' + color + '"/></svg>'
			},

			'grid': {
				'default': '<svg width="40" height="40" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M20.5 20.5V40h-1V20.5H0v-1h19.5V0h1v19.5H40v1H20.5z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="40" height="40" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M19.5 0H0v19.5h19.5V0zm1 0v19.5H40V0H20.5zM40 20.5H20.5V40H40V20.5zM19.5 40V20.5H0V40h19.5z" fill="' + color + '"/></svg>'
			},

			'hexagon': {
				'default': '<svg width="28" height="49" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.99 9.25l13 7.5v15l-13 7.5L1 31.75v-15l12.99-7.5zM3 17.9v12.7l10.99 6.34 11-6.35V17.9l-11-6.34L3 17.9zM0 15l12.98-7.5V0h-2v6.35L0 12.69V15zm0 18.5L12.98 41v8h-2v-6.85L0 35.81V33.5zM15 0v7.5L27.99 15H28v-2.31h-.01L17 6.35V0h-2zm0 49v-8l12.99-7.5H28v2.31h-.01L17 42.15V49h-2z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="28" height="49" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.98 0H0v12.69l10.98-6.34V0zm2 0v7.5L0 15v18.5L12.98 41v8H15v-8l12.99-7.5H28V15h-.01L15 7.5V0h-2.02zM17 0v6.35l10.99 6.34H28V0H17zm11 35.81h-.01L17 42.15V49h11V35.81zM10.98 49v-6.85L0 35.81V49h10.98zm16.01-32.25l-13-7.5L1 16.75v15l12.99 7.5 13-7.5v-15zM3 30.6V17.9l10.99-6.34 11 6.34v12.69l-11 6.35L3 30.6z" fill="' + color + '"/></svg>'
			},

			'half-diamond': {
				'default': '<svg width="80" height="120" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_43_320)" fill="' + color + '"><path d="M40 0L0 30v30l40-30V0zm0 30v30l40 30V60L40 30zM40-30V0l40 30V0L40-30zm0 90L0 90v30l40-30V60zm0 30v30l40 30v-30L40 90z"/></g><defs><clipPath id="prefix__clip0_43_320"><path fill="#fff" d="M0 0h80v120H0z"/></clipPath></defs></svg>',
				'inverted': '<svg width="80" height="120" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M40 0H0v30L40 0zM0 60v30l40-30v30L0 120h40V90l40 30V90L40 60V30l40 30V30L40 0v30L0 60z" fill="' + color + '"/></svg>'
			},

			'half-circle': {
				'default': '<svg width="100" height="50" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_43_335)"><path fill-rule="evenodd" clip-rule="evenodd" d="M50 50c27.614 0 50-22.386 50-50v50H50zM0 0c0 27.614 22.386 50 50 50H0V0zm0 0c0-27.614 22.386-50 50-50s50 22.386 50 50H0z" fill="' + color + '"/></g><defs><clipPath id="prefix__clip0_43_335"><path fill="#fff" d="M0 0h100v50H0z"/></clipPath></defs></svg>',
				'inverted': '<svg width="100" height="50" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_43_332)"><path d="M50 50c27.614 0 50-22.386 50-50S77.614-50 50-50 0-27.614 0 0s22.386 50 50 50z" fill="' + color + '"/></g><defs><clipPath id="prefix__clip0_43_332"><path fill="#fff" d="M0 0h100v50H0z"/></clipPath></defs></svg>'
			},

			'pastel': {
				'default': '<svg width="75" height="75" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_43_338)"><path d="M32.763-11.976c-1.05-.075-1.95.676-2.024 1.726L29.764.849c-.075 1.05.675 1.95 1.725 2.026 1.05.075 1.95-.675 2.025-1.725l.975-11.1c.075-1.05-.675-1.95-1.725-2.025l-.001-.001zM54.299 1.32a1.911 1.911 0 00-.386.015c-.975.15-1.725 1.05-1.575 2.1l1.5 11.025c.15.975 1.05 1.725 2.1 1.575a1.732 1.732 0 001.575-2.1l-1.5-11.025c-.131-.853-.836-1.533-1.714-1.59zM7.369 2.54a1.809 1.809 0 00-1.662 1.663c-.075 1.05.675 1.952 1.65 2.027l11.1 1.05c.975.15 1.95-.601 2.025-1.651.15-.975-.6-1.95-1.65-2.025l-11.1-1.05a1.642 1.642 0 00-.363-.015v.001zM1.76 13.017a1.824 1.824 0 00-1.285.6l-7.65 8.101c-.75.75-.675 1.95.075 2.625s1.95.674 2.625-.076l7.651-8.099c.75-.75.674-1.95-.076-2.625a1.786 1.786 0 00-1.34-.526zm75 0a1.824 1.824 0 00-1.285.6l-7.65 8.101c-.75.75-.675 1.95.075 2.625s1.95.674 2.625-.076l7.651-8.099c.75-.75.674-1.95-.076-2.625a1.786 1.786 0 00-1.34-.526zm-39.731 2.906a1.786 1.786 0 00-1.34.527l-7.95 7.723c-.75.675-.826 1.875-.076 2.625.675.75 1.875.752 2.625.077l7.95-7.725c.75-.675.826-1.875.076-2.625a1.827 1.827 0 00-1.285-.602zm24.639 18.928c-.24.02-.48.085-.705.197a1.903 1.903 0 00-.825 2.55l5.1 9.902a1.902 1.902 0 002.55.824c.975-.45 1.276-1.574.826-2.55l-5.1-9.9c-.395-.73-1.125-1.083-1.846-1.023zm-50.37-4.862c-.372 0-.734.117-1.035.336-.825.6-1.05 1.725-.524 2.625l6.15 9.223c.6.9 1.8 1.127 2.625.526.9-.6 1.124-1.8.524-2.624l-6.15-9.226a1.911 1.911 0 00-1.59-.86zm32.705 9.766c-.12-.006-.243 0-.365.019l-10.95 2.175c-1.05.15-1.725 1.126-1.5 2.176.15 1.05 1.126 1.725 2.176 1.5l10.95-2.175c1.05-.15 1.725-1.125 1.5-2.175a1.99 1.99 0 00-1.811-1.52zm4.556 12.195a1.933 1.933 0 00-1.845.949c-.45.9-.15 2.025.75 2.55l9.75 5.4c.9.45 2.025.15 2.55-.75.525-.9.15-2.025-.75-2.55l-9.75-5.4c-.22-.11-.46-.178-.705-.199zM71.913 58c-1.05-.075-1.875.748-1.95 1.798l-.45 11.1c-.075 1.05.75 1.876 1.8 1.95.975 0 1.875-.75 1.95-1.8l.45-11.1c.075-1.05-.75-1.873-1.8-1.948zm-55.44 1.08c-.38.031-.741.178-1.035.42l-8.775 6.825c-.75.6-.9 1.8-.3 2.625.6.75 1.8.9 2.626.3l8.775-6.827c.75-.6.9-1.8.3-2.625a1.783 1.783 0 00-1.591-.72v.002zm16.29 3.945c-1.05-.075-1.95.675-2.024 1.725l-.975 11.099c-.075 1.05.675 1.95 1.725 2.026 1.05.075 1.95-.675 2.025-1.725l.975-11.102c.075-1.05-.675-1.95-1.725-2.024l-.001.001z" fill="' + color + '"/></g><defs><clipPath id="prefix__clip0_43_338"><path fill="#fff" d="M0 0h75v75H0z"/></clipPath></defs></svg>',
				'inverted': '<svg width="75" height="75" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M29.839 0l-.075.849c-.075 1.05.675 1.95 1.725 2.026 1.05.075 1.95-.675 2.025-1.725L33.615 0H75v14.12l-7.175 7.598c-.75.75-.675 1.95.075 2.625s1.95.674 2.625-.076L75 19.53V75H33.615l.874-9.952c.075-1.05-.675-1.95-1.725-2.024l-.001.001c-1.05-.075-1.95.675-2.024 1.725l-.9 10.25H0V19.53l3.176-3.362c.75-.75.674-1.95-.076-2.625a1.786 1.786 0 00-1.34-.526 1.824 1.824 0 00-1.285.6L0 14.12V0h29.839zm24.074 1.335c.128-.018.257-.023.386-.015.878.057 1.583.737 1.714 1.59l1.5 11.025a1.732 1.732 0 01-1.575 2.1c-1.05.15-1.95-.6-2.1-1.575l-1.5-11.025c-.15-1.05.6-1.95 1.575-2.1zM6.231 3.065a1.809 1.809 0 011.138-.525v-.001c.121-.008.243-.003.363.015l11.1 1.05c1.05.075 1.8 1.05 1.65 2.025-.075 1.05-1.05 1.801-2.025 1.651l-11.1-1.05c-.975-.075-1.725-.977-1.65-2.027.033-.43.22-.833.524-1.138zm30.072 12.98a1.826 1.826 0 012.01.48c.751.75.675 1.95-.075 2.625l-7.95 7.725c-.75.675-1.95.673-2.625-.077-.75-.75-.674-1.95.076-2.625l7.95-7.723c.175-.176.384-.314.614-.405zm24.66 19.003c.225-.112.465-.177.705-.197.721-.06 1.451.293 1.846 1.023l5.1 9.9c.45.976.149 2.1-.826 2.55a1.902 1.902 0 01-2.55-.824l-5.1-9.902a1.903 1.903 0 01.825-2.55zm-50.7-4.723c.3-.219.663-.336 1.035-.336a1.913 1.913 0 011.59.86l6.15 9.226c.6.824.376 2.024-.524 2.624-.825.601-2.025.374-2.625-.526l-6.15-9.223c-.526-.9-.301-2.025.524-2.625zm33.375 9.449c.122-.019.245-.025.365-.019a1.99 1.99 0 011.811 1.52c.225 1.05-.45 2.025-1.5 2.175l-10.95 2.175c-1.05.225-2.026-.45-2.176-1.5-.225-1.05.45-2.026 1.5-2.176l10.95-2.175zm3.86 12.382a1.958 1.958 0 011.766-.007l9.75 5.4c.9.525 1.275 1.65.75 2.55-.525.9-1.65 1.2-2.55.75l-9.75-5.4c-.9-.525-1.2-1.65-.75-2.55.185-.317.458-.575.784-.743zm22.465 7.642c.075-1.05.9-1.873 1.95-1.798 1.05.075 1.875.898 1.8 1.948l-.45 11.1c-.075 1.05-.975 1.8-1.95 1.8-1.05-.074-1.875-.9-1.8-1.95l.45-11.1zM15.438 59.5a1.866 1.866 0 011.035-.42v-.002a1.783 1.783 0 011.591.72c.6.825.45 2.025-.3 2.625L8.989 69.25c-.826.6-2.026.45-2.626-.3-.6-.825-.45-2.025.3-2.625l8.775-6.825z" fill="' + color + '"/></svg>'
			},

			'square': {
				'default': '<svg width="72" height="72" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M72 51.036v3.133H13.022l-3.334 2.96L0 57.178v-6.14h72zM72 42.13v3.132H22.794l-3.334 2.96L0 48.325v-6.195h72zM72 33.063v3.132H33.01l-3.334 2.961L0 39.305v-6.242h72zM72 24.156v3.132H42.78l-3.334 2.962L0 30.45v-6.295h72zM72 15.25v3.132H52.996l-3.334 2.96L0 21.593v-6.343h72zM72 6.343v3.132h-9.232l-3.334 2.961L0 12.738V6.343h72zM72 0v1.208l-2.499 2.22L0 3.779V0h72zM72 60.043v3.133H2.955L0 65.8v-5.756h72zM72 68.95H0V72h72v-3.05z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="72" height="68" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M72 5.343V.208l-2.499 2.22L0 2.779v2.565h72zm0 3.132h-9.232l-3.334 2.961L0 11.738v2.511h72V8.475zm0 8.907H52.996l-3.334 2.96L0 20.593v2.564h72v-5.774zm0 8.906H42.78l-3.334 2.962L0 29.45v2.611h72v-5.774zm0 8.907H33.01l-3.334 2.961L0 38.305v2.824h72v-5.934zm0 9.067H22.794l-3.334 2.96L0 47.325v2.712h72v-5.774zm0 8.907H13.022l-3.334 2.96L0 56.178v2.866h72V53.17zm0 9.007H2.955L0 64.8v3.151h72v-5.774z" fill="' + color + '"/></svg>'
			},

			'square-2': {
				'default': '<svg width="60" height="60" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M26 1h-7a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3V4a3 3 0 00-3-3zM56 1h-7a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3V4a3 3 0 00-3-3zM11 31H4a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-7a3 3 0 00-3-3zM26 31h-7a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-7a3 3 0 00-3-3zM41 31h-7a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-7a3 3 0 00-3-3zM11 16H4a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-7a3 3 0 00-3-3zM41 16h-7a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-7a3 3 0 00-3-3zM56 16h-7a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-7a3 3 0 00-3-3zM26 46h-7a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-7a3 3 0 00-3-3zM56 46h-7a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-7a3 3 0 00-3-3z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="60" height="60" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M60 0H0v60h60V0zM19 46a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-7a3 3 0 00-3-3h-7zM4 31a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-7a3 3 0 00-3-3H4zM16 4a3 3 0 013-3h7a3 3 0 013 3v7a3 3 0 01-3 3h-7a3 3 0 01-3-3V4zm30 0a3 3 0 013-3h7a3 3 0 013 3v7a3 3 0 01-3 3h-7a3 3 0 01-3-3V4zM16 34a3 3 0 013-3h7a3 3 0 013 3v7a3 3 0 01-3 3h-7a3 3 0 01-3-3v-7zm18-3a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-7a3 3 0 00-3-3h-7zM1 19a3 3 0 013-3h7a3 3 0 013 3v7a3 3 0 01-3 3H4a3 3 0 01-3-3v-7zm33-3a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-7a3 3 0 00-3-3h-7zm12 3a3 3 0 013-3h7a3 3 0 013 3v7a3 3 0 01-3 3h-7a3 3 0 01-3-3v-7zm0 30a3 3 0 013-3h7a3 3 0 013 3v7a3 3 0 01-3 3h-7a3 3 0 01-3-3v-7z" fill="' + color + '"/></svg>'
			},

			'triangle': {
				'default': '<svg width="100" height="100" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 0v100h1v-1l99-99H83L1 82V0H0zM98.178 85L100 83v17H83l15.178-15z" fill="' + color + '"/><path d="M84 99H1v1h83v-1z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="99" height="100" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M82 0H0v82L82 0zm17 83V0L0 99h83v.012L97.178 85 99 83z" fill="' + color + '"/></svg>'
			},

			'triangle-2': {
				'default': '<svg width="60" height="60" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M30 30h30V0L45 15 30 30z" fill="' + color + '" fill-opacity=".25"/><path d="M60 60H30V30l15 15 15 15z" fill="' + color + '" fill-opacity=".75"/><path d="M0 60h30V30L15 45 0 60z" fill="' + color + '" fill-opacity=".1"/><path d="M60 0H30v30l15-15L60 0z" fill="' + color + '" fill-opacity=".05"/><path d="M30 30H0v30l15-15 15-15z" fill="' + color + '" fill-opacity=".5"/><path d="M30 30H0V0l15 15 15 15z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="60" height="60" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M30 30h30V0L45 15 30 30z" fill="' + color + '" fill-opacity=".25"/><path d="M60 60H30V30l15 15 15 15z" fill="' + color + '" fill-opacity=".75"/><path d="M0 60h30V30L15 45 0 60z" fill="' + color + '" fill-opacity=".1"/><path d="M60 0H30v30l15-15L60 0z" fill="' + color + '" fill-opacity=".05"/><path d="M30 30H0v30l15-15 15-15z" fill="' + color + '" fill-opacity=".5"/><path d="M30 30H0V0l15 15 15 15z" fill="' + color + '"/><path d="M0 0h30v30L15 15 0 0zM30 30h30v30L45 45 30 30z" fill="' + color + '"/></svg>'
			},

			'triangle-3': {
				'default': '<svg width="60" height="60" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_44_683)" fill="' + color + '"><path d="M28.48 45.76l-.127.063.254-.129-.127.066zM24.004 47.996l-.255.128.51-.257-.255.129z"/><path d="M60 30L28.607 45.695v-31.39L60 30zM28.352 14.178v31.644l-4.093 2.046V12.131l4.093 2.046zM23.75 11.875v36.25l-3.463 1.732V10.143l3.462 1.732zM19.268 9.635v40.73l-2.291 1.146V8.489l2.291 1.146zM15.449 7.726v44.548l-1.563.78V6.945l1.563.78zM11.849 5.927v48.146l-1.222.613V5.313l1.222.614zM8.08 4.044v51.912l-.804.406V3.638l.805.406zM4.221 2.112v55.775l-.464.234V1.878l.464.234zM.193.097v59.806L0 60V0l.193.097zM31.52 15.76l.127.063-.254-.129.127.066zM35.996 17.996l.255.128-.51-.256.255.128z"/><path d="M0 0l31.393 15.695v-31.39L0 0zM31.648-15.822v31.645l4.093 2.045v-35.737l-4.093 2.046zM36.25-18.125v36.25l3.463 1.732v-39.714l-3.462 1.732zM40.732-20.365v40.73l2.291 1.146v-43.022l-2.291 1.146zM44.551-22.274v44.548l1.563.78v-46.109l-1.563.78zM48.151-24.073v48.146l1.222.613v-49.373l-1.222.614zM51.92-25.956v51.912l.804.406v-52.724l-.805.406zM55.779-27.888v55.775l.464.234v-56.243l-.464.234zM59.807-29.903v59.806L60 30v-60l-.193.097zM0 60l31.393 15.695v-31.39L0 60zM31.648 44.178v31.645l4.093 2.045V42.131l-4.093 2.047zM36.25 41.875v36.25l3.463 1.732V40.143l-3.462 1.732zM40.732 39.635v40.73l2.291 1.146V38.489l-2.291 1.146zM44.551 37.726v44.548l1.563.78V36.945l-1.563.78zM48.151 35.927v48.146l1.222.613V35.313l-1.222.614zM51.92 34.044v51.912l.804.406V33.638l-.805.406zM55.779 32.112v55.775l.464.234V31.878l-.464.234zM59.807 30.097v59.806L60 90V30l-.193.097z"/></g><defs><clipPath id="prefix__clip0_44_683"><path fill="#fff" d="M0 0h60v60H0z"/></clipPath></defs></svg>',
				'inverted': '<svg width="60" height="60" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_44_680)"><path fill-rule="evenodd" clip-rule="evenodd" d="M.193.097v59.806l3.564-1.782V1.878L0 0h31.393v15.694L3.786 1.894l.435.22v55.774l-.45.227 27.622-13.809V60H0l31.393 15.695.127.066.127.062-.254-.129V60h.255v15.823l4.093 2.045.51.256 3.462 1.733V60h1.019v20.365l2.291 1.146V60h1.528v22.274l1.563.78V60h2.037v24.073l1.222.613V60h2.546v25.956l.805.406V60h3.055v27.887l.464.234V60h3.564v29.903L60 90V60h-.193V30.097L60 30l-3.757 1.878V60h-.464V32.112l.436-.22-27.608 13.803v-31.39l27.621 13.809-.45-.227V0h.465v28.121l3.564 1.782V0H60v-30l-.193.097V0h-3.564v-28.122l-.464.234V0h-3.055v-26.362l-.805.406V0h-2.546v-24.686l-1.222.613V0h-2.037v-23.055l-1.563.78V0h-1.528v-21.511l-2.291 1.146V0h-1.019v-19.857l-3.462 1.732V0h-.51v-17.869l-4.093 2.046V0h-.255v-15.695L0 0l.193.097zm35.548 17.77v.001l-4.093-2.045V0h4.093v17.867zm.51.257l-.51-.257.255.129.255.128zm0 0V0h3.462v19.857l-3.462-1.733zM40.732 0v20.365l2.291 1.146V0h-2.291zm3.82 0v22.274l1.563.78V0H44.55zm3.6 0v24.073l1.221.613V0h-1.222zm3.767 0v25.956l.805.406V0h-.805zM28.607 45.694l-.127.066-.127.063.254-.129zM52.724 60V33.638l-.805.406V60h.805zm-3.35 0V35.313l-1.223.614V60h1.222zm-3.26 0V36.945l-1.563.78V60h1.563zm-3.09 0V38.489l-2.292 1.146V60h2.291zm-6.773 0v18.124l-.255-.128-.255-.129V60h.51zm0 0V41.875l3.462-1.732V60h-3.462zm-.51 0h-4.093V44.178l4.093-2.047V60zm-4.348-44.306l.254.128-.127-.061-.127-.067zM8.08 4.044v51.912l-.805.406V3.638l.805.406zm3.768 50.03V5.926l-1.222-.614v49.373l1.222-.613zm3.6-46.348v44.548l-1.563.78V6.945l1.563.78zm3.819 42.639V9.635L16.977 8.49V51.51l2.291-1.146zm4.482-38.49v36.25l-3.463 1.732V10.143l3.463 1.732zm.509 35.992l-.255.129-.255.128.51-.257zm0 0V12.131l4.093 2.046v31.645l-4.093 2.046z" fill="' + color + '"/></g><defs><clipPath id="prefix__clip0_44_680"><path fill="#fff" d="M0 0h60v60H0z"/></clipPath></defs></svg>'
			},

			'wave': {
				'default': '<svg width="160" height="120" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_43_381)" stroke="' + color + '" stroke-width="2"><path d="M-66.839 19.027C-44.46 18.538-22.38 7.377 0 7.5c22.383.123 57.617 15 80 15 22.383 0 57.617-14.877 80-15 22.381-.123 44.461 11.037 66.839 11.527M-66.839 49.028C-44.46 48.537-22.38 37.377 0 37.5c22.383.123 57.617 15 80 15 22.383 0 57.617-14.877 80-15 22.381-.123 44.461 11.037 66.839 11.528M-66.839 79.028C-44.46 78.537-22.38 67.377 0 67.5c22.383.123 57.617 15 80 15 22.383 0 57.617-14.877 80-15 22.381-.123 44.461 11.037 66.839 11.528M-66.839 109.027C-44.46 108.537-22.38 97.377 0 97.5c22.383.123 57.617 15 80 15 22.383 0 57.617-14.877 80-15 22.381-.123 44.461 11.037 66.839 11.527"/></g><defs><clipPath id="prefix__clip0_43_381"><path fill="#fff" d="M0 0h160v120H0z"/></clipPath></defs></svg>',
				'inverted': '<svg width="160" height="120" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M160 0H0v6.5h.006c11.256.062 25.663 3.797 39.912 7.492l.333.086C54.701 17.825 68.965 21.5 80 21.5c11.035 0 25.299-3.675 39.749-7.422l.333-.086c14.249-3.695 28.656-7.43 39.913-7.492H160V0zm0 8.5c-11.005.062-25.218 3.747-39.624 7.482l-.125.032C105.892 19.737 91.347 23.5 80 23.5c-11.347 0-25.892-3.763-40.251-7.486l-.125-.032C25.218 12.247 11.005 8.562 0 8.5v28h.006c11.256.062 25.663 3.797 39.912 7.492l.333.086C54.701 47.825 68.965 51.5 80 51.5c11.035 0 25.299-3.675 39.749-7.422l.333-.086c14.249-3.695 28.656-7.43 39.913-7.492H160v-28zm0 30c-11.005.062-25.218 3.747-39.624 7.482l-.125.032C105.892 49.737 91.347 53.5 80 53.5c-11.347 0-25.892-3.763-40.251-7.486l-.125-.032C25.218 42.247 11.005 38.562 0 38.5v28h.006c11.256.062 25.663 3.797 39.912 7.492l.333.086C54.701 77.825 68.965 81.5 80 81.5c11.035 0 25.299-3.675 39.749-7.422l.333-.086c14.249-3.695 28.656-7.43 39.913-7.492H160v-28zm0 30c-11.005.062-25.218 3.747-39.624 7.482l-.125.032C105.892 79.737 91.347 83.5 80 83.5c-11.347 0-25.892-3.763-40.251-7.486l-.125-.032C25.218 72.247 11.005 68.562 0 68.5v28h.006c11.256.062 25.663 3.797 39.912 7.492l.333.086C54.701 107.825 68.965 111.5 80 111.5c11.035 0 25.299-3.675 39.749-7.422l.333-.086c14.249-3.695 28.656-7.43 39.913-7.492H160v-28zm0 30c-11.005.062-25.218 3.747-39.624 7.482l-.125.032C105.892 109.737 91.347 113.5 80 113.5c-11.347 0-25.892-3.763-40.251-7.486l-.125-.032C25.218 102.247 11.005 98.562 0 98.5V120h160V98.5z" fill="' + color + '"/></svg>'
			},

			'x': {
				'default': '<svg width="40" height="40" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0 38.59l2.83-2.83 1.41 1.41L1.41 40H0v-1.41zM0 1.4l2.83 2.83 1.41-1.41L1.41 0H0v1.41-.01zM38.59 40l-2.83-2.83 1.41-1.41L40 38.59V40h-1.41zM40 1.41l-2.83 2.83-1.41-1.41L38.59 0H40v1.41z" fill="' + color + '"/><path d="M22.83 15.77L20 18.6v-.01l-2.83-2.83-1.41 1.41L18.59 20l-2.83 2.83 1.41 1.41L20 21.41l2.83 2.83 1.41-1.41L21.41 20l2.83-2.82-1.41-1.41z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="40" height="40" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.41 0h37.18l-2.83 2.83 1.41 1.41L40 1.41v37.18l-2.83-2.83-1.41 1.41L38.59 40H1.41l2.83-2.83-1.41-1.41L0 38.59V1.4l2.83 2.83 1.41-1.41L1.41 0zM20 18.6l2.83-2.83 1.41 1.41L21.41 20l2.83 2.83-1.41 1.41L20 21.41l-2.83 2.83-1.41-1.41L18.59 20l-2.83-2.83 1.41-1.41L20 18.59v.01z" fill="' + color + '"/></svg>'
			}
		};

		return patterns[ name ] ? patterns[ name ][ style ] : '';
	},

	/**
	 * Get pattern background element.
	 *
	 * @since 3.8
	 * @param {Object} values - The values.
	 * @return {String}
	 */
	fusionGetPatternElement: function( values ) {
				// Early exit if no pattern selected.
				if ( '' === values.pattern_bg ) {
					return;
				}
				let style = '';
				if ( 'custom' === values.pattern_bg ) {
					style += 'background-image:  url(' + values.pattern_custom_bg + ');';
				} else {
					style += 'background-image:  url(data:image/svg+xml;base64,' + window.btoa( this.fusionGetPattern( values.pattern_bg, values.pattern_bg_color, values.pattern_bg_style ) ) + ');';
				}

				if ( '' !== values.pattern_bg_opacity ) {
					style += 'opacity: ' + ( parseInt( values.pattern_bg_opacity ) / 100 ) + ' ;';
				}
				if ( '' !== values.pattern_bg_size ) {
					style += 'background-size:' + values.pattern_bg_size + ';';
				}
				if ( '' !== values.pattern_bg_blend_mode ) {
					style += 'mix-blend-mode:' + values.pattern_bg_blend_mode + ';';
				}

				const element = '<div class="awb-background-pattern" style="' + style + '"></div>';

				return element;
	},

	/**
	 * Get mask.
	 *
	 * @since 3.8
	 * @param {String} name - The selected mask name.
	 * @param {String} color - The mask color.
	 * @param {String} style - The mask style default|inverted.
	 * @return {String}
	 */
	fusionGetMask( name, color, style, accent_color ) {
		style = style || 'default';
		color = jQuery.AWB_Color( color ).toRgbaString() || 'rgba(255,255,255,1)';
		accent_color = accent_color ? jQuery.AWB_Color( accent_color ).toRgbaString() : color;

		const masks = {
			'mask-1': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M904.977 0H0v954h807.883l581.057-580.912.16-.153c117.27-117.238 307.32-117.316 424.49-.174 117.17 117.143 117.09 307.146-.17 424.384L1656.52 954H1920V0h-166.39l-580.96 580.813c-117.27 117.239-307.318 117.317-424.491.174-117.172-117.142-117.094-307.145.174-424.383L904.977 0z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_58_35)"><path fill-rule="evenodd" clip-rule="evenodd" d="M1773.6-19.976l-600.95 600.789c-117.27 117.239-307.318 117.317-424.491.174-117.172-117.142-117.094-307.145.175-424.383l600.946-600.79 424.32 424.21zM1212.47 1397.93l600.95-600.785c117.26-117.238 117.34-307.241.17-424.384-117.17-117.142-307.22-117.064-424.49.174l-.16.153-600.787 600.637 424.317 424.205z" fill="' + color + '"/></g><defs><clipPath id="prefix__clip0_58_35"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath></defs></svg>'
            },
			'mask-2': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1920 445.163c-2.92 3.137-5.9 6.234-8.96 9.288-123.43 123.436-316.13 135.398-452.99 35.887l245 184.622c-116.76 154.952-336.57 186.262-490.94 69.933-36.72-27.669-66.43-61.199-88.81-98.322l157.02 271.978c-124.14 71.672-282.874 29.138-354.546-95.001-71.672-124.14-29.139-282.876 94.996-354.548l95.91 166.116c-64.81-117.365-58.83-267.08 27.33-381.421l304.5 229.454a353.453 353.453 0 01-32.44-28.698C1292.2 330.576 1280.59 136.947 1381.25 0H0v954h1920V445.163z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_58_68)" fill="' + color + '"><circle cx="1663.56" cy="206.967" r="349.995" transform="rotate(-45 1663.56 206.967)"/><path d="M1703.05 674.96c-116.77 154.952-336.57 186.262-490.94 69.933s-184.86-336.246-68.1-491.198l559.04 421.265z"/><path d="M1280.32 918.549c-124.14 71.672-282.874 29.138-354.546-95.001-71.672-124.14-29.139-282.876 94.996-354.548l259.55 449.549z"/></g><defs><clipPath id="prefix__clip0_58_68"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath></defs></svg>'
            },
			'mask-3': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1087.8 0H0v954h1087.8S1252 777.5 1252 477 1087.8 0 1087.8 0z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1920 0h-832.2S1252 176.5 1252 477s-164.2 477-164.2 477H1920V0z" fill="' + color + '"/></svg>'
            },
			'mask-4': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1920 0H0v954h778.074l242.786-434.234c6.47-11.566 22.45-20.942 35.71-20.942h109.86l205.43-371.883c6.47-11.565 22.46-20.941 35.71-20.941h376.02c30.93 0 43.77 21.877 28.68 48.863L1371 954h88.38l296.28-534.011c6.47-11.664 22.45-21.12 35.7-21.12H1920V0z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_58_109)" fill="' + color + '"><path d="M1020.86 519.766c6.47-11.566 22.45-20.942 35.71-20.942h375.02c30.93 0 43.77 21.877 28.68 48.863L1204.02 1006H749l271.86-486.234zM1755.66 419.989c6.47-11.664 22.45-21.12 35.7-21.12h391.65c26.5 0 37.5 18.912 24.57 42.24L1923 954h-463.62l296.28-534.011z"/><path d="M1371.86 126.941c6.47-11.565 22.46-20.941 35.71-20.941h376.02c30.93 0 43.77 21.877 28.68 48.863L1371 954H914.98l456.88-827.059z"/></g><defs><clipPath id="prefix__clip0_58_109"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath></defs></svg>'
            },
			'mask-5': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1825.81 268.638c-6.34 9.063-8.07 20.566-5.16 31.236 4.79 17.562 7.35 36.045 7.35 55.126 0 96.169-64.95 177.168-153.38 201.519-20.69 5.696-34.4 28.702-29.1 49.493 3.59 14.108 15.52 25.271 29.92 27.399C1783.26 649.339 1866 742.257 1866 854.5c0 35.748-8.39 69.535-23.31 99.5H1920V0h-41.08c5.29 23.823 8.08 48.586 8.08 74 0 72.392-22.62 139.5-61.19 194.638zM1215.08 0c-5.29 23.823-8.08 48.586-8.08 74 0 46.922 9.5 91.624 26.69 132.285 7.71 18.227-.51 39.945-17.44 50.19C1160.96 289.938 1124 350.653 1124 420c0 10.258.81 20.327 2.37 30.146 3.08 19.43-15.7 40.854-35.37 40.854-93.888 0-170 76.112-170 170s76.112 170 170 170c41.09 0 78.78-14.579 108.17-38.847 21.89-18.078 66.76-16.212 85.59 5.041 27.99 31.604 65.37 54.706 107.77 64.945 15.25 3.684 27.18 16.239 29.92 31.69A221.86 221.86 0 001442.31 954H0V0h1215.08z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_58_127)"><path fill-rule="evenodd" clip-rule="evenodd" d="M1820.65 299.874c-2.91-10.67-1.18-22.173 5.16-31.236C1864.38 213.5 1887 146.392 1887 74c0-187.777-152.22-340-340-340s-340 152.223-340 340c0 46.922 9.5 91.624 26.69 132.285 7.71 18.227-.51 39.945-17.44 50.19C1160.96 289.938 1124 350.653 1124 420c0 10.258.81 20.327 2.37 30.146 3.08 19.43-15.7 40.854-35.37 40.854-93.888 0-170 76.112-170 170s76.112 170 170 170c41.09 0 78.78-14.579 108.17-38.847 21.89-18.078 66.76-16.212 85.59 5.041 27.99 31.604 65.37 54.706 107.77 64.945 15.25 3.684 27.18 16.239 29.92 31.69C1441.03 998.509 1532.48 1078 1642.5 1078c123.44 0 223.5-100.064 223.5-223.5 0-112.243-82.74-205.161-190.56-221.089-14.4-2.128-26.33-13.291-29.92-27.399-5.3-20.791 8.41-43.797 29.1-49.493C1763.05 532.168 1828 451.169 1828 355c0-19.081-2.56-37.564-7.35-55.126z" fill="' + color + '"/></g><defs><clipPath id="prefix__clip0_58_127"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath></defs></svg>'
            },
			'mask-6': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1697.02 0c48.23 74.466 73.5 162.212 71.63 252.091-2.38 113.804-48.07 222.366-127.7 303.434-79.62 81.065-187.12 128.499-300.47 132.611-113.34 4.112-223.98-35.407-309.23-110.486-41.529-36.574-104.796-32.505-141.313 9.088-36.517 41.594-32.454 104.961 9.074 141.535C1022.67 837.18 1183.21 894.546 1347.73 888.577c164.52-5.969 320.5-74.817 435.99-192.395A646.282 646.282 0 001920 489.919V954H0V0h1697.02zm-152.13 37.429c50.71-22.192 109.79.947 131.97 51.683a383.79 383.79 0 0110.87 279.732c-31.73 91.288-96.71 167.233-181.94 212.762a384.921 384.921 0 01-278 33.06c-93.53-24.262-174.53-82.823-226.82-164.088-29.965-46.559-16.532-108.606 30-138.586 46.53-29.979 108.54-16.538 138.51 30.021 24.99 38.834 63.75 66.898 108.61 78.536a184.603 184.603 0 00133.31-15.847c40.83-21.815 71.88-58.154 87.03-101.727a183.148 183.148 0 00-5.19-133.5c-22.18-50.735.95-109.855 51.65-132.046z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_58_787)" fill-rule="evenodd" clip-rule="evenodd" fill="' + color + '"><path d="M1660.78-198.081c41-37.158 104.32-33.985 141.42 7.087 110.48 122.305 170.1 282.332 166.66 447.27-3.44 164.938-69.67 322.331-185.14 439.906-115.49 117.578-271.47 186.426-435.99 192.395-164.52 5.968-325.06-51.397-448.719-160.304-41.528-36.574-45.591-99.941-9.074-141.535 36.517-41.593 99.784-45.662 141.313-9.088 85.25 75.079 195.89 114.598 309.23 110.486 113.35-4.112 220.85-51.546 300.47-132.611 79.63-81.068 125.32-189.63 127.7-303.434 2.37-113.804-38.77-224.188-114.95-308.523-37.1-41.072-33.93-104.49 7.08-141.649z"/><path d="M1544.89 37.429c50.71-22.192 109.79.947 131.97 51.683a383.79 383.79 0 0110.87 279.732c-31.73 91.288-96.71 167.233-181.94 212.762a384.921 384.921 0 01-278 33.06c-93.53-24.262-174.53-82.823-226.82-164.088-29.965-46.559-16.532-108.606 30-138.586 46.53-29.979 108.54-16.538 138.51 30.021 24.99 38.834 63.75 66.898 108.61 78.536a184.603 184.603 0 00133.31-15.847c40.83-21.815 71.88-58.154 87.03-101.727a183.148 183.148 0 00-5.19-133.5c-22.18-50.735.95-109.854 51.65-132.046z"/></g><defs><clipPath id="prefix__clip0_58_787"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath></defs></svg>'
            },
			'mask-7': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1920 0H0v954h1920V0zm-219 30c17.67 0 32 14.327 32 32v87c0 17.673 14.33 32 32 32h92c17.67 0 32 14.327 32 32v556c0 17.673-14.33 32-32 32h-294c-17.67 0-32 14.327-32 32v59c0 17.673-14.33 32-32 32H63c-17.673 0-32-14.327-32-32V316c0-17.673 14.327-32 32-32h108c17.673 0 32-14.327 32-32V62c0-17.673 14.327-32 32-32h1466z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1733 62c0-17.673-14.33-32-32-32H235c-17.673 0-32 14.327-32 32v190c0 17.673-14.327 32-32 32H63c-17.673 0-32 14.327-32 32v576c0 17.673 14.327 32 32 32h1436c17.67 0 32-14.327 32-32v-59c0-17.673 14.33-32 32-32h294c17.67 0 32-14.327 32-32V213c0-17.673-14.33-32-32-32h-92c-17.67 0-32-14.327-32-32V62z" fill="' + color + '"/></svg>'
            },
			'mask-8': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_59_1015)"><path fill-rule="evenodd" clip-rule="evenodd" d="M1920 134.857v46.809c-1.93.847-3.91 1.713-5.93 2.596-14.17 6.187-30.38 13.268-45.88 21.199-23.5 11.872-45.37 25.918-56.11 42.09 6.05 6.421 14.3 7.113 22.15 3.702-3.53 3.787-8.09 7.357-12.52 10.669l-.52.391c-4.24 3.172-8.31 6.22-11.18 9.215 1.42.233 2.86.402 4.31.561a3280.3 3280.3 0 00-12.42 10.453c-18.12 15.303-36.32 30.668-55.23 43.686-17.08 11.758-35.46 23.507-54.19 34.584-3.56 1.318-6.75 1.957-8.94.943.74-.648 1.44-1.291 2.12-1.938-8.25 6.043-17.08 12.283-24.4 17.456-2.47 1.745-4.77 3.369-6.81 4.822-.65.341-1.3.679-1.95 1.014l-.01.005-.61.312-.9.472c2.75-3.323 6.05-6.41 9.55-9.325-1.59.542-3.37 1.322-5.22 2.194.18-.56.23-1.117.18-1.652.79-.778 1.6-1.524 2.42-2.267v-.002c1.46-1.339 2.92-2.669 4.2-4.157a27.932 27.932 0 00-5.56-2.382c4.56-2.816 9.4-6.039 10.45-9.714-3.09.469-7.11 2.378-11.2 4.322-3.36 1.591-6.76 3.206-9.74 4.072l-.58-.004-.41-.002c-1.66-.009-3.32-.018-5.01-.125l.01-.026c1.99-1.225 3.96-2.412 5.84-3.554l.04-.021.01-.007.01-.003c3.86-2.341 7.42-4.493 10.33-6.389 16.4-10.684 33.13-22.088 46.85-35.095-11.94 9.008-33.25 19.463-47 22.506.99-.926 2.05-1.816 3.11-2.701 2.37-1.992 4.72-3.966 6.19-6.308-3.52.862-7.19 2.883-10.8 4.878-4.75 2.621-9.41 5.196-13.57 5.029.86-.586 1.73-1.158 2.61-1.73v-.002c3.29-2.153 6.57-4.299 9-7.12-4.31.703-10.49 4.968-16.96 9.433-3.09 2.136-6.25 4.318-9.3 6.178 11.44-7.927 22.6-16.339 32.22-25.584-8.34 6.393-21.45 13.53-33.04 18.087 3.26-2.272 6.52-4.532 9.78-6.791.18-.075.37-.149.56-.223l.44-.174v-.002l.01-.001v-.001c1.43-.565 2.78-1.098 3.95-1.692.23-.113.45-.226.67-.339l.23-.111.43-.218c.39-.195.78-.391 1.18-.589 9.64-4.821 20.45-10.224 27.12-20.921 6.6-4.775 13.13-9.642 19.57-14.658 4.2-3.271 8.36-6.596 12.53-9.923v-.007l.01-.003v-.002c10.38-8.289 20.78-16.597 31.76-24.147 3.14-2.156 6.39-4.253 9.66-6.358 7.92-5.104 15.91-10.259 22.51-16.432-13.6 8.65-32.95 19.047-47.53 21.019 1.47-1.384 3.47-2.917 5.56-4.518 2.31-1.764 4.72-3.609 6.64-5.426-3.74 2.09-7.37 2.772-9.63 1.224 9.86-11.552 33.1-24.438 50.06-33.839v-.002a792.25 792.25 0 009.37-5.242c11.56-6.699 23.02-13.531 34.47-20.359v-.003c17.76-10.593 35.51-21.179 53.65-31.257 8.61-4.768 17.21-9.654 25.8-14.531v-.004c6.12-3.476 12.24-6.948 18.34-10.37 2.48-.235 4.88-.843 7.25-1.67zM0 83.772v88.276c4.402-4.739 9.119-9.476 13.942-14.255 11.9-11.658 24.804-22.835 36.457-32.409 11.623-9.633 21.91-17.867 28.775-23.239 6.387-5.054 12.918-9.934 19.494-14.765-.376-.153-.755-.31-1.13-.467-.949-.395-1.866-.776-2.612-1.03-4.095-6.844-3.365-9.864-1.288-11.558 1.002-.801 2.325-1.307 3.547-1.774l.145-.055c1.223-.49 2.349-.971 2.941-1.698-2.905-1.436-5.514-2.495-8.117-3.297-2.59-.799-4.995-1.281-7.256-1.52-4.522-.48-8.472.034-12.067 1.113a57.117 57.117 0 00-5.317 1.935c-1.738.732-3.413 1.557-5.048 2.389-.934.475-1.851.957-2.76 1.434-2.288 1.202-4.517 2.373-6.798 3.335-5.271 2.132-10.33 4.721-15.23 7.623-.612.37-1.24.745-1.877 1.125v.001h-.002l-.001.001-.001.001h-.001v.001c-1.856 1.11-3.786 2.264-5.619 3.451.089-1.143.019-2.45-.244-3.954-3.465-2.052-7.065-2.416-10.727-2.441-3.23 1.361-6.458 2.278-9.494 2.004.174.005 1.151-.84 2.402-2-2.331-.077-4.676-.373-7.022-1.18-1.248.699-2.483 1.424-3.7 2.138H1.39L0 83.772zm0 138.341c9.679-3.745 20-9.849 30.22-16.715 5.008-3.314 9.886-6.965 14.687-10.556v-.001l.864-.646c2.627-1.989 5.23-3.962 7.784-5.898l.016-.012.036-.027.042-.032.01-.008.02-.015.032-.024.009-.007.058-.043c2.286-1.733 4.534-3.437 6.728-5.098 9.434-7.111 17.978-13.265 24.852-16.707 1.776-.907 3.947-2.038 6.444-3.338h.001c11.81-6.15 30.897-16.089 49.791-24.024 14.071-6.002 27.989-10.955 38.637-12.342-1.838 2.965-3.324 6.358-4.236 10.369 2.634-2.088 6.321-4.809 10.56-7.937l.001-.001.011-.008c1.089-.804 2.215-1.634 3.368-2.488 2.209.379 4.103 1.077 5.585 2.185a95.156 95.156 0 00-2.352 1.94c5.865-4.045 12.021-7.686 18.356-10.108a53.78 53.78 0 014.24-1.432c4.51 1.866 9.17 1.522 13.771 1.059-5.12 9.334-13.626 17.824-21.999 26.181h-.001v.001h-.001l-.001.002c-3.82 3.812-7.612 7.597-11.042 11.421 15.024-2.53 28.314-9.401 41.445-16.191l1.968-1.016c.992.613 1.84 1.133 2.401 1.384-1.337.986-2.934 2.407-4.576 3.867-2.993 2.662-6.134 5.455-8.112 5.988 1.849-.364 4.624.272 7.181.859 1.214.278 2.379.545 3.373.689 3.037 7.391.45 11.203-3.576 13.672-1.941 1.195-4.227 2.074-6.37 2.899l-.217.083c-2.178.854-4.15 1.678-5.457 2.705 7.429 2.267 14.626 3.009 21.441 2.602a60.756 60.756 0 006.373-.716c-1.108.819-2.205 1.625-3.283 2.418-3.629 2.668-7.056 5.188-10.046 7.549-2.883 2.3-5.83 4.632-8.821 6.999-27.497 21.763-58.639 46.411-77.006 75.06l2.778-1.19.005-.002.004-.002.008-.003c1.543-.662 3.088-1.325 4.637-1.981l.011-.005c2.426-1.116 4.851-2.232 7.29-3.345 4.851-2.264 9.674-4.6 14.471-7.008 9.588-4.79 19.088-9.839 28.474-15.071 9.436-5.209 18.574-10.702 27.691-16.307a920.761 920.761 0 006.902-4.303l.008-.005 1.799-1.129c5.132-2.252 9.851-4.095 13.074-3.88-2.546 2.125-5.721 3.443-8.912 4.767-.84.348-1.68.697-2.511 1.061 3.603 1.022 8.059-.415 12.59-1.876 3.43-1.106 6.904-2.226 10.083-2.304-1.645 1.896-4.004 3.262-6.378 4.636h-.001v.001h-.001v.001h-.001c-1.031.597-2.065 1.195-3.045 1.84 12.415.001 32.529-5.363 44.257-11.311-2.797 1.873-5.713 3.656-8.684 5.375l-.104-.02c-7.905-.265-15.289 4.634-20.565 10.539-5.516 2.505-11.103 4.89-16.658 7.172-2.714 1.118-5.984 2.33-9.539 3.648l-.002.001h-.001c-14.382 5.332-33.415 12.387-39.036 21.87 7.188 1.691 16.869-1.875 26.335-5.363 6.717-2.474 13.325-4.909 18.859-5.397-1.102 2.859-5.182 5.053-9.032 7.125-1.371.737-2.713 1.459-3.881 2.19 5.411-2.012 12.223-4.239 17.038-4.838-4.049 2.303-7.891 4.873-11.081 7.927 2.739-1.312 6.493-3.041 10.811-5.031l.012-.005v-.001h.001l.006-.003c7.259-3.345 16.107-7.422 24.394-11.474a40.26 40.26 0 01-2.17 1.394c2.999 2.414 9.224.888 15.287-.597 4.027-.987 7.984-1.957 10.877-1.743-1.659 3.619-7.377 5.74-12.294 7.563h-.001l-.003.001c-1.145.425-2.248.834-3.245 1.242 1.966-.28 4.179.144 5.801.456.696.133 1.283.246 1.695.273-1.365.649-3.071 1.53-4.826 2.437l-.001.001c-3.216 1.662-6.595 3.408-8.345 3.959 1.592-.461 3.624-.616 5.494-.759.872-.067 1.71-.131 2.451-.222.211 4.192-7.246 6.862-13.319 9.036-2.028.727-3.902 1.398-5.284 2.052 17.312-.452 36.867-8.177 54.634-15.196 4.002-1.581 7.913-3.126 11.688-4.545 16.401-6.289 33.029-13.372 49.543-20.718 12.119-5.4 24.146-11.092 35.985-16.696 4.297-2.033 8.569-4.055 12.812-6.047 5.233-2.436 10.422-4.853 15.598-7.272l4.164-1.999.018-.008.123-.06c3.717-1.783 7.426-3.563 11.129-5.344 7.296-3.539 14.53-7.185 21.707-11.03-5.883 15.902-23.9 28.27-31.101 33.214l-.02.013c-6.32 4.288-13.172 7.896-20.023 11.503-.878.463-1.756.925-2.633 1.389-.73.38-1.462.77-2.186 1.155l-.442.236c3.425-.517 7.002-.467 11.011 1.118a18.416 18.416 0 01-.466 3.123c-1.637 3.356-4.018 6.027-6.366 8.662l-.001.001v.001l-.001.001h-.001l-.001.001c-.689.773-1.375 1.543-2.038 2.326 4.216.383 8.345.804 12.176 1.521-.34 2.148-1.078 3.819-1.957 5.302 10.452 2.836 21.444 3.614 31.643 2.764l.536.314c2.767 9.89.335 15.644-3.705 19.512-.97.954-2.041 1.795-3.15 2.549.609 12.688-8.208 20.033-18.847 25.331-4.868 2.413-10.111 4.402-15.008 6.261l-.899.341-.218.083c-.453.172-.901.342-1.346.513-5.55 2.108-10.535 4.293-13.627 6.744-8.116 6.463-15.388 14.428-22.435 22.704a825.577 825.577 0 00-5.414 6.433c.027 2.054.201 4.189.518 6.431 15.077 10.829 27.166 8.277 38.66 3.823 1.967-.76 3.918-1.575 5.861-2.387 3.734-1.561 7.442-3.112 11.196-4.24a44.187 44.187 0 014.308-1.069 36.646 36.646 0 014.42-.509 27.97 27.97 0 019.135 1.057c1.644 6.975.041 11.519-2.506 15.19-1.174 1.695-2.532 3.226-3.865 4.728-1.563 1.761-3.091 3.483-4.248 5.388 3.756-1.589 8.059-3.144 12.602-3.954 4.546-.823 9.229-1.001 13.565-.017-2.173 2.729-4.146 5.763-5.742 9.244a41.76 41.76 0 00-2.093 5.601 46.714 46.714 0 00-1.336 6.467c1.098-.917 2.336-1.93 3.72-3.01.357-.266.722-.539 1.093-.818l.484-.364.397-.298.073-.055.073-.055 1.201-.902 1.229-.919c2.854-2.108 6.018-4.439 9.368-6.906l1.436-1.057c7.781-5.743 16.46-12.263 24.52-18.938a28.847 28.847 0 01-2.103 3.523c4.063 5.113 8.161 8.049 12.249 9.716 4.112 1.686 8.236 2.133 12.32 2.209 1.637.031 3.267.004 4.89-.023 6.49-.106 12.85-.211 18.92 3.384 2.303 12.794-5.72 15.778-12.616 18.342-1.594.593-3.129 1.164-4.461 1.828 3.491.774 8.581 4.584 12.283 7.356l.001.001.002.001.002.002c1.557 1.166 2.868 2.147 3.71 2.641-1.712 1.287-3.737 3.186-5.813 5.132a238.43 238.43 0 01-2.482 2.308c-2.899 2.649-5.611 5.009-7.588 5.427 2.423-.312 6.165.966 9.621 2.148l.002.001c1.584.541 3.108 1.062 4.419 1.4 4.66 11.181 1.654 16.466-3.36 19.616-2.464 1.543-5.417 2.572-8.174 3.532-2.849.993-5.487 1.912-7.158 3.25a82.599 82.599 0 0012.615 4.271 4792.95 4792.95 0 0121.543-10.723c12.079-5.951 24.246-11.791 36.609-17.231 3.202-1.387 6.437-2.769 9.685-4.158 5.53-2.364 11.099-4.744 16.609-7.188 8.76-3.878 17.348-7.922 25.257-12.522-10.661 4.54-23.387 9.535-35.896 13.641a207.816 207.816 0 01-18.442 5.214c-5.93 1.391-11.532 2.384-16.508 2.832 2.521-1.43 5.805-3.009 9.234-4.658l.002-.001c3.776-1.816 7.73-3.717 11.035-5.597-5.797 2.13-10.837 2.782-13.116 1.096 2.69-1.798 5.789-3.628 9.201-5.468 3.422-1.825 7.134-3.691 11.013-5.58 7.794-3.758 16.447-7.494 25.235-11.083 8.788-3.589 17.695-7.022 25.985-10.177a4531.19 4531.19 0 015.468-2.074l.002-.001.012-.004.009-.003c6.178-2.341 11.877-4.499 16.734-6.48 22.639-9.134 45.148-18.588 67.624-28.155 22.409-9.715 44.845-19.425 67.512-28.715 6.353-2.662 12.716-5.376 19.078-8.089a2973.09 2973.09 0 0011.688-4.998l.189-.073c2.017-.862 4.034-1.727 6.051-2.592 2.021-.867 4.043-1.734 6.064-2.597l7.811-3.337c-2.976 2.607-10.297 7.317-19.369 13.153-24.773 15.935-62.6 40.268-60.651 53.136 5.32 12.902 31.325 6.382 41.785 3.76 1.552-.389 2.762-.692 3.511-.834 2.108-.402 10.221-3.442 19.889-7.065 16.926-6.342 38.618-14.471 41.193-13.354l.282.12.352.148.717.298h.001c4.978 2.065 15.245 6.323 23.363 15.335l-1.906 1.013-.012.007c-1.432.761-2.861 1.521-4.294 2.274-7.331 3.951-14.622 7.977-21.905 12.032-6.046 3.375-12.083 6.753-18.11 10.125l-3.703 2.071c-7.256 4.059-14.495 8.095-21.794 11.944-12.159 6.462-24.37 12.617-36.623 18.63-.9 13.093-9.957 15.318-17.189 17.094h-.001c-1.583.389-3.079.757-4.383 1.212-1.116.391-2.253.779-3.404 1.172l-.025.009-.005.001-.004.002-.012.004c-6.256 2.134-12.945 4.417-19.038 8.149-1.498.209-3.042.411-4.621.618-5.408.707-11.222 1.468-16.947 2.754a10769.09 10769.09 0 01-11.138 5.245l-.02.009-.19.089a39.156 39.156 0 014.945-.13c5.23.219 11.019 1.511 17.446 4.242-.774.842-2.149 1.425-3.639 2.054l-.095.038-.234.095c-1.421.571-2.929 1.177-4.112 2.098-2.556 1.957-3.681 5.296.45 12.457.791.213 1.739.545 2.73.893h.001c2.177.764 4.562 1.601 5.96 1.404-1.434.335-3.248 2.686-4.983 4.934-.959 1.242-1.894 2.453-2.726 3.274.547.314 1.42.932 2.457 1.667 2.499 1.77 5.952 4.217 8.101 4.707-.687.419-1.492.785-2.328 1.164-2.693 1.222-5.703 2.588-6.08 6.377l.222-.026c9.907-1.137 19.644-2.255 28.798 3.346-.03 3.392-.63 6.072-1.575 8.284a30.504 30.504 0 013.774 3.456c-.335.721-.62 1.464-.86 2.255a204.549 204.549 0 016.278-6.288 372.268 372.268 0 016.061-5.99c.836-.813 1.653-1.601 2.44-2.362 2.872-2.776 5.364-5.184 7.09-7.036.368 5.851-.882 10.164-2.774 13.712 5.399 1.139 11.078-.716 15.2-2.83-.45 1.217-1.157 2.31-1.879 3.429-1.984 3.068-4.091 6.328-1.369 12.864 6.864 1.833 12.252-.993 17.657-3.828l.003-.001c.827-.435 1.655-.869 2.489-1.286 6.349-3.081 13.302-4.972 23.88 1.581 4.423 12.768.129 20.809-6.449 26.529-.37.335-.764.652-1.159.97h-.001v.001l-.001.001-.001.001.304.066.866.186.221.047.492.104.004.001.003.001h.004l.003.001c6.473 1.373 12.84 2.724 18.142 7.627-.455 13.374-9.253 14.532-16.8 15.526-1.76.231-3.451.454-4.952.82 3.312 1.56 7.549 6.477 10.663 10.089 1.341 1.556 2.474 2.871 3.234 3.57-2.021.892-4.5 2.324-7.049 3.795-4.633 2.674-9.494 5.48-12.25 5.426 2.329.239 5.46 2.054 8.467 3.887.587-.189 1.161-.38 1.746-.555 3.618-1.129 7.393-1.716 13.006 2.145-.617.501-1.269.959-1.921 1.417-2.478 1.741-4.949 3.476-5.445 7.569 5.604 2.609 10.035-1.183 14.662-5.142l.002-.002c1.217-1.041 2.448-2.094 3.718-3.046 5.192-3.831 10.964-6.195 19.432.549 2.404-.526 4.781-1.028 7.103-1.519 9.873-2.085 18.765-3.963 24.654-6.614 17.905-8.04 34.815-19.036 51.496-29.883l.001-.001c8.448-5.493 16.838-10.949 25.268-15.962 16.63-9.887 33.72-17.531 50.87-24.776 11.14 1.291 24.39-.197 37.76-3.243 4.05-.901 8.09-1.95 12.14-3.041 6.69-.457 13.52-.752 20.57-.616 2.53.019 5.49.246 8.7.492 1.64.126 3.34.257 5.08.367 5.2.28 10.82.316 16.11-.412 5.38-.832 10.45-2.462 14.55-5.524 3.39-2.558 6.09-6.08 7.69-10.827 6.41-.372 14.67-.838 23.91-1.218 13.06-.619 28.09-1.095 42.68-.842 2.65.041 5.27.104 7.87.177 3.76 2.04 7.78 4.772 14.25 15.126 5.32 19.539.23 21.11-3.74 22.332-.78.24-1.51.467-2.12.815-.48.282-.97.561-1.46.842-5.72 3.245-12.15 6.894-13.07 22.343 5.32 3.835 10.27 6.308 14.97 7.885 4.69 1.575 9.11 2.09 13.37 2.252 2.17.065 4.18-.99 6.26-2.081 4.57-2.402 9.49-4.98 17.17 3.836-.65.164-1.85 3.716-2.96 7.02-1.07 3.154-2.05 6.082-2.41 5.621.06.088.13.173.2.256l.02.029.02.027.07.084.03.04c.02.033.05.066.07.099-6.2 3.322-12.71 6.844-19.63 11.182-4.25 2.657-8.05 5.907-10.8 10.026-25.21 7.449-49.75 16.556-69.97 29.18 25.45.428 51.13-.815 76.89-3.216 6.45 1.63 12.31.279 17.98-1.685.17-.055.34-.119.52-.184v-.001c.07-.025.13-.049.2-.073 8.62-.992 17.25-2.09 25.88-3.295 1.21 7.208-2.13 10.774-5.28 14.146-1.18 1.26-2.34 2.493-3.22 3.878 5.72-2.225 13.28-4.112 19.77-2.449-3.33 4.094-6.03 9.111-7.16 16.087 2.64-2.081 6.4-4.787 10.73-7.899v-.003c1.22-.874 2.48-1.78 3.77-2.712 2.95-2.127 6.05-4.402 9.2-6.748 3.2-2.284 6.44-4.642 9.55-7.049-.5.92-1.06 1.774-1.67 2.592 7.23 9.577 14.76 9.679 22.08 9.778 4.87.066 9.66.13 14.19 3.001 1.59 9.787-4.47 11.878-9.68 13.675-1.2.416-2.36.815-3.37 1.29 2.62.661 6.4 3.672 9.16 5.864 1.16.922 2.13 1.7 2.76 2.095-1.29.931-2.82 2.317-4.39 3.741-2.85 2.583-5.84 5.29-7.79 5.628 1.85-.168 4.68.91 7.28 1.899 1.2.457 2.35.895 3.33 1.181 3.41 8.619 1.02 12.534-2.86 14.774-1.87 1.069-4.07 1.752-6.16 2.397l-.25.078c-2.11.691-4.06 1.344-5.31 2.328 7.56 3.477 14.75 5.215 21.53 5.652 6.77.434 13.14-.418 19.23-1.955 7.66-1.94 14.86-4.993 21.79-7.933 4.07-1.724 8.04-3.41 11.96-4.808 17.21-5.803 33.87-13.851 50.12-22.824 1.27-.711 2.55-1.423 3.82-2.135l.13-.073c1.37-.767 2.74-1.533 4.12-2.299 1.37-.767 2.75-1.533 4.12-2.298 4.09-2.282 8.14-4.598 12.17-6.932 5.6-3.244 11.16-6.527 16.67-9.783l.06-.036.02-.011c2.4-1.414 4.78-2.822 7.16-4.22 5.15-3.03 10.34-5.905 15.54-8.751 5.27-2.793 10.52-5.577 15.7-8.493 10.36-5.848 20.45-12.272 29.85-20.549 5.52-4.928 11-10.282 16.51-15.665v-.006l.01-.006c1.97-1.926 3.94-3.856 5.92-5.771 1.31-.352 2.63-.7 3.94-1.048 1.32-.349 2.63-.698 3.94-1.05 8.91-2.395 17.81-4.79 26.74-7.196 3.65-.959 7.27-1.978 10.89-2.997l.01-.001c-1.62 1.224-3.29 2.418-4.94 3.599l-.01.002c-2.99 2.142-5.92 4.238-8.4 6.389 12.46-5.449 28.27-11.469 40.17-12.465-8.76 6.774-16.83 14.445-22.97 23.885 6.79-4.165 16.4-9.818 27.43-16.304 2.32-1.365 4.7-2.766 7.13-4.199 7-4.12 14.4-8.504 21.88-13.006 3.89-2.428 7.79-4.879 11.67-7.348-2.08 2.04-4.16 4.108-6.24 6.183v.004l-.02.02-.02.021-.02.02c-2.58 2.568-5.16 5.145-7.78 7.693-7.23 7.151-14.82 13.764-23.05 18.829-3.16 1.927-7.97 3.283-13.47 4.524-.86.195-1.74.391-2.63.588l-.01.001c-4.77 1.058-9.86 2.187-14.64 3.771-2.85.948-5.59 2.062-8.07 3.412-2.51 1.332-4.81 2.905-6.72 4.818-3.75 3.733-6.1 8.748-6.11 15.66 1.99.769 4.1 1.534 5.43 1.388-.63.127-1.36.599-2.16 1.275 2 1.031 4 1.595 5.98 2.154 3.77 1.066 7.49 2.118 11.15 6.37-.94.772-1.81 2.162-2.71 3.575-1.03 1.632-2.08 3.296-3.26 4.076 1.21-.04 2.48-.127 3.77-.217 2.74-.188 5.61-.386 8.46-.166-.76 2.554-2.25 4.189-3.79 5.889-1.68 1.841-3.42 3.756-4.38 6.995 4.2 1.543 8.86 2.753 13.59 3.98v.001l.67.173.36-.297c2.32-1.987 4.62-3.911 6.79-5.708.32-.259.63-.516.94-.77.55-.459 1.1-.909 1.64-1.35v-.006l.01-.003c3.27-2.695 6.12-5.033 8.09-6.829-.31 5.819-2.06 10.028-4.37 13.471 5.31 1.326 11.22-.297 15.6-2.243-.59 1.196-1.43 2.256-2.29 3.341-2.33 2.961-4.81 6.099-2.82 12.651 6.62 2.06 12.27-.465 17.94-3.004.95-.421 1.89-.842 2.83-1.242 6.73-2.782 13.91-4.349 23.68 2.609 2.93 12.831-2.24 20.562-9.42 25.889-3.57 2.701-7.74 4.645-11.65 6.394-.78.347-1.56.685-2.32 1.017-3.03 1.321-5.81 2.534-7.99 3.816l-.28.171v.001c-6.19 3.751-12.48 7.563-18.03 12.492-2.83 2.486-5.49 5.233-7.91 8.334-.19.246-.38.501-.57.753v.004c-.07.083-.13.166-.19.248 2.36.891 4.52 2.017 6.35 3.444 2.23-.521 4.58-.883 7.07-1.034 7.94-.51 16.9.961 24.92 4.576-.55 1.129-1.23 2.085-1.91 3.038-1.48 2.078-2.95 4.142-2.94 7.96 4.19 1.748 7.55.835 10.86-.061l.29-.081c.13-.034.26-.068.38-.102l.48-.125c3.69-.953 7.5-1.346 12.66 2.702-.67.47-1.37.894-2.07 1.316-2.62 1.589-5.22 3.169-6.09 7.16 5.29 2.799 10.04-.711 15.01-4.38 1.3-.959 2.61-1.929 3.95-2.799 6.5-4.144 13.6-6.251 23.41 6.253-2.48 2.159-4.99 4.287-7.54 6.311a433.038 433.038 0 01-7.78 5.919c-5.24 3.876-10.48 7.74-15.72 11.617-10.52 7.597-20.78 15.768-30.23 25.344-1.08 1.122-2.15 2.273-3.23 3.423 5.1-.816 10.13-1.901 15.08-3.108 3.67-.874 7.45-2.673 11.37-4.532 8.3-3.94 17.17-8.155 26.7-4.437-1.12.43-4.3 3.892-7.23 7.097-2.79 3.037-5.36 5.845-5.78 5.624 8.85 4.701 17.66 1.343 26.05-1.857 3.38-1.289 6.69-2.552 9.91-3.253 12.62-2.605 25.11-6.059 37.66-9.528 7.42-2.05 14.85-4.105 22.35-5.994 7.92-2.049 15.7-4.421 23.41-6.969 6.21-2.067 12.33-4.342 18.45-6.627V954H0V222.113zm281.33-28.74c5.549-2.688 11.055-5.438 16.52-8.33l-.013-.002c4.779-2.515 9.515-5.122 14.252-7.729 9.549-5.258 19.104-10.518 29.033-15.029a148.334 148.334 0 013.683-1.628c.502-.579 1.002-1.159 1.501-1.736l.003-.003.002-.003.071-.082.003-.004.003-.003c1.105-1.279 2.2-2.547 3.275-3.774-7.347 1.832-14.65 2.917-20.835 2.536-11.689 7.501-23.195 16.627-34.312 25.443v.001h-.001c-4.462 3.539-8.862 7.029-13.185 10.343zm434.151 65.555c-7.495 5.943-15.849 10.623-21.609 13.798 20.533-9.925 40.86-20.413 60.691-31.652a63.104 63.104 0 01-2.147-.273c1.395-.749 2.715-2.634 4.01-4.482l.001-.001c1.12-1.599 2.222-3.171 3.335-3.954-1.397-2.306-2.836-4.012-4.322-5.39a363.236 363.236 0 013.653-2.052c2.61-1.516 5.029-2.945 6.805-4.17a194.408 194.408 0 00-12.581 4.497c-1.794-1.289-3.625-2.224-5.467-3.163-3.354-1.712-6.747-3.444-10.027-7.379.048-.853.142-1.658.236-2.463a477.686 477.686 0 01-11.492 3.928 309.397 309.397 0 01-6.764 2.114l-.237.34c-6.195 8.897-13.589 19.515-20.767 17.505 8.454 2.367 16.346 2.059 24.198 1.752 3.068-.12 6.131-.239 9.218-.199-2.428 8.154-9.177 15.326-16.734 21.244zm212.359 14.824c3.975-.736 7.822-1.873 11.376-4.041-.239-.314-.495-.603-.75-.906l-3.462 1.616h-.001c-2.421 1.131-4.817 2.251-7.163 3.331zm-576.201 18.974c9.979 1.078 23.014-.235 32.712-5.261l7.825 1.436c6.56-5.845 10.78-12.308 11.338-21.075-22.829-9.948-50.038-1.842-51.875 24.9zm47.068 89.452c2.972-12.368 19.015-14.8 29.988-15.022l.016-.011c3.262-.075 5.948 3.801 3.389 6.432-2.444 2.516-5.786 5.151-9.375 6.272-.182.842-.729 1.618-1.799 2.163-5.727 2.925-11.397 4.876-17.942 4.766-2.432-.042-4.914-1.94-4.277-4.6zm34.254-105.939c.516.715 1.336 1.243 2.467 1.397 3.221.424 6.058-.418 9.079-1.313l.419-.124c4.431-1.303 2.6-7.408-1.399-7.509-.147-1.618-1.185-3.089-3.145-3.354-2.077-.287-4.103-.12-5.9 1.005-.466.287-.889.662-1.313 1.038-.543.481-1.087.963-1.725 1.262-1.946.91-3.174 2.828-2.371 4.984.552 1.476 2.235 2.661 3.888 2.614zm296.102 128.59c6.799-6.853 16.264-12.811 26.411-10.369l-.013-.002c2.749.667 4.525 4.133 2.319 6.384-4.791 4.876-10.96 8.151-17.593 9.844-2.729 1.278-5.902 1.747-9.499 1.221-2.744-.396-4.161-3.837-2.633-5.996a3.293 3.293 0 011.008-1.082zm-.647 48.892c-10.501-2.336-20.123 1.814-29.511 5.864l-.009.004c-.856.369-1.71.737-2.562 1.1-4.709 2.006-2.207 8.639 2.737 7.336 1.94-.506 3.892-.93 5.86-1.296-.119 2.189 1.574 4.643 4.28 4.439 2.154-.159 4.289-.48 6.425-.801h.004c2.74-.412 5.481-.823 8.266-.895 3.509-.097 4.265-3.773 2.752-6.032.272-.449.508-.931.672-1.454.584-.243 1.17-.499 1.756-.756 3.554-1.585 3.357-6.608-.659-7.494l-.011-.015zm512.844 81.312c-.34.128-.66.257-.99.385l-.02.024c-.33 1.045-1.05 2.003-2.24 2.539-1.35.609-2.69 1.234-4.04 1.86-10.76 4.998-21.78 10.114-34.04 8.401-2.87-.404-4.43-4.317-2.32-6.383a90.882 90.882 0 0129.56-19.4c.54-.966 1.48-1.737 2.85-1.985l1.09-.192.02-.003.04-.006c1.62-.286 3.23-.568 4.85-.895 2-.588 4.04-1.117 6.11-1.56 2.64-.58 4.66 1.154 5.19 3.124 3.17-.091 5.48 4.106 3.08 6.442a39.778 39.778 0 01-6.81 5.342c-.32.952-1.06 1.814-2.33 2.307zm-89.85 4.421c-10.63-3.663-21.78.167-29.38 7.856-2.58 2.626.15 6.512 3.39 6.432 1.61-.041 3.2-.222 4.76-.501.31.273.68.517 1.15.697 8.47 3.348 16.54-1.749 21.39-8.446 1.33-1.833 1.33-5.149-1.3-6.049l-.01.011zm507.78 95.884c1.73-.071 3.23 1.052 3.94 2.476l.02.018c1.47 1.698 1.82 4.405-.6 5.631a101.93 101.93 0 01-10.21 4.501l-.1.059c-8.74 5.177-17.46 10.339-27.15 13.81-1.14.411-2.15.291-2.98-.117-4.85 2.061-9.88 3.565-14.92 4.269-4.74.654-5.58-5.823-1.73-7.556l.06-.029c.1-1.572.99-3.214 2.45-3.903 3.63-1.706 7.4-3.846 11.26-6.047a4.161 4.161 0 01.01-1.953c-.76-1.569-.65-3.583 1.07-4.952 4.08-3.242 8.36-6.095 13.11-7.555a27.422 27.422 0 017.51-1.991c1.54-.188 2.84.657 3.62 1.851.75.623 1.21 1.557 1.34 2.538 3.33-.737 6.7-1.047 10.11-.786 1.06-.116 2.11-.218 3.19-.264zm-66.45 58.943c-3.2.896-6.41 1.815-9.51 2.985-1.12.428-2.19.932-3.23 1.51-2.08 1.14-3.19 3.472-3.05 5.761.15 2.629 2.54 5.171 5.24 5.425 1.08.102 2.03.129 3.07-.017 2.35-.324 4.68-.906 6.96-1.538 1.44-.41 2.52-1.439 3.17-2.695l1.45-.42c3.11-.871 4.61-4.626 3.54-7.478-1.17-3.141-4.52-4.416-7.65-3.535l.01.002zm84.13 7.257l.95-.14-.02-.002c5.02-.75 8.72 6.116 5.08 9.653-4.43 4.308-9.48 8.166-15.59 9.888-2.29.646-4.62 1.071-6.95 1.496-2.08.381-4.17.762-6.23 1.302-7.36 1.936-11.5-9.055-4.09-11.011 1.96-.519 3.88-.933 5.75-1.337 2.19-.474 4.32-.933 6.38-1.53.01-.063.02-.122.02-.182 0-.063.01-.127.02-.201 1.1-5.949 9.47-7.174 14.68-7.936z" fill="' + color + '"/></g><defs><clipPath id="prefix__clip0_59_1015"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath></defs></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_58_864)"><g clip-path="url(#prefix__clip1_58_864)" fill="' + color + '"><path d="M-116.708-231.745c-2.746 2.475-4.527 6.596-6.175 10.944-8.668 3.746-17.288 7.595-25.567 12.087-14.736 8.01-59.648 32.356-56.449 57.88 17.017 15.243 45.54-10.805 60.149-13.797 3.57-.72 10.128-2.225 17.676-3.467 6.504 7.233 7.542 1.358 13.486 6.722 1.275 4.804-.136 5.663-.365 8.304 3.658 3.421 7.34 6.052 10.766 7.881a86.367 86.367 0 00-5.205 2.063c-10.345 4.436-18.972 11.478-17.476 24.329 3.782 2.38 7.308 2.488 10.799 3.075-.589 3.208-.121 7.782 2.108 14.433 2.798 3.412 5.139 5.567 7.175 6.884-.093.36-.188.734-.26 1.125 2.145.502 4.417.906 6.724 1.262-2.97 2.96-5.663 6.171-7.934 9.73 7.738-.735 15.325-1.97 22.781-3.593.172 1.339.069 2.344-.144 3.208-.51.284-1.008.57-1.516.84-3.271 1.813-6.639 3.486-10.025 5.115-6.775 3.271-13.626 6.448-19.86 10.587-2.109 1.432-5.33 3.483-8.804 6.323-3.479 2.798-7.248 6.217-10.539 10.115-6.581 7.796-11.21 17.514-7.687 28.135 9.455 9.002 18.71 3.382 26.556-1.904 6.336 4.992 11.984 4.183 17.284 1.53 5.337-2.565 10.362-6.953 15.32-9.21 3.264.208 6.466.755 9.32 1.805-2.11 3.859-5.978 5.17-9.306 7.295 11.358 8.988 17.935-1.869 28.57 2.322a14.68 14.68 0 01-.329 2.23c-1.9 3.885-5.193 6.475-7.523 9.767.867.348 1.693.62 2.513.852 9.223 3.73 19.542 4.87 28.92 4.084l.376.23c1.122 4.022 1.046 7.08.24 9.48-7.646 3.692-14.796 8.053-17.312 13.603-3.328 2.193-6.712 4.618-9.68 7.564-1.878.76-3.847 1.572-5.875 2.412-1.01.435-2.033.867-3.071 1.31a75.83 75.83 0 00-3.072 1.458c-4.08 2.027-8.11 4.374-11.572 7.297-6.921 5.833-11.563 13.93-9.773 26.134 4.656 3.349 8.914 4.9 12.918 5.338-.698 1.53-1.057 3.04-.945 4.49 7.674 4.968 21.944-7.697 28.979-9.964 1.777-.577 5.099-1.706 8.87-2.792.907.126 1.83.309 2.766.575 1.174 4.987.04 8.23-1.792 10.859-1.817 2.631-4.26 4.703-5.789 7.227 2.69-1.138 5.751-2.247 9.013-2.834 3.25-.59 6.602-.715 9.695-.013-1.558 1.952-2.967 4.12-4.098 6.608a29.183 29.183 0 00-1.49 4 32.423 32.423 0 00-.956 4.622c.78-.652 1.669-1.379 2.66-2.153 1-.747 2.095-1.57 3.251-2.436 2.337-1.714 4.944-3.649 7.733-5.698 5.555-4.115 11.761-8.772 17.53-13.535a18.88 18.88 0 01-1.513 2.512c.374.46.737.904 1.105 1.322-2.785 2.17-6.345 3.795-8.798 5.259-7.625 4.598-16.97 9.756-22.359 17.15 9.988-2.344 19.528-6.064 28.696-10.258 3.442-1.565 7.203-3.988 11.08-6.16 2.346.807 4.69 1.103 7.022 1.18-1.25 1.16-2.228 2.005-2.402 2 3.036.274 6.265-.643 9.494-2.004 3.662.025 7.262.39 10.727 2.441.263 1.504.333 2.811.244 3.954 2.464-1.596 5.105-3.133 7.5-4.58 4.901-2.902 9.96-5.49 15.231-7.623 3.186-1.343 6.271-3.095 9.558-4.77 1.635-.831 3.31-1.656 5.048-2.388a57.098 57.098 0 015.317-1.935c3.595-1.079 7.545-1.594 12.067-1.114 2.261.24 4.666.722 7.256 1.521 2.603.802 5.213 1.86 8.117 3.297-.592.727-1.718 1.208-2.94 1.698-1.262.482-2.65.996-3.693 1.829-2.077 1.694-2.807 4.714 1.288 11.559 1.04.353 2.412.955 3.742 1.496-6.576 4.831-13.107 9.71-19.494 14.765-6.864 5.372-17.152 13.606-28.775 23.239-11.653 9.574-24.557 20.751-36.457 32.409-5.933 5.879-11.705 11.694-16.929 17.528-5.223 5.834-9.92 11.671-13.797 17.362-7.752 11.382-12.235 22.218-11.144 31.396 15.42 6.471 37.003-4.474 58.148-18.681 5.31-3.513 10.472-7.404 15.55-11.203 5.058-3.83 10.023-7.595 14.736-11.164 9.434-7.11 17.979-13.265 24.852-16.707 10.175-5.195 33.347-17.749 56.236-27.362 14.071-6.002 27.989-10.955 38.637-12.342-1.838 2.965-3.324 6.358-4.236 10.369 3.313-2.626 8.293-6.254 13.94-10.434 2.209.379 4.103 1.077 5.585 2.185a97.052 97.052 0 00-2.352 1.94c5.865-4.045 12.021-7.686 18.356-10.108a53.78 53.78 0 014.24-1.432c4.51 1.866 9.17 1.522 13.771 1.059-7.457 13.593-22.093 25.395-33.044 37.605 15.775-2.657 29.638-10.099 43.413-17.207.992.613 1.84 1.133 2.401 1.384-3.774 2.785-9.625 9.03-12.688 9.856 2.727-.538 7.468 1.101 10.554 1.547 3.037 7.391.45 11.203-3.576 13.672-2.006 1.235-4.382 2.133-6.587 2.982-2.178.854-4.15 1.678-5.457 2.705 7.429 2.267 14.626 3.009 21.441 2.603a61.024 61.024 0 006.373-.717c-4.835 3.573-9.45 6.905-13.329 9.967-29.393 23.448-65.462 50.294-85.827 82.059 2.472-1.056 4.947-2.125 7.432-3.178 2.429-1.118 4.859-2.235 7.301-3.35a559.46 559.46 0 0014.471-7.008c9.588-4.79 19.088-9.839 28.474-15.071 9.436-5.209 18.574-10.702 27.691-16.307 2.918-1.796 5.815-3.623 8.709-5.437 5.132-2.252 9.851-4.095 13.074-3.88-3.216 2.685-7.435 4.08-11.423 5.828 6.33 1.796 15.293-3.999 22.673-4.18-2.36 2.721-6.19 4.349-9.426 6.478 12.415.002 32.529-5.363 44.257-11.311-2.797 1.873-5.713 3.656-8.684 5.375l-.104-.02c-7.905-.265-15.288 4.634-20.565 10.539-5.515 2.505-11.103 4.89-16.658 7.172-13.694 5.64-41.567 13.692-48.578 25.519 12.289 2.891 31.861-9.583 45.194-10.76-1.494 3.877-8.466 6.533-12.913 9.315 5.411-2.012 12.223-4.239 17.038-4.838-4.049 2.303-7.891 4.873-11.08 7.927 7.345-3.517 21.998-10.047 35.223-16.514a40.26 40.26 0 01-2.17 1.394c4.992 4.017 18.915-2.875 26.164-2.34-2.047 4.464-10.266 6.648-15.543 8.806 2.811-.401 6.125.639 7.496.729-3.869 1.837-10.468 5.546-13.172 6.397 2.335-.677 5.614-.694 7.945-.981.281 5.592-13.082 8.476-18.603 11.088 21.212-.553 45.79-12.026 66.322-19.741 16.401-6.289 33.029-13.372 49.544-20.718 16.516-7.36 32.863-15.263 48.796-22.743 5.233-2.436 10.422-4.853 15.598-7.272 5.159-2.477 10.303-4.943 15.434-7.411 7.296-3.539 14.53-7.185 21.707-11.03-5.889 15.917-23.933 28.293-31.121 33.227-7.13 4.838-14.937 8.81-22.656 12.892-.879.458-1.761.93-2.628 1.391 3.425-.517 7.002-.467 11.011 1.118a18.316 18.316 0 01-.466 3.123c-2.118 4.342-5.482 7.539-8.408 10.992 4.216.383 8.345.804 12.177 1.521-.341 2.148-1.079 3.819-1.958 5.302 10.452 2.836 21.444 3.614 31.643 2.764l.536.314c2.767 9.89.335 15.644-3.705 19.512-.97.954-2.041 1.795-3.15 2.549.609 12.688-8.208 20.033-18.847 25.331-5.695 2.823-11.903 5.066-17.471 7.198-5.55 2.108-10.534 4.293-13.627 6.744-8.115 6.463-15.388 14.428-22.435 22.704a825.577 825.577 0 00-5.414 6.433c.027 2.054.201 4.189.518 6.431 15.077 10.829 27.166 8.277 38.66 3.823 5.746-2.22 11.35-4.911 17.057-6.627a44.187 44.187 0 014.308-1.069 36.646 36.646 0 014.42-.509 27.97 27.97 0 019.135 1.057c1.644 6.975.041 11.519-2.506 15.19-2.551 3.683-5.969 6.587-8.113 10.116 3.756-1.589 8.059-3.144 12.602-3.954 4.546-.823 9.23-1.001 13.565-.016-2.173 2.728-4.146 5.762-5.742 9.243a41.76 41.76 0 00-2.093 5.601 46.861 46.861 0 00-1.336 6.467c1.098-.917 2.336-1.93 3.72-3.01 1.406-1.049 2.925-2.199 4.55-3.411 3.259-2.407 6.922-5.104 10.804-7.963 7.781-5.742 16.46-12.263 24.52-18.938a28.847 28.847 0 01-2.103 3.523c4.063 5.113 8.161 8.049 12.249 9.716 4.113 1.686 8.236 2.133 12.32 2.209 8.182.154 16.223-1.132 23.81 3.361 2.836 15.752-9.984 16.633-17.077 20.17 4.961 1.1 13.15 8.332 15.998 10.001-2.392 1.798-5.395 4.792-8.295 7.44-2.899 2.649-5.611 5.009-7.588 5.427 3.534-.456 9.872 2.474 14.042 3.549 4.66 11.181 1.654 16.466-3.36 19.616-5.011 3.137-12.045 4.15-15.332 6.782a82.599 82.599 0 0012.615 4.271 4792.95 4792.95 0 0121.543-10.723c12.079-5.951 24.246-11.791 36.609-17.231 8.655-3.748 17.547-7.467 26.294-11.346 8.76-3.878 17.348-7.922 25.257-12.522-10.661 4.54-23.387 9.535-35.896 13.641a207.816 207.816 0 01-18.442 5.214c-5.93 1.391-11.532 2.384-16.507 2.832 5.296-3.004 13.963-6.669 20.27-10.256-5.797 2.13-10.837 2.782-13.116 1.096 2.69-1.797 5.789-3.628 9.201-5.468 3.422-1.825 7.134-3.691 11.013-5.58 7.795-3.758 16.447-7.494 25.235-11.083 8.788-3.589 17.695-7.022 25.986-10.177 8.29-3.155 15.958-6.006 22.224-8.562 22.639-9.134 45.148-18.588 67.625-28.155 22.408-9.715 44.844-19.425 67.511-28.715 6.353-2.662 12.716-5.376 19.078-8.089a2973.09 2973.09 0 0011.688-4.998l.189-.073c4.037-1.725 8.077-3.464 12.115-5.189l7.811-3.337c-11.101 9.728-82.682 48.709-80.02 66.289 6.109 14.817 39.502 4.019 45.297 2.926 5.797-1.105 57.036-22.173 61.081-20.419 4.211 1.811 15.768 5.968 24.715 15.901-2.076 1.102-4.139 2.205-6.212 3.294-7.331 3.951-14.622 7.977-21.905 12.032-7.285 4.067-14.556 8.137-21.813 12.196-7.256 4.059-14.495 8.095-21.794 11.944-12.159 6.462-24.37 12.617-36.623 18.63-1.097 15.96-14.314 15.771-21.573 18.306-7.19 2.522-15.258 4.909-22.488 9.337-6.627.926-14.172 1.711-21.568 3.372-3.763 1.776-7.556 3.559-11.348 5.343a39.156 39.156 0 014.945-.13c5.23.219 11.019 1.511 17.446 4.242-.774.842-2.149 1.425-3.639 2.054-1.514.612-3.166 1.239-4.441 2.231-2.556 1.957-3.681 5.296.45 12.457 2.53.68 6.656 2.584 8.691 2.297-2.226.521-5.371 5.901-7.709 8.208 1.866 1.071 7.517 5.681 10.558 6.374-2.9 1.772-7.914 2.577-8.408 7.541 9.984-1.146 19.798-2.323 29.02 3.32-.029 3.392-.629 6.072-1.575 8.284a30.504 30.504 0 013.774 3.456c-.335.721-.62 1.464-.86 2.255 2.039-2.146 4.165-4.249 6.278-6.288a372.268 372.268 0 016.061-5.99c3.888-3.775 7.331-7.038 9.53-9.398.368 5.851-.882 10.164-2.774 13.712 5.399 1.139 11.078-.716 15.2-2.83-1.686 4.556-6.962 7.375-3.248 16.293 7.916 2.114 13.869-1.969 20.149-5.115 6.349-3.081 13.302-4.972 23.88 1.581 4.423 12.768.129 20.809-6.449 26.529-.371.336-.767.655-1.162.973 7.116 1.549 14.22 2.651 20.039 8.033-.561 16.49-13.807 14.409-21.752 16.346 4.739 2.232 11.372 11.335 13.897 13.659-5.692 2.514-15.027 9.305-19.299 9.221 2.329.239 5.46 2.054 8.467 3.887.587-.189 1.161-.38 1.746-.555 3.618-1.129 7.393-1.716 13.006 2.145-2.963 2.408-6.74 3.817-7.366 8.986 7.079 3.295 12.285-3.621 18.382-8.19 5.192-3.831 10.964-6.195 19.432.549 12.626-2.763 24.483-4.859 31.757-8.133 26.974-12.112 51.689-30.933 76.765-45.846 16.63-9.887 33.72-17.531 50.87-24.776 11.14 1.291 24.39-.197 37.76-3.243 4.05-.901 8.09-1.95 12.14-3.041 6.69-.457 13.52-.752 20.57-.616 3.83.028 8.63.532 13.78.859 5.2.28 10.82.316 16.11-.412 5.38-.832 10.45-2.462 14.55-5.524 3.39-2.558 6.09-6.08 7.69-10.827 6.41-.372 14.67-.838 23.91-1.218 13.06-.619 28.09-1.095 42.68-.842 2.65.041 5.27.104 7.87.178 3.76 2.039 7.78 4.771 14.25 15.125 6.37 23.383-2.18 21.032-5.86 23.147-6.03 3.543-13.53 6.397-14.53 23.185 5.32 3.835 10.27 6.308 14.97 7.885 4.69 1.575 9.11 2.09 13.37 2.252 6.94.21 12.26-11.066 23.43 1.755-1.26.321-4.64 13.584-5.37 12.641.14.188.28.363.41.535-6.2 3.322-12.71 6.844-19.63 11.182-4.25 2.657-8.05 5.907-10.8 10.026-25.21 7.449-49.75 16.556-69.97 29.18 25.45.428 51.13-.815 76.89-3.216 6.45 1.63 12.31.279 17.98-1.685.24-.077.47-.169.72-.258 8.62-.992 17.25-2.09 25.88-3.295 1.66 9.902-5.26 12.931-8.5 18.025 5.72-2.226 13.28-4.113 19.77-2.45-3.33 4.094-6.03 9.111-7.16 16.087 3.39-2.667 8.61-6.358 14.5-10.614 2.95-2.127 6.05-4.402 9.2-6.748 3.2-2.284 6.44-4.642 9.55-7.049-.5.92-1.06 1.774-1.67 2.592 12.05 15.961 24.94 5.603 36.27 12.779 1.96 12.047-7.68 12.433-13.05 14.965 3.72.94 9.8 6.625 11.92 7.959-3.63 2.621-9.16 8.845-12.18 9.369 2.7-.246 7.52 2.175 10.61 3.08 3.41 8.619 1.02 12.534-2.86 14.774-1.94 1.112-4.25 1.807-6.41 2.475-2.11.691-4.06 1.344-5.31 2.328 7.56 3.477 14.75 5.215 21.53 5.652 6.77.434 13.14-.418 19.23-1.955 12.16-3.078 23.15-8.959 33.75-12.741 17.21-5.803 33.87-13.851 50.12-22.824 4.07-2.272 8.13-4.546 12.19-6.805 4.09-2.282 8.14-4.598 12.17-6.932 8.06-4.667 16.03-9.417 23.91-14.05 5.15-3.03 10.34-5.905 15.54-8.751 5.27-2.793 10.52-5.577 15.7-8.493 10.36-5.848 20.45-12.272 29.85-20.549 7.51-6.694 14.92-14.176 22.44-21.448 2.63-.704 5.26-1.394 7.88-2.098 8.91-2.395 17.81-4.79 26.74-7.196 3.65-.96 7.27-1.979 10.9-2.998-4.57 3.447-9.5 6.653-13.35 9.99 12.46-5.449 28.27-11.469 40.17-12.465-8.76 6.774-16.83 14.445-22.97 23.885 8.21-5.041 20.57-12.263 34.56-20.503 7-4.12 14.4-8.504 21.88-13.006 3.89-2.428 7.79-4.879 11.67-7.348-4.67 4.588-9.33 9.313-14.08 13.941-7.23 7.151-14.82 13.764-23.05 18.829-3.16 1.928-7.97 3.283-13.47 4.524-5.49 1.241-11.6 2.479-17.28 4.361-2.85.947-5.59 2.061-8.07 3.411-2.51 1.332-4.81 2.905-6.72 4.818-3.75 3.733-6.1 8.748-6.11 15.66 1.99.769 4.1 1.534 5.43 1.388-.63.127-1.36.599-2.16 1.275 5.82 2.997 11.55 2.041 17.13 8.524-2.02 1.665-3.77 6.196-5.97 7.651 3.79-.125 8.04-.707 12.23-.383-1.57 5.319-6.34 6.656-8.17 12.884 4.4 1.615 9.3 2.867 14.26 4.154l.36-.297c2.32-1.987 4.62-3.911 6.79-5.708 4.36-3.595 8.2-6.704 10.68-8.958-.31 5.819-2.06 10.028-4.37 13.471 5.31 1.326 11.22-.297 15.6-2.243-2.21 4.461-7.83 7.04-5.11 15.992 7.72 2.401 14.11-1.426 20.77-4.246 6.73-2.782 13.91-4.349 23.68 2.609 2.93 12.831-2.24 20.562-9.42 25.889-3.57 2.701-7.74 4.645-11.65 6.394-3.91 1.734-7.58 3.23-10.31 4.833-6.28 3.807-12.67 7.66-18.31 12.664-2.83 2.486-5.49 5.233-7.91 8.334-.26.329-.51.674-.76 1.005 2.36.891 4.52 2.017 6.35 3.444 2.23-.521 4.58-.883 7.07-1.034 7.94-.51 16.9.961 24.92 4.576-1.73 3.592-4.87 5.428-4.85 10.998 4.68 1.952 8.33.586 12.01-.369 3.69-.953 7.5-1.346 12.66 2.702-3.18 2.235-7.06 3.422-8.16 8.476 6.67 3.53 12.49-2.976 18.96-7.179 6.5-4.144 13.6-6.25 23.41 6.253-2.48 2.159-4.99 4.287-7.54 6.311a433.038 433.038 0 01-7.78 5.919c-5.24 3.876-10.48 7.74-15.72 11.617-10.52 7.598-20.78 15.769-30.23 25.344-1.08 1.122-2.15 2.273-3.23 3.424 5.1-.817 10.13-1.902 15.08-3.109 11.43-2.726 24.05-14.442 38.07-8.969-2.19.838-12.15 13.176-13.01 12.721 12.42 6.594 24.74-2.668 35.96-5.11 20.08-4.145 39.84-10.437 60.01-15.522 7.92-2.049 15.7-4.421 23.41-6.969 7.7-2.563 15.26-5.447 22.86-8.271l11.39-4.257 11.39-4.325a496.424 496.424 0 0123.17-8.01c8.65-2.833 21.65-5.649 33.41-10.554 11.7-5.009 22.23-11.863 25.78-22.44-4.52-5.858-9.36-7.784-13.33-6.572 2.85-4.491 8.17-7.625 10.52-11.454-5.02-2.391-10.05-3.812-14.99-4.571-4.96-.778-9.82-.834-14.49-.37-4.65.466-9.11 1.441-13.22 2.721-4.12 1.292-7.93 2.884-11.33 4.498 3.68-5.498 8.61-9.297 13.95-12.427 5.34-3.157 11.06-5.734 16.15-9.059 3.72-2.511 7.24-5.328 10.65-8.328a260.416 260.416 0 0030.35-8.553c9.48-3.208 18.65-6.902 27.79-10.762 7.36-6.266 19.75-12.266 32.93-17.786 1.38-.581 2.8-1.144 4.2-1.723a71.027 71.027 0 015.28-2.13c5.35-2.117 10.65-4.164 15.61-6.084 1.97-.769 3.89-1.522 5.75-2.246 1.84-.755 3.6-1.536 5.27-2.267 3.34-1.476 6.35-2.865 8.85-4.158 13.07-6.767 25.95-14.214 38.71-21.764 6.37-3.791 12.74-7.581 19.1-11.361 3.17-1.883 6.34-3.769 9.5-5.655 3.14-1.917 6.28-3.849 9.4-5.755 14.36-8.672 28.86-16.658 43.5-24.471 7.33-3.871 14.67-7.739 22.03-11.631 7.37-3.863 14.62-8.045 21.96-12.142 22.39-12.59 45.42-25.56 68.44-38.507 14.89-8.6 29.88-17.064 44.8-25.202-.43.205-.87.393-1.29.6-6.38 3.034-12.36 6.249-17.69 9.448 6.53-7.873 14.67-13.966 23.19-19.801 4.27-2.883 8.66-5.636 12.99-8.398 4.32-2.833 8.6-5.714 12.69-8.738 23.43-17.399 43.23-39.737 63.43-61.22 4.51-4.671 9.01-9.345 13.52-14.003 4.53-4.669 9.15-9.102 13.72-13.723 4.56-4.622 9.16-9.183 13.79-13.697l3.46-3.381 3.42-3.484 6.87-6.921c9.25-9.057 18.13-18.736 27.09-28.184 8.67-9.906 17.44-19.591 25.42-30.204-6.97-4.149-13.23-3.925-19.03-1.229-5.89 2.585-11.4 7.545-16.69 13.177a213.53 213.53 0 00-3.95 4.304c-1.34 1.412-2.66 2.827-3.98 4.215-2.64 2.791-5.24 5.507-7.84 7.889-2.58 2.396-5.13 4.475-7.69 5.974-2.58 1.44-5.15 2.344-7.69 2.444 2.71-7.415 8.96-12.186 14.31-17.7-3.79-.63-7.21-.043-10.4 1.217-3.21 1.244-6.18 3.233-9.25 5.217-6.08 4.019-12.08 8.673-19.22 9.654.9-7.329 6.16-12.174 9.5-18.165-6.11.658-12.65 2.207-19.22 4.409-3.27 1.125-6.64 2.312-9.96 3.631a135.65 135.65 0 00-9.82 4.37c-3.23 1.564-6.32 3.288-9.37 5.005a127.078 127.078 0 00-8.78 5.345c-2.76 1.851-5.37 3.772-7.75 5.734a88.662 88.662 0 00-3.41 2.958c-1.1.972-2.14 1.94-3.12 2.933 15.48-24.602 37.06-44.336 57.77-63.799 2.21-2.115 4.78-4.366 7.59-6.761 2.79-2.413 5.92-4.857 8.91-7.65 3.04-2.745 6.19-5.631 9.3-8.619 3.1-3.003 6.19-6.073 8.91-9.429 2.77-3.307 5.36-6.713 7.63-10.179a77.192 77.192 0 003.15-5.245c.92-1.785 1.7-3.623 2.38-5.451 2.75-7.301 3.64-14.713 1.6-21.988-7.01-.332-13.06 1.55-18.56 4.705-2.78 1.566-5.3 3.516-7.93 5.539-.48.372-.94.772-1.41 1.144.17.072.35.119.53.191-.39 1.21-1.68 2.24-2.14 3.706 1.04-.443 2.56.079 4.32 1.83-.41 6.637-9.69 9.22-14.12 11.331-4.02 1.918-7.68 4.199-11.52 6.405-3.83 2.221-7.67 4.414-11.81 6.242-5.25 2.312-10.23 5.064-15.47 7.242-2.9 1.247-5.54 4.698-9.73 3.173.22.082 1.38-2.685 2.11-3.939-1.95 1.744-3.88 3.519-5.83 5.305-2.16 1.935-4.36 3.796-6.57 5.682-2.22 1.871-4.45 3.726-6.73 5.504-2.28 1.792-4.6 3.55-7.05 5.068-3.39 2.167-7.04 4.126-10.56 6.256-1.75 1.066-3.47 2.18-5.11 3.389-1.64 1.167-3.22 2.414-4.66 3.807 4.42-2.248 9.71-4.849 15.22-6.655 5.5-1.834 11.26-2.825 16.35-2.523-.92.99-2.24 2.082-3.63 3.134-1.4 1.052-2.82 2.151-3.96 3.318 2.46-1.113 4.99-1.038 6.84.716-3.46 4.918-10.45 9.417-17.87 13.258-7.49 3.639-15.41 6.658-20.84 9.019-19.66 8.184-38.6 17.364-58.5 24.155-12.62 4.287-25.19 8.934-37.73 12.671-7.02 2.176-15.53 6.962-22.2 5.778.46.083 7.95-5.672 9.38-6.218-6.59-.522-14.08 3.587-20.63 6.2 3.72-3.2 7.43-6.387 11.17-9.569 17.73-15.169 35.7-29.596 54.56-42.334 3.62-2.436 8.07-4.935 13.11-7.514 5.03-2.581 10.66-5.254 16.62-7.933 11.91-5.402 25.14-11.209 37.63-17.662 6.26-3.22 12.32-6.607 17.97-10.168 5.65-3.477 10.88-7.141 15.42-11.024 9.09-7.781 15.46-16.45 17.12-26.256-13.09-3.468-28.18 4.842-40-1.48 4.65-3.595 9.48-10.957 14.48-14.393-7.98 2.633-16.98 6.155-25.64 8.421 4.66-8.324 14.95-13.242 20.35-22.98-7.8.536-16.51 1.727-25.38 2.982l12.11-7.992a1899.952 1899.952 0 0133.05-21.119 2549.799 2549.799 0 0133.38-20.479l16.95-10.139 17.12-10.12 34.32-20.104 17.16-10.03 4.29-2.514 2.15-1.251 1.07-.626 1.13-.71 8.91-5.574c9.71-6.13 20.43-12.586 31.66-19.446 11.27-6.869 23.09-14.16 35.31-22.161-4.52-4.28-9.73-6.626-15.34-7.642-2.83-.492-5.77-.627-8.81-.431-3 .136-6.11.59-9.3 1.312-12.69 2.818-26.41 9.479-39.31 16.909-3.27 1.866-6.33 3.758-9.5 5.683-3.54 2.207-7.09 4.4-10.59 6.574-1.26.738-2.52 1.465-3.77 2.192.65-2.428.96-4.839.86-7.2-9.55-6.528-23.02-3.824-37.08 2.046-19.1 5.361-39.27 17.982-58.7 18.834 6.89-11.971 20.27-19.26 30.91-28.763-23.74.439-53.62 6.72-82.46 16.17-.64.207-1.25.417-1.89.625-3.72-.293-7.49.388-11.14 1.944 3.77-4.538 8.77-8.689 13.64-12.648 4.9-4.032 9.66-7.876 12.88-11.707-3.6-.729-7.26-1.225-10.95-1.539 25.11-15.689 51.13-30.079 76.96-43.883 5.91-3.16 12.63-6.495 19.91-10.024 1.82-.88 3.67-1.78 5.57-2.686 1.87-.908 3.72-1.918 5.62-2.889 3.76-1.978 7.74-3.984 11.79-6.045 16.16-8.193 33.54-16.755 49.69-26.46 16.13-9.696 31.06-20.567 41.89-32.637 4.76-5.273 8.71-10.763 11.64-16.33-6.32 2.586-12.67 5.073-19.06 7.364-11.51 4.209-23.26 15.529-36.99 10.92.95.323 9.97-13.711 12.18-14.923-15.28-3.223-27.22 10.625-38.79 15.319-7.15 2.921-14.45 5.611-22.03 7.696-7.58 2.073-15.44 3.567-23.68 4.212.27-.693.55-1.369.85-2.029-3.13 1.043-6.28 2.042-9.35 2.839-.82.214-1.7.456-2.46.614-.76.157-1.5.317-2.24.464-1.5.292-2.97.533-4.44.723.22-2.105.75-4.136 1.54-6.094.42-.987.81-1.913 1.44-2.903a36.29 36.29 0 012.09-2.959c1.52-1.946 3.27-3.861 5.18-5.722 1.87-1.813 3.91-3.595 6.03-5.348 8.46-6.938 18.26-13.147 25.17-19.25-1.77.874-3.62 1.774-5.53 2.678 8.12-6.785 16.28-13.576 24.44-20.396-13.76-4.791-21.88 3.666-35.52-.43 4.01-5.274 10.01-9.335 11.63-16.735-6.45-4.095-11.83-3.196-16.44-.984.3-.228.59-.443.89-.645-4.93-6.135-14.78-7.243-24.44-6.618-9.68.595-19.16 2.939-23.34 3.817-11.18 2.381-29.74 25.932-45.96 10.929-.97-3.399-1.15-6.735-.76-9.992 2.06-1.2 4.12-2.398 6.16-3.6 9.8-5.764 19.42-11.545 29.09-17.385 12.61-7.605 25.42-14.432 37.87-21.879 12.51-7.556 24.72-15.841 35.97-26.069 4.92-4.488 9.71-9.215 14.48-14.109 3.48-3.607 6.97-7.238 10.46-10.858-1.67-.184-3.3-.39-4.87-.651 1.38-6.863 6.6-9.18 7.8-15.052-4.75.476-9.48 1.897-13.77 2.768 2.34-1.956 3.84-7.032 5.93-9.168-8.31-6.771-15.79-2.482-24.32-7.189-1.81-14.999 8.75-23.479 21.27-29.658 6.28-3.079 13.13-5.542 19.25-7.896 6.13-2.351 11.46-4.635 14.88-7.243 8.87-6.849 16.89-15.216 24.49-24.011 5.98-6.894 11.86-13.93 17.81-20.628-5.84-1.18-12.08-1.989-18.36-2.873-7.41-1.131-14.81-2.408-21.48-4.578 4.36-3.203 8.99-5.977 13.79-8.508 4.77-2.6 9.69-5 14.64-7.353 9.89-4.694 19.89-9.246 28.99-15.191 3.09-2.049 7.78-5.001 12.84-9.073 5.07-4.03 10.58-8.935 15.37-14.539 9.6-11.219 16.32-25.203 11.13-40.539-3.93-3.7-7.85-5.701-11.69-6.485 3.74-1.848 7.4-4.02 10.93-6.661 15.62-11.636 1.05-12.329-9.15-15.374-10.26 5.542-20.61 10.825-31.31 15.48-18.77 8.159-36.88 17.382-55.29 25.094a37.095 37.095 0 01-3.56-1.098c.36-.661.77-1.247 1.19-1.816-.31.117-.63.234-.95.351-7.42 2.533-15.19 4.53-23.46 5.68 1.8-7.003 5.39-12.705 9.8-17.611-4.58 1.047-9.3 1.595-14.78-.557.07-1.174.25-2.233.47-3.216 2.75-5.601 7.56-9.328 10.96-14.054-1.26-.516-2.49-.902-3.67-1.24-13.49-5.404-28.58-7.095-42.29-6l-.56-.332c-2.91-10.2-.39-16.126 3.83-20.096 4.16-4.034 10.04-6.137 13.88-8.572 4.55-2.885 9.48-5.888 14.17-9.395-5.79-2.977-13.8-3.1-21.62-2.082-9.94 1.33-19.56 4.482-23.83 5.707-2.86.822-6.06 3.052-9.54 5.702-3.46 2.694-7.19 5.811-11.14 8.401-7.9 5.167-16.68 8.165-25.98 1.281-4.93-12.066.4-24.39 8.29-34.763 3.94-5.208 8.54-9.837 12.79-13.679 4.25-3.856 8.15-6.942 10.73-8.988 7.61-6.043 16.06-10.935 24.41-15.94 4.78-2.868 9.54-5.796 14.08-8.952-.1-3.334.65-6.027 1.86-8.34-9.78-.568-20.32.165-29.42-.63.95-6.997 6.1-9.718 6.96-15.72-2.44.428-4.86 1.104-7.21 1.817-7.63 5.568-15.95 11.763-23.72 18.101a29.511 29.511 0 012.2-3.626c-4.26-5.271-8.54-8.309-12.82-10.051-4.29-1.745-8.61-2.215-12.88-2.311-8.54-.18-16.95 1.133-24.89-3.519-3-16.241 10.38-17.113 17.79-20.74-5.2-1.143-13.76-8.619-16.74-10.353 2.49-1.847 5.62-4.926 8.65-7.644 3.02-2.721 5.84-5.142 7.91-5.57-3.69.454-10.31-2.581-14.67-3.692-4.89-11.534-1.77-16.971 3.45-20.205 5.23-3.232 12.58-4.242 16-6.956-10.66-4.518-20.52-6.518-29.78-6.791-4.61-.117-9.17.137-13.62.722-4.43.588-8.74 1.508-12.94 2.664-16.82 4.608-31.82 13.002-46.81 18.39-11.88 4.343-23.53 9.429-35.05 15.05-11.7 5.575-23.23 11.681-34.6 18.086a1610.044 1610.044 0 00-34.04 19.899c-11.29 6.863-22.49 13.676-33.53 20.383-14.63 8.799-29.59 16.74-43.94 25.305a373.71 373.71 0 00-11.69 7.25c-2.19-1.104-4.3-2.353-6.2-3.808 3.58-4.357 8.65-4.653 13.26-6.245-5.69-8.109-11.3-9.151-16.97-9.371-5.67-.192-11.39.509-17.4-4.193.27-1.068.61-2.004.98-2.866 3.35-4.507 8.1-6.829 11.88-10.368a26.995 26.995 0 00-2.88-2.053c-10.4-8.271-23.06-13.506-35.03-15.489l-.41-.44c-.08-.998-.09-1.944-.08-2.844-15.66 9.229-31.4 18.321-47.36 27.037-25.39 13.857-50.87 28.171-76.16 41.414-14.18 7.52-32 19.303-44.66 22.506.88-.217 16.98-12.519 19.92-14.203-14.97 4.677-34.16 18.151-48.86 25.952-18.13 9.705-36.57 19.017-55.17 26.966 12.95-11.412 31.76-22.3 47.39-31.682 9.6-5.732 26.66-14.286 29.12-21.936-14.03 2.169-28.04 12.173-42.38 16.076 6.81-4.923 15.24-9.979 20.42-15.134-5.16.373-11.47 3.286-17.56 6.656.44-.27.87-.529 1.28-.777-2.58-1.512-12.9 2.187-23.83 6.84-10.93 4.638-22.49 10.239-27.51 12.62-13.47 6.396-44.67 28.609-55.87 26.942 3.34-11.315 48.4-38.209 63.19-47.099 10.91-6.514 22.07-12.754 33.21-19.025 11.15-6.271 22.25-12.563 32.92-19.31-10.98 3.455-26 9.714-37.27 13.522 4.61-4.626 11.25-8.246 15.22-12.22-5.28 2.333-10.98 5.221-15.96 7.582 3.39-2.167 7.32-5.799 10.55-7.97-5.75-.395-15.71 5.379-22.68 6.31 9.99-16.053 60.72-38.481 80.17-50.843 12.7-8.046 25.27-16.589 37.61-25.39 12.34-8.774 24.67-17.55 36.85-26.203 13.98-9.835 28.15-19.053 42.25-28.353 7.09-4.603 14.19-9.203 21.3-13.814 7.1-4.642 14.16-9.369 21.26-14.117a3113.31 3113.31 0 0016.9-11.415c-.81.336-1.63.684-2.48 1.028-4.35 1.816-8.96 3.732-13.61 5.79a418.522 418.522 0 00-13.93 6.528c.87-1.041 1.8-2.002 2.79-2.899-10.92-23.572-30.18-13.67-42.93-25.608.58-16.001 13.25-14.011 20.86-15.918-4.54-2.154-10.86-10.984-13.27-13.219 5.46-2.463 14.41-9.069 18.49-8.993-3.57-.359-9.14-4.684-12.95-6.611-2-11.946 2.31-16.398 8.08-18.317 2.89-.966 6.14-1.285 9.16-1.606 3.02-.281 5.81-.552 7.76-1.446-8.94-6.669-17.89-10.631-26.73-12.835-8.86-2.205-17.62-2.642-26.2-2.114-8.58.526-17.02 2.185-25.21 3.916-4.91 1.053-9.72 2.137-14.45 3.143 2.78-2.522 5.58-5.042 8.35-7.579l3.6-3.328 3.53-3.465 6.97-6.983c-6.42-4.938-12.67-5.453-18.74-3.454-3.02 1.036-6.1 2.533-9.11 4.487-3.03 1.938-6.02 4.273-8.97 6.764-2.97 2.46-5.85 5.207-8.81 7.657-2.94 2.493-5.84 4.898-8.68 7.004-5.71 4.183-11.1 7.21-16.22 6.835 1.76-3.559 4.27-6.412 7.15-8.943 2.87-2.534 6.07-4.764 9.04-7.224-7.43-2.038-13.87.864-20.29 4.403-6.51 3.387-13.03 7.406-20.28 7.653 1.7-7.196 7.5-11.482 11.51-17.123-6.19.051-12.91.932-19.75 2.48-3.43.759-6.85 1.749-10.33 2.74a143.963 143.963 0 00-10.33 3.414c-13.5 5.164-26.3 11.656-34.79 19.006 18.06-23.184 41.81-40.968 64.78-58.371 9.91-7.427 25.8-16.937 38.65-28.839 3.19-2.986 6.22-6.097 8.93-9.305 1.35-1.613 2.66-3.219 3.82-4.894a67.103 67.103 0 003.11-5.169c3.73-6.974 5.64-14.264 4.61-21.799-6.96-1.157-13.3.051-19.26 2.595-3 1.269-5.86 2.901-8.73 4.693-2.9 1.744-5.76 3.7-8.58 5.729-5.64 4.06-11.11 8.502-16.88 11.916-5.71 3.468-11.42 6.221-17.3 7.068-2.11-9.958 7.42-16.916 12.05-24.085-3.73 2.63-8.06 5.421-12.46 7.74-4.41 2.319-8.84 4.227-13.01 4.904 2.37-2.867 4.41-6.037 6.16-9.396 1.74-3.361 3.15-6.943 4.08-10.817-4.51 4.442-11.17 11.051-19.26 18.153-1.99 1.805-4.04 3.665-6.14 5.558-2.1 1.892-4.19 3.867-6.41 5.764-4.4 3.83-8.84 7.759-13.14 11.673a40.886 40.886 0 012.44-3.448c-3.48-2.714-7.2-3.706-11.02-3.721-3.84-.031-7.88.844-11.87 2.025-4.01 1.179-8 2.615-11.87 3.604-3.83 1.007-7.7 1.43-11.16.767-.83-11.286 11.59-15.811 18.9-20.644-4.74.626-11.94-2.57-14.53-3.044 2.49-2.091 5.7-5.275 8.77-8.163 3.04-2.919 5.84-5.6 7.79-6.523-3.47 1.425-9.23 1.137-13.17 1.477-3.41-7.243-.11-12.259 4.94-16.185 2.51-1.966 5.48-3.666 8.24-5.266 2.77-1.58 5.18-3.16 6.82-4.69-18.49-.32-35.12 5.46-50.89 13.392-3.91 1.992-7.94 4.016-11.79 6.18-3.88 2.131-7.64 4.393-11.45 6.565-3.82 2.182-7.61 4.33-11.32 6.44-3.71 2.095-7.33 4.167-11.05 5.991-23.27 11.942-47.16 25.227-71.35 38.928-4.14 2.34-8.29 4.691-12.44 7.043a18.74 18.74 0 01-4.56-1.026c1.78-.899 8.39-13.139 9.2-12.733-5.98-2.946-11.26-2.054-16.21-.106-2.47.988-4.89 2.202-7.23 3.377-2.32 1.192-4.62 2.292-6.94 2.957-16.56 4.765-32.57 11.246-49.19 16.376-.08.027-.16.052-.23.079 2.78-3.762 4.24-8.44 3.56-14.348-6.96-4.378-12.99-1.038-19.69-7.214 1.69-1.725 2.88-5.996 4.76-7.565-3.44.541-7.25 1.54-11.06 1.717.95-4.961 5.14-6.686 6.18-12.465-4.56-1.067-9.62-1.687-14.67-2.398-5.06-.767-10.12-1.642-14.68-3.128 3-2.226 6.17-4.151 9.44-5.921 3.27-1.812 6.64-3.486 10.03-5.115 6.77-3.271 13.62-6.448 19.86-10.587 2.11-1.433 5.33-3.483 8.79-6.312 3.48-2.799 7.25-6.218 10.54-10.115 6.58-7.792 11.2-17.502 7.68-28.132-9.45-9.01-18.71-3.39-26.55 1.9-6.34-4.99-11.99-4.18-17.29-1.53-5.33 2.57-10.36 6.95-15.32 9.21-3.26-.21-6.46-.75-9.32-1.8 2.11-3.86 5.98-5.17 9.31-7.3-11.36-8.99-17.95 1.87-28.57-2.32.05-.81.18-1.55.33-2.23 1.9-3.89 5.19-6.48 7.52-9.77-.87-.35-1.69-.62-2.51-.85-9.23-3.73-19.54-4.87-28.92-4.08l-.38-.24c-1.97-7.07-.24-11.17 2.66-13.94 2.86-2.81 6.88-4.28 9.51-5.98 4.77-3.07 10.12-6.31 14.58-10.72 1.87-.76 3.85-1.57 5.88-2.41 1.01-.44 2.03-.87 3.07-1.31 1.03-.46 2.05-.96 3.07-1.46 4.08-2.03 8.11-4.38 11.57-7.3 6.92-5.83 11.57-13.93 9.78-26.13-10.78-7.75-19.42-5.91-27.63-2.74-4.11 1.59-8.12 3.51-12.2 4.73-1.02.29-2.04.56-3.08.76-1.04.19-2.1.32-3.16.37-2.14.09-4.3-.12-6.52-.76-1.18-4.98-.04-8.23 1.79-10.86 1.82-2.63 4.26-4.7 5.79-7.22-2.69 1.14-5.75 2.24-9.01 2.82-3.25.59-6.61.71-9.7.01 1.56-1.95 2.97-4.12 4.1-6.61.57-1.24 1.08-2.57 1.49-4 .41-1.43.73-2.97.96-4.62-.78.65-1.67 1.38-2.66 2.16-1.01.75-2.1 1.57-3.26 2.43-2.33 1.72-4.94 3.65-7.71 5.7-5.56 4.12-11.76 8.76-17.53 13.52.44-.89.95-1.72 1.51-2.51-2.9-3.65-5.83-5.75-8.75-6.94-2.94-1.2-5.9-1.53-8.81-1.58-5.85-.12-11.6.81-17.02-2.4-2.03-11.26 7.13-11.89 12.21-14.42-3.55-.78-9.4-5.94-11.45-7.14 1.71-1.28 3.86-3.43 5.94-5.31 2.07-1.9 4-3.58 5.43-3.87-2.53.33-7.05-1.77-10.03-2.55-3.33-7.99-1.19-11.76 2.39-14 3.58-2.25 8.61-2.97 10.96-4.85-7.27-3.12-14.03-4.49-20.36-4.66a58.4 58.4 0 00-9.3.53c-3.03.41-5.98 1.07-8.87 1.88-11.51 3.22-21.79 9.07-32.05 12.83-8.14 3.04-16.11 6.59-24 10.51-8.02 3.87-15.9 8.13-23.71 12.6-7.74 4.48-15.6 9.13-23.33 13.86-7.72 4.78-15.42 9.51-22.97 14.19-6.34 3.88-12.75 7.51-19.11 11.17-3.54 1.47-7.05 2.6-10.52 3.26-.57-10.74 12.13-19 19.1-27.21-9.9 6.27-22.66 13.37-32.65 15.7 6.58-6.85 12.45-14.37 16.38-23.1-6.3 5.29-15.83 12.92-26.63 21.65-5.42 4.35-11.07 9.05-16.94 13.72-5.88 4.64-11.84 9.4-17.6 14.11a63.92 63.92 0 013.48-4.01c-7.42-5.19-17.12-2.78-27.03.83-9.9 3.62-19.98 8.45-28.27 7.89.53-12.2 16.29-18.97 25.83-25.29-5.79 1.32-13.99-1.21-17.05-1.35 6.71-5.34 17.58-15.97 22.66-18.52-4.47 2.03-11.6 2.38-16.39 3.36-2.97-7.61 2.11-13.61 9.13-18.67 3.51-2.52 7.51-4.82 11.22-6.95 3.7-2.14 7.03-4.23 9.38-6.17-11.24 1.08-22.55 3.65-33.45 7.58-10.93 3.9-21.62 9.01-32.02 14.72-10.48 5.65-20.87 11.74-30.86 17.89-5 3.08-9.94 6.13-14.78 9.11-4.88 2.96-9.58 5.91-14.38 8.55-29.58 16.89-59.36 35.48-89.02 54.51.08-3.08-.27-6.1-1.15-9.01-15.496-8.91-33.314 1.71-49.36 13.85 11.252-10.1 19.504-20.25 22.508-29.63-7.159.88-15.206 4.55-23 7.72-7.804 3.15-15.346 5.9-21.485 4.98 2.816-2.35 5.808-5.57 8.835-8.84 3.025-3.26 6.082-6.47 9.058-8.88-4.555 2.56-9.471 5.32-14.435 8.13a312.261 312.261 0 01-14.949 7.85c6.239-8.87 18.199-16.59 25.244-27.13-21.428 7.9-49.314 20.27-70.309 25.96 18.786-13.69 38.406-26.73 57.941-40.24 19.533-13.51 38.995-27.51 57.259-42.99 6.208-5.25 15.523-13.27 25.783-23.04 2.55-2.48 5.18-5.01 7.86-7.61 2.65-2.64 5.31-5.41 7.99-8.19 5.38-5.53 10.6-11.55 15.68-17.45 9.98-12.07 18.92-24.38 24.71-35.57 1.46-2.77 2.75-5.45 3.84-8 1.05-2.59 1.9-5.07 2.53-7.39 1.25-4.64 1.62-8.67.89-11.9-3.13.06-6.47 1.03-9.95 2.71-3.49 1.68-7.08 4.12-10.9 6.93-3.82 2.81-7.63 6.24-11.65 9.81-4.04 3.57-8.01 7.58-12.15 11.52-16.31 16-33.22 33.38-44.57 41-2.073 1.45-4.907 3.31-8.196 5.51-3.312 2.18-7.071 4.74-11.264 7.31-4.183 2.59-8.635 5.4-13.307 8.08-4.685 2.69-9.474 5.42-14.272 7.91-19.171 10.07-37.973 17.1-44.069 13.15.675-.52 1.382-1.07 2.093-1.64-5.119 3.51-10.468 6.65-15.808 8.94-5.315 2.29-10.661 3.66-15.755 3.57 7.575-11.08 21.472-21.49 32.023-31.97-6.578 1.76-12.909 4.3-19.064 7.29-6.235 2.89-12.252 6.22-18.4 9.31-6.125 3.11-12.251 6.08-18.593 8.32-6.294 2.27-12.783 3.84-19.521 4.19.045-.76.213-1.52.351-2.28 10.683-8.77 19.142-17.99 22.965-27.06 3.899-2.88 7.865-5.68 11.638-8.32 3.787-2.63 7.381-5.1 10.405-7.4 12.592-9.4 26.173-19.86 38.558-31.15a227.61 227.61 0 0017.373-17.53c5.214-6.08 9.993-12.21 13.747-18.55-29.221 17.22-58.426 35.91-87.971 54.68-10.873 6.87-23.015 16.1-35.747 24.72-12.741 8.6-25.947 16.65-38.681 21.17a92.92 92.92 0 003.697-3.17c-2.509 1.48-5.166 3.03-7.912 4.54-1.17.35-2.329.72-3.497.99 2.484-1.56 10.238-8.4 17.395-14.93 7.21-6.52 13.848-12.72 14.652-12.84-11.583 2-24.647 8.62-37.93 16.42-13.332 7.78-26.904 16.72-39.4 23.26-17.858 9.37-35.961 19.06-54.248 28.94-9.176 3.86-18.426 7.99-25.372 8.67 1.569-5.17 6.704-9.72 12.803-13.79 6.068-4.04 13.145-7.64 18.528-10.9-5.99 1.87-13.589.89-16.609 1.19 7.93-5.38 21.108-15.7 26.663-18.49-4.838 2.26-11.957 3.52-16.945 4.99-2.328-11.92 25.659-22.79 36.544-30.39-22.62 4.41-46.347 14.84-70.147 27.5-9.547 5.09-19.093 10.53-28.524 16.08 2.13-2.28 4.171-4.66 6.108-7.13 2.009-2.57.982-6.96-2.703-7.45-18.773-2.45-32.565 18.33-45.001 28.72-1.735 1.45-2.14 3.29-1.686 4.85-1.439 1.96-.876 4.84.828 6.36-.211.12-.425.26-.636.38.334-.06.638-.1.955-.14 1.047.76 2.45 1.05 4.076.42-.863.66-1.698 1.32-2.478 1.97 1.375-.88 2.78-1.78 4.173-2.69 12.119-5.06 22.942-12.18 32.22-21.08 1.426-.94 2.731-1.8 3.881-2.56-3.517 4.03-7.835 7.72-12.412 11.21 5.608-1.91 13.433-6.05 19.639-9.6-5.012 4.09-13.006 8.72-14.483 13.16 15.59-4.8 37.664-23.05 52.422-23.4-7.318 14.16-39.637 30.13-55.36 39.7-2.277 1.39-4.572 2.8-6.864 4.2a1397.41 1397.41 0 00-6.938 4.16 537.317 537.317 0 00-13.735 8.58c-6.871 4.45-13.546 9.09-19.867 13.93-13.173-.57-24.362 5.45-31.263 17.04-.45.77-.575 1.59-.477 2.38-4.172 1.28-8.535 1.99-13.172 1.77-5.95-.27-5.052 8.79.842 9.07 19.493.88 34.768-12.49 50.529-21.61 3.499-2.02 2.02-7.62-1.821-8.22a47.72 47.72 0 00-3.061-.36c13.686-7.8 32.17-15.59 44.549-18.18-3.725 3.03-8.201 5.68-10.862 9.08 4.392-1.01 9.191-3.58 13.822-5.9 4.589-2.37 9.042-4.46 12.845-4.39-4.604 2.9-9.474 5.57-13.141 9.17 11.597-1.93 37.983-27.21 47.075-20.42-7.492 4.97-15.008 9.86-22.559 14.71l-11.312 7.28c-3.763 2.44-7.54 4.87-11.365 7.2-15.243 9.45-30.414 19.03-45.271 29.24-4.19 2.93-8.393 5.85-12.599 8.78-4.205 2.94-8.483 5.75-12.742 8.61-8.539 5.68-17.165 11.25-26.047 16.46-6.192 3.62-12.717 7.07-19.083 10.687a225.67 225.67 0 00-9.372 5.598c-3.073 1.902-6.059 3.848-8.883 5.944 7.933-4.002 17.265-8.718 26.632-12.525 9.382-3.817 18.759-6.804 26.604-7.674-3.681 2.87-9.947 6.379-14.34 9.818 4.259-2.048 8.178-2.69 10.338-1.094-7.279 6.9-19.747 14.234-32.724 20.963-13.135 6.485-26.767 12.379-36.205 16.793-33.909 16.256-67.809 32.609-102.334 47.742a52171.33 52171.33 0 00-33.255 13.938c-11.085 4.65-22.138 9.265-33.128 13.69-3.085 1.24-6.364 2.727-9.731 4.252-3.395 1.453-6.881 2.969-10.305 4.362-6.861 2.784-13.541 5.103-19.055 5.546.753-.064 15.004-9.107 17.579-10.211-13.057 1.956-29.975 11.574-42.838 16.653a989.274 989.274 0 01-11.957 4.624c-4.008 1.489-7.985 3.036-12.074 4.307a427.358 427.358 0 01-16.886 5.109c-13.278 9.908-26.7 19.575-40.384 28.614-28.524 18.863-105.421 49.723-116.525 79.212 11.269-.141 25.844-9.043 35.512-6.85-4.616 3.479-9.906 9.679-14.809 13.105 7.451-3.43 15.921-7.711 23.897-10.991-5.26 6.838-15.033 12.349-21.107 20.319 8.695-2.353 18.733-5.699 28.735-8.93 10.017-3.162 19.991-6.237 28.551-8.143-7.762 5.193-15.713 10.176-23.763 15.033-8.045 4.899-16.17 9.729-24.33 14.525-16.318 9.58-32.705 19.093-48.493 29.309-21.464 13.892-86.63 56.045-88.042 77.461 12.755 3.946 36.263-10.649 56.51-23.645 19.082 2.385 49.77-22.457 73.887-36.414 6.733-2.457 13.099-4.455 18.377-5.522-7.214 6.615-16.306 11.496-24.872 16.96 17.528 1.816 39.282-17.4 58.936-21.138a35.966 35.966 0 01-2.279 3.087c-6.812 6.486-15.691 12.552-23.03 18.672a69.114 69.114 0 004.738-1.058c16.997-3.146 38.339-10.577 59.139-19.603l.654-.042c-.76 7.153-7.471 13.819-15.711 19.787-8.222 6.012-17.965 11.353-24.885 15.784-12.525 8.012-26.451 16.751-39.338 26.219-36.756 20.748-89.959 49.355-101.921 78.16 33.895 1.952 81.15-39.339 116.148-48.386-2.492 10.784-20.025 20.814-30.755 30.059.374-.201.75-.415 1.127-.629-3.724 3.616-6.204 8.161-6.71 13.999 7.123 4.894 14.941 1.814 21.467 8.485-2.36 1.696-4.718 6.021-7.287 7.531 4.183-.391 8.912-1.181 13.425-1.241-2.192 5.11-7.556 6.646-10.125 12.563 10.354 2.71 23.464 3.406 33.772 6.66-16.325 9.174-35.296 12.942-52.218 20.969a242.99 242.99 0 00-12.099 6.054c-4.887 2.608-10.305 5.832-15.282 9.595-9.97 7.524-18.273 17.214-17.264 28.533 8.582 10.082 21.497 4.864 32.54-.063a23.2 23.2 0 001.396 1.132c-.113.101-.24.199-.352.3-3.037 2.515-6.111 4.943-9.252 7.143-3.153 2.198-6.405 4.122-9.727 5.695-2.553 1.203-6.359 1.596-10.645 1.887-2.143.146-4.406.283-6.692.47-2.298.184-4.62.418-6.876.813-9.014 1.593-16.87 5.758-17.615 18.332 4.977 6.076 10.394 4.55 14.972 12.212-1.646 1.18-3.259 4.901-5.04 5.868 2.916.414 6.189.503 9.373 1.37-1.53 4.437-5.248 4.941-7.025 10.141 4.244 2.653 9.186 4.881 14.012 7.304a2483.487 2483.487 0 00-14.967 12.43c-4.067.857-8.098 1.815-11.963 3.262-7.396 2.834-28.882 10.535-31.294 29.03-19.034 11.521-75.183 18.073-78.108 49.05 2.052 1.011 4.167 1.534 6.281 1.841 4 6.113 6.995 9.035 8.621 9.064-.074 3.047-1.869 4.159-1.956 6.691 3.934 4.119 7.507 7.053 10.715 9.112a17.341 17.341 0 00-1.061 1.961c1.784.193 3.631.344 5.523.462 5.106 2.218 9.019 1.979 11.683.757-.312 7.572-5.763 7.38-9.029 10.338l-.163.078zM1354.7-736.653c-2 .751-3.99 1.477-5.98 2.164 1.91-3.923 5.89-7.385 10.7-10.518-2.05 2.562-3.65 5.341-4.72 8.354zm19.67 5.592c1.24.363 2.44.799 3.64 1.261-1.29 1.071-1.95 2.649-2.04 4.25-5.84.963-11.42 1.272-16.44-.162 2.92-1.97 5.49-3.574 7.36-4.391-2.26.894-5.01 1.481-7.84 1.931 1.21-1.059 2.42-2.238 3.65-3.495 3.92.315 7.89.182 11.7.598l-.03.008zm-19.81 250.273c-2.22 2.207-4.54 4.235-6.91 6.158-3.1 1.695-5.99 2.903-8.62 3.04-3.93.208-11.73.947-20.53.597 6.73-4.641 13.38-9.377 19.99-14.175 12.67-9.145 25.12-18.667 37.66-27.997 2.94-.052 5.94.081 9.02.497-11.65-1.613-23.07 24.381-30.61 31.88zM979.992-152.024l.55-.277c.76-1.572 2.434-1.44 4.813.143-2.408 1.512-4.866 3.446-7.315 5.625.006-3.045.886-4.676 1.952-5.491zm-140.355 47.042c7.809-4.794 15.66-9.525 23.539-14.185a1729.068 1729.068 0 0147.715-27.201 1929.535 1929.535 0 0124.207-13.051c1.373-.718 2.766-1.406 4.149-2.109 1.34.044 2.679.168 4.015.454-2.736 5.28-4.5 11.656-3.89 20.3 3.164-3.503 8.143-8.372 13.749-14.014a.29.29 0 00.084-.093c-.499.812-.983 1.612-1.402 2.425 1.371-.126 2.749-.29 4.125-.442.043 1.747.153 3.721-.281 4.396 2.126.755 4.339 1.107 6.857 2.446 1.169 5.525-1.043 5.901.315 12.378.441.391.884.769 1.339 1.149-4.018 4.263-7.658 8.083-10.629 10.314-18.134 13.964-38.552 21.645-58.151 32.941-.543.318-1.074.639-1.618.957-2.97.317-5.824.736-8.661 1.213a246.55 246.55 0 00-8.497 1.567c-2.802.577-5.661 1.17-8.387 1.843a306.802 306.802 0 00-8.14 2.118c-3.946 1.081-8.108 2.824-12.159 4.844-4.061 2.004-8.234 4.243-12.496 6.237-4.303 2-8.473 3.782-12.907 4.882a37.68 37.68 0 01-6.966 1.08c6.955-13.132 15.367-24.366 29.47-26.292-4.4.62-9.534 1.269-14.578 1.502-5.025.277-9.971.123-14.055-.91 3.846-2.515 7.693-5.044 11.568-7.5l11.698-7.247-.013-.002zM1683.51 361.78a60.82 60.82 0 002.15-1.964c-11.04 8.082-23.1 16.515-31.21 22.278-1.16.609-2.33 1.205-3.47 1.803 2.75-3.323 6.05-6.41 9.55-9.325-1.59.542-3.37 1.322-5.22 2.194.18-.56.23-1.117.18-1.652 2.22-2.18 4.62-4.111 6.62-6.426a27.932 27.932 0 00-5.56-2.382c4.56-2.816 9.4-6.039 10.45-9.714-5.63.853-14.31 6.47-20.94 8.394-1.99-.015-3.97-.002-6-.131l.01-.026c6.1-3.745 11.89-7.144 16.23-9.974 16.4-10.684 33.13-22.088 46.85-35.095-11.94 9.008-33.25 19.463-47 22.506 3.21-3.01 7.17-5.626 9.3-9.009-8.16 1.994-17.05 10.2-24.37 9.908 4.1-2.793 8.53-5.281 11.61-8.853-6.37 1.039-16.82 9.863-26.26 15.611 11.44-7.927 22.6-16.339 32.22-25.584-8.34 6.393-21.45 13.53-33.04 18.087 3.26-2.272 6.52-4.532 9.78-6.791 1.8-.72 3.52-1.361 4.96-2.093 10.23-5.173 22.38-10.544 29.63-22.178 6.6-4.775 13.13-9.641 19.57-14.658 14.65-11.423 28.91-23.498 44.3-34.082 10.74-7.383 22.85-14.07 32.17-22.79-13.6 8.65-32.95 19.047-47.53 21.019 3.09-2.909 8.53-6.479 12.2-9.944-3.74 2.09-7.37 2.772-9.63 1.224 11.85-13.891 43.07-29.713 59.43-39.083 29.5-17.092 58.29-35.044 88.12-51.619 14.75-8.169 29.47-16.68 44.14-24.905 10.7-1.014 20.08-8.997 30.74-11.433-4.6 4.007-10.49 8.021-13.75 12.369 4.07.06 8.63-2.136 12.92-4.771-.29.216-.6.415-.88.62 3.16 2.158 16.16-3.448 26.43-8.397-.29.675-.49 1.352-.56 2.039 11.38-.821 21.25-9.225 32.55-11.76-4.8 4.12-10.93 8.236-14.34 12.706 2.06.027 4.24-.489 6.44-1.324-6.1 4.527-11.76 8.408-15.15 10.724-15.19 10.42-31.55 19.561-46.47 30.247 8.25-2.286 19.23-6.903 27.61-9.555-2.9 3.915-7.6 6.745-10.12 10.098 3.84-1.748 7.92-3.966 11.51-5.772-.62.452-1.25 1.01-1.88 1.595-7.86 3.881-15.75 7.703-23.7 11.433-17.15 7.97-42.35 18.062-65.91 30.115-23.5 11.872-45.37 25.918-56.11 42.09 6.05 6.421 14.3 7.113 22.15 3.702-3.53 3.787-8.09 7.357-12.52 10.669-4.42 3.313-8.71 6.488-11.7 9.606 1.42.233 2.86.402 4.31.561-22.26 18.612-44.41 38.147-67.65 54.139-17.08 11.758-35.46 23.507-54.19 34.584-3.58 1.325-6.78 1.964-8.98.927l.01.042zm-175.9-1198.544c6.93 2.527 13.61 3.186 19.71 2.677-25.39 16.931-50.8 36.176-78.35 50.029-11.56 5.804-33.42 10.349-53.43 17.498-6.28 2.245-12.37 4.738-17.88 7.608-3.53.497-7.06 1.143-10.61 1.881 1.41-1.71 2.73-3.489 3.88-5.395-6.01 1.013-12.41 2.318-18.99 3.618 4.17-3.361 8.38-6.618 12.65-9.715 1.01-.057 2.02-.141 3.02-.268 1.31-.164 2.59-.374 3.87-.623 1.27-.238 2.57-.553 3.74-.837 2.4-.583 4.73-1.315 7.04-2.118 1.14-.398 2.29-.832 3.39-1.237 1.07-.396 2.13-.808 3.19-1.22 2.1-.842 4.18-1.673 6.22-2.485 2.1-.827 4.26-1.631 6.36-2.337 1.69-.565 3.32-1.129 5.04-1.77 1.83-.702 3.66-1.431 5.48-2.188 3.64-1.503 7.26-3.102 10.86-4.786 7.18-3.346 14.32-7.021 21.3-10.834 6.89-3.791 13.67-7.762 20.38-11.761 6.7-4.011 13.29-8.044 19.92-12.112 7.73-4.74 15.55-9.06 23.22-13.636l-.01.011zM297.85 185.042c-5.465 2.892-10.971 5.642-16.52 8.33 15.093-11.571 31.117-25.275 47.498-35.787 6.185.381 13.489-.704 20.835-2.536-1.587 1.811-3.216 3.709-4.858 5.605a148.334 148.334 0 00-3.683 1.628c-14.854 6.749-28.87 15.174-43.285 22.758l.013.002zm396.022 87.683c5.76-3.175 14.114-7.855 21.609-13.798 7.557-5.918 14.306-13.09 16.734-21.244-10.987-.143-21.659 1.739-33.416-1.553 7.27 2.036 14.761-8.881 21.004-17.845a309.397 309.397 0 006.764-2.114 477.686 477.686 0 0011.492-3.928c-.094.805-.187 1.61-.236 2.464 5.081 6.095 10.433 6.903 15.494 10.541a195.008 195.008 0 0112.581-4.497c-1.775 1.225-4.195 2.654-6.805 4.17a363.236 363.236 0 00-3.653 2.052c1.486 1.378 2.925 3.084 4.322 5.39-2.4 1.689-4.744 7.041-7.346 8.437.696.101 1.42.193 2.147.273-19.831 11.239-40.158 21.728-60.691 31.652zm233.922 1.047c3.475-1.599 7.061-3.286 10.672-4.968.255.303.512.592.751.906-3.568 2.175-7.43 3.313-11.42 4.049l-.003.013zM2077.84 619.746c-5.99 4.655-13.57 7.942-18.78 11.069-5.96 3.597-12.35 7.382-18.6 11.545.23-.24.46-.493.7-.734 10.98-11.058 22.72-20.724 34.79-29.857 3.84-2.973 7.68-5.949 11.53-8.922.02 7.351-4.16 12.688-9.64 16.899zm593.2-1116.651a1.524 1.524 0 00-.18-.127c.2-.045.4-.09.58-.137-.14.083-.28.179-.4.264zm-98.57 107.486c2.91-1.811 6.46-2.588 10.35-2.696 7.78-.229 16.91 2.149 24.82 3.993 5.15 18.454-3.59 28.32-11.39 32.967l-1.66.854c-5.42 4.748-13.29 4.731-22.21.828 2.18-7.957 6.79-14.146 8.98-20.645-4.47-.673-11.01-5.662-15.37-6.422 1.29-4.278 3.56-7.096 6.48-8.866v-.013zm-246.41-160.079c-6.02 3.25-14.72 8.067-22.54 14.166-7.89 6.074-14.92 13.451-17.43 21.846 11.48.18 22.62-1.725 34.92 1.693-7.59-2.122-15.4 9.115-21.89 18.342-2.37.698-4.73 1.411-7.06 2.168-6.8 2.162-13.51 4.489-20.16 6.908-18.2 6.632-38.23 23.645-59.93 25.55 2.27-3.559 3.75-8.099 4.11-13.992-.4-.491-.82-.944-1.21-1.367 4.78-5.162 8.97-9.623 9.68-9.384-4.9-1.587-9.78-2.025-14.63-1.676a678.32 678.32 0 019.92-6.36c11.32-7.153 22.74-13.98 34.38-20.618 23.37-13.381 47.47-25.697 71.85-37.273l-.01-.003zm-397.38-138.074c-8.84-3.982-20.23 14.254-27.51 21.328-2.45.506-4.88 1.044-7.31 1.636-11.78 2.877-23.33 6.716-34.54 10.668-11.2 4.023-22.04 8.219-32.51 11.648-8.38 2.735-16.73 5.675-25.07 8.769-.11.046-.23.092-.36.135.2-.557.4-1.127.61-1.681 4.46-12.04 10.08-22.385 20.86-21.47-3.14-.239-6.82-.631-10.45-1.352-.53-.098-1.07-.237-1.6-.362 8.29-2.952 16.97-6.251 25.45-9.885 9.75-4.382 19.21-9.183 27.32-14.65 8.1-5.547 14.88-11.635 19.3-18.265-.34-.372-.68-.717-1.02-1.05 18.19-7.698 36.76-14.572 55.71-20.678-2.95 1.131-6.55 2.532-10.41 4.236-3.84 1.749-7.93 3.828-11.85 6.236-7.82 4.817-14.95 10.975-18.05 18.669 10.43 1.619 20.65 1.19 31.43 6.081v-.013zM1544.06-951.02c2-1.251 4.43-1.801 7.09-1.893 5.32-.183 11.57 1.451 16.98 2.728 3.49 12.785-2.51 19.649-7.85 22.874l-1.13.601c-3.73 3.306-9.1 3.303-15.19.609 1.5-5.521 4.68-9.818 6.18-14.34-3.05-.467-7.52-3.915-10.51-4.424.89-2.963 2.46-4.927 4.45-6.152l-.02-.003zm-168.28-110.58c-4.13 2.26-10.09 5.62-15.46 9.86-5.4 4.24-10.22 9.35-11.95 15.18.57.01 1.13.01 1.7 0-6.25 4.75-12.43 9.77-18.3 13.82-6.86 4.69-13.24 8.16-18.92 8.05 2.4-3.88 5.57-7.05 9.11-9.88 3.52-2.83 7.39-5.37 11.02-8.13-4.51-1.04-8.89-.4-13.24 1.1 2.27-1.35 4.54-2.7 6.83-4.01 16.02-9.33 32.52-17.91 49.22-25.99h-.01zM115.421-757.057c1.22-12.81 18.167-26.035 31.387-34.31l2.772-1.661c9.901-7.078 21.191-11.597 32.267-13.404-6.726 7.998-16.184 15.899-22.311 22.686 6.137-2 13.291-1.536 19.258-3.419-15.339 17.537-42.415 24.333-63.358 30.098l-.015.01zM84.363-571.111a240.568 240.568 0 00-5.36 4.381c-5.184 1.285-10.759 2.647-16.154 4.353 7.645-3.678 15.172-6.866 21.499-8.723l.015-.011z"/><path d="M351.639 292.726c9.98 1.077 23.014-.236 32.712-5.262l7.825 1.437c6.56-5.846 10.78-12.309 11.338-21.076-22.829-9.948-50.038-1.842-51.875 24.901zM428.695 367.155c-10.973.222-27.016 2.654-29.988 15.022-.637 2.66 1.845 4.558 4.277 4.6 6.545.11 12.215-1.841 17.942-4.766 1.07-.545 1.617-1.321 1.799-2.163 3.589-1.121 6.931-3.755 9.375-6.272 2.559-2.63-.127-6.507-3.389-6.432l-.016.011zM432.961 276.238c.516.715 1.335 1.243 2.466 1.396 3.37.444 6.32-.497 9.498-1.437 4.432-1.302 2.601-7.408-1.398-7.508-.148-1.618-1.185-3.089-3.145-3.355-2.077-.287-4.103-.119-5.9 1.006-1.064.654-1.902 1.767-3.038 2.3-1.946.91-3.175 2.827-2.371 4.983.552 1.477 2.235 2.662 3.888 2.615zM755.474 394.459c-10.147-2.443-19.612 3.516-26.411 10.369-.43.285-.754.656-1.008 1.082-1.528 2.159-.111 5.6 2.633 5.996 3.597.526 6.771.057 9.499-1.221 6.633-1.694 12.802-4.969 17.593-9.844 2.206-2.251.43-5.718-2.319-6.384l.013.002zM728.416 453.719c-11.462-2.549-21.876 2.629-32.082 6.968-4.709 2.007-2.207 8.64 2.737 7.337 1.94-.506 3.892-.93 5.86-1.296-.119 2.189 1.574 4.643 4.28 4.439 4.92-.364 9.738-1.569 14.695-1.696 3.509-.097 4.265-3.773 2.753-6.032.271-.449.507-.931.671-1.454.584-.243 1.17-.5 1.756-.756 3.554-1.585 3.357-6.608-.659-7.494l-.011-.016zM1240.27 535.417c.33-.128.65-.257.99-.385 1.27-.493 2.01-1.355 2.33-2.307 2.42-1.51 4.7-3.275 6.81-5.342 2.4-2.336.09-6.534-3.08-6.442-.53-1.97-2.55-3.704-5.19-3.124-2.07.443-4.11.972-6.11 1.56-1.99.403-3.98.739-6 1.096-1.37.248-2.31 1.019-2.85 1.985a90.882 90.882 0 00-29.56 19.4c-2.11 2.066-.55 5.979 2.32 6.383 13.8 1.928 26.02-4.792 38.08-10.261 1.19-.536 1.91-1.494 2.24-2.539l.02-.024zM1151.41 539.453c-10.63-3.662-21.78.167-29.38 7.857-2.58 2.625.15 6.511 3.39 6.431 1.61-.041 3.2-.222 4.76-.501.31.273.68.517 1.15.697 8.47 3.348 16.54-1.749 21.39-8.446 1.33-1.833 1.33-5.148-1.3-6.048l-.01.01zM1663.13 637.813c-.71-1.424-2.21-2.547-3.94-2.476-1.08.046-2.13.148-3.19.264-3.41-.261-6.78.049-10.11.786-.13-.981-.59-1.915-1.34-2.538-.78-1.194-2.08-2.039-3.62-1.851-2.65.309-5.15.996-7.51 1.99-4.75 1.461-9.03 4.314-13.11 7.556-1.72 1.369-1.83 3.383-1.07 4.952a4.161 4.161 0 00-.01 1.953c-3.86 2.201-7.63 4.341-11.26 6.047-1.46.689-2.35 2.331-2.45 3.903l-.06.029c-3.85 1.733-3.01 8.21 1.73 7.556 5.04-.704 10.07-2.208 14.92-4.269.83.408 1.84.528 2.98.117 9.73-3.485 18.47-8.672 27.25-13.869a101.93 101.93 0 0010.21-4.501c2.42-1.226 2.07-3.933.6-5.631l-.02-.018zM1592.74 694.281c-3.2.895-6.41 1.815-9.51 2.984-1.12.429-2.19.932-3.23 1.51-2.08 1.141-3.19 3.472-3.05 5.761.15 2.629 2.54 5.171 5.24 5.425 1.07.103 2.03.129 3.06-.017 2.36-.323 4.69-.906 6.97-1.538 1.44-.41 2.52-1.438 3.17-2.694l1.45-.421c3.11-.871 4.61-4.626 3.54-7.477-1.17-3.141-4.52-4.417-7.65-3.536l.01.003zM1677.82 701.397c-5.08.767-14.47 1.752-15.63 8.076-.02.144-.02.253-.04.383-3.82 1.106-7.85 1.739-12.13 2.867-7.41 1.956-3.27 12.947 4.1 11.011 4.34-1.143 8.83-1.573 13.17-2.798 6.11-1.722 11.16-5.58 15.59-9.888 3.64-3.537-.06-10.403-5.08-9.653l.02.002z"/></g></g><defs><clipPath id="prefix__clip0_58_864"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath><clipPath id="prefix__clip1_58_864"><path fill="#fff" transform="rotate(-170.647 1287.35 378.959)" d="M0 0h3044v2003H0z"/></clipPath></defs></svg>'
            },
			'mask-9': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1023.23 665.88c2.15 1.805 2.54 3.894 1.31 4.269l-.21-.139c-2.72-2.451-2.19-4.012-1.58-5.773.25-.709.5-1.45.56-2.294-1.33.415-1.35-.341-1.39-1.411-.04-1.09-.1-2.506-1.55-3.339.92 4.265-.51 6.351-2.62 1.656.6-.842.87-1.823 1.11-2.687.43-1.537.76-2.701 2.58-2.044-.43-.404-.65-.867-.86-1.329-.17-.36-.34-.719-.61-1.048-1.02-.919-1.57-.224-2.05.377-.57.71-1.03 1.288-2.02-1.094.97-1.371 2.52-1.358 4.08-1.345 1.76.014 3.51.029 4.42-1.977-.17-.809-.82-1.484-1.46-2.159-.75-.79-1.5-1.58-1.49-2.58 1.12 1.257 1.62 1.058 2.12.859.57-.228 1.14-.456 2.65 1.509.01.3 0 .607-.02.915-.04.944-.09 1.89.59 2.631 1.74-.254 4.48-4.026 2.17-7.099 1.16 1.426 1.76 1.538 2.36 1.651.6.112 1.2.224 2.36 1.642.17 2.236-.8 1.832-1.76 1.429s-1.92-.806-1.75 1.431c1.22.647 2.05.914 2.76 1.143 1.58.512 2.57.83 5.93 4.751.06-1.334.52-1.74.98-2.13-.81-1.686-1.02-1.728-1.43-1.81-.32-.063-.76-.149-1.69-1.029.16-1.582-.42-2.964-1.35-4.249.02-1.845.71-2.14 1.4-2.436.64-.275 1.28-.551 1.39-2.076-1.22-1.957-2.23-1.679-2.92-1.49-.69.19-1.06.291-.98-2.046.4-.037.85-.1 1.34-.168 2.5-.347 5.93-.822 7.68 1.345-1.72-1.415-1.83.86-1.87 3.298 2.65 2.003 4.51 1.126 6.18.339 1.52-.72 2.89-1.365 4.56.338.21-1.604-.65-2.896-2.24-3.992-.2-3.435.93-3.831 2.06-4.227.24-.083.47-.166.7-.277 5.3 4.936 8.27 1.793 11.47-1.598.36-.379.72-.761 1.09-1.135.15.85-.22 1.328-.58 1.78-.53.672-1.02 1.29.34 2.993 1.81 1.66 2.22.19 2.63-1.278.32-1.155.64-2.308 1.64-1.936.28.564.37 1.186.47 1.807.17 1.17.35 2.34 1.75 3.116l.07-1.243.12-2.551c3.14 2.731 3.67-.271 4.21-3.264.36-2.041.73-4.077 1.91-4.288 4.82 5.089 8.03-.116 5.18-5.37-.21.029-.42.574-.64 1.16-.48 1.263-1.04 2.718-1.87-.368 1.21.573.71-.092-.03-1.082-.78-1.047-1.84-2.456-1.44-3.153 1.12.711 2.16 1.258 3.21 1.804 1.05.551 2.1 1.102 3.23 1.821.28 4.415.24 4.482-.6 5.984-.15.258-.32.559-.51.931 5.32 2.066 9.49-.382 9.27-4.784.21 2.017 4.08 3.386 4.84 3.175-1.43-3.638.24-3.048 2.04-2.408 1.31.463 2.69.952 3.02-.119-.72-1.443-1.5-7.511.24-6.64.19.473.36.951.52 1.428.73 2.058 1.46 4.11 3.37 5.79.08-2.699 1.25-2.012 2.44-1.307 1.32.781 2.68 1.584 2.64-2.19-1.25.399-1.52-.726-1.88-2.22-.13-.542-.27-1.133-.47-1.717.68.035 1.42-.135 2.13-.299 1.94-.446 3.67-.844 3.52 3.097 1.07-3.777 3.97-8.051 9.2-5.989.02-1.266.05-2.551.08-3.829 1.76.606 1.65 1.851 1.54 3.097-.09 1.033-.18 2.067.79 2.737 1.19-.894 1.82-2.156 2.42-3.366.89-1.81 1.73-3.505 4.32-3.683-1.24-1.381-1.65-1.049-2.05-.718-.4.331-.8.661-2.04-.726 1 .125 1.93-.69 2.86-1.499 1.44-1.271 2.88-2.527 4.57-.1-1.12-.835-3.23 1.118-1.68 2.471.51-.164 1.09-.177 1.68-.19 1.64-.037 3.3-.074 3.31-3.435-.38-.085-.73-.049-1.05-.016-.94.096-1.69.172-2.19-2.642.7.237 1.23.048 1.68-.113.78-.277 1.33-.471 2.13 1.722.07.24.16.499.26.779-.09-.288-.18-.547-.26-.779-.87-2.74-.22-2.954.59-3.216.53-.173 1.13-.368 1.39-1.336-.65-.422-1.2-.674-1.76-.926-1.1-.497-2.2-.991-3.95-2.793.14 1.187-.17 1.454-.49 1.721-.37.31-.73.621-.38 2.377-4.31-5.017-6.28-3.22-8.36-1.313-1.93 1.761-3.96 3.614-8.02.283-.33 2.099-1.8 1.83-3.27 1.561-1.69-.307-3.37-.615-3.35 2.613-5.25-.286-10.27-.146-13.97 2.785-.33-.858-.89-1.648-1.45-2.441-.71-.998-1.41-1.999-1.67-3.143-.36.223-.9.05-1.45-.122-.95-.299-1.89-.598-1.87 1.163-2.04-1.465-3-3.265-3.97-5.067-.47-.877-.94-1.755-1.53-2.594-.01.486-.04.974-.06 1.461-.11 2.062-.21 4.104.65 5.853-2.51.369-5.18-2.349-6.37-3.55l-.06-.066c.06.478.12.917.18 1.315.29 1.919.44 2.888-1.14 2.735-.78-2.036-.54-3.218-.34-4.225.24-1.155.42-2.079-1.03-3.795-.07.133-.16.283-.24.441-.89 1.611-2.25 4.091-4.63-.781.03 1.45-.44 1.826-.92 2.204-.33.262-.66.525-.83 1.147 1.68 2.275 2.72 4.776 2.17 7.753-1.3-1.136-1.31-1.729-1.32-2.392-.02-.725-.04-1.533-1.74-3.224-.53 2.365-1.92 1.909-3.53 1.385-1.26-.413-2.65-.868-3.85-.021.09-.818.19-1.465.27-2.008.35-2.244.41-2.691-1.6-6.024 1.08 1.063.99-.437.9-1.935-.06-.868-.11-1.736.07-2.103 1.92 1.104 3.04.46 4.16-.185.24-.137.48-.274.72-.394.45 1.541-.08 2.045-.52 2.456-.47.452-.83.791.37 2.277-.06-2.195 1.23-1.612 2.36-1.099 1.29.584 2.38 1.075 1.02-2.76 4.31 2.802 6.4 3.153 10.02 3.763.4.067.81.137 1.25.213.12-1.598-.07-3.076-.66-4.454.96.724 1.88 1.345 1.64-.521 1.57.537 1.54 1.576 1.5 2.616-.03.854-.06 1.708.8 2.283 2.67 1.333 2.83-1.469.97-4.978 1.22.63 1.72 1.479 2.21 2.33.43.733.86 1.467 1.75 2.06.97-.228 1.44-.952 1.86-1.611.82-1.29 1.48-2.329 5.48 1.097.02-.842-.39-1.536-.81-2.231-.53-.882-1.05-1.764-.7-2.95l.79.173c2.45.539 2.69.593.05-2.365.48-.602 1.51-.046 2.53.511.9.482 1.79.964 2.32.695 1.28-4.35 4.92-3.283 8.29-2.294 1.78.522 3.48 1.022 4.72.69-.02-.805.01-1.627.03-2.448.11-3.014.21-6.022-2.33-8.13.02.886.22 1.684.4 2.367.3 1.19.51 2.029-.46 2.372-1.31-.496-1.45-1.393-1.59-2.293-.15-.898-.29-1.798-1.59-2.302-.44.103-.88.207-1.31.312-6.07 1.457-12.14 2.914-19.71 1.177-.38-2.587.42-2.687 1.22-2.788.53-.066 1.06-.132 1.25-.904-.64-1.852-1.38-2.459-1.99-2.952-.66-.532-1.15-.929-1.15-2.618 1.1.902 1.75 1.947 2.37 2.976.47-1.306.64-2.466.79-3.437.23-1.609.39-2.701 1.71-3.087-1.1 1.266-.55 2.194.18 3.431.56.94 1.22 2.06 1.35 3.643-.33-.235-.7-.555-1.07-.875-.98-.842-1.94-1.676-2.14-.893.43.781.66 1.625.89 2.469.41 1.471.81 2.939 2.24 4.058-1.7-3.603-.95-3.426.15-3.162.95.227 2.17.518 2.33-1.488-2.38-1.933-1.13-5.279-.77-6.235.02-.052.04-.097.05-.134 1.8.331 3.88-.975 6.06-2.349 3.32-2.081 6.88-4.319 10.09-1.253-.13-.868-.69-1.594-1.24-2.318-.78-1.028-1.57-2.055-1.14-3.485 4.55 4.743 5.44 4.692 7.4 4.578.42-.024.89-.052 1.46-.034-.71.686-.56 1.899-.43 2.904.15 1.206.26 2.112-1.21 1.437-.08-1.221-.23-2.435-1.59-3.23-.03.457-.15.936-.29 1.459-.34 1.33-.76 2.942.24 5.176.17-.737 1.04-.974 2.03-1.246 2.25-.617 5.15-1.411 2.06-8.665 1.06.386 1.75.015 2.43-.357.68-.373 1.36-.747 2.44-.348.38 1.885.02 2.911-.26 3.727-.37 1.063-.62 1.772 1.05 3.56 1.39 1.021 1.53-.453 1.68-1.928.14-1.439.28-2.879 1.57-2.006-2.64-4.128-1.01-4.93.63-5.738 1.31-.649 2.64-1.301 1.76-3.68 1.13 1.649 1.13 2.167 1.14 2.655.01.368.01.718.49 1.519 1.97 1.118 4.17.69 2.4-2.789 1.69 1.596 2.15 1.301 2.51 1.065.4-.256.68-.442 2.32 1.913 1.16-1.798 2.21-3.789-.07-7.634.73-.898 1.43.464 2.1 1.78.68 1.313 1.33 2.58 1.96 1.514-1.65-2.861-2.07-5.568 1.55-4.422.7.572.81 1.365.91 2.157.1.728.2 1.456.76 2.011.99-.39.61-1.125.05-2.178-.34-.647-.74-1.414-.92-2.295.8.753 1.25.829 1.7.906.42.072.84.144 1.55.769 1.29 1.908.61 2.163-.04 2.41-.65.247-1.28.485.1 2.361 3.28 1.153 4.08-3.339.7-7.919 3.44 3.833 8.11 6.599 4.82.107 1.01-.353 1.39.386 1.93 1.441.33.651.72 1.421 1.36 2.129 1.67-2.05 3.53-2.64 5.32-3.207 2.22-.705 4.33-1.375 5.85-4.766 1.61.79 2.21 1.948 2.81 3.105.37.708.74 1.416 1.34 2.04.66-.929.92-2.268 1.14-3.425.44-2.255.74-3.816 3.64-.286-.11-2.281.76-2.695 1.48-3.033.55-.261 1-.476.84-1.466.3 1.073 1.36 1.819 2.43 2.576l.01.007c-.36-2.801 2.68-11.604 6.28-4.483.05-1.86.43-3.477.78-4.997.49-2.108.93-4.03.42-6.151 3.42 3.27 6.03 1.726 8.01.552 1.92-1.134 3.25-1.923 4.17 2.287 1.47.575 1.32-.597 1.18-1.671-.17-1.22-.31-2.312 1.96-.566 1.3 1.121.71 1.304.05 1.513-.77.241-1.65.516.11 2.302 2.58.693 6.94-.096 2.97-6.054-.3 1.288-3.39 1.956-4.05-.301 1.03-.033 2.02-.271 3.02-.509 2.38-.57 4.77-1.14 7.51 1.103.45-1.145-.24-1.836-.92-2.527-.69-.693-1.37-1.387-.92-2.539 2.42 1.624 2.6 1.034 2.83.322.15-.477.31-1.009 1.18-.963-2.07-1.442-4.04-2.915-5.07-4.755-.6 2.408-1.58 4.156-5.64.328.12-1.271 1.04-1.107 1.96-.943 1.05.188 2.11.375 1.95-1.601 1.51.619 2.98 2.174 4.22 3.488 1.63 1.732 2.87 3.046 3.28 1.246.03-1.334-.49-2.427-1-3.516-.51-1.088-1.02-2.172-.99-3.489.56.421 1.13.841 1.67 1.253.02-.557.06-1.049.78-.342.05 2.925.31 5.759 2.05 7.954 1.11-1.642 1.62-.235 2.34 1.73.31.851.65 1.807 1.1 2.666 1.78.363 1.53-1.053 1.23-2.776-.21-1.248-.45-2.657.02-3.668 4.31 2.296 9.58.496 5.2-7.191 2 1.274 2.56 3.172 3.13 5.069.47 1.586.94 3.172 2.25 4.39-.46-1.285-.68-2.672-.91-4.056-.27-1.681-.54-3.359-1.23-4.851 2.73 1.062 4.81.991 4.64-3.004 1.39.873 1.75 2.194 2.11 3.516.45 1.609.89 3.22 3.19 4.024-.14-2.253-.28-4.481-.42-6.702 1.87 3.266 2.93 3.189 4.33 3.087.85-.061 1.81-.132 3.16.522-.05-.55-.28-1.024-.52-1.498-.29-.607-.59-1.213-.5-1.978 3.93 1.438 7.83.123 5.16-7.18 2.78 3.943 5.76 1.95 3.92-1.722 3.81 3.723 4.53 2.202 5.26.681.53-1.103 1.05-2.205 2.76-1.308-1.1-2.014-.85-1.967-.37-1.875.34.064.79.15.99-.412-1.16-.778-1.89-1.769-2.62-2.758.12-.403.21-.871.3-1.339.3-1.653.6-3.3 2.64-2.026.07.96-.34 1.481-.71 1.947-.64.798-1.14 1.433 1.18 3.831.84-1.883 3.27-1.115 5.7-.345 2.24.711 4.49 1.424 5.5.047.7-1.967.18-4.111-2.18-6.949-2.75-1.215-2.07.582-1.39 2.359.6 1.572 1.2 3.128-.6 2.569-.07-2.44-1.38-4.299-2.7-6.156-.8-1.141-1.61-2.282-2.13-3.556 2.56 3.252 3.9 2.948 5.33 2.624.64-.145 1.3-.295 2.1-.131.09.288.17.577.26.866.8 2.621 1.59 5.244 4.42 6.917.14-.548-.29-1.583-.7-2.564-.61-1.453-1.17-2.787.29-2.233.51 1.421 1.09 2.806 1.95 4.063.57-.605 1.06-1.351 1.54-2.096.64-.974 1.27-1.945 2.1-2.591 3.03 3.076 4.82 1.687 6.66.252 1.32-1.027 2.67-2.078 4.53-1.534-.73-.649-1.01-1.51-1.28-2.371-.19-.583-.37-1.166-.7-1.684.27.009.57.089.88.17 1.08.282 2.16.565 1.36-2.222 4.15 4.274 4.61 2.198 5.12-.142.24-1.055.48-2.165 1.08-2.768.56 1.644 1.11 1.676 1.7 1.71.57.033 1.18.068 1.86 1.614.77 3.41.18 4.605-1.21 4.529 4.76 5.79 7.43 1.939 7.92-1.8-.81-.344-1.21-.024-1.6.296-.61.489-1.22.978-3.3-.91.9-1.62 2.17-2.645 3.44-3.67 1.85-1.493 3.7-2.985 4.41-6.314-.26-1.209-1.45-1.931-2.66-2.666l-.04-.024-.5-4.81c1.49.758 2.11 1.961 2.73 3.165.63 1.206 1.25 2.414 2.75 3.178 2.56.969 2.62-.412 2.67-1.579.05-1.195.09-2.165 2.79-.161-1.67-4.963-6.14-5.013-6.82-3.634-.77-2.55.46-2.52 1.51-2.494 1.04.025 1.88.046.34-2.506 1.86 1.319 2.85 3.078 3.83 4.834 1.22 2.19 2.45 4.376 5.37 5.706.02-1.25.9-2.1 1.83-3.001 1.96-1.899 4.16-4.025-.9-10.611-.27 3.265-1.75 4.65-3.4 5.759-1.07-1.766-.64-2.169-.26-2.521.42-.396.78-.728-1.14-2.852 2.24.48 3.87-1.008 5.38-2.38 1.52-1.381 2.91-2.644 4.65-1.655-.93-.429-1.21.137-1.5.707l-.03.061c1.05.857 1.82 1.06 2.37 1.208.93.244 1.27.334 1.33 2.997.07 1.596-.62.367-1.3-.862-.69-1.232-1.37-2.465-1.31-.855-.19 2.51.41 2.666 1.17 2.862.6.155 1.3.335 1.77 1.726-1.32.001-1.05 1.172-.75 2.482.34 1.458.72 3.087-1.02 3.462 2.32 1.64 3.13.925 3.95.208.26-.237.53-.474.86-.627-.13-.497-.32-1.073-.51-1.66-.72-2.198-1.49-4.548.74-3.509.34.619.6 1.284.85 1.948.5 1.318.99 2.636 2.22 3.602.09-.169.19-.333.28-.495.75-1.263 1.46-2.442-.93-5.253-.43 1.112-1.47-.003-2.53-1.137-1.04-1.111-2.09-2.24-2.6-1.322-.88-3.24.77-2.556 2.42-1.872.89.367 1.77.734 2.27.498-.09-.412-.13-.843-.18-1.274-.12-1.163-.24-2.328-1.14-3.116-.32 1.491-1.74 1.284-3.16 1.078-.26-.038-.52-.075-.77-.102-1.16-2.542-.81-2.634-.34-2.759.44-.116 1-.26.56-2.4-1.34-.112-2.23.473-3.11 1.056-.88.583-1.77 1.165-3.1 1.054-.06-.295-.1-.602-.15-.91-.13-.949-.26-1.901-1.04-2.518-1.23.947-2.06 1.731.54 4.773-3.24-.622-5.56-.124-5.33 2.683-.67-2.888-2.74-1.849-1.53.768-1.19-.754-1.95-1.715-2.7-2.68-.6.633-.86 1.804-.34 4.212-4.6-3.784-5.25-1.357-5.9 1.088-.09.349-.19.699-.29 1.031-.17-1.012-1.04-1.676-1.9-2.339-.73-.562-1.46-1.124-1.77-1.897.27 3.36-2.01 3.49-4.15 3.613-2.43.138-4.68.266-2.93 5.009-2.57-2.368-2.65-.742-2.73.886-.03.596-.06 1.193-.21 1.594-1.5-.637-2.36-1.595-3.21-2.554-.61-.683-1.22-1.366-2.06-1.935 1.65 4.251-1.11 3.581-3.95 2.889-1.96-.475-3.96-.961-4.61.133.18.805.61 1.486 1.04 2.163.38.605.75 1.207.95 1.895-1.87.053-2.56.349-3.88.912-.44.189-.95.408-1.6.659-.78-1.123-.67-1.833-.57-2.519.14-.897.27-1.753-1.59-3.437 2.1 8.643-3.02 8.69-7.23 8.729-1.59.014-3.05.027-3.95.498l.45.578c1.19 1.496 2.38 2.998 2.44 5.033-1.63-.965-2.28-.295-2.93.374-.47.483-.94.964-1.77.832-.31-2.516-.87-4.893-2.2-6.947-3.17.452-5.81 2.472-8.44 4.491-4.23 3.244-8.45 6.484-14.89 3.207.13.008.27.021.4.034 1.07.101 2.14.202 1.86-2.002-1.26-.513-1.83-1.321-2.4-2.126-.49-.695-.98-1.388-1.91-1.888 1.05 4.574-.94 3.17-2.68 1.948-.9-.641-1.74-1.233-2.03-.886-.21 1.228.2 2.191.62 3.159.41.966.83 1.936.64 3.178-2.35-2.561-3.18-2.695-4.16-2.852-.47-.075-.97-.155-1.68-.514-.08 1.122.5 1.948 1.08 2.779.3.425.59.851.81 1.319-.96-.343-1.64.552-2.3 1.411-.9 1.186-1.75 2.303-3.2-.007.24-.367.67-.397 1.09-.427.82-.058 1.64-.116 1.11-2.494-2.33.263-2.94-.848-3.91-2.651-.64-1.168-1.43-2.626-2.95-4.189-.41 2.043.5 3.516 1.41 4.984.21.337.42.673.61 1.016-1 1.897-4.13 2.065-7.25 2.232-1 .054-2.01.108-2.94.218 1.35 2.35 1.07 3.571.75 4.919-.18.786-.37 1.616-.26 2.738-1.36-.686-1.71-1.81-2.05-2.933-.36-1.186-.73-2.373-2.29-3.047.79 4.074.12 5.574-.56 7.083-.18.4-.35.801-.51 1.251.68.67 1.17.756 1.54.823.44.077.72.127.96 1.041-2.61-1.542-1.4 2.908.29 4.795-.98-.484-1.45-.064-1.92.356-.6.533-1.2 1.066-2.84-.252-.71-2.342-.07-2.278.57-2.214.38.038.76.076.86-.381-2.73-1.411-1.45-2.974-1.01-3.493-1.86.687-3.93.198-6.19-2.628 1.81 2.113 1.82-1.908.33-2.68-2.43-.841-3.14 1.389-3.84 3.622-.51 1.601-1.02 3.203-2.16 3.675-.03-.428-.04-.866-.04-1.303-.02-2.141-.04-4.277-2.71-5.349 1.53 5.1 1.41 12.137-2.77 7.927 1.03 1.022 2.7-.583.67-2.236-.54.243-1.13.404-1.71.565-1.49.409-2.98.818-3.73 2.582-1.89-1.166-3.17-2.595-4.18-4.135-.47 1.369-.36 1.849-.15 2.802.14.631.33 1.469.42 2.911-.8-.731-1.25-.798-1.7-.864-.41-.063-.83-.125-1.54-.732-.59-.459-.79-1.079-.99-1.697-.17-.532-.34-1.064-.76-1.492-4.04-.832-5.3 1.843-6.59 4.582-.7 1.48-1.41 2.979-2.57 3.952-.95-2.839-2.02-5.649-3.5-8.289-.33.511-.84.669-1.36.827-.97.3-1.95.599-1.64 3.292.94.672 1.52-.007 2-.57.65-.754 1.12-1.302 2.02 1.869-1.07 1.928-4.17-.096-7.97-2.576l-.04-.027c-.11 2.687.8 3.109 1.85 3.597.83.389 1.76.82 2.33 2.475-4.84-1.308-7.24 1.867-9.38 4.696-.76 1.015-1.5 1.986-2.3 2.688-.37-1.713.17-1.697.71-1.68.42.013.84.025.82-.791-1.25-.659-1.66-1.627-2.08-2.593-.41-.964-.82-1.926-2.06-2.58-.12 6.424-3.77 6.127-7.42 5.829-2.26-.184-4.51-.367-5.93 1.032.42-2.083-.41-3.687-2.5-4.817.22 3.722.12 6.866-1.41 7.235-.19-1.187-.91-2.178-1.62-3.166l-.05-.069c-.94 2.003-2.69 2.446-4.43 2.889-2.11.538-4.23 1.076-4.92 4.405.01-1.611-.68-2.957-1.67-4.178-3.23 3.181-4.44 2.821-6.9 2.093-1.06-.313-2.35-.694-4.13-.891.18-1.986-.25-3.739-1.66-5.138.12 2.647-.65 1.887-1.47 1.085-.71-.7-1.46-1.431-1.67.035-.23 1.967.16 3.734.85 5.386-1.5-1.133-2.67-1.634-3.19-.812.01.819.61 1.439 1.22 2.057.55.568 1.1 1.134 1.19 1.854-1.64-.417-2.44-.896-3.05-1.261-.86-.518-1.34-.806-3.32-.358.36.362.81.699 1.25 1.036 1.27.968 2.54 1.941 1.95 3.543-2.69-2.262-5.15-2.47-7.25-2.647-2.91-.246-5.13-.434-6.29-6.005-1.63-.756-2.4.258-3.17 1.275-.63.828-1.26 1.657-2.35 1.53a6.915 6.915 0 01-.05-.914c-.03-.941-.06-1.882-.76-2.601-.55 1.651-1.61.707-2.71-.27-1.04-.923-2.11-1.875-2.81-.691.43 1.919 1.41 3.654 2.38 5.392.96 1.704 1.91 3.41 2.36 5.294-3.15-4.614-8.78-6.756-6.32.23-8.79-.815-16.66.215-24.5 1.241-5.46.714-10.91 1.427-16.64 1.514-.29-2.226.64-1.827 1.57-1.429.77.328 1.53.656 1.62-.477-.64-1.335-.49-1.869-.35-2.336.16-.58.3-1.058-1.14-2.832-.01.666-.41.516-.81.367-.34-.128-.67-.256-.77.134.08.506.23.991.37 1.476.29.936.58 1.874.33 2.967-1.54-.7-1.87-1.771-2.21-2.841-.2-.633-.4-1.265-.85-1.819-.34 0-.71-.062-1.09-.124-.76-.126-1.52-.253-2.06.142.36 1.449.78 2.881 1.5 4.22 2.6 1.84 4.04 3.974 5.47 6.084.48.703.95 1.404 1.47 2.091-.81-.599-1.2-.287-1.59.023-.5.395-1 .789-2.35-.689-.04-.467 0-.959.04-1.451.12-1.427.23-2.857-1.52-3.72-.88 1.335-2.68.639-4.48-.056-2.41-.928-4.81-1.855-5.01 2.043-2.37-3.225-2.73-3.108-1.58.485-1.92-2.312-2.78-2.279-3.64-2.246-.32.012-.65.025-1.02-.088.26-1.32.93-1.764 1.6-2.207.71-.477 1.43-.954 1.65-2.527-3.46-3.474-4.72-2.084-5.97-.694-.74.819-1.48 1.638-2.67 1.463-1.11-3.009-2.15-2.799-3.23-2.583-.7.141-1.41.284-2.16-.444-.41-1.217-.46-2.546-.51-3.876-.08-2.063-.15-4.128-1.59-5.78.15 3.088-1.05 3.181-2.25 3.274-.67.052-1.337.105-1.774.677.107 2.513.243 2.956.564 3.989.19.615.44 1.44.79 3.038-1.985-2.375-2.507-2.781-3.547-3.59a43.147 43.147 0 01-2.567-2.138c.106-1.942-.279-2.903-.937-4.547-.33-.825-.729-1.822-1.17-3.201.354-.328.898-.231 1.442-.133 1.064.191 2.129.382 1.773-2.607 3.634 2.875 5.656 2.371 7.866 1.82 1.5-.375 3.09-.772 5.33-.147-.6-4.907 1.33-4.242 3.25-3.577 1.25.428 2.49.856 3.05-.209-.93-.961-1.81-1.95-1.51-3.28 1.13 1.237 1.59 1.04 2.06.842.53-.225 1.06-.451 2.58 1.481.27.669.43 1.372.59 2.076.37 1.629.74 3.261 2.43 4.486 0-.539.21-.644.42-.75.15-.081.31-.161.38-.435.42-1.85-.34-3.332-1.1-4.814-.76-1.48-1.52-2.96-1.1-4.805 2.89 2.625 3.12 2.242 3.14-1.894 1.07.93 1.7 1.985 2.31 3.053.29-.408.4-1.199.51-1.989.16-1.189.33-2.378 1.1-2.262 2.28 4.306.69 4.52-1.01 4.749-.86.117-1.75.236-2.17.896.61.718.81 1.563 1.01 2.406.29 1.196.57 2.388 2.03 3.211.27-1.731 1.15-2.173 2.02-2.615.82-.418 1.64-.835 1.96-2.34.87.185 1.57.941 2.09 1.499.78.844 1.13 1.231 1.04-1.516.87.604 1.29 1.341 1.71 2.077.49.856.98 1.711 2.16 2.35-.31-1.964.03-2.557.36-3.15.24-.423.48-.847.48-1.769.49.344 1.11 1.214 1.76 2.106 1.17 1.621 2.39 3.315 2.91 2.063-.16-.219-.32-.438-.49-.656-1.93-2.563-3.85-5.121-3.35-8.435 2.18 3.591 2.83 3.652 3.51 3.715.66.062 1.34.126 3.49 3.481-.57-3.236-.37-3.531.06-4.171.22-.329.49-.749.76-1.707-.76-.596-1.47-.313-2.16-.035-1.07.431-2.11.85-3.31-2.012 1.04-.994 2.66-.778 4.29-.562 1.26.168 2.52.337 3.51-.062-1.96-3.741.33-4.06 2.69-4.389 1.48-.207 2.99-.418 3.5-1.483-1.02-.692-2.3-1.179-3.67-1.696-3.45-1.312-7.4-2.813-8.78-8.309-1.04-.293-1.43.744-1.53 2.408 1.34 1.132 1.99 2.479 2.34 3.953-2.38-1.033-1.93.897-1.46 2.908.43 1.854.88 3.777-.86 3.508.13-2.222.15-4.434-1.54-6.07-.92-.364-1.77-.583-2.34-.221l-.02 3.736c-1.1-.906-1.91-1.205-2.72-1.503-.82-.299-1.63-.598-2.73-1.508-.49-2.652.1-2.995.7-3.338.46-.262.91-.524.88-1.813-1.45-1.168-2.6-1.703-3.11-.922 0 .685-.01 1.367-.01 2.048 0 1.193-.01 2.38-.01 3.572 1.1-.057 1.89.693 2.31 3.028-2.54-1.39-4.23-.984-5.93-.578-.84.202-1.69.405-2.64.384 1.85 8.947-2.54 7.467-6.77 6.044-2.91-.984-5.75-1.939-6.39.59.13-2.484-.72-4.652-1.59-6.835 4.19 3.064 10.46 7.462 8.54.227 3.07 3.386 4.11 2.392 5.14 1.403.1-.088.19-.176.28-.261-.9-1.615-1.52-1.762-2.11-1.901-.72-.172-1.4-.332-2.5-3.221.79 3.888-.83 2.838-2.23 1.932-1.4-.904-2.58-1.667-.92 2.754-2.85-3.147-4.1-2.835-5.35-2.523-.87.217-1.73.434-3.14-.512.05-.738.22-1.162.36-1.5.28-.696.41-1.02-1.08-2.954-.13 1.945-2.01-1.122-2.8-2.4-.11-.182-.2-.328-.26-.418.96 2.049.55 2.178.01 2.35-.55.171-1.23.385-.84 2.581 1.12-.028 1.88.717 2.28 3.056-1-.595-1.66-.418-2.31-.241-1.05.283-2.1.566-4.593-2.337-.033-.42-.024-.851-.015-1.282.023-1.138.047-2.272-.7-3.176-1.222.308-1.18 1.521-1.133 2.898.038 1.087.079 2.276-.496 3.2-2.059-3.096-3.169-3.348-4.326-3.611-1.185-.269-2.418-.549-4.767-3.901-.588 1.627-2.801.301-4.603-.778-.605-.363-1.164-.697-1.599-.883.328-.699-.863-3.848-1.39-4.541-.497.195-1.538-.413-2.461-.951-1.518-.886-2.716-1.585-.643 1.785-1.718-.522-1.962-1.297-2.334-2.479-.249-.792-.555-1.766-1.401-2.969.148 1.553-.689.896-1.527.24-.712-.558-1.424-1.116-1.534-.323 1.002 2.939 1.397 2.96 2.111 2.999.396.021.891.048 1.643.581.727 1.658.545 1.911.271 2.292-.241.337-.555.775-.379 2.37-1.953-2.433-2.722-2.218-2.335.62-.709-1.596-1.611-2.116-2.42-2.582-1.023-.59-1.897-1.094-2.042-3.585.191.063.539.467.951.947 1.25 1.453 3.089 3.59 2.895-1.063-2.41-1.074-2.985-2.283-3.778-3.952-.488-1.025-1.058-2.224-2.187-3.672.256 2.885-1.216 1.849-2.686.813-1.236-.87-2.471-1.74-2.678-.278-.237-1.68.1-2.052.439-2.424.338-.373.678-.746.441-2.432 2.966 3.461 4.002 2.523 5.035 1.587.095-.086.19-.172.286-.254-.806-1-1.764-3.255-.71-2.578 2.32 3.58 4.794-.828 5.434-4.227.842.623 1.246 1.364 1.652 2.106.47.86.941 1.721 2.091 2.405.486-2.9 3.236-4.348 5.336-.625.64 2.643-.39 1.581-1.419.518-.854-.88-1.707-1.76-1.608-.53.644 1.172.664 2.506.683 3.837.026 1.738.051 3.47 1.469 4.826.771-.227.606-.802.354-1.679-.221-.771-.509-1.777-.288-2.985 1.46 1.517 1.969.926 2.477.334.347-.404.693-.807 1.342-.541 1.222.566 1.357 1.458 1.493 2.349.135.893.27 1.785 1.498 2.35-1.782-4.235-.064-6.52 1.587-6.098-1.648-4.017-1.881-4.931-2.22-7.731 1.549.896 2.486.452 3.424.009 1.079-.51 2.159-1.021 4.172.501.323 1.09-.11 1.404-.475 1.669-.445.323-.79.574.455 2.067.588-1.339 1.432-.874 2.261-.418.915.505 1.811.998 2.322-.959 1.545 2.677 6.505 4.666 6.875 1.655 1.33 1.057 1.85 2.356 2.37 3.653.59 1.478 1.18 2.953 2.97 4.062-1-2.383-.53-3.045-.05-3.726.48-.673.98-1.363.07-3.745 1.17 1.454 1.51 1.14 1.85.826.27-.247.54-.494 1.21.117.01.182.01.366.02.549.03 1.355.07 2.724 1.51 3.644-.09-.99.15-1.297.38-1.603.22-.286.44-.571.4-1.416 1.35.984 1.82 2.229 2.29 3.476.37.981.75 1.964 1.55 2.82-.69-3.759.4-3.759 1.48-3.758.68.001 1.35.001 1.6-.918-2.42-1.7-4.3-2.261-6.16-2.8.67-1.798 2.44-1.29 4.21-.781 1.8.516 3.59 1.033 4.25-.845-1.32-.349-2.78-.978-4.23-1.606-3.15-1.361-6.29-2.717-8.06-1.201.14.144.29.287.44.431 1.08 1.038 2.17 2.091 1.85 3.542a21.806 21.806 0 01-2.15-2.31c-.66-.803-1-1.205-2.45-1.908-.4-.975-.59-2.011-.77-3.045-.19-1.028-.38-2.055-.77-3.018 6.08 4.821 11.47 6.629 10.72-.424.32 2.594 3.53 3.904 2.3.233 1.46.928 1.69 2.246 1.93 3.563.19 1.048.37 2.096 1.17 2.946.14-1.114.89-.954 1.63-.794.56.122 1.13.243 1.42-.202-.45-.482-1.04-.72-1.63-.957-1.2-.479-2.38-.95-2.22-3.427-.29-1.526.62-.544 1.52.438.91.981 1.82 1.963 1.53.437-.81-2.92-1.12-3.786-.98-4.566.1-.518.4-.998.9-2.017-1.91-1.084-2.32-2.65-2.73-4.216-.12-.458-.24-.917-.4-1.364 2.32 2.315 1.66-.403 1.1-2.707-.23-.943-.44-1.817-.43-2.247 1.35 1.141 1.67 2.62 2 4.099.32 1.456.64 2.912 1.93 4.044.52-2.737 2.41-2.6 4.32-2.462l.2.015a3.992 3.992 0 01-.3 1.519c-.26.703-.38 1.032 1.15 2.931.88-.351 1.47.465 2.25 1.547.61.836 1.32 1.83 2.38 2.571-2.26-4.769 1.89-1.419 4.61 1.296.06-.514.14-.977.22-1.409.36-2.144.6-3.554-1.15-6.785 3.27 2.069 4.42 4.837 5.5 7.614-.36-2.432.66-2.787 1.56-3.098.95-.33 1.76-.61.61-3.276.41.177 1.07.849 1.73 1.522 1.13 1.157 2.26 2.315 2.15.96-2.21-4.622-4.5-4.912-6.71-5.194-1.99-.253-3.93-.5-5.7-3.888 1.12.186 2.36.618 3.61 1.05 1.4.488 2.81.977 4.05 1.114-2.75-4.165-3.6-7.294-4.45-10.465-.66-2.433-1.32-4.89-2.86-7.858 2.82 2.87 4.23 1.055 5.84-1.004.89-1.15 1.85-2.376 3.14-2.905-.25 1.206-.22 2.241-.19 3.322.04 1.277.08 2.618-.34 4.383 5.93 3.961 10.98 6.043 12.2.627 3.61 3.397 5.17 2.757 6.74 2.114 1.61-.661 3.23-1.325 7.09 2.411-.38-1.71.13-1.683.64-1.656.41.021.81.042.78-.776l1.32 1.12c.88.744 1.75 1.488 2.63 2.226 1.11-3.726 5.28-2.127 8.99-.702 2.3.881 4.42 1.695 5.54 1.145-.23-.23-.49-.451-.74-.673-.78-.677-1.56-1.355-1.69-2.266.24-.113.34-.643.44-1.177.21-1.107.42-2.227 1.82.355-1.87-1.549.41 2.797 1.69 3.212.2-.841 2.53-.608 4.59-.402 1.43.143 2.73.274 3.09.017.02.805-.33 1.399-.65 1.939-.47.796-.87 1.476.07 2.54 1.67.844 2.85.734 4.03.624.68-.065 1.37-.13 2.16-.002-.48-1.532-1.25-2.961-2.01-4.39-.95-1.765-1.9-3.53-2.31-5.489 3.78 6.5 9.8 15.061 11.18 8.295a7.506 7.506 0 01-1.72-1.849c-.41-.603-.63-.915-1.48-.773-.09-3.584 1.19-4.131 2.6-4.733 1.16-.493 2.4-1.023 3.04-3.288 2.29 2.45 3.44 1.845 4.73 1.169 1.48-.779 3.14-1.653 6.94 1.951-.27-1.535.22-1.673.71-1.811.56-.158 1.12-.315.54-2.571-.23-.22-.49-.432-.74-.644-.79-.659-1.58-1.319-1.74-2.237 2.56 2.221 3.31.852 4.13-.648.78-1.424 1.62-2.967 4.15-1.663.58 2.633-.71 2.987-1.82 3.29-1.04.286-1.92.525-.86 2.608 1.41.476 3.22.005 4.99-.458 3.06-.799 6.02-1.571 6.7 2.599.72-.428 1.35-1.258 2.03-2.154 1.52-1.996 3.3-4.322 6.85-3.293 1.45 1.007 2.48 2.018 3.31 2.821 1.34 1.302 2.11 2.059 3.17 1.367 1.47 1.995 1.45 3.138 1.42 4.213-.02.824-.04 1.608.62 2.706.9.516 1.2-.024 1.5-.571l.02-.025c-.06-.918-.35-1.747-.64-2.581-.41-1.152-.82-2.314-.6-3.735 1.7.95 2.02 2.503 2.34 4.054.23 1.155.47 2.309 1.28 3.212 1.83 1.171 1.39-.537.94-2.289-.47-1.797-.95-3.639 1.01-2.472.01 1.959.9 3.548 2.67 4.744.79-1.465.42-2.252-.19-3.53-.33-.712-.74-1.576-1.06-2.795 1.37 1.601 1.47.95 1.56.299.1-.649.19-1.298 1.56.304 1.05 1.398.75 1.838.48 2.253-.29.422-.56.819.63 2.172 2.18 1.883 1.73-.843 1.29-3.565-.25-1.489-.49-2.977-.3-3.708-1.03 1.117-5.39-1.299-5.83-5.345.75-.038 1.53-.008 2.32.022 2.84.109 5.68.218 6.67-2.912-.17-.175-.36-.344-.54-.513-.55-.491-1.11-.99-1.18-1.68 2.09 1.141 2.7 2.918 3.32 4.697.56 1.635 1.13 3.272 2.84 4.421-.69-2.794.75-1.885 2.19-.974 1.24.784 2.48 1.569 2.37-.005-.7-.838-1.43-1.251-2.16-1.66-1.42-.803-2.82-1.59-3.86-5.554 1.74.973 2.88 2.216 4.03 3.46.58.633 1.17 1.266 1.83 1.864l-.57-6.62c1.9.914 3.02.493 4.15.072 1.17-.438 2.34-.877 4.39.188.52 2.473-.02 1.692-.6.866-.48-.702-.99-1.436-.89-.227.2.889.84 1.586 1.49 2.285.43.471.87.942 1.17 1.474-.22-3.697 1.3-8.133 4.72-7.912 1.24.605 1.7 1.548 2.16 2.49.46.945.92 1.89 2.17 2.491.44-.018.84.02 1.19.053 1.43.133 2.01.187.8-3.844 1.94.822 2.58 2.258 3.21 3.689.33.765.67 1.529 1.21 2.198.96-.631 1.67-1.665 2.39-2.698.12-.188.25-.375.38-.56.38 2.865.99 3.133 2.18 3.663.57.257 1.29.575 2.18 1.278-1.12-3.067 1.22-4.148 2.22-1.041-2.87 2.054-6.81 2.295-10.75 2.536-1.52.093-3.03.186-4.49.383 1.08.982 1.41 2.287 1.73 3.59.27 1.054.54 2.108 1.2 2.99 1.31-.769 3.37-.937 5.5-1.111 5.1-.416 10.62-.868 7.46-9.688.92-.472 1.34.272 1.92 1.289.51.903 1.14 2.022 2.36 2.691.07-1.968 1.08-2.375 2.09-2.781.52-.209 1.03-.418 1.42-.838-.83-1.715-.69-2.377-.57-2.938.13-.571.23-1.037-.75-2.404-.38-.01-.81-.102-1.24-.195-1.38-.3-2.77-.6-2.46 1.907.24.209.51.41.77.611.82.622 1.65 1.248 1.84 2.154-1.09-.786-1.48-.404-1.86-.021-.41.4-.82.8-2.03-.143-.38-1.839-1.41-3.373-2.44-4.907-1.01-1.499-2.01-2.997-2.42-4.777.91-.48 1.34.261 1.92 1.277.51.9 1.15 2.016 2.35 2.691-.81-3.291 1.13-2.746 2.81-2.275 1.87.526 3.42.959.39-4.177 1.01.38 1.59.022 1.37-1.63.93.133 1.84 1.342 2.57 2.325.81 1.084 1.42 1.895 1.63.695-.08-.666-.52-1.165-.96-1.666-.39-.438-.78-.879-.93-1.435.92.012 1.67-.247 2.42-.507 1.08-.369 2.15-.739 3.7-.33-1.58-1.183-1.09-1.632-.66-2.027.38-.354.73-.666-.5-1.423-.44 1.801-2.37 1.14-4.31.481-.65-.222-1.3-.444-1.9-.572 2.14.01 2.53-1.216 2.96-2.58.25-.793.51-1.634 1.15-2.304.34.527.53 1.13.71 1.732.35 1.135.69 2.271 2.13 2.906-.04-.51.13-.676.31-.842.22-.214.44-.427.21-1.384-.7-.878-1.18-1.857-1.67-2.836-.58-1.168-1.16-2.337-2.11-3.337-.13 1.264-.92 1.448-1.71 1.632-.99.234-1.99.467-1.66 2.89-1.45-.683-2.28-1.647-3.12-2.61-.6-.687-1.19-1.373-2.01-1.957-.62 1.176-.71 2.723-.79 4.121-.17 2.819-.3 5.032-4.67 2.378-.99-1.181-.76-1.796-.55-2.353.26-.672.48-1.258-1.53-2.649 1.19 4.401.84 4.628-.96 5.41-.92-.963-.72-1.851-.52-2.772.26-1.226.54-2.511-1.75-4.115.5 1.636.41 2.455.32 3.26-.13 1.167-.26 2.307 1.42 5.869-2.54-1.416-2.5-2.537-2.45-3.735.04-1.163.09-2.397-2.23-4.042.46 2.291.02 3.087-.28 3.632-.4.71-.56.996 1.82 3.609-4.81-3.999-6.52-3.191-3.66 1.697-1.01-.638-1.94-1.157-1.46.688-.63-.385-1.32-1.437-2.02-2.485-1.14-1.727-2.28-3.443-3.07-2.136.42 1.394 1.07 2.669 1.72 3.949.45.863.89 1.728 1.26 2.635-.17.101-.38.262-.61.443-1.16.923-2.97 2.361-4.83-.968 1.3.867 1.79.353 2.28-.159.37-.393.75-.786 1.49-.553-2.2-1.227-3.67-.883-4.98-.576-2.05.478-3.71.866-7.1-4.924.05 1.895-1.05 1.843-2.14 1.791-1.13-.054-2.27-.108-2.14 1.984.49-.167 1.32.261 2.16.69 1.59.818 3.19 1.638 2.38-1.648l3.33 2.494.1 1.163c.08.868.17 1.736.24 2.589-1.61-1.205-3.03-1.439-4.66-1.707-.99-.163-2.05-.337-3.28-.748-.33-.647-.56-1.335-.8-2.023-.53-1.59-1.07-3.178-2.86-4.252.32 4.226-3.41 2.971-5.55 2.25-.5-.169-.92-.308-1.17-.341.84 1.861.76 3.007.69 4.054-.09 1.323-.17 2.489 1.63 4.747-.62-.268-.89.057-.67 1.254-1.25-2.031-2.33-2.988-3.26-3.812-1.16-1.036-2.09-1.86-2.83-4.338-1.49.747.66 5.455 2.23 7.851-1.47-.794-2.69.078-3.72.814-1.45 1.031-2.52 1.792-3.34-2.684-.49-.278-.97-.068-1.47.15-.95.42-1.98.869-3.28-2.04.19-.392.61-.388 1.03-.384.65.006 1.31.013 1.1-1.5-2.5-2.634-3.06-1.871-3.62-1.108-.38.524-.76 1.048-1.77.472l-.16-1.89c-.65-.692-1.01-1.527-.98-2.524.32.209.69.496 1.05.783 1.01.798 2.04 1.606 2.14.798-.05-.299-.09-.607-.12-.916-.11-.938-.22-1.877-.95-2.558-.33.024-.7.016-1.07.008-2.13-.044-4.55-.095-2.26 5.363-3.65-.784-6.69-.529-8.16 2.507 2.15 2.355 3.63 4.984 4.45 7.896-2.58-3.925-2.61-3.358-2.67-2.284-.05.955-.12 2.311-2.03 1.26-1.04-2.075-.88-3.331-.73-4.446.16-1.288.31-2.386-1.46-4.338-.86.305-1.85.347-2.85.389-2.55.107-5.09.214-5.2 4.706-3.03-2.558-5.27-2.376-7.44-2.202-2.06.167-4.05.328-6.58-1.908-.2-.868 0-1.244.16-1.53.18-.333.29-.547-.36-1.275-1.24-.346-2.06-1.948-2.76-3.294-.75-1.46-1.34-2.619-2.14-1.548.36 1.256 1.11 2.37 1.85 3.483.61.928 1.23 1.855 1.63 2.864-.04 2.872-1.44 3.28-2.83 3.687-.8.236-1.6.471-2.14 1.182.35.29.71.575 1.08.86 1.83 1.428 3.65 2.851 3.86 4.922-.5.426-1.3.305-2.09.183-1.01-.153-2.01-.306-2.42.637.05-1.062-.63-1.825-1.31-2.585-.6-.667-1.19-1.332-1.29-2.195 2.86 2.516 4.78 3.258 5.44 1.717-.88-.422-1.45-.556-1.85-.652-.98-.231-1.01-.238-2.23-3.525-1.16.381-2.6.262-4.03.143-1.54-.127-3.08-.254-4.28.232-.02-3.01-3.29-7.292-4.97-6.731-.52-2.103.45-1.447 1.42-.792.9.606 1.8 1.211 1.51-.372-2.85-3.738-6.44-5.676-5.99.433.67.752 1.19 1.296 1.59 1.72 1.4 1.478 1.4 1.484 1.77 3.716 1.43.652 1.3-.426 1.18-1.43-.13-1.103-.25-2.117 1.71-.665.06.924-.14 1.362-.33 1.8-.28.616-.56 1.23-.11 3.194-.94-.017-2.01-.15-3.11-.285-3.95-.489-8.25-1.02-8.05 3.61-1.2-.676-1.81-1.57-2.42-2.464-.61-.894-1.22-1.788-2.42-2.464.04.539-.16.653-.35.767-.14.087-.29.173-.33.446-.3.497.19 1.584.6 2.503.5 1.122.89 1.994-.39 1.242-3.7-3.757-11.57-4.2-9.76 2.561-1.5-.506-1.55-1.54-1.59-2.573-.04-.847-.08-1.693-.92-2.247-.06.956-1.11.02-2.16-.913-.33-.293-.65-.585-.95-.818.94 3.792-.94 2.146-3.01.126.04.546.25 1.026.46 1.505.27.617.54 1.232.46 1.981-1.15-1.089-1.73-1.076-2.31-1.062-.58.013-1.16.027-2.32-1.086-.03-1.561-.73-2.904-1.71-4.144-.17.541-.44.891-.71 1.24-.25.334-.51.669-.68 1.171.78.771 1.2.859 1.63.948.4.082.79.164 1.47.791-.33.496-.7.855-1.04 1.181-.68.661-1.23 1.188-1.05 2.451-.81-2.831-1.63-2.79-2.63-2.741-.99.049-2.15.107-3.64-2.622-2.56 3.816-6.99 4.037-13.48.098.26 2.839.04 2.566-.85 1.437-.36-.453-.83-1.043-1.42-1.625-1.44 2.811-5.42 4.556-9.04.152.79 5.682-6.57-.47-8.36-2.906.6.443 2.25-.99.68-2.1-1.59-.882-2.57-.56-2.97.969.94.948 1.83 1.907 1.6 3.246-1.7-.764-2.52.246-3.34 1.261-.34.421-.68.843-1.09 1.139.49-2.42.4-4.756.33-6.562-.13-3.204-.18-4.741 3.15-2.118.25-1.953 1.23-2.43 2.22-2.908.86-.422 1.73-.843 2.09-2.28 1.05.74 1.78 1.73 2.39 2.562.79 1.079 1.39 1.894 2.25 1.559.37 1.073-.04 1.395-.39 1.667-.43.332-.76.591.53 2.053.39-.176.8-.345 1.22-.516 3.1-1.269 6.55-2.686 5.21-8.204 1.35.94 1.86 2.183 2.38 3.428.51 1.246 1.02 2.493 2.38 3.439-.74-4.394 1.89-3.355 3.92-2.555.72.282 1.36.535 1.74.508-.88.386-1.1 2.056-1.07 4.221-3.07-3.418-5.82-1.83-3.64 2.27 2 1.313 3.13.907 4.26.501 1.09-.388 2.17-.777 4.01.327l-.13-2.816c-.94-1.453-1.65-1.448-2.17-1.444-.49.004-.79.007-.94-1.253 1.08 1.024.87-.456.67-1.936-.12-.861-.24-1.722-.11-2.09 3.3 2.85 3.26.752 3.21-1.498-.03-1.863-.07-3.83 1.78-3.182.58 1.633.17 2.398-.13 2.973-.29.552-.49.928.37 1.726.76-.29 1.59-.436 2.41-.581 1.58-.277 3.15-.553 4.21-1.811 1.17.599 1.69 1.434 2.21 2.27.45.722.9 1.445 1.77 2.018.17-1.952.69-3.582 1.1-4.831.04-.133.08-.261.12-.385 1.18 1.483 1.41 3.311 1.64 5.138.19 1.517.38 3.032 1.11 4.349 1.21 1.121 1.61.696 2.02.273.26-.271.52-.541.99-.406-2.72-3.946.32-3.78 1.33-3.338-1.66-2.512-1.29-3.168-.96-3.734.22-.389.42-.735-.08-1.61-.49-.463-.94-.921-.84-1.595 1.48.767 1.82 1.949 2.16 3.131.33 1.122.65 2.244 1.95 3.014 1.76.648 1.59-1.774 1.44-3.945-.14-2.023-.27-3.829 1.2-2.728-.1 1.946.39 3.67 1.8 5.075 3.88 2.115 2.73-3.671 2.06-7.075-.2-1.002-.36-1.798-.34-2.125.24 1.397 1.14 2.534 2.05 3.676.46.581.93 1.164 1.3 1.783.11 1.007-.21 1.865-.52 2.701-.48 1.265-.94 2.482.15 4.093 3.27 1.047 3.06-1.207 2.82-3.877-.16-1.745-.33-3.668.43-4.964.77.533 1.23 1.184 1.69 1.834.43.61.85 1.217 1.54 1.724-.12-1.896-.25-3.772-.38-5.648-.58-.497-1.07-.182-1.55.121-.71.458-1.39.891-2.28-1.51.45-.071.95-.036 1.45-.002 1.33.093 2.67.186 2.92-1.728-3.37-.225-6.45.092-9.52.41-1.79.184-3.58.369-5.43.446.27-3.28 3.47-2.967 6.41-2.681 3.3.323 6.27.613 4.38-4.262-.11-2.199.99-2.15 2.09-2.101.25.012.51.023.75.006.05.292.07.592.1.892.09.946.18 1.893.9 2.593 1.24-.262 2.87.183 4.5.63 1.65.452 3.31.905 4.57.623.05.286.07.581.1.878.09.943.18 1.898.9 2.588-.05-1.253.7-1.05 1.44-.846.82.224 1.64.447 1.41-1.25-.61-.339-1.03-.337-1.45-.334-.61.003-1.22.007-2.4-1.036.18-1.47.49-2.395.76-3.222.57-1.693.99-2.972-.07-7.669 1.6 1.02 1.68.51 1.79-.105.12-.767.28-1.695 3.44-.012-.76-4.676 3-4.405 6.57-4.149 1.64.119 3.24.234 4.34-.137.06.293.09.595.12.897.1.931.21 1.868.92 2.554.12-.586.59-.55 1.54.365-2.28-3.544-2.27-6.966 1.15-4.312.57.463.78 1.075 1 1.686.18.526.36 1.05.77 1.478-.06-1.077-.35-1.775-.56-2.285-.28-.666-.42-1.01.25-1.453 1.03.665 1.45 1.566 1.86 2.466.21.461.42.921.72 1.349.02-.833-.27-1.55-.57-2.27-.27-.673-.55-1.349-.57-2.124.77-.589 2.15-.463 3.51-.339 2.27.206 4.47.407 3.68-2.712 1.47.72 1.7 1.97 1.93 3.222.22 1.162.43 2.327 1.64 3.071-1.09-10.16 5.07-6.826 10.12-3.418-.74-.458-.39.794.99 1.63-1.74.609-.96 6.956 2.51 10.668.31-.875.17-1.785.06-2.547-.17-1.095-.29-1.886 1.04-1.833-1.51-1.961-2.99-3.943-2.9-6.595 1.38.947 1.89 2.264 2.39 3.583.46 1.207.93 2.415 2.05 3.342.56.076 1.1.44 1.61.776.94.63 1.74 1.165 2.2-.458-1.91-6.627.26-8.582 2.39-10.507 1.29-1.159 2.56-2.307 2.92-4.458.12 2.211.32 4.412 2.13 5.93.47-.296.93-.414 1.32-.513 1.18-.299 1.69-.428-.35-4.78 2.78 2.06 2.12.604 1.42-.942-.63-1.406-1.3-2.887.55-1.86.02 2.187 1.04 3.937 2.06 5.692.35.594.69 1.189 1 1.801.88.499 1.16-.039 1.44-.571l.01-.031c-.19-.347-.41-.688-.62-1.028-.91-1.464-1.82-2.922-1.51-4.91 3.53 4.476 3.48.874 1.26-2.486 4.01.921 6.68-.491 9.17-1.809 3.49-1.848 6.63-3.512 12.64 1.7-.49-2.066-.05-2.608.39-3.149.43-.541.87-1.081.38-3.141 4.92 4.126 4.68.897 3.35-3.52.94.041 1.81.265 2.55.454 1.97.503 2.97.757 1.63-3.355 1.69.481 1.77 1.712 1.85 2.941.07 1.017.13 2.033 1.11 2.621 1.26.771 1.65.336 1.97-.03.42-.481.74-.843 2.82 1.811-1.06-3.72.26-3.551 1.92-2.863.06.287.11.583.15.88.14.933.27 1.869 1.03 2.53 2.14-2.687 5.37-4.31 8.56-5.912 4.33-2.175 8.59-4.312 9.91-9.016-1.05-.204-2 .359-2.94.915-1.43.846-2.83 1.674-4.51-.245.88.417 1.79-.127.57-1.296l-.18-.041c-1.89-.443-3.97-.932-.98 2.627-2.65.81-5.06-4.957-5.66-8.284 4.91 2.557 12.13 5.821 8.01-1.926 1.67 1.643 1.85.916 2.02.188.16-.691.32-1.384 1.77-.05.75 1.512.3 2.004-.03 2.358-.31.338-.51.55.57 1.402 2.41 1.818 2.54.019 2.66-1.788.01-.181.02-.363.04-.54-.19-.186-.4-.359-.61-.532-.56-.465-1.11-.926-1.22-1.598 2.89 1.932 4.19 4.643 4.16 8.002-.48-.111-.92-.34-1.31-.54-1.05-.546-1.7-.883-1.51 1.938 1.88.852 2.48 2.336 3.08 3.819.53 1.315 1.05 2.629 2.48 3.499 0-1.156.59-1.36 1.18-1.563.3-.103.6-.206.83-.431-4.71-9.513-3.83-16.408-1.13-18.551-.1 3.144 3.25 3.235 3.36 2.339-1.71-3.598.92-3.239 3.3-2.915.92.124 1.79.243 2.37.132.1.408.16.835.22 1.262.16 1.139.32 2.279 1.19 3.075 1.79.74 1.13-1.531.55-3.565-.47-1.638-.9-3.122.03-2.75 1.83 3.543 2.15 2.069 2.42.752.14-.643.27-1.248.56-1.216.27 1.345 1.64 2.136 3.03 2.928.82.47 1.64.94 2.22 1.527-.23-.976-.06-1.322.12-1.668.16-.329.33-.657.14-1.527-.54 1.062-2.37 1-3.38-2.309 2.96 1.385 4.95.503 1.65-3.92 1.28.346 1.56 1.216 1.84 2.087.28.872.56 1.744 1.84 2.088-1.06-3.615-.43-3.139.44-2.498.53.396 1.14.854 1.49.454-.19-2.004-1.44-3.472-2.67-4.936l-.48-.563c1.42 2.485-.2 2.277-1.86 2.064-.95-.122-1.91-.245-2.31.139.11-1.206-.52-2.01-1.16-2.817-.49-.634-1-1.269-1.14-2.101.24-.038.49-.051.74-.064 1.1-.057 2.2-.114 1.89-2.33-1.33-2.066-2.35-2.417-3.52-2.815-.48-.166-.99-.339-1.56-.647 3.05-1.027 4.93.654 8.39 3.729.46.409.95.843 1.47 1.298.08-2.303 2.4-.925 4.64.411 1.22.725 2.42 1.437 3.23 1.543.69 1.069 1.14 2.264 1.59 3.46.51 1.363 1.02 2.727 1.89 3.907.54-.655 1.41-.825 2.27-.994 1.09-.213 2.18-.426 2.63-1.6-.23-.605-.6-1.319-.97-2.013-.97-1.88-1.88-3.622.19-2.686.69 1.029.74 1.693.78 2.282.05.896.1 1.618 2.41 3.181-1.3-2.847-.99-2.759-.28-2.556.4.116.93.268 1.37-.057-.37-.626-.71-1.268-1.05-1.91-.64-1.222-1.28-2.445-2.15-3.561-.09.701-2.66 1.042-3.26-1.336.33-.08.8.048 1.26.175 1.08.293 2.15.585 1.52-1.65-1.1-1.32-1.8-1.217-2.31-1.142-.49.071-.79.116-1.09-1.131 1.29.647.24-1.669-.47-2.815 2.66 1.311 4.34 3.148 5.44 5.299.21-.494.79-.458 1.35-.423.75.047 1.5.094 1.44-1.056.9.581 2.02 1.702 3.09 2.773 1.96 1.958 3.75 3.749 3.78 1.772.06-1.508-.87-2.494-1.79-3.478-1-1.064-2-2.126-1.75-3.843 1.58.702 2.02 1.996 2.47 3.29.44 1.292.89 2.584 2.46 3.284.19-.037.37-.074.56-.11 3.04-.589 6.09-1.181 5.98-6.397 1.98 3.311 2.43 1.805 2.85.42.21-.699.41-1.367.79-1.369.88.281 1.95 1.365 2.94 2.38 1.38 1.4 2.63 2.668 3.08 1.502-.32-1.363-.77-3.001-1.21-4.614-.71-2.575-1.39-5.086-1.54-6.306 1.14 1.426 2.52 2.07 4.38 2.936.25.117.51.238.78.366 1.51 1.734.93 2.07.37 2.394-.55.324-1.09.636.5 2.324.17-.08.37-.163.58-.253 1.88-.79 4.91-2.07 2.86-5.858-.78-.347-1.17.022-1.53.353-.54.492-.98.904-2.43-1.217.38-2.458 2.63-2.907 4.75-3.328 2.24-.447 4.33-.863 3.83-3.619 1.62.136 3.28.605 4.77 1.026 3.63 1.026 6.25 1.767 4.74-3.286.54.41 1.09.904 1.59 1.364 1.43 1.295 2.55 2.318 2.59.437-.43-.354-.79-.745-1.15-1.137-.69-.748-1.38-1.497-2.53-1.981l-1.08-5.601c2.99 3.755 3.61 2.057 4.19.499.48-1.317.93-2.533 2.74-.27-2.35-1.206-.94 1.949.73 3.726.05-.918.94-.66 1.83-.402 1.15.335 2.3.67 1.64-1.54-2.01-2.024-1.61-2.396-1.02-2.947.43-.395.95-.883.76-2.136 1.29.285 1.61 1.151 1.93 2.015.33.865.65 1.729 1.94 2.006.01-.16-.01-.361-.03-.562-.06-.575-.11-1.154.5-.8.98 1.471.73 2.44.47 3.429-.36 1.364-.73 2.766 2.12 5.572-.42-2.122 1.05-2.181 2.45-2.237 1.84-.074 3.57-.143.68-4.926.84.409 1.39.994 1.93 1.576.51.545 1.02 1.089 1.76 1.484-.55-1.763.09-2.277.64-2.719.62-.491 1.12-.892-.24-2.806-1.11-.783-1.16-.121-1.22.542-.06.749-.12 1.499-1.7.154.13-2.025.69-3.087 1.16-3.997.73-1.402 1.27-2.441-.21-6.09 1.23.858 1.92 2.039 2.62 3.222.68 1.158 1.36 2.317 2.54 3.173-4.61-3.109-2.43 3.611.49 6.043-.14-1.936.6-2.677 1.33-3.419.58-.592 1.17-1.185 1.31-2.387-1.2-1.411-2.62-2.663-4.58-3.574-1-2.883-.37-3.529.26-4.173.4-.403.79-.805.78-1.753 1.63.546 2.41 1.616 3.19 2.686.47.651.94 1.302 1.6 1.835-.84-3.626.92-4.777 2.66-5.912 1.71-1.12 3.4-2.224 2.53-5.678 1.07 1.087 1.8 2.377 2.52 3.667.61 1.079 1.22 2.157 2.03 3.115 1.63-.048.85-1.428.15-2.669-.67-1.191-1.27-2.254.4-1.89.34 1.186.74 2.353 2.17 2.912-.01-.243-.01-.507-.01-.788.01-1.347.02-3.089-1.21-4.818-1.01.623-2.05 1.216-3.6 1.118-.57-1.779-1.98-3.032-3.93-3.96.78-1.791 2.47-2.366 4.16-2.942.93-.314 1.85-.628 2.63-1.14.15-2.67-1.47-4.26-3.09-5.855-.6-.589-1.19-1.178-1.71-1.823 2.18.606 2.67 1.177 3.37 2.001.56.654 1.26 1.467 3.04 2.585.84 4.798-.41 5.609-1.71 6.452-.69.451-1.4.912-1.81 1.999 1.52.332 2.35.858 3.18 1.391.75.482 1.51.969 2.81 1.324 1 1.584 1.4 2.643 1.76 3.603.53 1.425.99 2.631 3.2 5.014-1.99-1.277-2.63 2.579-1.63 3.116 4.04 2.93 3.45-3.46.14-6.437 3.43 2.754 4.08-1.576 2.34-3.501-1.62-1.333-1.67-.586-1.73.161-.05.66-.1 1.319-1.22.543-.38-1.158-1.6-1.815-2.82-2.477l-.04-.022c.68-3.07 3.99-4.167 7.08-5.191 4.6-1.527 8.72-2.893 2.95-10.39-.16-.297-.41-.624-.77-.967.28.333.53.655.77.967.96 1.71-.92 2.442-2.1-.109.08-.143.31-.301.58-.482.75-.519 1.76-1.221.31-2.243-2.07.534-1.78 4.172-1.14 8.273-3.07-2.471-3.43-1.36-3.79-.251-.28.873-.57 1.745-2.17.869-.87-2.04-.09-1.901.69-1.761.55.098 1.11.197 1.08-.467-.46-.541-.88-1.117-1.29-1.694-.82-1.145-1.65-2.292-2.88-3.166.12-1.066-.18-2.134-.42-2.971-.41-1.423-.62-2.178 1.75-1.121.99 1.252 1.09 2.343 1.2 3.399.15 1.509.29 2.948 2.97 4.688-.51-1.704-.83-3.192-1.11-4.513-.62-2.904-1.07-4.998-3.05-6.798-.92-.808-1.38-.414-1.8-.055-.55.469-1.03.875-2.38-1.565 1.02.259 1.51-.17 1.1-1.791 2.1 2.009 3.34 2.038 4.46 2.064 1.47.034 2.74.064 5.48 4.496-2.05-4.912-.58-12.547 4.64-9.432-1.03-.763-1.62-1.806-2.21-2.855l-.02-.042c1.06.534 2.03.93 1.33-.832 2.07 1.536 3.56 3.445 4.44 5.752-1.98-.069-3.03 1.062-4.08 2.193-.4.425-.79.849-1.23 1.21.74.562 1.13 1.347 1.52 2.13.56 1.115 1.12 2.228 2.68 2.696-1.2-2.603-.07-3.424 1.19-4.341 1.29-.942 2.72-1.986 1.91-5.17-.71-.373-1.21-.48-1.61-.566-.81-.176-1.2-.26-2.17-2.393.33-.967 1.43-1.454 2.52-1.937 2.03-.895 4.03-1.777 1-5.674 3.04 2.933 6.52 6.246 4.67.24.84.383 1.4.939 1.96 1.498.53.526 1.06 1.056 1.83 1.444.93 1.812.25 1.581-.43 1.349-.68-.232-1.37-.464-.45 1.347 3.03 1.309 2.6 1.756 2.13 2.244-.35.367-.73.757.34 1.556 1.62.99 1.83.17 2.03-.652.12-.471.24-.943.63-1.074-2.25-1.692-2.63-2.856-3.02-4.05-.35-1.102-.72-2.228-2.57-3.817 2.81 2.276 7.89 3.599 4.87-1.323.9.589 1.33.589 1.75.588.41 0 .81-.001 1.6.491 2.58 2.58 2.16 3.986 1.8 5.216-.36 1.222-.67 2.271 2.06 4.125-4.76-.206-6.12 2.665-7.52 5.615-.69 1.448-1.39 2.916-2.5 4.048 2.64 1.921 2.99.713 3.29-.331.29-1.035.54-1.908 2.95.611.44-2.205-.18-3.235-2.24-6.084 1.96.367 2.38.265 2.58.428.25.196.18.775 2.12 3.009-.01-.836.28-1.217.5-1.514.41-.549.62-.818-1.63-3.141 1.64-.566 2.85 1.832 4.12 4.355.8 1.598 1.63 3.247 2.61 4.226-.11-1.656 1.12-1.613 2.35-1.569 1.2.042 2.39.085 2.35-1.448-.7-.596-1.34-1.241-1.98-1.887-.67-.672-1.33-1.344-2.07-1.96.93 2.111.43 2.404-.07 2.698-.31.18-.61.359-.59.949-1.88-.694-5.63-5.544-3.6-5.218-.29 1.157 2.77 5.039 2.26 2.866-.25-2.002-1.02-3.661-2.04-5.154.35-.138.95-.196 1.6-.259 1.77-.172 3.92-.381 2.38-2.355-1.55-.902-1.88-.256-2.21.388-.33.648-.66 1.295-2.23.37-.93-2.055-.35-2.444.12-2.756.46-.314.81-.549-.61-2.321 5.64 5.689 10.68.791 5.63-4.959 1.25.384 2.38 1.332 3.26 2.068 1.19.993 1.91 1.601 1.86-.079-.29-.755-.79-1.369-1.3-1.982-.45-.562-.91-1.124-1.21-1.793 2.09 1.1 1.37-.299.61-1.796-.26-.501-.52-1.013-.68-1.446.7.026 1.51.178 2.31.33 1.18.221 2.36.443 3.22.275-.01 4.657.57 10.107 4.09 15.87 1.06-.653.09-2.003-.98-3.5-.39-.543-.79-1.104-1.12-1.659 2.1 1.381 2.56.713 3.02.045.4-.586.8-1.171 2.3-.384.67 2.114 2.79 3.216 4.98 4.282-1.51-3.747.61-2.98 2.08-2.445.63.229 1.15.416 1.21.186-.13-.387-.23-.796-.33-1.205-.29-1.152-.58-2.31-1.54-3.012-.33-.096-.55.31-.78.735-.44.818-.92 1.706-2.32-.787.84.111.75-3.996-.32-4.186.72 2.009-2.11 2.756-4.02-.563-1.08-2.923-.31-3.556.37-4.123.68-.567 1.28-1.068-.1-3.725 1.14.457 1.7 1.285 2.27 2.111.28.417.57.833.93 1.2.28-.46.84-.781 1.42-1.115 1.43-.813 2.98-1.697.87-4.835 3.77 1.517 3.6-.353 3.42-2.495-.11-1.25-.23-2.592.42-3.408-.06.061 4.66 2.519 1.07-1.811 1.26.303 1.94 1.009 2.62 1.717.59.612 1.18 1.226 2.14 1.583-1.76-2.784-.47-3.406.91-4.068 1-.483 2.04-.988 1.98-2.372.67 1.742 2.14 2.924 4.12 3.723 1.78.301 1.02-.736.29-1.72-.56-.757-1.1-1.482-.44-1.546.7 1.365 1.7 2.519 3.24 3.275-1.28-2.212-.28-1.691.72-1.17.82.429 1.64.858 1.2-.235-.17-1.062-.98-1.668-1.79-2.273-.71-.532-1.42-1.064-1.69-1.905 1.61-.052 2.01-1.545 2.42-3.038.43-1.609.87-3.218 2.82-3.019.4 1.564 1.37 2.732 2.6 3.716-.99-4.359-.18-5.554 3.48-1.413l.02-.177c.4-3.81.9-8.488 6.41-4.493-.83-2.199.02-2.43.86-2.66.57-.154 1.13-.309 1.19-1.062-.85-1.222-.96-1.848-1.09-2.553-.14-.766-.29-1.626-1.42-3.445.22.168.44.326.63.475 2.35 1.753 2.91 2.17 3.66.887 1.68 1.961 1.08 2.666.45 3.401-.3.354-.61.715-.67 1.227 2.46 1.57 3.76 3.977 5.08 6.391.43.801.87 1.602 1.35 2.374 2.04-.174 3.62-.919.86-5.121.57-.097 1.25-.087 1.92-.078 2.52.036 5.05.073 2.73-5.521.99-.047 2.01 1.307 2.76 2.304.7.933 1.16 1.554 1.16.424-.15-.518-.47-.919-.79-1.321-.25-.308-.5-.617-.67-.98 3.61 1.353 5.19-.892 6.86-3.251 1.42-2.009 2.9-4.102 5.74-4.127-1.47-.66-2.32-.969-2.98-1.211-1.19-.436-1.78-.65-4.33-2.309 1.02 1.78.34 1.599-.35 1.418-.68-.18-1.36-.36-.35 1.41.44.247.69.176.89.12.29-.079.47-.13.93.768-.8 2.19-2.45 2.447-4.08 2.701-1.5.234-2.98.465-3.78 2.192-.08-.487-.07-.929-.05-1.339.05-1.266.08-2.225-2.86-3.25-.59-3.074.71-3.01 3.7-.559-.34-2.022-.26-8.202 2.99-8.038l.18.526c.44 1.335.89 2.681 2.5 3.135 1.38.62 1.26-.496 1.13-1.608-.08-.74-.17-1.477.19-1.702l-2.73-1.329c3.26-2.085 5.31-5.556 7.31-9.068 1.27.254 1.97.935 2.68 1.616.61.588 1.22 1.177 2.19 1.49-.38-1.229-.77-2.47-1.14-3.686 1.14.385 1.74 1.187 2.34 1.989.31.404.61.809.98 1.159-.24-.802.15-.896.53-.989.5-.118.99-.236.17-1.819-.27-.156-.56-.299-.85-.443-.91-.453-1.83-.909-2.19-1.779.83.136 1.1-.363 1.37-.863.33-.617.67-1.233 2.07-.648.06.183.13.368.19.554.45 1.311.9 2.631 2.51 3.066-.5-1.102-.31-1.441-.12-1.781.22-.393.44-.786-.4-2.364-1.58-.715-2.59-1.836-3.35-3.15 4.15 2.568 4.36.739 4.56-1.088.13-1.162.26-2.324 1.4-2.354.68.537 1 1.343 1.32 2.147.38.943.76 1.883 1.7 2.382 3.18.284 4.66-1.863 6.22-4.133 2.13-3.089 4.41-6.406 11.35-4.134-.25-.652-.78-1.077-1.31-1.502-.68-.539-1.36-1.078-1.45-2.081.82-.651 1.12-.172 1.59.558.44.707 1.04 1.649 2.39 2.03.81-.668.35-1.019-.35-1.555-.68-.517-1.59-1.206-1.79-2.518.96.29 1.59.864 2.21 1.437.72.664 1.44 1.329 2.7 1.555-.29-1.538.4-2.009 1.1-2.48.74-.499 1.47-.997 1.04-2.759-1.59-.664-2.62-1.765-3.4-3.074 1.02.318.52-.981 0-2.305l-.01-.022c3.4 1.252 3.67-.304 3.93-1.746.19-1.09.38-2.116 1.89-1.814 2.33 2.588 1.43 3.119.49 3.68-.56.331-1.14.674-1.07 1.458.52.235.89.598 1.25.959.28.278.56.555.91.773 1.28-1.533 2.91-2.699 4.54-3.866.75-.532 1.49-1.064 2.2-1.63-3.15-.84-5.38-.735-5.85 1.26-.23-1.095-.97-1.768-1.71-2.441-.38-.35-.77-.7-1.08-1.108 3.51 1.235 3.18.4 2.77-.642-.29-.722-.62-1.544.27-1.844 2.22 1.424 6.05 2.877 6.77 1.434-1.84-1.476-2.82-2.047-3.7-2.56-.75-.434-1.42-.827-2.48-1.691 1.24.581 1.71.337 2.18.092.61-.321 1.22-.643 3.6.892-2.23-2.633-1.39-5.447 2.38-2.04-1.23-4.743 1.01-5.835 3.26-6.931.59-.286 1.17-.571 1.7-.922.11-1.13-.92-2.701-1.77-3.998-.89-1.353-1.58-2.408-.57-2.354 1.56 2.18 2.07 2.116 2.82 2.024.73-.09 1.68-.205 4.01 1.666-1.97-4.022-1.05-5.57-.21-6.985.86-1.444 1.63-2.749-.84-6.406 1.92.926 1.83-.203 1.73-1.331-.06-.697-.12-1.392.29-1.604 3.13 4.32 5.04 3.509 6.02 1.113.93-2.416.98-6.37.56-8.232 2.87 2.18 2.46 3.326 2.11 4.31-.24.659-.44 1.244.37 2.017.36.103.74.215 1.11.326 1.97.585 3.94 1.175 4.68.53-2.77-1.878-2.19-1.919-1.31-1.982.44-.032.97-.07 1.17-.36-2.51-2.24-2.9-2.89-3.73-4.29-.23-.38-.49-.815-.83-1.353 1.62.351 2.32 1.533 3.02 2.716.55.94 1.1 1.879 2.12 2.404.96.323 1.74.412 2.46.495 1.77.202 3.22.368 6.29 3.801-.99-1.59-2.13-3.039-3.27-4.486-1.3-1.647-2.59-3.291-3.67-5.137 2.18 1.432 3.59 1.363 4.96 1.296 1.12-.055 2.2-.109 3.66.638-1.37-1.614-3.45-2.58-5.53-3.547-1.77-.825-3.55-1.651-4.89-2.882.37-1.24 2.27-.465 4.17.307 2.68 1.092 5.35 2.177 3.6-2.482-2.06.809-4.05 0-6.13-.848-2-.813-4.1-1.663-6.43-1.167.39-.236.77-.707 1.17-1.21 1.03-1.27 2.22-2.743 4.12-1.16-.64-.095-.78.314-.22 1.404 5.1 2.648 7.07 2.216 8.29 1.047.45.636.74 1.155.99 1.612.68 1.252 1.11 2.03 4.18 3.445.06-.443.45-.566.83-.689.6-.191 1.2-.383.55-1.786-.98-.594-1.48-.424-1.83-.305-.46.156-.65.222-1.35-1.689.46-.1.93-.137 1.37-.171 1.81-.142 3.04-.238-.14-4.6 5.65 3.136 7.13-1.168 3.07-4.053 1.23.476 2.48.95 3.71 1.42-1.99-2.491-1.6-2.865-1.06-3.381.29-.274.62-.588.66-1.277 1.39.639 2.38 1.357 3.22 1.969 1.27.925 2.21 1.608 3.7 1.41l-.1-.264c-1.29-3.447-2.65-7.087.99-6.897.64 1.072.41 1.788.19 2.464-.3.938-.58 1.8 1.54 3.432.31-.962 1.55-.546 2.56-.207.66.22 1.22.408 1.37.165-.3-1.312-.39-2.23-.47-3.007-.19-1.834-.3-2.878-2.81-6.449 3.15.518 3.09-1.94 3.02-4.4-.08-2.954-.15-5.911 5.34-3.708-1.66-1.708-2.07-2.627-1.56-3.537 1.29-.008 2.74 1.222 4 2.29 1.42 1.208 2.6 2.21 3.03.985-.34-1.045-1.25-1.553-2.15-2.061-1.01-.573-2.02-1.145-2.25-2.487.39-.63.95-1.049 1.46-1.44 1.3-.982 2.38-1.8.19-5.38 1.49-.105 1.93.87 2.36 1.846.36.801.73 1.602 1.67 1.803.22-.19.47-.374.73-.563 1.57-1.13 3.41-2.462-.1-6.615.35-.174.75-.296 1.15-.419 1.46-.444 2.93-.89 1.64-3.785.92.307 1.68.785 2.44 1.264 1.05.67 2.11 1.34 3.62 1.548-1.8-2.505-.7-2.445.4-2.386.97.053 1.94.105.92-1.593-1.56-1.081-2.72-2.567-3.88-4.063-.34-.426-.67-.853-1.01-1.272 1.87.712 3.03 2.171 4.19 3.632 1.3 1.635 2.6 3.272 4.91 3.858-.72-1.196-.29-1.38.15-1.566.22-.095.44-.19.51-.421-.2-.144-.41-.287-.62-.429-1.55-1.071-3.1-2.136-3.87-4.024 1.3.26 2.24 1.127 3.01 1.827.86.789 1.49 1.366 2.14.627-1.21-2.423-3.41-3.803-5.61-5.19-.4-.25-.8-.502-1.2-.76 2.47 1.158 1.76.405.91-.497-.11-.11-.21-.22-.31-.33H0v954h1920V137.952l-3.66-.625c-.27 2.547-.67 4.99-1.08 7.439-.7 4.217-1.4 8.455-1.4 13.285-.57-1.148-.36-2.017-.19-2.756.26-1.118.45-1.938-2.2-2.98-1.81 2.364-1.59 6.689-.74 9.516-.58-1.172-1.21-2.306-2.79-2.288 1.42 2.302.72 2.872.02 3.441-.46.376-.92.752-.77 1.626.24.099.49.176.74.253.69.212 1.38.424 1.68 1.104-2.9-.171-1.98 1.497-1.17 2.982.53.96 1.02 1.843.4 2.102-.9-1.274-2.08-2.203-3.81-2.51-.64.881-1.3 1.719-1.96 2.542-3.11 3.931-5.94 7.504-4.64 13.605-.37-.011-.79-.055-1.22-.099-2.27-.234-4.68-.482-.94 4.04-.94-.099-1.61-.509-2.28-.92-.63-.383-1.26-.767-2.11-.9 2.28 3.853.17 9.017-4.95 8.606.3.804.6 1.6.9 2.397l.54 1.436c-8.94 4.107-13.88 11.62-18.44 19.561-.59 1.037-1.18 2.084-1.77 3.132-3.91 6.942-7.84 13.919-14.28 18.422.21 4.38-2.09 6.431-4.38 8.479-.46.41-.92.82-1.36 1.249l.32.964c.33.969.65 1.938.97 2.896-4.3-.088-5.73 4.505-4.76 7.417-2.84-2.296-2.43-.889-1.89.989.3 1.037.64 2.218.5 2.998-5.71 2.482-8.5 7.729-11.28 12.966-2.35 4.417-4.69 8.828-8.78 11.567.13.446.27.897.41 1.348l.48 1.552c-7.92 10.468-15.73 20.931-23.49 31.348-15.31 20.524-30.48 40.869-46.15 60.714l-2.52 3.075c-11.08 13.536-22.21 27.12-34.2 39.88-6.48 6.827-13.1 13.538-19.9 20.092-6.8 6.551-13.72 13.004-21.14 18.932 1.81 2.159.78 4.104-.96 4.002-.32-.848-.15-1.307-.02-1.659.16-.413.26-.679-.55-1.254-2.6-.361-1.99.875-1.3 2.282.49.979 1.01 2.04.53 2.706l-3.8-1.744c-3.16 4.735-6.38 8.142-9.61 11.57-2.92 3.095-5.85 6.208-8.79 10.332-.42-1.163-1.98-3.086-2.07-1.874l.72 3.946c-8.11 4.962-15.03 11.493-21.94 18.024-7.21 6.814-14.43 13.629-23.01 18.663-5.6 7.066-11.66 13.627-21.15 15.438.03 2.608-1.44 3.139-2.91 3.671-1.58.568-3.16 1.138-2.89 4.253-3.81-.779-5.52 1.364-7.23 3.506-1.73 2.162-3.45 4.322-7.33 3.475-3.13 6.188-8.52 9.191-13.92 12.197-5.15 2.873-10.31 5.748-13.51 11.406l-3.69-2.021c-2.4 4.538-6.98 6.763-11.43 8.916-4.65 2.253-9.13 4.429-10.79 9.096-3.18-1.773-4.38-.231-5.47 1.167-1.09 1.391-2.07 2.639-4.76.346l.37 3.936c-3.48-1.634-4.9-.086-6.53 1.687-.75.816-1.54 1.68-2.59 2.303-2.04 1.203-4.79 2.147-7.53 3.087-5.57 1.914-11.08 3.81-10.46 7.836-6.96 2.559-13.27 6.14-19.57 9.718-8.06 4.572-16.11 9.139-25.49 11.561.03.566.29 1.016.54 1.465.19.339.38.678.48 1.065-1.36.316-2.72.609-4.09.901-8.47 1.817-16.91 3.626-22.07 10.862l-5.34-3.444c-1.43 5.197-5.79 4.82-9.26 4.437.04.702.5 1.202.96 1.701.39.43.78.858.91 1.415-.72.714-2.22.124-3.71-.465-1.81-.713-3.62-1.426-4.04.174-5.92 7.088-15.05 8.775-24.57 9.677-.25-.099-.55-.083-.93.086l.93-.086c.57.231.84 1.088 1.16 2.126.17.534.35 1.117.6 1.686-9.87-.412-17.34 3.278-24.81 6.966-2.25 1.108-4.49 2.215-6.8 3.212-.97-.489-1.46-1.194-1.94-1.899-.56-.813-1.12-1.625-2.43-2.105.5 3.029-.34 3.647-1.19 4.264-.78.577-1.57 1.154-1.27 3.697-3.23-2.565-4.74-2.04-6.25-1.515-1.3.453-2.6.906-5.01-.642-.87 3.873-5.12 4.707-9.37 5.54-2.58.505-5.15 1.01-6.97 2.19-2.37-3.774-4.34-2.275-6.57-.574-2.01 1.533-4.24 3.23-7.17 1.383-.34 1.242.23 2.096.8 2.952.45.672.91 1.345.91 2.208-.4-.087-.91-.342-1.45-.615-1.87-.931-4.11-2.055-3.71 2.719-6.43-2.495-10.42-.414-14.41 1.667-.38.197-.75.394-1.13.588-1.55-.796-1.73-2.117-1.9-3.437-.2-1.425-.39-2.849-2.3-3.61-2.44 6.327-4.92 9.897-10.55 11.711-1.01-1.918-.54-2.34.04-2.869.46-.425 1.01-.92.93-2.312-1.31-.618-1.71-1.588-2.1-2.558-.4-.973-.8-1.946-2.12-2.566-.53 4.243-2.6 7.798-6.1 5.204.15-.17.48-.303.86-.454 1.1-.441 2.58-1.031.93-3.103-.39.121-.79.228-1.19.335-2.47.667-4.95 1.333-5.78 5.213-.95-.536-1.4-1.26-1.86-1.983-.52-.835-1.05-1.669-2.33-2.206-.47.897-1.12 1.257-1.71 1.584-1.14.634-2.07 1.145-1.02 5.224-4.24-2.766-6.13-.898-8.02.975-.97.952-1.93 1.905-3.21 2.25-.16 1.068.81 1.742 1.77 2.414 1.16.808 2.32 1.614 1.5 3.1-1.73-.385-2.66-2.24-3.43-3.764-.76-1.524-1.36-2.716-2.42-1.779 1.42 2.401 2.85 8.512-1.05 5.116-.03-.422-.01-.861.01-1.299.05-1.169.1-2.338-.72-3.204-6.07-.291-11.18 1.34-15.81 3.934-.11-.225-.32-.554-.67-1.02-4.28-.292-13.37-.91-11.37 4.923-1.07-1.051-1.47-.717-1.86-.382-.21.173-.41.347-.71.327v-.524c.04-1.413.07-2.833-1.52-3.698.44 2.339-.26 2.288-.96 2.236-.7-.052-1.4-.103-.95 2.245.62.235 1.15.293 1.68.35.54.058 1.08.115 1.71.355-.3 2.774-1.86 1.272-3.42-.231-1.56-1.502-3.12-3.005-3.41-.239.67.978 1.32.937 1.88.901.67-.043 1.19-.076 1.42 1.754-2.45.086-4.64.891-6.91 1.732-2.83 1.043-5.8 2.14-9.61 1.974.33-2.373 2.28-2.324 4.12-2.278 2.17.054 4.17.104 3.1-3.884 3.46 3.009 5.13 2.242 5.99-.195-1.72-1.827-2.35-1.388-2.99-.95-.5.352-1.01.704-2.09-.13-.52-1.597.09-2.062.53-2.397.42-.32.68-.521-.35-1.473-4.43-.083-8.32.909-10.56 5.462-.33-1.464-.76-2.902-1.48-4.221-.18.869-.76.881-1.35.893-.58.012-1.16.024-1.34.898.06.798.4 1.509.75 2.222.31.645.63 1.292.73 2.005-2.73-1.863-4.46-1.622-5.98-.918 1.02.527 2.67 5.116.65 3.557-1.24-4.338-2.27-3.608-3.5-2.735-.91.646-1.94 1.37-3.24.179-.65-.838-.6-1.898-.56-2.957.05-1.164.1-2.328-.78-3.194.21 3.415-1.15 3.466-2.49 3.517-.79.029-1.58.059-2.06.754 3.64 3.216-.61 5.113-2 4.383 1.04-1.819.97-4.93-2.07-7.784-.19.368-.16 1.238-.13 2.111.06 1.512.12 3.036-.99 1.996-1.86-7.226-7.41-5.276-10.83-4.073-.69.241-1.29.452-1.76.553.97.936 1.9 1.91 1.49 3.291-2.24-2.182-2.18-1.652-2.09-.762.07.626.15 1.429-.53 1.592l-4.04-3.435c.72-1.854-.13-3.219-.98-4.583-.45-.73-.9-1.459-1.12-2.263.64.173 1.19.35 1.68.506 2.49.794 3.27 1.043 4.41-2.441 2.59 2.783 3.2 2.773 3.97 2.761.71-.011 1.56-.024 4.25 2.155-2.16-2.576-.89-5.727 1.16-5.075.01.428-.02.868-.05 1.307-.08 1.166-.17 2.32.62 3.21.08-.752.64-.11 1.28.628.88 1.02 1.92 2.224 2.09.192-.29-1.194-.79-2.325-1.29-3.454-.84-1.892-1.67-3.78-1.54-5.961 1.3 1.123 1.54 2.595 1.78 4.071.28 1.8.57 3.606 2.8 4.791-.13-3.081 1.51-2.341 3.14-1.603 1.74.788 3.48 1.574 3.08-2.263-.45-.492-.95-.969-1.45-1.446-1.71-1.637-3.42-3.276-3.27-5.503.81.882 1.43 1.518 1.94 2.035 1.65 1.673 2.07 2.095 3.6 5.595.93-5.75 4.57-5.655 8.22-5.559 1.42.037 2.84.074 4.11-.235-.65-.749-.92-1.59-.73-2.598-.71-.583-1.02-.316-1.34-.047-.4.342-.8.686-2.03-.732.41-2.63 2.09-.95 3.73.689 1.18 1.181 2.34 2.34 2.99 1.848-1.14-2.78-2.05-5.07.21-4.847-.57 2.662.19 2.983 1.51 3.535.92.389 2.13.892 3.32 2.41.44-1.952 2.64-2.701 4.97-3.491 3.44-1.171 7.15-2.43 5.72-7.835 1.65.615 1.99 1.69 2.33 2.765.21.638.41 1.277.89 1.819.99-.537 2.18-.666 3.37-.795 2.17-.235 4.34-.471 5.3-3.172-.31 2.014.07 3.788 1.52 5.182.21-.305.49-.489.77-.673.74-.492 1.49-.985 1.11-3.785-.34-.308-.71-.601-1.09-.896-1.02-.802-2.05-1.607-2.2-2.699.86.553 1.29.21 1.71-.134.54-.437 1.08-.874 2.53.543-.48 2.71.57 4.87 3.2 6.488.17-3.591 2.67-3.085 5.01-2.612 2.61.527 5.02 1.014 3.78-4.259 1.27-.137 2.13.539 3.34 2.633-.7-2.216.29-2.474 1.01-2.658.6-.158 1-.262-.03-1.485-.32-.205-.64-.255-.94-.304-.63-.101-1.19-.192-1.57-1.671 1.44-.343 3.38.314 5.32.972 1.74.587 3.47 1.174 4.86 1.054-.59-.459-.76-1.088-.93-1.718-.14-.544-.29-1.088-.71-1.522-.8-.741-1.28-.806-1.75-.87-.44-.061-.88-.122-1.6-.746 1.55-2.986 4.65-2.928 7.75-2.87 3.76.07 7.51.14 8.52-5.199 1.99 1.146 1.62 1.462 1.23 1.796-.33.284-.67.581.41 1.409 2.7.808 4.6.057 6.5-.694 1.65-.649 3.29-1.298 5.45-.945-.12-4.296 2.98-3.548 5.87-2.851 3.06.737 5.89 1.419 4.45-3.955 2.04 2.706 2.96 2.004 4.02 1.195 1.13-.859 2.41-1.839 5.38 1.007-.46-3.793 1-3.96 2.47-4.128.64-.074 1.28-.148 1.76-.528 3.3 6.037 8.99 1.833 7.61-5.066 1.69.535 2.08 1.599 2.47 2.662.23.63.46 1.261.96 1.78-.02-1.484 1.05-.936 2.12-.386s2.15 1.101 2.13-.39c-1.6-1.928-2.62-6.453-.03-4.845.18.64.09 1.39.01 2.142-.15 1.289-.29 2.583.87 3.331 1.94 1.141 2.14-.933 2.35-3.004.14-1.453.28-2.905 1.02-3.239 1.08 2.633 1.81 2.955 3.28 3.599.3.132.63.278 1.01.46-.13-2.59.55-3.699 1.23-4.808.7-1.133 1.39-2.264 1.22-4.976-1.22.213-2.08-.459-2.59-2.811.18.069.38.185.58.3.56.324 1.12.651 1.13-.025-.25-.801-.97-1.401-1.69-1.999-.84-.7-1.68-1.399-1.76-2.418 2.86 1.122 3.51.335 4.13-.415.71-.854 1.38-1.66 5.22.451.04.88-.75 1.196-1.44 1.472-1.14.454-2.01.803 1.52 3.38.36 1.779-.85 1.849-1.88 1.908-1.06.06-1.92.11-.63 2.028.56.059.97-.163 1.38-.385.93-.507 1.85-1.013 4.62 1.816-.51-2.608.34-2.749 1.19-2.89.74-.123 1.48-.246 1.3-2.036-1.32-1.339-1.69-.966-2.07-.591-.29.296-.59.592-1.35.047.36-1.417-.52-2.324-1.41-3.232-.47-.48-.93-.959-1.22-1.515-.27-3.312.95-3.928 3.37-2.362.05.417.05.852.05 1.288.01 1.182.02 2.368.89 3.198-.01-.683.42-.578.85-.473.37.091.74.181.83-.238.3-1.251-.3-2.138-.9-3.027-.6-.887-1.2-1.776-.9-3.03 2.56.967 4.4.174 6.22-.607 2.82-1.211 5.58-2.391 10.79 3.055.07-.287.38-.512.73-.762.84-.609 1.88-1.363.01-3.5-.86-.916-1.46-.558-2.01-.233-.72.425-1.34.793-2.29-1.831 1.27.453 2.58-.173 3.89-.801 2.5-1.191 5-2.387 7.21 3.781.95.462 1.31-.122 1.66-.713l.02-.036c.28-1.274-.33-2.154-.94-3.035-.61-.878-1.22-1.756-.95-3.026 1.92-.697 4.12-1.262 6.38-1.839 5.92-1.517 12.2-3.123 14.53-7.462 6.71.889 11.49-1.528 16.26-3.946 2.57-1.298 5.13-2.595 7.99-3.382-.18-.181-.39-.35-.6-.519-.57-.457-1.14-.913-1.19-1.612 5.7 2.861 10.08.204 14.5-2.477 2.84-1.72 5.69-3.451 8.92-3.738-.15-.803-.57-1.479-1-2.154-.38-.609-.76-1.217-.95-1.919 3.27.229 5.77-.805 8.27-1.839 2.35-.97 4.69-1.939 7.67-1.871-1.08-.793-2.11-1.636-1.9-3.09 5.15 3.337 9.64 3.165 7.41-2.767 4.5 1.67 6.67-.36 8.84-2.393 2.13-1.996 4.27-3.995 8.61-2.491-2.69-3.577 2.52-4.224 3.42.268-2.74-2.812-3.51 1.725-1.39 3.783 1.94.062 2.75 1.019 3.87 2.343.63.754 1.37 1.628 2.48 2.524 2.17-3.499-1.96-8.855-3.89-11.365-.19-.238-.35-.45-.49-.633 3.62-.246 5.87-2.643 8.12-5.036.77-.816 1.54-1.632 2.36-2.361.86.426 1.38 1.028 1.91 1.628.49.566.99 1.131 1.76 1.547 2.6-6.961 8.85-9.615 14.82-12.146 5.01-2.129 9.82-4.171 12.08-8.618 1.71.653 2.57.757 3.3.844.8.096 1.44.172 2.85.943-.08-.561-.34-1.02-.6-1.478-.33-.591-.66-1.18-.61-1.981 8.96-3.38 16.03-8.474 23.06-13.535 6.47-4.661 12.9-9.293 20.73-12.53-1.73-4.381 3.55-4.825 8.2-2.697-.92-4.365 1.13-4.521 3.18-4.678 1.14-.087 2.29-.175 2.92-1.005-.82-2.505 3.01-5.519 6.87-8.564 2.56-2.021 5.15-4.057 6.4-5.966.55 1.431 1.48 2.643 3.05 3.422-.41-4.377 1.88-5.155 3.85-5.829 1.84-.626 3.41-1.161 2.3-4.412 1.59.775 2.91 1.741 3.86 2.938.99-.647.65-2.98-.74-4.885 1.79.479 1.94-.312 2.03-.765.1-.522.12-.597 2.46 2.233.47-1.643 1.55-2.451 2.63-3.257 1.5-1.112 2.99-2.222 2.86-5.521.29 1.25.63 2.443 2.16 2.926.15-1.608 1.12-2.114 2.08-2.62 1.16-.606 2.32-1.212 2.07-3.717.64 1.306 4.46 3.137 3.66 1.447.32-1.275-.46-1.853-1.24-2.431-.78-.576-1.56-1.152-1.24-2.42 5.82 1.658 8.61-.771 9.19-6.081 2.69 1.598 3.3-.13 4.08-2.326.52-1.475 1.12-3.16 2.47-4.191.62-.474 1.79-.679 3.01-.894 2.72-.477 5.73-1.005 3.71-4.648 6.85-.385 9.98-4.364 13.1-8.328 2.85-3.614 5.69-7.216 11.32-8.069a14.36 14.36 0 00-.87-.47c-.92-.467-1.84-.936-2.12-1.857.87-.422 1.14-.157 1.67.357.4.399.97.947 2.08 1.442-.21-4.755 3.45-7.513 7.35-10.456 3.4-2.557 6.98-5.254 8.36-9.523.38.864 1.02 1.499 2.09 1.851.74-6.124 5.07-7.817 9.4-9.513.75-.291 1.49-.583 2.22-.897 2.01-7.719 6.01-15.023 15.54-11.421-.28-1.27-1.11-2.125-1.94-2.981-.87-.897-1.74-1.794-1.97-3.168 2.54 1.446 2.72.047 2.89-1.354.14-1.085.28-2.172 1.51-1.941.35.86.19 1.326.07 1.682-.13.413-.22.679.58 1.243.13-.383.26-.693.38-.968.48-1.151.71-1.695-.72-4.433 6-1.691 9.46-6.378 12.92-11.066 3.61-4.891 7.22-9.784 13.73-11.278a28.7 28.7 0 01-.38-.938c-.68-1.767-1.37-3.531-3.43-4.176-54.15 55.051-112.21 106.388-175.83 149.855-63.59 43.402-132.9 78.73-208.24 100.591-1.41.513-2.89 1.172-4.41 1.844-4.49 1.999-9.25 4.113-13.16 2.869-.49 2.481-1.93 3.259-3.37 4.037-1.35.726-2.69 1.453-3.27 3.559-.46-.142-1.18-.762-1.9-1.383-1.24-1.069-2.48-2.136-2.37-.746 2 3.938-2.52 2.811-5.23 2.134-.63-.158-1.17-.292-1.5-.33-1.97-.239-3.2 1.31-4.28 2.668-1.27 1.598-2.32 2.93-4.13.772.29-.264.54-.593.8-.923.65-.845 1.3-1.691 2.55-1.425-3.85-3.82-5.51-1.482-7.25.955-1.77 2.48-3.61 5.062-7.89 1.364-.35 4.755-4.33 4.954-7.88 5.133l-.06.003-.06.002c-3.58.139-6.73.262-5.46 5.059-.95-.896-1.41-.883-1.87-.869-.54.015-1.08.03-2.35-1.368 1.23 4.258-3.71 5.774-6.77-.309-1.3.642-2.3 1.858-3.3 3.074-1.91 2.308-3.81 4.618-7.73 3.017-.69-.813-.7-1.879-.7-2.945-.01-1.172-.02-2.342-.93-3.17-.03.314-.14.682-.26 1.085-.39 1.329-.88 3.03 1.05 4.384-1.44 2.16-3.93 2.233-6.43 2.306-2.97.086-5.94.173-7.12 3.783-1.62-3.66-4.55-2.369-2.56 1.92-1.08-.648-1.44-1.567-1.8-2.487-.19-.478-.38-.955-.67-1.396-.49.826-1.48.921-2.43 1.013-1.68.161-3.26.312-1.87 4.426-1.35-.222-2.52-.096-3.69.03-.73.08-1.47.159-2.25.152-3.5-3.933-6.66-2.754-10.16-1.451-3.41 1.268-7.13 2.652-11.77-.438-.89 4.558-4.25 4.028-7.61 3.497-3.63-.573-7.26-1.147-7.81 4.68-1.62-2.088-1.98-1.563-2.35-1.039-.32.459-.64.917-1.8-.379.75-3.092 2.99-3.845 5.39-4.651 1.73-.582 3.55-1.191 4.95-2.726a42.57 42.57 0 01-.69-1.921c-.46-1.333-.91-2.67-1.65-3.919-1.77-.615-2.95-.004-2.63 3.763-1.59-.795-1.61-2.127-1.64-3.459-.03-1.498-.06-2.995-2.31-3.724.91 1.79.15 2.42-.62 3.053-.95.789-1.9 1.582.37 4.633-1.99-1.362-2.7-.486-3.33.276-.59.725-1.1 1.346-2.53-.16 1.97 2.438 2.05 3.457 2.16 4.768.07.903.16 1.946.88 3.686-4.69-3.455-5.33-.364-5.95 2.621-.02.115-.04.23-.07.345-1.26-.648-1.6-1.597-1.94-2.545-.26-.742-.52-1.482-1.23-2.077-1.99 2.397-7.18 3.585-10.16.391 1.61.555 2.51-.397 3.4-1.349.75-.789 1.5-1.578 2.65-1.511-1.1-.872-1.65-1.914-2.21-2.956-.72-1.365-1.45-2.732-3.39-3.721.95 1.958.66 2.465.27 3.134-.29.518-.65 1.133-.53 2.593-2.23-6.587-14.02-7.344-13.39-1.404-1.33-.528-1.45-1.432-1.56-2.334-.12-.902-.23-1.802-1.56-2.322-.03.539-.26.653-.48.767-.17.087-.34.174-.42.455.27.841.48 1.604.67 2.312.7 2.587 1.19 4.429 3.91 6.57-1.4-.689-2.31-.325-3.21.039-.33.132-.66.264-1.02.345 1.09 1.609 2.12 1.866 2.96 2.075.89.222 1.56.391 1.87 2.047-1.68-1.757-2.22-1.044-2.77-.33-.58.753-1.16 1.506-3.07-.653-.46-2.036-.22-2.98-.02-3.799.23-.906.41-1.659-.42-3.569-1.4-1.054-2.04-.414-2.67.226-.59.596-1.18 1.192-2.4.424.03-1.266-.74-2.277-1.5-3.287-.76-1.008-1.51-2.014-1.49-3.27 1.01-.8 3.04-3.946.35-6.681-.78-.334-.83.938-.87 2.211-.05 1.359-.09 2.72-1.03 2.127-.05-2.119-1.29-2.881-2.38-3.544-1.04-.636-1.93-1.181-1.46-2.746-1.97-.158-4.05 2.746.49 6.412-.84-3.24-.19-2.187.86-.477.71 1.148 1.6 2.592 2.34 3.229.06-.155.1-.356.13-.557.1-.568.2-1.134.77-.65-.14.845.08 1.581.29 2.32.2.694.41 1.389.31 2.179-1.02.324-1.65-.763-2.45-2.133-.74-1.273-1.63-2.789-3.1-3.651-.1.367-.28.582-.46.797-.34.416-.68.831-.53 2.347.65.987 1.29.97 1.82.955.66-.017 1.17-.031 1.37 1.788-1.68-.478-2.92.04-4.15.556-1.62.68-3.24 1.359-5.88-.223 1.21-1.418 1.84-2.884 2.47-4.342.72-1.676 1.44-3.341 3.04-4.905-.45-.384-.69-.842-.92-1.298-.17-.348-.35-.696-.62-1.007-1.35.809-2.48 2.091-3.62 3.374-1.53 1.727-3.06 3.456-5.11 4.032-1.05-.709-1.37-1.644-1.68-2.58-.17-.476-.33-.953-.59-1.4-.02.545-.24.659-.47.773-.17.087-.34.175-.43.453-.03.677.35 1.224.74 1.772.33.478.67.957.74 1.525v.001c-1.27 1.058-3.7 3.088-5.81-1.12-.58.707-1.1 2.071-1.65 3.498-1.3 3.402-2.73 7.159-5.51 3.183.34-.266.72-1.337 1.27-2.872.62-1.767 1.47-4.148 2.72-6.62-1.4-.08-2.28 1.04-3.16 2.16-1.28 1.622-2.56 3.244-5.43 1.215 1.74 1.24 2.01 2.899 2.28 4.564.22 1.291.43 2.585 1.33 3.69-.55-.451-.77-.143-.99.166-.27.392-.55.785-1.51-.385 1.26-1.914.04-6.17-2.7-9.446.08 5.357-1.89 3.099-4.53.076-.15-.173-.3-.349-.46-.526-1.39 4.727-3.85 4.897-6.37 5.071-1.74.12-3.5.242-4.97 1.87-1.05-2.882-.1-3.692.86-4.51 1.04-.877 2.08-1.764.68-5.222-1.57-.558-2.53.309-3.49 1.176-1.56 1.403-3.12 2.805-7.26-1.836-.91 3.067-3.02 3.301-5.14 3.534-.6.067-1.21.133-1.78.267-.08.927.1 1.782.28 2.638.26 1.194.51 2.391.05 3.79-.23-.171-.48-.392-.73-.612-.76-.668-1.51-1.331-1.67-.578zm549.73-168.908c.03.174.05.339.07.496-.09-.178-.12-.347-.07-.496zM1193.64 717.3c.2-.117.4-.233.61-.347.25.537-.1.492-.61.347zm0 0c-.64.37-1.26.757-1.88 1.159-.52-1.868.72-1.501 1.65-1.225l.23.066zM1920 136.949v-11.117c-.61.069-1.34-.011-2.28-.496.19 2.026-.08 3.457-.32 4.776-.36 1.941-.67 3.636.56 6.619.63-.262 1.36-.109 2.04.218zM1643.01 0l-.03.019-.03-.019h.06zm-5.03 0h1.52c-.04.3-.03.65-.01 1.001.07 1.275.13 2.557-2.58 1.418-.85-.458-.83-.712-.8-1.1.02-.304.05-.69-.32-1.319h.21c1.82 1.001 1.94.534 1.98 0zm-242.31 203.589zm-344.4 181.04a.484.484 0 01-.23.069c.08-.032.15-.055.23-.069zm52.42 35.333c.19.233.19.388.05.486 0-.154-.02-.315-.05-.486zm-123.467 51.023c.156-.064.404-.008.715.125-.094.202-.315.2-.715-.125zm31.327 19.616c.01-.449.06-.832.14-1.158-.02.378-.07.764-.14 1.158zm194.2-2.702zm-23.72 45.442c.02.108.04.207.06.3a1.505 1.505 0 01-.06-.3zm26.59 123.38c-1.66-.65-3.32-1.301-3.2 1.517 2.53 1.332 4.83 2.207 5.05-.96-.54-.041-1.2-.299-1.85-.557zm140.43-20.276c.47-.539 1.1-1.253.76-2.976l.01-.01c-2.84-2.76-3.55-1.9-4.26-1.041-.42.514-.85 1.029-1.73.765.12.552.51.985.89 1.42.45.508.9 1.016.94 1.716-.16.821-1.25.07-2.32-.675-.4-.276-.8-.552-1.15-.746 1.12 3.342 1.04 3.989.95 4.764-.06.525-.12 1.108.17 2.63 3.45.774 6.49.83 7.61-2.398-2.88-2.291-2.52-2.702-1.87-3.449zm254.52-585.97c.83-.023 1.72-.047 2.88.657-1.87-2.682-2.52-2.45-3.42-2.13-.68.242-1.51.536-3.12-.35 1.48 1.883 2.51 1.854 3.66 1.823zm-65.16 62.867c-.13.396-.26.791-.74.82 2.19 3.057 4.96 1.9 3.7-.664-2.46-1.632-2.71-.893-2.96-.156zm-476.62 273.349l.17-.077c-1.63-2.908-3.32-1.991-4.71-1.241-.48.262-.93.504-1.32.555 2.91 2.13 4.38 1.447 5.86.763zm52.37 40.119c1.58 5.578 8.75 3.877 6.93.229-2.13-1.83-2.84-1.004-3.55-.179-.69.803-1.38 1.606-3.38-.05zm-133.204 32.374c.145.488.291.975.366 1.482 2.343 2.642 2.436.264 2.323-2.569-.655-.669-1.053-.773-1.449-.877-.424-.111-.846-.221-1.581-1.025-.223 1.1.059 2.046.341 2.989zm245.064 60.429c-3.42 1.838-5.16 2.275-8.78 2.607 1.28-3.818 4.5-4.154 8.78-2.607zm-78.04 31.345c.55-.197 1.05-.379 1.46-.374-1.64-3.386-3.65-2.068-4.95-1.211-.27.175-.51.331-.71.423 1.08 2.29 2.79 1.674 4.2 1.162zm-41.3 8.056c.62 1.173 2.03 7.412-.84 4.084-1.77-1.888-.72-5.855.84-4.084zm-73.76 94.629c-.21-.799-.34-1.314.44-.847-1.27-2.862-5.56-3.44-5.08 1.436 1.76 2.477 2.1 1.595 2.43.711.33-.882.67-1.765 2.43.702.13-.628-.07-1.396-.22-2.002zm68.66 3.031c.2-.409.41-.818.72-1a2.875 2.875 0 01-.28-.237c-.87-.788-3.25-2.928-3.12 1.299 1.73 1.816 2.21.877 2.68-.062zm67.5 7.039c2.99 2.673 5.7 1.723 4.25-1.487-.2-.083-.4-.171-.6-.258-1.91-.836-3.83-1.67-3.65 1.745zm10.29-6.423c1.97 3.89 6.3 3.982 6.74 1.457-2.27-2.2-3.24-1.794-4.21-1.388-.69.287-1.38.575-2.53-.069zm-155.83-140.667c-.93.611-1.86 1.223-3.02 1.333 2.55 2.677 7.4 2.595 7.9-2.418-2.12-.728-3.5.178-4.88 1.085zm228.1 182.717c1.67 3.695-1.35 4.779-4.32 2.639-.62-4.858 1.59-4.244 4.32-2.639zm-113.4-86.988c.34-.47.68-.939 1.2-1.048-1.79-4.216-4.18-1.332-4.16 1.424 1.71 1.351 2.34.486 2.96-.376zm-126.28-109.671c.52-.238 1.09-.5 1.83.491-.74-3.581-1.62-3.198-2.56-2.786-.42.182-.85.371-1.29.225.75 2.65 1.34 2.381 2.02 2.07zm5.83-.889c-.41.46-.68.762-2.38-1.922v.006c-.24 1.082.04 2.017.32 2.952.15.493.3.988.37 1.504l2.31 2.103c.09-2.46.45-2.206 1.21-1.674.48.335 1.11.781 1.94.726-.08-.733-.61-1.32-1.14-1.907-.57-.634-1.15-1.267-1.14-2.082-.87-.394-1.21-.014-1.49.294zm9.23 7.599c.22-.449.44-.898.75-1.138-2.67-2.559-4.48-3.257-3.94 1.202 2.08 2.195 2.64 1.065 3.19-.064zm92.49 61.335c-1.08-.269-1.51.783-1.64 2.462 1.39 1.07 1.32 1.873 1.25 2.861-.08.977-.17 2.133 1.11 3.905 2.22.802 1.44-1.344.5-3.925-.68-1.854-1.43-3.933-1.22-5.303zm-80.68-67.6l.02-1.071c-.26-.044-.55-.166-.85-.289-1.01-.422-2.02-.844-1.53 1.962.52.46 1.03.919 1.54 1.376l.78.705c.01-.894.03-1.79.04-2.683zm-49.099-45.37c.265-.897.466-1.577 2.284 1.525.346-5.455-6.482-5.966-3.806-.708 1.071.709 1.316-.12 1.522-.817zm139.999 109.885c-.88.95-1.82 1.962-3.7.867v-.006c1.33 2.762.7 3.658.01 4.628-.41.592-.85 1.211-.87 2.297-2.32-2.109-4.41-3.785-4.07-.508 2.4 1.644 4 1.669 4.89.23 1.64 1.09-.21 2.602-.84 2.185 2.28 3.22 4.38.053 5.84-2.158.26-.387.5-.744.71-1.033 1.78 2.487 1.87 2.175 1.99 1.788.14-.434.3-.962 2.87 2.224.86-2.238.61-4.578.38-6.697-.29-2.583-.53-4.837 1.32-6.177-2.64-1.817-5.5-.981-3.28 3.964-3.01-4.012-4.08-2.863-5.25-1.604zm90.93 80.974c2.05 4.059-.93 3.93-2.56 3.85-1.65-3.306.32-5.12 2.56-3.85zm-105.95-85.719c-1.07-.895-2.13-1.781-3.21-2.682-.5 1.114.15 1.839.79 2.564.65.726 1.3 1.452.79 2.57 1.41.96 1.85-.105 1.63-2.452zm-105.85-100.3v-.006.006zm-1.59.672c.93.636 1.85 1.272 1.59-.672-1.05-.008-2.48-1.578-3.82-3.05-2.04-2.235-3.88-4.245-3.84-.232 1.14-.405 1.73.721 2.45 2.115.53 1.036 1.15 2.22 2.12 3.033-.5-2.568.5-1.882 1.5-1.194zm46.54 39.759c-.01-.305-.02-.61-.02-.907-1.63-1.654-1.74-.102-1.85 1.455-.06.941-.13 1.883-.53 2.122 2.53 2.738 2.46.021 2.4-2.67zm-29.54-35.782c-3.24-2.55-4.37 4.455-.81 8.672-.7-3.416-.48-6.555 1.57-2.352.67-.676.73-3.201-.76-6.32zM1156.1 574.93c-.77-3.419-.58-6.654 1.61-2.517v-.009c.06-.854-.2-1.785-.45-2.689-.51-1.822-.98-3.537 1.22-4.298-4.11-3.323-4.33.915-4.01 6.283-.62.521-1.83-.124-3.05-.77-.98-.526-1.97-1.051-2.63-.944.49-.443.96-.919-.02-1.908 2.64.457 5.87-1.884 4-7.244-2.25-1.923-2.67-.285-3.09 1.353-.22.86-.45 1.719-.93 2.065-.84-.677-1.68-1.355-2.51-2.031-.52-.42-1.03-.839-1.55-1.258-.43 1.08-1.31 1.729-2.19 2.381-1.98 1.463-3.99 2.942-1.01 9.338-1.38-.791-1.53-1.998-1.61-3.226-3.52-1.615-.19 4.855 2.42 6.738-.11-3.185.24-2.561 1.02-1.197.54.965 1.3 2.301 2.25 2.919.35-2.697 2-2.807 3.66-2.917 1.6-.107 3.2-.214 3.62-2.669 1.2 2.066 2.05 2.739 3.25 2.6zm-101.93-78.463c.57.699 1.15 1.4 1.18 2.279v2.843c.39.34.78.684 1.18 1.029.39.339.77.68 1.17 1.021.11-1.776-.34-3.359-.78-4.944-.37-1.297-.73-2.594-.79-3.998-1.27-1.694-1.42-1.057-1.56-.422-.15.635-.3 1.268-1.56-.423-.12 1.039.52 1.826 1.16 2.615zm242.18 194.632c2.56 3 3.69-1.451 1.7-3.622-2.02-2.151-1.87-.304-1.72 1.541.07.804.13 1.607.02 2.081zm-51.24-47.109c-2.16-.128 6.14 5.117 4.19-.741-1.77-1.572-2.31-.828-2.86-.085-.34.468-.68.935-1.33.826zm-208.42-172.727c-.01-.42-.01-.84-.01-1.259-2.01-2.055-1.94-1-1.87-.061.04.605.08 1.162-.45.81.11.919.86 1.624 1.62 2.332.25.228.49.456.72.691 0-.834-.01-1.674-.01-2.513zm25.65 15.683c-1.57-1.046-2.45-2.331-2.37-3.939l-.01.003c-.45.679-.8 1.574-1.16 2.47-.52 1.328-1.05 2.658-1.91 3.292.66.323 1.38.768 2.09 1.213 1.22.757 2.45 1.514 3.38 1.67l-.02-4.709zm-28.02-24.811c1.01 1.923 2.02 3.839 2.35 5.977l-1.54-1.363c1.94 3.659 3.07 2.799 4.24 1.91.79-.6 1.6-1.213 2.69-.458-.6-2.174-1.82-2.365-2.96-2.542-1.39-.216-2.64-.411-2.47-4.145-1.19-1.431-1.53-1.105-1.87-.778-.27.256-.54.511-1.21-.082.25.496.51.989.77 1.481zm34.9 28.242c.68-.881 1.26-1.637 3.3 1.64-1.97-6.621-8.06-8.648-5.48-.986 1.16.671 1.69-.024 2.18-.654zm9.46 4.597c-.54-.938-1.31-2.242-2.22-2.37.36 1.037.47 2.161.58 3.287.23 2.227.45 4.459 2.62 6.03.14-.93-.16-1.705-.46-2.479-.35-.895-.7-1.789-.36-2.919 1.35.812 1.5 2.016 1.59 3.239 1.73 1.345 1.69-.924 1.55-3.342-1.47-.886-1.52-2.234-1.57-3.602l-.03-.59-.56-.477c-.87-.751-1.74-1.494-2.61-2.242.12 1.066.66 1.988 1.2 2.913.54.92 1.08 1.842 1.21 2.908-.09 1.098-.41.537-.94-.356zm19.21 14.981c-.04 3.941-1.86 4.352-5.55.927.5-3.038 2.69-2.632 5.55-.927zm-52.81-45.028c-1.29-.407-2.57-.814-3.04.461l.07.055c1.07.881 2.12 1.751 2.26 2.953.36-.078.7-.135 1.01-.188 1.83-.308 2.89-.488 2.81-3.939-.41 1.511-1.76 1.084-3.11.658zm118.24 98.698l-.03-3.813-.01-.006c-.5.091-1.04.112-1.57.133-1.83.073-3.65.144-4.07 2.937.56.449 1.13.901 1.69 1.348l.75.599c-.65-3.788.42-4.189 3.24-1.198zm-111.74-94.993c.57-.11 1.13-.219 2.06.421-1.41-3.727-5.14-4.507-4.65-1.262 1.26 1.098 1.93.969 2.59.841zm217.76 172.504c1.47-.006 2.95-.012 3.5-1.694l-.21-.065c-3.13-.945-8.53-2.579-6.5 2.834.69-1.066 1.95-1.07 3.21-1.075zm-236.44-196.36c.24 1.504.47 3.012 1.3 4.318l.02.009c-.91-.09-1.03.414-1.14.864-.14.571-.26 1.056-1.91.142 5.38 7.436 9.44 2.695 10.73 1.15.26 3.024 1.6 4.214 3.88 3.429-.36-1.452-1.02-2.806-2.36-3.939.54.034 1.17.13 1.85.23 2.92.435 6.55.974 5.79-2.776-1.12-.484-2-.705-2.8-.904-1.93-.483-3.37-.843-6.49-4.497-.35.373-.79.569-1.23.765-1.26.563-2.51 1.125-1.73 5.889-.28-.701-.52-1.413-.76-2.124-.63-1.835-1.26-3.67-2.42-5.333-1.35-.357-2.36-.036-3.04 1.018.13.577.22 1.168.31 1.759zm38.8 40.922c3.36 1.555 2.94-.941 1.51-5.236-1.48.114-1.17 1.257-.84 2.457.32 1.177.66 2.41-.67 2.779zm-25.98-28.228c-.43-.017-.86-.034-1.46-.391.61 3.988 4.21 4.811 3.87 1.54-1.15-1.1-1.78-1.125-2.41-1.149zm124.84 98.669c.05 1.661-.58 1.985-1.61 1.552 1.02 1.655 2.09 1.704 3.21 1.754 1.07.048 2.18.099 3.34 1.553.32 2.103-.21 2.592-.73 3.081-.46.422-.91.843-.83 2.294 1.85 1.518 3.07 1.854 4.3 2.188.47.13.95.26 1.46.459.23-3.28-1.34-5.907-3.38-8.332.06-.145.13-.292.2-.44.82-1.839 1.65-3.681 4.66-1.382.31-1.19-.25-2.05-.82-2.911-.44-.677-.88-1.353-.9-2.189-.74 1.787-1.67 2.089-2.52 2.366-1 .326-1.9.618-2.28 3.245-1.36-1.071-2.74-2.16-4.1-3.238zm-3.19 5.01c3.07 2.821 4.8 3.069 4.88.11-.51-.1-1.1-.345-1.68-.59-1.47-.617-2.94-1.232-3.2.48zm-108.37-98.6c-.16-.889-.32-1.78-1.59-2.305v-.006c-1.05.263-1 3.506.08 5.642 1.99 1.622 3.03 1.283 3.08-1.033-1.25-.522-1.41-1.41-1.57-2.298zm243.84 190.908v-.087c-1.5-.2-2.98-.358-2.51 2.98 2.39 1.846 2.45-.529 2.51-2.893zm-249.28-197.048c-.44-.668-.88-1.334-1.64-1.892l.05 3.76c2.05 2.053 3.36 2.597 3.08-.083-.67-.54-1.08-1.163-1.49-1.785zm66.44 50.453c-.72-1.31-1.83-2.517-3.22-3.583.36 2.26-.38 2.348-1.12 2.435-.72.085-1.44.17-1.15 2.234 2.99 2.605 3.93 1.188 4.88-.235.2-.295.4-.591.61-.851zm-54.43-47.405c-.86.699-1.72 1.397-3.53.173.09.558.44 1.032.79 1.506.4.544.8 1.087.82 1.754 2.26 1.731 2.88.135 3.5-1.464.43-1.117.87-2.236 1.86-2.221-1.74-1.12-2.59-.433-3.44.252zm14.53 11.136l.06 3.76c1.22 1.389 1.59 1.073 1.96.756.37-.318.74-.636 1.97.765-.5-1.424-1.6-2.628-2.71-3.834-.44-.477-.88-.955-1.28-1.447zm-26.36-21.777c.87.78 1.74 1.56 1.99 2.553v.006c-.33.263-.89.077-1.44-.11-.69-.233-1.39-.466-1.64.188.42 1.988 1.23 2.186 2.11 2.401.6.145 1.23.298 1.79 1.011-.02-1.574-.05-3.123-.08-4.687-1.5-.805-2.21-2.243-2.78-3.392-.57-1.149-.99-2.01-1.93-1.658-.39 1.578.79 2.633 1.98 3.688zm130.96 108.429c.03-.85.05-1.676 1.33-1.138-1.97-4.548-3.99-1.493-3.23 2.172 1.84.918 1.87-.073 1.9-1.034zm-93.68-83.106c.27-.335.54-.67.84-.939-.37-.048-.77-.163-1.17-.278-1.55-.447-3.1-.893-2.68 2.58 1.5.532 2.26-.416 3.01-1.363zm231.94 175.964c.04-.262.08-.524.13-.769-3.13-2.96-4.24-.918-2.49 2.096 1.95 1.594 2.15.137 2.36-1.327zm-213.15-164.921c.55-.162 1.1-.324 1.83-.115-1.28-2.706-5.89-4.388-5.5-.842 1.83 1.503 2.75 1.23 3.67.957zm89.75 66.689c-1.77-.134-3.55-.268-4.7.748 2.38 6.394 6.6 3.415 6.45-.657-.57-.002-1.16-.047-1.75-.091zm-138.34-123.665c-.5-.031-.72-.044-.7-2.019l.01.002c-1.75-.552-3.38-.88-4.6-.362-.63-.769-.5-.975-.31-1.294.17-.277.4-.639.23-1.525-2.05-2.404-2.71-2.062-3.38-1.718-.15.078-.31.157-.48.203 1.54 2.408 1.35 2.548.91 2.866-.32.224-.75.536-.79 1.795.71 1.343 1.79 2.555 3.15 3.667-.07.566-.6.197-1.14-.171-.58-.402-1.17-.804-1.15-.002-.28-.448-.47-.924-.66-1.398-.37-.913-.73-1.821-1.74-2.524.37 1.392.66 2.665.92 3.838 1.1 4.917 1.81 8.058 6.32 10.716-.1-.713-.64-1.283-1.18-1.855-.59-.621-1.18-1.244-1.21-2.054 1.89-.641 3.13.05 6.23 3.59.14-1.258 1.04-1.006 1.94-.755 1.18.329 2.35.657 1.84-2.375.94 1.373 1.7 1.457 2.33 1.526.7.077 1.22.134 1.61 1.877 2.55.457-.85-3.771-2.19-5.447-.11-.13-.2-.245-.27-.341-.93-.687-1.55-.772-2.16-.856-.98-.133-1.95-.265-4.09-2.733-.14-1.245.52-.902 1.17-.56.75.388 1.49.776 1.08-1.135-.75-.898-1.3-.932-1.69-.956zm24.5 18.453c-.52.547-.96 1.005.05 2.93v0c5.02 1.413 7.39 12.045 6.65 15.748 2.67.875 1.92-1.772.93-5.284-.72-2.54-1.57-5.533-1.34-7.971.52-.74 1.55-.506 2.57-.271 1.2.274 2.4.548 2.8-.734-1.55-2.281-2.56-1.92-3.27-1.666-.71.255-1.13.404-1.49-2.314-1.04-.459-1.74-.103-2.39.23-1.01.518-1.92.978-3.82-1.785-.13.534-.42.837-.69 1.117zm249.7 194.056c-.1-.883-.2-1.764-.77-2.436v.006c-3.33.623-3.83 5.052-3.98 8.669.82.171 1.5.285 2.1.386 2.69.45 3.82.639 9.16 4.474-.82-2.72-1.07-4.627-1.32-6.542-.29-2.2-.57-4.413-1.74-7.885-.76-.581-1.21-.616-1.66-.652-.47-.038-.95-.076-1.8-.769.16 1.041.61 1.954 1.06 2.867.75 1.512 1.49 3.025.93 5.129-1.67-.632-1.82-1.941-1.98-3.247zm-133.28-88.117c2.58 1.097 3.46-.325 2.32-3.846-.21.272-.43.516-.66.761-.69.755-1.39 1.515-1.66 3.085zm-153.56-130.164c-.57-.693-1.14-1.386-1.2-2.246-2.45-.727 1.12 6.463 2.41 4.851.1-1.033-.55-1.82-1.21-2.605zm50.03 42.554l-.24-7.58-.01.003c-2.92-1.552-4.3 3.082-2.11 6.524.73.751 1.06.735 1.39.72.26-.012.52-.024.97.333zm109.92 84.043v-.008.008zm0 0c.02.646.04 1.283.07 1.921l.03.962c1.29 1.364 1.64 1 1.99.637.28-.285.55-.569 1.28-.021-.55-2.25-.94-2.391-1.78-2.692-.4-.146-.91-.329-1.59-.807zM1146 500.159c2.37 1.709 4.77-2.061.65-5.028.34 1.685 0 2.098-.33 2.511-.33.413-.66.826-.32 2.517zm-79.17-68.52c.72-.326 4.01 2.59 3.98 5.25-1.93-1.514-3.81-3.119-3.98-5.25zm142.38 113.252c-.7-.374-.81-.984-.87-1.603-.82-.408-.77.775-.72 1.96.02.436.04.873.02 1.23 2.24 1.152 3.18-.137 3.13-3.202-1-.353-1.67-.084-1.56 1.615zm-12.25-15.404c.28-.964.56-1.928 1.97-.819-2-4.177-4.36-1.555-3.98 1.592 1.45 1.177 1.73.202 2.01-.773zm-166.53-144.236c1.58 2.214.36 4.908-.71 1.173v-.003.003c-.9.645-.57 2.88.19 5.601 1.72.341 2.03-.16 2.32-.627.24-.378.46-.732 1.4-.6-.09-.224-.17-.45-.25-.676-.62-1.745-1.25-3.491-2.95-4.871zM1363.85 631.7c1.16.287 2.33.575 1.93-1.753-2.78-2.276-3.87-1.737-3.31 1.593.37-.09.88.035 1.38.16zm-192.22-144.431c.37 2.703-.99 2.235-3.14.3-.36-2.713 1-2.236 3.14-.3zm73.47 58.46c3.28 3.125 3.02-2.243-.18-3.864-.74.502-.6.844-.33 1.513.21.515.49 1.224.51 2.351zm-57.53-57.095c-2.92-4.408-7.27-4.051-7.15-.044v-.006c.67 1.578 1.17 1.608 1.58 1.632.38.022.67.039.94 1.234.57-.269.8-1.141 1.03-2.012.44-1.652.88-3.297 3.6-.804zm201.64 135.215l.09-.145c-3.01-4.059-5.37-1.024-5.88 1.814 3.45 1.962 4.62.148 5.79-1.669zm-321.25-235.833c.61.163 1.18.315 1.61.612-1.02-2.994-5.38-5.391-5.45-3.76.84 2.342 2.46 2.776 3.84 3.148zm60.46 35.976c-.92-.114-1.85-.228-3.81-2.243l.01.006c.36 2.231.51 2.988.54 3.749.02.65-.05 1.303-.15 2.879 1.24 1.409 1.72 1.406 2.21 1.402.42-.003.85-.005 1.76.897-.95-3.547-.08-3.725.78-3.903.64-.131 1.28-.261 1.19-1.729-1.13-.888-1.83-.973-2.53-1.058zm117.52 91.849c.37-3.134-1.6-5.224-4.46-6.929.28 1.553-.23 1.719-.75 1.886-.59.191-1.18.383-.59 2.644.9-.977 2.26.089 3.55 1.094.83.651 1.63 1.276 2.25 1.305zm-148.27-125.028c1.96.601 2.05-2.279 1.91-5.581-2.44-2.048-2.23.847-2.02 3.758.05.623.09 1.247.11 1.823zm74.09 54.764c.4 2.682-.96 2.214-3.12.27-.39-2.686.96-2.205 3.12-.27zm-31.74-23.155c3.09 2.571 3.68.57 3.7-2.445-.33.061-.69.074-1.06.087-1.28.044-2.56.089-2.64 2.358zm25.15 7.022c-.78 1.128-1.57 2.256-3.73.916-.14 1.962.35 3.702 1.11 5.336 1.52 1.219 1.34.715 1.02-.158-.12-.334-.26-.721-.33-1.087.68-.439 1.44-.665 2.15-.877 1.96-.586 3.56-1.064 2.12-5.63-1.16-.184-1.75.658-2.34 1.5zm56.76 48.689c2.25-.262 4.41-.7 4.43-4.868l-.32.046c-2.11.304-4.23.609-4.11 4.822zm-119.18-101.031c.72-.314 1.43-.627 1.37-2.373l.01-.003c-1.18-.753-1.91-1.689-2.63-2.626-.79-1.034-1.59-2.069-3.01-2.861.27 1.607 1.06 3.011 1.84 4.415.69 1.228 1.38 2.457 1.73 3.823.21-.162.45-.269.69-.375zm-5.89.56c.85.18 1.48.314 3.14 2.575v-.01c-.25-1.142-.6-2.241-.94-3.275-.76-2.354-1.42-4.377-.44-5.834-2.98-3.577-3.07-1.359-3.18 1.296-.07 1.662-.14 3.496-.94 4.186 1.13.801 1.79.942 2.36 1.062zm9.05 3.595c.59-.417 1.27-.901 2.47.5 1.5 1.791 1.09 2.857.7 3.879-.21.568-.42 1.122-.3 1.779 1.73.465 2.7-.511 2.81-3.046-1.6-1.009-2.53-2.243-2.54-3.84-2.7-2.517-3.68-1.862-4.66-1.206-.21.145-.43.29-.67.401 1 2.377 1.54 1.996 2.19 1.533zm52.34 36.338c.35-.339.7-.679 1.55-.14-.86-2.524-4.55-3.259-3.91-.382 1.46 1.384 1.91.954 2.36.522zm-4.36-.954c.71.777 1.41 1.554 1.38 2.626 2.12.626-.27-4.935-2.7-5.691-.28 1.308.52 2.187 1.32 3.065zm19.05 10.945c-.48.558-.96 1.118-.56 3.235v.01c.77 1.303 1.91 2.439 3.33 3.468-.5-4.114.7-3.094 3.09-.34-.14-1.44-.34-2.275-.5-2.902-.23-.95-.35-1.424.08-2.8-.39-.078-.71-.033-1.02.013-.84.119-1.67.238-3.77-1.888-.11.581-.38.892-.65 1.204zm127.44 93.777c.25-.719.55-1.568.1-3.38l-.01.004c-2.98-1.286-3.51 1.977-2 5.936.75.682.79.184.79-.369l1.73 1.183c-1.14-1.858-.91-2.525-.61-3.374zm121.22 80.691c-.33.179-.71.391-1.39.163.37 2.951 4.44 5.016 3.51 1.257-1.23-1.914-1.6-1.711-2.12-1.42zm-312.08-225.568c-.92.767-2.45.408-4.6-1.086-1.43-4.597 4.18-.391 4.6 1.086zm166.57 119.061c-.41 2.657-1.96 3.388-4.65 2.2.4-2.673 1.96-3.389 4.65-2.2zm-149.47-118.293c-.29-.764-.48-1.255.28-.859v.007-.006-.001c-3.47-4.59-6.26-1.076-5.76 4.202 2.49 3.179 4.16 4.251 5.58 3.604-.51-2.284-1.35-2.726-2.22-3.188-.67-.353-1.36-.717-1.95-1.927-.05-2.945 1.03-2.106 2.32-1.098.73.567 1.53 1.187 2.24 1.219.02-.628-.26-1.37-.49-1.953zm122.95 94.185c-.4.724-.8 1.448-1.71 1.307.33 1.816 1.62 3.225 3.53 4.349.08-1.189.54-1.728 1.01-2.266.6-.699 1.2-1.396.95-3.518-2.53-2.118-3.15-.996-3.78.128zm-72.16-55.539c.89-.063 2.1.432 4.03 2.19 1.08 4.093-3.74.701-4.03-2.19zm-43.51-37.422c-.49-1.095-.97-2.194-1.04-3.463l.01-.004c-2.04-1.513-3.03-1.123-2.95 1.165 1.89 1.796 3.1 2.342 3.98 2.302zm51.55 37.956c-.02-.945-.04-1.889.32-2.17-1.65-.444-2.28.893-2.01 3.808 1.76 1.536 1.73-.052 1.69-1.638zm18.31 8.82c.43-.086.97-.194.61-2.327l-.01.003c-2.98-2.422-4.23-1.894-3.77 1.614-2.25-2.152-2.67-1.146-3.09-.141-.37.88-.73 1.76-2.32.524 2.31 3.968 6.75 4.696 5.42-.353.81 1.29 1.97 2.408 3.41 3.419-1.06-2.577-.72-2.646-.25-2.739zm-3.16-.68c0-.007-.01-.013-.01-.019v-.011.007c.01.008.01.015.01.023zm-59.97-41.59c1.91 1.612 3.82 3.226 2.72-.519-.43-.077-1.04-.466-1.64-.855-1.28-.818-2.56-1.637-2.21.445.36.281.74.605 1.13.929zm51.15 34.63c.8-.038 1.6-.075 1.05-2.446-2.72-2.331-4.2-.787-2.11 2.835.23-.351.64-.37 1.06-.389zm1.83-8.401c.03-.637.06-1.366.06-2.262l.01-.003c.61.601 1.01 1.294 1.4 1.986.65 1.14 1.31 2.279 2.91 3.008.12-1.118-.26-2.029-.63-2.938-.2-.476-.4-.951-.52-1.455-.49-.311-.96-.635-1.42-.95-2.53-1.731-4.69-3.21-5.72-.874.51 1.443 1.02 1.513 1.59 1.593.28.038.58.079.9.278-.11.577-.38.887-.65 1.197-.47.556-.95 1.111-.53 3.209.59.623 1.12 1.365 1.6 2.049.95 1.34 1.74 2.457 2.62 2.024-1.77-3.443-1.72-4.539-1.62-6.862zm10.7 3.931c-.7 4.165-3.45 4.829-6.6 4.802-3.96-7.55 2.57-6.189 6.6-4.802zm-79.63-53.431c1.55.072 3.1.145 3.81-1.301-3.73-2.654-5.07-1.062-5.94 1.363.65-.132 1.39-.097 2.13-.062zm28.3 18.163a5.772 5.772 0 01-.21-.413c-1.88-1.224-.1 3.792.41 4.718 2.62 1.381.89-2.098-.2-4.305zm-11.31-14.442c-.04-1.233-.08-2.467-1.75-3.012 1.15 3.008.83 4.178.55 5.218-.24.86-.45 1.631.21 3.281.38-.055.77-.078 1.17-.102 1.93-.116 3.87-.232 3.74-3.96-1.57-1.586-1.71-.649-1.85.286-.11.744-.22 1.487-1.05.955-.96-.625-.99-1.645-1.02-2.666zm79.45 63.046c-1.81-5.092-8.7-11.142-8.3-5.239 3.58.771 4.8 1.939 6.56 3.635.51.489 1.07 1.023 1.74 1.604zm-13.48-10.845a749.15 749.15 0 00-.41-4.714l-.01.003c-1.86-2.166-1.67-.811-1.48.544.16 1.123.32 2.246-.69 1.365.18.909.99 1.533 1.8 2.164.27.208.54.418.79.638zm-34.58-31.197c-.38-1.788-.77-3.577-2.6-4.752l-.01.003c.15 1.554.29 3.134.43 4.72.77.724 1.1.691 1.43.659.25-.025.5-.05.96.274-.08-.299-.14-.601-.21-.904zm-19.21-20.148c2.47 2.536 4.09 5.418 4.58 8.79-3.34-2.083-5.72-7.829-4.58-8.79zm51.28 40.665c-1.39-2.381-5.85-5.922-4.83-1.832 1.14.627 2.14 1.654 2.93 2.468 1.34 1.372 2.09 2.137 1.9-.636zm-42.37-42.524c-.66.183-1.75-.4-3.19-1.575v.007c.14 1.972-.77 2.132-1.67 2.292-.88.155-1.75.309-1.69 2.089 1.42 1.047 1.99.747 2.49.489.63-.327 1.13-.587 3.05 2.028-1.18-3.549-.76-3.716-.11-3.973.39-.157.87-.347 1.12-1.357zm6.21 1.9c1.26-1.047 2.64-2.202 5.38.009-3.46-6.333-5.96-3.983-7.97-2.088-.72.674-1.37 1.29-1.99 1.437 2.21 2.623 3.33 1.687 4.58.642zm3.18.95v-.006.006zm1.02 2.14c-.46-.658-.93-1.316-1.02-2.14-1.39-.428-1.28.154-1.09 1.072.14.694.31 1.581-.1 2.367 1.16 1.13 1.72 1.212 2.27 1.294.28.043.57.085.94.27.19-1.182-.4-2.021-1-2.863zm12.34-9.404c-.32.355-.65.709-1.95-.589.2.688.57 1.299.94 1.911.41.681.82 1.364 1 2.156 1.57.563 1.92-.887 1.96-2.9-1.3-1.288-1.62-.933-1.95-.578zm61.21 36.404c.25-.404.48-.766.69-.961-.94-1.631-1.52-1.109-2.23-.468-.75.674-1.65 1.48-3.26.055 2.26 5.367 3.75 3.02 4.8 1.374zm90.51 65.491c2.59 1.623 1.69-4.18-1.41-5.354-.22 1.199.23 2.061.68 2.92.39.741.78 1.481.73 2.434zm-95.63-68.988c1.39.735 2.78 1.468 2.32-.808-.55-.786-1.22-1.515-1.88-2.245-1.04-1.126-2.07-2.253-2.65-3.591-.94-.397-.35.995.34 2.632.56 1.323 1.19 2.807 1.13 3.635.24.114.49.246.74.377zm1.38-2.352c2.76 1.654 1-4.631-2.22-5.918-.31 2.025.64 3.472 1.58 4.915.22.332.44.664.64 1.003zm-36.88-34.769c-.29.715-.58 1.43-2.55-.618v.004l.65 5.65c.19-.094.39-.183.58-.272 1.62-.743 3.24-1.485 3.44-4.563-1.59-1.523-1.86-.862-2.12-.201zm31.28 26.434l-.43-3.758c-.25-.014-.56-.103-.86-.192-1.04-.307-2.08-.613-1.25 2.139l2.54 1.811zm17.97 8.047c-1.42-1.238-2.51-2.181-2.21.535 1.96 2.938 5.19 3.239 5.6 1.315-1.01.218-2.3-.902-3.39-1.85zm4.99 2.09l-.01-.008v-.003c.01.003.01.007.01.011zm0 0c.04.098.11.249.2.437.61 1.335 2.06 4.507.25 3.374 1.92 4.017 3.89 1.676 2.92-1.468l-3.37-2.343zm86.73 56.501v-.005l-.01-.002c0 .002 0 .004.01.007zm-2.79-.393c1.18.53 2.37 1.06 2.79.393 1.14 1.18 1.59 2.71 2.05 4.242.32 1.103.65 2.206 1.23 3.182 1.62.923 2.86 1.264 3.3.365.22-1.37-.66-2.186-1.53-3.001-.77-.718-1.54-1.435-1.56-2.528.51-1.22 1.15-.194 1.95 1.102.72 1.146 1.57 2.503 2.56 2.702-.55-1.696-.25-2.062.06-2.429.27-.318.53-.636.23-1.828-2.25-1.936-3.1-1.742-3.95-1.549-.33.075-.65.15-1.06.103-.57-2.595.56-2.591 1.68-2.588 1.08.003 2.16.006 1.74-2.299 1.39.548 2.44 1.612 3.34 2.513 1.06 1.064 1.89 1.9 2.77 1.382-.75-2.212-1.14-2.315-1.99-2.534-.4-.106-.92-.24-1.63-.656-.59-2.365.65-1.941 1.89-1.518 1.39.472 2.77.943 1.63-2.423-7.41-.515-12.05 3.087-16.73 6.712l-.13.104c.42.134.89.344 1.36.553zm-93.54-67.645c-.9.588-1.79 1.176-4.29-.815.14 2.576.48 5.069 3.08 6.519-.23-1.831.38-2.303.99-2.774.36-.275.72-.551.9-1.1.56.662.91 1.424 1.27 2.185.66 1.421 1.33 2.841 3.29 3.614.85-1.41.1-2.682-.84-4.276-.59-1.017-1.27-2.165-1.66-3.565-1.38-.682-2.06-.235-2.74.212zm-37.33-26.155l-.12-1.024c-1.86-1.309-1.77.584-1.67 2.481.05.865.09 1.731-.05 2.294 2.54 1.906 2.19-.928 1.84-3.751zm66.56 52.088l-.45-3.816c-2.28-1.817-2.64-.579-2.1 2.067.43.301.88.605 1.32.909l1.23.84zm1.22-27.859c-1.47-1.033-2.11-.807-2.31.158v.01c-3.65-4.147-.62-4.68 1.36-5.03.43-.075.8-.141 1.06-.232-1.72-2.397-8.48-3.087-5.11 2.555-4.05-3.048-5.08-10.272-1.97-9.108-1.77-2.598-2.56-1.702-3.43-.719-.69.788-1.43 1.631-2.77.782.58 1.436 1.09 1.479 1.68 1.528.27.023.56.048.88.221.13 1.641-.33 2.375-.78 3.11-.41.672-.83 1.344-.8 2.711-1.15-.702-1.17-1.96-1.2-3.222-.01-.736-.03-1.473-.26-2.102-.76.691-1.22 2.086-1.7 3.518-1.06 3.207-2.19 6.604-6.79 2.721 1.48 4.545 1.57 7.577 1.67 11.309.03.932.06 1.907.11 2.96 1.89 1.408 1.89-.008 1.9-1.075 0-1.082 0-1.804 1.98 1.15.24-1.361-.57-4.854-2.63-8.78 1.88-2.847 4.13-5.069 9.85-1.886l-.54-4.091-.08-.646c.74.18 2.05 1.271 3.49 2.463 2.92 2.421 6.34 5.255 6.39 1.695zm-5.64-13.313c-.99.523-1.22 3.403-.1 5.089.34-.044.69-.061 1.05-.078 1.67-.079 3.34-.159 3.2-3.049-1.01-1.04-1.8-.891-2.45-.768-.7.132-1.23.232-1.7-1.194zm-61.31-10.387c1.18-1.869 3.31-2.181 5.92-1.731-.75 2.336-2.7 5.835-5.92 1.731zm30.84 20.472c.17-.286.34-.572.81-.377-.58-2.315-5.1-4.772-3.16-.515 1.76 1.866 2.05 1.378 2.35.892zm5.4-.633c2.21 4.21 4.21 1.716 3.74-.866-.94-.358-1.41.038-1.87.433-.47.396-.93.791-1.87.433zm-27.54-23.855c1.43 2.293.66 4.909-1.95 2.887-.76-3.245.57-3.096 1.95-2.887zm142.8 92.568c.27-.498.55-1.023-.33-2.616-2.64-1.63-1.67 5.214 1.62 6.319-1.94-2.509-1.63-3.086-1.29-3.703zm4.09-1.946c.22-1.049.4-1.889-.79-3.667-2.43-1.295-2.12 1.51-1.8 4.345.06.565.12 1.132.16 1.667.47.298.94.6 1.42.9.4.259.81.517 1.22.772-.63-2.016-.41-3.097-.21-4.017zm-105.54-71.148c.08-.233.16-.437.23-.581-1.47-.984-1.95-.404-2.43.175-.33.404-.67.808-1.34.68 2.02 3.775 2.99 1.183 3.54-.274zm-21.68-20.982c.58 2.666.24 3.867-2.02 2.033-.56-2.653-.22-3.854 2.02-2.033zm43.69 20.64c-.43.32-.86.64-1.45.726.01 1.065.75 1.768 1.49 2.47.64.617 1.29 1.233 1.44 2.093 1.66.092 1.25-1.312.67-3.269-.25-.839-.52-1.78-.68-2.749-.6.08-1.03.404-1.47.729zm14.01 5.106c-.67.227-1.82-.289-3.33-1.363l.01.006c.18 1.278.56 2.457.94 3.637.56 1.713 1.11 3.428 1.06 5.453 1.77 1.639 2.29 1.355 2.82 1.07.32-.173.63-.347 1.24-.087.05-.733-.94-2.192-1.81-3.474-1.22-1.805-2.21-3.256.31-1.826.07-.694-.46-1.119-.98-1.514-.09-.626-.17-1.266-.26-1.902zm-15.46-12.063c-.39-.263-.79-.526-1.14-.808.52 1.988-.12 2.145-.89 2.334-.7.169-1.5.365-1.65 1.941 2.81 2.251 3.49 1.194 4.18.136.44-.688.88-1.377 1.92-1.153-.33-1.058-1.38-1.756-2.42-2.45zm268.74 165.875c-.22-2.706-.65-5.288-3.48-6.378l-.01.003c-1.07.585-.59 3.958.86 5.888.67-.139 1.35-.237 2.63.487zm-164.34-103.018c-2.88-1.723-4.6-1.737-4.78.529 3.06-.029 3.99 2.898 4.95 6.553 3.04 1.415 2.08-.984.99-3.704-.46-1.158-.95-2.373-1.16-3.378zm-86.86-58.031c1.07 2.166 3.11 4.6 4.44 3.79-1.48-3.61-1.08-3.571.38-3.427.59.058 1.36.133 2.24-.008-.61-1.919-.45-2.934-.32-3.763.15-.886.25-1.559-.65-2.897-2.51-1.793-4.28.095-1.17 2.662-.45.338-.99.196-1.39.088-.72-.191-1.03-.275.22 2.593-.32-.405-.57-.854-.81-1.303-.48-.877-.95-1.753-2.03-2.293-1.39 1.087-1.35 1.415.68 4.761-2.05-1.655-3.84-3.422-4.72-5.707-.16 3.964-2.33 7.95-7.7 6.027 5.16 5.198 10.7 4.481 10.83-.523zm-65.33-47.952c-.01-.002-.01-.004-.01-.006l.01.003v.003zm0 0c.44 1.792 1.74 3.148 3.63 4.235-.01-.396-.12-.939-.22-1.481-.31-1.567-.62-3.128 1.37-1.107-.79-1.838-.86-3.033-.92-4.007-.08-1.464-.14-2.431-2.56-4.331-.27.32-.05 1.487.2 2.784.47 2.447 1.03 5.356-1.5 3.907zm63.94 38.445c-1.18.174-2.36.347-3.87.018.75 3.092 2.3 3.152 3.36 3.192.59.023 1.03.04 1.07.595 1.19-.424 1.72-1.828 1.74-4.008-.82-.014-1.56.094-2.3.203zm-43.33-27.517c2.34 1.597 1.38-3.04.03-5.046-1.16-.741-.78.913-.39 2.574.23.99.45 1.982.36 2.472zm-11.97-16.718c.26-.433.53-.864.01-2.508l-.01.004c-3.18-1.169-3.04 1.836-2.91 4.601.03.639.06 1.266.05 1.824 1.93 3.12 3.42 2.596 4.74 2.138.57-.199 1.11-.387 1.63-.255-.77-2.176-1.14-2.296-1.95-2.553-.39-.125-.89-.283-1.58-.726-.51-1.656-.24-2.092.02-2.525zm67.35 47.153c-.34.756-.76 1.698-2.28.068 1.98 5.188 3.58 2.303 4.03.92-1.16-2.302-1.41-1.736-1.75-.988zM1283 337.758c.68-.547 1.36-1.092 1.73-2.108-2.58-3.698-4.54 1.612-3.27 3.785.39-.751.97-1.214 1.54-1.677zm-28.53-20.077c-.56-1.065-1.11-2.14-1.27-3.408l-.01.004c-1.83-1.41-2.18-.52-2.52.368-.3.769-.6 1.536-1.86.807 1.07 2.073 2.3 1.995 3.53 1.917.72-.046 1.45-.091 2.13.312zm65.87 38.251c1.99-1.631 4.06-3.331 4.58-5.848-2.24-.536-3.52.631-4.76 1.763-1.84 1.687-3.6 3.297-8.37-.924 3.18 3.848 3.29 5.092 3.47 7.149.06.612.12 1.296.27 2.142 1.7.781 2.31.29 2.8-.098.67-.54 1.09-.878 3.84 2.698-.09-1.971-1.59-3.199-3.09-4.426a38.26 38.26 0 01-.8-.67c.62-.61 1.33-1.194 2.06-1.786zm253.37 149.87c.18-.843.35-1.685 1.25-1.546-2.35-3.61-3.7.61-3.9 2.445 2.17 1.438 2.41.269 2.65-.899zM1410.37 409.1c0-.004-.01-.009-.01-.014v-.003c.01.006.01.012.01.017zm0 0c.16.24.3.467.44.681 2.26 3.503 2.51 3.883 2.52 8.036 3.85 1.307 3.9-6.332-.75-10.023.93 2.815.19 3.233-2.21 1.306zm-149.08-96.842v-.004l.01.006-.01-.002zm-3.84 2.438c1.63-.289 3.25-.578 3.84-2.438-1.51-.527-2.44-.16-3.37.207-.94.373-1.88.746-3.44.175.14.935.28 1.87.43 2.817.73-.438 1.63-.599 2.54-.761zm89.23 57.546c.45-.28.85-.535 1.22-.656-2.34-3.127-3.47-2.139-4.62-1.138-.73.641-1.47 1.287-2.54.866 2.41 3.156 4.4 1.899 5.94.928zm-8.35-13.621c-1.04.536-2.07 1.07-4.26-.113.72 1.157 1.75 3.464.44 2.842 2.42 1.88 7.7 1.633 7.41-2.949-1.75-.736-2.67-.257-3.59.22zm-73.8-47.477c-.65-.538-1.31-1.075-1.43-1.879l-.01-.006c-1.83-1.12-1.44 1.134-.95 3.563 1.53 1.271 1.93.795 2.32.318.31-.373.61-.746 1.47-.282-.19-.716-.8-1.216-1.4-1.714zm4.21.231c1.97.599 3.34.288 2.5-3.374-1.69-.836-1.91.529-2.14 1.894-.09.538-.18 1.076-.36 1.48zm63.19 36.989c-2.63-2.056-4.36-3.407-4.12.162v.006c2.54.099 4.43.83 6.25 4.85.59.374 1.15.737 1.72 1.099l.01.01c-.79-1.255-1.26-2.719-1.44-4.315-.86-.597-1.67-1.233-2.42-1.812zm-69.26-50.359c-.51.606-1.03 1.213-1.94 1.244v.003c.25.443.49.891.73 1.34 1.05 1.94 2.09 3.879 4.12 5.29.15-1.563-.58-2.657-1.32-3.752-.44-.666-.89-1.333-1.14-2.106.38-.76.92.154 1.68 1.43.91 1.529 2.12 3.578 3.73 3.895.64-.59.05-1.986-.56-3.458-.66-1.559-1.35-3.204-.66-4.072-2.85-1.903-3.74-.859-4.64.186zm15.09 12.682c-.08-2.003-.72-3.711-1.62-5.286-.97.086-1.19.905-1.37 1.604-.24.906-.43 1.61-2.13.261.09.617.2 1.236.3 1.875 1.28 1.047 1.69.774 2.09.5.47-.314.94-.629 2.73 1.046zm62.05 35.427c.37-1.24.73-2.433 1.28-2.717-2.19-1.24-2.57.147-2.96 1.534-.37 1.352-.75 2.705-2.79 1.628 2.84 4.982 3.69 2.164 4.47-.445zm20.24 3.138c.09 2.047-.21 3.535-1.82 3.133-1.56-2.222-.92-4.969 1.82-3.133zm-8.41-6.544c.95-1.01 1.89-2.004 3.06-1.866-2.13-1.961-3.43-1.085-4.87-.121-1.34.906-2.8 1.89-5.18.658 3.42 5.122 5.23 3.199 6.99 1.329zm28.63 9.564c.93 3.238-.43 3.216-1.83 3.141-1.67-2.362-.33-4.104 1.83-3.141zm-4.55-8.676c-.98 1.019-1.96 2.037-5.81-1.018l.01.006c-.15 1.197.34 2.032.83 2.867.42.723.84 1.446.86 2.403.7-.368 1.78-.501 2.89-.64 2.69-.332 5.6-.693 4.13-4.422-1.57-.582-2.24.111-2.91.804zm-5.57-6.567c.44 1.827.85 3.561-1.61 2.166v-.006c1.72 4.427 3.74 2.498 4.74 1.54.08-.074.15-.142.21-.202-.54-1.308-.94-2.021-1.24-2.54-.57-.996-.73-1.276-.64-3.683-2.37-1.019-1.91.899-1.46 2.725zm11.62-3.303c1.07 3.623.11 4.414-1.65 4.066-1.89-2.742-2.17-7.207 1.65-4.066zm-64.16-38.859c1.23.045 2.47.09 1.26-3.379-1.71-.791-1.91.59-2.1 1.972-.08.543-.16 1.087-.33 1.499.33-.123.75-.108 1.17-.092zm-5.32-5.894c1.73 3.327 4.69 2.625 3.81-.076-2.07-1.927-2.3-1.191-2.52-.455-.17.553-.34 1.106-1.29.531zm16.72 7.412c-.87-.63-1.74-1.258-1.99-2.245l.01-.01c-1.51-.048-.61 1.348.4 2.917.76 1.175 1.58 2.446 1.49 3.28 2.35 1.739 2.63.5 1.93-2.12-.36-.754-1.1-1.288-1.84-1.822zm-12.71-16.166c-.2-.626-.38-1.149-.57-1.514-3.11-.878-1.28 2.109 1 5.831 1.89 3.086 4.09 6.678 4.04 8.991 2.75 1.355 2.67-1.362 2.58-4.076-.03-.92-.06-1.84.02-2.601-2.18-1.486-4.24-2.833-3.98-.844-1.84-1.989-2.57-4.213-3.09-5.787zm84.62 58.986c1.51 3.159 4.73 2.845 3.02-.769-1.77-1.478-1.94-.742-2.11-.008-.12.515-.24 1.03-.91.777zm-72.16-52.285c.71 2.613.44 3.843-1.9 2.125-.71-2.606-.43-3.836 1.9-2.125zm66.12 36.903c-3.44-3.678-5.74-.707-6.88.995l.01-.008c2.02-.455 2.81.469 4.08 1.945.9 1.053 2.05 2.386 4.05 3.701l-.9-4.758c.54.46.87 1.055 1.19 1.65.55 1.005 1.1 2.006 2.72 2.346-3.06-5.102-1.99-5.696 1.39-5.076-1.08-1.485-.95-1.793-.77-2.256.16-.408.37-.936-.16-2.498l-1.53-.895c-.67-.393-1.34-.786-2.02-1.182 2.14 3.136 1.19 3.56.14 4.027-.74.329-1.53.678-1.32 2.009zm8.67 4.238c3.49 2.395 5.48-3.394.32-6.547v.004c1.61 3.926 1.17 3.656.14 3.02-.8-.489-1.95-1.196-2.79-.365.27.682.7 1.259 1.13 1.835.47.637.95 1.274 1.2 2.053zm4.85-7.068c-.67-2.813-1.34-5.63-4.42-6.975l-.01.004c1.68 3.152 1.63 3.905 1.58 4.783-.05.767-.1 1.629.98 4.268 1.16.925 1.31.479 1.46.033.12-.351.23-.703.85-.381-.16-.568-.3-1.15-.44-1.732zm16.82 4.963c.01-.17.01-.341.01-.508-2.49-1.27-3.48-.534-2.65 2.678 2.6 1.605 2.62-.29 2.64-2.17zm241.49 96.539c-2.63-3.825-4.19-.317-3.88 1.783v-.003c1.01 1.14 2.36 2.012 3.99 2.676-1.56-1.818-2.5-5.433-.11-4.456zM1435.1 307.583c4.47 6.215 5.67-2.687.39-5.642-.66.794-.15 2.27.28 3.528.5 1.437.89 2.59-.67 2.114zm240.23 107.319c-5.2-4.76-6.95.716-7.78 3.577v-.004.004c3.34 3.913 4.39 1.058 2.72-3.194.66.67 1.26 1.385 1.87 2.101 1.55 1.837 3.11 3.675 5.64 4.753-.23-1.096-1.29-2.426-2.26-3.646-1.57-1.96-2.9-3.635-.19-3.591zm1.93-4.51c.24-.546.47-1.093 1.11-1.152-1.55-4.427-6.83-1.959-2.95 2.204 1.23.387 1.54-.332 1.84-1.052zM1386.58 246.13c2.47 1.059 3.59.337 3.37-2.146l-.18-.066c-2.28-.856-4.53-1.699-3.19 2.212zm6.06-10.417c.01.013.01.027.02.041l.01-.014-.03-.027zm.61-2.426c-1.52-.839-2.83-1.568-.61 2.426-3.57-3.102-6.69.167-5.32 3.477 2.75 3.278 3.82 1.399 4.73-.194.34-.601.66-1.161 1.04-1.388 4.05 7.119 4.55 3.667 5.09-.046.37-2.534.75-5.191 2.3-4.691-.88-2.279-4.9-3.394-5.35-3.027.05 1.144.55 2.008 1.05 2.865.26.451.52.9.71 1.387-2.01-1.028-2.72-2.903-3.43-4.78-.7-1.88-1.41-3.763-3.44-4.8.26.588.47 1.243.71 1.944.77 2.3 1.7 5.085 4.61 7.567-.42.185-1.28-.293-2.09-.74zm-6.97-.93c1.57-.891 3.15-1.786 2.86-5.098-3.28-1.234-3.31 1.76-3.34 4.77 0 .203 0 .406-.01.608.16-.095.33-.187.49-.28zm308.91 144.919c-.4 1.391-.82 2.834-2.12 2.617 3.35 3.168 4 1.004 4.64-1.103.32-1.083.65-2.152 1.33-2.473-2.78-2.782-3.3-.961-3.85.959zm-302.27-156.754v-.008.008zm0 0c-.03.419-.12.814-.21 1.206-.36 1.581-.71 3.1 2.66 5.824-2.18-4.045-2.04-5.232 1.34-4.082-.43-1.031-1.53-1.645-2.62-2.255-.4-.225-.8-.449-1.17-.693zm14.16 3.864c-.16.499-.32.998-1.67-.044 1.27 3.56 4.3 2.819 4.01.629-1.97-1.728-2.16-1.156-2.34-.585zm-12.62-10.233c-.61-1.762-2.05-2.993-4.01-3.876v.006c.38 1.553.75 3.09 1.12 4.643.65.352.57-.217.5-.786a2.993 2.993 0 01-.05-.568c-.1-.767.76-.303 1.62.162.28.154.57.309.82.419zm316.11 151.506c-.28-.436-.57-.873-.78-1.361l.01-.004c-3.16-1.281-2.99 1.224-2.39 4.212.66.136.97-.127 1.28-.389.5-.428 1.01-.855 3.03.439-.07-1.21-.61-2.053-1.15-2.897zm-291.95-149.646c-.77.044-1.73.098-2.31-.91l-.01-.006c-.3.218-.14.936.08 1.915.2.909.46 2.045.44 3.215 1.92.301 3.05-.405 4.19-1.113.68-.422 1.35-.845 2.2-1.056-1.15-4.019-4.84-7.513-6.07-5.721.3.654.75 1.208 1.19 1.761.51.626 1.02 1.253 1.31 2.027-.22-.157-.59-.136-1.02-.112zm300.81 147.931c.55.223 1.09.445 1.63.665-.17-3.41-5.53-5.604-3.84-1.574l2.21.909zm-288.8-153.157c-2.92-1.737-3.48-.531-3.52 1.314.71.237.94.829 1.14 1.427 1.91.496 3.32.341 2.38-2.741zm298.07 130.1c-.44.17-.88.339-1.58.217v-.006c.02 1.747.93 2.759 1.84 3.773.91 1.013 1.83 2.029 1.86 3.783 1.57.972 1.83.488 2.1.004.2-.381.41-.761 1.25-.44a12.42 12.42 0 00-3.67-4.055c-.29-2.143 2.03-1.237 2.91 1.143 1.68.118.59-1.076-.54-2.298-.68-.744-1.37-1.499-1.45-1.972-1.44-.641-2.08-.394-2.72-.149zm-294.9-143.79c-.75.674-1.51 1.36-2.53 1.198l.01.003c2.83 3.171 5.9 6.195 9.32 8.985-1.78-2.148-1.74-3.785-1.71-5.31.01-.409.02-.81 0-1.21-.8-.469-1.2-.457-1.6-.446-.43.012-.86.024-1.77-.556.38-.258.56-.773.73-1.288.28-.818.55-1.636 1.66-1.428-1.89-1.94-2.98-.957-4.11.052zm301.81 140.263c-.52-.69-1.04-1.381-1.41-2.201-3.52-.561.66 4.407 3.21 5.588-.29-1.393-1.05-2.391-1.8-3.387zm-286.13-131.792c-1.99-3.387-4.35-6.52-8.45-8.472l-.01.004c.27 2.12 1.73 3.413 3.2 4.706 1.1.971 2.2 1.942 2.79 3.262.6-.109 1.22-.193 2.47.5zm290.96 130.261c.96 2.633.77 4.035-1.87 2.758-.94-2.634-.76-4.033 1.87-2.758zM1443.23 192.72c.12-.217.24-.434.36-.646v-.007c-1.65-1.156-1.52-.182-1.32.893-1.01-.4-1.68-1.062-2.06-1.881-.73.521-.86 1.215-1.03 2.064-.13.727-.3 1.567-.87 2.509 2.98.66 3.95-1.136 4.92-2.932zm7.12 2.381c-.73-.581-1.47-1.163-1.82-2.016v.001l-.01-.006c.01.001.01.003.01.005-1.3-.269-2.01.175-2.73.618-.65.404-1.29.807-2.38.671.24 1.297.94 2.281 1.64 3.265s1.4 1.968 1.64 3.265c3.27.43 5.63-.265 5.03-4.542-.38-.475-.88-.868-1.38-1.261zM1745 323.718c0-.002-.01-.004-.01-.006l.01.006zm0 0c-1.19.099-1.52 1.127-1.85 2.156-.43 1.323-.86 2.647-3.1 2.003.66 1.571 1.29 1.644 1.96 1.721.87.1 1.81.207 2.95 3.619.9.055 1.22-.515 1.54-1.086.39-.704.78-1.409 2.26-.94-2.3-1.605-3.2-4.397-3.76-7.473zm-298.52-138.475c-.57-.545-1.15-1.09-2.01-1.449l.02.009c1.17 1.906 1.63 2.569 1.9 3.303.27.724.36 1.519.74 3.649.92.476 1.82.949 2.73 1.425-.28-1.279-.57-2.548-1.66-3.251-.11-.76.75-.328 1.63.108.29.144.58.289.84.392-.4-2.286-.97-4.5-3.73-5.129.17.37.41.683.65.996.31.406.62.81.76 1.333-.77-.358-1.32-.872-1.87-1.386zm12.4 3.238c-1.01.125-2.01.249-1.77 1.876 3.53 4.091 5.05 1.923 3.23-2.341-.35.327-.91.396-1.46.465zm.94-9.981c-.07.402-.13.804-.27 1.13.87.86 1.74 1.716 2.98 2.324-.2-.682-.82-1.063-1.43-1.443-.22-.136-.44-.271-.64-.421.12-.432.56-.479 1.01-.525.83-.087 1.65-.173.39-2.741-1.65-.606-1.85.535-2.04 1.676zm9.53 1.309c.35-.496.7-.992 1.71-.692-2.66-3.505-5.08-1.543-4.4.954 1.81.996 2.25.367 2.69-.262zm2.62-.231c.46 1.156.27 1.556-.38 1.398.23.521.68.891 1.12 1.26.51.425 1.02.849 1.21 1.503.53.136.77-.062 1.02-.259.28-.218.55-.436 1.2-.205-.68-1.744-2.18-2.918-4.17-3.697zm3.64 1.833c3.11.381 5.17-.501 4-5.157-.24 1.105-1.14 1.426-2.03 1.747-1.25.443-2.49.886-1.97 3.41zm-1.51-13.469c3.49 5.279.92 6.132-1.25 6.51-1.22-4.11-1.72-7.342 1.25-6.51zm17 4.11c-.39-.35-.78-.701-1.23-1.01 1.56 1.872 1.73 4.962.31 6.502 4.2 2.226 4.43-.171 3.62-3.771-1.18-.36-1.94-1.039-2.7-1.721zm9.79-24.591c-.47.151-.77.246-1.24-.977-.64-.174-.36.285-.08.743.3.494.6.986-.26.682 1.3 2.383 5.37 2.943 4.01.331-1.27-1.155-1.95-.937-2.43-.779zm112.3-115.542l-.01-.001.01-.004v.005zm0 0c1.88 2.444 2.5 3.14 2.69 3.938.12.531.05 1.108.02 2.275l2.83 1.032.06-.172c.58-1.513 1.16-3.028-.71-6.784-2.02-.956-2.73-.718-3.45-.48-.41.134-.81.267-1.44.191zm10.06-4.966c.19-.76.44-1.73 2.44-.947-2.06-2.153-2.92-1.586-3.8-1.005-.55.368-1.12.74-2.01.43 2.98 3.037 3.14 2.43 3.37 1.522z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_58_843)"><g clip-path="url(#prefix__clip1_58_843)"><path fill-rule="evenodd" clip-rule="evenodd" d="M1024.54 670.149c1.23-.376.84-2.464-1.31-4.269.22-1.001 1.47.499 2.4 1.189.79-2.4-.52-4.205-.33-6.427 2.58-.6 5.75.147 6.92-3.802 6.7 7.51 6.64-.801 10.75.66 2.7 6.686-3.72 3.762-1.54 9.733 3.59-3.986 8.98 1.052 11.34-6.941 2.87 3.264 5.08 6.114 4.99.45 2.74 3.275 3.96 7.531 2.7 9.446 1.71 2.089 1.25-.806 2.5.218-2.06-2.528-.52-6.051-3.61-8.254 4.86 3.431 5.15-3.571 8.59-3.374-2.34 4.619-3.25 8.92-3.99 9.492 3.94 5.642 5.18-4.291 7.16-6.681 2.11 4.209 4.54 2.176 5.81 1.119-.14-1.221-1.54-2.03-1.48-3.297.21-.645.86-.268.9-1.226.77 1.326.69 2.909 2.27 3.98 3.57-1.005 5.57-5.508 8.73-7.407.63.718.74 1.626 1.54 2.306-2.98 2.923-2.9 6.199-5.51 9.246 4.64 2.786 6.13-1.438 10.03-.332-.36-3.31-1.75-.552-3.19-2.743-.23-2.299.67-2.067.99-3.144 3.06 1.789 3.58 6.408 5.55 5.784.19-1.631-.87-2.86-.6-4.499-.78-.656-.68.615-.9 1.206-1.84-1.585-4.61-8.168-3.2-2.752-4.54-3.666-2.46-6.57-.49-6.411-.96 3.195 3.76 2.137 3.84 6.29 1.82 1.148.28-5.029 1.9-4.338 2.69 2.735.66 5.88-.35 6.681-.05 2.512 3.04 4.026 3 6.557 2.51 1.593 2.35-2.686 5.06-.651 1.59 3.637-.53 3.081.44 7.368 3.72 4.205 2.39-2.63 5.84.984-.6-3.223-2.59-.814-4.83-4.122 1.33-.305 2.33-1.324 4.23-.384-3.46-2.727-3.32-4.968-4.58-8.882.18-.646.84-.267.9-1.223 2.66 1.041.45 3.6 3.12 4.657-.63-5.94 11.16-5.184 13.39 1.403-.27-3.347 1.93-2.253.26-5.726 3.42 1.745 3.07 4.662 5.6 6.677-2.54-.15-3.11 3.874-6.05 2.859 2.98 3.195 8.17 2.007 10.16-.391 1.61 1.355.92 3.468 3.17 4.622.65-3.081 1.15-6.554 6.02-2.965-1.77-4.268.29-4.338-3.04-8.454 2.94 3.088 1.98-2.774 5.86-.117-4.09-5.497 2.29-3.663.25-7.685 4.25 1.377.58 5.495 3.95 7.182-.32-3.766.86-4.377 2.63-3.763 1.09 1.853 1.57 3.902 2.34 5.841-3.34 3.662-9.05 2.055-10.34 7.376 2.48 2.777 1.12-2.497 4.15 1.419 1.05-11.225 13.55 1.301 15.42-8.178 9.41 6.268 15.03-5.869 21.93 1.889 2.02.019 3.75-.542 5.94-.182-2.18-6.45 2.94-3.158 4.3-5.438.85 1.29.83 2.898 2.47 3.883-1.99-4.29.94-5.581 2.56-1.92 2.17-6.642 10.4-1.358 13.55-6.089-2.52-1.764-.91-4.116-.79-5.47 1.74 1.582.19 4.41 1.63 6.115 5.99 2.445 7.27-4.232 11.03-6.09 3.06 6.083 8 4.567 6.77.308 2.38 2.605 2.18.305 4.22 2.237-1.28-4.821 1.91-4.92 5.52-5.06 3.57-.18 7.59-.354 7.94-5.137 8.49 7.333 7.39-10.026 15.14-2.318-1.73-.37-2.32 1.409-3.35 2.347 3.34 3.993 4.12-3.96 8.41-3.439 1.74.201 9.2 3.056 6.73-1.805-.18-2.196 3.02 1.744 4.27 2.13 1.21-4.367 5.69-2.804 6.64-7.597 5.22 1.663 11.96-2.671 17.57-4.713 75.34-21.861 144.65-57.189 208.24-100.59 63.62-43.467 121.68-94.805 175.83-149.855 2.43.76 2.95 3.073 3.81 5.114-12.74 2.924-14.39 18.888-26.65 22.343 1.78 3.394 1 3.418.34 5.401-1.5-1.05.11-1.067-.65-2.924-2.82-.529.1 5.86-4.4 3.294.45 2.685 3.34 3.547 3.91 6.15-9.53-3.602-13.53 3.702-15.54 11.42-4.96 2.143-10.75 3.234-11.62 10.411-1.07-.353-1.71-.987-2.09-1.851-2.98 9.18-16.1 11.092-15.71 19.978-2.55-1.133-2.21-2.548-3.75-1.798.37 1.22 1.87 1.647 2.99 2.327-11.81 1.788-11.33 15.662-24.42 16.396 2.93 5.284-4.71 4.015-6.72 5.543-3.36 2.565-2.05 9.187-6.55 6.517-.58 5.31-3.37 7.739-9.19 6.081-.65 2.539 3.13 2.302 2.48 4.851.91 1.92-4.15-.707-3.73-1.943.83 5.22-3.74 3.175-4.08 6.832-1.53-.483-1.87-1.675-2.16-2.925.22 5.691-4.38 4.868-5.49 8.778-4.37-5.287-.63-.439-4.49-1.468 1.39 1.905 1.73 4.238.74 4.885-.95-1.198-2.27-2.164-3.86-2.938 2.31 6.747-6.94 1.795-6.15 10.241-1.57-.78-2.5-1.992-3.05-3.422-3.14 4.784-14.63 10.362-13.27 14.53-1.75 2.309-7.53-1.13-6.1 5.683-4.65-2.128-9.93-1.685-8.2 2.697-16.33 6.751-26.57 19.571-43.79 26.065-.09 1.422 1.03 2.174 1.21 3.459-2.7-1.471-2.57-.414-6.15-1.788-4.94 9.734-22.11 7.947-26.9 20.764-1.59-.857-2-2.347-3.67-3.174-3.23 2.867-5.62 7.067-10.48 7.397 1.58 2.109 6.76 8.167 4.38 11.997-3.05-2.466-3.3-4.77-6.35-4.866-2.12-2.058-1.35-6.595 1.39-3.784-.9-4.491-6.11-3.844-3.42-.268-8.77-3.034-8.54 8.196-17.45 4.884 2.23 5.933-2.26 6.104-7.41 2.767-.21 1.455.82 2.298 1.9 3.091-6.16-.142-9.6 4.152-15.94 3.71.39 1.479 1.66 2.546 1.95 4.072-8.26.735-14.06 10.913-23.42 6.215.07.958 1.11 1.461 1.79 2.132-8.19 2.251-13.94 8.694-24.25 7.328-3.23 5.99-13.95 6.77-20.91 9.301-.55 2.541 2.45 3.515 1.89 6.06-.36.603-.71 1.221-1.68.749-3.38-9.418-7.43-1.666-11.1-2.98 1.67 4.629 2.33-.051 4.3 2.064 2.64 3.014-.52 3.277-.74 4.262-8.58-8.959-10.49.017-17.01-2.447-.59 2.51 2.4 3.556 1.8 6.057-.19.904-1.69-.562-1.68.711-1.18-1.136-.77-2.94-.94-4.486-2.42-1.567-3.64-.95-3.37 2.362.82 1.606 3.19 2.582 2.63 4.746 1.73 1.237 1.06-1.852 3.42.544.39 3.83-3.45.03-2.49 4.927-3.98-4.071-4.15-1.237-6-1.431-2.54-3.774 3.25-.316 2.51-3.937-5.67-4.144.03-2.524-.08-4.852-7.22-3.965-3.23 2.363-9.35-.035.16 1.889 2.9 2.677 3.45 4.417-.01.915-1.05-.01-1.71-.276.51 2.352 1.37 3.025 2.59 2.812.34 5.363-2.71 4.546-2.45 9.784-2.19-1.07-2.99-.885-4.29-4.06-1.8.81-.07 8.186-3.37 6.244-1.84-1.185-.4-3.737-.88-5.474-2.59-1.607-1.57 2.918.03 4.845.04 2.98-4.3-2.194-4.25.776-1.35-1.393-.75-3.589-3.43-4.441 1.38 6.898-4.31 11.102-7.61 5.065-1.58 1.248-4.89-.802-4.23 4.657-5.76-5.528-5.18 3.376-9.4-2.202 2.8 10.462-10.57-2.028-10.32 6.806-4.66-.762-6.92 3.144-11.95 1.639-2.35-1.803 2.05-1.086-1.64-3.205-1.84 9.747-12.83 1.465-16.27 8.069 1.48 1.286 1.79.178 3.35 1.615.9.937.54 2.385 1.64 3.24-2.94.256-7.45-2.675-10.18-2.025.56 2.183 1.52 1.341 2.51 1.974 2.24 2.657-2.27.035-.98 4.144-1.21-2.094-2.07-2.77-3.34-2.633 2.35 10.003-8.43-.723-8.79 6.87-2.63-1.617-3.68-3.778-3.2-6.487-2.58-2.529-2.28.848-4.24-.41.21 1.494 2.05 2.45 3.29 3.596.52 3.846-1.08 3.339-1.88 4.457-1.45-1.393-1.83-3.168-1.52-5.181-1.49 4.183-5.88 2.452-8.67 3.967-1.28-1.454-.59-3.605-3.22-4.584 2.39 9.048-9.61 6.478-10.69 11.326-2.89-3.673-5.81-1.408-4.83-5.945-2.26-.223-1.35 2.066-.21 4.847-1.55 1.173-6-7.063-6.72-2.538 2.19 2.53 1.75-.547 3.37.779-.19 1.009.08 1.85.73 2.599-4.51 1.1-11.04-2.196-12.33 5.793-2.01-4.58-2.1-3.888-5.54-7.63-.19 2.877 2.72 4.773 4.72 6.949.78 7.439-6.49-2.497-6.22 3.867-4.06-2.157-1.68-6.369-4.58-8.863-.21 3.484 2.04 6.22 2.83 9.416-.29 3.502-3.18-2.611-3.37-.82-1.08-1.225-.52-2.952-.57-4.518-2.05-.652-3.32 2.499-1.16 5.076-5.59-4.54-3.24.435-8.22-4.916-1.36 4.166-2.21 2.994-6.09 1.934.62 2.308 3.2 4.001 2.1 6.846l4.04 3.435c1.65-.394-1.2-4.546 2.62-.83.41-1.38-.52-2.354-1.49-3.29 2.81-.607 10.36-5.154 12.59 3.519 1.75 1.642.59-3.1 1.12-4.107 3.04 2.854 3.11 5.966 2.07 7.785 1.39.73 5.64-1.168 2-4.383 1.3-1.888 4.87 1.134 4.55-4.272 1.69 1.655-.03 4.392 1.34 6.151 3.06 2.802 4.58-4.991 6.74 2.556 2.02 1.559.37-3.03-.65-3.556 1.52-.704 3.25-.945 5.98.918-.22-1.502-1.36-2.707-1.48-4.228.36-1.746 2.33-.051 2.69-1.791.72 1.32 1.15 2.758 1.48 4.222 2.24-4.553 6.13-5.546 10.57-5.462 2.1 1.95-1.22.749-.19 3.869 2.41 1.87 1.97-2.217 5.08 1.081-.86 2.437-2.53 3.204-5.99.194 1.98 7.355-6.5.978-7.22 6.163 6.87.299 11.02-3.514 16.52-3.707-.41-3.332-1.81-.485-3.3-2.654.59-5.534 6.24 6.016 6.83.47-1.25-.478-2.15-.233-3.39-.705-.9-4.694 2.8.2 1.91-4.482 1.79.973 1.52 2.646 1.52 4.222.87.059.95-1.54 2.57.056-2-5.833 7.09-5.216 11.37-4.923 2.69 3.611-2.82-1.07-1.82 2.526 5.3-3.443 11.12-5.785 18.3-5.44 1.13 1.19.61 2.955.71 4.503 3.9 3.396 2.47-2.715 1.05-5.117 2.12-1.874 2.39 4.773 5.85 5.544 1.5-2.723-3.63-3.162-3.27-5.515 3.78-1.021 4.83-7.398 11.23-3.224-1.59-6.181 1.35-4.169 2.73-6.809 2.39 1.003 2.15 3.035 4.19 4.189.97-4.505 4.14-4.676 6.97-5.548 2.21 2.783-1.21 2.893-1.79 3.557 3.5 2.594 5.58-.96 6.1-5.204 2.64 1.24 1.59 3.888 4.22 5.125.18 3.127-2.79 1.723-.97 5.181 5.63-1.815 8.11-5.384 10.55-11.712 3.68 1.466.98 5.392 4.2 7.047 4.39-2.233 8.5-4.986 15.54-2.254-.52-6.172 3.39-2.487 5.16-2.104-.02-1.962-2.33-2.945-1.71-5.161 6.18 3.898 9.24-7.983 13.74-.808 4.82-3.129 14.95-1.511 16.34-7.73 5.19 3.337 5.24-2.627 11.26 2.157-.63-5.266 3.43-2.102 2.46-7.961 2.44.895 2.28 2.95 4.37 4.003 9.99-4.313 18.78-10.713 31.61-10.177-.93-2.149-.95-4.489-2.69-3.726 9.85-.884 19.39-2.444 25.5-9.764.78-2.921 6.16 1.87 7.75.291-.27-1.203-1.8-1.81-1.87-3.115 3.47.383 7.83.759 9.26-4.437 1.78 1.145 3.56 2.299 5.34 3.443 5.99-8.401 16.4-9.487 26.16-11.762-.23-.901-.96-1.537-1.02-2.531 16.72-4.316 29.21-15.449 45.06-21.279-.93-6.001 11.79-7.269 17.99-10.922 3.35-1.977 4.03-6.376 9.12-3.991l-.37-3.935c5.4 4.599 3.89-5.049 10.23-1.513 3.24-9.129 17.31-8.725 22.22-18.012l3.69 2.021c6.55-11.579 21.3-11.502 27.43-23.603 7.73 1.686 6.9-8.546 14.56-6.982-.53-6.025 5.88-2.525 5.8-7.923 9.49-1.811 15.55-8.372 21.15-15.439 16.8-9.857 28.37-26.547 44.95-36.686l-.72-3.946c.09-1.212 1.65.711 2.07 1.873 6.18-8.692 12.38-12.891 18.4-21.902l3.8 1.745c1.18-1.624-3.64-5.6.77-4.989 1.5 1.067-.13 1.072.57 2.914 1.74.102 2.77-1.843.96-4.003 7.42-5.927 14.34-12.381 21.14-18.931 6.8-6.554 13.42-13.266 19.9-20.093 12.9-13.727 24.8-28.407 36.72-42.955 23.62-29.917 46.11-60.971 69.64-92.061-.3-.961-.6-1.94-.89-2.901 8.93-5.986 9.54-19.956 20.06-24.533.39-2.193-3.02-7.55 1.39-3.987-.97-2.911.46-7.504 4.76-7.416-.42-1.277-.86-2.572-1.29-3.861 2.63-2.569 5.99-4.47 5.74-9.727 7.41-5.183 11.49-13.643 16.05-21.555 4.56-7.94 9.5-15.453 18.44-19.56-.48-1.277-.96-2.547-1.44-3.833 5.12.411 7.23-4.753 4.95-8.606 1.77.275 2.57 1.627 4.39 1.82-4.44-5.374-.21-4.012 2.16-3.941-1.58-7.379 2.89-11.059 6.6-16.147 1.73.306 2.91 1.236 3.81 2.509 1.57-.659-4.01-5.365.77-5.083-.41-.929-1.55-.984-2.42-1.358-.37-2.197 3.11-1.244.75-5.066 1.58-.019 2.21 1.116 2.79 2.288-.85-2.827-1.07-7.152.74-9.516 4.41 1.73.96 2.852 2.39 5.736 0-7.636 1.75-13.792 2.48-20.724l5.06.864c2.97 2.887-.96-2.489-3.44-1.461-2.07-5.008.22-6.387-.24-11.394 3.48 1.805 4.25-2.005 5.49 1.776 2.07-.534-3.31-4.54 3.01-3.884-1.66-4.099-2.99-.723-4.06-.646-1.8-5.714 3.63-5.868 1.31-11.938 1.27.343 1.62 1.84 2.27 2.948 1.43-.137-.44-2.797 3.21-1.269-4.52-2.213-.67-2.66-3.03-6.613 10.11-3.316-.16-22.41 12.04-22.968-5.81-6.02-1.85-9.672-1.6-13.247 4.66 3.357-.98 4.486.97 7.936 1.87.33 1.59-.877 3.62-.436.49 3.778 3.28 9.249 3.14 12.62-7.52-5.605-3.94 6.294-3.75 8.334 6.71-2.292 9.1-12.212 5.25-21.284 1.53.452 1.96 2.404 2.8 3.818.68-3.258 3.97-8.756-2.69-13.442-.5-2.64 3.53.89 5.05.488-1.96-8.21-.18-11.379-2.71-20.572 3.76-6.6 7.34-13.437 3.38-25.034 3.23-3.189 2.74-12.002-.98-17.632 3.7-3.383 2.51-11.741 2.41-19.74-.14-8.061 1.06-15.54 9.06-18.31-11.12-2.68-2.61-9.919-11.76-14.165 1.79-2.001-6.29-9.539.5-8.695-2.35-7.183-2.71-13.165-1.03-18.046 1.69-4.865 5.42-8.63 10.93-11.591-2.09-2.543-4.15-5.18-7.09-5.876-.37-2.014 1.51-2.86 5.17-2.83-3.14-3.254-.89-3.739-.56-5.219-4.15-2.746-3.06-3.736-6.74-6.643-2.47-.154.62 2.471-1.18 2.653-3.37-8.755.16-18.489 10.93-22.21-5.54-3.54-6.02-6.795-3.86-9.313-10.26-11.168-14.52-21.371-16.64-30.506l5.92-.584c-5.52-1.368-2.74-5.67-5.1-8.829 1.16.255 2.32.493 3.25 1.349-.2-7.433.03-14.291-6.18-22.383-.09-7.656-.52-18.016-8.91-26.765.98-2.193-.48-6.269-2.57-10.368-2.22-4.149-5.15-8.349-6.74-10.82-5.39-8.338-9.56-15.447-17.59-22.556-.36 1.673-3.24.864-5.67 1.07-2.37-1.953-.09-2.5-2.45-4.426-2.04 1.672.96 3.506-2.78 3.756 5.41 3.826-3.44 5.114 2.52 7.681 1.47 1.535-3.71.973-4.94 1.64.75 3.154.76 6.078.39 8.87-3.64-3.815-3.63-5.051-9.88-10.546.06 2.706.14 5.652.21 8.754.08 3.111-.03 6.319.28 9.728.53 6.796 2.11 14.2 6.17 22.072.69 1.512-.73 2.223-2.72 2.695 1.33.609 2.76.933 4.23 1.165-.32 1.284.65 3.074 1.23 4.726-7.97-.97 3.01 8.595 7.53 13.025-3.18.127-5.22-2.733-7.49-4.96.62 1.567-2.25 2.457-5.2 1.205-2.9-3.463.34-2.644-4.27-5.96-2.79-.036 4.59 4.048-.3 3.159 1.04 2.142 3.07 1.814 4.25 3.62.15.693.32 1.392-.64 1.633-2.73-1.103-4.77-3.944-7.51-4.974-.35-.614.33-.801-.33-1.537 4.68 2.915 1.99-2.123 1.56-4.031 1.62-.256 2.85.718 4.48.388-1.57-4.708-9.87-8.59-10.67-12.519 3.14.8 5.14 5.015 7.8 7.181.53-1.477-.42-5.337-4.52-5.917 3.87-2.985-2.13-9.721-5.16-15.183-3.24 1.519 2.6 6.396 4.17 9.748-7.56-4.402-8.76-10.194-3.63-11.362-3.57-4.776-6.59-9.241-2.69-11.32-4.22-6.767-5.47-12.352-6.22-17.792-.8-5.434-1.33-10.782-5.03-16.977-4.48.968-1.98 4.014-8.36 4.365 1.19 2.774.49 5.017-4.3 5.917.91 1.096 2.14 1.003 3.13 1.845-5.97-.057-1.03 3.378-3.86 4.301-2.17.165-3.84-1.408-5.52-3.061-3.82 1.001-.2 4.434-1.65 6.21-2.47.901-3.62-2.034-5.93-1.578-3.3 2.183 2.58 5.892 3.07 9.951-4.85-.567-7.26-5.723-9.32-7.868-.31.585-1.41.908-3.13 1.014.7.635 1.7.397 2.61.476 1.67 2.237-2.1 1.917 2.28 4.151-1.62.234-2.87-.641-4.51-.333 2.16 3.548.61 5.811-.9 8.054 1.89 1.704 4.08 2.519 6.58 2.41 1.92 1.559 2.52 2.638 2.54 3.494-3.57-.402-6.98-1.319-10.39-2.181-.17-3.452-5.42-10.306-12.06-13.518-.7-5.294-1.77-10.652 2.06-14.35-6.9-.961-4.02-3.713-3.06-5.412 3.64.572-.62 1.895 1.79 3.386 4.07-.813-2.75-5.174-3.4-7.486 2.55-.724 3.92 2.135 4.46.215-5.59-2.195-4.74-7.348-2.77-9.632-4.82-3.726-1.88-5.623-3.72-9.363-5.65-1.836-1.77 2.327-2.68 3.138-2.92-3.529-7.8-11.319-1.52-14.421-6.6-4.835 1.68-13.867-13.04-18.03 2.35-2.225-5.02-4.854-2.15-6.446 2.92.581 1.79 1.843 5.67 2.398-5.08-7.118-13.02-14.414-24.51-16.944-2.81.803-5.79 2.574-9.99 1.123-4.33 2.131-3.74 6.948-14.55 5.469 4.51 2.324 2.26 4.353 7.54 5.846.96-.238-.61-1.068-1.38-1.705 3.34.066 2.17.635 4 1.964-3.4.131-4.38.819-3.96 1.835-2.18.496-4.08-.324-5.82-1.725-.72.711-.06 1.681 2.16 2.218-6.36.652-1.56 6.541 1.23 10.621-8.14 1.721 1.67 8.291 6.92 13.874-.62.056-1.25.113-1.88.176-8.64-4.052-8.28-7.824-13.87-11.246-2.55-.009-.67 1.196-3.93.982 8.18 8.075 4.15 10.629 9.23 17.938-.9-.054-1.67-.516-2.6-.448-.65-2.317-4.17-6.505-8.33-8.752-2.75.101.06 1.845-2.41 2.019 3.62.643 4.27 1.757 2.03 2.323 2.31.38 4.53 1.059 6.69 2.041-2.36 1.32-.11 6.527 6.83 7.747 2.49 1.632-5.3-1.258-2.95.819-1.75-.328-1.45-1.841-4.82-2.242-.85 1.133 5.36 4.321 7.77 4.609 1.01 1.267-4.55-.648-6.29-.277 2.03 2.514 4.83 2.206 7.02 4.066 1.72 1.482-2.94.281-3.52-.455-1.98.859 3.4 2.254 4.52 2.949-.44.303-2.98.944-5.67.255-2.86-1.804-1.63-2.221-3.33-3.628-3.51.954 2.25 3.791 5.7 4.791.15 2.601-.1 4.805 6.15 8.686-2.8.921-4.33-1.716-6.84-1.686-.96-1.794-1.17-3.309-1.92-5.005-3.76.103-7.05-1.059-9.92-3.425-1.98.233-.26 1.658 1.9 2.424-4.04-1.193-.73 1.434-5.65.136 3.97 3.3 3.35 4.905 2.92 7.409-2.54-.394-3.78-3.219-.04-2.501-.44-.523-1.04-.563-1.64-.596-.47-.401-.95-.804-1.42-1.211-3.37.199-.75 2.541.28 4.337-3.52-.576-6.58-2.444-10.1-3.008 3.29 1.915 1.69 1.382 4.26 3.67-2.34.446-4.13-.619-6.31-.612 1.11 1.283 2.54 1.63 3.77 2.454-1.94.257-3.33-1.131-5.37-.64 1.37 2.599.3 4.279-.31 6.117-3.91-.585-1.85-1.534-5.19-3.745-2.5.052 2.23 2.938-1.79 2.395 1.36.519 2.43 1.715 3.95 1.91-4.78 3.234 1.4 10.966-2.55 14.554.95.769 2.3.631 3.5.777 1.82 1.607 3.42 3.137 3.15 3.864-13.3-4.248 1.28 7.578-2.03 9.264-11.27-5.867-6.91-7.799-11.04-12.398-3.06.208 2.18 4.258-3.83 2.208l4.07 3.929c-1.65.153-2.95-.62-4.42-.904.44-.756-2.92-3.181-.59-3.086-3.77-1.118-1.78-1.643-5.3-3.376-6.84-1.413 7.1 6.403-.13 4.823l11.99 10.051c-2.75.225-4.59-1.385-6.43-2.946-4.54-.874-2 1.489-3.11 2.187 2.24.778 4.26 2.038 5.82 4.171 1.22-.168-1.24-2.041 2.51-1.037 1.47 1.904.96 2.909-1.5 2.971l5.85 6.1c-4.95-1.726-3.56-2.366-4.76-.42-2.83.409-3.93-2.683-6.68-2.47 7.6 4.114 4.48 4.667 10.03 9.683-2.11 1.216-2.95 3.051-5.88 3.824 6.97 5.156 7.65 7.7 10.95 11.984-2.79.311-3.73-3.175-6.02-3.856-.43 3.186 8.74 11.477 13.73 15.72 3.26-2.047-6.69-8.285-8.01-11.249 1.57.131 2.91.785 4.1 1.72 1.82-.081-2.62-3.819-4.15-3.67 7.39.404-9.56-14.021.65-13.354 8.46 6.645 6.94 11.838 4.7 14.551 5.08 3.751 7 6.701 7.43 9.267.38 2.556-.6 4.746-1.05 7.076-4.26-3.664-7.49-6.128-13.1-9.324 2.89 2.581.93 2.587 3.33 4.921-9.88-1.676 2.73 6.331-5.94 5.108 2.34 4.957 1.74 3.424 2.89 7.557 1.78-.113 2.51 1.593 4.07 1.858.72 1.482-.63 1.81 1.74 4.209-3.65-.172-4.6-2.96-3.98-3.836-3.7-1.652-.93 4.452 2.62 6.316-.94 1.088 1.55 4.147-3.62 2.809-1.23 1.554 5.7 7.951 2.28 8.327-5.1-1.418-8.33-5.766-12.16-9.164-.67 2.55 5.2 7.197 2.37 9.613-3.62-.641-3.96-1.647-4.21-.027 2.28 2.708 2.13-1.303 4.41 1.389.81-.759 1.82-1.395.94-3.162 4.24 1.845 3.68 1.894 4.24.003 1.21.295 1.85 1.499 2.68 2.411.89 1.646-5.09-.787-4.6.604 1.3.748 2.14 2.168 3.65 2.576.64 1.781-1.54 1.877-1.92 3.037-2.31-.744-2.92-1.521-2.67-2.435-.45-1.065 1.44.495 2.86.471-2.21-1.51-4.25-3.306-7.1-3.827 9.88 11.448 7.22 15.526 10.69 23.354-1.3-.163-2.6-.339-3.43-1.277 2.24 1.831 4.79 7.821 4.13 10.335-4.06-2.168-4.2-6.872-6.59-8.047 3.84 1.189 2.45-.735-.56-.783 1.83 5.871 1.57 10.203-.07 12.951-5.75-2.231-1.36-5.426-6.95-8.839-.54 1.332-2.37.715.8 3.542-6.43 1.548.91 16.499.04 21.986-2.6-.435-2.35-5.017-5.53-4.639 3.91 3.383 4.18 5.775 4.39 7.877 1.35-.411 3.15-.532 2.64-2.22.86.308 1.47 1.001 2.43 1.207 1.51 3.721-5.02 1.928-.82 7.513-1.63-.26-2.64-1.429-4.32-1.64 2.59 3.88-.81 3.654-.14 6.257-1.62-.33-2.87-1.169-3.9-2.282-1.65-.092 1.22 2.98-2.34 1.55.53 1.31 1.72 1.717 2.96 2.078-4.49 5.3-5.62 10.13-7.43 17.213-1.81-.625-3.04-1.97-3.9-3.786-1 .65.07 2.822-1.32 3.197.86 2.852 4.36 2.2 5.35 4.873-3.96-.748-3.68 1.605-1.71 5.257-2.1-.242-2.96-2.045-5.24-2.048 1.26 1.76 3.54 2.303 4.79 4.076-5.39-1.372-.64 1.687-1.46 4.595-2.11-.264-2.96-2.074-5.28-2.098 1.01 1.131 2.2 2.038 3.84 2.441.7 3.513-7.16 4.08-11.99.32.62 4.915 5.97.94 9.06 6.025-.86.978-2.57-.803-5.25-.711 1.73 3.536-2.22 3.157 2.79 7.925-.92 1.386-1.55 3.295-6.21.427.78 1.43 1.56 2.838 2.34 4.255 4.65 1.846-2.73-2.74 1.92-.897 4.7 4.943-2.37 6.543-.65 12.131-2.52-2.685-5.11-4.292-5.13-6.555-5.45-.956 3.62 6.773 2.59 9.44-5.61-2.236-2.77 2.36-2.18 5.17-5.29-1.514-4.81 1.68-5.06 4.307-3.39-2.052.17-2.456-1.7-4.878-2.61-1.13-3.51-4.199-6.02-5.452 2.9 5.022 1.72 7.965 5.15 13.19-1.72-.599-1.09 1.114-3.43 1.133-4.6-2.709 2.08-4.219-4.52-5.776-2.08-3.207 2.27-1.082.2-4.293-4.03-1.833-.27 5.345-4.87 1.378 1.72 3.01-.2 3.195 1.1 6.886-5.62-4.776-1.44 1.653-6.23.203 2.62 5.223.06 6.016.9 9.776 2.82 2.432 6.15 2.69 4.82.098 4.71 4.215-.21 4.514-1.36 5.428-2.38-1.541-3.9-4.187-3.98-4.967-2.44-1.696-3.24-.917-6.55-3.612 6.12 5.34.2 3.11 3.22 8.367 4.19 2.451.67-1.797 3.92-.169-.52 1.241 1.11 4.37-2.68 2.781-1.59-.851-.16-.997-1.24-2.612-1.72-.736-1.76.655-2.85-.954-3.04-.726 4.13 4.22-.65 1.973 2.58 1.682 5.38 3.089 6.81 5.95-1.23 1.395-2.38-1.899-5.15-2.454.87 2.14 2.74 3.222 4.49 4.454-.2.682-1.75.178-.66 1.986-4.37-1.11-5.12-5.981-9.1-7.489 1.55 1.889 2.89 3.946 4.89 5.334 2.17 3.628-4.7-.73-1.32 3.98-2.59-.356-3.86-2.077-6.06-2.812 1.64 3.693-1.19 3.4-2.79 4.204 4.1 4.849.9 5.85-.63 7.178-2.09-.446-1.32-3.84-4.03-3.649 3.06 5.01-.27 4.609-1.65 6.82.42 2.531 3.66 2.324 4.4 4.547-.82 2.308-4.28-3.29-7.03-3.275-.51.911-.1 1.83 1.56 3.537-10.06-4.036-1.43 9.25-8.36 8.109 3.58 5.083 2.28 5.047 3.28 9.456-.38.616-3.42-1.545-3.93.042-3.65-2.808-.19-3.336-1.73-5.896-3.73-.196-2.2 3.639-.89 7.162-2.48.326-3.43-1.776-6.92-3.38-.12 1.992-2.65.847.4 4.657-1.23-.47-2.48-.944-3.71-1.42 4.06 2.885 2.58 7.19-3.07 4.053 3.94 5.414 1.11 4.257-1.23 4.772 1.22 3.357.9.613 3.18 1.994 1.06 2.303-1.23 1.341-1.38 2.474-4.2-1.93-3.46-2.676-5.17-5.056-1.22 1.168-3.19 1.6-8.29-1.047-.56-1.09-.42-1.5.22-1.404-2.65-2.21-3.92 1.538-5.29 2.37 4.77-1.015 8.53 3.597 12.56 2.015 2.99 7.948-6.89-.822-7.77 2.175 2.9 2.672 7.89 3.437 10.42 6.43-3.25-1.67-4.67.657-8.62-1.936 2.02 3.468 4.82 6.226 6.94 9.624-4.33-4.841-5.44-3.187-8.75-4.297-2.3-1.186-2.23-4.49-5.14-5.12 1.61 2.518 1.37 2.796 4.56 5.644-.59.859-4.04-.494.14 2.342-.88.767-3.51-.214-5.79-.856-2.03-1.93 2.31-2.692-2.48-6.327.42 1.861.37 5.815-.56 8.231-.98 2.396-2.89 3.207-6.02-1.113-1.08.555 1.09 4.433-2.02 2.936 4.89 7.237-2.93 5.26 1.05 13.391-4.74-3.803-3.76.6-6.83-3.69-1.97-.107 2.57 4.041 2.34 6.352-2.54 1.696-6.51 1.873-4.96 7.853-3.77-3.407-4.61-.593-2.38 2.039-4.19-2.705-2.9.359-5.78-.983 2.31 1.887 2.79 1.528 6.18 4.25-.72 1.444-4.55-.01-6.77-1.433-2.17.731 2.91 4.577-3.04 2.485.91 1.194 2.45 1.886 2.79 3.549.47-1.994 2.7-2.099 5.85-1.259-2.27 1.81-4.88 3.265-6.74 5.495-.8-.501-1.23-1.317-2.16-1.731-.17-2.111 4.29-1.02.58-5.139-3.51-.701.14 5.759-5.82 3.56.52 1.332 1.03 2.648.01 2.328.78 1.309 1.81 2.41 3.4 3.074.84 3.427-2.74 2.073-2.14 5.239-2.35-.422-2.82-2.365-4.91-2.993.42 2.672 3.74 2.761 2.14 4.073-2.75-.774-2.37-3.869-3.98-2.587.17 1.794 2.2 2.104 2.76 3.582-12.04-3.94-10.07 8.939-17.57 8.268-1.74-.925-1.55-3.363-3.02-4.53-2.94.079.83 7.643-5.96 3.442.76 1.314 1.77 2.435 3.35 3.15 1.57 2.942-.55 1.767.52 4.146-1.83-.497-2.16-2.148-2.7-3.621-2.54-1.058-1.57 1.816-3.44 1.512.48 1.146 1.92 1.574 3.04 2.222 1.46 2.825-1.25.983-.7 2.808-1.11-1.045-1.6-2.568-3.32-3.148.37 1.216.76 2.457 1.14 3.685-2.09-.675-2.51-2.631-4.87-3.106-2 3.512-4.05 6.984-7.31 9.069l2.73 1.329c-.9.562.99 4.343-1.32 3.309-1.82-.513-2.15-2.17-2.68-3.66-3.25-.164-3.33 6.015-2.99 8.037-2.98-2.45-4.29-2.515-3.7.559 3.9 1.357 2.57 2.6 2.91 4.59 1.67-3.607 6.31-.69 7.86-4.893-.79-1.538-.76-.294-1.82-.888-2.03-3.544 2.74.728.7-2.828 3.96 2.576 3.18 1.667 7.31 3.519-6.17.056-5.92 9.884-12.6 7.379.39.837 1.2 1.385 1.46 2.3.01 2.338-2-2.818-3.92-2.727 2.94 7.084-1.9 5.138-4.65 5.599 2.76 4.201 1.18 4.947-.86 5.121-1.92-3.1-3.16-6.675-6.43-8.765.19-1.574 2.71-1.723.22-4.628-.81 1.392-1.4.782-4.29-1.363 2.17 3.494.74 3.449 2.51 5.999-.14 1.872-3.44.044-2.05 3.721-5.59-4.057-6.02.832-6.43 4.671-3.66-4.141-4.47-2.946-3.48 1.413-1.23-.984-2.2-2.152-2.6-3.716-3.76-.385-1.89 5.948-5.24 6.057.58 1.797 3.16 2.183 3.48 4.177.98 2.422-4.24-2.626-1.92 1.405-1.54-.755-2.54-1.909-3.24-3.274-1.52.146 3.31 3.797.15 3.266-1.98-.799-3.45-1.982-4.12-3.723.15 3.278-5.93 1.623-2.89 6.44-2.08-.771-2.42-2.736-4.76-3.3 3.59 4.329-1.13 1.871-1.07 1.81-1.76 2.216 2.12 8.306-3.84 5.904 2.97 4.424-1.33 4.367-2.29 5.95-1.07-1.095-1.49-2.623-3.2-3.311 2.77 5.315-2.42 2.003-.27 7.848 1.91 3.319 4.74 2.571 4.02.563 1.07.189 1.16 4.297.32 4.186 2.13 3.788 2.13-.23 3.1.052 1.3.951 1.37 2.738 1.87 4.217-.2.765-5.45-3.094-3.29 2.258-2.19-1.065-4.31-2.167-4.98-4.281-3.21-1.687-1.38 2.929-5.32.338 1.22 2.087 3.54 4.27 2.1 5.16-3.52-5.763-4.1-11.214-4.09-15.871-1.46.285-3.8-.54-5.53-.605.65 1.727 2.86 4.711.07 3.242.62 1.402 1.95 2.329 2.51 3.775.09 2.926-2.18-1.086-5.12-1.989 5.05 5.751.01 10.649-5.63 4.959 2.83 3.534-1.37.956.49 5.078 3.13 1.845 1.33-2.566 4.44-.758 2.1 2.697-2.69 2.098-3.98 2.614 1.02 1.492 1.79 3.152 2.04 5.154.51 2.173-2.55-1.709-2.26-2.867-2.03-.326 1.72 4.525 3.6 5.219-.05-1.56 2.16-.251.66-3.648 1.45 1.209 2.61 2.632 4.05 3.848.09 3.104-4.92-.254-4.7 3.017-2.53-2.526-4.05-9.505-6.73-8.582 3.46 3.585 1.1 2.278 1.13 4.656-3.55-4.095-.38-2.63-4.7-3.438 2.06 2.85 2.68 3.879 2.24 6.085-4.83-5.056-.97 3.549-6.24-.28 3.38-3.439 2.92-9.971 10.02-9.663-5.48-3.721 1.28-4.197-3.86-9.342-1.63-1.013-1.61.066-3.35-1.079 3.11 5.067-2.37 3.516-5.11 1.121 4.2 3.46 1.42 4.751 5.83 8.07-1.06.359-.1 3.284-2.66 1.725-2.49-1.858 2.84-1.505-2.47-3.8-1.84-3.621 2.73.929.88-2.695-1.59-.8-2.16-2.199-3.79-2.942 1.85 6.006-1.63 2.692-4.67-.241 4.67 6.002-2.6 4.853-3.52 7.612 1.44 3.166 1.59 1.816 3.78 2.958 1.6 6.282-5.53 4.233-3.1 9.512-2.65-.797-2.41-3.463-4.2-4.826 1.62-1.324 2.59-3.498 5.31-3.403-.88-2.307-2.37-4.216-4.44-5.753.7 1.762-.27 1.366-1.33.833.59 1.063 1.18 2.124 2.23 2.896-5.22-3.114-6.68 4.521-4.64 9.432-4.84-7.824-5.1-1.926-9.94-6.559.41 1.62-.08 2.05-1.1 1.791 2.39 4.303 2.04-.247 4.18 1.619 2.88 2.619 2.52 5.859 4.16 11.312-4.56-2.96-1.78-5.048-4.17-8.087-3.77-1.68-1.01 1.213-1.33 4.091 1.85 1.315 2.78 3.248 4.17 4.86.06 1.607-3.25-1.25-1.77 2.228 3.64 1.989.47-5.033 5.96-.618-.64-4.1-.93-7.738 1.14-8.273 1.95 1.379-.57 2.176-.89 2.725 1.38 2.995 3.73 1.466 1.33-.858 11.31 13.732-7.52 8.713-9.26 16.548 1.24.669 2.48 1.329 2.86 2.5 2.39 1.654-.09-3.215 2.95-.704 1.74 1.925 1.09 6.255-2.34 3.5 3.31 2.978 3.9 9.368-.14 6.438-1-.538-.36-4.394 1.63-3.116-3.71-3.989-2.48-4.682-4.96-8.618-2.73-.747-3.09-2.082-5.99-2.715 1.17-3.116 4.81-1.083 3.52-8.45-4.03-2.528-2.5-3.499-6.41-4.586 1.9 2.392 5 4.022 4.8 7.678-2.2 1.45-5.58 1.313-6.79 4.082 1.95.928 3.36 2.181 3.93 3.96 1.55.098 2.59-.495 3.6-1.118 1.48 2.09 1.16 4.199 1.22 5.606-1.43-.559-1.83-1.726-2.17-2.913-3.41-.742 2.64 4.466-.55 4.559-1.78-2.104-2.59-4.787-4.55-6.781 1.75 6.952-6.87 4.382-5.19 11.59-1.75-1.41-2.17-3.643-4.78-4.521.01 2.463-2.68 1.24-1.05 5.926 1.96.911 3.38 2.163 4.58 3.574-.32 2.706-2.89 2.324-2.64 5.806-2.92-2.433-5.1-9.153-.49-6.044-2.39-1.73-2.73-4.695-5.16-6.395 2.44 6.015-.61 4.938-.95 10.088 2.98 2.533.56-2.366 2.92-.696 2.58 3.64-1.56 1.808-.4 5.524-1.53-.816-2.07-2.267-3.69-3.059 5.07 8.411-4.1 2.244-3.13 7.163-4.92-4.841-.26-5.502-2.59-9.002-.83-.477-.43.744-.47 1.363-2.57-.554-1.3-3.451-3.87-4.021.46 3.003-3.2 1.608.26 5.082 1.18 3.919-3.36-.163-3.46 1.942-1.68-1.776-3.09-4.931-.74-3.726-3.95-4.94-1.41 6.701-6.93-.228l1.08 5.601c1.75.737 2.44 2.09 3.68 3.117-.05 2.551-2.1-.236-4.18-1.8 2.13 7.126-3.96 2.727-9.51 2.26.96 5.351-7.8 1.878-8.58 6.947 2.42 3.551 2.04.001 3.96.864 2.28 4.218-1.74 5.326-3.44 6.111-3.18-3.376 2.15-1.25-.87-4.718-2.26-1.072-3.86-1.684-5.16-3.302.24 1.984 1.9 7.381 2.75 10.92-.79 2.01-3.92-3.214-6.02-3.882-1.14.005-.66 5.931-3.64.949.12 5.535-3.32 5.862-6.54 6.506-3.15-1.399-1.78-5.168-4.93-6.574-.48 3.307 3.67 4.185 3.54 7.322-.05 3.058-4.3-2.902-6.87-4.546.1 2.027-2.29.338-2.79 1.479-1.1-2.151-2.78-3.987-5.44-5.298.71 1.145 1.76 3.461.47 2.814.61 2.552 1.25-.307 3.4 2.273.9 3.209-1.7 1.211-2.78 1.476.6 2.378 3.17 2.036 3.26 1.335 1.32 1.703 2.13 3.652 3.2 5.471-1.22.899-3.12-1.848-1.09 2.613-3.82-2.591-1.44-2.87-3.19-5.462-2.83-1.282-.09 2.457.78 4.698-.8 2.11-3.68 1.117-4.9 2.594-1.63-2.215-2.01-5.079-3.48-7.366-2.3-.301-7.75-5.507-7.87-1.954-4.44-3.879-6.41-6.191-9.86-5.028 1.93 1.05 3.2.539 5.08 3.462.38 2.719-1.36 2.188-2.63 2.394.33 1.893 2.5 2.766 2.3 4.919 1.1-1.054 6.4 1.709 4.17-2.204 1.38 1.661 2.93 3.239 3.15 5.5-.92 1.049-3.65-3.802-1.93 2.044-2.55-.689-1.13-3.483-3.68-4.176 3.3 4.424 1.31 5.306-1.65 3.921 1.01 3.309 2.84 3.371 3.38 2.309.39 1.784-.72 1.291-.25 3.195-1.59-1.576-4.83-2.312-5.26-4.455-.89-.099-.25 5.735-2.98.464-2.08-.834 2.65 7.652-.58 6.315-1.2-1.094-1.05-2.84-1.41-4.337-2.08.401-8.04-2.195-5.67 2.783-.11.895-3.46.805-3.36-2.339-2.7 2.143-3.58 9.037 1.13 18.55-.67.673-2.02.255-2.01 1.995-3.03-1.851-2-5.711-5.56-7.318-.26-3.859 1.05-1.81 2.82-1.398.03-3.36-1.27-6.07-4.16-8.002.15.922 1.14 1.446 1.83 2.129-.16 1.951-.04 4.329-2.7 2.328-2.2-1.745.92-.805-.54-3.76-2.97-2.737-.53 3.065-3.79-.138 4.12 7.747-3.1 4.484-8.01 1.927.6 3.327 3.01 9.093 5.66 8.283-3.09-3.67-.77-3.034 1.16-2.585 1.22 1.169.31 1.713-.57 1.295 2.78 3.181 4.8-1.184 7.45-.67-2.3 8.171-13.44 8.595-18.47 14.929-1-.871-.92-2.219-1.18-3.411-1.66-.688-2.98-.856-1.92 2.863-3.65-4.672-1.87.004-4.79-1.78-2.16-1.3.13-4.683-2.96-5.562 1.84 5.655-.73 3.052-4.18 2.9 1.33 4.417 1.57 7.646-3.35 3.52.97 4.122-1.74 2.16-.77 6.291-10.3-8.93-12.18 2.322-21.81.108 2.22 3.361 2.27 6.963-1.26 2.487-.39 2.449 1.09 4.095 2.13 5.938-.28.541-.56 1.11-1.45.602-1.22-2.424-3.04-4.566-3.06-7.494-3.89-2.154 3.34 6.737-1.97 2.803 2.71 5.792.92 4.102-.97 5.293-1.81-1.518-2.01-3.72-2.13-5.93-.96 5.721-8.37 4.346-5.31 14.965-.7 2.49-2.21-.1-3.81-.319-2.36-1.941-1.8-5.113-4.43-6.924-.1 2.652 1.38 4.633 2.89 6.594-2.27-.089-.33 2.25-1.1 4.38-3.47-3.711-4.25-10.058-2.51-10.667-1.43-.867-1.75-2.182-.91-1.572-5.06-3.428-11.3-6.859-10.2 3.359-2.51-1.545-.73-4.904-3.57-6.293 1.27 4.993-5.15 1.481-7.19 3.052.04 1.604 1.18 2.781 1.14 4.394-.87-1.266-1.02-2.81-2.58-3.815-1.19.782.17 1.254.31 3.738-.89-.925-.7-2.302-1.77-3.165-3.42-2.653-3.43.768-1.15 4.312-.95-.914-1.41-.95-1.54-.365-.95-.908-.82-2.259-1.04-3.45-3.49 1.175-12.02-2.541-10.91 4.285-5.69-3.031-1.64 2.411-5.23.118 1.58 6.99-.13 6.411-.69 10.891 2 1.76 2.36.538 3.85 1.369.43 3.246-2.95-.531-2.85 2.096-.94-.906-.8-2.27-1-3.465-2.5.559-6.57-1.782-9.07-1.253-.95-.922-.8-2.274-1-3.485-1.28.089-2.98-.616-2.84 2.095 3.57 9.212-10.21-.023-10.79 6.943 5.01-.209 9.62-1.213 14.95-.857-.34 2.632-2.74 1.471-4.37 1.73 1.48 3.996 2.38.143 3.83 1.39.13 1.876.26 3.752.38 5.647-1.41-1.046-1.74-2.525-3.23-3.557-1.94 3.28 2.15 10.571-3.25 8.84-1.81-2.676.65-4.263.37-6.794-1.11-1.835-3-3.351-3.35-5.459-.1 1.436 3.3 11.939-1.72 9.201-1.41-1.405-1.9-3.129-1.8-5.076-3.04-2.282.76 7.927-2.64 6.673-2.67-1.581-1.22-4.65-4.11-6.144-.1.673.35 1.132.84 1.595 1.23 2.15-1.77 1.11 1.04 5.343-1.01-.441-4.05-.608-1.33 3.339-1.2-.347-1.03 1.971-3.01.133-1.62-2.904-.59-6.774-2.75-9.487-.41 1.292-1.03 3.056-1.22 5.215-1.88-1.234-1.8-3.17-3.98-4.287-1.62 1.92-4.43 1.552-6.62 2.392-1.74-1.63.89-1.499-.24-4.699-4.09-1.432 1.03 9.889-4.99 4.679-.36 1.003 1.14 5.646-.56 4.026.3 2.584 1.28-.14 3.11 2.698l.13 2.816c-3.77-2.261-4.36 1.738-8.27-.829-2.18-4.099.57-5.687 3.64-2.27-.03-2.342.23-4.105 1.3-4.29-1.06.702-6.95-4.194-5.89 2.117-2.72-1.892-2.05-4.988-4.76-6.867 1.52 6.264-3.13 7.242-6.43 8.72-2.34-2.661.67-1.336-.14-3.721-1.53.594-2.22-2.423-4.64-4.12-.77 3.064-3.84 1.51-4.31 5.188-5.22-4.102-2.13 1.967-3.48 8.68 1.39-1.011 2.02-3.482 4.43-2.4.23-1.339-.66-2.299-1.6-3.246.4-1.529 1.38-1.852 2.97-.969 1.57 1.11-.08 2.542-.68 2.099 1.79 2.437 9.15 8.589 8.36 2.906 3.62 4.405 7.6 2.66 9.04-.152 2.06 2.037 2.63 4.166 2.27.188 6.49 3.94 10.92 3.719 13.48-.098 2.99 5.481 4.67-.276 6.27 5.364-.26-1.887 1.08-2.133 2.09-3.633-1.41-1.303-1.6-.253-3.1-1.738.35-1.028 1.06-1.353 1.39-2.411.98 1.239 1.68 2.583 1.71 4.143 2.31 2.218 2.32-.037 4.63 2.149.15-1.33-.82-2.238-.92-3.486 2.07 2.019 3.95 3.666 3.01-.127 1.24.977 3.03 2.988 3.11 1.732 1.87 1.228-.23 3.898 2.51 4.82-1.81-6.761 6.06-6.319 9.76-2.561 2.33 1.368-.87-2.641-.21-3.745.1-.632.74-.264.68-1.213 2.4 1.352 2.44 3.576 4.84 4.928-.26-5.918 6.83-3.405 11.16-3.325-.76-3.359.59-2.77.44-4.994-3.76-2.776.12 3.461-2.89 2.095-.47-2.872-.35-2.06-3.36-5.436-.45-6.11 3.14-4.172 5.99-.433.6 3.293-3.93-2.886-2.93 1.164 1.68-.561 4.95 3.721 4.97 6.73 2.32-.939 5.91.415 8.31-.374 1.73 4.647 1.07 2.737 4.08 4.176-.66 1.542-2.58.799-5.44-1.716.21 1.846 2.69 2.786 2.6 4.78.73-1.692 3.38.143 4.51-.82-.25-2.485-2.82-4.036-4.94-5.783 1.47-1.94 4.9-.336 4.97-4.869-.88-2.218-2.81-4.043-3.48-6.346 1.53-2.059 2.32 4.12 4.9 4.841 1.22 1.356-.24.929.2 2.806 5.17 4.577 8.11-.89 14.02 4.11.15-6.251 5.02-4.011 8.05-5.095 3.3 3.642-.06 4.313 2.19 8.784 4.05 2.231-.17-6.393 4.7 1.023-.82-2.911-2.3-5.541-4.45-7.896 1.47-3.036 4.51-3.291 8.16-2.506-2.7-6.426 1.13-5.217 3.33-5.371.97.904.84 2.265 1.07 3.474-.14 1.098-1.99-.792-3.19-1.581-.03.996.33 1.832.98 2.524l.16 1.89c2.48 1.413 1.17-3.806 5.39.635.34 2.479-1.64.88-2.13 1.884 1.97 4.427 3.32 1.078 4.75 1.891 1.41 7.669 3.54-.038 7.06 1.87-1.57-2.397-3.72-7.105-2.23-7.852 1.33 4.45 3.26 3.568 6.09 8.15-.22-1.196.05-1.521.67-1.253-3.22-4.044-.42-4.587-2.32-8.802 1.34.174 7.11 3.308 6.72-1.909 2.57 1.539 2.56 4.131 3.66 6.276 3.25 1.088 5.35.52 7.94 2.455-.11-1.235-.23-2.502-.34-3.753l-3.33-2.494c1.23 5.011-3.13.474-4.54.959-.25-4.117 4.38.077 4.28-3.776 5.57 9.506 6.47 2.362 12.08 5.5-1.71-.536-1.47 2.245-3.77.713 2.23 3.982 4.38 1.143 5.44.524-.92-2.25-2.28-4.249-2.97-6.584 1.26-2.099 3.43 3.603 5.08 4.622-.48-1.846.45-1.327 1.46-.688-2.86-4.888-1.15-5.696 3.66-1.697-4.21-4.62-.48-1.966-1.54-7.242 4.7 3.339-.33 4.988 4.68 7.778-2.84-6.02-.51-5.121-1.74-9.129 4.01 2.808.14 4.641 2.27 6.887 1.8-.782 2.16-1.01.96-5.41 3.68 2.542-.11 2.395 2.08 5.001 6.54 3.972 3.59-2.953 5.46-6.499 1.96 1.404 2.65 3.399 5.13 4.568-.59-4.338 3.07-1.658 3.37-4.522 1.74 1.836 2.24 4.245 3.78 6.173.41 1.697-.61 1.056-.52 2.226-2.2-.973-1.84-3.12-2.84-4.638-1.73 1.822-.72 4.899-4.11 4.884 2.36.506 5.63 2.498 6.21.091 2.6 1.603-1.84 1.209 1.16 3.449-2.64-.696-3.9.866-6.12.837.32 1.191 1.74 1.853 1.89 3.101-.4 2.289-2.24-2.739-4.2-3.019.22 1.651-.36 2.01-1.37 1.629 5.74 9.728-4.93-.52-3.2 6.452-2.57-1.437-2.55-4.871-4.27-3.968.82 3.604 4.1 6.05 4.86 9.685 2.37 1.843 1.66-1.446 3.89.164-.25-1.199-1.62-1.907-2.61-2.766-.4-3.282 2.1-1.752 3.7-1.711 1.94 2.71-.36 1.879 1.32 5.342-1.15 1.238-3.4.639-3.51 3.619-2.59-1.424-2.54-4.873-4.28-3.98 4.47 12.503-8.49 8.189-12.96 10.799-1.48-1.973-.98-4.804-2.93-6.581 5.23-.707 11.26-.072 15.24-2.918-1-3.107-3.34-2.026-2.22 1.041-2.74-2.157-3.79-.69-4.36-4.941-.85 1.204-1.64 2.513-2.77 3.257-1.54-1.918-1.44-4.624-4.42-5.887 1.51 5.026.23 3.702-1.99 3.791-2.5-1.2-1.84-3.768-4.33-4.98-3.42-.221-4.94 4.215-4.72 7.912-.74-1.321-2.33-2.271-2.66-3.76-.22-2.629 2.45 3.937 1.49-.639-4.01-2.087-4.67 1.605-8.54-.259l.57 6.62c-1.96-1.772-3.23-3.856-5.86-5.324 1.57 5.981 3.95 4.73 6.02 7.214.24 3.403-5.85-4.22-4.56.978-3.57-2.399-2.15-6.928-6.16-9.117.1.928 1.07 1.511 1.72 2.193-1.26 3.995-5.55 2.712-8.99 2.89.44 4.046 4.8 6.461 5.83 5.345-.53 2.064 2.38 10.186-.99 7.272-2.35-2.682 1.01-1.604-1.11-4.424-2.74-3.205-.37 2.598-3.12-.603.89 3.405 2.48 4.043 1.25 6.324-1.77-1.196-2.66-2.784-2.67-4.743-3.86-2.306 1.77 7.132-1.95 4.76-1.9-2.116-.65-5.608-3.62-7.265-.38 2.449 1.11 4.128 1.24 6.316-.31.555-.61 1.119-1.52.596-1.52-2.53.56-3.394-2.04-6.92-1.71 1.119-2.69-1.55-6.48-4.187-5.15-1.492-6.56 4.065-8.88 5.446-1.07-6.586-7.83-.841-11.69-2.141-2.17-4.281 3.81-.772 2.68-5.897-5.19-2.677-3.28 6.641-8.28 2.311.21 1.213 1.52 1.976 2.48 2.881 1.09 4.226-1.83 1.088-1.25 4.382-7.09-6.731-6.74 2.154-11.67-3.121-1.43 5.031-5.8 1.502-5.64 8.022 1.51-.253 1.02.927 3.2 2.622-1.38 6.766-7.4-1.795-11.18-8.295.75 3.544 3.24 6.453 4.32 9.878-2.13-.344-3.54.718-6.19-.622-1.7-1.924.98-2.59.53-4.964 1.37 1.712-7.24-.782-7.63.871-1.28-.416-3.56-4.761-1.69-3.212-2.08-3.826-1.54.474-2.26.822.18 1.209 1.49 2.007 2.43 2.938-2.94 1.441-12.73-6.471-14.53-.442-1.32-1.11-2.63-2.231-3.95-3.346.07 1.86-2.1-.621-1.42 2.432-7.62-7.366-6.5 2.369-13.83-4.525-1.22 5.416-6.27 3.333-12.2-.627.77-3.261-.01-5.075.53-7.706-3.61 1.478-4.59 8.382-8.98 3.909 3.54 6.839 2.45 10.964 7.31 18.323-2.33-.257-5.27-1.767-7.66-2.163 3.74 7.153 8.21.301 12.41 9.082.17 2.143-2.76-2.001-3.88-2.483 2.23 5.177-2.92 1.36-2.17 6.375-1.08-2.778-2.23-5.545-5.5-7.614 2.1 3.883 1.33 5.136.93 8.193-2.72-2.715-6.87-6.064-4.61-1.296-2.43-1.699-3.06-4.739-4.63-4.117-2.27-2.824-.9-2.176-.85-4.45-1.97-.139-3.99-.39-4.52 2.447-2.61-2.283-1.26-5.88-3.93-8.143-.05 1.48 2.6 8.216-.67 4.954.7 1.971.66 4.178 3.13 5.58-1.25 2.552-1.26 1.724.08 6.582.58 3.053-3.63-3.926-3.05-.874-.24 3.699 2.51 2.924 3.85 4.384-.68 1.028-2.8-.969-3.05.996-1.81-1.918-.48-4.843-3.1-6.51 1.23 3.672-1.98 2.361-2.3-.233.75 7.053-4.64 5.246-10.72.424.78 1.932.75 4.119 1.54 6.063 2.41 1.167 1.73 1.503 4.6 4.218.37-1.651-1.1-2.786-2.29-3.972 2.59-2.217 8.1 1.702 12.29 2.807-1.31 3.731-7.11-1.994-8.46 1.625 1.86.54 3.74 1.101 6.16 2.801-.64 2.389-4.2-1.435-3.08 4.676-1.82-1.946-1.43-4.539-3.84-6.297.09 1.748-.95 1.102-.78 3.02-1.64-1.045-1.46-2.669-1.53-4.193-1.52-1.389-.98 1.653-3.06-.944 1.82 4.795-2.01 2.735-.02 7.472-3.36-2.082-2.5-5.453-5.34-7.715-.37 3.01-5.33 1.021-6.875-1.655-.974 3.73-3.345-1.442-4.583 1.377-2.266-2.72.737-1.318.02-3.737-3.763-2.845-4.266 1.416-7.596-.509.339 2.8.572 3.713 2.22 7.731-1.651-.422-3.369 1.863-1.587 6.098-2.453-1.13-.544-3.566-2.991-4.699-1.599-.657-1.363 2.759-3.819.207-.473 2.582 1.382 4.236-.066 4.663-2.503-2.394-.666-5.96-2.152-8.662-.217-2.716 4.197 4.846 3.027.012-2.1-3.724-4.85-2.275-5.336.625-2.144-1.275-1.928-3.168-3.743-4.511-.64 3.399-3.114 7.806-5.434 4.226-1.054-.676-.096 1.578.71 2.578-1.145.98-2.083 2.447-5.321-1.332.473 3.37-1.355 1.495-.88 4.855.453-3.2 5.835 4.776 5.364-.535 2.966 3.805 2.075 5.892 5.965 7.624.257 6.188-3.078.369-3.845.117.258 4.459 2.856 2.55 4.461 6.167-.387-2.839.382-3.053 2.335-.62-.376-3.401 1.477-1.54.108-4.663-2.107-1.493-2.196.99-3.754-3.58.24-1.726 3.335 2.954 3.061.084 2.108 2.998.865 4.575 3.735 5.448-3.333-5.419 1.788-.32 3.104-.834.679.893 2.461 5.865.675 4.416 1.026-.421 5.991 4.35 6.917 1.785 4.644 6.627 4.928 1.248 9.093 7.513 1.303-2.095-.559-5.547 1.629-6.098 1.03 1.247.595 2.931.715 4.458 4.043 4.712 4.283 1.029 6.903 2.577-.4-2.339-1.16-3.084-2.28-3.055-.79-4.402 2.75-.841.83-4.932.47.719 2.91 5.041 3.06 2.818 2.22 2.872.85 2.195.72 4.454 3.42 2.308 3.66-2.299 8.49 3.036-3.32-8.853 4.73 3.08 3.15-4.687 2 5.232 2.59 1.515 4.61 5.123-1.14 1.037-2.08 2.545-5.42-1.142 1.92 7.235-4.35 2.837-8.54-.228 1 2.516 1.98 5.011 1.45 7.993.11-8.976 16.59 8.133 13.3-7.792 2.86.064 4.76-1.89 8.57.194-.42-2.334-1.21-3.084-2.31-3.028.01-1.871.01-3.732.02-5.62.51-.78 1.66-.245 3.11.922.07 2.978-2.45.475-1.58 5.151 2.2 1.819 3.25 1.197 5.45 3.012l.02-3.737c.57-.361 1.42-.142 2.34.222 1.69 1.636 1.67 3.848 1.54 6.069 3.62.561-2.25-8.402 2.32-6.415-.35-1.475-1-2.821-2.34-3.954.1-1.664.49-2.701 1.53-2.407 1.92 7.66 8.84 7.559 12.45 10.004-1.31 2.763-9.38-.218-6.19 5.873-2.27.911-5.95-1.143-7.8.624 1.98 4.712 3.52.528 5.47 2.047-.78 2.82-1.68.977-.82 5.878-4.35-6.791-2.69-.099-7-7.197-.55 3.597 1.77 6.304 3.85 9.092-.82 1.941-3.31-3.201-4.68-4.17-.01 2.215-1.37 1.553-.84 4.92-2.2-1.189-1.99-3.12-3.87-4.427.15 4.561-.93.481-3.13.016-.65 3.097-3.45 1.589-3.98 4.955-2.49-1.403-1.56-3.879-3.04-5.617 1.26-1.959 6.62.845 3.18-5.644-1.28-.193-.89 3.229-1.61 4.251-.61-1.069-1.24-2.124-2.31-3.054-.02 4.137-.25 4.52-3.14 1.894-.84 3.694 3.05 5.922 2.2 9.619-.16.632-.79.235-.8 1.186-2.43-1.754-2.14-4.344-3.02-6.563-2.85-3.618-2.23.331-4.64-2.322-.3 1.33.58 2.319 1.51 3.279-1.43 2.719-7.29-4.285-6.3 3.786-5.55-1.544-7.09 3.155-13.196-1.672.538 4.519-2.171 1.769-3.215 2.74 1.318 4.126 2.266 4.832 2.107 7.747 3.481 3.107 3.022 2.03 6.114 5.728-.938-4.279-1.183-3.016-1.354-7.027 1.224-1.597 4.254.86 4.024-3.951 2.37 2.717 1.05 6.55 2.1 9.657 1.91 1.851 3.56-1.932 5.39 3.027 3.21.471 3.14-6.29 8.64-.769-.42 3.033-2.7 1.991-3.25 4.734 1.38.412 2.02-.849 4.66 2.334-1.15-3.593-.79-3.71 1.58-.486.35-6.818 7.44 1.129 9.49-1.986 2.36 1.161 1.34 3.347 1.48 5.171 2.42 2.64 2.1-.694 3.94.666-2.06-2.748-3.48-5.722-6.94-8.175-.72-1.339-1.14-2.771-1.5-4.221.8-.587 2.11-.018 3.15-.017 1.21 1.49.61 3.546 3.06 4.66.37-1.661-.48-2.963-.7-4.443.21-.844 1.55.736 1.58-.501 2.6 3.204.06 2.178 1.49 5.167-.2 2.512-3.71-2.15-3.19 1.907 13.96-.213 26.24-4.138 41.14-2.755-2.46-6.986 3.17-4.844 6.33-.231-.92-3.804-3.89-6.885-4.75-10.686 1.44-2.437 4.46 4.173 5.52.962.93.954.68 2.297.81 3.515 2.44.282 2.55-4.178 5.52-2.806 1.99 9.578 7.11 3.245 13.54 8.652.8-2.159-1.8-3.176-3.2-4.578 3.37-.764 2.39.609 6.37 1.618-.18-1.503-2.39-2.339-2.41-3.911.52-.821 1.69-.32 3.19.813-.69-1.652-1.08-3.42-.85-5.387.46-3.146 3.36 3.835 3.14-1.119 1.41 1.399 1.84 3.151 1.67 5.137 5.91.656 6.41 3.348 11.02-1.201.99 1.22 1.68 2.567 1.67 4.178 1.26-6.072 7.26-2.86 9.35-7.295.73 1.011 1.47 2.021 1.67 3.235 1.53-.368 1.63-3.512 1.41-7.234 2.09 1.129 2.92 2.734 2.5 4.817 3.72-3.666 13.16 3.53 13.35-6.861 2.48 1.309 1.64 3.855 4.14 5.173.04 1.859-2.2-.583-1.53 2.471 3.03-2.66 5.11-9.162 11.68-7.384-1.29-3.731-4.38-1.242-4.18-6.073 3.82 2.493 6.94 4.538 8.01 2.604-1.58-5.54-1.83.271-4.02-1.3-.47-4.113 2.06-2.642 3-4.118 1.48 2.639 2.55 5.449 3.5 8.289 3.31-2.774 2.94-9.816 9.16-8.534.91.923.65 2.333 1.75 3.188 1.47 1.256 1.68.182 3.24 1.597-.24-3.619-1.05-3.437-.27-5.714 1.01 1.541 2.29 2.969 4.18 4.136 1.04-2.458 3.52-2.286 5.44-3.147 2.04 1.662.34 3.277-.69 2.217 4.19 4.249 4.32-2.801 2.79-7.908 3.21 1.291 2.59 4.123 2.75 6.651 2.73-1.129 1.83-8.74 6-7.297 1.58.817 1.47 5.273-.66 2.247 2.38 3.208 4.56 3.782 6.52 3.061-.44.519-1.72 2.082 1.01 3.494-.27 1.23-2.56-1.129-1.43 2.595 2.92 2.356 2.54-1.204 4.76-.105-1.69-1.886-2.9-6.336-.29-4.794-.44-1.698-1.04-.414-2.5-1.864.73-2.146 2.06-3.179 1.07-8.335 3.04 1.314 1.54 4.57 4.34 5.981-.31-3.043 1.64-3.936-.49-7.657 3.84-.456 8.87.055 10.19-2.45-1.03-1.838-2.52-3.489-2.02-6 3.87 3.973 3.02 7.274 6.86 6.84.81 3.626-1.52 1.857-2.2 2.92 2.49 3.983 3.22-2.22 5.5-1.403-.63-1.382-2.01-2.401-1.89-4.098 2.2 1.103 2.37-.428 5.84 3.365.39-2.485-1.67-3.882-1.26-6.337.85-1.007 6.31 5.91 4.71-1.061 2.01 1.077 1.96 3.058 4.31 4.014.32 2.483-1.08 2.04-2.26 1.968 10.44 5.316 15.06-6.52 23.33-7.699 1.33 2.055 1.89 4.432 2.2 6.948 1.99.315 1.89-2.867 4.7-1.206-.07-2.296-1.57-3.914-2.89-5.611 3.28-1.721 14.08 2.671 11.18-9.227 3.28 2.972.36 3.367 2.16 5.955 2.58-.993 2.98-1.5 5.48-1.571-.42-1.458-1.65-2.534-1.99-4.057 1.58-2.686 11.35 4.15 8.56-3.023 2.02 1.368 2.7 3.398 5.27 4.49.56-1.499-.57-5.715 2.94-2.48-3.31-8.948 7.66-1.473 7.08-8.623.67 1.685 3.35 2.366 3.67 4.237.84-2.655.94-6.444 6.19-2.119-.52-2.408-.26-3.58.34-4.213.75.965 1.51 1.926 2.7 2.681-1.21-2.618.86-3.657 1.53-.768-.23-2.807 2.09-3.306 5.33-2.683-2.6-3.043-1.77-3.826-.54-4.774 1.04.818.93 2.222 1.19 3.429 2.67.221 3.53-2.334 6.21-2.11.91 4.452-2.46.265-.22 5.158 1.64.179 3.55.785 3.93-.976 1.23 1.08 1.01 2.869 1.32 4.39-1.43.677-6.04-3.601-4.69 1.374 1.03-1.856 4.29 4.659 5.13 2.459 2.7 3.173 1.45 4.267.65 5.748-1.84-1.452-2.04-3.702-3.07-5.549-2.82-1.317-.83 2.807-.23 5.169-1.31.615-1.73 2.6-4.81.419 3.3-.713-1.02-5.942 1.77-5.944-1.08-3.15-3.28-.092-2.94-4.588-.13-3.216 2.74 4.911 2.61 1.717-.1-4.269-.91-1.927-3.7-4.205.3-.59.57-1.213 1.53-.768-3.47-1.972-5.54 4.998-10.03 4.035 3.62 4.016-.87 1.624 1.4 5.372 1.65-1.109 3.13-2.493 3.4-5.759 7.46 9.71-.85 9.726-.93 13.612-5.27-2.396-5.02-7.576-9.2-10.539 3.09 5.109-3.39-.095-1.85 4.999.68-1.378 5.15-1.328 6.82 3.634-5.33-3.961-.29 3.702-5.46 1.741-3.01-1.528-2.49-4.824-5.48-6.344l.5 4.811c1.22.742 2.43 1.467 2.7 2.69-1.19 5.614-5.64 6.004-7.85 9.984 3.44 3.122 2.85-.258 4.9.614-.49 3.739-3.16 7.59-7.92 1.8 1.39.075 1.98-1.12 1.21-4.529-1.37-3.129-2.46-.072-3.56-3.325-1.93 1.942-.17 9.114-6.2 2.91 1.03 3.588-1.06 2.09-2.24 2.053.81 1.282.75 2.966 1.98 4.055-4.46-1.305-5.99 6.559-11.19 1.281-1.47 1.139-2.31 3.29-3.64 4.688-.86-1.258-1.44-2.643-1.95-4.063-2.44-.928.77 3.435.41 4.797-3.14-1.858-3.77-4.888-4.68-7.784-2.58-.528-3.73 2.215-7.43-2.492 1.36 3.348 4.72 5.774 4.83 9.712 3.82 1.19-3.19-7.219 1.99-4.928 2.36 2.838 2.88 4.982 2.18 6.948-2.1 2.868-9.58-3.325-11.2.298-3.68-3.797-.26-3.173-.47-5.778-2.62-1.634-2.37 1.54-2.94 3.366.73.988 1.46 1.98 2.62 2.758-.49 1.373-2.48-1.122-.62 2.286-4.05-2.133-1.45 7.049-8.03.628 1.85 3.672-1.13 5.664-3.91 1.721 2.67 7.304-1.23 8.618-5.16 7.18-.15 1.362.9 2.22 1.02 3.477-3.57-1.736-4.49 1.632-7.49-3.61.14 2.222.28 4.449.42 6.703-4.19-1.464-2.22-5.603-5.3-7.54.17 3.995-1.91 4.066-4.64 3.003 1.25 2.723 1.13 6.063 2.14 8.908-2.88-2.676-1.7-7.12-5.38-9.46 4.38 7.687-.89 9.487-5.2 7.192-1.13 2.406 1.81 7.07-1.25 6.443-1.49-2.839-1.85-6.749-3.44-4.395-1.74-2.195-2-5.029-2.05-7.955-.72-.706-.76-.214-.78.342-.54-.411-1.11-.832-1.67-1.252-.06 2.636 2.04 4.34 1.99 7.005-.72 3.165-4.01-3.301-7.5-4.734.29 3.699-3.65-.184-3.91 2.544 4.06 3.828 5.04 2.08 5.64-.329 1.03 1.841 3 3.314 5.07 4.755-2.17-.114.03 3.356-4.01.642-.9 2.302 2.74 2.773 1.84 5.066-3.89-3.181-7.06-.707-10.53-.594.66 2.257 3.75 1.589 4.05.3 3.97 5.958-.39 6.747-2.97 6.054-3.29-3.334 2.63-1.402-.16-3.814-4.27-3.282 0 3.465-3.14 2.237-1.88-8.574-5.46 3.586-12.18-2.839.88 3.65-1.07 6.709-1.2 11.147-3.6-7.12-6.64 1.682-6.28 4.484-1.17-.829-2.33-1.642-2.5-2.883.68 2.732-2.47.596-2.26 4.798-4.39-5.339-2.83.97-4.78 3.711-1.58-1.642-1.55-3.871-4.15-5.144-2.75 6.12-7.41 3.376-11.17 7.973-1.68-1.856-1.65-4.142-3.29-3.57 3.29 6.492-1.38 3.726-4.82-.108 3.38 4.581 2.58 9.072-.7 7.919-2.76-3.75 2.52-.955-.06-4.77-1.46-1.294-1.71-.218-3.25-1.676.48 2.315 2.47 3.844.87 4.474-1.17-1.159-.32-3.071-1.67-4.168-3.62-1.146-3.2 1.561-1.55 4.422-1.27 2.134-2.6-5.088-4.06-3.295 2.28 3.846 1.23 5.837.07 7.634-3.15-4.53-1.3.346-4.83-2.978 1.77 3.48-.43 3.907-2.4 2.79-1.11-1.867.34-1.285-1.63-4.174 1.96 5.342-7.15 1.976-2.39 9.418-2.62-1.768-.51 5.951-3.25 3.933-2.95-3.161.09-2.949-.79-7.286-2.14-.796-2.74 1.479-4.87.705 4.44 10.444-3.51 7.497-4.09 9.911-1.39-3.112-.03-5.017.05-6.635 1.36.795 1.51 2.009 1.59 3.23 2.7 1.235.07-2.831 1.64-4.342-3.21-.096-3.32 1.222-8.86-4.544-.72 2.439 2.06 3.704 2.38 5.803-5.34-5.089-11.62 4.436-16.15 3.603-.26.724-1.79 4.331.72 6.369-.34 4.334-5.64-2.056-2.48 4.65-2.25-1.761-1.96-4.387-3.13-6.527.27-1.08 2 .915 3.21 1.767-.29-3.664-3.46-4.845-1.53-7.073-2.11.618-1.25 3.054-2.5 6.524-.62-1.03-1.27-2.074-2.37-2.977.01 3.252 1.82 1.718 3.14 5.57-.48 1.947-3.11-.594-2.47 3.693 8.11 1.861 14.5.054 21.02-1.49 2.6 1.009.57 3.605 3.18 4.595 1.53-.539.13-2.307.06-4.739 3.23 2.684 2.18 6.825 2.3 10.578-3.59.962-11.05-5.044-13.01 1.604-1.15.58-3.96-2.328-4.85-1.205 2.91 3.253 2.32 2.862-.84 2.192-.63 2.121 1.55 3.27 1.51 5.181-6.05-5.176-4.47-.16-7.34.514-1.93-1.282-1.68-3.218-3.96-4.39 1.86 3.509 1.7 6.31-.97 4.978-1.91-1.276.57-3.922-2.3-4.899.24 1.865-.68 1.244-1.64.521.59 1.377.78 2.856.66 4.454-4.44-.775-6.49-.868-11.27-3.976 2.55 7.196-3.51-.841-3.38 3.858-2.3-2.835 1.1-1.495.15-4.732-1.4.688-2.55 1.916-4.88.579-.48.998.74 5.717-.97 4.038 2.5 4.138 1.79 3.828 1.33 8.032 2.72-1.923 6.44 2.863 7.38-1.364 3.25 3.238.35 3.238 3.06 5.616.55-2.977-.49-5.478-2.17-7.753.41-1.521 1.8-.896 1.75-3.351 2.62 5.35 4 1.834 4.87.339 2.72 3.214-.3 3.649 1.37 8.021 1.91.184 1.3-1.266.96-4.05 1.15 1.163 3.88 3.991 6.43 3.616-1.06-2.163-.66-4.773-.59-7.314 1.81 2.562 2.47 5.483 5.5 7.661-.04-2.777 2.34-.433 3.32-1.041.46 2.053 2.38 3.646 3.12 5.584 3.7-2.932 8.72-3.071 13.97-2.786-.04-6.051 5.92.325 6.62-4.174 8.47 6.94 8.09-8.62 16.38 1.031-.65-3.267 1.17-1.532.87-4.099 2.63 2.713 3.79 2.463 5.71 3.72-.7 2.554-3.72-.276-1.72 5.331-1.45-4.598-2.03-1.701-4.07-2.389.68 3.8 1.8 2.331 3.24 2.658-.02 4.559-3.05 3.004-4.99 3.625-1.54-1.352.56-3.305 1.68-2.47-2.77-3.972-4.84 1.92-7.43 1.599 2.48 2.773 1.6-1.319 4.09 1.444-4.32.297-3.77 4.819-6.74 7.049-2.14-1.478.88-4.725-2.33-5.835-.03 1.279-.06 2.564-.08 3.829-5.23-2.061-8.13 2.213-9.2 5.99.2-5.386-3.1-2.669-5.65-2.798.76 2.193.64 4.48 2.35 3.936.08 7.177-4.9-2.195-5.08 3.497-2.36-2.069-2.91-4.704-3.89-7.218-1.74-.87-.96 5.197-.24 6.64-.79 2.551-7.52-3.744-5.06 2.527-.76.211-4.63-1.157-4.83-3.175.21 4.402-3.96 6.851-9.28 4.785 1.33-2.539 1.44-1.741 1.11-6.915-2.25-1.434-4.19-2.197-6.44-3.625-.77 1.355 3.95 5.413 1.47 4.235 1.22 4.519 1.84-.702 2.51-.792 2.85 5.253-.36 10.458-5.18 5.37-2.92.519-.84 12.146-6.12 7.552-.06 1.255-.12 2.529-.19 3.794-2.15-1.189-1.42-3.297-2.22-4.923-2.28-.845-1.03 6.179-4.27 3.213-2.27-2.849.62-2.661.24-4.772-3.66 3.723-6.67 8.22-12.56 2.733-1.29.639-3 .348-2.76 4.504 1.59 1.095 2.45 2.388 2.24 3.992-3.49-3.565-5.67 3.157-10.74-.677.04-2.439.15-4.713 1.87-3.298-2.09-2.594-6.59-1.402-9.02-1.177-.16 4.666 1.46-.386 3.9 3.536-.22 3.164-2.75.95-2.79 4.512.93 1.285 1.51 2.666 1.35 4.248 2.13 2.023 1.69-.145 3.12 2.84-.46.39-.92.795-.98 2.13-4.87-5.676-4.75-3.803-8.69-5.895-.35-4.473 3.85 1.612 3.51-2.859-2.33-2.84-2.4-.444-4.72-3.293 2.31 3.072-.43 6.845-2.17 7.099-.9-.982-.53-2.326-.57-3.546-2.83-3.68-2.37.33-4.77-2.369-.01 1.856 2.57 2.984 2.95 4.74-1.7 3.774-6.43.395-8.5 3.321 1.84 4.397 1.85-1.289 4.07.718.61.751.7 1.657 1.47 2.377-2.84-1.027-2.04 2.39-3.69 4.731 2.11 4.695 3.54 2.608 2.62-1.656 2.88 1.65.26 5.587 2.94 4.749-.2 2.944-2.8 4.631 1.02 8.067l.21.14zm180.88-11.911c-.16-3.935 3.13-1.106 5.05-.961-.22 3.168-2.52 2.293-5.05.961zm144.39-24.77c.8 4.11-3.85 2.48 1.11 6.426-1.12 3.228-4.16 3.172-7.61 2.397-.73-3.764.75-1.785-1.12-7.393 1.29.719 3.25 2.546 3.47 1.421-.07-1.299-1.57-1.939-1.83-3.136 2.35.703 1.45-4.137 5.99.275l-.01.01zm478.06-872.924c-2.09-2.263-5.57-3.137-1.58-3.567 2.45.857 4.54 3.51 1.58 3.567zm-89.71-30.649c-1.34-2.762-2.13-3.312-6.87-6.906 3.85.573 2.08-.922-.02-2.524 3.88.225 4.17.08 8.93 1.049-2.71-2.842-6.44-2.84-9.9-3.544 2.66-1.992 10.54.949 14.37 4.048-6.8.106 3.63 6.462.04 7.747-3.93-.129-1.5-1.968-4.7-2.484 1.62 1.748 1.35 3.772-1.84 2.61l-.01.004zm-65.74 218.732c1.68 3.066-2.41 1.654-1.95 3.776-1.88-3.238-.66-4.023 1.95-3.776zm-65.97 102.504c-2.76-1.673-3.99.77-6.54-2.479 3.73 2.054 3.26-2.235 6.54 2.48zm-68.78 63.031c1.36-.082-.08-3.172 3.7-.664 1.26 2.564-1.51 3.721-3.7.664zm-475.71 272.452c-1.54.705-3.01 1.524-6.03-.687 1.51-.195 3.83-3.238 6.03.687zm59.13 40.425c1.82 3.648-5.35 5.349-6.93-.229 4.06 3.355 2.73-3.384 6.93.229zm-139.768 33.627c-.22-1.486-1.045-2.802-.707-4.471 1.421 1.554 1.673.516 3.03 1.902.113 2.832.02 5.21-2.323 2.569zm244.698 58.946c-3.42 1.838-5.16 2.276-8.78 2.608 1.28-3.819 4.5-4.155 8.78-2.608zm-76.58 30.972c-1.47-.02-4.16 2.384-5.66-.788 1.18-.547 3.69-3.29 5.66.788zm-42.76 8.43c.62 1.173 2.03 7.411-.84 4.084-1.77-1.888-.72-5.855.84-4.084zm-73.32 93.782c-1.36-.821.07 1.39-.22 2.849-3.53-4.937-1.33 3.539-4.86-1.414-.48-4.875 3.81-4.297 5.08-1.435zm68.94 2.878c-1.02.598-.91 3.669-3.4 1.061-.15-4.863 3.02-1.299 3.4-1.061zm71.03 6.551c1.45 3.211-1.26 4.161-4.25 1.488-.2-3.774 2.16-2.359 4.25-1.488zm12.78-3.479c-.44 2.526-4.77 2.433-6.74-1.457 2.77 1.552 2.86-2.302 6.74 1.457zm-165.59-140.791c2.88-.271 4.34-3.636 7.9-2.418-.5 5.014-5.35 5.095-7.9 2.418zm231.12 181.384c1.67 3.696-1.35 4.78-4.32 2.639-.62-4.857 1.59-4.243 4.32-2.639zm-112.2-88.036c-1.46.31-1.52 3.512-4.16 1.425-.02-2.756 2.37-5.64 4.16-1.425zm-125.65-108.132c-1.71-2.289-2.53 2.111-3.85-2.561 1.43.476 2.78-2.603 3.85 2.561zm1.62-3.302c2.85 4.479 1.69.645 3.87 1.629-.01 1.57 2.13 2.464 2.28 3.989-2.13.141-3.01-3.062-3.15.948l-2.31-2.104c-.21-1.493-1.06-2.801-.69-4.456v-.006zm12.36 8.383c-1.11.845-1.03 4.271-3.94 1.202-.54-4.458 1.27-3.76 3.94-1.202zm90.1 64.935c.13-1.678.56-2.73 1.64-2.461-.51 3.277 4.53 10.606.72 9.228-2.57-3.565.39-4.639-2.36-6.767zm-79.02-71.132c-.02 1.246-.04 2.502-.05 3.754l-2.33-2.081c-.64-3.617 1.23-1.871 2.38-1.673zm-46.835-42.774c-3.23-5.511-1.355.913-3.806-.709-2.676-5.258 4.152-4.746 3.806.709zm134.015 109.227c4.37 2.546 3.66-6.302 8.95.737-2.22-4.946.64-5.781 3.28-3.964-3.37 2.438.21 7.909-1.7 12.873-4.87-6.034-1.09 1.257-4.86-4.012-1.44 1.939-3.87 6.976-6.55 3.192.63.416 2.48-1.096.84-2.185-.89 1.438-2.49 1.414-4.89-.231-.34-3.276 1.75-1.601 4.07.509.06-2.869 3-2.48.86-6.925v.006zm94.63 80.106c2.05 4.06-.93 3.931-2.56 3.851-1.65-3.306.32-5.12 2.56-3.851zm-109.16-88.4l3.21 2.681c.22 2.347-.22 3.412-1.63 2.452 1.02-2.233-2.59-2.903-1.58-5.133zm-102.64-97.624c.56 4.057-4.06-3.076-3.09 1.871-2.29-1.905-2.58-5.854-4.57-5.147-.06-6.66 5.03 3.268 7.66 3.282v-.006zm44.93 39.53c.04 2.91.44 6.625-2.38 3.576 1.07-.632-.24-6.23 2.38-3.576zm-30.33-26.203c-3.56-4.217-2.43-11.222.81-8.672 1.49 3.118 1.43 5.644.76 6.32-2.05-4.203-2.27-1.065-1.57 2.352zm134.25 96.496c-2.19-4.137-2.38-.902-1.61 2.517-1.2.139-2.05-.535-3.25-2.6-.85 4.99-6.59.278-7.28 5.585-2.29-1.491-3.46-7.161-3.27-1.722-2.61-1.882-5.94-8.353-2.42-6.737.08 1.227.23 2.435 1.61 3.225-4.3-9.245 1.8-8.216 3.2-11.718 1.35 1.094 2.7 2.192 4.06 3.288 1.41-1.002.59-6.35 4.02-3.417 1.87 5.36-1.36 7.701-4 7.243.98.989.51 1.465.02 1.908 1.47-.238 4.56 2.661 5.68 1.714-.32-5.368-.1-9.605 4.01-6.282-3.3 1.138-.59 4.413-.77 6.986v.01zm-102.36-73.668c-.08-1.869-2.57-2.932-2.34-4.894 2.52 3.384.59-2.541 3.12.846.13 3.117 1.77 5.712 1.57 8.942-.79-.69-1.58-1.376-2.35-2.051v-2.843zm242.7 188.731c1.99 2.171.86 6.622-1.7 3.622.37-1.56-1.2-6.709 1.7-3.622zm-48.75-44.227c1.95 5.857-6.35.613-4.19.74 1.68.284 1.3-3.301 4.19-.74zm-212.62-173.246c.01 1.258.02 2.521.02 3.773-.92-.968-2.2-1.809-2.34-3.024 1.34.898-.99-4.126 2.32-.749zm23.29 13.003c-.08 1.609.8 2.893 2.37 3.94l.02 4.709c-1.48-.248-3.69-2.012-5.47-2.883 1.44-1.062 1.94-4.078 3.07-5.762l.01-.004zm-23.3-14.895c-.41-2.684-1.9-5.019-3.12-7.458 1.53 1.35.96-1.693 3.08.861-.31 6.799 4.11 1.865 5.43 6.687-2.7-1.875-3.68 4.676-6.93-1.452l1.54 1.362zm35.85 23.906c-3.5-5.621-2.71.623-5.48-.986-2.58-7.662 3.51-5.636 5.48.986zm3.94.586c1.79.252 2.99 4.979 3.16 2.726-.26-2.135-2.17-3.694-2.41-5.82 1.05.905 2.1 1.805 3.17 2.719.09 1.56-.09 3.178 1.6 4.192.14 2.418.18 4.686-1.55 3.342-.09-1.224-.24-2.428-1.59-3.24-.63 2.109 1.13 3.395.82 5.398-3.27-2.364-2.12-6.225-3.2-9.317zm21.43 17.352c-.04 3.94-1.86 4.351-5.55.927.5-3.038 2.69-2.633 5.55-.927zm-55.85-44.567c.96-2.612 5.35 1.832 6.15-1.119.1 4.044-1.38 3.596-3.82 4.126-.14-1.227-1.24-2.107-2.33-3.007zm121.25 94.424l.03 3.812c-2.82-2.991-3.89-2.589-3.24 1.198-.8-.641-1.62-1.296-2.44-1.946.55-3.616 3.43-2.671 5.64-3.07l.01.006zm-109.65-90.76c-2.02-1.393-2.32.768-4.65-1.261-.49-3.246 3.24-2.465 4.65 1.261zm219.2 170.389c-1.03 3.127-5.22.463-6.71 2.769-2.07-5.538 3.63-3.699 6.71-2.769zM1034.2 449.094c-1.15-1.82-1.15-4.031-1.61-6.078.68-1.054 1.69-1.375 3.04-1.018 1.6 2.309 2.19 4.948 3.18 7.458-1.05-6.42 1.59-5.209 2.96-6.654 4.41 5.167 5.46 3.747 9.29 5.401.93 4.619-4.79 2.73-7.64 2.545 1.34 1.133 2 2.488 2.36 3.94-2.28.785-3.62-.405-3.88-3.429-1.29 1.545-5.35 6.286-10.73-1.151 2.95 1.637 1-1.21 3.05-1.005l-.02-.009zm39.01 31.368c1.43 4.294 1.85 6.79-1.51 5.236 2.68-.746-1.42-5.01 1.51-5.236zm-28.95-23.383c1.47.88 1.93-.31 3.87 1.54.34 3.27-3.26 2.447-3.87-1.54zm124.69 100.611c1.03.434 1.66.11 1.61-1.551 1.36 1.077 2.74 2.167 4.1 3.238.71-4.864 3.19-1.726 4.8-5.611.04 1.9 2.27 2.976 1.72 5.099-3.25-2.483-3.95-.134-4.86 1.823 2.04 2.425 3.61 5.052 3.38 8.331-1.82-.71-3.2-.539-5.76-2.646-.18-3.137 2.15-1.464 1.56-5.376-2.38-2.976-4.55-.07-6.55-3.307zm3.3 3.569c-.08 2.958-1.81 2.71-4.88-.11.37-2.392 3.08-.243 4.88.11zm-114.84-101.015c2.52 1.049.65 3.556 3.16 4.603-.05 2.316-1.09 2.654-3.08 1.033-1.08-2.136-1.13-5.379-.08-5.642v.006zm245.43 193.125c-.06 2.392-.09 4.849-2.51 2.981-.47-3.339 1.01-3.18 2.51-2.981zm-250.92-198.853c1.47 1.078 1.74 2.558 3.13 3.678.28 2.68-1.03 2.136-3.08.083l-.05-3.761zm64.86 48.762c1.39 1.067 2.5 2.274 3.22 3.583-1.25 1.511-1.88 4.233-5.49 1.086-.58-4.193 2.98-.217 2.27-4.669zm-54.74-43.648c3.58 2.424 3.45-2.688 6.97-.425-2.42-.036-1.52 6.624-5.36 3.685-.04-1.249-1.41-2.063-1.61-3.26zm18.12 14.723l-.06-3.76c1.42 1.735 3.3 3.292 3.99 5.28-2.45-2.797-1.49 1.262-3.93-1.52zm-24.43-22.984c-.57-2.338-4.65-3.498-3.97-6.242 1.87-.703 1.7 3.44 4.71 5.051.03 1.564.06 3.112.08 4.686-1.4-1.771-3.2-.084-3.9-3.412.46-1.174 2.33.515 3.08-.077v-.006zm130.3 104.737c-2.71-1.146.23 3.903-3.23 2.172-.76-3.664 1.26-6.72 3.23-2.172zm-94.17-82.906c-1.15 1.031-1.82 3.021-3.85 2.301-.53-4.367 2.06-2.537 3.85-2.301zm231.23 176.133c-.32 1.614-.2 3.976-2.49 2.096-1.75-3.013-.64-5.056 2.49-2.096zm-211.45-164.267c-1.97-.561-2.59 1.553-5.5-.841-.39-3.547 4.22-1.864 5.5.841zm83.22 67.553c1.54-1.357 4.18-.664 6.45-.658.15 4.073-4.07 7.052-6.45.658zm-134.34-126.433c-.03 3.468.65.889 2.39 2.976.77 3.6-2.55-.959-2.25 1.695 3.51 4.039 3.87 1.822 6.25 3.588 1.05 1.332 5.21 6.281 2.46 5.789-.75-3.325-1.97-.517-3.94-3.403.9 5.354-3.45.229-3.78 3.129-3.1-3.539-4.34-4.23-6.23-3.59.05 1.557 2.18 2.423 2.39 3.91-5.59-3.292-5.34-7.325-7.24-14.554 1.53 1.067 1.58 2.611 2.4 3.921-.03-1.538 2.15 1.355 2.29.174-1.36-1.112-2.44-2.325-3.15-3.667.09-3.045 2.51-.554-.12-4.662.92-.248 1.34-1.436 3.86 1.516.36 1.909-1.09 1.384.08 2.819 1.22-.518 2.85-.191 4.6.362l-.01-.003zm25.25 23.408c-1.53-2.916.26-2.473.64-4.053 3.13 4.539 3.55.381 6.21 1.555.73 5.432 1.65-.585 4.76 3.98-.74 2.38-4.23-.6-5.37 1.006-.54 5.806 5.01 14.763.41 13.255.74-3.704-1.63-14.335-6.65-15.749v.006zm248.88 188.685c1.41 1.665-.04 4.624 2.75 5.682.91-3.373-1.57-5.229-1.99-7.996 1.65 1.346 1.89.223 3.46 1.422 2.18 6.495 1.29 8.581 3.06 14.427-6.54-4.696-6.77-3.926-11.26-4.86.15-3.618.65-8.047 3.98-8.669v-.006zm-130.19-89.527c1.14 3.521.26 4.942-2.32 3.846.36-2.079 1.47-2.737 2.32-3.846zm-157.08-128.564c.12 1.834 2.59 2.906 2.41 4.851-1.29 1.611-4.86-5.578-2.41-4.851zm50.99 37.22l.24 7.579c-1.02-.812-1.06.286-2.36-1.052-2.19-3.442-.81-8.076 2.11-6.524l.01-.003zm110.16 91.618c2.08 1.472 2.55.161 3.37 3.504-1.65-1.248-.96 1.817-3.27-.617-.03-.964-.07-1.917-.1-2.891v.004zm-61.88-53.896c4.12 2.967 1.72 6.738-.65 5.028-.68-3.379 1.32-1.654.65-5.028zm-79.82-63.491c.72-.326 4.01 2.589 3.98 5.25-1.93-1.514-3.81-3.119-3.98-5.25zm141.51 111.649c.06.619.17 1.229.87 1.603-.11-1.7.56-1.968 1.56-1.616.05 3.066-.89 4.355-3.13 3.203.09-1.33-.42-3.749.7-3.19zm-9.41-14.62c-2.83-2.232-1.1 3.931-3.98 1.592-.38-3.148 1.98-5.77 3.98-1.592zm-169.21-142.244c1.07 3.734 2.29 1.041.71-1.174 1.92 1.559 2.47 3.584 3.2 5.548-2.1-.297-.62 1.844-3.72 1.226-.76-2.726-1.1-4.965-.19-5.604v.004zm336.06 243.523c.57 3.338-2.07 1.296-3.31 1.593-.56-3.331.53-3.869 3.31-1.593zm-194.15-142.679c.37 2.704-.99 2.236-3.14.3-.36-2.713 1-2.235 3.14-.3zm73.29 54.596c3.2 1.622 3.46 6.99.18 3.864-.05-2.588-1.49-2.975-.18-3.864zm-64.5-53.274c-.12-4.008 4.23-4.364 7.15.043-4.15-3.806-2.99 2.038-4.63 2.817-.56-2.501-1.24.156-2.52-2.866v.006zm208.88 135.114c-1.2 1.863-2.33 3.828-5.88 1.814.51-2.838 2.87-5.874 5.88-1.814zm-319.73-235.076c-1.42-.973-4.23-.388-5.45-3.761.07-1.63 4.43.767 5.45 3.761zm55.04 33.121c3.42 3.526 3.7 1.229 6.34 3.301.21 3.465-3.62-.523-1.97 5.632-1.97-1.95-1.66.325-3.97-2.3.22-3.421.28-2.491-.39-6.627l-.01-.006zm116.87 87.162c2.86 1.706 4.83 3.795 4.46 6.93-1.57-.074-4.33-4.009-5.8-2.4-1.1-4.24 1.94-1.202 1.34-4.53zm-141.9-123.679c.14 3.301.05 6.182-1.91 5.581-.11-3.268-1.05-8.068 1.91-5.581zm72.18 60.345c.4 2.681-.96 2.214-3.12.269-.39-2.685.96-2.204 3.12-.269zm-28.04-25.601c-.02 3.016-.61 5.017-3.7 2.445.1-2.905 2.18-2.163 3.7-2.445zm17.72 10.384c3.77 2.34 3.35-2.848 6.07-2.416 1.96 6.218-1.72 4.854-4.27 6.506.26 1.325 1.41 2.93-.69 1.246-.76-1.635-1.25-3.375-1.11-5.336zm64.92 42.904c-.02 4.168-2.18 4.606-4.43 4.869-.13-4.424 2.21-4.539 4.43-4.869zm-122.24-98.535c.08 2.336-1.22 2.106-2.06 2.748-.74-2.929-3.06-5.226-3.57-8.238 2.7 1.508 3.15 3.902 5.64 5.487l-.01.003zm-4.12 5.507c-2.76-3.766-2.68-1.63-5.5-3.637 2.07-1.791-.73-11.298 4.12-5.482-1.41 2.099.57 5.369 1.38 9.11v.009zm8.38 1.52c-2.53-2.957-2.75 2.484-4.66-2.032 1.3-.612 2.03-2.269 5.33.804.01 1.597.94 2.831 2.54 3.841-.11 2.534-1.08 3.51-2.81 3.046-.35-1.841 1.92-2.873-.4-5.659zm51.42 35.699c-1.92-1.225-1.31 2.089-3.91-.382-.64-2.877 3.05-2.143 3.91.382zm-4.53 1.812c.07-2.285-3.21-3.228-2.7-5.691 2.43.756 4.82 6.316 2.7 5.691zm17.11 11.554c-.62-3.301.89-2.817 1.21-4.44 2.9 2.936 3.38 1.592 4.79 1.876-.71 2.285.08 2.081.42 5.701-2.39-2.753-3.59-3.774-3.09.341-1.42-1.03-2.56-2.165-3.33-3.468v-.01zm128.1 87.162c.98 3.953-1.59 3.322.51 6.754l-1.73-1.183c0 .553-.04 1.051-.79.368-1.51-3.959-.98-7.221 2-5.935l.01-.004zm119.73 84.234c1.78.598 1.53-1.836 3.51 1.257.93 3.758-3.14 1.693-3.51-1.257zm-310.69-225.731c-.92.766-2.45.407-4.6-1.087-1.43-4.596 4.18-.39 4.6 1.087zm166.57 119.06c-.41 2.658-1.96 3.389-4.65 2.201.4-2.674 1.96-3.389 4.65-2.201zM1126.9 376.395c-1.34-.701.27 1.359.21 2.811-1.96-.088-4.64-4.72-4.56-.12 1.35 2.79 3.27 1.084 4.17 5.114-1.42.648-3.09-.424-5.58-3.603-.5-5.281 2.29-8.796 5.76-4.196v-.006zm120.96 96.35c2.33.359 1.33-4.917 5.49-1.434.43 3.753-1.77 3.047-1.96 5.784-1.91-1.124-3.2-2.533-3.53-4.35zm-70.45-56.846c.89-.063 2.1.433 4.03 2.191 1.08 4.093-3.74.701-4.03-2.191zm-44.55-40.885c.07 1.27.55 2.369 1.04 3.464-.88.04-2.09-.507-3.98-2.302-.08-2.289.91-2.678 2.95-1.165l-.01.003zm52.91 39.249c-.96.754.8 6.26-2.01 3.809-.27-2.916.36-4.252 2.01-3.809zm18.6 8.663c.75 4.44-2.39.106-.36 5.066-1.45-1.015-2.61-2.14-3.42-3.438-.46-3.517.79-4.049 3.77-1.624l.01-.004zm-9.19 2.001c3.4 2.651 1.2-4.427 5.41-.376 1.35 5.074-3.09 4.349-5.41.376zm-51.83-42.462c1.32 4.498-1.7 1.265-3.85-.41-.52-3.072 2.51.17 3.85.41zm49.48 32.702c.84 3.609-1.45 1.812-2.11 2.836-2.09-3.622-.61-5.166 2.11-2.836zm.84-8.216c0 4.161-.7 4.736 1.56 9.124-1.32.654-2.45-2.229-4.22-4.073-.65-3.267.87-2.793 1.18-4.406-.99-.612-1.73.269-2.49-1.871 1.21-2.763 4.02-.19 7.14 1.824.36 1.467 1.34 2.689 1.15 4.393-2.58-1.173-2.7-3.405-4.31-4.995l-.01.004zm10.64 6.193c-.7 4.165-3.45 4.829-6.6 4.802-3.96-7.55 2.57-6.189 6.6-4.802zm-75.82-54.732c-1.05 2.139-3.94.954-5.94 1.363.87-2.425 2.21-4.017 5.94-1.363zm24.28 19.051c1.06 2.147 3.2 6.188.41 4.717-.51-.925-2.29-5.942-.41-4.717zm-12.85-17.041c3.05.995.65 4.296 2.77 5.678 1.88 1.199.08-4.089 2.9-1.241.15 4.488-2.69 3.741-4.91 4.061-1.47-3.643 1.35-3.003-.76-8.498zm72.9 60.819c-.4-5.903 6.49.147 8.3 5.238-3-2.593-3.68-4.245-8.3-5.238zm-5.59-10.321c.14 1.568.28 3.147.41 4.714-1-.886-2.36-1.592-2.59-2.801 2.24 1.943-1.24-5.871 2.17-1.909l.01-.004zm-36.77-31.235c2.14 1.374 2.31 3.587 2.81 5.657-1.04-.745-1.02.35-2.39-.934a581.88 581.88 0 00-.43-4.719l.01-.004zm-16.61-15.395c2.47 2.535 4.09 5.418 4.58 8.79-3.34-2.084-5.72-7.829-4.58-8.79zm46.45 38.832c-1.02-4.089 3.44-.549 4.83 1.833.3 4.419-1.77-.148-4.83-1.833zm-40.73-42.266c1.44 1.175 2.53 1.757 3.19 1.575-.67 2.666-2.9-.382-1.01 5.33-3.43-4.683-2.33-.147-5.54-2.517-.13-3.626 3.63-.507 3.36-4.382v-.006zm14.78 3.483c-5.48-4.416-5.53 4.601-9.96-.65 2.35-.56 5.27-7.936 9.96.65zm-2.2.936c.2 1.881 2.36 2.901 2.02 5.009-1.09-.545-1.45.147-3.21-1.565.95-1.826-1.26-4.193 1.19-3.438v-.006zm11.41-7.847c2.61 2.594 1.3-1.412 3.9 1.166-.04 2.013-.39 3.464-1.96 2.9-.34-1.504-1.52-2.614-1.94-4.066zm63.85 36.032c-1.05.984-2.67 6.274-5.49-.414 3.15 2.781 3.56-2.932 5.49.414zm88.41 61.098c3.1 1.173 4 6.976 1.41 5.353.1-2.058-1.83-3.12-1.41-5.353zm-91.9-64.443c.54 2.684-1.49 1.185-3.06.432.13-1.854-3.17-6.985-1.47-6.268.95 2.205 3.14 3.836 4.53 5.836zm-3.16-7.462c3.22 1.287 4.98 7.572 2.22 5.918-1.08-1.809-2.6-3.427-2.22-5.918zm-37.21-29.468c3.79 3.94 1.36-2.352 4.67.819-.23 3.447-2.23 3.964-4.02 4.834l-.65-5.649v-.004zm33.4 23.294l.43 3.757-2.54-1.811c-1.07-3.553.97-2.008 2.11-1.946zm16.19 12.34c-.53-4.797 3.26 1.818 5.6 1.315-.41 1.924-3.64 1.623-5.6-1.315zm7.19 1.547l3.39 2.35c.96 3.144-1.01 5.486-2.93 1.469 2.07 1.298-.14-3.057-.46-3.823v.004zm86.74 56.504c-.58.94-2.67-.466-4.15-.942 4.72-3.66 9.38-7.335 16.86-6.815 2.16 6.391-4.77-1.054-3.52 3.94 2.18 1.273 2.51-.096 3.62 3.191-1.63.955-3.08-2.699-6.11-3.896.87 4.703-4.53-.201-3.42 4.887 1.46.17 1.9-1.235 5.01 1.446.65 2.563-1.33 1.087-.28 4.258-2.13-.425-3.57-6.104-4.52-3.805.04 2.336 3.51 2.954 3.09 5.529-.44.9-1.68.558-3.3-.364-1.4-2.336-1.32-5.403-3.29-7.432l.01.003zm-100.62-68.849c4.39 3.503 3.83-.976 7.03.604 1 3.593 3.89 5.531 2.5 7.841-3.02-1.188-2.97-3.903-4.56-5.799-.5 1.485-2.26.968-1.89 3.874-2.6-1.451-2.94-3.943-3.08-6.52zm-33.16-26.364c.34 3.086 1.13 6.914-1.72 4.776.45-1.8-.99-6.681 1.72-4.776zm66.23 49.296l.45 3.816c-.85-.581-1.71-1.17-2.55-1.749-.54-2.645-.18-3.883 2.1-2.067zm-.64-23.884c.2-.965.84-1.191 2.31-.158-.08 5.312-7.65-3.613-9.88-4.159.2 1.57.41 3.155.62 4.737-5.72-3.182-7.97-.961-9.85 1.887 2.06 3.926 2.87 7.419 2.63 8.78-3.94-5.865-.06 2.761-3.88-.075-.26-5.273.07-8.59-1.78-14.269 6.65 5.617 6.04-4.002 8.49-6.239.64 1.705-.36 4.211 1.46 5.324-.06-2.861 1.82-2.679 1.58-5.821-.99-.541-1.71.366-2.56-1.749 3 1.91 3.01-4.742 6.2-.064-3.11-1.163-2.08 6.061 1.97 9.108-3.37-5.641 3.39-4.951 5.11-2.555-1.45.519-6.84.233-2.42 5.262v-.009zm-3.43-8.383c-1.12-1.686-.89-4.565.1-5.088.9 2.757 2.05-.193 4.15 1.962.17 3.502-2.32 2.877-4.25 3.126zm-61.21-15.476c1.18-1.868 3.31-2.18 5.92-1.73-.75 2.335-2.7 5.835-5.92 1.73zm31.65 20.096c-1.27-.527-.37 2.449-3.16-.515-1.94-4.257 2.58-1.8 3.16.515zm8.33-1.122c.47 2.582-1.53 5.075-3.74.866 1.88.716 1.86-1.582 3.74-.866zm-31.28-22.99c1.43 2.294.66 4.91-1.95 2.887-.76-3.245.57-3.096 1.95-2.887zm142.47 89.953c1.97 3.563-1.88 1.78 1.62 6.319-3.29-1.105-4.26-7.949-1.62-6.319zm3.63-2.997c2.23 3.337-.35 3.372 1 7.683-.88-.551-1.77-1.117-2.64-1.671-.24-3.222-1.27-7.565 1.64-6.012zm-104.52-68.062c-.5 1.046-1.43 5.234-3.77.854 1.63.312 1.27-2.525 3.77-.854zm-21.91-20.401c.58 2.665.24 3.867-2.02 2.033-.56-2.653-.22-3.855 2.02-2.033zm42.24 21.365c1.18-.172 1.73-1.295 2.92-1.455.52 3.228 2.38 6.15.01 6.018-.32-1.839-2.92-2.562-2.93-4.563zm12.13 3.017c1.51 1.075 2.66 1.59 3.33 1.363.09.636.17 1.276.26 1.903.52.394 1.05.82.98 1.513-4.3-2.445 1.63 3.536 1.5 5.3-1.59-.685-1.21 1.655-4.06-.982.08-3.419-1.55-5.954-2-9.091l-.01-.006zm-13.27-11.508c1.28 1.025 3.11 1.799 3.56 3.258-2.62-.567-1.45 4.734-6.1 1.018.32-3.329 3.54-.499 2.54-4.276zm266.4 160.305c2.83 1.091 3.26 3.673 3.48 6.378-1.28-.723-1.96-.625-2.63-.487-1.45-1.929-1.93-5.302-.86-5.887l.01-.004zm-165.64-96.111c.18-2.266 1.9-2.252 4.78-.529.71 3.366 4.5 9.1.17 7.083-.96-3.656-1.89-6.582-4.95-6.554zm-77.64-54.77c-1.33.811-3.37-1.624-4.44-3.789-.13 5.004-5.67 5.72-10.83.523 5.37 1.923 7.54-2.063 7.7-6.027.88 2.285 2.67 4.051 4.72 5.707-2.03-3.347-2.07-3.675-.68-4.761 1.63.816 1.88 2.399 2.84 3.596-1.96-4.491-.08-1.745 1.17-2.681-3.11-2.567-1.34-4.455 1.17-2.662 1.75 2.588-.29 2.688.97 6.659-3.07.492-4.7-1.633-2.62 3.435zm-69.78-51.747c3.89 2.237.52-5.761 1.31-6.686 4.03 3.167 1.5 3.741 3.48 8.339-2.68-2.72-1.2 1.05-1.15 2.587-1.89-1.087-3.2-2.444-3.63-4.238l-.01-.002zm60.08 38.468c2.45.536 4.04-.258 6.17-.221-.02 2.18-.55 3.584-1.74 4.008-.12-1.536-3.25 1.053-4.43-3.787zm-39.43-32.581c1.35 2.006 2.31 6.643-.03 5.046.26-1.311-1.82-6.227.03-5.046zm-11.99-14.179c1.04 3.288-1.05 1.721-.03 5.032 2.11 1.354 2.39.044 3.53 3.28-1.72-.434-3.61 2.595-6.37-1.883.06-2.977-1.05-7.864 2.86-6.426l.01-.003zm65.06 49.728c3.02 3.245 1.7-3.704 4.03.92-.45 1.384-2.05 4.268-4.03-.92zm-26.04-24.025c-.68 1.877-2.42 2.15-3.27 3.786-1.27-2.173.69-7.483 3.27-3.786zm-31.53-21.376c.16 1.267.71 2.343 1.27 3.408-1.84-1.084-3.95 1.073-5.66-2.229 2.72 1.57.96-3.806 4.38-1.175l.01-.004zm71.72 35.81c-.71 3.432-4.3 5.344-6.64 7.635 1.71 1.475 3.78 2.768 3.89 5.096-4.72-6.145-2.57-.732-6.64-2.6-.66-3.689.38-4.298-3.74-9.291 7.97 7.052 7.53-2.174 13.13-.84zm250.04 154.173c-2.14-.332-.17 4.92-3.9 2.445.2-1.835 1.55-6.055 3.9-2.445zm-164.6-95.17c2.4 1.942 3.15 1.529 2.22-1.293 4.65 3.691 4.6 11.33.75 10.024-.01-4.414-.29-4.566-2.97-8.734v.003zm-149.07-96.833c-.92 2.908-4.35 1.983-6.38 3.204-.15-.947-.29-1.882-.43-2.817 3.09 1.133 3.76-1.449 6.82-.381l-.01-.006zm86.61 59.333c-1.65.54-4.06 3.791-7.16-.273 2.74 1.081 3.32-4.855 7.16.273zm-13.83-13.079c4.15 2.238 4.14-1.668 7.85-.106.29 4.582-4.99 4.829-7.41 2.948 1.31.623.28-1.684-.44-2.842zm-70.97-49.243c.24 1.551 2.43 2.106 2.83 3.594-1.95-1.058-1.05 2.229-3.79-.036-.49-2.429-.88-4.683.95-3.564l.01.006zm8.14-1.264c.84 3.663-.53 3.974-2.5 3.374.63-1.429.15-4.538 2.5-3.374zm56.57 40.526c-.3-4.575 2.62-1.066 6.54 1.649.18 1.596.65 3.061 1.44 4.316-.57-.366-1.14-.732-1.73-1.109-1.82-4.021-3.71-4.752-6.25-4.85v-.006zm-67.08-49.277c2.46-.084 2.08-4.438 6.58-1.431-1.34 1.688 2.53 6.315 1.22 7.531-2.95-.582-4.59-6.995-5.41-5.326.66 2.044 2.7 3.345 2.46 5.859-2.49-1.737-3.5-4.275-4.85-6.63v-.003zm15.41 6.151c.9 1.576 1.54 3.284 1.62 5.287-3.36-3.137-2.07.702-4.82-1.547-.1-.638-.21-1.258-.3-1.875 3.02 2.392 1.27-1.667 3.5-1.865zm64.95 37.996c-1.71.881-1.55 10.514-5.75 3.162 4.13 2.182 1.44-5.609 5.75-3.162zm18.96 5.856c.09 2.046-.21 3.535-1.82 3.133-1.56-2.222-.92-4.969 1.82-3.133zm-5.35-8.41c-3.33-.394-4.78 8.424-10.05.536 4.9 2.542 5.91-4.344 10.05-.536zm25.57 11.43c.93 3.238-.43 3.215-1.83 3.14-1.67-2.361-.33-4.103 1.83-3.14zm-10.36-9.694c6.46 5.129 4.85-1.225 8.72.213 2.07 5.278-4.61 3.809-7.02 5.062-.03-2.063-1.96-3.036-1.69-5.269l-.01-.006zm-1.37-3.383c4.93 2.788-1.67-6.929 3.07-4.892-.13 3.661.31 2.402 1.88 6.224-.9.823-3.09 3.432-4.95-1.338v.006zm13.23-5.47c1.07 3.624.11 4.415-1.65 4.067-1.89-2.742-2.17-7.207 1.65-4.067zm-62.9-42.238c1.62 4.648-1.15 2.988-2.43 3.471.6-1.459.04-4.572 2.43-3.471zm-2.77-2.59c.88 2.701-2.08 3.402-3.81.076 2.22 1.34.18-3.451 3.81-.076zm10.92 5.242c.46 1.825 3.05 2.425 3.83 4.068.7 2.62.42 3.859-1.93 2.12.2-1.946-4.53-6.281-1.89-6.198l-.01.01zm-11.29-15.435c.68 1.284 1.09 4.521 3.66 7.302-.26-1.989 1.8-.642 3.98.844-.32 3.002 1.09 8.491-2.6 6.677.1-5.104-10.72-16.429-5.04-14.823zm88.21 59.732c1.71 3.613-1.51 3.928-3.02.769 1.63.613 0-3.285 3.02-.769zm-75.18-51.516c.71 2.613.44 3.842-1.9 2.125-.71-2.607-.43-3.836 1.9-2.125zm59.24 37.897c1.14-1.701 3.44-4.672 6.88-.994-.52-3.222 4.83-.694 1.18-6.036 1.19.697 2.38 1.388 3.55 2.077 1.12 3.331-1.09 1.959.93 4.753-3.38-.62-4.45-.025-1.39 5.077-2.58-.541-2.44-2.76-3.91-3.996l.9 4.757c-4.81-3.157-4.67-6.425-8.13-5.645l-.01.007zm15.87-3.303c5.16 3.152 3.17 8.941-.32 6.546-.48-1.483-1.77-2.45-2.33-3.887 1.92-1.91 5.51 4.297 2.65-2.655v-.004zm.11-7.496c3.71 1.623 3.92 5.39 4.86 8.707-1.39-.73-.24 2.002-2.31.348-2.32-5.661.57-3.147-2.56-9.051l.01-.004zm21.25 11.429c-.05 2.017.19 4.43-2.65 2.678-.83-3.212.16-3.948 2.65-2.678zm237.6 98.831c-.31-2.1 1.25-5.608 3.88-1.783-2.39-.978-1.45 2.637.11 4.456-1.63-.665-2.98-1.536-3.99-2.677v.004zm-227.34-122.337c5.28 2.955 4.08 11.856-.39 5.642 2.93.891-1.03-3.941.39-5.642zm232.06 116.539c.83-2.86 2.58-8.339 7.78-3.579-4.39-.07 1.84 4.377 2.45 7.238-3.52-1.498-5.15-4.462-7.51-6.855 1.68 4.254.61 7.11-2.72 3.19v.006zm10.82-9.24c-1.49.136-.77 2.886-2.95 2.203-3.88-4.163 1.4-6.63 2.95-2.203zm-288.42-165.256c.22 2.483-.9 3.205-3.37 2.145-1.37-4.011 1.03-3.021 3.37-2.145zm2.71-8.231c-3.45-6.18 1.47-1.193 2.68-1.726-3.79-3.238-4.23-6.992-5.32-9.511 4.05 2.072 2.84 7.522 6.87 9.579-.56-1.411-1.68-2.506-1.76-4.251.45-.367 4.47.747 5.35 3.026-3.81-1.232-.57 16.716-7.39 4.738-1.38.829-1.98 6.096-5.77 1.582-1.37-3.32 1.77-6.599 5.35-3.45l-.01.013zm-3.52-8.494c.32 3.655-1.64 4.367-3.35 5.378.05-3.195-.15-6.696 3.35-5.378zm303.93 152.633c3.09.517 1.18-8.373 5.97-3.575-2.01.946-.91 8.372-5.97 3.575zm-300.15-159.374c1.36.909 3.2 1.538 3.79 2.951-3.38-1.15-3.52.037-1.34 4.083-4.2-3.404-2.62-4.925-2.45-7.038v.004zm12.49 3.824c2.91 2.233.31-2.611 4.01.629.29 2.19-2.74 2.931-4.01-.629zm-14.96-14.066c1.96.883 3.4 2.115 4.01 3.876-1.01-.441-2.57-1.602-2.44-.581-.03.612.43 1.833-.45 1.355-.37-1.553-.74-3.091-1.12-4.644v-.006zm319.34 154.021c.63 1.43 1.83 2.422 1.93 4.258-3.26-2.088-2.58.307-4.31-.049-.6-2.988-.77-5.493 2.39-4.212l-.01.003zm-293.48-149.195c.9 1.56 2.7.578 3.33 1.023-.55-1.46-1.87-2.394-2.5-3.788 1.23-1.793 4.92 1.702 6.07 5.721-2.27.565-3.32 2.648-6.39 2.169.04-2.429-1.09-4.709-.52-5.131l.01.006zm304.75 149.507c-1.27-.518-2.56-1.044-3.84-1.574-1.69-4.03 3.67-1.837 3.84 1.574zm-293.95-152.508c.04-1.846.6-3.051 3.52-1.314.94 3.081-.47 3.236-2.38 2.741-.2-.598-.43-1.191-1.14-1.427zm300.01 129.002c1.72.3 1.87-1.15 4.3-.067.21 1.248 4.7 4.46 1.99 4.27-.88-2.38-3.2-3.286-2.91-1.144a12.43 12.43 0 013.67 4.055c-1.91-.73-.54 2.17-3.35.437-.06-3.51-3.66-4.063-3.7-7.557v.006zm-295.85-142.808c2.56.405 3.48-4.485 6.64-1.25-1.81-.34-1.4 2.047-2.39 2.716 1.76 1.12 1.72.03 3.37 1.002.12 1.895-.55 3.796 1.71 6.519-3.42-2.79-6.49-5.813-9.32-8.985l-.01-.002zm302.93 136.863c.9 2.005 2.72 3.229 3.21 5.588-2.55-1.181-6.73-6.148-3.21-5.588zm-293.17-138.062c4.1 1.951 6.46 5.084 8.45 8.472-1.25-.694-1.87-.609-2.47-.5-1.38-3.081-5.52-4.258-5.99-7.969l.01-.003zm299.41 138.733c.96 2.633.77 4.035-1.87 2.758-.94-2.634-.76-4.033 1.87-2.758zm-296.85-143.756c-1.12 1.973-1.94 4.318-5.28 3.579 1.24-2.044.55-3.606 1.9-4.573.38.818 1.05 1.48 2.06 1.88-.2-1.074-.33-2.048 1.32-.892v.006zm4.93 1.006c.59 1.433 2.27 2.103 3.21 3.282.6 4.278-1.76 4.972-5.03 4.543-.49-2.595-2.8-3.936-3.28-6.531 2.28.286 2.63-1.803 5.11-1.288l-.01-.006zm296.47 130.632c.57 3.079 1.47 5.874 3.77 7.48-2.67-.85-1.8 2.15-3.8 2.025-2.02-6.037-3.4-1.727-4.91-5.339 3.98 1.145 2.24-3.937 4.95-4.16l-.01-.006zm-300.52-139.917c1.66.698 2.29 2.096 3.88 2.835-.25-.928-1.03-1.481-1.41-2.329 2.76.629 3.33 2.843 3.73 5.128-1.04-.411-2.62-1.51-2.47-.5 1.09.703 1.38 1.973 1.66 3.252-.91-.476-1.81-.95-2.73-1.425-.78-4.289-.32-3.163-2.64-6.953l-.02-.008zm12.64 6.562c-.38-2.531 2.26-1.425 3.23-2.341 1.82 4.265.3 6.433-3.23 2.341zm2.44-10.726c.51-1.25.07-3.625 2.31-2.807 1.94 3.949-1.06 2.03-1.4 3.267.76.567 1.79.937 2.07 1.864-1.24-.608-2.11-1.465-2.98-2.324zm11.51-.513c-2.31-.681-1.16 2.735-4.4.953-.68-2.497 1.74-4.459 4.4-.953zm.53 1.858c.65.158.84-.241.38-1.398 1.99.779 3.49 1.953 4.17 3.698-1.24-.441-1.11.749-2.22.463-.35-1.22-1.82-1.64-2.33-2.763zm8.02-4.721c1.17 4.656-.89 5.538-4 5.156-.89-4.345 3.43-2.521 4-5.156zm-5.51-8.312c3.49 5.279.92 6.132-1.25 6.51-1.22-4.11-1.72-7.342 1.25-6.51zm15.77 3.1c1.33.911 2.14 2.187 3.93 2.73.81 3.601.58 5.997-3.62 3.772 1.42-1.54 1.25-4.63-.31-6.502zm9.78-24.558c.96 2.499 1.19-.507 3.67 1.755 1.36 2.612-2.71 2.053-4.01-.331 1.65.587-.99-1.786.34-1.424zm113.53-114.567c1.77.219 1.75-1.203 4.9.29 1.94 3.9 1.24 5.383.65 6.956l-2.83-1.031c.08-2.923.41-2.143-2.71-6.218l-.01.003zm12.51-5.912c-4.39-1.718-.33 5.007-5.81-.575 2.29.803 2.45-2.942 5.81.575zm310.42 29.526c2.82 7.486 4.03-3.11 8.85 6.165-4.1-.783-4.09-1.372-5.58.337-1.85-1.778-6.71-6.092-3.26-6.496l-.01-.006zm6.15 10.313c-1.13.02-2.94-.443-2.58.606 1.14 1.656 6.98 4.482 4.74 5.756-1.93-.887-2.96-3.097-5.43-3.235.81-2.643-5.53-6.693-4.56-9.22 3.02 1.405 6.27 2.413 7.83 6.093zM1644.99-7.359c-1.2-2.533.54-2.548 1.6-3.152 2.4 1.993.63 2.65 3.05 6.1-2.26-.173-2.76-2.331-4.65-2.948zm294.41 56.188c3.23.508 5.22 2.92 7.93 4.26-2.21.07 1.64 4.32-2.14 3.327-3.06-3.771-2.39-3.687-5.79-7.587zm-286.99-60.432c-2.58.29 2.37 3.796-.66 3.45-1.82-1.053-3.65-7.692.66-3.45zm288.54 59.71c-1.21-2.02-.69-2.846 1.59-2.484 1.55 1.682 2.6 2.954 1.9 3.685-.72-1.09-2.12-1.144-3.49-1.2zm-242.04-162.834c-3.13-.469.88 3.844-1.58 3.824-3.33-3.602-2.22-6.461 1.58-3.824zm1.85-5.85c-1.31 1.346-.48 4.1-3.9 4.045-1.96-3.509-1.09-5.106 3.9-4.045zm-2.28-4.571l2.17 3.177c-.97-.193-1.91-.372-2.87-.562-2.57-2.714-.23-2.194.7-2.615zm181.19-47.398c3.2-.125 5.53 2.012 7.73 4.463 1.36 2.907.18 5.795 2.45 10.407-1.26-.077-2.7.216-3.59-.69-3.05-3.822-5.19-9.798-6.59-14.19v.01zm3.54-11.383c1.89.004 3.67.298 5.18 1.28-.2 3.122 1.61 7.146 0 9.662-2.26-3.911-2.9-7.083-5.17-10.945l-.01.003zm-167.59-7.093c2.98 1.877 4.97 5.96 2.62 6.259-3.49-3.513-2.37-4.505-2.62-6.259zm164.28-8.548c3.06 5.292 3.58 9.066 9.79 13.629l-3.88.259c-.85-3.243-10.63-10.518-5.9-13.882l-.01-.006zm-178.52-25.789c-1.19-1.202-1.78-2.113-1.63-2.659 4.51.238 6.95 4.579 11.78 4.169.13 1.453 3.31 4.4-.87 3.758 1.08-1.967-4.11-5.112-9.28-5.268zm190.65-4.249c2.59-.467 4.35 1.721 6.6 2.38-1.55.541.22 2.293-3.43 2.061-1.65-1.702-.39-2.34-3.17-4.441zm-72.01-8.579c1.32-.693-4.28-3.6-5.82-4.746 2.16-.971 3.39.924 5.17 1.122-.32-.552-.01-.838 1.21-.809.29 2.206-.95 3.567 4.85 4.81-.24.958-3.29.531-5.42-.38l.01.003zm-83.05-16.503c-2.7-.225 1.87 2.521-1.77 1.904-3.34-2.042-1.16-3.653 1.77-1.904zm3.54-9.014c2.03 2.636 4 5.259.7 5.828.37-1.743-4.04-5.281-.7-5.828zm3.95-5.877c1.32 1.477 2.7 2.992-.94 2.603-1.68-1.623-1.37-2.484.94-2.603zm-16.69-5.503c2.94.147 3.05-.19.45-1.26 3.2-.452 5.68 3.658 2.63 3.127-.96-.87-2.01-1.369-3.08-1.867zm46.94-50.194c3.09.765 4.78 3.077 4.44 4.082-2.27-.585-7.09-3.672-4.44-4.082z" fill="' + color + '"/></g></g><defs><clipPath id="prefix__clip0_58_843"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath><clipPath id="prefix__clip1_58_843"><path fill="#fff" transform="rotate(-30 788.145 -1314.71)" d="M0 0h1234.03v706.511H0z"/></clipPath></defs></svg>'
            },
			'mask-10': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1920 615.353V340.175l-137.59 137.589L1920 615.353zM1919.24 954l-167.18-167.177-139.35-139.353L1443 477.764l169.71-169.706 139.35-139.352L1920 .764V0h-137.19L1345 437.812 907.188 0H0v954h907l84.447-84.447 268.703-268.7L1345 516l84.85 84.853 268.7 268.7L1783 954h136.24zM1345 685.706L1613.29 954H1526l-138.57-138.574L1345 773l-42.43 42.426L1164 954h-87.29L1345 685.706zM1248.85 954l96.15-96.147L1441.15 954h-192.3z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_58_894)"><path stroke="' + color + '" stroke-width="120" d="M1345 600.853l353.553 353.553L1345 1307.96 991.447 954.406z"/><path stroke="' + color + '" stroke-width="60" d="M1345 815.426L1633.574 1104 1345 1392.574 1056.426 1104z"/><path fill="' + color + '" d="M1345-439L1783.406-.594 1345 437.812 906.594-.594z"/><path stroke="' + color + '" stroke-width="240" d="M1921.76 168.706l309.059 309.059-309.059 309.058-309.059-309.058z"/></g><defs><clipPath id="prefix__clip0_58_894"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath></defs></svg>'
            },
			'mask-11': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0 142.846l198.069 114.355L346.565 0H1920v277.018L1802.38 73.291l-734.85 424.264L1331.06 954H0V142.846zm649.936 713.71l18.301-68.301 68.301 18.301-18.301 68.301-68.301-18.301z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_58_766)" fill="' + color + '"><path d="M1802.38 73.292l-734.85 424.264L1491.8 1232.4l734.84-424.262-424.26-734.846zM42.778-322.354L-169.354 45.069l367.423 212.132 212.132-367.423L42.778-322.354zM649.936 856.556l18.301-68.301 68.302 18.301-18.302 68.301-68.301-18.301z"/></g><defs><clipPath id="prefix__clip0_58_766"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath></defs></svg>'
            },
			'mask-12': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0 708.798V954h1326.42l-3.72-13.882a95.994 95.994 0 0124.84-92.729l147.89-147.89c24.26-24.252 59.6-33.724 92.73-24.847l202.02 54.132a96.012 96.012 0 0167.89 67.882L1900.22 954H1920V0H767.891a199.9 199.9 0 012.183 111.078L669.378 486.881c-18.493 69.019-72.403 122.928-141.421 141.422L152.154 728.999C100.099 742.947 45.414 735.151 0 708.798zM1595.66 258.87a23.996 23.996 0 01-23.18-6.211l-36.98-36.973a24.004 24.004 0 01-6.21-23.182l13.53-50.506a24 24 0 0116.98-16.97l50.5-13.533c8.28-2.22 17.12.148 23.18 6.211l36.98 36.973a24.004 24.004 0 016.21 23.182l-13.54 50.506a23.97 23.97 0 01-16.97 16.97l-50.5 13.533z" fill="' + color + '"/></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_58_745)" fill="' + color + '"><path d="M250.018-408.977c69.019-18.494 142.66 1.238 193.185 51.763L718.31-82.107c50.525 50.525 70.258 124.167 51.764 193.185L669.378 486.881c-18.493 69.019-72.403 122.928-141.421 141.422L152.154 728.999c-69.019 18.493-142.66-1.239-193.186-51.764l-275.106-275.107c-50.525-50.525-70.258-124.167-51.764-193.185l100.696-375.803c18.493-69.018 72.403-122.928 141.421-141.421l375.803-100.696zM1646.73 1264.15c33.13 8.88 68.48-.59 92.73-24.84l147.89-147.89a96.031 96.031 0 0024.85-92.732l-54.13-202.022a96.012 96.012 0 00-67.89-67.882l-202.02-54.132c-33.13-8.877-68.47.595-92.73 24.847l-147.89 147.89a95.994 95.994 0 00-24.84 92.729l54.13 202.022a95.967 95.967 0 0067.88 67.88l202.02 54.13zM1572.48 252.659a23.996 23.996 0 0023.18 6.211l50.5-13.533a23.97 23.97 0 0016.97-16.97l13.54-50.506a24.004 24.004 0 00-6.21-23.182l-36.98-36.973a23.993 23.993 0 00-23.18-6.211l-50.5 13.533a24 24 0 00-16.98 16.97l-13.53 50.506a24.004 24.004 0 006.21 23.182l36.98 36.973z"/></g><defs><clipPath id="prefix__clip0_58_745"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath></defs></svg>'
            },
			'mask-13': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_75_23203)"><path fill-rule="evenodd" clip-rule="evenodd" d="M1920 0H0v859.506l-190.573 51.064 35.756 133.44L32.783 954H51.31l-375.326 172.87 90.136 394.4L565.33 954h19.141c-170.193 122.31-368.853 265.1-580.537 417.25l-.165.12-135.29 97.24 957.378-451.83L893.286 954h47.639L-114.454 2058.62l62.826 234.47L1103.34 954h27.42L103.93 2180l65.439 244.22 705.555-863.57 240.546-94.82L1470.23 954h53.31L191.011 2939.16l104.974 391.76 375.717-100.67 377.978-571.43c13.98-21.33 164.03-221.74 151.7-199.8L787.965 3199.1l348.975-93.51L2169.62 804.001l-131.46-490.62L1935 341.024l-15 22.347v-58.283l61.63-88.923L1920 232.68V0zm0 232.68l-238.67 63.951L1130.76 954h339.47L1920 305.088V232.68zm0 130.691L1523.54 954H1920V363.371zM1103.34 954l127.91-148.297 60.47-169.07-5.85-43.671L940.925 954h162.415zm248.69-427.1L893.286 954H584.471c248.761-178.774 436.699-313.798 515.599-370.351l251.96-56.749zm0 0l.03-.029.01.021-.04.008zM565.33 954l434.321-308.275-553.752 126.532L51.311 954H565.33zm-532.548 0l487.485-233.9L0 859.506V954h32.782zM1528.04 468.975l-174.96 202.889-25.3-117.832 40.22-42.175 160.04-42.882zm931.55 1244.855L2213 793.524l-218.25 490.736-.14.24 156.33 1465.08 131.12-35.13 177.53-1000.62zm-520.18-346.59l21.42 218.34-146.61 63.17 125.19-281.51zm84.33 588.28l-29.58-300.33-157.39 86.36-225.64 506.93 412.61-292.96zm-112.76 125.5l87.14 889.52-885.51 237.27 364.48-819.53 433.89-307.26zm668.86 220.36l104.4 389.61-384.91 103.14 27.46-149.15-.05-.19 253.1-343.41zM807.326 1730.06l172.895-51.15-812.664 1172.72-67.065-250.29 706.834-871.28zM-89.52 1852.55c-.896-.13-1.789-.26-2.679-.4l.006.03c-28.186-4.25-56.008-10.99-83.04-20.39l53.373 129.55 75.04-98.45c-13.099-6.01-28.262-8.23-42.698-10.34h-.002zm829.267-783.16l-722.21 797.48c-31.83-8.57-62.09-22.77-92.32-36.95h-.002c-17.155-8.04-34.3-16.09-51.718-23.09l-56.788-179.13c255.882-154.84 738.844-446.96 923.038-558.31zm558.503-416.072l14.14 76.974-82.22 95.978 68.08-172.952zM2478.63 1923.68l-163.84 701.82 248.83-311.25-84.99-390.57zm-2799.147-309l-5.334 4.37-.056-.21.41-1.57 4.582-2.48.398-.11z" fill="' + color + '"/></g><defs><clipPath id="prefix__clip0_75_23203"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath></defs></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_75_22965)" fill="' + color + '"><path d="M2579.84 2301.38l104.4 389.61-384.91 103.14 27.46-149.15-.05-.19 253.1-343.41zM1910.98 2081.02l87.14 889.52-885.51 237.27 364.48-819.53 433.89-307.26zM1994.16 1655.19l29.58 300.33-412.61 292.96 225.64-506.93 157.39-86.36zM1939.41 1367.24l21.42 218.34-146.61 63.17 125.19-281.51zM2213 793.524l246.59 920.306-177.53 1000.62-131.12 35.13-156.33-1465.08.14-.24L2213 793.524zM807.326 1730.06l172.895-51.15-812.664 1172.72-67.065-250.29 706.834-871.28zM520.267 720.1l-675.084 323.91-35.756-133.44 710.84-190.47zM999.651 645.725l-1233.53 875.545-90.136-394.4 769.914-354.613 553.752-126.532zM-92.199 1852.15c15.225 2.25 31.467 4.36 45.379 10.74l-75.04 98.45-53.373-129.55c27.032 9.4 54.854 16.14 83.04 20.39l-.006-.03zM739.747 1069.39l-722.21 797.48c-49.891-13.44-95.926-40.69-144.04-60.04l-56.788-179.13c255.882-154.84 738.844-446.96 923.038-558.31zM1352.06 526.871L825.857 1016.78l-957.378 451.83C447.028 1052.77 955.82 687.05 1100.07 583.649l252-56.757-.01-.021zM1298.25 653.318l14.14 76.974-82.22 95.978 68.08-172.952zM1285.87 592.962l5.85 43.671-60.47 169.07L-51.628 2293.09l-62.826-234.47L1285.87 592.962zM1528.04 468.975l-174.96 202.889-25.3-117.832 40.22-42.175 160.04-42.882z"/><path d="M1981.63 216.165L1115.47 1465.83l-240.546 94.82-705.555 863.57L103.93 2180l1577.4-1883.369 300.3-80.466zM2038.16 313.381l131.46 490.62L1136.94 3105.59l-348.975 93.51 413.415-740.08c12.33-21.94-137.72 178.47-151.7 199.8l-377.978 571.43-375.717 100.67-104.974-391.76L1935 341.024l103.16-27.643zM2314.79 2625.5l163.84-701.82 84.99 390.57-248.83 311.25zM-320.517 1614.68l-5.334 4.37-.056-.21.41-1.57 4.582-2.48.398-.11z"/><path d="M-320.517 1614.68l-5.334 4.37-.056-.21.41-1.57 4.582-2.48.398-.11z"/><path d="M-320.517 1614.68l-5.334 4.37-.056-.21 4.992-4.05.398-.11z"/><path d="M-320.517 1614.68l-5.334 4.37-.056-.21.41-1.57 4.582-2.48.398-.11z"/></g><defs><clipPath id="prefix__clip0_75_22965"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath></defs></svg>'
            },
			'mask-14': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_75_23245)"><path fill-rule="evenodd" clip-rule="evenodd" d="M2351.91 237.689l-852.66-492.288L1352.25 0H0v954h801.461l-.689 1.194 852.668 492.286 10.79-18.7L841.885 954h136.628l719.877 415.62 16.46-28.51L1044.35 954h109.29l588.53 339.79 20.98-36.33L1237.53 954h81.97l464.14 267.97 30.74-53.25L1442.47 954h54.66l330.91 191.06 46.45-80.46L1682.93 954h27.3l171.09 98.78 54.86-95.016-16.18-9.339v-7.881l19.59 11.31 412.32-714.165zM1920 948.425l-836.49-482.948-54.86 95.015L1710.23 954H1920v-5.575zM1682.93 954l-661.1-381.686-46.453 80.454L1497.13 954h185.8zm-240.46 0L961.712 676.436l-30.743 53.248L1319.5 954h122.97zm-204.94 0L910.479 765.174 889.506 801.5 1153.64 954h83.89zm-193.18 0L862.184 848.824l-16.46 28.51L978.513 954h65.837zm-202.465 0l-30.318-17.504L801.461 954h40.424zM1920 940.544l-833.08-480.978L1352.25 0H1920v940.544zm-307.55 577.926l-852.666-492.28-6.215 10.76 852.671 492.29 6.21-10.77zm-906.694-398.7l852.664 492.28-2.6 4.51-852.663-492.29 2.599-4.5z" fill="' + color + '"/></g><defs><clipPath id="prefix__clip0_75_23245"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath></defs></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_75_23004)" fill="' + color + '"><path d="M2351.91 237.689l-852.66-492.288-412.33 714.165 852.67 492.288 412.32-714.165zM1936.18 957.764l-852.67-492.287-54.86 95.015 852.67 492.288 54.86-95.016zM1874.49 1064.6l-852.66-492.286-46.452 80.454 852.672 492.292 46.44-80.46zM1814.38 1168.72L961.712 676.436l-30.743 53.248 852.671 492.286 30.74-53.25zM1763.15 1257.46L910.479 765.174 889.507 801.5l852.663 492.29 20.98-36.33zM1714.85 1341.11L862.185 848.824l-16.461 28.51 852.666 492.286 16.46-28.51zM1664.23 1428.78L811.568 936.496l-10.796 18.698 852.668 492.286 10.79-18.7zM1612.45 1518.47l-852.666-492.28-6.215 10.76 852.671 492.29 6.21-10.77zM1558.42 1612.05l-852.663-492.28-2.599 4.5 852.662 492.29 2.6-4.51z"/></g><defs><clipPath id="prefix__clip0_75_23004"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath></defs></svg>'
            },
			'mask-15': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__prefix__clip0_77_23452)"><path fill-rule="evenodd" clip-rule="evenodd" d="M1353 308.5c0 255.507-131.58 485.45-341.45 645.5-187.291 142.83-436.933 230-711.05 230C-280.78 1184-752 792.025-752 308.5S-280.78-567 300.5-567c450.743 0 835.31 235.692 985.3 567 43.44 95.948 67.2 199.916 67.2 308.5z" fill="' + color + '"/><path d="M1321.57 538C1357.08 287.516 1273.7 89.91 1127-.418L1374.18-6c65.77 100.68-15.89 431.512-52.61 544zM312 955c432.242 0 746.77-180.667 850-271-90.34 157.09-176.766 246.121-208.688 271H312z" fill="' + accent_color + '" fill-opacity=".25"/><path d="M1344.5 427c0-252.4-212.67-390.833-319-428.5H1373c70 82.4 10.17 320-28.5 428.5z" fill="' + accent_color + '" fill-opacity=".1"/><path fill-rule="evenodd" clip-rule="evenodd" d="M1337 184.5c0 324.402-198.63 609.049-497.356 769.5H0V0h1285.8c-149.99-331.308-534.557-567-985.3-567C-280.78-567-752-175.025-752 308.5c0 40.898 3.371 81.142 9.895 120.549C-617.527 811.461-207.995 1092 278 1092c206.311 0 398.842-50.56 561.644-138h171.906C1221.42 793.95 1353 564.007 1353 308.5c0-53.218-5.71-105.326-16.64-155.925.42 10.596.64 21.239.64 31.925z" fill="' + accent_color + '" fill-opacity=".5"/></g><defs><clipPath id="prefix__prefix__clip0_77_23452"><path fill="' + accent_color + '" d="M0 0h1920v954H0z"/></clipPath></defs></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_75_23031)" fill="' + color + '"><path d="M1321.57 538C1357.08 287.516 1273.7 89.91 1127-.418L1374.18-6c65.77 100.68-15.89 431.512-52.61 544zM312 955c432.242 0 746.77-180.667 850-271-90.34 157.09-176.766 246.121-208.688 271H312z" fill="' + accent_color + '" fill-opacity=".25"/><path d="M1344.5 427c0-252.4-212.67-390.833-319-428.5H1373c70 82.4 10.17 320-28.5 428.5z" fill="' + accent_color + '" fill-opacity=".1"/><path fill-rule="evenodd" clip-rule="evenodd" d="M839.644 954C1138.37 793.549 1337 508.902 1337 184.5c0-63.218-7.54-124.926-21.9-184.5H1920v954H839.644zm0 0C676.842 1041.44 484.311 1092 278 1092c-584.87 0-1059-406.302-1059-907.5S-306.87-723 278-723c511.098 0 937.63 310.269 1037.1 723H0v954h839.644z" fill="' + accent_color + '" fill-opacity=".5"/><path fill-rule="evenodd" clip-rule="evenodd" d="M1011.55 954C1221.42 793.95 1353 564.007 1353 308.5c0-108.584-23.76-212.552-67.2-308.5H1920v954h-908.45zm0 0c-187.291 142.83-436.933 230-711.05 230C-280.78 1184-752 792.025-752 308.5S-280.78-567 300.5-567c450.743 0 835.31 235.692 985.3 567H0v954h1011.55z"/></g><defs><clipPath id="prefix__clip0_75_23031"><path fill="#fff" d="M0 0h1920v954H0z"/></clipPath></defs></svg>'
			},
			'mask-16': {
				'default': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#prefix__clip0_77_23308)"><path fill-rule="evenodd" clip-rule="evenodd" d="M1173 0H0v954h1313l-140-238.5h70l70-119h70L1313 477l70-119.5h-70l-70-119h-70l70-119.5-70-119zm747 0v954h113V0h-113z" fill="' + color + '"/><path d="M1102.98 835l69.98 119H1033l69.98-119z" fill="' + accent_color + '" fill-opacity=".1"/><path d="M1382.91 834.998l69.98 119h-139.96l69.98-119z" fill="' + accent_color + '" fill-opacity=".24"/><path d="M1172.96 954l-69.98-119h139.97l-69.99 119z" fill="' + accent_color + '" fill-opacity=".4"/><path d="M1032.98 715.941l-69.98 119h139.96l-69.98-119z" fill="' + accent_color + '" fill-opacity=".12"/><path d="M1172.96 715.5l69.99 119.5h-139.97l69.98-119.5z" fill="' + accent_color + '" fill-opacity=".55"/><path d="M1102.98 834.998l69.98-119.5H1033l69.98 119.5z" fill="' + accent_color + '" fill-opacity=".25"/><path d="M1382.91 834.998l-69.98-119.5h139.96l-69.98 119.5z" fill="' + accent_color + '" fill-opacity=".85"/><path d="M1172.96 596.5l69.99 119h-139.97l69.98-119z" fill="' + accent_color + '" fill-opacity=".75"/><path d="M1242.95 715.5l69.98-119h-139.97l69.99 119z" fill="' + accent_color + '" fill-opacity=".85"/><path d="M1242.98 596.5l69.98-119.5H1173l69.98 119.5z" fill="' + accent_color + '" fill-opacity=".7"/><path d="M1032.98 477l69.98 119.5H963l69.98-119.5z" fill="' + accent_color + '" fill-opacity=".05"/><path d="M1312.93 477l69.98 119.5h-139.96l69.98-119.5z" fill="' + accent_color + '" fill-opacity=".9"/><path d="M1102.98 119.002l69.98-119H1033l69.98 119z" fill="' + accent_color + '" fill-opacity=".1"/><path d="M1312.93 0l69.98 119h-139.96l69.98-119z" fill="' + accent_color + '" fill-opacity=".7"/><path d="M1172.96 0l-69.98 119h139.97L1172.96 0z" fill="' + accent_color + '" fill-opacity=".25"/><path d="M1172.98 238.5l69.98-119.5H1103l69.98 119.5z" fill="' + accent_color + '" fill-opacity=".42"/><path d="M1032.98 238.5l69.98-119.5H963l69.98 119.5z" fill="' + accent_color + '" fill-opacity=".24"/><path d="M1172.96 357.5l69.99-119h-139.97l69.98 119z" fill="' + accent_color + '" fill-opacity=".15"/><path d="M1242.95 238.5l69.98 119h-139.97l69.99-119z" fill="' + accent_color + '" fill-opacity=".65"/><path d="M1102.98 357.5l69.98 119.5H1033l69.98-119.5z" fill="' + accent_color + '" fill-opacity=".3"/><path d="M1312.98 477l69.98-119.5H1243l69.98 119.5z" fill="' + accent_color + '" fill-opacity=".85"/><path d="M1172.96 477l69.99-119.5h-139.97l69.98 119.5z" fill="' + accent_color + '" fill-opacity=".75"/></g><defs><clipPath id="prefix__clip0_77_23308"><path fill="' + accent_color + '" d="M0 0h1920v954H0z"/></clipPath></defs></svg>',
				'inverted': '<svg width="1920" height="954" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1102.98 835l69.98 119H1033l69.98-119z" fill="' + accent_color + '" fill-opacity=".1"/><path d="M1382.91 834.998l69.99 119h-139.97l69.98-119z" fill="' + accent_color + '" fill-opacity=".24"/><path d="M1452.9 954l69.98-119h-139.97l69.99 119z" fill="' + color + '"/><path d="M1172.96 954l-69.98-119h139.97l-69.99 119z" fill="' + accent_color + '" fill-opacity=".4"/><path d="M1032.98 715.941l-69.98 119h139.96l-69.98-119z" fill="' + accent_color + '" fill-opacity=".12"/><path d="M1522.88 834.998l-69.99 119h139.97l-69.98-119z" fill="' + color + '"/><path d="M1172.96 715.5l69.99 119.5h-139.97l69.98-119.5z" fill="' + accent_color + '" fill-opacity=".55"/><path d="M1242.95 835l69.98-119.5h-139.97l69.99 119.5z" fill="' + color + '"/><path d="M1312.98 953.5l69.98-119.5H1243l69.98 119.5z" fill="' + color + '"/><path d="M1102.98 834.998l69.98-119.5H1033l69.98 119.5z" fill="' + accent_color + '" fill-opacity=".25"/><path d="M1312.93 715.5L1242.95 835h139.96l-69.98-119.5zm139.96 0L1382.91 835h139.97l-69.99-119.5z" fill="' + color + '"/><path d="M1382.91 834.998l-69.98-119.5h139.97l-69.99 119.5z" fill="' + accent_color + '" fill-opacity=".85"/><path d="M1172.96 596.5l69.99 119h-139.97l69.98-119z" fill="' + accent_color + '" fill-opacity=".75"/><path d="M1522.88 834.998l69.98-119.5H1452.9l69.98 119.5z" fill="' + color + '"/><path d="M1242.95 715.5l69.98-119h-139.97l69.99 119z" fill="' + accent_color + '" fill-opacity=".85"/><path d="M1452.9 596.5l69.98 119h-139.97l69.99-119z" fill="' + color + '"/><path d="M1522.88 715.5l69.98-119H1452.9l69.98 119zm-209.95-119l69.98 119h-139.96l69.98-119z" fill="' + color + '"/><path d="M1382.91 715.5l69.99-119h-139.97l69.98 119z" fill="' + color + '"/><path d="M1242.98 596.5l69.98-119.5H1173l69.98 119.5z" fill="' + accent_color + '" fill-opacity=".7"/><path d="M1032.98 477l69.98 119.5H963l69.98-119.5z" fill="' + accent_color + '" fill-opacity=".05"/><path d="M1452.9 477l69.98 119.5h-139.97L1452.9 477z" fill="' + color + '"/><path d="M1522.88 596.5l69.98-119.5H1452.9l69.98 119.5z" fill="' + color + '"/><path d="M1312.93 477l69.98 119.5h-139.96l69.98-119.5z" fill="' + accent_color + '" fill-opacity=".9"/><path d="M1382.91 596.5L1452.9 477h-139.97l69.98 119.5z" fill="' + color + '"/><path d="M1102.98 119.002l69.98-119H1033l69.98 119z" fill="' + accent_color + '" fill-opacity=".1"/><path d="M1452.89 0l69.99 119h-139.97l69.98-119zm-209.94 119l69.98-119h-139.97l69.99 119z" fill="' + color + '"/><path d="M1382.98 119l69.98-119H1313l69.98 119z" fill="' + color + '"/><path d="M1312.93 0l69.98 119h-139.96l69.98-119z" fill="' + accent_color + '" fill-opacity=".7"/><path d="M1172.96 0l-69.98 119h139.97L1172.96 0z" fill="' + accent_color + '" fill-opacity=".25"/><path d="M1522.88 119.002l-69.99-119h139.97l-69.98 119zM1242.96 119l69.99 119.5h-139.97l69.98-119.5z" fill="' + color + '"/><path d="M1172.98 238.5l69.98-119.5H1103l69.98 119.5z" fill="' + accent_color + '" fill-opacity=".42"/><path d="M1032.98 238.5l69.98-119.5H963l69.98 119.5z" fill="' + accent_color + '" fill-opacity=".24"/><path d="M1312.93 238.5L1242.95 119h139.96l-69.98 119.5zm139.96 0L1382.91 119h139.97l-69.99 119.5zm-69.98-119.498l-69.98 119.5h139.96l-69.98-119.5z" fill="' + color + '"/><path d="M1172.96 357.5l69.99-119h-139.97l69.98 119z" fill="' + accent_color + '" fill-opacity=".15"/><path d="M1522.88 119.002l69.98 119.5h-139.97l69.99-119.5z" fill="' + color + '"/><path d="M1242.95 238.5l69.98 119h-139.97l69.99-119z" fill="' + accent_color + '" fill-opacity=".65"/><path d="M1452.89 357.5l69.99-119h-139.97l69.98 119zm69.99-119l69.98 119h-139.97l69.99-119zm-209.95 119l69.98-119h-139.96l69.98 119z" fill="' + color + '"/><path d="M1382.91 238.5l69.98 119h-139.96l69.98-119z" fill="' + color + '"/><path d="M1102.98 357.5l69.98 119.5H1033l69.98-119.5z" fill="' + accent_color + '" fill-opacity=".3"/><path d="M1312.98 477l69.98-119.5H1243l69.98 119.5z" fill="' + accent_color + '" fill-opacity=".85"/><path d="M1172.96 477l69.99-119.5h-139.97l69.98 119.5z" fill="' + accent_color + '" fill-opacity=".75"/><path d="M1452.89 477l69.99-119.5h-139.97l69.98 119.5zm69.99-119.5l69.98 119.5h-139.97l69.99-119.5z" fill="' + color + '"/><path d="M1382.91 357.5l69.98 119.5h-139.96l69.98-119.5zM1490 0h430v954h-430V0z" fill="' + color + '"/></svg>'
			}
		};

		return masks[ name ] ? masks[ name ][ style ] : '';
	},

	/**
	 * Get mask background element.
	 *
	 * @since 3.8
	 * @param {Object} values - The values.
	 * @return {String}
	 */
	fusionGetMaskElement: function( values ) {
				// Early exit if no pattern selected.
				if ( '' === values.mask_bg ) {
					return;
				}
				let style = '';
				if ( 'custom' === values.mask_bg ) {
					style += 'background-image:  url(' + values.mask_custom_bg + ');';
				} else {
					style += 'background-image:  url(data:image/svg+xml;base64,' + window.btoa( this.fusionGetMask( values.mask_bg, values.mask_bg_color, values.mask_bg_style, values.mask_bg_accent_color ) ) + ');';
				}

				if ( '' !== values.mask_bg_opacity ) {
					style += 'opacity: ' + ( parseInt( values.mask_bg_opacity ) / 100 ) + ' ;';
				}
				if ( '' !== values.mask_bg_transform ) {
					const transform = values.mask_bg_transform;
					let scaleX = 1;
					let scaleY = 1;

					if ( transform.includes( 'flip-Horizontal' ) ) {
						scaleX = -1;
					}
					if ( transform.includes( 'flip-vertical' ) ) {
						scaleY = -1;
					}

					style += 'transform: scale(' + scaleX + ', ' + scaleY + ');';
				}
				if ( '' !== values.mask_bg_blend_mode ) {
					style += 'mix-blend-mode:' + values.mask_bg_blend_mode + ';';
				}

				const element = '<div class="awb-background-mask" style="' + style + '"></div>';

				return element;
	},

	/**
	 * Generates gradient string for provided options.
	 *
	 * @since 2.1
	 * @param {Object} values - Element params.
	 * @param {string} type - Required gradient string type.
	 * @return {string} - Generated string.
	 */
	getGradientString: function( values, type ) {
		var gradientString          = '',
			alphaGradientStartColor = jQuery.AWB_Color( values.gradient_start_color ).alpha(),
			alphaGradientEndColor   = jQuery.AWB_Color( values.gradient_end_color ).alpha(),
			isGradientColor         = ( ! _.isEmpty( values.gradient_start_color ) && 0 !== alphaGradientStartColor ) || ( ! _.isEmpty( values.gradient_end_color ) && 0 !== alphaGradientEndColor ) ? true : false;

		if ( isGradientColor ) {
			if ( 'linear' === values.gradient_type ) {
				gradientString += 'linear-gradient(' + values.linear_angle + 'deg,';
			} else if ( 'radial' === values.gradient_type ) {
				gradientString += 'radial-gradient(circle at ' + values.radial_direction + ', ';
			}

			gradientString += values.gradient_start_color + ' ' + values.gradient_start_position + '%,';
			gradientString += values.gradient_end_color + ' ' + values.gradient_end_position + '%)';

			switch ( type ) {

			case 'main_bg':
			case 'parallax':
				if ( '' !== values.background_image && 'yes' !== values.fade ) {
					gradientString += ',url(\'' + values.background_image + '\')';
				}
				break;
			case 'fade':
			case 'column':
				if ( '' !== values.background_image ) {
					gradientString += ',url(\'' + values.background_image + '\')';
				}
				break;
			}
		}

		return gradientString;
	},

	/**
	 * Generates gradient string for provided options.
	 *
	 * @since 2.1
	 * @param {Object} values - Element params.
	 * @return {string} - Generated string.
	 */
	getGradientFontString: function( values ) {
		var gradientString          = '',
			gradientStart           = 'string' === typeof values.gradient_start_color && '' !== values.gradient_start_color ? values.gradient_start_color : 'rgba(255,255,255,0)',
			gradientEnd             = 'string' === typeof values.gradient_end_color && '' !== values.gradient_end_color ? values.gradient_end_color : 'rgba(255,255,255,0)',
			alphaGradientStartColor = jQuery.AWB_Color( gradientStart ).alpha(),
			alphaGradientEndColor   = jQuery.AWB_Color( gradientEnd ).alpha(),
			isGradientColor         = 0 !== alphaGradientStartColor || 0 !== alphaGradientEndColor;

		if ( isGradientColor ) {
			gradientString += 'background-color:' + gradientStart + ';';

			// Check for type and set accordingly.
			gradientString += 'background-image:';
			if ( 'linear' === values.gradient_type ) {
				gradientString += 'linear-gradient(' + values.linear_angle + 'deg, ';
			} else if ( 'radial' === values.gradient_type ) {
				gradientString += 'radial-gradient(circle at ' + values.radial_direction + ', ';
			}
			gradientString += gradientStart + ' ' + values.gradient_start_position + '%,';
			gradientString += gradientEnd + ' ' + values.gradient_end_position + '%);';
		}

		return gradientString;
	},

	/**
	 * Generates box-shadow style for provided options.
	 *
	 * @since 2.1
	 * @param {Object} values - The values.
	 * @return {string} - The generated CSS.
	 */
	fusionGetBoxShadowStyle: function( values ) {
		var style = '';

		style += _.fusionGetValueWithUnit( values.box_shadow_horizontal );
		style += ' ' + _.fusionGetValueWithUnit( values.box_shadow_vertical );
		style += ' ' + _.fusionGetValueWithUnit( values.box_shadow_blur );
		style += ' ' + _.fusionGetValueWithUnit( values.box_shadow_spread );
		style += ' ' + values.box_shadow_color;

		if ( 'undefined' !== typeof values.box_shadow_style && '' !== values.box_shadow_style ) {
			style += ' ' + values.box_shadow_style;
		}

		style += ';';

		return style;
	},

	/**
	 * Generates text-shadow style for provided options.
	 *
	 * @since 2.1
	 * @param {Object} values - The values.
	 * @return {string} - The generated CSS.
	 */
	fusionGetTextShadowStyle: function( values ) {
		var style = '';

		style += _.fusionGetValueWithUnit( values.text_shadow_horizontal );
		style += ' ' + _.fusionGetValueWithUnit( values.text_shadow_vertical );
		style += ' ' + _.fusionGetValueWithUnit( values.text_shadow_blur );
		style += ' ' + values.text_shadow_color;

		return style;
	},

	/**
	 * Get font family styling.
	 *
	 * @since 2.1
	 * @param {string} param_id - Param ID.
	 * @param {Object} values - The values.
	 * @param {string} format - Format of returned value, string or object.
	 * @return {mixed} - The generated styling.
	 */
	fusionGetFontStyle: function( param_id, values, format = 'string', important = false ) {
		var style     = {},
			style_str = '',
			weight    = '';

		if ( 'string' === typeof values[ 'fusion_font_family_' + param_id ] && '' !== values[ 'fusion_font_family_' + param_id ] ) {
			if ( values[ 'fusion_font_family_' + param_id ].includes( 'var(' ) ) {
				style[ 'font-family' ] = values[ 'fusion_font_family_' + param_id ];
				if ( 'object' === typeof window.awbTypographySelect ) {
					style[ 'font-weight' ] = window.awbTypographySelect.getVarString( values[ 'fusion_font_family_' + param_id ], 'font-weight' );
					style[ 'font-style' ]  = window.awbTypographySelect.getVarString( values[ 'fusion_font_family_' + param_id ], 'font-style' );
				}
			} else if ( values[ 'fusion_font_family_' + param_id ].includes( '\'' ) || 'inherit' === values[ 'fusion_font_family_' + param_id ] ) {
				style[ 'font-family' ] = values[ 'fusion_font_family_' + param_id ];
			} else {
				style[ 'font-family' ] = '\'' + values[ 'fusion_font_family_' + param_id ] + '\'';
			}

			if ( 'string' === typeof values[ 'fusion_font_variant_' + param_id ] && '' !== values[ 'fusion_font_variant_' + param_id ] && 'undefined' === typeof style[ 'font-weight' ] ) {
				weight = values[ 'fusion_font_variant_' + param_id ].replace( 'italic', '' );
				if ( weight !== values[ 'fusion_font_variant_' + param_id ] ) {
					style[ 'font-style' ] = 'italic';
				}
				if ( '' !== weight ) {
					style[ 'font-weight' ] = weight;
				}
			}
		}

		if ( 'string' === format ) {
			important = important ? ' !important' : '';
			jQuery.each( style, function( key, value ) {
				style_str += key + ':' + value + important + ';';
			} );

			return style_str;
		}

		return style;
	},

	/**
	 * Checks if valid JSON or not.
	 *
	 * @since 3.0
	 * @param {String} value - The value to check.
	 * @return {Bolean}
	 */
	FusionIsValidJSON: function( value ) {
		try {
			JSON.parse( value );
		} catch ( e ) {
			return false;
		}
		return true;
	},

	/**
	 * Returns sticky class string.
	 *
	 * @since 3.0
	 * @param {String} value - The value to check.
	 * @return {String}
	 */
	fusionGetStickyClass: function( value ) {
		return '' !== value && ! value.includes( ',' ) ? ' fusion-display-' + value + '-only' : '';
	},

	/**
	 * Link attributes.
	 *
	 * @since 3.3
	 * @param {Object} attr - Element attributes.
	 * @param {Object} values - Element values.
	 * @return {Object}
	 */
	fusionLinkAttributes: function( attr, values ) {
		var linkAttributes;
		if ( 'undefined' !== typeof values.link_attributes && '' !== values.link_attributes ) {
			linkAttributes = values.link_attributes.split( ' ' );

			_.each( linkAttributes, function( linkAttribute ) {
				var attributeKeyValue = linkAttribute.split( '=' );

				if ( ! _.isUndefined( attributeKeyValue[ 0 ] ) ) {
					if ( ! _.isUndefined( attributeKeyValue[ 1 ] ) ) {
						attributeKeyValue[ 1 ] = attributeKeyValue[ 1 ].trim().replace( /{/g, '[' ).replace( /}/g, ']' ).replace( /'/g, '' ).trim();
						if ( 'rel' === attributeKeyValue[ 0 ] ) {
							attr.rel += ' ' + attributeKeyValue[ 1 ];
						} else if ( 'string' === typeof attr[ attributeKeyValue[ 0 ] ] ) {
							attr[ attributeKeyValue[ 0 ] ] += ' ' + attributeKeyValue[ 1 ];
						} else {
							attr[ attributeKeyValue[ 0 ] ] = attributeKeyValue[ 1 ];
						}
					} else {
						attr[ attributeKeyValue[ 0 ] ] = 'valueless_attribute';
					}
				}
			} );
		}
		return attr;
	},

	fusionSanitize: function( str ) {

		var map = {
				'À': 'A',
				'Á': 'A',
				'Â': 'A',
				'Ã': 'A',
				'Ä': 'A',
				'Å': 'A',
				'Æ': 'AE',
				'Ç': 'C',
				'È': 'E',
				'É': 'E',
				'Ê': 'E',
				'Ë': 'E',
				'Ì': 'I',
				'Í': 'I',
				'Î': 'I',
				'Ï': 'I',
				'Ð': 'D',
				'Ñ': 'N',
				'Ò': 'O',
				'Ó': 'O',
				'Ô': 'O',
				'Õ': 'O',
				'Ö': 'O',
				'Ø': 'O',
				'Ù': 'U',
				'Ú': 'U',
				'Û': 'U',
				'Ü': 'U',
				'Ý': 'Y',
				'ß': 's',
				'à': 'a',
				'á': 'a',
				'â': 'a',
				'ã': 'a',
				'ä': 'a',
				'å': 'a',
				'æ': 'ae',
				'ç': 'c',
				'è': 'e',
				'é': 'e',
				'ê': 'e',
				'ë': 'e',
				'ì': 'i',
				'í': 'i',
				'î': 'i',
				'ï': 'i',
				'ñ': 'n',
				'ò': 'o',
				'ó': 'o',
				'ô': 'o',
				'õ': 'o',
				'ö': 'o',
				'ø': 'o',
				'ù': 'u',
				'ú': 'u',
				'û': 'u',
				'ü': 'u',
				'ý': 'y',
				'ÿ': 'y',
				'Ā': 'A',
				'ā': 'a',
				'Ă': 'A',
				'ă': 'a',
				'Ą': 'A',
				'ą': 'a',
				'Ć': 'C',
				'ć': 'c',
				'Ĉ': 'C',
				'ĉ': 'c',
				'Ċ': 'C',
				'ċ': 'c',
				'Č': 'C',
				'č': 'c',
				'Ď': 'D',
				'ď': 'd',
				'Đ': 'D',
				'đ': 'd',
				'Ē': 'E',
				'ē': 'e',
				'Ĕ': 'E',
				'ĕ': 'e',
				'Ė': 'E',
				'ė': 'e',
				'Ę': 'E',
				'ę': 'e',
				'Ě': 'E',
				'ě': 'e',
				'Ĝ': 'G',
				'ĝ': 'g',
				'Ğ': 'G',
				'ğ': 'g',
				'Ġ': 'G',
				'ġ': 'g',
				'Ģ': 'G',
				'ģ': 'g',
				'Ĥ': 'H',
				'ĥ': 'h',
				'Ħ': 'H',
				'ħ': 'h',
				'Ĩ': 'I',
				'ĩ': 'i',
				'Ī': 'I',
				'ī': 'i',
				'Ĭ': 'I',
				'ĭ': 'i',
				'Į': 'I',
				'į': 'i',
				'İ': 'I',
				'ı': 'i',
				'Ĳ': 'IJ',
				'ĳ': 'ij',
				'Ĵ': 'J',
				'ĵ': 'j',
				'Ķ': 'K',
				'ķ': 'k',
				'Ĺ': 'L',
				'ĺ': 'l',
				'Ļ': 'L',
				'ļ': 'l',
				'Ľ': 'L',
				'ľ': 'l',
				'Ŀ': 'L',
				'ŀ': 'l',
				'Ł': 'l',
				'ł': 'l',
				'Ń': 'N',
				'ń': 'n',
				'Ņ': 'N',
				'ņ': 'n',
				'Ň': 'N',
				'ň': 'n',
				'ŉ': 'n',
				'Ō': 'O',
				'ō': 'o',
				'Ŏ': 'O',
				'ŏ': 'o',
				'Ő': 'O',
				'ő': 'o',
				'Œ': 'OE',
				'œ': 'oe',
				'Ŕ': 'R',
				'ŕ': 'r',
				'Ŗ': 'R',
				'ŗ': 'r',
				'Ř': 'R',
				'ř': 'r',
				'Ś': 'S',
				'ś': 's',
				'Ŝ': 'S',
				'ŝ': 's',
				'Ş': 'S',
				'ş': 's',
				'Š': 'S',
				'š': 's',
				'Ţ': 'T',
				'ţ': 't',
				'Ť': 'T',
				'ť': 't',
				'Ŧ': 'T',
				'ŧ': 't',
				'Ũ': 'U',
				'ũ': 'u',
				'Ū': 'U',
				'ū': 'u',
				'Ŭ': 'U',
				'ŭ': 'u',
				'Ů': 'U',
				'ů': 'u',
				'Ű': 'U',
				'ű': 'u',
				'Ų': 'U',
				'ų': 'u',
				'Ŵ': 'W',
				'ŵ': 'w',
				'Ŷ': 'Y',
				'ŷ': 'y',
				'Ÿ': 'Y',
				'Ź': 'Z',
				'ź': 'z',
				'Ż': 'Z',
				'ż': 'z',
				'Ž': 'Z',
				'ž': 'z',
				'ſ': 's',
				'ƒ': 'f',
				'Ơ': 'O',
				'ơ': 'o',
				'Ư': 'U',
				'ư': 'u',
				'Ǎ': 'A',
				'ǎ': 'a',
				'Ǐ': 'I',
				'ǐ': 'i',
				'Ǒ': 'O',
				'ǒ': 'o',
				'Ǔ': 'U',
				'ǔ': 'u',
				'Ǖ': 'U',
				'ǖ': 'u',
				'Ǘ': 'U',
				'ǘ': 'u',
				'Ǚ': 'U',
				'ǚ': 'u',
				'Ǜ': 'U',
				'ǜ': 'u',
				'Ǻ': 'A',
				'ǻ': 'a',
				'Ǽ': 'AE',
				'ǽ': 'ae',
				'Ǿ': 'O',
				'ǿ': 'o',
				'α': 'a',
				'Α': 'A',
				'β': 'v',
				'Β': 'V',
				'γ': 'g',
				'Γ': 'G',
				'δ': 'd',
				'Δ': 'D',
				'ε': 'e',
				'Ε': 'E',
				'ζ': 'z',
				'Ζ': 'Z',
				'η': 'i',
				'Η': 'I',
				'θ': 'th',
				'Θ': 'TH',
				'ι': 'i',
				'Ι': 'I',
				'κ': 'k',
				'Κ': 'K',
				'λ': 'l',
				'Λ': 'L',
				'μ': 'm',
				'Μ': 'M',
				'ν': 'n',
				'Ν': 'N',
				'ξ': 'ks',
				'Ξ': 'KS',
				'ο': 'o',
				'Ο': 'O',
				'π': 'p',
				'Π': 'P',
				'ρ': 'r',
				'Ρ': 'R',
				'σ': 's',
				'Σ': 'S',
				'ς': 's',
				'τ': 't',
				'Τ': 'T',
				'υ': 'y',
				'Υ': 'Y',
				'φ': 'f',
				'Φ': 'F',
				'χ': 'x',
				'Χ': 'X',
				'ψ': 'ps',
				'Ψ': 'PS',
				'ω': 'o',
				'Ω': 'O',
				' ': '_',
				'\'': '',
				'?': '',
				'/': '',
				'\\': '',
				'.': '',
				',': '',
				'`': '',
				'>': '',
				'<': '',
				'"': '',
				'[': '',
				']': '',
				'|': '',
				'{': '',
				'}': '',
				'(': '',
				')': ''
			},
			nonWord = /\W/g,
			mapping = function ( c ) {
				return ( map[ c ] !== undefined ) ? map[ c ] : c;
			};
		return str.replace( nonWord, mapping ).toLowerCase();
	}
} );

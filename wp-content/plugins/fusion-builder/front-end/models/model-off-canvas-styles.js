/* global FusionApp, FusionPageBuilderApp, FusionEvents */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.offCanvasStyles = Backbone.Model.extend( {

		/**
		 * Off Canvas Live editor preview initialization.
		 *
		 * @since 3.6
		 * @return {void}
		 */
		initialize: function() {
			const 	body 				= jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ),
					ocID				= body.find( '.awb-off-canvas-wrap' ).attr( 'data-id' );

			this.baseSelector = '.awb-off-canvas-wrap#awb-oc-' + ocID;
			this.dynamic_css  = {};
			this.options     = this.filterOptions();
			this.buildStyles();

			// Remove saved styles.
			body.find( '#awb-off-canvas-style-block-' + ocID ).remove();

			this.listenTo( FusionEvents, 'awb-off-canvas-styles', this.buildStyles );
			this.listenTo( FusionEvents, 'awb-off-canvas-attr', this.buildAttr );
			this.listenTo( FusionEvents, 'awb-off-canvas-custom-close-button', this.customCloseButton );
			this.listenTo( FusionEvents, 'awb-off-canvas-enter-animation', this.enterAnimation );
			this.listenTo( FusionEvents, 'awb-off-canvas-exit-animation', this.exitAnimation );

			this.listenTo( FusionEvents, 'fusion-builder-loaded', this.buildStyles );
			this.listenTo( FusionEvents, 'fusion-builder-loaded', this.buildAttr );
			this.listenTo( FusionEvents, 'fusion-builder-loaded', this.customCloseButton );
		},

		/**
		 * Array with animations without directions.
		 *
		 * @since 3.6
		 * @return {void}
		 */
		animationsWithoutDirection: [ 'flash', 'rubberBand', 'shake', 'flipinx', 'flipiny', 'lightspeedin', 'flipOutX', 'flipOutY', 'lightSpeedOut' ],

		/**
		 * Modify options mostly for sliding bar type.
		 *
		 * @since 3.6
		 * @return {Object} Modified options object.
		 */
		filterOptions: function() {
			const options = FusionApp.data.postMeta._fusion;
			const filteredOptions = Object.assign( {}, options );

			if ( 'sliding-bar' === options.type ) {
				filteredOptions.type = 'sliding-bar';
				filteredOptions.enter_animation = filteredOptions.sb_enter_animation;
				filteredOptions.enter_animation_speed = filteredOptions.sb_enter_animation_speed;
				filteredOptions.exit_animation = filteredOptions.sb_exit_animation;
				filteredOptions.exit_animation_speed = filteredOptions.sb_exit_animation_speed;

				if ( 'left' === filteredOptions.position || !filteredOptions.position ) {
					filteredOptions.height = 'full';
					filteredOptions.width = options.width || 400;
					filteredOptions.enter_animation_direction = 'left';
					filteredOptions.exit_animation_direction = 'left';
					filteredOptions.vertical_position = 'flex-start';
					if ( this.isRTL() ) {
						filteredOptions.horizontal_position = 'flex-end';
					} else {
						filteredOptions.horizontal_position = 'flex-start';
					}
				}

				if ( 'right' === filteredOptions.position ) {
					filteredOptions.height = 'full';
					filteredOptions.width = options.width || 400;
					filteredOptions.enter_animation_direction = 'right';
					filteredOptions.exit_animation_direction = 'right';
					filteredOptions.vertical_position = 'flex-start';
					if ( this.isRTL() ) {
						filteredOptions.horizontal_position = 'flex-start';
					} else {
						filteredOptions.horizontal_position = 'flex-end';
					}
				}

				if ( 'top' === filteredOptions.position ) {
					const height = filteredOptions.sb_height || 'auto';
					filteredOptions.width = '100vw';
					filteredOptions.height = 'custom';
					filteredOptions.custom_height = height;
					filteredOptions.enter_animation_direction = 'down';
					filteredOptions.exit_animation_direction = 'up';
					filteredOptions.vertical_position = 'flex-start';
					filteredOptions.horizontal_position = 'flex-start';
				}

				if ( 'bottom' === filteredOptions.position ) {
					const height = filteredOptions.sb_height || 'auto';
					filteredOptions.width = '100vw';
					filteredOptions.height = 'custom';
					filteredOptions.custom_height = height;
					filteredOptions.enter_animation_direction = 'up';
					filteredOptions.exit_animation_direction = 'down';
					filteredOptions.vertical_position = 'flex-end';
					filteredOptions.horizontal_position = 'flex-start';
				}
				return this.parseOptions( filteredOptions );
			}

			return this.parseOptions( options );
		},

		/**
		 * Merge default options with current options.
		 * To ensure the preview works as same as the front-end.
		 * @since 3.6
		 * @param {Object} options - The options object.
		 * @return {Object} New options object with default values.
		 */
		parseOptions( options ) {
			const defaults = {
				// General.
				'type': 'popup',
				'width': '800',
				'width_medium': '',
				'width_small': '',
				'height': 'fit',
				'custom_height': '',
				'custom_height_medium': '',
				'custom_height_small': '',
				'horizontal_position': 'center',
				'horizontal_position_medium': '',
				'horizontal_position_small': '',
				'vertical_position': 'center',
				'vertical_position_medium': '',
				'vertical_position_small': '',
				'content_layout': 'column',
				'align_content': 'flex-start',
				'valign_content': 'flex-start',
				'content_wrap': 'wrap',
				'enter_animation': '',
				'enter_animation_direction': 'static',
				'enter_animation_speed': 0.5,
				'exit_animation': '',
				'exit_animation_direction': 'static',
				'exit_animation_speed': 0.5,

				'off_canvas_state': 'closed',
				'sb_height': '',
				'position': 'left',
				'transition': 'overlap',

				'sb_enter_animation': 'slideShort',
				'sb_enter_animation_speed': 0.5,
				'sb_exit_animation': 'slideShort',
				'sb_exit_animation_speed': 0.5,

				// Design.
				'background_color': '#ffffff',
				'background_image': '',
				'background_position': 'left top',
				'background_repeat': 'repeat',
				'background_size': '',
				'background_custom_size': '',
				'background_blend_mode': 'none',
				'oc_scrollbar': 'default',
				'oc_scrollbar_background': '#f2f3f5',
				'oc_scrollbar_handle_color': '#65bc7b',
				'margin': '',
				'padding': '',
				'box_shadow': 'no',
				'box_shadow_position': '',
				'box_shadow_blur': '0',
				'box_shadow_spread': '0',
				'box_shadow_color': '',
				'border_radius': '',
				'border_width': '',
				'border_color': '',

				// Overlay.
				'overlay': 'yes',
				'overlay_z_index': '',
				'overlay_close_on_click': 'yes',
				'overlay_page_scrollbar': 'yes',
				'overlay_background_color': 'rgba(0,0,0,0.8)',
				'overlay_background_image': '',
				'overlay_background_position': 'left top',
				'overlay_background_repeat': 'repeat',
				'overlay_background_size': '',
				'overlay_background_custom_size': '',
				'overlay_background_blend_mode': 'none',

				// close button.
				'close_button': 'yes',
				'close_on_esc': 'yes',
				'close_button_position': 'right',
				'close_button_margin': {},
				'close_button_color': '',
				'close_button_color_hover': '',
				'close_icon_size': '16',
				'close_button_custom_icon': ''
			};

			return Object.assign( defaults, options );
		},

		/**
		 * Adds CSS property to object.
		 *
		 * @since  3.2
		 * @param  {String} selectors - The CSS selectors.
		 * @param  {String} property - The CSS property.
		 * @param  {String} value - The CSS property value.
		 * @param  {Bool}   important - Should have important tag.
		 * @return {void}
		 */
		addCssProperty: function ( selectors, property, value, important ) {

			if ( 'object' === typeof selectors ) {
				selectors = Object.values( selectors );
			}

			if ( 'object' === typeof selectors ) {
				selectors = selectors.join( ',' );
			}

			if ( 'object' !== typeof this.dynamic_css[ selectors ] ) {
				this.dynamic_css[ selectors ] = {};
			}

			if ( 'undefined' !== typeof important && important ) {
				value += ' !important';
			}
			if ( 'undefined' === typeof this.dynamic_css[ selectors ][ property ] || ( 'undefined' !== typeof important && important ) || ! this.dynamic_css[ selectors ][ property ].includes( 'important' ) ) {
				this.dynamic_css[ selectors ][ property ] = value;
			}
		},

		/**
		 * Parses CSS.
		 *
		 * @since  3.2
		 * @return {String}
		 */
		parseCSS: function () {
			var css = '';
			if ( 'object' !== typeof this.dynamic_css ) {
				return '';
			}

			_.each( this.dynamic_css, function ( properties, selector ) {
				if ( 'object' === typeof properties ) {
					css += selector + '{';
					_.each( properties, function ( value, property ) {
						css += property + ':' + value + ';';
					} );
					css += '}';
				}
			} );

			return css;
		},

		/**
		 * Checks if param has got default value or not.
		 *
		 * @since  3.2
		 * @param  {String} param - The param.
		 * @return {Bool}
		 */
		isDefault: function( param, subset ) {
			if ( 'string' === typeof subset ) {
				return 'undefined' === typeof this.options[ param ] || 'undefined' === typeof this.options[ param ][ subset ] || '' === this.options[ param ][ subset ];
			}
			return 'undefined' === typeof this.options[ param ] || '' === this.options[ param ];
		},

		/**
		 * Checks if website using RTL language.
		 *
		 * @since  3.6
		 * @return {Bool}
		 */
		isRTL: function () {
			return jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).hasClass( 'rtl' );
		},

		/**
		 * Get CSS from spacing fields.
		 * used for margin, padding, position, etc.
		 * @since 3.6
		 * @param {Object} options - The options object.
		 * @param {String} key - options key.
		 * @param {String} prop - CSS property, if empty key will used instead.
		 * @return {String} CSS code.
		 */
		getSpacing: function( options, key, prop = '', empty ) {
			if ( !options[ key ] && 'object' !== typeof options[ key ] ) {
				return '';
			}
			prop = prop || key;
			let prefix = '';

			if ( 'margin' === prop || 'padding' === prop ) {
				prefix = prop + '-';
			}
			let css = '';
			const keys = [ 'top', 'right', 'bottom', 'left' ];
			empty = empty ? '' : 0;

			keys.forEach( ( k ) => {
				const v = options[ key ][ k ] || empty;
				if ( '' !== v ) {
					css += `${prefix}${k}: ${_.fusionGetValueWithUnit( v )};`;
				}
			} );

			return css;
		},

		/**
		 * Get CSS code for box shadow.
		 *
		 * @since 3.6
		 * @param {Object} options - The options object.
		 * @return {String} CSS code.
		 */
		getShadow( options ) {
			if ( 'yes' !== options.box_shadow ) {
				return '';
			}
			let 	h 		= '0',
					v 		= '0';
			const 	blur 	= options.box_shadow_blur || '0',
					spread	= options.box_shadow_spread || '0',
					color	= options.box_shadow_color || '';
			if ( options.box_shadow_position && 'object' === typeof options.box_shadow_position ) {
				h = options.box_shadow_position.horizontal || h;
				v = options.box_shadow_position.vertical || v;
			}

			return `box-shadow:${_.fusionGetValueWithUnit( h )} ${_.fusionGetValueWithUnit( v )} ${_.fusionGetValueWithUnit( blur )} ${_.fusionGetValueWithUnit( spread )} ${color};`;
		},

		/**
		 * Get CSS code for borders including border radius.
		 *
		 * @since 3.6
		 * @param {Object} options - The options object.
		 * @return {String} CSS code.
		 */
		getBorder( options ) {
			let css = '';

			// Border radius.
			if ( options.border_radius && 'object' === typeof options.border_radius ) {
				const br = options.border_radius;
				// ensure preview works when delete value.
				if ( !br.top_left ) {
					br.top_left = '';
				}
				if ( !br.top_right ) {
					br.top_right = '';
				}
				if ( !br.bottom_right ) {
					br.bottom_right = '';
				}
				if ( !br.bottom_left ) {
					br.bottom_left = '';
				}
				// loop through border radius.
				Object.keys( br ).forEach( ( r ) => {
					const v = br[ r ] || 0;
					css += `border-${r.replace( '_', '-' )}-radius:${_.fusionGetValueWithUnit( v )};`;
				} );
			}

			// Border width.
			if ( options.border_width && 'object' === typeof options.border_width ) {
				const bw = options.border_width;
				// ensure preview works when delete value.
				if ( !bw.top ) {
					bw.top = '';
				}
				if ( !bw.right ) {
					bw.right = '';
				}
				if ( !bw.bottom ) {
					bw.bottom = '';
				}
				if ( !bw.left ) {
					bw.left = '';
				}
				Object.keys( bw ).forEach( ( b ) => {
					const v = bw[ b ] || 0;
					css += `border-${b}-width:${_.fusionGetValueWithUnit( v )};`;
				} );
			}
			// Border color.
			if ( options.border_color ) {
				css += `border-color: ${options.border_color};`;
			}
			return css;
		},

		/**
		 * Build CSS style block and add it to the head.
		 *
		 * @since 3.6
		 * @return {void} CSS code.
		 */
		buildStyles: function() {
			var selectors,
				media,
				css = '';

			this.dynamic_css = {};

			const options = this.filterOptions();
			selectors = [ this.baseSelector ];
			// remove opacity.
			this.addCssProperty( selectors, 'opacity',  1, true );

			// remove wrapper margin for push transition.
			this.addCssProperty( [ '#wrapper' ], 'margin-left', '0', true );
			this.addCssProperty( [ '#wrapper' ], 'margin-right', '0', true );

			// Wrap styles.
			if ( options.horizontal_position ) {
				this.addCssProperty( selectors, 'justify-content',  options.horizontal_position );
			}
			if ( options.vertical_position ) {
				this.addCssProperty( selectors, 'align-items',  options.vertical_position );
			}
			if ( options.overlay_background_color ) {
				this.addCssProperty( selectors, 'background-color',  options.overlay_background_color );
			}

			if ( options.overlay_background_image && 'yes' === options.overlay ) {
				let overlayBgImg = options.overlay_background_image;
				if ( _.isObject( overlayBgImg ) ) {
					overlayBgImg = overlayBgImg.url;
				}
				this.addCssProperty( selectors, 'background-image',  `url(${overlayBgImg})` );
				if ( options.overlay_background_position ) {
					this.addCssProperty( selectors, 'background-position',  options.overlay_background_position );
				}
				if ( options.overlay_background_repeat ) {
					this.addCssProperty( selectors, 'background-repeat',  options.overlay_background_repeat );
				}
				if ( 'none' !== options.overlay_background_blend_mode ) {
					this.addCssProperty( selectors, 'background-blend-mode',  options.overlay_background_blend_mode );
				}
				if ( options.overlay_background_size ) {
					if ( 'custom' === options.overlay_background_size ) {
						const width = options.overlay_background_custom_size && options.overlay_background_custom_size.width ? _.fusionGetValueWithUnit( options.overlay_background_custom_size.width ) : '';
						const height = options.overlay_background_custom_size && options.overlay_background_custom_size.height ? _.fusionGetValueWithUnit( options.overlay_background_custom_size.height ) : '';

						this.addCssProperty( selectors, 'background-size',  `${width} ${height}` );
					} else {
						this.addCssProperty( selectors, 'background-size',  options.overlay_background_size );
					}
				}
			} else {
				this.addCssProperty( selectors, 'background-image',  'none' );
			}

			// off canvas styles.
			const offCanvasSelector = this.baseSelector + ' .awb-off-canvas';
			selectors = [ offCanvasSelector ];
			if ( options.width ) {
				this.addCssProperty( selectors, 'width',  _.fusionGetValueWithUnit( options.width ) );
			}
			if ( 'full' === options.height ) {
				this.addCssProperty( selectors, 'height',  '100vh' );
			} else if ( 'custom' === options.height && options.custom_height ) {
				if ( options.custom_height ) {
					this.addCssProperty( selectors, 'height',  _.fusionGetValueWithUnit( options.custom_height ) );
				}
			} else {
				this.addCssProperty( selectors, 'height',  'auto' );
			}

			const offCanvasInnerSelector = this.baseSelector + ' .awb-off-canvas-inner';
			selectors = [ offCanvasInnerSelector ];

			if ( options.background_color ) {
				this.addCssProperty( selectors, 'background-color',  options.background_color );
			}

			if ( options.background_image ) {
				let bgImg = options.background_image;
				if ( _.isObject( bgImg ) ) {
					bgImg = bgImg.url;
				}
				this.addCssProperty( selectors, 'background-image',  `url(${bgImg})` );
				if ( options.background_position ) {
					this.addCssProperty( selectors, 'background-position',  options.background_position );
				}
				if ( options.background_repeat ) {
					this.addCssProperty( selectors, 'background-repeat',  options.background_repeat );
				}
				if ( 'none' !== options.background_blend_mode ) {
					this.addCssProperty( selectors, 'background-blend-mode',  options.background_blend_mode );
				}
				if ( options.background_size ) {
					if ( 'custom' === options.background_size ) {
						const width = options.background_custom_size && options.background_custom_size.width ? _.fusionGetValueWithUnit( options.background_custom_size.width ) : '';
						const height = options.background_custom_size && options.background_custom_size.height ? _.fusionGetValueWithUnit( options.background_custom_size.height ) : '';

						this.addCssProperty( selectors, 'background-size',  `${width} ${height}` );
					} else {
						this.addCssProperty( selectors, 'background-size',  options.background_size );
					}
				}
			} else {
				this.addCssProperty( selectors, 'background-image',  'none' );
			}

			selectors = [ this.baseSelector + ' .off-canvas-content .fusion-builder-live-editor' ];
			this.addCssProperty( selectors, 'height',  '100%' );
			this.addCssProperty( selectors, 'width',  '100%' );

			// Prepare the container.
			selectors = [ this.baseSelector + ' .off-canvas-content #fusion_builder_container' ];
			this.addCssProperty( selectors, 'height',  '100%' );
			this.addCssProperty( selectors, 'width',  '100%' );
			this.addCssProperty( selectors, 'display',  'flex' );
			this.addCssProperty( selectors, 'flex-wrap',  'wrap' );
			this.addCssProperty( selectors, 'flex-direction',  'column' );

			//Flex Alignment options.
			if ( options.content_layout && 'block' !== options.content_layout ) {
				this.addCssProperty( selectors, 'flex-direction',  options.content_layout );
				this.addCssProperty( selectors, 'justify-content',  options.align_content );
			}
			if ( options.content_layout && 'block' === options.content_layout ) {
				this.addCssProperty( selectors, 'display',  options.content_layout );
			}
			if ( options.valign_content && 'row' === options.content_layout ) {
				this.addCssProperty( selectors, 'align-items',  options.valign_content );
			}
			if ( options.content_wrap && 'row' === options.content_layout ) {
				this.addCssProperty( selectors, 'flex-wrap',  options.content_wrap );
			}
			if ( 'column' === options.content_layout ) {
				this.addCssProperty( selectors, 'flex-wrap',  'nowrap' );
			}

			// Fix close button z-index in LE.
			selectors = [ this.baseSelector + ' .awb-off-canvas:hover .off-canvas-close' ];
			this.addCssProperty( selectors, 'display',  'none' );

			// Close button.
			selectors = [ offCanvasSelector + ' .off-canvas-close' ];
			if ( 'no' === options.close_button ) {
				this.addCssProperty( selectors, 'display', 'none' );
			}
			if ( 'undefined' === typeof options.close_button_margin.top || '' === options.close_button_margin.top ) {
				this.addCssProperty( selectors, 'margin-top', '20px' );
			}
			if ( 'left' === options.close_button_position ) {
				this.addCssProperty( selectors, 'right', 'auto' );
				this.addCssProperty( selectors, 'left', '0' );
				if ( 'undefined' === typeof options.close_button_margin.right || '' === options.close_button_margin.right ) {
					this.addCssProperty( selectors, 'margin-right', '0' );
				}
				if ( 'undefined' === typeof options.close_button_margin.left || '' === options.close_button_margin.left ) {
					this.addCssProperty( selectors, 'margin-left', '20px' );
				}
			} else {
				this.addCssProperty( selectors, 'left', 'auto' );
				this.addCssProperty( selectors, 'right', '0' );
				if ( 'undefined' === typeof options.close_button_margin.right || '' === options.close_button_margin.right ) {
					this.addCssProperty( selectors, 'margin-right', '20px' );
				}
				if ( 'undefined' === typeof options.close_button_margin.left || '' === options.close_button_margin.left ) {
					this.addCssProperty( selectors, 'margin-left', '0' );
				}
			}
			if ( options.close_button_color ) {
				this.addCssProperty( selectors, 'color',  options.close_button_color );
			}
			if ( options.close_icon_size ) {
				this.addCssProperty( selectors, 'font-size',  options.close_icon_size + 'px' );
			}

			selectors = [ offCanvasSelector + ' .off-canvas-close:hover' ];
			if ( options.close_button_color_hover ) {
				this.addCssProperty( selectors, 'color',  options.close_button_color_hover );
			}

			// Hide close button in wire frame mode.
			selectors = [ offCanvasSelector + '.is-empty .off-canvas-close'  ];
			this.addCssProperty( selectors, 'display',  'none' );

			// custom scrollbar.
			if ( 'custom' === options.oc_scrollbar ) {
				selectors = [ offCanvasInnerSelector + ' .off-canvas-content' ];
				const scrollbarBg = options.oc_scrollbar_background || '#f2f3f5';
				const scrollbarHandleColor = options.oc_scrollbar_handle_color || '#65bc7b';
				// Firefox.
				this.addCssProperty( selectors, 'scrollbar-width',  'thin' );
				this.addCssProperty( selectors, 'scrollbar-color',  scrollbarHandleColor + ' ' + scrollbarBg );

				// Chrome, Safari, Edge.
				this.addCssProperty( [ offCanvasInnerSelector + ' .off-canvas-content::-webkit-scrollbar' ], 'width',  '10px' );
				this.addCssProperty( [ offCanvasInnerSelector + ' .off-canvas-content::-webkit-scrollbar-track' ], 'background',  scrollbarBg );
				this.addCssProperty( [ offCanvasInnerSelector + ' .off-canvas-content::-webkit-scrollbar-thumb' ], 'background',  scrollbarHandleColor );
			}
			// hidden scrollbar.
			if ( 'hidden' === options.oc_scrollbar ) {
				selectors = [ offCanvasInnerSelector + ' .off-canvas-content' ];
				// Firefox.
				this.addCssProperty( selectors, 'scrollbar-width',  'none' );

				// Chrome, Safari, Edge.
				this.addCssProperty( [ offCanvasInnerSelector + ' .off-canvas-content::-webkit-scrollbar' ], 'display',  'none' );
			}

			css = this.parseCSS();
			css += this.getSpacing( options, 'margin' ) ? `${offCanvasSelector} { ${this.getSpacing( options, 'margin' )} }` : '';
			css += this.getSpacing( options, 'padding' ) ? `${this.baseSelector} .off-canvas-content { ${this.getSpacing( options, 'padding' )} }` : '';
			css += this.getSpacing( options, 'close_button_margin' ) ? `${offCanvasSelector} .off-canvas-close { ${this.getSpacing( options, 'close_button_margin', 'margin', true )} }` : '';
			css += this.getShadow( options ) ? `${offCanvasInnerSelector} { ${this.getShadow( options )} }` : '';
			css += this.getBorder( options ) ? `${offCanvasInnerSelector} { ${this.getBorder( options )} }` : '';

			if ( 'sliding-bar' !== options.type ) {
				// Horizontal position responsive styles.
				_.each( [ 'medium', 'small' ], function( size ) {
					var key = 'horizontal_position_' + size;
					media = '@media only screen and (max-width:' + FusionApp.settings[ 'visibility_' + size ] + 'px)';

					if ( '' === options[ key ] ) {
						return;
					}

					this.dynamic_css = {};
					this.addCssProperty( [ this.baseSelector ], 'justify-content', options[ key ] );
					css += media + '{' + this.parseCSS() + '}';
				}, this );

				// vertical position responsive styles.
				_.each( [ 'medium', 'small' ], function( size ) {
					var key = 'vertical_position_' + size;
					media = '@media only screen and (max-width:' + FusionApp.settings[ 'visibility_' + size ] + 'px)';

					if ( '' === options[ key ] ) {
						return;
					}

					this.dynamic_css = {};
					this.addCssProperty( [ this.baseSelector ], 'align-items', options[ key ] );
					css += media + '{' + this.parseCSS() + '}';
				}, this );

				// Height responsive styles.
				if ( 'custom' === options.height ) {
					_.each( [ 'medium', 'small' ], function( size ) {
						var key = 'custom_height_' + size;
						media = '@media only screen and (max-width:' + FusionApp.settings[ 'visibility_' + size ] + 'px)';

						if ( '' === options[ key ] ) {
							return;
						}

						this.dynamic_css = {};
						this.addCssProperty( [ this.baseSelector + ' .awb-off-canvas' ], 'height', _.fusionGetValueWithUnit( options[ key ] ) );
						css += media + '{' + this.parseCSS() + '}';
					}, this );
				}
			}

			// Width responsive styles.
			_.each( [ 'medium', 'small' ], function( size ) {
				var key = 'width_' + size;
				media = '@media only screen and (max-width:' + FusionApp.settings[ 'visibility_' + size ] + 'px)';

				if ( '' === options[ key ] ) {
					return;
				}

				this.dynamic_css = {};
				this.addCssProperty( [ this.baseSelector + ' .awb-off-canvas' ], 'width', _.fusionGetValueWithUnit( options[ key ] ) );
				css += media + '{' + this.parseCSS() + '}';
			}, this );

			// Add attribute to the option.
			const value = jQuery( '[data-option-id="content_layout"]' ).find( 'input#content_layout' ).val();
			jQuery( '[data-option-id="content_layout"]' ).attr( 'data-direction', value );

			if ( jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'head' ).find( '#awb-off-canvas-style-block' ).length ) {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'head' ).find( '#awb-off-canvas-style-block' ).html( css );
				return;
			}

			jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'head' ).prepend( '<style id="awb-off-canvas-style-block">' + css + '</style>' );

		},

		/**
		 * build attributes.
		 *
		 * @since 3.6
		 * @return {String} CSS code.
		 */
		buildAttr: function() {
			const body = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' );
			const options = this.filterOptions();

			// Wrap Classes.
			let wrapClasses = 'awb-off-canvas-wrap awb-show';
			if ( '' !== options.css_class ) {
				wrapClasses += ' ' + options.css_class;
			}
			if ( '' !== options.type ) {
				wrapClasses += ' type-' + options.type;
			}

			if ( 'sliding-bar' === options.type ) {
				if ( !options.position ) {
					options.position = 'left';
				}
				wrapClasses += ' position-' + options.position;
			}

			if ( 'no' === options.overlay ) {
				wrapClasses += ' overlay-disabled';
			}
			body.find( this.baseSelector ).removeClass().addClass( wrapClasses );

			// remove is empty class.
			body.find( this.baseSelector + ' .awb-off-canvas-inner' ).removeClass( 'hidden-scrollbar' );
			if ( 1 < FusionPageBuilderApp.collection.length ) {
				body.find( this.baseSelector + ' .awb-off-canvas-inner' ).removeClass( 'is-empty' );
			}

		},

		/**
		 * Custom close button.
		 *
		 * @since 3.6
		 * @return {void}.
		 */
		customCloseButton: function() {
			const options = this.filterOptions();

			const 	body 				= jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' );
			let		closeButton 		= body.find( '.off-canvas-close' );
			if ( ! closeButton.length ) {
				body.find( '.awb-off-canvas' ).prepend( '<button class="off-canvas-close"></button>' );
				closeButton = body.find( '.off-canvas-close' );
			}
			let cls = 'off-canvas-close';
			if ( options.close_button_custom_icon ) {
				cls +=  ' ' + _.fusionFontAwesome( options.close_button_custom_icon );
			} else {
				cls += ' awb-icon-close';
			}
			closeButton.removeClass().addClass( cls );
		},

		/**
		 * Capitalize string.
		 *
		 * @since 3.6
		 * @return {String} The capitalized string.
		 */
		capitalize: function ( string ) {
			return string.charAt( 0 ).toUpperCase() + string.slice( 1 );
		},

		/**
		 * Enter animation preview.
		 *
		 * @since 3.6
		 * @param {String} string
		 * @return {void}
		 */
		enterAnimation: function() {
			const 	body 				= jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ),
					offCanvas			= body.find( '.awb-off-canvas' ),
					options 			= this.filterOptions();

			let     animation = options.enter_animation;
			const   animationDirection = options.enter_animation_direction && 'static' !== options.enter_animation_direction ? this.capitalize( options.enter_animation_direction ) : '',
			animationSpeed = options.enter_animation_speed || 1;

			if ( animation ) {
				if ( ! this.animationsWithoutDirection.includes( animation ) ) {
					animation = animation + 'In' + animationDirection;
				}
				offCanvas.addClass( 'fusion-animated ' + animation );
				offCanvas.attr( 'data-animation-type', animation );
				offCanvas.css( {
					'visibility': 'visible',
					'animation-duration': animationSpeed + 's'
				} );
			}
			offCanvas.addClass( 'fusion-animated ' + animation );

			offCanvas.on( 'animationend', function() {
				const   el = jQuery( this );

				if ( el.attr( 'data-animation-type' ) ) {
					el.removeClass( 'fusion-animated' ).removeClass( el.attr( 'data-animation-type' ) ).removeAttr( 'data-animation-type' );
				}
			} );
		},

		/**
		 * Exit animation preview.
		 *
		 * @since 3.6
		 * @return {void}
		 */
		exitAnimation: function() {
			const 	body 				= jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ),
					offCanvas			= body.find( '.awb-off-canvas' ),
					options 			= this.filterOptions();

			let     animation = options.exit_animation;
			const   animationDirection = options.exit_animation_direction && 'static' !== options.exit_animation_direction ? this.capitalize( options.exit_animation_direction ) : '',
			animationSpeed = options.enter_animation_speed || 1;

			if ( animation ) {
				if ( ! this.animationsWithoutDirection.includes( animation ) ) {
					animation = animation + 'Out' + animationDirection;
				}
				offCanvas.addClass( 'fusion-animated ' + animation );
				offCanvas.attr( 'data-animation-type', animation );
				offCanvas.css( {
					'visibility': 'visible',
					'animation-duration': animationSpeed + 's'
				} );
			}
			offCanvas.addClass( 'fusion-animated ' + animation );

			offCanvas.on( 'animationend', function() {
				const   el = jQuery( this );
				setTimeout( () => {
					if ( el.attr( 'data-animation-type' ) ) {
						el.removeClass( 'fusion-animated' ).removeClass( el.attr( 'data-animation-type' ) ).removeAttr( 'data-animation-type' );
					}
				}, 500 );

			} );
		}
	} );
}( jQuery ) );

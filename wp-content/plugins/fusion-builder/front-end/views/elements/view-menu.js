/* global FusionApp, fusionAllElements, fusionSanitize */
var FusionPageBuilder = FusionPageBuilder || {};

( function () {

	jQuery( document ).ready( function () {

		// Alert Element View.
		FusionPageBuilder.fusion_menu = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.0
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function ( atts ) {
				var attributes = {};

				this.values = atts.values;

				attributes.attr = this.buildAttr();

				attributes.menuMarkup         = 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.menu_markup ? atts.query_data.menu_markup : 'No menu markup';
				attributes.buttonMarkup       = 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.button_markup ? atts.query_data.button_markup : 'No button markup';
				attributes.flyoutButtonMarkup = 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.flyout_button_markup ? atts.query_data.flyout_button_markup : 'No flyout button markup';
				attributes.inlineStyles       = this.getStyles();

				return attributes;
			},

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

			isDefault: function( param ) {
				return this.values[ param ] === fusionAllElements.fusion_menu.defaults[ param ];
			},

			getStyles: function () {
				var selectors, gap_value, gap_unit, half_gap, value, unit, half,
				menuStyles = {},
				self       = this;;

				this.baseSelector = '.fusion-menu-element-wrapper[data-cid="' +  this.model.get('cid') + '"]';
				this.dynamic_css  = {};

				if (!this.isDefault('font_size')) {
				  selectors = [ this.baseSelector, this.baseSelector + ' .fusion-menu-element-list .menu-item > a' ];
				  this.addCssProperty(selectors, 'font-size',  this.values['font_size']);
				}

				if (!this.isDefault('margin_top')) {
				  this.addCssProperty( this.baseSelector, 'margin-top',  this.values['margin_top']);
				}

				if (!this.isDefault('margin_bottom')) {
				  this.addCssProperty( this.baseSelector, 'margin-bottom',  this.values['margin_bottom']);
				}

				if (!this.isDefault('direction')) {
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list', 'flex-direction',  this.values['direction']);
				}

				if (!this.isDefault('justify_content')) {
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list', 'justify-content',  this.values['justify_content']);
				}

				if (!this.isDefault('align_items')) {
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list', 'align-items',  this.values['align_items']);
				}

				selectors  = [ this.baseSelector + ' .fusion-menu-element-list', this.baseSelector + ' > .avada-menu-mobile-menu-trigger', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu .fusion-megamenu-title .awb-justify-title' ];
				menuStyles = _.fusionGetFontStyle( 'typography', this.values, 'object' );
				jQuery.each( menuStyles, function( rule, value ) {
					self.addCssProperty( selectors, rule, value );
				} );

				this.addCssProperty([ this.baseSelector + ' [class*="awb-icon-"]', this.baseSelector + ' [class^="awb-icon-"]' ], 'font-family',  this.values['fusion_font_family_typography'], true);

				if (!this.isDefault('min_height')) {
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list', 'min-height',  this.values['min_height']);
				}

				if (!this.isDefault('sticky_min_height')) {
				  this.addCssProperty('.fusion-body .fusion-sticky-container.fusion-sticky-transition ' +  this.baseSelector + ' .fusion-menu-element-list', 'min-height',  this.values['sticky_min_height']);
				}

				if (!this.isDefault('text_transform') ) {
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list > li,' + this.baseSelector + ' .fusion-menu-element-list .fusion-megamenu-title', 'text-transform',  this.values['text_transform']);
				}

				if (!this.isDefault('line_height') ) {
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list > li,' + this.baseSelector + ' .fusion-menu-element-list .fusion-megamenu-title', 'line-height',  this.values['line_height']);
				}

				if (!this.isDefault('letter_spacing')) {
					this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list > li > a', 'letter-spacing',  _.fusionGetValueWithUnit( this.values['letter_spacing'] ));
				}

				if (!this.isDefault('mobile_trigger_background_color')) {
				  this.addCssProperty( this.baseSelector + ' > .avada-menu-mobile-menu-trigger', 'background-color',  this.values['mobile_trigger_background_color']);
				}

				if (!this.isDefault('mobile_trigger_color')) {
				  this.addCssProperty( this.baseSelector + ' > .avada-menu-mobile-menu-trigger', 'color',  this.values['mobile_trigger_color']);
				}

				if (!this.isDefault('trigger_padding_top')) {
				  this.addCssProperty( this.baseSelector + ' > .avada-menu-mobile-menu-trigger', 'padding-top',  this.values['trigger_padding_top']);
				}

				if (!this.isDefault('trigger_padding_right')) {
				  this.addCssProperty( this.baseSelector + ' > .avada-menu-mobile-menu-trigger', 'padding-right',  this.values['trigger_padding_right']);
				}

				if (!this.isDefault('trigger_padding_bottom')) {
				  this.addCssProperty( this.baseSelector + ' > .avada-menu-mobile-menu-trigger', 'padding-bottom',  this.values['trigger_padding_bottom']);
				}

				if (!this.isDefault('trigger_padding_left')) {
				  this.addCssProperty( this.baseSelector + ' > .avada-menu-mobile-menu-trigger', 'padding-left',  this.values['trigger_padding_left']);
				}

				if (!this.isDefault('transition_time')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list', this.baseSelector + ' .fusion-menu-element-list .menu-item a', this.baseSelector + ' .fusion-menu-element-list > li', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button)', this.baseSelector + ' .fusion-overlay-search', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active', this.baseSelector + '.expand-method-click.direction-row > ul > li > .fusion-open-nav-submenu', this.baseSelector + ':not(.submenu-mode-flyout) .fusion-menu-element-list li:not(.fusion-mega-menu) .sub-menu', this.baseSelector + ':not(.submenu-mode-flyout) .fusion-menu-element-list .fusion-megamenu-wrapper', this.baseSelector + ' .avada-menu-mobile-menu-trigger .collapsed-nav-icon-open', this.baseSelector + ' .avada-menu-mobile-menu-trigger .collapsed-nav-icon-close', this.baseSelector + ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled' ];
				  if ('never' !==  this.values['breakpoint']) {
				    selectors.push( this.baseSelector + '.collapse-enabled.mobile-mode-collapse-to-button > ul');
				    selectors.push( this.baseSelector + '.collapse-enabled .menu-item a > .fusion-button');
				  }

				  this.addCssProperty(selectors, 'transition-duration', Number( this.values['transition_time']) + 'ms');
				}

				if (!this.isDefault('gap')) {
				  if ('column' !==  this.values['direction']) {
				    this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list > li', 'margin-left', 'calc(' +  this.values['gap'] + ' / 2)');
				    this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list > li', 'margin-right', 'calc(' +  this.values['gap'] + ' / 2)');
				  }
				  else {
				    this.addCssProperty( this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(:last-child)', 'margin-bottom',  this.values['gap'], true);
				  }

				}

				if (!this.isDefault('bg')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button)', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default', this.baseSelector + ' .custom-menu-search-overlay ~ .fusion-overlay-search', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list .custom-menu-search-overlay .fusion-overlay-search', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list .custom-menu-search-dropdown' ];
				  this.addCssProperty(selectors, 'background-color',  this.values['bg']);
				}

				if (!this.isDefault('border_radius_top_left') || !this.isDefault('border_radius_top_right') || !this.isDefault('border_radius_bottom_right') || !this.isDefault('border_radius_bottom_left')) {
				  value =  this.values['border_radius_top_left'] + ' ' +  this.values['border_radius_top_right'] + ' ' +  this.values['border_radius_bottom_right'] + ' ' +  this.values['border_radius_bottom_left'];
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button)', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active' ];
				  this.addCssProperty(selectors, 'border-radius', value);
				}

				if (!this.isDefault('items_padding_top') || !this.isDefault('border_top')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) .fusion-widget-cart > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .fusion-open-nav-submenu' ];
				  this.addCssProperty(selectors, 'padding-top', 'calc(' +  this.values['items_padding_top'] + ' + ' +  this.values['border_top'] + ')');
				}

				if (!this.isDefault('items_padding_right') || !this.isDefault('border_right')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) .fusion-widget-cart > a' ];
				  this.addCssProperty(selectors, 'padding-right', 'calc(' +  this.values['items_padding_right'] + ' + ' +  this.values['border_right'] + ')');
				}

				if (!this.isDefault('items_padding_bottom') || !this.isDefault('border_bottom')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > a' ];
				  if (true) {
				    selectors.push( this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) .fusion-widget-cart > a');
				  }

				  this.addCssProperty(selectors, 'padding-bottom', 'calc(' +  this.values['items_padding_bottom'] + ' + ' +  this.values['border_bottom'] + ')');
				}

				if (!this.isDefault('items_padding_left') || !this.isDefault('border_left')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > a' ];
				  if (true) {
				    selectors.push( this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) .fusion-widget-cart > a');
				  }

				  this.addCssProperty(selectors, 'padding-left', 'calc(' +  this.values['items_padding_left'] + ' + ' +  this.values['border_left'] + ')');
				}

				if (!this.isDefault('items_padding_top') || !this.isDefault('active_border_top')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):hover > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).hover > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):focus > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):active > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):focus-within > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-item > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-ancestor > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-parent > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).expanded > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):hover > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).hover > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):focus > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):active > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):focus-within > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-item > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-ancestor > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-parent > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).expanded > .fusion-open-nav-submenu' ];
				  if ('column' ===  this.values['direction']) {
				    selectors.push( this.baseSelector + '.direction-column .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .fusion-open-nav-submenu');
				  }

				  this.addCssProperty(selectors, 'padding-top', 'calc(' +  this.values['items_padding_top'] + ' + ' +  this.values['active_border_top'] + ')');
				}

				selectors = [ this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children):hover > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children).hover > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children):focus > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children):active > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children):focus-within > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children).current-menu-item > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children).current-menu-ancestor > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children).current-menu-parent > a', this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children).expanded > a' ];
				// Combined padding right and active border right.
				if (!this.isDefault('items_padding_right') || !this.isDefault('active_border_right')) {
				  this.addCssProperty(selectors, 'padding-right', 'calc(' +  this.values['items_padding_right'] + ' + ' +  this.values['active_border_right'] + ')');
				}

				if (!this.isDefault('items_padding_bottom') || !this.isDefault('active_border_bottom')) {
				  this.addCssProperty(selectors, 'padding-bottom', 'calc(' +  this.values['items_padding_bottom'] + ' + ' +  this.values['active_border_bottom'] + ')');
				  if ('column' ===  this.values['direction']) {
				    this.addCssProperty( this.baseSelector + '.direction-column .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .fusion-open-nav-submenu', 'padding-bottom', 'calc(' +  this.values['items_padding_bottom'] + ' + ' +  this.values['active_border_bottom'] + ')');
				  }
				  else if ('click' ===  this.values['expand_method']) {
				    this.addCssProperty( this.baseSelector + '.expand-method-click.direction-row > ul > li > .fusion-open-nav-submenu', 'padding-bottom', 'calc(' +  this.values['items_padding_bottom'] + ' + ' +  this.values['active_border_bottom'] + ')');
				  }

				}

				if (!this.isDefault('items_padding_left') || !this.isDefault('active_border_left')) {
				  this.addCssProperty(selectors, 'padding-left', 'calc(' +  this.values['items_padding_left'] + ' + ' +  this.values['active_border_left'] + ')');
				}

				if (!this.isDefault('items_padding_top')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-form-inline', this.baseSelector + ' .custom-menu-search-overlay ~ .fusion-overlay-search', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list .custom-menu-search-overlay .fusion-overlay-search', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list .fusion-menu-form-inline' ];
				  this.addCssProperty(selectors, 'padding-top',  this.values['items_padding_top']);
				}

				if ('row' ===  this.values['direction'] && 'click' ===  this.values['expand_method'] &&  (!this.isDefault('items_padding_top') || !this.isDefault('submenu_items_padding_top'))) {
				  this.addCssProperty( this.baseSelector + '.expand-method-click.direction-row > ul > li > .fusion-open-nav-submenu', 'padding-bottom', 'calc(' +  this.values['items_padding_bottom'] + ' + ' +  this.values['active_border_bottom'] + ')');
				  if ('yes' ===  this.values['dropdown_carets']) {
				    this.addCssProperty( this.baseSelector + '.dropdown-carets-yes:not(.collapse-enabled).direction-row.expand-method-click li.menu-item-has-children:not(.fusion-menu-item-button) ul .fusion-open-nav-submenu', 'padding-top',  this.values['submenu_items_padding_top']);
				    this.addCssProperty( this.baseSelector + '.dropdown-carets-yes:not(.collapse-enabled).direction-row.expand-method-click li.menu-item-has-children:not(.fusion-menu-item-button) ul .fusion-open-nav-submenu', 'padding-bottom',  this.values['submenu_items_padding_bottom']);
				  }

				}

				if (!this.isDefault('items_padding_bottom')) {
				  selectors = [ this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .fusion-open-nav-submenu', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-form-inline', this.baseSelector + ':not(.collapse-enabled) .custom-menu-search-overlay ~ .fusion-overlay-search', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list .custom-menu-search-overlay .fusion-overlay-search', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list .fusion-menu-form-inline' ];
				  this.addCssProperty(selectors, 'padding-bottom',  this.values['items_padding_bottom']);
				}

				if (!this.isDefault('items_padding_right')) {
				  if (jQuery( 'body' ).hasClass( 'rtl' ) && 'click' ===  this.values['expand_method']) {
				    this.addCssProperty(['.ltr' +  this.baseSelector + '.expand-method-click li.menu-item-has-children:not(.fusion-menu-item-button) > .fusion-open-nav-submenu' ], 'padding-right',  this.values['items_padding_right']);
				  }

				  selectors = [ this.baseSelector + ':not(.collapse-enabled) .fusion-menu-form-inline', this.baseSelector + ':not(.collapse-enabled) .custom-menu-search-overlay ~ .fusion-overlay-search', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list .custom-menu-search-overlay .fusion-overlay-search', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list .fusion-menu-form-inline' ];
				  if (jQuery( 'body' ).hasClass( 'rtl' ) && 'click' ===  this.values['expand_method'] && 'column' ===  this.values['direction']) {
				    selectors.push('.ltr' +  this.baseSelector + '.direction-column.expand-method-click.expand-left .menu-item-has-children > a');
				  }

				  this.addCssProperty(selectors, 'padding-right',  this.values['items_padding_right']);
				  this.addCssProperty( this.baseSelector + ' .custom-menu-search-dropdown .fusion-main-menu-icon', 'padding-right',  this.values['items_padding_right'], true);
				}

				if (!this.isDefault('items_padding_left')) {
				  if (jQuery( 'body' ).hasClass( 'rtl' ) && 'click' ===  this.values['expand_method']) {
				    selectors = ['.rtl' +  this.baseSelector + '.expand-method-click li.menu-item-has-children:not(.fusion-menu-item-button) > .fusion-open-nav-submenu' ];
				    this.addCssProperty(selectors, 'padding-left',  this.values['items_padding_left']);
				  }

				  selectors = [ this.baseSelector + ':not(.collapse-enabled) .fusion-menu-form-inline', this.baseSelector + ':not(.collapse-enabled) .custom-menu-search-overlay ~ .fusion-overlay-search', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list .custom-menu-search-overlay .fusion-overlay-search', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list .fusion-menu-form-inline' ];
				  if (jQuery( 'body' ).hasClass( 'rtl' ) && 'click' ===  this.values['expand_method'] && 'column' ===  this.values['direction']) {
				    selectors.push('.ltr' +  this.baseSelector + '.direction-column.expand-method-click.expand-left .menu-item-has-children > a');
				  }

				  this.addCssProperty(selectors, 'padding-left',  this.values['items_padding_left']);
				  // Important ones.
				  this.addCssProperty( this.baseSelector + ' .custom-menu-search-dropdown .fusion-main-menu-icon', 'padding-left',  this.values['items_padding_left'], true);
				}

				if (!this.isDefault('color')) {
				  // Ones with important.
				  selectors = [ this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button) > a', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .fusion-open-nav-submenu' ];
				  this.addCssProperty(selectors, 'color',  this.values['color'], true);
				  // Ones without important.
				  if ('click' ===  this.values['expand_method']) {
				    selectors.push( this.baseSelector + '.expand-method-click li ul .fusion-open-nav-submenu');
				  }

				  this.addCssProperty(selectors, 'color',  this.values['color']);
				  // Background, but why?
				  selectors = [ this.baseSelector + ' .fusion-overlay-search .fusion-close-search:before', this.baseSelector + ' .fusion-overlay-search .fusion-close-search:after' ];
				  this.addCssProperty(selectors, 'background',  this.values['color']);
				}

				if (!this.isDefault('active_bg')) {
				  selectors = [ this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active' ];
				  this.addCssProperty( this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active', 'background',  this.values['active_bg']);
				  // Border top.
				  if ('row' ===  this.values['direction']) {
				    // Click method.
				    selectors = [ this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children.expanded:after', this.baseSelector + ':not(.collapse-enabled).expand-method-' +  this.values['expand_method'] + '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:hover:after', this.baseSelector + ':not(.collapse-enabled).expand-method-' +  this.values['expand_method'] + '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children.hover:after', this.baseSelector + ':not(.collapse-enabled).expand-method-' +  this.values['expand_method'] + '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:focus:after', this.baseSelector + ':not(.collapse-enabled).expand-method-' +  this.values['expand_method'] + '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:active:after', this.baseSelector + ':not(.collapse-enabled).expand-method-' +  this.values['expand_method'] + '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:focus-within:after' ];
				    if (false !==  this.values['arrows'].includes('active')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-' +  this.values['expand_method'] + '.direction-row > ul > li:hover:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-' +  this.values['expand_method'] + '.direction-row > ul > li:focus:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-' +  this.values['expand_method'] + '.direction-row > ul > li:active:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-' +  this.values['expand_method'] + '.direction-row > ul > li:focus-within:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-' +  this.values['expand_method'] + '.direction-row > ul > li.current-menu-item:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-' +  this.values['expand_method'] + '.direction-row > ul > li.current-menu-ancestor:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-' +  this.values['expand_method'] + '.direction-row > ul > li.current-menu-parent:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-' +  this.values['expand_method'] + '.direction-row > ul > li.expanded:after');
				      if ('click' ===  this.values['expand_method']) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li.hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li:focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li:active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li:focus-within:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li.current-menu-item:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li.current-menu-ancestor:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li.current-menu-parent:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li.expanded:after');
				      }

				    }

				    this.addCssProperty(selectors, 'border-top-color',  this.values['active_bg']);
				  }

				  if ('column' ===  this.values['direction']) {
				    if ('click' ===  this.values['expand_method']) {
				      selectors = [ this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu):after' ];
				      if (false !==  this.values['arrows'].includes('active')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li:hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li.hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li:focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li:active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li:focus-within:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li.current-menu-item:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li.current-menu-ancestor:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li.current-menu-parent:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li.expanded:after');
				      }

				    }

				    if ('hover' ===  this.values['expand_method']) {
				      selectors = [ ];
				      if (false !==  this.values['arrows'].includes('main')) {
				        selectors = [ this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):hover:after', this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).hover:after', this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus:after', this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):active:after', this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus-within:after' ];
				      }

				      if (false !==  this.values['arrows'].includes('active')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li:hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li.hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li:focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li:active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li:focus-within:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li.current-menu-item:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li.current-menu-ancestor:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li.current-menu-parent:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li.expanded:after');
				      }

				    }

				    this.addCssProperty(selectors, 'border-left-color',  this.values['active_bg']);
				  }

				  if ('column' ===  this.values['direction']) {
				    if ('click' ===  this.values['expand_method']) {
				      selectors = [ this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu):after' ];
				      if (false !==  this.values['arrows'].includes('active')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li:hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li.hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li:focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li:active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li:focus-within:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li.current-menu-item:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li.current-menu-ancestor:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li.current-menu-parent:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li.expanded:after');
				      }

				    }

				    if ('hover' ===  this.values['expand_method']) {
				      selectors = [ ];
				      if (false !==  this.values['arrows'].includes('main')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus-within:after');
				      }

				      if (false !==  this.values['arrows'].includes('active')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li:hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li.hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li:focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li:active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li:focus-within:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li.current-menu-item:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li.current-menu-ancestor:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li.current-menu-parent:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li.expanded:after');
				      }

				    }

				    this.addCssProperty(selectors, 'border-right-color',  this.values['active_bg']);
				  }

				}

				if (!this.isDefault('active_color')) {
				  // Important ones.
				  selectors = [ this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.custom-menu-search-overlay):hover > a', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).hover > a', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.custom-menu-search-overlay):focus > a', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.custom-menu-search-overlay):active > a', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.custom-menu-search-overlay):focus-within > a', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-item > a', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-ancestor > a', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-parent > a', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).expanded > a', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):hover > .fusion-open-nav-submenu', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).hover > .fusion-open-nav-submenu', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):focus > .fusion-open-nav-submenu', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):active > .fusion-open-nav-submenu', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):focus-within > .fusion-open-nav-submenu', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-item > .fusion-open-nav-submenu', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-ancestor > .fusion-open-nav-submenu', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-parent > .fusion-open-nav-submenu', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).expanded > .fusion-open-nav-submenu' ];
				  this.addCssProperty(selectors, 'color',  this.values['active_color'], true);
				}

				if (!this.isDefault('border_top')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default' ];
				  this.addCssProperty(selectors, 'border-top-width',  this.values['border_top']);
				}

				if (!this.isDefault('border_right')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default' ];
				  this.addCssProperty(selectors, 'border-right-width',  this.values['border_right']);
				}

				if (!this.isDefault('border_bottom')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default' ];
				  this.addCssProperty(selectors, 'border-bottom-width',  this.values['border_bottom']);
				}

				if (!this.isDefault('border_left')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default' ];
				  this.addCssProperty(selectors, 'border-left-width',  this.values['border_left']);
				}

				if (!this.isDefault('border_color')) {
				  // Important ones.
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default' ];
				  this.addCssProperty(selectors, 'border-color',  this.values['border_color']);
				}

				if (!this.isDefault('active_border_top')) {
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active', 'border-top-width',  this.values['active_border_top']);
				}

				if (!this.isDefault('active_border_right')) {
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active', 'border-right-width',  this.values['active_border_right']);
				}

				if (!this.isDefault('active_border_bottom')) {
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active', 'border-bottom-width',  this.values['active_border_bottom']);
				}

				if (!this.isDefault('active_border_left')) {
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active', 'border-left-width',  this.values['active_border_left']);
				}

				if (!this.isDefault('active_border_color')) {
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active', 'border-color',  this.values['active_border_color']);
				  if ('row' ===  this.values['direction']) {
				    selectors = [ ];
				    if ('click' ===  this.values['expand_method']) {
				      if (false !==  this.values['arrows'].includes('main')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-row.has-active-border-bottom-yes > ul > li.menu-item-has-children.expanded:after');
				      }

				      if (false !==  this.values['arrows'].includes('active')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li:hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li.hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li:focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li:active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li:focus-within:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li.current-menu-item:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li.current-menu-ancestor:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li.current-menu-parent:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li.expanded:after');
				      }

				    }

				    if ('hover' ===  this.values['expand_method']) {
				      if (false !==  this.values['arrows'].includes('main')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-row.has-active-border-bottom-color-yes > ul > li.menu-item-has-children:hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-row.has-active-border-bottom-color-yes > ul > li.menu-item-has-children.hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-row.has-active-border-bottom-color-yes > ul > li.menu-item-has-children:focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-row.has-active-border-bottom-color-yes > ul > li.menu-item-has-children:active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-row.has-active-border-bottom-color-yes > ul > li.menu-item-has-children:focus-within:after');
				      }

				      if (false !==  this.values['arrows'].includes('active')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li:hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li.hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li:focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li:active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li:focus-within:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li.current-menu-item:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li.current-menu-ancestor:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li.current-menu-parent:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li.expanded:after');
				      }

				    }

				    this.addCssProperty(selectors, 'border-top-color',  this.values['active_border_color']);
				  }

				  if ('column' ===  this.values['direction']) {
				    selectors = [ ];
				    if ('click' ===  this.values['expand_method']) {
				      if (false !==  this.values['arrows'].includes('main')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-column.expand-right.has-active-border-right-yes > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu):after');
				      }

				      if (false !==  this.values['arrows'].includes('active')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li:hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li.hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li:focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li:active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li:focus-within:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li.current-menu-item:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li.current-menu-ancestor:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li.current-menu-parent:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li.expanded:after');
				      }

				    }

				    if ('hover' ===  this.values['expand_method']) {
				      if (false !==  this.values['arrows'].includes('main')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right.has-active-border-right-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right.has-active-border-right-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right.has-active-border-right-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right.has-active-border-right-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right.has-active-border-right-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus-within:after');
				      }

				      if (false !==  this.values['arrows'].includes('active')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li:hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li.hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li:focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li:active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li:focus-within:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li.current-menu-item:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li.current-menu-ancestor:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li.current-menu-parent:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li.expanded:after');
				      }

				    }

				    this.addCssProperty(selectors, 'border-left-color',  this.values['active_border_color']);
				  }

				  if ('column' ===  this.values['direction']) {
				    selectors = [ ];
				    if ('click' ===  this.values['expand_method']) {
				      if (false !==  this.values['arrows'].includes('main')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-column.expand-left.has-active-border-left-yes > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu):after');
				      }

				      if (false !==  this.values['arrows'].includes('active')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li:hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li.hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li:focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li:active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li:focus-within:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li.current-menu-item:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li.current-menu-ancestor:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li.current-menu-parent:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li.expanded:after');
				      }

				    }

				    if ('hover' ===  this.values['expand_method']) {
				      if (false !==  this.values['arrows'].includes('main')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left.has-active-border-left-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left.has-active-border-left-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left.has-active-border-left-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left.has-active-border-left-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left.has-active-border-left-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus-within:after');
				      }

				      if (false !==  this.values['arrows'].includes('active')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li:hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li.hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li:focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li:active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li:focus-within:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li.current-menu-item:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li.current-menu-ancestor:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li.current-menu-parent:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li.expanded:after');
				      }

				    }

				    this.addCssProperty(selectors, 'border-right-color',  this.values['active_border_color']);
				  }

				}

				if (!this.isDefault('submenu_space')) {
				  if ('flyout' !==  this.values['submenu_mode']) {
				    selectors = [ this.baseSelector + ':not(.collapse-enabled):not(.submenu-mode-flyout) .fusion-menu-element-list .fusion-megamenu-wrapper', this.baseSelector + '.direction-row:not(.collapse-enabled):not(.submenu-mode-flyout) .fusion-menu-element-list > li > ul.sub-menu:not(.fusion-megamenu)' ];
				    this.addCssProperty(selectors, 'margin-top',  this.values['submenu_space'], true);
				  }

				  if ('row' ===  this.values['direction']) {
				    selectors = [ ];
				    if (false !==  this.values['arrows'].includes('active')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li:after');
				    }

				    if ('hover' ===  this.values['expand_method']) {
				      selectors.push( this.baseSelector + '.expand-method-hover.direction-row .fusion-menu-element-list > li:hover:before');
				      selectors.push( this.baseSelector + '.expand-method-hover.direction-row .fusion-menu-element-list > li.hover:before');
				      selectors.push( this.baseSelector + '.expand-method-hover.direction-row .fusion-menu-element-list > li:focus:before');
				      selectors.push( this.baseSelector + '.expand-method-hover.direction-row .fusion-menu-element-list > li:active:before');
				      selectors.push( this.baseSelector + '.expand-method-hover.direction-row .fusion-menu-element-list > li:focus-within:before');
				      if (false !==  this.values['arrows'].includes('main')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:after');
				      }

				      if (false !==  this.values['arrows'].includes('submenu')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:after');
				      }

				      if (false !==  this.values['arrows'].includes('active')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row > ul > li:after');
				      }

				    }

				    this.addCssProperty(selectors, 'bottom', 'calc(0px - ' +  this.values['submenu_space'] + ')');
				    if ('click' ===  this.values['expand_method']) {
				      selectors = [ ];
				      if (false !==  this.values['arrows'].includes('submenu')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children::after');
				      }

				      if (false !==  this.values['arrows'].includes('main')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children::after');
				      }

				      this.addCssProperty(selectors, 'bottom', 'calc(0px - ' +  this.values['submenu_space'] + ')');
				    }

				  }

				  if ('column' ===  this.values['direction']) {
				    if ('hover' ===  this.values['expand_method']) {
				      selectors = [ this.baseSelector + '.expand-method-hover.direction-column.expand-right li:hover:before', this.baseSelector + '.expand-method-hover.direction-column.expand-right li.hover:before', this.baseSelector + '.expand-method-hover.direction-column.expand-right li:focus:before', this.baseSelector + '.expand-method-hover.direction-column.expand-right li:active:before', this.baseSelector + '.expand-method-hover.direction-column.expand-right li:focus-within:before' ];
				      this.addCssProperty(selectors, 'width',  this.values['submenu_space']);
				    }

				    this.addCssProperty( this.baseSelector + '.direction-column.expand-right .fusion-menu-element-list ul', 'margin-left',  this.values['submenu_space'], true);
				    this.addCssProperty( this.baseSelector + '.direction-column.expand-left .fusion-menu-element-list ul', 'margin-right',  this.values['submenu_space'], true);
				    selectors = [ ];
				    if ('click' ===  this.values['expand_method']) {
				      if (false !==  this.values['arrows'].includes('main')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-column.expand-left > ul > li:after');
				      }

				      if (false !==  this.values['arrows'].includes('submenu')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-column.expand-left > ul > li:after');
				      }

				      if (false !==  this.values['arrows'].includes('active')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.active-item-arrows-on.direction-column.expand-left > ul > li:after');
				      }

				    }

				    if ('hover' ===  this.values['expand_method']) {
				      if (false !==  this.values['arrows'].includes('main')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li:after');
				      }

				      if (false !==  this.values['arrows'].includes('submenu')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li:after');
				      }

				      if (false !==  this.values['arrows'].includes('active')) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li:after');
				      }

				    }

				    this.addCssProperty(selectors, 'left', 'calc(0px - ' +  this.values['submenu_space'] + ')');
				  }

				  if ('row' ===  this.values['direction']) {
				    if ('hover' ===  this.values['expand_method']) {
				      selectors = [ this.baseSelector + '.expand-method-hover.direction-row .fusion-menu-element-list > li:hover:before', this.baseSelector + '.expand-method-hover.direction-row .fusion-menu-element-list > li.hover:before', this.baseSelector + '.expand-method-hover.direction-row .fusion-menu-element-list > li:focus:before', this.baseSelector + '.expand-method-hover.direction-row .fusion-menu-element-list > li:active:before', this.baseSelector + '.expand-method-hover.direction-row .fusion-menu-element-list > li:focus-within:before' ];
				      this.addCssProperty(selectors, 'height',  this.values['submenu_space']);
				    }

				    if ('slide_up' ===  this.values['expand_transition']) {
				      this.addCssProperty([ this.baseSelector + '.submenu-transition-slide_up:not(.collapse-enabled).expand-method-' +  this.values['expand_method'] + '.direction-row .fusion-menu-element-list li::after' ], 'transform', 'translateY(' +  this.values['submenu_space'] + ')');
				    }

				  }

				}

				if ( (!this.isDefault('submenu_space') || !this.isDefault('arrows_size_width')) && 'column' ===  this.values['direction']) {
				  selectors = [ ];
				  if ('click' ===  this.values['expand_method']) {
				    if (false !==  this.values['arrows'].includes('main')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-column > ul > li:not(.fusion-menu-item-button):after');
				    }

				    if (false !==  this.values['arrows'].includes('submenu')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-column > ul > li:not(.fusion-menu-item-button):after');
				    }

				  }

				  if ('hover' ===  this.values['expand_method']) {
				    if (false !==  this.values['arrows'].includes('main')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li:after');
				    }

				    if (false !==  this.values['arrows'].includes('submenu')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-right > ul > li:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li:after');
				    }

				  }

				  this.addCssProperty(selectors, 'width', 'calc(' +  this.values['submenu_space'] + ' - ' +  this.values['arrows_size_width'] + ' * 2)');
				}

				if ( (!this.isDefault('submenu_space') || !this.isDefault('arrows_size_height')) && 'row' ===  this.values['direction']) {
				  if (false !==  this.values['arrows'].includes('active')) {
				    this.addCssProperty([ this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-' +  this.values['expand_method'] + '.direction-row > ul > li:after' ], 'height',  this.values['submenu_space']);
				  }

				  if ('slide_up' ===  this.values['expand_transition']) {
				    this.addCssProperty([ this.baseSelector + ':not(.collapse-enabled).submenu-transition-slide_up.direction-row.dropdown-arrows-parent > ul > li:after' ], 'top', 'calc(100% - ' +  this.values['submenu_space'] + ')', true);
				  }

				  selectors = [ ];
				  if ('click' ===  this.values['expand_method']) {
				    if (false !==  this.values['arrows'].includes('main')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children.expanded:after');
				    }

				    if (false !==  this.values['arrows'].includes('submenu')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.expanded:after');
				    }

				  }

				  if (false !==  this.values['arrows'].includes('main')) {
				    selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-' +  this.values['expand_method'] + '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:hover:after');
				    selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-' +  this.values['expand_method'] + '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children.hover:after');
				    selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-' +  this.values['expand_method'] + '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:focus:after');
				    selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-' +  this.values['expand_method'] + '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:active:after');
				    selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-' +  this.values['expand_method'] + '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:focus-within:after');
				  }

				  if ('hover' ===  this.values['expand_method'] && false !==  this.values['arrows'].includes('submenu')) {
				    selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:hover:after');
				    selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.hover:after');
				    selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:focus:after');
				    selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:active:after');
				    selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:focus-within:after');
				  }

				  this.addCssProperty(selectors, 'height', 'calc(' +  this.values['submenu_space'] + ' - ' +  this.values['arrows_size_height'] + ' * 2)');
				  if (false !==  this.values['arrows'].includes('active')) {
				    this.addCssProperty([ this.baseSelector + '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li:hover:after', this.baseSelector + '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li.hover:after', this.baseSelector + '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li:focus:after', this.baseSelector + '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li:active:after', this.baseSelector + '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li:focus-within:after', this.baseSelector + '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li.current-menu-item:after', this.baseSelector + '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li.current-menu-ancestor:after', this.baseSelector + '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li.current-menu-parent:after', this.baseSelector + '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li.expanded:after' ], 'height', 'calc(' +  this.values['submenu_space'] + ' - ' +  this.values['arrows_size_height'] + ' * 2)', true);
				  }

				}

				if (!this.isDefault('arrows_size_width')) {
				  selectors = [ ];
				  if ('click' ===  this.values['expand_method']) {
				    if (false !==  this.values['arrows'].includes('main')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent > ul > li.menu-item-has-children.expanded:after');
				    }

				    if (false !==  this.values['arrows'].includes('submenu')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child > ul > li.menu-item-has-children.expanded:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-click > ul > li:not(.fusion-menu-item-button):after');
				    }

				  }

				  if ('hover' ===  this.values['expand_method']) {
				    if (false !==  this.values['arrows'].includes('main')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:hover:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children.hover:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:focus:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:active:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:focus-within:after');
				    }

				    if (false !==  this.values['arrows'].includes('submenu')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:hover:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children.hover:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:focus:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:active:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:focus-within:after');
				    }

				    if (false !==  this.values['arrows'].includes('active')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover > ul > li:not(.fusion-menu-item-button):after');
				    }

				  }

				  this.addCssProperty(selectors, 'border-left-width',  this.values['arrows_size_width']);
				  this.addCssProperty(selectors, 'border-right-width',  this.values['arrows_size_width']);
				}

				if (!this.isDefault('arrows_size_height')) {
				  selectors = [ ];
				  if ('click' ===  this.values['expand_method']) {
				    if (false !==  this.values['arrows'].includes('main')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent > ul > li.menu-item-has-children.expanded:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent > ul > li.menu-item-has-children.expanded:after');
				    }

				    if (false !==  this.values['arrows'].includes('submenu')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child > ul > li.menu-item-has-children.expanded:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child > ul > li.menu-item-has-children.expanded:after');
				    }

				    if (false !==  this.values['arrows'].includes('active')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.active-item-arrows-on > ul > li:not(.fusion-menu-item-button):after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.active-item-arrows-on > ul > li:not(.fusion-menu-item-button):after');
				    }

				  }

				  if ('hover' ===  this.values['expand_method']) {
				    if (false !==  this.values['arrows'].includes('main')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:hover:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children.hover:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:focus:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:active:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:focus-within:after');
				    }

				    if (false !==  this.values['arrows'].includes('submenu')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:hover:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children.hover:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:focus:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:active:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:focus-within:after');
				    }

				    if (false !==  this.values['arrows'].includes('active')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover > ul > li:not(.fusion-menu-item-button):after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover > ul > li:not(.fusion-menu-item-button):after');
				    }

				  }

				  this.addCssProperty(selectors, 'border-top-width',  this.values['arrows_size_height']);
				  this.addCssProperty(selectors, 'border-bottom-width',  this.values['arrows_size_height']);
				  selectors = [ ];
				  if ('click' ===  this.values['expand_method']) {
				    if (false !==  this.values['arrows'].includes('main')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-column > ul > li:not(.fusion-menu-item-button):after');
				    }

				    if (false !==  this.values['arrows'].includes('submenu')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-column > ul > li:not(.fusion-menu-item-button):after');
				    }

				  }

				  if ('hover' ===  this.values['expand_method']) {
				    if (false !==  this.values['arrows'].includes('main')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li:after');
				    }

				    if (false !==  this.values['arrows'].includes('submenu')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-right > ul > li:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li:after');
				    }

				  }

				  this.addCssProperty(selectors, 'top', 'calc(50% - ' +  this.values['arrows_size_height'] + ')');
				}

				if ('flyout' !==  this.values['submenu_mode']) {
				  if (  !  this.isDefault('submenu_border_radius_top_left')) {
				    selectors = [ this.baseSelector + ' .fusion-menu-element-list .sub-menu', this.baseSelector + ' .fusion-menu-element-list .sub-menu > li:first-child', this.baseSelector + ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content', this.baseSelector + ' .fusion-megamenu-wrapper' ];
				    this.addCssProperty(selectors, 'border-top-left-radius',  this.values['submenu_border_radius_top_left']);
				  }

				  if (  !  this.isDefault('submenu_border_radius_top_right')) {
				    selectors = [ this.baseSelector + ' .fusion-menu-element-list .sub-menu', this.baseSelector + ' .fusion-menu-element-list .sub-menu > li:first-child', this.baseSelector + ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content', this.baseSelector + ' .fusion-megamenu-wrapper' ];
				    this.addCssProperty(selectors, 'border-top-right-radius',  this.values['submenu_border_radius_top_right']);
				  }

				  if (  !  this.isDefault('submenu_border_radius_bottom_left')) {
				    selectors = [ this.baseSelector + ' .fusion-menu-element-list .sub-menu', this.baseSelector + ' .fusion-menu-element-list .sub-menu > li:last-child', this.baseSelector + ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content', this.baseSelector + ' .fusion-megamenu-wrapper' ];
				    this.addCssProperty(selectors, 'border-bottom-left-radius',  this.values['submenu_border_radius_bottom_left']);
				  }

				  if (  !  this.isDefault('submenu_border_radius_bottom_right')) {
				    selectors = [ this.baseSelector + ' .fusion-menu-element-list .sub-menu', this.baseSelector + ' .fusion-menu-element-list .sub-menu > li:last-child', this.baseSelector + ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content', this.baseSelector + ' .fusion-megamenu-wrapper' ];
				    this.addCssProperty(selectors, 'border-bottom-right-radius',  this.values['submenu_border_radius_bottom_right']);
				  }

				}

				selectors = [ this.baseSelector + ' .fusion-menu-element-list .sub-menu > li', this.baseSelector + ' .fusion-menu-element-list .sub-menu li a' ];
				menuStyles = _.fusionGetFontStyle( 'submenu_typography', this.values, 'object' );
				jQuery.each( menuStyles, function( rule, value ) {
					self.addCssProperty( selectors, rule, value );
				} );

				if (!this.isDefault('submenu_bg')) {
				  selectors = [ this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-holder', this.baseSelector + ' .sub-menu .fusion-menu-cart', this.baseSelector + ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content', this.baseSelector + ' .avada-menu-login-box .avada-custom-menu-item-contents' ];
				  if ('flyout' ===  this.values['submenu_mode']) {
				    selectors.push( this.baseSelector + '.submenu-mode-flyout .fusion-custom-menu .sub-menu');
				    selectors.push( this.baseSelector + '.submenu-mode-flyout .fusion-custom-menu .fusion-megamenu-wrapper');
				    selectors.push( this.baseSelector + '.submenu-mode-flyout .fusion-custom-menu .fusion-flyout-menu-backgrounds');
				  }
				  else {
				    selectors.push( this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button)');
					selectors.push( this.baseSelector + ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled' );

				  }

				  this.addCssProperty(selectors, 'background-color',  this.values['submenu_bg']);
				  if ('row' ===  this.values['direction']) {
				    if (false !==  this.values['arrows'].includes('submenu')) {
				      selectors = [ ];
				      if ('click' ===  this.values['expand_method']) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.expanded:after');
				      }

				      if ('hover' ===  this.values['expand_method']) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:focus-within:after');
				      }

				      this.addCssProperty(selectors, 'border-bottom-color',  this.values['submenu_bg']);
				    }

				  }

				  if ('column' ===  this.values['direction']) {
				    if (false !==  this.values['arrows'].includes('submenu')) {
				      selectors = [ ];
				      if ('click' ===  this.values['expand_method']) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-column.expand-right > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu):after');
				      }

				      if ('hover' ===  this.values['expand_method']) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus-within:after');
				      }

				      this.addCssProperty(selectors, 'border-right-color',  this.values['submenu_bg']);
				    }

				    if (false !==  this.values['arrows'].includes('submenu')) {
				      selectors = [ ];
				      if ('click' ===  this.values['expand_method']) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-column.expand-left > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu):after');
				      }

				      if ('hover' ===  this.values['expand_method']) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus-within:after');
				      }

				      this.addCssProperty(selectors, 'border-left-color',  this.values['submenu_bg']);
				    }

				  }

				}

				if (!this.isDefault('submenu_color')) {
				  selectors = [ this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu .fusion-megamenu-title a', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu .fusion-megamenu-icon', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu .fusion-megamenu-widgets-container .widget_text .textwidget', this.baseSelector + ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled' ];
				  // In hover mode color is inherited from parent anchor.
				  if ('click' ===  this.values['expand_method']) {
				    selectors.push( this.baseSelector + ' ul ul .fusion-open-nav-submenu');
				  }

				  this.addCssProperty(selectors, 'color',  this.values['submenu_color']);
				  // Important ones.
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button) > a', 'color',  this.values['submenu_color'], true);
				  if ('click' ===  this.values['expand_method']) {
				    this.addCssProperty( this.baseSelector + '.expand-method-click li .sub-menu .fusion-open-nav-submenu', 'color',  this.values['submenu_color'], true);
				  }

				}

				if (true) {
				  this.addCssProperty([ this.baseSelector + ' .fusion-menu-cart-checkout a:before', this.baseSelector + ' .fusion-menu-cart-items a', this.baseSelector + ' ul .fusion-menu-login-box-register', this.baseSelector + ' ul .fusion-menu-cart-checkout a:before', this.baseSelector + ' .fusion-menu-cart-items a' ], 'color',  this.values['submenu_color']);
				}

				if (!this.isDefault('submenu_active_bg')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu):not(.fusion-menu-searchform-dropdown) > li:not(.fusion-menu-item-button):hover', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu):not(.fusion-menu-searchform-dropdown) > li:not(.fusion-menu-item-button).hover', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu):not(.fusion-menu-searchform-dropdown) > li:not(.fusion-menu-item-button):focus', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu):not(.fusion-menu-searchform-dropdown) > li:not(.fusion-menu-item-button):focus-within', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu):not(.fusion-menu-searchform-dropdown) > li:not(.fusion-menu-item-button).expanded', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-item:not(.fusion-menu-item-button)', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-parent:not(.fusion-menu-item-button)', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-ancestor:not(.fusion-menu-item-button)', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current_page_item:not(.fusion-menu-item-button)', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a.hover', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:active', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus-within', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a.hover > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:active > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus-within > .fusion-open-nav-submenu', this.baseSelector + '.submenu-mode-dropdown li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children:focus-within .fusion-open-nav-submenu', this.baseSelector + '.submenu-mode-dropdown li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children .fusion-background-highlight:hover .fusion-open-nav-submenu', this.baseSelector + '.submenu-mode-dropdown li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children:focus-within > .fusion-background-highlight', this.baseSelector + '.submenu-mode-dropdown li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children .fusion-background-highlight:hover' ];
				  this.addCssProperty(selectors, 'background-color',  this.values['submenu_active_bg']);
				  if ('column' ===  this.values['direction']) {
				    selectors = [ ];
				    if ('click' ===  this.values['expand_method'] && false !==  this.values['arrows'].includes('submenu')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-column.expand-right > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu).alt-arrow-child-color:after');
				    }

				    if ('hover' ===  this.values['expand_method'] && false !==  this.values['arrows'].includes('main')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:hover:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color.hover:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:focus:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:active:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:focus-within:after');
				    }

				    this.addCssProperty(selectors, 'border-right-color',  this.values['submenu_active_bg']);
				    selectors = [ ];
				    if ('click' ===  this.values['expand_method'] && false !==  this.values['arrows'].includes('submenu')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-column.expand-left > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu).alt-arrow-child-color:after');
				    }

				    if ('hover' ===  this.values['expand_method'] && false !==  this.values['arrows'].includes('main')) {
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:hover:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color.hover:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:focus:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:active:after');
				      selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:focus-within:after');
				    }

				    this.addCssProperty(selectors, 'border-left-color',  this.values['submenu_active_bg']);
				  }

				  if ('row' ===  this.values['direction']) {
				    if (false !==  this.values['arrows'].includes('submenu')) {
				      selectors = [ ];
				      if ('click' ===  this.values['expand_method']) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.expanded.alt-arrow-child-color:after');
				      }

				      if ('hover' ===  this.values['expand_method']) {
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.alt-arrow-child-color:hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.alt-arrow-child-color.hover:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.alt-arrow-child-color:focus:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.alt-arrow-child-color:active:after');
				        selectors.push( this.baseSelector + ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.alt-arrow-child-color:focus-within:after');
				      }

				      this.addCssProperty(selectors, 'border-bottom-color',  this.values['submenu_active_bg']);
				    }

				  }

				}

				if (!this.isDefault('submenu_active_color')) {
				  // Important ones.
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:hover > a', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.hover > a', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:focus > a', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:focus-within > a', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button):hover > a', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.hover:not(.fusion-menu-item-button) > a', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button):focus > a', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button):focus-within > a', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.expanded > a', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button).current-menu-item > a', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-ancestor > a', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-parent > a', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:hover > a .fusion-button', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.hover > a .fusion-button', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:focus > a .fusion-button', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:focus-within > a .fusion-button', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.expanded > a .fusion-button', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button).current-menu-item > a .fusion-button', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-ancestor > a .fusion-button', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-parent > a .fusion-button', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:hover > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.hover > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:focus > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:focus-within > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.expanded > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-item > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-ancestor > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-parent > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a.hover', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:active', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus-within', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a.hover > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:active > .fusion-open-nav-submenu', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus-within > .fusion-open-nav-submenu', this.baseSelector + '.submenu-mode-dropdown li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children:focus-within .fusion-open-nav-submenu', this.baseSelector + ' li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children .fusion-background-highlight:hover .fusion-open-nav-submenu', this.baseSelector + ' li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children:focus-within > .fusion-background-highlight', this.baseSelector + ' li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children .fusion-background-highlight:hover' ];
				  if (true) {
				    selectors.push( this.baseSelector + ' .fusion-menu-cart-checkout:hover .fusion-menu-cart-link a');
				    selectors.push( this.baseSelector + ' .fusion-menu-cart-checkout:hover .fusion-menu-cart-checkout-link a');
				    selectors.push( this.baseSelector + ' .fusion-menu-cart-checkout:hover .fusion-menu-cart-link a:before');
				    selectors.push( this.baseSelector + ' .fusion-menu-cart-checkout:hover .fusion-menu-cart-checkout-link a:before');
					selectors.push( this.baseSelector + ' .fusion-menu-cart-checkout:focus .fusion-menu-cart-link a:before');
					selectors.push( this.baseSelector + ' .fusion-menu-cart-checkout:focus .fusion-menu-cart-checkout-link a:before');					
				  }

				  this.addCssProperty(selectors, 'color',  this.values['submenu_active_color'], true);
				}

				if ('flyout' ===  this.values['submenu_mode']) {
				  selectors = [ this.baseSelector + '.submenu-mode-flyout .fusion-close-flyout:before', this.baseSelector + '.submenu-mode-flyout .fusion-close-flyout:after' ];
				  this.addCssProperty(selectors, 'background-color',  this.values['flyout_close_color']);
				  selectors = [ this.baseSelector + '.submenu-mode-flyout .fusion-close-flyout:hover:before', this.baseSelector + '.submenu-mode-flyout .fusion-close-flyout:hover:after' ];
				  this.addCssProperty(selectors, 'background-color',  this.values['flyout_active_close_color'], true);
				}

				if (!this.isDefault('submenu_max_width') && 'dropdown' ===  this.values['submenu_mode']) {
				  this.addCssProperty([ this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list ul:not(.fusion-megamenu) > li' ], 'width',  this.values['submenu_max_width'], true);
				  // Don't set min width if sub menu width is explictly set.
				  this.addCssProperty([ this.baseSelector + '.direction-row:not(.collapse-enabled) .sub-menu' ], 'min-width', '0');
				}

				if (!this.isDefault('submenu_items_padding_top')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button) > a', this.baseSelector + ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu .sub-menu a', this.baseSelector + ' .sub-menu .fusion-menu-cart a', this.baseSelector + ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content', this.baseSelector + ' .avada-menu-login-box .avada-custom-menu-item-contents form' ];
				  if ('click' ===  this.values['expand_method']) {
				    selectors.push( this.baseSelector + '.expand-method-click li ul .fusion-open-nav-submenu');
				  }

				  this.addCssProperty(selectors, 'padding-top',  this.values['submenu_items_padding_top']);
				  if ('column' ===  this.values['direction']) {
				    this.addCssProperty( this.baseSelector + '.direction-column .fusion-menu-element-list ul', 'top', 'calc(0.5em - ' +  this.values['submenu_items_padding_top'] + ')');
				  }

				}

				if (!this.isDefault('submenu_items_padding_right')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button) > a', this.baseSelector + ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu .sub-menu a', this.baseSelector + ' .sub-menu .fusion-menu-cart a', this.baseSelector + ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content', this.baseSelector + ' ul ul .fusion-open-nav-submenu:before', this.baseSelector + ' .avada-menu-login-box .avada-custom-menu-item-contents form', this.baseSelector + ' .avada-menu-login-box .avada-custom-menu-item-contents .fusion-menu-login-box-register' ];
				  if ('never' !==  this.values['breakpoint']) {
				    selectors.push( this.baseSelector + '.collapse-enabled .fusion-megamenu-holder');
				  }

				  if ('column' ===  this.values['direction'] && jQuery( 'body' ).hasClass( 'rtl' ) && 'click' ===  this.values['expand_method']) {
				    selectors.push('.ltr' +  this.baseSelector + '.direction-column.expand-method-click.expand-left .menu-item-has-children li a');
				  }

				  if (true) {
				    selectors.push( this.baseSelector + ' .fusion-menu-cart-checkout');
				  }

				  if ('flyout' ===  this.values['submenu_mode']) {
				    selectors.push('.ltr' +  this.baseSelector + '.submenu-mode-flyout:not(.collapse-enabled) .sub-menu li:not(.fusion-menu-item-button) > a');
				  }

				  this.addCssProperty(selectors, 'padding-right',  this.values['submenu_items_padding_right']);
				  if (true) {
				    this.addCssProperty('.rtl' +  this.baseSelector + ' .fusion-menu-cart-link', 'padding-right', '0');
				    this.addCssProperty('.ltr' +  this.baseSelector + ' .fusion-menu-cart-checkout-link', 'padding-right', '0');
				  }

				}

				if (!this.isDefault('submenu_items_padding_bottom')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button) > a', this.baseSelector + ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu .sub-menu a', this.baseSelector + ' .sub-menu .fusion-menu-cart a', this.baseSelector + ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content', this.baseSelector + ' .avada-menu-login-box .avada-custom-menu-item-contents .fusion-menu-login-box-register' ];
				  if ('click' ===  this.values['expand_method']) {
				    selectors.push( this.baseSelector + '.expand-method-click li ul .fusion-open-nav-submenu');
				  }

				  this.addCssProperty(selectors, 'padding-bottom',  this.values['submenu_items_padding_bottom']);
				}

				if (!this.isDefault('submenu_items_padding_left')) {
				  selectors = [ this.baseSelector + ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button) > a', this.baseSelector + ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu .sub-menu a', this.baseSelector + ' .sub-menu .fusion-menu-cart a', this.baseSelector + ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content', this.baseSelector + ' ul ul .fusion-open-nav-submenu:before', this.baseSelector + ' .avada-menu-login-box .avada-custom-menu-item-contents form', this.baseSelector + ' .avada-menu-login-box .avada-custom-menu-item-contents .fusion-menu-login-box-register' ];
				  if ('never' !==  this.values['breakpoint']) {
				    selectors.push( this.baseSelector + '.collapse-enabled .fusion-megamenu-holder');
				  }

				  if ('column' ===  this.values['direction'] && jQuery( 'body' ).hasClass( 'rtl' ) && 'click' ===  this.values['expand_method']) {
				    selectors.push('.rtl' +  this.baseSelector + '.direction-column.expand-method-click.expand-right .menu-item-has-children li a');
				  }

				  if (true) {
				    selectors.push( this.baseSelector + ' .fusion-menu-cart-checkout');
				  }

				  if ('flyout' ===  this.values['submenu_mode']) {
				    selectors.push('.rtl' +  this.baseSelector + '.submenu-mode-flyout:not(.collapse-enabled) .sub-menu li:not(.fusion-menu-item-button) > a');
				  }

				  this.addCssProperty(selectors, 'padding-left',  this.values['submenu_items_padding_left']);
				  if (true) {
				    this.addCssProperty('.rtl' +  this.baseSelector + ' .fusion-menu-cart-checkout-link', 'padding-left', '0');
				    this.addCssProperty('.ltr' +  this.baseSelector + ' .fusion-menu-cart-link', 'padding-left', '0');
				  }

				}

				if ( (!this.isDefault('submenu_items_padding_left') || !this.isDefault('submenu_items_padding_right')) && 'click' ===  this.values['expand_method']) {
				  this.addCssProperty( this.baseSelector + '.expand-method-click li ul .fusion-open-nav-submenu', 'width', 'calc(1em + ' +  this.values['submenu_items_padding_left'] + ' / 2 + ' +  this.values['submenu_items_padding_right'] + ' / 2)');
				}

				if (!this.isDefault('submenu_sep_color')) {
				  this.addCssProperty([ this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list ul:not(.fusion-megamenu) > li' ], 'border-bottom-color',  this.values['submenu_sep_color']);
				  this.addCssProperty([ this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu', this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-submenu .fusion-megamenu-border' ], 'border-color',  this.values['submenu_sep_color']);
				  this.addCssProperty( this.baseSelector + ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled', 'border-color',  this.values['submenu_sep_color']);
				}

				if (!this.isDefault('submenu_font_size')) {
				  selectors = [ this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list ul:not(.fusion-megamenu) a', this.baseSelector + ':not(.collapse-enabled) .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled', this.baseSelector + ':not(.collapse-enabled) .fusion-megamenu-wrapper .fusion-megamenu-submenu > a', this.baseSelector + ':not(.collapse-enabled) .fusion-megamenu-wrapper .fusion-megamenu-submenu > a.hover', this.baseSelector + ':not(.collapse-enabled) .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover', this.baseSelector + ':not(.collapse-enabled) .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus', this.baseSelector + ':not(.collapse-enabled) .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:active', this.baseSelector + ':not(.collapse-enabled) .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus-within', this.baseSelector + ':not(.collapse-enabled) .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu' ];
				  this.addCssProperty(selectors, 'font-size',  this.values['submenu_font_size']);
				  if ('flyout' ===  this.values['submenu_mode']) {
				    selectors = [ this.baseSelector + '.submenu-mode-flyout .fusion-close-flyout' ];
				    this.addCssProperty(selectors, 'width',  this.values['submenu_font_size']);
				    this.addCssProperty(selectors, 'height',  this.values['submenu_font_size']);
				  }

				}

				if (!this.isDefault('mobile_nav_button_align_hor')) {
				  selectors = [ ];
				  if ('on' ===  this.values['mobile_nav_trigger_fullwidth']) {
				    selectors.push( this.baseSelector + '.mobile-trigger-fullwidth-on > .avada-menu-mobile-menu-trigger > .inner');
				  }

				  if ('never' !==  this.values['breakpoint']) {
				    selectors.push( this.baseSelector + '.collapse-enabled');
				  }

				  this.addCssProperty(selectors, 'justify-content',  this.values['mobile_nav_button_align_hor']);
				}

				if (!this.isDefault('mobile_nav_trigger_bottom_margin') && 'never' !==  this.values['breakpoint']) {
				  this.addCssProperty( this.baseSelector + '.collapse-enabled .fusion-menu-element-list', 'margin-top',  _.fusionGetValueWithUnit( this.values['mobile_nav_trigger_bottom_margin']));
				}

				if (!this.isDefault('submenu_text_transform') ) {
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list ul', 'text-transform',  this.values['submenu_text_transform']);
				}

				if (!this.isDefault('submenu_line_height') ) {
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list ul', 'line-height',  this.values['submenu_line_height']);
				}

				if (!this.isDefault('submenu_letter_spacing') ) {
				  this.addCssProperty( this.baseSelector + ' .fusion-menu-element-list ul', 'letter-spacing',  _.fusionGetValueWithUnit( this.values['submenu_letter_spacing'] ));
				}

				if (!this.isDefault('icons_size')) {
				  this.addCssProperty([ this.baseSelector + ':not(.collapse-enabled) li.menu-item > .fusion-megamenu-icon', this.baseSelector + ':not(.collapse-enabled) li.menu-item > a > .fusion-megamenu-icon', this.baseSelector + ':not(.collapse-enabled) li.menu-item > a.fusion-menu-icon-search' ], 'font-size',  _.fusionGetValueWithUnit( this.values['icons_size']));
				}

				if (!this.isDefault('icons_color')) {
				  this.addCssProperty([ this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item > .fusion-megamenu-icon', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item > a > .fusion-megamenu-icon' ], 'color',  this.values['icons_color']);
				  this.addCssProperty([ this.baseSelector + ' .custom-menu-search-dropdown .fusion-main-menu-icon', this.baseSelector + ' .custom-menu-search-overlay .fusion-menu-icon-search.trigger-overlay', this.baseSelector + ' .custom-menu-search-overlay ~ .fusion-overlay-search' ], 'color',  this.values['icons_color'], true);
				}

				this.addCssProperty([ this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item:hover > a .fusion-megamenu-icon', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item.hover > a .fusion-megamenu-icon', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item:focus > a .fusion-megamenu-icon', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item:active > a .fusion-megamenu-icon', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item:focus-within > a .fusion-megamenu-icon', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item.current-menu-item > a .fusion-megamenu-icon', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item.current-menu-ancestor > a .fusion-megamenu-icon', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.awb-flyout-top-level-no-link).menu-item.current-menu-parent > a .fusion-megamenu-icon', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item.expanded > a .fusion-megamenu-icon' ], 'color',  this.values['icons_hover_color']);
				this.addCssProperty([ this.baseSelector + ' .custom-menu-search-dropdown:hover .fusion-main-menu-icon', this.baseSelector + ' .custom-menu-search-overlay:hover .fusion-menu-icon-search.trigger-overlay', this.baseSelector + ' .custom-menu-search-overlay:hover ~ .fusion-overlay-search' ], 'color',  this.values['icons_hover_color'], true);
				// Thumbnail size.
				if (!this.isDefault('thumbnail_size_width')) {
				  this.addCssProperty( this.baseSelector + ':not(.collapse-enabled) .fusion-megamenu-title .fusion-megamenu-image > img', 'width',  this.values['thumbnail_size_width']);
				  this.addCssProperty( this.baseSelector + ':not(.collapse-enabled) .fusion-megamenu-title .fusion-megamenu-thumbnail > img', 'width',  this.values['thumbnail_size_width']);
				}

				if (!this.isDefault('thumbnail_size_height')) {
				  this.addCssProperty( this.baseSelector + ':not(.collapse-enabled) .fusion-megamenu-title .fusion-megamenu-image > img', 'height',  this.values['thumbnail_size_height']);
				  this.addCssProperty( this.baseSelector + ':not(.collapse-enabled) .fusion-megamenu-title .fusion-megamenu-thumbnail > img', 'height',  this.values['thumbnail_size_width']);
				}

				if (!this.isDefault('justify_title')) {
				  this.addCssProperty( this.baseSelector + ' .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu .fusion-megamenu-title .awb-justify-title', 'justify-content', this.values['justify_title']);
				}

				if (!this.isDefault('mobile_trigger_font_size')) {
				  this.addCssProperty( this.baseSelector + ' > .avada-menu-mobile-menu-trigger', 'font-size',  this.values['mobile_trigger_font_size']);
				}

				if ('never' !==  this.values['breakpoint']) {
				  // Mobile background.
				  if (  !  this.isDefault('mobile_bg')) {
				    selectors = [
						this.baseSelector + '.collapse-enabled ul li > a', this.baseSelector + '.collapse-enabled ul li:hover .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',	this.baseSelector + '.collapse-enabled ul li.hover .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li:focus .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li:active .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li:focus-within .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li.current-menu-item .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li.current-menu-ancestor .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li.current-menu-parent .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li.expanded .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li.custom-menu-search-inline', this.baseSelector + '.collapse-enabled ul .fusion-menu-form-inline', this.baseSelector + '.collapse-enabled ul li.fusion-menu-item-button', this.baseSelector + '.collapse-enabled ul', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li' ];
				    this.addCssProperty(selectors, 'background',  this.values['mobile_bg'], true);
				  }

				  if (  !  this.isDefault('mobile_color')) {
				    selectors = [ this.baseSelector + '.collapse-enabled ul li > a', this.baseSelector + '.collapse-enabled ul li > a .fusion-button', this.baseSelector + '.collapse-enabled ul li > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul li:hover .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li.hover .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li:focus .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li:active .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li:focus-within .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li.current-menu-item .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li.current-menu-ancestor .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li.current-menu-parent .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled ul li.expanded .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li a', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li .awb-justify-title' ];
				    this.addCssProperty(selectors, 'color',  this.values['mobile_color'], true);
				  }

				  if (  !  this.isDefault('mobile_active_bg')) {
					selectors = [ this.baseSelector + '.collapse-enabled ul li:hover > a', this.baseSelector + '.collapse-enabled ul li.hover > a', this.baseSelector + '.collapse-enabled ul li:focus > a', this.baseSelector + '.collapse-enabled ul li:active > a', this.baseSelector + '.collapse-enabled ul li:focus-within > a', this.baseSelector + '.collapse-enabled ul li.current-menu-item > a', this.baseSelector + '.collapse-enabled ul li.current-menu-ancestor > a', this.baseSelector + '.collapse-enabled ul li.current-menu-parent > a', this.baseSelector + '.collapse-enabled ul li.expanded > a', this.baseSelector + '.collapse-enabled ul li.fusion-menu-item-button:hover', this.baseSelector + '.collapse-enabled ul li.fusion-menu-item-button.hover', this.baseSelector + '.collapse-enabled ul li.fusion-menu-item-button:focus', this.baseSelector + '.collapse-enabled ul li.fusion-menu-item-button:active', this.baseSelector + '.collapse-enabled ul li.fusion-menu-item-button:focus-within', this.baseSelector + '.collapse-enabled ul li.fusion-menu-item-button.current-menu-item', this.baseSelector + '.collapse-enabled ul li.fusion-menu-item-button.current-menu-ancestor', this.baseSelector + '.collapse-enabled ul li.fusion-menu-item-button.current-menu-parent', this.baseSelector + '.collapse-enabled ul li.fusion-menu-item-button.expanded', this.baseSelector + '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):hover a', this.baseSelector + '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded).hover a', this.baseSelector + '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):focus a', this.baseSelector + '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):active a', this.baseSelector + '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):focus-within a',	this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li.current-menu-item', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:hover', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:active', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:focus', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:focus-within' ];
					this.addCssProperty(selectors, 'background',  this.values['mobile_active_bg'], true);
				  }

				  if (  !  this.isDefault('mobile_active_color')) {
					selectors = [ this.baseSelector + '.collapse-enabled ul li:hover > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul li.hover > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul li:focus > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul li:active > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul li:focus-within > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul li:hover > a', this.baseSelector + '.collapse-enabled ul li.hover > a', this.baseSelector + '.collapse-enabled ul li:focus > a', this.baseSelector + '.collapse-enabled ul li:active > a', this.baseSelector + '.collapse-enabled ul li:focus-within > a', this.baseSelector + '.collapse-enabled ul li.current-menu-item > a', this.baseSelector + '.collapse-enabled ul li.current-menu-ancestor > a', this.baseSelector + '.collapse-enabled ul li.current-menu-parent > a', this.baseSelector + '.collapse-enabled ul li.expanded > a', this.baseSelector + '.collapse-enabled ul li:hover > a .fusion-button', this.baseSelector + '.collapse-enabled ul li.hover > a .fusion-button', this.baseSelector + '.collapse-enabled ul li:focus > a .fusion-button', this.baseSelector + '.collapse-enabled ul li:active > a .fusion-button', this.baseSelector + '.collapse-enabled ul li:focus-within > a .fusion-button', this.baseSelector + '.collapse-enabled ul li.current-menu-item > a .fusion-button', this.baseSelector + '.collapse-enabled ul li.current-menu-ancestor > a .fusion-button', this.baseSelector + '.collapse-enabled ul li.current-menu-parent > a .fusion-button', this.baseSelector + '.collapse-enabled ul li.expanded > a .fusion-button', this.baseSelector + '.collapse-enabled ul li:hover > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul li.hover > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul li:focus > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul li:active > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul li:focus-within > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul li.current-menu-item > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul li.current-menu-parent > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul li.current-menu-ancestor > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-ancestor > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-parent > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.expanded > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):hover a', this.baseSelector + '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded).hover a', this.baseSelector + '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):focus a', this.baseSelector + '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):active a', this.baseSelector + '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):focus-within a', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:hover > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.hover > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:focus > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:active > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:focus-within > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:hover > a', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.hover > a', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:focus > a', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:active > a', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:focus-within > a', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-item > a', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-ancestor > a', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-parent > a', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.expanded > a', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:hover > a:hover', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.hover > a:hover', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:focus > a:hover', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:active > a:hover', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:focus-within > a:hover', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-item > a:hover', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-ancestor > a:hover', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-parent > a:hover', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.expanded > a:hover', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:hover > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.hover > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:focus > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:active > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li:focus-within > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-item > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-ancestor > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-parent > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled ul.sub-menu.sub-menu li.expanded > .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li.current-menu-item a', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:hover a', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:active a', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:focus a', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:focus-within a', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:hover .fusion-megamenu-title', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:hover .fusion-megamenu-title .awb-justify-title', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:active .fusion-megamenu-title', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:active .fusion-megamenu-title .awb-justify-title', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:focus .fusion-megamenu-title', this.baseSelector + '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:focus .fusion-megamenu-title .awb-justify-title' ];
				    this.addCssProperty(selectors, 'color',  this.values['mobile_active_color'], true);
				  }

				  if (  !  this.isDefault('mobile_sep_color')) {
				    selectors = [ this.baseSelector + '.collapse-enabled li:not(:last-child)', this.baseSelector + '.collapse-enabled li.menu-item.expanded .fusion-megamenu-wrapper ul.fusion-megamenu li.menu-item-has-children .fusion-megamenu-title', this.baseSelector + '.collapse-enabled li.menu-item:not(.expanded)' ];
				    this.addCssProperty(selectors, 'border-bottom-color',  this.values['mobile_sep_color'], true);
				    this.addCssProperty( this.baseSelector + '.collapse-enabled li.menu-item.expanded .fusion-megamenu-wrapper', 'border-top-color',  this.values['mobile_sep_color']);
				    this.addCssProperty( this.baseSelector + '.collapse-enabled li.menu-item.menu-item-has-children ul.sub-menu li.menu-item-has-children.expanded>ul.sub-menu', 'border-top-color',  this.values['mobile_sep_color']);
				  }

				  if (  !  this.isDefault('mobile_nav_items_height')) {
				    selectors = [ this.baseSelector + '.collapse-enabled ul li > a', this.baseSelector + '.collapse-enabled .fusion-open-nav-submenu-on-click:before', this.baseSelector + '.collapse-enabled li.menu-item' ];
				    this.addCssProperty(selectors, 'min-height',  _.fusionGetValueWithUnit( this.values['mobile_nav_items_height']));
				  }

				  if ( ! this.isDefault('mobile_sticky_max_height') ) {
				  	selectors = [
						'.fusion-body .fusion-sticky-container.fusion-sticky-transition ' + this.baseSelector + '.collapse-enabled.mobile-size-column-absolute:not(.mobile-mode-always-expanded).expanded>ul',
						'.fusion-body .fusion-sticky-container.fusion-sticky-transition ' + this.baseSelector + '.collapse-enabled.mobile-size-full-absolute:not(.mobile-mode-always-expanded).expanded>ul'
					];
					this.addCssProperty(selectors, 'max-height', _.fusionGetValueWithUnit( this.values['mobile_sticky_max_height'] ) );
				  }

					selectors = [ this.baseSelector + '.collapse-enabled .fusion-menu-element-list li a', this.baseSelector + '.collapse-enabled .fusion-menu-element-list li a .fusion-button', this.baseSelector + '.collapse-enabled .fusion-menu-element-list li .fusion-open-nav-submenu:before', this.baseSelector + '.collapse-enabled .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu-submenu .fusion-megamenu-title a' ];

					if (  !  this.isDefault('mobile_font_size')) {
						this.addCssProperty( selectors, 'font-size',  this.values['mobile_font_size']);
					}

					if (  !  this.isDefault('mobile_text_transform')) {
				    this.addCssProperty( selectors, 'text-transform',  this.values['mobile_text_transform']);
				  }

					if (  !  this.isDefault('mobile_line_height')) {
				    this.addCssProperty( selectors, 'line-height',  this.values['mobile_line_height']);
				  }

					if (  !  this.isDefault('mobile_letter_spacing')) {
				    this.addCssProperty( selectors, 'letter-spacing',  _.fusionGetValueWithUnit( this.values['mobile_letter_spacing'] ) );
				  }

					selectors = [ this.baseSelector + '.collapse-enabled', this.baseSelector + '.collapse-enabled ul li > a', this.baseSelector + '.collapse-enabled ul li > a .fusion-button', this.baseSelector + '.collapse-enabled .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu-submenu .fusion-megamenu-title a' ];
					menuStyles = _.fusionGetFontStyle( 'mobile_typography', this.values, 'object' );
					jQuery.each( menuStyles, function( rule, value ) {
						self.addCssProperty( selectors, rule, value );
					} );

				}

				if (!this.isDefault('box_shadow')) {
				  selectors = [ this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list ul', this.baseSelector + ':not(.collapse-enabled) .fusion-menu-element-list .fusion-megamenu-wrapper', this.baseSelector + ':not(.collapse-enabled) .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content' ];
				  this.addCssProperty(selectors, 'box-shadow',  _.fusionGetBoxShadowStyle( this.values));
				}

				css = this.parseCSS();
				return ( css ) ? '<style>' + css + '</style>' : '';
			},

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
			 * Builds attributes.
			 *
			 * @since 3.0
			 * @return {Object}
			 */
			buildAttr: function () {

				var hasActiveBorderBottom = !this.values.active_border_bottom || '' === this.values.active_border_bottom || 0 === parseInt( this.values.active_border_bottom ) ? 'no' : 'yes',
					hasActiveBorderLeft = !this.values.active_border_left || '' === this.values.active_border_left || 0 === parseInt( this.values.active_border_left ) ? 'no' : 'yes',
					hasActiveBorderRight = !this.values.active_border_right || '' === this.values.active_border_right || 0 === parseInt( this.values.active_border_right ) ? 'no' : 'yes',
					expandTransition = 'row' !== this.values.direction ? 'opacity' : this.values.expand_transition,
					attr = {
						class: [
							'fusion-menu-element-wrapper',
							'direction-' + this.values.direction,
							'mode-' + this.values.collapsed_mode,
							'expand-method-' + this.values.expand_method,
							'submenu-mode-' + this.values.submenu_mode,
							'mobile-mode-' + this.values.mobile_nav_mode,
							'mobile-size-' + this.values.mobile_nav_size,
							'icons-position-' + this.values.icons_position,
							'dropdown-carets-' + this.values.dropdown_carets,
							'has-active-border-bottom-' + hasActiveBorderBottom,
							'has-active-border-left-' + hasActiveBorderLeft,
							'has-active-border-right-' + hasActiveBorderRight,
							'mobile-trigger-fullwidth-' + this.values.mobile_nav_trigger_fullwidth,
							'mobile-indent-' + this.values.mobile_indent_submenu,
							'mobile-justify-' + this.values.mobile_justify_content,
							'main-justify-' + this.values.main_justify_content,
						].join( ' ' ),
						style: ''
					};

				if ( 0 <= this.values.arrows.indexOf( 'active' ) ) {
					attr[ 'class' ] += ' active-item-arrows-on';
				}

				if ( 0 <= this.values.arrows.indexOf( 'main' ) ) {
					attr[ 'class' ] += ' dropdown-arrows-parent';
				}

				if ( 0 <= this.values.arrows.indexOf( 'submenu' ) ) {
					attr[ 'class' ] += ' dropdown-arrows-child';
				}

				if ( 'flyout' === this.values.submenu_mode ) {
					attr[ 'class' ] += ' submenu-flyout-direction-' + this.values.submenu_flyout_direction;
				}

				if ( 'flyout' !== this.values.submenu_mode ) {
					attr[ 'class' ] += ' expand-' + this.values.expand_direction;
				}

				if ( 'dropdown' === this.values.submenu_mode ) {
					attr[ 'class' ] += ' submenu-transition-' + expandTransition;
				}

				attr[ 'data-cid' ] = this.model.get( 'cid' );

				this.addUnitsWhereRequired();

				if ( 'small' === this.values.breakpoint ) {
					attr[ 'data-breakpoint' ] = FusionApp.settings.visibility_small;
				} else if ( 'medium' === this.values.breakpoint ) {
					attr[ 'data-breakpoint' ] = FusionApp.settings.visibility_medium;
				} else if ( 'large' === this.values.breakpoint ) {
					attr[ 'data-breakpoint' ] = 10000;
				} else if ( 'custom' === this.values.breakpoint ) {
					attr[ 'data-breakpoint' ] = parseInt( this.values.custom_breakpoint );
				}

				attr[ 'class' ] += _.fusionGetStickyClass( this.values.sticky_display );

				attr[ 'data-transition-type' ] = this.values.transition_type;

				if ( '' !== this.values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + this.values[ 'class' ];
				}

				if ( '' !== this.values.id ) {
					attr.id = this.values.id;
				}

				attr = _.fusionAnimations( this.values, attr );
				attr = _.fusionVisibilityAtts( this.values.hide_on_mobile, attr );

				return attr;
			},

			/**
			 * Modify values.
			 *
			 * @since 3.0
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			addUnitsWhereRequired: function () {
				var values = this.values;

				_.each( [
					'margin_top',
					'margin_bottom',
					'items_padding_top',
					'items_padding_bottom',
					'items_padding_left',
					'items_padding_right',
					'gap',
					'font_size',
					'min_height',
					'border_top',
					'border_bottom',
					'border_left',
					'border_right',
					'active_border_top',
					'active_border_bottom',
					'active_border_left',
					'active_border_right',
					'border_radius_top_left',
					'border_radius_top_right',
					'border_radius_bottom_right',
					'border_radius_bottom_left',
					'submenu_border_radius_top_left',
					'submenu_border_radius_top_right',
					'submenu_border_radius_bottom_right',
					'submenu_border_radius_bottom_left',
					'submenu_space',
					'arrows_size_width',
					'arrows_size_height',
					'submenu_items_padding_top',
					'submenu_items_padding_bottom',
					'submenu_items_padding_left',
					'submenu_items_padding_right',
					'submenu_font_size',
					'box_shadow_horizontal',
					'box_shadow_spread',
					'box_shadow_vertical',
					'thumbnail_size_width',
					'thumbnail_size_height',
					'trigger_padding_top',
					'trigger_padding_right',
					'trigger_padding_bottom',
					'trigger_padding_left',
					'mobile_trigger_font_size',
				], function ( param ) {
					if ( 'undefined' !== typeof values[ param ] && '' !== values[ param ] && !isNaN( values[ param ] ) ) {
						values[ param ] = values[ param ].trim() + 'px';
					}
				} );

				this.values = values;
			},

			/**
			 * Runs just after render on cancel.
			 *
			 * @since 3.5
			 * @return null
			 */
			 beforeGenerateShortcode: function() {
				var elementType = this.model.get( 'element_type' ),
					options     = fusionAllElements[ elementType ].params,
					values      = jQuery.extend( true, {}, fusionAllElements[ elementType ].defaults, _.fusionCleanParameters( this.model.get( 'params' ) ) );

				if ( 'object' !== typeof options ) {
					return;
				}

				// If images needs replaced lets check element to see if we have media being used to add to object.
				if ( 'undefined' !== typeof FusionApp.data.replaceAssets && FusionApp.data.replaceAssets && ( 'undefined' !== typeof FusionApp.data.fusion_element_type || 'fusion_template' === FusionApp.getPost( 'post_type' ) ) ) {

					this.mapStudioImages( options, values );

					if ( '' !== values.menu ) {
						// If its not within object already, add it.
						if ( 'undefined' === typeof FusionPageBuilderApp.mediaMap.menus[ values.menu ] ) {
							FusionPageBuilderApp.mediaMap.menus[ values.menu ] = true;
						}
					}
				}
			},
		} );
	} );
}( jQuery ) );

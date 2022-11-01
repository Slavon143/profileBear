<?php
/**
 * This file contains filters to override Avada Builder global options.
 *
 * @author     ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Sets fusion-builder classes.
 *
 * @param array  $default_elements The default elements array.
 * @param string $class            Used as index in the array.
 */
function avada_set_builder_classes( $default_elements, $class ) {

	// Button class selector.
	$elements = [
		' .fusion-portfolio-one .fusion-button',
		' #main .comment-submit',
		' #main #comment-submit',
		' #reviews input#submit',
		' .comment-form input[type="submit"]',
		' .button-default',
		' .fusion-button-default',
		' .button.default',
		' input.button-default',
		' .post-password-form input[type="submit"]',
		' .ticket-selector-submit-btn[type=submit]',
		' .tml-submit-wrap input[type="submit"]',
		' .slidingbar-area .button-default',
		' .fusion-footer-widget-area .fusion-privacy-placeholder .button-default',
	];
	if ( class_exists( 'GFForms' ) ) {
		$elements[] = ' .gform_wrapper .gform_button';
		$elements[] = ' .gform_wrapper .button';
		$elements[] = ' .gform_page_footer input[type="button"]';
	}
	if ( defined( 'WPCF7_PLUGIN' ) ) {
		$elements[] = ' .wpcf7-form input[type="submit"]';
		$elements[] = ' .wpcf7-submit';
	}
	if ( class_exists( 'bbPress' ) ) {
		$elements[] = ' .bbp-submit-wrapper .button';
		$elements[] = ' #bbp_user_edit_submit';
	}
	if ( class_exists( 'WooCommerce' ) ) {
		$elements[] = ' .price_slider_amount button';
		$elements[] = ' .woocommerce .single_add_to_cart_button';
		$elements[] = '.woocommerce button.button';
		$elements[] = ' .woocommerce button.button';
		$elements[] = ' .woocommerce .avada-shipping-calculator-form .button';
		$elements[] = ' .woocommerce .login .button';
		$elements[] = ' .woocommerce .register .button';
		$elements[] = ' .woocommerce .avada-order-details .order-again .button';
		$elements[] = ' .woocommerce .avada-order-details .order-again .button';
		$elements[] = ' .woocommerce .lost_reset_password input[type="submit"]';
		$elements[] = ' .woocommerce-MyAccount-content form .button'; // Needs space prepended (doesn't start with a body class).
		$elements[] = ' .woocommerce.add_to_cart_inline .button'; // Needs space prepended (doesn't start with a body class).
		$elements[] = ' .woocommerce .cart-collaterals .checkout-button';
		$elements[] = ' .woocommerce .checkout #place_order';
		$elements[] = ' .woocommerce .checkout_coupon .button';
	}
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$elements[] = ' #tribe-events-bar .tribe-bar-filters .tribe-bar-filters-inner .tribe-bar-submit input[type=submit]';
		$elements[] = ' #tribe-events .tribe-events-button';
		$elements[] = ' .single-tribe_events .tribe-events-cal-links .tribe-events-button';
		$elements[] = ' #tribe-events-footer ~ a.tribe-events-ical.tribe-events-button';
		$elements[] = ' #tribe_events_filter_control #tribe_events_filters_toggle';
		$elements[] = ' #tribe_events_filter_control #tribe_events_filters_reset';
		$elements[] = ' #tribe-events .tribe-events-tickets .add-to-cart .tribe-button';
		$elements[] = ' #tribe-events .tribe-events-tickets .tickets_submit .tribe-button';
		$elements[] = ' .page-tribe-attendee-registration button';
		$elements[] = ' #tribe-events .tribe-events-list .tribe-events-event-cost form .tribe-button';
		$elements[] = ' .avada-ec-views-v1 #tribe_events_filters_wrapper .tribe-events-filters-mobile-controls button';
		$elements[] = '.fusion-body .tribe-common .tribe-events-c-search__button';
		$elements[] = '.fusion-body .tribe-common .tribe-common-c-btn-border';
		$elements[] = '.fusion-body .tribe-common a.tribe-common-c-btn-border';
		$elements[] = ' .tribe-events .tribe-events-c-ical > a';
		$elements[] = ' .tribe-block__events-link .tribe-block__btn--link > a';
		$elements[] = ' .tribe-block__event-website a';
		$elements[] = '.fusion-body .tribe_events .tribe-tickets .tribe-tickets__buy';
		$elements[] = '.fusion-body .tribe_events .tribe-tickets__tickets-wrapper .tribe-tickets__tickets-buy';
		$elements[] = '.fusion-body .tribe_events .tribe-events-tickets .tribe-button--rsvp';
		$elements[] = '.fusion-body .tribe-block.tribe-tickets .tribe-tickets .tribe-tickets__buy';
		$elements[] = '.fusion-body .tribe_events .tribe-tickets__attendee-tickets-footer-checkout-button';
		$elements[] = '.fusion-body.page-tribe-attendee-registration .tribe-tickets__registration-submit';
		$elements[] = '.fusion-body .tribe-tickets__rsvp-actions-button-going';
		$elements[] = '.fusion-body .tribe-tickets__rsvp-form-button[type="submit"]';
		$elements[] = '.fusion-body.tribe_community_edit .tribe-button';
	}

	$element_map['.fusion-button-default'] = $elements;

	// Default styling, not default size.
	$non_default_size_elements                  = [
		' .button-default',
		' .fusion-button-default',
		' .button.default',
		' input.button-default',
	];
	$default_size_elements                      = array_diff( $elements, $non_default_size_elements );
	$element_map['.fusion-button-default-size'] = $default_size_elements;

	// Special styling for quantity buttons.
	$elements                               = [
		'.fusion-body #main .quantity .minus',
		'.fusion-body #main .quantity .plus',
		'.fusion-body #main .quantity .qty',
		'.fusion-body #main .quantity .tribe-ticket-quantity',
		'.tribe-events-tickets .woocommerce .quantity input',
		'.tribe-block__tickets__item__quantity button',
		'.single-product .product .summary .cart .quantity .minus',
		'.single-product .product .summary .cart .quantity .plus',
		'.single-product .product .summary .cart .quantity .qty',
	];
	$element_map['.fusion-button-quantity'] = $elements;

	if ( class_exists( 'GFForms' ) ) {
		$elements = [
			' .gform_wrapper .gform_validation_errors',
			' .gform_wrapper .gfield_error .gfield_validation_message',
		];

		$element_map['.alert-danger'] = $elements;
	}

	if ( isset( $element_map[ $class ] ) ) {
		return array_merge( $element_map[ $class ], $default_elements );
	}
	return $default_elements;
}
add_filter( 'fusion_builder_element_classes', 'avada_set_builder_classes', 10, 2 );

/**
 * Alter the default args, note, this is different to simple option override.
 *
 * @since 5.1
 * @param array  $defaults Defaults array.
 * @param string $element  Element name.
 * @param string $args     Saved element args.
 * @return array altered defaults array.
 */
function avada_change_builder_default_args( $defaults, $element, $args ) {

	// If its a custom color scheme selected, then set options based on that.
	if ( 'fusion_button' === $element && false !== strpos( $defaults['color'], 'scheme-' ) && class_exists( 'Avada' ) ) {
		$scheme_id    = str_replace( 'scheme-', '', $defaults['color'] );
		$custom_color = ( class_exists( 'Avada' ) && method_exists( 'Avada_Settings', 'get_custom_color' ) ) ? Avada()->settings->get_custom_color( $scheme_id ) : '';
		// If the scheme exists and has options, use them.  Otherwise set the color scheme to default as fallback.
		if ( ! empty( $custom_color ) ) {
			$defaults['accent_color']          = ( isset( $custom_color['button_accent_color'] ) ) ? strtolower( $custom_color['button_accent_color'] ) : '#ffffff';
			$defaults['accent_hover_color']    = ( isset( $custom_color['button_accent_hover_color'] ) ) ? strtolower( $custom_color['button_accent_hover_color'] ) : '#ffffff';
			$defaults['border_color']          = ( isset( $custom_color['button_border_color'] ) ) ? strtolower( $custom_color['button_border_color'] ) : '#ffffff';
			$defaults['border_hover_color']    = ( isset( $custom_color['button_border_hover_color'] ) ) ? strtolower( $custom_color['button_border_hover_color'] ) : '#ffffff';
			$defaults['bevel_color']           = ( isset( $custom_color['button_bevel_color'] ) ) ? strtolower( $custom_color['button_bevel_color'] ) : '#54770F';
			$defaults['gradient_colors']       = strtolower( $custom_color['button_gradient_top_color'] ) . '|' . strtolower( $custom_color['button_gradient_bottom_color'] );
			$defaults['gradient_hover_colors'] = strtolower( $custom_color['button_gradient_top_color_hover'] ) . '|' . strtolower( $custom_color['button_gradient_bottom_color_hover'] );
		} else {
			$defaults['color'] = 'default';
		}
	}
	return $defaults;
}
add_filter( 'fusion_builder_default_args', 'avada_change_builder_default_args', 10, 3 );

/**
 * Pass on the image_rollover to FB.
 *
 * @since 5.1
 * @param int $image_rollover side header width.
 * @return bool
 */
function fusion_builder_image_rollover( $image_rollover ) {
	return Avada()->settings->get( 'image_rollover' );
}
add_filter( 'fusion_builder_image_rollover', 'fusion_builder_image_rollover', 10, 1 );

/**
 * Pass on the cats_image_rollover to FB.
 *
 * @since 5.1
 * @param int $cats_image_rollover side header width.
 * @return bool
 */
function fusion_builder_cats_image_rollover( $cats_image_rollover ) {
	return Avada()->settings->get( 'cats_image_rollover' );
}
add_filter( 'fusion_builder_cats_image_rollover', 'fusion_builder_cats_image_rollover', 10, 1 );

/**
 * Pass on the title_image_rollover to FB.
 *
 * @access public
 * @since 5.1
 * @param int $title_image_rollover side header width.
 * @return bool
 */
function fusion_builder_title_image_rollover( $title_image_rollover ) {
	return Avada()->settings->get( 'title_image_rollover' );
}

/**
 * Pass on the portfolio_link_icon_target to FB.
 *
 * @since 5.1
 * @param int $portfolio_link_icon_target Side header width.
 * @param int $post_id                    The post-ID.
 * @return bool
 */
function fusion_builder_portfolio_link_icon_target( $portfolio_link_icon_target, $post_id = 0 ) {
	return fusion_get_option( 'portfolio_link_icon_target', false, $post_id );
}

add_filter( 'fusion_builder_portfolio_link_icon_target', 'fusion_builder_portfolio_link_icon_target', 10, 2 );

/**
 * Alter the link target attribute.
 *
 * @since 5.1
 * @param array $link_icon_target The link target.
 * @param array $post_id          The post ID.
 * @return array page option value ( link_icon_target ).
 */
function fusion_builder_link_icon_target( $link_icon_target, $post_id ) {
	return fusion_get_page_option( 'link_icon_target', $post_id );
}
add_filter( 'fusion_builder_link_icon_target', 'fusion_builder_link_icon_target', 10, 2 );

/**
 * Alter the link url attribute.
 *
 * @since 5.1
 * @param string $link_icon_url The URL.
 * @param array  $post_id       The post ID.
 * @return array page option value ( link_icon_url ).
 */
function fusion_builder_link_icon_url( $link_icon_url, $post_id ) {
	return fusion_get_page_option( 'link_icon_url', $post_id );
}
add_filter( 'fusion_builder_link_icon_url', 'fusion_builder_link_icon_url', 10, 2 );

/**
 * Alter the link target attribute.
 *
 * @access public
 * @since 5.1
 * @param array $post_links_target The links target.
 * @param array $post_id           The post ID.
 * @return array page option value ( post_links_target ).
 */
function fusion_builder_post_links_target( $post_links_target, $post_id ) {
	return fusion_get_page_option( 'post_links_target', $post_id );
}
add_filter( 'fusion_builder_post_links_target', 'fusion_builder_post_links_target', 10, 2 );

/**
 * Alter post video option.
 *
 * @since   5.1
 * @param   array $post_id Post ID.
 * @return  array page option value ( video ).
 */
function fusion_builder_get_post_video( $post_id ) {
	return fusion_get_page_option( 'video', $post_id );
}
add_filter( 'fusion_builder_post_video', 'fusion_builder_get_post_video', 10, 1 );

/**
 * Alter the video_url page option used for portfolio.
 *
 * @since 5.1
 * @param string $video_url The URL of the video.
 * @param array  $post_id   The post ID.
 * @return array page option value ( video_url ).
 */
function fusion_builder_video_url( $video_url, $post_id ) {
	return fusion_get_page_option( 'video_url', $post_id );
}
add_filter( 'fusion_builder_video_url', 'fusion_builder_video_url', 10, 2 );

/**
 * Alter the default value of widget_area_title_color option.
 *
 * @since 5.1
 * @param string $title_color Widget area title color.
 * @return string option value ( h4_typography, color ).
 */
function fusion_builder_widget_area_title_color( $title_color ) {
	$h4_typography = Avada()->settings->_get( 'h4_typography' );
	return isset( $h4_typography['color'] ) ? $h4_typography['color'] : $title_color;
}
add_filter( 'fusion_builder_widget_area_title_color', 'fusion_builder_widget_area_title_color', 10, 2 );

/**
 * Alter the default value of widget_area_title_size option.
 *
 * @since 5.1
 * @param string $title_size Widget area title font size.
 * @return string option value ( h4_typography, font-size ).
 */
function fusion_builder_widget_area_title_size( $title_size ) {
	$h4_typography = Avada()->settings->_get( 'h4_typography' );
	return isset( $h4_typography['font-size'] ) ? $h4_typography['font-size'] : $title_size;
}
add_filter( 'fusion_builder_widget_area_title_size', 'fusion_builder_widget_area_title_size', 10, 2 );

/**
 * Alter tagline inline style.
 *
 * @since 5.1
 * @param string $styles   Existing styles.
 * @param array  $defaults Default arguments.
 * @param int    $count    Integer value used in the CSS class.
 * @return string style with additional content.
 */
function fusion_builder_tagline_box_style( $styles, $defaults, $count ) {

	// If its a custom color scheme selected, then created a style block.
	if ( false !== strpos( $defaults['buttoncolor'], 'scheme-' ) && class_exists( 'Avada' ) ) {
		extract( $defaults );
		$scheme_id    = str_replace( 'scheme-', '', $defaults['buttoncolor'] );
		$custom_color = ( class_exists( 'Avada' ) && method_exists( 'Avada_Settings', 'get_custom_color' ) ) ? Avada()->settings->get_custom_color( $scheme_id ) : '';
		// If the scheme exists and has options then create style block.
		$accent_color          = ( isset( $custom_color['button_accent_color'] ) ) ? strtolower( $custom_color['button_accent_color'] ) : '#ffffff';
		$accent_hover_color    = ( isset( $custom_color['button_accent_hover_color'] ) ) ? strtolower( $custom_color['button_accent_hover_color'] ) : '#ffffff';
		$border_color          = ( isset( $custom_color['button_border_color'] ) ) ? strtolower( $custom_color['button_border_color'] ) : '#ffffff';
		$border_hover_color    = ( isset( $custom_color['button_border_hover_color'] ) ) ? strtolower( $custom_color['button_border_hover_color'] ) : '#ffffff';
		$bevel_color           = ( isset( $custom_color['button_bevel_color'] ) ) ? strtolower( $custom_color['button_bevel_color'] ) : '#54770F';
		$gradient_colors       = strtolower( $custom_color['button_gradient_top_color'] ) . '|' . strtolower( $custom_color['button_gradient_bottom_color'] );
		$gradient_hover_colors = strtolower( $custom_color['button_gradient_top_color_hover'] ) . '|' . strtolower( $custom_color['button_gradient_bottom_color_hover'] );

		$button_3d_styles = '';
		if ( ( '3d' === $button_type ) && $bevel_color ) {
			if ( 'small' === $button_size ) {
				$button_3d_add = 0;
			} elseif ( 'medium' === $button_size ) {
				$button_3d_add = 1;
			} elseif ( 'large' === $button_size ) {
				$button_3d_add = 2;
			} elseif ( 'xlarge' === $button_size ) {
				$button_3d_add = 3;
			}

			$button_3d_shadow_part_1 = 'inset 0px 1px 0px #fff,';
			$button_3d_shadow_part_2 = '0px ' . ( 2 + $button_3d_add ) . 'px 0px ' . $bevel_color . ',';
			$button_3d_shadow_part_3 = '1px ' . ( 4 + $button_3d_add ) . 'px ' . ( 4 + $button_3d_add ) . 'px 3px rgba(0,0,0,0.3)';
			if ( 'small' === $button_size ) {
				$button_3d_shadow_part_3 = str_replace( '3px', '2px', $button_3d_shadow_part_3 );
			}
			$button_3d_shadow = $button_3d_shadow_part_1 . $button_3d_shadow_part_2 . $button_3d_shadow_part_3;
			$button_3d_styles = '-webkit-box-shadow:' . $button_3d_shadow . ';-moz-box-shadow:' . $button_3d_shadow . ';box-shadow:' . $button_3d_shadow . ';';
		}

		$text_color_styles       = 'color:' . $accent_color . ';';
		$text_color_hover_styles = 'color:' . $accent_hover_color . ';';
		$general_styles          = 'border-color:' . $border_color . ';';
		$hover_styles            = 'border-color:' . $border_hover_color . ';';

		if ( $gradient_colors ) {
			// Checking for deprecated separators.
			$grad_colors = explode( '|', $gradient_colors );
			if ( 1 === count( $grad_colors ) || empty( $grad_colors[1] ) || $grad_colors[0] == $grad_colors[1] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				$gradient_styles = "background: {$grad_colors[0]};";
			} else {
				$gradient_styles =
				"background: {$grad_colors[0]};
				background-image: -webkit-gradient( linear, left bottom, left top, from( {$grad_colors[1]} ), to( {$grad_colors[0]} ) );
				background-image: -webkit-linear-gradient( bottom, {$grad_colors[1]}, {$grad_colors[0]} );
				background-image:   -moz-linear-gradient( bottom, {$grad_colors[1]}, {$grad_colors[0]} );
				background-image:     -o-linear-gradient( bottom, {$grad_colors[1]}, {$grad_colors[0]} );
				background-image: linear-gradient( to top, {$grad_colors[1]}, {$grad_colors[0]} );";
			}
		}
		if ( $gradient_hover_colors ) {
			// Checking for deprecated separators.
			$grad_colors = explode( '|', $gradient_hover_colors );
			if ( 1 === count( $grad_colors ) || empty( $grad_colors[1] ) || $grad_colors[0] == $grad_colors[1] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				$gradient_styles = "background: {$grad_colors[0]};";
			} else {
				$gradient_hover_styles =
				"background: {$grad_colors[0]};
				background-image: -webkit-gradient( linear, left bottom, left top, from( {$grad_colors[1]} ), to( {$grad_colors[0]} ) );
				background-image: -webkit-linear-gradient( bottom, {$grad_colors[1]}, {$grad_colors[0]} );
				background-image:   -moz-linear-gradient( bottom, {$grad_colors[1]}, {$grad_colors[0]} );
				background-image:     -o-linear-gradient( bottom, {$grad_colors[1]}, {$grad_colors[0]} );
				background-image: linear-gradient( to top, {$grad_colors[1]}, {$grad_colors[0]} );";
			}
		}
		$styles .= '<style type=\'text/css\'>.reading-box-container-' . $count . ' .button{' . $button_3d_styles . $text_color_styles . $general_styles . $gradient_styles . '} .reading-box-container-' . $count . ' .button:hover{' . $text_color_hover_styles . $hover_styles . $gradient_hover_styles . '}</style>';
	}
	return $styles;
}
add_filter( 'fusion_builder_tagline_box_style', 'fusion_builder_tagline_box_style', 10, 3 );

/**
 * Add dynamic styles which require FB.
 *
 * @since 5.1
 * @param string $css existing styling.
 * @return string style with additional content.
 */
function avada_add_fb_styling( $css ) {

	$fusion_settings = awb_get_fusion_settings();

	if ( class_exists( 'FusionBuilder' ) ) {
		$dynamic_css         = Fusion_Dynamic_CSS::get_instance();
		$dynamic_css_helpers = $dynamic_css->get_helpers();

		$css['global']['.fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-widget-content']['background-color'] = 'var(--tabs_bg_color)';
		$css['global']['.fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-nav ul li']['border-right-color']    = 'var(--tabs_bg_color)';
		if ( is_rtl() ) {
			$css['global']['.rtl .fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-nav ul li']['border-left-color'] = 'var(--tabs_bg_color)';
		}

		$css['global']['.fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-clean .fusion-tabs-nav ul']['border']                = '1px solid var(--tabs_border_color)';
		$css['global']['.fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-clean .fusion-tabs-nav ul li']['border-right-color'] = 'var(--tabs_border_color)';
		if ( is_rtl() ) {
			$css['global']['.rtl .fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-clean .fusion-tabs-nav ul li']['border-left-color'] = 'var(--tabs_border_color)';
		}

		$css['global']['.fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-nav ul li a']['border-top-color'] = 'var(--tabs_inactive_color)';
		$elements = [
			'.fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-nav ul li a',
			'.fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-widget-content .fusion-date-box',
		];
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background'] = 'var(--tabs_inactive_color)';

		$css['global']['.fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-nav ul li a:hover']['background']       = 'var(--tabs_bg_color)';
		$css['global']['.fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-nav ul li a:hover']['border-top-color'] = 'var(--tabs_bg_color)';
		$css['global']['.fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-nav ul li.active a']['background']      = 'var(--tabs_bg_color)';

		$elements = [
			'.fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-classic',
			'.fusion-content-widget-area .fusion-tabs-widget .fusion-tabs-widget-content .fusion-tabs-widget-items li',
		];
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = 'var(--tabs_border_color)';

		$css['global']['.fusion-secondary-menu .fusion-menu-cart-item img']['border-color'] = 'var(--sep_color)';
		if ( class_exists( 'WooCommerce' ) ) {
			$elements = [
				'.product .product-border',
			];
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = 'var(--title_border_color)';
			$css['global']['.product-images .crossfade-images']['background']            = 'var(--title_border_color)';
			$elements = [
				'.fusion-menu-cart-item img',
				'.fusion-body .quantity',
				'.fusion-body .quantity .minus',
				'.fusion-body .quantity .plus',
				'.woocommerce form.checkout #customer_details .col-1',
				'.woocommerce form.checkout #customer_details .col-2',
			];
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = 'var(--sep_color)';
		}

		$elements = [ '.review blockquote q', '.post-content blockquote', '.fusion-body blockquote', '.checkout .payment_methods .payment_box' ];
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = 'var(--testimonial_bg_color)';

		$elements = [ '.review blockquote q' ];
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = 'var(--testimonial_text_color)';

		$css['global']['.fontawesome-icon']['color'] = 'var(--icon_color)';

		$elements = [
			'.fontawesome-icon.circle-yes',
		];
		if ( class_exists( 'WooCommerce' ) ) {
			$elements[] = '.woocommerce .social-share li a i';
			$elements[] = '.avada-myaccount-data .digital-downloads li:before';
			$elements[] = '.avada-myaccount-data .digital-downloads li:after';
			$elements[] = '.avada-thank-you .order_details li:before';
			$elements[] = '.avada-thank-you .order_details li:after';
		}
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color']            = 'var(--icon_color)';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = 'var(--icon_circle_color)';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color']     = 'var(--icon_border_color)';

		$icon_border_radius = Avada()->settings->get( 'icon_border_radius' );
		if ( is_array( $icon_border_radius ) ) {
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-radius'] = implode( ' ', $icon_border_radius );
		}

		$css['global']['.fontawesome-icon:hover']['color'] = 'var(--icon_color_hover)';

		$elements = [
			'.fontawesome-icon.circle-yes:hover',
		];
		if ( class_exists( 'WooCommerce' ) ) {
			$elements[] = '.woocommerce .social-share li a:hover i';
		}
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color']            = 'var(--icon_color_hover)';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = 'var(--icon_circle_color_hover)';
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color']     = 'var(--icon_border_color_hover)';

		$elements = [
			'.search-page-search-form',
			'.ls-avada',
			'.avada-skin-rev',
			'.es-carousel-wrapper.fusion-carousel-small .es-carousel ul li img',
			'.progress-bar',
			'#small-nav',
			'.fusion-filters',
			'.single-navigation',
			'.project-content .project-info .project-info-box',
			'.fusion-project-details-tb .project-info .project-info-box',
			'.post .fusion-meta-info',
			'.fusion-counters-box .fusion-counter-box .counter-box-border',
			'tr td',
			'.table',
			'.table > thead > tr > th',
			'.table > tbody > tr > th',
			'.table > tfoot > tr > th',
			'.table > thead > tr > td',
			'.table > tbody > tr > td',
			'.table > tfoot > tr > td',
			'.table-1 table',
			'.table-1 table th',
			'.table-1 tr td',
			'.tkt-slctr-tbl-wrap-dv table',
			'.tkt-slctr-tbl-wrap-dv tr td',
			'.table-2 table thead',
			'.table-2 tr td',
			'.fusion-content-widget-area .widget li a',
			'.fusion-content-widget-area .widget li a:before',
			'.fusion-content-widget-area .widget .recentcomments',
			'.fusion-content-widget-area .widget_categories li',
			'.commentlist .the-comment',
			'.side-nav',
			'#wrapper .side-nav li a',
			'h5.toggle.active + .toggle-content',
			'#wrapper .side-nav li.current_page_item li a',
			'.tabs-vertical .tabset',
			'.tabs-vertical .tabs-container .tab_content',
			'.fusion-tabs.vertical-tabs.clean .nav-tabs li .tab-link',
			'.pagination a.inactive',
			'.fusion-hide-pagination-text .pagination-prev',
			'.fusion-hide-pagination-text .pagination-next',
			'.fusion-pagination .page-numbers',
			'.page-links a',
			'.fusion-author .fusion-author-social',
			'.side-nav li a',
			'.price_slider_wrapper',
			'.tagcloud a',
			'.fusion-content-widget-area .widget_nav_menu li',
			'.fusion-content-widget-area .widget_meta li',
			'.fusion-content-widget-area .widget_recent_entries li',
			'.fusion-content-widget-area .widget_archive li',
			'.fusion-content-widget-area .widget_pages li',
			'.fusion-content-widget-area .widget_links li',
			'.chzn-container-single .chzn-single',
			'.chzn-container-single .chzn-single div',
			'.chzn-drop',
			'.input-radio',
			'.panel.entry-content',
			'#reviews li .comment-text',
			'.fusion-author-widget .fusion-author-widget-separator .fusion-author-widget-sep',
		];
		if ( is_rtl() ) {
			$elements[] = '.rtl .side-nav';
		}
		if ( class_exists( 'bbPress' ) ) {
			$elements[] = '#bbpress-forums .bbp-pagination .bbp-pagination-links a.inactive';
			$elements[] = '#bbpress-forums .bbp-topic-pagination .page-numbers';
			$elements[] = '.widget.widget.widget_display_replies ul li';
			$elements[] = '.widget.widget_display_topics ul li';
			$elements[] = '.widget.widget_display_views ul li';
			$elements[] = '.widget.widget_display_stats dt';
			$elements[] = '.widget.widget_display_stats dd';
			$elements[] = '.bbp-pagination-links span.dots';
			$elements[] = '.fusion-hide-pagination-text #bbpress-forums .bbp-pagination .bbp-pagination-links .pagination-prev';
			$elements[] = '.fusion-hide-pagination-text #bbpress-forums .bbp-pagination .bbp-pagination-links .pagination-next';
		}
		if ( class_exists( 'WooCommerce' ) ) {
			$elements[] = '#customer_login_box';
			$elements[] = '#customer_login .col-1';
			$elements[] = '#customer_login .col-2';
			$elements[] = '#customer_login h2';
			$elements[] = '.fusion-body .avada-myaccount-user';
			$elements[] = '.fusion-body .avada-myaccount-user .avada-myaccount-user-column';
			$elements[] = '.woocommerce-pagination .page-numbers';
			$elements[] = '.fusion-body.woo-tabs-horizontal .woocommerce-tabs > .entry-content';
			$elements[] = '.woo-tabs-horizontal .woocommerce-tabs > .tabs';
			$elements[] = '.woo-tabs-horizontal .woocommerce-tabs > .wc-tab';
			$elements[] = '.fusion-body .woocommerce-side-nav li a';
			$elements[] = '.fusion-body .woocommerce-content-box';
			$elements[] = '.fusion-body .woocommerce-content-box h2';
			$elements[] = '.fusion-body .woocommerce .address h4';
			$elements[] = '.woo-tabs-horizontal .woocommerce-MyAccount-navigation';
			$elements[] = '.woo-tabs-horizontal .woocommerce .woocommerce-MyAccount-navigation > ul .is-active';
			$elements[] = '.fusion-body .woocommerce-MyAccount-navigation ul li a';
			$elements[] = '.fusion-body .woocommerce-MyAccount-content';
			$elements[] = '.fusion-body .woocommerce-MyAccount-content h2';
			$elements[] = '.fusion-body .woocommerce-MyAccount-content h3';
			$elements[] = '.fusion-body .woocommerce-tabs .tabs li a';
			$elements[] = '.fusion-body .woocommerce .social-share';
			$elements[] = '.fusion-body .woocommerce .social-share li';
			$elements[] = '.fusion-body .woocommerce-success-message';
			$elements[] = '.fusion-body .woocommerce .cross-sells';
			$elements[] = '.fusion-body .woocommerce-info';
			$elements[] = '.fusion-body .woocommerce-message';
			$elements[] = '.fusion-body .woocommerce .checkout #customer_details .col-1';
			$elements[] = '.fusion-body .woocommerce .checkout #customer_details .col-2';
			$elements[] = '.woo-tabs-horizontal .woocommerce .woocommerce-checkout-nav .is-active';
			$elements[] = '.fusion-body .woocommerce .checkout h3';
			$elements[] = '.fusion-body .woocommerce .cross-sells h2';
			$elements[] = '.fusion-body .woocommerce .addresses .title';
			$elements[] = '.fusion-content-widget-area .widget_product_categories li';
			$elements[] = '.widget_product_categories li';
			$elements[] = '.widget_layered_nav li';
			$elements[] = '.fusion-content-widget-area .product_list_widget li';
			$elements[] = '.fusion-content-widget-area .widget_layered_nav li';
			$elements[] = '.fusion-body .my_account_orders tr';
			$elements[] = '.side-nav-left .side-nav';
			$elements[] = '.fusion-body .shop_table tr';
			$elements[] = '.fusion-body .cart_totals .total';
			$elements[] = '.fusion-body .checkout .shop_table tfoot';
			$elements[] = '.fusion-body .shop_attributes tr';
			$elements[] = '.fusion-body .cart-totals-buttons';
			$elements[] = '.fusion-body .cart_totals';
			$elements[] = '.fusion-body .woocommerce-shipping-calculator';
			$elements[] = '.fusion-body .coupon';
			$elements[] = '.fusion-body .cart_totals h2';
			$elements[] = '.fusion-body .woocommerce-shipping-calculator h2';
			$elements[] = '.fusion-body .coupon h2';
			$elements[] = '.fusion-body .order-total';
			$elements[] = '.fusion-body .woocommerce .cart-empty';
			$elements[] = '.fusion-body .woocommerce .return-to-shop';
			$elements[] = '.fusion-body .avada-order-details .shop_table.order_details tfoot';
			$elements[] = '#final-order-details .mini-order-details tr:last-child';
			$elements[] = '.fusion-body .order-info';
			$elements[] = '.woocommerce .social-share';
			$elements[] = '.woocommerce .social-share li';
			$elements[] = '.fusion-body .quantity .minus, .fusion-body .quantity .qty';
			if ( is_rtl() ) {
				$elements[] = '.rtl .woocommerce .social-share li';
			}
		}

		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = 'var(--sep_color)';

		$css['global']['.price_slider_wrapper .ui-widget-content']['background-color'] = 'var(--sep_color)';
		if ( class_exists( 'GFForms' ) ) {
			$css['global']['.gform_wrapper .gsection']['border-bottom'] = '1px dotted var(--sep_color)';
		}

		if ( class_exists( 'Tribe__Events__Main' ) ) {
			$elements = [
				'.tribe-countdown-timer',
				'.tribe-countdown-text',
			];
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = 'var(--countdown_background_color)';
			$elements = [
				'.tribe-countdown-timer .tribe-countdown-number',
			];
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = 'var(--countdown_counter_box_color)';
			$elements = [
				'.tribe-countdown-timer .tribe-countdown-number .fusion-tribe-counterdown-over',
				'.tribe-countdown-timer .tribe-countdown-number .tribe-countdown-under',
			];
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = 'var(--countdown_counter_text_color)';
			$elements = [
				'.tribe-events-countdown-widget .tribe-countdown-text, .tribe-events-countdown-widget .tribe-countdown-text a',
				'#slidingbar-area .tribe-events-countdown-widget .tribe-countdown-text, #slidingbar-area .tribe-events-countdown-widget .tribe-countdown-text a',
				'.tribe-events-countdown-widget .tribe-countdown-text, .tribe-events-countdown-widget .tribe-countdown-text a:hover',
				'#slidingbar-area .tribe-events-countdown-widget .tribe-countdown-text, #slidingbar-area .tribe-events-countdown-widget .tribe-countdown-text a:hover',
			];
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = 'var(--countdown_heading_text_color)';
		}
	}

	return $css;
}
add_filter( 'fusion_dynamic_css_array', 'avada_add_fb_styling' );

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

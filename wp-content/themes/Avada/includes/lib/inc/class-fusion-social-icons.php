<?php
/**
 * Social Icons Class
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      4.0.0
 */

/**
 * Social Icons Class
 *
 * @since 4.0.0
 */
class Fusion_Social_Icons extends Fusion_Social_Icon {

	/**
	 * Renders all social icons not belonging to shortcodes.
	 *
	 * @since 3.5.0
	 * @param  array $args Holding all necessarry data for social icons.
	 * @return string  The HTML mark up for social icons, incl. wrapping container.
	 */
	public function render_social_icons( $args ) {

		parent::$args = $args;

		$html  = '';
		$icons = '';
		// Get the social networks setting.
		$social_networks = fusion_library()->get_option( 'social_media_icons' );
		// Get a list of all the available social networks.
		$social_networks_full_array = Fusion_Data::fusion_social_icons( true, true );

		// Count how many social icons we have.
		$count = is_array( $social_networks ) ? count( $social_networks ) : 0;

		$use_brand_colors = false;
		if ( isset( parent::$args['position'] ) ) {
			if ( 'footer' === parent::$args['position'] ) {
				if ( 'brand' === fusion_library()->get_option( 'footer_social_links_color_type' ) ) {
					$use_brand_colors = true;
				}
			} else {
				if ( 'brand' === fusion_library()->get_option( 'header_social_links_color_type' ) ) {
					$use_brand_colors = true;
				}
			}
		}

		// Check that we have social networks defined before proceeding.
		if ( 0 < $count && ! empty( $social_networks ) && isset( $social_networks['url'] ) && ! empty( $social_networks['url'] ) ) {
			$social_networks_count = count( $social_networks['url'] );
			for ( $i = 0; $i <= $social_networks_count - 1; $i++ ) {
				// Get the icon's arguments.
				$icon                 = ( isset( $social_networks['icon'][ $i ] ) ) ? str_replace( '_link', '', $social_networks['icon'][ $i ] ) : false;
				$url                  = ( isset( $social_networks['url'][ $i ] ) && ! empty( $social_networks['url'][ $i ] ) ) ? $social_networks['url'][ $i ] : false;
				$header_box_color     = ( isset( $social_networks['header_box_color'][ $i ] ) && ! empty( $social_networks['header_box_color'][ $i ] ) ) ? $social_networks['header_box_color'][ $i ] : false;
				$footer_box_color     = ( isset( $social_networks['footer_box_color'][ $i ] ) && ! empty( $social_networks['footer_box_color'][ $i ] ) ) ? $social_networks['footer_box_color'][ $i ] : false;
				$custom_title         = ( isset( $social_networks['custom_title'][ $i ] ) && ! empty( $social_networks['custom_title'][ $i ] ) ) ? $social_networks['custom_title'][ $i ] : '';
				$custom_source        = ( isset( $social_networks['custom_source'][ $i ] ) && isset( $social_networks['custom_source'][ $i ]['url'] ) && ! empty( $social_networks['custom_source'][ $i ]['url'] ) ) ? $social_networks['custom_source'][ $i ]['url'] : '';
				$custom_source_height = ( isset( $social_networks['custom_source'][ $i ] ) && isset( $social_networks['custom_source'][ $i ]['height'] ) && ! empty( $social_networks['custom_source'][ $i ]['height'] ) ) ? $social_networks['custom_source'][ $i ]['height'] : '';
				$custom_source_width  = ( isset( $social_networks['custom_source'][ $i ] ) && isset( $social_networks['custom_source'][ $i ]['width'] ) && ! empty( $social_networks['custom_source'][ $i ]['width'] ) ) ? $social_networks['custom_source'][ $i ]['width'] : '';
				$custom_icon          = isset( $social_networks['icon_mark'][ $i ] ) ? $social_networks['icon_mark'][ $i ] : false;
				if ( ! isset( $social_networks_full_array[ $icon ] ) && 'custom' !== $icon ) {
					continue;
				}

				// Make sure we have a URL & an icon defined.
				if ( $icon && $url ) {
					$icon_args = [
						'icon'        => $icon,
						'custom_icon' => $custom_icon,
						'url'         => $url,
					];

					if ( $use_brand_colors ) {
						$brand_color             = $social_networks_full_array[ $icon ]['color'];
						$icon_args['icon_color'] = ( parent::$args['icon_boxed'] ) ? '#ffffff' : $social_networks_full_array[ $icon ]['color'];
						$icon_args['box_color']  = ( parent::$args['icon_boxed'] ) ? $social_networks_full_array[ $icon ]['color'] : 'transparent';
					}

					// Check if are on the last social icon.
					$icon_args['last'] = ( $count === $i );

					// Custom icons.
					if ( 'custom' === $icon && ( ! empty( $custom_source ) || $custom_icon ) ) {
						$icon_args['custom_source']        = $custom_source;
						$icon_args['custom_source_height'] = $custom_source_height;
						$icon_args['custom_source_width']  = $custom_source_width;
						$icon_args['custom_title']         = $custom_title;
					}

					$icons .= parent::get_markup( $icon_args );

				}
			}
		}

		if ( ! empty( $icons ) ) {
			$attr = [
				'class' => 'fusion-social-networks',
			];
			if ( parent::$args['icon_boxed'] ) {
				$attr['class'] .= ' boxed-icons';
			}
			$html = '<div ' . fusion_attr( 'social-icons-class-social-networks', $attr ) . '><div ' . fusion_attr( 'fusion-social-networks-wrapper' ) . '>' . $icons;
			if ( isset( parent::$args['position'] ) && ( 'header' === parent::$args['position'] || 'footer' === parent::$args['position'] ) ) {
				$html .= '</div></div>';
			} else {
				$html .= '<div class="fusion-clearfix"></div></div></div>';
			}
		}

		return apply_filters( 'fusion_social_icons_html', $html, $args );

	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

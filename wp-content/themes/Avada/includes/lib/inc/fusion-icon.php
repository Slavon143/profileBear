<?php
/**
 * Icon picker methods.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * Icons handler.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */
class Fusion_Icon {
	/**
	 * Associative Array of Icon Data.
	 *
	 * @access private
	 * @since 1.0
	 * @var array
	 */
	private $data = [];

	/**
	 * Iterator.
	 *
	 * @access private
	 * @since 1.0
	 * @var object Iterator
	 */
	private $iterator;

	/**
	 * Constructor.
	 *
	 * @param object $iterator The iterator class.
	 * @param string $class    Icon css class.
	 * @param string $unicode  Unicode character reference.
	 * @param string $subset   The FA subset.
	 */
	public function __construct( $iterator, $class, $unicode, $subset ) {

		$this->iterator = $iterator;

		// Set Basic Data.
		$this->data['class']   = $class;
		$this->data['unicode'] = $unicode;
		$this->data['subset']  = $subset;
	}

	/**
	 * Simple getter.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $key The key we'll be looking for in the array.
	 */
	public function __get( $key ) {

		if ( strtolower( $key ) === 'name' ) {
			return $this->get_name( $this->__get( 'class' ) );
		}

		if ( is_array( $this->data ) && isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}
	}

	/**
	 * Gets the icon name.
	 *
	 * @access private
	 * @since 1.0
	 * @param string $class The icon class.
	 * @return string
	 */
	private function get_name( $class ) {

		// Remove Prefix.
		$name = substr( $class, strlen( $this->iterator->getPrefix() ) + 1 );

		// Convert Hyphens to Spaces.
		$name = str_replace( '-', ' ', $name );

		// Capitalize Words.
		$name = ucwords( $name );

		// Show Directional Variants in Parenthesis.
		$directions        = [
			'/up$/i',
			'/down$/i',
			'/left$/i',
			'/right$/i',
		];
		$directions_format = [ '(Up)', '(Down)', '(Left)', '(Right)' ];
		$name              = preg_replace( $directions, $directions_format, $name );

		// Use Word "Outlined" in Place of "O".
		$outlined_variants = [ '/\so$/i', '/\so\s/i' ];
		$name              = preg_replace( $outlined_variants, ' Outlined ', $name );

		// Remove Trailing Characters.
		$name = trim( $name );

		return $name;
	}
}

if ( ! function_exists( 'fusion_get_icons_array' ) ) {
	/**
	 * Get an array of available icons.
	 *
	 * @return array
	 */
	function fusion_get_icons_array() {
		$path = Fusion_Font_Awesome::is_fa_pro_enabled() ? '/assets/fonts/fontawesome/icons_pro.php' : '/assets/fonts/fontawesome/icons_free.php';

		return include FUSION_LIBRARY_PATH . $path;
	}
}

if ( ! function_exists( 'fusion_font_awesome_name_handler' ) ) {
	/**
	 * Tweaks the icon names.
	 *
	 * @static
	 * @access public
	 * @since 1.0
	 * @param string $icon The icon.
	 * @return string
	 */
	function fusion_font_awesome_name_handler( $icon ) {

		$fa_icon = '';
		if ( isset( $icon ) && ! empty( $icon ) ) {

			// Custom icon is used so we need to remove our prefix.
			if ( 'fusion-prefix-' === substr( $icon, 0, 14 ) ) {
				return str_replace( 'fusion-prefix-', '', $icon );
			}

			// AWB icon is used.
			if ( 'awb-icon-' === substr( $icon, 0, 9 ) ) {
				return $icon;
			}

			// FA icon, but we need to handle BC.
			$fa_icon = $icon;
			if ( 'icon-' === substr( $icon, 0, 5 ) || 'fa-' !== substr( $icon, 0, 3 ) ) {
				$icon      = str_replace( 'icon-', 'fa-', $icon );
				$fa_icon   = $icon;
				$old_icons = Fusion_Data::old_icons();

				if ( array_key_exists( str_replace( 'fa-', '', $icon ), $old_icons ) ) {
					$fa_icon = 'fa-' . $old_icons[ str_replace( 'fa-', '', $icon ) ];
				} elseif ( 'fa-' !== substr( $icon, 0, 3 ) ) {
					$fa_icon = 'fa-' . $icon;
				}
			} elseif ( 'fa-' !== substr( $icon, 0, 3 ) ) {
				$fa_icon = 'fa-' . $icon;
			}

			// We add fa-fw class in menu walker, for side headers.
			if ( false === strpos( str_replace( ' fa-fw', '', trim( $fa_icon ) ), ' ' ) ) {
				$fa_icon = ' fa ' . $fa_icon;
			}
		}

		return $fa_icon;
	}
}

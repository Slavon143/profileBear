<?php
/**
 * Handle dynamic-CSS from Fusion Elements.
 *
 * @package fusion-builder
 * @since 2.0
 */

/**
 * Handle dynamic-CSS from Fusion Elements.
 *
 * @since 2.0
 */
class Fusion_Elements_Dynamic_CSS {

	/**
	 * An array of styles.
	 *
	 * @static
	 * @access private
	 * @since 2.0
	 * @var array
	 */
	private static $styles_array = [];

	/**
	 * The class contructor.
	 * Add all actions/filters here.
	 *
	 * @access public
	 * @since 2.0
	 */
	public function __construct() {
		add_filter( 'fusion_dynamic_css_final', [ $this, 'append_compiled_styles' ], 1020 );
	}

	/**
	 * Appends the compiled styles to our final dynamic-CSS.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $css Existing styles.
	 * @return string     Existing styles with the elements dynamic styles appended.
	 */
	public function append_compiled_styles( $css ) {
		return $css . $this->compile_css();
	}

	/**
	 * Compiles the CSS array to a string.
	 *
	 * @access private
	 * @since 2.0
	 * @return string
	 */
	private function compile_css() {
		$helpers = new Fusion_Dynamic_CSS_Helpers();
		return $helpers->parser( self::$styles_array, true );
	}

	/**
	 * Add styles to the $styles_array property.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param array $styles An array of styles to add.
	 * @return void
	 */
	public static function add_styles_to_array( $styles ) {
		self::$styles_array = array_merge_recursive( self::$styles_array, $styles );
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

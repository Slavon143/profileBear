<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( ! class_exists( 'FusionSC_FusionInline' ) ) {
	/**
	 * Shortcode class.
	 *
	 * @since 1.0
	 */
	class FusionSC_FusionInline extends Fusion_Element {

		/**
		 * The one, true instance of this object.
		 *
		 * @static
		 * @access private
		 * @since 1.0
		 * @var object
		 */
		public static $instance;

		/**
		 * Constructor.
		 *
		 * @access public
		 * @since 1.0
		 */
		public function __construct() {
			parent::__construct();

			add_shortcode( 'fusion_builder_inline', [ $this, 'render' ] );
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @static
		 * @access public
		 * @since 2.2
		 */
		public static function get_instance() {

			// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
			if ( null === self::$instance ) {
				self::$instance = new FusionSC_FusionInline();
			}
			return self::$instance;
		}

		/**
		 * Render the shortcode
		 *
		 * @access public
		 * @since 1.0
		 * @param  array  $args    Shortcode parameters.
		 * @param  string $content Content between shortcode.
		 * @return string          HTML output.
		 */
		public function render( $args, $content = '' ) {
			$html = '';

			add_filter( 'fusion_container_is_flex', '__return_false' );
			$html = '<div class="fusion-inline-elements">' . do_shortcode( $content ) . '</div>';
			remove_filter( 'fusion_container_is_flex', '__return_false' );

			return $html;
		}
	}
}

/**
 * Instantiates the container class.
 *
 * @return object FusionSC_FusionInline
 */
function fusion_builder_inline() { // phpcs:ignore WordPress.NamingConventions
	return FusionSC_FusionInline::get_instance();
}

// Instantiate container.
fusion_builder_inline();


/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_inline() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_FusionInline',
			[
				'name'              => esc_attr__( 'Fusion Inline', 'fusion-builder' ),
				'shortcode'         => 'fusion_builder_inline',
				'hide_from_builder' => true,
				'params'            => [],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_inline' );

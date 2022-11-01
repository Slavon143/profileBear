<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( ! class_exists( 'FusionSC_Column' ) ) {
	/**
	 * Shortcode class.
	 *
	 * @since 1.0
	 */
	class FusionSC_Column extends Fusion_Column_Element {

		/**
		 * Constructor.
		 *
		 * @access public
		 * @since 1.0
		 */
		public function __construct() {
			$shortcode         = 'fusion_builder_column';
			$shortcode_attr_id = 'fusion-column';
			$classname         = 'fusion-builder-column';
			$content_filter    = 'fusion_element_column_content';
			parent::__construct( $shortcode, $shortcode_attr_id, $classname, $content_filter );
		}
	}
}

/**
 * Instantiates the container class.
 *
 * @return object FusionSC_Column
 */
function fusion_builder_column() { // phpcs:ignore WordPress.NamingConventions
	return FusionSC_Column::get_instance();
}

// Instantiate container.
fusion_builder_column();


/**
 * Map column shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_column() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Column',
			[
				'name'              => esc_attr__( 'Column', 'fusion-builder' ),
				'shortcode'         => 'fusion_builder_column',
				'hide_from_builder' => true,
				'help_url'          => 'https://theme-fusion.com/documentation/avada/elements/column-element/',
				'params'            => fusion_get_column_params(),
				'subparam_map'      => fusion_get_column_subparam_map(),
			]
		)
	);
}
add_action( 'wp_loaded', 'fusion_element_column' );

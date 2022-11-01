<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( ! class_exists( 'FusionSC_Row' ) ) {
	/**
	 * Shortcode class.
	 *
	 * @since 3.0
	 */
	class FusionSC_Row extends Fusion_Row_Element {

		/**
		 * Constructor.
		 *
		 * @access public
		 * @since 3.0
		 */
		public function __construct() {
			$shortcode         = 'fusion_builder_row';
			$shortcode_attr_id = 'row';
			$classname         = 'fusion-row';
			$content_filter    = 'fusion_element_row_content';
			parent::__construct( $shortcode, $shortcode_attr_id, $classname, $content_filter );
		}

	}
}

new FusionSC_Row();

/**
 * Map Row shortcode to Avada Builder
 */
function fusion_element_row() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Row',
			[
				'name'              => esc_attr__( 'Row', 'fusion-builder' ),
				'shortcode'         => 'fusion_builder_row',
				'hide_from_builder' => true,
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_row' );

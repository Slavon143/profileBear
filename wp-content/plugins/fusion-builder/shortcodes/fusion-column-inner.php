<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( ! class_exists( 'FusionSC_ColumnInner' ) ) {
	/**
	 * Shortcode class.
	 *
	 * @since 1.0
	 */
	class FusionSC_ColumnInner extends Fusion_Column_Element {

		/**
		 * Constructor.
		 *
		 * @access public
		 * @since 1.0
		 */
		public function __construct() {
			$shortcode         = 'fusion_builder_column_inner';
			$shortcode_attr_id = 'fusion-column-inner';
			$classname         = 'fusion-builder-nested-column';
			$content_filter    = 'fusion_element_column_inner_content';
			parent::__construct( $shortcode, $shortcode_attr_id, $classname, $content_filter );
		}
	}
}

/**
 * Instantiates the column class.
 *
 * @return object FusionSC_ColumnInner
 */
function fusion_builder_column_inner() { // phpcs:ignore WordPress.NamingConventions
	return FusionSC_ColumnInner::get_instance();
}

// Instantiate nested column.
fusion_builder_column_inner();

/**
 * Map Column shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_column_inner() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_ColumnInner',
			[
				'name'              => esc_attr__( 'Nested Column', 'fusion-builder' ),
				'shortcode'         => 'fusion_builder_column_inner',
				'hide_from_builder' => true,
				'params'            => fusion_get_column_params(),
				'subparam_map'      => fusion_get_column_subparam_map(),
			]
		)
	);
}
add_action( 'wp_loaded', 'fusion_element_column_inner' );

<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( ! class_exists( 'Fusion_Row_Element' ) ) {
	/**
	 * Shortcode class.
	 *
	 * @since 1.0
	 */
	class Fusion_Row_Element extends Fusion_Element {
		/**
		 * An array of the shortcode arguments.
		 *
		 * @access protected
		 * @since 3.0
		 * @var array
		 */
		protected $args;

		/**
		 * Constructor.
		 *
		 * @access public
		 * @param string $shortcode         The shortcode we want to add.
		 * @param string $shortcode_attr_id The shortcode attribute-ID.
		 * @param string $classname         The shortcode's CSS classname.
		 * @param string $content_filter    The filter-name we want to apply using apply_filters.
		 * @since 3.0
		 */
		public function __construct( $shortcode, $shortcode_attr_id, $classname, $content_filter ) {
			parent::__construct();
			add_shortcode( $shortcode, [ $this, 'render' ] );

			$this->shortcode_attr_id   = $shortcode_attr_id;
			$this->shortcode_classname = $classname;
			$this->shortcode_name      = $shortcode;
			$this->content_filter      = $content_filter;

			add_filter( "fusion_attr_{$this->shortcode_attr_id}-shortcode", [ $this, 'attr' ] );
		}

		/**
		 * Gets the default values.
		 *
		 * @static
		 * @access public
		 * @since 3.0
		 * @return array
		 */
		public static function get_element_defaults() {
			return [
				'id'    => '',
				'class' => '',
			];
		}

		/**
		 * Used to set any other variables for use on front-end editor template.
		 *
		 * @static
		 * @access public
		 * @since 3.0
		 * @return array
		 */
		public static function get_element_extras() {
			$fusion_settings = awb_get_fusion_settings();
			return [
				'site_width' => $fusion_settings->get( 'site_width' ),
			];
		}

		/**
		 * Maps settings to extra variables.
		 *
		 * @static
		 * @access public
		 * @since 3.0
		 * @return array
		 */
		public static function settings_to_extras() {

			return [
				'site_width' => 'site_width',
			];
		}

		/**
		 * Render the shortcode
		 *
		 * @access public
		 * @since 3.0
		 * @param  array  $atts    Shortcode parameters.
		 * @param  string $content Content between shortcode.
		 * @return string          HTML output.
		 */
		public function render( $atts, $content = '' ) {
			$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $atts, $this->shortcode_name );

			$html = '<div ' . FusionBuilder::attributes( $this->shortcode_attr_id . '-shortcode' ) . '>' . do_shortcode( fusion_builder_fix_shortcodes( $content ) ) . '</div>';

			$this->on_render();

			return apply_filters( $this->content_filter, $html, $atts );
		}

		/**
		 * Row attributes.
		 *
		 * @access public
		 * @since 3.0
		 * @return array
		 */
		public function attr() {

			$fusion_settings = awb_get_fusion_settings();

			$attr = [
				'class' => 'fusion-builder-row ' . $this->shortcode_classname,
				'style' => '',
			];

			// Get information about parent container.
			$container_args                 = false;
			$this->args['flex']             = false;
			$this->args['nested_container'] = 'fusion_builder_row_inner' === $this->shortcode_name;

			if ( function_exists( 'fusion_builder_container' ) ) {
				$container_args               = fusion_builder_container()->args;
				$this->args['flex']           = fusion_builder_container()->is_flex();
				$this->args['column_spacing'] = fusion_library()->sanitize->size( fusion_builder_container()->get_column_spacing(), 'px' );

				// Not an inner row, check if we are nested though.
				if ( ! $this->args['nested_container'] ) {
					$nesting_depth = fusion_builder_container()->get_nesting_depth();
					$override_name = function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_current_override_name() : false;

					$this->args['nested_container'] = ( 1 === $nesting_depth && 'content' === $override_name ) ? false : fusion_builder_container()->is_nested();
				}
			}

			// We have container arguments, set styling to row.
			if ( $container_args ) {
				if ( $this->args['flex'] ) {
					$attr['class'] .= ' fusion-flex-align-items-' . $container_args['flex_align_items'];
					if ( 'stretch' !== $container_args['align_content'] ) {
						$attr['class'] .= ' fusion-flex-align-content-' . $container_args['align_content'];
					}
					if ( 'flex-start' !== $container_args['flex_justify_content'] ) {
						$attr['class'] .= ' fusion-flex-justify-content-' . $container_args['flex_justify_content'];
					}
					// If this is not nested row, then increase width with negative margins to match column margin.
					$width = 'yes' === $container_args['hundred_percent'] || $this->args['nested_container'] ? '100%' : $fusion_settings->get( 'site_width' );

					// In case Global Options are still not set.
					if ( '' === $width ) {
						$width = '1200px';
					}

					$column_spacing_unit = Fusion_Sanitize::get_unit( $this->args['column_spacing'] );

					if ( '%' === $column_spacing_unit ) {
						$column_spacing_value = Fusion_Sanitize::number( $this->args['column_spacing'] );
						$width_value          = Fusion_Sanitize::number( $width );
						$width_unit           = Fusion_Sanitize::get_unit( $width );

						$width = ( $width_value * ( 1 + $column_spacing_value / 100 ) ) . $width_unit;
					} else {
						$width = 'calc( ' . $width . ' + ' . $this->args['column_spacing'] . ' )';
					}

					if ( 'no' === $container_args['hundred_percent'] && ! $this->args['nested_container'] ) {
						$attr['style'] .= 'max-width:' . $width . ';';
					} else {
						$attr['style'] .= 'width:' . $width . ' !important;';
						$attr['style'] .= 'max-width:' . $width . ' !important;';
					}

					$attr['style'] .= 'margin-left: calc(-' . $this->args['column_spacing'] . ' / 2 );';
					$attr['style'] .= 'margin-right: calc(-' . $this->args['column_spacing'] . ' / 2 );';
				}
			}

			if ( '' !== $this->args['id'] ) {
				$attr['id'] = $this->args['id'];
			}

			if ( '' !== $this->args['class'] ) {
				$attr['class'] .= ' ' . $this->args['class'];
			}

			return $attr;
		}
	}
}

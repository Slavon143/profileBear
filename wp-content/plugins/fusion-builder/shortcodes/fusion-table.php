<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_table' ) ) {

	if ( ! class_exists( 'FusionSC_FusionTable' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_FusionTable extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_shortcode( 'fusion_table', [ $this, 'render' ] );

				add_filter( 'fusion_attr_table-element', [ $this, 'attr' ] );

				add_filter( 'fusion_table_content', 'shortcode_unautop' );
				add_filter( 'fusion_table_content', 'do_shortcode' );
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
				$fusion_settings = awb_get_fusion_settings();

				$defaults = FusionBuilder::set_shortcode_defaults(
					[
						'animation_type'       => '',
						'animation_direction'  => 'left',
						'animation_speed'      => '',
						'animation_offset'     => $fusion_settings->get( 'animation_offset' ),
						'class'                => '',
						'fusion_table_columns' => '',
						'fusion_table_rows'    => '',
						'fusion_table_type'    => '',
						'margin_top'           => '',
						'margin_right'         => '',
						'margin_bottom'        => '',
						'margin_left'          => '',
						'hide_on_mobile'       => fusion_builder_default_visibility( 'string' ),
						'id'                   => '',
					],
					$args,
					'fusion_table'
				);
				$defaults = apply_filters( 'fusion_builder_default_args', $defaults, 'fusion_table_element', $args );

				$this->args = $defaults;

				$this->args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_bottom'], 'px' );
				$this->args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_left'], 'px' );
				$this->args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_right'], 'px' );
				$this->args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_top'], 'px' );

				$this->args['content'] = $content;

				if ( $this->args['fusion_table_type'] ) {
					$replacement = preg_replace( '/<div (.*?)">/', '<div ' . FusionBuilder::attributes( 'table-element' ) . '>', $content );

					$content = is_string( $replacement ) ? $replacement : $content;
				}

				$content = apply_filters( 'fusion_table_content', fusion_builder_fix_shortcodes( $content ) );

				$this->on_render();

				return apply_filters( 'fusion_element_table_content', $content, $args );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/table.min.css' );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {
				if ( $this->args['fusion_table_type'] ) {
					$table_style = $this->args['content'][19];

					if ( ( '1' === $table_style || '2' === $table_style ) && $table_style !== $this->args['fusion_table_type'] ) {
						$this->args['fusion_table_type'] = $table_style;
					}

					$attr = fusion_builder_visibility_atts(
						$this->args['hide_on_mobile'],
						[
							'class' => 'table-' . $this->args['fusion_table_type'],
						]
					);

					if ( $this->args['animation_type'] ) {
						$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
					}

					$attr['style'] = Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

					if ( $this->args['class'] ) {
						$attr['class'] .= ' ' . $this->args['class'];
					}

					if ( $this->args['id'] ) {
						$attr['id'] = $this->args['id'];
					}

					return $attr;
				}
			}
		}
	}

	new FusionSC_FusionTable();

}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_table() {
	fusion_builder_map(
		[
			'name'             => __( 'Table', 'fusion-builder' ),
			'shortcode'        => 'fusion_table',
			'icon'             => 'fusiona-table',
			'allow_generator'  => true,
			'admin_enqueue_js' => FUSION_BUILDER_PLUGIN_URL . 'shortcodes/js/fusion-table.js',
			'help_url'         => 'https://theme-fusion.com/documentation/avada/elements/table-element/',
			'on_settings'      => 'calculateTableData',
			'params'           => [
				[
					'type'        => 'select',
					'heading'     => esc_attr__( 'Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the table style.', 'fusion-builder' ),
					'param_name'  => 'fusion_table_type',
					'value'       => [
						'1' => esc_attr__( 'Style 1', 'fusion-builder' ),
						'2' => esc_attr__( 'Style 2', 'fusion-builder' ),
					],
					'default'     => '1',
				],
				[
					'type'        => 'range',
					'heading'     => esc_attr__( 'Number of Rows', 'fusion-builder' ),
					'description' => esc_attr__( 'Select how many rows to display.', 'fusion-builder' ),
					'param_name'  => 'fusion_table_rows',
					'value'       => '',
					'min'         => '1',
					'max'         => '50',
					'step'        => '1',
					'default'     => '2',
				],
				[
					'type'        => 'range',
					'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
					'description' => esc_attr__( 'Select how many columns to display.', 'fusion-builder' ),
					'param_name'  => 'fusion_table_columns',
					'value'       => '',
					'min'         => '1',
					'max'         => '25',
					'step'        => '1',
					'default'     => '2',
				],
				[
					'type'        => 'tinymce',
					'heading'     => esc_attr__( 'Table', 'fusion-builder' ),
					'description' => esc_attr__( 'Table content will appear here.', 'fusion-builder' ),
					'param_name'  => 'element_content',
					'value'       => '<div class="table-1"><table width="100%"><thead><tr><th align="left">Column 1</th><th align="left">Column 2</th></tr></thead><tbody><tr><td align="left">Column 1 Value</td><td align="left">Column 2 Value</td></tr></tbody></table></div>',
				],
				'fusion_animation_placeholder' => [
					'preview_selector' => '.table-1,.table-2',
				],
				'fusion_margin_placeholder'    => [
					'param_name' => 'margin',
					'group'      => esc_attr__( 'General', 'fusion-builder' ),
					'value'      => [
						'margin_top'    => '',
						'margin_right'  => '',
						'margin_bottom' => '',
						'margin_left'   => '',
					],
				],
				[
					'type'        => 'checkbox_button_set',
					'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					'param_name'  => 'hide_on_mobile',
					'value'       => fusion_builder_visibility_options( 'full' ),
					'default'     => fusion_builder_default_visibility( 'array' ),
				],
				[
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
					'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					'param_name'  => 'class',
					'value'       => '',
				],
				[
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
					'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					'param_name'  => 'id',
					'value'       => '',
				],
			],
		]
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_table' );

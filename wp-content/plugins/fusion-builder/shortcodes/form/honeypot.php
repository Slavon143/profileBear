<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.5
 */

if ( fusion_is_element_enabled( 'fusion_form_honeypot' ) ) {

	if ( ! class_exists( 'FusionForm_Honeypot' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.5
		 */
		class FusionForm_Honeypot extends Fusion_Form_Component {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.5
			 * @var array
			 */
			protected $args;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.5
			 * @var int
			 */
			public $counter = 0;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.5
			 */
			public function __construct() {
				parent::__construct( 'fusion_form_honeypot' );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.5
			 * @return array
			 */
			public static function get_element_defaults() {

				return [
					'name'  => 'hypot_field',
					'class' => '',
					'id'    => '',
				];
			}

			/**
			 * Render form field html.
			 *
			 * @access public
			 * @since 3.5
			 * @param string $content The content.
			 * @return string
			 */
			public function render_input_field( $content ) {
				$html = '<input type="hidden" tabindex="-1" name="' . esc_attr( $this->args['name'] ) .
				'" class="fusion-form-input" autocomplete="' . esc_attr( uniqid( 'hypot-' ) ) . '" aria-hidden="true" data-fusion-is-honeypot="true">';
				return $html;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.5
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/form/honeypot.min.css' );
			}
		}
	}

	new FusionForm_Honeypot();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.5
 */
function fusion_form_honeypot() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Honeypot',
			[
				'name'           => esc_attr__( 'Honeypot Field', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_honeypot',
				'icon'           => 'fusiona-privacy',
				'form_component' => true,
				'preview'        => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-form-element-preview.php',
				'preview_id'     => 'fusion-builder-block-module-form-element-preview-template',
				'params'         => [
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Field Name', 'fusion-builder' ),
						'description' => esc_html__( 'Enter the field name. Please use only lowercase alphanumeric characters, dashes, and underscores.', 'fusion-builder' ),
						'param_name'  => 'name',
						'value'       => 'hypot_field',
						'placeholder' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_attr__( 'Add a class for the input field.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'description' => esc_attr__( 'Add an ID for the input field.', 'fusion-builder' ),
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_form_honeypot' );

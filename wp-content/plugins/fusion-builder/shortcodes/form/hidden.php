<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_hidden' ) ) {

	if ( ! class_exists( 'FusionForm_Hidden' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_Hidden extends Fusion_Form_Component {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.1
			 * @var array
			 */
			protected $args;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.1
			 * @var int
			 */
			public $counter = 0;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.1
			 */
			public function __construct() {
				parent::__construct( 'fusion_form_hidden' );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public static function get_element_defaults() {

				return [
					'name'        => '',
					'field_value' => '',
					'class'       => '',
					'id'          => '',
					'tooltip'     => '',
				];
			}

			/**
			 * Render form field html.
			 *
			 * @access public
			 * @since 3.1
			 * @param string $content The content.
			 * @return string
			 */
			public function render_input_field( $content ) {
				$this->args['value']  = apply_filters( 'fusion_form_hidden_field_value', $this->args['field_value'], $this->args['name'] );
				$this->args['hidden'] = true;

				$html = $this->generate_input_field( $this->args, 'hidden' );

				return $html;
			}
		}
	}

	new FusionForm_Hidden();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_hidden() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Password',
			[
				'name'           => esc_attr__( 'Hidden Field', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_hidden',
				'icon'           => 'fusiona-eye-slash',
				'form_component' => true,
				'preview'        => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-form-element-preview.php',
				'preview_id'     => 'fusion-builder-block-module-form-element-preview-template',
				'params'         => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Field Label', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter the label for the input field. This will not be visual, but is used for HubSpot matching.', 'fusion-builder' ),
						'param_name'  => 'label',
						'value'       => '',
						'placeholder' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Field Name', 'fusion-builder' ),
						'description' => esc_html__( 'Enter the field name. Please use only lowercase alphanumeric characters, dashes, and underscores.', 'fusion-builder' ),
						'param_name'  => 'name',
						'value'       => esc_html__( 'hidden_field', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_html__( 'Field Value', 'fusion-builder' ),
						'description'  => esc_html__( 'Enter the value to be set for this hidden field.', 'fusion-builder' ),
						'param_name'   => 'field_value',
						'value'        => esc_html__( 'Hidden Value', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
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
add_action( 'fusion_builder_before_init', 'fusion_form_hidden' );

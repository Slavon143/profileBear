<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_radio' ) ) {

	if ( ! class_exists( 'FusionForm_Radio' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_Radio extends Fusion_Form_Component {

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
				parent::__construct( 'fusion_form_radio' );
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
					'label'             => '',
					'name'              => '',
					'required'          => '',
					'empty_notice'      => '',
					'placeholder'       => '',
					'options'           => '',
					'form_field_layout' => '',
					'tab_index'         => '',
					'class'             => '',
					'id'                => '',
					'logics'            => '',
					'tooltip'           => '',
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
				return $this->checkbox( $this->args, 'radio' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.1
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/form/radio.min.css' );
			}
		}
	}

	new FusionForm_Radio();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_radio() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Radio',
			[
				'name'           => esc_attr__( 'Radio Field', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_radio',
				'icon'           => 'fusiona-af-radio',
				'form_component' => true,
				'preview'        => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-form-element-preview.php',
				'preview_id'     => 'fusion-builder-block-module-form-element-preview-template',
				'params'         => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Field Label', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter the label for the input field. This is how users will identify individual fields.', 'fusion-builder' ),
						'param_name'  => 'label',
						'value'       => '',
						'placeholder' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Field Name', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter the field name. Please use only lowercase alphanumeric characters, dashes, and underscores.', 'fusion-builder' ),
						'param_name'  => 'name',
						'value'       => '',
						'placeholder' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Required Field', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection to ensure that this field is completed before allowing the user to submit the form.', 'fusion-builder' ),
						'param_name'  => 'required',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Empty Input Notice', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter text validation notice that should display if data input is empty.', 'fusion-builder' ),
						'param_name'  => 'empty_notice',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'required',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Tooltip Text', 'fusion-builder' ),
						'param_name'  => 'tooltip',
						'value'       => '',
						'description' => esc_attr__( 'The text to display as tooltip hint for the input.', 'fusion-builder' ),
					],
					[
						'type'           => 'form_options',
						'heading'        => esc_html__( 'Options', 'fusion-builder' ),
						'param_name'     => 'options',
						'description'    => esc_html__( 'Add options for the input field.', 'fusion-builder' ),
						'value'          => 'W1tmYWxzZSwiT3B0aW9uIl1d',
						'allow_multiple' => 'no',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Field Layout', 'fusion-builder' ),
						'description' => esc_html__( 'Make a selection for field layout. Floated will have them side by side. Stacked will have one per row.', 'fusion-builder' ),
						'param_name'  => 'form_field_layout',
						'default'     => 'stacked',
						'value'       => [
							'stacked' => esc_html__( 'Stacked', 'fusion-builder' ),
							'floated' => esc_html__( 'Floated', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Tab Index', 'fusion-builder' ),
						'param_name'  => 'tab_index',
						'value'       => '',
						'description' => esc_attr__( 'Tab index for this input field.', 'fusion-builder' ),
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
					'fusion_form_logics_placeholder' => [],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_form_radio' );

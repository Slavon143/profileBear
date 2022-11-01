<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_checkbox' ) ) {

	if ( ! class_exists( 'FusionForm_Checkbox' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_Checkbox extends Fusion_Form_Component {

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
				parent::__construct( 'fusion_form_checkbox' );
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
					'min_required'      => '0',
					'max_required'      => '0',
					'placeholder'       => '',
					'form_field_layout' => '',
					'options'           => '',
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
				if ( 'selection' === $this->args['required'] ) {
					$min_fields = intval( $this->args['min_required'] );
					$max_fields = intval( $this->args['max_required'] );

					$number_of_options = 0;
					if ( ! empty( $this->args['options'] ) ) {
						$options = json_decode( fusion_decode_if_needed( $this->args['options'] ), true );
						if ( ! empty( $options ) && is_array( $options ) ) {
							$number_of_options = count( $options );
						}
					}

					if ( $min_fields > $number_of_options ) {
						$min_fields = $number_of_options;
					}

					if ( $max_fields > $number_of_options ) {
						$max_fields = $number_of_options;
					}

					if ( $max_fields < $min_fields && 0 !== $max_fields ) {
						$max_fields = $min_fields;
					}

					$fieldset_error = '' !== $this->args['empty_notice'] ? $this->args['empty_notice'] : $this->get_selection_fieldset_error_text( $min_fields, $max_fields, $number_of_options );

					if ( $fieldset_error ) {
						$this->args['required_label_text'] = $fieldset_error;
					}

					// If no error is present or minimum is not required, required is removed.
					if ( empty( $fieldset_error ) || 0 === $min_fields ) {
						$this->args['required'] = '';
					}

					$this->args['fieldset_attr_string'] = 'data-awb-fieldset-min-required="' . esc_attr( $min_fields ) . '" data-awb-fieldset-max-required="' . esc_attr( $max_fields ) . '" data-awb-fieldset-error="' . esc_attr( $fieldset_error ) . '"';
				}

				return $this->checkbox( $this->args );
			}

			/**
			 * Get the custom selection error text.
			 *
			 * @param int $min_fields The minimum number of required checkboxes.
			 * @param int $max_fields The maximum number of required checkboxes.
			 * @param int $number_of_options The number of checkboxes.
			 * @return string The translated error.
			 */
			public function get_selection_fieldset_error_text( $min_fields, $max_fields, $number_of_options ) {
				$fieldset_error = '';

				if ( 0 === $min_fields && 0 === $max_fields ) {
					return $fieldset_error;
				}

				if ( 0 < $min_fields && 0 === $max_fields ) {
					if ( $min_fields === $number_of_options ) {
						$fieldset_error = __( 'Please select all checkboxes from this field.', 'fusion-builder' );
					} else {
						/* translators: %1$s: will be replaced with a number, that represents the checkboxes. */
						$fieldset_error = _n( 'Please select at least %1$s checkbox from this field.', 'Please select at least %1$s checkboxes from this field.', $min_fields, 'fusion-builder' );
						$fieldset_error = sprintf( $fieldset_error, $min_fields );
					}
				} elseif ( 0 === $min_fields && 0 < $max_fields ) {
					/* translators: %1$s: will be replaced with a number, that represents the checkboxes. */
					$fieldset_error = _n( 'Please select a maximum of %1$s checkbox from this field.', 'Please select a maximum of %1$s checkboxes from this field.', $max_fields, 'fusion-builder' );
					$fieldset_error = sprintf( $fieldset_error, $max_fields );
				} elseif ( $min_fields === $number_of_options && $max_fields === $number_of_options ) {
					$fieldset_error = __( 'Please select all checkboxes from this field.', 'fusion-builder' );
				} elseif ( $min_fields === $max_fields && 0 < $min_fields ) {
					/* translators: %1$s: will be replaced with a number, that represents the checkboxes. */
					$fieldset_error = _n( 'Please select exactly %1$s checkbox from this field.', 'Please select exactly %1$s checkboxes from this field.', $max_fields, 'fusion-builder' );
					$fieldset_error = sprintf( $fieldset_error, $max_fields );
				} else {
					/* translators: %1$s: replaced with the minimum number of checkboxes, %2$s: replaced with the maximum number of checkboxes. */
					$fieldset_error = __( 'Please select between %1$s and %2$s checkboxes from this field.', 'fusion-builder' );
					$fieldset_error = sprintf( $fieldset_error, $min_fields, $max_fields );
				}

				return $fieldset_error;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.1
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/form/checkbox.min.css' );
			}
		}
	}

	new FusionForm_Checkbox();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_checkbox() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Checkbox',
			[
				'name'           => esc_attr__( 'Checkbox Field', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_checkbox',
				'icon'           => 'fusiona-af-checkbox',
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
						'description' => esc_attr__( 'How many checkboxes are required to be checked. On "All" the user will need to mark each checkbox in order to submit the form. On "Selection" the user needs to check a number of minimum/maximum checkboxes to proceed. Ideal for use as a privacy acceptance.', 'fusion-builder' ),
						'param_name'  => 'required',
						'default'     => 'no',
						'value'       => [
							'yes'       => esc_attr__( 'All', 'fusion-builder' ),
							'selection' => esc_attr__( 'Selection', 'fusion-builder' ),
							'no'        => esc_attr__( 'None', 'fusion-builder' ),
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
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Minimum Required Fields', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the minimum required fields for the user to submit the application. 0 means no minimum fields are required.', 'fusion-builder' ),
						'param_name'  => 'min_required',
						'value'       => '0',
						'min'         => '0',
						'max'         => '25',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'required',
								'value'    => 'selection',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Maximum Required Fields', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the maximum required fields for the user to submit the application. 0 means no maximum.', 'fusion-builder' ),
						'param_name'  => 'max_required',
						'value'       => '0',
						'min'         => '0',
						'max'         => '25',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'required',
								'value'    => 'selection',
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
						'type'        => 'form_options',
						'heading'     => esc_html__( 'Options', 'fusion-builder' ),
						'param_name'  => 'options',
						'description' => esc_html__( 'Add options for the input field.', 'fusion-builder' ),
						'value'       => 'W1tmYWxzZSwiT3B0aW9uIl1d',
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
add_action( 'fusion_builder_before_init', 'fusion_form_checkbox' );

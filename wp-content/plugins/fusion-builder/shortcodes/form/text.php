<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_text' ) ) {

	if ( ! class_exists( 'FusionForm_Text' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_Text extends Fusion_Form_Component {

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
				parent::__construct( 'fusion_form_text' );
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
					'label'            => '',
					'name'             => '',
					'placeholder'      => '',
					'tab_index'        => '',
					'class'            => '',
					'id'               => '',
					'disabled'         => '',
					'logics'           => '',
					'input_field_icon' => '',
					'pattern'          => '',
					'custom_pattern'   => '',
					'required'         => '',
					'invalid_notice'   => '',
					'empty_notice'     => '',
					'maxlength'        => '0',
					'minlength'        => '0',
					'tooltip'          => '',
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
				return $this->generate_input_field( $this->args, 'text' );
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.1
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$this->counter++;

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, $this->shortcode_handle );
				if ( isset( $defaults['border_size'] ) ) {
					$defaults['border_size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );
				}
				$content = apply_filters( 'fusion_shortcode_content', $content, $this->shortcode_handle, $args );

				$this->args = $defaults;

				$html = $this->get_form_field( $content );

				return apply_filters( 'fusion_form_component_' . $this->shortcode_handle . '_content', $html, $args );
			}
		}
	}

	new FusionForm_Text();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_text() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Text',
			[
				'name'           => esc_attr__( 'Text Field', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_text',
				'icon'           => 'fusiona-af-text',
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
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Disabled Field', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to disable the field, which makes its content uneditable. Disabled fields won\'t be submitted.', 'fusion-builder' ),
						'param_name'  => 'disabled',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Placeholder Text', 'fusion-builder' ),
						'param_name'  => 'placeholder',
						'value'       => '',
						'description' => esc_attr__( 'The placeholder text to display as hint for the input type. If tooltip is enabled, the placeholder will be displayed as tooltip.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Tooltip Text', 'fusion-builder' ),
						'param_name'  => 'tooltip',
						'value'       => '',
						'description' => esc_attr__( 'The text to display as tooltip hint for the input.', 'fusion-builder' ),
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Input Field Icon', 'fusion-builder' ),
						'param_name'  => 'input_field_icon',
						'value'       => '',
						'description' => esc_attr__( 'Select an icon for the input field, click again to deselect.', 'fusion-builder' ),
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Allowed Input Pattern', 'fusion-builder' ),
						'description' => esc_attr__( 'Select allowed input pattern. Select custom to add your own pattern.', 'fusion-builder' ),
						'param_name'  => 'pattern',
						'value'       => [
							''                   => esc_attr__( 'None', 'fusion-builder' ),
							'custom'             => esc_attr__( 'Custom', 'fusion-builder' ),
							'letters'            => esc_attr__( 'Letters', 'fusion-builder' ),
							'alpha_numeric'      => esc_attr__( 'Alpha Numeric', 'fusion-builder' ),
							'number'             => esc_attr__( 'Numbers Only', 'fusion-builder' ),
							'credit_card_number' => esc_attr__( 'Credit Card Number', 'fusion-builder' ),
							'phone'              => esc_attr__( 'Phone Number', 'fusion-builder' ),
							'url'                => esc_attr__( 'URL', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'raw_text',
						'heading'     => esc_attr__( 'Custom Pattern', 'fusion-builder' ),
						'param_name'  => 'custom_pattern',
						'value'       => '',
						/* translators: Patterns link. */
						'description' => sprintf( __( 'Enter the custom pattern. For pattern examples, you can check %s.', 'fusion-builder' ), '<a href="https://www.html5pattern.com/" target="_blank">' . esc_attr__( 'HTML5 Pattern', 'fusion-builder' ) . '</a>' ),
						'dependency'  => [
							[
								'element'  => 'pattern',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Invalid Input Notice', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter validation notice that should display if data input is invalid.', 'fusion-builder' ),
						'param_name'  => 'invalid_notice',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'pattern',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Minimum Required Characters', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the minimum number of characters that will be required for this input field. Leave at 0 to have no minimum.', 'fusion-builder' ),
						'param_name'  => 'minlength',
						'value'       => '0',
						'min'         => '0',
						'max'         => '120',
						'step'        => '1',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Maximum Allowed Characters', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the maximum number of characters that will be allowed for this input field. Leave at 0 to have no maximum.', 'fusion-builder' ),
						'param_name'  => 'maxlength',
						'value'       => '0',
						'min'         => '0',
						'max'         => '120',
						'step'        => '1',
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
add_action( 'fusion_builder_before_init', 'fusion_form_text' );

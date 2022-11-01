<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_textarea' ) ) {

	if ( ! class_exists( 'FusionForm_Textarea' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_Textarea extends Fusion_Form_Component {

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
				parent::__construct( 'fusion_form_textarea' );
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
					'disabled'         => '',
					'required'         => '',
					'empty_notice'     => '',
					'placeholder'      => '',
					'input_field_icon' => '',
					'rows'             => '4',
					'maxlength'        => '0',
					'minlength'        => '0',
					'tab_index'        => '',
					'class'            => '',
					'id'               => '',
					'logics'           => '',
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

				$element_data = $this->create_element_data( $this->args );

				$html       = '';
				$min_lenght = '';
				$max_length = '';

				if ( '' !== $this->args['tooltip'] ) {
					$element_data['label'] .= $this->get_field_tooltip( $this->args );
				}

				$content = $content ? $content : ( isset( $element_data['value'] ) ? $element_data['value'] : '' );

				if ( isset( $this->args['minlength'] ) && is_numeric( $this->args['minlength'] ) ) {
					$min_lenght = ' minlength="' . $this->args['minlength'] . '"';
				}
				if ( isset( $this->args['maxlength'] ) && ! empty( $this->args['maxlength'] ) ) {
					$max_length = ' maxlength="' . $this->args['maxlength'] . '"';
				}

				$element_html  = '<textarea cols="40" ';
				$element_html .= '' !== $element_data['empty_notice'] ? 'data-empty-notice="' . $element_data['empty_notice'] . '" ' : '';
				$element_html .= $min_lenght . ' ' . $max_length . ' rows="' . $this->args['rows'] . '" tabindex="' . $this->args['tab_index'] . '" id="' . $this->args['name'] . '" name="' . $this->args['name'] . '"' . $element_data['class'] . $element_data['required'] . $element_data['disabled'] . $element_data['placeholder'] . $element_data['style'] . $element_data['holds_private_data'] . '>' . $content . '</textarea>';

				if ( isset( $this->args['input_field_icon'] ) && '' !== $this->args['input_field_icon'] ) {
					$icon_html     = '<div class="fusion-form-input-with-icon">';
					$icon_html    .= '<i class=" ' . fusion_font_awesome_name_handler( $this->args['input_field_icon'] ) . '"></i>';
					$element_html  = $icon_html . $element_html;
					$element_html .= '</div>';
				}

				if ( 'above' === $this->params['form_meta']['label_position'] ) {
					$html .= $element_data['label'] . $element_html;
				} else {
					$html .= $element_html . $element_data['label'];
				}

				return $html;
			}
		}
	}

	new FusionForm_Textarea();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_textarea() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Textarea',
			[
				'name'           => esc_attr__( 'Textarea Field', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_textarea',
				'icon'           => 'fusiona-af-text-area',
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
						'type'        => 'range',
						'heading'     => esc_html__( 'Textarea Row Value', 'fusion-builder' ),
						'param_name'  => 'rows',
						'value'       => '4',
						'min'         => '4',
						'max'         => '20',
						'step'        => '1',
						'description' => esc_html__( 'Choose number of rows you want to have for this textarea field.', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Minimum Required Characters', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the minimum number of characters that will be required for this input field. Leave at 0 to have no minimum.', 'fusion-builder' ),
						'param_name'  => 'minlength',
						'value'       => '0',
						'min'         => '0',
						'max'         => '300',
						'step'        => '5',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Maximum Allowed Characters', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the maximum number of characters that will be allowed for this input field. Leave at 0 to have no maximum.', 'fusion-builder' ),
						'param_name'  => 'maxlength',
						'value'       => '0',
						'min'         => '0',
						'max'         => '1000',
						'step'        => '5',
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
add_action( 'fusion_builder_before_init', 'fusion_form_textarea' );

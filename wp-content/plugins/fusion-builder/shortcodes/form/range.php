<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_range' ) ) {

	if ( ! class_exists( 'FusionForm_Range' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_Range extends Fusion_Form_Component {

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
				parent::__construct( 'fusion_form_range' );
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
					'label'       => '',
					'name'        => '',
					'required'    => '',
					'placeholder' => '',
					'min'         => '',
					'max'         => '',
					'step'        => '',
					'value'       => '',
					'tab_index'   => '',
					'class'       => '',
					'id'          => '',
					'logics'      => '',
					'orientation' => '',
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
				$html = '';

				$element_data = $this->create_element_data( $this->args );
				$unique       = uniqid();
				$class_name   = 'fusion-form-range-field-container fusion-form-range-field-container-' . $unique;
				$styles       = '';

				if ( '' !== $this->args['tooltip'] ) {
					$element_data['label'] .= $this->get_field_tooltip( $this->args );
				}

				if ( 'right' === $this->args['orientation'] ) {
					$class_name .= ' orientation-right';
				}

				$element_html = '<div class="' . $class_name . '">';

				if ( 'right' !== $this->args['orientation'] ) {
					$element_html .= '<input type="text" tabindex="' . $this->args['tab_index'] . '" min="' . $this->args['min'] . '" max="' . $this->args['max'] . '" class="fusion-form-range-value" value="' . $this->args['value'] . '"' . $element_data['style'] . '/>';
				}
				$element_html .= '<input type="range" id="' . $this->args['name'] . '" name="' . $this->args['name'] . '" min="' . $this->args['min'] . '" max="' . $this->args['max'] . '" step="' . $this->args['step'] . '" value="' . $this->args['value'] . '"' . $element_data['class'] . $element_data['required'] . $element_data['placeholder'] . $element_data['style'] . $element_data['holds_private_data'] . '/>';
				if ( 'right' === $this->args['orientation'] ) {
					$element_html .= '<input type="text" tabindex="' . $this->args['tab_index'] . '" min="' . $this->args['min'] . '" max="' . $this->args['max'] . '" class="fusion-form-range-value" value="' . $this->args['value'] . '"' . $element_data['style'] . '/>';
				}
				$element_html .= '</div>';

				if ( 'above' === $this->params['form_meta']['label_position'] ) {
					$html .= $element_data['label'] . $element_html;
				} else {
					$html .= $element_html . $element_data['label'];
				}

				if ( '' !== $styles ) {
					$html .= '<style type="text/css">' . $styles . '</style>';
				}

				return $html;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.1
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/form/range.min.css' );
			}
		}
	}

	new FusionForm_Range();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_range() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Range',
			[
				'name'           => esc_attr__( 'Range  Field', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_range',
				'icon'           => 'fusiona-af-range',
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
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Orientation', 'fusion-builder' ),
						'description' => esc_attr__( 'Range input field orientation', 'fusion-builder' ),
						'param_name'  => 'orientation',
						'default'     => '',
						'value'       => [
							''      => esc_attr__( 'Left', 'fusion-builder' ),
							'right' => esc_attr__( 'Right', 'fusion-builder' ),
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
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Range Min Value', 'fusion-builder' ),
						'param_name'  => 'min',
						'value'       => '0',
						'description' => esc_html__( 'Minimum allowed value for range input type.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Range Max Value', 'fusion-builder' ),
						'param_name'  => 'max',
						'value'       => '100',
						'description' => esc_html__( 'Maximum allowed value for range input type.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Range Step Value', 'fusion-builder' ),
						'param_name'  => 'step',
						'value'       => '1',
						'description' => esc_html__( 'Incremental Value for range input type.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Default Value', 'fusion-builder' ),
						'param_name'  => 'value',
						'value'       => '0',
						'description' => esc_html__( 'Set default value for range input type.', 'fusion-builder' ),
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
add_action( 'fusion_builder_before_init', 'fusion_form_range' );

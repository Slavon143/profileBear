<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_image_select' ) ) {

	if ( ! class_exists( 'FusionForm_ImageSelect' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_ImageSelect extends Fusion_Form_Component {

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
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.1
			 * @var int
			 */
			public $child_counter = 0;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.1
			 */
			public function __construct() {
				parent::__construct( 'fusion_form_image_select' );
				add_shortcode( 'fusion_form_image_select_input', [ $this, 'render_select_image' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.1
			 * @param string $context Whether we want parent or child.
			 * @return array
			 */
			public static function get_element_defaults( $context = 'parent' ) {

				$parent = [
					'label'              => '',
					'name'               => '',
					'required'           => '',
					'empty_notice'       => '',
					'multiple_select'    => '',
					'placeholder'        => 'no',
					'form_field_layout'  => '',
					'width'              => '',
					'height'             => '',
					'border_size_top'    => '',
					'border_size_right'  => '',
					'border_size_bottom' => '',
					'border_size_left'   => '',
					'border_radius'      => '',
					'active_color'       => '',
					'inactive_color'     => '',
					'tab_index'          => '',
					'class'              => '',
					'id'                 => '',
					'logics'             => '',
					'tooltip'            => '',

					// Padding.
					'padding_top'        => '',
					'padding_right'      => '',
					'padding_bottom'     => '',
					'padding_left'       => '',
				];

				$child = [
					'image'    => '',
					'image_id' => '',
					'name'     => '',
					'label'    => '',
					'checked'  => 'no',
				];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				}
			}

			/**
			 * Adds field wrapper html.
			 *
			 * @access public
			 * @since 3.1
			 * @return string
			 */
			public function add_field_wrapper_html() {
				$label_position = 'above';

				if ( $this->params['form_meta']['label_position'] ) {
					$label_position = $this->params['form_meta']['label_position'];
				}

				$html = '<div ';

				// Add custom ID if it's there.
				if ( isset( $this->args['id'] ) && '' !== $this->args['id'] ) {
					$html .= 'id="' . esc_attr( $this->args['id'] ) . '" ';
				}

				// Start building class.
				$html .= 'class="fusion-form-field ' . str_replace( '_', '-', $this->shortcode_handle ) . '-field fusion-form-label-' . $label_position . ' fusion-form-field-fusion-form-image-select-' . $this->counter;

				// Add inline class if needed.
				if ( 'floated' === $this->args['form_field_layout'] ) {
					$html .= ' option-inline';
				}

				// Add custom class if it's there.
				if ( isset( $this->args['class'] ) && '' !== $this->args['class'] ) {
					$html .= ' ' . esc_attr( $this->args['class'] );
				}

				// Close class quotes.
				$html .= '"';

				$html .= ' data-form-id="' . $this->params['form_number'] . '"';

				$html .= '>';

				return $html;
			}

			/**
			 * Render the main wrapping input for radio.
			 *
			 * @access public
			 * @since 3.1
			 * @param string $content Content markup.
			 * @return string
			 */
			public function render_input_field( $content ) {
				global $fusion_form;

				$type    = 'radio';
				$options = '';
				$html    = '';
				$styles  = '';

				$element_data       = $this->create_element_data( $this->args );
				$this->element_data = $element_data;

				$element_html  = '<fieldset>';
				$element_html .= do_shortcode( $content );
				$element_html .= '</fieldset>';

				if ( '' !== $this->args['tooltip'] ) {
					$element_data['label'] .= $this->get_field_tooltip( $this->args );
				}

				if ( '' !== $element_data['label'] ) {
					$element_data['label'] = '<div class="fusion-form-label-wrapper">' . $element_data['label'] . '</div>';
				}

				if ( 'above' === $fusion_form['form_meta']['label_position'] ) {
					$html .= $element_data['label'] . $element_html;
				} else {
					$html .= $element_html . $element_data['label'];
				}

				// Build styles.
				$base_selector = '.fusion-form-form-wrapper.fusion-form-' . $this->params['form_number'] . ' .fusion-form-field-fusion-form-image-select-' . $this->counter;

				if ( '' !== $this->args['width'] ) {
					$styles .= $base_selector . ' .fusion-form-image-select label .fusion-form-image-wrapper{width:' . Fusion_Sanitize::get_value_with_unit( $this->args['width'] ) . ';}';
				}

				if ( '' !== $this->args['height'] ) {
					$styles .= $base_selector . ' .fusion-form-image-select label .fusion-form-image-wrapper{height:' . Fusion_Sanitize::get_value_with_unit( $this->args['height'] ) . ';}';
				}

				foreach ( [ 'top', 'right', 'bottom', 'left' ] as $direction ) {
					if ( '' !== $this->args[ 'border_size_' . $direction ] ) {
						$styles .= $base_selector . ' .fusion-form-image-select label{border-' . $direction . '-width:' . Fusion_Sanitize::get_value_with_unit( $this->args[ 'border_size_' . $direction ] ) . ';}';
					}
				}

				if ( '' !== $this->args['border_radius'] ) {
					$styles .= $base_selector . ' .fusion-form-image-select label{border-radius:' . Fusion_Sanitize::get_value_with_unit( $this->args['border_radius'] ) . ';}';
				}

				if ( '' !== $this->args['inactive_color'] ) {
					$styles .= $base_selector . ' .fusion-form-image-select label{border-color:' . $this->args['inactive_color'] . ';}';
				}

				if ( '' !== $this->args['active_color'] ) {
					$styles .= $base_selector . ' .fusion-form-image-select .fusion-form-input:checked + label{border-color:' . $this->args['active_color'] . ';}';
					$styles .= $base_selector . ' .fusion-form-image-select .fusion-form-input:hover:not(:checked) + label{border-color:' . Fusion_Color::new_color( $this->args['active_color'] )->get_new( 'alpha', '0.5' )->to_css_var_or_rgba() . ';}';
				}

				// Padding.
				$paddings       = [ 'top', 'right', 'bottom', 'left' ];
				$padding_styles = '';

				foreach ( $paddings as $padding ) {
					$padding_name = 'padding_' . $padding;

					if ( '' !== $this->args[ $padding_name ] ) {

						$padding_styles .= 'padding-' . $padding . ':' . fusion_library()->sanitize->get_value_with_unit( $this->args[ $padding_name ] ) . ';';
					}
				}

				if ( '' !== $padding_styles ) {
					$styles .= $base_selector . ' label{' . $padding_styles . ';}';
				}

				if ( '' !== $styles ) {
					$html .= '<style type="text/css">' . $styles . '</style>';
				}

				return $html;
			}

			/**
			 * Render the individual select images.
			 *
			 * @access public
			 * @since 3.1
			 * @param array  $args The input arguments.
			 * @param string $content The markup.
			 * @return string
			 */
			public function render_select_image( $args, $content = '' ) {
				global $fusion_form;

				$child_args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'child' ), $args, 'fusion_form_image_select_input' );

				$this->child_counter++;

				$html         = '';
				$checked      = 'yes' === $child_args['checked'] ? ' checked ' : '';
				$type         = 'image-select';
				$input_type   = 'yes' === $this->args['multiple_select'] ? 'checkbox' : 'radio';
				$element_name = $this->args['name'] . '[]';
				$element_data = $this->element_data;
				$child_label  = trim( $child_args['label'] );
				$label_html   = '';

				if ( '' !== $child_label ) {
					$label_html = '<span>' . esc_html( $child_label ) . '</span>';
				}

				$value = '' === $child_args['name'] ? str_replace( ' ', '-', strtolower( $child_label ) ) : $child_args['name'];

				$checkbox_class = 'fusion-form-' . $type;
				$label_id       = $type . '-' . str_replace( ' ', '-', strtolower( $value ) ) . $this->child_counter;

				$html .= '<div class="' . $checkbox_class . '">';
				$html .= '<input ';
				$html .= '' !== $element_data['empty_notice'] ? 'data-empty-notice="' . $element_data['empty_notice'] . '" ' : '';
				$html .= 'tabindex="' . $this->args['tab_index'] . '" id="' . esc_attr( $label_id ) . '" type="' . $input_type . '" value="' . esc_attr( $value ) . '" name="' . esc_attr( $element_name ) . '"' . $element_data['class'] . $element_data['required'] . $checked . $element_data['holds_private_data'] . '/>';
				$html .= '<label for="' . esc_attr( $label_id ) . '">';

				// Perhaps an option whether to show label or not.
				if ( 'above' === $fusion_form['form_meta']['label_position'] ) {
					$html .= $label_html;
				}

				// Add image.
				$image_data = fusion_library()->images->get_attachment_data_by_helper( $child_args['image_id'], $child_args['image'] );
				if ( $image_data && '' !== $image_data['id'] ) {
					$html .= '<picture class="fusion-form-image-wrapper">';
					$html .= wp_get_attachment_image( $image_data['id'], 'full' );
					$html .= '</picture>';
				}

				if ( 'above' !== $fusion_form['form_meta']['label_position'] ) {
					$html .= $label_html;
				}

				$html .= '</label>';
				$html .= '</div>';

				return $html;
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @param string $context Whether we want parent or child.
			 * @since 3.1
			 * @return array
			 */
			public static function settings_to_params( $context = '' ) {

				$parent = [
					'form_border_radius'      => [
						'param'    => 'border_radius',
						'callback' => 'fusionOption',
					],
					'form_border_color'       => [
						'param'    => 'inactive_color',
						'callback' => 'fusionOption',
					],
					'form_focus_border_color' => [
						'param'    => 'active_color',
						'callback' => 'fusionOption',
					],
				];

				$child = [];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				} else {
					return [
						'parent' => $parent,
						'child'  => $child,
					];
				}
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.1
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/form/image-select.min.css' );
			}
		}
	}

	new FusionForm_ImageSelect();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_image_select() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_ImageSelect',
			[
				'name'           => esc_attr__( 'Image Select Field', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_image_select',
				'icon'           => 'fusiona-af-radio-image',
				'form_component' => true,
				'preview'        => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-form-element-preview.php',
				'preview_id'     => 'fusion-builder-block-module-form-element-preview-template',
				'multi'          => 'multi_element_parent',
				'element_child'  => 'fusion_form_image_select_input',
				'child_ui'       => true,
				'sortable'       => false,
				'params'         => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this radio image.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_form_image_select_input /]',
					],
					[
						'type'             => 'multiple_upload',
						'heading'          => esc_attr__( 'Bulk Image Upload', 'fusion-builder' ),
						'description'      => __( 'This option allows you to select multiple images at once and they will populate into individual items. It saves time instead of adding one image at a time.', 'fusion-builder' ),
						'param_name'       => 'multiple_upload',
						'child_params'     => [
							'image'    => 'url',
							'image_id' => 'id',
						],
						'remove_from_atts' => true,
					],
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
						'heading'     => esc_attr__( 'Multiple Select', 'fusion-builder' ),
						'description' => esc_attr__( 'Allow multiple options to be selected.', 'fusion-builder' ),
						'param_name'  => 'multiple_select',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
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
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Image Width', 'fusion-builder' ),
						'param_name'  => 'width',
						'value'       => '80px',
						'description' => esc_html__( 'In pixels (px), ex: 10px.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Image Height', 'fusion-builder' ),
						'param_name'  => 'height',
						'value'       => '80px',
						'description' => esc_html__( 'In pixels (px), ex: 10px.', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'padding_dimensions',
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Border Size', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the border size of the image options.  If left empty will inherit from the form options.', 'fusion-builder' ),
						'param_name'       => 'border_size',
						'value'            => [
							'border_size_top'    => '',
							'border_size_right'  => '',
							'border_size_bottom' => '',
							'border_size_left'   => '',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Inactive Image Border Color', 'fusion-builder' ),
						'param_name'  => 'inactive_color',
						'value'       => '',
						'default'     => fusion_get_option( 'form_border_color' ),
						'description' => esc_html__( 'Set border color for inactive image.', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Active Image Border Color', 'fusion-builder' ),
						'param_name'  => 'active_color',
						'value'       => '',
						'default'     => fusion_get_option( 'form_focus_border_color' ),
						'description' => esc_html__( 'Set border color for selected image.', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Border Radius', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border radius of the image options. In pixels.', 'fusion-builder' ),
						'param_name'  => 'border_radius',
						'value'       => '',
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
						'default'     => fusion_get_option( 'form_border_radius' ),
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
			],
			'parent'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_form_image_select' );

/**
 * Map shortcode to Avada Builder.
 */
function fusion_form_image_select_input() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_ImageSelect',
			[
				'name'              => esc_attr__( 'Select Image', 'fusion-builder' ),
				'description'       => esc_attr__( 'Single image input for the image select.', 'fusion-builder' ),
				'shortcode'         => 'fusion_form_image_select_input',
				'hide_from_builder' => true,
				'selectors'         => [
					'class' => 'fusion-form-image-select',
				],
				'params'            => [
					[
						'type'        => 'upload',
						'heading'     => esc_attr__( 'Image', 'fusion-builder' ),
						'description' => esc_attr__( 'Upload an image to display.', 'fusion-builder' ),
						'param_name'  => 'image',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Image ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'image_id',
						'value'       => '',
						'hidden'      => true,
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_image_carousel',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Label', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter the label for the input field. This is how users will identify individual fields.', 'fusion-builder' ),
						'param_name'  => 'label',
						'value'       => '',
						'placeholder' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Value', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter the field value.', 'fusion-builder' ),
						'param_name'  => 'name',
						'value'       => '',
						'placeholder' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Initial State', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection to ensure that this field is completed before allowing the user to submit the form.', 'fusion-builder' ),
						'param_name'  => 'checked',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Checked', 'fusion-builder' ),
							'no'  => esc_attr__( 'Unchecked', 'fusion-builder' ),
						],
					],
				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_form_image_select_input' );

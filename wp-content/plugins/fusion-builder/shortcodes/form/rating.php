<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_rating' ) ) {

	if ( ! class_exists( 'FusionForm_Rating' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_Rating extends Fusion_Form_Component {

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
				parent::__construct( 'fusion_form_rating' );
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
					'icon'              => '',
					'limit'             => '5',
					'icon_color'        => '',
					'active_icon_color' => '',
					'icon_size'         => '',
					'options'           => '',
					'class'             => '',
					'id'                => '',
					'logics'            => '',
					'tooltip'           => '',
				];
			}
			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'form_border_color'       => [
						'param'    => 'icon_color',
						'callback' => 'fusionOption',
					],
					'form_focus_border_color' => [
						'param'    => 'active_icon_color',
						'callback' => 'fusionOption',
					],
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
				global $fusion_library;

				$options      = '';
				$html         = '';
				$styles       = '';
				$element_html = '';
				$hover_color  = '';

				$element_data = $this->create_element_data( $this->args );
				$limit        = fusion_library()->sanitize->number( $this->args['limit'] );
				$element_name = $this->args['name'];

				while ( $limit > 0 ) {
					$option   = $limit;
					$options .= '<input ';
					$options .= '' !== $element_data['empty_notice'] ? 'data-empty-notice="' . $element_data['empty_notice'] . '" ' : '';
					$options .= 'id="' . $option . '-' . $this->counter . '" type="radio" value="' . $option . '" name="' . $element_name . '"' . $element_data['class'] . $element_data['required'] . $element_data['checked'] . $element_data['holds_private_data'] . '/>';
					$options .= '<label for="' . $option . '-' . $this->counter . '" class="fusion-form-rating-icon">';
					$options .= '<i class="' . $this->args['icon'] . '"></i>';
					$options .= '</label>';
					$limit--;
				}

				$form_id = isset( $this->params['id'] ) ? $this->params['id'] : 1;

				// CSS for .rating-icon.
				if ( $this->args['icon_color'] || ( isset( $this->args['icon_size'] ) && '' !== $this->args['icon_size'] ) ) {
					$styles .= '.fusion-form-' . $form_id . '.fusion-form-form-wrapper .fusion-form-field .fusion-form-rating-area-' . $this->counter . '.fusion-form-rating-area .fusion-form-rating-icon {';
					if ( $this->args['icon_color'] ) {
						$styles .= 'color: ' . fusion_library()->sanitize->color( $this->args['icon_color'] ) . ';';
					}
					if ( isset( $this->args['icon_size'] ) && '' !== $this->args['icon_size'] ) {
						$styles .= 'font-size: ' . FusionBuilder::validate_shortcode_attr_value( $this->args['icon_size'], 'px' ) . ';';
					}
					$styles .= '}';
				}

				// CSS for .rating-icon:hover, .rating-icon:checked.
				if ( $this->args['active_icon_color'] ) {
					$hover_color = Fusion_Color::new_color( $this->args['active_icon_color'] )->get_new( 'alpha', '0.5' )->to_css_var_or_rgba();

					$styles .= '.fusion-form-' . $form_id . '.fusion-form-form-wrapper .fusion-form-field .fusion-form-rating-area-' . $this->counter . '.fusion-form-rating-area .fusion-form-input:checked~label i{ color: ' . $this->args['active_icon_color'] . ';}';

					$styles .= '.fusion-form-' . $form_id . '.fusion-form-form-wrapper .fusion-form-field .fusion-form-rating-area-' . $this->counter . '.fusion-form-rating-area .fusion-form-input:checked:hover ~ label i,';
					$styles .= '.fusion-form-' . $form_id . '.fusion-form-form-wrapper .fusion-form-field .fusion-form-rating-area-' . $this->counter . '.fusion-form-rating-area .fusion-form-rating-icon:hover i,';
					$styles .= '.fusion-form-' . $form_id . '.fusion-form-form-wrapper .fusion-form-field .fusion-form-rating-area-' . $this->counter . '.fusion-form-rating-area .fusion-form-rating-icon:hover ~ label i,';
					$styles .= '.fusion-form-' . $form_id . '.fusion-form-form-wrapper .fusion-form-field .fusion-form-rating-area-' . $this->counter . '.fusion-form-rating-area .fusion-form-input:hover ~ label i{ color: ' . $hover_color . ';}';
				}

				if ( '' !== $styles ) {
					$element_html .= '<style type="text/css">' . $styles . '</style>';
				}

				$element_html .= '<fieldset class="fusion-form-rating-area fusion-form-rating-area-' . $this->counter . ( is_rtl() ? ' rtl' : '' ) . '">';
				$element_html .= $options;
				$element_html .= '</fieldset>';

				if ( '' !== $this->args['tooltip'] ) {
					$element_data['label'] .= $this->get_field_tooltip( $this->args );
				}

				if ( 'above' === $this->params['form_meta']['label_position'] ) {
					$html .= $element_data['label'] . $element_html;
				} else {
					$html .= $element_html . $element_data['label'];
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/form/rating.min.css' );
			}
		}
	}

	new FusionForm_Rating();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_rating() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Rating',
			[
				'name'           => esc_attr__( 'Rating Field', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_rating',
				'icon'           => 'fusiona-af-rating',
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
						'type'        => 'range',
						'heading'     => esc_attr__( 'Rating Limit', 'fusion-builder' ),
						'param_name'  => 'limit',
						'value'       => '3',
						'min'         => '1',
						'max'         => '10',
						'step'        => '1',
						'description' => esc_attr__( 'Set the maximum rating that can be given.', 'fusion-builder' ),
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_html__( 'Rating Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => 'fa-star fas',
						'description' => esc_html__( 'Choose icon for rating.', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Icon Color', 'fusion-builder' ),
						'param_name'  => 'icon_color',
						'value'       => '',
						'description' => esc_html__( 'Choose icon color for rating.', 'fusion-builder' ),
						'default'     => fusion_get_option( 'form_border_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Hover/Active Icon Color', 'fusion-builder' ),
						'param_name'  => 'active_icon_color',
						'value'       => '',
						'description' => esc_html__( 'Choose icon color for rating.', 'fusion-builder' ),
						'default'     => fusion_get_option( 'form_focus_border_color' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Icon Font Size', 'fusion-builder' ),
						'param_name'  => 'icon_size',
						'description' => esc_html__( 'Controls the size of the icon. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'value'       => '',
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
add_action( 'fusion_builder_before_init', 'fusion_form_rating' );

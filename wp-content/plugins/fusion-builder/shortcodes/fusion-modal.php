<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_modal' ) ) {

	if ( ! class_exists( 'FusionSC_Modal' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Modal extends Fusion_Element {

			/**
			 * The modals counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $modal_counter = 1;

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
				add_filter( 'fusion_attr_modal-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_modal-shortcode-dialog', [ $this, 'dialog_attr' ] );
				add_filter( 'fusion_attr_modal-shortcode-content', [ $this, 'content_attr' ] );
				add_filter( 'fusion_attr_modal-shortcode-heading', [ $this, 'heading_attr' ] );
				add_filter( 'fusion_attr_modal-body', [ $this, 'modal_body_attr' ] );
				add_filter( 'fusion_attr_modal-shortcode-button', [ $this, 'button_attr' ] );
				add_filter( 'fusion_attr_modal-shortcode-button-footer', [ $this, 'button_footer_attr' ] );

				add_shortcode( 'fusion_modal', [ $this, 'render' ] );

			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'class'        => '',
					'id'           => '',
					'background'   => $fusion_settings->get( 'modal_bg_color' ),
					'border_color' => $fusion_settings->get( 'modal_border_color' ),
					'name'         => '',
					'size'         => 'small',
					'title'        => '',
					'show_footer'  => 'yes',
				];
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'modal_bg_color'     => 'background',
					'modal_border_color' => 'border_color',
				];
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_extras() {
				return [
					'close_text' => esc_attr__( 'Close', 'fusion-builder' ),
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode paramters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_modal' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_modal', $args );

				extract( $defaults );

				$this->args = $defaults;

				$color_obj = Fusion_Color::new_color( $background );
				$style     = '<style type="text/css">';

				if ( $border_color ) {
					$style .= '.modal-' . $this->modal_counter . ' .modal-header, .modal-' . $this->modal_counter . ' .modal-footer{border-color:' . $border_color . ';}';
				}

				if ( 40 >= $color_obj->lightness ) {
					$style .= '.modal-' . $this->modal_counter . ' .close{color:#ffffff; opacity:0.35}';
				}

				$style .= '</style>';

				$html  = '<div ' . FusionBuilder::attributes( 'modal-shortcode' ) . '>';
				$html .= $style;
				$html .= '<div ' . FusionBuilder::attributes( 'modal-shortcode-dialog' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'modal-shortcode-content' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'modal-header' ) . '>';
				$html .= '<button ' . FusionBuilder::attributes( 'modal-shortcode-button' ) . '>&times;</button>';
				$html .= '<h3 ' . FusionBuilder::attributes( 'modal-shortcode-heading' ) . '>' . $title . '</h3>';
				$html .= '</div>';

				fusion_element_rendering_elements( true );
				$html .= '<div ' . FusionBuilder::attributes( 'modal-body' ) . '>' . do_shortcode( $content ) . '</div>';
				fusion_element_rendering_elements( false );

				if ( 'yes' === $show_footer ) {
					$html .= '<div ' . FusionBuilder::attributes( 'modal-footer' ) . '>';
					$html .= '<button ' . FusionBuilder::attributes( 'modal-shortcode-button-footer' ) . '>' . esc_html__( 'Close', 'fusion-builder' ) . '</button>';
					$html .= '</div>';
				}

				$html .= '</div></div></div>';

				$this->modal_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_modal_content', $html, $args );

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = [
					'class'           => 'fusion-modal modal fade modal-' . $this->modal_counter,
					'tabindex'        => '-1',
					'role'            => 'dialog',
					'aria-labelledby' => 'modal-heading-' . $this->modal_counter,
					'aria-hidden'     => 'true',
				];

				if ( $this->args['name'] ) {
					$attr['class'] .= ' ' . $this->args['name'];
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;

			}

			/**
			 * Builds the dialog attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function dialog_attr() {

				$attr = [
					'class' => 'modal-dialog',
					'role'  => 'document',
				];

				$attr['class'] .= ( 'small' === $this->args['size'] ) ? ' modal-sm' : ' modal-lg';

				return $attr;

			}

			/**
			 * Builds the content attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function content_attr() {

				$attr = [
					'class' => 'modal-content fusion-modal-content',
				];

				if ( $this->args['background'] ) {
					$attr['style'] = 'background-color:' . $this->args['background'];
				}

				return $attr;

			}

			/**
			 * Builds the button attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function button_attr() {
				return [
					'class'        => 'close',
					'type'         => 'button',
					'data-dismiss' => 'modal',
					'aria-hidden'  => 'true',
					'aria-label'   => esc_html__( 'Close', 'fusion-builder' ),
				];
			}

			/**
			 * Builds the heading attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function heading_attr() {
				return [
					'class'        => 'modal-title',
					'id'           => 'modal-heading-' . $this->modal_counter,
					'data-dismiss' => 'modal',
					'aria-hidden'  => 'true',
				];
			}

			/**
			 * Builds the heading attributes array.
			 *
			 * @access public
			 * @since 1.6
			 * @return array
			 */
			public function modal_body_attr() {
				return [
					'class' => 'modal-body fusion-clearfix',
				];
			}

			/**
			 * Builds the button attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function button_footer_attr() {
				return [
					'class'        => 'fusion-button button-default button-medium button default medium',
					'type'         => 'button',
					'data-dismiss' => 'modal',
				];
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Modal settings.
			 */
			public function add_options() {

				return [
					'modal_shortcode_section' => [
						'label'       => esc_html__( 'Modal', 'fusion-builder' ),
						'description' => '',
						'id'          => 'modal_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-external-link',
						'fields'      => [
							'modal_bg_color'     => [
								'label'       => esc_html__( 'Modal Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the background color of the modal popup box.', 'fusion-builder' ),
								'id'          => 'modal_bg_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'modal_border_color' => [
								'label'       => esc_html__( 'Modal Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color of the modal popup box.', 'fusion-builder' ),
								'id'          => 'modal_border_color',
								'default'     => 'var(--awb-color3)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
						],
					],
				];
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-modal',
					FusionBuilder::$js_folder_url . '/general/fusion-modal.js',
					FusionBuilder::$js_folder_path . '/general/fusion-modal.js',
					[ 'bootstrap-modal' ],
					'1',
					true
				);
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/modal.min.css' );
			}
		}
	}

	new FusionSC_Modal();

}

if ( fusion_is_element_enabled( 'fusion_modal_text_link' ) ) {

	if ( ! class_exists( 'FusionSC_ModalTextLink' ) ) {
		/**
		 * The FusionSC_ModalTextLink class.
		 *
		 * @since 1.0
		 */
		class FusionSC_ModalTextLink {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * Initiate the shortcode
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {

				add_filter( 'fusion_attr_modal-text-link-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_modal_text_link', [ $this, 'render' ] );

			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_defaults() {
				return [
					'name'  => '',
					'class' => '',
					'id'    => '',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode paramters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_modal_text_link' );

				return '<a ' . FusionBuilder::attributes( 'modal-text-link-shortcode' ) . '>' . do_shortcode( $content ) . '</a>';

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = [
					'class' => 'fusion-modal-text-link',
				];

				if ( isset( $this->args['name'] ) && $this->args['name'] ) {
					$attr['data-toggle'] = 'modal';
					$attr['data-target'] = '.fusion-modal.' . $this->args['name'];
				}

				if ( isset( $this->args['class'] ) && $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( isset( $this->args['id'] ) && $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				$attr['href'] = '#';

				return $attr;

			}
		}
	}

	new FusionSC_ModalTextLink();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_modal() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Modal',
			[
				'name'            => esc_attr__( 'Modal', 'fusion-builder' ),
				'shortcode'       => 'fusion_modal',
				'icon'            => 'fusiona-external-link',
				'preview'         => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-modal-preview.php',
				'preview_id'      => 'fusion-builder-block-module-modal-preview-template',
				'allow_generator' => true,
				'help_url'        => 'https://theme-fusion.com/documentation/avada/elements/modal-element/',
				'inline_editor'   => true,
				'params'          => [
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Name Of Modal', 'fusion-builder' ),
						'description'  => esc_attr__( 'Needs to be a unique identifier (lowercase), used for button or modal_text_link element to open the modal. ex: mymodal.', 'fusion-builder' ),
						'param_name'   => 'name',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Modal Heading', 'fusion-builder' ),
						'description'  => esc_attr__( 'Heading text for the modal.', 'fusion-builder' ),
						'param_name'   => 'title',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Size Of Modal', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the modal window size.', 'fusion-builder' ),
						'param_name'  => 'size',
						'value'       => [
							'small' => esc_attr__( 'Small', 'fusion-builder' ),
							'large' => esc_attr__( 'Large', 'fusion-builder' ),
						],
						'default'     => 'small',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the modal background color. ', 'fusion-builder' ),
						'param_name'  => 'background',
						'value'       => '',
						'default'     => $fusion_settings->get( 'modal_bg_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the modal border color. ', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'modal_border_color' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Footer', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show the modal footer with close button.', 'fusion-builder' ),
						'param_name'  => 'show_footer',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'         => 'tinymce',
						'heading'      => esc_attr__( 'Contents of Modal', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add your content to be displayed in modal.', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_modal' );

/**
 * Map shortcode to Avada Builder
 */
function fusion_element_modal_link() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_ModalTextLink',
			[
				'name'          => esc_attr__( 'Modal Text / HTML Link', 'fusion-builder' ),
				'shortcode'     => 'fusion_modal_text_link',
				'icon'          => 'fusiona-external-link',
				'help_url'      => 'https://theme-fusion.com/documentation/avada/elements/modal-text-html-link-element/',
				'inline_editor' => true,
				'params'        => [
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Name Of Modal', 'fusion-builder' ),
						'description'  => esc_attr__( 'Unique identifier of the modal to open on click.', 'fusion-builder' ),
						'param_name'   => 'name',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'         => 'textarea',
						'heading'      => esc_attr__( 'Text or HTML code', 'fusion-builder' ),
						'description'  => esc_attr__( 'Insert text or HTML code here (e.g: HTML for image). This content will be used to trigger the modal popup.', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_modal_link' );

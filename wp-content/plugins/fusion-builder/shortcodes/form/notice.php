<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_notice' ) ) {

	if ( ! class_exists( 'FusionForm_Notice' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_Notice extends Fusion_Form_Component {

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
				parent::__construct( 'fusion_form_notice' );
				add_filter( 'fusion_attr_form-notice-shortcode', [ $this, 'attr' ] );
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
					'error'         => '',
					'success'       => '',
					'class'         => '',
					'id'            => '',
					'margin_bottom' => '',
					'margin_left'   => '',
					'margin_right'  => '',
					'margin_top'    => '',
				];
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

				$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, $this->shortcode_handle );

				$this->args['success'] = fusion_decode_if_needed( $this->args['success'] );
				$this->args['error']   = fusion_decode_if_needed( $this->args['error'] );

				$html = '<div ' . FusionBuilder::attributes( 'form-notice-shortcode' ) . '>';

				$this->params = $this->get_form_data();

				// If post, notices are up to user when processing data.
				if ( 'post' === $this->params['form_meta']['form_type'] ) {
					$html .= '<input type="hidden" value="fusion-notices-' . $this->counter . '" name="form_notices" />';
					ob_start();
					do_action( 'fusion_form_post_notice', $this->args );
					$html .= ob_get_contents();
					ob_get_clean();
				} else {

					// Not default we render and then show appropriate on submit.
					$html .= $this->render_notice( $this->args['success'], 'success' );
					$html .= $this->render_notice( $this->args['error'], 'error' );
				}

				$html .= '</div>';

				$this->on_render();

				return apply_filters( 'fusion_form_component_' . $this->shortcode_handle . '_content', $html, $args );
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
					'class' => 'form-submission-notices',
					'id'    => 'fusion-notices-' . $this->counter,
				];

				if ( '' !== $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( '' !== $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;

			}

			/**
			 * Renders form submission notice.
			 *
			 * @since 3.1
			 * @access private
			 * @param string $notice      The submission notice.
			 * @param string $notice_type Can be error|success.
			 * @return string The form submission notices.
			 */
			private function render_notice( $notice, $notice_type ) {

				// If form was not sent yet, $notice will be empty, so return early.
				if ( ! $notice ) {
					return '';
				}

				if ( class_exists( 'FusionSC_Alert' ) ) {
					$shortcode = '[fusion_alert type="' . $notice_type . '" class="fusion-form-response fusion-form-response-' . $notice_type . '" ';
					foreach ( [ 'margin_top', 'margin_right', 'margin_bottom', 'margin_left' ] as $param ) {
						if ( isset( $this->args[ $param ] ) && '' !== $this->args[ $param ] ) {
							$shortcode .= $param . '="' . $this->args[ $param ] . '" ';
						}
					}
					$shortcode .= ']' . $notice . '[/fusion_alert]';
					$notice     = do_shortcode( $shortcode );
				} else {
					$notice = '<div class="fusion-form-response fusion-form-response-' . $notice_type . '">' . $notice . '</div>';
				}

				return apply_filters( 'fusion_form_notice', $notice, $notice_type );
			}
		}
	}

	new FusionForm_Notice();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_notice() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Notice',
			[
				'name'           => esc_attr__( 'Notice', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_notice',
				'icon'           => 'fusiona-exclamation-triangle',
				'form_component' => true,
				'preview'        => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-form-element-preview.php',
				'preview_id'     => 'fusion-builder-block-module-form-element-preview-template',
				'params'         => [
					[
						'type'        => 'raw_textarea',
						'heading'     => esc_attr__( 'Success Message', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter a message to be shown when the form has been successfully submitted.', 'fusion-builder' ),
						'param_name'  => 'success',
						'value'       => 'Thank you for your message. It has been sent.',
					],
					[
						'type'        => 'raw_textarea',
						'heading'     => esc_attr__( 'Error Message', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter a message to be shown when a problem has been encountered while submitting the form.', 'fusion-builder' ),
						'param_name'  => 'error',
						'value'       => 'There was an error trying to send your message. Please try again later.',
					],
					'fusion_margin_placeholder' => [
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'param_name' => 'margin',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
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
add_action( 'fusion_builder_before_init', 'fusion_form_notice' );

<?php
/**
 * Avada-Builder Shortcode Element.
 *
 * @package Avada-Core
 * @since 3.5.2
 */

if ( function_exists( 'fusion_is_element_enabled' ) && fusion_is_element_enabled( 'fusion_privacy' ) ) {

	if ( ! class_exists( 'FusionSC_Privacy' ) && class_exists( 'Fusion_Element' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-core
		 * @since 3.5.2
		 */
		class FusionSC_Privacy extends Fusion_Element {

			/**
			 * Element counter, used for CSS.
			 *
			 * @since 3.5.2
			 * @var int $args
			 */
			private $privacy_counter = 0;

			/**
			 * Posted data if set.
			 *
			 * @since 3.5.2
			 * @var array
			 */
			private $data = false;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @static
			 * @access public
			 * @since 3.5.2
			 * @var array
			 */
			public static $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.5.2
			 */
			public function __construct() {

				parent::__construct();

				add_action( 'template_redirect', [ $this, 'save_consents' ] );
				add_filter( 'fusion_attr_privacy-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_privacy-content', [ $this, 'content_attr' ] );
				add_filter( 'fusion_attr_privacy-form', [ $this, 'form_attr' ] );
				add_shortcode( 'fusion_privacy', [ $this, 'render' ] );
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
					'animation_direction' => 'left',
					'animation_offset'    => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'     => '',
					'animation_type'      => '',
					'class'               => '',
					'form_field_layout'   => 'stacked',
					'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
					'id'                  => '',
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
					'embed_types'   => Avada()->settings->get( 'privacy_embed_types' ),
					'button_string' => esc_attr__( 'Update', 'fusion-core' ),
				];
			}

			/**
			 * Maps settings to extra variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_extras() {

				return [
					'privacy_embed_types' => 'embed_types',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.5.2
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				global $fusion_library;

				$defaults = apply_filters(
					'fusion_privacy_default_parameter',
					FusionBuilder::set_shortcode_defaults(
						self::get_element_defaults(),
						$args,
						'fusion_privacy'
					)
				);

				$content    = apply_filters( 'fusion_shortcode_content', $content, 'fusion_privacy', $args );
				self::$args = $defaults;

				$this->privacy_counter++;

				$html = '<div ' . FusionBuilder::attributes( 'privacy-shortcode' ) . '>';

				$html .= '<div ' . FusionBuilder::attributes( 'privacy-content' ) . '>' . wpautop( $content, false ) . '</div>';

				if ( class_exists( 'Avada_Privacy_Embeds' ) && Avada()->settings->get( 'privacy_embeds' ) ) {
					$html .= $this->privacy_embed_form();
				}

				$html .= '</div>';

				$this->on_render();

				return apply_filters( 'fusion_element_privacy_content', $html, $args );
			}

			/**
			 * Gets the HTML for the privacy embed form.
			 *
			 * @access public
			 * @since 3.5.2
			 * @return string
			 */
			public function privacy_embed_form() {
				$html  = '';
				$html .= $this->get_alert();

				$embeds   = Avada()->privacy_embeds->get_embed_types();
				$consents = Avada()->privacy_embeds->get_consents();

				if ( is_array( $embeds ) ) {
					$html .= '<form ' . FusionBuilder::attributes( 'privacy-form' ) . '>';
					$html .= '<ul>';

					// Loop each embed type and add a checkbox.
					foreach ( $embeds as $id => $embed ) {
						$selected = Avada()->privacy_embeds->is_selected( $id ) ? 'checked' : '';

						$html .= '<li>';
						$html .= '<label for="' . $id . '">';
						$html .= '<input name="consents[]" type="checkbox" value="' . $id . '" ' . $selected . ' id="' . $id . '">';
						$html .= $embed['label'];
						$html .= '</label>';
						$html .= '</li>';
					}

					$html .= '</ul>';
					$html .= wp_referer_field( false );
					$html .= '<input type="hidden" name="privacyformid" value="' . $this->privacy_counter . '">';
					$html .= '<input type="hidden" name="consents[]" value="consent">';
					$html .= '<input class="fusion-button fusion-button-default fusion-button-default-size" type="submit" value="' . esc_attr__( 'Update', 'fusion-core' ) . '" >';
					$html .= '</form>';
				}
				return $html;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.5.2
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					self::$args['hide_on_mobile'],
					[
						'class' => 'fusion-privacy-element fusion-privacy-element-' . $this->privacy_counter,
					]
				);

				// Add custom class.
				if ( self::$args['class'] ) {
					$attr['class'] .= ' ' . self::$args['class'];
				}

				// Add custom id.
				if ( self::$args['id'] ) {
					$attr['id'] = self::$args['id'];
				}

				// Add animation classes.
				if ( self::$args['animation_type'] ) {
					$animations = FusionBuilder::animations(
						[
							'type'      => self::$args['animation_type'],
							'direction' => self::$args['animation_direction'],
							'speed'     => self::$args['animation_speed'],
							'offset'    => self::$args['animation_offset'],
						]
					);

					$attr = array_merge( $attr, $animations );

					$attr['class'] .= ' ' . $attr['animation_class'];
					unset( $attr['animation_class'] );
				}

				return $attr;
			}

			/**
			 * Builds the attributes array for the content div.
			 *
			 * @access public
			 * @since 3.5.2
			 * @return array
			 */
			public function content_attr() {

				$attr = [
					'class' => 'fusion-privacy-form-intro',
				];

				return $attr;
			}

			/**
			 * Builds the attributes array for the form.
			 *
			 * @access public
			 * @since 3.5.2
			 * @return array
			 */
			public function form_attr() {

				$attr = [
					'id'     => 'fusion-privacy-form-' . $this->privacy_counter,
					'action' => '',
					'method' => 'post',
					'class'  => 'fusion-privacy-form fusion-privacy-form-' . self::$args['form_field_layout'],
				];

				return $attr;
			}

			/**
			 * Save the consents if submitted.
			 *
			 * @since 3.5.2
			 * @return void
			 */
			public function save_consents() {

				if ( isset( $_POST ) && isset( $_POST['privacyformid'] ) ) { // phpcs:ignore WordPress.Security

					$query_args = [
						'success' => 1,
						'id'      => (int) $_POST['privacyformid'], // phpcs:ignore WordPress.Security
					];

					if ( isset( $_POST['consents'] ) ) { // phpcs:ignore WordPress.Security
						Avada()->privacy_embeds->save_cookie( array_map( 'esc_attr', wp_unslash( $_POST['consents'] ) ) ); // phpcs:ignore WordPress.Security
					} else {
						Avada()->privacy_embeds->clear_cookie();
						$query_args['success'] = 2;
					}

					if ( isset( $_POST['_wp_http_referer'] ) ) { // phpcs:ignore WordPress.Security
						$redirection_link = wp_unslash( $_POST['_wp_http_referer'] ); // phpcs:ignore WordPress.Security
						$redirection_link = add_query_arg( $query_args, $redirection_link );
						wp_safe_redirect( $redirection_link );
					}
				}
			}

			/**
			 * Get the alert markup.
			 *
			 * @since 3.5.2
			 * @return string The alert.
			 */
			public function get_alert() {
				$alert = '';

				if ( isset( $_GET ) && isset( $_GET['success'] ) && isset( $_GET['id'] ) && $this->privacy_counter === (int) $_GET['id'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					if ( 1 === (int) $_GET['success'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						if ( shortcode_exists( 'fusion_alert' ) ) {
							$alert = do_shortcode( '[fusion_alert type="success"]' . esc_html__( 'Your embed preferences have been updated.', 'fusion-core' ) . '[/fusion_alert]' );
						} else {
							$alert = '<h3 style="color:#468847;">' . esc_html__( 'Your embed preferences have been updated.', 'fusion-core' ) . '</h3>';
						}
					} else {
						if ( shortcode_exists( 'fusion_alert' ) ) {
							$alert = do_shortcode( '[fusion_alert type="success"]' . esc_html__( 'Your embed preferences have been cleared.', 'fusion-core' ) . '[/fusion_alert]' );
						} else {
							$alert = '<h3 style="color:#b94a48;">' . esc_html__( 'Your embed preferences have been cleared.', 'fusion-core' ) . '</h3>';
						}
					}
				}

				return $alert;
			}
		}
	}

	new FusionSC_Privacy();
}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 3.5.2
 */
function fusion_element_privacy() {

	global $pagenow;
	$fusion_settings = awb_get_fusion_settings();
	if ( class_exists( 'Avada_Privacy_Embeds' ) && $fusion_settings->get( 'privacy_embeds' ) && function_exists( 'fusion_builder_map' ) && function_exists( 'fusion_builder_frontend_data' ) ) {
		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionSC_Privacy',
				[
					'name'       => esc_attr__( 'Privacy', 'fusion-core' ),
					'shortcode'  => 'fusion_privacy',
					'icon'       => 'fusiona-privacy',
					'preview'    => FUSION_CORE_PATH . '/shortcodes/previews/fusion-privacy-preview.php',
					'preview_id' => 'fusion-builder-block-module-privacy-preview-template',
					'front-end'  => FUSION_CORE_PATH . '/shortcodes/previews/front-end/fusion-privacy.php',
					'params'     => [
						[
							'type'        => 'tinymce',
							'heading'     => esc_attr__( 'Privacy Text', 'fusion-core' ),
							'description' => esc_attr__( 'Controls the privacy text which will show above the form.', 'fusion-core' ),
							'param_name'  => 'element_content',
							'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-core' ),
							'placeholder' => true,
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Form Field Layout', 'fusion-core' ),
							'description' => esc_attr__( 'Choose if form checkboxes should be stacked and full width, or if they should be floated.', 'fusion-core' ),
							'param_name'  => 'form_field_layout',
							'value'       => [
								'stacked' => esc_attr__( 'Stacked', 'fusion-core' ),
								'floated' => esc_attr__( 'Floated', 'fusion-core' ),
							],
							'default'     => 'stacked',
						],
						'fusion_animation_placeholder' => [
							'preview_selector' => '.fusion-privacy-element',
						],
						[
							'type'        => 'checkbox_button_set',
							'heading'     => esc_attr__( 'Element Visibility', 'fusion-core' ),
							'param_name'  => 'hide_on_mobile',
							'value'       => fusion_builder_visibility_options( 'full' ),
							'default'     => fusion_builder_default_visibility( 'array' ),
							'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-core' ),
						],
						[
							'type'        => 'textfield',
							'heading'     => esc_attr__( 'CSS Class', 'fusion-core' ),
							'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-core' ),
							'param_name'  => 'class',
							'value'       => '',
							'group'       => esc_attr__( 'General', 'fusion-core' ),
						],
						[
							'type'        => 'textfield',
							'heading'     => esc_attr__( 'CSS ID', 'fusion-core' ),
							'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-core' ),
							'param_name'  => 'id',
							'value'       => '',
							'group'       => esc_attr__( 'General', 'fusion-core' ),
						],
					],
				]
			)
		);
	}
}
add_action( 'wp_loaded', 'fusion_element_privacy' );

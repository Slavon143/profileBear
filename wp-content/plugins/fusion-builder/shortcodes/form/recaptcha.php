<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_recaptcha' ) ) {

	if ( ! class_exists( 'FusionForm_Recaptcha' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_Recaptcha extends Fusion_Form_Component {

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
				add_filter( 'fusion_attr_recaptcha-shortcode', [ $this, 'attr' ] );

				parent::__construct( 'fusion_form_recaptcha' );
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 */
			public function on_first_render() {

				// On first render is also called for live editor so when you add the element.  We don't need that here.
				if ( null === $this->args || empty( $this->args ) ) {
					return;
				}

				// Add reCAPTCHA script.
				$this->enqueue_scripts();
				add_action( 'wp_footer', [ $this, 'recaptcha_callback' ] );
			}

			/**
			 * Generate reCaptcha callback
			 *
			 * @access public
			 * @since 3.2
			 */
			public function recaptcha_callback() {
				?>
				<script type='text/javascript'>
					var active_captcha = [];

					var fusionOnloadCallback = function () {
						grecaptcha.ready( function () {
							jQuery( '.g-recaptcha-response' ).each( function () {
								var $el        = jQuery( this ),
									$container = $el.parent().find( 'div.recaptcha-container' ),
									id         = $container.attr( 'id' ),
									renderId;

								if ( 0 === $container.length || 'undefined' !== typeof active_captcha[ id ] || ( 1 === jQuery( '.fusion-modal' ).find( $container ).length && $container.closest( '.fusion-modal' ).is( ':hidden' ) ) ) {
									return;
								}

								renderId = grecaptcha.render(
									id,
									{
										sitekey: $container.data( 'sitekey' ),
										badge: $container.data( 'badge' ),
										size: 'invisible'
									}
								);

								active_captcha[ id ] = renderId;

								grecaptcha.execute( renderId, { action: 'contact_form' } ).then( function ( token ) {
									$el.val( token );
								});
							});
						});
					};
				</script>
				<?php
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
				$fusion_settings = awb_get_fusion_settings();
				return [
					'color_theme'    => $fusion_settings->get( 'recaptcha_color_scheme' ),
					'badge_position' => $fusion_settings->get( 'recaptcha_badge_position' ),
					'tab_index'      => '',
					'class'          => '',
					'id'             => '',
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
				$fusion_settings = awb_get_fusion_settings();

				ob_start();
				?>
				<?php if ( $fusion_settings->get( 'recaptcha_public' ) && $fusion_settings->get( 'recaptcha_private' ) ) : ?>
					<div <?php echo FusionBuilder::attributes( 'recaptcha-shortcode' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> >
						<?php if ( 'v2' === $fusion_settings->get( 'recaptcha_version' ) ) : ?>
							<div
								id="g-recaptcha-<?php echo esc_attr( $this->params['form_number'] . '-' . $this->counter ); ?>"
								class="fusion-form-recaptcha-v2"
								data-theme="<?php echo esc_attr( $this->args['color_theme'] ); ?>"
								data-sitekey="<?php echo esc_attr( $fusion_settings->get( 'recaptcha_public' ) ); ?>"
								data-tabindex="<?php echo esc_attr( $this->args['tab_index'] ); ?>">
							</div>
						<?php else : ?>
							<?php $hide_badge_class = 'hide' === $this->args['badge_position'] ? ' fusion-form-hide-recaptcha-badge' : ''; ?>
							<div
								id="g-recaptcha-<?php echo esc_attr( $this->counter ); ?>"
								class="fusion-form-recaptcha-v3 recaptcha-container <?php echo esc_attr( $hide_badge_class ); ?>"
								data-sitekey="<?php echo esc_attr( $fusion_settings->get( 'recaptcha_public' ) ); ?>"
								data-badge="<?php echo esc_attr( $this->args['badge_position'] ); ?>">
							</div>
							<input
								type="hidden"
								name="fusion-form-recaptcha-response"
								class="g-recaptcha-response"
								id="fusion-form-recaptcha-response-<?php echo esc_attr( $this->counter ); ?>"
								value="">
						<?php endif; ?>
					</div>
				<?php elseif ( is_user_logged_in() && current_user_can( 'manage_options' ) ) : ?>
						<div class="fusion-builder-placeholder"><?php echo esc_html__( 'reCAPTCHA configuration error. Please check the Global Options settings and your reCAPTCHA account settings.', 'fusion-builder' ); ?></div>
				<?php endif; ?>
				<?php
				$recaptcha_content = ob_get_clean();

				return $recaptcha_content;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public function attr() {

				$attr = [
					'class' => 'form-creator-recaptcha',
				];

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public static function get_element_extras() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'recaptcha_public'         => $fusion_settings->get( 'recaptcha_public' ),
					'recaptcha_private'        => $fusion_settings->get( 'recaptcha_private' ),
					'recaptcha_version'        => $fusion_settings->get( 'recaptcha_version' ),
					'recaptcha_badge_position' => $fusion_settings->get( 'recaptcha_badge_position' ),
				];
			}

			/**
			 * Maps settings to extra variables.
			 *
			 * @static
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public static function settings_to_extras() {

				return [
					'recaptcha_public'         => 'recaptcha_public',
					'recaptcha_private'        => 'recaptcha_private',
					'recaptcha_version'        => 'recaptcha_version',
					'recaptcha_badge_position' => 'recaptcha_badge_position',
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
					'recaptcha_color_scheme'   => 'color_theme',
					'recaptcha_badge_position' => 'badge_position',
				];
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.1
			 * @return void
			 */
			public function enqueue_scripts() {
				$fusion_settings = awb_get_fusion_settings();

				if ( $fusion_settings->get( 'recaptcha_public' ) && $fusion_settings->get( 'recaptcha_private' ) && ! function_exists( 'recaptcha_get_html' ) && ! class_exists( 'ReCaptcha' ) ) {
					$recaptcha_script_uri = 'https://www.google.com/recaptcha/api.js?render=explicit&hl=' . get_locale() . '&onload=fusionOnloadCallback';
					if ( 'v2' === $fusion_settings->get( 'recaptcha_version' ) ) {
						$recaptcha_script_uri = 'https://www.google.com/recaptcha/api.js?hl=' . get_locale();
					}
					wp_enqueue_script( 'recaptcha-api', $recaptcha_script_uri, [], FUSION_BUILDER_VERSION, false );
				}
			}
		}
	}

	new FusionForm_Recaptcha();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_recaptcha() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Recaptcha',
			[
				'name'           => esc_attr__( 'reCAPTCHA Field', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_recaptcha',
				'icon'           => 'fusiona-af-recaptcha',
				'form_component' => true,
				'preview'        => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-form-element-preview.php',
				'preview_id'     => 'fusion-builder-block-module-form-element-preview-template',
				'params'         => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'reCAPTCHA Color Scheme', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the reCAPTCHA color scheme.', 'fusion-builder' ),
						'param_name'  => 'color_theme',
						'default'     => '',
						'value'       => [
							''      => esc_attr__( 'Default', 'fusion-builder' ),
							'light' => esc_attr__( 'Light', 'fusion-builder' ),
							'dark'  => esc_attr__( 'Dark', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'reCAPTCHA Badge Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose where the reCAPTCHA badge should be displayed.', 'fusion-builder' ),
						'param_name'  => 'badge_position',
						'default'     => '',
						'value'       => [
							''            => esc_attr__( 'Default', 'fusion-builder' ),
							'inline'      => esc_attr__( 'Inline', 'fusion-builder' ),
							'bottomleft'  => esc_attr__( 'Bottom Left', 'fusion-builder' ),
							'bottomright' => esc_attr__( 'Bottom Right', 'fusion-builder' ),
							'hide'        => esc_attr__( 'Hide', 'fusion-builder' ),
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
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_form_recaptcha' );

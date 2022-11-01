<?php
/**
 * Handled the options panel for Element Options.
 *
 * @package fusion-builder
 * @subpackage Options
 * @since 1.1.0
 */

/**
 * Instantiates Fusion Options
 * and takes care of its options.
 */
class Fusion_Builder_Options_Panel {

	/**
	 * The arguments we'll be passing-on to the options-framework.
	 *
	 * @access protected
	 * @since 1.1.0
	 * @var array
	 */
	protected $args = [];

	/**
	 * The Fusion_Builder_Redux object.
	 *
	 * @access private
	 * @since 1.1.0
	 * @var object
	 */
	private $fusion_builder_redux;

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {

		$this->includes();

		// Set vars for i18n handling.
		$option_name      = Fusion_Settings::get_option_name();
		$is_language_all  = ( 'all' === Fusion_Multilingual::get_active_language() );
		$default_language = Fusion_Multilingual::get_default_language();
		if ( $is_language_all && 'fusion_options' === $option_name ) {
			$option_name = $option_name . '_' . $default_language;
		}

		$this->args = [
			'sections'             => Fusion_Builder_Options::get_instance(),
			'is_language_all'      => $is_language_all,
			'default_language'     => $default_language,
			'option_name'          => $option_name,
			'original_option_name' => Fusion_Settings::get_original_option_name(),
			'version'              => FUSION_BUILDER_VERSION,
			'textdomain'           => 'fusion-builder',
			'disable_dependencies' => false,
			'display_name'         => 'Avada Builder',
			'menu_title'           => esc_attr__( 'Element Options', 'fusion-builder' ),
			'page_title'           => esc_attr__( 'Element Options', 'fusion-builder' ),
			'global_variable'      => 'fusion_fusionredux_options',
			'page_parent'          => 'fusion-builder-options',
			'page_slug'            => 'fusion-element-options',
			'menu_type'            => 'submenu',
			'page_permissions'     => 'manage_options',
		];

		if ( ! is_customize_preview() ) {
			require_once FUSION_BUILDER_PLUGIN_DIR . '/inc/class-fusion-builder-redux.php';

			// If the current theme doesn't have an integration with fusion-builder-options
			// Instantiate our admin options.
			if ( ! current_theme_supports( 'fusion-builder-options' ) ) {
				$this->init_redux();
			}
		}

		add_filter( 'fusion_admin_options_injection', [ $this, 'fusion_options_integration' ] );

	}

	/**
	 * Instantiates the options panel.
	 *
	 * @access public
	 * @since 1.1.0
	 */
	public function init_redux() {
		$this->fusion_builder_redux = new Fusion_Builder_Redux( $this->args );
	}

	/**
	 * Include any needed files.
	 *
	 * @access protected
	 * @since 1.1.0
	 */
	protected function includes() {

		if ( ! class_exists( 'Fusion_Builder_Options' ) ) {
			include_once FUSION_BUILDER_PLUGIN_DIR . '/inc/class-fusion-builder-options.php';
		}

	}

	/**
	 * Allows adding our options to another options panel.
	 *
	 * @access public
	 * @since 1.1.0
	 * @param object $sections The sections object.
	 * @return object
	 */
	public function fusion_options_integration( $sections ) {

		$fb_options       = Fusion_Builder_Options::get_instance();
		$element_options  = $fb_options->sections;
		$fields_array     = fusion_get_fields_array( $sections );
		$has_addons_class = '';

		// Options tweaks.
		$option_panels = [
			'shortcode_styling',
			'fusion_builder_addons',
		];
		if ( ! isset( $element_options['fusion_builder_addons'] ) ) {
			$has_addons_class = 'fusion-builder-no-addon-elements';
		}
		foreach ( $option_panels as $option_panel ) {
			if ( isset( $element_options[ $option_panel ] ) ) {
				ksort( $element_options[ $option_panel ]['fields'] );

				if ( current_theme_supports( 'fusion-builder-options' ) ) {
					foreach ( $element_options[ $option_panel ]['fields'] as $key => $value ) {
						$element_options[ $option_panel ]['fields'][ $key ]['type'] = 'accordion';
						if ( isset( $element_options[ $option_panel ]['fields'][ $key ]['fields'] ) &&
							is_array( $element_options[ $option_panel ]['fields'][ $key ]['fields'] ) ) {
							foreach ( $element_options[ $option_panel ]['fields'][ $key ]['fields'] as $field_key => $field_value ) {
								if ( is_array( $fields_array ) && in_array( $field_key, $fields_array ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict

									// If the field already exist somewhere in $sections, then don't add twice.
									unset( $element_options[ $option_panel ]['fields'][ $key ]['fields'][ $field_key ] );
									if ( empty( $element_options[ $option_panel ]['fields'][ $key ]['fields'] ) ) {

										// If the accordian is now empty, remove it.
										unset( $element_options[ $option_panel ]['fields'][ $key ] );
									}
								}
							}
						}
					}
				}
			}
		}
		$new_options['shortcode_styling']                                      = [
			'label'    => esc_html__( 'Avada Builder Elements', 'fusion-builder' ),
			'id'       => 'shortcode_styling',
			'is_panel' => 'true',
			'class'    => $has_addons_class,
			'priority' => 14,
			'icon'     => 'el-icon-check',
			'fields'   => [],
		];
		$new_options['shortcode_styling']['fields']['fusion_builder_elements'] = [
			'label'    => esc_html__( 'Avada Builder Elements', 'fusion-builder' ),
			'id'       => 'fusion_builder_elements',
			'type'     => 'sub-section',
			'priority' => 14,
			'fields'   => $element_options['shortcode_styling']['fields'],
		];
		if ( isset( $element_options['fusion_builder_addons'] ) ) {
			$new_options['shortcode_styling']['fields']['fusion_builder_addons'] = [
				'label'    => esc_html__( 'Add-on Elements', 'fusion-builder' ),
				'id'       => 'fusion_builder_addons',
				'type'     => 'sub-section',
				'priority' => 14,
				'fields'   => array_merge(
					[
						'fusion_builder_addons_important_note_info' => [
							'label'       => '',
							'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> This panel holds element options for any Avada Builder Add-on you have purchased from a 3rd party source. These are not made by ThemeFusion. If you require support for these elements, please contact the individual Add-on creator you purchased them from.', 'fusion-builder' ) . '</div>',
							'id'          => 'fusion_builder_addons_important_note_info',
							'type'        => 'custom',
						],
					],
					$element_options['fusion_builder_addons']['fields']
				),
			];
		}

		$sections->sections['shortcode_styling'] = $new_options['shortcode_styling'];

		return $sections;
	}

	/**
	 * Gets the $fusion_builder_redux private property.
	 *
	 * @access public
	 * @since 1.1.0
	 * @return object
	 */
	public function get_fusion_builder_redux() {
		if ( null === $this->fusion_builder_redux ) {
			$this->init_redux();
		}
		return $this->fusion_builder_redux;
	}
}

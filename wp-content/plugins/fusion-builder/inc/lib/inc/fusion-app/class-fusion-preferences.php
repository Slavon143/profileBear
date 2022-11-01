<?php
/**
 * Main Fusion_Preferences Class.
 *
 * @since 2.0
 * @package fusion-library
 */

/**
 * Main Fusion_Preferences Class.
 *
 * @since 2.0
 */
class Fusion_Preferences {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 2.0
	 * @var object
	 */
	private static $instance;

	/**
	 * An array of preferences.
	 *
	 * @static
	 * @access public
	 * @var null|object
	 */
	public static $preferences = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new Fusion_Preferences();
		}
		return self::$instance;
	}

	/**
	 * Initializes the plugin by setting localization, hooks, filters,
	 * and administrative functions.
	 *
	 * @access private
	 * @since 2.0
	 */
	public function __construct() {
		if ( ! $this->has_capability() ) {
			return;
		}

		$this->init();
	}

	/**
	 * Initializes the plugin by setting localization, hooks, filters,
	 * and administrative functions.
	 *
	 * @access public
	 * @since 2.0
	 */
	public function init() {
		$this->set_preferences();

		// Save builder preferences.
		add_action( 'wp_ajax_fusion_app_save_builder_preferences', [ $this, 'save_preferences' ] );
	}

	/**
	 * Checks if user should see builder.
	 *
	 * @access public
	 * @since 2.0
	 */
	public function has_capability() {
		return is_user_logged_in();
	}

	/**
	 * Sets Avada Builder front-end preferences.
	 *
	 * @access public
	 * @since 2.0
	 */
	public function set_preferences() {
		$db_preference = get_option( 'fusion_builder_frontend_preferences' );

		if ( false === $db_preference ) {
			self::$preferences = [];
		} else {
			self::$preferences = $db_preference;
		}
	}

	/**
	 * Gets Avada Builder front-end preferences.
	 *
	 * @access public
	 * @since 2.0
	 */
	public function get_preferences() {
		return self::$preferences;
	}

	/**
	 * Save Avada Builder front-end preferences.
	 *
	 * @access public
	 * @since 2.0
	 */
	public function save_preferences() {
		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

		if ( isset( $_POST['preferences'] ) ) {
			$prferences_data = wp_unslash( $_POST['preferences'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			update_option( 'fusion_builder_frontend_preferences', $prferences_data );
			$this->set_preferences();
			echo wp_json_encode( $this->params(), JSON_FORCE_OBJECT );
			die();
		}
	}

	/**
	 * This method is used to get fusion builder preferences options.
	 *
	 * @access public
	 * @since 3.0.10
	 */
	public function params() {

		$params = [
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_html__( 'Builder Styling Mode', 'fusion-builder' ),
				'description' => esc_html__( 'Choose if you want a light or dark builder UI style.', 'fusion-builder' ),
				'param_name'  => 'styling_mode',
				'value'       => [
					'light' => esc_html__( 'Light', 'fusion-builder' ),
					'dark'  => esc_html__( 'Dark', 'fusion-builder' ),
				],
				'default'     => ( isset( self::$preferences['styling_mode'] ) ? self::$preferences['styling_mode'] : 'light' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_html__( 'Sidebar Panel Position', 'fusion-builder' ),
				'description' => esc_html__( 'Select the side which you want the sidebar panel to be displayed on.', 'fusion-builder' ),
				'param_name'  => 'sidebar_position',
				'value'       => [
					'left'  => esc_html__( 'Left', 'fusion-builder' ),
					'right' => esc_html__( 'Right', 'fusion-builder' ),
				],
				'default'     => ( isset( self::$preferences['sidebar_position'] ) ? self::$preferences['sidebar_position'] : 'left' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_html__( 'Element Editing Mode', 'fusion-builder' ),
				'description' => esc_html__( 'Choose if element editing should be in the sidebar or in dialogs.', 'fusion-builder' ),
				'param_name'  => 'editing_mode',
				'value'       => [
					'sidebar' => esc_html__( 'Sidebar', 'fusion-builder' ),
					'dialog'  => esc_html__( 'Dialog', 'fusion-builder' ),
				],
				'default'     => ( isset( self::$preferences['editing_mode'] ) ? self::$preferences['editing_mode'] : 'sidebar' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_html__( 'Automatically Open Element Settings', 'fusion-builder' ),
				'description' => esc_html__( 'Choose if the settings should be opened automatically after adding an element.', 'fusion-builder' ),
				'param_name'  => 'open_settings',
				'value'       => [
					'on'  => esc_html__( 'On', 'fusion-builder' ),
					'off' => esc_html__( 'Off', 'fusion-builder' ),
				],
				'default'     => ( isset( self::$preferences['open_settings'] ) ? self::$preferences['open_settings'] : 'on' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_html__( 'Enable Keyboard Shortcuts', 'fusion-builder' ),
				'description' => esc_html__( 'Choose if keyboard shortcuts should be enabled or disabled.', 'fusion-builder' ),
				'param_name'  => 'keyboard_shortcuts',
				'value'       => [
					'on'  => esc_html__( 'On', 'fusion-builder' ),
					'off' => esc_html__( 'Off', 'fusion-builder' ),
				],
				'default'     => ( isset( self::$preferences['keyboard_shortcuts'] ) ? self::$preferences['keyboard_shortcuts'] : 'on' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_html__( 'Show Option Descriptions By Default', 'fusion-builder' ),
				'description' => esc_html__( 'Choose if option descriptions should be shown or hidden by default.', 'fusion-builder' ),
				'param_name'  => 'descriptions',
				'value'       => [
					'show' => esc_html__( 'Show', 'fusion-builder' ),
					'hide' => esc_html__( 'Hide', 'fusion-builder' ),
				],
				'default'     => ( isset( self::$preferences['descriptions'] ) ? self::$preferences['descriptions'] : 'hide' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_html__( 'Show Tooltips', 'fusion-builder' ),
				'description' => esc_html__( 'Choose if tooltips should be enabled or disabled.', 'fusion-builder' ),
				'param_name'  => 'tooltips',
				'value'       => [
					'on'  => esc_html__( 'On', 'fusion-builder' ),
					'off' => esc_html__( 'Off', 'fusion-builder' ),
				],
				'default'     => ( isset( self::$preferences['tooltips'] ) ? self::$preferences['tooltips'] : 'on' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => __( 'Enable Sticky Header & Containers', 'fusion-builder' ),
				'description' => esc_html__( 'Choose if sticky header and sticky containers should be enabled or disabled.', 'fusion-builder' ),
				'param_name'  => 'sticky_header',
				'value'       => [
					'on'  => esc_html__( 'On', 'fusion-builder' ),
					'off' => esc_html__( 'Off', 'fusion-builder' ),
				],
				'default'     => ( isset( self::$preferences['sticky_header'] ) ? self::$preferences['sticky_header'] : 'on' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => __( 'Enable Transparent Header & Absolute Containers', 'fusion-builder' ),
				'description' => esc_html__( 'Choose if transparent header and absolute containers should be enabled or disabled.', 'fusion-builder' ),
				'param_name'  => 'transparent_header',
				'value'       => [
					'on'  => esc_html__( 'On', 'fusion-builder' ),
					'off' => esc_html__( 'Off', 'fusion-builder' ),
				],
				'default'     => ( isset( self::$preferences['transparent_header'] ) ? self::$preferences['transparent_header'] : 'on' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_html__( 'Enable Preview for Filter Options', 'fusion-builder' ),
				'description' => esc_html__( 'Choose if element filter options preview should be enabled or disabled.', 'fusion-builder' ),
				'param_name'  => 'element_filters',
				'value'       => [
					'on'  => esc_html__( 'On', 'fusion-builder' ),
					'off' => esc_html__( 'Off', 'fusion-builder' ),
				],
				'default'     => ( isset( self::$preferences['element_filters'] ) ? self::$preferences['element_filters'] : 'on' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_html__( 'Enable Preview for Transform Options', 'fusion-builder' ),
				'description' => esc_html__( 'Choose if element transform options preview.', 'fusion-builder' ),
				'param_name'  => 'element_transform',
				'value'       => [
					'always'  => esc_html__( 'Always', 'fusion-builder' ),
					'editing' => esc_html__( 'Editing', 'fusion-builder' ),
					'never'   => esc_html__( 'Never', 'fusion-builder' ),
				],
				'default'     => ( isset( self::$preferences['element_transform'] ) ? self::$preferences['element_transform'] : 'editing' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_html__( 'Show Droppable Areas While Dragging', 'fusion-builder' ),
				'description' => esc_html__( 'Enable in order to see all dropppable areas while dragging element.', 'fusion-builder' ),
				'param_name'  => 'droppables_visible',
				'value'       => [
					'on'  => esc_html__( 'On', 'fusion-builder' ),
					'off' => esc_html__( 'Off', 'fusion-builder' ),
				],
				'default'     => ( isset( self::$preferences['droppables_visible'] ) ? self::$preferences['droppables_visible'] : 'off' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_html__( 'Sidebar Panel Overlay Mode', 'fusion-builder' ),
				'description' => esc_html__( 'Choose if the sidebar panel should act as an overlay.  If enabled, the sidebar will overlay the preview content.', 'fusion-builder' ),
				'param_name'  => 'sidebar_overlay',
				'value'       => [
					'on'  => esc_html__( 'On', 'fusion-builder' ),
					'off' => esc_html__( 'Off', 'fusion-builder' ),
				],
				'default'     => ( isset( self::$preferences['sidebar_overlay'] ) ? self::$preferences['sidebar_overlay'] : 'off' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_html__( 'Options Sub Tabs', 'fusion-builder' ),
				'description' => esc_html__( 'Choose if options sub tabs expanded or collapsed.', 'fusion-builder' ),
				'param_name'  => 'options_subtabs',
				'value'       => [
					'expanded'  => esc_html__( 'Expanded', 'fusion-builder' ),
					'collapsed' => esc_html__( 'Collapsed', 'fusion-builder' ),
				],
				'default'     => ( isset( self::$preferences['options_subtabs'] ) ? self::$preferences['options_subtabs'] : 'expanded' ),
			],
		];

		return $params;
	}
}

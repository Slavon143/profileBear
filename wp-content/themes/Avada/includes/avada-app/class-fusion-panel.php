<?php
/**
 * The main Fusion_Panel class.
 *
 * @since 6.0
 * @package Avada
 */

/**
 * Main Fusion_Panel Class.
 *
 * @since 6.0
 */
class Fusion_Panel {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 6.0
	 * @var object
	 */
	private static $instance;

	/**
	 * All options.
	 *
	 * @access protected
	 * @since 6.0
	 * @var array
	 */
	protected $options = [];

	/**
	 * All settings.
	 *
	 * @access protected
	 * @since 6.0
	 * @var array
	 */
	protected $settings = [];

	/**
	 * All page options.
	 *
	 * @access protected
	 * @since 6.0
	 * @var array
	 */
	public $page_options = [];

	/**
	 * All page settings.
	 *
	 * @access protected
	 * @since 6.0
	 * @var array
	 */
	public $page_settings = [];

	/**
	 * All Avada-Builder options.
	 *
	 * @access public
	 * @since 6.0
	 * @var array
	 */
	public $fusion_builder_options = [];

	/**
	 * All page values.
	 *
	 * @access protected
	 * @since 6.0
	 * @var array
	 */
	public $page_values = [];

	/**
	 * All Avada-Builder flat options.
	 *
	 * @access public
	 * @since 6.0
	 * @var array
	 */
	public $flat_tos = [];

	/**
	 * Pause meta filtering.
	 *
	 * @access private
	 * @since 2.0.3
	 * @var bool
	 */
	private $filtering_paused = false;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new Fusion_Panel();
		}
		return self::$instance;
	}

	/**
	 * Initializes the plugin by setting localization, hooks, filters,
	 * and administrative functions.
	 *
	 * @access private
	 * @since 6.0
	 */
	private function __construct() {
		add_action( 'fusion_save_post', [ $this, 'fusion_panel_save' ] );

		// Add options.
		if ( $this->show_theme_options() ) {
			add_action( 'wp_loaded', [ $this, 'set_flat_options' ], 999 );
			add_action( 'wp_loaded', [ $this, 'get_options' ], 999 );
		}

		// Load TOs for all user roles.
		add_action( 'wp_loaded', [ $this, 'get_settings' ], 999 );

		// Parse options.
		add_action( 'wp_footer', [ $this, 'parse' ], 999 );

		// Import ajax.
		add_action( 'wp_ajax_fusion_panel_import', [ $this, 'ajax_import_options' ] );

		$this->option_values_mods();

		$this->init();
	}

	/**
	 * Initializes the plugin by setting localization, hooks, filters,
	 * and administrative functions.
	 *
	 * @access public
	 * @since 6.0
	 */
	public function init() {
		add_action( 'fusion_pause_meta_filter', [ $this, 'pause_meta_filter' ], 999 );
		add_action( 'fusion_resume_meta_filter', [ $this, 'resume_meta_filter' ], 999 );

		add_filter( 'fusion_app_preview_data', [ $this, 'add_panel_data' ], 10 );
		add_action( 'fusion_filter_data', [ $this, 'avada_options_filter' ] );

		// If preview frame.
		if ( fusion_is_preview_frame() ) {

			if ( ! Fusion_App()->is_full_refresh() ) {
				add_action( 'wp', [ $this, 'get_page_options' ], 999 );
			}
			add_action( 'wp_enqueue_scripts', [ $this, 'preview_scripts' ] );
		}
		if ( fusion_is_builder_frame() ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'live_scripts' ], 999 );
			add_action( 'wp_footer', [ $this, 'load_templates' ] );
			add_filter( 'body_class', [ $this, 'body_class' ], 998 );
		}
		$this->add_hook_wrappers();
	}

	/**
	 * Add necessary data for panel.
	 *
	 * @access public
	 * @since 6.0
	 * @param  array $data The data already added.
	 * @return array $data The data with panel data added.
	 */
	public function add_panel_data( $data ) {
		$data['postMeta']               = $this->page_values;
		$data['fusionPageOptions']      = apply_filters( 'awb_metaboxes_sections', $this->page_options );
		$data['fusionElementsOptions']  = $this->fusion_builder_options;
		$data['singular']               = is_singular() || ( class_exists( 'WooCommerce' ) && is_shop() ) || ( is_home() && ! is_front_page() );
		$data['featured_image_default'] = $this->get_featured_image_object();

		return $data;
	}

	/**
	 * Modifies values for the preview frame.
	 * This is used to disabled the compilers
	 * and enable us to work with media-queries a lot more efficiently.
	 *
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public function option_values_mods() {
		if ( fusion_is_preview_frame() ) {

			// Disable css-caching.
			add_filter(
				'avada_setting_get_css_cache_method',
				function() {
					return 'off';
				}
			);

			// Asyncronously load media-queries.
			add_filter(
				'avada_setting_get_media_queries_async',
				function() {
					return '1';
				}
			);

			// Disable JS compiler.
			add_filter(
				'avada_setting_get_js_compiler',
				function() {
					return '0';
				}
			);

			// Enable css-vars.
			add_filter(
				'avada_setting_get_css_vars',
				function() {
					return '1';
				}
			);
		}
	}

	/**
	 * Whether or not to show TO.
	 *
	 * @access public
	 * @since 6.0
	 * @return string
	 */
	public function show_theme_options() {
		return current_user_can( 'edit_theme_options' );
	}

	/**
	 * Whether or not to show PO.
	 *
	 * @since 6.0
	 * @param string $post_id Id of current post.
	 * @return string
	 */
	public function show_page_options( $post_id = '' ) {
		$post_id          = '' === $post_id ? Avada()->fusion_library->get_page_id() : $post_id;
		$post_type        = get_post_type( $post_id );
		$post_type_object = get_post_type_object( $post_type );
		return current_user_can( $post_type_object->cap->edit_post, $post_id );
	}

	/**
	 * Whether or not to show taxonomy options.
	 *
	 * @since 6.0
	 * @param string $term_id Id of current term.
	 * @return string
	 */
	public function show_tax_options( $term_id = '' ) {
		$term_id = $term_id && '' !== $term_id ? $term_id : (int) str_replace( 'archive-', '', fusion_library()->get_page_id() );

		// If this is an archive which is not editable.
		if ( 0 === $term_id ) {
			return false;
		}

		if ( '' === $term_id ) {
			$query_object = get_queried_object();
			if ( $query_object && ! is_wp_error( $query_object ) ) {
				$taxonomy_object = get_taxonomy( $query_object->taxonomy );
				return current_user_can( $taxonomy_object->cap->edit_terms, $query_object->term_id );
			}
		}

		$term = get_term( $term_id );
		if ( $term && ! is_wp_error( $term ) ) {
			$taxonomy_object = get_taxonomy( $term->taxonomy );
			return current_user_can( $taxonomy_object->cap->edit_terms, $term_id );
		}
	}

	/**
	 * Returns contents of json.
	 *
	 * @since 6.0
	 * @return string
	 */
	public function ajax_import_options() {

		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

		$wp_filesystem = Fusion_Helper::init_filesystem();
		$upload_dir    = wp_upload_dir();
		$dir_path      = wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . 'fusion-page-options-export/' );
		$dir_url       = trailingslashit( $upload_dir['baseurl'] ) . 'fusion-page-options-export/';
		$content_json  = false;

		// If its an uploaded file.
		if ( isset( $_FILES['po_file_upload'] ) ) {
			if ( ! isset( $_FILES['po_file_upload']['name'] ) ) {
				return false;
			}

			$json_file_path = wp_normalize_path( $dir_path . wp_unslash( $_FILES['po_file_upload']['name'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

			if ( ! file_exists( $dir_path ) ) {
				wp_mkdir_p( $dir_path );
			}

			if ( ! isset( $_FILES['po_file_upload'] ) || ! isset( $_FILES['po_file_upload']['tmp_name'] ) ) {
				return false;
			}
			// We're already checking if defined above.
			if ( ! $wp_filesystem->move( wp_normalize_path( $_FILES['po_file_upload']['tmp_name'] ), $json_file_path, true ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				return false;
			}

			$content_json = $wp_filesystem->get_contents( $json_file_path );

			$wp_filesystem->delete( $json_file_path );

		} elseif ( isset( $_POST['toUrl'] ) ) {
			$args = [
				'user-agent' => 'avada-user-agent',
			];

			$content_json = wp_remote_retrieve_body( wp_remote_get( esc_url( wp_unslash( $_POST['toUrl'] ) ), $args ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}

		echo wp_json_encode( $content_json );
		die();
	}

	/**
	 * Add panel body class.
	 *
	 * @since 6.0
	 * @access public
	 * @param array $classes classes being used.
	 * @return string
	 */
	public function body_class( $classes ) {
		$preferences = Fusion_App()->preferences->get_preferences();
		if ( isset( $preferences['styling_mode'] ) && 'dark' === $preferences['styling_mode'] ) {
			$classes[] = 'dark-mode';
		}
		$classes[] = 'fusion-builder-panel-main';
		return $classes;
	}

	/**
	 * Save global options and page options.
	 *
	 * @since 6.0
	 * @access public
	 */
	public function fusion_panel_save() {

		$app = Fusion_App();

		$post_id       = $app->get_data( 'post_id' );
		$theme_options = $app->get_data( 'fusion_options' );
		$meta_values   = $app->get_data( 'meta_values' );

		// Avada part.
		if ( is_array( $theme_options ) ) {
			if ( $this->show_theme_options() ) {
				$updated_to = update_option( Fusion_Settings::get_option_name(), $this->sanitize_fusion_options( $theme_options ) );
				if ( $updated_to ) {
					$app->add_save_data( 'theme_options', true, esc_html__( 'The Global Options updated.', 'Avada' ) );
				}
			} else {
				$app->add_save_data( 'theme_options', false, esc_html__( 'You do not have permission to update Global Options.', 'Avada' ) );
			}
		}

		if ( $post_id && '' !== $post_id ) {

			if ( is_array( $meta_values ) ) {

				if ( ! strpos( $post_id, '-archive' ) ) {

					if ( $this->show_page_options( $post_id ) ) {
						foreach ( $meta_values as $key => $value ) {
							if ( '_fusion' === $key ) {
								foreach ( $value as $_fusion_k => $_fusion_v ) {
									if ( ( '' === $_fusion_v || 'default' === $_fusion_v ) && 'form_type' !== $_fusion_k ) {
										unset( $value[ $_fusion_k ] );
									}
								}
							}

							if ( '_fusion_builder_custom_css' === $key ) {
								$value = wp_slash( str_replace( "\'", "'", $value ) );
							}

							update_post_meta( $post_id, $key, $value );
						}
						$app->add_save_data( 'page_options', true, esc_html__( 'The page options updated.', 'Avada' ) );
					} else {
						$app->add_save_data( 'page_options', false, esc_html__( 'You do not have permission to update the page options.', 'Avada' ) );
					}
				} else {

					// Archive, save term meta.
					$term_id = (int) str_replace( '-archive', '', $post_id );
					if ( 0 !== $term_id ) {
						if ( $this->show_tax_options( $term_id ) ) {

							// Update the fusion meta.
							$update_taxonomy_options = false;
							if ( isset( $meta_values['_fusion'] ) ) {
								$update_taxonomy_options = fusion_data()->term_meta( $term_id )->set_raw( $meta_values['_fusion'] );
							}

							// Update non-fusion meta.
							unset( $meta_values['_fusion'] );
							foreach ( $meta_values as $key => $val ) {
								update_term_meta( $term_id, $key, $val );
							}
							if ( $update_taxonomy_options ) {
								$app->add_save_data( 'tax_options', true, esc_html__( 'The taxonomy options updated.', 'Avada' ) );
							}
						} else {
							$app->add_save_data( 'tax_options', false, esc_html__( 'You do not have permission to update taxonomy options.', 'Avada' ) );
						}
					}
				}
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification
	}

	/**
	 * Load the template files.
	 *
	 * @since 6.0
	 * @access public
	 */
	public function load_templates() {
		// Panel.
		include Avada::$template_dir_path . '/includes/avada-app/panel/templates/sidebar.php';
		include Avada::$template_dir_path . '/includes/avada-app/panel/templates/panel.php';
		include Avada::$template_dir_path . '/includes/avada-app/panel/templates/tab.php';
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 6.0
	 * @access public
	 * @param mixed $hook The hook.
	 */
	public function preview_scripts( $hook ) {
		$version = Avada::get_theme_version();
		wp_enqueue_style( 'fusion_app_panel_preview_css', trailingslashit( Avada::$template_dir_url ) . 'includes/avada-app/panel/css/shortcuts.css', [], $version );

		wp_enqueue_script( 'fusion_avada_ptb_sliders', trailingslashit( Avada::$template_dir_url ) . 'includes/avada-app/ptb-and-slider.js', [], $version, true );

		wp_localize_script(
			'fusion_avada_ptb_sliders',
			'avadaPTBSlidersL10n',
			[
				'addPTB'               => esc_html__( 'Add Page TitleBar', 'Avada' ),
				'editPTB'              => esc_html__( 'Edit Global Page TitleBar', 'Avada' ),
				'editPTBOptions'       => esc_html__( 'Edit Page TitleBar', 'Avada' ),
				'editPTBLayoutSection' => esc_html__( 'Edit Page TitleBar Layout Section', 'Avada' ),
				'removePTB'            => esc_html__( 'Remove Page TitleBar', 'Avada' ),
				'addSlider'            => esc_html__( 'Add Slider', 'Avada' ),
				'editSlide'            => esc_html__( 'Edit Slide', 'Avada' ),
				'editSlider'           => esc_html__( 'Edit Slider', 'Avada' ),
				'editSliderOptions'    => esc_html__( 'Edit Slider Options', 'Avada' ),
				'removeSlider'         => esc_html__( 'Remove Slider', 'Avada' ),
				'editSelectedSlider'   => esc_html__( 'You have chosen to edit your selected slider. This operation must be performed from the WordPress dashboard.', 'Avada' ),
				'cancel'               => esc_html__( 'Cancel', 'Avada' ),
				'types'                => [
					'layer'   => esc_attr__( 'LayerSlider', 'Avada' ),
					'flex'    => esc_attr__( 'Avada Slider', 'Avada' ),
					'rev'     => esc_attr__( 'Slider Revolution', 'Avada' ),
					'elastic' => esc_attr__( 'Elastic Slider', 'Avada' ),
				],
			]
		);

	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 6.0
	 * @access public
	 * @param mixed $hook The hook.
	 */
	public function live_scripts( $hook ) {

		$version = Avada::get_theme_version();

		wp_enqueue_style( 'fusion_app_panel_css', trailingslashit( Avada::$template_dir_url ) . 'includes/avada-app/panel/css/panel.css', [], $version );

		// If we're not debugging, load the combined script.
		if ( ( ! defined( 'AVADA_DEV_MODE' ) || ! AVADA_DEV_MODE ) && ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) ) {
			wp_enqueue_script( 'fusion_avada_frontend_combined', trailingslashit( Avada::$template_dir_url ) . 'includes/avada-app/fusion-frontend-combined.min.js', [ 'jquery', 'underscore' ], $version, true );
			$localize_handle = 'fusion_avada_frontend_combined';
		} else {
			wp_enqueue_script( 'fusion_sidebar', trailingslashit( Avada::$template_dir_url ) . 'includes/avada-app/panel/view-sidebar.js', [], $version, true );
			wp_enqueue_script( 'fusion_panel', trailingslashit( Avada::$template_dir_url ) . 'includes/avada-app/panel/view-panel.js', [], $version, true );
			wp_enqueue_script( 'avada_panel_iframe', trailingslashit( Avada::$template_dir_url ) . 'includes/avada-app/panel/panel-iframe.js', [], $version, true );
			wp_enqueue_script( 'fusion_panel_tab', trailingslashit( Avada::$template_dir_url ) . 'includes/avada-app/panel/view-tab.js', [ 'avada_panel_iframe' ], $version, true );

			wp_enqueue_script( 'fusion_avada_callback_functions', trailingslashit( Avada::$template_dir_url ) . 'includes/avada-app/model-callback-functions.js', [], $version, true );

			$localize_handle = 'fusion_panel_tab';
		}

		wp_localize_script(
			$localize_handle,
			'fusionBuilderTabL10n',
			[
				'invalidCssValue'    => esc_attr__( 'Invalid CSS Value', 'fusion-builder' ),
				/* translators: The value. */
				'invalidCssValueVar' => esc_attr__( 'Invalid CSS Value: %s', 'fusion-builder' ),
				'invalidColor'       => esc_attr__( 'Invalid Color', 'fusion-builder' ),
			]
		);
	}

	/**
	 * Sanitize fusion-options. (TODO)
	 *
	 * @access private
	 * @since 6.0
	 * @param array $options The options we're saving.
	 * @return array         The array of options, sanitized.
	 */
	private function sanitize_fusion_options( $options ) {
		$fusion_panel   = self::get_instance();
		$fusion_options = $this->flat_tos;
		$types          = [];
		foreach ( $options as $key => $value ) {
			if ( ! isset( $fusion_options[ $key ] ) || ! isset( $fusion_options[ $key ]['type'] ) ) {
				continue;
			}
			switch ( $fusion_options[ $key ]['type'] ) {
				case 'radio-buttonset':
				case 'select':
				case 'radio-image':
				case 'radio':
					if ( isset( $fusion_options[ $key ]['multi'] ) && $fusion_options[ $key ]['multi'] && is_array( $value ) ) {
						$options[ $key ] = [];
						foreach ( $value as $sub_value ) {
							if ( '' !== $sub_value ) {
								$options[ $key ][] = esc_attr( $sub_value );
							}
						}
					} else {
						$options[ $key ] = esc_attr( $value );
					}
					break;
				case 'dimension':
					$options[ $key ] = Fusion_Sanitize::size( $value );
					break;
				case 'spacing':
				case 'dimensions':
				case 'border_radius':
					if ( is_array( $value ) ) {
						foreach ( $value as $sub_key => $sub_value ) {
							$options[ $key ][ $sub_key ] = Fusion_Sanitize::size( $sub_value );
						}
					}
					break;
				case 'slider':
					$options[ $key ] = Fusion_Sanitize::number( $value );
					break;
				case 'color-alpha':
					$options[ $key ] = Fusion_Sanitize::color( $value );
					break;
				case 'switch':
				case 'toggle':
					$options[ $key ] = ( in_array( $value, [ '1', 1, true, 'yes' ], true ) ) ? '1' : '0';
					break;
				case 'typography':
					if ( isset( $value['font-size'] ) ) {
						$options[ $key ]['font-size'] = Fusion_Sanitize::size( $value['font-size'] );
					}
					if ( isset( $value['color'] ) ) {
						$options[ $key ]['color'] = Fusion_Sanitize::color( $value['color'] );
					}
					break;
				case 'color':
					$options[ $key ] = Fusion_Color::new_color( $value )->to_css( 'hex' );
					break;
				case 'text':
					break;
				case 'textarea':
					$options[ $key ] = html_entity_decode( wp_kses_post( $value ) );
					break;
				case 'media':
					break;
			}
		}
		return $options;
	}

	/**
	 * Filter in POST data for preview.
	 *
	 * @access public
	 * @since 6.0
	 */
	public function avada_options_filter() {
		$option_name   = Fusion_App()->get_data( 'option_name' );
		$theme_options = Fusion_App()->get_data( 'fusion_options' );

		if ( $option_name && is_array( $theme_options ) ) {

			add_filter(
				'pre_option_' . $option_name,
				function( $options ) use ( $theme_options ) {
					return $theme_options;
				}
			);
			do_action( 'fusion_preview_update' );
		}

		// Emulate the PO.
		$post_id     = Fusion_App()->get_data( 'post_id' );
		$meta_values = Fusion_App()->get_data( 'meta_values' );
		if ( is_array( $meta_values ) ) {
			if ( strpos( $post_id, '-archive' ) ) {
				$_term = get_term( (int) str_replace( 'archive-', '', $post_id ) );
				fusion_data()->term_meta( $_term->term_id )->reset_data();
				add_filter( 'get_term_metadata', [ $this, 'fusion_filter_term_meta' ], 10, 4 );
			} else {
				fusion_data()->post_meta( $post_id )->reset_data();
				add_filter( 'get_post_metadata', [ $this, 'fusion_filter_post_meta' ], 10, 4 );
			}
		}
		do_action( 'fusion_builder_full_refresh_load' );
	}

	/**
	 * Flag to pause filtering of meta.
	 *
	 * @since 6.2
	 * @return void
	 */
	public function pause_meta_filter() {
		$this->filtering_paused = true;
	}

	/**
	 * Flag to resume filtering of meta.
	 *
	 * @since 6.2
	 * @return void
	 */
	public function resume_meta_filter() {
		$this->filtering_paused = false;
	}

	/**
	 * Get Archive Options.
	 *
	 * @access public
	 * @since 6.0
	 */
	public function get_archive_options() {
		global $post;
		$sections = [];

		if ( ! $this->show_tax_options() ) {
			return;
		}

		$tabs = false;
		if ( class_exists( 'Avada_Taxonomy_Meta' ) ) {
			$sections = Avada_Taxonomy_Meta::avada_taxonomy_map();
		} elseif ( class_exists( 'Avada' ) ) {
			$path = Avada::$template_dir_path . '/includes/class-avada-taxonomy-meta.php';
			require_once wp_normalize_path( $path );
			$sections = Avada_Taxonomy_Meta::avada_taxonomy_map();
		} else {
			return;
		}

		foreach ( $sections as $section_id => $section_args ) {
			if ( is_array( $section_args ) && isset( $section_args['fields'] ) ) {
				foreach ( $section_args['fields'] as $field_id => $field ) {
					if ( 'fusion_tax_heading' !== $field_id ) {
						$sections[ $section_id ]['fields'][ $field_id ] = $this->modify_field( $field, 'term' );
					} else {
						unset( $sections[ $section_id ]['fields'][ $field_id ] );
					}
				}
			}
		}

		// Add in core archive settings panel.
		$sections = array_merge( $this->get_archive_settings(), $sections );

		$this->page_options = $sections;

		$this->page_values = [
			'_fusion' => fusion_data()->term_meta( get_queried_object_id() )->get_all_meta(),
		];
	}

	/**
	 * Get Archive Settings.
	 *
	 * @access public
	 * @since 6.0
	 */
	public function get_archive_settings() {
		$_term        = get_term( (int) str_replace( 'archive-', '', fusion_library()->get_page_id() ) );
		$term_parents = false;
		if ( ! is_null( $_term ) && is_taxonomy_hierarchical( $_term->taxonomy ) ) {
			$all_terms = get_terms(
				[
					'taxonomy' => $_term->taxonomy,
				]
			);

			$term_parents = [
				0 => esc_html__( 'None', 'fusion-builder' ),
			];

			foreach ( $all_terms as $term ) {
				if ( $_term->term_id === $term->term_id ) {
					continue;
				}
				$term_parents[ $term->term_id ] = $term->name;
			}
		}

		$data = [
			'fusion_page_settings_section' => [
				'id'     => 'fusion_page_settings_section',
				'label'  => esc_html__( 'Settings', 'fusion-builder' ),
				'icon'   => 'fusiona-page-settings',
				'fields' => [
					'name' => [
						'id'       => 'name',
						'label'    => esc_html__( 'Name', 'fusion-builder' ),
						'type'     => 'text',
						'default'  => $_term->name,
						'location' => 'PS',
						'output'   => [
							[
								'element'  => '.fusion-page-title-row h1.entry-title, .fusion-page-title-row h2.entry-title',
								'function' => 'html',
							],
						],
					],
					'slug' => [
						'id'        => 'slug',
						'label'     => esc_html__( 'Slug', 'fusion-builder' ),
						'type'      => 'text',
						'default'   => $_term->slug,
						'transport' => 'postMessage',
						'location'  => 'PS',
					],
				],
			],
		];

		if ( $term_parents ) {
			$data['fusion_page_settings_section']['fields']['parent'] = [
				'id'        => 'parent',
				'label'     => esc_html__( 'Parent Category', 'fusion-builder' ),
				'type'      => 'select',
				'choices'   => $term_parents,
				'default'   => (int) $_term->parent,
				'transport' => 'postMessage',
				'location'  => 'PS',
			];
		}

		$data['fusion_page_settings_section']['fields']['description'] = [
			'id'        => 'description',
			'label'     => esc_html__( 'Description', 'fusion-builder' ),
			'type'      => 'textarea',
			'default'   => $_term->description,
			'transport' => 'postMessage',
			'location'  => 'PS',
		];

		return $data;
	}

	/**
	 * Get Page Settings.
	 *
	 * @access public
	 * @since 6.0
	 */
	public function get_page_settings() {

		$real_page_id = (int) str_replace( 'archive-', '', fusion_library()->get_page_id() );
		if ( 0 === $real_page_id ) {
			return;
		}

		// Page settings.
		$post_type = get_post_type( $real_page_id );
		$_post     = get_post( $real_page_id );
		$parents   = false;
		if ( is_post_type_hierarchical( $post_type ) ) {
			$all_hierarchical_posts_in_post_type = get_posts(
				[
					'post_type'           => $post_type,
					'suppress_filters'    => false,
					'numberposts'         => -1, // phpcs:ignore WPThemeReview.CoreFunctionality.PostsPerPage
					'post_parent__not_in' => [ $real_page_id ],
				]
			);
			// Properly format the array.
			$items = [
				0 => esc_html__( 'Select Parent', 'fusion-builder' ),
			];
			foreach ( $all_hierarchical_posts_in_post_type as $item ) {

				// Exclude current post.
				if ( $item->ID === $real_page_id ) {
					continue;
				}
				$items[ $item->ID ] = ( '' === $item->post_title ? '#' . $item->ID . ' ' . esc_html__( '(no title)', 'fusion-builder' ) : $item->post_title );
			}
			$parents = $items;
			wp_reset_postdata();
		}

		// Get the templates.
		$templates     = false;
		$all_templates = wp_get_theme()->get_post_templates();
		if ( isset( $all_templates[ $post_type ] ) ) {
			$templates = array_merge(
				[
					'default' => apply_filters( 'default_page_template_title', esc_html__( 'Default Template', 'fusion-builder' ), 'meta-box' ),
				],
				$all_templates[ $post_type ]
			);
		}

		// Disable templates if we have a content override.
		if ( class_exists( 'Fusion_Template_Builder' ) && Fusion_Template_Builder::get_instance()->get_override( 'content' ) ) {
			$templates = false;
		}

		$post_taxonomies     = [];
		$taxonomy_post_types = (array) apply_filters( 'fusion_taxonomy_post_type', [ 'post', 'avada_portfolio' ] );
		if ( in_array( $post_type, $taxonomy_post_types, true ) ) {
			$post_taxonomies = get_object_taxonomies( $post_type, 'objects' );
		}

		// Get post formats.
		$supported_post_formats = get_theme_support( 'post-formats' );
		$post_formats           = false;

		if ( is_array( $supported_post_formats ) && ! empty( $supported_post_formats ) ) {
			$post_formats = [
				'standard' => 'Standard',
			];

			foreach ( $supported_post_formats[0] as $format ) {
				$post_formats[ $format ] = ucfirst( $format );
			}
		}

		$data = [
			'fusion_page_settings_section' => [
				'id'     => 'fusion_page_settings_section',
				'label'  => esc_html__( 'Settings', 'fusion-builder' ),
				'icon'   => 'fusiona-page-settings',
				'fields' => [
					'post_title' => [
						'id'       => 'post_title',
						'label'    => esc_html__( 'Page Title', 'fusion-builder' ),
						'type'     => 'text',
						'default'  => get_the_title( $real_page_id ),
						'location' => 'PS',
						'output'   => [
							[
								'element'  => 'h1.entry-title, h2.entry-title:not(.blog-shortcode-post-title)',
								'function' => 'html',
							],
						],
					],
					'post_name'  => [
						'id'        => 'post_name',
						'label'     => esc_html__( 'Slug', 'fusion-builder' ),
						'type'      => 'text',
						'location'  => 'PS',
						'default'   => $_post->post_name,
						'transport' => 'postMessage',
					],
				],
			],
		];
		if ( $parents || $templates ) {
			$data['fusion_page_settings_section']['fields']['fusion_page_options_page_attributes_info'] = [
				'label'       => esc_html__( 'Page Attributes', 'fusion-builder' ),
				'description' => '',
				'id'          => 'fusion_page_options_page_attributes_info',
				'type'        => 'info',
			];
		}

		if ( $parents ) {
			$data['fusion_page_settings_section']['fields']['post_parent'] = [
				'id'        => 'post_parent',
				'label'     => esc_html__( 'Parent', 'fusion-builder' ),
				'type'      => 'select',
				'choices'   => $parents,
				'default'   => (int) wp_get_post_parent_id( fusion_library()->get_page_id() ),
				'transport' => 'postMessage',
				'location'  => 'PS',
			];
		}

		if ( $templates ) {
			$template = get_post_meta( fusion_library()->get_page_id(), '_wp_page_template', true );
			$template = 'page' === $post_type && '' === $template && '100_width' === Avada()->settings->get( 'page_template' ) ? '100-width.php' : $template;
			$data['fusion_page_settings_section']['fields']['_wp_page_template'] = [
				'id'       => '_wp_page_template',
				'label'    => esc_html__( 'Template', 'fusion-builder' ),
				'type'     => 'select',
				'choices'  => $templates,
				'value'    => $template ? $template : 'default',
				'default'  => 'default',
				'location' => 'PO',
				'not_pyre' => true,
			];
		}

		if ( $parents ) {
			$data['fusion_page_settings_section']['fields']['menu_order'] = [
				'id'        => 'menu_order',
				'label'     => esc_html__( 'Order', 'fusion-builder' ),
				'type'      => 'text',
				'default'   => $_post->menu_order,
				'transport' => 'postMessage',
				'location'  => 'PS',
			];
		}

		if ( post_type_supports( $post_type, 'post-formats' ) && current_theme_supports( 'post-formats' ) && $post_formats ) {
			$data['fusion_page_settings_section']['fields']['post_format'] = [
				'id'       => 'post_format',
				'label'    => esc_html__( 'Post Format', 'fusion-builder' ),
				'type'     => 'select',
				'choices'  => $post_formats,
				'value'    => get_post_format( $real_page_id ) ? : 'standard', // phpcs:ignore WordPress.PHP.DisallowShortTernary
				'default'  => 'standard',
				'location' => 'PS',
				'not_pyre' => true,
			];
		}
		if ( 0 < count( $post_taxonomies ) && in_array( $post_type, $taxonomy_post_types, true ) ) {
			foreach ( $post_taxonomies as $taxonomy ) {
				if ( 'post_format' !== $taxonomy->name && 'fusion_tb_category' !== $taxonomy->name && 'element_category' !== $taxonomy->name ) {
					$selection   = [];
					$field_type  = 'ajax_select';
					$ajax        = 'fusion_search_query';
					$ajax_params = [
						'taxonomy' => $taxonomy->name,
					];

					if ( 25 > wp_count_terms( $taxonomy->name ) ) {
						$ajax       = '';
						$field_type = 'multiple_select';

						$terms     = get_terms(
							[
								'taxonomy'   => $taxonomy->name,
								'hide_empty' => false,
							]
						);
						$selection = [];

						// All terms.
						foreach ( $terms as $term ) {
							$selection[ $term->term_id ] = $term->name;
						}
					}

					// Add field.
					$data['fusion_page_settings_section']['fields'][ $taxonomy->name ] = [
						'id'               => $taxonomy->name,
						'label'            => $taxonomy->labels->name,
						'placeholder'      => $taxonomy->labels->name,
						'placeholder_text' => esc_html__( 'Choose', 'fusion-builder' ) . ' ' . $taxonomy->labels->name,
						'type'             => $field_type,
						'choices'          => $selection,
						'add_new'          => [
							'add_to' => $taxonomy->name,
						],
						'location'         => 'PS',
						'ajax'             => $ajax,
						'ajax_params'      => $ajax_params,
						'transport'        => 'postMessage',
						'not_pyre'         => true,
					];
				}
			}
		}

		if ( post_type_supports( $post_type, 'thumbnail' ) ) {
			$data['fusion_page_settings_section']['fields']['fusion_page_options_featured_image_info'] = [
				'label'       => esc_html__( 'Featured Image', 'fusion-builder' ),
				'description' => '',
				'id'          => 'fusion_page_options_featured_image_info',
				'type'        => 'info',
			];

			$data['fusion_page_settings_section']['fields']['_thumbnail_id'] = [
				'id'              => '_thumbnail_id',
				'label'           => esc_html__( 'Featured Image', 'fusion-builder' ),
				'type'            => 'upload_id',
				'default'         => has_post_thumbnail( $real_page_id ) ? get_post_thumbnail_id( $real_page_id ) : '',
				'location'        => 'PO',
				'not_pyre'        => true,
				'partial_refresh' => [
					'_thumbnail_id' => [
						'selector'              => '.fusion-featured-image-wrapper',
						'container_inclusive'   => true,
						'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'singular_featured_image' ],
						'success_trigger_event' => 'fusion-reinit-single-post-slideshow',
					],
				],
			];
		}

		if ( in_array( $post_type, [ 'post', 'page', 'avada_portfolio' ], true ) ) {
			$posts_slideshow_number = Avada()->settings->get( 'posts_slideshow_number' );
			for ( $i = 2; $i <= $posts_slideshow_number; $i++ ) {
				$data['fusion_page_settings_section']['fields'][ 'kd_featured-image-' . $i . '_' . $post_type . '_id' ] = $this->get_featured_image_object( $i, $post_type );
			}
		}

		return $data;
	}

	/**
	 * Get object structure for additional featured images.
	 *
	 * @access private
	 * @since 6.0
	 * @param int    $i         Ordinal number of image.
	 * @param string $post_type The post-type.
	 * @return array
	 */
	private function get_featured_image_object( $i = '$', $post_type = '#' ) {
		$real_page_id = (int) str_replace( 'archive-', '', fusion_library()->get_page_id() );

		return [
			'id'              => 'kd_featured-image-' . $i . '_' . $post_type . '_id',

			// Translators: Ordinal number of featured image.
			'label'           => esc_html( sprintf( __( 'Featured Image %s', 'fusion-builder' ), $i ) ),
			'type'            => 'upload_id',
			'default'         => fusion_data()->post_meta( fusion_library()->get_page_id() )->get( 'kd_featured-image-' . $i . '_' . $post_type . '_id' ),
			'location'        => 'PO',
			'not_pyre'        => true,
			'partial_refresh' => [
				'kd_featured-image-' . $i . '_' . $post_type . '_id' => [
					'selector'              => '.fusion-featured-image-wrapper',
					'container_inclusive'   => true,
					'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'singular_featured_image' ],
					'success_trigger_event' => 'fusion-reinit-single-post-slideshow',
				],
			],
		];
	}

	/**
	 * Get Page Options.
	 *
	 * @access public
	 * @since 6.0
	 */
	public function get_page_options() {
		global $post;
		$sections  = [];
		$post_type = get_post_type( fusion_library()->get_page_id() );

		if ( ! is_singular() && get_option( 'page_for_posts' ) !== fusion_library()->get_page_id() && ! ( class_exists( 'WooCommerce' ) && is_shop() ) ) {
			$this->get_archive_options();
			return;
		}

		if ( ! $this->show_page_options() ) {
			return;
		}

		if ( ! class_exists( 'PyreThemeFrameworkMetaboxes' ) ) {
			$path = Avada::$template_dir_path . '/includes/metaboxes/metaboxes.php';
			require_once wp_normalize_path( $path );
		}

		// Parse sections.
		$page_options = PyreThemeFrameworkMetaboxes::$instance;
		if ( ! $page_options ) {
			$page_options = new PyreThemeFrameworkMetaboxes();
		}

		$sections = $page_options->get_options();

		// Library elements have no POs.
		if ( 'fusion_element' !== $post_type ) {
			$sections['custom_css'] = [
				'label'  => esc_html__( 'Custom CSS', 'Avada' ),
				'id'     => 'custom_css',
				'icon'   => 'fusiona-code',
				'fields' => [
					'_fusion_builder_custom_css' => [
						'label'       => esc_html__( 'CSS Code', 'Avada' ),
						/* translators: <code>!important</code> */
						'description' => sprintf( esc_html__( 'Enter your CSS code in the field below. Do not include any tags or HTML in the field. Custom CSS entered here will override the theme CSS. In some cases, the %s tag may be needed. Don\'t URL encode image or svg paths. Contents of this field will be auto encoded.', 'Avada' ), '<code>!important</code>' ),
						'id'          => '_fusion_builder_custom_css',
						'default'     => '',
						'type'        => 'code',
						'not_pyre'    => true,
						'choices'     => [
							'language' => 'css',
							'height'   => 450,
							'theme'    => 'chrome',
							'minLines' => 40,
							'maxLines' => 50,
						],
					],
				],
			];

			$sections['import_export_po'] = [
				'label'    => esc_html__( 'Import/Export', 'Avada' ),
				'id'       => 'import_export_po',
				'priority' => 27,
				'icon'     => 'el-icon-css',
				'alt_icon' => 'fusiona-loop-alt2',
				'fields'   => [
					'import_to' => [
						'label'       => esc_html__( 'Import Page Options', 'Avada' ),
						'description' => esc_html__( 'Import Page Options.  You can import via file or copy and paste from JSON data.' ),
						'id'          => 'import_po',
						'type'        => 'import',
						'context'     => 'PO',
					],
					'export_to' => [
						'label'       => esc_html__( 'Export Page Options', 'Avada' ),
						'description' => esc_html__( 'Export your Page Options.  You can either export as a file or copy the data.' ),
						'id'          => 'export_po',
						'type'        => 'export',
						'context'     => 'PO',
					],
				],
			];
		}

		// Add in core page settings panel.
		if ( apply_filters( 'fusion_load_page_settings', true, $post_type ) ) {
			$sections = array_merge( $this->get_page_settings(), $sections );
		}

		foreach ( $sections as $section_id => $section ) {
			foreach ( $section['fields'] as $field_id => $field ) {
				$sections[ $section_id ]['fields'][ $field_id ] = $this->modify_field( $field, 'post' );
			}
		}
		$this->page_options = $sections;

		$ids = [];
		foreach ( $sections as $section ) {
			foreach ( $section['fields'] as $field ) {
				if ( isset( $field['type'] ) && 'dimensions' === $field['type'] && isset( $field['value'] ) && is_array( $field['value'] ) ) {
					foreach ( $field['value'] as $dimensions_id => $dimensions_value ) {
						$ids[] = $dimensions_id;
					}
				} elseif ( ! isset( $field['not_pyre'] ) ) {
					if ( is_string( $field['id'] ) ) {
						$ids[] = $field['id'];
					}
				} else {
					$ids[] = $field['id'];
				}
			}
		}

		$values        = [];
		$custom_fields = get_post_custom( fusion_library()->get_page_id() );
		$post_type     = get_post_type( fusion_library()->get_page_id() );

		$ids[] = 'fusion_builder_status';
		$ids[] = '_fusion_builder_custom_css';
		$ids[] = '_thumbnail_id';
		$ids[] = '_wp_page_template';
		$ids[] = '_fusion_google_fonts';

		// Only when Avada Studio plugin is active.
		if ( class_exists( 'Avada_Studio' ) ) {
			$ids[] = 'avada_media';
		}

		if ( in_array( $post_type, [ 'post', 'page', 'avada_portfolio' ], true ) ) {
			$posts_slideshow_number = Avada()->settings->get( 'posts_slideshow_number' );
			for ( $i = 2; $i <= $posts_slideshow_number; $i++ ) {
				$ids[] = 'kd_featured-image-' . $i . '_' . $post_type . '_id';
			}
		}

		if ( isset( $custom_fields['_fusion'] ) && isset( $custom_fields['_fusion'][0] ) ) {
			$custom_fields['_fusion'] = maybe_unserialize( $custom_fields['_fusion'][0] );
			$values['_fusion']        = $custom_fields['_fusion'];
		}

		foreach ( $ids as $id ) {
			if ( isset( $custom_fields[ $id ] ) && isset( $custom_fields[ $id ][0] ) ) {

				// TODO: check why this is necessary.
				if ( '_fusion_google_fonts' === $id || 'pyre_conditions' === $id ) {
					$values[ $id ] = maybe_unserialize( wp_unslash( $custom_fields[ $id ][0] ) );
				} else {
					$values[ $id ] = $custom_fields[ $id ][0];
				}
			}
		}

		$this->page_values = $values;
	}

	/**
	 * Filters post metadata.
	 *
	 * @access public
	 * @since 2.0
	 *
	 * @param null|array|string $value     The value get_metadata() should return - a single metadata value,
	 *                                     or an array of values.
	 * @param int               $object_id Object ID.
	 * @param string            $meta_key  Meta key.
	 * @param bool              $single    Whether to return only the first value of the specified $meta_key.
	 * @return array|null
	 */
	public function fusion_filter_term_meta( $value, $object_id, $meta_key, $single ) {
		$meta_values = Fusion_App()->get_data( 'meta_values' );
		if ( is_array( $meta_values ) && Fusion_Data_PostMeta::ROOT === $meta_key && isset( $meta_values[ $meta_key ] ) ) {
			return [ $meta_values[ $meta_key ] ];
		}
		return $value;
	}

	/**
	 * Filters post metadata.
	 *
	 * @access public
	 * @since 2.0
	 *
	 * @param null|array|string $value     The value get_metadata() should return - a single metadata value,
	 *                                     or an array of values.
	 * @param int               $object_id Object ID.
	 * @param string            $meta_key  Meta key.
	 * @param bool              $single    Whether to return only the first value of the specified $meta_key.
	 * @return array|null
	 */
	public function fusion_filter_post_meta( $value, $object_id, $meta_key, $single ) {
		$post_id     = Fusion_App()->get_data( 'post_id' );
		$meta_values = Fusion_App()->get_data( 'meta_values' );

		if ( $post_id && (int) $post_id === (int) $object_id && is_array( $meta_values ) && ! $this->filtering_paused ) {

			// Get what ever is in DB, needed for meta fields added by 3rd party code.
			remove_filter( 'get_post_metadata', [ $this, 'fusion_filter_post_meta' ] );
			$post_meta = get_post_custom( $post_id );
			add_filter( 'get_post_metadata', [ $this, 'fusion_filter_post_meta' ], 10, 4 );

			// Transient meta is saved as $key => $value, need to have it like WP default.
			foreach ( $meta_values as $key => $val ) {
				$meta_values[ $key ] = [ $val ];
			}

			// Merge post meta with transient meta, latter having priority.
			$meta_values = array_merge( $post_meta, $meta_values );

			// Getting a specific value, then check if we have it.
			if ( $meta_key && '' !== $meta_key && isset( $meta_values[ $meta_key ] ) ) {
				if ( $single && isset( $meta_values[ $meta_key ][0] ) ) {
					return [ maybe_unserialize( $meta_values[ $meta_key ][0] ) ];
				}
				return array_map( 'maybe_unserialize', $meta_values[ $meta_key ] );
			}

			// All post meta fields should be returned.
			if ( $meta_values && ( ! $meta_key || '' === $meta_key ) && $object_id === (int) $post_id ) {
				return $meta_values;
			}
		}

		return $value;
	}

	/**
	 * Get options.
	 *
	 * @access public
	 * @since 6.0
	 */
	public function get_options() {

		/**
		 * Use caches if available.
		 * If WP_DEBUG is on, don't cache.
		 */
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			$this->options                = get_transient( 'fusion_tos' );
			$this->fusion_builder_options = get_transient( 'fusion_fb_tos' );
		}
		if ( ! empty( $this->options ) && ! empty( $this->fusion_builder_options ) ) {
			return;
		}

		/**
		 * No caches were found, populate the class properties.
		 */
		global $avada_avadaredux_args;

		$avada_options = [];
		$has_addons    = false;

		if ( class_exists( 'Avada_Options' ) ) {
			$avada_options = (array) Avada_Options::get_instance();
		}

		if ( ! isset( $avada_options['sections'] ) ) {
			$avada_options['sections'] = [];
		}

		if ( defined( 'FUSION_BUILDER_PLUGIN_DIR' ) ) {
			if ( ! class_exists( 'Fusion_Builder_Options' ) ) {
				require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-builder-options.php';
			}
			unset( $avada_options['sections']['shortcode_styling'] );
			$fusion_builder_options_full = (array) Fusion_Builder_Options::get_instance();

			if ( isset( $fusion_builder_options_full['sections'] ) && isset( $fusion_builder_options_full['sections']['shortcode_styling'] ) && isset( $fusion_builder_options_full['sections']['shortcode_styling']['fields'] ) ) {

				// Sharing box already exists in main TO, no need for duplicates.
				if ( isset( $fusion_builder_options_full['sections']['shortcode_styling']['fields']['sharing_box_shortcode_section'] ) ) {
					unset( $fusion_builder_options_full['sections']['shortcode_styling']['fields']['sharing_box_shortcode_section'] );
				}
				$this->fusion_builder_options = $fusion_builder_options_full['sections']['shortcode_styling']['fields'];

				$has_addons = isset( $fusion_builder_options_full['sections']['fusion_builder_addons'] );
				if ( $has_addons ) {
					foreach ( $fusion_builder_options_full['sections']['fusion_builder_addons']['fields'] as $addon ) {
						$addon['addon']                 = true;
						$this->fusion_builder_options[] = $addon;
					}
				}
				ksort( $this->fusion_builder_options );
			}
		}

		$sections = $avada_options['sections'];

		$sections['shortcode_styling'] = [
			'label' => esc_html__( 'Avada Builder Elements', 'Avada' ),
			'id'    => 'shortcode_styling',
			'icon'  => 'fusiona-element-options',
		];

		if ( $has_addons ) {
			$sections['shortcode_styling']['fields'] = [
				'fusion_builder_elements' => [
					'label' => esc_html__( 'Avada Builder Elements', 'Avada' ),
					'id'    => 'fusion_builder_elements',
					'type'  => 'sub-section',
				],
				'fusion_builder_addons'   => [
					'label' => esc_html__( 'Add-on Elements', 'Avada' ),
					'id'    => 'fusion_builder_addons',
					'type'  => 'sub-section',
				],
			];
		}

		// Instantiate the Avada_AvadaRedux_No_Init object.
		$avadaredux_no_init = new Avada_AvadaRedux_No_Init( $avada_avadaredux_args );

		// Apply mods to fields.
		foreach ( $sections as $section_id => $section ) {

			// Skip if there's no fields in this section.
			if ( ! isset( $section['fields'] ) ) {
				continue;
			}
			foreach ( $section['fields'] as $field_id => $field ) {

				// We don't want empty arrays.
				if ( empty( $field ) ) {
					unset( $sections[ $section_id ]['fields'][ $field_id ] );
					continue;
				}

				// Skip if no type is defined.
				if ( ! isset( $field['type'] ) ) {
					continue;
				}

				// Parse subsections.
				if ( 'sub-section' === $field['type'] ) {

					// Skip if no type is defined.
					if ( ! isset( $field['id'] ) ) {
						continue;
					}
					if ( isset( $field['fields'] ) && is_array( $field['fields'] ) ) {
						foreach ( $field['fields'] as $subfield_id => $subfield ) {

							// Skip if no ID is defined.
							if ( ! isset( $subfield['id'] ) ) {
								continue;
							}

							// Parse accordions.
							if ( isset( $subfield['type'] ) && 'accordion' === $subfield['type'] ) {
								if ( isset( $subfield['fields'] ) && is_array( $subfield['fields'] ) ) {
									foreach ( $subfield['fields'] as $sub_subfield_id => $sub_subfield ) {

										// Get field mods.
										$sections[ $section_id ][ $field_id ][ $subfield_id ][ $sub_subfield_id ] = $avadaredux_no_init->apply_soft_dependency( $sub_subfield, true );
										$sections[ $section_id ][ $field_id ][ $subfield_id ][ $sub_subfield_id ] = $this->modify_field( $sections[ $section_id ][ $field_id ][ $subfield_id ][ $sub_subfield_id ], 'theme' );

										if ( ! is_array( $sections[ $section_id ][ $field_id ][ $subfield_id ][ $sub_subfield_id ] ) ) {
											unset( $sections[ $section_id ][ $field_id ][ $subfield_id ][ $sub_subfield_id ] );
										}
									}
								}
							} else {

								// Not an accordion, get field mods.
								$sections[ $section_id ]['fields'][ $field_id ]['fields'][ $subfield_id ] = $avadaredux_no_init->apply_soft_dependency( $subfield, true );
								$sections[ $section_id ]['fields'][ $field_id ]['fields'][ $subfield_id ] = $this->modify_field( $sections[ $section_id ]['fields'][ $field_id ]['fields'][ $subfield_id ], 'theme' );

								if ( ! is_array( $sections[ $section_id ]['fields'][ $field_id ]['fields'][ $subfield_id ] ) ) {
									unset( $sections[ $section_id ]['fields'][ $field_id ]['fields'][ $subfield_id ] );
								}
							}
						}
					}
				} elseif ( 'accordion' === $field['type'] ) { // Accordion.
					if ( isset( $field['fields'] ) && is_array( $field['fields'] ) ) {
						foreach ( $field['fields'] as $subfield_id => $subfield ) {

							// Get field mods.
							$sections[ $section_id ][ $field_id ][ $subfield_id ] = $avadaredux_no_init->apply_soft_dependency( $subfield, true );
							$sections[ $section_id ][ $field_id ][ $subfield_id ] = $this->modify_field( $sections[ $section_id ][ $field_id ][ $subfield_id ], 'theme' );

							if ( ! is_array( $sections[ $section_id ][ $field_id ][ $subfield_id ] ) ) {
								unset( $sections[ $section_id ][ $field_id ][ $subfield_id ] );
							}
						}
					}
				} else {

					// Get field mods.
					$sections[ $section_id ]['fields'][ $field_id ] = $avadaredux_no_init->apply_soft_dependency( $field, true );
					$sections[ $section_id ]['fields'][ $field_id ] = $this->modify_field( $sections[ $section_id ]['fields'][ $field_id ], 'theme' );

					if ( ! is_array( $sections[ $section_id ]['fields'][ $field_id ] ) ) {
						unset( $sections[ $section_id ]['fields'][ $field_id ] );
					}
				}
			}
		}

		$demo_options = apply_filters( 'awb_global_options', [] );

		$sections['import_export'] = [
			'label'    => esc_html__( 'Import/Export', 'Avada' ),
			'id'       => 'import_export',
			'priority' => 27,
			'icon'     => 'el-icon-css',
			'alt_icon' => 'fusiona-loop-alt2',
			'fields'   => [
				'import_to' => [
					'label'       => esc_html__( 'Import Global Options', 'Avada' ),
					'description' => esc_html__( 'Import Global Options. You can import via file, copy and paste or select an Avada prebuilt website.' ),
					'id'          => 'import_to',
					'type'        => 'import',
					'demos'       => $demo_options,
					'context'     => 'TO',
				],
				'export_to' => [
					'label'       => esc_html__( 'Export Global Options', 'Avada' ),
					'description' => esc_html__( 'Export your Global Options. You can either export as a file or copy the data.' ),
					'id'          => 'export_to',
					'type'        => 'export',
					'context'     => 'TO',
					'text'        => esc_html__( 'Export Global Options', 'Avada' ),
				],
			],
		];

		if ( empty( $demo_options ) ) {
			unset( $sections['import_export']['fields']['demo_import'] );
		}

		$this->options = $sections;

		/**
		 * Cache the object properties for next time.
		 */
		set_transient( 'fusion_tos', $this->options, WEEK_IN_SECONDS );
		set_transient( 'fusion_fb_tos', $this->fusion_builder_options, WEEK_IN_SECONDS );
	}

	/**
	 * This method applies the array_replace_recursive function to the arrays
	 * but also takes into account the 'output' arguments of fields.
	 *
	 * @since 6.0
	 * @access private
	 * @param array $array1 The 1st array.
	 * @param array $array2 The 2nd array.
	 * @return array
	 */
	private function array_replace_recursive_sections( $array1, $array2 ) {
		if ( empty( $array1 ) ) {
			return $array2;
		}
		if ( empty( $array2 ) ) {
			return $array1;
		}

		foreach ( $array2 as $a2_section_id => $a2_section ) {
			if ( ! isset( $a2_section['fields'] ) ) {
				continue;
			}
			foreach ( $a2_section['fields'] as $a2_field_id => $a2_field ) {
				foreach ( $array1 as $a1_section_id => $a1_section ) {
					if ( ! isset( $a1_section['fields'] ) ) {
						continue;
					}
					foreach ( $a1_section['fields'] as $a1_field_id => $a1_field ) {
						if ( $a1_field_id !== $a2_field_id ) {
							continue;
						}
						if ( ! isset( $a2_field['output'] ) || ! isset( $a1_field['output'] ) ) {
							continue;
						}
						if ( $a2_field['output'] === $a1_field['output'] ) {
							continue;
						}
						foreach ( $a2_field['output'] as $a2_field_output_key => $a2_field_output_val ) {
							$array1[ $a1_section_id ]['fields'][ $a1_field_id ]['output'][] = $a2_field_output_val;
							unset( $array2[ $a2_section_id ]['fields'][ $a2_field_id ]['output'][ $a2_field_output_key ] );
						}
					}
				}
			}
		}
		return array_replace_recursive( $array1, $array2 );
	}

	/**
	 * Gets an array of options, flattened (no panels/sections).
	 *
	 * @access public
	 * @since 6.0
	 * @return array
	 */
	public function get_flat_options() {

		/**
		 * Use caches if available.
		 * If WP_DEBUG is on, don't cache.
		 */
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			$flat_options = get_transient( 'fusion_tos_flat' );
			if ( $flat_options ) {
				return $flat_options;
			}
		}

		if ( ! $this->options || ! empty( $this->options ) ) {
			$this->get_options();
		}
		$float_options = [];
		foreach ( $this->options as $item ) {
			if ( isset( $item['fields'] ) ) {
				foreach ( $item['fields'] as $sub_item ) {
					if ( isset( $sub_item['fields'] ) ) {
						foreach ( $sub_item['fields'] as $sub_sub_item ) {
							if ( ! isset( $sub_sub_item['id'] ) ) {
								continue;
							}
							$flat_options[ $sub_sub_item['id'] ] = $this->modify_field( $sub_sub_item, 'theme' );
						}
						continue;
					}
					if ( ! isset( $sub_item['id'] ) ) {
						continue;
					}
					$flat_options[ $sub_item['id'] ] = $this->modify_field( $sub_item, 'theme' );
				}
				continue;
			}
			if ( ! isset( $item['id'] ) ) {
				continue;
			}
			$flat_options[ $item['id'] ] = $this->modify_field( $item, 'theme' );
		}

		set_transient( 'fusion_tos_flat', $flat_options, WEEK_IN_SECONDS );
		return $flat_options;
	}

	/**
	 * Get Settings
	 *
	 * @access public
	 * @since 6.0
	 */
	public function get_settings() {
		$option_name = Avada::get_option_name();
		$settings    = get_option( $option_name );

		if ( $settings ) {
			$this->settings = $settings;
			return;
		}

		// No saved options, get defaults.
		$options  = $this->get_flat_options();
		$settings = [];
		if ( is_array( $options ) ) {
			foreach ( $options as $option ) {
				$settings[ $option['id'] ] = Avada()->settings->get( $option['id'] );
			}
		}
		$this->settings = $settings;
	}

	/**
	 * The main parser
	 *
	 * @access public
	 * @since 6.0
	 */
	public function parse() {
		foreach ( $this->options as $key0 => $val0 ) {
			if ( ! isset( $val0['fields'] ) ) {
				$this->options[ $key0 ] = $this->modify_field( $val0 );
				continue;
			}
			foreach ( $this->options[ $key0 ]['fields'] as $key1 => $val1 ) {
				if ( ! isset( $val1['fields'] ) ) {
					$this->options[ $key0 ]['fields'][ $key1 ] = $this->modify_field( $val1 );
					continue;
				}
				foreach ( $this->options[ $key0 ]['fields'][ $key1 ]['fields'] as $key2 => $val2 ) {
					if ( ! isset( $val2['fields'] ) ) {
						$this->options[ $key0 ]['fields'][ $key1 ]['fields'][ $key2 ] = $this->modify_field( $val2 );
						continue;
					}
					foreach ( $this->options[ $key0 ]['fields'][ $key1 ]['fields'][ $key2 ]['fields'] as $key3 => $val3 ) {
						$this->options[ $key0 ]['fields'][ $key1 ]['fields'][ $key2 ]['fields'][ $key3 ] = $this->modify_field( $val3 );
					}
				}
			}
		}
		echo '<script>';
		echo 'var fusionSettings=' . wp_json_encode( $this->settings ) . ';';
		echo 'var customizer=' . wp_json_encode( $this->options ) . ';';
		echo 'var fusionSiteVars={adminUrl:"' . esc_url_raw( admin_url() ) . '",siteUrl:"' . esc_url_raw( site_url() ) . '"};';
		echo '</script>';
	}

	/**
	 * Apply modifications to options if needed.
	 *
	 * @access public
	 * @since 6.0
	 * @param array  $field   The field arguments.
	 * @param string $context Whether this is a global option, page option, term option or archive option.
	 * @see Fusion_Options_Map::get_option_name() for the $context param.
	 * @return array
	 */
	public function modify_field( $field, $context = 'theme' ) {

		// Modify radio-buttonsets if needed.
		if ( is_array( $field ) && isset( $field['type'] ) && ( 'radio-buttonset' === $field['type'] || 'button_set' === $field['type'] ) && isset( $field['choices'] ) && ! isset( $field['icons'] ) ) {
			$option_labels = implode( '', array_values( $field['choices'] ) );
			if ( false === strpos( $option_labels, '<svg' ) && 269 < 10 * strlen( $option_labels ) + 24 * count( $field['choices'] ) ) {
				$field['type'] = 'select';
			}
		}

		if ( isset( $field['hide_on_front'] ) ) {
			return false;
		}

		// Inherit "output" and "css_vars" arguments from theme-options fields.
		if ( isset( $field['id'] ) ) {
			if ( 'theme' !== $context ) {
				if ( ! isset( $field['output'] ) || ! isset( $field['css_vars'] ) ) {
					$option_to = Fusion_Options_Map::get_map_key_from_context( $field['id'], $context );
					if ( is_string( $option_to ) ) {
						foreach ( [ 'output', 'css_vars', 'partial_refresh' ] as $varname ) {
							$result = isset( $this->flat_tos[ $option_to ] ) && isset( $this->flat_tos[ $option_to ][ $varname ] ) ? $this->flat_tos[ $option_to ][ $varname ] : null;
							if ( $result ) {
								$field[ $varname ] = $result;
							}
						}
					}
				}
			}
		}

		return $field;
	}

	/**
	 * Sets Avada-Builder flat options.
	 *
	 * @access public
	 * @since 6.0
	 * @return void
	 */
	public function set_flat_options() {
		$this->flat_tos = $this->get_flat_options();
	}

	/**
	 * Get argument from TOs for a specific field.
	 *
	 * @access public
	 * @since 6.0
	 * @param string $id The field-ID.
	 * @param string $arg The argument we want to get.
	 * @return mixed
	 */
	public function get_arg_from_to( $id, $arg ) {
		return isset( $this->flat_tos[ $id ] ) && isset( $this->flat_tos[ $id ][ $arg ] ) ? $this->flat_tos[ $id ][ $arg ] : null;
	}

	/**
	 * Add extra hook wrapper elements to help with partial-refreshes.
	 *
	 * @access private
	 * @since 6.0
	 * @return void
	 */
	public function add_hook_wrappers() {

		// Hook: avada_before_header_wrapper - open wrapper.
		add_action(
			'avada_before_header_wrapper',
			function() {
				echo '<span class="avada-hook-before-header-wrapper">';
			},
			defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : -99999999999 // phpcs:ignore PHPCompatibility.Constants.NewConstants
		);

		// Hook: avada_before_header_wrapper - close wrapper.
		add_action(
			'avada_before_header_wrapper',
			function() {
				echo '</span>';
			},
			PHP_INT_MAX
		);

		// Hook: avada_after_header_wrapper - open wrapper.
		add_action(
			'avada_after_header_wrapper',
			function() {
				echo '<span class="avada-hook-after-header-wrapper">';
			},
			defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : -99999999999 // phpcs:ignore PHPCompatibility.Constants.NewConstants
		);

		// Hook: avada_after_header_wrapper - close wrapper.
		add_action(
			'avada_after_header_wrapper',
			function() {
				echo '</span>';
			},
			PHP_INT_MAX
		);
	}
}

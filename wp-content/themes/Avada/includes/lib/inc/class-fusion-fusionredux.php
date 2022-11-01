<?php
/**
 * Redux handler.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * Handle Redux in Fusion-Library.
 *
 * @since 1.0
 */
class Fusion_FusionRedux {

	/**
	 * The option name.
	 *
	 * @access public
	 * @since 1.0
	 * @var string
	 */
	public $key;

	/**
	 * The version.
	 *
	 * @access public
	 * @since 1.0
	 * @var string
	 */
	public $ver;

	/**
	 * The arguments used in the object's constructor.
	 *
	 * @access protected
	 * @since 1.0
	 * @var array
	 */
	protected $args = [];

	/**
	 * An array of all the image fields we're using.
	 *
	 * @access protected
	 * @since 1.1
	 * @var array
	 */
	protected $media_fields = [];

	/**
	 * Facilitates copying options to 3rd-party option-tables in the db.
	 *
	 * @static
	 * @access public
	 * @since 1.0
	 * @var array
	 */
	public static $option_name_settings = [];

	/**
	 * Whether or not we're using "all" language.
	 * This is needed in order to determine if on WPML or PolyLang
	 * the user is using "all" language,
	 * in which case we'll have to clone the saved settings
	 * to all available languages.
	 *
	 * @static
	 * @access protected
	 * @since 1.0
	 * @var bool
	 */
	protected static $is_language_all = false;

	/**
	 * The class constructor
	 *
	 * @access public
	 * @since 1.0
	 * @param array $args The arguments we'll be passing-on to the object.
	 */
	public function __construct( $args = [] ) {

		$this->args = $args;

		add_filter( 'fusionredux/_url', [ $this, 'redux_url_change' ] );
		add_filter( 'fusionredux/_dir', [ $this, 'redux_dir_change' ] );

		/**
		 * Initialization of the framework needs to be hooked, due to globals not being set earlier etc.
		 * Priority 2 loads he options framework directly after widgets are initialized.
		 */
		add_action( 'init', [ $this, 'init_fusionredux' ], 2 );
		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
		add_action( 'wp_ajax_fusionredux_hide_remote_media_admin_notification', [ $this, 'hide_remote_media_admin_notification' ] );

		if ( ! defined( 'FUSION_AJAX_SAVE' ) ) {
			define( 'FUSION_AJAX_SAVE', true );
		}

		// Backwards-compatibility tweak.
		add_action( 'fusionredux/options/fusion_options/saved', [ $this, 'bc_action_on_save' ], 10, 2 );

		add_action( 'wp_ajax_fusion_reset_all_caches', [ $this, 'reset_caches_handler' ] );
		add_action( 'wp_ajax_nopriv_fusion_reset_all_caches', [ $this, 'reset_caches_handler' ] );

	}

	/**
	 * Change the URL to redux.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $url The URL to redux.
	 * @return string     The new URL to redux.
	 */
	public function redux_url_change( $url ) {
		return FUSION_LIBRARY_URL . '/inc/redux/framework/FusionReduxCore/';
	}

	/**
	 * Change the path to redux.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $dir The path to redux.
	 * @return string     The new path to redux.
	 */
	public function redux_dir_change( $dir ) {
		return FUSION_LIBRARY_PATH . '/inc/redux/framework/FusionReduxCore/';
	}

	/**
	 * Extra functionality on save.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $data           The data.
	 * @param array $changed_values The changed values to save.
	 * @return void
	 */
	public function bc_action_on_save( $data, $changed_values ) {
		do_action( 'fusion_options_save', $data, $changed_values );
	}

	/**
	 * Initializes and triggers all other actions/hooks.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function init_fusionredux() {

		$this->fusion_sections = $this->args['sections'];
		// Add a filter to allow modifying the array.
		$this->fusion_sections = apply_filters( 'fusion_admin_options_injection', $this->fusion_sections );

		self::$is_language_all = $this->args['is_language_all'];

		add_action( 'update_option_' . $this->args['option_name'], [ $this, 'option_name_settings_update' ], 10, 3 );

		$this->key = $this->args['option_name'];

		if ( ! class_exists( 'FusionRedux' ) ) {
			require_once wp_normalize_path( dirname( __FILE__ ) . '/redux/framework/fusionredux-framework.php' );
		}

		require_once wp_normalize_path( dirname( __FILE__ ) . '/redux/validation-functions.php' );
		if ( ! class_exists( 'Fusion_Redux_Custom_Fields' ) ) {
			require_once wp_normalize_path( dirname( __FILE__ ) . '/redux/class-fusion-redux-addons.php' );
			new Fusion_Redux_Addons( $this->args['option_name'] );
		}

		$version       = $this->args['version'];
		$version_array = explode( '.', $version );

		if ( isset( $version_array[2] ) && '0' === $version_array[2] ) {
			$version = $version_array[0] . '.' . $version_array[1];
		}

		$this->ver = $version;
		$this->add_config();
		$this->parse();

		add_action( 'fusionredux/page/' . $this->args['option_name'] . '/enqueue', [ $this, 'enqueue' ] );
		add_action( 'admin_head', [ $this, 'dynamic_css' ] );

		add_action( 'admin_init', [ $this, 'remove_fusionredux_notices' ] );
		add_action( 'admin_notices', [ $this, 'remove_fusionredux_notices' ], 999 );

		// Update option for fusion builder and code block encoding.
		add_action( 'fusionredux/options/' . $this->args['option_name'] . '/saved', [ $this, 'save_as_option' ], 10, 2 );

		// Reset caches when loading fusionredux. This is a hack for the preset options.
		add_action( 'fusion_fusionredux_header', [ $this, 'reset_cache' ] );
		// Make sure caches are reset when saving/resetting options.
		add_action( 'fusionredux/options/' . $this->args['option_name'] . '/reset', [ $this, 'reset_cache' ] );
		add_action( 'fusionredux/options/' . $this->args['option_name'] . '/section/reset', [ $this, 'reset_cache' ] );
		add_action( 'fusionredux/options/' . $this->args['option_name'] . '/saved', [ $this, 'reset_cache' ] );
		add_action( 'wp_ajax_custom_option_import', [ $this, 'reset_cache' ] );

		// Save all languages.
		add_action( 'fusionredux/options/' . $this->args['option_name'] . '/reset', [ $this, 'save_all_languages' ] );
		add_action( 'fusionredux/options/' . $this->args['option_name'] . '/section/reset', [ $this, 'save_all_languages' ] );
		add_action( 'fusionredux/options/' . $this->args['option_name'] . '/saved', [ $this, 'save_all_languages' ] );

		add_filter( 'fusionredux/' . $this->args['option_name'] . '/localize', [ $this, 'localize_data' ] );
		add_filter( 'fusionredux/' . $this->args['option_name'] . '/localize/reset', [ $this, 'reset_message_l10n' ] );
		add_filter( 'fusionredux/' . $this->args['option_name'] . '/localize/reset_section', [ $this, 'reset_section_message_l10n' ] );
		add_filter( 'fusionredux-import-file-description', [ $this, 'fusionredux_import_file_description_l10n' ] );

		add_filter( 'fusionredux/options/' . $this->args['option_name'] . '/ajax_save/response', [ $this, 'merge_options' ] );
	}

	/**
	 * Triggers the cache reset.
	 * Add functionality in child classes.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function reset_cache() {
		$fusion_cache = new Fusion_Cache();
		$fusion_cache->reset_all_caches();
	}

	/**
	 * Removes fusionredux admin notices & nag messages
	 * as well as the fusionredux demo mode.
	 *
	 * @access public
	 * @since 1.0
	 * return void
	 */
	public function remove_fusionredux_notices() {
		if ( class_exists( 'FusionReduxFrameworkPlugin' ) ) {
			remove_filter( 'plugin_row_meta', [ FusionReduxFrameworkPlugin::get_instance(), 'plugin_metalinks' ], null, 2 );
			remove_action( 'admin_notices', [ FusionReduxFrameworkPlugin::get_instance(), 'admin_notices' ] );
			remove_action( 'admin_notices', [ FusionReduxFrameworkInstances::get_instance( $this->args['option_name'] ), '_admin_notices' ], 99 );
			// Remove the admin metabox.
			remove_meta_box( 'fusionredux_dashboard_widget', 'dashboard', 'side' );
		}
	}

	/**
	 * The main parser
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function parse() {

		// Start looping through the sections from the $fusion_sections object.
		foreach ( $this->fusion_sections->sections as $section ) {

			// Create the section.
			$this->create_section( $section );

			// Start looping through the section's fields.
			// Make sure we have fields defined before proceeding.
			if ( isset( $section['fields'] ) ) {
				foreach ( $section['fields'] as $field ) {
					if ( isset( $field['type'] ) ) {
						if ( 'sub-section' === $field['type'] ) {
							if ( ! isset( $field['id'] ) ) {
								continue;
							}

							// This is a subsection so first we need to add the section.
							$this->create_subsection( $field );

							// Make sure we have fields defined before proceeding.
							// We'll need to add these fields to the subsection.
							if ( isset( $field['fields'] ) && is_array( $field['fields'] ) ) {
								foreach ( $field['fields'] as $subfield ) {
									if ( ! isset( $subfield['id'] ) ) {
										continue;
									}

									// Handle accordions in subsections.
									if ( isset( $subfield['type'] ) && 'accordion' === $subfield['type'] ) {
										// Make sure we have fields defined before proceeding.
										// We'll need to add these fields to the subsection.
										if ( isset( $subfield['fields'] ) && is_array( $subfield['fields'] ) ) {

											// Open the accordion.
											$accordion_start             = $subfield;
											$accordion_start['position'] = 'start';
											$accordion_start['id']       = $subfield['id'] . '_start_accordion';
											$this->create_field( $accordion_start, $field['id'] );

											// Add the fields inside the accordion.
											foreach ( $subfield['fields'] as $sub_subfield ) {
												$this->create_field( $sub_subfield, $field['id'] );
											}

											// Close the accordion.
											$accordion_end             = $subfield;
											$accordion_end['position'] = 'end';
											$accordion_end['id']       = $subfield['id'] . '_end_accordion';
											$this->create_field( $accordion_end, $field['id'] );
										}
									} else {
										$this->create_field( $subfield, $field['id'] );
									}
								}
							}
						} elseif ( 'accordion' === $field['type'] ) {

							// Make sure we have fields defined before proceeding.
							// We'll need to add these fields to the subsection.
							if ( isset( $field['fields'] ) && is_array( $field['fields'] ) ) {

								// Open the accordion.
								$accordion_start             = $field;
								$accordion_start['position'] = 'start';
								$accordion_start['id']       = $field['id'] . '_start_accordion';
								$this->create_field( $accordion_start, $section['id'] );

								// Add the fields inside the accordion.
								foreach ( $field['fields'] as $subfield ) {
									$this->create_field( $subfield, $section['id'] );
								}

								// Close the accordion.
								$accordion_end             = $field;
								$accordion_end['position'] = 'end';
								$accordion_end['id']       = $field['id'] . '_end_accordion';
								$this->create_field( $accordion_end, $section['id'] );
							}
						} else {
							$this->create_field( $field, $section['id'] );
						}
					}
				}
			}
		}
	}

	/**
	 * Create a section.
	 *
	 * @access public
	 * @param array $section The section arguments.
	 * @since 1.0
	 * @return void
	 */
	public function create_section( $section ) {

		if ( ! isset( $section['id'] ) ) {
			return;
		}

		if ( ! class_exists( 'FusionRedux' ) ) {
			return;
		}

		// Ability to hide sections via a filter.
		if ( 'fusion_options' === $this->args['option_name'] && 1 == apply_filters( 'fusion_builder_hide_theme_section', $section['id'] ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons
			return;
		}

		FusionRedux::setSection(
			$this->key,
			[
				'title'     => ( isset( $section['label'] ) ) ? $section['label'] : '',
				'id'        => $section['id'],
				'desc'      => ( isset( $section['description'] ) ) ? $section['description'] : '',
				'highlight' => ( isset( $section['highlight'] ) ) ? $section['highlight'] : '',
				'icon'      => ( isset( $section['icon'] ) ) ? $section['icon'] : 'el el-home',
				'class'     => ( isset( $section['class'] ) ) ? $section['class'] : '',
			]
		);
	}

	/**
	 * Creates a subsection.
	 *
	 * @access public
	 * @param array $subsection The subsection arguments.
	 * @since 1.0
	 * @return void
	 */
	public function create_subsection( $subsection ) {

		$args = [
			'title'      => ( isset( $subsection['label'] ) ) ? $subsection['label'] : '',
			'id'         => $subsection['id'],
			'subsection' => true,
			'desc'       => ( isset( $subsection['description'] ) ) ? $subsection['description'] : '',
			'highlight'  => ( isset( $subsection['highlight'] ) ) ? $subsection['highlight'] : '',
			'class'      => ( isset( $subsection['class'] ) ) ? $subsection['class'] : '',
			'hidden'     => ( isset( $subsection['hidden'] ) ) ? $subsection['hidden'] : false,
		];

		// Ability to hide sub sections via a filter.
		if ( 'fusion_options' === $this->args['option_name'] && 1 == apply_filters( 'fusion_builder_hide_theme_sub_section', $subsection['id'] ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			return;
		}

		if ( class_exists( 'FusionRedux' ) ) {
			FusionRedux::setSection( $this->key, $args );
		}
	}

	/**
	 * Gets a field.
	 *
	 * @access public
	 * @since 2.0
	 * @param array       $field      The field arguments.
	 * @param null|string $section_id The ID of the section.
	 * @return array|null             Returns null if there was an error when getting the field, otherwise an array.
	 */
	public function get_field( $field, $section_id = null ) {

		$args = [
			'section_id'  => $section_id,
			'title'       => ( isset( $field['label'] ) ) ? $field['label'] : '',
			'subtitle'    => ( isset( $field['description'] ) ) ? $field['description'] : '',
			'description' => ( isset( $field['help'] ) ) ? $field['help'] : '',
			'class'       => ( isset( $field['class'] ) ) ? $field['class'] . ' fusion_options' : 'fusion_options',
			'options'     => ( isset( $field['choices'] ) ) ? $field['choices'] : [],
			'required'    => [],
			'output'      => [],
		];

		if ( isset( $field['hidden'] ) && $field['hidden'] ) {
			$args['class'] .= ' hidden';
		}

		if ( isset( $field['required'] ) && is_array( $field['required'] ) && ! empty( $field['required'] ) ) {
			foreach ( $field['required'] as $requirement ) {
				$requirement['operator'] = ( '==' === $requirement['operator'] ) ? '=' : $requirement['operator'];
				$args['required'][]      = [
					$requirement['setting'],
					$requirement['operator'],
					$requirement['value'],
				];
			}
		} elseif ( isset( $args['required'] ) ) {
			unset( $args['required'] );
		}

		// This will allow us to have an 'options_mode' setting.
		// We can have 'simple', 'advanced' etc there, and options will be shown depending on our selection.
		if ( isset( $field['option_mode'] ) ) {
			if ( ! isset( $args['required'] ) ) {
				$args['required'] = [];
			}
			$args['required'][] = [ 'options_mode', '=', $field['option_mode'] ];
		}

		if ( ! isset( $field['type'] ) ) {
			return;
		}

		// Ability to hide options via a filter.
		if ( 'fusion_options' === $this->args['option_name'] && 1 == apply_filters( 'fusion_builder_hide_theme_option', $field['id'] ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons
			return;
		}

		$font_size_dimension_fields = apply_filters( 'fusion_options_font_size_dimension_fields', [] );

		switch ( $field['type'] ) {
			case 'color':
				if ( ! isset( $field['transparent'] ) ) {
					$args['transparent'] = false;
				}
				$args['validate_callback'] = 'fusion_fusionredux_validate_color_hex';
				break;
			case 'code':
				$args['type']  = 'ace_editor';
				$args['mode']  = ( isset( $args['options'] ) && isset( $args['options']['language'] ) ) ? $args['options']['language'] : 'css';
				$args['theme'] = ( isset( $args['choices'] ) && isset( $args['choices']['theme'] ) ) ? $args['choices']['theme'] : 'chrome';

				$args['options']['minLines'] = ( ! isset( $args['options']['minLines'] ) ) ? 18 : $args['options']['minLines'];
				$args['options']['maxLines'] = ( ! isset( $args['options']['maxLines'] ) ) ? 30 : $args['options']['maxLines'];

				if ( 'custom_css' === $field['id'] ) {
					$args['full_width'] = true;
				}
				break;
			case 'radio-buttonset':
				$args['type'] = 'button_set';
				break;
			case 'dimension':
				$args['type']              = 'text';
				$args['class']            .= ' dimension';
				$args['options']           = '';
				$args['validate_callback'] = 'fusion_fusionredux_validate_dimension';

				if ( in_array( $field['id'], $font_size_dimension_fields, true ) ) {
					$args['validate_callback'] = 'fusion_fusionredux_validate_font_size';
					/* translators: The description subtitle and an example value. */
					$args['subtitle'] = sprintf( esc_html__( '%1$s Enter value including CSS unit (px, em, rem), ex: %2$s.', 'Avada' ), $args['subtitle'], $field['default'] );
				} elseif ( 'text_column_spacing' === $field['id'] ) {
					/* translators: The description subtitle, percetange sign and an example value. */
					$args['subtitle'] = sprintf( esc_html__( '%1$s Enter value including any valid CSS unit besides %2$s which does not work for inline columns, ex: %3$s.', 'Avada' ), $args['subtitle'], '%', $field['default'] );
				} elseif ( 'page_title_height' === $field['id'] || 'page_title_mobile_height' === $field['id'] ) {
					/* translators: The description subtitle, percentage sign and an example value. */
					$args['subtitle'] = sprintf( esc_html__( '%1$s Enter value including any valid CSS unit besides %2$s which does not work for page title bar, ex: %3$s.', 'Avada' ), $args['subtitle'], '%', $field['default'] );
				} else {
					/* translators: The description subtitle and an example value. */
					$args['subtitle'] = sprintf( esc_html__( '%1$s Enter value including any valid CSS unit, ex: %2$s.', 'Avada' ), $args['subtitle'], $field['default'] );
				}
				break;
			case 'dimensions':
				if ( 'lightbox_video_dimensions' === $field['id'] || 'menu_arrow_size' === $field['id'] ) {
					/* translators: The description subtitle and an example value. */
					$args['subtitle'] = sprintf( esc_html__( '%1$s In pixels, ex: %2$s.', 'Avada' ), $args['subtitle'], implode( ', ', $field['default'] ) );
				} else {
					/* translators: The description subtitle and an example value. */
					$args['subtitle'] = sprintf( esc_html__( '%1$s Enter values including any valid CSS unit, ex: %2$s.', 'Avada' ), $args['subtitle'], implode( ', ', $field['default'] ) );
				}
				$args['validate_callback'] = 'fusion_fusionredux_validate_dimensions';
				break;
			case 'spacing':
				$args['top']               = ( isset( $field['choices'] ) && isset( $field['choices']['top'] ) );
				$args['bottom']            = ( isset( $field['choices'] ) && isset( $field['choices']['bottom'] ) );
				$args['left']              = ( isset( $field['choices'] ) && isset( $field['choices']['left'] ) );
				$args['right']             = ( isset( $field['choices'] ) && isset( $field['choices']['right'] ) );
				$args['validate_callback'] = 'fusion_fusionredux_validate_dimensions';

				$default = is_array( $field['default'] ) ? implode( ', ', $field['default'] ) : $field['default'];
				if ( is_array( $field['default'] ) && empty( array_filter( $field['default'] ) ) ) {
					$default = '10px, 1em';
				}
				/* translators: The description subtitle and an example value. */
				$args['subtitle'] = sprintf( esc_html__( '%1$s Enter values including any valid CSS unit, ex: %2$s.', 'Avada' ), $args['subtitle'], $default );
				break;
			case 'border_radius':
				$args['top_left']          = ( isset( $field['choices'] ) && isset( $field['choices']['top_left'] ) );
				$args['top_right']         = ( isset( $field['choices'] ) && isset( $field['choices']['top_right'] ) );
				$args['bottom_right']      = ( isset( $field['choices'] ) && isset( $field['choices']['bottom_right'] ) );
				$args['bottom_left']       = ( isset( $field['choices'] ) && isset( $field['choices']['bottom_left'] ) );
				$args['validate_callback'] = 'fusion_fusionredux_validate_dimensions';

				$default = is_array( $field['default'] ) ? implode( ', ', $field['default'] ) : $field['default'];
				/* translators: The description subtitle and an example value. */
				$args['subtitle'] = sprintf( esc_html__( '%1$s Enter values including any valid CSS unit, ex: %2$s.', 'Avada' ), $args['subtitle'], $default );
				break;
			case 'number':
				$args['type'] = 'spinner';
				if ( isset( $field['choices'] ) && isset( $field['choices']['min'] ) ) {
					$args['min'] = $field['choices']['min'];
				}
				if ( isset( $field['choices'] ) && isset( $field['choices']['max'] ) ) {
					$args['max'] = $field['choices']['max'];
				}
				if ( isset( $field['choices'] ) && isset( $field['choices']['step'] ) ) {
					$args['step'] = $field['choices']['step'];
				}
				break;
			case 'select':
				$args['width']             = 'width:100%;';
				$args['select3']           = [
					'minimumResultsForSearch' => '-1',
					'allowClear'              => false,
				];
				$args['validate_callback'] = 'fusion_fusionredux_validate_select';
				break;
			case 'slider':
				$not_in_pixels = apply_filters( 'fusion_options_sliders_not_in_pixels', [] );

				if ( ! in_array( $field['id'], $not_in_pixels, true ) ) {
					$args['subtitle'] = $args['subtitle'] . ' ' . esc_html__( 'In pixels.', 'Avada' );
				}

				if ( isset( $field['choices'] ) && isset( $field['choices']['min'] ) ) {
					$args['min'] = $field['choices']['min'];
				}
				if ( isset( $field['choices'] ) && isset( $field['choices']['max'] ) ) {
					$args['max'] = $field['choices']['max'];
				}
				if ( isset( $field['choices'] ) && isset( $field['choices']['step'] ) ) {
					$args['step'] = $field['choices']['step'];
				}
				if ( isset( $field['choices']['step'] ) && 1 > $field['choices']['step'] ) {
					$args['resolution'] = 0.1;
					if ( .1 > $field['choices']['step'] ) {
						$args['resolution'] = 0.01;
					} elseif ( .01 > $field['choices']['step'] ) {
						$args['resolution'] = 0.001;
					}
				}
				break;
			case 'switch':
			case 'toggle':
				$args['type'] = 'switch';
				if ( isset( $field['choices'] ) && isset( $field['choices']['on'] ) ) {
					$args['on'] = $field['choices']['on'];
				}
				if ( isset( $field['choices'] ) && isset( $field['choices']['off'] ) ) {
					$args['off'] = $field['choices']['off'];
				}
				break;
			case 'color-alpha':
				$args['type']              = 'color_alpha';
				$args['transparent']       = false;
				$args['validate_callback'] = 'fusion_fusionredux_validate_color_rgba';
				break;
			case 'color-palette':
				$args['type'] = 'color_palette';
				break;
			case 'typography-sets':
				$args['type'] = 'typography_sets';
				break;
			case 'preset':
			case 'preset':
				$args['type']    = 'image_select';
				$args['presets'] = true;
				$args['options'] = [];
				foreach ( $field['choices'] as $choice => $choice_args ) {
					if ( is_array( $choice_args ) ) {
						$args['options'][ $choice ] = [
							'alt'     => $choice_args['label'],
							'img'     => $choice_args['image'],
							'presets' => $choice_args['settings'],
						];
					}
				}
				break;
			case 'radio-image':
				$args['type']    = 'image_select';
				$args['options'] = [];
				foreach ( $field['choices'] as $id => $url ) {
					$args['options'][ $id ] = [
						'alt' => $id,
						'img' => $url,
					];
				}
				if ( 'header_layout' === $field['id'] ) {
					$args['full_width'] = true;
				}
				break;
			case 'upload':
			case 'media':
				$args['type'] = 'media';
				if ( isset( $field['default'] ) && ! is_array( $field['default'] ) ) {
					$args['default'] = empty( $field['default'] ) ? [] : $args['default'] = [ 'url' => $field['default'] ];
				}
				$this->media_fields[ $field['id'] ] = $field;
				break;
			case 'radio':
				$args['options'] = [];
				foreach ( $field['choices'] as $choice => $label ) {
					if ( is_array( $label ) ) {
						$args['options'][ $choice ] = '<span style="font-weight: bold; font-size: 1.1em; line-height: 2.2em;">' . $label[0] . '</span><p>' . $label[1] . '<p>';
					} else {
						$args['options'][ $choice ] = $label;
					}
				}
				break;
			case 'multicheck':
				$args['type'] = 'checkbox';
				break;
			case 'typography':
				$args['default'] = [];
				if ( isset( $field['default'] ) ) {
					if ( isset( $field['default']['font-weight'] ) ) {
						$args['default']['font-weight'] = $field['default']['font-weight'];
					}
					if ( isset( $field['default']['font-size'] ) ) {
						$args['default']['font-size'] = $field['default']['font-size'];
					}
					if ( isset( $field['default']['font-family'] ) ) {
						$args['default']['font-family'] = $field['default']['font-family'];
						$args['default']['font-backup'] = true;
						$args['default']['google']      = true;
					}
					if ( isset( $field['default']['line-height'] ) ) {
						$args['default']['line-height'] = $field['default']['line-height'];
					}
					if ( isset( $field['default']['word-spacing'] ) ) {
						$args['default']['word-spacing'] = $field['default']['word-spacing'];
					}
					if ( isset( $field['default']['letter-spacing'] ) ) {
						$args['default']['letter-spacing'] = $field['default']['letter-spacing'];
					}
					if ( isset( $field['default']['color'] ) ) {
						$args['default']['color'] = $field['default']['color'];
					}
					if ( isset( $field['default']['text-align'] ) ) {
						$args['default']['text-align'] = $field['default']['text-align'];
					}
					if ( isset( $field['default']['text-transform'] ) ) {
						$args['default']['text-transform'] = $field['default']['text-transform'];
					}
					if ( isset( $field['default']['margin-top'] ) ) {
						$args['default']['margin-top'] = $field['default']['margin-top'];
					}
					if ( isset( $field['default']['margin-bottom'] ) ) {
						$args['default']['margin-bottom'] = $field['default']['margin-bottom'];
					}
				}
				$args['fonts']          = Fusion_Data::standard_fonts();
				$args['font-backup']    = true;
				$args['font-style']     = ( isset( $args['default']['font-style'] ) || ( isset( $field['choices']['font-style'] ) && $field['choices']['font-style'] ) ) ? true : false;
				$args['font-weight']    = ( isset( $args['default']['font-weight'] ) || ( isset( $field['choices']['font-weight'] ) && $field['choices']['font-weight'] ) ) ? true : false;
				$args['font-size']      = ( isset( $args['default']['font-size'] ) || ( isset( $field['choices']['font-size'] ) && $field['choices']['font-size'] ) ) ? true : false;
				$args['font-family']    = ( isset( $args['default']['font-family'] ) || ( isset( $field['choices']['font-family'] ) && $field['choices']['font-family'] ) ) ? true : false;
				$args['line-height']    = ( isset( $args['default']['line-height'] ) || ( isset( $field['choices']['line-height'] ) && $field['choices']['line-height'] ) ) ? true : false;
				$args['word-spacing']   = ( isset( $args['default']['word-spacing'] ) || ( isset( $field['choices']['word-spacing'] ) && $field['choices']['word-spacing'] ) ) ? true : false;
				$args['letter-spacing'] = ( isset( $args['default']['word-spacing'] ) || ( isset( $field['choices']['letter-spacing'] ) && $field['choices']['letter-spacing'] ) ) ? true : false;
				$args['text-align']     = ( isset( $args['default']['text-align'] ) || ( isset( $field['choices']['text-align'] ) && $field['choices']['text-align'] ) ) ? true : false;
				$args['text-transform'] = ( isset( $args['default']['text-transform'] ) || ( isset( $field['choices']['text-transform'] ) && $field['choices']['text-transform'] ) ) ? true : false;
				$args['color']          = ( isset( $args['default']['color'] ) || ( isset( $field['choices']['color'] ) && $field['choices']['color'] ) ) ? true : false;
				$args['margin-top']     = ( isset( $args['default']['margin-top'] ) || ( isset( $field['choices']['margin-top'] ) && $field['choices']['margin-top'] ) ) ? true : false;
				$args['margin-bottom']  = ( isset( $args['default']['margin-bottom'] ) || ( isset( $field['choices']['margin-bottom'] ) && $field['choices']['margin-bottom'] ) ) ? true : false;

				$args['select3'] = [
					'allowClear' => false,
				];

				$args['validate_callback'] = 'fusion_fusionredux_validate_typography';

				break;
			case 'repeater':
				$args['fields']       = [];
				$args['group_values'] = true;
				$args['sortable']     = true;
				$i                    = 0;
				foreach ( $field['fields'] as $repeater_field_id => $repeater_field_args ) {
					$repeater_field_args['label'] = ( isset( $repeater_field_args['label'] ) ) ? $repeater_field_args['label'] : '';
					$args['fields'][ $i ]         = [
						'id'          => $repeater_field_id,
						'type'        => isset( $repeater_field_args['type'] ) ? $repeater_field_args['type'] : 'text',
						'title'       => $repeater_field_args['label'],
						'placeholder' => ( isset( $repeater_field_args['default'] ) ) ? $repeater_field_args['default'] : $repeater_field_args['label'],
					];
					if ( isset( $repeater_field_args['choices'] ) ) {
						$args['fields'][ $i ]['options'] = $repeater_field_args['choices'];
					}
					if ( isset( $repeater_field_args['type'] ) && 'select' === $repeater_field_args['type'] ) {
						$args['fields'][ $i ]['width']   = 'width:100%;';
						$args['fields'][ $i ]['select3'] = [
							'minimumResultsForSearch' => '-1',
						];
					}
					if ( isset( $repeater_field_args['type'] ) && 'color' === $repeater_field_args['type'] ) {
						$args['fields'][ $i ]['transparent'] = false;
					}
					if ( isset( $repeater_field_args['type'] ) && ( 'upload' === $repeater_field_args['type'] || 'media' === $repeater_field_args['type'] ) ) {
						$args['fields'][ $i ]['type'] = 'media';
						if ( isset( $repeater_field_args['mode'] ) ) {
							$args['fields'][ $i ]['mode'] = $repeater_field_args['mode'];
						}
						if ( isset( $repeater_field_args['preview'] ) ) {
							$args['fields'][ $i ]['preview'] = $repeater_field_args['preview'];
						}
					}

					$i++;
				}
				unset( $args['options'] );
				if ( 'custom_fonts' === $field['id'] ) {
					$args['validate_callback'] = 'fusion_fusionredux_validate_custom_fonts';
				}
				break;
			case 'accordion':
				$args['type']     = 'accordion';
				$args['title']    = $field['label'];
				$args['subtitle'] = ( isset( $field['description'] ) ) ? $field['description'] : '';
				unset( $field['fields'] );
				unset( $field['label'] );
				unset( $field['default'] );
				unset( $field['options'] );
				break;
			case 'custom':
				$args['type']       = 'raw';
				$args['full_width'] = isset( $field['subtitle'] ) ? false : true;
				if ( isset( $field['style'] ) && 'heading' === $field['style'] ) {
					$args['content'] = '<div class="fusionredux-field-info"><p class="fusionredux-info-desc" style="font-size:13px;"><b>' . $field['description'] . '</b></p></div>';
					$args['class']  .= ' custom-heading';
				} else {
					$args['content'] = $field['description'];
					$args['class']  .= ' custom-info';
				}
				$args['description'] = '';
				$args['subtitle']    = isset( $field['subtitle'] ) ? $field['subtitle'] : '';
				$args['raw_html']    = true;
				break;
		}

		// Add validation to the email field.
		if ( isset( $field['id'] ) && 'email_address' === $field['id'] ) {
			$args['validate'] = 'email';
		}

		$args = wp_parse_args( $args, $field );
		$args = $this->apply_soft_dependency( $args );

		// Only process required arguments if we don't pass "disable_dependencies={$args['id']}" in the URL.
		if ( $_GET && isset( $_GET['disable_dependencies'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			if ( $_GET['disable_dependencies'] === $args['id'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				$args['required'] = [];
			}
			if ( ! empty( $args['required'] ) ) {
				foreach ( $args['required'] as $key => $requirement ) {
					if ( isset( $requirement['setting'] ) && $_GET['disable_dependencies'] === $requirement['setting'] ) { // phpcs:ignore WordPress.Security.NonceVerification
						unset( $args['required'][ $key ] );
					}
				}
			}
		}

		// Disable all dependencies if needed.
		if ( true === $this->args['disable_dependencies'] ) {
			$args['required'] = [];
		}

		return $args;
	}

	/**
	 * Alters a field's arguments to add soft dependencies.
	 *
	 * @access public
	 * @since 2.0
	 * @param array $args  The field arguments.
	 * @param bool  $panel Whether we're in the frontend panel or not.
	 * @return array       Returns the field $args, modified.
	 */
	public function apply_soft_dependency( $args, $panel = false ) {
		if ( isset( $args['soft_dependency'] ) && $args['soft_dependency'] && 'custom' !== $args['type'] && 'raw' !== $args['type'] ) {

			$correlation_link = '  <span class="fusion-hover-description"><a href="https://theme-fusion.com/documentation/avada/options/how-options-work/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'This is a dependent option that always stays visible because other options can utilize it.', 'Avada' ) . '</a></span>';

			if ( ! $panel ) {
				$args['subtitle'] .= $correlation_link;
			} elseif ( isset( $args['description'] ) ) {
				$args['description'] .= $correlation_link;
			}

			$dep_key = $panel ? 'setting' : 0;
		}
		return $args;
	}

	/**
	 * Creates a field.
	 *
	 * @access public
	 * @since 1.0
	 * @param array       $field      The field arguments.
	 * @param null|string $section_id The ID of the section.
	 * @return void
	 */
	public function create_field( $field, $section_id = null ) {
		$args = $this->get_field( $field, $section_id );

		if ( ! $args ) {
			return;
		}

		if ( class_exists( 'FusionRedux' ) ) {
			FusionRedux::setField( $this->key, $args );
		}
	}

	/**
	 * Enqueue additional scripts.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function enqueue() {
		$vars = [
			'option_name'        => $this->args['option_name'],
			'theme_skin'         => esc_html__( 'Theme Skin', 'Avada' ),
			'color_scheme'       => esc_html__( 'Color Scheme', 'Avada' ),
			'theme_options_name' => ( class_exists( 'Avada' ) ) ? Avada::get_option_name() : 'fusion_theme_options',
		];
		wp_register_script( 'fusion-redux-custom-js', trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/assets/fusion-redux.js', [ 'jquery' ], time(), true );
		wp_localize_script( 'fusion-redux-custom-js', 'fusionFusionreduxVars', $vars );
		wp_enqueue_script( 'fusion-redux-custom-js' );

		wp_enqueue_script( 'fusion-redux-reset-caches', trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/assets/fusion-reset-caches.js', [], time(), false );
		wp_localize_script(
			'fusion-redux-reset-caches',
			'fusionReduxResetCaches',
			[
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'general'   => [
					'confirm' => esc_html__( 'Are you sure you want to reset all Avada caches?', 'Avada' ),
					'success' => esc_html__( 'All Avada caches have been reset.', 'Avada' ),
				],
				'mailchimp' => [
					'confirm' => esc_html__( 'Are you sure you want to reset all Mailchimp caches?', 'Avada' ),
					'success' => esc_html__( 'All Mailchimp caches have been reset.', 'Avada' ),
				],
				'hubspot'   => [
					'confirm' => esc_html__( 'Are you sure you want to reset all HubSpot caches?', 'Avada' ),
					'success' => esc_html__( 'All HubSpot caches have been reset.', 'Avada' ),
				],
				'instagram' => [
					'confirm' => esc_html__( 'Are you sure you want to reset all Instagram caches?', 'Avada' ),
					'success' => esc_html__( 'All Instagram caches have been reset.', 'Avada' ),
				],
				'adobe'     => [
					'confirm' => esc_html__( 'Are you sure you want to reset all Adobe caches?', 'Avada' ),
					'success' => esc_html__( 'All Adobe caches have been reset. Refresh the page to see the changes.', 'Avada' ),
				],
			]
		);
	}

	/**
	 * Applies custom CSS in the panel
	 * so that it matches the selected admin-colors.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function dynamic_css() {
		$screen = get_current_screen();

		// Early exit if we're not in the fusionredux panel.
		if ( is_null( $screen ) || 'appearance_page_avada_options' !== $screen->id && 'fusion-builder_page_fusion-element-options' !== $screen->id ) {
			return;
		}

		// Get the user's admin colors.
		$color_scheme = get_user_option( 'admin_color' );

		// If no theme is active set it to 'fresh'.
		if ( empty( $color_scheme ) ) {
			$color_scheme = 'fresh';
		}

		$main_colors = $this->get_main_colors( $color_scheme );
		$text_colors = $this->get_text_colors( $color_scheme );

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();
		}

		$styles = $wp_filesystem->get_contents( dirname( __FILE__ ) . '/redux/assets/style.css' );

		if ( ! $styles || empty( $styles ) ) {
			ob_start();
			include wp_normalize_path( dirname( __FILE__ ) . '/redux/assets/style.css' );
			$styles = ob_get_clean();
		}

		if ( $styles && ! empty( $styles ) ) {

			$themefusion_logo = trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/assets/themefusion_logo_white.png';

			$styles = str_replace( '$color_back_1', $main_colors['color_back_1'], $styles );
			$styles = str_replace( '$color_back_2', $main_colors['color_back_2'], $styles );
			$styles = str_replace( '$color_back_top_level_hover', $main_colors['color_back_top_level_hover'], $styles );
			$styles = str_replace( '$color_back_top_level_active', $main_colors['color_back_top_level_active'], $styles );
			$styles = str_replace( '$color_accent_1', $main_colors['color_accent_1'], $styles );
			$styles = str_replace( '$color_accent_2', $main_colors['color_accent_2'], $styles );

			$styles = str_replace( '$color_text_menu_top_level_hover', $text_colors['menu_top_level_hover'], $styles );
			$styles = str_replace( '$color_text_menu_sub_level_hover', $text_colors['menu_sub_level_hover'], $styles );
			$styles = str_replace( '$color_text_menu_top_level_active', $text_colors['menu_top_level_active'], $styles );
			$styles = str_replace( '$color_text_menu_sub_level_active', $text_colors['menu_sub_level_active'], $styles );
			$styles = str_replace( '$color_text_menu_top_level', $text_colors['menu_top_level'], $styles );
			$styles = str_replace( '$color_text_menu_sub_level', $text_colors['menu_sub_level'], $styles );

			$styles = str_replace( '$themefusion_logo', $themefusion_logo, $styles );

			// Add custom fonts.
			if ( function_exists( 'fusion_custom_fonts_font_faces' ) ) {
				$styles .= fusion_custom_fonts_font_faces();
			}

			/**
			 * Security: The use of wp_strip_all_tags() here prevents malicious attempts
			 * to close the <style> tag and open a <script> tag.
			 */
			echo '<style id="fusion-redux-custom-styles" type="text/css">' . wp_strip_all_tags( $styles ) . '</style>'; // phpcs:ignore WordPress.Security
		}
	}

	/**
	 * Gets the main admin-color scheme.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $scheme The color scheme to use.
	 * @return array
	 */
	public function get_main_colors( $scheme ) {
		$main_colors = [
			'color_back_1'                => '',
			'color_back_2'                => '',
			'color_back_top_level_hover'  => '',
			'color_back_top_level_active' => '',
			'color_accent_1'              => '',
			'color_accent_2'              => '',
		];

		// Get the active admin theme.
		global $_wp_admin_css_colors;

		if ( ! isset( $_wp_admin_css_colors[ $scheme ] ) ) {
			$scheme = 'fresh';
		}

		$colors = (array) $_wp_admin_css_colors[ $scheme ];

		if ( isset( $colors['colors'] ) ) {
			$main_colors['color_accent_1'] = ( isset( $colors['colors'][2] ) ) ? $colors['colors'][2] : $main_colors['color_accent_1'];
			$main_colors['color_accent_2'] = ( isset( $colors['colors'][3] ) ) ? $colors['colors'][3] : $main_colors['color_accent_2'];
		}

		switch ( $scheme ) {
			case 'fresh':
				$main_colors['color_back_1']                = '#32373c';
				$main_colors['color_back_2']                = '#23282d';
				$main_colors['color_back_top_level_hover']  = '#191e23';
				$main_colors['color_back_top_level_active'] = '#198FD9';
				$main_colors['color_accent_1']              = '#0e7abd';
				$main_colors['color_accent_2']              = '#198FD9';
				break;
			case 'light':
				$main_colors['color_back_1']                = '#fff';
				$main_colors['color_back_2']                = '#e5e5e5';
				$main_colors['color_back_top_level_hover']  = '#888';
				$main_colors['color_back_top_level_active'] = '#888';
				break;
			case 'blue':
				$main_colors['color_back_1']                = '#4796b3';
				$main_colors['color_back_2']                = '#52accc';
				$main_colors['color_back_top_level_hover']  = '#096484';
				$main_colors['color_back_top_level_active'] = '#096484';
				$main_colors['color_accent_1']              = '#e1a948';
				break;
			case 'coffee':
				$main_colors['color_back_1']                = '#46403c';
				$main_colors['color_back_2']                = '#59524c';
				$main_colors['color_back_top_level_hover']  = '#c7a589';
				$main_colors['color_back_top_level_active'] = '#c7a589';
				break;
			case 'ectoplasm':
				$main_colors['color_back_1']                = '#413256';
				$main_colors['color_back_2']                = '#523f6d';
				$main_colors['color_back_top_level_hover']  = '#a3b745';
				$main_colors['color_back_top_level_active'] = '#a3b745';
				break;
			case 'midnight':
				$main_colors['color_back_1']                = '#26292c';
				$main_colors['color_back_2']                = '#363b3f';
				$main_colors['color_back_top_level_hover']  = '#e14d43';
				$main_colors['color_back_top_level_active'] = '#e14d43';
				break;
			case 'ocean':
				$main_colors['color_back_1']                = '#627c83';
				$main_colors['color_back_2']                = '#738e96';
				$main_colors['color_back_top_level_hover']  = '#9ebaa0';
				$main_colors['color_back_top_level_active'] = '#9ebaa0';
				break;
			case 'sunrise':
				$main_colors['color_back_1']                = '#be3631';
				$main_colors['color_back_2']                = '#cf4944';
				$main_colors['color_back_top_level_hover']  = '#dd823b';
				$main_colors['color_back_top_level_active'] = '#dd823b';
				break;
			default:
				if ( isset( $colors['colors'] ) ) {
					$main_colors['color_back_1']                = ( isset( $colors['colors'][0] ) ) ? $colors['colors'][0] : $main_colors['color_back_1'];
					$main_colors['color_back_2']                = ( isset( $colors['colors'][1] ) ) ? $colors['colors'][1] : $main_colors['color_back_2'];
					$main_colors['color_back_top_level_hover']  = ( isset( $colors['colors'][2] ) ) ? $colors['colors'][2] : $main_colors['color_accent_1'];
					$main_colors['color_back_top_level_active'] = ( isset( $colors['colors'][2] ) ) ? $colors['colors'][2] : $main_colors['color_accent_1'];
				}
		}
		return $main_colors;
	}

	/**
	 * Gets the text colors depending on the admin-color-scheme.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $scheme The selected admin theme.
	 * @return array
	 */
	public function get_text_colors( $scheme ) {
		$text_colors = [];

		switch ( $scheme ) {
			case 'fresh':
				$text_colors['menu_top_level']        = '#eee';
				$text_colors['menu_sub_level']        = 'rgba(240, 245, 250, 0.7)';
				$text_colors['menu_top_level_hover']  = '#198FD9';
				$text_colors['menu_sub_level_hover']  = '#198FD9';
				$text_colors['menu_top_level_active'] = '#fff';
				$text_colors['menu_sub_level_active'] = '#fff';
				break;
			case 'light':
				$text_colors['menu_top_level']        = '#333';
				$text_colors['menu_sub_level']        = '#686868';
				$text_colors['menu_top_level_hover']  = '#fff';
				$text_colors['menu_sub_level_hover']  = '#198FD9';
				$text_colors['menu_top_level_active'] = '#fff';
				$text_colors['menu_sub_level_active'] = '#333';
				break;
			case 'blue':
				$text_colors['menu_top_level']        = '#fff';
				$text_colors['menu_sub_level']        = '#e2ecf1';
				$text_colors['menu_top_level_hover']  = '#fff';
				$text_colors['menu_sub_level_hover']  = '#fff';
				$text_colors['menu_top_level_active'] = '#fff';
				$text_colors['menu_sub_level_active'] = '#fff';
				break;
			case 'coffee':
				$text_colors['menu_top_level']        = '#fff';
				$text_colors['menu_sub_level']        = '#cdcbc9';
				$text_colors['menu_top_level_hover']  = '#fff';
				$text_colors['menu_sub_level_hover']  = '#c7a589';
				$text_colors['menu_top_level_active'] = '#fff';
				$text_colors['menu_sub_level_active'] = '#fff';
				break;
			case 'ectoplasm':
				$text_colors['menu_top_level']        = '#fff';
				$text_colors['menu_sub_level']        = '#cbc5d3';
				$text_colors['menu_top_level_hover']  = '#fff';
				$text_colors['menu_sub_level_hover']  = '#a3b745';
				$text_colors['menu_top_level_active'] = '#fff';
				$text_colors['menu_sub_level_active'] = '#fff';
				break;
			case 'midnight':
				$text_colors['menu_top_level']        = '#fff';
				$text_colors['menu_sub_level']        = '#c3c4c5';
				$text_colors['menu_top_level_hover']  = '#fff';
				$text_colors['menu_sub_level_hover']  = '#e14d43';
				$text_colors['menu_top_level_active'] = '#fff';
				$text_colors['menu_sub_level_active'] = '#fff';
				break;
			case 'ocean':
				$text_colors['menu_top_level']        = '#fff';
				$text_colors['menu_sub_level']        = '#d5dde0';
				$text_colors['menu_top_level_hover']  = '#fff';
				$text_colors['menu_sub_level_hover']  = '#9ebaa0';
				$text_colors['menu_top_level_active'] = '#fff';
				$text_colors['menu_sub_level_active'] = '#fff';
				break;
			case 'sunrise':
				$text_colors['menu_top_level']        = '#fff';
				$text_colors['menu_sub_level']        = '#f1c8c7';
				$text_colors['menu_top_level_hover']  = '#fff';
				$text_colors['menu_sub_level_hover']  = '#f7e3d3';
				$text_colors['menu_top_level_active'] = '#fff';
				$text_colors['menu_sub_level_active'] = '#fff';
				break;
			default:
				$text_colors['menu_top_level']        = '#eee';
				$text_colors['menu_sub_level']        = 'rgba(240, 245, 250, 0.7)';
				$text_colors['menu_top_level_hover']  = '#00b9eb';
				$text_colors['menu_sub_level_hover']  = '#00b9eb';
				$text_colors['menu_top_level_active'] = '#fff';
				$text_colors['menu_sub_level_active'] = '#fff';
		}

		return $text_colors;
	}

	/**
	 * Create the FusionRedux Config.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function add_config() {

		$args = apply_filters(
			'fusion_fusionredux_args',
			[
				'opt_name'            => $this->key,
				'is_language_all'     => $this->args['is_language_all'],
				'default_language'    => $this->args['default_language'],
				'display_name'        => $this->args['display_name'],
				'display_version'     => $this->ver,
				'allow_sub_menu'      => true,
				'menu_title'          => $this->args['menu_title'],
				'page_title'          => $this->args['page_title'],
				'async_typography'    => true,
				'admin_bar'           => false,
				'admin_bar_icon'      => 'dashicons-portfolio',
				'admin_bar_priority'  => 50,
				'global_variable'     => $this->args['global_variable'],
				'update_notice'       => true,
				'page_parent'         => $this->args['page_parent'],
				'page_slug'           => $this->args['page_slug'],
				'menu_type'           => $this->args['menu_type'],
				'page_permissions'    => $this->args['page_permissions'],
				'dev_mode'            => false,
				'customizer'          => false,
				'default_show'        => false,
				'templates_path'      => dirname( __FILE__ ) . '/redux/panel_templates/',
				'show_options_object' => false,
				'forced_dev_mode_off' => true,
				'footer_credit'       => ' ',
				'allow_tracking'      => false,
				'ajax_save'           => FUSION_AJAX_SAVE,
			]
		);
		if ( class_exists( 'FusionRedux' ) ) {
			FusionRedux::setArgs( $this->key, $args );
		}

	}

	/**
	 * Extra functionality on save.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $data           The data.
	 * @param array $changed_values The changed values to save.
	 * @return void
	 */
	public function save_as_option( $data, $changed_values ) {
	}

	/**
	 * Extra functionality on save.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $data The data.
	 * @return array
	 */
	public function merge_options( $data ) {

		$data = (array) $data;

		$previous_options = get_option( Fusion_Settings::get_option_name(), [] );
		$data['options']  = array_replace_recursive( $previous_options, $data['options'] );

		return $data;

	}

	/**
	 * When in Polylang or WPML we're using "all" languages,
	 * saved options should be copied to ALL languages.
	 *
	 * @access public
	 * @since 1.0.2
	 * @return void
	 */
	public function save_all_languages() {

		$is_all = self::$is_language_all;
		if ( ! $is_all ) {

			$parsed_url = fusion_get_referer();

			// Check the HTTP referrer to determine if the language is set to "all".
			if ( $parsed_url ) {

				if ( ! function_exists( 'wp_parse_url' ) ) {
					require_once wp_normalize_path( ABSPATH . '/wp-includes/http.php' );
				}

				$parsed_url = wp_parse_url( $parsed_url );

				if ( isset( $parsed_url['query'] ) ) {
					parse_str( $parsed_url['query'], $query_args );
					if ( isset( $query_args['lang'] ) && 'all' === $query_args['lang'] ) {
						$is_all = true;
					}
				}
			}
		}

		if ( ! $is_all ) {
			return;
		}

		// Get the options.
		$option_name          = $this->args['option_name'];
		$original_option_name = $this->args['original_option_name'];
		$options              = get_option( $option_name );

		// Get available languages.
		$all_languages = Fusion_Multilingual::get_available_languages();

		// Get default language.
		$default_language = Fusion_Multilingual::get_default_language();

		if ( 'en' !== $default_language ) {
			update_option( $original_option_name . '_' . $default_language, $options );
			update_option( $original_option_name, $options );
		}

		foreach ( $all_languages as $language ) {

			// Skip English.
			if ( '' === $language || 'en' === $language ) {
				continue;
			}

			// Skip the main language if something other than English.
			// We've already handled that above.
			if ( 'en' !== $default_language && $default_language === $language ) {
				continue;
			}

			// Copy options to the new language.
			update_option( $original_option_name . '_' . $language, $options );

		}

	}

	/**
	 * Localize some of the args.
	 *
	 * @access public
	 * @since 2.2.2
	 * @param array $localize_data Localized data array.
	 * @return array The adjusted localized data.
	 */
	public function localize_data( $localize_data ) {
		$localize_data['args']['is_language_all']  = self::$is_language_all;
		$localize_data['args']['default_language'] = $this->args['default_language'];

		return $localize_data;
	}


	/**
	 * Modify the FusionRedux reset message (global).
	 *
	 * @access public
	 * @since 1.0
	 * @return string
	 */
	public function reset_message_l10n() {
		return esc_html__( 'Are you sure? This will reset all saved options to the default Avada Classic Global Options. This does not reset them to any other demo that you may have imported.', 'Avada' );
	}

	/**
	 * Modify the FusionRedux reset message (section)
	 *
	 * @access public
	 * @since 1.0
	 * @return string
	 */
	public function reset_section_message_l10n() {
		return esc_html__( 'Are you sure? This will reset all saved options to the default Avada Classic Global Options for this section. This does not reset them to any other demo that you may have imported.', 'Avada' );
	}

	/**
	 * Modify the import file description
	 *
	 * @access public
	 * @since 1.0
	 * @return string
	 */
	public function fusionredux_import_file_description_l10n() {
		return esc_html__( 'Copy the contents of the json file and paste it below. Then click "Import" to restore your setings.', 'Avada' );
	}

	/**
	 * Fires after the value of the option has been successfully updated.
	 * We'll be using this function to update any 3rd-party options injected.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param mixed  $old_value The old option value.
	 * @param mixed  $value     The new option value.
	 * @param string $option    Option name.
	 * @return void
	 */
	public function option_name_settings_update( $old_value, $value, $option ) {
		$other_options = [];
		// No need to proceed any further if we don't have any options to process.
		if ( empty( self::$option_name_settings ) ) {
			return;
		}
		foreach ( self::$option_name_settings as $setting => $option_name ) {
			// Get the option_name setting value.
			if ( ! isset( $other_options[ $option_name ] ) ) {
				$other_options[ $option_name ] = get_option( $option_name, [] );
			}
			// Set the value to the new option.
			if ( isset( $value[ $setting ] ) ) {
				$other_options[ $option_name ][ $setting ] = $value[ $setting ];
			}
		}
		// Save the new options.
		foreach ( $other_options as $other_option_name => $other_option_value ) {
			update_option( $other_option_name, $other_option_value );
		}
	}

	/**
	 * Adds an admin notice for remote URLs in media fields.
	 *
	 * @access public
	 * @since 1.1.0
	 * @return void
	 */
	public function admin_notice() {

		$wpurl = content_url();
		$wpurl = str_replace( [ 'http://', 'https://' ], '//', $wpurl );

		$dismissed = get_option( 'fusionredux_hide_ajax_notification', false );
		if ( $dismissed ) {
			return;
		}

		foreach ( $this->media_fields as $field ) {
			$options = get_option( $this->args['option_name'], [] );
			if ( ! isset( $options[ $field['id'] ] ) ) {
				continue;
			}
			// Skip empty fields.
			if ( ! isset( $options[ $field['id'] ]['url'] ) || empty( $options[ $field['id'] ]['url'] ) ) {
				unset( $this->media_fields[ $field['id'] ] );
				continue;
			}
			// Skip fields that contain the $wpurl.
			if ( false !== strpos( $options[ $field['id'] ]['url'], $wpurl ) ) {
				unset( $this->media_fields[ $field['id'] ] );
				continue;
			}
		}
		// No fields with remote URLs were found, early exit.
		if ( empty( $this->media_fields ) ) {
			return;
		}
		?>
		<div id="remote-media-found-in-fusion-options" class="notice notice-error avada-db-card avada-db-notice settings-error" style="max-width: 1200px; box-sizing: border-box;">
			<strong><span class="avada-db-settings-error-heading"><?php esc_html_e( 'Media fields using remote URLs were detected in your Global Options', 'Avada' ); ?></span></strong>
			<ul>
				<?php foreach ( $this->media_fields as $field ) : ?>
					<li><span><?php echo esc_html( $field['label'] ); ?></span></li>
				<?php endforeach; ?>
			</ul>
			<p><?php esc_html_e( 'Please replace them with locally-imported files from your media library.', 'Avada' ); ?></p>
			<span id="fusion-redux-remote-media-ajax-notification-nonce" class="hidden">
				<?php echo esc_attr( wp_create_nonce( 'avada-redux-ajax-notification-nonce' ) ); ?>
			</span>
			<button id="dismiss-fusion-redux-ajax-notification" class="notice-dismiss">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss Message', 'Avada' ); ?></span>
			</button>
		</div>
		<?php
	}

	/**
	 * Dismisses the remote-media-found-in-fusion-options admin notice.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function hide_remote_media_admin_notification() {

		$nonce = false;
		if ( isset( $_REQUEST['nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ); // phpcs:ignore WordPress.Security
		}
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'avada-redux-ajax-notification-nonce' ) ) {
			return;
		}
		if ( update_option( 'fusionredux_hide_ajax_notification', true ) ) {
			die( '1' );
		}
		die( '0' );
	}

	/**
	 * Handles resetting caches.
	 *
	 * @access public
	 * @since 2.0
	 * @return void
	 */
	public function reset_caches_handler() {
		if ( is_multisite() && is_main_site() ) {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				fusion_reset_all_caches();
				restore_current_blog();
			}
			return;
		}
		fusion_reset_all_caches();
	}
}

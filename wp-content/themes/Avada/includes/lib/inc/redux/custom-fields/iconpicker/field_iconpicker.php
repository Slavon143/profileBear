<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'FusionReduxFramework_iconpicker' ) ) {
		class FusionReduxFramework_iconpicker {

			protected $parent;
			protected $field;
			protected $value;

			/**
			 * Field Constructor.
			 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
			 *
			 * @since FusionReduxFramework 1.0.0
			 */
			function __construct( $field = array(), $value = '', $parent = null ) {
				$this->parent = $parent;
				$this->field  = $field;
				$this->value  = $value;
			} //function

			/**
			 * Field Render Function.
			 * Takes the vars and outputs the HTML for the field in the settings
			 *
			 * @since FusionReduxFramework 1.0.0
			 */
			function render() {

				/*
				 * So, in_array() wasn't doing it's job for checking a passed array for a proper value.
				 * It's wonky.  It only wants to check the keys against our array of acceptable values, and not the key's
				 * value.  So we'll use this instead.  Fortunately, a single no array value can be passed and it won't
				 * take a dump.
				 */

				// No errors please
				$defaults = [
					'default' => '',
				];

				$this->field = wp_parse_args( $this->field, $defaults );

				echo '<fieldset id="' . $this->field['id'] . '" class="fusionredux-container-iconpicker" data-id="' . $this->field['id'] . '">';
				echo '<h4>' . esc_html__( 'Icon', 'Avada' ) . '</h4>';
				echo '<span class="description">' . esc_html__( 'Click an icon to select, click again to deselect.', 'Avada' )  . '</span>';
				echo '<div class="nav-menus-php"><div class="option-field fusion-iconpicker">';
				echo '<input type="hidden" class="fusion-iconpicker-input" data-id="' . $this->field['id'] . '"
				value="' . $this->value . '" id="' . $this->field['id'] . '" name="' . $this->field['name'] . $this->field['name_suffix'] .'"/>';
				echo '<div class="fusion-iconpicker-preview">';
				echo '<input type="text" style="width: calc(100% - 42px) !important;" class="fusion-icon-search fusion-hide-from-atts fusion-dont-update" placeholder="' . esc_html__( 'Search', 'Avada' ) . '" />' .
				'<span class="input-icon fusiona-search"></span>' .
				'<span class="add-custom-icons">' .
				'<a href="post-new.php?post_type=fusion_icons" target="_blank" class="fusiona-plus"></a>' .
				'</span>';
				echo '</div>';
				echo '<div class="fusion-iconselect-wrapper"><div class="icon_select_container"></div></div>';
				echo '</fieldset>';

			}

			/**
			 * Enqueue Function.
			 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
			 *
			 * @since FusionReduxFramework 1.0.0
			 */
			function enqueue() {
				global $fusion_library_latest_version;
				wp_enqueue_script( 'fusion-menu-options', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/fusion-menu-options.js', [ 'selectwoo-js' ], time(), true );
				wp_enqueue_style( 'fontawesome', Fusion_Font_Awesome::get_backend_css_url(), [], time() );
				wp_enqueue_script( 'fusion_app_option_icon_picker', FUSION_LIBRARY_URL . '/inc/fusion-app/options/icon-picker.js', [], $fusion_library_latest_version, true );

				wp_enqueue_script(
					'fusionredux_field_iconpicker_js',
					trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/custom-fields/iconpicker/field_iconpicker.js',
					array( 'jquery', 'fusionredux-js' ),
					time(),
					true
				);

				wp_enqueue_style(
					'fusionredux-field-iconpicker-css',
					trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/custom-fields/iconpicker/field_iconpicker.css',
					array(),
					time(),
					'all'
				);

				wp_enqueue_script(
					'selectwoo-js',
					Avada::$template_dir_url . '/assets/admin/js/selectWoo.full.min.js',
					[ 'jquery' ],
					'1.0.2',
					false
				);
				wp_enqueue_script( 'fuse-script', FUSION_LIBRARY_URL . '/assets/min/js/library/fuse.js', [], $fusion_library_latest_version, false );
				wp_enqueue_script( 'fontawesome-search-script', FUSION_LIBRARY_URL . '/assets/fonts/fontawesome/js/icons-search-free.js', [], $fusion_library_latest_version, false );



				wp_localize_script(
					'fusion-menu-options',
					'fusionMenuConfig',
					[
						'fontawesomeicons'   => fusion_get_icons_array(),
						'fontawesomesubsets' => Avada()->settings->get( 'status_fontawesome' ),
						'customIcons'        => fusion_get_custom_icons_array(),

						/* translators: The iconset name. */
						'no_results_in'      => esc_html__( 'No Results in "%s"', 'fusion-builder' ),
					]
				);
				wp_localize_script( 'fusion-menu-options', 'fusionBuilderText', fusion_app_textdomain_strings() );
			}

			public function output() {

				$height = isset( $this->field['mode'] ) && ! empty( $this->field['mode'] ) ? $this->field['mode'] : 'height';
				$width  = isset( $this->field['mode'] ) && ! empty( $this->field['mode'] ) ? $this->field['mode'] : 'width';

				$cleanValue = array(
					$height => isset( $this->value['height'] ) ? Fusion_Sanitize::size( $this->value['height'] ) : '',
					$width  => isset( $this->value['width'] ) ? Fusion_Sanitize::size( $this->value['width'] ) : '',
				);

				$style = "";

				foreach ( $cleanValue as $key => $value ) {
					// Output if it's a numeric entry
					if ( isset( $value ) ) {
						$style .= $key . ':' . $value . ';';
					}
				}

				if ( ! empty( $style ) ) {
					if ( ! empty( $this->field['output'] ) && is_array( $this->field['output'] ) ) {
						$keys = implode( ",", $this->field['output'] );
						$this->parent->outputCSS .= $keys . "{" . $style . '}';
					}

					if ( ! empty( $this->field['compiler'] ) && is_array( $this->field['compiler'] ) ) {
						$keys = implode( ",", $this->field['compiler'] );
						$this->parent->compilerCSS .= $keys . "{" . $style . '}';
					}
				}
			} //function
		} //class
	}

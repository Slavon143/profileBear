<?php
/**
 * Dynamic-CSS handler
 *
 * Generated dynamic CSS by parsing the options
 * and using the `output` argument from them.
 *
 * @package Avada
 * @since 6.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handle generating the dynamic CSS.
 *
 * @since 1.0.0
 */
class Fusion_Dynamic_CSS_From_Options {

	/**
	 * An array of all the fields that have an output argument.
	 *
	 * @access private
	 * @since 6.0.0
	 * @var array
	 */
	private $fields = [];

	/**
	 * The CSS array, ready to be used in the dynamic-css filter.
	 *
	 * @access private
	 * @since 6.0.0
	 * @var array
	 */
	private $css_array = [];

	/**
	 * The Fusion_Dynamic_CSS_Helpers object.
	 *
	 * @access private
	 * @since 6.0.0
	 * @var object
	 */
	private $dynamic_css_helpers;

	/**
	 * The option-name.
	 *
	 * @access private
	 * @since 6.0.0
	 * @var string
	 */
	private $option_name = 'fusion_options';

	/**
	 * The value of option_name using get_option().
	 *
	 * @access private
	 * @since 6.0.0
	 * @var array
	 */
	private $option_value = [];

	/**
	 * Builder status
	 *
	 * @access private
	 * @since 6.0.0
	 * @var boolean
	 */
	private $builder_status = false;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 6.0.0
	 */
	public function __construct() {

		// Get the Fusion_Dynamic_CSS_Helpers object.
		$dynamic_css               = Fusion_Dynamic_CSS::get_instance();
		$this->dynamic_css_helpers = $dynamic_css->get_helpers();

		add_filter( 'fusion_dynamic_css_array', [ $this, 'dynamic_css_array_filter_to' ], 1002 );
		add_filter( 'fusion_dynamic_css_array', [ $this, 'dynamic_css_array_filter_po' ], 1003 );

	}

	/**
	 * Adds our TO CSS to the global compiler CSS array.
	 *
	 * @access public
	 * @since 6.0.0
	 * @param array $css The CSS array.
	 * @return array     The original CSS merged with the generated CSS.
	 */
	public function dynamic_css_array_filter_to( $css ) {

		// Parse TO options.
		$this->css_array = [];
		$this->init( 'TO' );

		// Combine CSS.
		return array_replace_recursive( $this->css_array, $css );
	}

	/**
	 * Adds our PO CSS to the global compiler CSS array.
	 *
	 * @access public
	 * @since 6.0.0
	 * @param array $css The CSS array.
	 * @return array     The original CSS merged with the generated CSS.
	 */
	public function dynamic_css_array_filter_po( $css ) {

		// Parse PO options.
		$this->css_array = [];
		if ( ! is_404() && ! is_search() ) { // 404 & search pages don't have POs (not real pages)
			$this->init( 'PO' );
		}

		// Return final combined CSS.
		return array_replace_recursive( $css, $this->css_array );
	}

	/**
	 * Init method.
	 *
	 * Loads any other methods we need and adds all actions & filters.
	 *
	 * @access private
	 * @since 6.0.0
	 * @param string $context TO/PO.
	 * @return void
	 */
	private function init( $context = 'TO' ) {

		// Set builder status.
		$this->set_builder_status();

		if ( 'PO' === $context ) {

			// Get options.
			if ( ! class_exists( 'PyreThemeFrameworkMetaboxes' ) ) {
				include_once Avada::$template_dir_path . '/includes/metaboxes/metaboxes.php';
			}

			// Parse sections.
			$page_options = PyreThemeFrameworkMetaboxes::$instance;
			if ( ! $page_options ) {
				$page_options = new PyreThemeFrameworkMetaboxes();
			}
			$this->parse_sections( 'PO', $page_options->get_options() );

			// Generate CSS from fields.
			$this->generate_css_from_fields( 'PO' );

			// Early exit.
			return;
		}
		// Get the option-name.
		$this->option_name = Fusion_Settings::get_option_name();

		// Get the option-value.
		$this->option_value = get_option( $this->option_name, [] );

		// Parse sections.
		$this->parse_sections( 'TO', $this->get_options( 'Avada' ) );
		$this->parse_sections( 'TO', $this->get_options( 'FB' ) );

		// Generate CSS from fields.
		$this->generate_css_from_fields( 'TO' );
	}

	/**
	 * Set builder status
	 *
	 * @access private
	 * @since 6.0.0
	 * @return void
	 */
	private function set_builder_status() {
		$builder_status = false;
		if ( function_exists( 'fusion_is_preview_frame' ) ) {
			$builder_status = fusion_is_preview_frame();
		}
		$this->builder_status = $builder_status;
	}

	/**
	 * Get options.
	 *
	 * @access private
	 * @since 6.0.0
	 * @param string $context Avada|FB.
	 * @return array          The array of options.
	 */
	private function get_options( $context = 'Avada' ) {

		// Get Avada-Builder options if $context is set to FB.
		if ( 'FB' === $context ) {
			$fusion_builder_options = [];
			if ( defined( 'FUSION_BUILDER_PLUGIN_DIR' ) ) {
				if ( ! class_exists( 'Fusion_Builder_Options' ) ) {
					require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-builder-options.php';
				}
				$fusion_builder_options = (array) Fusion_Builder_Options::get_instance();
			}

			if ( ! isset( $fusion_builder_options['sections'] ) ) {
				$fusion_builder_options['sections'] = [];
			}
			return $fusion_builder_options['sections'];
		}

		$avada_options = [];
		if ( class_exists( 'Avada_Options' ) ) {
			$avada_options = (array) Avada_Options::get_instance();
		}
		if ( ! isset( $avada_options['sections'] ) ) {
			$avada_options['sections'] = [];
		}
		return $avada_options['sections'];
	}

	/**
	 * Parse sections.
	 *
	 * @access private
	 * @since 6.0.0
	 * @param string $context TO/PO.
	 * @param array  $options The options we're going to parse.
	 * @return void
	 */
	private function parse_sections( $context, $options = [] ) {
		if ( empty( $options ) ) {
			return;
		}
		foreach ( $options as $section ) {
			// Continue if section is hidden.
			if ( isset( $section['hidden'] ) && true === $section['hidden'] ) {
				continue;
			}
			if ( isset( $section['fields'] ) ) {
				foreach ( $section['fields'] as $field ) {
					if ( isset( $field['type'] ) ) {
						if ( 'sub-section' === $field['type'] || 'accordion' === $field['type'] ) {
							if ( isset( $field['fields'] ) && is_array( $field['fields'] ) ) {
								foreach ( $field['fields'] as $subfield ) {
									if ( isset( $subfield['hidden'] ) && true === $subfield['hidden'] ) {
										continue;
									}
									$this->parse_field( $subfield, $context );
								}
							}
						} else {
							if ( isset( $field['hidden'] ) && true === $field['hidden'] ) {
								continue;
							}
							$this->parse_field( $field, $context );
						}
					}
				}
			}
		}
	}

	/**
	 * Process field.
	 *
	 * Populates the $this->fields array.
	 *
	 * @access private
	 * @since 6.0.0
	 * @param array  $field   The field.
	 * @param string $context TO/PO.
	 * @return void
	 */
	private function parse_field( $field, $context ) {

		// No need to proceed if field has no ID or no output defined.
		if ( ! isset( $field['id'] ) || ( ! isset( $field['output'] ) && ! isset( $field['css_vars'] ) ) ) {
			return;
		}

		// Add the field to the $this->fields array.
		$this->merge_field( $field );
	}

	/**
	 * Make sure the field is properly merged and not just replaced.
	 *
	 * @access private
	 * @since 6.0
	 * @param array $field The field.
	 * @return void        Adds/modifies the field to $this->fields.
	 */
	private function merge_field( $field ) {
		// If the field doesn't already exist, this is a simple matter.
		if ( ! isset( $this->fields[ $field['id'] ] ) ) {
			$this->fields[ $field['id'] ] = $field;
			return;
		}

		// If output & css_vars are not defined then we don't need to do anything.
		if ( ( ! isset( $field['output'] ) || empty( $field['output'] ) ) && ( ! isset( $field['css_vars'] ) || empty( $field['css_vars'] ) ) ) {
			return;
		}

		if ( isset( $field['output'] ) && is_array( $field['output'] ) && ! empty( $field['output'] ) ) {

			// If the field that already exists in $this->fields doesn't have an output argument,
			// Then replace it with this one.
			if ( ! isset( $this->fields[ $field['id'] ]['output'] ) || empty( $this->fields[ $field['id'] ]['output'] ) ) {
				$this->fields[ $field['id'] ]['output'] = $field['output'];
			} else {

				// If we got this far, both fields have output defined so we need to merge those.
				foreach ( $field['output'] as $output ) {
					if ( ! in_array( $output, $this->fields[ $field['id'] ]['output'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
						$this->fields[ $field['id'] ]['output'][] = $output;
					}
				}
			}
		}

		if ( isset( $field['css_vars'] ) && is_array( $field['css_vars'] ) && ! empty( $field['css_vars'] ) ) {

			// If the field that already exists in $this->fields doesn't have a css_vars argument,
			// Then replace it with this one.
			if ( ! isset( $this->fields[ $field['id'] ]['css_vars'] ) || empty( $this->fields[ $field['id'] ]['css_vars'] ) ) {
				$this->fields[ $field['id'] ]['css_vars'] = $field['css_vars'];
			} else {

				// If we got this far, both fields have css_vars defined so we need to merge those.
				foreach ( $field['css_vars'] as $css_vars ) {
					if ( ! in_array( $css_vars, $this->fields[ $field['id'] ]['css_vars'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
						$this->fields[ $field['id'] ]['css_vars'][] = $css_vars;
					}
				}
			}
		}
	}

	/**
	 * Loops $this->fields to generate the CSS for fields.
	 *
	 * @access private
	 * @since 6.0.0
	 * @param string $context TO/PO.
	 * @return void
	 */
	private function generate_css_from_fields( $context ) {

		foreach ( $this->fields as $field ) {
			$this->generate_css_from_field( $field, $context );
		}
	}

	/**
	 * Generates CSS for a field and pupulates the $css_array.
	 *
	 * @access private
	 * @since 6.0.0
	 * @param array  $field   The field arguments.
	 * @param string $context TO/PO.
	 * @return void
	 */
	private function generate_css_from_field( $field, $context = 'TO' ) {

		$builder_status = $this->builder_status;

		$id = $field['id'];
		// Get the value.
		if ( 'PO' === $context ) {
			$value = fusion_data()->post_meta( Avada()->fusion_library->get_page_id() )->get( $id );

			// If no PO exists fallback to TO.
			if ( '' === $value || 'default' === $value || null === $value ) {
				$context = 'TO';
			}
		}
		if ( 'TO' === $context ) {
			$value = ( isset( $this->option_value[ $id ] ) ) ? $this->option_value[ $id ] : null;
			if ( is_null( $value ) ) {
				$value = Avada()->settings->get_default( $id, false );
			}
			if ( isset( $field['type'] ) && in_array( $field['type'], [ 'color', 'color-alpha' ], true ) && empty( $value ) ) {
				$value = Avada()->settings->get_default( $id, false );
			}
		}

		$value = apply_filters( "generate_css_get_{$id}", $value );

		// No reason to proceed if value is not set.
		if ( is_null( $value ) ) {
			return;
		}

		// No reason to proceed if $field['output'] is not an array or if $field['css_vars'] is not properly defined.
		if (
			( ! isset( $field['output'] ) || ! is_array( $field['output'] ) ) &&
			( ! isset( $field['css_vars'] ) || ! is_array( $field['css_vars'] ) )
		) {
			return;
		}

		// Process the 'css_vars' argument.
		if ( isset( $field['css_vars'] ) && is_array( $field['css_vars'] ) ) {

			$original_value = fusion_get_option( $id );
			foreach ( $field['css_vars'] as $css_var ) {
				$value = $original_value;
				if ( isset( $css_var['choice'] ) ) {
					if ( is_array( $value ) && isset( $value[ $css_var['choice'] ] ) ) {

						// If we want a weight or style from a var family.
						if ( ( 'font-weight' === $css_var['choice'] || 'font-style' === $css_var['choice'] ) && isset( $value['font-family'] ) && AWB_Global_Typography()->is_typography_css_var( $value['font-family'] ) ) {
							$value = AWB_Global_Typography()->get_var_string( $value['font-family'], $css_var['choice'] );
						} else {
							$value = $value[ $css_var['choice'] ];

							// PO set not to be used.
							if ( isset( $css_var['po'] ) && false === $css_var['po'] ) {
								$value_combo = Avada()->settings->get( $id, $css_var['choice'] );
							} else {
								$value_combo = fusion_get_option( $id . '[' . $css_var['choice'] . ']' );
							}

							if ( 0 === $value_combo || '0' === $value_combo || ( $value_combo && ! empty( $value_combo ) ) ) {
								$value = $value_combo;
							}
						}
					} elseif ( ! is_string( $value ) ) {
						$value = Avada()->settings->get( $id, $css_var['choice'] );
					}
				} elseif ( isset( $css_var['po'] ) && false === $css_var['po'] ) {
					$value = Avada()->settings->get( $id );
				}

				if ( isset( $css_var['exclude'] ) ) {
					$css_var['exclude'] = (array) $css_var['exclude'];
					foreach ( $css_var['exclude'] as $exclusion ) {
						if ( $value === $exclusion ) {
							$value = '';
						}
					}
				}

				// Pattern.
				if ( ! isset( $css_var['value_pattern'] ) ) {
					$css_var['value_pattern'] = '$';
				}

				if ( is_string( $value ) ) {
					$value = str_replace( '$', $value, $css_var['value_pattern'] );
				}

				// Apply the sanitization callback if one is defined.
				if ( isset( $css_var['callback'] ) ) {
					$css_var['callback'] = (array) $css_var['callback'];
					if ( ! isset( $css_var['callback'][1] ) ) {
						$css_var['callback'][1] = '';
					}
					if ( is_callable( 'Fusion_Panel_Callbacks::' . $css_var['callback'][0] ) ) {
						$value = call_user_func_array( 'Fusion_Panel_Callbacks::' . $css_var['callback'][0], [ $value, $css_var['callback'][1] ] );
					}
				}

				Fusion_Dynamic_CSS::add_css_var(
					[
						'name'    => $css_var['name'],
						'value'   => $value,
						'element' => isset( $css_var['element'] ) ? $css_var['element'] : ':root',
					]
				);
			}
		}

		// No reason to proceed if $field['output'] is not an array.
		if ( isset( $field['output'] ) && is_array( $field['output'] ) ) {

			if ( 'media' === $field['type'] && is_array( $value ) ) {
				$value = ( isset( $value['url'] ) ) ? $value['url'] : '';
			}

			// Apply filters.
			$field['output'] = apply_filters( "fusion_options_{$id}_output", $field['output'] );

			// Loop outputs to generate the CSS for each output individually.
			foreach ( $field['output'] as $output ) {

				// No reason to proceed if empty or element is not defined.
				if ( empty( $output ) || ! isset( $output['element'] ) ) {
					continue;
				}

				// Make sure we have the right media-query.
				$output['media_query'] = ( ! isset( $output['media_query'] ) ) ? 'global' : $output['media_query'];

				// Some helpers if builder is active.
				if ( $builder_status ) {
					$elements = $output['element'];

					// If string but multiple selectors we need as array.
					if ( is_string( $elements ) && false !== strpos( $elements, ',' ) ) {
						$elements = explode( ',', $elements );
					}

					if ( ! is_array( $elements ) && false !== strpos( $elements, ':hover' ) ) {
						$fake_hover        = str_replace( ':hover', '.hover', $elements ) . ',';
						$output['element'] = $fake_hover . $elements;
					} elseif ( is_array( $elements ) ) {
						foreach ( $elements as $element ) {
							if ( false !== strpos( $element, ':hover' ) ) {
								$fake_hover          = str_replace( ':hover', '.hover', $element );
								$output['element'][] = $fake_hover;
							}
						}
					}
				}

				$output['element'] = Fusion_Dynamic_CSS_Helpers::get_elements_string( $output['element'] );

				// If we have an exclude argument defined and the value is identical, then skip this.
				if ( isset( $output['exclude'] ) ) {
					$skip = false;
					if ( $value === $output['exclude'] ) {
						$skip = true;
					}
					if ( ! $skip && is_array( $output['exclude'] ) ) {
						foreach ( $output['exclude'] as $exclusion ) {
							if ( $value === $exclusion ) {
								$skip = true;
							}
						}
					}
					if ( $skip ) {
						continue;
					}
				}

				// If value is not an array then this is pretty straight-forward.
				if ( ! is_array( $value ) ) {
					if ( ! isset( $output['property'] ) ) {
						continue;
					}

					$value_complete = $this->calculate_value( $field, $value, $output );
					if ( false === $value_complete || '' === $value_complete || null === $value_complete ) {
						continue;
					}

					// Add the CSS to the var.
					if ( 'background-image' === $output['property'] ) {
						if ( 'url(  )' === $value_complete || 'url( )' === $value_complete || 'url()' === $value_complete ) {
							continue;
						}
						if ( ! isset( $this->css_array[ $output['media_query'] ][ $output['element'] ][ $output['property'] ] ) ) {
							$this->css_array[ $output['media_query'] ][ $output['element'] ][ $output['property'] ] = [];
						}
						$this->css_array[ $output['media_query'] ][ $output['element'] ][ $output['property'] ]   = (array) $this->css_array[ $output['media_query'] ][ $output['element'] ][ $output['property'] ];
						$this->css_array[ $output['media_query'] ][ $output['element'] ][ $output['property'] ][] = $value_complete;
					} else {
						$this->css_array[ $output['media_query'] ][ $output['element'] ][ $output['property'] ] = $value_complete;
					}
					continue;
				}

				// If we got this far, the value is an array so we need to go through the array
				// and figure out what to do with each sub-value.
				$value_keys = array_keys( $value );
				foreach ( $value_keys as $key ) {

					// Get the sub-value.
					$sub_value = Avada()->settings->get( $id, $key );

					// If no sub value, then use the default.
					if ( false === $sub_value || '' === $sub_value || null === $sub_value ) {
						$sub_value = Avada()->settings->get_default( $id, $key );
					}

					// If 'choice' is defined, only process this specific subvalue.
					if ( isset( $output['choice'] ) && ! empty( $output['choice'] ) && $output['choice'] !== $key ) {
						continue;
					}

					// If property is not defined, use $key as the property.
					$property = ( ! isset( $output['property'] ) ) ? $key : $output['property'];

					// Make sure padding-top, margin-left etc work properly.
					if ( in_array( $property, [ 'margin', 'padding' ], true ) && in_array( $key, [ 'top', 'bottom', 'left', 'right' ], true ) ) {
						$property .= '-' . $key;
					}

					$value_complete = $this->calculate_value( $field, $sub_value, $output );

					if ( empty( $value_complete ) ) {
						continue;
					}

					// Add the CSS to the var.
					if ( 'background-image' === $property ) {
						if ( ! isset( $this->css_array[ $output['media_query'] ][ $output['element'] ][ $property ] ) ) {
							$this->css_array[ $output['media_query'] ][ $output['element'] ][ $property ] = [];
						}
						$this->css_array[ $output['media_query'] ][ $output['element'] ][ $property ]   = (array) $this->css_array[ $output['media_query'] ][ $output['element'] ][ $output['property'] ];
						$this->css_array[ $output['media_query'] ][ $output['element'] ][ $property ][] = $value_complete;
					} else {
						$this->css_array[ $output['media_query'] ][ $output['element'] ][ $property ] = $value_complete;
					}
				}
			}
		}
	}

	/**
	 * Calculates the value.
	 *
	 * Adds prefix, suffix, units, applies the value_pattern
	 * and also any sanitization callbacks we may have.
	 *
	 * @access private
	 * @since 6.0.0
	 * @param string $field  The field.
	 * @param string $value  The value.
	 * @param array  $output The output definition.
	 * @return string
	 */
	private function calculate_value( $field, $value, $output ) {

		// Make sure we've got all the arguments.
		$output = wp_parse_args(
			$output,
			[
				'prefix'            => '',
				'suffix'            => '',
				'units'             => '',
				'value_pattern'     => false,
				'sanitize_callback' => false,
				'callback'          => false,
				'function'          => false,
			]
		);

		// Apply the sanitization callback if one is defined.
		if ( $output['callback'] ) {
			$output['callback'] = (array) $output['callback'];
			if ( ! isset( $output['callback'][1] ) ) {
				$output['callback'][1] = '';
			}
			if ( is_callable( 'Fusion_Panel_Callbacks::' . $output['callback'][0] ) ) {
				$value = call_user_func_array( 'Fusion_Panel_Callbacks::' . $output['callback'][0], [ $value, $output['callback'][1] ] );
				if ( empty( $value ) ) {
					return '';
				}
			}
		}
		if ( $output['sanitize_callback'] && is_callable( $output['sanitize_callback'] ) ) {
			$value = call_user_func( $output['sanitize_callback'], $value );
			if ( false === $value || '' === $value ) {
				return '';
			}
		}

		// Don't generate CSS if we want to change HTML or an attribute.
		if ( $output['function'] && 'attr' === $output['function'] || 'html' === $output['function'] ) {
			return '';
		}

		// Apply the value_pattern.
		if ( false !== $output['value_pattern'] && is_string( $value ) ) {
			$value_pattern_val = str_replace( '$', $value, $output['value_pattern'] );
			$value             = $value_pattern_val;

			// Add any other values from pattern_replace.
			if ( isset( $output['pattern_replace'] ) && is_array( $output['pattern_replace'] ) ) {
				foreach ( $output['pattern_replace'] as $key => $val ) {
					$val = str_replace( $this->option_name, '', $val );
					$val = explode( ']', $val );
					foreach ( $val as $k => $v ) {
						if ( false === $v || '' === $v ) {
							unset( $val[ $k ] );
							continue;
						}
						$val[ $k ] = str_replace( '[', '', $v );
					}
					$val = array_values( $val );
					if ( 1 === count( $val ) ) {
						$value = str_replace( $key, Avada()->settings->get( $val[0] ), $value );
						continue;
					}
					$value = str_replace( $key, Avada()->settings->get( $val[0], $val[1] ), $value );
				}
			}
		}

		// If the property is 'background-image', make sure tha value is properly formatted.
		if ( isset( $output['property'] ) && 'background-image' === $output['property'] ) {
			if ( 'media' === $field['type'] ) {
				if ( is_array( $value ) && isset( $value['url'] ) ) {
					$value = $value['url'];
				}

				$value = trim( $value );
				if ( ! empty( $value ) ) {
					if ( false === strpos( $value, 'url(' ) ) {
						$value = 'url("' . $value . '")';
					}
				}
			}
		}

		// If the property is 'font-family', make sure we add backup fonts as well.
		if ( isset( $output['property'] ) && 'font-family' === $output['property'] ) {
			$value = $this->dynamic_css_helpers->combined_font_family( Avada()->settings->get( $field['id'] ) );
		}

		if ( ! is_string( $value ) || ( '0' !== $value && empty( $value ) ) ) {
			return;
		}

		// Return including prefix, units & suffix.
		return $output['prefix'] . $value . $output['units'] . $output['suffix'];
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FusionReduxFramework_border_radius' ) ) {
	class FusionReduxFramework_border_radius {

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
		}

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
			// Set field values
			$defaults = array(
				'top_left'     => true,
				'top_right'    => true,
				'all'          => false,
				'bottom_right' => true,
				'bottom_left'  => true,
			);

			$this->field = wp_parse_args( $this->field, $defaults );

			// Set default values
			$defaults = array(
				'all'          => '',
				'top_left'     => '',
				'top_right'    => '',
				'bottom_right' => '',
				'bottom_left'  => '',
			);

			$this->value = wp_parse_args( $this->value, $defaults );

			$value = array(
				'all'          => isset( $this->value['all'] ) ? Fusion_Sanitize::size( $this->value['all'] ) : '',
				'top_left'     => isset( $this->value['top_left'] ) ? Fusion_Sanitize::size( $this->value['top_left'] ) : '',
				'top_right'    => isset( $this->value['top_right'] ) ? Fusion_Sanitize::size( $this->value['top_right'] ) : '',
				'bottom_right' => isset( $this->value['bottom_right'] ) ? Fusion_Sanitize::size( $this->value['bottom_right'] ) : '',
				'bottom_left'  => isset( $this->value['bottom_left'] ) ? Fusion_Sanitize::size( $this->value['bottom_left'] ) : '',
			);

			$this->value = $value;

			$defaults = array(
				'top_left'     => '',
				'top_right'    => '',
				'bottom_right' => '',
				'bottom_left'  => '',
			);

			$this->value = wp_parse_args( $this->value, $defaults );

			if ( isset( $this->field['all'] ) && $this->field['all'] == true ) {
				echo '<div class="field-border_radius-input input-prepend"><span class="add-on"><i class="el el-fullscreen icon-large"></i></span><input type="text" class="fusionredux-border_radius-all fusionredux-border_radius-input mini ' . $this->field['class'] . '" placeholder="' . __( 'All', 'Avada' ) . '" rel="' . $this->field['id'] . '-all" name="' . $this->field['name'] . $this->field['name_suffix'] . '[all]" value="' . $this->value['all']. '"></div>';
			}

			if ( $this->field['top_left'] === true ) {
				echo '<input type="hidden" class="fusionredux-border_radius-value" id="' . $this->field['id'] . '-top-left" name="' . $this->field['name'] . $this->field['name_suffix'] . '[top_left]" value="' . $this->value['top_left'] . '">';
			}

			if ( $this->field['top_right'] === true ) {
				echo '<input type="hidden" class="fusionredux-border_radius-value" id="' . $this->field['id'] . '-top-right" name="' . $this->field['name'] . $this->field['name_suffix'] . '[top_right]" value="' . $this->value['top_right'] . '">';
			}

			if ( $this->field['bottom_right'] === true ) {
				echo '<input type="hidden" class="fusionredux-border_radius-value" id="' . $this->field['id'] . '-bottom-right" name="' . $this->field['name'] . $this->field['name_suffix'] . '[bottom_right]" value="' . $this->value['bottom_right'] . '">';
			}

			if ( $this->field['bottom_left'] === true ) {
				echo '<input type="hidden" class="fusionredux-border_radius-value" id="' . $this->field['id'] . '-bottom-left" name="' . $this->field['name'] . $this->field['name_suffix'] . '[bottom_left]" value="' . $this->value['bottom_left'] . '">';
			}

			if ( ! isset( $this->field['all'] ) || $this->field['all'] !== true ) {
				/**
				 * Top
				 * */
				if ( $this->field['top_left'] === true ) {
					echo '<div class="field-border_radius-input input-prepend"><span class="add-on"><i class="el el-arrow-up icon-large"></i></span><input type="text" class="fusionredux-border_radius-top-left fusionredux-border_radius-input mini ' . $this->field['class'] . '" placeholder="' . __( 'Top Left', 'Avada' ) . '" rel="' . $this->field['id'] . '-top-left" value="' . $this->value['top_left'] . '"></div>';
				}

				/**
				 * Right
				 * */
				if ( $this->field['top_right'] === true ) {
					echo '<div class="field-border_radius-input input-prepend"><span class="add-on"><i class="el el-arrow-right icon-large"></i></span><input type="text" class="fusionredux-border_radius-top-right fusionredux-border_radius-input mini ' . $this->field['class'] . '" placeholder="' . __( 'Top Right', 'Avada' ) . '" rel="' . $this->field['id'] . '-top-right" value="' . $this->value['top_right'] . '"></div>';
				}

				/**
				 * Bottom
				 * */
				if ( $this->field['bottom_right'] === true ) {
					echo '<div class="field-border_radius-input input-prepend"><span class="add-on"><i class="el el-arrow-down icon-large"></i></span><input type="text" class="fusionredux-border_radius-bottom-right fusionredux-border_radius-input mini ' . $this->field['class'] . '" placeholder="' . __( 'Bottom Right', 'Avada' ) . '" rel="' . $this->field['id'] . '-bottom-right" value="' . $this->value['bottom_right'] . '"></div>';
				}

				/**
				 * Left
				 * */
				if ( $this->field['bottom_left'] === true ) {
					echo '<div class="field-border_radius-input input-prepend"><span class="add-on"><i class="el el-arrow-left icon-large"></i></span><input type="text" class="fusionredux-border_radius-bottom-left fusionredux-border_radius-input mini ' . $this->field['class'] . '" placeholder="' . __( 'Bottom Left', 'Avada' ) . '" rel="' . $this->field['id'] . '-bottom-left" value="' . $this->value['bottom_left'] . '"></div>';
				}
			}
		}

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since FusionReduxFramework 1.0.0
		 */
		function enqueue() {

            // We can use existing JS.
			wp_enqueue_script(
				'fusion-redux-field-border-radius-js',
				trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/custom-fields/border_radius/field_border_radius.js',
				array( 'jquery', 'fusionredux-js' ),
				time(),
				true
			);
			wp_enqueue_style(
				'fusion-redux-field-borde-radius-css',
				trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/custom-fields/border_radius/field_border_radius.css',
				array(),
				time(),
				'all'
			);
		} //function

		public function output() {

            // WIP.
            return;


			if ( ! isset( $this->field['mode'] ) ) {
				$this->field['mode'] = "padding";
			}

			if ( isset( $this->field['mode'] ) && ! in_array( $this->field['mode'], array(
						'padding',
						'absolute',
						'margin'
					) )
			) {
				$this->field['mode'] = "";
			}

			$mode  = ( $this->field['mode'] != "absolute" ) ? $this->field['mode'] : "";
			$units = isset( $this->value['units'] ) ? $this->value['units'] : "";
			$style = '';

			if ( ! empty( $mode ) && is_array( $this->value ) ) {
				foreach ( $this->value as $key => $value ) {
					if ( $key == "units" ) {
						continue;
					}

					// Strip off any alpha for is_numeric test - kp
					$num_no_alpha = preg_replace('/[^\d.-]/', '', $value);

					// Output if it's a numeric entry
					if ( isset( $value ) && is_numeric( $num_no_alpha ) ) {
						$style .= $key . ':' . $value . ';';
					}

				}
			} else {
				$this->value['top']    = isset( $this->value['top'] ) ? $this->value['top'] : 0;
				$this->value['bottom'] = isset( $this->value['bottom'] ) ? $this->value['bottom'] : 0;
				$this->value['left']   = isset( $this->value['left'] ) ? $this->value['left'] : 0;
				$this->value['right']  = isset( $this->value['right'] ) ? $this->value['right'] : 0;

				$cleanValue = array(
					'top'    => isset( $this->value[ $mode . '-top' ] ) ? filter_var( $this->value[ $mode . '-top' ], FILTER_SANITIZE_NUMBER_INT ) : filter_var( $this->value['top'], FILTER_SANITIZE_NUMBER_INT ),
					'right'  => isset( $this->value[ $mode . '-right' ] ) ? filter_var( $this->value[ $mode . '-right' ], FILTER_SANITIZE_NUMBER_INT ) : filter_var( $this->value['right'], FILTER_SANITIZE_NUMBER_INT ),
					'bottom' => isset( $this->value[ $mode . '-bottom' ] ) ? filter_var( $this->value[ $mode . '-bottom' ], FILTER_SANITIZE_NUMBER_INT ) : filter_var( $this->value['bottom'], FILTER_SANITIZE_NUMBER_INT ),
					'left'   => isset( $this->value[ $mode . '-left' ] ) ? filter_var( $this->value[ $mode . '-left' ], FILTER_SANITIZE_NUMBER_INT ) : filter_var( $this->value['left'], FILTER_SANITIZE_NUMBER_INT )
				);

				if ( isset( $this->field['all'] ) && true == $this->field['all'] ) {
					$style .= $mode . 'top:' . $cleanValue['top'] . $units . ';';
					$style .= $mode . 'bottom:' . $cleanValue['top'] . $units . ';';
					$style .= $mode . 'right:' . $cleanValue['top'] . $units . ';';
					$style .= $mode . 'left:' . $cleanValue['top'] . $units . ';';
				} else {
					if ( true == $this->field['top'] ) {
						$style .= $mode . 'top:' . $cleanValue['top'] . $units . ';';
					}

					if ( true == $this->field['bottom'] ) {
						$style .= $mode . 'bottom:' . $cleanValue['bottom'] . $units . ';';
					}

					if ( true == $this->field['left'] ) {
						$style .= $mode . 'left:' . $cleanValue['left'] . $units . ';';
					}

					if ( true == $this->field['right'] ) {
						$style .= $mode . 'right:' . $cleanValue['right'] . $units . ';';
					}
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
		}
	}
}

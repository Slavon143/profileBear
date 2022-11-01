<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_form' ) ) {

	if ( ! class_exists( 'FusionSC_FusionForm' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_FusionForm extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * An array of rendered form's IDs.
			 *
			 * @access protected
			 * @since 3.7
			 * @var array
			 */
			protected $rendered_forms = [];

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_fusion-form-wrapper', [ $this, 'wrapper_attr' ] );
				add_shortcode( 'fusion_form', [ $this, 'render' ] );
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
					'form_post_id'   => '',
					'margin_bottom'  => '',
					'margin_left'    => '',
					'margin_right'   => '',
					'margin_top'     => '',
					'class'          => '',
					'hide_on_mobile' => fusion_builder_default_visibility( 'string' ),
					'id'             => '',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				// Early return if form_post_id is invalid.
				if ( ! isset( $args['form_post_id'] ) || '' === $args['form_post_id'] ) {

					// Editor user, display message.
					if ( current_user_can( 'publish_posts' ) ) {
						return apply_filters( 'fusion_element_form_content', '<div class="fusion-builder-placeholder">' . esc_html__( 'No form selected. Please select a form to display it here.', 'fusion-builder' ) . '</div>', $args );
					}

					// Non editor, display nothing.
					return apply_filters( 'fusion_element_form_content', '', $args );
				}

				// Set data.
				$this->params = Fusion_Builder_Form_Helper::fusion_form_set_form_data( $args['form_post_id'] );
				$this->args   = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_form' );
				$form_data    = Fusion_Builder_Form_Helper::fusion_get_form_post_content( $this->args['form_post_id'] );

				// No form found.
				if ( false === $form_data ) {

					// Editor user, display message.
					if ( current_user_can( 'publish_posts' ) ) {
						return apply_filters(
							'fusion_element_form_content',
							'<div class="fusion-builder-placeholder">' . esc_html__( 'This form no longer exists. It has been deleted or moved. Please create a new form to assign here.', 'fusion-builder' ) . '</div>',
							$this->args
						);
					}

					// Non editor, display nothing.
					return apply_filters( 'fusion_element_form_content', '', $args );
				}

				$this->params['is_upload'] = false !== strpos( $form_data['content'], 'fusion_form_upload' );

				// Make forms translateable.
				$this->args['form_post_id'] = apply_filters( 'wpml_object_id', $this->args['form_post_id'], 'fusion_form', true );

				$this->args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_bottom'], 'px' );
				$this->args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_left'], 'px' );
				$this->args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_right'], 'px' );
				$this->args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_top'], 'px' );

				// We have a valid form.
				$content                  = isset( $args['use_content'] ) ? $content : $form_data['content'];
				$this->args['custom_css'] = $form_data['css'];

				// Member only checks.
				if ( 'yes' === $this->params['form_meta']['member_only_form'] ) {
					if ( ! is_user_logged_in() ) {
						return apply_filters( 'fusion_element_form_content', '', $args );
					}

					$user_roles = [];
					if ( $this->params['form_meta']['user_roles'] ) {
						$user_roles = is_array( $this->params['form_meta']['user_roles'] )
							? $this->params['form_meta']['user_roles']
							: explode( ',', $this->params['form_meta']['user_roles'] );
					}
					if ( ! Fusion_Builder_Form_Helper::user_can_see_fusion_form( $user_roles ) ) {
						return apply_filters( 'fusion_element_form_content', '', $args );
					}
				}

				// Add Off Canvas to stack, so it's markup is added to the page.
				if ( class_exists( 'AWB_Off_Canvas' ) && false !== AWB_Off_Canvas::is_enabled() && in_array( 'off-canvas', $this->params['form_meta']['form_actions'], true ) && $this->params['form_meta']['off_canvas'] ) {
					AWB_Off_Canvas_Front_End::add_off_canvas_to_stack( $this->params['form_meta']['off_canvas'] );
				}

				// Build the form markup.
				$html = $this->create_style_tag();

				$form  = $this->open_form();
				$form .= do_shortcode( $content );
				$form .= '<input type="hidden" name="fusion_privacy_store_ip_ua" value="' . ( 'yes' === $this->params['form_meta']['privacy_store_ip_ua'] ? 'true' : 'false' ) . '">';
				$form .= '<input type="hidden" name="fusion_privacy_expiration_interval" value="' . absint( $this->params['form_meta']['privacy_expiration_interval'] ) . '">';
				$form .= '<input type="hidden" name="privacy_expiration_action" value="' . esc_attr( $this->params['form_meta']['privacy_expiration_action'] ) . '">';

				if ( isset( $this->params['form_meta']['nonce_method'] ) && 'localized' === $this->params['form_meta']['nonce_method'] ) {
					$form .= wp_nonce_field( 'fusion_form_nonce', 'fusion-form-nonce-' . absint( $this->args['form_post_id'] ), false, false );
				}

				$form .= $this->close_form();

				$html .= '<div ' . FusionBuilder::attributes( 'fusion-form-wrapper' ) . '>';
				$html .= $form;
				$html .= '</div>';

				$this->on_render();

				return apply_filters( 'fusion_element_form_content', $html, $args );
			}

			/**
			 * Fires on render.
			 *
			 * @access protected
			 * @since 3.2
			 */
			protected function on_render() {
				if ( ! $this->has_rendered ) {
					$this->on_first_render();
					$this->has_rendered = true;
				}

				if ( ! in_array( (int) $this->args['form_post_id'], $this->rendered_forms, true ) && isset( $this->params['form_meta']['nonce_method'] ) && ( 'none' === $this->params['form_meta']['nonce_method'] || 'localized' === $this->params['form_meta']['nonce_method'] ) ) {
					Fusion_Form_Builder()->increase_view_count( $this->args['form_post_id'] );
					$this->rendered_forms[] = (int) $this->args['form_post_id'];
				}
			}

			/**
			 * Check if a param is default.
			 *
			 * @access public
			 * @since 3.0
			 * @param string $param Param name.
			 * @param mixed  $subset Subset name.
			 * @return string
			 */
			public function is_default( $param, $subset = false ) {

				// If we have a subset value.
				if ( $subset ) {
					if ( isset( $this->params['form_meta'][ $param ] ) && isset( $this->params['form_meta'][ $param ][ $subset ] ) && '' !== $this->params['form_meta'][ $param ][ $subset ] ) {
						return false;
					} elseif ( ! isset( $this->params['form_meta'][ $param ][ $subset ] ) || '' === $this->params['form_meta'][ $param ][ $subset ] ) {
						return true;
					}
				}

				// No arg, means we are using default.
				if ( ! isset( $this->params['form_meta'][ $param ] ) || '' === $this->params['form_meta'][ $param ] ) {
					return true;
				}

				return false;
			}

			/**
			 * Create styles for form render.
			 *
			 * @access public
			 * @since 3.1
			 * @return string HTML output.
			 */
			public function create_style_tag() {
				$this->base_selector = '.fusion-form-form-wrapper .fusion-form-' . $this->params['form_number'];

				$text_inputs = [
					$this->base_selector . ' input[type="date"]',
					$this->base_selector . ' input[type="datetime-local"]',
					$this->base_selector . ' input[type="email"]',
					$this->base_selector . ' input[type="month"]',
					$this->base_selector . ' input[type="number"]',
					$this->base_selector . ' input[type="password"]',
					$this->base_selector . ' input[type="search"]',
					$this->base_selector . ' input[type="tel"]',
					$this->base_selector . ' input[type="text"]',
					$this->base_selector . ' input[type="time"]',
					$this->base_selector . ' input[type="url"]',
					$this->base_selector . ' input[type="week"]',
					$this->base_selector . ' input[type="datetime"]',
				];

				$upload_input = $this->base_selector . ' input[type="upload"]';
				$range_input  = $this->base_selector . ' input[type="range"]';

				$select_input   = $this->base_selector . ' select';
				$textarea_input = $this->base_selector . ' textarea';

				// Input types that are not target: checkbox, radio, range.
				$customizable_inputs = array_merge( $text_inputs, [ $select_input ], [ $textarea_input ], [ $upload_input ] );

				// Help tooltips.
				$this->add_css_property( $this->base_selector . ' .fusion-form-tooltip .fusion-form-tooltip-content', 'color', $this->params['form_meta']['tooltip_text_color'], true );
				$this->add_css_property( $this->base_selector . ' .fusion-form-tooltip .fusion-form-tooltip-content', 'background-color', $this->params['form_meta']['tooltip_background_color'], true );
				$this->add_css_property( $this->base_selector . ' .fusion-form-tooltip .fusion-form-tooltip-content', 'border-color', $this->params['form_meta']['tooltip_background_color'], true );

				// Field margin.
				if ( '' !== $this->params['form_meta']['field_margin']['top'] ) {
					$this->add_css_property( $this->base_selector . ' .fusion-form-field', 'margin-top', $this->params['form_meta']['field_margin']['top'] );
				}
				if ( '' !== $this->params['form_meta']['field_margin']['bottom'] ) {
					$this->add_css_property( $this->base_selector . ' .fusion-form-field', 'margin-bottom', $this->params['form_meta']['field_margin']['bottom'] );
				}

				// Field height.
				if ( ! $this->is_default( 'form_input_height' ) ) {
					$height_inputs = array_merge( $text_inputs, [ $select_input ], [ $range_input ] );
					$this->add_css_property( $height_inputs, 'height', $this->params['form_meta']['form_input_height'] );
					$this->add_css_property( $this->base_selector . ' .fusion-form-input-with-icon > i', 'line-height', $this->params['form_meta']['form_input_height'] );
				}

				// Background color.
				if ( ! $this->is_default( 'form_bg_color' ) ) {
					$this->add_css_property( $customizable_inputs, 'background-color', $this->params['form_meta']['form_bg_color'] );
				}

				// Font Size.
				if ( ! $this->is_default( 'form_font_size' ) ) {
					$this->add_css_property( $customizable_inputs, 'font-size', $this->params['form_meta']['form_font_size'] );
					$this->add_css_property( $this->base_selector . ' .fusion-form-field .fusion-form-input-with-icon>i', 'font-size', $this->params['form_meta']['form_font_size'] );
				}

				// Placeholders.
				$placeholders_color = '';
				if ( '' !== $this->params['form_meta']['form_placeholder_color'] ) {
					$placeholders_color = $this->params['form_meta']['form_placeholder_color'];
				} elseif ( ! $this->is_default( 'form_text_color' ) ) {
					$placeholders_color = Fusion_Color::new_color( $this->params['form_meta']['form_text_color'] )->get_new( 'alpha', '0.5' )->to_css_var_or_rgba();
				}

				if ( $placeholders_color ) {

					// Regular browser placeholders.
					$selectors = [
						$this->base_selector . ' input::placeholder',
						$this->base_selector . ' textarea::placeholder',
						$this->base_selector . ' select:invalid',
					];
					$this->add_css_property( $selectors, 'color', $placeholders_color );
				}

				// Text color.
				if ( ! $this->is_default( 'form_text_color' ) ) {

					// Select field.
					$this->add_css_property( $this->base_selector . ' option', 'color', $this->params['form_meta']['form_text_color'] );

					// Icon color.
					$this->add_css_property( $this->base_selector . ' .fusion-form-input-with-icon > i', 'color', $this->params['form_meta']['form_text_color'], true );

					// Upload field.
					$this->add_css_property( $this->base_selector . ' input.fusion-form-upload-field::placeholder', 'color', $this->params['form_meta']['form_text_color'] );

					// Input text color.
					$this->add_css_property( $customizable_inputs, 'color', $this->params['form_meta']['form_text_color'] );

					// Select stroke color.
					$this->add_css_property( $this->base_selector . ' .fusion-select-wrapper .select-arrow path', 'stroke', $this->params['form_meta']['form_text_color'], true );
				}

				// Label color.
				if ( ! $this->is_default( 'form_label_color' ) ) {
					$this->add_css_property( $this->base_selector . ' label, ' . $this->base_selector . ' .label,' . $this->base_selector . ' .fusion-form-tooltip > i', 'color', $this->params['form_meta']['form_label_color'] );
				}

				// Border size.
				if ( ! $this->is_default( 'form_border_width', 'top' ) ) {
					$this->add_css_property( $customizable_inputs, 'border-top-width', FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_width']['top'], 'px' ) );
					$this->add_css_property( $this->base_selector . ' .fusion-form-field .fusion-form-image-select label', 'border-top-width', FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_width']['top'], 'px' ) );
				}
				if ( ! $this->is_default( 'form_border_width', 'bottom' ) ) {
					$this->add_css_property( $customizable_inputs, 'border-bottom-width', FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_width']['bottom'], 'px' ) );
					$this->add_css_property( $this->base_selector . ' .fusion-form-field .fusion-form-image-select label', 'border-bottom-width', FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_width']['bottom'], 'px' ) );
				}
				if ( ! $this->is_default( 'form_border_width', 'right' ) ) {
					$this->add_css_property( $customizable_inputs, 'border-right-width', FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_width']['right'], 'px' ) );
					$this->add_css_property( $this->base_selector . ' .fusion-form-field .fusion-form-image-select label', 'border-right-width', FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_width']['right'], 'px' ) );

					if ( is_rtl() ) {
						$this->add_css_property( $this->base_selector . ' .fusion-form-field .fusion-form-input-with-icon > i', 'right', 'calc( 1em + ' . FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_width']['right'], 'px' ) . ')', true );
					} else {
						$this->add_css_property( $this->base_selector . ' .fusion-select-wrapper .select-arrow', 'right', 'calc( 1em + ' . FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_width']['right'], 'px' ) . ')', true );
					}
				}
				if ( ! $this->is_default( 'form_border_width', 'left' ) ) {
					$this->add_css_property( $customizable_inputs, 'border-left-width', FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_width']['left'], 'px' ) );
					$this->add_css_property( $this->base_selector . ' .fusion-form-field .fusion-form-image-select label', 'border-left-width', FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_width']['left'], 'px' ) );

					if ( is_rtl() ) {
						$this->add_css_property( $this->base_selector . ' .fusion-select-wrapper .select-arrow', 'left', 'calc( 1em + ' . FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_width']['left'], 'px' ) . ')', true );
					} else {
						$this->add_css_property( $this->base_selector . ' .fusion-form-field .fusion-form-input-with-icon > i', 'left', 'calc( 1em + ' . FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_width']['left'], 'px' ) . ')', true );
					}
				}

				// Vertical icon align.
				if ( ! $this->is_default( 'form_border_width', 'bottom' ) || ! $this->is_default( 'form_border_width', 'top' ) ) {
					$fusion_settings = awb_get_fusion_settings();
					$border_top      = $this->is_default( 'form_border_width', 'top' ) ? $fusion_settings->get( 'form_border_width', 'top' ) : FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_width']['top'], 'px' );
					$border_bottom   = $this->is_default( 'form_border_width', 'bottom' ) ? $fusion_settings->get( 'form_border_width', 'bottom' ) : FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_width']['bottom'], 'px' );
					$border_top      = empty( $border_top ) ? '1px' : $border_top;
					$border_bottom   = empty( $border_bottom ) ? '1px' : $border_bottom;
					$font_size       = $this->is_default( 'form_font_size' ) ? '1em' : $this->params['form_meta']['form_font_size'];
					$this->add_css_property( $this->base_selector . ' .fusion-form-field:not( .fusion-form-upload-field ) .fusion-form-input-with-icon > i', 'top', 'calc( 50% + (' . $border_top . ' - ' . $border_bottom . ' ) / 2 )', true );
					$this->add_css_property( $this->base_selector . ' .fusion-form-field.fusion-form-textarea-field .fusion-form-input-with-icon > i', 'top', 'calc( ' . $border_top . ' + ' . $font_size . ' )', true );
				}

				// Border color.
				if ( ! $this->is_default( 'form_border_color' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-form-field .fusion-form-checkbox label:before',
						$this->base_selector . ' .fusion-form-field .fusion-form-radio label:before',
						$this->base_selector . ' .fusion-form-field .fusion-form-image-select label',
					];

					$this->add_css_property( $customizable_inputs, 'border-color', $this->params['form_meta']['form_border_color'] );
					$this->add_css_property( $selectors, 'border-color', $this->params['form_meta']['form_border_color'] );

					$selectors = [
						$this->base_selector . ' .fusion-form-field .fusion-form-rating-area .fusion-form-rating-icon',
					];
					$this->add_css_property( $selectors, 'color', $this->params['form_meta']['form_border_color'] );

					// Range input type.
					$this->add_css_property( $this->base_selector . ' .fusion-form-field input[type=range]::-webkit-slider-runnable-track', 'background', $this->params['form_meta']['form_border_color'] );
					$this->add_css_property( $this->base_selector . ' .fusion-form-field input[type=range]::-moz-range-track', 'background', $this->params['form_meta']['form_border_color'] );
				}

				// Border focus color.
				if ( ! $this->is_default( 'form_focus_border_color' ) ) {
					$hover_color = Fusion_Color::new_color( $this->params['form_meta']['form_focus_border_color'] )->get_new( 'alpha', '0.5' )->to_css_var_or_rgba();

					$selectors = [
						$this->base_selector . ' input:not([type="submit"]):focus',
						$this->base_selector . ' select:focus',
						$this->base_selector . ' textarea:focus',
						$this->base_selector . ' .fusion-form-field.focused.fusion-form-upload-field .fusion-form-upload-field',
						$this->base_selector . ' .fusion-form-field .fusion-form-radio input:checked + label:before',
						$this->base_selector . ' .fusion-form-field .fusion-form-radio input:hover + label:before',
						$this->base_selector . ' .fusion-form-field .fusion-form-checkbox input:checked + label:before',
						$this->base_selector . ' .fusion-form-field .fusion-form-checkbox input:hover + label:before',
						$this->base_selector . ' .fusion-form-field .fusion-form-image-select .fusion-form-input:checked + label',
						$this->base_selector . ' .fusion-form-field .fusion-form-image-select .fusion-form-input:hover + label',
						$this->base_selector . ' .fusion-form-field .fusion-form-checkbox input:focus + label:before',
						$this->base_selector . ' .fusion-form-field .fusion-form-radio input:focus + label:before',
						$this->base_selector . ' .fusion-form-field .fusion-form-image-select .fusion-form-input:focus + label',
					];
					$this->add_css_property( $selectors, 'border-color', $this->params['form_meta']['form_focus_border_color'] );

					$selectors = [
						$this->base_selector . ' .fusion-form-field .fusion-form-radio input:hover:not(:checked) + label:before',
						$this->base_selector . ' .fusion-form-field .fusion-form-checkbox input:hover:not(:checked) + label:before',
						$this->base_selector . ' .fusion-form-field .fusion-form-image-select .fusion-form-input:hover:not(:checked) + label',
						$this->base_selector . ' .fusion-form-field .fusion-form-upload-field-container:hover .fusion-form-upload-field',
						$this->base_selector . ' .fusion-form-field .fusion-form-range-field-container .fusion-form-range-value:hover:not(:focus)',
						$this->base_selector . ' .fusion-form-field .fusion-form-input:hover:not(:focus)',
					];

					$this->add_css_property( $selectors, 'border-color', $hover_color );

					$selectors = [
						$this->base_selector . ' .fusion-form-field .fusion-form-rating-area .fusion-form-input:checked ~ label i',
					];
					$this->add_css_property( $selectors, 'color', $this->params['form_meta']['form_focus_border_color'] );

					$selectors = [
						$this->base_selector . ' .fusion-form-field .fusion-form-rating-area .fusion-form-input:checked:hover ~ label i',
						$this->base_selector . ' .fusion-form-field .fusion-form-rating-area .fusion-form-rating-icon:hover i',
						$this->base_selector . ' .fusion-form-field .fusion-form-rating-area .fusion-form-rating-icon:hover ~ label i',
						$this->base_selector . ' .fusion-form-field .fusion-form-rating-area .fusion-form-input:hover ~ label i',
					];

					$this->add_css_property( $selectors, 'color', $hover_color );

					$selectors = [
						$this->base_selector . ' .fusion-form-field .fusion-form-checkbox input:checked + label:after',
						$this->base_selector . ' .fusion-form-field .fusion-form-radio input:checked + label:after',
					];
					$this->add_css_property( $selectors, 'background', $this->params['form_meta']['form_focus_border_color'] );

					// Range input.
					$this->add_css_property( $this->base_selector . ' .fusion-form-field input[type=range]::-webkit-slider-thumb', 'background', $this->params['form_meta']['form_focus_border_color'] );
					$this->add_css_property( $this->base_selector . ' .fusion-form-field input[type=range]::-moz-range-thumb', 'background', $this->params['form_meta']['form_focus_border_color'] );
				}

				// Border radius.
				if ( ! $this->is_default( 'form_border_radius' ) ) {
					$this->add_css_property( $customizable_inputs, 'border-radius', FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_radius'], 'px' ) );
					$this->add_css_property( $this->base_selector . ' .fusion-form-field .fusion-form-image-select label', 'border-radius', FusionBuilder::validate_shortcode_attr_value( $this->params['form_meta']['form_border_radius'], 'px' ) );
				}

				$css = $this->parse_css() . $this->args['custom_css'];

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Renders the opening form tag.
			 *
			 * @since 3.1
			 * @access private
			 * @return string The form tag.
			 */
			private function open_form() {
				global $fusion_form;

				$data_attributes = '';
				$id              = '';
				$html            = '';
				$enctype         = '';
				$class           = 'fusion-form';

				if ( 'url' === $this->params['form_meta']['form_type'] ) {
					$class .= ' fusion-form-post';
				}

				if ( ! empty( $this->params['data_attributes'] ) ) {
					foreach ( $this->params['data_attributes'] as $key => $value ) {
						$data_attributes .= ' data-' . $key . '="' . $value . '"';
					}
				}

				if ( $this->params['is_upload'] ) {
					$enctype = ' enctype="multipart/form-data"';
				}

				$class .= ' fusion-form-' . $this->params['form_number'];

				$action = get_permalink();

				if ( 'post' === $this->params['form_meta']['form_type'] && '' !== $this->params['form_meta']['post_method_url'] ) {
					$action = $this->params['form_meta']['post_method_url'];
				}
				$html .= '<form action="' . $action . '" method="' . $this->params['form_meta']['method'] . '"' . $data_attributes . ' class="' . $class . '"' . $id . $enctype . '>';

				/**
				 * The fusion_form_after_open hook.
				 */
				ob_start();
				do_action( 'fusion_form_after_open', $this->args, $this->params );
				$html .= ob_get_clean();

				return $html;

			}

			/**
			 * Closes the form and adds an action.
			 *
			 * @since 3.1
			 * @access public
			 * @return string Form closing plus action output.
			 */
			private function close_form() {

				/**
				 * The fusion_form_before_close hook.
				 */
				ob_start();
				do_action( 'fusion_form_before_close', $this->args, $this->params );
				$html = ob_get_clean();

				$html .= '</form>';

				return $html;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function wrapper_attr() {

				$attr = [
					'class' => 'fusion-form fusion-form-builder fusion-form-form-wrapper fusion-form-' . $this->args['form_post_id'],
					'style' => '',
				];

				$attr['data-form-id'] = $this->args['form_post_id'];

				$attr          = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );
				$attr['style'] = Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				// Studio Preview.
				if ( isset( $this->params['form_meta']['preview_width'] ) && isset( $_GET['awb-studio-form'] ) && ! isset( $_GET['fb-edit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$attr['style'] .= 'width: ' . $this->params['form_meta']['preview_width'] . '%;';
				}

				$attr['data-config'] = $this->localize_form_data();

				// Add Off Canvas data attr.
				if ( class_exists( 'AWB_Off_Canvas' ) && false !== AWB_Off_Canvas::is_enabled() && in_array( 'off-canvas', $this->params['form_meta']['form_actions'], true ) && $this->params['form_meta']['off_canvas'] ) {
					AWB_Off_Canvas_Front_End::add_off_canvas_to_stack( $this->params['form_meta']['off_canvas'] );
					$attr['data-off-canvas'] = $this->params['form_meta']['off_canvas'];
				}

				return $attr;
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {
				Fusion_Dynamic_JS::enqueue_script(
					'fusion-form-js',
					FusionBuilder::$js_folder_url . '/general/fusion-form.js',
					FusionBuilder::$js_folder_path . '/general/fusion-form.js',
					[ 'jquery' ],
					FUSION_BUILDER_VERSION,
					true
				);

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-form-logics',
					FusionBuilder::$js_folder_url . '/general/fusion-form-logics.js',
					FusionBuilder::$js_folder_path . '/general/fusion-form-logics.js',
					[ 'jquery', 'fusion-form-js' ],
					FUSION_BUILDER_VERSION,
					true
				);

				Fusion_Dynamic_JS::localize_script(
					'fusion-form-js',
					'formCreatorConfig',
					[
						'ajaxurl'             => admin_url( 'admin-ajax.php' ),
						'invalid_email'       => esc_attr__( 'The supplied email address is invalid.', 'fusion-builder' ),
						'max_value_error'     => esc_attr__( 'Max allowed value is: 2.', 'fusion-builder' ),
						'min_value_error'     => esc_attr__( 'Min allowed value is: 1.', 'fusion-builder' ),
						'max_min_value_error' => esc_attr__( 'Value out of bounds, limits are: 1-2.', 'fusion-builder' ),
						'file_size_error'     => esc_attr__( 'Your file size exceeds max allowed limit of ', 'fusion-builder' ),
						'file_ext_error'      => esc_attr__( 'This file extension is not allowed. Please upload file having these extensions: ', 'fusion-builder' ),
					]
				);
			}

			/**
			 * Localize form data.
			 *
			 * @since 3.1
			 * @access public
			 * @return string
			 */
			private function localize_form_data() {
				global $fusion_form;

				$form_data = [
					'form_id'           => isset( $this->params['form_number'] ) ? $this->params['form_number'] : '',
					'form_post_id'      => isset( $this->args['form_post_id'] ) ? $this->args['form_post_id'] : '',
					'post_id'           => get_the_ID(),
					'form_type'         => isset( $this->params['form_meta']['form_type'] ) ? $this->params['form_meta']['form_type'] : '',
					'confirmation_type' => isset( $this->params['form_meta']['form_confirmation_type'] ) ? $this->params['form_meta']['form_confirmation_type'] : '',
					'redirect_url'      => isset( $this->params['form_meta']['redirect_url'] ) ? $this->params['form_meta']['redirect_url'] : '',
					'field_labels'      => $fusion_form['field_labels'],
					'field_logics'      => $fusion_form['field_logics'],
					'nonce_method'      => isset( $this->params['form_meta']['nonce_method'] ) ? $this->params['form_meta']['nonce_method'] : 'ajax',
				];

				return wp_json_encode( $form_data );
			}
		}
	}

	new FusionSC_FusionForm();
}

/**
 * Map shortcode to Fusion Builder.
 *
 * @since 1.0
 */
function fusion_element_form() {
	$fusion_settings = awb_get_fusion_settings();
	$forms_link      = '<a href="' . esc_url_raw( admin_url( 'admin.php?page=avada-forms' ) ) . '" target="_blank">' . esc_attr__( 'Forms Dashboard', 'fusion-builder' ) . '</a>';

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_FusionForm',
			[
				'name'            => esc_attr__( 'Avada Form', 'fusion-builder' ),
				'shortcode'       => 'fusion_form',
				'icon'            => 'fusiona-avada-form-element',
				'allow_generator' => true,
				'inline_editor'   => true,
				'preview'         => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-form-preview.php',
				'preview_id'      => 'fusion-builder-block-module-form-preview-template',
				'help_url'        => 'https://theme-fusion.com/documentation/avada/elements/avada-form-element/',
				'params'          => [
					[
						'type'        => 'select',
						'heading'     => esc_html__( 'Form', 'fusion-builder' ),
						'description' => sprintf(
							/* translators: link to forms-dashboard */
							esc_html__( 'Select the form from list. NOTE: You can create, edit and find forms on the %s page.', 'fusion-builder' ),
							$forms_link
						),
						'param_name'  => 'form_post_id',
						'value'       => Fusion_Builder_Form_Helper::fusion_form_creator_form_list(),
						'quick_edit'  => [
							'label' => esc_html__( 'Edit Form', 'fusion-builder' ),
							'type'  => 'form',
							'items' => Fusion_Builder_Form_Helper::fusion_form_creator_form_list( 'permalinks' ),
						],
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
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_form' );

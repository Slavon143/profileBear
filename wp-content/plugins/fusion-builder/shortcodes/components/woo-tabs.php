<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_tabs' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Tabs' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.2
		 */
		class FusionTB_Woo_Tabs extends Fusion_Woo_Component {

			/**
			 * An array of the shortcode defaults.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $defaults;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $args;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.2
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.2
			 */
			public function __construct() {
				parent::__construct( 'fusion_tb_woo_tabs' );
				add_filter( 'fusion_attr_fusion_tb_woo_tabs-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_woo_tabs', [ $this, 'ajax_render' ] );
			}

			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 3.2
			 * @return boolean
			 */
			public function should_render() {
				return is_singular();
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					// Element margin.
					'margin_top'                     => '',
					'margin_right'                   => '',
					'margin_bottom'                  => '',
					'margin_left'                    => '',

					// Content padding.
					'content_padding_top'            => '',
					'content_padding_right'          => '',
					'content_padding_bottom'         => '',
					'content_padding_left'           => '',

					// Nav padding.
					'nav_padding_top'                => '',
					'nav_padding_right'              => '',
					'nav_padding_bottom'             => '',
					'nav_padding_left'               => '',

					// Nav text color.
					'active_nav_text_color'          => '',
					'inactive_nav_text_color'        => '',

					'display_tabs'                   => 'description,additional_information,reviews',
					'layout'                         => $fusion_settings->get( 'woocommerce_product_tab_design' ),
					'nav_content_space'              => '',

					// Text styling.
					'text_color'                     => '',
					'fusion_font_family_text_font'   => '',
					'fusion_font_variant_text_font'  => '',
					'text_font_size'                 => '',
					'text_text_transform'            => '',
					'text_line_height'               => '',
					'text_letter_spacing'            => '',

					// Title styling.
					'title_color'                    => '',
					'fusion_font_family_title_font'  => '',
					'fusion_font_variant_title_font' => '',
					'title_font_size'                => '',
					'title_text_transform'           => '',
					'title_line_height'              => '',
					'title_letter_spacing'           => '',

					'stars_color'                    => '',
					'backgroundcolor'                => '',
					'bordercolor'                    => '',
					'inactivebackgroundcolor'        => '',
					'show_tab_titles'                => 'yes',
					'hide_on_mobile'                 => fusion_builder_default_visibility( 'string' ),
					'class'                          => '',
					'id'                             => '',
					'animation_type'                 => '',
					'animation_direction'            => 'down',
					'animation_speed'                => '0.1',
					'animation_offset'               => $fusion_settings->get( 'animation_offset' ),

					'responsive_typography'          => 0.0 < $fusion_settings->get( 'typography_sensitivity' ),
				];
			}

			/**
			 * Render for live editor.
			 *
			 * @static
			 * @access public
			 * @since 3.2
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_render( $defaults ) {
				global $product, $post, $withcomments;
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				$live_request = false;

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults       = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$this->defaults = self::get_element_defaults();
					$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $defaults, 'fusion_tb_woo_tabs' );
					$return_data    = [];
					$live_request   = true;
					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				if ( class_exists( 'Fusion_App' ) && $live_request ) {

					$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : get_the_ID(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					if ( ( ! $post_id || -99 === $post_id ) || ( isset( $_POST['post_id'] ) && 'fusion_tb_section' === get_post_type( $_POST['post_id'] ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						echo wp_json_encode( [] );
						wp_die();
					}

					do_action( 'fusion_pause_live_editor_filter' );

					$this->emulate_product();

					if ( ! $this->is_product() ) {
						echo wp_json_encode( $return_data );
						wp_die();
					}

					// Needed in order to bypass early exit in comments_template function.
					$withcomments = true;

					// We need to set global $post because Woo template expects it.
					$post = get_post( $product->get_id() );

					// Ensure legacy templates are not used.
					if ( function_exists( 'Fusion_Builder_WooCommerce' ) ) {
						Fusion_Builder_WooCommerce()->init_single_product();
					}

					$return_data['woo_tabs'] = $this->get_woo_tabs_content( $defaults, $post_id );
					$this->restore_product();

					do_action( 'fusion_resume_live_editor_filter' );

					// Restore global $post.
					$post = null;
				}

				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.2
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_tabs' );

				if ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) {
					do_action( 'fusion_pause_live_editor_filter' );
				}

				$this->emulate_product();

				if ( ! $this->is_product() ) {
					return;
				}

				$html  = $this->get_styles();
				$html .= '<div ' . FusionBuilder::attributes( 'fusion_tb_woo_tabs-shortcode' ) . '>' . $this->get_woo_tabs_content( $this->args ) . '</div>';

				$this->restore_product();

				if ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) {
					do_action( 'fusion_resume_live_editor_filter' );
				}

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle, $html, $args );
			}

			/**
			 * Builds HTML for Woo Rating element.
			 *
			 * @static
			 * @access public
			 * @since 3.2
			 * @param array $args The arguments.
			 * @return string
			 */
			public function get_woo_tabs_content( $args ) {
				global $product;

				add_filter( 'woocommerce_product_tabs', [ $this, 'maybe_remove_tabs' ], 10 );

				if ( 'no' === $args['show_tab_titles'] ) {
					add_filter( 'woocommerce_product_description_heading', '__return_false', 99 );
					add_filter( 'woocommerce_product_additional_information_heading', '__return_false', 99 );
				}

				$content = '';
				if ( function_exists( 'wc_get_template_html' ) && is_object( $product ) ) {
					$content = wc_get_template_html( 'single-product/tabs/tabs.php' );
				}

				if ( 'no' === $args['show_tab_titles'] ) {
					remove_filter( 'woocommerce_product_description_heading', '__return_false', 99 );
					remove_filter( 'woocommerce_product_additional_information_heading', '__return_false', 99 );
				}

				// Remove inline script if in Live Editor.
				if ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) {
					$content = str_replace( [ '<script', '</script>' ], [ '<!--<script', '</script>-->' ], $content );
				}

				if ( $this->args['responsive_typography'] && ! $this->is_default( 'title_font_size' ) ) {
					$font_size = fusion_library()->sanitize->get_value_with_unit( $this->args['title_font_size'] );
					$data      = awb_get_responsive_type_data( 'h3', $font_size, $font_size );
					$content   = str_replace(
						'class="fusion-woocommerce-tab-title"',
						'class="fusion-woocommerce-tab-title ' . $data['class'] . '" style="' . $data['font_size'] . $data['line_height'] . '"',
						$content
					);
				}

				return apply_filters( 'fusion_woo_component_content', $content, $this->shortcode_handle, $this->args );
			}

			/**
			 * Maybe remove some Woo Tabs.
			 *
			 * @access public
			 * @since 3.2
			 * @param  array $tabs Woo Tabs array.
			 * @return array
			 */
			public function maybe_remove_tabs( $tabs ) {
				$available_tabs = [ 'description', 'additional_information', 'reviews' ];
				$selected_tabs  = is_array( $this->args['display_tabs'] ) ? $this->args['display_tabs'] : explode( ',', $this->args['display_tabs'] );
				$display_tabs   = array_diff( $available_tabs, $selected_tabs );

				// Early exit if all tabs are selected.
				if ( empty( $display_tabs ) ) {
					return $tabs;
				}

				foreach ( $display_tabs as $tab ) {
					unset( $tabs[ $tab ] );
				}

				return $tabs;
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.2
			 * @return string
			 */
			protected function get_styles() {
				$this->base_selector = '.fusion-woo-tabs-tb.fusion-woo-tabs-tb-' . $this->counter;
				$this->dynamic_css   = [];

				$sides = [ 'top', 'right', 'bottom', 'left' ];

				foreach ( $sides as $side ) {

					// Element margin.
					$margin_name = 'margin_' . $side;

					if ( '' !== $this->args[ $margin_name ] ) {
						$this->add_css_property( $this->base_selector, 'margin-' . $side, fusion_library()->sanitize->get_value_with_unit( $this->args[ $margin_name ] ) );
					}
				}

				if ( ! $this->is_default( 'backgroundcolor' ) ) {
					$this->add_css_property( $this->base_selector . ' .wc-tabs > li.active > a', 'background-color', $this->args['backgroundcolor'] );
					$this->add_css_property( $this->base_selector . ' .wc-tabs > li > a:hover', 'background-color', $this->args['backgroundcolor'] );
					$this->add_css_property( $this->base_selector . ' .woocommerce-Tabs-panel', 'background-color', $this->args['backgroundcolor'] );
				}

				if ( ! $this->is_default( 'inactivebackgroundcolor' ) ) {
					$this->add_css_property( $this->base_selector . ' .wc-tabs > li > a', 'background-color', $this->args['inactivebackgroundcolor'] );
				}

				if ( ! $this->is_default( 'active_nav_text_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .wc-tabs > li.active > a', 'color', $this->args['active_nav_text_color'] );
					$this->add_css_property( $this->base_selector . ' .wc-tabs > li > a:hover', 'color', $this->args['active_nav_text_color'] );
				}

				if ( ! $this->is_default( 'inactive_nav_text_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .wc-tabs > li > a', 'color', $this->args['inactive_nav_text_color'] );
				}

				if ( ! $this->is_default( 'bordercolor' ) ) {

					if ( 'horizontal' === $this->args['layout'] ) {
						$this->add_css_property( $this->base_selector . '.woo-tabs-horizontal .woocommerce-tabs > .tabs .active', 'border-color', $this->args['bordercolor'] );
						$this->add_css_property( $this->base_selector . '.woo-tabs-horizontal .woocommerce-tabs > .tabs', 'border-color', $this->args['bordercolor'] );
					} else {
						$this->add_css_property( $this->base_selector . ' .woocommerce-tabs .tabs li a', 'border-color', $this->args['bordercolor'] );
					}

					$this->add_css_property( $this->base_selector . ' .woocommerce-tabs .panel', 'border-color', $this->args['bordercolor'] );
					$this->add_css_property( $this->base_selector . ' .woocommerce-tabs .panel .shop_attributes tr', 'border-color', $this->args['bordercolor'] );
				}

				// Text styles.
				if ( ! $this->is_default( 'text_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .woocommerce-tabs .panel', 'color', $this->args['text_color'] );
					$this->add_css_property( $this->base_selector . ' .woocommerce-tabs .panel .shop_attributes th', 'color', $this->args['text_color'] );
					$this->add_css_property( '#wrapper ' . $this->base_selector . ' .meta', 'color', $this->args['text_color'] );
					$this->add_css_property( [ $this->base_selector . ' .stars a', $this->base_selector . ' .stars a:after' ], 'color', $this->args['text_color'] );
				}

				if ( ! $this->is_default( 'text_font_size' ) ) {
					$this->add_css_property( $this->base_selector . ' .woocommerce-tabs .panel', 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['text_font_size'] ) );
				}

				if ( ! $this->is_default( 'text_line_height' ) ) {
					$this->add_css_property( $this->base_selector . ' .woocommerce-tabs .panel', 'line-height', $this->args['text_line_height'] );
				}

				if ( ! $this->is_default( 'text_letter_spacing' ) ) {
					$this->add_css_property( $this->base_selector . ' .woocommerce-tabs .panel', 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['text_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'text_text_transform' ) ) {
					$this->add_css_property( $this->base_selector . ' .woocommerce-tabs .panel', 'text-transform', $this->args['text_text_transform'] );
				}

				// Text typography styles.
				$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'text_font', 'array' );
				foreach ( $text_styles as $rule => $value ) {
					$this->add_css_property( $this->base_selector . ' .woocommerce-tabs .panel', $rule, $value );
				}

				// Title styles.
				$title_selectors = [
					'#wrapper ' . $this->base_selector . ' #tab-reviews #reviews .woocommerce-Reviews-title',
					'#wrapper ' . $this->base_selector . ' .woocommerce-Tabs-panel .fusion-woocommerce-tab-title',
				];
				if ( ! $this->is_default( 'title_color' ) ) {
					$this->add_css_property( $title_selectors, 'color', $this->args['title_color'] );
				}

				if ( ! $this->is_default( 'title_font_size' ) ) {
					$this->add_css_property( $title_selectors, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['title_font_size'] ) );
				}

				if ( ! $this->is_default( 'title_line_height' ) ) {
					$this->add_css_property( $title_selectors, 'line-height', $this->args['title_line_height'] );
				}

				if ( ! $this->is_default( 'title_letter_spacing' ) ) {
					$this->add_css_property( $title_selectors, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['title_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'title_text_transform' ) ) {
					$this->add_css_property( $title_selectors, 'text-transform', $this->args['title_text_transform'] );
				}

				// Heading typography styles.
				$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'title_font', 'array' );
				foreach ( $text_styles as $rule => $value ) {
					$this->add_css_property( $title_selectors, $rule, $value );
				}

				if ( 'vertical' === $this->args['layout'] && ! $this->is_default( 'nav_content_space' ) ) {
					$this->add_css_property( $this->base_selector . ' .woocommerce-tabs .panel', 'margin-left', 'calc(220px + ' . fusion_library()->sanitize->get_value_with_unit( $this->args['nav_content_space'] ) . ')' );
				}

				// Stars color.
				if ( ! $this->is_default( 'stars_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .comment-text .star-rating:before', 'color', $this->args['stars_color'] );
					$this->add_css_property( $this->base_selector . ' .comment-text .star-rating span:before', 'color', $this->args['stars_color'] );
				}

				// Get padding.
				$paddings = [ 'top', 'right', 'bottom', 'left' ];

				foreach ( $paddings as $padding ) {
					$content_padding_name = 'content_padding_' . $padding;
					$nav_padding_name     = 'nav_padding_' . $padding;

					// Add content padding to style.
					if ( '' !== $this->args[ $content_padding_name ] ) {
						$this->add_css_property( $this->base_selector . ' .woocommerce-tabs .panel', 'padding-' . $padding, fusion_library()->sanitize->get_value_with_unit( $this->args[ $content_padding_name ] ) );
					}

					// Add nav padding to style.
					if ( '' !== $this->args[ $nav_padding_name ] ) {
						$this->add_css_property( $this->base_selector . ' .woocommerce-tabs .tabs li a', 'padding-' . $padding, fusion_library()->sanitize->get_value_with_unit( $this->args[ $nav_padding_name ] ) );
					}
				}

				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'fusion-woo-tabs-tb fusion-woo-tabs-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( 'horizontal' === $this->args['layout'] ) {
					$attr['class'] .= ' woo-tabs-horizontal';
				}

				if ( 'no' === $this->args['show_tab_titles'] ) {
					$attr['class'] .= ' woo-tabs-hide-headings';
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function add_css_files() {
				if ( class_exists( 'Avada' ) ) {
					$version = Avada::get_theme_version();

					Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-tabs.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-tabs.min.css' );
					Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-reviews.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-reviews.min.css' );
					Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-additional-info.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-additional-info.min.css' );

					Fusion_Media_Query_Scripts::$media_query_assets[] = [
						'avada-max-sh-cbp-woo-tabs',
						get_template_directory_uri() . '/assets/css/media/max-sh-cbp-woo-tabs.min.css',
						[],
						$version,
						Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-sh-cbp' ),
					];
				}
			}
		}
	}

	new FusionTB_Woo_Tabs();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.2
 */
function fusion_component_woo_tabs() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Tabs',
			[
				'name'                    => esc_html__( 'Woo Tabs', 'fusion-builder' ),
				'shortcode'               => 'fusion_tb_woo_tabs',
				'icon'                    => 'fusiona-woo-tabs',
				'component'               => true,
				'templates'               => [ 'content' ],
				'components_per_template' => 1,
				'subparam_map'            => [
					'fusion_font_family_title_font'  => 'title_fonts',
					'fusion_font_variant_title_font' => 'title_fonts',
					'title_font_size'                => 'title_fonts',
					'title_text_transform'           => 'title_fonts',
					'title_line_height'              => 'title_fonts',
					'title_letter_spacing'           => 'title_fonts',
					'title_color'                    => 'title_fonts',
					'fusion_font_family_text_font'   => 'text_fonts',
					'fusion_font_variant_text_font'  => 'text_fonts',
					'text_font_size'                 => 'text_fonts',
					'text_text_transform'            => 'text_fonts',
					'text_line_height'               => 'text_fonts',
					'text_letter_spacing'            => 'text_fonts',
					'text_color'                     => 'text_fonts',
				],
				'params'                  => [
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_html__( 'Display Tabs', 'fusion-builder' ),
						'param_name'  => 'display_tabs',
						'value'       => [
							'description'            => esc_html__( 'Description', 'fusion-builder' ),
							'additional_information' => esc_html__( 'Additional Information', 'fusion-builder' ),
							'reviews'                => esc_html__( 'Reviews', 'fusion-builder' ),
						],
						'icons'       => [
							'description'            => '<span class="fusiona-woo-short-description"></span>',
							'additional_information' => '<span class="fusiona-woo-additional-info"></span>',
							'reviews'                => '<span class="fusiona-woo-reviews"></span>',
						],
						'default'     => [ 'description', 'additional_information', 'reviews' ],
						'description' => esc_attr__( 'Choose which tabs should be displayed.', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_tabs',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Layout', 'fusion-builder' ),
						'description' => esc_html__( 'Choose the tabs layout.' ),
						'param_name'  => 'layout',
						'default'     => '',
						'value'       => [
							''           => esc_attr__( 'Default', 'fusion-builder' ),
							'horizontal' => esc_attr__( 'Horizontal', 'fusion-builder' ),
							'vertical'   => esc_attr__( 'Vertical', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_tabs',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Show Tab Content Headings', 'fusion-builder' ),
						'description' => esc_html__( 'Choose to have tab content headings displayed.', 'fusion-builder' ),
						'param_name'  => 'show_tab_titles',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_tabs',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Space Between Nav and Content', 'fusion-builder' ),
						'description' => esc_html__( 'Set space between tab nav and tab content sections. Leave empty for default value of 20px.', 'fusion-builder' ),
						'param_name'  => 'nav_content_space',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'vertical',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'margin',
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the background tab color. ', 'fusion-builder' ),
						'param_name'  => 'backgroundcolor',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Inactive Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the inactive tab background color. ', 'fusion-builder' ),
						'param_name'  => 'inactivebackgroundcolor',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Inactive Nav Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the inactive nav text color, ex: #000.' ),
						'param_name'  => 'inactive_nav_text_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Active Nav Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the active nav text color, ex: #000.' ),
						'param_name'  => 'active_nav_text_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Border Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the border color. ', 'fusion-builder' ),
						'param_name'  => 'bordercolor',
						'value'       => '',
						'default'     => '#e7e6e6',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Nav Padding', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default 10px 0 10px 0 value.', 'fusion-builder' ),
						'param_name'       => 'nav_padding',
						'value'            => [
							'nav_padding_top'    => '',
							'nav_padding_right'  => '',
							'nav_padding_bottom' => '',
							'nav_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Content Padding', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default 40px value.', 'fusion-builder' ),
						'param_name'       => 'content_padding',
						'value'            => [
							'content_padding_top'    => '',
							'content_padding_right'  => '',
							'content_padding_bottom' => '',
							'content_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Content Heading Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the content heading. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'title_fonts',
						'choices'          => [
							'font-family'    => 'title_font',
							'font-size'      => 'title_font_size',
							'text-transform' => 'title_text_transform',
							'line-height'    => 'title_line_height',
							'letter-spacing' => 'title_letter_spacing',
							'color'          => 'title_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'font-size'      => '',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => '',
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'show_tab_titles',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Content Text Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the content text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'text_fonts',
						'choices'          => [
							'font-family'    => 'text_font',
							'font-size'      => 'text_font_size',
							'text-transform' => 'text_text_transform',
							'line-height'    => 'text_line_height',
							'letter-spacing' => 'text_letter_spacing',
							'color'          => 'text_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'font-size'      => '',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => '',
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Review Stars Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of review stars, ex: #000.' ),
						'param_name'  => 'stars_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_html__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_html__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_html__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-woo-tabs-tb',
					],
				],
				'callback'                => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_tabs',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_tabs' );

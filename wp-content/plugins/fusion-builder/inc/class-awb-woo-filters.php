<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.8
 */

if ( ! class_exists( 'AWB_Woo_Filters' ) ) {
	/**
	 * Shortcode class.
	 *
	 * @since 3.8
	 */
	class AWB_Woo_Filters extends Fusion_Component {
		/**
		 * The one, true instance of this object.
		 *
		 * @static
		 * @access private
		 * @since 3.3
		 * @var object
		 */
		private static $instance;

		/**
		 * The counter.
		 *
		 * @access private
		 * @since 3.8
		 * @var int
		 */
		private $element_counter = 1;

		/**
		 * An array of the shortcode arguments.
		 *
		 * @access protected
		 * @since 3.8
		 * @var array
		 */
		protected $args;

		/**
		 * Whether we are requesting from editor.
		 *
		 * @access public
		 * @since 3.8
		 * @var string
		 */
		protected $live_request = false;

		/**
		 * CSS Vars prefix.
		 *
		 * @access public
		 * @since 3.8
		 * @var string
		 */
		public $css_vars_prefix = '--awb-';

		/**
		 * CSS Vars default prefix.
		 *
		 * @access public
		 * @since 3.8.1
		 * @var string
		 */
		public $css_vars_prefix_default = '--awb-';

		/**
		 * Constructor.
		 *
		 * @access public
		 * @since 3.8
		 */
		public function __construct() {
			parent::__construct( $this->shortcode_handle );

			add_filter( "fusion_attr_{$this->shortcode_handle}-shortcode", [ $this, 'attr' ] );

			// Ajax mechanism for query related part.
			add_action( "wp_ajax_get_{$this->shortcode_handle}", [ $this, 'ajax_render' ] );
		}

		/**
		 * Check if component should render
		 *
		 * @access public
		 * @since 3.3
		 * @return boolean
		 */
		public function should_render() {
			return is_shop() || is_product_taxonomy();
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @static
		 * @access public
		 * @since 3.8
		 */
		public static function get_instance() {
			static $instances = [];

			$called_class = get_called_class();

			if ( ! isset( $instances[ $called_class ] ) ) {
				$instances[ $called_class ] = new $called_class();
			}

			return $instances[ $called_class ];
		}

		/**
		 * Gets the default values.
		 *
		 * @static
		 * @access public
		 * @since 3.8
		 * @return array
		 */
		public static function get_element_defaults() {
			$fusion_settings = awb_get_fusion_settings();
			return [
				'hide_on_mobile'                 => fusion_builder_default_visibility( 'string' ),
				'class'                          => '',
				'id'                             => '',
				'title'                          => '',
				'title_size'                     => '4',
				'margin_bottom'                  => '',
				'margin_left'                    => '',
				'margin_right'                   => '',
				'margin_top'                     => '',
				'fusion_font_family_title_font'  => '',
				'fusion_font_variant_title_font' => '',
				'title_font_size'                => '',
				'title_line_height'              => '',
				'title_letter_spacing'           => '',
				'title_text_transform'           => '',
				'title_color'                    => $fusion_settings->get( 'h4_typography', 'color' ),
				'show_title'                     => 'yes',
				'title_margin_bottom'            => '',
				'title_margin_top'               => '',
				'animation_type'                 => '',
				'animation_direction'            => 'down',
				'animation_speed'                => '0.1',
				'animation_offset'               => $fusion_settings->get( 'animation_offset' ),
			];
		}

		/**
		 * Gets the widget instance options.
		 *
		 * @static
		 * @access public
		 * @since 3.8
		 * @return array
		 */
		public function get_widget_instance() {
			return [
				'title' => $this->args['title'],
			];
		}

		/**
		 * Validate the arguments into correct format.
		 *
		 * @access public
		 * @since 3.8
		 * @return void
		 */
		public function validate_args() {
			// Validate margin values.
			foreach ( [ 'top', 'bottom' ] as $direction ) {
				$margin_key                = 'title_margin_' . $direction;
				$this->args[ $margin_key ] = fusion_library()->sanitize->get_value_with_unit( $this->args[ $margin_key ] );
			}

			if ( ! $this->is_default( 'title_font_size' ) ) {
				$this->args['title_font_size'] = fusion_library()->sanitize->get_value_with_unit( $this->args['title_font_size'] );
			}

			if ( ! $this->is_default( 'title_letter_spacing' ) ) {
				$this->args['title_letter_spacing'] = fusion_library()->sanitize->get_value_with_unit( $this->args['title_letter_spacing'] );
			}

			if ( ! $this->is_default( 'title_color' ) ) {
				$this->args['title_color'] = fusion_library()->sanitize->color( $this->args['title_color'] );
			}
		}

		/**
		 * Gets the query data.
		 *
		 * @static
		 * @access public
		 * @since 3.8
		 * @param array $args An array of args.
		 * @return void
		 */
		public function ajax_render( $args ) {
			check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
			$return_data = [];

			// From Ajax Request.
			if ( isset( $_POST['model'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$this->args         = wp_unslash( $_POST['model']['params'] ); // phpcs:ignore WordPress.Security
				$this->live_request = true;
				add_filter( 'fusion_builder_live_request', '__return_true' );
			}
			$this->emulate_filter();
			$return_data['output']        = $this->get_filter_elements();
			$return_data['msg']           = ( isset( $this->args['attribute'] ) && '' === $this->args['attribute'] ) ? esc_html__( 'Please select attribute.', 'fusion-builder' ) : '';
			$return_data['extra_classes'] = $this->add_extra_classes();
			$this->restore_filter();
			echo wp_json_encode( $return_data );
			wp_die();
		}

		/**
		 * Render the shortcode.
		 *
		 * @access public
		 * @since 3.8
		 * @param  array  $args    Shortcode parameters.
		 * @param  string $content Content between shortcode.
		 * @return string          HTML output
		 */
		public function render( $args, $content = '' ) {
			global $post;

			$this->defaults = static::get_element_defaults();
			$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, $this->shortcode_handle );

			$this->validate_args();

			$this->emulate_filter();
			$html = trim( $this->get_filter_elements() );
			$this->restore_filter();

			if ( ! empty( $html ) ) {
				$html = '<div ' . FusionBuilder::attributes( $this->shortcode_handle . '-shortcode' ) . '>' . $html . '</div>';
			}

			$this->element_counter++;

			$this->on_render();

			return apply_filters( $this->shortcode_handle . '_content', $html, $args );
		}

		/**
		 * Builds HTML for filter elements.
		 *
		 * @since 3.8
		 * @return array
		 */
		public function get_filter_elements() {
			$content   = '';
			$widgets   = [
				'fusion_tb_woo_filters_active' => 'WC_Widget_Layered_Nav_Filters',
				'fusion_tb_woo_filters_price'  => 'WC_Widget_Price_Filter',
				'fusion_tb_woo_filters_rating' => 'WC_Widget_Rating_Filter',
			];
			$widget    = isset( $widgets[ $this->shortcode_handle ] ) ? $widgets[ $this->shortcode_handle ] : 'WC_Widget_Layered_Nav';
			$title_tag = 'div' === $this->args['title_size'] ? 'div' : 'h' . $this->args['title_size'];
			$args      = [
				'before_title' => '<div class="heading"><' . $title_tag . ' class="widget-title">',
				'after_title'  => '</' . $title_tag . '></div>',
			];

			ob_start();
			the_widget( $widget, $this->get_widget_instance(), $args );
			$content .= ob_get_clean();

			return $content;
		}

		/**
		 * Emulate filter for LE.
		 *
		 * @access public
		 * @since 3.8
		 * @return void
		 */
		public function emulate_filter() {
			global $post;

			$option = isset( $post->ID ) ? fusion_get_page_option( 'dynamic_content_preview_type', $post->ID ) : '';
			$value  = 'product_cat';

			if ( $this->live_request ) {
				if ( isset( $_POST['fusion_meta'] ) && isset( $_POST['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
					$meta   = fusion_string_to_array( wp_unslash( $_POST['fusion_meta'] ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
					$option = ! isset( $meta['_fusion']['dynamic_content_preview_type'] ) || in_array( $meta['_fusion']['dynamic_content_preview_type'], [ 'search', 'archives', 'term' ], true ) ? $meta['_fusion']['dynamic_content_preview_type'] : '';
				}
			}

			if ( ( ( fusion_is_preview_frame() || $this->live_request ) && in_array( $option, [ 'search', 'archives', 'term' ], true ) ) ) {
				$term_args = [
					'taxonomy'   => $value,
					'hide_empty' => true,
					'number'     => 1,
				];
				$terms     = get_terms( $term_args );

				$this->original_queried_object = $GLOBALS['wp_query']->queried_object;
				$this->original_is_tax         = $GLOBALS['wp_query']->is_tax;
				$this->original_is_archive     = $GLOBALS['wp_query']->is_archive;

				// Re-index array.
				if ( is_array( $terms ) && ! empty( $terms ) ) {
					$terms = array_values( $terms );

					$GLOBALS['wp_query']->is_tax               = true;
					$GLOBALS['wp_query']->is_archive           = true;
					$GLOBALS['wp_query']->queried_object       = $terms[0];
					$GLOBALS['wp_query']->query_vars[ $value ] = $terms[0]->slug;

					if ( 0 === $GLOBALS['wp_query']->post_count ) {
						$GLOBALS['wp_query']->post_count = 1;
					}

					$this->emulate_filter_element();
				}
			}
		}

		/**
		 * Restore filter for LE.
		 *
		 * @access public
		 * @since 3.8
		 * @return void
		 */
		public function restore_filter() {
			global $post;

			$option = isset( $post->ID ) ? fusion_get_page_option( 'dynamic_content_preview_type', $post->ID ) : '';
			$value  = isset( $post->ID ) ? fusion_get_page_option( 'preview_' . $option, $post->ID ) : '';

			if ( $this->live_request ) {
				if ( isset( $_POST['fusion_meta'] ) && isset( $_POST['post_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
					$meta   = fusion_string_to_array( wp_unslash( $_POST['fusion_meta'] ) ); //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
					$option = ! isset( $meta['_fusion']['dynamic_content_preview_type'] ) || in_array( $meta['_fusion']['dynamic_content_preview_type'], [ 'search', 'archives', 'term' ], true ) ? $meta['_fusion']['dynamic_content_preview_type'] : $option;
					$value  = isset( $meta['_fusion'][ 'preview_' . $option ] ) ? $meta['_fusion'][ 'preview_' . $option ] : $value;
				}
			}

			if ( ( ( fusion_is_preview_frame() || $this->live_request ) && in_array( $option, [ 'search', 'archives', 'term' ], true ) ) ) {
				// Restore global data.
				$GLOBALS['wp_query']->is_tax         = $this->original_is_tax;          // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$GLOBALS['wp_query']->is_archive     = $this->original_is_archive;      // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$GLOBALS['wp_query']->queried_object = $this->original_queried_object;  // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

				$this->restore_filter_element();
			}
		}

		/**
		 * Emulate filter element for LE.
		 *
		 * @access public
		 * @since 3.8
		 * @return void
		 */
		public function emulate_filter_element() {}

		/**
		 * Restore filter element for LE.
		 *
		 * @access public
		 * @since 3.8
		 * @return void
		 */
		public function restore_filter_element() {}

		/**
		 * Add extra classes to the element div.
		 *
		 * @access public
		 * @since 3.8.1
		 * @return string
		 */
		public function add_extra_classes() {
			return '';
		}

		/**
		 * Builds the attributes array.
		 *
		 * @access public
		 * @since 3.8
		 * @return array
		 */
		public function attr() {

			$attr = fusion_builder_visibility_atts(
				$this->args['hide_on_mobile'],
				[
					'class' => 'awb-woo-filters awb-woo-filters-' . $this->element_counter,
					'style' => '',
				]
			);

			if ( $this->args['animation_type'] ) {
				$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
			}

			$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

			$attr['style'] .= $this->get_style_variables();

			if ( $this->args['class'] ) {
				$attr['class'] .= ' ' . $this->args['class'];
			}

			if ( $this->args['id'] ) {
				$attr['id'] = $this->args['id'];
			}

			if ( 'yes' !== $this->args['show_title'] ) {
				$attr['class'] .= ' hide-show_title';
			}

			return $attr;

		}

		/**
		 * Get the style variables.
		 *
		 * @access protected
		 * @since 3.8
		 * @return string
		 */
		protected function get_style_variables() {
			$styles = '';

			// Title Typo.
			$styles .= $this->get_typo_variables(
				[
					'title_font'           => 'font',
					'title_font_size'      => 'size',
					'title_line_height'    => 'line_height',
					'title_letter_spacing' => 'letter_spacing',
					'title_text_transform' => 'text_transform',
					'title_color'          => 'color',
				]
			);

			// Validate margin values.
			foreach ( [ 'top', 'bottom' ] as $direction ) {
				$margin_key = 'title_margin_' . $direction;

				if ( ! $this->is_default( $margin_key ) ) {
					$styles .= $this->css_vars_prefix . 'title-margin-' . $direction . ':' . $this->args[ $margin_key ] . ';';
				}
			}

			return $styles;
		}

		/**
		 * Get the typography variables.
		 *
		 * @access protected
		 * @since 3.8
		 * @param array $params The parameters.
		 * @return string
		 */
		protected function get_typo_variables( $params ) {
			$styles = '';

			foreach ( $params as $key => $type ) {
				$prefix  = false !== strpos( $key, 'title' ) ? $this->css_vars_prefix_default : $this->css_vars_prefix;
				$prefix .= str_replace( '_', '-', $key );

				switch ( $type ) {
					case 'font':
						$font_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, $key, 'array' );

						foreach ( $font_styles as $rule => $value ) {
							$styles .= $prefix . '-' . str_replace( '_', '-', $rule ) . ':' . $value . ';';
						}
						break;

					default:
						if ( ! $this->is_default( $key ) ) {
							$styles .= $prefix . ':' . $this->args[ $key ] . ';';
						}
						break;
				}
			}

			return $styles;
		}

		/**
		 * Get the dimension variables.
		 *
		 * @access protected
		 * @since 3.8
		 * @param string $option_name The option name.
		 * @return string
		 */
		protected function get_dimension_variables( $option_name ) {
			$styles = '';
			foreach ( [ 'top', 'right', 'bottom', 'left' ] as $direction ) {
				$key = $option_name . '_' . $direction;

				if ( ! $this->is_default( $key ) ) {
					$styles .= $this->css_vars_prefix . str_replace( '_', '-', $key ) . ':' . $this->args[ $key ] . ';';
				}
			}
			return $styles;
		}

		/**
		 * Load base CSS.
		 *
		 * @access public
		 * @since 3.8
		 * @return void
		 */
		public function add_css_files() {
			FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/woo-filters.min.css' );
		}

		/**
		 * Fetch general options.
		 *
		 * @access public
		 * @since 3.8
		 * @return array
		 */
		public function fetch_general_options() {
			return [
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Title', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose if title should show or not.', 'fusion-builder' ),
					'param_name'  => 'show_title',
					'default'     => 'yes',
					'value'       => [
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					],
				],
				[
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Title', 'fusion-builder' ),
					'param_name'  => 'title',
					'value'       => esc_html__( 'Title', 'fusion-builder' ),
					'description' => esc_attr__( 'Add the title that will display on element.', 'fusion-builder' ),
					'callback'    => [
						'function' => 'fusion_ajax',
						'action'   => "get_{$this->shortcode_handle}",
						'ajax'     => true,
					],
					'dependency'  => [
						[
							'element'  => 'show_title',
							'value'    => 'no',
							'operator' => '!=',
						],
					],
				],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Title Tag', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose HTML tag of the title, either div or the heading tag, h1-h6.', 'fusion-builder' ),
					'param_name'  => 'title_size',
					'value'       => [
						'1'   => 'H1',
						'2'   => 'H2',
						'3'   => 'H3',
						'4'   => 'H4',
						'5'   => 'H5',
						'6'   => 'H6',
						'div' => 'DIV',
					],
					'default'     => '4',
					'callback'    => [
						'function' => 'fusion_ajax',
						'action'   => "get_{$this->shortcode_handle}",
						'ajax'     => true,
					],
					'dependency'  => [
						[
							'element'  => 'show_title',
							'value'    => 'no',
							'operator' => '!=',
						],
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
			];
		}

		/**
		 * Fetch design options.
		 *
		 * @access public
		 * @since 3.8
		 * @return array
		 */
		public function fetch_design_options() {
			$fusion_settings = awb_get_fusion_settings();
			return [
				[
					'type'             => 'dimension',
					'remove_from_atts' => true,
					'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
					'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
					'param_name'       => 'margin',
					'value'            => [
						'margin_top'    => '',
						'margin_right'  => '',
						'margin_bottom' => '',
						'margin_left'   => '',
					],
					'group'            => esc_attr__( 'Design', 'fusion-builder' ),
				],
				[
					'type'             => 'typography',
					'heading'          => esc_attr__( 'Title Typography', 'fusion-builder' ),
					'description'      => esc_html__( 'Controls the typography of the title. Leave empty for the global font family.', 'fusion-builder' ),
					'param_name'       => 'title_fonts',
					'choices'          => [
						'font-family'    => 'title_font',
						'font-size'      => 'title_font_size',
						'text-transform' => 'title_text_transform',
						'line-height'    => 'title_line_height',
						'letter-spacing' => 'title_letter_spacing',
						'color'          => 'title_color',
						'margin-top'     => 'title_margin_top',
						'margin-bottom'  => 'title_margin_bottom',
					],
					'default'          => [
						'font-family'    => '',
						'variant'        => '400',
						'font-size'      => '',
						'text-transform' => '',
						'line-height'    => '',
						'letter-spacing' => '',
						'color'          => $fusion_settings->get( 'h4_typography', 'color' ),
						'margin-top'     => '',
						'margin-bottom'  => '',
					],
					'remove_from_atts' => true,
					'global'           => true,
					'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					'dependency'       => [
						[
							'element'  => 'show_title',
							'value'    => 'no',
							'operator' => '!=',
						],
					],
				],
			];
		}

		/**
		 * Get element subparams.
		 *
		 * @access public
		 * @since 3.8
		 * @return array
		 */
		public function get_element_subparams() {
			return [
				'fusion_font_family_title_font'  => 'title_fonts',
				'fusion_font_variant_title_font' => 'title_fonts',
				'title_font_size'                => 'title_fonts',
				'title_line_height'              => 'title_fonts',
				'title_letter_spacing'           => 'title_fonts',
				'title_text_transform'           => 'title_fonts',
				'title_color'                    => 'title_fonts',
			];
		}

		/**
		 * Get element params.
		 *
		 * @access public
		 * @since 3.8
		 * @return array
		 */
		public function get_element_params() {
			$general_options = $this->fetch_general_options();
			$design_options  = $this->fetch_design_options();

			$params = [];

			foreach ( $general_options as $general_opt ) {
				$params[] = $general_opt;
			}

			foreach ( $design_options as $design_opt ) {
				$params[] = $design_opt;
			}

			$params['fusion_animation_placeholder'] = [
				'preview_selector' => '.awb-woo-filters',
			];

			return $params;
		}
	}
}

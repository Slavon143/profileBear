<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( ! class_exists( 'Fusion_Woo_Products_Component' ) ) {
	/**
	 * Shortcode class.
	 *
	 * @since 1.0
	 */
	class Fusion_Woo_Products_Component extends Fusion_Woo_Component {

		/**
		 * The one, true instance array of this object.
		 *
		 * @static
		 * @access private
		 * @since 3.2
		 * @var array
		 */
		public static $instances = [];

		/**
		 * An array of the shortcode arguments.
		 *
		 * @access public
		 * @since 3.2
		 * @var array
		 */
		public $args;

		/**
		 * The internal container counter.
		 *
		 * @access private
		 * @since 3.2
		 * @var int
		 */
		private $counter = 1;

		/**
		 * Whether we are requesting from editor.
		 *
		 * @access protected
		 * @since 3.2
		 * @var array
		 */
		protected $live_ajax = false;

		/**
		 * Constructor.
		 *
		 * @access public
		 * @param string $shortcode         The shortcode we want to add.
		 * @since 1.0
		 */
		public function __construct( $shortcode ) {
			parent::__construct( $shortcode );

			add_filter( "fusion_attr_{$this->shortcode_handle}", [ $this, 'attr' ] );
			add_filter( "fusion_attr_{$this->shortcode_handle}-carousel", [ $this, 'carousel_attr' ] );

			// Ajax mechanism for query related part.
			add_action( "wp_ajax_get_{$this->shortcode_handle}", [ $this, 'ajax_render' ] );
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
		 * Checking the right page.
		 *
		 * @since 3.3
		 * @return string
		 */
		public function is_checking_page() {
			return $this->is_product();
		}

		/**
		 * Render for live editor.
		 *
		 * @static
		 * @access public
		 * @since 3.2
		 * @return void
		 */
		public function ajax_render() {
			check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

			$return_data = [];
			// From Ajax Request.
			if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$args           = $_POST['model']['params']; // phpcs:ignore WordPress.Security
				$post_id        = isset( $_POST['post_id'] ) ? $_POST['post_id'] : get_the_ID(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, $this->shortcode_handle );

				fusion_set_live_data();
				add_filter( 'fusion_builder_live_request', '__return_true' );

				$this->emulate_product();

				if ( ! $this->is_checking_page() ) {
					echo wp_json_encode( $return_data );
					wp_die();
				}

				$this->live_ajax = true;
				$products        = $this->get_query();

				$return_data[ $this->shortcode_handle ] = is_array( $products['products'] ) && 0 < count( $products['products'] ) ? $this->render_layout( $products ) : $this->get_placeholder();
				$return_data['product_counts']          = is_array( $products['products'] ) ? count( $products['products'] ) : 0;
				$return_data['query_type']              = $this->query_type();
				$this->restore_product();
			}

			echo wp_json_encode( $return_data );
			wp_die();
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
				'number_products'         => $fusion_settings->get( 'number_related_posts' ),
				'products_columns'        => $fusion_settings->get( 'woocommerce_related_columns' ),
				'products_layout'         => 'boxes',
				'products_navigation'     => 'yes',
				'products_autoplay'       => 'no',
				'products_swipe'          => 'no',
				'products_column_spacing' => $fusion_settings->get( 'related_posts_column_spacing' ),
				'products_swipe_items'    => $fusion_settings->get( 'related_posts_swipe_items' ),
				'heading_enable'          => 'yes',
				'heading_size'            => '3',
				'margin_bottom'           => '',
				'margin_left'             => '',
				'margin_right'            => '',
				'margin_top'              => '',
				'hide_on_mobile'          => fusion_builder_default_visibility( 'string' ),
				'class'                   => '',
				'id'                      => '',
				'animation_type'          => '',
				'animation_direction'     => 'down',
				'animation_speed'         => '0.1',
				'animation_offset'        => $fusion_settings->get( 'animation_offset' ),
			];
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
			$this->emulate_product();

			if ( ! $this->is_checking_page() ) {
				return;
			}

			$this->defaults = self::get_element_defaults();
			$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, $this->shortcode_handle );

			$products     = $this->get_query();
			$main_heading = $this->get_main_heading();
			$html         = $this->get_styles();
			$html        .= '<section ' . FusionBuilder::attributes( $this->shortcode_handle ) . '>';

			if ( 'yes' === $this->args['heading_enable'] ) {
				$html .= fusion_render_title( $this->args['heading_size'], apply_filters( $this->shortcode_handle . '_heading_text', $main_heading, 'product' ) );
			}

			if ( is_array( $products['products'] ) && 0 < count( $products['products'] ) ) {
				$html .= $this->render_layout( $products );
				$html .= '</section>';
			} elseif ( is_array( $products['products'] ) && 0 === count( $products['products'] ) && $this->is_builder() ) {
				// Add preview for Live Builder.
				$html .= $this->get_placeholder();
			} else {
				$html = '';
			}

			$this->restore_product();

			$this->counter++;

			$this->on_render();

			return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
		}

		/**
		 * Builds HTML for Woo Related Products.
		 *
		 * @access public
		 * @since 3.2
		 * @param array $args layout arguments.
		 * @return string
		 */
		public function render_layout( $args ) {

			$html = '';

			// Set global loop values.
			$this->set_loop_props( $args );

			add_filter( 'woocommerce_product_loop_start', [ $this, 'loop_start_attr' ], 20 );

			if ( 'carousel' === $this->args['products_layout'] ) {
				add_filter( 'woocommerce_post_class', [ $this, 'wc_post_class' ], 20, 2 );
				add_action( 'woocommerce_before_shop_loop_item', [ $this, 'before_shop_loop_item' ], 8 );
				add_action( 'woocommerce_after_shop_loop_item', [ $this, 'after_shop_loop_item' ], 15 );

				if ( ! $this->live_ajax ) {
					$html .= '<div ' . FusionBuilder::attributes( $this->shortcode_handle . '-carousel' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$html .= '<div class="fusion-carousel-positioner">';
				}
			}

			if ( ! $this->live_ajax ) {
				$html .= woocommerce_product_loop_start( false );
			}

			ob_start();
			foreach ( $args['products'] as $product_data ) :

				$post_object = get_post( $product_data->get_id() );

				setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

				wc_get_template_part( 'content', 'product' );

			endforeach;
			$html .= ob_get_clean();

			if ( ! $this->live_ajax ) {
				$html .= woocommerce_product_loop_end( false );
			}

			wp_reset_postdata();

			/**
			 * Add navigation if needed.
			 */
			if ( 'carousel' === $this->args['products_layout'] && 'yes' === $this->args['products_navigation'] && ! $this->live_ajax ) {
				$html .= '<div class="fusion-carousel-nav">';
				$html .= '<button class="fusion-nav-prev"></button>';
				$html .= '<button class="fusion-nav-next"></button>';
				$html .= '</div>';
			}

			if ( 'carousel' === $this->args['products_layout'] && ! $this->live_ajax ) {
				$html .= '</div><!-- fusion-carousel-positioner -->';
				$html .= '</div><!-- fusion-carousel -->';
			}

			remove_filter( 'woocommerce_product_loop_start', [ $this, 'loop_start_attr' ], 20 );

			if ( 'carousel' === $this->args['products_layout'] ) {
				remove_filter( 'woocommerce_post_class', [ $this, 'wc_post_class' ], 20, 2 );
				remove_action( 'woocommerce_before_shop_loop_item', [ $this, 'before_shop_loop_item' ], 8 );
				remove_action( 'woocommerce_after_shop_loop_item', [ $this, 'after_shop_loop_item' ], 15 );
			}

			return $html;
		}

		/**
		 * Get product query.
		 *
		 * @access public
		 * @since 3.2
		 * @return array
		 */
		public function get_query() {
			return [];
		}

		/**
		 * Get query type.
		 *
		 * @access public
		 * @since 3.3
		 * @return string
		 */
		public function query_type() {
			return '';
		}

		/**
		 * Sets the necessary scripts.
		 *
		 * @access public
		 * @since 3.2
		 * @return void
		 */
		public function on_first_render() {
			Fusion_Dynamic_JS::enqueue_script( 'fusion-carousel' );

			if ( class_exists( 'Avada' ) ) {
				global $avada_woocommerce;

				$js_folder_suffix = FUSION_BUILDER_DEV_MODE ? '/assets/js' : '/assets/min/js';
				$js_folder_url    = Avada::$template_dir_url . $js_folder_suffix;
				$js_folder_path   = Avada::$template_dir_path . $js_folder_suffix;
				$version          = Avada::get_theme_version();

				Fusion_Dynamic_JS::enqueue_script(
					'avada-woo-products',
					$js_folder_url . '/general/avada-woo-products.js',
					$js_folder_path . '/general/avada-woo-products.js',
					[ 'jquery', 'fusion-flexslider' ],
					$version,
					true
				);

				Fusion_Dynamic_JS::localize_script(
					'avada-woo-products',
					'avadaWooCommerceVars',
					$avada_woocommerce::get_avada_wc_vars()
				);
			}
		}

		/**
		 * Set wc loop props.
		 *
		 * @access public
		 * @param array $args layout arguments.
		 * @since 3.2
		 * @return void
		 */
		public function set_loop_props( $args ) {}

		/**
		 * Builds the attributes array.
		 *
		 * @access public
		 * @since 3.2
		 * @return array
		 */
		public function attr() {
			$attr = [
				'class' => "fusion-woo-products-tb {$this->shortcode_classname} {$this->shortcode_classname}-" . $this->counter,
				'style' => '',
			];

			$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

			if ( '' !== $this->args['animation_type'] ) {
				$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
			}

			$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

			if ( $this->args['class'] ) {
				$attr['class'] .= ' ' . $this->args['class'];
			}

			if ( $this->args['id'] ) {
				$attr['id'] = $this->args['id'];
			}

			return $attr;
		}

		/**
		 * Builds the carousel wrapper attributes array.
		 *
		 * @access public
		 * @since 2.2
		 * @return array
		 */
		public function carousel_attr() {

			$attr['class'] = 'fusion-carousel';

			/**
			 * Set the autoplay variable.
			 */
			$attr['data-autoplay'] = $this->args['products_autoplay'];

			/**
			 * Set the touch scroll variable.
			 */
			$attr['data-touchscroll'] = $this->args['products_swipe'];

			$attr['data-columns']    = $this->args['products_columns'];
			$attr['data-itemmargin'] = intval( $this->args['products_column_spacing'] ) . 'px';
			$attr['data-itemwidth']  = 180;

			$products_swipe_items     = $this->args['products_swipe_items'];
			$products_swipe_items     = ( 0 == $products_swipe_items ) ? '1' : $products_swipe_items; // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$attr['data-scrollitems'] = $products_swipe_items;

			return $attr;
		}

		/**
		 * Build loop start attributes.
		 *
		 * @access public
		 * @param string $html HTML.
		 * @since 3.2
		 * @return array
		 */
		public function loop_start_attr( $html ) {
			$html = str_replace( 'columns-', 'products-', $html );
			return $html;
		}

		/**
		 * Build wc post class attributes.
		 *
		 * @access public
		 * @param string $classes classes.
		 * @param object $product product object.
		 * @since 3.2
		 * @return array
		 */
		public function wc_post_class( $classes, $product ) {
			$key = array_search( 'product-grid-view', $classes, true );

			if ( false !== $key ) {
				unset( $classes[ $key ] );
			}

			$classes[] = 'fusion-carousel-item';
			return $classes;
		}

		/**
		 * Build wc post class attributes.
		 *
		 * @access public
		 * @since 3.2
		 * @return void
		 */
		public function before_shop_loop_item() {
			echo '<div class="fusion-carousel-item-wrapper">';
		}

		/**
		 * Build wc post class attributes.
		 *
		 * @access public
		 * @since 3.2
		 * @return void
		 */
		public function after_shop_loop_item() {
			echo '</div>';
		}

		/**
		 * Get the styles.
		 *
		 * @access protected
		 * @since 3.2
		 * @return string
		 */
		protected function get_styles() {
			$this->base_selector = ".{$this->shortcode_classname}.{$this->shortcode_classname}-" . $this->counter;
			$this->dynamic_css   = [];

			if ( ! $this->is_default( 'products_layout' ) ) {
				$selectors = [
					'body:not(.fusion-woocommerce-equal-heights):not(.fusion-woo-archive-page-columns-1) ' . $this->base_selector . ' .fusion-carousel .fusion-carousel-item .fusion-carousel-item-wrapper',
					'.fusion-woocommerce-equal-heights:not(.fusion-woo-archive-page-columns-1) ' . $this->base_selector . ' .products .product',
				];
				$this->add_css_property( $selectors, 'display', 'block' );
				$selectors = [
					'.fusion-woocommerce-equal-heights:not(.fusion-woo-archive-page-columns-1) ' . $this->base_selector . ' .fusion-carousel .fusion-carousel-item .fusion-carousel-item-wrapper',
				];
				$this->add_css_property( $selectors, 'vertical-align', 'top' );
			}

			$css = $this->parse_css();

			return $css ? '<style>' . $css . '</style>' : '';
		}

		/**
		 * Get 'no related products' placeholder.
		 *
		 * @since 3.2
		 * @return string
		 */
		protected function get_placeholder() {
			return '';
		}

		/**
		 * Define heading text.
		 *
		 * @access public
		 * @since 3.2
		 * @return string
		 */
		public function get_main_heading() {
			return '';
		}

		/**
		 * Used to set any other variables for use on front-end editor template.
		 *
		 * @static
		 * @access public
		 * @since 3.2
		 * @return array
		 */
		public static function get_element_extras() {
			$fusion_settings = awb_get_fusion_settings();
			return [
				'title_margin'       => $fusion_settings->get( 'title_margin' ),
				'title_border_color' => $fusion_settings->get( 'title_border_color' ),
				'title_style_type'   => $fusion_settings->get( 'title_style_type' ),
			];
		}

		/**
		 * Maps settings to extra variables.
		 *
		 * @static
		 * @access public
		 * @since 3.2
		 * @return array
		 */
		public static function settings_to_extras() {

			return [
				'title_margin'       => 'title_margin',
				'title_border_color' => 'title_border_color',
				'title_style_type'   => 'title_style_type',
			];
		}

		/**
		 * Check if it live builder page.
		 *
		 * @access public
		 * @since 3.3
		 * @return boolean
		 */
		public function is_builder() {
			return ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() )
				|| ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() )
				|| ( fusion_doing_ajax() && isset( $_POST['fusion_load_nonce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
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
				Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-products.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-products.min.css' );
			}
			FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/woo-products.min.css' );
		}
	}

	if ( ! function_exists( 'fusion_get_woo_product_params' ) ) {

		/**
		 * Return an array of product parameters.
		 *
		 * @since 3.2
		 * @param array $args arguments.
		 * @return array
		 */
		function fusion_get_woo_product_params( $args ) {
			$fusion_settings = awb_get_fusion_settings();

			// Default Args.
			$args = wp_parse_args(
				$args,
				[
					'ajax_action'                => '',
					'animation_preview_selector' => '',
				]
			);

			return [
				[
					'heading'     => esc_html__( 'Layout', 'fusion-builder' ),
					'description' => esc_html__( 'Controls the layout style for products.', 'fusion-builder' ),
					'param_name'  => 'products_layout',
					'default'     => 'boxes',
					'type'        => 'radio_button_set',
					'value'       => [
						'boxes'    => esc_html__( 'Columns', 'fusion-builder' ),
						'carousel' => esc_html__( 'Carousel', 'fusion-builder' ),
					],
					'callback'    => [
						'function' => 'fusion_ajax',
						'action'   => $args['ajax_action'],
						'ajax'     => true,
					],
				],
				[
					'heading'     => esc_html__( 'Number of Products', 'fusion-builder' ),
					'description' => esc_html__( 'Controls the number of products that display on a single product.', 'fusion-builder' ),
					'param_name'  => 'number_products',
					'value'       => $fusion_settings->get( 'number_related_posts' ),
					'type'        => 'range',
					'min'         => '0',
					'max'         => '30',
					'step'        => '1',
					'callback'    => [
						'function' => 'fusion_ajax',
						'action'   => $args['ajax_action'],
						'ajax'     => true,
					],
				],
				[
					'heading'     => esc_html__( 'Maximum Columns', 'fusion-builder' ),
					'description' => esc_html__( 'Controls the number of columns for products layout.', 'fusion-builder' ),
					'param_name'  => 'products_columns',
					'value'       => $fusion_settings->get( 'woocommerce_related_columns' ),
					'type'        => 'range',
					'min'         => '1',
					'max'         => '6',
					'step'        => '1',
				],
				[
					'heading'     => esc_html__( 'Column Spacing', 'fusion-builder' ),
					'description' => esc_html__( 'Controls the amount of spacing between columns for products.', 'fusion-builder' ),
					'param_name'  => 'products_column_spacing',
					'value'       => $fusion_settings->get( 'related_posts_column_spacing' ),
					'type'        => 'range',
					'min'         => '0',
					'step'        => '1',
					'max'         => '300',
					'dependency'  => [
						[
							'element'  => 'products_layout',
							'value'    => 'boxes',
							'operator' => '!=',
						],
					],
				],
				[
					'heading'     => esc_html__( 'Autoplay', 'fusion-builder' ),
					'description' => esc_html__( 'Turn on to autoplay products carousel.', 'fusion-builder' ),
					'param_name'  => 'products_autoplay',
					'default'     => 'no',
					'type'        => 'radio_button_set',
					'value'       => [
						'yes' => esc_html__( 'Yes', 'fusion-builder' ),
						'no'  => esc_html__( 'No', 'fusion-builder' ),
					],
					'dependency'  => [
						[
							'element'  => 'products_layout',
							'value'    => 'boxes',
							'operator' => '!=',
						],
					],
				],
				[
					'heading'     => esc_html__( 'Show Navigation', 'fusion-builder' ),
					'description' => esc_html__( 'Turn on to display navigation arrows on the carousel.', 'fusion-builder' ),
					'param_name'  => 'products_navigation',
					'default'     => 'yes',
					'type'        => 'radio_button_set',
					'value'       => [
						'yes' => esc_html__( 'Yes', 'fusion-builder' ),
						'no'  => esc_html__( 'No', 'fusion-builder' ),
					],
					'dependency'  => [
						[
							'element'  => 'products_layout',
							'value'    => 'boxes',
							'operator' => '!=',
						],
					],
				],
				[
					'heading'     => esc_html__( 'Mouse Scroll', 'fusion-builder' ),
					'description' => esc_html__( 'Turn on to enable mouse drag control on the carousel.', 'fusion-builder' ),
					'param_name'  => 'products_swipe',
					'default'     => 'no',
					'type'        => 'radio_button_set',
					'value'       => [
						'yes' => esc_html__( 'Yes', 'fusion-builder' ),
						'no'  => esc_html__( 'No', 'fusion-builder' ),
					],
					'dependency'  => [
						[
							'element'  => 'products_layout',
							'value'    => 'boxes',
							'operator' => '!=',
						],
					],
				],
				[
					'heading'     => esc_html__( 'Scroll Items', 'fusion-builder' ),
					'description' => esc_html__( 'Controls the number of items that scroll at one time. Set to 0 to scroll the number of visible items.', 'fusion-builder' ),
					'param_name'  => 'products_swipe_items',
					'value'       => $fusion_settings->get( 'related_posts_swipe_items' ),
					'type'        => 'range',
					'min'         => '1',
					'max'         => '15',
					'step'        => '1',
					'dependency'  => [
						[
							'element'  => 'products_layout',
							'value'    => 'boxes',
							'operator' => '!=',
						],
					],
				],
				[
					'type'        => 'checkbox_button_set',
					'heading'     => esc_html__( 'Element Visibility', 'fusion-builder' ),
					'param_name'  => 'hide_on_mobile',
					'value'       => fusion_builder_visibility_options( 'full' ),
					'default'     => fusion_builder_default_visibility( 'array' ),
					'description' => esc_html__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
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
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_html__( 'Enable Heading', 'fusion-builder' ),
					'description' => esc_html__( 'Turn on if you want to display default heading.', 'fusion-builder' ),
					'param_name'  => 'heading_enable',
					'default'     => 'yes',
					'value'       => [
						'yes' => esc_html__( 'Yes', 'fusion-builder' ),
						'no'  => esc_html__( 'No', 'fusion-builder' ),
					],
					'group'       => esc_html__( 'Design', 'fusion-builder' ),
				],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_html__( 'HTML Heading Size', 'fusion-builder' ),
					'description' => esc_html__( 'Choose the size of the HTML heading that should be used, h1-h6.', 'fusion-builder' ),
					'param_name'  => 'heading_size',
					'value'       => [
						'1' => 'H1',
						'2' => 'H2',
						'3' => 'H3',
						'4' => 'H4',
						'5' => 'H5',
						'6' => 'H6',
					],
					'default'     => '3',
					'group'       => esc_html__( 'Design', 'fusion-builder' ),
					'dependency'  => [
						[
							'element'  => 'heading_enable',
							'value'    => 'no',
							'operator' => '!=',
						],
					],
				],
				'fusion_margin_placeholder'    => [
					'param_name' => 'margin',
					'value'      => [
						'margin_top'    => '',
						'margin_right'  => '',
						'margin_bottom' => '',
						'margin_left'   => '',
					],
				],
				'fusion_animation_placeholder' => [
					'preview_selector' => $args['animation_preview_selector'],
				],
			];
		}
	}
}

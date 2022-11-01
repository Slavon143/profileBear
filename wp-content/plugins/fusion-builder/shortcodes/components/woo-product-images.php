<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_product_images' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Product_Images' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.2
		 */
		class FusionTB_Woo_Product_Images extends Fusion_Woo_Component {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $args;

			/**
			 * An array of the unmerged shortcode arguments.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $params;

			/**
			 * Whether we are requesting from editor.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $live_ajax = false;

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
				parent::__construct( 'fusion_tb_woo_product_images' );
				add_filter( 'fusion_attr_fusion_tb_woo_product_images-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_woo_product_images', [ $this, 'ajax_render' ] );
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
					$args = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
					$this->live_ajax = true;

					$return_data['markup'] = $this->render( $args );
				}

				echo wp_json_encode( $return_data );
				wp_die();
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
					'display_sale_badge'       => 'yes',
					'display_outofstock_badge' => 'no',
					'margin_bottom'            => '',
					'margin_left'              => '',
					'margin_right'             => '',
					'margin_top'               => '',
					'product_images_layout'    => $fusion_settings->get( 'woocommerce_product_images_layout' ),
					'product_images_width'     => $fusion_settings->get( 'woocommerce_single_gallery_size' ),
					'product_images_zoom'      => $fusion_settings->get( 'woocommerce_product_images_zoom' ) ? 'yes' : 'no',
					'thumbnail_column_width'   => $fusion_settings->get( 'woocommerce_product_images_thumbnail_column_width' ),
					'thumbnail_columns'        => $fusion_settings->get( 'woocommerce_gallery_thumbnail_columns' ),
					'thumbnail_position'       => $fusion_settings->get( 'woocommerce_product_images_thumbnail_position' ),
					'hide_on_mobile'           => fusion_builder_default_visibility( 'string' ),
					'class'                    => '',
					'id'                       => '',
					'animation_type'           => '',
					'animation_direction'      => 'down',
					'animation_speed'          => '0.1',
					'animation_offset'         => $fusion_settings->get( 'animation_offset' ),
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
				global $product;

				$this->emulate_product();

				if ( ! $this->is_product() ) {
					return;
				}

				$this->params   = $args;
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_woo_product_images' );

				$this->add_hooks();
				$this->add_badges();

				if ( $this->live_ajax ) {
					$html = $this->get_images();
				} else {
					$html      = '<div ' . FusionBuilder::attributes( 'fusion_tb_woo_product_images-shortcode' ) . '>';
						$html .= $this->get_images();
						$html .= $this->get_styles();
					$html     .= '</div>';
				}

				$this->remove_hooks();
				$this->remove_badges();

				$this->restore_product();

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_woo_product_images', $html, $args );
			}

			/**
			 * Add needed hooks.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function add_hooks() {
				add_filter( 'avada_single_product_images_wrapper_classes', [ $this, 'add_single_product_images_wrapper_classes' ], 20 );
				add_filter( 'avada_woocommerce_product_images_layout', [ $this, 'product_images_layout' ], 20 );
				add_filter( 'woocommerce_product_thumbnails_columns', [ $this, 'product_thumbnails_columns' ], 20 );

				if ( 'no' === $this->args['product_images_zoom'] ) {
					// Script is auto enqueued through adding theme support in class-avada-init.php.
					wp_dequeue_script( 'zoom' );
				}

				if ( 'woocommerce' === $this->args['product_images_layout'] ) {
					// Style is auto dequeued in class-fusion.woocommerce.php.
					wp_enqueue_style( 'photoswipe-default-skin' );
				} else {

					// Script is auto enqueued through adding theme support in class-avada-init.php.
					wp_dequeue_script( 'photoswipe-ui-default' );
				}
			}

			/**
			 * Remove hooks.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function remove_hooks() {
				remove_filter( 'avada_single_product_images_wrapper_classes', [ $this, 'add_single_product_images_wrapper_classes' ], 20 );
				remove_filter( 'avada_woocommerce_product_images_layout', [ $this, 'product_images_layout' ], 20 );
				remove_filter( 'woocommerce_product_thumbnails_columns', [ $this, 'product_thumbnails_columns' ], 20 );
			}

			/**
			 * Add badges stuff.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function add_badges() {
				global $product, $avada_woocommerce;

				// Add badge wrapper if needed.
				if ( is_object( $product ) && is_object( $avada_woocommerce ) && ( $product->is_on_sale() && 'yes' === $this->args['display_sale_badge'] || ! $product->is_in_stock() && 'yes' === $this->args['display_outofstock_badge'] ) ) {

					// Open wrapper.
					add_action( 'woocommerce_before_single_product_summary', [ $avada_woocommerce, 'open_badges_wrapper' ], 8 );

					// Close wrapper.
					add_action( 'woocommerce_before_single_product_summary', [ $avada_woocommerce, 'close_badges_wrapper' ], 11 );
				}

				// Add out of stock.
				if ( 'yes' === $this->args['display_outofstock_badge'] && is_object( $avada_woocommerce ) ) {
					add_action( 'woocommerce_before_single_product_summary', [ $avada_woocommerce, 'show_product_loop_outofstock_flash' ], 9 );
				}

				// Sale badge is added by default, remove it if needed.
				if ( 'no' === $this->args['display_sale_badge'] ) {
					remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
				}
			}

			/**
			 * Remove badges stuff.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function remove_badges() {
				global $product, $avada_woocommerce;

				if ( is_object( $product ) && is_object( $avada_woocommerce ) && ( $product->is_on_sale() && 'yes' === $this->args['display_sale_badge'] || ! $product->is_in_stock() && 'yes' === $this->args['display_outofstock_badge'] ) ) {

					// Remove open wrapper.
					remove_action( 'woocommerce_before_single_product_summary', [ $avada_woocommerce, 'open_badges_wrapper' ], 8 );

					// Remove close wrapper.
					remove_action( 'woocommerce_before_single_product_summary', [ $avada_woocommerce, 'close_badges_wrapper' ], 11 );
				}

				// Remove out of stock if it was added.
				if ( 'yes' === $this->args['display_outofstock_badge'] && is_object( $avada_woocommerce ) ) {
					remove_action( 'woocommerce_before_single_product_summary', [ $avada_woocommerce, 'show_product_loop_outofstock_flash' ], 9 );
				}

				// Add back sale badge is added by default, if it was removed.
				if ( 'no' === $this->args['display_sale_badge'] ) {
					add_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_loop_sale_flash', 10 );
				}
			}

			/**
			 * Add special class for the single product images wrapper.
			 *
			 * @access public
			 * @since 3.2
			 * @param string $classes The single product images wrapper classes.
			 * @return string The filtered classes.
			 */
			public function add_single_product_images_wrapper_classes( $classes ) {
				$classes .= ' avada-product-images-element';
				$classes  = str_replace( 'avada-product-images-global', '', $classes );

				if ( 'avada' === $this->args['product_images_layout'] ) {
					$classes .= ' avada-product-images-thumbnails-' . esc_attr( $this->args['thumbnail_position'] );
				}

				return $classes;
			}

			/**
			 * Set the product images layout.
			 * class-avada-woocommerce.php uses same hook for the Global Option.
			 *
			 * @access public
			 * @since 3.2
			 * @param string $layout The product images layout.
			 * @return string The filtered layout.
			 */
			public function product_images_layout( $layout ) {
				return $this->args['product_images_layout'];
			}

			/**
			 * Set the number of product thumbnails.
			 * class-avada-woocommerce.php uses same hook for the Global Option.
			 *
			 * @access public
			 * @since 3.2
			 * @param int $columns The amount of columns.
			 * @return int The filtered amount of columns.
			 */
			public function product_thumbnails_columns( $columns ) {
				return $this->args['thumbnail_columns'];
			}

			/**
			 * Builds HTML for Woo product images.
			 *
			 * @static
			 * @access public
			 * @since 3.2
			 * @return string
			 */
			public function get_images() {
				$content = '';
				ob_start();
				do_action( 'woocommerce_before_single_product_summary' );
				$content .= ob_get_clean();

				return apply_filters( 'fusion_woo_component_content', $content, $this->shortcode_handle, $this->args );
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
					'class'                   => 'fusion-woo-product-images fusion-woo-product-images-' . $this->counter,
					'style'                   => '',
					'data-type'               => $this->product ? esc_attr( $this->product->get_type() ) : false,
					'data-zoom_enabled'       => 'yes' === $this->args['product_images_zoom'] ? 1 : 0,
					'data-photoswipe_enabled' => 'woocommerce' === $this->args['product_images_layout'] ? 1 : 0,
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( '' !== $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.0
			 * @return string
			 */
			protected function get_styles() {
				$this->base_selector = '.fusion-body .fusion-woo-product-images-' . $this->counter;
				$this->dynamic_css   = [];

				$this->add_css_property( $this->base_selector . ' .woocommerce-product-gallery', 'max-width', fusion_library()->sanitize->get_value_with_unit( $this->args['product_images_width'] ) );

				if ( ( 'right' === $this->args['thumbnail_position'] || 'left' === $this->args['thumbnail_position'] ) ) {
					$this->add_css_property( $this->base_selector . ' .avada-product-gallery .flex-control-thumbs', 'width', fusion_library()->sanitize->get_value_with_unit( $this->args['thumbnail_column_width'], '%' ) );
				}

				if ( ! $this->is_default( 'margin_top' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-top', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_top'] ) );
				}
				if ( ! $this->is_default( 'margin_right' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-right', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_right'] ) );
				}
				if ( ! $this->is_default( 'margin_bottom' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_bottom'] ) );
				}
				if ( ! $this->is_default( 'margin_left' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-left', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_left'] ) );
				}

				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
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
					Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-product-images.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-product-images.min.css' );
				}

				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/woo-product-images.min.css' );
			}

			/**
			 * Load the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {
				if ( class_exists( 'Avada' ) ) {
					$js_folder_suffix = FUSION_BUILDER_DEV_MODE ? '/assets/js' : '/assets/min/js';
					$js_folder_url    = Avada::$template_dir_url . $js_folder_suffix;
					$js_folder_path   = Avada::$template_dir_path . $js_folder_suffix;
					$version          = Avada::get_theme_version();

					Fusion_Dynamic_JS::enqueue_script(
						'avada-woo-product-images',
						$js_folder_url . '/general/avada-woo-product-images.js',
						$js_folder_path . '/general/avada-woo-product-images.js',
						[ 'jquery', 'fusion-lightbox', 'jquery-flexslider' ],
						$version,
						true
					);
				}
			}
		}
	}

	new FusionTB_Woo_Product_Images();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.2
 */
function fusion_component_woo_product_images() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Product_Images',
			[
				'name'                    => esc_attr__( 'Woo Product Images', 'fusion-builder' ),
				'shortcode'               => 'fusion_tb_woo_product_images',
				'icon'                    => 'fusiona-woo-product-images',
				'component'               => true,
				'templates'               => [ 'content' ],
				'components_per_template' => 1,
				'params'                  => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Product Images Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the layout for your product images.', 'fusion-builder' ),
						'param_name'  => 'product_images_layout',
						'default'     => 'avada',
						'value'       => [
							''            => esc_attr__( 'Default', 'fusion-builder' ),
							'avada'       => esc_attr__( 'Avada', 'fusion-builder' ),
							'woocommerce' => esc_attr__( 'WooCommerce', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_product_images',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Product Images Zoom', 'fusion-builder' ),
						'description' => __( 'Turn on to enable the WooCommerce product images zoom feature. <strong>IMPORTANT NOTE:</strong> Every product image you use must be larger than the product images container for zoom to work correctly. <a href="https://theme-fusion.com/documentation/avada/woocommerce-single-product-gallery/" target="_blank">See this post for more information.</a>', 'fusion-builder' ),
						'param_name'  => 'product_images_zoom',
						'default'     => '',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_product_images',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Product Images Max Width', 'fusion-builder' ),
						'description' => __( 'Controls the max width of the single product page image gallery. For the image gallery zoom feature to work, the images you upload must be larger than the gallery size you select for this option. <strong>IMPORTANT NOTE:</strong> When this option is changed, you may need to adjust the Single Product Image size setting in WooCommerce Settings to make sure that one is larger and also regenerate thumbnails. <a href="https://theme-fusion.com/documentation/avada/woocommerce-single-product-gallery/" target="_blank">See this post for more information.</a><br/>', 'fusion-builder' ),
						'param_name'  => 'product_images_width',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Product Images Thumbnail Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the position of the product images thumbnails with respect to the gallery images.', 'fusion-builder' ),
						'param_name'  => 'thumbnail_position',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => '',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'top'    => esc_attr__( 'Top', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
							'bottom' => esc_attr__( 'Bottom', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'product_images_layout',
								'value'    => 'avada',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_product_images',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Product Images Thumbnails', 'fusion-builder' ),
						'description' => __( 'Controls the number of columns of the product images thumbnails. In order to avoid blurry thumbnails, make sure the Product Thumbnails size setting in WooCommerce Settings is large enough. It has to be at least WooCommerce Product Gallery Size setting divided by this number of columns.', 'fusion-builder' ),
						'param_name'  => 'thumbnail_columns',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'min'         => '1',
						'max'         => '6',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'woocommerce_gallery_thumbnail_columns' ),
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_product_images',
							'ajax'     => true,
						],
						'dependency'  => [
							[
								'element'  => 'thumbnail_position',
								'value'    => 'right',
								'operator' => '!=',
							],
							[
								'element'  => 'thumbnail_position',
								'value'    => 'left',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Product Images Thumbnail Column Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the width of the left/right column of product images thumbnails as a percentage of the full gallery width.', 'fusion-builder' ),
						'param_name'  => 'thumbnail_column_width',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'min'         => '1',
						'max'         => '100',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'woocommerce_product_images_thumbnail_column_width' ),
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_product_images',
							'ajax'     => true,
						],
						'dependency'  => [
							[
								'element'  => 'thumbnail_position',
								'value'    => 'top',
								'operator' => '!=',
							],
							[
								'element'  => 'thumbnail_position',
								'value'    => 'bottom',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Display Out of Stock Badge', 'fusion-builder' ),
						'description' => esc_attr__( 'Turn on to enable the WooCommerce out of stock badge.', 'fusion-builder' ),
						'param_name'  => 'display_outofstock_badge',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_product_images',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Display Sale Badge', 'fusion-builder' ),
						'description' => esc_attr__( 'Turn on to enable the WooCommerce sale badge.', 'fusion-builder' ),
						'param_name'  => 'display_sale_badge',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_product_images',
							'ajax'     => true,
						],
					],
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
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-woo-images',
					],
				],
				'callback'                => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_product_images',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_product_images' );

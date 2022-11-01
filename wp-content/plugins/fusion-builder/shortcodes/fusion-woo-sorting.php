<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( fusion_is_element_enabled( 'fusion_woo_sorting' ) && class_exists( 'WooCommerce' ) ) {

	if ( ! class_exists( 'FusionSC_WooSorting' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionSC_WooSorting extends Fusion_Element {

			/**
			 * The counter.
			 *
			 * @access private
			 * @since 3.3
			 * @var int
			 */
			private $element_counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $args;

			/**
			 * Shortcode name.
			 *
			 * @access public
			 * @since 3.3
			 * @var string
			 */
			public $shortcode_name;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.3
			 */
			public function __construct() {
				parent::__construct();
				$this->shortcode_name = 'fusion_woo_sorting';
				add_filter( 'fusion_attr_' . $this->shortcode_name . '-shortcode', [ $this, 'attr' ] );

				add_shortcode( $this->shortcode_name, [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_' . $this->shortcode_name, [ $this, 'ajax_render' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'hide_on_mobile'          => fusion_builder_default_visibility( 'string' ),
					'class'                   => '',
					'id'                      => '',
					'margin_bottom'           => '',
					'margin_left'             => '',
					'margin_right'            => '',
					'margin_top'              => '',
					'sort_options'            => 'default,name,price,date,popularity,rating',
					'elements'                => 'orderby,count,view',
					'number_products'         => $fusion_settings->get( 'woo_items' ),
					'dropdown_bg_color'       => $fusion_settings->get( 'woo_dropdown_bg_color' ),
					'dropdown_hover_bg_color' => $fusion_settings->get( 'woo_dropdown_bg_color' ),
					'dropdown_text_color'     => $fusion_settings->get( 'woo_dropdown_text_color' ),
					'dropdown_border_color'   => $fusion_settings->get( 'woo_dropdown_border_color' ),
				];
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public static function get_element_extras() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'box_design'        => $fusion_settings->get( 'woocommerce_product_box_design', false, 'classic' ),
					'load_more_text'    => apply_filters( 'avada_load_more_products_name', esc_attr__( 'Load More Products', 'fusion-builder' ) ),
					'visibility_medium' => $fusion_settings->get( 'visibility_medium' ),
				];
			}

			/**
			 * Maps settings to extra variables.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public static function settings_to_extras() {
				return [
					'woocommerce_product_box_design' => 'box_design',
				];
			}

			/**
			 * Gets the query data.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @param array $args An array of args.
			 * @return void
			 */
			public function ajax_render( $args ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
				$live_request = false;
				$return_data  = [];

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$this->args   = wp_unslash( $_POST['model']['params'] ); // phpcs:ignore WordPress.Security
					$live_request = true;
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				$return_data['output'] = $this->get_sorting_elements( $live_request );
				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Render the shortcode.
			 *
			 * @access public
			 * @since 3.3
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output
			 */
			public function render( $args, $content = '' ) {
				$this->defaults = self::get_element_defaults();

				$this->args = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, $this->shortcode_name );

				$html  = '<div ' . FusionBuilder::attributes( $this->shortcode_name . '-shortcode' ) . '>';
				$html .= $this->get_sorting_elements();
				$html .= $this->get_styles();
				$html .= '</div>';

				$this->element_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_woo_sorting_content', $html, $args );
			}

			/**
			 * Builds HTML for sorting elements.
			 *
			 * @access public
			 * @since 3.3
			 * @param bool $is_live If it's live editor request or not.
			 * @return array
			 */
			public function get_sorting_elements( $is_live = false ) {
				global $product;

				$options      = explode( ',', $this->args['elements'] );
				$content      = '';
				$query_string = '';
				if ( isset( $_SERVER['QUERY_STRING'] ) ) {
					$query_string = sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) );
					parse_str( $query_string, $params );
					$query_string = '?' . $query_string;
				}

				$per_page = $this->args['number_products'];

				// Use "relevance" as default if we're on the search page.
				$default_orderby = is_search() ? 'relevance' : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', '' ) );

				$pob = ! empty( $params['product_orderby'] ) ? $params['product_orderby'] : $default_orderby;

				if ( ! empty( $params['product_order'] ) ) {
					$po = $params['product_order'];
				} else {
					switch ( $pob ) {
						case 'default':
						case 'menu_order':
						case 'price':
						case 'name':
							$po = 'asc';
							break;
						default:
							$po = 'desc';
							break;
					}
				}

				$order_string = esc_attr__( 'Default Order', 'fusion-builder' );

				switch ( $pob ) {
					case 'date':
						$order_string = esc_attr__( 'Date', 'fusion-builder' );
						break;
					case 'price':
					case 'price-desc':
						$order_string = esc_attr__( 'Price', 'fusion-builder' );
						break;
					case 'popularity':
						$order_string = esc_attr__( 'Popularity', 'fusion-builder' );
						break;
					case 'rating':
						$order_string = esc_attr__( 'Rating', 'fusion-builder' );
						break;
					case 'name':
						$order_string = esc_attr__( 'Name', 'fusion-builder' );
						break;
					case 'relevance':
						$order_string = esc_attr__( 'Relevance', 'fusion-builder' );
						break;
				}

				$pc = ! empty( $params['product_count'] ) ? $params['product_count'] : $per_page;

				$sort_options = explode( ',', $this->args['sort_options'] );

				foreach ( $options as $index => $option ) {
					switch ( $option ) {
						case 'orderby':
							ob_start(); ?>

							<div class="orderby-order-container">
								<ul class="orderby order-dropdown">
									<li>
										<span class="current-li">
											<span class="current-li-content">
												<?php /* translators: Name, Price, Date etc. */ ?>
												<a aria-haspopup="true"><?php printf( esc_html__( 'Sort by %s', 'fusion-builder' ), '<strong>' . esc_attr( $order_string ) . '</strong>' ); ?></a>
											</span>
										</span>
										<ul>
											<?php if ( is_search() ) : ?>
												<li class="<?php echo ( 'relevance' === $pob ) ? 'current' : ''; ?>">
													<?php /* translators: Relevance, Price, Date etc. */ ?>
													<a href="<?php echo esc_url_raw( fusion_add_url_parameter( $query_string, 'product_orderby', 'relevance' ) ); ?>"><?php printf( esc_html__( 'Sort by %s', 'fusion-builder' ), '<strong>' . esc_attr__( 'Relevance', 'fusion-builder' ) . '</strong>' ); ?></a>
												</li>
											<?php endif; ?>
											<?php if ( 'menu_order' === apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) ) ) : ?>
												<li class="<?php echo ( 'menu_order' === $pob ) ? 'current' : ''; ?>">
													<?php /* translators: Name, Price, Date etc. */ ?>
													<a href="<?php echo esc_url_raw( fusion_add_url_parameter( $query_string, 'product_orderby', 'default' ) ); ?>"><?php printf( esc_html__( 'Sort by %s', 'fusion-builder' ), '<strong>' . esc_attr__( 'Default Order', 'fusion-builder' ) . '</strong>' ); ?></a>
												</li>
											<?php endif; ?>
											<?php if ( $this->is_sorting_enabled( 'name' ) ) : ?>
											<li class="<?php echo ( 'name' === $pob ) ? 'current' : ''; ?>">
												<?php /* translators: Name, Price, Date etc. */ ?>
												<a href="<?php echo esc_url_raw( fusion_add_url_parameter( $query_string, 'product_orderby', 'name' ) ); ?>"><?php printf( esc_html__( 'Sort by %s', 'fusion-builder' ), '<strong>' . esc_attr__( 'Name', 'fusion-builder' ) . '</strong>' ); ?></a>
											</li>
											<?php endif; ?>
											<?php if ( $this->is_sorting_enabled( 'price' ) ) : ?>
											<li class="<?php echo ( 'price' === $pob || 'price-desc' === $pob ) ? 'current' : ''; ?>">
												<?php /* translators: Name, Price, Date etc. */ ?>
												<a href="<?php echo esc_url_raw( fusion_add_url_parameter( $query_string, 'product_orderby', 'price' ) ); ?>"><?php printf( esc_html__( 'Sort by %s', 'fusion-builder' ), '<strong>' . esc_attr__( 'Price', 'fusion-builder' ) . '</strong>' ); ?></a>
											</li>
											<?php endif; ?>
											<?php if ( $this->is_sorting_enabled( 'date' ) ) : ?>
											<li class="<?php echo ( 'date' === $pob ) ? 'current' : ''; ?>">
												<?php /* translators: Name, Price, Date etc. */ ?>
												<a href="<?php echo esc_url_raw( fusion_add_url_parameter( $query_string, 'product_orderby', 'date' ) ); ?>"><?php printf( esc_html__( 'Sort by %s', 'fusion-builder' ), '<strong>' . esc_attr__( 'Date', 'fusion-builder' ) . '</strong>' ); ?></a>
											</li>
											<?php endif; ?>
											<?php if ( $this->is_sorting_enabled( 'popularity' ) ) : ?>
											<li class="<?php echo ( 'popularity' === $pob ) ? 'current' : ''; ?>">
												<?php /* translators: Name, Price, Date etc. */ ?>
												<a href="<?php echo esc_url_raw( fusion_add_url_parameter( $query_string, 'product_orderby', 'popularity' ) ); ?>"><?php printf( esc_html__( 'Sort by %s', 'fusion-builder' ), '<strong>' . esc_attr__( 'Popularity', 'fusion-builder' ) . '</strong>' ); ?></a>
											</li>
											<?php endif; ?>

											<?php if ( $this->is_sorting_enabled( 'rating' ) && 'no' !== get_option( 'woocommerce_enable_reviews' ) && 'no' !== get_option( 'woocommerce_enable_review_rating' ) ) : ?>
												<li class="<?php echo ( 'rating' === $pob ) ? 'current' : ''; ?>">
													<?php /* translators: Name, Price, Date etc. */ ?>
													<a href="<?php echo esc_url_raw( fusion_add_url_parameter( $query_string, 'product_orderby', 'rating' ) ); ?>"><?php printf( esc_html__( 'Sort by %s', 'fusion-builder' ), '<strong>' . esc_attr__( 'Rating', 'fusion-builder' ) . '</strong>' ); ?></a>
												</li>
											<?php endif; ?>
										</ul>
									</li>
								</ul>

								<ul class="order">
									<?php if ( isset( $po ) ) : ?>
										<?php if ( 'desc' === $po ) : ?>
											<li class="desc"><a aria-label="<?php esc_attr_e( 'Ascending order', 'fusion-builder' ); ?>" aria-haspopup="true" href="<?php echo esc_url_raw( fusion_add_url_parameter( $query_string, 'product_order', 'asc' ) ); ?>"><i class="awb-icon-arrow-down2 icomoon-up" aria-hidden="true"></i></a></li>
										<?php else : ?>
											<li class="asc"><a aria-label="<?php esc_attr_e( 'Descending order', 'fusion-builder' ); ?>" aria-haspopup="true" href="<?php echo esc_url_raw( fusion_add_url_parameter( $query_string, 'product_order', 'desc' ) ); ?>"><i class="awb-icon-arrow-down2" aria-hidden="true"></i></a></li>
										<?php endif; ?>
									<?php endif; ?>
								</ul>
							</div>

							<?php
							$content .= ob_get_clean();
							break;
						case 'count':
							ob_start();
							?>

							<ul class="sort-count order-dropdown">
								<li>
									<span class="current-li">
										<a aria-haspopup="true">
											<?php
											printf(
												/* translators: Number. */
												__( 'Show <strong>%s Products</strong>', 'fusion-builder' ), // phpcs:ignore WordPress.Security.EscapeOutput
												(int) $per_page
											);
											?>
											</a>
										</span>
									<ul>
										<li class="<?php echo ( $pc == $per_page ) ? 'current' : ''; // phpcs:ignore WordPress.PHP.StrictComparisons ?>">
											<a href="<?php echo esc_url_raw( fusion_add_url_parameter( $query_string, 'product_count', $per_page ) ); ?>">
												<?php
												printf(
													/* translators: Number of products. */
													__( 'Show <strong>%s Products</strong>', 'fusion-builder' ), // phpcs:ignore WordPress.Security.EscapeOutput
													(int) $per_page
												);
												?>
											</a>
										</li>
										<li class="<?php echo ( $pc == $per_page * 2 ) ? 'current' : ''; // phpcs:ignore WordPress.PHP.StrictComparisons ?>">
											<a href="<?php echo esc_url_raw( fusion_add_url_parameter( $query_string, 'product_count', $per_page * 2 ) ); ?>">
												<?php
												printf(
													/* translators: Number of products.*/
													__( 'Show <strong>%s Products</strong>', 'fusion-builder' ), // phpcs:ignore WordPress.Security.EscapeOutput
													(int) $per_page * 2
												);
												?>
											</a>
										</li>
										<li class="<?php echo ( $pc == $per_page * 3 ) ? 'current' : ''; // phpcs:ignore WordPress.PHP.StrictComparisons ?>">
											<a href="<?php echo esc_url_raw( fusion_add_url_parameter( $query_string, 'product_count', $per_page * 3 ) ); ?>">
												<?php
												printf(
													/* translators: Number of products.*/
													__( 'Show <strong>%s Products</strong>', 'fusion-builder' ), // phpcs:ignore WordPress.Security.EscapeOutput
													(int) $per_page * 3
												);
												?>
											</a>
										</li>
									</ul>
								</li>
							</ul>

							<?php
							$content .= ob_get_clean();
							break;
						case 'view':
							$product_view = 'grid';
							if ( isset( $_SERVER['QUERY_STRING'] ) ) {
								parse_str( sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ), $params );
								$product_view = ( isset( $params['product_view'] ) ) ? $params['product_view'] : Avada()->settings->get( 'woocommerce_product_view' );
							}
							ob_start();
							?>

							<ul class="fusion-grid-list-view">
								<li class="fusion-grid-view-li<?php echo ( 'grid' === $product_view ) ? ' active-view' : ''; ?>">
									<a class="fusion-grid-view" aria-label="<?php esc_attr_e( 'View as grid', 'fusion-builder' ); ?>" aria-haspopup="true" href="<?php echo esc_url_raw( fusion_add_url_parameter( $query_string, 'product_view', 'grid' ) ); ?>"><i class="awb-icon-grid icomoon-grid" aria-hidden="true"></i></a>
								</li>
								<li class="fusion-list-view-li<?php echo ( 'list' === $product_view ) ? ' active-view' : ''; ?>">
									<a class="fusion-list-view" aria-haspopup="true" aria-label="<?php esc_attr_e( 'View as list', 'fusion-builder' ); ?>" href="<?php echo esc_url_raw( fusion_add_url_parameter( $query_string, 'product_view', 'list' ) ); ?>"><i class="awb-icon-list icomoon-list" aria-hidden="true"></i></a>
								</li>
							</ul>

							<?php
							$content .= ob_get_clean();
							break;
					}
				}

				return $content;
			}

			/**
			 * Check if sorting options enabled.
			 *
			 * @access public
			 * @since 3.3
			 * @param string $name Option Name.
			 * @return boolean
			 */
			public function is_sorting_enabled( $name ) {
				return in_array( $name, explode( ',', $this->args['sort_options'] ), true );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'catalog-ordering fusion-woo-sorting fusion-woo-sorting-' . $this->element_counter,
					]
				);

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;

			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function on_first_render() {
				// Skip if empty.
				if ( null === $this->args || empty( $this->args ) ) {
					return;
				}

				if ( class_exists( 'Avada' ) && class_exists( 'WooCommerce' ) ) {
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
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function add_css_files() {
				if ( class_exists( 'Avada' ) ) {
					Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-sorting.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-sorting.min.css' );
				}
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.3
			 * @return string
			 */
			protected function get_styles() {

				$this->base_selector = '.fusion-woo-sorting.fusion-woo-sorting-' . $this->element_counter;
				$this->dynamic_css   = [];

				$selectors = [
					$this->base_selector,
				];

				// Fix z-index issue.
				$this->add_css_property( $selectors, 'z-index', '100' );
				$this->add_css_property( $selectors, 'position', 'relative' );

				// Margin styles.
				if ( ! $this->is_default( 'margin_top' ) ) {
					$this->add_css_property( $selectors, 'margin-top', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_top'] ) );
				}
				if ( ! $this->is_default( 'margin_right' ) ) {
					$this->add_css_property( $selectors, 'margin-right', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_right'] ) );
				}
				if ( ! $this->is_default( 'margin_bottom' ) ) {
					$this->add_css_property( $selectors, 'margin-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_bottom'] ) );
				} else {
					$this->add_css_property( $selectors, 'margin-bottom', '0px' );
				}
				if ( ! $this->is_default( 'margin_left' ) ) {
					$this->add_css_property( $selectors, 'margin-left', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_left'] ) );
				}

				$selectors = [
					$this->base_selector . ' .order-dropdown .current-li',
					$this->base_selector . ' .order-dropdown ul li a:not(:hover)',
					$this->base_selector . '.catalog-ordering .order li a:not(:hover)',
					$this->base_selector . ' .fusion-grid-list-view li:not(.active-view):not(:hover)',
				];

				// Dropdown bg color.
				if ( ! $this->is_default( 'dropdown_bg_color' ) ) {
					$this->add_css_property( $selectors, 'background-color', fusion_library()->sanitize->color( $this->args['dropdown_bg_color'] ) );
				}

				$selectors = [
					$this->base_selector . ' .order-dropdown ul li a:hover',
					$this->base_selector . '.catalog-ordering .order li a:hover',
					$this->base_selector . ' .fusion-grid-list-view li:hover',
					$this->base_selector . ' .fusion-grid-list-view li.active-view',
				];

				// Dropdown hover / active bg color.
				if ( ! $this->is_default( 'dropdown_hover_bg_color' ) ) {
					$this->add_css_property( $selectors, 'background-color', fusion_library()->sanitize->color( $this->args['dropdown_hover_bg_color'] ) );
				}

				$selectors = [
					$this->base_selector . ' .order-dropdown',
					$this->base_selector . ' .order-dropdown a',
					$this->base_selector . ' .order-dropdown ul li a',
					$this->base_selector . ' .order-dropdown a:hover',
					$this->base_selector . ' .order-dropdown > li:after',
					$this->base_selector . ' .order-dropdown ul li a:hover',
					$this->base_selector . '.catalog-ordering .order li a',
					$this->base_selector . ' .fusion-grid-list-view a',
					$this->base_selector . ' .fusion-grid-list-view li:hover',
					$this->base_selector . ' .fusion-grid-list-view li.active-view a i',
				];

				// Dropdown text color.
				if ( ! $this->is_default( 'dropdown_text_color' ) ) {
					$this->add_css_property( $selectors, 'color', fusion_library()->sanitize->color( $this->args['dropdown_text_color'] ) );
				}

				$selectors = [
					$this->base_selector . ' .order-dropdown > li:after',
					$this->base_selector . ' .order-dropdown .current-li',
					$this->base_selector . ' .order-dropdown ul li a',
					$this->base_selector . '.catalog-ordering .order li a',
					$this->base_selector . ' .fusion-grid-list-view',
					$this->base_selector . ' .fusion-grid-list-view li',
				];

				// Dropdown border color.
				if ( ! $this->is_default( 'dropdown_border_color' ) ) {
					$this->add_css_property( $selectors, 'border-color', fusion_library()->sanitize->color( $this->args['dropdown_border_color'] ) );
				}

				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
			}
		}
	}

	new FusionSC_WooSorting();

}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_woo_sorting() {
	if ( class_exists( 'WooCommerce' ) ) {

		$fusion_settings   = awb_get_fusion_settings();
		$lookup_table_link = admin_url( 'admin.php?page=wc-status&tab=tools' );

		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionSC_WooSorting',
				[
					'name'      => esc_attr__( 'Woo Sorting', 'fusion-builder' ),
					'shortcode' => 'fusion_woo_sorting',
					'icon'      => 'fusiona-sorting-boxes',
					'help_url'  => 'https://theme-fusion.com/documentation/avada/elements/woocommerce-product-carousel-element/',
					'params'    => [
						[
							'type'        => 'multiple_select',
							'param_name'  => 'sort_options',
							'choices'     => [
								'name'       => esc_html__( 'Name', 'fusion-builder' ),
								'price'      => esc_html__( 'Price', 'fusion-builder' ),
								'date'       => esc_html__( 'Date', 'fusion-builder' ),
								'popularity' => esc_html__( 'Popularity', 'fusion-builder' ),
								'rating'     => esc_html__( 'Rating', 'fusion-builder' ),
							],
							'default'     => 'name,price,date,popularity,rating',
							'heading'     => esc_html__( 'Sorting Options', 'fusion-builder' ),
							/* translators: WooCommerce lookup table link. */
							'description' => sprintf( __( 'Select sorting options that you want to be displayed in the sorting list box. <strong>NOTE:</strong> If Order by Price is not working, please regenerate the Product Lookup Tables <a href="%s" target="_blank">here</a>.', 'fusion-builder' ), $lookup_table_link ),
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_woo_sorting',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'connected_sortable',
							'heading'     => esc_attr__( 'Sorting Elements', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose the order of sorting elements.', 'fusion-builder' ),
							'param_name'  => 'elements',
							'default'     => 'orderby,count,view',
							'choices'     => [
								'orderby' => esc_attr__( 'Product Order By', 'fusion-builder' ),
								'count'   => esc_attr__( 'Product Count', 'fusion-builder' ),
								'view'    => esc_attr__( 'Product View', 'fusion-builder' ),
							],
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_woo_sorting',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Product Count Selection', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the number of product count to display. For best results it should equal the number of posts set in the woo archives element.', 'fusion-builder' ),
							'param_name'  => 'number_products',
							'min'         => '0',
							'max'         => '50',
							'step'        => '1',
							'value'       => $fusion_settings->get( 'woo_items' ),
							'default'     => '',
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_woo_sorting',
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
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Dropdown Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the background color for the dropdowns.', 'fusion-builder' ),
							'param_name'  => 'dropdown_bg_color',
							'value'       => '',
							'default'     => $fusion_settings->get( 'woo_dropdown_bg_color' ),
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Dropdown Hover / Active Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the background color for the dropdowns hover / active states.', 'fusion-builder' ),
							'param_name'  => 'dropdown_hover_bg_color',
							'value'       => '',
							'default'     => $fusion_settings->get( 'woo_dropdown_bg_color' ),
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Dropdown Text Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the text color for the dropdowns.', 'fusion-builder' ),
							'param_name'  => 'dropdown_text_color',
							'value'       => '',
							'default'     => $fusion_settings->get( 'woo_dropdown_text_color' ),
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Dropdown Border Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the border color for the dropdowns.', 'fusion-builder' ),
							'param_name'  => 'dropdown_border_color',
							'value'       => '',
							'default'     => $fusion_settings->get( 'woo_dropdown_border_color' ),
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
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
					'callback'  => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_woo_sorting',
						'ajax'     => true,
					],
				]
			)
		);
	}
}
add_action( 'wp_loaded', 'fusion_element_woo_sorting' );

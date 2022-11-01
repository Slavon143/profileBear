<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( class_exists( 'WooCommerce' ) ) {

	if ( ! class_exists( 'FusionSC_WooCartTable' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionSC_WooCartTable extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $args;

			/**
			 * An array of available columns
			 *
			 * @since 3.3
			 * @var array
			 */
			private $columns = [];

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.3
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.3
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_woo-cart-table-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_woo-cart-table-wrapper', [ $this, 'wrapper_attr' ] );
				add_shortcode( 'fusion_woo_cart_table', [ $this, 'render' ] );

				$image = wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' );

				$this->columns = [
					'name'          => [
						'title'         => esc_attr__( 'Product', 'fusion-builder' ),
						'method'        => 'get_product_name_row',
						'dummy_content' => '<div class="fusion-product-name-wrapper">
						<span class="product-thumbnail"><img  style="max-width:120px;" src="' . $image . '"></span>
						<div class="product-info"><a class="product-title" href="#sample_product">Sample Product</a></div>
						</div>',
					],
					'price'         => [
						'title'         => esc_attr__( 'Price', 'fusion-builder' ),
						'method'        => 'get_price_row',
						'dummy_content' => '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>195.00</bdi></span>',
					],
					'quantity'      => [
						'title'         => esc_attr__( 'Quantity', 'fusion-builder' ),
						'method'        => 'get_quantity_row',
						'dummy_content' => '<div class="quantity buttons_added"><input type="button" value="-" class="minus">
						<label class="screen-reader-text" for="quantity_60042410012e5">Product #2 quantity</label>
						<input type="number" id="quantity_60042410012e5" class="input-text qty text" step="1" min="0" max="" name="" value="1" title="Qty" size="4" placeholder="" inputmode="numeric"><input type="button" value="+" class="plus">
						</div>',
					],
					'subtotal'      => [
						'title'         => esc_attr__( 'Subtotal', 'fusion-builder' ),
						'method'        => 'get_subtotal_row',
						'dummy_content' => '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>195.00</bdi></span>',
					],
					'remove'        => [
						'title'         => '',
						'method'        => 'get_remove_icon_row',
						'dummy_content' => '<a href="#remove" class="remove">×</a>',
					],
					'product_title' => [
						'title'         => esc_attr__( 'Title', 'fusion-builder' ),
						'method'        => 'get_product_title_row',
						'dummy_content' => '<div class="product-info"><a href="#sample_product">Sample Product</a></div>',
					],
					'product_image' => [
						'title'         => '',
						'method'        => 'get_product_image_row',
						'dummy_content' => '<span class="product-thumbnail"><img src="' . $image . '" width="100" height="100"></span>',
					],
				];

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_fusion_get_woo_cart_table', [ $this, 'ajax_query' ] );

				// woocommerce_get_cart_page_id.
				add_filter( 'woocommerce_get_cart_page_id', [ $this, 'simulate_cart_page' ] );

				// Empty cart.
				add_action( 'wp_loaded', [ $this, 'empty_cart' ] );

			}

			/**
			 * Substitute cart id when Woo Cart Table or Woo Cart Totals elements is used on the page
			 *
			 * @param int $page The page ID.
			 * @return int
			 */
			public function simulate_cart_page( $page ) {

				global $post;

				if ( is_admin() && ! fusion_doing_ajax() ) {
					return $page;
				}

				if ( empty( $post ) && fusion_doing_ajax() && isset( $_POST['fusion_load_nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					if ( isset( $_POST['post_id'] ) && ! empty( $_POST['post_id'] ) ) { // phpcs:ignore WordPress.Security
						$post_id = $_POST['post_id']; // phpcs:ignore WordPress.Security
						$post    = get_post( $post_id );
						if ( isset( $post->post_content ) && ! empty( $post->post_content ) &&
							( strpos( $post->post_content, 'fusion_woo_cart_table' ) > 0 || strpos( $post->post_content, 'fusion_woo_cart_totals' ) > 0 ) ) {
							wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );
						}
					}
				}
				if ( isset( $post->ID ) ) {
					if ( $page === $post->ID || empty( $post->post_content ) ) {
						return $page;
					}
					if ( strpos( $post->post_content, 'fusion_woo_cart_table' ) > 0 || strpos( $post->post_content, 'fusion_woo_cart_totals' ) > 0 ) {
						$page = $post->ID;
					}
				} else {
					if ( isset( $_POST['_wp_http_referer'] ) && isset( $_POST['cart'] ) && is_array( $_POST['cart'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
						$id = url_to_postid( $_POST['_wp_http_referer'] ); // phpcs:ignore WordPress.Security
						if ( ! empty( $id ) ) {
							$post = get_post( $id );
							if ( isset( $post->post_content ) && ! empty( $post->post_content ) &&
								( strpos( $post->post_content, 'fusion_woo_cart_table' ) > 0 || strpos( $post->post_content, 'fusion_woo_cart_totals' ) > 0 ) ) {
									return $id;
							}
						}
					}
				}

				return $page;
			}


			/**
			 * Gets the query data.
			 *
			 * @access public
			 * @since 3.3
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_query( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
				$this->args = $_POST['model']['params']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$html       = $this->generate_table_content();

				echo wp_json_encode( $html );
				wp_die();
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					// Element margin.
					'margin_top'                       => '',
					'margin_right'                     => '',
					'margin_bottom'                    => '',
					'margin_left'                      => '',

					// Cell padding.
					'cell_padding_top'                 => '',
					'cell_padding_right'               => '',
					'cell_padding_bottom'              => '',
					'cell_padding_left'                => '',

					'table_cell_backgroundcolor'       => '',
					'heading_cell_backgroundcolor'     => '',

					// Heading styles.
					'heading_color'                    => '',
					'fusion_font_family_heading_font'  => '',
					'fusion_font_variant_heading_font' => '',
					'heading_font_size'                => '',
					'heading_text_transform'           => '',
					'heading_line_height'              => '',
					'heading_letter_spacing'           => '',

					// Text styles.
					'text_color'                       => '',
					'fusion_font_family_text_font'     => '',
					'fusion_font_variant_text_font'    => '',
					'text_font_size'                   => '',
					'text_text_transform'              => '',
					'text_line_height'                 => '',
					'text_letter_spacing'              => '',

					'border_color'                     => '',

					'hide_on_mobile'                   => fusion_builder_default_visibility( 'string' ),
					'class'                            => '',
					'id'                               => '',
					'animation_type'                   => '',
					'animation_direction'              => 'down',
					'animation_speed'                  => '0.1',
					'animation_offset'                 => $fusion_settings->get( 'animation_offset' ),

					'table_columns'                    => 'name,price,quantity,subtotal,remove',
					'coupons_visibility'               => 'show',
					'update_cart_button_visibility'    => 'show',
					'table_header_visibility'          => 'show',
					'empty_cart_message'               => '',
				];
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
				$this->args     = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_woo_cart_table' );

				ob_start();
				if ( is_object( WC()->cart ) && WC()->cart->is_empty() && ! fusion_is_preview_frame() ) {
					$content = apply_filters( 'fusion_shortcode_content', $content, 'fusion_woo_cart_table', $args );
					$html    = preg_replace( '!^<p>(.*?)</p>$!i', '$1', trim( $content ) );
				} else {
					?>
					<div <?php echo FusionBuilder::attributes( 'woo-cart-table-wrapper' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<form class="woocommerce-cart-form woocommerce" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
					<table <?php echo FusionBuilder::attributes( 'woo-cart-table-shortcode' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> cellspacing="0">
						<?php echo $this->generate_table_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php do_action( 'woocommerce_cart_contents' ); ?>
						<tr class="avada-cart-actions">
							<td class="actions">
								<?php if ( 'show' === $this->args['coupons_visibility'] ) { ?>
									<div class="coupon">
										<label for="coupon_code"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
										<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" /> <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?></button>
										<?php do_action( 'woocommerce_cart_coupon' ); ?>
									</div>
								<?php } ?>
								<?php do_action( 'woocommerce_cart_actions' ); ?>
								<button type="submit" class="button" name="update_cart">
								<?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>
								<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
							</td>
						</tr>
						<?php do_action( 'woocommerce_after_cart_contents' ); ?>
					</table>

					</form>
					</div>
					<?php
					$html  = ob_get_clean();
					$html .= $this->get_styles();
				}

				$this->on_render();
				$this->counter++;
				return apply_filters( 'fusion_element_cart_table_content', $html, $args );
			}

			/**
			 * Generates table content
			 *
			 * @return string
			 */
			public function generate_table_content() {

				ob_start();

				if ( 'show' === $this->args['table_header_visibility'] ) {
					echo $this->get_table_header(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}

				if ( fusion_is_preview_frame() || fusion_doing_ajax() ) {
					if ( ! is_object( WC()->cart ) || WC()->cart->is_empty() ) {
						echo $this->generate_cart_dummy_products(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						return ob_get_clean();
					}
				}
				?>
				<tbody>
				<?php
				$columns = explode( ',', $this->args['table_columns'] );
				if ( is_object( WC()->cart ) ) {
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
						$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

						if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
							$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
							?>
							<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
							<?php

							foreach ( $columns as $column ) :
								$method = $this->columns[ $column ]['method'];
								if ( is_callable( [ $this, $method ] ) ) {
									echo $this->$method( $_product, $cart_item_key, $cart_item, $product_permalink ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
							endforeach;
							?>
							</tr>
							<?php
						}
					}
				}
				?>
				</tbody>
				<?php
				return ob_get_clean();
			}


			/**
			 * Generates cart dummy products when a cart is empty
			 *
			 * @return string
			 */
			public function generate_cart_dummy_products() {
				ob_start();
				$columns = explode( ',', $this->args['table_columns'] );
				for ( $i = 0; $i < 2; $i++ ) {
					?>
				<tr>
					<?php
					foreach ( $columns as $column ) :
						echo '<td class="product-' . $column . '">' . $this->columns[ $column ]['dummy_content'] . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						endforeach;
					?>
				</tr>
					<?php
				}
				return ob_get_clean();
			}


			/**
			 * Generates product image
			 *
			 * @param object $_product          The product object.
			 * @param string $cart_item_key     Cart item key.
			 * @param string $cart_item         Cart item.
			 * @param string $product_permalink The product permalink.
			 * @return string
			 */
			public function get_product_image( $_product, $cart_item_key, $cart_item, $product_permalink ) {
				ob_start();
				?>
					<span class="product-thumbnail">
						<?php
						$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

						if ( ! $product_permalink ) {
							echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} else {
							printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
					</span>
				<?php
				return ob_get_clean();
			}


			/**
			 * Generates product title
			 *
			 * @param object $_product          The product object.
			 * @param string $cart_item_key     Cart item key.
			 * @param string $cart_item         Cart item.
			 * @param string $product_permalink The product permalink.
			 * @return string
			 */
			public function get_product_title( $_product, $cart_item_key, $cart_item, $product_permalink ) {
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

				ob_start();
				?>
					<div class="product-info">
						<?php
						if ( ! $product_permalink ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
						} else {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
						}

						do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

						// Meta data.
						echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

						// Backorder notification.
						if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
						}
						?>
					</div>
				<?php
				return ob_get_clean();
			}


			/**
			 * Generates product information row
			 *
			 * @param object $_product          The product object.
			 * @param string $cart_item_key     Cart item key.
			 * @param string $cart_item         Cart item.
			 * @param string $product_permalink The product permalink.
			 * @return string
			 */
			public function get_product_title_row( $_product, $cart_item_key, $cart_item, $product_permalink ) {
				ob_start();
				?>
				<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
				<div class="fusion-product-name-wrapper">

					<?php echo $this->get_product_title( $_product, $cart_item_key, $cart_item, $product_permalink ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				</div>
				</td>
				<?php
				return ob_get_clean();
			}


			/**
			 * Generates product information row
			 *
			 * @param object $_product          The product object.
			 * @param string $cart_item_key     Cart item key.
			 * @param string $cart_item         Cart item.
			 * @param string $product_permalink Cart permalink.
			 * @return string
			 */
			public function get_product_image_row( $_product, $cart_item_key, $cart_item, $product_permalink ) {
				ob_start();
				?>
				<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
				<div class="fusion-product-name-wrapper">

					<?php echo $this->get_product_image( $_product, $cart_item_key, $cart_item, $product_permalink ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				</div>
				</td>
				<?php
				return ob_get_clean();
			}


			/**
			 * Generates product information row
			 *
			 * @param object $_product          The product.
			 * @param string $cart_item_key     Cart item key.
			 * @param string $cart_item         Cart item.
			 * @param string $product_permalink The product permalink.
			 * @return string
			 */
			public function get_product_name_row( $_product, $cart_item_key, $cart_item, $product_permalink ) {
				ob_start();
				?>
				<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
				<div class="fusion-product-name-wrapper">

					<?php echo $this->get_product_image( $_product, $cart_item_key, $cart_item, $product_permalink ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo $this->get_product_title( $_product, $cart_item_key, $cart_item, $product_permalink ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				</div>
				</td>
				<?php
				return ob_get_clean();
			}


			/**
			 * Generates price
			 *
			 * @param object $_product          The product object.
			 * @param string $cart_item_key     Cart item key.
			 * @param string $cart_item         Cart item.
			 * @param string $product_permalink The product permalink.
			 * @return string
			 */
			public function get_price_row( $_product, $cart_item_key, $cart_item, $product_permalink ) {
				ob_start();
				?>
				<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
					<?php
					$price = is_object( WC()->cart ) ? WC()->cart->get_product_price( $_product ) : '';
					echo apply_filters( 'woocommerce_cart_item_price', $price, $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</td>
				<?php
				return ob_get_clean();
			}


			/**
			 * Generates quantity
			 *
			 * @param object $_product          The product object.
			 * @param string $cart_item_key     Cart item key.
			 * @param string $cart_item         Cart item.
			 * @param string $product_permalink The product permalink.
			 * @return string
			 */
			public function get_quantity_row( $_product, $cart_item_key, $cart_item, $product_permalink ) {
				ob_start();
				?>
				<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
					<?php
					if ( $_product->is_sold_individually() ) {
						$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
					} else {
						$product_quantity = woocommerce_quantity_input(
							[
								'input_name'   => "cart[{$cart_item_key}][qty]",
								'input_value'  => $cart_item['quantity'],
								'max_value'    => $_product->get_max_purchase_quantity(),
								'min_value'    => '0',
								'product_name' => $_product->get_name(),
							],
							$_product,
							false
						);
					}

					echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</td>
				<?php
				return ob_get_clean();
			}


			/**
			 * Generates subtotal
			 *
			 * @param object $_product          The product objecct.
			 * @param string $cart_item_key     Cart item key.
			 * @param string $cart_item         Cart item.
			 * @param string $product_permalink The product permalink.
			 * @return string
			 */
			public function get_subtotal_row( $_product, $cart_item_key, $cart_item, $product_permalink ) {
				ob_start();
				?>
				<td class="product-subtotal" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
					<?php
					echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</td>
				<?php
				return ob_get_clean();
			}


			/**
			 * Generates remove icon
			 *
			 * @param object $_product          The product objecct.
			 * @param string $cart_item_key     Cart item key.
			 * @param string $cart_item         Cart item.
			 * @param string $product_permalink The product permalink.
			 * @return string
			 */
			public function get_remove_icon_row( $_product, $cart_item_key, $cart_item, $product_permalink ) {
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

				ob_start();
				?>
				<td class="product-remove">
					<?php
					echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'woocommerce_cart_item_remove_link',
						sprintf(
							'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
							esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
							esc_html__( 'Remove this item', 'woocommerce' ),
							esc_attr( $product_id ),
							esc_attr( $_product->get_sku() )
						),
						$cart_item_key
					);
					?>
				</td>
				<?php
				return ob_get_clean();
			}


			/**
			 * Generates table headers
			 *
			 * @return string
			 */
			public function get_table_header() {
				$columns = explode( ',', $this->args['table_columns'] );
				ob_start();
				?>
					<thead>
					<tr>
						<?php foreach ( $columns as $column ) : ?>
							<th class="product-<?php echo $column; ?>"><?php echo $this->columns[ $column ]['title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
						<?php endforeach; ?>
					</tr>
					</thead>
				<?php
				return ob_get_clean();
			}


			/**
			 * Generates the element styles
			 *
			 * @access protected
			 * @since 3.3
			 * @return string
			 */
			public function get_styles() {
				$this->base_selector = '.fusion-woo-cart_table-' . $this->counter;
				$this->dynamic_css   = [];

				if ( ! $this->is_default( 'margin_top' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-top', $this->args['margin_top'] );
				}

				if ( ! $this->is_default( 'margin_bottom' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-bottom', $this->args['margin_bottom'] );
				}

				if ( ! $this->is_default( 'margin_left' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-left', $this->args['margin_left'] );
				}

				if ( ! $this->is_default( 'margin_right' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-right', $this->args['margin_right'] );

				}

				$selector = $this->base_selector . ' tbody tr td, ' . $this->base_selector . ' thead tr th';
				if ( ! $this->is_default( 'cell_padding_top' ) ) {
					$this->add_css_property( $selector, 'padding-top', $this->args['cell_padding_top'] );
				}

				if ( ! $this->is_default( 'cell_padding_bottom' ) ) {
					$this->add_css_property( $selector, 'padding-bottom', $this->args['cell_padding_bottom'] );
				}

				if ( ! $this->is_default( 'cell_padding_left' ) ) {
					$this->add_css_property( $selector, 'padding-left', $this->args['cell_padding_left'] );
				}

				if ( ! $this->is_default( 'cell_padding_right' ) ) {
					$this->add_css_property( $selector, 'padding-right', $this->args['cell_padding_right'] );
				}

				if ( ! $this->is_default( 'cell_padding_bottom' ) || ! $this->is_default( 'cell_padding_top' ) ) {
					$this->add_css_property( $this->base_selector . '.shop_table tbody tr', 'height', 'auto' );
				}

				$selector = $this->base_selector . ' thead tr th';
				if ( ! $this->is_default( 'heading_cell_backgroundcolor' ) ) {
					$this->add_css_property( $selector, 'background-color', $this->args['heading_cell_backgroundcolor'] );
				}

				if ( ! $this->is_default( 'heading_color' ) ) {
					$this->add_css_property( $selector, 'color', $this->args['heading_color'] );
				}

				if ( ! $this->is_default( 'fusion_font_family_heading_font' ) ) {
					$this->add_css_property( $selector, 'font-family', $this->args['fusion_font_family_heading_font'] );
				}

				if ( ! $this->is_default( 'fusion_font_variant_heading_font' ) ) {
					$this->add_css_property( $selector, 'font-weight', $this->args['fusion_font_variant_heading_font'] );
				}

				if ( ! $this->is_default( 'heading_font_size' ) ) {
					$this->add_css_property( $selector, 'font-size', $this->args['heading_font_size'] );
				}

				if ( ! $this->is_default( 'heading_line_height' ) ) {
					$this->add_css_property( $selector, 'line-height', $this->args['heading_line_height'] );
				}

				if ( ! $this->is_default( 'heading_letter_spacing' ) ) {
					$this->add_css_property( $selector, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['heading_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'heading_text_transform' ) ) {
					$this->add_css_property( $selector, 'text-transform', $this->args['heading_text_transform'] );
				}

				$selector = $this->base_selector . ' tbody tr td';
				if ( ! $this->is_default( 'table_cell_backgroundcolor' ) ) {
					$this->add_css_property( $selector, 'background-color', $this->args['table_cell_backgroundcolor'] );
				}

				if ( ! $this->is_default( 'text_color' ) ) {
					$this->add_css_property( [ $selector, $selector . ' a', $selector . ' .amount' ], 'color', $this->args['text_color'], true );
				}

				if ( ! $this->is_default( 'fusion_font_family_text_font' ) ) {
					$this->add_css_property( $selector, 'font-family', $this->args['fusion_font_family_text_font'] );
				}

				if ( ! $this->is_default( 'fusion_font_variant_text_font' ) ) {
					$this->add_css_property( $selector, 'font-weight', $this->args['fusion_font_variant_text_font'] );
				}

				if ( ! $this->is_default( 'text_font_size' ) ) {
					$this->add_css_property( $selector, 'font-size', $this->args['text_font_size'] );
				}

				if ( ! $this->is_default( 'text_line_height' ) ) {
					$this->add_css_property( $selector, 'line-height', $this->args['text_line_height'] );
				}

				if ( ! $this->is_default( 'text_letter_spacing' ) ) {
					$this->add_css_property( $selector, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['text_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'text_text_transform' ) ) {
					$this->add_css_property( $selector, 'text-transform', $this->args['text_text_transform'] );
				}

				$selector = $this->base_selector . ' tr, ' . $this->base_selector . ' tr td, ' . $this->base_selector . ' tr th';
				if ( ! $this->is_default( 'border_color' ) ) {
					$this->add_css_property( $selector, 'border-color', $this->args['border_color'], true );
				}

				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
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
						'class' => 'shop_table shop_table_responsive cart woocommerce-cart-form__contents fusion-woo-cart_table fusion-woo-cart_table-' . $this->counter,
						'style' => '',
					]
				);

				return $attr;

			}


			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function wrapper_attr() {

				$attr = [
					'class' => 'fusion-woo-cart_table-wrapper',
				];

				if ( $this->args['animation_type'] && ! fusion_doing_ajax() ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= '  ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;

			}

			/**
			 * Empty the cart based on URL.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function empty_cart() {
				if ( isset( $_GET['empty_cart'] ) && 1 === (int) $_GET['empty_cart'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					WC()->cart->empty_cart();
					$referer = wp_get_referer() ? esc_url( remove_query_arg( 'empty_cart' ) ) : wc_get_cart_url();
					wp_safe_redirect( $referer );
					exit;
				}
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function on_first_render() {
				// Maybe enqueue, we check at last moment to see if conditional rendering has been used.
				add_action( 'wp_footer', [ $this, 'maybe_enqueue' ], 5 );
			}

			/**
			 * Maybe enqueue JS for cart reloading.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function maybe_enqueue() {

				// No conditional loading at all.
				if ( empty( fusion_library()->conditional_loading ) ) {
					return;
				}

				foreach ( fusion_library()->conditional_loading as $conditional ) {
					if ( 'cart_status' === $conditional['field_name'] && 'empty' === $conditional['desired_value'] ) {

						Fusion_Dynamic_JS::enqueue_script(
							'fusion-cart-table',
							FusionBuilder::$js_folder_url . '/general/fusion-cart-table.js',
							FusionBuilder::$js_folder_path . '/general/fusion-cart-table.js',
							[ 'jquery' ],
							'1',
							true
						);

						Fusion_Dynamic_JS::localize_script(
							'fusion-cart-table',
							'fusionCartTable',
							[
								'emptyCart' => ( WC()->cart->is_empty() ? 1 : 0 ),
							]
						);
						break;
					}
				}
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/woo-cart-table.min.css' );
			}
		}
	}

	new FusionSC_WooCartTable();

}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_woo_cart_table() {
	if ( class_exists( 'WooCommerce' ) ) {
		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionSC_WooCartTable',
				[
					'name'          => esc_attr__( 'Woo Cart Table', 'fusion-builder' ),
					'shortcode'     => 'fusion_woo_cart_table',
					'icon'          => 'fusiona-cart-table',
					'help_url'      => '',
					'inline_editor' => true,
					'subparam_map'  => [
						'fusion_font_family_heading_font'  => 'heading_fonts',
						'fusion_font_variant_heading_font' => 'heading_fonts',
						'heading_font_size'                => 'heading_fonts',
						'heading_text_transform'           => 'heading_fonts',
						'heading_line_height'              => 'heading_fonts',
						'heading_letter_spacing'           => 'heading_fonts',
						'heading_color'                    => 'heading_fonts',
						'fusion_font_variant_text_font'    => 'text_fonts',
						'fusion_font_family_text_font'     => 'text_fonts',
						'text_font_size'                   => 'text_fonts',
						'text_text_transform'              => 'text_fonts',
						'text_line_height'                 => 'text_fonts',
						'text_letter_spacing'              => 'text_fonts',
						'text_color'                       => 'text_fonts',
					],
					'params'        => [
						[
							'type'        => 'connected_sortable',
							'heading'     => esc_attr__( 'Table Columns', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose the order of cart table columns.', 'fusion-builder' ),
							'param_name'  => 'table_columns',
							'default'     => 'name,price,quantity,subtotal,remove',
							'value'       => 'name,price,quantity,subtotal,remove',
							'choices'     => [
								'name'          => esc_attr__( 'Product', 'fusion-builder' ),
								'price'         => esc_attr__( 'Price', 'fusion-builder' ),
								'quantity'      => esc_attr__( 'Quantity', 'fusion-builder' ),
								'subtotal'      => esc_attr__( 'Subtotal', 'fusion-builder' ),
								'remove'        => esc_attr__( 'Remove Icon', 'fusion-builder' ),
								'product_title' => esc_attr__( 'Product Title', 'fusion-builder' ),
								'product_image' => esc_attr__( 'Product Image', 'fusion-builder' ),
							],
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'fusion_get_woo_cart_table',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Table Headers', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to have table headers displayed.', 'fusion-builder' ),
							'param_name'  => 'table_header_visibility',
							'value'       => [
								'show' => esc_attr__( 'Show', 'fusion-builder' ),
								'hide' => esc_attr__( 'Hide', 'fusion-builder' ),
							],
							'default'     => 'show',
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'fusion_get_woo_cart_table',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'tinymce',
							'heading'     => esc_attr__( 'Empty Cart Message', 'fusion-builder' ),
							'description' => esc_attr__( 'Show a message when the cart is empty.', 'fusion-builder' ),
							'param_name'  => 'element_content',
							'value'       => esc_html__( 'Your cart is empty!', 'fusion-builder' ),
							'placeholder' => true,
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
						'fusion_animation_placeholder' => [
							'preview_selector' => '.fusion-woo-cart_table',
						],
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Table Cell Padding', 'fusion-builder' ),
							'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default 5px 0 5px 0 value.', 'fusion-builder' ),
							'param_name'       => 'cell_padding',
							'value'            => [
								'cell_padding_top'    => '',
								'cell_padding_right'  => '',
								'cell_padding_bottom' => '',
								'cell_padding_left'   => '',
							],
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'         => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Heading Cell Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the heading cell background color. ', 'fusion-builder' ),
							'param_name'  => 'heading_cell_backgroundcolor',
							'value'       => '',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_style_block',
							],
							'dependency'  => [
								[
									'element'  => 'table_header_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Table Cell Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the table cell background color. ', 'fusion-builder' ),
							'param_name'  => 'table_cell_backgroundcolor',
							'value'       => '',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Table Border Color', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the color of the table border, ex: #000.' ),
							'param_name'  => 'border_color',
							'value'       => '',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'             => 'typography',
							'heading'          => esc_attr__( 'Heading Cell Typography', 'fusion-builder' ),
							'description'      => esc_html__( 'Controls the typography of the heading. Leave empty for the global font family.', 'fusion-builder' ),
							'param_name'       => 'heading_fonts',
							'choices'          => [
								'font-family'    => 'heading_font',
								'font-size'      => 'heading_font_size',
								'text-transform' => 'heading_text_transform',
								'line-height'    => 'heading_line_height',
								'letter-spacing' => 'heading_letter_spacing',
								'color'          => 'heading_color',
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
									'element'  => 'table_header_visibility',
									'value'    => 'hide',
									'operator' => '!=',
								],
							],
							'callback'         => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'             => 'typography',
							'heading'          => esc_attr__( 'Content Typography', 'fusion-builder' ),
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
					'callback'      => [
						'function' => 'fusion_ajax',
						'action'   => 'fusion_get_woo_cart_table',
						'ajax'     => true,
					],
				]
			)
		);
	}
}
add_action( 'wp_loaded', 'fusion_element_woo_cart_table' );

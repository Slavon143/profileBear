<?php
/**
 * Plugin Name: Stock Manager for WooCommerce
 * Plugin URI: https://www.storeapps.org/woocommerce-plugins/
 * Description: Manage product's stock and price in your WooCommerce store. Export/Import inventory, track history, sort and more...
 * Version: 2.9.0
 * Author: StoreApps
 * Author URI: https://www.storeapps.org/
 * Developer: StoreApps
 * Developer URI: https://www.storeapps.org/
 * Requires at least: 5.0.0
 * Tested up to: 6.0.3
 * Requires PHP: 5.6+
 * WC requires at least: 3.5.0
 * WC tested up to: 7.0.0
 * Text Domain: woocommerce-stock-manager
 * Domain Path: /languages/
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright (c) 2020-2022 StoreApps. All rights reserved.
 *
 * @package woocommerce-stock-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'STOCKDIR' ) ) {
	define( 'STOCKDIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'STOCKURL' ) ) {
	define( 'STOCKURL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'WSM_PLUGIN_FILE' ) ) {
	define( 'WSM_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'WSM_PLUGIN_VERSION' ) ) {
	define( 'WSM_PLUGIN_VERSION', get_woocommerce_stock_manager_plugin_version() );
}

// Public-Facing Functionality.

require_once plugin_dir_path( __FILE__ ) . 'admin/includes/class-wsm-save.php';

require_once plugin_dir_path( __FILE__ ) . 'public/class-stock-manager.php';

register_activation_hook( __FILE__, array( 'Stock_Manager', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Stock_Manager', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Stock_Manager', 'get_instance' ) );

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'admin/class-stock-manager-admin.php';
	add_action( 'plugins_loaded', array( 'Stock_Manager_Admin', 'get_instance' ) );
}

add_action( 'in_plugin_update_message-woocommerce-stock-manager/woocommerce-stock-manager.php', 'stockmanager_update_message_cb', 10, 2 );

/**
 * Function to check if minimum WP & WC is satisfied before updating the plugin
 * Shown on plugins page
 *
 * @param array  $plugin_data Details about current plugin version + new update with plugin assets.
 * @param object $r           Details about new plugin version with plugin assets.
 */
function stockmanager_update_message_cb( $plugin_data, $r ) {
	if ( version_compare( WSM_PLUGIN_VERSION, '2.0.0', '<' ) ) {
		?>
		<div class="wc_plugin_upgrade_notice extensions_warning minor">
			<p><strong><?php esc_html_e( 'Alert!', 'woocommerce-stock-manager' ); ?></strong> <?php esc_html_e( 'Stock Manager for WooCommerce needs following versions of WordPress and WooCommerce:', 'woocommerce-stock-manager' ); ?></p>
			<table class="plugin-details-table" cellspacing="0">
				<tbody>
					<tr>
						<td>WordPress</td>
						<td>5.0.0+</td>
					</tr>
					<tr>
						<td>WooCommerce</td>
						<td>3.5.0+</td>
					</tr>
				</tbody>
			</table>
			<p><?php esc_html_e( 'Please update WordPress and WooCommerce to the above versions before updating Stock Manager for WooCommerce.', 'woocommerce-stock-manager' ); ?></p>
		</div>
		<?php
	}
}

/**
 * Function to search a product by title
 *
 * @param string $search    The term to search.
 * @param object $wp_query  The WP query object.
 */
function wsm_search_by_title_only( $search, &$wp_query ) {
	global $wpdb;
	if ( empty( $search ) ) {
		return $search; // skip processing - no search term in query.
	}
	$q         = $wp_query->query_vars;
	$n         = ! empty( $q['exact'] ) ? '' : '%';
	$search    = '';
	$searchand = '';
	foreach ( (array) $q['search_terms'] as $term ) {
		$term      = esc_sql( $wpdb->esc_like( $term ) );
		$search   .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
		$searchand = ' AND ';
	}
	if ( ! empty( $search ) ) {
		$search = " AND ({$search}) ";
		if ( ! is_user_logged_in() ) {
			$search .= " AND ($wpdb->posts.post_password = '') ";
		}
	}
	return $search;
}

add_action( 'wp_ajax_wsm_get_products_or_export', 'wsm_get_products_or_export' );
/**
 * Get products for export.
 */
function wsm_get_products_or_export() {

	check_ajax_referer( 'sa-wsm-export', 'security' );

	$passed_offset  = ( ! empty( $_POST['offset'] ) ) ? intval( wc_clean( wp_unslash( $_POST['offset'] ) ) ) : 0; // phpcs:ignore
	$posts_per_page = 10; // TODO: would need to increase and handle logic to get all products in export.
	$args           = array(
		'post_type'      => 'product',
		'posts_status'   => 'publish',
		'posts_per_page' => $posts_per_page,
		'offset'         => $passed_offset,
	);
	$_products      = new WP_Query( $args );
	if ( ! empty( $_products->posts ) ) {
		$data = array();
		$i    = 1 + $passed_offset;
		foreach ( $_products->posts as $item ) {
			$index   = $item->ID;
			$product = wc_get_product( $item->ID );
			if ( ! $product instanceof WC_Product ) {
				continue;
			}
			if ( ! empty( $product->get_sku() ) ) {
				$sku = $product->get_sku();
			} else {
				$sku = '';
			}
			$product_name = $item->post_title;
			if ( ! empty( $product->get_manage_stock() ) ) {
				$manage_stock = $product->get_manage_stock();
			} else {
				$manage_stock = '';
			}
			if ( ! empty( $product->get_stock_status() ) ) {
				$stock_status = $product->get_stock_status();
			} else {
				$stock_status = '';
			}
			if ( ! empty( $product->get_backorders() ) ) {
				$backorders = $product->get_backorders();
			} else {
				$backorders = '';
			}
			if ( ! empty( $product->get_stock_quantity() ) ) {
				$stock = $product->get_stock_quantity();
			} else {
				$stock = '';
			}
			$product_type = $product->get_type();

			$data[ $index ]['id']           = $item->ID;
			$data[ $index ]['sku']          = $sku;
			$data[ $index ]['product_name'] = $product_name;
			$data[ $index ]['manage_stock'] = $manage_stock;
			$data[ $index ]['stock_status'] = $stock_status;
			$data[ $index ]['backorders']   = $backorders;
			$data[ $index ]['stock']        = $stock;
			$data[ $index ]['type']         = $product_type;
			$data[ $index ]['parent_id']    = '';

			$i++;

			if ( 'variable' === $product_type ) {
				$args             = array(
					'post_parent' => $item->ID,
					'post_type'   => 'product_variation',
					'numberposts' => -1,
					'post_status' => 'publish',
				);
				$variations_array = get_children( $args );
				foreach ( $variations_array as $vars ) {
					$vindex       = $vars->ID;
					$item_product = wc_get_product( $vars->ID );
					if ( ! $item_product instanceof WC_Product ) {
						continue;
					}
					if ( ! empty( $item_product->get_sku() ) ) {
						$sku = $item_product->get_sku();
					} else {
						$sku = '';
					}

					$product_name            = '';
					$item_product_attributes = wc_get_product_variation_attributes( $vindex );
					foreach ( $item_product_attributes as $k => $v ) {
						$tag = get_term_by( 'slug', $v, str_replace( 'attribute_', '', $k ) );
						if ( false === $tag ) {
							$product_name .= $v . ' ';
						} else {
							if ( is_array( $tag ) ) {
								$product_name .= $tag['name'] . ' ';
							} else {
								$product_name .= $tag->name . ' ';
							}
						}
					}

					if ( ! empty( $item_product->get_manage_stock() ) ) {
						$manage_stock = $item_product->get_manage_stock();
					} else {
						$manage_stock = '';
					}
					if ( ! empty( $item_product->get_stock_status() ) ) {
						$stock_status = $item_product->get_stock_status();
					} else {
						$stock_status = '';
					}
					if ( ! empty( $item_product->get_backorders() ) ) {
						$backorders = $item_product->get_backorders();
					} else {
						$backorders = '';
					}
					if ( ! empty( $item_product->get_stock_quantity() ) ) {
						$stock = $item_product->get_stock_quantity();
					} else {
						$stock = '';
					}
					$product_type = 'product-variant';

					$data[ $vindex ]['id']           = $vars->ID;
					$data[ $vindex ]['sku']          = $sku;
					$data[ $vindex ]['product_name'] = $product_name;
					$data[ $vindex ]['manage_stock'] = $manage_stock;
					$data[ $vindex ]['stock_status'] = $stock_status;
					$data[ $vindex ]['backorders']   = $backorders;
					$data[ $vindex ]['stock']        = $stock;
					$data[ $vindex ]['type']         = $product_type;
					$data[ $vindex ]['parent_id']    = $item->ID;

					$i++;
				}
			}
		}
		$passed_offset = $passed_offset + 10;
		$response      = array(
			'status' => 'continue',
			'data'   => $data,
			'offset' => $passed_offset,
		);
		echo wp_json_encode( $response );
		exit();
	} else {
		$response = array(
			'status' => 'finish',
			'text'   => __( 'All done! Close.', 'woocommerce-stock-manager' ),
		);
		echo wp_json_encode( $response );
		exit();
	}

}

add_action( 'wp_ajax_wsm_get_csv_file', 'wsm_get_csv_file' );
/**
 * Get CSV file for export.
 */
function wsm_get_csv_file() {

	check_ajax_referer( 'sa-wsm-get-csv', 'security' );

	$data = ( ! empty( $_POST['data'] ) ) ? wc_clean( wp_unslash( $_POST['data'] ) ) : array(); // phpcs:ignore
	$data = (array) json_decode( $data );

	$string = array();
	foreach ( $data as $line ) {

		$line_array = array(
			'Id'           => $line->id,
			'Sku'          => $line->sku,
			'Product name' => $line->product_name,
			'Manage stock' => $line->manage_stock,
			'Stock status' => $line->stock_status,
			'Backorders'   => $line->backorders,
			'Stock'        => $line->stock,
			'Type'         => $line->type,
			'Parent ID'    => $line->parent_id,
		);
		$string[]   = $line_array;
	}

	echo wp_json_encode( $string );

	exit();

}

add_action( 'wp_ajax_wsm_klawoo_subscribe', 'wsm_klawoo_subscribe' );
/**
 * Function for Klawoo subscribe.
 */
function wsm_klawoo_subscribe() {
	$url = 'http://app.klawoo.com/subscribe';
	if ( ! empty( $_POST ) ) {
		$params = ( ! empty( $_POST['params'] ) ) ? wc_clean( wp_unslash( $_POST['params'] ) ) : array(); // phpcs:ignore
	} else {
		exit();
	}

	$post_sa_wsm_nonce = ( ! empty( $params['sa_wsm_sub_nonce'] ) ) ? wc_clean( wp_unslash( $params['sa_wsm_sub_nonce'] ) ) : '';
	if ( ! empty( $post_sa_wsm_nonce ) && wp_verify_nonce( $post_sa_wsm_nonce, 'sa-wsm-subscribe' ) ) {
		if ( empty( $params['name'] ) ) {
			$params['name'] = '';
		}

		$method = 'POST';
		$qs     = http_build_query( $params );

		$options = array(
			'timeout' => 15,
			'method'  => $method,
		);

		if ( 'POST' === $method ) {
			$options['body'] = $qs;
		} else {
			if ( strpos( $url, '?' ) !== false ) {
				$url .= '&' . $qs;
			} else {
				$url .= '?' . $qs;
			}
		}

		$response = wp_remote_request( $url, $options );
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			$data = $response['body'];

			if ( 'error' !== $data ) {
				$message_start = substr( $data, strpos( $data, '<body>' ) + 6 );
				$remove        = substr( $message_start, strpos( $message_start, '</body>' ) );
				$message       = trim( str_replace( $remove, '', $message_start ) );

				// Hide the in-app lead notice.
				update_option( 'wsm_dismiss_subscribe_admin_notice', true, 'no' );

				echo wp_kses_post( $message );
				exit();
			}
		}
	}

	exit();
}

/**
 * Function to return plugin data.
 *
 * @since 2.7.0
 */
function get_woocommerce_stock_manager_plugin_data() {
	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	return get_plugin_data( WSM_PLUGIN_FILE );
}

/**
 * Function to return current plugin version.
 *
 * @since 2.7.0
 */
function get_woocommerce_stock_manager_plugin_version() {
	$plugin_data    = get_woocommerce_stock_manager_plugin_data();
	$plugin_version = $plugin_data['Version'];

	return $plugin_version;
}

/**
 * Function to check if WSM admin page.
 */
function is_wsm_admin_page() {
	$page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
	if ( 'stock-manager' === $page || 'stock-manager-import-export' === $page || 'stock-manager-log' === $page || 'stock-manager-setting' === $page || 'stock-manager-storeapps-plugins' === $page ) {
		return true;
	}

	return false;
}

/**
 * Function to show SA in app offers in WSM if any.
 *
 * @since: 2.5.2.
 */
function wsm_may_be_show_sa_in_app_offer() {

	if ( ! class_exists( 'SA_WSM_In_App_Offer' ) && file_exists( STOCKDIR . 'sa-includes/class-sa-wsm-in-app-offer.php' ) ) {
		include_once STOCKDIR . 'sa-includes/class-sa-wsm-in-app-offer.php';

		$is_wsm_admin = is_wsm_admin_page();

		$args     = array(
			'file'           => STOCKDIR . 'sa-includes/',
			'prefix'         => 'wsm',              // prefix/slug of your plugin.
			'option_name'    => 'sa_wsm_offer_halloween_2022',
			'campaign'       => 'sa_halloween_2022',
			'start'          => '2022-10-28 13:30:00',
			'end'            => '2022-11-03 06:00:00',
			'is_plugin_page' => $is_wsm_admin ? true : false,   // page where you want to show offer, do not send this if no plugin page is there and want to show offer on Products page.
		);
		$sa_offer = SA_WSM_In_App_Offer::get_instance( $args );
	}
}
add_action( 'plugins_loaded', 'wsm_may_be_show_sa_in_app_offer' );


/**
 * Action for WooCommerce v7.1 custom order tables related compatibility.
 *
 * @since: 2.9.0.
*/
add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, false );
		}
	}
);

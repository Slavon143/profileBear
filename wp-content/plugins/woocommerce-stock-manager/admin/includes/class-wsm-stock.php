<?php
/**
 * Stock Log Class
 *
 * @package   woocommerce-stock-manager/admin/includes/
 * @version   2.8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for Stock Log Manager.
 */
class WSM_Stock {

	/**
	 * Instance of this class.
	 *
	 * @var  object
	 */
	protected static $instance = null;

	/**
	 * Limit to display number of products on a page.
	 *
	 * @var  int
	 */
	public $limit = 100;

	/**
	 * Constructor for the stock class.
	 */
	private function __construct() {

		$limit = get_option( 'woocommerce_stock_limit' );
		if ( ! empty( $limit ) ) {
			$this->limit = $limit;
		}

	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Return products
	 *
	 * @param array $data The products data.
	 */
	public function get_products( $data = array() ) {

		$get_sku = ( ! empty( $_GET['sku'] ) ) ? wc_clean( wp_unslash( $_GET['sku'] ) ) : ''; // phpcs:ignore
		if ( ! empty( $get_sku ) ) {
			return $this->get_product_by_sku( $get_sku );
		}

		$get_product_title = ( ! empty( $_GET['product-title'] ) ) ? wc_clean( wp_unslash( $_GET['product-title'] ) ) : ''; // phpcs:ignore
		if ( ! empty( $get_product_title ) ) {
			return $this->get_product_by_product_title( $get_product_title );
		}

		$args              = array();
		$args['post_type'] = 'product';

		$get_product_type     = ( ! empty( $_GET['product-type'] ) ) ? wc_clean( wp_unslash( $_GET['product-type'] ) ) : ''; // phpcs:ignore
		$get_product_category = ( ! empty( $_GET['product-category'] ) ) ? wc_clean( wp_unslash( $_GET['product-category'] ) ) : ''; // phpcs:ignore
		// Initialize tax_query array.
		if ( ! empty( $get_product_type ) || ! empty( $get_product_category ) ) {
			$args['tax_query'] = array(); // phpcs:ignore

			if ( ! empty( $get_product_type ) ) {
				if ( 'variable' === $get_product_type ) {
						$args['tax_query'][] = array(
							'taxonomy' => 'product_type',
							'terms'    => 'variable',
							'field'    => 'slug',
						);
				} else {
					$args['tax_query'] = array( // phpcs:ignore
						'taxonomy' => 'product_type',
						'terms'    => 'simple',
						'field'    => 'slug',
					);
				}
			}

			/**
			 * Product category filter.
			 */
			if ( ! empty( $get_product_category ) ) {
				if ( 'all' !== $get_product_category ) {
					$category            = $get_product_category;
					$args['tax_query'][] = array(
						'taxonomy' => 'product_cat',
						'terms'    => $category,
						'field'    => 'term_id',
					);
				}
			}
		}

		$get_stock_status     = ( ! empty( $_GET['stock-status'] ) ) ? wc_clean( wp_unslash( $_GET['stock-status'] ) ) : ''; // phpcs:ignore
		$get_manage_stock     = ( ! empty( $_GET['manage-stock'] ) ) ? wc_clean( wp_unslash( $_GET['manage-stock'] ) ) : ''; // phpcs:ignore
		// Initialize meta_query array.
		if ( ! empty( $get_stock_status ) || ! empty( $get_manage_stock ) ) {
			$args['meta_query'] = array(); // phpcs:ignore

			if ( ! empty( $get_stock_status ) ) {
				$args['meta_query'][] = array(
					'key'     => '_stock_status',
					'value'   => $get_stock_status,
					'compare' => '=',
				);
			}

			if ( ! empty( $get_manage_stock ) ) {
				$args['meta_query'][] = array(
					'key'     => '_manage_stock',
					'value'   => $get_manage_stock,
					'compare' => '=',
				);
			}
		}

		$get_order_by = ( ! empty( $_GET['order-by'] ) ) ? wc_clean( wp_unslash( $_GET['order-by'] ) ) : ''; // phpcs:ignore
		if ( ! empty( $get_order_by ) ) {
			if ( 'name-asc' === $get_order_by ) {
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
			} elseif ( 'name-desc' === $get_order_by ) {
				$args['orderby'] = 'title';
				$args['order']   = 'DESC';
			} elseif ( 'sku-asc' === $get_order_by ) {
				$args['meta_key'] = '_sku'; // phpcs:ignore
				$args['orderby']  = 'meta_value';
				$args['order']    = 'ASC';
			} elseif ( 'sku-desc' === $get_order_by ) {
				$args['meta_key'] = '_sku'; // phpcs:ignore
				$args['orderby']  = 'meta_value';
				$args['order']    = 'DESC';
			}
		}

		$args['posts_per_page'] = $this->limit;

		$passed_offset = ( ! empty( $_GET['offset'] ) ) ? wc_clean( wp_unslash( $_GET['offset'] ) ) : 0; // phpcs:ignore

		$args['offset'] = ( ! empty( $passed_offset ) ) ? ( ( intval( $passed_offset ) - 1 ) * $this->limit ) : $passed_offset;

		$the_query = new WP_Query( $args );

		return $the_query;
	}

	/**
	 * Return pagination
	 *
	 * @param string $query The query.
	 */
	public function pagination( $query ) {

		$get_sku = ( ! empty( $_GET['sku'] ) ) ? wc_clean( wp_unslash( $_GET['sku'] ) ) : ''; // phpcs:ignore
		if ( ! empty( $get_sku ) ) {
			return false;
		}

		$all = $query->found_posts;

		$pages         = (int) ceil( $all / $this->limit );
		$passed_offset = ( ! empty( $_GET['offset'] ) ) ? wc_clean( wp_unslash( $_GET['offset'] ) ) : 0; // phpcs:ignore
		$current       = ( ! empty( $passed_offset ) ) ? intval( $passed_offset ) : 1;

		$html         = '';
		$html        .= '<div class="stock-manager-pagination">';
		$query_string = ( ! empty( $_SERVER['QUERY_STRING'] ) ) ? wc_clean( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : ''; // phpcs:ignore
		if ( 1 !== $pages ) {
			for ( $i = 1; $i <= $pages; $i++ ) {
				if ( $current === $i ) {
					$html .= '<span class="btn btn-default">' . $i . '</span>';
				} else {
					$html .= '<a class="btn btn-primary" href="' . admin_url() . 'admin.php?' . $query_string . '&offset=' . $i . '">' . $i . '</a>';
				}
			}
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Save all meta data.
	 *
	 * @param array $data The column key to name map.
	 */
	public function save_all( $data ) {
		$post = ( ! empty( $_POST ) ) ? wc_clean( wp_unslash( $_POST ) ) : array(); // phpcs:ignore
		foreach ( $data['product_id'] as $item ) {
			WSM_Save::save_one_item( $post, $item );
		}
	}

	/**
	 * Save all meta data
	 *
	 * @param array $data The column display data.
	 */
	public function save_filter_display( $data ) {
		$option = array();
		if ( ! empty( $data['thumbnail'] ) ) {
			$option['thumbnail'] = 'display';
		} else {
			$option['thumbnail'] = 'no'; }
		if ( ! empty( $data['price'] ) ) {
			$option['price'] = 'display';
		} else {
			$option['price'] = 'no'; }
		if ( ! empty( $data['sales_price'] ) ) {
			$option['sales_price'] = 'display';
		} else {
			$option['sales_price'] = 'no'; }
		if ( ! empty( $data['weight'] ) ) {
			$option['weight'] = 'display';
		} else {
			$option['weight'] = 'no'; }
		if ( ! empty( $data['manage_stock'] ) ) {
			$option['manage_stock'] = 'display';
		} else {
			$option['manage_stock'] = 'no'; }
		if ( ! empty( $data['stock_status'] ) ) {
			$option['stock_status'] = 'display';
		} else {
			$option['stock_status'] = 'no'; }
		if ( ! empty( $data['backorders'] ) ) {
			$option['backorders'] = 'display';
		} else {
			$option['backorders'] = 'no'; }
		if ( ! empty( $data['stock'] ) ) {
			$option['stock'] = 'display';
		} else {
			$option['stock'] = 'no'; }
		if ( ! empty( $data['tax_status'] ) ) {
			$option['tax_status'] = 'display';
		} else {
			$option['tax_status'] = 'no'; }
		if ( ! empty( $data['tax_class'] ) ) {
			$option['tax_class'] = 'display';
		} else {
			$option['tax_class'] = 'no'; }
		if ( ! empty( $data['shipping_class'] ) ) {
			$option['shipping_class'] = 'display';
		} else {
			$option['shipping_class'] = 'no'; }

		if ( ! empty( $option ) ) {
			update_option( 'wsm_display_option', $option, 'no' );
		}
	}

	/**
	 * Get products by sku.
	 *
	 * @param string $sku The product SKU to search.
	 */
	private function get_product_by_sku( $sku ) {
		$args                   = array();
		$args['post_type']      = array( 'product', 'product_variation' );
		$args['meta_query']     = array( // phpcs:ignore
			array(
				'key'     => '_sku',
				'value'   => $sku,
				'compare' => 'LIKE',
			),
		);
		$args['posts_per_page'] = $this->limit;

		$the_query = new WP_Query( $args );

		return $the_query;
	}

	/**
	 * Get products by title.
	 *
	 * @param string $title The product title to search.
	 */
	private function get_product_by_product_title( $title ) {

		add_filter( 'posts_search', 'wsm_search_by_title_only', 500, 2 );

		$args                   = array();
		$args['post_type']      = 'product';
		$args['s']              = $title;
		$args['post_status']    = 'publish';
		$args['orderby']        = 'title';
		$args['order']          = 'ASC';
		$args['posts_per_page'] = $this->limit;

		$passed_offset = ( ! empty( $_GET['offset'] ) ) ? wc_clean( wp_unslash( $_GET['offset'] ) ) : 0; // phpcs:ignore

		$args['offset'] = ( ! empty( $passed_offset ) ) ? ( ( intval( $passed_offset ) - 1 ) * $this->limit ) : $passed_offset;

		$the_query = new WP_Query( $args );

		remove_filter( 'posts_search', 'wsm_search_by_title_only' );

		return $the_query;

	}

}//end class

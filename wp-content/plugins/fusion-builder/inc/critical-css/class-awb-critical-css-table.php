<?php
/**
 * Allow management of critical css.
 *
 * @package fusion-builder
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

// WP_List_Table is not loaded automatically so we need to load it in our application.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Create a new table class that will extend the WP_List_Table.
 */
class AWB_Critical_CSS_Table extends WP_List_Table {

	/**
	 * Data columns.
	 *
	 * @since 1.0
	 * @var array
	 */
	public $columns = [];

	/**
	 * URL
	 *
	 * @since 1.0
	 * @var string
	 */
	public $url = '';

	/**
	 * CSS Key Label.
	 *
	 * @since 3.6.1
	 * @var array
	 */
	public $css_key_labels = [];

	/**
	 * Class constructor.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct(
			[
				'singular' => esc_html__( 'Critical CSS', 'fusion-builder' ), // Singular name of the listed records.
				'plural'   => esc_html__( 'Critical CSS', 'fusion-builder' ), // Plural name of the listed records.
				'ajax'     => false, // This table doesn't support ajax.
				'class'    => 'avada-critical-css-table',
			]
		);

		$this->columns        = $this->get_columns();
		$this->css_key_labels = [
			'global_post'            => esc_html__( 'Global Single Post', 'fusion-builder' ),
			'global_avada_portfolio' => esc_html__( 'Global Single Portfolio', 'fusion-builder' ),
			'global_product'         => esc_html__( 'Global Single Product', 'fusion-builder' ),
		];

		// Actions which need to removed from the URL after page reloads.
		$remove_actions = [ 'action', 'action2', 'post' ];
		$this->url      = remove_query_arg( $remove_actions, wp_unslash( $_SERVER['REQUEST_URI'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	}

	/**
	 * Set the custom classes for table.
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function get_table_classes() {
		return [ 'widefat', 'striped' ];
	}

	/**
	 * Prepare the items for the table to process.
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function prepare_items() {
		$columns      = $this->columns;
		$per_page     = 10;
		$current_page = $this->get_pagenum();
		$data         = $this->table_data( $per_page, $current_page );
		$hidden       = $this->get_hidden_columns();
		$sortable     = $this->get_sortable_columns();
		$total        = AWB_Critical_CSS()->get_total();

		$this->set_pagination_args(
			[
				'total_items' => $total,
				'per_page'    => $per_page,
			]
		);

		$this->_column_headers = [ $columns, $hidden, $sortable ];
		$this->items           = $data;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table.
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'cb'          => '<input type="checkbox" />',
			'css_key'     => esc_html__( 'Key', 'fusion-builder' ),
			'mobile_css'  => __( 'Mobile CSS', 'fusion-builder' ),
			'desktop_css' => __( 'Desktop CSS', 'fusion-builder' ),
			'updated_at'  => __( 'Last Updated', 'fusion-builder' ),
			'update'      => __( 'Regenerate', 'fusion-builder' ),
		];

		return $columns;
	}

	/**
	 * Define which columns are hidden
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function get_hidden_columns() {
		return [];
	}

	/**
	 * Define the sortable columns
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function get_sortable_columns() {
		return [
			'id'         => [ 'id', true ],
			'css_key'    => [ 'css_key', true ],
			'updated_at' => [ 'updated_at', true ],
		];
	}

	/**
	 * Get the table data.
	 *
	 * @since 1.0
	 * @access public
	 * @param  number $per_page     Posts per page.
	 * @param  number $current_page - Current page number.
	 * @return array
	 */
	private function table_data( $per_page = -1, $current_page = 0 ) {
		$data = [];

		// Make sure current-page and per-page are integers.
		$per_page     = (int) $per_page;
		$current_page = (int) $current_page;

		$args = [
			'limit'  => $per_page,
			'offset' => ( $current_page - 1 ) * $per_page,
			'where'  => [],
		];

		// Add sorting.
		if ( isset( $_GET['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$args['order_by'] = sanitize_text_field( wp_unslash( $_GET['orderby'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$args['order']    = ( isset( $_GET['order'] ) ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'ASC'; // phpcs:ignore WordPress.Security.NonceVerification
		}

		$codes = AWB_Critical_CSS()->get( $args );

		foreach ( $codes as $code ) {
			$data[] = [
				'id'          => $code->id,
				'css_key'     => $code->css_key,
				'mobile_css'  => $code->mobile_css,
				'desktop_css' => $code->desktop_css,
				'updated_at'  => ! empty( $code->updated_at ) ? gmdate( 'M d Y', $code->updated_at ) : '',
			];
		}

		return $data;
		// phpcs:enable WordPress.Security.NonceVerification
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @since 1.0
	 * @access public
	 * @param  array $item        Data.
	 * @return void
	 */
	public function single_row( $item ) {
		$row_classes = '';

		echo '<tr class="' . ltrim( $row_classes ) . '" data-id="' . $item['id'] . '">'; // phpcs:ignore WordPress.Security.EscapeOutput
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @since 1.0
	 * @access public
	 * @param  array  $item        Data.
	 * @param  string $column_id - Current column id.
	 * @return string
	 */
	public function column_default( $item, $column_id ) {
		do_action( 'awb_critical_css_custom_column', $column_id, $item );

		if ( isset( $item[ $column_id ] ) ) {
			return $item[ $column_id ];
		}
		return '';
	}

	/**
	 * Reset registration counter.
	 *
	 * @since 1.0
	 * @access public
	 * @param  array $item Data.
	 * @return string
	 */
	public function column_css_key( $item ) {
		$markup = $item['css_key'];
		if ( is_numeric( $item['css_key'] ) ) {
			$markup = '<a href="' . get_permalink( (int) $item['css_key'] ) . '" target="_blank" rel="noopener">' . get_the_title( (int) $item['css_key'] ) . '</a>';
		} elseif ( 'homepage' === $item['css_key'] ) {
			$markup = '<a href="' . get_home_url() . '" target="_blank" rel="noopener">' . esc_html__( 'Homepage', 'fusion-builder' ) . '</a>';
		} elseif ( false !== strpos( $item['css_key'], 'global_' ) ) {
			$markup = '<a href="' . admin_url( 'edit.php?post_type=' . str_replace( 'global_', '', $item['css_key'] ) ) . '" target="_blank" rel="noopener">' . $this->css_key_labels[ $item['css_key'] ] . '</a>';
		}
		$actions['delete'] = sprintf( '<a href="' . $this->url . '&action=%s&post=%s">' . esc_html__( 'Delete', 'fusion-builder' ) . '</a>', 'delete_css', esc_attr( $item['id'] ) );

		return $markup . $this->row_actions( $actions );
	}

	/**
	 * Reset registration counter.
	 *
	 * @since 1.0
	 * @access public
	 * @param  array $item Data.
	 * @return string
	 */
	public function column_desktop_css( $item ) {
		$actions['clear'] = sprintf( '<a href="' . $this->url . '&action=%s&post=%s">' . esc_html__( 'Clear', 'fusion-builder' ) . '</a>', 'clear_desktop_css', esc_attr( $item['id'] ) );

		$actions['update'] = sprintf( '<a href="' . $this->url . '&action=%s&post=%s" class="awb-regenerate-desktop-css">' . esc_html__( 'Regenerate', 'fusion-builder' ) . '</a>', 'update_desktop_css', esc_attr( $item['id'] ) );

		$icon = '<i class="fusiona-checkmark"></i>';
		if ( empty( $item['desktop_css'] ) ) {
			$icon = '-';
		}
		return '<span class="awb-ccss-icon">' . $icon . '</span>' . $this->row_actions( $actions );
	}

	/**
	 * Reset registration counter.
	 *
	 * @since 1.0
	 * @access public
	 * @param  array $item Data.
	 * @return string
	 */
	public function column_mobile_css( $item ) {
		$actions['clear'] = sprintf( '<a href="' . $this->url . '&action=%s&post=%s">' . esc_html__( 'Clear', 'fusion-builder' ) . '</a>', 'clear_mobile_css', esc_attr( $item['id'] ) );

		$actions['update'] = sprintf( '<a href="' . $this->url . '&action=%s&post=%s" class="awb-regenerate-mobile-css">' . esc_html__( 'Regenerate', 'fusion-builder' ) . '</a>', 'update_mobile_css', esc_attr( $item['id'] ) );

		$icon = '<i class="fusiona-checkmark"></i>';
		if ( empty( $item['mobile_css'] ) ) {
			$icon = '-';
		}

		return '<span class="awb-ccss-icon">' . $icon . '</span>' . $this->row_actions( $actions );
	}

	/**
	 * Reset registration counter.
	 *
	 * @since 1.0
	 * @access public
	 * @param  array $item Data.
	 * @return string
	 */
	public function column_update( $item ) {
		return '<a href="#" class="awb-update-row button"><span class="awb-spinner"><i class="fusiona-loop"></i></span>' . esc_html__( 'Regenerate', 'fusion-builder' ) . '</a>';
	}

	/**
	 * Reset registration counter.
	 *
	 * @since 1.0
	 * @access public
	 * @param  array $item Data.
	 * @return string
	 */
	public function column_delete( $item ) {
		return '<a href="#" class="awb-delete-row button">' . esc_html__( 'Delete', 'fusion-builder' ) . '</a>';
	}

	/**
	 * Set bulk actions dropdown.
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'awb_bulk_delete_css' => esc_html__( 'Delete', 'fusion-builder' ),
			'awb_bulk_update_css' => esc_html__( 'Regenerate', 'fusion-builder' ),
		];

		return $actions;
	}

	/**
	 * Set checkbox for bulk selection and actions.
	 *
	 * @since 1.0
	 * @access public
	 * @param  array $item Data.
	 * @return string
	 */
	public function column_cb( $item ) {
		return "<input type='checkbox' name='post[]' value='{$item['id']}' />";
	}

	/**
	 * Display custom text if form builder is empty.
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function no_items() {
		esc_attr_e( 'No critical CSS entries created yet.', 'fusion-builder' );
	}
}

<?php
/**
 * Fusion Custom Icons Table.
 *
 * @package Avada-Builder
 * @subpackage Options
 * @since 2.2
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
class Fusion_Custom_Icons_Table extends WP_List_Table {

	/**
	 * Data columns.
	 *
	 * @since 1.0
	 * @var array
	 */
	public $columns = [];

	/**
	 * Number of total table items.
	 *
	 * @since 3.6
	 * @var int
	 */
	public $total_items = -1;

	/**
	 * Class constructor.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct(
			[
				'singular' => esc_html__( 'Icon Set', 'fusion-builder' ), // Singular name of the listed records.
				'plural'   => esc_html__( 'Icon Sets', 'fusion-builder' ), // Plural name of the listed records.
				'ajax'     => false, // This table doesn't support ajax.
				'class'    => 'fusion-custom-icons-table',
			]
		);

		$this->columns = $this->get_columns();
	}

	/**
	 * Set the custom classes for table.
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function get_table_classes() {
		return [ 'widefat', 'fixed', 'striped', 'fusion-custom-icons-table' ];
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
		$per_page     = 15;
		$current_page = $this->get_pagenum();
		$data         = $this->table_data( $per_page, $current_page );
		$hidden       = $this->get_hidden_columns();
		$sortable     = $this->get_sortable_columns();

		$this->set_pagination_args(
			[
				'total_items' => -1 !== $this->total_items ? $this->total_items : count( $this->table_data() ),
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
			'cb'           => '<input type="checkbox" />',
			'title'        => esc_html__( 'Title', 'fusion-builder' ),
			'icons_prefix' => esc_html__( 'CSS Prefix', 'Avada' ),
		];

		return apply_filters( 'manage_fusion_custom_icons_columns', $columns );
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
			'title' => [ 'title', true ],
		];
	}

	/**
	 * Get term name from slug.
	 *
	 * @since 2.2
	 * @access public
	 * @param string $term_name term name.
	 * @return string
	 */
	public function get_term_name( $term_name ) {
		$types = Fusion_Template_Builder()->get_template_terms();

		foreach ( $types as $type_name => $type ) {
			if ( $type_name === $term_name ) {
				return isset( $type['label'] ) ? $type['label'] : $type_name;
			}
		}

		return $types['content']['label'];
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
		$data          = [];
		$library_query = [];
		$status        = [ 'publish', 'draft', 'future', 'pending', 'private' ];

		// Make sure current-page and per-page are integers.
		$per_page     = (int) $per_page;
		$current_page = (int) $current_page;

		// phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_GET['status'] ) ) {
			$status = sanitize_text_field( wp_unslash( $_GET['status'] ) );
		}

		$args = [
			'post_type'      => [ 'fusion_icons' ],
			'posts_per_page' => $per_page,
			'post_status'    => $status,
			'offset'         => ( $current_page - 1 ) * $per_page,
		];

		// Add sorting.
		if ( isset( $_GET['orderby'] ) ) {
			$args['orderby'] = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
			$args['order']   = ( isset( $_GET['order'] ) ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'ASC';
		}

		$library_query = new WP_Query( $args );

		// Check if there are items available.
		if ( $library_query->have_posts() ) {

			$this->total_items = $library_query->found_posts;

			// The loop.
			while ( $library_query->have_posts() ) :
				$library_query->the_post();
				$element_post_id = get_the_ID();

				$element_post = [
					'title'  => get_the_title(),
					'id'     => $element_post_id,
					'date'   => get_the_date( 'm/d/Y' ),
					'time'   => get_the_date( 'm/d/Y g:i:s A' ),
					'status' => get_post_status(),
				];

				$data[] = $element_post;
			endwhile;

			// Restore original Post Data.
			wp_reset_postdata();
		}
		return $data;
		// phpcs:enable WordPress.Security.NonceVerification
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
		do_action( 'manage_fusion_icons_custom_column', $column_id, $item );

		if ( isset( $item[ $column_id ] ) ) {
			return $item[ $column_id ];
		}
		return '';
	}

	/**
	 * Set row actions for title column.
	 *
	 * @since 1.0
	 * @access public
	 * @param  array $item Data.
	 * @return string
	 */
	public function column_title( $item ) {
		$wpnonce = wp_create_nonce( 'fusion-template-builder' );

		if ( isset( $_GET['status'] ) && 'trash' === $_GET['status'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			$actions['restore'] = sprintf( '<a href="?_wpnonce=%s&action=%s&post=%s">' . esc_html__( 'Restore', 'fusion-builder' ) . '</a>', esc_attr( $wpnonce ), 'fusion_restore_element', esc_attr( $item['id'] ) );
			$actions['delete']  = sprintf( '<a href="?_wpnonce=%s&action=%s&post=%s">' . esc_html__( 'Delete Permanently', 'fusion-builder' ) . '</a>', esc_attr( $wpnonce ), 'fusion_delete_element', esc_attr( $item['id'] ) );
		} else {
			$actions['edit']  = sprintf( '<a href="post.php?post=%s&action=%s">' . esc_html__( 'Edit', 'fusion-builder' ) . '</a>', esc_attr( $item['id'] ), 'edit' );
			$actions['trash'] = sprintf( '<a href="?_wpnonce=%s&action=%s&post=%s">' . esc_html__( 'Trash', 'fusion-builder' ) . '</a>', esc_attr( $wpnonce ), 'fusion_trash_element', esc_attr( $item['id'] ) );
		}

		$status = '';
		if ( 'draft' === $item['status'] ) {
			$status = ' &mdash; <span class="post-state">' . ucwords( $item['status'] ) . '</span>';
		}

		$title = '<strong><a href="post.php?post=' . esc_attr( $item['id'] ) . '&action=edit">' . esc_html( $item['title'] ) . '</a>' . $status . '</strong>';

		return $title . ' ' . $this->row_actions( $actions );
	}

	/**
	 * Set date column.
	 *
	 * @since 1.0
	 * @access public
	 * @param  array $item Data.
	 * @return string
	 */
	public function column_icons_prefix( $item ) {
		$icon_set = fusion_data()->post_meta( $item['id'] )->get( 'custom_icon_set' );
		if ( ! empty( $icon_set['css_prefix'] ) ) {
			return '<pre>' . esc_html( '.' . $icon_set['css_prefix'] ) . '</pre>';
		}
	}

	/**
	 * Set bulk actions dropdown.
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function get_bulk_actions() {
		if ( isset( $_GET['status'] ) && 'trash' === $_GET['status'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			$actions = [
				'fusion_restore_element' => esc_html__( 'Restore', 'fusion-builder' ),
				'fusion_delete_element'  => esc_html__( 'Delete Permanently', 'fusion-builder' ),
			];
		} else {
			$actions = [
				'fusion_trash_element' => esc_html__( 'Move to Trash', 'fusion-builder' ),
			];
		}

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
	 * Display custom text if template builder is empty.
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function no_items() {
		if ( isset( $_GET['status'] ) && 'trash' === $_GET['status'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			esc_attr_e( 'No custom icon sets found in Trash.', 'fusion-builder' );
		} else {
			esc_attr_e( 'No custom icon sets have been created yet.', 'fusion-builder' );
		}
	}

	/**
	 * Display status count with link.
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function get_status_links() {
		$post_status  = [];
		$status_lists = [];
		$count_posts  = wp_count_posts( 'fusion_icons' );
		$count_posts  = (array) $count_posts;

		if ( isset( $count_posts['publish'] ) && $count_posts['publish'] ) {
			$post_status['all'] = $count_posts['publish'];
		}

		if ( isset( $count_posts['trash'] ) && $count_posts['trash'] ) {
			$post_status['trash'] = $count_posts['trash'];
		}

		$status_html = '<ul class="subsubsub">';

		foreach ( $post_status as $status => $count ) {
			$current_type = 'all';

			if ( isset( $_GET['status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$current_type = sanitize_text_field( wp_unslash( $_GET['status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}

			$current = ( $status === $current_type ) ? ' class="current" ' : '';

			$status_attr = ( 'all' !== $status ) ? '&type=' . $status : '';
			if ( 'trash' === $status ) {
				$status_attr = '&status=' . $status;
			}

			$status_title = $status;
			if ( 'trash' !== $status && 'all' !== $status ) {
				$status_title = $this->get_term_name( $status );
			}

			$status_list  = '<li class="' . $status . '">';
			$status_list .= '<a href="' . admin_url( 'admin.php?page=avada-icons' ) . $status_attr . '"' . $current . '>' . ucwords( $status_title );
			$status_list .= ' (' . $count . ')</a>';
			$status_list .= '</li>';

			$status_lists[] = $status_list;
		}

		$status_html .= implode( ' | ', $status_lists );
		$status_html .= '</ul>';

		echo $status_html; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

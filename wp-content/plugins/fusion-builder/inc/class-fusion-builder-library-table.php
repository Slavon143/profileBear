<?php
/**
 * Fusion Library.
 *
 * @package Avada-Builder
 * @subpackage Options
 * @since 1.6
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
class Fusion_Builder_Library_Table extends WP_List_Table {

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
				'singular' => esc_html__( 'Element', 'fusion-builder' ), // Singular name of the listed records.
				'plural'   => esc_html__( 'Elements', 'fusion-builder' ), // Plural name of the listed records.
				'ajax'     => false, // This table doesn't support ajax.
				'class'    => 'fusion-library-table',
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
		return [ 'widefat', 'fixed', 'striped', 'fusion-library-table' ];
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
			'cb'     => '<input type="checkbox" />',
			'title'  => esc_html__( 'Title', 'fusion-builder' ),
			'type'   => esc_html__( 'Type', 'fusion-builder' ),
			'global' => esc_html__( 'Global', 'fusion-builder' ),
			'date'   => esc_html__( 'Date', 'fusion-builder' ),
		];

		return apply_filters( 'manage_fusion_element_posts_columns', $columns );
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
			'date'  => [ 'date', true ],
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
			'post_type'      => [ 'fusion_template', 'fusion_element' ],
			'posts_per_page' => $per_page,
			'post_status'    => $status,
			'offset'         => ( $current_page - 1 ) * $per_page,
		];

		// Add sorting.
		if ( isset( $_GET['orderby'] ) ) {
			$args['orderby'] = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
			$args['order']   = ( isset( $_GET['order'] ) ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'ASC';
		}

		// Get by type.
		if ( isset( $_GET['type'] ) ) {
			$args['post_type'] = 'fusion_element';

			if ( 'global' === $_GET['type'] ) {
				$args['meta_key']   = '_fusion_is_global';
				$args['meta_value'] = 'yes';
			} elseif ( 'template' === $_GET['type'] ) {
				$args['post_type'] = 'fusion_template';
			} else {
				$args['tax_query'] = [
					[
						'taxonomy' => 'element_category',
						'field'    => 'name',
						'terms'    => sanitize_text_field( wp_unslash( $_GET['type'] ) ),
					],
				];
			}
		}

		$library_query = new WP_Query( $args );

		// Check if there are items available.
		if ( $library_query->have_posts() ) {

			$this->total_items = $library_query->found_posts;

			// The loop.
			while ( $library_query->have_posts() ) :
				$library_query->the_post();
				$element_post_id = get_the_ID();

				$terms         = get_the_terms( $element_post_id, 'element_category' );
				$display_terms = '';
				$global        = '';

				if ( $terms ) {
					foreach ( $terms as $term ) {
						$term_name = $term->name;

						if ( 'sections' === $term_name ) {
							$term_name = esc_html__( 'Container', 'fusion-builder' );
						} elseif ( 'columns' === $term_name ) {
							$term_name = esc_html__( 'Column', 'fusion-builder' );
						} elseif ( 'elements' === $term_name ) {
							$term_name = esc_html__( 'Element', 'fusion-builder' );
						} elseif ( 'post_cards' === $term_name ) {
							$term_name = esc_html__( 'Post Card', 'fusion-builder' );
						}
						$display_terms .= '<span class="fusion-library-element-type fusion-library-element-' . esc_attr( $term->name ) . '"><a href="' . esc_url_raw( admin_url( 'admin.php?page=avada-library&type=' ) . $term->name ) . '">' . esc_html( $term_name ) . '</a></span>';
					}
				} else {
					$display_terms .= '<span class="fusion-library-element-type fusion-library-element-template"><a href="' . esc_url_raw( admin_url( 'admin.php?page=avada-library&type=template' ) ) . '">' . esc_html__( 'Template', 'fusion-builder' ) . '</a></span>';
				}

				$global = '';
				if ( 'yes' === get_post_meta( $element_post_id, '_fusion_is_global', true ) ) {
					$global  = '<a href="' . esc_url_raw( admin_url( 'admin.php?page=avada-library&type=global' ) ) . '"><span class="fusion-library-element-global"></span></a>';
					$global .= '<span class="fusion-library-global-sc"><input type="text" onfocus="this.select();" readonly="readonly" value=\'[fusion_global id="' . $element_post_id . '"]\'></span>';
				}

				$element_post = [
					'title'  => get_the_title(),
					'id'     => $element_post_id,
					'date'   => get_the_date( 'm/d/Y' ),
					'time'   => get_the_date( 'm/d/Y g:i:s A' ),
					'status' => get_post_status(),
					'global' => $global,
					'type'   => $display_terms,
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
		do_action( 'manage_fusion_element_custom_column', $column_id, $item );

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
		$wpnonce = wp_create_nonce( 'fusion-library' );
		$actions = [];

		if ( isset( $_GET['status'] ) && 'trash' === $_GET['status'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			$actions['restore'] = sprintf( '<a href="?_wpnonce=%s&action=%s&post=%s">' . esc_html__( 'Restore', 'fusion-builder' ) . '</a>', esc_attr( $wpnonce ), 'fusion_restore_element', esc_attr( $item['id'] ) );
			$actions['delete']  = sprintf( '<a href="?_wpnonce=%s&action=%s&post=%s">' . esc_html__( 'Delete Permanently', 'fusion-builder' ) . '</a>', esc_attr( $wpnonce ), 'fusion_delete_element', esc_attr( $item['id'] ) );
		} else {
			$actions = awb_get_list_table_edit_links( $actions, $item );

			if ( current_user_can( 'edit_others_posts' ) ) {
				$actions['clone_element'] = '<a href="' . $this->get_element_clone_link( $item['id'] ) . '" title="' . esc_attr( __( 'Clone this element', 'fusion-builder' ) ) . '">' . __( 'Clone', 'fusion-builder' ) . '</a>';
			}

			$actions['trash'] = sprintf( '<a href="?_wpnonce=%s&action=%s&post=%s">' . esc_html__( 'Trash', 'fusion-builder' ) . '</a>', esc_attr( $wpnonce ), 'fusion_trash_element', esc_attr( $item['id'] ) );
		}

		return awb_get_list_table_title( $item ) . ' ' . $this->row_actions( $actions );
	}

	/**
	 * Gets the link to clone an element.
	 *
	 * @access public
	 * @since 3.3
	 * @param int $id The item-id.
	 * @return string
	 */
	public function get_element_clone_link( $id ) {

		$args = [
			'_fusion_library_clone_nonce' => wp_create_nonce( 'clone_element' ),
			'item'                        => $id,
			'action'                      => 'clone_library_element',
		];

		$url = add_query_arg( $args, admin_url( 'admin.php' ) );

		return $url;
	}
	/**
	 * Set date column.
	 *
	 * @since 1.0
	 * @access public
	 * @param  array $item Data.
	 * @return string
	 */
	public function column_date( $item ) {
		$date_html = __( 'Published', 'fusion-builder' );
		if ( isset( $_GET['status'] ) && ( 'draft' === $_GET['status'] || 'trash' === $_GET['status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$date_html = esc_html__( 'Last Modified', 'fusion-builder' );
		}
		$date_html .= '<br/>';
		$date_html .= '<abbr title="' . $item['time'] . '">' . $item['date'] . '</abbr>';
		return $date_html;
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
				'fusion_trash_element' => esc_html__( 'Move To Trash', 'fusion-builder' ),
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
	 * Display custom text if library is empty.
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function no_items() {
		if ( isset( $_GET['status'] ) && 'trash' === $_GET['status'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			esc_attr_e( 'No Avada Library items found in Trash.', 'fusion-builder' );
		} else {
			esc_attr_e( 'Avada Library is empty.', 'fusion-builder' );
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
		$post_status     = [];
		$status_lists    = [];
		$count_posts     = [];
		$count_elements  = wp_count_posts( 'fusion_element' );
		$count_templates = wp_count_posts( 'fusion_template' );
		$count_elements  = (array) $count_elements;
		$count_templates = (array) $count_templates;
		$element_types   = [ 'sections', 'columns', 'elements', 'post_cards' ];

		$count_posts['publish'] = $count_elements['publish'] + $count_templates['publish'];
		$count_posts['trash']   = $count_elements['trash'] + $count_templates['trash'];

		if ( isset( $count_posts['publish'] ) && $count_posts['publish'] ) {
			$post_status['all'] = $count_posts['publish'];
		}

		$globals_query = new WP_Query(
			[
				'post_type'      => 'fusion_element',
				'posts_per_page' => '-1',
				'post_status'    => 'publish',
				'meta_key'       => '_fusion_is_global',
				'meta_value'     => 'yes',
			]
		);

		if ( isset( $count_templates['publish'] ) && $count_templates['publish'] ) {
			$post_status['template'] = $count_templates['publish'];
		}

		foreach ( $element_types as $type ) {
			$element = get_term_by( 'name', $type, 'element_category' );
			if ( $element ) {
				$post_status[ $type ] = $element->count;
			}
		}

		if ( $globals_query->have_posts() ) {
			$post_status['global'] = $globals_query->post_count;
		}

		if ( isset( $count_posts['trash'] ) && $count_posts['trash'] ) {
			$post_status['trash'] = $count_posts['trash'];
		}

		$status_html = '<ul class="subsubsub">';

		foreach ( $post_status as $status => $count ) {
			$current_type = 'all';

			if ( isset( $_GET['type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$current_type = sanitize_text_field( wp_unslash( $_GET['type'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}

			if ( isset( $_GET['status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$current_type = sanitize_text_field( wp_unslash( $_GET['status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}

			$current = ( $status === $current_type ) ? ' class="current" ' : '';

			$status_attr = ( 'all' !== $status ) ? '&type=' . $status : '';
			if ( 'trash' === $status ) {
				$status_attr = '&status=' . $status;
			}

			$status_title = $status;
			if ( 'publish' === $status ) {
				$status_title = esc_html__( 'Published', 'fusion-builder' );
			} elseif ( 'sections' === $status ) {
				$status_title = esc_html__( 'Containers', 'fusion-builder' );
			} elseif ( 'post_cards' === $status ) {
				$status_title = esc_html__( 'Post Cards', 'fusion-builder' );
			}

			$status_list  = '<li class="' . $status . '">';
			$status_list .= '<a href="' . admin_url( 'admin.php?page=avada-library' ) . $status_attr . '"' . $current . '>' . ucwords( $status_title );
			$status_list .= ' (' . $count . ')</a>';
			$status_list .= '</li>';

			$status_lists[] = $status_list;
		}

		$status_html .= implode( ' | ', $status_lists );
		$status_html .= '</ul>';

		echo $status_html; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

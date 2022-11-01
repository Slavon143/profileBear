<?php
/**
 * Handle critical CSS generating and rendering.
 *
 * @package fusion-builder
 * @since 3.4
 */

/**
 * Critical CSS.
 *
 * @since 3.4
 */
class AWB_Critical_CSS {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 3.4
	 * @var object
	 */
	private static $instance;

	/**
	 * Table version.
	 *
	 * @access protected
	 * @since 3.4
	 * @var string.
	 */
	protected $table_version;

	/**
	 * Table collation.
	 *
	 * @access protected
	 * @since 3.4
	 * @var string.
	 */
	protected $table_collation;

	/**
	 * Table name.
	 *
	 * @access protected
	 * @since 3.4
	 * @var string.
	 */
	protected $table_name = 'awb_critical_css';

	/**
	 * Table structure.
	 *
	 * @access protected
	 * @since 3.4
	 * @var array.
	 */
	protected $table = [];

	/**
	 * Page
	 *
	 * @access protected
	 * @since 3.4
	 * @var object.
	 */
	protected $page;

	/**
	 * CSS for current page.
	 *
	 * @access protected
	 * @since 3.4
	 * @var mixed.
	 */
	protected $css = [];

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new AWB_Critical_CSS();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	private function __construct() {
		$option_name     = class_exists( 'Fusion_Settings' ) ? Fusion_Settings::get_option_name() : 'fusion_options';
		$option          = get_option( $option_name, [] );
		$enable_critical = isset( $option['critical_css'] ) && '1' === $option['critical_css'];
		if ( ! apply_filters( 'enable_awb_critical_css', $enable_critical ) ) {
			return;
		}

		$this->table_version   = '3.4';
		$this->table_collation = 'utf8mb4_unicode_ci';

		// Create tables if needed.
		if ( ! get_option( 'awb_critical_table' ) || ( isset( $_GET['create_tables'] ) && $_GET['create_tables'] ) ) { // phpcs:ignore WordPress.Security
			$this->create_table();
		}

		// Critical CSS request.
		if ( ! empty( $_GET['mcritical'] ) || ! empty( $_GET['dcritical'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->generating_css();

			add_filter( 'body_class', [ $this, 'body_classes' ] );
		} else {

			// Check if we critical CSS.
			add_action( 'wp', [ $this, 'check_for_critical' ] );
		}

		// Load admin page.
		if ( is_admin() ) {
			add_action( 'init', [ $this, 'admin_init' ] );
		}
	}

	/**
	 * Admin init.
	 */
	public function admin_init() {
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/critical-css/class-awb-critical-css-table.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/critical-css/class-awb-critical-css-page.php';
		$this->page = new AWB_Critical_CSS_Page();
	}

	/**
	 * Generating CSS.
	 *
	 * @since 3.4
	 * @return void
	 */
	public function generating_css() {

		// Disable admin bar.
		show_admin_bar( false );

		// Disable QM if it exists.
		if ( class_exists( 'QM' ) ) {
			add_filter( 'user_has_cap', [ $this, 'disable_qm' ], 10, 1 );
		}

		// Disable animations.
		add_action(
			'wp_head',
			function() {
				echo '<style id="test-critical-css">.fusion-animated{ visibility: visible !important;}.fusion-menu-element-wrapper.loading { opacity: 1 !important; } .fusion-megamenu-wrapper { display: none !important; }</style>';
			}
		);

		// Emulate load as if mobile.
		if ( ! empty( $_GET['mcritical'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			add_filter(
				'wp_is_mobile',
				function() {
					return true;
				}
			);

			add_filter(
				'awb_device_detection_is_mobile',
				function() {
					return true;
				}
			);
		}
	}

	/**
	 * Disables query monitor in front-end builder mode.
	 *
	 * @param array $user_caps Array of user capabilities.
	 *
	 * @since 6.0
	 * @return array
	 */
	public function disable_qm( $user_caps ) {
		$user_caps['view_query_monitor'] = false;
		return $user_caps;
	}

	/**
	 * Check if mobile or not.
	 *
	 * @access public
	 * @since 3.4
	 */
	public function is_mobile() {
		return apply_filters( 'awb_critical_css_is_mobile', wp_is_mobile() );
	}

	/**
	 * Check if page load has critical CSS saved.
	 *
	 * @access public
	 * @since 3.4
	 */
	public function check_for_critical() {

		$this->css = apply_filters( 'awb_critical_css_block', $this->find_css() );

		// Check we have critical CSS entry.
		if ( ! empty( $this->css ) ) {

			// Check we have CSS for the current device.
			if ( ( $this->is_mobile() && ! empty( $this->css[0]->mobile_css ) ) || ( ! $this->is_mobile() && ! empty( $this->css[0]->desktop_css ) ) ) {
				add_filter( 'awb_defer_styles', '__return_true' );
				add_action( 'wp_head', [ $this, 'output_critical_css' ], -10 );
			}

			// Check we have preload for the current device and its not a global critical set.
			if ( false === strpos( $this->css[0]->css_key, 'global' ) && ( ( $this->is_mobile() && ! empty( $this->css[0]->mobile_preloads ) ) || ( ! $this->is_mobile() && ! empty( $this->css[0]->desktop_preloads ) ) ) ) {
				add_action( 'wp_head', [ $this, 'output_critical_preloads' ], -11 );
			}
		}
	}

	/**
	 * Find critical CSS for current page load.
	 *
	 * @access public
	 * @since 3.4
	 */
	public function find_css() {
		$post_id = fusion_library()->get_page_id();

		// If homepage, check if homepage critical is set.
		if ( is_front_page() ) {
			$home = $this->get(
				[
					'where' => [
						'css_key' => '"homepage"',
					],
				]
			);
			return $home;
		}

		// Check if we have a targeted critical CSS, takes priority over global/generic.
		if ( $post_id ) {
			$specific = $this->get(
				[
					'where' => [
						'css_key' => '"' . $post_id . '"',
					],
				]
			);
			if ( $specific ) {
				return $specific;
			}
		}

		// Single post type check if global is set.
		if ( is_singular() ) {
			$global = 'global_' . get_post_type();
			$post   = $this->get(
				[
					'where' => [
						'css_key' => '"' . $global . '"',
					],
				]
			);
			return $post;
		}

		return false;
	}

	/**
	 * Sets table structure.
	 *
	 * @access public
	 * @since 3.4
	 */
	public function set_table() {
		$this->table = [
			'unique_key'  => [ 'id' ],
			'primary_key' => 'id',
			'columns'     => [

				// CSS ID.
				[
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				],

				// The post ID string.
				[
					'name'      => 'css_key',
					'type'      => 'varchar(255)',
					'not_null'  => true,
					'collation' => $this->table_collation,
				],

				// The mobile critical CSS.
				[
					'name'      => 'mobile_css',
					'type'      => 'longtext',
					'not_null'  => true,
					'collation' => $this->table_collation,
				],

				// The desktop critical CSS.
				[
					'name'      => 'desktop_css',
					'type'      => 'longtext',
					'not_null'  => true,
					'collation' => $this->table_collation,
				],

				// The mobile preload tags.
				[
					'name'      => 'mobile_preloads',
					'type'      => 'longtext',
					'not_null'  => true,
					'collation' => $this->table_collation,
				],

				// The desktop preload tags.
				[
					'name'      => 'desktop_preloads',
					'type'      => 'longtext',
					'not_null'  => true,
					'collation' => $this->table_collation,
				],

				// Time critical CSS was created at.
				[
					'name'      => 'updated_at',
					'type'      => 'VARCHAR(25)',
					'not_null'  => true,
					'collation' => $this->table_collation,
				],
			],
		];
	}

	/**
	 * Create the critical CSS table.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function create_table() {
		global $wpdb;

		$this->set_table();

		// Save version of table construction.
		add_option( 'awb_critical_table', $this->table_version, '', false );

		// Include file from wp-core if not already loaded.
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$table_name = $this->table_name;
		$table      = $this->table;

		// Get collation.
		$charset_collate = $wpdb->get_charset_collate();
		$query_array     = [];

		/**
		 * Loop columns for this table.
		 *
		 * Generates the query fragment for this column
		 * which will be them used to build the final query.
		 */
		foreach ( $table['columns'] as $column ) {

			// Basic row properties.
			$query_fragment = [
				$column['name'],
				$column['type'],
			];

			// Add "NOT NULL" if needed.
			if ( isset( $column['not_null'] ) && $column['not_null'] ) {
				$query_fragment[] = 'NOT NULL';
			}

			// Add "AUTO_INCREMENT" if needed.
			if ( isset( $column['auto_increment'] ) && $column['auto_increment'] ) {
				$query_fragment[] = 'AUTO_INCREMENT';
			}

			// Add "DEFAULT" if needed.
			if ( isset( $column['default'] ) ) {
				$query_fragment[] = "DEFAULT {$column['default']}";
			}

			// Add our row to the query array.
			$query_array[] = implode( ' ', $query_fragment );
		}

		// Add "UNIQUE KEY" if needed.
		if ( isset( $table['unique_key'] ) ) {
			foreach ( $table['unique_key'] as $unique_key ) {
				$query_array[] = "UNIQUE KEY $unique_key ($unique_key)";
			}
		}

		// Add "PRIMARY KEY" if needed.
		if ( isset( $table['primary_key'] ) ) {
			$query_array[] = "PRIMARY KEY {$table['primary_key']} ({$table['primary_key']})";
		}

		// Build the query string.
		$columns_query_string = implode( ', ', $query_array );

		// Run the SQL query.
		dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}$table_name` ($columns_query_string) $charset_collate" );
	}

	/**
	 * Get total number of CSS posts.
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function get_total() {
		global $wpdb;

		$table_name  = $wpdb->prefix . $this->table_name;
		$count_query = "select count(*) from $table_name";
		return (int) $wpdb->get_var( $count_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
	}

	/**
	 * Get critical CSS entry.
	 *
	 * @param array $args An array of arguments for the query.
	 * @return array      An array of submissions.
	 */
	public function get( $args = [] ) {
		global $wpdb;

		$defaults = [
			'what'     => '*',
			'where'    => [],
			'order_by' => '',
			'order'    => 'ASC',
			'limit'    => '',
			'offset'   => 0,
		];
		$args     = wp_parse_args( $args, $defaults );

		// The table name.
		$table_name = $wpdb->prefix . $this->table_name;

		// The query basics.
		$query = 'SELECT ' . $args['what'] . " FROM `$table_name`";

		// Build the WHERE fragment of the query.
		if ( ! empty( $args['where'] ) ) {
			$where = [];
			foreach ( $args['where'] as $where_fragment_key => $where_fragment_val ) {
				if ( false === strpos( $where_fragment_val, 'LIKE' ) ) {
					$where[] = "$where_fragment_key = $where_fragment_val";
				} else {
					$where[] = "$where_fragment_key $where_fragment_val";
				}
			}

			$query .= ' WHERE ' . implode( ' AND ', $where );
		}

		// Build the ORDER BY fragment of the query.
		if ( '' !== $args['order_by'] ) {
			$order  = 'ASC' !== strtoupper( $args['order'] ) ? 'DESC' : 'ASC';
			$query .= ' ORDER BY ' . $args['order_by'] . ' ' . $order;
		}

		// Build the LIMIT fragment of the query.
		if ( '' !== $args['limit'] ) {
			$query .= ' LIMIT ' . absint( $args['limit'] );
		}

		// Build the OFFSET fragment of the query.
		if ( 0 !== $args['offset'] ) {
			$query .= ' OFFSET ' . absint( $args['offset'] );
		}

		return $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB
	}

	/**
	 * Insert critical css code.
	 *
	 * @param array $args An array of arguments for the query.
	 * @return array      An array of submissions.
	 */
	public function insert( $args = [] ) {
		global $wpdb;

		return $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prefix . $this->table_name,
			apply_filters( $this->table_name . '_insert_query_args', $args )
		);
	}

	/**
	 * Update item.
	 *
	 * @since 3.1
	 * @access public
	 * @param array        $data         Data to update (in column => value pairs).
	 * @param array        $where        A named array of WHERE clauses (in column => value pairs).
	 * @param array|string $format       An array of formats to be mapped to each of the values in $data.
	 * @param array|string $where_format An array of formats to be mapped to each of the values in $where.
	 * @return mixed
	 */
	public function update( $data, $where, $format = null, $where_format = null ) {
		global $wpdb;

		return $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prefix . $this->table_name,
			$data,
			$where,
			$format,
			$where_format
		);
	}

	/**
	 * Delete entries.
	 *
	 * @access public
	 * @since 3.1
	 * @param int|array $ids       The submission ID(s).
	 * @return void
	 */
	public function bulk_delete( $ids ) {
		global $wpdb;

		$ids = implode( ',', array_map( 'absint', $ids ) );
		$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . $this->table_name . " WHERE id IN ($ids)" ); // phpcs:ignore WordPress.DB
	}

	/**
	 * Get css_keys in bulk.
	 *
	 * @access public
	 * @since 3.1
	 * @param int|array $ids The submission ID(s).
	 * @return array
	 */
	public function get_bulk_css_keys( $ids ) {
		global $wpdb;

		$ids    = implode( ',', array_map( 'absint', $ids ) );
		$result = $wpdb->get_results( 'SELECT css_key FROM ' . $wpdb->prefix . $this->table_name . " WHERE id IN ($ids)", OBJECT_K ); // phpcs:ignore WordPress.DB

		return array_keys( $result );
	}

	/**
	 * Delete an entry.
	 *
	 * @access public
	 * @since 3.1
	 * @param int|array $where        The where param.
	 * @param string    $where_format The format of where clause.
	 * @return void
	 */
	public function delete( $where, $where_format = null ) {
		global $wpdb;

		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prefix . $this->table_name,
			$where,
			$where_format
		);
	}

	/**
	 * Output the critical CSS.
	 *
	 * @access public
	 * @since 3.4
	 * @return void
	 */
	public function output_critical_css() {
		if ( $this->is_mobile() ) {
			echo '<style id="awb-critical-css">' . $this->css[0]->mobile_css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo '<style id="awb-critical-css">' . $this->css[0]->desktop_css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Output the preload tags for images skipping lazy load.
	 *
	 * @access public
	 * @since 3.4
	 * @return void
	 */
	public function output_critical_preloads() {
		if ( $this->is_mobile() ) {
			echo wp_unslash( $this->css[0]->mobile_preloads ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo wp_unslash( $this->css[0]->desktop_preloads ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Calculate any extra classes for the <body> element.
	 *
	 * @param  array $classes CSS classes.
	 * @return array The needed body classes.
	 */
	public function body_classes( $classes ) {

		$classes[] = 'awb-generating-critical-css';

		return $classes;
	}
}

/**
 * Instantiates the AWB_Critical_CSS class.
 * Make sure the class is properly set-up.
 *
 * @since object 3.4
 * @return object AWB_Critical_CSS
 */
function AWB_Critical_CSS() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Critical_CSS::get_instance();
}
AWB_Critical_CSS();
/* Omit closing PHP tag to avoid "Headers already sent" issues. */

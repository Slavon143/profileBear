<?php
/**
 * Critical CSS management.
 *
 * @package fusion-builder
 * @since 3.4
 */

/**
 * Critical CSS Page.
 *
 * @since 3.4
 */
class AWB_Critical_CSS_Page {

	/**
	 * Current URL.
	 *
	 * @since 1.0
	 * @var string
	 */
	public $url = '';

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.1
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ], 12 );
		add_action( 'wp_ajax_awb_search_query', [ $this, 'search_query' ] );
		add_action( 'wp_ajax_awb_critical_new', [ $this, 'ajax_get_urls' ] );
		add_action( 'wp_ajax_awb_bulk_update_css', [ $this, 'ajax_get_bulk_urls' ] );
		add_action( 'wp_ajax_awb_critical_css', [ $this, 'ajax_save_css' ] );
		add_action( 'wp_ajax_awb_regenerate_critical_css', [ $this, 'ajax_regenerate_css' ] );

		if ( is_admin() ) {
			$this->bulk_actions();
		}
	}

	/**
	 * Get action based on request.
	 * phpcs:disable WordPress.Security
	 *
	 * @since 3.2
	 * @access public
	 */
	public function get_action() {

		if ( isset( $_REQUEST['action'] ) ) {
			if ( -1 !== $_REQUEST['action'] && '-1' !== $_REQUEST['action'] ) {
				return sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
			}
		}
		if ( isset( $_REQUEST['action2'] ) ) {
			if ( -1 !== $_REQUEST['action2'] && '-1' !== $_REQUEST['action2'] ) {
				return sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) );
			}
		}

		if ( ( isset( $_REQUEST['page'] ) && false !== strpos( $_REQUEST['page'], 'avada' ) ) && ( isset( $_REQUEST['action2'] ) || isset( $_REQUEST['action'] ) ) ) {
			$referer = fusion_get_referer();
			if ( $referer ) {
				wp_safe_redirect( $referer );
				exit;
			}
		}

		return false;
	}
	// phpcs:enable WordPress.Security

	/**
	 * Apply bulk action.
	 *
	 * @since 3.2
	 * @access public
	 */
	public function bulk_actions() {
		$action = $this->get_action();

		if ( $action ) {
			switch ( $action ) {
				case 'awb_bulk_delete_css':
					$this->bulk_delete_css();
					break;
				case 'delete_css':
					$this->delete_css();
					break;
				case 'clear_desktop_css':
					$this->clear_desktop_css();
					break;
				case 'clear_mobile_css':
					$this->clear_mobile_css();
					break;
			}
		}
	}

	/**
	 * Save the critical CSS received.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function ajax_save_css() {
		if ( wp_verify_nonce( 'fusion-page-options-nonce', 'security' ) ) {
			wp_send_json_error( __( 'Security check failed.', 'fusion-builder' ) );
		}

		$css_key = sanitize_text_field( wp_unslash( $_POST['post_id'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$css     = wp_unslash( $_POST['css'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( empty( $css ) ) {
			wp_send_json_error( __( 'No compiled CSS available for page.', 'fusion-builder' ) );
		}

		// Build array which will be inserted into DB.
		$db_data = [];

		// Get separate CSS for devices.
		if ( isset( $css['desktop'] ) ) {
			$db_data['desktop_css'] = is_array( $css['desktop'] ) ? $css['desktop'][0] : $css['desktop'];
		}

		if ( isset( $css['mobile'] ) ) {
			$db_data['mobile_css'] = is_array( $css['mobile'] ) ? $css['mobile'][0] : $css['mobile'];
		}

		// Check if we have any preloads.
		$preloads = isset( $_POST['preloads'] ) ? wp_unslash( $_POST['preloads'] ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( $preloads ) {
			if ( isset( $preloads['desktop'] ) ) {
				$db_data['desktop_preloads'] = ! empty( $preloads['desktop'] ) ? $this->preload_markup( $preloads['desktop'] ) : '';
			}

			if ( isset( $preloads['mobile'] ) ) {
				$db_data['mobile_preloads'] = ! empty( $preloads['mobile'] ) ? $this->preload_markup( $preloads['mobile'] ) : '';
			}
		}

		$db_data['updated_at'] = time();

		// Check if we have critical CSS already.
		$already_found = AWB_Critical_CSS()->get(
			[
				'where' => [
					'css_key' => '"' . $css_key . '"',
				],
			]
		);

		if ( $already_found && ! empty( $already_found ) ) {
			AWB_Critical_CSS()->update(
				$db_data,
				[ 'id' => $already_found[0]->id ],
				'%s',
				[ '%s' ]
			);
		} else {
			$db_data['css_key'] = $css_key;
			$insert             = AWB_Critical_CSS()->insert(
				$db_data,
				'%s'
			);
		}
		wp_send_json_success( __( 'CSS has been saved.', 'fusion-builder' ) );
	}

	/**
	 * Bulk delete the critical CSS.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function bulk_delete_css() {

		if ( wp_verify_nonce( 'fusion-page-options-nonce', 'security' ) || ! current_user_can( 'switch_themes' ) ) {
			return;
		}

		if ( ! isset( $_GET['post'] ) ) {
			$this->redirect_to_critical_css_page();
		}

		$ids = wp_unslash( $_GET['post'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		AWB_Critical_CSS()->bulk_delete( $ids );

		$this->redirect_to_critical_css_page();
	}

	/**
	 * Delete the critical CSS.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function delete_css() {

		if ( wp_verify_nonce( 'fusion-page-options-nonce', 'security' ) || ! current_user_can( 'switch_themes' ) ) {
			return;
		}

		$id = sanitize_text_field( wp_unslash( $_GET['post'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		AWB_Critical_CSS()->delete(
			[ 'id' => $id ],
			[ '%d' ]
		);

		$this->redirect_to_critical_css_page();
	}

	/**
	 * Clear desktop critical CSS.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function clear_desktop_css() {

		if ( wp_verify_nonce( 'fusion-page-options-nonce', 'security' ) || ! current_user_can( 'switch_themes' ) ) {
			return;
		}

		$id = sanitize_text_field( wp_unslash( $_GET['post'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		AWB_Critical_CSS()->update(
			[ 'desktop_css' => '' ],
			[ 'id' => $id ],
			null,
			[ '%d' ]
		);

		$this->redirect_to_critical_css_page();
	}

	/**
	 * Clear mobile critical CSS.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function clear_mobile_css() {

		if ( wp_verify_nonce( 'fusion-page-options-nonce', 'security' ) || ! current_user_can( 'switch_themes' ) ) {
			return;
		}

		$id = sanitize_text_field( wp_unslash( $_GET['post'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		AWB_Critical_CSS()->update(
			[ 'mobile_css' => '' ],
			[ 'id' => $id ],
			null,
			[ '%d' ]
		);

		$this->redirect_to_critical_css_page();
	}

	/**
	 * Redirect back to the main Critical CSS admin page.
	 * We want to strip get vars, but to keep pagination.
	 *
	 * @access protected
	 * @since 1.0
	 * @return void
	 */
	protected function redirect_to_critical_css_page() {

		$url = wp_unslash( $_SERVER['REQUEST_URI'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( isset( $_REQUEST['_wp_http_referer'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$referer = fusion_get_referer();

			if ( $referer ) {
				$url = $referer;
			}
		}

		$remove_actions = [ 'action', 'action2', 'post' ];
		$url            = remove_query_arg( $remove_actions, $url );

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Generate preload link for images found.
	 *
	 * @param array $preloads Array of preloads.
	 * @access public
	 * @since 3.4
	 * @return string
	 */
	public function preload_markup( $preloads = [] ) {
		$markup = '';
		foreach ( (array) $preloads as $preload ) {
			if ( 'background' === $preload['type'] || empty( $preload['srcset'] ) || empty( $preload['sizes'] ) ) {
				$markup .= '<link rel="preload" as="image" href="' . $preload['src'] . '">';
			} else {
				$markup .= '<link rel="preload" as="image" href="' . $preload['src'] . '" imagesrcset="' . $preload['srcset'] . '" imagesizes="' . $preload['sizes'] . '">';
			}
		}
		return $markup;
	}

	/**
	 * Get the URLs for the selection
	 *
	 * @access public
	 * @since 3.4
	 * @return void
	 */
	public function ajax_get_urls() {
		$urls = [];
		if ( wp_verify_nonce( 'fusion-page-options-nonce', 'fusion_po_nonce' ) ) {
			wp_send_json_error( __( 'Security check failed.', 'fusion-builder' ) );
		}

		if ( empty( $_GET['awb_critical_type'] ) ) {
			wp_send_json_error( __( 'No selection made.', 'fusion-builder' ) );
		}
		$type = sanitize_text_field( wp_unslash( $_GET['awb_critical_type'] ) );

		$urls = $this->get_urls( $type );
		wp_send_json_success( $urls );
	}

	/**
	 * Get the URLs for the selection
	 *
	 * @access public
	 * @since 3.4
	 * @return void
	 */
	public function ajax_get_bulk_urls() {
		$urls = [];
		if ( wp_verify_nonce( 'fusion-page-options-nonce', 'fusion_po_nonce' ) ) {
			wp_send_json_error( __( 'Security check failed.', 'fusion-builder' ) );
		}

		$ids      = wp_unslash( $_GET['post'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$css_keys = AWB_Critical_CSS()->get_bulk_css_keys( $ids );

		foreach ( $css_keys as $css_key ) {
			$urls += $this->get_urls( $css_key );
		}

		wp_send_json_success( $urls );
	}

	/**
	 * Get the URLs for the selection.
	 *
	 * @param array $type The URL type.
	 * @access protected
	 * @since 3.4
	 * @return array
	 */
	protected function get_urls( $type ) {
		$urls = [];

		switch ( $type ) {
			case 'homepage':
				$urls['homepage'] = get_home_url();
				break;

			case 'all_pages':
				$args  = [
					'post_type'      => 'page',
					'posts_per_page' => -1,
				];
				$pages = new WP_Query( $args );
				if ( $pages->have_posts() ) {
					foreach ( $pages->posts as $page ) {
						$urls[ $page->ID ] = get_permalink( $page->ID );
					}
				}
				break;

			case 'specific_pages':
				if ( empty( $_GET['_fusion']['_specific_pages'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					wp_send_json_error( __( 'No selection made.', 'fusion-builder' ) );
				}
				$pages = wp_unslash( $_GET['_fusion']['_specific_pages'] ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput

				foreach ( (array) $pages as $page ) {
					$url = get_permalink( $page );
					if ( $url ) {
						$urls[ $page ] = $url;
					}
				}
				break;

			default:
				if ( false !== strpos( $type, 'global_' ) ) {
					$post_type = str_replace( 'global_', '', $type );
					$args      = [
						'post_type'      => $post_type,
						'posts_per_page' => 1,
					];
					$pages     = new WP_Query( $args );
					if ( $pages->have_posts() ) {
						foreach ( $pages->posts as $page ) {
							$urls[ $type ] = get_permalink( $page->ID );
						}
					}
				}

				// Single post.
				if ( true === is_numeric( $type ) ) {
					$urls[ $type ] = get_permalink( $type );
				}
				break;

		}

		return $urls;
	}

	/**
	 * Regenerate CSS AJAX callback.
	 *
	 * @access protected
	 * @since 3.4
	 * @return void
	 */
	public function ajax_regenerate_css() {
		if ( wp_verify_nonce( 'fusion-page-options-nonce', 'fusion_po_nonce' ) ) {
			wp_send_json_error( __( 'Security check failed.', 'fusion-builder' ) );
		}

		if ( empty( $_GET['awb_critical_id'] ) ) {
			wp_send_json_error( __( 'No ID specified.', 'fusion-builder' ) );
		}

		$urls = [];

		$id = sanitize_text_field( wp_unslash( $_GET['awb_critical_id'] ) );

		$entry = AWB_Critical_CSS()->get(
			[
				'where' => [
					'id' => '"' . $id . '"',
				],
			]
		);

		if ( ! isset( $entry[0] ) || ! isset( $entry[0]->css_key ) ) {
			wp_send_json_error( __( 'Entry not found.', 'fusion-builder' ) );
		}

		$urls = $this->get_urls( $entry[0]->css_key );
		wp_send_json_success( $urls );
	}

	/**
	 * Return the search query.
	 *
	 * @access public
	 * @since 6.2.0
	 * @return array|void
	 */
	public function search_query() {
		$req_method  = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : 'GET'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$req         = $GLOBALS[ '_' . $req_method ];
		$return_data = [
			'results' => [],
			'labels'  => [],
		];

		// Check nonce.
		check_ajax_referer( 'fusion-page-options-nonce', 'fusion_po_nonce' );

		// Do search query.
		if ( isset( $req['search'] ) ) {
			$search = trim( sanitize_text_field( wp_unslash( $req['search'] ) ) );
			$params = isset( $req['params'] ) ? $req['params'] : [];

			// Terms search.
			if ( isset( $params['taxonomy'] ) ) {
				$terms = get_terms(
					[
						'taxonomy'   => $params['taxonomy'],
						'hide_empty' => false,
						'name__like' => $search,
					]
				);
				foreach ( $terms as $term ) {
					$return_data['results'][] = [
						'id'   => $term->term_id,
						'text' => $term->name,
					];
				}
			}

			// Post types search.
			if ( isset( $params['post_type'] ) ) {
				$args           = [
					's'         => $search, // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					'post_type' => $params['post_type']['name'],
				];
				$search_results = fusion_cached_query( $args );
				if ( $search_results->have_posts() ) {
					global $post;
					while ( $search_results->have_posts() ) {
						$search_results->the_post();
						$return_data['results'][] = [
							'id'   => esc_attr( $post->ID ),
							'text' => esc_html( get_the_title( $post->ID ) ),
						];
					}
				}
			}
		}

		// Get labels.
		if ( isset( $req['labels'] ) ) {
			$labels = $req['labels']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$params = isset( $req['params'] ) ? $req['params'] : [];

			// Terms search.
			if ( isset( $params['taxonomy'] ) ) {
				foreach ( $labels as $key => $label_id ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					$return_data['labels'][] = [
						'id'   => $label_id,
						'text' => get_term( $label_id, $params['taxonomy'] )->name,
					];
				}
			}
			// Post types search.
			if ( isset( $params['post_type'] ) ) {
				foreach ( $labels as $key => $label_id ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					$return_data['labels'][] = [
						'id'   => $label_id,
						'text' => get_the_title( $label_id ),
					];
				}
			}
		}

		echo wp_json_encode( $return_data );
		wp_die();
	}

	/**
	 * Add top level menu item for testing.
	 *
	 * @access public
	 * @since 3.4
	 * @return void
	 */
	public function add_menu_page() {
		add_submenu_page( 'avada', esc_html__( 'Critical CSS', 'fusion-builder' ), esc_html__( 'Critical CSS', 'fusion-builder' ), 'switch_themes', 'avada-critical', [ $this, 'render_page' ], 16 );
	}

	/**
	 * Render the page.
	 *
	 * @access public
	 * @since 3.4
	 * @return void
	 */
	public function render_page() {
		if ( ! class_exists( 'Avada' ) ) {
			return;
		}
		wp_enqueue_script(
			'critical-css-iframe',
			FUSION_BUILDER_PLUGIN_URL . 'inc/critical-css/admin/critical-calc.min.js',
			[ 'jquery' ],
			FUSION_BUILDER_VERSION,
			true
		);

		wp_enqueue_script(
			'critical-css-js',
			FUSION_BUILDER_PLUGIN_URL . 'inc/critical-css/admin/critical-css.js',
			[ 'jquery' ],
			FUSION_BUILDER_VERSION,
			true
		);

		wp_localize_script(
			'critical-css-js',
			'criticalCSS',
			[
				'ajaxurl'         => admin_url( 'admin-ajax.php' ),
				'gatheringURLs'   => esc_html__( 'Gathering the URLs for generation.', 'fusion-builder' ),
				'generateMobile'  => esc_html__( 'Generating the mobile CSS.', 'fusion-builder' ),
				'generateDesktop' => esc_html__( 'Generating the desktop CSS.', 'fusion-builder' ),
				'generatingError' => esc_html__( 'There was a problem while generating the CSS.', 'fusion-builder' ),
				'saving'          => esc_html__( 'Saving the generated CSS.', 'fusion-builder' ),
				'successComplete' => esc_html__( 'CSS successfully generated, refreshing page.', 'fusion-builder' ),
				'noURLs'          => esc_html__( 'No valid URLs found to generate.', 'fusion-builder' ),
				'noSelection'     => esc_html__( 'There is no CSS selected to process.', 'fusion-builder' ),
			]
		);

		wp_enqueue_style( 'avada_admin_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/avada-admin.css', [], AVADA_VERSION );

		wp_enqueue_style(
			'critical-css-css',
			FUSION_BUILDER_PLUGIN_URL . 'inc/critical-css/admin/critical-css.css',
			false,
			FUSION_BUILDER_VERSION
		);

		wp_dequeue_script( 'tribe-events-select2' );

		wp_enqueue_style(
			'select2-css',
			Avada::$template_dir_url . '/assets/admin/css/select2.css',
			[],
			'4.0.3',
			'all'
		);
		wp_enqueue_script(
			'selectwoo-js',
			Avada::$template_dir_url . '/assets/admin/js/selectWoo.full.min.js',
			[ 'jquery' ],
			'1.0.2',
			false
		);

		// Option type JS.
		wp_enqueue_script(
			'avada-fusion-options',
			Avada::$template_dir_url . '/assets/admin/js/avada-fusion-options.js',
			[ 'jquery', 'jquery-ui-sortable' ],
			FUSION_BUILDER_VERSION,
			false
		);
		?>
		<?php Fusion_Builder_Admin::header( 'critical' ); ?>
		<section class="avada-db-card avada-db-card-first awb-critical-hero">
			<div class="intro-text">
				<h1 class="avada-db-settings-heading"><?php esc_html_e( 'Manage Critical CSS', 'fusion-builder' ); ?></h1>
				<p><?php esc_html_e( 'Here you can manage critical CSS for your pages and posts.  Critical CSS is the CSS necessary for above the fold styling. When critical CSS is available the rest of the styles are deferred - leading to a faster render time.', 'fusion-builder' ); ?></p>

				<div class="avada-db-card-notice">
					<i class="fusiona-info-circle"></i>
					<p class="avada-db-card-notice-heading">
						<?php esc_html_e( 'Do not close the browser window while generating CSS.', 'fusion-builder' ); ?>
					</p>
				</div>
			</div>
			<form class="avada-db-create-form" id="awb-critical-form">
				<input type="hidden" name="action" value="awb_critical_new">

				<select id="awb-critical-type" name="awb_critical_type" >
					<option value="" disabled selected><?php esc_html_e( 'Select Page Type', 'fusion-builder' ); ?></option>
				<?php
					$fusion_settings = awb_get_fusion_settings();

					$types = [
						'homepage'       => esc_html__( 'Homepage', 'fusion-builder' ),
						'all_pages'      => esc_html__( 'All Pages', 'fusion-builder' ),
						'specific_pages' => esc_html__( 'Specific Pages', 'fusion-builder' ),
						'global_post'    => esc_html__( 'Global Single Post', 'fusion-builder' ),
					];

					if ( class_exists( 'FusionCore_Plugin' ) && $fusion_settings->get( 'status_fusion_portfolio' ) ) {
						$types['global_avada_portfolio'] = esc_html__( 'Global Single Portfolio', 'fusion-builder' );
					}
					if ( class_exists( 'WooCommerce' ) ) {
						$types['global_product'] = esc_html__( 'Global Single Product', 'fusion-builder' );
					}
					?>
				<?php foreach ( $types as $type_name => $type_label ) : ?>
					<option value="<?php echo esc_attr( $type_name ); ?>"><?php echo esc_html( $type_label ); ?></option>
				<?php endforeach; ?>

				</select>

				<?php
				if ( ! PyreThemeFrameworkMetaboxes::$instance ) {
					new PyreThemeFrameworkMetaboxes();
				}
				$metaboxes = PyreThemeFrameworkMetaboxes::$instance;

				$post_type   = get_post_type_object( 'page' );
				$selection   = [];
				$ajax        = 'awb_search_query';
				$ajax_params = [
					'post_type' => $post_type,
				];

				if ( 25 > wp_count_posts( $post_type->name )->publish ) {
					$ajax       = '';
					$field_type = 'select';
					$posts      = get_posts(
						[
							'post_type'   => $post_type->name,
							'numberposts' => -1, // phpcs:ignore WPThemeReview.CoreFunctionality.PostsPerPage
						]
					);
					foreach ( $posts as $single_post ) {
						$selection[ $single_post->ID ] = $single_post->post_title;
					}
				}

				/* translators: The name. */
				$placeholder = sprintf( esc_attr__( 'Select %s', 'Avada' ), $post_type->labels->name );
				$metaboxes->multiple( 'specific_pages', '', $selection, '', [], $ajax, $ajax_params, 50, $placeholder, false );
				?>

				<input type="hidden" id="fusion-page-options-nonce" value="<?php echo esc_attr( wp_create_nonce( 'fusion-page-options-nonce' ) ); ?>" />

				<input type="submit" value="<?php esc_attr_e( 'Generate Critical CSS', 'fusion-builder' ); ?>" class="button button-large button-primary avada-large-button" />

				<div class="awb-critical-loader">
					<span class="avada-db-loader"></span>
					<span class="loading-message"></span>
				</div>
			</form>

		</section>

		<div class="fusion-library-data-items avada-db-table">
			<?php
				$css_table = new AWB_Critical_CSS_Table();
				$css_table->get_status_links();
			?>
			<form id="awb-critical-css" method="get" data-nonce="<?php echo wp_create_nonce( 'critical-css' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>">
				<?php
				$css_table->prepare_items();
				$css_table->display();
				?>
			</form>
		</div>

		<div class="awb-admin-modal-wrap">
			<div class="awb-admin-modal-inner">

				<div class="awb-admin-modal-content">

					<h2 class="awb-critical-css-modal-title">
						<i class="fusiona-info-circle"></i>
						<?php echo esc_html( __( 'Generating Critical CSS', 'fusion-builder' ) ); ?>
					</h2>

					<div class="awb-critical-css-modal-text">
						<?php echo esc_html( __( 'During this process each page will be loaded in an iframe and scanned to generate the critical CSS path. It is important that you remain on this page until the process is completed. If you move page, the process will stop. Please also be patient as the generation can take time to complete.', 'fusion-builder' ) ); ?>
					</div>
				</div>

				<div class="awb-admin-modal-status-bar">
					<div class="awb-admin-modal-status-bar-label"><span></span></div>
					<div class="awb-admin-modal-status-bar-progress-bar"></div>

					<a class="button-done-demo demo-update-modal-close" href="#">Done</a>
				</div>
			</div>

			<a href="#" class="awb-admin-modal-corner-close"><span class="dashicons dashicons-no-alt"></span></a>
		</div>

		<div class="awb-modal-overlay"></div>
		<?php
	}
}

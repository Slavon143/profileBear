<?php
/**
 * Management of Avada Studio.
 *
 * @package fusion-builder
 * @since 3.5
 */

/**
 * Avada Studio Admin.
 *
 * @since 3.5
 */
class AWB_Studio_Admin {

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 3.5
	 */
	public function __construct() {
		// Sync ajax.
		add_action( 'wp_ajax_awb_studio_sync', [ $this, 'ajax_sync' ] );

		// Import Studio Post.
		add_action( 'wp_ajax_awb_studio_import', [ $this, 'ajax_import' ] );

		// Import Studio Media.
		add_action( 'wp_ajax_awb_studio_admin_import_media', [ $this, 'ajax_import_media' ] );

		add_action( 'avada_add_admin_menu_pages', [ $this, 'add_menu_page' ], 15 );
	}

	/**
	 * Add top level menu item.
	 *
	 * @access public
	 * @since 3.5
	 * @return void
	 */
	public function add_menu_page() {
		add_submenu_page( 'avada', esc_html__( 'Studio', 'fusion-builder' ), esc_html__( 'Studio', 'fusion-builder' ), 'switch_themes', 'avada-studio', [ $this, 'render_page' ], 4 );
	}

	/**
	 * Render the page.
	 *
	 * @access public
	 * @since 3.5
	 * @return void
	 */
	public function render_page() {
		if ( ! class_exists( 'Avada' ) ) {
			return;
		}
		$data = AWB_Studio()->get_data();

		wp_enqueue_style( 'avada_admin_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/avada-admin.css', [], AVADA_VERSION );

		wp_register_script( 'fuse-script', FUSION_LIBRARY_URL . '/assets/min/js/library/fuse.js', [], AVADA_VERSION, true );
		wp_enqueue_script(
			'avada-studio',
			Avada::$template_dir_url . '/assets/admin/js/awb-studio.js',
			[ 'jquery', 'fuse-script', 'imagesloaded' ],
			FUSION_BUILDER_VERSION,
			false
		);

		// Studio preview.
		wp_enqueue_script(
			'fusion-admin-notices',
			trailingslashit( Fusion_Scripts::$js_folder_url ) . 'general/awb-studio-preview-admin.js',
			[ 'jquery' ],
			FUSION_BUILDER_VERSION,
			false
		);

		wp_localize_script( 'avada-studio', 'fusionBuilderText', fusion_app_textdomain_strings() );

		wp_localize_script( 'avada-studio', 'awbStudioData', (array) $data );

		?>
		<?php Fusion_Builder_Admin::header( 'studio' ); ?>
		<?php if ( Avada()->registration->should_show( 'studio' ) ) : ?>
			<section class="avada-db-card avada-db-card-first awb-studio-start">
				<h1><?php esc_html_e( 'Avada Studio', 'fusion-builder' ); ?></h1>

				<p><?php esc_html_e( 'Here you can preview all Avada Studio content, and save any of the content blocks directly to your local Library.', 'fusion-builder' ); ?></p>

				<div class="avada-db-card-notice-button">
					<div class="avada-db-card-notice">
						<i class="fusiona-info-circle"></i>
						<p class="avada-db-card-notice-heading">
							<?php esc_html_e( 'Avada Studio content syncs daily. However, you can manually sync by clicking on the button.', 'fusion-builder' ); ?>
						</p>
					</div>
					<div class="avada-db-card-notice notice-button">
						<span class="awb-studio-sync-button"><a href="#" class="button awb-studio-sync" title="<?php esc_attr_e( 'Sync Avada Studio', 'fusion-builder' ); ?>"><span><?php esc_html_e( 'Sync Avada Studio', 'fusion-builder' ); ?></span></a><span>
					</div>
				</div>
			</section>

			<section class="avada-db-card awb-studio-categories">
				<ul>
					<li data-type="fusion_template" class="active">
						<i class="fusiona-template"></i>
						<span><?php esc_html_e( 'Templates', 'fusion-builder' ); ?></span>
					</li>
					<li data-type="header">
						<i class="fusiona-header"></i>
						<span><?php esc_html_e( 'Headers', 'fusion-builder' ); ?></span>
					</li>
					<li data-type="page_title_bar">
						<i class="fusiona-page_title"></i>
						<span><?php esc_html_e( 'Page Title Bars', 'fusion-builder' ); ?></span>
					</li>
					<li data-type="content">
						<i class="fusiona-content"></i>
						<span><?php esc_html_e( 'Content', 'fusion-builder' ); ?></span>
					</li>
					<li data-type="footer">
						<i class="fusiona-footer"></i>
						<span><?php esc_html_e( 'Footers', 'fusion-builder' ); ?></span>
					</li>
					<li data-type="sections">
						<i class="fusiona-container"></i>
						<span><?php esc_html_e( 'Containers', 'fusion-builder' ); ?></span>
					</li>
					<li data-type="columns">
						<i class="fusiona-column"></i>
						<span><?php esc_html_e( 'Columns', 'fusion-builder' ); ?></span>
					</li>
					<li data-type="elements">
						<i class="fusiona-element"></i>
						<span><?php esc_html_e( 'Elements', 'fusion-builder' ); ?></span>
					</li>
					<li data-type="icons">
						<i class="fusiona-icons"></i>
						<span><?php esc_html_e( 'Icons', 'fusion-builder' ); ?></span>
					</li>
					<li data-type="forms">
						<i class="fusiona-forms"></i>
						<span><?php esc_html_e( 'Forms', 'fusion-builder' ); ?></span>
					</li>
					<li data-type="post_cards">
						<i class="fusiona-post-cards-element"></i>
						<span><?php esc_html_e( 'Post Cards', 'fusion-builder' ); ?></span>
					</li>
					<li data-type="awb_off_canvas">
						<i class="fusiona-off-canvas"></i>
						<span><?php esc_html_e( 'Off Canvas', 'fusion-builder' ); ?></span>
					</li>
				</ul>
			</section>

			<?php if ( ! is_array( $data ) ) : ?>
				<div class="avada-db-card avada-db-notice"><?php esc_html_e( 'Sorry, could not fetch data from server. Please check your internet connection and then refresh the page to retry.', 'fusion-builder' ); ?></div>
			<?php else : ?>
				<section class="awb-studio-content">
					<div id="filter-bar" class="avada-db-card">
						<input id="search-input" type="search" placeholder="Search" />
						<nav data-type="templates">
							<?php if ( isset( $data['fusion_template'] ) && is_array( $data['fusion_template'] ) ) : ?>
							<a href="#" data-tag="all" class="active"><?php esc_html_e( 'All' ); ?> <span><?php echo count( $data['fusion_template'] ); ?></span></a>
							<?php endif; ?>
							<?php if ( isset( $data['studio_tags']['fusion_template'] ) && is_array( $data['studio_tags']['fusion_template'] ) ) : ?>
								<?php foreach ( $data['studio_tags']['fusion_template'] as $tag ) : ?>
									<a href="#" data-tag="<?php echo esc_attr( $tag['slug'] ); ?>"><?php echo esc_html( $tag['name'] ); ?><span><?php echo esc_attr( $tag['count'] ); ?></span></a>
								<?php endforeach; ?>
							<?php endif; ?>
						</nav>
					</div>
					<main id="main-content">
						<section class="previews">
						<?php if ( isset( $data['fusion_template'] ) && is_array( $data['fusion_template'] ) ) : ?>
							<?php foreach ( $data['fusion_template'] as $template ) : ?>
								<article data-type="fusion_template" data-id="<?php echo esc_attr( $template['ID'] ); ?>" data-url="<?php echo esc_attr( $template['url'] ); ?>">
									<?php if ( $template['thumbnail'] ) : ?>
									<div class="preview lazy-load">
										<img src="data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%27<?php echo esc_attr( $template['thumbnail']['width'] ); ?>%27%20height%3D%27<?php echo esc_attr( $template['thumbnail']['height'] ); ?>%27%20viewBox%3D%270%200%20<?php echo esc_attr( $template['thumbnail']['width'] ); ?>%20<?php echo esc_attr( $template['thumbnail']['height'] ); ?>%27%3E%3Crect%20width%3D%27<?php echo esc_attr( $template['thumbnail']['width'] ); ?>%27%20height%3D%273<?php echo esc_attr( $template['thumbnail']['height'] ); ?>%27%20fill-opacity%3D%220%22%2F%3E%3C%2Fsvg%3E" alt="" width="<?php echo esc_attr( $template['thumbnail']['width'] ); ?>" height="<?php echo esc_attr( $template['thumbnail']['height'] ); ?>" data-src="<?php echo esc_attr( $template['thumbnail']['url'] ); ?>" data-alt="<?php echo esc_attr( $template['post_title'] ); ?>"/>
									</div>
									<?php endif; ?>
									<div class="bar">
										<span class="fusion_module_title"><span class="awb-preview-title-text"><?php echo esc_html( $template['post_title'] ); ?></span></span>
										<span class="awb-studio-actions">
											<a href="#" class="awb-save" data-id="<?php echo esc_attr( $template['ID'] ); ?>"><i class="fusiona-plus"></i></a>
										</span>
									</div>
								</article>
							<?php endforeach; ?>
						<?php endif; ?>
						</section>
					</main>
				</section>
			<?php endif; ?>

			<div class="awb-studio-modal">
				<div class="post-modal-bg"></div>
				<div class="post-preview">
					<div id="fusion-loader"><span class="fusion-builder-loader"></span></div>
					<iframe class="awb-studio-preview-frame" frameborder="0" scrolling="auto" allowfullscreen=""></iframe>
					<?php AWB_Studio::studio_import_options_template( 'studio-admin' ); ?>
				</div>
			</div>

			<div class="awb-admin-modal-wrap">
				<div class="awb-admin-modal-inner">

					<div class="awb-admin-modal-content">

						<h2 class="awb-studio-modal-title">
							<i class="fusiona-info-circle"></i>
							<span><?php echo esc_html( __( 'Importing Avada Studio Content', 'fusion-builder' ) ); ?></span>
						</h2>

						<div class="awb-studio-modal-text">
							<?php echo esc_html( __( 'Your Studio content is now being imported. This includes the layout, and any assets that may be associated (images, menus, forms, post cards etc). The import process should only take a few seconds, depending on the amount of content to be imported.', 'fusion-builder' ) ); ?>
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

			<?php wp_nonce_field( 'awb_studio_nonce', 'awb-studio-nonce' ); ?>
		<?php else : ?>
			<div class="avada-db-card avada-db-notice">
			<h2><?php esc_html_e( 'Avada Studio Content Can Only Be Imported With Valid Product Registration', 'fusion-builder' ); ?></h2>

			<?php /* translators: "Product Registration" link. */ ?>
			<p><?php printf( esc_html__( 'Please visit the %s page and enter a valid purchase code to import Avada Studio conetnt.', 'fusion-builder' ), '<a href="' . esc_url_raw( admin_url( 'admin.php?page=avada#avada-db-registration' ) ) . '">' . esc_attr__( 'Product Registration', 'fusion-builder' ) . '</a>' ); ?></p>
		</div>
		<?php endif; ?>
		<?php Fusion_Builder_Admin::footer( 'studio' ); ?>
		<?php
	}

	/**
	 * Check if nonce is valid.
	 *
	 * @access public
	 */
	public function check_nonce() {
		check_admin_referer( 'awb_studio_nonce', 'awb_studio_nonce' );
	}

	/**
	 * Sync studio and results.
	 *
	 * @access public
	 */
	public function ajax_sync() {

		$this->check_nonce();

		if ( is_multisite() && is_main_site() ) {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				delete_transient( 'avada_studio' );
				restore_current_blog();
			}
			return;
		}
		delete_transient( 'avada_studio' );

		echo wp_json_encode( AWB_Studio()->get_data() );

		die();
	}

	/**
	 * Import Studio content from Admin page.
	 */
	public function ajax_import() {

		$this->check_nonce();

		$data_type = sanitize_text_field( wp_unslash( $_GET['data']['dataType'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$data_id   = sanitize_text_field( wp_unslash( $_GET['data']['dataID'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		if ( ! $data_type || ! $data_id ) {
			echo wp_json_encode( [] );
			die();
		}

		// Set import options from $_REQUEST global array.
		AWB_Studio_Import()->set_import_options_from_request();

		$post_details = AWB_Studio_Import()->import_post(
			[
				'post_id'   => $data_id,
				'post_type' => $data_type,
			],
			[],
			false
		);

		echo wp_json_encode( $post_details );
		die();
	}

	/**
	 * Import Studio content media from Admin page.
	 */
	public function ajax_import_media() {

		$this->check_nonce();

		$post_data = wp_unslash( $_POST['data']['postData'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		// Dont import content if post was already imported.
		if ( 'false' === $post_data['was_imported'] ) {
			$post_id   = absint( $post_data['post_id'] );
			$media_key = sanitize_text_field( wp_unslash( $_POST['data']['mediaImportKey'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			$overwrite = isset( $post_data['mapping'] ) ? $post_data['mapping'] : [];

			// We need it for post_content.
			$post = get_post( $post_id );

			// Set import options from $_REQUEST global array.
			AWB_Studio_Import()->set_import_options_from_request();

			// Import assets.
			AWB_Studio_Import()->import_post_media( $post_id, $post->post_content, [ $media_key => $post_data['avada_media'][ $media_key ] ], $overwrite );
		}

		echo wp_json_encode( $post_data );
		die();
	}
}

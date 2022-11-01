<?php
/**
 * Fusion Form Builder.
 *
 * @package Fusion-Builder
 * @since 3.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Fusion Form Builder class.
 *
 * @since 3.0
 */
class Fusion_Form_Builder {
	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 2.2
	 * @var object
	 */
	private static $instance;

	/**
	 * Form post type handle.
	 *
	 * @access private
	 * @since 7.0
	 * @var string
	 */
	private $post_type = 'fusion_form';

	/**
	 * Class constructor.
	 *
	 * @since 2.2
	 * @access private
	 */
	private function __construct() {
		if ( ! apply_filters( 'fusion_load_form_builder', true ) || false === self::is_enabled() ) {
			return;
		}

		$this->register_post_types();

		$this->setup_form_submit_functions();

		add_action( 'fusion_builder_shortcodes_init', [ $this, 'init_shortcodes' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

		add_action( 'wp_ajax_fusion_form_export', [ $this, 'ajax_export_entries' ] );
		add_action( 'admin_init', [ $this, 'export_entries' ] );

		// Enqueue styles on frontend.
		add_action( 'wp', [ $this, 'frontend_styles' ] );

		// Requirements for live editor.
		add_action( 'fusion_builder_load_templates', [ $this, 'load_component_templates' ] );
		add_action( 'fusion_builder_enqueue_separate_live_scripts', [ $this, 'load_component_views' ] );

		// Process action to update form view in database.
		add_action( 'wp_ajax_fusion_form_update_view', [ $this, 'fusion_form_update_view' ] );
		add_action( 'wp_ajax_nopriv_fusion_form_update_view', [ $this, 'fusion_form_update_view' ] );

		// Handles ajax request for removing form entry from database.
		add_action( 'wp_ajax_fusion_remove_form_entry', [ $this, 'remove_form_entry' ] );

		// Enqueue custom backbone templates for form creator.
		add_action( 'fusion_builder_after', [ $this, 'add_form_templates' ] );

		// New layout hook.
		add_action( 'admin_action_fusion_form_new', [ $this, 'add_new_form' ] );

		// Clone section.
		add_action( 'admin_action_clone_form', [ $this, 'maybe_clone_form' ] );

		// Overwrite page template for form preview.
		add_filter( 'template_include', [ $this, 'form_builder_form_preview_template' ] );

		// There should be a better way for this.
		add_action( 'wp_head', [ $this, 'get_form_data' ] );

		add_action( 'avada_dashboard_sticky_menu_items', [ $this, 'add_avada_dashboard_sticky_menu_items' ], 15 );

		// Force button if this is enabled.
		add_filter( 'fusion_is_fusion_button_enabled', '__return_true' );

		// Add wrapper class.
		add_action( 'fusion_builder_live_editor_wrapper_class', [ $this, 'add_wrapper_class' ] );

		add_action( 'wp', [ $this, 'wp' ] );

		add_action( 'after_setup_theme', [ $this, 'create_db_tables' ], 11 );

		// Predefined choices.
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/i18n/countries.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/i18n/days-of-the-week.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/i18n/months-of-the-year.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/i18n/us-states.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/i18n/years.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/i18n/days-of-the-month.php';
	}

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
			self::$instance = new Fusion_Form_Builder();
		}
		return self::$instance;
	}

	/**
	 * Checks if forms are enabled.
	 *
	 * @static
	 * @access public
	 * @since 3.1
	 * @return bool
	 */
	public static function is_enabled() {
		$fusion_settings = awb_get_fusion_settings();

		$status_fusion_forms = $fusion_settings->get( 'status_fusion_forms' );
		$status_fusion_forms = '0' === $status_fusion_forms ? false : true;
		return boolval( apply_filters( 'fusion_load_form_builder', $status_fusion_forms ) );
	}

	/**
	 * Instantiates the Fusion_Form_Submit object.
	 *
	 * @access public
	 * @since 3.1
	 * @return void
	 */
	public function setup_form_submit_functions() {
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-form-submit.php';
		new Fusion_Form_Submit();
	}

	/**
	 * Adds wrapper class to live editor.
	 *
	 * @access public
	 * @param string $classes Existing classes.
	 * @since 3.1
	 * @return string
	 */
	public function add_wrapper_class( $classes ) {

		if ( 'fusion_form' === get_post_type() && fusion_is_preview_frame() ) {
			$classes .= ' fusion-form fusion-form-builder fusion-form-form-wrapper';
		}
		return $classes;
	}

	/**
	 * Enqueue form styles on frontend.
	 *
	 * @since 2.3
	 * @access public
	 * @return void
	 */
	public function frontend_styles() {

		Fusion_Dynamic_CSS::enqueue_style(
			FUSION_BUILDER_PLUGIN_DIR . 'assets/css/fusion-form.min.css',
			FUSION_BUILDER_PLUGIN_URL . 'assets/css/fusion-form.min.css'
		);

		if ( is_rtl() ) {
			Fusion_Dynamic_CSS::enqueue_style(
				FUSION_BUILDER_PLUGIN_DIR . 'assets/css/fusion-form-rtl.min.css',
				FUSION_BUILDER_PLUGIN_URL . 'assets/css/fusion-form-rtl.min.css'
			);
		}
	}

	/**
	 * Update form view in database.
	 *
	 * @since 2.3
	 * @access public
	 * @return void
	 */
	public function fusion_form_update_view() {

		// No need for nonce check, we're just updating form view and sending back nonce.

		$form_id = '';
		if ( isset( $_POST['form_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$form_id = sanitize_text_field( wp_unslash( $_POST['form_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		if ( '' !== $form_id ) {
			$this->increase_view_count( $form_id );
		}

		// Send back nonce field.
		wp_nonce_field( 'fusion_form_nonce', 'fusion-form-nonce-' . absint( $form_id ), false, true );
		die();
	}

	/**
	 * Return whether or not a visitor can increase the form views.
	 *
	 * @since 3.5
	 * @param string|int $form_id The form id.
	 * @return bool
	 */
	public function can_increase_views_count( $form_id ) {
		$fusion_settings        = awb_get_fusion_settings();
		$increase_views_setting = $fusion_settings->get( 'form_views_counting' );

		$can_increase_views = false;

		if ( 'all' === $increase_views_setting ) {
			$can_increase_views = true;
		}

		if ( 'logged_out' === $increase_views_setting && ! is_user_logged_in() ) {
			$can_increase_views = true;
		}

		if ( 'non_admins' === $increase_views_setting && ! current_user_can( 'manage_options' ) ) {
			$can_increase_views = true;
		}

		$can_increase_views = apply_filters( 'fusion_forms_can_increase_views', $can_increase_views, $form_id );

		return $can_increase_views;
	}

	/**
	 * Increase form view count.
	 *
	 * @since 3.7
	 * @param string|int $form_id The form id.
	 * @return void
	 */
	public function increase_view_count( $form_id ) {
		if ( $this->can_increase_views_count( $form_id ) ) {
			$fusion_forms = new Fusion_Form_DB_Forms();
			$fusion_forms->insert(
				[
					'form_id' => $form_id,
					'views'   => 0,
				]
			);
			$fusion_forms->increment_views( $form_id );
		}
	}

	/**
	 * Add items to the Avada dashboard sticky menu.
	 *
	 * @access public
	 * @since 5.0
	 * @param string $screen The current screen.
	 * @return void
	 */
	public function add_avada_dashboard_sticky_menu_items( $screen ) {
		?>
			<li class="avada-db-menu-item avada-db-menu-item-forms"><a class="avada-db-menu-item-link<?php echo ( 'forms' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'forms' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-forms' ) ); ?>" ><i class="fusiona-forms"></i><span class="avada-db-menu-item-text"><?php esc_html_e( 'Forms', 'fusion-builder' ); ?></span></a>
				<ul class="avada-db-menu-sub avada-db-menu-sub-forms">
					<li class="avada-db-menu-sub-item avada-db-menu-sub-item-forms">
						<a class="avada-db-menu-sub-item-link<?php echo ( 'forms' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'forms' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-forms' ) ); ?>">
							<i class="fusiona-forms"></i>
							<div class="avada-db-menu-sub-item-text">
								<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Form Builder', 'fusion-builder' ); ?></div>
								<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'Build / Edit your Avada Forms.', 'fusion-builder' ); ?></div>
							</div>
						</a>
					</li>
					<li class="avada-db-menu-sub-item avada-db-menu-sub-item-form-entries">
						<a class="avada-db-menu-sub-item-link<?php echo ( 'form-entries' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'form-entries' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-form-entries' ) ); ?>">
							<i class="fusiona-content"></i>
							<div class="avada-db-menu-sub-item-text">
								<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Form Entries', 'fusion-builder' ); ?></div>
								<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'Manage your form entries.', 'fusion-builder' ); ?></div>
							</div>
						</a>
					</li>
				</ul>
			</li>
		<?php
	}

	/**
	 * Register the post types and taxonomies.
	 *
	 * @since 2.2
	 * @access public
	 */
	public function register_post_types() {
		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
		$args       = [
			'labels'              => [
				'name'          => _x( 'Avada Form', 'Post Type General Name', 'fusion-builder' ),
				'singular_name' => _x( 'Avada Form', 'Post Type Singular Name', 'fusion-builder' ),
				'add_new_item'  => _x( 'Add New Form', 'fusion-builder' ),
				'edit_item'     => _x( 'Edit Form', 'fusion-builder' ),
			],
			'public'              => false,
			'publicly_queryable'  => $is_builder,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'exclude_from_search' => true,
			'can_export'          => true,
			'query_var'           => true,
			'has_archive'         => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'supports'            => [ 'title', 'editor' ],
			'menu_icon'           => 'dashicons-fusiona-logo',
		];

		register_post_type( 'fusion_form', apply_filters( 'fusion_form_cpt_args', $args ) );
	}

	/**
	 * Init shortcode files specific to templates.
	 *
	 * @since 2.2
	 * @access public
	 */
	public function init_shortcodes() {

		// TODO: WHat is this ?.
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-form-db.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-form-list-table.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-form-widget.php';

		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-form-db-privacy.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-form-db-items.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-form-db-submissions.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-form-db-entries.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-form-db-forms.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-form-db-fields.php';

		new Fusion_Form_DB_Privacy();

		// Real shortcodes...
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/notice.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/text.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/password.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/textarea.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/number.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/email.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/phone-number.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/select.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/radio.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/image-select.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/recaptcha.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/checkbox.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/range.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/upload.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/date.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/time.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/rating.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/hidden.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/honeypot.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/fusion-form.php';
		require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/form/submit.php';
	}

	/**
	 * The main send function, handling the form submission.
	 *
	 * @since 2.3
	 * @access public
	 * @return void
	 */
	public function remove_form_entry() {
		// Verify the form submission nonce.
		check_ajax_referer( 'fusion_entry_nonce', 'fusion_entry_nonce' );

		if ( isset( $_POST['entry'] ) ) {
			$entry_id    = sanitize_text_field( wp_unslash( $_POST['entry'] ) );
			$submissions = new Fusion_Form_DB_Submissions();

			$submissions->delete( $entry_id );
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Enqueue required js on backend.
	 *
	 * @since 2.3
	 * @access public
	 * @return void
	 */
	public function admin_scripts() {
		global $pagenow, $typenow, $form_creator_fields;
		if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && post_type_supports( $typenow, 'editor' ) ) {
			if ( 'fusion_form' === $typenow ) {
				wp_enqueue_script( 'fusion_builder_form_blank', FUSION_BUILDER_PLUGIN_URL . 'js/views/view-blank-form.js', [], FUSION_BUILDER_VERSION, true );
			}
		}
	}

	/**
	 * Add templates required for form creator.
	 *
	 * @since 2.3
	 * @access public
	 * @return void
	 */
	public function add_form_templates() {
		include FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/blank-form.php';
	}


	/**
	 * Load the templates for live editor.
	 *
	 * @since 2.3
	 * @access public
	 */
	public function load_component_templates() {
		include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-blank-form.php';
		include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/text.php';
	}

	/**
	 * Load the views for the components.
	 *
	 * @since 2.3
	 * @access public
	 */
	public function load_component_views() {
		wp_enqueue_script( 'fusion_builder_blank_form', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-blank-form.js', [], FUSION_BUILDER_VERSION, true );
		wp_enqueue_script( 'fusion_builder_form_text', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-text.js', [], FUSION_BUILDER_VERSION, true );
	}

	/**
	 * Create a new form, fired from forms page.
	 */
	public function add_new_form() {
		check_admin_referer( 'fusion_new_form' );

		$post_type_object = get_post_type_object( $this->post_type );
		if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
			return;
		}

		$custom_icon_set = [
			'post_title'  => isset( $_GET['name'] ) ? sanitize_text_field( wp_unslash( $_GET['name'] ) ) : '',
			'post_status' => 'publish',
			'post_type'   => $this->post_type,
		];

		$set_id = wp_insert_post( $custom_icon_set );
		if ( is_wp_error( $set_id ) ) {
			$error_string = $set_id->get_error_message();
			wp_die( esc_html( $error_string ) );
		}

		// Just redirect to back-end editor.  In future tie it to default editor option.
		wp_safe_redirect( awb_get_new_post_edit_link( $set_id ) );
		die();
	}

	/**
	 * Saves a new form.
	 *
	 * @access public
	 * @since 3.0
	 */
	public function maybe_clone_form() {
		if ( ! ( isset( $_GET['item'] ) || isset( $_POST['item'] ) || ( isset( $_REQUEST['action'] ) && 'clone_form' === $_REQUEST['action'] ) ) ) { // phpcs:ignore WordPress.Security
			wp_die( esc_attr__( 'No form to clone.', 'fusion-builder' ) );
		}

		if ( isset( $_REQUEST['_fusion_form_clone_nonce'] ) && check_admin_referer( 'clone_form', '_fusion_form_clone_nonce' ) && current_user_can( 'edit_others_posts' ) ) {

			// Get the post being copied.
			$id   = isset( $_GET['item'] ) ? wp_unslash( $_GET['item'] ) : wp_unslash( $_POST['item'] ); // phpcs:ignore WordPress.Security
			$post = get_post( $id );

			// Copy the section and insert it.
			if ( isset( $post ) && $post ) {
				$this->clone_form( $post );

				// Redirect to the all sections screen.
				wp_safe_redirect( admin_url( 'admin.php?page=avada-forms' ) );

				exit;

			} else {

				/* translators: The ID not found. */
				wp_die( sprintf( esc_attr__( 'Cloning failed. Form not found. ID: %s', 'fusion-builder' ), htmlspecialchars( $id ) ) ); // phpcs:ignore WordPress.Security
			}
		}
	}

	/**
	 * Clones a section.
	 *
	 * @access public
	 * @since 3.0
	 * @param object $post The post object.
	 * @return int
	 */
	public function clone_form( $post ) {

		// Ignore revisions.
		if ( 'revision' === $post->post_type ) {
			return;
		}

		$post_meta       = fusion_data()->post_meta( $post->ID )->get_all_meta();
		$new_post_parent = $post->post_parent;

		$new_post = [
			'menu_order'     => $post->menu_order,
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $post->post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_mime_type' => $post->post_mime_type,
			'post_parent'    => $new_post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'publish',

			/* translators: The post title. */
			'post_title'     => sprintf( esc_attr__( '%s ( Cloned )', 'fusion-builder' ), $post->post_title ),
			'post_type'      => $post->post_type,
		];

		// Add new section post.
		$new_post_id = wp_insert_post( $new_post );

		// Set a proper slug.
		$post_name             = wp_unique_post_slug( $post->post_name, $new_post_id, 'publish', $post->post_type, $new_post_parent );
		$new_post              = [];
		$new_post['ID']        = $new_post_id;
		$new_post['post_name'] = $post_name;

		wp_update_post( $new_post );

		// Post terms.
		// TODO: Maybe copy terms.

		// Clone section meta.
		if ( ! empty( $post_meta ) ) {
			foreach ( $post_meta as $key => $val ) {
				fusion_data()->post_meta( $new_post_id )->set( $key, $val );
			}
		}

		return $new_post_id;
	}

	/**
	 * Display form preview.
	 *
	 * @since 2.3
	 * @param string $single_template Template file name or uri.
	 * @return array
	 */
	public function form_builder_form_preview_template( $single_template ) {
		global $post_type;

		wp_verify_nonce( 'preview_nonce' );

		$show_form_preview = isset( $_GET['preview'] ) && is_user_logged_in();
		$has_form_id       = ( isset( $_GET['preview_id'] ) && '' !== $_GET['preview_id'] ) || ( isset( $_GET['p'] ) && '' !== $_GET['p'] );
		$is_form           = 'fusion_form' === $post_type;

		if ( is_singular( 'fusion_form' ) || ( $is_form && $show_form_preview && $has_form_id ) ) {
			$single_template = FUSION_BUILDER_PLUGIN_DIR . 'templates/form-builder-preview.php';
		}

		return $single_template;
	}

	/**
	 * Sets the global $fusion_form var.
	 *
	 * @access public
	 * @since 3.1
	 * @return void
	 */
	public function get_form_data() {
		if ( 'fusion_form' === get_post_type() ) {
			global $fusion_form, $post;

			$fusion_form = Fusion_Builder_Form_Helper::fusion_form_set_form_data( $post->ID );
		}
	}

	/**
	 * Function attached to wp hook.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function wp() {
		if ( is_singular( 'fusion_form' ) ) {
			add_filter( 'the_content', [ $this, 'render_form' ] );
		}
	}

	/**
	 * We are viewing a form directly, replace it with the shortcode.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $content The content.
	 * @return string         The content, modified.
	 */
	public function render_form( $content ) {

		// So we only target main content.
		if ( ! fusion_doing_ajax() && is_main_query() && false !== strpos( $content, 'fusion_form_' ) && ! fusion_is_preview_frame() && ! fusion_is_builder_frame() ) {
			$content = '[fusion_form form_post_id="' . fusion_library()->get_page_id() . '" class="" id="" /]';
		}
		return $content;
	}
	/**
	 * Creates Avada Forms database tables.
	 */
	public function create_db_tables() {

		if ( true === FusionBuilder::is_upgrading() ) {
			// Include Form Installer.
			if ( ! class_exists( 'Fusion_Form_DB_Install' ) ) {
				include_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-form-db-install.php';
			}

			$fusion_form_db_install = new Fusion_Form_DB_Install();
			$fusion_form_db_install->create_tables();
		}
	}

	/**
	 * Export form entries for a specific form.
	 *
	 * @access public
	 * @since 3.3
	 * @return void.
	 */
	public function ajax_export_entries() {

		// Nonce check.
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'fusion-form-nonce' ) ) {
			echo wp_json_encode(
				[
					'status' => 'error_nonce',
				]
			);
			die();
		}

		$per_page     = apply_filters( 'fusion_form_export_per_step', 250 );
		$form_id      = isset( $_GET['formID'] ) ? absint( wp_unslash( $_GET['formID'] ) ) : 0;
		$current_page = isset( $_GET['currentPage'] ) ? absint( wp_unslash( $_GET['currentPage'] ) ) : 0;
		$total_pages  = isset( $_GET['totalPages'] ) ? absint( wp_unslash( $_GET['totalPages'] ) ) : 0;

		$status_message = 'export_processing';

		if ( current_user_can( 'export' ) ) {
			$submissions = new Fusion_Form_DB_Submissions();

			if ( 0 === $total_pages ) {
				$submission_args = [
					'what'  => 'COUNT(id) AS count',
					'where' => [ 'form_id' => (int) $form_id ],
				];
				$result          = $submissions->get( $submission_args );

				$total_count = $result[0]->count;
				$total_pages = (int) ceil( $total_count / $per_page );
			}

			// Get form post content.
			global $wpdb;
			$query             = "SELECT p.post_content FROM $wpdb->posts AS p INNER JOIN {$wpdb->prefix}fusion_forms AS ff ON p.ID = ff.form_id WHERE ff.id = %d";
			$results           = $wpdb->get_results( $wpdb->prepare( $query, (int) $form_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$form_post_content = $results && isset( $results[0] ) ? $results[0]->post_content : '';
			$field_labels      = [];
			$field_names       = [];

			// Get labels and names for all fields / inputs.
			if ( '' !== $form_post_content ) {
				// Get form field names.
				preg_match_all( '/\[fusion_form_[^\]]*\sname=\"([^\"]*)\"/', $form_post_content, $matches );
				$field_names = isset( $matches[1] ) ? $matches[1] : [];

				// Get form field labels.
				preg_match_all( '/\[fusion_form_[^\]]*\slabel=\"([^\"]*)\"/', $form_post_content, $matches );
				$field_labels = isset( $matches[1] ) ? $matches[1] : [];

				// If (some) labels are missing or empty use name instead.
				if ( count( $field_names ) !== count( array_filter( $field_labels ) ) || count( array_unique( $field_labels ) ) !== count( $field_labels ) ) {
					$field_labels = map_deep( $field_names, 'Fusion_Builder_Form_Helper::fusion_name_to_label' );
				}
			}

			$form_creator_list_table = new Fusion_Form_List_Table( $form_id );
			$form_creator_list_table->prepare_items( $per_page, $current_page );

			$upload_dir = wp_upload_dir();
			$file       = $upload_dir['basedir'] . '/form-' . $form_id . '-entries.csv';

			if ( 1 === $current_page && file_exists( $file ) ) {
				@unlink( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}

			$f      = fopen( $file, 'a' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$titles = 1 === $current_page ? false : true;

			if ( is_array( $form_creator_list_table->items ) && ! empty( $form_creator_list_table->items ) ) {
				foreach ( $form_creator_list_table->items as $form_entry ) {

					unset( $form_entry['Actions'] );
					if ( ! $titles ) {
						$titles = true;

						// Add BOM.
						fprintf( $f, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

						// Add title row.
						fputcsv( $f, $field_labels );
					}

					$csv_entry = [];
					foreach ( $field_labels as $label ) {
						$csv_entry[ $label ] = isset( $form_entry[ $label ] ) ? $form_entry[ $label ] : '';
					}
					fputcsv( $f, array_values( $csv_entry ) );
				}
			}
			fclose( $f ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}

		if ( $current_page === $total_pages ) {
			$status_message = 'export_done';
		}

		echo wp_json_encode(
			[
				'status'      => $status_message,
				'total_pages' => $total_pages,
			]
		);
		die();

	}

	/**
	 * Export form entries..
	 *
	 * @access public
	 * @since 3.3
	 * @return void.
	 */
	public function export_entries() {

		if ( isset( $_GET['page'] ) && 'avada-form-entries' === $_GET['page'] && isset( $_GET['nonce'] ) ) {

			// Nonce check.
			if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'fusion-form-nonce' ) ) {
				die();
			}

			$action  = filter_input( INPUT_GET, 'fusion_action', FILTER_SANITIZE_STRING );
			$form_id = filter_input( INPUT_GET, 'form_id', FILTER_SANITIZE_STRING );

			$upload_dir = wp_upload_dir();
			$file       = $upload_dir['basedir'] . '/form-' . $form_id . '-entries.csv';

			if ( file_exists( $file ) ) {
				$file_parts = pathinfo( $file );

				if ( 'csv' === $file_parts['extension'] ) {
					// Trigger file download.
					header( 'Content-type: application/csv' );
					header( 'Content-Disposition: attachment; filename=form-' . $form_id . '-entries.csv' );
					header( 'Content-Transfer-Encoding: UTF-8' );

					readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions

					echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

					@unlink( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

					die();
				}
			}
		}

	}
}

/**
 * Instantiates the Fusion_Form_Builder class.
 * Make sure the class is properly set-up.
 *
 * @since object 2.2
 * @return object Fusion_App
 */
function Fusion_Form_Builder() { // phpcs:ignore WordPress.NamingConventions
	return Fusion_Form_Builder::get_instance();
}
Fusion_Form_Builder();

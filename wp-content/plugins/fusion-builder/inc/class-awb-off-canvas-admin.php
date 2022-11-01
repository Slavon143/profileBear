<?php
/**
 * Backend Off Canvas class.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Fusion-Builder
 * @since      3.6
 */

/**
 * Adds Off Canvas feature.
 */
class AWB_Off_Canvas_Admin {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 3.6
	 * @var object
	 */
	private static $instance;

	/**
	 * Off Canvas post type handle.
	 *
	 * @access private
	 * @since 3.6
	 * @var string
	 */
	private $post_type = 'awb_off_canvas';

	/**
	 * The default template conditions.
	 *
	 * @access public
	 * @var array
	 */
	public static $default_conditions_data = [
		'conditions' => [],
	];

	/**
	 * The class constructor.
	 *
	 * @access private
	 * @since 3.6
	 * @return void
	 */
	private function __construct() {
		if ( ! apply_filters( 'fusion_load_off_canvas', true ) || false === self::is_enabled() ) {
			return;
		}

		// Add admin page.
		add_action( 'admin_action_awb_off_canvas_new', [ $this, 'add_new_off_canvas' ] );

		// clone off canvas.
		add_action( 'admin_action_clone_off_canvas', [ $this, 'clone_off_canvas' ] );

		// Enqueue the JS script for the PO layout conditions option.
		add_action( 'avada_page_option_scripts', [ $this, 'option_script' ], 10, 2 );

	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new AWB_Off_Canvas_Admin();
		}
		return self::$instance;
	}

	/**
	 * Checks if off canvas are enabled.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @return bool
	 */
	public static function is_enabled() {
		$fusion_settings = awb_get_fusion_settings();

		$status_awb_off_canvas = $fusion_settings->get( 'status_awb_Off_Canvas' );
		$status_awb_off_canvas = '0' === $status_awb_off_canvas ? false : true;
		return boolval( apply_filters( 'fusion_load_off_canvas', $status_awb_off_canvas ) );
	}

	/**
	 * Create a new off canvas, fired from off canvas page.
	 */
	public function add_new_off_canvas() {
		wp_safe_redirect( admin_url() );

		check_admin_referer( 'awb_off_canvas_new' );

		$post_type_object = get_post_type_object( $this->post_type );
		if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
			return;
		}

		$off_canvas = [
			'post_title'  => isset( $_GET['name'] ) ? sanitize_text_field( wp_unslash( $_GET['name'] ) ) : '',
			'post_status' => 'publish',
			'post_type'   => $this->post_type,
		];

		$set_id = wp_insert_post( $off_canvas );
		if ( is_wp_error( $set_id ) ) {
			$error_string = $set_id->get_error_message();
			wp_die( esc_html( $error_string ) );
		}

		// Just redirect to back-end editor.  In future tie it to default editor option.
		wp_safe_redirect( awb_get_new_post_edit_link( $set_id ) );
		die();
	}
	/**
	 * Saves a new element.
	 *
	 * @access public
	 * @since 3.6
	 */
	public function clone_off_canvas() {

		if ( ! ( isset( $_GET['item'] ) || isset( $_POST['item'] ) || ( isset( $_REQUEST['action'] ) && 'clone_off_canvas' === $_REQUEST['action'] ) ) ) { // phpcs:ignore WordPress.Security
			wp_die( esc_attr__( 'No element to clone.', 'fusion-builder' ) );
		}

		if ( isset( $_REQUEST['_fusion_library_clone_nonce'] ) && check_admin_referer( 'clone_off_canvas', '_fusion_library_clone_nonce' ) && current_user_can( 'edit_others_posts' ) ) {

			// Get the post being copied.
			$id   = isset( $_GET['item'] ) ? wp_unslash( $_GET['item'] ) : wp_unslash( $_POST['item'] ); // phpcs:ignore WordPress.Security
			$post = get_post( $id );

			// Copy the off canvas and insert it.
			if ( isset( $post ) && $post ) {
				$this->clone_off_canvas_post( $post );

				// Redirect to the all off canvass screen.
				wp_safe_redirect( admin_url( 'admin.php?page=avada-off-canvas' ) );

				exit;

			} else {

				/* translators: The ID not found. */
				wp_die( sprintf( esc_attr__( 'Cloning failed. Element not found. ID: %s', 'fusion-builder' ), htmlspecialchars( $id ) ) ); // phpcs:ignore WordPress.Security
			}
		}
	}

		/**
		 * Clones Off Canvas.
		 *
		 * @access public
		 * @since 3.6
		 * @param object $post The post object.
		 * @return int
		 */
	public function clone_off_canvas_post( $post ) {

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

		// Add new off canvas post.
		$new_post_id = wp_insert_post( $new_post );

		// Set a proper slug.
		$post_name             = wp_unique_post_slug( $post->post_name, $new_post_id, 'publish', $post->post_type, $new_post_parent );
		$new_post              = [];
		$new_post['ID']        = $new_post_id;
		$new_post['post_name'] = $post_name;

		wp_update_post( $new_post );

		// Clone off canvas meta.
		if ( ! empty( $post_meta ) ) {
			foreach ( $post_meta as $key => $val ) {
				fusion_data()->post_meta( $new_post_id )->set( $key, $val );
			}
		}

		return $new_post_id;
	}

	/**
	 * Add field data.
	 *
	 * @since 3.6
	 * @access public
	 * @param string $post_type Post type being added to.
	 * @return void
	 */
	public function option_script( $post_type ) {

		// Not editing a off canvas then we don't need it.
		if ( $this->post_type !== $post_type ) {
			return;
		}

		include FUSION_BUILDER_PLUGIN_DIR . 'inc/admin-screens/layout-builder/layout-options.php';
		include FUSION_BUILDER_PLUGIN_DIR . 'inc/admin-screens/layout-builder/layout-child-option.php';

		wp_enqueue_style( 'fusion_builder_admin_css', FUSION_BUILDER_PLUGIN_URL . 'assets/admin/css/fusion-builder-admin.css', [], FUSION_BUILDER_VERSION );

		wp_enqueue_script( 'fusion_builder_app_util_js', FUSION_LIBRARY_URL . '/inc/fusion-app/util.js', [ 'jquery', 'jquery-ui-core', 'underscore', 'backbone' ], FUSION_BUILDER_VERSION, true );
		wp_enqueue_script( 'fusion_layout_conditions', FUSION_BUILDER_PLUGIN_URL . 'assets/admin/js/layout-conditions-option.js', [ 'fusion_builder_app_util_js' ], FUSION_BUILDER_VERSION, false );
	}
}

/**
 * Instantiates the AWB_Off_Canvas_Admin class.
 * Make sure the class is properly set-up.
 *
 * @since object 3.6
 * @return object AWB_Off_Canvas_Admin
 */
function AWB_Off_Canvas_Admin() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Off_Canvas_Admin::get_instance();
}
AWB_Off_Canvas_Admin();

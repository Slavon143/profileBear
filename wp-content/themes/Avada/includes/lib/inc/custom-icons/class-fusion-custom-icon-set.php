<?php
/**
 * Main Custom Icons class.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Fusion-Library
 * @since      2.2
 */

/**
 * Adds Custom Icons feature.
 */
class Fusion_Custom_Icon_Set {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 6.2
	 * @var object
	 */
	private static $instance;

	/**
	 * WP Filesystem object.
	 *
	 * @access private
	 * @since 6.2
	 * @var object
	 */
	private $wp_filesystem;

	/**
	 * Icons post type handle.
	 *
	 * @access private
	 * @since 6.2
	 * @var string
	 */
	private $post_type = 'fusion_icons';

	/**
	 * Used to cache configs.
	 *
	 * @access private
	 * @since 6.2
	 * @var array
	 */
	private $package_config = [];

	/**
	 * Default post meta values.
	 *
	 * @access private
	 * @since 6.2
	 * @var array
	 */
	private $post_meta_defaults = [
		'attachment_id'     => '',
		'icon_set_dir_name' => '',
		'service'           => 'icomoon',
		'css_prefix'        => '',
		'icons'             => [],
	];

	/**
	 * Reserved CSS prefixes.
	 *
	 * @access private
	 * @since 6.2
	 * @var array
	 */
	private $reserved_css_prefixes = [
		'fusiona-',
	];

	/**
	 * The class constructor.
	 *
	 * @access private
	 * @since 6.2
	 * @return void
	 */
	private function __construct() {

		$this->wp_filesystem = Fusion_Helper::init_filesystem();

		// Register custom post type.
		add_action( 'init', [ $this, 'register_post_type' ] );

		// Front end styles.
		add_filter( 'fusion_dynamic_css_final', [ $this, 'combine_stylesheets' ] );

		// Live Builders scripts.
		add_action( 'fusion_enqueue_live_scripts', [ $this, 'enqueue_scripts' ] );

		// Dashboard scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );

		add_action( 'admin_head', [ $this, 'admin_head' ] );

		add_action( 'wp_ajax_fusion-icons-uploader-action', [ $this, 'handle_upload' ] );

		if ( is_admin() ) {

			// Add meta box.
			add_action( 'add_meta_boxes_' . $this->post_type, [ $this, 'add_meta_box' ] );

			// Save post meta.
			add_action( 'save_post_' . $this->post_type, [ $this, 'save_post_meta' ], 10, 3 );

			// Cleanup when post is deleted (trash emptied).
			add_action( 'before_delete_post', [ $this, 'delete_icon_set' ], 10, 1 );

			add_action( 'do_meta_boxes', [ $this, 'remove_revolution_slider_meta_box' ], 10, 3 );

			// Display 'duplicate css prefix note' if needed.
			add_action( 'admin_notices', [ $this, 'add_duplicate_prefix_notice' ], 1, 1 );

			// Process icon package when post is saved.
			add_action( $this->post_type . '_post_saved', [ $this, 'process_upload' ], 10, 1 );

			// Add admin page.
			add_action( 'admin_action_fusion_custom_icons_new', [ $this, 'add_new_custom_icon_set' ] );
		}
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 6.2
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new Fusion_Custom_Icon_Set();
		}
		return self::$instance;
	}

	/**
	 * Register custom post type.
	 *
	 * @since 6.2
	 * @return void
	 */
	public function register_post_type() {

		$labels = [
			'name'               => _x( 'Custom Icons', 'Avada Icon', 'Avada' ),
			'singular_name'      => _x( 'Icon Set', 'Avada Icon', 'Avada' ),
			'add_new'            => _x( 'Add New', 'Avada Icon', 'Avada' ),
			'add_new_item'       => _x( 'Add New Icon Set', 'Avada Icon', 'Avada' ),
			'edit_item'          => _x( 'Edit Icon Set', 'Avada Icon', 'Avada' ),
			'new_item'           => _x( 'New Icon Set', 'Avada Icon', 'Avada' ),
			'all_items'          => _x( 'All Icon Sets', 'Avada Icon', 'Avada' ),
			'view_item'          => _x( 'View Icon Set', 'Avada Icon', 'Avada' ),
			'search_items'       => _x( 'Search Icon Sets', 'Avada Icon', 'Avada' ),
			'not_found'          => _x( 'No Icon Sets found', 'Avada Icon', 'Avada' ),
			'not_found_in_trash' => _x( 'No Icon Sets found in Trash', 'Avada Icon', 'Avada' ),
			'parent_item_colon'  => '',
			'menu_name'          => _x( 'Custom Icons', 'Avada Icon', 'Avada' ),
		];

		$args = [
			'labels'              => $labels,
			'public'              => false,
			'rewrite'             => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'exclude_from_search' => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => [ 'title' ],
		];

		register_post_type( $this->post_type, apply_filters( 'fusion_custom_icons_args', $args ) ); // phpcs:ignore WPThemeReview.PluginTerritory.ForbiddenFunctions.plugin_territory_register_post_type
	}

	/**
	 * Removes Slider Revolution metabox from new / edit screen.
	 *
	 * @access public
	 * @since 6.2
	 *
	 * @param string $screen  Screen identifier.
	 * @param string $context The screen context for which to display meta boxes.
	 * @param object $post    Post object.
	 * @return void
	 */
	public function remove_revolution_slider_meta_box( $screen, $context, $post ) {

		if ( 'normal' !== $context ) {
			return;
		}

		remove_meta_box( 'mymetabox_revslider_0', $this->post_type, 'normal' );
	}

	/**
	 * Adds assets to the compiled CSS.
	 *
	 * @access public
	 * @since 3.4
	 * @param string $original_styles The compiled styles.
	 * @return string The compiled styles with any additional CSS appended.
	 */
	public function combine_stylesheets( $original_styles ) {

		$icon_style  = '';
		$icon_styles = '';
		$icon_sets   = fusion_get_custom_icons_array();

		foreach ( $icon_sets as $key => $icon_set ) {
			if ( isset( $icon_set['icon_set_dir_name'] ) && '' !== $icon_set['icon_set_dir_name'] ) {
				$icon_style_path = FUSION_ICONS_BASE_DIR . $icon_set['icon_set_dir_name'] . '/style.css';
				if ( file_exists( $icon_style_path ) ) {
					$icon_style   = fusion_file_get_contents( $icon_style_path );
					$icon_style   = str_replace( 'fonts/', FUSION_ICONS_BASE_URL . $icon_set['icon_set_dir_name'] . '/fonts/', $icon_style );
					$icon_styles .= $icon_style;
				}
			}
		}

		return $icon_styles . $original_styles;
	}

	/**
	 * Enqueue front end scripts, used in Live Editor.
	 *
	 * @since 2.2.0
	 * @return void
	 */
	public function enqueue_scripts() {
		global $fusion_library_latest_version;

		$icon_sets = fusion_get_custom_icons_array();

		foreach ( $icon_sets as $key => $icon_set ) {
			if ( isset( $icon_set['css_url'] ) && '' !== $icon_set['css_url'] ) {
				wp_enqueue_style( 'fusion-custom-icons-' . $key, $icon_set['css_url'], [], $fusion_library_latest_version, 'all' );
			}
		}
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since 2.2.0
	 * @param string $hook_suffix The current admin page.
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		global $fusion_library_latest_version, $typenow, $post;

		$current_screen_id = null;
		$allowed_screens   = [ 'nav-menus', 'appearance_page_avada_options' ];
		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen    = get_current_screen();
			$current_screen_id = $current_screen->id;
		}

		if ( 'post-new.php' !== $hook_suffix && 'post.php' !== $hook_suffix && ! in_array( $current_screen_id, $allowed_screens, true ) ) {
			return;
		}

		if ( get_post_type() === $this->post_type ) {

			$min = '';
			if ( ( ! defined( 'FUSION_LIBRARY_DEV_MODE' ) || ! FUSION_LIBRARY_DEV_MODE ) ) {
				$min = 'min/';
			}

			// Enqueue WP media.
			wp_enqueue_media();

			// Scripts.
			wp_enqueue_script( 'fusion-custom-icons', trailingslashit( FUSION_LIBRARY_URL ) . 'assets/' . $min . 'js/general/fusion-custom-icons.js', [ 'jquery' ], $fusion_library_latest_version, false );

			wp_enqueue_script( 'plupload-handlers' );

			// Styles.
			wp_enqueue_style( 'fusion-custom-icons', trailingslashit( FUSION_LIBRARY_URL ) . 'assets/css/fusion-custom-icons.css', [], $fusion_library_latest_version, 'all' );

			// Icon set is already saved.
			if ( 'post.php' === $hook_suffix ) {
				$css_url = fusion_get_custom_icons_css_url();

				if ( $css_url ) {
					wp_enqueue_style( 'fusion-custom-icons-style', $css_url, [], get_the_ID(), 'all' );
				}
			}
		}

		// Enqueue custom icon's styles.
		if ( isset( $typenow ) && class_exists( 'FusionBuilder' ) && in_array( $typenow, FusionBuilder::allowed_post_types(), true ) || in_array( $current_screen_id, $allowed_screens, true ) ) {

			$icon_sets = fusion_get_custom_icons_array();

			foreach ( $icon_sets as $key => $icon_set ) {
				if ( isset( $icon_set['css_url'] ) && '' !== $icon_set['css_url'] ) {
					wp_enqueue_style( 'fusion-custom-icons-' . $key, $icon_set['css_url'], [], $fusion_library_latest_version, 'all' );
				}
			}
		}
	}

	/**
	 * Adds the fusionUploaderOptions global var.
	 *
	 * @access public
	 * @since 2.2.0
	 * @return void
	 */
	public function admin_head() {
		$uploader_options = [
			'runtimes'            => 'html5,silverlight,flash,html4',
			'browse_button'       => 'fusion-icons-uploader-button',
			'container'           => 'fusion-icons-uploader-wrapper',
			'drop_element'        => 'fusion-icons-uploader-drop-zone',
			'file_data_name'      => 'async-upload',
			'multiple_queues'     => true,
			'max_file_size'       => wp_max_upload_size() . 'b',
			'url'                 => admin_url( 'admin-ajax.php' ),
			'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
			'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
			'filters'             => [
				[
					'title'      => __( 'Allowed Files' ),
					'extensions' => 'zip',
				],
			],
			'multipart'           => true,
			'urlstream_upload'    => true,
			'multi_selection'     => true,
			'multipart_params'    => [
				'_ajax_nonce' => '',
				'action'      => 'fusion-icons-uploader-action',
			],
		];
		?>
	<script type="text/javascript">
		var fusionUploaderOptions=<?php echo wp_json_encode( $uploader_options ); ?>;
	</script>
		<?php
	}

	/**
	 * Handles uploads.
	 *
	 * @access public
	 * @since 2.2.0
	 * @return void
	 */
	public function handle_upload() {

		// Check ajax nonce.
		check_ajax_referer( 'fusion-custom-icon-set' );

		if ( current_user_can( 'upload_files' ) ) {
			$response = [];

			// Handle file upload.
			$id = media_handle_upload(
				'async-upload',
				0,
				[
					'test_form' => true,
					'action'    => 'fusion-icons-uploader-action',
				]
			);

			// Send the file' url as response.
			if ( is_wp_error( $id ) ) {
				$response['status'] = 'error';
				$response['error']  = $id->get_error_messages();
			} else {
				$response['status'] = 'success';

				$response['attachment']       = [];
				$response['attachment']['id'] = $id;
			}
		}

		echo wp_json_encode( $response );
		die();
	}

	/**
	 * Display 'duplicate css prefix note' if needed.
	 *
	 * @since 6.2
	 * @return void
	 */
	public function add_duplicate_prefix_notice() {
		global $post, $pagenow;

		if ( ! is_admin() || 'post.php' !== $pagenow || $post->post_type !== $this->post_type ) {
			return;
		}

		$icon_set = fusion_data()->post_meta( $post->ID )->get( 'custom_icon_set' );

		if ( isset( $icon_set['css_prefix'] ) && true === $this->is_duplicate_prefix( $icon_set['css_prefix'] ) && class_exists( 'Fusion_Admin_Notice' ) ) {
			new Fusion_Admin_Notice(
				'fusion-custom-icons-notice',
				'<h2>' . esc_html__( 'Icon set with same CSS prefix already exists!', 'Avada' ) . '</h2><p>' . esc_html__( 'Icon set with same CSS prefix already exists! Please use unique prefix in order to avoid conflicts.', 'Avada' ) . '</p>',
				true,
				'error',
				true,
				'user_meta',
				'the-meta-custom-icons',
				[ 'fusion_icons' ]
			);
		}

	}

	/**
	 * Display 'duplicate css prefix note' if needed.
	 *
	 * @since 6.2
	 * @param int $post_id The post-ID.
	 * @return void
	 */
	protected function change_post_status_to_draft( $post_id ) {

		$duplicate_prefix = fusion_data()->post_meta( $post_id )->get( 'duplicate_css_prefix' );

		if ( true === $duplicate_prefix ) {

			wp_update_post(
				[
					'ID'          => $post_id,
					'post_status' => 'draft',
				]
			);
		}

	}

	/**
	 * Add metaboxes.
	 *
	 * @since 6.2
	 * @return void
	 */
	public function add_meta_box() {
		add_meta_box(
			'fusion-custom-icons-metabox',
			__( 'Icon Set', 'Avada' ),
			[ $this, 'render_metabox' ],
			$this->post_type,
			'normal',
			'default'
		);
	}

	/**
	 * Meta box callback, outputs metabox content.
	 *
	 * @since 6.2
	 * @return void
	 */
	public function render_metabox() {
		global $post;

		$icon_set = fusion_data()->post_meta( $post->ID )->get( 'custom_icon_set' );

		$icon_set = wp_parse_args( $icon_set, $this->post_meta_defaults );

		$is_new_icon_set = empty( $icon_set['icon_set_dir_name'] ) ? true : false;
		$buton_label     = $is_new_icon_set ? __( 'Browse Files', 'Avada' ) : __( 'Update Custom Icon Set', 'Avada' );
		$wrapper_class   = $is_new_icon_set ? 'fusion-no-custom-icons-uploaded' : 'fusion-custom-icons-uploaded';
		?>
		<div class="fusion-custom-icons-wrapper <?php echo esc_attr( $wrapper_class ); ?>">
			<div class="fusion-custom-icons-inner">

			<input type="hidden" id="fusion-custom-icons-attachment-id" name="fusion-custom-icons[attachment_id]" value="<?php echo esc_attr( $icon_set['attachment_id'] ); ?>">
			<input type="hidden" name="fusion-custom-icons-nonce" id="fusion-custom-icons-nonce" value="<?php echo esc_attr( wp_create_nonce( 'fusion-custom-icon-set' ) ); ?>">

			<?php if ( $is_new_icon_set ) : ?>

				<div id="fusion-icons-uploader-wrapper" class="fusion-icons-uploader multiple">
					<div id="fusion-icons-uploader-drop-zone">
						<div>
							<span class="fusiona-file-upload-solid fusion-icon-upload"></span>
							<h3>
								<?php esc_html_e( 'Drop zip file to upload', 'Avada' ); ?>
							</h3>
							<p>
								<?php
								printf(
									/* translators: %1$s: Link to Icomoon site. %2$s <br>. %3$s Note: */
									esc_html__(
										'Supported Icon Tool - %1$s %2$s %3$s Every uploaded custom icon set needs a unique font name and CSS class prefix.
									For more info, see the %4$s.',
										'Avada'
									),
									'<a href="https://icomoon.io/app/" target="_blank" rel="noreferrer">Icomoon</a>',
									'<br>',
									'<strong>' . esc_html__( 'Note:', 'Avada' ) . '</strong>',
									'<a href="https://theme-fusion.com/documentation/avada/how-to/how-to-upload-and-use-custom-icons-in-avada/" target="_blank" rel="noreferrer">Custom Icon documentation</a>'
								);
								?>
							</p>
							<input id="fusion-icons-uploader-button" type="button" value="<?php esc_attr_e( 'Select File' ); ?>" class="fusion-icons-uploader-button button">
							<div class="fusion-icons-spinner-wrapper">
								<span class="spinner"></span>
							</div>
							<div id="fusion-icons-error">
							</div>
						</div>
					</div>
				</div>

			<?php else : ?>

				<input type="hidden" id="fusion-custom-icons-update" name="fusion-custom-icons[icon_set_update]" value="">
				<div class="fusion-custom-icons-top-bar">
					<a href="#" id="fusion-custom-icons-upload" data-title="<?php echo esc_attr( $buton_label ); ?>">
						<i class="fusiona-file-upload-solid" aria-hidden="true"></i>
						<?php echo esc_html( $buton_label ); ?>
					</a>
					<?php
					$json_exists = method_exists( $this->wp_filesystem, 'exists' ) && $this->wp_filesystem->exists( FUSION_ICONS_BASE_DIR . $icon_set['icon_set_dir_name'] . '/selection.json' );
					if ( $json_exists ) :
						?>
					<a href="<?php echo FUSION_ICONS_BASE_URL . $icon_set['icon_set_dir_name'] . '/selection.json'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" id="fusion-custom-icons-json" target="_blank" data-title="<?php echo esc_attr( __( 'JSON', 'Avada' ) ); ?>">
						<?php echo esc_html( __( 'Download', 'Avada' ) ); ?>
					</a>
					<?php endif; ?>

					<div class="fusion-custom-icons-info">
						<span>
							<?php esc_html_e( 'CSS Prefix:', 'Avada' ); ?>
							<span class="fusion-custom-icons-value">
								<?php echo esc_html( '.' . $icon_set['css_prefix'] ); ?>
							</span>
						</span>

						<span>
							<?php esc_html_e( 'Icons Count:', 'Avada' ); ?>
							<span class="fusion-custom-icons-value">
								<?php echo esc_html( count( $icon_set['icons'] ) ); ?>
							</span>
						</span>
					</div>
				</div>
				<div class="fusion-custom-icons-preview">
					<?php

					// Print icons' markup.
					echo $this->get_icons_html(); // phpcs:ignore WordPress.Security.EscapeOutput
					?>
				</div>

				<?php
			endif;
			?>
			</div>
		</div>
		<?php
	}

	/**
	 * Save post meta.
	 *
	 * @since 6.2
	 * @param int    $post_id Post ID.
	 * @param object $post    Post Object.
	 * @param bool   $update  Whether this is an existing post being updated or not.
	 * @return void|int
	 */
	public function save_post_meta( $post_id, $post, $update ) {

		// Early exit if it is autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return $post_id;
		}

		// Check user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Check nonce.
		if ( ! isset( $_POST['fusion-custom-icons-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fusion-custom-icons-nonce'] ) ), 'fusion-custom-icon-set' ) ) {
			return $post_id;
		}

		// Process package.
		do_action( $this->post_type . '_post_saved', $post_id );
	}

	/**
	 * Processes icon package, exctracts files and saves post meta.
	 * Separate method as it might be needed to be called as AJAX callback.
	 *
	 * @since 6.2
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function process_upload( $post_id ) {

		// Early exit if post ID is not valid.
		if ( ! $post_id || ( isset( $_POST['fusion-custom-icons']['icon_set_update'] ) && 'true' !== $_POST['fusion-custom-icons']['icon_set_update'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		// Remove icon set files if we're updating.
		if ( isset( $_POST['fusion-custom-icons']['icon_set_update'] ) && 'true' === $_POST['fusion-custom-icons']['icon_set_update'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->delete_icon_set( $post_id );
		}

		$icon_set = [];

		// Get $_POST values and set defaults.
		foreach ( $this->post_meta_defaults as $key => $value ) {
			$icon_set[ $key ] = isset( $_POST['fusion-custom-icons'][ $key ] ) ? sanitize_text_field( wp_unslash( $_POST['fusion-custom-icons'][ $key ] ) ) : $this->post_meta_defaults[ $key ]; // phpcs:ignore WordPress.Security.NonceVerification
		}

		// Return if attachment ID is not set.
		if ( empty( $icon_set['attachment_id'] ) ) {
			return;
		}

		// Create base directory if it's not there.
		if ( ! file_exists( FUSION_ICONS_BASE_DIR ) ) {
			wp_mkdir_p( FUSION_ICONS_BASE_DIR );
		}

		// Get package path.
		$package_path = get_attached_file( $icon_set['attachment_id'] );
		$status       = false;

		if ( $package_path && file_exists( $package_path ) ) {
			// Create icon set path.
			$icon_set_dir_name = $this->get_unique_dir_name( pathinfo( $package_path, PATHINFO_FILENAME ), FUSION_ICONS_BASE_DIR );
			$icon_set_path     = FUSION_ICONS_BASE_DIR . $icon_set_dir_name;

			// Create icon set directory.
			wp_mkdir_p( $icon_set_path );

			// Attempt to manually extract the zip file first. Required for fptext method.
			if ( class_exists( 'ZipArchive' ) ) {
				$zip = new ZipArchive();
				if ( true === $zip->open( $package_path ) ) {
					$zip->extractTo( $icon_set_path );
					$zip->close();
					$status = true;
				}
			} else {
				$status = unzip_file( $package_path, $icon_set_path );
			}
		}

		// Update post meta if extract didn't fail.
		if ( true === $status ) {

			$icon_set['icon_set_dir_name'] = $icon_set_dir_name;

			// Parse package.
			$parsed_package = $this->parse_icons_package( $icon_set_dir_name );

			// Update post meta with package data.
			foreach ( $parsed_package as $key => $value ) {
				$icon_set[ $key ] = $parsed_package[ $key ];
			}

			$icon_set['icon_set_id'] = md5( $post_id . $icon_set['attachment_id'] );

			// Finally save post meta.
			fusion_data()->post_meta( $post_id )->set( 'custom_icon_set', $icon_set );

			// Add icon set id to attachment as well.
			update_post_meta( $icon_set['attachment_id'], '_fusion_icon_set_id', $icon_set['icon_set_id'] );
		}

	}

	/**
	 * Processes icon package, exctracts files and saves post meta.
	 * Separate method as it might be needed to be called as AJAX callback.
	 *
	 * @since 6.2
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function regenerate_icon_files( $post_id ) {

		// Early exit if post ID is not valid.
		if ( ! $post_id ) {
			return;
		}

		// Remove icon set files.
		$this->delete_icon_set( $post_id );

		$icon_set = fusion_data()->post_meta( $post_id )->get( 'custom_icon_set' );

		// Return if attachment ID is not set.
		if ( empty( $icon_set['attachment_id'] ) ) {
			return;
		}

		// Create base directory if it's not there.
		if ( ! file_exists( FUSION_ICONS_BASE_DIR ) ) {
			wp_mkdir_p( FUSION_ICONS_BASE_DIR );
		}

		// Get package path.
		$package_path = get_attached_file( $icon_set['attachment_id'] );
		$status       = false;

		if ( $package_path && file_exists( $package_path ) ) {
			// Create icon set path.
			$icon_set_dir_name = $this->get_unique_dir_name( pathinfo( $package_path, PATHINFO_FILENAME ), FUSION_ICONS_BASE_DIR );
			$icon_set_path     = FUSION_ICONS_BASE_DIR . $icon_set_dir_name;

			// Create icon set directory.
			wp_mkdir_p( $icon_set_path );

			// Attempt to manually extract the zip file first. Required for fptext method.
			if ( class_exists( 'ZipArchive' ) ) {
				$zip = new ZipArchive();
				if ( true === $zip->open( $package_path ) ) {
					$zip->extractTo( $icon_set_path );
					$zip->close();
					$status = true;
				}
			} else {
				$status = unzip_file( $package_path, $icon_set_path );
			}
		}

		// Update post meta if extract didn't fail.
		if ( true === $status ) {

			$icon_set['icon_set_dir_name'] = $icon_set_dir_name;

			// Parse package.
			$parsed_package = $this->parse_icons_package( $icon_set_dir_name );

			// Update post meta with package data.
			foreach ( $parsed_package as $key => $value ) {
				$icon_set[ $key ] = $parsed_package[ $key ];
			}

			// Update post meta.
			fusion_data()->post_meta( $post_id )->set( 'custom_icon_set', $icon_set );
		}

	}

	/**
	 * Checks if icon set with same prefix already exists.
	 *
	 * @since 6.2
	 * @param string $css_prefix Icon set prefix.
	 * @return bool
	 */
	protected function is_duplicate_prefix( $css_prefix ) {
		global $post;

		if ( $css_prefix ) {

			// Check if there is conflict with our icon fonts.
			if ( in_array( $css_prefix, $this->reserved_css_prefixes, true ) ) {
				return true;
			}

			// Exclude currently edited post.
			$custom_icon_sets = fusion_get_custom_icons_array( [ 'post__not_in' => [ $post->ID ] ] );

			foreach ( $custom_icon_sets as $custom_set ) {
				if ( $css_prefix === $custom_set['css_prefix'] ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Delete icon set directory and do general cleanup.
	 *
	 * @since 6.2
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function delete_icon_set( $post_id ) {

		if ( get_post_type( $post_id ) !== $this->post_type ) {
			return;
		}

		$icon_set = fusion_data()->post_meta( $post_id )->get( 'custom_icon_set' );

		if ( isset( $icon_set['icon_set_dir_name'] ) ) {
			$icon_set_path = FUSION_ICONS_BASE_DIR . $icon_set['icon_set_dir_name'];

			// Delete directory.
			$this->wp_filesystem->rmdir( $icon_set_path, true );
		}
	}

	/**
	 * Get unique directory name for passed parent directory.
	 *
	 * @since 6.2
	 * @param string $dir_name Name of the directory.
	 * @param string $parent_dir_path Path of the parent directory.
	 * @return string Unique directory name.
	 */
	protected function get_unique_dir_name( $dir_name, $parent_dir_path ) {

		$parent_dir_path = trailingslashit( $parent_dir_path );
		$dir_path        = $parent_dir_path . $dir_name;

		$counter  = 0;
		$tmp_name = $dir_name;
		while ( file_exists( $dir_path ) ) {
			$counter++;
			$dir_name = $tmp_name . '-' . $counter;
			$dir_path = $parent_dir_path . $dir_name;
		}

		return $dir_name;
	}

	/**
	 * Get package config.
	 *
	 * @since 6.2
	 * @param string $icon_set_dir_name Icon set dir name.
	 * @return array Config array.
	 */
	protected function get_package_config( $icon_set_dir_name ) {

		if ( ! isset( $this->package_config[ $icon_set_dir_name ] ) ) {
			$json_file                                  = $this->wp_filesystem->get_contents( FUSION_ICONS_BASE_DIR . '/' . $icon_set_dir_name . '/selection.json' );
			$this->package_config[ $icon_set_dir_name ] = json_decode( $json_file, true );
		}

		return $this->package_config[ $icon_set_dir_name ];
	}

	/**
	 * Parse package.
	 *
	 * @since 6.2
	 * @param string $icon_set_dir_name Icon set dir name.
	 * @return array Post meta array.
	 */
	protected function parse_icons_package( $icon_set_dir_name ) {

		// Get icons config file.
		$icons_config = $this->get_package_config( $icon_set_dir_name );

		$parsed_package          = [];
		$parsed_package['icons'] = [];

		// Add icons.
		foreach ( $icons_config['icons'] as $icon ) {
			$parsed_package['icons'][] = $icon['properties']['name'];
		}

		// Set icon prefix.
		$parsed_package['css_prefix'] = $icons_config['preferences']['fontPref']['prefix'];

		// Set icon count.
		$parsed_package['icon_count'] = count( $parsed_package['icons'] );

		return $parsed_package;
	}

	/**
	 * Get icon HTML code, for example to be used in a meta box.
	 *
	 * @since 6.2
	 * @param int $post_id Post ID.
	 * @return string HTML code.
	 */
	public function get_icons_html( $post_id = 0 ) {

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$icon_set = fusion_data()->post_meta( $post_id )->get( 'custom_icon_set' );

		$html = '';
		foreach ( $icon_set['icons'] as $icon ) {
			$html .= '<span class="fusion-custom-icon-preview" title="' . esc_attr( $icon ) . '"><i class="' . esc_attr( $icon_set['css_prefix'] . $icon ) . '" aria-hidden="true"></i><span class="fusion-custom-icon-preview-name">' . esc_html( $icon ) . '</span></span>';
		}

		return $html;
	}

	/**
	 * Create a new library element, fired from library page.
	 */
	public function add_new_custom_icon_set() {
		check_admin_referer( 'fusion_new_custom_icon_set' );

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
		wp_safe_redirect( get_edit_post_link( $set_id, false ) );
		die();
	}

}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

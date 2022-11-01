<?php
/**
 * Main Off Canvas class.
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
class AWB_Off_Canvas {

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
		// Register custom post type.
		add_action( 'init', [ $this, 'register_post_type' ] );

		// Overwrite page template for off canvas preview.
		add_filter( 'template_include', [ $this, 'off_canvas_preview_template' ] );

		// Enqueue the JS script for the PO layout conditions option.
		if ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() ) {
			add_action( 'wp_footer', [ $this, 'live_option_script' ] );
		}

		if ( ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || isset( $_GET['awb-studio-off-canvas'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			add_action( 'wp_enqueue_scripts', [ $this, 'styles' ] );
		}
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
			self::$instance = new AWB_Off_Canvas();
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
	 * Register custom post type.
	 *
	 * @since 3.6
	 * @return void
	 */
	public function register_post_type() {
		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

		$labels = [
			'name'               => _x( 'Off Canvas', 'Avada Off Canvas', 'fusion-builder' ),
			'singular_name'      => _x( 'Off Canvas', 'Avada Off Canvas', 'fusion-builder' ),
			'add_new'            => _x( 'Add New', 'Avada Off Canvas', 'fusion-builder' ),
			'add_new_item'       => _x( 'Add New Off Canvas', 'Avada Off Canvas', 'fusion-builder' ),
			'edit_item'          => _x( 'Edit Off Canvas', 'Avada Off Canvas', 'fusion-builder' ),
			'new_item'           => _x( 'New Off Canvas', 'Avada Off Canvas', 'fusion-builder' ),
			'all_items'          => _x( 'All Off Canvases', 'Avada Off Canvas', 'fusion-builder' ),
			'view_item'          => _x( 'View Off Canvas', 'Avada Off Canvas', 'fusion-builder' ),
			'search_items'       => _x( 'Search Off Canvases', 'Avada Off Canvas', 'fusion-builder' ),
			'not_found'          => _x( 'No Off Canvases found', 'Avada Off Canvas', 'fusion-builder' ),
			'not_found_in_trash' => _x( 'No Off Canvases found in Trash', 'Avada Off Canvas', 'fusion-builder' ),
			'parent_item_colon'  => '',
			'menu_name'          => _x( 'Off Canvas', 'Avada Off Canvas', 'fusion-builder' ),
		];

		$args = [
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => $is_builder,
			'rewrite'             => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'exclude_from_search' => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => [ 'title', 'editor' ],
		];

		register_post_type( $this->post_type, apply_filters( 'fusion_of_canvas_args', $args ) ); // phpcs:ignore WPThemeReview.PluginTerritory.ForbiddenFunctions.plugin_territory_register_post_type
	}

	/**
	 * Adds condition layout templates.
	 *
	 * @since 3.6
	 * @access public
	 * @return void
	 */
	public function live_option_script() {
		// Not editing a off canvas then we don't need it.
		if ( get_post_type() !== $this->post_type ) {
			return;
		}

		// Enqueue model.
		wp_enqueue_script( 'fusion_builder_off_canvas_styles', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-off-canvas-styles.js', [], FUSION_BUILDER_VERSION, true );

		// Include option templates.
		include FUSION_BUILDER_PLUGIN_DIR . 'inc/admin-screens/layout-builder/layout-options.php';
		include FUSION_BUILDER_PLUGIN_DIR . 'inc/admin-screens/layout-builder/layout-child-option.php';
	}

	/**
	 * Enqueue styles on frontend.
	 *
	 * @since 3.6
	 * @access public
	 * @return void
	 */
	public function styles() {

		// Not editing a off canvas then we don't need it.
		if ( get_post_type() !== $this->post_type ) {
			return;
		}

		Fusion_Dynamic_CSS::enqueue_style(
			FUSION_BUILDER_PLUGIN_DIR . 'assets/css/off-canvas.min.css',
			FUSION_BUILDER_PLUGIN_URL . 'assets/css/off-canvas.min.css'
		);
	}

	/**
	 * Display off canvas preview.
	 *
	 * @since 3.6
	 * @param string $single_template Template file name or uri.
	 * @return array
	 */
	public function off_canvas_preview_template( $single_template ) {
		global $post_type;

		wp_verify_nonce( 'preview_nonce' );

		if ( is_singular( 'awb_off_canvas' ) || 'awb_off_canvas' === $post_type ) {
			$single_template = FUSION_BUILDER_PLUGIN_DIR . 'templates/off-canvas-preview.php';
			if ( fusion_is_preview_frame() || isset( $_GET['awb-studio-off-canvas'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				add_action( 'awb_off_canvas_preview_content', [ $this, 'off_canvas_content' ], 0 );
			}
		}

		return $single_template;
	}

	/**
	 * Display off canvas preview.
	 *
	 * @since 3.6
	 * @return void
	 */
	public function off_canvas_content() {
		ob_start();
		// Handle preview for either Draft elements or Published ones.
		$query = new WP_Query(
			[
				'post_type'      => 'awb_off_canvas',
				'posts_per_page' => 1,
				'p'              => get_the_ID(),
			]
		);
		wp_verify_nonce( 'preview_nonce' );

		?>
			<?php while ( $query->have_posts() ) : ?>
				<?php $query->the_post(); ?>

					<?php echo AWB_Off_Canvas_Front_End::get_style( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<div <?php echo AWB_Off_Canvas_Front_End::wrap_attr( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo ! isset( $_GET['awb-studio-off-canvas'] ) ? 'style="opacity:0;"' : ''; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<div class="awb-off-canvas">
							<?php echo AWB_Off_Canvas_Front_End::close_button( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<div <?php echo AWB_Off_Canvas_Front_End::attr( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
								<div class="off-canvas-content">
									<?php the_content(); ?>
								</div>
							</div>
						</div>
					</div>
					<?php echo isset( $_GET['awb-studio-off-canvas'] ) ? AWB_Off_Canvas_Front_End::get_script( get_the_ID() ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			<?php endwhile; ?>
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Instantiates the AWB_Off_Canvas class.
 * Make sure the class is properly set-up.
 *
 * @since object 3.6
 * @return object AWB_Off_Canvas
 */
function AWB_Off_Canvas() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Off_Canvas::get_instance();
}
AWB_Off_Canvas();

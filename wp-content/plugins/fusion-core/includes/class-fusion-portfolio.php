<?php
/**
 * Fusion-Portfolio main class.
 *
 * @package Fusion-Portfolio
 * @since 5.1.2
 */

if ( ! class_exists( 'Fusion_Portfolio' ) ) {
	/**
	 * The main Fusion_Portfolio class.
	 */
	class Fusion_Portfolio {

		/**
		 * The one, true instance of this object.
		 *
		 * @static
		 * @access private
		 * @var object
		 */
		private static $instance;

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		private function __construct() {

			add_action( 'after_setup_theme', [ $this, 'add_image_size' ], 15 );

			// Provide single portfolio template via filter.
			add_filter( 'single_template', [ $this, 'portfolio_single_template' ] );

			// Provide archive portfolio template via filter.
			add_filter( 'archive_template', [ $this, 'fusion_portfolio_archive_template' ] );

			// Add thumbnails to portfolio.
			add_filter( 'manage_avada_portfolio_posts_columns', 'fusion_wp_list_add_column', 10 );
			add_action( 'manage_avada_portfolio_posts_custom_column', 'fusion_add_thumbnail_in_column', 10, 2 );

			add_action( 'wp_enqueue_scripts', [ $this, 'archive_script' ] );

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
				self::$instance = new Fusion_Portfolio();
			}
			return self::$instance;
		}

		/**
		 * Enqueue script for archives.
		 *
		 * @access public
		 * @since 3.2
		 */
		public function archive_script() {

			// Checks if the archive is portfolio.
			if ( is_post_type_archive( 'avada_portfolio' ) || is_tax( 'portfolio_category' ) || is_tax( 'portfolio_skills' ) || is_tax( 'portfolio_tags' ) ) {
				$this->enqueue_script();
			}
		}

		/**
		 * Enqueue script required for portfolio.
		 *
		 * @access public
		 * @since 3.2
		 */
		public function enqueue_script() {
			$fusion_settings = awb_get_fusion_settings();

			Fusion_Dynamic_JS::localize_script(
				'avada-portfolio',
				'avadaPortfolioVars',
				[
					'lightbox_behavior'     => $fusion_settings->get( 'lightbox_behavior' ),
					'infinite_finished_msg' => '<em>' . __( 'All items displayed.', 'fusion-core' ) . '</em>',
					'infinite_blog_text'    => '<em>' . __( 'Loading the next set of posts...', 'fusion-core' ) . '</em>',
					'content_break_point'   => intval( $fusion_settings->get( 'content_break_point' ) ),
				]
			);
			Fusion_Dynamic_JS::enqueue_script(
				'avada-portfolio',
				FusionCore_Plugin::$js_folder_url . '/avada-portfolio.js',
				FusionCore_Plugin::$js_folder_path . '/avada-portfolio.js',
				[ 'jquery', 'modernizr', 'fusion-video-general', 'fusion-lightbox', 'images-loaded', 'packery', 'isotope', 'jquery-infinite-scroll', 'fusion-carousel' ],
				FUSION_CORE_VERSION,
				true
			);
		}

		/**
		 * Load single portfolio template from FC.
		 *
		 * @access public
		 * @since 3.1
		 * @param string $single_post_template The post template.
		 * @return string
		 */
		public function portfolio_single_template( $single_post_template ) {
			global $post;

			// Check the post-type.
			if ( 'avada_portfolio' !== $post->post_type ) {
				return $single_post_template;
			}

			// The filename of the template.
			$filename = 'single-avada_portfolio.php';

			// Include template file from the theme if it exists.
			if ( locate_template( 'single-avada_portfolio.php' ) ) {
				return locate_template( 'single-avada_portfolio.php' );
			}

			// Include template file from the plugin.
			$single_portfolio_template = FUSION_CORE_PATH . '/templates/' . $filename;

			// Checks if the single post is portfolio.
			if ( file_exists( $single_portfolio_template ) ) {
				return $single_portfolio_template;
			}
			return $single_post_template;
		}

		/**
		 * Add image sizes.
		 *
		 * @access  public
		 */
		public function add_image_size() {
			add_image_size( 'portfolio-full', 940, 400, true );
			add_image_size( 'portfolio-one', 540, 272, true );
			add_image_size( 'portfolio-two', 460, 295, true );
			add_image_size( 'portfolio-three', 300, 214, true );
			add_image_size( 'portfolio-five', 177, 142, true );
		}

		/**
		 * Load portfolio archive template from FC.
		 *
		 * @access public
		 * @since 3.1
		 * @param string $archive_post_template The post template.
		 * @return string
		 */
		public function fusion_portfolio_archive_template( $archive_post_template ) {
			$archive_portfolio_template = FUSION_CORE_PATH . '/templates/archive-avada_portfolio.php';

			// Checks if the archive is portfolio.
			if ( is_post_type_archive( 'avada_portfolio' )
				|| is_tax( 'portfolio_category' )
				|| is_tax( 'portfolio_skills' )
				|| is_tax( 'portfolio_tags' ) ) {
				if ( file_exists( $archive_portfolio_template ) ) {
					if ( function_exists( 'fusion_portfolio_scripts' ) ) {
						fusion_portfolio_scripts();
					}
					return $archive_portfolio_template;
				}
			}
			return $archive_post_template;
		}

	}
}

/**
 * Instantiates the Fusion_Portfolio class.
 * Make sure the class is properly set-up.
 *
 * @return object Fusion_App
 */
function Fusion_Portfolio() { // phpcs:ignore WordPress.NamingConventions
	return Fusion_Portfolio::get_instance();
}
Fusion_Portfolio();

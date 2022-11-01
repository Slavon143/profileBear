<?php
/**
 * The main import handler.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Importer
 * @since      5.2
 */

/**
 * Import a demo.
 */
class Avada_Demo_Import {

	/**
	 * The demo type.
	 *
	 * @access private
	 * @since 5.2
	 * @var string
	 */
	private $demo_type;

	/**
	 * Path to the XML file.
	 *
	 * @access private
	 * @since 5.2
	 * @var string
	 */
	private $theme_xml;

	/**
	 * Path to the theme-options file.
	 *
	 * @access private
	 * @since 5.2
	 * @var string
	 */
	private $theme_options_file;

	/**
	 * Path to the widgets file.
	 *
	 * @access private
	 * @since 5.2
	 * @var string
	 */
	private $widgets_file;

	/**
	 * Path to the Fusion-Slider file.
	 *
	 * @access private
	 * @since 5.2
	 * @var string
	 */
	private $fs_url;

	/**
	 * Whether we should fetch attachments or not.
	 *
	 * @access private
	 * @since 5.2
	 * @var bool
	 */
	private $fetch_attachments;

	/**
	 * Whether this is a WooCommerce site or not.
	 *
	 * @access private
	 * @since 5.2
	 * @var bool
	 */
	private $shop_demo;

	/**
	 * The sidebars.
	 *
	 * @access private
	 * @since 5.2
	 * @var array
	 */
	private $sidebars;

	/**
	 * The Homepage title.
	 *
	 * @access private
	 * @since 5.2
	 * @var string
	 */
	private $homepage_title;

	/**
	 * WooCommerce pages.
	 *
	 * @access private
	 * @since 5.2
	 * @var array
	 */
	private $woopages;

	/**
	 * Whether Fusion-Slider exists or not.
	 *
	 * @access private
	 * @since 5.2
	 * @var bool
	 */
	private $fs_exists;

	/**
	 * Avada_Importer_Data instance.
	 *
	 * @access private
	 * @since 5.2
	 * @var object
	 */
	private $importer_files;

	/**
	 * Avada_Demo_Content_Tracker instance.
	 *
	 * @access private
	 * @since 5.2
	 * @var object
	 */
	private $content_tracker;

	/**
	 * The content-types we'll be importing.
	 *
	 * @access private
	 * @since 5.2
	 * @var array
	 */
	private $import_content_types;

	/**
	 * An array of allowed post-types.
	 *
	 * @access private
	 * @since 5.2
	 * @var array
	 */
	private $allowed_post_types = [];

	/**
	 * An array of allowed taxonomies.
	 *
	 * @access private
	 * @since 5.2
	 * @var array
	 */
	private $allowed_taxonomies = [];

	/**
	 * Whether we want to import everything or not.
	 *
	 * @access private
	 * @since 5.2
	 * @var bool
	 */
	private $import_all;

	/**
	 * Import stages still left to process.
	 *
	 * @access private
	 * @since 6.2
	 * @var array
	 */
	private $import_stages = [];

	/**
	 * Site title.
	 *
	 * @access private
	 * @since 7.7
	 * @var string
	 */
	private $site_title = '';

	/**
	 * The class constructor.
	 *
	 * @access public
	 * @since 5.2
	 */
	public function __construct() {

		// Hook importer into admin init.
		add_action( 'wp_ajax_fusion_import_demo_data', [ $this, 'ajax_import_demo_stage' ] );

		// Modify Theme Options before import.
		add_filter( 'avada_import_modify_theme_options', [ $this, 'modify_theme_options' ], 10, 1 );
	}

	/**
	 * The main importer function.
	 *
	 * @access public
	 * @since 5.2
	 */
	public function ajax_import_demo_stage() {

		check_ajax_referer( 'avada_demo_ajax', 'security' );

		if ( current_user_can( 'manage_options' ) ) {

			if ( isset( $_POST['importStages'] ) ) {
				$this->import_stages = wp_unslash( $_POST['importStages'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			}

			$this->demo_type = 'classic';
			if ( isset( $_POST['demoType'] ) && '' !== sanitize_text_field( wp_unslash( $_POST['demoType'] ) ) ) {
				$this->demo_type = sanitize_text_field( wp_unslash( $_POST['demoType'] ) );
			}

			$this->fetch_attachments = false;
			if ( isset( $_POST['fetchAttachments'] ) && 'true' === sanitize_text_field( wp_unslash( $_POST['fetchAttachments'] ) ) ) {
				$this->fetch_attachments = true;
			}

			$this->import_content_types = [];
			if ( isset( $_POST['contentTypes'] ) && is_array( $_POST['contentTypes'] ) ) {
				$this->import_content_types = wp_unslash( $_POST['contentTypes'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			}

			$this->import_all = false;
			if ( isset( $_POST['allImport'] ) && 'true' === sanitize_text_field( wp_unslash( $_POST['allImport'] ) ) ) {
				$this->import_all = true;
			}

			if ( isset( $_POST['siteTitle'] ) && '' !== sanitize_text_field( wp_unslash( $_POST['siteTitle'] ) ) ) {
				$this->site_title = sanitize_text_field( wp_unslash( $_POST['siteTitle'] ) );
			}

			try {
				$this->run_demo_stage_import();

				// We've just processed last import stage.
				if ( 1 === count( $this->import_stages ) ) {

					/**
					 * WIP
					$this->content_tracker->set_general_data();
					*/
					$this->after_import();

					// Reset all caches, don't remove demo data.
					fusion_reset_all_caches(
						[
							'demo_data' => false,
						]
					);

					echo 'imported';
				} else {
					echo 'import partially completed: ' . $this->import_stages[0]; // phpcs:ignore WordPress.Security.EscapeOutput
				}
				// Save data after import, for example imported terms.
				$this->content_tracker->save_demo_history();
			} catch ( Throwable $t ) {
				// Executed only in PHP 7+.

				/* translators: %1$s: Opening span tag. %2$s: PHP error message. %3$s: PHP file path. %4$s: PHP file line. %5$s: Closing span tag. */
				$response = sprintf( __( '%1$s<strong>PHP ERROR</strong>: %2$s in %3$s on line %4$s.%5$s', 'Avada' ), '<span class="awb-prebuilt-import-error">', $t->getMessage(), $t->getFile(), $t->getLine(), '</span>' );
				/* translators: %1$s: Opening span tag. %2$: Opening anchor tag. %3$s: closing ancor tag. %4$s: Closing span tag. */
				$response .= sprintf( __( '%1$sIf you need help to debug this, please create a %2$s support ticket%3$s.%4$s', 'Avada' ), '<span class="awb-prebuilt-import-message">', '<a href="https://theme-fusion.com/support/submit-a-ticket/" target="_blank">', '</a>', '</span>' );

				// Echo response message.
				echo $response; // phpcs:ignore WordPress.Security.EscapeOutput

				// Sending 500 response to the client side (ajax request).
				http_response_code( 500 );
			}

			exit;
		}
	}

	/**
	 * The main importer function.
	 *
	 * @access public
	 * @since 7.3
	 * @param array $args CLI arguments.
	 */
	public function import_demo_stage( $args ) {

		if ( defined( 'WP_CLI' ) && WP_CLI ) {

			if ( isset( $args['importStages'] ) ) {
				$this->import_stages = wp_unslash( $args['importStages'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			}

			$this->demo_type = 'classic';
			if ( isset( $args['demoType'] ) && '' !== sanitize_text_field( wp_unslash( $args['demoType'] ) ) ) {
				$this->demo_type = sanitize_text_field( wp_unslash( $args['demoType'] ) );
			}

			$this->fetch_attachments = false;
			if ( isset( $args['fetchAttachments'] ) && '1' === sanitize_text_field( wp_unslash( $args['fetchAttachments'] ) ) ) {
				$this->fetch_attachments = true;
			}

			$this->import_content_types = [];
			if ( isset( $args['contentTypes'] ) && is_array( $args['contentTypes'] ) ) {
				$this->import_content_types = wp_unslash( $args['contentTypes'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			}

			$this->import_all = false;
			if ( isset( $args['allImport'] ) && '1' === sanitize_text_field( wp_unslash( $args['allImport'] ) ) ) {
				$this->import_all = true;
			}

			$this->run_demo_stage_import();

			// We've just processed last import stage.
			if ( 1 === count( $this->import_stages ) ) {

				/**
				 * WIP
				$this->content_tracker->set_general_data();
				*/
				$this->after_import();

				// Reset all caches, don't remove demo data.
				fusion_reset_all_caches(
					[
						'demo_data' => false,
					]
				);

				// Demo imported.
			} else { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedElse
				// Demo import partially completed.
			}
			// Save data after import, for example imported terms.
			$this->content_tracker->save_demo_history();

		}
	}

	/**
	 * Actually run demo stage import.
	 *
	 * @access private
	 * @since 7.3
	 */
	private function run_demo_stage_import() {

		// Include the remote file getter.
		if ( ! class_exists( 'Avada_Importer_Data' ) ) {
			include_once Avada::$template_dir_path . '/includes/importer/class-avada-importer-data.php';
		}

		if ( ! class_exists( 'Avada_Demo_Content_Tracker' ) ) {
			include_once Avada::$template_dir_path . '/includes/importer/class-avada-demo-content-tracker.php';
		}

		$this->importer_files  = new Avada_Importer_Data( $this->demo_type );
		$this->content_tracker = new Avada_Demo_Content_Tracker( $this->demo_type );

		$this->before_import_stage();

		if ( ! empty( $this->import_stages[0] ) && method_exists( $this, 'import_' . $this->import_stages[0] ) ) {

			if ( 'download' !== $this->import_stages[0] ) {
				$this->theme_xml          = $this->importer_files->get_path( 'avada.xml' );
				$this->theme_options_file = $this->importer_files->get_path( 'theme_options.json' );
				$this->widgets_file       = $this->importer_files->get_path( 'widget_data.json' );
				$this->fs_url             = $this->importer_files->get_path( 'fusion_slider.zip' );

				$this->shop_demo      = $this->importer_files->is_shop();
				$this->sidebars       = $this->importer_files->get_sidebars();
				$this->homepage_title = $this->importer_files->get_homepage_title();
				$this->woopages       = $this->importer_files->get_woopages();
				$this->fs_exists      = true;

				if ( 'landing_product' === $this->demo_type ) {
					$this->fs_exists = false;
				}

				if ( 'content' === $this->import_stages[0] ) {
					$this->before_content_import();

					foreach ( $this->import_content_types as $content_type ) {
						// Note import stage which is currently processed.
						$this->content_tracker->update_import_stage_data( $content_type );
					}
				} else {
					// Note import stage which is currently processed.
					$this->content_tracker->update_import_stage_data( $this->import_stages[0] );
				}
			}

			// Make import stage backup if needed.
			if ( method_exists( $this->content_tracker, 'set_' . $this->import_stages[0] ) ) {
				call_user_func( [ $this->content_tracker, 'set_' . $this->import_stages[0] ] );
			}

			call_user_func( [ $this, 'import_' . $this->import_stages[0] ] );

			// Menus are imported with the content.
			if ( 'content' === $this->import_stages[0] ) {
				$this->after_content_import();
			}
		}

	}

	/**
	 * Just some stuff that needs to be set before any import stage is run.
	 *
	 * @access private
	 * @since 5.2
	 */
	private function before_import_stage() {

		add_filter( 'intermediate_image_sizes_advanced', 'avada_filter_image_sizes' );

		if ( function_exists( 'ini_get' ) ) {
			if ( 300 < ini_get( 'max_execution_time' ) ) {
				set_time_limit( 300 );
			}
			if ( 512 < intval( ini_get( 'memory_limit' ) ) ) {
				wp_raise_memory_limit();
			}
		}

	}

	/**
	 * Just some stuff that needs to be set after any import stage is run.
	 *
	 * @access private
	 * @since 5.2
	 */
	private function after_import() {

		if ( true === $this->import_all ) {
			$this->assign_menus_to_locations();

			$this->content_tracker->update_import_stage_data( 'all' );
		}

		// Map zip attachments to fusion_icons posts. Doing it here as it's probably shortest import stage.
		if ( $this->import_all || in_array( 'fusion_icons', $this->import_content_types, true ) ) {
			$args = [
				'posts_per_page' => -1, // phpcs:ignore WPThemeReview.CoreFunctionality.PostsPerPage
				'post_type'      => 'fusion_icons',
				'meta_key'       => 'fusion_demo_import', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $this->demo_type, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			];

			$icon_posts = get_posts( $args );

			foreach ( $icon_posts as $icon_post ) {
				$icon_set_meta = fusion_data()->post_meta( $icon_post->ID )->get( 'custom_icon_set' );

				$attachment_args = [
					'posts_per_page' => 1,
					'post_type'      => 'attachment',
					'meta_key'       => '_fusion_icon_set_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'     => $icon_set_meta['icon_set_id'], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				];

				$attachment_posts = get_posts( $attachment_args );

				if ( is_array( $attachment_posts ) && isset( $attachment_posts[0] ) && isset( $attachment_posts[0]->ID ) ) {
					$icon_set_meta['attachment_id'] = $attachment_posts[0]->ID;
					fusion_data()->post_meta( $icon_post->ID )->set( 'custom_icon_set', $icon_set_meta );

					// (Re)generate icon files.
					Fusion_Custom_Icon_Set::get_instance()->regenerate_icon_files( $icon_post->ID );
				}
			}
		}
	}

	/**
	 * Downloads demo package (zip) file.
	 *
	 * @access private
	 * @since 5.2
	 */
	private function import_download() {

		// Get remote files and save locally.
		if ( ! $this->importer_files->remote_files_downloaded() ) {
			$this->importer_files->download_remote_files();
		}

		// Allow attributes import.
		// We want attributes taxonomies to be imported before the content, so they can be registered before XML file processing starts (otherwise terms will be skipped).
		if ( $this->importer_files->is_shop() && class_exists( 'Avada_Woocommerce_Variations' ) && function_exists( 'wc_create_attribute' ) && ( $this->import_all || in_array( 'product', $this->import_content_types, true ) ) ) {

			$product_attributes = $this->importer_files->get_woo_product_attributes();

			if ( ! empty( $product_attributes ) ) {

				// Add Avada attribute types.
				add_filter( 'product_attributes_type_selector', [ Avada_Woocommerce_Variations::get_instance(), 'add_attribute_types' ] );

				$imported_attribute_ids = [];

				foreach ( $product_attributes as $args ) {
					$attr_id = wc_create_attribute( $args );

					if ( ! is_wp_error( $attr_id ) ) {
						$imported_attribute_ids[] = $attr_id;
					}
				}

				// Add imported attribute ids to demo tracker.
				$this->content_tracker->set_woo_attributes( $imported_attribute_ids );
			}
		}
	}

	/**
	 * This is called before 'content' import stages are run.
	 * Mostly used to add hooks which will filter allowed post types and taxonomies from avada.xml file.
	 *
	 * Currently 'content' import stages are: posts, pages, images, CPT.
	 *
	 * @access private
	 * @since 5.2
	 */
	private function before_content_import() {

		add_filter( 'wxr_importer.pre_process.user', [ $this, 'skip_authors' ], 10, 2 );
		add_action( 'wxr_importer.processed.post', [ $this, 'add_fusion_demo_import_meta' ], 10, 5 );
		add_filter( 'import_post_meta_key', [ $this, 'skip_unnecessary_meta' ], 10, 3 );
		add_filter( 'wxr_importer.pre_process.post', [ $this, 'trim_post_content' ], 10, 4 );

		if ( ! $this->import_all ) {

			if ( ! empty( $this->import_content_types ) ) {

				foreach ( $this->import_content_types as $content_type ) {

					if ( method_exists( $this, 'allow_import_' . $content_type ) ) {
						call_user_func( [ $this, 'allow_import_' . $content_type ] );
					}
				}
			}

			add_filter( 'wxr_importer.pre_process.post', [ $this, 'skip_not_allowed_post_types' ], 10, 4 );
			add_filter( 'wxr_importer.pre_process.term', [ $this, 'skip_not_allowed_taxonomies' ], 10, 2 );
		} else {
			// Slides are imported separately, not from avada.xml file.
			add_filter( 'wxr_importer.pre_process.post', [ $this, 'skip_slide_post_type' ], 10, 4 );
			add_filter( 'wxr_importer.pre_process.term', [ $this, 'skip_slide_taxonomy' ], 10, 2 );

			// Don't import WooCommerce orders.
			add_filter( 'wxr_importer.pre_process.post', [ $this, 'skip_shop_order_post_type' ], 10, 4 );
		}

		if ( $this->import_all || in_array( 'avada_layout', $this->import_content_types, true ) ) {

			// Make global layout backup, since they are part of 'content' stage need to be handled separately.
			$this->content_tracker->set_avada_layout();
			add_filter( 'wxr_importer.pre_process.post', [ $this, 'add_slashes_to_layout_content' ], 8, 4 );
			add_filter( 'wxr_importer.pre_process.post', [ $this, 'import_global_avada_layout' ], 9, 4 );
		}
	}

	/**
	 * This is called after 'content' import stages are run.
	 *
	 * @access private
	 * @since 5.2
	 */
	private function after_content_import() {

		remove_filter( 'wxr_importer.pre_process.user', [ $this, 'skip_authors' ], 10 );
		remove_action( 'wxr_importer.processed.post', [ $this, 'add_fusion_demo_import_meta' ], 10 );
		remove_filter( 'import_post_meta_key', [ $this, 'skip_unnecessary_meta' ], 10 );
		remove_filter( 'wxr_importer.pre_process.post', [ $this, 'trim_post_content' ], 10 );

		if ( ! $this->import_all ) {
			remove_filter( 'wxr_importer.pre_process.post', [ $this, 'skip_not_allowed_post_types' ], 10 );
			remove_filter( 'wxr_importer.pre_process.term', [ $this, 'skip_not_allowed_taxonomies' ], 10 );
		} else {
			remove_filter( 'wxr_importer.pre_process.post', [ $this, 'skip_slide_post_type' ], 10 );
			remove_filter( 'wxr_importer.pre_process.term', [ $this, 'skip_slide_taxonomy' ], 10 );
			remove_filter( 'wxr_importer.pre_process.post', [ $this, 'skip_shop_order_post_type' ], 10, 4 );
		}

		if ( $this->import_all || in_array( 'avada_layout', $this->import_content_types, true ) ) {
			remove_filter( 'wxr_importer.pre_process.post', [ $this, 'add_slashes_to_layout_content' ], 8, 4 );
			remove_filter( 'wxr_importer.pre_process.post', [ $this, 'import_global_avada_layout' ], 9, 4 );
		}
	}

	/**
	 * We don't want to import demo authors.
	 *
	 * @access public
	 * @since 5.2
	 * @param array $data User importer data.
	 * @param array $meta User meta.
	 * @return bool
	 */
	public function skip_authors( $data, $meta ) {
		return false;
	}

	/**
	 * Adds import meta to demos.
	 *
	 * @access public
	 * @since 5.2
	 * @param int   $post_id  The Post ID.
	 * @param array $data     The Post importer data.
	 * @param array $meta     The Post meta.
	 * @param array $comments The Post comments.
	 * @param array $terms    The Post terms.
	 */
	public function add_fusion_demo_import_meta( $post_id, $data, $meta, $comments, $terms ) {

		update_post_meta( $post_id, 'fusion_demo_import', $this->demo_type );
	}

	/**
	 * Allow importing a post.
	 *
	 * @access public
	 * @since 5.2
	 */
	public function allow_import_post() {

		$this->allowed_post_types = array_merge( $this->allowed_post_types, [ 'post' ] );
		$this->allowed_taxonomies = array_merge( $this->allowed_taxonomies, [ 'category', 'post_tag' ] );
	}

	/**
	 * Allow importing a page.
	 *
	 * @access public
	 * @since 5.2
	 */
	public function allow_import_page() {

		$this->allowed_post_types = array_merge( $this->allowed_post_types, [ 'page', 'fusion_element', 'fusion_template', 'wpcf7_contact_form' ] );
		$this->allowed_taxonomies = array_merge( $this->allowed_taxonomies, [ 'element_category' ] );
	}

	/**
	 * Allow importing a portfolio.
	 *
	 * @access public
	 * @since 5.2
	 */
	public function allow_import_avada_portfolio() {

		$this->allowed_post_types = array_merge( $this->allowed_post_types, [ 'avada_portfolio' ] );
		$this->allowed_taxonomies = array_merge( $this->allowed_taxonomies, [ 'portfolio_category', 'portfolio_skills', 'portfolio_tags' ] );
	}

	/**
	 * Allow importing an FAQ.
	 *
	 * @access public
	 * @since 5.2
	 */
	public function allow_import_avada_faq() {

		$this->allowed_post_types = array_merge( $this->allowed_post_types, [ 'avada_faq' ] );
		$this->allowed_taxonomies = array_merge( $this->allowed_taxonomies, [ 'faq_category' ] );
	}

	/**
	 * Allow importing layouts.
	 *
	 * @access public
	 * @since 6.2
	 */
	public function allow_import_avada_layout() {

		$this->allowed_post_types = array_merge( $this->allowed_post_types, [ 'fusion_tb_layout', 'fusion_tb_section' ] );
		$this->allowed_taxonomies = array_merge( $this->allowed_taxonomies, [ 'fusion_tb_category' ] );
	}

	/**
	 * Allow importing custom icon sets.
	 *
	 * @access public
	 * @since 6.2
	 */
	public function allow_import_fusion_icons() {

		$this->allowed_post_types = array_merge( $this->allowed_post_types, [ 'fusion_icons' ] );
	}

	/**
	 * Allow importing fusion forms.
	 *
	 * @access public
	 * @since 7.1
	 */
	public function allow_import_fusion_form() {

		$this->allowed_post_types = array_merge( $this->allowed_post_types, [ 'fusion_form' ] );
	}

	/**
	 * Allow importing a product.
	 *
	 * @access public
	 * @since 5.2
	 */
	public function allow_import_product() {

		$this->allowed_post_types = array_merge( $this->allowed_post_types, [ 'product', 'shop_coupon', 'product_variation' ] );
		$this->allowed_taxonomies = array_merge( $this->allowed_taxonomies, [ 'product_cat', 'product_tag', 'product_visibility', 'product_type' ] );

		// Allow attribute taxonomies to be imported as well.
		if ( function_exists( 'wc_get_attribute_taxonomies' ) && function_exists( 'wc_attribute_taxonomy_name' ) ) {
			$attribute_taxonomies = wc_get_attribute_taxonomies();
			if ( $attribute_taxonomies ) {
				foreach ( $attribute_taxonomies as $tax ) {
					$this->allowed_taxonomies[] = wc_attribute_taxonomy_name( $tax->attribute_name );
				}
			}
		}
	}

	/**
	 * Allow importing an event.
	 *
	 * @access public
	 * @since 5.2
	 */
	public function allow_import_event() {

		$this->allowed_post_types = array_merge( $this->allowed_post_types, [ 'tribe_events', 'tribe_venue', 'tribe_organizer' ] );
		$this->allowed_taxonomies = array_merge( $this->allowed_taxonomies, [ 'tribe_events_cat' ] );
	}

	/**
	 * Allow importing a forum.
	 *
	 * @access public
	 * @since 5.2
	 */
	public function allow_import_forum() {

		$this->allowed_post_types = array_merge( $this->allowed_post_types, [ 'forum', 'topic', 'reply' ] );
		$this->allowed_taxonomies = array_merge( $this->allowed_taxonomies, [ 'topic-tag' ] );
	}

	/**
	 * Allow importing an attachment.
	 *
	 * @access public
	 * @since 5.2
	 */
	public function allow_import_attachment() {

		$this->allowed_post_types = array_merge( $this->allowed_post_types, [ 'attachment' ] );
	}

	/**
	 * Imports global avada layout.
	 * Inserts data to wp_options table and passes post to 'skip_not_allowed_post_types' (which will skip it).
	 *
	 * @access public
	 * @since 6.2
	 * @param array $data     The Post importer data.
	 * @param array $meta     The Post meta.
	 * @param array $comments The Post comments.
	 * @param array $terms    The Post terms.
	 * @return bool|array
	 */
	public function import_global_avada_layout( $data, $meta, $comments, $terms ) {
		if ( isset( $data['post_type'] ) && 'fusion_tb_layout_global' === $data['post_type'] && isset( $data['post_content'] ) && '' !== $data['post_content'] ) {
			update_option( 'fusion_tb_layout_default', trim( wp_unslash( $data['post_content'] ), '"' ) );
		}

		return $data;
	}

	/**
	 * Main content importer method.
	 *
	 * @access private
	 * @since 5.2
	 */
	private function import_content() {

		if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
			define( 'WP_LOAD_IMPORTERS', true ); // We are loading importers.
		}

		if ( ! class_exists( 'WP_Importer' ) ) { // If main importer class doesn't exist.
			$wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			include $wp_importer;
		}

		if ( ! class_exists( 'WP_Importer_Logger' ) ) { // If WP importer doesn't exist.
			include FUSION_LIBRARY_PATH . '/inc/importer/class-logger.php';
		}

		if ( ! class_exists( 'AWB_Importer_Logger' ) ) { // If WP importer doesn't exist.
			include FUSION_LIBRARY_PATH . '/inc/importer/class-awb-importer-logger.php';
		}

		if ( ! class_exists( 'WXR_Importer' ) ) { // If WP importer doesn't exist.
			include FUSION_LIBRARY_PATH . '/inc/importer/class-wxr-importer.php';
		}

		if ( ! class_exists( 'Fusion_WXR_Importer' ) ) {
			include FUSION_LIBRARY_PATH . '/inc/importer/class-fusion-wxr-importer.php';
		}

		if ( class_exists( 'AWB_Importer_Logger' ) && class_exists( 'WP_Importer' ) && class_exists( 'WXR_Importer' ) && class_exists( 'Fusion_WXR_Importer' ) ) { // Check for main import class and wp import class.

			$logger = new AWB_Importer_Logger();

			// It's important to disable 'prefill_existing_posts'.
			// In case GUID of importing post matches GUID of an existing post it won't be imported.
			$importer = new Fusion_WXR_Importer(
				[
					'fetch_attachments'      => $this->fetch_attachments,
					'prefill_existing_posts' => false,
					'aggressive_url_search'  => true,
				]
			);
			$importer->set_logger( $logger );

			ob_start();
			$importer->import( $this->theme_xml );
			ob_end_clean();

			// Import WooCommerce if WooCommerce Exists.
			if ( class_exists( 'WooCommerce' ) && $this->shop_demo ) {
				foreach ( $this->woopages as $woo_page_name => $woo_page_title ) {
					$woopage = get_page_by_title( $woo_page_title );
					if ( isset( $woopage ) && $woopage->ID ) {
						update_option( $woo_page_name, $woopage->ID ); // Front Page.
					}
				}
				// We no longer need to install pages.
				delete_option( '_wc_needs_pages' );
				delete_transient( '_wc_activation_redirect' );
			}
			// Flush rules after install.
			flush_rewrite_rules();
		}
	}

	/**
	 * Skips post-types that are not allowed.
	 *
	 * @access public
	 * @since 5.2
	 * @param array $data     The Post importer data.
	 * @param array $meta     The Post meta.
	 * @param array $comments The Post comments.
	 * @param array $terms    The Post terms.
	 * @return bool|array
	 */
	public function skip_not_allowed_post_types( $data, $meta, $comments, $terms ) {

		if ( ! in_array( $data['post_type'], $this->allowed_post_types ) && ! $this->is_icon_package( $data, $meta ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
			return false;
		}

		return $data;
	}

	/**
	 * Adds extra slashes to fusion_tb_layout post's content.
	 *
	 * @access public
	 * @since 6.2
	 * @param array $data     The Post importer data.
	 * @param array $meta     The Post meta.
	 * @param array $comments The Post comments.
	 * @param array $terms    The Post terms.
	 * @return array
	 */
	public function add_slashes_to_layout_content( $data, $meta, $comments, $terms ) {

		if ( isset( $data['post_type'] ) && 'fusion_tb_layout' === $data['post_type'] ) {
			$data['post_content'] = wp_slash( $data['post_content'] );
		}

		return $data;
	}

	/**
	 * Checks if current post is icon package and if it should be imported.
	 *
	 * @access public
	 * @since 6.2
	 * @param array $data The Post importer data.
	 * @param array $meta The Post meta.
	 * @return bool
	 */
	private function is_icon_package( $data, $meta ) {

		if ( 'attachment' !== $data['post_type'] || ! in_array( 'fusion_icons', $this->allowed_post_types, true ) ) {
			return false;
		}

		foreach ( $meta as $meta_field ) {
			if ( '_fusion_icon_set_id' === $meta_field['key'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Trim post content which seems to be added by WP 5.1+ exporter.
	 *
	 * @access public
	 * @since 5.9
	 * @param array $data     The Post importer data.
	 * @param array $meta     The Post meta.
	 * @param array $comments The Post comments.
	 * @param array $terms    The Post terms.
	 * @return bool|array
	 */
	public function trim_post_content( $data, $meta, $comments, $terms ) {
		$data['post_content'] = trim( $data['post_content'] );
		return $data;
	}

	/**
	 * Skip non-allowed taxonomies.
	 *
	 * @access public
	 * @since 5.2
	 * @param array $data     The Post importer data.
	 * @param array $meta     The Post meta.
	 * @return bool|array
	 */
	public function skip_not_allowed_taxonomies( $data, $meta ) {

		if ( ! in_array( $data['taxonomy'], $this->allowed_taxonomies ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
			return false;
		}
		return $data;
	}

	/**
	 * Skips 'slide' post type.
	 * This is used to skip importing 'slides' from avada.xml file.
	 *
	 * @access public
	 * @since 5.2
	 * @param array $data     The Post importer data.
	 * @param array $meta     The Post meta.
	 * @param array $comments The Post comments.
	 * @param array $terms    The Post terms.
	 * @return bool|array
	 */
	public function skip_slide_post_type( $data, $meta, $comments, $terms ) {

		if ( 'slide' === $data['post_type'] ) {
			return false;
		}
		return $data;
	}

	/**
	 * Skip 'slide-page' terms.
	 *
	 * @access public
	 * @since 5.2
	 * @param array $data The Post importer data.
	 * @param array $meta The Post meta.
	 * @return bool|array
	 */
	public function skip_slide_taxonomy( $data, $meta ) {

		if ( $data && 'slide-page' === $data['taxonomy'] ) {
			return false;
		}
		return $data;
	}

	/**
	 * Skips 'shop_order' post type.
	 * This is used to skip importing 'slides' from avada.xml file.
	 *
	 * @access public
	 * @since 7.7
	 * @param array $data     The Post importer data.
	 * @param array $meta     The Post meta.
	 * @param array $comments The Post comments.
	 * @param array $terms    The Post terms.
	 * @return bool|array
	 */
	public function skip_shop_order_post_type( $data, $meta, $comments, $terms ) {

		if ( $data && 'shop_order' === $data['post_type'] ) {
			return false;
		}
		return $data;
	}

	/**
	 * Used to skip unnecessary menu meta when importing.
	 *
	 * @access public
	 * @since 5.2
	 * @param string $meta_key The meta key.
	 * @param int    $post_id  Post ID.
	 * @param object $post     Post object.
	 * @return bool|string
	 */
	public function skip_unnecessary_meta( $meta_key, $post_id, $post ) {

		$meta_keys = [
			// Skip importing old menu meta to 5.2+ installs.
			'_menu_item_fusion_megamenu_status',
			'_menu_item_fusion_megamenu_width',
			'_menu_item_fusion_megamenu_columns',
			'_menu_item_fusion_megamenu_title',
			'_menu_item_fusion_megamenu_widgetarea',
			'_menu_item_fusion_megamenu_columnwidth',
			'_menu_item_fusion_megamenu_icon',
			'_menu_item_fusion_megamenu_modal',
			'_menu_item_fusion_megamenu_thumbnail',
			'_menu_item_fusion_menu_style',
			'_menu_item_fusion_menu_icononly',

			// Skip post views.
			'avada_post_views_count',
			'avada_today_post_views_count',
			'avada_post_views_count_today_date',
		];

		if ( in_array( $meta_key, $meta_keys, true ) ) {
			return false;
		}

		return $meta_key;
	}

	/**
	 * Assigns imported menus to correct locations.
	 * Called from 'import_content' method.
	 *
	 * @access private
	 * @since 5.2
	 */
	private function assign_menus_to_locations() {

		// Set imported menus to registered theme locations.
		$locations = maybe_unserialize( get_theme_mod( 'nav_menu_locations' ) ); // Registered menu locations in theme.
		$menus     = wp_get_nav_menus(); // Registered menus.

		if ( $menus ) {
			if ( 'landing_product' === $this->demo_type ) {
				$opmenu = get_page_by_title( 'Homepage' );
			} elseif ( 'technology' === $this->demo_type ) {
				$opmenu = get_page_by_title( 'Technology' );
			} elseif ( 'wedding' === $this->demo_type ) {
				$opmenu = get_page_by_title( 'Home' );
			} elseif ( 'resume' === $this->demo_type ) {
				$opmenu = get_page_by_title( 'Home' );
			}
			foreach ( $menus as $menu ) { // Assign menus to theme locations.

				// Legacy Special Cases.
				if ( 'landing_product' === $this->demo_type ) {
					// Assign One Page Menu.
					if ( isset( $opmenu ) && $opmenu->ID && 'Landing Product Landing Page Menu' === $menu->name ) {
						fusion_data()->post_meta( $opmenu->ID )->set( 'displayed_menu', $menu->term_id );
					}
				} elseif ( 'resume' === $this->demo_type ) {
					// Assign One Page Menu.
					if ( isset( $opmenu ) && $opmenu->ID && 'Resume Homepage Menu' === $menu->name ) {
						fusion_data()->post_meta( $opmenu->ID )->set( 'displayed_menu', $menu->term_id );
					}
				} elseif ( 'wedding' === $this->demo_type ) {
					// Assign One Page Menu.
					if ( isset( $opmenu ) && $opmenu->ID && 'Wedding Homepage Menu' === $menu->name ) {
						fusion_data()->post_meta( $opmenu->ID )->set( 'displayed_menu', $menu->term_id );
					}
				} elseif ( 'technology' === $this->demo_type ) {
					// Assign One Page Menu.
					if ( isset( $opmenu ) && $opmenu->ID && 'Technology Front Page Menu' === $menu->name ) {
						fusion_data()->post_meta( $opmenu->ID )->set( 'displayed_menu', $menu->term_id );
					}
				}

				// General menu assignment.
				if ( false !== strpos( $menu->name, 'Main Menu' ) ) {

					// Main menu.
					$locations['main_navigation'] = $menu->term_id;
				} elseif ( false !== strpos( $menu->name, 'Top Secondary Menu' ) ) {

					// Top Secondary Menu.
					$locations['top_navigation'] = $menu->term_id;
				} elseif ( false !== strpos( $menu->name, 'One Page Menu' ) ) {

					// Custom One Page Menu.
					if ( false !== strpos( $menu->name, 'Home' ) ) {

						// When on homepage we need to leave the demo name in there.
						$op_menu_name = str_replace( ' One Page Menu', '', $menu->name );
					} else {

						// Remove demo name and the suffix.
						$demo_name    = ucwords( str_replace( '_', ' ', $this->demo_type ) ) . ' ';
						$op_menu_name = str_replace( [ $demo_name, ' One Page Menu' ], '', $menu->name );
					}

					// Get the page.
					$op_menu = get_page_by_title( $op_menu_name );

					// Assign One Page Menu.
					if ( isset( $op_menu ) && $op_menu->ID ) {
						fusion_data()->post_meta( $op_menu->ID )->set( 'displayed_menu', $menu->term_id );
					}
				}
			}
		}

		set_theme_mod( 'nav_menu_locations', $locations ); // Set menus to locations.
	}

	/**
	 * Imports Global Options.
	 *
	 * @access private
	 * @since 5.2
	 */
	private function import_theme_options() {

		$theme_options_json    = fusion_file_get_contents( $this->theme_options_file );
		$theme_options         = apply_filters( 'avada_import_modify_theme_options', json_decode( $theme_options_json, true ) );
		$theme_options_db_name = Avada::get_original_option_name();
		update_option( $theme_options_db_name, $theme_options );
	}

	/**
	 * Modify Theme Options before inserting in DB.
	 *
	 * @access public
	 * @since 7.4
	 * @param array $theme_options Theme Options array.
	 * @return array
	 */
	public function modify_theme_options( $theme_options ) {

		$options = [
			'status_fusion_slider'    => '1',
			'status_fusion_forms'     => '1',
			'status_awb_Off_Canvas'   => '1',
			'status_fusion_portfolio' => '1',
			'status_fusion_faqs'      => '1',
			'js_compiler'             => '1',
			'css_cache_method'        => 'file',
			'status_yt'               => '1',
			'status_vimeo'            => '1',
			'status_gmap'             => '1',
			'status_fontawesome'      => [ 'fab', 'far', 'fas' ],
		];

		foreach ( $options as $key => $value ) {
			$theme_options[ $key ] = $value;
		}

		return $theme_options;
	}

	/**
	 * Imports widgets.
	 *
	 * @access private
	 * @since 5.2
	 */
	private function import_widgets() {

		// Add sidebar widget areas.
		if ( false !== $this->sidebars ) {
			update_option( 'sbg_sidebars', $this->sidebars );

			foreach ( $this->sidebars as $sidebar ) {
				$sidebar_class = avada_name_to_class( $sidebar );
				register_sidebar(
					[
						'name'          => $sidebar,
						'id'            => 'avada-custom-sidebar-' . strtolower( $sidebar_class ),
						'before_widget' => '<div id="%1$s" class="widget %2$s">',
						'after_widget'  => '</div>',
						'before_title'  => '<div class="heading"><h4 class="widget-title">',
						'after_title'   => '</h4></div>',
					]
				);
			}
		}

		// Add data to widgets.
		if ( isset( $this->widgets_file ) && $this->widgets_file ) {
			$widgets_json = $this->widgets_file; // Widgets data file.
			$widget_data  = fusion_file_get_contents( $widgets_json );

			if ( '' !== $widget_data ) {
				$import_widgets = fusion_import_widget_data( $widget_data );
			}
		}
	}

	/**
	 * Calls Fusion, Rev and Layer sliders import methods.
	 *
	 * @access private
	 * @since 5.2
	 */
	private function import_sliders() {
		add_action( 'wxr_importer.processed.post', [ $this, 'add_fusion_demo_import_meta' ], 10, 5 );
		$this->import_fusion_sliders();
		remove_action( 'wxr_importer.processed.post', [ $this, 'add_fusion_demo_import_meta' ], 10 );

		$this->import_layer_sliders();
		$this->import_revolution_sliders();
	}

	/**
	 * Imports LayerSlider.
	 *
	 * @access private
	 * @since 5.2
	 */
	private function import_layer_sliders() {
		global $wpdb;

		$layersliders = $this->importer_files->get_layerslider();

		// Import Layerslider.
		if ( defined( 'LS_PLUGIN_VERSION' ) && false !== $layersliders ) {
			// Get importUtil.
			if ( version_compare( LS_PLUGIN_VERSION, '6.11.0', '>=' ) ) {
				include WP_PLUGIN_DIR . '/LayerSlider/assets/classes/class.ls.importutil.php';
			} elseif ( file_exists( WP_PLUGIN_DIR . '/LayerSlider/classes/class.ls.importutil.php' ) ) {
				include WP_PLUGIN_DIR . '/LayerSlider/classes/class.ls.importutil.php';
			} else {
				return;
			}

			foreach ( $layersliders as $layer_file ) {
				// Finally import rev slider data files.
				$filepath = $this->importer_files->get_path( 'layersliders/' . $layer_file );
				$import   = new LS_ImportUtil( $filepath );
			}

			// Get sliders.
			$sliders = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}layerslider WHERE flag_hidden = '0' AND flag_deleted = '0' ORDER BY date_c ASC" );
			$slides  = [];
			if ( ! empty( $sliders ) ) {
				foreach ( $sliders as $key => $item ) {
					$slides[ $item->id ] = $item->name;

					$this->content_tracker->add_layer_slider_to_stack( $item->id );
				}
			}

			if ( $slides ) {
				foreach ( $slides as $key => $val ) {
					$slides_array[ $val ] = $key;
				}
			}
		}
	}

	/**
	 * Imports revsliders.
	 *
	 * @access private
	 * @since 5.2
	 */
	private function import_revolution_sliders() {

		$revsliders = $this->importer_files->get_revslider();

		// Import Revslider.
		if ( class_exists( 'RevSliderSliderImport' ) && false != $revsliders ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			// If revslider is activated.
			add_action( 'wp_generate_attachment_metadata', [ $this, 'add_rev_slider_demo_import_meta' ], 10, 2 );

			$slider = new RevSliderSliderImport();
			foreach ( $revsliders as $rev_file ) {
				// Finally import rev slider data files.
				$filepath = $this->importer_files->get_path( 'revsliders/' . $rev_file );
				ob_start();
				$result = $slider->import_slider( true, $filepath );
				ob_clean();
				ob_end_clean();

				if ( true === $result['success'] ) {
					$this->content_tracker->add_rev_slider_to_stack( $result['sliderID'] );
				}
			}

			remove_action( 'wp_generate_attachment_metadata', [ $this, 'add_rev_slider_demo_import_meta' ], 10 );
		}
	}

	/**
	 * Add meta data for media imported by Rev Slider importer.
	 *
	 * @access public
	 * @since 5.4.1
	 *
	 * @param mixed $metadata      Metadata for attachment.
	 * @param int   $attachment_id ID of the attachment.
	 */
	public function add_rev_slider_demo_import_meta( $metadata, $attachment_id ) {
		update_post_meta( $attachment_id, 'fusion_slider_demo_import', $this->demo_type );
	}

	/**
	 * Import fusion-sliders.
	 *
	 * @access private
	 * @since 5.2
	 */
	private function import_fusion_sliders() {

		// Avada Sliders Import.
		if ( true === $this->fs_exists && class_exists( 'Fusion_Slider' ) && file_exists( $this->fs_url ) ) {

			add_action( 'fusion_slider_import_image_attached', [ $this, 'add_fusion_slider_demo_import_meta' ], 10, 2 );
			$fusion_slider = new Fusion_Slider();
			$fusion_slider->import_sliders( $this->fs_url, $this->demo_type );
			remove_action( 'fusion_slider_import_image_attached', [ $this, 'add_fusion_slider_demo_import_meta' ], 10 );
		}
	}

	/**
	 * Adds meta to fusion-sliders.
	 *
	 * @access public
	 * @since 5.2
	 * @param int $attachment_id The attachment-ID.
	 * @param int $post_id       The post-ID.
	 */
	public function add_fusion_slider_demo_import_meta( $attachment_id, $post_id ) {
		update_post_meta( $attachment_id, 'fusion_slider_demo_import', $this->demo_type );
	}

	/**
	 * Sets home page, site title and imports menus.
	 *
	 * @access private
	 * @since 5.2
	 */
	private function import_general_data() {

		// Menus are imported with the rest of the content.
		// Set reading options.
		$homepage = get_page_by_title( $this->homepage_title );
		if ( isset( $homepage ) && $homepage->ID ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $homepage->ID ); // Front Page.
		}

		// Import site title.
		$site_title = '' !== $this->site_title ? $this->site_title : 'Avada ' . ucwords( str_replace( '_', ' ', $this->demo_type ) );
		update_option( 'blogname', $site_title );

		$this->content_tracker->set( 'general_data', 'imported' );
	}

	/**
	 * Import Convert Plus plugin's modules.
	 *
	 * @access private
	 * @since 6.2
	 */
	private function import_convertplug() {

		// Plugin is not active or there are no modules to import.
		if ( ! defined( 'CP_VERSION' ) || false === $this->importer_files->get_cp_modules() ) {
			return;
		}

		// Wait for init or require.
		if ( ! function_exists( 'smile_backend_create_folder' ) ) {
			require_once CP_BASE_DIR . '/framework/functions/functions.admin.php';
		}

		$upload_dir = wp_upload_dir();

		$files = $this->importer_files->get_cp_modules();

		foreach ( $files as $file_basename ) {

			$file_type = wp_check_filetype( $file_basename, null );
			$file_path = $this->importer_files->get_path( 'convertplus/' . $file_basename );
			$title     = pathinfo( $file_basename, PATHINFO_FILENAME );

			$attachment_id = wp_insert_attachment(
				[
					'post_title'     => $title,
					'post_content'   => '',
					'post_status'    => 'inherit',
					'post_mime_type' => $file_type['type'],
				],
				$file_path,
				0
			);

			// Set module name.
			$module_name = 'modal';
			if ( false !== strpos( $file_basename, 'info_bar' ) ) {
				$module_name = 'info_bar';
			} elseif ( false !== strpos( $file_basename, 'slide_in' ) ) {
				$module_name = 'slide_in';
			}

			$data['module'] = $module_name;
			$data['file']   = [
				'id'       => $attachment_id,
				'filename' => $file_basename,
				'title'    => $title,
			];

			// We're all set.
			if ( function_exists( 'fusion_cp_import_' . $module_name ) ) {

				// Add post meta to imported images.
				add_action( 'add_attachment', [ $this, 'cp_add_postmeta' ] );

				// Call CP's import function.
				call_user_func( 'fusion_cp_import_' . $module_name, $data );

				// Post import work.
				$this->add_cp_to_tracker( $module_name, $attachment_id );
			}
		}
	}

	/**
	 * Add CP modules' ID to our tracker.
	 *
	 * @since 6.2
	 * @param string $module_name   Name of the module.
	 * @param int    $attachment_id ID of zip archive.
	 * @return void
	 */
	public function add_cp_to_tracker( $module_name, $attachment_id ) {
		global $wpdb;
		$option_name = 'smile_' . $module_name . '_styles';
		$modules     = get_option( $option_name, [] );

		// Latest module is last.
		$module = end( $modules );

		if ( isset( $module['style_id'] ) ) {
			$this->content_tracker->add_convertplug_to_stack( [ $module['style_id'], $module_name ] );
		}

		// Remove zip from Media Library (we want to keep the file).
		$wpdb->query( $wpdb->prepare( "DELETE $wpdb->posts, $wpdb->postmeta FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE $wpdb->posts.ID = %d", $attachment_id ) );
	}

	/**
	 * Add post meta to any attachment added during CP import.
	 *
	 * @since 6.2
	 * @param int $attachment_id Attachment ID.
	 */
	public function cp_add_postmeta( $attachment_id ) {
		update_post_meta( $attachment_id, 'fusion_demo_import', $this->demo_type );
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

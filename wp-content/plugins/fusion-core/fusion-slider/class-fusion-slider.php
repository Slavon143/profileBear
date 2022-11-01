<?php
/**
 * Fusion-Slider main class.
 *
 * @package Fusion-Slider
 * @since 1.0.0
 */

if ( ! class_exists( 'Fusion_Slider' ) ) {
	/**
	 * The main Fusion_Slider class.
	 */
	class Fusion_Slider {

		/**
		 * The demo type.
		 *
		 * @access private
		 * @since 7.0
		 * @var string
		 */
		private $demo_type;

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'init_post_type' ] );
			add_action( 'wp_before_admin_bar_render', [ $this, 'fusion_admin_bar_render' ] );
			add_filter( 'themefusion_es_groups_row_actions', [ $this, 'remove_taxonomy_actions' ], 10, 1 );
			add_filter( 'slide-page_row_actions', [ $this, 'remove_taxonomy_actions' ], 10, 1 );
			add_action( 'admin_init', [ $this, 'admin_init' ] );
			add_action( 'admin_menu', [ $this, 'admin_menu' ], 20 );
			add_filter( 'parent_file', [ $this, 'change_current_menu_item' ] );

			add_action( 'avada_dashboard_sticky_menu_items', [ $this, 'add_avada_dashboard_sticky_menu_items' ], 20 );

			add_action( 'all_admin_notices', [ $this, 'get_admin_screens_header' ] );
			add_action( 'in_admin_footer', [ $this, 'get_admin_screens_footer' ] );

			// Add settings.
			add_action( 'slide-page_add_form_fields', [ $this, 'slider_add_new_meta_fields' ], 10, 2 );
			add_action( 'slide-page_edit_form_fields', [ $this, 'slider_edit_meta_fields' ], 10, 2 );
			add_action( 'edited_slide-page', [ $this, 'slider_save_taxonomy_custom_meta' ], 10, 2 );
			add_action( 'create_slide-page', [ $this, 'slider_save_taxonomy_custom_meta' ], 10, 2 );

			// Clone slide.
			add_action( 'admin_action_save_as_new_slide', [ $this, 'save_as_new_slide' ] );
			add_filter( 'post_row_actions', [ $this, 'admin_clone_slide_button' ], 10, 2 );
			add_action( 'edit_form_after_title', [ $this, 'admin_clone_slide_button_after_title' ] );
			// Clone slider.
			add_filter( 'slide-page_row_actions', [ $this, 'admin_clone_slider_button' ], 10, 2 );
			add_action( 'slide-page_edit_form_fields', [ $this, 'admin_clone_slider_button_edit_form' ] );
			add_action( 'admin_action_clone_fusion_slider', [ $this, 'save_as_new_slider' ] );

			add_filter( 'taxonomy_labels_slide-page', [ $this, 'change_slide_labels' ] );

			if ( apply_filters( 'avada_force_enqueue', ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) ) ) {
				add_action( 'wp_enqueue_scripts', [ $this, 'init' ], 10 );
			} else {
				add_action( 'fusion_slider_enqueue', [ $this, 'init' ], 10 );
			}
		}

		/**
		 * Update lables on slide pages.
		 *
		 * @access public
		 * @since 5.0.2
		 * @param object $labels Object with labels for the taxonomy as member variables.
		 * @return object The updated labels.
		 */
		public function change_slide_labels( $labels ) {
			foreach ( $labels as $index => $label ) {
				if ( null !== $label ) {
					$labels->$index = str_replace( [ 'Categories', 'Category', 'categories', 'category' ], [ 'Slides', 'Slides', 'slides', 'slide' ], $label );
				}
			}

			return $labels;
		}

		/**
		 * Runs on wp_loaded.
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function init_post_type() {
			register_post_type(
				'slide',
				[
					'public'              => true,
					'has_archive'         => false,
					'rewrite'             => [
						'slug' => 'slide',
					],
					'supports'            => [ 'title', 'thumbnail' ],
					'can_export'          => true,
					'hierarchical'        => false,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'show_in_menu'        => false,
					'labels'              => [
						'name'                     => _x( 'Avada Slides', 'Post Type General Name', 'fusion-core' ),
						'singular_name'            => _x( 'Avada Slide', 'Post Type Singular Name', 'fusion-core' ),
						'menu_name'                => __( 'Avada Slider', 'fusion-core' ),
						'parent_item_colon'        => __( 'Parent Slide:', 'fusion-core' ),
						'all_items'                => __( 'Add or Edit Slides', 'fusion-core' ),
						'view_item'                => __( 'View Slide', 'fusion-core' ),
						'add_new_item'             => __( 'Add New Slide', 'fusion-core' ),
						'add_new'                  => __( 'Add New Slide', 'fusion-core' ),
						'edit_item'                => __( 'Edit Slide', 'fusion-core' ),
						'update_item'              => __( 'Update Slide', 'fusion-core' ),
						'search_items'             => __( 'Search Slide', 'fusion-core' ),
						'not_found'                => __( 'Not found', 'fusion-core' ),
						'not_found_in_trash'       => __( 'Not found in Trash', 'fusion-core' ),
						'item_published'           => __( 'Slide published.', 'fusion-core' ),
						'item_published_privately' => __( 'Slide published privately.', 'fusion-core' ),
						'item_reverted_to_draft'   => __( 'Slide reverted to draft.', 'fusion-core' ),
						'item_scheduled'           => __( 'Slide scheduled.', 'fusion-core' ),
						'item_updated'             => __( 'Slide updated.', 'fusion-core' ),
					],
				]
			);

			register_taxonomy(
				'slide-page',
				'slide',
				[
					'public'             => true,
					'hierarchical'       => true,
					'label'              => 'Slider',
					'query_var'          => true,
					'rewrite'            => true,
					'show_in_nav_menus'  => false,
					'show_tagcloud'      => false,
					'publicly_queryable' => false,
					'labels'             => [
						'name'                       => __( 'Avada Sliders', 'fusion-core' ),
						'singular_name'              => __( 'Avada Slider', 'fusion-core' ),
						'menu_name'                  => __( 'Add or Edit Sliders', 'fusion-core' ),
						'all_items'                  => __( 'All Sliders', 'fusion-core' ),
						'parent_item_colon'          => __( 'Parent Slider:', 'fusion-core' ),
						'new_item_name'              => __( 'New Slider Name', 'fusion-core' ),
						'add_new_item'               => __( 'Add Slider', 'fusion-core' ),
						'edit_item'                  => __( 'Edit Slider', 'fusion-core' ),
						'update_item'                => __( 'Update Slider', 'fusion-core' ),
						'separate_items_with_commas' => __( 'Separate sliders with commas', 'fusion-core' ),
						'search_items'               => __( 'Search Sliders', 'fusion-core' ),
						'add_or_remove_items'        => __( 'Add or remove sliders', 'fusion-core' ),
						'choose_from_most_used'      => __( 'Choose from the most used sliders', 'fusion-core' ),
						'not_found'                  => __( 'Not Found', 'fusion-core' ),
					],
				]
			);
		}

		/**
		 * Runs on wp.
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function init() {
			if ( ! class_exists( 'Fusion' ) || ! class_exists( 'Fusion_Settings' ) ) {
				return;
			}
			global $fusion_library;

			$fusion_settings = awb_get_fusion_settings();

			if ( ! $fusion_library ) {
				$fusion_library = Fusion::get_instance();
			}

			$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

			if ( $fusion_settings->get( 'status_fusion_slider' ) || $is_builder ) {

				// Check if header is enabled.
				if ( ! is_page_template( 'blank.php' ) && 'no' !== fusion_get_page_option( 'display_header', $fusion_library->get_page_id() ) ) {
					$dependencies = [ 'jquery', 'avada-header', 'modernizr', 'cssua', 'jquery-flexslider', 'fusion-flexslider', 'fusion-video-general', 'fusion-video-bg' ];
				} else {
					$dependencies = [ 'jquery', 'modernizr', 'cssua', 'jquery-flexslider', 'fusion-flexslider', 'fusion-video-general', 'fusion-video-bg' ];
				}
				if ( $fusion_settings->get( 'status_vimeo' ) ) {
					$dependencies[] = 'vimeo-player';
				}

				if ( $fusion_settings->get( 'status_yt' ) ) {
					$dependencies[] = 'fusion-youtube';
				}

				$dependencies[] = 'fusion-responsive-typography';
				if ( function_exists( 'fusion_is_element_enabled' ) && fusion_is_element_enabled( 'fusion_title' ) ) {
					$dependencies[] = 'fusion-title';
				}
				Fusion_Dynamic_JS::enqueue_script(
					'avada-fusion-slider',
					FusionCore_Plugin::$js_folder_url . '/avada-fusion-slider.js',
					FusionCore_Plugin::$js_folder_path . '/avada-fusion-slider.js',
					$dependencies,
					FUSION_CORE_VERSION,
					true
				);

				$c_page_id             = $fusion_library->get_page_id();
				$slider_position       = fusion_get_option( 'slider_position' );
				$mobile_header_opacity = 1;
				$header_opacity        = 1;
				if ( class_exists( 'Avada_Helper' ) ) {
					$header_color          = Avada_Helper::get_header_color( $c_page_id, false );
					$header_opacity        = 1 === Fusion_Color::new_color( $header_color )->alpha ? 0 : 1;
					$mobile_header_color   = Avada_Helper::get_header_color( $c_page_id, true );
					$mobile_header_opacity = 1 === Fusion_Color::new_color( $mobile_header_color )->alpha ? 0 : 1;
				}

				Fusion_Dynamic_JS::localize_script(
					'avada-fusion-slider',
					'avadaFusionSliderVars',
					[
						'side_header_break_point'    => (int) $fusion_settings->get( 'side_header_break_point' ),
						'slider_position'            => ( $slider_position && 'default' !== $slider_position ) ? strtolower( $slider_position ) : strtolower( $fusion_settings->get( 'slider_position' ) ),
						'header_transparency'        => $header_opacity,
						'mobile_header_transparency' => $mobile_header_opacity,
						'header_position'            => fusion_get_option( 'header_position' ),
						'content_break_point'        => (int) $fusion_settings->get( 'content_break_point' ),
						'status_vimeo'               => $fusion_settings->get( 'status_vimeo' ),
					]
				);
			}
		}

		/**
		 * Removes the 'view' in the admin bar.
		 *
		 * @access public
		 */
		public function fusion_admin_bar_render() {
			global $wp_admin_bar, $typenow;

			if ( 'slide' === $typenow || 'themefusion_elastic' === $typenow ) {
				$wp_admin_bar->remove_menu( 'view' );
			}
		}

		/**
		 * Removes the 'view' link in taxonomy page.
		 *
		 * @access public
		 * @param array $actions WordPress actions array for the taxonomy admin page.
		 * @return array $actions
		 */
		public function remove_taxonomy_actions( $actions ) {
			global $typenow;

			if ( 'slide' === $typenow || 'themefusion_elastic' === $typenow ) {
				unset( $actions['view'] );
			}
			return $actions;
		}
		/**
		 * Enqueue Scripts and Styles
		 *
		 * @access public
		 * @return void
		 */
		public function admin_init() {
			global $pagenow;

			$post_type = '';

			if ( isset( $_GET['post'] ) && wp_unslash( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security
				$post_type = get_post_type( wp_unslash( $_GET['post'] ) ); // phpcs:ignore WordPress.Security
			}

			if ( ( isset( $_GET['taxonomy'] ) && 'slide-page' === $_GET['taxonomy'] ) || ( isset( $_GET['post_type'] ) && 'slide' === $_GET['post_type'] ) || 'slide' === $post_type ) { // phpcs:ignore WordPress.Security
				wp_enqueue_script( 'fusion-slider', esc_url_raw( FusionCore_Plugin::$js_folder_url . '/fusion-slider.js' ), false, FUSION_CORE_VERSION, true );
			}

			if ( isset( $_GET['page'] ) && 'avada_slider_export_import' === $_GET['page'] ) { // phpcs:ignore WordPress.Security
				$this->export_sliders();
			}
		}

		/**
		 * Adds the submenu.
		 *
		 * @access public
		 */
		public function admin_menu() {
			global $submenu;

			// Admin role menu entry.
			if ( current_user_can( 'switch_themes' ) ) {
				$sliders = add_submenu_page( 'avada', esc_html__( 'Avada Sliders', 'fusion-core' ), esc_html__( 'Sliders', 'fusion-core' ), 'manage_options', 'avada_sliders', null, 9 );
			} else { // Editor role menu entry.
				$sliders     = add_menu_page( esc_html__( 'Avada Sliders', 'fusion-core' ), esc_html__( 'Avada Sliders', 'fusion-core' ), 'edit_pages', 'avada_sliders', '', 'dashicons-avada', '2.111111' );
				$sliders_sub = add_submenu_page( 'avada_sliders', esc_html__( 'Avada Slides', 'fusion-core' ), esc_html__( 'Avada Slides', 'fusion-core' ), 'edit_pages', 'avada_slides', null, 29 );
			}

			add_submenu_page( 'avada', __( 'Export / Import', 'fusion-core' ), __( 'Export / Import', 'fusion-core' ), 'manage_options', 'avada_slider_export_import', [ $this, 'add_slider_import_export' ], 30 );

			add_action( 'admin_print_styles', [ $this, 'add_styles' ] );

			// Change menu links for admin role.
			if ( array_key_exists( 'avada', $submenu ) ) {
				foreach ( $submenu['avada'] as $key => $value ) {
					$k = array_search( 'avada_sliders', $value, true );
					if ( $k ) {
						$submenu['avada'][ $key ][ $k ] = ( current_user_can( $submenu['avada'][ $key ][1] ) ) ? esc_url( admin_url( 'edit-tags.php?taxonomy=slide-page&post_type=slide' ) ) : ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
					}
				}
			} elseif ( array_key_exists( 'avada_sliders', $submenu ) ) { // Change menu links for editor role.
				foreach ( $submenu['avada_sliders'] as $key => $value ) {
					$k = array_search( 'avada_sliders', $value, true );
					if ( $k ) {
						$submenu['avada_sliders'][ $key ][ $k ] = ( current_user_can( $submenu['avada_sliders'][ $key ][1] ) ) ? esc_url( admin_url( 'edit-tags.php?taxonomy=slide-page&post_type=slide' ) ) : ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
					}

					$k = array_search( 'avada_slides', $value, true );
					if ( $k ) {
						$submenu['avada_sliders'][ $key ][ $k ] = ( current_user_can( $submenu['avada_sliders'][ $key ][1] ) ) ? esc_url( admin_url( 'edit.php?post_type=slide' ) ) : ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
					}
				}
			}
		}

		/**
		 * Adds the submenu.
		 *
		 * @access public
		 * @since 5.0
		 * @param string $parent_file The menu parent file / page name.
		 * @return string The changed parent file / page name.
		 */
		public function change_current_menu_item( $parent_file ) {
			global $submenu_file;
			$screen = get_current_screen();

			if ( ( 'edit-slide-page' === $screen->id || 'edit-slide' === $screen->id || ( 'slide' === $screen->id && 'slide' === $screen->post_type ) || ( isset( $_GET['page'] ) && 'avada_slider_export_import' === $_GET['page'] ) ) && class_exists( 'Avada' ) ) { // phpcs:ignore WordPress.Security.NonceVerification

				$submenu_file = esc_url( admin_url( 'edit-tags.php?taxonomy=slide-page&post_type=slide' ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride

				$parent_file = 'avada';
			}

			return $parent_file;
		}


		/**
		 * Add styles to the admin screen.
		 *
		 * @access public
		 * @since 5.0
		 * @return void
		 */
		public function add_styles() {
			$screen = get_current_screen();

			if ( ( 'edit-slide-page' === $screen->id || 'edit-slide' === $screen->id || ( 'slide' === $screen->id && 'slide' === $screen->post_type ) || ( isset( $_GET['page'] ) && 'avada_slider_export_import' === $_GET['page'] ) ) && class_exists( 'Avada' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				wp_enqueue_style( 'avada_admin_css', get_template_directory_uri() . '/assets/admin/css/avada-admin.css', [], AVADA_VERSION );

				add_action( 'admin_footer', 'fusion_the_admin_font_async' );
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
			<li class="avada-db-menu-item avada-db-menu-item-sliders"><a class="avada-db-menu-item-link<?php echo ( 'sliders' === $screen || 'slides' === $screen || 'slide-edit' === $screen || 'slider-export' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'sliders' === $screen ) ? '#' : admin_url( 'edit-tags.php?taxonomy=slide-page&post_type=slide' ) ); ?>" ><i class="fusiona-carousel"></i><span class="avada-db-menu-item-text"><?php esc_html_e( 'Sliders', 'fusion-core' ); ?></span></a>
				<ul class="avada-db-menu-sub avada-db-menu-sub-sliders">
					<li class="avada-db-menu-sub-item avada-db-menu-sub-item-sliders">
						<a class="avada-db-menu-sub-item-link<?php echo ( 'sliders' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'sliders' === $screen ) ? '#' : admin_url( 'edit-tags.php?taxonomy=slide-page&post_type=slide' ) ); ?>">
							<i class="fusiona-layouts"></i>
							<div class="avada-db-menu-sub-item-text">
								<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Sliders', 'fusion-core' ); ?></div>
								<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'Edit your Avada Sliders.', 'fusion-core' ); ?></div>
							</div>
						</a>
					</li>
					<li class="avada-db-menu-sub-item avada-db-menu-sub-item-slides">
						<a class="avada-db-menu-sub-item-link<?php echo ( 'slides' === $screen || 'slide-edit' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'slides' === $screen ) ? '#' : admin_url( 'edit.php?post_type=slide' ) ); ?>">
							<i class="fusiona-content"></i>
							<div class="avada-db-menu-sub-item-text">
								<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Slides', 'fusion-core' ); ?></div>
								<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'Edit your Avada Slides.', 'fusion-core' ); ?></div>
							</div>
						</a>
					</li>
					<li class="avada-db-menu-sub-item avada-db-menu-sub-item-slider-export">
						<a class="avada-db-menu-sub-item-link<?php echo ( 'slider-export' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'slider-export' === $screen ) ? '#' : admin_url( 'admin.php?page=avada_slider_export_import' ) ); ?>">
							<i class="fusiona-file-import-solid"></i>
							<div class="avada-db-menu-sub-item-text">
								<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Export / Import Sliders', 'fusion-core' ); ?></div>
								<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'Export & import your Avada sliders.', 'fusion-core' ); ?></div>
							</div>
						</a>
					</li>
				</ul>
			</li>
			<?php
		}

		/**
		 * Adds the Avada admin header.
		 *
		 * @access public
		 * @since 5.0
		 * @return void
		 */
		public function get_admin_screens_header() {
			$screen = get_current_screen();

			if ( ( 'edit-slide-page' === $screen->id || 'edit-slide' === $screen->id || ( 'slide' === $screen->id && 'slide' === $screen->post_type ) || ( isset( $_GET['page'] ) && 'avada_slider_export_import' === $_GET['page'] ) ) && class_exists( 'Avada' ) ) { // phpcs:ignore WordPress.Security.NonceVerification

				if ( 'edit-slide-page' === $screen->id ) {
					$current_screen = 'sliders';
					$heading        = __( 'Avada Sliders', 'fusion-core' );
					$notice         = sprintf(
						/* translators: %s: "Slides Page Link". */
						esc_html__( 'To edit single slides, please go to the %s.', 'fusion-core' ),
						'<a href="' . esc_url( admin_url( 'edit.php?post_type=slide' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Slides Page', 'fusion-core' ) . '</a>'
					);
				} elseif ( 'edit-slide' === $screen->id ) {
					$current_screen = 'slides';
					$heading        = __( 'Avada Slides', 'fusion-core' );
					$notice         = sprintf(
						/* translators: %s: "Slides Page Link". */
						esc_html__( 'To edit sliders, please go to the  %s.', 'fusion-core' ),
						'<a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=slide-page&post_type=slide' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Sliders Page', 'fusion-core' ) . '</a>'
					);
				} elseif ( 'slide' === $screen->id && 'slide' === $screen->post_type ) {
					$current_screen = 'slide-edit';
				} else {
					$current_screen = 'slider-export';
				}

				Avada_Admin::get_admin_screens_header( $current_screen );

				if ( ( 'edit-slide-page' === $screen->id && ! isset( $_GET['tag_ID'] ) ) || 'edit-slide' === $screen->id ) : // phpcs:ignore WordPress.Security.NonceVerification
					?>
					<section class="avada-db-card avada-db-card-first avada-db-support-start">
						<h1><?php echo esc_html( $heading ); ?></h1>

						<p><?php esc_html_e( 'Create stunning sliders that seamlessly integrate with your pages built with the Avada Website Builder.', 'fusion-core' ); ?></p>

						<div class="avada-db-card-notice">
							<i class="fusiona-info-circle"></i>
							<p class="avada-db-card-notice-heading">
								<?php echo $notice; // phpcs:ignore WordPress.Security ?>
							</p>
						</div>
					</section>
					<?php
				elseif ( 'slide' === $screen->id && 'slide' === $screen->post_type ) :
					Avada_Admin::get_admin_screens_footer( 'slide-edit' );
				endif;
			}
		}

		/**
		 * Adds the Avada admin footer.
		 *
		 * @access public
		 * @since 5.0
		 * @return void
		 */
		public function get_admin_screens_footer() {
			$screen = get_current_screen();

			if ( ( 'edit-slide-page' === $screen->id || 'edit-slide' === $screen->id ) && class_exists( 'Avada' ) ) {
				?>
			</div>
			<div class="avada-dashboard avada-db-edit-screen-footer">
				<?php Avada_Admin::get_admin_screens_footer(); ?>

			<div id="wpfooter" role="contentinfo">
				<?php
			}
		}

		/**
		 * Add term page.
		 *
		 * @access public
		 * @return void
		 */
		public function add_slider_import_export() {
			if ( $_FILES && isset( $_FILES['import'] ) && isset( $_FILES['import']['tmp_name'] ) ) {

				// Don't strip slashes, that would mess up import on Windows machines.
				$this->import_sliders( $_FILES['import']['tmp_name'] ); // phpcs:ignore WordPress.Security
			}

			include FUSION_CORE_PATH . '/fusion-slider/templates/export-import-sliders.php';
		}

		/**
		 * Add term page.
		 *
		 * @access public
		 */
		public function slider_add_new_meta_fields() {

			// This will add the custom meta field to the add new term page.
			include FUSION_CORE_PATH . '/fusion-slider/templates/add-new-meta-fields.php';

		}

		/**
		 * Edit term page.
		 *
		 * @access public
		 * @param object $term The term object.
		 */
		public function slider_edit_meta_fields( $term ) {
			// Put the term ID into a variable.
			$t_id = $term->term_id;

			// Retrieve the existing value(s) for this meta field. This returns an array.
			$term_meta = fusion_data()->term_meta( $t_id )->get_all_meta();

			$defaults = [
				'slider_indicator'       => '',
				'slider_indicator_color' => '',
				'typo_sensitivity'       => '0.1',
				'typo_factor'            => '1.5',
				'nav_box_width'          => '63px',
				'nav_box_height'         => '63px',
				'nav_arrow_size'         => '25px',
				'slider_width'           => '100%',
				'slider_height'          => '500px',
				'full_screen'            => false,
				'parallax'               => false,
				'nav_arrows'             => true,
				'autoplay'               => true,
				'loop'                   => true,
				'animation'              => 'fade',
				'slideshow_speed'        => 7000,
				'animation_speed'        => 600,
			];

			$term_meta = wp_parse_args( $term_meta, $defaults );

			include FUSION_CORE_PATH . '/fusion-slider/templates/edit-meta-fields.php';

		}

		/**
		 * Save extra taxonomy fields callback function.
		 *
		 * @access public
		 * @param int $term_id The term ID.
		 */
		public function slider_save_taxonomy_custom_meta( $term_id ) {

			if ( ! empty( $_POST ) && isset( $_POST['fusion_core_meta_fields_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fusion_core_meta_fields_nonce'] ) ), 'fusion_core_meta_fields_nonce' ) && current_user_can( 'manage_categories' ) ) {
				if ( isset( $_POST['term_meta'] ) ) {
					$t_id      = $term_id;
					$term_meta = fusion_data()->term_meta( $t_id )->get_all_meta();
					$cat_keys  = array_keys( wp_unslash( $_POST['term_meta'] ) ); // phpcs:ignore WordPress.Security
					foreach ( $cat_keys as $key ) {
						if ( isset( $_POST['term_meta'][ $key ] ) ) {
							$term_meta[ $key ] = wp_unslash( $_POST['term_meta'][ $key ] ); // phpcs:ignore WordPress.Security
						}
					}
					// Save the option array.
					fusion_data()->term_meta( $t_id )->set_raw( $term_meta );
				}
			}
		}

		/**
		 * Exports the sliders.
		 *
		 * @access public
		 */
		public function export_sliders() {
			if ( isset( $_POST['fusion_slider_export_button'] ) ) {

				check_admin_referer( 'fs_export' );

				if ( ! wp_unslash( $_POST['fusion_slider_export_button'] ) ) { // phpcs:ignore WordPress.Security
					return;
				}

				// Load Importer API.
				require_once wp_normalize_path( ABSPATH . 'wp-admin/includes/export.php' );

				ob_start();
				export_wp(
					[
						'content' => 'slide',
					]
				);
				$export = ob_get_contents();
				ob_get_clean();

				$terms = get_terms(
					'slide-page',
					[
						'hide_empty' => 1,
					]
				);

				foreach ( $terms as $term ) {
					$term_meta                   = fusion_data()->term_meta( $term->term_id )->get_all_meta();
					$export_terms[ $term->slug ] = $term_meta;
				}

				$json_export_terms = wp_json_encode( $export_terms );

				$upload_dir = wp_upload_dir();
				$base_dir   = trailingslashit( $upload_dir['basedir'] );
				$fs_dir     = $base_dir . 'fusion_slider/';
				wp_mkdir_p( $fs_dir );

				$loop = new WP_Query(
					[
						'post_type'      => 'slide',
						'posts_per_page' => -1,
						'meta_key'       => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery
					]
				);

				while ( $loop->have_posts() ) {
					$loop->the_post();
					$post_image_id = get_post_thumbnail_id( get_the_ID() );
					$image_path    = get_attached_file( $post_image_id );
					if ( isset( $image_path ) && $image_path ) {
						$ext = pathinfo( $image_path, PATHINFO_EXTENSION );
						$this->filesystem()->copy( $image_path, $fs_dir . $post_image_id . '.' . $ext, true );
					}
				}

				wp_reset_postdata();

				$url   = wp_nonce_url( 'edit.php?post_type=slide&page=avada_slider_export_import' );
				$creds = request_filesystem_credentials( $url, '', false, false, null );
				if ( false === $creds ) {
					return; // Stop processing here.
				}

				if ( WP_Filesystem( $creds ) ) {
					global $wp_filesystem;

					if ( ! $wp_filesystem->put_contents( $fs_dir . 'sliders.xml', $export, FS_CHMOD_FILE ) || ! $wp_filesystem->put_contents( $fs_dir . 'settings.json', $json_export_terms, FS_CHMOD_FILE ) ) {
						?>
						<div class="avada-db-card avada-db-notice">
							<h2><?php esc_html_e( 'Error: Sliders could not be exported', 'fusion-core' ); ?></h2>
							<p><?php esc_html_e( 'Please make sure wp-content/uploads folder is writeable.', 'fusion-core' ); ?></p>
						</div>
						<?php
					} else {
						// Initialize archive object.
						$zip = new ZipArchive();
						$zip->open( $fs_dir . 'avada_slider.zip', ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE );

						$files_iterator = new DirectoryIterator( $fs_dir );

						foreach ( $files_iterator as $file ) {
							if ( $file->isDot() ) {
								continue;
							}

							$zip->addFile( $fs_dir . $file->getFilename(), $file->getFilename() );
						}

						$zip_file = $zip->filename;

						// Zip archive will be created only after closing object.
						$zip->close();

						if ( ob_get_level() ) {
							ob_end_clean();
						}

						header( 'X-Accel-Buffering: no' );
						header( 'Pragma: public' );
						header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
						header( 'Content-Length: ' . filesize( $zip_file ) );
						header( 'Content-Type: application/octet-stream' );
						header( 'Content-Disposition: attachment; filename="avada_slider-' . gmdate( 'd-m-Y' ) . '.zip"' );

						readfile( $zip_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions

						$files_iterator = new DirectoryIterator( $fs_dir );
						foreach ( $files_iterator as $file ) {
							if ( $file->isDot() ) {
								continue;
							}

							$this->filesystem()->delete( $fs_dir . $file->getFilename() );
						}
					}
				}
			}
		}

		/**
		 * Replaces asset url from our demo site.
		 *
		 * @access public
		 * @param array $meta_item Meta data.
		 * @param int   $post_id Post the meta is attached to.
		 */
		public function replace_asset_urls( $meta_item = [], $post_id = 0 ) {
			if ( is_array( $meta_item ) && '_fusion' === $meta_item['key'] && isset( $meta_item['value'] ) ) {
				$values = maybe_unserialize( $meta_item['value'] );

				if ( is_array( $values ) ) {
					$wp_upload_dir = wp_upload_dir();
					$home_url      = untrailingslashit( get_home_url() );

					foreach ( $values as $key => $value ) {
						$sub_array = false;

						if ( is_array( $value ) && isset( $value['url'] ) ) {
							$value     = $value['url'];
							$sub_array = 'url';
						}
						if ( is_string( $value ) ) {
							// Replace URLs.
							$value = str_replace(
								[
									'http://avada.theme-fusion.com/' . $this->demo_type,
									'https://avada.theme-fusion.com/' . $this->demo_type,
								],
								$home_url,
								$value
							);

							// Make sure assets are still from the remote server.
							// We can use http instead of https here for performance reasons
							// since static assets don't require https anyway.
							$value = str_replace(
								$home_url . '/wp-content/',
								'http://avada.theme-fusion.com/' . $this->demo_type . '/wp-content/',
								$value
							);

							if ( $sub_array ) {

								// Video and preview image URL correction.
								if ( is_string( $value ) && false !== strpos( $value, 'wp-content/uploads/sites/' ) ) {
									$parts = explode( 'wp-content/uploads/sites/', $value );
									if ( isset( $parts[1] ) ) {
										$sub_parts = explode( '/', $parts[1] );
										unset( $sub_parts[0] );
										$parts[1] = implode( '/', $sub_parts );

										// append the url to the uploads url.
										$parts[0] = $wp_upload_dir['baseurl'];
										$value    = implode( '/', $parts );
									}
								}

								$values[ $key ][ $sub_array ] = $value;
								continue;
							}

							$values[ $key ] = $value;
						}
					}

					$meta_item['value'] = maybe_serialize( $values );
				}
			}
			return $meta_item;
		}

		/**
		 * Imports sliders from a zip file.
		 *
		 * @access public
		 * @param string $zip_file The path to the zip file.
		 * @param string $demo_type Demo type, used when sliders are imported during demo import process.
		 */
		public function import_sliders( $zip_file = '', $demo_type = null ) {
			if ( isset( $zip_file ) && '' !== $zip_file ) {
				$upload_dir = wp_upload_dir();
				$base_dir   = trailingslashit( $upload_dir['basedir'] );
				$fs_dir     = $base_dir . 'fusion_slider_exports/';

				// Delete entire folder to ensure all it's content is removed.
				$this->filesystem()->delete( $fs_dir, true, 'd' );

				// Attempt to manually extract the zip file first. Required for fptext method.
				if ( class_exists( 'ZipArchive' ) ) {
					$zip = new ZipArchive();
					if ( true === $zip->open( $zip_file ) ) {
						$zip->extractTo( $fs_dir );
						$zip->close();
					}
				}

				unzip_file( $zip_file, $fs_dir );

				// Replace remote URLs with local ones.
				$sliders_xml = $this->filesystem()->get_contents( $fs_dir . 'sliders.xml' );

				// This is run when Avada demo content is imported.
				if ( null !== $demo_type ) {

					$this->demo_type = str_replace( '_', '-', $demo_type );

					add_filter( 'wxr_importer.pre_process.post_meta', [ $this, 'replace_asset_urls' ], 10, 2 );
				}

				$this->filesystem()->put_contents( $fs_dir . 'sliders.xml', $sliders_xml );

				if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
					define( 'WP_LOAD_IMPORTERS', true );
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

					$xml = $fs_dir . 'sliders.xml';

					$logger = new AWB_Importer_Logger();

					// It's important to disable 'prefill_existing_posts'.
					// In case GUID of importing post matches GUID of an existing post it won't be imported.
					$importer = new Fusion_WXR_Importer(
						[
							'fetch_attachments'      => true,
							'prefill_existing_posts' => false,
						]
					);

					$importer->set_logger( $logger );

					add_filter( 'wp_import_post_terms', [ $this, 'add_slider_terms' ], 10, 3 );

					ob_start();
					$importer->import( $xml );
					ob_end_clean();

					remove_filter( 'wp_import_post_terms', [ $this, 'add_slider_terms' ], 10 );

					$loop = new WP_Query(
						[
							'post_type'      => 'slide',
							'posts_per_page' => -1,
							'meta_key'       => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery
						]
					);

					if ( $loop->have_posts() ) {

						while ( $loop->have_posts() ) {
							$loop->the_post();
							$post_thumb_meta = get_post_meta( get_the_ID(), '_thumbnail_id', true );

							if ( isset( $post_thumb_meta ) && $post_thumb_meta ) {
								$thumbnail_ids[ $post_thumb_meta ] = get_the_ID();
							}
						}
					}
					wp_reset_postdata();

					if ( ! $this->filesystem()->is_dir( $fs_dir ) ) {
						return;
					}

					$files_iterator = new DirectoryIterator( $fs_dir );
					foreach ( $files_iterator as $file ) {
						if ( $file->isDot() || '.DS_Store' === $file->getFilename() ) {
							continue;
						}

						$image_path = pathinfo( $fs_dir . $file->getFilename() );

						if ( 'xml' !== $image_path['extension'] && 'json' !== $image_path['extension'] ) {
							$filename          = $image_path['filename'];
							$new_file_basename = wp_unique_filename( $upload_dir['path'] . '/', $image_path['basename'] );
							$new_image_path    = $upload_dir['path'] . '/' . $new_file_basename;
							$new_image_url     = $upload_dir['url'] . '/' . $new_file_basename;
							$this->filesystem()->copy( $fs_dir . $file->getFilename(), $new_image_path, true );

							// Check the type of tile. We'll use this as the 'post_mime_type'.
							$filetype = wp_check_filetype( basename( $new_image_path ), null );

							// Prepare an array of post data for the attachment.
							$attachment = [
								'guid'           => $new_image_url,
								'post_mime_type' => $filetype['type'],
								'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $new_image_path ) ),
								'post_content'   => '',
								'post_status'    => 'inherit',
							];

							// Insert the attachment.
							if ( isset( $thumbnail_ids[ $filename ] ) && $thumbnail_ids[ $filename ] ) {
								$attach_id = wp_insert_attachment( $attachment, $new_image_path, $thumbnail_ids[ $filename ] );

								// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
								require_once wp_normalize_path( ABSPATH . 'wp-admin/includes/image.php' );

								// Generate the metadata for the attachment, and update the database record.
								$attach_data = wp_generate_attachment_metadata( $attach_id, $new_image_path );
								wp_update_attachment_metadata( $attach_id, $attach_data );

								set_post_thumbnail( $thumbnail_ids[ $filename ], $attach_id );

								do_action( 'fusion_slider_import_image_attached', $attach_id, $thumbnail_ids[ $filename ] );
							}
						}
					}

					$url   = wp_nonce_url( 'edit.php?post_type=slide&page=avada_slider_export_import' );
					$creds = request_filesystem_credentials( $url, '', false, false, null );

					if ( false === $creds ) {
						return; // Stop processing here.
					}

					if ( WP_Filesystem( $creds ) ) {
						global $wp_filesystem;

						$settings = $wp_filesystem->get_contents( $fs_dir . 'settings.json' );

						$decode = json_decode( $settings, true );

						if ( is_array( $decode ) ) {
							foreach ( $decode as $slug => $settings ) {
								$get_term = get_term_by( 'slug', $slug, 'slide-page' );

								if ( $get_term ) {
									fusion_data()->term_meta( $get_term->term_id )->set_raw( $settings );
								}
							}
						}
					}
				}
			} else {
				?>
				<div class="avada-db-card avada-db-notice">
					<h2><?php esc_html_e( 'Error: No file to import', 'fusion-core' ); ?></h2>
					<p><?php esc_html_e( 'Please make sure to upload a zip file containing valid Avada Slider export data.', 'fusion-core' ); ?></p>
				</div>
				<?php
			}
		}

		/**
		 * Correcting importer bug which uses 'wp_set_post_terms' to set terms for all post types.
		 * This is used to create 'slide-page' term (if it doesn't exist) and set it to a 'slide' post.
		 *
		 * @param array $terms Post terms.
		 * @param int   $post_id Post ID.
		 * @param array $data Raw data imported for the post.
		 *
		 * @return mixed
		 */
		public function add_slider_terms( $terms, $post_id, $data ) {
			if ( ! empty( $terms ) ) {

				$term_ids = [];
				foreach ( $terms as $term ) {

					if ( ! term_exists( $term['slug'], $term['taxonomy'] ) ) {
						wp_insert_term(
							$term['name'],
							$term['taxonomy'],
							[
								'slug' => $term['slug'],
							]
						);

						$t = get_term_by( 'slug', $term['slug'], $term['taxonomy'], ARRAY_A );
						do_action( 'fusion_slider_import_processed_term', $t['term_id'], $t );
					} else {
						$t = get_term_by( 'slug', $term['slug'], $term['taxonomy'], ARRAY_A );
					}

					$term_ids[ $term['taxonomy'] ][] = (int) $t['term_id'];
				}

				foreach ( $term_ids as $tax => $ids ) {
					wp_set_object_terms( $post_id, $ids, $tax );
				}
			}

			return $terms;
		}

		/**
		 * Clones the slide button.
		 *
		 * @access public
		 * @param array  $actions An array of actions.
		 * @param object $post    The post object.
		 */
		public function admin_clone_slide_button( $actions, $post ) {
			if ( current_user_can( 'edit_others_posts' ) && 'slide' === $post->post_type ) {
				$actions['clone_slide'] = '<a href="' . $this->get_slide_clone_link( $post->ID ) . '" title="' . esc_attr__( 'Clone this slide', 'fusion-core' ) . '">' . esc_html__( 'Clone', 'fusion-core' ) . '</a>';
			}
			return $actions;
		}

		/**
		 * Clones the slider button.
		 *
		 * @access public
		 * @param array  $actions An array of actions.
		 * @param object $term    The term object.
		 */
		public function admin_clone_slider_button( $actions, $term ) {
			$args = [
				'slider_id'                  => $term->term_id,
				'_fusion_slider_clone_nonce' => wp_create_nonce( 'clone_slider' ),
				'action'                     => 'clone_fusion_slider',
			];

			$url = add_query_arg( $args, admin_url( 'edit-tags.php' ) );

			$actions['clone_slider'] = "<a href='{$url}' title='" . esc_attr__( 'Clone this slider', 'fusion-core' ) . "'>" . esc_html__( 'Clone', 'fusion-core' ) . '</a>';

			return $actions;
		}

		/**
		 * Clones the slider button edit form.
		 *
		 * @access public
		 * @param object $term The term object.
		 */
		public function admin_clone_slider_button_edit_form( $term ) {

			if ( isset( $_GET['taxonomy'] ) && 'slide-page' === $_GET['taxonomy'] && current_user_can( 'edit_others_posts' ) ) { // phpcs:ignore WordPress.Security

				$args = [
					'slider_id'                  => $term->term_id,
					'_fusion_slider_clone_nonce' => wp_create_nonce( 'clone_slider' ),
					'action'                     => 'clone_fusion_slider',
				];

				$url = add_query_arg( $args, admin_url( 'edit-tags.php' ) );
				include FUSION_CORE_PATH . '/fusion-slider/templates/clone-button-edit-form.php';
			}
		}

		/**
		 * Clones the slider button after the title.
		 *
		 * @access public
		 * @param object $post The post object.
		 */
		public function admin_clone_slide_button_after_title( $post ) {
			if ( isset( $_GET['post'] ) && current_user_can( 'edit_others_posts' ) && 'slide' === $post->post_type ) { // phpcs:ignore WordPress.Security
				include FUSION_CORE_PATH . '/fusion-slider/templates/clone-button-after-title.php';
			}
		}

		/**
		 * Saves a new slider.
		 *
		 * @access public
		 */
		public function save_as_new_slider() {
			if ( isset( $_REQUEST['_fusion_slider_clone_nonce'] ) && isset( $_REQUEST['slider_id'] ) && check_admin_referer( 'clone_slider', '_fusion_slider_clone_nonce' ) && current_user_can( 'manage_categories' ) ) {
				$term_id            = wp_unslash( $_REQUEST['slider_id'] ); // phpcs:ignore WordPress.Security
				$term_tax           = 'slide-page';
				$original_term      = get_term( $term_id, $term_tax );
				$original_term_meta = fusion_data()->term_meta( $term_id )->get_all_meta();

				/* translators: The term title. */
				$new_term_name = sprintf( esc_attr__( '%s ( Cloned )', 'fusion-core' ), $original_term->name );

				$term_details = [
					'description' => $original_term->description,
					'slug'        => wp_unique_term_slug( $original_term->slug, $original_term ),
					'parent'      => $original_term->parent,
				];

				$new_term = wp_insert_term( $new_term_name, $term_tax, $term_details );

				if ( ! is_wp_error( $new_term ) ) {

					// Add slides (posts) to new slider (term).
					$posts = get_objects_in_term( $term_id, $term_tax );

					if ( ! is_wp_error( $posts ) ) {
						foreach ( $posts as $post_id ) {
							$result = wp_set_post_terms( $post_id, $new_term['term_id'], $term_tax, true );
						}
					}

					// Clone slider (term) meta.
					if ( isset( $original_term_meta ) ) {
						$t_id = $new_term['term_id'];
						fusion_data()->term_meta( $t_id )->set_raw( $original_term_meta );
					}

					// Redirect to the all sliders screen.
					wp_safe_redirect( admin_url( 'edit-tags.php?taxonomy=slide-page&post_type=slide' ) );
				}
			}
		}

		/**
		 * Gets the link to clone a slide.
		 *
		 * @access public
		 * @param int $id The post-id.
		 * @return string
		 */
		public function get_slide_clone_link( $id = 0 ) {

			if ( ! current_user_can( 'edit_others_posts' ) ) {
				return;
			}

			$post = get_post( $id );
			if ( ! $post ) {
				return;
			}

			$args = [
				'_fusion_slide_clone_nonce' => wp_create_nonce( 'clone_slide' ),
				'post'                      => $post->ID,
				'action'                    => 'save_as_new_slide',
			];

			$url = add_query_arg( $args, admin_url( 'admin.php' ) );

			return $url;
		}

		/**
		 * Saves a new slide.
		 *
		 * @access public
		 */
		public function save_as_new_slide() {

			if ( ! ( isset( $_GET['post'] ) || isset( $_POST['post'] ) || ( isset( $_REQUEST['action'] ) && 'save_as_new_slide' === $_REQUEST['action'] ) ) ) { // phpcs:ignore WordPress.Security
				wp_die( esc_html__( 'No slide to clone.', 'fusion-core' ) );
			}

			if ( isset( $_REQUEST['_fusion_slide_clone_nonce'] ) && check_admin_referer( 'clone_slide', '_fusion_slide_clone_nonce' ) && current_user_can( 'edit_others_posts' ) ) {

				// Get the post being copied.
				$id   = isset( $_GET['post'] ) ? wp_unslash( $_GET['post'] ) : wp_unslash( $_POST['post'] ); // phpcs:ignore WordPress.Security
				$post = get_post( $id );

				// Copy the post and insert it.
				if ( isset( $post ) && $post ) {
					$new_id = $this->clone_slide( $post );

					// Redirect to the all slides screen.
					wp_safe_redirect( admin_url( 'edit.php?post_type=' . $post->post_type ) );

					exit;

				} else {
					/* translators: The ID found. */
					wp_die( sprintf( esc_html__( 'Cloning failed. Post not found. ID: %s', 'fusion-core' ), htmlspecialchars( $id ) ) ); // phpcs:ignore WordPress.Security
				}
			}
		}

		/**
		 * Clones a slide.
		 *
		 * @access public
		 * @param object $post The post object.
		 */
		public function clone_slide( $post ) {
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
				'post_title'     => sprintf( esc_attr__( '%s ( Cloned )', 'fusion-core' ), $post->post_title ),
				'post_type'      => $post->post_type,
			];

			// Add new slide post.
			$new_post_id = wp_insert_post( $new_post );

			// Set a proper slug.
			$post_name             = wp_unique_post_slug( $post->post_name, $new_post_id, 'publish', $post->post_type, $new_post_parent );
			$new_post              = [];
			$new_post['ID']        = $new_post_id;
			$new_post['post_name'] = $post_name;

			wp_update_post( $new_post );
			update_post_meta( $new_post_id, '_thumbnail_id', get_post_thumbnail_id( $post->ID ) );

			// Post terms.
			wp_set_object_terms(
				$new_post_id,
				wp_get_object_terms(
					$post->ID,
					'slide-page',
					[ 'fields' => 'ids' ]
				),
				'slide-page'
			);

			wp_get_post_terms( $post->ID, 'slide-page' );

			// Clone post meta.
			if ( ! empty( $post_meta ) ) {
				foreach ( $post_meta as $key => $val ) {
					fusion_data()->post_meta( $new_post_id )->set( $key, $val );
				}
			}

			return $new_post_id;
		}

		/**
		 * Renders a slider.
		 *
		 * @access public
		 * @param string $term The term slug.
		 */
		public static function render_fusion_slider( $term ) {

			$fusion_settings = awb_get_fusion_settings();

			if ( $fusion_settings->get( 'status_fusion_slider' ) ) {
				$term_details    = get_term_by( 'slug', $term, 'slide-page' );
				$slider_settings = [];

				if ( is_object( $term_details ) ) {
					$slider_settings              = fusion_data()->term_meta( $term_details->term_id )->get_all_meta();
					$slider_settings['slider_id'] = $term_details->term_id;
				} else {
					$slider_settings['slider_id'] = '0';
				}

				if ( ! isset( $slider_settings['typo_sensitivity'] ) ) {
					$slider_settings['typo_sensitivity'] = '0.1';
				}

				if ( ! isset( $slider_settings['typo_factor'] ) ) {
					$slider_settings['typo_factor'] = '1.5';
				}

				if ( ! isset( $slider_settings['slider_width'] ) || '' === $slider_settings['slider_width'] ) {
					$slider_settings['slider_width'] = '100%';
				}

				if ( ! isset( $slider_settings['slider_height'] ) || '' === $slider_settings['slider_height'] ) {
					$slider_settings['slider_height'] = '500px';
				}

				if ( ! isset( $slider_settings['orderby'] ) ) {
						$slider_settings['orderby'] = 'date';
				}

				if ( ! isset( $slider_settings['order'] ) ) {
						$slider_settings['order'] = 'DESC';
				}

				if ( ! isset( $slider_settings['full_screen'] ) ) {
					$slider_settings['full_screen'] = false;
				}

				if ( ! isset( $slider_settings['animation'] ) ) {
					$slider_settings['animation'] = true;
				}

				if ( ! isset( $slider_settings['nav_box_width'] ) ) {
					$slider_settings['nav_box_width'] = '63px';
				}

				if ( ! isset( $slider_settings['nav_box_height'] ) ) {
					$slider_settings['nav_box_height'] = '63px';
				}

				if ( ! isset( $slider_settings['nav_arrow_size'] ) ) {
					$slider_settings['nav_arrow_size'] = '25px';
				}

				$nav_box_height_half = '0';
				if ( $slider_settings['nav_box_height'] ) {
					$nav_box_height_half = intval( $slider_settings['nav_box_height'] ) / 2;
				}

				if ( ! isset( $slider_settings['slider_indicator'] ) ) {
					$slider_settings['slider_indicator'] = '';
				}

				if ( ! isset( $slider_settings['slider_indicator_color'] ) || '' === $slider_settings['slider_indicator_color'] ) {
					$slider_settings['slider_indicator_color'] = '#ffffff';
				}

				$slider_data = '';

				if ( $slider_settings ) {
					foreach ( $slider_settings as $slider_setting => $slider_setting_value ) {
						if ( is_string( $slider_setting ) && is_string( $slider_setting_value ) ) {
							$slider_data .= 'data-' . $slider_setting . '="' . $slider_setting_value . '" ';
						}
					}
				}

				$slider_class = '';

				if ( '100%' === $slider_settings['slider_width'] && ! $slider_settings['full_screen'] ) {
					$slider_class .= ' full-width-slider';
				} elseif ( '100%' !== $slider_settings['slider_width'] && ! $slider_settings['full_screen'] ) {
					$slider_class .= ' fixed-width-slider';
				}

				if ( isset( $slider_settings['slider_content_width'] ) && '' !== $slider_settings['slider_content_width'] ) {
					$content_max_width = 'max-width:' . $slider_settings['slider_content_width'];
				} else {
					$content_max_width = '';
				}

				$args = [
					'post_type'        => 'slide',
					'posts_per_page'   => -1,
					'suppress_filters' => 0,
					'orderby'          => $slider_settings['orderby'],
					'order'            => $slider_settings['order'],
				];

				$args['tax_query'][] = [
					'taxonomy' => 'slide-page',
					'field'    => 'slug',
					'terms'    => $term,
				];

				$query = FusionCore_Plugin::fusion_core_cached_query( $args );

				if ( $query->have_posts() ) {
					include FUSION_CORE_PATH . '/fusion-slider/templates/slider.php';
				}

				wp_reset_postdata();

				do_action( 'fusion_slider_enqueue' );
			}
		}

		/**
		 * Gets the $wp_filesystem.
		 *
		 * @access private
		 * @since 3.1
		 * @return object
		 */
		private function filesystem() {
			// The WordPress filesystem.
			global $wp_filesystem;

			if ( empty( $wp_filesystem ) ) {
				require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
				WP_Filesystem();
			}
			return $wp_filesystem;
		}
	}

	$fusion_slider = new Fusion_Slider();
}

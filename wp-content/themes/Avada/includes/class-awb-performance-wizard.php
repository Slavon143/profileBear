<?php
/**
 * Needed functionality for the performance wizard.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      7.4
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 */

/**
 * Performance wizard handling.
 *
 * @since 7.4
 */
class AWB_Performance_Wizard {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 7.4
	 * @var object
	 */
	private static $instance;

	/**
	 * The array of parsed posts.
	 *
	 * @access protected
	 * @var array
	 */
	protected $posts = [];

	/**
	 * Map between what is matched in content and other icon data.
	 *
	 * @access protected
	 * @var array
	 */
	protected $icon_map = [];

	/**
	 * Icon subsets.
	 *
	 * @access protected
	 * @var array
	 */
	protected $icon_subsets = [
		'fab' => 'brands',
		'far' => 'regular',
		'fas' => 'solid',
		'fal' => 'light',
	];

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 7.4
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new AWB_Performance_Wizard();
		}
		return self::$instance;
	}

	/**
	 * The class constructor
	 *
	 * @access public
	 */
	public function __construct() {
		// Scan ajax.
		add_action( 'wp_ajax_awb_performance_scan', [ $this, 'ajax_scan' ] );

		// Save ajax.
		add_action( 'wp_ajax_awb_performance_save', [ $this, 'ajax_save' ] );

		// Save ajax.
		add_action( 'wp_ajax_awb_performance_cache', [ $this, 'ajax_cache' ] );

		// Trigger icon download.
		add_action( 'init', [ $this, 'download_icon' ] );

		if ( ! fusion_doing_ajax() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'add_scripts' ] );
		}

		if ( ( isset( $_GET['page'] ) && 'avada-performance' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_filter( 'awb_po_get_value', [ $this, 'global_value' ], 10, 2 );
			add_filter( 'awb_po_get_option_name', [ $this, 'global_name' ], 10, 2 );
		}
	}

	/**
	 * Return global value instead of usual page option.
	 *
	 * @access public
	 * @param string $value The value.
	 * @param int    $id    The ID.
	 * @return string
	 */
	public function global_value( $value, $id ) {
		$settings = awb_get_fusion_settings();
		return $settings->get( $id );
	}

	/**
	 * Override root so IDs are correct.
	 *
	 * @access public
	 * @param string $name The name.
	 * @param int    $id   The ID.
	 * @return string
	 */
	public function global_name( $name, $id ) {
		return str_replace( '[]', '', ltrim( $id, '_' ) );
	}

	/**
	 * Enequeue required scripts.
	 *
	 * @access public
	 * @return void
	 */
	public function add_scripts() {
		$version = Avada::get_theme_version();
		wp_enqueue_style( 'awb_performance_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/awb-wizard.css', [], $version );

		AWB_Global_Typography()->enqueue();

		wp_enqueue_script( 'awb_performance_js', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/awb-wizard.js', [ 'jquery' ], $version, true );

		wp_localize_script( 'awb_performance_js', 'fusionBuilderText', fusion_app_textdomain_strings() );

		$api_key = apply_filters( 'awb_lighthouse_api_key', false );

		wp_localize_script(
			'awb_performance_js',
			'awbPerformance',
			[
				'homeURL'            => get_home_url(),
				'lighthouse'         => ! empty( $_GET['lighthouse'] ) || false !== $api_key, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'apiKey'             => $api_key,
				'saveChange'         => __( 'Do you want to proceed without saving changes?', 'Avada' ),
				'loadingHome'        => __( 'Loading the homepage to generate assets.', 'Avada' ),
				'performLighthouse'  => __( 'Running a lighthouse test to find new performance scores.', 'Avada' ),
				'errorLoadingPage'   => __( 'Automated asset generation failed. Visit your homepage in the browser.', 'Avada' ),
				'errorClearingCache' => __( 'There was a problem when clearing the cache. Please clear it from the Global Options.', 'Avada' ),
				'wizardComplete'     => __( 'Cache and assets have been cleared successfully!', 'Avada' ),
				'scanError'          => __( 'Something went wrong while scanning the content, please check PHP error log and try again.', 'Avada' ),
			]
		);

		// Select field assets.
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

		// Range field assets.
		wp_enqueue_style(
			'avadaredux-nouislider-css',
			FUSION_LIBRARY_URL . '/inc/redux/framework/FusionReduxCore/inc/fields/slider/vendor/nouislider/fusionredux.jquery.nouislider.css',
			[],
			'5.0.0',
			'all'
		);

		wp_enqueue_script(
			'avadaredux-nouislider-js',
			Avada::$template_dir_url . '/assets/admin/js/jquery.nouislider.min.js',
			[ 'jquery' ],
			'5.0.0',
			true
		);
		wp_enqueue_script(
			'wnumb-js',
			Avada::$template_dir_url . '/assets/admin/js/wNumb.js',
			[ 'jquery' ],
			'1.0.2',
			true
		);

		// Color fields.
		if ( function_exists( 'AWB_Global_Colors' ) ) {
			AWB_Global_Colors()->enqueue();
		}

		// Option type JS.
		wp_enqueue_script(
			'avada-fusion-options',
			Avada::$template_dir_url . '/assets/admin/js/avada-fusion-options.js',
			[ 'jquery', 'jquery-ui-sortable' ],
			$version,
			false
		);
	}

	/**
	 * Check if nonce is valid.
	 *
	 * @access public
	 */
	public function check_nonce() {
		check_admin_referer( 'awb_performance_nonce', 'awb_performance_nonce' );
	}

	/**
	 * Handles resetting caches.
	 *
	 * @access public
	 * @since 7.4
	 * @return void
	 */
	public function ajax_cache() {

		$this->check_nonce();

		if ( is_multisite() && is_main_site() ) {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				fusion_reset_all_caches();
				restore_current_blog();
			}
			return;
		}
		fusion_reset_all_caches();
	}

	/**
	 * Save global option changes.
	 *
	 * @access public
	 */
	public function ajax_save() {
		$this->check_nonce();

		$save_data = isset( $_POST['save_data'] ) ? wp_unslash( $_POST['save_data'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! $save_data || ! is_array( $save_data ) ) {
			wp_send_json_error( __( 'No data to save.', 'Avada' ) );
		}

		// Handle element saving, separate location.
		if ( isset( $_POST['step'] ) && 'elements' === $_POST['step'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$builder_options = get_option( 'fusion_builder_settings', [] );

			if ( ! $builder_options ) {
				$builder_options = [];
			}

			$builder_options['fusion_elements'] = $save_data;
			update_option( 'fusion_builder_settings', $builder_options );
			wp_send_json_success();
		}

		// Handle global option saving.
		$options = get_option( Avada::get_option_name(), [] );
		foreach ( $save_data as $save_id => $save_value ) {

			// Check for typography options.
			if ( is_array( $save_value ) && ( isset( $save_value['font-variant'] ) || isset( $save_value['font-family'] ) ) ) {
				if ( isset( $save_value['font-variant'] ) ) {
					if ( false !== strpos( $save_value['font-variant'], 'italic' ) ) {
						$options[ $save_id ]['font-weight'] = wp_unslash( str_replace( 'italic', '', $save_value['font-variant'] ) );
						$options[ $save_id ]['font-style']  = 'italic';
					} else {
						$options[ $save_id ]['font-weight'] = wp_unslash( $save_value['font-variant'] );
						$options[ $save_id ]['font-style']  = '';
					}
				}
				if ( isset( $save_value['font-family'] ) ) {
					$options[ $save_id ]['font-family'] = wp_unslash( $save_value['font-family'] );
				}
				continue;
			}

			// Not a font variant, save as normal.
			$options[ $save_id ] = wp_unslash( $save_value );
		}

		$updated = update_option( Avada::get_option_name(), $options );

		if ( ! $updated ) {
			wp_send_json_success( __( 'No changes to update.', 'Avada' ) );
		}

		wp_send_json_success();
	}

	/**
	 * Perform scan and return results.
	 *
	 * @access public
	 */
	public function ajax_scan() {

		$this->check_nonce();

		$scan_type = isset( $_GET['scan_type'] ) ? wp_unslash( $_GET['scan_type'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! $scan_type ) {
			wp_send_json_error( new WP_Error( 404, __( 'No valid action found.', 'Avada' ) ) );
		}

		switch ( $scan_type ) {
			case 'features':
				$this->feature_scan();
				break;
			case 'icons':
				$this->icon_scan();
				break;
			case 'elements':
				$this->element_scan();
				break;
		}
	}

	/**
	 * Download Font Awesome icon.
	 *
	 * @access public
	 */
	public function download_icon() {
		if ( ! isset( $_GET['action'] ) || 'awb_trigger_icon_download' !== $_GET['action'] ) { // phpcs:ignore WordPress.Security
			return;
		}

		$this->check_nonce();

		$icon_subset = isset( $_GET['icon_subset'] ) ? sanitize_text_field( wp_unslash( $_GET['icon_subset'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$icon_name   = isset( $_GET['icon_name'] ) ? sanitize_text_field( wp_unslash( $_GET['icon_name'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$download_url = isset( $_GET['download_url'] ) ? strtok( sanitize_text_field( wp_unslash( $_GET['download_url'] ) ), '?' ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$download_url = 'https://raw.githubusercontent.com/FortAwesome/Font-Awesome/master/svgs/' . $icon_subset . '/' . $icon_name . '.svg';

		$response = wp_remote_get( $download_url );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			// TODO: add error message.
			wp_die();
		}

		header( 'Content-Description: File Transfer' );
		header( 'Content-type: image/svg+xml' );
		header( 'Content-Disposition: attachment; filename="' . $icon_name . '-' . $icon_subset . '.svg"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );

		echo wp_remote_retrieve_body( $response ); // phpcs:ignore WordPress.Security.EscapeOutput
		wp_die();
	}

	/**
	 * Scan for unused features which can be disabled.
	 *
	 * @access public
	 */
	public function feature_scan() {
		$recommendations = [];
		$maps            = $this->scan_for_google_map();
		$youtube         = $this->scan_for_videos( 'youtube' );
		$vimeo           = $this->scan_for_videos( 'vimeo' );
		$mega_menu       = $this->scan_for_megamenu();

		if ( ! $mega_menu ) {
			$recommendations['disable_megamenu'] = [
				'value'   => '0',
				'message' => __( 'You are not using mega menu. This option can be disabled.', 'Avada' ),
				'dynamic' => true,
			];
		} else {
			$recommendations['disable_megamenu'] = [
				'value'   => '1',
				'message' => __( 'You are using mega menu. Should be enabled.', 'Avada' ),
				'dynamic' => true,
			];
		}

		if ( ! $youtube ) {
			$recommendations['status_yt'] = [
				'value'   => '0',
				'message' => __( 'You are not using any Youtube element. This option can be disabled.', 'Avada' ),
				'dynamic' => true,
			];
		} else {
			$recommendations['status_yt'] = [
				'value'   => '1',
				'message' => __( 'You are using Youtube element. Should be enabled.', 'Avada' ),
				'dynamic' => true,
			];
		}

		if ( ! $vimeo ) {
			$recommendations['status_vimeo'] = [
				'value'   => '0',
				'message' => __( 'You are not using any Vimeo element. This option can be disabled.', 'Avada' ),
				'dynamic' => true,
			];
		} else {
			$recommendations['status_vimeo'] = [
				'value'   => '1',
				'message' => __( 'You are using Vimeo element. Should be enabled.', 'Avada' ),
				'dynamic' => true,
			];
		}

		if ( ! $maps ) {
			$recommendations['status_gmap'] = [
				'value'   => '0',
				'message' => __( 'You are not using any Google Map element or template. This option can be disabled.', 'Avada' ),
				'dynamic' => true,
			];
		} else {
			$recommendations['status_gmap'] = [
				'value'   => '1',
				'message' => __( 'You are using Google Map element or template. Should be enabled.', 'Avada' ),
				'dynamic' => true,
			];
		}

		// TODO: make somewhat dynamic. button_presets.
		$recommendations['button_presets'] = [
			'value'   => '0',
			'message' => __( 'Recommend disabling unless you are using the button color presets in many areas.', 'Avada' ),
			'dynamic' => true,
		];

		// TODO: make somewhat dynamic. load_block_styles.
		$recommendations['load_block_styles'] = [
			'value'   => 'off',
			'message' => __( 'Recommend setting to off unless you use the Gutenberg editor.', 'Avada' ),
			'dynamic' => false,
		];

		// TODO: make somewhat dynamic. emojis_disabled.
		$recommendations['emojis_disabled'] = [
			'value'   => 'disabled',
			'message' => __( 'Recommend setting to disabled unless you specifically want them for comments.', 'Avada' ),
			'dynamic' => false,
		];

		// avada_rev_styles.
		if ( ! class_exists( 'RevSliderFront' ) ) {
			$recommendations['avada_rev_styles'] = [
				'value'   => '0',
				'message' => __( 'The Slider Revolution plugin is not active, styles can be disabled.', 'Avada' ),
				'dynamic' => true,
			];
		} else {
			$recommendations['avada_rev_styles'] = [
				'value'   => '1',
				'message' => __( 'The Slider Revolution plugin is currently active, only disable if you don\'t want the extra Avada styling.', 'Avada' ),
				'dynamic' => true,
			];
		}

		// status_eslider.
		$elastic_sliders = new WP_Query( [ 'post_type' => 'themefusion_elastic' ] );
		if ( ! $elastic_sliders->have_posts() ) {
			$recommendations['status_eslider'] = [
				'value'   => '0',
				'message' => __( 'No elastic sliders found, should be disabled.', 'Avada' ),
				'dynamic' => true,
			];
		} else {
			$recommendations['status_eslider'] = [
				'value'   => '1',
				'message' => __( 'Elastic sliders found, only disable if you are not using them.', 'Avada' ),
				'dynamic' => true,
			];
		}

		// status_fusion_slider.
		$avada_sliders = new WP_Query( [ 'post_type' => 'slide' ] );
		if ( ! $avada_sliders->have_posts() ) {
			$recommendations['status_fusion_slider'] = [
				'value'   => '0',
				'message' => __( 'No Avada sliders found, should be disabled.', 'Avada' ),
				'dynamic' => true,
			];
		} else {
			$recommendations['status_fusion_slider'] = [
				'value'   => '1',
				'message' => __( 'Avada sliders found, only disable if you are not using them.', 'Avada' ),
				'dynamic' => true,
			];
		}

		// status_fusion_forms.
		$avada_forms = new WP_Query( [ 'post_type' => 'fusion_form' ] );
		if ( ! $avada_forms->have_posts() ) {
			$recommendations['status_fusion_forms'] = [
				'value'   => '0',
				'message' => __( 'No Avada forms found, can be disabled. Alternatively if you haven\'t tried them yet, give them a go.', 'Avada' ),
				'dynamic' => true,
			];
		} else {
			$recommendations['status_fusion_forms'] = [
				'value'   => '1',
				'message' => __( 'Avada forms found, only disable if you are not using them.', 'Avada' ),
				'dynamic' => true,
			];
		}

		// status_awb_Off_Canvas.
		$avada_off_canvas = new WP_Query( [ 'post_type' => 'awb_off_canvas' ] );
		if ( ! $avada_off_canvas->have_posts() ) {
			$recommendations['status_awb_Off_Canvas'] = [
				'value'   => '0',
				'message' => __( 'No Avada off canvas found, can be disabled. Alternatively if you haven\'t tried them yet, give them a go.', 'Avada' ),
				'dynamic' => true,
			];
		} else {
			$recommendations['status_awb_Off_Canvas'] = [
				'value'   => '1',
				'message' => __( 'Avada off canvas found, only disable if you are not using them.', 'Avada' ),
				'dynamic' => true,
			];
		}

		// status_fusion_portfolio.
		$avada_portfolios = new WP_Query( [ 'post_type' => 'avada_portfolio' ] );
		if ( ! $avada_portfolios->have_posts() ) {
			$recommendations['status_fusion_portfolio'] = [
				'value'   => '0',
				'message' => __( 'No portfolio posts found, can be disabled.', 'Avada' ),
				'dynamic' => true,
			];
		} else {
			$recommendations['status_fusion_portfolio'] = [
				'value'   => '1',
				'message' => __( 'Portfolio posts found, only disable if you are not using them.', 'Avada' ),
				'dynamic' => true,
			];
		}

		// status_fusion_faqs.
		$avada_faqs = new WP_Query( [ 'post_type' => 'avada_faq' ] );
		if ( ! $avada_faqs->have_posts() ) {
			$recommendations['status_fusion_faqs'] = [
				'value'   => '0',
				'message' => __( 'No FAQ posts found, can be disabled.', 'Avada' ),
				'dynamic' => true,
			];
		} else {
			$recommendations['status_fusion_faqs'] = [
				'value'   => '1',
				'message' => __( 'FAQ posts found, only disable if you are not using them.', 'Avada' ),
				'dynamic' => true,
			];
		}

		wp_send_json_success( $recommendations );
	}

	/**
	 * AJAX callback method, used to get used icons table.
	 *
	 * @access public
	 */
	public function icon_scan() {

		$found_icons = $this->scan_for_icons();
		$full_icons  = [];

		$fa_4_class = ' class="awb-fa-legacy-icon"';

		ob_start();
		foreach ( $found_icons as $found_icon ) : ?>
			<tr>
				<td>
					<?php
					$edit_link  = '';
					$edit_title = '';

					if ( isset( $found_icon['data']['post_type'] ) ) {

						$edit_title = $found_icon['data']['post_title'];

						if ( 'nav_menu_item' !== $found_icon['data']['post_type'] ) {
							$edit_link = admin_url( 'post.php?post=' . $found_icon['data']['post_id'] . '&action=edit' );
						} elseif ( 'nav_menu_item' === $found_icon['data']['post_type'] ) {
							$nav_terms = wp_get_object_terms( $found_icon['data']['post_id'], 'nav_menu' );

							if ( ! is_wp_error( $nav_terms ) && isset( $nav_terms[0] ) ) {
								$edit_link = admin_url( 'nav-menus.php?action=edit&menu=' . $nav_terms[0]->term_id );
							}

							// Check if menu item points to a page and didnt have post title saved.
							if ( '' === $edit_title && '' !== get_post_meta( $found_icon['data']['post_id'], '_menu_item_object_id', true ) ) {

								if ( 'post_type' === get_post_meta( $found_icon['data']['post_id'], '_menu_item_type', true ) ) {
									$edit_title = get_the_title( get_post_meta( $found_icon['data']['post_id'], '_menu_item_object_id', true ) );
								}

								if ( 'taxonomy' === get_post_meta( $found_icon['data']['post_id'], '_menu_item_type', true ) ) {
									$term = get_term( get_post_meta( $found_icon['data']['post_id'], '_menu_item_object_id', true ), get_post_meta( $found_icon['data']['post_id'], '_menu_item_object', true ) );

									if ( ! is_wp_error( $term ) ) {
										$edit_title = $term->name;
									}
								}
							}
						}
					} elseif ( isset( $found_icon['widget'] ) ) {
						$edit_title = $found_icon['widget']['widget_type'] . ' - ' . $found_icon['widget']['widget_area'];
						$edit_link  = admin_url( 'widgets.php' );
					}
					?>

					<a href="<?php echo esc_url_raw( $edit_link ); ?>" target="_blank">
					<?php echo esc_html( $edit_title ); ?>
					</a>
				</td>
				<td>
					<?php
					if ( isset( $found_icon['post_content']['icons']['matches'] ) ) {
						$full_icons = array_merge( $full_icons, $found_icon['post_content']['icons']['matches'] );
						foreach ( array_unique( $found_icon['post_content']['icons']['matches'] ) as $key => $icon ) {
							$css_class = '';
							if ( isset( $this->icon_map[ $icon ] ) && true === $this->icon_map[ $icon ]['is_fa4_icon'] ) {
								$css_class = $fa_4_class;
							}

							echo '<span' . $css_class . '>' . esc_html( $icon ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
						}
					} elseif ( isset( $found_icon['widget']['icons']['matches'] ) ) {
						$full_icons = array_merge( $full_icons, $found_icon['widget']['icons']['matches'] );
						foreach ( array_unique( $found_icon['widget']['icons']['matches'] ) as $key => $icon ) {
							$css_class = '';
							if ( isset( $this->icon_map[ $icon ] ) && true === $this->icon_map[ $icon ]['is_fa4_icon'] ) {
								$css_class = $fa_4_class;
							}
							echo '<span' . $css_class . '>' . esc_html( $icon ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
						}
					}
					?>
				</td>
				<td>
					<?php
					if ( isset( $found_icon['post_meta']['icons']['matches'] ) ) {
						$full_icons = array_merge( $full_icons, $found_icon['post_meta']['icons']['matches'] );
						foreach ( array_unique( $found_icon['post_meta']['icons']['matches'] ) as $key => $icon ) {
							$css_class = '';
							if ( isset( $this->icon_map[ $icon ] ) && true === $this->icon_map[ $icon ]['is_fa4_icon'] ) {
								$css_class = $fa_4_class;
							}
							echo '<span' . $css_class . '>' . esc_html( $icon ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
						}
					}
					?>
				</td>
				<td></td>
			</tr>
			<?php
		endforeach;

		$full_icon_markup = '';
		foreach ( array_unique( $full_icons ) as $icon ) {
			$data_download = '';
			if ( isset( $this->icon_map[ $icon ] ) ) {
				$icon_link_subset   = isset( $this->icon_subsets[ $this->icon_map[ $icon ]['fa5_subset'] ] ) ? $this->icon_subsets[ $this->icon_map[ $icon ]['fa5_subset'] ] : 'solid';
				$icon_name          = $this->icon_map[ $icon ]['fa5_name'];
				$icon_download_link = esc_url_raw( admin_url( 'admin-ajax.php?action=awb_trigger_icon_download&icon_subset=' . $icon_link_subset . '&icon_name=' . $icon_name . '&awb_performance_nonce=' . wp_create_nonce( 'awb_performance_nonce' ) ) );

				$data_download = ' href="' . $icon_download_link . '" target="_blank"';
			}

			$full_icon_markup .= '<a' . $data_download . ' class="awb-download-icon-link">' . esc_html( $icon ) . '</a>';
		}
		if ( '' !== $full_icon_markup ) {
			echo '<tr class="full-list"><td><strong>' . __( 'Full List', 'Avada' ) . '<strong></td><td colspan="3">' . $full_icon_markup . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}

		$return_data = [
			'markup'          => ob_get_clean(),
			'recommendations' => [],
		];

		$used_subsets = [];
		foreach ( [ 'fab', 'far', 'fas', 'fal' ] as $subset ) {
			if ( false !== strpos( $full_icon_markup, ' ' . $subset ) || false !== strpos( $full_icon_markup, $subset . ' ' ) ) {
				$used_subsets[] = $subset;
			}
		}

		$labels = [
			'fab' => esc_html__( 'Brands', 'Avada' ),
			'far' => esc_html__( 'Regular', 'Avada' ),
			'fas' => esc_html__( 'Solid', 'Avada' ),
			'fal' => esc_html__( 'Light', 'Avada' ),
		];

		$icon_message = __( 'No Font Awesome icons found being used. Recommend disabling all.', 'Avada' );
		if ( ! empty( $used_subsets ) ) {
			foreach ( $used_subsets as $used_subset ) {
				$used_subset_labels[] = $labels[ $used_subset ];
			}
			/* translators: Used subset labels. */
			$icon_message = sprintf( __( 'Content has been scanned for icons, recommend selecting %s.', 'Avada' ), implode( ', ', $used_subset_labels ) );
		}

		$return_data['recommendations']['status_fontawesome'] = [
			'value'   => $used_subsets,
			'message' => $icon_message,
			'dynamic' => true,
		];

		// TODO: scan for old icons.
		$return_data['recommendations']['fontawesome_v4_compatibility'] = [
			'value'   => '0',
			'message' => __( 'Recommended to disable this and update any old icon references by editing the pages/menus.', 'Avada' ),
			'dynamic' => true,
		];

		wp_send_json_success( $return_data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Check all elements on content.
	 *
	 * @access public
	 * @since 7.4
	 * @return void
	 */
	public function element_scan() {
		global $all_fusion_builder_elements;

		$elements = [];

		// No elements found, return empty.
		if ( empty( $all_fusion_builder_elements ) ) {
			wp_send_json_success( $elements );
			die();
		}

		$content = $this->get_content();

		foreach ( $all_fusion_builder_elements as $module ) {
			if ( empty( $module['hide_from_builder'] ) ) {
				if ( false === strpos( $content, $module['shortcode'] ) ) {
					$elements[ $module['shortcode'] ] = true;
				}
			}
		}

		// Add some more checks here at some point.
		unset( $elements['fusion_alert'] );
		unset( $elements['fusion_tooltip'] );
		unset( $elements['fusion_separator'] );

		// If it says to disable post cards but not archives, dont disable either.
		if ( isset( $elements['fusion_post_cards'] ) && ! isset( $elements['fusion_tb_post_card_archives'] ) ) {
			unset( $elements['fusion_post_cards'] );
		}

		if ( isset( $elements['fusion_woo_product_grid'] ) && ! isset( $elements['fusion_tb_woo_archives'] ) ) {
			unset( $elements['fusion_woo_product_grid'] );
		}

		if ( isset( $elements['fusion_blog'] ) && ! isset( $elements['fusion_tb_archives'] ) ) {
			unset( $elements['fusion_blog'] );
		}

		if ( isset( $elements['fusion_button'] ) && ! isset( $elements['fusion_tagline'] ) ) {
			unset( $elements['fusion_button'] );
		}

		if ( isset( $elements['fusion_accordion'] ) && ! isset( $elements['fusion_faq'] ) ) {
			unset( $elements['fusion_accordion'] );
		}

		wp_send_json_success( $elements );
	}

	/**
	 * Scans content for Google Map usage.
	 *
	 * @access public
	 * @since 7.4
	 * @return boolean
	 */
	public function scan_for_google_map() {
		$maps_count = 0;

		// Scan XML content (posts).
		$this->parse_xml();

		foreach ( $this->posts as $post ) {

			if ( 'publish' !== $post['data']['post_status'] && 'draft' !== $post['data']['post_status'] ) {
				continue;
			}

			// Get only stuff we actually use.
			$post_data = [
				'post_id'   => isset( $post['data']['post_id'] ) ? $post['data']['post_id'] : '',
				'post_type' => isset( $post['data']['post_type'] ) ? $post['data']['post_type'] : '',
			];

			if ( isset( $post['data']['post_content'] ) && '' !== $post['data']['post_content'] && false !== strpos( $post['data']['post_content'], 'fusion_map' ) ) {

				$maps_count++;

				// No need to check further.
				break;
			}

			if ( isset( $post['data']['post_type'] ) && isset( $post['data']['post_id'] ) && 'page' === $post['data']['post_type'] && 0 === $maps_count ) {

				$meta = get_post_meta( $post['data']['post_id'], '_wp_page_template', true );

				if ( 'contact.php' === $meta ) {
					$maps_count++;

					// No need to check further.
					break;
				}
			}
		}

		// Scan widgets if not found so far.
		if ( 0 === $maps_count ) {

			$all_widgets = wp_get_sidebars_widgets();
			$widgets     = [];

			foreach ( $all_widgets as $widget_area => $widget_array ) {
				foreach ( $widget_array as $widget_key ) {
					$widget               = [];
					$widget['type']       = trim( substr( $widget_key, 0, strrpos( $widget_key, '-' ) ) );
					$widget['type-index'] = trim( substr( $widget_key, strrpos( $widget_key, '-' ) + 1 ) );
					$widget['value']      = get_option( 'widget_' . $widget['type'] );
					$widget['value']      = $widget['value'][ $widget['type-index'] ];
					$widgets[]            = $widget;

					if ( false !== strpos( maybe_serialize( $widget['value'] ), 'fusion_map' ) ) {
						$maps_count++;

						// No need to check further.
						break;
					}
				}
			}
		}

		return 0 < $maps_count ? true : false;
	}

	/**
	 * Scan content for Youtube usage.
	 *
	 * @access public
	 * @since 7.4
	 * @param string $type The video type.
	 * @return boolean
	 */
	public function scan_for_videos( $type ) {
		$regex_pattern_container = get_shortcode_regex( [ 'fusion_builder_container' ] );
		$count                   = 0;

		// Scan XML content (posts).
		$this->parse_xml();

		foreach ( $this->posts as $post ) {

			if ( 'publish' !== $post['data']['post_status'] && 'draft' !== $post['data']['post_status'] ) {
				continue;
			}

			// Get only stuff we actually use.
			$post_data = [
				'post_id'   => isset( $post['data']['post_id'] ) ? $post['data']['post_id'] : '',
				'post_type' => isset( $post['data']['post_type'] ) ? $post['data']['post_type'] : '',
			];

			if ( ( isset( $post['data']['post_content'] ) && '' !== $post['data']['post_content'] ) || 'slide' === $post['data']['post_type'] ) {

				preg_match_all( "/$regex_pattern_container/s", $post['data']['post_content'], $matches );

				if ( isset( $matches[3] ) && ! empty( $matches[3] ) ) {
					foreach ( $matches[3] as $match ) {
						$atts = shortcode_parse_atts( $match );
						if ( ! empty( $atts['video_url'] ) ) {
							$video = fusion_builder_get_video_provider( $atts['video_url'] );
							if ( $type === $video['type'] ) {
								$count++;

								// No need to check further.
								break 2;
							}
						}
					}
				}

				if ( 0 === $count ) {
					if ( false !== strpos( $post['data']['post_content'], 'fusion_' . $type ) ) {
						$count++;
						// No need to check further.
						break;
					}
				}

				// Scan Fusion Slides.
				if ( 0 === $count && 'slide' === $post_data['post_type'] ) {
					$metadata = wp_parse_args( fusion_data()->post_meta( $post_data['post_id'] )->get_all_meta() );
					if ( $type === $metadata['type'] && '' !== $metadata[ $type . '_id' ] ) {
						$count++;
						// No need to check further.
						break;
					}
				}
			}
		}

		// Scan widgets if not found so far.
		if ( 0 === $count ) {
			$all_widgets = wp_get_sidebars_widgets();
			$widgets     = [];

			foreach ( $all_widgets as $widget_area => $widget_array ) {
				foreach ( $widget_array as $widget_key ) {
					$widget               = [];
					$widget['type']       = trim( substr( $widget_key, 0, strrpos( $widget_key, '-' ) ) );
					$widget['type-index'] = trim( substr( $widget_key, strrpos( $widget_key, '-' ) + 1 ) );
					$widget['value']      = get_option( 'widget_' . $widget['type'] );
					$widget['value']      = $widget['value'][ $widget['type-index'] ];
					$widgets[]            = $widget;

					preg_match_all( "/$regex_pattern_container/s", maybe_serialize( $widget['value'] ), $matches );

					if ( isset( $matches[3] ) && ! empty( $matches[3] ) ) {
						foreach ( $matches[3] as $match ) {
							$atts = shortcode_parse_atts( $match );
							if ( ! empty( $atts['video_url'] ) ) {
								$video = fusion_builder_get_video_provider( $atts['video_url'] );
								if ( $type === $video['type'] ) {
									$count++;

									// No need to check further.
									break 2;
								}
							}
						}
					}

					if ( 0 === $count ) {

						if ( false !== strpos( maybe_serialize( $widget['value'] ), 'fusion_' . $type ) ) {
							$count++;
							// No need to check further.
							break;
						}
					}
				}
			}
		}
		return 0 < $count ? true : false;
	}

	/**
	 * Scan content for mega menu usage.
	 *
	 * @access public
	 * @since 7.4
	 * @return boolean
	 */
	public function scan_for_megamenu() {
		$mega_menu_count = 0;

		// Scan XML content (posts).
		$this->parse_xml();

		foreach ( $this->posts as $post ) {

			if ( 'publish' !== $post['data']['post_status'] && 'draft' !== $post['data']['post_status'] ) {
				continue;
			}

			// Menu items have empty post_content.
			if ( 'nav_menu_item' === $post['data']['post_type'] && isset( $post['meta'] ) && is_array( $post['meta'] ) ) {

				foreach ( $post['meta'] as $meta ) {

					if ( '_menu_item_fusion_megamenu' === $meta['key'] ) {
						$values = maybe_unserialize( $meta['value'] );

						if ( 'enabled' === $values['status'] ) {
							$mega_menu_count++;
							break 2;
						}
					}
				}
			}
		}
		return 0 < $mega_menu_count ? true : false;

	}

	/**
	 * Method used to scan site content (posts for now) for used FA icons.
	 */
	public function scan_for_icons() {

		// Search for icons in following formats: "fa-gem fas", "far fa-gem".
		// Or (missing icon subset): "fa-gem".
		$regex_pattern = '/(fa[srbl]?)?\s?(fa-.[^\s"\']*)\s?(fa[srbl]?)?/';
		$found_icons   = [];

		// Scan XML content (posts).
		$this->parse_xml();

		foreach ( $this->posts as $post ) {

			if ( 'publish' !== $post['data']['post_status'] && 'draft' !== $post['data']['post_status'] ) {
				continue;
			}

			// Get only stuff we actually use.
			$post_data = [
				'post_id'    => isset( $post['data']['post_id'] ) ? $post['data']['post_id'] : '',
				'post_title' => isset( $post['data']['post_title'] ) ? $post['data']['post_title'] : '',
				'post_type'  => isset( $post['data']['post_type'] ) ? $post['data']['post_type'] : '',
			];

			if ( isset( $post['data']['post_content'] ) && '' !== $post['data']['post_content'] ) {

				preg_match_all( $regex_pattern, $post['data']['post_content'], $matches );

				/**
				 * $matches[0] - full match
				 * $matches[1] - subset, if it was before the icon code
				 * $matches[2] - icon code
				 * $matches[3] - subset, if it was after the icon code
				 */

				if ( isset( $matches[0] ) && ! empty( $matches[0] ) ) {

					$this->update_icon_map_array( $matches );

					$found_icons[] = [
						'post_content' => [
							'icons' => [
								'matches'   => $matches[0],
								'icon_code' => $matches[2],
							],
						],
						'data'         => $post_data,
					];
				}
			}

			// Menu items have empty post_content.
			if ( 'nav_menu_item' === $post['data']['post_type'] && isset( $post['meta'] ) && is_array( $post['meta'] ) ) {

				foreach ( $post['meta'] as $meta ) {

					if ( '_menu_item_fusion_megamenu' === $meta['key'] ) {

						preg_match_all( $regex_pattern, $meta['value'], $matches );

						if ( isset( $matches[0] ) && ! empty( $matches[0] ) ) {

							$this->update_icon_map_array( $matches );

							$found_icons[] = [
								'post_meta' => [
									'meta_key' => $meta['key'], // phpcs:ignore WordPress.DB.SlowDBQuery
									'icons'    => [
										'matches'   => $matches[0],
										'icon_code' => $matches[1],
									],
								],
								'data'      => $post_data,
							];
						}
					}
				}
			}
		}

		// Scan widgets.
		$all_widgets = wp_get_sidebars_widgets();
		$widgets     = [];

		foreach ( $all_widgets as $widget_area => $widget_array ) {
			foreach ( $widget_array as $widget_key ) {
				$widget               = [];
				$widget['type']       = trim( substr( $widget_key, 0, strrpos( $widget_key, '-' ) ) );
				$widget['type-index'] = trim( substr( $widget_key, strrpos( $widget_key, '-' ) + 1 ) );
				$widget['value']      = get_option( 'widget_' . $widget['type'] );
				$widget['value']      = $widget['value'][ $widget['type-index'] ];
				$widgets[]            = $widget;

				preg_match_all( $regex_pattern, maybe_serialize( $widget['value'] ), $matches );

				if ( isset( $matches[0] ) && ! empty( $matches[0] ) ) {

					$this->update_icon_map_array( $matches );

					$found_icons[] = [
						'widget' => [
							'widget_type' => $widget['type'],
							'widget_area' => $widget_area,
							'icons'       => [
								'matches'   => $matches[0],
								'icon_code' => $matches[1],
							],
						],
						'data'   => $widget,
					];
				}
			}
		}

		return $found_icons;
	}

	/**
	 * Update icon map array.
	 *
	 * @access public
	 * @param array $matches The matches array.
	 * @return void
	 */
	protected function update_icon_map_array( $matches ) {

		$shims = $this->get_icon_shims();

		if ( isset( $matches[0] ) && is_array( $matches[0] ) ) {
			$count = count( $matches[1] );
			for ( $i = 0; $i < $count; $i++ ) {

				// Skip if we have already processed this icon (match).
				if ( isset( $this->icon_map[ $matches[0][ $i ] ] ) ) {
					continue;
				}

				$icon_code   = $matches[2][ $i ];
				$icon_subset = '';
				$is_fa4_icon = false;

				// Set icon subsets.
				if ( isset( $matches[1][ $i ] ) && ! empty( $matches[1][ $i ] ) ) {
					$icon_subset = $matches[1][ $i ];
				} elseif ( isset( $matches[3][ $i ] ) && ! empty( $matches[3][ $i ] ) ) {
					$icon_subset = $matches[3][ $i ];
				}

				// It might be, we need to check if icon name or subset was changed.
				if ( isset( $shims[ $icon_code ] ) && ( '' === $icon_subset || 'fa' === $icon_subset ) ) {

					// Icon name changed.
					if ( null !== $shims[ $icon_code ][2] && 'fa-' . $shims[ $icon_code ][2] !== $icon_code ) {
						$is_fa4_icon = true;
					}

					// Icon subset changed.
					if ( null !== $shims[ $icon_code ][1] && $icon_subset !== $shims[ $icon_code ][1] ) {
						$is_fa4_icon = true;
					}
				}

				// Set defaulf subset if it is still empty.
				if ( '' === $icon_subset ) {
					$icon_subset = 'fas';
				}

				// Finally update map array.
				if ( true === $is_fa4_icon ) {
					$this->icon_map[ $matches[0][ $i ] ] = [
						'is_fa4_icon' => $is_fa4_icon,
						'fa5_name'    => isset( $shims[ $icon_code ][2] ) ? $shims[ $icon_code ][2] : null,
						'fa5_subset'  => isset( $shims[ $icon_code ][1] ) ? $shims[ $icon_code ][1] : 'fas',
					];
				} else {
					$this->icon_map[ $matches[0][ $i ] ] = [
						'is_fa4_icon' => $is_fa4_icon,
						'fa5_name'    => substr( $icon_code, 3 ),
						'fa5_subset'  => $icon_subset,
					];
				}
			}
		}
	}

	/**
	 * Get site XML contents.
	 */
	public function get_content() {
		if ( ! function_exists( 'export_wp' ) ) {
			include ABSPATH . '/wp-admin/includes/export.php';
		}

		// Skip things we don't need.
		add_filter( 'wxr_export_skip_commentmeta', '__return_true' );
		add_filter( 'wxr_export_skip_termmeta', '__return_true' );

		ob_start();
		export_wp();

		// Prevent starting file download.
		header_remove( 'Content-Description' );
		header_remove( 'Content-Disposition' );
		header_remove( 'Content-Type' );

		return ob_get_clean();
	}

	/**
	 * Parsing site's XML content.
	 */
	public function parse_xml() {

		// Early exit if already parsed.
		if ( is_array( $this->posts ) && 0 < count( $this->posts ) ) {
			return;
		}

		$xml_string = $this->get_content();

		$reader = new XMLReader();
		$reader->xml( $xml_string );

		while ( $reader->read() ) {

			if ( XMLReader::ELEMENT !== $reader->nodeType ) {
				continue;
			}

			switch ( $reader->name ) {
				case 'item':
					$node = $reader->expand();

					$parsed = $this->parse_post_node( $node );
					if ( is_wp_error( $parsed ) ) {
						$this->log_error( $parsed );

						// Skip the rest of this post.
						$reader->next();
						break;
					}

					$this->posts[] = $parsed;

					// Handled everything in this node, move on to the next.
					$reader->next();
					break;
			}
		}

	}

	/**
	 * Parse a post node into post data.
	 *
	 * @param DOMElement $node Parent node of post data (typically `item`).
	 * @return array|WP_Error Post data array on success, error otherwise.
	 */
	protected function parse_post_node( $node ) {
		$data     = [];
		$meta     = [];
		$comments = [];
		$terms    = [];

		foreach ( $node->childNodes as $child ) {
			// We only care about child elements.
			if ( XML_ELEMENT_NODE !== $child->nodeType ) {
				continue;
			}

			switch ( $child->tagName ) {
				case 'wp:post_type':
					$data['post_type'] = $child->textContent;
					break;

				case 'title':
					$data['post_title'] = $child->textContent;
					break;

				case 'guid':
					$data['guid'] = $child->textContent;
					break;

				case 'dc:creator':
					$data['post_author'] = $child->textContent;
					break;

				case 'content:encoded':
					$data['post_content'] = $child->textContent;
					break;

				case 'excerpt:encoded':
					$data['post_excerpt'] = $child->textContent;
					break;

				case 'wp:post_id':
					$data['post_id'] = $child->textContent;
					break;

				case 'wp:post_date':
					$data['post_date'] = $child->textContent;
					break;

				case 'wp:post_date_gmt':
					$data['post_date_gmt'] = $child->textContent;
					break;

				case 'wp:comment_status':
					$data['comment_status'] = $child->textContent;
					break;

				case 'wp:ping_status':
					$data['ping_status'] = $child->textContent;
					break;

				case 'wp:post_name':
					$data['post_name'] = $child->textContent;
					break;

				case 'wp:status':
					$data['post_status'] = $child->textContent;

					if ( 'auto-draft' === $data['post_status'] ) {
						// Bail now.
						return new WP_Error(
							'wxr_importer.post.cannot_import_draft',
							__( 'Cannot import auto-draft posts', 'Avada' ),
							$data
						);
					}
					break;

				case 'wp:post_parent':
					$data['post_parent'] = $child->textContent;
					break;

				case 'wp:menu_order':
					$data['menu_order'] = $child->textContent;
					break;

				case 'wp:post_password':
					$data['post_password'] = $child->textContent;
					break;

				case 'wp:is_sticky':
					$data['is_sticky'] = $child->textContent;
					break;

				case 'wp:attachment_url':
					$data['attachment_url'] = $child->textContent;
					break;

				case 'wp:postmeta':
					$meta_item = $this->parse_meta_node( $child );
					if ( ! empty( $meta_item ) ) {
						$meta[] = $meta_item;
					}
					break;
			}
		}

		return compact( 'data', 'meta' );
	}

	/**
	 * Parse a meta node into meta data.
	 *
	 * @param DOMElement $node Parent node of meta data (typically `wp:postmeta` or `wp:commentmeta`).
	 * @return array|null Meta data array on success, or null on error.
	 */
	protected function parse_meta_node( $node ) {

		foreach ( $node->childNodes as $child ) {
			// We only care about child elements.
			if ( XML_ELEMENT_NODE !== $child->nodeType ) {
				continue;
			}

			switch ( $child->tagName ) {
				case 'wp:meta_key':
					$key = $child->textContent;
					break;

				case 'wp:meta_value':
					$value = $child->textContent;
					break;
			}
		}

		if ( empty( $key ) || empty( $value ) ) {
			return null;
		}

		return compact( 'key', 'value' );
	}

	/**
	 * Render header specifically for wizard.
	 *
	 * @param string $screen_classes Classes for page.
	 * @return void
	 */
	public function render_header( $screen_classes ) {
		?>
		<div class="<?php echo esc_html( $screen_classes ); ?>">
			<header class="avada-db-header-main">
				<div class="avada-db-header-main-container">
					<a class="avada-db-logo" href="<?php echo esc_url( admin_url( 'admin.php?page=avada' ) ); ?>" aria-label="<?php esc_attr_e( 'Link to Avada dashboard', 'Avada' ); ?>">
						<i class="avada-db-logo-icon fusiona-avada-logo"></i>
						<h1><?php esc_html_e( 'Performance Wizard', 'Avada' ); ?></h1>
					</a>
					<div class="wizard-hero-header">
						<a class="button button-primary" target="_blank" href="https://developers.google.com/speed/pagespeed/insights/?url=<?php echo rawurlencode( trailingslashit( get_home_url() ) ); ?>"><?php esc_html_e( 'Run PageSpeed Insights', 'Avada' ); ?></a>
					</div>
				</div>
			</header>
			<header class="avada-db-header-sticky avada-db-card awb-wizard-steps">
				<ol>
					<li class="awb-wizard-link active" data-id="1"><span class="awb-wizard-link-text"><?php esc_html_e( 'Start', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="2"><span class="awb-wizard-link-text"><?php esc_html_e( 'Features', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="3"><span class="awb-wizard-link-text"><?php esc_html_e( 'Icons', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="4"><span class="awb-wizard-link-text"><?php esc_html_e( 'Fonts', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="5"><span class="awb-wizard-link-text"><?php esc_html_e( 'Elements', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="6"><span class="awb-wizard-link-text"><?php esc_html_e( 'Optimization', 'Avada' ); ?></span></li>
					<li class="awb-wizard-link" data-id="7"><span class="awb-wizard-link-text"><?php esc_html_e( 'Finish', 'Avada' ); ?></span></li>
				</ol>
			</header>

			<div class="avada-db-demos-notices"><h1></h1> <?php do_action( 'avada_dashboard_notices' ); ?></div>
		<?php
	}

	/**
	 * Get FA 4 icon shims.
	 *
	 * @return array
	 */
	public function get_icon_shims() {

		return [
			'fa-glass'                => [ 'glass', null, 'glass-martini' ],
			'fa-meetup'               => [ 'meetup', 'fab', null ],
			'fa-star-o'               => [ 'star-o', 'far', 'star' ],
			'fa-remove'               => [ 'remove', null, 'times' ],
			'fa-close'                => [ 'close', null, 'times' ],
			'fa-gear'                 => [ 'gear', null, 'cog' ],
			'fa-trash-o'              => [ 'trash-o', 'far', 'trash-alt' ],
			'fa-file-o'               => [ 'file-o', 'far', 'file' ],
			'fa-clock-o'              => [ 'clock-o', 'far', 'clock' ],
			'fa-arrow-circle-o-down'  => [ 'arrow-circle-o-down', 'far', 'arrow-alt-circle-down' ],
			'fa-arrow-circle-o-up'    => [ 'arrow-circle-o-up', 'far', 'arrow-alt-circle-up' ],
			'fa-play-circle-o'        => [ 'play-circle-o', 'far', 'play-circle' ],
			'fa-repeat'               => [ 'repeat', null, 'redo' ],
			'fa-rotate-right'         => [ 'rotate-right', null, 'redo' ],
			'fa-refresh'              => [ 'refresh', null, 'sync' ],
			'fa-list-alt'             => [ 'list-alt', 'far', null ],
			'fa-dedent'               => [ 'dedent', null, 'outdent' ],
			'fa-video-camera'         => [ 'video-camera', null, 'video' ],
			'fa-picture-o'            => [ 'picture-o', 'far', 'image' ],
			'fa-photo'                => [ 'photo', 'far', 'image' ],
			'fa-image'                => [ 'image', 'far', 'image' ],
			'fa-pencil'               => [ 'pencil', null, 'pencil-alt' ],
			'fa-map-marker'           => [ 'map-marker', null, 'map-marker-alt' ],
			'fa-pencil-square-o'      => [ 'pencil-square-o', 'far', 'edit' ],
			'fa-share-square-o'       => [ 'share-square-o', 'far', 'share-square' ],
			'fa-check-square-o'       => [ 'check-square-o', 'far', 'check-square' ],
			'fa-arrows'               => [ 'arrows', null, 'arrows-alt' ],
			'fa-times-circle-o'       => [ 'times-circle-o', 'far', 'times-circle' ],
			'fa-check-circle-o'       => [ 'check-circle-o', 'far', 'check-circle' ],
			'fa-mail-forward'         => [ 'mail-forward', null, 'share' ],
			'fa-expand'               => [ 'expand', null, 'expand-alt' ],
			'fa-compress'             => [ 'compress', null, 'compress-alt' ],
			'fa-eye'                  => [ 'eye', 'far', null ],
			'fa-eye-slash'            => [ 'eye-slash', 'far', null ],
			'fa-warning'              => [ 'warning', null, 'exclamation-triangle' ],
			'fa-calendar'             => [ 'calendar', null, 'calendar-alt' ],
			'fa-arrows-v'             => [ 'arrows-v', null, 'arrows-alt-v' ],
			'fa-arrows-h'             => [ 'arrows-h', null, 'arrows-alt-h' ],
			'fa-bar-chart'            => [ 'bar-chart', 'far', 'chart-bar' ],
			'fa-bar-chart-o'          => [ 'bar-chart-o', 'far', 'chart-bar' ],
			'fa-twitter-square'       => [ 'twitter-square', 'fab', null ],
			'fa-facebook-square'      => [ 'facebook-square', 'fab', null ],
			'fa-gears'                => [ 'gears', null, 'cogs' ],
			'fa-thumbs-o-up'          => [ 'thumbs-o-up', 'far', 'thumbs-up' ],
			'fa-thumbs-o-down'        => [ 'thumbs-o-down', 'far', 'thumbs-down' ],
			'fa-heart-o'              => [ 'heart-o', 'far', 'heart' ],
			'fa-sign-out'             => [ 'sign-out', null, 'sign-out-alt' ],
			'fa-linkedin-square'      => [ 'linkedin-square', 'fab', 'linkedin' ],
			'fa-thumb-tack'           => [ 'thumb-tack', null, 'thumbtack' ],
			'fa-external-link'        => [ 'external-link', null, 'external-link-alt' ],
			'fa-sign-in'              => [ 'sign-in', null, 'sign-in-alt' ],
			'fa-github-square'        => [ 'github-square', 'fab', null ],
			'fa-lemon-o'              => [ 'lemon-o', 'far', 'lemon' ],
			'fa-square-o'             => [ 'square-o', 'far', 'square' ],
			'fa-bookmark-o'           => [ 'bookmark-o', 'far', 'bookmark' ],
			'fa-twitter'              => [ 'twitter', 'fab', null ],
			'fa-facebook'             => [ 'facebook', 'fab', 'facebook-f' ],
			'fa-facebook-f'           => [ 'facebook-f', 'fab', 'facebook-f' ],
			'fa-github'               => [ 'github', 'fab', null ],
			'fa-credit-card'          => [ 'credit-card', 'far', null ],
			'fa-feed'                 => [ 'feed', null, 'rss' ],
			'fa-hdd-o'                => [ 'hdd-o', 'far', 'hdd' ],
			'fa-hand-o-right'         => [ 'hand-o-right', 'far', 'hand-point-right' ],
			'fa-hand-o-left'          => [ 'hand-o-left', 'far', 'hand-point-left' ],
			'fa-hand-o-up'            => [ 'hand-o-up', 'far', 'hand-point-up' ],
			'fa-hand-o-down'          => [ 'hand-o-down', 'far', 'hand-point-down' ],
			'fa-arrows-alt'           => [ 'arrows-alt', null, 'expand-arrows-alt' ],
			'fa-group'                => [ 'group', null, 'users' ],
			'fa-chain'                => [ 'chain', null, 'link' ],
			'fa-scissors'             => [ 'scissors', null, 'cut' ],
			'fa-files-o'              => [ 'files-o', 'far', 'copy' ],
			'fa-floppy-o'             => [ 'floppy-o', 'far', 'save' ],
			'fa-navicon'              => [ 'navicon', null, 'bars' ],
			'fa-reorder'              => [ 'reorder', null, 'bars' ],
			'fa-pinterest'            => [ 'pinterest', 'fab', null ],
			'fa-pinterest-square'     => [ 'pinterest-square', 'fab', null ],
			'fa-google-plus-square'   => [ 'google-plus-square', 'fab', null ],
			'fa-google-plus'          => [ 'google-plus', 'fab', 'google-plus-g' ],
			'fa-money'                => [ 'money', 'far', 'money-bill-alt' ],
			'fa-unsorted'             => [ 'unsorted', null, 'sort' ],
			'fa-sort-desc'            => [ 'sort-desc', null, 'sort-down' ],
			'fa-sort-asc'             => [ 'sort-asc', null, 'sort-up' ],
			'fa-linkedin'             => [ 'linkedin', 'fab', 'linkedin-in' ],
			'fa-rotate-left'          => [ 'rotate-left', null, 'undo' ],
			'fa-legal'                => [ 'legal', null, 'gavel' ],
			'fa-tachometer'           => [ 'tachometer', null, 'tachometer-alt' ],
			'fa-dashboard'            => [ 'dashboard', null, 'tachometer-alt' ],
			'fa-comment-o'            => [ 'comment-o', 'far', 'comment' ],
			'fa-comments-o'           => [ 'comments-o', 'far', 'comments' ],
			'fa-flash'                => [ 'flash', null, 'bolt' ],
			'fa-clipboard'            => [ 'clipboard', 'far', null ],
			'fa-paste'                => [ 'paste', 'far', 'clipboard' ],
			'fa-lightbulb-o'          => [ 'lightbulb-o', 'far', 'lightbulb' ],
			'fa-exchange'             => [ 'exchange', null, 'exchange-alt' ],
			'fa-cloud-download'       => [ 'cloud-download', null, 'cloud-download-alt' ],
			'fa-cloud-upload'         => [ 'cloud-upload', null, 'cloud-upload-alt' ],
			'fa-bell-o'               => [ 'bell-o', 'far', 'bell' ],
			'fa-cutlery'              => [ 'cutlery', null, 'utensils' ],
			'fa-file-text-o'          => [ 'file-text-o', 'far', 'file-alt' ],
			'fa-building-o'           => [ 'building-o', 'far', 'building' ],
			'fa-hospital-o'           => [ 'hospital-o', 'far', 'hospital' ],
			'fa-tablet'               => [ 'tablet', null, 'tablet-alt' ],
			'fa-mobile'               => [ 'mobile', null, 'mobile-alt' ],
			'fa-mobile-phone'         => [ 'mobile-phone', null, 'mobile-alt' ],
			'fa-circle-o'             => [ 'circle-o', 'far', 'circle' ],
			'fa-mail-reply'           => [ 'mail-reply', null, 'reply' ],
			'fa-github-alt'           => [ 'github-alt', 'fab', null ],
			'fa-folder-o'             => [ 'folder-o', 'far', 'folder' ],
			'fa-folder-open-o'        => [ 'folder-open-o', 'far', 'folder-open' ],
			'fa-smile-o'              => [ 'smile-o', 'far', 'smile' ],
			'fa-frown-o'              => [ 'frown-o', 'far', 'frown' ],
			'fa-meh-o'                => [ 'meh-o', 'far', 'meh' ],
			'fa-keyboard-o'           => [ 'keyboard-o', 'far', 'keyboard' ],
			'fa-flag-o'               => [ 'flag-o', 'far', 'flag' ],
			'fa-mail-reply-all'       => [ 'mail-reply-all', null, 'reply-all' ],
			'fa-star-half-o'          => [ 'star-half-o', 'far', 'star-half' ],
			'fa-star-half-empty'      => [ 'star-half-empty', 'far', 'star-half' ],
			'fa-star-half-full'       => [ 'star-half-full', 'far', 'star-half' ],
			'fa-code-fork'            => [ 'code-fork', null, 'code-branch' ],
			'fa-chain-broken'         => [ 'chain-broken', null, 'unlink' ],
			'fa-shield'               => [ 'shield', null, 'shield-alt' ],
			'fa-calendar-o'           => [ 'calendar-o', 'far', 'calendar' ],
			'fa-maxcdn'               => [ 'maxcdn', 'fab', null ],
			'fa-html5'                => [ 'html5', 'fab', null ],
			'fa-css3'                 => [ 'css3', 'fab', null ],
			'fa-ticket'               => [ 'ticket', null, 'ticket-alt' ],
			'fa-minus-square-o'       => [ 'minus-square-o', 'far', 'minus-square' ],
			'fa-level-up'             => [ 'level-up', null, 'level-up-alt' ],
			'fa-level-down'           => [ 'level-down', null, 'level-down-alt' ],
			'fa-pencil-square'        => [ 'pencil-square', null, 'pen-square' ],
			'fa-external-link-square' => [ 'external-link-square', null, 'external-link-square-alt' ],
			'fa-compass'              => [ 'compass', 'far', null ],
			'fa-caret-square-o-down'  => [ 'caret-square-o-down', 'far', 'caret-square-down' ],
			'fa-toggle-down'          => [ 'toggle-down', 'far', 'caret-square-down' ],
			'fa-caret-square-o-up'    => [ 'caret-square-o-up', 'far', 'caret-square-up' ],
			'fa-toggle-up'            => [ 'toggle-up', 'far', 'caret-square-up' ],
			'fa-caret-square-o-right' => [ 'caret-square-o-right', 'far', 'caret-square-right' ],
			'fa-toggle-right'         => [ 'toggle-right', 'far', 'caret-square-right' ],
			'fa-eur'                  => [ 'eur', null, 'euro-sign' ],
			'fa-euro'                 => [ 'euro', null, 'euro-sign' ],
			'fa-gbp'                  => [ 'gbp', null, 'pound-sign' ],
			'fa-usd'                  => [ 'usd', null, 'dollar-sign' ],
			'fa-dollar'               => [ 'dollar', null, 'dollar-sign' ],
			'fa-inr'                  => [ 'inr', null, 'rupee-sign' ],
			'fa-rupee'                => [ 'rupee', null, 'rupee-sign' ],
			'fa-jpy'                  => [ 'jpy', null, 'yen-sign' ],
			'fa-cny'                  => [ 'cny', null, 'yen-sign' ],
			'fa-rmb'                  => [ 'rmb', null, 'yen-sign' ],
			'fa-yen'                  => [ 'yen', null, 'yen-sign' ],
			'fa-rub'                  => [ 'rub', null, 'ruble-sign' ],
			'fa-ruble'                => [ 'ruble', null, 'ruble-sign' ],
			'fa-rouble'               => [ 'rouble', null, 'ruble-sign' ],
			'fa-krw'                  => [ 'krw', null, 'won-sign' ],
			'fa-won'                  => [ 'won', null, 'won-sign' ],
			'fa-btc'                  => [ 'btc', 'fab', null ],
			'fa-bitcoin'              => [ 'bitcoin', 'fab', 'btc' ],
			'fa-file-text'            => [ 'file-text', null, 'file-alt' ],
			'fa-sort-alpha-asc'       => [ 'sort-alpha-asc', null, 'sort-alpha-down' ],
			'fa-sort-alpha-desc'      => [ 'sort-alpha-desc', null, 'sort-alpha-down-alt' ],
			'fa-sort-amount-asc'      => [ 'sort-amount-asc', null, 'sort-amount-down' ],
			'fa-sort-amount-desc'     => [ 'sort-amount-desc', null, 'sort-amount-down-alt' ],
			'fa-sort-numeric-asc'     => [ 'sort-numeric-asc', null, 'sort-numeric-down' ],
			'fa-sort-numeric-desc'    => [ 'sort-numeric-desc', null, 'sort-numeric-down-alt' ],
			'fa-youtube-square'       => [ 'youtube-square', 'fab', null ],
			'fa-youtube'              => [ 'youtube', 'fab', null ],
			'fa-xing'                 => [ 'xing', 'fab', null ],
			'fa-xing-square'          => [ 'xing-square', 'fab', null ],
			'fa-youtube-play'         => [ 'youtube-play', 'fab', 'youtube' ],
			'fa-dropbox'              => [ 'dropbox', 'fab', null ],
			'fa-stack-overflow'       => [ 'stack-overflow', 'fab', null ],
			'fa-instagram'            => [ 'instagram', 'fab', null ],
			'fa-flickr'               => [ 'flickr', 'fab', null ],
			'fa-adn'                  => [ 'adn', 'fab', null ],
			'fa-bitbucket'            => [ 'bitbucket', 'fab', null ],
			'fa-bitbucket-square'     => [ 'bitbucket-square', 'fab', 'bitbucket' ],
			'fa-tumblr'               => [ 'tumblr', 'fab', null ],
			'fa-tumblr-square'        => [ 'tumblr-square', 'fab', null ],
			'fa-long-arrow-down'      => [ 'long-arrow-down', null, 'long-arrow-alt-down' ],
			'fa-long-arrow-up'        => [ 'long-arrow-up', null, 'long-arrow-alt-up' ],
			'fa-long-arrow-left'      => [ 'long-arrow-left', null, 'long-arrow-alt-left' ],
			'fa-long-arrow-right'     => [ 'long-arrow-right', null, 'long-arrow-alt-right' ],
			'fa-apple'                => [ 'apple', 'fab', null ],
			'fa-windows'              => [ 'windows', 'fab', null ],
			'fa-android'              => [ 'android', 'fab', null ],
			'fa-linux'                => [ 'linux', 'fab', null ],
			'fa-dribbble'             => [ 'dribbble', 'fab', null ],
			'fa-skype'                => [ 'skype', 'fab', null ],
			'fa-foursquare'           => [ 'foursquare', 'fab', null ],
			'fa-trello'               => [ 'trello', 'fab', null ],
			'fa-gratipay'             => [ 'gratipay', 'fab', null ],
			'fa-gittip'               => [ 'gittip', 'fab', 'gratipay' ],
			'fa-sun-o'                => [ 'sun-o', 'far', 'sun' ],
			'fa-moon-o'               => [ 'moon-o', 'far', 'moon' ],
			'fa-vk'                   => [ 'vk', 'fab', null ],
			'fa-weibo'                => [ 'weibo', 'fab', null ],
			'fa-renren'               => [ 'renren', 'fab', null ],
			'fa-pagelines'            => [ 'pagelines', 'fab', null ],
			'fa-stack-exchange'       => [ 'stack-exchange', 'fab', null ],
			'fa-arrow-circle-o-right' => [ 'arrow-circle-o-right', 'far', 'arrow-alt-circle-right' ],
			'fa-arrow-circle-o-left'  => [ 'arrow-circle-o-left', 'far', 'arrow-alt-circle-left' ],
			'fa-caret-square-o-left'  => [ 'caret-square-o-left', 'far', 'caret-square-left' ],
			'fa-toggle-left'          => [ 'toggle-left', 'far', 'caret-square-left' ],
			'fa-dot-circle-o'         => [ 'dot-circle-o', 'far', 'dot-circle' ],
			'fa-vimeo-square'         => [ 'vimeo-square', 'fab', null ],
			'fa-try'                  => [ 'try', null, 'lira-sign' ],
			'fa-turkish-lira'         => [ 'turkish-lira', null, 'lira-sign' ],
			'fa-plus-square-o'        => [ 'plus-square-o', 'far', 'plus-square' ],
			'fa-slack'                => [ 'slack', 'fab', null ],
			'fa-wordpress'            => [ 'wordpress', 'fab', null ],
			'fa-openid'               => [ 'openid', 'fab', null ],
			'fa-institution'          => [ 'institution', null, 'university' ],
			'fa-bank'                 => [ 'bank', null, 'university' ],
			'fa-mortar-board'         => [ 'mortar-board', null, 'graduation-cap' ],
			'fa-yahoo'                => [ 'yahoo', 'fab', null ],
			'fa-google'               => [ 'google', 'fab', null ],
			'fa-reddit'               => [ 'reddit', 'fab', null ],
			'fa-reddit-square'        => [ 'reddit-square', 'fab', null ],
			'fa-stumbleupon-circle'   => [ 'stumbleupon-circle', 'fab', null ],
			'fa-stumbleupon'          => [ 'stumbleupon', 'fab', null ],
			'fa-delicious'            => [ 'delicious', 'fab', null ],
			'fa-digg'                 => [ 'digg', 'fab', null ],
			'fa-pied-piper-pp'        => [ 'pied-piper-pp', 'fab', null ],
			'fa-pied-piper-alt'       => [ 'pied-piper-alt', 'fab', null ],
			'fa-drupal'               => [ 'drupal', 'fab', null ],
			'fa-joomla'               => [ 'joomla', 'fab', null ],
			'fa-spoon'                => [ 'spoon', null, 'utensil-spoon' ],
			'fa-behance'              => [ 'behance', 'fab', null ],
			'fa-behance-square'       => [ 'behance-square', 'fab', null ],
			'fa-steam'                => [ 'steam', 'fab', null ],
			'fa-steam-square'         => [ 'steam-square', 'fab', null ],
			'fa-automobile'           => [ 'automobile', null, 'car' ],
			'fa-envelope-o'           => [ 'envelope-o', 'far', 'envelope' ],
			'fa-spotify'              => [ 'spotify', 'fab', null ],
			'fa-deviantart'           => [ 'deviantart', 'fab', null ],
			'fa-soundcloud'           => [ 'soundcloud', 'fab', null ],
			'fa-file-pdf-o'           => [ 'file-pdf-o', 'far', 'file-pdf' ],
			'fa-file-word-o'          => [ 'file-word-o', 'far', 'file-word' ],
			'fa-file-excel-o'         => [ 'file-excel-o', 'far', 'file-excel' ],
			'fa-file-powerpoint-o'    => [ 'file-powerpoint-o', 'far', 'file-powerpoint' ],
			'fa-file-image-o'         => [ 'file-image-o', 'far', 'file-image' ],
			'fa-file-photo-o'         => [ 'file-photo-o', 'far', 'file-image' ],
			'fa-file-picture-o'       => [ 'file-picture-o', 'far', 'file-image' ],
			'fa-file-archive-o'       => [ 'file-archive-o', 'far', 'file-archive' ],
			'fa-file-zip-o'           => [ 'file-zip-o', 'far', 'file-archive' ],
			'fa-file-audio-o'         => [ 'file-audio-o', 'far', 'file-audio' ],
			'fa-file-sound-o'         => [ 'file-sound-o', 'far', 'file-audio' ],
			'fa-file-video-o'         => [ 'file-video-o', 'far', 'file-video' ],
			'fa-file-movie-o'         => [ 'file-movie-o', 'far', 'file-video' ],
			'fa-file-code-o'          => [ 'file-code-o', 'far', 'file-code' ],
			'fa-vine'                 => [ 'vine', 'fab', null ],
			'fa-codepen'              => [ 'codepen', 'fab', null ],
			'fa-jsfiddle'             => [ 'jsfiddle', 'fab', null ],
			'fa-life-ring'            => [ 'life-ring', 'far', null ],
			'fa-life-bouy'            => [ 'life-bouy', 'far', 'life-ring' ],
			'fa-life-buoy'            => [ 'life-buoy', 'far', 'life-ring' ],
			'fa-life-saver'           => [ 'life-saver', 'far', 'life-ring' ],
			'fa-support'              => [ 'support', 'far', 'life-ring' ],
			'fa-circle-o-notch'       => [ 'circle-o-notch', null, 'circle-notch' ],
			'fa-rebel'                => [ 'rebel', 'fab', null ],
			'fa-ra'                   => [ 'ra', 'fab', 'rebel' ],
			'fa-resistance'           => [ 'resistance', 'fab', 'rebel' ],
			'fa-empire'               => [ 'empire', 'fab', null ],
			'fa-ge'                   => [ 'ge', 'fab', 'empire' ],
			'fa-git-square'           => [ 'git-square', 'fab', null ],
			'fa-git'                  => [ 'git', 'fab', null ],
			'fa-hacker-news'          => [ 'hacker-news', 'fab', null ],
			'fa-y-combinator-square'  => [ 'y-combinator-square', 'fab', 'hacker-news' ],
			'fa-yc-square'            => [ 'yc-square', 'fab', 'hacker-news' ],
			'fa-tencent-weibo'        => [ 'tencent-weibo', 'fab', null ],
			'fa-qq'                   => [ 'qq', 'fab', null ],
			'fa-weixin'               => [ 'weixin', 'fab', null ],
			'fa-wechat'               => [ 'wechat', 'fab', 'weixin' ],
			'fa-send'                 => [ 'send', null, 'paper-plane' ],
			'fa-paper-plane-o'        => [ 'paper-plane-o', 'far', 'paper-plane' ],
			'fa-send-o'               => [ 'send-o', 'far', 'paper-plane' ],
			'fa-circle-thin'          => [ 'circle-thin', 'far', 'circle' ],
			'fa-header'               => [ 'header', null, 'heading' ],
			'fa-sliders'              => [ 'sliders', null, 'sliders-h' ],
			'fa-futbol-o'             => [ 'futbol-o', 'far', 'futbol' ],
			'fa-soccer-ball-o'        => [ 'soccer-ball-o', 'far', 'futbol' ],
			'fa-slideshare'           => [ 'slideshare', 'fab', null ],
			'fa-twitch'               => [ 'twitch', 'fab', null ],
			'fa-yelp'                 => [ 'yelp', 'fab', null ],
			'fa-newspaper-o'          => [ 'newspaper-o', 'far', 'newspaper' ],
			'fa-paypal'               => [ 'paypal', 'fab', null ],
			'fa-google-wallet'        => [ 'google-wallet', 'fab', null ],
			'fa-cc-visa'              => [ 'cc-visa', 'fab', null ],
			'fa-cc-mastercard'        => [ 'cc-mastercard', 'fab', null ],
			'fa-cc-discover'          => [ 'cc-discover', 'fab', null ],
			'fa-cc-amex'              => [ 'cc-amex', 'fab', null ],
			'fa-cc-paypal'            => [ 'cc-paypal', 'fab', null ],
			'fa-cc-stripe'            => [ 'cc-stripe', 'fab', null ],
			'fa-bell-slash-o'         => [ 'bell-slash-o', 'far', 'bell-slash' ],
			'fa-trash'                => [ 'trash', null, 'trash-alt' ],
			'fa-copyright'            => [ 'copyright', 'far', null ],
			'fa-eyedropper'           => [ 'eyedropper', null, 'eye-dropper' ],
			'fa-area-chart'           => [ 'area-chart', null, 'chart-area' ],
			'fa-pie-chart'            => [ 'pie-chart', null, 'chart-pie' ],
			'fa-line-chart'           => [ 'line-chart', null, 'chart-line' ],
			'fa-lastfm'               => [ 'lastfm', 'fab', null ],
			'fa-lastfm-square'        => [ 'lastfm-square', 'fab', null ],
			'fa-ioxhost'              => [ 'ioxhost', 'fab', null ],
			'fa-angellist'            => [ 'angellist', 'fab', null ],
			'fa-cc'                   => [ 'cc', 'far', 'closed-captioning' ],
			'fa-ils'                  => [ 'ils', null, 'shekel-sign' ],
			'fa-shekel'               => [ 'shekel', null, 'shekel-sign' ],
			'fa-sheqel'               => [ 'sheqel', null, 'shekel-sign' ],
			'fa-meanpath'             => [ 'meanpath', 'fab', 'font-awesome' ],
			'fa-buysellads'           => [ 'buysellads', 'fab', null ],
			'fa-connectdevelop'       => [ 'connectdevelop', 'fab', null ],
			'fa-dashcube'             => [ 'dashcube', 'fab', null ],
			'fa-forumbee'             => [ 'forumbee', 'fab', null ],
			'fa-leanpub'              => [ 'leanpub', 'fab', null ],
			'fa-sellsy'               => [ 'sellsy', 'fab', null ],
			'fa-shirtsinbulk'         => [ 'shirtsinbulk', 'fab', null ],
			'fa-simplybuilt'          => [ 'simplybuilt', 'fab', null ],
			'fa-skyatlas'             => [ 'skyatlas', 'fab', null ],
			'fa-diamond'              => [ 'diamond', 'far', 'gem' ],
			'fa-intersex'             => [ 'intersex', null, 'transgender' ],
			'fa-facebook-official'    => [ 'facebook-official', 'fab', 'facebook' ],
			'fa-pinterest-p'          => [ 'pinterest-p', 'fab', null ],
			'fa-whatsapp'             => [ 'whatsapp', 'fab', null ],
			'fa-hotel'                => [ 'hotel', null, 'bed' ],
			'fa-viacoin'              => [ 'viacoin', 'fab', null ],
			'fa-medium'               => [ 'medium', 'fab', null ],
			'fa-y-combinator'         => [ 'y-combinator', 'fab', null ],
			'fa-yc'                   => [ 'yc', 'fab', 'y-combinator' ],
			'fa-optin-monster'        => [ 'optin-monster', 'fab', null ],
			'fa-opencart'             => [ 'opencart', 'fab', null ],
			'fa-expeditedssl'         => [ 'expeditedssl', 'fab', null ],
			'fa-battery-4'            => [ 'battery-4', null, 'battery-full' ],
			'fa-battery'              => [ 'battery', null, 'battery-full' ],
			'fa-battery-3'            => [ 'battery-3', null, 'battery-three-quarters' ],
			'fa-battery-2'            => [ 'battery-2', null, 'battery-half' ],
			'fa-battery-1'            => [ 'battery-1', null, 'battery-quarter' ],
			'fa-battery-0'            => [ 'battery-0', null, 'battery-empty' ],
			'fa-object-group'         => [ 'object-group', 'far', null ],
			'fa-object-ungroup'       => [ 'object-ungroup', 'far', null ],
			'fa-sticky-note-o'        => [ 'sticky-note-o', 'far', 'sticky-note' ],
			'fa-cc-jcb'               => [ 'cc-jcb', 'fab', null ],
			'fa-cc-diners-club'       => [ 'cc-diners-club', 'fab', null ],
			'fa-clone'                => [ 'clone', 'far', null ],
			'fa-hourglass-o'          => [ 'hourglass-o', 'far', 'hourglass' ],
			'fa-hourglass-1'          => [ 'hourglass-1', null, 'hourglass-start' ],
			'fa-hourglass-2'          => [ 'hourglass-2', null, 'hourglass-half' ],
			'fa-hourglass-3'          => [ 'hourglass-3', null, 'hourglass-end' ],
			'fa-hand-rock-o'          => [ 'hand-rock-o', 'far', 'hand-rock' ],
			'fa-hand-grab-o'          => [ 'hand-grab-o', 'far', 'hand-rock' ],
			'fa-hand-paper-o'         => [ 'hand-paper-o', 'far', 'hand-paper' ],
			'fa-hand-stop-o'          => [ 'hand-stop-o', 'far', 'hand-paper' ],
			'fa-hand-scissors-o'      => [ 'hand-scissors-o', 'far', 'hand-scissors' ],
			'fa-hand-lizard-o'        => [ 'hand-lizard-o', 'far', 'hand-lizard' ],
			'fa-hand-spock-o'         => [ 'hand-spock-o', 'far', 'hand-spock' ],
			'fa-hand-pointer-o'       => [ 'hand-pointer-o', 'far', 'hand-pointer' ],
			'fa-hand-peace-o'         => [ 'hand-peace-o', 'far', 'hand-peace' ],
			'fa-registered'           => [ 'registered', 'far', null ],
			'fa-creative-commons'     => [ 'creative-commons', 'fab', null ],
			'fa-gg'                   => [ 'gg', 'fab', null ],
			'fa-gg-circle'            => [ 'gg-circle', 'fab', null ],
			'fa-tripadvisor'          => [ 'tripadvisor', 'fab', null ],
			'fa-odnoklassniki'        => [ 'odnoklassniki', 'fab', null ],
			'fa-odnoklassniki-square' => [ 'odnoklassniki-square', 'fab', null ],
			'fa-get-pocket'           => [ 'get-pocket', 'fab', null ],
			'fa-wikipedia-w'          => [ 'wikipedia-w', 'fab', null ],
			'fa-safari'               => [ 'safari', 'fab', null ],
			'fa-chrome'               => [ 'chrome', 'fab', null ],
			'fa-firefox'              => [ 'firefox', 'fab', null ],
			'fa-opera'                => [ 'opera', 'fab', null ],
			'fa-internet-explorer'    => [ 'internet-explorer', 'fab', null ],
			'fa-television'           => [ 'television', null, 'tv' ],
			'fa-contao'               => [ 'contao', 'fab', null ],
			'fa-500px'                => [ '500px', 'fab', null ],
			'fa-amazon'               => [ 'amazon', 'fab', null ],
			'fa-calendar-plus-o'      => [ 'calendar-plus-o', 'far', 'calendar-plus' ],
			'fa-calendar-minus-o'     => [ 'calendar-minus-o', 'far', 'calendar-minus' ],
			'fa-calendar-times-o'     => [ 'calendar-times-o', 'far', 'calendar-times' ],
			'fa-calendar-check-o'     => [ 'calendar-check-o', 'far', 'calendar-check' ],
			'fa-map-o'                => [ 'map-o', 'far', 'map' ],
			'fa-commenting'           => [ 'commenting', null, 'comment-dots' ],
			'fa-commenting-o'         => [ 'commenting-o', 'far', 'comment-dots' ],
			'fa-houzz'                => [ 'houzz', 'fab', null ],
			'fa-vimeo'                => [ 'vimeo', 'fab', 'vimeo-v' ],
			'fa-black-tie'            => [ 'black-tie', 'fab', null ],
			'fa-fonticons'            => [ 'fonticons', 'fab', null ],
			'fa-reddit-alien'         => [ 'reddit-alien', 'fab', null ],
			'fa-edge'                 => [ 'edge', 'fab', null ],
			'fa-credit-card-alt'      => [ 'credit-card-alt', null, 'credit-card' ],
			'fa-codiepie'             => [ 'codiepie', 'fab', null ],
			'fa-modx'                 => [ 'modx', 'fab', null ],
			'fa-fort-awesome'         => [ 'fort-awesome', 'fab', null ],
			'fa-usb'                  => [ 'usb', 'fab', null ],
			'fa-product-hunt'         => [ 'product-hunt', 'fab', null ],
			'fa-mixcloud'             => [ 'mixcloud', 'fab', null ],
			'fa-scribd'               => [ 'scribd', 'fab', null ],
			'fa-pause-circle-o'       => [ 'pause-circle-o', 'far', 'pause-circle' ],
			'fa-stop-circle-o'        => [ 'stop-circle-o', 'far', 'stop-circle' ],
			'fa-bluetooth'            => [ 'bluetooth', 'fab', null ],
			'fa-bluetooth-b'          => [ 'bluetooth-b', 'fab', null ],
			'fa-gitlab'               => [ 'gitlab', 'fab', null ],
			'fa-wpbeginner'           => [ 'wpbeginner', 'fab', null ],
			'fa-wpforms'              => [ 'wpforms', 'fab', null ],
			'fa-envira'               => [ 'envira', 'fab', null ],
			'fa-wheelchair-alt'       => [ 'wheelchair-alt', 'fab', 'accessible-icon' ],
			'fa-question-circle-o'    => [ 'question-circle-o', 'far', 'question-circle' ],
			'fa-volume-control-phone' => [ 'volume-control-phone', null, 'phone-volume' ],
			'fa-asl-interpreting'     => [ 'asl-interpreting', null, 'american-sign-language-interpreting' ],
			'fa-deafness'             => [ 'deafness', null, 'deaf' ],
			'fa-hard-of-hearing'      => [ 'hard-of-hearing', null, 'deaf' ],
			'fa-glide'                => [ 'glide', 'fab', null ],
			'fa-glide-g'              => [ 'glide-g', 'fab', null ],
			'fa-signing'              => [ 'signing', null, 'sign-language' ],
			'fa-viadeo'               => [ 'viadeo', 'fab', null ],
			'fa-viadeo-square'        => [ 'viadeo-square', 'fab', null ],
			'fa-snapchat'             => [ 'snapchat', 'fab', null ],
			'fa-snapchat-ghost'       => [ 'snapchat-ghost', 'fab', null ],
			'fa-snapchat-square'      => [ 'snapchat-square', 'fab', null ],
			'fa-pied-piper'           => [ 'pied-piper', 'fab', null ],
			'fa-first-order'          => [ 'first-order', 'fab', null ],
			'fa-yoast'                => [ 'yoast', 'fab', null ],
			'fa-themeisle'            => [ 'themeisle', 'fab', null ],
			'fa-google-plus-official' => [ 'google-plus-official', 'fab', 'google-plus' ],
			'fa-google-plus-circle'   => [ 'google-plus-circle', 'fab', 'google-plus' ],
			'fa-font-awesome'         => [ 'font-awesome', 'fab', null ],
			'fa-fa'                   => [ 'fa', 'fab', 'font-awesome' ],
			'fa-handshake-o'          => [ 'handshake-o', 'far', 'handshake' ],
			'fa-envelope-open-o'      => [ 'envelope-open-o', 'far', 'envelope-open' ],
			'fa-linode'               => [ 'linode', 'fab', null ],
			'fa-address-book-o'       => [ 'address-book-o', 'far', 'address-book' ],
			'fa-vcard'                => [ 'vcard', null, 'address-card' ],
			'fa-address-card-o'       => [ 'address-card-o', 'far', 'address-card' ],
			'fa-vcard-o'              => [ 'vcard-o', 'far', 'address-card' ],
			'fa-user-circle-o'        => [ 'user-circle-o', 'far', 'user-circle' ],
			'fa-user-o'               => [ 'user-o', 'far', 'user' ],
			'fa-id-badge'             => [ 'id-badge', 'far', null ],
			'fa-drivers-license'      => [ 'drivers-license', null, 'id-card' ],
			'fa-id-card-o'            => [ 'id-card-o', 'far', 'id-card' ],
			'fa-drivers-license-o'    => [ 'drivers-license-o', 'far', 'id-card' ],
			'fa-quora'                => [ 'quora', 'fab', null ],
			'fa-free-code-camp'       => [ 'free-code-camp', 'fab', null ],
			'fa-telegram'             => [ 'telegram', 'fab', null ],
			'fa-thermometer-4'        => [ 'thermometer-4', null, 'thermometer-full' ],
			'fa-thermometer'          => [ 'thermometer', null, 'thermometer-full' ],
			'fa-thermometer-3'        => [ 'thermometer-3', null, 'thermometer-three-quarters' ],
			'fa-thermometer-2'        => [ 'thermometer-2', null, 'thermometer-half' ],
			'fa-thermometer-1'        => [ 'thermometer-1', null, 'thermometer-quarter' ],
			'fa-thermometer-0'        => [ 'thermometer-0', null, 'thermometer-empty' ],
			'fa-bathtub'              => [ 'bathtub', null, 'bath' ],
			'fa-s15'                  => [ 's15', null, 'bath' ],
			'fa-window-maximize'      => [ 'window-maximize', 'far', null ],
			'fa-window-restore'       => [ 'window-restore', 'far', null ],
			'fa-times-rectangle'      => [ 'times-rectangle', null, 'window-close' ],
			'fa-window-close-o'       => [ 'window-close-o', 'far', 'window-close' ],
			'fa-times-rectangle-o'    => [ 'times-rectangle-o', 'far', 'window-close' ],
			'fa-bandcamp'             => [ 'bandcamp', 'fab', null ],
			'fa-grav'                 => [ 'grav', 'fab', null ],
			'fa-etsy'                 => [ 'etsy', 'fab', null ],
			'fa-imdb'                 => [ 'imdb', 'fab', null ],
			'fa-ravelry'              => [ 'ravelry', 'fab', null ],
			'fa-eercast'              => [ 'eercast', 'fab', 'sellcast' ],
			'fa-snowflake-o'          => [ 'snowflake-o', 'far', 'snowflake' ],
			'fa-superpowers'          => [ 'superpowers', 'fab', null ],
			'fa-wpexplorer'           => [ 'wpexplorer', 'fab', null ],
			'fa-cab'                  => [ 'cab', null, 'taxi' ],
		];
	}
}

/**
 * Instantiates the Fusion_Template_Builder class.
 * Make sure the class is properly set-up.
 *
 * @since object 2.2
 * @return object Fusion_App
 */
function AWB_Performance_Wizard() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Performance_Wizard::get_instance();
}
AWB_Performance_Wizard();

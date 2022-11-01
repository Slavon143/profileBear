<?php
/**
 * Handles redux in Avada.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      4.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handle Redux in Avada.
 */
class Avada_AvadaRedux extends Fusion_FusionRedux {

	/**
	 * Initializes and triggers all other actions/hooks.
	 *
	 * @access public
	 */
	public function init_fusionredux() {

		$this->args['sections'] = Avada::get_options();

		add_action( 'admin_menu', [ $this, 'deprecated_adminpage_hook' ] );
		add_filter( 'fusion_redux_typography_font_groups', [ $this, 'fusion_redux_typography_font_groups' ] );
		add_filter( 'fusion_options_font_size_dimension_fields', [ $this, 'fusion_options_font_size_dimension_fields' ] );
		add_filter( 'fusion_options_sliders_not_in_pixels', [ $this, 'fusion_options_sliders_not_in_pixels' ] );

		if ( class_exists( 'Fusion_Builder_Redux' ) ) {
			// Split to multiple lines for PHP 5.2 compatibility.
			$fusion_builder               = FusionBuilder();
			$fusion_builder_options_panel = $fusion_builder->get_fusion_builder_options_panel();
			$fusion_builder_redux         = $fusion_builder_options_panel->get_fusion_builder_redux();
			add_filter( 'fusion_options_sliders_not_in_pixels', [ $fusion_builder_redux, 'fusion_options_sliders_not_in_pixels' ] );
		}
		parent::init_fusionredux();

		// Import options via Ajax.
		add_action( 'wp_ajax_custom_option_import_code', [ $this, 'custom_option_import_code' ] );

		// Importing/switching color scheme.
		add_action( 'wp_ajax_custom_option_import', [ $this, 'reset_caches_handler' ] );

		// Custom color scheme ajax save.
		add_action( 'wp_ajax_custom_colors_ajax_save', [ $this, 'custom_colors_ajax_save' ] );

		// Custom color scheme ajax delete.
		add_action( 'wp_ajax_custom_colors_ajax_delete', [ $this, 'custom_colors_ajax_delete' ] );
	}

	/**
	 * Import settings via Ajax.
	 *
	 * @access public
	 */
	public function custom_option_import_code() {
		$option_name = Fusion_Settings::get_option_name();
		$nonce_name  = 'fusionredux_ajax_nonce' . $option_name;
		if ( ! isset( $_REQUEST['security'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['security'] ), $nonce_name ) ) { // phpcs:ignore WordPress.Security
			echo wp_json_encode(
				[
					'status' => 'failed',
					'action' => 'reload',
				]
			);
			die();
		}

		if ( isset( $_POST['data'] ) && ! empty( $_POST['data'] ) ) {
			$values      = [];
			$fusionredux = FusionReduxFrameworkInstances::get_instance( $option_name );

			$values = $fusionredux->fields;
			$values = wp_parse_args(
				get_option( $option_name ),
				$values
			);

			if ( isset( $_POST['data']['import_code'] ) && '' !== $_POST['data']['import_code'] ) {
				$import_code           = stripslashes( $_POST['data']['import_code'] ); // phpcs:ignore WordPress.Security
				$values['import_code'] = $import_code;
			} elseif ( isset( $_POST['data']['import_link'] ) && '' !== $_POST['data']['import_link'] ) {
				$values['import_link'] = $_POST['data']['import_link']; // phpcs:ignore WordPress.Security
			} else {
				echo wp_json_encode(
					[
						'status' => 'failed',
						'action' => 'reload',
					]
				);
				die();
			}

			if ( isset( $fusionredux->validation_ran ) ) {
				unset( $fusionredux->validation_ran );
			}

			$fusionredux->set_options( $fusionredux->_validate_options( $values ) );

			echo wp_json_encode(
				[
					'status' => 'success',
					'action' => 'reload',
				]
			);
		} else {
			echo wp_json_encode(
				[
					'status' => 'failed',
					'action' => 'reload',
				]
			);
		}
		die();
	}

	/**
	 * Save the custom color scheme to an option
	 *
	 * @since 5.0.0
	 * @return void
	 */
	public function custom_colors_ajax_save() {

		global $wpdb;

		// Check that the user has the right permissions.
		if ( ! current_user_can( 'switch_themes' ) ) {
			return;
		}

		if ( ! empty( $_POST['data'] ) && isset( $_POST['data']['values'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$existing_colors = get_option( 'avada_custom_color_schemes', [] );

			if ( ! empty( $_POST['data']['type'] ) && 'import' !== $_POST['data']['type'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				$scheme        = [];
				$scheme_colors = wp_unslash( $_POST['data']['values'] ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
				$scheme_name   = isset( $_POST['data']['name'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

				if ( defined( 'FUSION_BUILDER_PLUGIN_DIR' ) ) {
					$fb_options = get_option( 'fusion_options' );
					foreach ( $scheme_colors as $option => $value ) {
						if ( array_key_exists( $option, $fb_options ) ) {
							$scheme_colors[ $option ] = $fb_options[ $option ];
						}
					}
				}

				$scheme[] = [
					'name'   => $scheme_name,
					'values' => $scheme_colors,
				];

				// Check if scheme trying to be saved already exists, if so unset and merge.
				if ( isset( $_POST['data']['type'] ) && 'update' === $_POST['data']['type'] ) { // phpcs:ignore WordPress.Security.NonceVerification
					// Remove existing saved version and and merge in.
					foreach ( $existing_colors as $key => $existing_color ) {
						if ( $existing_color['name'] === $scheme_name ) {
							unset( $existing_colors[ $key ] );
						}
					}
					$schemes = array_merge( $scheme, $existing_colors );
				} elseif ( is_array( $existing_colors ) ) {
					$schemes = array_merge( $scheme, $existing_colors );
				} else {
					$schemes = $scheme;
				}

				// Sanitize schemes.
				$schemes = $this->sanitize_color_schemes( $schemes );

				update_option( 'avada_custom_color_schemes', $schemes );
				echo wp_json_encode(
					[
						'status' => 'success',
						'action' => '',
					]
				);

			} else {
				$schemes = stripslashes( stripcslashes( wp_unslash( $_POST['data']['values'] ) ) ); // phpcs:ignore WordPress.Security
				$schemes = json_decode( $schemes, true );
				if ( is_array( $existing_colors ) ) {
					// Add imported schemes to existing set.
					$schemes = array_merge( $schemes, $existing_colors );
				}

				// Sanitize schemes.
				$schemes = $this->sanitize_color_schemes( $schemes );

				update_option( 'avada_custom_color_schemes', $schemes );

				echo wp_json_encode(
					[
						'status' => 'success',
						'action' => '',
					]
				);
			}
		}
		die();
	}

	/**
	 * Delete the custom color schemes selected
	 *
	 * @since 5.0.0
	 * @return void
	 */
	public function custom_colors_ajax_delete() {

		global $wpdb;

		// Check that the user has the right permissions.
		if ( ! current_user_can( 'switch_themes' ) ) {
			return;
		}

		if ( ! empty( $_POST['data'] ) && isset( $_POST['data']['names'] ) && is_array( $_POST['data']['names'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$existing_colors = get_option( 'avada_custom_color_schemes', [] );
			$post_data_names = wp_unslash( $_POST['data']['names'] ); // phpcs:ignore WordPress.Security
			foreach ( $post_data_names as $scheme_name ) {
				$scheme_name = sanitize_text_field( $scheme_name );
				// Remove from array of existing schemes.
				foreach ( $existing_colors as $key => $existing_color ) {
					if ( $existing_color['name'] === $scheme_name ) {
						unset( $existing_colors[ $key ] );
					}
				}
			}

			update_option( 'avada_custom_color_schemes', $existing_colors );

			echo wp_json_encode(
				[
					'status' => 'success',
					'action' => '',
				]
			);

		}
		die();
	}

	/**
	 * Register the page and then unregister it.
	 * This allows the user to access the URL of the page,
	 * but without an actual menu for the page.
	 *
	 * @access public
	 */
	public function deprecated_adminpage_hook() {
		add_submenu_page( 'themes.php', __( 'Avada Options have moved!', 'Avada' ), __( 'Avada Options', 'Avada' ), 'switch_themes', 'optionsframework', [ $this, 'deprecated_adminpage' ] );
		remove_submenu_page( 'themes.php', 'optionsframework' );
	}

	/**
	 * Creates a countdown counter and then redirects the user to the new admin page.
	 * We're using this to accomodate users that perhaps have the page bookmarked.
	 * This way they won't get an error page but we'll gracefully migrate them to the new page.
	 *
	 * @access public
	 */
	public function deprecated_adminpage() {
		?>
		<script type="text/javascript">
			var count = 6;
			var redirect = "<?php echo esc_url_raw( admin_url( 'themes.php?page=avada_options' ) ); ?>";

			function countDown(){
				var timer = document.getElementById("timer");
				if (count > 0){
					count--;
					<?php /* translators: Number. */ ?>
					timer.innerHTML = "<?php printf( esc_html__( 'Global Options have changed, redirecting you to the new page in %s seconds.', 'Avada' ), '" + count + "' ); ?>";
					setTimeout("countDown()", 1000);
				} else {
					window.location.href = redirect;
				}
			}
		</script>
		<span id="timer" style="font-size: 1.7em; padding: 100px; text-align: center; line-height: 10em;"><script type="text/javascript">countDown();</script></span>
		<?php
	}

	/**
	 * Add a "Custom Fonts" group.
	 *
	 * @access public
	 * @since 5.1
	 * @param array $font_groups An array of our font-groups.
	 * @return array
	 */
	public function fusion_redux_typography_font_groups( $font_groups ) {

		// Get Custom fonts.
		$options = get_option( Avada::get_option_name(), [] );

		if ( isset( $options['custom_fonts'] ) ) {

			$custom_fonts = $options['custom_fonts'];

			// Check if there's at least one custom font set.
			if ( isset( $custom_fonts['name'] ) && is_array( $custom_fonts['name'] ) && ! empty( $custom_fonts['name'][0] ) ) {

				// Add Custom Fonts group.
				$font_groups['customfonts'] = [
					'text'     => __( 'Custom Fonts', 'Avada' ),
					'children' => [],
				];

				// Add custom fonts.
				foreach ( $custom_fonts['name'] as $key => $label ) {

					$font_groups['customfonts']['children'][] = [
						'id'          => esc_attr( $label ),
						'text'        => esc_attr( $label ),
						'data-google' => 'false',
					];
				}
			}
		}
		return $font_groups;
	}

	/**
	 * Adds options to be processes as font-sizes.
	 * Affects the field's sanitization call.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param array $fields An array of fields.
	 * @return array
	 */
	public function fusion_options_font_size_dimension_fields( $fields ) {
		$extra_fields = [
			'meta_font_size',
			'es_title_font_size',
			'es_caption_font_size',
			'ec_sidew_font_size',
			'image_rollover_icon_size',
			'pagination_font_size',
			'form_input_height',
			'copyright_font_size',
			'tagline_font_size',
			'header_sticky_nav_font_size',
			'page_title_font_size',
			'page_title_subheader_font_size',
			'breadcrumbs_font_size',
			'social_links_font_size',
			'sidew_font_size',
			'slider_arrow_size',
			'slidingbar_font_size',
			'header_social_links_font_size',
			'footer_social_links_font_size',
			'sharing_social_links_font_size',
			'woo_icon_font_size',
		];
		return array_unique( array_merge( $fields, $extra_fields ) );
	}

	/**
	 * Sliders that are not in pixels.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param array $fields An array of fields.
	 * @return array
	 */
	public function fusion_options_sliders_not_in_pixels( $fields ) {
		$extra_fields = [
			'slidingbar_widgets_columns',
			'footer_widgets_columns',
			'blog_archive_grid_columns',
			'excerpt_length_blog',
			'blog_excerpt_length',
			'portfolio_archive_excerpt_length',
			'portfolio_archive_columns',
			'portfolio_archive_items',
			'portfolio_excerpt_length',
			'portfolio_items',
			'portfolio_columns',
			'posts_slideshow_number',
			'slideshow_speed',
			'tfes_interval',
			'tfes_speed',
			'lightbox_slideshow_speed',
			'lightbox_opacity',
			'map_zoom_level',
			'search_results_per_page',
			'number_related_posts',
			'related_posts_columns',
			'related_posts_speed',
			'related_posts_swipe_items',
			'pw_jpeg_quality',
			'woo_items',
			'woocommerce_shop_page_columns',
			'woocommerce_related_columns',
			'woocommerce_archive_page_columns',
			'woocommerce_product_images_thumbnail_column_width',
			'typography_sensitivity',
			'typography_factor',
			'testimonials_speed',
			'masonry_grid_ratio',
			'privacy_expiry',
			'pagination_range',
			'pagination_start_end_range',
			'search_excerpt_length',
			'search_grid_columns',
			'live_search_min_char_count',
			'live_search_results_per_page',
		];
		return array_unique( array_merge( $fields, $extra_fields ) );
	}

	/**
	 * Extra functionality on save.
	 *
	 * @access public
	 * @since 4.0
	 * @param array $data           The data.
	 * @param array $changed_values The changed values to save.
	 * @return void
	 */
	public function save_as_option( $data, $changed_values ) {
		update_option( 'avada_disable_encoding', $data['disable_code_block_encoding'] );
		// Delete migration option for 5.1.
		if ( isset( $data['site_width'] ) && false === strpos( $data['site_width'], 'calc' ) ) {
			delete_option( 'avada_510_site_width_calc' );
		}
	}

	/**
	 * Sanitizes color schemes.
	 *
	 * @since 5.1.0
	 * @param array $schemes The color schemes.
	 * @return array The color schemens, sanitized.
	 */
	private function sanitize_color_schemes( $schemes ) {

		if ( ! is_array( $schemes ) ) {
			return [];
		}
		$final_schemes = [];
		foreach ( $schemes as $scheme ) {
			// Sanitize the scheme name.
			if ( ! isset( $scheme['name'] ) ) {
				$scheme['name'] = '';
			}
			$scheme['name'] = esc_attr( $scheme['name'] );
			// Sanitize the scheme values.
			if ( ! isset( $scheme['values'] ) ) {
				$scheme['values'] = [];
			}
			$scheme_values = [];
			foreach ( $scheme['values'] as $key => $value ) {
				$key = sanitize_key( $key );
				// Color sanitization.
				$color_obj             = Fusion_Color::new_color( $value );
				$scheme_values[ $key ] = $color_obj->toCSS( $color_obj->mode );
			}
			$final_schemes[] = [
				'name'   => $scheme['name'],
				'values' => $scheme_values,
			];
		}
		return $final_schemes;
	}
}

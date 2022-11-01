<?php
/**
 * Avada Studio
 *
 * @package Avada-Builder
 * @since 3.5
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * AWB Studio class.
 *
 * @since 3.5
 */
class AWB_Studio_Import {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 3.0
	 * @var object
	 */
	private static $instance;

	/**
	 * The studio data.
	 *
	 * @access public
	 * @var mixed
	 */
	public $data = null;

	/**
	 * Whether to update existing posts when importing.
	 *
	 * @access public
	 * @var mixed
	 */
	public $update_post = false;

	/**
	 * URL to fetch from.
	 *
	 * @access public
	 * @var boolean
	 */
	public $studio_url = 'https://avada.studio/';

	/**
	 * Import options.
	 *
	 * @access protected
	 * @var array
	 */
	protected $import_options = [
		'type'   => null,
		'invert' => null,
		'images' => null,
	];

	/**
	 * Class constructor.
	 *
	 * @since 3.0
	 * @access private
	 */
	private function __construct() {

		if ( ! class_exists( 'AWB_Studio' ) || ! AWB_Studio::is_studio_enabled() ) {
			return;
		}

		// Downloads and imports icons package.
		add_filter( 'awb_studio_post_imported', [ $this, 'import_icons_package' ] );

		// Import Studio Media from Builder (both live and backend).
		add_action( 'wp_ajax_awb_studio_import_media', [ $this, 'ajax_import_media' ] );
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 3.0
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new AWB_Studio_Import();
		}
		return self::$instance;
	}

	/**
	 * Set import options from global $_REQUEST array.
	 *
	 * @access public
	 * @since 3.7
	 * @return void
	 */
	public function set_import_options_from_request() {

		if ( isset( $_REQUEST['overWriteType'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->import_options['type'] = sanitize_text_field( wp_unslash( $_REQUEST['overWriteType'] ) );  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( isset( $_REQUEST['shouldInvert'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->import_options['invert'] = sanitize_text_field( wp_unslash( $_REQUEST['shouldInvert'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( isset( $_REQUEST['imagesImport'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->import_options['images'] = sanitize_text_field( wp_unslash( $_REQUEST['imagesImport'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}

	/**
	 * Set import options.
	 *
	 * @access public
	 * @since 3.7
	 * @param array $new_options array New options.
	 * @return void
	 */
	public function set_import_options( $new_options ) {

		if ( isset( $new_options['type'] ) ) {
			$this->import_options['type'] = $new_options['type'];
		}

		if ( isset( $new_options['invert'] ) ) {
			$this->import_options['invert'] = $new_options['invert'];
		}

		if ( isset( $new_options['images'] ) ) {
			$this->import_options['images'] = $new_options['images'];
		}
	}

	/**
	 * Get import options.
	 *
	 * @access public
	 * @since 3.7
	 * @return array
	 */
	public function get_import_options() {
		return $this->import_options;
	}

	/**
	 * Get the data for ajax requests.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function get_ajax_data() {
		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

		echo wp_json_encode( $this->get_data() );
		wp_die();
	}

	/**
	 * Fetches studio content from REST API endpoint.
	 * Used to import studio content directly into to the page content.
	 *
	 * @access public
	 * @since 3.5
	 * @return array
	 */
	public function get_studio_content() {
		$studio_data = AWB_Studio()->get_data();
		$layout_id   = (int) $_POST['fusion_layout_id']; // phpcs:ignore WordPress.Security
		$category    = isset( $_POST['category'] ) ? (string) esc_attr( $_POST['category'] ) : false; // phpcs:ignore WordPress.Security

		$layout_data = [
			'post_content' => '',
		];

		if ( $category ) {
			if ( ! isset( $studio_data[ $category ] ) ) {
				echo wp_json_encode( $layout_data );
				wp_die();
			}
			$layout = $studio_data[ $category ][ 'item-' . $layout_id ];
		} else {
			if ( ! isset( $studio_data['fusion_template'] ) ) {
				echo wp_json_encode( $layout_data );
				wp_die();
			}
			$layout = $studio_data['fusion_template'][ 'item-' . $layout_id ];
		}

		// No layout found.
		if ( ! is_array( $layout ) ) {
			return $layout_data;
		}

		// Fetch studio object data.
		$response = wp_remote_get( $this->studio_url . '/wp-json/wp/v2/' . $layout['post_type'] . '/' . $layout_id . '/' );

		// TODO: better error handling.
		if ( is_wp_error( $response ) ) {
			return $layout_data;
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		$response_body['content']['raw'] = isset( $response_body['content']['raw'] ) ? $response_body['content']['raw'] : '';
		$post_meta                       = isset( $response_body['post_meta'] ) ? $response_body['post_meta'] : '';

		// Colors & typography overwrite.
		if ( isset( $response_body['post_meta'] ) ) {
			$overwrite                  = $this->get_colors_and_typography_overwrite_map( $response_body );
			$response_body['post_meta'] = $this->overwrite_colors_and_typography( $response_body['post_meta'], $overwrite );
		}

		if ( isset( $response_body['content']['raw'] ) ) {
			// Process title typography.
			if ( '' !== $post_meta ) {
				$response_body['content']['raw'] = $this->process_title_typography( $response_body['content']['raw'], $post_meta );
			}

			$response_body['content']['raw'] = $this->overwrite_colors_and_typography( $response_body['content']['raw'], $overwrite );

			// If placeholders are selected, replace images with them.
			if ( 'dont-import-images' === $this->import_options['images'] ) {
				$response_body = $this->replace_with_placeholders( $response_body );
			}
		}

		if ( $post_meta && isset( $post_meta['_fusion'] ) ) {
			$remove_keys = [ 'studio_replace_params', 'exclude_form_studio' ];

			// Remove internal studio options.
			foreach ( $remove_keys as $key ) {
				if ( isset( $post_meta['_fusion'][ $key ] ) ) {
					unset( $post_meta['_fusion'][ $key ] );
				}
			}

			if ( empty( $post_meta['_fusion'] ) ) {
				unset( $post_meta['_fusion'] );
			}
		}

		// Basic content cleanup.
		$response_body['content']['raw'] = $this->post_content_cleanup( $response_body['content']['raw'] );

		return [
			'post_id'      => absint( $_POST['post_id'] ), // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			'post_content' => $response_body['content']['raw'],
			'avada_media'  => $response_body['avada_media'],
			'custom_css'   => isset( $response_body['custom_css'] ) ? $response_body['custom_css'] : '',
			'post_meta'    => $post_meta,
			'mapping'      => isset( $overwrite ) ? $overwrite : [],
		];
	}

	/**
	 * Replace images in content with placeholders, also remove from media map.
	 *
	 * @access public
	 * @since 3.7
	 * @param string $content Post content to process.
	 * @param array  $post_meta Post meta to check for typography size overrides.
	 * @return array
	 */
	public function process_title_typography( $content = '', $post_meta = [] ) {
		// No fusion post meta data.
		if ( ! isset( $post_meta['_fusion'] ) ) {
			return $content;
		}

		// No title elements.
		if ( false === strpos( $content, 'fusion_title' ) ) {
			return $content;
		}

		$type    = $this->import_options['type'];
		$pattern = get_shortcode_regex( [ 'fusion_title' ] );
		$meta    = $post_meta['_fusion'];

		// Replace all if heading for inherit, use font size value for as is.
		return preg_replace_callback(
			"/$pattern/",
			function( $m ) use ( $meta, $type ) {
				$tag  = $m[2];
				$attr = shortcode_parse_atts( $m[3] );

				// Not a heading tag.
				if ( ! isset( $attr['size'] ) || 'div' === $attr['size'] ) {
					return $m[0];
				}

				// Inherit, we just need to wipe out heading typo set variables.
				if ( 'inherit' === $type ) {
					$find = [
						'"var(--awb-typography1-font-size)"',
						'"var(--awb-typography1-font-family)"',
						'"var(--awb-typography1-font-variant)"',
						'"var(--awb-typography1-font-weight)"',
						'"var(--awb-typography1-font-style)"',
						'"var(--awb-typography1-line-height)"',
						'"var(--awb-typography1-letter-spacing)"',
						'"var(--awb-typography1-text-transform)"',
					];
					return str_replace( $find, '""', $m[0] );
				}

				// If we have a font size override set, then use that.
				$meta_key = 'h' . $attr['size'] . '_size';
				if ( isset( $meta[ $meta_key ] ) && '' !== $meta[ $meta_key ] ) {
					return str_replace( 'font_size="var(--awb-typography1-font-size)"', 'font_size="' . $meta[ $meta_key ] . '"', $m[0] );
				}

				return $m[0];
			},
			$content
		);
	}

	/**
	 * Gets colors and typography overwrite map.
	 *
	 * @access public
	 * @since 3.7
	 * @param array $data The item data.
	 * @return array
	 */
	public function get_colors_and_typography_overwrite_map( $data ) {
		$overwrite_palette    = $this->get_overwrite_palette( $data );
		$overwrite_typography = $this->get_overwrite_typography( $data );

		return array_merge( $overwrite_palette, $overwrite_typography );
	}

	/**
	 * Replace images with placeholders.
	 *
	 * @access public
	 * @since 3.7
	 * @param array $data Data of content and avada_media.
	 * @return array
	 */
	public function replace_images( $data = [] ) {
		$data['content']['raw'] = $data['post_content'];
		$data                   = $this->replace_with_placeholders( $data );
		$data['post_content']   = $data['content']['raw'];
		return $data;
	}

	/**
	 * Replace images in content with placeholders, also remove from media map.
	 *
	 * @access public
	 * @since 3.7
	 * @param array $data Data of content and avada_media.
	 * @return array
	 */
	public function replace_with_placeholders( $data = [] ) {
		if ( ! isset( $data['avada_media']['images'] ) || ! isset( $data['content']['raw'] ) ) {
			return $data;
		}

		// First lets create a replacement map for each image.
		foreach ( (array) $data['avada_media']['images'] as $url => $this_data ) {

			// If we have the image ID, we use that to lookup.
			$id     = isset( $this_data['image_id'] ) ? (int) $this_data['image_id'] : false;
			$lookup = $id ? $id : $url;

			$args = [
				'timeout'    => 30,
				'user-agent' => 'avada-user-agent',
			];

			// Fetch the dimensions and color of the image.
			$response   = wp_remote_get( $this->studio_url . 'wp-json/studio/image/' . $lookup . '/', $args );
			$image_data = [];
			$defaults   = [
				'width'  => '1067',
				'height' => '667',
				'color'  => '#808080',
				'var'    => 'color3',
			];

			$image_data = [];
			if ( ! is_wp_error( $response ) ) {
				$response = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( ! empty( $response ) ) {
					$image_data = $response;
				}
			}

			// Merge so that we do not have empty dimensions.
			$image_data = wp_parse_args( $image_data, $defaults );

			// If it had an ID, just wipe it out.
			if ( $id ) {
				$data['content']['raw'] = str_replace( 'image_id="' . $id, 'image_id="', $data['content']['raw'] );
			}

			// Use variable closest to image luminance if its inherit mode.
			if ( 'inherit' === $this->import_options['type'] ) {
				$palette = fusion_get_option( 'color_palette' );

				// Invert inherit, flip the var.
				if ( 'do-invert' === $this->import_options['invert'] ) {
					$flip              = [
						'color1' => 'color8',
						'color2' => 'color7',
						'color3' => 'color6',
						'color4' => 'color5',
						'color5' => 'color4',
						'color6' => 'color3',
						'color7' => 'color2',
						'color8' => 'color1',
					];
					$image_data['var'] = str_replace( array_keys( $flip ), array_values( $flip ), $image_data['var'] );
				}

				// If var is set, use that as the color.
				if ( isset( $palette[ $image_data['var'] ]['color'] ) ) {
					$image_data['color'] = $palette[ $image_data['var'] ]['color'];
				}
			}

			// Replace image URL with encoded placeholder image.
			$data['content']['raw'] = str_replace( $url, $this->generate_dynamic_placeholder( $image_data ), $data['content']['raw'] );
		}

		// Skip all images from being downloaded.
		$data['avada_media']['images'] = [];

		return $data;
	}

	/**
	 * Checks if a string is a placeholder.
	 *
	 * @access public
	 * @since 7.7
	 * @param array $data The image data.
	 * @return string
	 */
	public function generate_dynamic_placeholder( $data = '' ) {
		$text_color       = 'rgba(0,0,0,0.5)';
		$brightness_level = Fusion_Color::new_color( $data['color'] )->brightness;
		if ( isset( $brightness_level['total'] ) && $brightness_level['total'] < 140 ) {
			$text_color = 'rgba(255,255,255,0.5)';
		}
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $data['width'] . '" height="' . $data['height'] . '" viewBox="0 0 ' . $data['width'] . ' ' . $data['height'] . '"><rect fill="' . $data['color'] . '" width="' . $data['width'] . '" height="' . $data['height'] . '"/><text fill="' . $text_color . '" font-family="sans-serif" font-size="30" dy="10.5" font-weight="bold" x="50%" y="50%" text-anchor="middle">' . $data['width'] . '×' . $data['height'] . '</text></svg>';

		return 'data:image/svg+xml;utf8,' . rawurlencode( $svg );
	}

	/**
	 * Imports needed studio post assets.
	 *
	 * @access public
	 * @since 3.5
	 * @param array $layout Holds content and import assets data.
	 * @return array
	 */
	public function process_studio_content( $layout ) {

		// Post content set.
		$post_content = $layout['post_content'];

		$layout_data  = [];
		$off_canvases = [];
		$mapping      = isset( $layout['overwrite'] ) ? $layout['overwrite'] : [];
		$mapping      = $this->is_json( $mapping ) ? json_decode( $mapping, true ) : $mapping;

		if ( ! isset( $layout['post_id'] ) ) {
			$layout['post_id'] = null;
		}

		// Check for other media to be imported.
		if ( isset( $layout['avada_media'] ) && ! empty( $layout['avada_media'] ) ) {

			// Import images if they are set.
			if ( isset( $layout['avada_media']['images'] ) && ! empty( $layout['avada_media']['images'] ) && current_user_can( 'upload_files' ) ) {
				foreach ( (array) $layout['avada_media']['images'] as $image_url => $replacements ) {
					$existing_image = $this->find_existing_media( $image_url );
					if ( $existing_image ) {
						$image_id = $existing_image;
					} else {

						// We don't already have it, need to load it.
						$image_id = media_sideload_image( $image_url, $layout['post_id'], null, 'id' ); // phpcs:ignore WordPress.Security

						if ( ! is_wp_error( $image_id ) ) {
							// Add flag to prevent duplicate imports.
							$this->add_media_meta( $image_id, $image_url );
						}
					}

					if ( ! is_wp_error( $image_id ) ) {
						foreach ( (array) $replacements as $param_name => $old_value ) {
							// Get ID if its mixed with size.
							$old_id    = (int) $old_value;
							$new_value = str_replace( $old_id, $image_id, $old_value );

							// Replace the old image ID with the new one.
							$post_content = str_replace( $param_name . '="' . $old_value . '"', $param_name . '="' . $new_value . '"', $post_content );
						}
						$new_url = wp_get_attachment_url( $image_id );
					} else {
						foreach ( (array) $replacements as $param_name => $old_value ) {

							// Replace the old image ID with the empty value.
							$post_content = str_replace( $param_name . '="' . $old_value . '"', $param_name . '=""', $post_content );
						}

						$new_url = '';
					}

					// Replace the URL as well.
					$post_content = str_replace( $image_url, $new_url, $post_content );
				}
			}

			// Import videos if they are set.
			if ( isset( $layout['avada_media']['videos'] ) && ! empty( $layout['avada_media']['videos'] ) && current_user_can( 'upload_files' ) ) {
				foreach ( $layout['avada_media']['videos'] as $video_url => $active ) {

					$new_video_url = $this->import_video( $video_url );

					// If import failed $new_video_url will be empty string.
					$post_content = str_replace( $video_url, $new_video_url, $post_content );
				}
			}

			// Import menus if they are set.
			if ( isset( $layout['avada_media']['menus'] ) && ! empty( $layout['avada_media']['menus'] ) && current_user_can( 'edit_theme_options' ) ) {
				foreach ( $layout['avada_media']['menus'] as $menu_slug => $active ) {
					if ( $active ) {
						$new_menu = $this->import_menu( $menu_slug );
					}
					// Can use new menu ID here is we want but slug is unchanged anyway.
				}
			}

			// Import forms if they are set.
			if ( isset( $layout['avada_media']['forms'] ) && ! empty( $layout['avada_media']['forms'] ) && current_user_can( 'edit_theme_options' ) ) {
				foreach ( $layout['avada_media']['forms'] as $form_post_id => $active ) {

					$post_details     = $this->import_post(
						[
							'post_id'   => $form_post_id,
							'post_type' => 'fusion_form',
						],
						[],
						true,
						$mapping
					);
					$new_form_post_id = $post_details['post_id'];

					if ( $new_form_post_id ) {
						$post_content = str_replace( 'form_post_id="' . $form_post_id . '"', 'form_post_id="' . $new_form_post_id . '"', $post_content );
					}
				}
			}

			// Import referenced off canvases if set.
			if ( isset( $layout['avada_media']['off_canvases'] ) && ! empty( $layout['avada_media']['off_canvases'] ) && current_user_can( 'edit_theme_options' ) && class_exists( 'AWB_Off_Canvas' ) && false !== AWB_Off_Canvas::is_enabled() ) {
				foreach ( $layout['avada_media']['off_canvases'] as $off_canvas_id => $active ) {

					$post_details                   = $this->import_post(
						[
							'post_id'   => $off_canvas_id,
							'post_type' => 'awb_off_canvas',
						],
						[],
						true,
						$mapping
					);
					$new_off_canvas_id              = $post_details['post_id'];
					$off_canvases[ $off_canvas_id ] = $new_off_canvas_id;

					// Update dynamic data references.
					if ( false !== strpos( $post_content, 'b2ZmX2NhbnZhc' ) && false !== strpos( $post_content, 'dynamic_params' ) ) {
						preg_match_all( '/(?<=dynamic_params=")(.*?)(?=\")/', $post_content, $matches );
						if ( ! empty( $matches ) ) {
							foreach ( (array) $matches[0] as $match ) {
								if ( false !== strpos( $match, 'b2ZmX2NhbnZhc' ) ) {
									$dynamic_params = json_decode( base64_decode( $match ), true );
									if ( is_array( $dynamic_params ) ) {
										foreach ( $dynamic_params as $id => $data ) {

											if ( isset( $data['off_canvas_id'] ) ) {
												$dynamic_params['link']['off_canvas_id'] = isset( $off_canvases[ $dynamic_params['link']['off_canvas_id'] ] ) ? $off_canvases[ $dynamic_params['link']['off_canvas_id'] ] : $dynamic_params['link']['off_canvas_id'];
												$update_contents                         = base64_encode( wp_json_encode( $dynamic_params ) );
											}
										}
										$post_content = str_replace( $match, $update_contents, $post_content );
									}
								}
							}
						}
					}
				}

				// Update menu references.
				$menus = $post_data = isset( $_POST['data']['postData']['avada_media']['menus'] ) ? wp_unslash( $_POST['data']['postData']['avada_media']['menus'] ) : []; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				foreach ( $menus as $menu_slug => $active ) {
					if ( $active ) {
						$this->update_menu_off_canvas_references( $menu_slug, $off_canvases );
					}
				}
			}

			// Import post cards if they are set.
			if ( isset( $layout['avada_media']['post_cards'] ) && ! empty( $layout['avada_media']['post_cards'] ) && current_user_can( 'edit_theme_options' ) ) {
				foreach ( $layout['avada_media']['post_cards'] as $post_card_post_id => $active ) {

					$post_details          = $this->import_post(
						[
							'post_id'   => $post_card_post_id,
							'post_type' => 'fusion_element',
						],
						[],
						true,
						$mapping
					);
					$new_post_card_post_id = $post_details['post_id'];

					if ( $new_post_card_post_id ) {
						$post_content = str_replace( 'post_card="' . $post_card_post_id . '"', 'post_card="' . $new_post_card_post_id . '"', $post_content );
					}
				}
			}

			// Import icons if they are set.
			if ( isset( $layout['avada_media']['icons'] ) && ! empty( $layout['avada_media']['icons'] ) && current_user_can( 'upload_files' ) ) {
				foreach ( $layout['avada_media']['icons'] as $icons_post_id => $icons_css_prefix ) {
					$post_details = $this->import_post(
						[
							'post_id'   => $icons_post_id,
							'post_type' => 'fusion_icons',
						]
					);

					if ( isset( $post_details['custom_icons'] ) ) {
						if ( ! isset( $layout_data['custom_icons'] ) ) {
							$layout_data['custom_icons'] = [];
						}

						$layout_data['custom_icons'][] = $post_details['custom_icons'];
					}
				}
			}
		}

		// Set content.
		$layout_data['post_content'] = apply_filters( 'content_edit_pre', $post_content, $layout['post_id'] );

		if ( isset( $layout['custom_css'] ) && strlen( $layout['custom_css'] ) ) {
			$layout_data['custom_css'] = $layout['custom_css'];
		}

		return $layout_data;
	}

	/**
	 * Find an media with the post meta.
	 *
	 * @access public
	 * @since 3.5
	 * @param string $media_url The media URL on studio server.
	 * @return mixed
	 */
	public function find_existing_media( $media_url ) {
		global $wpdb;

		return $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				'SELECT `post_id` FROM `' . $wpdb->postmeta . '`
					WHERE `meta_key` = \'_avada_studio_media\'
						AND `meta_value` = %s
				;',
				md5( $media_url )
			)
		);
	}

	/**
	 * Add a meta flag to attachment.
	 *
	 * @access public
	 * @since 3.0
	 * @param int    $media_id The media ID in the database.
	 * @param string $media_url The media URL on studio server.
	 * @return mixed
	 */
	public function add_media_meta( $media_id, $media_url ) {
		if ( ! $media_id ) {
			return;
		}
		update_post_meta( $media_id, '_avada_studio_media', md5( $media_url ) );
	}

	/**
	 * Import a menu to compliment content.
	 *
	 * @access public
	 * @since 3.0
	 * @param string $menu_slug The menu slug to import.
	 * @return mixed
	 */
	public function import_menu( $menu_slug ) {
		$response = wp_remote_get( $this->studio_url . '/wp-json/studio/menu/' . $menu_slug );

		// Check for error.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data['nav_items'] ) ) {
			return false;
		}

		// Create a new menu.
		$menu_id = wp_create_nav_menu( $data['name'] );
		if ( is_wp_error( $menu_id ) ) {
			return false;
		}

		// Match old IDs to new, for hierarchy.
		$id_matcher   = [];
		$sidebar_data = false;

		foreach ( $data['nav_items'] as $nav_item ) {

			// Replace old ID with new for parent.
			if ( isset( $nav_item['post_meta']['menu-item-menu-item-parent'] ) && '' !== $nav_item['post_meta']['menu-item-menu-item-parent'] ) {
				$parent_id = $nav_item['post_meta']['menu-item-menu-item-parent'];
				if ( isset( $id_matcher[ $parent_id ] ) ) {
					$nav_item['post_meta']['menu-item-parent-id'] = $id_matcher[ $parent_id ];
				}
			}

			// Create menu item.
			$nav_item_id = wp_update_nav_menu_item( $menu_id, 0, $nav_item['post_meta'] );
			$old_item_id = (int) $nav_item['post']['ID'];

			if ( ! is_wp_error( $nav_item_id ) ) {

				// Match old to new ID.
				$id_matcher[ $old_item_id ] = $nav_item_id;

				// Update mega menu meta.
				if ( isset( $nav_item['post_meta']['menu-item-fusion-megamenu'] ) ) {
					update_post_meta( $nav_item_id, '_menu_item_fusion_megamenu', maybe_unserialize( $nav_item['post_meta']['menu-item-fusion-megamenu'] ) );
				}

				// Add meta so we know menu item was imported as studio content.
				update_post_meta( $nav_item_id, '_avada_studio_post', $old_item_id );

				// If we have sidebar data.
				if ( isset( $data['sidebars'] ) && ! empty( $data['sidebars'] ) ) {

					$existing_sidebars = get_option( 'sbg_sidebars', [] );
					$new_sidebars      = $data['sidebars'];
					$import_widgets    = false;
					foreach ( $new_sidebars as $sidebar_id => $sidebar_name ) {
						// New sidebar, add it in.
						if ( ! isset( $existing_sidebars[ $sidebar_id ] ) ) {
							$import_widgets                   = true;
							$existing_sidebars[ $sidebar_id ] = $sidebar_name;
							register_sidebar(
								[
									'name'          => $sidebar_name,
									'id'            => 'avada-custom-sidebar-' . $sidebar_id,
									'before_widget' => '<div id="%1$s" class="widget %2$s">',
									'after_widget'  => '</div>',
									'before_title'  => '<div class="heading"><h4 class="widget-title">',
									'after_title'   => '</h4></div>',
								]
							);
						}
					}

					if ( $import_widgets && function_exists( 'fusion_import_widget_data' ) ) {

						// Update custom option.
						update_option( 'sbg_sidebars', $existing_sidebars );

						// Import the widgets.
						fusion_import_widget_data( wp_json_encode( $data['widgets'] ) );
					}
				}
			}
		}

		return $menu_id;
	}

	/**
	 * Updates menu.
	 *
	 * @access public
	 * @since 3.6
	 * @param string $menu_slug    The menu slug to import.
	 * @param array  $off_canvases Referrenced off canvases in menu.
	 * @return void
	 */
	public function update_menu_off_canvas_references( $menu_slug, $off_canvases ) {

		// Get menu items.
		$nav_items = wp_get_nav_menu_items( $menu_slug );

		if ( is_array( $nav_items ) && ! empty( $nav_items ) ) {
			foreach ( $nav_items as $nav_item ) {
				$meta = maybe_unserialize( get_post_meta( $nav_item->ID, '_menu_item_fusion_megamenu', true ) );

				if ( isset( $meta['special_link'] ) && 'awb-off-canvas-menu-trigger' === $meta['special_link'] && ! empty( $meta['off_canvas_id'] ) && class_exists( 'AWB_Off_Canvas' ) && false !== AWB_Off_Canvas::is_enabled() ) {
					$meta['off_canvas_id'] = isset( $off_canvases[ $meta['off_canvas_id'] ] ) ? $off_canvases[ $meta['off_canvas_id'] ] : $meta['off_canvas_id'];
					update_post_meta( $nav_item->ID, '_menu_item_fusion_megamenu', $meta );
				}
			}
		}
	}

	/**
	 * Find a a post with the post meta.
	 *
	 * @access public
	 * @since 3.5
	 * @param int|string $import_key The studio post ID or studio post import key.
	 * @return mixed
	 */
	public function find_existing_post( $import_key ) {
		global $wpdb;

		return $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				'SELECT `post_id` FROM `' . $wpdb->postmeta . '`
					WHERE `meta_key` = \'_avada_studio_import_key\'
						AND `meta_value` = %s
				;',
				$import_key
			)
		);
	}

	/**
	 * Generate studio post's import key.
	 *
	 * @access protected
	 * @since 3.7
	 * @param int $studio_post_id The studio post ID.
	 * @return mixed
	 */
	protected function generate_post_import_key( $studio_post_id ) {
		$import_key = $studio_post_id;

		foreach ( $this->import_options as $key => $value ) {

			if ( null !== $value ) {
				$import_key .= '-' . $key . ':' . $value;
			}
		}

		if ( $import_key !== $studio_post_id ) {
			$import_key = md5( $import_key );
		}

		return $import_key;
	}

	/**
	 * Generate imported post title.
	 *
	 * @access protected
	 * @since 3.7
	 * @param string $post_title The post title..
	 * @return string
	 */
	protected function generate_post_title( $post_title ) {
		$title_suffix = '';

		if ( null !== $this->import_options['type'] && 'inherit' === $this->import_options['type'] ) {
			$title_suffix .= ' L';
		}

		if ( null !== $this->import_options['images'] && 'dont-import-images' === $this->import_options['images'] ) {
			$title_suffix .= ' P';
		}

		if ( null !== $this->import_options['invert'] && 'do-invert' === $this->import_options['invert'] ) {
			$title_suffix .= ' I';
		}

		if ( '' !== $title_suffix ) {
			$post_title .= ' (' . trim( $title_suffix ) . ')';
		}

		return $post_title;
	}

	/**
	 * Clean up anything unnecessary or required for better preview.
	 *
	 * @access public
	 * @since 3.7
	 * @param string $post_content Post content for content being imported.
	 * @return string
	 */
	public function post_content_cleanup( $post_content = '' ) {
		return preg_replace( '/posts_by="(.*?)"|include_term_portfolio_category="(.*?)"/', '', $post_content );
	}

	/**
	 * Import a form to compliment content.
	 *
	 * @access public
	 * @since 3.5
	 * @param array $studio_post  Studio post info.
	 * @param array $local_post   Local post info.
	 * @param bool  $import_media Should post media be imported with the content or not.
	 * @param array $replacements Array of colors & typography map to overwrite.
	 * @return mixed
	 */
	public function import_post( $studio_post = [], $local_post = [], $import_media = true, $replacements = [] ) {

		$studio_post_id   = isset( $studio_post['post_id'] ) ? $studio_post['post_id'] : 0;
		$studio_post_type = isset( $studio_post['post_type'] ) ? $studio_post['post_type'] : '';

		$post_id    = isset( $local_post['post_id'] ) ? $local_post['post_id'] : 0;
		$post_title = isset( $local_post['post_title'] ) ? $local_post['post_title'] : '';
		$post_type  = isset( $local_post['post_type'] ) ? $local_post['post_type'] : $studio_post_type;

		$import_key       = $this->generate_post_import_key( $studio_post_id );
		$existing_post_id = $this->find_existing_post( $import_key );

		if ( $post_id ) {
			$existing_post_id = $post_id;
		}

		// Any additonal data that post might need.
		$data              = [];
		$post_was_imported = false;

		// Post is already imported.
		if ( $existing_post_id && false === $this->update_post ) {
			$imported_post_id  = $existing_post_id;
			$post_was_imported = true;
		} else {

			// TODO: better error handling.
			if ( ! $studio_post_id ) {
				return [ 'post_id' => false ];
			}

			$response = wp_remote_get( $this->studio_url . '/wp-json/wp/v2/' . $studio_post_type . '/' . $studio_post_id . '/' );

			// TODO: better error handling.
			if ( is_wp_error( $response ) ) {
				return [ 'post_id' => false ];
			}

			$post_data                 = apply_filters( 'awb_studio_post_data', json_decode( wp_remote_retrieve_body( $response ), true ), $studio_post_id );
			$post_data['post_content'] = isset( $post_data['post_content'] ) ? $post_data['post_content'] : $post_data['content']['raw'];

			if ( '' === $post_title ) {
				$post_title = isset( $post_data['post_title'] ) ? $post_data['post_title'] : $post_data['title']['rendered'];
			}

			// Check if this is a setup wizard import.
			if ( $this->update_post ) {
				if ( 'page' === $post_type ) {

					// Change post type and set some page options.
					$post_data['post_meta']['_fusion']['main_padding']['top']    = '0px';
					$post_data['post_meta']['_fusion']['main_padding']['bottom'] = '0px';
					$post_data['post_meta']['_fusion']['page_title_bar']         = 'no';
				}

				// Add in menu and logo.
				$post_data = AWB_Setup_Wizard()->process_content( $post_data );
			}

			// Process title typography.
			if ( isset( $post_data['post_meta']['_fusion'] ) && '' !== $post_data['post_meta']['_fusion'] ) {
				$post_data['post_content'] = $this->process_title_typography( $post_data['post_content'], $post_data['post_meta'] );
			}

			// Colors & typography overwrite map.
			if ( empty( $replacements ) ) {
				$replacements = $this->get_colors_and_typography_overwrite_map( $post_data );
			}

			$post_data['post_content'] = $this->overwrite_colors_and_typography( $post_data['post_content'], $replacements );
			$post_data['post_meta']    = $this->overwrite_colors_and_typography( $post_data['post_meta'], $replacements );

			if ( 'dont-import-images' === $this->import_options['images'] ) {
				$post_data = $this->replace_images( $post_data );
			}

			// Basic content cleanup.
			$post_data['post_content'] = $this->post_content_cleanup( $post_data['post_content'] );

			$post_insert_data = apply_filters(
				'awb_studio_insert_post_data',
				[
					'post_title'   => 'page' === $post_type ? $post_title : $this->generate_post_title( $post_title ),
					'post_content' => $post_data['post_content'],
					'post_type'    => $post_type,
					'post_status'  => 'publish',
				],
				$studio_post_id
			);

			if ( $existing_post_id && true === $this->update_post ) {
				$post_insert_data['ID'] = $existing_post_id;
				$imported_post_id       = wp_update_post( $post_insert_data );
			} else {
				$imported_post_id = wp_insert_post( $post_insert_data );
			}

			// TODO: better error handling.
			if ( ! $imported_post_id || is_wp_error( $imported_post_id ) ) {
				return [ 'post_id' => false ];
			}

			if ( true === $import_media ) {
				$this->import_post_media( $imported_post_id, $post_insert_data['post_content'], $post_data['avada_media'], $replacements );
			}

			// Page means we always always want 100-width.
			if ( 'page' === $post_insert_data['post_type'] ) {
				update_post_meta( $imported_post_id, '_wp_page_template', '100-width.php' );
				update_post_meta( $imported_post_id, 'fusion_builder_status', 'active' );
			}

			// Set post terms.
			if ( isset( $post_data['terms'] ) && is_array( $post_data['terms'] ) ) {
				foreach ( $post_data['terms'] as $term ) {
					wp_set_object_terms( $imported_post_id, $term['slug'], $term['taxonomy'] );
				}
			}

			// Custom CSS.
			if ( isset( $post_data['custom_css'] ) && strlen( $post_data['custom_css'] ) ) {
				update_post_meta( $imported_post_id, '_fusion_builder_custom_css', $post_data['custom_css'] );
			}

			// Set post meta.
			if ( isset( $post_data['post_meta']['_fusion'] ) && '' !== $post_data['post_meta']['_fusion'] ) {
				update_post_meta( $imported_post_id, '_fusion', $post_data['post_meta']['_fusion'] );
			}

			// Set font meta.
			if ( isset( $post_data['fonts'] ) && is_array( $post_data['fonts'] ) ) {
				update_post_meta( $imported_post_id, '_fusion_google_fonts', $post_data['fonts'] );
			} elseif ( isset( $post_data['post_meta']['_fusion_google_fonts'] ) && '' !== $post_data['post_meta']['_fusion_google_fonts'] ) {
				update_post_meta( $imported_post_id, '_fusion_google_fonts', $post_data['post_meta']['_fusion_google_fonts'] );
			}

			// Icons specific stuff.
			if ( 'fusion_icons' === $studio_post_type ) {
				$data['package_url'] = $post_data['avada_media']['package_url'];
			}

			update_post_meta( $imported_post_id, '_avada_studio_import_key', $import_key );
			update_post_meta( $imported_post_id, '_avada_studio_post', $studio_post_id );
		}

		$post_details = [
			'post_id'      => $imported_post_id,
			'data'         => $data,
			'was_imported' => $post_was_imported,
			'avada_media'  => isset( $post_data ) && ! empty( $post_data['avada_media'] ) ? $post_data['avada_media'] : [],
			'mapping'      => isset( $replacements ) ? $replacements : [],
		];

		$post_details = apply_filters( 'awb_studio_post_imported', $post_details );

		return $post_details;
	}

	/**
	 * Overwrite colors & typography.
	 *
	 * @access public
	 * @since 3.7
	 * @param array|string $content   The post content.
	 * @param array        $overwrite The overwrite mapping array.
	 * @return array|string
	 */
	public function overwrite_colors_and_typography( $content, $overwrite ) {

		if ( ! is_array( $content ) ) {
			$content = str_replace( array_keys( $overwrite ), array_values( $overwrite ), $content );
			return $this->remove_calc_in_hsla_and_get_rgba( $content );
		}

		$overwrite_array = [];

		foreach ( $content as $key => $value ) {
			$overwrite_array[ $key ] = $this->overwrite_colors_and_typography( $value, $overwrite );
		}

		return $overwrite_array;
	}

	/**
	 * Calculate calc() functions between 2 simple values inside hsla, and transform to valid rgba.
	 *
	 * After the global color variables are replaced with actual values, the
	 * ones inside calc will be like "calc(40% + 20%)", triggering errors after.
	 *
	 * @param string $content The content.
	 * @return string Will return rgba value of the color.
	 */
	private function remove_calc_in_hsla_and_get_rgba( $content ) {
		$comma   = '\s*,\s*';
		$matches = [];
		// Try to resolve calc() functions that appear after values are replaced in hsla.
		preg_match_all(
			'/hsla\s*\(\s*' . // begin of hsla function.
			'(\d+)' . $comma . // hue.
			'(\d+%|calc\(\s*\d+%\s*(?:\+|-)\s*\d+%\s*\))' . $comma . // saturation.
			'(\d+%|calc\(\s*\d+%\s*(?:\+|-)\s*\d+%\s*\))' . $comma . // lightness.
			'(\d+%|calc\(\s*\d+%\s*(?:\+|-)\s*\d+%\s*\))' . // alpha.
			'\s*\)/i', // end of hsla.
			$content,
			$matches
		);

		if ( ! isset( $matches [4], $matches [0][0] ) ) {
			return $content;
		}

		$to_search_calc_array  = [];
		$to_replace_calc_array = [];
		foreach ( $matches[0] as $index => $full_hsla_match ) {
			if ( strpos( $full_hsla_match, 'calc' ) === false ) {
				continue;
			}

			$hue        = $this->calculate_css_calc( $matches[1][ $index ], 'is_hue' );
			$saturation = $this->calculate_css_calc( $matches[2][ $index ] );
			$lightness  = $this->calculate_css_calc( $matches[3][ $index ] );
			$alpha      = $this->calculate_css_calc( $matches[4][ $index ] );

			$final_value = 'hsla(' . $hue . ',' . $saturation . ',' . $lightness . ',' . $alpha . ')';

			array_push( $to_search_calc_array, $full_hsla_match );
			array_push( $to_replace_calc_array, Fusion_Color::new_color( $final_value )->to_css( 'rgba' ) );
		}

		return str_replace( $to_search_calc_array, $to_replace_calc_array, $content );
	}

	/**
	 * Calculate a css "calc" function after the global is replaced with a static value.
	 *
	 * @param string $expression The expression to determine the value.
	 * @param string $type 'is_percent' when calculating between 2 percentages, 'is_hue' when calc hue.
	 * @return string
	 */
	private function calculate_css_calc( $expression, $type = 'is_percent' ) {
		if ( strpos( $expression, 'calc' ) === false ) {
			return $expression;
		}

		$matches      = [];
		$return_value = '';
		preg_match( '/calc\s*\(\s*(\d+%?)\s*(\+|-)\s*(\d+%?)\s*\)/', $expression, $matches );

		if ( ! count( $matches ) ) {
			return $expression;
		}

		$first   = floatval( $matches[1] );
		$operand = $matches[2];
		$second  = floatval( $matches[3] );

		if ( '+' === $operand ) {
			$return_value = $first + $second;
		} else {
			$return_value = $first - $second;
		}

		if ( 'is_hue' === $type ) {
			$return_value = round( $return_value, 2 ) % 360;
			if ( $return_value < 0 ) {
				$return_value = 360 - $return_value;
			}
			$return_value = (string) $return_value;
		} else { // 'is_percent'(default) is_percent refers to hsla value percents, so it is between 0 and 100.
			$return_value = max( min( 100, $return_value ), 0 );
			$return_value = round( $return_value, 2 );
			$return_value = $return_value . '%';
		}

		return $return_value;
	}

	/**
	 * Gets colors overwrite pallete.
	 *
	 * @access public
	 * @since 3.7
	 * @param array $post_data The current post data.
	 * @return array
	 */
	public function get_overwrite_palette( $post_data ) {
		$overwrite_palette = [];

		if ( empty( $this->import_options['type'] ) ) {
			return $overwrite_palette;
		}

		if ( 'replace-pos' === $this->import_options['type'] ) {
			$overwrite_palette = $this->get_overwrite_colors_from_pos( $post_data, $this->import_options['invert'] );
		} elseif ( 'inherit' === $this->import_options['type'] && 'do-invert' === $this->import_options['invert'] ) {
			$overwrite_palette = [
				'--awb-color1'                    => '--awo-color1',
				'--awb-color2'                    => '--awo-color2',
				'--awb-color3'                    => '--awo-color3',
				'--awb-color4'                    => '--awo-color4',
				'--awb-color5'                    => '--awo-color5',
				'--awb-color6'                    => '--awo-color6',
				'--awb-color7'                    => '--awo-color7',
				'--awb-color8'                    => '--awo-color8',
				'--awo-color1'                    => '--awb-color8',
				'--awo-color2'                    => '--awb-color7',
				'--awo-color3'                    => '--awb-color6',
				'--awo-color4'                    => '--awb-color5',
				'--awo-color5'                    => '--awb-color4',
				'--awo-color6'                    => '--awb-color3',
				'--awo-color7'                    => '--awb-color2',
				'--awo-color8'                    => '--awb-color1',

				// Flip lightness manipulation from hsla.
				'-l) -'                           => '-x) -',
				'-l) +'                           => '-l) -',
				'-x) -'                           => '-l) +',

				// Flip blend mode on container.
				'background_blend_mode="lighten'  => 'xackground_blend_mode="lighten',
				'background_blend_mode="multiply' => 'background_blend_mode="lighten',
				'xackground_blend_mode="lighten'  => 'background_blend_mode="multiply',
			];
		}

		return $overwrite_palette;
	}

	/**
	 * Gets typography overwrite pallete.
	 *
	 * @access public
	 * @since 3.7
	 * @param array $post_data The current post data.
	 * @return array
	 */
	public function get_overwrite_typography( $post_data ) {
		$overwrite_typography = [];

		if ( 'replace-pos' === $this->import_options['type'] ) {
			$overwrite_typography = $this->get_overwrite_typography_from_pos( $post_data );
		}

		return $overwrite_typography;
	}

	/**
	 * Gets overwrite pallete from POs meta.
	 *
	 * @access public
	 * @since 3.7
	 * @param array  $post_data The current post data.
	 * @param string $invert    If should invert or not.
	 * @return array
	 */
	public function get_overwrite_colors_from_pos( $post_data, $invert ) {
		$new_colors = [];
		if ( isset( $post_data['post_meta']['_fusion'] ) && '' !== $post_data['post_meta']['_fusion'] && is_array( $post_data['post_meta']['_fusion'] ) ) {
			$fusion_meta = $post_data['post_meta']['_fusion'];

			// If invert is selected.
			if ( 'do-invert' === $invert ) {
				for ( $start = 1, $end = 8; $start <= 8; $start++, $end-- ) {
					if ( isset( $fusion_meta[ 'color' . $end . '_overwrite' ] ) ) {

						if ( class_exists( 'Fusion_Color' ) ) {
							$color_object = Fusion_Color::new_color( $fusion_meta[ 'color' . $end . '_overwrite' ] );

							// HSLA.
							$new_colors[ 'var(--awb-color' . $start . '-h)' ] = $color_object->hue;
							$new_colors[ 'var(--awb-color' . $start . '-s)' ] = $color_object->saturation . '%';
							$new_colors[ 'var(--awb-color' . $start . '-l)' ] = $color_object->lightness . '%';
							$new_colors[ 'var(--awb-color' . $start . '-a)' ] = ( $color_object->alpha * 100 ) . '%';
						}

						$new_colors[ 'var(--awb-color' . $start . ')' ] = $fusion_meta[ 'color' . $end . '_overwrite' ];
					}
				}
			} else {
				for ( $i = 1; $i <= 8; $i++ ) {
					if ( isset( $fusion_meta[ 'color' . $i . '_overwrite' ] ) ) {

						if ( class_exists( 'Fusion_Color' ) ) {
							$color_object = Fusion_Color::new_color( $fusion_meta[ 'color' . $i . '_overwrite' ] );

							// HSLA.
							$new_colors[ 'var(--awb-color' . $i . '-h)' ] = $color_object->hue;
							$new_colors[ 'var(--awb-color' . $i . '-s)' ] = $color_object->saturation . '%';
							$new_colors[ 'var(--awb-color' . $i . '-l)' ] = $color_object->lightness . '%';
							$new_colors[ 'var(--awb-color' . $i . '-a)' ] = ( $color_object->alpha * 100 ) . '%';
						}

						$new_colors[ 'var(--awb-color' . $i . ')' ] = $fusion_meta[ 'color' . $i . '_overwrite' ];
					}
				}
			}
		}

		return $new_colors;
	}

	/**
	 * Gets overwrite typography pallete from POs meta.
	 *
	 * @access public
	 * @since 3.7
	 * @param array $post_data The current post data.
	 * @return array
	 */
	public function get_overwrite_typography_from_pos( $post_data ) {
		$new_typography = [];
		if ( isset( $post_data['post_meta']['_fusion'] ) && '' !== $post_data['post_meta']['_fusion'] && is_array( $post_data['post_meta']['_fusion'] ) ) {
			$fusion_meta = $post_data['post_meta']['_fusion'];
			for ( $i = 1; $i <= 8; $i++ ) {
				if ( isset( $fusion_meta[ 'typography' . $i . '_overwrite' ] ) ) {
					$typography         = $fusion_meta[ 'typography' . $i . '_overwrite' ];
					$font_family_is_set = isset( $typography['font-family'] ) && '' !== $typography['font-family'];

					// Font family.
					if ( $font_family_is_set ) {
						$new_typography[ 'var(--awb-typography' . $i . '-font-family)' ] = $typography['font-family'];
					}

					// Font weight.
					if ( isset( $typography['font-weight'] ) && '' !== $typography['font-weight'] ) {
						$new_typography[ 'var(--awb-typography' . $i . '-font-weight)' ] = $typography['font-weight'];
					}

					// Font style.
					if ( isset( $typography['font-style'] ) && $font_family_is_set ) {
						$style = $typography['font-style'];
						if ( ! $style ) {
							$style = 'normal';
						}

						$new_typography[ 'var(--awb-typography' . $i . '-font-style)' ] = $style;
					}

					// Font size.
					if ( isset( $typography['font-size'] ) && '' !== $typography['font-size'] ) {
							$new_typography[ 'var(--awb-typography' . $i . '-font-size)' ] = $typography['font-size'];
					}

					// Line height.
					if ( isset( $typography['line-height'] ) && '' !== $typography['line-height'] ) {
						$new_typography[ 'var(--awb-typography' . $i . '-line-height)' ] = $typography['line-height'];
					}

					// Letter Spacing.
					if ( isset( $typography['letter-spacing'] ) && '' !== $typography['letter-spacing'] ) {
						$new_typography[ 'var(--awb-typography' . $i . '-letter-spacing)' ] = $typography['letter-spacing'];
					}

					// Text transform.
					if ( isset( $typography['text-transform'] ) && '' !== $typography['text-transform'] ) {
						$new_typography[ 'var(--awb-typography' . $i . '-text-transform)' ] = $typography['text-transform'];
					}

					// Font variant.
					if ( isset( $typography['variant'] ) && '' !== $typography['variant'] ) {
						$new_typography[ 'var(--awb-typography' . $i . ')' ] = $typography['variant'];
					}
				}
			}
		}

		return $new_typography;
	}

	/**
	 * Is string JSON?
	 *
	 * @access public
	 * @since 3.7
	 * @param string $string The string.
	 * @return boolean
	 */
	public function is_json( $string ) {

		if ( is_array( $string ) ) {
			return false;
		}

		json_decode( $string );
		return JSON_ERROR_NONE === json_last_error();
	}

	/**
	 * Imports studio post's media and updates post content if needed.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $post_content Post Content.
	 * @param array  $avada_media Avada Media array.
	 * @param array  $overwrite   Colors and typography overwrite mapping array.
	 * @return void
	 */
	public function import_post_media( $post_id, $post_content, $avada_media, $overwrite = [] ) {

		// Check content and import necessary assets from studio site.
		$processed_post_data = $this->process_studio_content(
			[
				'post_id'      => $post_id,
				'post_content' => $post_content,
				'avada_media'  => $avada_media,
				'overwrite'    => $overwrite,
			]
		);

		// Update post content if it was changed.
		if ( $processed_post_data['post_content'] !== $post_content ) {
			wp_update_post(
				[
					'ID'           => $post_id,
					'post_content' => $processed_post_data['post_content'],
				]
			);
		}
	}

	/**
	 * Imports icons package.
	 *
	 * @access public
	 * @since 3.5
	 * @param array $post_details Post details array, returned from import_post function.
	 * @return mixed null|array
	 */
	public function import_icons_package( $post_details ) {

		// Post was already imported (and package processed) or something went wrong, either case zip package can't be imported.
		if ( ! $post_details['post_id'] || ! isset( $post_details['was_imported'] ) || true === $post_details['was_imported'] || ! isset( $post_details['data']['package_url'] ) ) {
			return $post_details;
		}

		// Fetch zip icon package and process it.
		$imported_post_id = $post_details['post_id'];

		// ZIP package URL.
		$package_url = $post_details['data']['package_url'];

		// Fetch icon package and add it to Media Library.
		$file_array         = [];
		$file_array['name'] = wp_basename( $package_url );

		// Download file to temp location.
		$file_array['tmp_name'] = download_url( $package_url );

		if ( is_wp_error( $file_array['tmp_name'] ) ) {
			return;
		}

		$attachment_data = [
			'post_title' => $file_array['name'],
		];

		$overrides['test_form'] = false;
		$file_data              = wp_handle_sideload( $file_array, $overrides );

		if ( ! isset( $file_data['file'] ) ) {
			return;
		}

		$attachment_id = wp_insert_attachment( $attachment_data, $file_data['file'], $imported_post_id );

		// Flag imported zip package as studio media.
		$this->add_media_meta( $attachment_id, $package_url );

		// Set necessary post meta if attachment ID is passed.
		$icon_set_meta = [
			'attachment_id' => $attachment_id,
		];

		fusion_data()->post_meta( $imported_post_id )->set( 'custom_icon_set', $icon_set_meta );

		// (Re)generate icon files.
		Fusion_Custom_Icon_Set::get_instance()->regenerate_icon_files( $imported_post_id );

		// WIP: begin.
		$meta = fusion_data()->post_meta( $post_details['post_id'] )->get( 'custom_icon_set' );

		if ( '' !== $meta ) {
			$post_details['custom_icons']              = $meta;
			$post_details['custom_icons']['name']      = get_the_title( $post_details['post_id'] );
			$post_details['custom_icons']['post_id']   = $post_details['post_id'];
			$post_details['custom_icons']['css_url']   = fusion_get_custom_icons_css_url( $post_details['post_id'] );
			$post_details['custom_icons']['post_name'] = get_post_field( 'post_name', $post_details['post_id'] );
		}
		// WIP: end.

		return $post_details;
	}

	/**
	 * Import a video to compliment content.
	 *
	 * @access public
	 * @since 3.5©
	 * @param string $video_url The video URL.
	 * @return mixed
	 */
	public function import_video( $video_url ) {

		$existing_video = $this->find_existing_media( $video_url );

		if ( $existing_video ) {
			$new_video_url = $existing_video;
		} else {
			$new_video_url      = '';
			$file_array         = [];
			$file_array['name'] = wp_basename( $video_url );

			// Download file to temp location.
			$file_array['tmp_name'] = download_url( $video_url );

			if ( is_wp_error( $file_array['tmp_name'] ) ) {
				return;
			}

			if ( ! function_exists( 'media_handle_sideload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/media.php';

				// Needed for wp_read_image_metadata().
				require_once ABSPATH . 'wp-admin/includes/image.php';
			}

			$post_data = [
				'post_title' => $file_array['name'],
			];

			$attachment_id = media_handle_sideload( $file_array, 0, '', $post_data );

			if ( ! is_wp_error( $attachment_id ) ) {
				$new_video_url = wp_get_attachment_url( $attachment_id );
				$this->add_media_meta( $attachment_id, $new_video_url );
			}

			// Remove tmp file.
			if ( file_exists( $file_array['tmp_name'] ) ) {
				unlink( $file_array['tmp_name'] );
			}
		}

		return $new_video_url;
	}

	/**
	 * Ajax callback, used to import Studio media (for example from builder screen).
	 */
	public function ajax_import_media() {

		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

		$post_data        = isset( $_POST['data']['postData'] ) ? wp_unslash( $_POST['data']['postData'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$media_import_key = isset( $_POST['data']['mediaImportKey'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['mediaImportKey'] ) ) : '';
		$mapping          = isset( $_POST['data']['postData']['mapping'] ) ? $_POST['data']['postData']['mapping'] : []; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput

		// Set import options from $_REQUEST global array.
		AWB_Studio_Import()->set_import_options_from_request();

		if ( $media_import_key ) {
			$layout = $this->process_studio_content(
				[
					'post_id'      => $post_data['post_id'],
					'post_content' => $post_data['post_content'],
					'avada_media'  => [ $media_import_key => $post_data['avada_media'][ $media_import_key ] ],
					'overwrite'    => $mapping,
				]
			);

			$post_data['post_content'] = $layout['post_content'];
		}

		if ( isset( $layout['custom_icons'] ) ) {
			$post_data['custom_icons'] = $layout['custom_icons'];
		}

		echo wp_json_encode( $post_data );
		die();
	}

	/**
	 * Get post content.
	 *
	 * @access public
	 * @since 3.7
	 * @param array  $id  Studio post ID.
	 * @param string $type  Studio post type.
	 * @return mixed
	 */
	public function get_post_content( $id, $type ) {
		$response = wp_remote_get( $this->studio_url . '/wp-json/wp/v2/' . $type . '/' . $id . '/' );

		// TODO: better error handling.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$post_data = apply_filters( 'awb_studio_post_data', json_decode( wp_remote_retrieve_body( $response ), true ), $id );
		return isset( $post_data['post_content'] ) ? $post_data['post_content'] : $post_data['content']['raw'];
	}
}

/**
 * Instantiates the AWB_Studio_Import class.
 * Make sure the class is properly set-up.
 *
 * @since object 3.0
 * @return object AWB_Studio_Import
 */
function AWB_Studio_Import() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Studio_Import::get_instance();
}
AWB_Studio_Import();

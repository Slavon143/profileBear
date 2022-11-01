<?php
/**
 * Handles google maps in Avada.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      3.8.5
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handles google maps in Avada.
 */
class Avada_GoogleMap {

	/**
	 * The Map ID.
	 *
	 * @access private
	 * @var string
	 */
	private $map_id;

	/**
	 * Arguments array.
	 *
	 * @static
	 * @access public
	 * @var array
	 */
	public static $args;

	/**
	 * Initiate the shortcode
	 */
	public function __construct() {

		add_filter( 'fusion_attr_avada-google-map', [ $this, 'attr' ] );
		add_action( 'wp_ajax_fusion_cache_map', [ $this, 'fusion_cache_map' ] );
		add_action( 'wp_ajax_nopriv_fusion_cache_map', [ $this, 'fusion_cache_map' ] );
		add_action( 'avada_after_page_title_bar', [ $this, 'before_main_container' ] );
	}

	/**
	 * Function to get the default shortcode param values applied.
	 *
	 * @param  array $defaults  Array with user set param values.
	 * @param  array $args      Array with user set param values.
	 * @return array
	 */
	public static function set_shortcode_defaults( $defaults, $args ) {

		if ( empty( $args ) || ! is_array( $args ) ) {
			$args = [];
		}

		$args = shortcode_atts( $defaults, $args );

		foreach ( $args as $key => $value ) {
			if ( '' === $value ) {
				$args[ $key ] = $defaults[ $key ];
			}
		}

		return $args;

	}

	/**
	 * Function to apply attributes to HTML tags.
	 * Devs can override attributes in a child theme by using the correct slug
	 *
	 * @param  string $slug    Slug to refer to the HTML tag.
	 * @param  array  $attributes Attributes for HTML tag.
	 * @return string
	 */
	public static function attributes( $slug, $attributes = [] ) {

		$out  = '';
		$attr = apply_filters( "fusion_attr_{$slug}", $attributes );

		if ( empty( $attr ) ) {
			$attr['class'] = $slug;
		}

		foreach ( $attr as $name => $value ) {
			if ( empty( $value ) ) {
				$out .= ' ' . esc_html( $name );
			} else {
				$out .= ' ' . esc_html( $name ) . '="' . esc_attr( $value ) . '"';
			}
		}

		return trim( $out );

	}

	/**
	 * JS API render method.
	 *
	 * @access public
	 * @since 5.6
	 * @return string The needed map data.
	 */
	public function use_js_api() {

		extract( self::$args );

		$html = '';

		if ( $address ) {
			$addresses       = explode( '|', $address );
			$infobox_content = ( ! in_array( $map_style, [ 'default', 'theme' ], true ) ) ? html_entity_decode( $infobox_content ) : '';

			$infobox_content_array = ( $infobox_content ) ? explode( '|', $infobox_content ) : '';
			$icon_array            = ( $icon && 'default' !== $infobox ) ? explode( '|', $icon ) : '';

			if ( ! empty( $addresses ) ) {
				self::$args['address'] = $addresses;
			}

			$num_of_addresses = count( $addresses );

			if ( $icon && false === strpos( $icon, '|' ) && 'default' !== $infobox ) {
				for ( $i = 0; $i < $num_of_addresses; $i++ ) {
					$icon_array[ $i ] = $icon;
				}
			}

			if ( 'theme' === $map_style ) {

				$map_style                = 'custom';
				$icon                     = 'theme';
				$animation                = 'yes';
				$infobox                  = 'custom';
				$color_obj                = Fusion_Color::new_color( Avada()->settings->get( 'primary_color' ) );
				$infobox_background_color = 'rgba(' . $color_obj->red . ', ' . $color_obj->green . ', ' . $color_obj->blue . ', 0.8)';
				$overlay_color            = $color_obj->color;
				$brightness_level         = $color_obj->brightness['level'];
				$infobox_text_color       = ( $brightness_level > 140 ) ? '#fff' : '#747474';

			} elseif ( 'custom' === $map_style ) {
				$overlay_color = Avada()->settings->get( 'map_overlay_color' );
				$color_obj     = Fusion_Color::new_color( $overlay_color );
				if ( 0 === $color_obj->alpha ) {
					$overlay_color = '';
				} elseif ( 1 > $color_obj->alpha ) {
					$overlay_color = $color_obj->get_new( 'lightness', $color_obj->lightness + absint( 100 * ( 1 - $color_obj->alpha ) ) )->to_css( 'hex' );
				}
			}

			if ( 'theme' === $icon && 'custom' === $map_style ) {
				for ( $i = 0; $i < $num_of_addresses; $i++ ) {
					$icon_array[ $i ] = Avada::$template_dir_url . '/assets/images/avada_map_marker.png';
				}
			}

			if ( wp_script_is( 'google-maps-api', 'registered' ) ) {
				wp_print_scripts( 'google-maps-api' );
				if ( wp_script_is( 'google-maps-infobox', 'registered' ) ) {
					wp_print_scripts( 'google-maps-infobox' );
				}
			}

			foreach ( self::$args['address'] as $add ) {
				$add     = trim( $add );
				$add_arr = explode( "\n", $add );
				$add_arr = array_filter( $add_arr, 'trim' );
				$add     = implode( '<br/>', $add_arr );
				$add     = str_replace( "\r", '', $add );
				$add     = str_replace( "\n", '', $add );

				$coordinates[]['address'] = $add;
			}

			if ( ! is_array( $coordinates ) ) {
				return;
			}

			for ( $i = 0; $i < $num_of_addresses; $i++ ) {
				if ( 0 === strpos( self::$args['address'][ $i ], 'latlng=' ) ) {
					self::$args['address'][ $i ] = $coordinates[ $i ]['address'];
				}
			}

			if ( is_array( $infobox_content_array ) && ! empty( $infobox_content_array ) ) {
				for ( $i = 0; $i < $num_of_addresses; $i++ ) {
					if ( ! array_key_exists( $i, $infobox_content_array ) ) {
						$infobox_content_array[ $i ] = self::$args['address'][ $i ];
					}
				}
				self::$args['infobox_content'] = $infobox_content_array;
			} else {
				self::$args['infobox_content'] = self::$args['address'];
			}

			$cached_addresses = get_option( 'fusion_map_addresses' );

			foreach ( self::$args['address'] as $key => $address ) {
				$json_addresses[] = [
					'address'         => $address,
					'infobox_content' => self::$args['infobox_content'][ $key ],
				];

				if ( isset( $icon_array ) && is_array( $icon_array ) ) {
					$json_addresses[ $key ]['marker'] = $icon_array[ $key ];
				}

				if ( false !== strpos( $address, strtolower( 'latlng=' ) ) ) {
					$json_addresses[ $key ]['address']     = str_replace( 'latlng=', '', $address );
					$lat_lng                               = explode( ',', $json_addresses[ $key ]['address'] );
					$json_addresses[ $key ]['coordinates'] = true;
					$json_addresses[ $key ]['latitude']    = $lat_lng[0];
					$json_addresses[ $key ]['longitude']   = $lat_lng[1];
					$json_addresses[ $key ]['cache']       = false;

					if ( false !== strpos( self::$args['infobox_content'][ $key ], strtolower( 'latlng=' ) ) ) {
						$json_addresses[ $key ]['infobox_content'] = '';
					}

					if ( isset( $cached_addresses[ trim( $json_addresses[ $key ]['latitude'] . ',' . $json_addresses[ $key ]['longitude'] ) ] ) ) {
						$json_addresses[ $key ]['geocoded_address'] = $cached_addresses[ trim( $json_addresses[ $key ]['latitude'] . ',' . $json_addresses[ $key ]['longitude'] ) ]['address'];
						$json_addresses[ $key ]['cache']            = true;
					}
				} else {
					$json_addresses[ $key ]['coordinates'] = false;
					$json_addresses[ $key ]['cache']       = false;

					if ( isset( $cached_addresses[ trim( $json_addresses[ $key ]['address'] ) ] ) ) {
						$json_addresses[ $key ]['latitude']  = $cached_addresses[ trim( $json_addresses[ $key ]['address'] ) ]['latitude'];
						$json_addresses[ $key ]['longitude'] = $cached_addresses[ trim( $json_addresses[ $key ]['address'] ) ]['longitude'];
						$json_addresses[ $key ]['cache']     = true;
					}
				}
			}

			$json_addresses = wp_json_encode( $json_addresses );

			$map_id       = uniqid( 'fusion_map_' ); // Generate a unique ID for this map.
			$this->map_id = $map_id;
			ob_start(); ?>
			<script type="text/javascript">
				var map_<?php echo esc_attr( $map_id ); ?>;
				var markers = [];
				var counter = 0;
				var fusionMapNonce = '<?php echo wp_create_nonce( 'avada_admin_ajax' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>';
				function fusion_run_map_<?php echo esc_attr( $map_id ); ?>() {
					jQuery('#<?php echo esc_attr( $map_id ); ?>').fusion_maps({
						addresses: <?php echo $json_addresses; // phpcs:ignore WordPress.Security.EscapeOutput ?>,
						address_pin: <?php echo ( 'yes' === $address_pin ) ? 'true' : 'false'; ?>,
						animations: <?php echo ( 'yes' === $animation ) ? 'true' : 'false'; ?>,
						infobox_background_color: '<?php echo esc_attr( $infobox_background_color ); ?>',
						infobox_styling: '<?php echo esc_attr( $infobox ); ?>',
						infobox_text_color: '<?php echo esc_attr( $infobox_text_color ); ?>',
						map_style: '<?php echo esc_attr( $map_style ); ?>',
						map_type: '<?php echo esc_attr( $type ); ?>',
						marker_icon: '<?php echo esc_attr( $icon ); ?>',
						overlay_color: '<?php echo esc_attr( $overlay_color ); ?>',
						overlay_color_hsl: <?php echo wp_json_encode( Fusion_Color::new_color( $overlay_color )->to_hsl() ); ?>,
						pan_control: <?php echo ( 'yes' === $zoom_pancontrol ) ? 'true' : 'false'; ?>,
						show_address: <?php echo ( 'yes' === $popup ) ? 'true' : 'false'; ?>,
						scale_control: <?php echo ( 'yes' === $scale ) ? 'true' : 'false'; ?>,
						scrollwheel: <?php echo ( 'yes' === $scrollwheel ) ? 'true' : 'false'; ?>,
						zoom: <?php echo esc_attr( $zoom ); ?>,
						zoom_control: <?php echo ( 'yes' === $zoom_pancontrol ) ? 'true' : 'false'; ?>,
					});
				}

				google.maps.event.addDomListener(window, 'load', fusion_run_map_<?php echo esc_attr( $map_id ); ?>);
			</script>
			<?php
			if ( self::$args['id'] ) {
				$html = ob_get_clean() . '<div id="' . self::$args['id'] . '"><div ' . $this->attributes( 'avada-google-map' ) . '></div></div>';
			} else {
				$html = ob_get_clean() . '<div ' . $this->attributes( 'avada-google-map' ) . '></div>';
			}
		}

		Fusion_Dynamic_JS::enqueue_script(
			'fusion-google-map',
			FUSION_LIBRARY_URL . '/assets/min/js/general/fusion-google-map.js',
			FUSION_LIBRARY_PATH . '/assets/min/js/general/fusion-google-map.js',
			[ 'jquery-fusion-maps' ],
			'1',
			true
		);

		return $html;
	}

	/**
	 * Embed API render method.
	 *
	 * @access public
	 * @since 5.6
	 * @return string The needed map data.
	 */
	public function use_embed_api() {
		$html          = '';
		$api_key       = apply_filters( 'fusion_google_maps_api_key', Avada()->settings->get( 'gmap_api' ) );
		$embed_address = str_replace( ' ', '+', self::$args['embed_address'] );
		$lang_code     = fusion_get_google_maps_language_code();

		$html .= '<iframe width="' . self::$args['width'] . '" height="' . self::$args['height'] . '" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?key=' . $api_key . '&language=' . $lang_code . '&q=' . $embed_address . '&maptype=' . self::$args['embed_map_type'] . '&zoom=' . self::$args['zoom'] . '" allowfullscreen></iframe>';

		$html = '<div ' . $this->attributes( 'avada-google-map' ) . '>' . $html . '</div>';

		return $html;
	}

	/**
	 * Render the shortcode.
	 *
	 * @access public
	 * @param  array  $args    Shortcode parameters.
	 * @param  string $content Content between shortcode.
	 * @return string          HTML output.
	 */
	public function render_map( $args, $content = '' ) {

		if ( ! Avada()->settings->get( 'status_gmap' ) ) {
			return '';
		}

		$defaults = $this->set_shortcode_defaults(
			[
				'api_type'                 => 'js',
				'embed_address'            => '',
				'embed_map_type'           => '',
				'class'                    => '',
				'id'                       => '',
				'animation'                => 'no',
				'address'                  => '',
				'address_pin'              => 'yes',
				'height'                   => '300px',
				'icon'                     => '',
				'infobox'                  => '',
				'infobox_background_color' => '',
				'infobox_content'          => '',
				'infobox_text_color'       => '',
				'map_style'                => '',
				'overlay_color'            => '',
				'popup'                    => 'yes',
				'scale'                    => 'yes',
				'scrollwheel'              => 'yes',
				'type'                     => 'roadmap',
				'width'                    => '100%',
				'zoom'                     => '14',
				'zoom_pancontrol'          => 'yes',
			],
			$args
		);

		self::$args = $defaults;

		if ( 'js' === self::$args['api_type'] ) {
			$html = $this->use_js_api();
			$html = apply_filters( 'privacy_script_embed', $html, 'gmaps', true, self::$args['width'], self::$args['height'] );
		} else {
			$html = $this->use_embed_api();
			$html = apply_filters( 'privacy_iframe_embed', $html );
		}

		return $html;

	}

	/**
	 * Modifies attributes.
	 *
	 * @access  public
	 * @return array
	 */
	public function attr() {

		$attr['class'] = 'shortcode-map fusion-google-map avada-google-map';

		if ( self::$args['class'] ) {
			$attr['class'] .= ' ' . self::$args['class'];
		}

		if ( 'embed' === self::$args['api_type'] ) {
			$attr['class'] .= ' fusion-maps-embed-type';
		}

		$attr['id'] = $this->map_id;

		$attr['style'] = 'height:' . self::$args['height'] . ';width:' . self::$args['width'] . ';';

		return $attr;

	}
	/**
	 * Caches google maps.
	 *
	 * @access  public
	 * @return null
	 */
	public function fusion_cache_map() {

		check_ajax_referer( 'avada_admin_ajax', 'security' );

		// Check that the user has the right permissions.
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		$addresses_to_cache = get_option( 'fusion_map_addresses' );
		$post_addresses     = isset( $_POST['addresses'] ) ? wp_unslash( $_POST['addresses'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		foreach ( $post_addresses as $address ) {

			if ( isset( $address['latitude'] ) && isset( $address['longitude'] ) ) {
				$addresses_to_cache[ trim( $address['address'] ) ] = [
					'address'   => trim( $address['address'] ),
					'latitude'  => esc_attr( $address['latitude'] ),
					'longitude' => esc_attr( $address['longitude'] ),
				];

				if ( isset( $address['geocoded_address'] ) && $address['geocoded_address'] ) {
					$addresses_to_cache[ trim( $address['address'] ) ]['address'] = $address['geocoded_address'];
				}
			}
		}
		update_option( 'fusion_map_addresses', $addresses_to_cache );

		wp_die();

	}

	/**
	 * Adds a map before the content.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function before_main_container() {

		if ( is_page_template( 'contact.php' ) && Avada()->settings->get( 'recaptcha_public' ) && Avada()->settings->get( 'recaptcha_private' ) ) {
			echo '<script type="text/javascript">var RecaptchaOptions = { theme : \'' . esc_attr( Avada()->settings->get( 'recaptcha_color_scheme' ) ) . '\' };</script>';
		}

		$is_address_set = ( 'js' === Avada()->settings->get( 'gmap_api_type' ) && Avada()->settings->get( 'gmap_address' ) ) || ( 'embed' === Avada()->settings->get( 'gmap_api_type' ) && Avada()->settings->get( 'gmap_embed_address' ) );

		if ( is_page_template( 'contact.php' ) && Avada()->settings->get( 'status_gmap' ) && $is_address_set ) {

			$map_args = [
				'api_type'                 => esc_html( Avada()->settings->get( 'gmap_api_type' ) ),
				'embed_address'            => esc_html( Avada()->settings->get( 'gmap_embed_address' ) ),
				'embed_map_type'           => esc_html( Avada()->settings->get( 'gmap_embed_map_type' ) ),
				'address'                  => esc_html( Avada()->settings->get( 'gmap_address' ) ),
				'type'                     => esc_attr( Avada()->settings->get( 'gmap_type' ) ),
				'address_pin'              => ( Avada()->settings->get( 'map_pin' ) ) ? 'yes' : 'no',
				'animation'                => ( Avada()->settings->get( 'gmap_pin_animation' ) ) ? 'yes' : 'no',
				'map_style'                => esc_attr( Avada()->settings->get( 'map_styling' ) ),
				'overlay_color'            => esc_attr( Avada()->settings->get( 'map_overlay_color' ) ),
				'infobox'                  => esc_attr( Avada()->settings->get( 'map_infobox_styling' ) ),
				'infobox_background_color' => esc_attr( Avada()->settings->get( 'map_infobox_bg_color' ) ),
				'infobox_text_color'       => esc_attr( Avada()->settings->get( 'map_infobox_text_color' ) ),
				'infobox_content'          => htmlentities( Avada()->settings->get( 'map_infobox_content' ) ),
				'icon'                     => esc_attr( Avada()->settings->get( 'map_custom_marker_icon' ) ),
				'width'                    => esc_attr( Avada()->settings->get( 'gmap_dimensions', 'width' ) ),
				'height'                   => esc_attr( Avada()->settings->get( 'gmap_dimensions', 'height' ) ),
				'zoom'                     => esc_attr( Avada()->settings->get( 'map_zoom_level' ) ),
				'scrollwheel'              => ( Avada()->settings->get( 'map_scrollwheel' ) ) ? 'yes' : 'no',
				'scale'                    => ( Avada()->settings->get( 'map_scale' ) ) ? 'yes' : 'no',
				'zoom_pancontrol'          => ( Avada()->settings->get( 'map_zoomcontrol' ) ) ? 'yes' : 'no',
				'popup'                    => ( ! Avada()->settings->get( 'map_popup' ) ) ? 'yes' : 'no',
			];

			echo '<div id="fusion-gmap-container">' . $this->render_map( $map_args ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}
}

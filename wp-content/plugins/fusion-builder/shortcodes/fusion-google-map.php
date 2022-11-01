<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_map' ) ) {

	if ( ! class_exists( 'FusionSC_GoogleMap' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_GoogleMap extends Fusion_Element {

			/**
			 * The Unique ID of this map.
			 *
			 * @access private
			 * @since 1.0
			 * @var string
			 */
			private $map_id;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * Whether the nonces script has already been added for the map.
			 *
			 * @static
			 * @access private
			 * @since 1.3
			 * @var bool
			 */
			private static $nonce_added = false;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_google-map-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_map', [ $this, 'render' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'api_type'                 => ( '' !== $fusion_settings->get( 'google_map_api_type' ) ) ? $fusion_settings->get( 'google_map_api_type' ) : 'js',
					'embed_address'            => '',
					'embed_map_type'           => '',
					'margin_top'               => '',
					'margin_bottom'            => '',
					'hide_on_mobile'           => fusion_builder_default_visibility( 'string' ),
					'class'                    => '',
					'id'                       => '',
					'animation'                => 'no',
					'address'                  => '',
					'height'                   => '300px',
					'icon'                     => '',
					'icon_static'              => '',
					'infobox'                  => '',
					'infobox_background_color' => '',
					'infobox_content'          => '',
					'infobox_text_color'       => '',
					'map_style'                => '',
					'overlay_color'            => '',
					'static_map_color'         => '',
					'popup'                    => 'yes',
					'scale'                    => 'yes',
					'scrollwheel'              => 'yes',
					'type'                     => 'roadmap',
					'width'                    => '100%',
					'zoom'                     => '14',
					'zoom_pancontrol'          => 'yes',
				];
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_params() {

				return [
					'google_map_api_type' => 'api_type',
				];
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_extras() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'primary_color'    => $fusion_settings->get( 'primary_color' ),
					'theme_icon'       => plugins_url( 'images/avada_map_marker.png', dirname( __FILE__ ) ),
					'cached_addresses' => get_option( 'fusion_map_addresses' ),
					'gmap_api'         => apply_filters( 'fusion_google_maps_api_key', $fusion_settings->get( 'gmap_api' ) ),
					'amms_icon'        => plugins_url( 'images/amms.png', dirname( __FILE__ ) ),
				];
			}

			/**
			 * Maps settings to extra variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_extras() {

				return [
					'primary_color' => 'primary_color',
					'gmap_api'      => 'gmap_api',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode paramters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$fusion_settings = awb_get_fusion_settings();

				if ( ! $fusion_settings->get( 'status_gmap' ) ) {
					return '';
				}

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_map' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_map', $args );

				$defaults['width']  = FusionBuilder::validate_shortcode_attr_value( $defaults['width'], 'px' );
				$defaults['height'] = FusionBuilder::validate_shortcode_attr_value( $defaults['height'], 'px' );

				$this->args = $defaults;

				if ( 'js' === $this->args['api_type'] ) {
					$html = $this->use_js_api();
					$html = apply_filters( 'privacy_script_embed', $html, 'gmaps', true, $this->args['width'], $this->args['height'] );
				} elseif ( 'static' === $this->args['api_type'] ) {
					$html = $this->use_static_api();
					$html = apply_filters( 'privacy_image_embed', $html, 'gmaps', true, $this->args['width'], $this->args['height'] );
				} else {
					$html = $this->use_embed_api();
					$html = apply_filters( 'privacy_iframe_embed', $html );
				}

				$this->on_render();

				return apply_filters( 'fusion_element_google_map_content', $html, $args );

			}

			/**
			 * Sets up the map data when using the embed API.
			 *
			 * @access public
			 * @since 1.6
			 * @return string The needed map data.
			 */
			public function use_embed_api() {
				$fusion_settings = awb_get_fusion_settings();

				$html          = '';
				$api_key       = apply_filters( 'fusion_google_maps_api_key', $fusion_settings->get( 'gmap_api' ) );
				$embed_address = str_replace( ' ', '+', $this->args['embed_address'] );
				$lang_code     = fusion_get_google_maps_language_code();

				$html .= '<iframe width="' . $this->args['width'] . '" height="' . $this->args['height'] . '" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?key=' . $api_key . '&language=' . $lang_code . '&q=' . $embed_address . '&maptype=' . $this->args['embed_map_type'] . '&zoom=' . $this->args['zoom'] . '" allowfullscreen></iframe>';
				$html  = fusion_library()->images->apply_global_selected_lazy_loading_to_iframe( $html );
				$html  = '<div ' . FusionBuilder::attributes( 'google-map-shortcode' ) . '>' . $html . '</div>';

				return $html;
			}

			/**
			 * Sets up the map data when using the static API.
			 *
			 * @access public
			 * @since 1.6.1
			 * @return string The needed map data.
			 */
			public function use_static_api() {
				$fusion_settings = awb_get_fusion_settings();

				$html = '';

				if ( $this->args['address'] ) {
					$api_key = apply_filters( 'fusion_google_maps_api_key', $fusion_settings->get( 'gmap_api' ) );
					$width   = (int) $this->args['width'];
					$height  = (int) $this->args['height'];
					$style   = '';

					$addresses_array = explode( '|', $this->args['address'] );
					$icon_array      = ( ! empty( $this->args['icon_static'] ) ) ? explode( '|', $this->args['icon_static'] ) : [];
					$markers         = [];
					$address_count   = count( $addresses_array );

					for ( $i = 0; $i < $address_count; $i++ ) {

						$addresses_array[ $i ] = trim( $addresses_array[ $i ] );
						if ( 0 === strpos( $addresses_array[ $i ], 'latlng=' ) ) {
							$addresses_array[ $i ] = str_replace( 'latlng=', '', $addresses_array[ $i ] );
						} else {
							$addresses_array[ $i ] = str_replace( [ ', ', ' ,', ' , ', ' ' ], [ ',', ',', ',', '+' ], $addresses_array[ $i ] );
						}

						if ( isset( $icon_array[ $i ] ) ) {
							if ( 'theme' === $icon_array[ $i ] ) {
								$icon_array[ $i ] = plugins_url( 'images/amms.png', dirname( __FILE__ ) );
							}

							$icon_array[ $i ] = trim( filter_var( $icon_array[ $i ], FILTER_VALIDATE_URL ) ? 'icon:' . $icon_array[ $i ] : $icon_array[ $i ] );

							// str_replace is used so we can pass other params too (for example color or label could be used instead).
							$icon_array[ $i ] = str_replace( ',', '|', $icon_array[ $i ] ) . '|';
						} else {
							$icon_array[ $i ] = '';
						}

						$markers[] = $icon_array[ $i ] . $addresses_array[ $i ];
					}

					if ( $this->args['static_map_color'] ) {
						$rgb          = str_replace( '#', '', $this->args['static_map_color'] );
						$color_object = Fusion_Color::new_color( $this->args['static_map_color'] );
						$saturation   = $color_object->saturation * 2 - 100;
						$lightness    = $color_object->lightness * 2 - 100;

						$style .= '&style=feature:all|hue:0x' . $rgb . '|saturation:' . $saturation . '|lightness:' . $lightness . '|visibility:simplified';
						$style .= '&style=feature:administrative|hue:0x' . $rgb . '|saturation:' . $saturation . '|lightness:' . $lightness . '|visibility:simplified';
						$style .= '&style=feature:landscape|hue:0x' . $rgb . '|saturation:' . $saturation . '|lightness:' . $lightness . '|visibility:simplified';
						$style .= '&style=feature:poi|hue:0x' . $rgb . '|saturation:' . $saturation . '|lightness:' . $lightness . '|visibility:simplified';
						$style .= '&style=feature:road|hue:0x' . $rgb . '|saturation:' . $saturation . '|lightness:' . $lightness . '|visibility:simplified';
						$style .= '&style=feature:transit|hue:0x' . $rgb . '|saturation:' . $saturation . '|lightness:' . $lightness . '|visibility:simplified';
						$style .= '&style=feature:water|hue:0x' . $rgb . '|saturation:' . $saturation . '|lightness:' . $lightness . '|visibility:simplified';
					}

					$lang_code = fusion_get_google_maps_language_code();

					$html .= '<img width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" src="' . esc_url( 'https://maps.googleapis.com/maps/api/staticmap?key=' . $api_key . '&language=' . $lang_code . '&center=' . $addresses_array[0] . '&maptype=' . $this->args['type'] . '&zoom=' . $this->args['zoom'] . '&size=' . $width . 'x' . $height . '&markers=' . implode( '&markers=', $markers ) ) . $style . '&scale=2">';

					$html = '<div ' . FusionBuilder::attributes( 'google-map-shortcode' ) . '>' . $html . '</div>';
				}

				return $html;
			}


			/**
			 * Sets up the map data when using the JS API.
			 *
			 * @access public
			 * @since 1.6
			 * @return string The needed map data.
			 */
			public function use_js_api() {
				$fusion_settings = awb_get_fusion_settings();

				extract( $this->args );

				$html = '';

				if ( $address ) {
					$addresses = explode( '|', $address );

					if ( $addresses ) {
						$this->args['address'] = $addresses;
					}

					$num_of_addresses = count( $addresses );

					$infobox_content = fusion_decode_if_needed( $infobox_content );

					$infobox_content_array = [];
					$infobox_content_array = ( ! in_array( $map_style, [ 'default', 'theme' ], true ) ) ? explode( '|', $infobox_content ) : [];

					$icon_array = [];
					if ( $icon ) {
						$icon_array = explode( '|', $icon );
					}

					if ( 'theme' === $map_style ) {
						$map_style                = 'custom';
						$icon                     = 'theme';
						$animation                = 'yes';
						$infobox                  = 'custom';
						$overlay_color            = $fusion_settings->get( 'primary_color' );
						$infobox_background_color = FusionBuilder::hex2rgb( $overlay_color );
						$infobox_background_color = 'rgba(' . $infobox_background_color[0] . ', ' . $infobox_background_color[1] . ', ' . $infobox_background_color[2] . ', 0.8)';
						$brightness_level         = Fusion_Color::new_color( $overlay_color )->brightness;

						$infobox_text_color = '#747474';
						if ( $brightness_level > 140 ) {
							$infobox_text_color = '#fff';
						}
					} elseif ( 'custom' === $map_style ) {
						if ( Fusion_Color::new_color( $overlay_color )->is_color_transparent() ) {
							$overlay_color = '';
						}
					}

					// If only one custom icon is set, use it for all markers.
					if ( 'custom' === $map_style && $icon && 'theme' !== $icon && $icon_array && 1 === count( $icon_array ) ) {
						$icon_url = $icon_array[0];
						for ( $i = 0; $i < $num_of_addresses; $i++ ) {
							$icon_array[ $i ] = $icon_url;
						}
					}

					if ( 'theme' === $icon && 'custom' === $map_style ) {
						for ( $i = 0; $i < $num_of_addresses; $i++ ) {
							$icon_array[ $i ] = plugins_url( 'images/avada_map_marker.png', dirname( __FILE__ ) );
						}
					}

					if ( wp_script_is( 'google-maps-api', 'registered' ) ) {
						wp_print_scripts( 'google-maps-api' );
						if ( wp_script_is( 'google-maps-infobox', 'registered' ) ) {
							wp_print_scripts( 'google-maps-infobox' );
						}
					}

					foreach ( $this->args['address'] as $add ) {

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
						if ( 0 === strpos( $this->args['address'][ $i ], 'latlng=' ) ) {
							$this->args['address'][ $i ] = $coordinates[ $i ]['address'];
						}
					}

					$this->args['infobox_content'] = $this->args['address'];
					if ( ! empty( $infobox_content_array ) ) {
						for ( $i = 0; $i < $num_of_addresses; $i++ ) {
							if ( ! array_key_exists( $i, $infobox_content_array ) ) {
								$infobox_content_array[ $i ] = $this->args['address'][ $i ];
							}
						}
						$this->args['infobox_content'] = $infobox_content_array;
					}

					$cached_addresses = get_option( 'fusion_map_addresses' );

					foreach ( $this->args['address'] as $key => $address ) {
						$json_addresses[] = [
							'address'         => $address,
							'infobox_content' => html_entity_decode( $this->args['infobox_content'][ $key ] ),
						];

						if ( isset( $icon_array ) && array_key_exists( $key, $icon_array ) ) {
							$json_addresses[ $key ]['marker'] = $icon_array[ $key ];
						}

						if ( false !== strpos( $address, strtolower( 'latlng=' ) ) ) {
							$json_addresses[ $key ]['address']     = str_replace( 'latlng=', '', $address );
							$lat_lng                               = explode( ',', $json_addresses[ $key ]['address'] );
							$json_addresses[ $key ]['coordinates'] = true;
							$json_addresses[ $key ]['latitude']    = $lat_lng[0];
							$json_addresses[ $key ]['longitude']   = $lat_lng[1];
							$json_addresses[ $key ]['cache']       = false;

							if ( false !== strpos( $this->args['infobox_content'][ $key ], strtolower( 'latlng=' ) ) ) {
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

					$map_id            = uniqid( 'fusion_map_' ); // Generate a unique ID for this map.
					$this->map_id      = $map_id;
					$overlay_color_hsl = [
						'hue' => Fusion_Color::new_color( $overlay_color )->hue,
						'sat' => Fusion_Color::new_color( $overlay_color )->saturation,
						'lum' => Fusion_Color::new_color( $overlay_color )->lightness,
					];

					ob_start(); ?>
					<script type="text/javascript">
						var map_<?php echo esc_html( $map_id ); ?>;
						var markers = [];
						var counter = 0;
						<?php if ( ! self::$nonce_added ) : ?>
							<?php self::$nonce_added = true; ?>
							var fusionMapNonce = '<?php echo wp_create_nonce( 'avada_admin_ajax' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>';
						<?php endif; ?>
						function fusion_run_map_<?php echo esc_html( $map_id ); ?>() {
							jQuery('#<?php echo esc_html( $map_id ); ?>').fusion_maps({
								addresses: <?php echo $json_addresses; // phpcs:ignore WordPress.Security.EscapeOutput ?>,
								animations: <?php echo ( 'yes' === $animation ) ? 'true' : 'false'; ?>,
								infobox_background_color: '<?php echo $infobox_background_color; // phpcs:ignore WordPress.Security.EscapeOutput ?>',
								infobox_styling: '<?php echo $infobox; // phpcs:ignore WordPress.Security.EscapeOutput ?>',
								infobox_text_color: '<?php echo $infobox_text_color; // phpcs:ignore WordPress.Security.EscapeOutput ?>',
								map_style: '<?php echo $map_style; // phpcs:ignore WordPress.Security.EscapeOutput ?>',
								map_type: '<?php echo $type; // phpcs:ignore WordPress.Security.EscapeOutput ?>',
								marker_icon: '<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput ?>',
								overlay_color: '<?php echo $overlay_color; // phpcs:ignore WordPress.Security.EscapeOutput ?>',
								overlay_color_hsl: <?php echo wp_json_encode( $overlay_color_hsl ); ?>,
								pan_control: <?php echo ( 'yes' === $zoom_pancontrol ) ? 'true' : 'false'; ?>,
								show_address: <?php echo ( 'yes' === $popup ) ? 'true' : 'false'; ?>,
								scale_control: <?php echo ( 'yes' === $scale ) ? 'true' : 'false'; ?>,
								scrollwheel: <?php echo ( 'yes' === $scrollwheel ) ? 'true' : 'false'; ?>,
								zoom: <?php echo $zoom; // phpcs:ignore WordPress.Security.EscapeOutput ?>,
								zoom_control: <?php echo ( 'yes' === $zoom_pancontrol ) ? 'true' : 'false'; ?>,
							});
						}

						google.maps.event.addDomListener(window, 'load', fusion_run_map_<?php echo esc_html( $map_id ); ?>);
					</script>
					<?php
					if ( $this->args['id'] ) {
						$html = ob_get_clean() . '<div id="' . $this->args['id'] . '"><div ' . FusionBuilder::attributes( 'google-map-shortcode' ) . '></div></div>';
					} else {
						$html = ob_get_clean() . '<div ' . FusionBuilder::attributes( 'google-map-shortcode' ) . '></div>';
					}
				}

				return $html;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'shortcode-map fusion-google-map',
						'style' => '',
					]
				);

				$attr['class'] .= ' fusion-maps-' . $this->args['api_type'] . '-type';

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( 'js' === $this->args['api_type'] ) {
					$attr['id'] = $this->map_id;
				} elseif ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( 'js' === $this->args['api_type'] ) {
					$attr['style'] = 'height:' . $this->args['height'] . ';width:' . $this->args['width'] . ';';
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				return $attr;

			}

			/**
			 * Gets the coordinates from an address.
			 *
			 * @access public
			 * @since 1.0
			 * @param string $address The address we want to geo-locate.
			 * @param bool   $force_refresh Whether we want to force-refresh the geolocating or not.
			 * @return string|array
			 */
			public function get_coordinates( $address, $force_refresh = false ) {
				$fusion_settings = awb_get_fusion_settings();

				$key          = $fusion_settings->get( 'google_console_api_key' );
				$data         = '';
				$address_hash = md5( $address );
				$coordinates  = get_transient( $address_hash );

				if ( $force_refresh || false === $coordinates ) {

					$args = [
						'address' => rawurlencode( $address ),
						'sensor'  => 'false',
					];
					if ( 0 === strpos( $address, 'latlng=' ) ) {
						$args = [
							'latlng' => rawurlencode( substr( $address, 7 ) ),
							'sensor' => 'false',
						];
					}

					$url = 'http://maps.googleapis.com/maps/api/geocode/json';
					if ( $key ) {
						$args['key'] = $key;
						$url         = 'https://maps.googleapis.com/maps/api/geocode/json';
					}
					$url      = esc_url_raw( add_query_arg( $args, $url ) );
					$response = wp_remote_get( $url );

					if ( is_wp_error( $response ) ) {
						return;
					}

					$data = wp_remote_retrieve_body( $response );

					if ( is_wp_error( $data ) ) {
						return;
					}

					if ( 200 === $response['response']['code'] || '200' === $response['response']['code'] ) {

						$data = json_decode( $data );

						if ( 'OK' === $data->status ) {

							$coordinates = $data->results[0]->geometry->location;

							$cache_value['lat']     = $coordinates->lat;
							$cache_value['lng']     = $coordinates->lng;
							$cache_value['address'] = (string) $data->results[0]->formatted_address;

							// Cache coordinates for 3 months.
							set_transient( $address_hash, $cache_value, 3600 * 24 * 30 * 3 );
							$data = $cache_value;

						} elseif ( 'ZERO_RESULTS' === $data->status ) {
							return esc_attr__( 'No location found for the entered address.', 'fusion-builder' );
						} elseif ( 'INVALID_REQUEST' === $data->status ) {
							return esc_attr__( 'Invalid request. Did you enter an address?', 'fusion-builder' );
						} else {
							return esc_attr__( 'Something went wrong while retrieving your map, please ensure you have entered the short code correctly.', 'fusion-builder' );
						}
					} else {
						return esc_attr__( 'Unable to contact Google API service.', 'fusion-builder' );
					}
				} else {
					// Return cached results.
					$data = $coordinates;
				}

				$data = apply_filters( 'privacy_script_embed', $data, 'gmaps', false, false, false );

				return $data;

			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.1
			 * @return array
			 */
			public function add_styling() {
				global $wp_version, $content_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $content_min_media_query;

				$css[ $content_media_query ]['.fusion-google-map']['width']                 = '100% !important';
				$css[ $three_twenty_six_fourty_media_query ]['.fusion-google-map']['width'] = '100% !important';
				$css[ $ipad_portrait_media_query ]['.fusion-google-map']['width']           = '100% !important';

				return $css;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.6
			 * @return array $sections Image Frame settings.
			 */
			public function add_options() {

				return [
					'google_map_shortcode_section' => [
						'label'       => esc_html__( 'Google Map', 'fusion-builder' ),
						'description' => '',
						'id'          => 'google_map_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-map',
						'fields'      => [
							'google_map_api_type' => [
								'label'       => esc_html__( 'Google API Type', 'fusion-builder' ),
								/* translators: https://cloud.google.com/maps-platform/user-guide/ URL. */
								'description' => sprintf( __( 'Select the Google API type that should be used to load your map. The JavaScript API allows for more options and custom styling, but could be charged for by Google depending on the amount of map loads. The embed and the static API can be used for free regardless of map loads. For more information please see the <a href="%s" target="_blank">Google Maps Users Guide</a>.', 'fusion-builder' ), 'https://cloud.google.com/maps-platform/user-guide/' ),
								'id'          => 'google_map_api_type',
								'type'        => 'radio-buttonset',
								'default'     => 'js',
								'transport'   => 'postMessage',
								'choices'     => [
									'js'     => esc_attr__( 'JS API', 'fusion-builder' ),
									'embed'  => esc_attr__( 'Embed API', 'fusion-builder' ),
									'static' => esc_attr__( 'Static API', 'fusion-builder' ),
								],
							],
						],
					],
				];
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {
				Fusion_Dynamic_JS::enqueue_script(
					'fusion-google-map',
					FUSION_LIBRARY_URL . '/assets/min/js/general/fusion-google-map.js',
					FUSION_LIBRARY_PATH . '/assets/min/js/general/fusion-google-map.js',
					[ 'jquery-fusion-maps' ],
					'1',
					true
				);
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/google-map.min.css' );
			}
		}
	}

	new FusionSC_GoogleMap();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_google_map() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_GoogleMap',
			[
				'name'       => esc_attr__( 'Google Map', 'fusion-builder' ),
				'shortcode'  => 'fusion_map',
				'icon'       => 'fusiona-map',
				'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-google-map-preview.php',
				'preview_id' => 'fusion-builder-block-module-google-map-preview-template',
				'help_url'   => 'https://theme-fusion.com/documentation/avada/elements/google-map-element/',
				'params'     => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Google API Type', 'fusion-builder' ),
						/* translators: URL to google maps doc. */
						'description' => sprintf( __( 'Select the Google API type that should be used to load your map. The JavaScript API allows for more options and custom styling, but could be charged for by Google depending on the amount of map loads. The embed and the static API can be used for free regardless of map loads. For more information please see the <a href="%s" target="_blank">Google Maps Users Guide</a>.', 'fusion-builder' ), 'https://cloud.google.com/maps-platform/user-guide/' ),
						'param_name'  => 'api_type',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'js'     => esc_attr__( 'JS API', 'fusion-builder' ),
							'embed'  => esc_attr__( 'Embed API', 'fusion-builder' ),
							'static' => esc_attr__( 'Static API', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Address', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the address of the location you wish to display. Address example: 775 New York Ave, Brooklyn, Kings, New York 11203. If the location is off, please try to use long/lat coordinates. ex: 12.381068,-1.492711.', 'fusion-builder' ),
						'param_name'   => 'embed_address',
						'dynamic_data' => true,
						'value'        => '',
						'dependency'   => [
							[
								'element'  => 'api_type',
								'value'    => 'js',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'static',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Map Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the type of google map to display.', 'fusion-builder' ),
						'param_name'  => 'embed_map_type',
						'value'       => [
							'roadmap'   => esc_attr__( 'Roadmap', 'fusion-builder' ),
							'satellite' => esc_attr__( 'Satellite', 'fusion-builder' ),
						],
						'default'     => 'roadmap',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'js',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'static',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textarea',
						'heading'     => esc_attr__( 'Address', 'fusion-builder' ),
						'description' => esc_attr__( 'Add the address of the location you wish to display. Single address example: 775 New York Ave, Brooklyn, Kings, New York 11203. If the location is off, please try to use long/lat coordinates with latlng=. ex: latlng=12.381068,-1.492711. For multiple addresses, separate addresses by using the | symbol. ex: Address 1|Address 2|Address 3.', 'fusion-builder' ),
						'param_name'  => 'address',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Map Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the type of google map to display.', 'fusion-builder' ),
						'param_name'  => 'type',
						'value'       => [
							'roadmap'   => esc_attr__( 'Roadmap', 'fusion-builder' ),
							'satellite' => esc_attr__( 'Satellite', 'fusion-builder' ),
							'hybrid'    => esc_attr__( 'Hybrid', 'fusion-builder' ),
							'terrain'   => esc_attr__( 'Terrain', 'fusion-builder' ),
						],
						'default'     => 'roadmap',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Map Dimensions', 'fusion-builder' ),
						'description'      => __( 'Map dimensions in percentage, pixels or ems. <strong>NOTE:</strong> Height does not accept percentage value. In case static API is selected width and height are limited to 640px (except for the premium Google Maps API plan) and must be specified in px only.', 'fusion-builder' ),
						'param_name'       => 'map_dimensions',
						'value'            => [
							'width'  => '100%',
							'height' => '300px',
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Zoom Level', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the zoom level for the map. 0 corresponds to a map of the earth fully zoomed out, and larger zoom levels zoom in at a higher resolution.', 'fusion-builder' ),
						'param_name'  => 'zoom',
						'value'       => '14',
						'min'         => '1',
						'max'         => '25',
						'step'        => '1',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Scrollwheel on Map', 'fusion-builder' ),
						'description' => esc_attr__( 'Enable zooming using the mouse scroll wheel. Use Cmd/Ctrl key + scroll to zoom. If set to no, zooming through two-finger movements (cooperative gesture handling) will be enabled.', 'fusion-builder' ),
						'param_name'  => 'scrollwheel',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'static',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Scale Control on Map', 'fusion-builder' ),
						'description' => esc_attr__( 'Display the map scale.', 'fusion-builder' ),
						'param_name'  => 'scale',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'static',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Pan Control on Map', 'fusion-builder' ),
						'description' => esc_attr__( 'Display pan control button.', 'fusion-builder' ),
						'param_name'  => 'zoom_pancontrol',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'static',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Address Pin Animation', 'fusion-builder' ),
						'description' => esc_attr__( 'Animate the address pin when the map first loads.', 'fusion-builder' ),
						'param_name'  => 'animation',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'static',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Tooltip by Default', 'fusion-builder' ),
						'description' => esc_attr__( 'Display or hide tooltip by default when the map first loads.', 'fusion-builder' ),
						'param_name'  => 'popup',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'static',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Select the Map Styling Switch', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose default styling for classic google map styles. Choose theme styling for our custom style. Choose custom styling to make your own with the advanced options below.', 'fusion-builder' ),
						'param_name'  => 'map_style',
						'value'       => [
							'default' => esc_attr__( 'Default Styling', 'fusion-builder' ),
							'theme'   => esc_attr__( 'Theme Styling', 'fusion-builder' ),
							'custom'  => esc_attr__( 'Custom Styling', 'fusion-builder' ),
						],
						'default'     => 'default',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'static',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Map Overlay Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Custom styling setting only. Pick any overlaying color for the map besides pure black or white. Works best with "roadmap" type.', 'fusion-builder' ),
						'param_name'  => 'overlay_color',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'static',
								'operator' => '!=',
							],
							[
								'element'  => 'map_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpicker',
						'heading'     => esc_attr__( 'Map Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Custom styling setting only. Pick any color for the map besides pure black or white.', 'fusion-builder' ),
						'param_name'  => 'static_map_color',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'js',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
						],
					],
					[
						'type'         => 'raw_textarea',
						'heading'      => esc_attr__( 'Infobox Content', 'fusion-builder' ),
						'description'  => esc_attr__( 'Custom styling setting only. Type in custom info box content to replace address string. For multiple addresses, separate info box contents by using the | symbol. ex: InfoBox 1|InfoBox 2|InfoBox 3.', 'fusion-builder' ),
						'param_name'   => 'infobox_content',
						'value'        => '',
						'dynamic_data' => true,
						'dependency'   => [
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'static',
								'operator' => '!=',
							],
							[
								'element'  => 'map_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Infobox Styling', 'fusion-builder' ),
						'description' => esc_attr__( 'Custom styling setting only. Choose between default or custom info box.', 'fusion-builder' ),
						'param_name'  => 'infobox',
						'value'       => [
							'default' => esc_attr__( 'Default Infobox', 'fusion-builder' ),
							'custom'  => esc_attr__( 'Custom Infobox', 'fusion-builder' ),
						],
						'default'     => 'default',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'static',
								'operator' => '!=',
							],
							[
								'element'  => 'map_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Info Box Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Custom styling setting only. Pick a color for the info box text.', 'fusion-builder' ),
						'param_name'  => 'infobox_text_color',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'static',
								'operator' => '!=',
							],
							[
								'element'  => 'map_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'infobox',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Info Box Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Custom styling setting only. Pick a color for the info box background.', 'fusion-builder' ),
						'param_name'  => 'infobox_background_color',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'static',
								'operator' => '!=',
							],
							[
								'element'  => 'map_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'infobox',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textarea',
						'heading'     => esc_attr__( 'Custom Marker Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Custom styling setting only. Use full image urls for custom marker icons or input "theme" for our custom marker. For multiple addresses, separate icons by using the | symbol or use one for all. ex: Icon 1|Icon 2|Icon 3.', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'static',
								'operator' => '!=',
							],
							[
								'element'  => 'map_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textarea',
						'heading'     => esc_attr__( 'Custom Marker Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Custom styling setting only. Use full image urls for custom marker icons or input "theme" for our custom marker. For multiple addresses, separate icons by using the | symbol or use one for all. ex: Icon 1|Icon 2|Icon 3. NOTE: Icon images may be in PNG, JPEG or GIF formats and may be up to 4096 pixels maximum size (64x64 for square images).', 'fusion-builder' ),
						'param_name'  => 'icon_static',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'api_type',
								'value'    => 'js',
								'operator' => '!=',
							],
							[
								'element'  => 'api_type',
								'value'    => 'embed',
								'operator' => '!=',
							],
						],
					],
					'fusion_margin_placeholder' => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_bottom' => '',
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_google_map' );

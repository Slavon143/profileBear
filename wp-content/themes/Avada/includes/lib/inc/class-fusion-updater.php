<?php
/**
 * Envato API class.
 *
 * @package Fusion_Updater
 */

/**
 * Creates the Envato API connection.
 *
 * @class Fusion_Updater
 * @version 5.0.0
 * @since 5.0.0
 */
final class Fusion_Updater {

	/**
	 * The arguments that are used in the Fusion_Product_Registration class.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var array
	 */
	private $args = [];

	/**
	 * An instance of the Fusion_Product_Registration class.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var object Fusion_Product_Registration.
	 */
	private $registration;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $registration An instance of the Fusion_Product_Registration class.
	 */
	public function __construct( $registration ) {

		$this->registration = $registration;
		$this->args         = $registration->get_args();

		// Check for theme & plugin updates.
		add_filter( 'http_request_args', [ $this, 'update_check' ], 5, 2 );

		// Inject theme updates into the response array.
		add_filter( 'pre_set_site_transient_update_themes', [ $this, 'update_themes' ] );
		add_filter( 'pre_set_transient_update_themes', [ $this, 'update_themes' ] );

		// Inject plugin updates into the response array.
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'update_plugins' ] );
		add_filter( 'pre_set_transient_update_plugins', [ $this, 'update_plugins' ] );

		// Inject plugin information into the API calls.
		add_filter( 'plugins_api', [ $this, 'plugins_api' ], 10, 3 );

		// Requests to update server args.
		add_filter( 'http_request_args', [ $this, 'request_headers' ], 10, 2 );

		// Requests to update server args.
		add_filter( 'http_response', [ $this, 'response_errors' ], 10, 3 );
	}

	/**
	 * Inject update data for premium themes.
	 *
	 * @since 5.0.0
	 *
	 * @param object $transient The pre-saved value of the `update_themes` site transient.
	 * @return object
	 */
	public function update_themes( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}

		// Process Avada updates.
		if ( isset( $transient->checked ) && class_exists( 'Avada' ) ) {

			// Get the installed version of Avada.
			$latest_avada = '';
			if ( Avada()->registration->should_show( 'plugins' ) ) {
				$latest_avada = Fusion_Helper::normalize_version( Avada::get_latest_version() );
			}
			$current_avada_version = Fusion_Helper::normalize_version( Avada::get_theme_version() );

			$_theme = [
				'theme'        => 'Avada',
				'new_version'  => $latest_avada,
				'url'          => 'https://theme-fusion.com/avada-documentation/changelog.txt',
				'package'      => '',
				'required'     => AVADA_MIN_WP_VER_REQUIRED,
				'requires_php' => AVADA_MIN_PHP_VER_REQUIRED,
			];

			// Only if latest version is found add download url.
			if ( '' !== $latest_avada ) {
				$_theme['package'] = FUSION_UPDATES_URL . '/?avada_action=get_theme&ver=' . $current_avada_version;
				if ( Avada()->registration->is_registered() ) {
					$_theme['package'] .= '&code=' . Avada()->registration->get_purchase_code();
				} elseif ( Avada()->registration->legacy_support() ) {
					$_theme['package'] .= '&token=' . Avada()->registration->get_token();
				}
			}

			// If registered and latest version is newer, show update.
			if ( '' !== $latest_avada && version_compare( $current_avada_version, $latest_avada, '<' ) ) {
				$transient->response['Avada'] = $_theme;
			} else {
				$transient->no_update['Avada'] = $_theme;
			}
		}

		return $transient;
	}

	/**
	 * Inject update data for premium plugins.
	 *
	 * @since 1.0.0
	 * @param object $transient The pre-saved value of the `update_plugins` site transient.
	 * @return object
	 */
	public function update_plugins( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}

		// Get the array of arguments.
		$bundled_plugins = [];
		$plugins         = [];

		if ( class_exists( 'Avada' ) ) {
			$plugins_info    = Avada::get_bundled_plugins();
			$bundled_plugins = $this->args['bundled'];

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once wp_normalize_path( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$plugins = get_plugins();
		}

		// Loop available plugins.
		if ( isset( $plugins ) && ! empty( $plugins ) && isset( $bundled_plugins ) && ! empty( $bundled_plugins ) ) {
			foreach ( $plugins as $plugin_file => $plugin ) {

				if ( 'Fusion Core' === $plugin['Name'] ) {
					$plugin['Name'] = 'Avada Core';
				} elseif ( 'Fusion Builder' === $plugin['Name'] ) {
					$plugin['Name'] = 'Avada Builder';
				}

				// Process bundled plugin updates.
				foreach ( $bundled_plugins as $bundled_plugin_slug => $bundled_plugin_name ) {
					if ( $plugin['Name'] === $bundled_plugin_name && isset( $plugins_info[ $bundled_plugin_slug ] ) && class_exists( 'Avada' ) ) {

						$_plugin = [
							'id'          => $plugin_file,
							'slug'        => dirname( $plugin_file ),
							'plugin'      => $plugin_file,
							'new_version' => $plugins_info[ $bundled_plugin_slug ]['version'],
							'url'         => '',
							'package'     => '',
							'icons'       => [
								'1x' => esc_url_raw( $plugins_info[ $bundled_plugin_slug ]['icon'] ),
								'2x' => esc_url_raw( $plugins_info[ $bundled_plugin_slug ]['icon'] ),
							],
						];
						if ( $plugins_info[ $bundled_plugin_slug ]['banner'] ) {
							$_plugin['banners'] = [
								'2x'      => esc_url_raw( $plugins_info[ $bundled_plugin_slug ]['banner'] ),
								'default' => esc_url_raw( $plugins_info[ $bundled_plugin_slug ]['banner'] ),
							];
						}

						if ( version_compare( $plugin['Version'], $plugins_info[ $bundled_plugin_slug ]['version'], '<' ) ) {
							$_plugin['package'] = Avada()->remote_install->get_package( $bundled_plugin_name );

							$transient->response[ $plugin_file ] = (object) $_plugin;
						} else {
							$transient->no_update[ $plugin_file ] = (object) $_plugin;
						}
					}
				}
			}
		}

		return $transient;
	}

	/**
	 * Disables requests to the wp.org repository for Avada.
	 *
	 * @since 5.0.0
	 *
	 * @param array  $request An array of HTTP request arguments.
	 * @param string $url The request URL.
	 * @return array
	 */
	public function update_check( $request, $url ) {

		// Theme update request.
		if ( false !== strpos( $url, '//api.wordpress.org/themes/update-check/1.1/' ) ) {

			// Decode JSON so we can manipulate the array.
			$data = json_decode( $request['body']['themes'] );

			// Remove Avada.
			unset( $data->themes->Avada );

			// Encode back into JSON and update the response.
			$request['body']['themes'] = wp_json_encode( $data );
		}
		return $request;
	}

	/**
	 * Inject API data for premium plugins.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $response Always false.
	 * @param string $action The API action being performed.
	 * @param object $args Plugin arguments.
	 * @return bool|object $response The plugin info or false.
	 */
	public function plugins_api( $response, $action, $args ) {
		// Process premium theme updates.
		if ( 'plugin_information' === $action && isset( $args->slug ) && class_exists( 'Avada' ) ) {
			$plugins_info    = Avada::get_bundled_plugins();
			$bundled_plugins = $this->args['bundled'];

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once wp_normalize_path( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$plugins = get_plugins();

			if ( isset( $bundled_plugins[ $args->slug ] ) ) {
				$plugin = $plugins_info[ $args->slug ];

				$response                  = new stdClass();
				$response->slug            = $args->slug;
				$response->plugin          = $args->slug;
				$response->plugin_name     = $plugin['plugin_name'];
				$response->name            = $plugin['plugin_name'];
				$response->author          = '<a href="' . $plugin['plugin_author_url'] . '" target="_blank">' . $plugin['plugin_author'] . '</a>';
				$response->homepage        = $plugin['external_url'];
				$response->sections        = [
					'description' => isset( $plugins[ $args->slug . '/' . $args->slug . '.php' ]['Description'] ) ? $plugins[ $args->slug . '/' . $args->slug . '.php' ]['Description'] : $plugin['plugin_name'],
				];
				$response->banners['high'] = $plugin['banner'];
				$response->banners['low']  = $plugin['banner'];
			}
		}
		return $response;
	}

	/**
	 * Add referrer to headers.
	 *
	 * @since 3.3
	 *
	 * @param array  $parsed_args Parsed request args.
	 * @param string $url         Request URL.
	 * @return array
	 */
	public function request_headers( $parsed_args = [], $url = '' ) {

		// If its not requesting the updates server.
		if ( false === strpos( $url, FUSION_UPDATES_URL ) ) {
			return $parsed_args;
		}

		if ( ! isset( $parsed_args['headers'] ) || ! is_array( $parsed_args['headers'] ) ) {
			$parsed_args['headers'] = [];
		}

		$parsed_args['headers']['Referer'] = site_url();
		$parsed_args['user-agent']         = 'avada-user-agent';

		return $parsed_args;
	}

	/**
	 * Check for errors when downloading plugin or theme.
	 *
	 * @since 3.3
	 *
	 * @param array  $response    Remote response.
	 * @param array  $parsed_args Parsed request args.
	 * @param string $url         Request URL.
	 * @return array
	 */
	public function response_errors( $response = [], $parsed_args = [], $url = [] ) {
		if ( false === strpos( $url, '?avada_action=get_theme' ) && false === strpos( $url, '?avada_action=get_download' ) && false === strpos( $url, '?avada_demo=' ) ) {
			return $response;
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$code = wp_remote_retrieve_response_code( $response );
		if ( 399 < $code && 501 > $code ) {
			$type = 'download';
			if ( false !== strpos( $url, '?avada_demo=' ) ) {
				$type = 'prebuilt';
			}
			return Avada()->registration->get_error( $code, $type );
		}

		return $response;
	}
}

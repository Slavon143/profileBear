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
class AWB_Studio {

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
	 * The studio status.
	 *
	 * @access public
	 * @var boolean
	 */
	public static $status = null;

	/**
	 * URL to fetch from.
	 *
	 * @access private
	 * @var boolean
	 */
	private $studio_url = 'https://avada.studio/';

	/**
	 * Class constructor.
	 *
	 * @since 3.0
	 * @access private
	 */
	private function __construct() {

		if ( ! self::is_studio_enabled() ) {
			return;
		}

		add_action( 'wp_ajax_fusion_builder_load_studio_elements', [ $this, 'get_ajax_data' ] );
		add_action( 'fusion_builder_load_templates', [ $this, 'builder_template' ] );
		add_action( 'fusion_builder_after', [ $this, 'builder_template' ] );

		// Requests to update server args.
		add_filter( 'http_request_args', [ $this, 'request_headers' ], 10, 2 );

		// Load admin page.
		if ( is_admin() ) {
			add_action( 'init', [ $this, 'admin_init' ] );
		}
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
			self::$instance = new AWB_Studio();
		}
		return self::$instance;
	}

	/**
	 * Studio status.
	 *
	 * @static
	 * @access public
	 * @since 3.0
	 */
	public static function is_studio_enabled() {

		if ( null !== self::$status ) {
			return self::$status;
		}

		$option_name  = class_exists( 'Fusion_Settings' ) ? Fusion_Settings::get_option_name() : 'fusion_options';
		$option       = get_option( $option_name, [] );
		self::$status = apply_filters( 'fusion_load_studio', isset( $option['status_avada_studio'] ) && '0' === $option['status_avada_studio'] ? false : true );

		return self::$status;
	}

	/**
	 * Renders studio import options section.
	 *
	 * @static
	 * @access public
	 * @param string $type The template type.
	 * @since 3.7.0
	 */
	public static function studio_import_options_template( $type = '' ) {
		$should_open     = 'studio-admin' === $type || 'setup-wizard' === $type ? 'open' : '';
		$should_hide     = 'studio-admin' === $type || 'setup-wizard' === $type ? 'display:none' : '';
		$fusion_settings = awb_get_fusion_settings();

		$heading_typo = [
			'h1_typography' => $fusion_settings->get( 'h1_typography' ),
			'h2_typography' => $fusion_settings->get( 'h2_typography' ),
			'h3_typography' => $fusion_settings->get( 'h3_typography' ),
			'h4_typography' => $fusion_settings->get( 'h4_typography' ),
			'h5_typography' => $fusion_settings->get( 'h5_typography' ),
			'h6_typography' => $fusion_settings->get( 'h6_typography' ),
		];

		?>
		<div class="awb-import-options <?php echo esc_attr( $should_open ); ?>" data-awb-headings-typographies="<?php echo esc_attr( wp_json_encode( $heading_typo, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP ) ); ?>">
			<button class="awb-import-options-toggle" type="button" name="button"><span class="icon fusiona-cog"></span> <?php esc_html_e( 'Import Options', 'fusion-builder' ); ?></button>
			<button class="fusion-studio-preview-back"><i class="fusiona-arrow-back"></i><?php esc_html_e( 'Go Back', 'fusion-builder' ); ?></button>
			<div class="awb-import-options-group">
				<!-- Position option -->
				<div class="awb-import-option awb-import-position" style="<?php echo esc_attr( $should_hide ); ?>">
					<label for=""><?php esc_html_e( 'Position', 'fusion-builder' ); ?></label>
					<div class="awb-option-button">
						<div class="awb-option-item">
							<input id="load-type-above" type="radio" name="load-type" value="load-type-above">
							<label for="load-type-above" data-tooltip="<?php esc_html_e( 'Insert above current content', 'fusion-builder' ); ?>">
								<i class="fusiona-before-content"></i>
							</label>
						</div>
						<div class="awb-option-item">
							<input id="load-type-replace" type="radio" name="load-type" value="load-type-replace">
							<label for="load-type-replace" data-tooltip="<?php esc_html_e( 'Replace all page content', 'fusion-builder' ); ?>">
								<i class="fusiona-replace-content"></i>
							</label>
						</div>
						<div class="awb-option-item">
							<input id="load-type-below" type="radio" name="load-type" value="load-type-below">
							<label for="load-type-below" data-tooltip="<?php esc_html_e( 'Insert below current content', 'fusion-builder' ); ?>">
								<i class="fusiona-after-content"></i>
							</label>
						</div>
					</div>
				</div>
				<!-- Overwrite option -->
				<div class="awb-import-option awb-import-style">
					<label for=""><?php esc_html_e( 'Style', 'fusion-builder' ); ?></label>
					<div class="awb-option-button">
						<div class="awb-option-item">
							<input id="inherit" type="radio" name="overwrite-type" value="inherit">
							<label for="inherit" data-tooltip="<?php esc_html_e( 'Local Colors & Typography', 'fusion-builder' ); ?>">

								<i class="fusiona-fit-import"></i>
							</label>
						</div>
						<div class="awb-option-item">
							<input id="replace-pos" type="radio" name="overwrite-type" value="replace-pos">
							<label for="replace-pos" data-tooltip="<?php esc_html_e( 'WYSIWYG Studio Styles', 'fusion-builder' ); ?>">
								<i class="fusiona-inherit-import"></i>
							</label>
						</div>
					</div>
				</div>
				<!-- Images import -->
					<div class="awb-import-option awb-images-import">
					<label for=""><?php esc_html_e( 'Images', 'fusion-builder' ); ?></label>
					<div class="awb-option-button">
						<div class="awb-option-item">
							<input id="do-import-images" type="radio" name="images" value="do-import-images">
							<label for="do-import-images" data-tooltip="<?php esc_html_e( 'Import Images', 'fusion-builder' ); ?>">
								<i class="fusiona-import-images"></i>
							</label>
						</div>
						<div class="awb-option-item">
							<input id="dont-import-images" type="radio" name="images" value="dont-import-images">
							<label for="dont-import-images" data-tooltip="<?php esc_html_e( 'Use Placeholders', 'fusion-builder' ); ?>">
								<i class="fusiona-replace-placeholder"></i>
							</label>
						</div>
					</div>
				</div>
				<!-- Invert option -->
				<div class="awb-import-option awb-import-inversion">
					<label for=""><?php esc_html_e( 'Colors', 'fusion-builder' ); ?></label>
					<div class="awb-option-button">
						<div class="awb-option-item">
							<input id="dont-invert" type="radio" name="invert" value="dont-invert">
							<label for="dont-invert" data-tooltip="<?php esc_html_e( 'Normal', 'fusion-builder' ); ?>">
								<i class="fusiona-dont-invert"></i>
							</label>
						</div>
						<div class="awb-option-item">
							<input id="do-invert" type="radio" name="invert" value="do-invert">
							<label for="do-invert" data-tooltip="<?php esc_html_e( 'Invert', 'fusion-builder' ); ?>">
								<i class="fusiona-do-invert"></i>
							</label>
						</div>
					</div>
				</div>
			</div>
			<a class="awb-import-studio-item-in-preview" href="#">
				<?php
				if ( 'setup-wizard' === $type ) :
					esc_html_e( 'Select', 'fusion-builder' );
					else :
						esc_html_e( 'Import', 'fusion-builder' );
					endif;
					?>
			</a>
		</div>
		<?php
	}

	/**
	 * Return the studio URL.
	 *
	 * @access public
	 * @since 3.0
	 * @return string
	 */
	public function get_studio_url() {
		return $this->studio_url;
	}

	/**
	 * Get the data from REST endpoint.
	 *
	 * @access public
	 * @since 3.0
	 * @return array
	 */
	public function get_data() {
		if ( null !== $this->data ) {
			return $this->data;
		}

		if ( ! FUSION_BUILDER_DEV_MODE && false !== get_transient( 'avada_studio' ) ) {
			$this->data = get_transient( 'avada_studio' );
			return $this->data;
		}

		$response = wp_remote_get( $this->studio_url . '/wp-json/studio/full', [ 'timeout' => 60 ] );

		// Exit if error.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// Get the body.
		$resources = json_decode( wp_remote_retrieve_body( $response ), true );

		set_transient( 'avada_studio', $resources, DAY_IN_SECONDS );

		return $resources;
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
	 * Template used for studio layouts.
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function builder_template() {
		?>
		<script type="text/html" id="tmpl-fusion_studio_layout">
			<#
			var slugs       = '';
			if ( 'object' === typeof tags ) {
				_.each( tags, function( tag ) {
					slugs += tag + ' ';
				} );
				slugs = slugs.trim();
			}

			if ( 'string' === typeof element ) {
				elementType = element;
			}

			#>
			<li class="fusion-page-layout" data-layout-id="{{ ID }}" data-slug="{{ slugs }}" data-url="{{url}}">
				<div class="preview lazy-load">
					<img src="data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%27{{ thumbnail.width }}%27%20height%3D%27{{ thumbnail.height }}%27%20viewBox%3D%270%200%20{{ thumbnail.width }}%20{{ thumbnail.height }}%27%3E%3Crect%20width%3D%27{{ thumbnail.width }}%27%20height%3D%273{{ thumbnail.height }}%27%20fill-opacity%3D%220%22%2F%3E%3C%2Fsvg%3E" alt="" width="{{ thumbnail.width }}" height="{{ thumbnail.height }}" data-src="{{ thumbnail.url }}" data-alt="{{ post_title }}"/>
				</div>
				<div class="bar">
					<span class="fusion_module_title">{{ post_title }}</span>
					<div class="fusion-module-right">
						<div class="awb-import-studio-item">
							<span class="fusiona-plus"></span>
						</div>
					</div>
				</div>
			</li>
		</script>
		<?php
	}

	/**
	 * Inits admin.
	 *
	 * @access public
	 * @since 3.5
	 * @return void
	 */
	public function admin_init() {
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-awb-studio-admin.php';
		new AWB_Studio_Admin();
	}

	/**
	 * Add referrer to headers.
	 *
	 * @since 3.5
	 *
	 * @param array  $parsed_args Parsed request args.
	 * @param string $url         Request URL.
	 * @return array
	 */
	public function request_headers( $parsed_args = [], $url = '' ) {

		// If its not requesting the studio site.
		if ( false === strpos( $url, $this->studio_url ) ) {
			return $parsed_args;
		}

		$parsed_args['user-agent'] = 'avada-user-agent';

		return $parsed_args;
	}
}

/**
 * Instantiates the AWB_Studio class.
 * Make sure the class is properly set-up.
 *
 * @since object 3.0
 * @return object AWB_Studio
 */
function AWB_Studio() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Studio::get_instance();
}
AWB_Studio();

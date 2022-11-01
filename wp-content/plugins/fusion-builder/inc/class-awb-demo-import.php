<?php
/**
 * Avada Demo Import
 *
 * @package Avada-Builder
 * @since 3.5
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * AWB Demo class.
 *
 * @since 3.5
 */
class AWB_Demo_Import {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 3.5
	 * @var object
	 */
	private static $instance;

	/**
	 * Location.
	 *
	 * @access private
	 * @since 3.5
	 * @var object
	 */
	private $location;

	/**
	 * URL to fetch for demo data.
	 *
	 * @access private
	 * @since 3.5
	 * @var boolean
	 */
	private $demo_url = 'https://updates.theme-fusion.com';

	/**
	 * The demo data.
	 *
	 * @access public
	 * @since 3.5
	 * @var mixed
	 */
	public $demo_data = null;

	/**
	 * URL to fetch for page data.
	 *
	 * @access private
	 * @since 3.5
	 * @var boolean
	 */
	private $page_url = 'https://avada.theme-fusion.com';

	/**
	 * Class constructor.
	 *
	 * @since 3.5
	 * @access private
	 */
	private function __construct() {

		add_action( 'wp_ajax_awb_load_websites', [ $this, 'get_ajax_website_data' ] );
		add_action( 'wp_ajax_awb_load_websites_page', [ $this, 'load_website_page' ] );

		add_action( 'fusion_builder_load_templates', [ $this, 'library_template' ] );
		add_action( 'fusion_builder_after', [ $this, 'library_template' ] );

		$this->location = true === Fusion_App()->is_builder || ( isset( $_POST ) && isset( $_POST['fusion_front_end'] ) && $_POST['fusion_front_end'] ) ? 'front' : 'back'; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 3.5
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new AWB_Demo_Import();
		}
		return self::$instance;
	}

	/**
	 * Get the data for ajax website requests.
	 *
	 * @access public
	 * @since 3.5
	 * @return void
	 */
	public function get_ajax_website_data() {
		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

		echo wp_json_encode( $this->get_website_data() );

		wp_die();
	}

	/**
	 * Return the demo URL.
	 *
	 * @access public
	 * @since 3.5
	 * @return string
	 */
	public function get_demo_url() {
		return $this->demo_url;
	}

	/**
	 * Get the demo data from REST endpoint.
	 *
	 * @access public
	 * @since 3.5
	 * @return array
	 */
	public function get_website_data() {
		if ( null !== $this->demo_data ) {
			return $this->demo_data;
		}

		if ( false !== get_transient( 'awb_library_demo' ) ) {
			$this->demo_data = get_transient( 'awb_library_demo' );
			return $this->demo_data;
		}

		$response = wp_remote_get( $this->get_demo_url() . '/wp-json/demo/full', [ 'timeout' => 60 ] );

		// Exit if error.
		if ( is_wp_error( $response ) ) {
			return;
		}

		// Get the body.
		$resources = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( is_array( $resources ) && ! isset( $resources['tags'] ) ) {
			return;
		}

		set_transient( 'awb_library_demo', $resources, DAY_IN_SECONDS );

		return $resources;
	}

	/**
	 * Load website page.
	 *
	 * @access public
	 * @since 3.5
	 * @return void
	 */
	public function load_website_page() {

		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

		if ( ! isset( $_POST['post_id'] ) && '' === $_POST['post_id'] ) {
			die( -1 );
		}

		if ( ! isset( $_POST['demo_name'] ) && '' === $_POST['demo_name'] ) {
			die( -1 );
		}

		if ( ! isset( $_POST['page_id'] ) && '' === $_POST['page_id'] ) {
			die( -1 );
		}

		$demo_name = sanitize_text_field( wp_unslash( $_POST['demo_name'] ) );
		$demo_name = str_replace( '_', '-', $demo_name );
		$post_id   = (int) $_POST['post_id'];
		$page_id   = (int) $_POST['page_id'];
		$data      = [
			'success' => true,
			'post_id' => $post_id,
		];

		$page_ep = $this->page_url . '/' . $demo_name . '/wp-json/wp/v2/pages/' . $page_id . '/';

		$response = wp_remote_get( $page_ep, [ 'timeout' => 60 ] );
		// Exit if error.
		if ( is_wp_error( $response ) ) {
			$data['success'] = false;
			echo wp_json_encode( $data );
			die();
		}

		// Get the body.
		$resources = json_decode( wp_remote_retrieve_body( $response ), true );

		// Set post_content.
		if ( isset( $resources['content'] ) && isset( $resources['content']['raw'] ) ) {
			$data['post_content'] = $resources['content']['raw'];
		} else {
			$data['success'] = false; // required to have post_content otherwise flag it as error.
		}

		// Set page template.
		if ( isset( $resources['template'] ) && ! empty( $resources['template'] ) ) {
			$data['page_template'] = $resources['template'];
		}

		// TODO: data meta.
		if ( isset( $resources['avada_media'] ) && is_array( $resources['avada_media'] ) ) {
			$data['avada_media'] = $resources['avada_media'];
		}

		$json_data = wp_json_encode( $data );

		die( $json_data ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Template used for website layouts.
	 *
	 * @access public
	 * @since 3.5
	 * @return void
	 */
	public function library_template() {
		?>
		<script type="text/html" id="tmpl-fusion_website_tags">
			<# var total = _.size( data ); #>
			<h3 class="awb-sites-heading-primary"><?php esc_html_e( 'Import Websites Pages', 'fusion-builder' ); ?></h3>
			<div class="awb-sites-search-wrapper">
				<span class="fusiona-search"></span>
				<input type="text" id="awb_sites_search_demos" placeholder="<?php esc_html_e( 'Paste page URL', 'fusion-builder' ); ?>">
			</div>
			<h4 class="awb-sites-heading-secondary"><?php esc_html_e( 'Filter Websites', 'fusion-builder' ); ?></h4>
			<ul>
				<li data-slug="all" class="current"><?php esc_html_e( 'All', 'fusion-builder' ); ?> <span>{{ total }}</span></li>
				<# _.each( tags, function( tag, slug ) { #>
				<li data-slug="{{ slug }}">{{{ tag.name }}} <span>{{ tag.count }}</span></li>
				<# } );#>
			</ul>
		</script>

		<script type="text/html" id="tmpl-fusion_website_layout">
			<#
			var slugs       = '';
			if ( 'object' === typeof tags ) {
				_.each( tags, function( tag ) {
					slugs += tag + ' ';
				} );
				slugs.trim();
			}

			#>
			<li class="fusion-page-layout awb-demo-pages-layout" data-website_id="{{ slug }}" data-website_title="{{{ post_title }}}" data-slug="{{ slugs }}">
				<div class="preview lazy-load">
					<# if ( 'undefined' !== typeof thumbnail ) { #>
					<img src="data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%27{{ thumbnail.width }}%27%20height%3D%27{{ thumbnail.height }}%27%20viewBox%3D%270%200%20{{ thumbnail.width }}%20{{ thumbnail.height }}%27%3E%3Crect%20width%3D%27{{ thumbnail.width }}%27%20height%3D%273{{ thumbnail.height }}%27%20fill-opacity%3D%220%22%2F%3E%3C%2Fsvg%3E" alt="" width="{{ thumbnail.width }}" height="{{ thumbnail.height }}" data-src="{{ thumbnail.url }}" data-alt="{{ post_title }}"/>
					<# } #>
				</div>
				<div class="bar">
					<span class="fusion_module_title">{{ post_title }}</span>
				</div>
			</li>
		</script>

		<script type="text/html" id="tmpl-fusion_website_pages">
			<div class="awb-page-item demo-{{{ slug }}} hidden" data-website_id="{{ slug }}" data-website_title="{{{ post_title }}}">
				<div class="awb-page-wrap">
					<ul class="awb-website-pages">
						<#
						if ( 'undefined' !== typeof pages ) {
							_.each( pages, function( page, page_key ) {
								var site_link = page.link.replace( /^(https?):\/\//, '' );
						#>
								<li class="awb-demo-page-link" data-page-link="{{{ site_link }}}"><span>{{{ page.name }}}</span> <a href="{{{ page.link }}}" class="awb-sites-preview" target="_blank"><span class="fusiona-eye"></span> <?php esc_html_e( 'Preview', 'fusion-builder' ); ?></a> <a href="#" data-demo-name="{{{ slug }}}" data-page-name="{{ page_key }}" data-page-id="{{ page.ID }}" data-post-id="{{ <?php echo ( 'front' === $this->location ) ? '0' : 'fusionBuilderConfig.post_id'; ?> }}" class="button button-primary awb-sites-import-js"><?php esc_html_e( 'Import', 'fusion-builder' ); ?></a></li>
						<#
							} );
						} #>
					</ul>
					<# if ( 'undefined' !== typeof thumbnail ) { #>
					<img width="240" src="{{{ thumbnail.url }}}">
					<# } #>
				</div>
			</div>
		</script>

		<script type="text/template" id="fusion-builder-demo-import-modal">
			<div class="awb-admin-modal-wrap">
				<div class="awb-admin-modal-inner">

					<div class="awb-admin-modal-content">

						<h2 class="awb-studio-modal-title">
							<i class="fusiona-exclamation-sign"></i>
							<span><?php echo esc_html( __( 'Importing Prebuilt Page Content', 'fusion-builder' ) ); ?></span>
						</h2>

						<div class="awb-studio-modal-text">
							<?php echo esc_html( __( 'Your prebuilt page content is now being imported. This includes the layout, and any associated images and videos. The import process should only take a few seconds, depending on the amount of content to be imported.', 'fusion-builder' ) ); ?>
						</div>
					</div>

					<div class="awb-admin-modal-status-bar">
						<div class="awb-admin-modal-status-bar-label"><span></span></div>
						<div class="awb-admin-modal-status-bar-progress-bar"></div>

						<a class="button-done-demo demo-update-modal-close" href="#"><?php esc_html_e( 'Done' ); ?></a>
					</div>
				</div>

				<a href="#" class="awb-admin-modal-corner-close"><span class="dashicons dashicons-no-alt"></span></a>
			</div>

			<div class="awb-modal-overlay"></div>
		</script>
		<?php
	}
}

/**
 * Instantiates the AWB_Demo_Import class.
 * Make sure the class is properly set-up.
 *
 * @since object 3.0
 * @return object AWB_Demo_Import
 */
function AWB_Demo_Import() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Demo_Import::get_instance();
}
AWB_Demo_Import();

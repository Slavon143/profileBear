<?php
/**
 * Fusion-Faqs main class.
 *
 * @package Fusion-Faqs
 * @since 5.1.2
 */

if ( ! class_exists( 'Fusion_Faqs' ) ) {
	/**
	 * The main Fusion_Faqs class.
	 */
	class Fusion_Faqs {

		/**
		 * The one, true instance of this object.
		 *
		 * @static
		 * @access private
		 * @var object
		 */
		private static $instance;

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		private function __construct() {
			// Add thumbnails to FAQs.
			add_filter( 'manage_avada_faq_posts_columns', 'fusion_wp_list_add_column', 10 );
			add_action( 'manage_avada_faq_posts_custom_column', 'fusion_add_thumbnail_in_column', 10, 2 );

			// JSON-LD implementation for FAQs.
			add_action( 'wp_footer', [ $this, 'faq_json_ld' ] );

		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @static
		 * @access public
		 * @since 2.2
		 */
		public static function get_instance() {

			// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
			if ( null === self::$instance ) {
				self::$instance = new Fusion_Faqs();
			}
			return self::$instance;
		}

		/**
		 * Add JSON-LD for FAQs.
		 *
		 * @access public
		 * @since 4.2.0
		 * @return void
		 */
		public function faq_json_ld() {
			$fusion_settings = awb_get_fusion_settings();
			if ( ! class_exists( 'Fusion_JSON_LD' ) || ( ! $fusion_settings->get( 'disable_date_rich_snippet_pages' ) || ! $fusion_settings->get( 'disable_rich_snippet_faq' ) ) ) {
				return;
			}

			// Handle FAQ Archives.
			if ( is_post_type_archive( 'avada_faq' ) ) {
				global $wp_query;
				if ( $wp_query->posts ) {
					foreach ( $wp_query->posts as $faq ) {
						new Fusion_JSON_LD(
							'fusion-faq',
							[
								'@context'   => 'https://schema.org',
								'@type'      => [ 'WebPage', 'FAQPage' ],
								'mainEntity' => [
									[
										'@type'          => 'Question',
										'name'           => $faq->post_title,
										'acceptedAnswer' => [
											'@type' => 'Answer',
											'text'  => $faq->post_content,
										],
									],
								],
							]
						);
					}
				}
			}

			if ( is_singular( 'avada_faq' ) ) {
				new Fusion_JSON_LD(
					'fusion-faq',
					[
						'@context'   => 'https://schema.org',
						'@type'      => [ 'FAQPage' ],
						'mainEntity' => [
							[
								'@type'          => 'Question',
								'name'           => get_the_title(),
								'acceptedAnswer' => [
									'@type' => 'Answer',
									'text'  => get_the_content(),
								],
							],
						],
					]
				);
			}
		}

	}
}

/**
 * Instantiates the Fusion_Faqs class.
 * Make sure the class is properly set-up.
 *
 * @return object Fusion_App
 */
function Fusion_Faqs() { // phpcs:ignore WordPress.NamingConventions
	return Fusion_Faqs::get_instance();
}
Fusion_Faqs();

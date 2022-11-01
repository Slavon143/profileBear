<?php
/**
 * The main Fusion_Builder_Front class.
 *
 * @package fusion-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Fusion_Builder_Front' ) ) {

	/**
	 * Main FusionBuilder Class.
	 *
	 * @since 1.0
	 */
	class Fusion_Builder_Front {

		/**
		 * The one, true instance of this object.
		 *
		 * @static
		 * @access private
		 * @since 1.0
		 * @var object
		 */
		private static $instance;

		/**
		 * Shortcode array for live builder.
		 *
		 * @access public
		 * @var array $shortcode_array.
		 */
		public $shortcode_array;

		/**
		 * Holds the number of next page elements.
		 *
		 * @access public
		 * @var array $next_page_elements_count.
		 */
		public $next_page_elements_count;

		/**
		 * Content filtered flag.
		 *
		 * @access private
		 * @since 4.0
		 * @var bool
		 */
		private $content_filtered = false;

		/**
		 * Pause content filtering.
		 *
		 * @access private
		 * @since 2.0.3
		 * @var bool
		 */
		private $filtering_paused = false;

		/**
		 * Whether we are within correct the_content call.
		 *
		 * @access private
		 * @since 4.0
		 * @var bool
		 */
		private $real_event_content = false;

		/**
		 * Actively capturing shortcode data.
		 *
		 * @access public
		 * @since 3.3
		 * @var bool
		 */
		public $capturing_active = false;

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @static
		 * @access public
		 * @since 1.0
		 */
		public static function get_instance() {

			// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
			if ( null === self::$instance ) {
				self::$instance = new Fusion_Builder_Front();
			}
			return self::$instance;
		}

		/**
		 * Initializes the plugin by setting localization, hooks, filters,
		 * and administrative functions.
		 *
		 * @access private
		 * @since 1.0
		 */
		private function __construct() {
			$this->shortcode_array = [];

			$this->init();
		}

		/**
		 * Initializes the plugin by setting localization, hooks, filters,
		 * and administrative functions.
		 *
		 * @access public
		 * @since 1.0
		 */
		public function init() {

			add_filter( 'fusion_app_preview_data', [ $this, 'add_builder_data' ], 10 );

			// If preview frame.
			if ( fusion_is_preview_frame() ) {

				if ( ! is_preview_only() ) {

					// Used to create shortcode map.
					add_filter( 'the_content', [ $this, 'front_end_content' ], 99 );
					add_filter( 'body_class', [ $this, 'body_class' ] );
					add_filter( 'do_shortcode_tag', [ $this, 'create_shortcode_contents_map' ], 10, 4 );
					remove_filter( 'the_content', 'wpautop' );
					remove_filter( 'the_excerpt', 'wpautop' );
					add_action( 'wp_enqueue_scripts', [ $this, 'preview_live_scripts' ], 999 );

					add_filter( 'revslider_include_libraries', [ $this, 'include_rev_scripts' ], 999 );

					add_action( 'tribe_events_single_event_before_the_content', [ $this, 'set_real_event_content' ], 999 );
					add_action( 'fusion_pause_live_editor_filter', [ $this, 'pause_content_filter' ], 999 );
					add_action( 'fusion_resume_live_editor_filter', [ $this, 'resume_content_filter' ], 999 );
				} else {

					// Preview only mode, just need to filter the unsaved content.
					add_filter( 'fusion_filter_data', [ $this, 'preview_only_content' ], 9999 );
				}

				add_filter( 'woocommerce_product_tabs', [ $this, 'product_tabs' ] );
			}

			if ( fusion_is_builder_frame() ) {

				add_action( 'wp_enqueue_scripts', [ $this, 'live_scripts' ], 999 );
				add_action( 'wp_footer', [ $this, 'load_templates' ] );

				// Builder mce button.
				add_filter( 'mce_external_plugins', [ $this, 'add_rich_plugins' ], 999 );
				add_action( 'wp_footer', [ $this, 'fusion_builder_add_quicktags_button' ], 999 );

				add_filter( 'fusion_privacy_bar', [ $this, 'disable_privacy_bar' ] );

				if ( class_exists( 'QM' ) ) {
					add_filter( 'user_has_cap', [ $this, 'disable_qm' ], 10, 1 );
				}

				add_action( 'wp_footer', [ $this, 'enqueue_wp_editor_scripts' ] );
			}

			if ( fusion_is_preview_frame() || fusion_is_builder_frame() ) {
				// Deregister WP admin forms.css.
				wp_deregister_style( 'forms' );

				// Disable All In One SEO head output.
				if ( defined( 'AIOSEO_PHP_VERSION_DIR' ) ) {
					add_filter( 'aioseo_meta_views', '__return_false' );
				}
			}

			add_action( 'wp_ajax_nopriv_get_shortcode_render', [ $this, 'get_shortcode_render' ] );
			add_action( 'wp_ajax_get_shortcode_render', [ $this, 'get_shortcode_render' ] );

			add_action( 'wp_ajax_fusion_create_post', [ $this, 'create_post' ] );

			add_filter( 'fusion_save_post_object', [ $this, 'save_builder_content' ] );

			add_filter( 'fusion_woo_component_content', [ $this, 'woo_placeholder' ], 10, 3 );

			// For hierarchical post types.
			add_filter( 'page_row_actions', [ $this, 'add_edit_link' ], 10, 2 );
			// For non-hierarchical post types.
			add_filter( 'post_row_actions', [ $this, 'add_edit_link' ], 10, 2 );

			remove_filter( 'the_posts', [ FusionBuilder::get_instance(), 'next_page' ], 10 );
		}

		/**
		 * Add necessary data for builder.
		 *
		 * @access public
		 * @since 6.0
		 * @param  array $data The data already added.
		 * @return array $data The data with panel data added.
		 */
		public function add_builder_data( $data ) {
			$data['shortcodeMap']             = $this->shortcode_array;
			$data['next_page_elements_count'] = $this->next_page_elements_count;
			return $data;
		}

		/**
		 * Add shortcode generator toggle button to text editor.
		 *
		 * @since 1.0
		 */
		public function fusion_builder_add_quicktags_button() {
			?>
			<script type="text/javascript" charset="utf-8">
				if ( 'function' === typeof QTags ) {
					QTags.addButton( 'fusion_shortcodes_text_mode', 'A:','', '', 'f' );
				}
			</script>
			<?php
		}

		/**
		 * Define TinyMCE rich editor js plugin.
		 *
		 * @access public
		 * @since 1.0
		 * @param array $plugin_array The plugins array.
		 * @return array.
		 */
		public function add_rich_plugins( $plugin_array ) {
			$plugin_array['fusion_button'] = FUSION_BUILDER_PLUGIN_URL . 'js/fusion-plugin.js';
			return $plugin_array;
		}

		/**
		 * Add TinyMCE rich editor button.
		 *
		 * @access public
		 * @since 1.0
		 * @param array $buttons The array of available buttons.
		 * @return array
		 */
		public function register_rich_buttons( $buttons ) {
			if ( is_array( $buttons ) ) {
				array_push( $buttons, 'fusion_button' );
			}

			return $buttons;
		}

		/**
		 * Checks if post type should be editable
		 *
		 * @access public
		 * @since 2.0.0
		 * @param string $post_id Id of current post.
		 * @return bool
		 */
		public function editable_posttype( $post_id = '' ) {

			// Is post type accepted.
			$allowed = FusionBuilder()->allowed_post_types();

			// Does use have capability to edit.
			$post_id          = '' === $post_id ? fusion_library()->get_page_id() : $post_id;
			$post_type        = get_post_type( $post_id );
			$post_type_object = get_post_type_object( $post_type );

			return in_array( $post_type, $allowed, true ) && current_user_can( $post_type_object->cap->edit_post, $post_id );
		}

		/**
		 * Creates the shortcode map for contents.
		 *
		 * @access public
		 * @since 2.0.0
		 * @param string $output The output.
		 * @param string $tag    The tag.
		 * @param array  $attr   The attributes.
		 * @param array  $m      Argument.
		 */
		public function create_shortcode_contents_map( $output, $tag, $attr, $m ) {

			// Add further checks here to only get necessary code.
			if ( ! fusion_doing_ajax() && is_main_query() && is_singular() && ! Fusion_App()->is_full_refresh() ) {
				$this->shortcode_array[] = [
					'shortcode' => $m[0],
					'output'    => $output,
					'tag'       => $tag,
				];
			}
			return $output;
		}

		/**
		 * Get the content for front-end editor.
		 *
		 * @since 6.0.0
		 * @param string $content The content.
		 * @return string
		 */
		public function front_end_content( $content ) {

			if ( ( is_singular( 'tribe_events' ) && ! $this->real_event_content ) || $this->filtering_paused ) {
				return $content;
			}

			// Count the amount of next page elements.
			$this->next_page_elements_count = substr_count( $content, '[fusion_builder_next_page]' );
			$this->next_page_elements_count = ( $this->next_page_elements_count ) ? $this->next_page_elements_count + 1 : 0;

			// Ajax check to make sure contents retrieved via ajax is regular content.
			if ( ( $this->editable_posttype() && $this->real_event_content ) || ( ! fusion_doing_ajax() && is_main_query() && ( is_singular() || ( class_exists( 'WooCommerce' ) && is_shop() ) ) && $this->editable_posttype() ) ) {
				$this->real_event_content = false;

				if ( false === $this->content_filtered ) {
					$this->capturing_active = true;
					do_shortcode( $content );
				}
				$this->capturing_active = false;
				$this->content_filtered = true;

				$front_end_content = '<div class="' . apply_filters( 'fusion_builder_live_editor_wrapper_class', 'fusion-builder-live-editor' ) . '"></div>';

				return apply_filters( 'fusion_builder_front_end_content', $front_end_content );
			}

			return $content;
		}

		/**
		 * We are within real content for single event.
		 *
		 * @since 6.0.0
		 * @return void
		 */
		public function set_real_event_content() {
			$this->real_event_content = true;
		}

		/**
		 * Flag to pause filtering of content.
		 *
		 * @since 2.0.3
		 * @return void
		 */
		public function pause_content_filter() {
			if ( true === $this->capturing_active ) {
				$this->capturing_active = 'paused';
			}
			$this->filtering_paused = true;
		}

		/**
		 * Flag to resume filtering of content.
		 *
		 * @since 2.0.3
		 * @return void
		 */
		public function resume_content_filter() {
			if ( 'paused' === $this->capturing_active ) {
				$this->capturing_active = true;
			}
			$this->filtering_paused = false;
		}

		/**
		 * Filter in unsaved conttent.
		 *
		 * @since 6.0.0
		 * @return void
		 */
		public function preview_only_content() {
			global $post;

			$post_content = Fusion_App()->get_data( 'post_content' );
			if ( $post_content ) {
				$post_content = apply_filters( 'content_save_pre', $post_content );

				// Post content.
				if ( $post_content ) {
					$post->post_content = $post_content;
				}
			}
		}

		/**
		 * Add editor body class.
		 *
		 * @since 6.0.0
		 * @param array $classes classes being used.
		 * @return string
		 */
		public function body_class( $classes ) {
			global $post;

			$classes[] = 'fusion-builder-live dont-animate';

			if ( 'fusion_element' === get_post_type() ) {
				$terms     = get_the_terms( $post->ID, 'element_category' );
				$classes[] = 'fusion-builder-library-edit';

				if ( $terms ) {
					$classes[] = 'fusion-element-post-type-' . $terms[0]->name;
				}
			}

			$target_post = function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_target_example() : false;
			if ( $target_post && 'product' === $target_post->post_type ) {
				$classes[] = 'woocommerce';
			}

			return $classes;
		}

		/**
		 * Load the template files.
		 *
		 * @since 6.0.0
		 */
		public function load_templates() {
			global $fusion_builder_elements, $fusion_builder_multi_elements;

			// Make sure map is generated later than customizer change.
			do_action( 'fusion_builder_before_init' );

			// Structure.
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-element.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-parent-element.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-child-element.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-container.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-row.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-column.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-toolbar.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-history.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-blank-page.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-library.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-custom-css.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-shortcuts.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-preferences.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-end-navigator.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/context-menu.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/context-menu-inline.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/previews/fusion-default-preview.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/next-page.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/dynamic-selection.php';

			// Studio Import Modal.
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/studio-import-modal.php';

			// Shared element components.
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/new-slideshow-blog-shortcode.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/featured-image.php';

			// Elements.
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-breadcrumbs.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-code-block.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-google-map.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-person.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-social-sharing.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-social-links.php';

			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-alert.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-audio.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-button.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-countdown.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-dropcap.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-fontawesome.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-highlight.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-image-hotspots.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-image.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-lightbox.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-lottie.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-menu-anchor.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-menu.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-modal.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-modal-text-link.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-news-ticker.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-one-page-link.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-popover.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-progress.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-scroll-progress.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-search.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-section-separator.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-separator.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-shortcode.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-soundcloud.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-star-rating.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-tagline-box.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-table.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-text.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-title.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-tooltip.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-user-login.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-vimeo.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-views-counter.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-video.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-youtube.php';

			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-syntax-highlighter.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-image-before-after.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-chart.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-pricing-table.php';

			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-blog.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-recent-posts.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-post-slider.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-events.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-widget-area.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-woo-product-slider.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-woo-featured-products-slider.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-gallery.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-facebook-page.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-twitter-timeline.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-flickr.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-tagcloud.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-instagram.php';

			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-woo-product-grid.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-woo-cart-table.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-woo-cart-totals.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-woo-cart-coupons.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-woo-cart-shipping.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-woo-sorting.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-post-cards.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/woo-checkout-form.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-woo-mini-cart.php';

			// Widget element.
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-widget.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-widget-content.php';

			// Advanced Elements.
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-checklist.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-content-boxes.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-image-carousel.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-counters-box.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-counters-circle.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-flip-boxes.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-media-slider.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-tabs.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-testimonials.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/fusion-toggle.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-element-settings.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/front-element-settings-children.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/element-library.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/element-library-generator.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/element-library-nested-column.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/column-library.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/container-library.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/nested-column.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/nested-row.php';

			// Form elements.
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/password.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/email.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/text.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/phone.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/hidden.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/honeypot.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/notice.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/number.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/checkbox.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/radio.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/recaptcha.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/date.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/range.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/textarea.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/upload.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/time.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/image-select.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/image-select-input.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/select.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/rating.php';
			include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/form-components/submit.php';

			do_action( 'fusion_builder_load_templates' );

			// Filter disabled elements.
			$fusion_builder_elements = fusion_builder_filter_available_elements();

			// Registered third party shortcodes.
			$vendor_shortcodes = fusion_get_vendor_shortcodes( $fusion_builder_elements );

			// Create elements js object. Load element's js and css.
			if ( ! empty( $fusion_builder_elements ) ) {

				$fusion_builder_elements = apply_filters( 'fusion_builder_all_elements', $fusion_builder_elements );
				echo '<script>var fusionAllElements = ' . wp_json_encode( $fusion_builder_elements ) . ';</script>';

				// Load modules backend js and css.
				foreach ( $fusion_builder_elements as $module ) {

					// Front-end preview template.
					if ( ! empty( $module['front-end'] ) ) {
						require_once wp_normalize_path( $module['front-end'] );
					}

					// Custom settings template.
					if ( ! empty( $module['front_end_custom_settings_template_file'] ) ) {
						require_once wp_normalize_path( $module['front_end_custom_settings_template_file'] );
					} elseif ( ! empty( $module['custom_settings_template_file'] ) ) {
						require_once wp_normalize_path( $module['custom_settings_template_file'] );
					}

					// Custom settings view.
					if ( ! empty( $module['front_end_custom_settings_view_js'] ) ) {
						wp_enqueue_script( $module['shortcode'] . '_custom_settings_view', $module['front_end_custom_settings_view_js'], '', FUSION_BUILDER_VERSION, true );
					} elseif ( ! empty( $module['custom_settings_view_js'] ) ) {
						wp_enqueue_script( $module['shortcode'] . '_custom_settings_view', $module['custom_settings_view_js'], '', FUSION_BUILDER_VERSION, true );
					}
				}
			}

			// Multi Element object.
			if ( ! empty( $fusion_builder_multi_elements ) ) {
				echo '<script>var fusionMultiElements = ' . wp_json_encode( $fusion_builder_multi_elements ) . ';</script>';
			}

			if ( ! empty( $vendor_shortcodes ) ) {
				echo '<script>var fusionVendorShortcodes = ' . wp_json_encode( $vendor_shortcodes ) . ';</script>';
			}

		}

		/**
		 * Enqueue preview frame scripts.
		 *
		 * @since 6.0.0
		 * @param mixed $hook The hook.
		 */
		public function preview_live_scripts( $hook ) {
			wp_enqueue_code_editor( [] );

			wp_enqueue_script( 'jquery' );

			wp_enqueue_style( 'fusion-preview-frame-builder-css', FUSION_BUILDER_PLUGIN_URL . 'front-end/css/preview.css', false, FUSION_BUILDER_VERSION );
			wp_enqueue_style( 'fusion-preview-frame-builder-no-controls-css', FUSION_BUILDER_PLUGIN_URL . 'front-end/css/preview-no-controls.css', false, FUSION_BUILDER_VERSION, 'none' );

			wp_enqueue_script( 'google-maps-api' );
			wp_enqueue_script( 'google-maps-infobox' );
		}

		/**
		 * Enqueue scripts.
		 *
		 * @since 6.0.0
		 * @param mixed $hook The hook.
		 */
		public function live_scripts( $hook ) {
			wp_enqueue_script( 'jquery' );

			wp_enqueue_script( 'quicktags' );

			global $typenow, $fusion_builder_elements, $fusion_builder_multi_elements, $pagenow, $post, $wp_scripts;

			// Isotope to avoid data issue cross frame.
			$js_folder_url = FUSION_LIBRARY_URL . '/assets' . ( ( true === FUSION_LIBRARY_DEV_MODE ) ? '' : '/min' ) . '/js';
			wp_enqueue_script( 'isotope', $js_folder_url . '/library/isotope.js', [], FUSION_BUILDER_VERSION, true );
			wp_enqueue_script( 'packery', $js_folder_url . '/library/packery.js', [], FUSION_BUILDER_VERSION, true );
			wp_enqueue_script( 'images-loaded', $js_folder_url . '/library/imagesLoaded.js', [], FUSION_BUILDER_VERSION, true );

			wp_enqueue_style( 'fusion-builder-frame-builder-css', FUSION_BUILDER_PLUGIN_URL . 'front-end/css/builder.css', false, FUSION_BUILDER_VERSION );

			// DiffDOM.
			wp_enqueue_script( 'diff-dom', FUSION_BUILDER_PLUGIN_URL . 'front-end/diffDOM.js', [], FUSION_BUILDER_VERSION, true );

			wp_enqueue_script( 'jquery-ui-overrides', FUSION_BUILDER_PLUGIN_URL . 'front-end/jquery-ui-overrides.js', [ 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-resizable' ], FUSION_BUILDER_VERSION, true );

			// If we're not debugging, load the combined script.
			if ( ( ! defined( 'FUSION_BUILDER_DEV_MODE' ) || ! FUSION_BUILDER_DEV_MODE ) && ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) ) {
				wp_enqueue_script( 'fusion_builder_frontend_combined', FUSION_BUILDER_PLUGIN_URL . 'front-end/fusion-frontend-combined.min.js', [ 'jquery', 'underscore', 'backbone', 'isotope', 'packery', 'diff-dom' ], FUSION_BUILDER_VERSION, true );
			} else {

				// Generator JS.
				wp_enqueue_script( 'fusion_builder_shortcode_generator', FUSION_BUILDER_PLUGIN_URL . 'front-end/fusion-shortcode-generator.js', [], FUSION_BUILDER_VERSION, true );

				// History.
				wp_enqueue_script( 'fusion_builder_history', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-history.js', [], FUSION_BUILDER_VERSION, true );

				// Globals.
				wp_enqueue_script( 'fusion_builder_globals', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-globals.js', [], FUSION_BUILDER_VERSION, true );

				// Settings helpers.
				wp_enqueue_script( 'fusion_builder_settings_helpers', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-settings-helpers.js', [], FUSION_BUILDER_VERSION, true );

				// Draggable helpers.
				wp_enqueue_script( 'fusion_builder_draggable_helpers', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-draggable-helpers.js', [], FUSION_BUILDER_VERSION, true );

				// Isotope helpers.
				wp_enqueue_script( 'fusion_builder_isotope_helpers', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-isotope-manager.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_toolbar', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-toolbar.js', [], FUSION_BUILDER_VERSION, true );

				// Models.
				wp_enqueue_script( 'fusion_builder_model_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-element.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_extra_shortcodes', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-extra-shortcodes.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_studio', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-studio.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_website_demos', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-website.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_dynamic_values', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-dynamic-values.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_dynamic_params', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-dynamic-params.js', [], FUSION_BUILDER_VERSION, true );

				// Collections.
				wp_enqueue_script( 'fusion_builder_collection_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/collections/collection-element.js', [], FUSION_BUILDER_VERSION, true );

				// Base view.
				wp_enqueue_script( 'fusion_builder_base', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-base.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_base_row', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-base-row.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_base_column', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-base-column.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_column', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-column.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_container', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-container.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_studio_import_modal', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-studio-import-modal.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_demo_import_modal', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-demo-import-modal.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_context_menu', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-context-menu.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_context_menu_inline', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-context-menu-inline.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-element.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_parent_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-parent-element.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_child_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-child-element.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_row', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-row.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_dyamic_selection', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-dynamic-selection.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_dyamic_data', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-dynamic-data.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_settings', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-element-settings.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_widget_settings', FUSION_BUILDER_PLUGIN_URL . 'js/views/view-base-widget-settings.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_settings-children', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-element-settings-parent.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_view_library_base', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-library-base.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_view_elements_library', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-library-elements.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_view_column_library', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-library-column.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_view_column_nested_library', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-library-nested-column.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_view_container_library', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-library-container.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_view_generator_library', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-generator-elements.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_view_column_nested', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-column-nested.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_view_row_nested', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-row-nested.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_blank_page', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-blank-page.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_front_element_preview', FUSION_BUILDER_PLUGIN_URL . 'js/views/view-element-preview.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_front_next_page', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-next-page.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_keyboard_shortcuts', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-keyboard-shortcuts.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_preferences', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-builder-preferences.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_library', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-library.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_template_helpers', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-template-helpers.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_inline_editor_helpers', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-inline-editor-helpers.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_callback_functions', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-callback-functions.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_styles', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-form-styles.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_off_canvas_styles', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-off-canvas-styles.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_bulk_add', FUSION_BUILDER_PLUGIN_URL . 'js/views/view-bulk-add.js', [], FUSION_BUILDER_VERSION, true );

				// Navigator.
				wp_enqueue_script( 'fusion_builder_navigator_model', FUSION_BUILDER_PLUGIN_URL . 'front-end/models/model-navigator.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_navigator_view', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-navigator.js', [], FUSION_BUILDER_VERSION, true );

				// App.
				wp_enqueue_script( 'fusion_builder_live_app', FUSION_BUILDER_PLUGIN_URL . 'front-end/front-end.js', [], FUSION_BUILDER_VERSION, true );

				// Element views.
				wp_enqueue_script( 'fusion_builder_counter_circle_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-child-counter-circle.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_counter_circles_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-child-counter-circles.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_gallery_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-gallery.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_menu_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-menu.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_gallery_item_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-gallery-child.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_separator_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-separator.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_title_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-title.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_testimonials_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-testimonials.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_testimonial_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-testimonial.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_tooltip_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-tooltip.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_sharingbox_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-sharingbox.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_section_separator_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-section-separator.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_modal_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-modal.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_code_block_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-code-block.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_alert_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-alert.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_audio_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-audio.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_map_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-google-map.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_fontawesome_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-font-awesome.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_button_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-button.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_image_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-image.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_layerslider_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-layerslider.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_lottie_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-lottie.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_slider_revolution_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-slider-revolution.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_convert_plus_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-convert-plus.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_person_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-person.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_video_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-video.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_views_counter_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-views-counter.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_vimeo_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-vimeo.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_post_slider_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-post-slider.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_user_login_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-user-login.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_user_register_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-user-register.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_user_lost_password_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-user-lost-password.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_woo_featured_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-featured-products-slider.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_highlight_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-highlight.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_syntax_highlighter_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-syntax-highlighter.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_tabs_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-tabs.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_tab_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-tab.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_table_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-table.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_progress_bar_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-progress-bar.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_scroll_progress_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-scroll-progress.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_recent_posts_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-recent-posts.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_woo_product_slider_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-product-slider.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_woo_product_grid_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-product-grid.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_woo_sorting_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-sorting.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_slider_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-media-slider.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_slide_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-slide.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_image_hotspots_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-image-hotspots.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_image_hotspot_point_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-image-hotspot-point.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_image_carousel_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-image-carousel.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_image_carousel_child_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-image-carousel-child.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_one_page_link_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-one-page-link.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_dropcap_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-dropcap.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_flip_boxes_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-flip-boxes.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_flip_box_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-flip-box.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_accordion_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-accordion.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_toggle_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-accordion-toggle.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_chart_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-chart.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_chart_dataset_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-chart-dataset.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_image_before_after_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-image-before-after.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_counters_box_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-counters-box.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_counter_box_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-counter-box.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_widget_area_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-widget-area.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_content_boxes_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-content-boxes.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_content_box_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-content-box.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_social_links_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-social-links.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_modal_text_link_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-modal-text-link.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_news_ticker_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-news-ticker.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_popover_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-popover.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_events_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-events.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_countdown_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-countdown.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_view_menu_anchor_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-menu-anchor.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_checklist_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-checklist.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_checklist_item_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-checklist-item.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_youtube_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-youtube.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_soundcloud_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-soundcloud.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_star_rating_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-star-rating.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_text_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-text.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_tagline_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-tagline-box.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_pricing_table_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-pricing-table.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_pricing_column_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-pricing-column.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_blog_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-blog.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_breadcrumbs_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-breadcrumbs.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_lightbox_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-lightbox.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_widget_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-widget.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_widget_content', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/widgets/view-widget-content.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_widget_revslider', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/widgets/view-revslider.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_widget_facebook', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/widgets/view-facebook.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_widget_wp_video', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/widgets/view-wp-video.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_search_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-search.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_base', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-form-base.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_password', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-password.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_email', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-email.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_text', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-text.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_phone', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-phone-number.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_hidden', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-hidden.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_honeypot', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-honeypot.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_number', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-number.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_checkbox', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-checkbox.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_radio', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-radio.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_date', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-date.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_range', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-range.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_recaptcha', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-recaptcha.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_textarea', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-textarea.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_upload', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-upload.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_time', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-time.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_image_select', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-image-select.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_form_image_select_input', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-image-select-input.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_select', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-select.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_form_rating', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-rating.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_form_notice', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-notice.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_form_submit', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/form/view-submit.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_post_cards', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-post-cards.js', [], FUSION_BUILDER_VERSION, true );

				wp_enqueue_script( 'fusion_builder_facebook_page', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-facebook-page.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_twitter_timeline', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-twitter-timeline.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_flickr', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-flickr.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_tagcloud', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-tagcloud.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_instagram', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-instagram.js', [], FUSION_BUILDER_VERSION, true );

				// Woo elements.
				wp_enqueue_script( 'fusion_builder_tb_woo_products_element', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/components/view-woo-products.js', [], FUSION_BUILDER_VERSION, true ); // Base Product class (Related, Upsells).
				wp_enqueue_script( 'fusion_builder_woo_cart_table', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-cart-table.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_woo_cart_totals', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-cart-totals.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_woo_cart_coupons', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-cart-coupons.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_woo_cart_shipping', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-cart-shipping.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_tb_woo_checkout_billing', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-checkout-billing.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_tb_woo_checkout_tabs', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-checkout-tabs.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_tb_woo_checkout_shipping', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-checkout-shipping.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_tb_woo_checkout_payment', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-checkout-payment.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_tb_woo_checkout_order_review', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-checkout-order-review.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_tb_woo_notices', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-notices.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_tb_woo_upsells', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-upsells.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_post_card_image', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-post-card-image.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_front_checkout_form', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/view-woo-checkout-form.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_post_card_cart', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-post-card-cart.js', [], FUSION_BUILDER_VERSION, true );
				wp_enqueue_script( 'fusion_builder_woo_mini_cart', FUSION_BUILDER_PLUGIN_URL . 'front-end/views/elements/view-woo-mini-cart.js', [], FUSION_BUILDER_VERSION, true );

				do_action( 'fusion_builder_enqueue_separate_live_scripts' );

			}

			// Localize Scripts.

			$localize_handle = ( ( ! defined( 'FUSION_LIBRARY_DEV_MODE' ) || ! FUSION_LIBRARY_DEV_MODE ) && ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) ) ? 'fusion_library_frontend_combined' : 'fusion_app';

			wp_localize_script(
				$localize_handle,
				'builderConfig',
				[
					'fusion_builder_plugin_dir' => FUSION_BUILDER_PLUGIN_URL,
					'allowed_post_types'        => FusionBuilder()->allowed_post_types(),
					'disable_encoding'          => get_option( 'avada_disable_encoding' ),
				]
			);

			do_action( 'fusion_builder_enqueue_live_scripts' );
		}

		/**
		 * Handle live-preview rendering in the front-end builder.
		 *
		 * @param bool $include_scripts Whether scripts should be included or not.
		 *
		 * @since 6.0
		 * @return bool
		 */
		public function include_rev_scripts( $include_scripts ) {
			return true;
		}

		/**
		 * Handles live-preview rendering in the front-end builder.
		 *
		 * @param bool $privacy_bar Whether privacy bar should be rendered or not.
		 *
		 * @since 6.0
		 * @return bool
		 */
		public function disable_privacy_bar( $privacy_bar ) {
			return false;
		}

		/**
		 * Disables query monitor in front-end builder mode.
		 *
		 * @param array $user_caps Array of user capabilities.
		 *
		 * @since 6.0
		 * @return array
		 */
		public function disable_qm( $user_caps ) {
			$user_caps['view_query_monitor'] = false;
			return $user_caps;
		}

		/**
		 * Test function to get shortcode output.
		 *
		 * @since 6.0.0
		 */
		public function get_shortcode_render() {
			check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
			$return_data = [];

			FusionBuilder()->set_global_shortcode_parent( wp_unslash( $_POST['cid'] ) );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput

			if ( isset( $_POST['shortcodes'] ) ) {
				$return_data['shortcodes'] = [];
				$post_shortcodes           = wp_unslash( $_POST['shortcodes'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				if ( ! empty( $post_shortcodes ) ) {
					foreach ( $post_shortcodes as $shortcode ) {
						$shortcode                               = wp_unslash( $shortcode );
						$return_data['shortcodes'][ $shortcode ] = do_shortcode( $shortcode );
					}
				}
			}

			$content = '';
			if ( isset( $_POST['content'] ) ) {
				$content = wp_unslash( $_POST['content'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			}
			$return_data['content'] = do_shortcode( $content );

			echo wp_json_encode( $return_data );
			wp_die();
		}

		/**
		 * Create a new post.
		 *
		 * @since 6.0.0
		 */
		public function create_post() {

			check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

			$post_type        = ( isset( $_POST['post_type'] ) ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : 'post';
			$post_type_object = get_post_type_object( $post_type );
			if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
				return;
			}

			$sidebar_option_names = ( class_exists( 'Avada' ) ? avada_get_sidebar_post_meta_option_names( $post_type ) : [] );

			// Create post object.
			$my_post = [
				'post_title'   => __( 'Draft', 'fusion-builder' ) . ' ' . $post_type_object->labels->singular_name,
				'post_content' => '',
				'post_status'  => 'draft',
				'post_type'    => $post_type,
				'meta_input'   => [
					$sidebar_option_names[0] => [ 'default_sidebar' ],
					$sidebar_option_names[1] => [ 'default_sidebar' ],
				],
			];

			if ( 'page' === $post_type && class_exists( 'Avada' ) && '100_width' === Avada()->settings->get( 'page_template' ) ) {
				$my_post['page_template'] = '100-width.php';
			}

			// Insert the post into the database.
			$post_id = wp_insert_post( apply_filters( 'fusion_live_editor_draft', $my_post ) );

			$return_data = [
				'post_id'   => $post_id,
				'permalink' => get_permalink( $post_id ),
			];

			echo wp_json_encode( $return_data );
			die();
		}

		/**
		 * Enqueue additional editor scripts if they are needed.
		 *
		 * @since 2.0.0
		 */
		public function enqueue_wp_editor_scripts() {

			if ( ! class_exists( '_WP_Editors' ) ) {
				require wp_normalize_path( ABSPATH . WPINC . '/class-wp-editor.php' );
			}

			$set = _WP_Editors::parse_settings( 'fusion_builder_editor', [] );

			if ( ! current_user_can( 'upload_files' ) ) {
				$set['media_buttons'] = false;
			}

			_WP_Editors::editor_settings( 'fusion_builder_editor', $set );

		}

		/**
		 * Save post content.
		 *
		 * @access public
		 * @since 2.0.0
		 * @param array $post The post args.
		 * @return array
		 */
		public function save_builder_content( $post ) {
			$post_id      = Fusion_App()->get_data( 'post_id' );
			$post_content = Fusion_App()->get_data( 'post_content' );
			if ( false !== $post_content && $this->editable_posttype( $post_id ) ) {
				$post['post_content'] = $post_content;
			}
			return $post;
		}

		/**
		 * Adds the FB frontend link in the posts list links.
		 *
		 * @since 2.0
		 * @access public
		 * @param  array   $actions Post actions.
		 * @param  WP_Post $post    Edited post.
		 * @return array            Updated post actions.
		 */
		public function add_edit_link( $actions, $post ) {

			// If user can't edit early exit.
			if ( ! isset( $actions['edit'] ) || ( isset( $_GET['post_status'] ) && 'trash' === $_GET['post_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				return $actions;
			}

			$live_editor = apply_filters( 'fusion_load_live_editor', true );

			// Add link if fusion_builder_status is set to active.
			if ( 'active' === get_post_meta( $post->ID, 'fusion_builder_status', true ) && $live_editor ) {

				/* translators: The title. */
				$actions['fusion_builder_live'] = '<a href="' . esc_url_raw( add_query_arg( 'fb-edit', '1', get_the_permalink( $post->ID ) ) ) . '" aria-label="' . sprintf( esc_attr__( 'Edit %s with Avada Live', 'fusion-builder' ), '&#8220;' . get_the_title( $post->ID ) . '&#8221;' ) . '">' . esc_html__( 'Avada Live', 'fusion-builder' ) . '</a>';
			}
			return $actions;
		}

		/**
		 * Filters tabs for single products.
		 *
		 * @access public
		 * @since 2.0
		 * @param array $tabs The tabs array.
		 * @return array
		 */
		public function product_tabs( $tabs ) {
			if ( ! isset( $tabs['description'] ) && fusion_is_preview_frame() ) {
				$tabs['description'] = [
					'title'    => esc_html__( 'Description', 'fusion-builder' ),
					'priority' => 10,
					'callback' => 'woocommerce_product_description_tab',
				];
			}
			return $tabs;
		}

		/**
		 * Returns placeholder if content is empty.
		 *
		 * @access public
		 * @since 3.2
		 * @param array $content The component content.
		 * @param array $handle  The component handle.
		 * @param array $args    The args array.
		 * @return string
		 */
		public function woo_placeholder( $content, $handle, $args ) {
			if ( empty( $content ) ) {
				switch ( $handle ) {
					case 'fusion_tb_woo_stock':
						ob_start();

						wc_get_template(
							'single-product/stock.php',
							[
								'class'        => 'out-of-stock',
								'availability' => __( 'Out of stock', 'woocommerce' ),
							]
						);

						$content = ob_get_clean();
						break;
					case 'fusion_tb_woo_rating':
						$content = '<div class="woocommerce-product-rating">' . wc_get_rating_html( 3.5, 1 );
						if ( comments_open() ) {
							/* Translators: Reviews count. */
							$content .= '<a href="#reviews" class="woocommerce-review-link" rel="nofollow">' . sprintf( __( '<span class="count">%s</span> customer review', 'fusion-builder' ), __( 'Example', 'fusion-builder' ) ) . '</a>';
						}
						$content .= '</div>';
						break;
					default:
						$content = '<div class="fusion-builder-placeholder">' . __( 'Element content will render here.', 'fusion-builder' ) . '</div>';
				}
			}
			return $content;
		}
	}
}

add_action( 'fusion_load_internal_module', 'load_builder_front_class', 4 );

/**
 * Instantiate Fusion_Builder_Front class.
 */
function load_builder_front_class() {
	Fusion_Builder_Front::get_instance();
}

/**
 * Loop for front-options.
 *
 * @since 2.0.0
 * @param array $params An array of arguments.
 */
function fusion_element_front_options_loop( $params ) {
	$preferences        = Fusion_App()->preferences->get_preferences();
	$descriptions_class = '';
	$descriptions_css   = '';

	if ( isset( $preferences['descriptions'] ) && 'show' === $preferences['descriptions'] ) {
		$descriptions_class = ' active';
		$descriptions_css   = ' style="display: block;"';
	}
	?>
	<#
	function fusion_display_option( param ) {
		var hasDynamic,
			supportsDynamic,
			escape_html,
			hasResponsive,
			supportsGlobal,
			responsiveIcons = {
				'large': 'desktop',
				'medium': 'tablet',
				'small': 'mobile'
			};

		option_value = 'undefined' !== typeof atts.added ? param.value : atts.params[param.param_name];

		if ( 'element_content' === param.param_name && 'undefined' !== typeof atts.inlineElement && 'undefined' !== typeof atts.added ) {
			option_value = atts.params[param.param_name];
		}
		if ( param.type == 'select' || param.type == 'multiple_select' || param.type == 'radio_button_set' || param.type == 'checkbox_button_set' || param.type == 'subgroup' ) {
			option_value = ( 'undefined' !== typeof atts.added || '' === atts.params[param.param_name] || 'undefined' === typeof atts.params[param.param_name] ) ? param.default : atts.params[param.param_name];
		};

		if ( ( 'code' === param.type && 1 === Number( FusionApp.settings.disable_code_block_encoding ) ) || 'raw_textarea' === param.type || 'raw_text' === param.type ) {
			try {
					if ( FusionPageBuilderApp.base64Encode( FusionPageBuilderApp.base64Decode( option_value ) ) === option_value ) {
						option_value = FusionPageBuilderApp.base64Decode( option_value );
					}
				} catch ( e ) {
					console.warn( 'Something went wrong! Error triggered - ' + e );
			}
		}

		option_value = _.unescape(option_value);

		hidden = 'undefined' !== typeof param.hidden ? ' hidden' : '';

		// Used to flag that option should be escaped.
		escape_html = 'undefined' !== typeof param.escape_html && true === param.escape_html ? ' escape_html' : '';

		childDependency = 'undefined' !== typeof param.child_dependency ? ' has-child-dependency' : '';
		tabGroup        = 'undefined' !== typeof param.tab ? ' fusion-subgroup-item ' + param.tab : '';
		optionMap       = '' !== param.option_map ? param.option_map : '';

		// Change radio-buttonset to select when needed.
		if ( 'radio_button_set' === param.type && 'object' !== typeof param.icons ) {
			var radioButtonSetLength = 0;

			if ( Object.keys( param.value ).length > 5 ) {
				param.type = 'select';
			} else {
				_.each( param.value, function( label ) {
					radioButtonSetLength += label.length * 10 + 24;
				} );
				if ( 310 <= radioButtonSetLength ) {
					param.type = 'select';
				}
			}
		}
		supportsDynamic = 'undefined' !== typeof param.dynamic_data && true === param.dynamic_data ? true : false;
		hasDynamic      = 'object' === typeof atts.dynamic_params && 'undefined' !== typeof atts.dynamic_params[ param.param_name ] && supportsDynamic;
		hasResponsive   = 'undefined' !== typeof param.responsive ? true : false;
		responsiveState = 'undefined' !== typeof param.responsive ? 'responsive-state-' + param.responsive.state : '';
		supportsGlobal  = 'undefined' !== typeof param.global;
		#>
		<li data-option-id="{{ param.param_name }}" data-option-type="{{ param.type }}" class="fusion-builder-option {{ param.type }}{{ hidden }}{{ childDependency }}{{tabGroup}} {{responsiveState}} {{optionMap}}{{escape_html}}" data-dynamic="{{ hasDynamic }}" data-dynamic-selection="false">

			<div class="option-details">
				<div class="option-details-inner">
					<# if ( 'undefined' !== typeof param.heading ) { #>
						<h3>
							{{ param.heading }}
						</h3>
						<ul class="fusion-panel-options">
							<# if ( 'subgroup' === param.type ) {
									const subgroupIconClass = 'collapsed' === window.FusionApp.preferencesData.options_subtabs ? 'active' : '';
								#>
								<li class="fusion-builder-toggle-subgroup {{ subgroupIconClass }}"> <a href="JavaScript:void(0);" class="fusion-range-default" data-default="{{ param.default }}"><i class="fusiona-chevron-small-down" aria-hidden="true"></i></a> <span class="fusion-elements-option-tooltip fusion-tooltip-toggle-subgroup"><?php esc_html_e( 'Toggle Subgroup', 'fusion-builder' ); ?></span></li>
							<# } #>
							<# if ( 'undefined' !== typeof param.description ) { #>
								<li> <a href="JavaScript:void(0);" class="fusion-panel-description<?php echo esc_attr( $descriptions_class ); ?>"><i class="fusiona-question-circle" aria-hidden="true"></i></a> <span class="fusion-elements-option-tooltip fusion-tooltip-description">{{ fusionBuilderText.fusion_panel_desciption_toggle }}</span></li>
							<# } #>
							<# if ( 'undefined' !== param.default_option && '' !== param.default_option && param.default_option ) { #>
								<li><a href="JavaScript:void(0);"><span class="fusion-panel-shortcut" data-fusion-option="{{ param.default_option }}"><i class="fusiona-cog" aria-hidden="true"></i></a><span class="fusion-elements-option-tooltip fusion-tooltip-global-settings"><?php esc_html_e( 'Global Options', 'fusion-builder' ); ?></span></li>
							<# } #>
							<# if ( 'undefined' !== param.to_link && '' !== param.to_link && param.to_link ) { #>
								<li><a href="JavaScript:void(0);"><span class="fusion-panel-shortcut" data-fusion-option="{{ param.to_link }}"><i class="fusiona-cog" aria-hidden="true"></i></a><span class="fusion-elements-option-tooltip fusion-tooltip-global-settings"><?php esc_html_e( 'Global Options', 'fusion-builder' ); ?></span></li>
							<# } #>
							<# if ( ( 'undefined' !== typeof param.description && 'undefined' !== typeof param.default && -1 !== param.description.indexOf( 'fusion-builder-default-reset' ) ) || ( param.reset && 'undefined' !== typeof param.default ) ) { #>
								<li class="fusion-builder-default-reset"> <a href="JavaScript:void(0);" class="fusion-range-default" data-default="{{ param.default }}"><i class="fusiona-undo" aria-hidden="true"></i></a> <span class="fusion-elements-option-tooltip fusion-tooltip-reset-defaults"><?php esc_html_e( 'Reset To Default', 'fusion-builder' ); ?></span></li>
							<# } #>

							<# if ( 'undefined' !== typeof param.description && -1 === param.description.indexOf( 'fusion-builder-default-reset' ) && 'image_focus_point' === param.type ) { #>
								<li class="fusion-builder-default-reset"> <a href="JavaScript:void(0);" class="fusion-range-default" data-default="{{ param.default }}"><i class="fusiona-undo" aria-hidden="true"></i></a> <span class="fusion-elements-option-tooltip fusion-tooltip-reset-defaults"><?php esc_html_e( 'Reset To Default', 'fusion-builder' ); ?></span></li>
							<# } #>
							<# if ( 'undefined' !== typeof param.preview ) { #>
								<#
									dataType     = 'undefined' !== typeof param.preview.type     ? param.preview.type       : '';
									dataSelector = 'undefined' !== typeof param.preview.selector ? param.preview.selector   : '';
									dataToggle   = 'undefined' !== typeof param.preview.toggle   ? param.preview.toggle     : '';
									dataAppend   = 'undefined' !== typeof param.preview.append   ? param.preview.append     : '';
								#>
								<li><a class="option-preview-toggle" href="JavaScript:void(0);" aria-label="<?php esc_attr_e( 'Preview', 'fusion-builder' ); ?>" data-type="{{ dataType }}" data-selector="{{ dataSelector }}" data-toggle="{{ dataToggle }}" data-append="{{ dataAppend }}"><i class="fusiona-eye" aria-hidden="true"></i></a><span class="fusion-elements-option-tooltip fusion-tooltip-preview"><?php esc_html_e( 'Preview', 'fusion-builder' ); ?></span></li>
							<# }; #>

							<# if ( hasResponsive ) { #>
								<li class="fusion-responsive-panel"><a class="option-has-responsive" href="JavaScript:void(0);" aria-label="{{ fusionBuilderText.fusion_panel_responsive_toggle }}"><i class="fusiona-{{responsiveIcons[param.responsive.state]}}" aria-hidden="true"></i></a><span class="fusion-elements-option-tooltip fusion-tooltip-preview">{{ fusionBuilderText.fusion_panel_responsive_toggle }}</span>
									<ul class="fusion-responsive-options">
										<li><a href="JavaScript:void(0);" data-indicator="desktop"><i class="fusiona-desktop" aria-hidden="true"></i></a></li>
										<li><a href="JavaScript:void(0);" data-indicator="tablet"><i class="fusiona-tablet" aria-hidden="true"></i></a></li>
										<li><a href="JavaScript:void(0);" data-indicator="mobile"><i class="fusiona-mobile" aria-hidden="true"></i></a></li>
									</ul>
								</li>
							<# } #>

							<# if ( supportsDynamic ) { #>
								<li><a class="option-dynamic-content" href="JavaScript:void(0);" aria-label="<?php esc_attr_e( 'Dynamic Content', 'fusion-builder' ); ?>"><i class="fusiona-dynamic-data" aria-hidden="true"></i></a><span class="fusion-elements-option-tooltip fusion-tooltip-preview"><?php esc_html_e( 'Dynamic Content', 'fusion-builder' ); ?></span></li>
							<# } #>

							<# if ( supportsGlobal ) { #>
								<li><a class="option-global-typography awb-quick-set" href="JavaScript:void(0);" aria-label="<?php esc_attr_e( 'Global Typography', 'fusion-builder' ); ?>"><i class="fusiona-globe" aria-hidden="true"></i></a><span class="fusion-elements-option-tooltip fusion-tooltip-preview"><?php esc_html_e( 'Global Typography', 'fusion-builder' ); ?></span></li>
							<# } #>
						</ul>
					<# }; #>
				</div>

				<# if ( 'undefined' !== typeof param.description ) { #>
					<p class="description"<?php echo $descriptions_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>{{{ param.description }}}</p>
				<# }; #>
			</div>

			<div class="option-field fusion-builder-option-container">
				<?php
				$field_types = [
					'textarea',
					'textfield',
					'range',
					'colorpickeralpha',
					'colorpicker',
					'column_width',
					'select',
					'upload',
					'uploadfile',
					'uploadattachment',
					'tinymce',
					'iconpicker',
					'multiple_select',
					'multiple_upload',
					'checkbox_button_set',
					'subgroup',
					'radio_button_set',
					'radio_image_set',
					'link_selector',
					'date_time_picker',
					'upload_images',
					'dimension',
					'code',
					'typography',
					'repeater',
					'raw_textarea',
					'raw_text',
					'sortable',
					'sortable_text',
					'connected_sortable',
					'info',
					'form_options',
					'fusion_logics',
					'ajax_select',
					'image_focus_point',
					'toggle',
				];
				?>
				<?php
					$fields = apply_filters( 'fusion_builder_fields', $field_types );
				?>

				<?php foreach ( $fields as $field_type ) : ?>
					<?php if ( is_array( $field_type ) && ! empty( $field_type ) ) : ?>
						<# if ( '<?php echo esc_attr( $field_type[0] ); ?>' == param.type ) { #>
							<?php include wp_normalize_path( $field_type[1] ); ?>
						<# }; #>
					<?php else : ?>
					<# if ( '<?php echo esc_attr( $field_type ); ?>' == param.type ) { #>
						<?php include FUSION_LIBRARY_PATH . '/inc/fusion-app/templates/options/' . str_replace( '_', '-', $field_type ) . '.php'; ?>
					<# }; #>
					<?php endif; ?>
				<?php endforeach; ?>

			</div>

			<# if ( supportsDynamic ) { #>
			<div class="fusion-dynamic-content">
				<?php include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/dynamic-data.php'; ?>
			</div>
			<div class="fusion-dynamic-selection">
			</div>
			<# } #>
		</li>
		<#
	}
	#>

	<ul class="fusion-builder-module-settings {{ atts.element_type }}" data-element="{{ atts.element_type }}" data-element-cid="{{ atts.cid }}">
		<#
		var SubGroup,
			activeSubGroup;
		_.each( <?php echo $params; // phpcs:ignore WordPress.Security.EscapeOutput ?>, function( param, index ) {
			if ( 'subgroup' !== param.type ) {
				fusion_display_option( param );
			} else {
				SubGroup = param.default;
				fusion_display_option( param );
				#>
				<div class="fusion-subgroups-content-wrap">
				<#
				_.each( param.subgroups, function( subgroup, tab ) {
					activeSubGroup = tab === SubGroup ? ' active' : '';
					#>
					<ul class="fusion-subgroup-content fusion-subgroup-{{tab}}{{activeSubGroup}}" data-group="{{param.param_name}}">
						<#
						_.each( subgroup, function( item ) {
							fusion_display_option( item );
						} );
						#>
					</ul>
					<#
				} );
				#>
				</div>
				<#
			}
		} ); #>
	</ul>
	<?php
}

/**
 * Get the image data.
 *
 * @since 2.0.0
 * @param int        $post_id                   The post-ID.
 * @param array      $post_featured_image_sizes An array of image sizes.
 * @param string     $post_permalink            The post-permalink.
 * @param bool       $display_placeholder_image If we should display a placeholder or not.
 * @param bool       $display_woo_price         If we should display WooCommerce prices or not.
 * @param bool       $display_woo_buttons       If we should display WooCommerce buttons or not.
 * @param string     $display_post_categories   How to display post-categories.
 * @param string     $display_post_title        How to display the post-title.
 * @param string     $type                      Context.
 * @param int|string $gallery_id                The gallery ID (if one exists).
 * @param string     $display_rollover          If a rollover should be displayed (yes/no).
 * @param bool       $display_woo_rating        If we should display WooCommerce ratings or not.
 * @return array
 */
function fusion_get_image_data( $post_id, $post_featured_image_sizes = [], $post_permalink = '', $display_placeholder_image = false, $display_woo_price = false, $display_woo_buttons = false, $display_post_categories = 'default', $display_post_title = 'default', $type = '', $gallery_id = '', $display_rollover = 'yes', $display_woo_rating = false ) {

	global $product;

	// Get the featured images.
	$featured_images = [];
	foreach ( $post_featured_image_sizes as $post_featured_image_size ) {

		if ( 'related' === $type && 'fixed' === $post_featured_image_size && get_post_thumbnail_id( $post_id ) ) {

			$image = Fusion_Image_Resizer::image_resize(
				[
					'width'  => '500',
					'height' => '383',
					'url'    => wp_get_attachment_url( get_post_thumbnail_id( $post_id ) ),
					'path'   => get_attached_file( get_post_thumbnail_id( $post_id ) ),
				]
			);

			$scrset         = ( isset( $image['retina_url'] ) && $image['retina_url'] ) ? ' srcset="' . $image['url'] . ' 1x, ' . $image['retina_url'] . ' 2x"' : '';
			$featured_image = '<img src="' . esc_url_raw( $image['url'] ) . '" alt="' . the_title_attribute( 'post=' . $post_id ) . '" />';

		} else {

			if ( has_post_thumbnail( $post_id ) ) {
				$featured_image = get_the_post_thumbnail( $post_id, $post_featured_image_size );

			} elseif ( fusion_get_option( 'video' ) ) {
				$image_size_class  = ( isset( $image_size_class ) ) ? $image_size_class : '';
				$image_size_class .= ' fusion-video';

				$featured_image  = '<div class="full-video">';
				$featured_image .= fusion_get_option( 'video' );
				$featured_image .= '</div>';

			} elseif ( $display_placeholder_image ) {
				ob_start();
				do_action( 'avada_placeholder_image', $post_featured_image_size );
				$featured_image = ob_get_clean();
			}
		}
		if ( isset( $featured_image ) ) {
			$featured_images[ $post_featured_image_size ] = $featured_image;
		}
	}
	$enable_rollover = apply_filters( 'fusion_builder_image_rollover', true );

	// Portfolio defaults.
	$link_icon_target = apply_filters( 'fusion_builder_link_icon_target', '', $post_id );
	$video_url        = apply_filters( 'fusion_builder_video_url', '', $post_id );

	// Blog defaults.
	$link_icon_url     = apply_filters( 'fusion_builder_link_icon_url', '', $post_id );
	$post_links_target = apply_filters( 'fusion_builder_post_links_target', '', $post_id );

	// Set defaults for Avada Builder ( Global Options ).
	$cats_image_rollover  = apply_filters( 'fusion_builder_cats_image_rollover', false );
	$title_image_rollover = apply_filters( 'fusion_builder_title_image_rollover', false );

	// Portfolio defaults.
	$portfolio_link_icon_target = apply_filters( 'fusion_builder_portfolio_link_icon_target', false, $post_id );

	// Retrieve the permalink if it is not set.
	$post_permalink = ( ! $post_permalink ) ? get_permalink( $post_id ) : $post_permalink;

	// Check if Global Options are used as base or if there is an override for post categories.
	if ( 'default' === $display_post_categories ) {
		$display_post_categories = fusion_library()->get_option( 'cats_image_rollover' );
	} elseif ( 'enable' === $display_post_categories ) {
		$display_post_categories = true;
	} elseif ( 'disable' === $display_post_categories ) {
		$display_post_categories = false;
	} else {
		$display_post_categories = $cats_image_rollover;
	}

	// Check if Global Options are used as base or if there is an override for post title.
	if ( 'default' === $display_post_title ) {
		$display_post_title = fusion_library()->get_option( 'title_image_rollover' );
	} elseif ( 'enable' === $display_post_title ) {
		$display_post_title = true;
	} elseif ( 'disable' === $display_post_title ) {
		$display_post_title = false;
	} else {
		$display_post_title = $title_image_rollover;
	}

	// Set the link and the link text on the link icon to a custom url if set in page options.
	if ( $link_icon_url ) {
		$icon_permalink       = $link_icon_url;
		$icon_permalink_title = $link_icon_url;
	} else {
		$icon_permalink       = $post_permalink;
		$icon_permalink_title = get_the_title( $post_id );
	}

	// Set the link target to blank if the option is set.
	$link_target = ( 'yes' === $link_icon_target || 'yes' === $post_links_target || ( 'avada_portfolio' === get_post_type() && $portfolio_link_icon_target ) ) ? ' target="_blank"' : '';

	$post_type = get_post_type( $post_id );

	$image_size_class = '';

	$full_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
	if ( ! is_array( $full_image ) ) {
		$full_image = [
			0 => '',
		];
	}
	$full_image = $full_image[0];
	if ( $video_url ) {
		$full_image        = $video_url;
		$image_size_class .= ' fusion-video';
	}

	$lightbox_content = ( 'individual' === fusion_library()->get_option( 'lightbox_behavior' ) ) ? avada_featured_images_lightbox( $post_id ) : '';
	$data_rel         = ( 'individual' === fusion_library()->get_option( 'lightbox_behavior' ) ) ? 'iLightbox[gallery' . $post_id . ']' : 'iLightbox[gallery' . $gallery_id . ']';

	$title = get_the_title( $post_id );

	// Determine the correct taxonomy.
	$post_taxonomy = '';
	if ( 'post' === get_post_type( $post_id ) ) {
		$post_taxonomy = 'category';
	} elseif ( 'avada_portfolio' === get_post_type( $post_id ) ) {
		$post_taxonomy = 'portfolio_category';
	} elseif ( 'product' === get_post_type( $post_id ) ) {
		$post_taxonomy = 'product_cat';
	}
	$terms = get_the_term_list( $post_id, $post_taxonomy, '<div class="fusion-rollover-categories">', ', ', '</div>' );

	$price         = false;
	$rating        = false;
	$buttons       = false;
	$cart_url      = false;
	$sale          = false;
	$items_in_cart = [];

	if ( class_exists( 'WooCommerce' ) && ! is_null( $product ) && method_exists( $product, 'is_purchasable' ) && $product->is_purchasable() ) {
		ob_start();
		fusion_wc_get_template( 'loop/rating.php' );
		$rating = ob_get_clean();

		ob_start();
		fusion_wc_get_template( 'loop/price.php' );
		$price = ob_get_clean();

		ob_start();
		do_action( 'avada_woocommerce_buttons_on_rollover' );
		$buttons = ob_get_clean();

		ob_start();
		woocommerce_show_product_sale_flash();
		$sale = ob_get_clean();

		$items_in_cart = fusion_library()->woocommerce->get_cart_products_ids();

		$cart_url = esc_url_raw( wc_get_cart_url() );
	}

	return [
		'post_id'                    => $post_id,
		'post_featured_image_size'   => $post_featured_image_size,
		'post_permalink'             => $post_permalink,
		'featured_images'            => $featured_images,
		'enable_rollover'            => $enable_rollover,
		'image_size_class'           => $image_size_class,
		'display_placeholder_image'  => $display_placeholder_image,
		'display_woo_price'          => $display_woo_price,
		'display_woo_buttons'        => $display_woo_buttons,
		'display_post_categories'    => $display_post_categories,
		'display_post_title'         => $display_post_title,
		'type'                       => $type,
		'gallery_id'                 => $gallery_id,
		'display_rollover'           => $display_rollover,
		'display_woo_rating'         => $display_woo_rating,
		'items_in_cart'              => $items_in_cart,
		'title'                      => $title,
		'terms'                      => $terms,
		'lightbox_content'           => $lightbox_content,
		'data_rel'                   => $data_rel,
		'full_image'                 => $full_image,
		'post_type'                  => $post_type,
		'link_target'                => $link_target,
		'image_rollover_icons'       => apply_filters( 'fusion_builder_image_rollover_icons', fusion_get_option( 'image_rollover_icons', false, $post_id ) ),
		'icon_permalink'             => $icon_permalink,
		'icon_permalink_title'       => $icon_permalink_title,
		'link_icon_target'           => $link_icon_target,
		'video_url'                  => $video_url,
		'link_icon_url'              => $link_icon_url,
		'post_links_target'          => $post_links_target,
		'cats_image_rollover'        => $cats_image_rollover,
		'title_image_rollover'       => $title_image_rollover,
		'portfolio_link_icon_target' => $portfolio_link_icon_target,
		'rating'                     => $rating,
		'price'                      => $price,
		'buttons'                    => $buttons,
		'cart_url'                   => $cart_url,
		'sale'                       => $sale,
	];
}

/**
 * Get meta data for post.
 *
 * @since 2.0.0
 * @param int $post_id The post-ID.
 * @return array
 */
function fusion_get_meta_data( $post_id ) {
	$fusion_settings = awb_get_fusion_settings();

	$disable_date_rich_snippet_pages = $fusion_settings->get( 'disable_date_rich_snippet_pages' );

	$post_meta = fusion_data()->post_meta( get_queried_object_id() )->get( 'post_meta' );

	ob_start();
	the_author_posts_link();
	$author_post_link = ob_get_clean();

	$date_format    = $fusion_settings->get( 'date_format' );
	$date_format    = $date_format ? $date_format : get_option( 'date_format' );
	$formatted_date = get_the_time( $date_format );

	ob_start();
	the_category( ', ' );
	$categories = ob_get_clean();

	ob_start();
	the_tags( '' );
	$tags = ob_get_clean();

	ob_start();
	comments_popup_link( esc_html__( '0 Comments', 'fusion-builder' ), esc_html__( '1 Comment', 'fusion-builder' ), esc_html__( '% Comments', 'fusion-builder' ) );
	$comments = ob_get_clean();

	return [
		'post_meta'                       => $post_meta,
		'author_post_link'                => $author_post_link,
		'formatted_date'                  => $formatted_date,
		'categories'                      => $categories,
		'tags'                            => $tags,
		'comments'                        => $comments,
		'disable_date_rich_snippet_pages' => $disable_date_rich_snippet_pages,
	];
}

/**
 * Get post content data for post.
 *
 * @since 2.0.0
 * @param string  $shortcode shortcode checking from.
 * @param boolean $get_full whether or not to get full contents.
 * @return array
 */
function fusion_get_content_data( $shortcode = '', $get_full = true ) {
	$fusion_settings = awb_get_fusion_settings();

	$full_content = '';
	if ( $get_full ) {
		$content = get_the_content();
		if ( '' === $shortcode || ! has_shortcode( $content, $shortcode ) ) {
			$full_content = apply_filters( 'the_content', $content );
		}
	}

	$excerpt_stripped     = fusion_builder_get_post_content_excerpt( 9999999, true );
	$excerpt_non_stripped = fusion_builder_get_post_content_excerpt( 9999999, false );

	$excerpt_base = fusion_get_option( 'excerpt_base' );

	$read_more = '';
	if ( $fusion_settings->get( 'disable_excerpts' ) ) {

		$read_more_text = $fusion_settings->get( 'excerpt_read_more_symbol' );
		if ( '' === $read_more_text ) {
			$read_more_text = '&#91;...&#93;';
		}

		$read_more_text = apply_filters( 'fusion_blog_read_more_excerpt', $read_more_text );

		if ( $fusion_settings->get( 'link_read_more' ) ) {
			$read_more = ' <a href="' . get_permalink( get_the_ID() ) . '">' . $read_more_text . '</a>';
		} else {
			$read_more = ' ' . $read_more_text;
		}
	}

	return [
		'full_content'     => $full_content,
		'excerpt_stripped' => $excerpt_stripped,
		'excerpt'          => $excerpt_non_stripped,
		'read_more'        => $read_more,
		'excerpt_base'     => $excerpt_base,
	];
}

/**
 * Returns the context specific values for parent/child elements.
 *
 * @since 2.0.0
 * @param string $context Context of the needed values.
 * @param array  $parent Parent values.
 * @param array  $child Child values.
 * @return array Either the $parent, the $child or a merged array of both.
 */
function fusion_get_context_specific_values( $context = '', $parent = [], $child = [] ) {
	if ( 'parent' === $context ) {
		return $parent;
	} elseif ( 'child' === $context ) {
		return $child;
	} else {
		return [
			'parent' => $parent,
			'child'  => $child,
		];
	}
}

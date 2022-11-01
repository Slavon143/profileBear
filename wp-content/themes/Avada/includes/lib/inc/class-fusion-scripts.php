<?php
/**
 * Register default scripts.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * Registers scripts.
 */
class Fusion_Scripts {

	/**
	 * JS folder URL.
	 *
	 * @static
	 * @access public
	 * @since 1.0.3
	 * @var string
	 */
	public static $js_folder_url;

	/**
	 * JS folder path.
	 *
	 * @static
	 * @access public
	 * @since 1.0.3
	 * @var string
	 */
	public static $js_folder_path;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$path = ( true === FUSION_LIBRARY_DEV_MODE ) ? '' : '/min';

		self::$js_folder_url  = FUSION_LIBRARY_URL . '/assets' . $path . '/js';
		self::$js_folder_path = FUSION_LIBRARY_PATH . '/assets' . $path . '/js';

		add_action( 'wp', [ $this, 'init' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
	}

	/**
	 * Runs on init.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {

		$this->register_scripts();
		$this->enqueue_scripts();
		$this->localize_scripts();

	}

	/**
	 * An array of our scripts.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @return void
	 */
	protected function register_scripts() {
		global $fusion_library_latest_version;

		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

		$scripts = [
			[
				'cssua',
				self::$js_folder_url . '/library/cssua.js',
				self::$js_folder_path . '/library/cssua.js',
				[],
				'2.1.28',
				true,
			],
			[
				'modernizr',
				self::$js_folder_url . '/library/modernizr.js',
				self::$js_folder_path . '/library/modernizr.js',
				[],
				'3.3.1',
				true,
			],
			[
				'fusion',
				self::$js_folder_url . '/general/fusion.js',
				self::$js_folder_path . '/general/fusion.js',
				[],
				$fusion_library_latest_version,
				true,
			],
			[
				'isotope',
				self::$js_folder_url . '/library/isotope.js',
				self::$js_folder_path . '/library/isotope.js',
				[ 'jquery' ],
				'3.0.4',
				true,
			],
			[
				'packery',
				self::$js_folder_url . '/library/packery.js',
				self::$js_folder_path . '/library/packery.js',
				[ 'jquery', 'isotope' ],
				'2.0.0',
				true,
			],

			// Lazy Loading.
			[
				'lazysizes',
				self::$js_folder_url . '/library/lazysizes.js',
				self::$js_folder_path . '/library/lazysizes.js',
				[ 'jquery' ],
				'4.1.5',
				true,
			],

			// Bootstrap.
			[
				'bootstrap-transition',
				self::$js_folder_url . '/library/bootstrap.transition.js',
				self::$js_folder_path . '/library/bootstrap.transition.js',
				[],
				'3.3.6',
				true,
			],
			[
				'bootstrap-tooltip',
				self::$js_folder_url . '/library/bootstrap.tooltip.js',
				self::$js_folder_path . '/library/bootstrap.tooltip.js',
				[ 'bootstrap-transition' ],
				'3.3.5',
				true,
			],
			[
				'bootstrap-popover',
				self::$js_folder_url . '/library/bootstrap.popover.js',
				self::$js_folder_path . '/library/bootstrap.popover.js',
				[ 'bootstrap-tooltip', 'cssua' ],
				'3.3.5',
				true,
			],
			[
				'bootstrap-collapse',
				self::$js_folder_url . '/library/bootstrap.collapse.js',
				self::$js_folder_path . '/library/bootstrap.collapse.js',
				[ 'bootstrap-transition' ],
				'3.1.1',
				true,
			],
			[
				'bootstrap-modal',
				self::$js_folder_url . '/library/bootstrap.modal.js',
				self::$js_folder_path . '/library/bootstrap.modal.js',
				[ 'bootstrap-transition' ],
				'3.1.1',
				true,
			],
			[
				'bootstrap-tab',
				self::$js_folder_url . '/library/bootstrap.tab.js',
				self::$js_folder_path . '/library/bootstrap.tab.js',
				[ 'bootstrap-transition' ],
				'3.1.1',
				true,
			],
			// jQuery.
			[
				'jquery-request-animation-frame',
				self::$js_folder_url . '/library/jquery.requestAnimationFrame.js',
				self::$js_folder_path . '/library/jquery.requestAnimationFrame.js',
				[ 'jquery' ],
				'1',
				true,
			],
			[
				'jquery-caroufredsel',
				self::$js_folder_url . '/library/jquery.carouFredSel.js',
				self::$js_folder_path . '/library/jquery.carouFredSel.js',
				[ 'jquery' ],
				'6.2.1',
				true,
			],
			[
				'jquery-easing',
				self::$js_folder_url . '/library/jquery.easing.js',
				self::$js_folder_path . '/library/jquery.easing.js',
				[ 'jquery' ],
				'1.3',
				true,
			],
			[
				'jquery-fitvids',
				self::$js_folder_url . '/library/jquery.fitvids.js',
				self::$js_folder_path . '/library/jquery.fitvids.js',
				[ 'jquery' ],
				'1.1',
				true,
			],
			[
				'jquery-flexslider',
				self::$js_folder_url . '/library/jquery.flexslider.js',
				self::$js_folder_path . '/library/jquery.flexslider.js',
				[ 'jquery' ],
				'2.7.2',
				true,
			],
			[
				'jquery-fusion-maps',
				self::$js_folder_url . '/library/jquery.fusion_maps.js',
				self::$js_folder_path . '/library/jquery.fusion_maps.js',
				[ 'jquery' ],
				'2.2.2',
				true,
			],
			[
				'jquery-lightbox',
				self::$js_folder_url . '/library/jquery.ilightbox.js',
				self::$js_folder_path . '/library/jquery.ilightbox.js',
				[ 'jquery' ],
				'2.2.3',
				true,
			],
			[
				'jquery-infinite-scroll',
				self::$js_folder_url . '/library/jquery.infinitescroll.js',
				self::$js_folder_path . '/library/jquery.infinitescroll.js',
				[ 'jquery' ],
				'2.1',
				true,
			],
			[
				'jquery-mousewheel',
				self::$js_folder_url . '/library/jquery.mousewheel.js',
				self::$js_folder_path . '/library/jquery.mousewheel.js',
				[ 'jquery' ],
				'3.0.6',
				true,
			],
			[
				'jquery-placeholder',
				self::$js_folder_url . '/library/jquery.placeholder.js',
				self::$js_folder_path . '/library/jquery.placeholder.js',
				[ 'jquery' ],
				'2.0.7',
				true,
			],
			[
				'jquery-touch-swipe',
				self::$js_folder_url . '/library/jquery.touchSwipe.js',
				self::$js_folder_path . '/library/jquery.touchSwipe.js',
				[ 'jquery' ],
				'1.6.6',
				true,
			],
			[
				'jquery-fade',
				self::$js_folder_url . '/library/jquery.fade.js',
				self::$js_folder_path . '/library/jquery.fade.js',
				[ 'jquery' ],
				'1',
				true,
			],
			[
				'images-loaded',
				self::$js_folder_url . '/library/imagesLoaded.js',
				self::$js_folder_path . '/library/imagesLoaded.js',
				[],
				'3.1.8',
				true,
			],

			// General.
			[
				'fusion-alert',
				self::$js_folder_url . '/general/fusion-alert.js',
				self::$js_folder_path . '/general/fusion-alert.js',
				[ 'jquery' ],
				'1',
				true,
			],
			[
				'fusion-equal-heights',
				self::$js_folder_url . '/general/fusion-equal-heights.js',
				self::$js_folder_path . '/general/fusion-equal-heights.js',
				[ 'jquery', 'modernizr' ],
				'1',
				true,
			],
			[
				'fusion-parallax',
				self::$js_folder_url . '/library/fusion-parallax.js',
				self::$js_folder_path . '/library/fusion-parallax.js',
				[ 'jquery', 'cssua', 'jquery-request-animation-frame' ],
				'1',
				true,
			],
			[
				'fusion-video-general',
				self::$js_folder_url . '/library/fusion-video-general.js',
				self::$js_folder_path . '/library/fusion-video-general.js',
				[ 'jquery-fitvids' ],
				'1',
				true,
			],
			[
				'fusion-video-bg',
				self::$js_folder_url . '/library/fusion-video-bg.js',
				self::$js_folder_path . '/library/fusion-video-bg.js',
				[ 'fusion-video-general', 'jquery-fitvids' ],
				'1',
				true,
			],
			[
				'fusion-lightbox',
				self::$js_folder_url . '/general/fusion-lightbox.js',
				self::$js_folder_path . '/general/fusion-lightbox.js',
				[ 'jquery-lightbox', 'jquery-mousewheel' ],
				'1',
				true,
			],
			[
				'fusion-carousel',
				self::$js_folder_url . '/general/fusion-carousel.js',
				self::$js_folder_path . '/general/fusion-carousel.js',
				[ 'jquery-caroufredsel', 'jquery-touch-swipe' ],
				'1',
				true,
			],
			[
				'fusion-flexslider',
				self::$js_folder_url . '/general/fusion-flexslider.js',
				self::$js_folder_path . '/general/fusion-flexslider.js',
				[ 'jquery-flexslider' ],
				'1',
				true,
			],
			[
				'fusion-popover',
				self::$js_folder_url . '/general/fusion-popover.js',
				self::$js_folder_path . '/general/fusion-popover.js',
				[ 'cssua', 'bootstrap-popover' ],
				'1',
				true,
			],
			[
				'fusion-tooltip',
				self::$js_folder_url . '/general/fusion-tooltip.js',
				self::$js_folder_path . '/general/fusion-tooltip.js',
				[ 'bootstrap-tooltip', 'cssua' ],
				'1',
				true,
			],
			[
				'fusion-sharing-box',
				self::$js_folder_url . '/general/fusion-sharing-box.js',
				self::$js_folder_path . '/general/fusion-sharing-box.js',
				[ 'jquery', 'cssua' ],
				'1',
				true,
			],
			[
				'fusion-blog',
				self::$js_folder_url . '/general/fusion-blog.js',
				self::$js_folder_path . '/general/fusion-blog.js',
				[ 'jquery', 'packery', 'isotope', 'fusion-lightbox', 'fusion-flexslider', 'jquery-infinite-scroll', 'images-loaded' ],
				'1',
				true,
			],
			[
				'fusion-button',
				self::$js_folder_url . '/general/fusion-button.js',
				self::$js_folder_path . '/general/fusion-button.js',
				[ 'jquery', 'cssua' ],
				'1',
				true,
			],
			[
				'jquery-sticky-kit',
				self::$js_folder_url . '/library/jquery.sticky-kit.js',
				self::$js_folder_path . '/library/jquery.sticky-kit.js',
				[ 'jquery' ],
				'1.1.2',
				true,
			],
		];

		// Conditional scripts.
		if ( fusion_library()->get_option( 'status_yt' ) || $is_builder ) {
			$scripts[] = [
				'fusion-youtube',
				self::$js_folder_url . '/library/fusion-youtube.js',
				self::$js_folder_path . '/library/fusion-youtube.js',
				[ 'fusion-video-bg' ],
				'2.2.1',
				true,
			];
		}
		if ( fusion_library()->get_option( 'status_vimeo' ) || $is_builder ) {
			$scripts[] = [
				'vimeo-player',
				self::$js_folder_url . '/library/vimeoPlayer.js',
				self::$js_folder_path . '/library/vimeoPlayer.js',
				[],
				'2.2.1',
				true,
			];
		}
		foreach ( $scripts as $script ) {
			Fusion_Dynamic_JS::register_script(
				$script[0],
				$script[1],
				$script[2],
				$script[3],
				$script[4],
				$script[5]
			);

		}
	}

	/**
	 * Enqueues scripts.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @return void
	 */
	public function wp_enqueue_scripts() {

		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

		if ( fusion_library()->get_option( 'status_gmap' ) || $is_builder ) {
			$map_protocol = ( is_ssl() ) ? 'https' : 'http';
			$map_key      = apply_filters( 'fusion_google_maps_api_key', fusion_library()->get_option( 'gmap_api' ) );
			$map_key      = ( $map_key ) ? 'key=' . $map_key . '&' : '';
			$lang_code    = fusion_get_google_maps_language_code();
			$map_api      = $map_protocol . '://maps.googleapis.com/maps/api/js?' . $map_key . 'language=' . $lang_code;
			wp_register_script( 'google-maps-api', $map_api, [], '1', true );
			wp_register_script( 'google-maps-infobox', self::$js_folder_url . '/library/infobox_packed.js', [], '1', true );
		}
	}

	/**
	 * Enqueues scripts.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @return void
	 */
	protected function enqueue_scripts() {
		global $post, $fusion_library_latest_version;

		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

		$header_override = false;
		if ( class_exists( 'Fusion_Template_Builder' ) ) {
			$header_override = Fusion_Template_Builder::get_instance()->get_override( 'header' );
		}

		// Some general enqueue for now.
		Fusion_Dynamic_JS::enqueue_script(
			'fusion-general-global',
			self::$js_folder_url . '/general/fusion-general-global.js',
			self::$js_folder_path . '/general/fusion-general-global.js',
			[ 'jquery', 'jquery-placeholder' ],
			'1',
			true
		);

		// Scroll to anchor, required in FB?
		$scroll_to_anchor_dependencies = [
			'jquery',
			'jquery-easing',
			'modernizr',
		];

		if ( ! $header_override && ( ! isset( $post->ID ) || 'no' !== fusion_get_page_option( 'display_header', $post->ID ) ) ) {
			$scroll_to_anchor_dependencies[] = 'avada-menu';
		}

		Fusion_Dynamic_JS::enqueue_script(
			'fusion-scroll-to-anchor',
			self::$js_folder_url . '/general/fusion-scroll-to-anchor.js',
			self::$js_folder_path . '/general/fusion-scroll-to-anchor.js',
			$scroll_to_anchor_dependencies,
			'1',
			true
		);

		// Responsive typography.
		Fusion_Dynamic_JS::enqueue_script(
			'fusion-responsive-typography',
			self::$js_folder_url . '/general/fusion-responsive-typography.js',
			self::$js_folder_path . '/general/fusion-responsive-typography.js',
			[ 'jquery', 'fusion' ],
			'1',
			true
		);

		// If responsive is disabled.
		if ( ! fusion_library()->get_option( 'responsive' ) || $is_builder ) {
			Fusion_Dynamic_JS::enqueue_script(
				'fusion-non-responsive',
				self::$js_folder_url . '/general/fusion-non-responsive.js',
				self::$js_folder_path . '/general/fusion-non-responsive.js',
				[ 'jquery' ],
				'1',
				true
			);
		}
	}

	/**
	 * Localizes scripts.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @return void
	 */
	protected function localize_scripts() {

		// Localize scripts.
		Fusion_Dynamic_JS::localize_script(
			'fusion',
			'fusionJSVars',
			[
				'visibility_small'  => fusion_library()->get_option( 'visibility_small' ),
				'visibility_medium' => fusion_library()->get_option( 'visibility_medium' ),
			]
		);
		Fusion_Dynamic_JS::localize_script(
			'fusion-video-bg',
			'fusionVideoBgVars',
			[
				'status_vimeo' => fusion_library()->get_option( 'status_vimeo' ) ? fusion_library()->get_option( 'status_vimeo' ) : '0',
				'status_yt'    => fusion_library()->get_option( 'status_yt' ) ? fusion_library()->get_option( 'status_yt' ) : '0',
			]
		);
		Fusion_Dynamic_JS::localize_script(
			'fusion-equal-heights',
			'fusionEqualHeightVars',
			[
				'content_break_point' => intval( fusion_library()->get_option( 'content_break_point' ) ),
			]
		);
		Fusion_Dynamic_JS::localize_script(
			'fusion-video-general',
			'fusionVideoGeneralVars',
			[
				'status_vimeo' => fusion_library()->get_option( 'status_vimeo' ) ? fusion_library()->get_option( 'status_vimeo' ) : '0',
				'status_yt'    => fusion_library()->get_option( 'status_yt' ) ? fusion_library()->get_option( 'status_yt' ) : '0',
			]
		);
		Fusion_Dynamic_JS::localize_script(
			'jquery-fusion-maps',
			'fusionMapsVars',
			[
				'admin_ajax' => admin_url( 'admin-ajax.php' ),
			]
		);
		Fusion_Dynamic_JS::localize_script(
			'jquery-lightbox',
			'fusionLightboxVideoVars',
			[
				'lightbox_video_width'  => fusion_library()->get_option( 'lightbox_video_dimensions' ) ? Fusion_Sanitize::number( fusion_library()->get_option( 'lightbox_video_dimensions', 'width' ) ) : '1280',
				'lightbox_video_height' => fusion_library()->get_option( 'lightbox_video_dimensions' ) ? Fusion_Sanitize::number( fusion_library()->get_option( 'lightbox_video_dimensions', 'height' ) ) : '720',
			]
		);
		Fusion_Dynamic_JS::localize_script(
			'fusion-lightbox',
			'fusionLightboxVars',
			[
				'status_lightbox'          => fusion_library()->get_option( 'status_lightbox' ) ? fusion_library()->get_option( 'status_lightbox' ) : false,
				'lightbox_gallery'         => fusion_get_option( 'lightbox_gallery' ),
				'lightbox_skin'            => fusion_get_option( 'lightbox_skin' ),
				'lightbox_title'           => fusion_get_option( 'lightbox_title' ),
				'lightbox_arrows'          => fusion_get_option( 'lightbox_arrows' ),
				'lightbox_slideshow_speed' => fusion_get_option( 'lightbox_slideshow_speed' ),
				'lightbox_autoplay'        => fusion_get_option( 'lightbox_autoplay' ),
				'lightbox_opacity'         => fusion_get_option( 'lightbox_opacity' ),
				'lightbox_desc'            => fusion_get_option( 'lightbox_desc' ),
				'lightbox_social'          => fusion_get_option( 'lightbox_social' ),
				'lightbox_social_links'    => Fusion_Social_Sharing::get_social_sharing_links_lightbox(),
				'lightbox_deeplinking'     => fusion_library()->get_option( 'lightbox_deeplinking' ) ? fusion_library()->get_option( 'lightbox_deeplinking' ) : false,
				'lightbox_path'            => fusion_get_option( 'lightbox_path' ),
				'lightbox_post_images'     => fusion_get_option( 'lightbox_post_images' ),
				'lightbox_animation_speed' => fusion_get_option( 'lightbox_animation_speed' ),
				'l10n'                     => [
					'close'           => esc_html__( 'Press Esc to close', 'Avada' ),
					'enterFullscreen' => esc_html__( 'Enter Fullscreen (Shift+Enter)', 'Avada' ),
					'exitFullscreen'  => esc_html__( 'Exit Fullscreen (Shift+Enter)', 'Avada' ),
					'slideShow'       => esc_html__( 'Slideshow', 'Avada' ),
					'next'            => esc_html__( 'Next', 'Avada' ),
					'previous'        => esc_html__( 'Previous', 'Avada' ),
				],
			]
		);
		Fusion_Dynamic_JS::localize_script(
			'fusion-carousel',
			'fusionCarouselVars',
			[
				'related_posts_speed' => fusion_library()->get_option( 'related_posts_speed' ) ? (int) fusion_library()->get_option( 'related_posts_speed' ) : 5000,
				'carousel_speed'      => fusion_library()->get_option( 'carousel_speed' ) ? (int) fusion_library()->get_option( 'carousel_speed' ) : 5000,
			]
		);
		Fusion_Dynamic_JS::localize_script(
			'fusion-scroll-to-anchor',
			'fusionScrollToAnchorVars',
			[
				'content_break_point'                     => intval( fusion_library()->get_option( 'content_break_point' ) ),
				'container_hundred_percent_height_mobile' => intval( fusion_library()->get_option( 'container_hundred_percent_height_mobile' ) ),
				'hundred_percent_scroll_sensitivity'      => intval( fusion_library()->get_option( 'container_hundred_percent_scroll_sensitivity' ) ),
			]
		);

		$flex_smooth_height = ( fusion_library()->get_option( 'slideshow_smooth_height' ) ) ? 'true' : 'false';

		Fusion_Dynamic_JS::localize_script(
			'fusion-flexslider',
			'fusionFlexSliderVars',
			[
				'status_vimeo'           => fusion_library()->get_option( 'status_vimeo' ) ? fusion_library()->get_option( 'status_vimeo' ) : false,
				'slideshow_autoplay'     => fusion_library()->get_option( 'slideshow_autoplay' ) ? fusion_library()->get_option( 'slideshow_autoplay' ) : false,
				'slideshow_speed'        => fusion_library()->get_option( 'slideshow_speed' ) ? (int) fusion_library()->get_option( 'slideshow_speed' ) : 5000,
				'pagination_video_slide' => fusion_library()->get_option( 'pagination_video_slide' ) ? fusion_library()->get_option( 'pagination_video_slide' ) : false,
				'status_yt'              => fusion_library()->get_option( 'status_yt' ) ? fusion_library()->get_option( 'status_yt' ) : false,
				'flex_smoothHeight'      => $flex_smooth_height,
			]
		);
		Fusion_Dynamic_JS::localize_script(
			'fusion-responsive-typography',
			'fusionTypographyVars',
			[
				'site_width'             => fusion_library()->get_option( 'site_width' ) ? fusion_library()->get_option( 'site_width' ) : '1200px',
				'typography_sensitivity' => fusion_library()->get_option( 'typography_sensitivity' ) ? fusion_library()->get_option( 'typography_sensitivity' ) : 1,
				'typography_factor'      => fusion_library()->get_option( 'typography_factor' ) ? fusion_library()->get_option( 'typography_factor' ) : 1,
				'elements'               => apply_filters( 'fusion_responsive_type_elements', 'h1, h2, h3, h4, h5, h6' ),
			]
		);
		Fusion_Dynamic_JS::localize_script(
			'fusion-blog',
			'fusionBlogVars',
			[
				'infinite_blog_text'    => '<em>' . __( 'Loading the next set of posts...', 'Avada' ) . '</em>',
				'infinite_finished_msg' => '<em>' . __( 'All items displayed.', 'Avada' ) . '</em>',
				'slideshow_autoplay'    => fusion_library()->get_option( 'slideshow_autoplay' ) ? fusion_library()->get_option( 'slideshow_autoplay' ) : false,
				'lightbox_behavior'     => fusion_library()->get_option( 'lightbox_behavior' ) ? fusion_library()->get_option( 'lightbox_behavior' ) : false,
				'blog_pagination_type'  => fusion_library()->get_option( 'blog_pagination_type' ) ? fusion_library()->get_option( 'blog_pagination_type' ) : false,
			]
		);
	}
}

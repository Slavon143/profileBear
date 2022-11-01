<?php
/**
 * Deprecate pyre_* post-meta.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Fusion-Library
 * @since      6.2.0
 */

/**
 * Get & set setting values.
 */
class Fusion_Deprecate_Pyre_PO {

	/**
	 * The root post-meta key.
	 *
	 * @since 6.2.0
	 */
	const ROOT = '_fusion';

	/**
	 * The post-ID.
	 *
	 * @access protected
	 * @since 6.2.0
	 * @var int
	 */
	protected $post_id;

	/**
	 * The post-type.
	 *
	 * @access protected
	 * @since 6.2.0
	 * @var int
	 */
	protected $post_type;

	/**
	 * The post-meta.
	 *
	 * @access protected
	 * @since 6.2.0
	 * @var array
	 */
	protected $post_meta;

	/**
	 * An array of mismatched options.
	 *
	 * Format: old_option_name => new_option_name.
	 *
	 * @static
	 * @access protected
	 * @since 6.2.0
	 * @var array
	 */
	protected static $mismatched = [
		'avada_rev_styles'          => 'avada_rev_styles',
		'show_first_featured_image' => 'show_first_featured_image',
	];

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 6.2.0
	 * @param array $post_id The arguments.
	 * @return void
	 */
	public function __construct( $post_id = 0 ) {
		$this->post_id = $post_id ? $post_id : fusion_library()->get_page_id();
		if ( ! $this->post_id || ! is_int( $this->post_id ) ) {
			return;
		}

		$this->post_type = get_post_type( $this->post_id );
		$this->post_meta = get_post_meta( $this->post_id );

		// Don't want to migrate for revisions or continue if we are not on a single post/page.
		if ( 'revision' === $this->post_type ) {
			return;
		}

		// Trigger migrations.
		$this->migrate();
	}

	/**
	 * Migrate old value to new structure.
	 *
	 * @access protected
	 * @since 6.2.0
	 * @return void
	 */
	protected function migrate() {

		$this->modify_initial_postmeta();

		// Get all post-meta.
		$new_meta = [];

		// Loop all postmeta.
		foreach ( $this->post_meta as $old_key => $value ) {

			// Only migrate non-empty, non-default meta.
			if ( '' === $value || 'default' === $value ) {
				continue;
			}

			// Get the new key.
			$new_key = $this->get_new_option_name( $old_key );

			// Format the value.
			$value = $this->format_value( $value, $old_key, $new_key );

			if ( false === strpos( $new_key, '[' ) ) {
				$new_meta[ $new_key ] = $value;
			} else {

				$new_key_root = explode( '[', $new_key )[0];
				if ( ! isset( $new_meta[ $new_key_root ] ) ) {
					$new_meta[ $new_key_root ] = [];
				}
				$new_key_child = str_replace( ']', '', explode( '[', $new_key )[1] );

				$new_meta[ $new_key_root ][ $new_key_child ] = $value;
			}
		}

		update_post_meta( $this->post_id, Fusion_Data_PostMeta::ROOT, $new_meta );
	}

	/**
	 * Get the new option name.
	 *
	 * @access protected
	 * @since 6.2.0
	 * @param string $option_name The old option-name.
	 * @return string
	 */
	protected function get_new_option_name( $option_name ) {

		switch ( $option_name ) {
			case 'portfolio_width_100':
				if ( 'product' === $this->post_type ) {
					return 'product_width_100';
				} elseif ( 'avada_portfolio' === $this->post_type ) {
					return 'portfolio_width_100';
				}
				return 'blog_width_100';

			case 'share_box':
				if ( 'tribe_events' === $this->post_type ) {
					return 'events_social_sharing_box';
				} elseif ( 'avada_portfolio' === $this->post_type ) {
					return 'portfolio_social_sharing_box';
				}
				return 'social_sharing_box';

			case 'post_pagination':
				return ( 'avada_portfolio' === $this->post_type ) ? 'portfolio_pn_nav' : 'blog_pn_nav';

			case 'related_posts':
				if ( 'avada_portfolio' === $this->post_type ) {
					return 'portfolio_related_posts';
				} elseif ( 'avada_faq' === $this->post_type ) {
					return 'faq_related_posts';
				}
				return 'related_posts';

			case 'sbg_selected_sidebar_replacement':
				if ( 'page' === $this->post_type ) {
					return 'pages_sidebar';
				} elseif ( 'post' === $this->post_type || 'avada_faq' === $this->post_type ) {
					return 'posts_sidebar';
				} elseif ( 'avada_portfolio' === $this->post_type ) {
					return 'portfolio_sidebar';
				} elseif ( 'product' === $this->post_type ) {
					return 'woo_sidebar';
				} elseif ( 'tribe_events' === $this->post_type ) {
					return 'ec_sidebar';
				} elseif ( 'forum' === $this->post_type || 'topic' === $this->post_type || 'reply' === $this->post_type ) {
					return 'ppbress_sidebar';
				}
				return 'posts_sidebar'; // Migrate to post option naming for CPT.

			case 'sbg_selected_sidebar_2_replacement':
				if ( 'page' === $this->post_type ) {
					return 'pages_sidebar_2';
				} elseif ( 'post' === $this->post_type || 'avada_faq' === $this->post_type ) {
					return 'posts_sidebar_2';
				} elseif ( 'avada_portfolio' === $this->post_type ) {
					return 'portfolio_sidebar_2';
				} elseif ( 'product' === $this->post_type ) {
					return 'woo_sidebar_2';
				} elseif ( 'tribe_events' === $this->post_type ) {
					return 'ec_sidebar_2';
				} elseif ( 'forum' === $this->post_type || 'topic' === $this->post_type || 'reply' === $this->post_type ) {
					return 'ppbress_sidebar_2';
				}
				return 'posts_sidebar_2'; // Migrate to post option naming for CPT.

			case 'sidebar_position':
				if ( 'page' === $this->post_type ) {
					return 'default_sidebar_pos';
				} elseif ( 'post' === $this->post_type || 'avada_faq' === $this->post_type ) {
					return 'blog_sidebar_position';
				} elseif ( 'avada_portfolio' === $this->post_type ) {
					return 'portfolio_sidebar_position';
				} elseif ( 'product' === $this->post_type ) {
					return 'woo_sidebar_position';
				} elseif ( 'tribe_events' === $this->post_type ) {
					return 'ec_sidebar_pos';
				} elseif ( 'forum' === $this->post_type || 'topic' === $this->post_type || 'reply' === $this->post_type ) {
					return 'bbpress_sidebar_position';
				}
				return 'blog_sidebar_position';

			case 'page_bg_layout':
				return 'layout';

			case 'page_bg_color':
				return 'bg_color';

			case 'page_bg':
				return 'bg_image[url]';

			case 'page_bg_full':
				return 'bg_full';

			case 'page_bg_repeat':
				return 'bg_repeat';

			case 'wide_page_bg_color':
				return 'content_bg_color';

			case 'wide_page_bg':
				return 'content_bg_image[url]';

			case 'wide_page_bg_full':
				return 'content_bg_full';

			case 'wide_page_bg_repeat':
				return 'content_bg_repeat';

			case 'display_footer':
				return 'footer_widgets';

			case 'display_copyright':
				return 'footer_copyright';

			case 'combined_header_bg_color':
				return 'header_bg_color';

			case 'header_bg':
				return 'header_bg_image[url]';

			case 'main_top_padding':
				return 'main_padding[top]';

			case 'main_bottom_padding':
				return 'main_padding[bottom]';

			case 'page_title':
				return 'page_title_bar';

			case 'page_title_breadcrumbs_search_bar':
				return 'page_title_bar_bs';

			case 'page_title_text':
				return 'page_title_bar_text';

			case 'page_title_text_alignment':
				return 'page_title_alignment';

			case 'page_title_text_size':
				return 'page_title_font_size';

			case 'page_title_font_color':
				return 'page_title_color';

			case 'page_title_custom_subheader_text_size':
				return 'page_title_subheader_font_size';

			case 'page_title_subheader_font_color':
				return 'page_title_subheader_color';

			case 'page_title_bar_bg_color':
				return 'page_title_bg_color';

			case 'page_title_bar_borders_color':
				return 'page_title_border_color';

			case 'page_title_bar_bg':
				return 'page_title_bg[url]';

			case 'page_title_bar_bg_retina':
				return 'page_title_bg_retina[url]';

			case 'page_title_bar_bg_full':
				return 'page_title_bg_full';

			case 'width':
				return 'portfolio_featured_image_width';

			case 'project_desc_title':
				return 'portfolio_project_desc_title';

			case 'project_details':
				return 'portfolio_project_details';

			case 'link_icon_target':
				return 'portfolio_link_icon_target';

			case 'post_comments':
				return 'blog_comments';

			case 'sidebar_bg_color':
				return ( 'tribe_events' === $this->post_type ) ? 'ec_sidebar_bg_color' : 'sidebar_bg_color';

			case 'fimg_width':
				return 'fimg[width]';

			case 'fimg_height':
				return 'fimg[height]';

			case 'mp4':
				return 'mp4[url]';

			case 'webm':
				return 'webm[url]';

			case 'ogv':
				return 'ogv[url]';

			case 'preview_image':
				return 'preview_image[url]';
		}

		return $option_name;
	}

	/**
	 * Format the value.
	 *
	 * @access private
	 * @since 6.2.0
	 * @param mixed  $value   The value.
	 * @param string $key     The old post-meta key.
	 * @param string $new_key The new post-meta key.
	 */
	private function format_value( $value, $key, $new_key ) {

		if ( 'sbg_selected_sidebar_replacement' === $key || 'sbg_selected_sidebar_2_replacement' === $key ) {
			$value = maybe_unserialize( $value );
			if ( is_array( $value ) && isset( $value[0] ) ) {
				return $value[0];
			}
		}

		if ( isset( self::$mismatched[ $key ] ) && $new_key === self::$mismatched[ $key ] ) {
			if ( 'yes' === $value ) {
				return 'no';
			} elseif ( '1' === $value ) {
				return '0';
			} elseif ( 1 === $value ) {
				return 0;
			} elseif ( true === $value ) {
				return false;
			} elseif ( 'no' === $value ) {
				return 'yes';
			} elseif ( '0' === $value ) {
				return '1';
			} elseif ( 0 === $value ) {
				return 1;
			} elseif ( false === $value ) {
				return true;
			}
		}
		return $value;
	}

	/**
	 * Run initial migrations on post-meta.
	 *
	 * @since 6.2.0
	 * @return void
	 */
	protected function modify_initial_postmeta() {

		$post_meta = [];

		foreach ( $this->post_meta as $key => $val ) {

			// Check if this is a meta we want to migrate.
			if ( ! $this->is_ours( $key ) ) {
				unset( $this->post_meta[ $key ] );
				continue;
			}

			$val = ( is_array( $val ) && isset( $val[0] ) ) ? $val[0] : $val;
			$val = maybe_unserialize( $val );

			$post_meta[ str_replace( 'pyre_', '', $key ) ] = $val;
		}

		/**
		 * Modification for the combined_header_bg_color page-option.
		 * In the past there were separate controls for color & opacity
		 * which were combined to a single rgba control in Avada v5.7.
		 */
		if ( isset( $post_meta['header_bg_opacity'] ) && isset( $post_meta['header_bg_color'] ) && ! isset( $post_meta['combined_header_bg_color'] ) ) {

			// Only proceed if header_bg_color is not rgba.
			if ( false === strpos( $post_meta['header_bg_color'], 'rgba' ) ) {
				$alpha = $post_meta['header_bg_opacity'];

				// Only proceed if alpha is numeric.
				if ( is_numeric( $alpha ) ) {
					$color = $post_meta['header_bg_color'];
					if ( ! $color || empty( $color ) ) {
						$color = fusion_get_theme_option( 'header_bg_color' );
					}

					$post_meta['combined_header_bg_color'] = $post_meta['header_bg_color'];
					if ( 1 > $alpha ) {
						$post_meta['combined_header_bg_color'] = Fusion_Color::new_color( $color )->getNew( 'alpha', $alpha )->toCSS( 'rgba' );
					}
				}
			}
		}

		$this->post_meta = $post_meta;
	}

	/**
	 * Check if the post-meta is one of ours.
	 *
	 * @access protected
	 * @since 6.2.0
	 * @param string $key The post-meta key.
	 * @return bool
	 */
	protected function is_ours( $key ) {
		if ( 0 === strpos( $key, 'pyre_' ) ) {
			return true;
		}
		if ( 0 === strpos( $key, 'sbg_' ) ) {
			return true;
		}
		if ( 0 === strpos( $key, 'kd_' ) ) {
			return true;
		}
		return false;
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

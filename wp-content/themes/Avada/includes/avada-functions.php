<?php
/**
 * Contains all theme-specific functions.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      3.8
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

if ( ! function_exists( 'avada_render_blog_post_content' ) ) {
	/**
	 * Get the post (excerpt).
	 *
	 * @return void Content is directly echoed.
	 */
	function avada_render_blog_post_content() {
		if ( ! is_search() && 'hide' !== fusion_get_option( 'content_length' ) ) {
			Avada()->blog->render_post_content();
		}
	}
}
add_action( 'avada_blog_post_content', 'avada_render_blog_post_content', 10 );

if ( ! function_exists( 'avada_render_search_post_content' ) ) {
	/**
	 * Get the post (excerpt).
	 *
	 * @since 5.9
	 * @return void Content is directly echoed.
	 */
	function avada_render_search_post_content() {
		if ( is_search() && 'no_text' !== fusion_get_option( 'search_content_length' ) ) {
			Avada()->blog->render_post_content();
		}
	}
}
add_action( 'avada_blog_post_content', 'avada_render_search_post_content', 10 );

if ( ! function_exists( 'avada_render_portfolio_post_content' ) ) {
	/**
	 * Get the portfolio post (excerpt).
	 *
	 * @param  int|string $page_id The page ID.
	 * @return void
	 */
	function avada_render_portfolio_post_content( $page_id ) {
		if ( 'no_text' !== fusion_get_option( 'portfolio_archive_content_length' ) ) {
			echo fusion_get_post_content( $page_id, 'portfolio' ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}
}
add_action( 'avada_portfolio_post_content', 'avada_render_portfolio_post_content', 10 );

if ( ! function_exists( 'avada_render_blog_post_date' ) ) {
	/**
	 * Render the HTML for the date box for large/medium alternate blog layouts.
	 *
	 * @return void
	 */
	function avada_render_blog_post_date() {
		get_template_part( 'templates/blog-post-date' );
	}
}
add_action( 'avada_blog_post_date_and_format', 'avada_render_blog_post_date', 10 );

if ( ! function_exists( 'avada_render_blog_post_format' ) ) {
	/**
	 * Render the HTML for the format box for large/medium alternate blog layouts.
	 *
	 * @return void
	 */
	function avada_render_blog_post_format() {
		get_template_part( 'templates/post-format-box' );
	}
}
add_action( 'avada_blog_post_date_and_format', 'avada_render_blog_post_format', 15 );

if ( ! function_exists( 'avada_render_author_info' ) ) {
	/**
	 * Output author information on the author archive page.
	 *
	 * @return void
	 */
	function avada_render_author_info() {
		get_template_part( 'templates/author-info' );
	}
}
add_action( 'avada_author_info', 'avada_render_author_info', 10 );

if ( ! function_exists( 'avada_render_footer_copyright_notice' ) ) {
	/**
	 * Output the footer copyright notice.
	 *
	 * @return void
	 */
	function avada_render_footer_copyright_notice() {
		get_template_part( 'templates/footer-copyright-notice' );
	}
}
add_action( 'avada_footer_copyright_content', 'avada_render_footer_copyright_notice', 10 );

if ( ! function_exists( 'avada_render_footer_social_icons' ) ) {
	/**
	 * Output the footer social icons.
	 *
	 * @return void
	 */
	function avada_render_footer_social_icons() {

		// Render the social icons.
		if ( Avada()->settings->get( 'icons_footer' ) ) {
			get_template_part( 'templates/footer-social-icons' );
		}
	}
}
add_action( 'avada_footer_copyright_content', 'avada_render_footer_social_icons', 15 );

if ( ! function_exists( 'avada_render_placeholder_image' ) ) {
	/**
	 * Action to output a placeholder image.
	 *
	 * @param  string $featured_image_size Size of the featured image that should be emulated.
	 * @return void
	 */
	function avada_render_placeholder_image( $featured_image_size = 'full' ) {
		Avada()->images->render_placeholder_image( $featured_image_size );
	}
}
add_action( 'avada_placeholder_image', 'avada_render_placeholder_image', 10 );

if ( ! function_exists( 'avada_get_image_orientation_class' ) ) {
	/**
	 * Returns the image class according to aspect ratio.
	 *
	 * @param  array $attachment The attachment.
	 * @return void
	 */
	function avada_get_image_orientation_class( $attachment ) {
		Avada()->images->get_image_orientation_class( $attachment );
	}
}

if ( ! function_exists( 'avada_render_post_title' ) ) {
	/**
	 * Render the post title as linked h1 tag.
	 *
	 * @param  int|string $post_id      The post ID.
	 * @param  bool       $linked       If we want it linked.
	 * @param  string     $custom_title A Custom title.
	 * @param  string|int $custom_size  A custom size.
	 * @param  string|int $custom_link  A custom link.
	 * @return string                   The post title as linked h1 tag.
	 */
	function avada_render_post_title( $post_id = '', $linked = true, $custom_title = '', $custom_size = '2', $custom_link = '' ) {

		$entry_title_class = '';

		// Add the entry title class if rich snippets are enabled.
		if ( Avada()->settings->get( 'disable_date_rich_snippet_pages' ) && Avada()->settings->get( 'disable_rich_snippet_title' ) ) {
			$entry_title_class = ' class="entry-title fusion-post-title"';
		} else {
			$entry_title_class = ' class="fusion-post-title"';
		}

		// If we have a custom title, use it otherwise get post title.
		$title     = ( $custom_title ) ? $custom_title : get_the_title( $post_id );
		$permalink = ( $custom_link ) ? $custom_link : get_permalink( $post_id );

		// Defaults.
		$link_icon_target           = apply_filters( 'fusion_builder_link_icon_target', '', $post_id );
		$post_links_target          = apply_filters( 'fusion_builder_post_links_target', '', $post_id );
		$portfolio_link_icon_target = apply_filters( 'fusion_builder_portfolio_link_icon_target', false, $post_id );

		// If the post title should be linked at the markup.
		if ( $linked ) {
			$link_target = '';
			if ( 'yes' === $link_icon_target || 'yes' === $post_links_target || ( 'avada_portfolio' === get_post_type() && $portfolio_link_icon_target ) ) {
				$link_target = ' target="_blank" rel="noopener noreferrer"';
			}
			$title = '<a href="' . $permalink . '"' . $link_target . '>' . $title . '</a>';
		}

		// Return the HTML markup of the post title.
		return '<h' . $custom_size . $entry_title_class . '>' . $title . '</h' . $custom_size . '>';
	}
}

if ( ! function_exists( 'avada_get_portfolio_classes' ) ) {
	/**
	 * Determine the css classes need for portfolio page content container.
	 *
	 * @param  int|string $post_id The post ID.
	 * @return string The classes separated with space.
	 */
	function avada_get_portfolio_classes( $post_id = '' ) {

		$classes = 'fusion-portfolio';

		// Get the page template slug without .php suffix.
		$page_template = str_replace( '.php', '', get_page_template_slug( $post_id ) );

		// Add the text class, if a text layout is used.
		if ( strpos( $page_template, 'text' ) || strpos( $page_template, 'one' ) ) {
			$classes .= ' fusion-portfolio-text';
		}

		// If one column text layout is used, add special class.
		if ( strpos( $page_template, 'one' ) && ! strpos( $page_template, 'text' ) ) {
			$classes .= ' fusion-portfolio-one-nontext';
		}

		// For text layouts add the class for boxed/unboxed.
		if ( strpos( $page_template, 'text' ) ) {
			$classes      .= ' fusion-portfolio-' . fusion_get_option( 'portfolio_text_layout', 'portfolio_text_layout', $post_id ) . ' ';
			$page_template = str_replace( '-text', '', $page_template );
		}

		// Add the column class.
		$page_template = str_replace( '-column', '', $page_template );
		return $classes . ' fusion-' . $page_template;
	}
}

if ( ! function_exists( 'avada_get_image_size_dimensions' ) ) {
	/**
	 * Get Image dimensions.
	 *
	 * @param  string $image_size The Image size (obviously).
	 * @return array
	 */
	function avada_get_image_size_dimensions( $image_size = 'full' ) {
		global $_wp_additional_image_sizes;

		if ( 'full' === $image_size ) {
			$image_dimension = [
				'height' => 'auto',
				'width'  => '100%',
			];
		} else {
			if ( 'portfolio-six' === $image_size ) {
				$image_size = 'portfolio-five';
			} elseif ( 'portfolio-four' === $image_size ) {
				$image_size = 'portfolio-three';
			}
			$image_dimension = [
				'height' => $_wp_additional_image_sizes[ $image_size ]['height'] . 'px',
				'width'  => $_wp_additional_image_sizes[ $image_size ]['width'] . 'px',
			];
		}
		return $image_dimension;
	}
}

if ( ! function_exists( 'avada_get_portfolio_image_size' ) ) {
	/**
	 * The portfolio Image Size.
	 *
	 * @param  int $current_page_id The ID of the current page.
	 * @return string
	 */
	function avada_get_portfolio_image_size( $current_page_id ) {

		$custom_image_size = 'full';
		if ( is_page_template( 'portfolio-one-column-text.php' ) ) {
			$custom_image_size = 'portfolio-full';
		} elseif ( is_page_template( 'portfolio-one-column.php' ) ) {
			$custom_image_size = 'portfolio-one';
		} elseif ( is_page_template( 'portfolio-two-column.php' ) || is_page_template( 'portfolio-two-column-text.php' ) ) {
			$custom_image_size = 'portfolio-two';
		} elseif ( is_page_template( 'portfolio-three-column.php' ) || is_page_template( 'portfolio-three-column-text.php' ) ) {
			$custom_image_size = 'portfolio-three';
		} elseif ( is_page_template( 'portfolio-four-column.php' ) || is_page_template( 'portfolio-four-column-text.php' ) ) {
			$custom_image_size = 'portfolio-three';
		} elseif ( is_page_template( 'portfolio-five-column.php' ) || is_page_template( 'portfolio-five-column-text.php' ) ) {
			$custom_image_size = 'portfolio-five';
		} elseif ( is_page_template( 'portfolio-six-column.php' ) || is_page_template( 'portfolio-six-column-text.php' ) ) {
			$custom_image_size = 'portfolio-five';
		}

		$portfolio_featured_image_size = fusion_get_page_option( 'portfolio_featured_image_size', $current_page_id );
		$featured_image_size           = $custom_image_size;
		if ( 'default' === $portfolio_featured_image_size || ! $portfolio_featured_image_size ) {
			$featured_image_size = ( 'full' === Avada()->settings->get( 'portfolio_featured_image_size' ) ) ? 'full' : $custom_image_size;
		} elseif ( 'full' === $portfolio_featured_image_size ) {
			$featured_image_size = 'full';
		}

		if ( is_page_template( 'portfolio-grid.php' ) ) {
			$featured_image_size = 'full';
		}

		return $featured_image_size;
	}
}

if ( ! function_exists( 'avada_get_blog_layout' ) ) {
	/**
	 * Get the blog layout for the current page template.
	 *
	 * @return string The correct layout name for the blog post class.
	 */
	function avada_get_blog_layout() {
		return Avada()->blog->get_blog_layout();
	}
}

if ( ! function_exists( 'avada_render_social_sharing' ) ) {
	/**
	 * Renders social sharing links.
	 *
	 * @param string $post_type The post-type.
	 * @return void
	 */
	function avada_render_social_sharing( $post_type = 'post' ) {
		include locate_template( 'templates/social-sharing.php' );
	}
}

if ( ! function_exists( 'avada_render_related_posts' ) ) {
	/**
	 * Render related posts carousel.
	 *
	 * @param  string $post_type The post type to determine correct related posts and headings.
	 * @return void              Directly includes the template file.
	 */
	function avada_render_related_posts( $post_type = '' ) {

		$post_id = get_the_ID();

		if ( ! $post_type ) {
			global $wp_query;
			$post_type = 'post';
			if ( is_object( $wp_query ) && isset( $wp_query->post ) && is_object( $wp_query->post ) && isset( $wp_query->post->ID ) && $wp_query->post->ID ) {
				$post_type = get_post_type( $wp_query->post->ID );
				$post_id   = $wp_query->post->ID;
			}
		}

		// Set the needed variables according to post type.
		$main_heading = '';
		if ( 'post' === $post_type ) {
			$theme_option_name = 'related_posts';
			$main_heading      = esc_html__( 'Related Posts', 'Avada' );
		} elseif ( 'avada_portfolio' === $post_type ) {
			$theme_option_name = 'portfolio_related_posts';
			$main_heading      = esc_html__( 'Related Projects', 'Avada' );
		} elseif ( 'avada_faq' === $post_type ) {
			$theme_option_name = 'faq_related_posts';
			$main_heading      = esc_html__( 'Related Faqs', 'Avada' );
		}

		$main_heading = apply_filters( 'fusion_related_posts_heading_text', $main_heading, $post_type );

		// Check if related posts should be shown.
		if ( isset( $theme_option_name ) && ( fusion_get_option( $theme_option_name ) ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$number_related_posts = Avada()->settings->get( 'number_related_posts' );
			$number_related_posts = ( '0' == $number_related_posts ) ? '-1' : $number_related_posts; // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( 'post' === $post_type ) {
				$related_posts = fusion_get_related_posts( $post_id, $number_related_posts );
			} else {
				$related_posts = fusion_get_custom_posttype_related_posts( $post_id, $number_related_posts, $post_type );
			}

			// If there are related posts, display them.
			if ( isset( $related_posts ) && $related_posts->have_posts() ) {
				Fusion_Dynamic_JS::enqueue_script( 'fusion-carousel' );
				include locate_template( 'templates/related-posts.php' );
			}
		}
	}
}

if ( ! function_exists( 'avada_page_title_bar' ) ) {
	/**
	 * Render the HTML markup of the page title bar.
	 *
	 * @param  string $title             Main title; page/post title or custom title set by user.
	 * @param  string $subtitle          Subtitle as custom user setting.
	 * @param  string $secondary_content HTML markup of the secondary content; breadcrumbs or search field.
	 * @return void
	 */
	function avada_page_title_bar( $title, $subtitle, $secondary_content ) {
		$post_id   = Avada()->fusion_library->get_page_id();
		$alignment = '';

		// Check for the secondary content.
		$content_type = 'none';
		if ( false !== strpos( $secondary_content, 'searchform' ) ) {
			$content_type = 'search';
		} elseif ( empty( $secondary_content ) ) {
			$content_type = 'breadcrumbs';
		}

		$alignment = fusion_get_option( 'page_title_alignment' );

		// Render the page title bar.
		include locate_template( 'templates/title-bar.php' );
	}
}

if ( ! function_exists( 'avada_add_login_box_to_nav' ) ) {
	/**
	 * Add woocommerce cart to main navigation or top navigation.
	 *
	 * @param  string       $items HTML for the main menu items.
	 * @param  array|Object $args  Arguments for the WP menu.
	 * @return string
	 */
	function avada_add_login_box_to_nav( $items, $args ) {

		$ubermenu = ( function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) && ubermenu_get_menu_instance_by_theme_location( $args->theme_location ) ) ? true : false; // Disable woo cart on ubermenu navigations.

		if ( $ubermenu ) {
			return $items;
		}

		if ( in_array( $args->theme_location, [ 'main_navigation', 'top_navigation', 'sticky_navigation' ], true ) ) {
			$is_enabled    = ( 'top_navigation' === $args->theme_location ) ? Avada()->settings->get( 'woocommerce_acc_link_top_nav' ) : Avada()->settings->get( 'woocommerce_acc_link_main_nav' );
			$header_layout = Avada()->settings->get( 'header_layout' );

			if ( class_exists( 'WooCommerce' ) && $is_enabled ) {
				$woo_account_page_link = wc_get_page_permalink( 'myaccount' );

				if ( $woo_account_page_link ) {
					$active_classes           = ( is_account_page() ) ? ' current-menu-item current_page_item' : '';
					$my_account_link_contents = esc_html__( 'My Account', 'Avada' );

					$items .= '<li class="menu-item fusion-dropdown-menu menu-item-has-children fusion-custom-menu-item fusion-menu-login-box' . $active_classes . '">';

					// If chosen in Global Options, display the caret icon, as the my account item alyways has a dropdown.
					$caret_icon   = '';
					$caret_before = '';
					$caret_after  = '';
					if ( 'none' !== Avada()->settings->get( 'menu_display_dropdown_indicator' ) && 'v6' !== $header_layout ) {
						$caret_icon = '<span class="fusion-caret"><i class="fusion-dropdown-indicator"></i></span>';
					}

					if ( 'right' === fusion_get_option( 'header_position' ) && ! is_rtl() || 'left' === fusion_get_option( 'header_position' ) && is_rtl() ) {
						$caret_before = $caret_icon;
					} else {
						$caret_after = $caret_icon;
					}
					$menu_highlight_style = Avada()->settings->get( 'menu_highlight_style' );

					$items .= '<a href="' . $woo_account_page_link . '" aria-haspopup="true" class="fusion-' . $menu_highlight_style . '-highlight">' . $caret_before . '<span class="menu-text">' . $my_account_link_contents . '</span>' . $caret_after;

					if ( 'main_navigation' === $args->theme_location && 'v6' !== $header_layout ) {
						$items = apply_filters( 'avada_menu_arrow_hightlight', $items, true );
					}

					$items .= '</a>';

					if ( 'v6' !== $header_layout ) {
						if ( ! is_user_logged_in() ) {
							$referer = fusion_get_referer();
							$referer = ( $referer ) ? $referer : '';

							$menu_id = '';
							if ( $args->menu_id ) {
								$menu_id = $args->menu_id;
							} elseif ( is_string( $args->menu ) ) {
								$menu_id = uniqid( $args->menu . '-' );
							} elseif ( isset( $args->menu->slug ) ) {
								$menu_id = uniqid( $args->menu->slug . '-' );
							}

							$items .= '<div class="fusion-custom-menu-item-contents">';
							if ( isset( $_GET['login'] ) && 'failed' === $_GET['login'] ) { // phpcs:ignore WordPress.Security.NonceVerification
								$items .= '<p class="fusion-menu-login-box-error">' . esc_html__( 'Login failed, please try again.', 'Avada' ) . '</p>';
							}
							$items .= '<form action="' . esc_attr( site_url( 'wp-login.php', 'login_post' ) ) . '" name="loginform" method="post">';
							$items .= '<p><input type="text" class="input-text" name="log" id="username-' . esc_attr( $menu_id ) . '" value="" placeholder="' . esc_html__( 'Username', 'Avada' ) . '" /></p>';
							$items .= '<p><input type="password" class="input-text" name="pwd" id="password-' . esc_attr( $menu_id ) . '" value="" placeholder="' . esc_html__( 'Password', 'Avada' ) . '" /></p>';
							$items .= '<p class="fusion-remember-checkbox"><label for="fusion-menu-login-box-rememberme-' . esc_attr( $menu_id ) . '"><input name="rememberme" type="checkbox" id="fusion-menu-login-box-rememberme" value="forever"> ' . esc_html__( 'Remember Me', 'Avada' ) . '</label></p>';
							$items .= '<input type="hidden" name="fusion_woo_login_box" value="true" />';
							$items .= '<p class="fusion-login-box-submit">';
							$items .= '<input type="submit" name="wp-submit" id="wp-submit-' . esc_attr( $menu_id ) . '" class="button button-small default comment-submit" value="' . esc_html__( 'Log In', 'Avada' ) . '">';
							$items .= '<input type="hidden" name="redirect" value="' . esc_url( $referer ) . '">';
							$items .= '</p>';
							$items .= '</form>';
							$items .= '<a class="fusion-menu-login-box-register" href="' . get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '" title="' . esc_attr__( 'Register', 'Avada' ) . '">' . esc_attr__( 'Register', 'Avada' ) . '</a>';
							$items .= '</div>';
						} else {
							$account_endpoints = wc_get_account_menu_items();
							unset( $account_endpoints['dashboard'] );

							$items .= '<ul class="sub-menu">';
							foreach ( $account_endpoints as $endpoint => $label ) {
								$active_classes = ( is_wc_endpoint_url( $endpoint ) ) ? ' current-menu-item current_page_item' : '';

								$items .= '<li class="menu-item fusion-dropdown-submenu' . $active_classes . '">';
								$items .= '<a href="' . esc_url( wc_get_account_endpoint_url( $endpoint ) ) . '">' . esc_html( $label ) . '</a>';
								$items .= '</li>';
							}
							$items .= '</ul>';
						}
					}
					$items .= '</li>';
				}
			}
		}
		return $items;
	}
}
add_filter( 'wp_nav_menu_items', 'avada_add_login_box_to_nav', 10, 2 );

if ( ! function_exists( 'avada_nav_woo_cart' ) ) {
	/**
	 * Woo Cart Dropdown for Main Nav or Top Nav.
	 *
	 * @param string $position The cart position.
	 * @return string HTML of Dropdown
	 */
	function avada_nav_woo_cart( $position = 'main' ) {

		if ( ! class_exists( 'WooCommerce' ) ) {
			return '';
		}

		$woo_cart_page_link       = wc_get_cart_url();
		$cart_link_active_class   = '';
		$cart_link_active_text    = '';
		$is_enabled               = false;
		$main_cart_class          = '';
		$cart_link_inactive_class = '';
		$cart_link_inactive_text  = '';
		$items                    = '';
		$cart_contents_count      = WC()->cart->get_cart_contents_count();

		if ( 'main' === $position ) {
			$is_enabled               = Avada()->settings->get( 'woocommerce_cart_link_main_nav' );
			$main_cart_class          = ' fusion-main-menu-cart';
			$cart_link_active_class   = 'fusion-main-menu-icon fusion-main-menu-icon-active';
			$cart_link_inactive_class = 'fusion-main-menu-icon';

			if ( Avada()->settings->get( 'woocommerce_cart_counter' ) ) {
				if ( $cart_contents_count ) {
					$cart_link_active_text = '<span class="fusion-widget-cart-number">' . $cart_contents_count . '</span>';
				}
				$main_cart_class .= ' fusion-widget-cart-counter';
			} elseif ( $cart_contents_count ) {
				// If we're here, then ( Avada()->settings->get( 'woocommerce_cart_counter' ) ) is not true.
				$main_cart_class .= ' fusion-active-cart-icons';
			}
		} elseif ( 'secondary' === $position ) {
			$is_enabled             = Avada()->settings->get( 'woocommerce_cart_link_top_nav' );
			$main_cart_class        = ' fusion-secondary-menu-cart';
			$cart_link_active_class = 'fusion-secondary-menu-icon';
			/* translators: Number of items. */
			$cart_link_active_text    = sprintf( esc_html__( '%s Item(s)', 'Avada' ), $cart_contents_count ) . ' <span class="fusion-woo-cart-separator">-</span> ' . WC()->cart->get_cart_subtotal();
			$cart_link_inactive_class = $cart_link_active_class;
			$cart_link_inactive_text  = esc_html__( 'Cart', 'Avada' );
		}

		$highlight_class = '';
		if ( 'bar' === Avada()->settings->get( 'menu_highlight_style' ) ) {
			$highlight_class = ' fusion-bar-highlight';
		}
		$cart_link_markup = '<a class="' . $cart_link_active_class . $highlight_class . '" href="' . $woo_cart_page_link . '"><span class="menu-text" aria-label="' . esc_html__( 'View Cart', 'Avada' ) . '">' . $cart_link_active_text . '</span></a>';

		if ( $is_enabled ) {
			if ( is_cart() ) {
				$main_cart_class .= ' current-menu-item current_page_item';
			}

			$items = '<li class="fusion-custom-menu-item fusion-menu-cart' . $main_cart_class . '">';
			if ( $cart_contents_count ) {
				$checkout_link = wc_get_checkout_url();

				$items .= $cart_link_markup;
				$items .= '<div class="fusion-custom-menu-item-contents fusion-menu-cart-items">';
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_link = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					$thumbnail_id = ( $cart_item['variation_id'] && has_post_thumbnail( $cart_item['variation_id'] ) ) ? $cart_item['variation_id'] : $cart_item['product_id'];

					if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
						$items .= '<div class="fusion-menu-cart-item">';
						$items .= '<a href="' . $product_link . '">';
						$items .= get_the_post_thumbnail( $thumbnail_id, 'recent-works-thumbnail' );

						// Check needed for pre Woo 2.7 versions only.
						$item_name = method_exists( $_product, 'get_name' ) ? $_product->get_name() : $cart_item['data']->post->post_title;

						$items .= '<div class="fusion-menu-cart-item-details">';
						$items .= '<span class="fusion-menu-cart-item-title">' . $item_name . '</span>';

						$product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
						if ( '' !== $product_price ) {
							$product_price = ' x ' . $product_price;
						}
						$items .= '<span class="fusion-menu-cart-item-quantity">' . $cart_item['quantity'] . $product_price . '</span>';
						$items .= '</div>';
						$items .= '</a>';
						$items .= '</div>';
					}
				}

				$items .= '<div class="fusion-menu-cart-checkout">';
				$items .= '<div class="fusion-menu-cart-link"><a href="' . $woo_cart_page_link . '"><span class>' . esc_html__( 'View Cart', 'Avada' ) . '</span></a></div>';
				$items .= '<div class="fusion-menu-cart-checkout-link"><a href="' . $checkout_link . '"><span>' . esc_html__( 'Checkout', 'Avada' ) . '</span></a></div>';
				$items .= '</div>';
				$items .= '</div>';
			} else {
				$items .= '<a class="' . $cart_link_inactive_class . $highlight_class . '" href="' . $woo_cart_page_link . '"><span class="menu-text" aria-label="' . esc_html__( 'View Cart', 'Avada' ) . '">' . $cart_link_inactive_text . '</span></a>';
			}
			$items .= '</li>';
		}
		return $items;
	}
}

if ( ! function_exists( 'avada_flyout_menu_woo_cart' ) ) {
	/**
	 * Woo Cart Dropdown for Main Nav or Top Nav.
	 *
	 * @since 5.9.1
	 * @return string HTML of the flyout menu cart icon and counter.
	 */
	function avada_flyout_menu_woo_cart() {
		$cart_icon_markup = '';

		if ( class_exists( 'WooCommerce' ) && Avada()->settings->get( 'woocommerce_cart_link_main_nav' ) ) {
			$cart_link_text  = '';
			$cart_link_class = '';
			if ( Avada()->settings->get( 'woocommerce_cart_counter' ) && WC()->cart->get_cart_contents_count() ) {
				$cart_link_text  = '<span class="fusion-widget-cart-number">' . esc_html( WC()->cart->get_cart_contents_count() ) . '</span>';
				$cart_link_class = ' fusion-widget-cart-counter';
			}

			$cart_icon_markup  = '<div class="fusion-flyout-cart-wrapper">';
			$cart_icon_markup .= '<a href="' . esc_attr( get_permalink( get_option( 'woocommerce_cart_page_id' ) ) ) . '" class="fusion-icon awb-icon-shopping-cart' . esc_attr( $cart_link_class ) . '" aria-hidden="true" aria-label="' . esc_attr__( 'Toggle Shopping Cart', 'Avada' ) . '">' . $cart_link_text . '</a>';
			$cart_icon_markup .= '</div>';
		}

		return $cart_icon_markup;
	}
}

if ( ! function_exists( 'fusion_add_woo_cart_to_widget_html' ) ) {
	/**
	 * Adds cart HTML to widget.
	 *
	 * @return string The final HTML.
	 */
	function fusion_add_woo_cart_to_widget_html() {
		$items = '';

		if ( class_exists( 'WooCommerce' ) && ! is_admin() ) {
			$counter             = '';
			$class               = '';
			$items               = '';
			$cart_contents_count = WC()->cart->get_cart_contents_count();

			if ( Avada()->settings->get( 'woocommerce_cart_counter' ) ) {
				$counter = '<span class="fusion-widget-cart-number">' . $cart_contents_count . '</span>';
				$class   = 'fusion-widget-cart-counter';
			}

			if ( ! Avada()->settings->get( 'woocommerce_cart_counter' ) && $cart_contents_count ) {
				$class .= ' fusion-active-cart-icon';
			}
			$items .= '<li class="fusion-widget-cart ' . $class . '"><a href="' . get_permalink( get_option( 'woocommerce_cart_page_id' ) ) . '" class=""><span class="fusion-widget-cart-icon"></span>' . $counter . '</a></li>';
		}
		return $items;
	}
}

if ( ! function_exists( 'avada_add_woo_cart_to_nav' ) ) {
	/**
	 * Add woocommerce cart to main navigation or top navigation.
	 *
	 * @param  string       $items HTML for the main menu items.
	 * @param  array|Object $args  Arguments for the WP menu.
	 * @return string
	 */
	function avada_add_woo_cart_to_nav( $items, $args ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return $items;
		}

		// Disable woo cart on ubermenu navigations.
		$ubermenu = ( function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) && ubermenu_get_menu_instance_by_theme_location( $args->theme_location ) );

		if ( 'v6' !== Avada()->settings->get( 'header_layout' ) ) {
			if ( false === $ubermenu && 'main_navigation' === $args->theme_location || 'sticky_navigation' === $args->theme_location ) {
				$items .= avada_nav_woo_cart( 'main' );
			} elseif ( false === $ubermenu && 'top_navigation' === $args->theme_location ) {
				$items .= avada_nav_woo_cart( 'secondary' );
			}
		}

		return $items;
	}
}
add_filter( 'wp_nav_menu_items', 'avada_add_woo_cart_to_nav', 10, 2 );

if ( ! function_exists( 'avada_add_sliding_bar_icon_to_main_nav' ) ) {
	/**
	 * Add sliding bar icon to the main navigation.
	 *
	 * @param  string       $items HTML for the main menu items.
	 * @param  array|Object $args  Arguments for the WP menu.
	 * @return string
	 */
	function avada_add_sliding_bar_icon_to_main_nav( $items, $args ) {
		if ( 'menu' === Avada()->settings->get( 'slidingbar_toggle_style' ) && Avada()->settings->get( 'slidingbar_widgets' ) ) {

			// Disable sliding bar on ubermenu navigations.
			$ubermenu = ( function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) && ubermenu_get_menu_instance_by_theme_location( $args->theme_location ) );

			if ( 'v6' !== Avada()->settings->get( 'header_layout' ) && false === $ubermenu ) {
				if ( 'main_navigation' === $args->theme_location || 'sticky_navigation' === $args->theme_location ) {
					$sliding_bar_label = esc_attr__( 'Toggle Sliding Bar', 'Avada' );

					$highlight_class = '';
					if ( 'bar' === Avada()->settings->get( 'menu_highlight_style' ) ) {
						$highlight_class = ' fusion-bar-highlight';
					}

					$items .= '<li class="fusion-custom-menu-item fusion-main-menu-sliding-bar" data-classes="fusion-main-menu-sliding-bar">';
					$items .= '<a class="fusion-main-menu-icon awb-icon-sliding-bar' . $highlight_class . '" href="#" aria-label="' . $sliding_bar_label . '" data-title="' . $sliding_bar_label . '" title="' . $sliding_bar_label . '"></a>';
					$items .= '</li>';
				}
			}
		}

		return $items;
	}
}
add_filter( 'wp_nav_menu_items', 'avada_add_sliding_bar_icon_to_main_nav', 20, 2 );

if ( ! function_exists( 'avada_add_search_to_main_nav' ) ) {
	/**
	 * Add search to the main navigation.
	 *
	 * @param  string       $items HTML for the main menu items.
	 * @param  array|Object $args  Arguments for the WP menu.
	 * @return string
	 */
	function avada_add_search_to_main_nav( $items, $args ) {

		// Disable woo cart on ubermenu navigations.
		$ubermenu = ( function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) && ubermenu_get_menu_instance_by_theme_location( $args->theme_location ) );

		if ( 'v6' !== Avada()->settings->get( 'header_layout' ) && false === $ubermenu ) {
			if ( 'main_navigation' === $args->theme_location || 'sticky_navigation' === $args->theme_location ) {
				if ( Avada()->settings->get( 'main_nav_search_icon' ) ) {
					$search_label = esc_attr__( 'Search', 'Avada' );

					if ( 'overlay' === Avada()->settings->get( 'main_nav_search_layout' ) && 'top' === fusion_get_option( 'header_position' ) && 'v6' !== Avada()->settings->get( 'header_layout' ) ) {
						$items .= '<li class="fusion-custom-menu-item fusion-main-menu-search fusion-search-overlay">';
					} else {
						$items .= '<li class="fusion-custom-menu-item fusion-main-menu-search">';
					}
					$highlight_class = '';
					if ( 'bar' === Avada()->settings->get( 'menu_highlight_style' ) ) {
						$highlight_class = ' fusion-bar-highlight';
					}
					$items .= '<a class="fusion-main-menu-icon' . $highlight_class . '" href="#" aria-label="' . $search_label . '" data-title="' . $search_label . '" title="' . $search_label . '" role="button" aria-expanded="false"></a>';
					if ( 'dropdown' === Avada()->settings->get( 'main_nav_search_layout' ) || 'top' !== fusion_get_option( 'header_position' ) ) {
						$items .= '<div class="fusion-custom-menu-item-contents">';
						$items .= get_search_form( false );
						$items .= '</div>';
					}
					$items .= '</li>';
				}
			}
		}
		return $items;
	}
}
add_filter( 'wp_nav_menu_items', 'avada_add_search_to_main_nav', 20, 2 );

if ( ! function_exists( 'avada_update_featured_content_for_split_terms' ) ) {
	/**
	 * Updates post meta.
	 *
	 * @param  int    $old_term_id      The ID of the old taxonomy term.
	 * @param  int    $new_term_id      The ID of the new taxonomy term.
	 * @param  int    $term_taxonomy_id Deprecated.
	 * @param  string $taxonomy         The taxonomy.
	 */
	function avada_update_featured_content_for_split_terms( $old_term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {
		if ( 'portfolio_category' === $taxonomy ) {
			$pages = get_pages();

			if ( $pages ) {
				foreach ( $pages as $page ) {
					$page_id        = $page->ID;
					$categories     = fusion_get_page_option( 'portfolio_category', $page_id );
					$new_categories = [];
					if ( $categories ) {
						foreach ( $categories as $category ) {
							if ( '0' != $category ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
								$new_categories[] = ( isset( $category ) && $old_term_id == $category ) ? $new_term_id : $category; // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
							} else {
								$new_categories[] = '0';
							}
						}
						fusion_data()->post_meta( $page_id )->set( 'portfolio_category', $new_categories );
					}
				}
			}
		}
	}
}
add_action( 'split_shared_term', 'avada_update_featured_content_for_split_terms', 10, 4 );

if ( ! function_exists( 'avada_wp_get_http' ) ) {
	/**
	 * Perform a HTTP HEAD or GET request.
	 *
	 * If $file_path is a writable filename, this will do a GET request and write
	 * the file to that path.
	 *
	 * This is a re-implementation of the deprecated wp_get_http() function from WP Core,
	 * but this time using the recommended WP_Http() class and the WordPress filesystem.
	 *
	 * @param string      $url       URL to fetch.
	 * @param string|bool $file_path Optional. File path to write request to. Default false.
	 * @param array       $args      Optional. Arguments to be passed-on to the request.
	 * @return bool|string False on failure and string of headers if HEAD request.
	 */
	function avada_wp_get_http( $url = false, $file_path = false, $args = [] ) {

		// No need to proceed if we don't have a $url or a $file_path.
		if ( ! $url || ! $file_path ) {
			return false;
		}

		// Make sure we normalize $file_path.
		$file_path = wp_normalize_path( $file_path );

		// Include the WP_Http class if it doesn't already exist.
		if ( ! class_exists( 'WP_Http' ) ) {
			include_once wp_normalize_path( ABSPATH . WPINC . '/class-http.php' );
		}
		// Inlude the wp_remote_get function if it doesn't already exist.
		if ( ! function_exists( 'wp_remote_get' ) ) {
			include_once wp_normalize_path( ABSPATH . WPINC . '/http.php' );
		}

		$args = wp_parse_args(
			$args,
			[
				'timeout'    => 30,
				'user-agent' => 'avada-user-agent',
			]
		);

		$response = wp_remote_get( esc_url_raw( $url ), $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$body = wp_remote_retrieve_body( $response );

		// Try alternaitve method if body is empty.
		if ( empty( $body ) ) {
			$body = fusion_file_get_contents( $url );
		}

		// Return early if body is still empty.
		if ( ! $body ) {
			return false;
		}

		// Initialize the WordPress filesystem.
		$wp_filesystem = Avada_Helper::init_filesystem();

		if ( ! defined( 'FS_CHMOD_DIR' ) ) {
			define( 'FS_CHMOD_DIR', ( 0755 & ~ umask() ) );
		}
		if ( ! defined( 'FS_CHMOD_FILE' ) ) {
			define( 'FS_CHMOD_FILE', ( 0644 & ~ umask() ) );
		}

		// Attempt to write the file.
		if ( ! $wp_filesystem->put_contents( $file_path, $body, FS_CHMOD_FILE ) ) {
			// If the attempt to write to the file failed, then fallback to fwrite.
			unlink( $file_path );
			$fp = fopen( $file_path, 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions

			// Return if fopen failed.
			if ( false === $fp ) {
				return false;
			}

			$written = fwrite( $fp, $body ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			fclose( $fp ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( false === $written ) {
				return false;
			}
		}

		// If all went well, then return the headers of the request.
		if ( isset( $response['headers'] ) ) {
			$response['headers']['response'] = $response['response']['code'];
			return $response['headers'];
		}

		// If all else fails, then return false.
		return false;
	}
}

if ( ! function_exists( 'avada_ajax_avada_slider_preview' ) ) {
	/**
	 * Add slider UI to FusionBuilder
	 *
	 * @since 5.0
	 * @return void
	 */
	function avada_ajax_avada_slider_preview() {
		global $post;

		$slider_type = ( isset( $_POST['data'] ) && isset( $_POST['data']['slidertype'] ) ) ? sanitize_text_field( wp_unslash( $_POST['data']['slidertype'] ) ) : fusion_get_page_option( 'slider_type', $post->ID ); // phpcs:ignore WordPress.Security.NonceVerification
		$slider_demo = ( isset( $_POST['data'] ) && isset( $_POST['data']['demoslider'] ) ) ? sanitize_text_field( wp_unslash( $_POST['data']['demoslider'] ) ) : fusion_get_page_option( 'demo_slider', $post->ID ); // phpcs:ignore WordPress.Security.NonceVerification

		$slider_object      = false;
		$slider_type_string = '';
		$edit_link          = '';

		if ( 'layer' === $slider_type ) {
			$slider             = ( isset( $_POST['data'] ) && isset( $_POST['data']['layerslider'] ) ) ? sanitize_text_field( wp_unslash( $_POST['data']['layerslider'] ) ) : fusion_get_page_option( 'slider', $post->ID ); // phpcs:ignore WordPress.Security.NonceVerification
			$slider_type_string = 'LayerSlider';
			if ( class_exists( 'LS_Sliders' ) ) {
				$slider_object = LS_Sliders::find( $slider );
				$edit_link     = admin_url( 'admin.php?page=layerslider&action=edit&id=' . $slider );
			}
		} elseif ( 'rev' === $slider_type ) {
			$slider             = ( isset( $_POST['data'] ) && isset( $_POST['data']['revslider'] ) ) ? sanitize_text_field( wp_unslash( $_POST['data']['revslider'] ) ) : fusion_get_page_option( 'revslider', $post->ID ); // phpcs:ignore WordPress.Security.NonceVerification
			$slider_type_string = 'Slider Revolution';

			if ( class_exists( 'RevSliderSlider' ) ) {
				$slider_object = new RevSliderSlider();
				if ( $slider_object->isAliasExistsInDB( $slider ) ) {
					$slider_object->initByAlias( $slider );
					$slider_id = $slider_object->getID();

					$edit_link = admin_url( 'admin.php?page=revslider&view=slide&id=' . $slider_id );
				}
			}
		} elseif ( 'flex' === $slider_type ) {
			$slider             = ( isset( $_POST['data'] ) && isset( $_POST['data']['wooslider'] ) ) ? sanitize_text_field( wp_unslash( $_POST['data']['wooslider'] ) ) : fusion_get_page_option( 'wooslider', $post->ID ); // phpcs:ignore WordPress.Security.NonceVerification
			$slider_type_string = 'Avada Slider';
			$slider_object      = get_term_by( 'slug', $slider, 'slide-page' );
			if ( is_object( $slider_object ) ) {
				$edit_link        = admin_url( 'term.php?taxonomy=slide-page&tag_ID=' . $slider_object->term_id . '&post_type=slide' );
				$edit_slides_link = admin_url( 'edit.php?slide-page=' . $slider . '&post_type=slide' );
			}
		} elseif ( 'elastic' === $slider_type ) {
			$slider             = ( isset( $_POST['data'] ) && isset( $_POST['data']['elasticslider'] ) ) ? sanitize_text_field( wp_unslash( $_POST['data']['elasticslider'] ) ) : fusion_get_page_option( 'elasticslider', $post->ID ); // phpcs:ignore WordPress.Security.NonceVerification
			$slider_type_string = 'Elastic Slider';
			$slider_object      = get_term_by( 'slug', $slider, 'themefusion_es_groups' );
			if ( is_object( $slider_object ) ) {
				$edit_link        = admin_url( 'term.php?taxonomy=themefusion_es_groups&tag_ID=' . $slider_object->term_id . '&post_type=themefusion_elastic' );
				$edit_slides_link = admin_url( 'edit.php?themefusion_es_groups=' . $slider . '&post_type=themefusion_elastic' );
			}
		}

		// If there was a demo import, but now they have changed slider, delete the demo post meta.
		if ( isset( $slider_demo ) && ! empty( $slider_demo ) && isset( $slider ) && '0' !== $slider && is_object( $post ) ) {
			fusion_data()->post_meta( $post->ID )->delete( 'demo_slider' );
		}
		?>

		<?php if ( isset( $slider ) && '0' !== $slider && $edit_link ) : ?>

			<?php // If there is a slider set and it can be found. ?>
			<div class="fusion-builder-slider-helper">
				<h2 class="fusion-builder-slider-type"><span class="fusion-module-icon fusiona-uniF61C"></span> <?php echo esc_attr( $slider_type_string ); ?></h2>
				<p><?php esc_attr_e( 'This Slider Is Assigned Via Avada Page Options', 'Avada' ); ?></p>
				<?php /* translators: The slider ID. */ ?>
				<h4 class="fusion-builder-slider-id"><?php printf( esc_attr__( 'Slider ID: %s', 'Avada' ), esc_attr( $slider ) ); ?></h4>
				<a href="<?php echo esc_url_raw( $edit_link ); ?>" title="<?php esc_attr_e( 'Edit slider', 'Avada' ); ?>" target="_blank" rel="noopener noreferrer" class="button button-large button-primary">
					<?php esc_attr_e( 'Edit Slider', 'Avada' ); ?>
				</a>
				<?php if ( isset( $edit_slides_link ) ) : ?>
					<a href="<?php echo esc_url_raw( $edit_slides_link ); ?>" title="<?php esc_attr_e( 'Edit Slides', 'Avada' ); ?>" style="margin-left:10px" target="_blank" rel="noopener noreferrer" class="button button-large button-primary">
						<?php esc_attr_e( 'Edit Slides', 'Avada' ); ?>
					</a>
				<?php endif; ?>
				<a href="#" id="avada-slider-remove" title="<?php esc_attr_e( 'Remove Slider', 'Avada' ); ?>" style="margin-left:10px" class="button button-large button-primary">
					<?php esc_attr_e( 'Remove Slider', 'Avada' ); ?>
				</a>
			</div>

		<?php elseif ( isset( $slider_demo ) && ! empty( $slider_demo ) ) : ?>

			<?php // If there is not a found slider, but there is demo post meta. ?>
			<div class="fusion-builder-slider-helper">
				<h2 class="fusion-builder-slider-type"><span class="fusion-module-icon fusiona-uniF61C"></span> <?php echo esc_attr( $slider_type_string ); ?></h2>
				<p><?php esc_attr_e( 'This Slider Is Assigned Via Avada Page Options', 'Avada' ); ?></p>
				<?php /* translators: The slider. */ ?>
				<h4 class="fusion-builder-slider-id"><?php printf( esc_html__( 'Slider "%s" cannot be found', 'Avada' ), esc_attr( $slider_demo ) ); ?></h4>
				<a href="https://theme-fusion.com/documentation/avada/sliders/how-to-get-our-demo-sliders/" title="<?php esc_attr_e( 'Learn How To Import Sliders', 'Avada' ); ?>" target="_blank" rel="noopener noreferrer" class="button button-large button-primary">
					<?php esc_attr_e( 'Learn How To Import Sliders', 'Avada' ); ?>
				</a> <a href="#" id="avada-slider-remove" title="<?php esc_attr_e( 'Remove Slider', 'Avada' ); ?>" style="margin-left:10px" class="button button-large button-primary"><?php esc_attr_e( 'Remove Slider', 'Avada' ); ?></a>
			</div>

		<?php endif; ?>
		<?php
	}
}
add_action( 'wp_ajax_avada_slider_preview', 'avada_ajax_avada_slider_preview' );
add_action( 'fusion_builder_before_content', 'avada_ajax_avada_slider_preview' );

if ( ! function_exists( 'avada_user_agent' ) ) {
	/**
	 * Returns an avada user agent for use with premium plugin downloads.
	 *
	 * @since 5.0.2
	 * @return string
	 */
	function avada_user_agent() {
		return 'avada-user-agent';
	}
}

if ( function_exists( 'wp_cache_clean_cache' ) && ! function_exists( 'wp_cache_debug' ) ) {
	/**
	 * This is an additional function to avoid PHP Fatal issues with WP Super Cache
	 */
	function wp_cache_debug() {
	}
}

if ( class_exists( 'GFForms' ) ) {
	add_filter( 'after_setup_theme', 'avada_gravity_form_merge_tags', 10, 1 );
}

if ( ! function_exists( 'avada_gravity_form_merge_tags' ) ) {
	/**
	 * Gravity Form Merge Tags in Post Content
	 *
	 * @access  public
	 * @param array $args Array of bool auto_append_eid and encrypt_eid.
	 */
	function avada_gravity_form_merge_tags( $args = [] ) {
		Avada_Gravity_Forms_Tags_Merger::get_instance( $args );
	}
}

if ( ! function_exists( 'avada_sliders_container' ) ) {
	/**
	 * Renders the slider container with slider and fallback image.
	 *
	 * @since 5.5
	 * @return void
	 */
	function avada_sliders_container() {
		$queried_object_id  = get_queried_object_id();
		$slider_page_id     = '';
		$visibility_options = [ 'small-visibility', 'medium-visibility', 'large-visibility' ];
		$visibility_values  = $visibility_options;
		$is_archive         = false;
		$should_render      = false;

		if ( ! is_search() ) {
			$slider_page_id = '';
			if ( ( ! is_home() && ! is_front_page() && ! is_archive() && isset( $queried_object_id ) ) || ( ! is_home() && is_front_page() && isset( $queried_object_id ) ) ) {
				$slider_page_id = $queried_object_id;
			}
			if ( is_home() && ! is_front_page() ) {
				$slider_page_id = get_option( 'page_for_posts' );
			}
			if ( class_exists( 'WooCommerce' ) && is_shop() ) {
				$slider_page_id = get_option( 'woocommerce_shop_page_id' );
			}
			if ( ! is_home() && ! is_front_page() && ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && isset( $queried_object_id ) && ( ! ( class_exists( 'WooCommerce' ) && is_shop() ) ) ) {
				$slider_page_id = $queried_object_id;
				$is_archive     = true;
				$should_render  = true;
			}
			$slider_post_status = get_post_status( $slider_page_id );
			if ( ( 'publish' === $slider_post_status && ! post_password_required() && ! is_archive() && ! Avada_Helper::bbp_is_topic_tag() ) || ( 'publish' === $slider_post_status && ! post_password_required() && ( class_exists( 'WooCommerce' ) && is_shop() ) ) || ( current_user_can( 'read_private_pages' ) && in_array( $slider_post_status, [ 'private', 'draft', 'pending', 'future' ], true ) ) ) {
				$is_archive    = ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && ! ( class_exists( 'WooCommerce' ) && is_shop() );
				$should_render = true;

			}

			$visibility_values = Avada_Helper::get_slider_visibility( $slider_page_id, $is_archive );
			$visibility_values = empty( $visibility_values ) ? $visibility_options : $visibility_values;
			$visibility_values = ! is_array( $visibility_values ) ? explode( ',', $visibility_values ) : $visibility_values;
		}
		?>
		<div id="sliders-container" class="<?php echo avada_slider_visibility( $visibility_values, $visibility_options ); // phpcs:ignore WordPress.Security.EscapeOutput ?>">
			<?php
			do_action( 'avada_sliders_inside_container_start' );
			if ( $should_render ) {
				avada_slider( $slider_page_id, $is_archive, $visibility_values );
			}
			?>
		</div>
		<?php
		$slider_fallback    = $is_archive ? fusion_data()->term_meta( $slider_page_id )->get( 'fallback[url]' ) : fusion_data()->post_meta( $slider_page_id )->get( 'fallback[url]' );
		$slider_fallback_id = $is_archive ? fusion_data()->term_meta( $slider_page_id )->get( 'fallback[id]' ) : fusion_data()->post_meta( $slider_page_id )->get( 'fallback[id]' );
		$slider_type        = Avada_Helper::get_slider_type( $slider_page_id, $is_archive );
		?>
		<?php if ( $slider_fallback && $slider_type && 'no' !== $slider_type ) : ?>
			<?php $slider_fallback_image_data = Avada()->images->get_attachment_data_by_helper( $slider_fallback_id, $slider_fallback ); ?>
			<div id="fallback-slide">
				<img src="<?php echo esc_url( $slider_fallback ); ?>" width="<?php echo esc_attr( $slider_fallback_image_data['width'] ); ?>" height="<?php echo esc_attr( $slider_fallback_image_data['height'] ); ?>" alt="<?php echo esc_attr( $slider_fallback_image_data['alt'] ); ?>" />
			</div>
			<?php
		endif;

		do_action( 'avada_sliders_inside_container_end' );
	}
}

if ( ! function_exists( 'avada_header_template' ) ) {
	/**
	 * Avada Header Template Function.
	 *
	 * @param  string $slider_position Show header below or above slider.
	 * @param  bool   $is_archive Flag for archive pages.
	 * @return void
	 */
	function avada_header_template( $slider_position = 'below', $is_archive = false ) {
		$slider_position  = 'above' === $slider_position ? 'above' : 'below';
		$page_id          = Avada()->fusion_library->get_page_id();
		$reverse_position = 'below' === $slider_position ? 'above' : 'below';
		$menu_text_align  = '';

		$theme_option_slider_position = strtolower( fusion_get_option( 'slider_position' ) );
		$page_option_slider_position  = ( true === $is_archive )
			? fusion_data()->term_meta( $page_id )->get( 'slider_position' )
			: fusion_data()->post_meta( $page_id )->get( 'slider_position' );

		if ( ( ! $theme_option_slider_position || ( $slider_position === $theme_option_slider_position && $reverse_position !== $page_option_slider_position ) || ( $theme_option_slider_position !== $slider_position && $slider_position === $page_option_slider_position ) ) && ! is_page_template( 'blank.php' ) && 'no' !== fusion_get_page_option( 'display_header', $page_id ) && 'top' === strtolower( fusion_get_option( 'header_position' ) ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons
			$header_wrapper_class  = 'fusion-header-wrapper';
			$header_wrapper_class .= ( Avada()->settings->get( 'header_shadow' ) ) ? ' fusion-header-shadow' : '';

			/**
			 * The avada_before_header_wrapper hook.
			 */
			do_action( 'avada_before_header_wrapper' );

			$sticky_header_logo = Avada()->settings->get( 'sticky_header_logo' );
			$sticky_header_logo = ( is_array( $sticky_header_logo ) && isset( $sticky_header_logo['url'] ) && $sticky_header_logo['url'] ) ? true : false;
			$mobile_logo        = Avada()->settings->get( 'mobile_logo' );
			$mobile_logo        = ( is_array( $mobile_logo ) && isset( $mobile_logo['url'] ) && $mobile_logo['url'] ) ? true : false;

			$sticky_header_type2_layout = '';

			if ( in_array( Avada()->settings->get( 'header_layout' ), [ 'v4', 'v5' ], true ) ) {
				$sticky_header_type2_layout = ( 'menu_and_logo' === Avada()->settings->get( 'header_sticky_type2_layout' ) ) ? ' fusion-sticky-menu-and-logo' : ' fusion-sticky-menu-only';
				$menu_text_align            = 'fusion-header-menu-align-' . Avada()->settings->get( 'menu_text_align' );
			}

			$fusion_header_class = 'fusion-header-' . Avada()->settings->get( 'header_layout' ) . ' fusion-logo-alignment fusion-logo-' . strtolower( Avada()->settings->get( 'logo_alignment' ) ) . ' fusion-sticky-menu-' . has_nav_menu( 'sticky_navigation' ) . ' fusion-sticky-logo-' . $sticky_header_logo . ' fusion-mobile-logo-' . $mobile_logo . $sticky_header_type2_layout . ' ' . $menu_text_align;

			if ( 'v6' !== Avada()->settings->get( 'header_layout' ) ) {
				$fusion_header_class .= ' fusion-mobile-menu-design-' . strtolower( Avada()->settings->get( 'mobile_menu_design' ) );
			}

			if ( 'v6' === Avada()->settings->get( 'header_layout' ) || 'flyout' === Avada()->settings->get( 'mobile_menu_design' ) ) {
				$fusion_header_class .= ' fusion-header-has-flyout-menu';
			}
			?>

			<header class="<?php echo esc_attr( $header_wrapper_class ); ?>">
				<div class="<?php echo esc_attr( $fusion_header_class ); ?>">
					<?php
					/**
					 * The avada_header hook.
					 *
					 * @hooked avada_secondary_header - 10.
					 * @hooked avada_header_1 - 20 (adds header content for header v1-v3,v6,v7).
					 * @hooked avada_header_2 - 20 (adds header content for header v4-v5).
					 */
					do_action( 'avada_header' );
					?>
				</div>
				<div class="fusion-clearfix"></div>
			</header>
			<?php
			/**
			 * The avada_after_header_wrapper hook.
			 */
			do_action( 'avada_after_header_wrapper' );
		}
	}
}

if ( ! function_exists( 'avada_side_header' ) ) {
	/**
	 * Avada Side Header Template Function.
	 *
	 * @return void
	 */
	function avada_side_header() {
		if ( ! is_page_template( 'blank.php' ) && 'no' !== fusion_get_page_option( 'display_header', get_queried_object_id() ) ) {
			get_template_part( 'templates/side-header' );
		}
	}
}

if ( ! function_exists( 'avada_secondary_header' ) ) {
	/**
	 * Gets the header-secondary template if needed.
	 */
	function avada_secondary_header() {
		if ( ! in_array( Avada()->settings->get( 'header_layout' ), [ 'v2', 'v3', 'v4', 'v5' ], true ) ) {
			return;
		}
		if ( 'leave_empty' !== fusion_get_option( 'header_left_content' ) || 'leave_empty' !== fusion_get_option( 'header_right_content' ) ) {
			get_template_part( 'templates/header-secondary' );
		}
	}
}
add_action( 'avada_header', 'avada_secondary_header', 10 );

if ( ! function_exists( 'avada_header_1' ) ) {
	/**
	 * Gets the header-1 template if needed.
	 */
	function avada_header_1() {
		if ( in_array( Avada()->settings->get( 'header_layout' ), [ 'v1', 'v2', 'v3' ], true ) ) {
			get_template_part( 'templates/header-1' );
		}
	}
}
add_action( 'avada_header', 'avada_header_1', 20 );

if ( ! function_exists( 'avada_header_2' ) ) {
	/**
	 * Gets the header-2 template if needed.
	 */
	function avada_header_2() {
		if ( in_array( Avada()->settings->get( 'header_layout' ), [ 'v4', 'v5' ], true ) ) {
			get_template_part( 'templates/header-2' );
		}
	}
}
add_action( 'avada_header', 'avada_header_2', 20 );

if ( ! function_exists( 'avada_header_3' ) ) {
	/**
	 * Getys the header-3 template if needed.
	 */
	function avada_header_3() {
		if ( 'v6' === Avada()->settings->get( 'header_layout' ) ) {
			get_template_part( 'templates/header-3' );
		}
	}
}
add_action( 'avada_header', 'avada_header_3', 10 );

if ( ! function_exists( 'avada_header_4' ) ) {
	/**
	 * Gets the template part for the v7 header.
	 *
	 * @since 5.0
	 */
	function avada_header_4() {
		if ( 'v7' === Avada()->settings->get( 'header_layout' ) ) {
			get_template_part( 'templates/header-4' );
		}
	}
}
add_action( 'avada_header', 'avada_header_4', 10 );

if ( ! function_exists( 'avada_secondary_main_menu' ) ) {
	/**
	 * Gets the secondary menu template if needed.
	 */
	function avada_secondary_main_menu() {
		if ( in_array( Avada()->settings->get( 'header_layout' ), [ 'v4', 'v5' ], true ) ) {
			get_template_part( 'templates/header-secondary-main-menu' );
		}
	}
}
add_action( 'avada_header', 'avada_secondary_main_menu', 30 );

if ( ! function_exists( 'avada_logo' ) ) {
	/**
	 * Gets the logo template if needed.
	 */
	function avada_logo() {
		// No need to proceed any further if no logo is set.
		if ( '' !== Avada()->settings->get( 'logo' ) || '' !== Avada()->settings->get( 'logo_retina' ) ) {
			get_template_part( 'templates/logo' );
		}
	}
}

if ( ! function_exists( 'avada_main_menu' ) ) {
	/**
	 * The main menu.
	 *
	 * @param bool $flyout_menu Whether we want the flyout menu or not.
	 */
	function avada_main_menu( $flyout_menu = false ) {

		$menu_class = 'fusion-menu';
		if ( 'v7' === Avada()->settings->get( 'header_layout' ) ) {
			$menu_class .= ' fusion-middle-logo-ul';
		}

		$main_menu_args = [
			'theme_location' => 'main_navigation',
			'depth'          => 5,
			'menu_class'     => $menu_class,
			'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
			'fallback_cb'    => 'Fusion_Nav_Walker::fallback',
			'walker'         => new Fusion_Nav_Walker(),
			'container'      => false,
			'item_spacing'   => 'discard',
			'echo'           => false,
		];

		if ( $flyout_menu ) {
			$flyout_menu_args = apply_filters(
				'fusion_flyout_menu_args',
				[
					'depth'     => 1,
					'container' => false,
				]
			);

			$main_menu_args = wp_parse_args( $flyout_menu_args, $main_menu_args );

			$main_menu = wp_nav_menu( $main_menu_args );

			if ( has_nav_menu( 'sticky_navigation' ) ) {
				$sticky_menu_args = [
					'theme_location' => 'sticky_navigation',
					'menu_id'        => 'menu-main-menu-1',
					'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
					'walker'         => new Fusion_Nav_Walker(),
					'item_spacing'   => 'discard',
				];
				$sticky_menu_args = wp_parse_args( $sticky_menu_args, $main_menu_args );
				$main_menu       .= wp_nav_menu( $sticky_menu_args );
			}

			return $main_menu;

		} else {
			$additional_menu_class = '';
			if ( function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) && ubermenu_get_menu_instance_by_theme_location( 'main_navigation' ) ) {
				$additional_menu_class = ' fusion-ubermenu';

				if ( ! function_exists( 'ubermenu_op' ) || 'on' !== ubermenu_op( 'disable_mobile', 'main' ) ) {
					$additional_menu_class .= ' fusion-ubermenu-mobile';
				}
			}

			if ( 'v7' === Avada()->settings->get( 'header_layout' ) && ! has_nav_menu( 'sticky_navigation' ) ) {
				$additional_menu_class .= ' fusion-main-menu-sticky';
			}

			echo '<nav class="fusion-main-menu' . esc_attr( $additional_menu_class ) . '" aria-label="' . esc_attr__( 'Main Menu', 'Avada' ) . '">';
			if ( 'overlay' === Avada()->settings->get( 'main_nav_search_layout' ) && 'top' === fusion_get_option( 'header_position' ) && 'v6' !== Avada()->settings->get( 'header_layout' ) ) {
				echo '<div class="fusion-overlay-search">';
				get_search_form( true );
				echo '<div class="fusion-search-spacer"></div>';
				echo '<a href="#" role="button" aria-label="' . esc_attr__( 'Close Search', 'Avada' ) . '" class="fusion-close-search"></a>';
				echo '</div>';
			}
			echo wp_nav_menu( $main_menu_args );
			echo '</nav>';

			if ( has_nav_menu( 'sticky_navigation' ) && 'top' === fusion_get_option( 'header_position' ) && ( ! function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) || ( function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) && ! ubermenu_get_menu_instance_by_theme_location( 'sticky_navigation' ) ) ) ) {

				$sticky_menu_args = [
					'theme_location' => 'sticky_navigation',
					'walker'         => new Fusion_Nav_Walker(),
					'item_spacing'   => 'discard',
				];

				$sticky_menu_args = wp_parse_args( $sticky_menu_args, $main_menu_args );

				echo '<nav class="fusion-main-menu fusion-sticky-menu" aria-label="' . esc_attr__( 'Main Menu Sticky', 'Avada' ) . '">';
				if ( 'overlay' === Avada()->settings->get( 'main_nav_search_layout' ) && 'top' === fusion_get_option( 'header_position' ) && 'v6' !== Avada()->settings->get( 'header_layout' ) ) {
					echo '<div class="fusion-overlay-search">';
					get_search_form( true );
					echo '<div class="fusion-search-spacer"></div>';
					echo '<a href="#" role="button" aria-label="' . esc_attr__( 'Close Search', 'Avada' ) . '" class="fusion-close-search"></a>';
					echo '</div>';
				}
				echo wp_nav_menu( $sticky_menu_args );
				echo '</nav>';
			}

			// Make sure mobile menu is not loaded when we use slideout menu or ubermenu.
			if ( ! function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) || ( function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) && ( ! ubermenu_get_menu_instance_by_theme_location( 'main_navigation' ) || ( ubermenu_get_menu_instance_by_theme_location( 'main_navigation' ) && function_exists( 'ubermenu_op' ) && 'on' === ubermenu_op( 'disable_mobile', 'main' ) ) ) ) ) {
				if ( has_nav_menu( 'mobile_navigation' ) ) {
					$mobile_menu_args = [
						'theme_location'  => 'mobile_navigation',
						'menu_class'      => 'fusion-mobile-menu',
						'depth'           => 5,
						'walker'          => new Fusion_Nav_Walker(),
						'item_spacing'    => 'discard',
						'container_class' => 'fusion-mobile-navigation',
					];
					echo wp_nav_menu( $mobile_menu_args );
				}
				avada_mobile_main_menu();
			}
		}
	}
}

if ( ! function_exists( 'avada_default_menu_fallback' ) ) {
	/**
	 * Return null.
	 *
	 * @param array $args Menu arguments. Irrelevant in this context.
	 * @return null
	 */
	function avada_default_menu_fallback( $args ) {
		return null;
	}
}

if ( ! function_exists( 'avada_mobile_menu_search' ) ) {
	/**
	 * Gets the mobile menu search template if needed.
	 */
	function avada_mobile_menu_search() {
		if ( Avada()->settings->get( 'mobile_menu_search' ) && 'flyout' !== Avada()->settings->get( 'mobile_menu_design' ) ) {
			get_template_part( 'templates/menu-mobile-search' );
		}
	}
}

if ( ! function_exists( 'avada_contact_info' ) ) {
	/**
	 * Returns the markup for the contact-info area.
	 */
	function avada_contact_info() {
		$phone_number    = do_shortcode( Avada()->settings->get( 'header_number' ) );
		$email           = Avada()->settings->get( 'header_email' );
		$header_position = fusion_get_option( 'header_position' );
		if ( ! apply_filters( 'fusion_disable_antispambot', false ) ) {
			$email = antispambot( $email );
		}

		$html = '';

		if ( $phone_number || $email ) {
			$html .= '<div class="fusion-contact-info">';
			$html .= '<span class="fusion-contact-info-phone-number">' . $phone_number . '</span>';
			if ( $phone_number && $email ) {
				if ( 'top' === $header_position ) {
					$html .= '<span class="fusion-header-separator">' . apply_filters( 'avada_header_separator', '|' ) . '</span>';
				} else {
					$html .= '<br />';
				}
			}
			if ( $email ) {
				$html .= '<span class="fusion-contact-info-email-address">' . sprintf( apply_filters( 'avada_header_contact_info_email', '<a href="mailto:%s">%s</a>' ), $email, $email ) . '</span>';
			}
			$html .= '</div>';
		}
		return $html;
	}
}

if ( ! function_exists( 'avada_secondary_nav' ) ) {
	/**
	 * Returns the markup for nav menu.
	 */
	function avada_secondary_nav() {
		if ( has_nav_menu( 'top_navigation' ) ) {
			return wp_nav_menu(
				[
					'theme_location' => 'top_navigation',
					'depth'          => 5,
					'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
					'container'      => false,
					'fallback_cb'    => 'Fusion_Nav_Walker::fallback',
					'walker'         => new Fusion_Nav_Walker(),
					'echo'           => false,
					'item_spacing'   => 'discard',
				]
			);
		}
	}
}

if ( ! function_exists( 'avada_header_social_links' ) ) {
	/**
	 * Return the social links markup.
	 *
	 * @return string
	 */
	function avada_header_social_links() {
		$social_icons = fusion_get_social_icons_class();

		$html = '';

		if ( $social_icons ) {
			$options = [
				'position'          => 'header',
				'icon_boxed'        => Avada()->settings->get( 'header_social_links_boxed' ),
				'tooltip_placement' => fusion_get_option( 'header_social_links_tooltip_placement' ),
				'linktarget'        => Avada()->settings->get( 'social_icons_new' ),
			];

			$render_social_icons = $social_icons->render_social_icons( $options );
			$html                = ( $render_social_icons ) ? '<div class="fusion-social-links-header">' . $render_social_icons . '</div>' : '';
		}

		return $html;
	}
}

if ( ! function_exists( 'avada_secondary_header_content' ) ) {
	/**
	 * Get the secondary header content based on the content area.
	 *
	 * @param  string $content_area Secondary header content area from theme optins.
	 * @return string               Html for the content.
	 */
	function avada_secondary_header_content( $content_area ) {
		$secondary_content  = '';
		$content_to_display = Avada()->settings->get( $content_area );
		if ( 'contact_info' === $content_to_display ) {
			$secondary_content = avada_contact_info();
		} elseif ( 'social_links' === $content_to_display ) {
			$secondary_content = avada_header_social_links();
		} elseif ( 'navigation' === $content_to_display ) {
			$mobile_menu_wrapper = '';
			if ( has_nav_menu( 'top_navigation' ) ) {

				$mobile_menu_text_align = ' fusion-mobile-menu-text-align-' . Avada()->settings->get( 'mobile_menu_text_align' );
				$mobile_menu_wrapper    = '<nav class="fusion-mobile-nav-holder' . esc_attr( $mobile_menu_text_align ) . '" aria-label="' . esc_attr__( 'Secondary Mobile Menu', 'Avada' ) . '"></nav>';
			}

			$secondary_menu    = '<nav class="fusion-secondary-menu" role="navigation" aria-label="' . esc_attr__( 'Secondary Menu', 'Avada' ) . '">';
			$secondary_menu   .= avada_secondary_nav();
			$secondary_menu   .= '</nav>';
			$secondary_content = $secondary_menu . $mobile_menu_wrapper;
		}

		return apply_filters( 'avada_secondary_header_content', $secondary_content, $content_area, $content_to_display );
	}
}

if ( ! function_exists( 'avada_header_content_3' ) ) {
	/**
	 * Renders the 3rd content in headers.
	 */
	function avada_header_content_3() {
		get_template_part( 'templates/header-content-3' );
	}
}

if ( 'top' === fusion_get_option( 'header_position' ) ) {
	add_action( 'avada_logo_append', 'avada_header_content_3', 10 );
}

if ( ! function_exists( 'avada_header_banner' ) ) {
	/**
	 * Returns the header banner.
	 *
	 * @return string
	 */
	function avada_header_banner() {
		return '<div class="fusion-header-banner">' . do_shortcode( Avada()->settings->get( 'header_banner_code' ) ) . '</div>';
	}
}

if ( ! function_exists( 'avada_header_tagline' ) ) {
	/**
	 * Returns the headers tagline.
	 *
	 * @return string
	 */
	function avada_header_tagline() {
		return '<h3 class="fusion-header-tagline">' . do_shortcode( Avada()->settings->get( 'header_tagline' ) ) . '</h3>';
	}
}

if ( ! function_exists( 'avada_modern_menu' ) ) {
	/**
	 * Gets the menu-mobile-modern template part.
	 *
	 * @return string
	 */
	function avada_modern_menu() {
		ob_start();
		get_template_part( 'templates/menu-mobile-modern' );
		return ob_get_contents();
	}
}

if ( ! function_exists( 'avada_mobile_main_menu' ) ) {
	/**
	 * Gets the menu-mobile-main template part.
	 */
	function avada_mobile_main_menu() {
		get_template_part( 'templates/menu-mobile-main' );
	}
}

if ( ! function_exists( 'avada_get_available_sliders_array' ) ) {
	/**
	 * Get array of available FS, LS, Rev and Elastic Sliders.
	 *
	 * @access public
	 * @since 5.3
	 * @return array
	 */
	function avada_get_available_sliders_array() {

		$sliders = [
			'layer_sliders'   => [],
			'fusion_sliders'  => [],
			'rev_sliders'     => [],
			'elastic_sliders' => [],
		];

		global $wpdb;
		$slides_array[0] = esc_html__( 'Select a slider', 'Avada' );

		// LayerSliders.
		if ( class_exists( 'LS_Sliders' ) ) {

			// Get sliders.
			$sliders = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}layerslider WHERE flag_hidden = %d AND flag_deleted = %d ORDER BY date_c ASC", '0', '0' ) );

			if ( ! empty( $sliders ) ) {
				foreach ( $sliders as $key => $item ) {
					$slides[ $item->id ] = $item->name . ' (#' . $item->id . ')';
				}
			}

			if ( isset( $slides ) && ! empty( $slides ) ) {
				foreach ( $slides as $key => $val ) {
					$slides_array[ $key ] = $val;
				}
			}
		}

		$sliders['layer_sliders'] = $slides_array;

		// Avada Sliders.
		if ( method_exists( 'FusionCore_Plugin', 'get_fusion_sliders' ) ) {
			$fusion_sliders            = esc_attr__( 'Select a slider', 'Avada' );
			$sliders['fusion_sliders'] = FusionCore_Plugin::get_fusion_sliders();
			array_unshift( $sliders['fusion_sliders'], $fusion_sliders );
		}

		// Slider Revolution sliders.
		$revsliders[0] = esc_attr__( 'Select a slider', 'Avada' );

		if ( function_exists( 'rev_slider_shortcode' ) ) {
			$slider_object = new RevSliderSlider();
			$sliders_array = $slider_object->getArrSliders();

			if ( $sliders_array ) {
				foreach ( $sliders_array as $slider ) {
					$revsliders[ $slider->getAlias() ] = $slider->getTitle() . ' (#' . $slider->getID() . ')';
				}
			}
		}

		$sliders['rev_sliders'] = $revsliders;

		// Elastic Sliders.
		$slides_array    = [];
		$slides_array[0] = esc_html__( 'Select a slider', 'Avada' );
		if ( true === taxonomy_exists( 'themefusion_es_groups' ) ) {
			$slides = get_terms( 'themefusion_es_groups' );
			if ( $slides && ! isset( $slides->errors ) ) {
				$slides = maybe_unserialize( $slides );
				foreach ( $slides as $key => $val ) {
					$slides_array[ $val->slug ] = $val->name . ' (#' . $val->term_id . ')';
				}
			}
		}

		$sliders['elastic_sliders'] = $slides_array;

		return $sliders;
	}
}

if ( ! function_exists( 'avada_get_available_sliders_dropdown' ) ) {
	/**
	 * Get array of available sliders for PO / Tax Panel.
	 *
	 * @access public
	 * @since 6.0
	 * @return array
	 */
	function avada_get_available_sliders_dropdown() {

		$active_slider_plugins = [
			'no' => esc_attr__( 'No Slider', 'Avada' ),
		];

		if ( class_exists( 'LS_Sliders' ) ) {
			$active_slider_plugins['layer'] = 'LayerSlider';
		}

		if ( method_exists( 'FusionCore_Plugin', 'get_fusion_sliders' ) ) {
			$active_slider_plugins['flex'] = esc_attr__( 'Avada Slider', 'Avada' );
		}

		if ( function_exists( 'rev_slider_shortcode' ) ) {
			$active_slider_plugins['rev'] = 'Slider Revolution';
		}

		if ( true === taxonomy_exists( 'themefusion_es_groups' ) ) {
			$active_slider_plugins['elastic'] = 'Elastic Slider';
		}

		return $active_slider_plugins;
	}
}

if ( ! function_exists( 'avada_get_sliders_note' ) ) {
	/**
	 * Get note about available sliders for PO / Tax panel.
	 *
	 * @access public
	 * @since 6.0
	 * @param array $sliders Array of available sliders.
	 * @param array $active_slider_plugins Array of active slider plugins.
	 * @return string
	 */
	function avada_get_sliders_note( $sliders, $active_slider_plugins ) {

		if ( empty( $sliders ) ) {
			$sliders = avada_get_available_sliders_array();
		}

		if ( empty( $active_slider_plugins ) ) {
			$active_slider_plugins = avada_get_available_sliders_dropdown();
		}

		// Elastic slider is ommited on purpose.
		$slider_types = [
			'flex'  => admin_url( 'edit-tags.php?taxonomy=slide-page&post_type=slide' ),
			'rev'   => admin_url( 'admin.php?page=revslider' ),
			'layer' => admin_url( 'admin.php?page=layerslider' ),
		];

		$slider_links = [];
		foreach ( $slider_types as $key => $edit_link ) {
			if ( isset( $active_slider_plugins[ $key ] ) ) {
				$slider_links[] = '<a href="' . $edit_link . '" target="_blank">' . $active_slider_plugins[ $key ] . '</a>';
			}
		}

		// Empty string returned in case of an empty array.
		$slider_links   = implode( ', ', $slider_links );
		$last_comma_pos = strrpos( $slider_links, ', ' );
		if ( false !== $last_comma_pos ) {
			$slider_links = substr_replace( $slider_links, ' ' . __( 'or', 'Avada' ) . ' ', $last_comma_pos, 2 );
		}

		// There are no sliders created.
		if ( 1 >= count( $sliders['layer_sliders'] ) && 1 >= count( $sliders['fusion_sliders'] ) && 1 >= count( $sliders['rev_sliders'] ) && 1 >= count( $sliders['elastic_sliders'] ) ) {
			$slider_note = '' !== $slider_links

				/* translators: Comma separated slider plugins, ie. Avada Slider, Revolution Slider or Layer Slider. */
				? sprintf( __( '<strong>IMPORTANT NOTE:</strong> No sliders have been created yet, click here to create a %s.', 'Avada' ), $slider_links )

				/* translators: URL. */
				: sprintf( __( '<strong>IMPORTANT NOTE:</strong> Currently there are no slider plugins active, click <a href="%s" target="_blank">here</a> to activate one.', 'Avada' ), admin_url( 'admin.php?page=avada-plugins' ) );
		} else {

			// There is at least one slider created.
			/* translators: Comma separated slider plugins, ie. Avada Slider, Revolution Slider or Layer Slider. */
			$slider_note = sprintf( __( '<strong>IMPORTANT NOTE:</strong> Click here to edit or to create a %s.', 'Avada' ), $slider_links );
		}

		return $slider_note;
	}
}

if ( ! function_exists( 'avada_wrap_embed_with_div' ) ) {
	/**
	 * Wrap video embeds in WP core with our custom wrapper if Avada Builder is not active.
	 *
	 * @since 5.3
	 * @param string $html HTML generated with video embeds.
	 * @return string
	 */
	function avada_wrap_embed_with_div( $html ) {
		return '<div class="avada-video-embed"><div class="fluid-width-video-wrapper">' . $html . '</div></div>';
	}
}

if ( ! class_exists( 'FusionBuilder' ) ) {
	add_filter( 'embed_oembed_html', 'avada_wrap_embed_with_div', 10 );

	// Add jetpack compatibility.
	if ( apply_filters( 'is_jetpack_site', false ) ) {
		add_filter( 'video_embed_html', 'avada_wrap_embed_with_div', 10 );
	}
}

if ( ! function_exists( 'avada_the_html_class' ) ) {
	/**
	 * Echo classes for <html>.
	 *
	 * @param string $class Any additional classes.
	 */
	function avada_the_html_class( $class = '' ) {
		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
		$classes    = [];

		if ( $class ) {
			$classes = explode( ' ', $class );
		}

		// Make sure an array.
		if ( ! is_array( $classes ) ) {
			$classes = (array) $classes;
		}

		// Add layout class.
		$classes[] = 'avada-html-layout-' . fusion_get_option( 'layout' );
		$classes[] = 'avada-html-header-position-' . fusion_get_option( 'header_position' );

		if ( 'framed' === fusion_get_option( 'scroll_offset' ) ) {
			$classes[] = 'avada-html-layout-framed';
		}

		if ( is_archive() && ( ! function_exists( 'is_shop' ) || function_exists( 'is_shop' ) && ! is_shop() ) ) {
			$classes[] = 'avada-html-is-archive';
		}

		if ( false !== strpos( fusion_get_option( 'site_width' ), '%' ) ) {
			$classes[] = 'avada-has-site-width-percent';
		}

		if ( apply_filters( 'fusion_is_hundred_percent_template', false ) ) {
			$classes[] = 'avada-is-100-percent-template';
		}

		if ( '100%' === fusion_get_option( 'site_width' ) ) {
			$classes[] = 'avada-has-site-width-100-percent';
		}

		if ( ! fusion_get_option( 'responsive' ) ) {
			$classes[] = 'avada-html-not-responsive';
		}

		if ( 1 > Fusion_Color::new_color( Avada_Helper::get_header_color( false, false ) )->alpha ) {
			$classes[] = 'avada-header-color-not-opaque';
		}

		if ( 1 > Fusion_Color::new_color( Avada_Helper::get_header_color( false, true ) )->alpha ) {
			$classes[] = 'avada-mobile-header-color-not-opaque';
		}

		if ( '' !== fusion_get_option( 'bg_image[url]' ) ) {
			$classes[] = 'avada-html-has-bg-image';
		}

		if ( fusion_get_option( 'bg_pattern_option' ) && ! ( fusion_get_page_option( 'bg_color' ) || fusion_get_page_option( 'bg_image[url]' ) ) ) {
			$classes[] = 'avada-has-page-background-pattern';
		}

		if ( fusion_get_option( 'custom_scrollbar' ) ) {
			$classes[] = 'awb-scroll';
		}

		echo esc_attr( implode( ' ', apply_filters( 'avada_the_html_class', $classes ) ) );
	}
}

if ( ! function_exists( 'avada_get_sidebar_post_meta_option_name' ) ) {
	/**
	 * Get the post-meta name depending on the post-type.
	 *
	 * @since 6.2.0
	 * @param string $post_type The post-type.
	 * @return string
	 */
	function avada_get_sidebar_post_meta_option_names( $post_type ) {
		$sidebars = [ '', '', '', '' ];

		switch ( $post_type ) {
			case 'page':
				$sidebars = [ 'pages_sidebar', 'pages_sidebar_2', 'default_sidebar_pos', 'pages_global_sidebar' ];
				break;

			case 'avada_portfolio':
				$sidebars = [ 'portfolio_sidebar', 'portfolio_sidebar_2', 'portfolio_sidebar_position', 'portfolio_global_sidebar' ];
				break;

			case 'product':
				$sidebars = [ 'woo_sidebar', 'woo_sidebar_2', 'woo_sidebar_position', 'woo_global_sidebar' ];
				break;

			case 'tribe_events':
			case 'tribe_organizer':
			case 'tribe_venue':
				$sidebars = [ 'ec_sidebar', 'ec_sidebar_2', 'ec_sidebar_pos', 'ec_global_sidebar' ];
				break;

			case 'forum':
			case 'topic':
			case 'reply':
				$sidebars = [ 'ppbress_sidebar', 'ppbress_sidebar_2', 'bbpress_sidebar_position', 'bbpress_global_sidebar' ];
				break;

			case false:
				break;

			default:
				$sidebars = [ 'posts_sidebar', 'posts_sidebar_2', 'blog_sidebar_position', 'posts_global_sidebar' ];
				break;
		}

		$override = function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_override( 'content' ) : false;
		if ( $override ) {
			$sidebars[0] = 'template_sidebar';
			$sidebars[1] = 'template_sidebar_2';
			$sidebars[2] = 'template_sidebar_position';
		}

		return apply_filters( 'avada_sidebar_post_meta_option_names', $sidebars, $post_type );
	}
}

if ( ! function_exists( 'avada_get_demo_import_stages' ) ) {
	/**
	 * Helper function which returns all demo import stages.
	 *
	 * @since 7.3
	 * @return array
	 */
	function avada_get_demo_import_stages() {

		return [
			[
				'value'              => 'post',
				'label'              => esc_html__( 'Posts', 'Avada' ),
				'data'               => 'content',
				'feature_dependency' => 'post',
			],
			[
				'value'              => 'page',
				'label'              => esc_html__( 'Pages', 'Avada' ),
				'data'               => 'content',
				'feature_dependency' => 'page',
			],
			[
				'value'              => 'avada_portfolio',
				'label'              => esc_html__( 'Portfolios', 'Avada' ),
				'data'               => 'content',
				'feature_dependency' => 'avada_portfolio',
			],
			[
				'value'              => 'avada_faq',
				'label'              => esc_html__( 'FAQs', 'Avada' ),
				'data'               => 'content',
				'feature_dependency' => 'avada_faq',
			],
			[
				'value'              => 'avada_layout',
				'label'              => esc_html__( 'Layouts', 'Avada' ),
				'data'               => 'content',
				'feature_dependency' => 'avada_layout',
			],
			[
				'value'              => 'fusion_icons',
				'label'              => esc_html__( 'Icons', 'Avada' ),
				'data'               => 'content',
				'feature_dependency' => 'fusion_icons',
			],
			[
				'value'              => 'fusion_form',
				'label'              => esc_html__( 'Forms', 'Avada' ),
				'data'               => 'content',
				'feature_dependency' => 'fusion_form', // Comment this line to test.
			],
			[
				'value'             => 'product',
				'label'             => esc_html__( 'Products', 'Avada' ),
				'data'              => 'content',
				'plugin_dependency' => 'woocommerce',
			],
			[
				'value'             => 'event',
				'label'             => esc_html__( 'Events', 'Avada' ),
				'data'              => 'content',
				'plugin_dependency' => 'the-events-calendar',
			],
			[
				'value'             => 'forum',
				'label'             => esc_html__( 'Forum', 'Avada' ),
				'data'              => 'content',
				'plugin_dependency' => 'bbpress',
			],
			[
				'value'             => 'convertplug',
				'label'             => esc_html__( 'Convert Plus', 'Avada' ),
				'plugin_dependency' => 'convertplug', // Comment this line to test.
			],
			[
				'value' => 'attachment',
				'label' => esc_html__( 'Images', 'Avada' ),
				'data'  => 'content',
			],
			[
				'value' => 'sliders',
				'label' => esc_html__( 'Sliders', 'Avada' ),
			],
			[
				'value' => 'theme_options',
				'label' => esc_html__( 'Options', 'Avada' ),
			],
			[
				'value' => 'widgets',
				'label' => esc_html__( 'Widgets', 'Avada' ),
			],
		];
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

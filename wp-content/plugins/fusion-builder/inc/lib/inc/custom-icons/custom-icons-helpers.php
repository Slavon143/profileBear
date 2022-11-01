<?php
/**
 * Custom Icons helper functions.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Fusion-Library
 * @since      2.2
 */

/**
 * Get Icon Set CSS URL.
 *
 * @since 6.2
 * @param int $post_id Post ID.
 * @return string URL.
 */
function fusion_get_custom_icons_css_url( $post_id = 0 ) {

	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$icon_set = fusion_data()->post_meta( $post_id )->get( 'custom_icon_set' );

	return ! empty( $icon_set['icon_set_dir_name'] ) ? FUSION_ICONS_BASE_URL . $icon_set['icon_set_dir_name'] . '/style.css' : '';
}

/**
 * WIP.
 *
 * @since 6.2
 * @param array $args Optional WP_Query arguments.
 * @return array Icon array.
 */
function fusion_get_custom_icons_array( $args = [] ) {

	$upload_dir         = wp_upload_dir();
	$icons_base_dir_url = trailingslashit( $upload_dir['baseurl'] ) . 'fusion-icons/';

	$custom_icons = [];
	$default_args = [
		'post_type'      => 'fusion_icons',
		'post_status'    => 'publish',
		'posts_per_page' => -1, // phpcs:ignore WPThemeReview.CoreFunctionality.PostsPerPage.posts_per_page_posts_per_page
	];

	$args = wp_parse_args(
		$args,
		$default_args
	);

	$posts = get_posts( $args );
	foreach ( $posts as $post ) {
		$meta = fusion_data()->post_meta( $post->ID )->get( 'custom_icon_set' );

		if ( '' !== $meta ) {
			$custom_icons[ $post->post_name ]            = $meta;
			$custom_icons[ $post->post_name ]['name']    = get_the_title( $post->ID );
			$custom_icons[ $post->post_name ]['post_id'] = $post->ID;
			$custom_icons[ $post->post_name ]['css_url'] = fusion_get_custom_icons_css_url( $post->ID );
		}
	}

	return apply_filters( 'fusion_custom_icons', $custom_icons );
}

/**
 * Gets preload tags for custom icons.
 *
 * @since 7.2
 * @return string.
 */
function fusion_get_custom_icons_preload_tags() {
	$transient_name = 'fusion_custom_icons_preload_tags';
	$tags           = get_transient( $transient_name );

	if ( ! $tags ) {
		$icons         = fusion_get_custom_icons_array();
		$wp_filesystem = Fusion_Helper::init_filesystem();
		foreach ( $icons as $icon ) {

			if ( ! file_exists( FUSION_ICONS_BASE_DIR . $icon['icon_set_dir_name'] . '/style.css' ) ) {
				continue;
			}

			// Get the file contents.
			$file_contents = $wp_filesystem->get_contents( $icon['css_url'] );

			// If it failed, try wp_remote_get().
			if ( ! $file_contents ) {
				$response = wp_remote_get( $icon['css_url'] );
				if ( is_array( $response ) && ! is_wp_error( $response ) ) {
					$file_contents = wp_remote_retrieve_body( $response );
				}
			}

			if ( $file_contents ) {
				// Get font files.
				preg_match_all( '/fonts\/.[^\?]*\.ttf?([^\')]+)/', $file_contents, $matches );
				$matches = array_shift( $matches );

				foreach ( $matches as $match ) {
					$path = ! empty( $icon['icon_set_dir_name'] ) ? FUSION_ICONS_BASE_URL . $icon['icon_set_dir_name'] . '/' . $match : false;

					if ( $path ) {
						$tags .= '<link rel="preload" href="' . $path . '" as="font" type="font/ttf" crossorigin>';
					}
				}
			}
		}

		set_transient( $transient_name, $tags );
	}

	return $tags;
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

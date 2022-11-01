<?php
/**
 * Dummy post object class.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada-Builder
 * @since      2.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Dummy post class.
 */
class Fusion_Dummy_Post {

	/**
	 * The post type.
	 *
	 * @access protected
	 * @since 2.2
	 * @var array
	 */
	protected static $dummy_posts = [];

	/**
	 * Constructor.
	 *
	 * @access private
	 * @since 2.2
	 * @return void
	 */
	private function __construct() {
	}

	/**
	 * Creates dummy WP post
	 *
	 * @since 2.2
	 * @access public
	 *
	 * @param  array $postarr An array of elements that make up a post to update or insert.
	 * @return WP_Post object.
	 */
	public static function get_dummy_post( $postarr = [] ) {

		$id = md5( implode( $postarr ) );

		// Early return if we have already create post object.
		if ( isset( self::$dummy_posts[ $id ] ) ) {
			return self::$dummy_posts[ $id ];
		}

		$dummy_post = (object) wp_parse_args(
			$postarr,
			[
				'ID'             => -99,
				'post_author'    => get_current_user_id(),
				'post_date'      => current_time( 'mysql' ),
				'post_date_gmt'  => current_time( 'mysql', 1 ),
				'post_title'     => __( 'Sample Post Title', 'fusion-builder' ),
				'post_content'   =>
					'[fusion_builder_container type="flex"][fusion_builder_row][fusion_builder_column type="1_1"]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed mauris ex, rutrum vitae nunc sed, tincidunt euismod velit. Duis quis cursus felis, nec viverra tellus. Donec lobortis gravida ex vel imperdiet.

					Morbi id dui mattis ex accumsan mattis. Etiam augue tortor, lacinia vitae auctor nec, <strong>blandit congue tortor</strong>. Aenean aliquet elementum scelerisque. Suspendisse sodales, odio in cursus aliquet, velit turpis volutpat est, id egestas mauris nibh ac massa.
					<h4>Cras sed accumsan augue vitae et sapien</h4>
					<ul>
						 <li>enim in risus</li>
						 <li>velit a posuere</li>
						 <li>fringilla ligula</li>
					</ul>
					Etiam sollicitudin bibendum ligula nec imperdiet. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae. Maecenas ut sapien vitae justo feugiat tempus nec non nisl. Fusce sit amet accumsan nibh, sed commodo enim. Fusce elementum nisl a ante aliquam, in pellentesque massa fermentum.[/fusion_builder_column][/fusion_builder_row][/fusion_builder_container]',
				'post_excerpt'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed mauris ex, rutrum vitae nunc sed, tincidunt euismod velit.',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_name'      => 'fake-page-' . wp_rand( 1, 99999 ),
				'post_type'      => 'page',
				'filter'         => 'raw',
				'post_modified'  => current_time( 'mysql' ),
			]
		);

		self::$dummy_posts[ $id ] = new WP_Post( $dummy_post );

		return self::$dummy_posts[ $id ];
	}

}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

<?php
/**
 * Layout Conditions Class.
 *
 * @package fusion-builder
 * @since 3.6
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}


/**
 * Layout Conditions Class.
 *
 * @since 3.6
 */
class AWB_Layout_Conditions {

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 */
	private function __construct() {
	}

	/**
	 * Check if archive condition is true.
	 *
	 * @since 3.6
	 * @param array $condition Condition array to check.
	 * @return bool  $return Whether it passed or not.
	 * @access public
	 */
	public function check_archive_condition( $condition ) {
		$archive_type   = isset( $condition['archives'] ) ? $condition['archives'] : '';
		$exclude        = isset( $condition['mode'] ) && 'exclude' === $condition['mode'];
		$condition_type = isset( $condition['type'] ) ? $condition['type'] : '';
		$sub_condition  = isset( $condition[ $archive_type ] ) ? $condition[ $archive_type ] : '';

		if ( '' === $sub_condition ) {
			if ( 'all_archives' === $archive_type ) {
				return $exclude ? ! is_archive() : is_archive();
			}

			if ( 'author_archive' === $archive_type ) {
				return $exclude ? ! is_author() : is_author();
			}

			if ( 'date_archive' === $archive_type ) {
				return $exclude ? ! is_date() : is_date();
			}

			if ( 'search_results' === $archive_type ) {
				return $exclude ? ! is_search() : is_search();
			}

			if ( 'archives' === $condition_type && taxonomy_exists( $archive_type ) ) {
				if ( 'category' === $archive_type ) {
					return $exclude ? ! is_category() : is_category();
				}
				if ( 'post_tag' === $archive_type ) {
					return $exclude ? ! is_tag() : is_tag();
				}

				return $exclude ? ! is_tax( $archive_type ) : is_tax( $archive_type );
			}

			// Blog archive, treat separately.
			if ( 'archive_of_post' === $archive_type ) {
				$blog_conditional = ( is_home() && get_option( 'page_for_posts' ) === fusion_library()->get_page_id() ) || is_post_type_archive( 'post' );
				return $exclude ? ! $blog_conditional : $blog_conditional;
			}

			// Check for general archive pages.
			if ( false !== strpos( $archive_type, 'archive_of_' ) && is_archive() && null !== get_queried_object() ) {
				$taxonomy = str_replace( 'archive_of_', '', $archive_type );
				return $exclude ? ! is_post_type_archive( $taxonomy ) : is_post_type_archive( $taxonomy );
			}

			return $exclude;
		}

		// Check for specific author pages.
		if ( false !== strpos( $archive_type, 'author_archive_' ) ) {
			$author_ids = [];
			foreach ( array_keys( $sub_condition ) as $id ) {
				$author_ids[] = explode( '|', $id )[1];
			}
			$curauth = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );

			if ( ! $curauth ) {
				return $exclude;
			}
			// Intentionally not strict comparison.
			return $exclude ? ! in_array( $curauth->ID, $author_ids ) : in_array( $curauth->ID, $author_ids ); // phpcs:ignore WordPress.PHP.StrictInArray
		}

		// Check for general archive pages.
		if ( false === strpos( $archive_type, 'taxonomy_of_' ) && is_archive() && null !== get_queried_object() ) {
			$terms = [];
			foreach ( array_keys( $sub_condition ) as $id ) {
				$terms[] = explode( '|', $id )[1];
			}

			if ( ! isset( get_queried_object()->term_id ) ) {
				return $exclude;
			}

			// Intentionally not strict comparison.
			return $exclude ? ! in_array( get_queried_object()->term_id, $terms ) : in_array( get_queried_object()->term_id, $terms ); // phpcs:ignore WordPress.PHP.StrictInArray
		}

		// Check if we're checking for specific terms.
		if ( false !== strpos( $archive_type, 'taxonomy_of_' ) && ! is_archive() ) {
			$taxonomy = str_replace( 'taxonomy_of_', '', $archive_type );
			$terms    = [];
			foreach ( array_keys( $sub_condition ) as $id ) {
				$terms[] = explode( '|', $id )[1];
			}
			switch ( $taxonomy ) {
				case 'category':
					return $exclude ? ! in_category( $terms ) : in_category( $terms );
				case 'post_tag':
					return $exclude ? ! has_tag( $terms ) : has_tag( $terms );
				default:
					return $exclude ? ! has_term( $terms, $taxonomy ) : has_term( $terms, $taxonomy );
			}
		}

		return $exclude;
	}

	/**
	 * Group conditions.
	 *
	 * @since 3.6
	 * @param  array $data   Condition array to group.
	 * @return array  $return Grouped conditions.
	 * @access public
	 */
	public static function group_conditions( $data ) {
		$conditions = [];

		// Group child conditions into same id.
		foreach ( $data as $id => $condition ) {
			if ( ! isset( $condition['parent'] ) ) {
				$conditions[ $id ] = $condition;
				continue;
			}
			// Create unique id for the parent condition to avoid collitions between same conditions with different modes.
			$parent_id = $condition['parent'] . '-' . $condition['mode'] . '-' . $condition['type'];
			if ( ! isset( $conditions[ $parent_id ] ) ) {
				$conditions[ $parent_id ] = [
					'mode'             => $condition['mode'],
					'type'             => $condition['type'],
					$condition['type'] => $condition['parent'],
				];
			}
			$conditions[ $parent_id ][ $condition['parent'] ][ $id ] = $condition;
		}
		// Sort exclude conditions first and remove unique id.
		usort(
			$conditions,
			function( $a, $b ) {
				return strcmp( $a['mode'], $b['mode'] );
			}
		);

		return $conditions;
	}

	/**
	 * Check if singular condition is true.
	 *
	 * @since 3.6
	 * @param array $condition Condition array to check.
	 * @return bool  $return Whether it passed or not.
	 * @access public
	 */
	public function check_singular_condition( $condition ) {
		global $post;

		$singular_type = isset( $condition['singular'] ) ? $condition['singular'] : '';
		$exclude       = isset( $condition['mode'] ) && 'exclude' === $condition['mode'];
		$sub_condition = isset( $condition[ $singular_type ] ) ? $condition[ $singular_type ] : '';
		$post_type     = str_replace( 'singular_', '', $singular_type );

		if ( '' === $sub_condition ) {
			if ( 'front_page' === $singular_type ) {
				return $exclude ? ! is_front_page() : is_front_page();
			}
			if ( 'not_found' === $singular_type ) {
				return $exclude ? ! is_404() : is_404();
			}
			$is_single = is_singular( $post_type ) || ( get_post_type() === $post_type && is_admin() && isset( $_GET['action'] ) && 'edit' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
			return $exclude ? ! $is_single : $is_single;
		}
		// Specific post check.
		if ( false !== strpos( $singular_type, 'specific_' ) ) {
			$specific_posts = [];
			foreach ( array_keys( $sub_condition ) as $id ) {
				$specific_posts[] = explode( '|', $id )[1];
			}
			// Intentionally not strict comparison.
			return $exclude ? ! in_array( get_the_id(), $specific_posts, false ) : in_array( get_the_id(), $specific_posts, false ); // phpcs:ignore WordPress.PHP.StrictInArray
		}
		// Hierarchy check.
		if ( false !== strpos( $singular_type, 'children_of' ) ) {
			$ancestors   = get_post_ancestors( $post );
			$is_children = false;
			foreach ( array_keys( $sub_condition ) as $id ) {
				$parent = explode( '|', $id )[1];
				if ( in_array( $parent, $ancestors ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
					$is_children = true;
					break;
				}
			}
			return $exclude ? ! $is_children : $is_children;
		}
		return $exclude;
	}
}

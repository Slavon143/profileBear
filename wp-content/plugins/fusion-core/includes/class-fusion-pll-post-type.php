<?php
/**
 * Handles translating Post-Type slugs in PLL.
 *
 * @package Avada
 */

/**
 * The post-type translations object.
 */
class Fusion_PLL_Post_Type {

	/**
	 * The post-type object.
	 *
	 * @access public
	 * @since 6.1
	 * @var object
	 */
	public $post_type_object;

	/**
	 * The translated rewrite slugs.
	 *
	 * @access public
	 * @since 6.1
	 * @var array
	 */
	public $translated_slugs;

	/**
	 * Contructor.
	 *
	 * @access public
	 * @param stdClass $post_type_object The post-type object.
	 * @param array    $translated_slugs An array of translated slugs.
	 * @since 6.1
	 */
	public function __construct( $post_type_object, $translated_slugs ) {
		$this->post_type_object = $post_type_object;
		$this->translated_slugs = $this->sanitize_translated_slugs( $translated_slugs );

		// Replace "extra_rules_top", for archive.
		$this->replace_extra_rules_top();

		// Replace "permastruct", for single.
		$this->replace_permastruct();
	}

	/**
	 * Sanitizes translated slugs.
	 *
	 * @access private
	 * @since 6.1
	 * @param array $translated_slugs The translated slugs.
	 * @return array
	 */
	private function sanitize_translated_slugs( $translated_slugs ) {
		$post_type = $this->post_type_object->name;

		// Add defaults to translated_slugs.
		$defaults = [
			'has_archive' => false,
			'rewrite'     => true,
		];

		foreach ( $translated_slugs as $lang => $translated_slug ) {
			$args = wp_parse_args( $translated_slug, $defaults );
			$args = (object) $args;

			if ( false !== $args->rewrite && ( is_admin() || get_option( 'permalink_structure' ) ) ) {
				if ( ! is_array( $args->rewrite ) ) {
					$args->rewrite = [];
				}
				if ( empty( $args->rewrite['slug'] ) ) {
					$args->rewrite['slug'] = $post_type;
				}
				if ( ! isset( $args->rewrite['with_front'] ) ) {
					$args->rewrite['with_front'] = true;
				}
				if ( ! isset( $args->rewrite['pages'] ) ) {
					$args->rewrite['pages'] = true;
				}
				if ( ! isset( $args->rewrite['feeds'] ) || ! $args->has_archive ) {
					$args->rewrite['feeds'] = (bool) $args->has_archive;
				}
				if ( ! isset( $args->rewrite['ep_mask'] ) ) {
					if ( isset( $args->permalink_epmask ) ) {
						$args->rewrite['ep_mask'] = $args->permalink_epmask;
					} else {
						$args->rewrite['ep_mask'] = EP_PERMALINK;
					}
				}
			}
			$translated_slugs[ $lang ] = $args;
		}
		return $translated_slugs;
	}

	/**
	 * Replace "extra_rules_top", for archive.
	 *
	 * This code simulates the code used in WordPress function "register_post_type"
	 * and executes it for each language. After that, Polylang will consider these
	 * rules like "individual" post types (one per language) and will create the appropriate
	 * rules.
	 *
	 * @access private
	 * @since 6.1
	 * @see Extra rules from WordPress (wp-include/post.php, register_post_type()).
	 * @return void
	 */
	private function replace_extra_rules_top() {
		global $polylang, $wp_rewrite;

		$post_type = $this->post_type_object->name;

		// Remove the original extra rules.
		if ( $this->post_type_object->has_archive ) {
			$archive_slug = ( true === $this->post_type_object->has_archive ) ? $this->post_type_object->rewrite['slug'] : $this->post_type_object->has_archive;

			if ( isset( $this->post_type_object->rewrite['with_front'] ) && $this->post_type_object->rewrite['with_front'] ) {
				$archive_slug = substr( $wp_rewrite->front, 1 ) . $archive_slug;
			} else {
				$archive_slug = $wp_rewrite->root . $archive_slug;
			}

			unset( $wp_rewrite->extra_rules_top[ "{$archive_slug}/?$" ] );

			if ( isset( $this->post_type_object->rewrite['feeds'] ) && $this->post_type_object->rewrite['feeds'] && $wp_rewrite->feeds ) {
				$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
				unset( $wp_rewrite->extra_rules_top[ "{$archive_slug}/feed/$feeds/?$" ] );
				unset( $wp_rewrite->extra_rules_top[ "{$archive_slug}/$feeds/?$" ] );
			}

			if ( isset( $this->post_type_object->rewrite['pages'] ) && $this->post_type_object->rewrite['pages'] ) {
				unset( $wp_rewrite->extra_rules_top[ "{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$" ] );
			}
		}

		// Add the translated extra rules for each languages.
		foreach ( $this->translated_slugs as $lang => $translated_slug ) {
			if ( $translated_slug->has_archive ) {
				$archive_slug = ( true === $translated_slug->has_archive ) ? $translated_slug->rewrite['slug'] : $translated_slug->has_archive;
				if ( isset( $translated_slug->rewrite['with_front'] ) && $translated_slug->rewrite['with_front'] ) {
					$archive_slug = substr( $wp_rewrite->front, 1 ) . $archive_slug;
				} else {
					$archive_slug = $wp_rewrite->root . $archive_slug;
				}

				add_rewrite_rule( "{$archive_slug}/?$", "index.php?post_type=$post_type", 'top' );
				if ( isset( $translated_slug->rewrite['feeds'] ) && $translated_slug->rewrite['feeds'] && $wp_rewrite->feeds ) {
					$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
					add_rewrite_rule( "{$archive_slug}/feed/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
					add_rewrite_rule( "{$archive_slug}/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
				}
				if ( isset( $translated_slug->rewrite['pages'] ) && $translated_slug->rewrite['pages'] ) {
					add_rewrite_rule( "{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type=$post_type" . '&paged=$matches[1]', 'top' );
				}
			}
		}
	}

	/**
	 * Replace "permastruct", for single.
	 *
	 * This code simulates the code used in WordPress function "register_post_type"
	 * and executes it for each language.
	 *
	 * @access private
	 * @since 6.1
	 * @see Permstruct from WordPress (wp-include/post.php, register_post_type()).
	 * @return void
	 */
	private function replace_permastruct() {
		global $polylang, $wp_rewrite;

		$post_type = $this->post_type_object->name;

		// Remove the original permastructs.
		unset( $wp_rewrite->extra_permastructs[ $post_type ] );

		// Add the translated permastructs for each languages.
		foreach ( $this->translated_slugs as $lang => $translated_slug ) {
			$args = $translated_slug;

			if ( false !== $args->rewrite && ( is_admin() || get_option( 'permalink_structure' ) ) ) {
				$permastruct_args         = $args->rewrite;
				$permastruct_args['feed'] = $permastruct_args['feeds'];
				// Set the walk_dirs to false to avoid conflict with has_archive = false and the %language%
				// in the rewrite directive. Without it the archive page redirect to the frontpage if has_archive is false.
				$permastruct_args['walk_dirs'] = false;

				// If "Hide URL language information for default language" option is
				// set to true the rules has to be different for the default language.
				if ( $polylang->options['hide_default'] && pll_default_language() === $lang ) {
					add_permastruct( $post_type . '_' . $lang, "{$args->rewrite['slug']}/%$post_type%", $permastruct_args );
				} else {
					add_permastruct( $post_type . '_' . $lang, "%language%/{$args->rewrite['slug']}/%$post_type%", $permastruct_args );
				}
			}
		}
	}
}

<?php
/**
 * Heavily modified version of https://github.com/KLicheR/wp-polylang-translate-rewrite-slugs
 *
 * @since 6.1
 * @package Avada
 */

/**
 * The object handling post-type translations.
 *
 * @since 6.1
 */
class Fusion_PLL_Rewrite_Slugs {
	/**
	 * Array of custom post types to handle.
	 *
	 * @since 6.1
	 * @var array
	 */
	public $post_types = [];

	/**
	 * Contructor.
	 *
	 * @since 6.1
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ], 9 );
	}

	/**
	 * Add hooks on init.
	 *
	 * @access public
	 * @since 6.1
	 * @return void
	 */
	public function init() {

		// Early exit if PLL is not active.
		if ( ! class_exists( 'Fusion_Multilingual' ) || ! Fusion_Multilingual::is_pll() ) {
			return;
		}

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		// Post types to handle.
		$post_type_translated_slugs = apply_filters( 'fusion_pll_translated_post_type_rewrite_slugs', [] );
		foreach ( $post_type_translated_slugs as $post_type => $translated_slugs ) {
			$this->add_post_type( $post_type, $translated_slugs );
		}

		// Fix "get_permalink" for these post types.
		add_filter( 'post_type_link', [ $this, 'post_type_link_filter' ], 10, 4 );

		// Fix "get_post_type_archive_link" for these post types.
		add_filter( 'post_type_archive_link', [ $this, 'post_type_archive_link_filter' ], 25, 2 );

		// Fix "PLL_Frontend_Links->get_translation_url".
		add_filter( 'pll_translation_url', [ $this, 'pll_translation_url_filter' ], 10, 2 );

		// Stop Polylang from translating rewrite rules for these post types.
		add_filter( 'pll_rewrite_rules', [ $this, 'pll_rewrite_rules_filter' ] );
	}

	/**
	 * Create a "Fusion_PLL_Post_Type" and add it to the handled post type list.
	 *
	 * @access public
	 * @since 6.1
	 * @param string $post_type        The post-type.
	 * @param array  $translated_slugs An array of translated slugs.
	 * @return void
	 */
	public function add_post_type( $post_type, $translated_slugs ) {
		global $polylang;

		$languages        = $polylang->model->get_languages_list();
		$post_type_object = get_post_type_object( $post_type );

		if ( $post_type_object ) {

			foreach ( $languages as $language ) {

				// Add non specified slug translation to post type default.
				if ( ! array_key_exists( $language->slug, $translated_slugs ) ) {
					$translated_slugs[ $language->slug ] = [];
				}

				// Trim "/" of the slug.
				if ( isset( $translated_slugs[ $language->slug ]['rewrite']['slug'] ) ) {
					$translated_slugs[ $language->slug ]['rewrite']['slug'] = trim( $translated_slugs[ $language->slug ]['rewrite']['slug'], '/' );
				}
			}
			$this->post_types[ $post_type ] = new Fusion_PLL_Post_Type( $post_type_object, $translated_slugs );
		}
	}

	/**
	 * Fix "get_permalink" for this post type.
	 *
	 * @access public
	 * @since 6.1
	 * @param string  $post_link The post URL.
	 * @param WP_Post $post      The post object.
	 * @param bool    $leavename Whether to keep the post name or not.
	 * @param bool    $sample    Whether this is a sample permalink or not.
	 * @return string            Returns the post URL varbatim.
	 */
	public function post_type_link_filter( $post_link, $post, $leavename, $sample ) {
		// We always check for the post language. Otherwise, the current language.
		$post_language = pll_get_post_language( $post->ID );
		if ( $post_language ) {
			$lang = $post_language;
		} else {
			$lang = pll_default_language();
		}

		$draft_or_pending = get_post_status( $post ) && in_array( get_post_status( $post ), [ 'draft', 'pending', 'auto-draft', 'future' ], true );
		// Check if the post type is published and if is handle.
		if ( ( ! $draft_or_pending || $sample ) && isset( $this->post_types[ $post->post_type ] ) ) {
			// Build URL. Lang prefix is already handled.
			flush_rewrite_rules();
			$post_link = home_url( user_trailingslashit( '/' . $this->post_types[ $post->post_type ]->translated_slugs[ $lang ]->rewrite['slug'] . '/' . ( $leavename ? "%$post->post_type%" : get_page_uri( $post->ID ) ) ) );
		}

		return $post_link;
	}

	/**
	 * Filters the post type archive permalink.
	 *
	 * @access public
	 * @since 6.1
	 * @param string $link      The post type archive permalink.
	 * @param string $post_type Post type name.
	 * @return string           Returns the permalink.
	 */
	public function post_type_archive_link_filter( $link, $post_type ) {
		if ( is_admin() ) {
			global $polylang;
			$lang = isset( $polylang->pref_lang ) ? $polylang->pref_lang->slug : null;
		} else {
			$lang = pll_current_language();
		}

		// Check if the post type is handle.
		if ( isset( $this->post_types[ $post_type ] ) ) {
			return $this->get_post_type_archive_link( $post_type, $lang );
		}

		return $link;
	}

	/**
	 * Filters the post type archive permalink.
	 *
	 * @access public
	 * @since 6.1
	 * @param string $post_type The post type.
	 * @param string $lang      The language.
	 * @return string           Returns the URL.
	 */
	private function get_post_type_archive_link( $post_type, $lang ) {
		global $wp_rewrite, $polylang;

		// If the post type is handle, let the "$this->get_post_type_archive_link"
		// function handle this.
		if ( isset( $this->post_types[ $post_type ] ) ) {
			$translated_slugs = $this->post_types[ $post_type ]->translated_slugs;
			$translated_slug  = $translated_slugs[ $lang ];

			if ( ! $translated_slug->has_archive ) {
				return false;
			}

			if ( get_option( 'permalink_structure' ) && is_array( $translated_slug->rewrite ) ) {
				$struct = ( true === $translated_slug->has_archive ) ? $translated_slug->rewrite['slug'] : $translated_slug->has_archive;

				if (
					// If the "URL modifications" is set to "The language is set from the directory name in pretty permalinks".
					$polylang->options['force_lang'] &&
					// If NOT ("Hide URL language information for default language" option is
					// set to true and the $lang is the default language).
					! ( $polylang->options['hide_default'] && pll_default_language() === $lang )
				) {
					$struct = $lang . '/' . $struct;
				}

				$struct = ( $translated_slug->rewrite['with_front'] ) ? $wp_rewrite->front . $struct : $wp_rewrite->root . $struct;
				return home_url( user_trailingslashit( $struct, 'post_type_archive' ) );
			}
			return home_url( '?post_type=' . $post_type );
		}

		return $link;
	}

	/**
	 * Filter the translation url of the current page before Polylang caches it
	 *
	 * @access public
	 * @since 6.1
	 * @param null|string $url  The translation url, null if none was found.
	 * @param string      $lang The language code of the translation.
	 * @return null|string      Returns the translation URL.
	 */
	public function pll_translation_url_filter( $url, $lang ) {
		global $wp_query, $polylang;

		if ( is_archive() ) {
			$post_type = $wp_query->query_vars['post_type'];

			if ( is_array( $post_type ) ) {
				$post_type = $post_type[0];
			}

			// If the post type is handle, let the "$this->get_post_type_archive_link" method handle this.
			if ( isset( $this->post_types[ $post_type ] ) ) {
				return $this->get_post_type_archive_link( $post_type, $lang );
			}
		}

		return $url;
	}

	/**
	 * Filter the list of rewrite rules filters to be used by Polylang.
	 *
	 * @access public
	 * @since 6.1
	 * @param array $types the list of filters (without '_rewrite_rules' at the end).
	 * @return array
	 */
	public function pll_rewrite_rules_filter( $types ) {
		// We don't want Polylang to take care of these rewrite rules groups.
		$keys = array_keys( $this->post_types );
		foreach ( $keys as $post_type ) {
			$rule_key = array_search( $post_type, $types, true );
			if ( $rule_key ) {
				unset( $types[ $rule_key ] );
			}
		}
		return $types;
	}
}

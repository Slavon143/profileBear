<?php
/**
 * Upgrades Handler.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handle migrations for Avada 5.9.
 *
 * @since 5.9
 */
class Avada_Upgrade_590 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 5.9
	 * @var string
	 */
	protected $version = '5.9.0';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 5.9
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 5.9
	 * @return void
	 */
	protected function migration_process() {
		$available_languages       = Fusion_Multilingual::get_available_languages();
		self::$available_languages = ( ! empty( $available_languages ) ) ? $available_languages : [ '' ];

		$this->migrate_options();
	}

	/**
	 * Migrate options.
	 *
	 * @since 5.9
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->migrate_recaptcha_options( $options );
		$options = $this->migrate_pagination_options( $options );
		$options = $this->migrate_totop_options( $options );
		$options = $this->migrate_search_options( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->migrate_recaptcha_options( $options );
			$options = $this->migrate_pagination_options( $options );
			$options = $this->migrate_totop_options( $options );
			$options = $this->migrate_search_options( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Migrate the reCAPTCHA Global Options.
	 *
	 * @access private
	 * @since 5.9
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_recaptcha_options( $options ) {
		if ( ! isset( $options['recaptcha_version'] ) ) {
			$options['recaptcha_version'] = 'v2';
		}

		return $options;
	}

	/**
	 * Migrate the pagination Global Options.
	 *
	 * @access private
	 * @since 5.9
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_pagination_options( $options ) {
		$options['pagination_sizing'] = 'padding';
		return $options;
	}

	/**
	 * Migrate the ToTop Global Options.
	 *
	 * @access private
	 * @since 5.9
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_totop_options( $options ) {
		if ( is_rtl() ) {
			$options['totop_position'] = 'left';
		}

		return $options;
	}

	/**
	 * Migrate the search page Global Options.
	 *
	 * @access private
	 * @since 5.9
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_search_options( $options ) {

		if ( isset( $options['search_content'] ) ) {
			$search_content = [];

			if ( 'Posts and Pages' === $options['search_content'] ) {
				$search_content = [ 'post', 'page', 'avada_portfolio', 'avada_faq', 'product', 'tribe_events' ];
			} elseif ( 'all_post_types_no_pages' === $options['search_content'] ) {
				$search_content = [ 'post', 'avada_portfolio', 'avada_faq', 'product', 'tribe_events' ];
			} elseif ( 'Only Pages' === $options['search_content'] ) {
				$search_content = [ 'page' ];
			} elseif ( 'Only Posts' === $options['search_content'] ) {
				$search_content = [ 'post' ];
			} elseif ( 'portfolio_items' === $options['search_content'] ) {
				$search_content = [ 'avada_portfolio' ];
			} elseif ( 'woocommerce_products' === $options['search_content'] ) {
				$search_content = [ 'product' ];
			} elseif ( 'tribe_events' === $options['search_content'] ) {
				$search_content = [ 'tribe_events' ];
			} else {
				$search_content = [ 'post', 'page', 'avada_portfolio', 'avada_faq', 'product', 'tribe_events' ];
			}

			$options['search_content'] = $search_content;
		}

		if ( isset( $options['search_layout'] ) ) {
			$options['search_pagination_type'] = $options['search_layout'];
		}

		if ( isset( $options['blog_pagination_type'] ) ) {
			$options['search_pagination_type'] = $options['blog_pagination_type'];
		}

		if ( isset( $options['blog_archive_grid_columns'] ) ) {
			$options['search_grid_columns'] = $options['blog_archive_grid_columns'];
		}

		if ( isset( $options['blog_archive_grid_column_spacing'] ) ) {
			$options['searche_grid_column_spacing'] = $options['blog_archive_grid_column_spacing'];
		}

		if ( isset( $options['excerpt_length_blog'] ) ) {
			$options['search_excerpt_length'] = $options['excerpt_length_blog'];
		}

		if ( isset( $options['search_excerpt'] ) && isset( $options['content_length'] ) ) {
			if ( $options['search_excerpt'] ) {
				if ( 'hide' === $options['content_length'] ) {
					$options['search_content_length'] = 'no_text';
				} else {
					$options['search_content_length'] = strtolower( str_replace( ' ', '_', $options['content_length'] ) );
				}
			} else {
				$options['search_content_length'] = 'no_text';
			}
		}

		if ( isset( $options['excerpt_length_blog'] ) ) {
			$options['search_excerpt_length'] = $options['excerpt_length_blog'];
		}

		if ( isset( $options['strip_html_excerpt'] ) ) {
			$options['search_strip_html_excerpt'] = $options['strip_html_excerpt'];
		}

		$search_meta = [];
		if ( isset( $options['post_meta'] ) && ! $options['post_meta'] ) {
			$options['search_meta'] = $search_meta;
		} elseif ( isset( $options['post_meta_author'] ) && isset( $options['post_meta_date'] ) && isset( $options['post_meta_cats'] ) && isset( $options['post_meta_comments'] ) && isset( $options['post_meta_read'] ) && isset( $options['post_meta_tags'] ) ) {
			if ( $options['post_meta_author'] ) {
				$search_meta[] = 'author';
			}
			if ( $options['post_meta_date'] ) {
				$search_meta[] = 'date';
			}
			if ( $options['post_meta_cats'] ) {
				$search_meta[] = 'categories';
			}
			if ( $options['post_meta_comments'] ) {
				$search_meta[] = 'comments';
			}
			if ( $options['post_meta_read'] ) {
				$search_meta[] = 'read_more';
			}
			if ( $options['post_meta_tags'] ) {
				$search_meta[] = 'tags';
			}

			$options['search_meta'] = $search_meta;
		}

		return $options;
	}
}

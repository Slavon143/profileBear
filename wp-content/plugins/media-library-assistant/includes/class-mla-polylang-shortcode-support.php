<?php
/**
 * Media Library Assistant Polylang Shortcode Support classes
 *
 * This file is conditionally loaded in MLAShortcodes::initialize after a check for Polylang presence.
 *
 * @package Media Library Assistant
 * @since 2.40
 */

/**
 * Class MLA (Media Library Assistant) Polylang Shortcdxodes provides front-end support for the
 * Polylang Multilingual plugin
 *
 * @package Media Library Assistant
 * @since 2.40
 */
class MLA_Polylang_Shortcodes {
	/**
	 * Polylang version conditional to avoid deprecated functions in v1.8+
	 *
	 * @since 2.78
	 *
	 * @var	boolean
	 */
	private static $polylang_2dot4_plus = false;

	/**
	 * Initialization function, similar to __construct()
	 *
	 * @since 2.40
	 *
	 * @return	void
	 */
	public static function initialize() {
		self::$polylang_2dot4_plus = version_compare( POLYLANG_VERSION, '2.3.99', '>' );

		// Defined in /media-library-assistant/includes/class-mla-shortcode-support.php
		add_filter( 'mla_get_terms_query_arguments', 'MLA_Polylang_Shortcodes::mla_get_terms_query_arguments', 10, 1 );
		add_filter( 'mla_get_terms_clauses', 'MLA_Polylang_Shortcodes::mla_get_terms_clauses', 10, 1 );
	}

	/**
	 * MLA Tag Cloud Query Arguments
	 *
	 * Saves [mla_tag_cloud] query parameters for use in MLA_Polylang_Shortcodes::mla_get_terms_clauses.
	 *
	 * @since 2.40
	 * @uses MLA_Polylang_Shortcodes::$all_query_parameters
	 *
	 * @param	array	shortcode arguments merged with attachment selection defaults
	 */
	public static function mla_get_terms_query_arguments( $all_query_parameters ) {
		MLA_Polylang_Shortcodes::$all_query_parameters = $all_query_parameters;

		return $all_query_parameters;
	} // mla_get_terms_query_arguments

	/**
	 * Save the query arguments
	 *
	 * @since 2.40
	 *
	 * @var	array
	 */
	private static $all_query_parameters = array();

	/**
	 * MLA Tag Cloud Query Clauses
	 *
	 * Adds language-specific clauses to filter the cloud terms.
	 *
	 * @since 2.40
	 * @uses MLA_Polylang_Shortcodes::$all_query_parameters
	 *
	 * @param	array	SQL clauses ( 'fields', 'join', 'where', 'order', 'orderby', 'limits' )
	 */
	public static function mla_get_terms_clauses( $clauses ) {
		global $polylang;

		if ( self::$polylang_2dot4_plus ) {
			$clauses = $polylang->terms->terms_clauses($clauses, MLA_Polylang_Shortcodes::$all_query_parameters['taxonomy'], MLA_Polylang_Shortcodes::$all_query_parameters );
		} else {
			// Before v2.4, the Polylang terms_clauses method is in one of two places
			if ( is_admin() ) {
				$clauses = $polylang->filters_term->terms_clauses($clauses, MLA_Polylang_Shortcodes::$all_query_parameters['taxonomy'], MLA_Polylang_Shortcodes::$all_query_parameters );
			} else {
				$clauses = $polylang->filters->terms_clauses($clauses, MLA_Polylang_Shortcodes::$all_query_parameters['taxonomy'], MLA_Polylang_Shortcodes::$all_query_parameters );
			}
		}

		return $clauses;
	} // mla_get_terms_clauses
} // Class MLA_Polylang_Shortcodes
?>
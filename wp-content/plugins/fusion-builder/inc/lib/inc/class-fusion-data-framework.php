<?php
/**
 * Setting framework.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Fusion-Library
 * @since      2.2.0
 */

/**
 * Get & set setting values.
 */
class Fusion_Data_Framework {

	/**
	 * An instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 2.2.0
	 * @var Fusion_Data_Framework
	 */
	private static $instance;

	/**
	 * An array of all our data objects.
	 *
	 * @static
	 * @access protected
	 * @since 2.2.0
	 * @var array
	 */
	protected static $data;

	/**
	 * Get an instance of this object
	 *
	 * @access public
	 * @since 2.2.0
	 * @return Fusion_Data_Framework
	 */
	public static function init() {
		if ( ! self::$instance ) {
			self::$instance = new Fusion_Data_Framework();
		}
		return self::$instance;
	}

	/**
	 * Get a post-meta object.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param int $id The post-ID.
	 * @return Fusion_Data_PostMeta
	 */
	public function post_meta( $id = 0 ) {
		if ( ! $id ) {
			$id = fusion_library()->get_page_id();
		}

		if ( ! isset( self::$data['post_meta'] ) ) {
			self::$data['post_meta'] = [];
		}
		if ( ! isset( self::$data['post_meta'][ $id ] ) ) {
			self::$data['post_meta'][ $id ] = new Fusion_Data_PostMeta( $id );
		}
		return self::$data['post_meta'][ $id ];
	}

	/**
	 * Get a term-meta object.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param int $id The term-ID.
	 * @return Fusion_Data_TermMeta
	 */
	public function term_meta( $id = 0 ) {
		if ( ! isset( self::$data['term_meta'] ) ) {
			self::$data['term_meta'] = [];
		}
		if ( ! isset( self::$data['term_meta'][ $id ] ) ) {
			self::$data['term_meta'][ $id ] = new Fusion_Data_TermMeta( $id );
		}
		return self::$data['term_meta'][ $id ];
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

<?php
/**
 * Post-Meta getter/setter.
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
class Fusion_Data_PostMeta {

	/**
	 * The root post-meta key.
	 *
	 * @since 2.2.0
	 */
	const ROOT = '_fusion';

	/**
	 * All the post-meta for the current post.
	 *
	 * @access protected
	 * @since 2.2.0
	 * @var array
	 */
	protected $data;

	/**
	 * The post-ID.
	 *
	 * @access protected
	 * @since 2.2.0
	 * @var int
	 */
	protected $post_id;

	/**
	 * An array of post-meta that should be retrieved from the content template override.
	 *
	 * @static
	 * @access protected
	 * @since 2.2.0
	 * @var array
	 */
	protected static $template_options = [
		'layout',
		'main_padding',
		'content_bg_color',
		'content_bg_image',
		'content_bg_full',
		'content_bg_repeat',
		'template_sidebar',
		'template_sidebar_2',
		'template_sidebar_position',
		'responsive_sidebar_order',
		'sidebar_sticky',
		'sidebar_bg_color',
		'hundredp_padding',
		'bg_color',
		'bg_image',
		'bg_full',
		'bg_repeat',
	];

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param int $id The arguments.
	 * @return void
	 */
	public function __construct( $id ) {

		$this->post_id = $id;
	}

	/**
	 * Get post-meta.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param string $option The option we want to get.
	 * @param array  $args   Additional arguments.
	 */
	public function get( $option, $args = [] ) {

		// Get all meta.
		$meta = $this->get_all_meta();

		// Migrate incorrectly named portfolio_featured_image_width setting.
		if ( 'portfolio_featured_image_width' === $option && ! isset( $meta[ $option ] ) && isset( $meta['width'] ) ) {
			$meta['portfolio_featured_image_width'] = $meta['width'];
			unset( $meta['width'] );
			update_post_meta( $this->post_id, self::ROOT, $meta );
		}

		// If we find a value return it.
		if ( isset( $meta[ $option ] ) ) {
			return $meta[ $option ];
		}

		// Check if we want item from inside an array.
		if ( false !== strpos( $option, '[' ) ) {
			$parts = explode( '[', $option );
			if ( ! isset( $meta[ $parts[0] ] ) ) {
				return '';
			}
			$value = $meta[ $parts[0] ];
			unset( $parts[0] );
			foreach ( $parts as $part ) {
				$part = str_replace( ']', '', $part );
				if ( ! is_array( $value ) || ! isset( $value[ $part ] ) ) {
					return '';
				}
				$value = $value[ $part ];
			}
			return $value;
		}

		// Fallback: empty string.
		return '';
	}

	/**
	 * Set post-meta.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param string $option The option we want to get.
	 * @param mixed  $value  The value we want to set.
	 * @param array  $args   Additional arguments.
	 * @return int|bool      See https://developer.wordpress.org/reference/functions/update_post_meta/
	 */
	public function set( $option, $value, $args = [] ) {

		// Get all existing post-meta.
		// We won't be using this but getting the value makes sure it is set in the object.
		$meta = $this->get_all_meta();

		// Set the value in our array.
		if ( false === strpos( $option, '[' ) ) { // This is a normal option.
			$this->data[ $option ] = $value;

			// If the value is default or empty, delete the item from our array.
			if ( '' === $value || 'default' === $value ) {
				unset( $this->data[ $option ] );
			}
		} else { // Option is part of an array.
			$option_root = explode( '[', $option )[0];
			if ( ! isset( $this->data[ $option_root ] ) ) {
				$this->data[ $option_root ] = [];
			}
			$option_child                                = str_replace( ']', '', explode( '[', $option )[1] );
			$this->data[ $option_root ][ $option_child ] = $value;

			// If the value is default or empty, delete the item from our array.
			if ( '' === $value || 'default' === $value ) {
				unset( $this->data[ $option_root ][ $option_child ] );
				if ( empty( $this->data[ $option_root ] ) ) {
					unset( $this->data[ $option_root ] );
				}
			}
		}

		// Update post-meta with new value.
		return update_post_meta( $this->post_id, self::ROOT, $this->data );
	}

	/**
	 * Delete data.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param string $option The option we want to delete.
	 */
	public function delete( $option ) {

		// Get all existing post-meta.
		$meta = $this->get_all_meta();
		if ( isset( $meta[ $option ] ) ) {
			unset( $meta[ $option ] );
		}

		// Update post-meta with new value.
		update_post_meta( $this->post_id, self::ROOT, $meta );
	}

	/**
	 * Gets all post-meta.
	 *
	 * @access public
	 * @since 2.2.0
	 * @return array
	 */
	public function get_all_meta() {
		global $pagenow;

		// No valid post ID, return early.
		if ( ! $this->post_id || ! is_numeric( $this->post_id ) ) {
			return [];
		}

		$post_type = get_post_type( $this->post_id );

		// If on new post screen, exit early.
		if ( is_admin() && 'post-new.php' === $pagenow && 'fusion_icons' !== $post_type ) {
			return [];
		}

		// Check if we are non-allowable post type and return early if so.
		$disallowed_post_types = apply_filters( 'avada_hide_page_options', [ 'attachment', 'shop_order' ] );
		if ( in_array( $post_type, $disallowed_post_types, true ) ) {
			return [];
		}

		// If we don't have the option in our cache, get it from the post.
		if ( is_null( $this->data ) ) {

			// Get the post-meta.
			$this->data = get_post_meta( $this->post_id, self::ROOT, true );

			if ( is_string( $this->data ) ) {
				$this->data = maybe_unserialize( $this->data );
			}

			// Check if we have a template override.
			$template_override = false;
			if ( class_exists( 'Fusion_Template_Builder' ) ) {
				$template_override = Fusion_Template_Builder::get_instance()->get_override( 'content' );
			}

			// Use the template post-meta, and fill-in the gaps with the post's post-meta.
			if ( $template_override && is_object( $template_override ) && isset( $template_override->ID ) ) {

				// Remove post-meta that should be overrides from the template.
				foreach ( self::$template_options as $option ) {
					if ( isset( $this->data[ $option ] ) ) {
						unset( $this->data[ $option ] );
					}
				}

				// Add the template post-meta.
				$this->data = wp_parse_args(
					$this->data,
					get_post_meta( $template_override->ID, self::ROOT, true )
				);
			}
		}

		// If post-meta doesn't exist, migrate it.
		if ( '' === $this->data ) {
			$this->data = [];
			if ( class_exists( 'Fusion_Deprecate_Pyre_PO' ) && is_int( $this->post_id ) ) {
				new Fusion_Deprecate_Pyre_PO( $this->post_id );
				$this->data = get_post_meta( $this->post_id, self::ROOT, true );
			}
		}

		return (array) apply_filters( 'fusion_get_all_meta', (array) $this->data, $this->post_id );
	}

	/**
	 * Resets the $data to force-get them anew.
	 *
	 * @access public
	 * @since 6.2.0
	 * @return void
	 */
	public function reset_data() {
		$this->data = null;
	}

	/**
	 * Get the $template_options static var.
	 *
	 * @static
	 * @access public
	 * @since 2.2.0
	 * @return array
	 */
	public static function get_template_options() {
		return self::$template_options;
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

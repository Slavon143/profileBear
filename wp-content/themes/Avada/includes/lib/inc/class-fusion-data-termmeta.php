<?php
/**
 * Term-Meta getter/setter.
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
class Fusion_Data_TermMeta {

	/**
	 * The root post-meta key.
	 *
	 * @since 2.2.0
	 */
	const ROOT = '_fusion';

	/**
	 * All the term-meta for the current term.
	 *
	 * @access protected
	 * @since 2.2.0
	 * @var array
	 */
	protected $data;

	/**
	 * The term-ID.
	 *
	 * @access protected
	 * @since 2.2.0
	 * @var int
	 */
	protected $term_id;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param int $id The arguments.
	 * @return void
	 */
	public function __construct( $id = 0 ) {
		$this->term_id = absint( $id );
		if ( ! $this->term_id ) {
			$queried_object = get_queried_object();
			if ( is_object( $queried_object && isset( $queried_object->term_id ) ) ) {
				$this->term_id = $queried_object->term_id;
			}
		}
	}

	/**
	 * Get term-meta.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param string $option The option we want to get.
	 * @param array  $args   Additional arguments.
	 */
	public function get( $option, $args = [] ) {

		// Get all meta.
		$meta = $this->get_all_meta();

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

		// Fallback: null.
		return null;
	}

	/**
	 * Set term-meta.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param string $option The option we want to get.
	 * @param mixed  $value  The value we want to set.
	 * @param array  $args   Additional arguments.
	 * @return int|WP_Error|bool See https://developer.wordpress.org/reference/functions/update_term_meta/
	 */
	public function set( $option, $value, $args = [] ) {

		// Get all existing term-meta.
		// We won't be using this but getting the value makes sure it is set in the object.
		$meta = $this->get_all_meta();

		// Set the value in our array.
		if ( false === strpos( $option, '[' ) ) { // This is a normal option.
			$this->data[ $option ] = $value;

			// If the value is default or empty, delete the item from our array.
			if ( '' === $value || 'default' === $value || '""' === $value || "''" === $value ) {
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

		// Update term-meta with new value.
		return update_term_meta( $this->term_id, self::ROOT, $this->data );
	}

	/**
	 * Delete data.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param string $option The option we want to delete.
	 * @return int|WP_Error|bool See https://developer.wordpress.org/reference/functions/update_term_meta/
	 */
	public function delete( $option ) {

		// Get all existing term-meta.
		$meta = $this->get_all_meta();
		if ( isset( $meta[ $option ] ) ) {
			unset( $meta[ $option ] );
		}

		// Update term-meta with new value.
		return update_term_meta( $this->term_id, self::ROOT, $meta );
	}

	/**
	 * Gets all term-meta.
	 *
	 * @access public
	 * @since 2.2.0
	 * @return array
	 */
	public function get_all_meta() {

		// If we don't have the option in our cache, get it from the term.
		if ( is_null( $this->data ) ) {

			// Get the term-meta.
			$this->data = get_term_meta( $this->term_id, self::ROOT, true );

			// Make sure template overrides are not checked while retrieving meta values for Avada Slider.
			if ( false === get_term_by( 'id', $this->term_id, 'slide-page' ) ) {

				// Check if we have a template override.
				$template_override = false;
				$ptb_override      = false;
				if ( class_exists( 'Fusion_Template_Builder' ) ) {
					$template_override = Fusion_Template_Builder::get_instance()->get_override( 'content' );
					$ptb_override      = Fusion_Template_Builder::get_instance()->get_override( 'page_title_bar' );
				}

				// Use the PTB's template post-meta, and fill-in the gaps with the term's meta.
				if ( $ptb_override && is_object( $ptb_override ) && isset( $ptb_override->ID ) ) {

					// Remove post-meta that should be overrides from the PTB template.
					foreach ( [ 'page_title_bar', 'page_title_bg', 'page_title_height', 'page_title_mobile_height' ] as $option ) {
						if ( isset( $this->data[ $option ] ) ) {
							unset( $this->data[ $option ] );
						}
					}

					// Add the template post-meta.
					$this->data = wp_parse_args(
						$this->data,
						get_post_meta( $ptb_override->ID, self::ROOT, true )
					);
				}

				// Use the template post-meta, and fill-in the gaps with the term's meta.
				if ( $template_override && is_object( $template_override ) && isset( $template_override->ID ) ) {

					// Remove post-meta that should be overrides from the content template.
					if ( isset( $this->data['main_padding'] ) ) {
						unset( $this->data['main_padding'] );
					}

					// Add the template post-meta.
					$this->data = wp_parse_args(
						$this->data,
						get_post_meta( $template_override->ID, self::ROOT, true )
					);
				}
			}
		}

		// If term-meta doesn't exist, migrate it.
		if ( '' === $this->data ) {
			$this->data = $this->migrate_meta();
		}

		return (array) $this->data;
	}

	/**
	 * Set raw meta.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param mixed  $value The value we want to set.
	 * @param string $key   The post-meta key. Leave empty for default.
	 * @return int|WP_Error|bool See https://developer.wordpress.org/reference/functions/update_term_meta/
	 */
	public function set_raw( $value, $key = '' ) {
		$key = ( $key ) ? $key : self::ROOT;
		return update_term_meta( $this->term_id, $key, $value );
	}

	/**
	 * Migrate old term-meta.
	 *
	 * @access protected
	 * @since 2.2.0
	 * @return array Returns the old post-meta, or an empty array if nothing existed.
	 */
	protected function migrate_meta() {
		$meta = get_term_meta( $this->term_id, 'fusion_taxonomy_options', true );
		if ( ! $meta ) {
			$meta = get_term_meta( $this->term_id, 'fusion_slider_options', true );
		}
		if ( ! $meta ) {
			update_term_meta( $this->term_id, self::ROOT, [] );
			return [];
		}

		foreach ( $meta as $meta_k => $meta_v ) {

			// Remove empty or default values from the array of meta to migrate.
			if ( '' === $meta_v || 'default' === $meta_v || null === $meta_v ) {
				unset( $meta[ $meta_k ] );
				continue;
			}

			switch ( $meta_k ) {
				case 'main_padding_top':
				case 'main_padding_bottom':
					$meta['main_padding'] = isset( $meta['main_padding'] ) ? (array) $meta['main_padding'] : [];
					$meta['main_padding'][ str_replace( 'main_padding_', '', $meta_k ) ] = $meta_v;
					unset( $meta[ $meta_k ] );
					break;

				case 'page_title_bg':
				case 'page_title_bg_retina':
					$meta[ $meta_k ] = [
						'url' => $meta_v,
					];
					break;

				case 'blog_page_title_bar':
					$meta['page_title_bar'] = $meta_v;
					unset( $meta['blog_page_title_bar'] );
					break;

				case ( 0 === strpos( $meta_k, 'fusion_tax_' ) ):
					$meta[ str_replace( 'fusion_tax_', '', $meta_k ) ] = $meta_v;
					unset( $meta[ $meta_k ] );
					break;

				case 'header_bg_color':
				case 'mobile_header_bg_color':
					$meta[ str_replace( 'header_bg_color', 'archive_header_bg_color', $meta_k ) ] = $meta_v;
					unset( $meta[ $meta_k ] );
					break;
			}
		}

		update_term_meta( $this->term_id, self::ROOT, $meta );
		return $meta;
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
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

<?php
/**
 * Fusion Dynamic Data class.
 *
 * @package Avada-Builder
 * @since 2.1
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Fusion Dynamic Data class.
 *
 * @since 2.1
 */
class Fusion_Dynamic_Data {

	/**
	 * Array of dynamic param definitions.
	 *
	 * @access private
	 * @since 2.1
	 * @var array
	 */
	private $params = [];

	/**
	 * Array of dynamic param values and arguments.
	 *
	 * @access private
	 * @since 2.1
	 * @var array
	 */
	private $values = [];

	/**
	 * Array of text fields.
	 *
	 * @access private
	 * @since 2.1
	 * @var array
	 */
	private $text_fields = [ 'textfield', 'textarea', 'tinymce', 'raw_textarea', 'raw_text' ];

	/**
	 * Array of image fields.
	 *
	 * @access private
	 * @since 2.1
	 * @var array
	 */
	private $image_fields = [ 'upload' ];

	/**
	 * Array of link fields.
	 *
	 * @access private
	 * @since 2.1
	 * @var array
	 */
	private $link_fields = [ 'link_selector' ];

	/**
	 * Options which show on both text and link.
	 *
	 * @access private
	 * @since 2.1
	 * @var array
	 */
	private $link_and_text_fields = [ 'link_selector', 'textfield', 'textarea', 'tinymce', 'raw_textarea', 'raw_text', 'date_time_picker' ];

	/**
	 * Date time picker.
	 *
	 * @access private
	 * @since 3.3
	 * @var array
	 */
	private $date_time_picker = [ 'date_time_picker' ];

	/**
	 * Array of image/video or any type of file fields.
	 *
	 * @access private
	 * @since 2.1
	 * @var array
	 */
	private $file_fields = [ 'uploadfile', 'upload' ];

	/**
	 * Array of image/video or any type of file fields.
	 *
	 * @access private
	 * @since 2.1
	 * @var array
	 */
	private $number_fields = [ 'range' ];

	/**
	 * Class constructor.
	 *
	 * @since 2.1
	 * @access public
	 */
	public function __construct() {
		if ( ! apply_filters( 'fusion_load_dynamic_data', true ) ) {
			return;
		}
		add_filter( 'fusion_pre_shortcode_atts', [ $this, 'filter_dynamic_args' ], 10, 4 );
		add_filter( 'fusion_shortcode_content', [ $this, 'filter_dynamic_content' ], 10, 4 );
		add_filter( 'fusion_app_preview_data', [ $this, 'filter_preview_data' ], 10, 3 );
		add_filter( 'fusion_dynamic_override', [ $this, 'extra_output_filter' ], 10, 5 );
		add_action( 'fusion_builder_admin_scripts_hook', [ $this, 'backend_builder_data' ], 10 );
		$this->include_and_init_callbacks();
	}

	/**
	 * Require callbacks class.
	 *
	 * @since 2.1
	 * @access public
	 */
	public function include_and_init_callbacks() {
		require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-dynamic-data-callbacks.php';
		new Fusion_Dynamic_Data_Callbacks();
	}

	/**
	 * Filter the shortcode content.
	 *
	 * @since 2.1
	 * @access public
	 * @param string $content Shortcode element content.
	 * @param string $shortcode Shortcode name.
	 * @param array  $args Shortcode parameters.
	 * @return array
	 */
	public function filter_dynamic_content( $content, $shortcode, $args ) {
		if ( ! isset( $args['dynamic_params'] ) ) {
			return $content;
		}

		$dynamic_args = $this->convert( $args['dynamic_params'] );
		$dynamic_arg  = $dynamic_args && isset( $dynamic_args['element_content'] ) ? $dynamic_args['element_content'] : false;

		if ( ( 'fusion_gallery' === $shortcode || 'fusion_images' === $shortcode ) && isset( $dynamic_args['multiple_upload'] ) ) {
			return $this->dynamic_gallery_content( $dynamic_args['multiple_upload'], $content, $shortcode );
		}

		if ( ! $dynamic_arg ) {
			return $content;
		}

		$value = $this->get_value( $dynamic_arg );

		if ( false === $value ) {
			return $content;
		}

		return $value;
	}

	/**
	 * Creates gallery child shortcodes for each dynamic image found.
	 *
	 * @since 3.2
	 * @access public
	 * @param array  $dynamic_arg Dynamic gallery reference.
	 * @param string $content Shortcode element content.
	 * @param string $shortcode Shortcode name.
	 * @return string
	 */
	public function dynamic_gallery_content( $dynamic_arg, $content = '', $shortcode = '' ) {
		$value = $this->get_value( $dynamic_arg );

		if ( empty( $value ) || ! is_array( $value ) ) {
			return $content;
		}

		$shortcode_map = [
			'fusion_gallery' => 'fusion_gallery_image',
			'fusion_images'  => 'fusion_image',
		];

		// Get single shortcode name for the child element.
		$single_shortcode = $shortcode_map[ $shortcode ];

		// Remove empty entries if they exist.
		if ( 'fusion_gallery' === $shortcode ) {
			$content = str_replace( '[fusion_gallery_image link="" linktarget="_self" alt="" /]', '', $content );
		} elseif ( 'fusion_images' === $shortcode ) {
			$content = str_replace( '[fusion_image link="" linktarget="_self" alt="" image_id="" /]', '', $content );
		}

		foreach ( $value as $image ) {
			$image_id  = '';
			$image_url = '';

			// Check for data type of gallery.
			if ( is_array( $image ) ) {
				$image_id  = isset( $image['ID'] ) ? $image['ID'] : '';
				$image_url = isset( $image['url'] ) ? $image['url'] : '';
			} elseif ( is_numeric( $image ) ) {
				$image_id = (int) $image;
			} else {
				$image_url = $image;
			}

			// If we have either ID or URL then build.
			if ( '' !== $image_id || '' !== $image_url ) {
				$content .= '[' . $single_shortcode . ' image="' . $image_url . '" image_id="' . $image_id . '"/]';
			}
		}
		return $content;
	}

	/**
	 * Filter full output array.
	 *
	 * @since 2.1
	 * @access public
	 * @param array  $out Array to filter.
	 * @param array  $dynamic_arg Args for dynamic param.
	 * @param string $param_id ID for param in element.
	 * @param string $shortcode Name of shortcode.
	 * @param mixed  $value Value being set to that param.
	 * @return array
	 */
	public function extra_output_filter( $out, $dynamic_arg, $param_id, $shortcode, $value ) {
		$dynamic_id = $dynamic_arg['data'];

		switch ( $dynamic_id ) {
			case 'post_featured_image':
				if ( isset( $dynamic_arg['type'] ) && false !== strpos( $dynamic_arg['type'], 'featured-image-' ) && ! empty( $value ) ) {
					$out['image_id'] = Fusion_Images::get_attachment_id_from_url( $value );
				} else {
					$post_id = apply_filters( 'fusion_dynamic_post_id', get_the_ID() );
					if ( 'fusion_imageframe' === $shortcode && 'element_content' === $param_id ) {
						$out['image_id'] = get_post_thumbnail_id( $post_id );
					} else {
						$out[ $param_id . '_id' ] = get_post_thumbnail_id( $post_id );
					}
				}
				break;
			case 'acf_image':
				$image_id   = false;
				$image_data = isset( $dynamic_arg['field'] ) ? get_field( $dynamic_arg['field'], get_queried_object() ) : false;

				if ( is_array( $image_data ) && isset( $image_data['url'] ) ) {
					$image_id = $image_data['ID'];
				} elseif ( $image_data ) {
					$image_id = $image_data;
				}

				if ( 'fusion_imageframe' === $shortcode && 'element_content' === $param_id ) {
					if ( is_string( $image_data ) ) {
						$out['src'] = $image_id;
					} else {
						$out['image_id'] = $image_id;
					}
				} else {
					$out[ $param_id . '_id' ] = $image_id;
				}
				break;
			case 'woo_add_to_cart':
				if ( function_exists( 'wc_get_product' ) ) {
					$_product = wc_get_product();

					if ( $_product ) {
						$css_classes = '';
						if ( $_product->is_purchasable() && $_product->is_in_stock() ) {
							$css_classes .= ' add_to_cart_button';
						}
						if ( $_product->supports( 'ajax_add_to_cart' ) ) {
							$css_classes .= ' ajax_add_to_cart';
						}

						// We use link attributes to ensure it is targeting the anchor.
						if ( ! isset( $out['link_attributes'] ) ) {
							$out['link_attributes'] = '';
						}

						if ( '' !== $css_classes ) {
							$out['link_attributes'] .= ' class=\'' . ltrim( $css_classes ) . '\'';
							$out['link_attributes'] .= ' data-product_id=\'' . $_product->get_id() . '\'';
							$out['link_attributes'] .= ' data-quantity=\'1\'';
						}
					}
				}
				break;
			case 'woo_quick_view':
				if ( function_exists( 'wc_get_product' ) ) {
					$_product = wc_get_product();

					if ( $_product ) {

						// We use link attributes to ensure it is targeting the anchor.
						if ( ! isset( $out['link_attributes'] ) ) {
							$out['link_attributes'] = '';
						}

						$out['link_attributes'] .= ' class=\'fusion-quick-view\'';
						$out['link_attributes'] .= ' data-product-id=\'' . $_product->get_id() . '\'';
						$out['link_attributes'] .= ' data-product-title=\'' . esc_attr( $_product->get_title() ) . '\'';
					}
				}
				break;
		}
		return $out;
	}

	/**
	 * Filter the arguments.
	 *
	 * @since 2.1
	 * @access public
	 * @param array  $out Array to filter.
	 * @param array  $defaults Defaults for shortcode.
	 * @param array  $args Arguments for shortcode.
	 * @param stirng $shortcode Shortcode name.
	 * @return array
	 */
	public function filter_dynamic_args( $out, $defaults, $args, $shortcode ) {
		if ( ! isset( $out['dynamic_params'] ) ) {
			return $out;
		}

		$dynamic_args = $this->convert( $out['dynamic_params'] );

		foreach ( $dynamic_args as $id => $dynamic_arg ) {

			$value = $this->get_value( $dynamic_arg );

			if ( false === $value ) {
				continue;
			}

			$out[ $id ] = $value;

			$out = apply_filters( 'fusion_dynamic_override', $out, $dynamic_arg, $id, $shortcode, $value );
		}
		return $out;
	}

	/**
	 * Get the dynamic value.
	 *
	 * @since 2.1
	 * @access public
	 * @param array $dynamic_arg Array of arguments.
	 * @return mixed
	 */
	public function get_value( $dynamic_arg ) {
		$param             = isset( $dynamic_arg['data'] ) ? $this->get_param( $dynamic_arg['data'] ) : false;
		$fallback          = isset( $dynamic_arg['fallback'] ) && '' !== $dynamic_arg['fallback'] ? $dynamic_arg['fallback'] : false;
		$callback          = $param && isset( $param['callback'] ) ? $param['callback'] : false;
		$default           = $param && isset( $param['default'] ) && function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() && ( is_singular( 'fusion_tb_section' ) || -99 === get_the_ID() ) ? $param['default'] : false;
		$callback_function = $callback && isset( $callback['function'] ) ? $callback['function'] : false;
		$callback_exists   = $callback_function && ( is_callable( 'Fusion_Dynamic_Data_Callbacks::' . $callback_function ) || is_callable( $callback_function ) ) ? true : false;
		if ( ! $param || ( ! $default && ! $fallback && ! $callback_exists ) ) {
			return false;
		}

		if ( ! $callback_exists ) {
			return false !== $fallback ? $fallback : $default;
		}

		$value = is_callable( 'Fusion_Dynamic_Data_Callbacks::' . $callback_function ) ? call_user_func_array( 'Fusion_Dynamic_Data_Callbacks::' . $callback_function, [ $dynamic_arg ] ) : call_user_func_array( $callback_function, [ $dynamic_arg ] );
		if ( ( ! $value || '' === $value ) && ( $default || $fallback ) ) {
			return false !== $fallback ? $fallback : $default;
		}

		(string) $before_string = isset( $dynamic_arg['before'] ) ? $dynamic_arg['before'] : '';
		(string) $after_string  = isset( $dynamic_arg['after'] ) ? $dynamic_arg['after'] : '';

		$this->maybe_store_value( $value, $dynamic_arg );

		if ( ! is_string( $value ) ) {
			return $value;
		}
		return $before_string . $value . $after_string;
	}

	/**
	 * If a live editor load then we store.
	 *
	 * @since 2.1
	 * @access public
	 * @param mixed $value Dynamic value.
	 * @param array $dynamic_arg The arguments for specific dynamic value.
	 * @return void
	 */
	public function maybe_store_value( $value, $dynamic_arg ) {
		if ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() && ( ! FusionBuilder()->post_card_data['is_rendering'] || FusionBuilder()->editing_post_card ) ) {
			$this->values[ $dynamic_arg['data'] ][] = [
				'value' => $value,
				'args'  => $dynamic_arg,
			];
		}
	}

	/**
	 * Add in dynamic data values to live editor data.
	 *
	 * @since 2.1
	 * @access public
	 * @param array  $data Existing data.
	 * @param string $page_id The ID of the page.
	 * @param string $post_type The post type of the page.
	 * @return array
	 */
	public function filter_preview_data( $data, $page_id, $post_type ) {
		$page_id = apply_filters( 'fusion_dynamic_post_id', $page_id );
		$user    = wp_get_current_user();

		// Avoid duplicate values.
		foreach ( $this->values as $key => $val ) {
			$this->values[ $key ] = array_unique( $val, SORT_REGULAR );
		}

		$data['dynamicValues'][ $page_id ] = $this->values;
		$data['dynamicOptions']            = $this->get_params();
		$data['dynamicCommon']             = $this->get_common();
		$data['dynamicPostID']             = $page_id;
		$data['site_title']                = get_bloginfo( 'name' );
		$data['site_tagline']              = get_bloginfo( 'description' );
		$data['site_url']                  = home_url( '/' );
		$data['loggined_in_username']      = is_user_logged_in() ? $user->display_name : '';
		return $data;
	}

	/**
	 * Add in dynamic data values to live editor data.
	 *
	 * @since 2.1
	 * @access public
	 */
	public function backend_builder_data() {
		$script = FUSION_BUILDER_DEV_MODE ? 'fusion_builder_app_js' : 'fusion_builder';
		wp_localize_script(
			$script,
			'fusionDynamicData',
			[
				'dynamicOptions'      => $this->get_params(),
				'commonDynamicFields' => $this->get_common(),
			]
		);
	}

	/**
	 * Convert from encoded string to array.
	 *
	 * @since 2.1
	 * @access public
	 * @param string $param_string Encoded param string.
	 * @return array
	 */
	public function convert( $param_string ) {
		(array) $params = json_decode( fusion_decode_if_needed( $param_string ), true );
		return $params;
	}

	/**
	 * Get param map.
	 *
	 * @since 2.1
	 * @access public
	 * @return array
	 */
	public function get_params() {
		if ( empty( $this->params ) ) {
			$this->set_params();
		}
		return $this->params;
	}

	/**
	 * Get single param.
	 *
	 * @since 2.1
	 * @access public
	 * @param string $id Param ID.
	 * @return mixed
	 */
	public function get_param( $id ) {
		if ( empty( $this->params ) ) {
			$this->set_params();
		}
		return is_array( $this->params ) && isset( $this->params[ $id ] ) ? $this->params[ $id ] : false;
	}

	/**
	 * Common shared fields.
	 *
	 * @since 2.1
	 * @access public
	 * @return array
	 */
	public function get_common() {
		return [
			'before'   => [
				'label'       => esc_html__( 'Before', 'fusion-builder' ),
				'description' => esc_html__( 'Text before value.' ),
				'id'          => 'before',
				'default'     => '',
				'type'        => 'text',
				'value'       => '',
			],
			'after'    => [
				'label'       => esc_html__( 'After', 'fusion-builder' ),
				'description' => esc_html__( 'Text after value.' ),
				'id'          => 'after',
				'default'     => '',
				'type'        => 'text',
				'value'       => '',
			],
			'fallback' => [
				'label'       => esc_html__( 'Fallback', 'fusion-builder' ),
				'description' => esc_html__( 'Fallback if no value found.' ),
				'id'          => 'fallback',
				'default'     => '',
				'type'        => 'text',
				'value'       => '',
			],
		];
	}

	/**
	 * Get builder status.
	 *
	 * @since 2.1
	 * @return bool
	 */
	private function get_builder_status() {
		global $pagenow;

		$allowed_post_types = class_exists( 'FusionBuilder' ) ? FusionBuilder()->allowed_post_types() : [];
		$post_type          = get_post_type();

		return ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() || ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) ) && $post_type && in_array( $post_type, $allowed_post_types, true );
	}

	/**
	 * Get builder status.
	 *
	 * @since 2.1
	 * @return bool
	 */
	private function is_template_edited() {
		global $pagenow;

		// If not editing.
		if ( ! ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) && ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow ) ) {
			return false;
		}

		$post_type = get_post_type();

		// Editing a layout section.
		if ( 'fusion_tb_section' === $post_type ) {
			return true;
		}

		// Editing a post card.
		if ( fusion_is_post_card() ) {
			return true;
		}

		// Editing an off canvas.
		if ( 'awb_off_canvas' === $post_type ) {
			return true;
		}

		// Editing a post card.
		if ( 'fusion_element' === $post_type ) {
			$terms = get_the_terms( get_the_ID(), 'element_category' );
			if ( $terms && 'post_cards' === $terms[0]->slug ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Set param map.
	 *
	 * @since 2.1
	 * @access public
	 * @return void
	 */
	public function set_params() {
		$fusion_settings = awb_get_fusion_settings();

		$post_taxonomies = [];
		$params          = [];
		$featured_images = [
			'main' => esc_html__( 'Main Featured Image', 'fusion-builder' ),
		];
		$single_label    = false;

		$post_data = [
			'id'        => get_the_ID(),
			'post_type' => get_post_type(),
			'archive'   => false,
		];
		$post_data = apply_filters( 'fusion_dynamic_post_data', $post_data );

		if ( $this->get_builder_status() ) {
			// Get all registered taxonomies.
			$object_tax_slugs = get_object_taxonomies( $post_data['post_type'] );

			// Create key value pairs.
			foreach ( $object_tax_slugs as $tax_slug ) {
				$tax = get_taxonomy( $tax_slug );
				if ( false !== $tax && $tax->public ) {
					$post_taxonomies[ $tax_slug ] = $tax->labels->name;
				}
			}
		}

		if ( 'fusion_element' === $post_data['post_type'] ) {
			$terms = get_the_terms( $post_data['id'], 'element_category' );
			if ( is_array( $terms ) && 'post_cards' === $terms[0]->name ) {
				$single_label = esc_html__( 'Post Card', 'fusion-builder' );
			}
		}
		if ( ! $single_label ) {
			$post_type_object = get_post_type_object( $post_data['post_type'] );
			if ( is_object( $post_type_object ) ) {
				$single_label = $post_type_object->labels->singular_name;
			} else {
				$single_label = esc_html__( 'Post', 'fusion-builder' );
			}
		}

		$posts_slideshow_number = $fusion_settings->get( 'posts_slideshow_number' );
		for ( $i = 2; $i <= $posts_slideshow_number; $i++ ) {
			/* Translators: %d: The number of our featured image. */
			$featured_images[ 'featured-image-' . $i ] = sprintf( esc_html__( 'Featured Image %d', 'fusion-builder' ), $i );
		}

		$params = [
			'post_title' => [
				/* translators: Single post type title. */
				'label'            => esc_html__( 'Title', 'fusion-builder' ),
				$single_label,
				'id'               => 'post_title',
				'group'            => $single_label,
				'options'          => $this->text_fields,
				'ajax_on_template' => true,
				'default'          => __( 'Your Title Goes Here', 'fusion-builder' ),
				'callback'         => [
					'function' => 'fusion_get_object_title',
					'ajax'     => true,
				],
				'listeners'        => [
					'post_title' => [
						'location' => 'postDetails',
					],
				],
				'fields'           => [
					'include_context' => [
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Include Context', 'fusion-builder' ),
						'description' => esc_html__( 'Whether to include title context, ie. Category: Avada.' ),
						'param_name'  => 'include_context',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
				],
			],
		];

		$params['post_excerpt'] = [
			/* translators: Single post type excerpt. */
			'label'            => esc_html__( 'Excerpt / Archive Description', 'fusion-builder' ),
			'id'               => 'post_excerpt',
			'group'            => $single_label,
			'options'          => $this->text_fields,
			'default'          => __( 'Your Description Goes Here', 'fusion-builder' ),
			'ajax_on_template' => true,
			'callback'         => [
				'function' => 'fusion_get_object_excerpt',
				'ajax'     => true,
			],
		];

		// Only add single post related for single posts.
		$params['post_comments'] = $this->is_template_edited() || ( $post_data['id'] && 0 < $post_data['id'] && comments_open( $post_data['id'] ) ) ? [
			/* translators: Single post type terms. */
			'label'    => esc_html__( 'Comments Number', 'fusion-builder' ),
			'id'       => 'post_comments',
			'group'    => $single_label,
			'options'  => $this->text_fields,
			'callback' => [
				'function' => 'fusion_get_post_comments',
				'ajax'     => true,
			],
			'fields'   => [
				'link' => [
					'type'        => 'radio_button_set',
					'heading'     => esc_html__( 'Link', 'fusion-builder' ),
					'description' => esc_html__( 'Whether the comment number should link to the comments form.' ),
					'param_name'  => 'link',
					'default'     => 'no',
					'value'       => [
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					],
				],
			],
		] : false;

		$params['post_terms'] = $this->is_template_edited() || ! empty( $post_taxonomies ) || ! $this->get_builder_status() ? [
			/* translators: Single post type terms. */
			'label'    => esc_html__( 'Terms', 'fusion-builder' ),
			'id'       => 'post_terms',
			'group'    => $single_label,
			'options'  => $this->text_fields,
			'default'  => 'Lorem, Ipsum, Dolor',
			'callback' => [
				'function' => 'fusion_get_post_terms',
				'ajax'     => true,
			],
			'fields'   => [
				'type'      => [
					'heading'     => esc_html__( 'Taxonomy', 'fusion-builder' ),
					'description' => $this->is_template_edited() ? esc_html__( 'Enter taxonomy slug.' ) : esc_html__( 'Taxonomy to use.' ),
					'param_name'  => 'type',
					'default'     => '',
					'type'        => $this->is_template_edited() ? 'text' : 'select',
					'value'       => $post_taxonomies,
				],
				'separator' => [
					'heading'     => esc_html__( 'Separator', 'fusion-builder' ),
					'description' => esc_html__( 'Separator between post terms.' ),
					'param_name'  => 'separator',
					'value'       => ',',
					'type'        => 'textfield',
				],
				'link'      => [
					'type'        => 'radio_button_set',
					'heading'     => esc_html__( 'Link', 'fusion-builder' ),
					'description' => esc_html__( 'Whether each term should link to term page.' ),
					'param_name'  => 'link',
					'default'     => 'yes',
					'value'       => [
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					],
				],
			],
		] : false;

		$params['post_id'] = [
			/* translators: Single post type ID. */
			'label'    => esc_html__( 'ID', 'fusion-builder' ),
			'id'       => 'post_id',
			'group'    => $single_label,
			'options'  => array_unique( array_merge( $this->text_fields, $this->number_fields ) ),
			'callback' => [
				'function' => 'fusion_get_post_id',
				'ajax'     => false,
			],
		];

		$params['post_time'] = [
			/* translators: Single post type time. */
			'label'    => esc_html__( 'Time', 'fusion-builder' ),
			'id'       => 'post_time',
			'group'    => $single_label,
			'options'  => $this->text_fields,
			'default'  => current_time( get_option( 'time_format' ) ),
			'callback' => [
				'function' => 'fusion_get_post_time',
				'ajax'     => true,
			],
			'fields'   => [
				'format' => [
					'heading'     => esc_html__( 'Format', 'fusion-builder' ),
					'description' => __( 'Time format to use.  <a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank" rel="noopener noreferrer">Formatting Date and Time</a>' ),
					'param_name'  => 'format',
					'value'       => get_option( 'time_format' ),
					'type'        => 'text',
				],
			],
		];

		$params['post_date'] = [
			/* translators: Single post type date. */
			'label'    => esc_html__( 'Date', 'fusion-builder' ),
			'id'       => 'post_date',
			'group'    => $single_label,
			'options'  => $this->text_fields,
			'default'  => current_time( get_option( 'date_format' ) ),
			'callback' => [
				'function' => 'fusion_get_post_date',
				'ajax'     => true,
			],
			'fields'   => [
				'type'   => [
					'heading'     => esc_html__( 'Date Type', 'fusion-builder' ),
					'description' => esc_html__( 'Date type to display.' ),
					'param_name'  => 'type',
					'default'     => '',
					'type'        => 'select',
					'value'       => [
						''         => esc_html__( 'Post Published', 'fusion-builder' ),
						'modified' => esc_html__( 'Post Modified', 'fusion-builder' ),
					],
				],
				'format' => [
					'heading'     => esc_html__( 'Format', 'fusion-builder' ),
					'description' => __( 'Date format to use.  <a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank" rel="noopener noreferrer">Formatting Date and Time</a>' ),
					'param_name'  => 'format',
					'value'       => get_option( 'date_format' ),
					'type'        => 'text',
				],
			],
		];

		if ( 'disabled' !== $fusion_settings->get( 'post_views' ) ) {
			$params['post_views'] = [
				'label'    => esc_html__( 'Total Views', 'fusion-builder' ),
				'id'       => 'post_views',
				'group'    => $single_label,
				'options'  => array_unique( array_merge( $this->text_fields, $this->number_fields ) ),
				'callback' => [
					'function' => 'get_post_total_views',
					'ajax'     => true,
				],
			];

			$params['post_today_views'] = [
				'label'    => esc_html__( 'Today Views', 'fusion-builder' ),
				'id'       => 'post_today_views',
				'group'    => $single_label,
				'options'  => array_unique( array_merge( $this->text_fields, $this->number_fields ) ),
				'callback' => [
					'function' => 'get_post_today_views',
					'ajax'     => true,
				],
			];
		}

		$params['post_reading_time'] = [
			'label'    => esc_html__( 'Reading Time', 'fusion-builder' ),
			'id'       => 'post_reading_time',
			'group'    => $single_label,
			'options'  => array_unique( array_merge( $this->text_fields, $this->number_fields ) ),
			'callback' => [
				'function' => 'get_post_reading_time',
				'ajax'     => true,
			],
			'fields'   => [
				'reading_speed'         => [
					'heading'     => esc_html__( 'Reading Speed', 'fusion-builder' ),
					'description' => esc_html__( 'Average words read per minute. Reading time will be displayed in minutes based on this value.' ),
					'param_name'  => 'reading_speed',
					'type'        => 'text',
					'value'       => '200',
				],
				'use_decimal_precision' => [
					'type'        => 'radio_button_set',
					'heading'     => esc_html__( 'Use Decimal Precision', 'fusion-builder' ),
					'description' => esc_html__( 'Whether to use decimal precision(ex 2.3 min) or not(2 min).' ),
					'param_name'  => 'use_decimal_precision',
					'default'     => 'yes',
					'value'       => [
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					],
				],
			],
		];

		$params['post_type'] = [
			'label'            => esc_html__( 'Post Type', 'fusion-builder' ),
			'id'               => 'post_type',
			'group'            => $single_label,
			'options'          => $this->text_fields,
			'ajax_on_template' => true,
			'callback'         => [
				'function' => 'fusion_get_post_type',
				'ajax'     => false,
			],
		];

		$params['post_custom_field'] = [
			/* translators: Single post type custom field. */
			'label'    => esc_html__( 'Custom Field', 'fusion-builder' ),
			'id'       => 'post_custom_field',
			'group'    => $single_label,
			'options'  => array_unique( array_merge( $this->link_and_text_fields, $this->number_fields ) ),
			'default'  => __( 'Custom Field Value Here', 'fusion-builder' ),
			'callback' => [
				'function' => 'fusion_get_post_custom_field',
				'ajax'     => false,
			],
			'fields'   => [
				'key' => [
					'heading'     => esc_html__( 'Key', 'fusion-builder' ),
					'description' => esc_html__( 'Custom field ID key.' ),
					'param_name'  => 'key',
					'default'     => '',
					'type'        => 'text',
				],
			],
		];

		$params['post_permalink'] = [
			/* translators: Single post type custom field. */
			'label'    => esc_html__( 'Permalink', 'fusion-builder' ),
			'id'       => 'post_permalink',
			'group'    => $single_label,
			'options'  => $this->link_fields,
			'callback' => [
				'function' => 'fusion_get_post_permalink',
				'ajax'     => false,
			],
		];

		$params['post_featured_image'] = post_type_supports( $post_data['post_type'], 'thumbnail' ) || ! $this->get_builder_status() || $this->is_template_edited() ? [
			'label'     => esc_html__( 'Featured Image', 'fusion-builder' ),
			'id'        => 'post_featured_image',
			'group'     => $single_label,
			'options'   => $this->image_fields,
			'callback'  => [
				'function' => 'post_featured_image',
				'ajax'     => true,
			],
			'exclude'   => [ 'before', 'after' ],
			'fields'    => [
				'type' => [
					'heading'     => esc_html__( 'Featured Image', 'fusion-builder' ),
					'description' => esc_html__( 'Select which featured image should display.', 'fusion-builder' ),
					'param_name'  => 'type',
					'default'     => 'main',
					'type'        => 'select',
					'value'       => $featured_images,
				],
			],
			'listeners' => [
				'_thumbnail_id' => [
					'location' => 'postMeta',
				],
			],
		] : false;

		$params['term_count'] = [
			'label'    => esc_html__( 'Term Count', 'fusion-builder' ),
			'id'       => 'term_count',
			'group'    => $single_label,
			'options'  => $this->text_fields,
			'fields'   => [
				'singular_text' => [
					'type'        => 'text',
					'heading'     => esc_html__( 'Singular', 'fusion-builder' ),
					'description' => esc_html__( 'Default singular text.' ),
					'param_name'  => 'singular_text',
					'default'     => '',
				],
				'plural_text'   => [
					'type'        => 'text',
					'heading'     => esc_html__( 'Plural', 'fusion-builder' ),
					'description' => esc_html__( 'Default plural text.' ),
					'param_name'  => 'plural_text',
					'default'     => '',
				],
			],
			'callback' => [
				'function' => 'get_term_count',
				'ajax'     => true,
			],
		];

		$params['search_count'] = [
			'label'    => esc_html__( 'Search Count', 'fusion-builder' ),
			'id'       => 'search_count',
			'group'    => $single_label,
			'options'  => $this->text_fields,
			'fields'   => [
				'singular_text' => [
					'type'        => 'text',
					'heading'     => esc_html__( 'Singular', 'fusion-builder' ),
					'description' => esc_html__( 'Default singular text.' ),
					'param_name'  => 'singular_text',
					'default'     => '',
				],
				'plural_text'   => [
					'type'        => 'text',
					'heading'     => esc_html__( 'Plural', 'fusion-builder' ),
					'description' => esc_html__( 'Default plural text.' ),
					'param_name'  => 'plural_text',
					'default'     => '',
				],
			],
			'callback' => [
				'function' => 'get_search_count',
				'ajax'     => true,
			],
		];

		$params['post_gallery'] = post_type_supports( $post_data['post_type'], 'thumbnail' ) || ! $this->get_builder_status() || $this->is_template_edited() ? [
			'label'     => esc_html__( 'Featured Images', 'fusion-builder' ),
			'id'        => 'post_gallery',
			'group'     => $single_label,
			'options'   => [ 'multiple_upload' ],
			'exclude'   => [ 'before', 'after', 'fallback' ],
			'callback'  => [
				'function' => 'post_gallery',
				'ajax'     => true,
			],
			'fields'    => [
				'include_main' => [
					'type'        => 'radio_button_set',
					'heading'     => esc_html__( 'Include Main Featured Image', 'fusion-builder' ),
					'description' => esc_html__( 'Whether to include the main featured image as well.' ),
					'param_name'  => 'include_main',
					'default'     => 'no',
					'value'       => [
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					],
				],
			],
			'listeners' => [
				'_thumbnail_id' => [
					'location' => 'postMeta',
				],
			],
		] : false;

		$params['site_title']        = [
			'label'    => esc_html__( 'Site Title', 'fusion-builder' ),
			'id'       => 'site_title',
			'group'    => esc_attr__( 'Site', 'fusion-builder' ),
			'options'  => $this->text_fields,
			'callback' => [
				'function' => 'fusion_get_site_title',
				'ajax'     => true,
			],
		];
		$params['site_tagline']      = [
			'label'    => esc_html__( 'Site Tagline', 'fusion-builder' ),
			'id'       => 'site_tagline',
			'group'    => esc_attr__( 'Site', 'fusion-builder' ),
			'options'  => $this->text_fields,
			'callback' => [
				'function' => 'fusion_get_site_tagline',
				'ajax'     => true,
			],
		];
		$params['site_url']          = [
			'label'    => esc_html__( 'Site URL', 'fusion-builder' ),
			'id'       => 'site_url',
			'group'    => esc_attr__( 'Site', 'fusion-builder' ),
			'options'  => $this->link_fields,
			'callback' => [
				'function' => 'fusion_get_site_url',
				'ajax'     => true,
			],
		];
		$params['site_logo']         = [
			'label'    => esc_html__( 'Logo', 'fusion-builder' ),
			'id'       => 'site_logo',
			'group'    => esc_attr__( 'Site', 'fusion-builder' ),
			'options'  => $this->image_fields,
			'callback' => [
				'function' => 'fusion_get_site_logo',
				'ajax'     => true,
			],
			'exclude'  => [ 'before', 'after' ],
			'fields'   => [
				'type' => [
					'heading'     => esc_html__( 'Logo Type', 'fusion-builder' ),
					'description' => esc_html__( 'Select logo type to display. All can be used in image element for header layout section.', 'fusion-builder' ),
					'param_name'  => 'type',
					'default'     => 'all',
					'type'        => 'select',
					'value'       => [
						'all'            => esc_html__( 'All', 'fusion-builder' ),
						'default_normal' => esc_html__( 'Default (Normal)', 'fusion-builder' ),
						'default_retina' => esc_html__( 'Default (Retina)', 'fusion-builder' ),
						'sticky_normal'  => esc_html__( 'Sticky (Normal)', 'fusion-builder' ),
						'sticky_retina'  => esc_html__( 'Sticky (Retina)', 'fusion-builder' ),
						'mobile_normal'  => esc_html__( 'Mobile (Normal)', 'fusion-builder' ),
						'mobile_retina'  => esc_html__( 'Mobile (Retina)', 'fusion-builder' ),
					],
				],
			],
		];
		$params['date']              = [
			'label'    => esc_html__( 'Date', 'fusion-builder' ),
			'id'       => 'date',
			'group'    => esc_attr__( 'Other', 'fusion-builder' ),
			'options'  => $this->text_fields,
			'callback' => [
				'function' => 'fusion_get_date',
				'ajax'     => true,
			],
			'fields'   => [
				'format' => [
					'heading'     => esc_html__( 'Format', 'fusion-builder' ),
					'description' => __( 'Date format to use.  <a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank" rel="noopener noreferrer">Formatting Date and Time</a>' ),
					'param_name'  => 'format',
					'value'       => get_option( 'date_format' ),
					'type'        => 'text',
				],
			],
		];
		$params['user']              = [
			'label'    => esc_html__( 'Logged in Display Name', 'fusion-builder' ),
			'id'       => 'user',
			'group'    => esc_attr__( 'Other', 'fusion-builder' ),
			'options'  => $this->text_fields,
			'callback' => [
				'function' => 'fusion_get_logged_in_username',
				'ajax'     => true,
			],
		];
		$params['request_parameter'] = [
			'label'    => esc_html__( 'Request Parameter', 'fusion-builder' ),
			'id'       => 'site_request_param',
			'group'    => esc_attr__( 'Other', 'fusion-builder' ),
			'options'  => $this->link_and_text_fields,
			'callback' => [
				'function' => 'fusion_get_site_request_param',
				'ajax'     => true,
			],
			'fields'   => [
				'type' => [
					'heading'    => esc_html__( 'Param Type', 'fusion-builder' ),
					'param_name' => 'type',
					'default'    => 'get',
					'type'       => 'select',
					'value'      => [
						'get'       => esc_html__( 'GET', 'fusion-builder' ),
						'post'      => esc_html__( 'POST', 'fusion-builder' ),
						'query_var' => esc_html__( 'Query Var', 'fusion-builder' ),
					],
				],
				'name' => [
					'heading'    => esc_html__( 'Query Var', 'fusion-builder' ),
					'param_name' => 'name',
					'type'       => 'textfield',
					'value'      => '',
				],
			],
		];
		$params['shortcode']         = [
			'label'    => esc_html__( 'Shortcode', 'fusion-builder' ),
			'id'       => 'shortcode',
			'group'    => esc_attr__( 'Other', 'fusion-builder' ),
			'options'  => $this->link_and_text_fields,
			'callback' => [
				'function' => 'dynamic_shortcode',
				'ajax'     => true,
			],
			'fields'   => [
				'shortcode' => [
					'heading'    => esc_html__( 'Shortcode', 'fusion-builder' ),
					'param_name' => 'shortcode',
					'type'       => 'textarea',
					'value'      => '',
				],
			],
		];

		$params = $this->maybe_add_off_canvas_fields( $params, $post_data['id'], $post_data['post_type'] );
		$params = $this->maybe_add_acf_fields( $params, $post_data['id'], $post_data['post_type'] );
		$params = $this->maybe_add_woo_fields( $params, $post_data['id'], $post_data['post_type'] );
		$params = $this->maybe_add_hubspot_fields( $params, $post_data['id'], $post_data['post_type'] );

		// Skip target post data.
		$params = $this->maybe_add_page_title_bar_fields( $params, get_the_ID(), get_post_type() );

		// Skip author if we are editing archive template.
		if ( ! $post_data['archive'] && ! is_404() && ! is_search() || $this->is_template_edited() ) {
			$params = $this->maybe_add_author_fields( $params, $post_data['id'], $post_data['post_type'] );
		}

		if ( class_exists( 'WooCommerce', false ) ) {
			$params = $this->maybe_add_woo_custom_fields( $params, $post_data['id'], $post_data['post_type'] );
		}

		if ( class_exists( 'Tribe__Events__Main', false ) ) {
			$params = $this->maybe_add_events_calendar_custom_fields( $params, $post_data['id'], $post_data['post_type'] );
		}

		$this->params = apply_filters( 'fusion_set_dynamic_params', $params );

	}

	/**
	 * Adds Off Canvas fields to dynamic sources
	 *
	 * @param array  $params    The params.
	 * @param int    $post_id   The post ID.
	 * @param string $post_type The post type.
	 * @return array
	 */
	public function maybe_add_off_canvas_fields( $params, $post_id, $post_type ) {

		if ( class_exists( 'AWB_Off_Canvas_Front_End' ) && false !== AWB_Off_Canvas::is_enabled() ) {
			$off_canvas_items = AWB_Off_Canvas_Front_End()->get_available_items();

			$params['toggle_off_canvas'] = [
				'label'    => esc_html__( 'Toggle Off Canvas', 'fusion-builder' ),
				'id'       => 'toggle_off_canvas',
				'group'    => esc_attr__( 'Off Canvas', 'fusion-builder' ),
				'options'  => $this->link_fields,
				'exclude'  => [ 'before', 'after', 'fallback' ],
				'callback' => [
					'function' => 'fusion_toggle_off_canvas',
					'ajax'     => false,
				],
				'fields'   => [
					'off_canvas_id' => [
						'heading'     => esc_html__( 'Off Canvas', 'fusion-builder' ),
						'description' => esc_html__( 'Select off canvas.' ),
						'param_name'  => 'off_canvas_id',
						'default'     => '',
						'type'        => 'select',
						'value'       => $off_canvas_items,
					],
				],
			];
			$params['open_off_canvas']   = [
				'label'    => esc_html__( 'Open Off Canvas', 'fusion-builder' ),
				'id'       => 'open_off_canvas',
				'group'    => esc_attr__( 'Off Canvas', 'fusion-builder' ),
				'options'  => $this->link_fields,
				'exclude'  => [ 'before', 'after', 'fallback' ],
				'callback' => [
					'function' => 'fusion_open_off_canvas',
					'ajax'     => false,
				],
				'fields'   => [
					'off_canvas_id' => [
						'heading'     => esc_html__( 'Off Canvas', 'fusion-builder' ),
						'description' => esc_html__( 'Select off canvas.' ),
						'param_name'  => 'off_canvas_id',
						'default'     => '',
						'type'        => 'select',
						'value'       => $off_canvas_items,
					],
				],
			];
			$params['close_off_canvas']  = [
				'label'    => esc_html__( 'Close Off Canvas', 'fusion-builder' ),
				'id'       => 'close_off_canvas',
				'group'    => esc_attr__( 'Off Canvas', 'fusion-builder' ),
				'options'  => $this->link_fields,
				'exclude'  => [ 'before', 'after', 'fallback' ],
				'callback' => [
					'function' => 'fusion_close_off_canvas',
					'ajax'     => false,
				],
				'fields'   => [
					'off_canvas_id' => [
						'heading'     => esc_html__( 'Off Canvas', 'fusion-builder' ),
						'description' => esc_html__( 'Select off canvas.' ),
						'param_name'  => 'off_canvas_id',
						'default'     => '',
						'type'        => 'select',
						'value'       => $off_canvas_items,
					],
				],
			];

		}

		return $params;
	}

	/**
	 * Adds Event start and end dates to dynamic sources
	 *
	 * @param array  $params    The params.
	 * @param int    $post_id   The post ID.
	 * @param string $post_type The post type.
	 * @return array
	 */
	public function maybe_add_events_calendar_custom_fields( $params, $post_id, $post_type ) {
		$params['event_date'] = [
			'label'            => esc_html__( 'Event Date', 'fusion-builder' ),
			'id'               => 'event_date',
			'group'            => esc_attr__( 'Events Calendar', 'fusion-builder' ),
			'options'          => $this->text_fields,
			'ajax_on_template' => true,
			'fields'           => [
				'event_date_type' => [
					'heading'     => esc_html__( 'Date Type', 'fusion-builder' ),
					'description' => esc_html__( 'The date format is taken from Events Calendar plugin settings.', 'fusion-builder' ),
					'param_name'  => 'event_date_type',
					'default'     => 'both',
					'type'        => 'select',
					'value'       => [
						'both'             => esc_html__( 'Full Date', 'fusion-builder' ),
						'start_event_date' => esc_html__( 'Start Date', 'fusion-builder' ),
						'end_event_date'   => esc_html__( 'End Date', 'fusion-builder' ),
					],
				],
			],
			'callback'         => [
				'function' => 'get_event_date_to_display',
				'ajax'     => true,
			],
		];

		$params['events_calendar_date'] = [
			'label'    => esc_html__( 'Event Date', 'fusion-builder' ),
			'id'       => 'events_calendar_date',
			'group'    => esc_attr__( 'Events Calendar', 'fusion-builder' ),
			'options'  => $this->date_time_picker,
			'default'  => '',
			'callback' => [
				'function' => 'get_event_date',
				'ajax'     => true,
			],
			'exclude'  => [ 'before', 'after' ],
			'fields'   => [
				'event_date' => [
					'heading'     => esc_html__( 'Date', 'fusion-builder' ),
					'description' => esc_html__( 'Event Date to display.', 'fusion-builder' ),
					'param_name'  => 'event_date',
					'default'     => '',
					'type'        => 'select',
					'value'       => [
						'start_event_date' => esc_html__( 'Event Start Date', 'fusion-builder' ),
						'end_event_date'   => esc_html__( 'Event End Date', 'fusion-builder' ),
					],
				],
				'event_id'   => [
					'heading'     => esc_html__( 'Event ID', 'fusion-builder' ),
					'description' => esc_html__( 'Event Date to display. Leave empty to use the current event ID.', 'fusion-builder' ),
					'param_name'  => 'event_id',
					'default'     => '',
					'type'        => 'text',
				],
			],
		];

		return $params;
	}


	/**
	 * Adds Woo start and end sale dates to dynamic sources
	 *
	 * @param array  $params    The params.
	 * @param int    $post_id   The post ID.
	 * @param string $post_type The post type.
	 * @return array
	 */
	public function maybe_add_woo_custom_fields( $params, $post_id, $post_type ) {

		$params['woo_sale_date'] = [
			'label'    => esc_html__( 'Sale Date', 'fusion-builder' ),
			'id'       => 'woo_sale_date',
			'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
			'options'  => $this->date_time_picker,
			'default'  => '',
			'callback' => [
				'function' => 'woo_sale_date',
				'ajax'     => true,
			],
			'exclude'  => [ 'before', 'after' ],
			'fields'   => [
				'sale_date'  => [
					'heading'     => esc_html__( 'Date', 'fusion-builder' ),
					'description' => esc_html__( 'Sale Date to display.', 'fusion-builder' ),
					'param_name'  => 'sale_date',
					'default'     => 'start_date',
					'type'        => 'select',
					'value'       => [
						'start_date' => esc_html__( 'Sale Start Date', 'fusion-builder' ),
						'end_date'   => esc_html__( 'Sale End Date', 'fusion-builder' ),
					],
				],
				'product_id' => [
					'heading'     => esc_html__( 'Product ID', 'fusion-builder' ),
					'description' => esc_html__( 'Product Sale Date to display. Leave empty to use the current product ID.', 'fusion-builder' ),
					'param_name'  => 'product_id',
					'default'     => '',
					'type'        => 'text',
				],
			],
		];

		$params['woo_cart_count'] = [
			'label'    => esc_html__( 'Cart Count', 'fusion-builder' ),
			'id'       => 'woo_cart_count',
			'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
			'options'  => $this->text_fields,
			'fields'   => [
				'singular_text' => [
					'type'        => 'text',
					'heading'     => esc_html__( 'Singular', 'fusion-builder' ),
					'description' => esc_html__( 'Default singular text.' ),
					'param_name'  => 'singular_text',
					'default'     => '',
				],
				'plural_text'   => [
					'type'        => 'text',
					'heading'     => esc_html__( 'Plural', 'fusion-builder' ),
					'description' => esc_html__( 'Default plural text.' ),
					'param_name'  => 'plural_text',
					'default'     => '',
				],
			],
			'callback' => [
				'function' => 'woo_get_cart_count',
				'ajax'     => true,
			],
		];

		$params['woo_cart_total'] = [
			'label'    => esc_html__( 'Cart Total', 'fusion-builder' ),
			'id'       => 'woo_get_cart_total',
			'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
			'options'  => $this->text_fields,
			'callback' => [
				'function' => 'woo_get_cart_total',
				'ajax'     => true,
			],
		];

		return $params;
	}

	/**
	 * Add Author fields if they exist.
	 *
	 * @since 2.2
	 * @access public
	 * @param array  $params Params being used.
	 * @param int    $post_id The target post id.
	 * @param string $post_type The target post type.
	 * @return array
	 */
	public function maybe_add_author_fields( $params, $post_id, $post_type ) {
		if ( post_type_supports( $post_type, 'author' ) || $this->is_template_edited() ) {
			$params['author_name']        = [
				'label'    => esc_html__( 'Author Name', 'fusion-builder' ),
				'id'       => 'author_name',
				'group'    => esc_attr__( 'Author', 'fusion-builder' ),
				'options'  => $this->text_fields,
				'default'  => 'Emery Burns',
				'callback' => [
					'function' => 'get_author_name',
					'ajax'     => true,
				],
			];
			$params['author_description'] = [
				'label'    => esc_html__( 'Author Description', 'fusion-builder' ),
				'id'       => 'author_description',
				'group'    => esc_attr__( 'Author', 'fusion-builder' ),
				'options'  => $this->text_fields,
				'default'  => 'Lorem ipsum dolor sit amet.',
				'callback' => [
					'function' => 'get_author_description',
					'ajax'     => true,
				],
			];
			$params['author_avatar']      = [
				'label'    => esc_html__( 'Author Avatar', 'fusion-builder' ),
				'id'       => 'author_avatar',
				'group'    => esc_attr__( 'Author', 'fusion-builder' ),
				'options'  => $this->image_fields,
				'callback' => [
					'function' => 'get_author_avatar',
					'ajax'     => true,
				],
			];
			$params['author_url']         = [
				'label'    => esc_html__( 'Author Page URL', 'fusion-builder' ),
				'id'       => 'author_url',
				'group'    => esc_attr__( 'Author', 'fusion-builder' ),
				'options'  => $this->link_fields,
				'exclude'  => [ 'before', 'after' ],
				'default'  => 'https://theme-fusion.com',
				'callback' => [
					'function' => 'get_author_url',
					'ajax'     => true,
				],
			];
			$params['author_social']      = [
				'label'    => esc_html__( 'Author Social URL', 'fusion-builder' ),
				'id'       => 'author_social',
				'group'    => esc_attr__( 'Author', 'fusion-builder' ),
				'options'  => $this->link_fields,
				'exclude'  => [ 'before', 'after' ],
				'callback' => [
					'function' => 'get_author_social',
					'ajax'     => true,
				],
				'fields'   => [
					'type' => [
						'heading'     => esc_html__( 'Social Link', 'fusion-builder' ),
						'description' => esc_html__( 'Select which social platform link to use.' ),
						'param_name'  => 'type',
						'default'     => 'author_email',
						'type'        => 'select',
						'value'       => [
							'author_email'    => esc_html__( 'Email', 'fusion-builder' ),
							'author_facebook' => esc_html__( 'Facebook', 'fusion-builder' ),
							'author_twitter'  => esc_html__( 'Twitter', 'fusion-builder' ),
							'author_linkedin' => esc_html__( 'LinkedIn', 'fusion-builder' ),
							'author_dribble'  => esc_html__( 'Dribble', 'fusion-builder' ),
							'author_whatsapp' => esc_html__( 'WhatsApp', 'fusion-builder' ),
						],
					],
				],
			];
		}
		return $params;
	}

	/**
	 * Add ACF fields if they exist.
	 *
	 * @since 2.1
	 * @access public
	 * @param array  $params    Params being used.
	 * @param int    $post_id   The target post id.
	 * @param string $post_type The target post type.
	 * @return array
	 */
	public function maybe_add_acf_fields( $params, $post_id, $post_type ) {
		if ( class_exists( 'ACF' ) ) {
			$fields              = [];
			$text_options        = false;
			$image_options       = false;
			$file_options        = false;
			$link_options        = false;
			$string_option_types = [ 'text', 'textarea', 'number', 'range', 'wysiwyg', 'raw_textarea', 'raw_text' ];
			$bulk_image_options  = false;

			// In builder get fields active for post type for each group.
			if ( $this->get_builder_status() ) {
				$groups = acf_get_field_groups( [ 'post_id' => $post_id ] );
				foreach ( $groups as $group ) {

					// Get fields for group and check for text or image types.
					$fields = acf_get_fields( $group['key'] );
					if ( $fields && is_array( $fields ) ) {
						foreach ( $fields as $field ) {
							if ( in_array( $field['type'], $string_option_types, true ) ) {
								$text_options[ $field['name'] ] = $field['label'];
							} elseif ( 'image' === $field['type'] ) {
								$image_options[ $field['name'] ] = $field['label'];
							} elseif ( 'file' === $field['type'] ) {
								$file_options[ $field['name'] ] = $field['label'];
							} elseif ( 'url' === $field['type'] ) {
								$link_options[ $field['name'] ] = $field['label'];
							} elseif ( 'gallery' === $field['type'] ) {
								$bulk_image_options[ $field['name'] ] = $field['label'];
							}
						}
					}
				}
			}

			// In builder and have text options add option, on front-end add for callback availability.
			if ( ! $this->get_builder_status() || $text_options || $this->is_template_edited() ) {
				$params['acf_text'] = [
					'label'    => esc_html__( 'ACF Text', 'fusion-builder' ),
					'id'       => 'acf_text',
					'group'    => esc_attr__( 'Advanced Custom Fields', 'fusion-builder' ),
					'options'  => array_unique( array_merge( $this->link_and_text_fields, $this->file_fields, $this->number_fields ) ),
					'default'  => __( 'Custom Field Value Here', 'fusion-builder' ),
					'callback' => [
						'function' => 'acf_get_field',
						'ajax'     => true,
					],
					'fields'   => [
						'field' => [
							'heading'     => esc_html__( 'Field', 'fusion-builder' ),
							'description' => $this->is_template_edited() ? esc_html__( 'Enter field name you want to use.', 'fusion-builder' ) : esc_html__( 'Which field you want to use.', 'fusion-builder' ),
							'param_name'  => 'field',
							'default'     => '',
							'type'        => $this->is_template_edited() ? 'text' : 'select',
							'value'       => $text_options,
						],
					],
				];
			}

			// In builder and have image options add option, on front-end add for callback availability.
			if ( ! $this->get_builder_status() || $image_options || $this->is_template_edited() ) {
				$params['acf_image'] = [
					'label'    => esc_html__( 'ACF Image', 'fusion-builder' ),
					'id'       => 'acf_image',
					'group'    => esc_attr__( 'Advanced Custom Fields', 'fusion-builder' ),
					'callback' => [
						'function' => 'acf_get_image_field',
						'ajax'     => true,
					],
					'exclude'  => [ 'before', 'after', 'fallback' ],
					'options'  => $this->image_fields,
					'fields'   => [
						'field' => [
							'heading'     => esc_html__( 'Field', 'fusion-builder' ),
							'description' => $this->is_template_edited() ? esc_html__( 'Enter field name you want to use.', 'fusion-builder' ) : esc_html__( 'Which field you want to use.', 'fusion-builder' ),
							'param_name'  => 'field',
							'default'     => '',
							'type'        => $this->is_template_edited() ? 'text' : 'select',
							'value'       => $image_options,
						],
					],
				];
			}

			// In builder and have video options add option, on front-end add for callback availability.
			if ( ! $this->get_builder_status() || $file_options || $this->is_template_edited() ) {
				$params['acf_file'] = [
					'label'    => esc_html__( 'ACF File', 'fusion-builder' ),
					'id'       => 'acf_file',
					'group'    => esc_attr__( 'Advanced Custom Fields', 'fusion-builder' ),
					'callback' => [
						'function' => 'acf_get_file_field',
						'ajax'     => true,
					],
					'exclude'  => [ 'before', 'after', 'fallback' ],
					'options'  => array_unique( array_merge( $this->link_and_text_fields, $this->file_fields ) ),
					'fields'   => [
						'field' => [
							'heading'     => esc_html__( 'Field', 'fusion-builder' ),
							'description' => $this->is_template_edited() ? esc_html__( 'Enter field name you want to use.', 'fusion-builder' ) : esc_html__( 'Which field you want to use.', 'fusion-builder' ),
							'param_name'  => 'field',
							'default'     => '',
							'type'        => $this->is_template_edited() ? 'text' : 'select',
							'value'       => $file_options,
						],
					],
				];
			}

			// In builder and have image options add option, on front-end add for callback availability.
			if ( ! $this->get_builder_status() || $link_options || $this->is_template_edited() ) {
				$params['acf_link'] = [
					'label'    => esc_html__( 'ACF Link', 'fusion-builder' ),
					'id'       => 'acf_link',
					'group'    => esc_attr__( 'Advanced Custom Fields', 'fusion-builder' ),
					'callback' => [
						'function' => 'acf_get_link_field',
						'ajax'     => true,
					],
					'exclude'  => [ 'before', 'after', 'fallback' ],
					'options'  => array_unique( array_merge( $this->link_and_text_fields, $this->file_fields ) ),
					'fields'   => [
						'field' => [
							'heading'     => esc_html__( 'Field', 'fusion-builder' ),
							'description' => $this->is_template_edited() ? esc_html__( 'Enter field name you want to use.', 'fusion-builder' ) : esc_html__( 'Which field you want to use.', 'fusion-builder' ),
							'param_name'  => 'field',
							'default'     => '',
							'type'        => $this->is_template_edited() ? 'text' : 'select',
							'value'       => $link_options,
						],
					],
				];
			}

			// In builder and have image options add option, on front-end add for callback availability.
			if ( ! $this->get_builder_status() || $bulk_image_options || $this->is_template_edited() ) {
				$params['acf_gallery'] = [
					'label'    => esc_html__( 'ACF Gallery', 'fusion-builder' ),
					'id'       => 'acf_gallery',
					'group'    => esc_attr__( 'Advanced Custom Fields', 'fusion-builder' ),
					'options'  => [ 'multiple_upload' ],
					'exclude'  => [ 'before', 'after', 'fallback' ],
					'callback' => [
						'function' => 'acf_get_field',
						'ajax'     => true,
					],
					'fields'   => [
						'field' => [
							'heading'     => esc_html__( 'Field', 'fusion-builder' ),
							'description' => $this->is_template_edited() ? esc_html__( 'Enter field name you want to use.', 'fusion-builder' ) : esc_html__( 'Which field you want to use.', 'fusion-builder' ),
							'param_name'  => 'field',
							'default'     => '',
							'type'        => $this->is_template_edited() ? 'text' : 'select',
							'value'       => $bulk_image_options,
						],
					],
				];
			}
		}

		return $params;
	}

	/**
	 * Add WooCommerce single product fields if they exist.
	 *
	 * @since 2.1
	 * @access public
	 * @param array  $params    Params being used.
	 * @param int    $post_id   The target post id.
	 * @param string $post_type The current post type.
	 * @return array
	 */
	public function maybe_add_woo_fields( $params, $post_id, $post_type ) {

		if ( ! function_exists( 'is_product' ) ) {
			return $params;
		}

		$params['woo_update_cart'] = [
			'label'    => esc_html__( 'Update Cart', 'fusion-builder' ),
			'id'       => 'woo_update_cart',
			'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
			'options'  => $this->link_fields,
			'exclude'  => [ 'before', 'after' ],
			'callback' => [
				'function' => 'woo_get_update_cart_class',
				'ajax'     => true,
			],
		];

		if ( is_product() || 'product' === $post_type || $this->is_template_edited() || ! $this->get_builder_status() ) {
			$params['woo_price'] = [
				'label'    => esc_html__( 'Product Price', 'fusion-builder' ),
				'id'       => 'woo_price',
				'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
				'options'  => $this->text_fields,
				'default'  => wc_price( 10 ),
				'callback' => [
					'function' => 'woo_get_price',
					'ajax'     => true,
				],
				'fields'   => [
					'format' => [
						'heading'     => esc_html__( 'Format', 'fusion-builder' ),
						'description' => esc_html__( 'Format of price to display.', 'fusion-builder' ),
						'param_name'  => 'format',
						'default'     => '',
						'type'        => 'select',
						'value'       => [
							''         => esc_html__( 'Both', 'fusion-builder' ),
							'original' => esc_html__( 'Original Only', 'fusion-builder' ),
							'sale'     => esc_html__( 'Sale Only', 'fusion-builder' ),
						],
					],
				],
			];

			$params['woo_rating'] = [
				'label'    => esc_html__( 'Product Rating', 'fusion-builder' ),
				'id'       => 'woo_rating',
				'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
				'options'  => $this->text_fields,
				'default'  => '5',
				'callback' => [
					'function' => 'woo_get_rating',
					'ajax'     => true,
				],
				'fields'   => [
					'format' => [
						'heading'     => esc_html__( 'Format', 'fusion-builder' ),
						'description' => esc_html__( 'Format of rating to display.', 'fusion-builder' ),
						'param_name'  => 'format',
						'default'     => '',
						'type'        => 'select',
						'value'       => [
							''       => esc_html__( 'Average Rating', 'fusion-builder' ),
							'rating' => esc_html__( 'Rating Count', 'fusion-builder' ),
							'review' => esc_html__( 'Review Count', 'fusion-builder' ),
						],
					],
				],
			];

			$params['woo_sku'] = [
				'label'    => esc_html__( 'Product SKU', 'fusion-builder' ),
				'id'       => 'woo_sku',
				'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
				'options'  => $this->text_fields,
				'default'  => '123',
				'callback' => [
					'function' => 'woo_get_sku',
					'ajax'     => true,
				],
			];

			$params['woo_stock'] = [
				'label'    => esc_html__( 'Product Stock', 'fusion-builder' ),
				'id'       => 'woo_stock',
				'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
				'options'  => $this->text_fields,
				'default'  => '10',
				'callback' => [
					'function' => 'woo_get_stock',
					'ajax'     => true,
				],
			];

			$params['woo_gallery'] = [
				'label'    => esc_html__( 'Woo Gallery', 'fusion-builder' ),
				'id'       => 'woo_gallery',
				'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
				'options'  => [ 'multiple_upload' ],
				'exclude'  => [ 'before', 'after', 'fallback' ],
				'callback' => [
					'function' => 'woo_get_gallery',
					'ajax'     => true,
				],
			];

			$params['woo_add_to_cart'] = [
				'label'    => esc_html__( 'Add To Cart', 'fusion-builder' ),
				'id'       => 'woo_add_to_cart',
				'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
				'options'  => $this->link_fields,
				'exclude'  => [ 'before', 'after' ],
				'callback' => [
					'function' => 'woo_get_cart_link',
					'ajax'     => true,
				],
			];

			$params['woo_quick_view'] = [
				'label'    => esc_html__( 'Quick View', 'fusion-builder' ),
				'id'       => 'woo_quick_view',
				'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
				'options'  => $this->link_fields,
				'exclude'  => [ 'before', 'after' ],
				'callback' => [
					'function' => 'fusion_get_post_permalink',
					'ajax'     => true,
				],
			];

			$params['woo_category_thumbnail'] = [
				'label'    => esc_html__( 'Category Thumbnail', 'fusion-builder' ),
				'id'       => 'woo_category_thumbnail',
				'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
				'options'  => $this->image_fields,
				'exclude'  => [ 'before', 'after' ],
				'callback' => [
					'function' => 'woo_category_thumbnail',
					'ajax'     => true,
				],
			];
		}

		$params['woo_shop_page_url'] = [
			'label'    => esc_html__( 'Shop Page URL', 'fusion-builder' ),
			'id'       => 'woo_shop_page_url',
			'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
			'options'  => $this->link_fields,
			'callback' => [
				'function' => 'woo_shop_page_url',
				'ajax'     => true,
			],
		];

		$params['woo_cart_page_url'] = [
			'label'    => esc_html__( 'Cart Page URL', 'fusion-builder' ),
			'id'       => 'woo_cart_page_url',
			'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
			'options'  => $this->link_fields,
			'callback' => [
				'function' => 'woo_cart_page_url',
				'ajax'     => true,
			],
		];

		$params['woo_checkout_page_url'] = [
			'label'    => esc_html__( 'Checkout Page URL', 'fusion-builder' ),
			'id'       => 'woo_checkout_page_url',
			'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
			'options'  => $this->link_fields,
			'callback' => [
				'function' => 'woo_checkout_page_url',
				'ajax'     => true,
			],
		];

		$params['woo_myaccount_page_url'] = [
			'label'    => esc_html__( 'My Account Page URL', 'fusion-builder' ),
			'id'       => 'woo_myaccount_page_url',
			'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
			'options'  => $this->link_fields,
			'callback' => [
				'function' => 'woo_myaccount_page_url',
				'ajax'     => true,
			],
		];

		// Terms & Conditions.
		$params['woo_tnc_page_url'] = [
			'label'    => esc_html__( 'Terms & Conditions URL', 'fusion-builder' ),
			'id'       => 'woo_tnc_page_url',
			'group'    => esc_attr__( 'WooCommerce', 'fusion-builder' ),
			'options'  => $this->link_fields,
			'callback' => [
				'function' => 'woo_tnc_page_url',
				'ajax'     => true,
			],
		];

		return $params;
	}

	/**
	 * Add page title bar fields.
	 *
	 * @since 2.2
	 * @access public
	 * @param array  $params    Params being used.
	 * @param int    $post_id   The target post id.
	 * @param string $post_type The current post type.
	 * @return array
	 */
	public function maybe_add_page_title_bar_fields( $params, $post_id, $post_type ) {
		$fb_template_type = false;
		$override         = Fusion_Template_Builder()->get_override( 'page_title_bar' );
		$is_builder       = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

		if ( 'fusion_tb_section' === $post_type ) {

			// Template category is used to filter components.
			$terms = get_the_terms( $post_id, 'fusion_tb_category' );

			if ( is_array( $terms ) ) {
				$fb_template_type = $terms[0]->name;
			}
		}

		if ( ( 'fusion_tb_section' === $post_type && 'page_title_bar' === $fb_template_type ) || ( ! is_admin() && $override ) || ( fusion_doing_ajax() && isset( $_POST['fusion_load_nonce'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$params['page_title_custom_text']      = [
				'label'    => esc_html__( 'Heading', 'fusion-builder' ),
				'id'       => 'page_title_custom_text',
				'group'    => esc_attr__( 'Page Title Bar', 'fusion-builder' ),
				'options'  => $this->text_fields,
				'default'  => __( 'Your Heading Goes Here', 'fusion-builder' ),
				'callback' => [
					'function' => 'fusion_get_dynamic_heading',
					'ajax'     => false,
				],
				'fields'   => [
					'include_context' => [
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Include Context', 'fusion-builder' ),
						'description' => esc_html__( 'Whether to include title context, ie. Category: Avada.' ),
						'param_name'  => 'include_context',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
				],
			];
			$params['page_title_custom_subheader'] = [
				'label'    => esc_html__( 'Subheading', 'fusion-builder' ),
				'id'       => 'page_title_custom_subheader',
				'group'    => esc_attr__( 'Page Title Bar', 'fusion-builder' ),
				'options'  => $this->text_fields,
				'default'  => __( 'Your Subheading Goes Here', 'fusion-builder' ),
				'callback' => [
					'function' => 'fusion_get_dynamic_option',
					'ajax'     => false,
				],
			];
			$params['page_title_bg']               = [
				'label'    => esc_html__( 'Background Image', 'fusion-builder' ),
				'id'       => 'page_title_bg',
				'group'    => esc_attr__( 'Page Title Bar', 'fusion-builder' ),
				'options'  => $this->image_fields,
				'exclude'  => [ 'before', 'after' ],
				'callback' => [
					'function' => 'fusion_get_dynamic_option',
					'ajax'     => false,
				],
			];
			$params['page_title_bg_retina']        = [
				'label'    => esc_html__( 'Retina Background Image', 'fusion-builder' ),
				'id'       => 'page_title_bg_retina',
				'group'    => esc_attr__( 'Page Title Bar', 'fusion-builder' ),
				'options'  => $this->image_fields,
				'exclude'  => [ 'before', 'after' ],
				'callback' => [
					'function' => 'fusion_get_dynamic_option',
					'ajax'     => false,
				],
			];
		}

		return $params;
	}

	/**
	 * Adds hubspot actions ( only chat for now ) to dynamic data.
	 *
	 * @since 3.7.1
	 * @access public
	 * @param array  $params    The params.
	 * @param int    $post_id   The post ID.
	 * @param string $post_type The post type.
	 * @return array
	 */
	public function maybe_add_hubspot_fields( $params, $post_id, $post_type ) {

		$params['hubspot_chat'] = [
			'label'    => esc_html__( 'Open Live Chat', 'fusion-builder' ),
			'id'       => 'hubspot_chat',
			'group'    => esc_attr__( 'HubSpot', 'fusion-builder' ),
			'options'  => $this->link_fields,
			'exclude'  => [ 'before', 'after', 'fallback' ],
			'callback' => [
				'function' => 'fusion_open_hubspot_chat',
				'ajax'     => false,
			],
		];

		return $params;
	}
}

<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_post_card_image' ) ) {

	if ( ! class_exists( 'FusionSC_PostCardImage' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionSC_PostCardImage extends Fusion_Element {

			/**
			 * The counter.
			 *
			 * @access private
			 * @since 3.3
			 * @var int
			 */
			public $element_counter = 1;

			/**
			 * Whether styles are already generated or not.
			 *
			 * @access protected
			 * @since 3.3
			 * @var bool
			 */
			protected $styles_generated = false;

			/**
			 * An array of generated CSS rules.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $element_css = [];

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.3
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_post-card-image', [ $this, 'attr' ] );
				add_shortcode( 'fusion_post_card_image', [ $this, 'render' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_post_card_image', [ $this, 'ajax_render' ] );

				// Add generated CSS rules to parent post card.
				add_filter( 'fusion_post_cards_elements_css', [ $this, 'append_and_clear_element_css' ] );

				add_action( 'fusion_post_card_rendered', [ $this, 'parent_post_card_rendered' ] );

				add_action( 'fusion_post_cards_rendered', [ $this, 'parent_post_cards_rendered' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'hide_on_mobile'             => fusion_builder_default_visibility( 'string' ),
					'class'                      => '',
					'id'                         => '',
					'layout'                     => 'static',
					'show_title'                 => 'yes',
					'show_buttons'               => 'yes',
					'show_cats'                  => 'yes',
					'show_nav'                   => 'yes',
					'show_price'                 => 'yes',
					'show_rating'                => 'yes',
					'show_sale'                  => 'no',
					'show_outofstock'            => 'no',
					'image_link'                 => 'yes',
					'image_link_target'          => '_self',
					'image_link_custom'          => '',

					// Margins.
					'margin_bottom'              => '',
					'margin_left'                => '',
					'margin_right'               => '',
					'margin_top'                 => '',

					// Border Radius.
					'border_radius_top_left'     => '',
					'border_radius_top_right'    => '',
					'border_radius_bottom_right' => '',
					'border_radius_bottom_left'  => '',

					'crossfade_bg_color'         => '',

					// aspect ratio.
					'aspect_ratio'               => '',
					'custom_aspect_ratio'        => '',
					'aspect_ratio_position'      => '',

					// Animation.
					'animation_type'             => '',
					'animation_direction'        => 'down',
					'animation_speed'            => '0.1',
					'animation_offset'           => $fusion_settings->get( 'animation_offset' ),
				];
			}

			/**
			 * Render for live editor.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_render( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				$return_data = [];
				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					global $post;
					$args           = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$post_id        = isset( $_POST['post_id'] ) ? $_POST['post_id'] : get_the_ID(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					$this->defaults = self::get_element_defaults();
					$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_post_card_image' );

					// Check if dynamic source is a term and if so emulate.
					if ( isset( $_POST['fusion_meta'] ) ) {
						$meta = fusion_string_to_array( wp_unslash( $_POST['fusion_meta'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						if ( isset( $meta['_fusion']['dynamic_content_preview_type'] ) && 'term' === $meta['_fusion']['dynamic_content_preview_type'] && isset( $meta['_fusion']['preview_term'] ) && '' !== $meta['_fusion']['preview_term'] ) {
							$GLOBALS['wp_query']->is_tax         = true;
							$GLOBALS['wp_query']->is_archive     = true;
							$GLOBALS['wp_query']->queried_object = get_term_by( 'id', $post_id, (string) $meta['_fusion']['preview_term'] );
						}
					}

					if ( $post_id ) {
						$post = get_post( $post_id );
					}

					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );

					global $product;
					if ( function_exists( 'wc_get_product' ) && is_null( $product ) ) {
						$product = wc_get_product( $post_id );
					}

					$return_data['fusion_post_card_image'] = $this->get_post_card_image_content( $post_id );
				}

				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Render the shortcode.
			 *
			 * @access public
			 * @since 3.3
			 * @param  array  $args   Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string         HTML output.
			 */
			public function render( $args, $content = '' ) {
				$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
				$html       = '';

				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_post_card_image' );

				$image = $this->get_post_card_image_content( get_the_ID() );

				$this->generate_styles();

				// Add styles to output if in Live Editor.
				if ( $is_builder || isset( $_GET['awb-studio-post-card'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$html .= $this->get_styles();
				}

				$html .= '<div ' . FusionBuilder::attributes( 'post-card-image' ) . '>' . $image . '</div>';

				$this->on_render();

				$this->element_counter++;

				return apply_filters( 'fusion_element_post_card_image_content', $html, $args );

			}

			/**
			 * Set necesarry stuff after the first post card has rendered.
			 * For example, counter needs to be reset and also we need to generate styles only once.
			 */
			public function parent_post_card_rendered() {
				$this->element_counter = 1;

				if ( ! $this->styles_generated ) {
					$this->styles_generated = true;
				}
			}

			/**
			 * Set necesarry stuff after the post cards element has rendered.
			 */
			public function parent_post_cards_rendered() {
				$this->styles_generated = false;
			}

			/**
			 * Builds HTML for Post Card Image.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @param  int $post_id Post ID.
			 * @return string
			 */
			public function get_post_card_image_content( $post_id = '' ) {

				if ( ! $post_id ) {
					$post_id = get_the_ID();
				}

				$this->args['in_cart'] = fusion_library()->woocommerce->is_product_in_cart( $post_id );

				$featured_image_size = 'full';

				fusion_library()->images->set_grid_image_meta(
					[
						'layout'       => 'grid',
						'columns'      => FusionBuilder()->post_card_data['columns'],
						'gutter_width' => FusionBuilder()->post_card_data['column_spacing'],
					]
				);

				$image_args = [
					'post_id'                   => $post_id,
					'post_featured_image_size'  => $featured_image_size,
					'post_permalink'            => get_permalink( $post_id ),
					'display_placeholder_image' => 'rollover' !== $this->args['layout'] ? true : false,
					'display_woo_sale'          => 'yes' === $this->args['show_sale'],
					'display_woo_outofstock'    => 'yes' === $this->args['show_outofstock'],
					'display_woo_price'         => 'yes' === $this->args['show_price'],
					'display_woo_buttons'       => 'yes' === $this->args['show_buttons'],
					'display_woo_rating'        => 'yes' === $this->args['show_rating'],
					'display_post_categories'   => 'yes' === $this->args['show_cats'] ? 'enable' : 'disable',
					'display_post_title'        => 'yes' === $this->args['show_title'] ? 'enable' : 'disable',
					'display_rollover'          => 'rollover' === $this->args['layout'] ? 'yes' : 'no',
					'image_link'                => 'no' !== $this->args['image_link'] ? true : false,
				];

				if ( is_tax() ) {
					$term_link = get_term_link( get_queried_object() );
					if ( ! is_wp_error( $term_link ) ) {
						$image_args['post_permalink']          = $term_link;
						$image_args['post_id']                 = get_queried_object()->term_id;
						$image_args['type']                    = 'taxonomy';
						$image_args['display_post_categories'] = 'disable';
					}
				}

				if ( 'custom' === $this->args['image_link'] ) {
					$image_args['post_permalink'] = $this->args['image_link_custom'];
				}

				if ( 'no' !== $this->args['image_link'] && '_blank' === $this->args['image_link_target'] ) {
					add_filter( 'fusion_builder_post_links_target', [ $this, 'set_rollover_image_link_target' ], 11, 2 );
				}

				// Add necessary class for image variation changes.
				if ( 'product' === get_post_type( $post_id ) ) {
					$image_args['attributes'] = [
						'class' => 'woocommerce-product-gallery__image',
					];

					add_filter( 'wp_get_attachment_image_attributes', [ $this, 'add_product_image_attr' ], 10, 3 );
					add_filter( 'awb_crossfade_image_classes', [ $this, 'add_to_crossfade_attr' ], 10, 2 );
				}

				if ( 'crossfade' !== $this->args['layout'] || is_tax() ) {
					$image = avada_first_featured_image_markup( $image_args );
				} else {
					$image = $this->crossfade_render( $post_id );
				}

				if ( 'no' !== $this->args['image_link'] && '_blank' === $this->args['image_link_target'] ) {
					remove_filter( 'fusion_builder_post_links_target', [ $this, 'set_rollover_image_link_target' ], 11 );
				}

				if ( 'product' === get_post_type( $post_id ) ) {
					remove_filter( 'wp_get_attachment_image_attributes', [ $this, 'add_product_image_attr' ], 10, 3 );
					remove_filter( 'awb_crossfade_image_classes', [ $this, 'add_to_crossfade_attr' ], 10, 2 );
				}

				fusion_library()->images->set_grid_image_meta( [] );

				return $image;
			}

			/**
			 * Add data attribute to product image.
			 *
			 * @access public
			 * @since 3.8
			 * @param array  $attr       The attributes array.
			 * @param array  $attachment The attachment data.
			 * @param string $size       The size.
			 * @return array
			 */
			public function add_product_image_attr( $attr, $attachment, $size ) {
				$full_size = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
				$full_src  = wp_get_attachment_image_src( $attachment->ID, $full_size );

				$attr['data-caption']            = _wp_specialchars( get_post_field( 'post_excerpt', $attachment->ID ), ENT_QUOTES, 'UTF-8', true );
				$attr['data-src']                = esc_url( $full_src[0] );
				$attr['data-large_image']        = esc_url( $full_src[0] );
				$attr['data-large_image_width']  = esc_attr( $full_src[1] );
				$attr['data-large_image_height'] = esc_attr( $full_src[2] );
				return $attr;
			}

			/**
			 * Add data attribute to product image.
			 *
			 * @access public
			 * @since 3.8
			 * @param array $classes   The classes array.
			 * @param array $attachment The attachment data.
			 * @return array
			 */
			public function add_to_crossfade_attr( $classes, $attachment ) {
				$classes[] = 'woocommerce-product-gallery__image';
				return $classes;
			}

			/**
			 * Change link target if necessary.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @param string $target   The rollover target.
			 * @param int    $post_id  Post ID.
			 * @return string
			 */
			public function set_rollover_image_link_target( $target, $post_id ) {

				// If post has link set in PO then use target from PO as well.
				if ( fusion_get_page_option( 'link_icon_url', $post_id ) ) {
					return $target;
				}

				return 'yes';
			}

			/**
			 * Get markup for crossfade image.
			 *
			 * @access public
			 * @since 3.3
			 * @param  int $post_id Post ID.
			 * @return string
			 */
			public function crossfade_render( $post_id ) {
				global $product;

				$woo_badges = '';
				$image      = '';
				$link_open  = '';
				$link_close = '';

				if ( is_object( $product ) ) {
					ob_start();
					if ( 'yes' === $this->args['show_outofstock'] && method_exists( $product, 'is_in_stock' ) ) {
						get_template_part( 'templates/wc-product-loop-outofstock-flash' );
					}

					if ( 'yes' === $this->args['show_sale'] && function_exists( 'woocommerce_show_product_sale_flash' ) ) {
						woocommerce_show_product_sale_flash();
					}
					$woo_badges = ob_get_clean();

					if ( '' !== $woo_badges ) {
						$woo_badges = '<div class="fusion-woo-badges-wrapper">' . $woo_badges . '</div>';
					}
				}

				ob_start();
				get_template_part( 'templates/wc-thumbnail-classic' );
				$image = ob_get_clean();

				// Tax source doesn't use crossfade layout.
				if ( 'no' !== $this->args['image_link'] ) {
					$link          = 'yes' === $this->args['image_link'] ? get_the_permalink() : $this->args['image_link_custom'];
					$link_icon_url = apply_filters( 'fusion_builder_link_icon_url', '', $post_id );
					$link          = '' !== $link_icon_url ? $link_icon_url : $link;

					$post_links_target = apply_filters( 'fusion_builder_post_links_target', '', $post_id );
					$link_target       = 'yes' === $post_links_target ? ' target="_blank"' : '';

					$link_open  = '<a href="' . esc_url_raw( $link ) . '" title="' . esc_attr( get_the_title() ) . '"' . $link_target . '>';
					$link_close = '</a>';
				}

				return $woo_badges . $link_open . $image . $link_close;
			}

			/**
			 * Builds the array of atributes.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function attr() {
				$fusion_settings = awb_get_fusion_settings();

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class'       => 'fusion-' . $fusion_settings->get( 'woocommerce_product_box_design', false, 'classic' ) . '-product-image-wrapper fusion-woo-product-image fusion-post-card-image fusion-post-card-image-' . $this->element_counter,
						'data-layout' => $this->args['layout'],
					]
				);

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( $this->args['in_cart'] ) {
					$attr['class'] .= ' fusion-item-in-cart';
				}

				if ( 'rollover' === $this->args['layout'] && 'no' === $this->args['image_link'] ) {
					$attr['class'] .= ' fusion-disable-link';
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( 'crossfade' === $this->args['layout'] ) {
					$attr['class'] .= ' product-images';
				}

				if ( '' !== $this->args['aspect_ratio'] ) {
					$attr['class'] .= ' has-aspect-ratio';
				}

				// Add necessary class for image variation changes.
				if ( 'product' === get_post_type( get_the_ID() ) ) {
					$attr['class'] .= ' images';
				}

				return $attr;

			}

			/**
			 * Generate CSS styles.
			 *
			 * @access protected
			 * @since 3.3
			 * @return void
			 */
			protected function generate_styles() {

				if ( $this->styles_generated ) {
					return;
				}
				$this->base_selector = '.fusion-post-card-image-' . $this->element_counter;

				$sides = [ 'top', 'right', 'bottom', 'left' ];

				// Margins.
				foreach ( $sides as $side ) {

					// Element margin.
					$margin_name = 'margin_' . $side;

					if ( '' !== $this->args[ $margin_name ] ) {
						$this->element_css[] = [
							'selector'  => $this->base_selector,
							'rule'      => 'margin-' . $side,
							'value'     => fusion_library()->sanitize->get_value_with_unit( $this->args[ $margin_name ] ),
							'important' => false,
						];
					}
				}

				if ( ! $this->is_default( 'crossfade_bg_color' ) ) {
					$this->element_css[] = [
						'selector'  => $this->base_selector . ' .crossfade-images',
						'rule'      => 'background-color',
						'value'     => fusion_library()->sanitize->color( $this->args['crossfade_bg_color'] ),
						'important' => false,
					];
				}

				// Border radius.
				if ( ! $this->is_default( 'border_radius_top_left' ) ) {
					$this->element_css[] = [
						'selector'  => $this->base_selector,
						'rule'      => 'border-top-left-radius',
						'value'     => fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_top_left'] ),
						'important' => false,
					];
				}

				if ( ! $this->is_default( 'border_radius_top_right' ) ) {
					$this->element_css[] = [
						'selector'  => $this->base_selector,
						'rule'      => 'border-top-right-radius',
						'value'     => fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_top_right'] ),
						'important' => false,
					];
				}

				if ( ! $this->is_default( 'border_radius_bottom_right' ) ) {
					$this->element_css[] = [
						'selector'  => $this->base_selector,
						'rule'      => 'border-bottom-right-radius',
						'value'     => fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_bottom_right'] ),
						'important' => false,
					];
				}

				if ( ! $this->is_default( 'border_radius_bottom_left' ) ) {
					$this->element_css[] = [
						'selector'  => $this->base_selector,
						'rule'      => 'border-bottom-left-radius',
						'value'     => fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_bottom_left'] ),
						'important' => false,
					];
				}

				$this->generate_aspect_ratio_styles();
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.3
			 * @return string
			 */
			protected function get_styles() {
				$this->dynamic_css = [];

				foreach ( $this->element_css as $rule ) {
					$this->add_css_property( $rule['selector'], $rule['rule'], $rule['value'], $rule['important'] );
				}
				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Filter callback, appends generated CSS rules and resets used vars.
			 *
			 * @access public
			 * @since 3.3
			 * @param  array $css  CSS rules.
			 * @return array
			 */
			public function append_and_clear_element_css( $css ) {

				// Append generated CSS.
				if ( ! empty( $this->element_css ) ) {
					array_push( $css, $this->element_css );
				}

				// Reset.
				$this->element_css      = [];
				$this->styles_generated = false;

				return $css;
			}

			/**
			 * Generate aspect ratio styles.
			 *
			 * @access public
			 * @since 3.7
			 * @return string CSS output.
			 */
			public function generate_aspect_ratio_styles() {
				if ( '' === $this->args['aspect_ratio'] ) {
					return '';
				}

				$this->dynamic_css = [];
				$selector          = '.fusion-post-card-image-' . $this->element_counter . '.has-aspect-ratio img';

				// Calc Ratio.
				if ( 'custom' === $this->args['aspect_ratio'] && '' !== $this->args['custom_aspect_ratio'] ) {
					$this->element_css[] = [
						'selector'  => $selector,
						'rule'      => 'aspect-ratio',
						'value'     => '100 / ' . $this->args['custom_aspect_ratio'],
						'important' => false,
					];
				} else {
					$aspect_ratio = explode( '-', $this->args['aspect_ratio'] );
					$width        = isset( $aspect_ratio[0] ) ? $aspect_ratio[0] : '';
					$height       = isset( $aspect_ratio[1] ) ? $aspect_ratio[1] : '';

					$this->element_css[] = [
						'selector'  => $selector,
						'rule'      => 'aspect-ratio',
						'value'     => "$width / $height",
						'important' => false,
					];

				}

				// Set Image Position.
				if ( '' !== $this->args['aspect_ratio_position'] ) {
					$this->element_css[] = [
						'selector'  => $selector,
						'rule'      => 'object-position',
						'value'     => $this->args['aspect_ratio_position'],
						'important' => false,
					];
				}
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function on_first_render() {
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/post-card-image.min.css' );
			}
		}
	}

	new FusionSC_PostCardImage();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 3.3
 */
function fusion_element_post_card_image() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_PostCardImage',
			[
				'name'      => esc_attr__( 'Post Card Image', 'fusion-builder' ),
				'shortcode' => 'fusion_post_card_image',
				'icon'      => 'fusiona-post-cards-image',
				'templates' => [ 'post_cards' ],
				'component' => true,
				'help_url'  => 'https://theme-fusion.com/documentation/avada/elements/post-card-image-element/',
				'params'    => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose image layout.', 'fusion-builder' ),
						'param_name'  => 'layout',
						'value'       => [
							'static'    => esc_attr__( 'Static', 'fusion-builder' ),
							'crossfade' => esc_attr__( 'Crossfade', 'fusion-builder' ),
							'rollover'  => esc_attr__( 'Rollover', 'fusion-builder' ),
						],
						'default'     => 'static',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_post_card_image',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Image Aspect Ratio', 'fusion-builder' ),
						'description' => esc_attr__( 'Select an aspect ratio for the image.', 'fusion-builder' ),
						'param_name'  => 'aspect_ratio',
						'value'       => [
							''       => esc_attr__( 'Automatic', 'fusion-builder' ),
							'1-1'    => esc_attr__( '1:1', 'fusion-builder' ),
							'2-1'    => esc_attr__( '2:1', 'fusion-builder' ),
							'2-3'    => esc_attr__( '2:3', 'fusion-builder' ),
							'3-1'    => esc_attr__( '3:1', 'fusion-builder' ),
							'3-2'    => esc_attr__( '3:2', 'fusion-builder' ),
							'4-1'    => esc_attr__( '4:1', 'fusion-builder' ),
							'4-3'    => esc_attr__( '4:3', 'fusion-builder' ),
							'5-4'    => esc_attr__( '5:4', 'fusion-builder' ),
							'16-9'   => esc_attr__( '16:9', 'fusion-builder' ),
							'9-16'   => esc_attr__( '9:16', 'fusion-builder' ),
							'21-9'   => esc_attr__( '21:9', 'fusion-builder' ),
							'9-21'   => esc_attr__( '9:21', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Custom Aspect Ratio', 'fusion-builder' ),
						'description' => esc_attr__( 'Set a custom aspect ratio for the image.', 'fusion-builder' ),
						'param_name'  => 'custom_aspect_ratio',
						'min'         => 1,
						'max'         => 500,
						'value'       => 100,
						'dependency'  => [
							[
								'element'  => 'aspect_ratio',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'image_focus_point',
						'heading'     => esc_attr__( 'Image Focus Point', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the image focus point by dragging the blue dot.', 'fusion-builder' ),
						'param_name'  => 'aspect_ratio_position',
						'image'       => 'element_content',
						'image_id'    => 'image_id',
						'dependency'  => [
							[
								'element'  => 'aspect_ratio',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Title', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide the title.', 'fusion-builder' ),
						'param_name'  => 'show_title',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'rollover',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_post_card_image',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Categories', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide the categories.', 'fusion-builder' ),
						'param_name'  => 'show_cats',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'rollover',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_post_card_image',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Price', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide the price. Applies only to WooCommerce products.', 'fusion-builder' ),
						'param_name'  => 'show_price',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'rollover',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_post_card_image',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Rating', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide the rating. Applies only to WooCommerce products.', 'fusion-builder' ),
						'param_name'  => 'show_rating',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'rollover',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_post_card_image',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Sale Badge', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide the sale badge. Applies only to WooCommerce products.', 'fusion-builder' ),
						'param_name'  => 'show_sale',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_post_card_image',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Out Of Stock Badge', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide the out of stock badge. Applies only to WooCommerce products.', 'fusion-builder' ),
						'param_name'  => 'show_outofstock',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_post_card_image',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Buttons', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide Add to Cart / Details buttons on the rollover. Applies only to WooCommerce products.', 'fusion-builder' ),
						'param_name'  => 'show_buttons',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'rollover',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_post_card_image',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Image', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to link image to default or custom link or disable link completely.', 'fusion-builder' ),
						'param_name'  => 'image_link',
						'value'       => [
							'yes'    => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'     => esc_attr__( 'No', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
						'description' => __( '_self = open in same window.<br />_blank = open in new window.', 'fusion-builder' ),
						'param_name'  => 'image_link_target',
						'value'       => [
							'_self'  => esc_attr__( '_self', 'fusion-builder' ),
							'_blank' => esc_attr__( '_blank', 'fusion-builder' ),
						],
						'default'     => '_self',
						'dependency'  => [
							[
								'element'  => 'image_link',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Custom Link', 'fusion-builder' ),
						'description'  => esc_attr__( 'Choos to link image to default or custom link or disable link completely.', 'fusion-builder' ),
						'param_name'   => 'image_link_custom',
						'value'        => '',
						'dynamic_data' => true,
						'dependency'   => [
							[
								'element'  => 'image_link',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'margin',
						'value'            => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Border Radius', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'border_radius',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'border_radius_top_left'     => '',
							'border_radius_top_right'    => '',
							'border_radius_bottom_right' => '',
							'border_radius_bottom_left'  => '',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Crossfade Background Color', 'fusion-builder' ),
						/* translators: URL for the link. */
						'description' => esc_attr__( 'Set the background of crossfade image container', 'fusion-builder' ),
						'param_name'  => 'crossfade_bg_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'title_border_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'crossfade',
								'operator' => '==',
							],
						],
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-post-card-image',
					],
				],
				'callback'  => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_post_card_image',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'wp_loaded', 'fusion_element_post_card_image' );

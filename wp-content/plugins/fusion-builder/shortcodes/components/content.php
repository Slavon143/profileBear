<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_content' ) ) {

	if ( ! class_exists( 'FusionTB_Content' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 2.2
		 */
		class FusionTB_Content extends Fusion_Component {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 2.2
			 * @var array
			 */
			protected $args;

			/**
			 * An array of the different status checks.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $status;

			/**
			 * Backup array of the different status checks for nested content elements..
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $backup_status = [];

			/**
			 * Have we paused live editor filters.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $paused_filtering = false;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 2.2
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 2.2
			 */
			public function __construct() {
				parent::__construct( 'fusion_tb_content' );
				add_filter( 'fusion_attr_fusion_tb_content-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_content', [ $this, 'ajax_query' ] );
			}


			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 2.2
			 * @return boolean
			 */
			public function should_render() {
				return is_singular() || ( fusion_doing_ajax() && isset( $_POST['action'] ) && 'get_fusion_post_cards' === $_POST['action'] ) || $this->status['post_card_rendering']; // phpcs:ignore WordPress.Security
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'margin_bottom'                 => '',
					'margin_left'                   => '',
					'margin_right'                  => '',
					'margin_top'                    => '',
					'hide_on_mobile'                => fusion_builder_default_visibility( 'string' ),
					'class'                         => '',
					'id'                            => '',
					'animation_type'                => '',
					'animation_direction'           => 'down',
					'animation_speed'               => '0.1',
					'animation_offset'              => $fusion_settings->get( 'animation_offset' ),
					'excerpt'                       => 'no',
					'excerpt_length'                => '55',
					'strip_html'                    => 'yes',

					// 3.3 additions.
					'content_alignment'             => '',
					'font_size'                     => '',
					'fusion_font_family_text_font'  => '',
					'fusion_font_variant_text_font' => '',
					'line_height'                   => '',
					'letter_spacing'                => '',
					'text_color'                    => '',
					'text_transform'                => '',
				];
			}

			/**
			 * Get the markup data for live editor on option change.
			 *
			 * @access public
			 * @since 3.3
			 * @return void.
			 */
			public function ajax_query() {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$args           = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$post_id        = isset( $_POST['post_id'] ) ? $_POST['post_id'] : get_the_ID(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					$this->defaults = self::get_element_defaults();
					$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_content' );
					$return_data    = [];

					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );

					// Ensure ajax column CSS does not conflict.
					$cid = isset( $_POST['cid'] ) ? sanitize_key( wp_unslash( $_POST['cid'] ) ) : $post_id;
					FusionBuilder()->set_global_shortcode_parent( $cid );

					if ( -99 === (int) $post_id ) {
						$dummy_post = Fusion_Dummy_Post::get_dummy_post();
						$content    = [
							'full_content'     => apply_filters( 'the_content', $dummy_post->post_content ),
							'excerpt_stripped' => $dummy_post->post_excerpt,
							'excerpt'          => $dummy_post->post_excerpt,
							'read_more'        => '',
							'excerpt_base'     => fusion_get_option( 'excerpt_base' ),
						];
					} else {
						global $post;

						$this->emulate_post();

						$content = fusion_get_content_data();

						if ( has_excerpt( $post_id ) ) {
							$content['has_custom_excerpt'] = true;
						} else {
							$content['has_custom_excerpt'] = false;
						}

						$this->restore_post();
					}

					$return_data['content'] = $content;

					echo wp_json_encode( $return_data );
					wp_die();
				}
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 2.2
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				global $global_column_array, $global_column_inner_array, $global_container_count;

				$this->params   = $args;
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $this->params, 'fusion_tb_content' );

				$this->set_status();

				// No recursion.
				if ( ! $this->status['editing_post_card'] && $this->status['target_post'] && $this->status['page_id'] === $this->status['target_post']->ID ) {
					$dummy_post = Fusion_Dummy_Post::get_dummy_post();
					return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $dummy_post->post_content, $args );
				}

				// Don't render on archive pages when not within post card.
				if ( false === $this->status['target_post'] && ! $this->should_render() ) {
					return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', '', $args );
				}

				// Save backups of the globals and then reset.
				$template_global_column_array       = $global_column_array;
				$template_global_column_inner_array = $global_column_inner_array;

				$global_column_array       = [];
				$global_column_inner_array = [];

				// Backup global container count, will rerun scoped for nested.
				$template_global_container_count = $global_container_count;
				$global_container_count          = false;

				$this->pre_render();
				do_action( 'fusion_content_pre_render' );

				// Emulate post if it is studio preview.
				if ( isset( $_GET['awb-studio-content'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$this->emulate_post();
				}

				// Full content, retrieve like we did before, no emulation needed.
				if ( 'no' === $this->args['excerpt'] ) {
					$content = false !== $this->status['target_post'] ? $this->status['target_post']->post_content : get_the_content();
					$content = apply_filters( 'the_content', $content );
					$content = str_replace( ']]>', ']]&gt;', $content );

				} else {

					// We want excerpt, emulate target post if needed.
					if ( false !== $this->status['target_post'] ) {
						$this->emulate_post();
					}

					// Get excerpt content.
					$content = fusion_builder_get_post_content( '', $this->args['excerpt'], $this->args['excerpt_length'], $this->args['strip_html'] );

					// Content retrieved, restore post if needed.
					if ( false !== $this->status['target_post'] ) {
						$this->restore_post();
					}
				}

				// Restore post if it is studio preview.
				if ( isset( $_GET['awb-studio-content'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$this->restore_post();
				}

				$this->post_render();

				do_action( 'fusion_content_post_render' );

				$global_column_array       = $template_global_column_array;
				$global_column_inner_array = $template_global_column_inner_array;
				$global_container_count    = $template_global_container_count;

				$content = '<div ' . FusionBuilder::attributes( 'fusion_tb_content-shortcode' ) . '>' . $content . $this->get_styles() . '</div>';

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $content, $args );
			}
				/**
				 * Get the styles.
				 *
				 * @access protected
				 * @since 3.0
				 * @return string
				 */
			protected function get_styles() {
				$this->base_selector = '.fusion-content-tb-' . $this->counter;
				$this->dynamic_css   = [];

				if ( ! $this->is_default( 'content_alignment' ) ) {
					$this->add_css_property( $this->base_selector, 'text-align', $this->args['content_alignment'] );
				}

				if ( ! $this->is_default( 'font_size' ) ) {
					$this->add_css_property( $this->base_selector, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['font_size'] ) );
				}

				// Font family and weight.
				$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'text_font', 'array' );
				foreach ( $text_styles as $rule => $value ) {
					$this->add_css_property( $this->base_selector, $rule, $value );
				}

				if ( ! $this->is_default( 'line_height' ) ) {
					$this->add_css_property( $this->base_selector, 'line-height', $this->args['line_height'] );
				}

				if ( ! $this->is_default( 'letter_spacing' ) ) {
					$this->add_css_property( $this->base_selector, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'text_transform' ) ) {
					$this->add_css_property( $this->base_selector, 'text-transform', $this->args['text_transform'] );
				}

				if ( ! $this->is_default( 'text_color' ) ) {
					$this->add_css_property( $this->base_selector, 'color', fusion_library()->sanitize->color( $this->args['text_color'] ) );
				}

				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Change filtering for live editor.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function pre_render() {

				// This content element is within a post card, turn rendering to false for nested elements for content.
				if ( $this->status['post_card_rendering'] ) {
					FusionBuilder()->post_card_data['is_rendering'] = false;
				}

				// We are in builder and not rendering post cards element.
				if ( $this->status['is_builder'] && ( ! $this->status['post_card_rendering'] || $this->status['editing_post_card'] ) ) {
					if ( false !== $this->status['target_post'] ) {
						do_action( 'fusion_pause_live_editor_filter' );
						$this->paused_filtering = true;
					} elseif ( false === $this->status['target_post'] ) {
						do_action( 'fusion_resume_live_editor_filter' );
					}
				}
			}

			/**
			 * Change filtering for live editor.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function post_render() {
				if ( ! empty( $this->backup_status ) ) {
					$this->status        = $this->backup_status;
					$this->backup_status = [];
				}

				// We are within post card, switch it back for later elements in post card.
				if ( $this->status['post_card_rendering'] ) {
					FusionBuilder()->post_card_data['is_rendering'] = true;
				}

				// We are in builder and not rendering post cards element.
				if ( $this->status['is_builder'] && ( ! $this->status['post_card_rendering'] || $this->status['editing_post_card'] ) ) {
					if ( $this->paused_filtering && false !== $this->status['target_post'] ) {
						do_action( 'fusion_resume_live_editor_filter' );
					} elseif ( false === $this->status['target_post'] ) {
						do_action( 'fusion_pause_live_editor_filter' );
					}
				}
			}

			/**
			 * Collect status of current request.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function set_status() {
				if ( empty( $this->backup_status ) ) {
					$this->backup_status = $this->status;
				}
				$this->status = [

					// Current page.
					'page_id'             => get_the_ID(),

					// Live editor is active.
					'is_builder'          => false,

					// Post cards element is rendering or single post card.
					'post_card_rendering' => FusionBuilder()->post_card_data['is_rendering'],

					// Live edit single post card.
					'editing_post_card'   => false,

					// Emulated target post.
					'target_post'         => false,

					// We are fetching post cards element markup.
					'live_ajax'           => fusion_doing_ajax() && isset( $_POST['action'] ) && 'get_fusion_post_cards' === $_POST['action'], // phpcs:ignore WordPress.Security
				];

				$this->status['is_builder'] = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

				// We are builder page load.
				if ( $this->status['is_builder'] && ! $this->status['live_ajax'] ) {
					$builder                           = Fusion_Builder_Front::get_instance();
					$this->status['editing_post_card'] = FusionBuilder()->editing_post_card;
					$this->status['target_post']       = $this->get_target_post();
				}

				if ( $this->status['live_ajax'] ) {
					$this->status['post_card_rendering'] = true;
				}
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'fusion-content-tb fusion-content-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}
		}
	}

	new FusionTB_Content();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 2.2
 */
function fusion_component_content() {

	$fusion_settings = awb_get_fusion_settings();

	$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
	$to_link    = '';

	if ( $is_builder ) {
		$to_link = '<span class="fusion-panel-shortcut" data-fusion-option="body_typography_important_note_info">' . esc_html__( 'Global Options Body Typography Settings', 'fusion-builder' ) . '</span>';
	} else {
		$to_link = '<a href="' . esc_url( $fusion_settings->get_setting_link( 'headers_typography_important_note_info' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Global Options Body Typography Settings', 'fusion-builder' ) . '</a>';
	}

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Content',
			[
				'name'                    => esc_attr__( 'Content', 'fusion-builder' ),
				'shortcode'               => 'fusion_tb_content',
				'icon'                    => 'fusiona-content',
				'component'               => true,
				'templates'               => [ 'content', 'post_cards' ],
				'components_per_template' => 1,
				'subparam_map'            => [
					'fusion_font_family_text_font'  => 'main_typography',
					'fusion_font_variant_text_font' => 'main_typography',
					'font_size'                     => 'main_typography',
					'line_height'                   => 'main_typography',
					'letter_spacing'                => 'main_typography',
					'text_transform'                => 'main_typography',
					'color'                         => 'main_typography',
				],
				'params'                  => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Content Display', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the blog post content is displayed as excerpt, full content or is completely disabled.', 'fusion-builder' ),
						'param_name'  => 'excerpt',
						'value'       => [
							'yes' => esc_attr__( 'Excerpt', 'fusion-builder' ),
							'no'  => esc_attr__( 'Full Content', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Excerpt Length', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert the number of words/characters you want to show in the excerpt.', 'fusion-builder' ),
						'param_name'  => 'excerpt_length',
						'value'       => '55',
						'min'         => '0',
						'max'         => '500',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'excerpt',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Strip HTML from Posts Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to strip HTML from the post content.', 'fusion-builder' ),
						'param_name'  => 'strip_html',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'excerpt',
								'value'    => 'yes',
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
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'margin',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the text alignment.', 'fusion-builder' ),
						'param_name'  => 'content_alignment',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => '',
						'value'       => [
							''        => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'    => esc_attr__( 'Left', 'fusion-builder' ),
							'center'  => esc_attr__( 'Center', 'fusion-builder' ),
							'right'   => esc_attr__( 'Right', 'fusion-builder' ),
							'justify' => esc_attr__( 'Justify', 'fusion-builder' ),
						],
					],
					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Typography', 'fusion-builder' ),
						/* translators: URL for the link. */
						'description'      => sprintf( esc_html__( 'Controls the title text typography.  Leave empty if the global typography for the corresponding heading size (h1-h6) should be used: %s.', 'fusion-builder' ), $to_link ),
						'param_name'       => 'main_typography',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'text_font',
							'font-size'      => 'font_size',
							'line-height'    => 'line_height',
							'letter-spacing' => 'letter_spacing',
							'text-transform' => 'text_transform',
							'color'          => 'text_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => 'none',
							'color'          => $fusion_settings->get( 'body_typography', 'color' ),
						],
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-content-tb',
					],
				],
				'callback'                => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_content',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_content' );

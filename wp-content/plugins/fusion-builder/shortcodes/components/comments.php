<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_comments' ) ) {

	if ( ! class_exists( 'FusionTB_Comments' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 2.2
		 */
		class FusionTB_Comments extends Fusion_Component {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 2.2
			 * @var array
			 */
			protected $args;

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
				parent::__construct( 'fusion_tb_comments' );
				add_filter( 'fusion_attr_fusion_tb_comments-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_' . $this->shortcode_handle, [ $this, 'ajax_render' ] );
			}

			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 2.2
			 * @return boolean
			 */
			public function should_render() {
				return is_singular();
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
					'headings'            => 'show',
					'heading_size'        => '2',
					'heading_color'       => '',
					'border_size'         => $fusion_settings->get( 'separator_border_size' ),
					'border_color'        => $fusion_settings->get( 'sep_color' ),
					'link_color'          => $fusion_settings->get( 'link_color' ),
					'link_hover_color'    => $fusion_settings->get( 'primary_color' ),
					'text_color'          => $fusion_settings->get( 'body_typography', 'color' ),
					'meta_color'          => $fusion_settings->get( 'body_typography', 'color' ),
					'avatar'              => 'square',
					'padding'             => '40',
					'margin_bottom'       => '',
					'margin_left'         => '',
					'margin_right'        => '',
					'margin_top'          => '',
					'template_order'      => 'comments,comment_form',
					'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
					'class'               => '',
					'id'                  => '',
					'animation_type'      => '',
					'animation_direction' => 'down',
					'animation_speed'     => '0.1',
					'animation_offset'    => $fusion_settings->get( 'animation_offset' ),
				];
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public static function get_element_extras() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'title_margin'       => $fusion_settings->get( 'title_margin' ),
					'title_border_color' => $fusion_settings->get( 'title_border_color' ),
					'title_style_type'   => $fusion_settings->get( 'title_style_type' ),
				];
			}

			/**
			 * Maps settings to extra variables.
			 *
			 * @static
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public static function settings_to_extras() {

				return [
					'title_margin'       => 'title_margin',
					'title_border_color' => 'title_border_color',
					'title_style_type'   => 'title_style_type',
				];
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'separator_border_size' => 'border_size',
					'sep_color'             => 'border_color',
				];
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
				$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
				$defaults   = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_comments' );

				$defaults['border_size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );
				$defaults['padding']     = FusionBuilder::validate_shortcode_attr_value( $defaults['padding'], 'px' );

				$this->args = $defaults;

				$this->emulate_post();
				$post_id = get_the_ID();
				if ( ( ! $post_id || -99 === $post_id ) && fusion_is_preview_frame() ) {
					$content = '';
					$this->restore_post();
					return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $content, $args );
				}

				ob_start();

				// Add filter to load template from FB.
				add_filter( 'comments_template', [ $this, 'template' ] );

				set_query_var( 'fusion_tb_comments_args', $defaults );

				comments_template();

				// Remove filter.
				remove_filter( 'comments_template', [ $this, 'template' ] );

				$content .= ob_get_clean();

				$this->restore_post();

				if ( $is_builder ) {
					$content = preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $content );
				}

				$styles = $this->get_styles();

				$html = '<div ' . FusionBuilder::attributes( 'fusion_tb_comments-shortcode' ) . '>' . $content . '</div>';

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $styles . $html, $args );
			}

			/**
			 * Render for live editor.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_render( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				$live_request = false;
				$content      = '';

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults     = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$return_data  = [];
					$live_request = true;
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				if ( class_exists( 'Fusion_App' ) && $live_request ) {

					// Do not hide headings and avatars.
					$defaults['headings'] = 'show';
					$defaults['avatar']   = 'square';

					$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : get_the_ID(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					if ( ( ! $post_id || -99 === $post_id ) || ( isset( $_POST['post_id'] ) && 'fusion_tb_section' === get_post_type( $_POST['post_id'] ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						echo wp_json_encode( [] );
						wp_die();
					}

					$this->emulate_post();

					ob_start();

					// Add filter to load template from FB.
					add_filter( 'comments_template', [ $this, 'template' ] );

					set_query_var( 'fusion_tb_comments_args', $defaults );

					comments_template();

					// Remove filter.
					remove_filter( 'comments_template', [ $this, 'template' ] );

					$content .= ob_get_clean();

					$this->restore_post();

					$content = preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $content );

					$return_data['comments'] = $content;
				}

				echo wp_json_encode( $return_data );
				wp_die();
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
					'class' => 'fusion-comments-tb fusion-comments-tb-' . $this->counter,
					'style' => '',
				];

				if ( $this->args['template_order'] ) {
					$template_order = explode( ',', $this->args['template_order'] );

					$attr['class'] .= ' fusion-order-' . str_replace( '_', '-', $template_order[0] );
				}

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( 'hide' !== $this->args['avatar'] ) {
					$attr['class'] .= ' ' . $this->args['avatar'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.5
			 * @return string
			 */
			protected function get_styles() {
				$this->base_selector = '.fusion-comments-tb.fusion-comments-tb-' . $this->counter;
				$this->dynamic_css   = [];

				if ( ! $this->is_default( 'border_size' ) ) {
					$this->add_css_property( $this->base_selector . ' .commentlist .the-comment', 'border-bottom-width', $this->args['border_size'] );
				}

				if ( ! $this->is_default( 'border_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .commentlist .the-comment', 'border-color', $this->args['border_color'] );
				}

				if ( 'circle' === $this->args['avatar'] ) {
					$this->add_css_property( $this->base_selector . '.circle .the-comment .avatar', 'border-radius', '50%' );
				}

				if ( 'square' === $this->args['avatar'] ) {
					$this->add_css_property( $this->base_selector . '.square .the-comment .avatar', 'border-radius', '0' );
				}

				if ( 'hide' === $this->args['avatar'] ) {
					$this->add_css_property( $this->base_selector . ' .commentlist .the-comment .comment-text', 'margin-left', '0' );
				}

				if ( ! $this->is_default( 'padding' ) ) {
					$this->add_css_property( $this->base_selector . ' .commentlist .children', 'padding-left', $this->args['padding'] );
				}

				if ( ! $this->is_default( 'heading_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-title h' . $this->args['heading_size'], 'color', $this->args['heading_color'], true );
				}

				if ( ! $this->is_default( 'link_color' ) ) {
					$this->add_css_property( $this->base_selector . ' a', 'color', $this->args['link_color'] );
					$this->add_css_property( $this->base_selector . ' .comment-author.meta a', 'color', $this->args['link_color'], true );
				}

				if ( ! $this->is_default( 'link_hover_color' ) ) {
					$this->add_css_property( $this->base_selector . ' a:hover', 'color', $this->args['link_hover_color'] );
					$this->add_css_property( $this->base_selector . ' .comment-author.meta a:hover', 'color', $this->args['link_hover_color'], true );
				}

				if ( ! $this->is_default( 'text_color' ) ) {
					$this->add_css_property( $this->base_selector, 'color', $this->args['text_color'] );
				}

				if ( ! $this->is_default( 'meta_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .comment-author.meta', 'color', $this->args['meta_color'], true );
				}

				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Load comments template from Avada Builder.
			 *
			 * @since 2.2
			 * @access public
			 * @param string $template current template path.
			 * @return string
			 */
			public function template( $template ) {
				return FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/components/templates/fusion-tb-comments.php';
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/comments.min.css' );
			}
		}
	}

	new FusionTB_Comments();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 2.2
 */
function fusion_component_comments() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Comments',
			[
				'name'                    => esc_attr__( 'Comments', 'fusion-builder' ),
				'shortcode'               => 'fusion_tb_comments',
				'icon'                    => 'fusiona-comments',
				'component'               => true,
				'templates'               => [ 'content' ],
				'components_per_template' => 1,
				'params'                  => [
					[
						'type'        => 'sortable',
						'heading'     => esc_attr__( 'Comments Template Order', 'fusion-builder' ),
						'description' => esc_attr__( 'Set if comments or comments form should be displayed first.', 'fusion-builder' ),
						'param_name'  => 'template_order',
						'default'     => 'comments,comment_form',
						'choices'     => [
							'comments'     => esc_html__( 'Comments', 'fusion-builder' ),
							'comment_form' => esc_html__( 'Comment Form', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Comment Avatar', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a section for user comment avatar.', 'fusion-builder' ),
						'param_name'  => 'avatar',
						'default'     => 'square',
						'value'       => [
							'square' => esc_html__( 'Square', 'fusion-builder' ),
							'circle' => esc_html__( 'Circle', 'fusion-builder' ),
							'hide'   => esc_html__( 'Hide', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Headings', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide headings.', 'fusion-builder' ),
						'param_name'  => 'headings',
						'default'     => 'show',
						'value'       => [
							'show' => esc_html__( 'Show', 'fusion-builder' ),
							'hide' => esc_html__( 'Hide', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'HTML Heading Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the size of the HTML heading that should be used, h1-h6.', 'fusion-builder' ),
						'param_name'  => 'heading_size',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							'1' => 'H1',
							'2' => 'H2',
							'3' => 'H3',
							'4' => 'H4',
							'5' => 'H5',
							'6' => 'H6',
						],
						'default'     => '2',
						'dependency'  => [
							[
								'element'  => 'headings',
								'value'    => 'show',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Heading Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the heading color.', 'fusion-builder' ),
						'param_name'  => 'heading_color',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'headings',
								'value'    => 'show',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Comment Separator Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border size of the separators. In pixels.', 'fusion-builder' ),
						'param_name'  => 'border_size',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'separator_border_size' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Comment Separator Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the separators.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'dependency'  => [
							[
								'element'  => 'border_size',
								'value'    => '0',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Comment Indent', 'fusion-builder' ),
						'description' => esc_attr__( 'Set left padding for child comments. In pixels.', 'fusion-builder' ),
						'param_name'  => 'padding',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '40',
						'min'         => '0',
						'max'         => '100',
						'step'        => '1',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Comment Separator Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the separators.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'dependency'  => [
							[
								'element'  => 'border_size',
								'value'    => '0',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the link color.', 'fusion-builder' ),
						'param_name'  => 'link_color',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the link hover color.', 'fusion-builder' ),
						'param_name'  => 'link_hover_color',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color.', 'fusion-builder' ),
						'param_name'  => 'text_color',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Meta Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the meta color.', 'fusion-builder' ),
						'param_name'  => 'meta_color',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
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
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-comments-tb',
					],
				],
				'callback'                => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_comments',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_comments' );

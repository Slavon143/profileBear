<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_author' ) ) {

	if ( ! class_exists( 'FusionTB_Author' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 2.2
		 */
		class FusionTB_Author extends Fusion_Component {

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
				parent::__construct( 'fusion_tb_author' );
				add_filter( 'fusion_attr_fusion_tb_author-shortcode', [ $this, 'attr' ] );

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
				return is_singular() || is_author();
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
					'animation_direction' => 'down',
					'animation_speed'     => '0.1',
					'animation_offset'    => $fusion_settings->get( 'animation_offset' ),
					'animation_type'      => '',
					'avatar'              => 'square',
					'biography'           => 'show',
					'class'               => '',
					'headings'            => 'show',
					'heading_size'        => '2',
					'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
					'id'                  => '',
					'margin_bottom'       => '',
					'margin_left'         => '',
					'margin_right'        => '',
					'margin_top'          => '',
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
				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_author' );

				$this->args = $defaults;

				$post_author_id = get_post_field( 'post_author', $this->get_post_id() );

				$content = '<section ' . FusionBuilder::attributes( 'fusion_tb_author-shortcode' ) . '>';

				if ( 'show' === $this->args['headings'] ) {
					$author_link = '<a href="' . esc_url( get_author_posts_url( $post_author_id ) ) . '">' . esc_html( get_the_author_meta( 'display_name', $post_author_id ) ) . '</a>';

					/* translators: The link. */
					$title = sprintf( __( 'About the Author: %s', 'fusion-builder' ), $author_link ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride

					$content .= fusion_render_title( $this->args['heading_size'], $title );
				}

				$content .= '<div class="about-author-container">';
				if ( 'hide' !== $this->args['avatar'] ) {
					$content .= '<div class="avatar">';
					$content .= get_avatar( get_the_author_meta( 'email', $post_author_id ), '72' );
					$content .= '</div>';
				}

				if ( 'hide' !== $this->args['biography'] ) {
					$content .= '<div class="description">';
					$content .= get_the_author_meta( 'description', $post_author_id );
					$content .= '</div>';
				}
				$content .= '</div>';
				$content .= '</section>';

				$styles = '<style type="text/css">';

				if ( 'circle' === $this->args['avatar'] ) {
					$styles .= ".fusion-author-tb-{$this->counter}.circle .about-author-container .avatar{border-radius: 50%;}";
				}

				if ( 'square' === $this->args['avatar'] ) {
					$styles .= ".fusion-author-tb-{$this->counter}.square .about-author-container .avatar{border-radius: 0;}";
				}

				$styles .= '</style>';

				$html = $styles . $content;

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
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
					$post_author_id = get_post_field( 'post_author', $this->get_post_id() );

					$author_link = '<a href="' . esc_url( get_author_posts_url( $post_author_id ) ) . '">' . esc_html( get_the_author_meta( 'display_name', $post_author_id ) ) . '</a>';

					/* translators: The link. */
					$title = sprintf( __( 'About the Author: %s', 'fusion-builder' ), $author_link ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride

					$content .= fusion_render_title( $defaults['heading_size'], $title );
					$content .= '<div class="about-author-container">';
					$content .= '<div class="avatar">';
					$content .= get_avatar( get_the_author_meta( 'email', $post_author_id ), '72' );
					$content .= '</div>';
					$content .= '<div class="description">';
					$content .= get_the_author_meta( 'description', $post_author_id );
					$content .= '</div>';
					$content .= '</div>';

					$return_data['author'] = $content;
				}

				echo wp_json_encode( $return_data );
				wp_die();
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
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'about-author fusion-author-tb fusion-author-tb-' . $this->counter,
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

				if ( 'hide' !== $this->args['avatar'] ) {
					$attr['class'] .= ' ' . $this->args['avatar'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}
		}
	}

	new FusionTB_Author();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 2.2
 */
function fusion_component_author() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Author',
			[
				'name'                    => esc_attr__( 'Author', 'fusion-builder' ),
				'shortcode'               => 'fusion_tb_author',
				'icon'                    => 'fusiona-author',
				'component'               => true,
				'templates'               => [ 'content', 'page_title_bar' ],
				'components_per_template' => 1,
				'params'                  => [
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
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Author Avatar', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection for author avatar.', 'fusion-builder' ),
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
						'heading'     => esc_attr__( 'Show Biography', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide author biography.', 'fusion-builder' ),
						'param_name'  => 'biography',
						'default'     => 'show',
						'value'       => [
							'show' => esc_html__( 'Show', 'fusion-builder' ),
							'hide' => esc_html__( 'Hide', 'fusion-builder' ),
						],
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
						'preview_selector' => '.fusion-author-tb',
					],
				],
				'callback'                => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_author',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_author' );

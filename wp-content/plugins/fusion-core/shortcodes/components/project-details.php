<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_project_details' ) ) {

	if ( ! class_exists( 'FusionTB_Project_Details' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 2.2
		 */
		class FusionTB_Project_Details extends Fusion_Component {

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
				parent::__construct( 'fusion_tb_project_details' );
				add_filter( 'fusion_attr_fusion_tb_project_details-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_project_details', [ $this, 'ajax_query' ] );
			}

			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 2.2
			 * @return boolean
			 */
			public function should_render() {
				return 'avada_portfolio' === get_post_type();
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
					'heading_enable'      => 'yes',
					'heading_size'        => '3',
					'author'              => 'yes',
					'margin_bottom'       => '',
					'margin_left'         => '',
					'margin_right'        => '',
					'margin_top'          => '',
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
			 * Render the shortcode
			 *
			 * @access public
			 * @since 2.2
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				global $post;
				$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_project_details' );

				$this->emulate_post();

				$this->post_type = get_post_type( $this->get_target_post() );

				$content = '<div ' . FusionBuilder::attributes( 'fusion_tb_project_details-shortcode' ) . '>';

				ob_start();
				require FUSION_CORE_PATH . '/shortcodes/components/templates/fusion-tb-project-details.php';
				$content .= ob_get_clean();

				$content .= '</div>';

				$this->restore_post();
				$this->counter++;

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $content );
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
					'class' => 'fusion-project-details-tb fusion-project-details-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$animations = FusionBuilder::animations(
						[
							'type'      => $this->args['animation_type'],
							'direction' => $this->args['animation_direction'],
							'speed'     => $this->args['animation_speed'],
							'offset'    => $this->args['animation_offset'],
						]
					);

					$attr = array_merge( $attr, $animations );

					$attr['class'] .= ' ' . $attr['animation_class'];
					unset( $attr['animation_class'] );
				}

				if ( $this->args['margin_top'] ) {
					$attr['style'] .= 'margin-top:' . $this->args['margin_top'] . ';';
				}

				if ( $this->args['margin_right'] ) {
					$attr['style'] .= 'margin-right:' . $this->args['margin_right'] . ';';
				}

				if ( $this->args['margin_bottom'] ) {
					$attr['style'] .= 'margin-bottom:' . $this->args['margin_bottom'] . ';';
				}

				if ( $this->args['margin_left'] ) {
					$attr['style'] .= 'margin-left:' . $this->args['margin_left'] . ';';
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}

			/**
			 * Gets the query data.
			 *
			 * @access public
			 * @since 2.2
			 * @return void
			 */
			public function ajax_query() {
				global $post, $authordata;
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );

					$this->emulate_post();

					if ( ! $post ) {
						$post = Fusion_Dummy_Post::get_dummy_post(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
					}

					$this->post_type = get_post_type( $this->get_target_post() );

					$this->restore_post();

					if ( ! is_object( $authordata ) ) {
						$authordata = get_userdata( $post->post_author ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
					}

					// Build live query response.
					$terms_skills   = get_the_term_list( $post->ID, 'portfolio_skills', '', '<br />', '' );
					$terms_category = get_the_term_list( $post->ID, 'portfolio_category', '', '<br />', '' );
					$terms_tags     = get_the_term_list( $post->ID, 'portfolio_tags', '', '<br />', '' );

					$return_data = [
						'terms_skills'     => $terms_skills,
						'terms_category'   => $terms_category,
						'terms_tags'       => $terms_tags,
						'project_url'      => fusion_data()->post_meta( $post->ID )->get( 'project_url' ),
						'project_url_text' => fusion_data()->post_meta( $post->ID )->get( 'project_url_text' ),
						'copy_url'         => fusion_data()->post_meta( $post->ID )->get( 'copy_url' ),
						'copy_url_text'    => fusion_data()->post_meta( $post->ID )->get( 'copy_url_text' ),
						'author'           => get_the_author_posts_link(),
					];

					wp_reset_postdata();

					echo wp_json_encode( $return_data );
					wp_die();
				}
			}
		}
	}

	new FusionTB_Project_Details();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 2.2
 */
function fusion_component_project_details() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Project_Details',
			[
				'name'                    => esc_attr__( 'Project Details', 'fusion-core' ),
				'shortcode'               => 'fusion_tb_project_details',
				'icon'                    => 'fusiona-project-details',
				'class'                   => 'hidden',
				'component'               => true,
				'templates'               => [ 'content' ],
				'components_per_template' => 1,
				'callback'                => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_project_details',
					'ajax'     => true,
				],
				'params'                  => [
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-core' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-core' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-core' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-core' ),
						'param_name'  => 'class',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-core' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-core' ),
						'param_name'  => 'id',
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Enable Heading', 'fusion-core' ),
						'description' => esc_html__( 'Turn on if you want to display default heading.', 'fusion-core' ),
						'param_name'  => 'heading_enable',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_html__( 'Yes', 'fusion-core' ),
							'no'  => esc_html__( 'No', 'fusion-core' ),
						],
						'group'       => esc_html__( 'Design', 'fusion-core' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'HTML Heading Size', 'fusion-core' ),
						'description' => esc_html__( 'Choose the size of the HTML heading that should be used, h1-h6.', 'fusion-core' ),
						'param_name'  => 'heading_size',
						'value'       => [
							'1' => 'H1',
							'2' => 'H2',
							'3' => 'H3',
							'4' => 'H4',
							'5' => 'H5',
							'6' => 'H6',
						],
						'default'     => '3',
						'group'       => esc_html__( 'Design', 'fusion-core' ),
						'dependency'  => [
							[
								'element'  => 'heading_enable',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'heading'     => esc_attr__( 'Show Author', 'fusion-core' ),
						'description' => esc_html__( 'Choose to show or hide the author in the Project Details.', 'fusion-core' ),
						'type'        => 'radio_button_set',
						'param_name'  => 'author',
						'group'       => esc_html__( 'Design', 'fusion-core' ),
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Show', 'fusion-core' ),
							'no'  => esc_attr__( 'Hide', 'fusion-core' ),
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Margin', 'fusion-core' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-core' ),
						'param_name'       => 'margin',
						'value'            => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
						'group'            => esc_html__( 'Design', 'fusion-core' ),
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-project-details-tb',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_project_details' );

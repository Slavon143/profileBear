<?php
/**
 * Handler for Taxonomy Meta
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.3
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handle taxonomy meta.
 */
class Avada_Taxonomy_Meta {
	/**
	 * Holds meta box object
	 *
	 * @var object
	 * @access protected
	 */
	protected $fusion_meta_box;

	/**
	 * Holds meta box fields.
	 *
	 * @access protected
	 * @since 5.3
	 * @var array
	 */
	protected $meta_fields = [];

	/**
	 * Type of form; edit or new term.
	 *
	 * @access protected
	 * @since 5.3
	 * @var string
	 */
	protected $form_type;

	/**
	 * Construct the object and init hooks
	 *
	 * @access public
	 * @since 5.3
	 * @param array $config Configuration data.
	 */
	public function __construct( $config ) {
		// Return if not admin.
		if ( ! is_admin() ) {
			return;
		}

		// Set config values.
		$this->fusion_meta_box = $config;

		// Add Actions.
		add_action( 'admin_init', [ $this, 'init_hooks' ] );

		// Add styles and scripts.
		add_action( 'admin_print_styles', [ $this, 'add_scripts_styles' ] );
	}

	/**
	 * Taxonomy options map.
	 *
	 * @access public
	 * @since 6.0
	 * @return array
	 */
	public static function avada_taxonomy_map() {

		// Allow regular logic to be skipped.
		$custom_options = apply_filters( 'before_avada_taxonomy_map', [] );
		if ( ! empty( $custom_options ) || false === $custom_options ) {
			return $custom_options;
		}

		// Regular PTB TO.
		$page_title_option_name = 'page_title_bar';

		if ( get_the_id() === (int) get_option( 'page_for_posts' ) ) {

			// Blog page PTB.
			$page_title_option_name = 'blog_show_page_title_bar';
		} elseif ( ( isset( $_GET['taxonomy'] ) && ( 'post_tag' === $_GET['taxonomy'] || 'category' === $_GET['taxonomy'] ) ) || ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() && is_single() ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			// Blog archive/post PTB.
			$page_title_option_name = 'blog_page_title_bar';
		}

		$page_title_option = Avada()->settings->get( $page_title_option_name );

		// Check if we have a template override.
		$template_override = false;
		$ptb_override      = false;
		if ( class_exists( 'Fusion_Template_Builder' ) ) {
			$template_override = Fusion_Template_Builder::get_instance()->get_override( 'content' );
			$ptb_override      = Fusion_Template_Builder::get_instance()->get_override( 'page_title_bar' );
		}

		// Dependency check that page title bar not hidden.
		$page_title_dependency = [
			[
				'field'      => 'page_title',
				'value'      => 'no',
				'comparison' => '!=',
			],
		];
		if ( 'hide' === $page_title_option ) {
			$page_title_dependency[] = [
				'field'      => 'page_title',
				'value'      => 'default',
				'comparison' => '!=',
			];
		}

		// Dependency check that background is used.
		$page_title_bg_dependency   = $page_title_dependency;
		$page_title_bg_dependency[] = [
			'field'      => 'page_title',
			'value'      => 'yes_without_bar',
			'comparison' => '!=',
		];
		if ( 'content_only' === $page_title_option ) {
			$page_title_bg_dependency[] = [
				'field'      => 'page_title',
				'value'      => 'default',
				'comparison' => '!=',
			];
		}

		// Get array of available sliders.
		$active_slider_types = avada_get_available_sliders_dropdown();
		$sliders_array       = avada_get_available_sliders_array();

		$sections                     = [];
		$sections['taxonomy_options'] = [
			'label'  => esc_html__( 'Avada Taxonomy Options', 'Avada' ),
			'id'     => 'taxonomy_options',
			'class'  => 'avada-tax-heading avada-tax-heading-edit',
			'icon'   => 'fusiona-page-options',
			'fields' => [
				'fusion_tax_heading' => [
					'id'    => 'fusion_tax_heading',
					'label' => esc_html__( 'Avada Taxonomy Options', 'Avada' ),
					'class' => 'avada-tax-heading avada-tax-heading-edit',
					'type'  => 'header',
				],
			],
		];

		$sections['taxonomy_options']['fields']['featured_image'] = [
			'id'          => 'featured_image',
			'label'       => esc_html__( 'Featured Image', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => esc_html__( 'Select an featured image to represent the term.  This can then be used in dynamic data and post cards.' ),
			'type'        => 'media',
			'transport'   => 'postMessage',
		];

		if ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) {
			// Click here for Avada Slider, Revolution Slider or Layer Slider.
			$sections['taxonomy_options']['fields']['sliders_note'] = [
				'label'       => '',
				'description' => '<div class="fusion-redux-important-notice">' . avada_get_sliders_note( $sliders_array, $active_slider_types ) . '</div>',
				'id'          => 'sliders_note',
				'type'        => 'custom',
			];
		}

		$sections['taxonomy_options']['fields']['slider_type'] = [
			'id'              => 'slider_type',
			'label'           => esc_html__( 'Slider Type ', 'Avada' ),
			'choices'         => $active_slider_types,
			'default'         => 'no',
			'class'           => 'avada-sliders-selection',
			'description'     => esc_html__( 'Select the type of slider that displays.', 'Avada' ),
			'type'            => 'select',
			'transport'       => 'postMessage',
			'partial_refresh' => [
				'fusion_slider_change' => [
					'selector'            => '#sliders-container',
					'container_inclusive' => false,
					'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'avada_slider' ],
				],
			],
		];
		if ( class_exists( 'LS_Sliders' ) ) {
			$sections['taxonomy_options']['fields']['slider'] = [
				'id'              => 'slider',
				'label'           => __( 'Select LayerSlider ', 'Avada' ),
				'choices'         => $sliders_array['layer_sliders'],
				'default'         => 0,
				'class'           => 'avada-sliders-group avada-layer-slider',
				'description'     => __( 'Select the unique name of the slider.', 'Avada' ),
				'dependency'      => [
					[
						'field'      => 'slider_type',
						'value'      => 'layer',
						'comparison' => '==',
					],
				],
				'type'            => 'select',
				'transport'       => 'postMessage',
				'partial_refresh' => [
					'fusion_slider_change' => [
						'selector'            => '#sliders-container',
						'container_inclusive' => false,
						'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'avada_slider' ],
					],
				],
			];
		}

		if ( method_exists( 'FusionCore_Plugin', 'get_fusion_sliders' ) ) {
			$sections['taxonomy_options']['fields']['wooslider'] = [
				'id'              => 'wooslider',
				'label'           => __( 'Select Avada Slider ', 'Avada' ),
				'choices'         => $sliders_array['fusion_sliders'],
				'default'         => 0,
				'class'           => 'avada-sliders-group avada-flex-slider',
				'description'     => __( 'Select the unique name of the slider.', 'Avada' ),
				'dependency'      => [
					[
						'field'      => 'slider_type',
						'value'      => 'flex',
						'comparison' => '==',
					],
				],
				'type'            => 'select',
				'transport'       => 'postMessage',
				'partial_refresh' => [
					'fusion_slider_change' => [
						'selector'            => '#sliders-container',
						'container_inclusive' => false,
						'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'avada_slider' ],
					],
				],
			];
		}

		if ( function_exists( 'rev_slider_shortcode' ) ) {
			$sections['taxonomy_options']['fields']['revslider'] = [
				'id'              => 'revslider',
				'label'           => esc_html__( 'Select Slider Revolution Slider', 'Avada' ),
				'choices'         => $sliders_array['rev_sliders'],
				'default'         => 0,
				'class'           => 'avada-sliders-group avada-rev-slider',
				'description'     => esc_html__( 'Select the unique name of the slider.', 'Avada' ),
				'dependency'      => [
					[
						'field'      => 'slider_type',
						'value'      => 'rev',
						'comparison' => '==',
					],
				],
				'type'            => 'select',
				'transport'       => 'postMessage',
				'partial_refresh' => [
					'fusion_slider_change' => [
						'selector'            => '#sliders-container',
						'container_inclusive' => false,
						'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'avada_slider' ],
					],
				],
			];
		}

		if ( true === taxonomy_exists( 'themefusion_es_groups' ) ) {
			$sections['taxonomy_options']['fields']['elasticslider'] = [
				'id'              => 'elasticslider',
				'label'           => esc_html__( 'Select Elastic Slider', 'Avada' ),
				'choices'         => $sliders_array['elastic_sliders'],
				'default'         => 0,
				'class'           => 'avada-sliders-group avada-elastic-slider',
				'description'     => esc_html__( 'Select the unique name of the slider.', 'Avada' ),
				'dependency'      => [
					[
						'field'      => 'slider_type',
						'value'      => 'elastic',
						'comparison' => '==',
					],
				],
				'type'            => 'select',
				'transport'       => 'postMessage',
				'partial_refresh' => [
					'fusion_slider_change' => [
						'selector'            => '#sliders-container',
						'container_inclusive' => false,
						'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'avada_slider' ],
					],
				],
			];
		}

		$sections['taxonomy_options']['fields']['slider_position'] = [
			'id'          => 'slider_position',
			'label'       => esc_html__( 'Slider Position', 'Avada' ),
			'choices'     => [
				'default' => esc_html__( 'Default', 'Avada' ),
				'below'   => esc_html__( 'Below', 'Avada' ),
				'above'   => esc_html__( 'Above', 'Avada' ),
			],
			'default'     => 'default',
			'class'       => 'avada-sliders-group avada-slider-buttonset',
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Select if the slider shows below or above the header. Only works for top header position. %s', 'Avada' ), Avada()->settings->get_default_description( 'slider_position', '', 'select' ) ),
			'to_default'  => [
				'id' => 'slider_position',
			],
			'dependency'  => [
				[
					'field'      => 'slider_type',
					'value'      => 'no',
					'comparison' => '!=',
				],
			],
			'type'        => 'radio-buttonset',
		];
		$is_builder = isset( $_GET['builder'] ) && isset( $_GET['builder_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$sections['taxonomy_options']['fields']['slider_visibility'] = [
			'id'          => 'slider_visibility',
			'label'       => esc_attr__( 'Slider Visibility', 'Avada' ),
			/* translators: Global Options link. */
			'description' => sprintf( esc_html__( 'Choose to show or hide the slider on small, medium or large screens. You can choose more than one at a time. Each of the 3 sizes has a custom width setting on the Responsive tab in the. %s.', 'Avada' ), '<a href="' . Avada()->settings->get_setting_link( 'visibility_small' ) . '" target="_blank" rel="noopener noreferrer">' . apply_filters( 'fusion_options_label', esc_html__( 'Element Options', 'Avada' ) ) . '</a>' ),
			'value'       => [
				'small-visibility'  => $is_builder ? '<span class="fusiona-mobile"></span>|' . esc_attr__( 'Small Screen', 'Avada' ) : esc_attr__( 'Small Screen', 'Avada' ),
				'medium-visibility' => $is_builder ? '<span class="fusiona-tablet"></span>|' . esc_attr__( 'Medium Screen', 'Avada' ) : esc_attr__( 'Medium Screen', 'Avada' ),
				'large-visibility'  => $is_builder ? '<span class="fusiona-desktop"></span>|' . esc_attr__( 'Large Screen', 'Avada' ) : esc_attr__( 'Large Screen', 'Avada' ),
			],
			'default'     => 'small-visibility,medium-visibility,large-visibility',
			'transport'   => 'postMessage',
			'output'      => [
				[
					'element'           => 'helperElement',
					'property'          => 'dummy',
					'callback'          => [
						'change_slider_visibility',
						[
							'element' => '#sliders-container',
						],
					],
					'sanitize_callback' => '__return_empty_string',
				],
			],
			'dependency'  => [
				[
					'field'      => 'slider_type',
					'value'      => 'no',
					'comparison' => '!=',
				],
			],
			'type'        => 'checkbox-buttonset',
		];

		if ( ! $template_override ) {
			$sections['taxonomy_options']['fields']['main_padding'] = [
				'id'          => 'main_padding',
				'label'       => esc_html__( 'Page Content Padding', 'Avada' ),
				/* translators: Additional description (defaults). */
				'description' => sprintf( esc_html__( 'In pixels ex: 20px. %s', 'Avada' ), Avada()->settings->get_default_description( 'main_padding', [ 'top', 'bottom' ] ) ),
				'type'        => 'dimensions',
				'value'       => [
					'top'    => '',
					'bottom' => '',
				],
				'location'    => 'TAXO',
				'css_vars'    => [
					[
						'name'    => '--main_padding-top',
						'element' => '#main',
					],
					[
						'name'    => '--main_padding-bottom',
						'element' => '#main',
					],
				],
			];
		}

		$sections['taxonomy_options']['fields']['archive_header_bg_color'] = [
			'id'          => 'archive_header_bg_color',
			'label'       => __( 'Header Background Color', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Controls the background color for the header. Hex code or rgba value, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'archive_header_bg_color' ) ),
			'default'     => Avada()->settings->get( 'archive_header_bg_color' ),
			'type'        => 'color-alpha',
			'location'    => 'TAXO',
			'css_vars'    => [
				[
					'name'     => '--header_bg_color',
					'element'  => '#side-header,.fusion-header',
					'callback' => [ 'sanitize_color' ],
				],
				[
					'name'     => '--archive_header_bg_color',
					'element'  => '#side-header,.fusion-header',
					'callback' => [ 'sanitize_color' ],
				],
			],
			'output'      => [
				[
					'element'           => 'helperElement',
					'property'          => 'dummy',
					'callback'          => [
						'toggle_class',
						[
							'condition' => [ '', 'header-not-opaque' ],
							'element'   => 'html, .avada-html-is-archive',
							'className' => 'avada-header-color-not-opaque',
						],
					],
					'sanitize_callback' => '__return_empty_string',
				],
			],
		];

		$sections['taxonomy_options']['fields']['mobile_archive_header_bg_color'] = [
			'id'          => 'mobile_archive_header_bg_color',
			'label'       => esc_html__( 'Mobile Header Background Color', 'Avada' ),
			/* translators: Additional description (defaults). */
			'description' => sprintf( esc_html__( 'Controls the background color for the header on mobile devices. Hex code or rgba value, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'mobile_archive_header_bg_color' ) ),
			'default'     => Avada()->settings->get( 'mobile_archive_header_bg_color' ),
			'type'        => 'color-alpha',
			'location'    => 'TAXO',
			'css_vars'    => [
				[
					'name'     => '--mobile_header_bg_color',
					'callback' => [ 'sanitize_color' ],
				],
			],
		];

		if ( ! $ptb_override ) {
			$sections['taxonomy_options']['fields']['page_title_bar'] = [
				'id'          => 'page_title_bar',
				'label'       => esc_html__( 'Page Title Bar', 'Avada' ),
				/* translators: Additional description (defaults). */
				'description' => sprintf( esc_html__( 'Choose to show or hide the page title bar. %s', 'Avada' ), Avada()->settings->get_default_description( $page_title_option_name, '', 'select' ) ),
				'dependency'  => [],
				'type'        => 'select',
				'choices'     => [
					'default'         => esc_html__( 'Default', 'Avada' ),
					'yes'             => esc_html__( 'Show Bar and Content', 'Avada' ),
					'yes_without_bar' => esc_html__( 'Show Content Only', 'Avada' ),
					'no'              => esc_html__( 'Hide', 'Avada' ),
				],
				'default'     => 'default',
				// We're forcing a refresh here because the TO option varies
				// depending on the context of the current page, and also because
				// the actual values of TO options are completely different to those in the PO.
				'transport'   => 'refresh',
				'to_default'  => [
					'id' => $page_title_option_name,
				],
			];

			$sections['taxonomy_options']['fields']['page_title_bg'] = [
				'id'          => 'page_title_bg',
				'label'       => esc_html__( 'Page Title Bar Background', 'Avada' ),
				/* translators: Additional description (defaults). */
				'description' => sprintf( esc_html__( 'Select an image to use for the page title bar background. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bg', 'url' ) ),
				'type'        => 'media',
				'dependency'  => $page_title_bg_dependency,
			];

			$sections['taxonomy_options']['fields']['page_title_bg_retina'] = [
				'id'          => 'page_title_bg_retina',
				'label'       => esc_html__( 'Page Title Bar Background Retina', 'Avada' ),
				/* translators: esc_html__ description (defaults). */
				'description' => sprintf( esc_html__( 'Select an image to use for retina devices. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bg_retina', 'url' ) ),
				'type'        => 'media',
				'dependency'  => [
					[
						'field'      => 'page_title_bg',
						'value'      => '',
						'comparison' => '!=',
					],
				],
				$page_title_dependency,
			];

			$sections['taxonomy_options']['fields']['page_title_height'] = [
				'id'          => 'page_title_height',
				'label'       => esc_html__( 'Page Title Bar Height', 'Avada' ),
				/* translators: percetange sign and additional description (defaults). */
				'description' => sprintf( esc_html__( 'Controls the height of the page title bar on desktop. Enter value including any valid CSS unit besides %1$s which does not work for page title bar, ex: 87px. %2$s', 'Avada' ), '%', Avada()->settings->get_default_description( 'page_title_height' ) ),
				'type'        => 'text',
				'location'    => 'TAXO',
				'css_vars'    => [
					[
						'name'    => '--page_title_height',
						'element' => '.fusion-page-title-bar',
					],
				],
				'dependency'  => $page_title_dependency,
			];

			$sections['taxonomy_options']['fields']['page_title_mobile_height'] = [
				'id'          => 'page_title_mobile_height',
				'label'       => esc_html__( 'Page Title Bar Mobile Height', 'Avada' ),
				/* translators: Percentage sign and additional description (defaults). */
				'description' => sprintf( esc_html__( 'Controls the height of the page title bar on mobile. Enter value including any valid CSS unit besides %1$s which does not work for page title bar, ex: 70px. %2$s', 'Avada' ), '%', Avada()->settings->get_default_description( 'page_title_mobile_height' ) ),
				'type'        => 'text',
				'location'    => 'TAXO',
				'css_vars'    => [
					[
						'name' => '--page_title_mobile_height',
					],
				],
				'dependency'  => $page_title_dependency,
			];
		}

		return $sections;
	}

	/**
	 * Add Meta Boxes for post types.
	 *
	 * @access public
	 * @since 5.3
	 */
	public function init_hooks() {
		// Loop through array and init hooks.
		foreach ( $this->fusion_meta_box['screens'] as $screen ) {
			// add fields to edit form.
			add_action( $screen . '_edit_form', [ $this, 'render_edit_form' ] );
			// add fields to add new form.
			add_action( $screen . '_add_form_fields', [ $this, 'render_new_form' ] );
			// this saves the edit fields.
			add_action( 'edited_' . $screen, [ $this, 'save_data' ], 10, 2 );
			// this saves the add fields.
			add_action( 'created_' . $screen, [ $this, 'save_data' ], 10, 2 );
		}
	}

	/**
	 * Add styles and scripts.
	 *
	 * @access public
	 * @since 5.3
	 */
	public function add_scripts_styles() {
		$screen = get_current_screen();

		// Add resources on required screens only.
		if ( 'edit-tags' === $screen->base || 'term' === $screen->base ) {
			$ver = Avada::get_theme_version();

			if ( function_exists( 'AWB_Global_Colors' ) ) {
				AWB_Global_Colors()->enqueue();
			}

			// Enqueu built-in script and styles for media JavaScript APIs.
			wp_enqueue_media();

			wp_enqueue_script(
				'avada-tax-meta-js',
				Avada::$template_dir_url . '/assets/admin/js/avada-tax-meta.js',
				[ 'jquery' ],
				$ver,
				true
			);

			wp_enqueue_style(
				'avada-tax-meta-css',
				Avada::$template_dir_url . '/assets/admin/css/avada-tax-meta.css',
				[],
				$ver
			);

			wp_enqueue_script(
				'selectwoo-js',
				Avada::$template_dir_url . '/assets/admin/js/selectWoo.full.min.js',
				[ 'jquery' ],
				'1.0.2',
				false
			);
			wp_enqueue_style(
				'select2-css',
				Avada::$template_dir_url . '/assets/admin/css/select2.css',
				[],
				'4.0.3',
				'all'
			);
		}
	}

	/**
	 * Set type of the form.
	 *
	 * @access public
	 * @since 5.3
	 * @param string $type Type of the form.
	 */
	public function set_form_type( $type ) {
		$this->form_type = $type;
	}

	/**
	 * Callback function to show fields on term edit form.
	 *
	 * @access public
	 * @since 5.3
	 * @param mixed $term_id ID of current term.
	 */
	public function render_edit_form( $term_id ) {
		$this->set_form_type( 'edit' );
		$this->render_fields( $term_id );
	}

	/**
	 * Callback function to show fields on add new taxonomy term form.
	 *
	 * @access public
	 * @since 5.3
	 * @param mixed $term_id ID of current term.
	 */
	public function render_new_form( $term_id ) {
		$this->set_form_type( 'new' );
		$this->render_fields( $term_id );
	}

	/**
	 * Callback function to show fields in meta box.
	 *
	 * @access public
	 * @since 5.3
	 * @param mixed $term_id ID of current term.
	 */
	public function render_fields( $term_id ) {

		// Check for Object.
		$term_id = is_object( $term_id ) ? $term_id->term_id : $term_id;

		if ( 'edit' === $this->form_type ) {

			// Check if we have a template override.
			$template_override = false;
			$ptb_override      = false;
			$header_override   = false;
			if ( class_exists( 'Fusion_Template_Builder' ) ) {
				$template_override = Fusion_Template_Builder::get_instance()->get_override( 'content' );
				$ptb_override      = Fusion_Template_Builder::get_instance()->get_override( 'page_title_bar' );
				$header_override   = Fusion_Template_Builder::get_instance()->get_override( 'header' );
			}
			?>

			<?php if ( $template_override || $ptb_override || $header_override ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'This term uses a custom template for:', 'Avada' ); ?></p>
					<ul style="padding-left:1em;">
						<?php if ( $template_override ) : ?>
							<li style="list-style-type:disc;"><?php echo esc_html( Fusion_Template_Builder::get_instance()->types['content']['label'] ); ?></li>
						<?php endif; ?>
						<?php if ( $ptb_override ) : ?>
							<li style="list-style-type:disc;"><?php echo esc_html( Fusion_Template_Builder::get_instance()->types['page_title_bar']['label'] ); ?></li>
						<?php endif; ?>
						<?php if ( $header_override ) : ?>
							<li style="list-style-type:disc;"><?php echo esc_html( Fusion_Template_Builder::get_instance()->types['header']['label'] ); ?></li>
						<?php endif; ?>
					</ul>
				</div>
			<?php endif; ?>

			<table class="avada-tax-meta-table">
			<tr class="avada-tax-meta-spacer"><td colspan="2"><?php wp_nonce_field( basename( __FILE__ ), 'fusion_taxnonomy_meta_nonce' ); ?></td></tr>
			<?php
		} else {
			wp_nonce_field( basename( __FILE__ ), 'fusion_taxnonomy_meta_nonce' );
		}

		foreach ( $this->meta_fields as $field ) {
			$name = $field['id'];
			// Field value.
			$meta = fusion_data()->term_meta( $term_id )->get( $name );
			$meta = ( null === $meta ) ? '' : $meta;
			$meta = ( '' !== $meta ) ? $meta : ( ( isset( $field['default'] ) && 'color' !== $field['type'] ) ? $field['default'] : '' );

			if ( 'image' !== $field['type'] ) {
				$meta = is_array( $meta ) ? array_map( 'esc_attr', $meta ) : esc_attr( $meta );
			}

			if ( 'edit' === $this->form_type ) {
				?>
				<tr class="form-field avada-tax-meta-field <?php echo esc_attr( $field['class'] ); ?>">
				<?php
			}

			// Call Separated methods for displaying each type of field.
			call_user_func( [ $this, 'render_field_' . $field['type'] ], $field, is_array( $meta ) ? $meta : stripslashes( $meta ) );
			if ( 'edit' === $this->form_type ) {
				?>
				</tr>
				<?php
			}
		}
		if ( 'edit' === $this->form_type ) {
			?>
			</table>
			<?php
		}
	}

	/**
	 * Save Data from Metabox
	 *
	 * @access public
	 * @since 5.3
	 * @param string $term_id ID of the current term.
	 */
	public function save_data( $term_id ) {
		$fields_data = [];

		// Return if inline save.
		if ( isset( $_REQUEST['action'] ) && 'inline-save-tax' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $term_id;
		}

		// Check revision, nonce, current taxonomy type, support of current taxonomy type and permission.
		if ( ! isset( $term_id ) || ( ! check_admin_referer( basename( __FILE__ ), 'fusion_taxnonomy_meta_nonce' ) ) || ( ! isset( $_POST['taxonomy'] ) ) || ( ! in_array( wp_unslash( $_POST['taxonomy'] ), $this->fusion_meta_box['screens'] ) ) || ( ! current_user_can( 'manage_categories' ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
			return $term_id;
		}

		foreach ( $this->meta_fields as $field ) {
			$value = isset( $_POST[ $field['id'] ] ) ? wp_unslash( $_POST[ $field['id'] ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( is_string( $value ) && 0 === strpos( $value, '{"' ) ) {
				$json = json_decode( $value, true );
				if ( $json ) {
					$value = $json;
				};
			}
			fusion_data()->term_meta( $term_id )->set( $field['id'], $value );
		}

		// Reset all caches except demo_data, fb_pages and patcher_messages.
		fusion_reset_all_caches(
			[
				'demo_data'        => false,
				'fb_pages'         => false,
				'patcher_messages' => false,
			]
		);
	}

	/**
	 *  Add Text Field to meta box
	 *
	 * @access public
	 * @since 5.3
	 * @param string $id   ID of the field.
	 * @param array  $args field aruguments.
	 */
	public function text( $id, $args ) {

		$field = [
			'type'    => 'text',
			'id'      => $id,
			'default' => '',
			'class'   => '',
			'desc'    => '',
			'style'   => '',
			'name'    => 'Text Field',
		];

		$field = array_merge( $field, $args );

		$this->meta_fields[] = $field;
	}

	/**
	 *  Add Text Field to meta box
	 *
	 * @access public
	 * @since 5.3
	 * @param string $id   ID of the field.
	 * @param array  $args field aruguments.
	 */
	public function dimensions( $id, $args ) {

		$field = [
			'type'    => 'dimensions',
			'id'      => $id,
			'default' => '',
			'class'   => '',
			'desc'    => '',
			'style'   => '',
			'name'    => 'Dimension Field',
		];

		$field = array_merge( $field, $args );

		$this->meta_fields[] = $field;
	}

	/**
	 *  Add Select Field to meta box
	 *
	 * @access public
	 * @since 5.3
	 * @param string $id      ID of the field.
	 * @param array  $options Array of available options.
	 * @param array  $args    Field aruguments.
	 */
	public function select( $id, $options, $args ) {
		$field = [
			'type'    => 'select',
			'id'      => $id,
			'default' => '',
			'class'   => '',
			'desc'    => '',
			'style'   => '',
			'name'    => 'Select Field',
			'options' => $options,
		];

		$field = array_merge( $field, $args );

		$this->meta_fields[] = $field;
	}

	/**
	 *  Add Radio Button Field to meta box
	 *
	 * @access public
	 * @since 5.3
	 * @param string $id      ID of the field.
	 * @param array  $options Array of available options.
	 * @param array  $args    Field aruguments.
	 */
	public function radio_buttonset( $id, $options, $args ) {
		$field = [
			'type'    => 'radio_buttonset',
			'id'      => $id,
			'default' => '',
			'class'   => '',
			'desc'    => '',
			'style'   => '',
			'name'    => 'Radio Field',
			'options' => $options,
		];

		$field = array_merge( $field, $args );

		$this->meta_fields[] = $field;
	}

	/**
	 *  Add Checkboxn Field to meta box
	 *
	 * @access public
	 * @since 7.4
	 * @param string $id      ID of the field.
	 * @param array  $options Array of available options.
	 * @param array  $args    Field aruguments.
	 */
	public function checkbox_buttonset( $id, $options, $args ) {
		$field = [
			'type'    => 'checkbox_buttonset',
			'id'      => $id,
			'default' => '',
			'class'   => '',
			'desc'    => '',
			'style'   => '',
			'name'    => 'Checkbox Field',
			'options' => $options,
		];

		$field = array_merge( $field, $args );

		$this->meta_fields[] = $field;
	}

	/**
	 *  Add Color Picket Field to meta box
	 *
	 * @access public
	 * @since 5.3
	 * @param string $id   ID of the field.
	 * @param array  $args Field aruguments.
	 */
	public function colorpicker( $id, $args ) {

		$field = [
			'type'    => 'color',
			'id'      => $id,
			'default' => '',
			'class'   => '',
			'desc'    => '',
			'style'   => '',
			'name'    => 'ColorPicker Field',
		];

		$field = array_merge( $field, $args );

		$this->meta_fields[] = $field;
	}

	/**
	 *  Add Color Picket Field to meta box
	 *
	 * @access public
	 * @since 5.3
	 * @param string $id   ID of the field.
	 * @param array  $args Field aruguments.
	 */
	public function image( $id, $args ) {

		$field = [
			'type'    => 'image',
			'id'      => $id,
			'default' => '',
			'class'   => '',
			'desc'    => '',
			'style'   => '',
			'url'     => '',
			'name'    => 'Image Field',
		];

		$field = array_merge( $field, $args );

		$this->meta_fields[] = $field;
	}

	/**
	 *  Add header to meta box
	 *
	 * @access public
	 * @since 5.3
	 * @param string $id   ID of the field.
	 * @param array  $args Field aruguments.
	 */
	public function header( $id, $args ) {

		$field = [
			'type'    => 'header',
			'id'      => $id,
			'value'   => '',
			'style'   => '',
			'default' => '',
		];

		$field = array_merge( $field, $args );

		$this->meta_fields[] = $field;
	}

	/**
	 * Render dependency markup.
	 *
	 * @since 5.3
	 * @param array $dependency dependence options.
	 * @return string $data_dependence markup
	 */
	private function render_dependency( $dependency = [] ) {

		// Disable dependencies if 'dependencies_status' is set to 0.
		if ( '0' === Avada()->settings->get( 'dependencies_status' ) ) {
			return '';
		}

		$data_dependency = '';
		if ( 0 < count( $dependency ) ) {
			$data_dependency .= '<div class="avada-tax-dependency">';
			foreach ( $dependency as $dependence ) {
				$data_dependency .= '<span class="hidden" data-value="' . $dependence['value'] . '" data-field="' . $dependence['field'] . '" data-comparison="' . $dependence['comparison'] . '"></span>';
			}
			$data_dependency .= '</div>';
		}
		return $data_dependency;
	}

	/**
	 * Show Field Text.
	 *
	 * @access public
	 * @since 5.3
	 * @param array  $field Field data.
	 * @param string $meta  Meta data.
	 */
	public function render_field_text( $field, $meta ) {
		$this->render_field_start( $field, $meta );
		?>
		<input type="text" class="avada-tax-text" name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $meta ); ?>" style="<?php echo esc_attr( $field['style'] ); ?>" size='30' />
		<?php
		$this->render_field_end( $field, $meta );
	}

	/**
	 * Dimensions field.
	 *
	 * @since 6.2.0
	 * @param array  $field Field data.
	 * @param string $meta  Meta data.
	 */
	public function render_field_dimensions( $field, $meta ) {
		$meta = (array) $meta;

		$this->render_field_start( $field, $meta );
		?>
		<div class="pyre_metabox_field">
			<div class="pyre_field avada-dimension">
				<?php foreach ( $field['default'] as $key => $default_val ) : ?>
					<?php
					$icon_class = 'fusiona-expand width';
					if ( false !== strpos( $key, 'height' ) ) {
						$icon_class = 'fusiona-expand  height';
					}
					if ( false !== strpos( $key, 'top' ) ) {
						$icon_class = 'dashicons dashicons-arrow-up-alt';
					}
					if ( false !== strpos( $key, 'right' ) ) {
						$icon_class = 'dashicons dashicons-arrow-right-alt';
					}
					if ( false !== strpos( $key, 'bottom' ) ) {
						$icon_class = 'dashicons dashicons-arrow-down-alt';
					}
					if ( false !== strpos( $key, 'left' ) ) {
						$icon_class = 'dashicons dashicons-arrow-left-alt';
					}
					$meta[ $key ] = isset( $meta[ $key ] ) ? (string) $meta[ $key ] : '';
					?>
					<div class="fusion-builder-dimension">
						<span class="add-on"><i class="<?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></i></span>
						<input type="text" name="<?php echo esc_attr( "{$field['id']}[{$key}]" ); ?>" id="pyre_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $meta[ $key ] ); ?>" />
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		$this->render_field_end( $field, $meta );
	}

	/**
	 * Show Field Select.
	 *
	 * @access public
	 * @since 5.3
	 * @param array  $field Field data.
	 * @param string $meta  Meta data.
	 */
	public function render_field_select( $field, $meta ) {

		if ( ! is_array( $meta ) ) {
			$meta = (array) $meta;
		}

		$this->render_field_start( $field, $meta );
		?>
		<select class="avada-tax-select" style="<?php echo esc_attr( $field['style'] ); ?>" name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>">
		<?php
		foreach ( $field['options'] as $key => $value ) :
			?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php echo selected( in_array( $key, $meta ), true, false ); // phpcs:ignore WordPress.PHP.StrictInArray ?>><?php echo esc_attr( $value ); ?></option>
		<?php endforeach; ?>
		</select>
		<?php

		$this->render_field_end( $field, $meta );
	}

	/**
	 * Show Radio Buttonset Field.
	 *
	 * @access public
	 * @since 5.3
	 * @param array  $field Field data.
	 * @param string $meta  Meta data.
	 */
	public function render_field_radio_buttonset( $field, $meta ) {

		if ( empty( $meta ) ) {
			$meta = 'default';
		}

		$this->render_field_start( $field, $meta );

		?>
		<div class="avada-tax-buttonset avada-buttonset">
			<div class="avada-tax-button-set ui-buttonset radio">
				<input type="hidden" id="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $meta ); ?>" class="button-set-value" />
				<?php foreach ( $field['options'] as $key => $option ) : ?>
					<?php $selected = ( $key == $meta ) ? ' ui-state-active' : ''; // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison ?>
					<a href="#" class="ui-button buttonset-item<?php echo esc_attr( $selected ); ?>" data-value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $option ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		$this->render_field_end( $field, $meta );
	}

	/**
	 * Show Checkbox buttonset Field.
	 *
	 * @access public
	 * @since 7.3
	 * @param array  $field Field data.
	 * @param string $meta  Meta data.
	 */
	public function render_field_checkbox_buttonset( $field, $meta ) {

		if ( empty( $meta ) ) {
			$meta = 'default';
		}

		$meta = ! is_array( $meta ) ? explode( ',', $meta ) : $meta;

		$this->render_field_start( $field, $meta );

		?>
		<div class="avada-tax-buttonset avada-buttonset">
			<div class="avada-tax-button-set ui-buttonset checkbox">
				<input type="hidden" id="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( implode( ',', $meta ) ); ?>" class="button-set-value" data-default="<?php echo esc_attr( implode( ',', $meta ) ); ?>" />
				<?php foreach ( $field['options'] as $key => $option ) : ?>
					<?php $selected = in_array( $key, $meta, true ) ? ' ui-state-active' : ''; ?>
					<a href="#" class="ui-button buttonset-item<?php echo esc_attr( $selected ); ?>" data-value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $option ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		$this->render_field_end( $field, $meta );
	}

	/**
	 * Show Color Picker.
	 *
	 * @access public
	 * @since 5.3
	 * @param array  $field Field data.
	 * @param string $meta  Meta data.
	 */
	public function render_field_color( $field, $meta ) {

		$this->render_field_start( $field, $meta );
		?>
		<input class="avada-tax-color color-picker fusion_options" data-alpha="true" type="text" name="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $meta ); ?>" data-default="<?php echo esc_attr( $field['default'] ); ?>" />
		<?php
		$this->render_field_end( $field, $meta );
	}

	/**
	 * Show Image Field.
	 *
	 * @access public
	 * @since 5.3
	 * @param array  $field Field data.
	 * @param string $meta  Meta data.
	 */
	public function render_field_image( $field, $meta ) {
		$this->render_field_start( $field, $meta );

		$image_url     = ( isset( $meta['url'] ) && ! empty( $meta['url'] ) ) ? $meta['url'] : '';
		$preview_style = 'max-width:100%';
		?>
		<span class="avada-tax-img-field <?php echo esc_attr( $field['id'] ); ?>">
			<input type="text" name="<?php echo esc_attr( $field['id'] ); ?>[url]" value="<?php echo esc_attr( $image_url ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" class="avada-tax-image-url"/>
			<input class="button avada-tax-image-upload-clear<?php echo ( $image_url ) ? '' : ' hidden'; ?>" value="<?php esc_attr_e( 'Remove Image', 'Avada' ); ?>" type="button" />
			<input class="button avada-tax-image-upload<?php echo ( $image_url ) ? ' hidden' : ''; ?>" value="<?php esc_attr_e( 'Upload Image', 'Avada' ); ?>" type="button" />
			<input type="hidden" name="<?php echo esc_attr( $field['id'] ); ?>" value='<?php echo esc_attr( wp_json_encode( $meta ) ); ?>' id="<?php echo esc_attr( $field['id'] ); ?>" class="avada-tax-image" />
		</span>
		<?php
		$this->render_field_end( $field, $meta );
	}

	/**
	 * Show header.
	 *
	 * @access public
	 * @since 5.3
	 * @param array $field Field data.
	 * @param array $meta  Meta data.
	 */
	public function render_field_header( $field, $meta ) {
		?>
		<?php if ( 'edit' === $this->form_type ) : ?>
			<td colspan="2">
				<div class="avada-tax-meta-header">
					<h3 style="<?php echo esc_attr( $field['style'] ); ?>"> <?php echo esc_attr( $field['value'] ); ?></h3>
					<span class="avada-tax-meta-handle toggle-indicator"></span>
				</div>
			</td>
		<?php elseif ( 'new' === $this->form_type ) : ?>
			<div class="form-field avada-tax-meta-field avada-tax-header">
				<h3 style="<?php echo esc_attr( $field['style'] ); ?>"> <?php echo esc_attr( $field['value'] ); ?></h3>
				<span class="avada-tax-meta-handle toggle-indicator"></span>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Begin Field.
	 *
	 * @access public
	 * @since 5.3
	 * @param array  $field Field data.
	 * @param string $meta  Meta data.
	 */
	public function render_field_start( $field, $meta ) {
		?>
		<?php if ( 'edit' === $this->form_type ) : ?>
			<th scope="row">
		<?php else : ?>
			<div class="form-field avada-tax-meta-field <?php echo esc_attr( $field['class'] ); ?>" >
		<?php endif; ?>

		<?php if ( '' !== $field['name'] || false !== $field['name'] ) : ?>
			<label> <?php echo esc_attr( $field['name'] ); ?></label>
		<?php endif; ?>

		<?php if ( isset( $field['desc'] ) && '' !== $field['desc'] ) : ?>
			<?php if ( 'new' === $this->form_type ) : ?>
				<p class='description'><?php echo $field['desc']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php elseif ( 'edit' === $this->form_type ) : ?>
				<p class='description'><?php echo $field['desc']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
		<?php endif; ?>

		<?php
		if ( isset( $field['dependency'] ) && is_array( $field['dependency'] ) ) {
			echo $this->render_dependency( $field['dependency'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>

		<?php if ( 'color' === $field['type'] && isset( $field['default'] ) && '' !== $field['default'] ) : ?>
			<span class="tax-meta-default-reset">
				<a href="#" id="default-<?php echo esc_attr( $field['id'] ); ?>" class="avada-range-default avada-hide-from-atts" type="radio" name="<?php echo esc_attr( $field['id'] ); ?>" value="" data-default="<?php echo esc_attr( $field['default'] ); ?>">
					<?php esc_attr_e( 'Reset to default.', 'Avada' ); ?>
				</a>
				<span><?php esc_attr_e( 'Using default value.', 'Avada' ); ?></span>
			</span>
		<?php endif; ?>

		<?php if ( isset( $field['desc'] ) && '' !== $field['desc'] ) : ?>
			</p>
		<?php endif; ?>

		<?php if ( 'edit' === $this->form_type ) : ?>
			</th><td>
		<?php endif; ?>
		<?php
	}

	/**
	 * End Field.
	 *
	 * @access public
	 * @since 5.3
	 * @param array  $field Field data.
	 * @param string $meta  Meta data.
	 */
	public function render_field_end( $field, $meta = null ) {
		if ( 'edit' === $this->form_type ) {
			?>
			</td>
			<?php
		} else {
			?>
			</div>
			<?php
		}
	}
}

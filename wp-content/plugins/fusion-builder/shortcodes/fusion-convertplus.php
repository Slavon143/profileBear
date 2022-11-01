<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_convert_plus' ) ) {

	if ( ! class_exists( 'FusionSC_FusionConvertPlus' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_FusionConvertPlus extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * Module ID selected by user through our element.
			 *
			 * @access protected
			 * @since 1.0
			 * @var string
			 */
			protected $style_id;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_shortcode( 'fusion_convert_plus', [ $this, 'render' ] );

				add_filter( 'fusion_convert_plus_content', 'shortcode_unautop' );
				add_filter( 'fusion_convert_plus_content', 'do_shortcode' );
				add_filter( 'cp_target_page_settings', [ $this, 'force_load_module' ], 10, 2 );
			}

			/**
			 * Force load the module if added via our element.
			 *
			 * @access public
			 * @since 1.0
			 * @param  bool   $display  Default display setting.
			 * @param  string $style_id Module ID used by the filter.
			 * @return bool   Returns true if id matches.
			 */
			public function force_load_module( $display, $style_id ) {
				if ( false === $display ) {
					$display = ( null !== $this->style_id ) ? $this->style_id == $style_id : $display; // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				}
				return $display;
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				// Set selected module type.
				$module = ( isset( $args['convert_plus_module'] ) && '' !== $args['convert_plus_module'] ) ? $args['convert_plus_module'] : '';

				// Set id depend on the module.
				$id             = $args[ $module . '_id' ];
				$this->style_id = $id;

				// Set display attr.
				$display = ( isset( $args['display'] ) && 'trigger' !== $args['display'] ) ? 'display="inline"' : '';

				// Set content blank if display inline is set.
				$content = ( isset( $args['display'] ) && 'trigger' === $args['display'] ) ? $content : '';

				$html = apply_filters( 'fusion_convert_plus_content', '[cp_' . $module . ' ' . $display . ' id="' . $id . '"]' . wpautop( $content, false ) . '[/cp_' . $module . ']' );

				$this->on_render();

				return apply_filters( 'fusion_element_convert_plus_content', $html, $args );
			}
		}
	}

	new FusionSC_FusionConvertPlus();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_convert_plus() {

	// Get all active modules.
	$convertplus_active_modules = get_option( 'convert_plug_modules', [] );
	$convertplus_modules        = [];

	foreach ( $convertplus_active_modules as $module ) {

		// Don't add stuff which gets saved as module by mistake.
		if ( 'security_nonce' === $module || '_wp_http_referer' === $module ) {
			continue;
		}

		$module                         = strtolower( str_replace( '_Popup', '', $module ) );
		$convertplus_modules[ $module ] = ucwords( str_replace( '_', ' ', $module ) );
	}

	// Get all Modals.
	$convertplus_modals_array = [];

	// If Modals are enabled.
	if ( isset( $convertplus_modules['modal'] ) ) {
		$convertplus_modals = get_option( 'smile_modal_styles', [] );
		if ( ! empty( $convertplus_modals ) ) {
			foreach ( $convertplus_modals as $value ) {
				$settings = maybe_unserialize( $value['style_settings'] );
				if ( isset( $settings['live'] ) && $settings['live'] ) {
					$convertplus_modals_array[ $value['style_id'] ] = urldecode( $value['style_name'] );
				}
			}
		}
	}

	if ( empty( $convertplus_modals_array ) ) {
		$convertplus_modals_array[] = __( 'No Modals Available', 'fusion-builder' );
	}

	// Get all SlideIns.
	$convertplus_slideins_array = [];

	if ( isset( $convertplus_modules['slide_in'] ) ) {
		$convertplus_slideins = get_option( 'smile_slide_in_styles', [] );
		if ( ! empty( $convertplus_slideins ) ) {
			foreach ( $convertplus_slideins as $value ) {
				$settings = maybe_unserialize( $value['style_settings'] );
				if ( isset( $settings['live'] ) && $settings['live'] ) {
					$convertplus_slideins_array[ $value['style_id'] ] = urldecode( $value['style_name'] );
				}
			}
		}
	}

	if ( empty( $convertplus_slideins_array ) ) {
		$convertplus_slideins_array[] = __( 'No Slide Ins Available', 'fusion-builder' );
	}

	// Get all Info Bars.
	$convertplus_info_bars_array = [];

	if ( isset( $convertplus_modules['info_bar'] ) ) {
		$convertplus_info_bars = get_option( 'smile_info_bar_styles', [] );
		if ( ! empty( $convertplus_info_bars ) ) {
			foreach ( $convertplus_info_bars as $value ) {
				$settings = maybe_unserialize( $value['style_settings'] );
				if ( isset( $settings['live'] ) && $settings['live'] ) {
					$convertplus_info_bars_array[ $value['style_id'] ] = urldecode( $value['style_name'] );
				}
			}
		}
	}
	if ( empty( $convertplus_info_bars_array ) ) {
		$convertplus_info_bars_array[] = __( 'No Info Bars Available', 'fusion-builder' );
	}

	fusion_builder_map(
		[
			'name'            => esc_attr__( 'Convert Plus', 'fusion-builder' ),
			'shortcode'       => 'fusion_convert_plus',
			'icon'            => 'fusiona-convertplus',
			'allow_generator' => true,
			'params'          => [
				[
					'type'        => 'select',
					'heading'     => esc_attr__( 'Select Module', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the module you want to use.', 'fusion-builder' ),
					'param_name'  => 'convert_plus_module',
					'default'     => 'modal',
					'value'       => $convertplus_modules,
				],
				[
					'type'        => 'select',
					'heading'     => esc_attr__( 'Select Modal', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the modal you want to use. Modals with status "Pause" are not included as they won\'t display until you make them "Live".', 'fusion-builder' ),
					'param_name'  => 'modal_id',
					'value'       => $convertplus_modals_array,
					'dependency'  => [
						[
							'element'  => 'convert_plus_module',
							'value'    => 'modal',
							'operator' => '==',
						],
					],
				],
				[
					'type'        => 'select',
					'heading'     => esc_attr__( 'Select Slide In', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the slide in you want to use. Slide Ins with status "Pause" are not included as they won\'t display until you make them "Live".', 'fusion-builder' ),
					'param_name'  => 'slide_in_id',
					'value'       => $convertplus_slideins_array,
					'dependency'  => [
						[
							'element'  => 'convert_plus_module',
							'value'    => 'slide_in',
							'operator' => '==',
						],
					],
				],
				[
					'type'        => 'select',
					'heading'     => esc_attr__( 'Select Info Bar', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the info bar you want to use. Info Bars with status "Pause" are not included as they won\'t display until you make them "Live".', 'fusion-builder' ),
					'param_name'  => 'info_bar_id',
					'value'       => $convertplus_info_bars_array,
					'dependency'  => [
						[
							'element'  => 'convert_plus_module',
							'value'    => 'info_bar',
							'operator' => '==',
						],
					],
				],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Launch Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls how the module will be launched.', 'fusion-builder' ),
					'param_name'  => 'display',
					'default'     => 'trigger',
					'value'       => [
						'trigger' => __( 'Manual Trigger', 'fusion-builder' ),
						'inline'  => __( 'Inline Display', 'fusion-builder' ),
					],
				],
				[
					'type'        => 'tinymce',
					'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
					'description' => esc_attr__( 'Enter some content for this textblock.', 'fusion-builder' ),
					'param_name'  => 'element_content',
					'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
					'placeholder' => true,
					'dependency'  => [
						[
							'element'  => 'display',
							'value'    => 'trigger',
							'operator' => '==',
						],
					],
				],
			],
		]
	);
}

if ( class_exists( 'Convert_Plug' ) ) {
	add_action( 'fusion_builder_before_init', 'fusion_element_convert_plus' );
}

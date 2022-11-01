<?php
/**
 * A class which is used to add various options to all widgets.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Fusion-Library
 * @since      3.9
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * A class which is used to add various options to all widgets.
 */
class AWB_Widget_Style {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @since 3.9
	 * @access private
	 * @var null|object
	 */
	private static $instance = null;

	/**
	 * Widget options we're going to add.
	 *
	 * @since 3.9
	 * @access private
	 * @var array
	 */
	public $widget_options;

	/**
	 * Returns a single instance of the object (singleton).
	 *
	 * @since 6.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new AWB_Widget_Style();
		}
		return self::$instance;
	}

	/**
	 * Construct the object.
	 *
	 * @since 5.3.0
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->init_options();

		// Add styles and scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'add_scripts_styles' ] );

		// If ajax request coming from front-end we dont want these added.
		if ( ! isset( $_POST ) || ! isset( $_POST['action'] ) || 'fusion_get_widget_data' !== $_POST['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			add_filter( 'in_widget_form', [ $this, 'add_widget_styling_options' ], 10, 3 );
			add_filter( 'widget_update_callback', [ $this, 'save_widget_styling_options' ], 10, 4 );
		}
		add_filter( 'dynamic_sidebar_params', [ $this, 'add_widget_styles' ] );

		// If front-end builder frame, add the JS object, else add the widget title filter.
		if ( class_exists( 'Fusion_App' ) ) {
			$builder_front = Fusion_App::get_instance();
			if ( $builder_front->get_builder_status() ) {
				add_action( 'wp_footer', [ $this, 'widget_options_object' ], 999 );
			}
		}

		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
		if ( ! $is_builder ) {
			add_filter( 'widget_title', [ $this, 'filter_widget_title' ], 10, 3 );

			add_filter( 'tribe_widget_arguments', [ $this, 'filter_tec_widget_title' ], 10 );

		}
	}

	/**
	 * Add styles and scripts.
	 *
	 * @access public
	 * @since 6.2.1
	 * @return void
	 */
	public function add_scripts_styles() {
		$theme_info = wp_get_theme();
		$screen     = get_current_screen();

		// General JS for fields.
		if ( 'widgets' === $screen->base && current_user_can( 'switch_themes' ) ) {
			if ( function_exists( 'AWB_Global_Colors' ) ) {
				AWB_Global_Colors()->enqueue();
			}

			wp_enqueue_script(
				'avada-fusion-options',
				Avada::$template_dir_url . '/assets/admin/js/avada-fusion-options.js',
				[ 'jquery', 'jquery-ui-sortable' ],
				$theme_info->get( 'Version' ),
				false
			);
		}
	}

	/**
	 * Hides the widget title if title option is set to "no".
	 *
	 * @since 6.2.1
	 * @access public
	 * @param string $title    The widget title. Default 'Pages'.
	 * @param array  $instance Array of settings for the current widget.
	 * @param mixed  $base_id  The widget ID.
	 * @return string The widget title.
	 */
	public function filter_widget_title( $title = '', $instance = [], $base_id = false ) {
		if ( isset( $instance['fusion_display_title'] ) && 'no' === $instance['fusion_display_title'] ) {
			return '';
		}

		return $title;
	}

	/**
	 * Hides the TEC widget title if title option is set to "no".
	 *
	 * @since 7.6.1
	 * @access public
	 * @param array $updated_instance  The widget options.
	 * @return array The updated widget options.
	 */
	public function filter_tec_widget_title( $updated_instance ) {
		if ( isset( $updated_instance['fusion_display_title'] ) && 'no' === $updated_instance['fusion_display_title'] ) {
			$updated_instance['title'] = '';
		}

		return $updated_instance;
	}

	/**
	 * Init all options that we're going to add.
	 *
	 * @since 5.3.0
	 * @access private
	 * @return void
	 */
	private function init_options() {

		$this->widget_options = [
			'fusion_display_title'  => [
				'key'         => 'fusion_display_title',
				'title'       => esc_html__( 'Display Widget Title', 'fusion-builder' ),
				'description' => esc_html__( 'Choose to enable or disable the widget title. Specifically useful for WP\'s default widget titles.', 'fusion-builder' ),
				'default'     => 'yes',
				'choices'     => [
					'yes' => esc_html__( 'Yes', 'fusion-builder' ),
					'no'  => esc_html__( 'No', 'fusion-builder' ),
				],
				'type'        => 'radio_button_set',
			],
			'fusion_padding_color'  => [
				'key'          => 'fusion_padding_color',
				'title'        => esc_html__( 'Padding', 'fusion-builder' ),
				'description'  => esc_html__( 'Controls the padding for this widget container. Enter value including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
				'css_property' => 'padding',
				'type'         => 'text',
			],
			'fusion_margin'         => [
				'key'          => 'fusion_margin',
				'title'        => esc_html__( 'Margin', 'fusion-builder' ),
				'description'  => esc_html__( 'Controls the margin for this widget container. Enter value including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
				'css_property' => 'margin',
				'type'         => 'text',
			],
			'fusion_bg_color'       => [
				'key'          => 'fusion_bg_color',
				'title'        => esc_html__( 'Background Color', 'fusion-builder' ),
				'description'  => esc_html__( 'Controls the background color for this widget container.', 'fusion-builder' ),
				'css_property' => 'background-color',
				'type'         => 'colorpickeralpha',
			],
			'fusion_bg_radius_size' => [
				'key'          => 'fusion_bg_radius_size',
				'title'        => esc_html__( 'Background Border Radius', 'fusion-builder' ),
				'description'  => esc_html__( 'Controls the background border radius for this widget container.', 'fusion-builder' ),
				'css_property' => 'border-radius',
				'type'         => 'text',
			],
			'fusion_border_size'    => [
				'key'          => 'fusion_border_size',
				'title'        => esc_html__( 'Border Size', 'fusion-builder' ),
				'description'  => esc_html__( 'Controls the border size for this widget container.', 'fusion-builder' ),
				'css_property' => 'border-width',
				'type'         => 'range',
				'min'          => 0,
				'max'          => 50,
				'step'         => 1,
				'value'        => 0,
			],
			'fusion_border_style'   => [
				'key'          => 'fusion_border_style',
				'title'        => esc_html__( 'Border Style', 'fusion-builder' ),
				'description'  => esc_html__( 'Controls the border style for this widget container.', 'fusion-builder' ),
				'css_property' => 'border-style',
				'type'         => 'select',
				'default'      => 'solid',
				'dependency'   => [
					[
						'element'  => 'fusion_border_size',
						'value'    => '0',
						'operator' => '!=',
					],
				],
				'choices'      => [
					'solid'  => esc_html__( 'Solid', 'fusion-builder' ),
					'dotted' => esc_html__( 'Dotted', 'fusion-builder' ),
					'dashed' => esc_html__( 'Dashed', 'fusion-builder' ),
				],
			],
			'fusion_border_color'   => [
				'key'          => 'fusion_border_color',
				'title'        => esc_html__( 'Border Color', 'fusion-builder' ),
				'description'  => esc_html__( 'Controls the border color for this widget container.', 'fusion-builder' ),
				'css_property' => 'border-color',
				'type'         => 'colorpickeralpha',
				'dependency'   => [
					[
						'element'  => 'fusion_border_size',
						'value'    => '0',
						'operator' => '!=',
					],
				],
			],
			'fusion_divider_color'  => [
				'key'          => 'fusion_divider_color',
				'title'        => esc_html__( 'Divider Color', 'fusion-builder' ),
				'description'  => esc_html__( 'Controls the color of dividers in this widget container. Leave empty for the Global Options default value or no dividers when using the vertical menu widget.', 'fusion-builder' ),
				'css_property' => 'border-color',
				'type'         => 'colorpickeralpha',
			],
			'fusion_align'          => [
				'key'          => 'fusion_align',
				'title'        => esc_html__( 'Content Align', 'fusion-builder' ),
				'description'  => esc_html__( 'Controls content alignment for this widget container. Inherit means it will inherit alignment from its parent element.', 'fusion-builder' ),
				'css_property' => 'text-align',
				'type'         => 'select',
				'choices'      => [
					''       => esc_html__( 'Inherit', 'fusion-builder' ),
					'left'   => esc_html__( 'Left', 'fusion-builder' ),
					'right'  => esc_html__( 'Right', 'fusion-builder' ),
					'center' => esc_html__( 'Center', 'fusion-builder' ),
				],
			],
			'fusion_align_mobile'   => [
				'key'          => 'fusion_align_mobile',
				'title'        => esc_html__( 'Mobile Content Align', 'fusion-builder' ),
				'description'  => esc_html__( 'Controls mobile content alignment for this widget container. Inherit means it will inherit alignment from its parent element.', 'fusion-builder' ),
				'css_property' => 'text-align',
				'type'         => 'select',
				'choices'      => [
					''       => esc_html__( 'Inherit', 'fusion-builder' ),
					'left'   => esc_html__( 'Left', 'fusion-builder' ),
					'right'  => esc_html__( 'Right', 'fusion-builder' ),
					'center' => esc_html__( 'Center', 'fusion-builder' ),
				],
			],
		];
	}

	/**
	 * Add widget options to form
	 *
	 * @since 5.3.0
	 * @access public
	 * @param object      $widget   WP_Widget object, passed by reference.
	 * @param null|string $return   Return null if new fields are added.
	 * @param array       $instance An array of the widget's settings.
	 */
	public function add_widget_styling_options( $widget, $return, $instance ) {
		$this->start_widget_options();
		?>
		<?php foreach ( $this->widget_options as $option ) : ?>
			<?php
			$field_css_classes   = 'option-field fusion-builder-option-container';
			$wrapper_css_classes = 'fusion-widget-' . $option['key'];

			if ( 'colorpickeralpha' === $option['type'] ) {
				$field_css_classes .= ' pyre_field avada-color colorpickeralpha';
			}

			$value = isset( $instance[ $option['key'] ] ) ? $instance[ $option['key'] ] : '';

			// For vertical menu widget, convert the old border_color option to the new fusion_divider_color one.
			if ( 'fusion_divider_color' === $option['key'] && empty( $instance['fusion_divider_color'] ) && ! empty( $instance['border_color'] ) ) {
				$value = $instance['border_color'];
			}

			$option['description'] = isset( $option['description'] ) ? $option['description'] : '';

			if ( 'range' === $option['type'] ) {
				$wrapper_css_classes .= ' avada-range';
				$value                = '' !== $value ? (int) $value : $option['value'];
			} elseif ( 'radio_button_set' === $option['type'] ) {
				$value = '' !== $value ? $value : $option['default'];
			}
			?>
			<li class="fusion-builder-option <?php echo esc_attr( $wrapper_css_classes ); ?>">
				<div class="option-details">
					<h3><?php echo esc_html( $option['title'] ); ?></h3>
					<p class="description"><?php echo esc_html( $option['description'] ); ?>
					<?php
					if ( 'range' === $option['type'] && isset( $option['value'] ) && '' !== $option['value'] ) {
						echo '<span class="pyre-default-reset" style="display:none;"><a href="#" id="default-' . esc_attr( $widget->get_field_id( $option['key'] ) ) . '" class="fusion-range-default fusion-hide-from-atts" type="radio" name="' . esc_attr( $widget->get_field_id( $option['key'] ) ) . '" value="" data-default="' . esc_attr( $option['value'] ) . '">' . esc_attr__( 'Reset to default.', 'fusion-builder' ) . '</a><span>' . esc_attr__( 'Using default value.', 'fusion-builder' ) . '</span></span>';
					}
					?>
					</p>
				</div>

				<div class="<?php echo esc_attr( $field_css_classes ); ?>">
				<?php if ( 'select' === $option['type'] ) : ?>
					<select id="<?php echo esc_attr( $widget->get_field_id( $option['key'] ) ); ?>"
							name="<?php echo esc_attr( $widget->get_field_name( $option['key'] ) ); ?>"
							class="awb_widget_options_select"
					>
					<?php foreach ( $option['choices'] as $val => $title ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>" <?php selected( esc_attr( $val ), esc_attr( $value ) ); ?>><?php echo esc_html( $title ); ?></option>
					<?php endforeach; ?>
					</select>
				<?php elseif ( 'colorpickeralpha' === $option['type'] ) : ?>
					<input type="text" id="<?php echo esc_attr( $widget->get_field_id( $option['key'] ) ); ?>"
							class="fusion-builder-color-picker-hex color-picker"
							data-alpha="true"
							name="<?php echo esc_attr( $widget->get_field_name( $option['key'] ) ); ?>"
							value="<?php echo esc_attr( $value ); ?>"
						/>
				<?php elseif ( 'range' === $option['type'] ) : ?>
					<input
						type="text"
						name="range-<?php echo esc_attr( $widget->get_field_name( $option['key'] ) ); ?>"
						id="range-<?php echo esc_attr( $widget->get_field_id( $option['key'] ) ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
						class="fusion-slider-input <?php echo ( isset( $option['value'] ) && '' !== $option['value'] ) ? 'fusion-hide-from-atts' : ''; ?>" />
					<div
						class="fusion-slider-container"
						data-id="<?php echo esc_attr( $widget->get_field_id( $option['key'] ) ); ?>"
						data-min="<?php echo esc_attr( $option['min'] ); ?>"
						data-max="<?php echo esc_attr( $option['max'] ); ?>"
						data-step="<?php echo esc_attr( $option['step'] ); ?>">
					</div>
					<?php if ( isset( $option['value'] ) && '' !== $option['value'] ) : ?>
						<input type="hidden"
							id="<?php echo esc_attr( $widget->get_field_name( $option['key'] ) ); ?>"
							name="<?php echo esc_attr( $widget->get_field_name( $option['key'] ) ); ?>"
							value="<?php echo esc_attr( $value ); ?>"
							class="fusion-hidden-value" />
					<?php endif; ?>
				<?php elseif ( 'radio_button_set' === $option['type'] ) : ?>
					<div class="pyre_field avada-buttonset">
						<div class="fusion-form-radio-button-set ui-buttonset">
							<input type="hidden" id="<?php echo esc_attr( $widget->get_field_id( $option['key'] ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( $option['key'] ) ); ?>" value="<?php echo esc_attr( $value ); ?>" class="button-set-value" />
							<?php foreach ( $option['choices'] as $key => $title ) : ?>
								<?php $selected = ( $key === $value ) ? ' ui-state-active' : ''; ?>
								<a href="#" class="ui-button buttonset-item<?php echo esc_attr( $selected ); ?>" data-value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $title ); ?></a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php else : ?>
					<input type="text" id="<?php echo esc_attr( $widget->get_field_id( $option['key'] ) ); ?>"
						name="<?php echo esc_attr( $widget->get_field_name( $option['key'] ) ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
					/>
				<?php endif; ?>
				</div>
			</li>
			<?php endforeach; ?>
		<?php
		$this->end_widget_options();
	}

	/**
	 * Open widget options container.
	 * 
	 * @since 5.3.0
	 * @access private
	 * @return void 
	 */
	private function start_widget_options() {
		?>
		<div class="fusion-menu-options-container">
			<a class="button button-primary button-large fusion-menu-option-trigger" href="#">
				<?php esc_html_e( 'Avada Widget Options', 'fusion-builder' ); ?>
			</a>
			<div class="fusion_builder_modal_overlay" style="display:none"></div>
			<div class="fusion-options-holder fusion-builder-modal-settings-container" style="display:none">
				<div class="fusion-builder-modal-container fusion_builder_module_settings">
					<div class="fusion-builder-modal-top-container fusion-widget-settings-top">
						<h2>
							<?php esc_attr_e( 'Avada Widget Options', 'fusion-builder' ); ?>
							<div class="fusion-modal-description">
								<?php esc_html_e( 'These options apply to the widget container, not the actual widget.', 'fusion-builder' ); ?>
							</div>
						</h2>
						<div class="fusion-builder-modal-close fusiona-plus2"></div>
					</div>
					<div class="fusion-builder-modal-bottom-container">
						<a href="#" class="fusion-builder-modal-save" ><span><?php esc_attr_e( 'Save', 'fusion-builder' ); ?></span></a>
						<a href="#" class="fusion-builder-modal-close" ><span><?php esc_attr_e( 'Cancel', 'fusion-builder' ); ?></span></a>
					</div>
					<div class="fusion-builder-main-settings fusion-builder-main-settings-full">
						<ul class="fusion-builder-module-settings">
							<?php
	}

	/**
	 * Close widget options container.
	 * 
	 * @since 5.3.0
	 * @access public
	 * @return void
	 */
	public function end_widget_options() {
		?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Save widget options.
	 *
	 * @since 5.3.0
	 * @access public
	 * @param array     $instance     An array of current widget's settings.
	 * @param array     $new_instance An array of new widget's settings.
	 * @param array     $old_instance An array of old widget's settings.
	 * @param WP_Widget $widget       The current widget instance.
	 * @return mixed
	 */
	public function save_widget_styling_options( $instance, $new_instance, $old_instance, $widget ) {

		foreach ( $this->widget_options as $option ) {

			// For vertical menu widget, convert the old border_color option to the new fusion_divider_color one.
			if ( 'fusion_divider_color' === $option['key'] && empty( $new_instance['fusion_divider_color'] ) && ! isset( $old_instance['fusion_divider_color'] ) && ! empty( $new_instance['border_color'] ) ) {
				$new_instance['fusion_divider_color'] = $new_instance['border_color'];
				unset( $new_instance['border_color'] );
			}

			$instance[ $option['key'] ] = ! empty( $new_instance[ $option['key'] ] ) ? sanitize_text_field( $new_instance[ $option['key'] ] ) : '';
		}

		return $instance;
	}

	/**
	 * Prints widget styles based on options.
	 * Desktop styles are printed using style element attribute.
	 * Mobile styles are printed inline.
	 *
	 * @since 5.3.0
	 * @access public
	 * @param array $params Widget params.
	 * @return mixed
	 */
	public function add_widget_styles( $params ) {

		global $wp_registered_widgets;

		if ( ! isset( $params[0] ) ) {
			return $params;
		}

		$sidebar_id = $params[0]['id']; // Get the id for the current sidebar we're processing.
		$widget_id  = $params[0]['widget_id'];
		$widget_obj = $wp_registered_widgets[ $widget_id ];
		$widget_num = $widget_obj['params'][0]['number'];
		$widget_opt = $this->get_widget_opt( $widget_obj );

		$style          = '';
		$style_mobile   = '';
		$divider_styles = '';

		// If calendar and no alignment set, set to default.
		if ( isset( $widget_opt[ $widget_num ] ) && ! isset( $widget_opt[ $widget_num ]['fusion_align'] ) && false !== strpos( $widget_id, 'calendar' ) ) {
			$widget_opt[ $widget_num ]['fusion_align'] = '';
		}

		foreach ( $this->widget_options as $option ) {
			if ( isset( $widget_opt[ $widget_num ][ $option['key'] ] ) ) {

				if ( 'fusion_align' === $option['key'] && false !== strpos( $widget_id, 'calendar' ) ) {
					$alignment                  = ( '' === $widget_opt[ $widget_num ][ $option['key'] ] ) ? 'default' : $widget_opt[ $widget_num ][ $option['key'] ];
					$params[0]['before_widget'] = str_replace( 'class="', 'class="fusion-widget-align-' . $alignment . ' ', $params[0]['before_widget'] );
				}

				if ( 'fusion_align' === $option['key'] || 'fusion_align_mobile' === $option['key'] ) {

					if ( 'fusion_align_mobile' === $option['key'] && '' === $widget_opt[ $widget_num ][ $option['key'] ] && '' !== $widget_opt[ $widget_num ]['fusion_align'] ) {

						if ( false !== strpos( $sidebar_id, 'avada-footer-widget-' ) && Avada()->settings->get( 'footer_widgets_center_content' ) ) {
							$widget_opt[ $widget_num ][ $option['key'] ] = 'center';
						} else {
							$widget_opt[ $widget_num ][ $option['key'] ] = 'initial';
						}
					}

					if ( '' !== $widget_opt[ $widget_num ][ $option['key'] ] ) {
						$alignment = ( '' === $widget_opt[ $widget_num ][ $option['key'] ] ) ? 'default' : $widget_opt[ $widget_num ][ $option['key'] ];
						$css_class = ( 'fusion_align' === $option['key'] ? 'fusion-widget-align-' : 'fusion-widget-mobile-align-' ) . $alignment;

						$params[0]['before_widget'] = str_replace( 'class="', 'class="' . esc_attr( $css_class ) . ' ', $params[0]['before_widget'] );
					}
				}

				if ( '' !== $widget_opt[ $widget_num ][ $option['key'] ] && isset( $option['css_property'] ) ) {
					if ( false === strpos( $option['key'], 'mobile' ) ) {
						if ( 'border-width' === $option['css_property'] ) {
							$widget_opt[ $widget_num ][ $option['key'] ] = (int) $widget_opt[ $widget_num ][ $option['key'] ] . 'px';
						}
						$style .= $option['css_property'] . ': ' . $widget_opt[ $widget_num ][ $option['key'] ] . ';';

						if ( 'border-radius' === $option['css_property'] ) {
							$style .= 'overflow:hidden;';
						}
					} else {
						$style_mobile .= '#' . $widget_id . '{' . $option['css_property'] . ':' . $widget_opt[ $widget_num ][ $option['key'] ] . ' !important;}';
					}
				}
			}
		}

		// Set border color to transparent and border size to 0px it those field were left empty, but border style isn't.
		if ( isset( $widget_opt[ $widget_num ]['fusion_border_style'] ) && '' !== $widget_opt[ $widget_num ]['fusion_border_style'] ) {
			if ( ! isset( $widget_opt[ $widget_num ]['fusion_border_color'] ) || '' === $widget_opt[ $widget_num ]['fusion_border_color'] ) {
				$style .= 'border-color:transparent;';
			}

			if ( ! isset( $widget_opt[ $widget_num ]['fusion_border_size'] ) || '' === $widget_opt[ $widget_num ]['fusion_border_size'] ) {
				$style .= 'border-width:0px;';
			}
		}

		if ( ! empty( $style ) ) {
			$params[0]['before_widget'] = str_replace( '>', ' style="' . esc_attr( $style ) . '">', $params[0]['before_widget'] );
		}

		if ( isset( $widget_opt[ $widget_num ]['fusion_divider_color'] ) && '' !== $widget_opt[ $widget_num ]['fusion_divider_color'] ) {
			$fusion_divider_color = Fusion_Sanitize::color( $widget_opt[ $widget_num ]['fusion_divider_color'] );
			$divider_styles       = '#' . $widget_id . ' li { border-color:' . $fusion_divider_color . ';}';

			if ( false !== strpos( $params[0]['widget_id'], 'tag_cloud' ) ) {
				$divider_styles .= '#' . $widget_id . ' .tagcloud a { border-color:' . $fusion_divider_color . ';}';
			} elseif ( false !== strpos( $params[0]['widget_id'], 'menu-widget' ) ) {
				$divider_styles .= '#' . $widget_id . ' .fusion-widget-menu ul li a:after { color:' . $fusion_divider_color . ';}';
			}
		}

		// Special handling for vertical menu widget, to take into account the deprecated border_color option.
		if ( false !== strpos( $params[0]['widget_id'], 'avada-vertical-menu-widget' ) ) {
			if ( isset( $widget_opt[ $widget_num ]['border_color'] ) && ! isset( $widget_opt[ $widget_num ]['fusion_divider_color'] ) ) {
				$widget_opt[ $widget_num ]['fusion_divider_color'] = $widget_opt[ $widget_num ]['border_color'];
			}

			if ( '' !== $widget_opt[ $widget_num ]['fusion_divider_color'] ) {
				$fusion_divider_color = Fusion_Sanitize::color( $widget_opt[ $widget_num ]['fusion_divider_color'] );

				$divider_styles .= '#' . $widget_id . ' .menu { border-right-color:' . $fusion_divider_color . ' !important;border-top-color:' . $fusion_divider_color . ' !important;}';
				$divider_styles .= '#' . $widget_id . ' .menu li a { border-bottom-color:' . $fusion_divider_color . ' !important; }';
				$divider_styles .= '#' . $widget_id . ' .right .menu { border-left-color:' . $fusion_divider_color . ' !important; }';
			} else {
				$divider_styles .= '#' . $widget_id . ' > ul.menu { margin-top: -8px; }'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}

		if ( ! empty( $style_mobile ) ) {
			$style_mobile = '@media (max-width: ' . Avada()->settings->get( 'content_break_point' ) . 'px){' . $style_mobile . '}';
		}

		if ( ! empty( $style_mobile ) || ! empty( $divider_styles ) ) {
			$params[0]['before_widget'] = '<style type="text/css" data-id="' . $widget_id . '">' . $divider_styles . $style_mobile . '</style>' . $params[0]['before_widget'];
		}

		return $params;
	}

	/**
	 * Get widget options.
	 *
	 * @since 5.3.0
	 * @access private
	 * @param object $widget WP_Widget object.
	 * @return mixed|void
	 */
	private function get_widget_opt( $widget ) {
		$widget_opt = get_option( $widget['callback'][0]->option_name );

		return $widget_opt;
	}

	/**
	 * Creates the JS object for widget options.
	 *
	 * @since 6.0.0
	 * @access public
	 * @return void.
	 */
	public function widget_options_object() {
		echo '<script>var widgetOptions = ' . wp_json_encode( $this->widget_options ) . ';</script>';
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

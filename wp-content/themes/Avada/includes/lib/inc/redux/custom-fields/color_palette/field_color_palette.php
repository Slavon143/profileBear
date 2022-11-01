<?php
/**
 * Custom Color Palette field Avada.
 *
 * @package Fusion-Library
 * @since 2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FusionReduxFramework_color_palette' ) ) {

	/**
	 * The field class.
	 *
	 * @since 2.0
	 */
	class FusionReduxFramework_color_palette {

		/**
		 * Field Constructor.
		 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
		 *
		 * @since FusionRedux_Options 2.0.1
		 */
		public function __construct( $field = array(), $value = '', $parent = null ) {
			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;
		}

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since FusionRedux_Options 2.0.1
		 */
		public function render() {
			$value = $this->value;
			if ( empty( $this->value ) || ! is_array( $this->value ) ) {
				$value = $this->field['default'];
			} else {
				$value = array_merge( $this->field['default'], $value );
			}
			?>

			<div class="awb-color-palette-color-template" style="display:none !important">
				<?php $this->render_color_template(); ?>
			</div>

			<ul id="<?php echo esc_attr( $this->field['id'] ); ?>-list" class="awb-color-palette-list">
				<?php
					foreach ( $value as $color_slug => $color_data ) :
						$this->render_color_item( $color_slug, $color_data );
					endforeach;
				?>
			</ul>

			<div class="awb-color-palette-add-btn-wrapper">
				<button class="button button-primary awb-color-palette-add-btn">
					<?php esc_html_e( 'Add New Color', 'Avada' ); ?>
				</button>
			</div>
			<?php
		}

		/**
		 * Generate the template for a color to be added. The strings marked
		 * with "___" before and after, are marked to be replaced in JS.
		 *
		 * Note: If you add a new string to be replaced, be sure that it's also
		 * replaced in JS.
		 *
		 * @since 3.6
		 */
		private function render_color_template() {
			$color_slug = "___color_slug___";
			$color_data = array(
				'color' => '#ffffff',
				'label' => esc_html__( 'New Color', 'Avada' ),
			);

			echo '<script type="text/template">';
			$this->render_color_item( $color_slug, $color_data );
			echo '</script>';
		}

		/**
		 * Render a new LI tag item for the color.
		 *
		 * Note: If you add a new required parameter/argument, be sure that is
		 * also added in render_color_template(), and modified in JS(if needed).
		 *
		 * @since 3.6
		 */
		private function render_color_item( $color_slug, $color_data ) {
			$option_base = $this->field['name'] . $this->field['name_suffix'] . '[' . $color_slug . ']';
			$is_removable_color = ( isset( $this->field['default'][ $color_slug ] ) ? false : true );
			?>
			<li class="fusion-color-palette-item" data-slug="<?php echo esc_attr( $color_slug ); ?>">
				<div class="awb-palette-title">
					<span class="preview" style="background-color:<?php echo esc_attr( $color_data['color'] ); ?>;"></span>
					<span class="label"><?php echo esc_html( $color_data['label'] ); ?></span>
					<div class="actions">
						<span class="fusiona-pen"></span>
						<?php if ( $is_removable_color ) : ?>
							<span class="fusiona-trash-o"></span>
						<?php endif; ?>
					</div>
				</div>
				<div class="awb-palette-content">
					<label for="name-<?php echo esc_attr( $color_slug ); ?>" class="color-name-label"><?php esc_html_e( 'Color Name', 'Avada' ); ?></label>
					<input class="color-name" name="<?php echo esc_attr( $option_base . '[label]' ); ?>" id="name-<?php echo esc_attr( $color_slug ); ?>" type="text" value="<?php echo esc_attr( $color_data['label'] ); ?>"/>
					<label for="color-<?php echo esc_attr( $color_slug ); ?>" class="color-code-label"><?php esc_html_e( 'Color Code', 'Avada' ); ?></label>
					<input id="color-<?php echo esc_attr( $color_slug ); ?>" class="color-picker awb-picker awb-palette-picker" type="text" value="<?php echo esc_attr( $color_data['color'] ); ?>" data-alpha="true" data-global="false" name="<?php echo esc_attr( $option_base . '[color]' ); ?>">
				</div>
			</li>
			<?php
		}

		/**
		 * Enqueue admin assets.
		 *
		 * @since 2.0
		 * @return void
		 */
		public function enqueue() {
			global $fusion_library_latest_version;
			wp_enqueue_script(
				'fusionredux-field-color-palette-js',
				trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/custom-fields/color_palette/field_color_palette.js',
				array( 'jquery', 'fusionredux-js' ),
				$fusion_library_latest_version,
				true
			);
			wp_enqueue_style(
				'fusionredux-field-color-palette-css',
				trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/custom-fields/color_palette/field_color_palette.css',
				array(),
				$fusion_library_latest_version,
				'all'
			);
		}
	}
}

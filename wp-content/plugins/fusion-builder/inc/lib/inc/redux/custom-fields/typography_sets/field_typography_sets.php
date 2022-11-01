<?php
/**
 * Custom Typography Sets field Avada.
 *
 * @package Fusion-Library
 * @since 2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FusionReduxFramework_typography_sets' ) ) {

	/**
	 * The field class.
	 *
	 * @since 2.0
	 */
	class FusionReduxFramework_typography_sets {

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

				// Clean out empty or invalid values.
				foreach ( $value as $set => $data ) {
					if ( is_array( $data ) ) {
						foreach ( $data as $key => $data_value ) {
							if ( '' === $data_value ) {
								unset( $value[ $set ][ $key ] );
							}
						}
					}
				}
				$value = fusion_array_merge_recursive( $this->field['default'], $value );
			}

			// Template for each set.
			include FUSION_LIBRARY_PATH . '/inc/fusion-app/templates/typography-set.php';

			echo '<script>var awbSetValue = \'' . wp_json_encode( $value, JSON_HEX_APOS | JSON_HEX_QUOT ) . '\';</script>';
			?>

			<div class="awb-typography-sets-wrapper <?php echo esc_attr( $this->field['id'] ); ?>">
				<ul id="<?php echo esc_attr( $this->field['id'] ); ?>-list" class="awb-typography-sets" data-option="<?php echo $this->field['name'] . $this->field['name_suffix']; ?>"></ul>

				<div class="awb-typography-sets-add-btn-wrapper">
					<button class="button button-primary awb-typography-set-add-btn">
						<span class="fusiona-plus"></span>
						<?php esc_html_e( 'Add New Typography', 'fusion-builder' ); ?>
					</button>
				</div>
			</div>
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
				'fusionredux-field-typo-sets-js',
				trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/custom-fields/typography_sets/field_typography_sets.js',
				array( 'jquery', 'fusionredux-js' ),
				$fusion_library_latest_version,
				true
			);
		}
	}
}

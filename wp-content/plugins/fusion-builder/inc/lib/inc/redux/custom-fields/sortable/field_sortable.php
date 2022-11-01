<?php
/**
 * Custom Sortable field Avada.
 *
 * @package Fusion-Library
 * @since 2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FusionReduxFramework_sortable' ) ) {

	/**
	 * The field class.
	 *
	 * @since 2.0
	 */
	class FusionReduxFramework_sortable {

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
			$value = ( empty( $this->value ) || ! is_string( $this->value ) ) ? $this->field['default'] : $this->value;
			$value = explode( ',', $value );
			?>
			<ul id="<?php echo esc_attr( $this->field['id'] ); ?>-list" class="fusionredux-sortable" data-sortable-id="<?php echo esc_attr( $this->field['id'] ); ?>">
				<?php foreach ( $value as $key ) : ?>
					<?php
					$key   = trim( $key );
					$label = ( isset( $this->field['choices'][ $key ] ) ) ? $this->field['choices'][ $key ] : false;
					if ( ! $label ) {
						continue;
					}
					?>
					<li>
						<span class="item" data-sortable-id="<?php echo esc_attr( $this->field['id'] ); ?>" data-sortable-item="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $label ); ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
			<input
				id="<?php echo esc_attr( $this->field['id'] ); ?>-hidden-value-csv"
				type="hidden"
				name="<?php echo esc_attr( $this->field['name'] . $this->field['name_suffix'] ); ?>"
				value="<?php echo esc_attr( implode( ',', $value ) ); ?>"/>
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
				'fusionredux-field-sortable-js',
				trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/custom-fields/sortable/field_sortable.js',
				array( 'jquery', 'fusionredux-js', 'jquery-ui-sortable' ),
				$fusion_library_latest_version,
				true
			);
			wp_enqueue_style(
				'fusionredux-field-sortable-css',
				trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/custom-fields/sortable/field_sortable.css',
				array(),
				$fusion_library_latest_version,
				'all'
			);
		}
	}
}

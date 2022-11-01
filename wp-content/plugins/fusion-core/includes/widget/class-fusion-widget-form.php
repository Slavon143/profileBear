<?php
/**
 * Widget Class.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada Core
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Widget class.
 */
class Fusion_Widget_Form extends WP_Widget {

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		$widget_ops  = [
			'classname'   => 'form',
			'description' => __( 'Adds a horizontal navigation', 'fusion-core' ),
		];
		$control_ops = [
			'id_base' => 'form-widget',
		];
		parent::__construct( 'form-widget', __( 'Avada: Form' ), $widget_ops, $control_ops );

	}

	/**
	 * Echoes the widget content.
	 *
	 * @access public
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {

		extract( $args );

		echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput

		// Get menu.
		$form_id = ! empty( $instance['form_id'] ) ? $instance['form_id'] : false;

		if ( ! $form_id ) {
			return;
		}

		if ( is_int( $args['widget_id'] ) ) {
			$args['widget_id'] = 'form-widget-' . $args['widget_id'];
		}

		echo do_shortcode( '[fusion_form form_post_id="' . absint( $form_id ) . '" class="" id="" /]' );

		echo $after_widget; // phpcs:ignore WordPress.Security.EscapeOutput

	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * This function should check that `$new_instance` is set correctly. The newly-calculated
	 * value of `$instance` should be returned. If false is returned, the instance won't be
	 * saved/updated.
	 *
	 * @access public
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['form_id'] = isset( $new_instance['form_id'] ) ? $new_instance['form_id'] : '';

		return $instance;

	}

	/**
	 * Outputs the settings update form.
	 *
	 * @access public
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {

		$defaults = [
			'form_id' => '',
		];

		$instance         = wp_parse_args( (array) $instance, $defaults );
		$selected_form_id = isset( $instance['form_id'] ) ? $instance['form_id'] : '';

		// Get forms.
		$forms = class_exists( 'Fusion_Builder_Form_Helper' ) && method_exists( 'Fusion_Builder_Form_Helper', 'fusion_form_creator_form_list' ) ? Fusion_Builder_Form_Helper::fusion_form_creator_form_list() : [];
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'form_id' ) ); ?>"><?php esc_attr_e( 'Select Form:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'form_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'form_id' ) ); ?>" class="widefat" style="width:100%;">
				<option value="0">&mdash; <?php esc_attr_e( 'Select', 'fusion-core' ); ?> &mdash;</option>
				<?php foreach ( $forms as $form_id => $form_name ) : ?>
					<option value="<?php echo esc_attr( $form_id ); ?>" <?php selected( $selected_form_id, $form_id ); ?>>
						<?php echo esc_html( $form_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php

	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

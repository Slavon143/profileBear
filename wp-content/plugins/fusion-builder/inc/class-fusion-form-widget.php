<?php
/**
 * Handles the form creator widget.
 *
 * @package fusion-builder
 * @since 3.1
 */

/**
 * Widget class.
 */
class Fusion_Form_Widget extends WP_Widget {

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		$widget_ops = [
			'classname'   => 'fusion_form_widget',
			'description' => __( 'Display forms in widget area.', 'fusion-builder' ),
		];

		$control_ops = [
			'id_base' => 'fusion_form-widget',
		];

		parent::__construct( 'fusion_form-widget', __( 'Fusion Builder: Form', 'fusion-core' ), $widget_ops, $control_ops );

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

		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract

		$title        = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
		$form_post_id = isset( $instance['form_post_id'] ) ? $instance['form_post_id'] : '';

		echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput

		if ( $title ) {
			echo $before_title . $title . $after_title; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>

		<div class="fusion-form-widget clearfix">
			<?php
			echo do_shortcode( '[fusion_form form_post_id="' . $form_post_id . '" options="default" /]' );
			?>
		</div>
		<?php

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

		$instance['title']        = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['form_post_id'] = isset( $new_instance['form_post_id'] ) ? $new_instance['form_post_id'] : '';

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
			'title'        => __( 'Form Creator', 'fusion-builder' ),
			'form_post_id' => '',
		];
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'fusion-builder' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'form_post_id' ) ); ?>"><?php esc_attr_e( 'Select Form:', 'fusion-builder' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'form_post_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'form_post_id' ) ); ?>">
				<?php
				$value = esc_attr( $instance['form_post_id'] );
				$forms = Fusion_Builder_Form_Helper::fusion_form_creator_form_list();
				?>
				<?php foreach ( $forms as $id => $option ) : ?>
					<option value="<?php echo esc_attr( $id ); ?>"<?php echo ( $id === $value ) ? ' selected="selected"' : ''; ?>>
						<?php echo esc_html( $option ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

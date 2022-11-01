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
class Fusion_Widget_Ad_125_125 extends WP_Widget {

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		$widget_ops  = [
			'classname'   => 'fusion-ad-125-125 ad_125_125',
			'description' => __( 'Add 125x125 ads.', 'fusion-core' ),
		];
		$control_ops = [
			'id_base' => 'ad_125_125-widget',
		];

		parent::__construct( 'ad_125_125-widget', __( 'Avada: 125x125 Ads', 'fusion-core' ), $widget_ops, $control_ops );

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

		if ( ! isset( $instance['ad_125_link_target'] ) ) {
			$instance['ad_125_link_target'] = '_self';
		}

		echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput
		?>

		<div class="fusion-image-row">
			<?php $ads = [ 1, 2, 3, 4 ]; ?>
			<?php foreach ( $ads as $ad_count ) : ?>
				<?php if ( $instance[ 'ad_125_img_' . $ad_count ] && $instance[ 'ad_125_link_' . $ad_count ] ) : ?>
					<?php
					$link_classes = 'fusion-image-link';
					if ( isset( $instance[ 'ad_125_hover_type_' . $ad_count ] ) ) {
						$link_classes .= ' hover-type-' . $instance[ 'ad_125_hover_type_' . $ad_count ];
					}
					?>
					<div class="fusion-image-holder">
						<a class="<?php echo esc_attr( $link_classes ); ?>" href="<?php echo esc_url( $instance[ 'ad_125_link_' . $ad_count ] ); ?>" target="<?php echo esc_attr( $instance['ad_125_link_target'] ); ?>">
							<img src="<?php echo esc_url( $instance[ 'ad_125_img_' . $ad_count ] ); ?>" alt="" width="123" height="123" />
						</a>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
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

		$instance['ad_125_img_1']        = isset( $new_instance['ad_125_img_1'] ) ? $new_instance['ad_125_img_1'] : '';
		$instance['ad_125_link_1']       = isset( $new_instance['ad_125_link_1'] ) ? $new_instance['ad_125_link_1'] : '';
		$instance['ad_125_hover_type_1'] = isset( $new_instance['ad_125_hover_type_1'] ) ? $new_instance['ad_125_hover_type_1'] : '';
		$instance['ad_125_img_2']        = isset( $new_instance['ad_125_img_2'] ) ? $new_instance['ad_125_img_2'] : '';
		$instance['ad_125_link_2']       = isset( $new_instance['ad_125_link_2'] ) ? $new_instance['ad_125_link_2'] : '';
		$instance['ad_125_hover_type_2'] = isset( $new_instance['ad_125_hover_type_2'] ) ? $new_instance['ad_125_hover_type_2'] : '';
		$instance['ad_125_img_3']        = isset( $new_instance['ad_125_img_3'] ) ? $new_instance['ad_125_img_3'] : '';
		$instance['ad_125_link_3']       = isset( $new_instance['ad_125_link_3'] ) ? $new_instance['ad_125_link_3'] : '';
		$instance['ad_125_hover_type_3'] = isset( $new_instance['ad_125_hover_type_3'] ) ? $new_instance['ad_125_hover_type_3'] : '';
		$instance['ad_125_img_4']        = isset( $new_instance['ad_125_img_4'] ) ? $new_instance['ad_125_img_4'] : '';
		$instance['ad_125_link_4']       = isset( $new_instance['ad_125_link_4'] ) ? $new_instance['ad_125_link_4'] : '';
		$instance['ad_125_hover_type_4'] = isset( $new_instance['ad_125_hover_type_4'] ) ? $new_instance['ad_125_hover_type_4'] : '';
		$instance['ad_125_link_target']  = isset( $new_instance['ad_125_link_target'] ) ? $new_instance['ad_125_link_target'] : '_self';

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
			'ad_125_img_1'        => '',
			'ad_125_link_1'       => '',
			'ad_125_hover_type_1' => '',
			'ad_125_img_2'        => '',
			'ad_125_link_2'       => '',
			'ad_125_hover_type_2' => '',
			'ad_125_img_3'        => '',
			'ad_125_link_3'       => '',
			'ad_125_hover_type_3' => '',
			'ad_125_img_4'        => '',
			'ad_125_link_4'       => '',
			'ad_125_hover_type_4' => '',
			'ad_125_link_target'  => '_self',

		];

		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<?php /* translators: Number.*/ ?>
		<p><strong><?php printf( esc_html__( 'Ad %s', 'fusion-core' ), '1' ); ?></strong></p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad_125_img_1' ) ); ?>"><?php esc_html_e( 'Image Ad Link:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'ad_125_img_1' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_125_img_1' ) ); ?>" value="<?php echo esc_attr( $instance['ad_125_img_1'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad_125_link_1' ) ); ?>"><?php esc_html_e( 'Ad Link:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'ad_125_link_1' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_125_link_1' ) ); ?>" value="<?php echo esc_attr( $instance['ad_125_link_1'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad_125_hover_type_1' ) ); ?>"><?php esc_html_e( 'Image Hover Type:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'ad_125_hover_type_1' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_125_hover_type_1' ) ); ?>" class="widefat" style="width:100%;">
				<option value="" <?php echo ( '' === $instance['ad_125_hover_type_1'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'None', 'fusion-core' ); ?></option>
				<option value="zoomin" <?php echo ( 'zoomin' === $instance['ad_125_hover_type_1'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Zoom In', 'fusion-core' ); ?></option>
				<option value="zoomout" <?php echo ( 'zoomout' === $instance['ad_125_hover_type_1'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Zoon Out', 'fusion-core' ); ?></option>
				<option value="liftup" <?php echo ( 'liftup' === $instance['ad_125_hover_type_1'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Lift Up', 'fusion-core' ); ?></option>
			</select>
		</p>
		<?php /* translators: Number.*/ ?>
		<p><strong><?php printf( esc_html__( 'Ad %s', 'fusion-core' ), '2' ); ?></strong></p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad_125_img_2' ) ); ?>"><?php esc_html_e( 'Image Ad Link:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'ad_125_img_2' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_125_img_2' ) ); ?>" value="<?php echo esc_attr( $instance['ad_125_img_2'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad_125_link_2' ) ); ?>"><?php esc_html_e( 'Ad Link:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'ad_125_link_2' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_125_link_2' ) ); ?>" value="<?php echo esc_attr( $instance['ad_125_link_2'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad_125_hover_type_2' ) ); ?>"><?php esc_html_e( 'Image Hover Type:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'ad_125_hover_type_2' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_125_hover_type_2' ) ); ?>" class="widefat" style="width:100%;">
				<option value="" <?php echo ( '' === $instance['ad_125_hover_type_2'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'None', 'fusion-core' ); ?></option>
				<option value="zoomin" <?php echo ( 'zoomin' === $instance['ad_125_hover_type_2'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Zoom In', 'fusion-core' ); ?></option>
				<option value="zoomout" <?php echo ( 'zoomout' === $instance['ad_125_hover_type_2'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Zoon Out', 'fusion-core' ); ?></option>
				<option value="liftup" <?php echo ( 'liftup' === $instance['ad_125_hover_type_2'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Lift Up', 'fusion-core' ); ?></option>
			</select>
		</p>
		<?php /* translators: Number.*/ ?>
		<p><strong><?php printf( esc_html__( 'Ad %s', 'fusion-core' ), '3' ); ?></strong></p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad_125_img_3' ) ); ?>"><?php esc_html_e( 'Image Ad Link:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'ad_125_img_3' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_125_img_3' ) ); ?>" value="<?php echo esc_attr( $instance['ad_125_img_3'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad_125_link_3' ) ); ?>"><?php esc_html_e( 'Ad Link:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'ad_125_link_3' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_125_link_3' ) ); ?>" value="<?php echo esc_attr( $instance['ad_125_link_3'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad_125_hover_type_3' ) ); ?>"><?php esc_html_e( 'Image Hover Type:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'ad_125_hover_type_3' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_125_hover_type_3' ) ); ?>" class="widefat" style="width:100%;">
				<option value="" <?php echo ( '' === $instance['ad_125_hover_type_3'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'None', 'fusion-core' ); ?></option>
				<option value="zoomin" <?php echo ( 'zoomin' === $instance['ad_125_hover_type_3'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Zoom In', 'fusion-core' ); ?></option>
				<option value="zoomout" <?php echo ( 'zoomout' === $instance['ad_125_hover_type_3'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Zoon Out', 'fusion-core' ); ?></option>
				<option value="liftup" <?php echo ( 'liftup' === $instance['ad_125_hover_type_3'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Lift Up', 'fusion-core' ); ?></option>
			</select>
		</p>
		<?php /* translators: Number.*/ ?>
		<p><strong><?php printf( esc_html__( 'Ad %s', 'fusion-core' ), '4' ); ?></strong></p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad_125_img_4' ) ); ?>"><?php esc_html_e( 'Image Ad Link:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'ad_125_img_4' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_125_img_4' ) ); ?>" value="<?php echo esc_attr( $instance['ad_125_img_4'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad_125_link_4' ) ); ?>"><?php esc_html_e( 'Ad Link:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'ad_125_link_4' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_125_link_4' ) ); ?>" value="<?php echo esc_attr( $instance['ad_125_link_4'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad_125_hover_type_4' ) ); ?>"><?php esc_html_e( 'Image Hover Type:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'ad_125_hover_type_4' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_125_hover_type_4' ) ); ?>" class="widefat" style="width:100%;">
				<option value="" <?php echo ( '' === $instance['ad_125_hover_type_4'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'None', 'fusion-core' ); ?></option>
				<option value="zoomin" <?php echo ( 'zoomin' === $instance['ad_125_hover_type_4'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Zoom In', 'fusion-core' ); ?></option>
				<option value="zoomout" <?php echo ( 'zoomout' === $instance['ad_125_hover_type_4'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Zoon Out', 'fusion-core' ); ?></option>
				<option value="liftup" <?php echo ( 'liftup' === $instance['ad_125_hover_type_4'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Lift Up', 'fusion-core' ); ?></option>
			</select>
		</p>
		<p><strong><?php esc_html_e( 'General Options', 'fusion-core' ); ?></strong></p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ad_125_link_target' ) ); ?>"><?php esc_html_e( 'Link Target:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'ad_125_link_target' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_125_link_target' ) ); ?>" class="widefat" style="width:100%;">
				<option value="_self" <?php echo ( '_self' === $instance['ad_125_link_target'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( '_self', 'fusion-core' ); ?></option>
				<option value="_blank" <?php echo ( '_blank' === $instance['ad_125_link_target'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( '_blank', 'fusion-core' ); ?></option>
			</select>
		</p>
		<?php

	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

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
class Fusion_Widget_Contact_Info extends WP_Widget {

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		$widget_ops  = [
			'classname'   => 'contact_info',
			'description' => __( 'Adds custom contact information.', 'fusion-core' ),
		];
		$control_ops = [
			'id_base' => 'contact_info-widget',
		];
		parent::__construct( 'contact_info-widget', __( 'Avada: Contact Info', 'fusion-core' ), $widget_ops, $control_ops );

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

		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );

		echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput

		if ( $title ) {
			echo $before_title . $title . $after_title; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>

		<div class="contact-info-container">
			<?php if ( ! empty( $instance['address'] ) ) : ?>
				<p class="address"><?php echo $instance['address']; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $instance['phone'] ) ) : ?>
				<p class="phone"><?php esc_attr_e( 'Phone:', 'fusion-core' ); ?> <a href="tel:<?php echo esc_attr( $instance['phone'] ); ?>"><?php echo $instance['phone']; // phpcs:ignore WordPress.Security.EscapeOutput ?></a></p>
			<?php endif; ?>

			<?php if ( ! empty( $instance['mobile'] ) ) : ?>
				<p class="mobile"><?php esc_attr_e( 'Mobile:', 'fusion-core' ); ?> <a href="tel:<?php echo esc_attr( $instance['mobile'] ); ?>"><?php echo $instance['mobile']; // phpcs:ignore WordPress.Security.EscapeOutput ?></a></p>
			<?php endif; ?>

			<?php if ( ! empty( $instance['fax'] ) ) : ?>
				<p class="fax"><?php esc_attr_e( 'Fax:', 'fusion-core' ); ?> <a href="fax:<?php echo esc_attr( $instance['fax'] ); ?>"><?php echo $instance['fax']; // phpcs:ignore WordPress.Security.EscapeOutput ?></a></p>
			<?php endif; ?>

			<?php if ( ! empty( $instance['email'] ) ) : ?>
				<?php if ( apply_filters( 'fusion_disable_antispambot', false ) ) : ?>
					<p class="email"><?php esc_attr_e( 'Email:', 'fusion-core' ); ?> <a href="mailto:<?php echo $instance['email']; // phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo ( $instance['emailtxt'] ) ? $instance['emailtxt'] : $instance['email']; ?></a></p>
				<?php else : ?>
					<p class="email"><?php esc_attr_e( 'Email:', 'fusion-core' ); ?> <a href="mailto:<?php echo antispambot( $instance['email'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo ( $instance['emailtxt'] ) ? $instance['emailtxt'] : $instance['email']; ?></a></p>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( ! empty( $instance['web'] ) && $instance['web'] ) : ?>
				<p class="web"><?php esc_attr_e( 'Web:', 'fusion-core' ); ?> <a href="<?php echo esc_url_raw( $instance['web'] ); ?>">
					<?php if ( ! empty( $instance['webtxt'] ) ) : ?>
						<?php echo $instance['webtxt']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php else : ?>
						<?php echo $instance['web']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php endif; ?>
				</a></p>
			<?php endif; ?>
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

		$instance['title']    = isset( $new_instance['title'] ) ? $new_instance['title'] : '';
		$instance['address']  = isset( $new_instance['address'] ) ? $new_instance['address'] : '';
		$instance['phone']    = isset( $new_instance['phone'] ) ? $new_instance['phone'] : '';
		$instance['mobile']   = isset( $new_instance['mobile'] ) ? $new_instance['mobile'] : '';
		$instance['fax']      = isset( $new_instance['fax'] ) ? $new_instance['fax'] : '';
		$instance['email']    = isset( $new_instance['email'] ) ? $new_instance['email'] : '';
		$instance['emailtxt'] = isset( $new_instance['emailtxt'] ) ? $new_instance['emailtxt'] : '';
		$instance['web']      = isset( $new_instance['web'] ) ? $new_instance['web'] : '';
		$instance['webtxt']   = isset( $new_instance['webtxt'] ) ? $new_instance['webtxt'] : '';

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
			'title'    => 'Contact Info',
			'address'  => '',
			'phone'    => '',
			'mobile'   => '',
			'fax'      => '',
			'email'    => '',
			'emailtxt' => '',
			'web'      => '',
			'webtxt'   => '',
		];
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'address' ) ); ?>"><?php esc_attr_e( 'Address:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'address' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'address' ) ); ?>" value="<?php echo esc_attr( $instance['address'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'phone' ) ); ?>"><?php esc_attr_e( 'Phone:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'phone' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'phone' ) ); ?>" value="<?php echo esc_attr( $instance['phone'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'mobile' ) ); ?>"><?php esc_attr_e( 'Mobile:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'mobile' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'mobile' ) ); ?>" value="<?php echo esc_attr( $instance['mobile'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'fax' ) ); ?>"><?php esc_attr_e( 'Fax:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'fax' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'fax' ) ); ?>" value="<?php echo esc_attr( $instance['fax'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'email' ) ); ?>"><?php esc_attr_e( 'Email:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'email' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'email' ) ); ?>" value="<?php echo esc_attr( $instance['email'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'emailtxt' ) ); ?>"><?php esc_attr_e( 'Email Link Text:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'emailtxt' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'emailtxt' ) ); ?>" value="<?php echo esc_attr( $instance['emailtxt'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'web' ) ); ?>"><?php esc_attr_e( 'Website URL (with HTTP(S)):', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'web' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'web' ) ); ?>" value="<?php echo esc_attr( $instance['web'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'webtxt' ) ); ?>"><?php esc_attr_e( 'Website URL Text:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'webtxt' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'webtxt' ) ); ?>" value="<?php echo esc_attr( $instance['webtxt'] ); ?>" />
		</p>
		<?php

	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

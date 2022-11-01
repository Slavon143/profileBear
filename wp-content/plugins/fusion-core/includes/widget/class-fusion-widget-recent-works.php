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
class Fusion_Widget_Recent_Works extends WP_Widget {

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		$widget_ops  = [
			'classname'   => 'recent_works',
			'description' => __( 'Recent works from the portfolio.', 'fusion-core' ),
		];
		$control_ops = [
			'id_base' => 'recent_works-widget',
		];

		parent::__construct( 'recent_works-widget', __( 'Avada: Recent Works', 'fusion-core' ), $widget_ops, $control_ops );

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

		$title  = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );
		$number = isset( $instance['number'] ) ? $instance['number'] : 6;

		echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput

		if ( $title ) {
			echo $before_title . $title . $after_title; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>

		<div class="recent-works-items clearfix">
			<?php

			$args = [
				'post_type'      => 'avada_portfolio',
				'posts_per_page' => $number,
				'has_password'   => false,
			];

			$portfolio = FusionCore_Plugin::fusion_core_cached_query( $args );
			?>

			<?php if ( $portfolio->have_posts() && '0' !== fusion_get_option( 'status_fusion_portfolio' ) ) : ?>
				<?php while ( $portfolio->have_posts() ) : ?>
					<?php $portfolio->the_post(); ?>
					<?php if ( has_post_thumbnail() ) : ?>
						<?php $url_check = fusion_get_option( 'link_icon_url' ); ?>
						<?php $new_permalink = ( ! empty( $url_check ) ) ? $url_check : get_permalink(); ?>
						<?php $link_icon_target = fusion_get_option( 'portfolio_link_icon_target' ); ?>
						<?php $link_target = ( 'yes' === $link_icon_target ) ? '_blank' : '_self'; ?>
						<?php $rel = ( 'yes' === $link_icon_target ) ? 'noopener noreferrer' : ''; ?>

						<a href="<?php echo esc_url_raw( $new_permalink ); ?>" target="<?php echo esc_attr( $link_target ); ?>" rel="<?php echo esc_attr( $rel ); ?>" title="<?php the_title_attribute(); ?>">
							<?php the_post_thumbnail( 'recent-works-thumbnail' ); ?>
						</a>
					<?php endif; ?>
				<?php endwhile; ?>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>
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

		$instance['title']  = isset( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
		$instance['number'] = isset( $new_instance['number'] ) ? $new_instance['number'] : '';

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
			'title'  => __( 'Recent Works', 'fusion-core' ),
			'number' => 6,
		];
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_attr_e( 'Number Of Items To Show:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" style="width: 30px;" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" value="<?php echo esc_attr( $instance['number'] ); ?>" />
		</p>
		<?php

	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

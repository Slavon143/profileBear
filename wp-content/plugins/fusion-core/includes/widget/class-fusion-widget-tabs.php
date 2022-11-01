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
class Fusion_Widget_Tabs extends WP_Widget {

	/**
	 * General settings of the widget.
	 *
	 * @access protected
	 * @since 4.0
	 * @var array
	 */
	protected $settings = [];

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->set_settings();

		$widget_ops  = [
			'classname'   => 'fusion-tabs-widget',
			'description' => __( 'Popular posts, recent post and comments.', 'fusion-core' ),
		];
		$control_ops = [
			'id_base' => 'pyre_tabs-widget',
		];

		parent::__construct( 'pyre_tabs-widget', __( 'Avada: Tabs', 'fusion-core' ), $widget_ops, $control_ops );

	}

	/**
	 * Set settings for this widget,
	 * with a fallback to some sane defaults if no customized settings are saved in the database.
	 *
	 * @access public
	 * @since 4.0
	 * @return void
	 */
	public function set_settings() {
		$fusion_settings = class_exists( 'Fusion_Settings' ) ? awb_get_fusion_settings() : false;

		if ( $fusion_settings ) {
			$date_format    = $fusion_settings->get( 'date_format' );
			$date_format    = $date_format ? $date_format : get_option( 'date_format' );
			$this->settings = [
				'alternate_date_format_day'        => $fusion_settings->get( 'alternate_date_format_day' ),
				'alternate_date_format_month_year' => $fusion_settings->get( 'alternate_date_format_month_year' ),
				'date_format'                      => $date_format,
			];
		} else {
			$this->settings = [
				'alternate_date_format_day'        => 'j',
				'alternate_date_format_month_year' => 'm, Y',
				'date_format'                      => get_option( 'date_format' ),
			];
		}
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

		global $post;

		extract( $args );

		$design_tabs        = isset( $instance['design_tabs'] ) ? $instance['design_tabs'] : 'classic';
		$design_posts       = isset( $instance['design_posts'] ) ? $instance['design_posts'] : 'image_default';
		$posts              = isset( $instance['posts'] ) ? $instance['posts'] : 3;
		$comments           = isset( $instance['comments'] ) ? $instance['comments'] : '3';
		$tags_count         = isset( $instance['tags'] ) ? $instance['tags'] : 3;
		$show_popular_posts = isset( $instance['show_popular_posts'] ) && 'on' === $instance['show_popular_posts'] ? true : false;
		$show_recent_posts  = isset( $instance['show_recent_posts'] ) && 'on' === $instance['show_recent_posts'] ? true : false;
		$show_comments      = isset( $instance['show_comments'] ) && 'on' === $instance['show_comments'] ? true : false;

		$count_tabs = (int) $show_popular_posts + (int) $show_recent_posts + (int) $show_comments;

		if ( isset( $instance['orderby'] ) ) {
			$orderby = $instance['orderby'];
		} else {
			$orderby = 'comment_count';
		}

		echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput
		?>
		<div class="fusion-tabs-widget-wrapper fusion-tabs-widget-<?php echo esc_attr( $count_tabs ); ?> fusion-tabs-<?php echo esc_attr( $design_tabs ); ?> fusion-tabs-<?php echo esc_attr( str_replace( '_', '-', $design_posts ) ); ?> tab-holder">
			<nav class="fusion-tabs-nav">
				<ul class="tabset tabs">

					<?php if ( $show_popular_posts ) : ?>
						<li class="active"><a href="#" data-link="fusion-tab-popular"><?php esc_html_e( 'Popular', 'fusion-core' ); ?></a></li>
					<?php endif; ?>

					<?php if ( $show_recent_posts ) : ?>
						<li<?php echo ( ! $show_popular_posts ) ? ' class="active"' : ''; ?>><a href="#" data-link="fusion-tab-recent"><?php esc_html_e( 'Recent', 'fusion-core' ); ?></a></li>
					<?php endif; ?>

					<?php if ( $show_comments ) : ?>
						<li<?php echo ( ! $show_popular_posts && ! $show_recent_posts ) ? ' class="active"' : ''; ?>><a href="#" data-link="fusion-tab-comments"><span class="awb-icon-bubbles"></span><span class="screen-reader-text"><?php esc_html_e( 'Comments', 'fusion-core' ); ?></span></a></li>
					<?php endif; ?>

				</ul>
			</nav>

			<div class="fusion-tabs-widget-content tab-box tabs-container">

				<?php if ( $show_popular_posts ) : ?>

					<div class="fusion-tab-popular fusion-tab-content tab tab_content" data-name="fusion-tab-popular">
						<?php
						if ( 'Highest Comments' === $orderby || 'comment_count' === $orderby ) {
							$order_string = '&orderby=comment_count';
						} else {
							$order_string = '&meta_key=avada_post_views_count&orderby=meta_value_num'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						}

						$popular_posts = FusionCore_Plugin::fusion_core_cached_query( 'showposts=' . $posts . $order_string . '&order=DESC&ignore_sticky_posts=1' );
						?>

						<ul class="fusion-tabs-widget-items news-list">
							<?php if ( $popular_posts->have_posts() ) : ?>
								<?php while ( $popular_posts->have_posts() ) : ?>
									<?php $popular_posts->the_post(); ?>
									<li>
										<?php if ( 'post_date' === $design_posts ) : ?>
											<div class="fusion-date-box updated">
												<div class="fusion-date"><?php the_time( $this->settings['alternate_date_format_day'] ); ?></div>
												<div class="fusion-month-year"><?php the_time( $this->settings['alternate_date_format_month_year'] ); ?></div>
											</div>
										<?php elseif ( has_post_thumbnail() ) : ?>
											<div class="image">
												<a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( 'recent-works-thumbnail' ); ?></a>
											</div>
										<?php endif; ?>

										<div class="post-holder">
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
											<div class="fusion-meta">
												<?php
												if ( 'post_date' === $design_posts ) {
													if ( function_exists( 'fusion_get_post_content_excerpt' ) ) {
														echo fusion_get_post_content_excerpt( apply_filters( 'fusion_tabs_widget_excerpt_length', 4 ), true ); // phpcs:ignore WordPress.Security.EscapeOutput
													} else {
														echo wp_trim_words( get_the_excerpt(), apply_filters( 'fusion_tabs_widget_excerpt_length', 4 ) ); // phpcs:ignore WordPress.Security.EscapeOutput
													}
												} else {
													the_time( $this->settings['date_format'] );
												}
												?>
											</div>
										</div>
									</li>
								<?php endwhile; ?>

								<?php wp_reset_postdata(); ?>
							<?php else : ?>
								<li><?php esc_attr_e( 'No posts have been published yet.', 'fusion-core' ); ?></li>
							<?php endif; ?>
						</ul>
					</div>

				<?php endif; ?>

				<?php if ( $show_recent_posts ) : ?>

					<div class="fusion-tab-recent fusion-tab-content tab tab_content" data-name="fusion-tab-recent"<?php echo ( $show_popular_posts ) ? ' style="display: none;"' : ''; ?>>

						<?php $recent_posts = FusionCore_Plugin::fusion_core_cached_query( 'showposts=' . $tags_count . '&ignore_sticky_posts=1' ); ?>

						<ul class="fusion-tabs-widget-items news-list">
							<?php if ( $recent_posts->have_posts() ) : ?>
								<?php while ( $recent_posts->have_posts() ) : ?>
									<?php $recent_posts->the_post(); ?>
									<li>
										<?php if ( 'post_date' === $design_posts ) : ?>
											<div class="fusion-date-box updated">
												<div class="fusion-date"><?php the_time( $this->settings['alternate_date_format_day'] ); ?></div>
												<div class="fusion-month-year"><?php the_time( $this->settings['alternate_date_format_month_year'] ); ?></div>
											</div>
										<?php elseif ( has_post_thumbnail() ) : ?>
											<div class="image">
												<a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( 'recent-works-thumbnail' ); ?></a>
											</div>
										<?php endif; ?>
										<div class="post-holder">
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
											<div class="fusion-meta">
												<?php
												if ( 'post_date' === $design_posts ) {
													if ( function_exists( 'fusion_get_post_content_excerpt' ) ) {
														echo fusion_get_post_content_excerpt( apply_filters( 'fusion_tabs_widget_excerpt_length', 4 ), true ); // phpcs:ignore WordPress.Security.EscapeOutput
													} else {
														echo wp_trim_words( get_the_excerpt(), apply_filters( 'fusion_tabs_widget_excerpt_length', 4 ) ); // phpcs:ignore WordPress.Security.EscapeOutput
													}
												} else {
													the_time( $this->settings['date_format'] );
												}
												?>
											</div>
										</div>
									</li>
								<?php endwhile; ?>
								<?php wp_reset_postdata(); ?>
							<?php else : ?>
								<li><?php esc_attr_e( 'No posts have been published yet.', 'fusion-core' ); ?></li>
							<?php endif; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php if ( $show_comments ) : ?>

					<div class="fusion-tab-comments tab fusion-tab-content tab_content" data-name="fusion-tab-comments"<?php echo ( $show_popular_posts || $show_recent_posts ) ? ' style="display: none;"' : ''; ?>>
						<ul class="fusion-tabs-widget-items news-list">
							<?php
							$the_comments = get_comments(
								[
									'number'      => $comments,
									'status'      => 'approve',
									'post_status' => 'publish',
								]
							);
							?>

							<?php if ( $the_comments ) : ?>
								<?php foreach ( $the_comments as $comment ) : ?>
									<li>
										<div class="image">
											<a><?php echo get_avatar( $comment, '52' ); ?></a>
										</div>
										<div class="post-holder">
											<?php /* translators: comment author. */ ?>
											<p><?php printf( esc_attr__( '%s says:', 'fusion-core' ), esc_attr( strip_tags( $comment->comment_author ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions ?></p>
											<div class="fusion-meta">
												<?php /* translators: %1$s: comment author. %2$s: post-title. */ ?>
												<a class="comment-text-side" href="<?php echo esc_url( get_comment_link( $comment ) ); ?>" title="<?php printf( esc_attr__( '%1$s on %2$s', 'fusion-core' ), esc_attr( strip_tags( $comment->comment_author ) ), get_the_title( $comment->comment_post_ID ) ); ?>"><?php echo wp_trim_words( strip_tags( $comment->comment_content ), 12 ); // phpcs:ignore WordPress.Security.EscapeOutput, WordPress.WP.AlternativeFunctions ?></a>
											</div>
										</div>
									</li>
								<?php endforeach; ?>
							<?php else : ?>
								<li><?php esc_attr_e( 'No comments have been published yet.', 'fusion-core' ); ?></li>
							<?php endif; ?>
						</ul>
					</div>

				<?php endif; ?>
			</div>
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

		$instance['design_tabs']        = $new_instance['design_tabs'];
		$instance['design_posts']       = $new_instance['design_posts'];
		$instance['posts']              = $new_instance['posts'];
		$instance['comments']           = $new_instance['comments'];
		$instance['tags']               = $new_instance['tags'];
		$instance['show_popular_posts'] = ! empty( $new_instance['show_popular_posts'] ) ? $new_instance['show_popular_posts'] : '0';
		$instance['show_recent_posts']  = ! empty( $new_instance['show_recent_posts'] ) ? $new_instance['show_recent_posts'] : '0';
		$instance['show_comments']      = ! empty( $new_instance['show_comments'] ) ? $new_instance['show_comments'] : '0';
		$instance['orderby']            = $new_instance['orderby'];

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
			'design_tabs'        => 'classic',
			'design_posts'       => 'image_default',
			'posts'              => 3,
			'comments'           => '3',
			'tags'               => 3,
			'show_popular_posts' => 'on',
			'show_recent_posts'  => 'on',
			'show_comments'      => 'on',
			'orderby'            => 'comments_count',
		];

		$instance = wp_parse_args( (array) $instance, $defaults );

		if ( 'Highest Comments' === $instance['orderby'] || 'comment_count' === $instance['orderby'] ) {
				$instance['orderby'] = 'comment_count';
		} else {
			$instance['orderby'] = 'view_count';
		}
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'design_tabs' ) ); ?>"><?php esc_html_e( 'Tabs Design:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'design_tabs' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'design_tabs' ) ); ?>" class="widefat" style="width:100%;">
				<option value="classic" <?php echo ( 'classic' === $instance['design_tabs'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Classic', 'fusion-core' ); ?></option>
				<option value="clean" <?php echo ( 'clean' === $instance['design_tabs'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Clean', 'fusion-core' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'design_posts' ) ); ?>"><?php esc_html_e( 'Post Design:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'design_posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'design_posts' ) ); ?>" class="widefat" style="width:100%;">
				<option value="image_default" <?php echo ( 'image_default' === $instance['design_posts'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Featured Image / Avatar Default Shape', 'fusion-core' ); ?></option>
				<option value="image_square" <?php echo ( 'image_square' === $instance['design_posts'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Featured Image / Avatar Square', 'fusion-core' ); ?></option>
				<option value="image_circle" <?php echo ( 'image_circle' === $instance['design_posts'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Featured Image / Avatar Circle', 'fusion-core' ); ?></option>
				<option value="post_date" <?php echo ( 'post_date' === $instance['design_posts'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Post Date', 'fusion-core' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php esc_html_e( 'Popular Posts Order By:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>" class="widefat" style="width:100%;">
				<option value="comment_count" <?php echo ( 'comment_count' === $instance['orderby'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Highest Comments', 'fusion-core' ); ?></option>
				<option value="view_count" <?php echo ( 'view_count' === $instance['orderby'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Highest Views', 'fusion-core' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'posts' ) ); ?>"><?php esc_html_e( 'Number of popular posts:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" style="width: 30px;" id="<?php echo esc_attr( $this->get_field_id( 'posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'posts' ) ); ?>" value="<?php echo esc_attr( $instance['posts'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'tags' ) ); ?>"><?php esc_html_e( 'Number of recent posts:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" style="width: 30px;" id="<?php echo esc_attr( $this->get_field_id( 'tags' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tags' ) ); ?>" value="<?php echo esc_attr( $instance['tags'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'comments' ) ); ?>"><?php esc_html_e( 'Number of comments:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" style="width: 30px;" id="<?php echo esc_attr( $this->get_field_id( 'comments' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'comments' ) ); ?>" value="<?php echo esc_attr( $instance['comments'] ); ?>" />
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_popular_posts'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_popular_posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_popular_posts' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_popular_posts' ) ); ?>"><?php esc_html_e( 'Show popular posts', 'fusion-core' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_recent_posts'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_recent_posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_recent_posts' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_recent_posts' ) ); ?>"><?php esc_html_e( 'Show recent posts', 'fusion-core' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_comments'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_comments' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_comments' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_comments' ) ); ?>"><?php esc_html_e( 'Show comments', 'fusion-core' ); ?></label>
		</p>
		<?php

	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

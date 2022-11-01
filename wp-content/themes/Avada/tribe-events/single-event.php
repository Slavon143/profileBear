<?php
/**
 * Single Event Template
 * A single event. This displays the event title, description, meta, and
 * optionally, the Google map for the event.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/single-event.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$events_label_singular = tribe_get_event_label_singular();
$events_label_plural = tribe_get_event_label_plural();
?>

<div id="tribe-events-content" class="tribe-events-single">

	<!-- Notices -->
	<?php
	if ( function_exists( 'tribe_the_notices' ) ) {
		tribe_the_notices();
	} else {
		tribe_events_the_notices();
	}
	?>

	<?php while ( have_posts() ) :  the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php if ( has_post_thumbnail() ) :  ?>
				<div class="fusion-events-featured-image">
					<div class="fusion-ec-hover-type hover-type-<?php echo Avada()->settings->get( 'ec_hover_type' ); ?>">

						<?php avada_singular_featured_image(); ?>

						<?php Avada_EventsCalendar::render_single_event_title(); ?>
					</div>
			<?php else : ?>
				<div class="fusion-events-featured-image fusion-events-single-title">
					<?php Avada_EventsCalendar::render_single_event_title(); ?>
			<?php endif; ?>
				</div>

			<!-- Event content -->
			<?php do_action( 'tribe_events_single_event_before_the_content' ); ?>
			<div class="tribe-events-single-event-description tribe-events-content entry-content description">
				<?php the_content(); ?>
			</div>
			<!-- .tribe-events-single-event-description -->
			<?php do_action( 'tribe_events_single_event_after_the_content' ); ?>

			<!-- Event meta -->
			<?php
			$columns = 1;
			$layout_class = '';

			if ( tribe_has_organizer() ) {
				$columns += 1;
			} else {
				$layout_class = ' fusion-event-meta-no-organizer';
			}

			$set_venue_apart = apply_filters( 'tribe_events_single_event_the_meta_group_venue', false, get_the_ID() );

			if ( tribe_get_venue_id() ) {
				// If we have no map to embed and no need to keep the venue separate...
				if ( ! $set_venue_apart && ! tribe_embed_google_map() ) {
					$layout_class .= ' fusion-event-meta-venue';
					$columns += 1;
				} elseif ( ! $set_venue_apart && ! tribe_has_organizer() && tribe_embed_google_map() ) {
					$layout_class .= ' fusion-event-meta-venue-map';
					$columns += 2;
				} else {
					$set_venue_apart = true;
				}
			}

			if ( $set_venue_apart )	{
				$layout_class .= ' fusion-event-meta-venue-apart';
				$columns = 4;
			}

			if ( 'below_content' === Avada()->settings->get( 'ec_meta_layout' ) ) :
			?>
				<?php do_action( 'tribe_events_single_event_before_the_meta' ); ?>
				<div class="fusion-content-widget-area fusion-event-meta-columns fusion-event-meta-columns-<?php echo esc_attr( $columns ) . esc_attr( $layout_class ); ?>">
					<div class="fusion-event-meta-wrapper">
						<?php tribe_get_template_part( 'modules/meta' ); ?>
					</div>
				</div>
			<?php endif; ?>
			<?php do_action( 'tribe_events_single_event_after_the_meta' ); ?>
		</div> <!-- #post-x -->

		<?php avada_render_social_sharing( 'events' ); ?>

		<?php
		if ( get_post_type() == Tribe__Events__Main::POSTTYPE && tribe_get_option( 'showComments', false ) ) {

			add_filter( 'comments_template', 'add_comments_template' );

			function add_comments_template() {
				return Avada::$template_dir_path . '/comments.php';
			}

			comments_template();
		}
		?>
	<?php endwhile;
	?>

	<!-- Event footer -->
	<div id="tribe-events-footer">
		<!-- Navigation -->
		<h3 class="tribe-events-visuallyhidden"><?php printf( __( '%s Navigation', 'the-events-calendar' ), $events_label_singular ); ?></h3>
		<ul class="tribe-events-sub-nav">
			<li class="tribe-events-nav-previous"><?php tribe_the_prev_event_link( '%title%' ) ?></li>
			<li class="tribe-events-nav-next"><?php tribe_the_next_event_link( '%title%' ) ?></li>
		</ul>
		<!-- .tribe-events-sub-nav -->
	</div>
	<!-- #tribe-events-footer -->

</div><!-- #tribe-events-content -->

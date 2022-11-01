<?php
/**
 * This file contains typography styles for The Events Calendar plugin.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * The Events Calendar CSS classes that inherit Avada's body typography settings.
 *
 * @param array $typography_elements An array of all typography elements.
 * @return array
 */
function avada_events_calendar_body_typography( $typography_elements ) {
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$typography_elements['size'][]   = '.tribe-events-loop .tribe-events-event-meta';
		$typography_elements['family'][] = '#tribe-events-content-wrapper #tribe_events_filters_wrapper .tribe-events-filters-label';
	}

	return $typography_elements;
}
add_filter( 'avada_body_typography_elements', 'avada_events_calendar_body_typography' );

/**
 * The Events Calendar css classes that inherit Avada's H1 typography settings.
 *
 * @param array $typography_elements An array of all typography elements.
 * @return array
 */
function avada_events_calendar_h1_typography( $typography_elements ) {
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$typography_elements['size'][]   = '.single-tribe_events h1.tribe-events-single-event-title';
		$typography_elements['color'][]  = '.single-tribe_events h1.tribe-events-single-event-title';
		$typography_elements['family'][] = '.single-tribe_events h1.tribe-events-single-event-title';
	}

	return $typography_elements;
}
add_filter( 'avada_h1_typography_elements', 'avada_events_calendar_h1_typography' );

/**
 * The Events Calendar css classes that inherit Avada's H2 typography settings.
 *
 * @access public
 * @since 6.2
 * @param array $typography_elements An array of all typography elements.
 * @return array The typography array.
 */
function avada_events_calendar_h2_typography( $typography_elements ) {
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$typography_elements['size'][]   = '.fusion-body #main .tribe-tickets .tribe-tickets__title';
		$typography_elements['color'][]  = '.fusion-body #main .tribe-tickets .tribe-tickets__title';
		$typography_elements['family'][] = '.fusion-body #main .tribe-tickets .tribe-tickets__title';
	}

	return $typography_elements;
}
add_filter( 'avada_h2_typography_elements', 'avada_events_calendar_h2_typography' );

/**
 * The Events Calendar css classes that inherit Avada's H3 typography settings.
 *
 * @param array $typography_elements An array of all typography elements.
 * @return array
 */
function avada_events_calendar_h3_typography( $typography_elements ) {
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$typography_elements['size'][]   = '.single-tribe_events .fusion-events-featured-image .fusion-events-single-title-content .tribe-events-schedule';
		$typography_elements['size'][]   = '.single-tribe_events .fusion-events-featured-image .fusion-events-single-title-content .tribe-events-schedule h3';
		$typography_elements['size'][]   = '.single-tribe_events .tribe-block__venue .tribe-block__venue__meta .tribe-block__venue__name h3';
		$typography_elements['size'][]   = '.single-tribe_events #tribe-events-content .tribe-events-event-meta .tribe-events-single-section-title';
		$typography_elements['size'][]   = '.fusion-body #main .tribe-events .tribe-events-calendar-month__header-column-title';
		$typography_elements['size'][]   = '.fusion-body #main .tribe-events .tribe-events-calendar-month__body .tribe-events-calendar-month__day-date';
		$typography_elements['size'][]   = '.fusion-body #main .tribe-events .tribe-events-calendar-month__body .tribe-events-calendar-month__day';
		$typography_elements['size'][]   = '.fusion-body .tooltipster-base h3';
		$typography_elements['family'][] = '.single-tribe_events .fusion-events-featured-image .fusion-events-single-title-content .tribe-events-schedule';
		$typography_elements['family'][] = '.single-tribe_events .tribe-block__venue .tribe-block__venue__meta .tribe-block__venue__name h3';
		$typography_elements['family'][] = '.single-tribe_events #tribe-events-content .tribe-events-event-meta .tribe-events-single-section-title';
		$typography_elements['family'][] = '.fusion-body #main .tribe-events .tribe-events-calendar-month__header-column-title';
		$typography_elements['family'][] = '.fusion-body #main .tribe-events .tribe-events-calendar-month__body .tribe-events-calendar-month__day-date';
		$typography_elements['family'][] = '.fusion-body .tooltipster-base h3';
		$typography_elements['color'][]  = '.fusion-body #main .tribe-events .tribe-events-calendar-month__header-column-title';
		$typography_elements['color'][]  = '.fusion-body #main .tribe-events .tribe-events-calendar-month__body .tribe-events-calendar-month__day-date';
	}

	return $typography_elements;
}
add_filter( 'avada_h3_typography_elements', 'avada_events_calendar_h3_typography' );

/**
 * The Events Calendar css classes that inherit Avada's H4 typography settings.
 *
 * @param array $typography_elements An array of all typography elements.
 * @return array
 */
function avada_events_calendar_h4_typography( $typography_elements ) {
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$typography_elements['size'][]   = '.fusion-content-widget-area .tribe-events-single-section-title';
		$typography_elements['size'][]   = '#tribe-events-content .tribe-events-tooltip .entry-title';
		$typography_elements['size'][]   = '#tribe-events-content .tribe-events-tooltip .tribe-event-title';
		$typography_elements['size'][]   = '.tribe-block__tickets__registration__tickets__header';
		$typography_elements['color'][]  = '.fusion-content-widget-area .tribe-events-single-section-title';
		$typography_elements['family'][] = '.fusion-content-widget-area .tribe-events-single-section-title';
		$typography_elements['family'][] = '#tribe-events-content .tribe-events-tooltip .entry-title';
		$typography_elements['family'][] = '#tribe-events-content .tribe-events-tooltip .tribe-event-title';
		$typography_elements['family'][] = '.tribe-block__tickets__registration__tickets__header';
	}

	return $typography_elements;
}
add_filter( 'avada_h4_typography_elements', 'avada_events_calendar_h4_typography' );


/**
 * The Events Calendar css classes that inherit Avada's button typography settings.
 *
 * @param array $typography_elements An array of all typography elements.
 * @return array
 */
function avada_events_calendar_button_typography( $typography_elements ) {
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$typography_elements['family'][] = '#tribe-events .tribe-events-tickets .add-to-cart .tribe-button';
		$typography_elements['family'][] = '.avada-ec-views-v2 .tribe-tickets__buy';
	}

	return $typography_elements;
}
add_filter( 'avada_button_typography_elements', 'avada_events_calendar_button_typography' );

/**
 * The Events Calendar css classes that inherit Avada's post title typography settings.
 *
 * @access public
 * @since 6.2
 * @param array $typography_elements An array of all typography elements.
 * @return array The typography array.
 */
function avada_events_post_title_typography_elements( $typography_elements ) {
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$typography_elements['size'][] = '#wrapper .fusion-events-shortcode .fusion-events-meta h2';
		$typography_elements['size'][] = '#wrapper .fusion-events-shortcode .fusion-events-meta h1';
		$typography_elements['size'][] = '.fusion-body .fusion-wrapper #main .tribe-events.tribe-events-view article header h3';
		$typography_elements['size'][] = '.fusion-body .fusion-wrapper #main .tribe-events .tribe-events-pro-photo__event-title';
		$typography_elements['size'][] = '.fusion-body .fusion-wrapper #main .tribe-events .tribe-events-pro-map__event-title';
		$typography_elements['size'][] = '.fusion-body .fusion-wrapper #main .tribe-events-calendar-month-mobile-events__mobile-event-title';
		$typography_elements['size'][] = '.fusion-body .fusion-wrapper #main .tribe-events-pro-week-mobile-events__event-title';

		$typography_elements['color'][] = '#wrapper .fusion-events-shortcode .fusion-events-meta h2';
		$typography_elements['color'][] = '#wrapper .fusion-events-shortcode .fusion-events-meta h1';
		$typography_elements['color'][] = '.fusion-body .fusion-wrapper #main .tribe-events .tribe-events-pro-photo__event-title';
		$typography_elements['color'][] = '.fusion-body .fusion-wrapper #main .tribe-events .tribe-events-pro-map__event-title';
		$typography_elements['color'][] = '.fusion-body .fusion-wrapper #main .tribe-events-calendar-month-mobile-events__mobile-event-title';

		$typography_elements['family'][] = '#wrapper .fusion-events-shortcode .fusion-events-meta h2';
		$typography_elements['family'][] = '#wrapper .fusion-events-shortcode .fusion-events-meta h1';
		$typography_elements['family'][] = '.fusion-body .fusion-wrapper #main .tribe-events.tribe-events-view article header h3';
		$typography_elements['family'][] = '.fusion-body .fusion-wrapper #main .tribe-events .tribe-events-pro-photo__event-title';
		$typography_elements['family'][] = '.fusion-body .fusion-wrapper #main .tribe-events .tribe-events-pro-map__event-title';
		$typography_elements['family'][] = '.fusion-body .fusion-wrapper #main .tribe-events-calendar-month-mobile-events__mobile-event-title';
		$typography_elements['family'][] = '.fusion-body .fusion-wrapper #main .tribe-events-pro-week-mobile-events__event-title';
	}

	return $typography_elements;
}
add_filter( 'avada_post_title_typography_elements', 'avada_events_post_title_typography_elements' );

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

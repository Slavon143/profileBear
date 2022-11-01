<?php
/**
 * Searchform
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Fusion-Library
 * @since      2.1
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Get & set setting values.
 */
class Fusion_Searchform {

	/**
	 * The search counter.
	 *
	 * @access private
	 * @since 7.5
	 * @var int
	 */
	private static $counter = 0;

	/**
	 * Outputs search form.
	 *
	 * @param array $args Search form arguments.
	 * @return void
	 */
	public static function get_form( $args ) {
		if ( ! is_array( $args ) ) {
			// Set an empty array and allow default arguments to take over.
			$args = [];
		}

		// Defaults are to echo and to output no custom label on the form.
		$defaults = [
			'live_search'   => '0',
			'design'        => 'classic',
			'after_fields'  => '',
			'before_fields' => '',
			'placeholder'   => __( 'Search...', 'Avada' ),
			'counter'       => self::$counter,
		];

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'search_form_after_fields', $args );

		$class = '';

		if ( $args['live_search'] ) {
			$class .= ' fusion-live-search';
		}

		if ( 'classic' === $args['design'] ) {
			$class .= ' fusion-search-form-classic';
		} elseif ( 'clean' === $args['design'] ) {
			$class .= ' fusion-search-form-clean';
		}

		$is_live_search = $args['live_search'];

		?>
		<form role="search" class="searchform fusion-search-form <?php echo esc_attr( $class ); ?>" method="get" action="<?php echo esc_url_raw( home_url( '/' ) ); ?>">
			<div class="fusion-search-form-content">

				<?php echo $args['before_fields']; // phpcs:ignore WordPress.Security.EscapeOutput ?>

				<div class="fusion-search-field search-field">
					<label><span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'Avada' ); ?></span>
						<?php if ( $is_live_search ) : ?>
							<input type="search" class="s fusion-live-search-input" name="s" id="fusion-live-search-input-<?php echo $args['counter']; // phpcs:ignore WordPress.Security.EscapeOutput ?>" autocomplete="off" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" required aria-required="true" aria-label="<?php echo esc_attr( $args['placeholder'] ); ?>"/>
						<?php else : ?>
							<input type="search" value="<?php echo get_search_query(); ?>" name="s" class="s" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" required aria-required="true" aria-label="<?php echo esc_attr( $args['placeholder'] ); ?>"/>
						<?php endif; ?>
					</label>
				</div>
				<div class="fusion-search-button search-button">
					<input type="submit" class="fusion-search-submit searchsubmit" aria-label="<?php esc_html_e( 'Search', 'Avada' ); ?>" value="&#xf002;" />
					<?php if ( $is_live_search ) : ?>
					<div class="fusion-slider-loading"></div>
					<?php endif; ?>
				</div>

				<?php echo $args['after_fields']; // phpcs:ignore WordPress.Security.EscapeOutput ?>

			</div>


			<?php if ( $is_live_search ) : ?>
				<div class="fusion-search-results-wrapper"><div class="fusion-search-results"></div></div>
			<?php endif; ?>

		</form>
		<?php
		self::$counter++;
	}

}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */

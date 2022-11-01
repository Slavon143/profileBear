<?php
/**
 * Privacy bar template.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

$text               = Avada()->settings->get( 'privacy_bar_text' );
$content            = Avada()->privacy_embeds->get_privacy_content();
$content_count      = is_array( $content ) ? count( $content ) : 0;
$column_size        = 0 !== $content_count ? 12 / $content_count : 0;
$more               = '0' !== Avada()->settings->get( 'privacy_bar_more' ) && 0 !== $content_count; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
$button_text        = Avada()->settings->get( 'privacy_bar_button_text' );
$button_alt_text    = Avada()->settings->get( 'privacy_bar_update_text' );
$settings_text      = Avada()->settings->get( 'privacy_bar_more_text' );
$privacy_embed      = Avada()->settings->get( 'privacy_embeds' );
$reject_button      = '0' !== Avada()->settings->get( 'privacy_bar_reject' ) && '0' !== $privacy_embed; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
$reject_button_text = Avada()->settings->get( 'privacy_bar_reject_text' );
?>
<div class="fusion-privacy-bar fusion-privacy-bar-bottom">
	<div class="fusion-privacy-bar-main">
		<span><?php echo do_shortcode( $text ); ?>
			<?php if ( $more ) : ?>
				<a href="#" class="fusion-privacy-bar-learn-more"><?php echo esc_attr( $settings_text ); ?> <i class="awb-icon-angle-down" aria-hidden="true"></i></a>
			<?php endif; ?>
		</span>
		<a href="#" class="fusion-privacy-bar-acceptance fusion-button fusion-button-default fusion-button-default-size fusion-button-span-no" data-alt-text="<?php echo esc_attr( $button_alt_text ); ?>" data-orig-text="<?php echo esc_attr( $button_text ); ?>">
			<?php echo esc_html( $button_text ); ?>
		</a>
		<?php if ( $reject_button ) : ?>
		<a href="#" class="fusion-privacy-bar-reject fusion-button fusion-button-default fusion-button-default-size fusion-button-span-no">
			<?php echo esc_html( $reject_button_text ); ?>
		</a>
		<?php endif; ?>
	</div>
	<?php if ( $more ) : ?>
	<div class="fusion-privacy-bar-full">
		<div class="fusion-row">
			<div class="fusion-columns row fusion-columns-<?php echo esc_attr( $content_count ); ?> columns columns-<?php echo esc_attr( $content_count ); ?>">
				<?php foreach ( $content as $id => $column ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride ?>
				<div class="fusion-column col-lg-<?php echo esc_attr( $column_size ); ?> col-md-<?php echo esc_attr( $column_size ); ?> col-sm-<?php echo esc_attr( $column_size ); ?>">
					<?php if ( '' !== $column['title'] ) : ?>
						<h4 class="column-title"><?php echo esc_html( $column['title'] ); ?></h4>
					<?php endif; ?>

					<?php if ( '' !== $column['description'] ) : ?>
						<?php echo do_shortcode( $column['description'] ); ?>
					<?php endif; ?>

					<?php if ( 'embeds' === $column['type'] && Avada()->settings->get( 'privacy_embeds' ) ) : ?>
						<?php $embeds = Avada()->privacy_embeds->get_embed_types(); ?>

						<ul class="fusion-privacy-choices">

						<?php if ( is_array( $embeds ) ) : ?>
							<?php foreach ( $embeds as $id => $embed ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride ?>
								<?php $selected = Avada()->privacy_embeds->is_selected( $id ) ? 'checked' : ''; ?>

								<?php if ( 'tracking' !== $id ) : ?>
									<li>
										<label for="bar-<?php echo esc_attr( $id ); ?>">
											<input name="consents[]" type="checkbox" value="<?php echo esc_attr( $id ); ?>" <?php echo esc_attr( $selected ); ?> id="bar-<?php echo esc_attr( $id ); ?>">
													<?php echo esc_html( $embed['label'] ); ?>
										</label>
									</li>
								<?php endif; ?>

							<?php endforeach; ?>
						<?php endif; ?>

						</ul>

					<?php elseif ( 'tracking' === $column['type'] && false !== Avada()->privacy_embeds->get_embed_type( 'tracking' ) && Avada()->settings->get( 'privacy_embeds' ) ) : ?>
						<?php $selected = Avada()->privacy_embeds->is_selected( 'tracking' ) ? 'checked' : ''; ?>
						<ul class="fusion-privacy-choices">
							<li>
								<label for="bar-tracking">
									<input name="consents[]" type="checkbox" value="tracking" <?php echo esc_attr( $selected ); ?> id="bar-tracking">
										<?php esc_html_e( 'Tracking Cookies', 'Avada' ); ?>
								</label>
							</li>
						</ul>
					<?php endif; ?>

				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>

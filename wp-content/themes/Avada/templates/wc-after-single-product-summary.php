<?php
/**
 * Adds HTML after the product summary.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.1.0
 */

?>
<div class="fusion-clearfix"></div>

<?php if ( Avada()->settings->get( 'woocommerce_social_links' ) ) : ?>
	<?php $nofollow = ( Avada()->settings->get( 'nofollow_social_links' ) ) ? ' rel="noopener noreferrer nofollow"' : ' rel="noopener noreferrer"'; ?>
	<ul class="social-share clearfix">
		<li class="facebook">
			<a class="fusion-facebook-sharer-icon" href="https://www.facebook.com/sharer.php?u=<?php echo rawurlencode( get_permalink() ); ?>" target="_blank"<?php echo $nofollow; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<i class="fontawesome-icon medium circle-yes awb-icon-facebook" aria-hidden="true"></i>
				<div class="fusion-woo-social-share-text">
					<span><?php esc_html_e( 'Share On Facebook', 'Avada' ); ?></span>
				</div>
			</a>
		</li>
		<li class="twitter">
			<a href="https://twitter.com/share?text=<?php the_title_attribute(); ?>&amp;url=<?php echo rawurlencode( get_permalink() ); ?>" target="_blank"<?php echo $nofollow; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<i class="fontawesome-icon medium circle-yes awb-icon-twitter" aria-hidden="true"></i>
				<div class="fusion-woo-social-share-text">
					<span><?php esc_html_e( 'Tweet This Product', 'Avada' ); ?></span>
				</div>
			</a>
		</li>
		<li class="pinterest">
			<?php $full_image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' ); ?>
			<a href="http://pinterest.com/pin/create/button/?url=<?php echo rawurlencode( get_permalink() ); ?>&amp;description=<?php echo rawurlencode( the_title_attribute( [ 'echo' => false ] ) ); ?>&amp;media=<?php echo rawurlencode( $full_image[0] ); ?>" target="_blank"<?php echo $nofollow; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<i class="fontawesome-icon medium circle-yes awb-icon-pinterest" aria-hidden="true"></i>
				<div class="fusion-woo-social-share-text">
					<span><?php esc_html_e( 'Pin This Product', 'Avada' ); ?></span>
				</div>
			</a>
		</li>
		<li class="email">
			<a href="mailto:?subject=<?php echo rawurlencode( html_entity_decode( the_title_attribute( [ 'echo' => false ] ), ENT_COMPAT, 'UTF-8' ) ); ?>&body=<?php echo esc_url_raw( get_permalink() ); ?>" target="_blank"<?php echo $nofollow; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<i class="fontawesome-icon medium circle-yes awb-icon-mail" aria-hidden="true"></i>
				<div class="fusion-woo-social-share-text">
					<span><?php echo esc_html_e( 'Email This Product', 'Avada' ); ?></span>
				</div>
			</a>
		</li>
	</ul>
<?php endif; ?>

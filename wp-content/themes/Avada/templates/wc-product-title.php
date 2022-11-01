<?php
/**
 * The product title.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.1.0
 */

?>
<h3 class="product-title">
	<a href="<?php echo esc_url_raw( get_the_permalink() ); ?>">
		<?php the_title(); ?>
	</a>
</h3>
<div class="fusion-price-rating">

<?php
/**
 * Description tab
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version	 2.0.0
 */

 if ( ! defined( 'ABSPATH' ) ) {
 	exit; // Exit if accessed directly
 }

global $post;

$heading = apply_filters( 'woocommerce_product_description_heading', __( 'Description', 'woocommerce' ) );
?>

<div class="post-content">
	<?php if ( $heading ): ?>
	  <h3 class="fusion-woocommerce-tab-title"><?php echo esc_html( $heading ); ?></h3>
	<?php endif; ?>

	<?php the_content(); ?>
</div>

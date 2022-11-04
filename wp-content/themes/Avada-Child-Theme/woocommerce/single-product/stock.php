<?php
/**
 * Single Product stock.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/stock.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<p class="stock <?php echo esc_attr($class); ?>"><strong>Intern lager: </strong>
    <?php
    echo wp_kses_post($availability);
    ?>
</p>

<p class="stock external_stock_info"><strong>Fjärr lager: </strong>
    <?php

    $check_external_stock = 0;

    $check_external_stock = get_post_meta($product->get_id(), 'custom_field');

    if ($check_external_stock == 0 || $check_external_stock == null) {
        echo 'Beställningsvara';
    } else {
        echo $check_external_stock[0] . ' i lager';
    }

    ?>
</p>
<style>
    .fusion-body .fusion-woo-price-tb {
        display: block !important;
    }
</style>
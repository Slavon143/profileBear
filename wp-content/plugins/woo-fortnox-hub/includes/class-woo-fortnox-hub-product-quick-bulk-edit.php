<?php

defined('ABSPATH') || exit;

if (!class_exists('WC_Fortnox_Hub_Quick_Bulk_Edit', false)) {

    class WC_Fortnox_Hub_Quick_Bulk_Edit
    {

        public function __construct()
        {
            add_action('woocommerce_product_quick_edit_end', array($this, 'quick_edit_fields'));
            add_action('woocommerce_product_quick_edit_save', array($this, 'quick_edit_save'));
            add_action('manage_product_posts_custom_column', array($this, 'generate_data'), 100, 2);
        }

        public function quick_edit_fields()
        {
            ?>
            <br class="clear" />
            <div class="fortnox_fields">
                <label>
                    <span class="title"><?php esc_html_e('Mfg.', 'woo-fortnox-integration');?></span>
                    <span class="input-text-wrap">
                        <input type="text" name="_fortnox_manufacturer" class="text fortnox_manufacturer" placeholder="<?php esc_attr_e('Manufacturer', 'woo-fortnox-integration');?>" value="">
                    </span>
                </label>
                <br class="clear" />
                <label>
                    <span class="title"><?php esc_html_e('Mfg. article', 'woo-fortnox-integration');?></span>
                    <span class="input-text-wrap">
                        <input type="text" name="_fortnox_manufacturer_article_number" class="text fortnox_manufacturer_article_number" placeholder="<?php esc_attr_e('Manufacturer article', 'woo-fortnox-integration');?>" value="">
                    </span>
                </label>
                <br class="clear" /><label>
                    <span class="title"><?php esc_html_e('Purchase Price (' . get_woocommerce_currency_symbol() . ')', 'woo-fortnox-integration');?></span>
                    <span class="input-text-wrap">
                        <input type="text" name="_fortnox_purchase_price" class="text wc_input_price fortnox_purchase_price" placeholder="<?php esc_attr_e('Purchase price', 'woo-fortnox-integration');?>" value="">
                    </span>
                </label>
                <br class="clear" /><label>
                    <span class="title"><?php esc_html_e('Stock place', 'woo-fortnox-integration');?></span>
                    <span class="input-text-wrap">
                        <input type="text" name="_fortnox_stock_place" class="text fortnox_stock_place" placeholder="<?php esc_attr_e('Stock place', 'woo-fortnox-integration');?>" value="">
                    </span>
                </label>
                <br class="clear" /><label>
                    <span class="title"><?php esc_html_e('Unit', 'woo-fortnox-integration');?></span>
                    <span class="input-text-wrap">
                        <input type="text" name="_fortnox_unit" class="text fortnox_unit" placeholder="<?php esc_attr_e('Unit', 'woo-fortnox-integration');?>" value="">
                    </span>
                </label>
                <br class="clear" /><label>
                    <span class="title"><?php esc_html_e('Barcode', 'woo-fortnox-integration');?></span>
                    <span class="input-text-wrap">
                        <input type="text" name="_fortnox_barcode" class="text fortnox_barcode" placeholder="<?php esc_attr_e('Barcode', 'woo-fortnox-integration');?>" value="">
                    </span>
                </label>
                <br class="clear" />
            </div>
<?php }

        /*
         * Quick Edit Save
         */

        public function quick_edit_save($product)
        {

            WCFH_Util::update_metadata($product, 'Manufacturer', isset($_REQUEST["_fortnox_manufacturer"]) ? wc_clean(wp_unslash($_REQUEST["_fortnox_manufacturer"])) : '');
            WCFH_Util::update_metadata($product, 'ManufacturerArticleNumber', isset($_REQUEST["_fortnox_manufacturer_article_number"]) ? wc_clean(wp_unslash($_REQUEST["_fortnox_manufacturer_article_number"])) : '');
            WCFH_Util::update_metadata($product, 'PurchasePrice', isset($_REQUEST["_fortnox_purchase_price"]) ? wc_clean(wp_unslash($_REQUEST["_fortnox_purchase_price"])) : '');
            WCFH_Util::update_metadata($product, 'StockPlace', isset($_REQUEST["_fortnox_stock_place"]) ? wc_clean(wp_unslash($_REQUEST["_fortnox_stock_place"])) : '');
            WCFH_Util::update_metadata($product, 'Unit', isset($_REQUEST["_fortnox_unit"]) ? wc_clean(wp_unslash($_REQUEST["_fortnox_unit"])) : '');
            WCFH_Util::update_metadata($product, 'EAN', isset($_REQUEST["_fortnox_barcode"]) ? wc_clean(wp_unslash($_REQUEST["_fortnox_barcode"])) : '');

            $product->save();
        }

        public function generate_data($column, $post_id)
        {
            switch ($column) {
                case 'name':
                    echo '
                    <div class="fortnox_fields">
                    <div class="hidden" id="fortnox_inline_' . absint($post_id) . '">
                    <div class="fortnox_manufacturer">' . esc_html(WCFH_Util::get_metadata($post_id, 'Manufacturer')) . '</div>
                    <div class="fortnox_manufacturer_article_number">' . esc_html(WCFH_Util::get_metadata($post_id, 'ManufacturerArticleNumber')) . '</div>
                    <div class="fortnox_purchase_price">' . esc_html(WCFH_Util::get_metadata($post_id, 'PurchasePrice')) . '</div>
                    <div class="fortnox_stock_place">' . esc_html(WCFH_Util::get_metadata($post_id, 'StockPlace')) . '</div>
                    <div class="fortnox_unit">' . esc_html(WCFH_Util::get_metadata($post_id, 'Unit')) . '</div>
                    <div class="fortnox_barcode">' . esc_html(WCFH_Util::get_metadata($post_id, 'EAN')) . '</div>
                    </div>
                    ';
                    break;
            }
        }
    }
    new WC_Fortnox_Hub_Quick_Bulk_Edit();
}

<?php
if (!defined('ABSPATH')) {
    exit;
}
$wf_admin_img_path=WF_PKLIST_PLUGIN_URL . 'admin/images';
?>
<style type="text/css">
    .qrcode_promotion_content,.qrcode_promotion_img{width: 50%;}
    .qrcode_promotion_content{line-height: 15px;font-style: normal;font-weight: 500;font-size: 14px;position: relative;padding-left: 1.5em;}
    .qrcode_promotion_img img{width: 100%;height: 100%;}
    #qrcode_pre_placeholders{columns: 3;}
    .printnode_pre_placeholders{columns: 1;position: relative;float: left;}
    .qr_code_features_list > li{font-style: normal;font-weight: 500;font-size: 14px;line-height: 25px;list-style: none;position: relative;padding-left: 49px;margin: 0 0 15px 0;}
    .qr_code_features_list > li:before{content: '';position: absolute;height: 18px;width: 18px;background-image: url(<?php echo esc_url($wf_admin_img_path.'/point_out.png'); ?>);background-size: contain;background-repeat: no-repeat;background-position: center;left: 10px;margin-top: 6px;}
    .printnode_pre_placeholders > li:before{content: '';position: absolute;height: 18px;width: 18px;background-image: url(<?php echo esc_url($wf_admin_img_path.'/point_out.png'); ?>);background-size: contain;background-repeat: no-repeat;background-position: center;left: -13px;margin-top: 6px;}
    .qr_code_features_list{width: 80%;}
    .qr_code_for,#qrcode_pre_placeholders,.printnode_pre_placeholders{margin-left: 1em;}
    .qr_code_for > li{list-style: disc;}
    #qrcode_pre_placeholders > li{list-style: disc;}
    .printnode_pre_placeholders > li{line-height: 2em;margin-left: 15px;}
    #qrcode_pre_placeholders,.printnode_pre_placeholders{margin-top: 5px;}
    .buy_qrcode_btn{background: linear-gradient(90.67deg, #2608DF -34.86%, #3284FF 115.74%);box-shadow: 0px 4px 13px rgb(46 80 242 / 39%);border-radius: 5px;padding: 10px 35px 10px 35px;display: inline-block;font-style: normal;font-weight: bold;font-size: 14px;line-height: 18px;color: #FFFFFF;text-decoration: none;transition: all .2s ease;border: none;margin-left: 10px;margin-top: 10px;margin-bottom: 20px;}
    .buy_qrcode_btn:hover{box-shadow: 0px 4px 13px rgb(46 80 242 / 50%);text-decoration: none;transform: translateY(2px);transition: all .2s ease;color: #FFFFFF;}
    .wt_addon_div{margin: 15px 15px 25px 15px;background: #FCFCFC; border: 1px solid rgba(164, 164, 164, 0.67);}
</style>
<div class="wrap">
    <h2 class="wp-heading-inline" style="margin-bottom: 1em;">
    <?php _e('Addons','print-invoices-packing-slip-labels-for-woocommerce');?>: <?php _e('WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels
','print-invoices-packing-slip-labels-for-woocommerce');?>
    </h2>
    <div class="wf-tab-container">
        <div class="wt_addon_div">
            <div style="display:flex;">
                <div class="qrcode_promotion_content">
                    <h3 style="font-size:1.7em;"><?php _e("QR Code Addon for WooCommerce PDF Invoices","print-invoices-packing-slip-labels-for-woocommerce"); ?></h3>
                    <p style="font-size: 14px;"><?php _e('To help you comply with invoice mandates that require QR Codes','print-invoices-packing-slip-labels-for-woocommerce'); ?></p>
                    <ul class="qr_code_features_list">
                        <li><?php _e('Assign QR code to all of invoices generated for the orders in your store','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                        <li><?php _e('Assign different types of info in your QR code, including:','print-invoices-packing-slip-labels-for-woocommerce'); ?>
                            <ul class="qr_code_for">
                                <li><?php _e('Order number (default)', 'print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                                <li><?php _e('Invoice number','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                                <li>
                                    <?php _e('Custom details, as below:','print-invoices-packing-slip-labels-for-woocommerce'); ?>
                                    <ul id="qrcode_pre_placeholders">
                                        <li><?php _e('Invoice number','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                                        <li><?php _e('Order number','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                                        <li><?php _e('Invoice date','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                                        <li><?php _e('Order date','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                                        <li><?php _e('Seller tax ID','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                                        <li><?php _e('Buyer name','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                                        <li><?php _e('Seller name','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                                        <li><?php _e('Invoice total','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                                        <li><?php _e('Invoice total tax','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li><?php _e('Compatible with WooCommerce PDF Invoice, Packing Slips, Delivery Notes, and Shipping Label (Free and Pro versions)','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                    </ul>
                    <a href="https://www.webtoffee.com/product/qr-code-addon-for-woocommerce-pdf-invoices/?utm_source=free_plugin_addon_page&utm_medium=PDF_invoice_pro&utm_campaign=QR_Code_Addon&utm_content=<?php echo WF_PKLIST_VERSION; ?>" class="buy_qrcode_btn" target="_blank"><?php echo __('Get Plugin','print-invoices-packing-slip-labels-for-woocommerce'); ?></a>
                </div>
                <div class="qrcode_promotion_img">
                    <img src="<?php echo $wf_admin_img_path; ?>/qrcode_promotion_img.png">
                </div>
            </div>
        </div>
        <div class="wt_addon_div">
            <div style="display:flex;">
                <div class="qrcode_promotion_content" style="width:75%;">
                    <h3 style="font-size:1.7em;line-height: 1.2em;"><?php _e("Remote Print Addon for WooCommerce PDF Invoices – PrintNode","print-invoices-packing-slip-labels-for-woocommerce"); ?></h3>
                    <p style="font-size: 14px;"><?php _e('Enables remote cloud printing for WooCommerce PDF Invoices with PrintNode.','print-invoices-packing-slip-labels-for-woocommerce'); ?></p>
                    <ul class="printnode_pre_placeholders" style="width:45%;">
                        <li><?php _e('Supports PDF Printing','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                        <li><?php _e('Faster printing of WooCommerce PDF Invoices ','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                        <li><?php _e('Integration with PrintNode','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                    </ul>
                    <ul class="printnode_pre_placeholders" style="width:40%;">
                        <li><?php _e('Secure print with base64 and URI printing','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                        <li><?php _e('Automatic & Manual printing','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                        <li><?php _e('Compatible with Windows, macOS / OS X, Linux, and Raspberry Pi.','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                    </ul>
                    <a href="https://www.webtoffee.com/product/remote-print-woocommerce-pdf-invoice-printnode/?utm_source=free_plugin_addon_page&utm_medium=PDF_invoice_pro&utm_campaign=Remote_Print_Addon&utm_content=<?php echo WF_PKLIST_VERSION; ?>" class="buy_qrcode_btn" target="_blank"><?php echo __('Get Plugin','print-invoices-packing-slip-labels-for-woocommerce'); ?></a>
                </div>
                <div class="qrcode_promotion_img" style="width:35%;margin-left: auto;">
                    <img src="<?php echo $wf_admin_img_path; ?>/printnode_promotion_img.png">
                </div>
            </div>    
        </div>
        <div class="wt_addon_div">
            <div style="display:flex;">
                <div class="qrcode_promotion_content" style="width:75%;padding-right: 1.5em;">
                    <h3 style="font-size:1.7em;line-height: 1.2em;"><?php _e("mPDF add-on for PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels","print-invoices-packing-slip-labels-for-woocommerce"); ?></h3>
                    <p style="font-size: 14px;"><?php _e('This addon works with PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels plugin to support RTL layout and Unicode for different languages.','print-invoices-packing-slip-labels-for-woocommerce'); ?></p>
                    <ul class="printnode_pre_placeholders" style="width:45%;">
                        <li><?php _e('Handles 12 RTL languages','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                        <li><?php _e('Support for all languages including Unicode standard','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                    </ul>
                    <ul class="printnode_pre_placeholders" style="width:40%;">
                        <li><?php _e('Prints Hebrew and Arabic languages','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                        <li><?php _e('Enables easy print, download, and attachment provisions','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
                    </ul>
                    <a href="https://wordpress.org/plugins/mpdf-addon-for-pdf-invoices" class="buy_qrcode_btn" target="_blank"><?php echo __('Get Plugin','print-invoices-packing-slip-labels-for-woocommerce'); ?></a>
                </div>
                <div class="qrcode_promotion_img" style="width:35%;margin-left: auto;">
                    <img src="<?php echo $wf_admin_img_path; ?>/mpdf_promotion_img.png">
                </div>
            </div>    
        </div>
    </div>
</div>
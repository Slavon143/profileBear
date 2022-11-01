<?php
if (!defined('ABSPATH')) {
    exit;
}
$wf_admin_img_path=WF_PKLIST_PLUGIN_URL . 'admin/images';
$tab_items=array(
    "general"=>__("General", 'print-invoices-packing-slip-labels-for-woocommerce'),
    "invoice-number"=>__("Invoice Number", 'print-invoices-packing-slip-labels-for-woocommerce'),
);
$show_qrcode_placeholder = apply_filters('wt_pklist_show_qrcode_placeholder_in_template',false,$this->module_base);
if(!$show_qrcode_placeholder){
    $promote_img_url = 
    $tab_items['qrcode_promote'] = '<div style="display:flex;">QR Code<img src="'.$wf_admin_img_path.'/promote_crown.png" style="width: 12px;height: 12px;background: #FFA800;padding: 5px;margin-left: 4px;border-radius: 25px;"></div>';
}
$tab_items = apply_filters('wt_pklist_add_additional_tab_item_into_module',$tab_items,$this->module_base,$this->module_id);
?>
<div class="wrap">
    <h2 class="wp-heading-inline">
    <?php _e('Settings','print-invoices-packing-slip-labels-for-woocommerce');?>: <?php _e('Invoice','print-invoices-packing-slip-labels-for-woocommerce');?>
    </h2>
    <?php
        //webtoffee branding
        include WF_PKLIST_PLUGIN_PATH.'/admin/views/admin-settings-branding.php';
    ?>
    <div class="nav-tab-wrapper wp-clearfix wf-tab-head">
    <?php Wf_Woocommerce_Packing_List::generate_settings_tabhead($tab_items, 'module'); ?>
    </div>
    <div class="wf-tab-container">
        <?php
        //inside the settings form
        $setting_views_a=array(
            'general'=>'general.php',         
        );

        //outside the settings form
        $setting_views_b=array(                    
            'invoice-number'=>'invoice-number.php',    
        );

        if(!$show_qrcode_placeholder){
            $setting_views_b['qrcode_promote'] = 'qrcode_promote.php';
        }
        ?>
        <form method="post" class="wf_settings_form">
            <input type="hidden" value="invoice" class="wf_settings_base" />
            <input type="hidden" value="wf_save_settings" class="wf_settings_action" />
            <?php
            // Set nonce:
            if (function_exists('wp_nonce_field'))
            {
                wp_nonce_field('wf-update-invoice-'.WF_PKLIST_POST_TYPE);
            }
            foreach ($setting_views_a as $target_id=>$value) 
            {
                $settings_view=plugin_dir_path( __FILE__ ).$value;
                if(file_exists($settings_view))
                {
                    include $settings_view;
                }
            }
            ?>
            <?php 
            //settings form fields
            do_action('wf_pklist_module_settings_form');?>
        </form>
        <?php do_action('wt_pklist_add_additional_tab_content_into_module',$this->module_base,$this->module_id); ?>
        <?php
        foreach ($setting_views_b as $target_id=>$value) 
        {
            $settings_view=plugin_dir_path( __FILE__ ).$value;
            if(file_exists($settings_view))
            {
                include $settings_view;
            }
        }
        ?>
        <?php do_action('wf_pklist_module_out_settings_form',array(
            'module_id'=>$this->module_base
        ));?>
    </div>
</div>
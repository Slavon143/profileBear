<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.webtoffee.com/
 * @since      2.5.0
 *
 * @package    Wf_Woocommerce_Packing_List
 * @subpackage Wf_Woocommerce_Packing_List/admin/partials
 */

$wf_admin_view_path=plugin_dir_path(WF_PKLIST_PLUGIN_FILENAME).'admin/views/';
$wf_img_path=WF_PKLIST_PLUGIN_URL . 'images/';

?>
<div class="wrap">
    <h2 class="wp-heading-inline">
	<?php _e('Settings','print-invoices-packing-slip-labels-for-woocommerce');?>: 
	<?php _e('WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels', 'print-invoices-packing-slip-labels-for-woocommerce');?>
	</h2>
    <?php
        //webtoffee branding
        include WF_PKLIST_PLUGIN_PATH.'/admin/views/admin-settings-branding.php';
    ?>
    <div class="wf_settings_left">
    	<div class="nav-tab-wrapper wp-clearfix wf-tab-head">
    		<?php
    	    $tab_head_arr=array(
                'wf-documents'=>__('Documents','print-invoices-packing-slip-labels-for-woocommerce'),
    	        'wf-general'=>__('General','print-invoices-packing-slip-labels-for-woocommerce'),
    	        'wf-help'=>__('Help Guide','print-invoices-packing-slip-labels-for-woocommerce')
    	    );
    	    if(isset($_GET['debug']))
    	    {
    	        $tab_head_arr['wf-debug']='Debug';
    	    }
    	    Wf_Woocommerce_Packing_List::generate_settings_tabhead($tab_head_arr);
    	    ?>
    	</div>
    	<div class="wf-tab-container">
            <?php
            //inside the settings form
            $setting_views_a=array(
                'wf-general'=>'admin-settings-general.php',         
            );

            //outside the settings form
            $setting_views_b=array(   
                'wf-documents'=>'admin-settings-documents.php',          
                'wf-help'=>'admin-settings-help.php',           
            );
            if(isset($_GET['debug']))
            {
                $setting_views_b['wf-debug']='admin-settings-debug.php';
            }
            ?>
            <form method="post" class="wf_settings_form">
                <input type="hidden" value="main" class="wf_settings_base" />
                <input type="hidden" value="wf_save_settings" class="wf_settings_action" />
                <?php
                // Set nonce:
                if (function_exists('wp_nonce_field'))
                {
                    wp_nonce_field(WF_PKLIST_PLUGIN_NAME);
                }
                foreach ($setting_views_a as $target_id=>$value) 
                {
                    $settings_view=$wf_admin_view_path.$value;
                    if(file_exists($settings_view))
                    {
                        include $settings_view;
                    }
                }
                ?>
                <?php 
                //settings form fields for module
                do_action('wf_pklist_plugin_settings_form');?>           
            </form>
            <?php
            foreach ($setting_views_b as $target_id=>$value) 
            {
                $settings_view=$wf_admin_view_path.$value;
                if(file_exists($settings_view))
                {
                    include $settings_view;
                }
            }
            ?>
            <?php do_action('wf_pklist_plugin_out_settings_form');?> 
        </div>
    </div>
    <div class="wf_settings_right">
        <?php do_action('wt_pklist_settings_sidebar_before');?>
        <?php include $wf_admin_view_path."goto-pro.php"; ?>
    </div>
    <?php do_action('wt_pklist_settings_bottom');?>
</div>
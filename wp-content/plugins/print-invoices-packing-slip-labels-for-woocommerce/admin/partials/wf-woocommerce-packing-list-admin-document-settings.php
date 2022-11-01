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
?>
<div class="wrap">
    <h2 class="wp-heading-inline">
	<?php _e('Document Settings','print-invoices-packing-slip-labels-for-woocommerce');?>
	</h2>
    <?php
        //webtoffee branding
        include WF_PKLIST_PLUGIN_PATH.'/admin/views/admin-settings-branding.php';
    ?>
	<div class="nav-tab-wrapper wp-clearfix wf-tab-head">
		<?php
        $tab_head_arr=array(
            'wf-other-documents'=>__('Other documents','print-invoices-packing-slip-labels-for-woocommerce'),
        );
	    Wf_Woocommerce_Packing_List::generate_settings_tabhead($tab_head_arr,'document');
	    ?>
	</div>
	<div class="wf-tab-container">
        <form method="post" class="wf_settings_form">
            <input type="hidden" value="document" class="wf_settings_base" />
            <input type="hidden" value="wf_save_document_settings" class="wf_settings_action" />
            <?php
            // Set nonce:
            if (function_exists('wp_nonce_field'))
            {
                wp_nonce_field(WF_PKLIST_PLUGIN_NAME);
            }
            include $wf_admin_view_path.'admin-document-settings-page.php'; 
            //settings form fields for module
            do_action('wf_pklist_document_settings_form');?>           
        </form>
        <?php do_action('wf_pklist_document_out_settings_form');?> 
    </div>
</div>
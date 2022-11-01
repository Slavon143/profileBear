<?php
/**
 * PDF Invoice Admin notices
 *  
 *
 * @package  Wf_Woocommerce_Packing_List  
 */

if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists('Wf_Woocommerce_Packing_List_Admin_Notices')){

class Wf_Woocommerce_Packing_List_Admin_Notices
{
    public function __construct(){
        add_action('admin_notices', array($this, 'invoice_number_action_scheduler_notice'));
    }

    public function invoice_number_action_scheduler_notice(){
        $auto_generate = Wf_Woocommerce_Packing_List_Admin::check_before_auto_generating_invoice_no();
        if((true === $auto_generate["invoice_enabled"]) && (true === $auto_generate["auto_generate"]) && (100 < $auto_generate["order_empty_invoice_count"]))
        {
            $group = "wt_pklist_invoice_number_auto_generation";
            $pending_actions_url = admin_url( 'admin.php?page=wc-status&tab=action-scheduler&s=wt_pklist_schedule_auto_generate_invoice_number');
            if (true === as_next_scheduled_action('wt_pklist_schedule_auto_generate_invoice_number', array(), $group)) {
                echo '<div class="notice-info notice">
                <h3 style="margin: 10px 0;">'.__("Invoice Generation In Progress","print-invoices-packing-slip-labels-for-woocommerce").'</h3>
                <p>'.__("Invoice numbers are getting generated in the background. This process may take a little while, so please be patient.","print-invoices-packing-slip-labels-for-woocommerce").'</p>
                <p><a class="button button-primary" href="'.esc_url($pending_actions_url).'">'.__("View progress","print-invoices-packing-slip-labels-for-woocommerce").'</a></p>
                </div>';
            }
        }
    }
}
new Wf_Woocommerce_Packing_List_Admin_Notices();

}
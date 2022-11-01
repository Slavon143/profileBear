<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.webtoffee.com/
 * @since      2.5.0
 *
 * @package    Wf_Woocommerce_Packing_List
 * @subpackage Wf_Woocommerce_Packing_List/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      2.5.0
 * @package    Wf_Woocommerce_Packing_List
 * @subpackage Wf_Woocommerce_Packing_List/includes
 * @author     WebToffee <info@webtoffee.com>
 */
class Wf_Woocommerce_Packing_List_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    2.5.0
	 */
	public static function deactivate()
	{
		do_action("wt_pklist_deactivate");
        
        // delete the schedule of getting the empty invoice count
        as_unschedule_all_actions('update_empty_invoice_number_count', array(), "wt_pklist_get_invoice_number_count_auto_generation");
        
        // delete the schedule of generating invoice number for high number of orders
        as_unschedule_all_actions('wt_pklist_schedule_auto_generate_invoice_number', array(), "wt_pklist_invoice_number_auto_generation");
	}

}

<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
<p><?php _e('Configure the general settings required for the invoice.','print-invoices-packing-slip-labels-for-woocommerce');?></p>
<table class="wf-form-table">
    <tbody>
        <tr valign="top" class="">
            <th scope="row">
                <label for="" style="font-weight:bold;">
                    <?php echo __("Enable invoice",'print-invoices-packing-slip-labels-for-woocommerce'); ?><?php echo Wf_Woocommerce_Packing_List_Admin::set_tooltip('woocommerce_wf_enable_invoice',$this->module_id); ?>
                </label>
            </th>
            <td>
                <div class="wf_pklist_dashboard_checkbox">
                    <?php 
                        $v = Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_enable_invoice',$this->module_id);
                    ?>
                    <input type="checkbox" value="Yes" name="woocommerce_wf_enable_invoice" <?php echo ($v=='Yes' ? 'checked' : '');?> class="wf_slide_switch wt_document_module_enable" id="woocommerce_wf_enable_invoice">    
                </div>
            <td></td>
        </tr>
	<?php
    Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
        array(
            'type'=>'field_group_hr_line', //field type
            'option_name' => 'create_and_email_invoice_group_hr'
        ),
        array(
            'type'=>'field_group_sub_head', //field type
            'head'=>__('Create and email invoice','print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name' => 'create_and_email_invoice_group'
        ),
        array(
            'type'=>'order_st_multiselect',
            'label'=>__("Create invoice automatically",'print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name'=>"woocommerce_wf_generate_for_orderstatus",
            'help_text'=>__("Creates invoices for selected order statuses.",'print-invoices-packing-slip-labels-for-woocommerce'),
            'order_statuses'=>$order_statuses,
            'field_vl'=>array_flip($order_statuses),
            'attr'=>'',
        ),
        array(
            'type'=>"checkbox",
            'label'=>__("Attach invoice PDF in order email",'print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name'=>"woocommerce_wf_add_invoice_in_mail",
            'field_vl' => 'Yes',
            'help_text'=>__('Invoice in PDF format will be attached with the order email for chosen order statuses.','print-invoices-packing-slip-labels-for-woocommerce'),
        ),
        array(
            'type' => 'print_button_checkbox',
            'label'=>__("Show print invoice button for customers",'print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name' => 'wf_woocommerce_invoice_show_print_button',
            'checkbox_fields' => array(
                'order_listing' => __('My account - Order lists page','print-invoices-packing-slip-labels-for-woocommerce'),
                'order_details' => __('My account - Order details page', 'print-invoices-packing-slip-labels-for-woocommerce'),
                'order_email' => __('Order email','print-invoices-packing-slip-labels-for-woocommerce'),
            ),
            'module_base'=>$this->module_base,
        ),
        array(
            'type'=>'field_group_hr_line', //field type
            'option_name' => 'create_and_email_invoice_group_hr'
        ),
        array(
            'type'=>'field_group_sub_head', //field type
            'head'=>__('Invoice details','print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name' => 'invoice_details_group'
        ),
        array(
            'type'=>"radio",
            'label'=>__("Invoice date",'print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name'=>"woocommerce_wf_orderdate_as_invoicedate",
            'radio_fields'=>array(
                'Yes'=>__('Order date','print-invoices-packing-slip-labels-for-woocommerce'),
                'No'=>__('Invoice created date','print-invoices-packing-slip-labels-for-woocommerce')
            ),
        ),
        array(
            'type'=>"additional_fields",
            'label'=>__("Order meta fields", 'print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name'=>'wf_'.$this->module_base.'_contactno_email',
            'module_base'=>$this->module_base,
        ),
        array(
            'type'=>"uploader",
            'label'=>__("Custom logo for invoice",'print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name'=>"woocommerce_wf_packinglist_logo",
            'help_text'=>__('If left blank, defaulted to logo from General settings.Ensure to select company logo from ‘Invoice > Customize > Company Logo’ to reflect on the invoice. Recommended size is 150x50px.','print-invoices-packing-slip-labels-for-woocommerce'),
        ),
        array(
            'type'=>'field_group_hr_line', //field type
            'option_name' => 'create_and_email_invoice_group_hr'
        ),
        array(
            'type'=>'field_group_sub_head', //field type
            'head'=>__('Advanced options','print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name' => 'invoice_advanced_option_group'
        ),
    ), $this->module_id);
    ?>
    <?php 
    $order_meta_doc_url = 'https://www.webtoffee.com/adding-additional-fields-pdf-invoices-woocommerce/#add-order-meta';
    
    Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
        
    ), $this->module_id);

    Wf_Woocommerce_Packing_List_Admin::generate_form_advanced_fields(array(
        array(
            'type'=>"checkbox",
            'label'=>__("Generate invoices for old orders",'print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name'=>"wf_woocommerce_invoice_prev_install_orders",
            'help_text'=>__("Enable to generate invoices for orders created before the installation of the plugin.",'print-invoices-packing-slip-labels-for-woocommerce'),
            'field_vl' => 'Yes',
            'field_group'=>'advanced_field',
        ),
        array(
            'type'=>"checkbox",
            'label'=>__("Generate invoices for free orders",'print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name'=>"wf_woocommerce_invoice_free_orders",
            'help_text'=>__("Enable to create invoices for free orders.",'print-invoices-packing-slip-labels-for-woocommerce'),
            'field_vl' => "Yes",
            'field_group'=>'advanced_field',
        ),
        array(
            'type'=>"checkbox",
            'label'=>__("Display free line items in the invoice",'print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name'=>"wf_woocommerce_invoice_free_line_items",
            'help_text'=>__("Enable to display free line items in the invoices.",'print-invoices-packing-slip-labels-for-woocommerce'),
            'field_vl' => 'Yes',
            'field_group'=>'advanced_field',
        ),
        array(
            'type' => "pdf_name_select",
            'label' => __("PDF name format", 'print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name' => 'woocommerce_wf_custom_pdf_name',
            'help_text'=>__("Select a name format for PDF invoice that includes invoice/order number.",'print-invoices-packing-slip-labels-for-woocommerce'),
            'field_group'=>'advanced_field',
        ),
        array(
            'type' => "pdf_name_prefix",
            'label' => __("Custom PDF name prefix", 'print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name' => 'woocommerce_wf_custom_pdf_name_prefix',
            'pdf_name_prefix_label' => 'yes',
            'help_text'=>__("Input a custom prefix for ‘PDF name format’ that will appear at the beginning of the name. Defaulted to ‘Invoice_’.",'print-invoices-packing-slip-labels-for-woocommerce'),
            'field_group'=>'advanced_field',
        ),
    ), $this->module_id);
    ?>
    </tbody>
</table>
<?php 
include plugin_dir_path( WF_PKLIST_PLUGIN_FILENAME )."admin/views/admin-settings-save-button.php";
?>
</div>
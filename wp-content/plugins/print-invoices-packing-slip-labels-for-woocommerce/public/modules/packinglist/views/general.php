<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<h3 style="margin-bottom:0px; padding-bottom:5px; border-bottom:dashed 1px #ccc;"><?php _e('Packing slip', 'print-invoices-packing-slip-labels-for-woocommerce'); ?></h3>
<table class="form-table wf-form-table">
	<?php
	Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
		array(
			'type'=>"radio",
			'label'=>__("Include product image",'print-invoices-packing-slip-labels-for-woocommerce'),
			'option_name'=>"woocommerce_wf_attach_image_packinglist",
			'field_name'=>$this->module_id."[woocommerce_wf_attach_image_packinglist]",
			'radio_fields'=>array(
				'Yes'=>__('Yes','print-invoices-packing-slip-labels-for-woocommerce'),
				'No'=>__('No','print-invoices-packing-slip-labels-for-woocommerce')
			),
			'help_text'=>__("Add product image in product table",'print-invoices-packing-slip-labels-for-woocommerce'),
		),
		array(
            'type'=>"radio",
            'label'=>__("Add customer note",'print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name'=>"woocommerce_wf_add_customer_note_in_packinglist",
            'field_name'=>$this->module_id."[woocommerce_wf_add_customer_note_in_packinglist]",
            'radio_fields'=>array(
                'Yes'=>__('Yes','print-invoices-packing-slip-labels-for-woocommerce'),
                'No'=>__('No','print-invoices-packing-slip-labels-for-woocommerce')
            ),
            'help_text'=>__("Add customer note in packing slip",'print-invoices-packing-slip-labels-for-woocommerce'),
        ),
        array(
            'type'=>"radio",
            'label'=>__("Add footer",'print-invoices-packing-slip-labels-for-woocommerce'),
            'option_name'=>"woocommerce_wf_packinglist_footer_pk",
            'field_name'=>$this->module_id."[woocommerce_wf_packinglist_footer_pk]",
            'radio_fields'=>array(
                'Yes'=>__('Yes','print-invoices-packing-slip-labels-for-woocommerce'),
                'No'=>__('No','print-invoices-packing-slip-labels-for-woocommerce')
            ),
            'help_text'=>__("Add footer in packing slip",'print-invoices-packing-slip-labels-for-woocommerce'),
        ),
	),$this->module_id);
	?>
</table>
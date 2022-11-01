<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

$order_meta_selected_list = Wf_Woocommerce_Packing_List::get_option('wf_additional_data_fields');
$first_meta_key = array_key_first($order_meta_selected_list);
if (null === $first_meta_key) {
    $meta_key_label = "";
} else {
    $meta_key_label = $order_meta_selected_list[$first_meta_key];
}
?>
<div class="wt_pklist_custom_field_form" style="display:none;">
	<div class="wt_pklist_checkout_field_tab">
		<div class="wt_pklist_custom_field_tab_head active_tab" data-target="add_new" data-add-title="<?php _e('Add new', 'print-invoices-packing-slip-labels-for-woocommerce');?>" data-edit-title="<?php _e('Edit','print-invoices-packing-slip-labels-for-woocommerce');?>">
			<span class="wt_pklist_custom_field_tab_head_title wfte_custom_field_tab_head_title"> <?php 
			if(empty($order_meta_selected_list)){
				_e('Add new', 'print-invoices-packing-slip-labels-for-woocommerce');
			}else{
				_e('Edit', 'print-invoices-packing-slip-labels-for-woocommerce');
			}
		?></span>
			<div class="wt_pklist_custom_field_tab_head_patch"></div>
		</div>
		<div class="wt_pklist_custom_field_tab_head wt_add_new_pro_tab" id="wt_add_new_order_meta" onclick="order_meta_add_buy_pro();" style="<?php if(empty($order_meta_selected_list)){echo 'display:none;'; } ?>">
			<span class="wt_pklist_custom_field_tab_head_title"> 
				<?php _e('Add new', 'print-invoices-packing-slip-labels-for-woocommerce'); ?>
			</span>
			<div class="wt_pklist_custom_field_tab_head_patch"></div>
		</div>
	</div>
	<div class="wt_pklist_custom_field_tab_content active_tab" data-id="add_new">
    	<div class='wt_pklist_custom_field_tab_form_row wt_pklist_custom_field_form_notice'>
    		<?php
    			if(empty($order_meta_selected_list)){
    				_e('You can add custom/predefined order meta using its key', 'print-invoices-packing-slip-labels-for-woocommerce');
    			}else{
    				_e('You can edit an existing item by using its key.', 'print-invoices-packing-slip-labels-for-woocommerce');
    			}
    		?>
    	</div>
    	<div class='wt_pklist_custom_field_tab_form_row'>
			<div style='width:48%; float:left;'><?php _e('Field Name', 'print-invoices-packing-slip-labels-for-woocommerce'); ?><i style="color:red;">*</i>: <input type='text' name='wt_pklist_new_custom_field_title' value="<?php echo $meta_key_label; ?>" data-required="1" style='width:100%'/></div>
			<div style='width:48%; float:right;'><?php _e('Meta Key', 'print-invoices-packing-slip-labels-for-woocommerce'); ?><i style="color:red;">*</i>: 
				<input type="text" value="<?php echo $first_meta_key; ?>" name="wt_pklist_new_custom_field_key" class="wt_pklist_new_custom_field_key" oninput="do_auto_complete()">
			</div>
		</div>
		<div class='wt_pklist_custom_field_tab_form_row wfte_pro_order_meta_alert_box' style="display:none;">
			<p class="wfte-alert wfte-alert-info"><?php echo __('To add more than one custom order meta,','print-invoices-packing-slip-labels-for-woocommerce'); ?> <a href="https://www.webtoffee.com/product/woocommerce-pdf-invoices-packing-slips/" target="_blank" style="font-weight:bold; text-decoration: none;"><?php echo __('upgrade to premium version','print-invoices-packing-slip-labels-for-woocommerce'); ?> </a></p>
		</div>
	</div>
	<div class="wt_pklist_custom_field_tab_content" data-id="list_view" style="height:155px; overflow:auto;">
		
	</div>
</div>
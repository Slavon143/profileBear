<?php
if (!defined('ABSPATH')) {
	exit;
}
$query = new WC_Order_Query( array(
    'limit' => 1,
    'orderby' => 'date',
    'order' => 'DESC',
    'parent'=>0,
) );

$orders = $query->get_orders();
$order_number = "123";
if(count($orders)>0)
{
	$order=$orders[0];
	$order_number=$order->get_order_number();
}

$current_invoice_number =(int) Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_Current_Invoice_number',$this->module_id);
$current_invoice_number=($current_invoice_number<0 ? 0 : $current_invoice_number);
$inv_num=++$current_invoice_number;
$date_frmt_tooltip=__('Click to append with existing data','print-invoices-packing-slip-labels-for-woocommerce');
$use_wc_order_number = Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_invoice_as_ordernumber',$this->module_id);
?>
<div class="wf-tab-content" data-id="<?php echo esc_attr($target_id);?>" style="position: relative;">
	<style type="text/css">
	.wf_inv_num_frmt_hlp_btn{ cursor:pointer; }
	.wf_inv_num_frmt_hlp table thead th{ font-weight:bold; text-align:left; }
	.wf_inv_num_frmt_hlp table tbody td{ text-align:left; }
	.wf_inv_num_frmt_hlp .wf_pklist_popup_body{min-width:300px; padding:20px;}
	.wf_inv_num_frmt_append_btn{ cursor:pointer; }
	</style>
	<!-- Invoice number Prefix/Suffix help popup -->
	<div class="wf_inv_num_frmt_hlp wf_pklist_popup">
		<div class="wf_pklist_popup_hd">
			<span style="line-height:40px;" class="dashicons dashicons-calendar-alt"></span> <?php _e('Date formats','print-invoices-packing-slip-labels-for-woocommerce');?>
			<div class="wf_pklist_popup_close">X</div>
		</div>
		<div class="wf_pklist_popup_body">
			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th><?php _e('Format','print-invoices-packing-slip-labels-for-woocommerce');?></th><th><?php _e('Output','print-invoices-packing-slip-labels-for-woocommerce');?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[F]</a></td>
						<td><?php echo date('F'); ?></td>
					</tr>
					<tr>
						<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[dS]</a></td>
						<td><?php echo date('dS'); ?></td>
					</tr>
					<tr>
						<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[M]</a></td>
						<td><?php echo date('M'); ?></td>
					</tr>
					<tr>
						<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[m]</a></td>
						<td><?php echo date('m'); ?></td>
					</tr>
					<tr>
						<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[d]</a></td>
						<td><?php echo date('d'); ?></td>
					</tr>
					<tr>
						<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[D]</a></td>
						<td><?php echo date('D'); ?></td>
					</tr>
					<tr>
						<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[y]</a></td>
						<td><?php echo date('y'); ?></td>
					</tr>
					<tr>
						<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[Y]</a></td>
						<td><?php echo date('Y'); ?></td>
					</tr>
					<tr>
						<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[d/m/y]</a></td>
						<td><?php echo date('d/m/y'); ?></td>
					</tr>
					<tr>
						<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[d-m-Y]</a></td>
						<td><?php echo date('d-m-Y'); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<p style="width: 75%;">
		<?php _e('Use the configurations below to set up a custom invoice number with prefix/suffix/number series or mirror the order number respectively.','print-invoices-packing-slip-labels-for-woocommerce');?>
	</p>
	<input type="hidden" value="<?php echo esc_attr($order_number); ?>" id="sample_invoice_number">
	<input type="hidden" id="sample_current_invoice_number" value="<?php echo esc_attr($current_invoice_number); ?>">
	<div id="invoice_number_prev_div" style="width: auto;border: 1px solid #dadadc;padding: 5px 12px;border-radius: 5px;display: inline-block;background: #f0f0f1;position: absolute;<?php echo is_rtl() ? "left: 1%;" : "right: 1%;"?>top: 0;">
		<p style="font-weight: bold;line-height: 0;"><?php echo __('PREVIEW','print-invoices-packing-slip-labels-for-woocommerce'); ?></p>
		<p style="margin: 1em  0 0.5em 0; <?php if($use_wc_order_number == "No"){ echo "display: none;"; }?>" id="preview_invoice_number_text"><?php echo __('If the order number is','print-invoices-packing-slip-labels-for-woocommerce'); ?> <?php echo $order_number; ?>, <br> <?php echo __("the invoice number would be",'print-invoices-packing-slip-labels-for-woocommerce'); ?> </p>
		<p style="margin: 1em  0 0.5em 0; <?php if($use_wc_order_number == "Yes"){ echo "display: none;"; }?>" id="preview_invoice_number_text_custom"><?php _e('Your next invoice number would be','print-invoices-packing-slip-labels-for-woocommerce'); ?></p>
		<span id="preview_invoice_number" style="background: #ffffff;padding: 5px;color: #3c434a;border-radius: 3px;float: left;font-weight: bold;margin-bottom: 0.5em;"></span>	
	</div>
	<form method="post" class="wf_settings_form">
	    <input type="hidden" value="invoice" class="wf_settings_base" />
	    <input type="hidden" name="update_sequential_number" value="invoice">
	    <input type="hidden" value="wf_save_settings" class="wf_settings_action" />
	<?php
	    // Set nonce:
	    if (function_exists('wp_nonce_field'))
	    {
	        wp_nonce_field(WF_PKLIST_PLUGIN_NAME);
	    }
	?>
	<table class="form-table wf-form-table">
		<?php
		Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
			array(
				'type'=>"radio",
				'label'=>__("Set invoice number using",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_invoice_as_ordernumber",
				'radio_fields'=>array(
					'Yes'=>__('Woocommerce order number','print-invoices-packing-slip-labels-for-woocommerce'),
					'No'=>__('Custom number series','print-invoices-packing-slip-labels-for-woocommerce')
				),
				'form_toggler'=>array(
					'type'=>'parent',
					'target'=>'wwpl_custom_inv_no',
				),
				'option_alignment' => 'vertical',
				'field_class' => 'invoice_preview_assert',
			),
		), $this->module_id);
		?>
		<?php
		$opt_name="woocommerce_wf_invoice_start_number";
		$vl=Wf_Woocommerce_Packing_List::get_option($opt_name,$this->module_id);
		$tt_text=Wf_Woocommerce_Packing_List_Admin::set_tooltip($opt_name,$this->module_id); //tooltip text
		?>
		<tr id="woocommerce_wf_invoice_start_number_tr" wf_frm_tgl-id="wwpl_custom_inv_no" wf_frm_tgl-val="No" wf_frm_tgl-lvl="2">
			<th><label><?php _e("Invoice Start Number",'print-invoices-packing-slip-labels-for-woocommerce'); ?> <?php echo $tt_text; ?></label></th>
			<td>
				<div class="wf-form-group">			
					<input type="number" min="1" step="1" readonly="" style="background:#eee; width:60%; float:left;" name="<?php echo $opt_name;?>" value="<?php echo $vl;?>" class="invoice_preview_assert" id="invoice_start_number">
					<input style="float: right;" id="reset_invoice_button" type="button"  class="button button-primary" value="<?php _e('Reset','print-invoices-packing-slip-labels-for-woocommerce'); ?>"/>
				</div>
				<?php
				$opt_name="woocommerce_wf_Current_Invoice_number";
				$vl=Wf_Woocommerce_Packing_List::get_option($opt_name,$this->module_id);
				?>
				<input type="hidden" class="wf_current_invoice_number" value="<?php echo $vl;?>" name="<?php echo $opt_name;?>" class="invoice_preview_assert">
			</td>
			<td></td>
		</tr>
		<?php
		Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
			array(
				'type'=>'select',
				'label'=>__("Invoice number format", 'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_invoice_number_format",
				'select_fields'=>array(
					'[number]'=>__('[number]', 'print-invoices-packing-slip-labels-for-woocommerce'),
					'[number][suffix]'=>__('[number][suffix]', 'print-invoices-packing-slip-labels-for-woocommerce'),
					'[prefix][number]'=>__('[prefix][number]', 'print-invoices-packing-slip-labels-for-woocommerce'),
					'[prefix][number][suffix]'=>__('[prefix][number][suffix]', 'print-invoices-packing-slip-labels-for-woocommerce'),
				),
				'field_class' => 'invoice_preview_assert'
			),
		), $this->module_id);
		?>
		<?php
		Wf_Woocommerce_Packing_List_Admin::generate_form_field(array(
			array(
				'label'=>__("Prefix",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_invoice_number_prefix",
				'help_text'=>sprintf(__("Use any of the %s date formats %s or alphanumeric characters.", 'print-invoices-packing-slip-labels-for-woocommerce'), '<a class="wf_inv_num_frmt_hlp_btn" data-wf-trget="woocommerce_wf_invoice_number_prefix">', '</a>'),
				'field_class' => 'invoice_preview_assert'
			),
			array(
				'label'=>__("Suffix",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_invoice_number_postfix",
				'help_text'=>sprintf(__("Use any of the %s date formats %s or alphanumeric characters.", 'print-invoices-packing-slip-labels-for-woocommerce'), '<a class="wf_inv_num_frmt_hlp_btn" data-wf-trget="woocommerce_wf_invoice_number_postfix">', '</a>'),
				'field_class' => 'invoice_preview_assert'
			),
			array(
				'type'=>'number',
				'label'=>__("Invoice length",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_invoice_padding_number",
				'attr'=>'min="0"',
				'help_text'=>__('Indicates the total length of the invoice number, excluding the length of prefix and suffix if added. If the length of the generated invoice number is less than the provided, it will be padded with ‘0’. E.g if you specify 7 as invoice length and your invoice number is 8009, it will be represented as 0008009 in the respective documents.', 'print-invoices-packing-slip-labels-for-woocommerce'),
				'field_class' => 'invoice_preview_assert'
			)
		), $this->module_id);
		?>	
	</table>
	<?php
	$settings_button_title=__('Save Invoice number settings', 'print-invoices-packing-slip-labels-for-woocommerce');
	include plugin_dir_path( WF_PKLIST_PLUGIN_FILENAME )."admin/views/admin-settings-save-button.php";
	?>
	</form>
</div>
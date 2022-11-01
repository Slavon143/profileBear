<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="wf-tab-content" data-id="wf-other-documents">
    <?php
	$html='';
	$html=apply_filters('wf_pklist_document_setting_fields', $html);
	global $wp_filter;
	
	//only if anybody hooked to the above action 
	if(isset($wp_filter['wf_pklist_document_setting_fields']))
    {
		echo $html;
	}else
	{
		$enable_save_btn=0; //remove settings button
		$gen_settings_url=admin_url('admin.php?page=wf_woocommerce_packing_list#wf-documents');
		?>
		<p class="wt_info_box" style="margin:30px 0px; font-size:14px; line-height:26px;">
			<?php _e('Either of the document types, Packing  slip, Shipping label, Dispatch label or Delivery note should be activated to view relevant settings.', 'print-invoices-packing-slip-labels-for-woocommerce');?> 
			<?php echo sprintf(__('Go to %s General Settings %s Documents %s in order to activate.', 'print-invoices-packing-slip-labels-for-woocommerce'), '<a href="'.$gen_settings_url.'"><b>', '->', '</b></a>'); ?>
		</p>
		<?php
	}	
	?>
	<?php 
	include "admin-settings-save-button.php";
	?>
</div>
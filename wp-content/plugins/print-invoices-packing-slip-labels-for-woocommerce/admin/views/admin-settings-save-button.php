<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
$settings_button_title=isset($settings_button_title) ? $settings_button_title : __('Update Settings', 'print-invoices-packing-slip-labels-for-woocommerce');
$enable_btn=(int) (!isset($enable_save_btn) ? 1 : $enable_save_btn);
?>
<div style="clear: both;"></div>
<div class="wf-plugin-toolbar bottom">
    <div class="left">
    </div>
    <div class="right">
    	<?php
    	if($enable_btn==1)
    	{
    	?>
        	<input type="submit" name="update_admin_settings_form" value="<?php echo $settings_button_title; ?>" class="button button-primary" style="float:right;"/>
        	<span class="spinner" style="margin-top:11px"></span>
        <?php
    	}
    	?>
    </div>
</div>
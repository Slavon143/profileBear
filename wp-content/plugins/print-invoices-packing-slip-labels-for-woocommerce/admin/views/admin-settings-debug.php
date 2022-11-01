<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
	<h3><?php _e('Debug','print-invoices-packing-slip-labels-for-woocommerce');?></h3>
	<p><?php _e('Caution: Settings here are only for advanced users.','print-invoices-packing-slip-labels-for-woocommerce');?></p>
	<form method="post">
		<?php
	    // Set nonce:
	    if(function_exists('wp_nonce_field'))
	    {
	        wp_nonce_field(WF_PKLIST_PLUGIN_NAME);
	    }
	    ?>
		<table class="wf-form-table">
			<?php
	        $wt_pklist_common_modules=get_option('wt_pklist_common_modules');
	        if($wt_pklist_common_modules===false)
	        {
	            $wt_pklist_common_modules=array();
	        }
	        ?>
	        <tr valign="top">
	            <th scope="row">Common modules</th>
	            <td>
	                <?php
	                foreach($wt_pklist_common_modules as $k=>$v)
	                {
	                    
	                    echo '<input type="checkbox" name="wt_pklist_common_modules['.$k.']" value="1" '.($v==1 ? 'checked' : '').' /> ';
	                    echo $k;
	                    echo '<br />';
	                }
	                ?>
	            </td>
	        </tr>
	        <?php
	        $wt_pklist_admin_modules=get_option('wt_pklist_admin_modules');
	        if($wt_pklist_admin_modules===false)
	        {
	            $wt_pklist_admin_modules=array();
	        }
	        ?>
	        <tr valign="top">
	            <th scope="row">Admin modules</th>
	            <td>
	                <?php
	                foreach($wt_pklist_admin_modules as $k=>$v)
	                {
	                    
	                    echo '<input type="checkbox" name="wt_pklist_admin_modules['.$k.']" value="1" '.($v==1 ? 'checked' : '').' /> ';
	                    echo $k;
	                    echo '<br />';
	                }
	                ?>
	            </td>
	        </tr>

	        <tr valign="top">
	            <th scope="row">&nbsp;</th>
	            <td>
	                <input type="submit" name="wt_pklist_admin_modules_btn" value="Save" class="button-primary">
	            </td>
	        </tr>	
		</table>
	</form>
<?php
//advanced settings form fields for module
do_action('wt_pklist_module_settings_debug');
?>
</div>
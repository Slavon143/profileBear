<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
function wt_code_view_colors($txt)
{
  $matches=array();

  /* add string color */
  $re = '/\'(.*?)\'/m';
  $txt=preg_replace($re,'<span class={str_css}>${0}</span>',$txt);

  $re = '/"(.*?)"/m';
  $txt=preg_replace($re,'<span class={str_css}>${0}</span>',$txt);


  /* add comment color */
  $re = '/\/\*(.*?) *\//m';
  $txt=preg_replace($re,'<i class={cmt_css}>${0}</i>',$txt);

  /* add built in function color */
  $inbuilt=array('/strtotime/');
  $txt=preg_replace($inbuilt,'<i class={inbuilt_fn}>${0}</i>',$txt);


  /*  color */
  $inbuilt=array('/function/','/return/','/if/','/else/','/elseif/','/switch/','/true/','/false/');
  $txt=preg_replace($inbuilt,'<i class={fn_str}>${0}</i>',$txt);

  
  return $txt;
}
?>
<style type="text/css">
.wf_filters_doc{ border:solid 1px #ccc; margin-bottom:15px; }
.wf_filters_doc td{ padding:5px 5px; font-size:14px; }
.wf_filters_doc td p{ margin:0px; padding:0px; font-size:14px; }
.wf_filter_doc_params{ color:#b46b6b; }
.wf_filter_doc_eg{ background:#fff; padding:5px; border:solid 1px #ececec; color:#000; margin:10px 0px; font-size:14px; display:none; }
.wf_filter_doc_eg div{ padding-left:30px; }
.wf_filter_doc_eg .inbuilt_fn{color:#c81cc8;}
.wf_filter_doc_eg .fn_str{color:#1111e8;}
.wf_filter_doc_eg .str_css{color:#679d67;}
.wf_filter_doc_eg .cmt_css{color:gray;}
</style>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
	<ul class="wf_sub_tab">
		<li style="border-left:none; padding-left: 0px;" data-target="filters"><a><?php _e('Filters','print-invoices-packing-slip-labels-for-woocommerce');?></a></li>
		<li data-target="help-links"><a><?php _e('Help Links','print-invoices-packing-slip-labels-for-woocommerce'); ?></a></li>
		<li data-target="system-info"><a><?php _e('System Info','print-invoices-packing-slip-labels-for-woocommerce'); ?></a></li>
	</ul>
	<div class="wf_sub_tab_container">		
		<div class="wf_sub_tab_content" data-id="help-links" style="display:block;">
			<h3><?php _e('Help Links','print-invoices-packing-slip-labels-for-woocommerce'); ?></h3>
			<ul class="wf-help-links">
			    <li>
			        <img src="<?php echo WF_PKLIST_PLUGIN_URL;?>assets/images/documentation.png">
			        <h3><?php _e('Documentation', 'print-invoices-packing-slip-labels-for-woocommerce'); ?></h3>
			        <p><?php _e('Refer to our documentation to setup and get started', 'print-invoices-packing-slip-labels-for-woocommerce'); ?></p>
			        <a target="_blank" href="https://www.webtoffee.com/woocommerce-pdf-invoices-packing-slips-delivery-notes-shipping-labels-userguide-free-version/" class="button button-primary">
			            <?php _e('Documentation', 'print-invoices-packing-slip-labels-for-woocommerce'); ?>        
			        </a>
			    </li>
			    <li>
			        <img src="<?php echo WF_PKLIST_PLUGIN_URL;?>assets/images/support.png">
			        <h3><?php _e('Help and Support','print-invoices-packing-slip-labels-for-woocommerce'); ?></h3>
			        <p><?php _e('We would love to help you on any queries or issues.','print-invoices-packing-slip-labels-for-woocommerce'); ?></p>
			        <a target="_blank" href="https://www.webtoffee.com/support/" class="button button-primary">
			            <?php _e('Contact Us', 'print-invoices-packing-slip-labels-for-woocommerce'); ?>
			        </a>
			    </li>               
			</ul>
		</div>
		<div class="wf_sub_tab_content" data-id="filters">
			<?php
				include WF_PKLIST_PLUGIN_PATH.'/admin/views/admin-settings-help-filters.php';
			?>
		</div>
		<div class="wf_sub_tab_content" data-id="system-info">
			<?php
				include WF_PKLIST_PLUGIN_PATH.'/admin/views/admin-settings-system-info.php';
			?>
		</div>
	</div>
</div>
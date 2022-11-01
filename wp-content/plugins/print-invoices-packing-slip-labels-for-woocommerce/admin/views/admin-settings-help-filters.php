<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
include WF_PKLIST_PLUGIN_PATH.'/admin/data/data.filters-help.php';
?>
<style type="text/css">
	table.fold-table {width: 100%;border-collapse: collapse; }
	table.fold-table  th {text-align: left;border-bottom: 1px solid #ccc;}
	table.fold-table  th,table.fold-table  td {padding: 0.4em 1.4em;}
	table.fold-table > tbody > tr.view td,table.fold-table > tbody > tr.view th {cursor: pointer;}
	table.fold-table > tbody > tr.view td.filter_actions{text-align: right;width: 50%;}
	table.fold-table > tbody > tr.view:hover {background: #f4f4f4;}
	table.fold-table > tbody > tr.view.open {border-color: #fff;}
	table.fold-table > tbody > tr.fold {display: none;}
	table.fold-table > tbody > tr.fold.open {display: table-row;}
	.filter_desc{background-color: #ececec;padding: 15px 5px;height: 95px;}
	tr.bordered{border-bottom:1px solid #c3c4c7;}
    .filter_hide{display: none;}
    .wf_filter_code_div{float: left;width: 98%;background:#ececec; padding:5px; border:solid 1px #ececec; color:#000; font-size:14px;direction: ltr;}
	.wf_filter_code_div div{ padding-left:30px; }
	.wf_filter_code_div .inbuilt_fn{color:#c81cc8;}
	.wf_filter_code_div .fn_str{color:#1111e8;}
	.wf_filter_code_div .str_css{color:#679d67;}
	.wf_filter_code_div .cmt_css{color:gray;}
	.filter_code_copy{cursor: pointer;}
</style>
<h3><?php _e('Filters','print-invoices-packing-slip-labels-for-woocommerce'); ?></h3>
<p>
	<?php _e("Some useful `filters` to extend plugin's functionality",'print-invoices-packing-slip-labels-for-woocommerce');?>
</p>
<input type="text" placeholder="<?php echo __('Search filter','print-invoices-packing-slip-labels-for-woocommerce'); ?>" id="filter_search_input_updated" style="float: left;margin: 0 0 15px 0;width: 48%;border: 1px solid #ccc;">
<table class="fold-table wp-list-table fixed" id="wf_filters_doc_table_updated" width="100%"  style="border: 1px solid #c3c4c7;">
  	<tbody>
  		<?php
		foreach($wf_filters_help_doc_cat as $cat => $cat_label){
			if(isset($wf_filters_help_doc_lists) && is_array($wf_filters_help_doc_lists) && array_key_exists($cat,$wf_filters_help_doc_lists))
			{	
				echo '<tr class="bordered filter_category">
						<td colspan="2" style="vertical-align: top;padding: 0px 20px;background-color:#ececec;"><p><b>'.$cat_label.'</b></p></td>
					</tr>';
				foreach($wf_filters_help_doc_lists[$cat] as $key => $value) 
				{
					if(isset($value['function_name'])){
				?>
					<tr class="bordered view">
						<td style="width: 40%;">
							<p>
								<b><?php 
									if(isset($value['title']) && trim($value['title'])!="")
									{
										echo $value['title']; 
									}elseif(isset($value['description']) && trim($value['description'])!="")
									{
										echo $value['description']; 
									}; 
								?></b>
							</p>
						</td>
						<td class="filter_actions">
							<span class="dashicons dashicons-arrow-down-alt2" title="<?php echo __('Example Code','print-invoices-packing-slip-labels-for-woocommerce');?>"></span>
							<span class="dashicons dashicons-arrow-up-alt2 filter_hide"></span>
						</td>
					</tr>
					<tr class="bordered fold">
						<td style="vertical-align:top;">
							<div class="filter_desc">
								<?php 
									if(isset($value['description']) && trim($value['description'])!="")
									{
										echo $value['description']; 
									}
								?>
							</div>
						</td>
						<td style="vertical-align:top;">
							<?php
							if(isset($value['function_name']) && trim($value['function_name'])!="")
							{
							?>
								<div class="wf_filter_code_div" style="position:relative;">
									<div class="filter_code_copy" style="float: left;text-align: right;position: relative;padding: 0;bottom: 0;position: absolute;right: 2px;opacity: 0.5;">
										<img src="<?php echo WF_PKLIST_PLUGIN_URL.'admin/images/copy-code.png'; ?>" title="<?php echo __('Copy Code','print-invoices-packing-slip-labels-for-woocommerce');?>" style="width: 25px;display: inline-block;background: #2b3337;border-radius: 25px;height: 25px;padding: 5px;">
									</div>
								<?php 
									$count=count(explode(" ",$value['params']));
									$str='<span class={fn_str}>'.'add_filter'.'</span>(\''.$key.'\', '.'\''.$value['function_name'].'\', 10, '.$count.');'.'<br/>';
									$str.='function '.$value['function_name'].'(<span class={prms_css}>'.$value['params'].'</span>)<br />{ <br /> <div>'.(isset($value['function_code']) ? $value['function_code'] : '').'</div> }';
									$str=wt_code_view_colors($str);
									$str=str_replace(array('{prms_css}','{inbuilt_fn}','{fn_str}','{cmt_css}','{str_css}'),array('"wf_filter_doc_params"','"inbuilt_fn"','"fn_str"','"cmt_css"','"str_css"'),$str);
									echo $str;
								?>
								</div>

								<er class="filter_code_div" style="display: none;">
									<?php
										$function_val = "";
										if(isset($value['function_code_copy'])){
											$function_val = $value['function_code_copy'];
										}elseif(isset($value['function_code'])){
											$function_val = $value['function_code'];
										}
										echo "add_filter('".$key."','".$value['function_name']."',10,".$count.");<br>";
										echo "function ".$value['function_name']."(".$value['params']."){<br>";
										echo $function_val;
										echo "}";
									?>
								</er>
							<?php 
							}
							?>
						</td>
					</tr>
				<?php
					}
				}
			}
		}
		?>
  	</tbody>
</table>
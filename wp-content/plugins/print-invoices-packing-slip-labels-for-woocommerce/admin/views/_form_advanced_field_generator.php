<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
if(is_array($args))
{	

	foreach ($args as $key => $value)
	{	
		$help_text_style = "";
		$tr_id=(isset($value['tr_id']) ? ' id="'.$value['tr_id'].'" ' : '');
		$field_group_attr=(isset($value['field_group']) ? ' data-field-group="'.$value['field_group'].'" ' : '');
		$tr_class =(isset($value['field_group']) ? ' wt_pklist_field_group_children ' : ''); //add an extra class to tr when field grouping enabled

		//echo $field_group_attr;
		//echo $tr_class;
		$type=(isset($value['type']) ? $value['type'] : 'text');
		if($type=='field_group_head') //heading for field group
		{
			$visibility=(isset($value['show_on_default']) ? $value['show_on_default'] : 0);
		?>
		<tr <?php echo $tr_id.$field_group_attr;?> class="<?php echo $tr_class;?>">
			<td colspan="3" class="wt_pklist_field_group">
				<div class="wt_pklist_field_group_hd">
					<?php echo isset($value['head']) ? $value['head'] : ''; ?>
					<div class="wt_pklist_field_group_toggle_btn" data-id="<?php echo isset($value['group_id']) ? $value['group_id'] : ''; ?>" data-visibility="<?php echo $visibility; ?>"><span class="dashicons dashicons-arrow-<?php echo ($visibility==1 ? 'down' : 'right'); ?>"></span></div>
				</div>
				<div class="wt_pklist_field_group_content" style="<?php if($visibility == 0){echo 'display: none;'; }?>">
					<table></table>
				</div>
			</td>
		</tr>
		<?php
		}else{
			$field_name=isset($value['field_name']) ? $value['field_name'] : $value['option_name'];
			$field_id=isset($value['field_id']) ? $value['field_id'] : $field_name;
	    	$option_name=$value['option_name'];
	    	$vl=Wf_Woocommerce_Packing_List::get_option($option_name,$base);
	    	$vl=is_string($vl) ? stripslashes($vl) : $vl;
			?>
			<tr valign="top" <?php echo $tr_id.$field_group_attr;?> class="<?php echo $tr_class;?>">
			    <th scope="row" >
			    	<label for="<?php echo $field_name;?>" style="float:left; width:100%;">
			    		<?php echo isset($value['label']) ? $value['label'] : ''; ?>
					</label>
				</th>
			    <td>
			    	<?php
			    	if($type=='text')
					{
			    	?>
			        	<input type="text" name="<?php echo $field_name;?>" value="<?php echo $vl;?>" />
			        <?php
			    	}elseif($type == 'pdf_name_select'){
						$default_select_fields=array(
							'[prefix][order_no]'=>__('[prefix][order_no]', 'print-invoices-packing-slip-labels-for-woocommerce'),
							'[prefix][invoice_no]'=>__('[prefix][invoice_no]', 'print-invoices-packing-slip-labels-for-woocommerce'),
						);
						$select_fields=(isset($value['select_fields']) && is_array($value['select_fields']) ? $value['select_fields'] : $default_select_fields);
						?>
						<select name="<?php echo $field_name;?>" id="<?php echo $field_id;?>" class="">
						<?php
						foreach ($select_fields as $sel_vl=>$sel_label) 
						{
							?>
								<option value="<?php echo $sel_vl;?>" <?php echo ($vl==$sel_vl) ? ' selected="selected"' : ''; ?>><?php echo $sel_label; ?></option>
							<?php
						}
						?>
						</select>
					<?php
					}elseif($type == "pdf_name_prefix"){
						$vl=Wf_Woocommerce_Packing_List::get_option($value['option_name'],$base);
		        		$vl=is_string($vl) ? stripslashes($vl) : $vl;
			        	?>
		            	<input type="text" name="<?php echo $field_name;?>" value="<?php echo $vl;?>" />
		            <?php
					}elseif($type=='checkbox') //checkbox
					{
						$field_vl=isset($value['field_vl']) ? $value['field_vl'] : "1";
					?>
						<input class="<?php //echo $form_toggler_p_class;?>" type="checkbox" value="<?php echo $field_vl;?>" id="<?php //echo $field_id;?>" name="<?php echo $field_name;?>" <?php echo ($field_vl==$vl ? ' checked="checked"' : '') ?> <?php //echo $form_toggler_register;?> <?php //echo $fld_attr;?>>
						<?php
					}elseif($type=='radio') //radio button
					{	
						$help_text_style = "";
						$radio_fields=isset($value['radio_fields']) ? $value['radio_fields'] : array();
						foreach ($radio_fields as $rad_vl=>$rad_label) 
						{
						?>
						<input type="radio" id="<?php echo $field_id.'_'.$rad_vl;?>" name="<?php echo $field_name;?>" class="" value="<?php echo $rad_vl;?>" <?php echo ($vl==$rad_vl) ? ' checked="checked"' : ''; ?> /> <?php echo $rad_label; ?>
						&nbsp;&nbsp;
						<?php
						}
						
					}

					
		        	if(isset($value['help_text']))
					{
		            ?>
			            <span class="wf_form_help" style="<?php echo $help_text_style; ?>"><?php echo $value['help_text']; ?></span>
			            <?php
			            	unset($value['help_text']);
		        	}	
			    	?>
			    </td>
			    <td></td>
			<?php
		}
	}
}
?>
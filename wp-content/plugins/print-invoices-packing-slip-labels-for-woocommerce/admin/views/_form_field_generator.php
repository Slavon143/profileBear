<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

if(is_array($args))
{
	foreach ($args as $key => $value)
	{	
		$module_base=isset($value['module_base']) ? $value['module_base'] : '';
		$type=(isset($value['type']) ? $value['type'] : 'text');
		$field_name=isset($value['field_name']) ? $value['field_name'] : $value['option_name'];
		$field_id=isset($value['field_id']) ? $value['field_id'] : $field_name;
		$field_class = isset($value['field_class']) ? $value['field_class'] : "";
		$field_placeholder = isset($value['field_placeholder']) ? $value['field_placeholder'] : "";
		
		$after_form_field=(isset($value['after_form_field']) ? $value['after_form_field'] : ''); /* after form field */
		$before_form_field=(isset($value['before_form_field']) ? $value['before_form_field'] : '');

		/** 
		*	conditional help texts 
		*	!!Important: Using OR mixed with AND then add OR conditions first.
		*/
		$conditional_help_html='';
		if(isset($value['help_text_conditional']) && is_array($value['help_text_conditional']))
		{		
			foreach ($value['help_text_conditional'] as $help_text_config)
			{
				if(is_array($help_text_config))
				{
					$condition_attr='';
					if(is_array($help_text_config['condition']))
					{
						$previous_type=''; /* this for avoiding fields without glue */
						foreach ($help_text_config['condition'] as $condition)
						{
							if(is_array($condition))
							{
								if($previous_type!='field')
								{
									$condition_attr.='['.$condition['field'].'='.$condition['value'].']';
									$previous_type='field';
								}
							}else
							{
								if(is_string($condition))
								{
									$condition=strtoupper($condition);
									if(($condition=='AND' || $condition=='OR') && $previous_type!='glue')
									{
										$condition_attr.='['.$condition.']';
										$previous_type='glue';
									}
								}
							}
						}
					}			
					$conditional_help_html.='<span class="wf_form_help wt_pklist_conditional_help_text" data-wt_pklist-help-condition="'.esc_attr($condition_attr).'">'.$help_text_config['help_text'].'</span>';
				}	
			}
		}


		$form_toggler_p_class="";
		$form_toggler_register="";
		$form_toggler_child="";
		if(isset($value['form_toggler']))
		{
			if($value['form_toggler']['type']=='parent')
			{
				$form_toggler_p_class="wf_form_toggle";
				$form_toggler_register=' wf_frm_tgl-target="'.$value['form_toggler']['target'].'"';
			}
			elseif($value['form_toggler']['type']=='child')
			{
				$form_toggler_child=' wf_frm_tgl-id="'.$value['form_toggler']['id'].'" wf_frm_tgl-val="'.$value['form_toggler']['val'].'" '.(isset($value['form_toggler']['chk']) ? 'wf_frm_tgl-chk="'.$value['form_toggler']['chk'].'"' : '').(isset($value['form_toggler']['lvl']) ? ' wf_frm_tgl-lvl="'.$value['form_toggler']['lvl'].'"' : '');	
			}else
			{
				$form_toggler_child=' wf_frm_tgl-id="'.$value['form_toggler']['id'].'" wf_frm_tgl-val="'.$value['form_toggler']['val'].'" '.(isset($value['form_toggler']['chk']) ? 'wf_frm_tgl-chk="'.$value['form_toggler']['chk'].'"' : '').(isset($value['form_toggler']['lvl']) ? ' wf_frm_tgl-lvl="'.$value['form_toggler']['lvl'].'"' : '');	
				$form_toggler_p_class="wf_form_toggle";
				$form_toggler_register=' wf_frm_tgl-target="'.$value['form_toggler']['target'].'"';				
			}
			
		}
		$fld_attr=(isset($value['attr']) ? $value['attr'] : '');
		$field_only=(isset($value['field_only']) ? $value['field_only'] : false);
		$mandatory=(boolean) (isset($value['mandatory']) ? $value['mandatory'] : false);
		if($mandatory)
		{
			$fld_attr.=' required="required"';	
		}
		if($field_only===false)
		{
			$tooltip_html=self::set_tooltip($field_name,$base);
			if($type === "field_group_sub_head"){
				?>
				<tr>
					<td colspan="3" class="wt_pklist_field_group">
						<div class="wt_pklist_field_group_hd_sub">
							<?php echo isset($value['head']) ? $value['head'] : ''; ?>
						</div>
					</td>
				</tr>
				<?php
			}elseif($type === "field_group_hr_line"){
				?>
				<tr>
					<td colspan="3" class="wt_pklist_field_group" style="border-bottom: dashed 1px #ccc;">
					</td>
				</tr>
				<?php
			}else{
		?>
			<tr valign="top" <?php echo $form_toggler_child; ?>>
			    <th scope="row" >
			    	<label for="<?php echo $field_name;?>" style="float:left; width:100%;">
			    		<?php echo isset($value['label']) ? $value['label'] : ''; ?><?php echo ($mandatory ? '<span class="wt_pklist_required_field">*</span>' : ''); ?><?php echo $tooltip_html;?>
					</label>
				</th>
			    <td style="<?php if($field_id == "woocommerce_wf_generate_for_taxstatus"){echo 'width:55%;'; }?>">
			    	<?php
			    	}
			    	$option_name=$value['option_name'];
			    	$vl=Wf_Woocommerce_Packing_List::get_option($option_name,$base);
			    	$vl=is_string($vl) ? stripslashes($vl) : $vl;

			    	echo $before_form_field;
			    	if($type=='text')
					{
			    	?>
			        	<input type="text" <?php echo $fld_attr;?> name="<?php echo esc_attr($field_name);?>" value="<?php echo esc_attr($vl);?>" class="<?php echo esc_attr($field_class); ?>" />
			        <?php
			    	}
			    	if($type=='number')
					{
					?>
			        	<input type="number" <?php echo $fld_attr;?> name="<?php echo esc_attr($field_name);?>" value="<?php echo esc_attr($vl);?>" class="<?php echo esc_attr($field_class); ?>"/>
			        <?php
					}
			    	elseif($type=='textarea')
					{
					?>
			       		<textarea <?php echo $fld_attr;?> name="<?php echo esc_attr($field_name);?>" class="<?php echo esc_attr($field_class); ?>" placeholder="<?php echo esc_attr($field_placeholder); ?>" id="<?php echo esc_attr($field_id); ?>"><?php echo $vl;?></textarea>
			        <?php
					}elseif($type=='order_st_multiselect') //order status multi select
					{
						$order_statuses=isset($value['order_statuses']) ? $value['order_statuses'] : array();
						$field_vl=isset($value['field_vl']) ? $value['field_vl'] : array();
					?>
						<input type="hidden" name="<?php echo $field_name;?>_hidden" value="1" />
						<select class="wc-enhanced-select" id='<?php echo $field_name;?>_st' data-placeholder='<?php _e('Choose Order Status','print-invoices-packing-slip-labels-for-woocommerce');?>' name="<?php echo $field_name;?>[]" multiple="multiple" <?php echo $fld_attr;?>>
				            <?php
				            $Pdf_invoice=$vl ? $vl : array();
				            foreach($field_vl as $inv_key => $inv_value) 
				            {
				    			echo "<option value=$inv_value".(in_array($inv_value, $Pdf_invoice) ? ' selected="selected"' : '').">$order_statuses[$inv_value]</option>";
				                
				            }
				            ?>
				        </select>
					<?php
					}elseif($type=='checkbox') //checkbox
					{
						$field_vl=isset($value['field_vl']) ? $value['field_vl'] : "1";
					?>
						<input class="<?php echo $form_toggler_p_class;?>" type="checkbox" value="<?php echo $field_vl;?>" id="<?php echo $field_id;?>" name="<?php echo $field_name;?>" <?php echo ($field_vl==$vl ? ' checked="checked"' : '') ?> <?php echo $form_toggler_register;?> <?php echo $fld_attr;?>>
						<?php
					}elseif($type=='print_button_checkbox') //checkbox
					{
						$module_id = Wf_Woocommerce_Packing_List::get_module_id($module_base);
						$show_on_frontend = Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_frontend_info',$module_id);
						$checkbox_fields = isset($value['checkbox_fields']) ? $value['checkbox_fields'] : array('order_listing','order_details','order_email');
						if(!empty($checkbox_fields)){
							foreach($checkbox_fields as $checkbox_key => $checkbox_label){
								$checked = '';
								if(in_array($checkbox_key,$vl) && $show_on_frontend === "Yes"){
									$checked = 'checked="checked"';
								}
								?>
						<input type="checkbox" name="wf_woocommerce_invoice_show_print_button[]" id="wf_woocommerce_invoice_show_print_button_<?php echo $checkbox_key; ?>" value="<?php echo esc_attr($checkbox_key); ?>" <?php echo $checked; ?>> <?php echo esc_html($checkbox_label); ?>
						<br>
						<br>
								<?php
							}
						}
					?>
						
						<?php
					}
					elseif($type=='radio') //radio button
					{
						$radio_fields=isset($value['radio_fields']) ? $value['radio_fields'] : array();
						$radio_fields_count = count($radio_fields);
						$i = 1;
						foreach ($radio_fields as $rad_vl=>$rad_label) 
						{
							$checked_attr='';
							if((is_array($vl) && in_array($rad_vl, $vl)) || (is_string($vl) && $vl==$rad_vl))
							{
								$checked_attr=' checked="checked"';
							}
						?>
						<input type="radio" id="<?php echo esc_attr($field_id.'_'.$rad_vl);?>" name="<?php echo esc_attr($field_name);?>" class="<?php echo esc_attr($form_toggler_p_class);?> <?php echo esc_attr($field_class); ?>" <?php echo $form_toggler_register;?> value="<?php echo esc_attr($rad_vl);?>" <?php echo $checked_attr; ?> <?php echo $fld_attr;?> /> <?php echo esc_html($rad_label); ?>
						&nbsp;&nbsp;
						<?php
							$option_alignment = isset($value['option_alignment']) ? $value['option_alignment'] : 'horizontal';
							if(("vertical" === $option_alignment) && ($i != $radio_fields_count)){
								echo "<br><br>";
							}
							$i++;
						}
						
					}elseif($type=='uploader') //uploader
					{
						?>
						<div class="wf_file_attacher_dv">
				            <input id="<?php echo $field_id; ?>"  type="text" name="<?php echo $field_name; ?>" value="<?php echo $vl; ?>" <?php echo $fld_attr;?>/>
							
							<input type="button" name="upload_image" class="wf_button button button-primary wf_file_attacher" wf_file_attacher_target="#<?php echo $field_name; ?>" value="<?php _e('Upload','print-invoices-packing-slip-labels-for-woocommerce'); ?>" />
						</div>
						<img class="wf_image_preview_small" src="<?php echo $vl ? $vl : Wf_Woocommerce_Packing_List::$no_image; ?>" />
						<?php
					}elseif($type=='select') //select
					{
						$select_fields=isset($value['select_fields']) ? $value['select_fields'] : array();
						?>
						<select name="<?php echo esc_attr($field_name);?>" id="<?php echo esc_attr($field_id);?>" class="<?php echo esc_attr($form_toggler_p_class);?> <?php echo esc_attr($field_class); ?>" <?php echo $form_toggler_register;?> <?php echo $fld_attr;?>>
						<?php
						foreach ($select_fields as $sel_vl=>$sel_label) 
						{
							$selected_attr='';
							if((is_array($vl) && in_array($sel_vl, $vl)) || (is_string($vl) && $vl==$sel_vl))
							{
								$selected_attr=' selected="selected"';
							}
						?>
							<option value="<?php echo esc_attr($sel_vl);?>" <?php echo $selected_attr; ?>><?php echo esc_html($sel_label); ?></option>
						<?php
						}
						?>
						</select>
						<?php
					}
					elseif($type=='additional_fields') //additional fields (Order meta)
					{
						
						$fields=array();
			            $add_data_flds=Wf_Woocommerce_Packing_List::$default_additional_data_fields; 
			            $user_created=Wf_Woocommerce_Packing_List::get_option('wf_additional_data_fields');		            
			            
			            if(is_array($user_created))  //user created
			            {
			                $fields=array_merge($add_data_flds,$user_created);
			            }else
			            {
			                $fields=$add_data_flds; //default
			            }
			            
		            	$user_selected_arr=$vl && is_array($vl) ? $vl : array();

		            	$vat_fields = array('vat','vat_number','eu_vat_number');
		            	$temp = array();
		            	foreach($user_selected_arr as $u_val){
		            		if(in_array($u_val,$vat_fields)){
		            			if(!in_array('vat',$temp)){
		            				$temp[] = 'vat';
		            			}
		            		}else{
		            			$temp[] = $u_val;
		            		}
		            	}
		            	$user_selected_arr = $temp;

		            	$d_temp = array();
		            	foreach($fields as $d_key => $d_val){
		            		if(in_array($d_key,$vat_fields)){
		            			if(!array_key_exists('vat',$d_temp)){
		            				$d_temp[$d_key] = 'VAT';
		            			}
		            		}else{
		            			$d_temp[$d_key] = $d_val;
		            		}
		            	}
		            	$fields = $d_temp;
						?>
						<div class="wf_select_multi">
							<input type="hidden" name="wf_<?php echo $module_base;?>_contactno_email_hidden" value="1" />
				            <select class="wc-enhanced-select" name="wf_<?php echo $module_base;?>_contactno_email[]" multiple="multiple">
				            <?php
				            
				            foreach ($fields as $id => $name) 
				            { 
				                $meta_key_display=Wf_Woocommerce_Packing_List::get_display_key($id);
				                ?>
				                <option value="<?php echo $id;?>" <?php echo in_array($id, $user_selected_arr) ? 'selected' : '';?>><?php echo $name.$meta_key_display;?></option>
				                <?php
				            }
				            ?>						 
				            </select>
				            <br>
			            	<button type="button" class="button button-secondary" data-wf_popover="1" data-title="<?php _e('Order Meta','print-invoices-packing-slip-labels-for-woocommerce'); ?>" data-module-base="<?php echo $module_base;?>" data-content-container=".wt_pklist_custom_field_form" data-field-type="order_meta" style="margin-top:5px; margin-left:5px; float: right;">
			                <?php _e('Add/Edit Order Meta Field','print-invoices-packing-slip-labels-for-woocommerce'); ?>                       
			             	</button>
				            <br>
				            <?php
				        	if(isset($value['help_text']))
							{
				            ?>
				            <span class="wf_form_help" style="display:inline;"><?php echo $value['help_text']; ?></span>
				            <?php
				            	unset($value['help_text']);
				        	}
				        	?>
				        </div>
						<?php
						include WF_PKLIST_PLUGIN_PATH."admin/views/_custom_field_editor_form.php";
					}
					elseif($type=='multi_select')
					{
						$sele_vals=(isset($value['sele_vals']) && is_array($value['sele_vals']) ? $value['sele_vals'] : array());
						$vl=(is_array($vl) ? $vl : array($vl));
						$vl=array_filter($vl);
						?>
						<div class="wf_select_multi">
							<input type="hidden" name="<?php echo $field_name;?>_hidden" value="1" />
							<select multiple="multiple" name="<?php echo $field_name;?>[]" id="<?php echo $field_id;?>" class="wc-enhanced-select  <?php echo $form_toggler_p_class;?>" <?php echo $form_toggler_register;?> <?php echo $fld_attr;?>>
								<?php
								foreach($sele_vals as $sele_val=>$sele_lbl) 
								{
								?>
		                      		<option value="<?php echo $sele_val;?>" <?php echo (in_array($sele_val, $vl) ? 'selected' : ''); ?>> <?php echo $sele_lbl;?> </option>
		                   		<?php
		                    	}
		                   		?>
	                   		</select>
	                   	</div>
	                   	<?php
					}
					echo $after_form_field;

					if(isset($value['help_text']))
					{
			        ?>
			        	<span class="wf_form_help"><?php echo $value['help_text']; ?></span>
			        <?php
			    	}
			    	echo $conditional_help_html;

			    	if($field_only===false)
					{
			    	?>
			    </td>
			    <td></td>
			</tr>
		<?php
			}
		}
	}
}
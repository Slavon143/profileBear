<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<style type="text/css">
.wf_loader_bg{background:rgba(255,255,255,.5) url(<?php echo WF_PKLIST_PLUGIN_URL;?>assets/images/loading.gif) center no-repeat;}
.wf_cst_loader{ box-sizing:border-box; position:absolute; z-index:1000; width:inherit; height:800px; left:0px; display:none; }
.wf_cst_warn_box{padding:20px; padding-bottom:0px;}
.wf_cst_warn{ display:inline-block; width:100%; box-sizing:border-box; padding:10px; background-color:#fff8e5; border-left:solid 2px #ffb900; color:#333; }
.wf_new_template_wrn_sub{ display:none; }
.wf_pklist_save_theme_sub_loading{ display:none; }
.wf_missing_wrn{display:inline-block; text-decoration:none; text-align:center; font-size:12px; font-weight:normal; width:100%; margin:0%; box-sizing:border-box; padding:4px; background-color:#fff8e5; border:dashed 1px #ffb900; color:#333; }
.wf_missing_wrn:hover{ color:#333; }
.wf_customize_sidebar{float:right; width:28%;}
.wf_customize_sidebartop{float:right; width:28%;}
.wf_side_panel *{box-sizing:border-box;}
.wf_side_panel{ float:left; width:100%; box-sizing:border-box; min-height:40px; padding-right:0px; margin-bottom:10px; box-shadow:0 1px 1px rgba(0,0,0,.04); }
.wf_side_panel_toggle{ float:right; width:40px; text-align:right;}
.wf_side_panel_hd{float:left; width:100%; height:auto; padding:5px 15px; background:#fafafa; border:solid 1px #e5e5e5; color:#2b3035; min-height:40px; line-height:30px; font-weight:500; cursor:pointer; }
.wf_side_panel_content{float:left; width:100%; padding:15px; height:auto; border:solid 1px #e5e5e5; margin-top:-1px; display:none; background:#fdfdfd;}
.wf_side_panel_info_text{ float:left; width:100%; font-style:italic; }
.wf_side_panel_frmgrp{ float:left; width:100%; }
.wf_side_panel_frmgrp label{ float:left; width:100%; margin-bottom:1px; margin-top:8px; }
.wf_side_panel_frmgrp .wf-checkbox{ margin-top:8px; }
.wf_side_panel_frmgrp .wf_sidepanel_sele, .wf_side_panel_frmgrp .wf_sidepanel_txt, .wf_side_panel_frmgrp .wf_sidepanel_txtarea, .wf_pklist_text_field{ display: block;
width: 100%;
font-size:.85rem;
line-height:1.2;
color: #495057;
background-color: #fff;
background-clip: padding-box;
border:1px solid #ced4da;
min-height:32px; border-radius:5px;}
.wf_side_panel_frmgrp .wf_sidepanel_sele{ height:32px; } /* google chrome min height issue */
.wf_inptgrp{ float:left; width:100%; margin-top:0px; }
.wf_inptgrp input[type="text"]{ float:left; width:75%; border-top-right-radius:0; border-bottom-right-radius:0; }
.wf_inptgrp .addonblock{ float:left; border:1px solid #ced4da; width:25%; border-radius:5px; border-top-left-radius:0; border-bottom-left-radius:0; background-color:#e9ecef; color:#4c535a; text-align:center; height:32px; line-height:28px; margin-left:-2px; margin-top:0px;}
.wf_inptgrp .addonblock input[type="text"]{ display:inline; text-align:center; box-shadow:none; background:none; outline:none; height:28px; border:none; width:90%; }
.wf_inptgrp .addonblock input[type="text"]:focus{outline:none; box-shadow:none;}
.iris-picker, .iris-picker *{ box-sizing:content-box; }
.wp-picker-input-wrap label{ width:auto; margin-top:0px; }

.wf_cst_headbar{float:left; height:70px; width:100%; border-bottom:solid 1px #efefef; margin-left:-15px; padding-right:30px; margin-bottom:15px; margin-top:-14px; box-shadow:0px 2px 3px #efefef;}
.wf_cst_theme_name{float:left; padding-left:15px; margin:0px; margin-top:15px; margin-bottom:2px;}
.wf_customizer_tabhead_main{float:left; width:70%;}
.wf_customizer_tabhead_inner{float:right; position:relative; z-index:1;}
.wf_cst_tabhead{float:left; padding:8px 12px; border:solid 1px #e5e5e5; border-bottom:none; cursor:pointer;}
.wf_cst_tabhead_vis{background:#f5f5f5; margin-right:5px;}
.wf_cst_tabhead_code{background:#ebebeb; margin-right:-2px;}

.wf_customizer_main{float:left; width:100%; padding-top:20px;}
.wf_customize_container_main{float:left; width:70%; background:#f5f5f5; border:solid 1px #e5e5e5; margin-top:-1px;}
.wf_customize_container{width:95%; box-sizing:border-box; padding:0%; min-height:500px; margin-left:2.5%; margin-top:2.5%; margin-bottom:2.5%; background-color:#fff; float:left; height:auto;}
.wf_customize_container *{box-sizing:border-box;}
.wf_customize_vis_container{ float:left; width:100%; box-sizing:border-box; padding:2%; min-height:500px;}
.wf_customize_code_container{ float:left; width:100%; min-height:500px; display:none; }


.wf_dropdown{ position:absolute; z-index:100; background:#fff; border:solid 1px #eee; padding:0px; display:none; }
.wf_dropdown li{ padding:10px 10px; margin-bottom:0px; cursor:pointer; }
.wf_dropdown li:hover{ background:#fafafa; }

.wf_default_template_list{width:100%; max-width:650px;}
.wf_default_template_list_item{ display:inline-block; width:130px; height:200px; margin:15px; padding:5px; cursor:pointer;}
.wf_default_template_list_item img{width:100%; max-height:200px; box-shadow:0px 2px 2px #ccc; border:solid 1px #efefef;}
.wf_default_template_list_item a:focus{ box-shadow:none; }
.wf_default_template_list_item_hd{ width:100%; display:inline-block; padding:10px 0px; text-align:center; font-weight:bold; }
.wf_default_template_list_btn_main{ width:100%; display:inline-block; padding:5px 0px; text-align:center; }

.wf_template_name{width:100%; max-width:300px;}
.wf_template_name_box{ float:left; width:90%; padding:5%; }
.wf_template_name_wrn{display:none; }

.wf_my_template{width:100%; max-width:450px;}
.wf_my_template_main{float:left; width:90%; margin:5%; max-height:350px; overflow:auto;}
.wf_my_template_list{float:left; width:100%; height:auto; min-height:100px;}
.wf_my_template_item{float:left; box-sizing:border-box; width:100%; height:auto; padding:8px 10px; border-bottom:solid 1px #efefef; border-top:solid 1px #fff; text-align:left; }
.wf_my_template_item_btn{ float:right; }
.wf_my_template_item_name{ float:left; max-width:60%; height:auto; line-height:28px; }
.wf_codeview_link_btn{float:left; margin-top:7px; cursor:pointer;}

/* styles inside template */
.wfte_hidden{ display:none !important; }
.wfte_text_right{text-align:right !important; }
.wfte_text_left{text-align:left !important; }
.wfte_text_center{text-align:center !important; }
</style>
<?php
	if($template_type == "invoice"){?>
		<style type="text/css">
			.wf_customize_sidebar{
			    height: 800px;
				overflow: scroll;
			}
		</style>
<?php
	}
?>
<div class="wf_cst_loader wf_loader_bg"></div>


<div class="wf_my_template wf_pklist_popup">
	<div class="wf_pklist_popup_hd">
		<span style="line-height:40px;" class="dashicons dashicons-list-view"></span> <?php _e('Templates','print-invoices-packing-slip-labels-for-woocommerce');?>
		<div class="wf_pklist_popup_close">X</div>
	</div>
	<div class="wf_my_template_main wf_pklist_popup_body">
		<div style="float:left; box-sizing:border-box; width:100%; padding:0px 5px; margin-bottom:5px;">
			<input placeholder="<?php _e('Type template name to search','print-invoices-packing-slip-labels-for-woocommerce');?>" type="text" name="" class="wf_pklist_text_field wf_my_template_search">
		</div>
		<div class="wf_my_template_list">		
			
		</div>
	</div>
</div>

<div class="wf_template_name wf_pklist_popup">
	<div class="wf_pklist_popup_hd">
		<span style="line-height:40px;" class="dashicons dashicons-edit"></span>  <?php _e('Enter a name for your template','print-invoices-packing-slip-labels-for-woocommerce');?>
		<div class="wf_pklist_popup_close">X</div>
	</div>
	<div class="wf_cst_warn_box">
		<div class="wf_cst_warn wf_template_name_wrn">
			<?php _e('Please enter name','print-invoices-packing-slip-labels-for-woocommerce');?> 
		</div>
	</div>
	<div class="wf_template_name_box">
		<input type="text" name="" class="wf_pklist_text_field wf_template_name_field">
		<div class="wf_pklist_popup_footer">
			<button type="button" name="" class="button-secondary wf_pklist_popup_cancel">
				<?php _e('Cancel','print-invoices-packing-slip-labels-for-woocommerce');?> 
			</button>
			<button type="button" name="" class="button-primary wf_template_create_btn">
				<?php _e('Save','print-invoices-packing-slip-labels-for-woocommerce');?> 
			</button>	
		</div>
	</div>
</div>

<div class="wf_default_template_list wf_pklist_popup">
	<div class="wf_default_template_list_hd wf_pklist_popup_hd">
		<span style="line-height:40px;" class="dashicons dashicons-admin-appearance"></span> <?php _e('Choose a layout.','print-invoices-packing-slip-labels-for-woocommerce');?>
		<div class="wf_pklist_popup_close">X</div>
	</div>
	<div class="wf_default_template_list_main wf_pklist_popup_body">
		<div class="wf_cst_warn_box">
			<div class="wf_cst_warn" style="line-height:26px;">
				<?php _e('All unsaved changes will be lost upon switching to a new layout.','print-invoices-packing-slip-labels-for-woocommerce');?>
				<br />
				<span class="wf_new_template_wrn_sub"><?php _e('Save before you proceed.','print-invoices-packing-slip-labels-for-woocommerce');?> 
				<span class="wf_pklist_save_theme_sub_loading"><?php _e('Saving...','print-invoices-packing-slip-labels-for-woocommerce');?></span>
				<button class="button button-secondary wf_pklist_save_theme_sub"><?php _e('Save','print-invoices-packing-slip-labels-for-woocommerce');?></button> </span>
			</div>
		</div>
		<?php
		$def_template_id=0;
		foreach($def_template_arr as $def_template)
		{
			?>
			<div class="wf_default_template_list_item" data-id="<?php echo $def_template_id;?>">
				<span class="wf_default_template_list_item_hd"><?php echo $def_template['title'];?></span>
					<?php
					if(isset($def_template['preview_img']) && $def_template['preview_img']!="")
					{
						?>
						<img src="<?php echo $def_template_url.$def_template['preview_img'];?>">	
						<?php
					}elseif(isset($def_template['preview_html']) && $def_template['preview_html']!="")
					{
						echo $def_template['preview_html'];
					}
					?>		
					<span class="wf_default_template_list_btn_main">
						<!--
						<button type="button" name="" class="button-primary">
							<?php _e('Use','print-invoices-packing-slip-labels-for-woocommerce');?>
						</button>
						-->
					</span>				
			</div>
			<?php
			$def_template_id++;
		}
		?>
	</div>
</div>

<div class="wf_cst_headbar">
	<div style="float:left;">
		<h3 class="wf_cst_theme_name"><?php echo $active_template_name;?></h3>
		<?php
		$tooltip_conf=Wf_Woocommerce_Packing_List_Admin::get_tooltip_configs('create_new_template',Wf_Woocommerce_Packing_List_Customizer::$module_id_static);
		?>
		<a class="wf_pklist_new_template <?php echo $tooltip_conf['class']; ?>" style="float:left; width:100%; padding-left:15px; cursor:pointer;" <?php echo $tooltip_conf['text']; ?>><?php _e('Create new template','print-invoices-packing-slip-labels-for-woocommerce');?></a>
	</div> 	
	<div style="float:right; margin-top:22px; margin-right:-15px;">			
		<button type="button" name="" class="button-primary wf_pklist_save_theme" style="height: 28px;margin-right: 5px;">
				<span class="dashicons dashicons-yes" style="line-height: 28px;"></span><?php _e('Save','print-invoices-packing-slip-labels-for-woocommerce');?>
		</button>
		<button type="button" name="" class="button-secondary" style="margin-right: 5px;" onclick="window.location.reload(true);">
		<span class="dashicons dashicons-no-alt" style="line-height: 28px;"></span> 
		<?php _e('Cancel','print-invoices-packing-slip-labels-for-woocommerce');?></button>
		
		<?php
		$tooltip_conf=Wf_Woocommerce_Packing_List_Admin::get_tooltip_configs('dropdown_menu',Wf_Woocommerce_Packing_List_Customizer::$module_id_static);
		?>
		<button type="button" name="" class="button-secondary wf_customizer_drp_menu <?php echo $tooltip_conf['class']; ?>" style="height: 28px;" <?php echo $tooltip_conf['text']; ?>>
				<span class="dashicons dashicons-menu" style="line-height: 28px;"></span>
		</button>
		<ul class="wf_dropdown" data-target="wf_customizer_drp_menu">
			<li class="wf_activate_theme wf_activate_theme_current" data-id="<?php echo $active_template_id;?>"><?php _e('Activate','print-invoices-packing-slip-labels-for-woocommerce');?></li>
			<li class="wf_delete_theme wf_delete_theme_current" data-id="<?php echo $active_template_id;?>"><?php _e('Delete','print-invoices-packing-slip-labels-for-woocommerce');?></li>
			<li class="wf_pklist_new_template"><?php _e('Create new','print-invoices-packing-slip-labels-for-woocommerce');?></li>
			<li class="wf_pklist_my_templates"><?php _e('My templates','print-invoices-packing-slip-labels-for-woocommerce');?></li>
		</ul>
	</div>
</div>
<div class="wf_customizer_main">
	<p class="template_qr_compatible_err" style="margin: 0 0 10px 0px;width: 68%;background: #ddd;padding: 10px;border-left: 5px solid red;font-size: 14px;display: none;"><?php echo __("This template is not comaptible with QR Code addon plugin","print-invoices-packing-slip-labels-for-woocommerce"); ?></p>
	<?php
		$show_qrcode_placeholder = apply_filters('wt_pklist_show_qrcode_placeholder_in_template',false,$template_type);
		$template_qr_compatible_err_val = 0;
		if(!$show_qrcode_placeholder){
			$template_qr_compatible_err_val = 2;
		}
	?>
	<input type="hidden" name="" id="template_qr_compatible_err_val" value="<?php echo $template_qr_compatible_err_val; ?>">
	<?php
	if($enable_code_view)
	{
	?>
	<div class="wf_customizer_tabhead_main">
		<div class="wf_customizer_tabhead_inner">
			<div class="wf_cst_tabhead_vis wf_cst_tabhead" data-target="wf_customize_vis_container"><?php _e('Visual','print-invoices-packing-slip-labels-for-woocommerce');?></div>
			<div class="wf_cst_tabhead_code wf_cst_tabhead" data-target="wf_customize_code_container"><?php _e('Code','print-invoices-packing-slip-labels-for-woocommerce');?></div>
		</div>
	</div>
	<?php
	}else
	{
		$tooltip_conf=Wf_Woocommerce_Packing_List_Admin::get_tooltip_configs('design_view',Wf_Woocommerce_Packing_List_Customizer::$module_id_static);
	?>
	<!--dummy code view for basic version -->
	<div class="wf_customizer_tabhead_main">
		<div class="wf_customizer_tabhead_inner">
			<div class="wf_cst_tabhead_vis wf_cst_tabhead <?php echo $tooltip_conf['class']; ?>" data-target="wf_customize_vis_container" <?php echo $tooltip_conf['text']; ?>><?php _e('Visual','print-invoices-packing-slip-labels-for-woocommerce');?></div>
			<div class="wf_cst_tabhead_code wf_cst_tabhead" data-target="wf_customize_vis_container" style="opacity:.5; cursor:not-allowed;"><?php _e('Code','print-invoices-packing-slip-labels-for-woocommerce');?> <span style="color:red;">(<?php _e('Pro version','print-invoices-packing-slip-labels-for-woocommerce');?>)</span></div>
		</div>
	</div>
	<?php
	}
	?>

	<div class="wf_customize_sidebartop">
		<?php
		do_action('wf_pklist_customizer_editor_sidebar_top',$template_type);

		$enable_pdf_preview=apply_filters('wf_pklist_intl_customizer_enable_pdf_preview', false, $template_type);
		if($enable_pdf_preview)
		{
			include "_pdf_preview.php";
		}
		?>
	</div>


	<div class="wf_customize_container_main">
		<div class="wf_customize_container">
			<div class="wf_customize_vis_container wf_customize_inner"></div>
			<div class="wf_customize_code_container wf_customize_inner">
			  <textarea id="wfte_code"></textarea>
			</div>
		</div>
	</div>

	<div class="wf_customize_sidebar">
		<?php
		include "_customize_properties.php";
		?>
	</div>
</div>

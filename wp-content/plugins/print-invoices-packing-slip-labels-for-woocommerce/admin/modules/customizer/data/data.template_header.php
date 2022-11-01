<?php
if (!defined('ABSPATH')){
    exit;
}
$the_options=!isset($the_options) ? Wf_Woocommerce_Packing_List::get_settings() : $the_options;
$print_preview=isset($the_options['woocommerce_wf_packinglist_preview']) ? $the_options['woocommerce_wf_packinglist_preview'] : 'disabled';
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo (isset($page_title) && $page_title!="" ? $page_title : WF_PKLIST_PLUGIN_DESCRIPTION); ?></title>
		<style>
		body, html{margin:0px; padding:0px; }
		.clearfix::after {
		    display: block;
		    clear: both;
		  content: "";}
		.wfte_hidden{ display:none !important; }
		.wfte_text_right{text-align:right !important; }
		.wfte_text_left{text-align:start !important; }
		.wfte_text_center{text-align:center !important; }
		.pagebreak {
			page-break-after: always;
		}
		.no-page-break {
			page-break-after: avoid;
		}
		</style>
		<style id="template_font_style">
			<?php
		if(isset($template_for_pdf) && $template_for_pdf===true)
		{
			?>
			*{font-family:"DeJaVu Sans", monospace;}
			<?php
		}?>
		</style>
		<style>
		<?php
		echo (isset($custom_css) ? $custom_css : '');
		?>
	</style>
		<style>
		@media print {
		  body{ -webkit-print-color-adjust:exact; color-adjust:exact;}
		  #Header, #Footer { display:none !important; }
		  @page { size:auto;  margin:0;  }
		   body,html{ margin:0; background-color:#FFFFFF; }
		   table.wfte_product_table tr, table.wfte_product_table tr td, table.wfte_payment_summary_table tr, table.wfte_payment_summary_table tr td{
		        page-break-inside: avoid;
		   }
		}
		.wfte_received_seal{ page-break-inside:avoid; }
		</style>
		<script>
		function wf_document_options(print_preview)
		{
			if(print_preview=='enabled')
			{
				window.print();
			}
		}
		</script>
   </head>
<body onload="wf_document_options('<?php echo $print_preview;?>')">
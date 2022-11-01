<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

$no_icon='<span class="dashicons dashicons-dismiss" style="color:#ea1515;"></span>&nbsp;';
$yes_icon='<span class="dashicons dashicons-yes-alt" style="color:#18c01d;"></span>&nbsp;';

global $wp_version;
if(version_compare($wp_version, '5.2.0')<0)
{
 	$yes_icon='<img src="'.plugin_dir_url(dirname(__FILE__)).'assets/images/tick_icon_green.png" style="float:left;" />&nbsp;';
}

$supported_docs_arr=array(
		__('Invoice', 'print-invoices-packing-slip-labels-for-woocommerce'),
		__('Packing slip', 'print-invoices-packing-slip-labels-for-woocommerce'),
		__('Shipping label', 'print-invoices-packing-slip-labels-for-woocommerce'),
		__('Delivery note', 'print-invoices-packing-slip-labels-for-woocommerce'),
		__('Dispatch label', 'print-invoices-packing-slip-labels-for-woocommerce'),
	);
$pro_only_docs_arr=array(
		__('Address label', 'print-invoices-packing-slip-labels-for-woocommerce'),
		__('Picklist', 'print-invoices-packing-slip-labels-for-woocommerce'),
		__('Proforma invoice', 'print-invoices-packing-slip-labels-for-woocommerce'),
		__('Credit note', 'print-invoices-packing-slip-labels-for-woocommerce')
	);

$basic_supported_docs=$yes_icon.implode("<br />$yes_icon", $supported_docs_arr)."<br />".$no_icon.implode("<br />$no_icon", $pro_only_docs_arr);
$pro_supported_docs=$yes_icon.implode("<br />$yes_icon", array_merge($supported_docs_arr, $pro_only_docs_arr));


/**
*	Array format
*	First 	: Feature
*	Second 	: Basic availability. Supports: Boolean, Array(Boolean and String values), String
*	Pro 	: Pro availability. Supports: Boolean, Array(Boolean and String values), String
*/
$comparison_data=array(
	array(
		__('Supported Documents', 'print-invoices-packing-slip-labels-for-woocommerce'),
		$basic_supported_docs,
		$pro_supported_docs,
	),
	array(
		__('Automatically Attach PDF Invoice with order email', 'print-invoices-packing-slip-labels-for-woocommerce'),
		true,
		true,
	),
	array(
		__('Customizer', 'print-invoices-packing-slip-labels-for-woocommerce'),
		__('Minimal customizer available for Invoice and Shipping label only', 'print-invoices-packing-slip-labels-for-woocommerce'),
		__('Advanced customizer with source code editor, available for all documents', 'print-invoices-packing-slip-labels-for-woocommerce'),
	),
	array(
		__('Predefined templates', 'print-invoices-packing-slip-labels-for-woocommerce'),
		__('One for document type', 'print-invoices-packing-slip-labels-for-woocommerce'),
		__('Multiple template options', 'print-invoices-packing-slip-labels-for-woocommerce'),
	),
	array(
		__('Free add-on for RTL and Unicode language support', 'print-invoices-packing-slip-labels-for-woocommerce'),
		true,
		true,
	),
	array(
		__('Auto-generate customized Invoice number', 'print-invoices-packing-slip-labels-for-woocommerce'),
		true,
		true,
	),
	array(
		__('Bulk print all documents from the orders page', 'print-invoices-packing-slip-labels-for-woocommerce'),
		true,
		true,
	),
	array(
		__('Option to include/exclude tax to Invoice', 'print-invoices-packing-slip-labels-for-woocommerce'),
		true,
		true,
	),
	array(
		__('WPML compatibility', 'print-invoices-packing-slip-labels-for-woocommerce'),
		true,
		true,
	),
	array(
		__('Signature in Invoice', 'print-invoices-packing-slip-labels-for-woocommerce'),
		false,
		true,
	),
	array(
		__('Remote(cloud) print add-on support', 'print-invoices-packing-slip-labels-for-woocommerce'),
		false,
		true,
	),
	array(
		__('Supports multiple packaging types', 'print-invoices-packing-slip-labels-for-woocommerce'),
		false,
		true,
	),
	array(
		__('Supports custom label sizes', 'print-invoices-packing-slip-labels-for-woocommerce'),
		false,
		true,
	),
	array(
		__('In-built custom checkout field manager', 'print-invoices-packing-slip-labels-for-woocommerce'),
		false,
		true,
	),
	array(
		__('In-built Order meta, Product meta, Product attribute manager', 'print-invoices-packing-slip-labels-for-woocommerce'),
		false,
		true,
	),
	array(
		__('Separate tax column in product table for multiple tax options', 'print-invoices-packing-slip-labels-for-woocommerce'),
		false,
		true,
	),
	array(
		__('Total tax column in product table', 'print-invoices-packing-slip-labels-for-woocommerce'),
		false,
		true,
	),
	array(
		__('Sort order items in product table', 'print-invoices-packing-slip-labels-for-woocommerce'),
		false,
		true,
	),
	array(
		__('Group order items by category in the product table', 'print-invoices-packing-slip-labels-for-woocommerce'),
		false,
		true,
	),
	array(
		__('Share Packing slip PDF and Picklist PDF as separate emails.', 'print-invoices-packing-slip-labels-for-woocommerce'),
		false,
		true,
	),
	array(
		__('Temp file manager (Auto cleanup, Download as zip etc)', 'print-invoices-packing-slip-labels-for-woocommerce'),
		false,
		true,
	),
	array(
		__('Custom footer for individual documents', 'print-invoices-packing-slip-labels-for-woocommerce'),
		false,
		true,
	),
	array(
		__('Custom logo to Invoice', 'print-invoices-packing-slip-labels-for-woocommerce'),
		false,
		true,
	),
);
function wt_pklist_free_vs_pro_column_vl($vl, $yes_icon, $no_icon)
{
	if(is_array($vl))
	{
		foreach ($vl as $value)
		{
			if(is_bool($value))
			{
				echo ($value ? $yes_icon : $no_icon);
			}else
			{
				//string only
				echo $value;
			}
		}
	}else
	{
		if(is_bool($vl))
		{
			echo ($vl ? $yes_icon : $no_icon);
		}else
		{
			//string only
			echo $vl;
		}
	}
}
?>

<table class="wt_pklist_freevs_pro">
	<tr>
		<td><?php _e('FEATURES', 'print-invoices-packing-slip-labels-for-woocommerce'); ?></td>
		<td><?php _e('FREE', 'print-invoices-packing-slip-labels-for-woocommerce'); ?></td>
		<td><?php _e('PREMIUM', 'print-invoices-packing-slip-labels-for-woocommerce'); ?></td>
	</tr>
	<?php
	foreach ($comparison_data as $val_arr)
	{
		?>
		<tr>
			<td><?php echo $val_arr[0];?></td>
			<td>
				<?php
				wt_pklist_free_vs_pro_column_vl($val_arr[1], $yes_icon, $no_icon);
				?>
			</td>
			<td>
				<?php
				wt_pklist_free_vs_pro_column_vl($val_arr[2], $yes_icon, $no_icon);
				?>
			</td>
		</tr>
		<?php
	}
	?>
</table>
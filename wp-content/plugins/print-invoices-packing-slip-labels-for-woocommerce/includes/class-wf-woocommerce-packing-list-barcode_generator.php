<?php
class Wf_Woocommerce_Packing_List_Barcode_generator{
	public function __construct()
	{

	}
	public static function generate($invoice_number)
	{
		$path=plugin_dir_path(__FILE__).'vendor/picqer/';
		include_once($path.'BarcodeGenerator.php');
		include_once($path.'BarcodeGeneratorPNG.php');
		include_once($path.'BarcodeGeneratorSVG.php');
		include_once($path.'BarcodeGeneratorJPG.php');
		include_once($path.'BarcodeGeneratorHTML.php');
		$generator = new BarcodeGeneratorPNG();
		return 'data:image/png;base64,' . base64_encode($generator->getBarcode($invoice_number, $generator::TYPE_CODE_128));
	}
	
}
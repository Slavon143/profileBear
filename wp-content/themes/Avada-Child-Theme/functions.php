<?php
#
function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [] );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 20 );

function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );


/*----product image delete----*/

add_action( 'before_delete_post', 'delete_product_images', 10, 1 );
function delete_product_images( $post_id )
{
$product = wc_get_product( $post_id );
if ( !$product ) {
return;
}
$featured_image_id = $product->get_image_id();
$image_galleries_id = $product->get_gallery_image_ids();
if( !empty( $featured_image_id ) ) {
wp_delete_post( $featured_image_id );
}
if( !empty( $image_galleries_id ) ) {
foreach( $image_galleries_id as $single_image_id ) {
wp_delete_post( $single_image_id );
}
}
}
// CUSTOM FIELD-----------------------------------------
// 1. Add custom field input @ Product Data > Variations > Single Variation
add_action( 'woocommerce_variation_options_pricing', 'bbloomer_add_custom_field_to_variations', 10, 3 );
 
function bbloomer_add_custom_field_to_variations( $loop, $variation_data, $variation ) {
   woocommerce_wp_text_input( array(
'id' => 'custom_field[' . $loop . ']',
'class' => 'short',
'label' => __( 'Custom Field (Extern lager saldo)', 'woocommerce' ),
'value' => get_post_meta( $variation->ID, 'custom_field', true )
   ) );
}
// -----------------------------------------
// 2. Save custom field on product variation save
 
add_action( 'woocommerce_save_product_variation', 'bbloomer_save_custom_field_variations', 10, 2 );
 
function bbloomer_save_custom_field_variations( $variation_id, $i ) {
   $custom_field = $_POST['custom_field'][$i];
   if ( isset( $custom_field ) ) update_post_meta( $variation_id, 'custom_field', esc_attr( $custom_field ) );
}
// -----------------------------------------
// 3. Store custom field value into variation data
add_filter( 'woocommerce_available_variation', 'bbloomer_add_custom_field_variation_data' );
function bbloomer_add_custom_field_variation_data( $variations ) {
   $variations['custom_field'] = '<div class="woocommerce_custom_field">Custom Field (Extern lager saldo): <span>' . get_post_meta( $variations[ 'variation_id' ], 'custom_field', true ) . '</span></div>';
   return $variations;
}
// CUSTOM FIELD END-----------------------------------------
/*---------------------------*/
/*Set Variant Prod to Managed*/
/*Set Backorder on Prod      */
/*Made by Hannells IT        */
/*---------------------------*/
//add_action( 'custom_check_variant_prod_cron_job', 'custom_check_variant_prod_function' );
function custom_check_variant_prod_function() {

global $wpdb;

//Hämtar data ifrån DB. (Endast POST_ID väljas för vidare skicka.)
$query1 = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_tax_class' and meta_value = 'parent'", ARRAY_A);

$array1 = array();

foreach($query1 as $row1)
{
    array_push($array1, $row1['post_id']);
}

//Hämtar data ifrån DB. (Endast POST_ID väljas för vidare skicka.)
$query2 = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_manage_stock' and meta_value = 'no'", ARRAY_A);

$array2 = array();

foreach($query2 as $row2)
{
    array_push($array2, $row2['post_id']);
}

//Hämtar data ifrån DB. (Endast POST_ID väljas för vidare skicka.)
$query3 = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_backorders' and meta_value = 'no'", ARRAY_A);

$array3 = array();

foreach($query3 as $row3)
{
    array_push($array3, $row3['post_id']);
}



//Kör en compare (Se vilka som inte är korrekt inställd)    
//Child produkter & inte lagerhanterat
$products_to_change_result1=array_intersect($array1,$array2);

//Child produkter & inte backorderable
$products_to_change_result2=array_intersect($array1,$array3);


//Sätt yes på _manage_stock
foreach($products_to_change_result1 as $product_id)
{
    update_post_meta($product_id,'_manage_stock','yes');
}

//Sätt yes på _backorders
foreach($products_to_change_result2 as $product_id)
{
    update_post_meta($product_id,'_backorders','yes');
    echo $product_id;
}



}
add_shortcode('custom_check_variant_prod_shortcode', 'custom_check_variant_prod_function');
/*---------------------------*/
/*WooCommerce Import Function*/
/*Made by Hannells IT        */
/*---------------------------*/
add_action( 'custom_new_cron_job', 'custom_cj_function' );
function custom_cj_function() {

//$valueout_sku = find_prod_id_by_sku("HIT-TEST001");
//$valueout_ean = find_prod_id_by_ean("HIT-BARCODE002");    
    
//echo $valueout_sku . " - Found by SKU";
//echo '<br>';
//echo $valueout_ean . " - Found by EAN";

// add_custom_external_stock($valueout_sku,10);//!
// add_custom_external_stock($valueout_ean,15);//!
//Save for testing purpose

run_portwest_import();
//run_bastadgruppen_import();

}
add_shortcode('custom_new_cron_job_shortcode', 'custom_cj_function');
/*-------------------*/
/*Find prod ID by SKU*/
/*-------------------*/
function find_prod_id_by_sku($sku_input) {
global $wpdb;

    $sku = $sku_input;
    
    if(!$sku)
        return null;
    $product_id = $wpdb->get_var(
      $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1",
        $sku)
    );

    if ($product_id)
        return $product_id;
    
    return null;
    
}
/*-------------------*/
/*Find prod ID by EAN*/
/*-------------------*/
function find_prod_id_by_ean($EAN_input) {

global $wpdb;

    $ean = $EAN_input;
    
    if(!$ean)
        return null;
    $product_id = $wpdb->get_var(
      $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_fortnox_ean' AND meta_value='%s' LIMIT 1",
        $ean)
    );

    if ($product_id)
        return $product_id;

    return null;
    
}
/*----------------------------------------*/
/*Add custom external stock (Custom field)*/
/*----------------------------------------*/
function add_custom_external_stock($prodIDtoChange,$stockValueToSet) {
    global $wpdb;
    update_post_meta($prodIDtoChange,'custom_field',$stockValueToSet);
}
/*--------*/
/*PortWest*/
/*--------*/
function run_portwest_import() {

if (($handle = fopen("https://d11ak7fd9ypfb7.cloudfront.net/marketing_files/simple_soh/simpleSOH20.csv", "r")) !== FALSE) {
    if (empty($handle)){
        error_log('Empty');
    }
    $row = 1;
    $lineFound = 0;


    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
        if ($row == 1){
			$row++;
		}
		
		else{
			$num = count($data);
			$row++;
			for ($c=0; $c < $num; $c++) {
								
				if($lineFound == 1){
					$storageAmount = $data[$c];
					$lineFound=0;
					/*Code for WooCommerce import*/
					
					//echo $storageAmount . " " . $article_number . "<br>";
					
					$valueout_sku = find_prod_id_by_sku($article_number);

					add_custom_external_stock($valueout_sku,$storageAmount);
				}
				
				else if ($lineFound == 0){
					$article_number = $data[$c];
					$lineFound = 1;
				}
				
			}
		}
    }
    fclose($handle);
}

}
/*--------------*/
/*Baastadgruppen*/
/*--------------*/
function run_bastadgruppen_import() {
// connect and login to FTP server
$contents = file_get_contents('ftp://saldofil:3astad5ruppen!@cmueshzubkda.bastadgruppen.se/Saldo.txt');

//For each F found; Make a new 'line'
$pieces = explode("F", $contents);

foreach ($pieces as $value) {
	//Set line not found
	$lineFound = 0;

	$find_prod = explode(" ", $value);
	
	foreach ($find_prod as $value1) {
	$string = $value1;
	$substring = "00000000000000";
	$length = strlen($substring);
	if ( substr_compare($string, $substring, -$length) === 0 ) {	
		$lineFound = 1;
		$get_amount_to_var = substr($string, -19,5);
		$var_get_amount = (int)$get_amount_to_var;
	} 
	}


	//Run this is stock is found
	if ($lineFound == 1) {
		$trimstring = str_replace(' ', '', $value);
		$get_EAN = substr($trimstring, -23,-1);
		$var_get_EAN = (int)$get_EAN;
	}
	

	//Check so EAN is not empty
	if ($var_get_EAN == 0){

	}
	else{
	/*Code for WooCommerce import*/
	//echo $var_get_amount . " " . $var_get_EAN . "<br>"; 
	
	$valueout_EAN = find_prod_id_by_ean($var_get_EAN);
	add_custom_external_stock($valueout_EAN,$var_get_amount);
	
	}

  }
    
}
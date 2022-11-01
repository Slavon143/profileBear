<?php
// to check whether accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Wf_Woocommerce_Packing_List_Box_packing
{
    public $wf_package_type;
    public $template_type;
    public function __construct()
    {
        $this->wf_package_type=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_package_type');
        $this->boxes=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_boxes');
        $this->dimension_unit=get_option('woocommerce_dimension_unit');
        $this->weight_unit = get_option('woocommerce_weight_unit');
    }
    public function create_order_package($order, $template_type) 
    {
        $this->template_type=$template_type;
        switch ($this->wf_package_type)
        {
            case 'box_packing':
                if(count($this->boxes)>0)
                {
                    $deleted_product_set = 0;
                    $order_items=$order->get_items();
                    foreach ($order_items as $order_item_id=>$order_item) 
                    {
                        $_product=$order_item->get_product();
                        if(empty($_product))
                        {
                            $deleted_product_set = 1;
                            break;
                        }
                    }
                    if($deleted_product_set == 0){
                        return $this->wf_pklist_create_order_box_shipping_package($order);
                    }else{
                        return $this->wf_pklist_create_order_indvidual_item_package($order);
                    }
                }else
                {
                    return $this->wf_pklist_create_order_indvidual_item_package($order);
                }
                break;
            case 'pack_items_individually':
                return $this->wf_pklist_create_order_indvidual_item_package($order);
                break;
            default:
                return $this->wf_pklist_create_order_single_package($order);
                break;
        }
    }

    // Function to create packaging list and shipping lables package
    public function wf_pklist_create_order_single_package($order) {

        $order_items = $order->get_items();
        $item_meta = array();
        $packinglist_package = array();
        foreach ($order_items as $id => $item) 
        {     
            $product = $item->get_product();      
            if($product) 
            {
                $extra_meta_details = $this->wf_pklist_get_extra_meta_details($item_meta, $order, $product, $id, $item );
                $sku = $variation_details = '';

                if (WC()->version < '2.7.0') {
                    $product_id = $product->id;
                    $product_variation_data = $product->variation_data;
                    $product_product_type = $product->product_type;
                    $product_variation_id = $product_product_type === 'variation' ? $product->variation_id : '';
                } else {
                    $product_id = $product->get_id();
                    $product_variation_data = $product->is_type('variation') ? wc_get_product_variation_attributes($product->get_id()) : '';
                    $product_product_type = $product->get_type();
                    $product_variation_id = $product->is_type('variation') ? $product->get_id() : '';
                }
                $sku = $product->get_sku();
                $item_meta = (WC()->version < '3.1.0') ? new WC_Order_Item_Meta($item) : new WC_Order_Item_Product($item);
                $variation_details ='';
                if(Wf_Woocommerce_Packing_List_Admin::module_exists('customizer'))
                {
                    $variation_details = Wf_Woocommerce_Packing_List_Customizer::get_order_line_item_variation_data($item, $id, $product, $order, $this->template_type);
                }
                $variation_id = $product_product_type == 'variation' ? $product_variation_id : '';
                $packinglist_package[0][] = array(
                    'sku' => $product->get_sku(),
                    'name' => $product->get_name(),
                    'type' => $product_product_type,
                    'extra_meta_details' => $extra_meta_details,
                    'weight' => $product->get_weight(),
                    'id' => $product_id,
                    'variation_id' => $variation_id,
                    'price' => $product->get_price(),
                    'variation_data' => $variation_details,
                    'quantity' => $item['qty'],
                    'order_item_id' =>$id,
                    'dimension_unit'=>$this->dimension_unit,
                    'weight_unit'=>$this->weight_unit,                   
                );
            }else{
                $packinglist_package[0][] = array(
                    'sku' => '',
                    'name' => $item['name'],
                    'type' => '',
                    'extra_meta_details' => '',
                    'weight' => '',
                    'id' => '',
                    'variation_id' => '',
                    'price' => (float)$item['line_total']/(int)$item['qty'],
                    'variation_data' => '',
                    'quantity' => $item['qty'],
                    'order_item_id' =>$id,
                    'dimension_unit'=>'',
                    'weight_unit'=>'',                   
                );
            }
        }
        return $packinglist_package;
    }


    // function to get template body table body content
    public function wf_packinglist_get_table_content($order, $order_package, $show_price = false)
    {
        $return = "";
        $weight_of_item_and_box = !empty($order_package[0]['package_weight']) ? $order_package[0]['package_weight'] : ' ';
        $box_name = !empty($order_package[0]['title']) ? $order_package[0]['title'] : ' ';
        $weight_of_item = 0;
        foreach ($order_package as $order_package_individual_item) {
            $weight_of_item += (!empty($order_package_individual_item['weight'])) ? $order_package_individual_item['weight'] * $order_package_individual_item['quantity'] : __('0', 'print-invoices-packing-slip-labels-for-woocommerce');
        }

        if (key_exists('Value', $order_package)) {
            $weight = ($order_package['Value'] != '') ? $order_package['Value'] : __('n/a', 'print-invoices-packing-slip-labels-for-woocommerce');
        } else {
            $weight = apply_filters('wf_shipping_label_weight_customization', $weight_of_item, $weight_of_item_and_box);
        }

        $orderdetails = array(
            'order_id' => $order->get_order_number(),
            'weight' => ($weight != '') ? $weight . ' ' . get_option('woocommerce_weight_unit') : __('n/a', 'print-invoices-packing-slip-labels-for-woocommerce'),
            'name' => !empty($order_package[0]['title']) ? $order_package[0]['title'] : ' '
        );
        return apply_filters('wf_pklist_modify_label_order_details', $orderdetails);
    }
    public function wf_pklist_create_order_box_shipping_package($order)
    {
        $packages = array();
        $boxpack = new WF_Boxpack();
         
        // Define boxes
        foreach ($this->boxes as $key => $box) {
            
           
            if (!is_numeric($key)) {
                continue;
            }
            if (!$box['enabled']) {
                continue;
            }
            $newbox = $boxpack->add_box($box['name'],$box['length'], $box['width'], $box['height'], $box['box_weight']);
            
            if (isset($box['id'])) {
                $newbox->set_id(current(explode(':', $box['id'])));
            }
            if ($box['max_weight']) {
                $newbox->set_max_weight($box['max_weight']);
            }


        }

        $orderItems = $order->get_items();
        $items = array();
        foreach ($orderItems as $orderItem) {
            if (!empty($orderItem)) {
                $product_data = wc_get_product($orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id']);
                $items[] = array('data' => $product_data, 'quantity' => $orderItem['qty']);
            }
        }
        if (!empty($items)) {
            $package['contents'] = $items;

            // Add items
            foreach ($package['contents'] as $item_id => $values) {
                if ($values['data']) {
                    if (!$values['data']->needs_shipping()) {
                        continue;
                    }
                    $skip_product = apply_filters('wf_shipping_skip_product', false, $values, $package['contents']);
                    if ($skip_product) {
                        continue;
                    }
                    if ((WC()->version < '2.7.0')) {
                        $p_length = $values['data']->length;
                        $p_height = $values['data']->height;
                        $p_weight = $values['data']->weight;
                        $p_width = $values['data']->width;
                    } else {
                        $p_length = $values['data']->get_length();
                        $p_height = $values['data']->get_height();
                        $p_weight = $values['data']->get_weight();
                        $p_width = $values['data']->get_width();
                    }

                    if ($p_length && $p_height && $p_width && $p_weight) {
                        $dimensions = array($p_length, $p_height, $p_width);
                        for ($i = 0; $i < $values['quantity']; $i ++) {

                            $boxpack->add_item($box['name'],
                                    wc_get_dimension($dimensions[2], $this->dimension_unit), wc_get_dimension($dimensions[1], $this->dimension_unit), wc_get_dimension($dimensions[0], $this->dimension_unit), wc_get_weight($values['data']->get_weight(), $this->weight_unit), $values['data']->get_price(), array(
                                'data' => $values['data']
                                    )
                            );
                        }

                    } else {
                        return $this->wf_pklist_create_order_indvidual_item_package($order);
                    }
                }
            }

            // Pack it
            $boxpack->pack();
            $packages = $boxpack->get_packages();
        }      
        return $this->wf_pklist_create_packinglist_boxpack_package($packages, $order);
    }
    public function wf_pklist_create_packinglist_boxpack_package($to_ship, $order) {
        $packinglist_package = array();
        $item_meta = array();
        $item=null;
        foreach ($to_ship as $key => $packages) {
            if (property_exists($packages, 'packed')) 
            {
                foreach ($packages->packed as $id => $product_data) {
                   
                    $is_product_already_exist = false;
                    $package_id_count;
                    $product = $product_data->meta['data'];                 
                    if ($product) 
                    {
                        $extra_meta_details = $this->wf_pklist_get_extra_meta_details($item_meta, $order, $product, $id, $item );
                        if (WC()->version < '2.7.0') {
                            $product_id = $product->id;
                            $product_variation_data = $product->variation_data;
                            $product_product_type = $product->product_type;
                            $product_variation_id = $product_product_type === 'variation' ? $product->variation_id : '';
                        } else {
                            $product_id = $product->get_id();
                            $product_variation_data = $product->is_type('variation') ? wc_get_product_variation_attributes($product->get_id()) : '';
                            $product_product_type = $product->get_type();
                            $product_variation_id = $product->is_type('variation') ? $product->get_id() : '';
                        }

                        if (is_array($packinglist_package) && (count($packinglist_package) > 0) && key_exists($key, $packinglist_package)) {
                            foreach ($packinglist_package[$key] as $package_id => $package_data) {
                                if ($product_id == $package_data['id']) {
                                    $package_id_count = $package_id;
                                    $is_product_already_exist = true;
                                }
                            }
                        }
                        if ($is_product_already_exist) {
                            $packinglist_package[$key][$package_id_count]['quantity'] += 1;
                        } else {
                            $variation_details = $product_product_type == 'variation' ? wc_get_formatted_variation($product_variation_data, true) : '';
                            $variation_id = $product_product_type == 'variation' ? $product_variation_id : '';
                            
                            $packinglist_package[$key][] = array(
                                'sku' => $product->get_sku(),
                                'name' => $product->get_name(),
                                'type' => $product_product_type,
                                'weight' => $product->get_weight(),
                                'id' => $product_id,
                                'variation_id' => $variation_id,
                                'extra_meta_details' => $extra_meta_details,
                                'price' => $product->get_price(),
                                'variation_data' => $variation_details,
                                'quantity' => 1,
                                'package_weight' => $packages->weight,
                                'title' => $packages->box_name,
                                //'order_item_id' =>$id,
                            );

                        }
                    }
                }
                $next_package = next($to_ship);
                if (!empty($next_package) && !(property_exists($next_package, 'packed'))) {
                    foreach ($packages->unpacked as $id => $product_data) {
                        $product = $product_data->meta['data'];                     
                        if ($product)
                        {
                            $extra_meta_details = $this->wf_pklist_get_extra_meta_details($item_meta, $order, $product, $id, $item );
                            if (WC()->version < '2.7.0') {
                                $product_id = $product->id;
                                $product_variation_data = $product->variation_data;
                                $product_product_type = $product->product_type;
                                $product_variation_id = $product_product_type === 'variation' ? $product->variation_id : '';
                            } else {
                                $product_id = $product->get_id();
                                $product_variation_data = $product->is_type('variation') ? wc_get_product_variation_attributes($product->get_id()) : '';
                                $product_product_type = $product->get_type();
                                $product_variation_id = $product->is_type('variation') ? $product->get_id() : '';
                            }
                            $variation_details = $product_product_type == 'variation' ? wc_get_formatted_variation($product_variation_data, true) : '';
                            $variation_id = $product_product_type == 'variation' ? $product_variation_id : '';
                            $packinglist_package[][] = array(
                                'sku' => $product->get_sku(),
                                'name' => $product->get_name(),
                                'type' => $product_product_type,
                                'weight' => $product->get_weight(),
                                'id' => $product_id,
                                'extra_meta_details' => $extra_meta_details,
                                'variation_id' => $variation_id,
                                'price' => $product->get_price(),
                                'variation_data' => $variation_details,
                                'quantity' => 1,
                                'package_weight' => $packages->weight,
                                'title' => $packages->box_name,
                            );
                        }
                    }
                }
            } else {
                if (empty($packinglist_package)) {
                    $packinglist_package = $this->wf_pklist_create_order_indvidual_item_package($order);
                }
            }
        }

        return $packinglist_package;
    }

    public function wf_pklist_get_extra_meta_details($item_meta, $order, $product, $id, $item)
    {
        $extra_meta_details='';
        if($product)
        {
            $product_id = (WC()->version < '2.7.0') ? $product->id : $product->get_id();
            $_product = wc_get_product($product_id);                        
            $item_meta = array();
            if (((WC()->version < '2.7.0') ? $product->id : $product->get_id()) == ((WC()->version < '2.7.0') ? $_product->id : $_product->get_id())) {
                $item_meta = function_exists('wc_get_order_item_meta') ? wc_get_order_item_meta($id, '', false) : $order->get_item_meta($id);
                   }
            $extra_meta_details = apply_filters('wf_print_invoice_variation_add', $item_meta);
        }
        return $extra_meta_details;
    }

    //function to create order package for individual items packing
    public function wf_pklist_create_order_indvidual_item_package($order) {
        
        $order_items = $order->get_items();
        $item_meta = array();
        $packinglist_package = array();
        foreach ($order_items as $id => $item) {
            $product = $item->get_product();
            if ($product) 
            {
                $extra_meta_details = $this->wf_pklist_get_extra_meta_details($item_meta, $order, $product, $id, $item );
                $sku = $variation_details = '';
                if (WC()->version < '2.7.0') {
                    $product_id = $product->id;
                    $product_variation_data = $product->variation_data;
                    $product_product_type = $product->product_type;
                    $product_variation_id = $product_product_type === 'variation' ? $product->variation_id : '';
                } else {
                    $product_id = $product->get_id();
                    $product_variation_data = $product->is_type('variation') ? wc_get_product_variation_attributes($product->get_id()) : '';
                    $product_product_type = $product->get_type();
                    $product_variation_id = $product->is_type('variation') ? $product->get_id() : '';
                }
                $sku = $product->get_sku();
                $item_meta = (WC()->version < '3.1.0') ? new WC_Order_Item_Meta($item) : new WC_Order_Item_Product($item);
                $variation_details ='';
                if(Wf_Woocommerce_Packing_List_Admin::module_exists('customizer'))
                {
                    $variation_details = Wf_Woocommerce_Packing_List_Customizer::get_order_line_item_variation_data($item, $id, $product, $order, $this->template_type);
                }
                $variation_id = $product_product_type === 'variation' ? $product_variation_id : '';
                for ($item_count = 0; $item_count < $item['qty']; $item_count++) {
                    $packinglist_package[][] = array(
                        'sku' => $product->get_sku(),
                        'name' => $product->get_name(),
                        'type' => $product_product_type,
                        'weight' => $product->get_weight(),
                        'id' => $product_id,
                        'extra_meta_details' => $extra_meta_details,
                        'variation_id' => $variation_id,
                        'price' => $product->get_price(),
                        'variation_data' => $variation_details,
                        'quantity' => 1,
                        'order_item_id' =>$id,
                        
                    );
                }
            }else{
                for ($item_count = 0; $item_count < $item['qty']; $item_count++) {
                    $packinglist_package[][] = array(
                        'sku' => '',
                        'name' => $item['name'],
                        'type' => '',
                        'weight' => '',
                        'id' => '',
                        'extra_meta_details' => '',
                        'variation_id' => '',
                        'price' => (float)$item['line_total']/(int)$item['qty'],
                        'variation_data' => '',
                        'quantity' => 1,
                        'order_item_id' =>$id,
                        
                    );
                }
            }
        }
        return $packinglist_package;
    }
}
class WF_Boxpack {

    private $boxes;
    private $items;
    private $packages;
    private $cannot_pack;

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        
    }

    /**
     * clear_items function.
     *
     * @access public
     * @return void
     */
    public function clear_items() {
        $this->items = array();
    }

    /**
     * clear_boxes function.
     *
     * @access public
     * @return void
     */
    public function clear_boxes() {
        $this->boxes = array();
    }

    /**
     * add_item function.
     *
     * @access public
     * @return void
     */
    public function add_item($box_name,$length, $width, $height, $weight, $value = '', $meta = array()) {
        $this->items[] = new WF_Boxpack_Item($box_name,$length, $width, $height, $weight, $value, $meta);
    }

    /**
     * add_box function.
     *
     * @access public
     * @param mixed $length
     * @param mixed $width
     * @param mixed $height
     * @param mixed $weight
     * @return void
     */
    public function add_box($box_name,$length, $width, $height, $weight = 0) {

        $new_box = new WF_Boxpack_Box($box_name,$length, $width, $height, $weight);


        $this->boxes[] = $new_box;
        return $new_box;
    }

    /**
     * get_packages function.
     *
     * @access public
     * @return void
     */
    public function get_packages() {
        return $this->packages ? $this->packages : array();
    }

    /**
     * pack function.
     *
     * @access public
     * @return void
     */
    public function pack() {
        try {
            // We need items
            if (sizeof($this->items) == 0) {
                throw new Exception('No items to pack!');
            }

            // Clear packages
            $this->packages = array();

            // Order the boxes by volume
            $this->boxes = $this->order_boxes($this->boxes);
            

            if (!$this->boxes) {
                $this->cannot_pack = $this->items;
                $this->items = array();
            }

            // Keep looping until packed
            while (sizeof($this->items) > 0) {
                $this->items = $this->order_items($this->items);

                $possible_packages = array();
                $best_package = '';

                // Attempt to pack all items in each box
                foreach ($this->boxes as $box) {
                    $possible_packages[] = $box->pack($this->items);

                }
                // Find the best success rate
                $best_percent = 0;

                foreach ($possible_packages as $package) {
                    if ($package->percent > $best_percent) {
                        $best_percent = $package->percent;
                    }
                }

                if ($best_percent == 0) {
                    $this->cannot_pack = $this->items;
                    $this->items = array();
                } else {
                    // Get smallest box with best_percent
                    $possible_packages = array_reverse($possible_packages);
            
                    foreach ($possible_packages as $package) {
                        if ($package->percent == $best_percent) {
                            $best_package = $package;
                            break; // Done packing
                        }
                    }

                    // Update items array
                    $this->items = $best_package->unpacked;


                    // Store package
                    $this->packages[] = $best_package;


                }
            }

            // Items we cannot pack (by now) get packaged individually
            if ($this->cannot_pack) {
                foreach ($this->cannot_pack as $item) {
                    $package = new stdClass();
                    $package->id = '';
                    $package->weight = $item->get_weight();
                    $package->length = $item->get_length();
                    $package->width = $item->get_width();
                    $package->height = $item->get_height();
                    $package->value = $item->get_value();
                    $package->unpacked = true;
                    $this->packages[] = $package;
                }
            }
                
             } catch (Exception $e) {

            // Display a packing error for admins
            if (current_user_can('manage_woocommerce')) {
                echo 'Packing error: ', $e->getMessage(), "\n";
            }
        }
    }

    /**
     * Order boxes by weight and volume
     * $param array $sort
     * @return array
     */
    private function order_boxes($sort) {
        if (!empty($sort)) {
            uasort($sort, array($this, 'box_sorting'));
        }
        return $sort;
    }

    /**
     * Order items by weight and volume
     * $param array $sort
     * @return array
     */
    private function order_items($sort) {
        if (!empty($sort)) {
            uasort($sort, array($this, 'item_sorting'));
        }
        return $sort;
    }

    /**
     * order_by_volume function.
     *
     * @access private
     * @return void
     */
    private function order_by_volume($sort) {
        if (!empty($sort)) {
            uasort($sort, array($this, 'volume_based_sorting'));
        }
        return $sort;
    }

    /**
     * item_sorting function.
     *
     * @access public
     * @param mixed $a
     * @param mixed $b
     * @return void
     */
    public function item_sorting($a, $b) {
        if ($a->get_volume() == $b->get_volume()) {
            if ($a->get_weight() == $b->get_weight()) {
                return 0;
            }
            return ( $a->get_weight() < $b->get_weight() ) ? 1 : -1;
        }
        return ( $a->get_volume() < $b->get_volume() ) ? 1 : -1;
    }

    /**
     * box_sorting function.
     *
     * @access public
     * @param mixed $a
     * @param mixed $b
     * @return void
     */
    public function box_sorting($a, $b) {
        if ($a->get_volume() == $b->get_volume()) {
            if ($a->get_max_weight() == $b->get_max_weight()) {
                return 0;
            }
            return ( $a->get_max_weight() < $b->get_max_weight() ) ? 1 : -1;
        }
        return ( $a->get_volume() < $b->get_volume() ) ? 1 : -1;
    }

    /**
     * volume_based_sorting function.
     *
     * @access public
     * @param mixed $a
     * @param mixed $b
     * @return void
     */
    public function volume_based_sorting($a, $b) {
        if ($a->get_volume() == $b->get_volume()) {
            return 0;
        }
        return ( $a->get_volume() < $b->get_volume() ) ? 1 : -1;
    }

}

/**
 * WF_Boxpack_Box class.
 */
class WF_Boxpack_Box {

    /** @var string ID of the box - given to packages */
    private $id = '';

    /** @var float Weight of the box itself */
    private $weight;

    /** @var float Max allowed weight of box + contents */
    private $max_weight = 0;

    /** @var float Outer dimension of box sent to shipper */
    private $outer_height;

    /** @var float Outer dimension of box sent to shipper */
    private $outer_width;

    /** @var float Outer dimension of box sent to shipper */
    private $outer_length;

    /** @var float Inner dimension of box used when packing */
    private $height;

    /** @var float Inner dimension of box used when packing */
    private $width;

    /** @var float Inner dimension of box used when packing */
    private $length;

    /** @var float Dimension is stored here if adjusted during packing */
    private $packed_height;
    private $maybe_packed_height = null;

    /** @var float Dimension is stored here if adjusted during packing */
    private $packed_width;
    private $maybe_packed_width = null;

    /** @var float Dimension is stored here if adjusted during packing */
    private $packed_length;
    private $maybe_packed_length = null;

    /** @var float Volume of the box */
    private $volume;

    /** @var Array Valid box types which affect packing */
    private $valid_types = array('box', 'tube', 'envelope', 'packet');

    /** @var string This box type */
    private $type = 'box';

    /** @var string This box name */
     private $box_name='';

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct($box_name,$length, $width, $height, $weight = 0) {
        $dimensions = array($length, $width, $height);

        sort($dimensions);

        $this->outer_length = $this->length = $dimensions[2];
        $this->outer_width = $this->width = $dimensions[1];
        $this->outer_height = $this->height = $dimensions[0];
        $this->weight = $weight;
        $this->box_name=$box_name;
    }

    /**
     * set_id function.
     *
     * @access public
     * @param mixed $weight
     * @return void
     */
    public function set_id($id) {
        $this->id = $id;
    }

    /**
     * Set the volume to a specific value, instead of calculating it.
     * @param float $volume
     */
    public function set_volume($volume) {
        $this->volume = floatval($volume);
    }

    /**
     * Set the type of box
     * @param string $type
     */
    public function set_type($type) {
        if (in_array($type, $this->valid_types)) {
            $this->type = $type;
        }
    }

    /**
     * Get max weight.
     *
     * @return float
     */
    public function get_max_weight() {
        return floatval($this->max_weight);
    }

    /**
     * set_max_weight function.
     *
     * @access public
     * @param mixed $weight
     * @return void
     */
    public function set_max_weight($weight) {
        $this->max_weight = $weight;
    }

    /**
     * set_inner_dimensions function.
     *
     * @access public
     * @param mixed $length
     * @param mixed $width
     * @param mixed $height
     * @return void
     */
    public function set_inner_dimensions($length, $width, $height) {
        $dimensions = array($length, $width, $height);

        sort($dimensions);

        $this->length = $dimensions[2];
        $this->width = $dimensions[1];
        $this->height = $dimensions[0];
    }

    /**
     * See if an item fits into the box.
     *
     * @param object $item
     * @return bool
     */
    public function can_fit($item) {
        switch ($this->type) {
            // Tubes are designed for long thin items so see if the item meets that criteria here.
            case 'tube' :
                $can_fit = ( $this->get_length() >= $item->get_length() && $this->get_width() >= $item->get_width() && $this->get_height() >= $item->get_height() && $item->get_volume() < $this->get_volume() ) ? true : false;
                $can_fit = $can_fit && $item->get_length() >= ( ( $item->get_width() + $this->get_height() ) * 2 );
                break;
            // Packets are flexible
            case 'packet' :
                $can_fit = ( $this->get_packed_length() >= $item->get_length() && $this->get_packed_width() >= $item->get_width() && $item->get_volume() < $this->get_volume() ) ? true : false;

                if ($can_fit && $item->get_height() > $this->get_packed_height()) {
                    $this->maybe_packed_height = $item->get_height();
                    $this->maybe_packed_length = $this->get_packed_length() - ( $this->maybe_packed_height - $this->get_height() );
                    $this->maybe_packed_width = $this->get_packed_width() - ( $this->maybe_packed_height - $this->get_height() );

                    $can_fit = ( $this->maybe_packed_height < $this->maybe_packed_width && $this->maybe_packed_length >= $item->get_length() && $this->maybe_packed_width >= $item->get_width() ) ? true : false;
                }
                break;
            // Boxes are easy
            default :
                $can_fit = ( $this->get_length() >= $item->get_length() && $this->get_width() >= $item->get_width() && $this->get_height() >= $item->get_height() && $item->get_volume() < $this->get_volume() ) ? true : false;
                break;
        }
        return $can_fit;
    }

    /**
     * Reset packed dimensions to originals
     */
    private function reset_packed_dimensions() {
        $this->packed_length = $this->length;
        $this->packed_width = $this->width;
        $this->packed_height = $this->height;
    }

    /**
     * pack function.
     *
     * @access public
     * @param mixed $items
     * @return object Package
     */
    public function pack($items) {
        $packed = array();
        $unpacked = array();
        $packed_weight = $this->get_weight();
        $packed_volume = 0;
        $packed_value = 0;
        $box_name = $this->get_box_name();

        $this->reset_packed_dimensions();

        while (sizeof($items) > 0) {
            $item = array_shift($items);

            // Check dimensions
            if (!$this->can_fit($item)) {
                $unpacked[] = $item;
                continue;
            }

            // Check max weight
            if (( $packed_weight + $item->get_weight() ) > $this->get_max_weight() && $this->get_max_weight() > 0) {
                $unpacked[] = $item;
                continue;
            }

            // Check volume
            if (( $packed_volume + $item->get_volume() ) > $this->get_volume()) {
                $unpacked[] = $item;
                continue;
            }

            // Pack
            $packed[] = $item;
            $packed_volume += $item->get_volume();
            $packed_weight += $item->get_weight();
            $packed_value += $item->get_value();

            // Adjust dimensions if needed, after this item has been packed inside
            if (!is_null($this->maybe_packed_height)) {
                $this->packed_height = $this->maybe_packed_height;
                $this->packed_length = $this->maybe_packed_length;
                $this->packed_width = $this->maybe_packed_width;
                $this->maybe_packed_height = null;
                $this->maybe_packed_length = null;
                $this->maybe_packed_width = null;
            }
        }

        // Get weight of unpacked items
        $unpacked_weight = 0;
        $unpacked_volume = 0;
        foreach ($unpacked as $item) {
            $unpacked_weight += $item->get_weight();
            $unpacked_volume += $item->get_volume();
        }

        $package = new stdClass();
        $package->id = $this->id; 
        $package->packed = $packed;
        $package->unpacked = $unpacked;
        $package->weight = $packed_weight;
        $package->volume = $packed_volume;
        $package->length = $this->get_outer_length();
        $package->width = $this->get_outer_width();
        $package->height = $this->get_outer_height();
        $package->value = $packed_value;
        $package->box_name = $box_name;
        

        // Calculate packing success % based on % of weight and volume of all items packed
        $packed_weight_ratio = null;
        $packed_volume_ratio = null;

        if ($packed_weight + $unpacked_weight > 0) {
            $packed_weight_ratio = $packed_weight / ( $packed_weight + $unpacked_weight );
        }
        if ($packed_volume + $unpacked_volume) {
            $packed_volume_ratio = $packed_volume / ( $packed_volume + $unpacked_volume );
        }

        if (is_null($packed_weight_ratio) && is_null($packed_volume_ratio)) { 
            // Fallback to amount packed
            $package->percent = ( sizeof($packed) / ( sizeof($unpacked) + sizeof($packed) ) ) * 100;
        } elseif (is_null($packed_weight_ratio)) {
            // Volume only
            $package->percent = $packed_volume_ratio * 100;
        } elseif (is_null($packed_volume_ratio)) {
            // Weight only
            $package->percent = $packed_weight_ratio * 100;
        } else { 
            $package->percent = $packed_weight_ratio * $packed_volume_ratio * 100;
        }
        return $package;
    }

    /**
     * get_volume function.
     * @return float
     */
    public function get_volume() {
        if ($this->volume) {
            return $this->volume;
        } else {
            return floatval($this->get_height() * $this->get_width() * $this->get_length());
        }
    }

    /**
     * get_height function.
     * @return float
     */
    public function get_height() {
        return $this->height;
    }

    /**
     * get_box_name function.
     * @return string
     */
    public function get_box_name() {
        return $this->box_name;
    }


    /**
     * get_width function.
     * @return float
     */
    public function get_width() {
        return $this->width;
    }

    /**
     * get_width function.
     * @return float
     */
    public function get_length() {
        return $this->length;
    }

    /**
     * get_weight function.
     * @return float
     */
    public function get_weight() {
        return $this->weight;
    }

    /**
     * get_outer_height
     * @return float
     */
    public function get_outer_height() {
        return $this->outer_height;
    }

    /**
     * get_outer_width
     * @return float
     */
    public function get_outer_width() {
        return $this->outer_width;
    }

    /**
     * get_outer_length
     * @return float
     */
    public function get_outer_length() {
        return $this->outer_length;
    }

    /**
     * get_packed_height
     * @return float
     */
    public function get_packed_height() {
        return $this->packed_height;
    }

    /**
     * get_packed_width
     * @return float
     */
    public function get_packed_width() {
        return $this->packed_width;
    }

    /**
     * get_width get_packed_length.
     * @return float
     */
    public function get_packed_length() {
        return $this->packed_length;
    }

}

/**
 * WF_Boxpack_Item class.
 */
class WF_Boxpack_Item {

    public $weight;
    public $height;
    public $width;
    public $length;
    public $volume;
    public $value;
    public $meta;
    public $box_name;

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct($box_name,$length, $width, $height, $weight, $value = '', $meta = array()) {
        $dimensions = array($length, $width, $height);

        sort($dimensions);

        $this->length = $dimensions[2];
        $this->width = $dimensions[1];
        $this->height = $dimensions[0];

        $this->volume = $width * $height * $length;
        $this->weight = $weight;
        $this->value = $value;
        $this->meta = $meta;
        $this->box_name = $box_name;
    }

    /**
     * get_volume function.
     *
     * @access public
     * @return void
     */
    function get_volume() {
        return $this->volume;
    }

    /**
     * get_height function.
     *
     * @access public
     * @return void
     */
    function get_height() {
        return $this->height;
    }

    /**
     * get_width function.
     *
     * @access public
     * @return void
     */
    function get_width() {
        return $this->width;
    }

    /**
     * get_width function.
     *
     * @access public
     * @return void
     */
    function get_length() {
        return $this->length;
    }

    /**
     * get_width function.
     *
     * @access public
     * @return void
     */
    function get_weight() {
        return $this->weight;
    }

    /**
     * get_value function.
     *
     * @access public
     * @return void
     */
    function get_value() {
        return $this->value;
    }

    /**
     * get_meta function.
     *
     * @access public
     * @return void
     */
    function get_meta($key = '') {
        if ($key) {
            if (isset($this->meta[$key])) {
                return $this->meta[$key];
            } else {
                return null;
            }
        } else {
            return array_filter((array) $this->meta);
        }
    }

}

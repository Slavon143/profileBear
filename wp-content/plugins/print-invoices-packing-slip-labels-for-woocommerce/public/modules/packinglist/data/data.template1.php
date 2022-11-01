<style type="text/css">
.clearfix::after {
    display: block;
    clear: both;
  content: "";}
.wfte_invoice-main{ color:#73879C; font-family:"Helvetica Neue", Roboto, Arial, "Droid Sans", sans-serif; font-size:12px; font-weight:400; line-height:14px; box-sizing:border-box; width:100%; margin:0px;}
.wfte_invoice-main *{ box-sizing:border-box;}
.wfte_invoice-header{ background:#fff; color:#000; padding:10px 20px; width:100%; }
.wfte_invoice-header_top{ padding:15px 0px; padding-bottom:10px; width:100%;}
.wfte_left_box{width:40%;}
.wfte_doc_title{ padding: 9px 0px; color:#23a8f9; font-size:30px; font-weight:bold; height:auto; width:auto; float:left; line-height:22px;}
.wfte_company_logo{ float:left; }
.wfte_company_logo_img{ width:150px; max-width:100%; }
.wfte_company_name{ font-size:18px; font-weight:bold; }
.wfte_company_logo_extra_details{ font-size:12px; }
.wfte_barcode{ width:100%; height:auto; margin-top:10px; }
.wfte_order_data{ margin-top:20px; width:100%; padding:0px .5%; float:left; }
.wfte_order_number{ font-size:12px; font-weight:bold; }
.wfte_order_date{ font-size:12px;}
.wfte_addrss_field_main{ width:100%; font-size:12px; padding-top:5px; }
.wfte_from_address{ width:30%; }
.wfte_addrss_fields{ width:30%; padding:0px .5%; line-height:14px;}
.wfte_address-field-header{ font-weight:bold; }
.wfte_invoice_data{ width:100%; margin-top:5px; }
.wfte_email_label,.wfte_tel_label,.wfte_vat_number_label,.wfte_shipping_method_label,.wfte_tracking_number_label,.wfte_ssn_number_label{ font-weight:bold; }
.wfte_invoice-body{background:#ffffff; color:#23272c; padding:10px 20px; width:100%;}
.wfte_product_table{ width:100%; border-collapse:collapse;}
.wfte_product_table_head_bg{background-color:#212529;}
.wfte_table_head_color{color:#ffffff;}
.wfte_product_table_head{}
.wfte_product_table_head th{ border:solid 1px #000; height:36px; padding:0px 5px; font-size:.75rem; text-align:center; line-height:10px;}
.wfte_product_table_body td{padding:8px 5px; text-align:center; font-size:12px; line-height:10px; border:solid 1px #dadada; }
.wfte_return_policy{ width:100%; height:auto; border-top:solid 1px #dfd5d5; padding:5px 0px; margin-top:10px; }
.wfte_footer{width:100%; height:auto; padding:5px 0px; margin-top:10px; font-size:10px;}

.wfte_invoice_data td, .wfte_order_data td, .wfte_extra_fields td{font-size:12px; padding:0px; font-family:"Helvetica Neue", Roboto, Arial, "Droid Sans", sans-serif;}

.float_left{ float:left; }
.float_right{ float:right; }
</style>
<div class="wfte_rtl_main wfte_invoice-main">
  <div class="wfte_invoice-header clearfix">
    <div class="wfte_invoice-header_top clearfix">
      <div class="wfte_doc_title wfte_template_element" data-hover-id="doc_title">__[Packing slip]__</div>
    </div>
      <div class="wfte_invoice-header_top clearfix">
        <div class="float_left wfte_left_box">
          <div class="wfte_company_logo float_left">
            <div class="wfte_company_logo_img_box">
                <img src="[wfte_company_logo_url]" class="wfte_company_logo_img">
            </div>
            <div class="wfte_company_name wfte_hidden">[wfte_company_name]</div>
            <div class="wfte_company_logo_extra_details">__[]__</div>
          </div>
          <div class="clearfix"></div>
          <div class="float_left wfte_text_left" style="width:100%;">
            <div class="wfte_order_data">
              <div class="wfte_order_number">
                <span class="wfte_order_number_label">__[Order No:]__</span> 
                <span class="wfte_order_number_val">[wfte_order_number]</span>
              </div>
              <div class="wfte_order_date" data-order_date-format="m-d-Y">
                <span class="wfte_order_date_label">__[Date:]__</span> 
                <span class="wfte_order_date_val">[wfte_order_date]</span>
              </div>
            </div>
          </div>
        </div>
        <div class="wfte_from_address float_right wfte_text_left">
          <div class="wfte_address-field-header wfte_from_address_label">__[From Address:]__</div>
          <div class="wfte_from_address_val">[wfte_from_address]</div>
        </div>
      </div>
      <div class="wfte_addrss_field_main clearfix">
         <div class="wfte_addrss_fields float_left">
            <div class="wfte_billing_address wfte_text_left">
              <div class="wfte_address-field-header wfte_billing_address_label">__[Billing Address:]__</div>
              [wfte_billing_address]
            </div>
            <div>
              <div class="wfte_invoice_data wfte_text_left">
                <div class="wfte_email">
                  <span class="wfte_email_label">__[Email:]__</span>
                  <span>[wfte_email]</span>
                </div>
                <div class="wfte_tel">
                  <span class="wfte_tel_label">__[Tel:]__</span>
                  <span>[wfte_tel]</span>
                </div>
                <div class="wfte_vat_number">
                  <span class="wfte_vat_number_label">__[VAT:]__</span>
                  <span>[wfte_vat_number]</span>
                </div>
                <div class="wfte_order_item_meta">[wfte_order_item_meta]</div>
              </div>
            </div>
         </div>
         <div class="wfte_addrss_fields float_right">
            <div class="wfte_shipping_address wfte_text_left">
                <div class="wfte_address-field-header wfte_shipping_address_label">__[Shipping Address:]__</div>
                [wfte_shipping_address]
            </div>
            <div class="wfte_text_left">
                <div class="wfte_invoice_data">
                    <div class="wfte_tracking_number">
                      <span class="wfte_tracking_number_label">__[Tracking number:]__</span>
                      <span>[wfte_tracking_number]</span>
                    </div>
                    <div class="wfte_ssn_number">
                      <span class="wfte_ssn_number_label">__[SSN:]__</span>
                      <span>[wfte_ssn_number]</span>
                    </div>
                    [wfte_extra_fields]
                    <div class="wfte_box_name">[wfte_box_name]</div>
                </div>
            </div>
         </div>
      </div>
  </div>
  <div class="wfte_invoice-body clearfix">
    [wfte_product_table_start]   
    <table class="wfte_product_table">
      <thead class="wfte_product_table_head">
        <tr>
          <th class="wfte_product_table_head_serial_no wfte_product_table_head_bg wfte_table_head_color" col-type="serial_no">__[S.No]__</th>
          <th class="wfte_product_table_head_image wfte_table_head_color wfte_product_table_head_bg" col-type="image">__[Image]__</th>
          <th class="wfte_product_table_head_sku wfte_table_head_color wfte_product_table_head_bg" col-type="sku">__[SKU]__</th>
          <th class="wfte_product_table_head_product wfte_table_head_color wfte_product_table_head_bg" col-type="product">__[Product]__</th>
          <th class="wfte_product_table_head_quantity wfte_table_head_color wfte_product_table_head_bg" col-type="quantity">__[Quantity]__</th>
          <th class="wfte_product_table_head_total_weight wfte_table_head_color wfte_product_table_head_bg" col-type="total_weight">__[Total Weight]__</th>    
        </tr>
      </thead>
      <tbody class="wfte_product_table_body wfte_table_body_color">
      </tbody>
    </table>
    [wfte_product_table_end]
    <div class="wfte_footer clearfix wfte_text_left">
      [wfte_footer]
    </div>
  </div>
</div>
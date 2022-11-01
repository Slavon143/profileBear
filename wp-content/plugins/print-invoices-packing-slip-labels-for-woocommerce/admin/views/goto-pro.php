<?php
  $wf_admin_img_path=WF_PKLIST_PLUGIN_URL . 'admin/images/';
?>
<style type="text/css">
  .wt-pdfpro-sidebar{
  background: #FFFFFF;
  border-radius: 7px;
  padding: 0;
}
.wt-pdfpro-header{
  background: #FFFFFF;
  box-shadow: 0px 4px 19px rgba(49, 117, 252, 0.2);
  border-radius: 7px;
  padding: 8px;
  margin: 0;
}
.wt-pdfpro-name{
  background: linear-gradient(87.57deg, #F4F1FF 3%, rgba(238, 240, 255, 0) 93.18%);
  border-radius: 3px;
  margin: 0;
  padding: 16px;
  display: flex;
  align-items: center;
}
.wt-pdfpro-name img{
  width: 36px;
  height: auto;
  box-shadow: 4px 4px 4px rgba(92, 98, 215, 0.23);
  border-radius: 7px;
}
.wt-pdfpro-name h4{
  font-style: normal;
  font-weight: 600;
  font-size: 11px;
  line-height: 15px;
  margin: 0 0 0 12px;
  color: #5D63D9;
}
.wt-pdfpro-mainfeatures ul{
  padding: 0;
  margin: 15px 25px 20px 25px;
}
.wt-pdfpro-mainfeatures li{
  font-style: normal;
  font-weight: bold;
  font-size: 14px;
  line-height:24px;
  letter-spacing: -0.01em;
  list-style: none;
  position: relative;
  color: #091E80;
  padding-left: 28px;
}
.wt-pdfpro-mainfeatures li.money-back:before{
  content: '';
  position: absolute;
  left: 0;
  height:24px ;
  width: 16px;
  background-image: url(<?php echo esc_url($wf_admin_img_path.'money-back.svg'); ?>);
  background-position: center;
  background-repeat: no-repeat;
  background-size: contain;
}
.wt-pdfpro-mainfeatures li.support:before{
  content: '';
  position: absolute;
  left: 0;
  height:24px ;
  width: 16px;
  background-image: url(<?php echo esc_url($wf_admin_img_path.'support.svg'); ?>);
  background-position: center;
  background-repeat: no-repeat;
  background-size: contain;
}
.wt-pdfpro-btn-wrapper{
  display: block;
  margin: 20px auto 20px;
  text-align: center;
}
.wt-pdfpro-blue-btn{
  background: linear-gradient(90.67deg, #2608DF -34.86%, #3284FF 115.74%);
  box-shadow: 0px 4px 13px rgba(46, 80, 242, 0.39);
  border-radius: 5px;
  padding: 10px 15px 10px 38px;
  display: inline-block;
  font-style: normal;
  font-weight: bold;
  font-size: 14px;
  line-height: 18px;
  color: #FFFFFF;
  text-decoration: none;
  transition: all .2s ease;
  position: relative;
  border: none;
}
.wt-pdfpro-blue-btn:before{
  content: '';
  position: absolute;
  height: 15px;
  width: 18px;
  background-image: url(<?php echo esc_url($wf_admin_img_path.'white-crown.svg'); ?>);
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  left: 15px;
}
.wt-pdfpro-blue-btn:hover{
  box-shadow: 0px 4px 13px rgba(46, 80, 242, 0.5);
  text-decoration: none;
  transform: translateY(2px);
  transition: all .2s ease;
  color: #FFFFFF;
}
.wt-pdfpro-features{
  padding: 40px 30px 25px 30px;
}
.wt-pdfpro-features ul{
  padding: 0;
  margin: 0;
}
.wt-pdfpro-features li{
  font-style: normal;
  font-weight: 500;
  font-size: 13px;
  line-height: 17px;
  color: #001A69;
  list-style: none;
  position: relative;
  padding-left: 49px;
  margin: 0 0 15px 0;
  display: flex;
  align-items: center;
}
.wt-pdfpro-newfeat li:before{
  content: '';
  position: absolute;
  height: 39px;
  width: 39px;
  background-image: url(<?php echo esc_url($wf_admin_img_path.'new-badge.svg'); ?>);
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  left: 0;
}
.wt-pdfpro-allfeat li:before{
  content: '';
  position: absolute;
  height: 18px;
  width: 18px;
  background-image: url(<?php echo esc_url($wf_admin_img_path.'tick.svg'); ?>);
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  left: 10px;
}
ul.wt-pdfpro-newfeat li {
  margin-bottom: 30px;
}
.wt-pdfpro-outline-btn{
  border: 1px solid #007FFF;
  background: #fff;
  border-radius: 5px;
  padding: 10px 15px 10px 38px;
  display: inline-block;
  font-style: normal;
  font-weight: bold;
  font-size: 14px;
  line-height: 18px;
  color: #007FFF;
  text-decoration: none;
  transition: all .2s ease;
  position: relative;
  background: transparent;
}
.wt-pdfpro-outline-btn:before{
  content: '';
  position: absolute;
  height: 15px;
  width: 18px;
  background-image: url(<?php echo esc_url($wf_admin_img_path.'blue-crown.svg'); ?>);
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  left: 15px;
}
.wt-pdfpro-outline-btn:hover{
  text-decoration: none;
  transform: translateY(2px);
  transition: all .2s ease;
  color: #007FFF;
}
</style>
<div class="wt-pdfpro-sidebar wf_gopro_block" style="margin-bottom: 1em;margin-top: 20px;">
  <div class="wt-pdfpro-header">
    <h2 style="text-align: center;"><?php _e('Watch setup video','print-invoices-packing-slip-labels-for-woocommerce');?></h2>
    <iframe src="//www.youtube.com/embed/mg2Ad5L5Ds4" allowfullscreen="allowfullscreen" frameborder="0" align="middle" style="width:100%;margin-bottom: 1em;"></iframe>
    </div>
  </div>
<div class="wt-pdfpro-sidebar wf_gopro_block">
  <div class="wt-pdfpro-header">
    <div class="wt-pdfpro-name">
      <img src="<?php echo esc_url($wf_admin_img_path.'thumbnail.svg'); ?>" alt="featured img" width="36" height="36">
      <h4 class="wt-product-name"><?php echo __('WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels','print-invoices-packing-slip-labels-for-woocommerce'); ?></h4>
    </div>
    <div class="wt-pdfpro-mainfeatures">
      <ul>
        <li class="money-back"><?php echo __('30 Day Money Back Guarantee','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
        <li class="support"><?php echo __('Fast and Superior Support','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
      </ul>
      <div class="wt-pdfpro-btn-wrapper">
        <a href="https://www.webtoffee.com/product/woocommerce-pdf-invoices-packing-slips/?utm_source=free_plugin_sidebar&utm_medium=pdf_basic&utm_campaign=PDF_invoice&utm_content=2.8.3" class="wt-pdfpro-blue-btn" target="_blank"><?php echo __('UPGRADE TO PREMIUM','print-invoices-packing-slip-labels-for-woocommerce'); ?></a>
      </div>
    </div>
  </div>
  <div class="wt-pdfpro-features">
    <ul class="wt-pdfpro-newfeat">
      <li><?php echo __('Dynamic customizer for invoice','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
      <li><?php echo __('Enable ‘Pay Later’ checkout method and pay via invoice','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
    </ul>
    <ul class="wt-pdfpro-allfeat">
      <li><?php echo __('Generate picklist, credit notes, proforma invoice, & address labels','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
      <li><?php echo __('A variety of pre-built templates for each document','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
      <li><?php echo __('Advanced customization of templates using code-editor','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
      <li><?php echo __('Choose from various packing methods','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
      <li><?php echo __('Option to customize return policy','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
      <li><?php echo __('Supports checkout, order & product meta fields (SSN, VAT, etc.)','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
      <li><?php echo __('Verified compatibility with major plugins','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
      <li><?php echo __('Create custom sized labels','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
      <li><?php echo __('Premium add-on for cloud printing','print-invoices-packing-slip-labels-for-woocommerce'); ?></li>
    </ul>
    <div class="wt-pdfpro-btn-wrapper">
      <a href="https://www.webtoffee.com/product/woocommerce-pdf-invoices-packing-slips/?utm_source=free_plugin_sidebar&utm_medium=pdf_basic&utm_campaign=PDF_invoice&utm_content=2.8.3" class="wt-pdfpro-outline-btn" target="_blank"><?php echo __('UPGRADE TO PREMIUM','print-invoices-packing-slip-labels-for-woocommerce'); ?></a>
    </div>
  </div>
</div>
<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
$yellow_yes_icon='<span class="dashicons dashicons-yes-alt"></span>';
global $wp_version;
if(version_compare($wp_version, '5.2.0')<0)
{
    $yellow_yes_icon='<img src="'.plugin_dir_url(dirname(__FILE__)).'assets/images/tick_icon_yellow.png" style="float:left;" />&nbsp;';
}
?>
<style>
/* hide default sidebar */
.wf_gopro_block{ display:none; }

.wt_pklist_gopro_block{ background:#fff; display:none; float: left; height:auto; padding:15px; box-shadow: 0px 2px 2px #ccc; margin-top:64px; width:100%; border-top:solid 1px #ccc; box-sizing:border-box;}
.wt_pklist_gopro_block a:hover{ color:#fff; }
.wt_pklist_upgrade_to_premium{ background:#f8f9fa; border-radius:5px; padding:20px; }
.wt_pklist_upgrade_to_premium_head{ font-size:14px; font-weight:bold;}
.wt_pklist_upgrade_to_premium_ul{ list-style:none; }
.wt_pklist_upgrade_to_premium_ul .dashicons{ color:#ffbc02; font-size:14px; width:15px; line-height:18px; text-align:left; }
.wt_pklist_upgrade_to_premium_btn{ width:100%; display:block; text-align:center; font-weight:bold; padding:10px 0px; border-radius:5px; background:#0cc572; color:#fff; text-transform:uppercase; text-decoration:none;}
.wt_pklist_other_plugins_hd{ text-align:left; margin-top:10px; padding:15px 0px; font-size:12px; font-weight:bold; }

.wt_pklist_other_plugin_box{float:left; background:#f8f9fa; border-radius:5px; padding:10px 0px; margin-bottom:15px; }
.wt_pklist_other_plugin_hd{float:left; text-align:left; width:100%; height:auto; font-weight:600; font-size:14px; padding:0px 20px; box-sizing:border-box; }
.wt_pklist_other_plugin_con{float:left; text-align:left; width:100%; font-size:12px; padding:5px 20px; box-sizing:border-box; }
.wt_pklist_other_plugin_foot{float:left; text-align:left; width:100%; border-top: solid 1px #e7eaef; padding:10px 20px 0px 20px; box-sizing:border-box; }
.wt_pklist_other_plugin_hd img{ float:left; width:50px; height:50px; margin-right:5px; border-radius:5px;}
.wt_pklist_other_plugin_foot_install_btn{ float:left; padding:5px 15px; font-weight:bold; font-size:11px; border-radius:5px; color:#fff; text-align:center; background:#6987a6; text-decoration:none; }
.wt_pklist_other_plugin_foot_not_installed{ float:left; padding:5px 15px; font-size:10px; color:#989ca0; text-align:center; }

.wt_pklist_freevs_pro{ width:95%; border-collapse:collapse; border-spacing:0px; margin:2.5%; }
.wt_pklist_freevs_pro td{ border:solid 1px #e7eaef; text-align:left; vertical-align:middle; padding:15px 20px;}
.wt_pklist_freevs_pro tr td:first-child{ background:#f8f9fa; }
.wt_pklist_freevs_pro tr:first-child td{ font-weight:bold; }

.wt_pklist_upgrade_to_pro_bottom_banner{ float:left; display:none; width:100%; box-sizing:border-box; padding:35px; color:#ffffff; height:auto; background:#35678b; margin-top:20px;}
.wt_pklist_upgrade_to_pro_bottom_banner_hd{ float:left; width:60%; border-left:solid 5px #feb439; font-size:20px; font-weight:bold; padding-left:10px; line-height:28px; margin-top:10px;}
.wt_pklist_upgrade_to_pro_bottom_banner_btn{ background:#0cc572; border-radius:5px; color:#fff; text-decoration:none; font-size:16px; font-weight:bold; float:left; padding:20px 15px; margin-left:10px; margin-top:10px; }
.wt_pklist_upgrade_to_pro_bottom_banner_btn:hover{ color:#fff; }
.wt_pklist_upgrade_to_pro_bottom_banner_feature_list_main{ float:left; width:100%; margin-top:30px; }
.wt_pklist_upgrade_to_pro_bottom_banner_feature_list{ float:left; box-sizing:border-box; width:31%; margin-right:2%; padding:3px 0px 3px 20px; font-size:11px; color:#fff; background:url(<?php echo plugin_dir_url(dirname(__FILE__));?>assets/images/tick_icon.png) no-repeat left 5px; }
@media screen and (max-width:768px) {
  .wt_pklist_upgrade_to_pro_bottom_banner_feature_list{ width:100%; margin:auto; }
}
</style>
<div class="wt_pklist_gopro_block">
    <div class="wt_pklist_upgrade_to_premium">
        <div class="wt_pklist_upgrade_to_premium_head"><?php _e('Upgrade to premium', 'print-invoices-packing-slip-labels-for-woocommerce');?></div>
        <ul class="wt_pklist_upgrade_to_premium_ul">
            <li>
                <?php echo $yellow_yes_icon;?>
                <?php _e('30 day money back guarantee','print-invoices-packing-slip-labels-for-woocommerce'); ?>
            </li>
            <li>
                <?php echo $yellow_yes_icon;?>
                <?php _e('Fast and superior support','print-invoices-packing-slip-labels-for-woocommerce'); ?>
            </li>
            <li>
                <?php echo $yellow_yes_icon;?>
                <?php _e('Features that every site needs','print-invoices-packing-slip-labels-for-woocommerce'); ?>
            </li>
        </ul>
        <a href="https://www.webtoffee.com/product/woocommerce-pdf-invoices-packing-slips/?utm_source=free_plugin_comparison&utm_medium=pdf_basic&utm_campaign=PDF_invoice&utm_content=<?php echo WF_PKLIST_VERSION;?>" target="_blank" class="wt_pklist_upgrade_to_premium_btn"><?php _e('UPGRADE TO PREMIUM', 'print-invoices-packing-slip-labels-for-woocommerce'); ?></a>
    </div>
    <div class="wt_pklist_other_wt_plugins">
        
    </div>
</div>
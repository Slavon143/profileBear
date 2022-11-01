(function( $ ) {
	'use strict';

	$(function() {

		function wt_pklist_toggle_settings_sidebar(wt_tab_hash)
		{
			wt_tab_hash=wt_tab_hash.charAt(0)=='#' ? wt_tab_hash.substring(1) : wt_tab_hash;
			if(wt_tab_hash=='freevspro')
 			{
 				$('.wt_pklist_gopro_block, .wt_pklist_upgrade_to_pro_bottom_banner').show();
 				$('.wf_gopro_block').hide();
 			}else
 			{
 				$('.wt_pklist_gopro_block, .wt_pklist_upgrade_to_pro_bottom_banner').hide();
 				$('.wf_gopro_block').show();
 			}
		}

		var wt_nav_tab=$('.wf-tab-head .nav-tab');
	 	if(wt_nav_tab.length>0)
	 	{
	 		wt_nav_tab.click(function(){
	 			var wt_tab_hash=$(this).attr('href'); 			
	 			wt_pklist_toggle_settings_sidebar(wt_tab_hash);
	 		});

	 		if($('.wf-tab-head .nav-tab.nav-tab-active').length>0)
	 		{
	 			var current_hash=$('.wf-tab-head .nav-tab.nav-tab-active').attr('href');
	 			wt_pklist_toggle_settings_sidebar(current_hash);
	 		}

	 	}

	 	/* load other WT plugins info */
	 	if(jQuery('.wt_pklist_other_wt_plugins').length>0)
		{
			jQuery.ajax({
				url:wf_pklist_params.ajaxurl,
				type:'POST',
				data:{action:'wt_pklist_wt_other_pluigns', _wpnonce:wf_pklist_params.nonces.wf_packlist},
				success:function(data)
				{
					jQuery('.wt_pklist_other_wt_plugins').html(data);
				}
			});
		}
		
	});

})( jQuery );
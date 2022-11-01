(function( $ ) {
	//'use strict';
	$(function() {
		wf_invoice_update_order_status_to_email_select_box();
		$('#woocommerce_wf_generate_for_orderstatus_st').on('change',function(){
			wf_invoice_update_order_status_to_email_select_box();
		})

	});
	function wf_invoice_update_order_status_to_email_select_box()
	{
		var attch_inv_elm=$('#woocommerce_wf_attach_invoice_st');
		var attch_inv_vl=attch_inv_elm.val();
		attch_inv_vl=attch_inv_vl!==null ? attch_inv_vl : new Array();
		var html='';
		$('#woocommerce_wf_generate_for_orderstatus_st').find('option:selected').each(function(){
			var slcted=$.inArray($(this).val(),attch_inv_vl)==-1 ? '' : 'selected';
			html+='<option value="'+$(this).val()+'" '+slcted+'>'+$(this).html()+'</option>';
		});
		attch_inv_elm.html(html).trigger('change');
	}

	function wf_toggle_invoice_number_fields()
	{
		var vl=$('#woocommerce_wf_invoice_number_format').val();
		var prefix_tr=$('[name="woocommerce_wf_invoice_number_prefix"]').parents('tr');
		var postfix_tr=$('[name="woocommerce_wf_invoice_number_postfix"]').parents('tr');
		prefix_tr.hide().find('th label').css({'padding-left':'0px'});
		postfix_tr.hide().find('th label').css({'padding-left':'0px'});
		$('.form-table th label').css({'float':'left','width':'100%'});
		var num_reg=/\[number\]/gm;
		var pre_reg=/\[prefix\]/gm;
		var pos_reg=/\[suffix\]/gm;
		if(vl.search(pre_reg)>=0)
		{  
			prefix_tr.show().find('th label').animate({'padding-left':'15px'});
		}
		if(vl.search(pos_reg)>=0)
		{
			postfix_tr.show().find('th label').animate({'padding-left':'15px'});
		}
	}
	$('[name="woocommerce_wf_invoice_as_ordernumber"]').on('change',function(){
		if("Yes" === $(this).val()){
			$('#preview_invoice_number_text').show();
			$("#preview_invoice_number_text_custom").hide();
		}else{
			$("#preview_invoice_number_text_custom").show();
			$('#preview_invoice_number_text').hide();
		}
	});
	$('#woocommerce_wf_invoice_number_format').change(function(){
		wf_toggle_invoice_number_fields();
	});
	wf_toggle_invoice_number_fields();

	$("#invoice_start_number").on('input change',function(){
		$("#sample_current_invoice_number").val($(this).val());
	});

	$('.invoice_preview_assert').on('input change',function(){
		wf_do_invoice_number_preview();
	});

	
	wf_do_invoice_number_preview();
	function wf_do_invoice_number_preview(){
		var invoice_no = $("#sample_invoice_number").val();
		var number_ref=$('[name="woocommerce_wf_invoice_as_ordernumber"]:checked').val();
		var invoice_start_no = $('#sample_current_invoice_number').val();
		var number_format=$('#woocommerce_wf_invoice_number_format').val();
		var prefix_val =$('[name="woocommerce_wf_invoice_number_prefix"]').val();
		var postfix_val =$('[name="woocommerce_wf_invoice_number_postfix"]').val();
		var number_len = $('[name="woocommerce_wf_invoice_padding_number"]').val();

		if(number_ref == "No"){
			invoice_no = invoice_start_no;
		}

		if(number_format === "[prefix][number][suffix]"){
			invoice_no = prefix_val+invoice_no+postfix_val;
		}else if(number_format === "[prefix][number]"){
			invoice_no = prefix_val+invoice_no;
		}else if(number_format === "[number][suffix]"){
			invoice_no = invoice_no+postfix_val;
		}
		invoice_no = replace_date_val_invoice_number(invoice_no);
		/* length change calculation */
		var padded_invoice_number = "";
		var invoice_no_length = invoice_no.length;
		var padding_count = number_len - invoice_no_length;
		if (padding_count > 0) {
            for (var i = 0; i < padding_count; i++)
            {
                padded_invoice_number += '0';
            }
        }
		var invoice_no = padded_invoice_number+invoice_no;

		/* final preview */
		$("#preview_invoice_number").text(invoice_no);
	}

	function replace_date_val_invoice_number(invoice_no){
		const monthNames = ["January", "February", "March", "April", "May", "June",
		  "July", "August", "September", "October", "November", "December"
		];
		const monthShortNamescaps = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
		  "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
		];
		const daysShortNamescaps = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri",
		  "Sat"];
		var d = new Date();
		var full_year = d.getFullYear();
	    var short_year = full_year.toString().substr(-2);
		invoice_no = invoice_no.replace('[F]',monthNames[d.getMonth()]).replace('[dS]',d.getDate()+'th').replace('[M]',monthShortNamescaps[d.getMonth()]).replace('[m]',("0" + (d.getMonth()+1)).slice(-2)).replace('[d]',("0" + d.getDate()).slice(-2)).replace('[y]',short_year).replace('[Y]',full_year).replace('[D]',daysShortNamescaps[d.getDay()]).replace('[d/m/y]',("0" + d.getDate()).slice(-2)+'/'+("0" + (d.getMonth()+1)).slice(-2)+'/'+short_year).replace('[d-m-Y]',("0" + d.getDate()).slice(-2)+'-'+("0" + (d.getMonth()+1)).slice(-2)+'-'+full_year);
		return invoice_no;
	}

	$('.wf_inv_num_frmt_hlp_btn').click(function(){
		var trgt_field=$(this).attr('data-wf-trget');
		$('.wf_inv_num_frmt_hlp').attr('data-wf-trget',trgt_field);
		wf_popup.showPopup($('.wf_inv_num_frmt_hlp'));
	});

	$('.wf_inv_num_frmt_append_btn').click(function(){
		var trgt_elm_name=$(this).parents('.wf_inv_num_frmt_hlp').attr('data-wf-trget');
		var trgt_elm=$('[name="'+trgt_elm_name+'"]'); 
		var exst_vl=trgt_elm.val();
		var cr_vl=$(this).text();
		trgt_elm.val(exst_vl+cr_vl);
		wf_popup.hidePopup();
		wf_do_invoice_number_preview();
	});
	
})( jQuery );
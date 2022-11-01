/**
*
* JavaScript file that handles public side JS
*
*/

(function($){

	"use strict";

	$( document ).ready(function() {

		var isIndigo = $('#b2bking_indigo_order_form').val();
		var isCream = $('#b2bking_indigo_order_form.b2bking_cream_order_form').val();

		/* Fix for country selector SCROLL ISSUE in popup (e.g. login in Flatsome theme) */
		$('.b2bking_country_field_selector select').on('select2:open', function (e) {
	        const evt = "scroll.select2";
	        $(e.target).parents().off(evt);
	        $(window).off(evt);
	      });

		/* Conversations START */

		// On load conversation, scroll to conversation end
		// if conversation exists
		if ($('#b2bking_conversation_messages_container').length){
			$("#b2bking_conversation_messages_container").scrollTop($("#b2bking_conversation_messages_container")[0].scrollHeight);
		}

		// On clicking "Send message" inside conversation in My account
		$('#b2bking_conversation_message_submit').on('click', function(){
			// loader icon
			$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_myaccount_conversation_endpoint_button_icon');
			$('.b2bking_myaccount_conversation_endpoint_button_icon').remove();
			// Run ajax request
			var datavar = {
	            action: 'b2bkingconversationmessage',
	            security: b2bking_display_settings.security,
	            message: $('#b2bking_conversation_user_new_message').val(),
	            conversationid: $('#b2bking_conversation_id').val(),
	        };

			$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
				location.reload();
			});
		});

		// On clicking "New conversation" button
		$('#b2bking_myaccount_make_inquiry_button').on('click', function(){
			// hide make inquiry button
			$('#b2bking_myaccount_make_inquiry_button').css('display','none');
			// hide conversations
			$('.b2bking_myaccount_individual_conversation_container').css('display','none');
			// hide conversations pagination
			$('.b2bking_myaccount_conversations_pagination_container').css('display','none');
			// show new conversation panel
			$('.b2bking_myaccount_new_conversation_container').css('display','block');
		});

		// On clicking "Close X" button
		$('.b2bking_myaccount_new_conversation_close').on('click', function(){
			// hide new conversation panel
			$('.b2bking_myaccount_new_conversation_container').css('display','none');
			// show new conversation button
			$('#b2bking_myaccount_make_inquiry_button').css('display','inline-flex');
			// show conversations
			$('.b2bking_myaccount_individual_conversation_container').css('display','block');
			// show pagination
			$('.b2bking_myaccount_conversations_pagination_container').css('display','flex');
			
		});

		// On clicking "Send inquiry" button
		$('#b2bking_myaccount_send_inquiry_button').on('click', function(){
			// if textarea empty OR title empty
			if (!$.trim($("#b2bking_myaccount_textarea_conversation_start").val()) || !$.trim($("#b2bking_myaccount_title_conversation_start").val())) {
				// Show "Text area or title is empty" message
			} else {
				// loader icon
				$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_myaccount_start_conversation_button_icon');
				$('.b2bking_myaccount_start_conversation_button_icon').remove();
				// Run ajax request
				var datavar = {
		            action: 'b2bkingsendinquiry',
		            security: b2bking_display_settings.security,
		            message: $('#b2bking_myaccount_textarea_conversation_start').val(),
		            title: $('#b2bking_myaccount_title_conversation_start').val(),
		            type: $("#b2bking_myaccount_conversation_type").children("option:selected").val(),
		        };

		        // If DOKAN addon exists, pass vendor
		        if (typeof b2bkingdokan_display_settings !== 'undefined') {
		        	datavar.vendor = $('#b2bking_myaccount_conversation_vendor').val();
		        }

		        // If WCFM addon exists, pass vendor
		        if (typeof b2bkingwcfm_display_settings !== 'undefined') {
		        	datavar.vendor = $('#b2bking_myaccount_conversation_vendor').val();
		        }

		        // If MarketKing addon exists, pass vendor
		        if (typeof marketking_display_settings !== 'undefined') {
		        	datavar.vendor = $('#b2bking_myaccount_conversation_vendor').val();
		        }



				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					// redirect to conversation
					window.location = response;
				});
			}
		});

		/* Conversations END */

		/* Request a custom quote START*/

		// On clicking "Request a Custom Quote" button
		$('body').on('click', '#b2bking_request_custom_quote_button', function(){

			// If DOKAN addon exists
			if (typeof b2bkingdokan_display_settings !== 'undefined') {
				// check number of vendors
				var vendors = [];
				$('.variation dd.variation-Vendor').each(function(){
					let value = $(this).text().trim();
					if (value.length !== 0){
						if (!vendors.includes(value)){
							vendors.push(value);
						}
					}
				});
				var vendorsNr = vendors.length;
				if (parseInt(vendorsNr) > 1){
					alert(b2bkingdokan_display_settings.request_many_vendors);
					return;
				}
			}

			// If WCFM addon exists
			if (typeof b2bkingwcfm_display_settings !== 'undefined') {
				// check number of vendors
				var vendors = [];
				$('.variation dd.variation-Store').each(function(){
					let value = $(this).text().trim();
					if (value.length !== 0){
						if (!vendors.includes(value)){
							vendors.push(value);
						}
					}
				});

				if (vendors.length == 0){
					// try different structure
					$('.wcfm_dashboard_item_title').each(function(){
						let value = $(this).text().trim();
						if (value.length !== 0){
							if (!vendors.includes(value)){
								vendors.push(value);
							}
						}
					});
				}

				var vendorsNr = vendors.length;
				if (parseInt(vendorsNr) > 1){
					alert(b2bkingwcfm_display_settings.request_many_vendors);
					return;
				}
			}

			// If MarketKing addon exists
			if (typeof marketking_display_settings !== 'undefined') {
				// check number of vendors
				var vendorsNr = $('#marketking_number_vendors_cart').val();
				if (parseInt(vendorsNr) > 1){
					alert(marketking_display_settings.request_many_vendors);
					return;
				}
			}

			// show hidden elements above the button
			$('#b2bking_request_custom_quote_textarea, #b2bking_request_custom_quote_textarea_abovetext, .b2bking_custom_quote_field_container, .b2bking_request_custom_quote_text_label, #b2bking_request_custom_quote_name, #b2bking_request_custom_quote_email, .b2bking_custom_quote_field').css('display','block');
			// replace the button text with "Send custom quote request"
			$('#b2bking_request_custom_quote_button').text(b2bking_display_settings.send_quote_request);

			// On clicking "Send custom quote request"
			$('#b2bking_request_custom_quote_button').addClass('b2bking_send_custom_quote_button');
		});

		$('body').on('click', '.b2bking_send_custom_quote_button', function(){
		

			// if no fields are empty
			let empty = 'no';
			if ($('#b2bking_request_custom_quote_name').val() === '' || $('#b2bking_request_custom_quote_email').val() === ''){
				empty = 'yes';		
			}
			// check all custom fields
			var requiredids = jQuery('#b2bking_quote_required_ids').val();
			let requiredidssplit = requiredids.split(',');
			requiredidssplit.forEach(function(item){
				if ($('#b2bking_field_'+item).val() === ''){
					empty = 'yes';
				}
			});

			if (empty === 'no'){

				// validate email
				if (validateEmail($('#b2bking_request_custom_quote_email').val())){

					
					// run ajax request
					var quotetextids = jQuery('#b2bking_quote_text_ids').val();
					var quotecheckboxids = jQuery('#b2bking_quote_checkbox_ids').val();
					var quotefileids = jQuery('#b2bking_quote_file_ids').val();

					let quotetextidssplit = quotetextids.split(',');
					let quotecheckboxidssplit = quotecheckboxids.split(',');
					let quotefileidssplit = quotefileids.split(',');

					var datavar = {
			            action: 'b2bkingrequestquotecart',
			            security: b2bking_display_settings.security,
			            message: jQuery('#b2bking_request_custom_quote_textarea').val(),
			            name: jQuery('#b2bking_request_custom_quote_name').val(),
			            email: jQuery('#b2bking_request_custom_quote_email').val(),
			            title: b2bking_display_settings.custom_quote_request,
			            type: 'quote',
			        };

			        datavar.quotetextids = quotetextids;
			        datavar.quotecheckboxids = quotecheckboxids;
			        datavar.quotefileids = quotefileids;

			        quotetextidssplit.forEach(function(item){
			        	let id = 'b2bking_field_'+item;
			        	datavar[id] = jQuery('#b2bking_field_'+item).val();
			        });

			        quotecheckboxidssplit.forEach(function(item){
			        	let id = 'b2bking_field_'+item;
			        	let value = '';

			        	jQuery('#b2bking_field_'+item+':checked').each(function() {
			        	   value+=jQuery(this).parent().find('span').text()+', ';
			        	});
			        	value = value.slice(0, -2);

			        	datavar[id] = value;
			        });

			        if (quotefileids !== ''){
			        	// if there are files
			        	var nroffiles = parseInt(quotefileidssplit.length);
			        	var currentnr = 1;
			        	if (currentnr <= nroffiles){
			        		quotefileidssplit.forEach(function(item, index, array){

			        			let id = 'b2bking_field_'+item;
			        			var fd = new FormData();
			        			var file = jQuery('#b2bking_field_'+item);
			        			var individual_file = file[0].files[0];
			        			fd.append("file", individual_file);
			        			fd.append('action', 'b2bkingquoteupload'); 
			        			fd.append('security', b2bking_display_settings.security); 

			        			// disable button to prevent double-clicks
			        			quote_button_loader();


			        			jQuery.ajax({
			        			    type: 'POST',
			        			    url: b2bking_display_settings.ajaxurl,
			        			    data: fd,
			        			    contentType: false,
			        			    processData: false,
			        			    success: function(response){
			        			        datavar[id] = response;
			        			        if (currentnr === nroffiles){
			        			        	// it is the last file

			        			        	// If MARKETKING addon exists, pass vendor
			        			        	if (typeof marketking_display_settings !== 'undefined') {
			        			        		datavar.vendor = $('#marketking_cart_vendor').val();
			        			        	}

	        			        	        // If DOKAN addon exists, pass vendor
	        			        	        if (typeof b2bkingdokan_display_settings !== 'undefined') {
	        			        	        	var vendors = [];
	        			        	        	$('.variation dd.variation-Vendor').each(function(){
	        			        	        		let value = $(this).text();
	        			        	        		if (!vendors.includes(value)){
	        			        	        			vendors.push(value);
	        			        	        		}
	        			        	        	});
	        			        	        	datavar.vendor = vendors[0];
	        			        	        }

	        			        	        // If WCFM addon exists, pass vendor
	        			        	        if (typeof b2bkingwcfm_display_settings !== 'undefined') {
	        			        	        	var vendors = [];
	        			        	        	$('.variation dd.variation-Store').each(function(){
	        			        	        		let value = $(this).text();
	        			        	        		if (!vendors.includes(value)){
	        			        	        			vendors.push(value);
	        			        	        		}
	        			        	        	});
	        			        	        	datavar.vendor = vendors[0];
	        			        	        }
	        			        	        if (datavar.vendor === undefined){
	        			        	        	// if nothing yet, check additional structures
	        			        	        	var vendors2 = [];
	        			        	        	$('.wcfm_dashboard_item_title').each(function(){
	        			        	        		let value = $(this).text();
	        			        	        		if (!vendors2.includes(value)){
	        			        	        			vendors2.push(value);
	        			        	        		}
	        			        	        	});
	        			        	        	datavar.vendor = vendors2[0];
	        			        	        }
	        			        	        
	        			        			$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
	        			        				let conversationurl = response;

	        			        				// if user is logged in redirect to conversation, else show alert
	        			        				if($('#b2bking_request_custom_quote_name').length){
	        			        					alert(b2bking_display_settings.quote_request_success);
	        			        					$('#b2bking_request_custom_quote_button').css('display','none');
	        			        					location.reload();
	        			        				} else {
	        			        				    window.location = conversationurl;
	        			        				}
	        			        				
	        			        			});
			        			        }
			        			        currentnr++;
			        			    }
			        			});
			        		});

			        	}
			        } else {
			        	// no files

	        	        // If WCFM addon exists, pass vendor
	        	        if (typeof b2bkingwcfm_display_settings !== 'undefined') {
	        	        	var vendors = [];
	        	        	$('.variation dd.variation-Store').each(function(){
	        	        		let value = $(this).text();
	        	        		if (!vendors.includes(value)){
	        	        			vendors.push(value);
	        	        		}
	        	        	});
	        	        	datavar.vendor = vendors[0];
	        	        }

	        	        // if nothing yet, check additional structures
	        	        var vendors2 = [];
	        	        $('.wcfm_dashboard_item_title').each(function(){
	        	        	let value = $(this).text();
	        	        	if (!vendors2.includes(value)){
	        	        		vendors2.push(value);
	        	        	}
	        	        });
	        	        datavar.vendor = vendors2[0];

	        	        // If MARKETKING addon exists, pass vendor
			        	if (typeof marketking_display_settings !== 'undefined') {
			        		datavar.vendor = $('#marketking_cart_vendor').val();
			        	}

	        	        // If DOKAN addon exists, pass vendor
	        	        if (typeof b2bkingdokan_display_settings !== 'undefined') {
	        	        	var vendors = [];
	        	        	$('.variation dd.variation-Vendor').each(function(){
	        	        		let value = $(this).text();
	        	        		if (!vendors.includes(value)){
	        	        			vendors.push(value);
	        	        		}
	        	        	});
	        	        	datavar.vendor = vendors[0];
	        	        }

	        	        quote_button_loader();

	        			$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
	        				let conversationurl = response;

	        				// if user is logged in redirect to conversation, else show alert
	        				if($('#b2bking_request_custom_quote_name').length){
	        					alert(b2bking_display_settings.quote_request_success);
	        					$('#b2bking_request_custom_quote_button').css('display','none');
	        					location.reload();
	        				} else {
	        				    window.location = conversationurl;
	        				}
	        				
	        			});
			        }

					
				} else {
					alert(b2bking_display_settings.quote_request_invalid_email);
				}
				
			} else {
				alert(b2bking_display_settings.quote_request_empty_fields);
			}
		});

		function validateEmail(email) {
			if ($('#b2bking_request_custom_quote_email').val() !== undefined){
				var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
				return regex.test(email);
			} else {
				return true;
			}
		}

		function quote_button_loader(){
			jQuery('#b2bking_request_custom_quote_button').attr('disabled', true);
			jQuery('#b2bking_request_custom_quote_button').html(b2bking_display_settings.sending_please_wait);

			// potentially problematic on certain themes 
			/*
			// add loader
			var newbuttonhtml = '<img class="b2bking_loader_icon_button_quote" src="'+b2bking_display_settings.loadertransparenturl+'">'+b2bking_display_settings.sending_please_wait;
			jQuery('#b2bking_request_custom_quote_button').html(newbuttonhtml);

			// determine if loader icon color needs to be changed from white to black
			var textcolor = jQuery('#b2bking_request_custom_quote_button').css('color');
			textcolor = textcolor.split('(')[1].split(')')[0];

			var r = parseInt(textcolor.split(',')[0]);  // extract red
			var g = parseInt(textcolor.split(',')[1].trim());  // extract red
			var b = parseInt(textcolor.split(',')[2].trim());  // extract red

			var luma = 0.2126 * r + 0.7152 * g + 0.0722 * b; // per ITU-R BT.709

			if (luma < 55) {
			    // change color of loader to black
			    jQuery('.b2bking_loader_icon_button_quote').css('filter','invert(1)');
			}
			*/
		}

		/* Request a custom quote END*/

		/* Offers START*/

		// On clicking "add offer to cart"
		$('.b2bking_offer_add').on('click',function(){
			if (b2bking_display_settings.disableofferadd !== 1){
				let offerId = $(this).val();
				// replace icon with loader
				$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore($(this).find('.b2bking_myaccount_individual_offer_bottom_line_button_icon'));
				$(this).find('.b2bking_myaccount_individual_offer_bottom_line_button_icon').remove();

				// run ajax request
				var datavar = {
			            action: 'b2bkingaddoffer',
			            security: b2bking_display_settings.security,
			            offer: offerId,
			        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					// redirect to cart
					window.location = b2bking_display_settings.carturl;
				});
			}
		});
		
		// offer download
		$('.b2bking_offer_download').on('click',function(){
			var logoimg = b2bking_display_settings.offers_logo;
			var offernr = $(this).parent().parent().parent().find('#b2bking_offer_id').val();

			// if images are lazy-loaded, replace
			let logodatasrc = jQuery('#b2bking_img_logo').attr('data-src');
			if (logodatasrc !== undefined && logodatasrc !== ''){
				jQuery('#b2bking_img_logo').attr('src', logodatasrc);
			}

			jQuery('.b2bking_hidden_img').each(function(i){
				let logodatasrcth = jQuery(this).attr('data-src');
				if (logodatasrcth !== undefined && logodatasrcth !== ''){
					jQuery(this).attr('src', logodatasrcth);
				}
			});


			var imgToExport = document.getElementById('b2bking_img_logo');
			var canvas = document.createElement('canvas');
	        canvas.width = imgToExport.width; 
	        canvas.height = imgToExport.height; 
	        canvas.getContext('2d').drawImage(imgToExport, 0, 0);
	  		var dataURL = canvas.toDataURL("image/png"); 

	  		// get all thumbnails 
	  		var thumbnails = [];
	  		var thumbnr = 0;
	  		
	  		if (parseInt(b2bking_display_settings.offers_images_setting) === 1){
		  		// get field;
		  		let field = $(this).parent().parent().parent().find('.b2bking_offers_thumbnails_str').val();
		  		let itemsArray = field.split('|');
		  		// foreach condition, add condition, add new item
		  		itemsArray.forEach(function(item){
		  			if (item !== 'no'){
		  				var idimg = 'b2bking_img_logo'+thumbnr+offernr;
  						var imgToExport = document.getElementById(idimg);
  						var canvas = document.createElement('canvas');
  				        canvas.width = imgToExport.width; 
  				        canvas.height = imgToExport.height; 
  				        canvas.getContext('2d').drawImage(imgToExport, 0, 0);
  				  		let datau = canvas.toDataURL("image/png"); 
  				  		thumbnr++;
  				  		thumbnails.push(datau);
		  			} else {
		  				thumbnails.push('no');
		  			}
		  		});
		  	}

		  	thumbnr = 0;
			var customtext = $(this).parent().parent().parent().find('.b2bking_myaccount_individual_offer_custom_text').text();
			customtext = customtext.replace('\t','').trim();

			var customtextvendor = $(this).parent().parent().parent().find('.b2bking_myaccount_individual_offer_custom_text_vendor').text();
			customtextvendor = customtextvendor.replace('\t','').trim();


			var customtexttitle = b2bking_display_settings.offer_custom_text;
			if (customtext.length === 0 && customtextvendor.length === 0){
				customtexttitle = '';
			}

			
	

			var bodyarray = [];
			bodyarray.push([{ text: b2bking_display_settings.item_name, style: 'tableHeader', margin: [7, 7, 7, 7] }, { text: b2bking_display_settings.item_quantity, style: 'tableHeader', margin: [7, 7, 7, 7] }, { text: b2bking_display_settings.unit_price, style: 'tableHeader', margin: [7, 7, 7, 7] }, { text: b2bking_display_settings.item_subtotal, style: 'tableHeader', margin: [7, 7, 7, 7] }]);

			// get values
			jQuery(this).parent().parent().parent().find('.b2bking_myaccount_individual_offer_element_line').each(function(i){
				let tempvalues = [];

				if (parseInt(b2bking_display_settings.offers_images_setting) === 1){
					if (thumbnails[thumbnr] !== 'no'){
						// add name + images
						tempvalues.push([{ text: jQuery(this).find('.b2bking_myaccount_individual_offer_element_line_item_name').first().text(), margin: [7, 7, 7, 7] },{
								image: thumbnails[thumbnr],
								width: 40,
								margin: [15, 5, 5, 5]
							}]);
					} else {
						// add name only
						tempvalues.push({ text: jQuery(this).find('.b2bking_myaccount_individual_offer_element_line_item_name').first().text(), margin: [7, 7, 7, 7] });
					}
					thumbnr++;
				} else {
					// add name only
					tempvalues.push({ text: jQuery(this).find('.b2bking_myaccount_individual_offer_element_line_item_name').first().text(), margin: [7, 7, 7, 7] });
				}


				tempvalues.push({ text: jQuery(this).find('.b2bking_myaccount_individual_offer_element_line_item:nth-child(2)').text(), margin: [7, 7, 7, 7] });
				tempvalues.push({ text: jQuery(this).find('.b2bking_myaccount_individual_offer_element_line_item:nth-child(3)').text(), margin: [7, 7, 7, 7] });
				tempvalues.push({ text: jQuery(this).find('.b2bking_myaccount_individual_offer_element_line_item:nth-child(4)').text(), margin: [7, 7, 7, 7] });
				bodyarray.push(tempvalues);
			});



			bodyarray.push(['','',{ text: b2bking_display_settings.offer_total+': ', margin: [7, 7, 7, 7], bold: true },{ text: jQuery(this).parent().parent().parent().find('.b2bking_myaccount_individual_offer_bottom_line_total strong').text(), margin: [7, 7, 7, 7], bold: true }]);

			let imgobj = {
						image: dataURL,
						width: 150,
						margin: [0, 0, 0, 30]
					};


			var contentarray =[
					{ text: b2bking_display_settings.offer_details, fontSize: 14, bold: true, margin: [0, 20, 0, 20] },
					{
						style: 'tableExample',
						table: {
							headerRows: 1,
							widths: ['*', '*', '*', '*'],
							body: bodyarray,
						},
						layout: 'lightHorizontalLines'
					},
					{ text: b2bking_display_settings.offer_go_to, link: b2bking_display_settings.offers_endpoint_link, decoration: 'underline', fontSize: 13, bold: true, margin: [0, 20, 40, 8], alignment:'right' },
					{ text: customtexttitle, fontSize: 14, bold: true, margin: [0, 50, 0, 8] },
					{ text: customtextvendor, fontSize: 12, bold: false, margin: [0, 8, 0, 8] },
					{ text: customtext, fontSize: 12, bold: false, margin: [0, 8, 0, 8] },

				];

			if (logoimg.length !== 0){
				contentarray.unshift(imgobj);
			}

			
			var docDefinition = {
				content: contentarray
			};
			

			if(b2bking_display_settings.pdf_download_lang === 'thai'){

				pdfMake.fonts = {
				  THSarabunNew: {
				    normal: 'THSarabunNew.ttf',
				    bold: 'THSarabunNew-Bold.ttf',
				    italics: 'THSarabunNew-Italic.ttf',
				    bolditalics: 'THSarabunNew-BoldItalic.ttf'
				  }
				};

				docDefinition = {
				  content: contentarray,
				  defaultStyle: {
				    font: 'THSarabunNew'
				  }
				}
			}

			if(b2bking_display_settings.pdf_download_lang === 'japanese'){

				pdfMake.fonts = {
				  Noto: {
				    normal: 'Noto.ttf',
				    bold: 'Noto.ttf',
				    italics: 'Noto.ttf',
				    bolditalics: 'Noto.ttf'
				  }
				};

				docDefinition = {
				  content: contentarray,
				  defaultStyle: {
				    font: 'Noto'
				  }
				}
			}


			pdfMake.createPdf(docDefinition).download('offer.pdf');
		});
		

		/* Offers END */


		/* Custom Registration Fields START */
		// Dropdown
		addCountryRequired(); // woocommerce_form_field does not allow required for country, so we add it here
		// On load, show hide fields depending on dropdown option
		showHideRegistrationFields();

		$('.country_to_state').trigger('change');
		$('#b2bking_registration_roles_dropdown').change(showHideRegistrationFields);
		$('.b2bking_country_field_selector select').change(showHideRegistrationFields);
		$('select#billing_country').change(showHideRegistrationFields);
		function addCountryRequired(){
			$('.b2bking_country_field_req_required').prop('required','true');
			$('.b2bking_custom_field_req_required select').prop('required','true');
		}
		// on state change, reapply required
		$('body').on('DOMSubtreeModified', '#billing_state_field', function(){
			//let selectedValue = $('#b2bking_registration_roles_dropdown').val();
			//$('.b2bking_custom_registration_'+selectedValue+' #billing_state_field.b2bking_custom_field_req_required #billing_state').prop('required','true');
			//$('.b2bking_custom_registration_allroles #billing_state_field.b2bking_custom_field_req_required #billing_state').prop('required','true');
		});

		function showHideRegistrationFields(){

			// Hide all custom fields. Remove 'required' for hidden fields with required
			$('.b2bking_custom_registration_container').css('display','none');
			$('.b2bking_custom_field_req_required').removeAttr('required');
			$('.b2bking_custom_field_req_required select').removeAttr('required');
			$('.b2bking_custom_field_req_required #billing_state').removeAttr('required');
			
			// Show fields of all roles. Set required
			$('.b2bking_custom_registration_allroles').css('display','block');
			$('.b2bking_custom_registration_allroles .b2bking_custom_field_req_required').prop('required','true');
			$('.b2bking_custom_registration_allroles .b2bking_custom_field_req_required select').prop('required','true');
			setTimeout(function(){
				$('.b2bking_custom_registration_allroles .b2bking_custom_field_req_required #billing_state').prop('required','true');
	        },125);

			// Show all fields of the selected role. Set required
			let selectedValue = $('#b2bking_registration_roles_dropdown').val();
			$('.b2bking_custom_registration_'+selectedValue).css('display','block');
			$('.b2bking_custom_registration_'+selectedValue+' .b2bking_custom_field_req_required').prop('required','true');
			$('.b2bking_custom_registration_'+selectedValue+' .b2bking_custom_field_req_required select').prop('required','true');
			setTimeout(function(){
	        	$('.b2bking_custom_registration_'+selectedValue+' .b2bking_custom_field_req_required #billing_state').prop('required','true');
	        },225);

			// if there is more than 1 country
			if(parseInt(b2bking_display_settings.number_of_countries) !== 1){
				// check VAT available countries and selected country. If vat not available, remove vat and required
				let vatCountries = $('#b2bking_vat_number_registration_field_countries').val();
				let selectedCountry = $('.b2bking_country_field_selector select').val();
				if (selectedCountry === undefined){
					selectedCountry = $('select#billing_country').val();
				}
				if (vatCountries !== undefined){
					if ( (! (vatCountries.includes(selectedCountry))) || selectedCountry.trim().length === 0 ){
						// hide and remove required
						$('.b2bking_vat_number_registration_field_container').css('display','none');
						$('#b2bking_vat_number_registration_field').removeAttr('required');
					}
				}
			}

			// New for My Account VAT
			if (parseInt(b2bking_display_settings.myaccountloggedin) === 1){
				// check VAT countries
				let vatCountries = $('#b2bking_custom_billing_vat_countries_field input').prop('placeholder');
				let billingCountry = $('#billing_country').val();
				if (vatCountries !== undefined){
					if ( (! (vatCountries.includes(billingCountry))) || billingCountry.trim().length === 0){
						$('.b2bking_vat_field_container, #b2bking_checkout_registration_validate_vat_button').removeClass('b2bking_vat_visible, b2bking_vat_hidden').addClass('b2bking_vat_hidden');
						$('.b2bking_vat_field_required_1 input').removeAttr('required');
					} else {
						$('.b2bking_vat_field_container, #b2bking_checkout_registration_validate_vat_button').removeClass('b2bking_vat_visible, b2bking_vat_hidden').addClass('b2bking_vat_visible');
						$('.b2bking_vat_field_required_1 .optional').after('<abbr class="required" title="required">*</abbr>');
						$('.b2bking_vat_field_required_1 .optional').remove();
						$('.b2bking_vat_field_required_1 input').prop('required','true');
					}
				}
			}
			
		}

		// when billing country is changed , trigger update checkout. Seems to be a change in how WooCommerce refreshes the page. In order for this to work well with tax exemptions, run update checkout
		$('#billing_country').on('change', function() {
			setTimeout(function(){
				$(document.body).trigger("update_checkout");
			},1750);
		});
        jQuery('body').on('change', 'input[name="payment_method"]', function(){
        	if (parseInt(b2bking_display_settings.enable_payment_method_change_refresh) === 1){

	        	setTimeout(function(){
					jQuery(document.body).trigger("update_checkout");
				},250);
	        }
        });
		// Hook into updated checkout for WooCommerce
		$( document ).on( 'updated_checkout', function() {

		    // check VAT countries
		    let vatCountries = $('#b2bking_custom_billing_vat_countries_field input').val();
		    let billingCountry = $('#billing_country').val();
		    if (vatCountries !== undefined){
		    	if ( (! (vatCountries.includes(billingCountry))) || billingCountry.trim().length === 0){
		    		$('.b2bking_vat_field_container').removeClass('b2bking_vat_visible, b2bking_vat_hidden').addClass('b2bking_vat_hidden');
		    		$('.b2bking_vat_field_required_1 input').removeAttr('required');
		    	} else {
		    		$('.b2bking_vat_field_container').removeClass('b2bking_vat_visible, b2bking_vat_hidden').addClass('b2bking_vat_visible');
		    		$('.b2bking_vat_field_required_1 .optional').after('<abbr class="required" title="required">*</abbr>');
		    		$('.b2bking_vat_field_required_1 .optional').remove();
		    		$('.b2bking_vat_field_required_1 input').prop('required','true');
		    	}
		    }
		} );

		// VALIDATE VAT AT CHECKOUT REGISTRATION
		$('#b2bking_checkout_registration_validate_vat_button').on('click', function(){

			$('#b2bking_checkout_registration_validate_vat_button').text(b2bking_display_settings.validating);
			var vatnumber = $('#b2bking_vat_number_registration_field').val();
			if (vatnumber === undefined){
				vatnumber = $('.b2bking_vat_field_container input[type="text"]').val().trim();
			} else {
				vatnumber = $('#b2bking_vat_number_registration_field').val().trim();
			}
			
			var datavar = {
	            action: 'b2bkingvalidatevat',
	            security: b2bking_display_settings.security,
	            vat: vatnumber,
	            country: $('#billing_country').val(),
	        };

			$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
				if (response === 'valid'){
					createCookie('b2bking_validated_vat_status','validated_vat', false);
					createCookie('b2bking_validated_vat_number', vatnumber, false);
					$('#b2bking_vat_number_registration_field').prop('readonly', true);
					$('#b2bking_checkout_registration_validate_vat_button').prop('disabled', true);
					$('#b2bking_checkout_registration_validate_vat_button').text(b2bking_display_settings.vatvalid);
					// refresh checkout for prices
					$(document.body).trigger("update_checkout");
				} else if (response === 'invalid'){

					eraseCookie('b2bking_validated_vat_status');

					$('#b2bking_checkout_registration_validate_vat_button').text(b2bking_display_settings.vatinvalid);
				}
			});
		});

		function createCookie(name, value, days) {
		    var expires;

		    if (days) {
		        var date = new Date();
		        date.setTime(date.getTime() + (days * 24 * 60 * 60 * parseFloat(b2bking_display_settings.cookie_expiration_days)));
		        expires = "; expires=" + date.toGMTString();
		    } else {
		        expires = "";
		    }
		    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
		}

		function eraseCookie(name) {
		    createCookie(name, "", -1);
		}

		// if country is changed, re-run validation
		$('.woocommerce-checkout #billing_country').change(function(){
			eraseCookie('b2bking_validated_vat_status');
			$('#b2bking_checkout_registration_validate_vat_button').text(b2bking_display_settings.validatevat);
			$('#b2bking_vat_number_registration_field').prop('readonly', false);
			$('#b2bking_vat_number_registration_field').val('');
			$('#b2bking_checkout_registration_validate_vat_button').prop('disabled', false);
			// refresh checkout for prices
			$(document.body).trigger("update_checkout");
		});

		// Check if delivery country is different than shop country
		if (parseInt(b2bking_display_settings.differentdeliverycountrysetting) === 1){
			// if setting is enabled
			$('#shipping_country').change(exempt_vat_delivery_country);
		}
		function exempt_vat_delivery_country(){
			var datavar = {
	            action: 'b2bkingcheckdeliverycountryvat',
	            security: b2bking_display_settings.security,
	            deliverycountry: $('#shipping_country').val(),
	        };

			$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
				setTimeout(function(){
					$(document.body).trigger("update_checkout");
				}, 250);
			});
		}

		// add validation via JS to checkout
		jQuery(function($){
		    $('form.woocommerce-checkout').on( 'click', "#place_order", function(e){
		   		var invalid = 'no';
		        var fields = $(".b2bking_custom_field_req_required");
		        $.each(fields, function(i, field) {
			       	if ($(field).css('display') !== 'none' && $(field).parent().parent().css('display') !== 'none' && $(field).parent().css('display') !== 'none'){
			       		if (!field.value || field.type === 'checkbox'){
			       			let parent = $(field).parent();

			       			let text = parent.find('label').text().slice(0,-2);
			       			if (text === ''){
			       				let parent = $(field).parent().parent();
			       				let text = parent.find('label').text().slice(0,-2);
			       				alert(text + ' ' + b2bking_display_settings.is_required);
			       			} else {
			       				alert(text + ' ' + b2bking_display_settings.is_required);
			       			}
			       			invalid = 'yes';
			       		}
			       	}
		       }); 
		    	
		    	if (invalid === 'yes'){
		    		e.preventDefault();
		    		$('#b2bking_js_based_invalid').val('invalid');
		    	} else {
		    		$('#b2bking_js_based_invalid').val('0');
		    	}     	
   
		    });
		});

		// force select a country on registration
		$('button.woocommerce-form-register__submit').on('click',function(e){
			if ($('.b2bking_country_field_selector').parent().css('display') !== 'none'){
				if ($('.b2bking_country_field_selector select').val() === 'default'){
					e.preventDefault();
					alert(b2bking_display_settings.must_select_country);
				}
			}
		});




		/* Custom Registration Fields END */

		/* Subaccounts START */
		// On clicking 'New Subaccount'
		$('.b2bking_subaccounts_container_top_button').on('click',function(){
			// Hide subaccounts, show new subaccount
			$('.b2bking_subaccounts_new_account_container').css('display','block');
			$('.b2bking_subaccounts_account_container').css('display','none');
			$('.b2bking_subaccounts_container_top_button').css('display','none');
		});
		// On clicking 'Close X', reverse
		$('.b2bking_subaccounts_new_account_container_top_close').on('click',function(){
			$('.b2bking_subaccounts_new_account_container').css('display','none');
			$('.b2bking_subaccounts_account_container').css('display','block');
			$('.b2bking_subaccounts_container_top_button').css('display','inline-flex');
		});

		// On clicking "Create new subaccount"
		$('.b2bking_subaccounts_new_account_container_content_bottom_button').on('click',function(){
			// clear displayed validation errors
			$('.b2bking_subaccounts_new_account_container_content_bottom_validation_errors').html('');
			let validationErrors = '';
			// get username and email and password
			let username = 123;
			if (parseInt(b2bking_display_settings.disable_username_subaccounts) === 0){
				username = $('input[name="b2bking_subaccounts_new_account_username"]').val().trim();
			}
			let email = $('input[name="b2bking_subaccounts_new_account_email_address"]').val().trim();
			let password = $('input[name="b2bking_subaccounts_new_account_password"]').val().trim();

			if (parseInt(b2bking_display_settings.disable_username_subaccounts) === 0){
				// check against regex
				if (/^(?!.*[_.]$)(?=.{8,30}$)(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._-\d@]+$/.test(username) === false){
					validationErrors += b2bking_display_settings.newSubaccountUsernameError;
				}
			}

			if (/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(email) === false){
				validationErrors += b2bking_display_settings.newSubaccountEmailError;
			}
			if (/^(?=.*[A-Za-z])(?=.*[\d]).{8,}$/.test(password) === false){
				validationErrors += b2bking_display_settings.newSubaccountPasswordError;
			}

			if (validationErrors !== ''){
				// show errors
				$('.b2bking_subaccounts_new_account_container_content_bottom_validation_errors').html(validationErrors);
			} else {
				// proceed with AJAX account registration request

				// get all other data
				let name = $('input[name="b2bking_subaccounts_new_account_name"]').val().trim();
				let lastName = $('input[name="b2bking_subaccounts_new_account_last_name"]').val().trim();
				let jobTitle = $('input[name="b2bking_subaccounts_new_account_job_title"]').val().trim();
				let phone = $('input[name="b2bking_subaccounts_new_account_phone_number"]').val().trim();

				// checkboxes are true or false
				let checkboxBuy = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_buy"]').prop('checked'); 
				let checkboxBuyApproval = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_buy_approval"]').prop('checked'); 
				let checkboxViewOrders = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_orders"]').prop('checked');
				let checkboxViewOffers = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_offers"]').prop('checked');
				let checkboxViewConversations = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_conversations"]').prop('checked');
				let checkboxViewLists = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_lists"]').prop('checked');

				// replace icon with loader
				// store icon
				var buttonoriginal = $('.b2bking_subaccounts_new_account_container_content_bottom_button').html();
				$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_subaccounts_new_account_container_content_bottom_button_icon');
				$('.b2bking_subaccounts_new_account_container_content_bottom_button_icon').remove();

				// send AJAX account creation request
				var datavar = {
		            action: 'b2bking_create_subaccount',
		            security: b2bking_display_settings.security,
		            username: username,
		            password: password, 
		            name: name,
		            lastName: lastName,
		            jobTitle: jobTitle,
		            email: email,
		            phone: phone,
		            permissionBuy: checkboxBuy,
		            permissionBuyApproval: checkboxBuyApproval,
		            permissionViewOrders: checkboxViewOrders,
		            permissionViewOffers: checkboxViewOffers,
		            permissionViewConversations: checkboxViewConversations,
		            permissionViewLists: checkboxViewLists,
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					if (response.startsWith('error')){
						console.log(response);
						$('.b2bking_subaccounts_new_account_container_content_bottom_validation_errors').html(b2bking_display_settings.newSubaccountAccountError+': '+response);
						// hide loader, restore button
						$('.b2bking_subaccounts_new_account_container_content_bottom_button').html(buttonoriginal);
					} else if (response === 'error_maximum_subaccounts'){
						$('.b2bking_subaccounts_new_account_container_content_bottom_validation_errors').html(b2bking_display_settings.newSubaccountMaximumSubaccountsError);
						// hide loader, restore button
						$('.b2bking_subaccounts_new_account_container_content_bottom_button').html(buttonoriginal);
					} else {
						// go to subaccounts endpoint
						window.location = b2bking_display_settings.subaccountsurl;
					}
				});
			}
		});

		// On clicking "Update subaccount"
		$('.b2bking_subaccounts_edit_account_container_content_bottom_button').on('click',function(){
			// get details and permissions
			let subaccountId = $('.b2bking_subaccounts_edit_account_container_content_bottom_button').val().trim();
			let name = $('input[name="b2bking_subaccounts_new_account_name"]').val().trim();
			let lastName = $('input[name="b2bking_subaccounts_new_account_last_name"]').val().trim();
			let jobTitle = $('input[name="b2bking_subaccounts_new_account_job_title"]').val().trim();
			let phone = $('input[name="b2bking_subaccounts_new_account_phone_number"]').val().trim();

			// checkboxes are true or false
			let checkboxBuy = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_buy"]').prop('checked'); 
			let checkboxBuyApproval = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_buy_approval"]').prop('checked'); 
			let checkboxViewOrders = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_orders"]').prop('checked');
			let checkboxViewOffers = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_offers"]').prop('checked');
			let checkboxViewConversations = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_conversations"]').prop('checked');
			let checkboxViewLists = $('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_view_lists"]').prop('checked');

			// replace icon with loader
			$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_subaccounts_edit_account_container_content_bottom_button .b2bking_subaccounts_new_account_container_content_bottom_button_icon');
			$('.b2bking_subaccounts_edit_account_container_content_bottom_button .b2bking_subaccounts_new_account_container_content_bottom_button_icon').remove();

			// send AJAX account creation request
			var datavar = {
	            action: 'b2bking_update_subaccount',
	            security: b2bking_display_settings.security,
	            subaccountId: subaccountId,
	            name: name,
	            lastName: lastName,
	            jobTitle: jobTitle,
	            phone: phone,
	            permissionBuy: checkboxBuy,
	            permissionBuyApproval: checkboxBuyApproval,
	            permissionViewOrders: checkboxViewOrders,
	            permissionViewOffers: checkboxViewOffers,
	            permissionViewConversations: checkboxViewConversations,
	            permissionViewLists: checkboxViewLists,
	        };

	        $.post(b2bking_display_settings.ajaxurl, datavar, function(response){
				// go to subaccounts endpoint
				window.location = b2bking_display_settings.subaccountsurl;
			});
		});

		// on clicking close inside subaccount edit
		$('.b2bking_subaccounts_edit_account_container_top_close').on('click',function(){
			// go to subaccounts endpoint
			window.location = b2bking_display_settings.subaccountsurl;
		});

		// on clicking delete user, run same function as reject user
		$('.b2bking_subaccounts_edit_account_container_content_bottom_button_delete').on('click', function(){
			if (confirm(b2bking_display_settings.are_you_sure_delete)){
				// replace icon with loader
				$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_subaccounts_edit_account_container_content_bottom_button_delete .b2bking_subaccounts_new_account_container_content_bottom_button_icon');
				$('.b2bking_subaccounts_edit_account_container_content_bottom_button_delete .b2bking_subaccounts_new_account_container_content_bottom_button_icon').remove();

				var datavar = {
		            action: 'b2bkingrejectuser',
		            security: b2bking_display_settings.security,
		            user: $('.b2bking_subaccounts_edit_account_container_content_bottom_button').val().trim(),
		            issubaccount: 'yes',
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					// go to subaccounts endpoint
					window.location = b2bking_display_settings.subaccountsurl;
				});
			}
		});

		showHideApproval();
		$('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_buy').on('change', showHideApproval);
		function showHideApproval(){
			if($('input[name="b2bking_subaccounts_new_account_container_content_element_checkbox_buy').prop('checked')) {
		    	$('.b2bking_checkbox_permission_approval').css('display','flex');
		    } else {      
		    	$('.b2bking_checkbox_permission_approval').css('display','none');
		    }
		}	

		// click on approve / reject order
		$('#b2bking_approve_order').on('click', function(){

			let orderid = $('#b2bking_order_number').val();

			if (confirm(b2bking_display_settings.approve_order_confirm)){
				var datavar = {
		            action: 'b2bking_approve_order',
		            security: b2bking_display_settings.security,
		            orderid: orderid
		        };

		        console.log(datavar);


				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					location.reload();
				});
			}
					
		});

		$('#b2bking_reject_order').on('click', function(){
			let orderid = $('#b2bking_order_number').val();

			if (confirm(b2bking_display_settings.reject_order_confirm)){
				var datavar = {
		            action: 'b2bking_reject_order',
		            security: b2bking_display_settings.security,
		            orderid: orderid
		        };


				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					location.reload();
				});
			}
		});

		/* Subaccounts END */

		/* Bulk order form START */

		/* Disallow entering numbers directly based on min max */
		$('body').on('input', '.b2bking_bulkorder_form_container_content_line_qty_indigo', function(e){
		    let v = parseInt($(this).val());
		    let min = parseInt($(this).attr('min'));
		    let max = parseInt($(this).attr('max'));

		    if (v < min){
		        $(this).val(min);
		    } else if (v > max){
		        $(this).val(max);
		    }
		});

		// On clicking "new line", prepend newline to button container
		var pricetextvar = b2bking_display_settings.currency_symbol+'0';
		if (parseInt(b2bking_display_settings.accountingsubtotals) === 1){
			pricetextvar = b2bking_display_settings.price0;
		}
		if (parseInt(b2bking_display_settings.quotes_enabled) === 1){
			pricetextvar = b2bking_display_settings.quote_text;
		}

		$('.b2bking_bulkorder_form_container_newline_button').on('click', function() {
			// Clone template.
			var template = $('.b2bking_bulkorder_form_newline_template').html();
			template = template.replace('pricetext',pricetextvar);
			template = template.replace('display:none','display:initial');
			// add line
			$('.b2bking_bulkorder_form_container_newline_container').before(template);
		});

		// on click 'save list' in bulk order form
		
		$('.b2bking_bulkorder_form_container_bottom_save_button').on('click', function(){
			let title = window.prompt(b2bking_display_settings.save_list_name, "");

			if (title !== '' && title !== null){

				let productString = ''; 
				// loop through all bulk order form lines
				document.querySelectorAll('.b2bking_bulkorder_form_container_content_line_product').forEach(function(textinput) {
					var classList = $(textinput).attr('class').split(/\s+/);
					$.each(classList, function(index, item) {
						// foreach line if it has selected class, get selected product ID 
					    if (item.includes('b2bking_selected_product_id_')) {
					    	let productID = item.split('_')[4];
					    	let quantity = $(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_qty').val();
				    		if (quantity > 0 || parseInt(b2bking_display_settings.lists_zero_qty) === 1){
					    		// set product
					    		productString+=productID+':'+quantity+'|';
					    	}
					    }
					});
				});
				// if not empty, send
				if (productString !== ''){
					// replace icon with loader
					var buttonoriginal = $('.b2bking_bulkorder_form_container_bottom_save_button').html();
					$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_bulkorder_form_container_bottom_save_button_icon');
					$('.b2bking_bulkorder_form_container_bottom_save_button_icon').remove();

					// build pricelist to be saved
					let pricestringsend = '';
					Object.entries(prices).forEach(function (index) {
						let idstring = index[0];
						let price = index[1];
						let id = idstring.split('B2BKINGPRICE')[0];
						pricestringsend += id+':'+price+'|';
					});

					var datavar = {
			            action: 'b2bking_bulkorder_save_list',
			            security: b2bking_display_settings.security,
			            productstring: productString,
			            title: title,
			            pricelist: pricestringsend
			        };


					$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
						// restore button
						$('.b2bking_bulkorder_form_container_bottom_save_button').html(buttonoriginal);
						alert(b2bking_display_settings.list_saved);
					});
				} else {
					alert(b2bking_display_settings.list_empty);
				}	
			}
		});
		

		var latestSearchTime = Date.now();

		$('body').on('input', '.b2bking_bulkorder_form_container_content_line_qty', function(e){
			let val = $(this).val();
			if(val % 1 != 0){
				$(this).val(parseInt(val));
			}
		});

		$('body').on('input', '.b2bking_bulkorder_form_container_content_line_product', function(){
			let thisSearchTime = Date.now();
			latestSearchTime = thisSearchTime;
			let parent = $(this).parent();
			let inputValue = $(this).val();
			let searchbyval = $('#b2bking_bulkorder_searchby_select').val();
			if (typeof(searchbyval) === "undefined"){
				searchbyval = 'productname';
			}
			parent.find('.b2bking_bulkorder_form_container_content_line_livesearch').html('<img class="b2bking_loader_img" src="'+b2bking_display_settings.loaderurl+'">');
			parent.find('.b2bking_bulkorder_form_container_content_line_livesearch').css('display','block');

			var excludeval = $('#b2bking_bulkorder_exclude').val();
			var categoryval = $('#b2bking_bulkorder_category').val();
			var sortby = $('#b2bking_bulkorder_sortby').val();


			if (inputValue.length > 0){ // min x chars

				// set timer for 600ms before loading the ajax search (resource consuming)
				setTimeout(function(){

					// if in the last 2 seconds there's been no new searches or input
					if (thisSearchTime === latestSearchTime){
						// run search AJAX function 
						let formids = getIdsInForm();
						var datavar = {
				            action: 'b2bking_ajax_search',
				            security: b2bking_display_settings.security,
				            searchValue: inputValue,
				            exclude: excludeval,
				            category: categoryval,
				            sortby: sortby,
				            searchby: searchbyval,
				            idsinform: JSON.stringify(formids),
				            dataType: 'json'
				        };

						$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
							let display = '';
							let results = response;
							if (thisSearchTime === latestSearchTime){
								if (parseInt(results) !== 1234){ // 1234 Integer for Empty
									let resultsObject = JSON.parse(results);
									Object.keys(resultsObject).forEach(function (index) {
										if (index.includes('B2BKINGPRICE')){
											prices[index] = resultsObject[index];
										} else if (index.includes('B2BTIERPRICE')){
											pricetiers[index] = resultsObject[index];
										} else if (index.includes('B2BKINGSTOCK')){
											stock[index] = resultsObject[index];
										} else if (index.includes('B2BKINGIMAGE')){
											images[index] = resultsObject[index];
										} else {
											if (parseInt(b2bking_display_settings.bulkorderformimages) === 1){
												let img = index+'B2BKINGIMAGE';
												if (resultsObject[img] !== 'no' && resultsObject[img] !== '' && resultsObject[img] !== null){
													display += '<div class="b2bking_livesearch_product_result productid_'+index+'">'+resultsObject[index]+'<img class="b2bking_livesearch_image" src="'+resultsObject[img]+'"></div>';
												} else {
													display += '<div class="b2bking_livesearch_product_result productid_'+index+'">'+resultsObject[index]+'</div>';
												}
											} else {
												display += '<div class="b2bking_livesearch_product_result productid_'+index+'">'+resultsObject[index]+'</div>';
											}
											
										}
									});


								} else {
									display = b2bking_display_settings.no_products_found;
								}
								
								parent.find('.b2bking_bulkorder_form_container_content_line_livesearch').html(display);
							}
						});
					}
				}, 600);
				
			} else {
				parent.find('.b2bking_bulkorder_form_container_content_line_livesearch').css('display','none');
			}
		});

		var prices = Object;
		var stock = Object;
		var pricetiers = Object;
		var images = Object;
		var currentline;

		// let's populate prices initially from the html value
		let initialhtmlprices = $('#b2bking_initial_prices').val();
		if (initialhtmlprices !== undefined){
			let htmlprices = initialhtmlprices.split('|');
			htmlprices.forEach(function(textinput) {
				let idprice = textinput.split('-');
				if (idprice[0] !== ''){
					prices[idprice[0]+'B2BKINGPRICE'] = parseFloat(idprice[1]);
					pricetiers[idprice[0]+'B2BTIERPRICE'] = idprice[2];
					stock[idprice[0]+'B2BKINGSTOCK'] = parseInt(idprice[3]);
				}
				
			});
		}


		// on clicking on search result, set result in field
		$('body').on('click', '.b2bking_livesearch_product_result', function(){
			let title = $(this).text();
			let parent = $(this).parent().parent();
			currentline = parent;
			var classList = $(this).attr('class').split(/\s+/);
			$.each(classList, function(index, item) {
			    if (item.includes('productid')) {

			        let productID = item.split('_')[1];
	        		// set input disabled
			        parent.find('.b2bking_bulkorder_form_container_content_line_product').val(title);
			        parent.find('.b2bking_bulkorder_form_container_content_line_product').css('color', b2bking_display_settings.colorsetting );
			        parent.find('.b2bking_bulkorder_form_container_content_line_product').css('font-weight', 'bold');
			        parent.find('.b2bking_bulkorder_form_container_content_line_product').addClass('b2bking_selected_product_id_'+productID);
			        parent.find('.b2bking_bulkorder_form_container_content_line_product').after('<button class="b2bking_bulkorder_clear">'+b2bking_display_settings.clearx+'</button>');
			        parent.find('.b2bking_bulkorder_form_container_content_line_qty').val(1);

			        setTimeout(function(){
			        	parent.find('.b2bking_bulkorder_form_container_content_line_product').prop('disabled', true);
			        	parent.find('.b2bking_bulkorder_form_container_content_line_livesearch').css('display','none');
			        },125);

			        // Set max stock on item
			        if (stock[productID+'B2BKINGSTOCK'] !== null){
			        	parent.find('.b2bking_bulkorder_form_container_content_line_qty').attr('max', stock[productID+'B2BKINGSTOCK']);
			        }
			        
			       
			    }
			});
			if (parseInt(b2bking_display_settings.quotes_enabled) !== 1){
				calculateBulkOrderTotals();
			}
		});

		$('body').on('click', '.b2bking_bulkorder_clear', function(){
			let parent = $(this).parent();
			currentline = parent;
			let line = parent.find('.b2bking_bulkorder_form_container_content_line_product');
			let qty = parent.find('.b2bking_bulkorder_form_container_content_line_qty');
			line.prop('disabled', false);
			qty.removeAttr('max');
			line.removeAttr("style");
			line.val('');
			qty.val('');
			var classList = line.attr('class').split(/\s+/);
			$.each(classList, function(index, item) {
			    if (item.includes('b2bking_selected_product_id_')) {
			    	line.removeClass(item);
			    }
			});

			if (parseInt(b2bking_display_settings.quotes_enabled) !== 1){
				calculateBulkOrderTotals();
			}
			$(this).remove();

		});

		// on click add to cart
		$('.b2bking_bulkorder_form_container_bottom_add_button').on('click', function(){

			let productString = ''; 
			// loop through all bulk order form lines
			document.querySelectorAll('.b2bking_bulkorder_form_container_content_line_product').forEach(function(textinput) {
				var classList = $(textinput).attr('class').split(/\s+/);
				$.each(classList, function(index, item) {
					// foreach line if it has selected class, get selected product ID 
				    if (item.includes('b2bking_selected_product_id_')) {
				    	let productID = item.split('_')[4];
				    	let quantity = $(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_qty').val();
				    	if (quantity > 0){
				    		// set product
				    		productString+=productID+':'+quantity+'|';
				    	}
				    }
				});
			});
			// if not empty, send
			if (productString !== ''){
				// replace icon with loader
				$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_bulkorder_form_container_bottom_add_button_icon');
				$('.b2bking_bulkorder_form_container_bottom_add_button_icon').remove();
				var datavar = {
		            action: 'b2bking_bulkorder_add_cart',
		            security: b2bking_display_settings.security,
		            productstring: productString,
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					window.location = b2bking_display_settings.carturl;
				});
			}
		});

		// on product or quantity change, calculate totals
		$('body').on('input', '.b2bking_bulkorder_form_container_content_line_qty', function(){
			// enforce max (stock)
			var max = parseInt($(this).attr('max'));


			let textinput = $(this).parent().find('.b2bking_bulkorder_form_container_content_line_product');
			var productID = 0;
			var classList = $(textinput).attr('class').split(/\s+/);
			$.each(classList, function(index, item) {
				// foreach line if it has selected class, get selected product ID 
			    if (item.includes('b2bking_selected_product_id_')) {
			    	productID = item.split('_')[4];
			    }
			});

			let totalQuantity = $(this).val();
			var cartQuantity = 0;

			if (b2bking_display_settings.cart_quantities[productID] !== undefined){
				cartQuantity = parseInt(b2bking_display_settings.cart_quantities[productID]);
				totalQuantity = parseInt(totalQuantity) + cartQuantity;
			}

			if (parseInt(b2bking_display_settings.cart_quantities_cartqty) !== 0){
				cartQuantity = parseInt(b2bking_display_settings.cart_quantities_cartqty);
				totalQuantity = parseInt(totalQuantity) + cartQuantity;
			}
			
	        if (totalQuantity > max){
	            $(this).val((max-cartQuantity));

	            let parent = $(this).parent();
	            // get max stock message
	            let newval = b2bking_display_settings.max_items_stock;
	            newval = newval.replace('%s', max);

	            // if message is not set to max stock, set it
	            let currentval = parent.find('.b2bking_bulkorder_form_container_content_line_product').val();

	            if (currentval !== newval){
	            	let originalval = parent.find('.b2bking_bulkorder_form_container_content_line_product').val();
	            	let originalcolor = parent.find('.b2bking_bulkorder_form_container_content_line_product').css('color');
	            	
	            	parent.find('.b2bking_bulkorder_form_container_content_line_product').val(newval);
	            	parent.find('.b2bking_bulkorder_form_container_content_line_product').css('color','rgb(194 25 25)');
	            	setTimeout(function(){
	            		parent.find('.b2bking_bulkorder_form_container_content_line_product').val(originalval);
	            		parent.find('.b2bking_bulkorder_form_container_content_line_product').css('color',originalcolor);
	            	}, 1200);
	            }
	            
	        }

			currentline = $(this).parent();
			if (parseInt(b2bking_display_settings.quotes_enabled) !== 1){
				calculateBulkOrderTotals();
			}
		});

		function getIdsInForm(){
			var ids = [];

			// loop through all bulk order form lines
			document.querySelectorAll('.b2bking_bulkorder_form_container_content_line_product').forEach(function(textinput) {
				var classList = $(textinput).attr('class').split(/\s+/);
				$.each(classList, function(index, item) {
					// foreach line if it has selected class, get selected product ID 
				    if (item.includes('b2bking_selected_product_id_')) {
				    	let productID = item.split('_')[4];
				    	ids.push(productID);
				    }
				});
			});

			return ids;

		}

		

		function calculateBulkOrderTotals(){
			let total = 0;
			// loop through all bulk order form lines
			let textinput = currentline.find('.b2bking_bulkorder_form_container_content_line_product');


			var classList = $(textinput).attr('class').split(/\s+/);
			$.each(classList, function(index, item) {
				// foreach line if it has selected class, get selected product ID 
			    if (item.includes('b2bking_selected_product_id_')) {
			    	let productID = item.split('_')[4];
			    	let quantity = $(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_qty').val();
			    	if (quantity > 0){
	    				let index = productID + 'B2BKINGPRICE';
	    				let price = parseFloat(prices[index]);

	    				// find if there's tiered pricing
	    				let indexTiers = productID + 'B2BTIERPRICE';    				
	    				let tieredprice = pricetiers[indexTiers];
	    				// if have tiered price

	    				if (tieredprice !== 0){
	    					// get total quantity (form + cart)
	    					let totalQuantity = quantity;
	    					if (b2bking_display_settings.cart_quantities[productID] !== undefined){
	    						let cartQuantity = parseInt(b2bking_display_settings.cart_quantities[productID]);
	    						totalQuantity = parseInt(quantity) + cartQuantity;
	    					}

	    					if (parseInt(b2bking_display_settings.cart_quantities_cartqty) !== 0){
	    						totalQuantity = parseInt(totalQuantity) + parseInt(b2bking_display_settings.cart_quantities_cartqty);
	    					}

	    					// get all ranges
	    					let ranges = tieredprice.split(';');
	    					let quantities_array = [];
	    					let prices_array = [];
	    					// first eliminate all quantities larger than the total quantity
	    					$.each(ranges, function(index, item) {
	    						let tier_values = item.split(':');
	    						tier_values[0] = parseInt(tier_values[0]);
	    						tier_values[1] = parseFloat(tier_values[1]);

	    						if (tier_values[0] <= totalQuantity ){
	    							quantities_array.push(tier_values[0]);
	    							prices_array[tier_values[0]] = tier_values[1];
	    						}
	    					});
	    					
	    					if (quantities_array.length > 0){
	    						// continue and try to find price
	    						let largest = Math.max(...quantities_array);
	    						let finalpricetier = prices_array[largest];
	    						// only set it if the tier price is smaller than the group price
	    						if (price > finalpricetier){
	    							price = finalpricetier;
	    						}
	    					}
	    				}

	    				let subtotal = price * quantity;
	    				subtotal = parseFloat(subtotal.toFixed(2));
	    				setTimeout(function(){
	    					if (parseInt(b2bking_display_settings.accountingsubtotals) === 1){
	    						// get price html via WC PRICE
								var datavar = {
						            action: 'b2bking_accountingsubtotals',
						            security: b2bking_display_settings.security,
						            pricesent: subtotal,
						        };

								$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
									$(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_subtotal').html(response);
								});

	    					} else {
	    						$(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_subtotal').text(b2bking_display_settings.currency_symbol+subtotal);
	    					}
	    				}, 100);

			    	} else {
			    		$(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_subtotal').text(b2bking_display_settings.currency_symbol+0);
			    	}
			    } else {
			    	if (isIndigo === undefined){
				    	if ($(textinput).val() === ''){
				    		$(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_subtotal').text(b2bking_display_settings.currency_symbol+0);	
				    	}
				    }
			    }
			});

			

			document.querySelectorAll('.b2bking_bulkorder_form_container_content_line_product').forEach(function(textinput) {
				var classList = $(textinput).attr('class').split(/\s+/);
				$.each(classList, function(index, item) {
					// foreach line if it has selected class, get selected product ID 
				    if (item.includes('b2bking_selected_product_id_')) {
				    	let productID = item.split('_')[4];
				    	let quantity = $(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_qty').val();
				    	if (quantity > 0){
		    				let index = productID + 'B2BKINGPRICE';
		    				let price = parseFloat(prices[index]);


		    				// find if there's tiered pricing
		    				let indexTiers = productID + 'B2BTIERPRICE';
		    				let tieredprice = pricetiers[indexTiers];

		    				// if have tiered price
		    				if (tieredprice !== 0){
		    					// get total quantity (form + cart)
		    					let totalQuantity = quantity;
		    					if (b2bking_display_settings.cart_quantities[productID] !== undefined){
		    						let cartQuantity = parseInt(b2bking_display_settings.cart_quantities[productID]);
		    						totalQuantity = parseInt(quantity) + cartQuantity;
		    					}

		    					if (parseInt(b2bking_display_settings.cart_quantities_cartqty) !== 0){
		    						totalQuantity = parseInt(quantity) + parseInt(b2bking_display_settings.cart_quantities_cartqty);
		    					}

		    					// get all ranges
		    					let ranges = tieredprice.split(';');
		    					let quantities_array = [];
		    					let prices_array = [];
		    					// first eliminate all quantities larger than the total quantity
		    					$.each(ranges, function(index, item) {
		    						let tier_values = item.split(':');
		    						tier_values[0] = parseInt(tier_values[0]);
		    						tier_values[1] = parseFloat(tier_values[1]);

		    						if (tier_values[0] <= totalQuantity ){
		    							quantities_array.push(tier_values[0]);
		    							prices_array[tier_values[0]] = tier_values[1];
		    						}
		    					});
		    					
		    					if (quantities_array.length > 0){
		    						// continue and try to find price
		    						let largest = Math.max(...quantities_array);
		    						let finalpricetier = prices_array[largest];
		    						// only set it if the tier price is smaller than the group price
		    						if (price > finalpricetier){
		    							price = finalpricetier;
		    						}
		    					}
		    				}


		    				let subtotal = price * quantity;
		    				subtotal = parseFloat(subtotal.toFixed(2));

		    				total = total + subtotal;
		    				total = parseFloat(total.toFixed(2));
				    	}
				    }
				});

			});


			if (parseInt(b2bking_display_settings.accountingsubtotals) === 1){
				// get price html via WC PRICE
				var datavar = {
		            action: 'b2bking_accountingsubtotals',
		            security: b2bking_display_settings.security,
		            pricesent: total,
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					$('.b2bking_bulkorder_form_container_bottom_total .woocommerce-Price-amount').html(response);
				});

			} else {
				$('.b2bking_bulkorder_form_container_bottom_total .woocommerce-Price-amount').text(b2bking_display_settings.currency_symbol+total);	
			}

		}

		// if this is indigo order form
		if (isIndigo !== undefined){
			// add "selected" style to list items
			// get pricing details that will allow to calculate subtotals
			document.querySelectorAll('.b2bking_bulkorder_form_container_content_line_product').forEach(function(textinput) {
				let inputValue = $(textinput).val().split(' (')[0];
				var datavar = {
		            action: 'b2bking_ajax_search',
		            security: b2bking_display_settings.security,
		            searchValue: inputValue,
		            searchType: 'purchaseListLoading',
		            dataType: 'json'
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					let results = response;
					if (results !== '"empty"'){
						let resultsObject = JSON.parse(results);
						Object.keys(resultsObject).forEach(function (index) {
							if (index.includes('B2BKINGPRICE')){
								prices[index] = resultsObject[index];
							} else if (index.includes('B2BTIERPRICE')){
								pricetiers[index] = resultsObject[index];
							} else if (index.includes('B2BKINGSTOCK')){
								stock[index] = resultsObject[index];
							}
						});
					}
				});
				var productID = 0;
				var classList = $(textinput).attr('class').split(/\s+/);
				$.each(classList, function(index, item) {
				    if (item.includes('b2bking_selected_product_id_')) {
				    	productID = item.split('_')[4];
				    }
				});

				// Set max stock on item
				if (stock[productID+'B2BKINGSTOCK'] !== null){
					$(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_qty').attr('max', stock[productID+'B2BKINGSTOCK']);
				}

				currentline = $(textinput).parent();
				if (parseInt(b2bking_display_settings.quotes_enabled) !== 1){
					calculateBulkOrderTotals();
				}
				
			});

			// add to cart button
			$('body').on('click', '.b2bking_bulkorder_indigo_add', function(){

				// loader icon
				// if does not have none_in_stock class
				let thisbutton = $(this);

				if (!$(this).hasClass('b2bking_none_in_stock')){
					$(this).html('<img class="b2bking_loader_icon_button_indigo" src="'+b2bking_display_settings.loadertransparenturl+'">');
				}

				let textinput = $(this).parent().parent().find('.b2bking_bulkorder_form_container_content_line_product');
				var productID = 0;
				var classList = $(textinput).attr('class').split(/\s+/);
				$.each(classList, function(index, item) {
					// foreach line if it has selected class, get selected product ID 
				    if (item.includes('b2bking_selected_product_id_')) {
				    	productID = item.split('_')[4];
				    }
				});
				let qty = $(this).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').val();


				// run stock quantity addable check			
				var datavar = {
		            action: 'b2bking_get_stock_quantity_addable',
		            security: b2bking_display_settings.security,
		            id: productID,
		        };

		        var qtyaddable = 9999999;
				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					qtyaddable = parseInt(response);

					// if quantity addable is higher (or equal) than quantity requested, proceed
					if (qtyaddable >= qty && qty !== 0){
						var datavar = {
				            action: 'b2bking_bulkorder_add_cart_item',
				            security: b2bking_display_settings.security,
				            productid: productID,
				            productqty: qty,
				        };

						$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
							if (response === 'success'){
								$(thisbutton).removeClass('b2bking_low_in_stock');
								$(thisbutton).removeClass('b2bking_none_in_stock');

								// Refresh cart fragments
								$( document.body ).trigger( 'wc_fragment_refresh' );

								let newqtyaddable = qtyaddable-qty;
		
								if (newqtyaddable > 0){
									// set button to 'Add more'
									$(thisbutton).html(b2bking_display_settings.add_more_indigo);

									// if quantity left is lower than the quantity set in qty, lower qty to the qty left
									if (newqtyaddable < qty){
										$(thisbutton).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').val(newqtyaddable);
									}
								} else {
									// 0 left in stock, permanently grey button
									$(thisbutton).addClass('b2bking_none_in_stock');
									$(thisbutton).html('0 ' + b2bking_display_settings.left_in_stock);
									$(thisbutton).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').val(0);
									$(thisbutton).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').attr('max', 0);

								}
								
								
							}
						});
					} else if (qtyaddable === 0){
						// 0 left in stock, permanently grey button
						$(thisbutton).addClass('b2bking_none_in_stock');

						$(thisbutton).html('0 ' + b2bking_display_settings.left_in_stock);
						$(thisbutton).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').val(0);
						$(thisbutton).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').attr('max', 0);


					} else {
						// x left in stock, temporarily grey button
						$(thisbutton).html(b2bking_display_settings.left_in_stock_low_left + qtyaddable + b2bking_display_settings.left_in_stock_low_right);
						$(thisbutton).addClass('b2bking_low_in_stock');
						// set qty of the item to the qty left
						$(thisbutton).parent().parent().find('.b2bking_bulkorder_form_container_content_line_qty').val(qtyaddable);

						// restore button to default
						setTimeout(function(){
							$(thisbutton).removeClass('b2bking_low_in_stock');
							$(thisbutton).html(b2bking_display_settings.add_to_cart);
						}, 2500);

					}
					
				});

				

			});

			// search indigo form
			$('#b2bking_bulkorder_search_text_indigo').not('.b2bking_bulkorder_search_text_cream').on('input', function(){

				let thisSearchTime = Date.now();
				latestSearchTime = thisSearchTime;

		        if ($('#b2bking_bulkorder_search_text_indigo').length > 0){ // min x chars

		        	// show loader
		        	$('.b2bking_bulkorder_form_container_content_indigo').html('<div class="b2bking_loader_indigo_content"><img class="b2bking_loader_icon_button_indigo" src="'+b2bking_display_settings.loadertransparenturl+'"></div>');


		        	// set timer for 600ms before loading the ajax search (resource consuming)
		        	setTimeout(function(){

		        		// if in the last 2 seconds there's been no new searches or input
		        		if (thisSearchTime === latestSearchTime){

		        			var excludeval = $('#b2bking_bulkorder_exclude').val();
		        			var categoryval = $('#b2bking_bulkorder_category').val();
		        			var sortby = $('#b2bking_bulkorder_sortby').val();


		        			var datavar = {
					            action: 'b2bking_ajax_search',
					            security: b2bking_display_settings.security,
					            searchValue: $('#b2bking_bulkorder_search_text_indigo').val(),
					            dataType: 'json',
					            theme: 'indigo',
					            exclude: excludeval,
					            sortby: sortby,
					            category: categoryval
					        };

							$.post(b2bking_display_settings.ajaxurl, datavar, function(response){

								// 1. populate data for prices
								let display = '';
								let results = response;
								let html = '';
								if (thisSearchTime === latestSearchTime){
									if (parseInt(results) !== 1234){ // 1234 Integer for Empty
										let resultsObject = JSON.parse(results);
										Object.keys(resultsObject).forEach(function (index) {
											if (index.includes('B2BKINGPRICE')){
												prices[index] = resultsObject[index];
											} else if (index.includes('B2BTIERPRICE')){
												pricetiers[index] = resultsObject[index];
											} else if (index.includes('B2BKINGSTOCK')){
												stock[index] = resultsObject[index];
											} else if (index.includes('B2BKINGIMAGE')){
												images[index] = resultsObject[index];
											} else if (index.includes('HTML')){
												html = resultsObject[index];
											}									
										});

										// 2. show html and products
										$('.b2bking_bulkorder_form_container_content_indigo').html(html);

									} else {
										// no products found

										$('.b2bking_bulkorder_form_container_content_indigo').html('<div class="b2bking_bulkorder_indigo_noproducts">'+b2bking_display_settings.no_products_found+'</div><div class="b2bking_bulkorder_form_container_bottom b2bking_bulkorder_form_container_bottom_indigo"></div>');

									}
									
									


								}
							});

						}

					}, 400);
		        }
			});

			// cream order form filters category
			$('.b2bking_orderform_filters').on('click', function(){
				if ($('.b2bking_bulkorder_form_cream_main_container').hasClass('b2bking_filters_open')){
					$('#b2bking_bulkorder_cream_filter_icon img').attr('src',b2bking_display_settings.filters);
					$('.b2bking_bulkorder_form_cream_main_container.b2bking_filters_open, .b2bking_bulkorder_form_container_cream_filters.b2bking_filters_open, .b2bking_bulkorder_form_cream_main_container_content.b2bking_filters_open').addClass('b2bking_filters_closed').removeClass('b2bking_filters_open');
				} else {
					$('#b2bking_bulkorder_cream_filter_icon img').attr('src',b2bking_display_settings.filters_close);
					$('.b2bking_bulkorder_form_cream_main_container.b2bking_filters_closed, .b2bking_bulkorder_form_container_cream_filters.b2bking_filters_closed, .b2bking_bulkorder_form_cream_main_container_content.b2bking_filters_closed').addClass('b2bking_filters_open').removeClass('b2bking_filters_closed');
				}
			});

			// on click filter category
			$('.b2bking_bulkorder_filters_list li').on('click', function(){

				// get and set category
				let cat = $(this).val();
				$('#b2bking_bulkorder_category').val(cat);
				jQuery('#b2bking_bulkorder_search_text_indigo').trigger('input');

				// underline selected item
				$('.b2bking_bulkorder_filters_list li').each(function (index) {
					$(this).css('text-decoration','none');
				});
				$(this).css('text-decoration','underline');

				// close filters
				// $('.b2bking_orderform_filters').click();
			});

			// on click filter sortby
			$('.b2bking_bulkorder_filters_list_sortby li').on('click', function(){

				// get and set category
				let sortby = $(this).attr('value');

				$('#b2bking_bulkorder_sortby').attr('value',sortby);
				jQuery('#b2bking_bulkorder_search_text_indigo').trigger('input');

				// underline selected item
				$('.b2bking_bulkorder_filters_list_sortby li').each(function (index) {
					$(this).css('text-decoration','none');
				});
				$(this).css('text-decoration','underline');

				// close filters
				// $('.b2bking_orderform_filters').click();
			});

			// search cream form
			$('#b2bking_bulkorder_search_text_indigo.b2bking_bulkorder_search_text_cream').on('input', function(){

				let thisSearchTime = Date.now();
				latestSearchTime = thisSearchTime;

		        if ($('#b2bking_bulkorder_search_text_indigo').length > 0){ // min x chars

		        	// show loader
		        	$('.b2bking_bulkorder_form_container_content_indigo').html('<div class="b2bking_loader_indigo_content b2bking_loader_cream_content"><img class="b2bking_loader_icon_button_indigo" src="'+b2bking_display_settings.loadertransparenturl+'"></div>');


		        	// set timer for 600ms before loading the ajax search (resource consuming)
		        	setTimeout(function(){

		        		// if in the last 2 seconds there's been no new searches or input
		        		if (thisSearchTime === latestSearchTime){

		        			var excludeval = $('#b2bking_bulkorder_exclude').val();
		        			var categoryval = $('#b2bking_bulkorder_category').val();
		        			var sortby = $('#b2bking_bulkorder_sortby').val();


		        			var datavar = {
					            action: 'b2bking_ajax_search',
					            security: b2bking_display_settings.security,
					            searchValue: $('#b2bking_bulkorder_search_text_indigo').val(),
					            dataType: 'json',
					            theme: 'cream',
					            exclude: excludeval,
					            category: categoryval,
					            sortby: sortby
					        };

							$.post(b2bking_display_settings.ajaxurl, datavar, function(response){

								// 1. populate data for prices
								let display = '';
								let results = response;
								let html = '';
								if (thisSearchTime === latestSearchTime){
									if (parseInt(results) !== 1234){ // 1234 Integer for Empty
										let resultsObject = JSON.parse(results);
										Object.keys(resultsObject).forEach(function (index) {
											if (index.includes('B2BKINGPRICE')){
												prices[index] = resultsObject[index];
											} else if (index.includes('B2BTIERPRICE')){
												pricetiers[index] = resultsObject[index];
											} else if (index.includes('B2BKINGSTOCK')){
												stock[index] = resultsObject[index];
											} else if (index.includes('B2BKINGIMAGE')){
												images[index] = resultsObject[index];
											} else if (index.includes('HTML')){
												html = resultsObject[index];
											}									
										});

										// 2. show html and products
										$('.b2bking_bulkorder_form_container_content_indigo').html(html);

									} else {
										// no products found

										$('.b2bking_bulkorder_form_container_content_indigo').html('<div class="b2bking_bulkorder_indigo_noproducts b2bking_bulkorder_cream_noproducts"><img class="b2bking_bulkorder_cream_noproducts_img" src="'+b2bking_display_settings.no_products_found_img+'"><div class="b2bking_cream_noproductsfound_text">'+b2bking_display_settings.no_products_found+'</div></div><div class="b2bking_bulkorder_form_container_bottom b2bking_bulkorder_form_container_bottom_indigo b2bking_bulkorder_form_container_bottom_cream"></div>');

									}
									
									


								}
							});

						}

					}, 400);
		        }
			});

		}

		$('body').on('click', '.b2bking_bulkorder_back_top', function(){
			$("html, body").animate({ scrollTop: 0 }, "slow");
		});

		// trigger first search
		jQuery('#b2bking_bulkorder_search_text_indigo').trigger('input');
		

		/* Bulk order form END */

		/* Purchase Lists START */

		// Download Purchase Lists
		// On clicking download price list
		$('.b2bking_download_list_button').on('click', function() {
			// get list id
			var classList = $(this).attr('class').split(/\s+/);
			$.each(classList, function(index, item) {
				// foreach line if it has selected class, get selected product ID 
			    if (item.includes('id_')) {
			    	let listid = item.split('_')[1];
			    	window.location = b2bking_display_settings.ajaxurl + '?action=b2bkingdownloadpurchaselist&list='+listid+'&security=' + b2bking_display_settings.security;
			    }
			});
	    });

		// purchase lists data table
		if (typeof $('#b2bking_purchase_lists_table').DataTable === "function") { 
			$('#b2bking_purchase_lists_table').dataTable({
	            "language": {
	                "url": b2bking_display_settings.datatables_folder+b2bking_display_settings.purchase_lists_language_option+'.json'
	            }
	        });
		}

		// on click 'trash' in purchase list
		$('.b2bking_bulkorder_form_container_bottom_delete_button').on('click', function(){
			if(confirm(b2bking_display_settings.are_you_sure_delete_list)){
				let listId = $(this).val();

				// replace icon with loader
				$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_bulkorder_form_container_bottom_delete_button_icon');
				$('.b2bking_bulkorder_form_container_bottom_delete_button_icon').remove();

				var datavar = {
		            action: 'b2bking_purchase_list_delete',
		            security: b2bking_display_settings.security,
		            listid: listId
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					window.location = b2bking_display_settings.purchaselistsurl;
				});
			}
		});

		
		// on click 'update' in purchase list
		$('.b2bking_bulkorder_form_container_bottom_update_button').on('click', function(){
			let listId = $(this).val();

			let productString = ''; 
			// loop through all bulk order form lines
			document.querySelectorAll('.b2bking_bulkorder_form_container_content_line_product').forEach(function(textinput) {
				var classList = $(textinput).attr('class').split(/\s+/);
				$.each(classList, function(index, item) {
					// foreach line if it has selected class, get selected product ID 
				    if (item.includes('b2bking_selected_product_id_')) {
				    	let productID = item.split('_')[4];
				    	let quantity = $(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_qty').val();
				    	if (quantity > 0 || parseInt(b2bking_display_settings.lists_zero_qty) === 1){
				    		// set product
				    		productString+=productID+':'+quantity+'|';
				    	}
				    }
				});
			});
			// if not empty, send
			if (productString !== ''){
				// replace icon with loader
				var buttonoriginal = $('.b2bking_bulkorder_form_container_bottom_update_button').html();
				$('<img class="b2bking_loader_icon_button" src="'+b2bking_display_settings.loadertransparenturl+'">').insertBefore('.b2bking_bulkorder_form_container_bottom_update_button_icon');
				$('.b2bking_bulkorder_form_container_bottom_update_button_icon').remove();

				var datavar = {
		            action: 'b2bking_purchase_list_update',
		            security: b2bking_display_settings.security,
		            productstring: productString,
		            listid: listId
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					location.reload();
				});
			}
		});


		// if this is a purchase list
		let isPurchaseList = $('#b2bking_purchase_list_page').val();
		if (isPurchaseList !== undefined){
			// add "selected" style to list items
			$('.b2bking_bulkorder_form_container_content_line_product').css('color', b2bking_display_settings.colorsetting);

			$('.b2bking_bulkorder_form_container_content_line_product').css('font-weight', 'bold' );
			// get pricing details that will allow to calculate subtotals
			document.querySelectorAll('.b2bking_bulkorder_form_container_content_line_product').forEach(function(textinput) {
				let inputValue = $(textinput).val().split(' (')[0];
				var datavar = {
		            action: 'b2bking_ajax_search',
		            security: b2bking_display_settings.security,
		            searchValue: inputValue,
		            searchType: 'purchaseListLoading',
		            dataType: 'json'
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					let results = response;
					if (results !== '"empty"'){
						let resultsObject = JSON.parse(results);
						Object.keys(resultsObject).forEach(function (index) {
							if (index.includes('B2BKINGPRICE')){
								prices[index] = resultsObject[index];
							} else if (index.includes('B2BTIERPRICE')){
								pricetiers[index] = resultsObject[index];
							} else if (index.includes('B2BKINGSTOCK')){
								stock[index] = resultsObject[index];
							}
						});
					}

					var productID = 0;
					var classList = $(textinput).attr('class').split(/\s+/);
					$.each(classList, function(index, item) {
					    if (item.includes('b2bking_selected_product_id_')) {
					    	productID = item.split('_')[4];
					    }
					});

					// Set max stock on item
					if (stock[productID+'B2BKINGSTOCK'] !== null){
						$(textinput).parent().find('.b2bking_bulkorder_form_container_content_line_qty').attr('max', stock[productID+'B2BKINGSTOCK']);
					}

					currentline = $(textinput).parent();
					if (parseInt(b2bking_display_settings.quotes_enabled) !== 1){
						calculateBulkOrderTotals();
					}
				});
				
				
			});

		}
		

		$('body').on('click', '.b2bking_add_cart_to_purchase_list_button', function(){

			let title = window.prompt(b2bking_display_settings.save_list_name, "");
			if (title !== '' && title !== null){

				var datavar = {
		            action: 'b2bking_save_cart_to_purchase_list',
		            security: b2bking_display_settings.security,
		            title: title,
		            dataType: 'json'
		        };

				$.post(b2bking_display_settings.ajaxurl, datavar, function(response){
					$('.b2bking_add_cart_to_purchase_list_button').text(b2bking_display_settings.list_saved);
					$('.b2bking_add_cart_to_purchase_list_button').prop('disabled', true);
				});
			}
		});

		// Tiered Pricing Table Active Color Hover Script
		setTimeout(function(){
			if (parseInt(b2bking_display_settings.is_enabled_color_tiered) === 1){
				setHoverColorTable();
			}
		}, 200);

		$('body').on('input', 'input[name=quantity]', function(){
			if (parseInt(b2bking_display_settings.is_enabled_color_tiered) === 1){
				setHoverColorTable();
			}
		});
		$('body').on('change', 'input[name=quantity]', function(){
			if (parseInt(b2bking_display_settings.is_enabled_color_tiered) === 1){
				setHoverColorTable();
			}
		});

		$('body').on('change', 'select[name=quantity]', function(){
			if (parseInt(b2bking_display_settings.is_enabled_color_tiered) === 1){
				setHoverColorTable();
			}
		});
		$('body').on('change', '.variations select', function(){
			if (parseInt(b2bking_display_settings.is_enabled_color_tiered) === 1){
				setHoverColorTable();
			}
		});

		
		function setHoverColorTable(){
			// remove all colors from table
			$('.b2bking_has_color').removeClass('b2bking_has_color');
			// get product id from table
			if ($('.b2bking_shop_table').attr('class') !== undefined){
				var classList = $('.b2bking_shop_table').attr('class').split(/\s+/);
				var productid = 0;
				$.each(classList, function(index, item) {
					// foreach line if it has selected class, get selected product ID 
				    if (item.includes('b2bking_productid_')) {
				    	productid = parseInt(item.split('_')[2]);
				    }
				});
				// get input quantity
				if ($('input[name=quantity]').val() !== undefined){
					var inputQuantity = parseInt($('input[name=quantity]').val());
				} else if ($('select[name=quantity]').val() !== undefined){
					var inputQuantity = parseInt($('select[name=quantity]').val());
				}
				// get cart item quantity
				var cartQuantity = 0;
				if (parseInt(b2bking_display_settings.add_cart_quantity_tiered_table) === 1){

					if (b2bking_display_settings.cart_quantities[productid] !== undefined){
						cartQuantity = parseInt(b2bking_display_settings.cart_quantities[productid]);
					}
					if (parseInt(b2bking_display_settings.cart_quantities_cartqty) !== 0){
						cartQuantity = parseInt(b2bking_display_settings.cart_quantities_cartqty);
					}
				}

				// calculate total quantity of the item
				var totalQuantity = inputQuantity + cartQuantity;
				// go through all ranges and check quantity. 
				// first set it to original price
				$('.b2bking_tiered_active_price').text($('.summary .b2bking_tiered_range_replaced:first').text().split(' – ')[1]);
				// if can't be found,
				if ($('.summary .b2bking_tiered_range_replaced:first').text().split(' – ')[1] === undefined){
					$('.b2bking_tiered_active_price').text($('.b2bking_tiered_range_replaced:first').text().split(' – ')[1]);
				}

				$('.b2bking_shop_table.b2bking_productid_'+productid+' tr td:nth-child(1)').each(function(){
					let rangeText = $(this).data('range');
					let values = rangeText.split(' - ');

					if (values.length === 2){
						// is of form 123 - 456
						let first = parseInt(values[0]);
						let second = parseInt(values[1]);
						if (totalQuantity >= first && totalQuantity <= second){
							// set color
							$(this).parent().find('td').addClass('b2bking_has_color');
							if (parseInt(b2bking_display_settings.is_enabled_discount_table) === 1){
								$('.b2bking_tiered_active_price').text($(this).parent().find('td:nth-child(3)').text());
							} else {
								$('.b2bking_tiered_active_price').text($(this).parent().find('td:nth-child(2)').text());
							}
						}
					} else if (!rangeText.includes('+')){
						// exception if the user enters 1 as a quantity in the table
						if (totalQuantity === parseInt(rangeText)){
							$(this).parent().find('td').addClass('b2bking_has_color');
							if (parseInt(b2bking_display_settings.is_enabled_discount_table) === 1){
								$('.b2bking_tiered_active_price').text($(this).parent().find('td:nth-child(3)').text());
							} else {
								$('.b2bking_tiered_active_price').text($(this).parent().find('td:nth-child(2)').text());
							}
						}
					} else {
						// is of form 456+
						let valuePlus = parseInt(rangeText.split('+')[0]);
						if (totalQuantity >= valuePlus){
							// set color
							$(this).parent().find('td').addClass('b2bking_has_color');
							if (parseInt(b2bking_display_settings.is_enabled_discount_table) === 1){
								$('.b2bking_tiered_active_price').text($(this).parent().find('td:nth-child(3)').text());
							} else {
								$('.b2bking_tiered_active_price').text($(this).parent().find('td:nth-child(2)').text());
							}
						}
					}
				});
			}
		}


		//

		/* Purchase Lists END */

		/* Checkout Registration Fields Checkbox*/
		
		if (parseInt(b2bking_display_settings.ischeckout) === 1 && parseInt(b2bking_display_settings.validate_vat_checkout) !== 1){
			showHideCheckout();

			$('#createaccount').change(showHideCheckout);
		}

		function showHideCheckout(){
			if($('#createaccount').prop('checked') || typeof $('#createaccount').prop('checked') === 'undefined') {
		    	$('#b2bking_checkout_registration_main_container_fields, .b2bking_registration_roles_dropdown_section').css('display','block');
		    	$('.b2bking_custom_field_req_required').prop('required','true');

		    } else {      
		    	$('#b2bking_checkout_registration_main_container_fields, .b2bking_registration_roles_dropdown_section').css('display','none');
		    	$('.b2bking_custom_field_req_required').removeAttr('required');
		    }
		}	

		// Fix issue with tiered price range below pricing
		document.querySelectorAll('.b2bking_both_prices_price.b2bking_b2b_price_price').forEach(function(textinput) {

			var str = jQuery(textinput).val();
			if (str !== undefined){
				if (parseInt(str.length) === 0){
					var classList = $(textinput).attr('class').split(/\s+/);
					$.each(classList, function(index, item) {
						// foreach line if it has selected class, get selected product ID 
					    if (item.includes('b2bking_b2b_price_id_')) {
					    	let productID = item.split('_')[4];
					    	// if empty price, find tiered range below and move it inside
					    	var htm = jQuery('.b2bking_tiered_price_range_replaced_'+productID).html();
					    	jQuery('.b2bking_tiered_price_range_replaced_'+productID).remove();
					    	jQuery('.b2bking_both_prices_price.b2bking_b2b_price_price.b2bking_b2b_price_id_'+productID).html(htm);
					    }
					});
				}
			}
		});


		// Support Required Multiple Quantity Step for Individual Variations
		$('body').on('show_variation', '.single_variation_wrap', function ( event, variation ) {
			var quantity_input_var = jQuery( this ).parent().find( '[name=quantity]' );

			if (variation.step !== undefined){
				quantity_input_var.attr( 'step', variation.step ).trigger( 'change' );
			} else {
				// if no step, set it to 1
				quantity_input_var.attr( 'step', 1 ).trigger( 'change' );
			}

			// modify current value
			var qty_val = parseFloat( quantity_input_var.val() );

			if ( isNaN( qty_val ) ) {
				qty_val = variation.min_qty;
			} else {
				qty_val = qty_val > parseFloat( variation.max_qty ) ? variation.max_qty : qty_val;
				qty_val = qty_val < parseFloat( variation.min_qty ) ? variation.min_qty : qty_val;
			}

			if (variation.max_qty === undefined || variation.max_qty === '') {
				variation.max_qty = 999999999;
			}

			// if there is a step
			if (variation.step !== undefined){
				let difference = qty_val%variation.step;
				let difmin = 0;
				let difmax = 0; 

				// if current qty does not step
				if (parseInt(difference) !== 0) {

					// get difmin and difmax = numbers to substract or increase to reach step
					if ((qty_val - difference) % variation.step === 0){
						difmin = difference;
						difmax = variation.step - difmin;
					} else {
						difmax = difference;
						difmin = variation.step - difmax
					}

					// change it
					// if adding difference doesn't go over max, add it, else substract it
					qty_val = ((qty_val + difmax) < parseFloat( variation.max_qty )) ? qty_val+difmax : qty_val-difmin;
				}
			}

			// set val
			quantity_input_var.val(qty_val);


			/*
			var variation_id = variation.variation_id;
			var quantity_select_var = jQuery( this ).parent().find( '[name=quantity_pq_dropdown]' );
			var cur_val = quantity_input_var.val();

			if(variation_id > 0 && product_quantities[ variation_id ] !== undefined) {
				
				var quantity_dropdown_var = jQuery( this ).parent().find( 'select[name=quantity_pq_dropdown]' );
				var max_qty_var = ( ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) ) && parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) > 0 ) ? parseFloat( product_quantities[ variation_id ][ 'max_qty' ] ) : ''  );
				var min_qty_var = ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'min_qty' ] ) ) ? parseFloat( product_quantities[ variation_id ][ 'min_qty' ] ) : 1  );
				var default_var = ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'default' ] ) ) ? parseFloat( product_quantities[ variation_id ][ 'default' ] ) : 1  );
				var lowest_var = ( !isNaN (parseFloat( product_quantities[ variation_id ][ 'lowest_qty' ] ) ) ? parseFloat( product_quantities[ variation_id ][ 'lowest_qty' ] ) : 1  );
				
				if ( quantity_dropdown_var.length <= 0 ) {
					quantity_input_var.prop( 'max', max_qty_var );
				}
			}
			*/


		});
	});

})(jQuery);

(function($){

	"use strict";

	$( document ).ready(function() {

		/**
		* General Functions
		*/

		// Initialize SemanticUI Menu Functions

		// radio buttons
		$('.ui.checkbox').checkbox();

		// accordions
		$('.ui.accordion').accordion();

		// Logo Upload
		$('#b2bking-logo-upload-btn').on('click', function(e) {
	       e.preventDefault();

	       var image = wp.media({ 
	           title: 'Upload Image',
	           multiple: false
	       }).open()
	       .on('select', function(e){
	           // This will return the selected image from the Media Uploader, the result is an object
	           var uploaded_image = image.state().get('selection').first();
	           // Convert uploaded_image to a JSON object 
	           var b2bking_image_url = uploaded_image.toJSON().url;
	           // Assign the url value to the input field
	           $('#b2bking_offers_logo_setting').val(b2bking_image_url);
	       });
	   	});
   		// Offers IMG Upload
   		$('#b2bking-logoimg-upload-btn').on('click', function(e) {
   	       e.preventDefault();

   	       var image = wp.media({ 
   	           title: 'Upload Image',
   	           multiple: false
   	       }).open()
   	       .on('select', function(e){
   	           // This will return the selected image from the Media Uploader, the result is an object
   	           var uploaded_image = image.state().get('selection').first();
   	           // Convert uploaded_image to a JSON object 
   	           var b2bking_image_url = uploaded_image.toJSON().url;
   	           // Assign the url value to the input field
   	           $('#b2bking_offers_image_setting').val(b2bking_image_url);
   	       });
   	   	});
		
		// Tab transition effect
		var previous = $('.ui.tab.segment.active');
	    $(".menu .item").tab({
	        onVisible: function (e) {
	            var current = $('.ui.tab.segment.active');
	            // hide the current and show the previous, so that we can animate them
	            previous.show();
	            current.hide();

	            // hide the previous tab - once this is done, we can show the new one
	            previous.find('.b2bking_attached_content_wrapper').css('opacity','0');
	            current.find('.b2bking_attached_content_wrapper').css('opacity','0');
	            setTimeout(function(){
	            	previous.hide();
	            	current.show();
	            	setTimeout(function(){
		            	current.find('.b2bking_attached_content_wrapper').css('opacity','1');
		            	// remember the current tab for next change
		            	previous = current;
		            },10);
	            },150);
	            
	        }
	    });
	    
		$('.ui.dropdown').dropdown();
		$('.b2bking_purchase_lists_language_setting').dropdown('set selected', b2bking.purchase_lists_language_option);
	
		$('.message .close').on('click', function() {
		    $(this).closest('.message').transition('fade');
		});

		// hide or show force login
		hideShowForceLogin();

		// On Product Visibility option change, update product visibility options 
		$('input[name=b2bking_guest_access_restriction_setting]').change(function() {
			hideShowForceLogin();
		});

		// Checks the selected Product Visibility option and hides or shows Automatic / Manual visibility options
		function hideShowForceLogin(){
			let selectedValue = $("input[name=b2bking_guest_access_restriction_setting]:checked").val();
			if(selectedValue === "hide_website") {
		      	$("#b2bking_access_restriction_force_redirect").css("display","block");
		   	} else {
				$("#b2bking_access_restriction_force_redirect").css("display","none");
			}
		}

		// On Submit (Save Settings), Get Current Tab and Pass The Tab as a Setting. 
		$('#b2bking_admin_form').on('submit', function() {
			let tabInput = document.querySelector('#b2bking_current_tab_setting_input');
		    tabInput.value = document.querySelector('.active').dataset.tab;
		    return true; 
		});

		// check if license activation
		const urlParams = new URLSearchParams(window.location.search);
		const myParam = urlParams.get('tab');
		if (myParam === 'activate'){
			$('.b2bking_license').click();
		}

		

	});

})(jQuery);

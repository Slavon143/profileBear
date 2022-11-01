/**
*
* JavaScript file that controls global admin notices (enables permanent dismissal)
*
*/
(function($){

	"use strict";

	$( document ).ready(function() {

		/* Onboarding notice permanent dismissal */
		$('body').on('click', '.b2bking_dismiss_onboarding_notice button, #b2bking_dismiss_onboarding_link, #b2bking_save_settings_button, #b2bking-admin-submit', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'b2bking_dismiss_onboarding_admin_notice',
	            security: b2bking_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});

		});

		/* Admin notice permanent dismissal */
		$('body').on('click', '.b2bking_activate_woocommerce_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'b2bking_dismiss_activate_woocommerce_admin_notice',
	            security: b2bking_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});

		});

		$('body').on('click', '.b2bking_groupsrules_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'b2bking_dismiss_groupsrules_howto_admin_notice',
	            security: b2bking_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});

		});

		$('body').on('click', '.b2bking_quotefields_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'b2bking_dismiss_quotefields_howto_admin_notice',
	            security: b2bking_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});

		});

		$('body').on('click', '.b2bking_groups_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'b2bking_dismiss_groups_howto_admin_notice',
	            security: b2bking_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});

		});

		$('body').on('click', '.b2bking_conversations_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'b2bking_dismiss_conversations_howto_admin_notice',
	            security: b2bking_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});

		});

		$('body').on('click', '.b2bking_offers_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'b2bking_dismiss_offers_howto_admin_notice',
	            security: b2bking_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});

		});

		$('body').on('click', '.b2bking_rules_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'b2bking_dismiss_rules_howto_admin_notice',
	            security: b2bking_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});

		});

		$('body').on('click', '.b2bking_roles_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'b2bking_dismiss_roles_howto_admin_notice',
	            security: b2bking_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});

		});

		$('body').on('click', '.b2bking_fields_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'b2bking_dismiss_fields_howto_admin_notice',
	            security: b2bking_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});

		});

		$('body').on('click', '.b2bking_customers_howto_notice button', function(){
			// Run ajax function that permanently dismisses notice
			var datavar = {
	            action: 'b2bking_dismiss_customers_howto_admin_notice',
	            security: b2bking_notice.security,
	        };

			$.post(ajaxurl, datavar, function(response){
				// do nothing
			});

		});

	});

})(jQuery);
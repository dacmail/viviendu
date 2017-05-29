/*
Document   :  Modules Manager
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/
// Initialization and events code for the app
WooZoneModulesManager = (function ($) {
	"use strict";
	
	// public
	var debug_level = 0;
	var maincontainer = null;
	var mainloading = null;
	var lightbox = null;

	// init function, autoload
	(function init() {
		// load the triggers
		$(document).ready(function(){
			maincontainer = $("#WooZone");
			mainloading = $("#WooZone-main-loading");
			lightbox = $("#WooZone-lightbox-overlay");

			triggers();
		});
	})();
	
	function activate_bulk_rows( status ) {
		var ids = [], __ck = $('.WooZone-form .WooZone-table input.WooZone-item-checkbox:checked');
		__ck.each(function (k, v) {
			ids[k] = $(this).attr('name').replace('WooZone-item-checkbox-', '');
		});
		ids = ids.join(',');
  
 		if (ids.length<=0) {
			alert('You didn\'t select any rows!');
			return false;
		}
  
		mainloading.fadeIn('fast');

		jQuery.post(ajaxurl, {
			'action' 		: 'WooZoneModuleChangeStatus_bulk_rows',
			'id'			: ids,
			'the_status'		: status == 'activate' ? 'true' : 'false',
			'debug_level'		: debug_level
		}, function(response) {
			if( response.status == 'valid' ){
				mainloading.fadeOut('fast');

				//refresh page!
				window.location.reload();
				return false;
			}
			mainloading.fadeOut('fast');
			alert('Problems occured while trying to activate the selected modules!');
		}, 'json');
	}
	
	function triggers()
	{
		maincontainer.on('click', 'input#WooZone-item-check-all', function(){
			var that = $(this),
				checkboxes = $('.WooZone-table input.WooZone-item-checkbox');
				
			if( that.is(':checked') ){
				checkboxes.prop('checked', true);
			}
			else{
				checkboxes.prop('checked', false);
			}
		});

		maincontainer.on('click', '#WooZone-activate-selected', function(e){
			e.preventDefault();
  
			if ( confirm('Are you sure you want to activate the selected modules?') ) {
				activate_bulk_rows( 'activate' );
			}
		});
		
		maincontainer.on('click', '#WooZone-deactivate-selected', function(e){
			e.preventDefault();
  
			if ( confirm('Are you sure you want to deactivate the selected modules?') ) {
				activate_bulk_rows( 'deactivate' );
			}
		});
		
		//all checkboxes are checked by default!
		$('.WooZone-form .WooZone-table input.WooZone-item-checkbox').attr('checked', 'checked');

		if ( $('.WooZone-form .WooZone-table input.WooZone-item-checkbox:checked').length <= 0 ) {
			$('.WooZone-form .WooZone-table input#WooZone-item-check-all').css('display', 'none');
			$('.WooZone-form input#WooZone-activate-selected').css('display', 'none');
			$('.WooZone-form input#WooZone-deactivate-selected').css('display', 'none');
			$('.WooZone-list-table-left-col').css('display', 'none');
		}
		
	}

	// external usage
	return {
    	}
})(jQuery);

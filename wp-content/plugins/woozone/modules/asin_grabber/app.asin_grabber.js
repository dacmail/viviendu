/*
Document   :  Asset Download
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/

// Initialization and events code for the app
WooZoneASINGrabber = (function($) {
	"use strict";

	// public
	var debug_level = 0;
	var maincontainer = null;

	// init function, autoload
	(function init() {
		// load the triggers
		$(document).ready(function() {
			maincontainer = $(".WooZone-asin-grabber");
			triggers();
		});
	})();

	function add_to_queue(form) {
		$.post(ajaxurl, {
			'action': 'WooZoneLoadSection',
			'section': 'csv_products_import',
			'debug_level': debug_level
		}, function(response) {
			if (response.status == 'ok') {

				var response_html = $(response.html);
				response_html.attr('id', 'WooZone-import-queue-html');
				$("#WooZone-import-queue-html").remove();
				response_html.find("#WooZone-csv-asin").val(form.find("textarea").val());

				maincontainer.find(".WooZone-content").find(".WooZone-main").append(response_html);
				response_html.find("#WooZone-addASINtoQueue").click();
			}

			WooZone.to_ajax_loader_close();
		}, 'json');
	}

	function grabb_asins(form) {
		var nb_asins = form.find(".WooZone-number-of-results").val();

		if( nb_asins == 0 ){
			nb_asins = parseInt( $(".WooZone-custom-nr-pages").val() );
		}

		if( nb_asins == 0 ){
			alert("Please select a number of pages greater than 0!");
			return;
		}

		var original_url = form.find("input[name='WooZone[grabb-url]']").val();
		var cc = 1;

		function grab_asin_page(form, cc) 
		{
			//form.find("input[name='WooZone[grabb-url]']").val(original_url + "&pg=" + cc)
			form.find("input[name='WooZone[grabb-url]']").val(original_url)

			$.post(ajaxurl, {
				'action': 'WooZone_grabb_asins',
				'params': form.serialize(),
				'debug_level': debug_level
			}, function(response) {
				$("#WooZone-grabb-asins").find("#WooZone-grabb-error").remove();

				if (response.status == 'valid') {
					var old_value = maincontainer.find("#WooZone-asin-codes textarea").val();

					if (old_value != "") {
						old_value = old_value + "\n";
					}

					maincontainer.find("#WooZone-asin-codes textarea").val(old_value + response.asins.join('\n'));
					$("#WooZone-asin-codes").show();
				}

				if (response.status == 'invalid') {
					$("#WooZone-asin-codes").hide();

					$("#WooZone-grabb-asins").append("<div id='WooZone-grabb-error' class='WooZone-message WooZone-error'>" + ( response.msg ) + "</div>");
				}

				

				if ((cc * 1) < nb_asins) {
					cc++;

					grab_asin_page(form, cc);
				} else {
					form.find("input[name='WooZone[grabb-url]']").val(original_url);
					WooZone.to_ajax_loader_close();
				}

			}, 'json');
		}
		
		grab_asin_page(form, cc);

	}

	function triggers() {
		maincontainer.on("click", '#WooZone-grabb-asins #WooZone-grabb-button', function(e) {
			e.preventDefault();
			var that = $(this)
			WooZone.to_ajax_loader( "Grabbing Asins..." );
			grabb_asins(maincontainer.find('#WooZone-grabb-asins'));
		});

		maincontainer.on("click", '#WooZone-import-to-queue', function(e) {
			e.preventDefault();
			var that = $(this)
			WooZone.to_ajax_loader( "Importing to queue..." );
			add_to_queue(maincontainer.find('#WooZone-asin-codes'));
		});

		maincontainer.on("change", '.WooZone-number-of-results', function(e) {
			var that = $(this),
					val = that.val();

			if( val == 0 ){
				$(".WooZoneCustomNrPages").show();
			}else{
				$(".WooZoneCustomNrPages").hide();
			}
		});
		
	}

	// external usage
	return {
		//"asin_grabber": asin_grabber
	}
})(jQuery);
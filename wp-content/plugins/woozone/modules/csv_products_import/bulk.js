/*
Document   :  CSV Products Import
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/
// Initialization and events code for the app
WooZoneCSVProductsImport = (function ($) {
    "use strict";

	// init function, autoload
	(function init() {

		$(document).ready(function() {
 
			var WooZone_asins_arr = [];
		
			var ajax_error_message = '<div id="WooZone-error-popup">' +
		    							'<h2><i class="fa fa-exclamation-triangle"></i>Error 500: Internal server error occured!</h2>' + 
		    							'<p id="WooZone-ajax-error-reason-title"><b>Possible reasons are:</b></p>' + 
		    							'<ul id="WooZone-ajax-error-reasons">' +
		    								'<li><a target="_blank" href="http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP">Memory limit exceeded</a>(ask hosting provider to increase it)</li>' +
		    								'<li><a target="_blank" href="https://affiliate-program.amazon.com/gp/advertising/api/detail/faq.html">Amazon request throttled</a>(you made too many requests to Amazon in a short period of time)</li>' +
		    								'<li>Default install not loaded - please run "Install Default Config" from SETUP/BACKUP section</li>' +
		    							'</ul>' +
		    							'<a onClick="window.location.reload();" id="WooZone-refresh-button" href="#"><i class="fa fa-refresh"></i>RELOAD PAGE</a>' + 
		    						'</div>';
		
			var addASINtoQueue = function() {
				var asins_str = jQuery.trim(jQuery('#WooZone-csv-asin').val()),
					delimiter = jQuery("input[name=WooZone-csv-delimiter]:checked").attr('id').split('radio-'),
					delimiter = delimiter[1];
		
					if( delimiter == 'newline' ){
						delimiter = "\n";
					}else if ( delimiter == 'comma' ){
						delimiter = ",";
					}else{
						delimiter = "\t";
					}
		
				if(asins_str == ""){
					alert('Please first add some ASINs!');
					return false;
				}
		
		
				jQuery.each( asins_str.split( delimiter ), function(key, val) {
					if(jQuery.trim( val ) != ""){
						WooZone_asins_arr.push( jQuery.trim( val ) );
					}
				});
		
				if(WooZone_asins_arr.length > 0){
					printASINtoQueue();
				}else{
					alert('No ASIN can be added to Queue!');
				}
			};
		
			function fireAjaxError(jqXHR, textStatus){
				//console.log( jqXHR, textStatus  );
				if( jqXHR.status == 500 ) {
					jQuery('.WooZone-loader').hide(); 
					jQuery('#WooZone').append( ajax_error_message );
				}
			}
		
			var printASINtoQueue = function() {
				jQuery("#WooZone-no-ASIN").hide();
				jQuery("#WooZone-csvBulkImport-queue-response").show();
		
				var print = '';
				jQuery.each( WooZone_asins_arr, function(key, val) {
					print += '<tr>';
					print += 	'<td>' + ( val ) + '</td>';
					print += 	'<td id="WooZone-asin-' + key + '"><div class="WooZone-message WooZone-error" style="display:none;">Error:!</div><div class="WooZone-message WooZone-success" style="display:none;">Ready!</div><div class="WooZone-message WooZone-info">Ready for import</div></td>';
					print += '</tr>';
				});
		
				jQuery("#WooZone-print-response").html( print );
			};
		
			jQuery("a#WooZone-addASINtoQueue").die().on('click', function(e) {
				e.preventDefault();
				addASINtoQueue();
			});
		
			jQuery('body').on('click', 'a#WooZone-startImportASIN', function (e) {
				e.preventDefault();
				
				var numberOfItems = WooZone_asins_arr.length,
					loaded = 0,
					labelCurr = jQuery('#WooZone-status-ready'),
					labelTotal = jQuery('#WooZone-status-remaining');
		
				jQuery(this).hide();
				jQuery('.WooZone-status-block').show();
				// update totals
				labelCurr.text(loaded);
				labelTotal.text(numberOfItems);
		
				if(numberOfItems == 0) alert('Please first select some products from list!');
		
				var WooZone_insert_new_product = function(curr_step) {
		
					// stop if not valid WooZone_asins_arr
					if(typeof WooZone_asins_arr[curr_step] == 'undefined') return false;
		
					var data = {
						'action': 'WooZoneImportProduct',
						'asin':  WooZone_asins_arr[curr_step],
						'category': 'All',
						'to-category': $("#WooZone-to-category").val()
					};
		
					// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
					jQuery.ajax({
						url: ajaxurl,
						type: 'POST',
						dataType: 'json',
						data: data,
						success: function(response) {
							
							// stop import? demo keys allowed number of imports  
							if ( misc.hasOwnProperty(response, 'do_import')
								&& response.do_import != true ) {
		
								WooZone.verify_products_demo_keys();
								return false;
							}
		
							if( typeof response.status != 'undefined' && response.status == 'valid' ) {
								// show the download assets lightbox
		
								if( response.show_download_lightbox == true ) {
		
									$("#WooZone").append( response.download_lightbox_html );
								
									WooZoneAssetDownload.download_asset( $('.WooZone-images-tail').find('li').eq(0), undefined, 100, function(){
								
										$(".WooZone-asset-download-lightbox").remove();
										
										jQuery("#WooZone-asin-" + loaded).find('.WooZone-success').show();
										jQuery("#WooZone-asin-" + loaded).find('.WooZone-info').hide();
										++loaded;
				
										labelCurr.text(loaded);
										labelTotal.text(numberOfItems - loaded);
				
										// continue insert the rest of ASIN
										if(numberOfItems > curr_step) {
											WooZone_insert_new_product(++curr_step);
										}
				
										if( numberOfItems == curr_step){
											jQuery('.WooZone-status-block').html('<div class="WooZone-message WooZone-success">All products import successful! </div>');
										}
									} );
								} else {
								
									jQuery("#WooZone-asin-" + loaded).find('.WooZone-success').show();
									jQuery("#WooZone-asin-" + loaded).find('.WooZone-info').hide();
									++loaded;
			
									labelCurr.text(loaded);
									labelTotal.text(numberOfItems - loaded);
			
									// continue insert the rest of ASIN
									if(numberOfItems > curr_step) {
										WooZone_insert_new_product(++curr_step);
									}
			
									if( numberOfItems == curr_step){
										jQuery('.WooZone-status-block').html('<div class="WooZone-message WooZone-success">All products import successful! </div>');
									}
								}
		
							}else{
								
								var errMsg = '';
								if ( typeof response.status != 'undefined' )
									errMsg = response.msg;
								else
									errMsg = 'unknown error occured: could be related to max_execution_time, memory_limit server settings!';
		
								jQuery("#WooZone-asin-" + loaded).find('.WooZone-error').text("Error: " + errMsg);
								jQuery("#WooZone-asin-" + loaded).find('.WooZone-error').show();
								jQuery("#WooZone-asin-" + loaded).find('.WooZone-info').hide();
								++loaded;
		
								labelCurr.text(loaded);
								labelTotal.text(numberOfItems - loaded);
		
								// continue insert the rest of ASIN
								if(numberOfItems > curr_step) {
									WooZone_insert_new_product(++curr_step);
								}
							}
						},
						error: function( jqXHR, textStatus ) {
				  			fireAjaxError(jqXHR, textStatus);
						}
					});
				}
		
				// run for first
				if(numberOfItems > 0) WooZone_insert_new_product(0);
			});
		});

	})();
	
    // :: MISC
    var misc = {
    
        hasOwnProperty: function(obj, prop) {
            var proto = obj.__proto__ || obj.constructor.prototype;
            return (prop in obj) &&
            (!(prop in proto) || proto[prop] !== obj[prop]);
        },
    };
    
	// external usage
	return {
    }
})(jQuery);
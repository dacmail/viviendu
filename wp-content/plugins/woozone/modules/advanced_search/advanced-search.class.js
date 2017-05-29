/*
Document   :  Amazon Advanced Search
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/
// Initialization and events code for the app
WooZoneAdvancedSearch = (function ($) {
    "use strict";

    // public
    var ASINs = [];
    var loaded_products = 0;
    var debug_level = 0;
    var ajax_error_message = '<div id="WooZone-error-popup">' +
    							'<h2><i class="fa fa-exclamation-triangle"></i>Error 500: Internal server error occured!</h2>' + 
    							'<p id="WooZone-ajax-error-reason-title"><b>Possible reasons are:</b></p>' + 
    							'<ul id="WooZone-ajax-error-reasons">' +
    								'<li><a target="_blank" href="http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP">Memory limit exceeded</a>(ask hosting provider to increase it)</li>' +
    								'<li><a target="_blank" href="https://affiliate-program.amazon.com/gp/advertising/api/detail/faq.html">Amazon request throttled</a>(you made too many requests to Amazon in a short period of time)</li>' +
    								'<li>Default install not loaded - please run "Install Default Config" from SETUP/BACKUP section</li>' +
    							'</ul>' +
    							'<a onClick="window.location.reload();" id="WooZone-refresh-button" href="javascript:void(0)"><i class="fa fa-refresh"></i>RELOAD PAGE</a>' + 
    						'</div>';

	// init function, autoload
	(function init() {
		// init the tooltip
		tooltip();

		// load the triggers
		$(document).ready(function(){

			triggers();
 
			load_categ_parameters( $(".WooZone-categories-list li.on a") );

			// show debug hint
			console.log( '// want some debug?' );
			console.log( 'WooZoneAdvancedSearch.setDegubLevel(1);' );
		});
	})();

	function fireAjaxError(jqXHR, textStatus){
		//console.log( jqXHR, textStatus  );
		if( jqXHR.status == 500 ) {
			jQuery('.WooZone-loader').hide(); 
			jQuery('#WooZone').append( ajax_error_message );
		}
	}

	function load_categ_parameters( that )
	{
		WooZone.to_ajax_loader( "Loading category: " + that.data('categ') + " node_id (<i>" + that.data('nodeid') + "</i>)" );

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, {
			'action' 		: 'WooZoneCategParameters',
			'categ'			: that.data('categ'),
			'nodeid'		: that.data('nodeid'),
			'debug_level'	: debug_level
		}, function(response) {
			if( response.status == 'valid' ){
				$('#WooZone-parameters-container').html( response.html );

				// clear the products from right panel
				$(".WooZone-product-list").html('');
			}

			WooZone.to_ajax_loader_close();
		}, 'json').fail( function( jqXHR, textStatus ) {
  			fireAjaxError(jqXHR, textStatus);
		});
	}

	function updateExecutionQueue()
	{
		var queue_list = $("input.WooZone-items-select");
		if( queue_list.length > 0 ){
			$.each( queue_list, function(){
				var that = $(this),
					asin = that.val();

				if( that.is(':checked') ){
					// if not in global asins storage than push to array
					if( $.inArray( asin, ASINs) == -1 ){
						ASINs.push( asin );
					}
				}

				if( that.is(':checked') == false ){
					// if not in global asins storage than push to array
					if( $.inArray( asin, ASINs) > -1){
						// remove array key by value
						ASINs.splice( ASINs.indexOf(asin), 1 );
					}
				}
			});

		}else{
			// refresh the array list
			ASINs = [];
		}

		// update the queue list DOM
		if( ASINs.length > 0 ){
			var newHtml = [];
			$.each( ASINs, function( key, value ){
				var original_img = $("img#WooZone-item-img-" + value);

				if( original_img.length > 0 ){
					newHtml.push( '<a href="#' + ( value ) + '" class="removeFromQueue" title="Remove from Queue">' );
					newHtml.push( 	'<img src="' + ( original_img.attr('src') ) + '" width="30" height="30">' );
					newHtml.push( 	'<span></span>' );
					newHtml.push( '</a>' );
				}
			});

			// append the new html DOM elements to queue container
			$("#WooZone-execution-queue-list").html( newHtml.join( "\n" ) );
		}

		// clear the execution queue if not ASIN(s)
		else{
			$("#WooZone-execution-queue-list").html( 'No item(s) yet' );

			// uncheck "select all" if need
			if( jQuery("#WooZone-items-select-all").is(':checked') ){
				jQuery("#WooZone-items-select-all").removeAttr('checked');
			}
		}
	}

	function launchSearch( that, reset_page )
	{
		// get the current browse node
		var current_node = '';
		jQuery("#WooZoneGetChildrens select").each(function(){
		    var that_select = jQuery(this);

		    if( that_select.val() != "" ){
		        current_node = that_select.val();
		    }
		});

		var page = $("select#WooZone-page").val() > 0 ? parseInt($("select#WooZone-page").val(), 10) : 1;
		if( reset_page == true ){
			page = 1;
		}

		WooZone.to_ajax_loader( "Launch search on node_id: " + current_node + " keyword: " + that.find('[name="WooZoneParameter[Keywords]"]').val() + "" );

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(ajaxurl, {
			'action' 		: 'WooZoneLaunchSearch',
			'params'		: that.serialize(),
			'page'			: page,
			'node'			: current_node,
			'debug_level'	: debug_level
		}, function(response) {

			ASINs = [];
			$(".WooZone-product-list").html( response );
			$("#WooZone-items-select-all").click();
			WooZone.to_ajax_loader_close();
		}, 'html').fail( function( jqXHR, textStatus ) {
  			fireAjaxError(jqXHR, textStatus);
		});
	}

	function tailProductImport( import_step, callback )
	{
		//console.log( import_step ); 
		// stop if not valid ASINs key
		//if(typeof ASINs[import_step] == 'undefined') return false;
		if ( ASINs.length <=0 ) { // june 2016 fix
			// execute the callback at the end of loop
			callback( loaded_products );
			return false; 
		}

		//var asin = ASINs[import_step];
		var asin = ASINs.shift(); // june 2016 fix
		
		// increse the loaded products marker
		++loaded_products;
		
		// make the import
		jQuery.post( ajaxurl, {
			'action' 		: 'WooZoneImportProduct',
			'asin'			: asin,
			'category'		: $(".WooZone-categories-list li.on a").data('categ'),
			'to-category'	: $("#WooZone-to-category").val(),
			'debug_level'	: debug_level
		}, function(response) {
			
			// stop import? demo keys allowed number of imports  
			if ( misc.hasOwnProperty(response, 'do_import')
				&& response.do_import != true ) {

				WooZone.verify_products_demo_keys();
				return false;
			}

			if( typeof response.status != 'undefined' && response.status == 'valid' ) {
				
				var theMsg = '';
				if ( typeof response.status != 'undefined' )
					theMsg = response.msg;
				else
					theMsg = asin + ' was successfully imported!';

				// show the download assets lightbox
				if( response.show_download_lightbox == true ){
					$("#WooZone").append( response.download_lightbox_html );
					
					WooZoneAssetDownload.download_asset( $('.WooZone-images-tail').find('li').eq(0), undefined, 100, function(){
						
						$(".WooZone-asset-download-lightbox").remove();
				
						jQuery('a.removeFromQueue[href$="#' + ( asin ) + '"]').html( '<span class="success"></span>' );
						
						// fix june 2016
						jQuery('.WooZone-execution-queue').find('.WooZone-execution-queue-msg').append('<div>' + theMsg + '</div>');

						// continue insert the rest of ASINs
						//if( ASINs.length > import_step ) tailProductImport( ++import_step, callback );
						//if( ASINs.length > 0 ) {
							tailProductImport( ++import_step, callback ); // june 2016 fix
						//}
		
						// execute the callback at the end of loop
						//if( ASINs.length == import_step ){
						//	callback( loaded_products );
						//}
					} );
				}
				else{
					jQuery('a.removeFromQueue[href$="#' + ( asin ) + '"]').html( '<span class="success"></span>' );
					
					// fix june 2016
					jQuery('.WooZone-execution-queue').find('.WooZone-execution-queue-msg').append('<div>' + theMsg + '</div>');

					// continue insert the rest of ASINs
					//if( ASINs.length > import_step ) tailProductImport( ++import_step, callback );
					//if( ASINs.length > 0 ) {
						tailProductImport( ++import_step, callback ); // june 2016 fix
					//}
	
					// execute the callback at the end of loop
					//if( ASINs.length == import_step ){
					//	callback( loaded_products );
					//}
				}
			} else {
				// alert('Unable to import product: ' + asin );
				// return false;
				
				var errMsg = '';
				if ( typeof response.status != 'undefined' )
					errMsg = response.msg;
				else
					errMsg = 'unknown error occured: could be related to max_execution_time, memory_limit server settings!';
 
				jQuery('a.removeFromQueue[href$="#' + ( asin ) + '"]').html( '<span class="error"></span>' );
				
				// fix june 2016
				//jQuery('.WooZone-queue-table').find('tbody:last').append('<tr><td colspan=3>' + errMsg + '</td></tr>');
				jQuery('.WooZone-execution-queue').find('.WooZone-execution-queue-msg').append('<div>' + errMsg + '</div>');

				// continue insert the rest of ASINs
				//if( ASINs.length > import_step ) tailProductImport( ++import_step, callback );
				//if( ASINs.length > 0 ) {
					tailProductImport( ++import_step, callback ); // june 2016 fix
				//}
	
				// execute the callback at the end of loop
				//if( ASINs.length == import_step ){
				//	callback( loaded_products );
				//}
			}

		}, 'json').fail( function( jqXHR, textStatus ) {
  			fireAjaxError(jqXHR, textStatus);
		});
	}

	// public method
	function launchImport( that )
	{
		WooZone.to_ajax_loader( "Importing Products, Please Wait..." ); // Launch import
		
		if( ASINs.length == 0 ){
			alert( 'First please select products from the list!' );
			WooZone.to_ajax_loader_close();
			return false;
		}
  
		tailProductImport( 0, function( loaded_products ){
			jQuery('body').find('#WooZone-advanced-search .WooZone-items-list .on').remove();
			WooZone.to_ajax_loader_close();

			return true;
		});
	}

	function getChildNodes( that )
	{
		WooZone.to_ajax_loader( "Get Child Nodes" );

		// prev element valud
		var ascensor_value = that.val(),
			that_index = that.index();

		// max 3 deep
		if ( that_index > 10 ){
			WooZone.to_ajax_loader_close();
			return false;
		}

		var container = $('#WooZoneGetChildrens');
		var remove = false;
		// remove items prev of current selected
		container.find('select').each( function(i){
			if( remove == true ) $(this).remove();
			if( $(this).index() == that_index ){
				remove = true;
			}
		});

		// store current childrens into array
		if( ascensor_value != "" ){
			// make the import
			jQuery.post(ajaxurl, {
				'action' 		: 'WooZoneGetChildNodes',
				'ascensor'		: ascensor_value,
				'debug_level'	: debug_level
			}, function(response) {
				if( response.status == 'valid' ){
					$('#WooZoneGetChildrens').append( response.html );

					WooZone.to_ajax_loader_close();
				}
			}, 'json').fail( function( jqXHR, textStatus ) {
  			fireAjaxError(jqXHR, textStatus);
		});

		}else{
			WooZone.to_ajax_loader_close();
		}
	}

	function setDegubLevel( new_level )
	{
		debug_level = new_level;
		return "new debug level: " + debug_level;
	}

	function tooltip()
	{
		/* CONFIG */
		var xOffset = -40,
			yOffset = -250;

		/* END CONFIG */
		jQuery('body').on('mouseover', '.WooZone-tooltip', function (e) {
			var img_src = $(this).data('img');
			
			$("body").append("<img id='WooZone-tooltip' src="+ img_src +">");
			$("#WooZone-tooltip")
				.css("top",(e.pageY - xOffset) + "px")
				.css("left",(e.pageX + yOffset) + "px")
				.show();
	  	});
		
		jQuery('body').on('mouseout', '.WooZone-tooltip', function (e) {
			$("#WooZone-tooltip").remove();
	    });
		jQuery('body').on('mousemove', '.WooZone-tooltip', function (e) {
			$("#WooZone-tooltip")
				.css("top",(e.pageY - xOffset) + "px")
				.css("left",(e.pageX + yOffset) + "px");
		});
	}

	function triggers()
	{
		$("body").on('mousemove', '.WooZone-asset-download-lightbox', function( event ) {
			var pageCoords = "( " + event.pageX + ", " + event.pageY + " )";
			var clientCoords = "( " + event.clientX + ", " + event.clientY + " )";
			
			event.pageY = event.pageY - 550;

			$(this).find(".WooZone-donwload-in-progress-box").css( 'margin-top', event.pageY + 'px' );
		});
		
		jQuery('body').on('click', '.WooZone-categories-list a', function (e) {
			e.preventDefault();

			var that = $(this),
				that_p = that.parent('li');

			// escape if is the same block
			if( that.parent('li').hasClass('on') ) return true;

			// get current clicked category paramertes
			load_categ_parameters(that);

			$(".WooZone-categories-list li.on").removeClass('on');
			that_p.addClass('on');
		});
		
		jQuery('body').on('change', 'select.WooZoneParameter-sort', function (e) {
		    var that = $(this),
		        val = that.val(),
		        opt = that.find("[value=" + ( val ) + "]"),
		        desc = opt.data('desc');

		    $("p#WooZoneOrderDesc").html( "<strong>" + ( val ) + ":</strong> " + desc );
		});

		// check / uncheck all
		jQuery('body').on('change', '#WooZone-items-select-all', function (e)
		{
			var that = $(this),
				selectors = $("input.WooZone-items-select");

			if( that.is(':checked') == true){

				selectors.each(function(){
					var sub_that = $(this),
						tr_parent = sub_that.parents('tr').eq(0);
					sub_that.attr('checked', 'true');
					tr_parent.addClass('on');
				});
			}else{
				selectors.each(function(){
					var sub_that = $(this),
						tr_parent = sub_that.parents('tr').eq(0);
					sub_that.removeAttr('checked');
					tr_parent.removeClass('on');
				});
			}

			// update the execution queue
			updateExecutionQueue();
		})

		// temp
		.click();
		
		jQuery('body').on('change', 'input.WooZone-items-select', function (e)
		{
			var that = $(this),
				tr_parent = that.parents('tr').eq(0);
			if( that.is(':checked') == false){
				tr_parent.removeClass('on');
			}else{
				tr_parent.addClass('on');
			}

			// update the execution queue
			updateExecutionQueue();
		});
		
		jQuery('body').on('click', '#WooZone-advanced-search .WooZone-items-list tr td:not(:last-child, :first-child)', function (e)
		{
			var that = $(this),
				tr_parent = that.parent('tr'),
				input = tr_parent.find('input');
			input.click();
		});
		
		jQuery('body').on('click', '#WooZone-advanced-search a.removeFromQueue', function (e) 
		{
			e.preventDefault();
			
			var that = $(this),
				href = that.attr('href').replace("#", ''),
				tr_parent = $('tr#WooZone-item-row-' + href),
				input = tr_parent.find('input');

			input.click();
		});
		
		jQuery('body').on('submit', '#WooZone_import_panel', function (e) {
			e.preventDefault();

			launchSearch( $(this), true );
		});
		
		jQuery('body').on('change', 'select#WooZone-page', function (e) {
			e.preventDefault();

			launchSearch( $("#WooZone_import_panel"), false );
		});

		jQuery('body').on('click', 'a#WooZone-advance-import-btn', function (e) {
			e.preventDefault();

			launchImport();
		});
		
		jQuery('body').on('change', '#WooZoneGetChildrens select', function (e) {
			e.preventDefault();

			getChildNodes( $(this) );
		});
	}


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
		"setDegubLevel": setDegubLevel,
        "ASINs": ASINs,
        "launchImport": launchImport
    }
})(jQuery);
/*
Document   :  Dashboard
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/

// Initialization and events code for the app
WooZoneDashboard = (function ($) {
    "use strict";

    // public
    var debug = false;
    var maincontainer = null;

	// init function, autoload
	function init()
	{
		maincontainer = $("#WooZone-ajax-response");
		triggers();
	};
	
	function boxLoadAjaxContent( box )
	{
		var allAjaxActions = [];
		box.find('.is_ajax_content').each(function(key, value){
			
			var alias = $(value).text().replace( /\n/g, '').replace("{", "").replace("}", "");
			$(value).attr('id', 'WooZone-row-alias-' + alias);
			allAjaxActions.push( alias );
		}); 
		
		 
		jQuery.post(ajaxurl, {
			'action' 		: 'WooZoneDashboardRequest',
			'sub_actions'	: allAjaxActions.join(","),
			'prod_per_page'	: box.find(".WooZone-numer-items-in-top").val(),
			'debug'			: debug
		}, function(response) {
			$.each(response, function(key, value){
				if( value.status == 'valid' ){
					var row = box.find( "#WooZone-row-alias-" + key );

					row.html(value.html);
					
					row.removeClass('is_ajax_content');
					
					tooltip();

					if( key == 'products_performances' && typeof(value.data) != "undefined" && value.data !== null ){
						var count = {
						    from: 0,
						    to: value.data.nb_products,
						    speed: 1000,
						    refreshInterval: 50,
						    formatter: function (value, options) {
						      return value.toFixed(options.decimals);
						    }
						};
						maincontainer.find(".WooZone-ds-value-nb_products").countTo( count );
						count.to = value.data.total_addtocart;
						maincontainer.find(".WooZone-ds-value-total_addtocart").countTo( count );

						count.to = value.data.total_redirect_to_amazon;
						maincontainer.find(".WooZone-ds-value-amazon_redirect").countTo( count );

						count.to = value.data.total_hits;
						maincontainer.find(".WooZone-ds-value-total_hits").countTo( count );
					}
				}

				WooZone.to_ajax_loader_close();
			});
			
		}, 'json');
	}
	
	function tooltip()
	{
		var xOffset = -30,
			yOffset = -300,
			winW 	= $(window).width();
		
		$(".WooZone-aa-products-container ul li a").hover(function(e){
			
			var that = $(this),
				preview = that.data('preview');

			$("body").append("<p id='WooZone-aa-preview'>"+ ( '<img src="' + ( preview ) + '" >' ) +"</p>");
			
			var new_left = e.pageX + yOffset;
			
			if( new_left > (winW - 640) ){
				new_left = (winW - 640)
			}
			$("#WooZone-aa-preview")
				.css("top",(e.pageY - xOffset) + "px")
				.css("left",(new_left) + "px")
				.fadeIn("fast");
	    },
		function(){
			this.title = this.t;
			$("#WooZone-aa-preview").remove();
	    });
		
	
		$(".WooZone-aa-products-container ul li a").mousemove(function(e){
			
			var new_left = e.pageX + yOffset;
			if( new_left > (winW - 640) ){
				new_left = (winW - 640)
			}
			
			$("#WooZone-aa-preview")
				.css("top",(e.pageY - xOffset) + "px")
				.css("left",(new_left) + "px");
		});
	}

	
	function triggers()
	{
		maincontainer.find(">div").each( function(e){
			var that = $(this);
			// check if box has ajax content
			if( that.find('.is_ajax_content').size() > 0 ){
				boxLoadAjaxContent(that);
			}
		});
		
		maincontainer.find(".WooZone-numer-items-in-top").on('change', function(){
			var that = $(this),
				box = that.parents('.WooZone-dashboard-status-box').eq(0);

			box.find('.WooZone-dashboard-status-box-content').addClass('is_ajax_content').html('{products_performances}');
			
			WooZone.to_ajax_loader( "Loading.." );
			boxLoadAjaxContent(box);
		});
		
		$(".WooZone-aa-products-tabs").on('click', "li:not(.on) a", function(e){
			e.preventDefault();
			
			var that = $(this),
				alias = that.attr('class').split("items-"),
				alias = alias[1];
			
			$('.WooZone-aa-products-container').hide();
			$("#aa-prod-" + alias).show();
			
			$(".WooZone-aa-products-tabs").find("li.on").removeClass('on');
			that.parent('li').addClass('on');
		});
	}
	
	// external usage
	return {
		"init": init
    }
})(jQuery);

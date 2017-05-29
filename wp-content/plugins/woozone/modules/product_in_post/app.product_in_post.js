/*
Document   :  Asset Download
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/

// Initialization and events code for the app
WooZoneProductInPost = (function($) {
	"use strict";

	// public
	var debug_level = 0;
	var loading = $('<div id="WooZone-ajaxLoadingBox" class="WooZone-panel-widget">loading</div>'); // append loading
	var _editor = null;
	
	// init function, autoload
	(function init() {
		// load the triggers
		$(document).ready(function() {
			//triggers();
		});
		
		if( typeof ajaxurl != "undefined" ){
			addButton(); 
		}
	})();

	function addButton()
	{
		
		tinymce.PluginManager.add( 'product_in_post', function( editor, url ) {
	        // Add a button that opens a window
	        editor.addButton( 'product_in_post', {
	            text: 'Add Products',
	            icon: 'product_in_post',
	            onclick: function() {
	            	createLightbox( editor );
	            }
	        } );
	    });
	}
	
	function createLightbox( editor )
	{
		_editor = editor;
		tb_show( 'Amazon Product to post/page', '#TB_inline?inlineId=WooZoneAddProductInline' );
		//tb_position();
		tb_resize();
		triggers_load_products();
	}
	
	function tb_resize()
	{
		function resize(){
			var tbWindow = $('#TB_window'),
				tb_width = tbWindow.width(),
				tb_height = tbWindow.height();
			
			$('#TB_ajaxContent').css({
				'width': (tb_width - 40) + "px",
				'height': (tb_height - 50) + "px"
			});
			
			$(".WooZoneAllProducts").css({
				'height': (tb_height - 80) + "px"
			});
		}
		resize();
		
		$(window).on('resize', function(){
			resize();
		});
	}
	
	function ajaxLoading(status) 
    {
    	if( status == 'show' ){
        	$("#WooZoneAddProduct").append( loading );
       	}
       	else{
       		$("#WooZone-ajaxLoadingBox").remove();
       	}
    }
    
    function trigger_select_products()
    {
    	$("#WooZoneListOfProducts .list-of-products").on('click', 'a', function(e){
    		e.preventDefault();
    		
    		var that = $(this),
    			postid = that.data('postid');
    		
    		if( that.hasClass('added') ){
    			alert('Already added!');
    			return;
    		}
    		that.addClass('added');
    		var chosedProducts = $(".WooZoneChosenProducts ul");
    		
    		chosedProducts.find("li.product-note").hide();
    		chosedProducts.append('<li data-prodid="' + ( postid ) + '"><a href="#"><span><img src="' + ( that.find('img').attr('src') ) + '" class="product-post-image" /></span><div class="product-mask">' + ( that.find('h3').text() ) + '</div><span class="product-delete-box"><em>delete</em></span></a></li>');
    		
    		that.find(".product-tick-box").remove();
    		that.append('<div class="product-tick-box"><em>tick</em><div>');
    		
    		
    		chosedProducts.sortable();
    	});
    	
    	$(".WooZoneChosenProducts ul").on('click', "a em", function(e){
    		e.preventDefault();
    		
    		var that = $(this),
    			parent = that.parents('li').eq(0);
    			
    		parent.remove();
    		
    		$('#list-product-' + ( parent.data('prodid') ))
    		.removeClass('added')
    		.find('.product-tick-box').remove(); 
    		
    		var left_prod = $('#list-product-' + ( parent.data('prodid') ));
    		
    		if( $(".WooZoneChosenProducts ul li").size() <= 1 ){
    			$(".WooZoneChosenProducts ul li.product-note").show();
    		}
    	});
    	
    	$(".WooZoneChosenProducts").on('click', 'input.button', function(e){
    		e.preventDefault();
    		
    		var asins = [];
    		
    		$(".WooZoneChosenProducts").find('li:not(.product-note)').each(function(){
    			asins.push($(this).find('.product-mask').text());
    		});
    		
    		_editor.insertContent( '[WooZoneProducts asin="' + ( asins.join(",")) + '"][/WooZoneProducts]' );
    		
    		tb_remove();
    		
    		$("#WooZoneAddImportedProducts").html('');
    	});
    }
    
    function triggers_load_products()
    {
    	/*$("#WooZoneAddProduct").on('click', ".WooZoneChooseMenu a", function(e){
    		e.preventDefault();
    		
    		var that = $(this),
    			rel = that.attr('rel'),
    			rel_elm = $( "#" + rel );
    		
    		ajaxLoading( 'show' );
    		
    		$(".WooZoneChooseMenu a.on").removeClass('on');
    		that.addClass('on');
    		
    		if( rel == 'WooZoneAddImportedProducts' ){*/
    			$.post(ajaxurl, {
    					'action': 'WooZoneProductInPost',
    					'subaction': 'load-products',
    					'categ': 'all'
    				}, function(response) {
    				
	    				if( response.status == 'valid' ){
	    					$("#WooZoneAddImportedProducts").html( response.html );
	    					
	    					var tbWindow = $('#TB_window'),
								tb_width = tbWindow.width(),
								tb_height = tbWindow.height();
	 
	    					$(".WooZoneAllProducts").css({
								'height': (tb_height - 80) + "px"
							});
	    				}
	    				
	    				ajaxLoading( 'remove' );
				}, 
				'json');
				
				$("#WooZoneAddAsinsCode").hide();
    			$("#WooZoneAddImportedProducts").show();
    		/*}
    		
    		if( rel == 'WooZoneAddAsinsCode' ){
    			$("#WooZoneAddAsinsCode").show();
    			$("#WooZoneAddImportedProducts").hide();
    			
    			ajaxLoading( 'remove' );
    		}
    	});*/
    }
    

	// external usage
	return {
		"trigger_select_products": trigger_select_products,
		"ajaxLoading": ajaxLoading
	}
})(jQuery);
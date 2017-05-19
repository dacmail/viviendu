// Initialization and events code for the app
WooZoneAmazonDebug = (function($) {
	"use strict";

	// public
	var debug_level = 0;
	var loading = $('<div id="WooZone-ajaxLoadingBox" class="WooZone-panel-widget">loading</div>'); // append loading
	
	// init function, autoload
	function init() {
		// load the triggers
		$(document).ready(function() {
			triggers();
		});
	};
	
	// check all!
	var checkall_notify = {
			default: function( wrapper ) {
				var self = this;
				
				var wrapper = wrapper + ' ' || '';
				var per_one = wrapper + "li input[name='WooZone-amzdbg-rg[]']",
					per_all = wrapper + "input[name='WooZone-amzdbg-rg[all]']";
 
				// select all checkbox status based on item checkboxes
				if ( $(per_one+":checked").length == $(per_one).length ) {
					$(per_all).prop('checked', true);
				} else {
					$(per_all).prop('checked', false);
				}
			},
			
			triggers: function( wrapper ) {
				var self = this;
				
				var wrapper = wrapper + ' ' || '';
				var per_one = wrapper + "li input[name='WooZone-amzdbg-rg[]']",
					per_all = wrapper + "input[name='WooZone-amzdbg-rg[all]']";
					
				self.default( wrapper );
				
				// select all checkbox - click
				$('body').on('click', per_all + ', ' + per_all+' ~ label', function () {
					var that = $(this), elType = that.prop('tagName').toUpperCase();
	
					var allStatus = that.prop('checked');
					$(per_all).prop('checked', allStatus);
					$(per_one).prop('checked', allStatus);
					
					if ( allStatus ) {
						$(per_one).parent().find('a').addClass('on');
					} else {
						$(per_one).parent().find('a').removeClass('on');
					}
				});
				
				// select item checkbox - click
				$('body').on('click', per_one + ', ' + per_one+' ~ label', function () {
					var that = $(this), elType = that.prop('tagName').toUpperCase();
					
					var status = that.prop('checked');
					
					// select all checkbox status based on individul checkboxes
					if ( $(per_one+":checked").length == $(per_one).length ) {
						$(per_all).prop('checked', true);
					} else {
						$(per_all).prop('checked', false);
					}
					
					if ( status ) {
						that.parent().find('a').addClass('on');
					} else {
						that.parent().find('a').removeClass('on');
					}
				});
			}
	};
	
	function make_request() {
		ajaxLoading( 'show' );

		var data = [];
		
		// action
		data.push({
			name	: 'action',
			value	: 'WooZoneAmazonDebugGetResponse'
		});
		
		// asin
		data.push({
			name	: 'asin',
			value	: $('#WooZone-amzdbg-asin').val()
		});
		
		// response groups
		var rg = [];
		var per_one = "#WooZone-amazonDebug li input[name='WooZone-amzdbg-rg[]']";
		$(per_one + ':checked').each(function (i, el) {
			rg.push( $(el).val() );
		});
		data.push({
			name	: 'rg',
			value	: rg
		});
		
		// turn the result into a query string
		//console.log( data ); return false;
		data = $.param( data );
 
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: data,
			dataType: 'json',
			success: function(response) {
				if( typeof response.status != 'undefined' && response.status == 'valid' ) {
					var jsonStr = response.html;
					//jsonStr = jsonStr.toString();
					//jsonStr = JSON.parse(jsonStr);
					//jsonStr = JSON.stringify(jsonStr, null, '\t');

 					/*					
					jsonStr = '<pre><code class="json">' + jsonStr + '</code></pre>';
					var $respWrap = $('#WooZone-amzdbg-amazonResponse').html( jsonStr );
					// highlight.js
					hljs.configure({
						tabReplace		: '    ' // 4 spaces
					});
					//hljs.initHighlightingOnLoad();
					$('pre code').each(function(i, block) {
  						hljs.highlightBlock(block);
					});
					*/
					
					var $respWrap = $('#WooZone-amzdbg-amazonResponse #RawJson').html( jsonStr );
					$('#WooZone-amzdbg-amazonResponse #GoFormatJson').trigger('click');
 
					ajaxLoading( 'remove' );
				}
			}
		});
	}
	
	function ajaxLoading(status) 
    {
    	if( status == 'show' ){
        	$('#WooZone-amzdbg-amazonResponse').append( loading );
       	}
       	else{
       		$('#WooZone-amzdbg-amazonResponse #WooZone-ajaxLoadingBox').remove();
       	}
    }
	
	function triggers() {
		// check all
		checkall_notify.triggers('#WooZone-amazonDebug');
		
		// get response
		$('#WooZone-amazonDebug #WooZone-amzdbg-getAmzResponse').click(function(e) {
			e.preventDefault();
			
			make_request();	
		});
		
		// restore to default response groups
		$('#WooZone-amazonDebug #WooZone-amzdbg-rg-godefault').click(function(e) {
			e.preventDefault();
			
			var groupsDefault = $('#WooZone-amazonDebug #WooZone-amzdbg-default').val();
			groupsDefault = groupsDefault.split(',');
			
			var per_one = "#WooZone-amazonDebug li input[name='WooZone-amzdbg-rg[]']",
				per_all = "#WooZone-amazonDebug input[name='WooZone-amzdbg-rg[all]']";

			$(per_all).prop('checked', false);
			$(per_one).prop('checked', false);
			$(per_one).each(function(i, el) {
				var that = $(el),
					group = that.val();

				if ( $.inArray(group, groupsDefault) > -1 ) {
					that.prop('checked', true);
				}
				
				var status = that.prop('checked');
				if ( status ) {
					that.parent().find('a').addClass('on');
				} else {
					that.parent().find('a').removeClass('on');
				}
			});
		});
		
		console.log( $("#WooZone-amazonDebug #WooZone-amzdbg-amazonResponse") ); 
	};
	
	init();

	// external usage
	return {
		"ajaxLoading": ajaxLoading
	};
})(jQuery);
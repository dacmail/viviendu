/*
Document   :  Price Select
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/

// Initialization and events code for the app
WooZonePriceSelect = (function($) {
	"use strict";

	// public
	var debug_level = 0;
	var loading = $('<div id="WooZone-ajaxLoadingBox" class="WooZone-panel-widget">loading</div>'); // append loading
	var loading_auto = $('<div id="WooZone-ajaxLoadingBox-auto" class="WooZone-panel-widget"><div>loading</div></div>');
	var page = '';
	var product_type = '';
	var priceFieldPrefix = '';
	var priceType = {
		'regular'	: 1,
		'sale'		: 1
	};
	var saveMetas = {
		'auto'		: 1,
		'selected'	: 1,
		'ancestry'	: 1,
		'current'	: 1
	};
	
	// init function, autoload
	(function init() {
		// load the triggers
		$(document).ready(function() {
			page = $('.inside #publish').length > 0 ? 'details' : 'list';
			//console.log( page );
			
			if ( page == 'details' ) {
				product_type = $('#woocommerce-product-data .hndle span #product-type').val();
			}
			//console.log( product_type ); 

			priceFieldPrefix = ( page == 'details' ? '' : '_WooZone');
				
			woo_buttons_all();
			triggers();
		});
	})();
	
	function is_prod_amazon( post_id ) {
		var $hiddenWrapper = get_current_wrapper( post_id, 'hidden' );
		if ( $hiddenWrapper.length > 0 ) {
			var is = $hiddenWrapper.find('input.WooZone-price-isprodamz').val();
			if ( String(is) == '1' ) return true;
		}
		return false;
	}
	
	function get_current_wrapper( post_id, type ) {
		var wrapper = '';
		switch (type) {
			case 'hidden':
				wrapper = '.WooZonePriceSelectHidden';
				break;
				
			case 'wrapper':
				wrapper = '.WooZonePriceSelectWrapper';
				break;
				
			case 'wrapper-btn':
				wrapper = '.WooZone-priceselect-wrapper';
				break;
				
			case 'buttons':
				wrapper = '.WooZonePriceSelectButtons';
				break;
		}
		var $wrapper = $(wrapper).filter(function(i) {
			return $(this).data('post_id') == post_id;
		});
		return $wrapper;
	}
	
	function choose_price_default( post_id ) {
		var $wrapper = get_current_wrapper( post_id, 'wrapper' ),
			$hiddenWrapper = get_current_wrapper( post_id, 'hidden' );
			
   		for (var pt in priceType) {
   			var $price = $wrapper.find('input.WooZone-price-' + pt).prop('checked', false);
   			var _current = $hiddenWrapper.find('input.WooZone-price-' + pt + '-current').val().trim(),
   				_selected = $hiddenWrapper.find('input.WooZone-price-' + pt + '-selected').val().trim(),
   				_ancestry = $hiddenWrapper.find('input.WooZone-price-' + pt + '-ancestry').val().trim();

   			if ( _current == 'selected' && _ancestry != '' && _selected != ''  ) {
   				_ancestry = _ancestry.split(',');
   				
   				if ( _ancestry.length > 0 ) {
   					var _ancestryCss = [];
   					for (var key in _ancestry) {
   						_ancestryCss.push( 'ul.WooZonePriceSelect-Ancestry-' + _ancestry[key] );
   					}
   					_ancestryCss = _ancestryCss.join(' ');

   					var $_inputWrapp = $wrapper.find(_ancestryCss + ' ul.WooZone-priceselect-price span');
   					if ( $_inputWrapp.length > 0 ) {
   						$_inputWrapp.find('input.WooZone-price-' + pt).prop('checked', true);
   					}
   				}
   			}
   		}
	}
	
	function get_ancestry( $el ) {
		var $_parent = $el.parent('span'),
			_ancestry = $_parent.data('ancestry');
		return _ancestry;
	}
	
	function when_variations_loaded() {
        $('#woocommerce-product-data .woocommerce_variations .woocommerce_variation').each(function(i) {
            var that = $(this);
            var container = that.find('.woocommerce_variable_attributes .data .variable_pricing') || that.find('.woocommerce_variable_attributes .data_table .variable_pricing');
            var post_id = that.find('h3 .remove_variation').attr('rel');
 
            var $regular = container.find('input[name^="variable_regular_price"].wc_input_price'),
                $sale = container.find('input[name^="variable_sale_price"].wc_input_price');

            woo_buttons_add( post_id, $regular, $sale );
        });
	}
	
	function woo_buttons_all() {
		var post_id = 0;
		
   		if ( page == 'details' ) {
			
			if ( product_type == 'variable' ) {
			    $( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', when_variations_loaded);
			} else { // simple product type
	
				var $regular = $('._regular_price_field #_regular_price'),
					$sale = $('._sale_price_field #_sale_price');
	
	   			post_id = $regular.parents('form').find('input#post_ID').val();

	   			woo_buttons_add( post_id, $regular, $sale );
	   		}
   		} else { // list page
   			
   			//WooZone_price
   			$('td.WooZone_product_info').each(function(i) {
   				var that = $(this),
   					parent = that.parent('tr'),
   					post_id = parent.prop('id').replace('post-', '');
   					
				var $regular = parent.find('._WooZone_regular_price_field #_WooZone_regular_price'),
					$sale = parent.find('._WooZone_sale_price_field #_WooZone_sale_price');
   					
   				woo_buttons_add( post_id, $regular, $sale );
   			});
   		}
		
		woo_buttons_triggers();  		
	}
	
	function woo_buttons_add( post_id, $regular, $sale ) {
   		// product is not amazon!
   		if ( !is_prod_amazon(post_id) ) {
   			return false;
   		}
   		
		var $hiddenWrapper = get_current_wrapper( post_id, 'hidden' ),
            $_clone = $hiddenWrapper.clone();
		$regular.after( $_clone );
		
		var btn_html = '\
		<div class="WooZone-priceselect-wrapper" data-post_id="' + post_id + '" data-pricetype="{pricetype}">\
			<a href="#" class="WooZone-price-btn-selected button button-primary button-large" data-btn="selected">select</a>\
			<a href="#" class="WooZone-price-btn-auto button button-secondary button-large" data-btn="auto">auto</a>\
		</div>\
		';
		
		/*
		var hidden_html = '';
		hidden_html += '<div class="WooZone-priceselect-hidden" data-post_id="' + post_id + '">';
		for (var pt in priceType) {
			for (var pt2 in saveMetas) {
				hidden_html += '<input type="hidden" class="WooZone-price-' + pt + '-' + pt2 + '" name="WooZone-price[' + post_id + '][' + pt + '][' + pt2 + ']" />';
			}
		}
		hidden_html += '</div>';
		$regular.after( hidden_html );
		*/

		$regular.after( btn_html.replace('{pricetype}', 'regular') );
		$sale.after( btn_html.replace('{pricetype}', 'sale') );

		btn_is_active( post_id, $hiddenWrapper );
	}
	
	function btn_is_active( post_id, hiddenWrapper, wrapperBtn) {
		var $hiddenWrapper = hiddenWrapper || get_current_wrapper( post_id, 'hidden' ),
			$wrapperBtn = wrapperBtn || get_current_wrapper( post_id, 'wrapper-btn' );
			
		for (var pt in priceType) {
			var $btnWrapper = $wrapperBtn.filter(function(i) {
				return $(this).data('pricetype') == pt;
			});
			var _current = $hiddenWrapper.find('input.WooZone-price-' + pt + '-current').val().trim();
			$btnWrapper.find('[class^="WooZone-price-btn-"]').removeClass('active');
			if ( _current != '' ) {
				$btnWrapper.find('.WooZone-price-btn-' + _current).addClass('active');
			}
		}
	}
	
	function get_field_price_woo( post_id, pt ) {
		var $price_woo = '';
		if ( page == 'details' ) {
			
			if ( product_type == 'variable' ) {
			
				var $wrapperBtn = get_current_wrapper( post_id, 'wrapper-btn' );
				var $btnWrapper = $wrapperBtn.filter(function(i) {
					return $(this).data('pricetype') == pt;
				});
				$price_woo = $btnWrapper.parent().find('input[name^="variable' + priceFieldPrefix + '_' + pt + '_price"]');
			} else {
				
				$price_woo = $('.' + priceFieldPrefix + '_' + pt + '_price_field #' + priceFieldPrefix + '_' + pt + '_price');
			}
		} else { // list page

			var $buttonsWrapper = get_current_wrapper( post_id, 'buttons' );
			$price_woo = $buttonsWrapper.find('.' + priceFieldPrefix + '_' + pt + '_price_field #' + priceFieldPrefix + '_' + pt + '_price');
		}
		return $price_woo;
	}
	
	function woo_buttons_triggers() {
    	$(document.body).on('click', ".WooZone-priceselect-wrapper > a", function(e){
    		e.preventDefault();
    		
    		var that = $(this),
    			btn = that.data('btn'),
    			$wrapperBtn = that.parents('.WooZone-priceselect-wrapper'),
    			post_id = $wrapperBtn.data('post_id'),
    			btnPriceType = $wrapperBtn.data('pricetype');

			// choose price
    		if ( btn == 'selected' ) {
    			createLightbox( post_id );
    			choose_price_default( post_id );
    		}
    		// auto choose price
    		else {

				var $hiddenWrapper = get_current_wrapper( post_id, 'hidden' );
				if ( $hiddenWrapper.length > 0 ) {
		    		for (var pt in priceType) {
		    			if ( btnPriceType != pt ) continue;
	
		    			var $price = $hiddenWrapper.find('input.WooZone-price-' + pt + '-auto');
		    			var $price_woo = get_field_price_woo( post_id, pt );
		
			    		if ( $price.length > 0 ) {
		    				var price = $price.val();
		    				//if ( price != '' ) {
	    						$hiddenWrapper.find('input.WooZone-price-' + pt + '-current').val( 'auto' );
	    						$hiddenWrapper.find('input.WooZone-price-' + pt + '-selected').val( '' );
	    						$hiddenWrapper.find('input.WooZone-price-' + pt + '-ancestry').val( '' );
		    					
		    					if ( $price_woo.length > 0 ) {
		    						$price_woo.val( price );
		    					}
		    					
								btn_is_active( post_id, $hiddenWrapper, $wrapperBtn );
								
								if ( page == 'list' ) {
									save_prices( post_id, pt, 'auto' );
								}
		    				//}
		    			}
		    		}
	    		}
    		}
    	});
	}
	
    function triggers()
    {
    	// Request-URI Too Long / The requested URL's length exceeds the capacity limit for this server.
    	// URI too long fix: wp filter form - products list page
    	var _clicked = false;
    	$('form#posts-filter').on('click', 'input#post-query-submit, input#search-submit, input#doaction, input#doaction2, input#bulk_edit, input#delete_all', function(e) {
            if (_clicked) {
                _clicked = false; // reset flag
                return; // let the event bubble away
            }
    		e.preventDefault();

    		var $this = $(this), $form = $this.parents('form');
    		
    		$form.find('div.WooZonePriceSelectButtons').remove();
    		$form.find('div.WooZonePriceSelectHidden').remove();
    		$form.find('div[id^="WooZonePriceSelectInline"]').remove();
    		$form.find('input[name^="WooZonePriceSelectInline"]').remove();
    		//$form.submit();
    		
    		_clicked = true;

    		//$(this).trigger('click');
    		// vanilla javascript
			if (this.onclick) {
			   this.onclick();
			} else if (this.click) {
			   this.click();
			}
    	});

    	$('table.wp-list-table #the-list input#bulk_edit').click(function(){
    		var $this = $(this), $form = $this.parents('form');
    		$form.find('.WooZonePriceSelectButtons').remove();
    		$form.find('input[name^="WooZonePriceSelectInline"]').remove();
    	});

    	// Cancel Prices button
    	$('.WooZonePriceSelectWrapper .WooZonePriceSelect-buttons').on('click', '> a.cancel', function(e) {
    		e.preventDefault();
    		$('#TB_closeWindowButton').trigger('click');
    	});
    	
    	// Save Prices button
    	$('.WooZonePriceSelectWrapper .WooZonePriceSelect-buttons').on('click', '> a.save', function(e) {
    		e.preventDefault();
    		
    		var that = $(this),
    			$wrapper = that.parents('.WooZonePriceSelectWrapper'),
    			post_id = $wrapper.data('post_id');
    		//console.log( post_id );

			var $hiddenWrapper = get_current_wrapper( post_id, 'hidden' );
			if ( $hiddenWrapper.length > 0 ) {
				
				// validate prices (sale price must be lower than regular price)
				var _currentPrices = { 
					regular 	: $hiddenWrapper.find('input.WooZone-price-' + pt + '-auto').val(),
					sale 		: $hiddenWrapper.find('input.WooZone-price-' + pt + '-auto').val()
				};
				for (var pt in priceType) {
	    			var $price = $wrapper.find('input.WooZone-price-' + pt + ':checked');
	    			if ( $price.length > 0 ) {
	    				var price = $price.parents('span').data('price');
	    				_currentPrices[pt] = price;
	    			}
				}
				if ( _currentPrices['sale'] > _currentPrices['regular'] ) {
					alert('Sale price must be lower than regular price!');
					return true;
				}

	    		for (var pt in priceType) {
	    			
	    			var $price = $wrapper.find('input.WooZone-price-' + pt + ':checked');
	    			var $price_woo = get_field_price_woo( post_id, pt );
	
		    		if ( $price.length > 0 ) {
	    				var price = $price.parents('span').data('price');
	    				//if ( price != '' ) {
	    					$hiddenWrapper.find('input.WooZone-price-' + pt + '-current').val( 'selected' );
	    					$hiddenWrapper.find('input.WooZone-price-' + pt + '-selected').val( price );
	    					$hiddenWrapper.find('input.WooZone-price-' + pt + '-ancestry').val( get_ancestry($price) );
	    					
	    					if ( $price_woo.length > 0 ) {
	    						$price_woo.val( price );
	    					}
	    					
	    					btn_is_active( post_id, $hiddenWrapper );
	    				//}
	    			}
	    		}
	    		
				if ( page == 'list' ) {
					save_prices( post_id, 'both', 'selected' );
				}
    		}
    		
    		if ( page == 'details' ) {
    			$('#TB_closeWindowButton').trigger('click');
    		}
    	});
    }
    
	function save_prices( post_id, whatType, operation ) {
		ajaxLoading( post_id, operation, 'show' );
			
		var data = {
			'action' 			: 'WooZonePriceSelectSave',
			'post_id'			: post_id,
			'whatType'			: whatType,
			'operation'			: operation,
			'debug_level'		: debug_level
		};
			
		var $hiddenWrapper = get_current_wrapper( post_id, 'hidden' );
		$hiddenWrapper.find('input[name^="WooZone-price"]').each(function(i) {
			var $this = $(this);
			data[ $this.prop('name') ] = $this.val();
		});
		//console.log( data ); return false;

		$.post(ajaxurl, data, function(response) {

			if( response.status == 'valid' ){
			}
				
			ajaxLoading( post_id, operation, 'close' );
				
			if ( operation == 'selected' ) {
				$('#TB_closeWindowButton').trigger('click');
			}

		}, 'json');
	}
    
	function createLightbox( id )
	{
		tb_show( 'Amazon Product Choose Price', '#TB_inline?inlineId=WooZonePriceSelectInline-'+id );
		//tb_position();
		tb_resize();
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
		}
		resize();
		
		$(window).on('resize', function(){
			resize();
		});
	}
	
	function ajaxLoading(post_id, operation, status)
    {
    	if ( page != 'list' ) return true;

   		var $wrapper = '';
		if ( operation == 'auto' ) {
    		$wrapper = get_current_wrapper( post_id, 'buttons' );
      	} else {
    		$wrapper = get_current_wrapper( post_id, 'wrapper' );
      	}

    	if( status == 'show' ){
        	$wrapper.append( loading_auto );
       	} else{
       		$wrapper.find('#WooZone-ajaxLoadingBox-auto').remove();
       	}
    }
    
	// external usage
	return {
	}
})(jQuery);
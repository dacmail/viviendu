/*
Document   :  Sync Monitor
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/

// Initialization and events code for the app
WooZoneTailSyncMonitor = (function ($) {
    "use strict";

    // public
    var debug_level = 0;
    var maincontainer = null;
    var loading = null;
    var loaded_page = 0;
    
    var lang = null;
    
    var mainsync = null;
    var synctable = null;
    var form_settings = null;
    var settings_wrapp = null;
    var pagination_wrapp = null;
    var module = 'synchronization';
    
    var syncall_msg = null;
    var syncall_rows_todo = null;
    var syncall_rows_done = null;
    var syncall_status = 0; // default is closed
    
    var load_is_running = false; // load products is already running;
    var reload_timer = null;
    var reload_interval = 30; // reload products interval in seconds
    var reload_countdown = reload_interval;
    
    var rows_nb = null;
    var row_first = null;
    var row_last = null;


	// init function, autoload
	(function init() {
		// load the triggers
		$(document).ready(function(){
 		    
			maincontainer = $("#WooZone-wrapper");
			loading = maincontainer.find("#WooZone-main-loading");

			lang = maincontainer.find('#WooZone-lang-translation').html();
			//lang = JSON.stringify(lang);
			lang = JSON && JSON.parse(lang) || $.parseJSON(lang);

            mainsync = maincontainer.find("#WooZone-sync-log");
            synctable = mainsync.find('.WooZone-sync-table');
			form_settings = 'form.WooZone-sync-settings';
            settings_wrapp = '.WooZone-sync-info';
            pagination_wrapp = '.WooZone-sync-info .WooZone-sync-pagination';
			module = mainsync.data('module');
			
            syncall_msg = maincontainer.find('#WooZone-content-area #WooZone-sync-log .WooZone-panel-sync-all');
            
			triggers();
            
            jQuery('i, a').tipsy({live: true, gravity: 'n'});
		});
	})();
	
	// Load products list
	function loadProducts( callback ) {
        var data = [];

        // already loading...
        if ( load_is_running ) {
            if ( typeof callback != 'undefined' && $.isFunction(callback) ) {
                callback();
            }
            return false;
        }
        load_is_running = true;

        WooZone.to_ajax_loader( "Loading products list..", $('.WooZone-sync-table') );
        
        data.push({name: 'action', value: 'WooZoneSyncAjax'});
        data.push({name: 'subaction', value: 'load_products'});
        data.push({name: 'module', value: module});
        data.push({name: 'debug_level', value: debug_level});
    
        var paged = mainsync.find('.WooZone-sync-pagination.WooZone-sync-top .paging-input input.current-page').val();
        data.push({name: 'paged', value: paged});
        
        data = $.param( data ); // turn the result into a query string
        
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function(response) {

            if( response.status == 'valid' ){
                mainsync.find( pagination_wrapp ).html( response.pagination );

                synctable.find('> table > tbody').html( response.html );
                if ( misc.hasOwnProperty(response, 'estimate') ) {
                    mainsync.find('.WooZone-sync-filters > span span.count').html( response.estimate.nb );
                    mainsync.find('.WooZone-sync-filters > span span.countv').html( response.estimate.nbv );
                } else {
                    mainsync.find('.WooZone-sync-filters > span span.count').html( response.nb );
                    mainsync.find('.WooZone-sync-filters > span span.countv').html( response.nbv );
                }
                
                // set table rows limits - first & last
                set_rows_limits();
            }
            
            WooZone.to_ajax_loader_close();
            
            goto_next();

            if ( typeof callback != 'undefined' && $.isFunction(callback) ) {
                callback();
            }
            load_is_running = false;
        }, 'json');
	}
	
	// Synchronisation settings
	function syncSettings( el ) {
	    var data = [];

        WooZone.to_ajax_loader( "Saving synchronisation settings..", $('.WooZone-sync-info') );

        data = el.parents('form').serializeArray();
        
        data.push({name: 'action', value: 'WooZoneSyncAjax'});
        data.push({name: 'subaction', value: 'save_settings'});
        data.push({name: 'debug_level', value: debug_level});
        
        data.push({name: 'sync_stop_reload', value: get_sync_stop_reload()});
        
        data = $.param( data ); // turn the result into a query string
	    
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function(response) {

            if( response.status == 'valid' ){
                mainsync.find( form_settings ).remove();
                mainsync.find( settings_wrapp + ' > h3' ).after( response.form );

                el.parent().append( response.msg );

                setTimeout(function() {
                    el.next().fadeOut();
                }, 3000);
            }

            WooZone.to_ajax_loader_close();
        }, 'json');
	}
	
    // Sync single product
	function syncProduct( row, callback )
	{
	    var data = [];
	    
	    if ( $.isFunction(callback) && syncall_status == 0 ) {
	        syncall_msg.html( lang.sync_now_stopped ).show();
	        goto_next( row_first, {'time': 1000} );

			row_loading( row, 'hide' );

			mainsync.find( settings_wrapp + ' .WooZone-sync-inprogress' ).hide();
            WooZone.to_ajax_loader_close();

	        return true;
	    }

		row_loading( row, 'show' );
		
        syncall_msg.html( 
            '<span>' + misc.decodeEntities( lang.sync_now.replace('{nb}', --syncall_rows_todo), true ) + '</span>: '
            + get_product_details( row, 'html' )
        ).show();
        ++syncall_rows_done;
		
        data.push({name: 'action', value: 'WooZoneSyncAjax'});
        data.push({name: 'subaction', value: 'sync_prod'});
        data.push({name: 'debug_level', value: debug_level});
        
        data.push({name: 'id', value: row.data('id')});
        data.push({name: 'asin', value: row.data('asin')});
        
        data = $.param( data ); // turn the result into a query string
 
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {

			if( response.status == 'valid' ){
			    var td = row.find("td");
				td.eq(3).find('> a > span').html( response.sync_hits ); // column "sync hits"
				if ( response.sync_last_status != '' ) {
				    //td.eq(3).find('> i').remove();
                    //td.eq(3).append( response.sync_last_status );
                    td.eq(3).find('> a').prop( 'title', response.sync_last_status );  // column "sync hits"
                }
				td.eq(4).html( response.sync_last_date );  // column "sync last date"

				// column "product title"
				if ( misc.hasOwnProperty( response, 'asins_details' ) ) {
					if ( misc.hasOwnProperty( response.asins_details, row.data('id') ) ) {
						td.eq(2).find('> a').text( response.asins_details[ row.data('id') ]['Title'] );
					}
				}
  
				if ( $.isFunction(callback) ) {
				    var row_next = row.next(), row_pnext = row_next ? row_next.next() : null;

                    //if ( row_pnext.length > 0 && row_pnext.hasClass('wz-variation') ) {
                    if ( row_next.length > 0 && row_next.find('td a.wz-show-variations').length ) {
                        //row_next.find('td a.wz-show-variations').trigger('click', [row_next, callback]);
                        open_product_variations( row_next, 'open', callback );
                    } else {
                        next_to_sync( row_next, callback );
				    }
				}
			}
			
			row_loading( row, 'hide' );
		}, 'json');
	}

	function next_to_sync( row, callback ) {
        if ( row.length > 0 ) { // next row
            goto_next( row, {'time': 100, 'useMethod': 'straight'} );
            callback( row, callback );
        } else { // no more rows
            syncall_status = 0; // status is closed again

            syncall_msg.html( lang.sync_now_finished ).show();
            goto_next( row_first, {'time': 1000} ); // go to first table row again

			mainsync.find( settings_wrapp + ' .WooZone-sync-inprogress' ).hide();
            WooZone.to_ajax_loader_close();
        }
	}

    function goto_next( next, pms ) {
        var $parent = synctable,
            $nextel = typeof next != 'undefined' ? next : $parent.find('.WooZone-sync-table-row.wz-next-sync');

        if ( $nextel.length ) {
            scrollToElement( $nextel, $parent, pms );
        }
    }

    function scrollToElement(child, parent, pms) {
        parent = typeof(parent) != 'undefined' ? parent : 'html, body';

        //time = typeof(time) != 'undefined' ? time : 1000;
        //verticalOffset = typeof(verticalOffset) != 'undefined' ? verticalOffset : 0;
        var time = typeof pms == 'object' && misc.hasOwnProperty(pms, 'time') ? pms.time : 1000,
            verticalOffset = typeof pms == 'object' && misc.hasOwnProperty(pms, 'verticalOffset') ? pms.verticalOffset : 0,
            scrollTop = typeof pms == 'object' && misc.hasOwnProperty(pms, 'scrollTop') ? pms.scrollTop : '',
            useMethod = typeof pms == 'object' && misc.hasOwnProperty(pms, 'useMethod') ? pms.useMethod : 'animation';

        var $parent = $(parent),
            $child = $(child);
        if ( $parent.length <= 0 || $child.length <= 0 ) return false;
        
        $parent.scrollTop(0);

        if ( scrollTop == '' ) {
            var offset = $child.position(),
                offsetTop = parseInt( parseInt(offset.top) + parseInt(verticalOffset) ),
                poffset = $parent.position(),
                poffsetTop = parseInt(poffset.top),
                scrollTop = parseInt( offsetTop - poffsetTop );

            if ( useMethod == 'animation' ) {
                $parent.animate({
                    'scrollTop': scrollTop
                }, time);
            } else {
                $parent.scrollTop( scrollTop );
            }
        } else {
            scrollTop = parseInt( scrollTop );
            $parent.scrollTop( scrollTop );
        }
    }
	
    // auto reload
    function auto_reload() {
        var data = [];
        
        WooZone.to_ajax_loader( lang.loading, $('.WooZone-sync-table') );

        data.push({name: 'action', value: 'WooZoneSyncAjax'});
        data.push({name: 'subaction', value: 'auto_reload'});
        data.push({name: 'debug_level', value: debug_level});
        
        data.push({name: 'sync_stop_reload', value: get_sync_stop_reload()});
        
        data = $.param( data ); // turn the result into a query string
        
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function(response) {
            if( response.status == 'valid' ){
                reload_products();
            }
            WooZone.to_ajax_loader_close();
        }, 'json');
    }
	
	function reload_products() {
	    // verify if stopped!
        if ( get_sync_stop_reload() ) {
            // delete old timer
            reset_timer();
            return false;            
        }

        //reload_countdown = reload_interval;
	    mainsync.find('.WooZone-sync-filters > span.right strong').html( reload_countdown );

	    function reload() {
            // verify if stopped!
            if ( get_sync_stop_reload() ) {
                // delete old timer
                reset_timer();
                return false;            
            }

            reload_countdown--;
            if ( reload_countdown <= 0 ) {
                mainsync.find('.WooZone-sync-filters > span.right strong').html( reload_countdown );

                // delete old timer
                reset_timer();
                
                reload_countdown = reload_interval;
                
                // load products
                loadProducts( reload_products );
            } else {
                mainsync.find('.WooZone-sync-filters > span.right strong').html( reload_countdown );
                reload_timer = setTimeout(reload, 1000);
            }
        };
        reload_timer = setTimeout(reload, 1000);
	}
	
	function reset_timer() {
        // delete old timer
        clearTimeout(reload_timer);
        reload_timer = null;
	}
	
    function get_sync_stop_reload( action ) {
        if ( typeof action != 'undefined' ) {
            if ( action == 'set_interval' ) {
                if ( mainsync.find('.WooZone-sync-filters > span.right strong').length > 0 ) {
                    mainsync.find('.WooZone-sync-filters > span.right strong').html( reload_interval );
                }
            } else if ( action == 'stop' ) {
                mainsync.find('.WooZone-sync-filters > span.right input#sync_stop_reload').prop('checked', true);
            }
        }
        var sync_stop_reload = mainsync.find('.WooZone-sync-filters > span.right input#sync_stop_reload'),
            sync_stop_reload_status = 1; // default is stopped
        if ( sync_stop_reload.length > 0 && !sync_stop_reload.is(':checked') ) {
            sync_stop_reload_status = 0;
        }
        return sync_stop_reload_status;
    }
    
    function open_product_variations( row, status, callback ) {
        var btn = row.find('td a.wz-show-variations'),
            row_id = row.data('id'),
            row_child = row.next(),
            first = row_child;

        // verify if variations are already loaded
        var variations_loaded = false; 
        if ( row_child.length && row_child.hasClass('wz-variation') ) {
            var parent_id = row_child.data('parent_id');
            if ( parent_id && parent_id == row_id ) {
                variations_loaded = true;
            }
        }
        
        // open variations
        var make_request = function( action, params, callback ){
            var data = [];

            //load_is_running = true;

            if ( typeof callback == 'undefined' ) WooZone.to_ajax_loader( lang.loading, $('.WooZone-sync-table') );

            data.push({name: 'action', value: 'WooZoneSyncAjax'});
            data.push({name: 'subaction', value: action});
            data.push({name: 'module', value: module});
            data.push({name: 'debug_level', value: debug_level});
            
            if ( 'open_variations' == action ) {
                data.push({name: 'id', value: params['id']});
            }
           
            data = $.param( data ); // turn the result into a query string
            
            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(ajaxurl, data, function(response) {
    
                if( response.status == 'valid' ){
                    synctable.find('> table > tbody tr').filter(function(i) {
                        return $(this).data('id') == params['id'];
                    }).after( response.html );
                    
                    // set table rows limits - first & last
                    set_rows_limits();
                }
                
                if ( typeof callback == 'undefined' ) WooZone.to_ajax_loader_close();
                
                //goto_next();
    
                //if ( typeof callback != 'undefined' && $.isFunction(callback) ) {
                //    callback();
                //}
                //load_is_running = false;

                open_product_variations_( row, status, callback );

            }, 'json');
        }
        
        // variations already loaded
        if (!variations_loaded) {
            make_request( 'open_variations', {
                'id' : row_id
            }, callback );
        }
        else {
            open_product_variations_( row, status, callback );
        }
    }
    
    function open_product_variations_( row, status, callback ) {
        var btn = row.find('td a.wz-show-variations'),
            row_id = row.data('id'),
            row_child = row.next(),
            first = row_child;

        while ( row_child.hasClass('wz-variation') ) {
            if ( status == 'open') {
                row_child.addClass("wz-hide-me");
            } else if ( status == 'close' ) {
                row_child.removeClass("wz-hide-me");
            } else {
                row_child.toggleClass("wz-hide-me");
            }
            row_child = row_child.next();
        }
        //var childs = synctable.find('tr.WooZone-sync-table-row.wz-variation').filter(function(i) {
        //    return id == $(this).data('parent_id'); 
        //}).toggleClass("wz-hide-me");
        
        //if ( childs.eq(0).hasClass('wz-hide-me') ) {
        if ( first.hasClass('wz-hide-me') ) {
            btn.removeClass('sign-plus').addClass('sign-minus').find('span').html('-');
        } else {
            btn.removeClass('sign-minus').addClass('sign-plus').find('span').html('+');
        }
        
        //if ( typeof row_next != 'undefined' && typeof callback === 'function' ) {
        if ( typeof callback === 'function' ) {
            next_to_sync( row, callback );
        }
    }
    
    function get_product_details( row, field ) {
        var asin = row.data('asin'),
            id = row.data('id'),
            title = row.find('td').eq(2).find('a').text(),
            ret = {};
        ret = {
            'asin'  : asin,
            'id'    : id,
            'title' : title,
            'html'  : ''
        };
        ret.html = misc.format( lang.sync_now_msgformat, ret.asin, ret.id, ret.title );

        if ( typeof field != 'undefined' ) {
            return ret[field];
        }
        return ret;
    }
    
    function set_rows_limits() {
        rows_nb = synctable.find('> table > tbody > tr.WooZone-sync-table-row').length;
        row_first = synctable.find('> table > tbody > tr.WooZone-sync-table-row:first');
        row_last = synctable.find('> table > tbody > tr.WooZone-sync-table-row:last');

        syncall_rows_todo = rows_nb;
        syncall_rows_todo = parseInt( syncall_rows_todo - syncall_rows_done );
    }
	
	function row_loading( row, status, pms )
	{
	    var pms = pms || {};

		if( status == 'show' ){
			if( row.size() > 0 ){
				if( row.find('.WooZone-row-loading-marker').size() == 0 ){
					var row_loading_box = $('<div class="WooZone-row-loading-marker"><div class="WooZone-row-loading"><div class="WooZone-meter WooZone-animate" style="width:30%; margin: 22px 0px 0px 30%;"><span style="width:100%"></span></div></div></div>')
					row_loading_box.find('div.WooZone-row-loading').css({
						'width': row.width(),
						'height': row.height(),
						'top': '-16px'
					});

                    row.find('td').eq(0).append(row_loading_box);
				}
				row.find('.WooZone-row-loading-marker').fadeIn('fast');
			}
		}else{
			row.find('.WooZone-row-loading-marker').fadeOut('fast');
		}
	}

    function triggers()
    {
        // save settings form
        maincontainer.on('click', form_settings + ' button', function(e){
            e.preventDefault();

            var that    = $(this);
                
            syncSettings( that );
        });
        
        // load products
        maincontainer.on('click', '.WooZone-sync-filters span.right button.load_prods', function(e){
            e.preventDefault();

            loadProducts();
        });
        loadProducts( reload_products ); // default page load
        
        // toggle product variations rows
        synctable.on('click', 'tr td a.wz-show-variations', function(e){
            e.preventDefault();

            var that = $(this), 
                parent = that.parents().eq(1),
                id = parent.data('id');
                
            open_product_variations( parent, 'toggle' );
        });
        
        // product sync
        synctable.on('click', 'td.WooZone-sync-now button', function(e){
            e.preventDefault();
 
            var that    = $(this),
                row     = that.parents("tr").eq(0);
     
            syncProduct( row );
        });
        
        // auto reload products
        maincontainer.on('click', '.WooZone-sync-filters span.right label, .WooZone-sync-filters span.right input', function(e){
            //e.preventDefault();

            var that = $(this), elType = that.prop('tagName').toUpperCase();
            if ( elType == 'LABEL' ) {
                maincontainer.find('.WooZone-sync-filters span.right input').trigger('click');
                return false;
            }
            auto_reload();
        });
        get_sync_stop_reload( 'set_interval' );

        // sync all products
        maincontainer.on('click', '.WooZone-sync-filters span.right button.sync-all', function(e){
            e.preventDefault();

            var rows = synctable.find('> table > tbody > tr.WooZone-sync-table-row'),
            	  first_row = rows.eq(0);
  
            if ( rows.length <= 0 ) {
                syncall_msg.html( lang.no_products ).show();
                return false;
            }
            
            get_sync_stop_reload( 'stop' ); // stop auto-reload products
            
            syncall_rows_todo = rows_nb; // reset number of products to do
            syncall_rows_done = 0; // init number of products done
            syncall_status = 1; // status is now open

			//'<strong>' + lang.sync_now_inwork + '</strong>' + '<button>' + lang.sync_now_stop_btn + '</button>'
			mainsync.find( settings_wrapp + ':first .WooZone-sync-inprogress' ).html( '<p><strong>' + lang.sync_now_inwork + '</strong>' + '<button>' + lang.sync_now_stop_btn + '</button></p>' ).show();
			mainsync.find( settings_wrapp + ':last .WooZone-sync-inprogress' ).html( '<p><strong>' + lang.sync_now_inwork + '</strong>' + '<button>' + lang.sync_now_stop_btn + '</button></p>' ).show();

            /*
            var row_debug = rows.filter(function(i) {
               var that = $(this);
               return 1651 == that.data('id'); 
            });
            syncProduct( row_debug, syncProduct );
            */

			// first row has variations
			if ( first_row.length > 0 && first_row.find('td a.wz-show-variations').length ) {
				//first_row.find('td a.wz-show-variations').trigger('click', [first_row, callback]);
				open_product_variations( first_row, 'open', function() {
					syncProduct( first_row, syncProduct );
				});
			}
			else {
            	syncProduct( first_row, syncProduct );
           }
        });
        
        // stop sync all
        maincontainer.on('click', '.WooZone-sync-inprogress button', function(e){
            e.preventDefault();

            syncall_status = 0; // status is now close

            mainsync.find( settings_wrapp + ' .WooZone-sync-inprogress > p > strong' ).html( lang.sync_now_stopping );
        });
        
        // pagination
        var make_request = function( action, params, callback ){
            var data = [];

            //load_is_running = true;
            WooZone.to_ajax_loader( lang.loading, $('.WooZone-sync-table') );

            data.push({name: 'action', value: 'WooZoneSyncAjax'});
            data.push({name: 'subaction', value: action});
            data.push({name: 'module', value: module});
            data.push({name: 'debug_level', value: debug_level});
            
            //var paged = mainsync.find('.WooZone-sync-pagination.WooZone-sync-top .paging-input input.current-page').val();
            //data.push({name: 'paged', value: paged)});
            
            // validate pagination vars
            var post_per_page = 'all',
                paged         = 1;
            if ( misc.hasOwnProperty(params, 'post_per_page') ) {
                post_per_page = params['post_per_page'];
                post_per_page = post_per_page != 'all' ? parseInt( post_per_page ) : post_per_page;
            }
            if ( misc.hasOwnProperty(params, 'paged') ) {
                var totalPages_ = mainsync.find('.WooZone-sync-pagination.WooZone-sync-top .paging-input .total-pages'),
                    totalPages  = totalPages_.length ? parseInt( totalPages_.text() ) : 0;
          
                paged = parseInt( params['paged'] );
                paged = paged < 1 ? 1 : paged;
                paged = totalPages && paged > totalPages ? totalPages : paged;
            }
            
            // build pagination vars
            if ( 'post_per_page' == action ) {
                data.push({name: 'paged', value: 1});
                data.push({name: 'post_per_page', value: post_per_page});
            }
            else if ( 'paged' == action ) {
                data.push({name: 'paged', value: paged});
            }
            
            data = $.param( data ); // turn the result into a query string
            
            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(ajaxurl, data, function(response) {
    
                if( response.status == 'valid' ){
                    mainsync.find( pagination_wrapp ).html( response.pagination );
                
                    synctable.find('> table > tbody').html( response.html );
                    if ( misc.hasOwnProperty(response, 'estimate') ) {
                        mainsync.find('.WooZone-sync-filters > span span.count').html( response.estimate.nb );
                        mainsync.find('.WooZone-sync-filters > span span.countv').html( response.estimate.nbv );
                    } else {
                        mainsync.find('.WooZone-sync-filters > span span.count').html( response.nb );
                        mainsync.find('.WooZone-sync-filters > span span.countv').html( response.nbv );
                    }
                    
                    // set table rows limits - first & last
                    set_rows_limits();
                }
                
                WooZone.to_ajax_loader_close();
                
                goto_next();
    
                if ( typeof callback != 'undefined' && $.isFunction(callback) ) {
                    callback();
                }
                //load_is_running = false;
            }, 'json');
        }

        mainsync.find( settings_wrapp ).on('change', 'select[name=WooZone-post-per-page]', function(e){
            e.preventDefault();

            make_request( 'post_per_page', {
                'post_per_page' : $(this).val()
            } );
        })
        .on('click', 'a.WooZone-jump-page', function(e){
            e.preventDefault();

            make_request( 'paged', {
                'paged' : $(this).attr('href').replace('#paged=', '')
            } );
        });
        
        // ENTER is pressed inside page input
        //var btnPublishActive = false;
        $(document).on('keypress', function(e) {
            if(e.which == 13 && $(e.target).is('input.current-page')) { // Enter
                //btnPublishActive = true;
                //$('.inside #publish').trigger('click'); // trigger event action!

                make_request( 'paged', {
                    'paged' : $(e.target).val()
                } ); 
            }
            e.stopPropagation();
            return true;
        });
    }
    
    var misc = {
    
        hasOwnProperty: function(obj, prop) {
            var proto = obj.__proto__ || obj.constructor.prototype;
            return (prop in obj) &&
            (!(prop in proto) || proto[prop] !== obj[prop]);
        },
    
        size: function(obj) {
            var size = 0;
            for (var key in obj) {
                if (misc.hasOwnProperty(obj, key)) size++;
            }
            return size;
        },
        
        format: function() {
            // The string containing the format items (e.g. "{field}")
            // will and always has to be the first argument.
            var args = arguments,
                str = args[0];
 
            return str.replace(/{(\d+)}/g, function(match, number) {
                return typeof args[number] !== 'undefined' ? args[number] : match;
            });
        },
        
        is_browser: function() {
            if(/chrom(e|ium)/.test(navigator.userAgent.toLowerCase())){
                return 'chrome';
            }
            return 'default';
        },
        
        // preserve = choose yes if you want to preserve html tags
        decodeEntities: (function() {
            var preserve = false;
   
            // create a new html document (doesn't execute script tags in child elements)
            // this also prevents any overhead from creating the object each time
            var doc = document.implementation.createHTMLDocument("");
            var element = doc.createElement('div');
                
            // regular expression matching HTML entities
            var entity = /&(?:#x[a-f0-9]+|#[0-9]+|[a-z0-9]+);?/ig;
        
            function getText(str) {
                if ( preserve ) {
                    // find and replace all the html entities
                    str = str.replace(entity, function(m) {
                        element.innerHTML = m;
                        return element.textContent;
                    });
                } else {
                    element.innerHTML = str;
                    str = element.textContent;
                }
                element.textContent = ''; // reset the value
                return str;
            }
        
            function decodeHTMLEntities(str, _preserve) {
                preserve = _preserve || false;
                if (str && typeof str === 'string') {

                    str = getText(str);
                    if ( preserve ) {
                        return str;
                    } else {
                        // called twice because initially text might be encoded like this: &lt;img src=fake onerror=&quot;prompt(1)&quot;&gt;
                        return getText(str);
                    }
                }
            }
            return decodeHTMLEntities;
        })(),
        decodeEntities2: function(str, preserve) {
            var preserve = preserve || false;

            if ( preserve )
                return $("<textarea/>").html(str).text();
            else
                return $("<div/>").html(str).text();
        }
    
    };

	// external usage
	return {
    }
})(jQuery);

/*
Document   :  Auto Import
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/

// Initialization and events code for the app
WooZoneAutoImport = (function($) {
	"use strict";

	// public
    var debug_level                     = 0,
        maincontainer                   = null,
        lang                            = null;

	// init function, autoload
	(function init() {
		// load the triggers
		$(document).ready(function() {

			maincontainer = $("#WooZone");
			
            // language messages
            lang = maincontainer.find('#WooZone-lang-translation').length
                ? maincontainer.find('#WooZone-lang-translation').html()
                : $('#WooZone-wrapper #WooZone-lang-translation').html();
            //lang = JSON.stringify(lang);
            lang = typeof lang != 'undefined'
                ? JSON && JSON.parse(lang) || $.parseJSON(lang) : lang;
                
            triggers();
		});
	})();


	// :: TRIGGERS
	function triggers() {
        //jQuery('i, a.WooZone-tipsy').tipsy({live: true, gravity: 'w', html: true});
        
        // simplemodal
        $('body').on('click', 'a.WooZone-tipsy', function(e) {
           //$(this).modal({overlayClose:true});
           $.modal( $(this).prop('title') ); // HTML
           return false; 
        });
	}

    // :: MESSAGES
    function set_status_msg_generic( status, msg, op, from ) {
        var from        = from || '';
    };


    // :: CRONJOB STATS
    var cronjob_status = (function() {
        
        var DISABLED                = false; // disable this module!
        var debug_level             = 0,
            reload_timer            = null,
            reload_interval         = 30, // reload products interval in seconds
            reload_countdown        = reload_interval,
            maincontainer           = null,
            what                    = '';

        // Test!
        function __() {};

        // get public vars
        function get_vars() {
            return $.extend( {}, {} );
        };

        // init function, autoload
        (function init() {
            // load the triggers
            $(document).ready(function() {
                maincontainer = $(".WooZone-panel .WooZone-sync-stats");
                what          = maincontainer.data('what');
 
                triggers();
            });
        })();

        // Triggers
        function triggers() {
            if ( DISABLED ) return false;
            else {
                reload_();
            }
        }

        // make request
        function make_request() {
            var data = [];
            
            WooZone.to_ajax_loader( lang.loading );

            what = $.inArray(what, ['queue', 'search']) > -1 ? what : '';
            if ( '' == what ) {
                WooZone.to_ajax_loader_close();
                return false;
            }

            var sub_action = 'cronjob_stats_' + what;
            data.push({name: 'action', value: 'WooZone_AutoImportAjax'});
            data.push({name: 'sub_action', value: sub_action});
            data.push({name: 'debug_level', value: debug_level});
            
            data = $.param( data ); // turn the result into a query string
            
            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(ajaxurl, data, function(response) {
                if( response.status == 'valid' ){
                    maincontainer.html( response.html );
                    reload_();
                }
                WooZone.to_ajax_loader_close();
            }, 'json');
        }

        function reset_timer() {
            // delete old timer
            clearTimeout(reload_timer);
            reload_timer = null;
        }

        function stop_reload() {
            return reload_countdown <= 0 ? true : false;
        }

        function reload_() {

            // verify if stopped!
            if ( stop_reload() ) {
                // delete old timer
                reset_timer();
                return false;            
            }

            function reload() {
                //console.log( reload_timer, ',', reload_countdown );

                // verify if stopped!
                if ( stop_reload() ) {
                    // delete old timer
                    reset_timer();
                    return false;            
                }
    
                reload_countdown--;
                if ( reload_countdown <= 0 ) {
                    // delete old timer
                    reset_timer();
                    
                    reload_countdown = reload_interval;
                    
                    // load products
                    make_request();
                } else {
                    reload_timer = setTimeout(reload, 1000);
                }
            };
            reload_timer = setTimeout(reload, 1000);
        }
    })();


    // :: Insane Import Page
    var insane = (function() {
        
        var DEBUG                   = false,
            TEST                    = 0;
        var debug_level             = 0,
            maincontainer           = $("#WooZone-insane-import"),
            lightbox                = null;

        // Test!
        function __() {};
        
        // get public vars
        function get_vars() {
            return $.extend( {}, {} );
        };
        
        // init function, autoload
        (function init() {
            // load the triggers
            $(document).ready(function() {
    
                // add lightbox container
                $("#WooZone").prepend( $('<div class="WooZone-big-overlay-lightbox"/>') );
                lightbox = $('.WooZone-big-overlay-lightbox');

                triggers();
            });
        })();
        
        // Triggers
        function triggers() {
            var box         = maincontainer.find('#WooZone-content-scroll'),
                box_import  = maincontainer.find('#WooZone-insane-import-parameters');

            // checkboxes with readonly attribute
            lightbox.on("click", 'input[type="checkbox"][readonly]', function(e){
                e.preventDefault();
                //$(this).prop('checked', true);
            });
            //lightbox.find('input[type="checkbox"][readonly]').css("opacity", "0.5");
            
            // checkboxes remove readonly attribute (become editable again)
            //lightbox.find('input[type="checkbox"]').off('.readonly').removeAttr("readonly").css("opacity", "1");

            // add search to schedule box
            //box.on('click', 'form#WooZone-search-products .WooZone-add-to-schedule', function(e) {
            $('#WooZone-insane-import').on('click', '.WooZone-add-to-schedule', function(e) {
                e.preventDefault();

                var form = $(this).parent().find('form#WooZone-search-products');
                get_search_params( { 'box' : box, 'form' : form, 'box_import' : box_import } );
            });
            
            // close lightbox
            lightbox.on("click", 'a#WooZone-close-btn', function(e){
                e.preventDefault();
                var that = $(this);
                
                boxstatus( 'close' );
            });
 
            // save search to schedule table
            lightbox.on('click', 'form#WooZone-search-add-schedule input[type="submit"]', function(e) {
                e.preventDefault();

                var form = $(this).parents('form');
                save_search_params( { 'form' : form } );
            });
        };
        
        // get search parameters
        function get_search_params( pms ) {
            boxstatus( 'show' );
            
            var pms             = typeof pms == 'object' ? pms : {},
                box             = misc.hasOwnProperty(pms, 'box') ? pms.box
                    : maincontainer.find('#WooZone-content-scroll'),
                form            = misc.hasOwnProperty(pms, 'form') ? pms.form
                    : box.find('form#WooZone-search-products'),
                box_import      = misc.hasOwnProperty(pms, 'box_import') ? pms.box_import
                    : maincontainer.find('#WooZone-insane-import-parameters');

            // Search Parameters
            /*
            var nodename        = null, 
                nodeid          = null;
  
            var data            = [],
                form_params     = form.serializeArray();

            // get last BrowseNode value
            if ( $.isArray(form_params) ) {
                for (var i = 0, len = form_params.length; i < len; i++) {
                    var obj = form_params[i];
                    if ( typeof(obj) != 'undefined' 
                        && misc.hasOwnProperty(obj, 'name') && misc.hasOwnProperty(obj, 'value') ) {

                        if ( obj.name.search(/BrowseNode/gi) > 0 ) {
                            if ( obj.value != '' ) {
                                nodename = obj.name;
                                nodeid   = obj.value;
                            }
                            form_params.splice(i, 1);
                            --i;
                        }
                    }
                }
                if ( nodeid ) {
                    form_params.push(
                        {name: nodename, value: nodeid}
                    );
                }
            }
            */
           
            var data            = [],
                form_params     = [];

            data.push(
                {name: 'debug_level',       value: debug_level},
                {name: 'action',            value: 'WooZone_AutoImportAjax'},
                {name: 'sub_action',        value: 'search_get_params'}
            );

            //loop through WooZone-search: input, select
            var browsenode       = [],
                browsenode_list  = [],
                browsenode_cc    = 0;

            form.find('input[name^="WooZone-search"], select[name^="WooZone-search"]').each(function (i) {
                var $this       = $(this),
                    type        = $this.prop('type'), //$this.prop('tagName').toLowerCase()
                    name        = $this.prop('name'),
                    _name       = name.replace('WooZone-search[', '').replace(']', ''),
                    value       = $this.val();
                    
                var add         = true;
                if ( 'select-one' == type ) {
                    var opt_sel = $this.find('option:selected'),
                        text    = $.trim( opt_sel.text() );

                    if ( 'category' == _name ) {
                        var nodeid  = opt_sel.data('nodeid');

                        form_params.push( { 'name': 'WooZone-search[category_id]', 'value': nodeid } );
                    }
                    else if ( 'BrowseNode' == _name ) {
                        if ( value != '' ) {
                            browsenode[0] = { 'name': 'WooZone-search['+_name+']', 'value': value };
                            browsenode[1] = { 'name': 'WooZone-search[_'+_name+']', 'value': text };
                            
                            browsenode_list[browsenode_cc] = [];
                            browsenode_list[browsenode_cc][0] = { 'name': 'WooZone-search['+_name+'_list]', 'value': value };
                            browsenode_list[browsenode_cc][1] = { 'name': 'WooZone-search[_'+_name+'_list]', 'value': text };
                            browsenode_cc++;
                        }
                        add = false; // insertion is made outside this loop
                    }
                    
                    if ( add ) {
                        form_params.push( { 'name': 'WooZone-search[_'+_name+']', 'value': text } );
                    }
                }

                if ( add ) {
                    form_params.push( { 'name': name, 'value': value } );
                }
            });
  
            // BrowseNode
            if (browsenode.length > 0) {
                for (var ii in [0, 1]) {
                    form_params.push( { 'name': browsenode[ii].name, 'value': browsenode[ii].value } );
                }
                
                for (var ii in browsenode_list) {
                    for (var ii2 in [0, 1]) {
                        form_params.push( {
                            'name'      : browsenode_list[ii][ii2].name+'['+ii+']',
                            'value'     : browsenode_list[ii][ii2].value
                        });
                    }
                }
            }

            form_params = $.param( form_params ); // turn the result into a query string
            data.push(
                {name: 'params', value: form_params}
            );
            
            // Import Parameters
            var import_params = get_parameters_import( { 'box' : box_import } );
            import_params = $.param( import_params ); // turn the result into a query string
            data.push(
                {name: 'import_params', value: import_params}
            );
            
            data = $.param( data ); // turn the result into a query string
            //console.log( data ); return false;

            $.post(ajaxurl, data, function(response) {
                if (1) {
                    //set_status_msg( response.status, response.msg, 'search' );

                   WooZone.to_ajax_loader_close();
                    if ( misc.hasOwnProperty(response, 'html') ) {
                        boxstatus( 'add_content', { 'html' : response.html } );
                    }
                }

            }, 'json')
            .fail(function() {})
            .done(function() {})
            .always(function() {});
        }
        
        // get import parameters
        function get_parameters_import( pms ) {
            var pms          = typeof pms == 'object' ? pms : {},
                box          = misc.hasOwnProperty(pms, 'box') ? pms.box : null,
                params       = misc.hasOwnProperty(pms, 'params') ? pms.params : [];
            
            // use cached params
            if ( $.isArray(params) && params.length > 0 ) {
                //import_params = params;
                return params;
            }
 
            //import-parameters[import_type]: input, output
            box.find('input[name^="import-parameters"]').each(function (i) {
                var $this   = $(this),
                    type    = $this.prop('type'),
                    name    = $this.prop('name').replace('import-parameters[', '').replace(']', ''),
                    value   = $this.val(),
                    param   = {};

                var add = true;
                if ( type == 'radio' || type == 'checkbox' ) {
                    if ( !$this.prop('checked') ) add = false;
                } else if ( type == 'range' ) {
                    if ( value >= 100 ) value = 'all';
                }

                param = { 'name': name, 'value': value };
                if ( add ) {
                    params.push( param );
                }
            });

            // import in
            params.push( { 'name': 'to-category', 'value': box.find('select#WooZone-to-category').val() } );
            var __ = box.find('select#WooZone-to-category option:selected').text();
            __ = $.trim( __ );
            params.push( { 'name': '_to-category', 'value': __ } );

            //console.log( params );
            //import_params = params;
            return params;
        }
        
        // save search parameters
        function save_search_params( pms ) {
            WooZone.to_ajax_loader( lang.loading );
            
            var pms             = typeof pms == 'object' ? pms : {},
                form            = misc.hasOwnProperty(pms, 'form') ? pms.form
                    : lightbox.find('form#WooZone-search-add-schedule');
                    
            var data            = [],
                form_params     = form.serializeArray();
                
            data.push(
                {name: 'debug_level',       value: debug_level},
                {name: 'action',            value: 'WooZone_AutoImportAjax'},
                {name: 'sub_action',        value: 'search_save_params'}
            );
            
            form_params = $.param( form_params ); // turn the result into a query string
            data.push(
                {name: 'allparams', value: form_params}
            );
            
            data = $.param( data ); // turn the result into a query string
            //console.log( data ); return false;

            $.post(ajaxurl, data, function(response) {
                if (1) {
                    //set_status_msg( response.status, response.msg, 'search' );

                    WooZone.to_ajax_loader_close();
                    if ( misc.hasOwnProperty(response, 'html') ) {
                        lightbox.find('.WooZone-donwload-in-progress-box .WooZone-autoimport-search-msg')
                            .html( response.html );
                        setTimeout(function() {
                            boxstatus( 'close' );
                        }, 10000);
                    }
                }

            }, 'json')
            .fail(function() {})
            .done(function() {})
            .always(function() {});
        }
        
        // Loading
        function boxstatus( status, pms ) {
            var status       = status || 'show',
                pms          = typeof pms == 'object' ? pms : {};
            
            if ( 'show' == status ) {
                lightbox.show();
                WooZone.to_ajax_loader( lang.loading );
            }
            else if ( 'close' == status ) {
                WooZone.to_ajax_loader_close();
                lightbox.find('.WooZone-donwload-in-progress-box').remove();
                lightbox.hide();
            }
            else if ( 'add_content' == status ) {
                var html = misc.hasOwnProperty(pms, 'html') ? pms.html : '';
                //lightbox.html( html );
                lightbox.find('.WooZone-donwload-in-progress-box').remove();
                lightbox.append( html );
                lightbox.find('input[type="checkbox"][readonly]').css("opacity", "0.5");
            }
        }

        function set_status_msg( status, msg, op ) {
            set_status_msg_generic( status, msg, op );
        };

        // external usage
        return {
            // attributes
            'v'                     : get_vars,
            
            // methods
            '__'                    : __
        };
    })();


    // :: MISC
    var misc = {

        hasOwnProperty: function(obj, prop) {
            var proto = obj.__proto__ || obj.constructor.prototype;
            return (prop in obj) &&
            (!(prop in proto) || proto[prop] !== obj[prop]);
        },

        arrayHasOwnIndex: function(array, prop) {
            return array.hasOwnProperty(prop) && /^0$|^[1-9]\d*$/.test(prop) && prop <= 4294967294; // 2^32 - 2
        },

        size: function(obj) {
            var size = 0;
            for (var key in obj) {
                if (misc.hasOwnProperty(obj, key)) size++;
            }
            return size;
        }
    }

	// external usage
	return {
		//"background_loading": background_loading
	}
})(jQuery);


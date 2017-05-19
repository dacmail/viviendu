/*
Document   :  404 Monitor
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/

// Initialization and events code for the app
WooZoneContentSpinner = (function ($) {
    "use strict";

    // public
    var debug_level = 0;
    var maincontainer = null;
    var loaded_page = 0;

	// init function, autoload
	(function init() {
		// load the triggers
		$(document).ready(function(){
			maincontainer = $(".WooZone-content");

			triggers();
		});
	})();
	
	function spin_product_content( that, row, prodID )
	{
		WooZone.to_ajax_loader( "Spinning content.." );
		
		var spin_replacements = row.find('.WooZone-spin-replacements').val();
		
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, {
			'action' 		: 'WooZoneSpinContentRequest',
			'sub_action'	: 'spin_content',
			'prodID'		: prodID,
			'replacements'	: spin_replacements,
			'debug_level'	: debug_level
		}, function(response) {
			
			var editor = row.find('#WooZone-spin-editor-' + prodID ),
				data = response.spin_content.data.reorder_content;
				
			editor.text( data );	
			
			spin_order_interface( editor );
			
			WooZone.to_ajax_loader_close();
		}, 'json');
	}
	
	function htmlEntities( str ) 
	{
		return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g,'&apos');
	}

	function spin_order_interface( editor )
	{
		var live_content 	= htmlEntities( editor.text() ),
			matches 		= live_content.match(/{(.*?)}/g);
		
		if( matches == null ) return;
		$.each(matches, function(key, val){
			
			// replace the { and }
			var new_val = val.replace("{", "");
			new_val = new_val.replace("}", "");
			var words = new_val.split("|");
			
			var select_container = $("<div class='WooZone-spin-replacement' />"),
				select_current_item = $("<span class='WooZone-spin-current-replacement' />"),
				select = $("<select class='WooZone-spin-replacements-list' />");
			
			select_current_item.text(words[0]);
			select_container.append(select_current_item);
			select_container.append(select);	

			$.each(words, function(word_key, word){
				select.append("<option value='" + ( word ) + "'>" + ( word ) + "</option>")
			});
			select.find('option:last').addClass('is_original');
			
			live_content = live_content.replace( val, select_container[0].outerHTML );
		});
		
		
		editor.html( live_content );
	}
	
	function closeReplacementBox( box )
	{
		box.find('.WooZone-spin-replacement-box').hide();
		box.find('.WooZone-hightlight').removeClass('WooZone-hightlight');
	}
	
	function convertToSlug(text)
	{
	    return text
	        .toLowerCase()
	        .replace(/ /g,'-')
	        .replace(/[^\w-]+/g,'');
	}

	function markOriginalWords( word, box )
	{
		var rel_box = box.parents('tr').eq(0).find(".WooZone-spin-original-content"),
			rel = rel_box.find("span.WooZone-word-" + convertToSlug(word));
		
		rel_box.find('.WooZone-hightlight').removeClass('WooZone-hightlight');
		rel.addClass('WooZone-hightlight');	
	}
	
	function openReplacementBox( that )
	{
		var box = that.parents('.WooZone-spin-editor-container').eq(0),
			suggestion_box = box.find(".WooZone-spin-replacement-box"),
			inline_suggest_box = that.parent(".WooZone-spin-replacement");
		
		box.find('.WooZone-hightlight').removeClass('WooZone-hightlight');
		that.addClass('WooZone-hightlight');	
			
		var suggestions_elm = inline_suggest_box.find('.WooZone-spin-replacements-list'),
			suggestions = [];
		
		suggestions_elm.find('option').each(function(){
			suggestions.push( $(this).val() );
		});
		
		var sel_list = suggestion_box.find('.WooZone-spin-box-suggest-select');
		
		// clean up original content
		sel_list.html('');
		
		$.each( suggestions, function(key, val) {
			var new_li = $("<li />");
			new_li.text(val);
			
			if( key == 0 ) new_li.addClass('current');
			if( key == (suggestions.length - 1) ) {
				new_li.addClass('original');
				
				
				markOriginalWords( new_li.text() , box );
		
				new_li.html( new_li.text() + '<sup>(*original)</sup>');
			}
			
			sel_list.append(new_li);
		});
		
		suggestion_box.show();
		//console.log( suggestions, box , suggestion_box, that, inline_suggest_box);
	}
	
	function changeWord( that )
	{
		var box = that.parents('.WooZone-spin-editor-container').eq(0),
			hightlight = box.find('.WooZone-hightlight'),
			hightlight_container = hightlight.parents('.WooZone-spin-replacement').eq(0);
		
		hightlight_container.find("span.WooZone-spin-current-replacement").text( that.text().replace("(*original)",'') );
		
		that.parents('ul').eq(0).find(".current").removeClass('current');
		that.addClass('current');
	}
	
	function word_nextback( el, type ) {
		var wrap = el.parent().parent(),
		wordsWrap = wrap.find('ul.WooZone-spin-box-suggest-select'),
		wordsList = wordsWrap.find('li'), nbWords = wordsList.length,
		current = wordsList.filter('.current');
 
		switch (type) {
			case 'next':
				current.removeClass('current');
				var newel = current.next('li');
				if ( !newel.length ) newel = wordsList.filter(':first');
				break;
				
			case 'prev':
				current.removeClass('current');
				var newel = current.prev('li');
				if ( !newel.length ) newel = wordsList.filter(':last');
				break;
		}
		newel.click().addClass('current');
	}
	
	function rollback_content( that, row, prodID ) {
		WooZone.to_ajax_loader( "Rolling back content.." );

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, {
			'action' 		: 'WooZone_rollback_content',
			'sub_action'	: 'rollback_content',
			'prodID'		: prodID,
			'debug_level'	: debug_level
		}, function(response) {
			
			var editor = row.find('#WooZone-spin-editor-' + prodID ),
				data = response.rollback_content.data.reorder_content;
				
			editor.text( data );	
			
			WooZone.to_ajax_loader_close();
		}, 'json');
	}
	
	function save_content( that, row, prodID ) {
		WooZone.to_ajax_loader( "Saving content.." );

		var wrap = row,
		content = wrap.find('.WooZone-spinner-container');
		
		var post_content = build_content_metas( content, 'post_content' ),
		spinned_content = build_content_metas( content, 'spinned_content' ),
		reorder_content = build_content_metas( content, 'reorder_content' );
		
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, {
			'action' 			: 'WooZone_rollback_content',
			'sub_action'		: 'save_content',
			'prodID'			: prodID,
			'post_content'		: post_content,
			'spinned_content'	: spinned_content,
			'reorder_content'	: reorder_content,
			'debug_level'		: debug_level
		}, function(response) {

			var editor = row.find('#WooZone-spin-editor-' + prodID ),
				data = response.save_content.data.reorder_content;

			editor.text( data );

			spin_order_interface( editor );

			WooZone.to_ajax_loader_close();
		}, 'json');
	}
	
	function build_content_metas( content, type ) {
		var clone = content.clone().appendTo('#wpfooter');
		
		if ( type == 'post_content' ) {
			clone.find(".WooZone-spin-replacements-list").remove();
			var cleanContent = clone.text();
			clone.remove();
			//console.log( type, cleanContent ); return false;
			return cleanContent;
		}
		
		var replacements_wrap = clone.find('.WooZone-spin-replacement');
		replacements_wrap.each(function(i, wrap) {

			var $wrap = $(wrap), replacements = $wrap.find('.WooZone-spin-replacements-list'), suggestions = [];
			
			switch (type) {
				case 'spinned_content':
					suggestions.push( replacements.find('option.is_original').val() );
					break;
					
				case 'reorder_content':
					suggestions.push( $wrap.find('.WooZone-spin-current-replacement').text() );
					break;
			}
			
			replacements.find('option').each(function(ii, el) {
				var current_val = $(el).val();
				if ( $.inArray( current_val, suggestions ) == -1 )
					suggestions.push( current_val );
			});
			
			$wrap.after( '{' + suggestions.join('|') + '}' );
		});
		
		clone.find(".WooZone-spin-replacement").remove();
		var cleanContent = clone.text();
		clone.remove();
		//console.log( type, cleanContent ); return false;
		return cleanContent;
	}
	
	function triggers()
	{
		maincontainer.on('click', 'a.WooZone-spin-content-btn', function(e){
			e.preventDefault();

			var that 	= $(this),
				row 	= that.parents("tr").eq(0),
				prodID	= that.data('prodid');
				
			
			spin_product_content( that, row, prodID );
		});
		
		maincontainer.on('click', 'span.WooZone-spin-current-replacement', function(e){
			e.preventDefault();
			
			openReplacementBox( $(this) );
		});
		
		maincontainer.on('click', '.WooZone-spin-replacement-box a.close', function(e){
			e.preventDefault();
			
			closeReplacementBox( $(this).parents('.WooZone-spin-editor-container').eq(0) );
		});
		
		maincontainer.on('click', '.WooZone-spin-box-suggest-select li:not(.current)', function(e){
			e.preventDefault();
		
			changeWord( $(this) ); 
		});
		
		// previous word change lightbox
		maincontainer.on('click', '.WooZone-spin-box-suggest-options .WooZone-skip-to-prev', function(e){
			e.preventDefault();
		
			word_nextback( $(this), 'prev' ); 
		});
		
		// next word change lightbox
		maincontainer.on('click', '.WooZone-spin-box-suggest-options .WooZone-skip-to-next', function(e){
			e.preventDefault();
		
			word_nextback( $(this), 'next' ); 
		});
		
		// roolback content button
		maincontainer.on('click', '.WooZone-spin-options .WooZone-rollback-content-btn', function(e){
			e.preventDefault();
			
			var that 	= $(this),
				row 	= that.parents("tr").eq(0),
				prodID	= that.data('prodid');
				
			
			rollback_content( that, row, prodID );
		});
		
		// save content button
		maincontainer.on('click', '.WooZone-spin-options .WooZone-save-content-btn', function(e){
			e.preventDefault();
			
			var that 	= $(this),
				row 	= that.parents("tr").eq(0),
				prodID	= that.data('prodid');
				
			
			save_content( that, row, prodID );
		});
	}

	// external usage
	return {
		"spin_order_interface": spin_order_interface
    }
})(jQuery);

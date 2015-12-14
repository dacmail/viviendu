(function($) {
	//Max equal height
	$.fn.setAllToMaxHeight = function(){
		return this.height( Math.max.apply(this, $.map( this , function(e){ return $(e).height() }) ) );
	}

	//Cycle defaults
	$.fn.cycle.log = $.noop;
	$.fn.cycle.speed = 0;
	$.fn.cycle.defaults.timeout = 0;
	$(document).ready(function() {

		//Menu movil
		$('#header').on('click', '.nav-toggle', function(event) {
			event.preventDefault();
			$('#header').toggleClass('open');
		});

		// Find all YouTube videos
		var $allVideos = $("#content iframe"),

	    // The element that is fluid width
	    $fluidEl = $("#content");

		// Figure out and save aspect ratio for each video
		$allVideos.each(function() {
			$(this)
				.data('aspectRatio', this.height / this.width)

				// and remove the hard coded width/height
				.removeAttr('height')
				.removeAttr('width');

		});

		// When the window is resized
		$(window).resize(function() {
			var newWidth = $fluidEl.width();
			// Resize all videos according to their own aspect ratio
			$allVideos.each(function() {
				var $el = $(this);
				$el
					.width(newWidth)
					.height(newWidth * $el.data('aspectRatio'));
			});
			// Kick off one resize to fix all videos on page load
		}).resize();


		//Legal checkbox
		$('.button.btn-contact').on('click', function(event) {
			event.preventDefault();
			if ($(this).closest('form').find('#legal').is(':checked')) 
				$(this).closest('form').submit();
		});
	});
	$(window).load(function() {
		//JS
		$('.catalogo.catalogo-list').setAllToMaxHeight();
		$('.link').setAllToMaxHeight();
	});

	//Sticky header on scroll
	var headerOffsetTop = $('#header').offset().top;
	$(window).scroll(function() {
		if ($(window).scrollTop() > headerOffsetTop) {
			$('body').addClass('sticky');
		} else {
			$('body').removeClass('sticky');
		}
	});

	$('.main-search form').on('submit', function(event) {
		ga('send', 'event', 'searchbox', window.location.href , $(this).find('#s').val());
	});
	$('#search-form').on('submit', function(event) {
		ga('send', 'event', 'searchbox', window.location.href , $(this).find('#s').val());
	});
	$('#main-menu a').on('click', function(event) {
		ga('send', 'event', 'header', window.location.href, $(this).attr('href'));
	});
	$('.tax-provincia .seccion-list a').each(function(index, el) {
		$(el).on('click', function(event) {
			ga('send', 'event', 'nav-prov', window.location.href, $(el).attr('href'), index);
		});
	});
	$('.tax-provincia .catalogo-list a.slide').each(function(index, el) {
		$(el).on('click', function(event) {
			ga('send', 'event', 'nav-prov', window.location.href, $(el).attr('href'), index);
		});
	});
	$('.tax-provincia .catalogo-list .title a').each(function(index, el) {
		$(el).on('click', function(event) {
			ga('send', 'event', 'nav-prov', window.location.href, $(el).attr('href'), index);
		});
	});

	$('.tax-provincia_seccion .catalogo-list a.slide').each(function(index, el) {
		$(el).on('click', function(event) {
			ga('send', 'event', 'nav-prov2', window.location.href, $(el).attr('href'), index);
		});
	});
	$('.tax-provincia_seccion .catalogo-list .title a').each(function(index, el) {
		$(el).on('click', function(event) {
			ga('send', 'event', 'nav-prov2', window.location.href, $(el).attr('href'), index);
		});
	});

	$('.category .catalogo-list a.slide').each(function(index, el) {
		$(el).on('click', function(event) {
			ga('send', 'event', 'sección', window.location.href, $(el).attr('href'), index);
		});
	});
	$('.category .catalogo-list .title a').each(function(index, el) {
		$(el).on('click', function(event) {
			ga('send', 'event', 'sección', window.location.href, $(el).attr('href'), index);
		});
	});
	$('.category .link a').each(function(index, el) {
		$(el).on('click', function(event) {
			ga('send', 'event', 'sección', window.location.href, $(el).attr('href'), index);
		});
	});

	$('#btn-contact').on('click', function(event) {
		ga('send', 'event', 'contacto_CTA', window.location.href);
	});

	$('.btn-visit').each(function(index, el) {
		$(el).on('click', function(event) {
			ga('send', 'event', 'visita_CTA', window.location.href, $(el).attr('href'));
		});
	});

	$('.btn-newsletter').each(function(index, el) {
		$(el).on('click', function(event) {
			ga('send', 'event', 'newsletter_sidebar', window.location.href);
		});
	});

	$('.text.main iframe').each(function(index, el) {
		$(el).on('click', function(event) {
			ga('send', 'event', 'video', window.location.href, $(el).attr('src'));
		});
	});
})(jQuery);
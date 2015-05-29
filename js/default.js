
(function($) {
	$.fn.cycle.log = $.noop;
	$.fn.cycle.speed = 0;
	$.fn.cycle.defaults.timeout = 0;
	$(document).ready(function() {
		$('#header').on('click', '.nav-toggle', function(event) {
			event.preventDefault();
			$('#header').toggleClass('open');
		});

	});
	$(window).load(function() {
		//JS
	});


})(jQuery);
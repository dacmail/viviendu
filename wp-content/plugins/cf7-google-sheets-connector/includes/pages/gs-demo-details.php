<div class="cd-faq-items">
	<ul id="basics" class="cd-faq-group">
		<li class="content-visible">
			<a class="cd-faq-trigger" href="#0"><?php echo esc_html( __( 'Google Sheet Connector Pro Demo ', 'gsconnector' ) ); ?></a>
			<div class="cd-faq-content" style="display: block;">
				
				<div class="gs-demo-fields gs-second-block">
					
					<p>
					  <label><?php echo esc_html( __( 'URL : ', 'gsconnector' ) ); ?></label>
					  <?php echo "https://cf7demo.gsheetconnector.com/wp-admin/" ?>
					</p>
					<p>
					  <label><?php echo esc_html( __( 'UserName : ', 'gsconnector' ) ); ?></label>
					  <?php echo "demo-repo-user" ?>
					</p>
					<p>
					  <label><?php echo esc_html( __( 'Password : ', 'gsconnector' ) ); ?></label>
					  <?php echo "!rJaJ@ixux!EIAwiW0FwKkTe" ?>
					</p>
					<p>
					  <label><?php echo esc_html( __( 'Sheet URL : ', 'gsconnector' ) ); ?></label>
					  <a href="https://docs.google.com/spreadsheets/d/1ooBdX0cgtk155ww9MmdMTw8kDavIy5J1m76VwSrcTSs/" target="_blank" rel="noopener">Click Here to view Sheet with data submitted.</a>
					</p>
				 </div>
				
			</div>
		</li>
	</ul>
</div>
<script>
jQuery(document).ready(function($){
	//update these values if you change these breakpoints in the style.css file (or _layout.scss if you use SASS)
	var MqM= 768,
		MqL = 1024;

	var faqsSections = $('.cd-faq-group'),
		faqTrigger = $('.cd-faq-trigger'),
		faqsContainer = $('.cd-faq-items'),
		faqsCategoriesContainer = $('.cd-faq-categories'),
		faqsCategories = faqsCategoriesContainer.find('a'),
		closeFaqsContainer = $('.cd-close-panel');
	
	//select a faq section 
	faqsCategories.on('click', function(event){
		event.preventDefault();
		var selectedHref = $(this).attr('href'),
			target= $(selectedHref);
		if( $(window).width() < MqM) {
			faqsContainer.scrollTop(0).addClass('slide-in').children('ul').removeClass('selected').end().children(selectedHref).addClass('selected');
			closeFaqsContainer.addClass('move-left');
			$('body').addClass('cd-overlay');
		} else {
	        $('body,html').animate({ 'scrollTop': target.offset().top - 19}, 200); 
		}
	});

	//close faq lateral panel - mobile only
	$('body').bind('click touchstart', function(event){
		if( $(event.target).is('body.cd-overlay') || $(event.target).is('.cd-close-panel')) { 
			closePanel(event);
		}
	});
	

	//show faq content clicking on faqTrigger
	faqTrigger.on('click', function(event){
		event.preventDefault();
		$(this).next('.cd-faq-content').slideToggle(200).end().parent('li').toggleClass('content-visible');
	});

});
</script>
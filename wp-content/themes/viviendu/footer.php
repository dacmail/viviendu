	<footer id="footer" class="clearfix">
		<div class="container">
			<div class="row">
				<div class="col-sm-4">
					<a href="<?php echo home_url(); ?>" class="logo"><img src="<?php echo asset_path('images/logo-foot.png'); ?>" alt="<?php bloginfo('name'); ?>"></a>
				</div>
				<div class="col-sm-8 menu-wrap">
					<?php wp_nav_menu(array('container' => 'nav', 'container_id' => 'footer-menu', 'container_class' => 'footer-menu', 'theme_location' => 'footer-social')); ?>
					<?php wp_nav_menu(array('container' => 'nav', 'container_id' => 'footer-menu1', 'container_class' => 'footer-menu', 'theme_location' => 'footer')); ?>
				</div>
				<div class="col-sm-12">
					<p class="copy">&copy;2015 Viviendu.es</p>
				</div>
			</div>
		</div>
	</footer>
	<div class="popup-wrapper" id="popup_contacto">
		<div class="popup-content">
			<a class="close" href="#">×</a>
			<h3 class="title">Pide presupuesto y contacta con fabricantes.</h3>
			<p>Pide presupuesto a través de este sencillo formulario para contactar con los fabricantes líderes de casas prefabricadas y viviendas móviles.
También puedes pedir presupuesto para reformas, ampliaciones y espacios comerciales.</p>
			<?php echo do_shortcode('[contact-form-7 id="45087" title="test2"]') ?>
		</div>
	</div>
	<a href="#popup_contacto" class="btn-fixed btn btn-block btn-contact btn-primary" id="btn-contact">Pide presupuesto</a>

	<!-- Facebook Conversion Code for Entradas -->
	<script>
		(function() {
			var _fbq = window._fbq || (window._fbq = []);
			if (!_fbq.loaded) {
				var fbds = document.createElement('script');
				fbds.async = true;
				fbds.src = 'https://connect.facebook.net/en_US/fbds.js';
				var s = document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(fbds, s);
				_fbq.loaded = true;
			}
		})();
		window._fbq = window._fbq || [];
		window._fbq.push(['track', '6022865114446', {'value':'0.00','currency':'EUR'}]);
	</script>
	<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/tr?ev=6022865114446&amp;cd[value]=0.00&amp;cd[currency]=EUR&amp;noscript=1" /></noscript>

	<?php wp_footer(); ?>
<script>
	(function($) {
			var page_name = $('h1').text();
			$('.form_page_title').val(page_name);
	})( jQuery );
	</script>
</body>
</html>

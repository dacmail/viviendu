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
	<script src="//code.tidio.co/xtwm4iqk4edj6z9u876w2rh2baes1qfx.js"></script>
	<?php wp_footer(); ?>
</body>
</html>

	<footer id="footer" class="clearfix">
		<div class="container">
			<div class="row">
				<div class="col-sm-4">
					<a href="<?php echo home_url(); ?>" class="logo"><img src="<?php echo get_template_directory_uri(); ?>/images/logo-foot.png" alt="<?php bloginfo('name'); ?>"></a>
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
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', 'UA-65973785-1', 'auto');
		ga('send', 'pageview');
	</script>
	<?php wp_footer(); ?>
</body>
</html>
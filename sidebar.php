<div id="sidebar" class="sidebar col-sm-4 col-sm-offset-1">
	<div class="widget">
		<?php get_search_form(true); ?>
	</div>
	<div class="widget location">
		<?php $location_info = viviendu_location_info(get_tax_meta(get_queried_object()->term_id, 'viviendu_comercio_seccion_comercio'));  ?>
		<?php if (!empty($location_info['address'])): ?>
			<iframe
			    width="100%"
			    height="200px"
			    frameborder="0" style="border:0"
			    src="https://www.google.com/maps/embed/v1/search?key=AIzaSyAS54wTsApQr8UcJjKUI2vMAWE8Op91sUo
			      &q=<?php echo urlencode($location_info['address']) ?>">
			  </iframe>
		<?php endif ?>
		
	</div>
	<?php dynamic_sidebar("Barra Lateral"); ?>
</div>
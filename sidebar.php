<div id="sidebar" class="sidebar col-sm-4 col-sm-offset-1">
	<div class="widget">
		<?php get_search_form(true); ?>
	</div>
	<div class="widget location">
		<?php if (is_tax('comercio_seccion')): ?>
			<?php $location_info = viviendu_location_info(get_tax_meta(get_queried_object()->term_id, 'viviendu_comercio_seccion_comercio'));  ?>
		<?php elseif (is_tax('comercio')) : ?>
			<?php $location_info = viviendu_location_info(get_queried_object()->term_id);  ?>
		<?php endif ?>
		<?php if (!empty($location_info['address'])): ?>
			<iframe
			    width="100%"
			    height="200px"
			    frameborder="0" style="border:0"
			    src="https://www.google.com/maps/embed/v1/search?key=AIzaSyAS54wTsApQr8UcJjKUI2vMAWE8Op91sUo
			      &q=<?php echo urlencode($location_info['address']) ?>">
			</iframe>
			<ul class="company-data">
				<li class="address"><i class="fa fa-map-marker"></i> <?php echo $location_info['address']; ?></li>
				<li class="phone"><i class="fa fa-phone"></i> <?php echo $location_info['phone']; ?></li>
				<li class="url"><i class="fa fa-link"></i> <a rel="nofollow" href="<?php echo esc_url($location_info['url']); ?>"><?php echo esc_url($location_info['url']); ?></a></li>
			</ul>
		<?php endif ?>
		
	</div>
	<?php dynamic_sidebar("Barra Lateral"); ?>
</div>
<?php while ($links->have_posts()) : $links->the_post(); ?>
	<article class="link col-sm-6">
		<h3 class="link-title"><a href="<?php echo viviendu_tax_link(get_the_ID(), 'comercio_seccion'); ?>"><?php echo viviendu_tax_name(get_the_ID(), 'comercio'); ?></a></h3>
		<p class="text"><?php echo viviendu_the_text(get_the_ID()); ?></p>
		<p><a class="more" href="<?php echo viviendu_tax_link(get_the_ID(), 'comercio_seccion'); ?>">Ver más <i class="fa fa-angle-right"></i></a></p>
	</article>
<?php endwhile; ?>
<?php wp_reset_query(); ?>
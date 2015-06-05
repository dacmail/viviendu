<?php while ($related->have_posts()) : $related->the_post(); ?>
	<article class='catalogo catalogo-list col-sm-4' id="post-<?php the_ID(); ?>">
		<?php echo viviendu_slideshow('featured', viviendu_tax_link(get_the_ID(), 'comercio_seccion'), 3); ?>
		<h3 class="title nm"><?php echo viviendu_tax_anchor(get_the_ID(), 'comercio_seccion', viviendu_tax_name(get_the_ID(), 'comercio')); ?></h3>
		<h4 class="category"><?php echo viviendu_tax_anchor(get_the_ID(), 'category'); ?></h4>
	</article>
<?php endwhile; ?>
<?php wp_reset_query(); ?>
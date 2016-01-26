<?php $exclude_posts = array(); ?>
<?php $related_categories = array(); ?>
<?php while ($related->have_posts()) : $related->the_post(); ?>
	<article class='catalogo catalogo-list col-sm-4' id="post-<?php the_ID(); ?>">
		<?php echo viviendu_slideshow('featured', viviendu_tax_link(get_the_ID(), 'comercio_seccion'), 2); ?>
		<h3 class="title nm">
			<a href="<?php echo viviendu_tax_link(get_the_ID(), 'comercio_seccion'); ?>">
				<?php echo viviendu_tax_name(get_the_ID(), 'comercio'); ?>
				<span class="title-category"><?php echo viviendu_tax_name(get_the_ID(), 'category'); ?></span>
			</a>
		</h3>
	</article>
	<?php $exclude_posts[] = get_the_ID(); ?>
	<?php $related_categories = array_merge($related_categories,wp_get_post_terms(get_the_ID(), 'category')); ?>
	<?php $related_categories = array_merge($related_categories, wp_get_post_terms(get_the_ID(), 'product')); ?>
<?php endwhile; ?>
<?php $related_categories = array_unique($related_categories,SORT_REGULAR); ?>
<?php wp_reset_query(); ?>
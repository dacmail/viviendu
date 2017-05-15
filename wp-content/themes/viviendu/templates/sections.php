<?php while ($related->have_posts()) : $related->the_post(); ?>
	<div class='col-sm-4' id="post-<?php the_ID(); ?>">
		<article class="seccion-list">
			<?php if (!has_post_thumbnail()): ?>
				<?php viviendu_set_post_thumb(get_the_ID()); ?>
			<?php endif ?>
			<?php the_post_thumbnail('square'); ?>
			<h3 class="title">
				<a href="<?php echo viviendu_tax_link(get_the_ID(), 'comercio_seccion'); ?>">
					<span class="title-category"><?php echo viviendu_tax_name(get_the_ID(), 'category'); ?></span>
				</a>
			</h3>
		</article>
	</div>
<?php endwhile; ?>
<?php wp_reset_query(); ?>
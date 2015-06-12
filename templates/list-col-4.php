<div class="row">
<?php 
	if (!isset($featured)) {
		global $wp_query;
		$featured = $wp_query;
	}
?>
<?php while ($featured->have_posts()) : $featured->the_post(); ?>
	<article class='catalogo catalogo-list col-sm-3' id="post-<?php the_ID(); ?>">
		
		<?php echo viviendu_slideshow('featured', viviendu_tax_link(get_the_ID(), 'comercio_seccion'), 3); ?>
		<h3 class="title">
			<a href="<?php echo viviendu_tax_link(get_the_ID(), 'comercio_seccion'); ?>">
				<?php echo viviendu_tax_name(get_the_ID(), 'comercio'); ?>
				<span class="title-category"><?php echo viviendu_tax_name(get_the_ID(), 'category'); ?></span>
			</a>
		</h3>
		<div class="text">
			<?php echo viviendu_the_text(get_the_ID()); ?>
		</div>
	</article>
<?php endwhile; ?>
</div>

<?php wp_reset_query(); ?>
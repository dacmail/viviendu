<?php $secciones = get_terms("category", array(
	'orderby'    => 'id',
	'hide_empty' => 0
)); ?>
<?php foreach ($secciones as $seccion): ?>
	<?php $related = new WP_Query(array(
				'posts_per_page' => 1,
				'tax_query' => array(
						array(
							'taxonomy' => 'provincia',
							'field'    => 'ID',
							'terms'    => $provincia->term_id
						),
						array(
							'taxonomy' => 'category',
							'field'    => 'ID',
							'terms'    => $seccion->term_id
						)
				),
			)
		); ?>
	<?php while ($related->have_posts()) : $related->the_post(); ?>
		<div class='col-sm-4' id="post-<?php the_ID(); ?>">
			<article class="seccion-list">
				<?php if (!has_post_thumbnail()): ?>
					<?php viviendu_set_post_thumb(get_the_ID()); ?>
				<?php endif ?>
				<?php the_post_thumbnail('square'); ?>
				<h3 class="title">
					<a href="<?php echo viviendu_term_combi_link('seccion_provincia', $seccion->slug, $provincia->slug); ?>">
						<span class="title-category"><?php echo $seccion->name; ?></span>
					</a>
				</h3>
			</article>
		</div>
	<?php endwhile; ?>
	<?php wp_reset_query(); ?>
<?php endforeach ?>

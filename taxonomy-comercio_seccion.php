<?php get_header() ?>
<div id="container" class="comercio-seccion section">
	<div class="container">
		<div class="row">
			<div id="content" class="col-sm-7">
				<?php while ( have_posts() ) : the_post(); ?>
					<h1 class="title"><?php echo single_term_title(); ?></h1>
					<div class="text">
						<?php echo viviendu_get_paragraph(apply_filters('the_content',viviendu_comercio_seccion_content(get_queried_object()->term_id))); ?>
						<?php echo viviendu_slideshow('featured','', 0, true); ?>
						<?php echo viviendu_get_paragraph(apply_filters('the_content',viviendu_comercio_seccion_content(get_queried_object()->term_id)), false); ?>
					</div>
				<?php endwhile; ?>
				
			</div>
			<?php get_sidebar('comercio'); ?>
		</div>
	</div>
</div>
<?php get_footer() ?>
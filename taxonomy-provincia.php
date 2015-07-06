<?php get_header() ?>
<div id="container" class="provincia-seccion section">
	<div class="container">
		<div class="row">
			<div id="content" class="col-sm-7">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php $provincia = get_term(get_queried_object()->term_id, 'provincia' ); ?>
					<h1 class="title nm"><?php echo single_term_title(); ?></h1>
					<?php if(function_exists("kk_star_ratings")) : echo kk_star_ratings(viviendu_post_id('provincia',get_queried_object()->term_id)); endif; ?>
					<div class="text main">
						<?php echo viviendu_get_paragraph(apply_filters('the_content',$provincia->description)); ?>
						<h2 class="title">Secciones</h2>
						<div class="row">
							
							<?php include(locate_template('templates/sections-provincias.php')); ?>
						</div>
						<?php echo viviendu_get_paragraph(apply_filters('the_content',$provincia->description), false); ?>
					</div>
					<?php break; ?>
				<?php endwhile; ?>
			</div>
			<?php get_sidebar('provincia'); ?>
		</div>
	</div>
</div>
<?php get_footer() ?>
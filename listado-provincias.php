<?php /* Template Name: Listado de provincias */ ?>
<?php get_header() ?>
<div id="container" class="empresas-destacadas section">
	<div class="container">
		<div class="row">
			<div class="col-sm-7">
				<section class="section">
					<h2 class="title tit-sep">Casas prefabricadas en España</h2>
					<?php while (have_posts()) : the_post(); ?>
						<?php the_content( __('Leer m&aacute;s &raquo;', 'ungrynerd')); ?>
					<?php endwhile; ?>
					<?php $list = array('term' => 'provincia', 'class' => 'col-sm-4', 'icon' => 'fa-map-marker', 'args' => array('hide_empty' => 0)); ?>
					<?php include(locate_template('templates/list-terms.php')); ?>
				</section>
			</div>
			<?php get_sidebar('provincia'); ?>
		</div>
	</div>
</div>
<?php get_footer() ?>
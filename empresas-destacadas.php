<?php /* Template Name: Empresas destacadas */ ?>
<?php get_header() ?>
<div id="container" class="empresas-destacadas section">
	<div class="container">
		<div class="row">
			<div class="col-sm-7">
				<section class="section">
					<h2 class="title tit-sep">Casas prefabricadas y viviendas móviles destacadas</h2>
					<?php $featured = new WP_Query(array('meta_key' => '_ungrynerd_featured', 'meta_value' => 1, 'post_type'=> array('post'), 'posts_per_page' => -1)); ?>
					<?php $class='col-sm-4'; include(locate_template('templates/list-col-4.php')); ?>
				</section>
			</div>
			<?php get_sidebar('provincia'); ?>
		</div>
	</div>
</div>
<?php get_footer() ?>
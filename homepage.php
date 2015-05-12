<?php /* Template Name: Pagina de inicio */ ?>
<?php get_header() ?>
<div id="container" class="clearfix">
	<section id="featureds" class="container section">
		<h2 class="title">Lo más destacado en viviendu</h2>
		<p class="subtitle">A continuación te mostramos los productos más destacados que podemos ofrecerte. No dejes de buscar la casa de tus sueños.</p>
		<?php $featured = new WP_Query(array('meta_key' => '_ungrynerd_featured', 'meta_value' => 1, 'post_type'=> array('post'), 'posts_per_page' => 8)); ?>
		<?php include(locate_template('templates/list-col-4.php')); ?>
	</section>
	<section id="links" class="container section">
		<?php include(locate_template('templates/home-links.php')); ?>
	</section>
	<section id="secciones" class="container section">
		<div class="row">
			<div class="col-sm-6">
				<?php $list = array('term' => 'provincia', 'class' => 'col-sm-4', 'icon' => 'fa-map-marker', 'args' => array('hide_empty' => 0)); ?>
				<h2 class="title mini">Provincias en viviendu</h2>
				<p class="subtitle">Aquí tienes el listado de provicnias de viviendu.com, selecciona tu provincia y encuentra la casa que estás buscando</p>
				<?php include(locate_template('templates/list-terms.php')); ?>
			</div>
			<div class="col-sm-6">
				<?php $list = array('term' => 'product', 'class' => 'col-sm-6', 'icon' => 'fa-star', 'args' => array('hide_empty' => 0, 'orderby' => 'count', 'number' => 36, 'order' => 'desc')); ?>
				<h2 class="title mini">Tipos de casa en viviendu</h2>
				<p class="subtitle">Navega por las distintas secciones de nuestra web y descubre las mejores empresas para empezar tu proyecto de vivienda</p>
				<?php include(locate_template('templates/list-terms.php')); ?>
			</div>
		</div>
	</section>
</div>
<?php get_footer() ?>
<?php get_header() ?>
<div id="container" class="clearfix">
	<section id="featureds" class="container section">
		<h2 class="title">Estás buscando: <?php the_search_query(); ?></h2>
		<p class="subtitle">Has hecho una búsqueda con las palabras <strong><?php the_search_query(); ?></strong> y a continuación te mostramos los resultados que encajan con esos términos en Viviedu.com</p>
		<?php include(locate_template('templates/list-col-4.php')); ?>
	</section>
</div>
<?php get_footer() ?>
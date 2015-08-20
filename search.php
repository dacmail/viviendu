<?php get_header() ?>
<div id="container" class="clearfix">
	<section id="featureds" class="container section">
		<div class="row">
			<div id="content" class="col-sm-7">
				<h2 class="title">Estás buscando: <?php the_search_query(); ?></h2>
				<p class="subtitle">Has hecho una búsqueda con las palabras <strong><?php the_search_query(); ?></strong> y a continuación te mostramos los resultados que encajan con esos términos en Viviedu.com</p>
				<?php $class = 'col-sm-4'; ?>
				<?php include(locate_template('templates/list-col-4.php')); ?>
				<div class="pagination container">
					<?php
					global $wp_query;

					$big = 999999999; // need an unlikely integer

					echo paginate_links( array(
						'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
						'format' => '?paged=%#%',
						'current' => max( 1, get_query_var('paged') ),
						'total' => $wp_query->max_num_pages
					) );
					?>
				</div>
			</div>
			<?php get_sidebar('comercio'); ?>
		</div>
	</section>
	
</div>
<?php get_footer() ?>
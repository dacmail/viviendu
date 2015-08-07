<?php get_header() ?>
<div id="container" class="comercio-seccion section">
	<div class="container">
		<div class="row">
			<div id="content" class="col-sm-7">
				<?php while ( have_posts() ) : the_post(); ?>
					<h1 class="title"><?php echo single_term_title(); ?></h1>
					<?php $comercio = get_term(get_tax_meta(get_queried_object()->term_id, 'viviendu_comercio_seccion_comercio'), 'comercio' ); ?>
					<div class="row">
						<div class="col-sm-6 title-category main"><?php echo viviendu_tax_anchor(get_the_ID(), 'category'); ?></div>
						<div class="col-sm-6 ratings-wrap"><?php if(function_exists("kk_star_ratings")) : echo kk_star_ratings(viviendu_post_id('comercio_seccion',get_queried_object()->term_id)); endif; ?></div>
					</div>
					<div class="text main">
						<?php echo viviendu_get_paragraph(apply_filters('the_content',viviendu_comercio_seccion_content(get_queried_object()->term_id))); ?>
						<?php echo viviendu_slideshow('featured','', 0, true); ?>
						<?php echo viviendu_get_paragraph(apply_filters('the_content',viviendu_comercio_seccion_content(get_queried_object()->term_id)), false); ?>
					</div>
					<?php /* $cities = get_the_terms( get_the_ID(), 'post_tag' ); ?>
					<?php if (!empty($cities)): ?>
						<div class="row">
							<div class="col-sm-12"><h2 class="title mini"><?php echo $comercio->name; ?> en tu provincia</h2></div>
							<?php include(locate_template('templates/list-provincias.php')); ?>
						</div>
					<?php endif */ ?>
					<?php $products = get_the_terms( get_the_ID(), 'product' ); ?>
					<?php if (!empty($products)): ?>
						<div class="row">
							<div class="col-sm-12"><h2 class="title mini">Otras propuestas de casas prefabricadas y viviendas móviles</h2></div>
							<?php include(locate_template('templates/list-products.php')); ?>
						</div>
					<?php endif ?>
					<div class="row">
						<?php $related = new WP_Query(array(
										'posts_per_page' => 3,
										'tax_query' => array(
											array(
												'taxonomy' => 'comercio',
												'field'    => 'ID',
												'terms'    => $comercio->term_id,
											),
										'posts_per_archive_page' => 3,
										'orderby' => 'rand'
										),
									)); ?>
						<?php if ($related->post_count>0): ?>
							<div class="col-sm-12"><h2 class="title mini">Otros catálogos de <?php echo $comercio->name; ?></h2></div>
							<?php include(locate_template('templates/related.php')); ?>
							<div class="col-sm-12 more"><a href="<?php echo get_term_link($comercio); ?>">Ver todos los catálogos de <?php echo $comercio->name; ?> <i class="fa fa-angle-right"></i></a></div>
						<?php endif ?>
					</div>
				<?php endwhile; ?>
			</div>
			<?php get_sidebar('comercio'); ?>

		</div>
	</div>
</div>
<?php get_footer() ?>
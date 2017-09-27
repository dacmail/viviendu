<?php get_header() ?>
<div id="container" class="comercio-seccion section">
	<div class="container">
		<div class="row">
			<div id="content" class="col-sm-7">
				<?php while ( have_posts() ) : the_post(); ?>
					<h1 class="title"><?php echo single_term_title(); ?></h1>
					<?php $comercio = get_term(get_tax_meta(get_queried_object()->term_id, 'viviendu_comercio_seccion_comercio', true), 'comercio' ); ?>
					<div class="row">
						<div class="col-sm-6 title-category main"><?php echo viviendu_tax_anchor(get_the_ID(), 'category'); ?></div>
						<div class="col-sm-6 ratings-wrap"><?php if(function_exists("kk_star_ratings")) : echo kk_star_ratings(viviendu_post_id('comercio_seccion',get_queried_object()->term_id)); endif; ?></div>
					</div>
					<div class="text main">
						<?php if (!get_post_meta(get_the_ID(),'_ungrynerd_baja', true ) || !get_post_meta(get_the_ID(),'_ungrynerd_no_cta', true )) :?>
 							<div class="show-on-mobile"><p><a href="#popup_contacto" class="btn btn-block btn-contact btn-primary" id="btn-contact-content-mobile">Contactar</a></p></div>
 						<?php endif; ?>
						<?php echo viviendu_get_paragraph(apply_filters('the_content',viviendu_comercio_seccion_content(get_queried_object()->term_id))); ?>
						<?php if (!get_post_meta(get_the_ID(),'_ungrynerd_baja', true )) :?>
							<?php echo viviendu_slideshow('featured','', 25, true); ?>
						<?php endif; ?>
						<?php echo viviendu_get_paragraph(apply_filters('the_content',viviendu_comercio_seccion_content(get_queried_object()->term_id)), false); ?>
					</div>
					<?php if (!get_post_meta(get_the_ID(),'_ungrynerd_baja', true ) || !get_post_meta(get_the_ID(),'_ungrynerd_no_cta', true )) :?>
						<div class="row voffset30">
							<?php $location_info = viviendu_location_info(get_tax_meta(get_queried_object()->term_id, 'viviendu_comercio_seccion_comercio', true));  ?>
							<?php if (!empty($location_info['url'])): ?>
								<div class="col-sm-12">
									<p><a href="#popup_contacto" class="btn btn-block btn-contact btn-primary" id="btn-contact-content">Contactar con la empresa</a></p>
								</div>
							<?php else: ?>
								<div class="col-sm-6 col-sm-offset-3">
									<p><a href="#popup_contacto" class="btn btn-block btn-contact btn-primary" id="btn-contact">Contactar con la empresa</a></p>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php $products = get_the_terms( get_the_ID(), 'oferta' ); ?>
					<?php if (!empty($products)): ?>
						<div class="row">
							<div class="col-sm-12"><h2 class="title mini">Otras propuestas de casas prefabricadas y viviendas móviles</h2></div>
							<?php include(locate_template('templates/list-products.php')); ?>
						</div>
					<?php endif ?>
					<div class="row voffset30">
						<?php $related = new WP_Query(array(
										'posts_per_page' => 3,
										'tax_query' => array(
											array(
												'taxonomy' => 'comercio',
												'field'    => 'ID',
												'terms'    => $comercio->term_id,
											),
										'posts_per_archive_page' => 3,
										'orderby' => 'rand',
										'post__not_in' => array(get_the_ID())
										),
									)); ?>
						<?php if ($related->post_count>1): ?>
							<div class="col-sm-12"><h2 class="title mini">Catálogos de <?php echo $comercio->name; ?></h2></div>
							<?php include(locate_template('templates/related.php')); ?>
							<div class="col-sm-12 more"><a href="<?php echo get_term_link($comercio); ?>">Ver todos los catálogos de <?php echo $comercio->name; ?> <i class="fa fa-angle-right"></i></a></div>
						<?php endif ?>
					</div>
				<?php endwhile; ?>
			</div>
			<?php get_sidebar('comercio_seccion'); ?>

		</div>
	</div>
</div>
<?php get_footer() ?>

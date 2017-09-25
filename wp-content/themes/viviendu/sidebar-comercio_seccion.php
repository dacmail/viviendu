<div id="sidebar" class="sidebar col-sm-4 offset-sm-1">
	<div class="widget">
		<?php get_search_form(true); ?>
	</div>
	<div class="widget share">
		<div class="addthis_sharing_toolbox"></div>
	</div>
	<?php $products = get_the_terms(get_the_ID(), 'oferta' ); ?>
	<?php if (!empty($products)): ?>
		<div class="widget">
			<h2 class="title mini"><?php echo single_term_title(); ?></h2>
			<ul class="list-terms provincias row">
			<?php foreach ($products as $product) : ?>
			    <li class="col-sm-6">
					<i class="fa fa-star"></i><?php echo $product->name; ?>
				</li>
			<?php endforeach; ?>
			</ul>
		</div>
	<?php endif ?>
	<?php if (!get_post_meta(get_the_ID(),'_ungrynerd_baja', true )  || !get_post_meta(get_the_ID(),'_ungrynerd_no_cta', true )) :?>
	<div class="widget location">
		<?php if (is_tax('comercio_seccion')): ?>
			<?php $location_info = viviendu_location_info(get_tax_meta(get_queried_object()->term_id, 'viviendu_comercio_seccion_comercio', true));  ?>
		<?php elseif (is_tax('comercio')) : ?>
			<?php $location_info = viviendu_location_info(get_queried_object()->term_id);  ?>
		<?php elseif (is_tag()) : ?>
			<?php $location_info = viviendu_location_info(get_tax_meta(get_queried_object()->term_id, 'viviendu_comercio', true));  ?>
		<?php endif ?>
	</div>
	<?php endif; ?>

	<?php dynamic_sidebar("Barra Lateral"); ?>
</div>



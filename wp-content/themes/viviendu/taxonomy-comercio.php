<?php get_header() ?>
<div id="container" class="comercio-seccion section">
	<div class="container">
		<div class="row">
			<div id="content" class="col-sm-7">
				<?php $comercio = get_term(get_queried_object()->term_id, 'comercio' ); ?>
				<h1 class="title nm"><?php echo single_term_title(); ?></h1>
				<?php if(function_exists("kk_star_ratings")) : echo kk_star_ratings(viviendu_post_id('comercio',get_queried_object()->term_id)); endif; ?>
				<div class="text main">
					<?php echo viviendu_get_paragraph(apply_filters('the_content',$comercio->description)); ?>
					<h2 class="title mini tit-sep"><?php echo single_term_title(); ?> te ofrece</h2>
					<div class="row">
						<?php $related = new WP_Query(array(
										'posts_per_page' => -1,
										'tax_query' => array(
											array(
												'taxonomy' => 'comercio',
												'field'    => 'ID',
												'terms'    => $comercio->term_id
											),
										'posts_per_archive_page' => -1,
										'orderby' => 'rand'
										),
									)); ?>
						<?php include(locate_template('templates/sections.php')); ?>
					</div>
					<?php echo viviendu_get_paragraph(apply_filters('the_content',$comercio->description), false); ?>
				</div>
				<div class="row">
					<?php $related = new WP_Query(array(
									'posts_per_page' => 3,
									'posts_per_archive_page' => 3,
									'orderby' => 'rand'
								)); ?>
					<?php if ($related->post_count>0): ?>
						<div class="col-sm-12"><h2 class="title mini tit-sep">Catálogos relacionados con <?php echo $comercio->name; ?></h2></div>
						<?php include(locate_template('templates/related.php')); ?>
					<?php endif ?>
				</div>
			</div>
			<?php get_sidebar('comercio_seccion'); ?>
		</div>
	</div>
</div>
<?php get_footer() ?>
<?php get_header() ?>
<div id="container" class="provincia-seccion section">
	<div class="container">
		<div class="row">
			<div id="content" class="col-sm-7">
				<?php $seccion = get_term(get_queried_object()->term_id, 'category'); ?>
				<h1 class="title nm"><?php echo single_term_title(); ?></h1>
				<div class="text main">
					<?php echo viviendu_get_paragraph(apply_filters('the_content', $seccion->description)); ?>
					<?php
					$featured_companies = get_term_meta($seccion->term_id, 'featured_companies', true);
					?>
					<?php if ($featured_companies): ?>
						<div class="row premium-featured">
							<div class="col-sm-12">
								<h2 class="title mini tit-sep">Empresas PREMIUM en <?php echo $seccion->name; ?></h2>
							</div>
							<?php foreach ($featured_companies as $company) : ?>
								<article class='catalogo col-sm-4'>
									<h3 class="title nm">
										<?php $company = get_term($company, 'comercio'); ?>
										<a href="<?php echo get_term_link($company, 'comercio') ?>">
											<?php echo $company->name; ?>
											<span class="title-category">Empresa destacada</span>
										</a>
									</h3>
								</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="row">
						<?php $related = new WP_Query(array(
							'posts_per_page' => -1,
							'meta_key' => '_ungrynerd_section_featured',
							'meta_value' => 1,
							'tax_query' => array(
								array(
									'taxonomy' => 'category',
									'field'    => 'ID',
									'terms'    => $seccion->term_id,
								),
								'orderby' => 'modified'
							),
						)); ?>
						<?php if ($related->post_count > 0) : ?>
							<div class="col-sm-12">
								<h2 class="title mini tit-sep">Empresas destacadas en <?php echo $seccion->name; ?></h2>
							</div>
							<?php include(locate_template('templates/related.php')); ?>
						<?php endif ?>
					</div>
					<?php echo viviendu_get_paragraph(apply_filters('the_content', $seccion->description), false); ?>
				</div>
				<div class="row">
					<?php $links = new WP_Query(array(
						'posts_per_page' => -1,
						'post__not_in' => $exclude_posts,
						'tax_query' => array(
							array(
								'taxonomy' => 'category',
								'field'    => 'ID',
								'terms'    => $seccion->term_id,
							),
							'orderby' => 'modified',
						),
					)); ?>
					<?php if ($links->post_count > 0) : ?>
						<div class="col-sm-12">
							<h2 class="title mini tit-sep">Más empresas en <?php echo $seccion->name; ?></h2>
						</div>
						<?php include(locate_template('templates/links.php')); ?>
					<?php endif ?>
				</div>
			</div>
			<?php get_sidebar('provincia'); ?>
		</div>
	</div>
</div>
<?php get_footer() ?>

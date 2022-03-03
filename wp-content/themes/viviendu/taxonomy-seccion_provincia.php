<?php get_header() ?>
<div id="container" class="provincia-seccion section">
	<div class="container">
		<div class="row">
			<div id="content" class="col-sm-7">
				<?php $seccion_provincia = get_term(get_queried_object()->term_id, 'seccion_provincia'); ?>
				<h1 class="title nm"><?php echo single_term_title(); ?></h1>
				<div class="text main">
					<?php echo viviendu_get_paragraph(apply_filters('the_content', $seccion_provincia->description)); ?>
				</div>
				<?php $featured_companies = get_term_meta($seccion_provincia->term_id, 'featured_companies', true); ?>
				<?php if ($featured_companies) : ?>
					<div class="row premium-featured">
						<div class="col-sm-12">
							<h2 class="title mini tit-sep">Empresas destacadas en <?php echo $seccion_provincia->name; ?></h2>
						</div>
						<?php foreach ($featured_companies as $company) : ?>
							<?php include(locate_template('templates/list-premium.php')) ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php $htitle = 'h2'; ?>
				<h2 class="title mini tit-sep">Empresas de <?php echo $seccion_provincia->name; ?></h2>
				<?php include(locate_template('templates/list-col-4.php')); ?>
				<div class="text main">
					<?php echo viviendu_get_paragraph(apply_filters('the_content', $seccion_provincia->description), false); ?>
				</div>
			</div>
			<?php get_sidebar('seccion_provincia'); ?>
		</div>
	</div>
</div>
<?php get_footer() ?>

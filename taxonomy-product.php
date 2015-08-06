<?php get_header() ?>
<div id="container" class="provincia-seccion section">
	<div class="container">
		<div class="row">
			<div id="content" class="col-sm-7">
				<?php $seccion = get_term(get_queried_object()->term_id, 'product' ); ?>
				<h1 class="title nm"><?php echo single_term_title(); ?></h1>
				<div class="text main">
					<?php echo viviendu_get_paragraph(apply_filters('the_content',$seccion->description)); ?>
					<div class="row">
						<?php $related = new WP_Query(array(
									'posts_per_page' => 6,
									'tax_query' => array(
										array(
											'taxonomy' => 'product',
											'field'    => 'ID',
											'terms'    => $seccion->term_id,
										),
									'orderby' => 'modified'
									),
								)); ?>
						<?php if ($related->post_count>0): ?>
							<div class="col-sm-12"><h2 class="title mini tit-sep">Empresas destacadas en <?php echo $seccion->name; ?></h2></div>
							<?php include(locate_template('templates/related.php')); ?>
						<?php endif ?>
					</div>
					<?php echo viviendu_get_paragraph(apply_filters('the_content',$seccion->description), false); ?>
				</div>
				<div class="row">
					<?php $links = new WP_Query(array(
									'posts_per_page' => 6,
									'offset' => 6,
									'tax_query' => array(
										array(
											'taxonomy' => 'product',
											'field'    => 'ID',
											'terms'    => $seccion->term_id,
										),
									'orderby' => 'modified'
									),
								)); ?>
					<?php if ($links->post_count>0): ?>
						<div class="col-sm-12"><h2 class="title mini tit-sep">Más empresas en <?php echo $seccion->name; ?></h2></div>
						<?php include(locate_template('templates/links.php')); ?>
					<?php endif ?>
				</div>
				
					
			</div>
			<?php get_sidebar('provincia'); ?>
		</div>
	</div>
</div>
<?php get_footer() ?>
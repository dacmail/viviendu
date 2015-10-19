<?php get_header() ?>
<div id="container" class="provincia-seccion section">
	<div class="container">
		<div class="row">
			<div id="content" class="col-sm-7">
				<?php $provincia = get_term(get_queried_object()->term_id, 'provincia' ); ?>
				<h1 class="title nm">Casas prefabricadas en <?php echo single_term_title(); ?></h1>
				<div class="text main">
					<?php echo viviendu_get_paragraph(apply_filters('the_content',$provincia->description)); ?>
					<h2 class="title mini tit-sep">Tipos de casas prefabricadas en <?php echo single_term_title(); ?></h2>
					<div class="row">
						<?php include(locate_template('templates/sections-provincias.php')); ?>
					</div>
					<?php echo viviendu_get_paragraph(apply_filters('the_content',$provincia->description), false); ?>
				</div>
				<div class="row">
					<?php $related = new WP_Query(array(
									'posts_per_page' => 3,
									'tax_query' => array(
										array(
											'taxonomy' => 'provincia',
											'field'    => 'ID',
											'terms'    => $provincia->term_id,
										),
									'posts_per_archive_page' => 3,
									'orderby' => 'rand'
									),
								)); ?>
					<?php if ($related->post_count>0): ?>
						<div class="col-sm-12"><h2 class="title mini tit-sep">Empresas de casas prefabricadas en <?php echo $provincia->name; ?></h2></div>
						<?php include(locate_template('templates/related.php')); ?>
					<?php endif ?>
				</div>
				
					
			</div>
			<?php get_sidebar('provincia'); ?>
		</div>
	</div>
</div>
<?php get_footer() ?>
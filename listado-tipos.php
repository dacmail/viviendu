<?php /* Template Name: Tipos de casas */ ?>
<?php get_header() ?>
<div id="container" class="empresas-destacadas section">
	<div class="container">
		<div class="row">
			<div class="col-sm-7">
				<section class="section">
					<h2 class="title tit-sep">Tipos de casas prefabricadas</h2>
					<?php $list = array('term' => 'product', 'class' => 'col-sm-6', 'icon' => 'fa-star', 'args' => array('hide_empty' => 0)); ?>
					<?php include(locate_template('templates/list-terms.php')); ?>
					<?php $list = array('term' => 'category', 'class' => 'col-sm-6', 'icon' => 'fa-star', 'args' => array('hide_empty' => 0)); ?>
					<?php include(locate_template('templates/list-terms.php')); ?>
				</section>
			</div>
			<?php get_sidebar('provincia'); ?>
		</div>
	</div>
</div>
<?php get_footer() ?>
<?php /* Template Name: Pagina de inicio */ ?>
<?php get_header() ?>
<div id="container" class="clearfix">
	<section id="featureds" class="container section">
		<h2 class="title">Lo más destacado en viviendu</h2>
		<p class="subtitle">A continuación te mostramos los productos más destacados que podemos ofrecerte. No dejes de buscar la casa de tus sueños.</p>
		<?php $featured = new WP_Query(array('meta_key' => '_ungrynerd_featured', 'meta_value' => 1, 'post_type'=> array('post'), 'posts_per_page' => 8)); ?>
		<?php include(locate_template('templates/list-col-4.php')); ?>
	</section>
	<section class="section" id="whatis">
		<div class="container">
			<div class="row">
				<div class="col-md-8">
					<h2 class="title">¿Qué es viviendu?</h2>
					<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ducimus magni aliquid culpa quidem tempore rem accusamus, asperiores consequatur quis quod odio tenetur a ad possimus nostrum itaque aliquam et illo!</p>
					<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ducimus magni aliquid culpa quidem tempore rem accusamus!</p>

				</div>
				<div class="col-md-4">
					<img src="<?php echo get_template_directory_uri(); ?>/images/whatis.jpg" alt="¿Qué es viviendu?">
				</div>
			</div>
		</div>
	</section>
	<section id="links" class="container section">
		<?php include(locate_template('templates/home-links.php')); ?>
	</section>
	<section class="section" id="howto">
		<div class="container">
			<div class="row">
				<div class="col-md-12"><h2 class="title">¿Cómo usar viviendu?</h2></div>
				<div class="col-md-4 howto-item">
					<img src="<?php echo get_template_directory_uri(); ?>/images/icon1.png" alt="Configura">
					<h3 class="title mini">Configura</h3>
					<div class="text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi, fuga? </div>
				</div>
				<div class="col-md-4 howto-item">
					<img src="<?php echo get_template_directory_uri(); ?>/images/icon1.png" alt="Configura">
					<h3 class="title mini">Configura</h3>
					<div class="text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi, fuga? </div>
				</div>
				<div class="col-md-4 howto-item">
					<img src="<?php echo get_template_directory_uri(); ?>/images/icon1.png" alt="Configura">
					<h3 class="title mini">Configura</h3>
					<div class="text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi, fuga? </div>
				</div>
			</div>
		</div>
	</section>
	<section id="secciones" class="container section">
		<div class="row">
			<div class="col-sm-5">
				<?php $list = array('term' => 'provincia', 'class' => 'col-sm-4', 'icon' => 'fa-map-marker', 'args' => array('hide_empty' => 0)); ?>
				<h2 class="title mini nm">Provincias en viviendu</h2>
				<p class="subtitle">Aquí tienes el listado de provicnias de viviendu.com, selecciona tu provincia y encuentra la casa que estás buscando</p>
				<?php include(locate_template('templates/list-terms.php')); ?>
			</div>
			<div class="col-sm-6 col-sm-offset-1">
				<?php $list = array('term' => 'product', 'class' => 'col-sm-6', 'icon' => 'fa-star', 'args' => array('hide_empty' => 0, 'orderby' => 'count', 'number' => 36, 'order' => 'desc')); ?>
				<h2 class="title mini nm">Tipos de casa en viviendu</h2>
				<p class="subtitle">Navega por las distintas secciones de nuestra web y descubre las mejores empresas para empezar tu proyecto de vivienda</p>
				<?php include(locate_template('templates/list-terms.php')); ?>
			</div>
		</div>
	</section>
</div>
<?php get_footer() ?>
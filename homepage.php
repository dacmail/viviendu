<?php /* Template Name: Pagina de inicio */ ?>
<?php get_header() ?>
<div id="container" class="clearfix">
	<section id="featureds" class="container section">
		<h2 class="title nm">Casas prefabricadas y viviendas móviles destacadas</h2>
		<p class="subtitle">Consulta en Viviendu las propuestas de las principales empresas del sector para descubrir las opciones de casas prefabricadas y viviendas móviles más originales, así como las que ofrecen mayores prestaciones.</p>
		<?php $featured = new WP_Query(array('meta_key' => '_ungrynerd_featured', 'meta_value' => 1, 'post_type'=> array('post'), 'posts_per_page' => 8)); ?>
		<?php include(locate_template('templates/list-col-4.php')); ?>
	</section>
	<section class="section" id="whatis">
		<div class="container">
			<div class="row">
				<div class="col-md-9">
					<h2 class="title">¿Qué es Viviendu?</h2>
					<p><strong>Viviendu</strong> es la web especializada en <strong>casas prefabricadas y viviendas móviles</strong>. Ponemos a tu disposición los catálogos de fotos e información de contacto de las principales empresas del sector para ayudarte a <strong>encontrar la casa de tus sueños</strong>. </p>
					<p>Descubre en <strong>Viviendu</strong> todas las opciones de <strong>viviendas prefabricadas y casas móviles</strong> que existen en el mercado. <strong>Busca y compara</strong> entre la más amplia oferta en casas de madera, cabañas de madera, casetas de madera, casas modulares, caravanas, autocaravanas, casas ecológicas, cabañas en árboles, eco estructuras, pérgolas, porches, cenadores, casas increíbles...</p> 
					<p>Ha llegado el momento de hacer realidad tu sueño. ¡Embárcate en el apasionante proyecto de <strong>comprar tu casa ideal</strong> y disfruta la experiencia!. </p>

				</div>
				<div class="col-md-3">
					<img src="<?php echo get_template_directory_uri(); ?>/images/icono_casa.jpg" alt="¿Qué es viviendu?">
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
				<div class="col-md-12"><h2 class="title text-center">¿Cómo usar viviendu?</h2></div>
				<div class="col-md-6 howto-item">
					<img src="<?php echo get_template_directory_uri(); ?>/images/search.png" alt="Configura">
					<h3 class="title mini">Viviendu para usuarios</h3>
					<div class="text">Consulta en una sola web todas las opciones de casas prefabricadas y viviendas móviles que existen en el mercado. ¡Continuamente ampliamos nuestro directorio con nuevas propuestas!. <a href="<?php echo get_permalink(7478); ?>">Saber más</a></div>
				</div>
				<div class="col-md-6 howto-item">
					<img src="<?php echo get_template_directory_uri(); ?>/images/statistic.png" alt="Configura">
					<h3 class="title mini">Viviendu para empresas</h3>
					<div class="text">Miles de usuarios buscan en Viviendu las mejores propuestas en casas prefabricadas y viviendas móviles. Contacta con potenciales compradores y aumenta tus ventas.. <a href="<?php echo get_permalink(7476); ?>">Saber más</a></div>
				</div>
			</div>
		</div>
	</section>
	<section id="secciones" class="container section">
		<div class="row">
			<div class="col-sm-5">
				<?php $list = array('term' => 'provincia', 'class' => 'col-sm-4', 'icon' => 'fa-map-marker', 'args' => array('hide_empty' => 0)); ?>
				<h2 class="title mini nm">Casas prefabricadas en tu provincia</h2>
				<p class="subtitle">Como norma general las empresas que venden casas prefabricadas y viviendas móviles ofrecen sus servicios en toda España. Consulta a las empresas para visitar sus instalaciones y zonas de exposición.</p>
				<?php include(locate_template('templates/list-terms.php')); ?>
			</div>
			<div class="col-sm-6 col-sm-offset-1">
				<?php $list = array('term' => 'product', 'class' => 'col-sm-6', 'icon' => 'fa-star', 'args' => array('hide_empty' => 0, 'orderby' => 'count', 'number' => 36, 'order' => 'desc')); ?>
				<h2 class="title mini nm">Tipos de casas prefabricadas</h2>
				<p class="subtitle">Las casas prefabricadas y viviendas móviles pueden ser de muchos tipos y en Viviendu los tenemos todos. Accede directamente a las opciones de viviendas que mejor se adaptan a tus necesidades y preferencias.</p>
				<?php include(locate_template('templates/list-terms.php')); ?>
			</div>
		</div>
	</section>
</div>
<?php get_footer() ?>
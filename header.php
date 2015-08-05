<!DOCTYPE html>
<html <?php language_attributes(); ?>
<head>
	<link href='http://fonts.googleapis.com/css?family=Roboto:400,300,700,900|Roboto+Condensed:400,700,300' rel='stylesheet' type='text/css'>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/favicon.png" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php if (is_front_page()) : ?>
		<section class="jumbo">
			<div class="top-header">
				<div class="container">
					<a href="<?php echo home_url(); ?>" class="logo"><img src="<?php echo get_template_directory_uri(); ?>/images/logo.png" alt="<?php bloginfo('name'); ?>"></a>
					<?php wp_nav_menu(array('container' => 'nav', 'container_id' => 'top-menu', 'container_class' => 'top-menu', 'theme_location' => 'top')); ?>
				</div>
			</div>
			<div class="main-search">
				<div class="container text-center">
					<h1>Encuentra la casa de tus sueños</h1>
					<h2>Busca y compara entre las mejores propuestas de casas prefabricadas y viviendas móviles.</h2>
					<form action="<?php echo home_url(); ?>" method="get">
					<input type="text" id="s" name="s" class="term" placeholder="¿Qué buscas?">
					<button type="submit" class="btn">Buscar</button>
					</form>
				</div>
			</div>
			<ul class="slide cycle-slideshow" data-cycle-slides="> .item" data-cycle-timeout="30000">
				<li class="item" style="background-image:url(<?php echo get_template_directory_uri(); ?>/images/slide1.jpg);"></li>
				<li class="item" style="background-image:url(<?php echo get_template_directory_uri(); ?>/images/slide2.jpg);"></li>
			</ul>
		</section>
	<?php endif ?>
	<header id="header" class="clearfix">
		<div class="container">
			<a class="logo" href="<?php echo home_url(); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/logo-mini.png" alt="<?php bloginfo('name'); ?>" /></a>
			<?php wp_nav_menu(array('container' => 'nav', 'container_id' => 'main-menu', 'container_class' => '', 'theme_location' => 'main')); ?>
			<a href="#" class="nav-toggle"><i class="fa fa-bars"></i></a>
		</div>
	</header>
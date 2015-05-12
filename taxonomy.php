<?php get_header() ?>
<div id="container" class="clearfix">
	<section id="featureds" class="section container">
		<h2 class="title"><?php $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); echo $term->name; ?></h2>
		<p class="subtitle">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Saepe culpa beatae ut, iste labore! Unde possimus deleniti omnis dolorem vitae itaque recusandae temporibus error quas fugit, delectus cum! Autem, aperiam.</p>
		<?php include(locate_template('templates/list-col-4.php')); ?>
	</section>
</div>
<?php get_footer() ?>
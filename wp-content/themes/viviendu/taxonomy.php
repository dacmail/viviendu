<?php get_header() ?>
<div id="container" class="clearfix">
	<section id="featureds" class="section container">
		<h2 class="title"><?php $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); echo $term->name; ?></h2>
		<div class="subtitle sep"><?php echo viviendu_get_paragraph(apply_filters('the_content',$term->description)); ?></div>
		<?php include(locate_template('templates/list-col-4.php')); ?>
	</section>
</div>
<?php get_footer() ?>
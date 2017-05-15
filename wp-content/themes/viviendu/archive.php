<?php get_header() ?>
<div id="container" class="clearfix">
	<section id="featureds" class="section container">
		<h2 class="title">Archivo</h2>
		<p class="subtitle">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Saepe culpa beatae ut, iste labore! Unde possimus deleniti omnis dolorem vitae itaque recusandae temporibus error quas fugit, delectus cum! Autem, aperiam.</p>
		<?php include(locate_template('templates/list-col-4.php')); ?>
	</section>
	<div class="pagination container">
		<?php
		global $wp_query;

		$big = 999999999; // need an unlikely integer

		echo paginate_links( array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'current' => max( 1, get_query_var('paged') ),
			'total' => $wp_query->max_num_pages
		) );
		?>
	</div>
</div>
<?php get_footer() ?>
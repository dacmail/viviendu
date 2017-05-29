<?php /* Template Name: Pagina sin sidebar  */ ?>
<?php get_header() ?>
<div id="container" class="page section">
	<div class="container">
		<div class="row">
			<div id="content" class="col-md-10 col-md-offset-1 col-sm-12">
				<?php get_template_part( 'loop', 'single' ); ?>
			</div>
		</div>
	</div>
</div>
<?php get_footer() ?>

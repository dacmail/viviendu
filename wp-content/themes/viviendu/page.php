<?php get_header() ?>
<div id="container" class="page section">
	<div class="container">
		<div class="row">
			<div id="content" class="col-sm-7">
				<?php get_template_part( 'loop', 'single' ); ?>
			</div>
			<?php get_sidebar('page'); ?>
		</div>
	</div>
</div>
<?php get_footer() ?>
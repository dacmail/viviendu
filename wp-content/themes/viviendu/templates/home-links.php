<h2 class="title nm"><?php echo get_post_meta(get_the_ID(), '_ungrynerd_main_title', true); ?></h2>
<div class="subtitle tit-sep"><?php echo get_post_meta(get_the_ID(), '_ungrynerd_subtitle', true); ?></div>
<div class="row">
	<?php while (have_rows('block', get_the_ID())) : the_row();  ?>
		<div class="link col-sm-4">
			<h3 class="link-title"><a href="<?php echo get_sub_field('_untrynerd_link_href'); ?>"><?php echo get_sub_field('_ungrynerd_link_title') ?></a></h3>
			<p class="text"><?php echo get_sub_field('_ungrynerd_link_text'); ?></p>
			<p><a class="more" href="<?php echo get_sub_field('_untrynerd_link_href'); ?>">Ver más <i class="fa fa-angle-right"></i></a></p>
		</div>
	<?php endwhile ?>
</div>
<?php wp_reset_query(); ?>

<?php 
	$titles = get_post_meta(get_the_ID(), '_ungrynerd_link_title', true );
	$texts = get_post_meta(get_the_ID(), '_ungrynerd_link_text', true );
	$hrefs = get_post_meta(get_the_ID(), '_ungrynerd_link_href', true );
	$texts_keys = array_keys($texts);
	$hrefs_keys = array_keys($hrefs);
?>
<h2 class="title nm"><?php echo get_post_meta(get_the_ID(), '_ungrynerd_main_title', true ); ?></h2>
<div class="subtitle"><?php echo get_post_meta(get_the_ID(), '_ungrynerd_subtitle', true ); ?></div>
<div class="row">
	<?php $i=0; ?>
	<?php foreach ($titles as $key => $title) : ?>
		<div class="link col-sm-4">
			<h3 class="link-title"><a href="<?php echo $hrefs[$hrefs_keys[$i]]; ?>"><?php echo $title ?></a></h3>
			<p class="text"><?php echo $texts[$texts_keys[$i]]; ?></p>
			<p><a class="more" href="<?php echo $hrefs[$hrefs_keys[$i]]; ?>">Ver más <i class="fa fa-angle-right"></i></a></p>
		</div>
		<?php $i++; ?>
	<?php endforeach; ?>
</div>
<?php wp_reset_query(); ?>
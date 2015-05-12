<?php 
	$titles = get_post_meta(get_the_ID(), '_ungrynerd_link_title', true ); 
	$texts = get_post_meta(get_the_ID(), '_ungrynerd_link_text', true ); 
	$hrefs = get_post_meta(get_the_ID(), '_ungrynerd_link_href', true ); 
?>
<h2 class="title"><?php echo get_post_meta(get_the_ID(), '_ungrynerd_main_title', true ); ?></h2>
<div class="subtitle"><?php echo get_post_meta(get_the_ID(), '_ungrynerd_subtitle', true ); ?></div>
<div class="row">
	<?php foreach ($titles as $key => $title) : ?>
		<div class="link col-sm-4">
			<h3 class="link-title"><a href="<?php echo $hrefs[$key]; ?>"><?php echo $title ?></a></h3>
			<p class="text"><?php echo $texts[$key]; ?></p>
			<p><a class="more" href="<?php echo $hrefs[$key]; ?>">Ver más <i class="fa fa-angle-right"></i></a></p>
		</div>
	<?php endforeach; ?>
</div>
<?php wp_reset_query(); ?>
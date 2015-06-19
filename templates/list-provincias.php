<ul class="list-terms provincias clearfix">
<?php foreach ($cities as $city) : ?>
    <li class="col-sm-4">
		<a href="<?php echo get_term_link($city); ?>"><i class="fa fa-map-marker"></i><?php echo $city->name; ?></a>
    <?php endforeach; ?>
</ul>
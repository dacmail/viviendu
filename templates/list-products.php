<ul class="list-terms provincias clearfix">
<?php foreach ($products as $product) : ?>
    <li class="col-sm-4">
		<a href="<?php echo get_term_link($product); ?>"><i class="fa fa-star"></i><?php echo $product->name; ?></a>
    <?php endforeach; ?>
</ul>
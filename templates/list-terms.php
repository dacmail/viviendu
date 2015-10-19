<?php $terms = get_terms($list['term'], $list['args']); ?>
<?php if (!empty($terms) && !is_wp_error($terms)) : ?>
	<ul class="list-terms row">
    <?php foreach ($terms as $term) : ?>
	    <li class="<?php echo $list['class']; ?>">
			<a href="<?php echo get_term_link($term); ?>"><i class="fa <?php echo $list['icon'] ?>"></i><?php echo $term->name; ?></a>
	    </li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>
<?php
	//Includes
	require get_template_directory() . '/meta-box/meta-box.php';
	include get_template_directory() . '/inc/meta-boxes.php';
	include get_template_directory() . '/inc/assets.php';
	include get_template_directory() . '/inc/Tax-meta-class/migration/tax_to_term_meta.php';
	include get_template_directory() . '/inc/Tax-meta-class/Tax-meta-class.php';
	include get_template_directory() . '/inc/actions.php';
	include get_template_directory() . '/inc/config.php';
	include get_template_directory() . '/inc/taxonomies.php';
	include get_template_directory() . '/inc/posts.php';
	include get_template_directory() . '/inc/helpers.php';
	if (WP_DEBUG) {
		include get_template_directory() . '/inc/development.php';
	} else {
		include get_template_directory() . '/inc/production.php';
	}

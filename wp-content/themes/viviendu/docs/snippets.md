

'//Cambia nombres de comercios_seccion
$terms = get_terms(array('comercio_seccion'));
foreach ($terms as $term) {
	$comercio_id = get_tax_meta($term->term_id, 'viviendu_comercio_seccion_comercio', true); 
	$comercio = get_term( $comercio_id, 'comercio');
	$seccion_id = get_tax_meta($term->term_id, 'viviendu_comercio_seccion_seccion', true); 
	$seccion = get_term( $seccion_id, 'category');
	wp_update_term($term->term_id, 'comercio_seccion', array(
		'name' => $seccion->name . ' en ' . $comercio->name,
	));
}


'//Actualiza tags de todos los posts
$query = new WP_Query(array('post_type' => 'post', 'posts_per_page' => 30, 'paged' => 6));
while ($query->have_posts() ) : $query->the_post();
    viviendu_generated_tax(get_the_ID());
    ?><p>Actualizado <?php the_title(); ?></p><?php 
endwhile;
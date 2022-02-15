<?php
//Includes
include get_template_directory() . '/inc/acf.php';
include get_template_directory() . '/inc/assets.php';
include get_template_directory() . '/inc/actions.php';
include get_template_directory() . '/inc/config.php';
include get_template_directory() . '/inc/taxonomies.php';
include get_template_directory() . '/inc/posts.php';
include get_template_directory() . '/inc/helpers.php';
include get_template_directory() . '/inc/woocommerce.php';
if (WP_DEBUG) {
	include get_template_directory() . '/inc/development.php';
} else {
	include get_template_directory() . '/inc/production.php';
}

add_filter('flamingo_csv_value_separator', function ($seperator) {
	return ';';
}, 999);

function convert_to_acf()
{
	add_menu_page(
		__('Convertir a ACF', 'viviendu'),
		__('Convertir a ACF', 'viviendu'),
		'manage_options',
		'sample-page',
		'convert_to_acf_content',
		'dashicons-schedule',
		3
	);
}
add_action('admin_menu', 'convert_to_acf');

function convert_to_acf_content()
{
?>
	<h1><?php esc_html_e('Convirtiendo a ACF.', 'viviendu'); ?> </h1>
	<p>Una vez finalizado el proceso, ejecuta la siguiente consulta en la BD: <code>DELETE FROM gyd15_postmeta where meta_key = '_ungrynerd_images_new';</code></p>
	<p>Para actualizar los datos de portada, ejecutar la siguiente consulta:</p>
	<pre style="max-width: 100%; overflow: scroll;">INSERT INTO `gyd15_postmeta` (`meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
	(553755, 2, '__ungrynerd_main_title', 'field_620bd429ce8fc'),
	(553756, 2, '__ungrynerd_subtitle', 'field_620bd436ce8fd'),
	(553757, 2, 'block_0__ungrynerd_link_title', 'Casas prefabricadas baratas'),
	(553758, 2, '_block_0__ungrynerd_link_title', 'field_620bd36483870'),
	(553759, 2, 'block_0__ungrynerd_link_text', 'Encuentra toda la oferta de casas prefabricadas baratas. Estas pueden ser de muchos tipos diferentes, ya que el concepto abarca desde las típicas casas modulares hasta viviendas más ligeras y fáciles de transportar'),
	(553760, 2, '_block_0__ungrynerd_link_text', 'field_620bd3fbce8fa'),
	(553761, 2, 'block_0__untrynerd_link_href', 'https://viviendu.com/ofertas/casas-prefabricadas-baratas/'),
	(553762, 2, '_block_0__untrynerd_link_href', 'field_620bd410ce8fb'),
	(553763, 2, 'block_1__ungrynerd_link_title', 'Casas de madera baratas'),
	(553764, 2, '_block_1__ungrynerd_link_title', 'field_620bd36483870'),
	(553765, 2, 'block_1__ungrynerd_link_text', 'Vas a encontrar la más amplia oferta de viviendas de madera económicas que existe en el mercado. Consulta los catálogos de las principales empresas del sector. '),
	(553766, 2, '_block_1__ungrynerd_link_text', 'field_620bd3fbce8fa'),
	(553767, 2, 'block_1__untrynerd_link_href', 'https://viviendu.com/ofertas/casas-de-madera-baratas/'),
	(553768, 2, '_block_1__untrynerd_link_href', 'field_620bd410ce8fb'),
	(553769, 2, 'block_2__ungrynerd_link_title', 'Casas americanas'),
	(553770, 2, '_block_2__ungrynerd_link_title', 'field_620bd36483870'),
	(553771, 2, 'block_2__ungrynerd_link_text', 'Consulta en Viviendu las más completa oferta de casas americanas, el tipo de vivienda prefabricada más extendido en todo el mundo.'),
	(553772, 2, '_block_2__ungrynerd_link_text', 'field_620bd3fbce8fa'),
	(553773, 2, 'block_2__untrynerd_link_href', 'https://viviendu.com/ofertas/casas-americanas/'),
	(553774, 2, '_block_2__untrynerd_link_href', 'field_620bd410ce8fb'),
	(553775, 2, 'block_3__ungrynerd_link_title', 'Casas canadienses'),
	(553776, 2, '_block_3__ungrynerd_link_title', 'field_620bd36483870'),
	(553777, 2, 'block_3__ungrynerd_link_text', 'Encuentra todas las empresas que venden casas canadienses en Viviendu, la web que ofrece toda la información para comprar casas prefabricadas. Canadá es un país que vive la mayor parte del año con temperaturas frías que provocan grandes nevadas en invierno'),
	(553778, 2, '_block_3__ungrynerd_link_text', 'field_620bd3fbce8fa'),
	(553779, 2, 'block_3__untrynerd_link_href', 'https://viviendu.com/ofertas/casas-canadienses/'),
	(553780, 2, '_block_3__untrynerd_link_href', 'field_620bd410ce8fb'),
	(553781, 2, 'block_4__ungrynerd_link_title', 'Casas con encanto'),
	(553782, 2, '_block_4__ungrynerd_link_title', 'field_620bd36483870'),
	(553783, 2, 'block_4__ungrynerd_link_text', 'Si buscas casas con encanto nada mejor que navegar a través de Viviendu, la web especializada en casas prefabricadas. Tenemos los catálogos de fotos y la información de contacto de las empresas que construyen las viviendas más singulares.'),
	(553784, 2, '_block_4__ungrynerd_link_text', 'field_620bd3fbce8fa'),
	(553785, 2, 'block_4__untrynerd_link_href', 'https://viviendu.com/ofertas/casas-con-encanto/'),
	(553786, 2, '_block_4__untrynerd_link_href', 'field_620bd410ce8fb'),
	(553787, 2, 'block_5__ungrynerd_link_title', 'Casas iglú'),
	(553788, 2, '_block_5__ungrynerd_link_title', 'field_620bd36483870'),
	(553789, 2, 'block_5__ungrynerd_link_text', '¿Sueñas con una vivienda en forma de iglú?. ¿Siempre has deseado vivir en una casa redonda?. En Viviendu somos especialistas en casas prefabricadas y tenemos todos los tipos de viviendas que existen en el mercado. '),
	(553790, 2, '_block_5__ungrynerd_link_text', 'field_620bd3fbce8fa'),
	(553791, 2, 'block_5__untrynerd_link_href', 'https://viviendu.com/ofertas/iglu/'),
	(553792, 2, '_block_5__untrynerd_link_href', 'field_620bd410ce8fb'),
	(553793, 2, 'block', '6'),
	(553794, 2, '_block', 'field_620bd33b8386f');
</pre>
<?php
	global $wpdb;
	$posts = get_posts(array('fields' => 'ids', 'posts_per_page'  => -1));
	foreach ($posts as $post) {
		$results = $wpdb->get_results("SELECT * FROM gyd15_postmeta WHERE post_id = " . $post . " AND meta_key = '_ungrynerd_images'");
		if ($results) {
			$images = array();
			foreach ($results as $result) {
				$images[] = $result->meta_value;
			}
			$insert = $wpdb->insert('gyd15_postmeta', array(
				'post_id' => $post,
				'meta_key' => '_ungrynerd_images_new',
				'meta_value' => serialize($images),
			));
			if ($insert) {
				echo '<li>Convertida la galería del Post:' . $post . ' con ' . count($results) . ' imágenes</li>';
			}
		}
	}
}

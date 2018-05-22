<?php
	/* Create custom taxonomies */
	function viviendu_create_taxonomies() {
		register_taxonomy('comercio','post',array(
			'label' => 'Empresas',
			'hierarchical' => true,
			'rewrite' => array('slug' => 'empresa')
		));
		register_taxonomy('provincia','post',array(
			'label' => 'Provincias',
			'hierarchical' => true,
			'rewrite' => array('slug' => 'p')
		));
		register_taxonomy('comercio_provincia','post',array(
			'label' => 'Empresas en provincias',
			'hierarchical' => true,
			'rewrite' => array('slug' => 'l')
		));
		register_taxonomy('comercio_seccion','post',array(
			'label' => 'Empresa por sección',
			'hierarchical' => true,
			'rewrite' => array('slug' => 'm')
		));
		register_taxonomy('seccion_provincia','post',array(
			'label' => 'Secciones por provincias',
			'hierarchical' => true,
			'rewrite' => array('slug' => 'g')
		));
		register_taxonomy('oferta','post',array(
			'label' => 'Etiquetas de catálogo',
			'hierarchical' => false,
			'rewrite' => array('slug' => 'ofertas')
		));

	}
	add_action( 'init', 'viviendu_create_taxonomies', 0 );

	/* Add auto taxonomies on save posts: provincia-comercio-seccion */
	function viviendu_add_auto_tags($post_ID) {
		global $wpdb;
		$tags = array();
		$comercios = wp_get_object_terms($post_ID, 'comercio');
		$provincias = wp_get_object_terms($post_ID, 'provincia');
		$categorias = wp_get_object_terms($post_ID, 'category');
		foreach ($provincias as $prov) :
			foreach ($comercios as $com) :
				foreach ($categorias as $cat) :
					$tag_slug = $prov->slug . "-" . $com->slug . "-" . $cat->slug;
					$tag_exists = get_term_by('slug', $tag_slug, 'post_tag');
					if (!$tag_exists) {
						$term = wp_insert_term(
							$com->name . " en " . $prov->name . " (" . $cat->name . ")", //Empresa en Provincia (Sección)
							'post_tag',
							array('slug' => $tag_slug)
						);
						$term_id = $term['term_id'];
					} else {
						$term_id = $tag_exists->term_id;
					}
					update_tax_meta($term_id, 'viviendu_provincia', $prov->term_id);
					update_tax_meta($term_id, 'viviendu_comercio', $com->term_id);
					update_tax_meta($term_id, 'viviendu_seccion', $cat->term_id);
					$tags[] = $tag_slug;
				endforeach;
			endforeach;
		endforeach;
		wp_set_object_terms( $post_ID, $tags, 'post_tag' , false );
	}

	/* Add auto taxonomies on save posts: provincia-comercio */
	function viviendu_add_comercio_provincia($post_ID) {
		global $wpdb;
		$tags = array();
		$comercios = wp_get_object_terms($post_ID, 'comercio');
		$provincias = wp_get_object_terms($post_ID, 'provincia');
		foreach ($provincias as $prov) :
			foreach ($comercios as $com) :
					$tag_slug = $com->slug . "-" . $prov->slug;
					$tag_exists = get_term_by('slug', $tag_slug, 'comercio_provincia');
					if (!$tag_exists) {
						$term = wp_insert_term(
							$com->name . " en " . $prov->name, //Empresa en Provincia
							'comercio_provincia',
							array('slug' => $tag_slug)
						);
						$term_id = $term['term_id'];
					} else {
						$term_id = $tag_exists->term_id;
					}
					update_tax_meta($term_id, 'viviendu_comercio_provincia_provincia', $prov->term_id);
					update_tax_meta($term_id, 'viviendu_comercio_provincia_comercio', $com->term_id);					$tags[] = $tag_slug;
					$tags[] = $tag_slug;
			endforeach;

		endforeach;
		wp_set_object_terms( $post_ID, $tags, 'comercio_provincia' , false );
	}

	/* Add auto taxonomies on save posts: comercio-seccion */
	function viviendu_add_comercio_seccion($post_ID) {
		global $wpdb;
		$tags = array();
		$comercios = wp_get_object_terms($post_ID, 'comercio');
		$categorias = wp_get_object_terms($post_ID, 'category');
		foreach ($comercios as $com) :
			foreach ($categorias as $cat) :
				$tag_slug = $com->slug . "-" . $cat->slug;
				$tag_exists = get_term_by('slug', $tag_slug, 'comercio_seccion');
				if (!$tag_exists) {
					$term = wp_insert_term(
						$cat->name . " en " . $com->name, //Sección en Empresa
						'comercio_seccion',
						array('slug' => $tag_slug)
					);
					if (is_array($term)) {
						$term_id = $term['term_id'];
					} else {
						wp_mail('dacmail@gmail.com', 'Error al crear comercio_seccion', $post_ID .  json_encode($categorias) . json_encode($comercios));
					}
				} else {
					$term_id = $tag_exists->term_id;
				}
				update_tax_meta($term_id, 'viviendu_comercio_seccion_seccion', $cat->term_id);
				update_tax_meta($term_id, 'viviendu_comercio_seccion_comercio', $com->term_id);
				$tags[] = $tag_slug;
			endforeach;
		endforeach;
		wp_set_object_terms( $post_ID, $tags, 'comercio_seccion' , false );
	}

	/* Add auto taxonomies on save posts: provincia-comercio-seccion */
	function viviendu_add_seccion_provincia($post_ID) {
		global $wpdb;
		$tags = array();
		$comercios = wp_get_object_terms($post_ID, 'comercio');
		$provincias = wp_get_object_terms($post_ID, 'provincia');
		$categorias = wp_get_object_terms($post_ID, 'category');
		foreach ($provincias as $prov) :
			foreach ($categorias as $cat) :
				$tag_slug = $cat->slug . "-" . $prov->slug;
				$tag_exists = get_term_by('slug', $tag_slug, 'seccion_provincia');
				if (!$tag_exists) {
					$term = wp_insert_term(
						$cat->name . " en " . $prov->name, //Sección en Provincia
						'seccion_provincia',
						array('slug' => $tag_slug)
					);
					$term_id = $term['term_id'];
				} else {
					$term_id = $tag_exists->term_id;
				}
				update_tax_meta($term_id, 'viviendu_seccion_provincia_seccion', $cat->term_id);
				update_tax_meta($term_id, 'viviendu_seccion_provincia_provincia', $prov->term_id);
				$tags[] = $tag_slug;
			endforeach;
		endforeach;
		wp_set_object_terms( $post_ID, $tags, 'seccion_provincia' , false );
	}

	//Auto generated taxonomies
	add_action('save_post', 'viviendu_generated_tax');
	function viviendu_generated_tax($post_ID) {
		viviendu_add_seccion_provincia($post_ID);
		viviendu_add_comercio_seccion($post_ID);
		viviendu_add_comercio_provincia($post_ID);
		viviendu_add_auto_tags($post_ID);
	}
	//Define taxonomy metaboxes
	if (is_admin()) {
		$prefix = 'viviendu_';
		$config = array(
			'id' => 'comercio_fields',          // meta box id, unique per meta box
			'title' => 'Campos extra',          // meta box title
			'pages' => array('comercio'),        // taxonomy name, accept categories, post_tag and custom taxonomies
			'context' => 'normal',            // where the meta box appear: normal (default), advanced, side; optional
			'fields' => array(),            // list of meta fields (can be added by field arrays)
			'local_images' => false,          // Use local or hosted images (meta box images for add/remove)
			'use_with_theme' => true          //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
		);

		$comercio_meta =  new Tax_Meta_Class($config);

		$comercio_meta->addText(
			$prefix.'comercio_url',
			array(
				'name'=> 'Dirección web',
			)
		);
		$comercio_meta->addText(
			$prefix.'comercio_address',
			array(
				'name'=> 'Dirección postal',
			)
		);
		$comercio_meta->addText(
			$prefix.'comercio_phone',
			array(
				'name'=> 'Teléfono',
			)
		);
		$comercio_meta->addText(
			$prefix.'comercio_email',
			array(
				'name'=> 'Dirección correo',
			)
		);
		$comercio_meta->addImage(
			$prefix.'comercio_logo',
			array(
				'name'=> 'Logotipo'
			)
		);

		//Finish Meta Box Decleration
		$comercio_meta->Finish();

		//Shortcode in taxonomies
		$shortcode_tax =  new Tax_Meta_Class(array(
			'id' => 'shortcode',          // meta box id, unique per meta box
			'title' => 'WooCommerce Shortcode',          // meta box title
			'pages' => array('category', 'post_tag', 'oferta'),        // taxonomy name, accept categories, post_tag and custom taxonomies
			'context' => 'normal',            // where the meta box appear: normal (default), advanced, side; optional
			'fields' => array(),            // list of meta fields (can be added by field arrays)
			'local_images' => false,          // Use local or hosted images (meta box images for add/remove)
			'use_with_theme' => true          //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
		));

		$shortcode_tax->addText(
			$prefix . 'wc_shortcode',
			array(
				'name'=> 'WooCommerce Shortcode <br>(ej. [product_category category="jardin"] <br> <a target="_blank" href="https://docs.woocommerce.com/document/woocommerce-shortcodes/">+info</a>)',
			)
		);
		$shortcode_tax->addText(
			$prefix . 'wc_shortcode_more',
			array(
				'name'=> 'Enlace ver más',
			)
		);
		$shortcode_tax->Finish();

	}

	// Provincias URLs rewrite
	function custom_taxonomies_rewrite(){
	    add_rewrite_rule('^p/casas-prefabricadas-en-([^/]*)/?','index.php?provincia=$matches[1]','top');
	}
	add_action('init','custom_taxonomies_rewrite');

	add_action("comercio_provincia_edit_form_fields", 'viviendu_add_form_tinymce', 10, 2);
	add_action("comercio_edit_form_fields", 'viviendu_add_form_tinymce', 10, 2);
	add_action("comercio_seccion_edit_form_fields", 'viviendu_add_form_tinymce', 10, 2);
	add_action("category_edit_form_fields", 'viviendu_add_form_tinymce', 10, 2);
	add_action("post_tag_edit_form_fields", 'viviendu_add_form_tinymce', 10, 2);
	add_action("provincia_edit_form_fields", 'viviendu_add_form_tinymce', 10, 2);
	add_action("seccion_provincia_edit_form_fields", 'viviendu_add_form_tinymce', 10, 2);
	add_action("oferta_edit_form_fields", 'viviendu_add_form_tinymce', 10, 2);

	function viviendu_add_form_tinymce($term, $taxonomy){
		?>
		<tr valign="top">
			<th scope="row">Description</th>
			<td>
				<?php wp_editor(html_entity_decode($term->description), 'description', array('media_buttons' => false)); ?>
				<script>
					jQuery(window).ready(function(){
							jQuery('label[for=description]').parent().parent().remove();
					});
				</script>
			</td>
		</tr>
		<?php
	}
?>

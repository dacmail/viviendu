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
					update_term_meta($term_id, 'viviendu_provincia', $prov->term_id);
					update_term_meta($term_id, 'viviendu_comercio', $com->term_id);
					update_term_meta($term_id, 'viviendu_seccion', $cat->term_id);
					$tags[] = $tag_slug;
				endforeach;
			endforeach;
		endforeach;
		wp_set_object_terms( $post_ID, $tags, 'post_tag' , false );
	}

	/* Add auto taxonomies on save posts: provincia-comercio */
	function viviendu_add_comercio_provincia($post_ID) {
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
					update_term_meta($term_id, 'viviendu_comercio_provincia_provincia', $prov->term_id);
					update_term_meta($term_id, 'viviendu_comercio_provincia_comercio', $com->term_id);
					$tags[] = $tag_slug;
			endforeach;
		endforeach;
		wp_set_object_terms( $post_ID, $tags, 'comercio_provincia' , false );
	}

	/* Add auto taxonomies on save posts: comercio-seccion */
	function viviendu_add_comercio_seccion($post_ID) {
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
				update_term_meta($term_id, 'viviendu_comercio_seccion_seccion', $cat->term_id);
				update_term_meta($term_id, 'viviendu_comercio_seccion_comercio', $com->term_id);
				$tags[] = $tag_slug;
			endforeach;
		endforeach;
		wp_set_object_terms( $post_ID, $tags, 'comercio_seccion' , false );
	}

	/* Add auto taxonomies on save posts: provincia-comercio-seccion */
	function viviendu_add_seccion_provincia($post_ID) {
		$tags = array();
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
				update_term_meta($term_id, 'viviendu_seccion_provincia_seccion', $cat->term_id);
				update_term_meta($term_id, 'viviendu_seccion_provincia_provincia', $prov->term_id);
				$tags[] = $tag_slug;
			endforeach;
		endforeach;
		wp_set_object_terms( $post_ID, $tags, 'seccion_provincia' , false );
	}

	//Auto generated taxonomies
	add_action('save_post', 'viviendu_generated_tax', 20, 1);
	function viviendu_generated_tax($post_ID) {
		viviendu_add_comercio_seccion($post_ID);
		viviendu_add_seccion_provincia($post_ID);
		viviendu_add_comercio_provincia($post_ID);
		viviendu_add_auto_tags($post_ID);
	}

	// Provincias URLs rewrite
	function custom_taxonomies_rewrite(){
	    add_rewrite_rule('^p/casas-prefabricadas-en-([^/]*)/?','index.php?provincia=$matches[1]','top');
	}
	add_action('init','custom_taxonomies_rewrite');


	// sync premium fields between taxonomies
	add_action('acf/update_value/name=featured_companies', function ($featured_companies, $tax) {
		$old = get_field('featured_companies', $tax);
		$tmp = explode('_', $tax);
		$term_id = end($tmp);
		if (is_array($featured_companies)) {
			foreach ($featured_companies as $company) {
				$featured_in_categories = get_term_meta($company, 'featured_in_categories', true);
				if (is_array($featured_in_categories)) {
					if (!in_array($term_id, $featured_in_categories)) {
						$featured_in_categories[] = (string) $term_id;
						update_field('featured_in_categories', $featured_in_categories, 'comercio_'. $company);
					}
				} else {
					update_field('featured_in_categories', [(string) $term_id], 'comercio_'. $company);
				}
			}

			$old = array_diff($old, $featured_companies);
		}

		if (is_array($old)) {
			foreach ($old as $company) {
				//companies removed from featured
				$featured_in_categories = get_term_meta($company, 'featured_in_categories', true);
				if (is_array($featured_in_categories)) {
					if (($key = array_search($term_id, $featured_in_categories)) !== false) {
						unset($featured_in_categories[$key]);
					}
					update_field('featured_in_categories', $featured_in_categories, 'comercio_'. $company);
				} else {
					update_field('featured_in_categories', '', 'comercio_'. $company);
				}
			}
		}

		return $featured_companies;
	}, 10, 2);

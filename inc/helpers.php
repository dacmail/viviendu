<?php 
	define('LIMIT_WORDS', 10);

	function viviendu_tax_link($post_id, $tax) {
		$terms = get_the_terms( $post_id, $tax );
		$term = array_pop($terms);
		if (!is_wp_error($term)) {
			return get_term_link($term->slug, $tax);
		}
	}

	function viviendu_tax_anchor($post_id, $tax, $anchor_text='') {
		$terms = get_the_terms( $post_id, $tax );
		$term = array_pop($terms);
		if (!is_wp_error($term)) {
			$anchor_text = empty($anchor_text) ? $term->name : $anchor_text;
			return '<a href="'. get_term_link($term->slug, $tax).'">'. $anchor_text .'</a>';
		}
	}

	function viviendu_tax_name($post_id, $tax) {
		$terms = get_the_terms( $post_id, $tax );
		$term = array_pop($terms);
		if (!is_wp_error($term)) {
			return $term->name;
		}
	}

	function viviendu_tax_desc($post_id, $tax, $excerpt=true) {
		$terms = get_the_terms( $post_id, $tax );
		$term = array_pop($terms);
		if (!is_wp_error($term)) {
			$desc = $excerpt ? wp_trim_words($term->description, LIMIT_WORDS) : $term->description;
			return $desc;
		}
	}

	function viviendu_comercio_seccion_content($tax_id) {
		$comercio_seccion_desc = term_description($tax_id);
		if (empty($comercio_seccion_desc)) {
			$comercio_id = get_tax_meta($tax_id, 'viviendu_comercio_seccion_comercio'); 
			$comercio = get_term( $comercio_id, 'comercio');
			return $comercio->description;
		} else {
			return $comercio_seccion_desc;
		}
	}

	function viviendu_tag_content($tax_id) {
		$tag_desc = term_description($tax_id);
		if (empty($tag_desc)) {
			$comercio = get_term(get_tax_meta($tax_id, 'viviendu_comercio'), 'comercio'); 
			$seccion = get_term(get_tax_meta($tax_id, 'viviendu_seccion'), 'category'); 
			$comercio_seccion = get_term_by('slug', $comercio->slug . "-" . $seccion->slug, 'comercio_seccion');

			return viviendu_comercio_seccion_content($comercio_seccion->term_id);
		}
		return $tag_desc;
	}

	function viviendu_location_info($comercio_id) {
		return array(
			'url' => get_tax_meta($comercio_id, 'viviendu_comercio_url'),
			'address' => get_tax_meta($comercio_id, 'viviendu_comercio_address'),
			'phone' => get_tax_meta($comercio_id, 'viviendu_comercio_phone'),
			'email' => get_tax_meta($comercio_id, 'viviendu_comercio_email'),
			'logo' => get_tax_meta($comercio_id, 'viviendu_comercio_logo')
		);
	}

	function viviendu_slideshow($size='full', $link='', $limit=0, $counter=false) {
		$images = rwmb_meta('_ungrynerd_images', 'type=image&size=' . $size);
		$return = '';
		if (!empty($images)) {
			if ($limit) {$images = array_slice($images, 0, $limit); }
			$options = empty($link) ? '' : ' data-cycle-slides="> .slide"';
			$return .= '<div class="cycle-slideshow"' . $options .'>';
			foreach ( $images as $image ) {
				$return .= empty($link) ? '' : "<a class='slide' href='{$link}'>";
			    $return .=  "<img src='{$image['url']}' width='{$image['width']}' height='{$image['height']}' alt='{$image['alt']}'/>";
			    $return .= empty($link) ? '' : "</a>";
			}
			if ($counter) {
				$return .= '<div class="cycle-caption"></div>';
			}
			$return .= '<a href="#" class="nav cycle-prev"><i class="fa fa-angle-left"></i></a>
	    				<a href="#" class="nav cycle-next"><i class="fa fa-angle-right"></i></a>
						</div>';
		}

		return $return;
	}

	function viviendu_set_post_thumb($post_id) {
		$images = rwmb_meta('_ungrynerd_images', 'type=image', $post_id);
		if (!empty($images)) {
			foreach ( $images as $image ) {
				set_post_thumbnail($post_id, $image['ID']);
				break;
			}
		}
	}

	/**
	* Devuelve el primer párrafo de un texto
	*
	* param string $text Texto formateado en html con etiquetas <p>
	* param bool $first Deveulve solo el primero o el resto de etiquetas <p>
	*/
	function viviendu_get_paragraph($text, $first=true) {
		preg_match("/<p>(.*)<\/p>/",$text,$matches);
		$first_p = '<p>'.array_pop($matches).'</p>';
		if ($first) {
			return $first_p;
		} else {
			return str_replace($first_p,'', $text);
		}
		
	}
	/**
	* Muestra el texto correspondiente a un catálogo
	*
	* param int $post_id ID del artículo del que se quiere mostrar el texto
	* param array $priority Define la prioridad del texto que se mostrará
	*/
	function viviendu_the_text($post_id, $excerpt=true, $priority = array('comercio_seccion', 'comercio', 'post', 'category')) {
		foreach ($priority as $type) {
			if ($type=='post') {
				$post = get_post($post_id);
				$text = $excerpt ? wp_trim_words($post->post_content, LIMIT_WORDS) : $post->post_content;
				if (!empty($text)) { return $text; }
			} else {
				$text = viviendu_tax_desc($post_id, $type, $excerpt);
				if (!empty($text)) { return $text; }
			}
		}
	}

	/**
	* Devuelve el ID del primer post dados un tipo taxonomía y su term_id
	*
	* param string $taxonomy  tipo de taxonomía
	* param int $term_id term id referente a la taxonomía
	*/
	function viviendu_post_id($taxonomy, $term_id) {
		$post = new WP_Query(array(
					'post_type' => 'post',
					'tax_query' => array(
						array(
							'taxonomy' => $taxonomy,
							'field'    => 'ID',
							'terms'    => $term_id,
						),
					'posts_per_page' => 1,
					'order' => 'DESC',
					'orderby' => 'ID'
					),
				));
		return $post->post->ID;
	}

?>
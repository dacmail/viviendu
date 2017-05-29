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

	// Taxonomy description tinyMCE
	add_action('init', 'kws_rich_text_tags', 9999);
	function kws_rich_text_tags() {

		global $wpdb, $user, $current_user, $pagenow, $wp_version;


		// ADD EVENTS
		if(
		$pagenow == 'edit-tags.php' ||
		$pagenow == 'categories.php' ||
		$pagenow == 'media.php' ||
		$pagenow == 'profile.php' ||
		$pagenow == 'user-edit.php'
		) {
			if(!user_can_richedit()) { return; }

			wp_enqueue_script('kws_rte', get_template_directory_uri() . '/inc/kws_rt_taxonomy.js', array('jquery'));
			wp_enqueue_style('editor-buttons');

			$taxonomies = get_taxonomies();

			foreach($taxonomies as $tax) {
				add_action($tax.'_edit_form_fields', 'kws_add_form');
				add_action($tax.'_add_form_fields', 'kws_add_form');
			}

			add_filter('attachment_fields_to_edit', 'kws_add_form_media', 1, 2);
			add_filter('media_post_single_attachment_fields_to_edit', 'kws_add_form_media', 1, 2);

			if($pagenow == 'edit-tags.php' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && empty($_REQUEST['taxonomy'])) {
				add_action('edit_term','kws_rt_taxonomy_save');
			}

			foreach ( array( 'pre_term_description', 'pre_link_description', 'pre_link_notes', 'pre_user_description' ) as $filter ) {
				remove_filter( $filter, 'wp_filter_kses' );
			}

			add_action('show_user_profile', 'kws_add_form', 1);
			add_action('edit_user_profile', 'kws_add_form', 1);
			add_action('edit_user_profile_update', 'kws_rt_taxonomy_save');

			if(empty($_REQUEST['action'])) {
				add_filter('get_terms', 'kws_shorten_term_description');
			}
		}

		// Enable shortcodes in category, taxonomy, tag descriptions
		if(function_exists('term_description')) {
			add_filter('term_description', 'do_shortcode');
		} else {
			add_filter('category_description', 'do_shortcode');
		}
	}

	// PROCESS FIELDS
	function kws_rt_taxonomy_save() {
		global $tag_ID;
		$a = array('description');
		foreach($a as $v) {
			wp_update_term($tag_ID,$v,$_POST[$v]);
		}
	}

	function kws_add_form_media($form_fields, $post) {
		$form_fields['post_content']['input'] = 'html';

		// We remove the ' and " from the $name so it works for tinyMCE.
		$name = "attachments[$post->ID][post_content]";

		// Let's grab the editor.
		ob_start();
		wp_editor($post->post_content, $name,
				array(
					'textarea_name' => $name,
					'editor_css' => kws_rtt_get_css(),
				)
		);
		$editor = ob_get_clean();

		$form_fields['post_content']['html'] = $editor;

		return $form_fields;
	}

	function kws_rtt_get_css() {
		return '
		<style type="text/css">
			.wp-editor-container .quicktags-toolbar input.ed_button {
				width:auto;
			}
			.html-active .wp-editor-area { border:0;}
		</style>';
	}

	function kws_add_form($object = ''){
		global $pagenow;?>

		<style type="text/css">
			.quicktags-toolbar input { width:auto!important; }
			.wp-editor-area {border: none!important;}
		</style>

		<?php
		// This is a profile page
		if(is_a($object, 'WP_User')) {
			$content = html_entity_decode(get_user_meta($object->ID, 'description', true));
			$editor_selector = $editor_id = 'description';
			?>
		<table class="form-table rich-text-tags">
		<tr>
			<th><label for="description"><?php _e('Biographical Info'); ?></label></th>

			<td><?php wp_editor($content, $editor_id,
				array(
					'textarea_name' => $editor_selector,
					'editor_css' => kws_rtt_get_css(),
				)); ?><br />
			<span class="description"><?php _e('Share a little biographical information to fill out your profile. This may be shown publicly.'); ?></span></td>
		</tr>
	<?php
		}
		// This is a taxonomy
		else {
			$content = is_object($object) && isset($object->description) ? html_entity_decode(htmlentities(trim(stripslashes($object->description)))) : '';
			if( in_array($pagenow, array('edit-tags.php')) ) {
				$editor_id = 'tag_description';
				$editor_selector = 'description';
			} else {
				$editor_id = $editor_selector = 'category_description';
			}
			?>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="description"><?php _ex('Description', 'Taxonomy Description'); ?></label></th>

		<td><?php wp_editor(htmlspecialchars_decode($content), $editor_id, array(
					'textarea_name' => $editor_selector,
					'editor_css' => kws_rtt_get_css(),
		)); ?><br />
		<span class="description"><?php _e('The description is not prominent by default, however some themes may show it.'); ?></span></td>
	</tr>
	<?php

		}

	}

	function kws_wp_trim_excerpt($text) {
		$raw_excerpt = $text;
		$text = str_replace(']]>', ']]&gt;', $text);
		$excerpt_length = apply_filters('term_excerpt_length', 40);
		$excerpt_more = ' ' . '[...]';
		$words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
		if ( count($words) > $excerpt_length ) {
			array_pop($words);
			$text = implode(' ', $words);
			$text = $text . $excerpt_more;
		} else {
			$text = implode(' ', $words);
		}
		return apply_filters('wp_trim_term_excerpt', force_balance_tags($text), $raw_excerpt);
	}

	function kws_shorten_term_description($terms = array(), $taxonomies = null, $args = array()) {
		if(is_array($terms)) {
		foreach($terms as $key=>$term) {
			if(is_object($term) && isset($term->description)) {
				$term->description = kws_wp_trim_excerpt($term->description);
			}
		}
		}
		return $terms;
	}

	function custom_taxonomies_rewrite(){
	    add_rewrite_rule('^p/casas-prefabricadas-en-([^/]*)/?','index.php?provincia=$matches[1]','top');
	}
	add_action('init','custom_taxonomies_rewrite');


?>

<?php
    add_action('init', 'viviendu_presupuestos');
    function viviendu_presupuestos()  {
      $labels = array(
        'name' => __('Presupuestos', 'framework'),
        'singular_name' => __('Presupuesto', 'framework'),
        'add_new' => __('Add Presupuesto', 'framework'),
        'add_new_item' => __('Add Presupuesto', 'framework'),
        'edit_item' => __('Edit Presupuesto', 'framework'),
        'new_item' => __('New presupuestos', 'framework'),
        'view_item' => __('View Presupuesto', 'framework'),
        'search_items' => __('Search presupuestos', 'framework'),
        'not_found' =>  __('No presupuestos found', 'framework'),
        'not_found_in_trash' => __('No presupuestos found in Trash', 'framework'),
        'parent_item_colon' => ''
      );

      $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'capability_type' => 'post',
        'show_in_nav_menus' => false,
        'hierarchical' => false,
        'exclude_from_search' => true,
        'menu_position' => 5,
        'taxonomies' => array('comercio', 'category', 'provincia'),
        'supports' => array('title', 'custom-fields')
      );
      register_post_type('presupuesto',$args);
    }


    function viviendu_columns_head($defaults) {
        unset($defaults['date']);
        $defaults['type'] = 'Tipo de petición';
        $defaults['to'] = 'Dirigida a';
        $defaults['customer'] = 'Cliente';
        return $defaults;
    }

    // SHOW THE FEATURED IMAGE
    function viviendu_columns_content($column_name, $post_ID) {
        if ($column_name == 'customer') {
            echo get_post_meta($post_ID, 'customer_name', true) . ' (' . get_post_meta($post_ID, 'customer_email', true) . ')';
        }
        if ($column_name == 'type') {
            echo get_post_meta($post_ID, 'petition_type', true);
        }
        if ($column_name == 'to') {
            $type = get_post_meta($post_ID, 'petition_type', true);
            if ($type == 'empresa') {
                $taxonomy = 'comercio';
            } else {
                $taxonomy = 'category';
            }
            $post_type = 'presupuesto';
            $terms = get_the_terms($post_ID, $taxonomy);
            if (!empty($terms) ) {
                foreach ( $terms as $term )
                $post_terms[] ="<a href='edit.php?post_type={$post_type}&{$taxonomy}={$term->slug}'> " .esc_html(sanitize_term_field('name', $term->name, $term->term_id, $taxonomy, 'edit')) . "</a>";
                echo join('', $post_terms );
            }
             else echo '<i>Sin datos. </i>';
        }
    }
    add_filter('manage_presupuesto_posts_columns', 'viviendu_columns_head');
    add_action('manage_presupuesto_posts_custom_column', 'viviendu_columns_content', 10, 2);


    /* WIKI */

    add_action('init', 'viviendu_wikis');
    function viviendu_wikis()  {
      $labels = array(
        'name' => __('Wikis', 'framework'),
        'singular_name' => __('Wiki', 'framework'),
        'add_new' => __('Add wiki', 'framework'),
        'add_new_item' => __('Add wiki', 'framework'),
        'edit_item' => __('Edit wiki', 'framework'),
        'new_item' => __('New wikis', 'framework'),
        'view_item' => __('View wiki', 'framework'),
        'search_items' => __('Search wikis', 'framework'),
        'not_found' =>  __('No wikis found', 'framework'),
        'not_found_in_trash' => __('No wikis found in Trash', 'framework'),
        'parent_item_colon' => ''
      );

      $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'capability_type' => 'post',
        'show_in_nav_menus' => false,
        'hierarchical' => false,
        'has_archive' => true,
        'exclude_from_search' => false,
        'menu_position' => 5,
        'taxonomies' => array('wiki-section'),
        "rewrite" => array( 'slug' => 'wiki'),
        'supports' => array('title', 'editor')
      );
      register_post_type('wiki',$args);
    }

    add_action('init', 'viviendu_wiki_section');
    function viviendu_wiki_section() {
        register_taxonomy("wiki-section",
        array("wiki"),
        array(
            "hierarchical" => true,
            "label" => esc_html__( "Sección", 'ungrynerd'),
            "singular_label" => esc_html__( "Sección", 'ungrynerd'),
            "rewrite" => array( 'slug' => 'wiki-pregunta', 'hierarchical' => true),
            'show_in_nav_menus' => false,
            )
        );
    }


    function viviendu_wiki_columns_head($defaults) {
        $defaults['wiki'] = 'Sección';
        return $defaults;
    }

    // SHOW THE FEATURED IMAGE
    function viviendu_wiki_columns_content($column_name, $post_ID) {
        if ($column_name == 'wiki') {
            $terms = get_the_terms($post_ID, 'wiki-section');
            if (!empty($terms) ) {
                foreach ( $terms as $term )
                $post_terms[] ="<a href='edit.php?post_type=wiki&wiki-section={$term->slug}'> " .esc_html(sanitize_term_field('name', $term->name, $term->term_id, 'wiki-section', 'edit')) . "</a><br/>";
                echo join('', $post_terms );
            }
             else echo '<i>Sin datos. </i>';
        }
    }
    add_filter('manage_wiki_posts_columns', 'viviendu_wiki_columns_head');
    add_action('manage_wiki_posts_custom_column', 'viviendu_wiki_columns_content', 10, 2);





    add_action('init', 'viviendu_photos');
    function viviendu_photos()  {
      $labels = array(
        'name' => __('Fotos', 'framework'),
        'singular_name' => __('Foto', 'framework'),
        'add_new' => __('Añadir Foto', 'framework'),
        'add_new_item' => __('Añadir Foto', 'framework'),
        'edit_item' => __('Editar Foto', 'framework'),
        'new_item' => __('Nueva Foto', 'framework'),
        'view_item' => __('Ver Foto', 'framework'),
        'search_items' => __('Buscar Fotos', 'framework'),
        'not_found' =>  __('No se han encontrado fotos', 'framework'),
        'not_found_in_trash' => __('No hay fotos en la papelera', 'framework'),
        'parent_item_colon' => ''
      );

      $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'capability_type' => 'post',
        'show_in_nav_menus' => false,
        'hierarchical' => false,
        'exclude_from_search' => false,
        'menu_position' => 5,
        'has_archive' => true,
        'taxonomies' => array('photo-tag'),
        "rewrite" => array( 'slug' => 'fotos-casas-prefabricadas'),
        'supports' => array('thumbnail')
      );
      register_post_type('photo',$args);
    }

    add_action('init', 'viviendu_photo_tag');
    function viviendu_photo_tag() {
        register_taxonomy("photo-tag",
        array("photo"),
        array(
            "hierarchical" => false,
            "label" => esc_html__( "Etiquetas", 'ungrynerd'),
            "singular_label" => esc_html__( "Etiqueta", 'ungrynerd'),
            "rewrite" => array( 'slug' => 'fotografias', 'hierarchical' => false),
            'show_in_nav_menus' => false,
            )
        );
    }


    function viviendu_photo_columns_head($defaults) {
        unset($defaults['title']);
        unset($defaults['date']);
        $defaults['photo'] = 'Fotografía';
        $defaults['photo-tag'] = 'Etiquetas';
        $defaults['date'] = 'Publicada el';
        return $defaults;
    }

    // SHOW THE FEATURED IMAGE
    function viviendu_photo_columns_content($column_name, $post_ID) {
        if ($column_name == 'photo-tag') {
            $terms = get_the_terms($post_ID, 'photo-tag');
            if (!empty($terms) ) {
                foreach ( $terms as $term )
                $post_terms[] ="<a href='edit.php?post_type=photo&photo-tag={$term->slug}'> " .esc_html(sanitize_term_field('name', $term->name, $term->term_id, 'photo-tag', 'edit')) . "</a><br/>";
                echo join('', $post_terms );
            }
             else echo '<i>Sin datos. </i>';
        }
        if ($column_name == 'photo') {
            echo "<a href='post.php?post={$post_ID}&action=edit'>" . get_the_post_thumbnail($post_ID, array(100,100)) . "</a>";
        }
    }
    add_filter('manage_photo_posts_columns', 'viviendu_photo_columns_head');
    add_action('manage_photo_posts_custom_column', 'viviendu_photo_columns_content', 10, 2);


    function viviendu_set_photo_data($post_ID) {
        if (get_post_type($post_ID )=='photo') {
            if (!wp_is_post_revision($post_ID)) {
                remove_action('save_post', 'viviendu_set_photo_data');
                wp_update_post(array(
                    'ID' => $post_ID,
                    'post_title' => "Fotografía " . $post_ID
                ));
                add_action('save_post', 'viviendu_set_photo_data');
            }
        }
    }
    add_action('save_post', 'viviendu_set_photo_data');
?>

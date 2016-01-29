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

?>
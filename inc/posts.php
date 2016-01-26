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
        'taxonomies' => array('comercio', 'category'),
        'supports' => array('title')
      );
      register_post_type('presupuesto',$args);
    }
?>
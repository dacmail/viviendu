<?php
  //Redirect to comercio_seccion if single
  $frontpage_id = get_option('page_on_front');
  add_action('wp', 'viviendu_redirect_single');
  function viviendu_redirect_single() {
    global $post;
    if (isset($post->ID) && is_single($post->ID) && is_singular('post')) {
      wp_redirect(viviendu_tax_link($post->ID, 'comercio_seccion'), 301);
    }
  }


  //redirecciones peticiones presupuesto
  function viviendu_rewrite_basic() {
    add_rewrite_tag('%petition_type%', '([^&]+)');
    add_rewrite_tag('%petition_item%', '([^&]+)');
    add_rewrite_rule('^presupuesto/([^/]*)/([^/]*)/?','index.php?page_id=8723&petition_type=$matches[1]&petition_item=$matches[2]','top');
  }
  add_action('init', 'viviendu_rewrite_basic');


  //Enqueue scripts and styles
  function ungrynerd_scripts() {
    wp_enqueue_style('main-css', asset_path('styles/main.css'), false, null);

    wp_enqueue_style('viviendu-fonts', '//fonts.googleapis.com/css?family=Roboto:400,300,700,900|Roboto+Condensed:400,700,300');
    wp_enqueue_style('viviendu-style', get_stylesheet_uri() );

    if( !is_admin()){
      wp_enqueue_script('cycle-js', asset_path('scripts/lightgallery.js'), array('jquery'), '1.6.1', true );
      wp_enqueue_script('cycle-js', asset_path('scripts/cycle.js'), array('jquery'), '2.0.1', true );
      wp_enqueue_script('viviendu-js', asset_path('scripts/main.js'), array('jquery'), null, true);
      wp_enqueue_script('addthis', '//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-566e723b50e69a9e', '', '', true );
      wp_enqueue_script('html5shiv', 'https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js', array(), '3.7.2');
      wp_enqueue_script('respond', 'https://oss.maxcdn.com/respond/1.4.2/respond.min.js', array(), '1.4.2');

      wp_script_add_data( 'html5shiv', 'conditional', 'lt IE 9' );
      wp_script_add_data( 'respond', 'conditional', 'lt IE 9' );
    }

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
      wp_enqueue_script( 'comment-reply' );
    }
  }
  add_action( 'wp_enqueue_scripts', 'ungrynerd_scripts' );


  //Remove emojis support
  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
  remove_action( 'wp_print_styles', 'print_emoji_styles' );


  //Remove recent comments styles in head
  function viviendu_remove_recent_comments_style() {
      global $wp_widget_factory;
      remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
  }
  add_action('widgets_init', 'viviendu_remove_recent_comments_style');


  //Exclude pages from search
  function viviendu_search_filter($query) {
    if ($query->is_search && empty(get_query_var('post_type'))) {
      $query->set('post_type', 'post');
    }
    return $query;
  }
  add_filter('pre_get_posts','viviendu_search_filter');

  //Change posts per page in photos
  function viviendu_photos_filter($query) {
    if (is_post_type_archive('photo') || is_tax('photo-tag')) {
      $query->set('posts_per_page', 24);
    }
    return $query;
  }
  add_filter('pre_get_posts','viviendu_photos_filter');

  //Cambiando URL provincias
  function viviendu_term_link_filter( $url, $term, $taxonomy ) {
    if ($taxonomy=="provincia") {
      return site_url() . '/p/casas-prefabricadas-en-' . $term->slug;
    }
      return $url;
  }
  add_filter('term_link', 'viviendu_term_link_filter', 10, 3);

?>

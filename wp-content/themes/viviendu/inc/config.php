<?php
  // Content width
  if ( ! isset( $content_width ) ) $content_width = 900;


  //Add title tag support
  add_theme_support( 'title-tag' );


  // Comments reply
  if ( is_singular() ) wp_enqueue_script("comment-reply");


  // automatic feed links
  add_theme_support('automatic-feed-links');
  add_theme_support( 'woocommerce' );


  // Definición widgets
  if ( function_exists('register_sidebar') ){
     register_sidebar(array(
      'id' => 'sidebar-1',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="title">',
      'after_title' => '</h4>',
      'name' => 'Barra Lateral'
     ));
     register_sidebar(array(
      'id' => 'product-list',
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h4 class="title">',
      'after_title' => '</h4>',
      'name' => 'Listado productos'
     ));
  }


  // Definición menús
  if ( function_exists( 'register_nav_menus' ) ) {
    register_nav_menus(
      array(
        'main' => 'Menu principal',
        'top' => 'Menu slider',
        'footer' => 'Menu footer',
        'footer-social' => 'Menu footer 2'
      )
    );
  }


  // Soporte para miniaturas y definición de tamaños
  add_theme_support( 'post-thumbnails' );
  if ( function_exists( 'add_image_size' ) ) {
    add_image_size( 'featured', 600, 400, true );
    add_image_size( 'square', 300, 300, true );
  }

?>

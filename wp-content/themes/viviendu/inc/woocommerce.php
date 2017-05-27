<?php
  //add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
  add_theme_support( 'wc-product-gallery-zoom' );
  add_theme_support( 'wc-product-gallery-lightbox' );
  add_theme_support( 'wc-product-gallery-slider' );

  remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10,0);
  remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10,0);

  add_action('woocommerce_before_main_content', 'viviendu_wc_outpup_content_wrapper', 10);
  function viviendu_wc_outpup_content_wrapper() {
    if (is_product()) {
       echo '<section class="container section">';
    } else {
      echo '<div class="container section">
              <div class="row">
                <section class="col-sm-8">';
    }
  }
  add_action('woocommerce_after_main_content', 'viviendu_wc_outpup_content_wrapper_end', 10);
  function viviendu_wc_outpup_content_wrapper_end() {
    echo '</section>';
  }

  add_action('woocommerce_before_shop_loop', 'viviendu_wc_before_shop_loop_wrapper', 5);
  function viviendu_wc_before_shop_loop_wrapper() {
    echo '<div class="results-orders">';
  }

  add_action('woocommerce_before_shop_loop', 'viviendu_wc_before_shop_loop_wrapper_end', 35);
  function viviendu_wc_before_shop_loop_wrapper_end() {
    echo '</div>';
  }

  /**
  * Place a cart icon with number of items and total cost in the menu bar.
  *
  * Source: http://wordpress.org/plugins/woocommerce-menu-bar-cart/
  */
  add_filter('wp_nav_menu_items','sk_wcmenucart', 10, 2);
  function sk_wcmenucart($menu, $args) {
    // Check if WooCommerce is active and add a new item to a menu assigned to Primary Navigation Menu location
    if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || 'main' !== $args->theme_location )
      return $menu;

    ob_start();
    global $woocommerce;
    $menu_item = '';
    $viewing_cart = __('View your shopping cart', 'your-theme-slug');
    $start_shopping = __('Start shopping', 'your-theme-slug');
    $cart_url = $woocommerce->cart->get_cart_url();
    $shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
    $cart_contents_count = $woocommerce->cart->cart_contents_count;
    $cart_contents = sprintf('%d', $cart_contents_count);
    if ( $cart_contents_count > 0 ) {
      if ($cart_contents_count == 0) {
        $menu_item = '<li class="menu-item cart"><a class="wcmenucart-contents" href="'. $shop_page_url .'" title="'. $start_shopping .'">';
      } else {
        $menu_item = '<li class="menu-item cart"><a class="wcmenucart-contents" href="'. $cart_url .'" title="'. $viewing_cart .'">';
      }
      $menu_item .= '<i class="fa fa-shopping-cart"></i> ';
      $menu_item .= $cart_contents;
      $menu_item .= '</a></li>';
    }
    echo $menu_item;
    $social = ob_get_clean();
    return $menu . $social;

  }

  //Default order in WOO Shortcode set to DESC
  add_filter('woocommerce_shortcode_products_query', 'wh_woocommerce_shortcode_products_orderby');
  function wh_woocommerce_shortcode_products_orderby($args) {
    $args['order'] = 'DESC';
    return $args;
  }
?>

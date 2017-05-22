<?php
  //add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
  add_theme_support( 'wc-product-gallery-zoom' );
  add_theme_support( 'wc-product-gallery-lightbox' );
  add_theme_support( 'wc-product-gallery-slider' );

  remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10,0);
  remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10,0);

  add_action('woocommerce_before_main_content', 'viviendu_wc_outpup_content_wrapper', 10);
  function viviendu_wc_outpup_content_wrapper() {
    echo '<section class="container section">';
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
?>

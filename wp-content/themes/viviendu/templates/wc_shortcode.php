<?php $shorcode = get_tax_meta(get_queried_object()->term_id, 'viviendu_wc_shortcode', true); ?>
<?php if (!empty($shorcode)): ?>
  <section class="wc-tax-shortcode">
    <h2 class="title mini tit-sep">Productos</h2>

    <?php echo do_shortcode($shorcode); ?>
  </section>
<?php endif ?>

<?php $shorcode = get_tax_meta(get_queried_object()->term_id, 'viviendu_wc_shortcode', true); ?>
<?php $more = get_tax_meta(get_queried_object()->term_id, 'viviendu_wc_shortcode_more', true); ?>
<?php if (!empty($shorcode)): ?>
  <section class="wc-tax-shortcode">
    <h2 class="title mini tit-sep">Productos</h2>
    <?php echo do_shortcode($shorcode); ?>
  </section>
  <?php if (!empty($more)): ?>
    <p><a href="https://viviendu.com/pedir-presupuesto/" class="btn btn-block btn-more-products">Ver más productos</a></p>
  <?php endif; ?>
<?php endif ?>

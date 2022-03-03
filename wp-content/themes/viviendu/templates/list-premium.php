<article class='catalogo premium-catalogo col-sm-4'>
  <h3 class="title nm">
    <?php $company = get_term($company, 'comercio'); ?>
    <a href="<?php echo get_term_link($company, 'comercio') ?>">
      <span class="premium-logo-wrapper">
        <?php echo wp_get_attachment_image(get_field('viviendu_comercio_logo', 'comercio_' . $company->term_id), 'medium', false, ['class' => 'premium-logo']) ?>
      </span>
      <?php echo $company->name; ?>
      <span class="premium-stamp">Selección viviendu</span>
    </a>
  </h3>
</article>

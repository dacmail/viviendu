<?php get_header() ?>
<div id="container" class="page section">
  <div class="container">
    <div class="row">
      <div id="content" class="col-md-12 col-sm-12">
        <h1 class="post-title title tit-sep">Fotografías de casas prefabricadas</h1>
        <p>Inspirarte en Viviendu con la <strong>mayor selección de fotos de casas prefabricadas y viviendas móviles</strong> es la mejor manera de acertar con el diseño de tu casa de madera, cabaña de madera, casa modular... <strong>las viviendas prefabricadas son totalmente personalizables</strong>. Esto quiere decir, que entre otras muchas características, podrás decidir el material que revestirá la fachada de la vivienda.</p>

        <p>Antes de ver el inmenso <strong>catálogo de fotos de casas prefabricadas y viviendas móviles</strong> que en Viviendu ponemos a tu disposición debes tener claro que <strong>una casa prefabricada puede lucir exactamente como el propietario decida</strong>; como una casa de madera, como una vivienda de hormigón con aspecto tradicional, o como una casa de piedra, por ejemplo. También es común la combinación de distintos materiales en la construcción de viviendas, tanto en el exterior como en el interior.</p>

        <p>Cualquier vivienda que veas en esta <strong>galería de fotos de casas prefabricadas y viviendas móviles</strong> puede ser llevada a cabo por un fabricante especializado. Es sorprende lo que las nuevas técnicas de construcción prefabricada y el empleo de nuevos y mejores materiales puede llegar a hacer. Hoy día las casas prefabricadas son más resistentes y duraderas que las viviendas convencionales. <strong>Inspirarte, informarte y contactar directamente con fabricantes de casas prefabricadas</strong> es posible con Viviendu. </p>
        <ul class="photos">
          <?php while (have_posts()) : the_post(); ?>
            <li <?php post_class('photos__photo'); ?>">
              <a class="photos__photo__link" href="<?php the_post_thumbnail_url('full'); ?>"><?php the_post_thumbnail('square') ?></a>
              <div class="photos__photo__tags"><?php the_terms(get_the_ID(), 'photo-tag', '', ' ') ?></div>
            </li>
          <?php endwhile; ?>
        </ul>
        <div class="pagination container">
          <?php
          global $wp_query;

          $big = 999999999; // need an unlikely integer

          echo paginate_links( array(
            'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format' => '?paged=%#%',
            'current' => max( 1, get_query_var('paged') ),
            'total' => $wp_query->max_num_pages
          ) );
          ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php get_footer() ?>

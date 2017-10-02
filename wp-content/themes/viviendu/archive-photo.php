<?php get_header() ?>
<div id="container" class="page section">
  <div class="container">
    <div class="row">
      <div id="content" class="col-md-12 col-sm-12">
        <h1 class="post-title title tit-sep">Fotografías de casas prefabricadas</h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
        tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
        quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
        consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
        cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
        proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
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

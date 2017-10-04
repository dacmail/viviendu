<?php get_header() ?>
<div id="container" class="page section">
  <div class="container">
    <div class="row">
      <div id="content" class="col-md-12 col-sm-12">
        <h1 class="post-title title tit-sep">Inspírate: Fotos de <?php single_term_title() ?></h1>
        <?php echo term_description(); ?>
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

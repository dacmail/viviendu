<?php get_header() ?>
<?php while (have_posts()) : the_post(); ?>
  <div id="container" class="clearfix">
      <section id="featureds" class="section container">
          <div class="wiki-breadcrumb"><?php viviendu_wiki_breadcrumb(); ?></div>
          <h1 class="title"><?php the_title(); ?></h1>
      </section>
  </div>
  <div class="page">
    <div class="container">
      <div class="row">
        <div id="content" class="col-sm-7">
            <article <?php post_class('wiki'); ?> id="post-<?php the_ID(); ?>">
              <div class="post-content">
                <?php table_contents(get_the_ID()) ?>
                <?php the_content(); ?>
                <?php table_contents(get_the_ID()) ?>
              </div>
            </article>
        </div>
        <?php get_sidebar('page'); ?>
      </div>
    </div>
  </div>
<?php endwhile; ?>

<?php get_footer() ?>

<?php get_header() ?>
<div id="container" class="clearfix">
    <section id="featureds" class="section container">
        <div class="wiki-breadcrumb"><?php viviendu_wiki_breadcrumb(); ?></div>
        <h1 class="title"><?php $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); echo $term->name; ?></h1>
    </section>
</div>
<?php $subsections=get_terms('wiki-section', array('parent' => $term->term_id)); ?>
<?php if (!empty($subsections)): ?>
    <div class="page">
        <div class="container">
            <div class="row">
                <div id="content" class="col-sm-7">
                    <?php echo term_description(); ?>
                    <ul class="wiki-list">
                    <?php foreach ($subsections as $subsect): ?>
                        <li class="wiki-list__item"><a href="<?php echo esc_url(get_term_link($subsect)) ?>"><?php echo $subsect->name; ?></a></li>
                    <?php endforeach ?>
                    </ul>
                </div>
                <?php get_sidebar('page'); ?>
            </div>

        </div>
    </div>
<?php else: ?>
    <div class="page">
        <div class="container">
            <div class="row">
                <div id="content" class="col-sm-7">
                    <?php echo term_description(); ?>
                    <?php while (have_posts()) : the_post(); ?>
                        <article <?php post_class('wiki'); ?> id="post-<?php the_ID(); ?>">
                            <h3 class="wiki__title">
                                <a href="<?php the_permalink() ?>" title="Enlace a <?php the_title_attribute(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h3>
                            <div class="post-content">
                                <?php the_excerpt(); ?>
                            </div>
                        </article>
                    <?php endwhile; ?>
                    <?php $parents = get_terms('wiki-section', array('child_of' => $term->parent)); ?>
                    <?php if (!empty($parents)): ?>
                        <ul class="wiki-list">
                        <?php foreach ($parents as $sec): ?>
                            <li class="wiki-list__item"><a href="<?php echo esc_url(get_term_link($sec)) ?>"><?php echo $sec->name; ?></a></li>
                        <?php endforeach ?>
                        </ul>
                    <?php endif ?>
                </div>
                <?php get_sidebar('page'); ?>
            </div>
        </div>
    </div>
<?php endif ?>


<?php get_footer() ?>

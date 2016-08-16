<?php get_header() ?>
<div id="container" class="clearfix">
    <section id="featureds" class="section container">
        <?php viviendu_wiki_breadcrumb(); ?>
        <h2 class="title"><?php $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); echo $term->name; ?></h2>
        <div class="subtitle sep"><?php echo viviendu_get_paragraph(apply_filters('the_content',$term->description)); ?></div>
    </section>
</div>
<?php $subsections=get_terms( 'wiki-section', array('parent' => $term->term_id)); ?>
<?php if (!empty($subsections)): ?>
    <div class="page">
        <div class="container">
            <ul>
            <?php foreach ($subsections as $subsect): ?>
                <li><a href="<?php echo esc_url(get_term_link($subsect)) ?>"><?php echo $subsect->name; ?></a></li>
            <?php endforeach ?>
            </ul>
        </div>
    </div>
<?php else: ?>
    <div class="page">
        <div class="container">
            <div class="row">
                <div id="content" class="col-sm-7">
                    <?php while (have_posts()) : the_post(); ?>
                        <article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
                            <h3>
                                <a href="<?php the_permalink() ?>" title="Enlace a <?php the_title_attribute(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h3>
                            <div class="post-content">
                                <?php the_content( __('Leer m&aacute;s &raquo;', 'ungrynerd')); ?>
                            </div>
                        </article>
                    <?php endwhile; ?>

                </div>
                <?php get_sidebar('page'); ?>
            </div>
        </div>
    </div>
<?php endif ?>


<?php get_footer() ?>

<?php /* Template Name: Pagina sin sidebar (Full width) */ ?>
<?php get_header() ?>
<div id="container" class="page section">
    <div class="container">
        <div class="row">
            <div id="content" class="col-md-12 col-sm-12">
                <?php get_template_part( 'loop', 'single' ); ?>
            </div>
        </div>
    </div>
</div>
<?php get_footer() ?>

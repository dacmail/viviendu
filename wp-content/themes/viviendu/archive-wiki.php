<?php get_header() ?>
<div id="container" class="page section">
  <div class="container">
    <div class="row">
      <div id="content" class="col-md-7">
        <h1 class="post-title title tit-sep">Preguntas y respuestas sobre casas prefabricadas</h1>
        <p>Consulta la wiki de <strong>preguntas y respuestas sobre casas prefabricadas</strong> de Viviendu y descubre la información más relevante sobre este tipo de viviendas. Encuentra respuesta a todas las preguntas sobre casas de madera, casa modulares...</p>
        <p>Cuando hayas resuelto todas tus dudas puedes pedir presupuesto en Viviendu y <strong>contactar directamente con los fabricantes líderes de casas prefabricadas.</strong>
        </p>
        <p>Informarte y contactar con fabricantes líderes es la mejor forma de acertar con la compra de tu casa prefabricada.
        </p>
        <p>Para <strong>inspirarte y obtener la mejor información sobre casas prefabricadas</strong> también puedes consultar otros recursos disponibles en Viviendu, como la galería de fotos de viviendas prefabricadas, la herramienta para diseñar viviendas en 3D o nuestro blog de artículos especializado en todo tipo de viviendas prefabricadas.
        </p>
        <?php $wikis = get_terms(array('taxonomy'=>'wiki-section', 'parent' => 0)) ?>
        <ul class="wiki-section">
        <?php foreach ($wikis as $section): ?>
          <li class="wiki-section__item">
            <a href="<?php echo get_term_link($section); ?>"><img src="<?php echo asset_path('/images/' . $section->slug . '.jpg') ?>" alt="<?php echo esc_attr($section->name); ?>"></a>
            <a class="wiki-section__item__title" href="<?php echo get_term_link($section); ?>"><?php echo $section->name; ?></a>
          </li>
        <?php endforeach ?>
        </ul>
      </div>
      <?php get_sidebar('page'); ?>
    </div>
  </div>
</div>
<?php get_footer() ?>

<?php /* Template Name: Petición presupuesto */ ?>
<?php get_header() ?>
<?php 
    $petition_type = get_query_var('petition_type');
    $petition_item = get_query_var('petition_item');
    if ($petition_type=='empresa') {
        $item = get_term_by('slug', $petition_item, 'comercio_seccion');
        $query = new WP_Query(array('comercio_seccion' => $petition_item, 
                                    'posts_per_page' => 1,
                                    'meta_key' => '_ungrynerd_petition_direct', 
                                    'meta_value' => 1));
        if ($query->have_posts() ) {
            while ( $query->have_posts() ) { 
                $query->the_post();
                $category = get_tax_meta($item->term_id, 'viviendu_comercio_seccion_seccion');
                $comercio = get_tax_meta($item->term_id, 'viviendu_comercio_seccion_comercio');
                $message = "Vas a solicitar presupuesto a " . $item->name;
            }
        } else {
            $message = "Ha ocurrido un error, la empresa no permite recibir solicitudes de presupuesto.";
        }
    }

    if ($petition_type == "seccion") {
        $cat = get_term_by('slug', $petition_item, 'category');
        $category = $cat->term_id;
        $query = new WP_Query(array('category' => $petition_item, 
                                    'posts_per_page' => -1,
                                    'meta_key' => '_ungrynerd_petition_category', 
                                    'meta_value' => 1));
        if ($query->have_posts() ) {
            $comercio = array();
            while ( $query->have_posts() ) { 
                $query->the_post();
                $terms = get_the_terms( $post->ID, 'comercio' );
                $term = array_pop($terms);
                if (!is_wp_error($term)) {
                    $comercio[] = $term->term_id;  
                }
            }
            $message = "Vas a solicitar presupuesto a " . $cat->name;
        } else {
            $message = "Ha ocurrido un error, la empresa no permite recibir solicitudes de presupuesto.";
        }
    }

?>
<?php 
if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "new_post") {
    $title= 'VIVIENDU#' . date('Ymdhms');
    $provincia = isset($_POST['provincia']) ? (array) $_POST['provincia'] : array();
    $provincia = array_map('intval',$provincia);

    $company = isset($_POST['comercio']) ? (array) explode(',', $_POST['comercio']) : array();
    $company = array_map('intval',$company);

    $new_post = array(
        'post_title'    => $title,
        'post_category' => array($_POST['cat']),  
        'post_status'   => 'private',          
        'post_type' => 'presupuesto',
        'tax_input' => array(
            'provincia' => $provincia,
            'comercio' => $company,
        )
  
    );
    $pid = wp_insert_post($new_post); 

    if ($pid) {
        add_post_meta( $pid, 'customer_name', $_POST['customer_name']);
        add_post_meta( $pid, 'customer_email', $_POST['customer_email']);
        add_post_meta( $pid, 'customer_phone', $_POST['customer_phone']);
        add_post_meta( $pid, 'customer_money', $_POST['customer_money']);
        add_post_meta( $pid, 'customer_comments', $_POST['customer_comments']);
        add_post_meta( $pid, 'petition_type', $petition_type);
        add_post_meta( $pid, 'petition_item', $petition_item);
        //wp_set_object_terms($pid, array($_POST['provincia']), 'provincia', true);

    	$message = "Tu petición se ha enviado correctamente con el número " . $title;
    } else {
    	$message = "Ha ocurrido un error, por favor ponte en contacto con nosotros"; 
    }
} ?>
<div id="container" class="page section">
	<div class="container">
		<div class="row">
			<div id="content" class="col-sm-7">
				<article id="post-<?php the_ID(); ?>">
					<h1 class="post-title title tit-sep">
						Petición de presupuesto
					</h1>
					<div class="post-content">
						<?php if (!empty($message)): ?>
							<h3><?php echo $message; ?></h3>
						<?php endif ?>
						<form id="new_post" name="new_post" method="post" action="">
                            <!-- post name -->
                            <p><label for="customer_name">Nombre *</label><br />
                            <input class="input-block" type="text" id="customer_name" value="" name="customer_name" />
                            </p>

                            <p><label for="customer_phone">Telefono</label><br />
                            <input class="input-block" type="text" id="customer_phone" value="" name="customer_phone" />
                            </p>

                            <p><label for="customer_email">Correo electrónico *</label><br />
                            <input class="input-block" type="text" id="customer_email" value="" name="customer_email" />
                            </p>
                            <?php if (!empty($category)): ?>
                                <input type="hidden" name="cat" value="<?php echo $category; ?>">
                            <?php else: ?>
                                <p><label for="Category">Tipo de vivienda que me interesa:</label><br />
                                <?php wp_dropdown_categories(array(
                                                                'show_option_none' => 'Selecciona el tipo de vivienda',
                                                                'taxonomy' => 'category',
                                                                'class' => 'input-block')); ?></p>
                            <?php endif ?>
                            
                           
                           	<p><label for="customer_money">Presupuesto máximo</label><br />
                            <input class="input-block" type="text" id="customer_money" value="" name="customer_money" />
                            </p>

                            <p><label for="provincia">Provincia:</label><br />
                            <?php wp_dropdown_categories(array(
                           									'show_option_none' => 'Selecciona tu provincia',
                            								'taxonomy' => 'provincia',
                            								'class' => 'input-block',
                                                            'name' => 'provincia')); ?></p>
                           
                            <!-- post Content -->
                            <p><label for="customer_comments">Comentarios</label><br />
                            <textarea class="input-block" id="customer_comments" name="customer_comments"></textarea>
                            </p>
                           
                            
                            <p><input type="submit" class="btn" value="Enviar petición" tabindex="6" id="submit" name="submit" /></p>
                            <?php if (!empty($comercio)): ?>
                                <input type="hidden" name="comercio" value="<?php echo (is_array($comercio) ? implode(', ', $comercio) : $comercio );?>" />
                            <?php endif ?>
                            <input type="hidden" name="action" value="new_post" />
                            <?php wp_nonce_field( 'new-post' ); ?>
                        </form>
					</div>
				</article>
			</div>
			<?php get_sidebar('page'); ?>
		</div>
	</div>
</div>
<?php get_footer() ?>
<?php /* Template Name: Petición presupuesto */ ?>
<?php get_header() ?>
<?php 
if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "new_post") {

    // Do some minor form validation to make sure there is content
    $title= 'VIVIENDU#' . date('Ymdhms');
    
    $new_post = array(
        'post_title'    => $title,
        'post_category' => array($_POST['cat']),  
        'post_status'   => 'publish',          
        'post_type' => 'presupuesto'  
    );
    $pid = wp_insert_post($new_post); 
    if ($pid) {
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
                            <p><label for="name">Nombre *</label><br />
                            <input class="input-block" type="text" id="name" value="" name="name" />
                            </p>

                            <p><label for="phone">Telefono</label><br />
                            <input class="input-block" type="text" id="phone" value="" name="phone" />
                            </p>

                            <p><label for="email">Correo electrónico *</label><br />
                            <input class="input-block" type="text" id="email" value="" name="email" />
                            </p>
                       
                            <!-- post Category -->
                            <p><label for="Category">Tipo de vivienda que me interesa:</label><br />
                            <?php wp_dropdown_categories(array(
                            								'show_option_none' => 'Selecciona el tipo de vivienda',
                            								'taxonomy' => 'category',
                            								'class' => 'input-block')); ?></p>
                           
                           	<p><label for="max">Presupuesto máximo</label><br />
                            <input class="input-block" type="text" id="max" value="" name="max" />
                            </p>

                            <p><label for="provincia">Provincia:</label><br />
                            <?php wp_dropdown_categories(array(
                           									'show_option_none' => 'Selecciona tu provincia',
                            								'taxonomy' => 'provincia',
                            								'class' => 'input-block')); ?></p>
                           
                            <!-- post Content -->
                            <p><label for="comments">Comentarios</label><br />
                            <textarea class="input-block" id="comments" name="comments"></textarea>
                            </p>
                           
                            
                            <p><input type="submit" class="btn" value="Enviar petición" tabindex="6" id="submit" name="submit" /></p>
                           
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
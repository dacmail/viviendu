<?php /* Template Name: Petición presupuesto */ ?>
<?php get_header() ?>
<script src='https://www.google.com/recaptcha/api.js'></script>
<?php 
    $petition = viviendu_determine_petition();
?>
<?php 
if( 'POST' == $_SERVER['REQUEST_METHOD'] 
    && !empty( $_POST['action'] ) 
    &&  $_POST['action'] == "new_post") {
        if (is_email($_POST['customer_email'])
            && !empty($_POST['customer_name'])
            && !empty($_POST['customer_phone'])) {
            //Comprobaciones captcha
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = array('secret' => get_option('captcha_secret'), 
                            'response' => $_POST['g-recaptcha-response']);
            $result = wp_remote_post($url, array('body' => $data));
            $captcha = json_decode( $result['body']);
            if (!is_wp_error($result) && $captcha->success) { // Si el captcha es válido
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

                if ($pid) { //Si el presupuesto se ha creado correctamente, completamos información
                    add_post_meta( $pid, 'customer_name', sanitize_text_field($_POST['customer_name']));
                    add_post_meta( $pid, 'customer_email', sanitize_email($_POST['customer_email']));
                    add_post_meta( $pid, 'customer_phone', sanitize_text_field($_POST['customer_phone']));
                    add_post_meta( $pid, 'customer_money', sanitize_text_field($_POST['customer_money']));
                    add_post_meta( $pid, 'estimated_date', sanitize_text_field($_POST['estimated_date']));
                    add_post_meta( $pid, 'customer_comments', sanitize_text_field($_POST['customer_comments']));
                    add_post_meta( $pid, 'petition_type', $petition['petition_type']);
                    add_post_meta( $pid, 'petition_item', $petition['petition_item']);
                    //Envío de correo electrónico
                    $petition['message'] = "Tu petición se ha enviado correctamente con el número " . $title;
                } else { //No se ha podido crear el post de presupuesto
                    $petition['message'] = "Ha ocurrido un error, por favor ponte en contacto con nosotros"; 
                }
            } else { //captcha no válido
                $petition['message'] = "No hemos podido comprobar que eres humano, por favor completa el captcha al final del formulario";
            }     
        } else { //Campos obligatorios (email, nombre) no rellenos
            $petition['message'] = "Revisa que los campos obligatorios estén rellenos";
        }
    
}  ?>
<div id="container" class="page section">
	<div class="container">
		<div class="row">
			<div id="content" class="col-sm-7">
				<article id="post-<?php the_ID(); ?>">
					<h1 class="post-title title tit-sep">
						Petición de presupuesto
					</h1>
					<div class="post-content">
						<?php if (!empty($petition['message'])): ?>
							<h3><?php echo $petition['message']; ?></h3>
						<?php endif ?>
						<form id="new_post" name="new_post" method="post" action="">
                            <!-- post name -->
                            <p><label for="customer_name">Nombre *</label><br />
                            <input placeholder="Escribe tu nombre y apellidos para dirigirnos a ti" class="input-block" type="text" id="customer_name" value="" name="customer_name" required />
                            </p>

                            <p><label for="customer_phone">Telefono *</label><br />
                            <input placeholder="Si prefieres llamadas de teléfono, ponlo aquí" class="input-block" type="text" id="customer_phone" value="" name="customer_phone" required/>
                            </p>

                            <p><label for="customer_email">Correo electrónico *</label><br />
                            <input placeholder="La dirección a la que te escribirán las empresas" class="input-block" type="email" id="customer_email" value="" name="customer_email" required />
                            </p>
                            <?php if (!empty($petition['category'])): ?>
                                <input type="hidden" name="cat" value="<?php echo $petition['category']; ?>">
                            <?php else: ?>
                                <p><label for="Category">Tipo de vivienda que me interesa:</label><br />
                                <?php wp_dropdown_categories(array(
                                                                'show_option_none' => 'Selecciona el tipo de vivienda',
                                                                'taxonomy' => 'category',
                                                                'class' => 'input-block')); ?></p>
                            <?php endif ?>
                            
                           
                           	<p><label for="customer_money">Presupuesto máximo</label><br />
                            <input placeholder="Si tienes un presupuesto máximo, especifícalo" class="input-block" type="text" id="customer_money" value="" name="customer_money" />
                            </p>

                            <p><label for="provincia">Provincia:</label><br />
                            <?php wp_dropdown_categories(array(
                           									'show_option_none' => 'Selecciona tu provincia',
                            								'taxonomy' => 'provincia',
                            								'class' => 'input-block',
                                                            'name' => 'provincia')); ?></p>
                           
                            
                            <p><label for="estimated_date">Fecha estimada</label><br />
                            <select name="estimated_date" class="input-block" id="estimated_date">
                                <option value="ASAP">Tan pronto como sea posible</option>
                                <option value="LESS_3M">En menos de 3 meses</option>
                                <option value="MORE_3M">En más de 3 meses</option>
                            </select>
                            </p>

                            <p><label for="customer_comments">Comentarios</label><br />
                            <textarea placeholder="Explica que es lo que necesitas, cuantos más detalles des, más fácil será para la empresa responder con un presupuesto." class="input-block" id="customer_comments" name="customer_comments"></textarea>
                            </p>
                           
                            <div class="g-recaptcha" data-sitekey="6LdeDRcTAAAAAP6AGwHu4dvBa_vB2ACppLPesbmR"></div>

                            <p><input type="submit" class="btn" value="Enviar petición" tabindex="6" id="submit" name="submit" /></p>
                            <?php if (!empty($petition['comercio'])): ?>
                                <input type="hidden" name="comercio" value="<?php echo (is_array($petition['comercio']) ? implode(', ', $petition['comercio']) : $petition['comercio'] );?>" />
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
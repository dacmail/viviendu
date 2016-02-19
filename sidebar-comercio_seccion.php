<div id="sidebar" class="sidebar col-sm-4 col-sm-offset-1">
	<div class="widget">
		<?php get_search_form(true); ?>
	</div>
	<div class="widget share">
		<div class="addthis_sharing_toolbox"></div>
	</div>
	<?php $products = get_the_terms(get_the_ID(), 'product' ); ?>
	<?php if (!empty($products)): ?>
		<div class="widget">
			<h2 class="title mini"><?php echo single_term_title(); ?></h2>
			<ul class="list-terms provincias row">
			<?php foreach ($products as $product) : ?>
			    <li class="col-sm-6">
					<i class="fa fa-star"></i><?php echo $product->name; ?>
				</li>
			<?php endforeach; ?>
			</ul>
		</div>
	<?php endif ?>
	<?php if (!get_post_meta(get_the_ID(),'_ungrynerd_baja', true )  || !get_post_meta(get_the_ID(),'_ungrynerd_no_cta', true )) :?>
	<div class="widget location">
		<?php if (is_tax('comercio_seccion')): ?>
			<?php $location_info = viviendu_location_info(get_tax_meta(get_queried_object()->term_id, 'viviendu_comercio_seccion_comercio', true));  ?>
		<?php elseif (is_tax('comercio')) : ?>
			<?php $location_info = viviendu_location_info(get_queried_object()->term_id);  ?>
		<?php elseif (is_tag()) : ?>
			<?php $location_info = viviendu_location_info(get_tax_meta(get_queried_object()->term_id, 'viviendu_comercio', true));  ?>
		<?php endif ?>
			<p><a href="#popup_contacto" class="btn btn-block btn-contact btn-primary" id="btn-contact-sidebar">Contactar</a></p>
		<?php if (!empty($location_info['url'])): ?>
			<p><a class="btn btn-block btn-visit" target="_blank" href="<?php echo esc_url($location_info['url']); ?>">Visitar web</a></p>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php dynamic_sidebar("Barra Lateral"); ?>
</div>

<div class="popup-wrapper" id="popup_contacto">
	<div class="popup-content">
		<a class="close" href="#">×</a>
		<!-- Begin MailChimp Signup Form -->
		<h3 class="title">Contactar con la empresa</h3>
		<p>¡Muchas gracias por tu interés!. Aún no es posible contactar con empresas a através de Viviendu, para conocer el momento en el que esta funcionalidad estará disponible déjanos tu correo electrónico.</p>
		<div id="mc_embed_signup1">
		<form action="//viviendu.us11.list-manage.com/subscribe/post?u=6421e5c37cf26359595d46661&amp;id=e64548f3e5" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
		    <div id="mc_embed_signup_scroll1">	
				<div class="mc-field-group">
					<p>
						<input placeholder="Correo electrónico" type="email" value="" name="EMAIL" class="required email input-block" id="mce-EMAIL">
					</p>
					<p><input type="checkbox" checked name="legal" id="legal"> <label for="legal">Acepto <a href="http://viviendu.com/aviso-legal/" target="_blank">términos y condiciones</a> para recibir alertas</label></p>
				</div>
				<input type="hidden" value="Contacto" name="CTA" id="mce-CTA">
				<input type="hidden" value="<?php echo $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]; ?>" name="URL" class="required" id="mce-URL">
				<input type="hidden" value="<?php the_title(); ?>" name="COMERCIO" class="" id="mce-COMERCIO">
				<div id="mce-responses1" class="clear">
					<div class="response" id="mce-error-response1" style="display:none"></div>
					<div class="response" id="mce-success-response1" style="display:none"></div>
				</div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
				<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_6421e5c37cf26359595d46661_e64548f3e5" tabindex="-1" value=""></div>
				<div class="clear"><p><input type="submit" value="Enviar" name="subscribe" id="mc-embedded-subscribe1" class="button btn btn-block btn-contact"></p></div>
			</div>
		</form>
		</div>

		<!--End mc_embed_signup-->
	</div>
</div>